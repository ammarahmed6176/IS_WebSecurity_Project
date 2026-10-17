<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, full_name, email, role
     FROM users
     WHERE id = ?'
);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$announcementStmt = $pdo->query(
    'SELECT id, title, content, created_at
     FROM announcements
     ORDER BY created_at DESC'
);
$announcements = $announcementStmt->fetchAll();

$feeStmt = $pdo->prepare(
    'SELECT semester, amount, status
     FROM fees
     WHERE user_id = ?
     ORDER BY id DESC'
);
$feeStmt->execute([$user['id']]);
$fees = $feeStmt->fetchAll();

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Dashboard</title>
<style>
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f1f5f9;
        color: #1e293b;
    }
    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        width: 230px;
        background: #0f172a;
        color: white;
        padding: 25px 18px;
    }
    .sidebar h2 { font-size: 21px; margin-bottom: 30px; }
    .sidebar a {
        display: block;
        color: #cbd5e1;
        text-decoration: none;
        padding: 13px;
        border-radius: 7px;
        margin-bottom: 6px;
    }
    .sidebar a:hover, .sidebar .active {
        background: #2563eb;
        color: white;
    }
    main { margin-left: 230px; padding: 32px; }
    .header { margin-bottom: 27px; }
    .header p { color: #64748b; }
    .cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    .card, .section {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0,0,0,.04);
    }
    .card small { color: #64748b; }
    .card h2 { margin-bottom: 0; }
    .section { margin-bottom: 22px; }
    .announcement {
        padding: 14px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .announcement:last-child { border-bottom: none; }
    .announcement p { line-height: 1.5; }
    table { width: 100%; border-collapse: collapse; }
    th, td {
        text-align: left;
        padding: 13px;
        border-bottom: 1px solid #e2e8f0;
    }
    th { color: #475569; }
    .paid { color: #15803d; }
    .unpaid { color: #b91c1c; }
    @media (max-width: 800px) {
        .sidebar {
            position: static;
            width: 100%;
        }
        main { margin-left: 0; padding: 18px; }
        .cards { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>

    <a class="active" href="dashboard.php">Dashboard</a>
    <a href="directory.php">Student Directory</a>
    <a href="search.php">Search Students</a>
    <a href="lookup.php">Student Lookup</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <div class="header">
        <h1>Welcome, <?= e($user['full_name']) ?>!</h1>
        <p>Your student dashboard overview</p>
    </div>

    <div class="cards">
        <div class="card">
            <small>Student ID</small>
            <h2>#<?= (int)$user['id'] ?></h2>
        </div>
        <div class="card">
            <small>Total Announcements</small>
            <h2><?= count($announcements) ?></h2>
        </div>
        <div class="card">
            <small>Fee Records</small>
            <h2><?= count($fees) ?></h2>
        </div>
    </div>

    <div class="section">
        <h2>My Profile</h2>
        <p><strong>Name:</strong>
            <?= e($user['full_name']) ?>
        </p>
        <p><strong>Email:</strong>
            <?= e($user['email']) ?>
        </p>
        <p><strong>Role:</strong>
            <?= e(ucfirst($user['role'])) ?>
        </p>
    </div>

    <div class="section">
        <h2>Latest Announcements</h2>

        <?php if (!$announcements): ?>
            <p>No announcements available.</p>
        <?php endif; ?>

        <?php foreach ($announcements as $a): ?>
            <div class="announcement">
                <h3><?= e($a['title']) ?></h3>
                <p><?= e($a['content']) ?></p>
                <small><?= e($a['created_at']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h2>My Fee Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Amount (PKR)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($fees as $fee): ?>
                <tr>
                    <td><?= e($fee['semester']) ?></td>
                    <td><?= number_format((float)$fee['amount'], 2) ?></td>
                    <td class="<?= e($fee['status']) ?>">
                        <?= e(ucfirst($fee['status'])) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$fees): ?>
                <tr>
                    <td colspan="3">No fee records available.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
