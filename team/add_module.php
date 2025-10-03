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

    <form id="curriculum-form" action="save_module.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <div id="modules-container"></div>
        <div id="add-module-area" class="mt-6">
            <div id="add-module-prompt" class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-yellow-500 hover:text-yellow-600 transition flex items-center justify-center gap-2 cursor-pointer"><i data-lucide="plus"></i> Bölüm Ekle</div>
            <div id="add-module-form" class="card hidden"><label for="new_module_title" class="font-bold text-lg mb-2 block">Bölüm Adı</label><input type="text" id="new_module_title" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Örn: Giriş ve Temel Kavramlar"><div class="flex gap-4 mt-4"><button type="button" id="add-module-btn" class="btn">Bölümü Ekle</button><button type="button" id="cancel-add-module-btn" class="btn btn-secondary">İptal</button></div></div>
        </div>
        <div class="text-right mt-8"><button type="submit" class="btn text-lg"><i data-lucide="save" class="w-5 h-5 mr-2"></i>İçeriği Kaydet ve Bitir</button></div>
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
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const TYPE_LIMITS = { text: 100, video: 50, document: 50, youtube: 50 };

// Yardımcılar
        function getTypeCount(container, type) {
            return container.querySelectorAll(`input[name*="[type]"][value="${type}"]`).length;
        }
        function canAdd(container, type) {
            const limit = TYPE_LIMITS[type] ?? Infinity;
            return getTypeCount(container, type) < limit;
        }
        function refreshAddButtons(moduleEl) {
            const container = moduleEl.querySelector('.content-items-container');
            moduleEl.querySelectorAll('.add-content-btn').forEach((btn) => {
                const type = btn.dataset.type;
                const allowed = canAdd(container, type);
                btn.disabled = !allowed;
                btn.classList.toggle('opacity-50', !allowed);
                btn.classList.toggle('cursor-not-allowed', !allowed);
            });
        }
        function parseYouTubeId(input) {
            if (!input) return null;
            input = input.trim();

            // Sadece ID girdiyse (11–20 arası güvenli aralık)
            if (/^[a-zA-Z0-9_-]{10,20}$/.test(input)) return input;

            try {
                const u = new URL(input);
                if (u.hostname.includes('youtu.be')) {
                    const id = u.pathname.split('/').filter(Boolean)[0];
                    return id || null;
                }
                if (u.hostname.includes('youtube.com')) {
                    if (u.pathname.startsWith('/shorts/')) {
                        const id = u.pathname.split('/').filter(Boolean)[1];
                        return id || null;
                    }
                    const v = u.searchParams.get('v');
                    if (v) return v;
                    if (u.pathname.startsWith('/embed/')) {
                        const id = u.pathname.split('/').filter(Boolean)[1];
                        return id || null;
                    }
                }
            } catch (_) { /* URL değilse */ }

            return null;
        }

        function buildYouTubePreviewHTML(embedId) {
            return `
    <div class="mt-3">
      <div class="aspect-video">
        <iframe
          class="w-full h-full"
          src="https://www.youtube.com/embed/${encodeURIComponent(embedId)}"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowfullscreen>
        </iframe>
      </div>
    </div>
  `;
        }



        lucide.createIcons();
        const curriculumForm = document.getElementById('curriculum-form');
        const modulesContainer = document.getElementById('modules-container');
        const addModuleArea = document.getElementById('add-module-area');
        const addModulePrompt = document.getElementById('add-module-prompt');
        const addModuleForm = document.getElementById('add-module-form');
        const addModuleBtn = document.getElementById('add-module-btn');
        const cancelAddModuleBtn = document.getElementById('cancel-add-module-btn');
        const newModuleTitleInput = document.getElementById('new_module_title');

        let isFormDirty = false, activeEditor = null, savedSelection = null;
        const linkModal = document.getElementById('link-modal');
        const pasteModal = document.getElementById('paste-modal');
        const previewModal = document.getElementById('preview-modal');

        // === GÜVENLİK VE SAYFA AKIŞI ===
        curriculumForm.addEventListener('input', () => { isFormDirty = true; });
        window.addEventListener('beforeunload', (e) => { if (isFormDirty) { e.preventDefault(); e.returnValue = ''; } });
        curriculumForm.addEventListener('submit', () => { isFormDirty = false; });

        // === BÖLÜM YÖNETİMİ ===
        addModulePrompt.addEventListener('click', () => { addModulePrompt.classList.add('hidden'); addModuleForm.classList.remove('hidden'); newModuleTitleInput.focus(); });
        cancelAddModuleBtn.addEventListener('click', () => { addModuleForm.classList.add('hidden'); addModulePrompt.classList.remove('hidden'); });
        addModuleBtn.addEventListener('click', () => {
            const title = newModuleTitleInput.value.trim();
            if (title) { createModule({ title: title }); addModuleArea.style.display = 'none'; }
            else { alert('Bölüm adı boş bırakılamaz.'); }
        });

        function createModule(moduleData) {
            const moduleKey = `new_${Date.now()}`;
            const moduleElement = document.createElement('div');
            moduleElement.className = 'module-card';
            moduleElement.innerHTML = `
  <div class="module-header">
    <h3 class="flex-grow">${moduleData.title}</h3>
    <input type="hidden" name="modules[${moduleKey}][title]" value="${moduleData.title}">
  </div>
  <div class="module-content">
    <div class="content-items-container space-y-4"></div>
    <div class="flex gap-4 mt-6 border-t pt-4">
      <button type="button" class="add-content-btn btn btn-sm btn-secondary" data-type="text">
        <i data-lucide="type" class="w-4 h-4 mr-1"></i>Metin
      </button>
      <button type="button" class="add-content-btn btn btn-sm btn-secondary" data-type="video">
        <i data-lucide="video" class="w-4 h-4 mr-1"></i>Video
      </button>
      <button type="button" class="add-content-btn btn btn-sm btn-secondary" data-type="document">
        <i data-lucide="file-plus" class="w-4 h-4 mr-1"></i>Döküman
      </button>
      <!-- YENİ: YouTube -->
            <button type="button" class="add-content-btn btn btn-sm btn-secondary" data-type="youtube">
            <i data-lucide="link-2" class="w-4 h-4 mr-1"></i>YouTube
            </button>
            </div>
            </div>`;
            modulesContainer.innerHTML = '';
            modulesContainer.appendChild(moduleElement);
            attachModuleListeners(moduleElement, moduleKey);
            lucide.createIcons();
            refreshAddButtons(moduleElement);


        }

        // === İÇERİK YÖNETİMİ ===
        function attachModuleListeners(moduleElement, moduleKey) {
              const contentContainer = moduleElement.querySelector('.content-items-container');
              makeSortable(contentContainer);

              moduleElement.querySelectorAll('.add-content-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                  const type = btn.dataset.type;
                  const contentContainer = moduleElement.querySelector('.content-items-container');

                  // LİMİT KONTROLÜ
                  if (!canAdd(contentContainer, type)) {
                    alert(`Bu modülde "${type}" içeriği en fazla ${TYPE_LIMITS[type]} kez eklenebilir.`);
                    return;
                  }

                  if (type === 'text') {
                    try {
                      const clipboardText = await navigator.clipboard.readText();
                      if (clipboardText.trim() && clipboardText.length < 750) {
                        pasteModal.classList.add('show');
                        document.getElementById('paste-confirm-btn').onclick = () => {
                          addContentItem(contentContainer, type, moduleKey, { data: clipboardText });
                          pasteModal.classList.remove('show');
                          refreshAddButtons(moduleElement);
                        };
                        document.getElementById('paste-cancel-btn').onclick = () => {
                          addContentItem(contentContainer, type, moduleKey);
                          pasteModal.classList.remove('show');
                          refreshAddButtons(moduleElement);
                        };
                      } else {
                        throw new Error("Pano boş veya limit aşıldı");
                      }
                    } catch (err) {
                      addContentItem(contentContainer, type, moduleKey);
                      refreshAddButtons(moduleElement);
                    }
                    // <-- BURADA 'text' if bloğunu düzgün kapatıyoruz
                  } else if (type === 'youtube') {
                    // YouTube için burada HTML kurma yok; addContentItem halledecek
                    addContentItem(contentContainer, 'youtube', moduleKey);
                    refreshAddButtons(moduleElement);
                  } else {
                    // video / document vs.
                    addContentItem(contentContainer, type, moduleKey);
                    refreshAddButtons(moduleElement);
                  }
                });
              });
        }




        function addContentItem(container, type, moduleKey, contentData = {}) {
            const contentKey = `new_c_${Date.now()}`;
            const itemElement = document.createElement('div');
            itemElement.className = 'content-item';
            const data = contentData.data || '';
            let typeText = '';
            let icon = '';
            let bodyHTML = '';

            switch (type) {
              case 'text':
                typeText = 'Metin İçeriği';
                icon = 'type';
                break;
              case 'video':
                typeText = 'Video';
                icon = 'video';
                break;
              case 'document':
                typeText = 'Döküman';
                icon = 'file-text';
                break;
              case 'youtube':
                typeText = 'YouTube';
                icon = 'link-2'; // lucide’da youtube yok; istersen başka ikon seçebilirsin
                break;
              default:
                typeText = 'İçerik';
                icon = 'file';
            }


            if (type === 'text') {
  const editorData = data || '<p>Metninizi buraya yazın...</p>';
  bodyHTML = `<div class="editor-toolbar"> ... </div>
            <div class="editable-content" contenteditable="true" spellcheck="false">${editorData}</div>
            <textarea class="hidden" name="modules[${moduleKey}][contents][${contentKey}][paragraph]">${editorData}</textarea>`;
} else if (type === 'youtube') {
  const initial = (data || '').trim();
  bodyHTML = `
            <div class="space-y-2">
            <label class="font-medium text-gray-700">YouTube URL veya Video ID</label>
            <div class="flex gap-2">
            <input type="text" class="yt-url-input w-full p-2 border border-gray-300 rounded-lg"
            placeholder="https://youtu.be/dQw4w9WgXcQ veya video ID" value="${initial}">
            <button type="button" class="yt-apply btn btn-sm">Uygula</button>
            <button type="button" class="yt-clear btn btn-sm btn-secondary">Temizle</button>
            </div>
            <p class="yt-error text-red-600 text-sm" style="display:none;"></p>
            <div class="yt-preview"></div>
            <input type="hidden"
            name="modules[${moduleKey}][contents][${contentKey}][youtube_url]"
            class="yt-hidden" value="${initial}">
            </div>
            `;
            } else {
            const accept = (type === 'video') ? 'video/*' : '.pdf,.doc,.docx,.ppt,.pptx';
            bodyHTML = `
    <div class="upload-container">
      <div class="file-display hidden"></div>
      <div class="horizontal-upload-card">
        <div class="upload-preview-thumb"><i data-lucide="${icon}"></i></div>
        <div class="upload-drop-area">
          <p>Dosyayı buraya sürükleyin veya <span class="font-bold text-yellow-600">tıklayın</span></p>
        </div>
      </div>
    </div>
    <input type="file" class="hidden-file-input"
           name="modules[${moduleKey}][contents][${contentKey}][file]"
           accept="${accept}" style="display:none;">
    <input type="hidden"
           name="modules[${moduleKey}][contents][${contentKey}][existing_file]"
           value="${data}">
  `;
            }

            itemElement.innerHTML = `<div class="content-item-header"><i  class="text-gray-500"></i><span class="content-item-title">${typeText}</span><div class="ml-auto flex items-center gap-2"><button type="button" class="action-button move-up-btn" title="Yukarı Taşı"><i data-lucide="chevron-up"></i></button><button type="button" class="action-button move-down-btn" title="Aşağı Taşı"><i data-lucide="chevron-down"></i></button><button type="button" class="action-button delete-content-btn text-red-500" title="Sil"><i data-lucide="trash-2"></i></button></div></div><div class="content-item-body">${bodyHTML}</div><input type="hidden" name="modules[${moduleKey}][contents][${contentKey}][type]" value="${type}"><input type="hidden" name="modules[${moduleKey}][contents][${contentKey}][sort_order]" value="">`;

            container.appendChild(itemElement);
            updateSortOrder(container);
            attachContentItemListeners(itemElement, type);
            lucide.createIcons();
        }

        function attachContentItemListeners(itemElement, type) {
            itemElement.querySelector('.delete-content-btn').addEventListener('click', () => {
                if (confirm('İçeriği sil?')) {
                    const moduleEl = itemElement.closest('.module-card');
                    itemElement.remove();
                    updateSortOrder(itemElement.parentElement);
                    // SİLİNDİ: Ekleme butonlarını güncelle
                    refreshAddButtons(moduleEl);
                }
            });
            itemElement.querySelector('.move-up-btn').addEventListener('click', (e) => moveContent(e.currentTarget, 'up'));
            itemElement.querySelector('.move-down-btn').addEventListener('click', (e) => moveContent(e.currentTarget, 'down'));

            if (type === 'text') {
                const editor = itemElement.querySelector('.editable-content');
                const toolbar = itemElement.querySelector('.editor-toolbar');
                const textarea = itemElement.querySelector('textarea');
                editor.addEventListener('focus', () => { activeEditor = editor; });
                editor.addEventListener('blur', () => { savedSelection = saveSelection(); });
                editor.addEventListener('input', () => { textarea.value = editor.innerHTML; isFormDirty = true; });
                editor.addEventListener('keyup', () => updateToolbarState(toolbar));
                editor.addEventListener('mouseup', () => updateToolbarState(toolbar));
                toolbar.addEventListener('change', (e) => handleEditorCommand(e.target.dataset.action, e.target.value));
                toolbar.addEventListener('click', (e) => { const button = e.target.closest('button'); if (button) handleEditorCommand(button.dataset.action); });
            } else if (type === 'youtube') {
                const urlInput = itemElement.querySelector('.yt-url-input');
                const applyBtn = itemElement.querySelector('.yt-apply');
                const clearBtn = itemElement.querySelector('.yt-clear');
                const err = itemElement.querySelector('.yt-error');
                const prev = itemElement.querySelector('.yt-preview');
                const hidden = itemElement.querySelector('.yt-hidden');

                function setPreviewFromInput() {
                    err.style.display = 'none';
                    const id = parseYouTubeId(urlInput.value);
                    if (!id) {
                        prev.innerHTML = '';
                        hidden.value = '';
                        err.textContent = 'Geçerli bir YouTube bağlantısı veya video ID girin.';
                        err.style.display = 'block';
                        return;
                    }
                    hidden.value = `https://youtu.be/${id}`; // canonical
                    prev.innerHTML = buildYouTubePreviewHTML(id);
                }

                applyBtn.addEventListener('click', setPreviewFromInput);
                urlInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); setPreviewFromInput(); } });
                clearBtn.addEventListener('click', () => {
                    urlInput.value = '';
                    hidden.value = '';
                    err.style.display = 'none';
                    prev.innerHTML = '';
                });

                // Eğer başlangıçta değer geldiyse önizleme oluştur
                if (urlInput.value.trim()) {
                    const id = parseYouTubeId(urlInput.value.trim());
                    if (id) prev.innerHTML = buildYouTubePreviewHTML(id);
                }
            }else {
                const dropArea = itemElement.querySelector('.upload-drop-area');
                const fileInput = itemElement.querySelector('.hidden-file-input');
                dropArea.addEventListener('click', () => fileInput.click());
                dropArea.addEventListener('dragover', (e) => { e.preventDefault(); dropArea.classList.add('drag-over'); });
                dropArea.addEventListener('dragleave', () => dropArea.classList.remove('drag-over'));
                dropArea.addEventListener('drop', (e) => { e.preventDefault(); dropArea.classList.remove('drag-over'); if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; showFileInPreview(itemElement, fileInput.files[0]); } });
                fileInput.addEventListener('change', () => { if (fileInput.files.length) showFileInPreview(itemElement, fileInput.files[0]); });
            }
        }

        // === SIRALAMA FONKSİYONLARI ===
        function makeSortable(container) {
            let draggingElement = null;
            container.addEventListener('dragstart', e => { if (e.target.closest('.drag-handle')) { draggingElement = e.target.closest('.content-item'); setTimeout(() => draggingElement.classList.add('dragging'), 0); } });
            container.addEventListener('dragend', () => { if (draggingElement) { draggingElement.classList.remove('dragging'); draggingElement = null; updateSortOrder(container); } });
            container.addEventListener('dragover', e => { e.preventDefault(); const afterElement = getDragAfterElement(container, e.clientY); if(draggingElement) container.insertBefore(draggingElement, afterElement); });
        }
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.content-item:not(.dragging)')];
            return draggableElements.reduce((closest, child) => { const box = child.getBoundingClientRect(); const offset = y - box.top - box.height / 2; if (offset < 0 && offset > closest.offset) { return { offset, element: child }; } else { return closest; } }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
        function moveContent(button, direction) {
            const item = button.closest('.content-item'); if (button.classList.contains('disabled')) return;
            const container = item.parentElement;
            if (direction === 'up' && item.previousElementSibling) { container.insertBefore(item, item.previousElementSibling); }
            else if (direction === 'down' && item.nextElementSibling) { container.insertBefore(item.nextElementSibling, item); }
            updateSortOrder(container);
        }
        function updateSortOrder(container) {
            const items = container.querySelectorAll('.content-item');
            items.forEach((item, index) => {
                item.querySelector('input[name*="[sort_order]"]').value = index;
                item.querySelector('.move-up-btn').classList.toggle('disabled', index === 0);
                item.querySelector('.move-down-btn').classList.toggle('disabled', index === items.length - 1);
            });
        }

        // === DOSYA ÖNİZLEME FONKSİYONLARI ===
        function showFileInPreview(itemElement, fileObject) {
            const type = itemElement.querySelector('input[name*="[type]"]').value;
            const uploadContainer = itemElement.querySelector('.upload-container');
            const fileDisplay = uploadContainer.querySelector('.file-display');
            const uploadCard = uploadContainer.querySelector('.horizontal-upload-card');
            const filePath = URL.createObjectURL(fileObject);
            fileDisplay.innerHTML = `<div class="upload-preview-thumb">${type === 'video' ? `<video src="${filePath}"></video>` : '<i data-lucide="file-text"></i>'}</div><div class="flex-grow min-w-0"><div class="file-name" title="${fileObject.name}">${fileObject.name}</div><div class="file-size">${(fileObject.size / 1024 / 1024).toFixed(2)} MB</div></div><div class="file-actions"><button type="button" class="action-button preview-btn" title="Önizle"><i data-lucide="eye"></i></button><button type="button" class="action-button change-btn" title="Değiştir"><i data-lucide="refresh-cw"></i></button></div>`;
            fileDisplay.querySelector('.preview-btn').addEventListener('click', () => showPreview(filePath, type));
            fileDisplay.querySelector('.change-btn').addEventListener('click', () => itemElement.querySelector('.hidden-file-input').click());
            uploadCard.classList.add('hidden');
            fileDisplay.classList.remove('hidden');
            lucide.createIcons({nodes: [fileDisplay]});
        }
        function showPreview(filePath, fileType) {
            let content = '';
            const fileExtension = filePath.split('.').pop().toLowerCase();
            if (fileType === 'video') { content = `<video src="${filePath}" controls autoplay></video>`; }
            else if (fileType === 'document') {
                if (fileExtension === 'pdf') { content = `<iframe src="${filePath}"></iframe>`; }
                else if (['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'].includes(fileExtension) && !filePath.startsWith('blob:')) {
                    const viewerUrl = `https://docs.google.com/gview?url=${encodeURIComponent(window.location.origin + '/' + filePath)}&embedded=true`;
                    content = `<iframe src="${viewerUrl}"></iframe>`;
                } else { content = `<div class="p-8 text-center text-white"><h3>Bu dosya türü için önizleme desteklenmiyor veya dosyanın önce kaydedilmesi gerekiyor.</h3></div>`; }
            }
            document.getElementById('preview-modal-content').innerHTML = content;
            previewModal.classList.remove('hidden');
        }
        document.getElementById('preview-modal-close').addEventListener('click', () => previewModal.classList.add('hidden'));

        // === METİN EDİTÖRÜ FONKSİYONLARI ===
        function saveSelection() { if (window.getSelection) { const sel = window.getSelection(); if (sel.getRangeAt && sel.rangeCount) return sel.getRangeAt(0); } return null; }
        function restoreSelection(range) { if (range && window.getSelection) { const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range); } }
        function handleEditorCommand(command, value = null) {
            if (!command || !activeEditor) return; activeEditor.focus();
            if (command === 'createLink') {
                savedSelection = saveSelection();
                if(!savedSelection || savedSelection.collapsed) { alert("Lütfen link eklemek için bir metin seçin."); return; }
                linkModal.classList.add('show'); return;
            }
            document.execCommand(command, false, value);
            updateToolbarState(activeEditor.closest('.content-item-body').querySelector('.editor-toolbar'));
        }
        function updateToolbarState(toolbar) { if (!toolbar) return; toolbar.querySelectorAll('[data-action]').forEach(button => { if (button.tagName !== 'SELECT' && document.queryCommandState(button.dataset.action)) { button.classList.add('is-active'); } else { button.classList.remove('is-active'); } }); }
        document.getElementById('apply-link-btn').addEventListener('click', () => {
            const url = document.getElementById('link-url').value;
            if (url && activeEditor) { activeEditor.focus(); restoreSelection(savedSelection); document.execCommand('createLink', false, url); }
            linkModal.classList.remove('show'); document.getElementById('link-url').value = '';
        });
        document.getElementById('cancel-link-btn').addEventListener('click', () => { linkModal.classList.remove('show'); });
    });
</script></body>
</html>
