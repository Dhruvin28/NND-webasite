<?php
// File: send_email.php

// Include PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = strip_tags(trim($_POST["name"]));
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $phone   = strip_tags(trim($_POST["phone"]));
    $message = trim($_POST["message"]);
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    header('Content-Type: application/json');

    // Verify reCAPTCHA
    if (empty($recaptchaResponse)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please complete the reCAPTCHA verification."]);
        exit;
    }

    // Verify reCAPTCHA with Google
    $recaptchaSecret = '6LcFRwgsAAAAAAa-ycpzH6n3TJl6fovtbtYzddvJ';
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaData = [
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($recaptchaData)
        ]
    ];

    $context  = stream_context_create($options);
    $verify = file_get_contents($recaptchaUrl, false, $context);
    $captchaSuccess = json_decode($verify);

    if (!$captchaSuccess->success) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "reCAPTCHA verification failed. Please try again."]);
        exit;
    }

    if ( empty($name) || empty($subject) || empty($phone) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please complete all required fields and provide a valid email."]);
        exit;
    }

    // Recipient
    $recipient = "nndtechlabsindia@gmail.com";

    // Build email content
    $email_subject = "New Contact Form Submission: $subject";
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Phone: $phone\n\n";
    $email_content .= "Message:\n$message\n";

    try {
        $mail = new PHPMailer(true);

        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nndtechlabsindia@gmail.com';     // Your Gmail
        $mail->Password   = 'vyla cvzi rphf cqte';       // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('nndtechlabsindia@gmail.com', 'Website Contact Form');
        $mail->addAddress($recipient);
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $email_subject;
        $mail->Body    = $email_content;

        $mail->send();

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Thank you! Your message has been sent."]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Mailer Error: {$mail->ErrorInfo}"]);
    }

} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
