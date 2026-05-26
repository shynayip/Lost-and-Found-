CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires DATETIME NOT NULL,
    UNIQUE KEY (email),
    INDEX (token)
);lost_foundlost_found