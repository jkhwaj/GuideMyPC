CREATE TABLE admin_audit_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id INT NULL,
    action VARCHAR(80) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id VARCHAR(100) NOT NULL,
    metadata_json TEXT NULL,
    request_id CHAR(24) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_admin_audit_actor_created (actor_user_id, created_at), KEY idx_admin_audit_target_created (target_type, target_id, created_at), KEY idx_admin_audit_action_created (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
