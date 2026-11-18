<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}

include 'koneksi.php';

// Check if POST data exists
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bookingId = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $newStatus = isset($_POST['new_status']) ? $_POST['new_status'] : '';
    
    // Validate inputs
    if ($bookingId <= 0) {
        $_SESSION['error_message'] = "ID booking tidak valid.";
        header("Location: admin_lihat_antrian.php");
        exit();
    }
    
    // Validate status
    $allowedStatuses = ['confirmed', 'cancelled', 'completed', 'waiting'];
    if (!in_array($newStatus, $allowedStatuses)) {
        $_SESSION['error_message'] = "Status tidak valid.";
        header("Location: admin_lihat_antrian.php");
        exit();
    }
    
    // Update status in database
    $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $bookingId);
    
    if ($stmt->execute()) {
        // Success message based on status
        $statusMessages = [
            'confirmed' => 'Pasien berhasil dipanggil!',
            'cancelled' => 'Antrian pasien berhasil ditolak.',
            'completed' => 'Antrian berhasil diselesaikan.',
            'waiting' => 'Status dikembalikan ke Menunggu.'
        ];
        
        $_SESSION['success_message'] = $statusMessages[$newStatus] ?? 'Status berhasil diperbarui.';
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui status: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Request tidak valid.";
}

header("Location: admin_lihat_antrian.php");
exit();
?>
