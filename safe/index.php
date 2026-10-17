
<?php
require_once __DIR__ . '/../config.php';

$count = $pdo->query(
    "SELECT COUNT(*) FROM announcements"
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Portal - Safe</title>
</head>
<body>
    <h1>University Student Portal</h1>
    <h2>Safe Version</h2>
    <p>PHP and MySQL are connected successfully.</p>
    <p>Announcements in database: <?= (int)$count ?></p>
    <a href="../vulnerable/">Open Vulnerable Version</a>
</body>
</html>
