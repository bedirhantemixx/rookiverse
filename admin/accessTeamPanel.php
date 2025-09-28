<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("location: ../index.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['exit']){
        unset($_SESSION['team_logged_in']);
        unset($_SESSION['team_db_id']);
        unset($_SESSION['team_number']);
        unset($_SESSION['admin_panel_view']);
        header("location: panel.php");
        exit();
    }
    $id = $_GET['team_id'];
    $details = getTeam($id);
    $_SESSION['team_logged_in'] = true;
    $_SESSION['team_db_id'] = $details['id'];
    $_SESSION['team_number'] = $details['team_number'];
    $_SESSION['admin_panel_view'] = 1;
    header("location: ../team/panel.php");
    exit();
}
else{
    header("location: ../courses.php");
    exit();
}