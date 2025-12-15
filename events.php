<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: index.html");
    exit();
}


$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

// --- 2. FETCH EVENTS LOGIC ---
$events_by_month = []; // Initialize empty array

if ($conn !== false) {
    // We join EVENTS_1 with TOURS_7 to get the name, price, etc.
    $sql = "SELECT 
                e.EventID,
                e.EventDate,
                e.MaxSlots,
                e.TakenSlots,
                e.Status,
                t.TOUR_ID,
                t.TOUR_NAME,
                t.IMAGE_URL,
                t.PRICE,
                t.DIFFICULTY
            FROM EVENTS_1 e
            JOIN TOURS_7 t ON e.TourID = t.TOUR_ID
            WHERE e.EventDate >= GETDATE() -- Show future events only
            ORDER BY e.EventDate ASC";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Fix column casing (ensure keys are UPPERCASE)
            $row = array_change_key_case($row, CASE_UPPER);

            // Group events by "December 2025", "January 2026", etc.
            // We use the SQL 'EventDate' object directly
            $monthYear = $row['EVENTDATE']->format('F Y');
            $events_by_month[$monthYear][] = $row;
        }
    }
}

?>


<!DOCTYPE html>

<html>

<head>
    <title>
        Schedule of events
    </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" type="text/css" href="mountpinoy.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="https://kit.fontawesome.com/97fe9f84ce.js" crossorigin="anonymous"></script>
    <script defer src="mountpinoy.js"></script>
    <script src="navbar.js"></script>

    <style>
        .month-header {
            border-left: 5px solid #ffc107;
            padding-left: 15px;
            margin: 40px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #fff;
            font-weight: bold;
        }

        .event-card {
            background-color: #1e1e1e;
            border: 1px solid #333;
            transition: transform 0.2s, border-color 0.2s;
        }

        .event-card:hover {
            transform: translateY(-5px);
            border-color: #ffc107;
        }

        .date-box {
            background-color: #2c2c2c;
            border-radius: 8px;
            text-align: center;
            min-width: 80px;
            padding: 10px;
        }

        .date-day {
            font-size: 2rem;
            font-weight: bold;
            color: #ffc107;
            line-height: 1;
        }

        .date-month {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #ccc;
        }

        .slots-badge {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid #ffc107;
            color: #ffc107;
        }

        .slots-full {
            border-color: #dc3545;
            color: #dc3545;
        }
    </style>
</head>


<body style="background-color: #121212; color: #f0f0f0;">
    <!-- navbar -->
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
                        <a class="nav-link navbar-text navbar-font-size active" href="events.php">
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
                        <li class="nav-item"><a class="nav-link navbar-text active" href="events.php"><i
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


    <main class="container py-5 mt-5">

        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-white">Joiners Schedule</h1>
            <p class="text-secondary">Join a scheduled group hike and meet new friends.</p>
        </div>

        <?php if (!empty($events_by_month)): ?>

            <?php foreach ($events_by_month as $month => $events): ?>

                <h3 class="month-header"><?php echo $month; ?></h3>

                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($events as $event):
                        // Calculate Slots
                        $slotsLeft = $event['MAXSLOTS'] - $event['TAKENSLOTS'];
                        $isFull = ($slotsLeft <= 0) || ($event['STATUS'] === 'Full');

                        // Format Date
                        $dateObj = $event['EVENTDATE']; // DateTime Object from SQL
                        $dayNum = $dateObj->format('d');
                        $dayName = $dateObj->format('D'); // Mon, Tue...
                        ?>
                        <div class="col">
                            <div class="card event-card h-100 p-3">
                                <div class="d-flex align-items-center">

                                    <div class="date-box me-3">
                                        <div class="date-day"><?php echo $dayNum; ?></div>
                                        <div class="date-month"><?php echo $dayName; ?></div>
                                    </div>

                                    <div class="flex-grow-1">
                                        <h5 class="text-white mb-1"><?php echo $event['TOUR_NAME']; ?></h5>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-secondary"><?php echo $event['DIFFICULTY']; ?>/9</span>

                                            <?php if ($isFull): ?>
                                                <span class="slots-badge slots-full">FULL</span>
                                            <?php else: ?>
                                                <span class="slots-badge">
                                                    <?php echo $slotsLeft; ?> slots left
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="text-warning mb-0">₱<?php echo number_format($event['PRICE']); ?></h5>
                                    </div>

                                    <div class="ms-3">
                                        <?php if ($isFull): ?>
                                            <button class="btn btn-secondary disabled">Closed</button>
                                        <?php else: ?>
                                            <a href="booking.php?event_id=<?php echo $event['EVENTID']; ?>"
                                                class="btn btn-warning fw-bold">
                                                Join
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endforeach; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-regular fa-calendar-xmark fa-4x text-secondary mb-3"></i>
                <h4 class="text-white">No scheduled hikes yet.</h4>
                <p class="text-secondary">Check back later or book a private tour.</p>
                <a href="tours.php" class="btn btn-outline-warning mt-3">Browse Private Tours</a>
            </div>
        <?php endif; ?>

    </main>



</body>




</html>