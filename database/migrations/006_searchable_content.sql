ALTER TABLE community_posts
    ADD COLUMN IF NOT EXISTS is_published TINYINT(1) NOT NULL DEFAULT 1,
    ADD INDEX idx_community_posts_published_created (is_published, created_at);
