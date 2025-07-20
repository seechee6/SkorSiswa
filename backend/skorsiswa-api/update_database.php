<?php
require_once 'config/database.php';

try {
    $config = require 'config/database.php';
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.\n";

    // Check if advisor_notes table exists and its structure
    $result = $pdo->query("DESCRIBE advisor_notes");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current advisor_notes table structure:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }

    // Check if course_id column exists
    if (!in_array('course_id', $columns)) {
        echo "\nAdding course_id column...\n";
        $pdo->exec("ALTER TABLE advisor_notes ADD COLUMN course_id INT NOT NULL DEFAULT 1 AFTER student_id");
        echo "course_id column added successfully.\n";
    } else {
        echo "\ncourse_id column already exists.\n";
    }

    // Ensure advisor_student_relationships table exists for testing
    $pdo->exec("CREATE TABLE IF NOT EXISTS advisor_student_relationships (
        id INT AUTO_INCREMENT PRIMARY KEY,
        advisor_id INT NOT NULL,
        student_id INT NOT NULL,
        UNIQUE KEY unique_relationship (advisor_id, student_id),
        FOREIGN KEY (advisor_id) REFERENCES users(id),
        FOREIGN KEY (student_id) REFERENCES users(id)
    )");

    // Add some test advisor-student relationships
    $pdo->exec("INSERT IGNORE INTO advisor_student_relationships (advisor_id, student_id) 
    SELECT 
        u1.id as advisor_id,
        u2.id as student_id
    FROM users u1 
    CROSS JOIN users u2 
    WHERE u1.role_id = (SELECT id FROM roles WHERE name = 'Advisor') 
    AND u2.role_id = (SELECT id FROM roles WHERE name = 'Student')
    LIMIT 5");

    echo "Database update completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
