<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: index.html");
    exit();
}

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

// 2. GET THE TOUR ID
if (!isset($_GET['id'])) {
    header("Location: tours.html"); // Redirect if no ID
    exit();
}
$tour_id = $_GET['id'];

// 3. FETCH DATA
$sql_main = "SELECT * FROM TOURS_7 WHERE TOUR_ID = ?";
$stmt_main = sqlsrv_query($conn, $sql_main, array($tour_id));
$tour = sqlsrv_fetch_array($stmt_main, SQLSRV_FETCH_ASSOC);

if (!$tour) {
    die("Tour not found!");
}

$sql_details = "SELECT * FROM DETAILS_1 WHERE TOUR_ID = ?";
$stmt_details = sqlsrv_query($conn, $sql_details, array($tour_id));
$details = sqlsrv_fetch_array($stmt_details, SQLSRV_FETCH_ASSOC);

$sql_inc = "SELECT * FROM INCLUSIONS_1 WHERE TOUR_ID = ?";
$stmt_inc = sqlsrv_query($conn, $sql_inc, array($tour_id));

$sql_itin = "SELECT * FROM ITINERARY_1 WHERE TOUR_ID = ?";
$stmt_itin = sqlsrv_query($conn, $sql_itin, array($tour_id));

// 4. CHECK WISHLIST STATE
$is_wishlisted = false;

