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

// 2. FETCH ONLY 'BEACH' AND 'OTHERS'
// We check T.CATEGORY instead of T.CLASS to handle NULL values
$sql = "SELECT 
            T.TOUR_ID, 
            T.TOUR_NAME, 
            T.LOCATION, 
            T.RATING, 
            T.REVIEWS, 
            T.PRICE, 
            T.IMAGE_URL, 
            T.CLASS,
            T.CATEGORY,
            (
                SELECT STUFF((
                    SELECT ',' + ITEM_NAME 
                    FROM INCLUSIONS_1 
                    WHERE TOUR_ID = T.TOUR_ID AND IS_INCLUDED = 1
                    FOR XML PATH('')
                ), 1, 1, '')
            ) AS INC_LIST
        FROM TOURS_7 T
        WHERE T.IS_ACTIVE = 1 
        AND (T.CATEGORY = 'Beach' OR T.CATEGORY = 'Others')";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["error" => sqlsrv_errors()]);
    exit;
}

$tours = array();
while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $tours[] = $row;
}

echo json_encode($tours);
?>