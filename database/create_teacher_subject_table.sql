-- Create teacher_subject junction table for proper subject-teacher assignments
CREATE TABLE teacher_subject (
    teacher_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject_year (teacher_id, subject_id, school_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
