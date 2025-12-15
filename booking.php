<?php
session_start();

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 2. CONNECT
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

// 3. INITIALIZE VARIABLES
$tour = null;
$event = null;
$is_event = false;
$travel_date_value = '';
$max_pax = 12; // Default max for private
$slots_left = 12; 

// 4. CHECK IF BOOKING AN EVENT (Joiner) OR TOUR (Private)
if (isset($_GET['event_id'])) {
    // --- CASE A: JOINER EVENT ---
    $is_event = true;
    $event_id = $_GET['event_id'];
    
    $sql = "SELECT e.*, t.TOUR_NAME, t.PRICE, t.IMAGE_URL, t.TOUR_ID 
            FROM EVENTS_1 e 
            JOIN TOURS_7 t ON e.TourID = t.TOUR_ID 
            WHERE e.EventID = ?";
    $stmt = sqlsrv_query($conn, $sql, array($event_id));
    $event = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if ($event) {
        $event = array_change_key_case($event, CASE_UPPER);
        $tour = $event; // Share variable for simpler HTML
        $travel_date_value = $event['EVENTDATE']->format('Y-m-d');
        $slots_left = $event['MAXSLOTS'] - $event['TAKENSLOTS'];
        $max_pax = $slots_left; // Cap input at remaining slots
    }

} elseif (isset($_GET['id'])) {
    // --- CASE B: PRIVATE TOUR ---
    $tour_id = $_GET['id'];
    
    $sql = "SELECT * FROM TOURS_7 WHERE TOUR_ID = ?";
    $stmt = sqlsrv_query($conn, $sql, array($tour_id));
    $tour = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if ($tour) {
        $tour = array_change_key_case($tour, CASE_UPPER);
    }
} else {
    die("No booking ID specified.");
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <title>Book Your Adventure | MountPinoy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mountpinoy.css">
</head>
<body style="background-color: #121212; color: #f0f0f0;">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="mb-4">
                    <a href="javascript:history.back()" class="btn btn-outline-light btn-sm">&larr; Go Back</a>
                </div>

                <div class="card bg-dark border-warning shadow-lg">
                    <img src="<?php echo $tour['IMAGE_URL']; ?>" class="card-img-top" style="height: 200px; object-fit: cover; opacity: 0.8;">
                    
                    <div class="card-body p-4">
                        <h2 class="card-title text-warning fw-bold mb-1"><?php echo $tour['TOUR_NAME']; ?></h2>
                        
                        <?php if($is_event): ?>
                            <span class="badge bg-danger mb-3">Joiner Event</span>
                            <p class="text-white mb-3">
                                <i class="bi bi-people-fill text-warning me-2"></i> 
                                Slots Left: <strong><?php echo $slots_left; ?></strong>
                            </p>
                        <?php else: ?>
                            <span class="badge bg-success mb-3">Private Tour</span>
                        <?php endif; ?>

                        <hr class="border-secondary">

                        <form action="booking_process.php" method="POST" id="bookingForm">
                            <input type="hidden" name="tour_id" value="<?php echo $tour['TOUR_ID']; ?>">
                            <input type="hidden" name="event_id" value="<?php echo $is_event ? $event['EVENTID'] : ''; ?>">
                            <input type="hidden" name="price_per_head" id="pricePerHead" value="<?php echo $tour['PRICE']; ?>">

                            <div class="mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Travel Date</label>
                                <input type="date" class="form-control bg-secondary text-white border-secondary p-3" 
                                       name="travel_date" 
                                       value="<?php echo $travel_date_value; ?>" 
                                       <?php echo $is_event ? 'readonly' : ''; ?> 
                                       required min="<?php echo date('Y-m-d'); ?>">
                                <?php if($is_event): ?>
                                    <small class="text-warning">* Date is fixed for this event.</small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Number of Guests</label>
                                <input type="number" class="form-control bg-secondary text-white border-secondary p-3" 
                                       name="pax" id="paxInput" 
                                       value="1" min="1" max="<?php echo $max_pax; ?>" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center bg-black bg-opacity-50 p-3 rounded mb-4">
                                <span class="text-secondary">Total Price</span>
                                <h3 class="text-warning mb-0 fw-bold" id="totalDisplay">
                                    ₱<?php echo number_format($tour['PRICE'], 2); ?>
                                </h3>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold">
                                    Confirm Booking
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const paxInput = document.getElementById('paxInput');
        const pricePerHead = parseFloat(document.getElementById('pricePerHead').value);
        const totalDisplay = document.getElementById('totalDisplay');

        paxInput.addEventListener('input', function() {
            let pax = parseInt(this.value) || 0;
            // Prevent booking more than slots
            let max = parseInt(this.getAttribute('max'));
            if (pax > max) {
                pax = max;
                this.value = max;
                alert("Only " + max + " slots available!");
            }
            
            let total = pax * pricePerHead;
            // Format to PHP currency style
            totalDisplay.innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        });
    </script>

</body>
</html>