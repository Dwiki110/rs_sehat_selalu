<?php
session_start();
include 'config.php';
include 'PriorityQueue.php';

// Check if user is logged in and is admin or dokter
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'admin' && !isset($_SESSION['dokter_username']))) {
    header('Location: index.php');
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : $_SESSION['dokter_username'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'dokter';

// Initialize Priority Queue
$priorityQueue = new PriorityQueue(100);

// Load patients from booking table into priority queue
$query = "SELECT b.id, b.pasien as name, b.dokter, b.tanggal, b.hari, b.jam, b.keluhan, 
          UNIX_TIMESTAMP(b.created_at) as arrival_time,
          COALESCE(b.priority, 1) as priority
          FROM booking b
          WHERE b.status = 'waiting'
          ORDER BY b.created_at ASC";

$result = $conn->query($query);
$patients = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determine priority based on keluhan keywords if not set
        if (!isset($row['priority']) || $row['priority'] == 0) {
            $row['priority'] = determinePriority($row['keluhan']);
        }
        
        $patients[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'dokter' => $row['dokter'],
            'tanggal' => $row['tanggal'],
            'hari' => $row['hari'],
            'jam' => $row['jam'],
            'keluhan' => $row['keluhan'],
            'priority' => (int)$row['priority'],
            'arrival_time' => (int)$row['arrival_time']
        );
    }
    
    // Build heap from patients
    if (count($patients) > 0) {
        $priorityQueue->buildHeap($patients);
    }
}

// Function to determine priority based on keluhan
function determinePriority($keluhan) {
    $keluhan = strtolower($keluhan);
    
    // Critical keywords - UPDATED: Added 'parah', 'kondisi parah', 'kejang'
    if (preg_match('/(henti jantung|tidak sadar|pingsan|kejang|sesak nafas berat|pendarahan hebat|parah|kondisi parah|sangat parah)/i', $keluhan)) {
        return PriorityQueue::PRIORITY_CRITICAL;
    }
    
    // Emergency keywords - UPDATED: Added 'sesak', 'tinggi', 'demam parah', 'sakit parah'
    if (preg_match('/(nyeri dada|sesak nafas|sesak|demam tinggi|tinggi|muntah darah|kecelakaan|demam parah|sakit parah|kondisi tinggi)/i', $keluhan)) {
        return PriorityQueue::PRIORITY_EMERGENCY;
    }
    
    // Urgent keywords
    if (preg_match('/(demam|diare|nyeri|sakit kepala berat|luka)/i', $keluhan)) {
        return PriorityQueue::PRIORITY_URGENT;
    }
    
    // Semi urgent keywords
    if (preg_match('/(batuk|pilek|flu|pusing)/i', $keluhan)) {
        return PriorityQueue::PRIORITY_SEMI_URGENT;
    }
    
    // Default non-urgent
    return PriorityQueue::PRIORITY_NON_URGENT;
}

// Get statistics
$stats = $priorityQueue->getStatistics();

