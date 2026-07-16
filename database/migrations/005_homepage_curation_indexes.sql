ALTER TABLE categories
    ADD INDEX idx_categories_published_featured (is_published, featured_order);

ALTER TABLE guides
    ADD INDEX idx_guides_published_featured (is_published, featured_order);

ALTER TABLE downloads
    ADD INDEX idx_downloads_published_featured (is_published, featured_order);
