<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }

$projectRoot = dirname(__DIR__);
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

/* =========================
   Yardımcılar
   ========================= */

function ensure_course_dir(int $course_id): array {
    $dirFs  = rtrim(dirname(__DIR__), '/\\') . '/uploads/course_' . $course_id . '/';
    $dirRel = 'uploads/course_' . $course_id . '/';

    if (!is_dir($dirFs)) {
        // 0775 yeterli; umask etkisi için geçici olarak düşür.
        $old = umask(0);
        if (!mkdir($dirFs, 0775, true) && !is_dir($dirFs)) {
            umask($old);
            throw new RuntimeException("Klasör oluşturulamadı: $dirFs");
        }
        umask($old);
    }
    if (!is_writable($dirFs)) {
        throw new RuntimeException("Yazma izni yok: $dirFs (web sunucu kullanıcısına yazma izni ver)");
    }
    return [$dirFs, $dirRel];
}

/** Basit ama güvenli dosya yükleyici */
function upload_file(array $file, int $course_id, array $allowedMimes, int $maxBytes): ?string {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null; // opsiyonel
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Dosya yükleme hatası (code {$file['error']}).");
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException("Dosya çok büyük (limit: " . round($maxBytes/1024/1024) . " MB).");
    }

    // Gerçek MIME tespiti
    $fi = new finfo(FILEINFO_MIME_TYPE);
    $mime = $fi->file($file['tmp_name']) ?: '';
    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException("İzin verilmeyen dosya türü ($mime).");
    }

    // Hedef klasör
    [$dirFs, $dirRel] = ensure_course_dir($course_id);

    // Güvenli dosya adı
    $origName = $file['name'] ?? 'file';
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $unique = date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    $safeName = $unique . ($ext ? ('.' . $ext) : '');

    $destFs  = $dirFs . $safeName;
    $destRel = $dirRel . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destFs)) {
        throw new RuntimeException("Dosya taşınamadı.");
    }

    // Ek güvenlik: 0644
    @chmod($destFs, 0644);

    return $destRel; // veritabanına göreli path yazıyoruz
}

/** Basit script-tag temizliği (ileri seviye: HTMLPurifier) */
function sanitize_html_basic(string $html): string {
    // <script> taglarını ve inline event’leri kaba şekilde temizle
    $html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html) ?? $html;
    // on*="..." eventleri kaba temizlik (opsiyonel)
    $html = preg_replace('/\son\w+="[^"]*"/i', '', $html) ?? $html;
    $html = preg_replace("/\son\w+='[^']*'/i", '', $html) ?? $html;
    return $html;
}

/* =========================
   İş mantığı
   ========================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
$title     = trim($_POST['title'] ?? '');
$text_body = $_POST['text_body'] ?? ''; // HTML gelecek

if ($course_id <= 0 || $title === '') {
    exit('Eksik parametre: course_id ve title zorunludur.');
}

// Kurs takıma ait mi?
$team_id = (int)($_SESSION['team_db_id'] ?? 0);
$stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND team_db_id = ?");
$stmt->execute([$course_id, $team_id]);
if (!$stmt->fetchColumn()) {
    exit('Yetkisiz işlem veya kurs bulunamadı.');
}

// Dosya türü izinleri (ihtiyaca göre genişletebilirsin)
$allowedDocMimes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];
$allowedVideoMimes = [
    'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/webm', 'video/ogg'
];

// Boyut limitleri (gereğine göre ayarla)
$maxDocBytes   = 50 * 1024 * 1024;   // 50 MB
$maxVideoBytes = 700 * 1024 * 1024;  // 700 MB

// Metni temelce temizle (script çıkar)
$text_body_clean = sanitize_html_basic($text_body);

// Yüklemeleri işle (opsiyonel olduklarından null olabilir)
$video_path = null;
$doc_path   = null;

try {
    if (!empty($_FILES['video_file']) && is_uploaded_file($_FILES['video_file']['tmp_name'])) {
        $video_path = upload_file($_FILES['video_file'], $course_id, $allowedVideoMimes, $maxVideoBytes);
    }
    if (!empty($_FILES['document_file']) && is_uploaded_file($_FILES['document_file']['tmp_name'])) {
        $doc_path = upload_file($_FILES['document_file'], $course_id, $allowedDocMimes, $maxDocBytes);
    }
} catch (Throwable $e) {
    // Dosya hatasında kullanıcıya anlaşılır bir mesaj göster, istersen logla
    exit('Dosya yükleme hatası: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

try {
    $pdo->beginTransaction();

    // 1) Bölüm (module) oluştur
    $stmt = $pdo->prepare("INSERT INTO course_modules (course_id, title) VALUES (?, ?)");
    $stmt->execute([$course_id, $title]);
    $module_id = (int)$pdo->lastInsertId();

    // 2) İçerik satırı ekle (tek parça)
    $stmt = $pdo->prepare("
        INSERT INTO module_contents (module_id, title, data, data_file, data_vid, sort_order)
        VALUES (?, ?, ?, ?, ?, 0)
    ");
    $stmt->execute([
        $module_id,
        $title,              // içerik başlığını modul başlığı ile aynı tutuyoruz; istersen '' yap
        $text_body_clean,    // HTML
        $doc_path,           // doküman (nullable)
        $video_path          // video (nullable)
    ]);

    $pdo->commit();

    // Başarılı → geri dön
    header("Location: view_curriculum.php?id=" . $course_id . "&status=module_added");
    exit();
} catch (Throwable $e) {
    $pdo->rollBack();
    // Hata mesajını çok açmadan bildir; detaylar log’a (önerilir)
    exit('Bir hata oluştu, lütfen tekrar deneyin.');
}
