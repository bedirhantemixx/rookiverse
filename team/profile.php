<?php
session_start();
$projectRoot = dirname(__DIR__); // Örn: C:\xampp\htdocs\rookiverse\rookiverse
require_once $projectRoot . '/config.php';
require_once __DIR__ . '/team_header.php'; // Stil ve <head>

if (!isset($_SESSION['team_logged_in'])) {
    $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    header('Location: ' . $base . '/team-login.php');
    exit();
}

$pdo    = get_db_connection();
$teamId = (int) ($_SESSION['team_db_id'] ?? 0);

// CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

$flash_success = null;
$flash_error   = null;

// Mevcut profil verilerini çek
$team = [
    'display_name'  => '',
    'contact_email' => '',
    'website'       => '',
    'bio'           => '',
    'avatar'        => '',
];

try {
    $stmt = $pdo->prepare("SELECT id, display_name, contact_email, website, bio, avatar FROM teams WHERE id = ? LIMIT 1");
    $stmt->execute([$teamId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $team = array_merge($team, $row);
    } else {
        $flash_error = 'Takım profili bulunamadı.';
    }
} catch (PDOException $e) {
    $flash_error = 'Veri okunamadı: ' . $e->getMessage();
}

// POST işlemi (güncelle)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in_csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $in_csrf)) {
        $flash_error = 'Oturum doğrulama (CSRF) başarısız.';
    } else {
        $display_name  = trim($_POST['display_name'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $website       = trim($_POST['website'] ?? '');
        $bio           = trim($_POST['bio'] ?? '');

        if ($display_name === '') {
            $flash_error = 'Görünen ad boş olamaz.';
        } elseif ($contact_email !== '' && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $flash_error = 'E-posta adresi geçersiz.';
        } elseif ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            $flash_error = 'Web sitesi adresi geçersiz.';
        } else {
            // Avatar yükleme (isteğe bağlı)
            $avatarRel = $team['avatar'] ?? '';
            if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $tmp  = $_FILES['avatar']['tmp_name'];
                    $size = (int) ($_FILES['avatar']['size'] ?? 0);

                    // Boyut ve MIME kontrolü
                    if ($size > 5 * 1024 * 1024) { // 5MB limit
                        $flash_error = 'Avatar 5MB sınırını aşıyor.';
                    } else {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime  = $finfo->file($tmp);
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/png'  => 'png',
                            'image/webp' => 'webp',
                        ];
                        if (!isset($allowed[$mime])) {
                            $flash_error = 'Avatar yalnızca JPG, PNG veya WEBP olabilir.';
                        } else {
                            $ext   = $allowed[$mime];
                            $dirFs = rtrim(str_replace('\\', '/', $projectRoot), '/') . '/uploads/team_' . $teamId . '/';
                            $dirRel = 'uploads/team_' . $teamId . '/';
                            if (!is_dir($dirFs)) {
                                @mkdir($dirFs, 0775, true);
                            }
                            if (is_dir($dirFs) && is_writable($dirFs)) {
                                $filename = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                                $destFs   = $dirFs . $filename;
                                if (@move_uploaded_file($tmp, $destFs)) {
                                    $avatarRel = $dirRel . $filename;
                                } else {
                                    $flash_error = 'Avatar yüklenemedi. Klasör izinlerini kontrol edin.';
                                }
                            } else {
                                $flash_error = 'Yükleme klasörü oluşturulamadı veya yazılamıyor.';
                            }
                        }
                    }
                } else {
                    $flash_error = 'Avatar yükleme hatası (kod: ' . (int)$_FILES['avatar']['error'] . ').';
                }
            }

            // Hata yoksa güncelle
            if ($flash_error === null) {
                try {
                    $upd = $pdo->prepare("UPDATE teams SET display_name = :d, contact_email = :e, website = :w, bio = :b, avatar = :a WHERE id = :id");
                    $upd->execute([
                        ':d'  => $display_name,
                        ':e'  => $contact_email,
                        ':w'  => $website,
                        ':b'  => $bio,
                        ':a'  => $avatarRel,
                        ':id' => $teamId,
                    ]);
                    $flash_success = 'Profil başarıyla güncellendi.';
                    // Ekranı güncel verilerle yenile
                    $team['display_name']  = $display_name;
                    $team['contact_email'] = $contact_email;
                    $team['website']       = $website;
                    $team['bio']           = $bio;
                    $team['avatar']        = $avatarRel;
                } catch (PDOException $e) {
                    $flash_error = 'Güncelleme başarısız: ' . $e->getMessage();
                }
            }
        }
    }
}

$page_title = 'Profilimi Düzenle';
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$avatarUrl = $team['avatar'] ? ($base . '/' . ltrim($team['avatar'], '/')) : ($base . '/assets/images/default-avatar.png');
$teamNumber = htmlspecialchars((string)($_SESSION['team_number'] ?? ''));
?>

