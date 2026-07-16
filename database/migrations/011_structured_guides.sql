ALTER TABLE guides
    ADD COLUMN IF NOT EXISTS platform_version VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS required_tools TEXT NULL,
    ADD COLUMN IF NOT EXISTS prerequisites TEXT NULL,
    ADD COLUMN IF NOT EXISTS backup_warning TEXT NULL,
    ADD COLUMN IF NOT EXISTS last_reviewed_at DATE NULL,
    ADD COLUMN IF NOT EXISTS next_actions TEXT NULL,
    ADD COLUMN IF NOT EXISTS video_url VARCHAR(255) NULL;

ALTER TABLE guide_steps
    ADD COLUMN IF NOT EXISTS step_title VARCHAR(180) NULL,
    ADD COLUMN IF NOT EXISTS expected_result TEXT NULL,
    ADD COLUMN IF NOT EXISTS warning_text TEXT NULL,
    ADD COLUMN IF NOT EXISTS recovery_text TEXT NULL,
    ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS image_alt VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS video_timestamp VARCHAR(30) NULL;

CREATE TABLE guide_tools (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    guide_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_guide_tools_guide_name (guide_id, name),
    KEY idx_guide_tools_guide (guide_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE guide_sources (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    guide_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    official_url VARCHAR(255) NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_guide_sources_guide_url (guide_id, official_url),
    KEY idx_guide_sources_guide (guide_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO guide_steps (guide_id, step_number, step_title, step_text)
SELECT guides.id, 1, 'Instructions', guides.content
FROM guides
LEFT JOIN guide_steps ON guide_steps.guide_id = guides.id
WHERE guide_steps.id IS NULL AND guides.content IS NOT NULL AND guides.content <> '';
