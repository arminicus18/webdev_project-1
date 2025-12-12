<?php
// service_gallery.php
header('Content-Type: application/json');

// --- CONFIGURATION ---
$accessKey = 'aSg6O8gSLrT0wmjB9y7uT3FueML1zhwxvDHoDUZ4dzc'; 
$cacheFolder = 'cache/'; // The folder you just created
$cacheDuration = 86400;  // 24 Hours (in seconds)

// 1. Get Parameters from JS
$name = isset($_GET['name']) ? $_GET['name'] : '';
$location = isset($_GET['loc']) ? $_GET['loc'] : '';

// 2. CHECK CACHE FIRST ⚡
// We create a unique filename ID based on the specific tour & location
$cacheKey = md5($name . $location);
$cacheFile = $cacheFolder . $cacheKey . '.json';

// If a saved file exists AND it is less than 24 hours old...
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheDuration)) {
    // ... SERVE THE SAVED FILE! (Stops here, uses 0 API credits)
    echo file_get_contents($cacheFile);
    exit;
}

// ---------------------------------------------------------
// IF WE ARE HERE, NO CACHE WAS FOUND. WE MUST CALL THE API.
// ---------------------------------------------------------

// Helper Function
function fetchUnsplash($query, $key, $page = 1) {
    // Ask for 12 landscape photos
    $encodedQuery = urlencode($query);
    $url = "https://api.unsplash.com/search/photos?page={$page}&query={$encodedQuery}&orientation=landscape&per_page=12&client_id={$key}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Only return data if request was successful (Status 200)
    if ($httpCode !== 200) {
        return false; 
    }
    return $result;
}

// === PRIORITY 1: LOCATION (e.g., "Nasugbu Batangas nature") ===
$searchQuery = $location . "Philippines";
$response = fetchUnsplash($searchQuery, $accessKey);
$data = json_decode($response, true);

// Check if Priority 1 failed
if (!$response || empty($data['results']) || count($data['results']) < 2) {

    // === PRIORITY 2: TOUR NAME (e.g., "Mt. Batulao") ===
    $fallbackQuery = $name . "Philippines";
    $response = fetchUnsplash($fallbackQuery, $accessKey);
    $data = json_decode($response, true);

    // Check if Priority 2 failed
    if (!$response || empty($data['results']) || count($data['results']) < 2) {

        // === PRIORITY 3: HAPPY HIKING (Generic Fallback) ===
        // Generate a random page number (1-20) based on tour name
        $seed = crc32($name); 
        $uniquePage = ($seed % 20) + 1; 
        
        $finalFallback = "people happily hiking adventure group";
        $response = fetchUnsplash($finalFallback, $accessKey, $uniquePage);
    }
}

// 3. SAVE TO CACHE 💾
// Only save if we actually got a valid response
if ($response) {
    // Create cache folder if it doesn't exist yet
    if (!is_dir($cacheFolder)) {
        mkdir($cacheFolder, 0777, true);
    }
    // Write the JSON text to the file
    file_put_contents($cacheFile, $response);
    
    // Output the result to your website
    echo $response;
} else {
    // If API failed (Rate Limit), send empty result to prevent JS error
    echo json_encode(['results' => []]); 
}
?>