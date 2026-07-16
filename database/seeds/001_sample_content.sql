INSERT INTO categories (name, slug, description, icon) VALUES
    ('Windows', 'windows', 'Practical help for common Windows issues.', 'fa-brands fa-windows'),
    ('macOS', 'macos', 'Practical help for common Mac issues.', 'fa-brands fa-apple'),
    ('Android', 'android', 'Practical help for common Android issues.', 'fa-brands fa-android'),
    ('Wi-Fi', 'wifi', 'Practical help for home network troubleshooting.', 'fa-solid fa-wifi')
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO guides (category_id, title, slug, description, difficulty, estimated_time, risk_level, content)
SELECT id, 'Check a Windows update issue', 'check-windows-update-issue', 'Start with safe checks before changing system settings.', 'Beginner', '10 minutes', 'Low', 'Use the guide steps below.'
FROM categories WHERE slug = 'windows'
ON DUPLICATE KEY UPDATE slug = VALUES(slug);

INSERT INTO guide_steps (guide_id, step_number, step_text)
SELECT id, 1, 'Restart the computer and try Windows Update again.'
FROM guides WHERE slug = 'check-windows-update-issue'
ON DUPLICATE KEY UPDATE step_text = VALUES(step_text);

INSERT INTO guide_steps (guide_id, step_number, step_text)
SELECT id, 2, 'Confirm that the device has a stable internet connection and enough free storage.'
FROM guides WHERE slug = 'check-windows-update-issue'
ON DUPLICATE KEY UPDATE step_text = VALUES(step_text);

INSERT INTO downloads (name, description, official_url, category) VALUES
    ('Microsoft Support', 'Official Microsoft support and recovery resources.', 'https://support.microsoft.com/', 'Windows'),
    ('Apple Support', 'Official Apple support resources.', 'https://support.apple.com/', 'macOS'),
    ('Android Help', 'Official Android help resources.', 'https://support.google.com/android/', 'Android')
ON DUPLICATE KEY UPDATE id = id;
