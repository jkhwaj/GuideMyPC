CREATE TABLE knowledge_articles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL,
    article_type ENUM('explanation', 'error_code', 'faq', 'glossary', 'maintenance', 'security', 'hardware', 'software', 'networking') NOT NULL,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    error_code VARCHAR(80) NULL,
    summary TEXT NOT NULL,
    content TEXT NOT NULL,
    publication_state ENUM('draft', 'review', 'published', 'archived') NOT NULL DEFAULT 'draft',
    author_id INT UNSIGNED NULL,
    reviewer_id INT UNSIGNED NULL,
    published_at TIMESTAMP NULL DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    last_reviewed_at DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_knowledge_articles_slug (slug),
    UNIQUE KEY uq_knowledge_articles_error_code (error_code),
    KEY idx_knowledge_articles_publication (publication_state, article_type, category_id),
    KEY idx_knowledge_articles_category (category_id),
    KEY idx_knowledge_articles_author (author_id),
    KEY idx_knowledge_articles_reviewer (reviewer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE knowledge_tags (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_knowledge_tags_name (name),
    UNIQUE KEY uq_knowledge_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE knowledge_article_tags (
    article_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    CONSTRAINT fk_knowledge_article_tags_article FOREIGN KEY (article_id) REFERENCES knowledge_articles (id) ON DELETE CASCADE,
    CONSTRAINT fk_knowledge_article_tags_tag FOREIGN KEY (tag_id) REFERENCES knowledge_tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE knowledge_sources (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    article_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    official_url VARCHAR(255) NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_knowledge_sources_article_url (article_id, official_url),
    KEY idx_knowledge_sources_article (article_id, sort_order),
    CONSTRAINT fk_knowledge_sources_article FOREIGN KEY (article_id) REFERENCES knowledge_articles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE knowledge_relations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    article_id INT UNSIGNED NOT NULL,
    related_article_id INT UNSIGNED NULL,
    guide_id INT UNSIGNED NULL,
    relation_type ENUM('article', 'guide', 'diagnostic', 'video', 'official_reference') NOT NULL,
    label VARCHAR(180) NULL,
    external_url VARCHAR(255) NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_knowledge_relations_article (article_id, sort_order),
    KEY idx_knowledge_relations_guide (guide_id),
    CONSTRAINT fk_knowledge_relations_article FOREIGN KEY (article_id) REFERENCES knowledge_articles (id) ON DELETE CASCADE,
    CONSTRAINT fk_knowledge_relations_related_article FOREIGN KEY (related_article_id) REFERENCES knowledge_articles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
