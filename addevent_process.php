<?php
// 1. CONNECT
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// 2. CHECK REQUEST METHOD
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check which action we are doing (Add or Delete)
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        // --- ADD LOGIC ---
        $tourId = $_POST['tour_id'];
        $eventDate = $_POST['event_date'];
        $maxSlots = $_POST['max_slots'];
        $status = $_POST['status'];

        $sql = "INSERT INTO EVENTS_1 (TourID, EventDate, MaxSlots, TakenSlots, Status) VALUES (?, ?, ?, 0, ?)";
        $params = array($tourId, $eventDate, $maxSlots, $status);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            header("Location: admin.php?msg=event_created");
        } else {
            die(print_r(sqlsrv_errors(), true));
        }

    } elseif ($action === 'delete') {
        // --- DELETE LOGIC ---
        $eventId = $_POST['event_id'];

        $sql = "DELETE FROM EVENTS_1 WHERE EventID = ?";
        $params = array($eventId);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            header("Location: admin.php?msg=event_deleted");
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    } else {
        die("Invalid action.");
    }
}
?>