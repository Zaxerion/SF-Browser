<?php
// REST API: Replace file
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}
$baseDir = __DIR__ . '/uploads/';
$path = $_POST['path'] ?? '';
$currentDir = realpath($baseDir . $path);
function safePath($base, $target) {
    return strpos(realpath($target), realpath($base)) === 0;
}
if (!safePath($baseDir, $currentDir)) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
if (!isset($_FILES['file']) || !isset($_POST['filename'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing file or filename']);
    exit;
}
$filename = basename($_POST['filename']);
$targetFile = $currentDir . '/' . $filename;
if (!file_exists($targetFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Target file does not exist']);
    exit;
}
$tmpName = $_FILES['file']['tmp_name'];
if (!is_uploaded_file($tmpName)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid upload']);
    exit;
}
if (!move_uploaded_file($tmpName, $targetFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to replace file']);
    exit;
}
echo json_encode(['success' => true, 'message' => 'File replaced']);
exit;
