<?php
/**
 * Notifications API
 * RESTful API for patient notifications
 * 
 * @author RS Sehat Selalu
 * @version 1.0
 */

session_start();
header('Content-Type: application/json');

require_once 'koneksi.php';
require_once 'NotificationManager.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only allow patients to access their notifications
if ($_SESSION['role'] !== 'pasien') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$username = $_SESSION['username'];
$notificationManager = new NotificationManager($conn);

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

try {
    switch ($action) {
        case 'get':
            // Get notifications
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            
            $notifications = $notificationManager->getNotifications($username, $unreadOnly, $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications)
            ]);
            break;
            
        case 'count':
            // Get unread count
            $count = $notificationManager->getUnreadCount($username);
            
            echo json_encode([
                'success' => true,
                'unread_count' => $count
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
            $success = $notificationManager->markAsRead($notificationId);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notification marked as read' : 'Failed to mark as read'
            ]);
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read
            $success = $notificationManager->markAllAsRead($username);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'All notifications marked as read' : 'Failed to mark all as read'
            ]);
            break;
            
        case 'delete':
            // Delete notification
            if (!isset($_POST['notification_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing notification_id']);
                exit;
            }
            
            $notificationId = (int)$_POST['notification_id'];
            $success = $notificationManager->deleteNotification($notificationId);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notification deleted' : 'Failed to delete notification'
            ]);
            break;
            
        case 'stats':
            // Get notification statistics
            $stats = $notificationManager->getStatistics($username);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
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
