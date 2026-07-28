CREATE TEMPORARY TABLE official_download_catalog (
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    official_url VARCHAR(255) NOT NULL,
    normalized_name VARCHAR(150) NOT NULL,
    normalized_url VARCHAR(255) NOT NULL
);

INSERT INTO official_download_catalog (name, description, category, official_url, normalized_name, normalized_url) VALUES
    ('Malwarebytes', 'Scan for and remove malware, spyware, and other threats.', 'Windows, macOS, Android, iOS', 'https://www.malwarebytes.com/mwb-download', 'malwarebytes', 'https://www.malwarebytes.com/mwb-download'),
    ('Rufus', 'Create bootable USB installation and recovery drives.', 'Windows', 'https://rufus.ie/', 'rufus', 'https://rufus.ie'),
    ('CPU-Z', 'View detailed processor, motherboard, chipset, and memory information.', 'Windows, Android', 'https://www.cpuid.com/softwares/cpu-z.html', 'cpu-z', 'https://www.cpuid.com/softwares/cpu-z.html'),
    ('CrystalDiskInfo', 'Check SMART information and the health of HDD, SSD, and NVMe drives.', 'Windows', 'https://crystalmark.info/en/software/CrystalDiskInfo/', 'crystaldiskinfo', 'https://crystalmark.info/en/software/CrystalDiskInfo'),
    ('HWMonitor', 'Monitor hardware temperatures, voltages, power, and fan speeds.', 'Windows', 'https://www.cpuid.com/softwares/HWmonitor.html', 'hwmonitor', 'https://www.cpuid.com/softwares/HWmonitor.html'),
    ('MemTest86', 'Create a self-booting memory test to detect faulty RAM.', 'Bootable UEFI, x86/x64, ARM64', 'https://www.memtest86.com/', 'memtest86', 'https://www.memtest86.com'),
    ('Ninite', 'Install or update several common Windows applications from one installer.', 'Windows', 'https://ninite.com/', 'ninite', 'https://ninite.com'),
    ('Windows 11 Download', 'Download the official Windows 11 installation assistant, media creation tool, or ISO.', 'Windows', 'https://www.microsoft.com/software-download/windows11', 'windows 11 download', 'https://www.microsoft.com/software-download/windows11'),
    ('Intel Driver & Support Assistant', 'Detect Intel hardware and identify available driver and software updates.', 'Windows', 'https://www.intel.com/content/www/us/en/support/detect.html', 'intel driver & support assistant', 'https://www.intel.com/content/www/us/en/support/detect.html'),
    ('AMD Drivers and Support', 'Download official drivers for AMD Ryzen chipsets, Radeon graphics, and supported products.', 'Windows, Linux', 'https://www.amd.com/en/support/download/drivers.html', 'amd drivers and support', 'https://www.amd.com/en/support/download/drivers.html'),
    ('NVIDIA Drivers', 'Find official NVIDIA graphics drivers by product and operating system.', 'Windows, Linux', 'https://www.nvidia.com/en-us/drivers/', 'nvidia drivers', 'https://www.nvidia.com/en-us/drivers'),
    ('Samsung Magician', 'Manage, diagnose, optimize, and update supported Samsung storage devices.', 'Windows, macOS, Android', 'https://semiconductor.samsung.com/consumer-storage/support/tools/', 'samsung magician', 'https://semiconductor.samsung.com/consumer-storage/support/tools');

CREATE TEMPORARY TABLE official_download_matches AS
SELECT downloads.id AS download_id, catalog.name AS catalog_name
FROM downloads
JOIN official_download_catalog AS catalog ON
    REGEXP_REPLACE(LOWER(TRIM(downloads.name)), '[[:space:]]+', ' ') = catalog.normalized_name
    OR CONCAT(
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
    ) = BINARY catalog.normalized_url;

UPDATE downloads
JOIN official_download_matches AS matches ON matches.download_id = downloads.id
JOIN official_download_catalog AS catalog ON catalog.name = matches.catalog_name
SET downloads.name = catalog.name,
    downloads.description = catalog.description,
    downloads.category = catalog.category,
    downloads.official_url = catalog.official_url,
    downloads.review_state = 'approved',
    downloads.is_published = 1,
    downloads.verified_at = UTC_DATE();

DELETE duplicate
FROM downloads AS duplicate
JOIN downloads AS kept ON kept.name = duplicate.name AND BINARY kept.official_url = BINARY duplicate.official_url AND kept.id < duplicate.id
JOIN official_download_catalog AS catalog ON catalog.name = duplicate.name AND BINARY catalog.official_url = BINARY duplicate.official_url;

INSERT INTO downloads (name, description, official_url, category, review_state, is_published, verified_at)
SELECT catalog.name, catalog.description, catalog.official_url, catalog.category, 'approved', 1, UTC_DATE()
FROM official_download_catalog AS catalog
WHERE NOT EXISTS (
    SELECT 1 FROM downloads
    WHERE downloads.name = catalog.name OR BINARY downloads.official_url = BINARY catalog.official_url
);

DROP TEMPORARY TABLE official_download_matches;
DROP TEMPORARY TABLE official_download_catalog;
