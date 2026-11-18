# 🎛️ Panduan Fitur Manajemen Status Antrian Admin

## 📋 Overview
Admin dapat mengelola status antrian pasien dengan kemampuan untuk:
- ✅ **Panggil** pasien (set status → Confirmed)
- ❌ **Tolak** antrian (set status → Cancelled)
- ✔️ **Selesai** pemeriksaan (set status → Completed)
- 🔄 **Kembalikan** status ke Menunggu

---

## 🚀 Cara Menggunakan

### 1️⃣ Akses Halaman Kelola Antrian
- Login sebagai **Admin**
- Dashboard → Klik card **"Karcis Antrian"** (card ungu dengan icon ticket)
- URL: `http://localhost/rs_sehat_selalu/admin_lihat_antrian.php`

### 2️⃣ Tampilan Karcis Antrian
Setiap card karcis menampilkan:
- **Nomor Antrian** (contoh: QA-20251224-019)
- **Badge Prioritas** (untuk priority 3-5)
- **Informasi Pasien** (username)
- **Dokter** yang dituju
- **Tanggal & Jam** pemeriksaan
- **Keluhan** pasien
- **Status** saat ini
- **Tombol Aksi** (sesuai status)

---

## 🎯 Tombol Aksi Berdasarkan Status

### Status: **MENUNGGU** (Waiting)
**Tombol yang tersedia:**
```
┌──────────────┬──────────────┐
│  📞 PANGGIL  │  ❌ TOLAK    │
└──────────────┴──────────────┘
```

**Aksi:**
- **Panggil** → Mengubah status ke "Dikonfirmasi" (pasien dipanggil untuk pemeriksaan)
- **Tolak** → Mengubah status ke "Dibatalkan" (antrian ditolak admin)

---

### Status: **DIKONFIRMASI** (Confirmed)
**Tombol yang tersedia:**
```
┌──────────────┬──────────────┐
│  ✔️ SELESAI  │  ❌ BATAL    │
└──────────────┴──────────────┘
```

**Aksi:**
- **Selesai** → Mengubah status ke "Selesai" (pemeriksaan completed)
- **Batal** → Mengubah status ke "Dibatalkan" (batalkan antrian)

---

### Status: **DIBATALKAN** (Cancelled)
**Tombol yang tersedia:**
```
┌────────────────────────────┐
│  🔄 KEMBALIKAN             │
└────────────────────────────┘
```

**Aksi:**
- **Kembalikan** → Mengembalikan status ke "Menunggu" (restore antrian)

---

### Status: **SELESAI** (Completed)
**Tampilan:**
```
┌────────────────────────────┐
│  ✅ Antrian Selesai        │
└────────────────────────────┘
```

**Aksi:** Tidak ada tombol (antrian sudah final)

---

## 🔔 Notifikasi Success/Error

### Success Notification (Hijau)
Muncul setelah berhasil update status:
```
✅ Pasien berhasil dipanggil!
✅ Antrian pasien berhasil ditolak.
✅ Antrian berhasil diselesaikan.
✅ Status dikembalikan ke Menunggu.
```

### Error Notification (Merah)
Muncul jika terjadi kesalahan:
```
⚠️ ID booking tidak valid.
⚠️ Status tidak valid.
⚠️ Gagal memperbarui status: [error message]
```

**Notifikasi akan otomatis hilang setelah 5 detik.**

---

## ⚠️ Konfirmasi Sebelum Update

Setiap aksi akan memunculkan dialog konfirmasi:

### Konfirmasi "Panggil"
```
Apakah Anda yakin ingin memanggil pasien dengan nomor antrian QA-20251224-019?
```

### Konfirmasi "Tolak"
```
Apakah Anda yakin ingin menolak antrian QA-20251224-019?
```

### Konfirmasi "Selesai"
```
Apakah Anda yakin pemeriksaan QA-20251224-019 sudah selesai?
```

### Konfirmasi "Kembalikan"
```
Kembalikan status QA-20251224-019 ke Menunggu?
```

---

## 🎨 Warna Tombol

| Tombol | Warna | Gradient |
|--------|-------|----------|
| **Panggil** | Hijau | #28a745 → #20c997 |
| **Tolak** | Merah | #dc3545 → #e74c3c |
| **Selesai** | Biru | #007bff → #0056b3 |
| **Batal** | Merah | #dc3545 → #e74c3c |
| **Kembalikan** | Abu-abu | #6c757d → #5a6268 |

**Efek Hover:**
- Tombol akan naik sedikit (translateY -2px)
- Shadow akan lebih gelap

---

## 📊 Flow Chart Status

