-- Create advisor_reports table for storing generated reports
CREATE TABLE IF NOT EXISTS advisor_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    advisor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    type ENUM('comprehensive', 'summary', 'at-risk', 'progress') NOT NULL,
    student_count INT NOT NULL,
    format ENUM('pdf', 'excel', 'html') NOT NULL,
    report_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE
);
