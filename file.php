<?php
$baseDir = __DIR__ . '/uploads/';
$path = $_GET['path'] ?? '';
$file = $_GET['file'] ?? '';
$currentDir = realpath($baseDir . $path);
$filePath = $currentDir . '/' . basename($file);

function safePath($base, $target) {
    return strpos(realpath($target), realpath($base)) === 0;
}

if (!safePath($baseDir, $filePath) || !file_exists($filePath) || is_dir($filePath)) die("Forbidden or not a file");

// Delete file
if (isset($_POST['delete_file'])) {
    unlink($filePath);
    header('Location: index.php?path=' . urlencode($path));
    exit;
}

// Edit file
if (isset($_POST['edit_file']) && isset($_POST['content'])) {
    file_put_contents($filePath, $_POST['content']);
    header('Location: file.php?path=' . urlencode($path) . '&file=' . urlencode($file));
    exit;
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
$content = '';
if (!$isImage) {
    $content = file_get_contents($filePath);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($file) ?> - File Preview</title>
    <style>
        body { font-family: sans-serif; padding: 30px; max-width: 800px; margin: auto; }
        a { color: #0366d6; text-decoration: none; }
        .actions { margin-bottom: 20px; text-align: right; }
        textarea { width: 100%; }
        pre { background: #f6f8fa; padding: 12px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<a href="index.php?path=<?= urlencode($path) ?>">⬅️ Back to Folder</a>
<h2>📄 <?= htmlspecialchars($file) ?></h2>

<div class="actions">
    <a href="raw.php?path=<?= ($path ? "$path/" : "") . urlencode($file) ?>" target="_blank">Raw</a> |
    <a href="file.php?path=<?= urlencode($path) ?>&file=<?= urlencode($file) ?>&edit=1">Edit</a> |
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
    <img src="raw.php?path=<?= ($path ? "$path/" : "") . urlencode($file) ?>" style="max-width:100%;border:1px solid #ccc;">
<?php elseif (isset($_GET['edit'])): ?>
    <form method="post">
        <textarea name="content" rows="18"><?= htmlspecialchars($content) ?></textarea><br>
        <input type="hidden" name="edit_file" value="1">
        <input type="submit" value="Save">
    </form>
<?php else: ?>
    <pre><?= htmlspecialchars($content) ?></pre>
<?php endif; ?>
</body>
</html>
