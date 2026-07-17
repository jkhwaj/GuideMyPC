CREATE TABLE maintenance_recommendations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT NULL,
    cadence ENUM('daily','weekly','monthly','quarterly','event') NOT NULL,
    importance ENUM('important','optional') NOT NULL DEFAULT 'optional',
    title VARCHAR(200) NOT NULL,
    summary TEXT NOT NULL,
    guide_slug VARCHAR(150) NULL,
    knowledge_slug VARCHAR(150) NULL,
    publication_state ENUM('draft','published') NOT NULL DEFAULT 'draft',
    reviewed_at DATE NULL,
    last_reviewed_at DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_maintenance_public_cadence (publication_state, cadence),
    KEY idx_maintenance_category_cadence (category_id, cadence),
    KEY idx_maintenance_review (last_reviewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenance_user_status (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    recommendation_id BIGINT UNSIGNED NOT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    snoozed_until TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_maintenance_user_recommendation (user_id, recommendation_id),
    KEY idx_maintenance_user_status (user_id, completed_at, snoozed_until),
    CONSTRAINT fk_maintenance_status_recommendation FOREIGN KEY (recommendation_id) REFERENCES maintenance_recommendations (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
