<?php
try {
    $config = array('host' => 'localhost', 'dbname' => 'skorsiswa_db', 'username' => 'root', 'password' => '');
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully.\n";

    // Add meeting_link column if it doesn't exist
    $sql = "ALTER TABLE meetings ADD COLUMN meeting_link VARCHAR(500) DEFAULT '' AFTER location";
    try {
        $pdo->exec($sql);
        echo "Meeting link field added successfully.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Meeting link field already exists.\n";
        } else {
            throw $e;
        }
    }

    // Show updated table structure
    $stmt = $pdo->query('DESCRIBE meetings');
    echo "\nUpdated meetings table structure:\n";
    while ($row = $stmt->fetch()) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }

    echo "\nDatabase update completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
