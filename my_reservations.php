<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$users_file = 'data/users.json';
$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';
$notifications_file = 'data/notifications.json';

$users = json_decode(file_get_contents($users_file), true);
$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);
$notifications = json_decode(file_get_contents($notifications_file), true);


$current_user = null;
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}


$user_reservations = array_filter($reservations, function($reservation) {
    return $reservation['user_id'] == $_SESSION['user_id'];
});

$user_notifications = array_filter($notifications, function($notification) {
    return $notification['user_id'] == $_SESSION['user_id'];
});
usort($user_notifications, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});


if (isset($_GET['cancel_reservation'])) {
    $reservation_id = intval($_GET['cancel_reservation']);
    
    foreach ($reservations as &$reservation) {
        if ($reservation['id'] == $reservation_id && $reservation['user_id'] == $_SESSION['user_id']) {
            if ($reservation['status'] === 'pending') {
                $reservation['status'] = 'cancelled';
                file_put_contents($reservations_file, json_encode($reservations, JSON_PRETTY_PRINT));
                header('Location: my_reservations.php?success=Reservation cancelled successfully');
                exit();
            }
            break;
        }
    }
    
    header('Location: my_reservations.php?error=Cannot cancel this reservation');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - CoRide</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background: var(--bg-light);
        }
        
        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: background 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--secondary-color);
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }
        
        .top-navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: var(--bg-light);
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            background: var(--secondary-color);
            color: white;
        }
        
        .reservations-grid {
            display: grid;
            gap: 25px;
        }
        
        .reservation-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .reservation-card:hover {
            transform: translateY(-5px);
        }
        
        .reservation-header {
            background: linear-gradient(135deg, var(--primary-color), var(--muted-color));
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .route {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .reservation-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .reservation-details {
            padding: 20px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .detail-value {
            color: var(--text-dark);
        }
        
        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .reservation-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-cancel {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #a03048;
        }
        
        .btn-cancel:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .no-reservations {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .no-reservations h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .btn-logout {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #a03048;
        }
        
        .notifications-section {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-top: 30px;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 15px;
            align-items: start;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background: #f8f9fa;
            border-left: 3px solid var(--primary-color);
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .notification-text {
            color: var(--text-dark);
            font-size: 0.95rem;
            margin-bottom: 5px;
        }
        
        .notification-date {
            font-size: 0.85rem;
            color: #999;
        }
        
        .no-notifications {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-dark);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="nav-logo">
                    <img src="images/logo.svg" alt="CoRide Logo" class="logo-img">
                    <span class="logo-text">CoRide</span>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="my_reservations.php" class="active">📋 My Reservations</a></li>
                <li><a href="available_offers.php">🚗 Available Offers</a></li>
                <li><a href="my_offers.php">📝 My Offers</a></li>
                <li><a href="my_invoices.php">📄 My Invoices</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.7;">User</div>
                    </div>
                </div>
                <form method="POST" action="logout.php" style="display: inline;">
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">My Reservations</h1>
                <p>View and manage your carpool reservations</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div style="background: #e8f5e8; color: var(--muted-color); padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid var(--muted-color);">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div style="background: #fee; color: var(--secondary-color); padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid var(--secondary-color);">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn active" onclick="filterReservations('all')">All Reservations</button>
                <button class="tab-btn" onclick="filterReservations('pending')">Pending</button>
                <button class="tab-btn" onclick="filterReservations('accepted')">Accepted</button>
                <button class="tab-btn" onclick="filterReservations('cancelled')">Cancelled</button>
            </div>

            <!-- Reservations Grid -->
            <?php if (count($user_reservations) > 0): ?>
                <div class="reservations-grid" id="reservationsGrid">
                    <?php foreach ($user_reservations as $reservation): ?>
                        <?php
                        // Get offer details
                        $offer_details = null;
                        $offer_owner = null;
                        foreach ($offers as $offer) {
                            if ($offer['id'] == $reservation['offer_id']) {
                                $offer_details = $offer;
                                foreach ($users as $user) {
                                    if ($user['id'] == $offer['user_id']) {
                                        $offer_owner = $user;
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                        
                        if ($offer_details && $offer_owner):
                        ?>
                            <div class="reservation-card" data-status="<?php echo $reservation['status']; ?>">
                                <div class="reservation-header">
                                    <div class="route">
                                        🚗 <?php echo htmlspecialchars($reservation['from']); ?> → <?php echo htmlspecialchars($reservation['to']); ?>
                                    </div>
                                    <div class="reservation-status">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </div>
                                </div>
                                <div class="reservation-details">
                                    <div class="detail-row">
                                        <span class="detail-label">👤 Driver:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($offer_owner['first_name'] . ' ' . $offer_owner['last_name']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">📅 Date:</span>
                                        <span class="detail-value"><?php echo date('M d, Y', strtotime($reservation['date'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">🕐 Time:</span>
                                        <span class="detail-value"><?php echo date('h:i A', strtotime($reservation['time'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">👥 Seats:</span>
                                        <span class="detail-value"><?php echo $reservation['seats']; ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">💰 Total Price:</span>
                                        <span class="detail-value price"><?php echo number_format($offer_details['price'] * $reservation['seats'], 3); ?> TND</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">📞 Driver Phone:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($offer_details['phone']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">📅 Booked:</span>
                                        <span class="detail-value"><?php echo date('M d, Y', strtotime($reservation['created_at'])); ?></span>
                                    </div>
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <div class="reservation-actions">
                                            <button class="btn-cancel" onclick="cancelReservation(<?php echo $reservation['id']; ?>)">
                                                Cancel Reservation
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-reservations">
                    <h3>No Reservations Yet</h3>
                    <p>Start exploring available offers and book your first carpool ride!</p>
                    <a href="available_offers.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block; padding: 12px 25px; text-decoration: none;">
                        Browse Offers
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="notifications-section">
                <h3 style="margin-bottom: 20px; color: var(--primary-color);">🔔 Notifications</h3>
                <?php if (count($user_notifications) > 0): ?>
                    <?php foreach (array_slice($user_notifications, 0, 5) as $notification): ?>
                        <div class="notification-item <?php echo !$notification['read'] ? 'unread' : ''; ?>">
                            <div class="notification-icon">🔔</div>
                            <div class="notification-content">
                                <div class="notification-title"><?php echo htmlspecialchars($notification['titre']); ?></div>
                                <div class="notification-text"><?php echo htmlspecialchars($notification['contenu']); ?></div>
                                <div class="notification-date"><?php echo date('M d, Y H:i', strtotime($notification['date'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-notifications">
                        <h3>No notifications</h3>
                        <p>You don't have any notifications yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="js/common.js"></script>
    <script src="js/my_reservations.js"></script>
</body>
</html>
