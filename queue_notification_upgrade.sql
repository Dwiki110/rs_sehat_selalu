-- Queue Number and Notification System Upgrade
-- RS Sehat Selalu - Hospital Management System

-- Add columns to booking table for queue number management
ALTER TABLE booking 
ADD COLUMN queue_number VARCHAR(20) UNIQUE COMMENT 'Format: QA-YYYYMMDD-XXX',
ADD COLUMN priority_notification_sent TINYINT(1) DEFAULT 0 COMMENT 'Flag if priority notification sent',
ADD COLUMN estimated_call_time TIME NULL COMMENT 'Estimated time patient will be called',
ADD INDEX idx_queue_number (queue_number);

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    patient_username VARCHAR(50) NOT NULL,
    priority_level TINYINT NOT NULL COMMENT '1-5 priority levels',
    message TEXT NOT NULL,
    type ENUM('priority_assigned', 'queue_update', 'call_soon', 'emergency') NOT NULL DEFAULT 'priority_assigned',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient (patient_username),
    INDEX idx_booking (booking_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create queue_counter table for daily counter
CREATE TABLE IF NOT EXISTS queue_counter (
    date DATE PRIMARY KEY,
    counter INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Stored procedure to generate queue number
DELIMITER //

CREATE PROCEDURE GenerateQueueNumber(IN p_date DATE, OUT p_queue_number VARCHAR(20))
BEGIN
    DECLARE v_counter INT;
    DECLARE v_formatted_date VARCHAR(8);
    
    -- Lock the row for update
    START TRANSACTION;
    
    -- Get or create counter for the date
    INSERT INTO queue_counter (date, counter) 
    VALUES (p_date, 1)
    ON DUPLICATE KEY UPDATE counter = counter + 1;
    
    -- Get the current counter
    SELECT counter INTO v_counter 
    FROM queue_counter 
    WHERE date = p_date
    FOR UPDATE;
    
    -- Format date as YYYYMMDD
    SET v_formatted_date = DATE_FORMAT(p_date, '%Y%m%d');
    
    -- Generate queue number: QA-YYYYMMDD-XXX
    SET p_queue_number = CONCAT('QA-', v_formatted_date, '-', LPAD(v_counter, 3, '0'));
    
    COMMIT;
END //

-- Stored procedure to create notification
CREATE PROCEDURE CreateNotification(
    IN p_booking_id INT,
    IN p_patient_username VARCHAR(50),
    IN p_priority_level TINYINT,
    IN p_message TEXT,
    IN p_type VARCHAR(20)
)
BEGIN
    INSERT INTO notifications (booking_id, patient_username, priority_level, message, type)
    VALUES (p_booking_id, p_patient_username, p_priority_level, p_message, p_type);
END //

-- Function to get priority message template
CREATE FUNCTION GetPriorityMessage(p_priority TINYINT, p_queue_number VARCHAR(20)) 
RETURNS TEXT
DETERMINISTIC
BEGIN
    DECLARE v_message TEXT;
    
    CASE p_priority
        WHEN 5 THEN 
            SET v_message = CONCAT('🚨 KRITIS - Nomor antrian Anda: ', p_queue_number, 
                '. Anda akan segera ditangani. Kondisi Anda memerlukan penanganan SEGERA!');
        WHEN 4 THEN 
            SET v_message = CONCAT('⚠️ DARURAT - Nomor antrian Anda: ', p_queue_number, 
                '. Anda akan dipanggil dalam 30 menit. Harap bersiap!');
        WHEN 3 THEN 
            SET v_message = CONCAT('⚡ MENDESAK - Nomor antrian Anda: ', p_queue_number, 
                '. Estimasi pemanggilan dalam 2 jam. Harap tetap standby.');
        WHEN 2 THEN 
            SET v_message = CONCAT('ℹ️ SEMI MENDESAK - Nomor antrian Anda: ', p_queue_number, 
                '. Estimasi pemanggilan dalam 4 jam. Silakan tunggu dengan tenang.');
        ELSE 
            SET v_message = CONCAT('✅ Nomor antrian Anda: ', p_queue_number, 
                '. Silakan tunggu, Anda akan dipanggil sesuai urutan.');
    END CASE;
    
    RETURN v_message;
END //

DELIMITER ;

-- Trigger to auto-generate queue number on booking insert
DELIMITER //

CREATE TRIGGER before_booking_insert
BEFORE INSERT ON booking
FOR EACH ROW
BEGIN
    DECLARE v_queue_number VARCHAR(20);
    DECLARE v_booking_date DATE;
    
    -- Use booking date or current date
    SET v_booking_date = COALESCE(NEW.tanggal, CURDATE());
    
    -- Generate queue number if not provided
    IF NEW.queue_number IS NULL THEN
        CALL GenerateQueueNumber(v_booking_date, v_queue_number);
        SET NEW.queue_number = v_queue_number;
    END IF;
    
    -- Set default priority if not provided
    IF NEW.priority IS NULL OR NEW.priority = 0 THEN
        -- Determine priority based on keluhan
        SET NEW.priority = CASE
            WHEN LOWER(NEW.keluhan) REGEXP '(henti jantung|tidak sadar|pingsan|kejang|sesak nafas berat|pendarahan hebat)' THEN 5
            WHEN LOWER(NEW.keluhan) REGEXP '(nyeri dada|sesak nafas|demam tinggi|muntah darah|kecelakaan)' THEN 4
            WHEN LOWER(NEW.keluhan) REGEXP '(demam|diare|nyeri|sakit kepala berat|luka)' THEN 3
            WHEN LOWER(NEW.keluhan) REGEXP '(batuk|pilek|flu|pusing)' THEN 2
            ELSE 1
        END;
    END IF;
    
    -- Set default status if not provided
    IF NEW.status IS NULL OR NEW.status = '' THEN
        SET NEW.status = 'waiting';
    END IF;
END //

-- Trigger to auto-create notification after booking insert
CREATE TRIGGER after_booking_insert
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    DECLARE v_message TEXT;
    DECLARE v_notification_type VARCHAR(20);
    
    -- Generate priority message
    SET v_message = GetPriorityMessage(NEW.priority, NEW.queue_number);
    
    -- Determine notification type
    SET v_notification_type = CASE
        WHEN NEW.priority >= 4 THEN 'emergency'
        WHEN NEW.priority = 3 THEN 'queue_update'
        ELSE 'priority_assigned'
    END;
    
    -- Create notification for high priority cases
    IF NEW.priority >= 3 THEN
        INSERT INTO notifications (booking_id, patient_username, priority_level, message, type)
        VALUES (NEW.id, NEW.pasien, NEW.priority, v_message, v_notification_type);
        
        -- Mark that notification has been sent
        UPDATE booking SET priority_notification_sent = 1 WHERE id = NEW.id;
    END IF;
END //

DELIMITER ;

-- View for queue with notifications
CREATE OR REPLACE VIEW v_queue_with_notifications AS
SELECT 
    b.id,
    b.queue_number,
    b.pasien AS patient_name,
    b.dokter,
    b.tanggal,
    b.hari,
    b.jam,
    b.keluhan,
    b.priority,
    CASE b.priority
        WHEN 5 THEN 'Kritis'
        WHEN 4 THEN 'Darurat'
        WHEN 3 THEN 'Mendesak'
        WHEN 2 THEN 'Semi Mendesak'
        WHEN 1 THEN 'Tidak Mendesak'
        ELSE 'Unknown'
    END AS priority_name,
    b.status,
    b.created_at AS arrival_time,
    b.called_at,
    b.priority_notification_sent,
    b.estimated_call_time,
    TIMESTAMPDIFF(MINUTE, b.created_at, COALESCE(b.called_at, NOW())) AS wait_time_minutes,
    COUNT(n.id) AS unread_notifications
FROM booking b
LEFT JOIN notifications n ON b.id = n.booking_id AND n.is_read = 0
WHERE b.status IN ('waiting', 'in_progress')
GROUP BY b.id
ORDER BY b.priority DESC, b.created_at ASC;

-- Insert initial counter for today
INSERT IGNORE INTO queue_counter (date, counter) VALUES (CURDATE(), 0);

-- Grant permissions (if needed)
-- GRANT EXECUTE ON PROCEDURE GenerateQueueNumber TO 'your_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE CreateNotification TO 'your_user'@'localhost';

COMMIT;

-- Usage instructions:
-- 1. Run this script after priority_queue_upgrade.sql
-- 2. Queue numbers will be auto-generated on booking insert
-- 3. Notifications will be auto-created for priority 3+ bookings
-- 4. Access notifications via NotificationManager.php
-- 5. View queue with notifications: SELECT * FROM v_queue_with_notifications;

-- Verification queries:
-- SELECT * FROM v_queue_with_notifications;
-- SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;
-- SELECT * FROM queue_counter ORDER BY date DESC LIMIT 7;
-- CALL GenerateQueueNumber(CURDATE(), @qn); SELECT @qn;
