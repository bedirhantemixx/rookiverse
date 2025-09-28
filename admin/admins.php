<?php

//bu sayfa henuz calismiyor !!!


$projectRoot = dirname(__DIR__);
require_once($projectRoot . '/config.php');
require_once 'admin_header.php'; // session_start() burada

$page_title = "Admin Yönetimi";
$pdo = get_db_connection();

// Basit CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

/** ------ İŞLEMLER (Add / Edit / Delete) ------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in_csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $in_csrf)) {
        header('Location: admin_manage.php?fail=csrf'); exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_admin') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = trim($_POST['role'] ?? 'admin');
        $pass  = $_POST['password'] ?? '';

        if ($name && $email && $pass) {
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hash, $role]);
                header('Location: admin_manage.php?ok=added&name='.urlencode($name)); exit();
            } catch (Throwable $e) {
                header('Location: admin_manage.php?fail=dup&email='.urlencode($email)); exit();
            }
        } else {
            header('Location: admin_manage.php?fail=missing'); exit();
        }
    }

    if ($action === 'edit_admin') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = trim($_POST['role'] ?? 'admin');
        $pass  = $_POST['password'] ?? '';

        if ($id && $name && $email) {
            try {
                if ($pass !== '') {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET name=?, email=?, role=?, password_hash=? WHERE id=?");
                    $stmt->execute([$name, $email, $role, $hash, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET name=?, email=?, role=? WHERE id=?");
                    $stmt->execute([$name, $email, $role, $id]);
                }
                header('Location: admin_manage.php?ok=updated&id='.$id); exit();
            } catch (Throwable $e) {
                header('Location: admin_manage.php?fail=dup&email='.urlencode($email)); exit();
            }
        } else {
            header('Location: admin_manage.php?fail=missing'); exit();
        }
    }

    if ($action === 'delete_admin') {
        $id = (int)($_POST['id'] ?? 0);
        // Örnek: id=1 süper admin silinemez
        if ($id && $id !== 1) {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id=?");
            $stmt->execute([$id]);
            header('Location: admin_manage.php?ok=deleted'); exit();
        } else {
            header('Location: admin_manage.php?fail=cannot_delete'); exit();
        }
    }
}

/** ------ LİSTE ------ */
$admins = $pdo->query("SELECT id, username, created_at FROM admins ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary-color: #FBBF24;
            --primary-dark-color: #F59E0B;
            --secondary-color: #2DD4BF;
            --background-color: #F8FAFC;
            --card-background-color: #FFFFFF;
            --text-color: #1F2937;
            --border-color: #E5E7EB;
            --danger-color: #EF4444;
            --info-color: #6B7280;
        }
        body { font-family: 'Inter', sans-serif; background: var(--background-color); color: var(--text-color); line-height:1.6; }
        .main-content { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .card { background: var(--card-background-color); border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,.08); padding: 2rem; transition:.3s; position:relative; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,.1); }
        .btn { background: var(--primary-color); color:#fff; padding:.75rem 1.5rem; border-radius:8px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; transition:.2s; white-space:nowrap; }
        .btn:hover { background: var(--primary-dark-color); transform: translateY(-2px); }
        .btn-sm { padding:.5rem 1rem; font-size:.875rem; }
        .btn-info { background: var(--info-color); } .btn-info:hover { background:#4B5563; }
        .btn-danger { background: var(--danger-color); } .btn-danger:hover { background:#DC2626; }
        table { width:100%; border-collapse: separate; border-spacing: 0 1rem; }
        thead th { font-weight:700; color:#6B7280; text-transform:uppercase; font-size:.875rem; padding:0 1rem .75rem 1rem; }
        th, td { padding:1rem; text-align:left; vertical-align:middle; }
        tbody tr { background: var(--card-background-color); box-shadow: 0 2px 4px rgba(0,0,0,.05); border-radius:8px; }
        td:first-child { border-top-left-radius:8px; border-bottom-left-radius:8px; }
        td:last-child  { border-top-right-radius:8px; border-bottom-right-radius:8px; }
        td.actions { display:flex; align-items:center; flex-wrap:wrap; gap:.75rem; }
        td.actions form { margin:0; }
        input, select { border:1px solid var(--border-color); border-radius:8px; padding:.875rem 1.25rem; width:100%; transition:border-color .3s; }
        input:focus, select:focus { outline:none; border-color: var(--primary-color); box-shadow:0 0 0 3px rgba(251,191,36,.25); }

        /* Modal */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.6); display:none; justify-content:center; align-items:center; z-index:1000; }
        .modal-overlay.flex { display:flex; }
        .modal-content { background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.2); padding:2.5rem; max-width:520px; width:92%; position:relative; border-style:dashed; border-width:2px; animation:fadeIn .3s ease-out; }
        .modal-close-btn { position:absolute; top:1rem; right:1rem; background:none; border:none; cursor:pointer; color:#9CA3AF; }
        .modal-close-btn:hover { color:#4B5563; }
        @keyframes fadeIn { from{opacity:0; transform:translateY(-20px);} to{opacity:1; transform:translateY(0);} }

        .copy-icon { cursor:pointer; color:#6B7280; transition:color .2s; margin-left:.5rem; }
        .copy-icon:hover { color: var(--primary-dark-color); }

        @media (max-width: 768px) {
            .main-content { padding: 1.5rem 1rem; }
            .card { padding: 1.5rem; }
            table, thead, tbody, th, td, tr { display:block; width:100%; }
            thead tr { position:absolute; top:-9999px; left:-9999px; }
            tr { border:1px solid var(--border-color); border-radius:8px; margin-bottom:1.5rem; padding:1rem; background:#fff; }
            td { border:none; position:relative; padding:.5rem 0; display:flex; justify-content:space-between; align-items:center; }
            td:before { content:attr(data-label); font-weight:bold; text-transform:uppercase; color:#6B7280; margin-right:1rem; }
            td.actions:before { display:none; }
            .modal-content { padding:1.5rem; }
        }
    </style>
</head>
<body>

<?php require_once 'admin_sidebar.php'; ?>

<div class="page-container">
    <main class="main-content">
        <div class="content-area">
            <div class="page-header mb-8 flex flex-col">
                <h1 class="text-4xl font-extrabold text-gray-800">Admin Yönetimi</h1>
                <h2 class="text-gray-600 mt-2 text-sm">Admin ekleyebilir, düzenleyebilir veya silebilirsiniz.</h2>
            </div>

            <!-- Admin Ekle -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="card lg:col-span-3 flex flex-col md:flex-row md:items-end gap-6">
                    <form action="admin_manage.php" method="POST" class="flex-grow grid gap-4 md:grid-cols-4 w-full">
                        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                        <input type="hidden" name="action" value="add_admin">

                        <div class="md:col-span-1">
                            <label class="font-medium text-gray-600">Ad Soyad</label>
                            <input name="name" type="text" required placeholder="Örn: Ada Lovelace">
                        </div>
                        <div class="md:col-span-1">
                            <label class="font-medium text-gray-600">E-posta</label>
                            <input name="email" type="email" required placeholder="admin@example.com">
                        </div>
                        <div class="md:col-span-1">
                            <label class="font-medium text-gray-600">Rol</label>
                            <select name="role">
                                <option value="admin">admin</option>
                                <option value="moderator">moderator</option>
                                <option value="superadmin">superadmin</option>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="font-medium text-gray-600">Şifre</label>
                            <input name="password" type="password" required placeholder="••••••••">
                        </div>

                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" class="btn h-14 md:w-auto w-full">
                                <i data-lucide="user-plus" class="mr-2"></i> Admin Oluştur
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Admin Listesi -->
            <div class="card">
                <h2 class="text-2xl font-semibold mb-6 text-gray-700">Kayıtlı Adminler</h2>
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($admins)): ?>
                            <tr><td colspan="6" class="text-center text-gray-500 py-8">Henüz admin bulunmuyor.</td></tr>
                        <?php else: foreach($admins as $a): ?>
                            <tr>
                                <td data-label="ID"><strong><?php echo (int)$a['id']; ?></strong></td>
                                <td data-label="Ad"><?php echo htmlspecialchars($a['username']); ?></td>
                                <td data-label="Oluşturulma" class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($a['created_at']); ?>
                                </td>
                                <td data-label="İşlemler" class="actions justify-end">
                                    <button
                                        class="btn btn-sm btn-info"
                                        onclick="openEdit(<?php echo (int)$a['id']; ?>,'<?php echo htmlspecialchars($a['username'], ENT_QUOTES); ?>','<?php echo htmlspecialchars($a['email'], ENT_QUOTES); ?>','<?php echo htmlspecialchars($a['role'] ?: 'admin', ENT_QUOTES); ?>')">
                                        <i data-lucide="pencil" class="w-4 h-4 mr-2"></i>Düzenle
                                    </button>

                                    <?php if ((int)$a['id'] !== 1): ?>
                                        <form action="admin_manage.php" method="POST" onsubmit="return confirm('Bu admini silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                                            <input type="hidden" name="action" value="delete_admin">
                                            <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>Sil
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
    <div class="modal-content modal-alert-success">
        <button onclick="closeModal('editModal')" class="modal-close-btn">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="user-cog" class="w-6 h-6 text-green-600"></i>
            <h3 class="text-xl font-bold text-gray-800">Admin Düzenle</h3>
        </div>
        <form action="admin_manage.php" method="POST" class="grid gap-3">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
            <input type="hidden" name="action" value="edit_admin">
            <input type="hidden" name="id" id="edit_id">

            <div>
                <label class="font-medium text-gray-600">Ad Soyad</label>
                <input id="edit_name" name="name" type="text" required>
            </div>
            <div>
                <label class="font-medium text-gray-600">E-posta</label>
                <input id="edit_email" name="email" type="email" required>
            </div>
            <div>
                <label class="font-medium text-gray-600">Rol</label>
                <select id="edit_role" name="role">
                    <option value="admin">admin</option>
                    <option value="moderator">moderator</option>
                    <option value="superadmin">superadmin</option>
                </select>
            </div>
            <div>
                <label class="font-medium text-gray-600">Yeni Şifre (boş bırakırsan değişmez)</label>
                <input name="password" type="password" placeholder="••••••••">
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" onclick="closeModal('editModal')" class="btn btn-sm" style="background:#E5E7EB;color:#111;"><i data-lucide="x" class="mr-1 w-4 h-4"></i>İptal</button>
                <button class="btn btn-sm"><i data-lucide="save" class="mr-1 w-4 h-4"></i>Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php
// Basit geri bildirim modalları
$ok   = $_GET['ok']   ?? null;
$fail = $_GET['fail'] ?? null;
?>
<?php if ($fail): ?>
    <div id="warningModal" class="modal-overlay flex">
        <div class="modal-content modal-alert-warning">
            <button onclick="closeModal('warningModal')" class="modal-close-btn"><i data-lucide="x" class="w-6 h-6"></i></button>
            <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-500"></i><h3 class="text-xl font-bold">Uyarı!</h3></div>
            <p class="text-gray-600">
                <?php
                if ($fail === 'dup')      echo 'Bu e-posta zaten kayıtlı: <strong>'.htmlspecialchars($_GET['email'] ?? '').'</strong>';
                elseif ($fail === 'missing') echo 'Lütfen tüm gerekli alanları doldurun.';
                elseif ($fail === 'csrf')    echo 'Oturum doğrulama hatası (CSRF).';
                elseif ($fail === 'cannot_delete') echo 'Bu admin silinemez.';
                else echo 'İşlem gerçekleştirilemedi.';
                ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php if ($ok): ?>
    <div id="successModal" class="modal-overlay flex">
        <div class="modal-content modal-alert-success">
            <button onclick="closeModal('successModal')" class="modal-close-btn"><i data-lucide="x" class="w-6 h-6"></i></button>
            <div class="flex items-center gap-2 mb-2"><i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i><h3 class="text-xl font-bold">Başarılı</h3></div>
            <p class="text-gray-600">
                <?php
                if ($ok === 'added')   echo 'Yeni admin eklendi: <strong>'.htmlspecialchars($_GET['name'] ?? '').'</strong>';
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
    lucide.createIcons();

    function closeModal(id){ document.getElementById(id).classList.remove('flex'); }

    function openEdit(id, name, email, role){
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role').value = role || 'admin';
        document.getElementById('editModal').classList.add('flex');
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text)
            .then(()=>alert('Panoya kopyalandı: ' + text))
            .catch(()=>alert('Kopyalama başarısız.'));
    }
</script>
</body>
</html>
