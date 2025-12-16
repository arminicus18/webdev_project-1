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
    echo json_encode(["error" => print_r(sqlsrv_errors(), true)]);
    exit;
}

// 2. FETCH 'OTHERS' AND 'BEACH' TOURS

$sql = "SELECT TOP (4) * FROM TOURS_7 WHERE CATEGORY IN ('Others', 'Beach') AND RATING >= 4.6 ORDER BY RATING DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["error" => print_r(sqlsrv_errors(), true)]);
    exit;
}

// 3. CONVERT TO JSON
$tours = array();

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Format Price
    $row['PRICE'] = number_format($row['PRICE'], 2);
    $tours[] = $row;
}

header('Content-Type: application/json');
echo json_encode($tours);
?>