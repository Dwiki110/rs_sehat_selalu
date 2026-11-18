# 🏥 Fitur Nomor Antrian & Notifikasi Prioritas

## 📋 Deskripsi
Fitur baru untuk RS Sehat Selalu yang menambahkan:
1. **Nomor Antrian Otomatis** dengan format `QA-YYYYMMDD-XXX`
2. **Batas Waktu Pendaftaran** sampai jam 23:00
3. **Notifikasi Prioritas** berdasarkan tingkat emergency dan diagnosa pasien

## 🚀 Instalasi

### 1. Upgrade Database
Jalankan script SQL untuk menambahkan tabel dan kolom baru:

```bash
mysql -u root -p rs_sehat_selalu < queue_notification_upgrade.sql
```

Atau melalui phpMyAdmin:
1. Buka phpMyAdmin
2. Pilih database `rs_sehat_selalu`
3. Klik tab "SQL"
4. Copy dan paste isi file `queue_notification_upgrade.sql`
5. Klik "Go"

### 2. Verifikasi Database
Pastikan tabel dan kolom berikut sudah ditambahkan:

**Tabel `booking`** - Kolom baru:
- `queue_number` VARCHAR(20) - Nomor antrian
- `priority_notification_sent` TINYINT(1) - Status notifikasi terkirim
- `estimated_call_time` TIME - Estimasi waktu pemanggilan

**Tabel baru:**
- `notifications` - Menyimpan notifikasi pasien
- `queue_counter` - Counter nomor antrian per hari
- `priority_levels` - Referensi level prioritas

**Stored Procedures:**
- `GenerateQueueNumber()` - Generate nomor antrian
- `CreateNotification()` - Buat notifikasi
- `UpdateQueueStatistics()` - Update statistik antrian

**Functions:**
- `GetPriorityMessage()` - Template pesan prioritas

**Triggers:**
- `before_booking_insert` - Auto-generate queue number dan prioritas
- `after_booking_insert` - Auto-create notifikasi untuk prioritas 3+

### 3. File-file Baru
Pastikan file-file berikut sudah ada:
- ✅ `QueueNumberGenerator.php` - Class generator nomor antrian
- ✅ `NotificationManager.php` - Class manager notifikasi
- ✅ `api_notifications.php` - API endpoint notifikasi
- ✅ `notifications.js` - JavaScript handler notifikasi
- ✅ `queue_notification_upgrade.sql` - Script upgrade database

### 4. File yang Dimodifikasi
File-file berikut sudah diupdate:
- ✅ `proses_booking.php` - Tambah validasi waktu & generate queue number
- ✅ `pasien_booking.php` - Validasi client-side untuk jam 23:00

## 🎯 Fitur

### 1. Nomor Antrian Otomatis

#### Format
```
QA-YYYYMMDD-XXX
```
Contoh: `QA-20251219-001`, `QA-20251219-002`

#### Cara Kerja
- Nomor antrian di-generate otomatis saat pasien booking
- Counter reset setiap hari
- Unique per hari (tidak ada duplikasi)
- Thread-safe dengan database locking

### 2. Batas Waktu Pendaftaran

#### Aturan
- Pendaftaran **hanya dibuka sampai jam 23:00**
- Setelah jam 23:00, form booking akan disabled
- Validasi di client-side dan server-side

#### Pesan Warning
- **Jam 21:00-22:00**: Warning kuning
- **Jam 22:00-23:00**: Warning merah dengan countdown
- **Jam 23:00+**: Form disabled, pesan "Pendaftaran ditutup"

### 3. Level Prioritas

| Level | Nama | Deskripsi | Waktu Tunggu Maks | Notifikasi |
|-------|------|-----------|-------------------|------------|
| 5 | Kritis | Penanganan segera | 0 menit | ✅ Auto |
| 4 | Darurat | Kondisi serius | 30 menit | ✅ Auto |
| 3 | Mendesak | Perlu perhatian cepat | 2 jam | ✅ Auto |
| 2 | Semi Mendesak | Kondisi stabil | 4 jam | ❌ |
| 1 | Tidak Mendesak | Kondisi ringan | 24 jam | ❌ |

### 4. Deteksi Prioritas Otomatis

#### Keywords Prioritas 5 (Kritis)
- henti jantung
- tidak sadar
- pingsan
- kejang
- sesak nafas berat
- pendarahan hebat

#### Keywords Prioritas 4 (Darurat)
- nyeri dada
- sesak nafas
- demam tinggi
- muntah darah
- kecelakaan

#### Keywords Prioritas 3 (Mendesak)
- demam
- diare
- nyeri
- sakit kepala berat
- luka

#### Keywords Prioritas 2 (Semi Mendesak)
- batuk
- pilek
- flu
- pusing

#### Default
Jika tidak ada keyword yang cocok → Prioritas 1 (Tidak Mendesak)

### 5. Sistem Notifikasi

#### Jenis Notifikasi
1. **Emergency** 🚨 - Untuk prioritas 4-5
2. **Queue Update** ⚡ - Untuk prioritas 3
3. **Call Soon** 🔔 - Ketika akan dipanggil
4. **Priority Assigned** ✅ - Konfirmasi booking

#### Fitur Notifikasi
- ✅ Badge unread count di navbar
- ✅ Dropdown notifikasi
- ✅ Real-time polling (30 detik)
- ✅ Mark as read
- ✅ Mark all as read
- ✅ Icon dan warna per jenis notifikasi

## 📱 Cara Penggunaan

### Untuk Pasien

