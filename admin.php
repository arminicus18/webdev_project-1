<?php
// 1. DATABASE CONNECTION
$serverName = "ARMINICUS-18\SQLEXPRESS";
$connectionOptions = [
    "Database" => "fp_catalog-2",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) { die(print_r(sqlsrv_errors(), true)); }

// 2. FETCH STATS (COUNTS)
// Count ALL tours
$sql_count_all = "SELECT COUNT(*) as total FROM TOURS_7";
$stmt_all = sqlsrv_query($conn, $sql_count_all);
$row_all = sqlsrv_fetch_array($stmt_all, SQLSRV_FETCH_ASSOC);
$total_tours = $row_all['total'];

// Count ACTIVE tours only
$sql_count_active = "SELECT COUNT(*) as active FROM TOURS_7 WHERE IS_ACTIVE = 1";
$stmt_active = sqlsrv_query($conn, $sql_count_active);
$row_active = sqlsrv_fetch_array($stmt_active, SQLSRV_FETCH_ASSOC);
$active_tours = $row_active['active'];

// 3. FETCH RECENT TOURS (For the Table)
$sql_list = "SELECT TOP 20 TOUR_ID, TOUR_NAME, PRICE, ISLAND, IS_ACTIVE, CATEGORY FROM TOURS_7 ORDER BY TOUR_ID DESC";
$stmt_list = sqlsrv_query($conn, $sql_list);
?>

<!DOCTYPE html>
<html>
<head>
    <title>MountPinoy Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="mountpinoy.css">
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">MountPinoy Admin Panel</span>
            <a href="index.html" class="btn btn-outline-light btn-sm" target="_blank">View Live Website &rarr;</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card card-stat bg-primary-gradient p-3">
                    <h3><?php echo $total_tours; ?></h3>
                    <span>Total Tours Listed</span>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card card-stat bg-success-gradient p-3">
                    <h3><?php echo $active_tours; ?></h3>
                    <span>Active / Visible Tours</span>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-6 d-grid">
                <a href="addtours.html" class="btn btn-primary btn-lg py-3 shadow-sm">
                    <strong>+ Edit/Create New Tour</strong> <br>
                    <small>Step 1: Basic Info, Price, & Image</small>
                </a>
            </div>
            <div class="col-md-6 d-grid">
                <a href="add_details.php" class="btn btn-success btn-lg py-3 shadow-sm">
                    <strong>+ Add Tour Details</strong> <br>
                    <small>Step 2: Itinerary, Map, & Inclusions</small>
                </a>
            </div>
        </div>

        <div class="table-container">
            <h4 class="mb-3">Tour Inventory (Recent)</h4>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tour Name</th>
                        <th>Category</th>
                        <th>Island</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Loop through the list
                    while($row = sqlsrv_fetch_array($stmt_list, SQLSRV_FETCH_ASSOC)) { 
                        // Determine Status Color
                        $status_class = ($row['IS_ACTIVE'] == 1) ? 'status-active' : '';
                        $status_text = ($row['IS_ACTIVE'] == 1) ? 'Active' : 'Hidden';
                    ?>
                        <tr>
                            <td><strong><?php echo $row['TOUR_ID']; ?></strong></td>
                            <td><?php echo $row['TOUR_NAME']; ?></td>
                            <td><?php echo $row['CATEGORY']; ?></td>
                            <td><?php echo $row['ISLAND']; ?></td>
                            <td>₱<?php echo number_format($row['PRICE'], 2); ?></td>
                            <td>
                                <span class="status-dot <?php echo $status_class; ?>"></span> 
                                <?php echo $status_text; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <?php if ($total_tours == 0) { ?>
                <p class="text-center text-muted py-3">No tours found. Create one to get started!</p>
            <?php } ?>
        </div>

    </div>

</body>
</html>