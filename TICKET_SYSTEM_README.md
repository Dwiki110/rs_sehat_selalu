# 🎫 Sistem Karcis Antrian Digital - RS Sehat Selalu

## 📋 Overview

Sistem karcis antrian digital yang profesional dengan tampilan visual menarik untuk pasien, dokter, dan admin. Sistem ini menampilkan nomor antrian dalam format card modern dengan detail lengkap dan dapat dicetak.

---

## ✨ Fitur Utama

### 1. Tampilan Karcis Grid Card
- **Layout Responsif**: Grid 3 kolom (desktop), 2 kolom (tablet), 1 kolom (mobile)
- **Design Modern**: Card dengan gradient header dan border animasi hover
- **Nomor Antrian Besar**: Format QA-YYYYMMDD-XXX di header card
- **Badge Prioritas**: Visual indicator untuk pasien prioritas tinggi
- **Detail Lengkap**: Nama dokter/pasien, tanggal, jam, keluhan, status

### 2. Modal Karcis Printable
- **Design Profesional**: Mirip tiket/karcis fisik dengan branding RS
- **Logo & Header**: Header dengan gradient RS Sehat Selalu
- **QR Code Placeholder**: Untuk integrasi sistem scan di masa depan
- **Informasi Lengkap**: Detail pasien, dokter, jadwal, keluhan
- **Print-Friendly**: Tombol cetak dan download (PDF)
- **Auto-Popup**: Muncul otomatis setelah booking berhasil

### 3. Filter & Sorting
- **Filter Tanggal**: Hari ini, Besok, Minggu ini, Semua
- **Filter Prioritas**: Kritis, Darurat, Mendesak, Normal, Reguler
- **Filter Status**: Menunggu, Dikonfirmasi, Selesai
- **Sorting Otomatis**: Priority DESC, Tanggal DESC, Queue Number ASC

---

## 👥 Akses Per Role

### 🩺 Pasien
**File**: `pasien_riwayat.php`
- Melihat riwayat booking sendiri dalam format karcis
- Tombol "Lihat Karcis" di setiap booking
- Auto-popup karcis setelah booking berhasil
- Dapat mencetak karcis untuk dibawa ke RS

**URL**: `localhost/rs_sehat_selalu/pasien_riwayat.php`

