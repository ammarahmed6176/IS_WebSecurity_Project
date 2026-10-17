
<?php
session_name('VULN_PORTAL');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config.php';

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Normal directory listing
$directoryStmt = $pdo->query(
    "SELECT id, full_name, email, role
     FROM users
     ORDER BY id ASC"
);

$students = $directoryStmt->fetchAll();

// Intentionally vulnerable advanced lookup
$recordId = $_GET['record_id'] ?? '';
$lookupResults = [];
$error = '';

if ($recordId !== '') {
    try {
        // Vulnerable: direct SQL concatenation.
        // The query returns exactly four columns.
        $sql = "SELECT id, full_name, email, role
                FROM users
                WHERE id = $recordId";

        $stmt = $pdo->query($sql);
        $lookupResults = $stmt->fetchAll();

    } catch (PDOException $ex) {
        $error = 'Database query failed.';
        error_log($ex->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Directory - Vulnerable</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="search.php">Search Students</a>
    <a href="directory.php" class="active">Student Directory</a>
    <a href="lookup.php">Student Lookup</a>
    <a href="comments.php">Comments</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">Student Directory</h1>
    <p class="subtitle">
        Browse university students and look up records.
    </p>

    <div class="card">
        <h2>Registered Students</h2>

        <?php foreach ($students as $student): ?>
            <div class="comment">
                <strong><?= e($student['full_name']) ?></strong>
                <p>Email: <?= e($student['email']) ?></p>
                <p class="meta">
                    ID: <?= (int)$student['id'] ?>
                    | Role: <?= e($student['role']) ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h2>Advanced Record Lookup</h2>

        <form method="GET" action="directory.php">

            <label for="record_id">Student Record ID</label>

            <input
                type="text"
                id="record_id"
                name="record_id"
                value="<?= e($recordId) ?>"
                placeholder="Enter record ID"
                style="display:block;width:100%;padding:12px;margin:12px 0;border:1px solid #cbd5e1;border-radius:7px"
            >

            <button class="btn" type="submit">
                Search Record
            </button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($recordId !== '' && !$error): ?>
        <div class="card">
            <h2>Lookup Results</h2>

            <p class="meta">
                <?= count($lookupResults) ?> record(s) returned.
            </p>

            <?php foreach ($lookupResults as $row): ?>
                <div class="comment">
                    <p>ID: <?= e($row['id']) ?></p>
                    <p>Name: <?= e($row['full_name']) ?></p>
                    <p>Email: <?= e($row['email']) ?></p>
                    <p>Role: <?= e($row['role']) ?></p>
                </div>
            <?php endforeach; ?>

            <?php if (!$lookupResults): ?>
                <p>No matching record found.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Directory Preview</h2>
        <p id="directory-preview"></p>
    </div>
</main>

<script>
    const params = new URLSearchParams(window.location.search);
    const preview = params.get('preview');

    if (preview !== null) {
        // Intentionally vulnerable DOM-based XSS.
        document.getElementById('directory-preview').innerHTML = preview;
    }
</script>

</body>
</html>
