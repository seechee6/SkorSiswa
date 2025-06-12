-- -- User roles (Admin, Lecturer, Student, Advisor)
-- CREATE TABLE roles (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     name VARCHAR(50) NOT NULL UNIQUE
-- );

-- -- Insert default roles
-- INSERT INTO roles (name) VALUES
-- ('Student'),
-- ('Lecturer'),
-- ('Advisor'),
-- ('Admin')
-- ON DUPLICATE KEY UPDATE name = VALUES(name);

-- -- Users table
-- CREATE TABLE users (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     name VARCHAR(100) NOT NULL,
--     matric_no VARCHAR(20) UNIQUE,
--     email VARCHAR(100) UNIQUE,
--     password_hash VARCHAR(255) NOT NULL,
--     role_id INT NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (role_id) REFERENCES roles(id)
-- );

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    matric_no VARCHAR(20) UNIQUE,      -- For students (nullable for staff)
    staff_no VARCHAR(20) UNIQUE,       -- For staff (nullable for students)
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Student', 'Lecturer', 'Advisor', 'Admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    lecturer_id INT NOT NULL,
    semester VARCHAR(20),
    year INT,
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

-- Enrollments (students in courses)
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Assessment components (Quiz, Assignment, etc.)
CREATE TABLE assessment_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    weight DECIMAL(5,2) NOT NULL, -- percentage weight
    max_mark DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Marks for each component
CREATE TABLE marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    component_id INT NOT NULL,
    mark DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    FOREIGN KEY (component_id) REFERENCES assessment_components(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Advisor notes
CREATE TABLE advisor_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (advisor_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);
