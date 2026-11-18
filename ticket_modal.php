<!-- Queue Ticket Modal - Professional Printable Ticket -->
<style>
    .ticket-modal .modal-dialog {
        max-width: 450px;
    }
    
    .ticket-print-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        position: relative;
    }
    
    .ticket-print-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 30px;
        text-align: center;
        position: relative;
    }
    
    .ticket-print-header::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 0;
        right: 0;
        height: 30px;
        background: white;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    }
    
    .ticket-print-logo {
        font-size: 2.5rem;
        color: white;
        margin-bottom: 10px;
    }
    
    .ticket-print-hospital {
        color: white;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .ticket-print-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.85rem;
    }
    
    .ticket-print-number {
        font-size: 3.5rem;
        font-weight: 900;
        color: #667eea;
        letter-spacing: 3px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 25px 0 10px;
        font-family: 'Courier New', monospace;
    }
    
    .ticket-print-label {
        font-size: 0.9rem;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .ticket-print-body {
        padding: 35px 30px 30px;
    }
    
    .ticket-print-info {
        margin-bottom: 20px;
        padding: 15px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 12px;
        border-left: 4px solid #667eea;
    }
    
    .ticket-print-info-label {
        font-size: 0.75rem;
        color: #95a5a6;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    
    .ticket-print-info-value {
        font-size: 1.1rem;
        color: #2c3e50;
        font-weight: 700;
    }
    
    .ticket-print-datetime {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .ticket-print-date,
    .ticket-print-time {
        flex: 1;
        background: white;
        border: 2px solid #667eea;
        padding: 15px;
        border-radius: 12px;
        text-align: center;
    }
    
    .ticket-print-date i,
    .ticket-print-time i {
        font-size: 1.8rem;
        color: #667eea;
        margin-bottom: 8px;
    }
    
    .ticket-print-footer {
        background: #f8f9fa;
        padding: 20px 30px;
        border-top: 2px dashed #e0e0e0;
        text-align: center;
    }
    
    .ticket-print-qr {
        width: 80px;
        height: 80px;
        background: white;
        border: 2px solid #667eea;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .ticket-print-qr i {
        font-size: 3rem;
        color: #667eea;
    }
    
    .ticket-print-note {
        font-size: 0.8rem;
        color: #7f8c8d;
        line-height: 1.6;
        margin-top: 15px;
    }
    
    .ticket-print-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    .btn-print,
    .btn-download {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-print {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-print:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-download {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
    }
    
    .ticket-priority-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }
    
    /* Print Styles */
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
</style>

<!-- Modal -->
<div class="modal fade ticket-modal" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ticket-print-card" id="printableTicket">
                    <!-- Priority Badge (if priority >= 3) -->
                    <div class="ticket-priority-badge priority-<?php echo $ticketPriority ?? 1; ?>" id="priorityBadge" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill"></i> <span id="priorityLabel">NORMAL</span>
                    </div>
                    
                    <div class="ticket-print-header">
                        <div class="ticket-print-logo">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <div class="ticket-print-hospital">RS SEHAT SELALU</div>
                        <div class="ticket-print-subtitle">Sistem Antrian Online</div>
                    </div>
                    
                    <div class="ticket-print-body">
                        <div class="text-center">
                            <div class="ticket-print-label">Nomor Antrian Anda</div>
                            <div class="ticket-print-number" id="ticketNumber">-</div>
                        </div>
                        
                        <div class="ticket-print-info">
                            <div class="ticket-print-info-label">
                                <i class="bi bi-person-badge"></i> Dokter
                            </div>
                            <div class="ticket-print-info-value" id="ticketDoctor">-</div>
                        </div>
                        
                        <div class="ticket-print-datetime">
                            <div class="ticket-print-date">
                                <div><i class="bi bi-calendar-event"></i></div>
                                <div class="ticket-print-info-label">Tanggal</div>
                                <div class="ticket-print-info-value" id="ticketDate" style="font-size: 0.95rem;">-</div>
                                <div style="font-size: 0.75rem; color: #7f8c8d; margin-top: 3px;" id="ticketDay">-</div>
                            </div>
                            <div class="ticket-print-time">
                                <div><i class="bi bi-clock-fill"></i></div>
                                <div class="ticket-print-info-label">Jam</div>
                                <div class="ticket-print-info-value" id="ticketTime" style="font-size: 0.95rem;">-</div>
                                <div style="font-size: 0.75rem; color: #7f8c8d; margin-top: 3px;">WIB</div>
                            </div>
                        </div>
                        
                        <div class="ticket-print-info">
                            <div class="ticket-print-info-label">
                                <i class="bi bi-chat-left-text"></i> Keluhan
                            </div>
                            <div class="ticket-print-info-value" id="ticketComplaint" style="font-size: 0.9rem; font-weight: 600;">-</div>
                        </div>
                    </div>
                    
                    <div class="ticket-print-footer">
                        <div class="ticket-print-qr">
                            <i class="bi bi-qr-code"></i>
                        </div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                            <i class="bi bi-info-circle"></i> Catatan Penting
                        </div>
                        <div class="ticket-print-note">
                            • Harap datang 15 menit sebelum jadwal<br>
                            • Tunjukkan nomor antrian ini pada petugas<br>
                            • Simpan karcis ini hingga pemeriksaan selesai<br>
                            • Hubungi: (021) 1234-5678
                        </div>
                    </div>
                </div>
                
                <div class="p-3">
                    <div class="ticket-print-buttons">
                        <button type="button" class="btn-print" onclick="printTicket()">
                            <i class="bi bi-printer"></i> Cetak Karcis
                        </button>
                        <button type="button" class="btn-download" onclick="downloadTicket()">
                            <i class="bi bi-download"></i> Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to show ticket modal with data
function showTicketModal(ticketData) {
    // Set ticket data
    document.getElementById('ticketNumber').textContent = ticketData.queue_number || 'N/A';
    document.getElementById('ticketDoctor').textContent = ticketData.doctor_name || ticketData.doctor || '-';
    document.getElementById('ticketDate').textContent = formatDate(ticketData.date) || '-';
    document.getElementById('ticketDay').textContent = ticketData.day || '-';
    document.getElementById('ticketTime').textContent = ticketData.time || '-';
    document.getElementById('ticketComplaint').textContent = ticketData.complaint || '-';
    
    // Set priority badge
    const priority = parseInt(ticketData.priority) || 1;
    const priorityBadge = document.getElementById('priorityBadge');
    const priorityLabel = document.getElementById('priorityLabel');
    
    const priorityLabels = {
        5: 'KRITIS',
        4: 'DARURAT',
        3: 'MENDESAK',
        2: 'NORMAL',
        1: 'REGULER'
    };
    
    if (priority >= 3) {
        priorityBadge.style.display = 'block';
        priorityBadge.className = 'ticket-priority-badge priority-' + priority;
        priorityLabel.textContent = priorityLabels[priority] || 'NORMAL';
    } else {
        priorityBadge.style.display = 'none';
    }
    
    // Show modal
    const ticketModal = new bootstrap.Modal(document.getElementById('ticketModal'));
    ticketModal.show();
}

// Format date function
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Oct', 'Nov', 'Des'];
    return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
}

// Print ticket function
function printTicket() {
    window.print();
}

// Download ticket as image (requires html2canvas library)
function downloadTicket() {
    alert('Untuk download karcis sebagai gambar, silakan gunakan fungsi Print to PDF pada browser Anda.');
    window.print();
}

// Check for success parameter and show ticket
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'booking_created') {
        const queueNumber = urlParams.get('queue_number');
        
        // Fetch booking data
        if (queueNumber) {
            // You can add AJAX call here to fetch complete booking data
            // For now, we'll use URL parameters
            const ticketData = {
                queue_number: queueNumber,
                doctor: urlParams.get('doctor') || '-',
                doctor_name: urlParams.get('doctor_name') || '-',
                date: urlParams.get('date') || new Date().toISOString().split('T')[0],
                day: urlParams.get('day') || '-',
                time: urlParams.get('time') || '-',
                complaint: urlParams.get('complaint') || '-',
                priority: urlParams.get('priority') || '1'
            };
            
            // Show ticket modal after a short delay
            setTimeout(() => {
                showTicketModal(ticketData);
            }, 500);
        }
    }
});
</script>
