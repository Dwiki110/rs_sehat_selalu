# 🏥 Fitur Notifikasi Dokter & Admin Rawat Inap

## 📋 Deskripsi
Fitur tambahan untuk sistem RS Sehat Selalu yang menambahkan:
1. **Notifikasi untuk Dokter** - Alert otomatis untuk pasien prioritas tinggi (Emergency/Critical)
2. **Sistem Request Rawat Inap** - Dokter dapat request rawat inap dengan prioritas
3. **Admin Approval Dashboard** - Admin dapat approve/reject request dengan respon cepat

## 🚀 Instalasi

### 1. Upgrade Database
Jalankan script SQL untuk menambahkan tabel baru:

```bash
# PowerShell
Get-Content room_request_upgrade.sql | C:\xampp\mysql\bin\mysql.exe -u root rs_sehat_selalu
```

### 2. Verifikasi Database
Pastikan tabel berikut sudah ditambahkan:

**Tabel Baru:**
- `doctor_notifications` - Notifikasi untuk dokter
- `admin_notifications` - Notifikasi untuk admin
- `room_requests` - Request rawat inap
- `room_types` - Referensi jenis ruangan

**Views:**
- `v_pending_room_requests` - View request pending dengan prioritas
- `v_doctor_emergency_patients` - View pasien emergency per dokter

**Triggers:**
- `after_booking_insert_doctor_notif` - Auto-create notifikasi untuk dokter
- `after_room_request_insert` - Auto-create notifikasi untuk admin

### 3. File-file Baru
Pastikan file-file berikut sudah ada:
- ✅ `room_request_upgrade.sql` - Script upgrade database
- ✅ `api_doctor_notifications.php` - API notifikasi dokter
- ✅ `dokter_request_rawat_inap.php` - Form request rawat inap
- ✅ `proses_request_rawat_inap.php` - Processor request rawat inap
- ✅ `admin_room_requests.php` - Dashboard admin untuk approve
- ✅ `proses_approve_request.php` - Processor approve/reject

## 🎯 Fitur

### 1. Notifikasi Dokter

#### Trigger Otomatis
Notifikasi otomatis dibuat ketika:
- Pasien dengan prioritas 4 (Darurat) booking
- Pasien dengan prioritas 5 (Kritis) booking

#### Jenis Notifikasi
| Type | Prioritas | Deskripsi |
|------|-----------|-----------|
| `emergency_patient` | 5 | Pasien kritis - Perlu penanganan SEGERA |
| `priority_patient` | 4 | Pasien darurat - Perlu perhatian tinggi |
| `appointment_reminder` | 1-3 | Reminder appointment biasa |

#### Fitur Notifikasi Dokter
- ✅ Badge unread count di navbar
- ✅ List pasien emergency/prioritas tinggi
- ✅ Auto-sort berdasarkan prioritas
- ✅ Waktu tunggu real-time
- ✅ Quick action untuk request rawat inap

### 2. Sistem Request Rawat Inap

#### Flow Request
```
Dokter → Pilih Pasien → Isi Diagnosis → Tentukan Prioritas → Pilih Ruangan → Submit
   ↓
Request Masuk ke Admin
   ↓
Admin Approve/Reject
   ↓
Pasien Masuk Rawat Inap
```

#### Form Request Rawat Inap
**Field yang diperlukan:**
- Pilih Pasien (dari booking yang ada)
- Diagnosis (min 10 karakter)
- Alasan Rawat Inap (min 10 karakter)
- Tingkat Prioritas (1-5)
- Jenis Ruangan (Kelas 3, Kelas 2, Kelas 1, VIP, VVIP, ICU)
- Estimasi Lama Rawat Inap (hari)

**Auto-fill:**
- Prioritas auto-set dari booking priority
- ICU auto-selected untuk priority 5 (Kritis)
- Patient info tampil otomatis

#### Priority Levels untuk Rawat Inap
| Level | Nama | Tindakan Admin | SLA |
|-------|------|----------------|-----|
| 5 | Kritis (ICU) | Segera | < 15 menit |
| 4 | Darurat | Prioritas | < 30 menit |
| 3 | Mendesak | Cepat | < 2 jam |
| 2 | Semi Mendesak | Normal | < 4 jam |
| 1 | Tidak Mendesak | Standard | < 24 jam |

### 3. Admin Approval Dashboard

#### Dashboard Features
- **Statistics Cards:**
  - Total Pending Requests
  - Emergency Count (Priority 4-5)
  - Overdue Count (> 1 jam waiting)

- **Request Table:**
  - Sorted by priority DESC, created_at ASC
  - Color-coded priority badges
  - Patient info + queue number
  - Doctor name
  - Diagnosis preview
  - Room type requested
  - Estimated days
  - Waiting time (highlighted if > 60 mins)

#### Actions Admin
1. **View Detail** - Lihat detail lengkap request
2. **Approve** - Setujui dan assign nomor ruangan
3. **Reject** - Tolak dengan alasan

#### Approval Process
```
Admin melihat request → Check availability → Assign room number → Approve
                                              ↓
                                    Patient masuk rawat_inap table
                                              ↓
                                    Notification to doctor & patient
```

### 4. Notifikasi Admin

#### Trigger Otomatis
Notifikasi admin dibuat ketika:
- Dokter membuat request rawat inap
- Request priority 4-5 → type `emergency_room`
- Request priority 1-3 → type `room_request`

#### Fitur Notifikasi Admin
- ✅ Badge unread count
- ✅ Priority-based alert
- ✅ Auto-sort by priority
- ✅ Quick approve/reject

## 📱 Cara Penggunaan

### Untuk Dokter

