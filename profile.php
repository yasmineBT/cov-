<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// transforme dtat en php
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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    $errors = [];
    
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    
    // Password validation (only if trying to change)
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if ($current_password !== $current_user['password']) {
            $errors[] = "Current password is incorrect";
        }
        if (empty($new_password)) {
            $errors[] = "New password is required";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }
    
    if (empty($errors)) {
        // Update user data
        foreach ($users as &$user) {
            if ($user['id'] == $_SESSION['user_id']) {
                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['phone'] = $phone;
                
                if (!empty($new_password)) {
                    $user['password'] = $new_password;
                }
                
                break;
            }
        }
        
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        
        // Update session
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        
        header('Location: profile.php?success=Profile updated successfully');
        exit();
    }
}

// Get user statistics
$user_offers = array_filter($offers, function($offer) {
    return $offer['user_id'] == $_SESSION['user_id'];
});

$user_reservations = array_filter($reservations, function($reservation) {
    return $reservation['user_id'] == $_SESSION['user_id'];
});

$stats = [
    'total_offers' => count($user_offers),
    'total_reservations' => count($user_reservations),
    'accepted_reservations' => count(array_filter($user_reservations, function($r) { return $r['status'] === 'accepted'; })),
    'pending_reservations' => count(array_filter($user_reservations, function($r) { return $r['status'] === 'pending'; }))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CoRide</title>
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
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--muted-color));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profile-email {
            opacity: 0.9;
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .info-group {
            margin-bottom: 25px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .info-value {
            color: var(--text-dark);
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 10px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--text-dark);
            font-weight: 500;
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
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .btn-save {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .btn-save:hover {
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
        
        .error-message {
            background: #fee;
            color: var(--secondary-color);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .error-message ul {
            margin: 0;
            padding-left: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .profile-grid {
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
                <?php if ($current_user['role'] === 'admin'): ?>
                    <li><a href="admin_dashboard.php">📊 Dashboard</a></li>
                    <li><a href="admin_users.php">👥 Users</a></li>
                    <li><a href="admin_trips.php">🚗 Trips</a></li>
                    <li><a href="admin_reservations.php">📋 Reservations</a></li>
                    <li><a href="profile.php" class="active">👤 Profile</a></li>
                <?php else: ?>
                    <li><a href="my_reservations.php">📋 My Reservations</a></li>
                    <li><a href="available_offers.php">🚗 Available Offers</a></li>
                    <li><a href="my_offers.php">📝 My Offers</a></li>
                    <li><a href="profile.php" class="active">👤 Profile</a></li>
                <?php endif; ?>
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
                        <div style="font-size: 0.9rem; opacity: 0.7;"><?php echo ucfirst($current_user['role']); ?></div>
                    </div>
                </div>
                <form method="POST" action="logout.php" style="display: inline;">
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </div>

            <!-- Profile Grid -->
            <div class="profile-grid">
                <!-- Profile Information Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?>
                        </div>
                        <div class="profile-name"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                        <div class="profile-email"><?php echo htmlspecialchars($current_user['email']); ?></div>
                    </div>
                    <div class="profile-body">
                        <div class="info-group">
                            <div class="info-label">Member Since</div>
                            <div class="info-value"><?php echo date('F j, Y', strtotime($current_user['created_at'])); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($current_user['phone']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Account Status</div>
                            <div class="info-value">
                                <span style="background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 20px; font-size: 0.9rem;">
                                    Active
                                </span>
                            </div>
                        </div>
                        
                        <h3 style="margin-bottom: 20px; color: var(--primary-color);">Your Statistics</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $stats['total_offers']; ?></div>
                                <div class="stat-label">Offers Created</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $stats['total_reservations']; ?></div>
                                <div class="stat-label">Reservations Made</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $stats['accepted_reservations']; ?></div>
                                <div class="stat-label">Accepted Trips</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $stats['pending_reservations']; ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        <h3>Edit Profile</h3>
                    </div>
                    <div class="profile-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div style="background: #e8f5e8; color: var(--muted-color); padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid var(--muted-color);">
                                <?php echo htmlspecialchars($_GET['success']); ?>
                            </div>
                        <?php endif; ?>
                        
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
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" required 
                                       value="<?php echo htmlspecialchars($current_user['first_name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" required 
                                       value="<?php echo htmlspecialchars($current_user['last_name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required 
                                       value="<?php echo htmlspecialchars($current_user['phone']); ?>">
                            </div>
                            
                            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                            
                            <h4 style="margin-bottom: 20px; color: var(--primary-color);">Change Password</h4>
                            <p style="margin-bottom: 20px; opacity: 0.7;">Leave blank if you don't want to change your password</p>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <button type="submit" class="btn-save">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
