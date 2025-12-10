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
    die(print_r(sqlsrv_errors(), true));
}

// 2. CHECK IF FORM WAS SUBMITTED
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // We use "action_type" to decide if we are Adding, Updating, or Deleting
    $action = $_POST['action_type'];

    // ---------------------------------------------------
    // CASE A: ADD NEW TOUR
    // ---------------------------------------------------
    if ($action == 'add') {
        
        // Get text inputs
        $TOUR_NAME = $_POST['TOUR_NAME'];
        $CATEGORY = $_POST['CATEGORY'];
        $LOCATION = $_POST['LOCATION'];
        $DIFFICULTY = $_POST['DIFFICULTY'];
        $CLASS = $_POST['CLASS'];
        $RATING = $_POST['RATING'];
        $REVIEWS = $_POST['REVIEWS'];
        $PRICE = $_POST['PRICE'];
        $DURATION = $_POST['DURATION'];
        $ISLAND = $_POST['ISLAND'];
        $MASL = $_POST['MASL'];
        
        // Handle Checkbox
        $IS_ACTIVE = isset($_POST['IS_ACTIVE']) ? 1 : 0;

        // --- FILE UPLOAD LOGIC ---
        $img_destination = "img_used/"; 
        $filename = basename($_FILES['IMAGE_URL']['name']);
        $targetfilepath = $img_destination . $filename;
        
        if (move_uploaded_file($_FILES['IMAGE_URL']['tmp_name'], $targetfilepath)) {
            
            // Using Parameters (?) is safer to prevent crashes with apostrophes (e.g. O'Neil)
            $sql = "INSERT INTO TOURS_7 
                    (TOUR_NAME, CATEGORY, LOCATION, DIFFICULTY, CLASS, RATING, REVIEWS, PRICE, DURATION, ISLAND, IMAGE_URL, IS_ACTIVE)
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = array($TOUR_NAME, $CATEGORY, $LOCATION, $DIFFICULTY, $CLASS, $RATING, $REVIEWS, $PRICE, $DURATION, $ISLAND, $targetfilepath, $IS_ACTIVE, $MASL);

            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt) {
                echo "<script>alert('Tour Added Successfully!'); window.location.href='addtours.html';</script>";
            } else {
                echo "Database Error: ";
                die(print_r(sqlsrv_errors(), true));
            }

        } else {
            echo "Error uploading image.";
        }
    }

    // ---------------------------------------------------
    // CASE B: UPDATE STATUS (Hide/Show)
    // ---------------------------------------------------
    elseif ($action == 'update_status') {
        $tour_id = $_POST['TOUR_ID_UPDATE'];
        $new_status = $_POST['NEW_STATUS'];

        $sql = "UPDATE TOURS_7 SET IS_ACTIVE = ? WHERE TOUR_ID = ?";
        $params = array($new_status, $tour_id);
        
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            echo "<script>alert('Status Updated!'); window.location.href='addtours.html';</script>";
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    // ---------------------------------------------------
    // CASE C: DELETE TOUR (UPDATED & ROBUST)
    // ---------------------------------------------------
    elseif ($action == 'delete') {
        $tour_id = $_POST['TOUR_ID_DELETE'];

        // STEP 1: Delete the "Child" data first to avoid Foreign Key Errors
        // We use separate queries to clear out the connected tables
        $delete_details = "DELETE FROM DETAILS_1 WHERE TOUR_ID = ?";
        sqlsrv_query($conn, $delete_details, array($tour_id));

        $delete_inclusions = "DELETE FROM INCLUSIONS_1 WHERE TOUR_ID = ?";
        sqlsrv_query($conn, $delete_inclusions, array($tour_id));

        $delete_itinerary = "DELETE FROM ITINERARY_1 WHERE TOUR_ID = ?";
        sqlsrv_query($conn, $delete_itinerary, array($tour_id));

        // STEP 2: Now it is safe to delete the "Parent" Tour
        $sql = "DELETE FROM TOURS_7 WHERE TOUR_ID = ?";
        $params = array($tour_id);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            echo "<script>alert('Tour and all related details deleted permanently.'); window.location.href='addtours.html';</script>";
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    }
}
?>