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

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$from_filter = $_GET['from'] ?? '';
$to_filter = $_GET['to'] ?? '';

$filtered_reservations = $reservations;

if ($search || $status_filter || $from_filter || $to_filter) {
    $filtered_reservations = array_filter($reservations, function($reservation) use ($search, $status_filter, $from_filter, $to_filter) {
        $match = true;
        
        if ($search) {
            $search_lower = strtolower($search);
            $match = $match && (
                strpos(strtolower($reservation['from']), $search_lower) !== false ||
                strpos(strtolower($reservation['to']), $search_lower) !== false
            );
        }
        
        if ($status_filter) {
            $match = $match && $reservation['status'] === $status_filter;
        }
        
        if ($from_filter) {
            $match = $match && strpos(strtolower($reservation['from']), strtolower($from_filter)) !== false;
        }
        
        if ($to_filter) {
            $match = $match && strpos(strtolower($reservation['to']), strtolower($to_filter)) !== false;
        }
        
        return $match;
    });
}

usort($filtered_reservations, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management - CoRide</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .search-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-color);
        }
        
        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .reservations-table {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .table-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .reservation-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--primary-color);
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .route {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .reservation-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .price {
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .no-reservations {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-dark);
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
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .reservations-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 900px;
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
                <li><a href="admin_dashboard.php">📊 Dashboard</a></li>
                <li><a href="admin_users.php">👥 Users</a></li>
                <li><a href="admin_trips.php">🚗 Trips</a></li>
                <li><a href="admin_reservations.php" class="active">📋 Reservations</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <div>
                        <div style="font-weight: 600;">Admin User</div>
                        <div style="font-size: 0.9rem; opacity: 0.7;">Administrator</div>
                    </div>
                </div>
                <form method="POST" action="logout.php" style="display: inline;">
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Reservation Management</h1>
                    <p>View and manage all carpool reservations</p>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <form method="GET" action="">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" placeholder="Search reservations..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="from">From</label>
                            <input type="text" id="from" name="from" placeholder="Departure city" value="<?php echo htmlspecialchars($from_filter); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="to">To</label>
                            <input type="text" id="to" name="to" placeholder="Destination city" value="<?php echo htmlspecialchars($to_filter); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="admin_reservations.php" class="btn btn-secondary" style="margin-left: 10px;">Clear</a>
                </form>
            </div>

            <!-- Reservations Table -->
            <div class="reservations-table">
                <div class="table-header">
                    <div class="table-title">All Reservations</div>
                    <div class="reservation-count"><?php echo count($filtered_reservations); ?> reservations</div>
                </div>
                
                <?php if (count($filtered_reservations) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Route</th>
                                <th>Passenger</th>
                                <th>Driver</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Seats</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Booked</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_reservations as $reservation): ?>
                                <?php
                                $passenger_name = 'Unknown';
                                foreach ($users as $user) {
                                    if ($user['id'] == $reservation['user_id']) {
                                        $passenger_name = $user['first_name'] . ' ' . $user['last_name'];
                                        break;
                                    }
                                }
                                
                                $driver_name = 'Unknown';
                                $offer_price = 0;
                                foreach ($offers as $offer) {
                                    if ($offer['id'] == $reservation['offer_id']) {
                                        $offer_price = $offer['price'];
                                        foreach ($users as $user) {
                                            if ($user['id'] == $offer['user_id']) {
                                                $driver_name = $user['first_name'] . ' ' . $user['last_name'];
                                                break;
                                            }
                                        }
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td>#<?php echo $reservation['id']; ?></td>
                                    <td class="route">
                                        <?php echo htmlspecialchars($reservation['from']); ?> → <?php echo htmlspecialchars($reservation['to']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($passenger_name); ?></td>
                                    <td><?php echo htmlspecialchars($driver_name); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($reservation['date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($reservation['time'])); ?></td>
                                    <td><?php echo $reservation['seats']; ?></td>
                                    <td class="price"><?php echo number_format($offer_price * $reservation['seats'], 3); ?> TND</td>
                                    <td>
                                        <span class="reservation-status status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($reservation['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-reservations">
                        <h3>No reservations found</h3>
                        <p><?php echo ($search || $status_filter || $from_filter || $to_filter) ? 'Try adjusting your search criteria.' : 'No reservations made yet.'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
