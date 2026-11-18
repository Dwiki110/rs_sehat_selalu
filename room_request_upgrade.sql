-- Room Request System for RS Sehat Selalu
-- Sistem request rawat inap dengan prioritas dan approval

-- Create room_requests table
CREATE TABLE IF NOT EXISTS room_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    diagnosis TEXT NOT NULL,
    reason TEXT NOT NULL,
    priority TINYINT NOT NULL COMMENT '1-5 priority levels',
    room_type ENUM('ICU', 'VVIP', 'VIP', 'Kelas 1', 'Kelas 2', 'Kelas 3') NOT NULL DEFAULT 'Kelas 3',
    estimated_days INT DEFAULT 1 COMMENT 'Estimated days for hospitalization',
    status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    admin_notes TEXT NULL,
    room_number VARCHAR(20) NULL,
    approved_by VARCHAR(50) NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_doctor (doctor_name),
    INDEX idx_patient (patient_name),
    INDEX idx_booking (booking_id),
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create doctor_notifications table
CREATE TABLE IF NOT EXISTS doctor_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_username VARCHAR(50) NOT NULL,
    booking_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    queue_number VARCHAR(20) NOT NULL,
    priority_level TINYINT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('emergency_patient', 'priority_patient', 'appointment_reminder') NOT NULL DEFAULT 'priority_patient',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_doctor (doctor_username),
    INDEX idx_booking (booking_id),
    INDEX idx_is_read (is_read),
    INDEX idx_priority (priority_level),
    INDEX idx_type (type),
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create admin_notifications table for room requests
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_request_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    priority_level TINYINT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('room_request', 'emergency_room', 'system_alert') NOT NULL DEFAULT 'room_request',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_room_request (room_request_id),
    INDEX idx_is_read (is_read),
    INDEX idx_priority (priority_level),
    INDEX idx_type (type),
    FOREIGN KEY (room_request_id) REFERENCES room_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Trigger to create doctor notification for high priority bookings
DELIMITER //

CREATE TRIGGER after_booking_insert_doctor_notif
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    DECLARE v_message TEXT;
    
    -- Create doctor notification for priority 4-5 (Emergency/Critical)
    IF NEW.priority >= 4 THEN
        SET v_message = CASE
            WHEN NEW.priority = 5 THEN 
                CONCAT('🚨 KRITIS - Pasien ', NEW.pasien, ' (', NEW.queue_number, ') memerlukan penanganan SEGERA! Keluhan: ', NEW.keluhan)
            WHEN NEW.priority = 4 THEN 
                CONCAT('⚠️ DARURAT - Pasien ', NEW.pasien, ' (', NEW.queue_number, ') prioritas tinggi. Keluhan: ', NEW.keluhan)
        END;
        
        INSERT INTO doctor_notifications (doctor_username, booking_id, patient_name, queue_number, priority_level, message, type)
        VALUES (
            NEW.dokter, 
            NEW.id, 
            NEW.pasien, 
            NEW.queue_number, 
            NEW.priority, 
            v_message,
            CASE WHEN NEW.priority = 5 THEN 'emergency_patient' ELSE 'priority_patient' END
        );
    END IF;
END //

-- Trigger to create admin notification for room requests
CREATE TRIGGER after_room_request_insert
AFTER INSERT ON room_requests
FOR EACH ROW
BEGIN
    DECLARE v_message TEXT;
    DECLARE v_type VARCHAR(20);
    
    SET v_message = CONCAT(
        'Request rawat inap dari Dr. ', NEW.doctor_name, 
        ' untuk pasien ', NEW.patient_name,
        ' - Prioritas: ', 
        CASE NEW.priority
            WHEN 5 THEN 'KRITIS'
            WHEN 4 THEN 'DARURAT'
            WHEN 3 THEN 'MENDESAK'
            ELSE 'NORMAL'
        END,
        '. Diagnosis: ', SUBSTRING(NEW.diagnosis, 1, 100)
    );
    
    SET v_type = CASE 
        WHEN NEW.priority >= 4 THEN 'emergency_room'
        ELSE 'room_request'
    END;
    
    INSERT INTO admin_notifications (room_request_id, patient_name, doctor_name, priority_level, message, type)
    VALUES (NEW.id, NEW.patient_name, NEW.doctor_name, NEW.priority, v_message, v_type);
