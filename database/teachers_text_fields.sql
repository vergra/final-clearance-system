-- Add department and strand text fields to teachers table for flexible signup
-- Run this after the main schema to allow teachers to enter department and strand as text
USE student_clearance;

-- Add new text columns for department and strand
ALTER TABLE teachers ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER department_id;
ALTER TABLE teachers ADD COLUMN strand VARCHAR(50) DEFAULT NULL AFTER subject_id;

-- Make department_id and subject_id nullable since we're using text fields now
ALTER TABLE teachers MODIFY COLUMN department_id INT DEFAULT NULL;
