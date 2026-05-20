<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

$offer_id = $_GET['offer_id'] ?? '';

if (empty($offer_id)) {
    echo json_encode(['success' => false, 'message' => 'Offer ID is required']);
    exit();
}

$users_file = 'data/users.json';
$reservations_file = 'data/reservations.json';

$users = json_decode(file_get_contents($users_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);

$offer_reservations = array_filter($reservations, function($reservation) use ($offer_id) {
    return $reservation['offer_id'] == $offer_id;
});

$reservations_with_names = [];
foreach ($offer_reservations as $reservation) {
    $user_name = 'Unknown User';
    foreach ($users as $user) {
        if ($user['id'] == $reservation['user_id']) {
            $user_name = $user['first_name'] . ' ' . $user['last_name'];
            break;
        }
    }
    
    $reservations_with_names[] = [
        'id' => $reservation['id'],
        'user_name' => $user_name,
        'seats' => $reservation['seats'],
        'phone' => $reservation['phone'],
        'status' => $reservation['status']
    ];
}

echo json_encode(['success' => true, 'reservations' => $reservations_with_names]);
?>
