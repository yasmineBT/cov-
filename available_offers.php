<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Load data
$users_file = 'data/users.json';
$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';

$users = json_decode(file_get_contents($users_file), true);
$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);

// Get current user
$current_user = null;
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}
$available_offers = [];
foreach ($offers as $offer) {
    if ($offer['user_id'] != $_SESSION['user_id']) {
        // Count reserved seats for this offer
        $reserved_seats = 0;
        foreach ($reservations as $reservation) {
            if ($reservation['offer_id'] == $offer['id'] && $reservation['status'] !== 'rejected') {
                $reserved_seats += $reservation['seats'];
            }
        }
        
        if ($reserved_seats < $offer['seats']) {
            $offer['available_seats'] = $offer['seats'] - $reserved_seats;
            $available_offers[] = $offer;
        }
    }
}

$search = $_GET['search'] ?? '';
$from_filter = $_GET['from'] ?? '';
$to_filter = $_GET['to'] ?? '';
$date_filter = $_GET['date'] ?? '';

if ($search || $from_filter || $to_filter || $date_filter) {
    $available_offers = array_filter($available_offers, function($offer) use ($search, $from_filter, $to_filter, $date_filter) {
        $match = true;
        
        if ($search) {
            $search_lower = strtolower($search);
            $match = $match && (
                strpos(strtolower($offer['from']), $search_lower) !== false ||
                strpos(strtolower($offer['to']), $search_lower) !== false ||
                strpos(strtolower($offer['description']), $search_lower) !== false
            );
        }
        
        if ($from_filter) {
            $match = $match && strpos(strtolower($offer['from']), strtolower($from_filter)) !== false;
        }
        
        if ($to_filter) {
            $match = $match && strpos(strtolower($offer['to']), strtolower($to_filter)) !== false;
        }
        
        if ($date_filter) {
            $match = $match && $offer['date'] === $date_filter;
        }
        
        return $match;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Offers - CoRide</title>
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
        
        .filters-section {
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
        
        .filter-group input {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .filter-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .offer-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .offer-card:hover {
            transform: translateY(-5px);
        }
        
        .offer-header {
            background: linear-gradient(135deg, var(--primary-color), var(--muted-color));
            color: white;
            padding: 20px;
        }
        
        .route {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .offer-details {
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
        
        .btn-reserve {
            width: 100%;
            padding: 12px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 15px;
        }
        
        .btn-reserve:hover {
            background: #a03048;
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
        
        .no-offers {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .no-offers h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .offers-grid {
                grid-template-columns: 1fr;
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
                <li><a href="my_reservations.php">📋 My Reservations</a></li>
                <li><a href="available_offers.php" class="active">🚗 Available Offers</a></li>
                <li><a href="my_offers.php">📝 My Offers</a></li>
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
                <h1 class="page-title">Available Offers</h1>
                <p>Find and book carpool rides that match your route</p>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" action="">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" placeholder="Search routes..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="from">From</label>
                            <input type="text" id="from" name="from" placeholder="Departure city" value="<?php echo htmlspecialchars($from_filter); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="to">To</label>
                            <input type="text" id="to" name="to" placeholder="Destination city" value="<?php echo htmlspecialchars($to_filter); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="date">Date</label>
                            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="available_offers.php" class="btn btn-secondary" style="margin-left: 10px;">Clear</a>
                </form>
            </div>

            <!-- Offers Grid -->
            <?php if (count($available_offers) > 0): ?>
                <div class="offers-grid">
                    <?php foreach ($available_offers as $offer): ?>
                        <?php
                        // Get offer owner info
                        $offer_owner = null;
                        foreach ($users as $user) {
                            if ($user['id'] == $offer['user_id']) {
                                $offer_owner = $user;
                                break;
                            }
                        }
                        ?>
                        <div class="offer-card">
                            <div class="offer-header">
                                <div class="route">
                                    🚗 <?php echo htmlspecialchars($offer['from']); ?> → <?php echo htmlspecialchars($offer['to']); ?>
                                </div>
                                <div style="opacity: 0.9; font-size: 0.9rem;">
                                    Driver: <?php echo htmlspecialchars($offer_owner['first_name'] . ' ' . $offer_owner['last_name']); ?>
                                </div>
                            </div>
                            <div class="offer-details">
                                <div class="detail-row">
                                    <span class="detail-label">📅 Date:</span>
                                    <span class="detail-value"><?php echo date('M d, Y', strtotime($offer['date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">🕐 Time:</span>
                                    <span class="detail-value"><?php echo date('h:i A', strtotime($offer['time'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">👥 Available Seats:</span>
                                    <span class="detail-value"><?php echo $offer['available_seats']; ?> / <?php echo $offer['seats']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">💰 Price:</span>
                                    <span class="detail-value price"><?php echo number_format($offer['price'], 3); ?> TND</span>
                                </div>
                                <?php if (!empty($offer['description'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">📝 Description:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars(substr($offer['description'], 0, 50)) . '...'; ?></span>
                                </div>
                                <?php endif; ?>
                                <button class="btn-reserve" onclick="openReservationModal(<?php echo $offer['id']; ?>, '<?php echo htmlspecialchars($offer['from']); ?>', '<?php echo htmlspecialchars($offer['to']); ?>', <?php echo $offer['available_seats']; ?>)">
                                    Reserve Now
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-offers">
                    <h3>No Available Offers</h3>
                    <p>There are no available offers matching your criteria. Try adjusting your filters or check back later.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Reservation Modal -->
    <div id="reservationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Make Reservation</h3>
                <span class="close" onclick="closeReservationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="reservationForm">
                    <input type="hidden" id="offer_id" name="offer_id">
                    <div class="form-group">
                        <label>Route:</label>
                        <div id="route_info" style="font-weight: 600; color: var(--primary-color); margin-bottom: 15px;"></div>
                    </div>
                    <div class="form-group">
                        <label for="seats">Number of Seats:</label>
                        <select id="seats" name="seats" required>
                            <option value="">Select seats</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($current_user['phone']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Confirm Reservation</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
        
        .close:hover {
            opacity: 0.7;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
    </style>

    <script src="js/common.js"></script>
    <script src="js/available_offers.js"></script>
</body>
</html>
