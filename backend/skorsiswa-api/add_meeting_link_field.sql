  -- Add meeting_link field to the meetings table
-- This will allow advisors to paste meeting links (Zoom, Teams, etc.) when scheduling meetings

ALTER TABLE meetings 
ADD COLUMN meeting_link VARCHAR(500) DEFAULT '' AFTER location;

-- Update the existing meetings to show the new column structure
DESCRIBE meetings;

-- You can also add some sample data to test
-- UPDATE meetings SET meeting_link = 'https://zoom.us/j/1234567890' WHERE id = 1;
