-- User roles (Admin, Lecturer, Student, Advisor)
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
    UNIQUE (advisor_id, student_id),
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
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
(NULL, 'ADMIN001', 'Admin User', 'admin@university.edu', '$2y$10$9r3tl78wN6hYPVY03rtmmOmsIs965dxO4r1Q78.kRQIGn8dEOnAqW', 1),
(NULL, 'LEC001', 'Dr. John Smith', 'john.smith@university.edu', '$2y$10$GZwTEixkt4X.NcYVkxQCVOei37DX.oza/KpwKpKrYF/6bkAJP9HF2', 2),
(NULL, 'LEC002', 'Dr. Sarah Johnson', 'sarah.johnson@university.edu', '$2y$10$GZwTEixkt4X.NcYVkxQCVOei37DX.oza/KpwKpKrYF/6bkAJP9HF2', 2),
('STU001', NULL, 'Alice Brown', 'alice.brown@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU002', NULL, 'Bob Wilson', 'bob.wilson@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU003', NULL, 'Charlie Davis', 'charlie.davis@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU004', NULL, 'Diana Evans', 'diana.evans@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU005', NULL, 'Edward Foster', 'edward.foster@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU006', NULL, 'Fiona Grant', 'fiona.grant@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU007', NULL, 'George Harris', 'george.harris@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU008', NULL, 'Hannah Irving', 'hannah.irving@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU009', NULL, 'Ian Jackson', 'ian.jackson@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
('STU010', NULL, 'Julia King', 'julia.king@student.university.edu', '$2y$10$N5rH/JKrqvN2SENF22myTOSTT0c2XYHYzgv4EyRM81e80mf7cg8BO', 3),
(NULL, 'ADV001', 'Prof. Mary Taylor', 'mary.taylor@university.edu', '$2y$10$3x0HjENbRTpgnEq8lkmxA.Iu.TO6beQd.f.OZHBmOIwV1aWIrAMoS', 4);

-- Insert sample courses
INSERT INTO courses (code, name, semester, year, lecturer_id) VALUES
('CS101', 'Introduction to Computer Science', 'Fall', '2024/2025', 2),
('CS102', 'Data Structures and Algorithms', 'Spring', '2024/2025', 2),
('MATH201', 'Calculus II', 'Fall', '2024/2025', 3),
('CS203', 'Database Systems', 'Fall', '2024/2025', 2),
('CS301', 'Software Engineering', 'Spring', '2024/2025', 3),
('MATH101', 'Linear Algebra', 'Fall', '2024/2025', 3);

-- Insert sample enrollments
INSERT INTO enrollments (student_id, course_id) VALUES
(4, 1), -- Alice in CS101
(5, 1), -- Bob in CS101
(6, 1), -- Charlie in CS101
(4, 2), -- Alice in CS102
(5, 2), -- Bob in CS102
(4, 3), -- Alice in MATH201
(6, 3), -- Charlie in MATH201
(7, 1), -- Diana in CS101
(8, 1), -- Edward in CS101
(9, 1), -- Fiona in CS101
(10, 1), -- George in CS101
(11, 1), -- Hannah in CS101
(12, 1), -- Ian in CS101
(13, 1), -- Julia in CS101
(7, 2), -- Diana in CS102
(8, 2), -- Edward in CS102
(9, 2), -- Fiona in CS102
(6, 2), -- Charlie in CS102
(10, 3), -- George in MATH201
(11, 3), -- Hannah in MATH201
(12, 3), -- Ian in MATH201
(13, 3), -- Julia in MATH201
(4, 4), -- Alice in CS203
(5, 4), -- Bob in CS203
(6, 4), -- Charlie in CS203
(7, 4), -- Diana in CS203
(8, 4), -- Edward in CS203
(9, 4), -- Fiona in CS203
(10, 5), -- George in CS301
(11, 5), -- Hannah in CS301
(12, 5), -- Ian in CS301
(13, 5), -- Julia in CS301
(7, 6), -- Diana in MATH101
(8, 6), -- Edward in MATH101
(9, 6); -- Fiona in MATH101

-- Insert sample assessments
INSERT INTO assessments (course_id, name, weight, max_mark) VALUES
-- CS101 assessments
(1, 'Assignment 1', 15.00, 100.00),
(1, 'Assignment 2', 15.00, 100.00),
(1, 'Midterm Exam', 25.00, 100.00),
(1, 'Project', 15.00, 100.00),
(1, 'Final Exam', 30.00, 100.00),
-- CS102 assessments
(2, 'Lab Assignment 1', 10.00, 100.00),
(2, 'Lab Assignment 2', 10.00, 100.00),
(2, 'Quiz 1', 15.00, 100.00),
(2, 'Quiz 2', 15.00, 100.00),
(2, 'Midterm Exam', 20.00, 100.00),
(2, 'Final Exam', 30.00, 100.00),
-- MATH201 assessments
(3, 'Homework 1', 10.00, 100.00),
(3, 'Homework 2', 10.00, 100.00),
(3, 'Test 1', 25.00, 100.00),
(3, 'Test 2', 25.00, 100.00),
(3, 'Final Exam', 30.00, 100.00),
-- CS203 assessments
(4, 'Assignment 1', 10.00, 100.00),
(4, 'Assignment 2', 10.00, 100.00),
(4, 'Database Project', 25.00, 100.00),
(4, 'Midterm Exam', 25.00, 100.00),
(4, 'Final Exam', 30.00, 100.00),
-- CS301 assessments
(5, 'Team Project Phase 1', 15.00, 100.00),
(5, 'Team Project Phase 2', 15.00, 100.00),
(5, 'Team Project Final', 30.00, 100.00),
(5, 'Midterm Exam', 20.00, 100.00),
(5, 'Final Exam', 20.00, 100.00),
-- MATH101 assessments
(6, 'Problem Set 1', 10.00, 100.00),
(6, 'Problem Set 2', 10.00, 100.00),
(6, 'Problem Set 3', 10.00, 100.00),
(6, 'Midterm Exam', 30.00, 100.00),
(6, 'Final Exam', 40.00, 100.00);

-- Insert sample assessment marks
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
-- Alice's marks in CS101 (enrollment_id = 1)
(1, 1, 85.00), -- Assignment 1
(1, 2, 90.00), -- Assignment 2
(1, 3, 78.00), -- Midterm Exam
(1, 4, 88.00), -- Project
(1, 5, 82.00), -- Final Exam
-- Bob's marks in CS101 (enrollment_id = 2)
(2, 1, 75.00), -- Assignment 1
(2, 2, 80.00), -- Assignment 2
(2, 3, 72.00), -- Midterm Exam
(2, 4, 82.00), -- Project
(2, 5, 78.00), -- Final Exam
-- Charlie's marks in CS101 (enrollment_id = 3)
(3, 1, 92.00), -- Assignment 1
(3, 2, 88.00), -- Assignment 2
(3, 3, 85.00), -- Midterm Exam
(3, 4, 90.00), -- Project
(3, 5, 88.00), -- Final Exam
-- Alice's marks in CS102 (enrollment_id = 4)
(4, 6, 88.00), -- Lab Assignment 1
(4, 7, 85.00), -- Lab Assignment 2
(4, 8, 90.00), -- Quiz 1
(4, 9, 87.00), -- Quiz 2
(4, 10, 83.00), -- Midterm Exam
(4, 11, 85.00), -- Final Exam
-- Bob's marks in CS102 (enrollment_id = 5)
(5, 6, 78.00), -- Lab Assignment 1
(5, 7, 75.00), -- Lab Assignment 2
(5, 8, 82.00), -- Quiz 1
(5, 9, 80.00), -- Quiz 2
(5, 10, 76.00), -- Midterm Exam
(5, 11, 78.00), -- Final Exam
-- Charlie's marks in CS102 (enrollment_id = 18)
(18, 6, 90.00), -- Lab Assignment 1
(18, 7, 88.00), -- Lab Assignment 2
(18, 8, 85.00), -- Quiz 1
(18, 9, 92.00), -- Quiz 2
(18, 10, 88.00), -- Midterm Exam
(18, 11, 90.00), -- Final Exam
-- Diana's marks in CS101 (enrollment_id = 8)
(8, 1, 72.00), -- Assignment 1
(8, 2, 78.00), -- Assignment 2
(8, 3, 68.00), -- Midterm Exam
(8, 4, 75.00), -- Project
(8, 5, 70.00), -- Final Exam
-- Edward's marks in CS101 (enrollment_id = 9)
(9, 1, 95.00), -- Assignment 1
(9, 2, 92.00), -- Assignment 2
(9, 3, 90.00), -- Midterm Exam
(9, 4, 94.00), -- Project
(9, 5, 92.00), -- Final Exam
-- Fiona's marks in CS101 (enrollment_id = 10)
(10, 1, 65.00), -- Assignment 1
(10, 2, 70.00), -- Assignment 2
(10, 3, 62.00), -- Midterm Exam
(10, 4, 68.00), -- Project
(10, 5, 65.00), -- Final Exam
-- George's marks in CS101 (enrollment_id = 11)
(11, 1, 88.00), -- Assignment 1
(11, 2, 85.00), -- Assignment 2
(11, 3, 80.00), -- Midterm Exam
(11, 4, 82.00), -- Project
(11, 5, 80.00), -- Final Exam
-- Hannah's marks in CS101 (enrollment_id = 12)
(12, 1, 78.00), -- Assignment 1
(12, 2, 75.00), -- Assignment 2
(12, 3, 72.00), -- Midterm Exam
(12, 4, 70.00), -- Project
(12, 5, 75.00), -- Final Exam
-- Ian's marks in CS101 (enrollment_id = 13)
(13, 1, 55.00), -- Assignment 1
(13, 2, 58.00), -- Assignment 2
(13, 3, 52.00), -- Midterm Exam
(13, 4, 60.00), -- Project
(13, 5, 55.00), -- Final Exam
-- Julia's marks in CS101 (enrollment_id = 14)
(14, 1, 82.00), -- Assignment 1
(14, 2, 85.00), -- Assignment 2
(14, 3, 80.00), -- Midterm Exam
(14, 4, 78.00), -- Project
(14, 5, 82.00), -- Final Exam
-- Alice's marks in MATH201 (enrollment_id = 6)
(6, 12, 75.00), -- Homework 1
(6, 13, 80.00), -- Homework 2
(6, 14, 72.00), -- Test 1
(6, 15, 78.00), -- Test 2
(6, 16, 75.00), -- Final Exam
-- Charlie's marks in MATH201 (enrollment_id = 7)
(7, 12, 90.00), -- Homework 1
(7, 13, 88.00), -- Homework 2
(7, 14, 85.00), -- Test 1
(7, 15, 90.00), -- Test 2
(7, 16, 92.00), -- Final Exam
-- Marks for CS203 (Database Systems)
(22, 17, 85.00), -- Alice Assignment 1
(22, 18, 88.00), -- Alice Assignment 2
(22, 19, 90.00), -- Alice Database Project
(22, 20, 82.00), -- Alice Midterm Exam
(22, 21, 85.00), -- Alice Final Exam
(23, 17, 75.00), -- Bob Assignment 1
(23, 18, 78.00), -- Bob Assignment 2
(23, 19, 80.00), -- Bob Database Project
(23, 20, 75.00), -- Bob Midterm Exam
(23, 21, 78.00), -- Bob Final Exam
(24, 17, 90.00), -- Charlie Assignment 1
(24, 18, 92.00), -- Charlie Assignment 2
(24, 19, 95.00), -- Charlie Database Project
(24, 20, 88.00), -- Charlie Midterm Exam
(24, 21, 90.00), -- Charlie Final Exam
-- MATH101 marks
(33, 27, 85.00), -- Diana Problem Set 1
(33, 28, 82.00), -- Diana Problem Set 2
(33, 29, 80.00), -- Diana Problem Set 3
(33, 30, 78.00), -- Diana Midterm Exam
(33, 31, 80.00), -- Diana Final Exam
(34, 27, 92.00), -- Edward Problem Set 1
(34, 28, 90.00), -- Edward Problem Set 2
(34, 29, 88.00), -- Edward Problem Set 3
(34, 30, 85.00), -- Edward Midterm Exam
(34, 31, 88.00), -- Edward Final Exam
(35, 27, 72.00), -- Fiona Problem Set 1
(35, 28, 70.00), -- Fiona Problem Set 2
(35, 29, 68.00), -- Fiona Problem Set 3
(35, 30, 65.00), -- Fiona Midterm Exam
(35, 31, 70.00);  -- Fiona Final Exam

-- Insert sample final exam marks
INSERT INTO final_exam_marks (enrollment_id, mark) VALUES
(1, 82.00),  -- Alice CS101
(2, 78.00),  -- Bob CS101
(3, 88.00),  -- Charlie CS101
(4, 85.00),  -- Alice CS102
(5, 78.00),  -- Bob CS102
(18, 90.00), -- Charlie CS102
(6, 75.00),  -- Alice MATH201
(7, 92.00),  -- Charlie MATH201
(8, 70.00),  -- Diana CS101
(9, 92.00),  -- Edward CS101
(10, 65.00), -- Fiona CS101
(11, 80.00), -- George CS101
(12, 75.00), -- Hannah CS101
(13, 55.00), -- Ian CS101
(14, 82.00), -- Julia CS101
(22, 85.00), -- Alice CS203
(23, 78.00), -- Bob CS203
(24, 90.00), -- Charlie CS203
(33, 80.00), -- Diana MATH101
(34, 88.00), -- Edward MATH101
(35, 70.00); -- Fiona MATH101

-- Insert advisor relationships
INSERT INTO advisors (advisor_id, student_id) VALUES
(7, 4),  -- Prof. Mary Taylor advises Alice
(7, 5),  -- Prof. Mary Taylor advises Bob
(7, 6),  -- Prof. Mary Taylor advises Charlie
(7, 7),  -- Prof. Mary Taylor advises Diana
(7, 8),  -- Prof. Mary Taylor advises Edward
(7, 9),  -- Prof. Mary Taylor advises Fiona
(7, 10), -- Prof. Mary Taylor advises George
(7, 11), -- Prof. Mary Taylor advises Hannah
(7, 12), -- Prof. Mary Taylor advises Ian
(7, 13); -- Prof. Mary Taylor advises Julia

-- Insert sample feedback
INSERT INTO feedback_remarks (enrollment_id, remark) VALUES
(1, 'Excellent work on assignments. Keep up the good performance!'),
(2, 'Good effort, but please focus more on exam preparation.'),
(3, 'Outstanding student with consistent high performance.'),
(4, 'Your lab work is excellent, continue to apply these skills to real-world problems.'),
(5, 'Needs to improve understanding of core data structure concepts.'),
(6, 'Your math skills are improving, keep practicing!'),
(7, 'Excellent grasp of mathematical concepts.'),
(8, 'You need to attend more office hours for extra help.'),
(9, 'Exceptional work throughout the semester.'),
(10, 'Please seek tutoring support to improve your understanding.'),
(11, 'Good consistent performance throughout the semester.'),
(18, 'Excellent progress in programming concepts!'),
(22, 'Your database design skills are excellent.'),
(24, 'Outstanding database project. Consider taking advanced DB courses.');

-- Insert sample notifications
INSERT INTO notifications (user_id, message, is_read) VALUES
(4, 'Your CS101 Assignment 1 mark has been uploaded.', TRUE),
(4, 'New feedback available for CS101.', FALSE),
(4, 'Your CS102 Final Exam result is now available.', FALSE),
(4, 'Reminder: CS203 Database Project due next week.', FALSE),
(5, 'Your CS101 midterm result is now available.', FALSE),
(5, 'New feedback available for CS102.', FALSE),
(5, 'CS101 final grades have been released.', FALSE),
(6, 'Welcome to the SkorSiswa system!', TRUE),
(6, 'Your MATH201 Test 2 grade has been updated.', FALSE),
(6, 'Congratulations on your excellent performance in CS101.', FALSE),
(7, 'Your CS101 marks have been uploaded.', FALSE),
(7, 'Please submit your missing assignment for CS102.', FALSE),
(8, 'Excellent work on your CS101 project!', FALSE),
(9, 'Your MATH101 Problem Set 3 requires revision.', FALSE),
(10, 'Your CS101 final grade has been posted.', FALSE),
(11, 'New feedback available from your advisor.', FALSE),
(12, 'CS301 Team Project Phase 2 grade now available.', FALSE),
(13, 'You have been enrolled in a new course: CS301.', FALSE);

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
(5, 'Quiz 1 grade appears to be lower than expected. Please verify the answer key for question 5.', 'approved'),
(6, 'I believe my solution to problem 4 in Test 1 deserves more points as it meets all the requirements.', 'pending'),
(7, 'Test 2 grade is significantly lower than expected. Request for review of entire exam.', 'approved'),
(8, 'My final project submission wasn\'t graded properly. I implemented all required features.', 'pending'),
(9, 'There seems to be a calculation error in my final grade. Please recalculate.', 'pending'),
(10, 'My Assignment 2 submission was affected by technical issues. Please consider this in the grading.', 'rejected'),
(18, 'I believe my implementation for Quiz 2 was correct but marked as wrong. Please review.', 'pending');

-- Insert sample system logs
INSERT INTO system_logs (user_id, action) VALUES
(1, 'Admin logged into the system'),
(2, 'Lecturer logged in'),
(4, 'Student logged in'),
(7, 'Advisor logged in');
