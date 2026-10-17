<?php
session_start();
require_once __DIR__ . '/../config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $password === ''
    ) {
        $error = 'Please enter valid login details.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, password_hash, role
             FROM users
             WHERE email = ?'
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (
            $user &&
            password_verify($password, $user['password_hash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - University Portal</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #2563eb);
        }
        .card {
            width: 390px;
            max-width: 92%;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,.2);
        }
        h2 { margin-bottom: 8px; color: #0f172a; }
        .subtitle { color: #64748b; margin-bottom: 25px; }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
            color: #334155;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
        }
        button {
            width: 100%;
            padding: 13px;
            margin-top: 22px;
            background: #2563eb;
            border: 0;
            border-radius: 7px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .error {
            padding: 10px;
            border-radius: 6px;
            background: #fee2e2;
            color: #991b1b;
        }
        .bottom { text-align: center; margin-top: 20px; }
        a { color: #2563eb; }
    </style>
</head>
<body>
<div class="card">
    <h2>Welcome Back</h2>
    <p class="subtitle">Sign in to your student portal</p>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            required
            autocomplete="username"
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
        >

        <button type="submit">Login</button>
    </form>

    <p class="bottom">
        Don't have an account?
        <a href="register.php">Register</a>
    </p>
</div>
</body>
</html>