END //

DELIMITER ;

-- View for pending room requests with priority
CREATE OR REPLACE VIEW v_pending_room_requests AS
SELECT 
    rr.id,
    rr.booking_id,
    rr.patient_name,
    rr.doctor_name,
    rr.diagnosis,
    rr.reason,
    rr.priority,
    CASE rr.priority
        WHEN 5 THEN 'Kritis'
        WHEN 4 THEN 'Darurat'
        WHEN 3 THEN 'Mendesak'
        WHEN 2 THEN 'Semi Mendesak'
        WHEN 1 THEN 'Tidak Mendesak'
    END AS priority_name,
    rr.room_type,
    rr.estimated_days,
    rr.status,
    rr.created_at,
    TIMESTAMPDIFF(MINUTE, rr.created_at, NOW()) AS waiting_minutes,
    b.queue_number,
    b.keluhan
FROM room_requests rr
LEFT JOIN booking b ON rr.booking_id = b.id
WHERE rr.status = 'pending'
ORDER BY rr.priority DESC, rr.created_at ASC;

-- View for doctor's emergency patients
CREATE OR REPLACE VIEW v_doctor_emergency_patients AS
SELECT 
    b.id AS booking_id,
    b.queue_number,
    b.pasien AS patient_name,
    b.dokter AS doctor_name,
    b.tanggal,
    b.jam,
    b.keluhan,
    b.priority,
    CASE b.priority
        WHEN 5 THEN 'Kritis'
        WHEN 4 THEN 'Darurat'
        WHEN 3 THEN 'Mendesak'
    END AS priority_name,
    b.status,
    b.created_at AS arrival_time,
    TIMESTAMPDIFF(MINUTE, b.created_at, NOW()) AS waiting_minutes,
    COUNT(dn.id) AS unread_notifications
FROM booking b
LEFT JOIN doctor_notifications dn ON b.id = dn.booking_id AND dn.is_read = 0
WHERE b.priority >= 3 
  AND b.status IN ('waiting', 'in_progress')
GROUP BY b.id
ORDER BY b.priority DESC, b.created_at ASC;

-- Insert reference data for room types
CREATE TABLE IF NOT EXISTS room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 1,
    price_per_day DECIMAL(10,2) DEFAULT 0,
    facilities TEXT,
    is_available TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO room_types (type_name, capacity, price_per_day, facilities) VALUES
('ICU', 1, 2000000, 'Intensive care, Ventilator, Monitor 24/7'),
('VVIP', 1, 1500000, 'Private room, AC, TV, Sofa, Private bathroom'),
('VIP', 1, 1000000, 'Private room, AC, TV, Private bathroom'),
('Kelas 1', 2, 500000, 'Semi-private, AC, Shared bathroom'),
('Kelas 2', 4, 300000, 'Shared room, Fan, Shared bathroom'),
('Kelas 3', 6, 150000, 'Shared room, Basic facilities')
ON DUPLICATE KEY UPDATE type_name = VALUES(type_name);

COMMIT;

-- Usage instructions:
-- 1. Run this script to add room request system
-- 2. Doctor can create room request via dokter_dashboard.php
-- 3. Admin receives notification and can approve via admin_dashboard.php
-- 4. System tracks priority and sends alerts

-- Verification queries:
-- SELECT * FROM v_pending_room_requests;
-- SELECT * FROM v_doctor_emergency_patients WHERE doctor_name = 'dwiki';
-- SELECT * FROM doctor_notifications WHERE is_read = 0 ORDER BY created_at DESC;
-- SELECT * FROM admin_notifications WHERE is_read = 0 ORDER BY priority_level DESC, created_at DESC;