```
┌─────────────┐
│   WAITING   │ ← Kembalikan ──────┐
└──────┬──────┘                     │
       │                            │
       ├─ Panggil →┐                │
       │            ↓                │
       │     ┌──────────────┐       │
       │     │  CONFIRMED   │       │
       │     └──────┬───────┘       │
       │            │                │
       │            ├─ Selesai →┐   │
       │            │            ↓   │
       │            │     ┌───────────┐
       │            │     │ COMPLETED │
       │            │     └───────────┘
       │            │
       └─ Tolak →──┴─ Batal →┐
                              ↓
                       ┌─────────────┐
                       │  CANCELLED  │
                       └─────────────┘
```

---

## 🛠️ File yang Terlibat

### 1. `admin_lihat_antrian.php`
**Fungsi:** Halaman utama untuk melihat dan mengelola antrian
**Fitur:**
- Tampilan grid karcis antrian
- Filter (tanggal, prioritas, status)
- Tombol aksi (Panggil, Tolak, Selesai, Kembalikan)
- Notifikasi success/error
- Auto-hide alert setelah 5 detik

**JavaScript Functions:**
```javascript
updateStatus(bookingId, newStatus, queueNumber)
// Menampilkan konfirmasi dan submit form update status

filterTickets()
// Filter antrian berdasarkan tanggal/prioritas/status
```

### 2. `admin_update_status.php`
**Fungsi:** Proses backend untuk update status
**Validasi:**
- Cek session admin
- Validasi booking ID (integer > 0)
- Validasi status (hanya: confirmed, cancelled, completed, waiting)

**Response:**
- Success → Set `$_SESSION['success_message']`
- Error → Set `$_SESSION['error_message']`
- Redirect → `admin_lihat_antrian.php`

---

## 🔐 Security Features

### 1. Session Validation
```php
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
```
**Hanya admin yang bisa akses.**

### 2. Input Validation
```php
// Validasi booking ID
$bookingId = intval($_POST['booking_id']);
if ($bookingId <= 0) { /* error */ }

// Validasi status
$allowedStatuses = ['confirmed', 'cancelled', 'completed', 'waiting'];
if (!in_array($newStatus, $allowedStatuses)) { /* error */ }
```

### 3. Prepared Statements
```php
$stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
$stmt->bind_param("si", $newStatus, $bookingId);
```
**Mencegah SQL Injection.**

### 4. XSS Prevention
```php
htmlspecialchars($successMessage);
htmlspecialchars($errorMessage);
```

### 5. Client-side Confirmation
```javascript
if (confirm(confirmMessages[newStatus])) {
    // Submit form
}
```
**Mencegah klik tidak sengaja.**

---

## 📝 Database Schema

