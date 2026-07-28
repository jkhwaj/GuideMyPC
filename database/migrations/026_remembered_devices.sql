CREATE TABLE account_remember_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    selector CHAR(24) NOT NULL,
    validator_hash CHAR(64) NOT NULL,
    device_label VARCHAR(100) NOT NULL DEFAULT 'This browser',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    revoked_at TIMESTAMP NULL DEFAULT NULL,
    revoked_reason VARCHAR(40) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_account_remember_tokens_selector (selector),
    KEY idx_account_remember_tokens_user_active (user_id, revoked_at, expires_at),
    KEY idx_account_remember_tokens_expiry (expires_at),
    CONSTRAINT fk_account_remember_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
