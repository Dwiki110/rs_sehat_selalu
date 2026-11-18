# 🚀 Quick Guide: Admin Kelola Status Antrian

## 📍 Cara Akses
1. Login sebagai **admin**
2. Dashboard → Klik **"Karcis Antrian"** (card ungu)
3. URL: `admin_lihat_antrian.php`

---

## 🎯 Tombol Aksi Cepat

### Status: MENUNGGU
```
[📞 PANGGIL]  [❌ TOLAK]
```
- **Panggil** → Status jadi "Dikonfirmasi"
- **Tolak** → Status jadi "Dibatalkan"

### Status: DIKONFIRMASI  
```
[✔️ SELESAI]  [❌ BATAL]
```
- **Selesai** → Status jadi "Selesai"
- **Batal** → Status jadi "Dibatalkan"

### Status: DIBATALKAN
```
[🔄 KEMBALIKAN]
```
- **Kembalikan** → Status jadi "Menunggu"

### Status: SELESAI
```
✅ Antrian Selesai
```
- Tidak ada tombol (final)

---

## 🔔 Notifikasi

### ✅ Success (Hijau)
- Pasien berhasil dipanggil!
- Antrian pasien berhasil ditolak.
- Antrian berhasil diselesaikan.
- Status dikembalikan ke Menunggu.

### ❌ Error (Merah)
- ID booking tidak valid.
- Status tidak valid.
- Gagal memperbarui status.

**Auto-hide setelah 5 detik**

---

## 🎨 Filter Antrian

**Tanggal:**
- Semua Tanggal
- Hari Ini
- Besok
- Minggu Ini

**Prioritas:**
- Semua / 5 (Kritis) / 4 (Darurat) / 3 (Mendesak) / 2 / 1

**Status:**
- Semua / Menunggu / Dikonfirmasi / Selesai

---

## ⚠️ Konfirmasi Dialog

Setiap klik tombol akan tampil konfirmasi:
```
Apakah Anda yakin ingin [AKSI] antrian [NOMOR]?
```

Klik **OK** untuk proses, **CANCEL** untuk batal.

---

## 📊 Flow Status

```
WAITING → PANGGIL → CONFIRMED → SELESAI → COMPLETED
   ↓                     ↓
 TOLAK               BATAL
   ↓                     ↓
CANCELLED ← ─ ─ ─ ─ ─ ─ ┘
   ↓
KEMBALIKAN → WAITING
```

---

## 🔐 Security

✅ Hanya admin yang bisa akses  
✅ Konfirmasi setiap aksi  
✅ Input validation  
✅ SQL injection protected  
✅ XSS prevention  

---

## 🧪 Quick Test

1. **Test Panggil:**
   - Cari card "Menunggu" → Klik "Panggil" → Konfirmasi
   - ✅ Notif hijau + status "Dikonfirmasi"

2. **Test Tolak:**
   - Cari card "Menunggu" → Klik "Tolak" → Konfirmasi  
   - ✅ Notif hijau + status "Dibatalkan"

3. **Test Selesai:**
   - Cari card "Dikonfirmasi" → Klik "Selesai" → Konfirmasi
   - ✅ Notif hijau + status "Selesai"

4. **Test Kembalikan:**
   - Cari card "Dibatalkan" → Klik "Kembalikan" → Konfirmasi
   - ✅ Notif hijau + status "Menunggu"

---

## 📱 Responsif

✅ Desktop: 3 kolom  
✅ Tablet: 2 kolom  
✅ Mobile: 1 kolom  

Tombol otomatis adjust di semua ukuran layar.

---

## 🎯 Priority Pasien

**Urutan otomatis:**
1. Priority 5 (KRITIS) - Badge merah, animasi pulse
2. Priority 4 (DARURAT) - Badge orange
3. Priority 3 (MENDESAK) - Badge kuning
4. Priority 2 (NORMAL) - Tanpa badge
5. Priority 1 (REGULER) - Tanpa badge

**Admin harus prioritaskan pasien kritis dulu!**

---

## 🆘 Troubleshooting Cepat

**Tombol tidak muncul?**
→ Refresh (Ctrl+F5)

**Notifikasi tidak muncul?**
→ Cek browser console (F12)

**Status tidak berubah?**
→ Cek koneksi database

**Konfirmasi tidak muncul?**
→ Enable JavaScript di browser

---

## 📞 Contact

Butuh bantuan teknis?  
Hubungi IT Support RS Sehat Selalu

---

**Version:** 1.0  
**Last Updated:** 2025-12-20  
**Status:** ✅ ACTIVE
