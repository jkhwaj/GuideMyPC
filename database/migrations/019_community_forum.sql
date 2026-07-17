CREATE TABLE community_questions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('open','solved','locked','hidden') NOT NULL DEFAULT 'open',
    accepted_answer_id BIGINT UNSIGNED NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_questions_public_activity (status, created_at), KEY idx_questions_owner_status (user_id, status), KEY idx_questions_category_status (category_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE community_answers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    question_id BIGINT UNSIGNED NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    moderation_state ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id), KEY idx_answers_question_state (question_id, moderation_state, created_at),
    CONSTRAINT fk_community_answers_question FOREIGN KEY (question_id) REFERENCES community_questions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE community_answer_votes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    answer_id BIGINT UNSIGNED NOT NULL,
    user_id INT NOT NULL,
    vote TINYINT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_answer_votes_user_answer (answer_id, user_id),
    CONSTRAINT fk_community_answer_votes_answer FOREIGN KEY (answer_id) REFERENCES community_answers (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE community_reports (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reporter_user_id INT NOT NULL,
    target_type ENUM('question','answer') NOT NULL,
    target_id BIGINT UNSIGNED NOT NULL,
    reason ENUM('spam','abuse','privacy','unsafe_advice','other') NOT NULL,
    status ENUM('open','resolved','dismissed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY uq_community_reports_reporter_target (reporter_user_id, target_type, target_id), KEY idx_community_reports_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
