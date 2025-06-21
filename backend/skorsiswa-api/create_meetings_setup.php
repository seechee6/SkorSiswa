<?php
require_once 'config/database.php';

try {
    $config = require 'config/database.php';
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.\n";

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
    echo "meetings table created successfully.\n";

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
    echo "advisors table ensured.\n";

    // Insert test advisor-student relationships
    $pdo->exec("INSERT IGNORE INTO advisors (advisor_id, student_id) VALUES 
    (10, 4),
    (10, 5), 
    (10, 6)");
    echo "Test advisor relationships added.\n";

    // Insert sample meetings for testing
    $pdo->exec("INSERT IGNORE INTO meetings (advisor_id, student_id, title, meeting_date, meeting_time, meeting_type, status, agenda) VALUES
    (10, 4, 'Academic Progress Review', '2024-12-25', '10:00:00', 'academic', 'scheduled', 'Review semester progress and plan for next semester'),
    (10, 4, 'Career Planning Session', '2024-12-30', '14:00:00', 'career', 'scheduled', 'Discuss internship opportunities and career goals'),
    (10, 5, 'Check-in Meeting', '2024-12-27', '11:00:00', 'routine', 'scheduled', 'Regular student check-in and support')");
    echo "Sample meetings added.\n";

    // Show some sample data
    echo "\nSample meetings:\n";
    $stmt = $pdo->query("SELECT m.id, u1.full_name as advisor, u2.full_name as student, m.title, m.meeting_date, m.status 
                        FROM meetings m
                        JOIN users u1 ON m.advisor_id = u1.id
                        JOIN users u2 ON m.student_id = u2.id
                        LIMIT 5");
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Advisor: {$row['advisor']}, Student: {$row['student']}, Meeting: {$row['title']}, Date: {$row['meeting_date']}, Status: {$row['status']}\n";
    }

    echo "\nDatabase setup completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
