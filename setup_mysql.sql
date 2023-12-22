CREATE DATABASE your_mysql_db;
USE your_mysql_db;

CREATE TABLE offer_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    offer_code TEXT NOT NULL,
    description TEXT,
    short_code VARCHAR(6) UNIQUE NOT NULL
);
