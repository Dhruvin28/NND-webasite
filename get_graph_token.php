<?php
/**
 * Generate refresh token for Microsoft Graph API (v2.0 endpoint)
 */

require 'vendor/autoload.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

session_start();

$tenantId = '6184b915-9411-4558-9053-e14bb5b2876a';
$clientId = '186bd228-98fc-43b1-924a-c96f4fe34b7b';
$clientSecret = 'ovD8Q~Rf05boVWtBgoaRP.q-VYzvQvQpZxePuc9Y';
$redirectUri = 'http://localhost/NND/get_graph_token.php';

// IMPORTANT: Configure provider for v2.0 endpoint with Microsoft Graph
$provider = new Azure([
    'clientId'                => $clientId,
    'clientSecret'            => $clientSecret,
    'redirectUri'             => $redirectUri,
    'tenantId'                => $tenantId,
    'defaultEndPointVersion'  => Azure::ENDPOINT_VERSION_2_0
]);

// Force the provider to use tenant-specific endpoint
$provider->tenant = $tenantId;

$options = [
    'scope' => [
        'https://graph.microsoft.com/Mail.Send',
        'https://graph.microsoft.com/User.Read',
        'offline_access'
    ]
];

if (!isset($_GET['code'])) {
    // Step 1: Get authorization URL
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();

    echo "<!DOCTYPE html>";
    echo "<html><head><title>Get Microsoft Graph Token</title></head><body>";
    echo "<h1>Generate Refresh Token for Microsoft Graph API</h1>";
    echo "<p><strong>This will request the following permissions:</strong></p>";
    echo "<ul>";
    echo "<li>Mail.Send - Send emails via Graph API</li>";
    echo "<li>User.Read - Read user profile</li>";
    echo "<li>offline_access - Get refresh token</li>";
    echo "</ul>";
    echo "<p>Click the button below to authorize:</p>";
    echo "<a href='$authUrl' style='display:inline-block;padding:15px 30px;background:#0078d4;color:white;text-decoration:none;border-radius:5px;font-size:16px;'>Authorize App with Microsoft Graph</a>";
    echo "</body></html>";
    exit;

} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state - please try again');

} else {
    // Step 2: Exchange code for token
    try {
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        echo "<!DOCTYPE html>";
        echo "<html><head><title>Success!</title></head><body>";
        echo "<h1 style='color:green;'>✅ Success!</h1>";
        echo "<h2>Your Refresh Token (Microsoft Graph API):</h2>";
        echo "<textarea readonly style='width:95%;height:200px;font-family:monospace;padding:10px;'>" . htmlspecialchars($token->getRefreshToken()) . "</textarea>";

        // Verify the token has correct scopes
        $tokenParts = explode('.', $token->getToken());
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            echo "<h3>Token Verification:</h3>";
            echo "<ul>";
            echo "<li><strong>Audience:</strong> " . ($payload['aud'] ?? 'N/A') . "</li>";
            echo "<li><strong>Scopes:</strong> " . ($payload['scp'] ?? 'N/A') . "</li>";

            if (isset($payload['aud']) && $payload['aud'] === 'https://graph.microsoft.com') {
                echo "<li style='color:green;'>✅ Correct audience for Microsoft Graph</li>";
            } else {
                echo "<li style='color:red;'>❌ Wrong audience: " . ($payload['aud'] ?? 'N/A') . "</li>";
            }

            if (isset($payload['scp']) && strpos($payload['scp'], 'Mail.Send') !== false) {
                echo "<li style='color:green;'>✅ Mail.Send permission present</li>";
            } else {
                echo "<li style='color:red;'>❌ Mail.Send permission missing</li>";
            }
            echo "</ul>";
        }

        echo "<h3>Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Copy the refresh token above</li>";
        echo "<li>Open <code>send_email.php</code></li>";
        echo "<li>Replace the refresh token on line 20</li>";
        echo "<li>Run <code>send_email.php</code> to test</li>";
        echo "</ol>";
        echo "</body></html>";

    } catch (Exception $e) {
        echo "<h1 style='color:red;'>❌ Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><a href='get_graph_token.php'>Try again</a></p>";
    }
}
