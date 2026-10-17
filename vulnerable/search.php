<?php
session_name('VULN_PORTAL');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config.php';

// Educational localhost demonstration only.
// This page intentionally contains SQL Injection
// and Reflected XSS vulnerabilities.

$query = trim($_GET['q'] ?? '');
$students = [];
$error = '';

if ($query !== '') {
    try {
        // Vulnerable: user input is directly concatenated.
        $sql = "SELECT id, full_name, email, role
                FROM users
                WHERE full_name LIKE '%$query%'
                   OR email LIKE '%$query%'
                ORDER BY full_name ASC";

        $stmt = $pdo->query($sql);
        $students = $stmt->fetchAll();

    } catch (PDOException $e) {
        $error = 'SQL query failed.';
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Search - Vulnerable</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="search.php" class="active">Search Students</a>
    <a href="directory.php">Student Directory</a>
    <a href="comments.php">Comments</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">Student Search</h1>
    <p class="subtitle">
        Search university student records.
    </p>

    <div class="card">
        <form method="GET" action="search.php">
            <label for="q">Student Name or Email</label>

            <input
                type="text"
                id="q"
                name="q"
                value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Enter name or email"
                style="display:block;width:100%;padding:12px;margin:12px 0;border:1px solid #cbd5e1;border-radius:7px"
            >

            <button class="btn" type="submit">
                Search
            </button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php elseif ($query !== ''): ?>
        <div class="card">
            <h2>Search Results</h2>

            <p>
                Results for:
                <strong><?= $query ?></strong>
            </p>

            <p class="meta">
                <?= count($students) ?> matching records
            </p>

            <?php if (!$students): ?>
                <p>No matching students found.</p>
            <?php endif; ?>

            <?php foreach ($students as $student): ?>
                <div class="comment">
                    <strong>
                        <?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?>
                    </strong>

                    <p>
                        Email:
                        <?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p class="meta">
                        Student ID: <?= (int)$student['id'] ?>
                        | Role:
                        <?= htmlspecialchars($student['role'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
