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

// 2. FETCH TOURS FOR "ADD" DROPDOWN
$sql_tours = "SELECT TOUR_ID, TOUR_NAME FROM TOURS_7 ORDER BY TOUR_NAME ASC";
$stmt_tours = sqlsrv_query($conn, $sql_tours);

// 3. FETCH EXISTING EVENTS FOR "REMOVE" DROPDOWN [NEW]
// We join with TOURS_7 to show the name, and format the date for readability
$sql_events = "SELECT 
                e.EventID, 
                t.TOUR_NAME, 
                e.EventDate 
              FROM EVENTS_1 e
              JOIN TOURS_7 t ON e.TourID = t.TOUR_ID
              ORDER BY e.EventDate ASC";
$stmt_events = sqlsrv_query($conn, $sql_events);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <title>Manage Events | MountPinoy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="mountpinoy.css">
</head>

<body style="background-color: #121212; color: #f0f0f0;">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="d-flex align-items-center mb-4">
                    <a href="admin.php" class="btn btn-outline-light me-3">&larr; Back</a>
                    <h2 class="text-warning mb-0">Manage Schedules</h2>
                </div>

                <div class="card bg-dark border-secondary shadow-lg mb-5">
                    <div class="card-header border-secondary bg-transparent text-white fw-bold">
                        <i class="bi bi-calendar-plus me-2 text-warning"></i> Schedule New Hike
                    </div>
                    <div class="card-body p-4">
                        <form action="addevent_process.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label class="form-label text-white">Select Tour</label>
                                <select class="form-select bg-secondary text-white border-secondary" name="tour_id"
                                    required>
                                    <option value="" selected disabled>Choose a mountain...</option>
                                    <?php while ($row = sqlsrv_fetch_array($stmt_tours, SQLSRV_FETCH_ASSOC)): ?>
                                        <option value="<?php echo $row['TOUR_ID']; ?>">
                                            <?php echo $row['TOUR_NAME']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white">Event Date</label>
                                <input type="date" class="form-control bg-secondary text-white border-secondary"
                                    name="event_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white">Max Slots (Pax)</label>
                                <input type="number" class="form-control bg-secondary text-white border-secondary"
                                    name="max_slots" value="12" min="1" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-white">Initial Status</label>
                                <select class="form-select bg-secondary text-white border-secondary" name="status">
                                    <option value="Open">Open for Booking</option>
                                    <option value="Closed">Closed</option>
                                    <option value="Full">Full</option>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning fw-bold py-2">
                                    Create Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card bg-dark border-danger shadow-lg">
                    <div class="card-header border-danger bg-transparent text-danger fw-bold">
                        <i class="bi bi-calendar-x me-2"></i> Remove Event
                    </div>
                    <div class="card-body p-4">
                        <form action="addevent_process.php" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this event? This cannot be undone.');">
                            <input type="hidden" name="action" value="delete">
                            <div class="mb-4">
                                <label class="form-label text-white">Select Event to Remove</label>
                                <select class="form-select bg-secondary text-white border-secondary" name="event_id"
                                    required>
                                    <option value="" selected disabled>Choose an event...</option>
                                    <?php
                                    if ($stmt_events) {
                                        while ($evt = sqlsrv_fetch_array($stmt_events, SQLSRV_FETCH_ASSOC)) {
                                            $dateStr = $evt['EventDate']->format('M d, Y');
                                            echo "<option value='{$evt['EventID']}'>ID: {$evt['EventID']} - {$evt['TOUR_NAME']} ({$dateStr})</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-danger fw-bold py-2">
                                    <i class="bi bi-trash me-2"></i> Delete Event
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>