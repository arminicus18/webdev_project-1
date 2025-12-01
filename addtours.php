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
        $DIFFICULTY = $_POST['DIFFICULTY']; // Radio button value (1-9)
        $CLASS = $_POST['CLASS'];
        $RATING = $_POST['RATING'];
        $REVIEWS = $_POST['REVIEWS'];
        $PRICE = $_POST['PRICE'];
        $DURATION = $_POST['DURATION'];
        
        // Handle Checkbox (If unchecked, it isn't sent in POST, so we default to 0)
        $IS_ACTIVE = isset($_POST['IS_ACTIVE']) ? 1 : 0;

        // --- FILE UPLOAD LOGIC ---
        // 1. Define folder
        $img_destination = "img_used/"; 
        
        // 2. Get the file name from the HTML input named "IMAGE_URL"
        $filename = basename($_FILES['IMAGE_URL']['name']);
        
        // 3. Create the full path (e.g., "img_used/myphoto.jpg")
        $targetfilepath = $img_destination . $filename;
        
        // 4. Move the file from temporary storage to your folder
        if (move_uploaded_file($_FILES['IMAGE_URL']['tmp_name'], $targetfilepath)) {
            
            // 5. INSERT INTO DATABASE (Direct Variable Injection)
            $sql = "INSERT INTO TOURS_6 
                    (TOUR_NAME, CATEGORY, LOCATION, DIFFICULTY, CLASS, RATING, REVIEWS, PRICE, DURATION, IMAGE_URL, IS_ACTIVE)
                    VALUES 
                    ('$TOUR_NAME', '$CATEGORY', '$LOCATION', '$DIFFICULTY', '$CLASS', '$RATING', '$REVIEWS', '$PRICE', '$DURATION', '$targetfilepath', '$IS_ACTIVE')";
            
            // Execute without params array
            $stmt = sqlsrv_query($conn, $sql);

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
        $new_status = $_POST['NEW_STATUS']; // 1 or 0

        // Direct Variable Injection
        $sql = "UPDATE TOURS_6 SET IS_ACTIVE = '$new_status' WHERE TOUR_ID = '$tour_id'";
        
        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt) {
            echo "<script>alert('Status Updated!'); window.location.href='addtours.html';</script>";
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    // ---------------------------------------------------
    // CASE C: DELETE TOUR
    // ---------------------------------------------------
    elseif ($action == 'delete') {
        $tour_id = $_POST['TOUR_ID_DELETE'];

        // Direct Variable Injection
        $sql = "DELETE FROM TOURS_6 WHERE TOUR_ID = '$tour_id'";

        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt) {
            echo "<script>alert('Tour Deleted Permanently.'); window.location.href='addtours.html';</script>";
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    }
}
?>