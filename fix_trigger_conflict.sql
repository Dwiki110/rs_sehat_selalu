-- Fix Trigger Conflict Error
-- The issue: trigger 'before_booking_insert' tries to UPDATE booking table 
-- while INSERT is in progress, which is not allowed in MySQL

-- Solution: Drop the problematic trigger since all logic is now in PHP

-- Drop the before_booking_insert trigger
DROP TRIGGER IF EXISTS before_booking_insert;

-- Keep only the AFTER triggers (these work fine)
-- after_booking_insert: creates patient notification
-- after_booking_insert_doctor_notif: creates doctor notification for priority 4-5

-- All priority detection and queue number generation is now handled in PHP
-- proses_booking.php line 56-93:
--   - determinePriority() function detects keywords
--   - QueueNumberGenerator class generates queue numbers
--   - Both values are inserted directly in the INSERT statement (line 97-100)

-- Verify remaining triggers
SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    ACTION_TIMING
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'rs_sehat_selalu' 
AND EVENT_OBJECT_TABLE = 'booking';

-- Expected result: 
-- 1. after_booking_insert (AFTER INSERT)
-- 2. after_booking_insert_doctor_notif (AFTER INSERT)
