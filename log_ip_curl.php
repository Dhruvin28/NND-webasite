<?php

// Set the content type header to JSON
header('Content-Type: application/json');

// This will hold our response data
$response = [];

// --- Configuration ---
$apiUrl = "https://ipapi.co/json/";
$logFile = "ip_log.txt";
// --------------------

function logIpInfo($url, $file) {
    // ... (The cURL logic from the previous answer remains exactly the same) ...
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'My-IP-Logger-Script/1.0');
    $jsonData = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_message = 'cURL Error: ' . curl_error($ch);
        logMessage($file, $error_message);
        curl_close($ch);
        // Return an error status and message
        return ['status' => 'error', 'message' => 'Failed to connect to the API. Check log for details.'];
    }
    curl_close($ch);

    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage($file, "Error decoding JSON response.");
        return ['status' => 'error', 'message' => 'Received an invalid response from the API.'];
    }
    $ip      = $data['ip'] ?? 'N/A';
    $city    = $data['city'] ?? 'N/A';
    $region  = $data['region'] ?? 'N/A';
    $country = $data['country_name'] ?? 'N/A';
    $org     = $data['org'] ?? 'N/A';
    $logEntry = "IP: $ip, Location: $city, $region, $country - ISP: $org";
    
    logMessage($file, $logEntry);
    
    // Return a success status and the data that was logged
    return ['status' => 'success', 'message' => "success"];
}

function logMessage($file, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
}


// --- Execute the script and prepare the JSON response ---
$result = logIpInfo($apiUrl, $logFile);

// Encode the result array into a JSON string and echo it
echo json_encode($result);