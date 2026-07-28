DROP TEMPORARY TABLE IF EXISTS historical_download_reference_preflight;
CREATE TEMPORARY TABLE historical_download_reference_preflight (
    id TINYINT NOT NULL PRIMARY KEY
);
INSERT INTO historical_download_reference_preflight (id) VALUES (1);
INSERT INTO historical_download_reference_preflight (id)
SELECT 1
FROM (
    SELECT download_verification_events.download_id
    FROM download_verification_events
    LEFT JOIN downloads ON downloads.id = download_verification_events.download_id
    WHERE downloads.id IS NULL
    LIMIT 1
) AS orphaned_download_reference;
DROP TEMPORARY TABLE historical_download_reference_preflight;

CREATE TEMPORARY TABLE historical_download_catalog (
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    official_url VARCHAR(255) NOT NULL,
    normalized_name VARCHAR(150) NOT NULL,
    normalized_url VARCHAR(255) NOT NULL
);

INSERT INTO historical_download_catalog (name, description, category, official_url, normalized_name, normalized_url) VALUES
    ('Android Help', 'Official Android Help resources for setup, updates, security, and troubleshooting.', 'Android', 'https://support.google.com/android/', 'android help', 'https://support.google.com/android'),
    ('Apple Support', 'Official Apple Support resources for Mac, iPhone, iPad, Apple Account, and device troubleshooting.', 'macOS, iOS, iPadOS', 'https://support.apple.com/', 'apple support', 'https://support.apple.com'),
    ('Microsoft Support', 'Official Microsoft Support resources for Windows troubleshooting, recovery, updates, and account help.', 'Windows', 'https://support.microsoft.com/', 'microsoft support', 'https://support.microsoft.com'),
    ('Malwarebytes', 'Scan for and remove malware, spyware, and other threats.', 'Windows, macOS, Android, iOS', 'https://www.malwarebytes.com/mwb-download', 'malwarebytes', 'https://www.malwarebytes.com/mwb-download');

CREATE TEMPORARY TABLE historical_normalized_downloads AS
SELECT downloads.id,
    REGEXP_REPLACE(LOWER(TRIM(downloads.name)), '[[:space:]]+', ' ') AS normalized_name,
    CONCAT(
        LOWER(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), '://', 1)),
        '://',
        CASE LOWER(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), '://', 1))
            WHEN 'https' THEN REGEXP_REPLACE(LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), CHAR_LENGTH(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), '://', 1)) + 4), '/', 1), '?', 1)), ':443$', '')
            WHEN 'http' THEN REGEXP_REPLACE(LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), CHAR_LENGTH(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), '://', 1)) + 4), '/', 1), '?', 1)), ':80$', '')
            ELSE LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), CHAR_LENGTH(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), '://', 1)) + 4), '/', 1), '?', 1))
        END,
        REGEXP_REPLACE(
            SUBSTRING(
                SUBSTRING(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), CHAR_LENGTH(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), '://', 1)) + 4),
                CHAR_LENGTH(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), CHAR_LENGTH(SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(downloads.official_url, '#', 1)), '://', 1)) + 4), '/', 1), '?', 1)) + 1
            ),
            '/+(\\?.*)?$', '\\1'
        )
    ) AS normalized_url
FROM downloads;

CREATE TEMPORARY TABLE historical_download_matches AS
SELECT normalized.id AS download_id,
    COALESCE(
        (
            SELECT catalog.name
            FROM historical_download_catalog AS catalog
            WHERE normalized.normalized_name = catalog.normalized_name
            LIMIT 1
        ),
        (
            SELECT catalog.name
            FROM historical_download_catalog AS catalog
            WHERE BINARY normalized.normalized_url = BINARY catalog.normalized_url
            LIMIT 1
        )
    ) AS catalog_name
FROM historical_normalized_downloads AS normalized
WHERE EXISTS (
    SELECT 1
    FROM historical_download_catalog AS catalog
    WHERE normalized.normalized_name = catalog.normalized_name
       OR BINARY normalized.normalized_url = BINARY catalog.normalized_url
);

INSERT INTO downloads (name, description, official_url, category, review_state, is_published, verified_at)
SELECT catalog.name, catalog.description, catalog.official_url, catalog.category, 'approved', 1, UTC_DATE()
FROM historical_download_catalog AS catalog
WHERE NOT EXISTS (
    SELECT 1
    FROM historical_download_matches AS matches
    WHERE matches.catalog_name = catalog.name
);

INSERT INTO historical_download_matches (download_id, catalog_name)
SELECT downloads.id, catalog.name
FROM downloads
JOIN historical_download_catalog AS catalog ON BINARY downloads.name = BINARY catalog.name
    AND BINARY downloads.official_url = BINARY catalog.official_url
LEFT JOIN historical_normalized_downloads AS normalized ON normalized.id = downloads.id
WHERE normalized.id IS NULL;

CREATE TEMPORARY TABLE historical_download_canonicals AS
SELECT catalog_name, download_id AS canonical_id
FROM (
    SELECT matches.catalog_name,
        matches.download_id,
        ROW_NUMBER() OVER (
            PARTITION BY matches.catalog_name
            ORDER BY (downloads.review_state = 'approved') DESC,
                downloads.is_published DESC,
                (BINARY downloads.official_url = BINARY catalog.official_url) DESC,
                CHAR_LENGTH(COALESCE(downloads.description, '')) DESC,
                downloads.id DESC
        ) AS preference_rank
    FROM historical_download_matches AS matches
    JOIN downloads ON downloads.id = matches.download_id
    JOIN historical_download_catalog AS catalog ON catalog.name = matches.catalog_name
) AS ranked
WHERE preference_rank = 1;

UPDATE download_verification_events
JOIN historical_download_matches AS matches ON matches.download_id = download_verification_events.download_id
JOIN historical_download_canonicals AS canonical ON canonical.catalog_name = matches.catalog_name
SET download_verification_events.download_id = canonical.canonical_id
WHERE matches.download_id <> canonical.canonical_id;

UPDATE admin_audit_events
JOIN historical_download_matches AS matches ON BINARY admin_audit_events.target_id = BINARY CAST(matches.download_id AS CHAR)
JOIN historical_download_canonicals AS canonical ON canonical.catalog_name = matches.catalog_name
SET admin_audit_events.target_id = CAST(canonical.canonical_id AS CHAR)
WHERE admin_audit_events.target_type = 'download'
    AND matches.download_id <> canonical.canonical_id;

UPDATE downloads
JOIN historical_download_canonicals AS canonical ON canonical.canonical_id = downloads.id
JOIN historical_download_catalog AS catalog ON catalog.name = canonical.catalog_name
SET downloads.name = catalog.name,
    downloads.description = catalog.description,
    downloads.category = catalog.category,
    downloads.official_url = catalog.official_url,
    downloads.review_state = 'approved',
    downloads.is_published = 1,
    downloads.verified_at = UTC_DATE();

DELETE downloads
FROM downloads
JOIN historical_download_matches AS matches ON matches.download_id = downloads.id
JOIN historical_download_canonicals AS canonical ON canonical.catalog_name = matches.catalog_name
WHERE matches.download_id <> canonical.canonical_id;

DROP TEMPORARY TABLE historical_download_canonicals;
DROP TEMPORARY TABLE historical_download_matches;
DROP TEMPORARY TABLE historical_normalized_downloads;
DROP TEMPORARY TABLE historical_download_catalog;
