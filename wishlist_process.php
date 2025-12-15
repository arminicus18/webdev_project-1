<?php
// 1. DISABLE HTML ERROR REPORTING (This fixes the "Unexpected token <" error)
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
$response = array();

// 2. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first.']);
    exit();
}

// 3. DATABASE CONNECTION (Wrapped in Try/Catch logic)
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2", // <--- VERIFY THIS NAME IN SSMS!
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    // FORMAT THE ERROR AS JSON
    $errors = sqlsrv_errors();
    $errorMsg = "Database Connection Failed: " . (isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown Error');
    echo json_encode(['status' => 'error', 'message' => $errorMsg]);
    exit();
}

// 4. PROCESS DATA
if (isset($_POST['tour_id'])) {
    $userId = $_SESSION['user_id'];
    $tourId = $_POST['tour_id'];

    // Check if exists
    $checkSql = "SELECT * FROM WISHLIST_1 WHERE UserID = ? AND TourID = ?";
    $params = array($userId, $tourId);
    $stmt = sqlsrv_query($conn, $checkSql, $params);

    if ($stmt === false) {
        $errors = sqlsrv_errors();
        echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $errors[0]['message']]);
        exit();
    }

    if (sqlsrv_has_rows($stmt)) {
        // REMOVE
        $deleteSql = "DELETE FROM WISHLIST_1 WHERE UserID = ? AND TourID = ?";
        $delStmt = sqlsrv_query($conn, $deleteSql, $params);

        if ($delStmt) {
            echo json_encode(['status' => 'success', 'action' => 'removed']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove from database.']);
        }
    } else {
        // ADD
        $insertSql = "INSERT INTO WISHLIST_1 (UserID, TourID, DateAdded) VALUES (?, ?, GETDATE())";
        $insStmt = sqlsrv_query($conn, $insertSql, $params);

        if ($insStmt) {
            echo json_encode(['status' => 'success', 'action' => 'added']);
        } else {
            // Capture specific SQL insert error
            $errors = sqlsrv_errors();
            echo json_encode(['status' => 'error', 'message' => 'Insert Failed: ' . $errors[0]['message']]);
        }
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'No Tour ID received.']);
}
?>