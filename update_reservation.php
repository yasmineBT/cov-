<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

$reservation_id = $_POST['reservation_id'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($reservation_id) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID and status are required']);
    exit();
}

if (!in_array($status, ['accepted', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Load data
$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';

$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);

$reservation_found = false;
foreach ($reservations as &$reservation) {
    if ($reservation['id'] == $reservation_id) {
        
        $offer_owner = null;
        foreach ($offers as $offer) {
            if ($offer['id'] == $reservation['offer_id']) {
                $offer_owner = $offer['user_id'];
                break;
            }
        }
        
        if ($offer_owner != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You can only update reservations for your own offers']);
            exit();
        }
        
        $reservation['status'] = $status;
        $reservation_found = true;
        break;
    }
}

if (!$reservation_found) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit();
}


file_put_contents($reservations_file, json_encode($reservations, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Reservation updated successfully']);
?>
