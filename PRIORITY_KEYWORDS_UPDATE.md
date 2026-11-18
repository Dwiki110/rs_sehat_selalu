# 🚨 Update Keywords Prioritas Tinggi

## 📋 Deskripsi
Update untuk menambahkan keyword baru yang otomatis trigger prioritas tinggi dan notifikasi ke dokter & admin.

## ✅ Keyword Baru yang Ditambahkan

### Priority 5 (KRITIS) - Tambahan:
- ✅ **parah**
- ✅ **kondisi parah**  
- ✅ **sangat parah**
- ✅ **kejang** (sudah ada sebelumnya, tetap dipertahankan)

**Contoh keluhan yang akan trigger Priority 5:**
- "Pasien dalam kondisi parah"
- "Sangat parah kejang terus menerus"
- "Kejang berkali-kali"
- "Pendarahan hebat kondisi parah"

### Priority 4 (DARURAT) - Tambahan:
- ✅ **sesak** (standalone, tidak harus "sesak nafas")
- ✅ **tinggi** (standalone, bisa "demam tinggi", "tekanan tinggi")
- ✅ **demam parah**
- ✅ **sakit parah**
- ✅ **kondisi tinggi**

**Contoh keluhan yang akan trigger Priority 4:**
- "Sesak nafas"
- "Demam tinggi"
- "Sakit parah"
- "Kondisi tinggi butuh penanganan"

## 🔄 File yang Diupdate

### 1. `proses_booking.php`
Function `determinePriority()` diupdate dengan keyword baru.

**Sebelum:**
```php
// Critical - Level 5
if (preg_match('/(henti jantung|tidak sadar|pingsan|kejang|sesak nafas berat|pendarahan hebat)/i', $keluhan)) {
    return 5;
}

// Emergency - Level 4
if (preg_match('/(nyeri dada|sesak nafas|demam tinggi|muntah darah|kecelakaan)/i', $keluhan)) {
    return 4;
}
```

**Sesudah:**
```php
// Critical - Level 5
if (preg_match('/(henti jantung|tidak sadar|pingsan|kejang|sesak nafas berat|pendarahan hebat|parah|kondisi parah|sangat parah)/i', $keluhan)) {
    return 5;
}

// Emergency - Level 4
if (preg_match('/(nyeri dada|sesak nafas|sesak|demam tinggi|tinggi|muntah darah|kecelakaan|demam parah|sakit parah|kondisi tinggi)/i', $keluhan)) {
    return 4;
}
```

### 2. `priority_queue_dashboard.php`
Function `determinePriority()` diupdate dengan pola yang sama.

### 3. Database Trigger `before_booking_insert`
SQL trigger diupdate dengan REGEXP pattern baru.

## 🚀 Cara Install Update

### Option 1: Via SQL Script
```bash
# PowerShell
Get-Content update_priority_keywords.sql | C:\xampp\mysql\bin\mysql.exe -u root rs_sehat_selalu
```

### Option 2: Manual Update
File PHP sudah diupdate otomatis. Untuk database:
```sql
-- Drop old trigger
DROP TRIGGER IF EXISTS before_booking_insert;

-- Create new trigger with updated keywords
-- (See update_priority_keywords.sql for full trigger code)
```

## 🧪 Testing

### Test Priority 5 (Kritis)
```sql
-- Via web interface (recommended)
-- 1. Login sebagai pasien
-- 2. Booking dengan keluhan: "kondisi sangat parah kejang"
-- 3. Verify priority = 5 di database
-- 4. Check notifikasi dokter dan admin

-- Manual test via PHP
-- Browse to pasien_booking.php dan submit dengan keluhan mengandung "parah"
```

### Test Priority 4 (Darurat)
```sql
-- Via web interface
-- 1. Login sebagai pasien
-- 2. Booking dengan keluhan: "sesak nafas tinggi"
-- 3. Verify priority = 4
-- 4. Check notifikasi dokter
```

### Verifikasi Notifikasi
```sql
-- Check doctor notification
SELECT * FROM doctor_notifications 
WHERE doctor_username = 'dwiki' 
ORDER BY created_at DESC LIMIT 5;

-- Check patient notification
SELECT * FROM notifications 
WHERE patient_username = 'pasien1' 
ORDER BY created_at DESC LIMIT 5;

-- Check admin notification (untuk rawat inap)
SELECT * FROM admin_notifications 
ORDER BY created_at DESC LIMIT 5;
```

## 📊 Priority Matrix (Updated)

| Priority | Level | Keywords (Contoh) | Auto-Notification | SLA Response |
|----------|-------|-------------------|-------------------|--------------|
| 5 | Kritis | parah, kejang, sangat parah, henti jantung, tidak sadar, pingsan | ✅ Dokter + Pasien | Segera (< 5 menit) |
| 4 | Darurat | sesak, tinggi, demam tinggi, nyeri dada, kecelakaan | ✅ Dokter + Pasien | < 30 menit |
| 3 | Mendesak | demam, diare, nyeri, luka | ✅ Pasien | < 2 jam |
| 2 | Semi Mendesak | batuk, pilek, flu | ❌ | < 4 jam |
| 1 | Tidak Mendesak | konsultasi, check up | ❌ | < 24 jam |

## ⚡ Automatic Actions