<style>
    /* Form stilleri: mevcut palete uyumlu */
    .form-card { display:flex; gap:24px; }
    .form-main { flex: 1 1 0; }
    .form-aside { width:320px; max-width:100%; }

    .form-grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
    .form-grid .col-span-2{ grid-column: span 2; }

    .field{ display:flex; flex-direction:column; gap:8px; }
    .label{ font-weight:700; font-size:14px; }
    .input, .textarea, .file{ width:100%; background:#fff; color:var(--text); border:1px solid var(--border); border-radius:12px; padding:10px 12px; }
    .textarea{ min-height:120px; resize:vertical; }
    .input:focus, .textarea:focus, .file:focus{ outline:none; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(229,174,50,.15); }
    .help{ font-size:12px; color:var(--muted); }

    .avatar-card{ display:flex; gap:16px; align-items:center; background:#fff; border:1px solid var(--border); border-radius:16px; padding:16px; }
    .avatar{ width:84px; height:84px; border-radius:18px; object-fit:cover; border:1px solid var(--border); background:#fff; }

    .actions{ display:flex; gap:12px; align-items:center; }
    .badge{ display:inline-flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid var(--border); border-radius:14px; background:var(--chip); font-weight:600; }

    .alert{ padding:12px 14px; border-radius:12px; border:1px solid var(--border); margin-bottom:16px; }
    .alert.success{ background:#f0fdf4; }
    .alert.error{ background:#fef2f2; }
</style>

<aside class="sidebar">
    <div class="sidebar-header"><a href="panel.php"><span class="rookieverse">TAKIM PANELİ</span></a></div>
    <div class="sidebar-profile">
        <div class="icon"><i data-lucide="users"></i></div>
        <h2>Hoş Geldin,</h2>
        <p>Takım #<?php echo $teamNumber; ?></p>
    </div>
    <nav class="sidebar-nav">
        <a href="panel.php"><i data-lucide="layout-dashboard"></i> Panelim</a>
        <a href="create_course.php"><i data-lucide="plus-square"></i> Yeni Kurs Oluştur</a>
        <a href="profile.php" class="active"><i data-lucide="settings"></i> Profilimi Düzenle</a>
        <a href="logout.php" class="logout-link"><i data-lucide="log-out"></i> Güvenli Çıkış</a>
    </nav>
</aside>

<main class="main-content">
    <div class="top-bar">
        <div class="font-bold">Takım #<?php echo $teamNumber; ?> Profil Ayarları</div>
        <div class="actions">
            <span class="badge"><i data-lucide="shield-check"></i> Güvenli Bölge</span>
            <a href="panel.php" class="btn btn-sm"><i data-lucide="arrow-left"></i> Panele Dön</a>
        </div>
    </div>

    <div class="content-area">
        <div class="page-header"><h1>Profilimi Düzenle</h1></div>

        <?php if ($flash_success): ?>
            <div class="alert success"><strong>Başarılı:</strong> <?php echo htmlspecialchars($flash_success); ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert error"><strong>Hata:</strong> <?php echo htmlspecialchars($flash_error); ?></div>
        <?php endif; ?>

        <form class="card form-card" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">

            <div class="form-main">
                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="display_name">Görünen Ad</label>
                        <input class="input" type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($team['display_name'] ?? ''); ?>" required>
                    </div>

                    <div class="field">
                        <label class="label" for="contact_email">İletişim E-postası</label>
                        <input class="input" type="email" id="contact_email" name="contact_email" placeholder="ornek@takim.com" value="<?php echo htmlspecialchars($team['contact_email'] ?? ''); ?>">
                    </div>

                    <div class="field col-span-2">
                        <label class="label" for="website">Web Sitesi</label>
                        <input class="input" type="url" id="website" name="website" placeholder="https://takimim.com" value="<?php echo htmlspecialchars($team['website'] ?? ''); ?>">
                    </div>

                    <div class="field col-span-2">
                        <label class="label" for="bio">Kısa Tanım / Biyografi</label>
                        <textarea class="textarea" id="bio" name="bio" placeholder="Takımınız hakkında kısa bir açıklama yazın..." maxlength="1000"><?php echo htmlspecialchars($team['bio'] ?? ''); ?></textarea>
                        <span class="help">En fazla 1000 karakter önerilir.</span>
                    </div>
                </div>

                <div class="actions" style="margin-top:16px;">
                    <button type="submit" class="btn"><i data-lucide="save"></i> Değişiklikleri Kaydet</button>
                    <a href="panel.php" class="btn btn-sm" style="background:#fff; border-color:var(--border);">İptal</a>
                </div>
            </div>

            <div class="form-aside">
                <div class="avatar-card">
                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>" class="avatar" alt="Avatar">
                    <div style="flex:1 1 0;">
                        <div class="field">
                            <label class="label" for="avatar">Takım Avatarı</label>
                            <input class="file" type="file" id="avatar" name="avatar" accept="image/*">
                            <span class="help">JPG, PNG veya WEBP (max 5MB)</span>
                        </div>
                    </div>
                </div>
                <div class="card" style="margin-top:16px;">
                    <div class="field">
                        <label class="label">Takım Numarası</label>
                        <input class="input" value="<?php echo $teamNumber; ?>" disabled>
                        <span class="help">Bu alan sistem tarafından belirlenir.</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    // Lucide ikonları çiz
    try { if (window.lucide?.createIcons) lucide.createIcons(); } catch(e){}
</script>

<?php require_once __DIR__ . '/../admin/admin_footer.php'; ?>
