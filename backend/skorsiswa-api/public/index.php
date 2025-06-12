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

// Example route to test DB connection
$app->get('/db-test', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query('SELECT 1');
    $result = $stmt->fetch();
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET /login route for browser access
$app->get('/login', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Please use POST method to log in with matric_no and password.'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Login endpoint
$app->post('/login', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $login_id = $data['login_id'] ?? null;
    $password = $data['password'] ?? null;

    if (!$login_id || !$password) {
        $response->getBody()->write(json_encode(['error' => 'Login ID and password required.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Try to find user by matric_no, staff_no, or email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE matric_no = ? OR staff_no = ? OR email = ?');
    $stmt->execute([$login_id, $login_id, $login_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        $response->getBody()->write(json_encode(['success' => true, 'user' => $user]));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['error' => 'Invalid credentials.']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
});

// ===================== USERS CRUD =====================
// Get all users
$app->get('/users', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id');
    $users = $stmt->fetchAll();
    foreach ($users as &$user) { unset($user['password_hash']); }
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});
// Get user by ID
$app->get('/users/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $stmt->execute([$args['id']]);
    $user = $stmt->fetch();
    if ($user) unset($user['password_hash']);
    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json');
});
// Create user
$app->post('/users', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, matric_no, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$data['name'], $data['matric_no'], $data['email'], $hash, $data['role_id']]);
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Update user
$app->put('/users/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $fields = [];
    $params = [];
    foreach (['name','matric_no','email','role_id'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
    }
    if (isset($data['password'])) {
        $fields[] = "password_hash = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    $params[] = $args['id'];
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Delete user
$app->delete('/users/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== COURSES CRUD =====================
// Get all courses
$app->get('/courses', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query('SELECT c.*, u.name AS lecturer_name FROM courses c JOIN users u ON c.lecturer_id = u.id');
    $courses = $stmt->fetchAll();
    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});
// Get course by ID
$app->get('/courses/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT c.*, u.name AS lecturer_name FROM courses c JOIN users u ON c.lecturer_id = u.id WHERE c.id = ?');
    $stmt->execute([$args['id']]);
    $course = $stmt->fetch();
    $response->getBody()->write(json_encode($course));
    return $response->withHeader('Content-Type', 'application/json');
});
// Create course
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
    $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
    $stmt->execute([$args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== ENROLLMENTS =====================
// Get all enrollments
$app->get('/enrollments', function (Request $request, Response $response) use ($pdo) {
    $stmt = $pdo->query('SELECT e.*, u.name AS student_name, c.name AS course_name FROM enrollments e JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id');
    $enrollments = $stmt->fetchAll();
    $response->getBody()->write(json_encode($enrollments));
    return $response->withHeader('Content-Type', 'application/json');
});
// Enroll a student
$app->post('/enrollments', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)');
    $stmt->execute([$data['user_id'], $data['course_id']]);
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Remove a student from a course
$app->delete('/enrollments/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('DELETE FROM enrollments WHERE id = ?');
    $stmt->execute([$args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== ASSESSMENT COMPONENTS =====================
// Get components for a course
$app->get('/courses/{course_id}/components', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM assessment_components WHERE course_id = ?');
    $stmt->execute([$args['course_id']]);
    $components = $stmt->fetchAll();
    $response->getBody()->write(json_encode($components));
    return $response->withHeader('Content-Type', 'application/json');
});
// Add component to a course
$app->post('/courses/{course_id}/components', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $stmt = $pdo->prepare('INSERT INTO assessment_components (course_id, name, weight, max_mark) VALUES (?, ?, ?, ?)');
    $stmt->execute([$args['course_id'], $data['name'], $data['weight'], $data['max_mark']]);
    $response->getBody()->write(json_encode(['success' => true, 'id' => $pdo->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Update component
$app->put('/components/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    $fields = [];
    $params = [];
    foreach (['name','weight','max_mark'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
    }
    $params[] = $args['id'];
    $sql = 'UPDATE assessment_components SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Delete component
$app->delete('/components/{id}', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('DELETE FROM assessment_components WHERE id = ?');
    $stmt->execute([$args['id']]);
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== MARKS =====================
// Get marks for a student in a course
$app->get('/enrollments/{enrollment_id}/marks', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT m.*, ac.name AS component_name FROM marks m JOIN assessment_components ac ON m.component_id = ac.id WHERE m.enrollment_id = ?');
    $stmt->execute([$args['enrollment_id']]);
    $marks = $stmt->fetchAll();
    $response->getBody()->write(json_encode($marks));
    return $response->withHeader('Content-Type', 'application/json');
});
// Add or update mark for a component
$app->post('/enrollments/{enrollment_id}/marks', function (Request $request, Response $response, $args) use ($pdo) {
    $data = $request->getParsedBody();
    // Check if mark exists
    $stmt = $pdo->prepare('SELECT id FROM marks WHERE enrollment_id = ? AND component_id = ?');
    $stmt->execute([$args['enrollment_id'], $data['component_id']]);
    $existing = $stmt->fetch();
    if ($existing) {
        // Update
        $stmt = $pdo->prepare('UPDATE marks SET mark = ? WHERE id = ?');
        $stmt->execute([$data['mark'], $existing['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO marks (enrollment_id, component_id, mark) VALUES (?, ?, ?)');
        $stmt->execute([$args['enrollment_id'], $data['component_id'], $data['mark']]);
    }
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});
// Get all marks for a course
$app->get('/courses/{course_id}/marks', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT m.*, e.user_id, ac.name AS component_name FROM marks m JOIN enrollments e ON m.enrollment_id = e.id JOIN assessment_components ac ON m.component_id = ac.id WHERE ac.course_id = ?');
    $stmt->execute([$args['course_id']]);
    $marks = $stmt->fetchAll();
    $response->getBody()->write(json_encode($marks));
    return $response->withHeader('Content-Type', 'application/json');
});

// ===================== NOTIFICATIONS =====================
// Get notifications for a user
$app->get('/users/{user_id}/notifications', function (Request $request, Response $response, $args) use ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
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

// Registration endpoint
$app->post('/register', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    
    $user = new User($pdo);
    
    // Validate input
    $errors = $user->validateInput($data);
    if (!empty($errors)) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'errors' => $errors
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
    
    // Register user
    $result = $user->register(
        $data['name'],
        $data['matric_no'],
        $data['email'],
        $data['password'],
        $data['role']
    );
    
    $response->getBody()->write(json_encode($result));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result['success'] ? 201 : 400);
});

$app->run();
