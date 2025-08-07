<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

$message = '';
// Form gönderildiyse
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Formdan gelen linkleri al
    $team_name = $_POST['team_name'];
    $website_url = $_POST['website_url'];
    // ... Diğer sosyal medya linkleri ...

    // Veritabanını güncelle
    $stmt = $pdo->prepare("UPDATE teams SET team_name = ?, website_url = ? WHERE id = ?");
    
    if ($stmt->execute([$team_name, $website_url, $_SESSION['team_db_id']])) {
        $message = "<div class='p-4 mb-6 text-sm text-green-700 bg-green-100 rounded-lg'>Profil bilgileri başarıyla güncellendi!</div>";
    }
}

// Takımın güncel bilgilerini veritabanından çek
$stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
$stmt->execute([$_SESSION['team_db_id']]);
$team_info = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Profilimi Düzenle";
require_once '../admin/admin_header.php';
?>

<aside class="sidebar">
    <div class="sidebar-header"><a href="panel.php"><span class="rookieverse">TAKIM PANELİ</span></a></div>
    <nav class="sidebar-nav">
        <a href="panel.php"><i data-lucide="layout-dashboard"></i> Panelim</a>
        <a href="create_course.php"><i data-lucide="plus-square"></i> Yeni Kurs Oluştur</a>
        <a href="profile.php" class="active"><i data-lucide="settings"></i> Profilim</a>
        <a href="logout.php" class="logout-link"><i data-lucide="log-out"></i> Güvenli Çıkış</a>
    </nav>
</aside>

<main class="main-content">
    <div class="top-bar">
        <div class="font-bold">Takım #<?php echo htmlspecialchars($_SESSION['team_number']); ?> Yönetim Paneli</div>
    </div>
    <div class="content-area">
        <div class="page-header"><h1>Profil ve Sosyal Medya</h1></div>
        <?php echo $message; ?>
        <div class="card">
            <h2 class="text-xl font-bold mb-4">Takım Bilgilerini Güncelle</h2>
            <form action="profile.php" method="POST" class="space-y-6">
                <div><label for="team_name" class="font-medium">Takım Adı</label><input type="text" id="team_name" name="team_name" value="<?php echo htmlspecialchars($team_info['team_name'] ?? ''); ?>" class="form-input p-2 border mt-1"></div>
                <div><label for="website_url" class="font-medium">Web Sitesi URL</label><input type="url" id="website_url" name="website_url" value="<?php echo htmlspecialchars($team_info['website_url'] ?? ''); ?>" placeholder="https://www.takiminiz.com" class="form-input p-2 border mt-1"></div>
                <button type="submit" class="btn">Bilgileri Kaydet</button>
            </form>
        </div>
    </div>
</main>

<?php require_once '../admin/admin_footer.php'; ?>