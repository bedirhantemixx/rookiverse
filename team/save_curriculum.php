<?php
session_start();
$projectRoot = dirname(__DIR__); // ör: /Applications/XAMPP/xamppfiles/htdocs/projeadi
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

// Güvenlik
if (!isset($_SESSION['team_logged_in']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Yetkisiz erişim.');
}

try {
    $course_id   = $_POST['course_id'] ?? null;
    $modulesData = $_POST['modules'] ?? [];
    $filesData   = $_FILES['modules'] ?? null;

    if (!$course_id || empty($modulesData)) {
        throw new Exception('Eksik veri: course_id veya modules boş.');
    }

    // Kurs sahipliği doğrula
    $own = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND team_db_id = ?");
    $own->execute([$course_id, $_SESSION['team_db_id']]);
    if ($own->fetchColumn() === false) {
        throw new Exception('Bu kurs üzerinde işlem yetkiniz yok.');
    }

    // Upload dizini (FS ve Rel)
    $uploadDirFs  = rtrim($projectRoot, '/\\') . '/uploads/course_content/';
    $uploadDirRel = 'uploads/course_content/';
    if (!is_dir($uploadDirFs)) {
        if (!mkdir($uploadDirFs, 0755, true) && !is_dir($uploadDirFs)) {
            throw new Exception('Yükleme dizini oluşturulamadı: ' . $uploadDirFs);
        }
    }

    // İzinli uzantılar
    $docExt = ['pdf','doc','docx','ppt','pptx','xls','xlsx'];
    $vidExt = ['mp4','mov','mkv','webm','avi','m4v'];

    // Yardımcı: $_FILES çok boyutlu erişim
    $getNestedFile = function ($filesRoot, array $path) {
        // path: [module_key, 'contents', content_key, 'file']
        $keys = ['name','type','tmp_name','error','size'];
        $out  = [];
        foreach ($keys as $k) {
            $node = $filesRoot[$k] ?? null;
            foreach ($path as $p) {
                if (!isset($node[$p])) { $node = null; break; }
                $node = $node[$p];
            }
            $out[$k] = $node;
        }
        if (!isset($out['error'])) return null;
        return $out;
    };

    $pdo->beginTransaction();

    foreach ($modulesData as $moduleKey => $moduleInfo) {
        $moduleTitle = trim($moduleInfo['title'] ?? '');
        if ($moduleTitle === '') { continue; }

        // 1) Bölümü ekle (tek bölüm bekleniyor ama çokluya dayanıklı)
        $insModule = $pdo->prepare("INSERT INTO course_modules (course_id, title, sort_order) VALUES (?, ?, ?)");
        $insModule->execute([$course_id, $moduleTitle, 0]);
        $moduleId = (int)$pdo->lastInsertId();

        // 2) Bu modül için TEK KAYIT hazırlayacağız
        $dataHtml = null;   // text -> module_contents.data
        $dataFile = null;   // document -> module_contents.data_file
        $dataVid  = null;   // video -> module_contents.data_vid

        // her tipten sadece ilkini al
        $seen = ['text' => false, 'document' => false, 'video' => false];

        if (!empty($moduleInfo['contents']) && is_array($moduleInfo['contents'])) {
            // sort_order'a göre sırala (gelen ilk içerik kazanır)
            $contents = $moduleInfo['contents'];
            uasort($contents, function ($a, $b) {
                $sa = isset($a['sort_order']) ? (int)$a['sort_order'] : 0;
                $sb = isset($b['sort_order']) ? (int)$b['sort_order'] : 0;
                return $sa <=> $sb;
            });

            foreach ($contents as $contentKey => $contentInfo) {
                $type = $contentInfo['type'] ?? null;
                if (!in_array($type, ['text','document','video'], true)) continue;
                if ($seen[$type]) continue; // bu tipten zaten aldık

                if ($type === 'text') {
                    $html = trim($contentInfo['paragraph'] ?? '');
                    if ($html !== '') {
                        $dataHtml = $html;
                        $seen['text'] = true;
                    }
                } elseif ($type === 'document') {
                    // dosyadan veya existing_file'dan
                    $file = $getNestedFile($filesData, [$moduleKey, 'contents', $contentKey, 'file']);
                    if ($file && $file['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, $docExt, true)) { throw new Exception('Geçersiz döküman uzantısı: '.$ext); }
                        $unique = uniqid('doc_', true).'.'.$ext;
                        if (!move_uploaded_file($file['tmp_name'], $uploadDirFs.$unique)) {
                            throw new Exception('Döküman yüklenemedi.');
                        }
                        $dataFile = $uploadDirRel.$unique;
                        $seen['document'] = true;
                    } else {
                        $exist = trim($contentInfo['existing_file'] ?? '');
                        if ($exist !== '') {
                            $dataFile = $exist;
                            $seen['document'] = true;
                        }
                    }
                } elseif ($type === 'video') {
                    $file = $getNestedFile($filesData, [$moduleKey, 'contents', $contentKey, 'file']);
                    if ($file && $file['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, $vidExt, true)) { throw new Exception('Geçersiz video uzantısı: '.$ext); }
                        $unique = uniqid('vid_', true).'.'.$ext;
                        if (!move_uploaded_file($file['tmp_name'], $uploadDirFs.$unique)) {
                            throw new Exception('Video yüklenemedi.');
                        }
                        $dataVid = $uploadDirRel.$unique;
                        $seen['video'] = true;
                    } else {
                        $exist = trim($contentInfo['existing_file'] ?? '');
                        if ($exist !== '') {
                            $dataVid = $exist;
                            $seen['video'] = true;
                        }
                    }
                }
            }
        }

        // 3) En az bir alan doluysa TEK SATIR insert et
        if ($dataHtml !== null || $dataFile !== null || $dataVid !== null) {
            $insContent = $pdo->prepare("
                INSERT INTO module_contents (module_id, title, data, data_file, data_vid, sort_order)
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $insContent->execute([
                $moduleId,
                null,       // title (opsiyonel; UI isteğine göre doldurulabilir)
                $dataHtml,
                $dataFile,
                $dataVid
            ]);
        }
    }

    $pdo->commit();
    header('Location: view_curriculum.php?id=' . urlencode((string)$course_id));
    exit();

} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log('[save_curriculum_single_row] ' . $e->getMessage());
    http_response_code(500);
    exit('Veritabanına kaydederken bir hata oluştu.');
}
