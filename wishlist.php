<?php
session_start();

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.html");
    exit();
}

// 2. DATABASE CONNECTION
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

// 3. FETCH WISHLIST ITEMS
// We join WISHLIST_1 with TOURS_7 to get the tour details
$sql = "SELECT 
            w.WishlistID,
            t.TOUR_ID,
            t.TOUR_NAME,
            t.PRICE,
            t.IMAGE_URL,
            t.LOCATION,
            t.DIFFICULTY
        FROM WISHLIST_1 w
        JOIN TOURS_7 t ON w.TourID = t.TOUR_ID
        WHERE w.UserID = ?
        ORDER BY w.DateAdded DESC";

$params = array($user_id);
$stmt = sqlsrv_query($conn, $sql, $params);

$wishlist_items = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Use array_change_key_case to ensure uppercase keys (fixes the image loading issue)
        $wishlist_items[] = array_change_key_case($row, CASE_UPPER);
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <title>My Wishlist | MountPinoy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/97fe9f84ce.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="mountpinoy.css">

    <style>
        body {
            background-color: #2c2c2c;
            color: #f0f0f0;
        }

        .card {
            background-color: #1e1e1e;
            border: 1px solid #333;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #ffc107;
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .navbar-custom {
            background-color: #000;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand navbar-brand-text" href="index.html">
                <span class="logo-mount">Mount</span><span class="logo-pinoy">Pinoy</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <a href="tours.php" class="btn btn-outline-light btn-sm">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to Tours
                </a>

                <div class="dropdown">
                    <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle"
                        data-bs-toggle="dropdown">
                        <img src="<?php echo isset($_SESSION['user_picture']) ? $_SESSION['user_picture'] : 'images/default_user.png'; ?>"
                            alt="mdo" width="32" height="32" class="rounded-circle border border-warning">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark text-small shadow">
                        <li><a class="dropdown-item" href="index.php">Home</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="text-warning mb-4"><i class="fa-solid fa-heart me-2"></i>My Wishlist</h2>

        <?php if (count($wishlist_items) > 0): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo !empty($item['IMAGE_URL']) ? $item['IMAGE_URL'] : 'images/default_tour.jpg'; ?>"
                                class="card-img-top" alt="Tour Image">

                            <div class="card-body">
                                <h5 class="card-title text-white"><?php echo $item['TOUR_NAME']; ?></h5>
                                <p class="card-text text-secondary small">
                                    <i class="fa-solid fa-location-dot text-warning"></i> <?php echo $item['LOCATION']; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <h4 class="text-warning fw-bold mb-0">₱<?php echo number_format($item['PRICE'], 2); ?></h4>
                                    <span class="badge bg-secondary"><?php echo $item['DIFFICULTY']; ?>/9 Difficulty</span>
                                </div>
                            </div>

                            <div
                                class="card-footer bg-transparent border-top border-secondary p-3 d-flex justify-content-between gap-2">
                                <button onclick="removeFromWishlist(<?php echo $item['TOUR_ID']; ?>, this)"
                                    class="btn btn-outline-danger btn-sm w-50">
                                    <i class="fa-solid fa-trash"></i> Remove
                                </button>

                                <a href="booking.php?id=<?php echo $item['TOUR_ID']; ?>"
                                    class="btn btn-warning btn-sm fw-bold w-50">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-regular fa-folder-open fa-4x text-secondary mb-3"></i>
                <h4 class="text-white">Your wishlist is empty.</h4>
                <p class="text-secondary">Looks like you haven't saved any adventures yet.</p>
                <a href="tours.php" class="btn btn-warning mt-3 px-4 py-2 fw-bold">Explore Tours</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function removeFromWishlist(tourId, btnElement) {
            if (!confirm("Remove this tour from your wishlist?")) return;

            const formData = new FormData();
            formData.append('tour_id', tourId);

            // We reuse your existing wishlist_process.php
            // Since it toggles items, sending an existing ID will remove it.
            fetch('wishlist_process.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.action === 'removed') {
                        // Animation: Fade out card then remove it
                        const cardCol = btnElement.closest('.col');
                        cardCol.style.transition = 'all 0.5s ease';
                        cardCol.style.opacity = '0';
                        cardCol.style.transform = 'scale(0.9)';

                        setTimeout(() => {
                            cardCol.remove();
                            // Optional: Reload if list becomes empty to show "Empty State"
                            if (document.querySelectorAll('.col').length === 0) {
                                location.reload();
                            }
                        }, 500);
                    } else {
                        alert("Failed to remove: " + (data.message || "Unknown error"));
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>

</html>