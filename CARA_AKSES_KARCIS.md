# 🎫 Cara Akses Karcis Antrian - RS Sehat Selalu

## 📍 Akses untuk Setiap Role

### 🩺 **PASIEN**
1. Login sebagai pasien
2. Pilih menu **"Riwayat Booking"** dari dashboard
3. Atau langsung akses: `localhost/rs_sehat_selalu/pasien_riwayat.php`
4. Karcis antrian akan muncul dalam format grid card
5. Klik tombol **"Lihat Karcis"** pada setiap card untuk melihat detail dan cetak

**Fitur:**
- ✅ Melihat semua booking sendiri
- ✅ Karcis dengan nomor antrian jumbo
- ✅ Badge prioritas (jika priority >= 3)
- ✅ Cetak/download karcis
- ✅ Warna theme: Purple

---

### 👨‍⚕️ **DOKTER**
1. Login sebagai dokter
2. Dari dashboard dokter, pilih menu **"Lihat Karcis Antrian"** (menu baru dengan ikon tiket ungu)
3. Atau langsung akses: `localhost/rs_sehat_selalu/dokter_lihat_antrian.php`
4. Akan muncul semua antrian pasien yang booking ke dokter tersebut
5. Klik tombol **"Lihat Karcis"** untuk detail

**Fitur:**
- ✅ Melihat antrian pasien sendiri
- ✅ Banner info dokter (nama + spesialis)
- ✅ Filter: Tanggal, Prioritas, Status
- ✅ Sorting berdasarkan Priority DESC
- ✅ Cetak karcis untuk dibagikan ke pasien
- ✅ Warna theme: Red/Orange

**Urutan di Dashboard Dokter:**
1. Lihat Booking Pasien (biru)
2. **Lihat Karcis Antrian** (ungu) ⭐ BARU
3. Catat Pemeriksaan (hijau)
4. Rawat Inap (pink)
5. Logout (merah)

---

### 🛡️ **ADMIN**
1. Login sebagai admin
2. Dari dashboard admin, pilih menu **"Karcis Antrian"** (card ungu dengan ikon tiket)
3. Atau langsung akses: `localhost/rs_sehat_selalu/admin_lihat_antrian.php`
4. Akan muncul **SEMUA antrian dari SEMUA dokter**
5. Klik tombol **"Lihat Karcis"** untuk detail

**Fitur:**
- ✅ Melihat SEMUA antrian rumah sakit
- ✅ Info pasien + dokter di setiap card
- ✅ Filter lengkap untuk manajemen
- ✅ Sorting berdasarkan Priority DESC
- ✅ Cetak semua karcis
- ✅ Warna theme: Purple

**Urutan di Dashboard Admin (Grid):**
1. Kelola Booking (biru)
2. **Karcis Antrian** (ungu) ⭐ BARU
3. Pembayaran (hijau)
4. Rawat Inap (pink)
5. Riwayat Diagnosa (orange)
6. Logout (merah)

---

## 🎨 Tampilan Karcis

### Format Card:
```
┌─────────────────────────────────┐
│  🏷️ BADGE PRIORITAS (top-right) │
│  ┌───────────────────────────┐  │
│  │   HEADER (Gradient)       │  │
│  │   QA-20251220-001         │  │
│  │   (Nomor Antrian Jumbo)   │  │
│  └───────────────────────────┘  │
│                                  │
│  👤 Pasien: [Nama]              │
│  👨‍⚕️ Dokter: [Nama]            │
│  📅 Tanggal: [DD MMM YYYY]      │
│  🕐 Jam: [HH:MM] WIB            │
│  🟢 Status: [Badge]             │
│                                  │
│  [🔍 Lihat Karcis Button]       │
│                                  │
│  ───────────────────────────    │
│  💬 KELUHAN:                    │
│  [Deskripsi...]                 │
└─────────────────────────────────┘
```

### Modal Karcis (saat klik "Lihat Karcis"):
```
┌────────────────────────────────┐
│  [X Close]                     │
│  ┌──────────────────────────┐ │
│  │   🏥 RS SEHAT SELALU     │ │
│  │   Nomor Antrian Anda     │ │
│  │   QA-20251220-001        │ │
│  └──────────────────────────┘ │
│                                │
│  Detail lengkap...             │
│                                │
│  [🖨️ Cetak] [📥 Download]      │
└────────────────────────────────┘
```

