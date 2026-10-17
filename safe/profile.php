
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['profile_csrf_token'])) {
    $_SESSION['profile_csrf_token'] = bin2hex(random_bytes(32));
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT id, full_name, email
     FROM users
     WHERE id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['profile_csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || mb_strlen($name, 'UTF-8') > 100) {
        $error = 'Please enter a valid name (maximum 100 characters).';
    } elseif (
        mb_strlen($email, 'UTF-8') > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'Please enter a valid email address.';
    } else {
        $check = $pdo->prepare(
            'SELECT id FROM users
             WHERE email = ? AND id != ?'
        );
        $check->execute([$email, $userId]);

        if ($check->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $update = $pdo->prepare(
                'UPDATE users
                 SET full_name = ?, email = ?
                 WHERE id = ?'
            );

            $update->execute([$name, $email, $userId]);

            $_SESSION['user_name'] = $name;
            $_SESSION['profile_csrf_token'] =
                bin2hex(random_bytes(32));

            $success = 'Profile updated successfully.';

            $user['full_name'] = $name;
            $user['email'] = $email;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="announcements.php">Announcements</a>
    <a href="directory.php">Student Directory</a>
    <a href="search.php">Search Students</a>
    <a href="profile.php" class="active">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">My Profile</h1>
    <p class="subtitle">Update your personal information.</p>

    <div class="card">
        <h2>Edit Profile</h2>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['profile_csrf_token']) ?>"
            >

            <p><label for="name">Full Name</label></p>
            <input
                type="text"
                id="name"
                name="full_name"
                value="<?= e($user['full_name']) ?>"
                maxlength="100"
                required
                style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:7px"
            >

            <p><label for="email">Email Address</label></p>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($user['email']) ?>"
                maxlength="150"
                required
                style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:7px"
            >

            <p style="margin-top:22px">
                <button class="btn" type="submit">
                    Save Changes
                </button>
            </p>
        </form>
    </div>
</main>

</body>
</html>
