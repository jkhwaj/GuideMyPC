INSERT INTO categories (name, slug, description, icon) VALUES
    ('Windows', 'windows', 'Practical help for common Windows issues.', '💻'),
    ('macOS', 'macos', 'Practical help for common Mac issues.', '🍎'),
    ('Linux', 'linux', 'Practical help for common Linux issues.', '🐧'),
    ('Android', 'android', 'Practical help for common Android issues.', '🤖'),
    ('iPhone / iPad', 'iphone', 'Practical help for common iPhone and iPad issues.', '📱'),
    ('Wi-Fi & Routers', 'wifi', 'Practical help for home network troubleshooting.', '📶')
ON DUPLICATE KEY UPDATE icon = CASE
    WHEN slug = 'windows' AND icon = 'fa-brands fa-windows' THEN VALUES(icon)
    WHEN slug = 'macos' AND icon = 'fa-brands fa-apple' THEN VALUES(icon)
    WHEN slug = 'linux' AND icon = 'fa-brands fa-linux' THEN VALUES(icon)
    WHEN slug = 'android' AND icon = 'fa-brands fa-android' THEN VALUES(icon)
    WHEN slug = 'iphone' AND icon = 'fa-brands fa-apple' THEN VALUES(icon)
    WHEN slug = 'wifi' AND icon = 'fa-solid fa-wifi' THEN VALUES(icon)
    ELSE icon
END;

INSERT INTO guides (category_id, title, slug, description, difficulty, estimated_time, risk_level, content)
SELECT id, 'Check a Windows update issue', 'check-windows-update-issue', 'Start with safe checks before changing system settings.', 'Beginner', '10 minutes', 'Low', 'Use the guide steps below.'
FROM categories WHERE slug = 'windows'
ON DUPLICATE KEY UPDATE slug = VALUES(slug);

INSERT INTO guide_steps (guide_id, step_number, step_text)
SELECT id, 1, 'Restart the computer and try Windows Update again.'
FROM guides WHERE slug = 'check-windows-update-issue'
ON DUPLICATE KEY UPDATE step_text = VALUES(step_text);

UPDATE guides
SET platform_version = 'Windows 10 or Windows 11',
    required_tools = 'Stable internet connection\nAt least 10 GB free storage',
    prerequisites = 'Save open work and connect the device to power before installing updates.',
    backup_warning = 'Back up important files before troubleshooting an update that has repeatedly failed.',
    last_reviewed_at = UTC_DATE(),
    next_actions = 'If the update still fails after these checks, use official Microsoft recovery guidance or contact a qualified technician.'
WHERE slug = 'check-windows-update-issue';

INSERT INTO guide_tools (guide_id, name, sort_order)
SELECT guides.id, seeded.name, seeded.sort_order
FROM guides
JOIN (
    SELECT 'Stable internet connection' AS name, 1 AS sort_order
    UNION ALL SELECT 'At least 10 GB free storage', 2
) AS seeded
WHERE guides.slug = 'check-windows-update-issue'
ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order);

INSERT INTO guide_sources (guide_id, title, official_url, sort_order)
SELECT guides.id, 'Microsoft Windows Update support', 'https://support.microsoft.com/', 1
FROM guides
WHERE guides.slug = 'check-windows-update-issue'
ON DUPLICATE KEY UPDATE title = VALUES(title), sort_order = VALUES(sort_order);

UPDATE guide_steps
JOIN guides ON guide_steps.guide_id = guides.id
SET guide_steps.step_title = CASE guide_steps.step_number WHEN 1 THEN 'Restart before changing settings' ELSE 'Check connection and storage' END,
    guide_steps.expected_result = CASE guide_steps.step_number WHEN 1 THEN 'Windows starts normally and Windows Update can be tried again.' ELSE 'The device has a reliable connection and enough room for the update.' END,
    guide_steps.warning_text = CASE guide_steps.step_number WHEN 1 THEN 'Save unsaved work before restarting.' ELSE 'Do not delete system files to create storage space.' END,
    guide_steps.recovery_text = CASE guide_steps.step_number WHEN 1 THEN 'If Windows cannot restart normally, stop and use official recovery guidance.' ELSE 'If storage cannot be freed safely, back up files before removing personal content.' END
WHERE guides.slug = 'check-windows-update-issue';

INSERT INTO guide_steps (guide_id, step_number, step_text)
SELECT id, 2, 'Confirm that the device has a stable internet connection and enough free storage.'
FROM guides WHERE slug = 'check-windows-update-issue'
ON DUPLICATE KEY UPDATE step_text = VALUES(step_text);

INSERT INTO downloads (name, description, official_url, category) VALUES
    ('Microsoft Support', 'Official Microsoft support and recovery resources.', 'https://support.microsoft.com/', 'Windows'),
    ('Apple Support', 'Official Apple support resources.', 'https://support.apple.com/', 'macOS'),
    ('Android Help', 'Official Android help resources.', 'https://support.google.com/android/', 'Android')
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO search_aliases (alias, replacement) VALUES
    ('bsod', 'blue screen'),
    ('wifi', 'wi-fi'),
    ('mac book', 'macbook')
ON DUPLICATE KEY UPDATE replacement = VALUES(replacement);

INSERT INTO search_related_queries (query_text, related_query) VALUES
    ('blue screen', 'driver issues'),
    ('blue screen', 'windows update'),
    ('wi-fi', 'router restart'),
    ('wi-fi', 'slow internet')
