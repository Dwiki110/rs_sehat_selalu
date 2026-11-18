<?php
/**
 * Priority Queue Implementation using Max Heap
 * Sistem Antrian Prioritas Pasien RS Sehat Selalu
 * 
 * Menggunakan Max Heap untuk mengurutkan pasien berdasarkan:
 * 1. Tingkat kegawatan (emergency level)
 * 2. Waktu kedatangan (FIFO jika prioritas sama)
 * 
 * @author RS Sehat Selalu
 * @version 1.0
 */

class PriorityQueue {
    private $heap;
    private $size;
    private $capacity;
    
    /**
     * Priority levels untuk pasien
     */
    const PRIORITY_CRITICAL = 5;    // Kritis - Penanganan Segera
    const PRIORITY_EMERGENCY = 4;   // Darurat - <30 menit
    const PRIORITY_URGENT = 3;      // Mendesak - <2 jam
    const PRIORITY_SEMI_URGENT = 2; // Semi Mendesak - <4 jam
    const PRIORITY_NON_URGENT = 1;  // Tidak Mendesak - <24 jam
    
    /**
     * Constructor
     * 
     * @param int $capacity Kapasitas maksimal antrian
     */
    public function __construct($capacity = 100) {
        $this->heap = array();
        $this->size = 0;
        $this->capacity = $capacity;
    }
    
    /**
     * Insert pasien ke dalam priority queue
     * Menggunakan operasi heapify-up untuk mempertahankan properti heap
     * 
     * @param array $patient Data pasien
     * @return bool Success status
     */
    public function insert($patient) {
        if ($this->isFull()) {
            throw new Exception("Priority queue is full. Cannot insert more patients.");
        }
        
        // Validasi data pasien
        if (!$this->validatePatient($patient)) {
            throw new Exception("Invalid patient data.");
        }
        
        // Insert di akhir array
        $this->heap[$this->size] = $patient;
        $this->size++;
        
        // Heapify-up untuk mempertahankan properti max heap
        $this->heapifyUp($this->size - 1);
        
        return true;
    }
    
    /**
     * Extract pasien dengan prioritas tertinggi
     * Menggunakan operasi heapify-down untuk mempertahankan properti heap
     * 
     * @return array|null Data pasien dengan prioritas tertinggi
     */
    public function extractMax() {
        if ($this->isEmpty()) {
            return null;
        }
        
        // Ambil root (prioritas tertinggi)
        $max = $this->heap[0];
        
        // Pindahkan elemen terakhir ke root
        $this->heap[0] = $this->heap[$this->size - 1];
        unset($this->heap[$this->size - 1]);
        $this->size--;
        
        // Heapify-down untuk mempertahankan properti max heap
        if ($this->size > 0) {
            $this->heapifyDown(0);
        }
        
        return $max;
    }
    
    /**
     * Peek pasien dengan prioritas tertinggi tanpa menghapus
     * 
     * @return array|null Data pasien dengan prioritas tertinggi
     */
    public function peek() {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->heap[0];
    }
    
    /**
     * Heapify-up operation
     * Memindahkan elemen ke atas sampai properti heap terpenuhi
     * Kompleksitas: O(log n)
     * 
     * @param int $index Index elemen yang akan di-heapify
     */
    private function heapifyUp($index) {
        while ($index > 0) {
            $parentIndex = $this->getParentIndex($index);
            
            // Jika child memiliki prioritas lebih tinggi dari parent, swap
            if ($this->compare($this->heap[$index], $this->heap[$parentIndex]) > 0) {
                $this->swap($index, $parentIndex);
                $index = $parentIndex;
            } else {
                break;
            }
        }
    }
    
    /**
     * Heapify-down operation
     * Memindahkan elemen ke bawah sampai properti heap terpenuhi
     * Kompleksitas: O(log n)
     * 
     * @param int $index Index elemen yang akan di-heapify
     */
    private function heapifyDown($index) {
        while ($this->hasLeftChild($index)) {
            $largerChildIndex = $this->getLeftChildIndex($index);
            
            // Cari child dengan prioritas lebih tinggi
            if ($this->hasRightChild($index) && 
                $this->compare($this->heap[$this->getRightChildIndex($index)], 
                              $this->heap[$largerChildIndex]) > 0) {
                $largerChildIndex = $this->getRightChildIndex($index);
            }
            
            // Jika parent memiliki prioritas lebih tinggi, stop
            if ($this->compare($this->heap[$index], $this->heap[$largerChildIndex]) > 0) {
                break;
            }
            
            // Swap dengan child yang lebih besar
            $this->swap($index, $largerChildIndex);
            $index = $largerChildIndex;
        }
    }
    
