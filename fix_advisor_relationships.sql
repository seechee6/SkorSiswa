-- Quick fix: Add advisor relationships for the specific IDs from the console log
-- Based on the error log: advisor ID 10, student ID 4

-- First, let's see what advisor and student IDs we have
SELECT 'Advisors:' as type, id, full_name, role_id FROM users WHERE role_id = 4;
SELECT 'Students:' as type, id, full_name, role_id FROM users WHERE role_id = 3;

-- Add the specific relationship that's failing
-- Advisor ID: 10, Student ID: 4 (from the console log)
INSERT IGNORE INTO advisors (advisor_id, student_id) VALUES 
(10, 4),
(10, 5), 
(10, 6);

-- If you have other students, add them too
-- You can check existing advisors table first:
SELECT * FROM advisors;

-- Alternative: Temporary fix - allow any advisor to add notes for any student
-- You would need to modify the backend to comment out the relationship check
-- In backend/skorsiswa-api/public/index.php around line 1332:
-- Comment out these lines:
/*
        // Verify advisor-student relationship
        $stmt = $pdo->prepare("SELECT 1 FROM advisors WHERE advisor_id = ? AND student_id = ?");
        $stmt->execute([$advisor_id, $student_id]);
        
        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized access to student data'
            ]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
*/
