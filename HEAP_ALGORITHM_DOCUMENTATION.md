# Dokumentasi Algoritma Heap
## Sistem Antrian Prioritas Pasien RS Sehat Selalu

---

## Daftar Isi
1. [Overview](#overview)
2. [Konsep Max Heap](#konsep-max-heap)
3. [Implementasi](#implementasi)
4. [Kompleksitas](#kompleksitas)
5. [Use Case](#use-case)
6. [Testing](#testing)

---

## Overview

### Apa itu Priority Queue?
Priority Queue adalah struktur data abstrak di mana setiap elemen memiliki "prioritas" yang menentukan urutan pengeluarannya. Elemen dengan prioritas tertinggi akan dilayani terlebih dahulu.

### Mengapa Menggunakan Heap?
**Max Heap** adalah pilihan ideal untuk implementasi Priority Queue karena:
- ✅ **Efisien**: O(log n) untuk insert dan extract
- ✅ **Optimal**: O(1) untuk akses elemen tertinggi
- ✅ **Fleksibel**: Mudah update prioritas
- ✅ **Memory Efficient**: Representasi array yang compact

### Aplikasi dalam RS Sehat Selalu
Sistem antrian prioritas pasien menggunakan Max Heap untuk:
1. **Mengurutkan pasien** berdasarkan tingkat kegawatan
2. **Akses cepat** ke pasien prioritas tertinggi
3. **Update dinamis** saat kondisi pasien berubah
4. **Fairness** dengan FIFO untuk prioritas sama

---

## Konsep Max Heap

### Definisi
Max Heap adalah Complete Binary Tree di mana nilai setiap node lebih besar atau sama dengan nilai anak-anaknya.

### Properti Max Heap
```
1. Complete Binary Tree: Semua level terisi kecuali mungkin level terakhir
2. Heap Property: parent[i] ≥ child[i] untuk semua node
3. Root adalah elemen maksimum
```

### Representasi Array
Untuk node pada index `i`:
```php
- Parent: floor((i - 1) / 2)
- Left Child: 2 * i + 1
- Right Child: 2 * i + 2
```

### Visualisasi
```
       [5: Kritis]
      /            \
  [4: Darurat]   [4: Darurat]
   /        \       /      \
[3: Urgent] [3]   [2]     [1]

Array: [5, 4, 4, 3, 3, 2, 1]
```

---

## Implementasi

### 1. Struktur Data

```php
class PriorityQueue {
    private $heap;      // Array untuk menyimpan heap
    private $size;      // Jumlah elemen saat ini
    private $capacity;  // Kapasitas maksimum
    
    const PRIORITY_CRITICAL = 5;
    const PRIORITY_EMERGENCY = 4;
    const PRIORITY_URGENT = 3;
    const PRIORITY_SEMI_URGENT = 2;
    const PRIORITY_NON_URGENT = 1;
}
```

### 2. Insert Operation

**Algoritma:**
1. Tambahkan elemen di akhir array
2. Heapify-up untuk restore heap property

**Code:**
```php
public function insert($patient) {
    // Insert di akhir
    $this->heap[$this->size] = $patient;
    $this->size++;
    
    // Heapify-up
    $this->heapifyUp($this->size - 1);
}

private function heapifyUp($index) {
    while ($index > 0) {
        $parentIndex = floor(($index - 1) / 2);
        
        if ($this->compare($this->heap[$index], 
                          $this->heap[$parentIndex]) > 0) {
            $this->swap($index, $parentIndex);
            $index = $parentIndex;
        } else {
            break;
        }
    }
}
```

**Kompleksitas:** O(log n)

**Visualisasi Insert:**
```
Insert pasien priority 5:

Step 1: Insert di akhir        Step 2: Heapify Up
      [4]                            [5]
     /   \                          /   \
   [3]   [2]                      [4]   [2]
   /                              /
 [5]                            [3]
```

### 3. Extract Max Operation

**Algoritma:**
1. Simpan root (max element)
2. Pindahkan elemen terakhir ke root
3. Heapify-down untuk restore heap property

**Code:**
```php
public function extractMax() {
    if ($this->isEmpty()) return null;
    
    // Ambil root
    $max = $this->heap[0];
    
    // Pindahkan elemen terakhir ke root
    $this->heap[0] = $this->heap[$this->size - 1];
    unset($this->heap[$this->size - 1]);
    $this->size--;
    
    // Heapify-down
    if ($this->size > 0) {
        $this->heapifyDown(0);
    }
    
    return $max;
}

private function heapifyDown($index) {
    while ($this->hasLeftChild($index)) {
        $largerChildIndex = $this->getLeftChildIndex($index);
        
        // Cari child dengan prioritas lebih tinggi
        if ($this->hasRightChild($index) && 
            $this->compare($this->heap[$this->getRightChildIndex($index)], 
                          $this->heap[$largerChildIndex]) > 0) {
            $largerChildIndex = $this->getRightChildIndex($index);
        }
        
        // Jika parent >= child, stop
        if ($this->compare($this->heap[$index], 
                          $this->heap[$largerChildIndex]) > 0) {
            break;
        }
        
        // Swap dengan child yang lebih besar
        $this->swap($index, $largerChildIndex);
        $index = $largerChildIndex;
    }
}
```

**Kompleksitas:** O(log n)

**Visualisasi Extract:**
```
Extract Max (root):

Step 1: Remove root            Step 2: Move last to root    Step 3: Heapify Down
      [5]                            [1]                          [4]
     /   \                          /   \                        /   \
   [4]   [3]          →           [4]   [3]         →          [1]   [3]
   /                              /                            /
 [1]                                                          
```

### 4. Peek Operation

**Algoritma:**
Kembalikan elemen root tanpa menghapus

**Code:**
```php
public function peek() {
    if ($this->isEmpty()) return null;
    return $this->heap[0];
}
```

**Kompleksitas:** O(1)

### 5. Build Heap Operation

**Algoritma:**
Bangun heap dari array unsorted dalam O(n)

**Code:**
```php
public function buildHeap($patients) {
    $this->heap = $patients;
    $this->size = count($patients);
    
    // Start from last non-leaf node
    for ($i = floor($this->size / 2) - 1; $i >= 0; $i--) {
        $this->heapifyDown($i);
    }
}
```

**Kompleksitas:** O(n)

### 6. Update Priority Operation

**Algoritma:**
1. Cari elemen yang akan diupdate
2. Update prioritas
3. Heapify up atau down sesuai perubahan

**Code:**
```php
public function updatePriority($patientId, $newPriority) {
    // Cari index pasien
    $index = -1;
    for ($i = 0; $i < $this->size; $i++) {
        if ($this->heap[$i]['id'] == $patientId) {
            $index = $i;
            break;
        }
    }
    
    if ($index == -1) return false;
    
    $oldPriority = $this->heap[$index]['priority'];
    $this->heap[$index]['priority'] = $newPriority;
    
    // Heapify sesuai perubahan
    if ($newPriority > $oldPriority) {
        $this->heapifyUp($index);
    } else {
        $this->heapifyDown($index);
    }
    
    return true;
}
```

**Kompleksitas:** O(n) untuk search, O(log n) untuk heapify

### 7. Compare Function

**Algoritma:**
Bandingkan dua pasien berdasarkan:
1. Priority level (higher is better)
2. Arrival time (earlier is better jika priority sama)

**Code:**
```php
private function compare($patient1, $patient2) {
    // Bandingkan priority
    if ($patient1['priority'] > $patient2['priority']) {
        return 1;
    } elseif ($patient1['priority'] < $patient2['priority']) {
        return -1;
    }
    
    // Jika priority sama, bandingkan arrival time (FIFO)
    if ($patient1['arrival_time'] < $patient2['arrival_time']) {
        return 1; // Earlier is higher priority
    } elseif ($patient1['arrival_time'] > $patient2['arrival_time']) {
        return -1;
    }
    
    return 0;
}
```

---

## Kompleksitas

### Time Complexity

| Operation | Average Case | Worst Case | Best Case |
|-----------|--------------|------------|-----------|
| Insert | O(log n) | O(log n) | O(1) |
| Extract Max | O(log n) | O(log n) | O(log n) |
| Peek | O(1) | O(1) | O(1) |
| Build Heap | O(n) | O(n) | O(n) |
| Update Priority | O(n) | O(n) | O(1) |
| Remove | O(n) | O(n) | O(1) |
| Get All Sorted | O(n log n) | O(n log n) | O(n log n) |

### Space Complexity

| Operation | Space |
|-----------|-------|
| Heap Storage | O(n) |
| Heapify Operations | O(1) auxiliary |
| Get All Sorted | O(n) temporary |

### Analisis Performa

**Untuk 100 pasien:**
- Insert: ~7 comparisons
- Extract Max: ~7 comparisons
- Build Heap: ~100 operations

**Keuntungan dibanding Array Biasa:**
```
Array Biasa (Sort setiap kali):
- Insert: O(1)
- Get Max: O(n) atau O(n log n) jika sort
- Total untuk n operations: O(n²) atau O(n² log n)

Max Heap:
- Insert: O(log n)
- Get Max: O(log n)
- Total untuk n operations: O(n log n)
```

---

## Use Case

### 1. Prioritas Kegawatan

**Level Prioritas:**
```php
5 - KRITIS: Henti jantung, tidak sadar, pendarahan hebat
4 - DARURAT: Nyeri dada, sesak nafas, demam tinggi
3 - MENDESAK: Demam, diare, nyeri sedang
2 - SEMI MENDESAK: Batuk, pilek, flu
1 - TIDAK MENDESAK: Check-up rutin, konsultasi
```

**Auto-Assignment:**
```php
function determinePriority($keluhan) {
    if (preg_match('/(henti jantung|tidak sadar|pingsan)/i', $keluhan)) {
        return 5; // KRITIS
    }
    if (preg_match('/(nyeri dada|sesak nafas)/i', $keluhan)) {
        return 4; // DARURAT
    }
    // ... dst
    return 1; // DEFAULT
}
```

### 2. Skenario Real-World

**Skenario 1: Pasien Datang**
```
10:00 - Pasien A (Priority 2: Batuk)
10:05 - Pasien B (Priority 3: Demam tinggi)
10:10 - Pasien C (Priority 5: Sesak nafas berat)

Heap State: [C(5), B(3), A(2)]
Next to serve: C (Kritis)
```

**Skenario 2: Update Priority**
```
10:15 - Pasien A kondisi memburuk → Priority 2 → 4

Before: [C(5), B(3), A(2)]
After:  [C(5), A(4), B(3)]
```

**Skenario 3: Serve Patient**
```
10:20 - Panggil pasien prioritas tertinggi

extractMax() → Pasien C
Remaining: [A(4), B(3)]
Next: A (Darurat)
```

### 3. Statistik & Monitoring

**Metrics Tracked:**
```php
- Total pasien dalam antrian
- Distribusi per priority level
- Average wait time
- Max wait time
- Patients served per hour
```

**Query Example:**
```sql
SELECT * FROM v_priority_queue 
WHERE status = 'waiting'
ORDER BY priority DESC, arrival_time ASC;
```

---

## Testing

### Unit Tests

**Test 1: Insert & Extract**
```php
$queue = new PriorityQueue();

// Insert patients
$queue->insert(['id' => 1, 'name' => 'A', 'priority' => 2, 'arrival_time' => 1000]);
$queue->insert(['id' => 2, 'name' => 'B', 'priority' => 5, 'arrival_time' => 1001]);
$queue->insert(['id' => 3, 'name' => 'C', 'priority' => 3, 'arrival_time' => 1002]);

// Extract should return B (priority 5)
$max = $queue->extractMax();
assert($max['id'] == 2);
assert($max['priority'] == 5);
```

**Test 2: FIFO for Same Priority**
```php
$queue->insert(['id' => 1, 'priority' => 3, 'arrival_time' => 1000]);
$queue->insert(['id' => 2, 'priority' => 3, 'arrival_time' => 1001]);

// Should return id 1 (earlier arrival)
$first = $queue->extractMax();
assert($first['id'] == 1);
```

**Test 3: Update Priority**
```php
$queue->insert(['id' => 1, 'priority' => 2, 'arrival_time' => 1000]);
$queue->insert(['id' => 2, 'priority' => 3, 'arrival_time' => 1001]);

// Update patient 1 to priority 5
$queue->updatePriority(1, 5);

// Should now extract patient 1
$max = $queue->extractMax();
assert($max['id'] == 1);
assert($max['priority'] == 5);
```

### Integration Tests

**Test Database Integration:**
```sql
-- Insert test data
INSERT INTO booking (pasien, dokter, tanggal, hari, jam, keluhan, priority) VALUES
('Test A', 'Dr. X', '2025-01-01', 'Senin', '10:00', 'Batuk', 2),
('Test B', 'Dr. Y', '2025-01-01', 'Senin', '10:05', 'Demam tinggi', 4),
('Test C', 'Dr. Z', '2025-01-01', 'Senin', '10:10', 'Sesak nafas', 5);

-- Verify order via view
SELECT * FROM v_priority_queue;
-- Expected: C(5), B(4), A(2)
```

### Performance Tests

**Benchmark Insert Performance:**
```php
$start = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $queue->insert([
        'id' => $i,
        'priority' => rand(1, 5),
        'arrival_time' => time()
    ]);
}

$duration = microtime(true) - $start;
echo "1000 inserts: {$duration}s"; // Expected: < 0.1s
```

---

## Best Practices

### 1. Capacity Planning
```php
// Set realistic capacity based on hospital size
$queue = new PriorityQueue(200); // Max 200 concurrent patients
```

### 2. Regular Cleanup
```php
// Remove completed/cancelled bookings
DELETE FROM booking WHERE status IN ('completed', 'cancelled') 
AND updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### 3. Monitoring
```php
// Alert if critical patients waiting > 5 minutes
if ($stats['critical'] > 0 && $maxWaitTime > 5) {
    sendAlert("CRITICAL PATIENT WAITING!");
}
```

### 4. Audit Trail
```php
// Log all priority changes
CALL LogPriorityChange(
    booking_id, patient_name, old_priority, 
    new_priority, 'Condition worsened', 'Dr. Smith'
);
```

---

## Kesimpulan

Max Heap adalah solusi optimal untuk sistem antrian prioritas karena:

✅ **Efficient**: O(log n) operations  
✅ **Fair**: FIFO untuk priority sama  
✅ **Flexible**: Dynamic priority updates  
✅ **Scalable**: Handles hundreds of patients  
✅ **Professional**: Production-ready implementation

---

**Dibuat untuk:** RS Sehat Selalu  
**Versi:** 1.0  
**Tanggal:** 2025
