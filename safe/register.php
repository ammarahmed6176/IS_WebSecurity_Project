
<?php
session_start();
require_once __DIR__ . '/../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid name and email.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } else {
        $check = $pdo->prepare(
            'SELECT id FROM users WHERE email = ?'
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([$name, $email, $hash]);

            $success = 'Registration successful. You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 35px;
            border-radius: 12px;
            width: 350px;
            box-shadow: 0 8px 25px rgba(0,0,0,.08);
        }
        h2 { text-align: center; color: #1e293b; }
        input {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 9px 0;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .error { color: #b91c1c; }
        .success { color: #15803d; }
    </style>
</head>
<body>
<div class="container">
    <h2>Student Registration</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password (min 8 characters)" required minlength="8">
        <button type="submit">Register</button>
    </form>

    <p style="text-align:center">
        Already registered? <a href="login.php">Login</a>
    </p>
</div>
</body>
</html>
