<?php
// Check database for sample data

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
    
    // Check for courses
    $result = $pdo->query('SELECT COUNT(*) as count FROM courses');
    $courseCount = $result->fetch()['count'];
    echo "Found $courseCount courses in the database.\n";
    
    if ($courseCount > 0) {
        $result = $pdo->query('SELECT id, code, name FROM courses LIMIT 5');
        echo "Sample courses:\n";
        while ($course = $result->fetch()) {
            echo "- {$course['id']}: {$course['code']} - {$course['name']}\n";
        }
    }
    
    // Check for enrollments
    $result = $pdo->query('SELECT COUNT(*) as count FROM enrollments');
    $enrollmentCount = $result->fetch()['count'];
    echo "\nFound $enrollmentCount enrollments in the database.\n";
    
    if ($enrollmentCount > 0) {
        $result = $pdo->query('
            SELECT 
                e.id, 
                u.full_name as student_name, 
                c.code as course_code 
            FROM 
                enrollments e
                JOIN users u ON e.student_id = u.id
                JOIN courses c ON e.course_id = c.id
            LIMIT 5
        ');
        echo "Sample enrollments:\n";
        while ($enrollment = $result->fetch()) {
            echo "- {$enrollment['id']}: {$enrollment['student_name']} is enrolled in {$enrollment['course_code']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
