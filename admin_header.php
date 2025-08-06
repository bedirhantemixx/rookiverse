<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.html"); exit(); }
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title ?? 'Admin Paneli'; ?> - FRC Rookieverse</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>@font-face { font-family: "Sakana"; src: url("../assets/fonts/Sakana.ttf") format("truetype"); }</style>
</head>
<body>
    <div class="panel-layout">