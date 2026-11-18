<?php
/**
 * Notification Manager
 * Manages patient notifications based on priority and queue status
 * 
 * @author RS Sehat Selalu
 * @version 1.0
 */

class NotificationManager {
    private $conn;
    
    // Notification types
    const TYPE_PRIORITY_ASSIGNED = 'priority_assigned';
    const TYPE_QUEUE_UPDATE = 'queue_update';
    const TYPE_CALL_SOON = 'call_soon';
    const TYPE_EMERGENCY = 'emergency';
    
    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Create notification for a patient
     * 
     * @param int $bookingId Booking ID
     * @param string $patientUsername Patient username
     * @param int $priorityLevel Priority level (1-5)
     * @param string $message Notification message
     * @param string $type Notification type
     * @return int|false Notification ID or false on error
     */
    public function createNotification($bookingId, $patientUsername, $priorityLevel, $message, $type = self::TYPE_PRIORITY_ASSIGNED) {
        $stmt = $this->conn->prepare(
            "INSERT INTO notifications (booking_id, patient_username, priority_level, message, type) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param("isiss", $bookingId, $patientUsername, $priorityLevel, $message, $type);
        
        if ($stmt->execute()) {
            $notificationId = $stmt->insert_id;
            $stmt->close();
            return $notificationId;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Create notification based on priority level
     * Automatically determines message and type
     * 
     * @param int $bookingId Booking ID
     * @param string $patientUsername Patient username
     * @param int $priorityLevel Priority level (1-5)
     * @param string $queueNumber Queue number
     * @param string $keluhan Patient complaint
     * @return int|false Notification ID or false on error
     */
    public function createPriorityNotification($bookingId, $patientUsername, $priorityLevel, $queueNumber, $keluhan = '') {
        $message = $this->generatePriorityMessage($priorityLevel, $queueNumber, $keluhan);
        $type = $this->determineNotificationType($priorityLevel);
        
        return $this->createNotification($bookingId, $patientUsername, $priorityLevel, $message, $type);
    }
    
    /**
     * Generate priority-based notification message
     * 
     * @param int $priority Priority level (1-5)
     * @param string $queueNumber Queue number
     * @param string $keluhan Patient complaint
     * @return string Notification message
     */
    private function generatePriorityMessage($priority, $queueNumber, $keluhan = '') {
        $messages = [
            5 => "🚨 KRITIS - Nomor antrian Anda: {$queueNumber}. Anda akan SEGERA ditangani! Kondisi Anda memerlukan penanganan darurat. Harap segera menuju ruang emergency!",
            4 => "⚠️ DARURAT - Nomor antrian Anda: {$queueNumber}. Prioritas tinggi! Anda akan dipanggil dalam 30 menit. Harap bersiap dan tetap di area tunggu!",
            3 => "⚡ MENDESAK - Nomor antrian Anda: {$queueNumber}. Estimasi pemanggilan dalam 2 jam. Harap tetap standby di area tunggu.",
            2 => "ℹ️ SEMI MENDESAK - Nomor antrian Anda: {$queueNumber}. Estimasi pemanggilan dalam 4 jam. Silakan tunggu dengan tenang, Anda dapat beristirahat di ruang tunggu.",
            1 => "✅ Nomor antrian Anda: {$queueNumber}. Pendaftaran berhasil! Silakan tunggu, Anda akan dipanggil sesuai urutan prioritas."
        ];
        
        return $messages[$priority] ?? $messages[1];
    }
    
    /**
     * Determine notification type based on priority
     * 
     * @param int $priority Priority level
     * @return string Notification type
     */
    private function determineNotificationType($priority) {
        if ($priority >= 4) {
            return self::TYPE_EMERGENCY;
        } elseif ($priority == 3) {
            return self::TYPE_QUEUE_UPDATE;
        } else {
            return self::TYPE_PRIORITY_ASSIGNED;
        }
    }
    
    /**
     * Get all notifications for a patient
     * 
     * @param string $patientUsername Patient username
     * @param bool $unreadOnly Only get unread notifications
     * @param int $limit Limit number of results
     * @return array Notifications
     */
    public function getNotifications($patientUsername, $unreadOnly = false, $limit = 50) {
        $sql = "SELECT n.*, b.queue_number, b.tanggal, b.dokter 
                FROM notifications n
                LEFT JOIN booking b ON n.booking_id = b.id
                WHERE n.patient_username = ?";
        
        if ($unreadOnly) {
            $sql .= " AND n.is_read = 0";
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $patientUsername, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        $stmt->close();
        return $notifications;
    }
    
    /**
     * Get unread notification count for a patient
     * 
     * @param string $patientUsername Patient username
     * @return int Unread count
     */
    public function getUnreadCount($patientUsername) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM notifications WHERE patient_username = ? AND is_read = 0"
        );
        $stmt->bind_param("s", $patientUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public function markAsRead($notificationId) {
        $stmt = $this->conn->prepare(
            "UPDATE notifications SET is_read = 1 WHERE id = ?"
        );
        $stmt->bind_param("i", $notificationId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Mark all notifications as read for a patient
     * 
     * @param string $patientUsername Patient username
     * @return bool Success status
     */
    public function markAllAsRead($patientUsername) {
        $stmt = $this->conn->prepare(
            "UPDATE notifications SET is_read = 1 WHERE patient_username = ? AND is_read = 0"
        );
        $stmt->bind_param("s", $patientUsername);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Delete notification
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public function deleteNotification($notificationId) {
        $stmt = $this->conn->prepare(
            "DELETE FROM notifications WHERE id = ?"
        );
        $stmt->bind_param("i", $notificationId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Create "call soon" notification
     * Used when patient is about to be called
     * 
     * @param int $bookingId Booking ID
     * @param string $patientUsername Patient username
     * @param string $queueNumber Queue number
     * @param int $minutesEstimate Estimated minutes until call
     * @return int|false Notification ID or false on error
     */
    public function createCallSoonNotification($bookingId, $patientUsername, $queueNumber, $minutesEstimate = 10) {
        $message = "🔔 Nomor antrian {$queueNumber} akan segera dipanggil! Estimasi {$minutesEstimate} menit lagi. Harap bersiap dan menuju area pemeriksaan.";
        
        return $this->createNotification($bookingId, $patientUsername, 0, $message, self::TYPE_CALL_SOON);
    }
    
    /**
     * Create queue position update notification
     * 
     * @param int $bookingId Booking ID
     * @param string $patientUsername Patient username
     * @param string $queueNumber Queue number
     * @param int $position Current position in queue
     * @param int $remaining Remaining patients before this one
     * @return int|false Notification ID or false on error
     */
    public function createQueueUpdateNotification($bookingId, $patientUsername, $queueNumber, $position, $remaining) {
        $message = "📊 Update antrian: Nomor {$queueNumber} berada di posisi {$position}. Masih ada {$remaining} pasien sebelum Anda. Harap tetap menunggu.";
        
        return $this->createNotification($bookingId, $patientUsername, 0, $message, self::TYPE_QUEUE_UPDATE);
    }
    
    /**
     * Get notification by ID
     * 
     * @param int $notificationId Notification ID
     * @return array|false Notification data or false if not found
     */
    public function getNotificationById($notificationId) {
        $stmt = $this->conn->prepare(
            "SELECT n.*, b.queue_number, b.tanggal, b.dokter, b.keluhan 
             FROM notifications n
             LEFT JOIN booking b ON n.booking_id = b.id
             WHERE n.id = ?"
        );
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Get latest notification for a booking
     * 
     * @param int $bookingId Booking ID
     * @return array|false Latest notification or false if none
     */
    public function getLatestNotificationForBooking($bookingId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM notifications WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Get notification statistics for a patient
     * 
     * @param string $patientUsername Patient username
     * @return array Statistics
     */
    public function getStatistics($patientUsername) {
        $stats = [
            'total' => 0,
            'unread' => 0,
            'by_type' => [
                self::TYPE_PRIORITY_ASSIGNED => 0,
                self::TYPE_QUEUE_UPDATE => 0,
                self::TYPE_CALL_SOON => 0,
                self::TYPE_EMERGENCY => 0
            ]
        ];
        
        // Get total and unread
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as total, SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
             FROM notifications WHERE patient_username = ?"
        );
        $stmt->bind_param("s", $patientUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['total'] = (int)$row['total'];
            $stats['unread'] = (int)$row['unread'];
        }
        $stmt->close();
        
        // Get by type
        $stmt = $this->conn->prepare(
            "SELECT type, COUNT(*) as count FROM notifications 
             WHERE patient_username = ? GROUP BY type"
        );
        $stmt->bind_param("s", $patientUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $stats['by_type'][$row['type']] = (int)$row['count'];
        }
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Clean old notifications (older than X days)
     * 
     * @param int $days Number of days to keep
     * @return int Number of deleted notifications
     */
    public function cleanOldNotifications($days = 30) {
        $stmt = $this->conn->prepare(
            "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->bind_param("i", $days);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        return $affected;
    }
    
    /**
     * Get notification icon based on type
     * 
     * @param string $type Notification type
     * @return string HTML icon class
     */
    public static function getNotificationIcon($type) {
        $icons = [
            self::TYPE_EMERGENCY => 'bi-exclamation-triangle-fill text-danger',
            self::TYPE_CALL_SOON => 'bi-bell-fill text-warning',
            self::TYPE_QUEUE_UPDATE => 'bi-info-circle-fill text-info',
            self::TYPE_PRIORITY_ASSIGNED => 'bi-check-circle-fill text-success'
        ];
        
        return $icons[$type] ?? 'bi-bell-fill text-secondary';
    }
    
    /**
     * Get notification badge color based on type
     * 
     * @param string $type Notification type
     * @return string Bootstrap badge class
     */
    public static function getNotificationBadgeColor($type) {
        $colors = [
            self::TYPE_EMERGENCY => 'danger',
            self::TYPE_CALL_SOON => 'warning',
            self::TYPE_QUEUE_UPDATE => 'info',
            self::TYPE_PRIORITY_ASSIGNED => 'success'
        ];
        
        return $colors[$type] ?? 'secondary';
    }
}
?>
