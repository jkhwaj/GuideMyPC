CREATE TABLE uploads (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    public_id CHAR(48) NOT NULL,
    user_id INT NULL,
    guest_token_hash CHAR(64) NULL,
    purpose ENUM('ai','community') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    storage_name CHAR(64) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    byte_size INT UNSIGNED NOT NULL,
    content_hash CHAR(64) NOT NULL,
    lifecycle_state ENUM('quarantined','approved','rejected','deleted') NOT NULL DEFAULT 'quarantined',
    ai_consent_at TIMESTAMP NULL DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id), UNIQUE KEY uq_uploads_public (public_id), UNIQUE KEY uq_uploads_storage (storage_name),
    KEY idx_uploads_owner_state (user_id, lifecycle_state), KEY idx_uploads_hash (content_hash), KEY idx_uploads_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
