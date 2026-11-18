<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

$pasien = $_SESSION['username'];
$booking = mysqli_query($conn, "SELECT b.*, d.nama as nama_dokter FROM booking b LEFT JOIN dokter d ON b.dokter = d.username WHERE b.pasien = '$pasien' ORDER BY b.priority DESC, b.tanggal ASC, b.queue_number ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking Janji Temu | RS Sehat Selalu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .history-container {
            min-height: 100vh;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .history-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
        }

        .history-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .history-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .history-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
            text-align: center;
        }

        .table tbody td {
            padding: 15px;
            border: none;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) {
            background-color: rgba(79, 172, 254, 0.05);
        }

        .table tbody tr:hover {
            background-color: rgba(79, 172, 254, 0.1);
            transition: all 0.3s ease;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-completed {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .status-pending {
            background: linear-gradient(135deg, #ffa726 0%, #ff7043 100%);
            color: white;
        }

        /* Ticket Card Styles */
        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .ticket-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            position: relative;
            border: 3px solid transparent;
        }

        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(79, 172, 254, 0.3);
            border-color: #4facfe;
        }

        .ticket-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            text-align: center;
            position: relative;
        }

        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .ticket-number {
            font-size: 3rem;
            font-weight: 900;
            color: white;
            letter-spacing: 2px;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: 0;
            font-family: 'Courier New', monospace;
        }

        .ticket-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .ticket-body {
            padding: 30px 25px 25px;
        }

        .ticket-info-row {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed #e0e0e0;
        }

        .ticket-info-row:last-child {
            border-bottom: none;
        }

        .ticket-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .ticket-icon i {
            color: white;
            font-size: 1.2rem;
        }

        .ticket-info-content {
            flex: 1;
        }

        .ticket-info-label {
            font-size: 0.75rem;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .ticket-info-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .ticket-doctor-name {
            font-size: 1.1rem;
            color: #667eea;
            font-weight: 700;
        }

        .ticket-date-time {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .ticket-date,
        .ticket-time {
            flex: 1;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }

        .ticket-date i,
        .ticket-time i {
            font-size: 1.3rem;
            color: #667eea;
            margin-bottom: 5px;
        }

        .ticket-date-value,
        .ticket-time-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .ticket-footer {
            background: #f8f9fa;
            padding: 15px 25px;
            border-top: 2px dashed #e0e0e0;
        }

        .ticket-complaint {
            font-size: 0.85rem;
            color: #7f8c8d;
            line-height: 1.5;
            margin: 0;
        }

        .ticket-priority {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .priority-5 {
            background: linear-gradient(135deg, #ff0844 0%, #ff6b6b 100%);
            color: white;
            animation: pulse 2s infinite;
        }

        .priority-4 {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
            color: white;
        }

        .priority-3 {
            background: linear-gradient(135deg, #ffa726 0%, #ffd32a 100%);
            color: white;
        }

        .priority-2 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .priority-1 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .status-waiting {
            background: linear-gradient(135deg, #ffa726 0%, #ff7043 100%);
            color: white;
        }

        .status-confirmed {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .status-completed {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .ticket-status {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
        }

        /* Status Notification Badge */
        .status-notification {
            margin: 15px 0;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .notification-confirmed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 5px solid #28a745;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .notification-cancelled {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 5px solid #dc3545;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .notification-completed {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 5px solid #17a2b8;
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
        }

        .notification-waiting {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-left: 5px solid #ffc107;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        .notification-icon {
            font-size: 1.5rem;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        @media (max-width: 768px) {
            .ticket-grid {
                grid-template-columns: 1fr;
            }

            .ticket-number {
                font-size: 2.5rem;
            }

            .ticket-body {
                padding: 25px 20px 20px;
            }
        }

        .back-button {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
            color: white;
            text-decoration: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .empty-state h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .history-container {
                padding: 15px;
            }

            .history-card {
                padding: 30px 20px;
            }

            .history-header h2 {
                font-size: 2rem;
            }

            .table-responsive {
                border-radius: 15px;
            }

            .table thead th,
            .table tbody td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="history-container">
    <div class="history-card">
        <div class="history-header">
            <h2><i class="bi bi-clock-history"></i> Riwayat Booking</h2>
            <p>Riwayat janji temu Anda di RS Sehat Selalu</p>
        </div>

        <?php if (mysqli_num_rows($booking) > 0): ?>
            <div class="ticket-grid">
                <?php
                while ($row = mysqli_fetch_assoc($booking)) {
                    $queueNumber = htmlspecialchars($row['queue_number'] ?? 'N/A');
                    $doctorName = htmlspecialchars($row['nama_dokter'] ?? $row['dokter']);
                    $tanggal = htmlspecialchars($row['tanggal']);
                    $hari = htmlspecialchars($row['hari']);
                    $jam = htmlspecialchars($row['jam']);
                    $keluhan = htmlspecialchars($row['keluhan']);
                    $priority = (int)($row['priority'] ?? 1);
                    $status = htmlspecialchars($row['status'] ?? 'waiting');
                    
                    // Format tanggal
                    $dateObj = DateTime::createFromFormat('Y-m-d', $row['tanggal']);
                    $formattedDate = $dateObj ? $dateObj->format('d M Y') : $tanggal;
                    
                    // Priority label
                    $priorityLabels = [
                        5 => 'KRITIS',
                        4 => 'DARURAT',
                        3 => 'MENDESAK',
                        2 => 'NORMAL',
                        1 => 'REGULER'
                    ];
                    
                    // Status label
                    $statusLabels = [
                        'waiting' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan'
                    ];
                    
                    $priorityLabel = $priorityLabels[$priority] ?? 'REGULER';
                    $statusLabel = $statusLabels[$status] ?? 'Menunggu';
                ?>
                <div class="ticket-card">
                    <?php if ($priority >= 3): ?>
                    <div class="ticket-priority priority-<?php echo $priority; ?>">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $priorityLabel; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="ticket-header">
                        <div class="ticket-label">Nomor Antrian</div>
                        <div class="ticket-number"><?php echo $queueNumber; ?></div>
                    </div>
                    
                    <div class="ticket-body">
                        <div class="ticket-info-row">
                            <div class="ticket-icon">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <div class="ticket-info-content">
                                <div class="ticket-info-label">Dokter</div>
                                <div class="ticket-doctor-name"><?php echo $doctorName; ?></div>
                            </div>
                        </div>
                        
                        <div class="ticket-date-time">
                            <div class="ticket-date">
                                <div><i class="bi bi-calendar-event"></i></div>
                                <div class="ticket-info-label">Tanggal</div>
                                <div class="ticket-date-value"><?php echo $formattedDate; ?></div>
                                <div style="font-size: 0.75rem; color: #7f8c8d; margin-top: 2px;"><?php echo $hari; ?></div>
                            </div>
                            <div class="ticket-time">
                                <div><i class="bi bi-clock-fill"></i></div>
                                <div class="ticket-info-label">Jam</div>
                                <div class="ticket-time-value"><?php echo $jam; ?></div>
                                <div style="font-size: 0.75rem; color: #7f8c8d; margin-top: 2px;">WIB</div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <span class="ticket-status status-<?php echo $status; ?>">
                                <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> <?php echo $statusLabel; ?>
                            </span>
                        </div>
                        
                        <!-- Status Notification -->
                        <?php if ($status == 'confirmed'): ?>
                        <div class="status-notification notification-confirmed">
                            <i class="bi bi-telephone-fill notification-icon"></i>
                            <div>
                                <strong>Anda Dipanggil!</strong><br>
                                <small>Segera datang ke ruang pemeriksaan dokter.</small>
                            </div>
                        </div>
                        <?php elseif ($status == 'cancelled'): ?>
                        <div class="status-notification notification-cancelled">
                            <i class="bi bi-x-circle-fill notification-icon"></i>
                            <div>
                                <strong>Antrian Ditolak</strong><br>
                                <small>Mohon maaf, antrian Anda ditolak oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.</small>
                            </div>
                        </div>
                        <?php elseif ($status == 'completed'): ?>
                        <div class="status-notification notification-completed">
                            <i class="bi bi-check-circle-fill notification-icon"></i>
                            <div>
                                <strong>Pemeriksaan Selesai</strong><br>
                                <small>Terima kasih telah menggunakan layanan kami.</small>
                            </div>
                        </div>
                        <?php elseif ($status == 'waiting'): ?>
                        <div class="status-notification notification-waiting">
                            <i class="bi bi-hourglass-split notification-icon"></i>
                            <div>
                                <strong>Menunggu Konfirmasi</strong><br>
                                <small>Mohon tunggu, antrian Anda sedang dalam proses verifikasi.</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <button class="btn btn-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600;" 
                                onclick='showTicketModal(<?php echo json_encode([
                                    "queue_number" => $queueNumber,
                                    "doctor" => $row["dokter"],
                                    "doctor_name" => $doctorName,
                                    "date" => $row["tanggal"],
                                    "day" => $hari,
                                    "time" => $jam,
                                    "complaint" => $keluhan,
                                    "priority" => $priority
                                ]); ?>, {"useStringify": false})'>
                                <i class="bi bi-eye"></i> Lihat Karcis
                            </button>
                        </div>
                    </div>
                    
                    <div class="ticket-footer">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px; font-size: 0.8rem;">
                            <i class="bi bi-chat-left-text"></i> KELUHAN:
                        </div>
                        <p class="ticket-complaint"><?php echo $keluhan; ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>Belum Ada Riwayat Booking</h4>
                <p>Anda belum memiliki riwayat janji temu. Silakan buat booking terlebih dahulu.</p>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="pasien_dashboard.php" class="back-button">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Ticket Modal -->
<?php include 'ticket_modal.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- 3D Particles -->
<script src="particles.js"></script>

</body>
</html>
