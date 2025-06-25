<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=skorsiswa_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Inserting semester 2 test data...\n";
    
    // First, let's check what students exist
    $stmt = $pdo->query("SELECT id, full_name, matric_no FROM users WHERE role = 'student' LIMIT 3");
    $students = $stmt->fetchAll();
    
    foreach ($students as $student) {
        echo "Student: {$student['full_name']} ({$student['matric_no']})\n";
    }
    
    // Define semester 2 courses
    $semester2Courses = [
        ['code' => 'CSC2103', 'name' => 'Data Structures and Algorithms', 'credits' => 3],
        ['code' => 'CSC2203', 'name' => 'Object-Oriented Programming', 'credits' => 4],
        ['code' => 'CSC2303', 'name' => 'Database Systems', 'credits' => 3],
        ['code' => 'MAT2101', 'name' => 'Discrete Mathematics', 'credits' => 3],
        ['code' => 'ENG2101', 'name' => 'Technical Writing', 'credits' => 2]
    ];
    
    // Get or create courses
    foreach ($semester2Courses as $courseData) {
        // Check if course exists
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE code = ?");
        $stmt->execute([$courseData['code']]);
        $course = $stmt->fetch();
        
        if (!$course) {
            // Create course with lecturer_id = 8 (assuming lecturer exists)
            $stmt = $pdo->prepare("INSERT INTO courses (code, name, credit_hours, lecturer_id, semester, year) VALUES (?, ?, ?, 8, 'Semester 2', '2024/2025')");
            $stmt->execute([$courseData['code'], $courseData['name'], $courseData['credits']]);
            $courseId = $pdo->lastInsertId();
            echo "Created course: {$courseData['code']}\n";
        } else {
            $courseId = $course['id'];
            echo "Course exists: {$courseData['code']}\n";
        }
        
        // Enroll students in semester 2 courses
        foreach ($students as $student) {
            // Check if enrollment exists
            $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$student['id'], $courseId]);
            $enrollment = $stmt->fetch();
            
            if (!$enrollment) {
                // Create enrollment
                $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, semester, academic_year) VALUES (?, ?, 'Semester 2', '2024/2025')");
                $stmt->execute([$student['id'], $courseId]);
                $enrollmentId = $pdo->lastInsertId();
                echo "Enrolled {$student['full_name']} in {$courseData['code']}\n";
            } else {
                $enrollmentId = $enrollment['id'];
            }
            
            // Create sample assessments for semester 2
            $assessments = [
                ['name' => 'Assignment 1', 'max_marks' => 20, 'weightage' => 15],
                ['name' => 'Assignment 2', 'max_marks' => 20, 'weightage' => 15],
                ['name' => 'Mid-term Test', 'max_marks' => 30, 'weightage' => 20],
                ['name' => 'Project', 'max_marks' => 30, 'weightage' => 20]
            ];
            
            foreach ($assessments as $assessment) {
                // Check if assessment exists
                $stmt = $pdo->prepare("SELECT id FROM assessments WHERE course_id = ? AND assessment_name = ?");
                $stmt->execute([$courseId, $assessment['name']]);
                $existingAssessment = $stmt->fetch();
                
                if (!$existingAssessment) {
                    // Create assessment
                    $stmt = $pdo->prepare("INSERT INTO assessments (course_id, assessment_name, max_marks, weightage, due_date) VALUES (?, ?, ?, ?, '2025-03-15')");
                    $stmt->execute([$courseId, $assessment['name'], $assessment['max_marks'], $assessment['weightage']]);
                    $assessmentId = $pdo->lastInsertId();
                } else {
                    $assessmentId = $existingAssessment['id'];
                }
                
                // Generate realistic marks (70-95% performance)
                $performance = 0.7 + (mt_rand() / mt_getrandmax()) * 0.25; // 70%-95%
                $studentMark = round($assessment['max_marks'] * $performance, 1);
                
                // Insert or update student marks
                $stmt = $pdo->prepare("
                    INSERT INTO marks (student_id, assessment_id, marks_obtained, remarks) 
                    VALUES (?, ?, ?, 'Good work') 
                    ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained)
                ");
                $stmt->execute([$student['id'], $assessmentId, $studentMark]);
            }
            
            // Add final exam marks
            $finalExamPerformance = 0.65 + (mt_rand() / mt_getrandmax()) * 0.3; // 65%-95%
            $finalExamMark = round(100 * $finalExamPerformance, 1);
            
            $stmt = $pdo->prepare("
                INSERT INTO final_exams (student_id, course_id, exam_marks, exam_date) 
                VALUES (?, ?, ?, '2025-05-20') 
                ON DUPLICATE KEY UPDATE exam_marks = VALUES(exam_marks)
            ");
            $stmt->execute([$student['id'], $courseId, $finalExamMark]);
        }
    }
    
    echo "\n✓ Semester 2 test data inserted successfully!\n";
    echo "You should now be able to view semester 2 mark breakdowns.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
