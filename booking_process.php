<?php
session_start();

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

// 2. CONNECT
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CAPTURE ALL INPUTS
    $userId = $_SESSION['user_id'];
    $tourId = $_POST['tour_id'];
    $eventId = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
    $travelDate = $_POST['travel_date'];
    $pax = $_POST['pax'];
    $pricePerHead = $_POST['price_per_head'];
    $totalPrice = $pax * $pricePerHead;

    // NEW FIELDS
    $joinerName = $_POST['joiner_name']; // <--- CAPTURE JOINER NAME
    $contactNumber = $_POST['contact_number'];
    $pickupLocation = $_POST['pickup_location'];
    $paymentMethod = $_POST['payment_method'];
    $notes = $_POST['notes'];

    // --- TRANSACTION START ---
    sqlsrv_begin_transaction($conn);

    // A. INSERT BOOKING (Updated to include JoinerName and new columns)
    $sql_book = "INSERT INTO BOOKINGS_1 
                 (UserID, TourID, EventID, TravelDate, Pax, TotalPrice, Status, JoinerName, ContactNumber, PickupLocation, PaymentMethod, Notes) 
                 VALUES (?, ?, ?, ?, ?, ?, 'Confirmed', ?, ?, ?, ?, ?)";

    $params_book = array($userId, $tourId, $eventId, $travelDate, $pax, $totalPrice, $joinerName, $contactNumber, $pickupLocation, $paymentMethod, $notes);

    $stmt_book = sqlsrv_query($conn, $sql_book, $params_book);

    if ($stmt_book) {

        // B. UPDATE SLOTS (ONLY IF IT'S AN EVENT)
        if ($eventId) {
            $sql_update = "UPDATE EVENTS_1 
                           SET TakenSlots = TakenSlots + ? 
                           WHERE EventID = ?";
            $params_update = array($pax, $eventId);
            $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

            if (!$stmt_update) {
                sqlsrv_rollback($conn);
                die("Error updating slots: " . print_r(sqlsrv_errors(), true));
            }
        }

        // C. COMMIT
        sqlsrv_commit($conn);

        // REDIRECT (Success)
        echo "<script>
                alert('Booking Confirmed for " . addslashes($joinerName) . "! See you at " . addslashes($pickupLocation) . ".');
                window.location.href = 'my_bookings.php'; 
              </script>";

    } else {
        sqlsrv_rollback($conn);
        die("Booking failed: " . print_r(sqlsrv_errors(), true));
    }
}
?>