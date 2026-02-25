-- Senior High School Clearance System - Complete Schema with Compliance System
-- Generated: 2025-02-25
-- Includes all tables, indexes, and compliance tracking features

-- Drop existing tables if they exist (for clean migration)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS students_clearance_status;
DROP TABLE IF EXISTS students_requirement;
DROP TABLE IF EXISTS student_subject;
DROP TABLE IF EXISTS signup_requests;
DROP TABLE IF EXISTS clearance_status;
DROP TABLE IF EXISTS teacher_subject;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS requirements;
DROP TABLE IF EXISTS school_year;
DROP TABLE IF EXISTS strands;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS blocks;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Create departments table
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create teacher-subject assignment table (many-to-many)
-- (created later, after teachers/subjects/school_year tables)

-- Create strands table
CREATE TABLE strands (
    strand_id INT AUTO_INCREMENT PRIMARY KEY,
    strand_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create school years table
CREATE TABLE school_year (
    school_year_id INT AUTO_INCREMENT PRIMARY KEY,
    year_label VARCHAR(20) NOT NULL UNIQUE,
    start_date DATE,
    end_date DATE,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create blocks table
CREATE TABLE blocks (
    block_id INT AUTO_INCREMENT PRIMARY KEY,
    block_code VARCHAR(20) NOT NULL UNIQUE,
    grade_level VARCHAR(20),
    strand_id INT,
    department_id INT,
    max_students INT DEFAULT 40,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (strand_id) REFERENCES strands(strand_id),
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
);

-- Create users table for authentication
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    reference_id VARCHAR(50), -- Links to teacher_id or student_lrn
    email VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- Create teachers table
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    surname VARCHAR(50) NOT NULL,
    given_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    contact_number VARCHAR(20),
    department_id INT,
    employment_status ENUM('permanent', 'temporary', 'part-time') DEFAULT 'permanent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    INDEX idx_name (surname, given_name),
    INDEX idx_department (department_id)
);

-- Create students table
CREATE TABLE students (
    lrn VARCHAR(12) PRIMARY KEY,
    surname VARCHAR(50) NOT NULL,
    given_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    email VARCHAR(100),
    contact_number VARCHAR(20),
    block_code VARCHAR(20),
    strand VARCHAR(50),
    grade_level VARCHAR(20),
    enrollment_status ENUM('enrolled', 'transferred', 'graduated', 'dropped') DEFAULT 'enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (block_code) REFERENCES blocks(block_code),
    INDEX idx_name (surname, given_name),
    INDEX idx_block (block_code),
    INDEX idx_strand (strand)
);

-- Create signup requests table (student requests for account approval)
CREATE TABLE signup_requests (
    signup_request_id INT AUTO_INCREMENT PRIMARY KEY,
    lrn VARCHAR(12) NOT NULL,
    requested_username VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    remarks TEXT DEFAULT NULL,
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY unique_requested_username (requested_username),
    INDEX idx_signup_status (status),
    INDEX idx_signup_requested_at (requested_at)
);

-- Create subjects table
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) UNIQUE,
    grade_level VARCHAR(20),
    strand_id INT,
    department_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (strand_id) REFERENCES strands(strand_id),
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    INDEX idx_name (subject_name),
    INDEX idx_grade (grade_level)
);

-- Create requirements table
CREATE TABLE requirements (
    requirement_id INT AUTO_INCREMENT PRIMARY KEY,
    requirement_name VARCHAR(100) NOT NULL,
    description TEXT,
    requirement_type ENUM('academic', 'administrative', 'financial', 'disciplinary') DEFAULT 'administrative',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (requirement_name),
    INDEX idx_type (requirement_type)
);

-- Create teacher-subject assignment table (many-to-many)
-- (created later, after teachers/subjects/school_year tables)

-- Create student-subject assignment table (enrollment)
CREATE TABLE student_subject (
    student_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    lrn VARCHAR(12) NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT DEFAULT NULL,
    school_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE SET NULL,
    FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (lrn, subject_id, school_year_id),
    INDEX idx_student_subject_lrn (lrn),
    INDEX idx_student_subject_subject (subject_id),
    INDEX idx_student_subject_school_year (school_year_id),
    INDEX idx_student_subject_teacher (teacher_id)
);

-- Create teacher-subject assignment table (many-to-many)
CREATE TABLE teacher_subject (
    teacher_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject_year (teacher_id, subject_id, school_year_id),
    INDEX idx_teacher_subject_teacher (teacher_id),
    INDEX idx_teacher_subject_subject (subject_id),
    INDEX idx_teacher_subject_school_year (school_year_id)
);

