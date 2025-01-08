-- Drop existing tables if they exist for a clean setup
DROP TABLE IF EXISTS api_keys;
DROP TABLE IF EXISTS offer_codes;

-- Create the 'offer_codes' table
CREATE TABLE offer_codes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    offer_code TEXT NOT NULL,
    description TEXT,
    short_code VARCHAR(6) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

-- Create the 'api_keys' table
CREATE TABLE api_keys (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    api_key VARCHAR(255) NOT NULL UNIQUE,
    active TINYINT(1) DEFAULT 1,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Example data for testing (optional)
INSERT INTO offer_codes (offer_code, description, short_code) VALUES
('OFFER-123', 'First offer', 'ABCDE'),
('OFFER-456', 'Second offer', 'FGHIJ');

INSERT INTO api_keys (user_id, api_key, active, expires_at) VALUES
('testuser1', 'API_KEY_1', TRUE, NULL),
('testuser2', 'API_KEY_2', TRUE, '2025-12-31 23:59:59');

-- Display tables to verify creation
SHOW TABLES;
DESCRIBE offer_codes;
DESCRIBE api_keys;
