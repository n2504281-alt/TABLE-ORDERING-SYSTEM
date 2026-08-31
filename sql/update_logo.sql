-- Run this only if the database was already installed before the logo update.
CREATE TABLE IF NOT EXISTS app_settings (
 setting_key VARCHAR(100) PRIMARY KEY,
 setting_value TEXT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
INSERT INTO app_settings(setting_key,setting_value) VALUES ('restaurant_logo','assets/logo-reference.jpg')
ON DUPLICATE KEY UPDATE setting_key=setting_key;
