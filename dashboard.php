<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$users_file = 'data/users.json';
$users = json_decode(file_get_contents($users_file), true);
$current_user = null;
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}

if ($current_user['role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit();
} else {
    header('Location: available_offers.php');
    exit();
}
?>
