<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
require_once '../config.php';
$pdo = connectDB();

// Dosya yükleme ve dizi düzenleme yardımcı fonksiyonları
function reorganize_files_array($files_arr) { /* ... (önceki cevaptaki gibi) ... */ }
function upload_content_file($file_data, $course_id) { /* ... (önceki cevaptaki gibi) ... */ }

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $team_id = $_SESSION['team_db_id'];
    $reorganized_files = isset($_FILES['modules']) ? reorganize_files_array($_FILES['modules']) : [];

    // Güvenlik kontrolü
    $stmt_check = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND team_db_id = ?");
    $stmt_check->execute([$course_id, $team_id]);
    if ($stmt_check->fetchColumn() === false) { die("Yetkisiz işlem."); }

    try {
        $pdo->beginTransaction();
        
        // Bu kursa ait tüm eski modülleri ve içerikleri temizle (En sağlam yöntem)
        $stmt_get_modules = $pdo->prepare("SELECT id FROM course_modules WHERE course_id = ?");
        $stmt_get_modules->execute([$course_id]);
        $module_ids = $stmt_get_modules->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($module_ids)) {
            $in_clause = implode(',', array_fill(0, count($module_ids), '?'));
            $pdo->prepare("DELETE FROM module_contents WHERE module_id IN ($in_clause)")->execute($module_ids);
            $pdo->prepare("DELETE FROM course_modules WHERE course_id = ?")->execute([$course_id]);
        }

        // Formdan gelen yeni verileri sırasıyla işle
        if (isset($_POST['modules']) && is_array($_POST['modules'])) {
            $module_sort_order = 0;
            foreach ($_POST['modules'] as $module_key => $module_data) {
                // ... (Önceki cevaptaki modül ve içerik ekleme döngüsü) ...
            }
        }
        
        $pdo->commit();
        header("Location: panel.php?status=curriculum_saved");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Bir hata oluştu: ". $e->getMessage());
    }
}
?>