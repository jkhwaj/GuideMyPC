CREATE TABLE trusted_source_domains (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    domain VARCHAR(255) NOT NULL,
    source_name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_reviewed_at DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_trusted_source_domains_domain (domain),
    KEY idx_trusted_source_domains_active (is_active, domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO trusted_source_domains (domain, source_name, category, is_active, last_reviewed_at) VALUES
    ('support.microsoft.com', 'Microsoft Support', 'Windows', 1, '2026-07-18'),
    ('support.apple.com', 'Apple Support', NULL, 1, '2026-07-18'),
    ('support.google.com', 'Google Help', 'Android', 1, '2026-07-18'),
    ('help.ubuntu.com', 'Ubuntu Desktop Guide', 'Linux', 1, '2026-07-18'),
    ('documentation.ubuntu.com', 'Ubuntu Documentation', 'Linux', 1, '2026-07-18'),
    ('www.tp-link.com', 'TP-Link Support', 'Wi-Fi & Routers', 1, '2026-07-18'),
    ('kb.netgear.com', 'NETGEAR Support', 'Wi-Fi & Routers', 1, '2026-07-18'),
    ('www.asus.com', 'ASUS Support', 'Wi-Fi & Routers', 1, '2026-07-18'),
    ('consumer.ftc.gov', 'Federal Trade Commission', 'Wi-Fi & Routers', 1, '2026-07-18')
ON DUPLICATE KEY UPDATE
    source_name = VALUES(source_name),
    category = VALUES(category),
    is_active = VALUES(is_active),
    last_reviewed_at = VALUES(last_reviewed_at);

ALTER TABLE guide_sources
    ADD COLUMN IF NOT EXISTS trusted_source_domain_id INT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS source_last_reviewed_at DATE NULL;

UPDATE guide_sources
JOIN trusted_source_domains ON trusted_source_domains.domain = LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(guide_sources.official_url, '/', 3), '/', -1))
SET guide_sources.trusted_source_domain_id = trusted_source_domains.id,
    guide_sources.source_last_reviewed_at = COALESCE(guide_sources.source_last_reviewed_at, trusted_source_domains.last_reviewed_at)
WHERE guide_sources.trusted_source_domain_id IS NULL;

ALTER TABLE guide_sources
    ADD KEY idx_guide_sources_trusted_domain (trusted_source_domain_id),
    ADD CONSTRAINT fk_guide_sources_trusted_domain
        FOREIGN KEY (trusted_source_domain_id) REFERENCES trusted_source_domains (id) ON DELETE RESTRICT;
