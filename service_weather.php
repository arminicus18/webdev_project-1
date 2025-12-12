<?php
// service_weather.php
header('Content-Type: application/json');

// --- CONFIGURATION ---
// PASTE YOUR WEATHERAPI KEY HERE
$apiKey = 'cb6f96b70391484193423431251212'; 

// 1. Get Location from URL
$location = isset($_GET['loc']) ? $_GET['loc'] : 'Manila';

// 2. Build the API URL
// "days=3" asks for a 3-day forecast
$url = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q=" . urlencode($location) . "&days=3&aqi=no&alerts=no";

// 3. Fetch Data
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

// 4. Output
echo $response;
?>