<?php
header('Content-Type: application/json');

// 1. DATABASE CONNECTION
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo json_encode(["error" => sqlsrv_errors()]);
    exit;
}

// 2. FETCH ALL ACTIVE TOURS (Hikes, Beach, Others)
// Crucial: We need the 'ISLAND' column here.
$sql = "SELECT 
            T.TOUR_ID, T.TOUR_NAME, T.LOCATION, T.RATING, T.REVIEWS, 
            T.PRICE, T.IMAGE_URL, T.CLASS, T.CATEGORY, T.ISLAND
        FROM TOURS_7 T
        WHERE T.IS_ACTIVE = 1";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["error" => sqlsrv_errors()]);
    exit;
}

$tours = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $tours[] = $row;
}

echo json_encode($tours);
?>