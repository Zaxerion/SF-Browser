<?php
$baseDir = __DIR__ . '/uploads/';
$path = $_GET['path'] ?? '';
$currentDir = realpath($baseDir . $path);

function safePath($base, $target) {
    return strpos(realpath($target), realpath($base)) === 0;
}

if (!safePath($baseDir, $currentDir)) die("Forbidden");

// Create Folder
if (isset($_POST['new_folder'])) {
    $newFolder = basename($_POST['new_folder']);
    mkdir($currentDir . '/' . $newFolder);
}

// Upload File
if (!empty($_FILES['files']['name'][0])) {
    foreach ($_FILES['files']['name'] as $index => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$index];
        if (is_uploaded_file($tmpName)) {
            move_uploaded_file($tmpName, $currentDir . '/' . basename($name));
        }
    }
}


// Edit File
if (isset($_POST['edit_file']) && isset($_POST['content'])) {
    file_put_contents($currentDir . '/' . basename($_POST['edit_file']), $_POST['content']);
}

// Delete Item
if (isset($_GET['delete'])) {
    $target = $currentDir . '/' . basename($_GET['delete']);
    is_dir($target) ? rmdir($target) : unlink($target);
}

// Rename Item
if (isset($_POST['rename_item']) && isset($_POST['new_name'])) {
    $oldName = basename($_POST['rename_item']);
    $newName = basename($_POST['new_name']);
    $oldPath = $currentDir . '/' . $oldName;
    $newPath = $currentDir . '/' . $newName;
    if (file_exists($oldPath) && !file_exists($newPath)) {
        rename($oldPath, $newPath);
    }
}

$items = scandir($currentDir);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP File Manager</title>
    <style>
        body { font-family: sans-serif; padding: 30px; max-width: 800px; margin: auto; }
        a { color: #0366d6; text-decoration: none; }
        .item { padding: 6px 0; display: flex; justify-content: space-between; }
        .icon { margin-right: 6px; }
        form { margin-bottom: 15px; }
        textarea { width: 100%; }
    </style>
</head>
<body>

<h2>📁 /<?= htmlspecialchars($path) ?></h2>

<hr>

<form method="post">
    Buat Folder: <input type="text" name="new_folder">
    <input type="submit" value="Buat">
</form>

<form method="post" enctype="multipart/form-data">
    Upload File: <input type="file" name="files[]" multiple>
    <input type="submit" value="Upload">
</form>

<hr>

<?php
$showBack = strlen($path) > 0 && $path !== '.' && $path !== '/';
if ($showBack): ?>
    <div class="item">
        <div>
            📁 <a href="?path=<?= urlencode(dirname($path)) ?>">...</a>
        </div>
        <div></div>
    </div>
<?php endif; ?>


<?php foreach ($items as $item): if ($item === '.' || $item === '..') continue;
    $itemPath = $path ? "$path/$item" : $item;
    $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    ?>
    <div class="item">
        <div>
            <?php if (is_dir($currentDir . '/' . $item)): ?>
                📁 <a href="?path=<?= $itemPath ?>"><?= $item ?></a>
            <?php else: ?>
                📄 <a href="file.php?path=<?= urlencode($path) ?>&file=<?= urlencode($item) ?>"><?= $item ?></a>
            <?php endif; ?>
        </div>
        <div>
            <a href="?path=<?= $path ?>&delete=<?= $item ?>" onclick="return confirm('Delete?')">❌</a>
            | <a href="?path=<?= $path ?>&rename=<?= $item ?>">✏️</a>
            <?php if (is_dir($currentDir . '/' . $item)): ?>
                | <a href="raw.php?path=<?= $itemPath ?>" target="_blank">🔗</a>
            <?php elseif (!is_dir($currentDir . '/' . $item)): ?>
                | <a href="raw.php?path=<?= $itemPath ?>" target="_blank">🔗</a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
