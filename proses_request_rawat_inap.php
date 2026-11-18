<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['dokter_username'])) {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dokter_request_rawat_inap.php");
    exit();
}

$dokter = $_SESSION['dokter_username'];
$bookingId = (int)$_POST['booking_id'];
$diagnosis = trim($_POST['diagnosis']);
$reason = trim($_POST['reason']);
$priority = (int)$_POST['priority'];
$roomType = trim($_POST['room_type']);
$estimatedDays = (int)$_POST['estimated_days'];

// Validate input
if (empty($bookingId) || empty($diagnosis) || empty($reason) || $priority < 1 || $priority > 5) {
    header("Location: dokter_request_rawat_inap.php?error=invalid_input");
    exit();
}

// Get booking and verify doctor
$stmt = $conn->prepare(
    "SELECT * FROM booking WHERE id = ? AND dokter = ?"
);
$stmt->bind_param("is", $bookingId, $dokter);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: dokter_request_rawat_inap.php?error=booking_not_found");
    exit();
}

// Check if request already exists for this booking
$checkStmt = $conn->prepare(
    "SELECT id FROM room_requests WHERE booking_id = ? AND status = 'pending'"
);
$checkStmt->bind_param("i", $bookingId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: dokter_request_rawat_inap.php?error=request_exists");
    exit();
}

// Insert room request
$insertStmt = $conn->prepare(
    "INSERT INTO room_requests 
     (booking_id, patient_name, doctor_name, diagnosis, reason, priority, room_type, estimated_days, status) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
);
$insertStmt->bind_param(
    "issssisi",
    $bookingId,
    $booking['pasien'],
    $dokter,
    $diagnosis,
    $reason,
    $priority,
    $roomType,
    $estimatedDays
);

if ($insertStmt->execute()) {
    $requestId = $insertStmt->insert_id;
    
    // Update booking status to in_progress
    $updateStmt = $conn->prepare("UPDATE booking SET status = 'in_progress' WHERE id = ?");
    $updateStmt->bind_param("i", $bookingId);
    $updateStmt->execute();
    
    // Redirect with success
    header("Location: dokter_dashboard.php?success=request_created&request_id=$requestId");
} else {
    error_log("Failed to insert room request: " . $insertStmt->error);
    header("Location: dokter_request_rawat_inap.php?error=insert_failed");
}

exit();
?>