if (isset($_SESSION['user_id'])) {
    $check_user_id = $_SESSION['user_id'];
    $current_tour_id = $_GET['id'];

    // Uses TourID as confirmed in DB
    $check_sql = "SELECT * FROM WISHLIST_1 WHERE UserID = ? AND TourID = ?";
    $check_params = array($check_user_id, $current_tour_id);
    $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);

    if ($check_stmt && sqlsrv_has_rows($check_stmt)) {
        $is_wishlisted = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <title><?php echo $tour['TOUR_NAME']; ?> | MountPinoy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/97fe9f84ce.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="mountpinoy.css">
    <link rel="stylesheet" type="text/css" href="tour_details.css">

    <style>
        :root {
            --brand-accent: #ffc107;
            /* The MountPinoy Yellow */
            --brand-dark: #121212;
            --brand-card-bg: #1e1e1e;
        }

        body {
            background-color: var(--brand-dark);
            color: #f0f0f0;
            /* Space for fixed navbar */
        }

        /* --- HERO SECTION --- */
        .tour-header {
            background: linear-gradient(to bottom, rgba(18, 18, 18, 0.4), var(--brand-dark)), url('<?php echo $tour['IMAGE_URL']; ?>');
            background-size: cover;
            background-position: center;
            height: 60vh;
            /* Takes up 60% of screen height */
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 60px;
            margin-bottom: 40px;
        }
    </style>
</head>

<body>
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
                        <a class="nav-link navbar-text navbar-font-size active" href="tours.php">
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

                    <?php if (isset($_SESSION['user_name'])): ?>

                        <div class="dropdown">
                            <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="userDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">

                                <img src="<?php echo $_SESSION['user_picture']; ?>" alt="Profile" class="rounded-circle"
                                    style="width: 35px; height: 35px; object-fit: cover; border: 2px solid #FFC107;">
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

                    <?php else: ?>

                        <a href="https://accounts.google.com/o/oauth2/auth?response_type=code&client_id=894735970360-ffmjspg7espidrlnv8addt1r1d7tiuam.apps.googleusercontent.com&redirect_uri=http://localhost/final_project/google_callback.php&scope=email%20profile&access_type=online"
                            class="btn btn-login">
                            Login
                        </a>

                    <?php endif; ?>
                </div>

            </div>

            <div class="offcanvas offcanvas-end navbar-custom navbar-dark d-lg-none" tabindex="-1" id="navbarOffcanvas">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title text-white">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column justify-content-between">
                    <ul class="navbar-nav nav-text">
                        <li class="nav-item"><a class="nav-link navbar-text" href="index.html"><i
                                    class="fa-regular fa-house fa-lg nav-icon me-2"></i> Home</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="tours.html"><i
                                    class="fa-solid fa-person-hiking fa-lg nav-icon me-2"></i> Tours</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="events.html"><i
                                    class="fa-regular fa-calendar-days fa-lg nav-icon me-2"></i> Events</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="tips.html"><i
                                    class="fa-solid fa-circle-info fa-lg nav-icon me-2"></i> Tips</a></li>
                    </ul>

                    <div class="mt-4">
                        <a href="login.html" class="btn btn-login w-100">Login / Sign Up</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <!-- hero section -->
        <header class="tour-header">
            <div class="container text-center">
                <h1 class="display-3 hero-title"><?php echo $tour['TOUR_NAME']; ?></h1>
                <p class="lead text-white fw-bold">
                    <i class="fa-solid fa-location-dot text-warning"></i> <?php echo $tour['LOCATION']; ?> •
                    <?php echo $tour['ISLAND']; ?>
                </p>
                <span class="badge bg-warning text-dark fs-6 mt-2 px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-gauge-high"></i> Difficulty: <?php echo $tour['DIFFICULTY']; ?> / 9
                </span>

                <?php if (!empty($tour['MASL'])) { ?>
                    <div class="elevation-value mt-2">
                        <i class="bi bi-caret-up-fill icon"></i> <?php echo $tour['MASL']; ?>
                    </div>
                <?php } ?>

            </div>
        </header>

        <div class="container pb-5">
            <div class="row">

                <div class="col-lg-8">
                    <!-- description -->
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="card-title"><i class="fa-solid fa-mountain-sun me-2"></i> About this Adventure
                            </h3>
                            <p class="card-text text-light" style="line-height: 1.8; font-size: 1.1rem;">
                                <?php echo isset($details['DESCRIPTION']) ? nl2br($details['DESCRIPTION']) : "No description available yet."; ?>
                            </p>
                        </div>
                    </div>

                    <!-- inclusions -->
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="card-title"><i class="fa-solid fa-check-double me-2"></i> What's Included</h3>
                            <div class="inclusions-grid mt-3">
                                <?php while ($inc = sqlsrv_fetch_array($stmt_inc, SQLSRV_FETCH_ASSOC)) { ?>

                                    <div class="inclusion-item">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span><?php echo $inc['ITEM_NAME']; ?></span>
                                    </div>

                                <?php } ?>
                            </div>

                        </div>
                    </div>

                    <!-- location -->
                    <?php if (!empty($details['GMAPS_LOC'])) { ?>
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h3 class="card-title"><i class="fa-solid fa-map-location-dot me-2"></i> Location</h3>
                                <div class="ratio ratio-16x9 mt-3">
                                    <?php echo $details['GMAPS_LOC']; ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- microservice UNSPLASH -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-0 overflow-hidden rounded">

                            <div id="tourGalleryCarousel" class="carousel slide" data-bs-ride="carousel"
                                data-bs-interval="2000" data-name="<?php echo $tour['TOUR_NAME']; ?>"
                                data-location="<?php echo $tour['LOCATION']; ?>">
                                <?php if (isset($tour['GALLERY_QUERY'])) { ?>
                                    data-custom-search="<?php echo $tour['GALLERY_QUERY']; ?>"
                                <?php } ?>

                                <div class="carousel-indicators" id="carousel-indicators">
                                </div>

                                <div class="carousel-inner" id="gallery-container">
                                    <div class="carousel-item active" style="height: 400px; background: #111;">
                                        <div class="d-flex justify-content-center align-items-center h-100">
                                            <div class="spinner-border text-warning" role="status"></div>
                                        </div>
                                    </div>
                                </div>

                                <button class="carousel-control-prev" type="button"
                                    data-bs-target="#tourGalleryCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button"
                                    data-bs-target="#tourGalleryCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-dark border-top border-secondary py-2">
                            <small class="text-warning"><i class="fa-solid fa-camera-retro me-2"></i>From Previous
                                Joiners</small>
                        </div>
                    </div>

                    <!-- itinerary -->
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="card-title"><i class="fa-regular fa-clock me-2"></i> Itinerary</h3>

                            <div class="timeline-track mt-4">
                                <?php while ($itin = sqlsrv_fetch_array($stmt_itin, SQLSRV_FETCH_ASSOC)) {
                                    // 1. LOGIC: Check if this row is the "Summit"
                                    $isSummit = (stripos($itin['ACTIVITY_DESC'], 'Summit') !== false);

                                    // 2. LOGIC: Set the class variable if true
                                    $specialClass = $isSummit ? 'summit-row' : '';
                                    ?>

                                    <div class="timeline-row <?php echo $specialClass; ?>">
                                        <div class="time-col">
                                            <span class="time-display"><?php echo $itin['TIME_MARKER']; ?></span>
                                        </div>

                                        <div class="track-col">
                                            <div class="track-line"></div>
                                            <div class="track-dot"></div>
                                        </div>

                                        <div class="activity-col">
                                            <div class="activity-box">
                                                <?php if ($isSummit) { ?>
                                                    <i class="fa-solid fa-flag text-warning me-2"></i>
                                                <?php } ?>

                                                <?php echo $itin['ACTIVITY_DESC']; ?>
                                            </div>
                                        </div>
                                    </div>

                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- yt vid embed -->
                    <?php if (!empty($details['YT_LINK'])) { ?>
                        <div class="card shadow-sm mt-4">
                            <div class="card-body p-4">
                                <h3 class="card-title"><i class="fa-brands fa-youtube me-2"></i> Video Tour</h3>

                                <div class="ratio ratio-16x9 mt-3">
                                    <?php echo $details['YT_LINK']; ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </div>

                <aside class="col-lg-4 d-none d-lg-block">
                    <div class="sticky-sidebar">

                        <div class="card shadow-sm mb-4 border-warning" id="weather-card" style="display: none;">
                            <div class="card-body p-3">
                                <h5 class="card-title text-warning mb-3">
                                    <i class="fa-solid fa-cloud-sun-rain me-2"></i> Weather Forecast
                                </h5>

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <img id="w-icon" src="" alt="Weather Icon" style="width: 50px; height: 50px;">
                                        <div class="ms-2">
                                            <h2 class="mb-0 fw-bold text-white" id="w-temp">--°C</h2>
                                            <small class="text-secondary" id="w-text">Loading...</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="d-block text-secondary">Chance of Rain</small>
                                        <strong class="text-info" id="w-rain">--%</strong>
                                    </div>
                                </div>

                                <div class="weather-forecast bg-dark rounded p-2">
                                    <div class="row text-center g-0" id="forecast-container">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card booking-card p-4">
                            <div class="text-center mb-3">
                                <small class="text-secondary text-uppercase ls-2">Price per Person</small>
                                <h2 class="text-white fw-bold display-4 mt-2">
                                    ₱<?php echo number_format($tour['PRICE'], 2); ?></h2>
                            </div>

                            <hr style="border-color: #444;">

                            <div class="mb-3">
                                <strong class="text-warning"><i class="fa-solid fa-location-arrow"></i> Meeting
                                    Point:</strong><br>
                                <span class="text-light ms-3 d-block mt-1">
                                    <?php echo isset($details['MEETING_POINT']) ? $details['MEETING_POINT'] : "To Be Announced"; ?>
                                </span>
                            </div>

                            <div class="mb-4">
                                <strong class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> Important
                                    Note:</strong><br>
                                <small class="text-light ms-3 d-block mt-1 fst-italic">
                                    <?php echo isset($details['NOTES']) ? $details['NOTES'] : "Standard hiking safety protocols apply."; ?>
                                </small>
                            </div>

                            <div class="d-grid gap-2">

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="booking.php?id=<?php echo $tour['TOUR_ID']; ?>"
                                        class="btn btn-warning btn-lg fw-bold py-3">
                                        Book This Tour
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-warning btn-lg fw-bold py-3" data-bs-toggle="modal"
                                        data-bs-target="#loginPromptModal">
                                        Book This Tour
                                    </button>
                                <?php endif; ?>

                                <?php
                                $btnClass = $is_wishlisted ? "btn-danger text-white" : "btn-outline-light";
                                $iconClass = $is_wishlisted ? "fa-solid" : "fa-regular";
                                $btnText = $is_wishlisted ? "Saved to Wishlist" : "Add to Wishlist";
                                ?>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="btn <?php echo $btnClass; ?>" data-id="<?php echo $tour['TOUR_ID']; ?>"
                                        onclick="toggleWishlist(this)">
                                        <i class="<?php echo $iconClass; ?> fa-heart"></i>
                                        <span><?php echo $btnText; ?></span>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-light" data-bs-toggle="modal"
                                        data-bs-target="#loginPromptModal">
                                        <i class="fa-regular fa-heart"></i> Add to Wishlist
                                    </button>
                                <?php endif; ?>

                            </div>
                        </div>


                    </div>
                </aside>

            </div>
        </div>

    </main>

    <footer class="footer mt-auto py-5 text-white" style="background-color: #1a1a1a;">
        <div class="container">
            <div class="row">

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-3">
                        <span class="logo-mount">Mount</span><span class="logo-pinoy">Pinoy</span>
                    </h5>
                    <p class="text-secondary small">
                        Your trusted guide to exploring the most majestic peaks and hidden trails across the
                        Philippines. Safety, sustainability, and adventure.
                    </p>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-success mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="tours.html" class="text-white text-decoration-none small">All
                                Tours & Expeditions</a></li>
                        <li class="mb-2"><a href="about.html" class="text-white text-decoration-none small">About
                                Us</a></li>
                        <li class="mb-2"><a href="faqs.html" class="text-white text-decoration-none small">FAQ &
                                Preparation</a></li>
                        <li class="mb-2"><a href="blog.html" class="text-white text-decoration-none small">Adventure
                                Blog</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-success mb-3">Get in Touch</h5>
                    <ul class="list-unstyled text-secondary small">
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-warning"></i> info@mountpinoy.ph</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-warning"></i> +63 923 417 5772</li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-warning"></i> Carmona, Cavite,
                            Philippines</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-success mb-3">Connect</h5>
                    <a href="https://www.facebook.com/armin.derosas.18" class="text-white me-3"><i
                            class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="https://www.instagram.com/arminicus18/" class="text-white me-3"><i
                            class="fab fa-instagram fa-lg"></i></a>
                    <a href="https://www.youtube.com/@arminderosas9461" class="text-white me-3"><i
                            class="fab fa-youtube fa-lg"></i></a>

                    <p class="text-secondary mt-3 small">Subscribe for early bird specials.</p>
                    <form>
                        <div class="input-group">
                            <input type="email" class="form-control form-control-sm" placeholder="Your Email"
                                aria-label="Email for newsletter">
                            <button class="btn btn-warning btn-sm" type="submit">Go</button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="border-secondary">
            <div class="row">
                <div class="col-12 text-center small text-secondary pt-3">
                    &copy; 2025 MountPinoy Expeditions. All rights reserved. | <a href="#"
                        class="text-warning text-decoration-none">DE ROSAS ARMIN CPE41</a>
                </div>
            </div>
        </div>
    </footer>

    <?php if (!isset($_SESSION['user_name'])): ?>

        <div class="modal fade" id="loginPromptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content custom-dark-modal" style="background-color: #1e1e1e; border: 1px solid #333;">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center px-4 pb-4">
                        <div class="mb-3" style="color: #FFC107;">
                            <i class="fa-solid fa-bookmark fa-3x"></i>
                        </div>
                        <h4 class="modal-title fw-bold mb-3 text-white">Save your adventure!</h4>
                        <p class="small mb-4" style="color: #bbb;">
                            Sign in to book this hike or save it to your wishlist.
                        </p>
                        <div class="d-grid gap-3">
                            <a href="https://accounts.google.com/o/oauth2/auth?response_type=code&client_id=894735970360-ffmjspg7espidrlnv8addt1r1d7tiuam.apps.googleusercontent.com&redirect_uri=http://localhost/final_project/google_callback.php&scope=email%20profile&access_type=online"
                                class="btn text-white py-2 fw-bold mt-2" style="background-color: #4CAF50; border: none;">
                                <i class="fa-brands fa-google me-2"></i> Continue with Google
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>

    <?php endif; ?>

    <div class="d-lg-none fixed-bottom bg-dark border-top border-secondary p-2 shadow-lg" style="z-index: 1050;">
        <div class="container">

            <div class="d-flex align-items-center justify-content-center mb-2 py-1 rounded bg-secondary bg-opacity-25"
                id="mobile-weather-row" style="display: none !important;">
                <img id="mw-icon" src="" alt="" style="width: 24px; height: 24px; margin-right: 8px;">
                <span id="mw-text" class="text-warning small me-2" style="font-size: 0.8rem;">Loading...</span>
                <span class="text-white fw-bold small" id="mw-temp">--°C</span>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-secondary text-uppercase d-block"
                        style="font-size: 0.65rem; line-height: 1;">Price per person</small>
                    <h4 class="text-white m-0 fw-bold">₱<?php echo number_format($tour['PRICE']); ?></h4>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="booking.php?id=<?php echo $tour['TOUR_ID']; ?>" class="btn btn-warning fw-bold px-4 py-2">
                        Book Now
                    </a>
                <?php else: ?>
                    <button class="btn btn-warning fw-bold px-4 py-2" data-bs-toggle="modal"
                        data-bs-target="#loginPromptModal">
                        Book Now
                    </button>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script src="navbar.js"></script>
    <script src="service_gallery.js"></script>
    <script src="service_weather.js"></script>
    <script src="wishlist_process.js"></script>

</body>

</html>