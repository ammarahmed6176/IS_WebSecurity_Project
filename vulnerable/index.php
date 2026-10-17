<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

$count = $pdo->query(
    "SELECT COUNT(*) FROM announcements"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Portal - Vulnerable</title>
</head>
<body>

    <h1>University Student Portal</h1>

    <h2>Vulnerable Version</h2>

    <p>PHP and MySQL are connected successfully.</p>

    <p>
        Announcements in database:
        <?= (int)$count ?>
    </p>

    <a href="../safe/">
        Open Safe Version
    </a>

</body>
</html>
