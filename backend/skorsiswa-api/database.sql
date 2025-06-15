CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('admin', 'lecturer', 'student', 'advisor') NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matric_no VARCHAR(20) UNIQUE,         -- For students
    staff_id VARCHAR(20) UNIQUE,          -- For staff (lecturers, advisors)
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    semester VARCHAR(10),
    year VARCHAR(10),  -- Changed from INT to VARCHAR(10) to support "2024/2025" format
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

CREATE TABLE advisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,  -- user.id of advisor
    student_id INT NOT NULL,  -- user.id of student
    UNIQUE (advisor_id, student_id),
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

CREATE TABLE advisor_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
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

-- Insert sample users (passwords are hashed for 'password123')
INSERT INTO users (matric_no, staff_id, full_name, email, password_hash, role_id) VALUES
(NULL, 'ADMIN001', 'Admin User', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(NULL, 'LEC001', 'Dr. John Smith', 'john.smith@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
(NULL, 'LEC002', 'Dr. Sarah Johnson', 'sarah.johnson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('STU001', NULL, 'Alice Brown', 'alice.brown@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU002', NULL, 'Bob Wilson', 'bob.wilson@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU003', NULL, 'Charlie Davis', 'charlie.davis@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
(NULL, 'ADV001', 'Prof. Mary Taylor', 'mary.taylor@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4);

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
(7, 6); -- Prof. Mary Taylor advises Charlie

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
INSERT INTO advisor_notes (advisor_id, student_id, note) VALUES
(7, 4, 'Alice shows excellent academic performance. Discussed career paths in computer science.'),
(7, 4, 'Alice expressed interest in AI/ML. Recommended advanced courses for next semester.'),
(7, 5, 'Bob needs to improve time management. Suggested study techniques and resources.'),
(7, 5, 'Follow-up meeting with Bob - showing improvement in recent assignments.'),
(7, 6, 'Charlie is doing well overall. Discussed internship opportunities.'),
(7, 6, 'Charlie completed summer internship application. Provided recommendation letter.');

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
(1, 'Created new user: Dr. John Smith'),
(1, 'Updated user role for student STU001'),
(1, 'Reset password for user ID: 5'),
(2, 'Lecturer logged in'),
(2, 'Created new course: CS101'),
(2, 'Added assessment: Assignment 1 for CS101'),
(2, 'Updated marks for student Alice Brown'),
(2, 'Exported marks for course CS101'),
(3, 'Lecturer logged in'),
(3, 'Updated course information for MATH201'),
(3, 'Added final exam marks for MATH201'),
(4, 'Student logged in'),
(4, 'Viewed course marks for CS101'),
(4, 'Submitted remark request for CS101'),
(5, 'Student logged in'),
(5, 'Used what-if simulator for grade calculation'),
(6, 'Student logged in'),
(6, 'Viewed class rankings for CS101'),
(7, 'Advisor logged in'),
(7, 'Added meeting note for student Alice Brown'),
(7, 'Generated advisee report'),
(1, 'Admin exported system activity logs'),
(2, 'Bulk uploaded marks via CSV for CS102'),
(4, 'Student viewed performance analytics'),
(5, 'Student compared marks with coursemates'),
(7, 'Advisor identified at-risk students'),
(3, 'Lecturer added feedback for multiple students'),
(1, 'Admin updated system settings'),
(2, 'Lecturer created new assessment for CS101'),
(6, 'Student requested grade rechecking'),
(7, 'Advisor scheduled meeting with student'),
(4, 'Student viewed notification about new feedback'),
(5, 'Student marked notifications as read');
