<?php

$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once $projectRoot . '/admin/admin_header.php'; // Tasarım için

if (!isset($_SESSION['team_logged_in'])) { header("Location: $projectRoot/team-login.php'"); exit(); }
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

// Takıma ait kursları çek
$stmt = $pdo->prepare("SELECT id, title, status FROM courses WHERE team_db_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['team_db_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Takım Paneli";
?>

<aside class="sidebar">
    <div class="sidebar-header"><a href="panel.php"><span class="rookieverse">TAKIM PANELİ</span></a></div>
    <div class="sidebar-profile">
        <div class="icon"><i data-lucide="users"></i></div>
        <h2>Hoş Geldin,</h2>
        <p>Takım #<?php echo htmlspecialchars($_SESSION['team_number']); ?></p>
    </div>
    <nav class="sidebar-nav">
        <a href="panel.php" class="active"><i data-lucide="layout-dashboard"></i> Panelim</a>
        <a href="create_course.php"><i data-lucide="plus-square"></i> Yeni Kurs Oluştur</a>
        <a href="profile.php"><i data-lucide="settings"></i> Profilimi Düzenle</a>
        <a href="logout.php" class="logout-link"><i data-lucide="log-out"></i> Güvenli Çıkış</a>
    </nav>
</aside>

<main class="main-content">
    <div class="top-bar">
        <div class="font-bold">Takım #<?php echo htmlspecialchars($_SESSION['team_number']); ?> Yönetim Paneli</div>
    </div>
    <div class="content-area">
        <div class="page-header"><h1>Panelim</h1></div>
        <div class="card mb-6">
            <a href="create_course.php" class="btn"><i data-lucide="plus"></i> Yeni Kurs Ekleme Talebi Oluştur</a>
        </div>
        <div class="card">
            <h2>Kursların</h2>
            <?php if (count($courses) > 0): ?>
                <table>
                    <thead><tr><th>Kurs Başlığı</th><th>Durum</th><th>İşlemler</th></tr></thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><span class="text-xs font-semibold px-2 py-1 rounded-full bg-yellow-100 text-yellow-800"><?php echo htmlspecialchars($course['status']); ?></span></td>
                            <td><a href="view_curriculum.php?id=<?php echo $course['id']; ?>" class="btn btn-sm">İçeriği Yönet</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Henüz oluşturulmuş bir kursunuz bulunmuyor.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once '../admin/admin_footer.php'; ?>