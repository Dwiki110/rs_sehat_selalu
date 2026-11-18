<?php
/**
 * Doctor Notifications API
 * API untuk notifikasi pasien prioritas tinggi untuk dokter
 * 
 * @author RS Sehat Selalu
 * @version 1.0
 */

session_start();
header('Content-Type: application/json');

require_once 'koneksi.php';

// Check if user is logged in as doctor
if (!isset($_SESSION['dokter_username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Doctor login required']);
    exit;
}

$doctorUsername = $_SESSION['dokter_username'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

try {
    switch ($action) {
        case 'get':
            // Get notifications for doctor
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            
            $sql = "SELECT dn.*, b.keluhan, b.tanggal, b.jam, b.status AS booking_status
                    FROM doctor_notifications dn
                    LEFT JOIN booking b ON dn.booking_id = b.id
                    WHERE dn.doctor_username = ?";
            
            if ($unreadOnly) {
                $sql .= " AND dn.is_read = 0";
            }
            
            $sql .= " ORDER BY dn.priority_level DESC, dn.created_at DESC LIMIT ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $doctorUsername, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications)
            ]);
            break;
            
        case 'count':
            // Get unread count
            $stmt = $conn->prepare(
                "SELECT COUNT(*) as count FROM doctor_notifications 
                 WHERE doctor_username = ? AND is_read = 0"
            );
            $stmt->bind_param("s", $doctorUsername);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'unread_count' => (int)$row['count']
            ]);
            break;
            
        case 'mark_read':
            // Mark notification as read
            if (!isset($_POST['notification_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing notification_id']);
                exit;
            }
            
            $notificationId = (int)$_POST['notification_id'];
            $stmt = $conn->prepare(
                "UPDATE doctor_notifications SET is_read = 1 
                 WHERE id = ? AND doctor_username = ?"
            );
            $stmt->bind_param("is", $notificationId, $doctorUsername);
            $success = $stmt->execute();
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notification marked as read' : 'Failed to mark as read'
            ]);
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read
            $stmt = $conn->prepare(
                "UPDATE doctor_notifications SET is_read = 1 
                 WHERE doctor_username = ? AND is_read = 0"
            );
            $stmt->bind_param("s", $doctorUsername);
            $success = $stmt->execute();
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'All notifications marked as read' : 'Failed to mark all as read'
            ]);
            break;
            
        case 'get_emergency_patients':
            // Get emergency patients for this doctor
            $stmt = $conn->prepare(
                "SELECT * FROM v_doctor_emergency_patients 
                 WHERE doctor_name = ?
                 ORDER BY priority DESC, arrival_time ASC"
            );
            $stmt->bind_param("s", $doctorUsername);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $patients = [];
            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $patients,
                'count' => count($patients)
            ]);
            break;
            
        case 'stats':
            // Get notification statistics
            $stmt = $conn->prepare(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN type = 'emergency_patient' AND is_read = 0 THEN 1 ELSE 0 END) as emergency,
                    SUM(CASE WHEN type = 'priority_patient' AND is_read = 0 THEN 1 ELSE 0 END) as priority
                 FROM doctor_notifications 
                 WHERE doctor_username = ?"
            );
            $stmt->bind_param("s", $doctorUsername);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => (int)$stats['total'],
                    'unread' => (int)$stats['unread'],
                    'emergency' => (int)$stats['emergency'],
                    'priority' => (int)$stats['priority']
                ]
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