-- Create clearance_status table with compliance tracking
CREATE TABLE clearance_status (
    clearance_id INT AUTO_INCREMENT PRIMARY KEY,
    lrn VARCHAR(12) NOT NULL,
    requirement_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year_id INT NOT NULL,
    status ENUM('Pending', 'Approved', 'Declined') DEFAULT 'Pending',
    date_submitted DATE,
    date_cleared DATE, -- When action was taken (approved or returned)
    date_returned DATE, -- When specifically returned for compliance
    remarks TEXT, -- Teacher's compliance requirements
    request_group_id VARCHAR(50), -- Groups multiple clearances submitted together
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (lrn) REFERENCES students(lrn),
    FOREIGN KEY (requirement_id) REFERENCES requirements(requirement_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id),
    
    -- Indexes for performance
    INDEX idx_student (lrn),
    INDEX idx_teacher (teacher_id),
    INDEX idx_status (status),
    INDEX idx_school_year (school_year_id),
    INDEX idx_request_group (request_group_id),
    INDEX idx_dates (date_submitted, date_cleared, date_returned),
    
    -- Ensure no duplicate clearances for same student-requirement-teacher-subject-year
    UNIQUE KEY unique_clearance (lrn, requirement_id, teacher_id, subject_id, school_year_id)
);

-- Create students_requirement junction table
CREATE TABLE students_requirement (
    lrn VARCHAR(12) NOT NULL,
    requirement_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (lrn, requirement_id),
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES requirements(requirement_id) ON DELETE CASCADE
);

-- Create students_clearance_status junction table
CREATE TABLE students_clearance_status (
    lrn VARCHAR(12) NOT NULL,
    clearance_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (lrn, clearance_id),
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (clearance_id) REFERENCES clearance_status(clearance_id) ON DELETE CASCADE
);

-- Insert default departments
INSERT INTO departments (department_name, description) VALUES
('Senior High School', 'Senior High School Department'),
('Junior High School', 'Junior High School Department'),
('Elementary', 'Elementary Department'),
('Administration', 'School Administration');

-- Insert default strands
INSERT INTO strands (strand_name, description) VALUES
('STEM', 'Science, Technology, Engineering and Mathematics'),
('ABM', 'Accountancy, Business and Management'),
('HUMSS', 'Humanities and Social Sciences'),
('GAS', 'General Academic Strand'),
('TVL', 'Technical-Vocational Livelihood'),
('SPORTS', 'Sports Track'),
('ARTS & DESIGN', 'Arts and Design Track');

-- Insert default school years
INSERT INTO school_year (year_label, start_date, end_date, is_current) VALUES
('2024-2025', '2024-06-01', '2025-03-31', TRUE),
('2023-2024', '2023-06-01', '2024-03-31', FALSE),
('2022-2023', '2022-06-01', '2023-03-31', FALSE);

-- Insert default requirements
INSERT INTO requirements (requirement_name, description, requirement_type) VALUES
('Library Clearance', 'Clearance from library for borrowed books and materials', 'administrative'),
('Laboratory Clearance', 'Clearance from laboratory for equipment usage', 'administrative'),
('Guidance Office Clearance', 'Clearance from guidance office', 'administrative'),
('Accounting Office Clearance', 'Clearance from accounting office for financial matters', 'financial'),
('Disciplinary Clearance', 'Clearance from disciplinary office', 'disciplinary'),
('Classroom Clearance', 'Clearance from classroom adviser', 'academic'),
('Club/Organization Clearance', 'Clearance from club or organization adviser', 'academic'),
('Property Clearance', 'Clearance for school properties and equipment', 'administrative');

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password_hash, role, reference_id, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'admin@school.edu');

-- Create views for common queries

-- View for pending clearance requests by teacher
CREATE VIEW teacher_pending_clearances AS
SELECT 
    cs.clearance_id,
    cs.lrn,
    CONCAT(s.surname, ', ', s.given_name) AS student_name,
    cs.requirement_id,
    r.requirement_name,
    cs.teacher_id,
    CONCAT(t.surname, ', ', t.given_name) AS teacher_name,
    cs.subject_id,
    sub.subject_name,
    cs.school_year_id,
    sy.year_label,
    cs.status,
    cs.date_submitted,
    cs.date_cleared,
    cs.date_returned,
    cs.remarks,
    cs.request_group_id
FROM clearance_status cs
JOIN students s ON cs.lrn = s.lrn
JOIN requirements r ON cs.requirement_id = r.requirement_id
JOIN teachers t ON cs.teacher_id = t.teacher_id
JOIN subjects sub ON cs.subject_id = sub.subject_id
JOIN school_year sy ON cs.school_year_id = sy.school_year_id
WHERE cs.status = 'Pending';

