<?php

session_start();
if (!isset($_SESSION['team_logged_in'])) {
    exit('Yetkisiz erişim');
}
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

// ... session, config, upload, reorganize fonksiyonları buraya kadar aynı kalsın

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['module_id'])) {
    $module_id = $_POST['module_id'];
    $course_id = $_POST['course_id'];

    // Yetki kontrolü (aynı)
    $stmt_check = $pdo->prepare("SELECT c.id FROM course_modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ? AND c.team_db_id = ?");
    $stmt_check->execute([$module_id, $_SESSION['team_db_id']]);
    if ($stmt_check->fetchColumn() === false) {
        die("Bu işlemi yapma yetkiniz yok.");
    }

    $reorganized_files = isset($_FILES['contents']) ? reorganize_files_array($_FILES['contents']) : [];

    try {
        $title = $_POST['title'] ?? null;
        $stmt_update = $pdo->prepare("UPDATE course_modules SET title = ? WHERE id = ?");
        $stmt_update->execute([$title, $module_id]);





        // Modül durumu güncelle
        $stmt_update = $pdo->prepare("UPDATE course_modules SET status = 'pending' WHERE id = ?");
        $stmt_update->execute([$module_id]);

        $pdo->commit();
    } catch (Exception $e) {
    }

    header("Location: view_curriculum.php?id=" . $module_id . "&status=updated");
    exit();
}

?>