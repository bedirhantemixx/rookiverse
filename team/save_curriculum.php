<?php
session_start();
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

// Güvenlik: Kullanıcının giriş yapıp yapmadığını ve formun POST ile gönderildiğini kontrol et
if (!isset($_SESSION['team_logged_in']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Yetkisiz erişim.");
}

try {
    $course_id = $_POST['course_id'];
    $modules_data = $_POST['modules'] ?? [];
    $files_data = $_FILES['modules'] ?? [];

    // Güvenlik: Bu kursun gerçekten bu takıma ait olduğunu tekrar doğrula
    $stmt_check_owner = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND team_db_id = ?");
    $stmt_check_owner->execute([$course_id, $_SESSION['team_db_id']]);
    if ($stmt_check_owner->fetchColumn() === false) {
        die("Bu kurs üzerinde işlem yapma yetkiniz yok.");
    }
    
    $pdo->beginTransaction();

    foreach ($modules_data as $module_key => $module_info) {
        // 1. Bölümü veritabanına ekle
        $stmt_module = $pdo->prepare("INSERT INTO course_modules (course_id, title, sort_order) VALUES (?, ?, ?)");
        $stmt_module->execute([$course_id, $module_info['title'], 0]); // Bu sayfada sadece 1 bölüm olduğu için sort_order = 0
        $module_db_id = $pdo->lastInsertId();

        // 2. Bölümün içeriklerini veritabanına ekle
        if (isset($module_info['contents'])) {
            foreach ($module_info['contents'] as $content_key => $content_info) {
                
                $content_type = $content_info['type'];
                $sort_order = $content_info['sort_order'];
                $data = null;

                if ($content_type === 'text') {
                    $data = $content_info['paragraph'];
                } else {
                    // Dosya yükleme işlemleri
                    if (isset($files_data['name'][$module_key]['contents'][$content_key]['file']) && $files_data['error'][$module_key]['contents'][$content_key]['file'] === UPLOAD_ERR_OK) {
                        
                        $upload_dir = '../uploads/course_content/';
                        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                        
                        $file_name = basename($files_data['name'][$module_key]['contents'][$content_key]['file']);
                        $file_tmp = $files_data['tmp_name'][$module_key]['contents'][$content_key]['file'];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $unique_name = uniqid(time() . '_', true) . '.' . $file_ext;
                        $destination = $upload_dir . $unique_name;

                        if (move_uploaded_file($file_tmp, $destination)) {
                            $data = 'uploads/course_content/' . $unique_name; // Veritabanına göreli yolu kaydet
                        }
                    }
                }

                if ($data !== null) {
                    $stmt_content = $pdo->prepare("INSERT INTO module_contents (module_id, content_type, data, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt_content->execute([$module_db_id, $content_type, $data, $sort_order]);
                }
            }
        }
    }
    
    $pdo->commit();

    // 3. Kullanıcıyı görüntüleme sayfasına yönlendir
    header('Location: view_curriculum.php?id=' . $course_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Veritabanına kaydederken bir hata oluştu: " . $e->getMessage());
}
?>