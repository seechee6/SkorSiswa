<<<<<<<<< Temporary merge branch 1
=========
-- User roles (Admin, Lecturer, Student, Advisor)
>>>>>>>>> Temporary merge branch 2
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Users table with correct column names
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    matric_no VARCHAR(20) UNIQUE,
    staff_id VARCHAR(20) UNIQUE,
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    semester VARCHAR(10),
    year VARCHAR(10),
    lecturer_id INT NOT NULL,
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    max_mark DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE assessment_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    assessment_id INT NOT NULL,
    mark DECIMAL(5,2),
    UNIQUE (enrollment_id, assessment_id),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    FOREIGN KEY (assessment_id) REFERENCES assessments(id)
);

CREATE TABLE final_exam_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    mark DECIMAL(5,2),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    UNIQUE (enrollment_id)
);

CREATE TABLE feedback_remarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    remark TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- New table for detailed mark update notifications
CREATE TABLE mark_update_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_id INT NOT NULL,
    assessment_id INT NULL,
    is_final_exam BOOLEAN DEFAULT FALSE,
    old_mark DECIMAL(5,2) NULL,
    new_mark DECIMAL(5,2) NOT NULL,
    change_reason TEXT,
    acknowledged BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    FOREIGN KEY (assessment_id) REFERENCES assessments(id)
);

CREATE TABLE advisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_advisor_student (advisor_id, student_id),
    FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create a dedicated meetings table for advisor-student meetings
-- This is much cleaner than trying to use advisor_notes table
CREATE TABLE IF NOT EXISTS meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
    title VARCHAR(255) NOT NULL DEFAULT 'Advisor Meeting',
    meeting_date DATE NOT NULL,
    meeting_time TIME NOT NULL,
    duration INT DEFAULT 60, -- Duration in minutes
    location VARCHAR(255) DEFAULT '',
    meeting_link VARCHAR(500) DEFAULT '', -- For virtual meetings (Zoom, Teams, etc.)
    meeting_type ENUM('academic', 'career', 'personal', 'crisis', 'routine') DEFAULT 'academic',
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    agenda TEXT,
    notes TEXT,
    action_items TEXT,
    next_meeting_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for better performance
    INDEX idx_advisor_id (advisor_id),
    INDEX idx_student_id (student_id),
    INDEX idx_meeting_date (meeting_date),
    INDEX idx_status (status)
);

CREATE TABLE advisor_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

CREATE TABLE remark_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
);

CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert roles
INSERT INTO roles (name) VALUES 
('admin'), 
('lecturer'), 
('student'), 
('advisor');

