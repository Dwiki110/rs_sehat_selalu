-- Update Priority Keywords
-- Menambahkan keyword: Parah, Tinggi, Sesak, Kejang untuk prioritas tinggi

-- Drop existing trigger
DROP TRIGGER IF EXISTS before_booking_insert;

DELIMITER //

-- Recreate trigger with updated keywords
CREATE TRIGGER before_booking_insert
BEFORE INSERT ON booking
FOR EACH ROW
BEGIN
    DECLARE v_counter INT;
    DECLARE v_formatted_date VARCHAR(8);
    DECLARE v_booking_date DATE;
    
    -- Use booking date or current date
    SET v_booking_date = COALESCE(NEW.tanggal, CURDATE());
    
    -- Generate queue number if not provided
    IF NEW.queue_number IS NULL THEN
        -- Get or increment counter
        INSERT INTO queue_counter (date, counter) 
        VALUES (v_booking_date, 1)
        ON DUPLICATE KEY UPDATE counter = counter + 1;
        
        -- Get current counter
        SELECT counter INTO v_counter 
        FROM queue_counter 
        WHERE date = v_booking_date;
        
        -- Format date as YYYYMMDD
        SET v_formatted_date = DATE_FORMAT(v_booking_date, '%Y%m%d');
        
        -- Generate queue number: QA-YYYYMMDD-XXX
        SET NEW.queue_number = CONCAT('QA-', v_formatted_date, '-', LPAD(v_counter, 3, '0'));
    END IF;
    
    -- Set default priority if not provided - UPDATED WITH NEW KEYWORDS
    IF NEW.priority IS NULL OR NEW.priority = 0 THEN
        SET NEW.priority = CASE
            -- Critical - Level 5: Added 'parah', 'kondisi parah', 'kejang'
            WHEN LOWER(NEW.keluhan) REGEXP '(henti jantung|tidak sadar|pingsan|kejang|sesak nafas berat|pendarahan hebat|parah|kondisi parah|sangat parah)' THEN 5
            
            -- Emergency - Level 4: Added 'sesak', 'tinggi', 'demam parah', 'sakit parah'
            WHEN LOWER(NEW.keluhan) REGEXP '(nyeri dada|sesak nafas|sesak|demam tinggi|tinggi|muntah darah|kecelakaan|demam parah|sakit parah|kondisi tinggi)' THEN 4
            
            -- Urgent - Level 3
            WHEN LOWER(NEW.keluhan) REGEXP '(demam|diare|nyeri|sakit kepala berat|luka)' THEN 3
            
            -- Semi urgent - Level 2
            WHEN LOWER(NEW.keluhan) REGEXP '(batuk|pilek|flu|pusing)' THEN 2
            
            -- Non-urgent - Level 1
            ELSE 1
        END;
    END IF;
    
    -- Set default status if not provided
    IF NEW.status IS NULL OR NEW.status = '' THEN
        SET NEW.status = 'waiting';
    END IF;
END //

DELIMITER ;

-- Update priority_queue_dashboard.php detection function (in comments for reference)
-- Also update the determinePriority function in the following files:
-- 1. proses_booking.php - DONE
-- 2. priority_queue_dashboard.php - Need to update

-- Test cases for new keywords:
-- INSERT INTO booking (pasien, dokter, tanggal, hari, jam, keluhan) 
-- VALUES ('test', 'dwiki', CURDATE(), 'Senin', '09:00-10:00', 'kondisi parah kejang');
-- Expected: priority = 5

-- INSERT INTO booking (pasien, dokter, tanggal, hari, jam, keluhan) 
-- VALUES ('test', 'dwiki', CURDATE(), 'Senin', '09:00-10:00', 'sesak nafas tinggi');
-- Expected: priority = 4

COMMIT;

-- Verification
SELECT 'Trigger updated successfully' AS status;
SHOW TRIGGERS LIKE 'booking';
