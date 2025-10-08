<?php
// admin_manage.php — Şemanıza uygun sade sürüm

$projectRoot = dirname(__DIR__);
require_once($projectRoot . '/config.php');
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Admin Yönetimi";
$pdo = get_db_connection();

// CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

/**
 * İŞLEMLER
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in_csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $in_csrf)) {
        header('Location: admin_manage.php?fail=csrf'); exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_admin') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username !== '' && $password !== '') {
            try {
                // (Öneri) username'e UNIQUE index ekleyin:
                // ALTER TABLE admins ADD UNIQUE KEY uniq_username (username);
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
                $stmt->execute([$username, $hash]);
                header('Location: admin_manage.php?ok=added&u=' . urlencode($username)); exit();
            } catch (Throwable $e) {
                header('Location: admin_manage.php?fail=dup&u=' . urlencode($username)); exit();
            }
        } else {
            header('Location: admin_manage.php?fail=missing'); exit();
        }
    }

    if ($action === 'edit_admin') {
        $id       = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($id && $username !== '') {
            try {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET username=?, password_hash=? WHERE id=?");
                    $stmt->execute([$username, $hash, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET username=? WHERE id=?");
                    $stmt->execute([$username, $id]);
                }
                header('Location: admin_manage.php?ok=updated&id=' . $id); exit();
            } catch (Throwable $e) {
                header('Location: admin_manage.php?fail=dup&u=' . urlencode($username)); exit();
            }
        } else {
            header('Location: admin_manage.php?fail=missing'); exit();
        }
    }

    if ($action === 'delete_admin') {
        $id = (int)($_POST['id'] ?? 0);
        // İstersen süper admin koruması:
        if ($id && $id !== 1) {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id=?");
            $stmt->execute([$id]);
            header('Location: admin_manage.php?ok=deleted'); exit();
        } else {
            header('Location: admin_manage.php?fail=cannot_delete'); exit();
        }
    }
}

/**
 * LİSTE
 */
