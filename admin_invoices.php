<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$invoices_file = 'data/invoices.json';
$users_file = 'data/users.json';
$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';

$invoices = json_decode(file_get_contents($invoices_file), true);
$users = json_decode(file_get_contents($users_file), true);
$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$filtered_invoices = $invoices;

if ($search || $status_filter) {
    $filtered_invoices = array_filter($invoices, function($invoice) use ($search, $status_filter) {
        $match = true;
        
        if ($search) {
            $search_lower = strtolower($search);
            $match = $match && (
                strpos(strtolower($invoice['numero']), $search_lower) !== false
            );
        }
        
        if ($status_filter) {
            $match = $match && $invoice['status'] === $status_filter;
        }
        
        return $match;
    });
}

usort($filtered_invoices, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management - CoRide</title>
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
        
        .invoices-table {
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
        
        .invoice-count {
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
        
        .invoice-number {
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .invoice-amount {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .invoice-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
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
            
            .invoices-table {
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
                <li><a href="admin_reservations.php">📋 Reservations</a></li>
                <li><a href="admin_coupons.php">🎟️ Coupons</a></li>
                <li><a href="admin_invoices.php" class="active">📄 Invoices</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
            </ul>
        </aside>

        
        <main class="main-content">
            
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

            
            <div class="page-header">
                <div>
                    <h1 class="page-title">Invoice Management</h1>
                    <p>View and manage all invoices</p>
                </div>
            </div>

            
            <div class="search-section">
                <form method="GET" action="">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" placeholder="Search invoice number..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Status</option>
                                <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="admin_invoices.php" class="btn btn-secondary" style="margin-left: 10px;">Clear</a>
                </form>
            </div>

           
            <div class="invoices-table">
                <div class="table-header">
                    <div class="table-title">All Invoices</div>
                    <div class="invoice-count"><?php echo count($filtered_invoices); ?> invoices</div>
                </div>
                
                <?php if (count($filtered_invoices) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Route</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_invoices as $invoice): ?>
                                <?php
                                
                                $customer_name = 'Unknown';
                                foreach ($users as $user) {
                                    if ($user['id'] == $invoice['user_id']) {
                                        $customer_name = $user['first_name'] . ' ' . $user['last_name'];
                                        break;
                                    }
                                }
                                
                                $route = 'Unknown';
                                foreach ($reservations as $reservation) {
                                    if ($reservation['id'] == $invoice['reservation_id']) {
                                        $route = $reservation['from'] . ' → ' . $reservation['to'];
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="invoice-number"><?php echo htmlspecialchars($invoice['numero']); ?></td>
                                    <td><?php echo htmlspecialchars($customer_name); ?></td>
                                    <td><?php echo htmlspecialchars($route); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($invoice['date'])); ?></td>
                                    <td class="invoice-amount"><?php echo number_format($invoice['montant'], 3); ?> TND</td>
                                    <td>
                                        <span class="invoice-status status-<?php echo $invoice['status']; ?>">
                                            <?php echo ucfirst($invoice['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-invoices">
                        <h3>No invoices found</h3>
                        <p><?php echo ($search || $status_filter) ? 'Try adjusting your search criteria.' : 'No invoices generated yet.'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
