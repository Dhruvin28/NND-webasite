<?php

// Set the content type header to JSON for the response
header('Content-Type: application/json');

// --- Configuration ---
$logFile = "ip_log.txt"; // Use a different log file to avoid confusion
// --------------------

// Ensure this script is called via a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use POST.']);
    exit();
}

// Get the raw JSON payload from the request body
$jsonPayload = file_get_contents('php://input');
$data = json_decode($jsonPayload, true);

// --- Validation Step (Crucial!) ---
// Check if decoding was successful and if the essential 'ip' key exists
if (json_last_error() !== JSON_ERROR_NONE || !isset($data['ip'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing JSON data.']);
    exit();
}

// Sanitize and extract data. The null coalescing operator '??' is great here.
$ip      = $data['ip'] ?? 'N/A';
$city    = $data['city'] ?? 'N/A';
$region  = $data['region'] ?? 'N/A';
$country = $data['country_name'] ?? 'N/A';
$org     = $data['org'] ?? 'N/A';

// Format the log entry from the data we RECEIVED
$logEntry = "IP: $ip, Location: $city, $region, $country - ISP: $org";

// Log the message
logMessage($logFile, $logEntry);

// Send a success response back to the JavaScript
echo json_encode(['status' => 'success', 'message' => 'Data logged successfully.']);


/**
 * Appends a formatted message to a log file.
 * @param string $file The path to the log file.
 * @param string $message The message to log.
 */
function logMessage($file, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message" . PHP_EOL;
    // Use FILE_APPEND to add to the file and LOCK_EX to prevent concurrent writes
    file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
}