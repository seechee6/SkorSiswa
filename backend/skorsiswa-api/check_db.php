<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=skorsiswa_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking meetings table structure:\n";
    $stmt = $pdo->query('DESCRIBE meetings');
    $columns = $stmt->fetchAll();
    
    $requiredColumns = ['location', 'meeting_link', 'title', 'duration', 'agenda', 'action_items'];
    
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
        if (in_array($col['Field'], $requiredColumns)) {
            echo "  ✓ Required column found\n";
        }
    }
    
    echo "\nChecking for required columns:\n";
    foreach ($requiredColumns as $required) {
        $found = false;
        foreach ($columns as $col) {
            if ($col['Field'] === $required) {
                $found = true;
                break;
            }
        }
        echo $required . ": " . ($found ? "✓ EXISTS" : "✗ MISSING") . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
