<?php
session_start();

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
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

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        // --- ADD EVENT LOGIC (Same as before) ---
        $tourId = $_POST['tour_id'];
        $eventDate = $_POST['event_date'];
        $maxSlots = $_POST['max_slots'];
        $status = $_POST['status'];

        $sql = "INSERT INTO EVENTS_1 (TourID, EventDate, MaxSlots, TakenSlots, Status) VALUES (?, ?, ?, 0, ?)";
        $params = array($tourId, $eventDate, $maxSlots, $status);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt)
            header("Location: admin.php?msg=event_created");
        else
            die(print_r(sqlsrv_errors(), true));

    } elseif ($action === 'delete') {
        // --- SMART REMOVE LOGIC ---
        $eventId = $_POST['event_id'];

        // Step 1: Check if there are existing bookings
        $check_sql = "SELECT TakenSlots FROM EVENTS_1 WHERE EventID = ?";
        $check_stmt = sqlsrv_query($conn, $check_sql, array($eventId));
        $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

        if ($row['TakenSlots'] > 0) {
            // PEOPLE HAVE BOOKED! DO NOT DELETE.
            // Instead, set status to 'Cancelled' so users know.
            $sql = "UPDATE EVENTS_1 SET Status = 'Cancelled' WHERE EventID = ?";
            $msg = "event_cancelled"; // Tell admin it was cancelled, not deleted
        } else {
            // NO BOOKINGS. SAFE TO DELETE.
            $sql = "DELETE FROM EVENTS_1 WHERE EventID = ?";
            $msg = "event_deleted";
        }

        $params = array($eventId);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            header("Location: admin.php?msg=" . $msg);
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    }
}
?>