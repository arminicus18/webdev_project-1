<?php
// 1. DATABASE CONNECTION
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    // Return error as JSON so JS can handle it
    echo json_encode(["error" => print_r(sqlsrv_errors(), true)]);
    exit;
}

// 2. FETCH ACTIVE TOURS
$sql = "SELECT * FROM TOURS_6 WHERE IS_ACTIVE = 1";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["error" => print_r(sqlsrv_errors(), true)]);
    exit;
}

// 3. CONVERT TO JSON
$tours = array();

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Clean up the data or format it if needed
    $row['PRICE'] = number_format($row['PRICE'], 2);
    $tours[] = $row;
}

// Set header to tell browser this is JSON data, not HTML
header('Content-Type: application/json');

// Output the data
echo json_encode($tours);
?>