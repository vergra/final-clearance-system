-- Add middle_name and subject_id to teachers for teacher sign-up (run if you already have the DB)
USE student_clearance;

ALTER TABLE teachers ADD COLUMN middle_name VARCHAR(50) DEFAULT NULL AFTER given_name;
ALTER TABLE teachers ADD COLUMN subject_id INT DEFAULT NULL AFTER department_id;
ALTER TABLE teachers ADD FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE SET NULL;
