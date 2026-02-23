-- Add users table for student/teacher/admin login (run this if you already have the DB and only need auth)
USE student_clearance;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    reference_id VARCHAR(20) DEFAULT NULL COMMENT 'LRN for student, teacher_id for teacher, NULL for admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_reference CHECK (
        (role = 'admin' AND reference_id IS NULL) OR
        (role IN ('student', 'teacher') AND reference_id IS NOT NULL AND reference_id != '')
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username 'admin', password 'password' (change in production)
INSERT IGNORE INTO users (username, password_hash, role, reference_id) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL);
