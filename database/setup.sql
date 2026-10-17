
-- University Student Portal
-- Information Security Assignment
-- Database Setup

CREATE DATABASE IF NOT EXISTS is_security_project
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE is_security_project;

-- Student accounts
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin')
        NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- University announcements
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student comments
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (announcement_id)
        REFERENCES announcements(id),

    FOREIGN KEY (user_id)
        REFERENCES users(id)
);

-- Fee records: retained for schema compatibility
CREATE TABLE IF NOT EXISTS fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    semester VARCHAR(30) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('paid', 'unpaid')
        NOT NULL DEFAULT 'unpaid',

    FOREIGN KEY (user_id)
        REFERENCES users(id)
);

-- Sample announcements
INSERT INTO announcements (title, content)
SELECT 'Welcome Students',
       'Welcome to the university student portal.'
WHERE NOT EXISTS (
    SELECT 1 FROM announcements
    WHERE title = 'Welcome Students'
);

INSERT INTO announcements (title, content)
SELECT 'Midterm Schedule',
       'The midterm examination schedule will be announced soon.'
WHERE NOT EXISTS (
    SELECT 1 FROM announcements
    WHERE title = 'Midterm Schedule'
);

-- Student accounts are registered through safe/register.php.
-- Passwords are hashed using PHP password_hash().
-- No real credentials or personal information are included.
