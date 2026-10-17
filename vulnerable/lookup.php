
<?php
session_name('VULN_PORTAL');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config.php';

// Intentionally vulnerable to Boolean-based SQL injection.
// Local educational demonstration only.

$studentId = trim($_GET['student_id'] ?? '');
$result = null;
$error = '';

if ($studentId !== '') {
    try {
        $sql = "SELECT COUNT(*) FROM users
                WHERE id = $studentId AND role = 'student'";

        $stmt = $pdo->query($sql);

        $result = (int)$stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        $error = 'SQL query failed.';
        error_log($e->getMessage());
    }
}

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
    <title>Student Lookup - Vulnerable</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="search.php">Search Students</a>
    <a href="directory.php">Student Directory</a>
    <a href="lookup.php" class="active">Student Lookup</a>
    <a href="comments.php">Comments</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">Student Lookup</h1>
    <p class="subtitle">
        Verify whether a student ID exists.
    </p>

    <div class="card">
        <form method="GET" action="lookup.php">
            <label for="student_id">Student ID</label>

            <input
                type="text"
                id="student_id"
                name="student_id"
                value="<?= e($studentId) ?>"
                placeholder="Enter Student ID"
                style="display:block;width:100%;padding:12px;margin:12px 0;border:1px solid #cbd5e1;border-radius:7px"
                required
            >

            <button class="btn" type="submit">
                Verify Student
            </button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php elseif ($result !== null): ?>
        <div class="card">
            <h2>Verification Result</h2>

            <?php if ($result): ?>
                <div class="alert alert-success">
                    Student record exists.
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    Student record not found.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>
</body>
</html>
