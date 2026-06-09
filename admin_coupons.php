<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$coupons_file = 'data/coupons.json';
$coupons = json_decode(file_get_contents($coupons_file), true);

if (isset($_GET['delete_coupon'])) {
    $coupon_id = intval($_GET['delete_coupon']);
    
    $coupons = array_filter($coupons, function($coupon) use ($coupon_id) {
        return $coupon['id'] != $coupon_id;
    });
    $coupons = array_values($coupons);
    file_put_contents($coupons_file, json_encode($coupons, JSON_PRETTY_PRINT));
    
    header('Location: admin_coupons.php?success=Coupon deleted successfully');
    exit();
}

$search = $_GET['search'] ?? '';
$filtered_coupons = $coupons;

if ($search) {
    $filtered_coupons = array_filter($coupons, function($coupon) use ($search) {
        $search_lower = strtolower($search);
        return strpos(strtolower($coupon['code']), $search_lower) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupon Management - CoRide</title>
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
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: end;
        }
        
        .search-group {
            flex: 1;
        }
        
        .search-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .search-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .search-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .coupons-table {
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
        
        .coupon-count {
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
        
        .coupon-code {
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 1.1rem;
        }
        
        .coupon-percentage {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .coupon-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-delete {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-delete:hover {
            background: #a03048;
        }
        
        .no-coupons {
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
        
        .add-coupon-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-color);
        }
        
        .form-group input {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .coupons-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 600px;
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
                <li><a href="admin_coupons.php" class="active">🎟️ Coupons</a></li>
                <li><a href="admin_invoices.php">📄 Invoices</a></li>
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
                    <h1 class="page-title">Coupon Management</h1>
                    <p>Manage discount coupons</p>
                </div>
            </div>

            
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

            
            <div class="add-coupon-section">
                <h3 style="margin-bottom: 20px; color: var(--primary-color);">Add New Coupon</h3>
                <form method="POST" action="process_coupon.php">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="code">Coupon Code</label>
                            <input type="text" id="code" name="code" placeholder="e.g., SUMMER2024" required>
                        </div>
                        <div class="form-group">
                            <label for="percentage">Discount Percentage (%)</label>
                            <input type="number" id="percentage" name="percentage" min="1" max="100" placeholder="e.g., 20" required>
                        </div>
                        <div class="form-group">
                            <label for="expiration">Expiration Date</label>
                            <input type="date" id="expiration" name="expiration" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Coupon</button>
                </form>
            </div>

            
            <div class="search-section">
                <form method="GET" action="" class="search-form">
                    <div class="search-group">
                        <label for="search">Search Coupons</label>
                        <input type="text" id="search" name="search" placeholder="Search by code..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="admin_coupons.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            
            <div class="coupons-table">
                <div class="table-header">
                    <div class="table-title">All Coupons</div>
                    <div class="coupon-count"><?php echo count($filtered_coupons); ?> coupons</div>
                </div>
                
                <?php if (count($filtered_coupons) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Expiration Date</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_coupons as $coupon): ?>
                                <?php
                                $is_expired = strtotime($coupon['dateExpiration']) < time();
                                $status_class = $is_expired ? 'status-expired' : 'status-active';
                                $status_text = $is_expired ? 'Expired' : 'Active';
                                ?>
                                <tr>
                                    <td>#<?php echo $coupon['id']; ?></td>
                                    <td class="coupon-code"><?php echo htmlspecialchars($coupon['code']); ?></td>
                                    <td class="coupon-percentage"><?php echo $coupon['pourcentage']; ?>%</td>
                                    <td><?php echo date('M d, Y', strtotime($coupon['dateExpiration'])); ?></td>
                                    <td>
                                        <span class="coupon-status <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($coupon['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin_coupons.php?delete_coupon=<?php echo $coupon['id']; ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('Are you sure you want to delete this coupon?')">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-coupons">
                        <h3>No coupons found</h3>
                        <p><?php echo $search ? 'Try adjusting your search criteria.' : 'No coupons created yet.'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
