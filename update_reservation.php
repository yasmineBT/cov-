<?php
session_start();

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


$offers_file = 'data/offers.json';
$reservations_file = 'data/reservations.json';
$notifications_file = 'data/notifications.json';
$invoices_file = 'data/invoices.json';

$offers = json_decode(file_get_contents($offers_file), true);
$reservations = json_decode(file_get_contents($reservations_file), true);
$notifications = json_decode(file_get_contents($notifications_file), true);
$invoices = json_decode(file_get_contents($invoices_file), true);


$reservation_found = false;
foreach ($reservations as &$reservation) {
    if ($reservation['id'] == $reservation_id) {

        $offer_owner = null;
        $offer_details = null;
        foreach ($offers as $offer) {
            if ($offer['id'] == $reservation['offer_id']) {
                $offer_owner = $offer['user_id'];
                $offer_details = $offer;
                break;
            }
        }
        
        if ($offer_owner != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You can only update reservations for your own offers']);
            exit();
        }
        
        $reservation['status'] = $status;
        $reservation_found = true;
        
        if ($status === 'accepted') {
            $notification = [
                'id' => count($notifications) + 1,
                'user_id' => $reservation['user_id'],
                'titre' => 'Reservation Accepted',
                'contenu' => "Your reservation for {$reservation['from']} to {$reservation['to']} on {$reservation['date']} has been accepted",
                'date' => date('Y-m-d H:i:s'),
                'read' => false
            ];
            $notifications[] = $notification;
            
            $invoice_numero = 'INV-' . str_pad(count($invoices) + 1, 6, '0', STR_PAD_LEFT);
            $montant = $offer_details['price'] * $reservation['seats'];
            
            $invoice = [
                'id' => count($invoices) + 1,
                'numero' => $invoice_numero,
                'reservation_id' => $reservation['id'],
                'user_id' => $reservation['user_id'],
                'montant' => $montant,
                'date' => date('Y-m-d'),
                'status' => 'pending'
            ];
            $invoices[] = $invoice;
        } elseif ($status === 'rejected') {
            $notification = [
                'id' => count($notifications) + 1,
                'user_id' => $reservation['user_id'],
                'titre' => 'Reservation Rejected',
                'contenu' => "Your reservation for {$reservation['from']} to {$reservation['to']} on {$reservation['date']} has been rejected",
                'date' => date('Y-m-d H:i:s'),
                'read' => false
            ];
            $notifications[] = $notification;
        }
        
        break;
    }
}

if (!$reservation_found) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit();
}

file_put_contents($reservations_file, json_encode($reservations, JSON_PRETTY_PRINT));
file_put_contents($notifications_file, json_encode($notifications, JSON_PRETTY_PRINT));
file_put_contents($invoices_file, json_encode($invoices, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Reservation updated successfully']);
?>
