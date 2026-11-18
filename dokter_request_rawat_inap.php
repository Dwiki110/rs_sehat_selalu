<?php
session_start();
if (!isset($_SESSION['dokter_username'])) {
    header("Location: index.html");
    exit();
}

require_once 'koneksi.php';

$dokter = $_SESSION['dokter_username'];

// Get patient from booking if booking_id provided
$selectedBooking = null;
if (isset($_GET['booking_id'])) {
    $bookingId = (int)$_GET['booking_id'];
    $stmt = $conn->prepare(
        "SELECT * FROM booking WHERE id = ? AND dokter = ?"
    );
    $stmt->bind_param("is", $bookingId, $dokter);
    $stmt->execute();
    $result = $stmt->get_result();
    $selectedBooking = $result->fetch_assoc();
}

// Get all pending bookings for this doctor
$stmt = $conn->prepare(
    "SELECT * FROM booking 
     WHERE dokter = ? AND status IN ('waiting', 'in_progress')
     ORDER BY priority DESC, created_at ASC"
);
$stmt->bind_param("s", $dokter);
$stmt->execute();
$bookings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Rawat Inap | RS Sehat Selalu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-cyan-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="bi bi-hospital text-blue-600"></i>
                        Request Rawat Inap
                    </h1>
                    <p class="text-gray-600">Form permintaan rawat inap untuk pasien</p>
                </div>
                <a href="dokter_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form Request Rawat Inap -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form action="proses_request_rawat_inap.php" method="POST" onsubmit="return validateForm()">
                <!-- Select Patient -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="bi bi-person"></i> Pilih Pasien *
                    </label>
                    <select name="booking_id" id="booking_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required onchange="loadPatientInfo()">
                        <option value="">-- Pilih Pasien --</option>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <option value="<?= $booking['id'] ?>" 
                                    data-patient="<?= htmlspecialchars($booking['pasien']) ?>"
                                    data-queue="<?= htmlspecialchars($booking['queue_number']) ?>"
                                    data-keluhan="<?= htmlspecialchars($booking['keluhan']) ?>"
                                    data-priority="<?= $booking['priority'] ?>"
                                    <?= ($selectedBooking && $selectedBooking['id'] == $booking['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($booking['pasien']) ?> - 
                                <?= htmlspecialchars($booking['queue_number']) ?> - 
                                Priority: <?= $booking['priority'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Patient Info Display -->
                <div id="patient-info" class="mb-6 p-4 bg-blue-50 rounded-lg hidden">
                    <h3 class="font-semibold text-gray-700 mb-2">Informasi Pasien:</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Nama:</strong> <span id="info-name"></span></div>
                        <div><strong>No. Antrian:</strong> <span id="info-queue"></span></div>
                        <div><strong>Keluhan:</strong> <span id="info-keluhan" class="col-span-2"></span></div>
                    </div>
                </div>

                <!-- Diagnosis -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="bi bi-clipboard-pulse"></i> Diagnosis *
                    </label>
                    <textarea name="diagnosis" id="diagnosis" rows="4" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Masukkan diagnosis lengkap pasien..." required></textarea>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="bi bi-journal-medical"></i> Alasan Rawat Inap *
                    </label>
                    <textarea name="reason" id="reason" rows="4" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Jelaskan alasan memerlukan rawat inap..." required></textarea>
                </div>

                <!-- Priority Level -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="bi bi-flag"></i> Tingkat Prioritas *
                    </label>
                    <select name="priority" id="priority" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <option value="1">1 - Tidak Mendesak</option>
                        <option value="2">2 - Semi Mendesak</option>
                        <option value="3">3 - Mendesak</option>
                        <option value="4">4 - Darurat</option>
                        <option value="5">5 - Kritis (ICU)</option>
                    </select>
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="bi bi-info-circle"></i> 
                        Level 4-5 akan mendapat perhatian segera dari admin
                    </p>
                </div>

                <!-- Room Type -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="bi bi-door-open"></i> Jenis Ruangan *
                    </label>
                    <select name="room_type" id="room_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <option value="Kelas 3">Kelas 3 - Rp 150,000/hari</option>
                        <option value="Kelas 2">Kelas 2 - Rp 300,000/hari</option>
                        <option value="Kelas 1">Kelas 1 - Rp 500,000/hari</option>
                        <option value="VIP">VIP - Rp 1,000,000/hari</option>
                        <option value="VVIP">VVIP - Rp 1,500,000/hari</option>
                        <option value="ICU">ICU - Rp 2,000,000/hari</option>
                    </select>
                </div>

                <!-- Estimated Days -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="bi bi-calendar3"></i> Estimasi Lama Rawat Inap (hari) *
                    </label>
                    <input type="number" name="estimated_days" id="estimated_days" min="1" max="365" value="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                        <i class="bi bi-send"></i> Kirim Request
                    </button>
                    <a href="dokter_dashboard.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-semibold text-center transition-colors">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function loadPatientInfo() {
            const select = document.getElementById('booking_id');
            const option = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('patient-info');
            
            if (option.value) {
                document.getElementById('info-name').textContent = option.dataset.patient;
                document.getElementById('info-queue').textContent = option.dataset.queue;
                document.getElementById('info-keluhan').textContent = option.dataset.keluhan;
                
                // Auto-set priority based on booking priority
                const priority = parseInt(option.dataset.priority);
                document.getElementById('priority').value = priority;
                
                // Auto-select ICU for critical patients
                if (priority === 5) {
                    document.getElementById('room_type').value = 'ICU';
                }
                
                infoDiv.classList.remove('hidden');
            } else {
                infoDiv.classList.add('hidden');
            }
        }
        
        function validateForm() {
            const bookingId = document.getElementById('booking_id').value;
            const diagnosis = document.getElementById('diagnosis').value.trim();
            const reason = document.getElementById('reason').value.trim();
            
            if (!bookingId) {
                alert('Silakan pilih pasien!');
                return false;
            }
            
            if (diagnosis.length < 10) {
                alert('Diagnosis minimal 10 karakter!');
                return false;
            }
            
            if (reason.length < 10) {
                alert('Alasan rawat inap minimal 10 karakter!');
                return false;
            }
            
            return confirm('Apakah Anda yakin akan mengirim request rawat inap ini?');
        }
        
        // Load patient info if booking_id is pre-selected
        window.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('booking_id');
            if (select.value) {
                loadPatientInfo();
            }
        });
    </script>
</body>
</html>
