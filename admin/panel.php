<?php 
$page_title = "Dashboard";
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
require_once($projectRoot . '/admin/admin_header.php');
require_once($projectRoot . '/admin/admin_sidebar.php');

$pdo = get_db_connection();
$total_teams = $pdo->query("SELECT count(*) FROM teams")->fetchColumn();
$pending_approvals = $pdo->query("SELECT count(*) FROM courses WHERE status='pending'")->fetchColumn();
$notification_count = $pending_approvals;
?>

    <main class="main-content">
        <div class="top-bar">
            <div class="top-bar-actions">
                <div class="top-bar-item">
                    <button id="notification-btn" class="top-bar-button relative">
                        <i data-lucide="bell"></i>
                        <?php if ($notification_count > 0): ?>
                            <span class="notification-badge"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="notification-dropdown" class="dropdown-menu">
                        <div class="p-2 font-bold border-b">Bildirimler</div>
                        <?php if ($notification_count > 0): ?>
                            <a href="approvals.php"><i data-lucide="check-square"></i><span><?php echo $notification_count; ?> yeni onay bekliyor.</span></a>
                        <?php else: ?>
                            <div class="p-4 text-center text-sm text-gray-500">Yeni bildirim yok.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="top-bar-item">
                    <button id="profile-btn" class="top-bar-button"><i data-lucide="user-circle"></i></button>
                    <div id="profile-dropdown" class="dropdown-menu">
                        <a href="#"><i data-lucide="users"></i> Kullanıcı Değiştir</a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php"><i data-lucide="log-out"></i> Güvenli Çıkış</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-area">
            <div class="page-header">
                <h1>Hoş geldin, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
            </div>

            <div class="stats-grid mb-8">
                <div class="stat-card card"><div class="value"><?php echo $total_teams; ?></div><div class="label">Toplam Takım Sayısı</div></div>
                <div class="stat-card card"><div class="value" style="color: red;"><?php echo $pending_approvals; ?></div><div class="label">Bekleyen Onay İşlemi</div></div>
                <div class="stat-card card"><div class="value">12</div><div class="label">Son Eklenen Takım</div></div>
            </div>

            <div class="card">
                <h2>Son Aktiviteler</h2>
                <p class="text-gray-500 mt-4">Burada son işlemlerin bir listesi görünecek.</p>
            </div>
        </div>
    </main>

<?php require_once 'admin_footer.php'; ?>