    /**
     * Compare dua pasien berdasarkan prioritas
     * Return: 1 jika p1 > p2, -1 jika p1 < p2, 0 jika sama
     * 
     * Kriteria perbandingan:
     * 1. Priority level (lebih tinggi = lebih prioritas)
     * 2. Arrival time (lebih awal = lebih prioritas jika priority sama)
     * 
     * @param array $patient1 Data pasien 1
     * @param array $patient2 Data pasien 2
     * @return int Hasil perbandingan
     */
    private function compare($patient1, $patient2) {
        // Bandingkan berdasarkan priority level
        if ($patient1['priority'] > $patient2['priority']) {
            return 1;
        } elseif ($patient1['priority'] < $patient2['priority']) {
            return -1;
        }
        
        // Jika priority sama, bandingkan berdasarkan waktu kedatangan (FIFO)
        if ($patient1['arrival_time'] < $patient2['arrival_time']) {
            return 1; // Yang lebih awal lebih prioritas
        } elseif ($patient1['arrival_time'] > $patient2['arrival_time']) {
            return -1;
        }
        
        return 0;
    }
    
    /**
     * Swap dua elemen dalam heap
     * 
     * @param int $index1 Index pertama
     * @param int $index2 Index kedua
     */
    private function swap($index1, $index2) {
        $temp = $this->heap[$index1];
        $this->heap[$index1] = $this->heap[$index2];
        $this->heap[$index2] = $temp;
    }
    
    /**
     * Helper functions untuk navigasi heap
     */
    private function getParentIndex($index) {
        return floor(($index - 1) / 2);
    }
    
    private function getLeftChildIndex($index) {
        return 2 * $index + 1;
    }
    
    private function getRightChildIndex($index) {
        return 2 * $index + 2;
    }
    
    private function hasLeftChild($index) {
        return $this->getLeftChildIndex($index) < $this->size;
    }
    
    private function hasRightChild($index) {
        return $this->getRightChildIndex($index) < $this->size;
    }
    
    /**
     * Check if queue is empty
     * 
     * @return bool
     */
    public function isEmpty() {
        return $this->size == 0;
    }
    
    /**
     * Check if queue is full
     * 
     * @return bool
     */
    public function isFull() {
        return $this->size >= $this->capacity;
    }
    
    /**
     * Get current size of queue
     * 
     * @return int
     */
    public function getSize() {
        return $this->size;
    }
    
    /**
     * Get all patients in queue (sorted by priority)
     * 
     * @return array
     */
    public function getAllPatients() {
        // Create a copy to avoid modifying original heap
        $tempHeap = array();
        $tempSize = $this->size;
        $tempHeap = $this->heap;
        
        $result = array();
        while ($tempSize > 0) {
            $result[] = $tempHeap[0];
            $tempHeap[0] = $tempHeap[$tempSize - 1];
            unset($tempHeap[$tempSize - 1]);
            $tempSize--;
            
            if ($tempSize > 0) {
                $this->heapifyDownTemp($tempHeap, $tempSize, 0);
            }
        }
        
        return $result;
    }
    
    /**
     * Temporary heapify-down for getAllPatients
     */
    private function heapifyDownTemp(&$heap, $size, $index) {
        while (2 * $index + 1 < $size) {
            $largerChildIndex = 2 * $index + 1;
            
            if (2 * $index + 2 < $size && 
                $this->compare($heap[2 * $index + 2], $heap[$largerChildIndex]) > 0) {
                $largerChildIndex = 2 * $index + 2;
            }
            
            if ($this->compare($heap[$index], $heap[$largerChildIndex]) > 0) {
                break;
            }
            
            $temp = $heap[$index];
            $heap[$index] = $heap[$largerChildIndex];
            $heap[$largerChildIndex] = $temp;
            $index = $largerChildIndex;
        }
    }
    
    /**
     * Update prioritas pasien yang sudah ada di queue
     * 
     * @param int $patientId ID pasien
     * @param int $newPriority Prioritas baru
     * @return bool Success status
     */
    public function updatePriority($patientId, $newPriority) {
        // Cari index pasien
        $index = -1;
        for ($i = 0; $i < $this->size; $i++) {
            if ($this->heap[$i]['id'] == $patientId) {
                $index = $i;
                break;
            }
        }
        
        if ($index == -1) {
            return false;
        }
        
        $oldPriority = $this->heap[$index]['priority'];
        $this->heap[$index]['priority'] = $newPriority;
        
        // Heapify sesuai perubahan prioritas
        if ($newPriority > $oldPriority) {
            $this->heapifyUp($index);
        } else {
            $this->heapifyDown($index);
        }
        
        return true;
    }
    
