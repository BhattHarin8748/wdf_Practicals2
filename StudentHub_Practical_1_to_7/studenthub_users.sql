CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    course VARCHAR(80) NOT NULL,
    year VARCHAR(20) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (full_name, username, email, mobile, course, year, gender, password_hash)
VALUES ('Demo User', 'demouser', 'demo@studenthub.edu', '+91 9876543210', 'B.Tech Computer Engineering', '1st Year', 'Male', '$2y$10$6FRxXbi0P2l7S3ZO8J9fGO6N3Ytwv0P8C8JYQ7I4EIbvVVN9Wl0PK')
ON DUPLICATE KEY UPDATE username = username;
