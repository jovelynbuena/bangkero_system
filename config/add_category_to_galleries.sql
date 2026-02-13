-- Add category column to galleries table
ALTER TABLE galleries ADD COLUMN category VARCHAR(100) DEFAULT 'Uncategorized' AFTER title;
