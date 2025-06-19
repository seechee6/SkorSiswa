<?php
// Add sample notifications to the database

// Load the PDO connection from the main application
require_once __DIR__ . '/vendor/autoload.php';

// Create PDO connection - make sure to adjust these credentials to match your setup
$host = 'localhost';
$dbname = 'skorsiswa_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // First, clear existing notifications
    $pdo->exec('TRUNCATE TABLE notifications');
    
    // Get all student IDs
    $stmt = $pdo->query("SELECT id FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'student')");
    $students = $stmt->fetchAll();
    
    // Sample notification messages
    $notifications = [
        'Your grade for Algorithms Assignment 1 has been updated.',
        'Reminder: Database Systems final exam is on Monday.',
        'You have a new remark request response for Software Engineering project.',
        'Assignment 2 for Artificial Intelligence has been graded.',
        'The deadline for Mobile Computing Assignment 3 has been extended by 48 hours.',
        'Software Engineering lab session has been rescheduled to Thursday.',
        'Your ranking in Database Systems has improved by 5 positions.',
        'New assignment posted for Algorithms class.',
        'You have achieved the highest grade in the Mobile Computing midterm!',
        'The final exam schedule has been posted. Check your student portal.',
    ];
    
    // Insert sample notifications for each student
    foreach ($students as $student) {
        // Add 3-5 random notifications for each student
        $notificationCount = rand(3, 5);
        $randomNotifications = array_rand(array_flip($notifications), $notificationCount);
        
        if (!is_array($randomNotifications)) {
            $randomNotifications = [$randomNotifications];
        }
        
        foreach ($randomNotifications as $message) {
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
            $stmt->execute([$student['id'], $message]);
        }
    }
    
    echo "Sample notifications added successfully!\n";
    
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    exit;
}
