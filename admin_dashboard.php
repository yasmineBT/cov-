<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


$users_file = 'data/users.json';
$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';

$users = json_decode(file_get_contents($users_file), true);
$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);


$current_user = null;
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}


$total_users = count($users);
$total_trips = count($offers);
$total_reservations = count($reservations);
$accepted_reservations = count(array_filter($reservations, function($r) { return $r['status'] === 'accepted'; }));
$rejected_reservations = count(array_filter($reservations, function($r) { return $r['status'] === 'rejected'; }));
$pending_reservations = count(array_filter($reservations, function($r) { return $r['status'] === 'pending'; }));


$trip_requests = [];
foreach ($reservations as $reservation) {
    $key = $reservation['from'] . ' to ' . $reservation['to'];
    if (!isset($trip_requests[$key])) {
        $trip_requests[$key] = 0;
    }
    $trip_requests[$key]++;
}
arsort($trip_requests);
$most_requested = array_slice($trip_requests, 0, 5, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CoRide</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .chart-card h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .recent-activity {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .activity-icon.user {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .activity-icon.trip {
            background: #f3e5f5;
            color: #9c27b0;
        }
        
        .activity-icon.reservation {
            background: #e8f5e8;
            color: var(--muted-color);
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
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="nav-logo">
                    <img src="images/logo.svg" alt="CoRide Logo" class="logo-img">
                    <span class="logo-text">CoRide</span>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="admin_users.php">👥 Users</a></li>
                <li><a href="admin_trips.php">🚗 Trips</a></li>
                <li><a href="admin_reservations.php">📋 Reservations</a></li>
                <li><a href="admin_coupons.php">🎟️ Coupons</a></li>
                <li><a href="admin_invoices.php">📄 Invoices</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
            </ul>
        </aside>

       
        <main class="main-content">
            
            <div class="top-navbar">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div style="font-weight: 600; font-size: 1.2rem; color: var(--primary-color);">Admin Dashboard</div>
                    <form method="POST" action="logout.php" style="display: inline;">
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                </div>
            </div>

           
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_trips; ?></div>
                    <div class="stat-label">Total Trips</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_reservations; ?></div>
                    <div class="stat-label">Total Reservations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $accepted_reservations; ?></div>
                    <div class="stat-label">Accepted Reservations</div>
                </div>
            </div>

           
            <div class="charts-container" style="grid-template-columns: 1fr 1fr; gap: 20px; max-width: 900px; margin: 0 auto 30px;">
                <div class="chart-card" style="padding: 20px;">
                    <h3 style="margin-bottom: 15px; font-size: 1.1rem;">Reservation Status</h3>
                    <div style="height: 250px; position: relative;">
                        <canvas id="reservationChart"></canvas>
                    </div>
                </div>
                <div class="chart-card" style="padding: 20px;">
                    <h3 style="margin-bottom: 15px; font-size: 1.1rem;">Most Requested Routes</h3>
                    <div style="height: 250px; position: relative;">
                        <canvas id="routesChart"></canvas>
                    </div>
                </div>
            </div>

            
            <div class="recent-activity">
                <h3 style="margin-bottom: 20px; color: var(--primary-color);">Recent Activity</h3>
                <?php
                
                $recent_users = array_slice(array_reverse($users), 0, 2);
                $recent_offers = array_slice(array_reverse($offers), 0, 2);
                $recent_reservations = array_slice(array_reverse($reservations), 0, 1);
                
                foreach ($recent_users as $user) {
                    echo '<div class="activity-item">
                        <div class="activity-icon user">👤</div>
                        <div>
                            <div style="font-weight: 600;">New User Registered</div>
                            <div style="font-size: 0.9rem; opacity: 0.7;">' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . ' - ' . date('M d, Y', strtotime($user['created_at'])) . '</div>
                        </div>
                    </div>';
                }
                
                foreach ($recent_offers as $offer) {
                    echo '<div class="activity-item">
                        <div class="activity-icon trip">🚗</div>
                        <div>
                            <div style="font-weight: 600;">New Trip Offered</div>
                            <div style="font-size: 0.9rem; opacity: 0.7;">' . htmlspecialchars($offer['from'] . ' to ' . $offer['to']) . ' - ' . date('M d, Y', strtotime($offer['date'])) . '</div>
                        </div>
                    </div>';
                }
                
                foreach ($recent_reservations as $reservation) {
                    echo '<div class="activity-item">
                        <div class="activity-icon reservation">📋</div>
                        <div>
                            <div style="font-weight: 600;">New Reservation</div>
                            <div style="font-size: 0.9rem; opacity: 0.7;">' . htmlspecialchars($reservation['from'] . ' to ' . $reservation['to']) . ' - ' . ucfirst($reservation['status']) . '</div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </main>
    </div>

    
    <div id="acceptedCount" style="display: none;"><?php echo $accepted_reservations; ?></div>
    <div id="rejectedCount" style="display: none;"><?php echo $rejected_reservations; ?></div>
    <div id="pendingCount" style="display: none;"><?php echo $pending_reservations; ?></div>
    <div id="routeLabels" style="display: none;"><?php echo json_encode(array_keys($most_requested)); ?></div>
    <div id="routeData" style="display: none;"><?php echo json_encode(array_values($most_requested)); ?></div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/common.js"></script>
    <script src="js/admin_dashboard.js"></script>
</body>
</html>
