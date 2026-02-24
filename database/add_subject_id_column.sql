-- Add subject_id column to clearance_status table if it doesn't exist
ALTER TABLE clearance_status 
ADD COLUMN subject_id INT NOT NULL DEFAULT 0 
AFTER teacher_id;

-- Add foreign key constraint for subject_id
ALTER TABLE clearance_status 
ADD CONSTRAINT fk_clearance_status_subject 
FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE RESTRICT;
