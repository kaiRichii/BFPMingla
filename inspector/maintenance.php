<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notifications</title>
    <!-- Add your CSS or Bootstrap here -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f6f7;
            margin: 0;
            padding: 0;
        }

        .notifications-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .notifications-header {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }

        .notifications-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            font-weight: bold;
            background-color: #f0f4f8;
        }

        .notification-item.read {
            font-weight: normal;
            background-color: #fff;
        }

        .notification-item .notification-content {
            flex: 1;
            margin-left: 10px;
        }

        .notification-item .notification-content strong {
            font-size: 14px;
            color: #333;
        }

        .notification-item .notification-content .details {
            font-size: 12px;
            color: #666;
        }

        .notification-item .notification-content .timestamp {
            font-size: 10px;
            color: #999;
        }

        .section-header {
            padding: 10px 15px;
            background: #f8f9fa;
            font-size: 14px;
            font-weight: bold;
            color: #555;
        }

        .no-notifications {
            text-align: center;
            padding: 20px;
            color: #888;
        }

        .notifications-footer {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            font-size: 14px;
            border-top: 1px solid #ddd;
            color: #007BFF;
            cursor: pointer;
        }

        .notifications-footer:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="notifications-container">
        <!-- Header -->
        <div class="notifications-header">
            All Notifications
        </div>

        <!-- Notifications List -->
        <div class="notifications-list" id="allNotifications">
            <!-- Notifications dynamically added here -->
        </div>

        <!-- Footer -->
        <div class="notifications-footer" onclick="redirectToDashboard()">
            Back to Equipments
        </div>
    </div>

    <!-- Script -->
    <script>
        // Fetch notifications from the backend
        async function fetchAllNotifications() {
            try {
                const response = await fetch('fetch_notifications.php'); // Replace with your backend endpoint
                const notifications = await response.json();

                if (notifications && notifications.length > 0) {
                    populateAllNotifications(notifications);
                } else {
                    displayNoNotificationsMessage();
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        }

        // Populate all notifications
        function populateAllNotifications(notifications) {
            const notificationsList = document.getElementById('allNotifications');
            notificationsList.innerHTML = ''; // Clear existing notifications

            const unreadNotifications = notifications.filter(n => n.read_status === 'unread');
            const readNotifications = notifications.filter(n => n.read_status === 'read');

            if (unreadNotifications.length > 0) {
                notificationsList.innerHTML += '<div class="section-header">New</div>';
                unreadNotifications.forEach(notification => addNotificationItem(notification, true));
            }

            if (readNotifications.length > 0) {
                notificationsList.innerHTML += '<div class="section-header">Earlier</div>';
                readNotifications.forEach(notification => addNotificationItem(notification, false));
            }
        }

        // Add a single notification item
        function addNotificationItem(notification, isUnread) {
            const notificationsList = document.getElementById('allNotifications');

            // Calculate days until/overdue from maintenance date
            const maintenanceDate = new Date(notification.next_maintenance_date);
            const today = new Date();
            const daysDifference = Math.floor((maintenanceDate - today) / (1000 * 60 * 60 * 24)); // Whole number difference

            let message = '';
            if (daysDifference < 0) {
                message = `Maintenance is overdue by ${Math.abs(daysDifference)} days.`;
            } else if (daysDifference === 0) {
                message = `Maintenance is scheduled for today.`;
            } else {
                message = `Maintenance is scheduled in ${daysDifference} days.`;
            }

            const listItem = document.createElement('div');
            listItem.className = `notification-item ${isUnread ? 'unread' : 'read'}`;
            listItem.innerHTML = `
                <div class="notification-content">
                    <strong>${notification.equipment_name}</strong>
                    <div class="details">${message}</div>
                    <div class="timestamp">Last Maintenance: ${new Date(notification.last_maintenance_date).toLocaleDateString()}</div>
                </div>
            `;

            notificationsList.appendChild(listItem);
        }

        // Display "No Notifications" message
        function displayNoNotificationsMessage() {
            const notificationsList = document.getElementById('allNotifications');
            notificationsList.innerHTML = `
                <div class="no-notifications">You have no notifications at this time.</div>
            `;
        }

        // Redirect to dashboard
        function redirectToDashboard() {
            window.location.href = 'equipments.php'; // Replace with your dashboard URL
        }

        // Fetch notifications when the page loads
        document.addEventListener('DOMContentLoaded', fetchAllNotifications);
    </script>
</body>
</html>
