<?php
session_start();

// --- 1. CONFIGURATION ---
$client_id = '894735970360-ffmjspg7espidrlnv8addt1r1d7tiuam.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://localhost/final_project/google_callback.php';

// --- DATABASE CONNECTION (SQL SERVER) ---
$dbHost = "ARMINICUS-18\SQLEXPRESS"; // Or your PC name (e.g., ARMINICUS-18)
$dbName = "fp_catalog-2"; // The database where USERS_1 is

try {
    // 1. We use NULL for user and password to trust your Windows login.
    // 2. We add "TrustServerCertificate=true" to prevent common SSL errors on localhost.
    $conn = new PDO(
        "sqlsrv:Server=$dbHost;Database=$dbName;TrustServerCertificate=true", 
        null, 
        null
    );
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // This will print the exact error if it fails
    die("Database Connection Failed: " . $e->getMessage());
}

// --- 2. GET THE AUTH CODE ---
if (!isset($_GET['code'])) {
    header('Location: login.html');
    exit();
}

// --- 3. EXCHANGE CODE FOR TOKEN ---
$token_url = "https://oauth2.googleapis.com/token";
$data = [
    'code' => $_GET['code'],
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);
if (!isset($token_data['access_token'])) {
    die("Error fetching token");
}
$access_token = $token_data['access_token'];

// --- 4. GET USER PROFILE FROM GOOGLE ---
$user_info_url = "https://www.googleapis.com/oauth2/v2/userinfo";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_info_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_response = curl_exec($ch);
curl_close($ch);

$google_user = json_decode($user_response, true);

// Extract simplified variables
$g_id = $google_user['id'];
$g_email = $google_user['email'];
$g_name = $google_user['name'];
$g_picture = $google_user['picture'];

// --- 5. DATABASE LOGIC (CHECK or CREATE) ---

// Check if email already exists
$sql = "SELECT * FROM USERS_1 WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$g_email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // === EXISTING USER ===
    // Update their google_id just in case they signed up manually before
    $updateSql = "UPDATE USERS_1 SET google_id = ?, profile_picture = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute([$g_id, $g_picture, $g_email]);

    // Set Session Variables from Database
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
} else {
    // === NEW USER ===
    // Insert them into USERS_1
    $insertSql = "INSERT INTO USERS_1 (google_id, full_name, email, profile_picture, role, created_at) VALUES (?, ?, ?, ?, ?, GETDATE())";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->execute([$g_id, $g_name, $g_email, $g_picture, 'User']);

    // Get the ID of the user we just created
    $_SESSION['user_id'] = $conn->lastInsertId();
    $_SESSION['role'] = 'User';
}

// Set common session variables
$_SESSION['user_name'] = $g_name;
$_SESSION['user_email'] = $g_email;
$_SESSION['user_picture'] = $g_picture;
$_SESSION['logged_in'] = true;

// --- 6. REDIRECT HOME ---
header('Location: index.php'); // Or index.php
exit();
?>