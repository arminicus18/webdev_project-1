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

    $userId = $_SESSION['user_id'];
    $tourId = $_POST['tour_id'];
    $eventId = !empty($_POST['event_id']) ? $_POST['event_id'] : null; // Handle NULL if private
    $travelDate = $_POST['travel_date'];
    $pax = $_POST['pax'];
    $pricePerHead = $_POST['price_per_head'];
    $totalPrice = $pax * $pricePerHead;

    // --- TRANSACTION START (To ensure both Insert and Update happen together) ---
    sqlsrv_begin_transaction($conn);

    // A. INSERT BOOKING
    $sql_book = "INSERT INTO BOOKINGS_1 (UserID, TourID, EventID, TravelDate, Pax, TotalPrice, Status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'Confirmed')";
    $params_book = array($userId, $tourId, $eventId, $travelDate, $pax, $totalPrice);
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
                // If slot update fails, undo the booking
                sqlsrv_rollback($conn);
                die("Error updating slots: " . print_r(sqlsrv_errors(), true));
            }
        }

        // C. COMMIT TRANSACTION
        sqlsrv_commit($conn);

        // Success! Redirect to a "Success Page" or Back to Profile
        // For now, let's send them to wishlist or tours with a success message
        echo "<script>
                alert('Booking Confirmed! Total: ₱" . number_format($totalPrice, 2) . "');
                window.location.href = 'wishlist.php'; 
              </script>";

    } else {
        sqlsrv_rollback($conn);
        die("Booking failed: " . print_r(sqlsrv_errors(), true));
    }
}
?>