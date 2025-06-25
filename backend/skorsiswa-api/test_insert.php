<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=skorsiswa_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test inserting a meeting with location
    echo "Testing meeting insert with location...\n";
    
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
        echo "✓ Meeting inserted successfully with ID: $meetingId\n";
        
        // Now fetch it back to verify
        $stmt = $pdo->prepare('SELECT * FROM meetings WHERE id = ?');
        $stmt->execute([$meetingId]);
        $meeting = $stmt->fetch();
        
        echo "Retrieved meeting data:\n";
        echo "Title: " . $meeting['title'] . "\n";
        echo "Location: " . $meeting['location'] . "\n";
        echo "Meeting Link: " . $meeting['meeting_link'] . "\n";
        echo "Duration: " . $meeting['duration'] . "\n";
        
    } else {
        echo "✗ Failed to insert meeting\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
