-- Migration to add validity dates functionality to dishes
-- Date: 2024-12-23
-- Description: Add validity date fields and availability days to dishes table

USE ejercito_restaurant;

-- Add validity fields to dishes table
ALTER TABLE dishes 
ADD COLUMN validity_start DATE NULL AFTER active,
ADD COLUMN validity_end DATE NULL AFTER validity_start,
ADD COLUMN availability_days VARCHAR(20) DEFAULT '1234567' AFTER validity_end,
ADD COLUMN has_validity BOOLEAN DEFAULT FALSE AFTER availability_days;

-- Update existing dishes to have no validity restrictions
UPDATE dishes SET 
    has_validity = FALSE,
    availability_days = '1234567'
WHERE has_validity IS NULL;

-- Create index for performance on validity queries
CREATE INDEX idx_dishes_validity ON dishes(has_validity, validity_start, validity_end, active);