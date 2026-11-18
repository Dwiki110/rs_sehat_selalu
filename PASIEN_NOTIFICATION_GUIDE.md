# 🔔 Panduan Notifikasi Status Antrian untuk Pasien

## 📋 Overview
Pasien sekarang mendapatkan **notifikasi visual real-time** di halaman riwayat booking mereka setiap kali admin mengubah status antrian.

---

## 🎨 Jenis Notifikasi

### 1️⃣ Status: MENUNGGU (Waiting)
**Notifikasi Kuning:**
```
⏳ Menunggu Konfirmasi
Mohon tunggu, antrian Anda sedang dalam proses verifikasi.
```

**Visual:**
- Background: Gradient kuning (#fff3cd → #ffeaa7)
- Icon: ⏳ Hourglass (animasi bounce)
- Border kiri: Kuning (#ffc107)
- Shadow: Subtle kuning

**Artinya:**
- Booking berhasil dibuat
- Menunggu admin untuk konfirmasi/panggil
- Pasien harus terus pantau halaman ini

---

### 2️⃣ Status: DIKONFIRMASI (Confirmed)
**Notifikasi Hijau (PENTING!):**
```
📞 Anda Dipanggil!
Segera datang ke ruang pemeriksaan dokter.
```

**Visual:**
- Background: Gradient hijau (#d4edda → #c3e6cb)
- Icon: 📞 Telephone (animasi bounce)
- Border kiri: Hijau (#28a745)
- Shadow: Hijau terang
- **Font bolder** untuk menarik perhatian

**Artinya:**
- ✅ Admin sudah memanggil pasien
- ✅ Pasien HARUS SEGERA datang ke ruang pemeriksaan
- ✅ Ini adalah notifikasi paling penting!

**Action Required:**
- Segera ke ruang tunggu dokter
- Bawa karcis antrian (print jika perlu)
- Tunjukkan nomor antrian ke petugas

---

### 3️⃣ Status: DIBATALKAN (Cancelled)
**Notifikasi Merah:**
```
❌ Antrian Ditolak
Mohon maaf, antrian Anda ditolak oleh admin. 
Silakan hubungi admin untuk informasi lebih lanjut.
```

**Visual:**
- Background: Gradient merah (#f8d7da → #f5c6cb)
- Icon: ❌ X-Circle (animasi bounce)
- Border kiri: Merah (#dc3545)
- Shadow: Merah terang

**Artinya:**
- ❌ Admin menolak antrian
- ❌ Pemeriksaan tidak bisa dilanjutkan

**Action Required:**
- Hubungi admin RS untuk penjelasan
- Mungkin ada konflik jadwal dokter
- Mungkin data booking tidak valid
- Buat booking baru jika diperlukan

---

### 4️⃣ Status: SELESAI (Completed)
**Notifikasi Biru:**
```
✅ Pemeriksaan Selesai
Terima kasih telah menggunakan layanan kami.
```

**Visual:**
- Background: Gradient biru (#d1ecf1 → #bee5eb)
- Icon: ✅ Check-Circle (animasi bounce)
- Border kiri: Biru (#17a2b8)
- Shadow: Biru terang

**Artinya:**
- ✅ Pemeriksaan sudah selesai dilakukan
- ✅ Transaksi complete
- Pasien bisa pulang

**Note:**
- Ini status final
- Tidak ada action required
- Hanya untuk informasi riwayat

---

## 📱 Tampilan di Halaman Pasien

### Struktur Card dengan Notifikasi:
```
┌─────────────────────────────────────┐
│  [Priority Badge]                   │
│  ┌───────────────────────────────┐  │
│  │     NOMOR ANTRIAN             │  │
│  │     QA-20251224-019           │  │
│  └───────────────────────────────┘  │
│                                     │
│  👨‍⚕️ Dokter: dokter wahyu           │
│  📅 24 Des 2025                     │
│  ⏰ 10:00 WIB                        │
│                                     │
│  ⭕ Status: Dikonfirmasi            │
│                                     │
│  ┌───────────────────────────────┐ │
│  │ 📞 ANDA DIPANGGIL!            │ │ ← NOTIFIKASI
│  │ Segera datang ke ruang        │ │
│  │ pemeriksaan dokter.           │ │
│  └───────────────────────────────┘ │
│                                     │
│  [Lihat Karcis]                    │
│                                     │
│  💬 KELUHAN: sakit parah           │
└─────────────────────────────────────┘
```

---

## 🎯 Cara Kerja Notifikasi

### Flow Notifikasi:

1. **Pasien membuat booking**
   ```
   Status: WAITING
   Notifikasi: ⏳ Menunggu Konfirmasi
   ```

2. **Admin memanggil pasien**
   ```
   Status: WAITING → CONFIRMED
   Notifikasi berubah: 📞 Anda Dipanggil!
   ```

3. **Pasien refresh halaman**
   ```
   Notifikasi baru muncul dengan animasi slide-in
   Icon beranimasi bounce untuk menarik perhatian
   ```

4. **Pasien datang ke ruang pemeriksaan**
   ```
   Dokter memeriksa pasien
   Admin update status: CONFIRMED → COMPLETED
   ```

5. **Pasien cek lagi setelah selesai**
   ```
   Status: COMPLETED
   Notifikasi: ✅ Pemeriksaan Selesai
   ```

---

## 🔄 Auto-Refresh Recommendation

**PENTING:** Notifikasi **tidak real-time otomatis**. Pasien harus:
- **Refresh halaman** secara berkala (Ctrl+R atau F5)
- Atau **klik "Riwayat Booking"** di dashboard untuk reload

**Rekomendasi untuk pasien:**
- Refresh setiap 2-3 menit jika menunggu konfirmasi
- Atau tunggu di ruang tunggu RS dan pantau layar display (jika ada)

**Future Enhancement Ideas:**
- Auto-refresh setiap 30 detik dengan JavaScript
- WebSocket/Server-Sent Events untuk real-time push
- Email/SMS notification saat status berubah
- Push notification via Progressive Web App (PWA)

---

## 🎨 Visual Design Details

### Animasi:

**1. Slide-In Animation (Notification Box)**
```css
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
```
- Notifikasi masuk dari kiri
- Duration: 0.5 detik
- Smooth ease timing

**2. Bounce Animation (Icon)**
```css
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}
```
- Icon naik-turun
- Duration: 1 detik
- Infinite loop
- Menarik perhatian pasien

### Color Coding:

| Status | Color Theme | Emotion | Priority |
|--------|-------------|---------|----------|
| **Waiting** | Kuning | Netral, Informatif | Low |
| **Confirmed** | Hijau | Urgent, Action! | **HIGH** |
| **Cancelled** | Merah | Warning, Error | Medium |
| **Completed** | Biru | Success, Done | Low |

---

## 📊 User Experience Flow

### Scenario 1: Pasien Dipanggil

**Timeline:**
```
09:00 - Pasien booking untuk jam 10:00
        Status: WAITING
        Notifikasi: ⏳ Menunggu Konfirmasi

09:30 - Admin cek antrian dan panggil pasien
        (Admin klik tombol "Panggil")

09:31 - Pasien refresh halaman riwayat
        Status berubah: CONFIRMED ✅
        Notifikasi berubah: 📞 ANDA DIPANGGIL!
        (Animasi slide-in + icon bounce)

09:35 - Pasien datang ke ruang pemeriksaan
        Tunjukkan karcis ke petugas

10:00 - Pemeriksaan dimulai

10:30 - Pemeriksaan selesai
        Admin update: COMPLETED

10:35 - Pasien cek lagi
        Notifikasi: ✅ Pemeriksaan Selesai
```

---

### Scenario 2: Antrian Ditolak

**Timeline:**
```
14:00 - Pasien booking untuk dokter A
        Status: WAITING

14:15 - Admin cek dan lihat konflik jadwal
        Admin klik "Tolak"

14:20 - Pasien refresh halaman
        Status: CANCELLED ❌
        Notifikasi: ❌ Antrian Ditolak
        
14:25 - Pasien hubungi admin via telepon
        Admin jelaskan alasan penolakan
        
14:30 - Pasien booking ulang dengan jadwal baru
```

---

## 🔐 Security & Privacy

### Data Protection:
- ✅ Notifikasi hanya tampil untuk pasien yang login
- ✅ Session validation (`$_SESSION['role'] == 'pasien'`)
- ✅ Hanya menampilkan data booking milik pasien tersebut
- ✅ XSS prevention dengan `htmlspecialchars()`

### Query Filter:
```php
WHERE b.pasien = '$pasien'
```
Pasien hanya bisa lihat booking mereka sendiri.

---

## 🧪 Testing Checklist

### Test Case 1: Notifikasi Waiting
- [x] Login sebagai pasien
- [x] Lihat card dengan status "Menunggu"
- [x] Verifikasi notifikasi kuning muncul
- [x] Verifikasi text: "⏳ Menunggu Konfirmasi"
- [x] Verifikasi icon hourglass beranimasi

### Test Case 2: Notifikasi Confirmed
- [x] Admin panggil pasien (status → confirmed)
- [x] Pasien refresh halaman
- [x] Verifikasi notifikasi hijau muncul
- [x] Verifikasi text: "📞 Anda Dipanggil!"
- [x] Verifikasi animasi slide-in
- [x] Verifikasi icon telephone bounce

### Test Case 3: Notifikasi Cancelled
- [x] Admin tolak antrian (status → cancelled)
- [x] Pasien refresh halaman
- [x] Verifikasi notifikasi merah muncul
- [x] Verifikasi text: "❌ Antrian Ditolak"
- [x] Verifikasi pesan instruksi hubungi admin

### Test Case 4: Notifikasi Completed
- [x] Admin selesaikan pemeriksaan
- [x] Pasien refresh halaman
- [x] Verifikasi notifikasi biru muncul
- [x] Verifikasi text: "✅ Pemeriksaan Selesai"
- [x] Verifikasi ucapan terima kasih

### Test Case 5: Multiple Cards
- [x] Pasien punya 3 booking dengan status berbeda
- [x] Setiap card tampil notifikasi sesuai statusnya
- [x] Tidak ada interferensi antar notifikasi
- [x] Responsive di mobile

---

## 📱 Responsive Design

### Desktop (>768px):
- Grid 3 kolom
- Notifikasi full width dalam card
- Font size normal

### Tablet (768px):
- Grid 2 kolom
- Notifikasi adjust width
- Font size sama

### Mobile (<768px):
- Grid 1 kolom (full width)
- Notifikasi stack vertikal
- Icon sedikit lebih kecil
- Text masih readable

---

## 🆘 Troubleshooting

### Problem: Notifikasi tidak muncul
**Solution:**
- Pastikan sudah login sebagai pasien
- Refresh halaman (Ctrl+F5)
- Cek status booking di database
- Clear browser cache

### Problem: Animasi tidak jalan
**Solution:**
- Pastikan CSS loaded (cek Network tab)
- Cek browser support untuk animations
- Update browser ke versi terbaru

### Problem: Warna notifikasi salah
**Solution:**
- Cek value status di database
- Pastikan: 'waiting', 'confirmed', 'cancelled', 'completed' (lowercase)
- Tidak ada typo di status value

### Problem: Icon tidak muncul
**Solution:**
- Pastikan Bootstrap Icons CDN loaded
- Cek internet connection
- View page source, cek CDN link aktif

---

## 🚀 Future Enhancements

### Priority 1 (High):
1. **Auto-refresh every 30 seconds**
   ```javascript
   setInterval(() => {
       location.reload();
   }, 30000);
   ```

2. **Sound notification untuk status Confirmed**
   - Play alert sound saat status confirmed
   - Gunakan Web Audio API

3. **Browser notification (Push API)**
   - Minta permission dari user
   - Push notification saat status berubah

### Priority 2 (Medium):
4. **SMS/Email notification**
   - Integrate dengan SMS gateway
   - Email otomatis saat status berubah

5. **WhatsApp notification**
   - Integrate WhatsApp Business API
   - Template message untuk setiap status

### Priority 3 (Low):
6. **Real-time dengan WebSocket**
   - Socket.io server
   - Instant update tanpa refresh

7. **Progressive Web App (PWA)**
   - Install app di smartphone
   - Offline support
   - Push notification native

---

## 📞 Support

Jika pasien bingung dengan notifikasi:
1. Baca dokumentasi ini
2. Tanya ke admin RS
3. Hubungi IT Support

**Instruksi untuk Petugas:**
- Jelaskan arti setiap warna notifikasi
- Minta pasien refresh halaman jika belum update
- Pastikan pasien tahu harus segera datang saat notifikasi hijau

---

## ✅ Summary

### Fitur Lengkap:
- ✅ 4 jenis notifikasi sesuai status
- ✅ Color coding untuk quick recognition
- ✅ Animated icons untuk menarik perhatian
- ✅ Slide-in animation untuk smoothness
- ✅ Responsive design (desktop/tablet/mobile)
- ✅ Clear action instructions
- ✅ Security (session & XSS prevention)

### Status Ready:
**PRODUCTION READY** ✅

Notifikasi siap digunakan dan membantu komunikasi antara admin dan pasien!

---

**Dokumen Dibuat:** 2025-12-20  
**Versi:** 1.0  
**Author:** AI Development Team  
**Status:** ✅ COMPLETE
