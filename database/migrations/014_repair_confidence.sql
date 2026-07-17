CREATE TABLE diagnostic_outcomes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    node_id BIGINT UNSIGNED NOT NULL,
    cause_key VARCHAR(100) NOT NULL,
    title VARCHAR(200) NOT NULL,
    explanation TEXT NOT NULL,
    minimum_evidence INT UNSIGNED NOT NULL DEFAULT 1,
    difficulty VARCHAR(50) NULL,
    estimated_time VARCHAR(50) NULL,
    required_tools TEXT NULL,
    risk_level VARCHAR(50) NULL,
    backup_warning TEXT NULL,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_outcomes_node_cause (node_id, cause_key),
    CONSTRAINT fk_diagnostic_outcomes_node FOREIGN KEY (node_id) REFERENCES diagnostic_nodes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE diagnostic_evidence_weights (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    option_id BIGINT UNSIGNED NOT NULL,
    outcome_id BIGINT UNSIGNED NOT NULL,
    weight SMALLINT NOT NULL,
    explanation TEXT NOT NULL,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_evidence_option_outcome (option_id, outcome_id),
    CONSTRAINT fk_diagnostic_evidence_option FOREIGN KEY (option_id) REFERENCES diagnostic_options (id) ON DELETE CASCADE,
    CONSTRAINT fk_diagnostic_evidence_outcome FOREIGN KEY (outcome_id) REFERENCES diagnostic_outcomes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE diagnostic_result_snapshots (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    algorithm_version VARCHAR(30) NOT NULL,
    result_json TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_diagnostic_results_session_created (session_id, created_at),
    CONSTRAINT fk_diagnostic_results_session FOREIGN KEY (session_id) REFERENCES diagnostic_sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE diagnostic_feedback (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    feedback ENUM('helpful','not_helpful','escalated') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_diagnostic_feedback_session (session_id),
    CONSTRAINT fk_diagnostic_feedback_session FOREIGN KEY (session_id) REFERENCES diagnostic_sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