#### 1. Booking Janji Temu
1. Login sebagai pasien
2. Klik menu "Booking Janji Temu"
3. Pastikan waktu masih sebelum jam 23:00
4. Isi form booking:
   - Pilih dokter
   - Pilih tanggal
   - Pilih jam
   - **Tuliskan keluhan dengan detail** (untuk deteksi prioritas)
5. Klik "Booking Sekarang"
6. Nomor antrian akan di-generate otomatis
7. Notifikasi akan dikirim jika prioritas 3+

#### 2. Melihat Notifikasi
1. Login ke dashboard pasien
2. Lihat icon bell 🔔 di navbar
3. Badge merah menunjukkan jumlah notifikasi unread
4. Klik icon bell untuk membuka dropdown notifikasi
5. Klik tomom ✓ untuk mark as read

#### 3. Cek Nomor Antrian
1. Nomor antrian akan ditampilkan setelah booking berhasil
2. Lihat di halaman "Riwayat Booking"
3. Nomor antrian juga ada di notifikasi

### Untuk Admin/Dokter

#### 1. Lihat Priority Queue
1. Login sebagai admin/dokter
2. Buka `priority_queue_dashboard.php`
3. Lihat antrian terurut berdasarkan prioritas
4. Nomor antrian ditampilkan per pasien

#### 2. Kirim Notifikasi Manual
(Akan diimplementasi di update berikutnya)

## 🛠️ API Endpoints

### GET /api_notifications.php

#### Get Notifications
```
GET /api_notifications.php?action=get&unread_only=true&limit=10
```

Response:
```json
{
  "success": true,
  "data": [...],
  "count": 5
}
```

#### Get Unread Count
```
GET /api_notifications.php?action=count
```

Response:
```json
{
  "success": true,
  "unread_count": 3
}
```

### POST /api_notifications.php

#### Mark as Read
```
POST /api_notifications.php
Content-Type: application/x-www-form-urlencoded

action=mark_read&notification_id=123
```

#### Mark All as Read
```
POST /api_notifications.php
Content-Type: application/x-www-form-urlencoded

action=mark_all_read
```

## 🧪 Testing

### 1. Test Generate Nomor Antrian
```sql
-- Test generate queue number
CALL GenerateQueueNumber(CURDATE(), @qn);
SELECT @qn;

-- Check counter
SELECT * FROM queue_counter WHERE date = CURDATE();
```

### 2. Test Prioritas Otomatis
Buat booking dengan keluhan berikut:

| Keluhan | Expected Priority |
|---------|-------------------|
| "Nyeri dada hebat" | 4 (Darurat) |
| "Demam tinggi" | 4 (Darurat) |
| "Sakit kepala berat" | 3 (Mendesak) |
| "Batuk pilek" | 2 (Semi Mendesak) |
| "Konsultasi rutin" | 1 (Tidak Mendesak) |

### 3. Test Validasi Waktu
Ubah waktu server untuk testing:
```php
// Temporary untuk testing
$currentHour = 23; // Simulasi jam 23:00
```

### 4. Test Notifikasi
1. Buat booking dengan prioritas 3+
2. Check database:
```sql
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;
```
3. Login sebagai pasien
4. Verifikasi notifikasi muncul di dashboard

## 📊 Monitoring

### View Queue Statistics
```sql
SELECT * FROM v_queue_with_notifications;
```

### View Notifications
```sql
SELECT * FROM notifications 
WHERE patient_username = 'pasien1' 
ORDER BY created_at DESC;
```

### View Queue Counter
```sql
SELECT * FROM queue_counter 
ORDER BY date DESC 
LIMIT 7;
```

### View Priority Log
```sql
SELECT * FROM priority_log 
ORDER BY changed_at DESC 
LIMIT 10;
```

## ⚠️ Troubleshooting

### Nomor antrian tidak ter-generate
1. Check apakah tabel `queue_counter` ada
2. Check apakah trigger `before_booking_insert` aktif:
```sql
SHOW TRIGGERS LIKE 'booking';
```
3. Check error log di PHP:
```bash
tail -f /xampp/apache/logs/error.log
```

### Notifikasi tidak muncul
1. Check apakah tabel `notifications` ada
2. Verify data notifikasi:
```sql
SELECT * FROM notifications WHERE patient_username = 'your_username';
```
3. Check browser console untuk error JavaScript
4. Pastikan session aktif

### Validasi waktu 23:00 tidak bekerja
1. Check timezone server:
```php
echo date_default_timezone_get();
```
2. Set timezone di `php.ini`:
```ini
date.timezone = "Asia/Jakarta"
```

## 🔄 Update Selanjutnya (TODO)

- [ ] Integrasi notifikasi di `pasien_dashboard.php`
- [ ] Update `priority_queue_dashboard.php` dengan nomor antrian
- [ ] Fitur kirim notifikasi manual dari dashboard admin/dokter
- [ ] Email notification (optional)
- [ ] SMS notification (optional)
- [ ] Push notification (optional)
- [ ] Statistik real-time di dashboard
- [ ] Export laporan antrian

## 📝 Changelog

### Version 1.0 (2025-12-19)
- ✅ Initial release
- ✅ Nomor antrian otomatis
- ✅ Validasi waktu pendaftaran 23:00
- ✅ Deteksi prioritas berdasarkan keluhan
- ✅ Sistem notifikasi prioritas
- ✅ API notifikasi
- ✅ JavaScript handler untuk real-time notifications

## 🤝 Support

Untuk bantuan atau pertanyaan:
- Email: support@rs-sehat-selalu.com
- GitHub Issues: https://github.com/whympxx/PendaftaranPasienRS/issues

---

**Developed with ❤️ by RS Sehat Selalu Team**
