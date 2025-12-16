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
$max_pax = 20; 
$slots_left = 20; 
// Auto-fill Joiner Name with the logged-in user's name
$default_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; 

// 4. FETCH DATA
if (isset($_GET['event_id'])) {
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
        $tour = $event;
        $travel_date_value = $event['EVENTDATE']->format('Y-m-d');
        $slots_left = $event['MAXSLOTS'] - $event['TAKENSLOTS'];
        $max_pax = $slots_left;
    }
} elseif (isset($_GET['id'])) {
    $tour_id = $_GET['id'];
    $sql = "SELECT * FROM TOURS_7 WHERE TOUR_ID = ?";
    $stmt = sqlsrv_query($conn, $sql, array($tour_id));
    $tour = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($tour) $tour = array_change_key_case($tour, CASE_UPPER);
} else {
    die("No booking ID specified.");
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <title>Complete Booking | MountPinoy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="mountpinoy.css">
    <style>
        .form-section-title {
            color: #ffc107;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
        .payment-option input[type="radio"] { display: none; }
        .payment-option label {
            display: block;
            border: 1px solid #444;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .payment-option input[type="radio"]:checked + label {
            border-color: #ffc107;
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
    </style>
</head>
<body style="background-color: #121212; color: #f0f0f0;">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="mb-4">
                    <a href="javascript:history.back()" class="btn btn-outline-light btn-sm">&larr; Go Back</a>
                </div>

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="card bg-dark border-secondary shadow-lg">
                            <div class="card-body p-4">
                                <h3 class="mb-4 fw-bold">Confirm Your Hike</h3>

                                <form action="booking_process.php" method="POST" id="bookingForm">
                                    <input type="hidden" name="tour_id" value="<?php echo $tour['TOUR_ID']; ?>">
                                    <input type="hidden" name="event_id" value="<?php echo $is_event ? $event['EVENTID'] : ''; ?>">
                                    <input type="hidden" name="price_per_head" id="pricePerHead" value="<?php echo $tour['PRICE']; ?>">

                                    <div class="form-section-title mt-2">Joiner Details</div>
                                    <div class="mb-3">
                                        <label class="form-label text-secondary small">Lead Hiker Name</label>
                                        <input type="text" class="form-control bg-secondary text-white border-secondary" 
                                               name="joiner_name" value="<?php echo $default_name; ?>" required>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="form-label text-secondary small">Date</label>
                                            <input type="date" class="form-control bg-secondary text-white border-secondary" 
                                                   name="travel_date" 
                                                   value="<?php echo $travel_date_value; ?>" 
                                                   <?php echo $is_event ? 'readonly' : ''; ?> 
                                                   required min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-secondary small">Guests (Pax)</label>
                                            <input type="number" class="form-control bg-secondary text-white border-secondary" 
                                                   name="pax" id="paxInput" 
                                                   value="1" min="1" max="<?php echo $max_pax; ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-section-title mt-4">Contact Info</div>

                                    <div class="mb-3">
                                        <label class="form-label text-secondary small">Mobile Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-secondary text-white border-secondary">+63</span>
                                            <input type="tel" class="form-control bg-secondary text-white border-secondary" 
                                                   name="contact_number" placeholder="912 345 6789" required pattern="[0-9]{10}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-secondary small">Pickup Location</label>
                                        <select class="form-select bg-secondary text-white border-secondary" name="pickup_location" required>
                                            <option value="" selected disabled>Select Pickup Point...</option>
                                            <option value="MOA (Mall of Asia)">MOA (Mall of Asia)</option>
                                            <option value="Greenfield District">Greenfield District</option>
                                            <option value="Cubao Farmers">Cubao Farmers</option>
                                            <option value="McDonalds Eton Centris">McDonalds Eton Centris</option>
                                            <option value="Own Transport / Meet at Jump-off">Own Transport / Meet at Jump-off</option>
                                        </select>
                                    </div>

                                    <div class="form-section-title mt-4">Payment Method</div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 payment-option">
                                            <input type="radio" name="payment_method" id="pay_gcash" value="GCash" checked>
                                            <label for="pay_gcash">
                                                <i class="bi bi-phone mb-1 d-block h4"></i> GCash
                                            </label>
                                        </div>
                                        <div class="col-6 payment-option">
                                            <input type="radio" name="payment_method" id="pay_cash" value="Cash">
                                            <label for="pay_cash">
                                                <i class="bi bi-cash-stack mb-1 d-block h4"></i> Cash
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label text-secondary small">Special Requests (Optional)</label>
                                        <textarea class="form-control bg-secondary text-white border-secondary" 
                                                  name="notes" rows="2" placeholder="Allergies, senior citizen, etc."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-warning w-100 fw-bold py-2">
                                        Complete Booking
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card bg-dark border-warning shadow-lg sticky-top" style="top: 20px;">
                            <img src="<?php echo $tour['IMAGE_URL']; ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                            <div class="card-body p-4">
                                <h5 class="text-white fw-bold"><?php echo $tour['TOUR_NAME']; ?></h5>
                                <div class="text-secondary small mb-3">
                                    <i class="bi bi-geo-alt text-warning"></i> <?php echo isset($tour['LOCATION']) ? $tour['LOCATION'] : 'Philippines'; ?>
                                </div>
                                
                                <hr class="border-secondary">
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary">Price per head</span>
                                    <span class="text-white">₱<?php echo number_format($tour['PRICE']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary">Guests</span>
                                    <span class="text-white" id="summaryPax">1</span>
                                </div>
                                
                                <hr class="border-secondary">

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-warning fw-bold">TOTAL</span>
                                    <span class="h3 text-warning fw-bold mb-0" id="totalDisplay">
                                        ₱<?php echo number_format($tour['PRICE']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const paxInput = document.getElementById('paxInput');
        const summaryPax = document.getElementById('summaryPax');
        const pricePerHead = parseFloat(document.getElementById('pricePerHead').value);
        const totalDisplay = document.getElementById('totalDisplay');

        paxInput.addEventListener('input', function() {
            let pax = parseInt(this.value) || 1;
            let max = parseInt(this.getAttribute('max'));
            
            if (pax > max) {
                pax = max;
                this.value = max;
                alert("Only " + max + " slots available!");
            }
            if (pax < 1) pax = 1;

            summaryPax.innerText = pax;
            
            let total = pax * pricePerHead;
            totalDisplay.innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
        });
    </script>

</body>
</html>