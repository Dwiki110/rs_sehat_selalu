-- Fixed version without COMMIT in trigger

DELIMITER //

-- Simplified trigger that generates queue number directly
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
    
    -- Set default priority if not provided
    IF NEW.priority IS NULL OR NEW.priority = 0 THEN
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

DELIMITER ;
