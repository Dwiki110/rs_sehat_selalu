<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Ambil data dokter dan jadwal
$dokter_result = mysqli_query($conn, "SELECT DISTINCT dokter FROM dokter_jadwal");
$dokter_jadwal_result = mysqli_query($conn, "SELECT * FROM dokter_jadwal");

// Simpan jadwal dokter dalam array
$dokter_jadwal = [];
while ($row = mysqli_fetch_assoc($dokter_jadwal_result)) {
    $dokter = $row['dokter'];
    $dokter_jadwal[$dokter][] = [
        'hari' => $row['hari'],
        'jam' => $row['jam']
    ];
}
?>

<h2>Booking Janji Temu</h2>

<form action="proses_booking.php" method="POST" onsubmit="return validasiBooking();">
    <label>Dokter:</label>
    <select name="dokter" id="dokter" onchange="tampilkanJadwal()" required>
        <option value="">-- Pilih Dokter --</option>
        <?php while ($dok = mysqli_fetch_assoc($dokter_result)): ?>
            <option value="<?= $dok['dokter'] ?>"><?= $dok['dokter'] ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <div id="jadwal_dokter" style="display: none;">
        <strong>Jadwal Dokter:</strong>
        <ul id="list_jadwal"></ul>
    </div>

    <label>Hari:</label>
    <input type="text" name="hari" id="hari" required><br>

    <label>Jam:</label>
    <input type="text" name="jam" id="jam" required><br>

    <!-- Kolom Keluhan -->
    <label>Keluhan:</label><br>
    <textarea name="keluhan" rows="4" cols="50" placeholder="Tuliskan keluhan Anda di sini..." required></textarea><br><br>

    <button type="submit">Booking</button>
</form>

<script>
    const dokterJadwal = <?= json_encode($dokter_jadwal) ?>;

    function tampilkanJadwal() {
        const dokter = document.getElementById('dokter').value;
        const list = document.getElementById('list_jadwal');
        const jadwalBox = document.getElementById('jadwal_dokter');
        list.innerHTML = '';
        if (dokter && dokterJadwal[dokter]) {
            jadwalBox.style.display = 'block';
            dokterJadwal[dokter].forEach(j => {
                const li = document.createElement('li');
                li.innerText = j.hari + ' - ' + j.jam;
                list.appendChild(li);
            });
        } else {
            jadwalBox.style.display = 'none';
        }
    }

    function validasiBooking() {
        const dokter = document.getElementById('dokter').value;
        const hari = document.getElementById('hari').value.trim().toLowerCase();
        const jam = document.getElementById('jam').value.trim();
        const cocok = dokterJadwal[dokter]?.some(j => j.hari.toLowerCase() === hari && j.jam === jam);
        if (!cocok) {
            alert("Jadwal tidak sesuai dengan jadwal dokter!");
            return false;
        }
        return true;
    }
</script>
