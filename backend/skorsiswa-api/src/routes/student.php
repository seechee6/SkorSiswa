<?php
// Student routes

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/student', function (RouteCollectorProxy $group) use ($pdo) {
    
    // Get all available courses (without student ID)
    $group->get('/courses', function (Request $request, Response $response) use ($pdo) {
        // Get all courses with lecturer information
        $stmt = $pdo->query('
            SELECT 
                c.id, 
                c.code, 
                c.name, 
                c.semester, 
                c.year, 
                u.full_name AS lecturer_name
            FROM 
                courses c
                JOIN users u ON c.lecturer_id = u.id
            ORDER BY 
                c.year DESC, c.semester DESC
        ');
        $courses = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Get all courses enrolled by a student
    $group->get('/courses/{student_id}', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        
        // Verify student exists
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = "student")');
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $response->getBody()->write(json_encode(['error' => 'Student not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Get enrolled courses with lecturer information
        $stmt = $pdo->prepare('
            SELECT 
                c.id, 
                c.code, 
                c.name, 
                c.semester, 
                c.year, 
                u.full_name AS lecturer_name,
                e.id AS enrollment_id
            FROM 
                enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN users u ON c.lecturer_id = u.id
            WHERE 
                e.student_id = ?
            ORDER BY 
                c.year DESC, c.semester DESC
        ');
        $stmt->execute([$studentId]);
        $courses = $stmt->fetchAll();
        
        // For each course, get a summary of assessment completion
        foreach ($courses as &$course) {
            // Count total assessments for this course
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM assessments WHERE course_id = ?');
            $stmt->execute([$course['id']]);
            $totalAssessments = $stmt->fetch()['total'];
            
            // Count graded assessments for this student
            $stmt = $pdo->prepare('
                SELECT COUNT(*) as graded
                FROM assessment_marks am
                JOIN assessments a ON am.assessment_id = a.id
                WHERE 
                    am.enrollment_id = ? 
                    AND am.mark IS NOT NULL
            ');
            $stmt->execute([$course['enrollment_id']]);
            $gradedAssessments = $stmt->fetch()['graded'];
            
            // Get current overall percentage
            $stmt = $pdo->prepare('
                SELECT 
                    SUM((am.mark / a.max_mark) * a.weight) AS current_percentage
                FROM 
                    assessment_marks am
                    JOIN assessments a ON am.assessment_id = a.id
                WHERE 
                    am.enrollment_id = ?
                    AND am.mark IS NOT NULL
            ');
            $stmt->execute([$course['enrollment_id']]);
            $currentPercentage = $stmt->fetch()['current_percentage'] ?? 0;
            
            // Add summary to course object
            $course['total_assessments'] = (int)$totalAssessments;
            $course['graded_assessments'] = (int)$gradedAssessments;
            $course['current_percentage'] = round((float)$currentPercentage, 2);
            $course['progress'] = $totalAssessments > 0 ? 
                round(($gradedAssessments / $totalAssessments) * 100) : 0;
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Get detailed mark breakdown for a specific course
    $group->get('/marks/{student_id}/{course_id}', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        $courseId = $args['course_id'];
        
        // Verify enrollment exists
        $stmt = $pdo->prepare('
            SELECT e.id as enrollment_id 
            FROM enrollments e 
            WHERE e.student_id = ? AND e.course_id = ?
        ');
        $stmt->execute([$studentId, $courseId]);
        $enrollment = $stmt->fetch();
        
        if (!$enrollment) {
            $response->getBody()->write(json_encode(['error' => 'Student not enrolled in this course']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $enrollmentId = $enrollment['enrollment_id'];
        
        // Get course details
        $stmt = $pdo->prepare('
            SELECT 
                c.*, 
                u.full_name AS lecturer_name
            FROM 
                courses c
                JOIN users u ON c.lecturer_id = u.id
            WHERE 
                c.id = ?
        ');
        $stmt->execute([$courseId]);
        $course = $stmt->fetch();
        
        // Get all assessments for this course with student marks
        $stmt = $pdo->prepare('
            SELECT 
                a.id, 
                a.name, 
                a.weight, 
                a.max_mark,
                am.mark
            FROM 
                assessments a
                LEFT JOIN assessment_marks am ON a.id = am.assessment_id AND am.enrollment_id = ?
            WHERE 
                a.course_id = ?
            ORDER BY 
                a.id
        ');
        $stmt->execute([$enrollmentId, $courseId]);
        $assessments = $stmt->fetchAll();
        
        // Calculate totals and weighted scores
        $totalWeightedScore = 0;
        $totalCompletedWeight = 0;
        
        foreach ($assessments as &$assessment) {
            // For graded assessments, calculate the weighted score
            if ($assessment['mark'] !== null) {
                $percentageScore = $assessment['mark'] / $assessment['max_mark'];
                $weightedScore = $percentageScore * $assessment['weight'];
                $assessment['percentage'] = round($percentageScore * 100, 2);
                $assessment['weighted_score'] = round($weightedScore, 2);
                
                $totalWeightedScore += $weightedScore;
                $totalCompletedWeight += $assessment['weight'];
            } else {
                $assessment['percentage'] = null;
                $assessment['weighted_score'] = null;
            }
              // Convert to appropriate types
            $assessment['weight'] = (float)$assessment['weight'];
            $assessment['max_marks'] = (float)$assessment['max_mark']; // Add max_marks for frontend compatibility
            $assessment['max_mark'] = (float)$assessment['max_mark'];
            $assessment['mark'] = $assessment['mark'] !== null ? (float)$assessment['mark'] : null;
        }
        
        // Get final exam mark if available
        $stmt = $pdo->prepare('
            SELECT mark 
            FROM final_exam_marks 
            WHERE enrollment_id = ?
        ');
        $stmt->execute([$enrollmentId]);
        $finalExam = $stmt->fetch();
        $finalExamMark = $finalExam ? (float)$finalExam['mark'] : null;
        
        // Calculate overall grade
        $overallPercentage = round($totalWeightedScore, 2);
        $grade = null;
        
        if ($totalWeightedScore > 0) {
            // Simple grade calculation logic
            if ($overallPercentage >= 90) $grade = 'A+';
            elseif ($overallPercentage >= 80) $grade = 'A';
            elseif ($overallPercentage >= 75) $grade = 'A-';
            elseif ($overallPercentage >= 70) $grade = 'B+';
            elseif ($overallPercentage >= 65) $grade = 'B';
            elseif ($overallPercentage >= 60) $grade = 'B-';
            elseif ($overallPercentage >= 55) $grade = 'C+';
            elseif ($overallPercentage >= 50) $grade = 'C';
            elseif ($overallPercentage >= 45) $grade = 'D+';
            elseif ($overallPercentage >= 40) $grade = 'D';
            else $grade = 'F';
        }
        
        // Get class average for comparison
        $stmt = $pdo->prepare('
            WITH student_scores AS (
                SELECT 
                    e.id as enrollment_id,
                    SUM((am.mark / a.max_mark) * a.weight) AS weighted_score
                FROM 
                    enrollments e
                    JOIN assessment_marks am ON e.id = am.enrollment_id
                    JOIN assessments a ON am.assessment_id = a.id
                WHERE 
                    e.course_id = ? AND am.mark IS NOT NULL
                GROUP BY 
                    e.id
            )
            SELECT AVG(weighted_score) as class_average
            FROM student_scores
        ');
        $stmt->execute([$courseId]);
        $classAverage = $stmt->fetch()['class_average'] ?? 0;
        
        // Get feedback/remarks if any
        $stmt = $pdo->prepare('
            SELECT remark, created_at
            FROM feedback_remarks
            WHERE enrollment_id = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([$enrollmentId]);
        $remarks = $stmt->fetchAll();
          $response->getBody()->write(json_encode([
            'success' => true,
            'course' => $course,
            'components' => $assessments,
            'totalMarks' => $overallPercentage,
            'grade' => $grade,
            'classAverage' => round((float)$classAverage, 2),
            'remarks' => $remarks,
            'completedWeight' => $totalCompletedWeight
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
      // Get comparison with coursemates
    $group->get('/compare/{student_id}/{course_id}', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        $courseId = $args['course_id'];
        
        // Verify enrollment exists
        $stmt = $pdo->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
        $stmt->execute([$studentId, $courseId]);
        $enrollment = $stmt->fetch();
        
        if (!$enrollment) {
            $response->getBody()->write(json_encode(['error' => 'Student not enrolled in this course']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $enrollmentId = $enrollment['id'];
        
        // Get course details
        $stmt = $pdo->prepare('SELECT code, name FROM courses WHERE id = ?');
        $stmt->execute([$courseId]);
        $course = $stmt->fetch();
        
        // Get all assessments for the course
        $stmt = $pdo->prepare('SELECT id, name, weight, max_mark FROM assessments WHERE course_id = ? ORDER BY id');
        $stmt->execute([$courseId]);
        $assessments = $stmt->fetchAll();
        
        // Get class size (total students enrolled in the course)
        $stmt = $pdo->prepare('SELECT COUNT(*) as class_size FROM enrollments WHERE course_id = ?');
        $stmt->execute([$courseId]);
        $classSize = (int)$stmt->fetch()['class_size'];        // Calculate overall score for student
        $stmt = $pdo->prepare('
            SELECT SUM((am.mark / a.max_mark) * a.weight) as total_score
            FROM assessment_marks am
            JOIN assessments a ON am.assessment_id = a.id
            WHERE am.enrollment_id = ? AND am.mark IS NOT NULL
        ');
        $stmt->execute([$enrollmentId]);
        $studentResult = $stmt->fetch();
        $studentOverallScore = $stmt->fetch() ? (float)$studentResult['total_score'] : 0;
        
        // Calculate class average overall score - ensure this matches the calculation in the marks endpoint
        $stmt = $pdo->prepare('
            WITH student_scores AS (
                SELECT 
                    e.id as enrollment_id,
                    SUM((am.mark / a.max_mark) * a.weight) AS weighted_score
                FROM 
                    enrollments e
                    JOIN assessment_marks am ON e.id = am.enrollment_id
                    JOIN assessments a ON am.assessment_id = a.id
                WHERE 
                    e.course_id = ? AND am.mark IS NOT NULL
                GROUP BY 
                    e.id
            )
            SELECT AVG(weighted_score) as class_average
            FROM student_scores
        ');
        $stmt->execute([$courseId]);
        $classAverageResult = $stmt->fetch();
        $classAverage = $classAverageResult ? (float)$classAverageResult['class_average'] : 0;
        
        // Calculate student's percentile
        $stmt = $pdo->prepare('
            WITH student_scores AS (
                SELECT 
                    e.student_id,
                    SUM((am.mark / a.max_mark) * a.weight) AS total_score
                FROM 
                    enrollments e
                    JOIN assessment_marks am ON e.id = am.enrollment_id
                    JOIN assessments a ON am.assessment_id = a.id
                WHERE 
                    e.course_id = ? AND am.mark IS NOT NULL
                GROUP BY 
                    e.student_id
            )
            SELECT COUNT(*) as better_than
            FROM student_scores
            WHERE total_score > (
                SELECT total_score FROM student_scores WHERE student_id = ?
            )
        ');
        $stmt->execute([$courseId, $studentId]);
        $betterThan = (int)($stmt->fetch()['better_than'] ?? 0);
        
        // Calculate percentile (higher is better)
        $percentile = $classSize > 0 ? 
            round(100 - (($betterThan / $classSize) * 100)) : 0;
          // Get score distribution for histogram
        $distribution = [0, 0, 0, 0, 0]; // 0-20, 21-40, 41-60, 61-80, 81-100
        
        $stmt = $pdo->prepare('
            WITH student_scores AS (
                SELECT 
                    e.student_id,
                    SUM((am.mark / a.max_mark) * a.weight) AS total_score
                FROM 
                    enrollments e
                    JOIN assessment_marks am ON e.id = am.enrollment_id
                    JOIN assessments a ON am.assessment_id = a.id
                WHERE 
                    e.course_id = ? AND am.mark IS NOT NULL
                GROUP BY 
                    e.student_id
            )
            SELECT total_score
            FROM student_scores
        ');
        $stmt->execute([$courseId]);
        $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($scores) > 0) {
            foreach ($scores as $score) {
                // Adjusted to use percentage values (0-100) rather than absolute values
                if ($score >= 0 && $score < 20) $distribution[0]++;
                elseif ($score >= 20 && $score < 40) $distribution[1]++;
                elseif ($score >= 40 && $score < 60) $distribution[2]++;
                elseif ($score >= 60 && $score < 80) $distribution[3]++;
                else $distribution[4]++;
            }
        }
          // Debug the score distribution array
        error_log("Score distribution: " . json_encode($distribution));
        
        // Process each assessment component
        $components = [];
        
        foreach ($assessments as $assessment) {
            $assessmentId = $assessment['id'];
            $assessmentName = $assessment['name'];
            $weight = (float)$assessment['weight'];
            $maxMark = (float)$assessment['max_mark'];
            
            // Get student's score
            $stmt = $pdo->prepare('
                SELECT mark
                FROM assessment_marks 
                WHERE assessment_id = ? AND enrollment_id = ?
            ');
            $stmt->execute([$assessmentId, $enrollmentId]);
            $markResult = $stmt->fetch();
            $studentMark = $markResult ? (float)$markResult['mark'] : null;
            
            // Skip if no mark for this assessment
            if ($studentMark === null) continue;
            
            // Calculate percentage score
            $yourScore = ($studentMark / $maxMark) * 100;
            
            // Get class statistics
            $stmt = $pdo->prepare('
                SELECT 
                    AVG(am.mark / ?) * 100 as avg_percentage,
                    COUNT(am.mark) as student_count
                FROM 
                    assessment_marks am
                    JOIN enrollments e ON am.enrollment_id = e.id
                WHERE 
                    am.assessment_id = ? AND am.mark IS NOT NULL
            ');
            $stmt->execute([$maxMark, $assessmentId]);
            $stats = $stmt->fetch();
            $average = (float)$stats['avg_percentage'];
            
            // Get student position for this component
            $stmt = $pdo->prepare('
                SELECT COUNT(*) + 1 as position
                FROM assessment_marks am
                JOIN enrollments e ON am.enrollment_id = e.id
                WHERE 
                    am.assessment_id = ? 
                    AND am.mark > ? 
                    AND e.course_id = ?
            ');
            $stmt->execute([$assessmentId, $studentMark, $courseId]);
            $position = (int)$stmt->fetch()['position'];
            
            $components[] = [
                'id' => $assessmentId,
                'name' => $assessmentName,
                'weight' => $weight,
                'yourScore' => round($yourScore, 1),
                'average' => round($average, 1),
                'difference' => round($yourScore - $average, 1),
                'position' => $position
            ];
        }          // Debug information
        error_log("Response data: components=" . count($components) . 
                  ", classAverage=" . round($classAverage, 2) . 
                  ", yourScore=" . round($studentOverallScore, 2) . 
                  ", classSize=" . $classSize . 
                  ", distribution=" . json_encode($distribution));

        $responseData = [
            'success' => true,
            'courseName' => $course['code'] . ' - ' . $course['name'],
            'components' => $components,
            'percentile' => $percentile,
            'classAverage' => round($classAverage, 2),
            'yourScore' => round($studentOverallScore, 2),
            'classSize' => $classSize,
            'distribution' => $distribution
        ];
                    
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Get class ranking for a specific course
    $group->get('/ranking/{student_id}/{course_id}', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        $courseId = $args['course_id'];
        
        try {
            // Verify enrollment exists
            $stmt = $pdo->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
            $stmt->execute([$studentId, $courseId]);
            $enrollment = $stmt->fetch();
            
            if (!$enrollment) {
                $response->getBody()->write(json_encode(['error' => 'Student not enrolled in this course']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            
            // Get course details
            $stmt = $pdo->prepare('SELECT code, name FROM courses WHERE id = ?');
            $stmt->execute([$courseId]);
            $course = $stmt->fetch();
            
            if (!$course) {
                $response->getBody()->write(json_encode(['error' => 'Course not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            
            // Get all students in the course with their scores
            $stmt = $pdo->prepare('
                WITH student_scores AS (
                    SELECT 
                        e.student_id,
                        u.full_name,
                        SUM((am.mark / a.max_mark) * a.weight) AS total_score
                    FROM 
                        enrollments e
                        JOIN users u ON e.student_id = u.id
                        JOIN assessment_marks am ON e.id = am.enrollment_id
                        JOIN assessments a ON am.assessment_id = a.id
                    WHERE 
                        e.course_id = ? AND am.mark IS NOT NULL
                    GROUP BY 
                        e.student_id, u.full_name
                )
                SELECT 
                    student_id,
                    full_name,
                    total_score,
                    ROW_NUMBER() OVER (ORDER BY total_score DESC) as rank
                FROM student_scores
                ORDER BY total_score DESC
            ');
            $stmt->execute([$courseId]);
            $allStudents = $stmt->fetchAll();
            
            if (empty($allStudents)) {
                $response->getBody()->write(json_encode(['error' => 'No graded assessments found for this course']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            
            // Find current student's position and data
            $yourRank = null;
            $yourScore = null;
            $yourGrade = null;
            
            foreach ($allStudents as $student) {
                if ($student['student_id'] == $studentId) {
                    $yourRank = (int)$student['rank'];
                    $yourScore = (float)$student['total_score'];
                    
                    // Calculate grade
                    if ($yourScore >= 90) $yourGrade = 'A+';
                    elseif ($yourScore >= 80) $yourGrade = 'A';
                    elseif ($yourScore >= 75) $yourGrade = 'A-';
                    elseif ($yourScore >= 70) $yourGrade = 'B+';
                    elseif ($yourScore >= 65) $yourGrade = 'B';
                    elseif ($yourScore >= 60) $yourGrade = 'B-';
                    elseif ($yourScore >= 55) $yourGrade = 'C+';
                    elseif ($yourScore >= 50) $yourGrade = 'C';
                    elseif ($yourScore >= 45) $yourGrade = 'D+';
                    elseif ($yourScore >= 40) $yourGrade = 'D';
                    else $yourGrade = 'F';
                    break;
                }
            }
            
            if ($yourRank === null) {
                $response->getBody()->write(json_encode(['error' => 'Student has no graded assessments in this course']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            
            // Calculate percentile
            $totalStudents = count($allStudents);
            $percentile = round(100 - (($yourRank - 1) / $totalStudents) * 100);
            
            // Get all students with grades and rankings
            $topStudents = [];
            foreach ($allStudents as $student) {
                $score = (float)$student['total_score'];
                
                // Calculate grade
                if ($score >= 90) $grade = 'A+';
                elseif ($score >= 80) $grade = 'A';
                elseif ($score >= 75) $grade = 'A-';
                elseif ($score >= 70) $grade = 'B+';
                elseif ($score >= 65) $grade = 'B';
                elseif ($score >= 60) $grade = 'B-';
                elseif ($score >= 55) $grade = 'C+';
                elseif ($score >= 50) $grade = 'C';
                elseif ($score >= 45) $grade = 'D+';
                elseif ($score >= 40) $grade = 'D';
                else $grade = 'F';
                
                $topStudents[] = [
                    'rank' => (int)$student['rank'],
                    'name' => $student['full_name'],
                    'score' => $score,
                    'grade' => $grade,
                    'isYou' => ($student['student_id'] == $studentId),
                    'anonymousId' => (int)$student['rank']
                ];
            }
            
            // Get score distribution for histogram
            $distribution = [0, 0, 0, 0, 0]; // 0-20, 21-40, 41-60, 61-80, 81-100
            
            foreach ($allStudents as $student) {
                $score = (float)$student['total_score'];
                if ($score >= 0 && $score < 20) $distribution[0]++;
                elseif ($score >= 20 && $score < 40) $distribution[1]++;
                elseif ($score >= 40 && $score < 60) $distribution[2]++;
                elseif ($score >= 60 && $score < 80) $distribution[3]++;
                else $distribution[4]++;
            }
            
            $responseData = [
                'success' => true,
                'courseName' => $course['code'] . ' - ' . $course['name'],
                'yourRank' => $yourRank,
                'yourScore' => $yourScore,
                'yourGrade' => $yourGrade,
                'percentile' => $percentile,
                'totalStudents' => $totalStudents,
                'topStudents' => $topStudents,
                'distribution' => $distribution
            ];
            
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    // Get dashboard data for student
    $group->get('/dashboard/{student_id}', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        
        // Verify student exists
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = "student")');
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $response->getBody()->write(json_encode(['error' => 'Student not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Get summary of current courses
        $stmt = $pdo->prepare('
            SELECT 
                c.id, 
                c.code, 
                c.name, 
                c.semester, 
                c.year,
                e.id AS enrollment_id
            FROM 
                enrollments e
                JOIN courses c ON e.course_id = c.id
            WHERE 
                e.student_id = ?
            ORDER BY 
                c.year DESC, c.semester DESC
            LIMIT 5
        ');
        $stmt->execute([$studentId]);
        $currentCourses = $stmt->fetchAll();
          // For each course, get current grade and progress
        foreach ($currentCourses as &$course) {
            // Get current percentage
            $stmt = $pdo->prepare('
                SELECT 
                    SUM((am.mark / a.max_mark) * a.weight) AS current_percentage
                FROM 
                    assessment_marks am
                    JOIN assessments a ON am.assessment_id = a.id
                WHERE 
                    am.enrollment_id = ?
                    AND am.mark IS NOT NULL
            ');
            $stmt->execute([$course['enrollment_id']]);
            $result = $stmt->fetch();
            $currentPercentage = $result['current_percentage'] ?? 0;
            
            // Calculate grade
            $grade = 'N/A';
            if ($currentPercentage >= 90) $grade = 'A+';
            elseif ($currentPercentage >= 80) $grade = 'A';
            elseif ($currentPercentage >= 75) $grade = 'A-';
            elseif ($currentPercentage >= 70) $grade = 'B+';
            elseif ($currentPercentage >= 65) $grade = 'B';
            elseif ($currentPercentage >= 60) $grade = 'B-';
            elseif ($currentPercentage >= 55) $grade = 'C+';
            elseif ($currentPercentage >= 50) $grade = 'C';
            elseif ($currentPercentage >= 45) $grade = 'D+';
            elseif ($currentPercentage >= 40) $grade = 'D';
            elseif ($currentPercentage > 0) $grade = 'F';
            
            // Calculate progress based on completed assessments
            $stmt = $pdo->prepare('
                SELECT 
                    COUNT(a.id) as total_assessments,
                    COUNT(CASE WHEN am.mark IS NOT NULL THEN 1 END) as completed_assessments
                FROM 
                    assessments a
                    LEFT JOIN assessment_marks am ON a.id = am.assessment_id AND am.enrollment_id = ?
                WHERE 
                    a.course_id = ?
            ');
            $stmt->execute([$course['enrollment_id'], $course['id']]);
            $progressResult = $stmt->fetch();
            
            $totalAssessments = $progressResult['total_assessments'] ?? 0;
            $completedAssessments = $progressResult['completed_assessments'] ?? 0;
            $progress = $totalAssessments > 0 ? 
                round(($completedAssessments / $totalAssessments) * 100, 0) : 0;
            
            $course['current_percentage'] = round((float)$currentPercentage, 2);
            $course['grade'] = $grade;
            $course['progress'] = $progress;
        }
          // Get recent assessments with marks
        $stmt = $pdo->prepare('
            SELECT 
                c.code, 
                c.name AS course_name,
                a.name AS assessment_name,
                a.max_mark,
                am.mark
            FROM 
                assessment_marks am
                JOIN assessments a ON am.assessment_id = a.id
                JOIN enrollments e ON am.enrollment_id = e.id
                JOIN courses c ON e.course_id = c.id
            WHERE 
                e.student_id = ? AND am.mark IS NOT NULL
            LIMIT 10
        ');
        $stmt->execute([$studentId]);
        $recentAssessments = $stmt->fetchAll();
        
        foreach ($recentAssessments as &$assessment) {
            $assessment['max_mark'] = (float)$assessment['max_mark'];
            $assessment['mark'] = (float)$assessment['mark'];
            $assessment['percentage'] = round(($assessment['mark'] / $assessment['max_mark']) * 100, 2);
        }
        
        // Get recent notifications
        $stmt = $pdo->prepare('
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5
        ');
        $stmt->execute([$studentId]);
        $notifications = $stmt->fetchAll();
          // Calculate CGPA (simplified version - in reality this would involve more complex calculations)
        $totalGradePoints = 0;
        $totalCourses = count($currentCourses);
        
        foreach ($currentCourses as $course) {
            $gradePoint = 0;
            if ($course['grade'] === 'A+') $gradePoint = 4.0;
            elseif ($course['grade'] === 'A') $gradePoint = 4.0;
            elseif ($course['grade'] === 'A-') $gradePoint = 3.7;
            elseif ($course['grade'] === 'B+') $gradePoint = 3.3;
            elseif ($course['grade'] === 'B') $gradePoint = 3.0;
            elseif ($course['grade'] === 'B-') $gradePoint = 2.7;
            elseif ($course['grade'] === 'C+') $gradePoint = 2.3;
            elseif ($course['grade'] === 'C') $gradePoint = 2.0;
            elseif ($course['grade'] === 'C-') $gradePoint = 1.7;
            elseif ($course['grade'] === 'D+') $gradePoint = 1.3;
            elseif ($course['grade'] === 'D') $gradePoint = 1.0;
            
            $totalGradePoints += $gradePoint;
        }
        
        $cgpa = $totalCourses > 0 ? number_format($totalGradePoints / $totalCourses, 2) : '0.00';
          // Get student ranking based on CGPA
        $stmt = $pdo->prepare("
            SELECT student_cgpa.student_id, student_cgpa.cgpa,
                   (SELECT COUNT(*) + 1 FROM (
                       SELECT s2.id, AVG(s2_grade_points) as s2_cgpa
                       FROM users s2
                       JOIN enrollments e2 ON s2.id = e2.student_id
                       JOIN courses c2 ON e2.course_id = c2.id
                       JOIN (
                           SELECT e3.id as enrollment_id,
                               CASE 
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 90 THEN 4.0
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 80 THEN 4.0
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 75 THEN 3.7
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 70 THEN 3.3
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 65 THEN 3.0
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 60 THEN 2.7
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 55 THEN 2.3
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 50 THEN 2.0
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 45 THEN 1.7
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 40 THEN 1.3
                                   WHEN SUM((am3.mark / a3.max_mark) * a3.weight) >= 35 THEN 1.0
                                   ELSE 0.0
                               END as s2_grade_points
                           FROM enrollments e3
                           JOIN assessment_marks am3 ON e3.id = am3.enrollment_id
                           JOIN assessments a3 ON am3.assessment_id = a3.id
                           WHERE am3.mark IS NOT NULL
                           GROUP BY e3.id
                       ) gp ON e2.id = gp.enrollment_id
                       WHERE s2.role_id = (SELECT id FROM roles WHERE name = 'student')
                       GROUP BY s2.id
                       HAVING s2_cgpa > ?
                   ) higher_performers) as rank,
                   (SELECT COUNT(*) FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'student')) as total_students
            FROM (
                SELECT ? as student_id, ? as cgpa
            ) student_cgpa
        ");
        $cgpaFloat = (float)$cgpa;
        $stmt->execute([$cgpaFloat, $studentId, $cgpaFloat]);
        $rankingData = $stmt->fetch();
        
        $studentRank = $rankingData['rank'] ?? 1;
        $totalStudents = $rankingData['total_students'] ?? 1;
        $ranking = $studentRank . ' / ' . $totalStudents;
        
        // Calculate real semester progress based on completed assessments
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT a.id) as total_assessments,
                COUNT(DISTINCT CASE WHEN am.mark IS NOT NULL THEN a.id END) as completed_assessments
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            JOIN assessments a ON c.id = a.course_id
            LEFT JOIN assessment_marks am ON a.id = am.assessment_id AND e.id = am.enrollment_id
            WHERE e.student_id = ?
            AND c.semester = (
                SELECT c2.semester 
                FROM enrollments e2 
                JOIN courses c2 ON e2.course_id = c2.id 
                WHERE e2.student_id = ? 
                ORDER BY c2.year DESC, c2.semester DESC 
                LIMIT 1
            )
            AND c.year = (
                SELECT c2.year 
                FROM enrollments e2 
                JOIN courses c2 ON e2.course_id = c2.id 
                WHERE e2.student_id = ? 
                ORDER BY c2.year DESC, c2.semester DESC 
                LIMIT 1
            )
        ");
        $stmt->execute([$studentId, $studentId, $studentId]);
        $progressData = $stmt->fetch();
        
        $totalAssessments = $progressData['total_assessments'] ?? 0;
        $completedAssessments = $progressData['completed_assessments'] ?? 0;
        $semesterProgress = $totalAssessments > 0 ? 
            round(($completedAssessments / $totalAssessments) * 100, 0) : 0;
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'student' => [
                'id' => $student['id'],
                'name' => $student['full_name'],
                'matric_no' => $student['matric_no'],
                'email' => $student['email']
            ],
            'cgpa' => $cgpa,
            'ranking' => $ranking,
            'semesterProgress' => $semesterProgress,
            'currentCourses' => $currentCourses,
            'notifications' => $notifications
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Get performance trends for a student
    $group->get('/performance/{student_id}', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        
        try {
            // Verify student exists
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = "student")');
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                $response->getBody()->write(json_encode(['error' => 'Student not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            
            // Get all courses enrolled by the student with performance data
            $stmt = $pdo->prepare('
                SELECT 
                    c.id,
                    c.code,
                    c.name,
                    c.semester,
                    c.year,
                    u.full_name AS lecturer_name,
                    e.id AS enrollment_id
                FROM 
                    enrollments e
                    JOIN courses c ON e.course_id = c.id
                    JOIN users u ON c.lecturer_id = u.id
                WHERE 
                    e.student_id = ?
                ORDER BY 
                    c.year ASC, c.semester ASC
            ');
            $stmt->execute([$studentId]);
            $courses = $stmt->fetchAll();
            
            $performanceData = [];
            
            foreach ($courses as $course) {
                $courseId = $course['id'];
                $enrollmentId = $course['enrollment_id'];
                
                // Calculate overall percentage for this course
                $stmt = $pdo->prepare('
                    SELECT 
                        SUM((am.mark / a.max_mark) * a.weight) AS current_percentage
                    FROM 
                        assessment_marks am
                        JOIN assessments a ON am.assessment_id = a.id
                    WHERE 
                        am.enrollment_id = ? AND am.mark IS NOT NULL
                ');
                $stmt->execute([$enrollmentId]);
                $result = $stmt->fetch();
                $currentPercentage = $result['current_percentage'] ?? 0;
                
                // Calculate grade based on percentage
                $grade = null;
                if ($currentPercentage >= 90) $grade = 'A+';
                elseif ($currentPercentage >= 80) $grade = 'A';
                elseif ($currentPercentage >= 75) $grade = 'A-';
                elseif ($currentPercentage >= 70) $grade = 'B+';
                elseif ($currentPercentage >= 65) $grade = 'B';
                elseif ($currentPercentage >= 60) $grade = 'B-';
                elseif ($currentPercentage >= 55) $grade = 'C+';
                elseif ($currentPercentage >= 50) $grade = 'C';
                elseif ($currentPercentage >= 45) $grade = 'D+';
                elseif ($currentPercentage >= 40) $grade = 'D';
                else $grade = 'F';
                
                // Determine status based on whether assessments are completed
                $stmt = $pdo->prepare('
                    SELECT 
                        COUNT(a.id) as total_assessments,
                        COUNT(am.mark) as completed_assessments
                    FROM 
                        assessments a
                        LEFT JOIN assessment_marks am ON a.id = am.assessment_id AND am.enrollment_id = ?
                    WHERE 
                        a.course_id = ?
                ');
                $stmt->execute([$enrollmentId, $courseId]);
                $assessmentStatus = $stmt->fetch();
                
                $status = 'In Progress';
                if ($assessmentStatus['completed_assessments'] == $assessmentStatus['total_assessments'] && $assessmentStatus['total_assessments'] > 0) {
                    $status = 'Completed';
                } elseif ($assessmentStatus['completed_assessments'] == 0) {
                    $status = 'Pending';
                }
                  // Assume 3 credit hours per course (you can adjust this or add a credit_hours field to courses table)
                $creditHours = 3;
                
                // Format semester for display
                $semesterDisplay = $course['semester'];
                if ($course['semester'] === 'Fall') {
                    $semesterDisplay = 'Semester 1';
                } elseif ($course['semester'] === 'Spring') {
                    $semesterDisplay = 'Semester 2';
                }
                
                $performanceData[] = [
                    'id' => $courseId,
                    'semester' => $semesterDisplay . ' ' . $course['year'],
                    'code' => $course['code'],
                    'name' => $course['name'],
                    'creditHours' => $creditHours,
                    'grade' => $grade,
                    'percentage' => round((float)$currentPercentage, 2),
                    'status' => $status,
                    'lecturer' => $course['lecturer_name']
                ];
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $performanceData
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    // Get all notifications for a student
    $group->get('/notifications/{student_id}', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        
        try {
            // Verify student exists
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = "student")');
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                $response->getBody()->write(json_encode(['error' => 'Student not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            
            // Get all notifications for the student
            $stmt = $pdo->prepare('
                SELECT 
                    id, 
                    message, 
                    is_read, 
                    created_at
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ');
            $stmt->execute([$studentId]);
            $notifications = $stmt->fetchAll();
            
            // Count unread notifications
            $stmt = $pdo->prepare('SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = FALSE');
            $stmt->execute([$studentId]);
            $unreadCount = $stmt->fetch()['unread_count'];
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => (int)$unreadCount
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });
    
    // Mark notifications as read
    $group->post('/notifications/{student_id}/mark-read', function (Request $request, Response $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        $data = $request->getParsedBody();
        
        try {
            // Verify student exists
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = "student")');
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                $response->getBody()->write(json_encode(['error' => 'Student not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            
            if (isset($data['notification_ids']) && is_array($data['notification_ids'])) {
                // Mark specific notifications as read
                $placeholders = str_repeat('?,', count($data['notification_ids']) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id IN ($placeholders) AND user_id = ?");
                $stmt->execute(array_merge($data['notification_ids'], [$studentId]));
            } else {
                // Mark all notifications as read
                $stmt = $pdo->prepare('UPDATE notifications SET is_read = TRUE WHERE user_id = ?');
                $stmt->execute([$studentId]);
            }
            
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    });

    // ...existing code...
    
});
