/**
 * Notification Handler
 * Handles real-time notifications for patients
 * 
 * @author RS Sehat Selalu
 * @version 1.0
 */

class NotificationHandler {
    constructor() {
        this.unreadCount = 0;
        this.notifications = [];
        this.pollInterval = 30000; // 30 seconds
        this.pollingTimer = null;
    }

    /**
     * Initialize notification system
     */
    init() {
        this.loadNotifications();
        this.startPolling();
        this.attachEventListeners();
    }

    /**
     * Load notifications from API
     */
    async loadNotifications() {
        try {
            const response = await fetch('api_notifications.php?action=get');
            const data = await response.json();
            
            if (data.success) {
                this.notifications = data.data;
                this.updateBadge();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    /**
     * Get unread count
     */
    async getUnreadCount() {
        try {
            const response = await fetch('api_notifications.php?action=count');
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = data.unread_count;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Error getting unread count:', error);
        }
    }

    /**
     * Update notification badge
     */
    updateBadge() {
        const badge = document.getElementById('notification-badge');
        const bellIcon = document.getElementById('notification-bell');
        
        if (badge) {
            // Count unread from current notifications
            this.unreadCount = this.notifications.filter(n => n.is_read == 0).length;
            
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.classList.remove('hidden');
                
                // Add pulsing animation for urgent notifications
                if (bellIcon && this.hasUrgentNotifications()) {
                    bellIcon.classList.add('animate-bounce');
                }
            } else {
                badge.classList.add('hidden');
                if (bellIcon) {
                    bellIcon.classList.remove('animate-bounce');
                }
            }
        }
    }

    /**
     * Check if there are urgent (emergency) notifications
     */
    hasUrgentNotifications() {
        return this.notifications.some(n => n.is_read == 0 && n.type === 'emergency');
    }

    /**
     * Render notifications in dropdown
     */
    renderNotifications() {
        const container = document.getElementById('notifications-list');
        
        if (!container) return;

        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="bi bi-bell-slash text-3xl mb-2"></i>
                    <p>Tidak ada notifikasi</p>
                </div>
            `;
            return;
        }

        // Sort by created_at desc and limit to 10
        const recentNotifications = this.notifications.slice(0, 10);

        container.innerHTML = recentNotifications.map(notification => {
            const isUnread = notification.is_read == 0;
            const iconClass = this.getNotificationIcon(notification.type);
            const badgeClass = this.getNotificationBadgeClass(notification.type);
            const timeAgo = this.getTimeAgo(notification.created_at);

            return `
                <div class="notification-item p-3 border-b hover:bg-gray-50 transition-colors ${isUnread ? 'bg-blue-50' : ''}" 
                     data-id="${notification.id}">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <i class="bi ${iconClass} text-2xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="badge badge-${badgeClass} text-xs px-2 py-1 rounded">
                                    ${this.getNotificationTypeLabel(notification.type)}
                                </span>
                                ${isUnread ? '<span class="w-2 h-2 bg-blue-500 rounded-full"></span>' : ''}
                            </div>
                            <p class="text-sm text-gray-800 mb-1">${this.escapeHtml(notification.message)}</p>
                            ${notification.queue_number ? `
                                <p class="text-xs text-gray-600 mb-1">
                                    <i class="bi bi-hash"></i> ${notification.queue_number}
                                </p>
                            ` : ''}
                            <p class="text-xs text-gray-500">${timeAgo}</p>
                        </div>
                        ${isUnread ? `
                            <button class="mark-read-btn flex-shrink-0 text-blue-500 hover:text-blue-700" 
                                    data-id="${notification.id}" title="Tandai sudah dibaca">
                                <i class="bi bi-check2"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');

        // Attach click handlers for mark-read buttons
        container.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const notificationId = btn.getAttribute('data-id');
                this.markAsRead(notificationId);
            });
        });
    }

    /**
     * Get notification icon class
     */
    getNotificationIcon(type) {
        const icons = {
            'emergency': 'bi-exclamation-triangle-fill text-danger',
            'call_soon': 'bi-bell-fill text-warning',
            'queue_update': 'bi-info-circle-fill text-info',
            'priority_assigned': 'bi-check-circle-fill text-success'
        };
        return icons[type] || 'bi-bell-fill text-secondary';
    }

    /**
     * Get notification badge class
     */
    getNotificationBadgeClass(type) {
        const classes = {
            'emergency': 'danger',
            'call_soon': 'warning',
            'queue_update': 'info',
            'priority_assigned': 'success'
        };
        return classes[type] || 'secondary';
    }

    /**
     * Get notification type label
     */
    getNotificationTypeLabel(type) {
        const labels = {
            'emergency': 'DARURAT',
            'call_soon': 'Segera Dipanggil',
            'queue_update': 'Update Antrian',
            'priority_assigned': 'Prioritas Ditentukan'
        };
        return labels[type] || 'Notifikasi';
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('notification_id', notificationId);

            const response = await fetch('api_notifications.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local notification
                const notification = this.notifications.find(n => n.id == notificationId);
                if (notification) {
                    notification.is_read = 1;
                }
                this.updateBadge();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_all_read');

            const response = await fetch('api_notifications.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update all local notifications
                this.notifications.forEach(n => n.is_read = 1);
                this.updateBadge();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }

    /**
     * Start polling for new notifications
     */
    startPolling() {
        this.pollingTimer = setInterval(() => {
            this.getUnreadCount();
        }, this.pollInterval);
    }

    /**
     * Stop polling
     */
    stopPolling() {
        if (this.pollingTimer) {
            clearInterval(this.pollingTimer);
            this.pollingTimer = null;
        }
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Toggle notification dropdown
        const bellBtn = document.getElementById('notification-btn');
        const dropdown = document.getElementById('notification-dropdown');

        if (bellBtn && dropdown) {
            bellBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
                
                // Load fresh notifications when opening
                if (!dropdown.classList.contains('hidden')) {
                    this.loadNotifications();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Mark all as read button
        const markAllBtn = document.getElementById('mark-all-read-btn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
    }

    /**
     * Get time ago string
     */
    getTimeAgo(datetime) {
        const now = new Date();
        const past = new Date(datetime);
        const diffMs = now - past;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Baru saja';
        if (diffMins < 60) return `${diffMins} menit yang lalu`;
        if (diffHours < 24) return `${diffHours} jam yang lalu`;
        if (diffDays < 7) return `${diffDays} hari yang lalu`;
        
        return past.toLocaleDateString('id-ID');
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize notification handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.notificationHandler = new NotificationHandler();
    window.notificationHandler.init();
});
