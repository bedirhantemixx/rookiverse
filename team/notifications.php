<?php
session_start();
$projectRoot = dirname(__DIR__);
require_once 'team_header.php';
require_once($projectRoot . '/config.php');

if (!isset($_SESSION['team_logged_in'])) {
    header('Location: ../team-login.php');
    exit();
}

$pdo = get_db_connection();
$teamId = (int)$_SESSION['team_db_id'];
$page_title = "Bildirimler";

// --- İşlemler (okundu yap / hepsini okundu yap) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_all_read'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE team_id = ?");
        $stmt->execute([$teamId]);
        header('Location: notifications.php');
        exit();
    }

    if (isset($_POST['toggle_read'], $_POST['nid'])) {
        $nid = (int)$_POST['nid'];
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 - is_read WHERE id = ? AND team_id = ?");
        $stmt->execute([$nid, $teamId]);
        header('Location: notifications.php');
        exit();
    }
}

// --- Sayfalama ---
$perPage = 20;
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

// --- Bildirimleri çek ---
$stmt = $pdo->prepare("
    SELECT id, content_id, type, `action`, is_read, notified_at
    FROM notifications
    WHERE team_id = :fuh
    ORDER BY notified_at DESC
");
$stmt->bindValue(':fuh', $teamId, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Toplam bildirim ve okunmamış sayısı ---
$count = $pdo->prepare("SELECT 
    COUNT(*) AS total,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM notifications WHERE team_id = ?");
$count->execute([$teamId]);
list($totalRows, $unreadTotal) = $count->fetch(PDO::FETCH_NUM);

$totalPages = max(1, ceil(($totalRows ?: 0) / $perPage));
?>

<script>
    try { if (window.lucide?.createIcons) lucide.createIcons(); } catch(e){}
    (function(){
        var here = 'notifications.php';
        document.querySelectorAll('.sidebar-nav a').forEach(a=>{
            if(a.getAttribute('href') === here){ a.classList.add('active'); }
        });
    })();
</script>

<style>
    .badge {
        display:inline-block;
        padding:0.25rem 0.5rem;
        border-radius:9999px;
        font-size:0.75rem;
        font-weight:600;
    }
    .badge-approve { background:#dcfce7; color:#166534; }
    .badge-reject { background:#fee2e2; color:#991b1b; }
    .row-unread { background:#fffdf2; }
    .note-time { font-size:.8rem; color:#666; }


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
    <?php
    if (!isset($_SESSION['admin_panel_view'])):
        ?>
        <a class="flex items-center space-x-2" href="<?php echo BASE_URL; ?>">
            <span class="rookieverse">FRC ROOKIEVERSE</span>
        </a>
    <?php endif;?>

    <div class="sidebar-profile">
        <h2>Hoş Geldin,</h2>
        <p>Takım #<?php echo htmlspecialchars($_SESSION['team_number']); ?></p>
    </div>
    <nav class="sidebar-nav">
        <a href="panel.php"><i data-lucide="layout-dashboard"></i> Panelim</a>
        <a href="create_course.php"><i data-lucide="plus-square"></i> Yeni Kurs Oluştur</a>
        <a href="profile.php"><i data-lucide="settings"></i> Profilimi Düzenle</a>
        <a href="notifications.php" class="active"><i data-lucide="bell"></i> Bildirimler</a>
        <a href="list_questions.php" ><i data-lucide="message-square"></i> Soru Yönetimi</a>

        <?php
        if (!isset($_SESSION['admin_panel_view'])):
            ?>
            <a href="logout.php" class="logout-link"><i data-lucide="log-out"></i> Güvenli Çıkış</a>
        <?php endif;?>
    </nav>
</aside>

<main class="main-content">
    <div class="top-bar">
        <div class="font-bold">Bildirimler</div>
        <div class="actions">
            <button class="notification-button">
                <i data-lucide="bell"></i>
                <div class="notification-badge">3</div>
            </button>
            <?php
            if (isset($_SESSION['admin_panel_view'])):
                ?>
                <a href="../admin/accessTeamPanel.php?exit=1" class="btn btn-sm"><i data-lucide="arrow-left"></i>Admin Paneline Dön</a>
            <?php endif;?>
        </div>
    </div>

    <div class="content-area">
        <div class="card mb-4 flex justify-between items-center">
            <h2>Toplam <?php echo (int)$totalRows; ?> bildirim</h2>
            <?php if ($unreadTotal > 0): ?>
                <form method="post">
                    <button name="mark_all_read" value="1" class="btn btn-sm"><i data-lucide="check"></i> Tümünü okundu yap</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <?php if (!$notifications): ?>
                <p>Henüz bir bildirimin yok.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Tür</th>
                        <th>Başlık</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($notifications as $n):
                        if ($n['type'] === 'module'){
                            $details = getModule($n['content_id']);
                            $gotoUrl = 'edit_module_content.php?id=' . $details['id'];
                        }
                        elseif ($n['type'] === 'course'){
                            $details = getCourseDetailsById($n['content_id']);
                            $gotoUrl = 'panel.php';

                        }
                        else{
                            echo 'hata';
                            continue;
                        }

                        $rowClass  = $n['is_read'] ? '' : 'row-unread';
                        $badgeClass = $n['action'] === 'approve' ? 'badge-approve' : 'badge-reject';
                        // Hedef URL: kurs sayfası; modül için aynı sayfada module anchor
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo htmlspecialchars($n['type']); ?></td>
                            <td><?php echo htmlspecialchars($details['title'] ?? '—'); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($n['action']); ?></span></td>
                            <td class="note-time"><?php echo date('d.m.Y H:i', strtotime($n['notified_at'])); ?></td>
                            <td class="flex gap-2">
                                <a class="btn btn-sm" href="<?= $gotoUrl; ?>">
                                    <i data-lucide="link"></i> <?php echo ($n['type']==='course'?'Kursa Git':'Modüle Git'); ?>
                                </a>
                                <form method="post" class="inline">
                                    <input type="hidden" name="nid" value="<?php echo (int)$n['id']; ?>">
                                    <button class="btn btn-sm" name="toggle_read" value="1">
                                        <?php echo $n['is_read'] ? 'Okunmadı yap' : 'Okundu yap'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>


                <?php if ($totalPages > 1): ?>
                    <div class="mt-4 flex justify-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a class="btn btn-sm" href="?p=<?php echo $page-1; ?>"><i data-lucide="chevron-left"></i> Önceki</a>
                        <?php endif; ?>
                        <span>Sayfa <?php echo $page; ?>/<?php echo $totalPages; ?></span>
                        <?php if ($page < $totalPages): ?>
                            <a class="btn btn-sm" href="?p=<?php echo $page+1; ?>">Sonraki <i data-lucide="chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once '../admin/admin_footer.php'; ?>
