<?php
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once $projectRoot . '/config.php';
$pdo = get_db_connection();
$count = $pdo->prepare("SELECT 
    COUNT(*) AS total,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM notifications WHERE team_id = ?");
$count->execute([$_SESSION['team_db_id']]);
list($totalRows, $unreadTotal) = $count->fetch(PDO::FETCH_NUM);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title ?? 'Admin Paneli'; ?> - FRC Rookieverse</title>
    <link rel="stylesheet" href="<?=BASE_URL?>/assets/css/panel2.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>@font-face { font-family: "Sakana"; src: url("../assets/fonts/Sakana.ttf") format("truetype"); }</style>
</head>
<body>
<div class="panel-layout">