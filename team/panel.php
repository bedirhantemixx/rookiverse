<?php
session_start();

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/config.php';

// 1) Auth
if (empty($_SESSION['team_logged_in'])) {
    header('Location: ../team-login.php');
    exit;
}

// CSRF hazırla
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

$pdo  = get_db_connection();
$team = getTeam($_SESSION['team_number']);

// Takıma ait kursları çek
$stmt = $pdo->prepare("SELECT id, title, status FROM courses WHERE team_db_id = ? AND is_deleted = 0 ORDER BY id DESC");
$stmt->execute([$_SESSION['team_db_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Takım Paneli";

// 2) HTML çıktısı başlatan dosyaları EN SON ekle
// Takıma ait bekleyen remove istekleri (is_resolved=0)
$pendingStmt = $pdo->prepare("
    SELECT content_id 
    FROM team_support 
    WHERE team_id = ? AND type = 'remove' AND is_resolved = 0
");
$pendingStmt->execute([$_SESSION['team_db_id']]);
$pending = array_fill_keys(array_map('intval', $pendingStmt->fetchAll(PDO::FETCH_COLUMN)), true);

require_once __DIR__ . '/team_header.php';
?>
<!-- buradan itibaren HTML -->

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
        color: black;
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
    .menu-label-with-badge{display:inline-flex;align-items:center;gap:8px}
    .menu-badge{
        background:#ef4444;color:#fff;border-radius:9999px;
        font-size:11px;line-height:1;padding:4px 6px;min-height: 15px;
        display:inline-flex;align-items:center;justify-content:center;
        font-weight:600;
    }
    .btn-danger{
        background:#fee2e2;border:1px solid #fecaca;color:#b91c1c;
    }
    .btn-danger:hover{ background:#fecaca; }
    .soft-badge{
        display:inline-flex;align-items:center;gap:6px;
        font-size:12px;color:#374151;background:#f3f4f6;border:1px solid #e5e7eb;
        padding:4px 8px;border-radius:9999px;
    }
</style>


<aside class="sidebar">
    <?php if (!isset($_SESSION['admin_panel_view'])): ?>
        <a class="flex items-center space-x-2" href="<?php echo BASE_URL; ?>">
            <span class="rookieverse">FRC ROOKIEVERSE</span>
        </a>
    <?php endif; ?>
    <div class="sidebar-profile">
        <h2>Hoş Geldin,</h2>
        <p>Takım #<?php echo htmlspecialchars($_SESSION['team_number']); ?></p>
    </div>
    <nav class="sidebar-nav">
        <a href="panel.php" class="active"><i data-lucide="layout-dashboard"></i> Panelim</a>
        <a href="create_course.php"><i data-lucide="plus-square"></i> Yeni Kurs Oluştur</a>
        <a href="profile.php"><i data-lucide="settings"></i> Profilimi Düzenle</a>
        <a href="notifications.php"><i data-lucide="bell"></i> Bildirimler<?php if (!empty($unreadTotal)): ?><span class="menu-badge"><?php echo (int)$unreadTotal; ?></span><?php endif; ?></a>
        <a href="list_questions.php" ><i data-lucide="message-square"></i> Soru Yönetimi</a>
        <?php if (!isset($_SESSION['admin_panel_view'])): ?>
            <a href="logout.php" class="logout-link"><i data-lucide="log-out"></i> Güvenli Çıkış</a>
        <?php endif; ?>
    </nav>
</aside>

<main class="main-content">
    <div class="top-bar">
        <div class="font-bold">Takım #<?php echo (int)$_SESSION['team_number']; ?> Paneli</div>
        <div class="actions">
            <button id="notif-button" class="notification-button">
                <i data-lucide="bell"></i>
                <?php if (!empty($unreadTotal)): ?>
                    <div class="notification-badge"><?= htmlspecialchars($unreadTotal) ?></div>
                <?php endif; ?>
            </button>

            <?php if (isset($_SESSION['admin_panel_view'])): ?>
                <a href="../admin/accessTeamPanel.php?exit=1" class="btn btn-sm"><i data-lucide="arrow-left"></i>Admin Paneline Dön</a>
            <?php endif; ?>
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
                    <thead>
                    <tr>
                        <th>Kurs Başlığı</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr data-course-row-id="<?= (int)$course['id'] ?>">
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                                    <?php echo htmlspecialchars($course['status']); ?>
                                </span>
                                <!-- Silme talebi etiketi dinamik eklenecek -->
                            </td>
                            <td class="space-x-2">
                                <a href="editCourse.php?id=<?= (int)$course['id'] ?>" class="btn btn-sm btn-outline">
                                    <i data-lucide="edit-3"></i> Kursu Düzenle
                                </a>
                                <a href="view_curriculum.php?id=<?= (int)$course['id'] ?>" class="btn btn-sm">
                                    <i data-lucide="list"></i> İçeriği Yönet
                                </a>

                                <!-- YENİ: Kursu Sil (İstek) -->
                                <?php
                                $hasReq = !empty($pending[(int)$course['id']]);
                                ?>
                                <!-- YENİ: Kursu Sil / İsteği Geri Çek (toggle) -->
                                <button
                                        class="btn btn-sm btn-danger delete-request-btn <?= $hasReq ? 'opacity-60' : '' ?>"
                                        data-course-id="<?= (int)$course['id'] ?>"
                                        data-csrf="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>"
                                        data-requested="<?= $hasReq ? '1' : '0' ?>"
                                        type="button">
                                    <i data-lucide="<?= $hasReq ? 'clock' : 'trash-2' ?>"></i>
                                    <span class="btn-text"><?= $hasReq ? 'İsteği Geri Çek' : 'Kursu Sil' ?></span>
                                </button>

                            </td>
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

<script>
        function renderToast(msg, type='info'){ /* … sende zaten var … */ }

        async function sendDeleteRequest(courseId, csrf){
        try{
        const res = await fetch('request_course_delete.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({course_id: courseId, csrf})
    });
        if(!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json().catch(()=>({}));
        return !!data.ok;
    }catch(e){ return false; }
    }

        async function sendCancelRequest(courseId, csrf){
        try{
        const res = await fetch('cancel_course_delete.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({course_id: courseId, csrf})
    });
        if(!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json().catch(()=>({}));
        return !!data.ok;
    }catch(e){ return false; }
    }

        function markRowRequested(row){
        const btn   = row.querySelector('.delete-request-btn');
        const icon  = btn?.querySelector('i.lucide');
        const text  = btn?.querySelector('.btn-text');

        btn?.classList.add('opacity-60','cursor-not-allowed');
        btn?.setAttribute('data-requested','1');
        if (icon) icon.setAttribute('data-lucide','clock');
        if (text) text.textContent = 'İsteği Geri Çek';

    }
        try{ lucide.createIcons(); }catch(_){}

        function unmarkRowRequested(row){
        const btn   = row.querySelector('.delete-request-btn');
        const icon  = btn?.querySelector('i.lucide');
        const text  = btn?.querySelector('.btn-text');

        btn?.classList.remove('opacity-60','cursor-not-allowed');
        btn?.setAttribute('data-requested','0');
        if (icon) icon.setAttribute('data-lucide','trash-2');
        if (text) text.textContent = 'Kursu Sil';

        // Rozeti kaldır
        const badge = row.querySelector('.delete-request-badge');
        badge?.remove();
        try{ lucide.createIcons(); }catch(_){}
    }

        document.querySelectorAll('.delete-request-btn').forEach(btn=>{
        btn.addEventListener('click', async ()=>{
            const row      = btn.closest('tr');
            const courseId = btn.dataset.courseId;
            const csrf     = btn.dataset.csrf;
            const requested = btn.getAttribute('data-requested') === '1';

            // Toggle: eğer zaten istek varsa -> Geri çek; yoksa -> İstek oluştur
            if (!requested){
                // optimistic
                markRowRequested(row);
                renderToast('Silme isteği gönderildi.', 'success');

                const ok = await sendDeleteRequest(courseId, csrf);
                if (!ok){
                    // başarısızsa geri al
                    unmarkRowRequested(row);
                    renderToast('Sunucu hatası: istek oluşturulamadı.', 'error');
                }
            } else {
                // Geri çek
                // optimistic
                unmarkRowRequested(row);
                renderToast('Silme isteği geri çekildi.', 'success');

                const ok = await sendCancelRequest(courseId, csrf);
                if (!ok){
                    // başarısızsa eski haline dön
                    markRowRequested(row);
                    renderToast('Sunucu hatası: istek geri çekilemedi.', 'error');
                }
            }
        });
    });

</script>

<?php require_once '../admin/admin_footer.php'; ?>
