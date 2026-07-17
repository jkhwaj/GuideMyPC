CREATE TABLE ai_conversations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    public_id CHAR(48) NOT NULL,
    user_id INT NULL,
    guest_token_hash CHAR(64) NULL,
    status ENUM('active','deleted') NOT NULL DEFAULT 'active',
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_ai_conversations_public (public_id), KEY idx_ai_conversations_user_updated (user_id, updated_at), KEY idx_ai_conversations_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ai_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender ENUM('user','assistant','system') NOT NULL,
    content TEXT NOT NULL,
    provider_name VARCHAR(50) NULL,
    token_count INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_ai_messages_conversation_created (conversation_id, created_at),
    CONSTRAINT fk_ai_messages_conversation FOREIGN KEY (conversation_id) REFERENCES ai_conversations (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ai_message_citations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    message_id BIGINT UNSIGNED NOT NULL,
    resource_type ENUM('guide','knowledge','download','diagnostic') NOT NULL,
    resource_slug VARCHAR(150) NOT NULL,
    PRIMARY KEY (id), KEY idx_ai_citations_message (message_id),
    CONSTRAINT fk_ai_citations_message FOREIGN KEY (message_id) REFERENCES ai_messages (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ai_usage_daily (
    usage_date DATE NOT NULL,
    usage_scope CHAR(64) NOT NULL,
    request_count INT UNSIGNED NOT NULL DEFAULT 0,
    token_count INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (usage_date, usage_scope)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ai_safety_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NULL,
    event_type ENUM('blocked_input','blocked_output','provider_failure','quota_exhausted') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_ai_safety_events_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
