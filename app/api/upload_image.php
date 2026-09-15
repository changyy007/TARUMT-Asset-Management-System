<?php
header('Content-Type: application/json');

// Check if file uploaded
if (!isset($_FILES['image']) && !isset($_POST['image_base64'])) {
    echo json_encode(['success' => false, 'message' => 'No image received.']);
    exit();
}

$uploadDir = __DIR__ . '/../picture/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Also sync with root picture/uploads if exists
$rootUploadDir = __DIR__ . '/../../picture/uploads/';

$filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
$targetFile = $uploadDir . $filename;

if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        if (file_exists($rootUploadDir)) {
            @copy($targetFile, $rootUploadDir . $filename);
        }
        echo json_encode([
            'success' => true,
            'url' => 'picture/uploads/' . $filename
        ]);
        exit();
    }
} elseif (isset($_POST['image_base64'])) {
    $data = $_POST['image_base64'];
    if (strpos($data, ',') !== false) {
        $data = explode(',', $data)[1];
    }
    $decoded = base64_decode($data);
    if ($decoded && file_put_contents($targetFile, $decoded)) {
        if (file_exists($rootUploadDir)) {
            @copy($targetFile, $rootUploadDir . $filename);
        }
        echo json_encode([
            'success' => true,
            'url' => 'picture/uploads/' . $filename
        ]);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
