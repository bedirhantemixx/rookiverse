<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kurs Modül Oynatıcı - Şablon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body{font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif}
        .hidden{display:none}
        .skip-indicator{
            position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
            background-color:rgba(0,0,0,.6);color:#fff;padding:1rem 1.5rem;border-radius:9999px;
            display:flex;align-items:center;gap:.5rem;font-size:1.125rem;font-weight:600;pointer-events:none;
            opacity:0;transition:opacity .5s ease-out
        }
        .skip-indicator.show{animation:skip-animation .8s ease-out forwards}
        @keyframes skip-animation{
            0%{opacity:0;transform:translate(-50%,-50%) scale(.8)}
            20%{opacity:1;transform:translate(-50%,-50%) scale(1)}
            80%{opacity:1;transform:translate(-50%,-50%) scale(1)}
            100%{opacity:0;transform:translate(-50%,-50%) scale(1)}
        }
        /* Basit toast */
        #toast{position:fixed;right:1rem;bottom:1rem;z-index:60}
    </style>
    <script>
        tailwind.config = { theme:{ extend:{ colors:{ 'custom-yellow':'#E5AE32' }}}}
    </script>
</head>
<body class="bg-gray-100">
<nav class="bg-white shadow-md p-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="#" class="rookieverse font-bold text-xl" style="font-family:'Sakana',system-ui,sans-serif!important;color:#E5AE32;">ROBOTİCTR</a>
    </div>
</nav>

<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <a href="#" class="inline-flex items-center text-custom-yellow hover:bg-custom-yellow/10 p-2 rounded-md">
                <i data-lucide="arrow-left" class="mr-2" style="width:18px;height:18px;"></i>
                Kurs Detayına Geri Dön
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Robotik ve Yapay Zeka Başlangıç Kursu</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- SOL / ANA İÇERİK -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Video -->
                <div class="bg-black rounded-lg overflow-hidden relative aspect-video">
                    <video id="module-video" class="w-full h-full" controls autoplay>
                        <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                        Tarayıcınız video etiketini desteklemiyor.
                    </video>
                    <div id="skip-backward-indicator" class="skip-indicator hidden"><i data-lucide="rewind" class="w-8 h-8"></i></div>
                    <div id="skip-forward-indicator" class="skip-indicator hidden"><i data-lucide="fast-forward" class="w-8 h-8"></i></div>
                </div>

                <!-- Modül başlık / açıklama -->
                <div class="p-6 bg-white rounded-lg shadow-md">
                    <div class="flex items-center space-x-2 mb-4">
                        <i data-lucide="play-circle" class="text-custom-yellow w-6 h-6"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Modül 2: Robotik Nedir?</h2>
                    </div>
                    <p class="text-lg leading-relaxed text-gray-700">
                        Bu modülde, robotik biliminin temel prensiplerini ve modern robotların nasıl çalıştığını öğreneceksiniz.
                    </p>

                    <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <a href="#" class="flex items-center text-gray-600 hover:text-custom-yellow transition-colors">
                            <i data-lucide="chevrons-left" class="w-5 h-5 mr-2"></i> Önceki Modül
                        </a>
                        <a href="#" class="flex items-center text-custom-yellow hover:text-custom-yellow/80 transition-colors">
                            Sonraki Modül <i data-lucide="chevrons-right" class="w-5 h-5 ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- METİN NOTLARI -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-text" class="text-custom-yellow w-5 h-5"></i>
                            <h3 class="text-lg font-bold text-gray-900">Metin Notları</h3>
                        </div>
                        <button id="print-all-notes" class="text-sm font-semibold text-custom-yellow hover:underline">Tümünü Yazdır</button>
                    </div>
                    <div id="notes-list" class="p-6 space-y-4">
                        <!-- JS ile doldurulacak -->
                    </div>
                </div>

                <!-- DOKÜMANLAR -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="files" class="text-custom-yellow w-5 h-5"></i>
                            <h3 class="text-lg font-bold text-gray-900">Dokümanlar</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <button id="download-all" class="text-sm font-semibold text-custom-yellow hover:underline">Tümünü İndir (.zip)</button>
                        </div>
                    </div>
                    <div id="docs-list" class="p-6 space-y-3">
                        <!-- JS ile doldurulacak -->
                    </div>
                </div>
            </div>

            <!-- SAĞ / KURS İÇERİĞİ -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Kurs İçeriği</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors hover:bg-gray-50">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-gray-200 text-gray-600">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-gray-900">Giriş ve Kurs Tanıtımı</h4></div>
                        </a>
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors bg-custom-yellow/10 border-l-4 border-custom-yellow">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-custom-yellow text-white">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-custom-yellow">Robotik Nedir?</h4></div>
                        </a>
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors hover:bg-gray-50">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-gray-200 text-gray-600">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-gray-900">Yapay Zeka Temelleri</h4></div>
                        </a>
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors hover:bg-gray-50">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-gray-200 text-gray-600">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-gray-900">İlk Robotunu Kodla</h4></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ÖNİZLEME MODALI (Metin ve Doküman için ortak) -->
<div id="preview-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="preview-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h4 id="preview-title" class="font-semibold text-gray-900">Önizleme</h4>
                <button id="preview-close" class="p-2 rounded-md hover:bg-gray-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="preview-body" class="p-0 max-h-[80vh] overflow-auto">
                <!-- Dinamik içerik -->
            </div>
            <div id="preview-actions" class="p-4 border-t flex items-center justify-end gap-3">
                <!-- Dinamik aksiyon butonları -->
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="hidden"></div>

