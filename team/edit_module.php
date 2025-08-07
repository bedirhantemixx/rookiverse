<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

$module_id = $_GET['id'] ?? null;
if (!$module_id) { die("Bölüm ID'si bulunamadı."); }

// Güvenlik: Bu bölümün gerçekten bu takıma ait olup olmadığını kontrol et ve mevcut bilgileri çek
$stmt = $pdo->prepare("SELECT m.id, m.course_id, m.title FROM course_modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ? AND c.team_db_id = ?");
$stmt->execute([$module_id, $_SESSION['team_db_id']]);
$module = $stmt->fetch();

if (!$module) { die("Hata: Bu bölümü düzenleme yetkiniz yok veya bölüm bulunamadı."); }

$page_title = "Bölüm Başlığını Düzenle";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/manage_curriculum.css">
</head>
<body class="bg-gray-100">
<?php require_once '../navbar.php'; ?>
<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800"><?php echo $page_title; ?></h1>
        <a href="view_curriculum.php?id=<?php echo $module['course_id']; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">Geri Dön</a>
    </div>
    
    <form action="update_module_title.php" method="POST">
        <input type="hidden" name="action" value="edit_title">
        <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
        <input type="hidden" name="course_id" value="<?php echo $module['course_id']; ?>">
        
        <div class="card space-y-6">
            <div>
                <label for="module_title" class="font-bold text-lg">Bölüm Başlığı</label>
                <input type="text" id="module_title" name="title" class="form-input p-3 border mt-2" required value="<?php echo htmlspecialchars($module['title']); ?>">
            </div>
        </div>

        <div class="flex justify-between items-center mt-8">
            <a href="panel.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Panele Geri Dön</a>
            <div class="flex gap-4">
                <a href="edit_module_content.php?id=<?php echo $module['id']; ?>" class="btn btn-secondary">İçeriği Düzenle</a>
                <button type="submit" class="btn text-lg"><i data-lucide="save" class="mr-2"></i> Başlığı Kaydet</button>
            </div>
        </div>
    </form>
</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>