ALTER TABLE users
    MODIFY COLUMN role ENUM('user', 'editor', 'admin') NOT NULL DEFAULT 'user';
