<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
$projectRoot = dirname(__DIR__);
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

$module_id = $_GET['id'] ?? null;
if (!$module_id) { die("Bölüm ID'si bulunamadı."); }

// Bölüm ve sahiplik kontrolü
$stmt = $pdo->prepare("
    SELECT m.*, c.id as course_id, c.team_db_id
    FROM course_modules m
    JOIN courses c ON m.course_id = c.id
    WHERE m.id = ? AND c.team_db_id = ?
");
$stmt->execute([$module_id, $_SESSION['team_db_id']]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$module) { die("Hata: Bu bölümü düzenleme yetkiniz yok veya bölüm bulunamadı."); }

// Bu modüle ait (tek satır) içerikleri çek
$row = null;
try {
    $stmt2 = $pdo->prepare("SELECT id, title, data, data_file, data_vid FROM module_contents WHERE module_id = ? LIMIT 1");
    $stmt2->execute([$module_id]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Exception $e) {
    $row = null;
}

$existing_id    = $row['id']        ?? '';
$title          = $row['title']     ?? '';
$text_body      = $row['data']      ?? '';
$document_path  = $row['data_file'] ?? '';
$video_path     = $row['data_vid']  ?? '';
// BASE_URL tanımı config’te yoksa basit fallback
if (!defined('BASE_URL')) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    define('BASE_URL', 'https://' . $host . $base);
}
$page_title = "Bölüm İçeriğini Düzenle";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <title><?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars($module['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <style>
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem}
        .content-item{border:1px solid #e5e7eb;border-radius:.5rem;overflow:hidden}
        .content-item-header{display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem;border-bottom:1px solid #e5e7eb;background:#fafafa}
        .content-item-body{padding:1rem}
        .form-input{width:100%;border:1px solid #e5e7eb;border-radius:.5rem;padding:.5rem .75rem}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1rem;border-radius:.5rem;background:#E5AE32;color:#fff;font-weight:600}
        .btn:hover{background:#c4952b}
        .btn-secondary{background:#e5e7eb;color:#374151}
        .editable-content{min-height:180px;border:1px solid #e5e7eb;border-radius:.5rem;padding:1rem;background:#fff}
        .editor-toolbar{display:flex;gap:.5rem;margin-bottom:.5rem}
        .editor-button{border:1px solid #e5e7eb;border-radius:.375rem;background:#fff;padding:.35rem .5rem;cursor:pointer}
        .upload-preview{border:1px dashed #d1d5db;border-radius:.5rem;padding:1rem}
        .note{font-size:.85rem;color:#6b7280}
    </style>
</head>
<body class="bg-gray-100">
<?php require_once '../navbar.php'; ?>

<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Bölüm Detayları</h1>
            <p class="text-gray-600">“<?= htmlspecialchars($module['title']) ?>” bölümünün içeriğini düzenleyin.</p>
        </div>
        <a href="view_curriculum.php?id=<?= (int)$module['course_id'] ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Geri Dön</a>
    </div>

    <form id="module-form" action="update_module.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="module_id" value="<?= (int)$module_id ?>">
        <input type="hidden" name="course_id" value="<?= (int)$module['course_id'] ?>">
        <input type="hidden" name="existing_id" value="<?= htmlspecialchars($existing_id) ?>">

        <div class="card space-y-6">

            <!-- GENEL BAŞLIK (opsiyonel) -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="heading-1"></i> Başlık</div>
                </div>
                <div class="content-item-body">
                    <input type="text" name="title" class="form-input" placeholder="Başlık (opsiyonel)" value="<?= htmlspecialchars($title) ?>">
                </div>
            </div>

            <!-- METİN -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="type"></i> Metin</div>
                </div>
                <div class="content-item-body">
                    <div class="editor-toolbar">
                        <button type="button" class="editor-button" data-command="bold"><b>B</b></button>
                        <button type="button" class="editor-button" data-command="italic"><i>I</i></button>
                        <button type="button" class="editor-button" id="link-btn"><i data-lucide="link"></i></button>
                        <button type="button" class="editor-button" data-command="insertUnorderedList"><i data-lucide="list"></i></button>
                        <button type="button" class="editor-button" data-command="insertOrderedList"><i data-lucide="list-ordered"></i></button>
                    </div>
                    <div id="editable" class="editable-content" contenteditable="true"><?= $text_body ?></div>
                    <textarea name="text_body" id="text_body" class="hidden"></textarea>
                </div>
            </div>

            <!-- VİDEO -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="video"></i> Video</div>
                </div>
                <div class="content-item-body space-y-3">
                    <?php if (!empty($video_path)): ?>
                        <div class="note">Mevcut video: <code><?= htmlspecialchars(basename($video_path)) ?></code></div>
                        <div class="upload-preview">
                            <video controls preload="metadata" style="width:100%;max-height:360px">
                                <source src="<?= htmlspecialchars((str_starts_with($video_path,'http') ? $video_path : ('/'.ltrim($video_path,'/')))) ?>" type="video/mp4">
                                Tarayıcınız video etiketini desteklemiyor.
                            </video>
                        </div>
                    <?php else: ?>
                        <div class="note">Bu modül için henüz video eklenmemiş.</div>
                    <?php endif; ?>

                    <label class="block text-sm mb-1">Yeni video yükle (opsiyonel)</label>
                    <input type="file" name="video_file" accept="video/*" class="form-input">

                    <input type="hidden" name="video_existing_path" value="<?= htmlspecialchars($video_path) ?>">
                    <label class="inline-flex items-center gap-2 text-sm mt-2">
                        <input type="checkbox" name="video_clear" value="1"> Videoyu kaldır
                    </label>
                </div>
            </div>

            <!-- DÖKÜMAN -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="file-text"></i> Döküman</div>
                </div>
                <div class="content-item-body space-y-3">
                    <?php if (!empty($document_path)): ?>
                        <div class="note">Mevcut döküman: <code><?= htmlspecialchars(basename($document_path)) ?></code></div>
                        <div class="upload-preview">
                            <?php if (str_ends_with(strtolower($document_path), '.pdf')): ?>
                                <iframe src="<?= htmlspecialchars((str_starts_with($document_path,'http') ? $document_path : ('/'.ltrim($document_path,'/')))) ?>" style="width:100%;height:60vh;border:0"></iframe>
                            <?php else: ?>
                                <a class="text-blue-600 underline" target="_blank" href="<?= htmlspecialchars((str_starts_with($document_path,'http') ? $document_path : ('/'.ltrim($document_path,'/')))) ?>">Dökümanı aç</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="note">Bu modül için henüz döküman eklenmemiş.</div>
                    <?php endif; ?>

                    <label class="block text-sm mb-1">Yeni döküman yükle (opsiyonel)</label>
                    <input type="file" name="document_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" class="form-input">

                    <input type="hidden" name="document_existing_path" value="<?= htmlspecialchars($document_path) ?>">
                    <label class="inline-flex items-center gap-2 text-sm mt-2">
                        <input type="checkbox" name="document_clear" value="1"> Dökümanı kaldır
                    </label>
                </div>
            </div>

        </div>

        <div class="text-right mt-8">
            <button type="submit" class="btn text-lg">
                <i data-lucide="save" class="w-5 h-5"></i> Bölümü Kaydet
            </button>
        </div>
    </form>
</div>

<!-- Basit link modalı yerine prompt kullanıyoruz -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();

        const editable = document.getElementById('editable');
        const hidden   = document.getElementById('text_body');

        if (editable && hidden) {
            hidden.value = editable.innerHTML;
            editable.addEventListener('input', () => hidden.value = editable.innerHTML);

            document.querySelectorAll('.editor-button[data-command]').forEach(btn => {
                btn.addEventListener('click', () => {
                    editable.focus();
                    document.execCommand(btn.dataset.command, false, null);
                    hidden.value = editable.innerHTML;
                });
            });

            const linkBtn = document.getElementById('link-btn');
            if (linkBtn) {
                linkBtn.addEventListener('click', () => {
                    const sel = window.getSelection();
                    if (!sel || sel.toString().length === 0) { alert('Lütfen link vermek için metin seçin.'); return; }
                    const url = prompt('Bağlantı URL\'si (https://):', 'https://');
                    if (!url) return;
                    editable.focus();
                    document.execCommand('createLink', false, url);
                    hidden.value = editable.innerHTML;
                });
            }

            document.getElementById('module-form').addEventListener('submit', () => {
                hidden.value = editable.innerHTML;
            });
        }
    });
</script>
</body>
</html>
