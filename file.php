<?php
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: /login.php");
    exit;
}

$baseDir = __DIR__ . '/uploads/';

$fullPath = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
$parts = explode('/', $fullPath);

$file = array_pop($parts);
$path = implode('/', $parts);
$currentDir = realpath($baseDir . '/' . $path);
$filePath = $currentDir . '/' . basename($file);

function safePath($base, $target) {
    $realBase = realpath($base);
    $realTarget = realpath($target);
    return $realBase && $realTarget && strpos($realTarget, $realBase) === 0;
}

if (!$currentDir || !$file || !$filePath || !safePath($baseDir, $filePath) || !file_exists($filePath) || is_dir($filePath)) {
    http_response_code(403);
    die("❌ Forbidden or not a file.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    unlink($filePath);
    header('Location: /index.php/' . $path);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_file']) && isset($_POST['content'])) {
    file_put_contents($filePath, $_POST['content']);
    header('Location: /file.php/' . $path . '/' . rawurlencode($file));
    exit;
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
$content = !$isImage ? file_get_contents($filePath) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($file) ?> - File Viewer</title>
    <style>
        body { font-family: sans-serif; padding: 30px; max-width: 800px; margin: auto; }
        a { color: #0366d6; text-decoration: none; }
        .actions { margin-bottom: 20px; text-align: right; }
        textarea { width: 100%; font-family: monospace; }
        pre { background: #f6f8fa; padding: 12px; border-radius: 4px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>

<a href="/index.php/<?= htmlspecialchars($path) ?>">⬅️ Back to Folder</a>
<h2>📄 <?= htmlspecialchars($file) ?></h2>

<div class="actions">
    <a href="/raw.php/<?= $path ? $path . '/' : '' ?><?= rawurlencode($file) ?>" target="_blank">Raw</a> |
    <a href="?edit=1">Edit</a> |
    <a href="#" onclick="if(confirm('Delete this file?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        const input = document.createElement('input');
        input.name = 'delete_file';
        input.value = '1';
        input.type = 'hidden';
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    } return false;">Delete</a>
</div>

<?php if ($isImage): ?>
    <img src="/raw.php/<?= $path ? $path . '/' : '' ?><?= rawurlencode($file) ?>" style="max-width:100%;border:1px solid #ccc;">
<?php elseif (isset($_GET['edit'])): ?>
    <form method="post">
        <textarea name="content" rows="18"><?= htmlspecialchars($content) ?></textarea><br>
        <input type="hidden" name="edit_file" value="1">
        <input type="submit" value="💾 Save">
    </form>
<?php else: ?>
    <pre><?= htmlspecialchars($content) ?></pre>
<?php endif; ?>

</body>
</html>
