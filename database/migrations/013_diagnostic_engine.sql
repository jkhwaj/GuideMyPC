CREATE TABLE IF NOT EXISTS diagnostic_flows (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT NULL,
    slug VARCHAR(120) NOT NULL,
    title VARCHAR(200) NOT NULL,
    summary TEXT NOT NULL,
    publication_state ENUM('draft','published') NOT NULL DEFAULT 'draft',
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_flows_slug (slug), KEY idx_diagnostic_flows_publication (publication_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diagnostic_flow_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    flow_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    initial_node_key VARCHAR(100) NOT NULL,
    publication_state ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_versions_flow_number (flow_id, version_number), KEY idx_diagnostic_versions_publication (flow_id, publication_state),
    CONSTRAINT fk_diagnostic_versions_flow FOREIGN KEY (flow_id) REFERENCES diagnostic_flows (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diagnostic_nodes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    version_id BIGINT UNSIGNED NOT NULL,
    node_key VARCHAR(100) NOT NULL,
    node_type ENUM('question','outcome') NOT NULL,
    input_type ENUM('yes_no','single_choice','observed') NULL,
    title VARCHAR(200) NOT NULL,
    prompt TEXT NOT NULL,
    evidence_text TEXT NULL,
    risk_level VARCHAR(50) NULL,
    estimated_time VARCHAR(50) NULL,
    required_tools TEXT NULL,
    backup_warning TEXT NULL,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_nodes_version_key (version_id, node_key),
    CONSTRAINT fk_diagnostic_nodes_version FOREIGN KEY (version_id) REFERENCES diagnostic_flow_versions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diagnostic_options (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    node_id BIGINT UNSIGNED NOT NULL,
    option_key VARCHAR(100) NOT NULL,
    label VARCHAR(200) NOT NULL,
    evidence_text TEXT NULL,
    next_node_key VARCHAR(100) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_options_node_key (node_id, option_key), KEY idx_diagnostic_options_node_order (node_id, sort_order),
    CONSTRAINT fk_diagnostic_options_node FOREIGN KEY (node_id) REFERENCES diagnostic_nodes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diagnostic_resources (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    node_id BIGINT UNSIGNED NOT NULL,
    resource_type ENUM('guide','knowledge','download','community','ai') NOT NULL,
    resource_slug VARCHAR(150) NOT NULL,
    label VARCHAR(200) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id), KEY idx_diagnostic_resources_node_order (node_id, sort_order),
    CONSTRAINT fk_diagnostic_resources_node FOREIGN KEY (node_id) REFERENCES diagnostic_nodes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diagnostic_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    public_id CHAR(48) NOT NULL,
    version_id BIGINT UNSIGNED NOT NULL,
    user_id INT NULL,
    guest_token_hash CHAR(64) NULL,
    current_node_key VARCHAR(100) NOT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_sessions_public (public_id), KEY idx_diagnostic_sessions_user_updated (user_id, updated_at), KEY idx_diagnostic_sessions_expiry (expires_at),
    CONSTRAINT fk_diagnostic_sessions_version FOREIGN KEY (version_id) REFERENCES diagnostic_flow_versions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS diagnostic_answers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    node_key VARCHAR(100) NOT NULL,
    option_key VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_answers_session_node (session_id, node_key),
    CONSTRAINT fk_diagnostic_answers_session FOREIGN KEY (session_id) REFERENCES diagnostic_sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
