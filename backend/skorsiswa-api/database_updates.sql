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
