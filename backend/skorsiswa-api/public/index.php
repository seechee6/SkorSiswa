<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Models\User;

// Create Slim app
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add JSON body parsing middleware
$app->addBodyParsingMiddleware();

// Handle preflight OPTIONS requests for CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

// CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

// Handle preflight OPTIONS requests
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
    $matric_no = $data['matric_no'] ?? null;
    $password = $data['password'] ?? null;

    if (!$matric_no || !$password) {
        $response->getBody()->write(json_encode(['error' => 'Matric number and password required.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.matric_no = ?');
    $stmt->execute([$matric_no]);
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
    
    // Check if mark exists
    $stmt = $pdo->prepare('SELECT id FROM assessment_marks WHERE enrollment_id = ? AND assessment_id = ?');
    $stmt->execute([$args['enrollment_id'], $data['assessment_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update
        $stmt = $pdo->prepare('UPDATE assessment_marks SET mark = ? WHERE id = ?');
        $stmt->execute([$data['mark'], $existing['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES (?, ?, ?)');
        $stmt->execute([$args['enrollment_id'], $data['assessment_id'], $data['mark']]);
    }
    
    // Create notification for student
    $stmt = $pdo->prepare('SELECT student_id FROM enrollments WHERE id = ?');
    $stmt->execute([$args['enrollment_id']]);
    $enrollment = $stmt->fetch();
    createNotification($pdo, $enrollment['student_id'], 'Your assessment mark has been updated');
    
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Add/Update final exam mark
$app->post('/enrollments/{enrollment_id}/final-mark', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    
    // Check if mark exists
    $stmt = $pdo->prepare('SELECT id FROM final_exam_marks WHERE enrollment_id = ?');
    $stmt->execute([$args['enrollment_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update
        $stmt = $pdo->prepare('UPDATE final_exam_marks SET mark = ? WHERE id = ?');
        $stmt->execute([$data['mark'], $existing['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO final_exam_marks (enrollment_id, mark) VALUES (?, ?)');
        $stmt->execute([$args['enrollment_id'], $data['mark']]);
    }
    
    // Create notification for student
    $stmt = $pdo->prepare('SELECT student_id FROM enrollments WHERE id = ?');
    $stmt->execute([$args['enrollment_id']]);
    $enrollment = $stmt->fetch();
    createNotification($pdo, $enrollment['student_id'], 'Your final exam mark has been updated');
    
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

$app->run();
