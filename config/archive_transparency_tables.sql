-- Create archive tables for transparency system

-- Archive table for donations/assistance
CREATE TABLE IF NOT EXISTS transparency_donations_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT NOT NULL,
    campaign_id INT,
    donor_name VARCHAR(255) NOT NULL,
    donor_type VARCHAR(100),
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'PHP',
    date_received DATE,
    payment_method VARCHAR(100),
    reference_code VARCHAR(255),
    status VARCHAR(50) DEFAULT 'confirmed',
    is_restricted TINYINT(1) DEFAULT 0,
    notes TEXT,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archived_by INT,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_donor_type (donor_type),
    INDEX idx_date_received (date_received),
    INDEX idx_archived_at (archived_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Archive table for campaigns/programs
CREATE TABLE IF NOT EXISTS transparency_campaigns_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255),
    description TEXT,
    goal_amount DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    banner_image VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archived_by INT,
    INDEX idx_status (status),
    INDEX idx_archived_at (archived_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
