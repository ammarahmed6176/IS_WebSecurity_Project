
<?php
session_name('VULN_PORTAL');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT id, full_name, email
     FROM users WHERE id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    exit('User not found.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (
        $name === '' ||
        mb_strlen($name, 'UTF-8') > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        mb_strlen($email, 'UTF-8') > 150
    ) {
        $error = 'Please enter valid profile details.';
    } else {
        $check = $pdo->prepare(
            'SELECT id FROM users WHERE email = ? AND id != ?'
        );
        $check->execute([$email, $userId]);

        if ($check->fetch()) {
            $error = 'Email is already registered.';
        } else {
            // Deliberately missing CSRF token verification.
            // For local educational demonstration only.
            $update = $pdo->prepare(
                'UPDATE users SET full_name = ?, email = ? WHERE id = ?'
            );
            $update->execute([$name, $email, $userId]);

            $_SESSION['user_name'] = $name;
            $user['full_name'] = $name;
            $user['email'] = $email;
            $success = 'Profile updated successfully.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile - Vulnerable</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="search.php">Search Students</a>
    <a href="directory.php">Student Directory</a>
    <a href="comments.php">Comments</a>
    <a href="profile.php" class="active">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">My Profile</h1>
    <p class="subtitle">Update personal information.</p>

    <div class="card">
        <h2>Edit Profile</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <p><label for="name">Full Name</label></p>
            <input
                type="text"
                id="name"
                name="full_name"
                value="<?= e($user['full_name']) ?>"
                maxlength="100"
                required
                style="width:100%;padding:12px"
            >

            <p><label for="email">Email</label></p>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($user['email']) ?>"
                maxlength="150"
                required
                style="width:100%;padding:12px"
            >

            <p>
                <button class="btn" type="submit">
                    Save Changes
                </button>
            </p>
        </form>
    </div>
</main>

</body>
</html>
