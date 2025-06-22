<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Create Slim app
$app = AppFactory::create();

// Add JSON body parsing middleware
$app->addBodyParsingMiddleware();

// CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'false');
});

// Handle preflight OPTIONS requests for CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

// Database connection settings
$host = 'localhost';
$db   = 'skorsiswa_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Helper function to log system activities
function logSystemActivity($pdo, $user_id, $action) {
    $stmt = $pdo->prepare('INSERT INTO system_logs (user_id, action) VALUES (?, ?)');
    $stmt->execute([$user_id, $action]);
}

// Helper function to create notification
function createNotification($pdo, $user_id, $message) {
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
    $stmt->execute([$user_id, $message]);
}

// Helper function to create mark update notification
function createMarkUpdateNotification($pdo, $lecturer_id, $student_id, $enrollment_id, $assessment_id = null, $is_final_exam = false, $old_mark = null, $new_mark, $reason = null) {
    $stmt = $pdo->prepare('INSERT INTO mark_update_notifications (lecturer_id, student_id, enrollment_id, assessment_id, is_final_exam, old_mark, new_mark, change_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$lecturer_id, $student_id, $enrollment_id, $assessment_id, $is_final_exam, $old_mark, $new_mark, $reason]);
}

// ===================== AUTHENTICATION =====================
// GET /login route for browser access
$app->get('/login', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Please use POST method to log in with matric_no/staff_id and password.'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Login endpoint - supports both matric_no and staff_id
$app->post('/login', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $identifier = $data['matric_no'] ?? null;
    $password = $data['password'] ?? null;

    if (!$identifier || !$password) {
        $response->getBody()->write(json_encode(['error' => 'Matric number/Staff ID and password required.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Try to find user by matric_no first (for students), then by staff_id (for lecturers/staff)
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.matric_no = ? OR u.staff_id = ?');
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        logSystemActivity($pdo, $user['id'], "User logged in");
        $response->getBody()->write(json_encode(['success' => true, 'user' => $user]));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['error' => 'Invalid credentials.']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== COURSES MANAGEMENT =====================
// Get all courses
$app->get('/courses', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query('SELECT c.*, u.full_name AS lecturer_name FROM courses c JOIN users u ON c.lecturer_id = u.id');
    $courses = $stmt->fetchAll();
    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});

// Create course (Admin/Lecturer)
$app->post('/courses', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('INSERT INTO courses (code, name, lecturer_id, semester, year) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$data['code'], $data['name'], $data['lecturer_id'], $data['semester'], $data['year']]);
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Update course
$app->put('/courses/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $fields = [];
    $params = [];
    foreach (['code','name','lecturer_id','semester','year'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
    }
    $params[] = $args['id'];
    $sql = 'UPDATE courses SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Delete course
$app->delete('/courses/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        // Start transaction to ensure data consistency
        $pdo->beginTransaction();
        
        // Get course info before deletion for logging
        $stmt = $pdo->prepare('SELECT c.*, u.full_name as lecturer_name FROM courses c JOIN users u ON c.lecturer_id = u.id WHERE c.id = ?');
        $stmt->execute([$args['id']]);
        $course_info = $stmt->fetch();
        
        if (!$course_info) {
            $pdo->rollBack();
            $response->getBody()->write(json_encode(['error' => 'Course not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete in the correct order to avoid foreign key constraint violations
        
        // 1. Delete assessment marks for all assessments in this course
        $stmt = $pdo->prepare('
            DELETE am FROM assessment_marks am 
            JOIN assessments a ON am.assessment_id = a.id 
            WHERE a.course_id = ?
        ');
        $stmt->execute([$args['id']]);
        
        // 2. Delete final exam marks for all enrollments in this course
        $stmt = $pdo->prepare('
            DELETE fem FROM final_exam_marks fem 
            JOIN enrollments e ON fem.enrollment_id = e.id 
            WHERE e.course_id = ?
        ');
        $stmt->execute([$args['id']]);
        
        // 3. Delete feedback remarks for all enrollments in this course
        $stmt = $pdo->prepare('
            DELETE fr FROM feedback_remarks fr 
            JOIN enrollments e ON fr.enrollment_id = e.id 
            WHERE e.course_id = ?
        ');
        $stmt->execute([$args['id']]);
        
        // 4. Delete remark requests for all enrollments in this course
        $stmt = $pdo->prepare('
            DELETE rr FROM remark_requests rr 
            JOIN enrollments e ON rr.enrollment_id = e.id 
            WHERE e.course_id = ?
        ');
        $stmt->execute([$args['id']]);
        
        // 5. Delete assessments for this course
        $stmt = $pdo->prepare('DELETE FROM assessments WHERE course_id = ?');
        $stmt->execute([$args['id']]);
        
        // 6. Delete enrollments for this course
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE course_id = ?');
        $stmt->execute([$args['id']]);
        
        // 7. Finally, delete the course itself
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([$args['id']]);
        
        // Commit the transaction
        $pdo->commit();
        
        // Log the activity
        if ($course_info['lecturer_id']) {
            logSystemActivity($pdo, $course_info['lecturer_id'], "Deleted course {$course_info['code']} - {$course_info['name']} (Course ID: {$args['id']})");
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Course and all associated data deleted successfully',
            'course_name' => $course_info['name'],
            'course_code' => $course_info['code']
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        // Rollback transaction on any error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Log the error for debugging
        error_log("Error deleting course {$args['id']}: " . $e->getMessage());
        
        $response->getBody()->write(json_encode([
            'error' => 'Failed to delete course',
            'details' => $e->getMessage(),
            'course_id' => $args['id']
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== ENROLLMENT MANAGEMENT =====================
// Get all enrollments
$app->get('/enrollments', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query('
        SELECT e.*, u.full_name AS student_name, u.matric_no, u.email, c.name as course_name, c.code as course_code,
               e.id as enrollment_id, e.created_at as enrollment_date
        FROM enrollments e 
        JOIN users u ON e.student_id = u.id 
        JOIN courses c ON e.course_id = c.id
        ORDER BY e.created_at DESC
    ');
    $enrollments = $stmt->fetchAll();
    
    // Add created_at if not exists (for compatibility)
    foreach ($enrollments as &$enrollment) {
        if (!isset($enrollment['created_at'])) {
            $enrollment['created_at'] = date('Y-m-d H:i:s');
        }
    }
    
    $response->getBody()->write(json_encode($enrollments));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get all enrollments for a course with detailed student info
$app->get('/courses/{course_id}/enrollments', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('
        SELECT e.*, u.full_name AS student_name, u.matric_no, u.email,
               e.id as enrollment_id, COALESCE(e.created_at, NOW()) as enrollment_date
        FROM enrollments e 
        JOIN users u ON e.student_id = u.id 
        WHERE e.course_id = ?
        ORDER BY u.full_name
    ');
    $stmt->execute([$args['course_id']]);
    $enrollments = $stmt->fetchAll();
    $response->getBody()->write(json_encode($enrollments));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get enrollments for a student
$app->get('/students/{student_id}/enrollments', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('
        SELECT e.*, c.name AS course_name, c.code AS course_code, c.semester, c.year,
               u.full_name as lecturer_name
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        JOIN users u ON c.lecturer_id = u.id
        WHERE e.student_id = ?
        ORDER BY c.year DESC, c.semester DESC
    ');
    $stmt->execute([$args['student_id']]);
    $enrollments = $stmt->fetchAll();
    $response->getBody()->write(json_encode($enrollments));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get unenrolled students for a course
$app->get('/courses/{course_id}/unenrolled-students', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('
        SELECT u.id, u.full_name, u.matric_no, u.email
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE r.name = "student"
        AND u.id NOT IN (
            SELECT e.student_id 
            FROM enrollments e 
            WHERE e.course_id = ?
        )
        ORDER BY u.full_name
    ');
    $stmt->execute([$args['course_id']]);
    $students = $stmt->fetchAll();
    $response->getBody()->write(json_encode($students));
    return $response->withHeader('Content-Type', 'application/json');
});

// Enroll student in course
$app->post('/enrollments', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    
    // Check if student is already enrolled
    $stmt = $pdo->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
    $stmt->execute([$data['student_id'], $data['course_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $response->getBody()->write(json_encode(['error' => 'Student already enrolled in this course']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    
    try {
        $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)');
        $stmt->execute([$data['student_id'], $data['course_id']]);
        
        // Get student and course info for notification
        $stmt = $pdo->prepare('SELECT u.full_name FROM users u WHERE u.id = ?');
        $stmt->execute([$data['student_id']]);
        $student = $stmt->fetch();
        
        $stmt = $pdo->prepare('SELECT c.name, c.code FROM courses c WHERE c.id = ?');
        $stmt->execute([$data['course_id']]);
        $course = $stmt->fetch();
        
        // Create notification for student
        createNotification($pdo, $data['student_id'], "You have been enrolled in {$course['code']} - {$course['name']}");
        
        // Log the activity
        $lecturer_id = $data['lecturer_id'] ?? null;
        if ($lecturer_id) {
            logSystemActivity($pdo, $lecturer_id, "Enrolled student {$student['full_name']} in course {$course['code']}");
        }
        
        $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Failed to enroll student: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Bulk enroll students
$app->post('/enrollments/bulk', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $course_id = $data['course_id'];
    $student_ids = $data['student_ids'];
    $lecturer_id = $data['lecturer_id'] ?? null;
    
    if (!is_array($student_ids) || empty($student_ids)) {
        $response->getBody()->write(json_encode(['error' => 'No students provided']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    
    $pdo->beginTransaction();
    
    try {
        $enrolled_count = 0;
        $already_enrolled = [];
        
        foreach ($student_ids as $student_id) {
            // Check if student is already enrolled
            $stmt = $pdo->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?');
            $stmt->execute([$student_id, $course_id]);
            $existing = $stmt->fetch();
            
            if (!$existing) {
                // Enroll the student
                $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)');
                $stmt->execute([$student_id, $course_id]);
                $enrolled_count++;
                
                // Get course info for notification
                if ($enrolled_count === 1) {
                    $stmt = $pdo->prepare('SELECT c.name, c.code FROM courses c WHERE c.id = ?');
                    $stmt->execute([$course_id]);
                    $course = $stmt->fetch();
                }
                
                // Create notification for student
                createNotification($pdo, $student_id, "You have been enrolled in {$course['code']} - {$course['name']}");
            } else {
                // Get student name for reporting
                $stmt = $pdo->prepare('SELECT full_name FROM users WHERE id = ?');
                $stmt->execute([$student_id]);
                $student = $stmt->fetch();
                $already_enrolled[] = $student['full_name'];
            }
        }
        
        // Log the activity
        if ($lecturer_id && $enrolled_count > 0) {
            logSystemActivity($pdo, $lecturer_id, "Bulk enrolled {$enrolled_count} students in course {$course['code']}");
        }
        
        $pdo->commit();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'enrolled_count' => $enrolled_count,
            'already_enrolled' => $already_enrolled,
            'message' => "Successfully enrolled {$enrolled_count} students"
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $response->getBody()->write(json_encode(['error' => 'Failed to enroll students: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Remove student enrollment
$app->delete('/enrollments/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        // Start transaction to ensure data consistency
        $pdo->beginTransaction();
        
        // Get enrollment info before deletion for logging
        $stmt = $pdo->prepare('
            SELECT e.student_id, e.course_id, u.full_name as student_name, c.name as course_name, c.code as course_code, c.lecturer_id
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            JOIN courses c ON e.course_id = c.id
            WHERE e.id = ?
        ');
        $stmt->execute([$args['id']]);
        $enrollment_info = $stmt->fetch();
        
        if (!$enrollment_info) {
            $pdo->rollBack();
            $response->getBody()->write(json_encode(['error' => 'Enrollment not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete related records first (to avoid foreign key constraint violations)
        
        // 1. Delete assessment marks
        $stmt = $pdo->prepare('DELETE FROM assessment_marks WHERE enrollment_id = ?');
        $stmt->execute([$args['id']]);
        
        // 2. Delete final exam marks
        $stmt = $pdo->prepare('DELETE FROM final_exam_marks WHERE enrollment_id = ?');
        $stmt->execute([$args['id']]);
        
        // 3. Delete feedback remarks
        $stmt = $pdo->prepare('DELETE FROM feedback_remarks WHERE enrollment_id = ?');
        $stmt->execute([$args['id']]);
        
        // 4. Delete remark requests
        $stmt = $pdo->prepare('DELETE FROM remark_requests WHERE enrollment_id = ?');
        $stmt->execute([$args['id']]);
        
        // 5. Finally, delete the enrollment record
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE id = ?');
        $stmt->execute([$args['id']]);
        
        // Commit the transaction
        $pdo->commit();
        
        // Create notification for student
        createNotification($pdo, $enrollment_info['student_id'], "You have been removed from {$enrollment_info['course_code']} - {$enrollment_info['course_name']}. All associated marks and records have been removed.");
        
        // Log the activity with the lecturer's ID instead of 0
        if ($enrollment_info['lecturer_id']) {
            logSystemActivity($pdo, $enrollment_info['lecturer_id'], "Removed student {$enrollment_info['student_name']} from course {$enrollment_info['course_code']} (Enrollment ID: {$args['id']})");
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Student successfully removed from course',
            'student_name' => $enrollment_info['student_name'],
            'course_name' => $enrollment_info['course_name']
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        // Rollback transaction on any error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Log the error for debugging
        error_log("Error removing enrollment {$args['id']}: " . $e->getMessage());
        
        $response->getBody()->write(json_encode([
            'error' => 'Failed to remove student from course',
            'details' => $e->getMessage(),
            'enrollment_id' => $args['id']
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== STUDENTS MANAGEMENT =====================
// Get all students
$app->get('/students', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->prepare('
        SELECT u.id, u.full_name, u.matric_no, u.email
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.name = "student"
        ORDER BY u.full_name
    ');
    $stmt->execute();
    $students = $stmt->fetchAll();
    $response->getBody()->write(json_encode($students));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== ASSESSMENTS MANAGEMENT =====================
// Get assessments for a course
$app->get('/courses/{course_id}/assessments', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM assessments WHERE course_id = ? ORDER BY name');
    $stmt->execute([$args['course_id']]);
    $assessments = $stmt->fetchAll();
    $response->getBody()->write(json_encode($assessments));
    return $response->withHeader('Content-Type', 'application/json');
});

// Create assessment
$app->post('/courses/{course_id}/assessments', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('INSERT INTO assessments (course_id, name, weight, max_mark) VALUES (?, ?, ?, ?)');
    $stmt->execute([$args['course_id'], $data['name'], $data['weight'], $data['max_mark']]);
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Update assessment
$app->put('/assessments/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $fields = [];
    $params = [];
    foreach (['name','weight','max_mark'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
    }
    $params[] = $args['id'];
    $sql = 'UPDATE assessments SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Delete assessment
$app->delete('/assessments/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('DELETE FROM assessments WHERE id = ?');
    $stmt->execute([$args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== MARKS MANAGEMENT =====================
// Get all marks for a course (with breakdown)
$app->get('/courses/{course_id}/marks', function (Request $request, Response $response, $args) use ($pdo) {
    // Get assessment marks
    $stmt = $pdo->prepare('
        SELECT am.*, e.student_id, u.full_name, u.matric_no, a.name as assessment_name, a.weight, a.max_mark
        FROM assessment_marks am 
        JOIN enrollments e ON am.enrollment_id = e.id
        JOIN users u ON e.student_id = u.id
        JOIN assessments a ON am.assessment_id = a.id
        WHERE e.course_id = ?
        ORDER BY u.full_name, a.name
    ');
    $stmt->execute([$args['course_id']]);
    $marks = $stmt->fetchAll();
    
    // Get final exam marks
    $stmt = $pdo->prepare('
        SELECT fem.*, e.student_id, u.full_name, u.matric_no
        FROM final_exam_marks fem
        JOIN enrollments e ON fem.enrollment_id = e.id
        JOIN users u ON e.student_id = u.id
        WHERE e.course_id = ?
    ');
    $stmt->execute([$args['course_id']]);
    $finalMarks = $stmt->fetchAll();
    
    $response->getBody()->write(json_encode([
        'assessment_marks' => $marks,
        'final_marks' => $finalMarks
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Add/Update assessment mark
$app->post('/enrollments/{enrollment_id}/assessment-marks', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    
    // Get old mark for comparison
    $stmt = $pdo->prepare('SELECT mark FROM assessment_marks WHERE enrollment_id = ? AND assessment_id = ?');
    $stmt->execute([$args['enrollment_id'], $data['assessment_id']]);
    $existing = $stmt->fetch();
    $old_mark = $existing ? $existing['mark'] : null;
    
    if ($existing) {
        // Update
        $stmt = $pdo->prepare('UPDATE assessment_marks SET mark = ? WHERE enrollment_id = ? AND assessment_id = ?');
        $stmt->execute([$data['mark'], $args['enrollment_id'], $data['assessment_id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES (?, ?, ?)');
        $stmt->execute([$args['enrollment_id'], $data['assessment_id'], $data['mark']]);
    }
    
    // Get enrollment and course info for notifications
    $stmt = $pdo->prepare('
        SELECT e.student_id, e.course_id, c.lecturer_id
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.id = ?
    ');
    $stmt->execute([$args['enrollment_id']]);
    $enrollment_info = $stmt->fetch();
    
    // Create notification for student
    createNotification($pdo, $enrollment_info['student_id'], 'Your assessment mark has been updated');
    
    // Create mark update notification for lecturer tracking
    createMarkUpdateNotification(
        $pdo,
        $enrollment_info['lecturer_id'],
        $enrollment_info['student_id'],
        $args['enrollment_id'],
        $data['assessment_id'],
        false, // not final exam
        $old_mark,
        $data['mark'],
        $data['reason'] ?? null
    );
    
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Add/Update final exam mark
$app->post('/enrollments/{enrollment_id}/final-mark', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    
    // Get old mark for comparison
    $stmt = $pdo->prepare('SELECT mark FROM final_exam_marks WHERE enrollment_id = ?');
    $stmt->execute([$args['enrollment_id']]);
    $existing = $stmt->fetch();
    $old_mark = $existing ? $existing['mark'] : null;
    
    if ($existing) {
        // Update
        $stmt = $pdo->prepare('UPDATE final_exam_marks SET mark = ? WHERE enrollment_id = ?');
        $stmt->execute([$data['mark'], $args['enrollment_id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO final_exam_marks (enrollment_id, mark) VALUES (?, ?)');
        $stmt->execute([$args['enrollment_id'], $data['mark']]);
    }
    
    // Get enrollment and course info for notifications
    $stmt = $pdo->prepare('
        SELECT e.student_id, e.course_id, c.lecturer_id
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.id = ?
    ');
    $stmt->execute([$args['enrollment_id']]);
    $enrollment_info = $stmt->fetch();
    
    // Create notification for student
    createNotification($pdo, $enrollment_info['student_id'], 'Your final exam mark has been updated');
    
    // Create mark update notification for lecturer tracking
    createMarkUpdateNotification(
        $pdo,
        $enrollment_info['lecturer_id'],
        $enrollment_info['student_id'],
        $args['enrollment_id'],
        null, // no assessment_id for final exam
        true, // is final exam
        $old_mark,
        $data['mark'],
        $data['reason'] ?? null
    );
    
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get final exam mark for enrollment
$app->get('/enrollments/{enrollment_id}/final-mark', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM final_exam_marks WHERE enrollment_id = ?');
    $stmt->execute([$args['enrollment_id']]);
    $finalMark = $stmt->fetch();
    
    if ($finalMark) {
        $response->getBody()->write(json_encode($finalMark));
    } else {
        $response->getBody()->write(json_encode(null));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Bulk upload marks via CSV (simplified - would need CSV parsing library)
$app->post('/courses/{course_id}/bulk-upload-marks', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $csvData = $data['csv_data']; // Assume CSV data is sent as string
    
    // Parse CSV and update marks (simplified implementation)
    $lines = explode("\n", $csvData);
    $header = str_getcsv(array_shift($lines));
    
    foreach ($lines as $line) {
        if (trim($line) === '') continue;
        $row = str_getcsv($line);
        $rowData = array_combine($header, $row);
        
        // Process each row (implementation depends on CSV format)
        // This is a simplified example
    }
    
    $response->getBody()->write(json_encode(['success' => true, 'message' => 'Bulk upload completed']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Export marks as CSV
$app->get('/courses/{course_id}/export-marks', function (Request $request, Response $response, $args) use ($pdo) {
    // Get all marks for the course
    $stmt = $pdo->prepare('
        SELECT u.full_name, u.matric_no, a.name as assessment_name, am.mark, a.max_mark
        FROM assessment_marks am
        JOIN enrollments e ON am.enrollment_id = e.id
        JOIN users u ON e.student_id = u.id
        JOIN assessments a ON am.assessment_id = a.id
        WHERE e.course_id = ?
        ORDER BY u.full_name
    ');
    $stmt->execute([$args['course_id']]);
    $marks = $stmt->fetchAll();
    
    // Convert to CSV format
    $csv = "Student Name,Matric No,Assessment,Mark,Max Mark\n";
    foreach ($marks as $mark) {
        $csv .= "{$mark['full_name']},{$mark['matric_no']},{$mark['assessment_name']},{$mark['mark']},{$mark['max_mark']}\n";
    }
    
    $response->getBody()->write($csv);
    return $response->withHeader('Content-Type', 'text/csv')
                   ->withHeader('Content-Disposition', 'attachment; filename="course_marks.csv"');
});

// Add missing students endpoint for courses
$app->get('/courses/{course_id}/students', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('
        SELECT u.id, u.full_name as name, u.matric_no, u.email
        FROM enrollments e 
        JOIN users u ON e.student_id = u.id 
        WHERE e.course_id = ? AND u.role_id = (SELECT id FROM roles WHERE name = "student")
        ORDER BY u.full_name
    ');
    $stmt->execute([$args['course_id']]);
    $students = $stmt->fetchAll();
    $response->getBody()->write(json_encode($students));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== PERFORMANCE ANALYTICS =====================
// Get course statistics and averages
$app->get('/courses/{course_id}/analytics', function (Request $request, Response $response, $args) use ($pdo) {
    // Get class averages per assessment
    $stmt = $pdo->prepare('
        SELECT a.name, a.max_mark, AVG(am.mark) as average_mark, COUNT(am.mark) as total_students
        FROM assessments a
        LEFT JOIN assessment_marks am ON a.id = am.assessment_id
        LEFT JOIN enrollments e ON am.enrollment_id = e.id
        WHERE a.course_id = ?
        GROUP BY a.id
    ');
    $stmt->execute([$args['course_id']]);
    $assessmentAverages = $stmt->fetchAll();
    
    // Get final exam average
    $stmt = $pdo->prepare('
        SELECT AVG(fem.mark) as average_final_mark, COUNT(fem.mark) as total_students
        FROM final_exam_marks fem
        JOIN enrollments e ON fem.enrollment_id = e.id
        WHERE e.course_id = ?
    ');
    $stmt->execute([$args['course_id']]);
    $finalAverage = $stmt->fetch();
    
    $response->getBody()->write(json_encode([
        'assessment_averages' => $assessmentAverages,
        'final_average' => $finalAverage
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get student ranking in course
$app->get('/courses/{course_id}/students/{student_id}/ranking', function (Request $request, Response $response, $args) use ($pdo) {
    // Calculate total marks for all students (simplified calculation)
    $stmt = $pdo->prepare('
        SELECT e.student_id, u.full_name,
               SUM(COALESCE(am.mark, 0) * a.weight / 100) + COALESCE(fem.mark * 0.3, 0) as total_score
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        LEFT JOIN assessment_marks am ON e.id = am.enrollment_id
        LEFT JOIN assessments a ON am.assessment_id = a.id
        LEFT JOIN final_exam_marks fem ON e.id = fem.enrollment_id
        WHERE e.course_id = ?
        GROUP BY e.student_id
        ORDER BY total_score DESC
    ');
    $stmt->execute([$args['course_id']]);
    $rankings = $stmt->fetchAll();
    
    // Find student position
    $position = 0;
    foreach ($rankings as $index => $rank) {
        if ($rank['student_id'] == $args['student_id']) {
            $position = $index + 1;
            break;
        }
    }
    
    $response->getBody()->write(json_encode([
        'position' => $position,
        'total_students' => count($rankings),
        'rankings' => $rankings
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== FEEDBACK AND REMARKS =====================
// Get feedback for a student
$app->get('/enrollments/{enrollment_id}/feedback', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM feedback_remarks WHERE enrollment_id = ? ORDER BY created_at DESC');
    $stmt->execute([$args['enrollment_id']]);
    $feedback = $stmt->fetchAll();
    $response->getBody()->write(json_encode($feedback));
    return $response->withHeader('Content-Type', 'application/json');
});

// Add feedback for a student
$app->post('/enrollments/{enrollment_id}/feedback', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('INSERT INTO feedback_remarks (enrollment_id, remark) VALUES (?, ?)');
    $stmt->execute([$args['enrollment_id'], $data['remark']]);
    
    // Notify student
    $stmt = $pdo->prepare('SELECT student_id FROM enrollments WHERE id = ?');
    $stmt->execute([$args['enrollment_id']]);
    $enrollment = $stmt->fetch();
    createNotification($pdo, $enrollment['student_id'], 'New feedback has been added to your course');
    
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== NOTIFICATIONS =====================
// Get notifications for a user
$app->get('/users/{user_id}/notifications', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
    $stmt->execute([$args['user_id']]);
    $notifications = $stmt->fetchAll();
    $response->getBody()->write(json_encode($notifications));
    return $response->withHeader('Content-Type', 'application/json');
});

// Mark notification as read
$app->put('/notifications/{id}/read', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?');
    $stmt->execute([$args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Create notification
$app->post('/users/{user_id}/notifications', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
    $stmt->execute([$args['user_id'], $data['message']]);
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== MARK UPDATE NOTIFICATIONS =====================
// Get mark update notifications for a lecturer
$app->get('/lecturers/{lecturer_id}/mark-notifications', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('
        SELECT 
            mun.*,
            s.full_name as student_name,
            s.matric_no as student_id,
            c.code as course_code,
            c.name as course_name,
            a.name as assessment_name,
            CASE 
                WHEN mun.is_final_exam = 1 THEN "final_exam"
                ELSE LOWER(REPLACE(a.name, " ", "_"))
            END as assessment_type
        FROM mark_update_notifications mun
        JOIN users s ON mun.student_id = s.id
        JOIN enrollments e ON mun.enrollment_id = e.id
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN assessments a ON mun.assessment_id = a.id
        WHERE mun.lecturer_id = ?
        ORDER BY mun.created_at DESC
    ');
    $stmt->execute([$args['lecturer_id']]);
    $notifications = $stmt->fetchAll();
    
    $response->getBody()->write(json_encode($notifications));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get courses for a lecturer (for filtering)
$app->get('/lecturers/{lecturer_id}/courses', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT id, code, name FROM courses WHERE lecturer_id = ? ORDER BY code');
    $stmt->execute([$args['lecturer_id']]);
    $courses = $stmt->fetchAll();
    
    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});

// Acknowledge a mark update notification
$app->put('/notifications/{id}/acknowledge', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('UPDATE mark_update_notifications SET acknowledged = 1 WHERE id = ?');
    $stmt->execute([$args['id']]);
    
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== ADVISOR NOTES =====================
// Get notes for a student by advisor
$app->get('/advisor-notes/{advisor_id}/{student_id}/{course_id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM advisor_notes WHERE advisor_id = ? AND student_id = ? AND course_id = ? ORDER BY created_at DESC');
    $stmt->execute([$args['advisor_id'], $args['student_id'], $args['course_id']]);
    $notes = $stmt->fetchAll();
    $response->getBody()->write(json_encode($notes));
    return $response->withHeader('Content-Type', 'application/json');
});
// Add note
$app->post('/advisor-notes', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('INSERT INTO advisor_notes (advisor_id, student_id, course_id, note) VALUES (?, ?, ?, ?)');
    $stmt->execute([$data['advisor_id'], $data['student_id'], $data['course_id'], $data['note']]);
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Update note
$app->put('/advisor-notes/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('UPDATE advisor_notes SET note = ? WHERE id = ?');
    $stmt->execute([$data['note'], $args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Delete note
$app->delete('/advisor-notes/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('DELETE FROM advisor_notes WHERE id = ?');
    $stmt->execute([$args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Add this test endpoint to debug the database
$app->get('/debug/users', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query('SELECT u.id, u.full_name, u.matric_no, u.staff_id, u.email, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id LIMIT 10');
        $users = $stmt->fetchAll();
        $response->getBody()->write(json_encode([
            'total_users' => count($users),
            'users' => $users
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));        
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Debug endpoint to list all advisors
$app->get('/debug/advisors', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query('
            SELECT u.id, u.staff_id, u.full_name, u.email, COUNT(a.student_id) as advisee_count
            FROM users u 
            LEFT JOIN advisors a ON u.id = a.advisor_id
            WHERE u.role_id = 4 
            GROUP BY u.id
            ORDER BY u.full_name
        ');
        $advisors = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'total_advisors' => count($advisors),
            'advisors' => $advisors
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Debug endpoint to show advisor-student relationships
$app->get('/debug/advisor-relationships', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query('
            SELECT 
                advisor.full_name as advisor_name,
                advisor.staff_id as advisor_staff_id,
                student.full_name as student_name,
                student.matric_no as student_matric_no
            FROM advisors a
            JOIN users advisor ON a.advisor_id = advisor.id
            JOIN users student ON a.student_id = student.id
            ORDER BY advisor.full_name, student.full_name
        ');
        $relationships = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'total_relationships' => count($relationships),
            'relationships' => $relationships
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// =============================================
// ADVISOR ENDPOINTS
// =============================================

// Get advisor's advisees with their performance data
$app->get('/advisors/{advisor_id}/advisees', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        
        // Simplified query to avoid complex subqueries that cause SQL errors
        $sql = "
            SELECT 
                u.id,
                u.full_name as name,
                u.matric_no as studentId,
                u.email,
                -- Determine academic year based on matric number pattern
                CASE 
                    WHEN u.matric_no LIKE '%2021%' THEN 4
                    WHEN u.matric_no LIKE '%2022%' THEN 3  
                    WHEN u.matric_no LIKE '%2023%' THEN 2
                    WHEN u.matric_no LIKE '%2024%' THEN 1
                    ELSE 1
                END as year,
                -- Determine program from matric number
                CASE 
                    WHEN u.matric_no LIKE 'CS%' THEN 'CS'
                    WHEN u.matric_no LIKE 'IS%' THEN 'IS'
                    WHEN u.matric_no LIKE 'SE%' THEN 'SE'
                    WHEN u.matric_no LIKE 'IT%' THEN 'IT'
                    ELSE 'CS'
                END as program
            FROM advisors a
            JOIN users u ON a.student_id = u.id
            WHERE a.advisor_id = ? AND u.role_id = (SELECT id FROM roles WHERE name = 'student')
            ORDER BY u.full_name
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$advisor_id]);
        $advisees = $stmt->fetchAll();
        
        // Calculate GPA and other fields for each advisee separately to avoid complex SQL
        foreach ($advisees as &$advisee) {
            // Get GPA calculation
            $gpa_sql = "
                SELECT AVG(
                    CASE 
                        WHEN total_marks >= 80 THEN 4.0
                        WHEN total_marks >= 75 THEN 3.7
                        WHEN total_marks >= 70 THEN 3.3
                        WHEN total_marks >= 65 THEN 3.0
                        WHEN total_marks >= 60 THEN 2.7
                        WHEN total_marks >= 55 THEN 2.3
                        WHEN total_marks >= 50 THEN 2.0
                        WHEN total_marks >= 45 THEN 1.7
                        WHEN total_marks >= 40 THEN 1.3
                        ELSE 0.0
                    END
                ) as gpa
                FROM (
                    SELECT 
                        COALESCE(
                            (SELECT SUM(am.mark * ass.weight / 100) 
                             FROM assessment_marks am 
                             JOIN assessments ass ON am.assessment_id = ass.id 
                             WHERE am.enrollment_id = e.id), 0
                        ) + 
                        COALESCE(
                            (SELECT fem.mark * 0.6 FROM final_exam_marks fem WHERE fem.enrollment_id = e.id), 0
                        ) as total_marks
                    FROM enrollments e 
                    WHERE e.student_id = ?
                ) as course_marks
            ";
            
            $gpa_stmt = $pdo->prepare($gpa_sql);
            $gpa_stmt->execute([$advisee['id']]);
            $gpa_result = $gpa_stmt->fetch();
            $advisee['gpa'] = (float) ($gpa_result['gpa'] ?? 0.0);
            
            // Get last meeting date
            $meeting_sql = "SELECT MAX(created_at) as lastMeeting FROM advisor_notes WHERE advisor_id = ? AND student_id = ?";
            $meeting_stmt = $pdo->prepare($meeting_sql);
            $meeting_stmt->execute([$advisor_id, $advisee['id']]);
            $meeting_result = $meeting_stmt->fetch();
            $advisee['lastMeeting'] = $meeting_result['lastMeeting'] ?? '2024-01-01';
            $advisee['lastMeetingType'] = 'Academic Review';
        }
          // Calculate status based on GPA and bottom 20% logic
        $advisee_count = count($advisees);
        $bottom_20_percent_count = max(1, ceil($advisee_count * 0.2)); // At least 1 student
        
        // Sort advisees by GPA to find bottom 20%
        $sorted_by_gpa = $advisees;
        usort($sorted_by_gpa, function($a, $b) {
            return $a['gpa'] <=> $b['gpa']; // Sort ascending (lowest first)
        });
        
        // Get the GPA threshold for bottom 20%
        $bottom_20_threshold = $advisee_count > 0 ? $sorted_by_gpa[$bottom_20_percent_count - 1]['gpa'] : 0.0;
        
        foreach ($advisees as &$advisee) {
            // At-risk if GPA < 2.0 OR in bottom 20%
            $is_low_gpa = $advisee['gpa'] < 2.0;
            $is_bottom_20 = $advisee['gpa'] <= $bottom_20_threshold;
            
            if ($is_low_gpa || $is_bottom_20) {
                $advisee['status'] = $advisee['gpa'] < 1.5 ? 'probation' : 'at-risk';
                $advisee['isAtRisk'] = true;
                $advisee['atRiskReason'] = [];
                if ($is_low_gpa) {
                    $advisee['atRiskReason'][] = 'GPA below 2.0';
                }
                if ($is_bottom_20) {
                    $advisee['atRiskReason'][] = 'Bottom 20% performance';
                }
            } elseif ($advisee['gpa'] >= 3.5) {
                $advisee['status'] = 'excellent';
                $advisee['isAtRisk'] = false;
                $advisee['atRiskReason'] = [];
            } else {
                $advisee['status'] = 'active';
                $advisee['isAtRisk'] = false;
                $advisee['atRiskReason'] = [];
            }
            
            // Convert GPA to float and year to int
            $advisee['gpa'] = (float) $advisee['gpa'];
            $advisee['year'] = (int) $advisee['year'];
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $advisees
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to fetch advisees: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Get detailed performance data for a specific advisee
$app->get('/advisors/{advisor_id}/advisees/{student_id}/performance', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $student_id = $args['student_id'];
        
        // Verify advisor-student relationship
        $stmt = $pdo->prepare("SELECT 1 FROM advisors WHERE advisor_id = ? AND student_id = ?");
        $stmt->execute([$advisor_id, $student_id]);
        
        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized access to student data'
            ]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
          // Get student's course performance with detailed breakdown
        $sql = "
            SELECT 
                c.id as course_id,
                c.code,
                c.name as course_name,
                c.semester,
                c.year,
                3 as credits,
                e.id as enrollment_id,
                COALESCE(fem.mark, 0) as final_exam_mark,
                50 as final_exam_max_mark,
                30 as final_exam_weight
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN final_exam_marks fem ON fem.enrollment_id = e.id
            WHERE e.student_id = ?
            ORDER BY c.year DESC, c.semester DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id]);
        $courses = $stmt->fetchAll();
        
        // For each course, get assessment details
        foreach ($courses as &$course) {
            // Get assessment marks with details
            $assessment_sql = "
                SELECT 
                    a.id as assessment_id,
                    a.name as assessment_name,
                    a.weight,
                    a.max_mark,
                    COALESCE(am.mark, 0) as student_mark
                FROM assessments a
                LEFT JOIN assessment_marks am ON (a.id = am.assessment_id AND am.enrollment_id = ?)
                WHERE a.course_id = ?
                ORDER BY a.name
            ";
            
            $assessment_stmt = $pdo->prepare($assessment_sql);
            $assessment_stmt->execute([$course['enrollment_id'], $course['course_id']]);
            $course['assessments'] = $assessment_stmt->fetchAll();
            
            // Calculate assessment totals (70% weight)
            $assessment_total_marks = 0;
            $assessment_max_marks = 0;
            $assessment_weighted_score = 0;
            
            foreach ($course['assessments'] as $assessment) {
                $assessment_total_marks += (float) $assessment['student_mark'];
                $assessment_max_marks += (float) $assessment['max_mark'];
                // Each assessment contributes to the 70% assessment portion
                $assessment_weighted_score += ((float) $assessment['student_mark'] / (float) $assessment['max_mark']) * (float) $assessment['weight'];
            }
            
            $course['assessment_total_marks'] = $assessment_total_marks;
            $course['assessment_max_marks'] = $assessment_max_marks;
            $course['assessment_percentage'] = $assessment_max_marks > 0 ? ($assessment_total_marks / $assessment_max_marks) * 100 : 0;
            $course['assessment_weighted_score'] = $assessment_weighted_score; // This should be out of 70
            
            // Calculate final exam contribution (30% weight)
            $final_exam_percentage = $course['final_exam_max_mark'] > 0 ? 
                ((float) $course['final_exam_mark'] / (float) $course['final_exam_max_mark']) * 100 : 0;
            $course['final_exam_percentage'] = $final_exam_percentage;
            $course['final_exam_weighted_score'] = ($final_exam_percentage / 100) * (float) $course['final_exam_weight'];
            
            // Calculate total course mark (Assessment 70% + Final Exam 30%)
            $total_weighted_score = $course['assessment_weighted_score'] + $course['final_exam_weighted_score'];
            $course['total_mark'] = $total_weighted_score;
            
            // Determine grade based on total mark
            if ($total_weighted_score >= 80) {
                $course['grade'] = 'A';
            } elseif ($total_weighted_score >= 75) {
                $course['grade'] = 'A-';
            } elseif ($total_weighted_score >= 70) {
                $course['grade'] = 'B+';
            } elseif ($total_weighted_score >= 65) {
                $course['grade'] = 'B';
            } elseif ($total_weighted_score >= 60) {
                $course['grade'] = 'B-';
            } elseif ($total_weighted_score >= 55) {
                $course['grade'] = 'C+';
            } elseif ($total_weighted_score >= 50) {
                $course['grade'] = 'C';
            } elseif ($total_weighted_score >= 45) {
                $course['grade'] = 'C-';
            } elseif ($total_weighted_score >= 40) {
                $course['grade'] = 'D';
            } else {
                $course['grade'] = 'F';
            }
            
            // Convert numeric values to proper types
            $course['final_exam_mark'] = (float) $course['final_exam_mark'];
            $course['final_exam_max_mark'] = (int) $course['final_exam_max_mark'];
            $course['final_exam_weight'] = (int) $course['final_exam_weight'];
            $course['total_mark'] = round($total_weighted_score, 2);
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to fetch student performance: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Add or update advisor notes for a student
$app->post('/advisors/{advisor_id}/advisees/{student_id}/notes', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $student_id = $args['student_id'];
        $data = $request->getParsedBody();
        
        // Verify advisor-student relationship
        $stmt = $pdo->prepare("SELECT 1 FROM advisors WHERE advisor_id = ? AND student_id = ?");
        $stmt->execute([$advisor_id, $student_id]);
        
        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized access to student data'
            ]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        
        $sql = "INSERT INTO advisor_notes (advisor_id, student_id, course_id, note) VALUES (?, ?, ?, ?)";        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $advisor_id,
            $student_id,
            $data['course_id'] ?? 1, // Default course if not specified
            $data['note']
        ]);
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Note added successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to add note: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// =============================================
// MEETINGS ENDPOINTS
// =============================================

// Create a new meeting
$app->post('/advisors/{advisor_id}/meetings', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $data = $request->getParsedBody();
        
        // Verify advisor-student relationship
        $stmt = $pdo->prepare("SELECT 1 FROM advisors WHERE advisor_id = ? AND student_id = ?");
        $stmt->execute([$advisor_id, $data['student_id']]);
        
        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized access to student data'
            ]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
          $sql = "INSERT INTO meetings (advisor_id, student_id, title, meeting_date, meeting_time, duration, location, meeting_link, meeting_type, status, agenda, notes, action_items) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $advisor_id,
            $data['student_id'],
            $data['title'] ?? 'Advisor Meeting',
            $data['meeting_date'],
            $data['meeting_time'],
            $data['duration'] ?? 60,
            $data['location'] ?? '',
            $data['meeting_link'] ?? '',
            $data['meeting_type'] ?? 'academic',
            $data['status'] ?? 'scheduled',
            $data['agenda'] ?? '',
            $data['notes'] ?? '',
            $data['action_items'] ?? ''
        ]);
        
        $meeting_id = $pdo->lastInsertId();
        
        // Create notification for student
        createNotification($pdo, $data['student_id'], "New meeting scheduled with your advisor on {$data['meeting_date']} at {$data['meeting_time']}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Meeting scheduled successfully',
            'meeting_id' => $meeting_id
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to schedule meeting: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Get all meetings for an advisor
$app->get('/advisors/{advisor_id}/meetings', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $student_id = $request->getQueryParams()['student_id'] ?? null;
        
        $sql = "
            SELECT 
                m.*,
                u.full_name as student_name,
                u.matric_no as student_matric
            FROM meetings m
            JOIN users u ON m.student_id = u.id
            WHERE m.advisor_id = ?
        ";
        
        $params = [$advisor_id];
        
        if ($student_id) {
            $sql .= " AND m.student_id = ?";
            $params[] = $student_id;
        }
        
        $sql .= " ORDER BY m.meeting_date DESC, m.meeting_time DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $meetings = $stmt->fetchAll();
        
        // Convert to frontend format
        foreach ($meetings as &$meeting) {
            $meeting['student'] = [
                'id' => $meeting['student_id'],
                'name' => $meeting['student_name'],
                'studentId' => $meeting['student_matric']
            ];
            $meeting['date'] = $meeting['meeting_date'];
            $meeting['time'] = $meeting['meeting_time'];
            $meeting['type'] = $meeting['meeting_type'];
            $meeting['actionItems'] = $meeting['action_items'];
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $meetings
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to fetch meetings: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Update a meeting
$app->put('/advisors/{advisor_id}/meetings/{meeting_id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $meeting_id = $args['meeting_id'];
        $data = $request->getParsedBody();
        
        // Verify the meeting belongs to this advisor
        $stmt = $pdo->prepare("SELECT student_id FROM meetings WHERE id = ? AND advisor_id = ?");
        $stmt->execute([$meeting_id, $advisor_id]);
        $meeting = $stmt->fetch();
        
        if (!$meeting) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Meeting not found or unauthorized'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Build update query dynamically
        $fields = [];
        $params = [];
        
        $allowed_fields = ['title', 'meeting_date', 'meeting_time', 'duration', 'location', 'meeting_link', 'meeting_type', 'status', 'agenda', 'notes', 'action_items', 'next_meeting_date'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'action_items') {
                    $fields[] = "action_items = ?";
                    $params[] = $data[$field];
                } else {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'No valid fields to update'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        $params[] = $meeting_id;
        $sql = "UPDATE meetings SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Create notification for student if status changed to completed
        if (isset($data['status']) && $data['status'] === 'completed') {
            createNotification($pdo, $meeting['student_id'], "Meeting with your advisor has been completed. Notes and action items have been added.");
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Meeting updated successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to update meeting: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Delete a meeting
$app->delete('/advisors/{advisor_id}/meetings/{meeting_id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $meeting_id = $args['meeting_id'];
        
        // Verify the meeting belongs to this advisor
        $stmt = $pdo->prepare("SELECT student_id FROM meetings WHERE id = ? AND advisor_id = ?");
        $stmt->execute([$meeting_id, $advisor_id]);
        $meeting = $stmt->fetch();
        
        if (!$meeting) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Meeting not found or unauthorized'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete the meeting
        $stmt = $pdo->prepare("DELETE FROM meetings WHERE id = ?");
        $stmt->execute([$meeting_id]);
        
        // Create notification for student
        createNotification($pdo, $meeting['student_id'], "A scheduled meeting with your advisor has been cancelled.");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Meeting deleted successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to delete meeting: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== REPORTS =====================
// Generate advisor report for selected students
$app->post('/advisors/{advisor_id}/reports/generate', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $body = $request->getParsedBody();
        
        $student_ids = $body['students'] ?? [];
        $report_type = $body['type'] ?? 'comprehensive';
        $sections = $body['sections'] ?? [];
        $period = $body['period'] ?? 'current';
        $format = $body['format'] ?? 'pdf';
        
        if (empty($student_ids)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'No students selected'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Get student data for the report
        $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
        $sql = "
            SELECT 
                u.id,
                u.full_name as name,
                u.matric_no as student_id,
                u.email,
                CASE 
                    WHEN u.matric_no LIKE '%2021%' THEN 4
                    WHEN u.matric_no LIKE '%2022%' THEN 3  
                    WHEN u.matric_no LIKE '%2023%' THEN 2
                    WHEN u.matric_no LIKE '%2024%' THEN 1
                    ELSE 1
                END as year,
                CASE 
                    WHEN u.matric_no LIKE 'CS%' THEN 'Computer Science'
                    WHEN u.matric_no LIKE 'IS%' THEN 'Information Systems'
                    WHEN u.matric_no LIKE 'SE%' THEN 'Software Engineering'
                    WHEN u.matric_no LIKE 'IT%' THEN 'Information Technology'
                    ELSE 'Computer Science'
                END as program
            FROM users u
            WHERE u.id IN ($placeholders)
            ORDER BY u.full_name
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($student_ids);
        $students = $stmt->fetchAll();
        
        // Calculate performance data for each student
        foreach ($students as &$student) {
            // Get GPA
            $gpa_sql = "
                SELECT AVG(
                    CASE 
                        WHEN total_marks >= 80 THEN 4.0
                        WHEN total_marks >= 75 THEN 3.7
                        WHEN total_marks >= 70 THEN 3.3
                        WHEN total_marks >= 65 THEN 3.0
                        WHEN total_marks >= 60 THEN 2.7
                        WHEN total_marks >= 55 THEN 2.3
                        WHEN total_marks >= 50 THEN 2.0
                        WHEN total_marks >= 45 THEN 1.7
                        WHEN total_marks >= 40 THEN 1.3
                        ELSE 0.0
                    END
                ) as gpa
                FROM (
                    SELECT 
                        COALESCE(
                            (SELECT SUM(am.mark * ass.weight / 100) 
                             FROM assessment_marks am 
                             JOIN assessments ass ON am.assessment_id = ass.id 
                             WHERE am.enrollment_id = e.id), 0
                        ) + 
                        COALESCE(
                            (SELECT fem.mark * 0.6 FROM final_exam_marks fem WHERE fem.enrollment_id = e.id), 0
                        ) as total_marks
                    FROM enrollments e 
                    WHERE e.student_id = ?
                ) as course_marks
            ";
            
            $gpa_stmt = $pdo->prepare($gpa_sql);
            $gpa_stmt->execute([$student['id']]);
            $gpa_result = $gpa_stmt->fetch();
            $student['gpa'] = round((float) ($gpa_result['gpa'] ?? 0.0), 2);
            
            // Get course performance if requested
            if (isset($sections['courseBreakdown']) && $sections['courseBreakdown']) {
                $course_sql = "
                    SELECT 
                        c.name as course_name,
                        c.code as course_code,
                        COALESCE(
                            (SELECT SUM(am.mark * ass.weight / 100) 
                             FROM assessment_marks am 
                             JOIN assessments ass ON am.assessment_id = ass.id 
                             WHERE am.enrollment_id = e.id), 0
                        ) + 
                        COALESCE(
                            (SELECT fem.mark * 0.6 FROM final_exam_marks fem WHERE fem.enrollment_id = e.id), 0
                        ) as total_marks
                    FROM enrollments e
                    JOIN courses c ON e.course_id = c.id
                    WHERE e.student_id = ?
                    ORDER BY c.name
                ";
                
                $course_stmt = $pdo->prepare($course_sql);
                $course_stmt->execute([$student['id']]);
                $student['courses'] = $course_stmt->fetchAll();
            }
            
            // Get advisor notes if requested
            if (isset($sections['meetingHistory']) && $sections['meetingHistory']) {
                $notes_sql = "
                    SELECT notes, created_at
                    FROM advisor_notes 
                    WHERE advisor_id = ? AND student_id = ?
                    ORDER BY created_at DESC
                    LIMIT 10
                ";
                
                $notes_stmt = $pdo->prepare($notes_sql);
                $notes_stmt->execute([$advisor_id, $student['id']]);
                $student['meeting_history'] = $notes_stmt->fetchAll();
            }
        }
          // Calculate summary statistics
        $total_gpa = 0;
        $at_risk_count = 0;
        $high_performers_count = 0;
        $recommendations = [];
        
        foreach ($students as $student) {
            $total_gpa += $student['gpa'];
            if ($student['gpa'] < 2.0) {
                $at_risk_count++;
            } elseif ($student['gpa'] >= 3.5) {
                $high_performers_count++;
            }
        }
        
        $average_gpa = count($students) > 0 ? $total_gpa / count($students) : 0;
        
        // Generate recommendations based on data
        if ($at_risk_count > 0) {
            $recommendations[] = "Schedule immediate intervention meetings with {$at_risk_count} at-risk students to develop academic improvement plans.";
        }
        
        if ($high_performers_count > 0) {
            $recommendations[] = "Consider recommending {$high_performers_count} high-performing students for advanced courses or research opportunities.";
        }
        
        if ($average_gpa < 2.5) {
            $recommendations[] = "Overall advisee performance is below expectations. Consider implementing group study sessions or tutoring programs.";
        } elseif ($average_gpa > 3.0) {
            $recommendations[] = "Strong overall performance. Continue current advising strategies and consider challenging advisees with advanced opportunities.";
        }
        
        if (count($students) > 10) {
            $recommendations[] = "With a large advisee load, consider prioritizing attention to at-risk and high-performing students for personalized guidance.";
        }
        
        // Save report record
        $report_title = ucfirst($report_type) . " Report - " . date('M d, Y');
        $insert_sql = "
            INSERT INTO advisor_reports (advisor_id, title, type, student_count, format, report_data, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $report_data = json_encode([
            'students' => $students,
            'summary' => [
                'average_gpa' => round($average_gpa, 2),
                'at_risk_count' => $at_risk_count,
                'high_performers_count' => $high_performers_count,
                'total_students' => count($students)
            ],
            'recommendations' => $recommendations,
            'sections' => $sections,
            'period' => $period,
            'generated_at' => date('Y-m-d H:i:s')
        ]);
        
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([
            $advisor_id,
            $report_title,
            $report_type,
            count($students),
            $format,
            $report_data
        ]);
        
        $report_id = $pdo->lastInsertId();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'report_id' => $report_id,
            'download_url' => "/advisors/{$advisor_id}/reports/{$report_id}/download",
            'data' => $students
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to generate report: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Get recent reports for advisor
$app->get('/advisors/{advisor_id}/reports/recent', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        
        $sql = "
            SELECT id, title, type, student_count, format, created_at
            FROM advisor_reports
            WHERE advisor_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$advisor_id]);
        $reports = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $reports
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to fetch reports: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// PDF Report Generator Function
function generatePDFReport($report) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Create new PDF document
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Course Mark Management System');
    $pdf->SetAuthor('Academic Advisor');
    $pdf->SetTitle($report['title']);
    $pdf->SetSubject('Advisee Report');
    
    // Set margins
    $pdf->SetMargins(20, 30, 20);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(15);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);
    
    // Add a page
    $pdf->AddPage();
    
    // Parse report data
    $reportData = json_decode($report['report_data'], true);
    
    // Header section
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 15, $report['title'], 0, 1, 'C');
    $pdf->Ln(5);
    
    // Report metadata
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 8, 'Generated on: ' . date('F j, Y \a\t g:i A', strtotime($report['created_at'])), 0, 1, 'R');
    $pdf->Cell(0, 8, 'Report Type: ' . ucfirst($report['type']), 0, 1, 'R');
    $pdf->Cell(0, 8, 'Students Included: ' . $report['student_count'], 0, 1, 'R');
    $pdf->Ln(10);
    
    // Summary section
    if (isset($reportData['summary'])) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 10, 'Summary', 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        $summary = $reportData['summary'];
        
        // Summary statistics
        $pdf->Cell(45, 8, 'Average GPA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, number_format($summary['average_gpa'], 2), 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(45, 8, 'At-Risk Students:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(220, 53, 69); // Red color for at-risk
        $pdf->Cell(0, 8, $summary['at_risk_count'], 0, 1, 'L');
        
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(45, 8, 'High Performers:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(40, 167, 69); // Green color for high performers
        $pdf->Cell(0, 8, $summary['high_performers_count'], 0, 1, 'L');
        
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Ln(10);
    }
    
    // Student details section
    if (isset($reportData['students']) && !empty($reportData['students'])) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Student Details', 0, 1, 'L');
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Cell(50, 10, 'Student Name', 1, 0, 'C', 1);
        $pdf->Cell(25, 10, 'Student ID', 1, 0, 'C', 1);
        $pdf->Cell(20, 10, 'GPA', 1, 0, 'C', 1);
        $pdf->Cell(25, 10, 'Status', 1, 0, 'C', 1);
        $pdf->Cell(50, 10, 'Latest Course', 1, 1, 'C', 1);
        
        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        foreach ($reportData['students'] as $student) {
            // Determine status color
            $statusColor = [51, 51, 51]; // Default
            if (isset($student['gpa']) && $student['gpa'] < 2.0) {
                $statusColor = [220, 53, 69]; // Red for at-risk
            } elseif (isset($student['gpa']) && $student['gpa'] >= 3.5) {
                $statusColor = [40, 167, 69]; // Green for high performers
            }
            
            $pdf->Cell(50, 8, $student['full_name'] ?? 'N/A', 1, 0, 'L');
            $pdf->Cell(25, 8, $student['student_id'] ?? 'N/A', 1, 0, 'C');
            $pdf->Cell(20, 8, isset($student['gpa']) ? number_format($student['gpa'], 2) : 'N/A', 1, 0, 'C');
            
            // Status cell with color
            $pdf->SetTextColor($statusColor[0], $statusColor[1], $statusColor[2]);
            $status = 'Regular';
            if (isset($student['gpa'])) {
                if ($student['gpa'] < 2.0) $status = 'At-Risk';
                elseif ($student['gpa'] >= 3.5) $status = 'High Performer';
            }
            $pdf->Cell(25, 8, $status, 1, 0, 'C');
            
            $pdf->SetTextColor(51, 51, 51);
            $pdf->Cell(50, 8, $student['latest_course'] ?? 'N/A', 1, 1, 'L');
        }
        $pdf->Ln(10);
    }
    
    // Recommendations section
    if (isset($reportData['recommendations']) && !empty($reportData['recommendations'])) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Recommendations', 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        foreach ($reportData['recommendations'] as $index => $recommendation) {
            $pdf->Cell(10, 8, ($index + 1) . '.', 0, 0, 'L');
            $pdf->MultiCell(0, 8, $recommendation, 0, 'L');
            $pdf->Ln(2);
        }
    }
    
    // Footer
    $pdf->SetY(-30);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 10, 'Generated by Course Mark Management System', 0, 0, 'C');
    
    return $pdf->Output('', 'S'); // Return PDF as string
}

// Download report
$app->get('/advisors/{advisor_id}/reports/{report_id}/download', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $advisor_id = $args['advisor_id'];
        $report_id = $args['report_id'];
        
        $sql = "SELECT * FROM advisor_reports WHERE id = ? AND advisor_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$report_id, $advisor_id]);
        $report = $stmt->fetch();
        
        if (!$report) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Report not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Generate PDF
        $pdfContent = generatePDFReport($report);
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $report['title']) . '.pdf';
        
        $response->getBody()->write($pdfContent);
        return $response->withHeader('Content-Type', 'application/pdf')
                        ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                        ->withHeader('Content-Length', strlen($pdfContent));
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Failed to download report: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();