#### 1. Melihat Notifikasi Pasien Prioritas
1. Login sebagai dokter
2. Dashboard akan menampilkan badge notifikasi jika ada pasien prioritas tinggi
3. Klik badge untuk melihat list pasien emergency
4. Pasien di-sort otomatis berdasarkan prioritas

#### 2. Request Rawat Inap
1. Dari dashboard dokter, klik "Request Rawat Inap"
2. Pilih pasien dari dropdown (otomatis filter pasien dokter tersebut)
3. Isi diagnosis lengkap
4. Isi alasan memerlukan rawat inap
5. Tentukan prioritas (auto-fill dari booking priority)
6. Pilih jenis ruangan (ICU auto-select untuk priority 5)
7. Tentukan estimasi lama rawat inap
8. Submit request
9. Admin akan menerima notifikasi

### Untuk Admin

#### 1. Melihat Request Rawat Inap
1. Login sebagai admin
2. Dari dashboard, klik "Request Rawat Inap" atau akses `admin_room_requests.php`
3. Lihat statistics cards:
   - Total pending
   - Emergency count
   - Overdue count

#### 2. Approve Request
1. Dari tabel request, klik tombol **Approve** (✓)
2. Masukkan nomor ruangan (contoh: ICU-101, VIP-205)
3. Masukkan catatan (optional)
4. Request akan disetujui
5. Data masuk ke tabel `rawat_inap`

#### 3. Reject Request
1. Dari tabel request, klik tombol **Reject** (✗)
2. Masukkan alasan penolakan
3. Request akan ditolak
4. Dokter dapat melihat alasan penolakan

## 🛠️ API Endpoints

### Doctor Notifications API

#### Get Notifications
```
GET /api_doctor_notifications.php?action=get&unread_only=true&limit=10
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "doctor_username": "dwiki",
      "booking_id": 10,
      "patient_name": "John Doe",
      "queue_number": "QA-20251220-001",
      "priority_level": 5,
      "message": "🚨 KRITIS - Pasien John Doe...",
      "type": "emergency_patient",
      "is_read": 0,
      "created_at": "2025-12-20 10:30:00"
    }
  ],
  "count": 1
}
```

#### Get Unread Count
```
GET /api_doctor_notifications.php?action=count
```

#### Mark as Read
```
POST /api_doctor_notifications.php
action=mark_read&notification_id=123
```

#### Get Emergency Patients
```
GET /api_doctor_notifications.php?action=get_emergency_patients
```

## 🧪 Testing

### 1. Test Notifikasi Dokter
```sql
-- Create booking with priority 5
-- Trigger akan auto-create notifikasi untuk dokter

-- Check notifikasi
SELECT * FROM doctor_notifications 
WHERE doctor_username = 'dwiki' AND is_read = 0;

-- Check view
SELECT * FROM v_doctor_emergency_patients 
WHERE doctor_name = 'dwiki';
```

### 2. Test Request Rawat Inap
1. Login sebagai dokter (dwiki)
2. Akses `dokter_request_rawat_inap.php`
3. Pilih pasien dengan priority tinggi
4. Isi form dan submit
5. Check database:
```sql
SELECT * FROM room_requests ORDER BY created_at DESC LIMIT 1;
SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 1;
```

### 3. Test Approval Admin
1. Login sebagai admin
2. Akses `admin_room_requests.php`
3. Approve request dengan nomor ruangan
4. Verify:
```sql
SELECT * FROM room_requests WHERE status = 'approved';
SELECT * FROM rawat_inap ORDER BY tanggal DESC LIMIT 1;
```

## 📊 Database Schema

### doctor_notifications
```sql
id, doctor_username, booking_id, patient_name, queue_number, 
priority_level, message, type, is_read, created_at
```

### admin_notifications
```sql
id, room_request_id, patient_name, doctor_name, priority_level,
message, type, is_read, created_at
```

### room_requests
```sql
id, booking_id, patient_name, doctor_name, diagnosis, reason,
priority, room_type, estimated_days, status, admin_notes,
room_number, approved_by, approved_at, created_at, updated_at
```

### room_types
```sql
id, type_name, capacity, price_per_day, facilities, is_available
```

## ⚠️ Troubleshooting

### Notifikasi dokter tidak muncul
1. Check trigger:
```sql
SHOW TRIGGERS LIKE 'booking';
```
2. Verify booking has priority >= 4
3. Check doctor_username match dengan booking.dokter

### Request rawat inap gagal
1. Check booking_id valid
2. Verify dokter match
3. Check duplicate request:
```sql
SELECT * FROM room_requests WHERE booking_id = ? AND status = 'pending';
```

### Admin tidak bisa approve
1. Check admin logged in dengan role 'admin'
2. Verify room_number diisi
3. Check request status masih 'pending'

## 🔄 Update Selanjutnya (TODO)

- [ ] Email notification untuk dokter dan admin
- [ ] SMS alert untuk emergency cases
- [ ] Real-time push notifications
- [ ] Room availability checker
- [ ] Auto-assign room berdasarkan availability
- [ ] Statistik response time admin
- [ ] Export laporan request rawat inap

## 📝 Changelog

### Version 1.0 (2025-12-20)
- ✅ Notifikasi otomatis untuk dokter (priority 4-5)
- ✅ Form request rawat inap dari dokter
- ✅ Dashboard admin untuk approve/reject
- ✅ Notifikasi otomatis untuk admin
- ✅ Priority-based sorting dan alerts
- ✅ Integration dengan rawat_inap table

## 🤝 Support

Untuk bantuan atau pertanyaan:
- Email: support@rs-sehat-selalu.com
- GitHub Issues: https://github.com/whympxx/PendaftaranPasienRS/issues

---

**Developed with ❤️ by RS Sehat Selalu Team**
