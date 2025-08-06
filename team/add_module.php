<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
require_once '../config.php';
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) { die("Kurs ID'si bulunamadı."); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Yeni Bölüm Ekle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/manage_curriculum.css">
</head>
<body class="bg-gray-100">
<?php require_once '../navbar.php'; ?>
<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Yeni Bölüm Ekle</h1>
        <a href="view_curriculum.php?id=<?php echo $course_id; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Geri Dön</a>
    </div>
    
    <form id="module-form" action="save_module.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_module">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        
        <div class="card space-y-6">
            <div>
                <label for="module_title" class="font-bold text-lg">Bölüm Başlığı</label>
                <input type="text" id="module_title" name="title" class="form-input p-3 border mt-2" required>
            </div>
            
            <div class="content-items-container space-y-4">
                <!-- İçerik ekleme alanı -->
            </div>
            <div class="flex gap-4 mt-6 border-t pt-4">
                <button type="button" onclick="addContentItem(this, 'video')" class="btn btn-secondary btn-sm"><i data-lucide="video-plus"></i> Video Ekle</button>
                <button type="button" onclick="addContentItem(this, 'document')" class="btn btn-secondary btn-sm"><i data-lucide="file-plus"></i> Döküman Ekle</button>
                <button type="button" onclick="addContentItem(this, 'text')" class="btn btn-secondary btn-sm"><i data-lucide="pilcrow"></i> Metin Ekle</button>
            </div>
        </div>

        <div class="text-right mt-8">
            <button type="submit" class="btn text-lg"><i data-lucide="save" class="mr-2"></i> Bölümü Kaydet ve Onaya Gönder</button>
        </div>
    </form>
</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Önceki yanıttaki çalışan "addContentItem", "setupContentUpload", "formatDoc" gibi
    // tüm JavaScript fonksiyonları buraya gelecek.
</script>
</body>
</html>