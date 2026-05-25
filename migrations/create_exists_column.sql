-- Add exists column to character_vocations table
ALTER TABLE
    character_vocations
ADD
    COLUMN `exists` TINYINT(1) DEFAULT 1;

-- Update existing records to have exists = 1
UPDATE
    character_vocations
SET
    `exists` = 1
WHERE
    `exists` IS NULL;