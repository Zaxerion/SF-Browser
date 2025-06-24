<?php
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: /login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /login.php");
    exit;
}

$baseDir = __DIR__ . "/uploads/";
$rawPath = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : "";
$path = $rawPath;
$currentDir = realpath($baseDir . '/' . $path);

function safePath($base, $target) {
    return strpos(realpath($target), realpath($base)) === 0;
}
if (!safePath($baseDir, $currentDir)) {
    die("Forbidden");
}

function joinUrlPath($path = '') {
    return rtrim($_SERVER['SCRIPT_NAME'], '/') . '/' . ltrim($path, '/');
}

if (isset($_POST["new_folder"])) {
    $newFolder = basename($_POST["new_folder"]);
    mkdir($currentDir . "/" . $newFolder);
}

if (!empty($_FILES["files"]["name"][0])) {
    foreach ($_FILES["files"]["name"] as $index => $name) {
        $tmpName = $_FILES["files"]["tmp_name"][$index];
        if (is_uploaded_file($tmpName)) {
            move_uploaded_file($tmpName, $currentDir . "/" . basename($name));
        }
    }
}

if (isset($_POST["edit_file"]) && isset($_POST["content"])) {
    file_put_contents($currentDir . "/" . basename($_POST["edit_file"]), $_POST["content"]);
}

function deleteFolderRecursively($dir) {
    if (!file_exists($dir)) return;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? deleteFolderRecursively($path) : unlink($path);
    }
    return rmdir($dir);
}

if (isset($_GET["delete"])) {
    $target = $currentDir . "/" . basename($_GET["delete"]);
    if (is_dir($target)) {
        deleteFolderRecursively($target);
    } else {
        unlink($target);
    }
    header("Location: " . joinUrlPath($path));
    exit;
}

if (isset($_POST["rename_item"]) && isset($_POST["new_name"])) {
    $oldName = basename($_POST["rename_item"]);
    $newName = basename($_POST["new_name"]);
    $oldPath = $currentDir . "/" . $oldName;
    $newPath = $currentDir . "/" . $newName;
    if (file_exists($oldPath) && !file_exists($newPath)) {
        rename($oldPath, $newPath);
    }
    header("Location: " . joinUrlPath($path));
    exit;
}

$items = scandir($currentDir);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP File Manager</title>
    <style>
        body { font-family: sans-serif; padding: 30px; max-width: 800px; margin: auto; }
        a { color: #0366d6; text-decoration: none; }
        .item { padding: 6px 0; display: flex; justify-content: space-between; }
        form { margin-bottom: 15px; }
        textarea { width: 100%; }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logout {
            font-size: 14px;
            text-decoration: none;
            color: #d00;
            padding: 6px 10px;
            border: 1px solid #d00;
            border-radius: 4px;
        }

        .logout:hover {
            background-color: #d00;
            color: white;
        }
    </style>
</head>
<body>

<div class="header-bar">
    <h2>📁 /<?= htmlspecialchars($path) ?></h2>
    <a href="?logout=true" class="logout">🚪 Logout</a>
</div>
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
$showBack = strlen($path) > 0 && $path !== "." && $path !== "/";
if ($showBack):
    $segments = explode('/', $path);
    array_pop($segments);
    $parentPath = implode('/', $segments);
    $backUrl = joinUrlPath($parentPath);
?>
    <div class="item">
        <div>📁 <a href="<?= htmlspecialchars($backUrl) ?>">...</a></div>
        <div></div>
    </div>
<?php endif; ?>

<?php foreach ($items as $item):
    if ($item === "." || $item === "..") continue;
    $itemPath = trim($path . '/' . $item, '/');
    $fullPath = $currentDir . "/" . $item;
    $isRenaming = isset($_GET["rename"]) && $_GET["rename"] === $item;
?>
    <div class="item">
        <div>
            <?php if ($isRenaming): ?>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="rename_item" value="<?= htmlspecialchars($item) ?>">
                    <input type="text" name="new_name" value="<?= htmlspecialchars($item) ?>" style="width: 200px;">
                    <input type="submit" value="💾">
                </form>
            <?php else: ?>
                <?php if (is_dir($fullPath)): ?>
                    📁 <a href="<?= joinUrlPath($itemPath) ?>"><?= $item ?></a>
                <?php else: ?>
                    📄 <a href="/file.php/<?= $path ?>/<?= rawurlencode($item) ?>"><?= $item ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div>
            <a href="<?= joinUrlPath($path) ?>?delete=<?= urlencode($item) ?>" onclick="return confirm('Delete?')">❌</a> |
            <?php if (!$isRenaming): ?>
                <a href="<?= joinUrlPath($path) ?>?rename=<?= urlencode($item) ?>">✏️</a> |
            <?php endif; ?>
            <a href="#" onclick="copyToClipboard('<?= 'raw.php/' . $itemPath ?>'); return false;">🔗</a>
        </div>
    </div>
<?php endforeach; ?>

<script>
function copyToClipboard(relativePath) {
    const fullUrl = location.origin + "/" + relativePath;
    navigator.clipboard.writeText(fullUrl).then(function() {
        showToast();
    }, function(err) {
        alert("Gagal menyalin: " + err);
    });
}
function showToast() {
    const toast = document.getElementById("copyToast");
    toast.style.display = "block";
    setTimeout(() => {
        toast.style.display = "none";
    }, 2000);
}
</script>
<div id="copyToast" style="
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    display: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    z-index: 9999;
">
    Link disalin ke clipboard!
</div>
</body>
</html>
