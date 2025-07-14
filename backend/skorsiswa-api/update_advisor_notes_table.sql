-- Update advisor_notes table to ensure it has the course_id column
-- This script will add the course_id column if it doesn't exist

-- First, check if the table exists and add the column if it's missing
-- Note: This script should be run carefully in production

-- Option 1: If the table exists but is missing the course_id column
ALTER TABLE advisor_notes 
ADD COLUMN IF NOT EXISTS course_id INT NOT NULL DEFAULT 1 AFTER student_id;

-- Option 2: If the table doesn't exist at all, create it with the correct structure
CREATE TABLE IF NOT EXISTS advisor_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
    course_id INT NOT NULL DEFAULT 1,
    note TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Ensure we have some test data for advisor relationships
INSERT IGNORE INTO advisor_student_relationships (advisor_id, student_id) 
SELECT 
    u1.id as advisor_id,
    u2.id as student_id
FROM users u1 
CROSS JOIN users u2 
WHERE u1.role_id = (SELECT id FROM roles WHERE name = 'Advisor') 
AND u2.role_id = (SELECT id FROM roles WHERE name = 'Student')
LIMIT 5;
