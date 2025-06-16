-- 1. Create the roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- 2. Insert roles
INSERT INTO roles (name) VALUES 
('admin'), 
('lecturer'), 
('student'), 
('advisor');

-- 3. Create the users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matric_no VARCHAR(20) UNIQUE,
    staff_id VARCHAR(20) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 4. Insert sample users (passwords are hashed for 'password123')
INSERT INTO users (matric_no, staff_id, full_name, email, password_hash, role_id) VALUES
(NULL, 'ADMIN001', 'Admin User', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(NULL, 'LEC001', 'Dr. John Smith', 'john.smith@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
(NULL, 'LEC002', 'Dr. Sarah Johnson', 'sarah.johnson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('STU001', NULL, 'Alice Brown', 'alice.brown@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU002', NULL, 'Bob Wilson', 'bob.wilson@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU003', NULL, 'Charlie Davis', 'charlie.davis@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
(NULL, 'ADV001', 'Prof. Mary Taylor', 'mary.taylor@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4);

-- 5. Create the courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    semester VARCHAR(10),
    year VARCHAR(10),
    lecturer_id INT NOT NULL,
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

-- 6. Insert sample courses
INSERT INTO courses (code, name, semester, year, lecturer_id) VALUES
('CS101', 'Introduction to Computer Science', 'Fall', '2024/2025', 2),
('CS102', 'Data Structures and Algorithms', 'Spring', '2024/2025', 2),
('MATH201', 'Calculus II', 'Fall', '2024/2025', 2);

-- 7. Create the enrollments table
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- 8. Insert sample enrollments
INSERT INTO enrollments (student_id, course_id) VALUES
(4, 1), -- Alice in CS101
(5, 1), -- Bob in CS101
(6, 1), -- Charlie in CS101
(4, 2), -- Alice in CS102
(5, 2), -- Bob in CS102
(4, 3), -- Alice in MATH201
(6, 3); -- Charlie in MATH201

-- 9. Create the assessments table
CREATE TABLE assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    max_mark DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- 10. Insert sample assessments
INSERT INTO assessments (course_id, name, weight, max_mark) VALUES
(1, 'Assignment 1', 15.00, 100.00),
(1, 'Assignment 2', 15.00, 100.00),
(1, 'Midterm Exam', 25.00, 100.00),
(1, 'Project', 15.00, 100.00),
(2, 'Lab Assignment 1', 10.00, 100.00),
(2, 'Lab Assignment 2', 10.00, 100.00),
(2, 'Quiz 1', 15.00, 100.00),
(2, 'Quiz 2', 15.00, 100.00),
(2, 'Midterm Exam', 20.00, 100.00),
(3, 'Homework 1', 10.00, 100.00),
(3, 'Homework 2', 10.00, 100.00),
(3, 'Test 1', 25.00, 100.00),
(3, 'Test 2', 25.00, 100.00);

-- 11. Create the assessment_marks table
CREATE TABLE assessment_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    assessment_id INT NOT NULL,
    mark DECIMAL(5,2),
    UNIQUE (enrollment_id, assessment_id),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    FOREIGN KEY (assessment_id) REFERENCES assessments(id)
);

-- 12. Insert sample assessment marks
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
(1, 1, 85.00),
(1, 2, 90.00),
(1, 3, 78.00),
(1, 4, 88.00),
(2, 1, 75.00),
(2, 2, 80.00),
(2, 3, 72.00),
(2, 4, 82.00),
(3, 1, 92.00),
(3, 2, 88.00),
(3, 3, 85.00),
(3, 4, 90.00);

-- 13. Create the final_exam_marks table
CREATE TABLE final_exam_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    mark DECIMAL(5,2),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    UNIQUE (enrollment_id)
);

-- 14. Insert sample final exam marks
INSERT INTO final_exam_marks (enrollment_id, mark) VALUES
(1, 80.00),
(2, 75.00),
(3, 88.00);

-- 15. Create the feedback_remarks table
CREATE TABLE feedback_remarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    remark TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
);

-- 16. Insert sample feedback remarks
INSERT INTO feedback_remarks (enrollment_id, remark) VALUES
(1, 'Excellent work on assignments. Keep up the good performance!'),
(2, 'Good effort, but please focus more on exam preparation.'),
(3, 'Outstanding student with consistent high performance.');

-- 17. Create the notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 18. Insert sample notifications
INSERT INTO notifications (user_id, message) VALUES
(4, 'Your CS101 Assignment 1 mark has been uploaded.'),
(4, 'New feedback available for CS101.'),
(5, 'Your CS101 midterm result is now available.'),
(6, 'Welcome to the SkorSiswa system!');

-- 19. Create the advisors table
CREATE TABLE advisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
    UNIQUE (advisor_id, student_id),
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- 20. Insert sample advisor relationships
INSERT INTO advisors (advisor_id, student_id) VALUES
(7, 4),
(7, 5),
(7, 6);

-- 21. Create the advisor_notes table
CREATE TABLE advisor_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- 22. Insert sample advisor notes
INSERT INTO advisor_notes (advisor_id, student_id, note) VALUES
(7, 4, 'Alice shows excellent academic performance. Discussed career paths in computer science.'),
(7, 5, 'Bob needs to improve time management. Suggested study techniques and resources.'),
(7, 6, 'Charlie is doing well overall. Discussed internship opportunities.');

-- 23. Create the remark_requests table
CREATE TABLE remark_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
);

-- 24. Insert sample remark requests
INSERT INTO remark_requests (enrollment_id, reason, status) VALUES
(1, 'I believe there was an error in the calculation of my midterm exam grade. Could you please review question #3?', 'pending'),
(2, 'The final exam mark seems inconsistent with my performance throughout the semester. Request for re-evaluation.', 'approved'),
(3, 'Missing marks for Assignment 2 - I submitted it on time but it shows as not graded.', 'rejected');

-- 25. Create the system_logs table
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 26. Insert sample system logs
INSERT INTO system_logs (user_id, action) VALUES
(1, 'Admin logged into the system'),
(2, 'Lecturer updated grades for CS101'),
(3, 'Student checked grades for CS101'),
(7, 'Advisor added meeting notes for student Alice Brown');
