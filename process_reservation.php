<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to make a reservation']);
    exit();
}


$offer_id = $_POST['offer_id'] ?? '';
$seats = $_POST['seats'] ?? '';
$phone = $_POST['phone'] ?? '';


if (empty($offer_id) || empty($seats) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}


$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';
$notifications_file = 'data/notifications.json';

$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);
$notifications = json_decode(file_get_contents($notifications_file), true);


$offer = null;
foreach ($offers as $o) {
    if ($o['id'] == $offer_id) {
        $offer = $o;
        break;
    }
}

if (!$offer) {
    echo json_encode(['success' => false, 'message' => 'Offer not found']);
    exit();
}


if ($offer['user_id'] == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot reserve your own offer']);
    exit();
}


$reserved_seats = 0;
foreach ($reservations as $reservation) {
    if ($reservation['offer_id'] == $offer_id && $reservation['status'] !== 'rejected') {
        $reserved_seats += $reservation['seats'];
    }
}

if ($reserved_seats + $seats > $offer['seats']) {
    echo json_encode(['success' => false, 'message' => 'Not enough seats available']);
    exit();
}


$new_reservation = [
    'id' => count($reservations) + 1,
    'offer_id' => $offer_id,
    'user_id' => $_SESSION['user_id'],
    'from' => $offer['from'],
    'to' => $offer['to'],
    'date' => $offer['date'],
    'time' => $offer['time'],
    'seats' => $seats,
    'phone' => $phone,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];

$reservations[] = $new_reservation;

file_put_contents($reservations_file, json_encode($reservations, JSON_PRETTY_PRINT));

$notification = [
    'id' => count($notifications) + 1,
    'user_id' => $offer['user_id'],
    'titre' => 'New Reservation Request',
    'contenu' => "You have a new reservation request for {$offer['from']} to {$offer['to']} on {$offer['date']}",
    'date' => date('Y-m-d H:i:s'),
    'read' => false
];
$notifications[] = $notification;
file_put_contents($notifications_file, json_encode($notifications, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Reservation created successfully']);
?>
