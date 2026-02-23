-- Gradline: Senior High School Clearance - Database Schema
-- Import this file into your empty database "student_clearance" in phpMyAdmin.
-- Creates all tables from the ERD design; safe to re-run (drops tables first).

USE student_clearance;

-- Drop tables in reverse dependency order (so foreign keys don't block)
DROP TABLE IF EXISTS students_clearance_status;
DROP TABLE IF EXISTS students_requirement;
DROP TABLE IF EXISTS clearance_status;
DROP TABLE IF EXISTS student_subject;
DROP TABLE IF EXISTS requirements;
DROP TABLE IF EXISTS signup_requests;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS blocks;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS school_year;

-- 1. School_Year
CREATE TABLE school_year (
    school_year_id INT AUTO_INCREMENT PRIMARY KEY,
    year_label VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Departments
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Blocks
CREATE TABLE blocks (
    block_code VARCHAR(20) PRIMARY KEY,
    block_name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Subjects (FK: department_id, strand_id)
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    strand VARCHAR(50) NOT NULL,
    strand_id INT DEFAULT NULL,
    department_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT,
    FOREIGN KEY (strand_id) REFERENCES strands(strand_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4b. Strands (FK: department_id)
CREATE TABLE strands (
    strand_id INT AUTO_INCREMENT PRIMARY KEY,
    strand_name VARCHAR(50) NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE,
    UNIQUE KEY unique_strand_per_dept (strand_name, department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Teachers (FK: department_id, subject_id for subject handle)
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    surname VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) DEFAULT NULL,
    given_name VARCHAR(50) NOT NULL,
    department_id INT NOT NULL,
    subject_id INT DEFAULT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5b. Users (login accounts: student, teacher, admin)
CREATE TABLE users (
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

-- 6. Students (FK: block_code)
CREATE TABLE students (
    lrn VARCHAR(20) PRIMARY KEY,
    surname VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) DEFAULT NULL,
    given_name VARCHAR(50) NOT NULL,
    strand VARCHAR(50) NOT NULL,
    block_code VARCHAR(20) NOT NULL,
    FOREIGN KEY (block_code) REFERENCES blocks(block_code) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6b. Sign-up requests (students request account; admin approves)
CREATE TABLE signup_requests (
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

-- 7. Requirements (FK: department_id)
CREATE TABLE requirements (
    requirement_id INT AUTO_INCREMENT PRIMARY KEY,
    requirement_name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Student_Subject (associative: Students <-> Subjects, School_Year)
CREATE TABLE student_subject (
    student_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    lrn VARCHAR(20) NOT NULL,
    subject_id INT NOT NULL,
    school_year_id INT NOT NULL,
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (lrn, subject_id, school_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Clearance_Status
CREATE TABLE clearance_status (
    clearance_id INT AUTO_INCREMENT PRIMARY KEY,
    lrn VARCHAR(20) NOT NULL,
    requirement_id INT NOT NULL,
    teacher_id INT NOT NULL,
    school_year_id INT NOT NULL,
    status ENUM('Pending', 'Approved', 'Declined') DEFAULT 'Pending',
    date_submitted DATE DEFAULT NULL,
    date_cleared DATE DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES requirements(requirement_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE RESTRICT,
    FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. students_Requirement (junction: which requirements apply to which students)
CREATE TABLE students_requirement (
    lrn VARCHAR(20) NOT NULL,
    requirement_id INT NOT NULL,
    PRIMARY KEY (lrn, requirement_id),
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES requirements(requirement_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. students_Clearance_Status (junction: link students to clearance status records)
CREATE TABLE students_clearance_status (
    lrn VARCHAR(20) NOT NULL,
    clearance_id INT NOT NULL,
    PRIMARY KEY (lrn, clearance_id),
    FOREIGN KEY (lrn) REFERENCES students(lrn) ON DELETE CASCADE,
    FOREIGN KEY (clearance_id) REFERENCES clearance_status(clearance_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data (optional - remove in production)
INSERT INTO school_year (year_label) VALUES ('2024-2025'), ('2025-2026');
-- Departments should be added via the admin interface, not hardcoded
INSERT INTO blocks (block_code, block_name) VALUES ('11-A', 'Grade 11 Section A'), ('11-B', 'Grade 11 Section B'), ('12-A', 'Grade 12 Section A');
-- Requirements should be added after departments are created
-- Default admin user: username 'admin', password 'password' (change in production)
INSERT INTO users (username, password_hash, role, reference_id) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL);
