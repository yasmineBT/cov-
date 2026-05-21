<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$users_file = 'data/users.json';
$users = json_decode(file_get_contents($users_file), true);

if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    
    foreach ($users as $user) {
        if ($user['id'] == $user_id && $user['role'] === 'admin') {
            header('Location: admin_users.php?error=Cannot delete admin user');
            exit();
        }
    }
    
    $users = array_filter($users, function($user) use ($user_id) {
        return $user['id'] != $user_id;
    });
    $users = array_values($users); 
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
    
    header('Location: admin_users.php?success=User deleted successfully');
    exit();
}

if (isset($_GET['toggle_status'])) {
    $user_id = intval($_GET['toggle_status']);
    
    foreach ($users as &$user) {
        if ($user['id'] == $user_id && $user['role'] !== 'admin') {
            $user['status'] = $user['status'] === 'active' ? 'inactive' : 'active';
            break;
        }
    }
    
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
    
    header('Location: admin_users.php?success=User status updated successfully');
    exit();
}

$search = $_GET['search'] ?? '';
$filtered_users = $users;

if ($search) {
    $filtered_users = array_filter($users, function($user) use ($search) {
        $search_lower = strtolower($search);
        return strpos(strtolower($user['first_name']), $search_lower) !== false ||
               strpos(strtolower($user['last_name']), $search_lower) !== false ||
               strpos(strtolower($user['email']), $search_lower) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CoRide</title>
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
        
        .users-table {
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
        
        .user-count {
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
        
        .user-role {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .role-admin {
            background: #ffeaa7;
            color: #d63031;
        }
        
        .role-user {
            background: #dfe6e9;
            color: #2d3436;
        }
        
        .user-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
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
        
        .btn-toggle {
            background: var(--muted-color);
            color: white;
        }
        
        .btn-toggle:hover {
            background: #5a6f60;
        }
        
        .btn-delete {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-delete:hover {
            background: #a03048;
        }
        
        .btn-delete:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .no-users {
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
            
            .search-form {
                flex-direction: column;
            }
            
            .users-table {
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
                <li><a href="admin_users.php" class="active">👥 Users</a></li>
                <li><a href="admin_trips.php">🚗 Trips</a></li>
                <li><a href="admin_reservations.php">📋 Reservations</a></li>
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
                    <h1 class="page-title">User Management</h1>
                    <p>Manage user accounts and permissions</p>
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

            <div class="search-section">
                <form method="GET" action="" class="search-form">
                    <div class="search-group">
                        <label for="search">Search Users</label>
                        <input type="text" id="search" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="admin_users.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            <div class="users-table">
                <div class="table-header">
                    <div class="table-title">All Users</div>
                    <div class="user-count"><?php echo count($filtered_users); ?> users</div>
                </div>
                
                <?php if (count($filtered_users) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_users as $user): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <span class="user-role <?php echo $user['role'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="user-status <?php echo $user['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <a href="admin_users.php?toggle_status=<?php echo $user['id']; ?>" class="btn-action btn-toggle">
                                                    <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </a>
                                                <a href="admin_users.php?delete_user=<?php echo $user['id']; ?>" 
                                                   class="btn-action btn-delete"
                                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                                    Delete
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #999; font-size: 0.9rem;">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-users">
                        <h3>No users found</h3>
                        <p><?php echo $search ? 'Try adjusting your search criteria.' : 'No users registered yet.'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
