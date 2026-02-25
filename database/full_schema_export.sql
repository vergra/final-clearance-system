-- Senior High School Clearance System - Full Database Schema
-- This file contains the complete database schema for the SHS Clearance System.
-- Use this to recreate the entire database on another PC or server.
-- Run this in MySQL/MariaDB (e.g., via phpMyAdmin or command line).
--
-- Database: student_clearance
-- Character Set: utf8mb4
-- Collation: utf8mb4_general_ci

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `student_clearance` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE `student_clearance`;

-- Drop existing tables (for clean recreation)
DROP TABLE IF EXISTS `students_clearance_status`;
DROP TABLE IF EXISTS `students_requirement`;
DROP TABLE IF EXISTS `clearance_status`;
DROP TABLE IF EXISTS `student_subject`;
DROP TABLE IF EXISTS `requirements`;
DROP TABLE IF EXISTS `signup_requests`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `teacher_subject`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `strands`;
DROP TABLE IF EXISTS `blocks`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `school_year`;

-- 1. School Year Table
CREATE TABLE `school_year` (
    `school_year_id` INT AUTO_INCREMENT PRIMARY KEY,
    `year_label` VARCHAR(20) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Departments Table
CREATE TABLE `departments` (
    `department_id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_name` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Blocks/Sections Table
CREATE TABLE `blocks` (
    `block_code` VARCHAR(20) PRIMARY KEY,
    `block_name` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Strands Table
CREATE TABLE `strands` (
    `strand_id` INT AUTO_INCREMENT PRIMARY KEY,
    `strand_name` VARCHAR(50) NOT NULL,
    `department_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_strand_per_dept` (`strand_name`, `department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Subjects Table
CREATE TABLE `subjects` (
    `subject_id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_name` VARCHAR(100) NOT NULL,
    `strand` VARCHAR(50) NOT NULL,
    `strand_id` INT DEFAULT NULL,
    `department_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE RESTRICT,
    FOREIGN KEY (`strand_id`) REFERENCES `strands`(`strand_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Teachers Table
CREATE TABLE `teachers` (
    `teacher_id` INT AUTO_INCREMENT PRIMARY KEY,
    `surname` VARCHAR(50) NOT NULL,
    `middle_name` VARCHAR(50) DEFAULT NULL,
    `given_name` VARCHAR(50) NOT NULL,
    `department_id` INT NOT NULL,
    `subject_id` INT DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL COMMENT 'Text field for department name',
    `strand` VARCHAR(50) DEFAULT NULL COMMENT 'Text field for strand name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE RESTRICT,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Teacher-Subject Assignment Junction Table (Many-to-Many)
CREATE TABLE `teacher_subject` (
    `teacher_subject_id` INT AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `school_year_id` INT NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`teacher_id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
    FOREIGN KEY (`school_year_id`) REFERENCES `school_year`(`school_year_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_teacher_subject_year` (`teacher_id`, `subject_id`, `school_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Users Table (Login Accounts)
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(80) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'teacher', 'admin') NOT NULL,
    `reference_id` VARCHAR(20) DEFAULT NULL COMMENT 'LRN for student, teacher_id for teacher, NULL for admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `chk_reference` CHECK (
        (`role` = 'admin' AND `reference_id` IS NULL) OR
        (`role` IN ('student', 'teacher') AND `reference_id` IS NOT NULL AND `reference_id` != '')
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Students Table
CREATE TABLE `students` (
    `lrn` VARCHAR(20) PRIMARY KEY,
    `surname` VARCHAR(50) NOT NULL,
    `middle_name` VARCHAR(50) DEFAULT NULL,
    `given_name` VARCHAR(50) NOT NULL,
    `strand` VARCHAR(50) NOT NULL,
    `block_code` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`block_code`) REFERENCES `blocks`(`block_code`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 10. Student Sign-up Requests (for admin approval)
CREATE TABLE `signup_requests` (
    `signup_request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `lrn` VARCHAR(20) NOT NULL,
    `requested_username` VARCHAR(80) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at` TIMESTAMP NULL,
    `reviewed_by` INT NULL,
    `remarks` TEXT DEFAULT NULL,
    FOREIGN KEY (`lrn`) REFERENCES `students`(`lrn`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_requested_username` (`requested_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 11. Requirements Table
CREATE TABLE `requirements` (
    `requirement_id` INT AUTO_INCREMENT PRIMARY KEY,
    `requirement_name` VARCHAR(100) NOT NULL,
    `department_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 12. Student-Subject Junction Table (Enrollments)
CREATE TABLE `student_subject` (
    `student_subject_id` INT AUTO_INCREMENT PRIMARY KEY,
    `lrn` VARCHAR(20) NOT NULL,
    `subject_id` INT NOT NULL,
    `school_year_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`lrn`) REFERENCES `students`(`lrn`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
    FOREIGN KEY (`school_year_id`) REFERENCES `school_year`(`school_year_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_enrollment` (`lrn`, `subject_id`, `school_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 13. Clearance Status Table (Main clearance records)
CREATE TABLE `clearance_status` (
    `clearance_id` INT AUTO_INCREMENT PRIMARY KEY,
    `lrn` VARCHAR(20) NOT NULL,
    `requirement_id` INT NOT NULL,
    `teacher_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `school_year_id` INT NOT NULL,
    `request_group_id` VARCHAR(36) DEFAULT NULL COMMENT 'Groups multiple subjects into one form submission',
    `status` ENUM('Pending', 'Approved', 'Declined') DEFAULT 'Pending',
    `date_submitted` DATE DEFAULT NULL,
    `date_cleared` DATE DEFAULT NULL,
    `remarks` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`lrn`) REFERENCES `students`(`lrn`) ON DELETE CASCADE,
    FOREIGN KEY (`requirement_id`) REFERENCES `requirements`(`requirement_id`) ON DELETE CASCADE,
    FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`teacher_id`) ON DELETE RESTRICT,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE RESTRICT,
    FOREIGN KEY (`school_year_id`) REFERENCES `school_year`(`school_year_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 14. Students-Requirements Junction Table (which requirements apply to which students)
CREATE TABLE `students_requirement` (
    `lrn` VARCHAR(20) NOT NULL,
    `requirement_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`lrn`, `requirement_id`),
    FOREIGN KEY (`lrn`) REFERENCES `students`(`lrn`) ON DELETE CASCADE,
    FOREIGN KEY (`requirement_id`) REFERENCES `requirements`(`requirement_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 15. Students-Clearance Status Junction Table
CREATE TABLE `students_clearance_status` (
    `lrn` VARCHAR(20) NOT NULL,
    `clearance_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`lrn`, `clearance_id`),
    FOREIGN KEY (`lrn`) REFERENCES `students`(`lrn`) ON DELETE CASCADE,
    FOREIGN KEY (`clearance_id`) REFERENCES `clearance_status`(`clearance_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Default Data
-- Default Admin User (username: admin, password: password - CHANGE IN PRODUCTION)
INSERT INTO `users` (`username`, `password_hash`, `role`, `reference_id`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL);

-- Default School Years
INSERT INTO `school_year` (`year_label`) VALUES
('2024-2025'),
('2025-2026'),
('2026-2027');

-- Default Departments
INSERT INTO `departments` (`department_name`) VALUES
('Senior High School'),
('Junior High School'),
('Elementary');

-- Default Blocks/Sections for Senior High School
INSERT INTO `blocks` (`block_code`, `block_name`) VALUES
('STEM-11A', 'STEM Grade 11 - Section A'),
('STEM-11B', 'STEM Grade 11 - Section B'),
('ABM-11A', 'ABM Grade 11 - Section A'),
('ABM-11B', 'ABM Grade 11 - Section B'),
('HUMSS-11A', 'HUMSS Grade 11 - Section A'),
('HUMSS-11B', 'HUMSS Grade 11 - Section B'),
('GAS-11A', 'GAS Grade 11 - Section A'),
('STEM-12A', 'STEM Grade 12 - Section A'),
('STEM-12B', 'STEM Grade 12 - Section B'),
('ABM-12A', 'ABM Grade 12 - Section A'),
('ABM-12B', 'ABM Grade 12 - Section B'),
('HUMSS-12A', 'HUMSS Grade 12 - Section A'),
('HUMSS-12B', 'HUMSS Grade 12 - Section B'),
('GAS-12A', 'GAS Grade 12 - Section A');

-- Default Strands for Senior High School
INSERT INTO `strands` (`strand_name`, `department_id`) VALUES
('STEM', 1),
('ABM', 1),
('HUMSS', 1),
('GAS', 1),
('TVL', 1),
('SPORTS', 1),
('ARTS & DESIGN', 1);

-- Default Sample Subjects (you can add more as needed)
INSERT INTO `subjects` (`subject_name`, `strand`, `department_id`) VALUES
('General Mathematics', 'STEM', 1),
('Pre-Calculus', 'STEM', 1),
('Basic Calculus', 'STEM', 1),
('Physics 1', 'STEM', 1),
('Chemistry 1', 'STEM', 1),
('Biology 1', 'STEM', 1),
('Earth Science', 'STEM', 1),
('Introduction to Philosophy', 'HUMSS', 1),
('Creative Writing', 'HUMSS', 1),
('Disciplines and Ideas', 'HUMSS', 1),
('Applied Economics', 'ABM', 1),
('Business Math', 'ABM', 1),
('Organization and Management', 'ABM', 1),
('Principles of Marketing', 'ABM', 1),
('Work Immersion', 'TVL', 1),
('Computer Hardware Servicing', 'TVL', 1),
('Cookery', 'TVL', 1);

-- Default Requirements
INSERT INTO `requirements` (`requirement_name`, `department_id`) VALUES
('Library Clearance', 1),
('Laboratory Clearance', 1),
('Guidance Clearance', 1),
('Discipline Clearance', 1),
('Accounting Clearance', 1),
('Subject Clearance', 1);

-- Create Indexes for Better Performance
CREATE INDEX `idx_users_username` ON `users`(`username`);
CREATE INDEX `idx_users_role` ON `users`(`role`);
CREATE INDEX `idx_students_lrn` ON `students`(`lrn`);
CREATE INDEX `idx_students_block` ON `students`(`block_code`);
CREATE INDEX `idx_teachers_department` ON `teachers`(`department_id`);
CREATE INDEX `idx_teachers_subject` ON `teachers`(`subject_id`);
CREATE INDEX `idx_subjects_department` ON `subjects`(`department_id`);
CREATE INDEX `idx_subjects_strand` ON `subjects`(`strand`);
CREATE INDEX `idx_strands_department` ON `strands`(`department_id`);
CREATE INDEX `idx_clearance_status_lrn` ON `clearance_status`(`lrn`);
CREATE INDEX `idx_clearance_status_teacher` ON `clearance_status`(`teacher_id`);
CREATE INDEX `idx_clearance_status_subject` ON `clearance_status`(`subject_id`);
CREATE INDEX `idx_clearance_status_school_year` ON `clearance_status`(`school_year_id`);
CREATE INDEX `idx_clearance_status_status` ON `clearance_status`(`status`);
CREATE INDEX `idx_clearance_status_request_group` ON `clearance_status`(`request_group_id`);
CREATE INDEX `idx_teacher_subject_teacher` ON `teacher_subject`(`teacher_id`);
CREATE INDEX `idx_teacher_subject_subject` ON `teacher_subject`(`subject_id`);
CREATE INDEX `idx_teacher_subject_school_year` ON `teacher_subject`(`school_year_id`);
CREATE INDEX `idx_student_subject_lrn` ON `student_subject`(`lrn`);
CREATE INDEX `idx_student_subject_subject` ON `student_subject`(`subject_id`);
CREATE INDEX `idx_student_subject_school_year` ON `student_subject`(`school_year_id`);

-- Set up completed
SELECT 'Database schema created successfully!' as status;