$admins = $pdo->query("SELECT id, username, created_at FROM admins ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
require_once 'admin_header.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary-color: #FBBF24;
            --primary-dark-color: #F59E0B;
            --background-color: #F8FAFC;
            --card-background-color: #FFFFFF;
            --text-color: #1F2937;
            --border-color: #E5E7EB;
            --danger-color: #EF4444;
            --info-color: #6B7280;
        }
        body { font-family: 'Inter', sans-serif; background: var(--background-color); color: var(--text-color); line-height:1.6; }
        .main-content { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .card { background: var(--card-background-color); border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,.08); padding: 2rem; transition:.3s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,.1); }
        .btn { background: var(--primary-color); color:#fff; padding:.75rem 1.25rem; border-radius:8px; font-weight:600; display:inline-flex; align-items:center; gap:.5rem; transition:.2s; white-space:nowrap; }
        .btn:hover { background: var(--primary-dark-color); transform: translateY(-1px); }
        .btn-sm { padding:.5rem .875rem; font-size:.875rem; }
        .btn-info { background: var(--info-color); } .btn-info:hover { background:#4B5563; }
        .btn-danger { background: var(--danger-color); } .btn-danger:hover { background:#DC2626; }
        table { width:100%; border-collapse: separate; border-spacing: 0 1rem; }
        thead th { font-weight:700; color:#6B7280; text-transform:uppercase; font-size:.875rem; padding:0 1rem .75rem 1rem; }
        th, td { padding:1rem; text-align:left; vertical-align:middle; }
        tbody tr { background:#fff; box-shadow: 0 2px 4px rgba(0,0,0,.05); border-radius:8px; }
        td:first-child { border-top-left-radius:8px; border-bottom-left-radius:8px; }
        td:last-child  { border-top-right-radius:8px; border-bottom-right-radius:8px; }
        td.actions { display:flex; align-items:center; flex-wrap:wrap; gap:.75rem; }
        input, select { border:1px solid var(--border-color); border-radius:8px; padding:.8rem 1rem; width:100%; }
        input:focus { outline:none; border-color: var(--primary-color); box-shadow:0 0 0 3px rgba(251,191,36,.25); }
        /* Modal */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.6); display:none; justify-content:center; align-items:center; z-index:1000; }
        .modal-overlay.flex { display:flex; }
        .modal-content { background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.2); padding:2rem; max-width:520px; width:92%; position:relative; animation:fadeIn .25s ease-out; }
        .modal-close-btn { position:absolute; top:1rem; right:1rem; background:none; border:none; cursor:pointer; color:#9CA3AF; }
        .modal-close-btn:hover { color:#4B5563; }
        @keyframes fadeIn { from{opacity:0; transform:translateY(-20px);} to{opacity:1; transform:translateY(0);} }
        @media (max-width: 768px) {
            .main-content { padding: 1.5rem 1rem; }
            .card { padding: 1.5rem; }
            table, thead, tbody, th, td, tr { display:block; width:100%; }
            thead tr { position:absolute; top:-9999px; left:-9999px; }
            tr { border:1px solid var(--border-color); border-radius:8px; margin-bottom:1.25rem; padding:.75rem; }
            td { border:none; position:relative; padding:.5rem 0; display:flex; justify-content:space-between; align-items:center; }
            td.actions { justify-content:flex-end; }
        }
    </style>
</head>
<body>

<?php require_once 'admin_sidebar.php'; ?>

<div style="width: 100%" class="page-container">
    <main class="main-content">
        <div class="content-area">
            <div class="page-header mb-8">
                <h1 class="text-3xl font-extrabold text-gray-800">Admin Yönetimi</h1>
                <p class="text-gray-600 mt-1 text-sm">Admin ekleyebilir, düzenleyebilir veya silebilirsiniz.</p>
            </div>

            <!-- Admin Ekle -->
            <div class="card mb-8">
                <form action="admin_manage.php" method="POST" class="grid gap-4 md:grid-cols-3">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="action" value="add_admin">
                    <div>
                        <label class="font-medium text-gray-600">Kullanıcı Adı</label>
                        <input name="username" type="text" required placeholder="ornek: admin">
                    </div>
                    <div>
                        <label class="font-medium text-gray-600">Şifre</label>
                        <input name="password" type="password" required placeholder="••••••••">
                    </div>
                    <div class="flex items-end">
                        <button class="btn w-full md:w-auto"><i data-lucide="user-plus"></i> Admin Oluştur</button>
                    </div>
                </form>
            </div>

            <!-- Admin Listesi -->
            <div class="card">
                <h2 class="text-2xl font-semibold mb-4 text-gray-700">Kayıtlı Adminler</h2>
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($admins)): ?>
                            <tr><td colspan="4" class="text-center text-gray-500 py-8">Henüz admin bulunmuyor.</td></tr>
                        <?php else: foreach($admins as $a): ?>
                            <tr>
                                <td data-label="ID"><strong><?= (int)$a['id'] ?></strong></td>
                                <td data-label="Kullanıcı Adı"><?= htmlspecialchars($a['username']) ?></td>
                                <td data-label="Oluşturulma" class="text-sm text-gray-500"><?= htmlspecialchars($a['created_at']) ?></td>
                                <td data-label="İşlemler" class="actions">
                                    <button
                                            class="btn btn-sm btn-info"
                                            onclick="openEdit(<?= (int)$a['id'] ?>,'<?= htmlspecialchars($a['username'], ENT_QUOTES) ?>')">
                                        <i data-lucide="pencil" class="w-4 h-4"></i> Düzenle
                                    </button>
                                    <?php if ((int)$a['id'] !== 1): ?>
                                        <form action="admin_manage.php" method="POST" onsubmit="return confirm('Bu admin silinsin mi?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="action" value="delete_admin">
                                            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i> Sil
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Düzenleme Modalı -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content">
        <button onclick="closeModal('editModal')" class="modal-close-btn"><i data-lucide="x" class="w-6 h-6"></i></button>
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="user-cog" class="w-6 h-6 text-green-600"></i>
            <h3 class="text-xl font-bold text-gray-800">Admin Düzenle</h3>
        </div>
        <form action="admin_manage.php" method="POST" class="grid gap-3">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="action" value="edit_admin">
            <input type="hidden" name="id" id="edit_id">

            <div>
                <label class="font-medium text-gray-600">Kullanıcı Adı</label>
                <input id="edit_username" name="username" type="text" required>
            </div>
            <div>
                <label class="font-medium text-gray-600">Yeni Şifre (boş bırak ≠ değişmez)</label>
                <input name="password" type="password" placeholder="••••••••">
            </div>

            <div class="flex justify-end gap-2 mt-2">
                <button type="button" onclick="closeModal('editModal')" class="btn btn-sm" style="background:#E5E7EB;color:#111;">
                    <i data-lucide="x" class="w-4 h-4"></i> İptal
                </button>
                <button class="btn btn-sm"><i data-lucide="save" class="w-4 h-4"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php
$ok   = $_GET['ok']   ?? null;
$fail = $_GET['fail'] ?? null;
?>
<?php if ($fail): ?>
    <div id="warningModal" class="modal-overlay flex">
        <div class="modal-content">
            <button onclick="closeModal('warningModal')" class="modal-close-btn"><i data-lucide="x" class="w-6 h-6"></i></button>
            <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-500"></i><h3 class="text-xl font-bold">Uyarı!</h3></div>
            <p class="text-gray-700">
                <?php
                if ($fail === 'dup')          echo 'Bu kullanıcı adı zaten kayıtlı: <strong>'.htmlspecialchars($_GET['u'] ?? '').'</strong>';
                elseif ($fail === 'missing')  echo 'Lütfen tüm alanları doldurun.';
                elseif ($fail === 'csrf')     echo 'Oturum doğrulama hatası (CSRF).';
                elseif ($fail === 'cannot_delete') echo 'Bu admin silinemez.';
                else echo 'İşlem gerçekleştirilemedi.';
                ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php if ($ok): ?>
    <div id="successModal" class="modal-overlay flex">
        <div class="modal-content">
            <button onclick="closeModal('successModal')" class="modal-close-btn"><i data-lucide="x" class="w-6 h-6"></i></button>
            <div class="flex items-center gap-2 mb-2"><i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i><h3 class="text-xl font-bold">Başarılı</h3></div>
            <p class="text-gray-700">
                <?php
                if ($ok === 'added')    echo 'Yeni admin eklendi: <strong>'.htmlspecialchars($_GET['u'] ?? '').'</strong>';
                elseif ($ok === 'updated') echo 'Admin bilgileri güncellendi.';
                elseif ($ok === 'deleted') echo 'Admin silindi.';
                else echo 'İşlem başarıyla tamamlandı.';
                ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'admin_footer.php'; ?>
<script>
    try{ lucide.createIcons(); }catch(e){}

    function closeModal(id){ document.getElementById(id).classList.remove('flex'); }
    function openEdit(id, username){
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('editModal').classList.add('flex');
    }
</script>
</body>
</html>
