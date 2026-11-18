<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Get success or error messages
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Get all bookings with patient and doctor info
$booking = mysqli_query($conn, "SELECT b.*, d.nama as nama_dokter FROM booking b LEFT JOIN dokter d ON b.dokter = d.username ORDER BY b.priority DESC, b.tanggal ASC, b.queue_number ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Semua Antrian | RS Sehat Selalu</title>
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
            margin-bottom: 30px;
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

        .admin-info-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .admin-info-banner h4 {
            margin: 0 0 5px 0;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .admin-info-banner p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.1rem;
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
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .ticket-patient-name {
            font-size: 1.1rem;
            color: #667eea;
            font-weight: 700;
        }

        .ticket-doctor-name {
            font-size: 1rem;
            color: #764ba2;
            font-weight: 600;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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

        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-bar select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            font-weight: 600;
        }

        .filter-bar select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #e0e0e0;
        }

        .btn-action {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-call {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-call:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
        }

        .btn-reject {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, #c82333 0%, #d62c1a 100%);
        }

        .btn-complete {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .btn-complete:hover {
            background: linear-gradient(135deg, #0069d9 0%, #004085 100%);
        }

        /* Alert Styles */
        .alert-custom {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            font-weight: 600;
            animation: slideDown 0.5s ease;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-custom.alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .alert-custom.alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
    </style>
</head>
<body>

<div class="history-container">
    <div class="history-card">
        <div class="history-header">
            <h2><i class="bi bi-ticket-perforated"></i> Semua Antrian Pasien</h2>
            <p>Kelola seluruh antrian pemeriksaan rumah sakit</p>
        </div>

        <div class="admin-info-banner">
            <h4><i class="bi bi-shield-check"></i> Administrator</h4>
            <p><i class="bi bi-hospital"></i> RS Sehat Selalu - Panel Manajemen Antrian</p>
        </div>

        <?php if ($successMessage): ?>
        <div class="alert alert-custom alert-success" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($successMessage); ?>
        </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
        <div class="alert alert-custom alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>

            <div class="filter-bar">
                <div class="row g-3">
                    <div class="col-md-3">
                    <select class="form-select" id="filterDate" onchange="filterTickets()">
                        <option value="">Semua Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="tomorrow">Besok</option>
                        <option value="week">Minggu Ini</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterdoctor" onchange="filterTickets()">
                    <option value="">Semua Dokter</option>
                 <?php
                          $dokterQuery = mysqli_query($conn, "SELECT username, nama FROM dokter");
                          while ($d = mysqli_fetch_assoc($dokterQuery)) {
                          echo '<option value="'.$d['username'].'">'.$d['nama'].'</option>';
                         }
                      ?>
                 </select>

                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterPriority" onchange="filterTickets()">
                        <option value="">Semua Prioritas</option>
                        <option value="5">Kritis</option>
                        <option value="4">Darurat</option>
                        <option value="3">Mendesak</option>
                        <option value="2">Normal</option>
                        <option value="1">Reguler</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus" onchange="filterTickets()">
                        <option value="">Semua Status</option>
                        <option value="waiting">Menunggu</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (mysqli_num_rows($booking) > 0): ?>
            <div class="ticket-grid" id="ticketGrid">
                <?php
                while ($row = mysqli_fetch_assoc($booking)) {
                    $queueNumber = htmlspecialchars($row['queue_number'] ?? 'N/A');
                    $patientName = htmlspecialchars($row['pasien']); // Username pasien
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
                <div class="ticket-card"
                     data-date="<?php echo $tanggal; ?>"
                     data-priority="<?php echo $priority; ?>"
                     data-status="<?php echo $status; ?>"
                     data-doctor="<?php echo htmlspecialchars($row['dokter']); ?>">

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
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="ticket-info-content">
                                <div class="ticket-info-label">Pasien</div>
                                <div class="ticket-patient-name"><?php echo $patientName; ?></div>
                            </div>
                        </div>
                        
                        <div class="ticket-info-row">
                            <div class="ticket-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="ticket-info-content">
                                <div class="ticket-info-label">Dokter</div>
                                <div class="ticket-doctor-name"><?php echo htmlspecialchars($row['nama_dokter'] ?? $row['dokter']); ?></div>
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
                        
                        <div class="text-center mt-3">
                            <button class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600;" 
                                onclick='showTicketModal(<?php echo json_encode([
                                    "queue_number" => $queueNumber,
                                    "doctor" => $row["dokter"],
                                    "doctor_name" => htmlspecialchars($row['nama_dokter'] ?? $row['dokter']),
                                    "date" => $row["tanggal"],
                                    "day" => $hari,
                                    "time" => $jam,
                                    "complaint" => $keluhan,
                                    "priority" => $priority,
                                    "patient_name" => $patientName
                                ]); ?>)'>
                                <i class="bi bi-eye"></i> Lihat Karcis
                            </button>
                        </div>
                        
                        <!-- Action Buttons for Admin -->
                        <div class="action-buttons">
                            <?php if ($status == 'waiting'): ?>
                                <button class="btn-action btn-call" onclick="updateStatus(<?php echo $row['id']; ?>, 'confirmed', '<?php echo $queueNumber; ?>')">
                                    <i class="bi bi-telephone-fill"></i> Panggil
                                </button>
                                <button class="btn-action btn-reject" onclick="updateStatus(<?php echo $row['id']; ?>, 'cancelled', '<?php echo $queueNumber; ?>')">
                                    <i class="bi bi-x-circle-fill"></i> Tolak
                                </button>
                            <?php elseif ($status == 'confirmed'): ?>
                                <button class="btn-action btn-complete" onclick="updateStatus(<?php echo $row['id']; ?>, 'completed', '<?php echo $queueNumber; ?>')">
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                </button>
                                <button class="btn-action btn-reject" onclick="updateStatus(<?php echo $row['id']; ?>, 'cancelled', '<?php echo $queueNumber; ?>')">
                                    <i class="bi bi-x-circle-fill"></i> Batal
                                </button>
                            <?php elseif ($status == 'cancelled'): ?>
                                <button class="btn-action" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white;" onclick="updateStatus(<?php echo $row['id']; ?>, 'waiting', '<?php echo $queueNumber; ?>')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Kembalikan
                                </button>
                            <?php elseif ($status == 'completed'): ?>
                                <div style="text-align: center; color: #28a745; font-weight: 600; padding: 10px;">
                                    <i class="bi bi-check-circle-fill"></i> Antrian Selesai
                                </div>
                            <?php endif; ?>
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
                <h4>Belum Ada Antrian</h4>
                <p>Belum ada pasien yang booking dengan Anda.</p>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="admin_dashboard.php" class="back-button">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Ticket Modal -->
<?php include 'ticket_modal.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function filterTickets() {
    const dateFilter = document.getElementById('filterDate').value;
    const priorityFilter = document.getElementById('filterPriority').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const dokterFilter = document.getElementById('filterdoctor').value;
    const tickets = document.querySelectorAll('.ticket-card');
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    tickets.forEach(ticket => {
        const ticketDate = new Date(ticket.dataset.date);
        ticketDate.setHours(0, 0, 0, 0);
        const ticketPriority = ticket.dataset.priority;
        const ticketStatus = ticket.dataset.status;
        const ticketDoctor = ticket.dataset.doctor;
        
        let showDate = true;
        let showPriority = true;
        let showStatus = true;
        let showdoctor = true;
        
        // Date filter
        if (dateFilter) {
            if (dateFilter === 'today') {
                showDate = ticketDate.getTime() === today.getTime();
            } else if (dateFilter === 'tomorrow') {
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                showDate = ticketDate.getTime() === tomorrow.getTime();
            } else if (dateFilter === 'week') {
                const weekEnd = new Date(today);
                weekEnd.setDate(weekEnd.getDate() + 7);
                showDate = ticketDate >= today && ticketDate <= weekEnd;
            }
        }
        
        // Priority filter
        if (priorityFilter) {
            showPriority = ticketPriority === priorityFilter;
        }
        
        // Status filter
        if (statusFilter) {
            showStatus = ticketStatus === statusFilter;
        }
        if (dokterFilter) {
            showdoctor = ticketDoctor === dokterFilter;
        }
        
        // Show/hide ticket
        if (showDate && showPriority && showStatus && showdoctor) {
            ticket.style.display = 'block';
        } else {
            ticket.style.display = 'none';
        }
    });
}

function updateStatus(bookingId, newStatus, queueNumber) {
    const statusNames = {
        'confirmed': 'Panggil',
        'cancelled': 'Tolak',
        'completed': 'Selesai',
        'waiting': 'Kembalikan ke Menunggu'
    };
    
    const confirmMessages = {
        'confirmed': `Apakah Anda yakin ingin memanggil pasien dengan nomor antrian ${queueNumber}?`,
        'cancelled': `Apakah Anda yakin ingin menolak antrian ${queueNumber}?`,
        'completed': `Apakah Anda yakin pemeriksaan ${queueNumber} sudah selesai?`,
        'waiting': `Kembalikan status ${queueNumber} ke Menunggu?`
    };
    
    if (confirm(confirmMessages[newStatus])) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin_update_status.php';
        
        const bookingIdInput = document.createElement('input');
        bookingIdInput.type = 'hidden';
        bookingIdInput.name = 'booking_id';
        bookingIdInput.value = bookingId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'new_status';
        statusInput.value = newStatus;
        
        form.appendChild(bookingIdInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-hide alerts after 5 seconds
window.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-custom');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideDown 0.5s ease reverse';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
</script>

</body>
</html>
