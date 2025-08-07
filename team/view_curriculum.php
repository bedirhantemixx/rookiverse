<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
$course_id = $_GET['id'] ?? null;
if (!$course_id) { die("Kurs ID'si bulunamadı."); }

$pdo = get_db_connection();
// Güvenlik ve kurs başlığını alma
$stmt_course = $pdo->prepare("SELECT title, status FROM courses WHERE id = ? AND team_db_id = ?");
$stmt_course->execute([$course_id, $_SESSION['team_db_id']]);
$course = $stmt_course->fetch();
if (!$course) { die("Hata: Bu kurs size ait değil veya bulunamadı."); }

// Bu kursa ait mevcut bölümleri çek
$stmt_modules = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order ASC, id ASC");
$stmt_modules->execute([$course_id]);
$modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Bölüm Yönetimi";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?php echo $page_title; ?> - <?php echo htmlspecialchars($course['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/manage_curriculum.css">
</head>
<body class="bg-gray-100">
<?php require_once '../navbar.php'; ?>
<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Bölüm Yönetimi</h1>
            <p class="text-gray-600">"<?php echo htmlspecialchars($course['title']); ?>" kursunun bölümlerini düzenleyin.</p>
        </div>
        <a href="panel.php" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300">Panele Geri Dön</a>
    </div>

    <div id="modules-container" class="space-y-4">
        <?php if (count($modules) > 0): ?>
            <?php foreach ($modules as $module): ?>
                <div class="module-card" data-module-id="<?php echo $module['id']; ?>">
                    <div class="module-header">
                        <h3 class="flex-grow"><?php echo htmlspecialchars($module['title']); ?></h3>
                        <div class="module-actions">
                            <a href="edit_module.php?id=<?php echo $module['id']; ?>" title="Düzenle"><i data-lucide="pencil"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card empty-state-card">
                <h3 class="text-xl font-semibold text-gray-700">Bu kurs için henüz bir bölüm bulunamadı.</h3>
                <p class="mt-2 mb-4">Aşağıdaki butona tıklayarak ilk bölümünüzü oluşturmaya başlayın.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8">
        <a href="add_module.php?course_id=<?php echo $course_id; ?>" class="btn text-lg"><i data-lucide="plus-circle" class="mr-2"></i> Yeni Bölüm Ekle</a>
    </div>
</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>