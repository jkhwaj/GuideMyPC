SET @guide_steps_fk = (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE constraint_schema = DATABASE()
      AND table_name = 'guide_steps'
      AND column_name = 'guide_id'
      AND referenced_table_name = 'guides'
    LIMIT 1
);
SET @drop_guide_steps_fk = IF(
    @guide_steps_fk IS NULL,
    'SELECT 1',
    CONCAT('ALTER TABLE guide_steps DROP FOREIGN KEY `', @guide_steps_fk, '`')
);
PREPARE drop_guide_steps_fk FROM @drop_guide_steps_fk;
EXECUTE drop_guide_steps_fk;
DEALLOCATE PREPARE drop_guide_steps_fk;

SET @favorites_fk = (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE constraint_schema = DATABASE()
      AND table_name = 'favorites'
      AND column_name = 'guide_id'
      AND referenced_table_name = 'guides'
    LIMIT 1
);
SET @drop_favorites_fk = IF(
    @favorites_fk IS NULL,
    'SELECT 1',
    CONCAT('ALTER TABLE favorites DROP FOREIGN KEY `', @favorites_fk, '`')
);
PREPARE drop_favorites_fk FROM @drop_favorites_fk;
EXECUTE drop_favorites_fk;
DEALLOCATE PREPARE drop_favorites_fk;

SET @ratings_fk = (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE constraint_schema = DATABASE()
      AND table_name = 'guide_ratings'
      AND column_name = 'guide_id'
      AND referenced_table_name = 'guides'
    LIMIT 1
);
SET @drop_ratings_fk = IF(
    @ratings_fk IS NULL,
    'SELECT 1',
    CONCAT('ALTER TABLE guide_ratings DROP FOREIGN KEY `', @ratings_fk, '`')
);
PREPARE drop_ratings_fk FROM @drop_ratings_fk;
EXECUTE drop_ratings_fk;
DEALLOCATE PREPARE drop_ratings_fk;

ALTER TABLE guides MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE guide_steps MODIFY guide_id INT UNSIGNED NOT NULL;
ALTER TABLE favorites MODIFY guide_id INT UNSIGNED NOT NULL;
ALTER TABLE guide_ratings MODIFY guide_id INT UNSIGNED NOT NULL;

ALTER TABLE guide_steps
    ADD CONSTRAINT fk_guide_steps_guide_v2
        FOREIGN KEY (guide_id) REFERENCES guides (id) ON DELETE CASCADE;
ALTER TABLE favorites
    ADD CONSTRAINT fk_favorites_guide_v2
        FOREIGN KEY (guide_id) REFERENCES guides (id) ON DELETE CASCADE;
ALTER TABLE guide_ratings
    ADD CONSTRAINT fk_guide_ratings_guide_v2
        FOREIGN KEY (guide_id) REFERENCES guides (id) ON DELETE CASCADE;
