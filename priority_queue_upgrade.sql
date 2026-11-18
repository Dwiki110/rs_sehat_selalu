-- Priority Queue Enhancement for RS Sehat Selalu
-- Menambahkan kolom priority dan tracking untuk implementasi Max Heap

-- Add priority column to booking table
ALTER TABLE booking 
ADD COLUMN priority TINYINT DEFAULT 1 COMMENT '1=Non-Urgent, 2=Semi-Urgent, 3=Urgent, 4=Emergency, 5=Critical',
ADD COLUMN status VARCHAR(20) DEFAULT 'waiting' COMMENT 'waiting, in_progress, completed, cancelled',
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN called_at TIMESTAMP NULL COMMENT 'Waktu pasien dipanggil',
ADD INDEX idx_priority (priority),
ADD INDEX idx_status (status),
ADD INDEX idx_created_at (created_at);

-- Create queue_statistics table untuk tracking
CREATE TABLE IF NOT EXISTS queue_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_patients INT DEFAULT 0,
    critical_count INT DEFAULT 0,
    emergency_count INT DEFAULT 0,
    urgent_count INT DEFAULT 0,
    semi_urgent_count INT DEFAULT 0,
    non_urgent_count INT DEFAULT 0,
    average_wait_time_minutes DECIMAL(10,2) DEFAULT 0,
    max_wait_time_minutes INT DEFAULT 0,
    total_served INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create priority_log table untuk audit trail prioritas
CREATE TABLE IF NOT EXISTS priority_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    old_priority TINYINT NULL,
    new_priority TINYINT NOT NULL,
    reason VARCHAR(255) NULL,
    changed_by VARCHAR(50) NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Update existing booking records with default priority based on keluhan
UPDATE booking 
SET priority = CASE
    WHEN LOWER(keluhan) REGEXP '(henti jantung|tidak sadar|pingsan|kejang|sesak nafas berat|pendarahan hebat)' THEN 5
    WHEN LOWER(keluhan) REGEXP '(nyeri dada|sesak nafas|demam tinggi|muntah darah|kecelakaan)' THEN 4
    WHEN LOWER(keluhan) REGEXP '(demam|diare|nyeri|sakit kepala berat|luka)' THEN 3
    WHEN LOWER(keluhan) REGEXP '(batuk|pilek|flu|pusing)' THEN 2
    ELSE 1
END
WHERE priority IS NULL OR priority = 0;

-- Create stored procedure untuk update statistics
DELIMITER //

CREATE PROCEDURE UpdateQueueStatistics(IN p_date DATE)
BEGIN
    DECLARE v_total INT DEFAULT 0;
    DECLARE v_critical INT DEFAULT 0;
    DECLARE v_emergency INT DEFAULT 0;
    DECLARE v_urgent INT DEFAULT 0;
    DECLARE v_semi_urgent INT DEFAULT 0;
    DECLARE v_non_urgent INT DEFAULT 0;
    DECLARE v_avg_wait DECIMAL(10,2) DEFAULT 0;
    DECLARE v_max_wait INT DEFAULT 0;
    DECLARE v_served INT DEFAULT 0;
    
    -- Count total patients for the date
    SELECT COUNT(*) INTO v_total
    FROM booking
    WHERE DATE(created_at) = p_date;
    
    -- Count by priority
    SELECT 
        SUM(CASE WHEN priority = 5 THEN 1 ELSE 0 END),
        SUM(CASE WHEN priority = 4 THEN 1 ELSE 0 END),
        SUM(CASE WHEN priority = 3 THEN 1 ELSE 0 END),
        SUM(CASE WHEN priority = 2 THEN 1 ELSE 0 END),
        SUM(CASE WHEN priority = 1 THEN 1 ELSE 0 END)
    INTO v_critical, v_emergency, v_urgent, v_semi_urgent, v_non_urgent
    FROM booking
    WHERE DATE(created_at) = p_date;
    
    -- Calculate average wait time
    SELECT 
        COALESCE(AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(called_at, NOW()))), 0),
        COALESCE(MAX(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(called_at, NOW()))), 0)
    INTO v_avg_wait, v_max_wait
    FROM booking
    WHERE DATE(created_at) = p_date;
    
    -- Count served patients
    SELECT COUNT(*) INTO v_served
    FROM booking
    WHERE DATE(created_at) = p_date AND status IN ('completed', 'in_progress');
    
    -- Insert or update statistics
    INSERT INTO queue_statistics (
        date, total_patients, critical_count, emergency_count, urgent_count,
        semi_urgent_count, non_urgent_count, average_wait_time_minutes,
        max_wait_time_minutes, total_served
    ) VALUES (
        p_date, v_total, v_critical, v_emergency, v_urgent,
        v_semi_urgent, v_non_urgent, v_avg_wait, v_max_wait, v_served
    )
    ON DUPLICATE KEY UPDATE
        total_patients = v_total,
        critical_count = v_critical,
        emergency_count = v_emergency,
        urgent_count = v_urgent,
        semi_urgent_count = v_semi_urgent,
        non_urgent_count = v_non_urgent,
        average_wait_time_minutes = v_avg_wait,
        max_wait_time_minutes = v_max_wait,
        total_served = v_served;
