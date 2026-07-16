ALTER TABLE knowledge_articles
    ADD FULLTEXT INDEX ft_knowledge_articles_search (title, error_code, summary, content);

ALTER TABLE search_events
    MODIFY result_type ENUM('search', 'guide', 'download', 'community', 'article') NOT NULL;