-- View for student clearance history
CREATE VIEW student_clearance_history AS
SELECT 
    cs.clearance_id,
    cs.lrn,
    CONCAT(s.surname, ', ', s.given_name, ' ', COALESCE(s.middle_name, '')) AS full_student_name,
    s.block_code,
    s.strand,
    cs.requirement_id,
    r.requirement_name,
    cs.teacher_id,
    CONCAT(t.surname, ', ', t.given_name) AS teacher_name,
    cs.subject_id,
    sub.subject_name,
    cs.school_year_id,
    sy.year_label,
    cs.status,
    cs.date_submitted,
    cs.date_cleared,
    cs.date_returned,
    cs.remarks,
    cs.request_group_id,
    cs.created_at
FROM clearance_status cs
JOIN students s ON cs.lrn = s.lrn
JOIN requirements r ON cs.requirement_id = r.requirement_id
JOIN teachers t ON cs.teacher_id = t.teacher_id
JOIN subjects sub ON cs.subject_id = sub.subject_id
JOIN school_year sy ON cs.school_year_id = sy.school_year_id
ORDER BY sy.year_label DESC, cs.date_submitted DESC;

-- View for compliance tracking
CREATE VIEW compliance_tracking AS
SELECT 
    cs.clearance_id,
    cs.lrn,
    CONCAT(s.surname, ', ', s.given_name) AS student_name,
    cs.teacher_id,
    CONCAT(t.surname, ', ', t.given_name) AS teacher_name,
    cs.subject_id,
    sub.subject_name,
    cs.requirement_id,
    r.requirement_name,
    cs.status,
    cs.date_submitted AS initial_request_date,
    cs.date_returned AS compliance_sent_date,
    cs.date_cleared AS action_date,
    cs.remarks AS compliance_requirements,
    CASE 
        WHEN cs.status = 'Declined' AND cs.date_returned IS NOT NULL THEN 'Returned for Compliance'
        WHEN cs.status = 'Pending' THEN 'Pending Review'
        WHEN cs.status = 'Approved' THEN 'Approved'
        ELSE 'Unknown'
    END AS current_status,
    DATEDIFF(COALESCE(cs.date_returned, cs.date_cleared, CURDATE()), cs.date_submitted) AS days_to_action
FROM clearance_status cs
JOIN students s ON cs.lrn = s.lrn
JOIN requirements r ON cs.requirement_id = r.requirement_id
JOIN teachers t ON cs.teacher_id = t.teacher_id
JOIN subjects sub ON cs.subject_id = sub.subject_id
WHERE cs.status IN ('Pending', 'Declined', 'Approved')
ORDER BY cs.date_submitted DESC;

-- Stored procedures for common operations

DELIMITER //

-- Procedure to get student clearance summary
CREATE PROCEDURE GetStudentClearanceSummary(IN student_lrn VARCHAR(12))
BEGIN
    SELECT 
        sy.year_label,
        COUNT(*) AS total_clearances,
        SUM(CASE WHEN cs.status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN cs.status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN cs.status = 'Declined' THEN 1 ELSE 0 END) AS declined_count,
        MAX(cs.date_submitted) AS last_submission
    FROM clearance_status cs
    JOIN school_year sy ON cs.school_year_id = sy.school_year_id
    WHERE cs.lrn = student_lrn
    GROUP BY sy.year_label, cs.school_year_id
    ORDER BY sy.year_label DESC;
END //

-- Procedure to get teacher workload
CREATE PROCEDURE GetTeacherWorkload(IN teacher_id_param INT)
BEGIN
    SELECT 
        sy.year_label,
        COUNT(*) AS total_assigned,
        SUM(CASE WHEN cs.status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN cs.status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN cs.status = 'Declined' THEN 1 ELSE 0 END) AS declined_count
    FROM clearance_status cs
    JOIN school_year sy ON cs.school_year_id = sy.school_year_id
    WHERE cs.teacher_id = teacher_id_param
    GROUP BY sy.year_label, cs.school_year_id
    ORDER BY sy.year_label DESC;
END //

DELIMITER ;

-- Triggers for data integrity

-- Trigger to update request_group_id for new submissions
DELIMITER //
CREATE TRIGGER before_clearance_insert
BEFORE INSERT ON clearance_status
FOR EACH ROW
BEGIN
    IF NEW.request_group_id IS NULL OR NEW.request_group_id = '' THEN
        SET NEW.request_group_id = CONCAT('req_', DATE_FORMAT(NOW(), '%Y%m%d'), '_', NEW.lrn, '_', FLOOR(RAND() * 1000));
    END IF;
    
    IF NEW.date_submitted IS NULL THEN
        SET NEW.date_submitted = CURDATE();
    END IF;
END //
DELIMITER ;

-- Final setup
SET FOREIGN_KEY_CHECKS = 1;

-- Display completion message
SELECT 'Senior High School Clearance System - Schema with Compliance Tracking Created Successfully!' AS status;