ON DUPLICATE KEY UPDATE related_query = VALUES(related_query);

INSERT INTO knowledge_articles (category_id, article_type, title, slug, error_code, summary, content, publication_state, published_at, reviewed_at, last_reviewed_at)
SELECT categories.id, seeded.article_type, seeded.title, seeded.slug, seeded.error_code, seeded.summary, seeded.content, 'published', UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_DATE()
FROM (
    SELECT 'windows' AS category_slug, 'error_code' AS article_type, 'Windows stop code 0x0000007B' AS title, 'windows-stop-code-0x0000007b' AS slug, '0x0000007B' AS error_code, 'This stop code can point to a Windows startup-storage problem.' AS summary, 'Start by disconnecting newly added storage devices and restarting the computer. If the code returns, use Microsoft recovery guidance before changing storage controller settings.' AS content
    UNION ALL SELECT 'windows', 'explanation', 'Why restarting can fix a Windows problem', 'windows-restart-explanation', NULL, 'A restart clears temporary system state and finishes pending updates.', 'Restarting is a safe first step for many temporary problems. Save open work first, then use the normal Restart option instead of holding the power button.'
    UNION ALL SELECT 'macos', 'faq', 'What does a full startup disk mean on a Mac?', 'macos-startup-disk-faq', NULL, 'A full startup disk can prevent updates and make a Mac slow.', 'Remove files you no longer need, then empty the Bin. Avoid deleting system folders when freeing space.'
    UNION ALL SELECT 'macos', 'hardware', 'Check a Mac laptop power connection', 'macos-power-hardware', NULL, 'A damaged cable or unsupported charger can prevent reliable charging.', 'Inspect the cable and adapter for damage. Try a known-good compatible charger before changing battery settings or opening the device.'
    UNION ALL SELECT 'linux', 'glossary', 'Package manager', 'linux-package-manager-glossary', NULL, 'A package manager installs and updates trusted software for a Linux distribution.', 'Use the package manager provided by your distribution when possible. It tracks updates and dependencies for installed software.'
    UNION ALL SELECT 'android', 'maintenance', 'Free safe storage space on Android', 'android-storage-maintenance', NULL, 'Freeing storage can help Android install updates and run reliably.', 'Review large downloads and unused apps first. Back up photos before removing them, and avoid cleaner apps that request unnecessary permissions.'
    UNION ALL SELECT 'android', 'software', 'Review Android app permissions', 'android-app-permissions-software', NULL, 'Permissions should match what an app needs to do its job.', 'Open the app permission settings and remove permissions that do not make sense for the app. Do not disable required permissions if the app needs them for a feature you use.'
    UNION ALL SELECT 'iphone', 'security', 'Recognize Apple ID sign-in prompts', 'iphone-apple-id-security', NULL, 'Unexpected sign-in prompts can indicate a password or device-security issue.', 'Do not share a verification code. Review signed-in devices in Apple ID settings and change your password if a device is unfamiliar.'
    UNION ALL SELECT 'wifi', 'networking', 'Choose a better Wi-Fi router location', 'wifi-router-location', NULL, 'Router placement affects coverage and connection stability.', 'Place the router in an open central location, away from thick walls and large metal objects. Change one setting at a time and test the connection.'
) AS seeded
JOIN categories ON categories.slug = seeded.category_slug
ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), content = VALUES(content), publication_state = VALUES(publication_state), reviewed_at = VALUES(reviewed_at), last_reviewed_at = VALUES(last_reviewed_at);

INSERT INTO knowledge_tags (name, slug) VALUES
    ('updates', 'updates'), ('startup', 'startup'), ('storage', 'storage'), ('security', 'security'), ('wi-fi', 'wifi')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO knowledge_article_tags (article_id, tag_id)
SELECT articles.id, tags.id FROM knowledge_articles AS articles JOIN knowledge_tags AS tags ON articles.slug = 'windows-stop-code-0x0000007b' AND tags.slug IN ('startup', 'storage')
ON DUPLICATE KEY UPDATE tag_id = VALUES(tag_id);

INSERT INTO knowledge_sources (article_id, title, official_url, sort_order)
SELECT articles.id, 'Microsoft stop error troubleshooting', 'https://support.microsoft.com/', 1 FROM knowledge_articles AS articles WHERE articles.slug = 'windows-stop-code-0x0000007b'
ON DUPLICATE KEY UPDATE title = VALUES(title), sort_order = VALUES(sort_order);

INSERT INTO knowledge_sources (article_id, title, official_url, sort_order)
SELECT articles.id, 'Apple Support', 'https://support.apple.com/', 1 FROM knowledge_articles AS articles WHERE articles.slug = 'iphone-apple-id-security'
ON DUPLICATE KEY UPDATE title = VALUES(title), sort_order = VALUES(sort_order);

INSERT INTO knowledge_relations (article_id, guide_id, relation_type, label, sort_order)
SELECT articles.id, guides.id, 'guide', 'Check a Windows update issue', 1
FROM knowledge_articles AS articles JOIN guides ON guides.slug = 'check-windows-update-issue'
WHERE articles.slug = 'windows-stop-code-0x0000007b' AND NOT EXISTS (
    SELECT 1 FROM knowledge_relations AS relations WHERE relations.article_id = articles.id AND relations.guide_id = guides.id
);
