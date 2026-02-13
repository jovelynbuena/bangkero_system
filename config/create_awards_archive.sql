CREATE TABLE IF NOT EXISTS awards_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    award_id INT NOT NULL,
    award_title VARCHAR(255) NOT NULL,
    awarding_body VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    year_received INT NOT NULL,
    date_received DATE NOT NULL,
    award_image VARCHAR(255),
    certificate_file VARCHAR(255),
    original_created_at TIMESTAMP NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (award_id)
);