### Ketika Priority 5 (Kritis):
1. ✅ Auto-set priority = 5 di database
2. ✅ Generate queue number dengan format QA-YYYYMMDD-XXX
3. ✅ Notifikasi otomatis ke **pasien** dengan message: "🚨 KRITIS - Anda akan SEGERA ditangani!"
4. ✅ Notifikasi otomatis ke **dokter** yang dituju
5. ✅ Highlight merah di dashboard priority queue
6. ✅ Sort paling atas di antrian

### Ketika Priority 4 (Darurat):
1. ✅ Auto-set priority = 4 di database
2. ✅ Generate queue number
3. ✅ Notifikasi otomatis ke **pasien** dengan message: "⚠️ DARURAT - Anda akan dipanggil dalam 30 menit"
4. ✅ Notifikasi otomatis ke **dokter** yang dituju
5. ✅ Highlight orange di dashboard priority queue

## 🎯 Use Cases

### Scenario 1: Pasien Kejang
**Input:**
- Keluhan: "Anak saya kejang terus menerus sudah 5 menit"

**Auto-Processing:**
- Priority: 5 (KRITIS)
- Queue: QA-20251220-001
- Notif Pasien: "🚨 KRITIS - Nomor antrian QA-20251220-001. Anda akan SEGERA ditangani!"
- Notif Dokter: "🚨 KRITIS - Pasien [nama] (QA-20251220-001) memerlukan penanganan SEGERA! Keluhan: kejang..."

### Scenario 2: Kondisi Parah
**Input:**
- Keluhan: "Kondisi parah setelah kecelakaan"

**Auto-Processing:**
- Priority: 5 (KRITIS)
- Queue: QA-20251220-002
- Notif ke dokter dan pasien otomatis
- Dokter dapat langsung request rawat inap

### Scenario 3: Sesak Nafas
**Input:**
- Keluhan: "Sesak nafas susah bernafas"

**Auto-Processing:**
- Priority: 4 (DARURAT)
- Queue: QA-20251220-003
- Notif ke dokter dan pasien

### Scenario 4: Demam Tinggi
**Input:**
- Keluhan: "Demam tinggi 40 derajat"

**Auto-Processing:**
- Priority: 4 (DARURAT)
- Queue: QA-20251220-004
- Fast-track di antrian

## 🔍 Monitoring Keywords

### Untuk Admin/Management
Query untuk melihat distribusi keyword usage:

```sql
-- Keywords yang sering muncul di Priority 5
SELECT 
    CASE 
        WHEN LOWER(keluhan) LIKE '%parah%' THEN 'parah'
        WHEN LOWER(keluhan) LIKE '%kejang%' THEN 'kejang'
        WHEN LOWER(keluhan) LIKE '%pingsan%' THEN 'pingsan'
        WHEN LOWER(keluhan) LIKE '%tidak sadar%' THEN 'tidak sadar'
        ELSE 'other'
    END AS keyword,
    COUNT(*) as count
FROM booking 
WHERE priority = 5 
GROUP BY keyword
ORDER BY count DESC;

-- Keywords yang sering muncul di Priority 4
SELECT 
    CASE 
        WHEN LOWER(keluhan) LIKE '%sesak%' THEN 'sesak'
        WHEN LOWER(keluhan) LIKE '%tinggi%' THEN 'tinggi'
        WHEN LOWER(keluhan) LIKE '%nyeri dada%' THEN 'nyeri dada'
        ELSE 'other'
    END AS keyword,
    COUNT(*) as count
FROM booking 
WHERE priority = 4 
GROUP BY keyword
ORDER BY count DESC;
```

## ⚠️ Important Notes

### 1. Case Insensitive
Semua keyword detection adalah **case insensitive**. 
- "Parah" = "parah" = "PARAH" = "PaRaH"

### 2. Partial Match
Keyword detection menggunakan **partial match**.
- "sangat parah" → match "parah"
- "sesak nafas" → match "sesak"
- "demam tinggi sekali" → match "tinggi"

### 3. Priority via PHP
**PENTING:** Detection prioritas dilakukan di **PHP** (`proses_booking.php`), bukan di SQL trigger, karena MySQL trigger limitation.

SQL trigger hanya untuk fallback jika PHP tidak set priority.

### 4. Multiple Keywords
Jika keluhan mengandung multiple keywords, akan diambil **priority tertinggi**.

**Contoh:**
- Keluhan: "sesak nafas parah kejang"
- Match: "sesak" (P4), "parah" (P5), "kejang" (P5)
- Result: Priority 5 (tertinggi)

## 📝 Changelog

### Version 1.1 (2025-12-20)
- ✅ Added keyword "parah", "kondisi parah", "sangat parah" untuk Priority 5
- ✅ Added keyword "sesak" (standalone) untuk Priority 4
- ✅ Added keyword "tinggi" (standalone) untuk Priority 4  
- ✅ Added keyword "demam parah", "sakit parah", "kondisi tinggi" untuk Priority 4
- ✅ Updated `proses_booking.php`
- ✅ Updated `priority_queue_dashboard.php`
- ✅ Updated SQL trigger `before_booking_insert`
- ✅ Auto-notification tetap berjalan untuk Priority 4-5

## 🤝 Support

Untuk pertanyaan atau issue:
- GitHub Issues: https://github.com/whympxx/PendaftaranPasienRS/issues
- Email: support@rs-sehat-selalu.com

---

**Developed with ❤️ by RS Sehat Selalu Team**