// Get sorted patient list
$sortedPatients = $priorityQueue->getAllPatients();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian Prioritas Pasien | RS Sehat Selalu</title>
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
                        <i class="bi bi-sort-numeric-down text-blue-600"></i>
                        Antrian Prioritas Pasien
                    </h1>
                    <p class="text-gray-600">Sistem Prioritas Berbasis Max Heap Algorithm</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Login sebagai:</p>
                    <p class="text-lg font-semibold text-blue-600"><?= htmlspecialchars($username) ?></p>
                    <a href="<?= $role == 'admin' ? 'admin_dashboard.php' : 'dokter_dashboard.php' ?>" 
                       class="text-sm text-blue-500 hover:text-blue-700">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Pasien -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Antrian</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $stats['total'] ?></h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="bi bi-people text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Critical -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Kritis</p>
                        <h3 class="text-3xl font-bold text-red-600"><?= $stats['critical'] ?></h3>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="bi bi-exclamation-triangle text-3xl text-red-600"></i>
                    </div>
                </div>
            </div>

            <!-- Emergency -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Darurat</p>
                        <h3 class="text-3xl font-bold text-orange-600"><?= $stats['emergency'] ?></h3>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="bi bi-lightning text-3xl text-orange-600"></i>
                    </div>
                </div>
            </div>

            <!-- Average Wait Time -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Rata-rata Tunggu</p>
                        <h3 class="text-3xl font-bold text-purple-600"><?= $stats['average_wait_time'] ?> <span class="text-sm">menit</span></h3>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="bi bi-clock text-3xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="bi bi-bar-chart"></i> Distribusi Prioritas
            </h2>
            <div class="grid grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="bg-red-600 text-white rounded-lg py-4 mb-2">
                        <div class="text-2xl font-bold"><?= $stats['critical'] ?></div>
                    </div>
                    <p class="text-sm text-gray-600">Kritis (5)</p>
                </div>
                <div class="text-center">
                    <div class="bg-orange-500 text-white rounded-lg py-4 mb-2">
                        <div class="text-2xl font-bold"><?= $stats['emergency'] ?></div>
                    </div>
                    <p class="text-sm text-gray-600">Darurat (4)</p>
                </div>
                <div class="text-center">
                    <div class="bg-yellow-500 text-white rounded-lg py-4 mb-2">
                        <div class="text-2xl font-bold"><?= $stats['urgent'] ?></div>
                    </div>
                    <p class="text-sm text-gray-600">Mendesak (3)</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-500 text-white rounded-lg py-4 mb-2">
                        <div class="text-2xl font-bold"><?= $stats['semi_urgent'] ?></div>
                    </div>
                    <p class="text-sm text-gray-600">Semi (2)</p>
                </div>
                <div class="text-center">
                    <div class="bg-green-500 text-white rounded-lg py-4 mb-2">
                        <div class="text-2xl font-bold"><?= $stats['non_urgent'] ?></div>
                    </div>
                    <p class="text-sm text-gray-600">Normal (1)</p>
                </div>
            </div>
        </div>

        <!-- Algorithm Explanation -->
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <h2 class="text-xl font-bold mb-4">
                <i class="bi bi-info-circle"></i> Tentang Algoritma Max Heap
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h3 class="font-bold mb-2">Kompleksitas Waktu:</h3>
                    <ul class="text-sm space-y-1">
                        <li>• Insert: O(log n)</li>
                        <li>• Extract Max: O(log n)</li>
                        <li>• Peek: O(1)</li>
                        <li>• Build Heap: O(n)</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold mb-2">Keuntungan:</h3>
                    <ul class="text-sm space-y-1">
                        <li>• Efisien untuk prioritas</li>
                        <li>• Akses cepat ke prioritas tertinggi</li>
                        <li>• Update prioritas dinamis</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold mb-2">Kriteria Prioritas:</h3>
                    <ul class="text-sm space-y-1">
                        <li>• Tingkat kegawatan (1-5)</li>
                        <li>• Waktu kedatangan (FIFO)</li>
                        <li>• Auto-assign berdasarkan keluhan</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Patient Queue Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-6">
                <h2 class="text-2xl font-bold text-white">
                    <i class="bi bi-list-ol"></i> Daftar Antrian (Terurut Berdasarkan Prioritas)
                </h2>
                <p class="text-blue-100 mt-2">Pasien diurutkan menggunakan Max Heap Algorithm</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">No. Urut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Prioritas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nama Pasien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Dokter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jadwal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Keluhan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Waktu Tunggu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($sortedPatients)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <i class="bi bi-inbox text-4xl mb-2"></i>
                                    <p>Tidak ada pasien dalam antrian</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($sortedPatients as $patient): ?>
                                <?php
                                $waitTime = floor((time() - $patient['arrival_time']) / 60);
                                $priorityColor = PriorityQueue::getPriorityColor($patient['priority']);
                                $priorityName = PriorityQueue::getPriorityName($patient['priority']);
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold">
                                                <?= $no++ ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="<?= $priorityColor ?> text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= $priorityName ?> (<?= $patient['priority'] ?>)
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($patient['name']) ?></div>
                                        <div class="text-sm text-gray-500">ID: <?= $patient['id'] ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($patient['dokter']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($patient['hari']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($patient['tanggal']) ?> - <?= htmlspecialchars($patient['jam']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate" title="<?= htmlspecialchars($patient['keluhan']) ?>">
                                            <?= htmlspecialchars($patient['keluhan']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm <?= $waitTime > 30 ? 'text-red-600 font-bold' : 'text-gray-900' ?>">
                                            <?= $waitTime ?> menit
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button onclick="callPatient(<?= $patient['id'] ?>)" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                                            <i class="bi bi-telephone"></i> Panggil
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Heap Visualization (Optional) -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="bi bi-diagram-3"></i> Visualisasi Struktur Max Heap
            </h2>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 mb-4">Heap Tree Structure (Top 3 Priority)</p>
                <?php if (count($sortedPatients) > 0): ?>
                    <div class="flex justify-center items-start space-x-8">
                        <!-- Root -->
                        <div class="text-center">
                            <div class="<?= PriorityQueue::getPriorityColor($sortedPatients[0]['priority']) ?> text-white rounded-lg p-4 mb-2 inline-block">
                                <div class="font-bold"><?= htmlspecialchars($sortedPatients[0]['name']) ?></div>
                                <div class="text-xs">Priority: <?= $sortedPatients[0]['priority'] ?></div>
                            </div>
                            <?php if (count($sortedPatients) > 1): ?>
                                <div class="flex justify-center gap-8 mt-4">
                                    <!-- Left Child -->
                                    <?php if (isset($sortedPatients[1])): ?>
                                        <div class="<?= PriorityQueue::getPriorityColor($sortedPatients[1]['priority']) ?> text-white rounded-lg p-3 text-sm">
                                            <div class="font-bold"><?= htmlspecialchars($sortedPatients[1]['name']) ?></div>
                                            <div class="text-xs">P: <?= $sortedPatients[1]['priority'] ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Right Child -->
                                    <?php if (isset($sortedPatients[2])): ?>
                                        <div class="<?= PriorityQueue::getPriorityColor($sortedPatients[2]['priority']) ?> text-white rounded-lg p-3 text-sm">
                                            <div class="font-bold"><?= htmlspecialchars($sortedPatients[2]['name']) ?></div>
                                            <div class="text-xs">P: <?= $sortedPatients[2]['priority'] ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">Heap kosong</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function callPatient(id) {
            if (confirm('Panggil pasien ini untuk pemeriksaan?')) {
                // Update status booking
                window.location.href = 'dokter_catat_pemeriksaan.php?booking_id=' + id;
            }
        }

        // Auto refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
