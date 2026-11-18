-- FINAL FIX: Correct ALL column names to match actual table structure

DROP TRIGGER IF EXISTS after_booking_insert;
DROP TRIGGER IF EXISTS after_booking_insert_doctor_notif;

-- Trigger 1: Patient notification
-- Table: notifications
-- Columns: booking_id, patient_username, priority_level, message, type, is_read
DELIMITER $$
CREATE TRIGGER after_booking_insert
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
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
-- Table: doctor_notifications  
-- Columns: doctor_username, booking_id, patient_name, queue_number, priority_level, message, type, is_read
DELIMITER $$
CREATE TRIGGER after_booking_insert_doctor_notif
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    DECLARE v_message TEXT;
    
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
            doctor_username,
            booking_id,
            patient_name,
            queue_number,
            priority_level,
            message,
            type,
            is_read,
            created_at
        ) VALUES (
            NEW.dokter,
            NEW.id,
            NEW.pasien,
            NEW.queue_number,
            NEW.priority,
            v_message,
            IF(NEW.priority = 5, 'emergency_patient', 'priority_patient'),
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
