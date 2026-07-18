ALTER TABLE guides
    DROP FOREIGN KEY IF EXISTS guides_ibfk_1;

ALTER TABLE guides
    DROP FOREIGN KEY IF EXISTS fk_guides_category;

ALTER TABLE categories
    MODIFY COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE guides
    MODIFY COLUMN category_id INT UNSIGNED NULL,
    ADD CONSTRAINT fk_guides_category_v2
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT;

ALTER TABLE diagnostic_flows
    MODIFY COLUMN category_id INT UNSIGNED NULL,
    ADD CONSTRAINT fk_diagnostic_flows_category
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT;

ALTER TABLE maintenance_recommendations
    MODIFY COLUMN category_id INT UNSIGNED NULL,
    ADD CONSTRAINT fk_maintenance_recommendations_category
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT;

ALTER TABLE community_questions
    MODIFY COLUMN category_id INT UNSIGNED NULL,
    ADD CONSTRAINT fk_community_questions_category
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT;

ALTER TABLE knowledge_articles
    ADD CONSTRAINT fk_knowledge_articles_category
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT;