### 👨‍⚕️ Dokter
**File**: `dokter_lihat_antrian.php`
- Melihat semua antrian pasien yang booking ke dokter tersebut
- Banner info dokter (nama + spesialis)
- Filter tanggal, prioritas, status
- Tombol "Lihat Karcis" di setiap pasien
- Warna theme: Red/Orange (#ff6b6b)

**URL**: `localhost/rs_sehat_selalu/dokter_lihat_antrian.php`

### 🛡️ Admin
**File**: `admin_lihat_antrian.php`
- Melihat SEMUA antrian dari semua dokter
- Menampilkan nama pasien DAN nama dokter
- Banner Administrator
- Filter lengkap untuk manajemen antrian
- Tombol "Lihat Karcis" di setiap antrian
- Warna theme: Purple (#667eea)

**URL**: `localhost/rs_sehat_selalu/admin_lihat_antrian.php`

---

## 🎨 Komponen Visual

### Priority Badge (Priority 3-5)
```
Priority 5 (KRITIS)    - 🔴 Red dengan animasi pulse
Priority 4 (DARURAT)   - 🟠 Orange
Priority 3 (MENDESAK)  - 🟡 Yellow
Priority 2 (NORMAL)    - Tidak ada badge
Priority 1 (REGULER)   - Tidak ada badge
```

### Status Badge
```
Menunggu      - 🟠 Orange gradient
Dikonfirmasi  - 🟢 Green gradient
Selesai       - 🟣 Purple gradient
Dibatalkan    - ⚫ Gray gradient
```

### Card Layout
```
┌─────────────────────────────────────┐
│  🏷️ PRIORITY BADGE (jika >= 3)     │
│  ┌─────────────────────────────┐   │
│  │    HEADER (Gradient)        │   │
│  │    Nomor Antrian            │   │
│  │    QA-20251220-001          │   │
│  │    (Large Font)             │   │
│  └─────────────────────────────┘   │
│                                     │
│  👤 Pasien: [Nama]                 │
│  👨‍⚕️ Dokter: [Nama]               │
│  📅 Tanggal: [DD MMM YYYY]         │
│  🕐 Jam: [HH:MM] WIB               │
│  🟢 Status: [Menunggu]             │
│                                     │
│  [Lihat Karcis Button]             │
│                                     │
│  ─────────────────────────────     │
│  💬 KELUHAN:                       │
│  [Deskripsi keluhan pasien...]     │
└─────────────────────────────────────┘
```

---

## 🖨️ Modal Karcis Printable

### Struktur Modal
```
┌──────────────────────────────────┐
│  [X Close]                       │
│  ┌────────────────────────────┐ │
│  │   🏥 RS SEHAT SELALU       │ │
│  │   Sistem Antrian Online    │ │
│  │                            │ │
│  │   Nomor Antrian Anda       │ │
│  │   QA-20251220-001          │ │
│  │   (3.5rem Font)            │ │
│  └────────────────────────────┘ │
│                                  │
│  👨‍⚕️ Dokter: Dr. Bambang        │
│                                  │
│  📅 20 Des 2024    🕐 10:00 WIB │
│                                  │
│  💬 Keluhan:                     │
│  Demam tinggi, sakit kepala      │
│                                  │
│  ─────────────────────────────   │
│         [QR CODE]                │
│                                  │
│  📌 Catatan Penting:             │
│  • Datang 15 menit sebelum       │
│  • Tunjukkan nomor antrian       │
│  • Simpan karcis ini             │
│  • Hubungi: (021) 1234-5678      │
│                                  │
│  [🖨️ Cetak Karcis]  [📥 Download]│
└──────────────────────────────────┘
```

### Fungsi JavaScript
```javascript
showTicketModal(ticketData)  // Tampilkan modal dengan data
printTicket()                 // Cetak karcis
downloadTicket()              // Download as PDF
```

---

## 📁 File Structure

```
rs_sehat_selalu/
├── pasien_riwayat.php           ✅ Halaman riwayat pasien dengan karcis
├── dokter_lihat_antrian.php     ✅ Halaman antrian dokter
├── admin_lihat_antrian.php      ✅ Halaman antrian admin
├── ticket_modal.php             ✅ Modal karcis universal
├── proses_booking.php           ✅ Updated dengan redirect data lengkap
└── TICKET_SYSTEM_README.md      📄 Dokumentasi ini
```

---

## 🔄 Flow Sistem

### 1. Booking Flow (Pasien)
```
Pasien Booking
    ↓
proses_booking.php (Generate queue number + priority)
    ↓
Redirect ke pasien_riwayat.php dengan params:
  - queue_number
  - doctor_name
  - date, day, time
  - complaint, priority
    ↓
Auto-popup modal karcis (500ms delay)
    ↓
Pasien dapat:
  - Lihat detail karcis
  - Cetak karcis
  - Download karcis
  - Tutup modal dan lihat grid card
```

### 2. View Flow (Dokter/Admin)
```
Dokter/Admin Login
    ↓
Akses halaman antrian
    ↓
Grid card dengan filter
    ↓
Klik "Lihat Karcis" pada card
    ↓
Modal popup dengan detail lengkap
    ↓
Dapat cetak/download
```

---

## 🎯 Data Format

### URL Parameters (Auto-popup)
```
?success=booking_created
&queue_number=QA-20251220-001
&doctor=dwiki
&doctor_name=Dr.%20Bambang%20Santoso
&date=2024-12-20
&day=Jumat
&time=10:00
&complaint=Demam%20tinggi
&priority=3
```

### JSON Data (showTicketModal)
```json
{
  "queue_number": "QA-20251220-001",
  "doctor": "dwiki",
  "doctor_name": "Dr. Bambang Santoso",
  "date": "2024-12-20",
  "day": "Jumat",
  "time": "10:00",
  "complaint": "Demam tinggi",
  "priority": 3,
  "patient_name": "John Doe"  // Untuk dokter/admin
}
```

---

## 🎨 Customization

### Warna Theme

**Pasien** (Purple):
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

**Dokter** (Red/Orange):
```css
background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
```

**Admin** (Purple):
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Priority Colors
```css
.priority-5 { background: linear-gradient(135deg, #ff0844 0%, #ff6b6b 100%); }
.priority-4 { background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%); }
.priority-3 { background: linear-gradient(135deg, #ffa726 0%, #ffd32a 100%); }
.priority-2 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.priority-1 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
```

---

## 📱 Responsive Design

### Desktop (>768px)
- Grid: 3 kolom
- Card width: minmax(350px, 1fr)
- Nomor antrian: 3rem

### Tablet (768px)
- Grid: 2 kolom
- Card width: auto
- Nomor antrian: 3rem

### Mobile (<768px)
- Grid: 1 kolom
- Card width: 100%
- Nomor antrian: 2.5rem
- Padding reduced

---

## 🖨️ Print Styling

### Media Query Print
```css
@media print {
    .modal-header,
    .ticket-print-buttons,
    .btn-close {
        display: none !important;
    }
    
    .modal-dialog {
        max-width: 100%;
        margin: 0;
    }
    
    .ticket-print-card {
        box-shadow: none;
        page-break-inside: avoid;
    }
}
```

---

## 🔧 Troubleshooting

### Modal tidak muncul setelah booking
**Cek:**
1. URL params ada dan benar
2. JavaScript `showTicketModal` loaded
3. Bootstrap JS loaded
4. Console error

### Karcis tidak bisa dicetak
**Cek:**
1. Print CSS loaded
2. Browser print permission
3. `window.print()` support

### Filter tidak bekerja
**Cek:**
1. Data attributes pada card: `data-date`, `data-priority`, `data-status`
2. JavaScript filterTickets() function
3. Select onChange event

### Card tidak responsif
**Cek:**
1. CSS grid template columns
2. Media queries loaded
3. Bootstrap breakpoints

---

## 🚀 Future Enhancements

### Planned Features
- [ ] Real QR Code generation (php-qrcode library)
- [ ] Email notification dengan karcis PDF attachment
- [ ] SMS reminder H-1 dengan link karcis
- [ ] Export karcis sebagai image (html2canvas)
- [ ] Barcode scanning di receptionist desk
- [ ] Real-time queue position update
- [ ] Estimated waiting time calculation
- [ ] Digital signage integration

### Optional Improvements
- [ ] Animation saat card hover
- [ ] Loading state saat print
- [ ] Toast notification setelah print
- [ ] Preview modal sebelum print
- [ ] Multiple ticket print (batch)
- [ ] WhatsApp sharing integration

---

## 📊 Database Dependencies

### Tables
- `booking` - Data booking dengan queue_number, priority
- `pasien` - Data pasien untuk nama lengkap
- `dokter` - Data dokter untuk nama lengkap

### Columns Required
```sql
booking:
  - queue_number VARCHAR(20)
  - priority TINYINT(1-5)
  - status ENUM('waiting','confirmed','completed','cancelled')
  - tanggal DATE
  - jam TIME
  - keluhan TEXT
```

---

## 📝 Testing Checklist

### Pasien
- [ ] Booking berhasil dan modal muncul
- [ ] Karcis dapat dicetak
- [ ] Grid card menampilkan semua booking
- [ ] Filter dan sorting bekerja
- [ ] Responsive di mobile

### Dokter
- [ ] Melihat semua antrian pasien sendiri
- [ ] Filter tanggal/prioritas/status
- [ ] Karcis menampilkan info lengkap
- [ ] Warna theme merah/orange
- [ ] Banner dokter tampil

### Admin
- [ ] Melihat SEMUA antrian
- [ ] Tampil nama pasien DAN dokter
- [ ] Filter berfungsi semua
- [ ] Warna theme purple
- [ ] Banner admin tampil

---

## 🎉 Kesimpulan

Sistem karcis antrian digital sudah siap digunakan dengan fitur:

✅ **3 Role Support**: Pasien, Dokter, Admin
✅ **Professional Design**: Modern card layout dengan gradients
✅ **Printable Ticket**: Modal dengan print/download support
✅ **Filtering**: Multiple filter untuk manajemen antrian
✅ **Priority System**: Visual indicators untuk pasien prioritas
✅ **Responsive**: Mobile-friendly grid layout
✅ **Auto-popup**: Karcis muncul otomatis setelah booking

**Status**: ✅ PRODUCTION READY

---

**Created**: 2025-12-20  
**Version**: 1.0  
**Author**: RS Sehat Selalu Development Team
