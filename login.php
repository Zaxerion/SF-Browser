<?php
session_start();

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: index.php");
    exit;
}

$stored_username = 'aa';
$stored_password_hash = password_hash('aa', PASSWORD_DEFAULT);

$error = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    if ($username === $stored_username && password_verify($password, $stored_password_hash)) {
        $_SESSION["logged_in"] = true;
        $_SESSION["username"] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f2f2f2;
            overflow: hidden;
        }

        .login-container {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 360px;
        }

        h2 {
            margin-bottom: 16px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"], input[type="password"] {
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
