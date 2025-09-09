<?php
// File: send_email.php

// Only process POST requests.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form fields and remove whitespace.
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $phone = strip_tags(trim($_POST["phone"]));
    $message = trim($_POST["message"]);

    // Set the response header to JSON
    header('Content-Type: application/json');

    // Check that data was sent.
    if ( empty($name) OR empty($subject) OR empty($phone) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Set a 400 (bad request) response code and exit.
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please complete all required fields and provide a valid email."]);
        exit;
    }

    // Set the recipient email address.
    $recipient = "mashruwaladhruvin@gmail.com";

    // Build the email subject.
    $email_subject = "New Contact Form Submission: $subject";

    // Build the email content.
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Phone: $phone\n\n";
    $email_content .= "Message:\n$message\n";

    // Build the email headers.
    // This allows you to hit "Reply" in your email client and reply directly to the user.
    $email_headers = "From: $name <$email>\r\n";
    $email_headers .= "Reply-To: $email\r\n";
    $email_headers .= "X-Mailer: PHP/" . phpversion();

    // Send the email.
    if (mail($recipient, $email_subject, $email_content, $email_headers)) {
        // Set a 200 (okay) response code.
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Thank you! Your message has been sent."]);
    } else {
        // Set a 500 (internal server error) response code.
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Oops! Something went wrong and we couldn't send your message."]);
    }

} else {
    // Not a POST request, set a 403 (forbidden) response code.
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "There was a problem with your submission, please try again."]);
}
?>