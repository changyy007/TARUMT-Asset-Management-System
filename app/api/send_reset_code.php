<?php
session_start();
header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

$email = isset($input['email']) ? trim($input['email']) : '';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid Gmail / Email address.']);
    exit();
}

// Generate secure random 6-digit verification code
$code = strval(random_int(100000, 999999));

// Store in session with 10-minute expiry
$_SESSION['reset_email'] = strtolower($email);
$_SESSION['reset_code'] = $code;
$_SESSION['reset_code_expiry'] = time() + 600; // 10 minutes

// Send email using PHP mail()
$subject = "TARUMT Asset System - Password Reset Code: {$code}";
$message = "Hello,\n\nYour 6-digit verification code for resetting your password is:\n\n{$code}\n\nThis code will expire in 10 minutes.\nIf you did not request this, please ignore this email.\n\nTARUMT Campus Asset Management Team";
$headers = "From: no-reply@tarumt.edu.my\r\nReply-To: no-reply@tarumt.edu.my\r\nX-Mailer: PHP/" . phpversion();

// Attempt mail send (may succeed or queue depending on server mail configuration)
@mail($email, $subject, $message, $headers);

echo json_encode([
    'success' => true,
    'message' => 'Verification code sent to your email address.',
    // Return code in development response so local testing without active mail server is also 100% testable
    'dev_code' => $code 
]);

