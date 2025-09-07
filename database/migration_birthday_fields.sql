-- Migration script for birthday field changes
-- This script documents the database changes that support saving day and month separately
-- According to the problem statement, these changes have already been applied to the database

-- Add columns for day and month of birthday
ALTER TABLE customers
  ADD COLUMN birthday_day TINYINT UNSIGNED AFTER birthday,
  ADD COLUMN birthday_month TINYINT UNSIGNED AFTER birthday_day;

-- Migrate existing data from the 'birthday' column (VARCHAR DD/MM format) to new columns
UPDATE customers
SET
  birthday_day = CAST(SUBSTRING_INDEX(birthday, '/', 1) AS UNSIGNED),
  birthday_month = CAST(SUBSTRING_INDEX(birthday, '/', -1) AS UNSIGNED)
WHERE birthday IS NOT NULL AND birthday != '' AND birthday LIKE '%/%';

-- Optional: Create index for birthday queries (improves performance for birthday searches)
CREATE INDEX idx_customers_birthday ON customers(birthday_month, birthday_day);

-- Verification queries to check the migration
-- SELECT id, name, birthday, birthday_day, birthday_month FROM customers WHERE birthday IS NOT NULL LIMIT 10;
-- SELECT COUNT(*) as migrated_birthdays FROM customers WHERE birthday_day IS NOT NULL AND birthday_month IS NOT NULL;
-- SELECT COUNT(*) as original_birthdays FROM customers WHERE birthday IS NOT NULL AND birthday != '';