-- Insert sample users (password is 'password123' for all users)
INSERT INTO users (matric_no, staff_id, full_name, email, password_hash, role_id) VALUES
(NULL, 'ADMIN001', 'Admin User', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(NULL, 'LEC001', 'Dr. John Smith', 'john.smith@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
(NULL, 'LEC002', 'Dr. Sarah Johnson', 'sarah.johnson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('STU001', NULL, 'Alice Brown', 'alice.brown@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU002', NULL, 'Bob Wilson', 'bob.wilson@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU003', NULL, 'Charlie Davis', 'charlie.davis@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
(NULL, 'ADV001', 'Prof. Mary Taylor', 'mary.taylor@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
(NULL, 'ADV002', 'Dr. James Wilson', 'james.wilson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
(NULL, 'ADV003', 'Prof. Lisa Chen', 'lisa.chen@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
(NULL, 'ADV004', 'Dr. Michael Brown', 'michael.brown@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4);

-- Insert sample courses
INSERT INTO courses (code, name, semester, year, lecturer_id) VALUES
('CS101', 'Introduction to Computer Science', 'Fall', '2024/2025', 2),
('CS102', 'Data Structures and Algorithms', 'Spring', '2024/2025', 2),
('MATH201', 'Calculus II', 'Fall', '2024/2025', 3);

-- Insert sample enrollments
INSERT INTO enrollments (student_id, course_id) VALUES
(4, 1), -- Alice in CS101
(5, 1), -- Bob in CS101
(6, 1), -- Charlie in CS101
(4, 2), -- Alice in CS102
(5, 2), -- Bob in CS102
(4, 3), -- Alice in MATH201
(6, 3); -- Charlie in MATH201

-- Insert sample assessments
INSERT INTO assessments (course_id, name, weight, max_mark) VALUES
-- CS101 assessments
(1, 'Assignment 1', 15.00, 100.00),
(1, 'Assignment 2', 15.00, 100.00),
(1, 'Midterm Exam', 25.00, 100.00),
(1, 'Project', 15.00, 100.00),
-- CS102 assessments
(2, 'Lab Assignment 1', 10.00, 100.00),
(2, 'Lab Assignment 2', 10.00, 100.00),
(2, 'Quiz 1', 15.00, 100.00),
(2, 'Quiz 2', 15.00, 100.00),
(2, 'Midterm Exam', 20.00, 100.00),
-- MATH201 assessments
(3, 'Homework 1', 10.00, 100.00),
(3, 'Homework 2', 10.00, 100.00),
(3, 'Test 1', 25.00, 100.00),
(3, 'Test 2', 25.00, 100.00);

-- Insert sample assessment marks
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
-- Alice's marks in CS101 (enrollment_id = 1)
(1, 1, 85.00), -- Assignment 1
(1, 2, 90.00), -- Assignment 2
(1, 3, 78.00), -- Midterm Exam
(1, 4, 88.00), -- Project
-- Bob's marks in CS101 (enrollment_id = 2)
(2, 1, 75.00), -- Assignment 1
(2, 2, 80.00), -- Assignment 2
(2, 3, 72.00), -- Midterm Exam
(2, 4, 82.00), -- Project
-- Charlie's marks in CS101 (enrollment_id = 3)
(3, 1, 92.00), -- Assignment 1
(3, 2, 88.00), -- Assignment 2
(3, 3, 85.00), -- Midterm Exam
(3, 4, 90.00), -- Project
-- Alice's marks in CS102 (enrollment_id = 4)
(4, 5, 88.00), -- Lab Assignment 1
(4, 6, 85.00), -- Lab Assignment 2
(4, 7, 90.00), -- Quiz 1
(4, 8, 87.00), -- Quiz 2
(4, 9, 83.00); -- Midterm Exam

-- Insert sample final exam marks
INSERT INTO final_exam_marks (enrollment_id, mark) VALUES
(1, 80.00), -- Alice CS101
(2, 75.00), -- Bob CS101
(3, 88.00), -- Charlie CS101
(4, 85.00), -- Alice CS102
(5, 78.00); -- Bob CS102

-- Insert advisor relationships
INSERT INTO advisors (advisor_id, student_id) VALUES
(7, 4), -- Prof. Mary Taylor advises Alice
(7, 5), -- Prof. Mary Taylor advises Bob
(7, 6), -- Prof. Mary Taylor advises Charlie
-- Additional advisor relationships for advisor ID 10 (if exists)
(10, 4), -- Additional advisor for Alice
(10, 5), -- Additional advisor for Bob  
(10, 6); -- Additional advisor for Charlie

-- Insert some sample meetings for testing
INSERT IGNORE INTO meetings (advisor_id, student_id, title, meeting_date, meeting_time, duration, location, meeting_type, status, agenda, notes) VALUES
(7, 4, 'Academic Progress Review', '2024-12-25', '10:00:00', 60, 'Office A101', 'academic', 'scheduled', 'Review semester progress and plan for next semester', ''),
(7, 4, 'Career Planning Session', '2024-12-30', '14:00:00', 90, 'Office A101', 'career', 'scheduled', 'Discuss internship opportunities and career goals', ''),
(7, 5, 'Check-in Meeting', '2024-12-27', '11:00:00', 45, 'Office A101', 'routine', 'completed', 'Regular student check-in and support', 'Student is doing well, discussed study habits and time management'),
(7, 5, 'Academic Support', '2025-01-05', '14:30:00', 60, 'Virtual Meeting', 'academic', 'scheduled', 'Review midterm performance and improvement strategies', ''),
(7, 6, 'Internship Discussion', '2024-12-28', '09:00:00', 75, 'Office A101', 'career', 'completed', 'Discuss summer internship applications and preparation', 'Provided recommendation letter, discussed interview tips'),
(7, 6, 'Semester Planning', '2025-01-10', '16:00:00', 60, 'Office A101', 'academic', 'scheduled', 'Plan course selection for next semester', '');

-- Insert sample feedback
INSERT INTO feedback_remarks (enrollment_id, remark) VALUES
(1, 'Excellent work on assignments. Keep up the good performance!'),
(2, 'Good effort, but please focus more on exam preparation.'),
(3, 'Outstanding student with consistent high performance.');

-- Insert sample notifications
INSERT INTO notifications (user_id, message, is_read) VALUES
(4, 'Your CS101 Assignment 1 mark has been uploaded.', TRUE),
(4, 'New feedback available for CS101.', FALSE),
(5, 'Your CS101 midterm result is now available.', FALSE),
(6, 'Welcome to the SkorSiswa system!', TRUE);

-- Insert sample advisor notes
INSERT INTO advisor_notes (advisor_id, student_id, course_id, note) VALUES
(7, 4, 1, 'Alice shows excellent academic performance. Discussed career paths in computer science.'),
(7, 4, 1, 'Alice expressed interest in AI/ML. Recommended advanced courses for next semester.'),
(7, 5, 1, 'Bob needs to improve time management. Suggested study techniques and resources.'),
(7, 5, 1, 'Follow-up meeting with Bob - showing improvement in recent assignments.'),
(7, 6, 1, 'Charlie is doing well overall. Discussed internship opportunities.'),
(7, 6, 1, 'Charlie completed summer internship application. Provided recommendation letter.');

-- Insert sample remark requests
INSERT INTO remark_requests (enrollment_id, reason, status) VALUES
(1, 'I believe there was an error in the calculation of my midterm exam grade. Could you please review question #3?', 'pending'),
(2, 'The final exam mark seems inconsistent with my performance throughout the semester. Request for re-evaluation.', 'approved'),
(3, 'Missing marks for Assignment 2 - I submitted it on time but it shows as not graded.', 'rejected'),
(4, 'Request to review Lab Assignment 1 grading criteria. Some points seem to have been deducted incorrectly.', 'pending'),
(5, 'Quiz 1 grade appears to be lower than expected. Please verify the answer key for question 5.', 'approved');

-- Insert sample system logs
INSERT INTO system_logs (user_id, action) VALUES
(1, 'Admin logged into the system'),
(2, 'Lecturer logged in'),
(4, 'Student logged in'),
(7, 'Advisor logged in');

-- Debugging queries to verify advisor relationships
-- Uncomment these lines to check user IDs and relationships:
-- SELECT 'Advisors:' as type, id, full_name, role_id FROM users WHERE role_id = 4;
-- SELECT 'Students:' as type, id, full_name, role_id FROM users WHERE role_id = 3;
-- SELECT 'Advisor Relationships:' as info;
-- SELECT a.id, u1.full_name as advisor, u2.full_name as student FROM advisors a
-- JOIN users u1 ON a.advisor_id = u1.id
-- JOIN users u2 ON a.student_id = u2.id;
