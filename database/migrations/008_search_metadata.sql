CREATE TABLE search_aliases (
    alias VARCHAR(120) NOT NULL,
    replacement VARCHAR(120) NOT NULL,
    PRIMARY KEY (alias)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE search_related_queries (
    query_text VARCHAR(120) NOT NULL,
    related_query VARCHAR(120) NOT NULL,
    PRIMARY KEY (query_text, related_query)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE search_events (
    event_date DATE NOT NULL,
    query_hash CHAR(64) NOT NULL,
    result_type ENUM('search', 'guide', 'download', 'community') NOT NULL,
    event_state ENUM('results', 'zero', 'selection') NOT NULL,
    result_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    event_count INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (event_date, query_hash, result_type, event_state, result_count),
    KEY idx_search_events_retention (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
