<?php
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title ?? 'Admin Paneli'; ?> - FRC Rookieverse</title>
    <link rel="stylesheet" href="<?=$projectRoot?>/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>@font-face { font-family: "Sakana"; src: url("../assets/fonts/Sakana.ttf") format("truetype"); }</style>
</head>
<body>
<div class="panel-layout">