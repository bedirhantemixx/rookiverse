<?php
// File: modules/curriculum/update_module_content.php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }

$projectRoot = $_SERVER['DOCUMENT_ROOT']; // .../projeadi
require_once('../config.php');
$pdo = get_db_connection();

// ==== Ayarlar ====
const DELETE_OLD_FILES_ON_REPLACE = true;   // yeni dosya yüklenince eskisini sil
const DELETE_FILES_ON_ROW_DELETE  = true;   // içerik satırı silinince dosyayı da sil (yalnızca local uploads)

// ---- Helpers ----
function fail($msg, $code = 400) { http_response_code($code); die($msg); }

function ensure_dir(string $path): void {
    if (is_dir($path)) return;
    if (!@mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException("mkdir_failed: $path");
    }
}

function sanitize_filename(string $name): string {
    $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    $name = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $name);
    return trim($name, '._');
}

function detect_mime(string $tmpPath): string {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->file($tmpPath) ?: 'application/octet-stream';
}

function is_allowed_mime(string $mime, string $kind): bool {
    $video = [
        'video/mp4','video/quicktime','video/x-msvideo','video/x-matroska','video/webm','video/ogg'
    ];
    $docs  = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    if ($kind === 'video') return in_array($mime, $video, true) || str_starts_with($mime, 'video/');
    if ($kind === 'document') return in_array($mime, $docs, true) || $mime === 'application/octet-stream';
    return false;
}

function move_uploaded_file_strict(array $file, string $destDir, string $projectRoot): string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('upload_error');
    }
    $tmp  = $file['tmp_name'];
    $name = sanitize_filename($file['name'] ?? ('file_' . bin2hex(random_bytes(6))));
    $ext  = pathinfo($name, PATHINFO_EXTENSION);

    ensure_dir($destDir);

    $base  = pathinfo($name, PATHINFO_FILENAME);
    $final = $base . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
    $destFs = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $final;

    if (!@move_uploaded_file($tmp, $destFs)) {
        throw new RuntimeException('move_failed');
    }
    // proje köküne göre göreli yol
    $rel = ltrim(str_replace($projectRoot, '', $destFs), '/\\');
    return str_replace('\\', '/', $rel);
}

function safe_unlink_if_local(string $projectRoot, string $relPath, int $course_id, int $module_id): void {
    $relPath = ltrim($relPath, '/\\');
    $prefix = "uploads/course_{$course_id}/module_{$module_id}/";
    if (!str_starts_with($relPath, $prefix)) return; // güvenlik: sadece bu klasör altını sil
    $full = $projectRoot . '/' . $relPath;
    if (is_file($full)) @unlink($full);
}

/** NEW: YouTube ID ayrıştırıcı (URL veya çıplak ID kabul eder) */
function parse_youtube_id(?string $input): ?string {
    if (!$input) return null;
    $input = trim($input);

    // çıplak ID (11–20 arası güvenli aralık)
    if (preg_match('/^[A-Za-z0-9_-]{10,20}$/', $input)) {
        return $input;
    }

    // URL gibi gelirse
    $parts = @parse_url($input);
    if (!$parts || empty($parts['host'])) return null;

    $host = strtolower($parts['host']);
    $path = $parts['path'] ?? '';
    $query= [];
    if (!empty($parts['query'])) parse_str($parts['query'], $query);

    // youtu.be/<id>
    if (strpos($host, 'youtu.be') !== false) {
        $segments = array_values(array_filter(explode('/', $path)));
        return $segments[0] ?? null;
    }

    if (strpos($host, 'youtube.com') !== false) {
        // /watch?v=<id>
        if (!empty($query['v'])) return $query['v'];

        // /embed/<id>
        if (preg_match('~/embed/([^/?#]+)~', $path, $m)) return $m[1];

        // /shorts/<id>
        if (preg_match('~/shorts/([^/?#]+)~', $path, $m)) return $m[1];
    }

    return null;
}

// ---- Validate ----
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Geçersiz istek yöntemi.');
$module_id = isset($_POST['module_id']) ? (int)$_POST['module_id'] : 0;
if ($module_id <= 0) fail('module_id eksik');

$contents = $_POST['contents'] ?? [];           // contents[exist_XX|new_YY][...]
$filesBag = $_FILES['contents'] ?? null;        // edit formunda üst key 'contents'
if (!is_array($contents)) $contents = [];

