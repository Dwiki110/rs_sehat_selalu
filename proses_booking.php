<?php
session_start();
include 'koneksi.php';
require_once 'QueueNumberGenerator.php';
require_once 'NotificationManager.php';

if ($_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pasien_booking.php");
    exit();
}

// Validate registration time - only until 23:00
$currentHour = (int)date('H');
if ($currentHour >= 23) {
    header("Location: pasien_booking.php?error=registration_closed");
    exit();
}

$pasien = $_SESSION['username'];
$dokter = trim($_POST['dokter'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');
$hari = trim($_POST['hari'] ?? '');
$jam = trim($_POST['jam'] ?? '');
$keluhan = trim($_POST['keluhan'] ?? '');

// Validate input
if (empty($dokter) || empty($tanggal) || empty($hari) || empty($jam)) {
    header("Location: pasien_booking.php?error=missing_fields");
    exit();
}

// Validate booking date - cannot book for past dates
if (strtotime($tanggal) < strtotime(date('Y-m-d'))) {
    header("Location: pasien_booking.php?error=invalid_date");
    exit();
}

// Check if booking already exists for this patient and doctor on same date
$check_stmt = $conn->prepare("SELECT id FROM booking WHERE pasien = ? AND dokter = ? AND tanggal = ?");
$check_stmt->bind_param("sss", $pasien, $dokter, $tanggal);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    header("Location: pasien_booking.php?error=booking_exists");
    exit();
}
$check_stmt->close();

// Determine priority based on keluhan keywords
function determinePriority($keluhan) {
    $keluhan = strtolower($keluhan);
    
    // Critical - Level 5
    if (preg_match('/(henti jantung|tidak sadar|pingsan|kejang|sesak nafas berat|pendarahan hebat|parah|kondisi parah)/i', $keluhan)) {
        return 5;
    }
    
    // Emergency - Level 4
    if (preg_match('/(nyeri dada|sesak nafas|sesak|demam tinggi|muntah darah|kecelakaan|demam parah|sakit parah|tinggi|demam tinggi)/i', $keluhan)) {
        return 4;
    }
    
    // Urgent - Level 3
    if (preg_match('/(demam|diare|nyeri|sakit kepala berat|luka)/i', $keluhan)) {
        return 3;
    }
    
    // Semi urgent - Level 2
    if (preg_match('/(batuk|pilek|flu|pusing)/i', $keluhan)) {
        return 2;
    }
    
    // Non-urgent - Level 1
    return 1;
}

$priority = determinePriority($keluhan);

// Initialize queue number generator
$queueGen = new QueueNumberGenerator($conn);
$queueNumber = $queueGen->generate($tanggal);

if (!$queueNumber) {
    error_log("Failed to generate queue number for patient: $pasien");
    header("Location: pasien_booking.php?error=queue_generation_failed");
    exit();
}

// Insert booking with queue number and priority
$stmt = $conn->prepare(
    "INSERT INTO booking (pasien, dokter, tanggal, hari, jam, keluhan, queue_number, priority, status) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'waiting')"
);
$stmt->bind_param("sssssssi", $pasien, $dokter, $tanggal, $hari, $jam, $keluhan, $queueNumber, $priority);

if ($stmt->execute()) {
    $bookingId = $stmt->insert_id;
    
    // Create notification for patient (priority 3 and above)
    if ($priority >= 3) {
        $notificationManager = new NotificationManager($conn);
        $notificationManager->createPriorityNotification(
            $bookingId, 
            $pasien, 
            $priority, 
            $queueNumber, 
            $keluhan
        );
    }
    
    // Get doctor name for ticket display
    $doctorStmt = $conn->prepare("SELECT nama FROM dokter WHERE username = ?");
    $doctorStmt->bind_param("s", $dokter);
    $doctorStmt->execute();
    $doctorResult = $doctorStmt->get_result();
    $doctorName = $doctorResult->num_rows > 0 ? $doctorResult->fetch_assoc()['nama'] : $dokter;
    $doctorStmt->close();
    
    // Redirect with success and complete ticket data
    $redirectUrl = "pasien_riwayat.php?success=booking_created" .
        "&queue_number=" . urlencode($queueNumber) .
        "&doctor=" . urlencode($dokter) .
        "&doctor_name=" . urlencode($doctorName) .
        "&date=" . urlencode($tanggal) .
        "&day=" . urlencode($hari) .
        "&time=" . urlencode($jam) .
        "&complaint=" . urlencode($keluhan) .
        "&priority=" . urlencode($priority);
    
    header("Location: " . $redirectUrl);
} else {
    error_log("Booking insert failed: " . $stmt->error);
    header("Location: pasien_booking.php?error=booking_failed");
}

$stmt->close();
exit();
?>