END //

CREATE PROCEDURE LogPriorityChange(
    IN p_booking_id INT,
    IN p_patient_name VARCHAR(100),
    IN p_old_priority TINYINT,
    IN p_new_priority TINYINT,
    IN p_reason VARCHAR(255),
    IN p_changed_by VARCHAR(50)
)
BEGIN
    INSERT INTO priority_log (
        booking_id, patient_name, old_priority, new_priority, reason, changed_by
    ) VALUES (
        p_booking_id, p_patient_name, p_old_priority, p_new_priority, p_reason, p_changed_by
    );
END //

DELIMITER ;

-- Create event untuk auto-update statistics setiap hari
CREATE EVENT IF NOT EXISTS update_daily_queue_stats
ON SCHEDULE EVERY 1 DAY
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 00:00:00')
DO
  CALL UpdateQueueStatistics(CURDATE() - INTERVAL 1 DAY);

-- Create view untuk quick access ke priority queue
CREATE OR REPLACE VIEW v_priority_queue AS
SELECT 
    b.id,
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
    TIMESTAMPDIFF(MINUTE, b.created_at, COALESCE(b.called_at, NOW())) AS wait_time_minutes,
    CASE 
        WHEN b.status = 'waiting' AND TIMESTAMPDIFF(MINUTE, b.created_at, NOW()) > 30 THEN 'OVERDUE'
        WHEN b.status = 'waiting' THEN 'WAITING'
        WHEN b.status = 'in_progress' THEN 'IN_PROGRESS'
        WHEN b.status = 'completed' THEN 'COMPLETED'
        ELSE 'CANCELLED'
    END AS queue_status
FROM booking b
WHERE b.status IN ('waiting', 'in_progress')
ORDER BY b.priority DESC, b.created_at ASC;

-- Insert sample priority levels reference
CREATE TABLE IF NOT EXISTS priority_levels (
    level TINYINT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    color_code VARCHAR(20),
    max_wait_time_minutes INT,
    icon VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO priority_levels (level, name, description, color_code, max_wait_time_minutes, icon) VALUES
(5, 'Kritis', 'Penanganan Segera - Kondisi mengancam nyawa', '#DC2626', 0, 'exclamation-triangle-fill'),
(4, 'Darurat', 'Penanganan dalam 30 menit - Kondisi serius', '#F59E0B', 30, 'lightning-fill'),
(3, 'Mendesak', 'Penanganan dalam 2 jam - Perlu perhatian cepat', '#EAB308', 120, 'exclamation-circle-fill'),
(2, 'Semi Mendesak', 'Penanganan dalam 4 jam - Kondisi stabil', '#3B82F6', 240, 'info-circle-fill'),
(1, 'Tidak Mendesak', 'Penanganan dalam 24 jam - Kondisi ringan', '#10B981', 1440, 'check-circle-fill')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    color_code = VALUES(color_code),
    max_wait_time_minutes = VALUES(max_wait_time_minutes),
    icon = VALUES(icon);

-- Grant permissions (if needed)
-- GRANT EXECUTE ON PROCEDURE UpdateQueueStatistics TO 'your_user'@'localhost';
-- GRANT EXECUTE ON PROCEDURE LogPriorityChange TO 'your_user'@'localhost';

-- Create indexes for better performance
CREATE INDEX idx_booking_priority_status ON booking(priority DESC, status, created_at ASC);
CREATE INDEX idx_booking_created_at ON booking(created_at);

-- Add comments
ALTER TABLE booking 
MODIFY COLUMN priority TINYINT DEFAULT 1 
COMMENT 'Priority level: 1=Non-Urgent, 2=Semi-Urgent, 3=Urgent, 4=Emergency, 5=Critical';

COMMIT;

-- Instructions for usage:
-- 1. Run this script to add priority queue features
-- 2. Use PriorityQueue.php class for heap operations
-- 3. Access priority_queue_dashboard.php to view sorted queue
-- 4. Statistics are automatically updated daily
-- 5. Priority can be manually adjusted through admin interface

-- Verification queries:
-- SELECT * FROM v_priority_queue;
-- SELECT * FROM queue_statistics ORDER BY date DESC LIMIT 7;
-- SELECT * FROM priority_log ORDER BY changed_at DESC LIMIT 10;
-- CALL UpdateQueueStatistics(CURDATE());
