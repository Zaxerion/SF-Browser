<?php
$baseDir = __DIR__ . '/uploads/';
$path = isset($_GET['path']) ? $_GET['path'] : '';
$targetPath = realpath($baseDir . $path);

if (strpos($targetPath, realpath($baseDir)) !== 0) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

if (is_dir($targetPath)) {
    header('Content-Type: text/plain');
    $items = scandir($targetPath);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        echo $item . "\n";
    }
} elseif (is_file($targetPath)) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $targetPath);
    finfo_close($finfo);
    header('Content-Type: ' . $mimeType);
    readfile($targetPath);
} else {
    http_response_code(404);
    echo "Not Found";
}
?>
