$(document).ready(function() {
    // Initialize notification area to ensure clean state
    initializeNotifications();
    
    // Load notifications when page loads
    loadNotifications();
    
    // Load notifications when dropdown is opened
    $('#page-header-notifications-dropdown').on('click', function() {
        loadNotifications();
    });
    
    // Mark all as read button
    $('#mark-all-read-btn').on('click', function() {
        markAllAsRead();
    });
    
    // Clear all notifications button
    $('#clear-all-btn').on('click', function() {
        if (confirm('Are you sure you want to clear all notifications?')) {
            clearAllNotifications();
        }
    });
    
    // Auto-refresh notifications every 30 seconds
    setInterval(function() {
        loadNotifications();
    }, 30000);
});

function initializeNotifications() {
    // Clear any existing notification content and ensure clean state
    const container = $('#notifications-list');
    const noNotifications = $('#no-notifications');
    
    // Remove any unwanted content
    container.find('*').not('#no-notifications').remove();
    
    // Ensure no-notifications element exists and is properly structured
    if (noNotifications.length === 0) {
        container.append(`
            <div class="text-center py-3" id="no-notifications">
                <i class="bx bx-bell-off fs-48 text-muted"></i>
                <p class="text-muted mt-2">No notifications found.</p>
            </div>
        `);
    }
    
    // Show the no notifications state by default
    noNotifications.show();
    
    // Hide badge and action buttons
    $('#notification-badge').hide();
    $('#mark-all-read-btn, #clear-all-btn').hide();
    $('#notification-count').text('0');
}

function loadNotifications() {
    $.ajax({
        url: base_url + 'notifications/get_notifications',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateNotificationBadge(response.unread_count);
                updateNotificationList(response.notifications);
                updateNotificationCount(response.notifications.length);
            } else {
                // If response is not successful, show empty state
                updateNotificationBadge(0);
                updateNotificationList([]);
                updateNotificationCount(0);
            }
        },
        error: function() {
            console.log('Error loading notifications');
            // On error, show empty state instead of any fallback message
            updateNotificationBadge(0);
            updateNotificationList([]);
            updateNotificationCount(0);
        }
    });
}

function updateNotificationBadge(count) {
    const badge = $('#notification-badge');
    const countSpan = badge.find('span:first');
    
    if (count > 0) {
        countSpan.text(count);
        badge.show();
    } else {
        badge.hide();
    }
}

function updateNotificationCount(count) {
    $('#notification-count').text(count);
    
    // Show/hide action buttons based on notification count
    if (count > 0) {
        $('#mark-all-read-btn, #clear-all-btn').show();
    } else {
        $('#mark-all-read-btn, #clear-all-btn').hide();
    }
}

function updateNotificationList(notifications) {
    const container = $('#notifications-list');
    const noNotifications = $('#no-notifications');
    
    // Clear all existing content first
    container.find('.notification-item').remove();
    container.find('.text-center').remove();
    container.find('.py-3').remove();
    
    if (notifications.length === 0) {
        // Show the existing no notifications message
        noNotifications.show();
        return;
    }
    
    noNotifications.hide();
    
    // Add new notifications
    notifications.forEach(function(notification) {
        const notificationHtml = createNotificationHtml(notification);
        container.append(notificationHtml);
    });
}

function createNotificationHtml(notification) {
    const timeAgo = getTimeAgo(notification.created_at);
    const typeIcon = getTypeIcon(notification.type);
    const typeClass = getTypeClass(notification.type);
    
    return `
        <div class="text-reset notification-item d-block dropdown-item position-relative" data-id="${notification.id}">
            <div class="d-flex">
                <div class="me-3 flex-shrink-0">
                    <div class="avatar-xs">
                        <span class="avatar-title rounded-circle ${typeClass}">
                            <i class="${typeIcon}"></i>
                        </span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">${notification.title}</h6>
                    <div class="fs-13 text-muted">
                        <p class="mb-1">${notification.message}</p>
                    </div>
                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                        <span><i class="mdi mdi-clock-outline"></i> ${timeAgo}</span>
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item mark-read-btn" href="#" data-id="${notification.id}">
                                <i class="bx bx-check me-2"></i>Mark as Read
                            </a></li>
                            <li><a class="dropdown-item delete-notification-btn" href="#" data-id="${notification.id}">
                                <i class="bx bx-trash me-2"></i>Delete
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getTypeIcon(type) {
    const icons = {
        'info': 'bx bx-info-circle',
        'success': 'bx bx-check-circle',
        'warning': 'bx bx-error',
        'error': 'bx bx-x-circle'
    };
    return icons[type] || icons['info'];
}

function getTypeClass(type) {
    const classes = {
        'info': 'bg-info',
        'success': 'bg-success',
        'warning': 'bg-warning',
        'error': 'bg-danger'
    };
    return classes[type] || classes['info'];
}

function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} min ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days > 1 ? 's' : ''} ago`;
    }
}

function markAllAsRead() {
    $.ajax({
        url: base_url + 'notifications/mark_all_as_read',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadNotifications();
                showToast('All notifications marked as read', 'success');
            } else {
                showToast('Failed to mark notifications as read', 'error');
            }
        },
        error: function() {
            showToast('Error marking notifications as read', 'error');
        }
    });
}

function clearAllNotifications() {
    $.ajax({
        url: base_url + 'notifications/delete_all_notifications',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadNotifications();
                showToast('All notifications cleared', 'success');
            } else {
                showToast('Failed to clear notifications', 'error');
            }
        },
        error: function() {
            showToast('Error clearing notifications', 'error');
        }
    });
}

// Event delegation for dynamically added buttons
$(document).on('click', '.mark-read-btn', function(e) {
    e.preventDefault();
    const notificationId = $(this).data('id');
    markAsRead(notificationId);
});

$(document).on('click', '.delete-notification-btn', function(e) {
    e.preventDefault();
    const notificationId = $(this).data('id');
    if (confirm('Are you sure you want to delete this notification?')) {
        deleteNotification(notificationId);
    }
});

function markAsRead(notificationId) {
    $.ajax({
        url: base_url + 'notifications/mark_as_read',
        type: 'POST',
        data: { notification_id: notificationId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadNotifications();
                showToast('Notification marked as read', 'success');
            } else {
                showToast('Failed to mark notification as read', 'error');
            }
        },
        error: function() {
            showToast('Error marking notification as read', 'error');
        }
    });
}

function deleteNotification(notificationId) {
    $.ajax({
        url: base_url + 'notifications/delete_notification',
        type: 'POST',
        data: { notification_id: notificationId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadNotifications();
                showToast('Notification deleted', 'success');
            } else {
                showToast('Failed to delete notification', 'error');
            }
        },
        error: function() {
            showToast('Error deleting notification', 'error');
        }
    });
}

function showToast(message, type) {
    // Simple toast notification - you can replace this with your preferred toast library
    const toastClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const toast = $(`
        <div class="alert ${toastClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(toast);
    
    // Auto remove after 3 seconds
    setTimeout(function() {
        toast.alert('close');
    }, 3000);
}