<script>
    lucide.createIcons();

    // --- Video skip indikatorleri ---
    const video = document.getElementById('module-video');
    const videoContainer = video.parentElement;
    const skipBackwardIndicator = document.getElementById('skip-backward-indicator');
    const skipForwardIndicator  = document.getElementById('skip-forward-indicator');
    let skipIndicatorTimeout;

    function showSkipIndicator(indicator){
        clearTimeout(skipIndicatorTimeout);
        (indicator===skipForwardIndicator?skipBackwardIndicator:skipForwardIndicator).classList.remove('show');
        indicator.classList.remove('hidden');
        indicator.classList.add('show');
        lucide.createIcons();
        skipIndicatorTimeout=setTimeout(()=>{
            indicator.classList.remove('show');indicator.classList.add('hidden');
        },800);
    }

    document.addEventListener('keydown',(event)=>{
        const activeEl=document.activeElement;
        if(['INPUT','TEXTAREA'].includes(activeEl.tagName)||activeEl.isContentEditable) return;
        switch(event.key.toLowerCase()){
            case ' ':
            case 'k':
                event.preventDefault();
                video.paused?video.play():video.pause();break;
            case 'f':
                if(!document.fullscreenElement){videoContainer.requestFullscreen()}else{document.exitFullscreen()}
                break;
            case 'l':
                video.currentTime+=5;showSkipIndicator(skipForwardIndicator);break;
            case 'j':
                video.currentTime-=5;showSkipIndicator(skipBackwardIndicator);break;
        }
    });

    // --- Örnek veri (PHP'den doldurabilirsin) ---
    const notes = [
        {
            title: "Ders Özeti",
            html: `<p>Robotik; algılama, karar verme ve eyleme geçme döngüsüne dayanır. Bu modülde sensör türleri ve aktüatör temellerini gördük.</p>
             <ul class="list-disc pl-5"><li>Açık çevrim vs kapalı çevrim</li><li>Temel sensör sınıfları</li><li>Basit kontrol şeması</li></ul>`,
            updatedAt: "2025-09-10"
        },
        {
            title: "Terimler",
            html: `<p><b>DoF</b>: Degrees of Freedom. <b>IMU</b>: Inertial Measurement Unit.</p>`,
            updatedAt: "2025-09-12"
        }
    ];

    const documents = [
        {
            title: "Robotik Giriş Slaytları (PDF)",
            url: "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf",
            type: "pdf",
            size: "1.2 MB"
        },
        {
            title: "Şema - Basit Sensör Yerleşimi (PNG)",
            url: "https://upload.wikimedia.org/wikipedia/commons/3/3f/Fronalpstock_big.jpg",
            type: "image",
            size: "820 KB"
        }
        // {title:"Ders Notları (DOCX)", url:"/path/file.docx", type:"file", size:"220 KB"}
    ];

    // --- Yardımcılar ---
    const $ = (sel, root=document) => root.querySelector(sel);
    const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

    function showToast(text){
        const t = document.getElementById('toast');
        t.innerHTML = `<div class="rounded-md bg-gray-900 text-white px-4 py-2 shadow">${text}</div>`;
        t.classList.remove('hidden');
        setTimeout(()=> t.classList.add('hidden'), 1500);
    }

    // --- Metin notlarını render et ---
    const notesList = document.getElementById('notes-list');
    function renderNotes(){
        notesList.innerHTML = notes.map((n,idx)=>`
      <div class="border rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between bg-gray-50">
          <div class="flex items-center gap-2">
            <i data-lucide="sticky-note" class="text-custom-yellow w-4 h-4"></i>
            <span class="font-semibold text-gray-900">${n.title}</span>
          </div>
          <div class="text-xs text-gray-500">Güncellendi: ${n.updatedAt}</div>
        </div>
        <div class="p-4 text-gray-700" id="note-body-${idx}">${n.html}</div>
        <div class="px-4 py-3 border-t bg-white flex items-center gap-2">
          <button class="inline-flex items-center gap-1 text-gray-700 hover:text-custom-yellow" onclick="openTextPreview('${encodeURIComponent(n.title)}', ${idx})">
            <i data-lucide="maximize-2" class="w-4 h-4"></i><span>Önizle</span>
          </button>
          <button class="inline-flex items-center gap-1 text-gray-700 hover:text-custom-yellow" onclick="copyNote(${idx})">
            <i data-lucide="copy" class="w-4 h-4"></i><span>Kopyala</span>
          </button>
          <button class="inline-flex items-center gap-1 text-gray-700 hover:text-custom-yellow" onclick="printNote(${idx})">
            <i data-lucide="printer" class="w-4 h-4"></i><span>Yazdır</span>
          </button>
        </div>
      </div>
    `).join('');
        lucide.createIcons();
    }

    // --- Dokümanları render et ---
    const docsList = document.getElementById('docs-list');
    function iconForDoc(type){
        switch(type){
            case 'pdf': return 'file-type-2';
            case 'image': return 'image';
            default: return 'file';
        }
    }
    function renderDocs(){
        docsList.innerHTML = documents.map((d,idx)=>`
      <div class="flex items-center justify-between p-3 border rounded-lg">
        <div class="flex items-center gap-3 min-w-0">
          <div class="flex items-center justify-center w-10 h-10 rounded-full bg-custom-yellow/10 flex-shrink-0">
            <i data-lucide="${iconForDoc(d.type)}" class="text-custom-yellow w-5 h-5"></i>
          </div>
          <div class="min-w-0">
            <div class="font-medium text-gray-900 truncate">${d.title}</div>
            <div class="text-xs text-gray-500">${d.size}</div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="${d.url}" download class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50">
            <i data-lucide="download" class="w-4 h-4"></i><span class="text-sm">İndir</span>
          </a>
          <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50"
                  onclick="openDocPreview('${encodeURIComponent(d.title)}','${encodeURIComponent(d.url)}','${d.type}')">
            <i data-lucide="eye" class="w-4 h-4"></i><span class="text-sm">Önizle</span>
          </button>
        </div>
      </div>
    `).join('');
        lucide.createIcons();
    }

    // --- Metin aksiyonları ---
    function copyNote(idx){
        const html = $(`#note-body-${idx}`).innerText; // düz metin
        navigator.clipboard.writeText(html).then(()=> showToast('Not kopyalandı'));
    }
    function printNote(idx){
        const w = window.open('', '_blank', 'width=800,height=900');
        const html = $(`#note-body-${idx}`).innerHTML;
        w.document.write(`
      <html><head><title>Yazdır</title></head>
      <body>${html}<script>window.onload=()=>window.print()<\/script></body></html>
    `);
        w.document.close();
    }
    $('#print-all-notes').addEventListener('click', ()=>{
        const allHTML = notes.map((n,i)=>`<h2>${n.title}</h2>${$(`#note-body-${i}`).innerHTML}`).join('<hr/>');
        const w = window.open('', '_blank', 'width=800,height=900');
        w.document.write(`<html><head><title>Tüm Notlar</title></head><body>${allHTML}<script>window.onload=()=>window.print()<\/script></body></html>`);
        w.document.close();
    });

    // --- Önizleme modal ortak ---
    const previewModal   = $('#preview-modal');
    const previewBackdrop= $('#preview-backdrop');
    const previewClose   = $('#preview-close');
    const previewTitle   = $('#preview-title');
    const previewBody    = $('#preview-body');
    const previewActions = $('#preview-actions');

    function openModal(){ previewModal.classList.remove('hidden'); previewModal.setAttribute('aria-hidden','false'); lucide.createIcons(); }
    function closeModal(){ previewModal.classList.add('hidden'); previewModal.setAttribute('aria-hidden','true'); previewBody.innerHTML=''; previewActions.innerHTML=''; }

    previewBackdrop.addEventListener('click', closeModal);
    previewClose.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !previewModal.classList.contains('hidden')) closeModal(); });

    // Metin önizleme
    function openTextPreview(titleEnc, idx){
        const title = decodeURIComponent(titleEnc);
        previewTitle.textContent = title;
        const html = $(`#note-body-${idx}`).innerHTML;
        previewBody.innerHTML = `<div class="p-6 prose max-w-none">${html}</div>`;
        previewActions.innerHTML = `
      <button class="inline-flex items-center gap-2 px-3 py-2 rounded-md border hover:bg-gray-50" onclick="copyNote(${idx})">
        <i data-lucide='copy' class='w-4 h-4'></i><span>Kopyala</span>
      </button>
      <button class="inline-flex items-center gap-2 px-3 py-2 rounded-md border hover:bg-gray-50" onclick="printNote(${idx})">
        <i data-lucide='printer' class='w-4 h-4'></i><span>Yazdır</span>
      </button>
    `;
        openModal();
    }

    // Doküman önizleme
    function openDocPreview(titleEnc, urlEnc, type){
        const title = decodeURIComponent(titleEnc);
        const url   = decodeURIComponent(urlEnc);
        previewTitle.textContent = title;

        if(type==='pdf'){
            previewBody.innerHTML = `<iframe src="${url}" class="w-full h-[75vh]" frameborder="0"></iframe>`;
        } else if(type==='image'){
            previewBody.innerHTML = `<img src="${url}" alt="${title}" class="max-h-[75vh] w-auto block mx-auto">`;
        } else {
            previewBody.innerHTML = `<div class="p-6 text-gray-700">Bu dosya türü için tarayıcı önizlemesi yok. <a class="text-custom-yellow underline" href="${url}" target="_blank">Dosyayı indir</a>.</div>`;
        }

        previewActions.innerHTML = `
      <a href="${url}" download class="inline-flex items-center gap-2 px-3 py-2 rounded-md border hover:bg-gray-50">
        <i data-lucide='download' class='w-4 h-4'></i><span>İndir</span>
      </a>
      <a href="${url}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-md border hover:bg-gray-50">
        <i data-lucide='external-link' class='w-4 h-4'></i><span>Yeni Sekmede Aç</span>
      </a>
    `;
        openModal();
    }

    // Tümünü indir (örn. ZIP endpoint’in varsa ona yönlendir)
    document.getElementById('download-all').addEventListener('click', ()=>{
        // window.location.href = `/download/module-2.zip`;
        showToast('ZIP indirme uç noktasını backend’de tanımla 🔧');
    });

    // Başlangıç render
    renderNotes();
    renderDocs();
</script>
</body>
</html>
