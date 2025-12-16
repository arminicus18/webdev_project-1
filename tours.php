<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: index.html");
    exit();
}
?>



<!DOCTYPE html>

<html>

<head>
    <title>
        Mountains and Tours
    </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" type="text/css" href="mountpinoy.css">

    <link rel="stylesheet" type="text/css" href="tours.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="https://kit.fontawesome.com/97fe9f84ce.js" crossorigin="anonymous"></script>
    <script defer src="mountpinoy.js"></script>
    <script src="navbar.js"></script>
    <script src="bookmark.js"></script>



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
                        <li class="nav-item"><a class="nav-link navbar-text active" href="tours.php"><i
                                    class="fa-solid fa-person-hiking fa-lg nav-icon me-2"></i> Tours</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="events.php"><i
                                    class="fa-regular fa-calendar-days fa-lg nav-icon me-2"></i> Events</a></li>
                        <li class="nav-item"><a class="nav-link navbar-text" href="tips.php"><i
                                    class="fa-solid fa-circle-info fa-lg nav-icon me-2"></i> Tips</a></li>
                    </ul>
                    <div class="mb-3">
                        <form class="d-flex input-group" role="search" onsubmit="handleSearch(event)">
                            <input class="form-control navbar-search" type="search" name="q"
                                placeholder="Search mountains..." aria-label="Search"
                                style="background-color: #222; border: 1px solid #555; color: white;">
                            <button class="btn btn-warning" type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </form>
                    </div>

                    <div class="mt-4">
                        <a class="text-danger text-decoration-none" href="logout.php">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <!-- top adventure -->
        <header class="tours-hero">
            <div class="container">
                <span class="badge bg-warning text-dark mb-2">FEATURED ADVENTURE</span>
                <h1 class="display-3 fw-bold text-white">Mt. Kabunian Day Hike</h1>
                <p class="text-light">Experience the home of the gods in Bakun, Benguet.</p>
                <a href="tour_details.php?id=118">
                    <button class="btn btn-warning fw-bold px-4 rounded-pill">View Details</button>
                </a>
            </div>
        </header>


        <!-- epic games like nav -->
        <div class="container mb-5">

            <!-- beginner -->
            <div class="section-header" id="beginner-section">
                <h3 class="fw-bold text-white">Beginner Friendly Hikes</h3>
                <div>
                    <button class="nav-btn" onclick="window.scrollRow('beginner-row', -1)"><i
                            class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn" onclick="window.scrollRow('beginner-row', 1)"><i
                            class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div id="beginner-row" class="scrolling-wrapper">
                <p class="text-secondary ms-2"></p>
            </div>

            <!-- intermediate -->
            <div class="section-header" id="intermediate-section">
                <h3 class="fw-bold text-white">Intermediates for Experienced Hikers</h3>
                <div>
                    <button class="nav-btn" onclick="window.scrollRow('intermediate-row', -1)"><i
                            class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn" onclick="window.scrollRow('intermediate-row', 1)"><i
                            class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div id="intermediate-row" class="scrolling-wrapper"></div>

            <!-- advance -->
            <div class="section-header" id="advance-section">
                <h3 class="fw-bold text-white">Advanced Hikes for Thrill Seeker</h3>
                <div>
                    <button class="nav-btn" onclick="window.scrollRow('advanced-row', -1)"><i
                            class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn" onclick="window.scrollRow('advanced-row', 1)"><i
                            class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div id="advanced-row" class="scrolling-wrapper"></div>

            <!-- expert -->
            <div class="section-header" id="expert-section">
                <h3 class="fw-bold text-white">Expert Hikes for Paramount Adventures</h3>
                <div>
                    <button class="nav-btn" onclick="window.scrollRow('expert-row', -1)"><i
                            class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn" onclick="window.scrollRow('expert-row', 1)"><i
                            class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div id="expert-row" class="scrolling-wrapper"></div>

            <!-- beach and others -->
            <div class="section-header">
                <h3 class="fw-bold text-white">Beach & Other Adventures</h3>
                <div>
                    <button class="nav-btn" onclick="window.scrollRow('others-row', -1)">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button class="nav-btn" onclick="window.scrollRow('others-row', 1)">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div id="others-row" class="scrolling-wrapper"></div>

            <!-- by Luzon, Visayas, Mindanao -->
            <hr class="border-secondary my-5">
            <div class="container mb-5">
                <hr class="border-secondary my-5">
                <div class="d-flex flex-column align-items-center mb-5" id="discover-region">
                    <h3 class="fw-bold text-white mb-4">Explore by Region</h3>

                    <ul class="nav nav-pills" id="island-pills">
                        <li class="nav-item">
                            <a class="nav-link island-pill active" href="#">Luzon</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link island-pill" href="#">Visayas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link island-pill" href="#">Mindanao</a>
                        </li>
                    </ul>
                </div>
                <div id="island-grid-container" class="row g-3">
                    <p class="text-center text-secondary">Loading tours...</p>
                </div>
            </div>

        </div>

        <!-- modal for saving the tour -->
        <div class="modal fade" id="loginPromptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content custom-dark-modal">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body text-center px-4 pb-4">
                        <div class="mb-3" style="color: #FFC107;">
                            <i class="fa-solid fa-bookmark fa-3x"></i>
                            <!--
                            <span class="logo-mount">Mount</span><span class="logo-pinoy">Pinoy</span>
                            -->
                        </div>

                        <h4 class="modal-title fw-bold mb-3 text-white">Save your adventure!</h4>
                        <p class="small mb-4" style="color: #bbb;">
                            Sign in to save this hike to your wishlist so you can easily compare trails later.
                        </p>


                        <div class="d-grid gap-3">
                            <button class="btn btn-outline-light d-flex align-items-center justify-content-center py-2"
                                type="button">
                                Sign Up / Log In with Email
                            </button>

                            <button class="btn text-white py-2 fw-bold mt-2"
                                style="background-color: #4CAF50; border: none;" type="button">
                                <i class="fa-brands fa-google me-2"></i> Continue with google
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </main>

    <!-- footer section -->
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


    <script src="tours_display.js"></script>
    <script src="tours_other.js"></script>
    <script src="tours_island.js"></script>


</body>



</html>