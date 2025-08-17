<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
$pdo = get_db_connection();

$module_id = $_GET['id'] ?? null;
if (!$module_id) { die("Bölüm ID'si bulunamadı."); }

// Güvenlik: Bu bölümün gerçekten bu takıma ait olup olmadığını kontrol et ve mevcut bilgileri çek
$stmt = $pdo->prepare(
    "SELECT m.*, c.id as course_id FROM course_modules m 
     JOIN courses c ON m.course_id = c.id 
     WHERE m.id = ? AND c.team_db_id = ?"
);
$stmt->execute([$module_id, $_SESSION['team_db_id']]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) { die("Hata: Bu bölümü düzenleme yetkiniz yok veya bölüm bulunamadı."); }

// Bu bölüme ait MEVCUT içerikleri veritabanından çek
$stmt_contents = $pdo->prepare("SELECT * FROM module_contents WHERE id = ? ORDER BY sort_order ASC");
$stmt_contents->execute([$module_id]);
$contents = $stmt_contents->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Bölüm İçeriğini Düzenle";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?php echo $page_title; ?> - <?php echo htmlspecialchars($module['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/manage_curriculum.css">
</head>
<body class="bg-gray-100">
<?php require_once '../navbar.php'; ?>
<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Bölüm Detayları</h1>
            <p class="text-gray-600">"<?php echo htmlspecialchars($module['title']); ?>" bölümünün içeriğini düzenleyin.</p>
        </div>
        <a href="view_curriculum.php?id=<?php echo $module['course_id']; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Geri Dön</a>
    </div>

    <?php
    // ---- Tek satırı (JSON) çek ve payload'ı hazırla ----
    $payload = [
        'text'     => ['title' => '', 'body' => ''],
        'document' => ['title' => '', 'path' => ''],
        'video'    => ['title' => '', 'path' => ''],
    ];

    $existing_id = '';

    try {
        $stmt = $pdo->prepare("SELECT id, data FROM module_contents WHERE module_id = ? LIMIT 1");
        $stmt->execute([$module_id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existing_id = (string)$row['id'];
            $decoded = json_decode($row['data'] ?? '', true);
            if (is_array($decoded)) {
                // mevcut alanları override et
                $payload = array_replace_recursive($payload, $decoded);
            }
        }
    } catch (Exception $e) {
        // burayı loglayabilirsin, form yine boşlarla çalışır
    }
    ?>

    <?php
    // GEREKENLER: $pdo, $module_id, $module['course_id'], BASE_URL

    // Varolan tek satırı çek
    $row = null;
    $existing_id = '';
    try {
        $stmt = $pdo->prepare("SELECT id, title, data, data_file, data_vid FROM module_contents WHERE module_id = ? LIMIT 1");
        $stmt->execute([$module_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($row) $existing_id = (string)$row['id'];
    } catch (Exception $e) {
        // loglayabilirsin, form boş da çalışır
    }

    $title          = $row['title']     ?? '';
    $text_body      = $row['data']      ?? '';
    $document_path  = $row['data_file'] ?? '';
    $video_path     = $row['data_vid']  ?? '';
    ?>

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
                    <input
                            type="text"
                            name="title"
                            class="form-input"
                            placeholder="Başlık (opsiyonel)"
                            value="<?= htmlspecialchars($title) ?>">
                </div>
            </div>

            <!-- METİN (opsiyonel) -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="type"></i> Metin</div>
                </div>
                <div class="content-item-body">
        <textarea
                name="text_body"
                class="form-textarea"
                rows="8"
                placeholder="Metin içeriği (opsiyonel)"><?= htmlspecialchars($text_body) ?></textarea>
                </div>
            </div>

            <!-- VİDEO (opsiyonel) -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="video"></i> Video</div>
                </div>
                <div class="content-item-body">
                    <?php if (!empty($video_path)): ?>
                        <div class="mb-2 text-sm">
                            Mevcut video: <code><?= htmlspecialchars(basename($video_path)) ?></code>
                        </div>
                    <?php endif; ?>

                    <div class="mt-2">
                        <label class="block text-sm mb-1">Yeni video yükle (opsiyonel)</label>
                        <input type="file" name="video_file" accept="video/*">
                    </div>

                    <!-- yeni dosya gelmezse mevcut korunsun -->
                    <input type="hidden" name="video_existing_path" value="<?= htmlspecialchars($video_path) ?>">
                    <!-- İstersen temizle seçeneği (opsiyonel) -->
                    <label class="inline-flex items-center gap-2 mt-2 text-sm">
                      <input type="checkbox" name="video_clear" value="1"> Videoyu kaldır
                    </label>
                </div>
            </div>

            <!-- DÖKÜMAN (opsiyonel) -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="file-text"></i> Döküman</div>
                </div>
                <div class="content-item-body">
                    <?php if (!empty($document_path)): ?>
                        <div class="mb-2 text-sm">
                            Mevcut döküman: <code><?= htmlspecialchars(basename($document_path)) ?></code>
                        </div>
                    <?php endif; ?>

                    <div class="mt-2">
                        <label class="block text-sm mb-1">Yeni döküman yükle (opsiyonel)</label>
                        <input type="file" name="document_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                    </div>

                    <!-- yeni dosya gelmezse mevcut korunsun -->
                    <input type="hidden" name="document_existing_path" value="<?= htmlspecialchars($document_path) ?>">
                    <!-- İstersen temizle seçeneği (opsiyonel) -->
                    <label class="inline-flex items-center gap-2 mt-2 text-sm">
                      <input type="checkbox" name="document_clear" value="1"> Dökümanı kaldır
                    </label>
                </div>
            </div>

        </div>

        <div class="text-right mt-8">
            <button type="submit" class="btn text-lg">
                <i data-lucide="save" class="mr-2"></i> Bölümü Kaydet
            </button>
        </div>
    </form>




</div>
<div id="link-modal" class="modal-overlay"><div class="modal-content"><div class="p-6"><h2 class="text-xl font-bold mb-4">Link Ekle/Düzenle</h2><div class="space-y-2"><label for="link-url" class="font-medium text-gray-700">URL Adresi</label><input type="url" id="link-url" class="form-input p-2 border" placeholder="https://www.example.com"></div></div><div class="bg-gray-100 p-4 flex justify-end gap-4 rounded-b-lg"><button type="button" id="cancel-link-btn" class="btn btn-secondary">İptal</button><button type="button" id="apply-link-btn" class="btn">Linki Uygula</button></div></div></div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Javascript kodunda bir değişiklik gerekmiyor, aynı kalabilir.
    let contentCounter = <?php echo count($contents); ?>;
    let lastFocusedEditor = null;
    let savedSelection = null;
    const linkModal = document.getElementById('link-modal');
    const linkUrlInput = document.getElementById('link-url');

    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
        document.querySelectorAll('.add-content-btn').forEach(btn => {
            btn.addEventListener('click', (e) => addContentItem(e.currentTarget, e.currentTarget.dataset.type));
        });
        document.querySelectorAll('.content-item').forEach(item => {
            const type = item.querySelector('input[name*="[type]"]').value;
            attachContentItemListeners(item, type);
        });
        document.getElementById('cancel-link-btn').addEventListener('click', closeLinkModal);
        document.getElementById('apply-link-btn').addEventListener('click', applyLink);
    });

    function addContentItem(button, type) {
        contentCounter++;
        const container = button.closest('.card').querySelector('.content-items-container');
        const itemElement = document.createElement('div');
        itemElement.className = 'content-item';
        let contentHTML = '';

        if (type === 'video' || type === 'document') {
            const accept = type === 'video' ? 'video/*' : '.pdf,.doc,.docx';
            contentHTML = `<div class="content-item-header"><div class="flex items-center gap-2"><i data-lucide="${type === 'video' ? 'video' : 'file-text'}"></i> ${type === 'video' ? 'Video' : 'Döküman'}</div><div class="module-actions"><button type="button" class="delete-content-btn text-red-500"><i data-lucide="trash-2"></i></button></div></div><div class="content-item-body"><div class="horizontal-upload-card"><div class="upload-preview-thumb"><i data-lucide="${type === 'video' ? 'video' : 'file-text'}"></i></div><div class="upload-drop-area"><input type="file" name="contents[new_${contentCounter}][file]" class="hidden-input" accept="${accept}" required><p>Dosyayı buraya sürükleyin veya tıklayın</p></div></div></div>`;
        } else if (type === 'text') {
            contentHTML = `<div class="content-item-header"><div class="flex items-center gap-2"><i data-lucide="type"></i> Metin</div><div class="module-actions"><button type="button" class="delete-content-btn text-red-500"><i data-lucide="trash-2"></i></button></div></div><div class="content-item-body space-y-2"><input type="text" name="contents[new_${contentCounter}][title]" class="form-input font-bold" placeholder="Başlık (opsiyonel)"><div class="editor-toolbar"><button type="button" class="editor-button" data-command="bold"><b>B</b></button><button type="button" class="editor-button" data-command="italic"><i>I</i></button><button type="button" class="editor-button link-btn"><i data-lucide="link"></i></button><button type="button" class="editor-button" data-command="insertUnorderedList"><i data-lucide="list"></i></button><button type="button" class="editor-button" data-command="insertOrderedList"><i data-lucide="list-ordered"></i></button></div><div class="editable-content" contenteditable="true"></div><textarea name="contents[new_${contentCounter}][paragraph]" class="hidden"></textarea></div>`;
        }
        itemElement.innerHTML = `<input type="hidden" name="contents[new_${contentCounter}][type]" value="${type}">${contentHTML}`;
        container.appendChild(itemElement);
        attachContentItemListeners(itemElement, type);
        lucide.createIcons();
    }
    
    function attachContentItemListeners(itemElement, type) {
        itemElement.querySelector('.delete-content-btn').addEventListener('click', () => itemElement.remove());
        if (type === 'video' || type === 'document') {
            const dropArea = itemElement.querySelector('.upload-drop-area');
            const fileInput = itemElement.querySelector('.hidden-input');
            dropArea.addEventListener('click', () => fileInput.click());
            dropArea.addEventListener('dragover', (e) => { e.preventDefault(); dropArea.classList.add('drag-over'); });
            dropArea.addEventListener('dragleave', () => dropArea.classList.remove('drag-over'));
            dropArea.addEventListener('drop', (e) => { e.preventDefault(); dropArea.classList.remove('drag-over'); if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; handleFile(e.dataTransfer.files[0], dropArea); } });
            fileInput.addEventListener('change', () => { if (fileInput.files.length) handleFile(fileInput.files[0], dropArea); });
        } else if (type === 'text') {
            const editor = itemElement.querySelector('.editable-content');
            editor.addEventListener('focus', () => saveLastFocusedEditor(editor));
            editor.addEventListener('input', () => updateHiddenTextarea(editor));
            itemElement.querySelectorAll('.editor-toolbar .editor-button').forEach(btn => {
                if(btn.dataset.command) { btn.addEventListener('click', () => formatDoc(btn.dataset.command)); }
            });
            itemElement.querySelector('.link-btn').addEventListener('click', toggleLink);
        }
    }

    function handleFile(file, dropArea) {
        const previewThumb = dropArea.parentElement.querySelector('.upload-preview-thumb');
        const uploadInfo = `<div class="upload-info"><div class="file-name">${file.name}</div><div class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</div></div>`;
        dropArea.innerHTML = uploadInfo;
        if (file.type.startsWith('video')) {
            const reader = new FileReader();
            reader.onload = () => { previewThumb.innerHTML = `<video src="${reader.result}" class="w-full h-full object-cover"></video>`; }
            reader.readAsDataURL(file);
        } else {
            previewThumb.innerHTML = `<i data-lucide="file-check-2"></i>`;
            lucide.createIcons();
        }
    }
    
    function saveLastFocusedEditor(editor) { lastFocusedEditor = editor; }
    function formatDoc(command) { if (lastFocusedEditor) { lastFocusedEditor.focus(); document.execCommand(command, false, null); updateHiddenTextarea(lastFocusedEditor); } }
    function updateHiddenTextarea(div) { if (div.nextElementSibling) div.nextElementSibling.value = div.innerHTML; }
    
    function toggleLink() {
        if (!lastFocusedEditor) return;
        lastFocusedEditor.focus();
        let selection = window.getSelection(); let parentNode = selection.anchorNode.parentNode;
        if (parentNode.tagName === 'A') { document.execCommand('unlink', false, null); }
        else {
            if (selection.toString().length === 0) { alert("Lütfen link eklemek için bir metin seçin."); return; }
            savedSelection = selection.getRangeAt(0).cloneRange();
            linkModal.classList.add('show');
            linkUrlInput.focus();
        }
    }
    
    function closeLinkModal() { linkModal.classList.remove('show'); }
    
    function applyLink() {
        const url = linkUrlInput.value;
        if (url && savedSelection) {
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(savedSelection);
            document.execCommand('createLink', false, url);
            closeLinkModal();
            linkUrlInput.value = '';
            updateHiddenTextarea(lastFocusedEditor);
        }
    }
</script>
</body>
</html>