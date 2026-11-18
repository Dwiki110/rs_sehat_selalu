<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}

require_once 'koneksi.php';

// Get all room requests with priority
$query = "SELECT * FROM v_pending_room_requests";
$pendingRequests = $conn->query($query);

// Get statistics
$statsQuery = "SELECT 
    COUNT(*) as total_pending,
    SUM(CASE WHEN priority >= 4 THEN 1 ELSE 0 END) as emergency_count,
    SUM(CASE WHEN waiting_minutes > 60 THEN 1 ELSE 0 END) as overdue_count
FROM v_pending_room_requests";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get unread admin notifications
$notifQuery = "SELECT COUNT(*) as unread FROM admin_notifications WHERE is_read = 0";
$notifResult = $conn->query($notifQuery);
$notifCount = $notifResult->fetch_assoc()['unread'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Rawat Inap | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="bi bi-hospital text-purple-600"></i>
                        Request Rawat Inap
                    </h1>
                    <p class="text-gray-600">Kelola permintaan rawat inap dari dokter</p>
                </div>
                <div class="flex gap-4">
                    <div class="relative">
                        <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                            <i class="bi bi-bell"></i>
                            <?php if ($notifCount > 0): ?>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">
                                    <?= $notifCount ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <a href="admin_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $stats['total_pending'] ?></h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="bi bi-inbox text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Emergency</p>
                        <h3 class="text-3xl font-bold text-red-600"><?= $stats['emergency_count'] ?></h3>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="bi bi-exclamation-triangle text-3xl text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Tunggu > 1 Jam</p>
                        <h3 class="text-3xl font-bold text-orange-600"><?= $stats['overdue_count'] ?></h3>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="bi bi-clock text-3xl text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                <i class="bi bi-list-check"></i> Daftar Request
            </h2>

            <?php if ($pendingRequests->num_rows == 0): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="bi bi-inbox text-6xl mb-4"></i>
                    <p class="text-xl">Tidak ada request pending</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-3 text-left">Prioritas</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left">Dokter</th>
                                <th class="px-4 py-3 text-left">Diagnosis</th>
                                <th class="px-4 py-3 text-left">Ruangan</th>
                                <th class="px-4 py-3 text-left">Estimasi</th>
                                <th class="px-4 py-3 text-left">Waktu Tunggu</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($request = $pendingRequests->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50 <?= $request['priority'] >= 4 ? 'bg-red-50' : '' ?>">
                                    <td class="px-4 py-3">
                                        <?php
                                        $priorityColors = [
                                            5 => 'bg-red-600',
                                            4 => 'bg-orange-500',
                                            3 => 'bg-yellow-500',
                                            2 => 'bg-blue-500',
                                            1 => 'bg-green-500'
                                        ];
                                        $color = $priorityColors[$request['priority']] ?? 'bg-gray-500';
                                        ?>
                                        <span class="<?= $color ?> text-white px-3 py-1 rounded-full text-sm font-semibold">
                                            <?= $request['priority'] ?> - <?= $request['priority_name'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold"><?= htmlspecialchars($request['patient_name']) ?></div>
                                        <div class="text-sm text-gray-600"><?= htmlspecialchars($request['queue_number']) ?></div>
                                    </td>
                                    <td class="px-4 py-3">Dr. <?= htmlspecialchars($request['doctor_name']) ?></td>
                                    <td class="px-4 py-3">
                                        <div class="max-w-xs truncate" title="<?= htmlspecialchars($request['diagnosis']) ?>">
                                            <?= htmlspecialchars(substr($request['diagnosis'], 0, 50)) ?>...
                                        </div>
                                    </td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($request['room_type']) ?></td>
                                    <td class="px-4 py-3"><?= $request['estimated_days'] ?> hari</td>
                                    <td class="px-4 py-3">
                                        <span class="<?= $request['waiting_minutes'] > 60 ? 'text-red-600 font-semibold' : 'text-gray-600' ?>">
                                            <?= $request['waiting_minutes'] ?> menit
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button onclick="showDetail(<?= $request['id'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded mr-2">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button onclick="approveRequest(<?= $request['id'] ?>)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mr-2">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button onclick="rejectRequest(<?= $request['id'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showDetail(requestId) {
            window.location.href = `admin_room_request_detail.php?id=${requestId}`;
        }

        function approveRequest(requestId) {
            const roomNumber = prompt('Masukkan nomor ruangan:');
            if (roomNumber) {
                const notes = prompt('Catatan (optional):') || '';
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'proses_approve_request.php';
                
                const fields = {
                    request_id: requestId,
                    action: 'approve',
                    room_number: roomNumber,
                    admin_notes: notes
                };
                
                for (const [key, value] of Object.entries(fields)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function rejectRequest(requestId) {
            const reason = prompt('Alasan penolakan:');
            if (reason) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'proses_approve_request.php';
                
                const fields = {
                    request_id: requestId,
                    action: 'reject',
                    admin_notes: reason
                };
                
                for (const [key, value] of Object.entries(fields)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
