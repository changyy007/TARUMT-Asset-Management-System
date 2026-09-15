<?php
session_start();
header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

$code = isset($input['code']) ? trim($input['code']) : '';
$email = isset($input['email']) ? strtolower(trim($input['email'])) : '';

if (!$code || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing verification code or email.']);
    exit();
}

if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_email'])) {
    echo json_encode(['success' => false, 'message' => 'No active password reset request found. Please request a new code.']);
    exit();
}

if (time() > $_SESSION['reset_code_expiry']) {
    unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_code_expiry']);
    echo json_encode(['success' => false, 'message' => 'Verification code has expired. Please request a new code.']);
    exit();
}

if ($_SESSION['reset_email'] !== $email || $_SESSION['reset_code'] !== $code) {
    echo json_encode(['success' => false, 'message' => 'Invalid verification code. Please check and try again.']);
    exit();
}

// Mark code as verified
$_SESSION['reset_verified'] = true;

echo json_encode([
    'success' => true,
    'message' => 'Verification code confirmed. You may now enter your new password.'
]);

