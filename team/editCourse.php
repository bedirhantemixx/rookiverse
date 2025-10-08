<?php
session_start();
$projectRoot = dirname(__DIR__);
require_once($projectRoot . '/config.php');

if (!isset($_SESSION['team_logged_in']) || !isset($_SESSION['team_db_id'])) {
    header('Location: ../team-login.php?error=session_expired'); exit();
}

$pdo = get_db_connection();

$course_id = $_GET['id'] ?? null;
if (!$course_id) { header('Location: panel.php'); exit(); }

// --- Mevcut kurs verilerini çek (front-end prefill için) ---
$stmt = $pdo->prepare("
  SELECT id, title, about_text, goal_text, learnings_text, category_id, level, status, is_deleted,
         cover_image_url, intro_video_url
  FROM courses
  WHERE id = :id AND team_db_id = :team
");
$stmt->execute([':id'=>$course_id, ':team'=>$_SESSION['team_db_id']]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) { header('Location: panel.php'); exit(); }

// Sadece approved kategoriler
$categories = $pdo->query("SELECT id, name FROM categories WHERE status='approved' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$catIDs = $pdo->query("SELECT id FROM categories WHERE status='approved' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$page_title = "Kursu Düzenle";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars($course['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/navbar.css">

    <style>
        :root{ --rv:#E5AE32; }

        /* Buttons */
        .btn{
            display:inline-flex; align-items:center; gap:.5rem;
            background:var(--rv); color:#fff; padding:.5rem 1rem;
            border-radius:.5rem; font-weight:600; border:1px solid transparent;
            transition:filter .2s, opacity .2s;
        }
        .btn:hover{ filter:brightness(.95); }
        .btn:disabled{ opacity:.6; cursor:not-allowed; }

        .btn-outline{
            display:inline-flex; align-items:center; gap:.5rem;
            background:#fff; color:#111827; padding:.5rem 1rem;
            border-radius:.5rem; font-weight:600; border:1px solid #d1d5db;
            transition:border-color .2s, color .2s, background .2s;
        }
        .btn-outline:hover{ border-color:var(--rv); }

        .btn-muted{
            display:inline-flex; align-items:center; gap:.5rem;
            background:#e5e7eb; color:#374151; padding:.5rem 1rem;
            border-radius:.5rem; font-weight:600; border:1px solid #e5e7eb;
        }
        .btn-sm{ padding:.375rem .75rem; font-size:.875rem; border-radius:.375rem; }

        /* Cards & inputs */
        .section-card{
            background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem;
        }
        .input-card{
            background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1rem;
        }
        .form-input,.form-select,.form-textarea{
            width:100%; border:1px solid #d1d5db; border-radius:.5rem;
            padding:.5rem .75rem; outline:none; transition: box-shadow .15s, border-color .15s;
            background:#fff;
        }
        .form-textarea{ min-height:100px; }
        .form-input:focus,.form-select:focus,.form-textarea:focus{
            border-color:var(--rv); box-shadow:0 0 0 3px rgba(229,174,50,.25);
        }

        /* Drop zones */
        .drop-zone{
            border:2px dashed #d1d5db; border-radius:.75rem; padding:1.5rem; text-align:center;
            background:#fff; cursor:pointer; transition:border-color .2s, background .2s;
        }
        .drop-zone.drag-over{ border-color:var(--rv); background:#fffbeb; }
        .hidden-input{ display:none; }

        /* Progress bar */
        .progress-bar-container{
            position:relative; width:100%; height:.75rem; background:#e5e7eb; border-radius:9999px; overflow:hidden;
        }
        .progress-bar-fill{
            position:absolute; left:0; top:0; height:100%; width:0%;
            background:var(--rv); transition:width .2s linear;
        }
        .progress-bar-text{ display:inline-block; margin-top:.5rem; font-size:.875rem; color:#374151; }

        /* Previews */
        .preview-container img, .preview-container video{
            width:100%; border:1px solid #e5e7eb; border-radius:.5rem; display:block; background:#fff;
        }
        .aspect-video{ aspect-ratio:16/9; object-fit:cover; }

        /* Badges */
        .badge{
            display:inline-block; font-size:.75rem; font-weight:700;
            padding:.25rem .5rem; border-radius:9999px; background:#fef3c7; color:#92400e;
        }

        /* Popup */
        .popup-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:50; }
        .popup-overlay.show{ display:flex; }
        .popup-content{ background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.5rem; width:min(520px,92vw); }
        .error-text{ color:#dc2626; font-size:.875rem; margin-top:.5rem; display:none; }
        .input-error{ border-color:#dc2626 !important; box-shadow:0 0 0 3px rgba(220,38,38,.15) !important; }

        /* Utility helpers if needed */
        .space-y-4 > * + *{ margin-top:1rem; } /* only if Tailwind classes elsewhere yet failing */
    </style>

</head>
<body class="bg-gray-100">
<?php require_once $projectRoot . '/navbar.php'; ?>

<div class="max-w-6xl mx-auto py-10 px-4 space-y-8">
    <?php
    if ($course['is_deleted']):
    ?>
        Bu kurs silinmiş.
    <?php
    else:
    ?>
    <div class="flex flex-wrap gap-3 justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($course['title']) ?></h1>
            <p class="text-gray-600">Kurs ID: #<?= (int)$course['id'] ?> · Takım #<?= (int)$_SESSION['team_number'] ?></p>
        </div>
        <div class="flex gap-2">
            <a href="panel.php" class="btn-muted"><i data-lucide="arrow-left"></i> Panele Dön</a>
            <button form="meta-form" type="submit" class="btn"><i data-lucide="save"></i> Değişiklikleri Kaydet</button>
        </div>
    </div>

    <!-- Üst: Meta Bilgiler -->
    <form id="meta-form" class="section-card grid grid-cols-1 lg:grid-cols-2 gap-5" method="POST" action="course_update_meta.php">
        <input type="hidden" name="id" value="<?= (int)$course_id ?>"/>


        <!-- YouTube linkini meta-form'a taşıyan hidden alan -->
        <input type="hidden" id="intro_video_url" name="intro_video_url"
               value="<?= htmlspecialchars($course['intro_video_url'] ?? '') ?>">



        <div class="space-y-4">
            <div class="input-card">
                <label for="title" class="font-semibold text-gray-800">Kurs Adı</label>
                <input id="title" name="title" class="form-input" required
                       value="<?= htmlspecialchars($course['title'] ?? '') ?>"
                       placeholder="Kursunuza dikkat çekici bir başlık verin...">
            </div>

            <div class="input-card">
                <label for="about" class="font-semibold text-gray-800">Kurs Hakkında</label>
                <textarea id="about" name="about_text" class="form-textarea min-h-[120px]" required
                          placeholder="Bu kursta nelerin anlatıldığını genel olarak açıklayın..."><?= htmlspecialchars($course['about_text'] ?? '') ?></textarea>
            </div>

            <div class="input-card">
                <label for="goal" class="font-semibold text-gray-800">Kurs Amacı</label>
                <textarea id="goal" name="goal_text" class="form-textarea min-h-[100px]" required
                          placeholder="Bu kursu tamamlayan bir öğrencinin neyi başarmış olacağını anlatın..."><?= htmlspecialchars($course['goal_text'] ?? '') ?></textarea>
            </div>

            <div class="input-card">
                <label for="learnings" class="font-semibold text-gray-800">Ne Öğreteceksiniz?</label>
                <textarea id="learnings" name="learnings_text" class="form-textarea min-h-[100px]" required
                          placeholder="Öğreteceğiniz konuları madde madde yazın (her satır yeni bir madde)..."><?= htmlspecialchars($course['learnings_text'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="space-y-4">
            <div class="input-card">
                <label for="category_id" class="font-semibold text-gray-800">Kategori</label>
                <div class="flex flex-col sm:flex-row gap-3 items-center">
                    <select id="category_id" name="category_id" class="form-select w-full" required>

                        <option value="" disabled <?= empty($course['category_id']) ? 'selected':''; ?>>Lütfen bir kategori seçin...</option>
                        <?php
                        if (!in_array($course['category_id'], $catIDs)):
                            $cat = getCategory($course['category_id']);
                        ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)$cat['id']===(int)$course['category_id'])?'selected':''; ?>>
                                <?= htmlspecialchars($cat['name']) ?> (Onay Bekliyor)
                            </option>
                        <?php endif;?>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)$cat['id']===(int)$course['category_id'])?'selected':''; ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" onclick="openPopup('category-popup')" class="w-full sm:w-auto btn-outline whitespace-nowrap text-sm">
                        <i data-lucide="lightbulb"></i> Kategori Öner
                    </button>
                </div>
            </div>

            <div class="input-card">
                <label for="level" class="font-semibold text-gray-800">Seviye</label>
                <select id="level" name="level" class="form-select" required>
                    <?php
                    $levels = ['Başlangıç','Orta','İleri'];
                    $curLvl = $course['level'] ?? '';
                    foreach($levels as $lvl){
                        $sel = ($lvl === $curLvl)?'selected':'';
                        echo "<option value=\"{$lvl}\" {$sel}>{$lvl}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="input-card">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-gray-800">Durum</span>
                    <span class="badge"><?= htmlspecialchars($course['status'] ?? 'Taslak') ?></span>
                </div>
                <p class="text-sm text-gray-500">Durum yönetimini admin paneli belirleyebilir.</p>
            </div>
        </div>
    </form>

    <!-- Alt: Medya Yükleme -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="section-card">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                <i data-lucide="image"></i> Kurs Kapağı
            </h2>

            <div id="cover-drop-zone" class="drop-zone <?= !empty($course['cover_image_url']) ? 'hidden':'' ?>">
                <input type="file" id="cover-image-input" class="hidden-input" accept="image/jpeg,image/png">
                <i data-lucide="upload-cloud" class="mx-auto mb-2"></i>
                <p class="font-semibold">Sürükleyin veya Tıklayın</p>
                <p class="text-sm text-gray-500">Sadece JPG ve PNG formatları</p>
            </div>

            <div id="cover-progress-container" class="progress-bar-container hidden mt-2">
                <div id="cover-progress-bar" class="progress-bar-fill"></div>
            </div>
            <span id="cover-progress-text" class="progress-bar-text hidden">0%</span>

            <div id="cover-preview-container" class="preview-container <?= empty($course['cover_image_url']) ? 'hidden':'' ?> mt-3">
                <?php if(!empty($course['cover_image_url'])): ?>
                    <img id="cover-preview" src="../<?= htmlspecialchars($course['cover_image_url']) ?>" class="aspect-video" alt="Kapak">
                <?php else: ?>
                    <img id="cover-preview" class="aspect-video" alt="Kapak önizleme">
                <?php endif; ?>
                <div class="mt-3 flex gap-2">
                    <button id="cover-replace" class="btn-outline"><i data-lucide="refresh-ccw"></i> Değiştir</button>
                    <button id="cover-remove" class="btn-outline text-red-600 border-red-300 hover:border-red-500"><i data-lucide="trash-2"></i> Kaldır</button>
                </div>
            </div>
        </div>

        <div class="section-card">

            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                <i data-lucide="video"></i> Tanıtım Videosu
            </h2>

            <!-- YouTube Linki (opsiyonel) -->
            <div class="mb-4 input-card">
                <label for="yt-input" class="font-semibold text-gray-800">YouTube Tanıtım Linki (opsiyonel)</label>
                <div class="flex gap-2 mt-2">
                    <input id="yt-input" type="text" class="form-input flex-1"
                           placeholder="Örn: https://youtu.be/dQw4w9WgXcQ veya video ID"
                           value="<?= htmlspecialchars($course['intro_video_url'] ?? '') ?>">
                    <button id="yt-apply" class="btn-sm btn-outline" type="button">
                        <i data-lucide="check"></i> Kullan
                    </button>
                    <button id="yt-clear" class="btn-sm btn-outline text-red-600 border-red-300 hover:border-red-500" type="button">
                        <i data-lucide="x"></i> Temizle
                    </button>
                </div>
                <p id="yt-error" class="error-text" style="display:none;"></p>
            </div>

            <!-- YouTube önizleme (iframe) -->
            <div id="yt-preview-container" class="preview-container hidden mt-3">
                <div class="aspect-video">
                    <iframe id="yt-iframe" class="w-full h-full" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                    Dosya yükleme ile YouTube linki birbirini dışlar. YouTube kullandığınızda dosya yükleme gizlenir.
                </div>
            </div>


            <div id="video-drop-zone" class="drop-zone <?= !empty($course['intro_video_url']) ? 'hidden':'' ?>">
                <input type="file" id="video-input" class="hidden-input" accept="video/mp4">
                <i data-lucide="upload-cloud" class="mx-auto mb-2"></i>
                <p class="font-semibold">Sürükleyin veya Tıklayın</p>
                <p class="text-sm text-gray-500">Sadece MP4 formatı</p>
            </div>

            <div id="video-progress-container" class="progress-bar-container hidden mt-2">
                <div id="video-progress-bar" class="progress-bar-fill"></div>
            </div>
            <span id="video-progress-text" class="progress-bar-text hidden">0%</span>

            <div id="video-preview-container" class="preview-container <?= empty($course['intro_video_url']) ? 'hidden':'' ?> mt-3">
                <?php if(!empty($course['intro_video_url'])): ?>
                    <video id="video-preview" controls src="../<?= htmlspecialchars($course['intro_video_url']) ?>"></video>
                <?php else: ?>
                    <video id="video-preview" controls></video>
                <?php endif; ?>
                <div class="mt-3 flex gap-2">
                    <button id="video-replace" class="btn-outline"><i data-lucide="refresh-ccw"></i> Değiştir</button>
                    <button id="video-remove" class="btn-outline text-red-600 border-red-300 hover:border-red-500"><i data-lucide="trash-2"></i> Kaldır</button>
                </div>
            </div>
        </div>

    </div>

    <div class="flex justify-end gap-2">
        <a href="manage_curriculum.php?id=<?= (int)$course_id ?>" class="btn-outline">
            <i data-lucide="list"></i> İçerik Yönetimine Geç
        </a>
        <button form="meta-form" type="submit" class="btn">
            <i data-lucide="save"></i> Değişiklikleri Kaydet
        </button>
    </div>
</div>

<!-- Kategori Önerme Pop-up -->
<div id="category-popup" class="popup-overlay">
    <div class="popup-content">
        <h2 class="text-2xl font-bold mb-4">Yeni Kategori Öner</h2>
        <div>
            <label for="new_category_name" class="block text-left font-semibold text-gray-700 mb-2">Önerdiğiniz Kategori Adı</label>
            <input type="text" id="new_category_name" class="form-input w-full" placeholder="Yeni kategori adı...">
            <div id="category-error-message" class="error-text"></div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="button" onclick="submitCategory()" class="btn w-full">Öneriyi Gönder</button>
            <button type="button" onclick="closePopup('category-popup')" class="w-full btn-muted">İptal</button>
        </div>
    </div>

    <?php
    endif;
    ?>
</div>

<script>
    lucide.createIcons();

    // --- YouTube yardımcıları ---
    function parseYouTubeId(input) {
        if (!input) return null;
        input = input.trim();

        // Eğer sadece ID verdiyse (11-12 karakterlik tipik ID'ler)
        if (/^[a-zA-Z0-9_-]{10,20}$/.test(input)) return input;

        try {
            const u = new URL(input);
            // youtu.be/<id>
            if (u.hostname.includes('youtu.be')) {
                const id = u.pathname.split('/').filter(Boolean)[0];
                return id || null;
            }
            // youtube.com/watch?v=<id>
            if (u.hostname.includes('youtube.com')) {
                // shorts -> /shorts/<id>
                if (u.pathname.startsWith('/shorts/')) {
                    const id = u.pathname.split('/').filter(Boolean)[1];
                    return id || null;
                }
                const v = u.searchParams.get('v');
                if (v) return v;
                // /embed/<id>
                if (u.pathname.startsWith('/embed/')) {
                    const id = u.pathname.split('/').filter(Boolean)[1];
                    return id || null;
                }
            }
        } catch (_) {
            // URL değilse ve regex tutmadıysa null dön
        }
        return null;
    }

    function setYouTubePreview(videoId) {
        const ytPrev = document.getElementById('yt-preview-container');
        const iframe = document.getElementById('yt-iframe');
        const hidden = document.getElementById('intro_video_url');
        const err = document.getElementById('yt-error');

        if (videoId) {
            iframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent(videoId);
            ytPrev.classList.remove('hidden');
            hidden.value = 'https://youtu.be/' + videoId; // db'ye canonical bir değer yazalım
            err.style.display = 'none';

            // YouTube aktifken dosya yükleme UI'ını gizle
            document.getElementById('video-drop-zone')?.classList.add('hidden');
            document.getElementById('video-preview-container')?.classList.add('hidden');
            document.getElementById('video-progress-container')?.classList.add('hidden');
            document.getElementById('video-progress-text')?.classList.add('hidden');
        } else {
            iframe.removeAttribute('src');
            ytPrev.classList.add('hidden');
            hidden.value = '';

            // YouTube kapalıyken dosya yükleme UI'ını göster (eğer dosya yoksa)
            const hasFilePreview = document.getElementById('video-preview')?.getAttribute('src');
            if (!hasFilePreview) {
                document.getElementById('video-drop-zone')?.classList.remove('hidden');
            }
        }
    }

    function showYtError(msg) {
        const err = document.getElementById('yt-error');
        err.textContent = msg;
        err.style.display = 'block';
    }

    // --- YouTube UI eventleri ---
    (function initYouTubeControls() {
        const ytInput = document.getElementById('yt-input');
        const ytApply = document.getElementById('yt-apply');
        const ytClear = document.getElementById('yt-clear');

        if (!ytInput) return;

        // Sayfa ilk yüklenirken (db'den dolu geldiyse) hazırla
        const initial = ytInput.value;
        const initialId = parseYouTubeId(initial);
        if (initialId) {
            setYouTubePreview(initialId);
        }

        ytApply?.addEventListener('click', function () {
            const id = parseYouTubeId(ytInput.value);
            if (!id) {
                showYtError('Geçerli bir YouTube bağlantısı veya video ID girin.');
                return;
            }
            setYouTubePreview(id);
        });

        ytClear?.addEventListener('click', function () {
            ytInput.value = '';
            setYouTubePreview(null);
        });

        // Kullanıcı yazarken hata mesajını sakla
        ytInput?.addEventListener('input', function () {
            const err = document.getElementById('yt-error');
            if (err) err.style.display = 'none';
        });
    })();

    // --- Dosya yükleme ile YouTube'un birbirini dışlaması ---
    // Var olan setupDropZone'a küçük bir ek: dosya yüklenince YouTube'u sıfırla
    (function hookUploadExclusivity(){
        const origSetup = setupDropZone;
        setupDropZone = function(o){
            origSetup(o);
            const inp = document.getElementById(o.inputId);
            const hidden = document.getElementById('intro_video_url');
            const ytInput = document.getElementById('yt-input');

            if (o.uploadType === 'intro' && inp) {
                // Dosya seçildiğinde YT'yi iptal et
                inp.addEventListener('change', function(){
                    if (inp.files && inp.files.length) {
                        // YouTube görünümünü kapat
                        document.getElementById('yt-clear')?.click();
                        if (ytInput) ytInput.value = '';
                        if (hidden) hidden.value = '';
                    }
                });
            }
        };
    })();

    // ----- Unsaved changes guard -----
    (function(){
        let isDirty = false;
        document.addEventListener('input', () => isDirty = true);
        document.getElementById('meta-form').addEventListener('submit', ()=> isDirty=false);
        window.addEventListener('beforeunload', (e)=>{
            if(isDirty){ e.preventDefault(); e.returnValue = 'Değişiklikleri kaydetmediniz.'; }
        });
    })();

    // ----- Popup -----
    const openPopup  = id => document.getElementById(id).classList.add('show');
    const closePopup = id => {
        document.getElementById(id).classList.remove('show');
        // reset errors
        const err = document.getElementById('category-error-message');
        const inp = document.getElementById('new_category_name');
        err.style.display='none'; err.textContent=''; inp.classList.remove('input-error');
    };
    document.getElementById('new_category_name').addEventListener('input', e=>{
        const err = document.getElementById('category-error-message');
        err.style.display='none'; e.target.classList.remove('input-error');
    });

    async function submitCategory(){
        const inp = document.getElementById('new_category_name');
        const err = document.getElementById('category-error-message');
        const name = inp.value.trim();
        err.style.display='none'; inp.classList.remove('input-error');
        if(!name){ err.textContent='Kategori adı boş olamaz.'; err.style.display='block'; inp.classList.add('input-error'); return; }

        try{
            // BACKEND: bu endpointi sen ayarlayacaksın
            const res = await fetch('category_handler.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ name })
            });
            const data = await res.json();
            if(data.status === 'success'){
                const sel = document.getElementById('category_id');
                const opt = new Option(`${data.name} (Onay Bekliyor)`, data.id, true, true);
                sel.add(opt, 1);
                inp.value=''; closePopup('category-popup');
            }else{
                err.textContent = data.message || 'Bilinmeyen bir hata oluştu.'; err.style.display='block'; inp.classList.add('input-error');
            }
        }catch(e){
            err.textContent = 'Sunucuya erişilemedi.'; err.style.display='block'; inp.classList.add('input-error');
            console.error(e);
        }
    }

    // ----- Dropzone Setup (cover + video) -----
    function setupDropZone(o){
        const dz = document.getElementById(o.dropZoneId);
        const inp = document.getElementById(o.inputId);
        const prevC = document.getElementById(o.previewContainerId);
        const prevEl= document.getElementById(o.previewElementId);
        const pc = document.getElementById(o.progressContainerId);
        const pb = document.getElementById(o.progressBarId);
        const pt = document.getElementById(o.progressTextId);

        // Klik ve sürükle bırak
        dz?.addEventListener('click', ()=> inp.click());
        dz?.addEventListener('dragover', e=>{ e.preventDefault(); dz.classList.add('drag-over'); });
        dz?.addEventListener('dragleave', ()=> dz.classList.remove('drag-over'));
        dz?.addEventListener('drop', e=>{
            e.preventDefault(); dz.classList.remove('drag-over');
            if(e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
        });
        inp?.addEventListener('change', ()=> inp.files.length && handleFile(inp.files[0]));

        function handleFile(file){
            if(!o.allowedTypes.includes(file.type)){
                alert(o.errorMessage); inp.value=""; return;
            }
            upload(file);
        }
        function upload(file){
            const fd = new FormData();
            fd.append('file', file);
            fd.append('course_id', '<?= (int)$course_id ?>');
            fd.append('upload_type', o.uploadType); // 'cover' | 'intro'

            const xhr = new XMLHttpRequest();
            xhr.open('POST', o.uploadUrl, true);

            xhr.upload.addEventListener('progress', e=>{
                if(e.lengthComputable){
                    pc.classList.remove('hidden'); pt.classList.remove('hidden'); dz.classList.add('hidden');
                    const p = (e.loaded / e.total) * 100;
                    pb.style.width = p.toFixed(2) + '%'; pt.textContent = Math.round(p) + '%';
                }
            });
            xhr.onload = function(){
                if(xhr.status === 200){
                    try{
                        const r = JSON.parse(xhr.responseText);
                        if(r.success){
                            const reader = new FileReader();
                            reader.onload = ()=>{
                                prevEl.src = reader.result;
                                pc.classList.add('hidden'); pt.classList.add('hidden'); prevC.classList.remove('hidden');
                            };
                            reader.readAsDataURL(file);
                        }else{
                            alert('Yükleme hatası: ' + (r.message||''));
                            reset();
                        }
                    }catch(err){
                        alert('Geçersiz sunucu yanıtı.'); console.error(err, xhr.responseText); reset();
                    }
                }else{
                    alert('Sunucu hatası ('+xhr.status+').'); reset();
                }
            };
            xhr.onerror = function(){ alert('Ağ hatası.'); reset(); };
            xhr.send(fd);
        }
        function reset(){ pc.classList.add('hidden'); pt.classList.add('hidden'); dz.classList.remove('hidden'); inp.value=''; }

        // Değiştir/Kaldır butonları (varsa)
        if(o.replaceBtnId){
            document.getElementById(o.replaceBtnId)?.addEventListener('click', e=>{
                e.preventDefault(); prevC.classList.add('hidden'); dz.classList.remove('hidden'); inp.click();
            });
        }
        if(o.removeBtnId){
            document.getElementById(o.removeBtnId)?.addEventListener('click', async e=>{
                e.preventDefault();
                if(!confirm('Medyayı kaldırmak istediğinize emin misiniz?')) return;
                try{
                    // BACKEND: kaldırma endpointi sana ait
                    const res = await fetch('course_media_delete.php', {
                        method:'POST', headers:{'Content-Type':'application/json'},
                        body: JSON.stringify({ id: <?= (int)$course_id ?>, type: o.uploadType })
                    });
                    const r = await res.json();
                    if(r.success){
                        prevEl.removeAttribute('src'); prevC.classList.add('hidden'); dz.classList.remove('hidden');
                    }else{
                        alert('Kaldırılamadı: ' + (r.message||''));
                    }
                }catch(err){ alert('Sunucuya ulaşılamadı.'); console.error(err); }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        setupDropZone({
            dropZoneId:'cover-drop-zone', inputId:'cover-image-input',
            previewContainerId:'cover-preview-container', previewElementId:'cover-preview',
            progressContainerId:'cover-progress-container', progressBarId:'cover-progress-bar', progressTextId:'cover-progress-text',
            allowedTypes:['image/jpeg','image/png'], errorMessage:'Lütfen JPG veya PNG yükleyin.',
            uploadType:'cover', uploadUrl:'save_course_files.php',
            replaceBtnId:'cover-replace', removeBtnId:'cover-remove'
        });

        setupDropZone({
            dropZoneId:'video-drop-zone', inputId:'video-input',
            previewContainerId:'video-preview-container', previewElementId:'video-preview',
            progressContainerId:'video-progress-container', progressBarId:'video-progress-bar', progressTextId:'video-progress-text',
            allowedTypes:['video/mp4'], errorMessage:'Lütfen MP4 video yükleyin.',
            uploadType:'intro', uploadUrl:'save_course_files.php',
            replaceBtnId:'video-replace', removeBtnId:'video-remove'
        });
    });
    (function () {
        const form = document.getElementById('meta-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const fd = new FormData(form);
            const id = fd.get('id');

            try {
                const res = await fetch(form.action, { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    // ister reload, ister parametre ile geri dön
                    window.location.href = `editCourse.php?id=${encodeURIComponent(id)}&saved=1`;
                } else {
                    alert(data.message || 'Kaydedilemedi.');
                }
            } catch (err) {
                console.error(err);
                alert('Ağ hatası veya sunucu hatası.');
            }

        });

    })();
    // ---- saved toast (opsiyonel) ----
    (function(){
        const url = new URL(location.href);
        if (url.searchParams.get('saved') === '1') {
            // basit toast
            const t = document.createElement('div');
            t.textContent = 'Kurs bilgileri güncellendi.';
            Object.assign(t.style, {
                position:'fixed', right:'16px', top:'16px', background:'#E5AE32',
                color:'#fff', padding:'10px 14px', borderRadius:'8px', zIndex:9999,
                boxShadow:'0 8px 24px rgba(0,0,0,.2)', fontWeight:'600'
            });
            document.body.appendChild(t);
            setTimeout(()=> t.remove(), 2400);
            // paramı temizle
            url.searchParams.delete('saved');
            history.replaceState({}, '', url.toString());
        }
    })();

</script>
</body>
</html>
