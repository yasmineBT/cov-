<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$users_file = 'data/users.json';
$invoices_file = 'data/invoices.json';
$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';

$users = json_decode(file_get_contents($users_file), true);
$invoices = json_decode(file_get_contents($invoices_file), true);
$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);

$current_user = null;
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}

$user_invoices = array_filter($invoices, function($invoice) {
    return $invoice['user_id'] == $_SESSION['user_id'];
});

usort($user_invoices, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Invoices - CoRide</title>
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
        
        .invoices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .invoice-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .invoice-card:hover {
            transform: translateY(-5px);
        }
        
        .invoice-header {
            background: linear-gradient(135deg, var(--primary-color), var(--muted-color));
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .invoice-number {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .invoice-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .invoice-details {
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
        
        .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .no-invoices {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
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
            
            .invoices-grid {
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
                <li><a href="available_offers.php">🚗 Available Offers</a></li>
                <li><a href="my_offers.php">📝 My Offers</a></li>
                <li><a href="my_invoices.php" class="active">📄 My Invoices</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
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
                    <h1 class="page-title">My Invoices</h1>
                    <p>View and manage your payment invoices</p>
                </div>
            </div>

            
            <?php if (count($user_invoices) > 0): ?>
                <div class="invoices-grid">
                    <?php foreach ($user_invoices as $invoice): ?>
                        <?php
                        $route = 'Unknown';
                        foreach ($reservations as $reservation) {
                            if ($reservation['id'] == $invoice['reservation_id']) {
                                $route = $reservation['from'] . ' → ' . $reservation['to'];
                                break;
                            }
                        }
                        ?>
                        <div class="invoice-card">
                            <div class="invoice-header">
                                <div class="invoice-number"><?php echo htmlspecialchars($invoice['numero']); ?></div>
                                <div class="invoice-status <?php echo $invoice['status'] === 'paid' ? 'status-paid' : 'status-pending'; ?>">
                                    <?php echo ucfirst($invoice['status']); ?>
                                </div>
                            </div>
                            <div class="invoice-details">
                                <div class="detail-row">
                                    <span class="detail-label">📍 Route:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($route); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">📅 Date:</span>
                                    <span class="detail-value"><?php echo date('M d, Y', strtotime($invoice['date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">💰 Amount:</span>
                                    <span class="detail-value amount"><?php echo number_format($invoice['montant'], 3); ?> TND</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-invoices">
                    <h3>No Invoices Yet</h3>
                    <p>You don't have any invoices at the moment. Invoices are generated when your reservations are accepted.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
