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
        Frequently Asked Questions
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
                        <a class="nav-link navbar-text navbar-font-size active" href="tips.php">
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
                        <li class="nav-item"><a class="nav-link navbar-text active" href="tips.php"><i
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




    <main>

    </main>



</body>


</html>