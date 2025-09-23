<?php
session_start();

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/config.php';

if (!isset($_SESSION['team_logged_in'])) {
    $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    header('Location: ' . $base . '/team-login.php');
    exit();
}

$pdo    = get_db_connection();
$teamId = (int) ($_SESSION['team_db_id'] ?? 0);
if ($teamId <= 0) { die('Geçersiz takım oturumu.'); }

/** Yardımcılar **/
function get_table_columns(PDO $pdo, string $table): array {
    $cols = [];
    try {
        $q = $pdo->query("DESCRIBE `$table`");
        foreach ($q as $row) { $cols[] = $row['Field']; }
    } catch (Throwable $e) { /* sessiz geç */ }
    return $cols;
}
function pick_col(array $columns, array $candidates): ?string {
    foreach ($candidates as $c) if (in_array($c, $columns, true)) return $c;
    return null;
}
function normalize_url(?string $u): string {
    $u = trim((string)$u);
    if ($u === '') return '';
    // Şema yoksa https ekle
    if (!preg_match('~^https?://~i', $u)) $u = 'https://' . $u;
    return $u;
}

/** Mevcut takım verisini çek (getTeam varsa onu kullan) **/
if (function_exists('getTeam')) {
    $team = getTeam($teamId);
} else {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ? LIMIT 1");
    $stmt->execute([$teamId]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

$flash_success = null;
$flash_error   = null;

/** POST: Güncelle **/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in_csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $in_csrf)) {
        $flash_error = 'Oturum doğrulama (CSRF) başarısız.';
    } else {
        // Form alanlarını al + normalize et
        $display_name  = trim($_POST['display_name']  ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $website       = normalize_url($_POST['website']       ?? '');
        $bio           = trim($_POST['bio'] ?? '');
        $instagram_url = normalize_url($_POST['instagram_url'] ?? '');
        $youtube_url   = normalize_url($_POST['youtube_url']   ?? '');
        $linkedin_url  = normalize_url($_POST['linkedin_url']  ?? '');

        // Basit validasyon
        if ($display_name === '') {
            $flash_error = 'Görünen ad boş olamaz.';
        } elseif ($contact_email !== '' && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $flash_error = 'E-posta adresi geçersiz.';
        } elseif ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            $flash_error = 'Web sitesi adresi geçersiz.';
        } elseif ($instagram_url !== '' && !filter_var($instagram_url, FILTER_VALIDATE_URL)) {
            $flash_error = 'Instagram linki geçersiz.';
        } elseif ($youtube_url !== '' && !filter_var($youtube_url, FILTER_VALIDATE_URL)) {
            $flash_error = 'YouTube linki geçersiz.';
        } elseif ($linkedin_url !== '' && !filter_var($linkedin_url, FILTER_VALIDATE_URL)) {
            $flash_error = 'LinkedIn linki geçersiz.';
        }

        // Logo upload (isteğe bağlı)
        $logoRel = $team['logo'] ?? ($team['profile_pic_path'] ?? '');
        if ($flash_error === null && !empty($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $tmp  = $_FILES['logo']['tmp_name'];
                $size = (int) ($_FILES['logo']['size'] ?? 0);
                if ($size > 5 * 1024 * 1024) {
                    $flash_error = 'Logo 5MB sınırını aşıyor.';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime  = $finfo->file($tmp);
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                    if (!isset($allowed[$mime])) {
                        $flash_error = 'Logo yalnızca JPG, PNG veya WEBP olabilir.';
                    } else {
                        $ext   = $allowed[$mime];
                        $dirFs = rtrim(str_replace('\\','/', $projectRoot), '/') . '/uploads/team_' . $teamId . '/';
                        $dirRel= 'uploads/team_' . $teamId . '/';
                        if (!is_dir($dirFs)) @mkdir($dirFs, 0775, true);
                        if (!is_dir($dirFs) || !is_writable($dirFs)) {
                            $flash_error = 'Yükleme klasörü oluşturulamadı veya yazılamıyor.';
                        } else {
                            $filename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                            $destFs   = $dirFs . $filename;
                            if (@move_uploaded_file($tmp, $destFs)) {
                                $logoRel = $dirRel . $filename;
                            } else {
                                $flash_error = 'Logo yüklenemedi. Klasör izinlerini kontrol edin.';
                            }
                        }
                    }
                }
            } else {
                $flash_error = 'Logo yükleme hatası (kod: ' . (int)$_FILES['logo']['error'] . ').';
            }
        }
        echo $flash_error;
        // DB güncelle (kolonları dinamik eşle)
        if ($flash_error === null) {
            echo 'tuf';

            $cols = get_table_columns($pdo, 'teams');

            $map = [
                // form alanı => db kolon adayları (öncelik sırasıyla)
                'display_name'  => ['display_name', 'team_name', 'name'],
                'contact_email' => ['contact_email', 'email'],
                'website'       => ['website', 'site'],
                'bio'           => ['bio', 'about', 'description'],
                'instagram_url' => ['instagram_url', 'instagram'],
                'youtube_url'   => ['youtube_url', 'youtube'],
                'linkedin_url'  => ['linkedin_url', 'linkedin'],
                'logo'          => ['logo', 'profile_pic_path', 'avatar'],
            ];

            $updates = [];
            $params  = [':id' => $teamId];

            // display_name
            if ($col = pick_col($cols, $map['display_name']))  { $updates[] = "`$col` = :d";  $params[':d']  = $display_name; }
            // contact_email
            if ($col = pick_col($cols, $map['contact_email'])) { $updates[] = "`$col` = :e";  $params[':e']  = $contact_email; }
            // website
            if ($col = pick_col($cols, $map['website']))       { $updates[] = "`$col` = :w";  $params[':w']  = $website; }
            // bio
            if ($col = pick_col($cols, $map['bio']))           { $updates[] = "`$col` = :b";  $params[':b']  = $bio; }
            // instagram
            if ($col = pick_col($cols, $map['instagram_url'])) { $updates[] = "`$col` = :iu"; $params[':iu'] = $instagram_url; }
            // youtube
            if ($col = pick_col($cols, $map['youtube_url']))   { $updates[] = "`$col` = :yu"; $params[':yu'] = $youtube_url; }
            // linkedin
            if ($col = pick_col($cols, $map['linkedin_url']))  { $updates[] = "`$col` = :li"; $params[':li'] = $linkedin_url; }
            // logo
            if ($logoRel !== '' && ($col = pick_col($cols, $map['logo']))) {
                $updates[] = "`$col` = :l"; $params[':l'] = $logoRel;
            }
            if (!empty($updates)) {
                $sql = "UPDATE `teams` SET " . implode(', ', $updates) . " WHERE id = :id";
                try {
                    $upd = $pdo->prepare($sql);
                    $upd->execute($params);
                    $flash_success = 'Profil başarıyla güncellendi.';

                    // Güncel veriyi tekrar çek
                    if (function_exists('getTeam')) {
                        $team = getTeam($teamId);
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ? LIMIT 1");
                        $stmt->execute([$teamId]);
                        $team = $stmt->fetch(PDO::FETCH_ASSOC) ?: $team;
                    }
                } catch (PDOException $e) {
                    $flash_error = 'Güncelleme başarısız: ' . $e->getMessage();
                }
            } else {
                $flash_error = 'Güncellenecek uygun kolon bulunamadı. (teams şemanı kontrol et)';
            }
        }
    }
}

/** Sayfa altındaki görseller ve placeholderlar için yardımcı değişkenler **/
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
// Logo/pp alanı hem `logo` hem `profile_pic_path` olabilir:
$logoPath = $team['logo'] ?? ($team['profile_pic_path'] ?? '');
$logoUrl  = $logoPath ? ($base . '/' . ltrim($logoPath, '/')) : ($base . '/assets/images/default.jpg'); // .jgp düzeltildi
$teamNumber = htmlspecialchars((string) ($_SESSION['team_number'] ?? ''));
?>
