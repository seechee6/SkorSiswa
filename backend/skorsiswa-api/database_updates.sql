-- Multi-advisor database update script
-- This demonstrates the multi-advisor functionality

-- Add more advisor users
INSERT INTO users (matric_no, staff_id, full_name, email, password_hash, role_id) VALUES
(NULL, 'A002', 'Dr. Jennifer Lee', 'jennifer.lee@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
(NULL, 'A003', 'Prof. Michael Chen', 'michael.chen@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
(NULL, 'A004', 'Dr. Sarah Williams', 'sarah.williams@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4);

-- Add more student users for testing
INSERT INTO users (matric_no, staff_id, full_name, email, password_hash, role_id) VALUES
('CS2021001', NULL, 'Emily Johnson', 'emily.johnson@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('IS2022003', NULL, 'Robert Smith', 'robert.smith@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('SE2020005', NULL, 'Jessica Davis', 'jessica.davis@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('IT2023007', NULL, 'David Wilson', 'david.wilson@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('CS2021009', NULL, 'Amanda Brown', 'amanda.brown@student.university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);

-- Add advisor relationships for multiple advisors
-- These relationships define which advisor supervises which students

-- Prof. Mary Taylor (A001) - ID 7
INSERT INTO advisors (advisor_id, student_id) VALUES
(7, 8), -- Emily Johnson
(7, 9), -- Robert Smith
(7, 10); -- Jessica Davis

-- Dr. Jennifer Lee (A002) - ID 8
INSERT INTO advisors (advisor_id, student_id) VALUES
(8, 11), -- David Wilson  
(8, 12); -- Amanda Brown

-- Prof. Michael Chen (A003) - ID 9
INSERT INTO advisors (advisor_id, student_id) VALUES
(9, 4), -- Alice Brown (existing student)
(9, 5); -- Bob Wilson (existing student)

-- Dr. Sarah Williams (A004) - ID 10
INSERT INTO advisors (advisor_id, student_id) VALUES
(10, 6); -- Charlie Davis (existing student)

-- =============================================
-- ORIGINAL CONTENT PRESERVED BELOW
-- =============================================

-- Assessment Components
CREATE TABLE assessment_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    due_date DATE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Student Marks
CREATE TABLE student_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    component_id INT NOT NULL,
    marks_obtained DECIMAL(5,2),
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (component_id) REFERENCES assessment_components(id)
);

-- Remark Requests
CREATE TABLE remark_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    component_id INT NOT NULL,
    justification TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolver_id INT,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (component_id) REFERENCES assessment_components(id),
    FOREIGN KEY (resolver_id) REFERENCES users(id)
);

-- Course Statistics
CREATE TABLE course_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    component_id INT NOT NULL,
    academic_year INT NOT NULL,
    semester INT NOT NULL,
    class_average DECIMAL(5,2),
    highest_mark DECIMAL(5,2),
    lowest_mark DECIMAL(5,2),
    median_mark DECIMAL(5,2),
    std_deviation DECIMAL(5,2),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (component_id) REFERENCES assessment_components(id)
);

-- Create indexes for better performance
CREATE INDEX idx_student_marks ON student_marks(student_id, component_id);
CREATE INDEX idx_course_stats ON course_statistics(course_id, component_id);
CREATE INDEX idx_assessment_components ON assessment_components(course_id);

-- Add more diverse test marks for at-risk highlighting
-- First, let's add some more enrollments for our new students

-- Get course IDs (assuming CS101 is id=1, CS102 is id=2)
INSERT INTO enrollments (student_id, course_id, academic_year, semester) VALUES
-- Emily Johnson (high performer)
(8, 1, 2024, 1),
(8, 2, 2024, 1),
-- Robert Smith (at-risk due to low GPA)
(9, 1, 2024, 1),
(9, 2, 2024, 1),
-- Jessica Davis (average performer)
(10, 1, 2024, 1),
(10, 2, 2024, 1),
-- David Wilson (at-risk due to bottom 20%)
(11, 1, 2024, 1),
(11, 2, 2024, 1),
-- Amanda Brown (excellent performer)
(12, 1, 2024, 1),
(12, 2, 2024, 1);

-- Add assessment marks for diverse GPA range
-- Emily Johnson (high performer - GPA ~3.5-4.0)
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
-- Assuming enrollment IDs continue from 6 onwards
(6, 1, 92.00), -- Assignment 1 
(6, 2, 95.00), -- Assignment 2
(6, 3, 88.00), -- Midterm
(6, 4, 90.00), -- Project
(7, 5, 93.00), -- Lab Assignment 1
(7, 6, 91.00), -- Lab Assignment 2
(7, 7, 89.00), -- Quiz 1
(7, 8, 94.00), -- Quiz 2
(7, 9, 87.00); -- Midterm

-- Robert Smith (at-risk due to low GPA - GPA ~1.5)
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
(8, 1, 45.00), -- Assignment 1
(8, 2, 50.00), -- Assignment 2  
(8, 3, 40.00), -- Midterm
(8, 4, 48.00), -- Project
(9, 5, 43.00), -- Lab Assignment 1
(9, 6, 47.00), -- Lab Assignment 2
(9, 7, 52.00), -- Quiz 1
(9, 8, 44.00), -- Quiz 2
(9, 9, 49.00); -- Midterm

-- Jessica Davis (average performer - GPA ~2.7)
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
(10, 1, 70.00), -- Assignment 1
(10, 2, 68.00), -- Assignment 2
(10, 3, 65.00), -- Midterm
(10, 4, 72.00), -- Project
(11, 5, 69.00), -- Lab Assignment 1
(11, 6, 71.00), -- Lab Assignment 2
(11, 7, 66.00), -- Quiz 1
(11, 8, 73.00), -- Quiz 2
(11, 9, 67.00); -- Midterm

-- David Wilson (bottom 20% performer - GPA ~2.3)
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
(12, 1, 60.00), -- Assignment 1
(12, 2, 58.00), -- Assignment 2
(12, 3, 55.00), -- Midterm
(12, 4, 62.00), -- Project
(13, 5, 57.00), -- Lab Assignment 1
(13, 6, 59.00), -- Lab Assignment 2
(13, 7, 56.00), -- Quiz 1
(13, 8, 61.00), -- Quiz 2
(13, 9, 58.00); -- Midterm

-- Amanda Brown (excellent performer - GPA ~3.8)
INSERT INTO assessment_marks (enrollment_id, assessment_id, mark) VALUES
(14, 1, 96.00), -- Assignment 1
(14, 2, 94.00), -- Assignment 2
(14, 3, 92.00), -- Midterm
(14, 4, 98.00), -- Project
(15, 5, 95.00), -- Lab Assignment 1
(15, 6, 97.00), -- Lab Assignment 2
(15, 7, 93.00), -- Quiz 1
(15, 8, 96.00), -- Quiz 2
(15, 9, 91.00); -- Midterm

-- Add final exam marks
INSERT INTO final_exam_marks (enrollment_id, mark) VALUES
(6, 90.00),  -- Emily Johnson CS101
(7, 88.00),  -- Emily Johnson CS102
(8, 45.00),  -- Robert Smith CS101
(9, 42.00),  -- Robert Smith CS102
(10, 68.00), -- Jessica Davis CS101
(11, 70.00), -- Jessica Davis CS102
(12, 58.00), -- David Wilson CS101
(13, 56.00), -- David Wilson CS102
(14, 94.00), -- Amanda Brown CS101
(15, 92.00); -- Amanda Brown CS102
