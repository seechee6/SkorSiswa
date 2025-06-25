<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=skorsiswa_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking users table structure:\n";
    $stmt = $pdo->query('DESCRIBE users');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nSample users data:\n";
    $stmt = $pdo->query('SELECT * FROM users LIMIT 5');
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Name: {$row['full_name']}, Type: " . ($row['user_type'] ?? 'N/A') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
