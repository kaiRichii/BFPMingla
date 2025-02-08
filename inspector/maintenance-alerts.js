// Global variables
let unreadNotifications = []; // Track unread notifications
let allNotifications = []; // All notifications (read + unread)
let isSoundMuted = false; // Track sound preference

// Fetch notifications from the backend
async function fetchNotifications() {
    try {
        const response = await fetch('fetch_notifications.php'); // Backend endpoint
        const notifications = await response.json();

        if (notifications && notifications.length > 0) {
            allNotifications = notifications;
            handleNewNotifications(notifications);
        }
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}

// Handle new notifications: filter unread, update badge, dropdown, and sound
function handleNewNotifications(notifications) {
    // Filter unread notifications
    unreadNotifications = notifications.filter(notification => notification.read_status === 'unread');

    // Update the notification count badge
    updateNotificationBadge();

    // Populate the notification dropdown with all notifications
    populateNotificationDropdown(notifications);

    // Play notification sound if there are unread notifications and sound is not muted
    if (!isSoundMuted && unreadNotifications.length > 0) {
        playNotificationSound();
    }
}

// Update the badge count for unread notifications
function updateNotificationBadge() {
    const notificationCount = document.getElementById('notificationCount');
    notificationCount.textContent = unreadNotifications.length;
    notificationCount.style.display = unreadNotifications.length > 0 ? 'inline-block' : 'none';
}

// Populate the notification dropdown with categorized notifications
function populateNotificationDropdown(notifications) {
    const notificationList = document.getElementById('notificationList');
    notificationList.innerHTML = ''; // Clear current notifications

    const newNotifications = notifications.filter(n => n.read_status === 'unread');
    const earlierNotifications = notifications.filter(n => n.read_status === 'read');

    // Display "New" section
    if (newNotifications.length > 0) {
        notificationList.innerHTML += '<li style="padding: 10px; background: #f8f9fa; font-weight: bold;">New</li>';
        newNotifications.forEach(notification => addNotificationItem(notification, true));
    }

    // Display "Earlier" section
    if (earlierNotifications.length > 0) {
        notificationList.innerHTML += '<li style="padding: 10px; background: #f8f9fa; font-weight: bold;">Earlier</li>';
        earlierNotifications.forEach(notification => addNotificationItem(notification, false));
    }
}

// Add a single notification item to the list
function addNotificationItem(notification, isUnread) {
    const notificationList = document.getElementById('notificationList');
    const listItem = document.createElement('li');
    listItem.className = isUnread ? 'notification-new' : 'notification-earlier';

    const maintenanceDate = new Date(notification.next_maintenance_date);
    const today = new Date();
    const daysDifference = Math.floor((maintenanceDate - today) / (1000 * 60 * 60 * 24));

    // Generate message based on maintenance date
    let message = getMaintenanceMessage(daysDifference);

    listItem.innerHTML = `
        <div style="flex: 1;">
            <div><strong>${notification.equipment_name}</strong></div>
            <div style="font-size: 12px; color: #666;">${message}</div>
            <div style="font-size: 12px; color: #999;">Last Maintenance: ${new Date(notification.last_maintenance_date).toLocaleDateString()}</div>
        </div>
        ${isUnread ? '<button class="mark-read-btn" style="font-size: 12px; color: #007BFF; background: none; border: none; cursor: pointer;">Mark as Read</button>' : ''}
    `;

    // Attach the "Mark as Read" button functionality for unread notifications
    if (isUnread) {
        const markReadButton = listItem.querySelector('.mark-read-btn');
        markReadButton.addEventListener('click', () => markNotificationAsRead(notification));
    }

    notificationList.appendChild(listItem);
}

// Generate the maintenance message based on the days difference
function getMaintenanceMessage(daysDifference) {
    if (daysDifference < 0) {
        return `Maintenance is overdue by ${Math.abs(daysDifference)} days.`;
    } else if (daysDifference === 0) {
        return 'Maintenance is scheduled for today.';
    } else {
        return `Maintenance is scheduled in ${daysDifference} days.`;
    }
}

// Mark a single notification as read
async function markNotificationAsRead(notification) {
    try {
        const response = await fetch('mark_as_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ equipment_name: notification.equipment_name })
        });

        const result = await response.json();
        if (result.success) {
            notification.read_status = 'read'; // Update status locally
            unreadNotifications = unreadNotifications.filter(n => n.equipment_name !== notification.equipment_name);
            handleNewNotifications(allNotifications); // Refresh notifications
        } else {
            console.error('Failed to mark notification as read:', result.error);
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

// Mark all unread notifications as read
async function markAllNotificationsAsRead() {
    try {
        const response = await fetch('mark_as_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ equipment_names: unreadNotifications.map(n => n.equipment_name) })
        });

        const result = await response.json();
        if (result.success) {
            unreadNotifications.forEach(notification => (notification.read_status = 'read')); // Update all notifications
            unreadNotifications = []; // Clear unread notifications
            handleNewNotifications(allNotifications); // Refresh notifications list
        } else {
            console.error('Failed to mark all notifications as read:', result.error);
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

// Toggle the sound mute/unmute
function toggleSound() {
    isSoundMuted = !isSoundMuted;
    const muteButton = document.getElementById('muteSoundToggle');
    muteButton.textContent = isSoundMuted ? 'Unmute Sound' : 'Mute Sound';
}

// Toggle the notification dropdown visibility
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Play a notification sound
function playNotificationSound() {
    console.log('Playing notification sound...');
    const audio = new Audio('../ringtone/alert.wav');
    audio.play().catch(error => console.error('Audio playback failed:', error));
}

// Event listeners for UI interaction
document.getElementById('notificationBell').addEventListener('click', toggleNotifications);
document.getElementById('muteSoundToggle').addEventListener('click', toggleSound);
document.getElementById('notificationFooter').addEventListener('click', () => {
    window.location.href = 'maintenance.php'; // Redirect to full notifications page
});
document.getElementById('markAllAsRead').addEventListener('click', markAllNotificationsAsRead);

// Fetch notifications every 30 seconds
setInterval(fetchNotifications, 30000);

// Initial fetch on page load
document.addEventListener('DOMContentLoaded', fetchNotifications);
