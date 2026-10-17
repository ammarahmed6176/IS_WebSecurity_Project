<?php
session_name('SAFE_PORTAL');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config.php';

// Secure output encoding
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Generate CSRF token
if (empty($_SESSION['comments_csrf_token'])) {
    $_SESSION['comments_csrf_token'] =
        bin2hex(random_bytes(32));
}

// Select first available announcement
$announcementStmt = $pdo->query(
    "SELECT id, title, content
     FROM announcements
     ORDER BY id ASC
     LIMIT 1"
);

$announcement = $announcementStmt->fetch();

if (!$announcement) {
    exit('No announcements found. Import database/setup.sql first.');
}

$announcementId = (int)$announcement['id'];
$error = '';

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals(
            $_SESSION['comments_csrf_token'],
            $token
        )
    ) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $comment = trim($_POST['comment'] ?? '');

    if ($comment === '') {
        $error = 'Comment cannot be empty.';
    } elseif (mb_strlen($comment, 'UTF-8') > 2000) {
        $error = 'Comment is too long.';
    } else {

        // Secure database insertion
        $stmt = $pdo->prepare(
            "INSERT INTO comments
             (announcement_id, user_id, comment)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $announcementId,
            (int)$_SESSION['user_id'],
            $comment
        ]);

        // Regenerate token after successful submission
        $_SESSION['comments_csrf_token'] =
            bin2hex(random_bytes(32));

        header('Location: comments.php?posted=1');
        exit;
    }
}

// Fetch comments
$stmt = $pdo->prepare(
    "SELECT c.id, c.comment, c.created_at, u.full_name
     FROM comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.announcement_id = ?
     ORDER BY c.id DESC"
);

$stmt->execute([$announcementId]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comments - Safe Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="search.php">Search Students</a>
    <a href="directory.php">Student Directory</a>
    <a href="comments.php" class="active">Comments</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<main>
    <h1 class="page-title">Student Comments</h1>

    <p class="subtitle">
        Share your thoughts about university announcements.
    </p>

    <div class="card">
        <h2><?= e($announcement['title']) ?></h2>
        <p><?= e($announcement['content']) ?></p>
    </div>

    <div class="card">
        <h2>Post a Comment</h2>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['posted'])): ?>
            <div class="alert alert-success">
                Comment posted successfully.
            </div>
        <?php endif; ?>

        <form method="POST" action="comments.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['comments_csrf_token']) ?>"
            >

            <textarea
                name="comment"
                maxlength="2000"
                placeholder="Write your comment..."
                required
            ></textarea>

            <p>
                <button class="btn" type="submit">
                    Post Comment
                </button>
            </p>
        </form>
    </div>

    <div class="card">
        <h2>All Comments (<?= count($comments) ?>)</h2>

        <?php if (!$comments): ?>
            <p>No comments yet.</p>
        <?php endif; ?>

        <?php foreach ($comments as $c): ?>
            <div class="comment">

                <strong><?= e($c['full_name']) ?></strong>

                <p class="meta">
                    <?= e($c['created_at']) ?>
                </p>

                <!-- Safe: HTML is escaped -->
                <div class="comment-text">
                    <?= e($c['comment']) ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
