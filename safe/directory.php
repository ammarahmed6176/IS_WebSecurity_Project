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

$stmt = $pdo->query(
    "SELECT id, full_name, email, role
     FROM users
     ORDER BY full_name ASC"
);

$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Directory</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="announcements.php">Announcements</a>
    <a href="directory.php" class="active">Student Directory</a>
    <a href="search.php">Search Students</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">Student Directory</h1>
    <p class="subtitle">Registered university students</p>

    <div class="card">
        <h2>Student Records (<?= count($students) ?>)</h2>

        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= (int)$student['id'] ?></td>
                        <td><?= e($student['full_name']) ?></td>
                        <td><?= e($student['email']) ?></td>
                        <td><?= e($student['role']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$students): ?>
                    <tr>
                        <td colspan="4">No students found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Directory Preview</h2>
        <p id="directory-preview"></p>
    </div>
</main>

<script>
    const params = new URLSearchParams(window.location.search);
    const preview = params.get('preview');

    if (preview !== null) {
        document.getElementById('directory-preview').textContent = preview;
    }
</script>

</body>
</html>
