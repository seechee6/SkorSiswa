<?php
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
    
    // Allow specific localhost origins when credentials are needed
    $allowedOrigin = 'http://localhost:8081'; // Your Vue app's origin
    if (preg_match('/^https?:\/\/localhost(:\d+)?$/', $origin)) {
        $allowedOrigin = $origin;
    }
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
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

// ===================== ADVISOR ENDPOINTS =====================
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
        
        // Verify this student is actually advised by this advisor
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM advisors WHERE advisor_id = ? AND student_id = ?');
        $stmt->execute([$advisor_id, $student_id]);
        if ($stmt->fetchColumn() == 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Student is not advised by this advisor'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Get student basic info
        $stmt = $pdo->prepare('
            SELECT 
                u.id,
                u.full_name as name,
                u.matric_no as studentId,
                u.email,
                CASE 
                    WHEN u.matric_no LIKE "%2021%" THEN 4
                    WHEN u.matric_no LIKE "%2022%" THEN 3  
                    WHEN u.matric_no LIKE "%2023%" THEN 2
                    WHEN u.matric_no LIKE "%2024%" THEN 1
                    ELSE 1
                END as year,
                CASE 
                    WHEN u.matric_no LIKE "CS%" THEN "Computer Science"
                    WHEN u.matric_no LIKE "IS%" THEN "Information Systems"
                    WHEN u.matric_no LIKE "SE%" THEN "Software Engineering"
                    WHEN u.matric_no LIKE "IT%" THEN "Information Technology"
                    ELSE "Computer Science"
                END as program
            FROM users u
            WHERE u.id = ?
        ');
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Student not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
          // Get course enrollments and marks
        $stmt = $pdo->prepare('
            SELECT 
                c.code as course_code,
                c.name as course_name,
                3 as credit_hours,
                "Current" as semester,
                "2024/2025" as academic_year,
                COALESCE(SUM(
                    CASE 
                        WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                        ELSE 0 
                    END
                ), 0) as assessment_total,
                COALESCE((fem.mark / 100) * 30, 0) as final_exam_weighted,
                COALESCE(fem.mark, 0) as final_exam_marks,
                COALESCE(SUM(
                    CASE 
                        WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                        ELSE 0 
                    END
                ), 0) + COALESCE((fem.mark / 100) * 30, 0) as total_marks,
                CASE 
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 80 THEN "A"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 75 THEN "A-"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 70 THEN "B+"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 65 THEN "B"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 60 THEN "B-"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 55 THEN "C+"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 50 THEN "C"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 45 THEN "C-"
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 40 THEN "D"
                    ELSE "F"
                END as grade,
                CASE 
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 80 THEN 4.0
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 75 THEN 3.7
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 70 THEN 3.3
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 65 THEN 3.0
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 60 THEN 2.7
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 55 THEN 2.3
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 50 THEN 2.0
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 45 THEN 1.7
                    WHEN (COALESCE(SUM(
                        CASE 
                            WHEN am.mark IS NOT NULL THEN ((am.mark / a.max_mark) * a.weight)
                            ELSE 0 
                        END
                    ), 0) + COALESCE((fem.mark / 100) * 30, 0)) >= 40 THEN 1.3
                    ELSE 0.0
                END as grade_points
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN assessments a ON c.id = a.course_id
            LEFT JOIN assessment_marks am ON e.id = am.enrollment_id AND a.id = am.assessment_id
            LEFT JOIN final_exam_marks fem ON e.id = fem.enrollment_id
            WHERE e.student_id = ?
            GROUP BY c.id, c.code, c.name, fem.mark
            ORDER BY c.code
        ');
        $stmt->execute([$student_id]);
        $courses = $stmt->fetchAll();
          // Get assessment breakdown for current courses
        $assessments = [];
        foreach ($courses as $course) {
            $stmt = $pdo->prepare('
                SELECT 
                    a.name as assessment_name,
                    "coursework" as assessment_type,
                    a.max_mark as max_marks,
                    a.weight as weightage,
                    am.mark as marks_obtained
                FROM assessments a
                LEFT JOIN assessment_marks am ON a.id = am.assessment_id 
                    AND am.enrollment_id = (
                        SELECT e.id FROM enrollments e 
                        WHERE e.student_id = ? AND e.course_id = a.course_id
                    )
                WHERE a.course_id = (SELECT id FROM courses WHERE code = ?)
                ORDER BY a.name
            ');
            $stmt->execute([$student_id, $course['course_code']]);
            $courseAssessments = $stmt->fetchAll();
            
            if ($courseAssessments) {
                $assessments[$course['course_code']] = $courseAssessments;
            }
        }
        
        // Calculate overall GPA
        $totalGradePoints = 0;
        $totalCreditHours = 0;
        foreach ($courses as $course) {
            if ($course['total_marks'] !== null) {
                $totalGradePoints += $course['grade_points'] * $course['credit_hours'];
                $totalCreditHours += $course['credit_hours'];
            }
        }
        $gpa = $totalCreditHours > 0 ? round($totalGradePoints / $totalCreditHours, 2) : 0;
        
        // Get recent meetings
        $stmt = $pdo->prepare('
            SELECT 
                meeting_date,
                meeting_time,
                meeting_type,
                notes,
                status
            FROM meetings
            WHERE advisor_id = ? AND student_id = ?
            ORDER BY meeting_date DESC, meeting_time DESC
            LIMIT 5
        ');
        $stmt->execute([$advisor_id, $student_id]);
        $recentMeetings = $stmt->fetchAll();
        
        $performanceData = [
            'student' => $student,
            'gpa' => $gpa,
            'courses' => $courses,
            'assessments' => $assessments,
            'recent_meetings' => $recentMeetings,
            'statistics' => [
                'total_courses' => count($courses),
                'completed_courses' => count(array_filter($courses, function($c) { return $c['total_marks'] !== null; })),
                'total_credit_hours' => $totalCreditHours,
                'average_marks' => $courses ? round(array_sum(array_column(array_filter($courses, function($c) { return $c['total_marks'] !== null; }), 'total_marks')) / count(array_filter($courses, function($c) { return $c['total_marks'] !== null; })), 2) : 0
            ]
        ];
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $performanceData
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        error_log("Error fetching advisee performance: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Failed to fetch performance data: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== MEETINGS MANAGEMENT =====================
// Schedule a meeting between advisor and student
$app->post('/advisors/{advisor_id}/meetings', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $data = $request->getParsedBody();
        $advisor_id = $args['advisor_id'];
        
        // Validate required fields
        if (!isset($data['student_id']) || !isset($data['meeting_date']) || !isset($data['meeting_time'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Missing required fields: student_id, meeting_date, and meeting_time are required'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        // Insert meeting record using separate date and time columns as per database schema
        $stmt = $pdo->prepare('
            INSERT INTO meetings (advisor_id, student_id, title, meeting_date, meeting_time, duration, location, meeting_type, meeting_link, agenda, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $advisor_id,
            $data['student_id'],
            $data['title'] ?? 'Advisor Meeting',
            $data['meeting_date'],
            $data['meeting_time'],
            $data['duration'] ?? 60,
            $data['location'] ?? '',
            $data['meeting_type'] ?? 'academic',
            $data['meeting_link'] ?? '',
            $data['agenda'] ?? '',
            $data['notes'] ?? '',
            $data['status'] ?? 'scheduled'
        ]);
        
        $meeting_id = $pdo->lastInsertId();
        
        // Get student info for notifications
        $stmt = $pdo->prepare('SELECT full_name, email FROM users WHERE id = ?');
        $stmt->execute([$data['student_id']]);
        $student = $stmt->fetch();
        
        // Get advisor info
        $stmt = $pdo->prepare('SELECT full_name FROM users WHERE id = ?');
        $stmt->execute([$advisor_id]);
        $advisor = $stmt->fetch();
        
        // Create notification for student
        $meeting_datetime = $data['meeting_date'] . ' ' . $data['meeting_time'];
        $notification_message = "Meeting scheduled with {$advisor['full_name']} on " . date('F j, Y \a\t g:i A', strtotime($meeting_datetime));
        if (!empty($data['meeting_link'])) {
            $notification_message .= ". Meeting link: {$data['meeting_link']}";
        }
        createNotification($pdo, $data['student_id'], $notification_message);
        
        // Log the activity
        logSystemActivity($pdo, $advisor_id, "Scheduled meeting with student {$student['full_name']} for {$data['meeting_date']} at {$data['meeting_time']}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'meeting_id' => $meeting_id,
            'message' => 'Meeting scheduled successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        error_log("Error scheduling meeting: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Failed to schedule meeting: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Get all meetings for an advisor
$app->get('/advisors/{advisor_id}/meetings', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT 
                m.*,
                u.full_name as student_name,
                u.matric_no as student_id,
                u.email as student_email,
                CONCAT(m.meeting_date, " ", m.meeting_time) as meeting_datetime
            FROM meetings m
            JOIN users u ON m.student_id = u.id
            WHERE m.advisor_id = ?
            ORDER BY m.meeting_date DESC, m.meeting_time DESC
        ');
        $stmt->execute([$args['advisor_id']]);
        $meetings = $stmt->fetchAll();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'meetings' => $meetings
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Failed to fetch meetings: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Update a meeting
$app->put('/meetings/{meeting_id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $data = $request->getParsedBody();
        $meeting_id = $args['meeting_id'];
        
        // Get current meeting data to validate ownership
        $stmt = $pdo->prepare('SELECT advisor_id, student_id FROM meetings WHERE id = ?');
        $stmt->execute([$meeting_id]);
        $meeting = $stmt->fetch();
        
        if (!$meeting) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Meeting not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Update meeting record
        $stmt = $pdo->prepare('
            UPDATE meetings 
            SET student_id = ?, title = ?, meeting_date = ?, meeting_time = ?, duration = ?, 
                location = ?, meeting_type = ?, meeting_link = ?, agenda = ?, notes = ?, 
                action_items = ?, status = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            $data['student_id'] ?? $meeting['student_id'],
            $data['title'] ?? 'Advisor Meeting',
            $data['meeting_date'],
            $data['meeting_time'],
            $data['duration'] ?? 60,
            $data['location'] ?? '',
            $data['meeting_type'] ?? 'academic',
            $data['meeting_link'] ?? '',
            $data['agenda'] ?? '',
            $data['notes'] ?? '',
            $data['action_items'] ?? '',
            $data['status'] ?? 'scheduled',
            $meeting_id
        ]);
        
        // Log the activity
        logSystemActivity($pdo, $meeting['advisor_id'], "Updated meeting (ID: {$meeting_id})");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Meeting updated successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        error_log("Error updating meeting: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Failed to update meeting: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Delete a meeting
$app->delete('/meetings/{meeting_id}', function (Request $request, Response $response, $args) use ($pdo) {
    try {
        $meeting_id = $args['meeting_id'];
        
        // Get meeting data to validate ownership and for logging
        $stmt = $pdo->prepare('
            SELECT m.advisor_id, m.student_id, m.meeting_date, m.meeting_time, u.full_name as student_name
            FROM meetings m
            JOIN users u ON m.student_id = u.id
            WHERE m.id = ?
        ');
        $stmt->execute([$meeting_id]);
        $meeting = $stmt->fetch();
        
        if (!$meeting) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Meeting not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Delete the meeting
        $stmt = $pdo->prepare('DELETE FROM meetings WHERE id = ?');
        $stmt->execute([$meeting_id]);
        
        // Log the activity
        logSystemActivity($pdo, $meeting['advisor_id'], "Deleted meeting with {$meeting['student_name']} scheduled for {$meeting['meeting_date']} at {$meeting['meeting_time']}");
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Meeting deleted successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        error_log("Error deleting meeting: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Failed to delete meeting: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Include student routes
require __DIR__ . '/../src/routes/student.php';

// ===========================================
// SETUP AND UTILITY ENDPOINTS
// ===========================================

// Database setup endpoint - creates meetings table and advisor relationships
$app->post('/setup/meetings', function (Request $request, Response $response) use ($pdo) {
    try {
        // Create meetings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS meetings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            advisor_id INT NOT NULL,
            student_id INT NOT NULL,
            title VARCHAR(255) NOT NULL DEFAULT 'Advisor Meeting',
            meeting_date DATE NOT NULL,
            meeting_time TIME NOT NULL,
            duration INT DEFAULT 60,
            location VARCHAR(255) DEFAULT '',
            meeting_type ENUM('academic', 'career', 'personal', 'crisis', 'routine') DEFAULT 'academic',
            status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
            agenda TEXT,
            notes TEXT,
            action_items TEXT,
            next_meeting_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            
            INDEX idx_advisor_id (advisor_id),
            INDEX idx_student_id (student_id),
            INDEX idx_meeting_date (meeting_date),
            INDEX idx_status (status)
        )");

        // Ensure advisors table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS advisors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            advisor_id INT NOT NULL,
            student_id INT NOT NULL,
            assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            UNIQUE KEY unique_advisor_student (advisor_id, student_id),
            FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        // Insert test advisor-student relationships
        $pdo->exec("INSERT IGNORE INTO advisors (advisor_id, student_id) VALUES 
        (10, 4),
        (10, 5), 
        (10, 6)");

        // Insert sample meetings for testing
        $pdo->exec("INSERT IGNORE INTO meetings (advisor_id, student_id, title, meeting_date, meeting_time, meeting_type, status, agenda) VALUES
        (10, 4, 'Academic Progress Review', '2024-12-25', '10:00:00', 'academic', 'scheduled', 'Review semester progress and plan for next semester'),
        (10, 4, 'Career Planning Session', '2024-12-30', '14:00:00', 'career', 'scheduled', 'Discuss internship opportunities and career goals'),
        (10, 5, 'Check-in Meeting', '2024-12-27', '11:00:00', 'routine', 'scheduled', 'Regular student check-in and support')");

        // Get sample data
        $stmt = $pdo->query("SELECT m.id, u1.full_name as advisor, u2.full_name as student, m.title, m.meeting_date, m.status 
                            FROM meetings m
                            JOIN users u1 ON m.advisor_id = u1.id
                            JOIN users u2 ON m.student_id = u2.id
                            LIMIT 5");
        $sampleMeetings = $stmt->fetchAll();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Meetings table and advisor relationships setup completed successfully',
            'sample_meetings' => $sampleMeetings
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Setup failed: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Insert semester 2 test data endpoint
$app->post('/setup/semester2-data', function (Request $request, Response $response) use ($pdo) {
    try {
        // Check what students exist
        $stmt = $pdo->query("SELECT id, full_name, matric_no FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'student') LIMIT 3");
        $students = $stmt->fetchAll();
        
        // Define semester 2 courses
        $semester2Courses = [
            ['code' => 'CSC2103', 'name' => 'Data Structures and Algorithms', 'credits' => 3],
            ['code' => 'CSC2203', 'name' => 'Object-Oriented Programming', 'credits' => 4],
            ['code' => 'CSC2303', 'name' => 'Database Systems', 'credits' => 3],
            ['code' => 'MAT2101', 'name' => 'Discrete Mathematics', 'credits' => 3],
            ['code' => 'ENG2101', 'name' => 'Technical Writing', 'credits' => 2]
        ];
        
        $insertedCourses = [];
        $enrollmentCount = 0;
        
        // Get or create courses
        foreach ($semester2Courses as $courseData) {
            // Check if course exists
            $stmt = $pdo->prepare("SELECT id FROM courses WHERE code = ?");
            $stmt->execute([$courseData['code']]);
            $course = $stmt->fetch();
            
            if (!$course) {
                // Create course with lecturer_id = 2 (assuming lecturer exists)
                $stmt = $pdo->prepare("INSERT INTO courses (code, name, semester, year, lecturer_id) VALUES (?, ?, 'Semester 2', '2024/2025', 2)");
                $stmt->execute([$courseData['code'], $courseData['name']]);
                $courseId = $pdo->lastInsertId();
                $insertedCourses[] = $courseData['code'];
            } else {
                $courseId = $course['id'];
            }
            
            // Enroll students in semester 2 courses
            foreach ($students as $student) {
                // Check if enrollment exists
                $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
                $stmt->execute([$student['id'], $courseId]);
                $enrollment = $stmt->fetch();
                
                if (!$enrollment) {
                    // Create enrollment
                    $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                    $stmt->execute([$student['id'], $courseId]);
                    $enrollmentId = $pdo->lastInsertId();
                    $enrollmentCount++;
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
                    $stmt = $pdo->prepare("SELECT id FROM assessments WHERE course_id = ? AND name = ?");
                    $stmt->execute([$courseId, $assessment['name']]);
                    $existingAssessment = $stmt->fetch();
                    
                    if (!$existingAssessment) {
                        // Create assessment
                        $stmt = $pdo->prepare("INSERT INTO assessments (course_id, name, max_mark, weight) VALUES (?, ?, ?, ?)");
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
                        INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE mark = VALUES(mark)
                    ");
                    $stmt->execute([$enrollmentId, $assessmentId, $studentMark]);
                }
                
                // Add final exam marks
                $finalExamPerformance = 0.65 + (mt_rand() / mt_getrandmax()) * 0.3; // 65%-95%
                $finalExamMark = round(100 * $finalExamPerformance, 1);
                
                $stmt = $pdo->prepare("
                    INSERT INTO final_exam_marks (enrollment_id, mark) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE mark = VALUES(mark)
                ");
                $stmt->execute([$enrollmentId, $finalExamMark]);
            }
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Semester 2 test data inserted successfully',
            'students_processed' => count($students),
            'courses_created' => $insertedCourses,
            'enrollments_created' => $enrollmentCount
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Semester 2 data setup failed: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Test meeting insert endpoint
$app->post('/test/meeting-insert', function (Request $request, Response $response) use ($pdo) {
    try {
        // Test inserting a meeting with location
        $stmt = $pdo->prepare('
            INSERT INTO meetings (advisor_id, student_id, title, meeting_date, meeting_time, duration, location, meeting_type, meeting_link, agenda, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $result = $stmt->execute([
            10, // advisor_id
            4,  // student_id
            'Test Meeting',
            '2025-06-27',
            '10:00:00',
            60,
            'Office Room 123', // location
            'academic',
            'https://zoom.us/j/test',
            'Test agenda',
            'Test notes',
            'scheduled'
        ]);
        
        if ($result) {
            $meetingId = $pdo->lastInsertId();
            
            // Fetch it back to verify
            $stmt = $pdo->prepare('SELECT * FROM meetings WHERE id = ?');
            $stmt->execute([$meetingId]);
            $meeting = $stmt->fetch();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Meeting inserted successfully',
                'meeting_id' => $meetingId,
                'meeting_data' => [
                    'title' => $meeting['title'],
                    'location' => $meeting['location'],
                    'meeting_link' => $meeting['meeting_link'],
                    'duration' => $meeting['duration']
                ]
            ]));
        } else {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to insert meeting'
            ]));
            return $response->withStatus(500);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Meeting test failed: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// Add meeting_link column to meetings table endpoint
$app->post('/setup/add-meeting-link', function (Request $request, Response $response) use ($pdo) {
    try {
        // Add meeting_link column if it doesn't exist
        $sql = "ALTER TABLE meetings ADD COLUMN meeting_link VARCHAR(500) DEFAULT '' AFTER location";
        
        try {
            $pdo->exec($sql);
            $message = "Meeting link field added successfully.";
            $columnAdded = true;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                $message = "Meeting link field already exists.";
                $columnAdded = false;
            } else {
                throw $e;
            }
        }

        // Get updated table structure
        $stmt = $pdo->query('DESCRIBE meetings');
        $tableStructure = [];
        while ($row = $stmt->fetch()) {
            $tableStructure[] = [
                'field' => $row['Field'],
                'type' => $row['Type'],
                'null' => $row['Null'],
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            ];
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => $message,
            'column_added' => $columnAdded,
            'table_structure' => $tableStructure
        ]));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Database update failed: ' . $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();
