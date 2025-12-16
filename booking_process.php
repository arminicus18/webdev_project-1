<?php
session_start();
require 'email_helper.php'; // <--- IMPORT THE HELPER

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

    // CAPTURE INPUTS
    $userId = $_SESSION['user_id'];
    $tourId = $_POST['tour_id'];
    $eventId = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
    $travelDate = $_POST['travel_date'];
    $pax = $_POST['pax'];
    $pricePerHead = $_POST['price_per_head'];
    $totalPrice = $pax * $pricePerHead;

    // EXTRA FIELDS
    $joinerName = $_POST['joiner_name'];
    $contactNumber = $_POST['contact_number'];
    $pickupLocation = $_POST['pickup_location'];
    $paymentMethod = $_POST['payment_method'];
    $notes = $_POST['notes'];

    // --- TRANSACTION START ---
    sqlsrv_begin_transaction($conn);

    // A. INSERT BOOKING
    $sql_book = "INSERT INTO BOOKINGS_1 
                 (UserID, TourID, EventID, TravelDate, Pax, TotalPrice, Status, JoinerName, ContactNumber, PickupLocation, PaymentMethod, Notes) 
                 VALUES (?, ?, ?, ?, ?, ?, 'Confirmed', ?, ?, ?, ?, ?)";

    $params_book = array($userId, $tourId, $eventId, $travelDate, $pax, $totalPrice, $joinerName, $contactNumber, $pickupLocation, $paymentMethod, $notes);
    $stmt_book = sqlsrv_query($conn, $sql_book, $params_book);

    if ($stmt_book) {

        // B. UPDATE SLOTS (If Event)
        if ($eventId) {
            $sql_update = "UPDATE EVENTS_1 SET TakenSlots = TakenSlots + ? WHERE EventID = ?";
            $stmt_update = sqlsrv_query($conn, $sql_update, array($pax, $eventId));

            if (!$stmt_update) {
                sqlsrv_rollback($conn);
                die("Error updating slots: " . print_r(sqlsrv_errors(), true));
            }
        }

        // C. GET BOOKING ID (For the ticket)
        // In SQL Server, we can get the last ID inserted in this scope
        $sql_id = "SELECT @@IDENTITY as last_id";
        $stmt_id = sqlsrv_query($conn, $sql_id);
        $row_id = sqlsrv_fetch_array($stmt_id, SQLSRV_FETCH_ASSOC);
        $bookingID = $row_id['last_id'];

        // D. GET TOUR NAME (For email)
        $sql_tour = "SELECT TOUR_NAME FROM TOURS_7 WHERE TOUR_ID = ?";
        $stmt_tour = sqlsrv_query($conn, $sql_tour, array($tourId));
        $row_tour = sqlsrv_fetch_array($stmt_tour, SQLSRV_FETCH_ASSOC);
        $tourName = $row_tour['TOUR_NAME'];

        // E. GET USER EMAIL
        // Assuming you have a USERS table with an 'Email' column
        $sql_user = "SELECT email FROM USERS_1 WHERE user_id = ?";
        $stmt_user = sqlsrv_query($conn, $sql_user, array($userId));
        $row_user = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_ASSOC);
        // Fallback: If no email found in DB, use a default for testing
        $userEmail = isset($row_user['email']) ? $row_user['email'] : 'derosasarmin@gmail.com';

        // --- COMMIT DATABASE CHANGES ---
        sqlsrv_commit($conn);

        // --- F. SEND EMAIL (MICROSERVICE) ---
        $ticketData = [
            'tour_name' => $tourName,
            'ref_id' => str_pad($bookingID, 6, '0', STR_PAD_LEFT),
            'date' => date('M d, Y', strtotime($travelDate)),
            'pax' => $pax,
            'price' => '₱' . number_format($totalPrice, 2),
            'joiner' => $joinerName,
            'pickup' => $pickupLocation
        ];

        // Call the function from email_helper.php
        $emailStatus = sendTicketEmail($userEmail, $joinerName, $ticketData);

        // REDIRECT
        if ($emailStatus == 201) {
            echo "<script>
                alert('Booking Confirmed! A ticket has been sent to your email.');
                window.location.href = 'my_bookings.php'; 
            </script>";
        } else {
            echo "<script>
                alert('Booking Confirmed, but email sending failed. Check My Bookings.');
                window.location.href = 'my_bookings.php'; 
            </script>";
        }

    } else {
        sqlsrv_rollback($conn);
        die("Booking failed: " . print_r(sqlsrv_errors(), true));
    }
}
?>