// ---- Ownership check & course_id ----
$own = $pdo->prepare("
    SELECT m.id AS module_id, m.course_id, c.team_db_id
    FROM course_modules m
    JOIN courses c ON c.id = m.course_id
    WHERE m.id = ? AND c.team_db_id = ?
");
$own->execute([$module_id, $_SESSION['team_db_id']]);
$modRow = $own->fetch(PDO::FETCH_ASSOC);
if (!$modRow) fail('Bu modül üzerinde yetkiniz yok.', 403);

$course_id = (int)$modRow['course_id'];
$destDir   = $projectRoot . "/uploads/course_{$course_id}/module_{$module_id}";
ensure_dir($destDir);

// ---- Read existing rows ----
$stmt = $pdo->prepare("SELECT id, type, data FROM module_contents WHERE module_id = ?");
$stmt->execute([$module_id]);
$existingRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentIds = [];
$oldDataById = [];
foreach ($existingRows as $r) {
    $currentIds[] = (int)$r['id'];
    $oldDataById[(int)$r['id']] = ['type' => $r['type'], 'data' => $r['data']];
}

// ---- Build keep-set from POST ----
$postedExistIds = [];
foreach ($contents as $key => $cv) {
    if (isset($cv['id']) && is_numeric($cv['id'])) {
        $postedExistIds[] = (int)$cv['id'];
    }
}

// ---- Compute deletions ----
$toDelete = array_values(array_diff($currentIds, $postedExistIds));

// ---- Transaction ----
try {
    $pdo->beginTransaction();

    // 1) Delete removed rows (and their files optionally)
    if (!empty($toDelete)) {
        if (DELETE_FILES_ON_ROW_DELETE) {
            foreach ($toDelete as $delId) {
                $info = $oldDataById[$delId] ?? null;
                if ($info && $info['type'] !== 'text' && !empty($info['data'])) {
                    // youtube URL'si local path ile başlamayacağından silinmeyecek (safe_unlink_if_local korur)
                    safe_unlink_if_local($projectRoot, $info['data'], $course_id, $module_id);
                }
            }
        }
        $in = implode(',', array_fill(0, count($toDelete), '?'));
        $delStmt = $pdo->prepare("DELETE FROM module_contents WHERE module_id = ? AND id IN ($in)");
        $delStmt->execute(array_merge([$module_id], $toDelete));
    }

    // 2) Upsert loop
    foreach ($contents as $cKey => $cVal) {
        $rawType  = $cVal['type'] ?? null;                  // 'text' | 'video' | 'document' | 'youtube'
        $sort     = isset($cVal['sort_order']) ? (int)$cVal['sort_order'] : 0;
        $para     = $cVal['paragraph'] ?? null;             // text HTML
        $existId  = isset($cVal['id']) ? (int)$cVal['id'] : 0;
        $existing = $cVal['existing_file'] ?? null;         // eski dosya yolu
        $ytRaw    = $cVal['youtube_url'] ?? null;           // youtube

        if (!$rawType) continue;

        // DB tip eşlemesi
        switch ($rawType) {
            case 'document': $dbType = 'doc';      break;
            case 'youtube':  $dbType = 'youtube';  break;   // eski satırlarda 'yt' olabilir; update bu değeri normalize eder
            default:         $dbType = $rawType;
        }

        // --- file array (if any) ---
        $fileArr = null;
        if ($filesBag &&
            isset($filesBag['name'][$cKey]['file'])
        ) {
            $fileArr = [
                'name'     => $filesBag['name'][$cKey]['file'] ?? null,
                'type'     => $filesBag['type'][$cKey]['file'] ?? null,
                'tmp_name' => $filesBag['tmp_name'][$cKey]['file'] ?? null,
                'error'    => $filesBag['error'][$cKey]['file'] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $filesBag['size'][$cKey]['file'] ?? 0,
            ];
        }

        if ($existId > 0) {
            // === UPDATE path ===
            if ($dbType === 'text') {
                $clean = trim((string)$para);
                if ($clean === '') {
                    // boş text gönderildiyse satırı silelim
                    $del = $pdo->prepare("DELETE FROM module_contents WHERE id = ? AND module_id = ?");
                    $del->execute([$existId, $module_id]);
                    continue;
                }
                $up = $pdo->prepare("UPDATE module_contents SET type='text', title=NULL, data=?, sort_order=? WHERE id=? AND module_id=?");
                $up->execute([$para, $sort, $existId, $module_id]);
            }
            elseif ($dbType === 'youtube') {
                $ytRaw = trim((string)$ytRaw);
                if ($ytRaw === '') {
                    // boşsa tamamen sil
                    $del = $pdo->prepare("DELETE FROM module_contents WHERE id = ? AND module_id = ?");
                    $del->execute([$existId, $module_id]);
                    continue;
                }
                $ytId = parse_youtube_id($ytRaw);
                if (!$ytId) {
                    // geçersiz ise satırı olduğu gibi bırakmak istiyorsan continue; (ben sileceğim)
                    $del = $pdo->prepare("DELETE FROM module_contents WHERE id = ? AND module_id = ?");
                    $del->execute([$existId, $module_id]);
                    continue;
                }
                $canon = "https://youtu.be/" . $ytId;
                $up = $pdo->prepare("UPDATE module_contents SET type='youtube', title=NULL, data=?, sort_order=? WHERE id=? AND module_id=?");
                $up->execute([$canon, $sort, $existId, $module_id]);
            }
            else {
                // video/document
                $newPath = null;  // yeni upload path
                if ($fileArr && $fileArr['error'] === UPLOAD_ERR_OK) {
                    $mime = detect_mime($fileArr['tmp_name']);
                    $kind = ($dbType === 'video') ? 'video' : 'document';
                    if (!is_allowed_mime($mime, $kind)) {
                        throw new RuntimeException("Yasaklı dosya türü: $mime ($kind)");
                    }
                    $newPath = move_uploaded_file_strict($fileArr, $destDir, $projectRoot);

                    // eskiyi sil
                    if (DELETE_OLD_FILES_ON_REPLACE) {
                        $old = $oldDataById[$existId]['data'] ?? null;
                        if ($old && trim($old) !== '') {
                            safe_unlink_if_local($projectRoot, $old, $course_id, $module_id);
                        }
                    }
                } else {
                    // yeni dosya yoksa eski path'i koru
                    if ($existing && trim($existing) !== '') {
                        $newPath = trim($existing);
                    } else {
                        // path yoksa -> satırı sil
                        $del = $pdo->prepare("DELETE FROM module_contents WHERE id = ? AND module_id = ?");
                        $del->execute([$existId, $module_id]);
                        continue;
                    }
                }

                $up = $pdo->prepare("UPDATE module_contents SET type=?, title=NULL, data=?, sort_order=? WHERE id=? AND module_id=?");
                $up->execute([$dbType, $newPath, $sort, $existId, $module_id]);
            }

        } else {
            // === INSERT path (new_*) ===
            if ($dbType === 'text') {
                $clean = trim((string)$para);
                if ($clean === '') continue; // boş text ekleme
                $ins = $pdo->prepare("INSERT INTO module_contents (module_id, type, title, data, sort_order) VALUES (?, 'text', NULL, ?, ?)");
                $ins->execute([$module_id, $para, $sort]);
            }
            elseif ($dbType === 'youtube') {
                $ytRaw = trim((string)$ytRaw);
                if ($ytRaw === '') continue;
                $ytId = parse_youtube_id($ytRaw);
                if (!$ytId) continue;
                $canon = "https://youtu.be/" . $ytId;
                $ins = $pdo->prepare("INSERT INTO module_contents (module_id, type, title, data, sort_order) VALUES (?, 'youtube', NULL, ?, ?)");
                $ins->execute([$module_id, $canon, $sort]);
            }
            else {
                $dataToStore = null;

                if ($fileArr && $fileArr['error'] === UPLOAD_ERR_OK) {
                    $mime = detect_mime($fileArr['tmp_name']);
                    $kind = ($dbType === 'video') ? 'video' : 'document';
                    if (!is_allowed_mime($mime, $kind)) {
                        throw new RuntimeException("Yasaklı dosya türü: $mime ($kind)");
                    }
                    $dataToStore = move_uploaded_file_strict($fileArr, $destDir, $projectRoot);
                } elseif ($existing && trim($existing) !== '') {
                    $dataToStore = trim($existing);
                } else {
                    // yeni içerikte dosya yoksa ekleme
                    continue;
                }

                $ins = $pdo->prepare("INSERT INTO module_contents (module_id, type, title, data, sort_order) VALUES (?, ?, NULL, ?, ?)");
                $ins->execute([$module_id, $dbType, $dataToStore, $sort]);
            }
        }
    }

    $pdo->commit();
    $stat = $pdo->prepare("UPDATE course_modules SET status='pending' WHERE id=?");
    $stat->execute([$module_id]);
    header('Location: edit_module_content.php?id=' . (int)$module_id . '&ok=1');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo "Güncelleme sırasında hata: " . htmlspecialchars($e->getMessage());
}
