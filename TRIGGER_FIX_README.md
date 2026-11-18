# 🔧 Trigger Conflict Fix

## ❌ Problem
**Error:** `Can't update table 'booking' in stored function/trigger because it is already used by statement which invoked this stored function/trigger`

**Location:** `proses_booking.php:102`

**Cause:** The trigger `before_booking_insert` was trying to UPDATE the `booking` table during an INSERT operation, which MySQL does not allow.

---

## ✅ Solution Applied

### Dropped Problematic Trigger
```sql
DROP TRIGGER IF EXISTS before_booking_insert;
```

**Reason:** This trigger was originally designed to:
1. Auto-detect priority from keluhan keywords
2. Auto-generate queue numbers
3. UPDATE the newly inserted row

However, MySQL/MariaDB does not allow modifying the same table within a BEFORE/AFTER trigger that's being modified.

---

## 🏗️ Current Architecture

### PHP Handles Everything (Recommended)
All logic has been moved to PHP in `proses_booking.php`:

**Line 56-81:** `determinePriority()` function
- Detects keywords: parah, kejang, sesak, tinggi, etc.
- Returns priority level 1-5

**Line 86-93:** `QueueNumberGenerator` class
- Generates format: QA-YYYYMMDD-XXX
- Thread-safe with transaction locking
- Auto-increments daily counter

**Line 96-100:** INSERT with values
```php
INSERT INTO booking (pasien, dokter, tanggal, hari, jam, keluhan, queue_number, priority, status) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'waiting')
```
All values including `queue_number` and `priority` are calculated BEFORE insert.

---

## 🔄 Remaining Triggers (Safe)

### 1. `after_booking_insert`
**Type:** AFTER INSERT  
**Purpose:** Create patient notification  
**Safe because:** Only INSERTs into `notifications` table, not `booking`

```sql
CREATE TRIGGER after_booking_insert
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    IF NEW.priority >= 3 THEN
        INSERT INTO notifications (...) VALUES (...);
    END IF;
END
```

### 2. `after_booking_insert_doctor_notif`
**Type:** AFTER INSERT  
**Purpose:** Create doctor notification for emergency cases  
**Safe because:** Only INSERTs into `doctor_notifications` table, not `booking`

```sql
CREATE TRIGGER after_booking_insert_doctor_notif
AFTER INSERT ON booking
FOR EACH ROW
BEGIN
    IF NEW.priority >= 4 THEN
        INSERT INTO doctor_notifications (...) VALUES (...);
    END IF;
END
```

---

## 🧪 Verification

### Check Active Triggers
```sql
SELECT TRIGGER_NAME, EVENT_MANIPULATION, ACTION_TIMING
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'rs_sehat_selalu' 
AND EVENT_OBJECT_TABLE = 'booking';
```

**Expected Output:**
| TRIGGER_NAME | EVENT_MANIPULATION | ACTION_TIMING |
|--------------|-------------------|---------------|
| after_booking_insert | INSERT | AFTER |
| after_booking_insert_doctor_notif | INSERT | AFTER |

### Test Booking
1. Login as patient
2. Create booking with keluhan: "sesak nafas parah"
3. Expected:
   - ✅ Priority = 5 (auto-detected)
   - ✅ Queue number = QA-20251220-XXX
   - ✅ No trigger error
   - ✅ Notification created automatically

---

## 📊 Comparison: Before vs After

### ❌ BEFORE (Problematic)
```
1. PHP: INSERT booking (queue_number=NULL, priority=NULL)
2. Trigger: UPDATE booking SET priority=5, queue_number='QA-...' ⚠️ ERROR!
3. MySQL: Cannot update same table during trigger
```

### ✅ AFTER (Fixed)
```
1. PHP: Calculate priority = 5
2. PHP: Generate queue_number = 'QA-20251220-001'
3. PHP: INSERT booking (queue_number='QA-...', priority=5) ✅
4. Trigger: INSERT INTO notifications ✅
5. Trigger: INSERT INTO doctor_notifications ✅
```

---

## 🎯 Benefits of Current Approach

1. **No Trigger Conflicts** - PHP calculates before INSERT
2. **Better Error Handling** - Can validate in PHP before database
3. **Easier Debugging** - Logic visible in PHP code
4. **More Flexible** - Easy to modify without ALTER TRIGGER
5. **Transaction Control** - PHP can rollback if queue generation fails

---

## 📝 Files Modified

### Database
- ✅ `fix_trigger_conflict.sql` - Drops problematic trigger

### PHP (No Changes Needed)
- ✅ `proses_booking.php` - Already handles priority & queue
- ✅ `QueueNumberGenerator.php` - Already thread-safe
- ✅ `NotificationManager.php` - Already creates notifications

---

## 🚀 Status

**Error Status:** ✅ FIXED  
**System Status:** ✅ READY FOR TESTING  
**Remaining Triggers:** ✅ 2 AFTER triggers (safe)

You can now test the booking system without trigger conflicts!

---

**Fixed By:** AI Assistant  
**Date:** 2025-12-20  
**Issue:** Trigger conflict on booking table UPDATE
