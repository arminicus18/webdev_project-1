<?php
// 1. DATABASE CONNECTION
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) { die(print_r(sqlsrv_errors(), true)); }

// ---------------------------------------------------------
// LOGIC: HANDLE FORM SUBMISSION (The part that saves!)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $tour_id = $_POST['TOUR_ID'];

    // A. INSERT MAIN DETAILS
    // Updated to match your specific column names (DESCRIPTION, GMAPS_LOC)
    $sql_details = "INSERT INTO DETAILS_1 (DESCRIPTION, GMAPS_LOC, TOUR_ID, MEETING_POINT, NOTES) 
                    VALUES (?, ?, ?, ?, ?)";
    $params_details = array( 
        $_POST['LONG_DESCRIPTION'], 
        $_POST['MAP_EMBED_CODE'],
        $tour_id,
        $_POST['MEETING_POINT'],
        $_POST['NOTES']
    );
    $stmt1 = sqlsrv_query($conn, $sql_details, $params_details);

    // B. INSERT INCLUSIONS
    if (!empty($_POST['INCLUSIONS_LIST'])) {
        $items = explode("\n", str_replace("\r", "", $_POST['INCLUSIONS_LIST']));
        
        // FIXED SQL: We map placeholders to (ITEM_NAME, IS_INCLUDED, TOUR_ID)
        // We set IS_INCLUDED to 1 (True) by default
        $sql_inc = "INSERT INTO INCLUSIONS_1 (ITEM_NAME, IS_INCLUDED, TOUR_ID) VALUES (?, 1, ?)";
        
        foreach ($items as $item) {
            if (!empty(trim($item))) {
                // FIXED PARAMS: Matches the order (?, 1, ?) above
                // 1. Item Name  2. Tour ID
                $params_inc = array(trim($item), $tour_id);
                
                sqlsrv_query($conn, $sql_inc, $params_inc);
            }
        }
    }

    // C. INSERT ITINERARY
    if (!empty($_POST['ITINERARY_LIST'])) {
        $lines = explode("\n", str_replace("\r", "", $_POST['ITINERARY_LIST']));
        
        $sql_itin = "INSERT INTO ITINERARY_1 (DAY_NUM, TIME_MARKER, ACTIVITY_DESC, TOUR_ID) VALUES (?, ?, ?, ?)";
        
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $parts = explode("-", $line, 2); 
                $time = isset($parts[0]) ? trim($parts[0]) : "";
                $activity = isset($parts[1]) ? trim($parts[1]) : $line;
                
                // FIXED PARAMS: Must match SQL order exactly
                // 1. DAY_NUM (1)
                // 2. TIME_MARKER ($time)
                // 3. ACTIVITY_DESC ($activity)
                // 4. TOUR_ID ($tour_id)
                $params_itin = array(1, $time, $activity, $tour_id);
                
                sqlsrv_query($conn, $sql_itin, $params_itin);
            }
        }
    }

    if ($stmt1) {
        echo "<script>alert('Details added successfully!');</script>";
    } else {
        echo "Error adding details.";
        die(print_r(sqlsrv_errors(), true));
    }
}

// ---------------------------------------------------------
// LOGIC: FETCH EXISTING TOURS FOR DROPDOWN
// ---------------------------------------------------------
$sql_get_tours = "SELECT TOUR_ID, TOUR_NAME FROM TOURS_7 ORDER BY TOUR_NAME ASC";
$result_tours = sqlsrv_query($conn, $sql_get_tours);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Tour Details</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        label { display: block; font-weight: bold; margin-top: 15px; }
        textarea { width: 100%; height: 100px; }
        select, input[type="text"] { width: 100%; padding: 8px; }
        .hint { font-size: 0.8em; color: #666; font-style: italic; }
    </style>
</head>
<body>

    <h1>Add Details to Existing Tour</h1>
    <p><a href="addtours.html">&larr; Back to Main Manager</a></p>

    <form method="POST" action="add_details.php">
        
        <label>Select Tour:</label>
        <select name="TOUR_ID" required>
            <option value="" disabled selected>-- Choose a Mountain --</option>
            <?php 
                // Loop through database results to create <option> tags
                while($row = sqlsrv_fetch_array($result_tours, SQLSRV_FETCH_ASSOC)) {
                    echo "<option value='" . $row['TOUR_ID'] . "'>" . $row['TOUR_NAME'] . " (ID: " . $row['TOUR_ID'] . ")</option>";
                }
            ?>
        </select>

        <label>Long Marketing Description:</label>
        <textarea name="LONG_DESCRIPTION" placeholder="Tell the story of the mountain here..." required></textarea>

        <label>Google Maps Embed Code:</label>
        <div class="hint">Go to Google Maps -> Share -> Embed a Map -> Copy HTML</div>
        <input type="text" name="MAP_EMBED_CODE" placeholder='<iframe src="..."></iframe>'>

        <label>Meeting Point Address:</label>
        <input type="text" name="MEETING_POINT" placeholder="e.g. McDonald's Greenfield District">

        <label>Inclusions List:</label>
        <div class="hint">Enter one item per line. (e.g. Van Transfer [Enter] Local Guide)</div>
        <textarea name="INCLUSIONS_LIST" placeholder="Roundtrip Van Transfer&#10;Local Guide Fee&#10;Environmental Fee"></textarea>

        <label>Itinerary:</label>
        <div class="hint">Format: TIME - ACTIVITY (One per line)</div>
        <textarea name="ITINERARY_LIST" placeholder="04:00 AM - Meetup at McDonalds&#10;05:00 AM - ETD to Jump-off&#10;08:00 AM - Start Trek"></textarea>


        <label>Important Notes (Safety/Policy):</label>
        <textarea name="NOTES" placeholder="Requires medical certificate..."></textarea>

        <br><br>
        <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer;">
            Save Details
        </button>

    </form>

</body>
</html>