<?php
header('Content-Type: application/json');

// Read GEMINI_API_KEY from .env
$apiKey = "";
$envFile = __DIR__ . '/../../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env';
}
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2) + [NULL, NULL];
        if (trim($name) === 'GEMINI_API_KEY') {
            $apiKey = trim(trim($value), "\"'");
            break;
        }
    }
}
if (!$apiKey && getenv('GEMINI_API_KEY')) {
    $apiKey = getenv('GEMINI_API_KEY');
}

// Read JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

$description = isset($input['description']) ? trim($input['description']) : '';
$imageBase64 = isset($input['image']) ? $input['image'] : '';
$mimeType = isset($input['mimeType']) ? $input['mimeType'] : 'image/jpeg';

// Heuristic fallback classifier in case API quota is exhausted or API unreachable
function heuristicClassify($text) {
    $t = strtolower($text);
    $criticalWords = ['wire', 'spark', 'smoke', 'fire', 'shock', 'leak near', 'gas', 'danger', 'hazard', 'burn', 'explode', 'explosion', 'exposed'];
    foreach ($criticalWords as $w) {
        if (strpos($t, $w) !== false) return 'CRITICAL';
    }
    $moderateWords = ['aircond', 'air conditioner', 'ac', 'projector', 'blackout', 'trip', 'power trip', 'lift', 'elevator', 'screen', 'audio', 'mic', 'light blackout', 'no power'];
    foreach ($moderateWords as $w) {
        if (strpos($t, $w) !== false) return 'MODERATE';
    }
    return 'MINOR';
}

if (!$apiKey) {
    echo json_encode(['success' => true, 'priority' => heuristicClassify($description), 'source' => 'heuristic_no_key']);
    exit();
}

// Build Gemini API payload
$prompt = "You are an automated campus maintenance classifier. 
Analyze the report photo and description to categorize the defect into one of three strict priority levels:
- CRITICAL: immediate safety or severe physical hazards (e.g. exposed live wires, sparking sockets, active severe leaks near electrical devices).
- MODERATE: faults that directly halt academic or operational activities (e.g. non-functional air conditioner in an active room, broken projector).
- MINOR: non-dangerous issues with easy workarounds (e.g. broken chair, loose desk screw, broken furniture).

Strictly output JSON with the single field 'priority' set to CRITICAL, MODERATE, or MINOR.";

$parts = [];
if ($imageBase64) {
    // Strip data url prefix if present
    if (strpos($imageBase64, ',') !== false) {
        $imageBase64 = explode(',', $imageBase64)[1];
    }
    $parts[] = [
        'inline_data' => [
            'mime_type' => $mimeType,
            'data' => $imageBase64
        ]
    ];
}
$parts[] = [
    'text' => "Defect Description: " . $description
];

$payload = [
    'system_instruction' => [
        'parts' => [
            ['text' => $prompt]
        ]
    ],
    'contents' => [
        [
            'parts' => $parts
        ]
    ],
    'generationConfig' => [
        'response_mime_type' => 'application/json',
        'response_schema' => [
            'type' => 'OBJECT',
            'properties' => [
                'priority' => [
                    'type' => 'STRING',
                    'enum' => ['CRITICAL', 'MODERATE', 'MINOR'],
                    'description' => 'Defect severity level'
                ]
            ],
            'required' => ['priority']
        ]
    ]
];

// Test models in order of priority: gemini-3.8-flash, gemini-3.6-flash, gemini-2.5-flash
$modelsToTry = ['gemini-3.8-flash', 'gemini-3.6-flash', 'gemini-2.5-flash'];
$classifiedPriority = null;

foreach ($modelsToTry as $modelName) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $json = json_decode($response, true);
        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            $parsedText = json_decode($json['candidates'][0]['content']['parts'][0]['text'], true);
            if (isset($parsedText['priority']) && in_array($parsedText['priority'], ['CRITICAL', 'MODERATE', 'MINOR'])) {
                $classifiedPriority = $parsedText['priority'];
                break;
            }
        }
    }
}

if (!$classifiedPriority) {
    // Graceful fallback to heuristic classification
    $classifiedPriority = heuristicClassify($description);
}

echo json_encode([
    'success' => true,
    'priority' => $classifiedPriority
]);

