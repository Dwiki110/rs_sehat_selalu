-- Fix ALL Trigger Conflicts
-- Issue: Triggers try to UPDATE booking table during INSERT/UPDATE operations

-- Step 1: Drop ALL triggers on booking table
DROP TRIGGER IF EXISTS before_booking_insert;
DROP TRIGGER IF EXISTS after_booking_insert;
DROP TRIGGER IF EXISTS after_booking_insert_doctor_notif;

-- Step 2: Recreate SAFE triggers (no UPDATE to booking table)

-- Trigger 1: Create patient notification (NO UPDATE to booking)
DELIMITER $$
CREATE TRIGGER after_booking_insert
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    -- Only create notification, do NOT update booking table
    IF NEW.priority >= 3 THEN
        INSERT INTO notifications (
            booking_id,
            patient_username,
            notification_type,
            message,
            priority,
            queue_number,
            is_read,
            created_at
        ) VALUES (
            NEW.id,
            NEW.pasien,
            CASE 
                WHEN NEW.priority = 5 THEN 'critical'
                WHEN NEW.priority = 4 THEN 'emergency'
                WHEN NEW.priority = 3 THEN 'urgent'
                ELSE 'normal'
            END,
            CONCAT('Booking Anda telah dikonfirmasi dengan nomor antrian ', NEW.queue_number, 
                   '. Prioritas: ', NEW.priority, '. Keluhan: ', NEW.keluhan),
            NEW.priority,
            NEW.queue_number,
            0,
            NOW()
        );
    END IF;
END$$
DELIMITER ;

-- Trigger 2: Create doctor notification (NO UPDATE to booking)
DELIMITER $$
CREATE TRIGGER after_booking_insert_doctor_notif
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    DECLARE v_message TEXT;
    
    -- Only create notification for high priority (4-5)
    IF NEW.priority >= 4 THEN
        SET v_message = CONCAT(
            'PASIEN PRIORITAS TINGGI - ',
            'Pasien: ', NEW.pasien,
            ', Keluhan: ', NEW.keluhan,
            ', Priority: ', NEW.priority,
            ', Queue: ', NEW.queue_number,
            ', Tanggal: ', NEW.tanggal
        );
        
        INSERT INTO doctor_notifications (
            booking_id,
            doctor_username,
            notification_type,
            message,
            priority,
            patient_name,
            complaint,
            queue_number,
            is_read,
            created_at
        ) VALUES (
            NEW.id,
            NEW.dokter,
            IF(NEW.priority = 5, 'emergency_patient', 'priority_patient'),
            v_message,
            NEW.priority,
            NEW.pasien,
            NEW.keluhan,
            NEW.queue_number,
            0,
            NOW()
        );
    END IF;
END$$
DELIMITER ;

-- Step 3: Verify triggers
SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    ACTION_TIMING,
    CREATED
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'rs_sehat_selalu' 
AND EVENT_OBJECT_TABLE = 'booking'
ORDER BY CREATED;

-- Expected: Only 2 AFTER INSERT triggers, no UPDATE statements inside