### Table: `booking`
```sql
CREATE TABLE booking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pasien VARCHAR(50),
    dokter VARCHAR(50),
    tanggal DATE,
    hari VARCHAR(20),
    jam VARCHAR(20),
    keluhan TEXT,
    priority TINYINT,
    status VARCHAR(20), -- ✅ Updated field
    queue_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Status Values:
- `waiting` - Menunggu (default untuk booking baru)
- `confirmed` - Dikonfirmasi (dipanggil oleh admin)
- `cancelled` - Dibatalkan (ditolak admin)
- `completed` - Selesai (pemeriksaan selesai)

---

## 🧪 Testing Scenarios

### Test Case 1: Panggil Pasien
**Steps:**
1. Login sebagai admin
2. Buka halaman "Karcis Antrian"
3. Cari card dengan status "Menunggu"
4. Klik tombol **"Panggil"**
5. Konfirmasi dialog → OK

**Expected Result:**
- ✅ Muncul notifikasi hijau: "Pasien berhasil dipanggil!"
- ✅ Status card berubah jadi "Dikonfirmasi"
- ✅ Tombol berubah jadi "Selesai" dan "Batal"

---

### Test Case 2: Tolak Antrian
**Steps:**
1. Pilih card dengan status "Menunggu"
2. Klik tombol **"Tolak"**
3. Konfirmasi → OK

**Expected Result:**
- ✅ Notifikasi hijau: "Antrian pasien berhasil ditolak."
- ✅ Status card berubah jadi "Dibatalkan"
- ✅ Tombol berubah jadi "Kembalikan"

---

### Test Case 3: Selesai Pemeriksaan
**Steps:**
1. Pilih card dengan status "Dikonfirmasi"
2. Klik tombol **"Selesai"**
3. Konfirmasi → OK

**Expected Result:**
- ✅ Notifikasi hijau: "Antrian berhasil diselesaikan."
- ✅ Status card berubah jadi "Selesai"
- ✅ Tombol hilang, muncul teks "✅ Antrian Selesai"

---

### Test Case 4: Kembalikan Status
**Steps:**
1. Pilih card dengan status "Dibatalkan"
2. Klik tombol **"Kembalikan"**
3. Konfirmasi → OK

**Expected Result:**
- ✅ Notifikasi: "Status dikembalikan ke Menunggu."
- ✅ Status card berubah jadi "Menunggu"
- ✅ Tombol berubah jadi "Panggil" dan "Tolak"

---

### Test Case 5: Cancel Confirmation
**Steps:**
1. Klik tombol aksi apapun
2. Dialog konfirmasi muncul
3. Klik **CANCEL**

**Expected Result:**
- ✅ Tidak ada perubahan
- ✅ Status tetap sama
- ✅ Tidak ada notifikasi

---

### Test Case 6: Filter Integration
**Steps:**
1. Update status beberapa antrian
2. Test filter status:
   - Filter: "Dikonfirmasi" → Hanya show card confirmed
   - Filter: "Dibatalkan" → Hanya show card cancelled
   - Filter: "Selesai" → Hanya show card completed

**Expected Result:**
- ✅ Filter bekerja dengan status baru
- ✅ Card tersembunyi/muncul sesuai filter

---

## 🎯 Use Cases

### Use Case 1: Admin Memanggil Pasien Priority Tinggi
**Scenario:**
- 10 pasien waiting
- 1 pasien Priority 5 (KRITIS)
- Admin ingin panggil pasien kritis dulu

**Action:**
1. Card Priority 5 otomatis di paling atas (sorting)
2. Admin klik "Panggil" pada card tersebut
3. Status → Dikonfirmasi
4. Pasien datang ke ruang pemeriksaan

---

### Use Case 2: Admin Menolak Antrian Duplikat
**Scenario:**
- Pasien booking 2x untuk dokter sama
- Admin ingin tolak salah satu

**Action:**
1. Admin lihat 2 card dengan pasien sama
2. Klik "Tolak" pada card duplikat
3. Status → Dibatalkan
4. Card ditandai sebagai "Dibatalkan"

---

### Use Case 3: Pemeriksaan Selesai
**Scenario:**
- Pasien sudah selesai diperiksa dokter
- Admin ingin update status

**Action:**
1. Cari card dengan pasien yang sudah selesai
2. Klik "Selesai"
3. Status → Completed
4. Card menampilkan "✅ Antrian Selesai"

---

### Use Case 4: Admin Salah Tolak
**Scenario:**
- Admin tidak sengaja klik "Tolak"
- Ingin mengembalikan status

**Action:**
1. Cari card yang statusnya "Dibatalkan"
2. Klik "Kembalikan"
3. Status → Waiting
4. Antrian kembali normal

---

## 🔍 Troubleshooting

### Problem: Tombol tidak muncul
**Solution:**
- Pastikan login sebagai admin
- Cek status antrian (tombol berbeda per status)
- Refresh halaman (Ctrl+F5)

### Problem: Notifikasi tidak muncul
**Solution:**
- Cek session di `admin_update_status.php`
- Pastikan redirect ke `admin_lihat_antrian.php`
- Cek browser console untuk JavaScript error

### Problem: Status tidak berubah
**Solution:**
- Cek koneksi database
- Verifikasi booking ID valid
- Lihat error message di notifikasi merah

### Problem: Konfirmasi tidak muncul
**Solution:**
- Pastikan JavaScript enabled
- Cek browser console untuk error
- Test di browser lain

---

## 📊 Performance Notes

### Query Optimization:
```php
// Single query untuk get semua data
$booking = mysqli_query($conn, "
    SELECT b.*, d.nama as nama_dokter 
    FROM booking b 
    LEFT JOIN dokter d ON b.dokter = d.username 
    ORDER BY b.priority DESC, b.tanggal ASC, b.queue_number ASC
");
```

### Update Efficiency:
```php
// Prepared statement untuk security dan speed
$stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
$stmt->bind_param("si", $newStatus, $bookingId);
$stmt->execute();
```

---

## ✅ Checklist Implementasi

- [x] File `admin_update_status.php` dibuat
- [x] Tombol aksi ditambahkan di `admin_lihat_antrian.php`
- [x] JavaScript `updateStatus()` function
- [x] Konfirmasi dialog sebelum update
- [x] Notifikasi success/error
- [x] Auto-hide alert setelah 5 detik
- [x] Session validation
- [x] Input validation
- [x] Prepared statements (SQL injection prevention)
- [x] XSS prevention (htmlspecialchars)
- [x] Responsive design untuk tombol
- [x] Gradient styling tombol
- [x] Hover effects
- [x] Status-based button visibility
- [x] Documentation completed

---

## 🚀 Status: PRODUCTION READY ✅

**Fitur ini siap digunakan dalam production environment!**

---

**Dokumen Dibuat:** 2025-12-20  
**Versi:** 1.0  
**Author:** AI Development Team  
**Status:** ✅ COMPLETE
