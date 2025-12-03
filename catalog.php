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

// 2. FETCH ACTIVE TOURS
// We select everything from TOURS_6 where IS_ACTIVE is 1
$sql = "SELECT * FROM TOURS_7 WHERE CATEGORY IN ('Others', 'Beach')";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MountPinoy - Featured Tours</title>
    
    <!-- Bootstrap CSS (Required for grid layout) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome (Required for star/bookmark icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- YOUR CUSTOM CSS STARTS HERE --- */
        body {
            background-color: #323232;
            color: white;
        }

        .featured-tours-section {
            background-color: #323232;
        }

        .tour-card {
            background-color: #1c1c1c;
            border-radius: 30px;
            overflow: hidden;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px; /* Added spacing for multiple rows */
        }

        .tour-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(47, 93, 52, 0.5);
            cursor: pointer;
        }

        .tour-card .card-body {
            padding: 1rem 1.23rem 1.5rem 1.25rem;
        }

        .card-image-wrapper {
            border-radius: 10px;
            position: relative;
        }

        .tour-card .card-img-top {
            height: 300px;
            object-fit: cover;
            width: 100%;
        }

        .favorite-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            background-color: #ffc107;
            border: none;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            color: #4CAF50;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .fav-btn {
            color: #000;
        }

        .location-tag {
            display: inline-block;
            background-color: #d8d8d8;
            color: #003930;
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 4px;
            margin-bottom: 8px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .tour-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #4CAF50; 
            line-height: 1.4;
            margin-bottom: 10px;
        }

        /* Rating Section */
        .rating-number {
            font-weight: 600;
            margin-right: 6px;
            color: #ffffff;
            font-size: 16px;
        }

        .rating-stars i {
            font-size: 0.85rem;
            color: #FFC107;
        }

        .review-count {
            font-size: 0.9rem;
            color: #fee18a;
        }

        .price-display {
            margin-top: 10px;
        }

        .price-label {
            color: white;
            font-size: 0.9rem;
        }

        .price-amount {
            font-weight: bold;
            color: #ffc107;
            font-size: 1.3rem;
            transition: text-shadow 0.3s ease, color 0.3s ease;
        }

        .price-unit {
            font-size: 0.80rem;
            color: white;
        }

        .tour-card:hover .price-amount {
            text-shadow: 0 0 8px #ffc107, 0 0 15px rgba(255, 193, 7, 0.5);
            color: #f5dc92; 
        }
    </style>
</head>
<body>

    <section class="featured-tours-section py-5">
        <div class="container py-5">
            <h2 class="section-heading mb-5 text-center text-white">
                Joiners' Top-Rated Adventures
            </h2>
            
            <div class="row py-5">
                
                <?php 
                // 3. START THE LOOP
                // This repeats the HTML card for every tour found in the database
                while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    
                    // Assign DB values to easy variables
                    $image_url = $row['IMAGE_URL'];
                    $name      = $row['TOUR_NAME'];
                    $location  = $row['LOCATION'];
                    $rating    = $row['RATING'];
                    $reviews   = $row['REVIEWS'];
                    // Format price with comma (e.g. 4,800.00)
                    $price     = number_format($row['PRICE'], 2); 
                ?>

                <!-- DYNAMIC CARD ITEM START -->
                <div class="col-lg-3 offset-lg-0 col-md-6 d-flex align-items-stretch">
                    <a href="#" class="text-decoration-none w-100">
                        <div class="card tour-card">
                            
                            <!-- IMAGE SECTION -->
                            <div class="card-image-wrapper">
                                <div class="zoom-wrapper">
                                    <!-- PHP: Pulls the image path from DB -->
                                    <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo $name; ?>">
                                </div>
                                <button class="btn btn-light rounded-circle favorite-btn" aria-label="Add to favorites">
                                    <i class="fa-regular fa-bookmark fav-btn"></i>
                                </button>
                            </div>
                            
                            <!-- DETAILS SECTION -->
                            <div class="card-body">
                                <!-- PHP: Location -->
                                <span class="location-tag"><?php echo $location; ?></span>
                                
                                <!-- PHP: Tour Name -->
                                <h5 class="card-title tour-title"><?php echo $name; ?></h5>
                                
                                <div class="d-flex align-items-center mb-2">
                                    <!-- PHP: Rating -->
                                    <span class="rating-number"><?php echo $rating; ?></span>
                                    
                                    <span class="rating-stars me-2">
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                        <i class="fa-solid fa-circle"></i>
                                    </span>
                                    
                                    <!-- PHP: Reviews -->
                                    <span class="review-count"><?php echo $reviews; ?></span>
                                </div>
                                
                                <div class="price-display mt-10">
                                    <span class="price-label">from</span>
                                    <!-- PHP: Price -->
                                    <span class="price-amount">₱<?php echo $price; ?></span>
                                    <span class="price-unit">per person</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- DYNAMIC CARD ITEM END -->

                <?php 
                } // End While Loop
                ?>

            </div>
        </div>
    </section>

    <!-- Bootstrap JS (Optional, for interactivity if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>