ALTER TABLE downloads
    ADD COLUMN IF NOT EXISTS publisher VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS supported_platforms VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS license_label VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS download_type ENUM('tool','driver','support','firmware') NOT NULL DEFAULT 'tool',
    ADD COLUMN IF NOT EXISTS review_state ENUM('pending','approved','stale','rejected','archived') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS verified_at DATE NULL,
    ADD COLUMN IF NOT EXISTS reviewer_note TEXT NULL,
    ADD COLUMN IF NOT EXISTS checksum_value VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS signature_url VARCHAR(500) NULL,
    ADD COLUMN IF NOT EXISTS affiliate_disclosure VARCHAR(255) NULL,
    ADD INDEX idx_downloads_review_category (review_state, category),
    ADD INDEX idx_downloads_publisher_verified (publisher, verified_at);

CREATE TABLE download_verification_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    download_id INT NOT NULL,
    checked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    result_state ENUM('reachable','redirected','broken','unsafe_target') NOT NULL,
    destination_url VARCHAR(500) NULL,
    note VARCHAR(500) NULL,
    PRIMARY KEY (id),
    KEY idx_download_verification_download_checked (download_id, checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
