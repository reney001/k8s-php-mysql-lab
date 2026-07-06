-- init.sql
-- Runs automatically on first MySQL container start
-- (mounted via ConfigMap/volume into /docker-entrypoint-initdb.d/)

CREATE DATABASE IF NOT EXISTS studentdb;
USE studentdb;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grant the app user access (username/password come from mysql-secret.yaml)
GRANT ALL PRIVILEGES ON studentdb.* TO 'appuser'@'%';
FLUSH PRIVILEGES;
