<?php
error_log("API Request: " . $_SERVER['REQUEST_URI']);
require __DIR__ . '/../vendor/autoload.php';

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

// CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    
    // Get the origin from the request
    $origin = $request->getHeaderLine('Origin');
    
    // Allow any localhost origin
    $allowedOrigin = '*';
    if (preg_match('/^https?:\/\/localhost(:\d+)?$/', $origin)) {
        $allowedOrigin = $origin;
    }
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

// Handle preflight OPTIONS requests for CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    // Get the origin from the request
    $origin = $request->getHeaderLine('Origin');
    
    // Allow any localhost origin
    $allowedOrigin = '*';
    if (preg_match('/^https?:\/\/localhost(:\d+)?$/', $origin)) {
        $allowedOrigin = $origin;
    }
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withStatus(204);
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

// Debug endpoint to check if users exist
$app->get('/debug-users', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT id, matric_no, staff_id, full_name, email, role_id FROM users LIMIT 10');
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'user_count' => count($users),
            'users' => $users
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            'error' => 'Database error: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Login endpoint - supports both matric_no and staff_id
$app->post('/login', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $identifier = $data['matric_no'] ?? null;
    $password = $data['password'] ?? null;

    // Debug log
    error_log("Login attempt with identifier: " . $identifier);

    if (!$identifier || !$password) {
        $response->getBody()->write(json_encode(['error' => 'Matric number/Staff ID and password required.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Try to find user by matric_no first (for students), then by staff_id (for lecturers/staff)
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.matric_no = ? OR u.staff_id = ?');
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    // Debug log
    if ($user) {
        error_log("User found: " . $user['full_name']);
    } else {
        error_log("No user found with identifier: " . $identifier);
    }

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        logSystemActivity($pdo, $user['id'], "User logged in");
        $response->getBody()->write(json_encode(['success' => true, 'user' => $user]));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        if ($user) {
            error_log("Password verification failed for: " . $user['full_name']);
        }
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
    
    try {
        // Get the current course data before updating
        $stmt = $pdo->prepare('SELECT c.*, u.full_name as current_lecturer_name FROM courses c LEFT JOIN users u ON c.lecturer_id = u.id WHERE c.id = ?');
        $stmt->execute([$args['id']]);
        $currentCourse = $stmt->fetch();
        
        if (!$currentCourse) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Course not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $fields = [];
        $params = [];
        foreach (['code','name','lecturer_id','semester','year'] as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }
        $params[] = $args['id'];
        $sql = 'UPDATE courses SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Log lecturer assignment changes
        if (isset($data['lecturer_id'])) {
            $newLecturerId = $data['lecturer_id'];
            $currentLecturerId = $currentCourse['lecturer_id'];
            
            if ($newLecturerId != $currentLecturerId) {
                if ($newLecturerId) {
                    // Get new lecturer info
                    $stmt = $pdo->prepare('SELECT full_name, staff_id FROM users WHERE id = ?');
                    $stmt->execute([$newLecturerId]);
                    $newLecturer = $stmt->fetch();
                    
                    if ($newLecturer) {
                        $action = "Assigned lecturer {$newLecturer['full_name']} ({$newLecturer['staff_id']}) to course {$currentCourse['code']} - {$currentCourse['name']}";
                        logSystemActivity($pdo, $newLecturerId, $action);
                    }
                } else {
                    // Lecturer was unassigned
                    $action = "Unassigned lecturer from course {$currentCourse['code']} - {$currentCourse['name']}";
                    logSystemActivity($pdo, $currentLecturerId, $action);
                }
            }
        }
        
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        error_log("Course update error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Database error']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
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
        SELECT e.student_id, e.course_id, c.lecturer_id, c.code, c.name, a.name as assessment_name
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        JOIN assessments a ON a.id = ?
        WHERE e.id = ?
    ');
    $stmt->execute([$data['assessment_id'], $args['enrollment_id']]);
    $enrollment_info = $stmt->fetch();
    
    // Create notification for student
    $message = "Your mark for " . $enrollment_info['assessment_name'] . " in " . $enrollment_info['code'] . " - " . $enrollment_info['name'] . " has been " . ($existing ? "updated" : "posted") . ".";
    createNotification($pdo, $enrollment_info['student_id'], $message);
    
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
        SELECT e.student_id, e.course_id, c.lecturer_id, c.code, c.name
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.id = ?
    ');
    $stmt->execute([$args['enrollment_id']]);
    $enrollment_info = $stmt->fetch();
    
    // Create notification for student
    $message = "Your final exam mark for " . $enrollment_info['code'] . " - " . $enrollment_info['name'] . " has been " . ($existing ? "updated" : "posted") . ".";
    createNotification($pdo, $enrollment_info['student_id'], $message);
    
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

// Test endpoint to manually create a notification (for demonstration)
$app->post('/test/create-notification', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    
    try {
        createNotification($pdo, $data['user_id'], $data['message']);
        
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Notification created']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Failed to create notification: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Include student routes
require __DIR__ . '/../src/routes/student.php';

// Simple test endpoint for debugging
$app->get('/test', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['message' => 'Backend is working!', 'timestamp' => date('Y-m-d H:i:s')]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== ADMIN USER MANAGEMENT =====================
// Get all users (Admin only)
$app->get('/api/admin/users', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query('
            SELECT u.id, u.full_name as name, u.matric_no, u.staff_id, u.email, 
                   r.name as role, u.created_at, u.status
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            ORDER BY u.created_at DESC
        ');
        $users = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Create new user (Admin only)
$app->post('/api/admin/users', function (Request $request, Response $response) use ($pdo) {
    try {
        $data = $request->getParsedBody();
        
        // Get role ID from role name
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
        $stmt->execute([$data['role']]);
        $role = $stmt->fetch();
        
        if (!$role) {
            $response->getBody()->write(json_encode(['error' => 'Invalid role']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert user based on role
        if ($data['role'] === 'student') {
            $stmt = $pdo->prepare('
                INSERT INTO users (full_name, matric_no, email, password_hash, role_id) 
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$data['name'], $data['matric_no'], $data['email'], $hashedPassword, $role['id']]);
        } else {
            // For non-students, use staff_id (can be generated or provided)
            $staff_id = isset($data['staff_id']) ? $data['staff_id'] : 'STAFF' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare('
                INSERT INTO users (full_name, staff_id, email, password_hash, role_id) 
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$data['name'], $staff_id, $data['email'], $hashedPassword, $role['id']]);
        }
        
        $userId = $pdo->lastInsertId();
        logSystemActivity($pdo, $userId, 'User created by admin');
        
        $response->getBody()->write(json_encode(['success' => true, 'id' => $userId]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Update user (Admin only)
$app->put('/api/admin/users/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $data = $request->getParsedBody();
        $userId = $args['id'];
        
        // Get role ID from role name
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
        $stmt->execute([$data['role']]);
        $role = $stmt->fetch();
        
        if (!$role) {
            $response->getBody()->write(json_encode(['error' => 'Invalid role']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        $stmt = $pdo->prepare('
            UPDATE users 
            SET full_name = ?, email = ?, role_id = ? 
            WHERE id = ?
        ');
        $stmt->execute([$data['name'], $data['email'], $role['id'], $userId]);
        
        logSystemActivity($pdo, $userId, 'User updated by admin');
        
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Toggle user status (Admin only) - Since there's no is_active column, we'll just return success
$app->patch('/api/admin/users/{id}/status', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $userId = $args['id'];
        
        // For now, just log the action since there's no is_active column in the database
        logSystemActivity($pdo, $userId, 'User status toggle requested by admin');
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User status feature not yet implemented in database schema'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Deactivate user (Admin only) - Actually update the user status to inactive
$app->patch('/api/admin/users/{id}/deactivate', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $userId = $args['id'];
        
        // Check if the user exists and get current status
        $stmt = $pdo->prepare('SELECT id, full_name, matric_no, staff_id, status FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        if ($user['status'] === 'inactive') {
            $response->getBody()->write(json_encode(['error' => 'User is already inactive']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Update user status to inactive
        $stmt = $pdo->prepare('UPDATE users SET status = "inactive" WHERE id = ?');
        $stmt->execute([$userId]);
        
        // Log the action
        logSystemActivity($pdo, $userId, 'User deactivated by admin');
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User deactivated successfully',
            'user' => [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'identifier' => $user['matric_no'] ?: $user['staff_id'],
                'status' => 'inactive'
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Reactivate user (Admin only) - Update the user status back to active
$app->patch('/api/admin/users/{id}/reactivate', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $userId = $args['id'];
        
        // Check if the user exists and get current status
        $stmt = $pdo->prepare('SELECT id, full_name, matric_no, staff_id, status FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        if ($user['status'] === 'active') {
            $response->getBody()->write(json_encode(['error' => 'User is already active']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Update user status to active
        $stmt = $pdo->prepare('UPDATE users SET status = "active" WHERE id = ?');
        $stmt->execute([$userId]);
        
        // Log the action
        logSystemActivity($pdo, $userId, 'User reactivated by admin');
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User reactivated successfully',
            'user' => [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'identifier' => $user['matric_no'] ?: $user['staff_id'],
                'status' => 'active'
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Reset user password (Admin only)
// Set new password for user (admin)
$app->put('/api/admin/users/{id}/password', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $userId = $args['id'];
        $data = $request->getParsedBody();
        
        if (!isset($data['new_password']) || empty($data['new_password'])) {
            $response->getBody()->write(json_encode(['error' => 'New password is required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        if (strlen($data['new_password']) < 6) {
            $response->getBody()->write(json_encode(['error' => 'Password must be at least 6 characters long']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Get user information for logging
        $stmt = $pdo->prepare('SELECT full_name, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Hash the new password
        $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        
        // Update the password
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hashedPassword, $userId]);
        
        // Log the activity
        logSystemActivity($pdo, $userId, "Password updated by admin for user: {$user['full_name']} ({$user['email']})");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Password updated successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        error_log("Password update error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to update password']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Legacy reset-password endpoint (kept for backward compatibility)
$app->post('/api/admin/users/{id}/reset-password', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $userId = $args['id'];
        
        // Generate a temporary password
        $tempPassword = 'temp' . rand(1000, 9999);
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hashedPassword, $userId]);
        
        logSystemActivity($pdo, $userId, 'Password reset by admin');
        
        // In a real system, you'd send an email here
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Password reset successful',
            'temp_password' => $tempPassword // Remove this in production
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');    }
});

// Admin change own password
$app->put('/api/admin/change-my-password', function (Request $request, Response $response) use ($pdo) {
    try {
        $data = $request->getParsedBody();
        
        if (!isset($data['current_password']) || !isset($data['new_password'])) {
            $response->getBody()->write(json_encode(['error' => 'Current password and new password are required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        if (strlen($data['new_password']) < 6) {
            $response->getBody()->write(json_encode(['error' => 'New password must be at least 6 characters long']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // For this example, we'll use a simple session check
        // In a real app, you'd get the user ID from the session/JWT token
        // For now, let's assume we get it from a header or find the admin user
          // Get admin user (assuming there's only one admin for this demo)
        $stmt = $pdo->prepare('SELECT u.id, u.full_name, u.password_hash FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = "admin" LIMIT 1');
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if (!$admin) {
            $response->getBody()->write(json_encode(['error' => 'Admin user not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Verify current password
        if (!password_verify($data['current_password'], $admin['password_hash'])) {
            $response->getBody()->write(json_encode(['error' => 'Current password is incorrect']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Check if new password is different from current
        if (password_verify($data['new_password'], $admin['password_hash'])) {
            $response->getBody()->write(json_encode(['error' => 'New password must be different from current password']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Hash the new password
        $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        
        // Update the password
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hashedPassword, $admin['id']]);
        
        // Log the activity
        logSystemActivity($pdo, $admin['id'], "Admin {$admin['full_name']} changed their own password");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Password changed successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        error_log("Admin password change error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to change password']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Get current admin info
$app->get('/api/admin/current-admin', function (Request $request, Response $response) use ($pdo) {
    try {
        // Get admin user (assuming there's only one admin for this demo)
        $stmt = $pdo->prepare('SELECT u.id, u.full_name, u.email FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = "admin" LIMIT 1');
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if (!$admin) {
            $response->getBody()->write(json_encode(['error' => 'Admin user not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
          $response->getBody()->write(json_encode($admin));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        error_log("Get current admin error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to get admin info']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Delete user (Admin only)
$app->delete('/api/admin/users/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $userId = $args['id'];
        
        // Get user information before deletion for logging
        $stmt = $pdo->prepare('SELECT full_name, email, role_id FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Check if user has associated data that would prevent deletion
        // Check enrollments
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM enrollments WHERE student_id = ?');
        $stmt->execute([$userId]);
        $enrollmentCount = $stmt->fetch()['count'];
        
        // Check if user is assigned as lecturer to any courses
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM courses WHERE lecturer_id = ?');
        $stmt->execute([$userId]);
        $courseCount = $stmt->fetch()['count'];
        
        if ($enrollmentCount > 0 || $courseCount > 0) {
            $response->getBody()->write(json_encode([
                'error' => 'Cannot delete user. User has associated enrollments or courses.'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete the user
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        
        // Log the deletion
        logSystemActivity($pdo, $userId, "User deleted by admin: {$user['full_name']} ({$user['email']})");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        error_log("User deletion error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Failed to delete user']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== ADMIN COURSE MANAGEMENT =====================
// Get all courses (admin view)
$app->get('/api/admin/courses', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query('SELECT c.*, u.full_name AS lecturer_name FROM courses c LEFT JOIN users u ON c.lecturer_id = u.id ORDER BY c.code');
        $courses = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode($courses));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Create new course (admin only)
$app->post('/api/admin/courses', function (Request $request, Response $response) use ($pdo) {
    try {
        $data = $request->getParsedBody();
        
        $stmt = $pdo->prepare('INSERT INTO courses (code, name, lecturer_id, semester, year) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['code'],
            $data['name'],
            $data['lecturer_id'] ?? null,
            $data['semester'],
            $data['year']
        ]);
        
        $courseId = $pdo->lastInsertId();
        
        // Log the activity
        logSystemActivity($pdo, 1, "Created course: {$data['code']} - {$data['name']}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Course created successfully',
            'course_id' => $courseId
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Update course (admin only)
$app->put('/api/admin/courses/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $courseId = $args['id'];
        $data = $request->getParsedBody();
        
        $stmt = $pdo->prepare('UPDATE courses SET code = ?, name = ?, lecturer_id = ?, semester = ?, year = ? WHERE id = ?');
        $stmt->execute([
           
            $data['code'],
            $data['name'],
            $data['lecturer_id'] ?? null,
            $data['semester'],
            $data['year'],
            $courseId
        ]);
        
        // Log the activity
        logSystemActivity($pdo, 1, "Updated course ID: {$courseId}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Course updated successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Delete course (admin only)
$app->delete('/api/admin/courses/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $courseId = $args['id'];
        
        // First check if course has enrollments
        $stmt = $pdo->prepare('SELECT COUNT(*) as enrollment_count FROM enrollments WHERE course_id = ?');
        $stmt->execute([$courseId]);
        $result = $stmt->fetch();
        
        if ($result['enrollment_count'] > 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Cannot delete course with existing enrollments'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Get course details for logging
        $stmt = $pdo->prepare('SELECT code, name FROM courses WHERE id = ?');
        $stmt->execute([$courseId]);
        $course = $stmt->fetch();
        
        if (!$course) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Course not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete the course
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([$courseId]);
        
        // Log the activity
        logSystemActivity($pdo, 1, "Deleted course: {$course['code']} - {$course['name']}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== ADMIN ENROLLMENT MANAGEMENT =====================
// Get all enrollments (admin view)
$app->get('/api/admin/enrollments', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query('
            SELECT e.*, 
                   u.full_name AS student_name, u.matric_no, u.email,
                   c.name as course_name, c.code as course_code, c.semester, c.year,
                   l.full_name as lecturer_name,
                   e.id as enrollment_id, e.created_at as enrollment_date
            FROM enrollments e 
            JOIN users u ON e.student_id = u.id 
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN users l ON c.lecturer_id = l.id
            ORDER BY e.created_at DESC
        ');
        $enrollments = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode($enrollments));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Create new enrollment (admin only)
$app->post('/api/admin/enrollments', function (Request $request, Response $response) use ($pdo) {
    try {
        $data = $request->getParsedBody();
        
        // Check if student is already enrolled in this course
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND course_id = ?');
        $stmt->execute([$data['student_id'], $data['course_id']]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Student is already enrolled in this course'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Create the enrollment
        $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, course_id, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$data['student_id'], $data['course_id']]);
        
        $enrollmentId = $pdo->lastInsertId();
        
        // Get student and course names for logging
        $stmt = $pdo->prepare('
            SELECT u.full_name as student_name, c.code as course_code, c.name as course_name
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            JOIN courses c ON e.course_id = c.id
            WHERE e.id = ?
        ');
        $stmt->execute([$enrollmentId]);
        $enrollmentInfo = $stmt->fetch();
        
        // Log the activity
        logSystemActivity($pdo, 1, "Enrolled {$enrollmentInfo['student_name']} in {$enrollmentInfo['course_code']} - {$enrollmentInfo['course_name']}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Student enrolled successfully',
            'enrollment_id' => $enrollmentId
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Delete enrollment (admin only)
$app->delete('/api/admin/enrollments/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $enrollmentId = $args['id'];
        
        // Get enrollment details for logging before deletion
        $stmt = $pdo->prepare('
            SELECT u.full_name as student_name, c.code as course_code, c.name as course_name
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            JOIN courses c ON e.course_id = c.id
            WHERE e.id = ?
        ');
        $stmt->execute([$enrollmentId]);
        $enrollmentInfo = $stmt->fetch();
        
        if (!$enrollmentInfo) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Enrollment not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete related records first
        // 1. Delete assessment marks
        $stmt = $pdo->prepare('DELETE FROM assessment_marks WHERE enrollment_id = ?');
        $stmt->execute([$enrollmentId]);
        
        // 2. Delete final exam marks
        $stmt = $pdo->prepare('DELETE FROM final_exam_marks WHERE enrollment_id = ?');
        $stmt->execute([$enrollmentId]);
        
        // 3. Delete feedback remarks
        $stmt = $pdo->prepare('DELETE FROM feedback_remarks WHERE enrollment_id = ?');
        $stmt->execute([$enrollmentId]);
        
        // 4. Delete remark requests
        $stmt = $pdo->prepare('DELETE FROM remark_requests WHERE enrollment_id = ?');
        $stmt->execute([$enrollmentId]);
        
        // 5. Delete mark update notifications
        $stmt = $pdo->prepare('DELETE FROM mark_update_notifications WHERE enrollment_id = ?');
        $stmt->execute([$enrollmentId]);
        
        // 6. Finally delete the enrollment
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE id = ?');
        $stmt->execute([$enrollmentId]);
        
        // Log the activity
        logSystemActivity($pdo, 1, "Unenrolled {$enrollmentInfo['student_name']} from {$enrollmentInfo['course_code']} - {$enrollmentInfo['course_name']}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Student unenrolled successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// ===== SYSTEM LOGS API ROUTES =====

// Get system logs for admin dashboard
$app->get('/api/admin/system-logs', function (Request $request, Response $response) use ($pdo) {
    try {
        // Get query parameters for filtering
        $queryParams = $request->getQueryParams();
        $activityType = $queryParams['type'] ?? '';
        $dateFilter = $queryParams['date'] ?? '';
        $limit = (int)($queryParams['limit'] ?? 50);
        $offset = (int)($queryParams['offset'] ?? 0);

        // Base query to get system logs with user information
        $sql = "SELECT 
                    sl.id,
                    sl.created_at as timestamp,
                    COALESCE(u.full_name, 'System') as user,
                    sl.action as description,
                    sl.user_id
                FROM system_logs sl 
                LEFT JOIN users u ON sl.user_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        // Add date filter if provided
        if ($dateFilter) {
            $sql .= " AND DATE(sl.created_at) = ?";
            $params[] = $dateFilter;
        }
        
        // Add activity type filter based on action content
        if ($activityType) {
            switch ($activityType) {
                case 'user':
                    $sql .= " AND (sl.action LIKE '%user%' OR sl.action LIKE '%User%' OR sl.action LIKE '%login%' OR sl.action LIKE '%register%')";
                    break;
                case 'course':
                    $sql .= " AND (sl.action LIKE '%course%' OR sl.action LIKE '%Course%')";
                    break;
                case 'enrollment':
                    $sql .= " AND (sl.action LIKE '%enroll%' OR sl.action LIKE '%Enroll%')";
                    break;
                case 'grade':
                    $sql .= " AND (sl.action LIKE '%grade%' OR sl.action LIKE '%mark%' OR sl.action LIKE '%Grade%' OR sl.action LIKE '%Mark%')";
                    break;
                case 'system':
                    $sql .= " AND (sl.user_id IS NULL OR sl.action LIKE '%system%' OR sl.action LIKE '%System%' OR sl.action LIKE '%backup%')";
                    break;
            }
        }
        
        // Order by most recent first
        $sql .= " ORDER BY sl.created_at DESC";
        
        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        
        // Transform the data to match frontend expectations
        $transformedLogs = array_map(function($log) {
            // Determine activity type based on action description
            $type = 'system'; // default
            $action = strtolower($log['description']);
            
            if (strpos($action, 'user') !== false || strpos($action, 'login') !== false || strpos($action, 'register') !== false) {
                $type = 'user';
            } elseif (strpos($action, 'course') !== false) {
                $type = 'course';
            } elseif (strpos($action, 'enroll') !== false) {
                $type = 'enrollment';
            } elseif (strpos($action, 'grade') !== false || strpos($action, 'mark') !== false) {
                $type = 'grade';
            }
            
            // Determine status - for now, assume success unless indicated otherwise
            $status = 'success';
            if (strpos($action, 'failed') !== false || strpos($action, 'error') !== false) {
                $status = 'error';
            } elseif (strpos($action, 'warning') !== false || strpos($action, 'retry') !== false) {
                $status = 'warning';
            }
            
            return [
                'id' => (int)$log['id'],
                'timestamp' => $log['timestamp'],
                'user' => $log['user'],
                'type' => $type,
                'description' => $log['description'],
                'status' => $status,
                'reviewed' => false // For now, all logs are unreviewed
            ];
        }, $logs);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM system_logs sl LEFT JOIN users u ON sl.user_id = u.id WHERE 1=1";
        $countParams = [];
        
        if ($dateFilter) {
            $countSql .= " AND DATE(sl.created_at) = ?";
            $countParams[] = $dateFilter;
        }
        
        if ($activityType) {
            switch ($activityType) {
                case 'user':
                    $countSql .= " AND (sl.action LIKE '%user%' OR sl.action LIKE '%User%' OR sl.action LIKE '%login%' OR sl.action LIKE '%register%')";
                    break;
                case 'course':
                    $countSql .= " AND (sl.action LIKE '%course%' OR sl.action LIKE '%Course%')";
                    break;
                case 'enrollment':
                    $countSql .= " AND (sl.action LIKE '%enroll%' OR sl.action LIKE '%Enroll%')";
                    break;
                case 'grade':
                    $countSql .= " AND (sl.action LIKE '%grade%' OR sl.action LIKE '%mark%' OR sl.action LIKE '%Grade%' OR sl.action LIKE '%Mark%')";
                    break;
                case 'system':
                    $countSql .= " AND (sl.user_id IS NULL OR sl.action LIKE '%system%' OR sl.action LIKE '%System%' OR sl.action LIKE '%backup%')";
                    break;
            }
        }
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($countParams);
        $totalCount = $countStmt->fetch()['total'];
        
        $responseData = [
            'success' => true,
            'logs' => $transformedLogs,
            'total' => (int)$totalCount,
            'limit' => $limit,
            'offset' => $offset
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        error_log("System logs API error: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Failed to fetch system logs: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Simple test route
$app->get('/api/test', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['message' => 'API is working']));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== REMARK REQUESTS =====================
// Get all remark requests for a student
$app->get('/students/{student_id}/remark-requests', function (Request $request, Response $response, $args) use ($pdo) {
    $studentId = $args['student_id'];
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                rr.*,
                a.name as assessment_name,
                a.max_mark,
                c.code as course_code,
                c.name as course_name,
                u.full_name as lecturer_name
            FROM remark_requests rr
            JOIN assessments a ON rr.assessment_id = a.id
            JOIN enrollments e ON rr.enrollment_id = e.id
            JOIN courses c ON e.course_id = c.id
            JOIN users u ON c.lecturer_id = u.id
            WHERE rr.student_id = ?
            ORDER BY rr.created_at DESC
        ');
        $stmt->execute([$studentId]);
        $requests = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'requests' => $requests
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Create a new remark request
$app->post('/remark-requests', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    
    try {
        // Validate required fields
        if (!isset($data['student_id'], $data['assessment_id'], $data['justification'], $data['requested_mark'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Get enrollment ID and current mark
        $stmt = $pdo->prepare('
            SELECT e.id as enrollment_id, am.mark as current_mark, c.lecturer_id, a.max_mark
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            JOIN assessments a ON a.course_id = c.id
            LEFT JOIN assessment_marks am ON am.enrollment_id = e.id AND am.assessment_id = a.id
            WHERE e.student_id = ? AND a.id = ?
        ');
        $stmt->execute([$data['student_id'], $data['assessment_id']]);
        $enrollmentData = $stmt->fetch();
        
        if (!$enrollmentData) {
            $response->getBody()->write(json_encode(['error' => 'Invalid assessment or enrollment not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Check if there's already a pending request for this assessment
        $stmt = $pdo->prepare('
            SELECT id FROM remark_requests 
            WHERE student_id = ? AND assessment_id = ? AND status IN ("pending", "under_review")
        ');
        $stmt->execute([$data['student_id'], $data['assessment_id']]);
        $existingRequest = $stmt->fetch();
        
        if ($existingRequest) {
            $response->getBody()->write(json_encode(['error' => 'You already have a pending remark request for this assessment']));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }
        
        // Validate requested mark
        if ($data['requested_mark'] > $enrollmentData['max_mark'] || $data['requested_mark'] < 0) {
            $response->getBody()->write(json_encode(['error' => 'Requested mark must be between 0 and maximum mark']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Create the remark request
        $stmt = $pdo->prepare('
            INSERT INTO remark_requests 
            (student_id, enrollment_id, assessment_id, current_mark, requested_mark, justification, lecturer_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['student_id'],
            $enrollmentData['enrollment_id'],
            $data['assessment_id'],
            $enrollmentData['current_mark'] ?? 0,
            $data['requested_mark'],
            $data['justification'],
            $enrollmentData['lecturer_id']
        ]);
        
        $requestId = $pdo->lastInsertId();
        
        // Create notification for lecturer
        $stmt = $pdo->prepare('
            SELECT a.name as assessment_name, c.code as course_code, u.full_name as student_name
            FROM assessments a
            JOIN courses c ON a.course_id = c.id
            JOIN users u ON u.id = ?
            WHERE a.id = ?
        ');
        $stmt->execute([$data['student_id'], $data['assessment_id']]);
        $notificationData = $stmt->fetch();
        
        $message = "New remark request from {$notificationData['student_name']} for {$notificationData['assessment_name']} in {$notificationData['course_code']}";
        createNotification($pdo, $enrollmentData['lecturer_id'], $message);
        
        // Create notification for student
        createNotification($pdo, $data['student_id'], "Your remark request for {$notificationData['assessment_name']} has been submitted and is under review");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'request_id' => $requestId,
            'message' => 'Remark request submitted successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Update a remark request (student can edit pending requests)
$app->put('/remark-requests/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $requestId = $args['id'];
    $data = $request->getParsedBody();
    
    try {
        // Check if request exists and is editable
        $stmt = $pdo->prepare('SELECT * FROM remark_requests WHERE id = ? AND status = "pending"');
        $stmt->execute([$requestId]);
        $existingRequest = $stmt->fetch();
        
        if (!$existingRequest) {
            $response->getBody()->write(json_encode(['error' => 'Remark request not found or cannot be edited']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Validate student ownership
        if ($existingRequest['student_id'] != $data['student_id']) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized to edit this request']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        
        // Update the request
        $stmt = $pdo->prepare('
            UPDATE remark_requests 
            SET requested_mark = ?, justification = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ');
        $stmt->execute([$data['requested_mark'], $data['justification'], $requestId]);
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Remark request updated successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Cancel a remark request
$app->delete('/remark-requests/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $requestId = $args['id'];
    $data = $request->getParsedBody();
    
    try {
        // Check if request exists and is cancellable
        $stmt = $pdo->prepare('SELECT * FROM remark_requests WHERE id = ? AND status IN ("pending", "under_review")');
        $stmt->execute([$requestId]);
        $existingRequest = $stmt->fetch();
        
        if (!$existingRequest) {
            $response->getBody()->write(json_encode(['error' => 'Remark request not found or cannot be cancelled']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Validate student ownership
        if ($existingRequest['student_id'] != $data['student_id']) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized to cancel this request']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete the request
        $stmt = $pdo->prepare('DELETE FROM remark_requests WHERE id = ?');
        $stmt->execute([$requestId]);
        
        // Notify lecturer about cancellation
        createNotification($pdo, $existingRequest['lecturer_id'], "A remark request has been cancelled by the student");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Remark request cancelled successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Get remark requests for lecturer to review
$app->get('/lecturers/{lecturer_id}/remark-requests', function (Request $request, Response $response, $args) use ($pdo) {
    $lecturerId = $args['lecturer_id'];
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                rr.*,
                a.name as assessment_name,
                a.max_mark,
                c.code as course_code,
                c.name as course_name,
                u.full_name as student_name,
                u.matric_no as student_matric
            FROM remark_requests rr
            JOIN assessments a ON rr.assessment_id = a.id
            JOIN enrollments e ON rr.enrollment_id = e.id
            JOIN courses c ON e.course_id = c.id
            JOIN users u ON rr.student_id = u.id
            WHERE rr.lecturer_id = ?
            ORDER BY rr.created_at DESC
        ');
        $stmt->execute([$lecturerId]);
        $requests = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'requests' => $requests
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Lecturer responds to remark request
$app->post('/remark-requests/{id}/respond', function (Request $request, Response $response, $args) use ($pdo) {
    $requestId = $args['id'];
    $data = $request->getParsedBody();
    
    try {
        // Validate required fields
        if (!isset($data['status'], $data['lecturer_response'])) {
            $response->getBody()->write(json_encode(['error' => 'Status and response are required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Validate status
        if (!in_array($data['status'], ['approved', 'rejected', 'under_review'])) {
            $response->getBody()->write(json_encode(['error' => 'Invalid status']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Get the request
        $stmt = $pdo->prepare('SELECT * FROM remark_requests WHERE id = ?');
        $stmt->execute([$requestId]);
        $remarkRequest = $stmt->fetch();
        
        if (!$remarkRequest) {
            $response->getBody()->write(json_encode(['error' => 'Remark request not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Validate lecturer ownership
        if ($remarkRequest['lecturer_id'] != $data['lecturer_id']) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized to respond to this request']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Update the request
        $stmt = $pdo->prepare('
            UPDATE remark_requests 
            SET status = ?, lecturer_response = ?, reviewed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ');
        $stmt->execute([$data['status'], $data['lecturer_response'], $requestId]);
        
        // If approved, update the actual mark
        if ($data['status'] === 'approved') {
            $stmt = $pdo->prepare('
                UPDATE assessment_marks 
                SET mark = ? 
                WHERE enrollment_id = ? AND assessment_id = ?
            ');
            $stmt->execute([
                $remarkRequest['requested_mark'],
                $remarkRequest['enrollment_id'],
                $remarkRequest['assessment_id']
            ]);
            
            // Create notification for mark update
            $stmt = $pdo->prepare('
                SELECT a.name as assessment_name, c.code as course_code
                FROM assessments a
                JOIN courses c ON a.course_id = c.id
                WHERE a.id = ?
            ');
            $stmt->execute([$remarkRequest['assessment_id']]);
            $assessmentInfo = $stmt->fetch();
            
            $message = "Your remark request for {$assessmentInfo['assessment_name']} in {$assessmentInfo['course_code']} has been approved. Your mark has been updated to {$remarkRequest['requested_mark']}.";
        } else {
            $message = "Your remark request has been {$data['status']}. Please check the lecturer's response for details.";
        }
        
        // Create notification for student
        createNotification($pdo, $remarkRequest['student_id'], $message);
        
        $pdo->commit();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Response submitted successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Get student's courses with assessment details for remark request form
$app->get('/students/{student_id}/courses-with-assessments', function (Request $request, Response $response, $args) use ($pdo) {
    $studentId = $args['student_id'];
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                c.id as course_id,
                c.code,
                c.name as course_name,
                a.id as assessment_id,
                a.name as assessment_name,
                a.max_mark,
                am.mark as current_mark
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            JOIN assessments a ON a.course_id = c.id
            LEFT JOIN assessment_marks am ON am.enrollment_id = e.id AND am.assessment_id = a.id
            WHERE e.student_id = ? AND am.mark IS NOT NULL
            ORDER BY c.code, a.name
        ');
        $stmt->execute([$studentId]);
        $results = $stmt->fetchAll();
        
        // Group by course
        $courses = [];
        foreach ($results as $row) {
            $courseId = $row['course_id'];
            if (!isset($courses[$courseId])) {
                $courses[$courseId] = [
                    'id' => $courseId,
                    'code' => $row['code'],
                    'name' => $row['course_name'],
                    'assessments' => []
                ];
            }
            
            $courses[$courseId]['assessments'][] = [
                'id' => $row['assessment_id'],
                'name' => $row['assessment_name'],
                'max_mark' => $row['max_mark'],
                'current_mark' => $row['current_mark']
            ];
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => array_values($courses)
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();