    /**
     * Remove specific patient from queue
     * 
     * @param int $patientId ID pasien
     * @return bool Success status
     */
    public function removePatient($patientId) {
        // Cari index pasien
        $index = -1;
        for ($i = 0; $i < $this->size; $i++) {
            if ($this->heap[$i]['id'] == $patientId) {
                $index = $i;
                break;
            }
        }
        
        if ($index == -1) {
            return false;
        }
        
        // Pindahkan elemen terakhir ke posisi yang akan dihapus
        $this->heap[$index] = $this->heap[$this->size - 1];
        unset($this->heap[$this->size - 1]);
        $this->size--;
        
        // Heapify
        if ($this->size > 0 && $index < $this->size) {
            $this->heapifyUp($index);
            $this->heapifyDown($index);
        }
        
        return true;
    }
    
    /**
     * Validate patient data
     * 
     * @param array $patient Data pasien
     * @return bool
     */
    private function validatePatient($patient) {
        if (!isset($patient['id']) || !isset($patient['name']) || 
            !isset($patient['priority']) || !isset($patient['arrival_time'])) {
            return false;
        }
        
        if ($patient['priority'] < self::PRIORITY_NON_URGENT || 
            $patient['priority'] > self::PRIORITY_CRITICAL) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get priority name from priority level
     * 
     * @param int $priority Priority level
     * @return string Priority name
     */
    public static function getPriorityName($priority) {
        switch ($priority) {
            case self::PRIORITY_CRITICAL:
                return "Kritis";
            case self::PRIORITY_EMERGENCY:
                return "Darurat";
            case self::PRIORITY_URGENT:
                return "Mendesak";
            case self::PRIORITY_SEMI_URGENT:
                return "Semi Mendesak";
            case self::PRIORITY_NON_URGENT:
                return "Tidak Mendesak";
            default:
                return "Unknown";
        }
    }
    
    /**
     * Get priority color for UI
     * 
     * @param int $priority Priority level
     * @return string CSS color class
     */
    public static function getPriorityColor($priority) {
        switch ($priority) {
            case self::PRIORITY_CRITICAL:
                return "bg-red-600";
            case self::PRIORITY_EMERGENCY:
                return "bg-orange-500";
            case self::PRIORITY_URGENT:
                return "bg-yellow-500";
            case self::PRIORITY_SEMI_URGENT:
                return "bg-blue-500";
            case self::PRIORITY_NON_URGENT:
                return "bg-green-500";
            default:
                return "bg-gray-500";
        }
    }
    
    /**
     * Clear all queue
     */
    public function clear() {
        $this->heap = array();
        $this->size = 0;
    }
    
    /**
     * Build heap from array of patients
     * Kompleksitas: O(n)
     * 
     * @param array $patients Array of patients
     */
    public function buildHeap($patients) {
        $this->heap = $patients;
        $this->size = count($patients);
        
        // Start from last non-leaf node and heapify down
        for ($i = floor($this->size / 2) - 1; $i >= 0; $i--) {
            $this->heapifyDown($i);
        }
    }
    
    /**
     * Get statistics of queue
     * 
     * @return array Statistics
     */
    public function getStatistics() {
        $stats = array(
            'total' => $this->size,
            'critical' => 0,
            'emergency' => 0,
            'urgent' => 0,
            'semi_urgent' => 0,
            'non_urgent' => 0,
            'average_wait_time' => 0
        );
        
        $totalWaitTime = 0;
        $currentTime = time();
        
        foreach ($this->heap as $patient) {
            switch ($patient['priority']) {
                case self::PRIORITY_CRITICAL:
                    $stats['critical']++;
                    break;
                case self::PRIORITY_EMERGENCY:
                    $stats['emergency']++;
                    break;
                case self::PRIORITY_URGENT:
                    $stats['urgent']++;
                    break;
                case self::PRIORITY_SEMI_URGENT:
                    $stats['semi_urgent']++;
                    break;
                case self::PRIORITY_NON_URGENT:
                    $stats['non_urgent']++;
                    break;
            }
            
            $totalWaitTime += ($currentTime - $patient['arrival_time']);
        }
        
        if ($this->size > 0) {
            $stats['average_wait_time'] = round($totalWaitTime / $this->size / 60, 2); // in minutes
        }
        
        return $stats;
    }
}
?>
