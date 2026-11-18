<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_room_requests.php");
    exit();
}

$adminUsername = $_SESSION['username'];
$requestId = (int)$_POST['request_id'];
$action = $_POST['action']; // 'approve' or 'reject'
$adminNotes = trim($_POST['admin_notes'] ?? '');
$roomNumber = trim($_POST['room_number'] ?? '');

// Validate input
if (empty($requestId) || !in_array($action, ['approve', 'reject'])) {
    header("Location: admin_room_requests.php?error=invalid_input");
    exit();
}

if ($action === 'approve' && empty($roomNumber)) {
    header("Location: admin_room_requests.php?error=room_number_required");
    exit();
}

// Get request details
$stmt = $conn->prepare("SELECT * FROM room_requests WHERE id = ?");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    header("Location: admin_room_requests.php?error=request_not_found");
    exit();
}

// Update request status
$newStatus = ($action === 'approve') ? 'approved' : 'rejected';
$updateStmt = $conn->prepare(
    "UPDATE room_requests 
     SET status = ?, admin_notes = ?, room_number = ?, approved_by = ?, approved_at = NOW()
     WHERE id = ?"
);
$updateStmt->bind_param("ssssi", $newStatus, $adminNotes, $roomNumber, $adminUsername, $requestId);

if ($updateStmt->execute()) {
    // If approved, update rawat_inap table
    if ($action === 'approve') {
        $insertInap = $conn->prepare(
            "INSERT INTO rawat_inap (pasien, dokter, alasan, status, tanggal) 
             VALUES (?, ?, ?, 'Disetujui', NOW())"
        );
        $reason = $request['diagnosis'] . ' - ' . $request['reason'];
        $insertInap->bind_param("sss", $request['patient_name'], $request['doctor_name'], $reason);
        $insertInap->execute();
    }
    
    // Mark admin notifications as read
    $markNotif = $conn->prepare(
        "UPDATE admin_notifications SET is_read = 1 WHERE room_request_id = ?"
    );
    $markNotif->bind_param("i", $requestId);
    $markNotif->execute();
    
    // Redirect with success
    $message = ($action === 'approve') ? 'approved' : 'rejected';
    header("Location: admin_room_requests.php?success=$message");
} else {
    error_log("Failed to update room request: " . $updateStmt->error);
    header("Location: admin_room_requests.php?error=update_failed");
}

exit();
?>
