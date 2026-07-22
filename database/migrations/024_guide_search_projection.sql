CREATE TABLE guide_search_documents (
    guide_id INT UNSIGNED NOT NULL,
    search_text MEDIUMTEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (guide_id),
    FULLTEXT KEY ft_guide_search_documents_text (search_text),
    CONSTRAINT fk_guide_search_documents_guide
        FOREIGN KEY (guide_id) REFERENCES guides (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO guide_search_documents (guide_id, search_text)
SELECT guides.id,
    CONCAT_WS('\n',
        guides.title,
        guides.description,
        guides.content,
        guides.platform_version,
        guides.required_tools,
        guides.prerequisites,
        guides.backup_warning,
        guides.next_actions,
        (SELECT GROUP_CONCAT(guide_tools.name ORDER BY guide_tools.sort_order SEPARATOR '\n') FROM guide_tools WHERE guide_tools.guide_id = guides.id),
        (SELECT GROUP_CONCAT(CONCAT_WS('\n', guide_steps.step_title, guide_steps.step_text, guide_steps.expected_result, guide_steps.warning_text, guide_steps.recovery_text) ORDER BY guide_steps.step_number SEPARATOR '\n') FROM guide_steps WHERE guide_steps.guide_id = guides.id)
    )
FROM guides;
