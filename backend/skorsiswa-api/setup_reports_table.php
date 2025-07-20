<?php
require __DIR__ . '/vendor/autoload.php';

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
    echo "Connected to database successfully.\n";
    
    // Create advisor_reports table
    $sql = "CREATE TABLE IF NOT EXISTS advisor_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        advisor_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        type ENUM('comprehensive', 'summary', 'at-risk', 'progress') NOT NULL,
        student_count INT NOT NULL,
        format ENUM('pdf', 'excel', 'html') NOT NULL,
        report_data TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Table 'advisor_reports' created successfully.\n";
    
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
