<?php
session_start();
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
header('Content-Type: application/json');
if (!isset($_SESSION['team_logged_in'])) { 
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit();
}

$response = ['success' => false, 'message' => 'Geçersiz istek.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file']) && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $upload_type = $_POST['upload_type']; // 'cover' or 'intro'
    $file = $_FILES['file'];
    
    // Gerekli güvenlik kontrolleri (dosya boyutu, kurs sahipliği vb.) burada yapılmalı
    
    $target_dir = ($upload_type === 'cover') ? "../uploads/covers/" : "../uploads/intros/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_file_name = "course_" . $course_id . "_" . $upload_type . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $pdo = get_db_connection();
        $db_column = ($upload_type === 'cover') ? 'cover_image_url' : 'intro_video_url';
        
        $stmt = $pdo->prepare("UPDATE courses SET $db_column = ?, status = 'pending' WHERE id = ? AND team_db_id = ?");
        $stmt->execute(["uploads/" . ($upload_type === 'cover' ? 'covers/' : 'intros/') . $new_file_name, $course_id, $_SESSION['team_db_id']]);
        
        $response = ['success' => true, 'message' => 'Dosya başarıyla yüklendi.'];
    } else {
        $response['message'] = 'Dosya sunucuya taşınırken bir hata oluştu.';
    }
}

echo json_encode($response);
?>