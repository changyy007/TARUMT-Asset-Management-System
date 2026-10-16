<?php
header('Content-Type: application/json');

$envFile = __DIR__ . '/../../.env';
$values = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$name, $value] = explode('=', $line, 2) + [null, null];
        if ($name === null || $value === null) continue;
        $values[trim($name)] = trim(trim($value), "\"'");
    }
}

$config = [
    'apiKey' => $values['FIREBASE_API_KEY'] ?? '',
    'authDomain' => $values['FIREBASE_AUTH_DOMAIN'] ?? '',
    'projectId' => $values['FIREBASE_PROJECT_ID'] ?? '',
    'storageBucket' => $values['FIREBASE_STORAGE_BUCKET'] ?? '',
    'messagingSenderId' => $values['FIREBASE_MESSAGING_SENDER_ID'] ?? '',
    'appId' => $values['FIREBASE_APP_ID'] ?? '',
    'measurementId' => $values['FIREBASE_MEASUREMENT_ID'] ?? ''
];

if ($config['apiKey'] === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Firebase configuration is missing']);
    exit();
}

echo json_encode($config);