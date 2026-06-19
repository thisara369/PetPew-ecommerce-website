<?php
// send.php

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo "Method not allowed.";
    exit;
}

// Simple helper function to sanitize inputs
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Get and sanitize form inputs
$name = isset($_POST['name']) ? clean_input($_POST['name']) : '';
$email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
$message = isset($_POST['message']) ? clean_input($_POST['message']) : '';

// Basic validation
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo "Please fill in all the fields.";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Invalid email address.";
    exit;
}

// Recipient email
$to = 'mikeshdilushika80@gmail.com';

// Email subject
$subject = "New Contact Form Message from $name";

// Email body
$body = "You have received a new message from your website contact form.\n\n";
$body .= "Name: $name\n";
$body .= "Email: $email\n\n";
$body .= "Message:\n$message\n";

// Email headers
$headers = "From: $name <$email>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

// Send email
if (mail($to, $subject, $body, $headers)) {
    // Success message shown to user
    echo "Thank you, your message has been sent successfully.";
} else {
    http_response_code(500);
    echo "Sorry, there was a problem sending your message. Please try again later.";
}
