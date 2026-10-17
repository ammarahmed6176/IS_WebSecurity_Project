
<?php
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

if (empty($_SESSION['comments_csrf_token'])) {
    $_SESSION['comments_csrf_token'] =
        bin2hex(random_bytes(32));
}

$id = filter_input(
    INPUT_GET,
    'announcement_id',
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid announcement ID.');
}

$stmt = $pdo->prepare(
    'SELECT id, title, content, created_at
     FROM announcements
     WHERE id = ?'
);
$stmt->execute([$id]);
$announcement = $stmt->fetch();

if (!$announcement) {
    http_response_code(404);
    exit('Announcement not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error = 'Comment cannot exceed 2000 characters.';
    } else {
        $insert = $pdo->prepare(
            'INSERT INTO comments
             (announcement_id, user_id, comment)
             VALUES (?, ?, ?)'
        );

        $insert->execute([
            $id,
            $_SESSION['user_id'],
            $comment
        ]);

        $_SESSION['comments_csrf_token'] =
            bin2hex(random_bytes(32));

        header(
            'Location: comments.php?announcement_id=' .
            $id . '&posted=1'
        );
        exit;
    }
}

$commentsStmt = $pdo->prepare(
    'SELECT c.comment, c.created_at, u.full_name
     FROM comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.announcement_id = ?
     ORDER BY c.created_at DESC, c.id DESC'
);

$commentsStmt->execute([$id]);
$comments = $commentsStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcement Comments</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="sidebar">
    <h2>University Portal</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="announcements.php" class="active">Announcements</a>
    <a href="logout.php">Logout</a>
</nav>

<main>

    <a class="back-link" href="announcements.php">
        &larr; Back to Announcements
    </a>

    <div class="card">
        <h1><?= e($announcement['title']) ?></h1>

        <p class="meta">
            Published:
            <?= e($announcement['created_at']) ?>
        </p>

        <p class="announcement-content"><?= e($announcement['content']) ?></p>
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

        <form method="POST"
              action="comments.php?announcement_id=<?= (int)$id ?>">

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
            ><?= isset($comment) ? e($comment) : '' ?></textarea>

            <p>
                <button class="btn" type="submit">
                    Post Comment
                </button>
            </p>
        </form>
    </div>

    <div class="card">
        <h2>Student Comments (<?= count($comments) ?>)</h2>

        <?php if (!$comments): ?>
            <p>No comments yet. Be the first to comment.</p>
        <?php endif; ?>

        <?php foreach ($comments as $c): ?>
            <div class="comment">
                <strong><?= e($c['full_name']) ?></strong>

                <p class="meta">
                    <?= e($c['created_at']) ?>
                </p>

                <p><?= e($c['comment']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

</main>
</body>
</html>
