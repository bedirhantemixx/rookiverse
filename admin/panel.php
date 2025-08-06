<?php 
$page_title = "Dashboard";
require_once 'admin_header.php';
require_once 'admin_sidebar.php';
require_once 'config.php';

$pdo = get_db_connection();
$total_teams = $pdo->query("SELECT count(*) FROM teams")->fetchColumn();
$pending_approvals = $pdo->query("SELECT count(*) FROM courses WHERE status='pending'")->fetchColumn();
$notification_count = $pending_approvals;