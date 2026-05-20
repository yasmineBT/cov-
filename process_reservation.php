<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to make a reservation']);
    exit();
}

// Get POST data
$offer_id = $_POST['offer_id'] ?? '';
$seats = $_POST['seats'] ?? '';
$phone = $_POST['phone'] ?? '';

// Validation
if (empty($offer_id) || empty($seats) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Load data
$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';

$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);

// Find the offer
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

// Check if user is trying to reserve their own offer
if ($offer['user_id'] == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot reserve your own offer']);
    exit();
}

// Check available seats
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

// Create new reservation
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

// Save reservations
file_put_contents($reservations_file, json_encode($reservations, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Reservation created successfully']);
?>
