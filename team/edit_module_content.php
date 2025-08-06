<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
require_once '../config.php';
$pdo = connectDB();

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
$stmt_contents = $pdo->prepare("SELECT * FROM module_contents WHERE module_id = ? ORDER BY sort_order ASC");
$stmt_contents->execute([$module_id]);
$contents = $stmt_contents->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Bölüm İçeriğini Düzenle";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?php echo $page_title; ?> - <?php echo htmlspecialchars($module['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/manage_curriculum.css">
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
    
    <form id="module-form" action="save_module_content.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
        <input type="hidden" name="course_id" value="<?php echo $module['course_id']; ?>">
        
        <div class="card space-y-6">
            <div class="content-items-container space-y-4">
                <?php foreach($contents as $content): ?>
                    <div class="content-item" data-content-id="<?php echo $content['id']; ?>">
                        <input type="hidden" name="contents[<?php echo $content['id']; ?>][type]" value="<?php echo $content['content_type']; ?>">
                        <?php if ($content['content_type'] === 'text'): ?>
                            <div class="content-item-header">
                                <div class="flex items-center gap-2"><i data-lucide="type"></i> Metin</div>
                                <div class="module-actions"><button type="button" class="delete-content-btn text-red-500"><i data-lucide="trash-2"></i></button></div>
                            </div>
                            <div class="content-item-body space-y-2">
                                <input type="text" name="contents[<?php echo $content['id']; ?>][title]" class="form-input font-bold" placeholder="Başlık (opsiyonel)" value="<?php echo htmlspecialchars($content['title'] ?? ''); ?>">
                                <div class="editor-toolbar">
                                    <button type="button" class="editor-button" data-command="bold"><b>B</b></button>
                                    <button type="button" class="editor-button" data-command="italic"><i>I</i></button>
                                    <button type="button" class="editor-button link-btn"><i data-lucide="link"></i></button>
                                    <button type="button" class="editor-button" data-command="insertUnorderedList"><i data-lucide="list"></i></button>
                                    <button type="button" class="editor-button" data-command="insertOrderedList"><i data-lucide="list-ordered"></i></button>
                                </div>
                                <div class="editable-content" contenteditable="true"><?php echo $content['data']; ?></div>
                                <textarea name="contents[<?php echo $content['id']; ?>][paragraph]" class="hidden"><?php echo htmlspecialchars($content['data']); ?></textarea>
                            </div>
                        <?php else: ?>
                            <div class="content-item-header">
                                <div class="flex items-center gap-2"><i data-lucide="<?php echo $content['content_type'] === 'video' ? 'video' : 'file-text'; ?>"></i> <?php echo $content['content_type'] === 'video' ? 'Video' : 'Döküman'; ?></div>
                                <div class="module-actions"><button type="button" class="delete-content-btn text-red-500"><i data-lucide="trash-2"></i></button></div>
                            </div>
                            <div class="content-item-body">
                                <div class="horizontal-upload-card">
                                    <div class="upload-preview-thumb">
                                        <?php if($content['content_type'] === 'video'): ?>
                                            <video src="<?php echo BASE_URL . '/' . $content['data']; ?>" class="w-full h-full object-cover"></video>
                                        <?php else: ?>
                                            <i data-lucide="file-check-2"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="upload-drop-area">
                                        <div class="upload-info">
                                            <div class="file-name"><?php echo basename($content['data']); ?></div>
                                            <p class="text-xs text-gray-500 mt-1">Yeni dosya yüklemek için buraya tıklayın veya sürükleyin.</p>
                                        </div>
                                        <input type="file" name="contents[<?php echo $content['id']; ?>][file]" class="hidden-input" accept="<?php echo $content['content_type'] === 'video' ? 'video/*' : '.pdf,.doc,.docx'; ?>">
                                        <input type="hidden" name="contents[<?php echo $content['id']; ?>][existing_file]" value="<?php echo $content['data']; ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex gap-4 mt-6 border-t pt-4">
                <button type="button" class="add-content-btn btn btn-secondary btn-sm" data-type="video"><i data-lucide="video-plus"></i> Video Ekle</button>
                <button type="button" class="add-content-btn btn btn-secondary btn-sm" data-type="document"><i data-lucide="file-plus"></i> Döküman Ekle</button>
                <button type="button" class="add-content-btn btn btn-secondary btn-sm" data-type="text"><i data-lucide="pilcrow"></i> Metin Ekle</button>
            </div>
        </div>

        <div class="text-right mt-8">
            <button type="submit" class="btn text-lg"><i data-lucide="save" class="mr-2"></i> Bölümü Kaydet</button>
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