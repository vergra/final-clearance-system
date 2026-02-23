-- Student sign-up requests table (run if you already have the DB and need sign-up feature)
USE student_clearance;

CREATE TABLE IF NOT EXISTS signup_requests (
    signup_request_id INT AUTO_INCREMENT PRIMARY KEY,
    lrn VARCHAR(20) NOT NULL,
    requested_username VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    remarks TEXT DEFAULT NULL,
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY unique_requested_username (requested_username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