---

## 🎯 Filter & Sorting

### Filter Tersedia:
**Tanggal:**
- Semua Tanggal
- Hari Ini
- Besok
- Minggu Ini

**Prioritas:**
- Semua Prioritas
- 5 - Kritis (🔴)
- 4 - Darurat (🟠)
- 3 - Mendesak (🟡)
- 2 - Normal (🔵)
- 1 - Reguler (🟢)

**Status:**
- Semua Status
- Menunggu
- Dikonfirmasi
- Selesai

### Urutan Tampilan:
```sql
ORDER BY priority DESC, tanggal ASC, queue_number ASC
```

Artinya:
1. **Priority 5** (Kritis) tampil paling atas
2. **Priority 4** (Darurat)
3. **Priority 3** (Mendesak)
4. **Priority 2** (Normal)
5. **Priority 1** (Reguler)

Dalam priority yang sama, diurutkan dari tanggal terdekat.

---

## 🚀 Testing

### Test Flow Lengkap:

**1. Test sebagai PASIEN:**
```
1. Login: pasien1
2. Buat booking dengan keluhan "demam parah kejang"
3. Otomatis priority = 5
4. Modal karcis muncul otomatis
5. Lihat di "Riwayat Booking"
6. Badge KRITIS dengan animasi pulse
7. Cetak karcis
```

**2. Test sebagai DOKTER:**
```
1. Login: dokter yang dipilih pasien
2. Dashboard > "Lihat Karcis Antrian" (menu ungu)
3. Akan muncul booking pasien tadi
4. Badge KRITIS di pojok kanan atas
5. Filter priority = 5
6. Klik "Lihat Karcis"
7. Modal muncul dengan detail
```

**3. Test sebagai ADMIN:**
```
1. Login: admin
2. Dashboard > "Karcis Antrian" (card ungu)
3. Muncul SEMUA booking (dari semua dokter)
4. Card menampilkan nama pasien DAN dokter
5. Filter, sorting, cetak semuanya
```

---

## 📊 Data yang Ditampilkan

### Pasien Dashboard:
- Nomor Antrian
- Nama Dokter
- Tanggal & Jam
- Keluhan
- Status
- Priority Badge (jika >= 3)

### Dokter Dashboard:
- Nomor Antrian
- **Nama Pasien** (dari tabel users)
- Tanggal & Jam
- Keluhan
- Status
- Priority Badge (jika >= 3)
- **Banner Dokter** (nama + spesialis)

### Admin Dashboard:
- Nomor Antrian
- **Nama Pasien** (dari tabel users)
- **Nama Dokter** (dari tabel dokter)
- Tanggal & Jam
- Keluhan
- Status
- Priority Badge (jika >= 3)

---

## 🔧 Troubleshooting

### Karcis tidak muncul?
**Cek:**
1. Ada data booking di database?
   ```sql
   SELECT COUNT(*) FROM booking;
   ```
2. Query JOIN benar? (users.name as nama_pasien)
3. Browser cache? (Ctrl+F5 untuk hard refresh)

### Modal tidak muncul saat klik?
**Cek:**
1. Bootstrap JS loaded?
2. `ticket_modal.php` included?
3. Console error di browser (F12)

### Filter tidak bekerja?
**Cek:**
1. JavaScript `filterTickets()` function
2. Data attributes: `data-date`, `data-priority`, `data-status`

---

## 🎉 Status Sistem

✅ **Pasien** - Riwayat Booking dengan karcis  
✅ **Dokter** - Menu "Lihat Karcis Antrian" di dashboard  
✅ **Admin** - Menu "Karcis Antrian" di dashboard  
✅ **Modal** - Printable ticket untuk semua role  
✅ **Sorting** - Priority DESC, Tanggal ASC  
✅ **Filter** - Tanggal, Priority, Status  

**SISTEM READY TO USE!** 🚀

---

**Created**: 2025-12-20  
**Version**: 1.1  
**Author**: RS Sehat Selalu Development Team
