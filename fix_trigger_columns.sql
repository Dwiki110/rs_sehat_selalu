-- Fix Trigger with Correct Column Names
-- Issue: Column names in trigger don't match actual table structure

-- Drop existing triggers
DROP TRIGGER IF EXISTS after_booking_insert;
DROP TRIGGER IF EXISTS after_booking_insert_doctor_notif;

-- Recreate with CORRECT column names

-- Trigger 1: Patient notification
DELIMITER $$
CREATE TRIGGER after_booking_insert
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    -- notifications table has: booking_id, patient_username, priority_level, message, type, is_read
    IF NEW.priority >= 3 THEN
        INSERT INTO notifications (
            booking_id,
            patient_username,
            priority_level,
            message,
            type,
            is_read,
            created_at
        ) VALUES (
            NEW.id,
            NEW.pasien,
            NEW.priority,
            CONCAT('Booking Anda telah dikonfirmasi dengan nomor antrian ', NEW.queue_number, 
                   '. Prioritas: ', NEW.priority, '. Keluhan: ', NEW.keluhan),
            CASE 
                WHEN NEW.priority = 5 THEN 'emergency'
                WHEN NEW.priority = 4 THEN 'emergency'
                WHEN NEW.priority = 3 THEN 'priority_assigned'
                ELSE 'priority_assigned'
            END,
            0,
            NOW()
        );
    END IF;
END$$
DELIMITER ;

-- Trigger 2: Doctor notification  
DELIMITER $$
CREATE TRIGGER after_booking_insert_doctor_notif
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    DECLARE v_message TEXT;
    
    -- doctor_notifications table has: booking_id, doctor_username, notification_type, message, priority, patient_name, complaint, queue_number, is_read
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

-- Verify
SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    ACTION_TIMING
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'rs_sehat_selalu' 
AND EVENT_OBJECT_TABLE = 'booking';
