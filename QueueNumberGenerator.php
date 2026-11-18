<?php
/**
 * Queue Number Generator
 * Generates unique queue numbers with format: QA-YYYYMMDD-XXX
 * 
 * @author RS Sehat Selalu
 * @version 1.0
 */

class QueueNumberGenerator {
    private $conn;
    
    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Generate unique queue number for a specific date
     * Format: QA-YYYYMMDD-XXX (e.g., QA-20251219-001)
     * 
     * @param string $date Date in Y-m-d format (default: today)
     * @return string|false Generated queue number or false on error
     */
    public function generate($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        // Validate date format
        if (!$this->isValidDate($date)) {
            error_log("Invalid date format: $date");
            return false;
        }
        
        try {
            // Start transaction for atomic operation
            $this->conn->begin_transaction();
            
            // Get or create counter for the date with row lock
            $stmt = $this->conn->prepare(
                "INSERT INTO queue_counter (date, counter) 
                 VALUES (?, 1) 
                 ON DUPLICATE KEY UPDATE counter = counter + 1"
            );
            $stmt->bind_param("s", $date);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update counter: " . $stmt->error);
            }
            $stmt->close();
            
            // Get the current counter value with lock
            $stmt = $this->conn->prepare(
                "SELECT counter FROM queue_counter WHERE date = ? FOR UPDATE"
            );
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $counter = $row['counter'];
            } else {
                throw new Exception("Failed to retrieve counter");
            }
            $stmt->close();
            
            // Generate queue number
            $queueNumber = $this->formatQueueNumber($date, $counter);
            
            // Commit transaction
            $this->conn->commit();
            
            return $queueNumber;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            error_log("Queue number generation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Format queue number from date and counter
     * 
     * @param string $date Date in Y-m-d format
     * @param int $counter Counter value
     * @return string Formatted queue number
     */
    private function formatQueueNumber($date, $counter) {
        $formattedDate = date('Ymd', strtotime($date));
        $formattedCounter = str_pad($counter, 3, '0', STR_PAD_LEFT);
        return "QA-{$formattedDate}-{$formattedCounter}";
    }
    
    /**
     * Validate date format
     * 
     * @param string $date Date string
     * @return bool True if valid
     */
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Get current counter for a date
     * 
     * @param string $date Date in Y-m-d format (default: today)
     * @return int Current counter value
     */
    public function getCurrentCounter($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $stmt = $this->conn->prepare(
            "SELECT counter FROM queue_counter WHERE date = ?"
        );
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (int)$row['counter'];
        }
        
        return 0;
    }
    
    /**
     * Reset counter for a specific date
     * Usually called at the start of a new day
     * 
     * @param string $date Date in Y-m-d format
     * @return bool Success status
     */
    public function resetCounter($date) {
        $stmt = $this->conn->prepare(
            "UPDATE queue_counter SET counter = 0 WHERE date = ?"
        );
        $stmt->bind_param("s", $date);
        return $stmt->execute();
    }
    
    /**
     * Get queue statistics for a date
     * 
     * @param string $date Date in Y-m-d format (default: today)
     * @return array Statistics
     */
    public function getStatistics($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $stats = [
            'date' => $date,
            'total_generated' => 0,
            'total_waiting' => 0,
            'total_completed' => 0,
            'total_cancelled' => 0
        ];
        
        // Get total generated
        $stmt = $this->conn->prepare(
            "SELECT counter FROM queue_counter WHERE date = ?"
        );
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['total_generated'] = (int)$row['counter'];
        }
        $stmt->close();
        
        // Get status breakdown
        $stmt = $this->conn->prepare(
            "SELECT status, COUNT(*) as count 
             FROM booking 
             WHERE DATE(created_at) = ? 
             GROUP BY status"
        );
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            $count = (int)$row['count'];
            
            switch ($status) {
                case 'waiting':
                    $stats['total_waiting'] = $count;
                    break;
                case 'completed':
                    $stats['total_completed'] = $count;
                    break;
                case 'cancelled':
                    $stats['total_cancelled'] = $count;
                    break;
            }
        }
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Validate if queue number exists and is valid
     * 
     * @param string $queueNumber Queue number to validate
     * @return bool True if valid and exists
     */
    public function isValidQueueNumber($queueNumber) {
        // Check format: QA-YYYYMMDD-XXX
        if (!preg_match('/^QA-\d{8}-\d{3}$/', $queueNumber)) {
            return false;
        }
        
        // Check if exists in database
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM booking WHERE queue_number = ?"
        );
        $stmt->bind_param("s", $queueNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
    
    /**
     * Parse queue number to extract date and counter
     * 
     * @param string $queueNumber Queue number
     * @return array|false Array with 'date' and 'counter' or false on error
     */
    public function parseQueueNumber($queueNumber) {
        if (!preg_match('/^QA-(\d{4})(\d{2})(\d{2})-(\d{3})$/', $queueNumber, $matches)) {
            return false;
        }
        
        return [
            'date' => "{$matches[1]}-{$matches[2]}-{$matches[3]}",
            'counter' => (int)$matches[4],
            'year' => $matches[1],
            'month' => $matches[2],
            'day' => $matches[3]
        ];
    }
    
    /**
     * Get next estimated queue number (without generating)
     * Useful for preview purposes
     * 
     * @param string $date Date in Y-m-d format (default: today)
     * @return string Next queue number
     */
    public function getNextQueueNumber($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $currentCounter = $this->getCurrentCounter($date);
        $nextCounter = $currentCounter + 1;
        
        return $this->formatQueueNumber($date, $nextCounter);
    }
    
    /**
     * Initialize queue counter table if not exists
     * Called during setup/installation
     * 
     * @return bool Success status
     */
    public function initializeTable() {
        $sql = "CREATE TABLE IF NOT EXISTS queue_counter (
            date DATE PRIMARY KEY,
            counter INT DEFAULT 0,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        return $this->conn->query($sql);
    }
}
?>
