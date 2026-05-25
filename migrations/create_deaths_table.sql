-- Add created column to character_vocations table
ALTER TABLE
    character_vocations
ADD
    COLUMN created_at DATETIME NULL;

-- Create table for storing character deaths
CREATE TABLE IF NOT EXISTS character_deaths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_name VARCHAR(255) NOT NULL,
    death_time DATETIME NOT NULL,
    level INT NOT NULL,
    killed_by VARCHAR(255) NOT NULL,
    is_player BOOLEAN NOT NULL,
    UNIQUE KEY unique_death (character_name, death_time, killed_by)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;