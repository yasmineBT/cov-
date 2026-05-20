<?php
session_start();

if (!isset($_SESSION['user_id'])) {
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

$user_offers = array_filter($offers, function($offer) {
    return $offer['user_id'] == $_SESSION['user_id'];
});

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_offer'])) {
    $from = trim($_POST['from']);
    $to = trim($_POST['to']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $seats = intval($_POST['seats']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $phone = trim($_POST['phone']);
    
    $errors = [];
    if (empty($from)) $errors[] = "Departure location is required";
    if (empty($to)) $errors[] = "Destination is required";
    if (empty($date)) $errors[] = "Date is required";
    if (empty($time)) $errors[] = "Time is required";
    if (empty($seats) || $seats < 1) $errors[] = "Number of seats must be at least 1";
    if (empty($price) || $price < 0) $errors[] = "Price must be a positive number";
    if (empty($phone)) $errors[] = "Phone number is required";
    
    if (empty($errors)) {
        $new_offer = [
            'id' => count($offers) + 1,
            'user_id' => $_SESSION['user_id'],
            'from' => $from,
            'to' => $to,
            'date' => $date,
            'time' => $time,
            'seats' => $seats,
            'price' => $price,
            'description' => $description,
            'phone' => $phone,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $offers[] = $new_offer;
        file_put_contents($offers_file, json_encode($offers, JSON_PRETTY_PRINT));
        
        header('Location: my_offers.php?success=Offer created successfully');
        exit();
    }
}

if (isset($_GET['delete_offer'])) {
    $offer_id = intval($_GET['delete_offer']);
    $offers = array_filter($offers, function($offer) use ($offer_id) {
        return !($offer['id'] == $offer_id && $offer['user_id'] == $_SESSION['user_id']);
    });
    $offers = array_values($offers); // Re-index array
    file_put_contents($offers_file, json_encode($offers, JSON_PRETTY_PRINT));
    
    header('Location: my_offers.php?success=Offer deleted successfully');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Offers - CoRide</title>
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
        
        .btn-add {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-add:hover {
            background: #a03048;
            transform: translateY(-2px);
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
            cursor: pointer;
        }
        
        .offer-card:hover {
            transform: translateY(-5px);
        }
        
        .offer-header {
            background: linear-gradient(135deg, var(--primary-color), var(--muted-color));
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .route {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .offer-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        
        .btn-action:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .btn-delete:hover {
            background: var(--secondary-color);
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
        
        .reservations-info {
            background: var(--bg-light);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .reservations-info h4 {
            margin-bottom: 10px;
            color: var(--primary-color);
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
            
            .offers-grid {
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
                <li><a href="my_reservations.php">📋 My Reservations</a></li>
                <li><a href="available_offers.php">🚗 Available Offers</a></li>
                <li><a href="my_offers.php" class="active">📝 My Offers</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
            </ul>
        </aside>

        <main class="main-content">
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

            <div class="page-header">
                <div>
                    <h1 class="page-title">My Offers</h1>
                    <p>Manage your carpool offers and view reservations</p>
                </div>
                <button class="btn-add" onclick="openCreateOfferModal()">
                    <span>+</span> Add Offer
                </button>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: #e8f5e8; color: var(--muted-color); padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid var(--muted-color);">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div style="background: #ffeaea; color: #d32f2f; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #d32f2f;">
                    <strong>Error:</strong><br>
                    <?php foreach ($errors as $error): ?>
                        • <?php echo htmlspecialchars($error); ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($user_offers) > 0): ?>
                <div class="offers-grid">
                    <?php foreach ($user_offers as $offer): ?>
                        <?php
                        // Count reservations for this offer
                        $offer_reservations = array_filter($reservations, function($reservation) use ($offer) {
                            return $reservation['offer_id'] == $offer['id'];
                        });
                        
                        $pending_count = count(array_filter($offer_reservations, function($r) { return $r['status'] === 'pending'; }));
                        $accepted_count = count(array_filter($offer_reservations, function($r) { return $r['status'] === 'accepted'; }));
                        $total_reserved = array_sum(array_column($offer_reservations, 'seats'));
                        ?>
                        <div class="offer-card" onclick="showReservations(<?php echo $offer['id']; ?>)">
                            <div class="offer-header">
                                <div class="offer-actions">
                                    <button class="btn-action btn-delete" onclick="event.stopPropagation(); deleteOffer(<?php echo $offer['id']; ?>)">🗑️</button>
                                </div>
                                <div class="route">
                                    🚗 <?php echo htmlspecialchars($offer['from']); ?> → <?php echo htmlspecialchars($offer['to']); ?>
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
                                    <span class="detail-label">👥 Seats:</span>
                                    <span class="detail-value"><?php echo $total_reserved; ?> / <?php echo $offer['seats']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">💰 Price:</span>
                                    <span class="detail-value price"><?php echo number_format($offer['price'], 3); ?> TND</span>
                                </div>
                                <div class="reservations-info">
                                    <h4>Reservations</h4>
                                    <div style="display: flex; gap: 15px;">
                                        <span>🟡 Pending: <?php echo $pending_count; ?></span>
                                        <span>🟢 Accepted: <?php echo $accepted_count; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-offers">
                    <h3>No Offers Yet</h3>
                    <p>Start offering rides to earn money and help others travel!</p>
                    <button class="btn-add" onclick="openCreateOfferModal()" style="margin-top: 20px;">
                        <span>+</span> Create Your First Offer
                    </button>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div id="createOfferModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Offer</h3>
                <span class="close" onclick="closeCreateOfferModal()">&times;</span>
            </div>
            <div class="modal-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="create_offer" value="1">
                    <div class="form-group">
                        <label for="from">From:</label>
                        <input type="text" id="from" name="from" required>
                    </div>
                    <div class="form-group">
                        <label for="to">To:</label>
                        <input type="text" id="to" name="to" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="time">Time:</label>
                        <input type="time" id="time" name="time" required>
                    </div>
                    <div class="form-group">
                        <label for="seats">Number of Seats:</label>
                        <input type="number" id="seats" name="seats" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price per Seat ($):</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($current_user['phone']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Offer</button>
                </form>
            </div>
        </div>
    </div>

    <div id="reservationsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Offer Reservations</h3>
                <span class="close" onclick="closeReservationsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="reservationsList"></div>
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
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .error-message {
            background: #fee;
            color: var(--secondary-color);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .reservation-item {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
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
        
        .reservation-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-accept {
            background: var(--muted-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-reject {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>

    <script src="js/common.js"></script>
    <script src="js/my_offers.js"></script>
</body>
</html>
