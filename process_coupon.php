<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'You must be an admin to create coupons']);
    exit();
}

$code = $_POST['code'] ?? '';
$percentage = $_POST['percentage'] ?? '';
$expiration = $_POST['expiration'] ?? '';

if (empty($code) || empty($percentage) || empty($expiration)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!is_numeric($percentage) || $percentage < 1 || $percentage > 100) {
    echo json_encode(['success' => false, 'message' => 'Percentage must be between 1 and 100']);
    exit();
}

$coupons_file = 'data/coupons.json';
$notifications_file = 'data/notifications.json';
$users_file = 'data/users.json';

$coupons = json_decode(file_get_contents($coupons_file), true);
$notifications = json_decode(file_get_contents($notifications_file), true);
$users = json_decode(file_get_contents($users_file), true);

foreach ($coupons as $coupon) {
    if (strtolower($coupon['code']) === strtolower($code)) {
        echo json_encode(['success' => false, 'message' => 'Coupon code already exists']);
        exit();
    }
}

$new_coupon = [
    'id' => count($coupons) + 1,
    'code' => strtoupper($code),
    'pourcentage' => intval($percentage),
    'dateExpiration' => $expiration,
    'created_at' => date('Y-m-d H:i:s')
];

$coupons[] = $new_coupon;
file_put_contents($coupons_file, json_encode($coupons, JSON_PRETTY_PRINT));

foreach ($users as $user) {
    if ($user['role'] === 'user') {
        $notification = [
            'id' => count($notifications) + 1,
            'user_id' => $user['id'],
            'titre' => 'New Coupon Available!',
            'contenu' => "Use coupon code {$new_coupon['code']} for {$new_coupon['pourcentage']}% discount. Valid until " . date('M d, Y', strtotime($expiration)),
            'date' => date('Y-m-d H:i:s'),
            'read' => false
        ];
        $notifications[] = $notification;
    }
}

file_put_contents($notifications_file, json_encode($notifications, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Coupon created successfully']);
?>
