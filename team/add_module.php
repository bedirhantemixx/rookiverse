<?php
session_start();
if (!isset($_SESSION['team_logged_in'])) { header('Location: ../team-login.php'); exit(); }
$projectRoot = dirname(__DIR__); // Ör: C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) { die("Kurs ID'si bulunamadı."); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Yeni Bölüm Ekle</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/manage_curriculum.css">
</head>
<body class="bg-gray-100">
<?php require_once $projectRoot . '/navbar.php'; ?>

<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Yeni Bölüm Ekle</h1>
            <p class="text-gray-600">Bir başlık, bir metin; isteğe bağlı olarak video ve doküman ekle.</p>
        </div>
        <a href="view_curriculum.php?id=<?= (int)$course_id ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Geri Dön</a>
    </div>

    <form id="module-form" action="save_module.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_module">
        <input type="hidden" name="course_id" value="<?= (int)$course_id ?>">

        <div class="card space-y-6">

            <!-- Başlık -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="heading-1"></i> Bölüm Başlığı</div>
                </div>
                <div class="content-item-body">
                    <input type="text" name="title" class="form-input" placeholder="Bölüm başlığı" required>
                </div>
            </div>

            <!-- Metin -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="type"></i> Metin</div>
                </div>
                <div class="content-item-body space-y-2">
                    <div class="editor-toolbar">
                        <button type="button" class="editor-button" data-command="bold" title="Kalın"><b>B</b></button>
                        <button type="button" class="editor-button" data-command="italic" title="İtalik"><i>I</i></button>
                        <button type="button" class="editor-button" id="link-btn" title="Link Ekle"><i data-lucide="link"></i></button>
                        <button type="button" class="editor-button" data-command="insertUnorderedList" title="Madde İşaretli">
                            <i data-lucide="list"></i>
                        </button>
                        <button type="button" class="editor-button" data-command="insertOrderedList" title="Numaralı">
                            <i data-lucide="list-ordered"></i>
                        </button>
                    </div>
                    <div id="editable" class="editable-content" contenteditable="true" placeholder="Metin içeriği"></div>
                    <textarea style="width: 100%" name="text_body" id="text_body" class="hidden"></textarea>
                </div>
            </div>

            <!-- Video (opsiyonel) -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="video"></i> Video (opsiyonel)</div>
                </div>
                <div class="content-item-body">
                    <div class="horizontal-upload-card">
                        <div class="upload-preview-thumb" id="video-thumb"><i data-lucide="video"></i></div>
                        <div class="upload-drop-area" id="video-drop">
                            <input type="file" name="video_file" id="video_input" class="hidden-input" accept="video/*">
                            <p>Dosyayı buraya sürükleyin veya tıklayın</p>
                            <div class="text-xs text-gray-500 mt-1">Maks. ~500MB önerilir</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Döküman (opsiyonel) -->
            <div class="content-item">
                <div class="content-item-header">
                    <div class="flex items-center gap-2"><i data-lucide="file-text"></i> Döküman (opsiyonel)</div>
                </div>
                <div class="content-item-body">
                    <div class="horizontal-upload-card">
                        <div class="upload-preview-thumb" id="doc-thumb"><i data-lucide="file-text"></i></div>
                        <div class="upload-drop-area" id="doc-drop">
                            <input type="file" name="document_file" id="doc_input" class="hidden-input"
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                            <p>Dosyayı buraya sürükleyin veya tıklayın</p>
                            <div class="text-xs text-gray-500 mt-1">PDF/Office dosyaları desteklenir</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-right mt-8">
            <button type="submit" class="btn text-lg">
                <i data-lucide="save" class="mr-2"></i> Bölümü Kaydet ve Onaya Gönder
            </button>
        </div>
    </form>
</div>

<!-- Link Modal -->
<div id="link-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Link Ekle/Düzenle</h2>
            <div class="space-y-2">
                <label for="link-url" class="font-medium text-gray-700">URL Adresi</label>
                <input type="url" id="link-url" class="form-input p-2 border" placeholder="https://www.example.com">
            </div>
        </div>
        <div class="bg-gray-100 p-4 flex justify-end gap-4 rounded-b-lg">
            <button type="button" id="cancel-link-btn" class="btn btn-secondary">İptal</button>
            <button type="button" id="apply-link-btn" class="btn">Linki Uygula</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // --- Iconlar
    document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });

    // --- Basit WYSIWYG
    const editable = document.getElementById('editable');
    const hidden   = document.getElementById('text_body');
    let lastFocusedEditor = editable;
    let savedSelection = null;

    editable.addEventListener('input', () => hidden.value = editable.innerHTML);
    editable.addEventListener('focus', () => lastFocusedEditor = editable);

    // toolbar
    document.querySelectorAll('.editor-button[data-command]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!lastFocusedEditor) return;
            lastFocusedEditor.focus();
            document.execCommand(btn.dataset.command, false, null);
            hidden.value = editable.innerHTML;
        });
    });

    // link modal
    const linkBtn = document.getElementById('link-btn');
    const linkModal = document.getElementById('link-modal');
    const linkUrlInput = document.getElementById('link-url');

    linkBtn.addEventListener('click', () => {
        if (!lastFocusedEditor) return;
        const sel = window.getSelection();
        if (!sel || sel.toString().length === 0) { alert('Lütfen link vermek için metin seçin.'); return; }
        savedSelection = sel.getRangeAt(0).cloneRange();
        linkModal.classList.add('show');
        linkUrlInput.value = '';
        linkUrlInput.focus();
    });
    document.getElementById('cancel-link-btn').addEventListener('click', () => linkModal.classList.remove('show'));
    document.getElementById('apply-link-btn').addEventListener('click', () => {
        const url = (linkUrlInput.value || '').trim();
        if (!url || !savedSelection) return;
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedSelection);
        document.execCommand('createLink', false, url);
        linkModal.classList.remove('show');
        hidden.value = editable.innerHTML;
    });

    // --- Basit sürükle-bırak + önizleme (video & doküman)
    setupDrop('video-drop', 'video_input', 'video', 'video-thumb');
    setupDrop('doc-drop',   'doc_input',   'doc',   'doc-thumb');

    function setupDrop(dropId, inputId, mode, thumbId) {
        const drop = document.getElementById(dropId);
        const input = document.getElementById(inputId);
        const thumb = document.getElementById(thumbId);

        drop.addEventListener('click', () => input.click());
        drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('drag-over'); });
        drop.addEventListener('dragleave', () => drop.classList.remove('drag-over'));
        drop.addEventListener('drop', e => {
            e.preventDefault(); drop.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                preview(input.files[0], mode, thumb, drop);
            }
        });
        input.addEventListener('change', () => {
            if (input.files.length) preview(input.files[0], mode, thumb, drop);
        });
    }

    function preview(file, mode, thumb, drop) {
        drop.innerHTML = `
        <div class="upload-info">
            <div class="file-name">${file.name}</div>
            <div class="file-size">${(file.size/1024/1024).toFixed(2)} MB</div>
        </div>
    `;
        if (mode === 'video' && file.type.startsWith('video')) {
            const reader = new FileReader();
            reader.onload = () => { thumb.innerHTML = `<video src="${reader.result}" class="w-full h-full object-cover" controls></video>`; };
            reader.readAsDataURL(file);
        } else {
            thumb.innerHTML = `<i data-lucide="file-check-2"></i>`;
            lucide.createIcons();
        }
    }
</script>
</body>
</html>
