<?php
// 1. CHECK LOGIN
if (isset($_SESSION['user_id']) && isset($conn)) {

    $current_user_id = $_SESSION['user_id'];

    // 2. FETCH DATA (Wrapped in try/catch logic via IF checks)
    // Ensure table names match your DB: WISHLIST_1, TOURS_7
    $sql = "SELECT 
                w.WishlistID,
                t.TOUR_ID,
                t.TOUR_NAME,
                t.PRICE,
                t.IMAGE_URL
            FROM WISHLIST_1 w
            JOIN TOURS_7 t ON w.TourID = t.TOUR_ID 
            WHERE w.UserID = ?";

    $params = array($current_user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    // SAFETY CHECK: If query fails, do NOT kill the page. Just stop this section.
    if ($stmt === false) {
        // Log error to console only (invisible to user)
        // echo "<script>console.error('Wishlist Float Error: " . print_r(sqlsrv_errors(), true) . "');</script>";
        return; 
    }

    $wishlist_items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $wishlist_items[] = $row;
    }

    $count = count($wishlist_items);
    ?>

    <button class="btn btn-warning rounded-circle shadow-lg position-fixed d-flex align-items-center justify-content-center"
        type="button" data-bs-toggle="offcanvas" data-bs-target="#wishlistCanvas"
        style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border: 2px solid #fff;">

        <i class="fa-solid fa-heart text-white fs-4"></i>

        <?php if ($count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                <?php echo $count; ?>
            </span>
        <?php endif; ?>
    </button>

    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="wishlistCanvas">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title text-warning">
                <i class="fa-solid fa-person-hiking me-2"></i>My Adventures
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
            <div class="d-flex flex-column gap-3">

                <?php if ($count > 0): ?>
                    <?php foreach ($wishlist_items as $item): ?>
                        <div class="card bg-secondary bg-opacity-10 border-secondary">
                            <div class="d-flex">
                                <img src="<?php echo $item['IMAGE_URL']; ?>" class="rounded-start"
                                    style="width: 80px; object-fit: cover;" alt="...">

                                <div class="card-body p-2 d-flex flex-column justify-content-center">
                                    <h6 class="card-title mb-1 text-white"><?php echo $item['TOUR_NAME']; ?></h6>
                                    <p class="card-text small text-warning mb-0">₱ <?php echo number_format($item['PRICE'], 2); ?>
                                    </p>
                                </div>

                                <div class="p-2 d-flex flex-column justify-content-between">
                                    <a href="#" class="btn btn-sm text-danger"><i class="fa-solid fa-trash"></i></a>

                                    <a href="booking.php?id=<?php echo $item['TOUR_ID']; ?>" class="btn btn-sm btn-primary py-0"
                                        style="font-size: 0.8rem;">Book</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted mt-4">Your wishlist is empty.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

<?php
}
?>