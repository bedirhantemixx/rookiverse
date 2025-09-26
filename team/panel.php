<?php
session_start();
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once 'team_header.php'; // Tasarım için

if (!isset($_SESSION['team_logged_in'])) { header("Location: ../team-login.php"); exit(); }
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();



// Takıma ait kursları çek
$stmt = $pdo->prepare("SELECT id, title, status FROM courses WHERE team_db_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['team_db_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Takım Paneli";
?>
    <script>
        // Lucide varsa ikonları çizelim; yoksa sessiz geç
        try { if (window.lucide?.createIcons) lucide.createIcons(); } catch(e){}
        // Aktif link sınıfını otomatik ata (exact match)
        (function(){
            var here = location.pathname.split('/').pop() || 'panel.php';
            document.querySelectorAll('.sidebar-nav a').forEach(a=>{
                var href = a.getAttribute('href') || '';
                if(href === here){ a.classList.add('active'); }
            });
        })();
    </script>

    <style>
        .logo-preview {
            width: 65px;
            height: 65px;
            border-radius: 18px;
            object-fit: cover;
            border: 1px solid var(--border);
            background: #fff;
            display: block;
            flex-shrink: 0;
        }
        .notification-button {
            position: relative;
            background: none;
            border: none;
            color: black;
            cursor: pointer;
            padding: 0;
        }

        .notification-button .lucide {
            color: black; /* İkon rengini siyah yap */
            width: 24px;
            height: 24px;
            margin-right: 15px;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: 8px;
            background-color: #ffc107;
            color: black;
            font-size: 10px;
            font-weight: bold;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid white;
        }

    </style>


<aside class="sidebar">
    <a class="flex items-center space-x-2" href="<?php echo BASE_URL; ?>">
        <span class="rookieverse">FRC ROOKIEVERSE</span>
    </a>
    <div class="sidebar-profile">
        <img src="http://localhost/rookiverse/rookiverse/uploads/team_3/logo_1758778191_d746d8b2.jpg" class="logo-preview" id="logoPreview" alt="Takım Logosu">        <h2>Hoş Geldin,</h2>
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
        <div class="font-bold">Takım #<?php echo $_SESSION['team_number']; ?> Paneli</div>
        <div class="actions">
            <button class="notification-button">
                <i data-lucide="bell"></i>
                <div class="notification-badge">3</div>
            </button>
            <a href="panel.php" class="btn btn-sm"><i data-lucide="arrow-left"></i> Panele Dön</a>
        </div>
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