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

$query = trim($_GET['q'] ?? '');
$students = [];
$error = '';

if (mb_strlen($query, 'UTF-8') > 100) {
    $error = 'Search query is too long.';
} elseif ($query !== '') {
    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, role
         FROM users
         WHERE full_name LIKE ? OR email LIKE ?
         ORDER BY full_name ASC
         LIMIT 50'
    );

    // Treat wildcard characters as literal search text.
    $escaped = str_replace(
        ['!', '%', '_'],
        ['!!', '!%', '!_'],
        $query
    );

    $pattern = '%' . $escaped . '%';

    $stmt->execute([$pattern, $pattern]);
    $students = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Students</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="announcements.php">Announcements</a>
    <a href="directory.php">Student Directory</a>
    <a href="search.php" class="active">Search Students</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">Search Students</h1>
    <p class="subtitle">Search by student name or email.</p>

    <div class="card">
        <form method="GET" action="search.php">
            <label for="q">Search Keyword</label>

            <input
                id="q"
                type="text"
                name="q"
                value="<?= e($query) ?>"
                maxlength="100"
                placeholder="Enter student name or email"
                style="width:100%;padding:12px;margin:12px 0;border:1px solid #cbd5e1;border-radius:7px"
            >

            <button class="btn" type="submit">Search</button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php elseif ($query !== ''): ?>
        <div class="card">
            <h2>Search Results</h2>

            <p>
                Results for:
                <strong><?= e($query) ?></strong>
            </p>

            <p class="meta">
                <?= count($students) ?> matching student(s)
            </p>

            <?php if (!$students): ?>
                <p>No matching students found.</p>
            <?php endif; ?>

            <?php foreach ($students as $student): ?>
                <div class="comment">
                    <strong><?= e($student['full_name']) ?></strong>
                    <p>Email: <?= e($student['email']) ?></p>
                    <p class="meta">
                        Student ID: <?= (int)$student['id'] ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
