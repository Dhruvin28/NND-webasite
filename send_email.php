<?php
/**
 * Send Email using Microsoft Graph API
 * Handles contact form submissions from contact.html
 */

require 'vendor/autoload.php';

use TheNetworg\OAuth2\Client\Provider\Azure;

// Set JSON response header
header('Content-Type: application/json');

// ----------------- CONFIGURATION -----------------
$tenantId     = "6184b915-9411-4558-9053-e14bb5b2876a";
$clientId     = "186bd228-98fc-43b1-924a-c96f4fe34b7b";
$clientSecret = "ovD8Q~Rf05boVWtBgoaRP.q-VYzvQvQpZxePuc9Y";
$userEmail    = "info@nndtechlabs.com";  // Email account that will send the email
$recipientEmail = "info@nndtechlabs.com"; // Where form submissions are sent

// Refresh Token (with Mail.Send permission)
$refreshToken = '1.Ab4AFbmEYRGUWEWQU-FLtbKHaijSaxj8mLFDkkrJb0_jS3t7Abe-AA.BQABAwEAAAADAOz_BQD0_40G0S6Gn_1LxVWDDWigWZDFqQBrcjwGTOexd1NVxbgintiyTJHCVqMTZZnIFtKZo_-koWSOGXsqGk--0uhgvE3BSYOfTGtqZq7WjEYhTIsQDP519WICdfdxSJ2d4Q_Ge9tJFeZzWdCKhOxkeAZMBmyDNKVPCBLxa7ZhBBsO7eVXEKt1QfzMRlA5Q1zPXlYtzpFnUGcHxNMGbGCyRReTFPj7jiDExjYrUcoNrj-83J4IfW_6SJOn2n98tM_VaR5ZxUWTeTLnioYNFFchUUgqlpSDCVDMBkgG2soWUXBHR7PBEKZVsrElHJPvYDykKW_wFolZ_BI1ynCtaEaZ-PkpmpUx0Sf4-naJ-t03df6BwrWX8k6HVQE-54Rhoi8sqjs5umkdSjxkp9spSERR-5zUL59DFbaNS2q9fXBLC81o9KbzZfHdIhMH5ZUUigmUtLDEB8VbLuH9pJe8TYhEWAzwrRQdvicYbhf7rUB-NwLEADsdy3DFBHHe4mffEeQkvuaMtNxfFkHJsSAQm2_3ZOFRVq5cOSg5qoxYrjfFR0gdZyyj4jimIbiCI9cTkOBkZ0CBOb7_q81LPvElmnZDP4iticmka4QElEfxNQnwU8wVfG9ppGTN2sOHlZXaWwLDEiKbbCID7mPTfV5URG5XCtFhShucgOTQG-5BO_sOz4CC5Hy6YY6Ew3NVEU5JQwJCazNEAGJVPQPWqp1y5mTlycPsyDTX4c-FUHUXH6CDwmk4szKu_xKfURCPw5xPUaQSJrWnhH1k9hxeHaDZovo59jNikxQyeaG7mcK9rwaYZoN_kOsGrVV5SbU8opLfao9L5vrrXOobAKPEIfXM0C6EzWt-mvWmMIb5Gm-XBFAqI-K75jy2qq_hayWrnLnllIIUo5lTIQbNc4eJJ_WuHM9YLyewZzcq7vBaPnF1blGozPXw5AqEIr-6Cziy72903Nv_rEYDqdkCXLVL_uxmx5HsLoFzgnFa2uwG7B8ytM4nRmV-8eq5QRaci8nrirYTWLTDqb5Ni8DdhsiMWhKOAy6MKB20ES0LxrBKcIdFxiOci2MTsLf6H74Nsu9WXtdWSdcXOYYTpUuBmybuYYRTKuwTCyyVIL2wO6n_E9RuaWTbMAdFkBgA0198xZhLB9hq6t3liTdBZfQEVcxN9UaqI3s30y3NkczaYE_Kc_nkWP5ri1azsAtCsjtlB9P8cClkw71HwkwBsAS3Km5HUKbbcnu3gATzUvhrgXS0_X77C4IS3Ooqkx37OagjW0-gEWi3wm--jSKMOxpYf_6wuP9fCd_28spYZw3ho4l2bqp9MS4ZHkGXwMrhXVD0O4YM9tMjvHABxObVBdr9JVHiqOEsodvfYHVpVTBUVT3r8X5Cp0zEiQ6XYrfIlhxcaCJ1POs7AiJP3HBshv1kkowQgWmPLa4fmVH3N_MctfPq5SP--sB-h3OZEgr07wgjOQ';

// ----------------- HANDLE FORM SUBMISSION -----------------
try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if (empty($name)) {
        throw new Exception('Name is required');
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Valid email is required');
    }
    if (empty($subject)) {
        throw new Exception('Subject is required');
    }
    if (empty($message)) {
        throw new Exception('Message is required');
    }

    // Get Access Token
    $provider = new Azure([
        'clientId'                => $clientId,
        'clientSecret'            => $clientSecret,
        'tenantId'                => $tenantId,
        'defaultEndPointVersion'  => Azure::ENDPOINT_VERSION_2_0
    ]);

    $provider->tenant = $tenantId;

    $accessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $refreshToken
    ]);

    // Prepare Email Message
    $emailSubject = "Contact Form: " . htmlspecialchars($subject);

    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #0078d4 0%, #1e88e5 100%); color: white; padding: 20px; border-radius: 5px 5px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .field { margin-bottom: 15px; }
            .field-label { font-weight: bold; color: #0078d4; }
            .field-value { margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #0078d4; }
            .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2 style='margin: 0;'>New Contact Form Submission</h2>
                <p style='margin: 5px 0 0 0; opacity: 0.9;'>From NND Techlabs Website</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='field-label'>Name:</div>
                    <div class='field-value'>" . htmlspecialchars($name) . "</div>
                </div>
                <div class='field'>
                    <div class='field-label'>Email:</div>
                    <div class='field-value'>" . htmlspecialchars($email) . "</div>
                </div>
                <div class='field'>
                    <div class='field-label'>Phone:</div>
                    <div class='field-value'>" . htmlspecialchars($phone) . "</div>
                </div>
                <div class='field'>
                    <div class='field-label'>Subject:</div>
                    <div class='field-value'>" . htmlspecialchars($subject) . "</div>
                </div>
                <div class='field'>
                    <div class='field-label'>Message:</div>
                    <div class='field-value'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p>This email was sent from the contact form on nndtechlabs.com</p>
                <p>Submitted on: " . date('F j, Y, g:i a') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $emailData = [
        'message' => [
            'subject' => $emailSubject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $emailBody
            ],
            'toRecipients' => [
                [
                    'emailAddress' => [
                        'address' => $recipientEmail
                    ]
                ]
            ],
            'replyTo' => [
                [
                    'emailAddress' => [
                        'address' => $email,
                        'name' => $name
                    ]
                ]
            ]
        ],
        'saveToSentItems' => true
    ];

    // Send Email via Graph API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me/sendMail");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken->getToken(),
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Network error: $curlError");
    }

    if ($httpCode === 202) {
        // Success
        echo json_encode([
            'status' => 'success',
            'message' => '✅ Thank you! Your message has been sent successfully. We will get back to you soon.'
        ]);
    } else {
        // Error from Graph API
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
        throw new Exception("Failed to send email: $errorMessage (HTTP $httpCode)");
    }

} catch (Exception $e) {
    // Return error as JSON
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
