ALTER TABLE guides
    ADD FULLTEXT INDEX ft_guides_search (title, description, content);

ALTER TABLE downloads
    ADD FULLTEXT INDEX ft_downloads_search (name, description, category);

ALTER TABLE community_posts
    ADD FULLTEXT INDEX ft_community_posts_search (title, content);
