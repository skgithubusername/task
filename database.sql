-- Database setup for Notes App

-- Create database
CREATE DATABASE IF NOT EXISTS notes_app;
USE notes_app;

-- Create notes table
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (optional)
INSERT INTO notes (title, description, summary) VALUES
('Welcome Note', 'This is a sample note to demonstrate the Notes CRUD application with AI summary feature.', 'This note covers: Welcome Note - This is a sample note to demonstrate the Notes CRUD application with AI summary feature...'),
('Project Ideas', 'Thinking about building a todo app, a blog platform, and an e-commerce site. These would help improve my web development skills.', 'This note covers: Project Ideas - Thinking about building a todo app, a blog platform, and an e-commerce site. These would...');
