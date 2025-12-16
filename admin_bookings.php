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

// 2. FETCH ALL BOOKINGS
// We sort by BookingDate DESC so the newest bookings appear at the top.
// NEW: Added LEFT JOIN to EVENTS_1 to get slot data
$sql = "SELECT 
            b.BookingID,
            b.BookingDate,
            b.JoinerName,
            b.ContactNumber,
            b.Pax,
            b.TotalPrice,
            b.Status,
            b.TravelDate,
            t.TOUR_NAME,
            e.MaxSlots,    
            e.TakenSlots   
        FROM BOOKINGS_1 b
        JOIN TOURS_7 t ON b.TourID = t.TOUR_ID
        LEFT JOIN EVENTS_1 e ON b.EventID = e.EventID
        ORDER BY b.BookingDate DESC";

$stmt = sqlsrv_query($conn, $sql);

// 3. AUTO-COMPLETE PAST BOOKINGS
$sql_update = "UPDATE BOOKINGS_1 
               SET Status = 'Completed' 
               WHERE TravelDate < CAST(GETDATE() AS DATE) 
               AND Status = 'Confirmed'";

$stmt_update = sqlsrv_query($conn, $sql_update);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Manage Bookings | Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .bg-confirmed {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .bg-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }

        .bg-pending {
            background-color: #fff3cd;
            color: #664d03;
        }

        .bg-completed {
            background-color: #e2e3e5;
            color: #41464b;
            border: 1px solid #d3d6d8;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0 h1">MountPinoy Admin</span>
            <div>
                <a href="admin.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="index.html" class="btn btn-warning btn-sm" target="_blank">Live Site</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Booking Management</h3>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer"></i> Print List
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Ref ID</th>
                                <th>Customer (Joiner)</th>
                                <th>Tour Name</th>
                                <th>Travel Date</th>
                                <th>Pax</th>
                                <th>Slots Status</th>
                                <th>Contact</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($stmt) {
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    // Formatting
                                    $refID = str_pad($row['BookingID'], 6, '0', STR_PAD_LEFT);
                                    $travelDate = $row['TravelDate']->format('M d, Y');
                                    $price = number_format($row['TotalPrice'], 2);

                                    // Status Badge Color
                                    $statusClass = 'bg-pending';
                                    if ($row['Status'] == 'Confirmed')
                                        $statusClass = 'bg-confirmed';
                                    if ($row['Status'] == 'Cancelled')
                                        $statusClass = 'bg-cancelled';
                                    if ($row['Status'] == 'Completed')
                                        $statusClass = 'bg-completed';

                                    // Calculate Remaining Slots
                                    // If MaxSlots is NULL, it means it's not a Joiner Event (likely Exclusive)
                                    if (isset($row['MaxSlots'])) {
                                        $remaining = $row['MaxSlots'] - $row['TakenSlots'];
                                        // Color code the slots
                                        $slotBadge = $remaining <= 5 ? 'text-danger fw-bold' : 'text-success';
                                        $slotsDisplay = "<span class='$slotBadge'>$remaining / " . $row['MaxSlots'] . " left</span>";
                                    } else {
                                        $slotsDisplay = "<span class='badge bg-secondary'>Exclusive</span>";
                                    }
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-secondary">#<?php echo $refID; ?></td>
                                        <td class="fw-bold"><?php echo $row['JoinerName']; ?></td>
                                        <td><?php echo $row['TOUR_NAME']; ?></td>
                                        <td><?php echo $travelDate; ?></td>
                                        <td><?php echo $row['Pax']; ?></td>

                                        <td><?php echo $slotsDisplay; ?></td>

                                        <td><?php echo $row['ContactNumber']; ?></td>
                                        <td>₱<?php echo $price; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $row['Status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-light border" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                } // end while
                            } else {
                                echo "<tr><td colspan='10' class='text-center py-4'>No bookings found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>

</html>