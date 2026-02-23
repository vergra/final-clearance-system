-- MIGRATION: Add strands table for existing databases
-- ONLY run this if you have an existing database without the strands table
-- For new installations, use schema.sql instead (includes everything)

USE student_clearance;

-- Create strands table
CREATE TABLE strands (
    strand_id INT AUTO_INCREMENT PRIMARY KEY,
    strand_name VARCHAR(50) NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE,
    UNIQUE KEY unique_strand_per_dept (strand_name, department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add strand_id to subjects table
ALTER TABLE subjects ADD COLUMN strand_id INT NULL AFTER department_id;

-- Add foreign key constraint for strand_id
ALTER TABLE subjects ADD FOREIGN KEY (strand_id) REFERENCES strands(strand_id) ON DELETE SET NULL;

-- Migrate existing strands from subjects text field to strands table
INSERT IGNORE INTO strands (strand_name, department_id)
SELECT DISTINCT strand, department_id 
FROM subjects 
WHERE strand IS NOT NULL AND strand != '';

-- Update subjects to reference the new strand records
UPDATE subjects s 
JOIN strands st ON s.strand = st.strand_name AND s.department_id = st.department_id
SET s.strand_id = st.strand_id 
WHERE s.strand IS NOT NULL AND s.strand != '';

-- Make strand_id required for new subjects (optional - keep nullable for flexibility)
-- ALTER TABLE subjects MODIFY COLUMN strand_id INT NOT NULL;

-- Keep strand text field for now for backward compatibility (can be removed later)
-- ALTER TABLE subjects DROP COLUMN strand;
