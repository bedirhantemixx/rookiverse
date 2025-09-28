<?php
$pending_courses = 0;
try {
    if (!isset($pdo)) {
        $projectRoot = dirname(__DIR__); // admin/ içinden çalışıyorsan doğru
        require_once $projectRoot . '/config.php';
        $pdo = get_db_connection();
    }
    $pending_courses = (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status='pending'")->fetchColumn();
} catch (Throwable $e) {
    $pending_courses = 0; // hata olursa sessizce 0
}
// Rozette aşırı uzun görünmesin diye:
$pending_badge = $pending_courses > 99 ? '99+' : (string)$pending_courses;
?>

<style>
    .menu-label-with-badge{display:inline-flex;align-items:center;gap:8px}
    .menu-badge{
        background:#ef4444;color:#fff;border-radius:9999px;
        font-size:11px;line-height:1;padding:4px 6px;min-width:20px;
        display:inline-flex;align-items:center;justify-content:center;
        font-weight:600;
    }
    .submenu-note{color:#6b7280;font-size:12px;padding:6px 12px;display:block}
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <a style="text-decoration: none" href="panel.php"><span class="rookieverse">FRC ROOKIEVERSE</span></a>
    </div>

    <nav class="sidebar-nav">
        <div class="menu-item">
            <div class="menu-title">
                <div class="menu-label-with-badge">
                    <i data-lucide="home" class="menu-icon"></i>Dashboard
                </div>
                <i data-lucide="chevron-right" class="menu-arrow"></i>
            </div>
            <div class="submenu"><a href="panel.php" class="<?php echo ($current_page == 'panel.php') ? 'active' : ''; ?>">Kontrol Paneli</a></div>
        </div>

        <div class="menu-item">
            <div class="menu-title">
                <div class="menu-label-with-badge">
                    <i data-lucide="users" class="menu-icon"></i>Takımlar
                </div>
                <i data-lucide="chevron-right" class="menu-arrow"></i>
            </div>
            <div class="submenu"><a href="team_actions.php">Takımları Görüntüle</a></div>
        </div>

        <div class="menu-item">
            <div class="menu-title">
                <div class="menu-label-with-badge">
                    <i data-lucide="book-open" class="menu-icon"></i>Kurslar
                    <?php if ($pending_courses > 0): ?>
                        <span class="menu-badge"><?php echo $pending_badge; ?></span>
                    <?php endif; ?>
                </div>
                <i data-lucide="chevron-right" class="menu-arrow"></i>
            </div>
            <div class="submenu">
                <a href="course_actions.php">Kursları Görüntüle</a>

            </div>
        </div>

        <div class="menu-item">
            <div class="menu-title">
                <div class="menu-label-with-badge">
                    <i data-lucide="user-cog" class="menu-icon"></i>Admin Yönetimi
                </div>
                <i data-lucide="chevron-right" class="menu-arrow"></i>
            </div>
            <div class="submenu"><a href="#">Adminleri Yönet</a><a href="logout.php">Çıkış Yap</a></div>
        </div>
    </nav>
</aside>
