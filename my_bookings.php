<?php
session_start();

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 2. CONNECT TO DATABASE
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

$user_id = $_SESSION['user_id'];

// 3. FETCH BOOKINGS
// UPDATED SORTING:
// - CASE WHEN... checks if the date is in the future (Group 0) or past (Group 1).
// - Then it sorts by date to get the nearest ones first.
$sql = "SELECT 
            b.BookingID,
            b.TravelDate,
            b.Pax,
            b.TotalPrice,
            b.Status,
            b.BookingDate,
            t.TOUR_NAME,
            t.IMAGE_URL,
            t.LOCATION
        FROM BOOKINGS_1 b
        JOIN TOURS_7 t ON b.TourID = t.TOUR_ID
        WHERE b.UserID = ?
        ORDER BY 
            CASE WHEN b.TravelDate >= CAST(GETDATE() AS DATE) THEN 0 ELSE 1 END ASC, 
            b.TravelDate ASC";

$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);

$bookings = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Fix column case issues
        $bookings[] = array_change_key_case($row, CASE_UPPER);
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <title>My Bookings | MountPinoy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/97fe9f84ce.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="mountpinoy.css">

    <style>
        body {
            background-color: #121212;
            color: #f0f0f0;
        }

        .ticket-card {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            height: 300px;
            display: flex;
        }

        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 193, 7, 0.2);
            border-color: #ffc107;
        }

        /* Ensure row fills card */
        .ticket-card>.row {
            width: 100%;
            height: 100%;
            margin: 0;
        }

        .ticket-card .col-md-4 {
            height: 100%;
        }

        .ticket-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            border-right: 2px dashed #333;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            z-index: 10;
        }

        .status-confirmed {
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }

        .status-pending {
            color: #f1c40f;
            border: 1px solid #f1c40f;
        }

        .status-cancelled {
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .ticket-card .col-md-8 {
            display: flex;
            flex-direction: column;
        }

        .ticket-card .card-body {
            padding: 1.2rem;
            display: flex;
            flex-direction: column;
        }

        .ticket-card .card-title {
            margin-bottom: 1rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ticket-card .row.g-3 {
            margin-bottom: auto;
        }

        @media (max-width: 768px) {
            .ticket-card {
                height: auto;
                flex-direction: column;
            }

            .ticket-card .col-md-4 {
                height: 200px;
            }

            .ticket-img {
                border-right: none;
                border-bottom: 2px dashed #333;
            }

            .status-badge {
                left: auto;
                right: 15px;
            }
        }
    </style>
</head>

<body>

    <nav class="fixed-top navbar navbar-expand-lg navbar-custom navbar-dark">
        <div class="container-lg container-md">
            <a class="navbar-brand navbar-brand-text" href="index.html">
                <span class="logo-mount">Mount</span><span class="logo-pinoy">Pinoy</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbarOffcanvas"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse d-none d-lg-flex justify-content-between" id="navbarNav">

                <ul class="navbar-nav mx-auto justify-content-around w-50 nav-text">
                    <li class="nav-item">
                        <a class="nav-link navbar-text navbar-font-size" href="index.php">
                            <i class="fa-regular fa-house fa-lg nav-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link navbar-text navbar-font-size" href="tours.php">
                            <i class="fa-solid fa-person-hiking fa-lg nav-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link navbar-text navbar-font-size" href="events.php">
                            <i class="fa-regular fa-calendar-days fa-lg nav-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link navbar-text navbar-font-size" href="tips.php">
                            <i class="fa-solid fa-circle-info fa-lg nav-icon"></i>
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <form class="d-flex input-group search-group" role="search">
                        <input class="form-control navbar-search" type="search" placeholder="Search..."
                            aria-label="Search" />
                        <button class="btn btn-outline-secondary search-icon-btn" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>

                    <div class="dropdown">
                        <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="userDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">

                            <img src="<?php echo $_SESSION['user_picture']; ?>" alt="Profile" class="rounded-circle"
                                style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #FFC107;">
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow"
                            aria-labelledby="userDropdown">
                            <li>
                                <h4 class="dropdown-header text-white text-truncate" style="max-width: 200px;">
                                    <strong class="text-warning">
                                        <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?>
                                    </strong>
                                </h4>
                            </li>

                            <li>
                                <hr class="dropdown-divider border-secondary">
                            </li>

                            <li>
                                <a class="dropdown-item text-white" href="wishlist.php">
                                    <i class="fa-solid fa-heart me-2 text-warning"></i> My Wishlist
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item text-white" href="my_bookings.php">
                                    <i class="fa-solid fa-ticket me-2 text-success"></i> My Bookings
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider border-secondary">
                            </li>

                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </a>
                            </li>
                        </ul>

                    </div>
                </div>

            </div>

            <div class="offcanvas offcanvas-end navbar-custom navbar-dark d-lg-none" tabindex="-1" id="navbarOffcanvas">
                <div class="offcanvas-header">
                    <img src="<?php echo $_SESSION['user_picture']; ?>" alt="Profile" class="rounded-circle"
                        style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #FFC107; margin-right: 5px;">
                    <h5 class="offcanvas-title text-white">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column justify-content-between">
                    <ul class="navbar-nav nav-text">
                        <li class="nav-item"><a class="nav-link navbar-text" href="index.php"><i
                                    class="fa-regular fa-house fa-lg nav-icon me-2"></i> Home</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="tours.php"><i
                                    class="fa-solid fa-person-hiking fa-lg nav-icon me-2"></i> Tours</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="events.php"><i
                                    class="fa-regular fa-calendar-days fa-lg nav-icon me-2"></i> Events</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="tips.php"><i
                                    class="fa-solid fa-circle-info fa-lg nav-icon me-2"></i> Tips</a></li>
                    </ul>

                    <div class="mt-4">
                        <a class="text-danger text-decoration-none" href="logout.php">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-5 mt-5">
            <div>
                <h2 class="text-warning fw-bold"><i class="fa-solid fa-ticket me-2"></i>My Adventure Tickets</h2>
            </div>
        </div>

        <?php if (count($bookings) > 0): ?>
            <div class="d-flex flex-column gap-4">
                <?php foreach ($bookings as $book):
                    // Format Dates
                    $travelDate = $book['TRAVELDATE']->format('M d, Y');
                    $travelDay = $book['TRAVELDATE']->format('l');
                    $bookedOn = $book['BOOKINGDATE']->format('M d, Y');

                    // Status Color Logic
                    $statusClass = 'status-pending';
                    if ($book['STATUS'] == 'Confirmed')
                        $statusClass = 'status-confirmed';
                    if ($book['STATUS'] == 'Cancelled')
                        $statusClass = 'status-cancelled';
                    ?>

                    <div class="ticket-card row g-0">
                        <div class="col-md-4 position-relative">
                            <img src="<?php echo $book['IMAGE_URL']; ?>" class="img-fluid ticket-img w-100" alt="Tour Image">
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $book['STATUS']; ?>
                            </span>
                        </div>

                        <div class="col-md-8">
                            <div class="card-body p-4 d-flex flex-column h-100">

                                <h3 class="card-title text-white fw-bold mb-3"><?php echo $book['TOUR_NAME']; ?></h3>

                                <div class="row g-3 mb-4">
                                    <div class="col-6 col-sm-4">
                                        <div class="info-label"><i class="bi bi-calendar-event me-1"></i> Travel Date</div>
                                        <div class="info-value text-warning"><?php echo $travelDate; ?></div>
                                        <small class="text-secondary"><?php echo $travelDay; ?></small>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-label"><i class="bi bi-people me-1"></i> Guests</div>
                                        <div class="info-value"><?php echo $book['PAX']; ?> Pax</div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="info-label"><i class="bi bi-tag me-1"></i> Total Price</div>
                                        <div class="info-value">₱<?php echo number_format($book['TOTALPRICE'], 2); ?></div>
                                    </div>
                                </div>

                                <div
                                    class="d-flex justify-content-between align-items-end mt-auto pt-3 border-top border-secondary">
                                    <small class="text-secondary">
                                        Booked on: <?php echo $bookedOn; ?> <br>
                                        Ref ID: #<?php echo str_pad($book['BOOKINGID'], 6, '0', STR_PAD_LEFT); ?>
                                    </small>

                                    <?php if ($book['STATUS'] == 'Confirmed'): ?>
                                        <button class="btn btn-outline-warning btn-sm"
                                            onclick="alert('Ticket feature coming soon!')">
                                            <i class="fa-solid fa-print me-2"></i>Print Ticket
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm disabled">
                                            Pending
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-5 border border-secondary rounded-4 bg-dark bg-opacity-50">
                <i class="bi bi-backpack4 fa-4x text-secondary mb-4"></i>
                <h3 class="text-white">No bookings yet?</h3>
                <p class="text-secondary">Time to start your next adventure!</p>
                <a href="tours.php" class="btn btn-warning mt-2 fw-bold px-4 py-2">Find a Tour</a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>