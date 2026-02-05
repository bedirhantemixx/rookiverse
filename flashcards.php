<?php
require_once 'config.php';
?>

<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/x-icon" href="assets/images/rokiverse_icon.png">
    <title>RookieVerse • FRC Ezber Kartları</title>
    <style>
        :root{ --rv-yellow:#E5AE32; --bg:#f5f5f7; --card:#ffffff; --ink:#111827; --muted:#6b7280; --line:#e5e7eb; }
        *{box-sizing:border-box}
        body{margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Helvetica,Arial,sans-serif; color:var(--ink); background:var(--bg)}
        .container{max-width:1100px; margin:0 auto; padding:16px}
        .btn{appearance:none; border:1px solid var(--line); background:#fff; padding:8px 12px; border-radius:10px; cursor:pointer}
        .btn:hover{background:#fafafa}
        .btn.primary{background:var(--rv-yellow); border-color:var(--rv-yellow); color:#111}
        .card{background:var(--card); border:1px solid var(--line); border-radius:18px; box-shadow:0 6px 20px rgba(0,0,0,.04)}
        .pad{padding:14px}
        /* progress */
        .progress{height:8px; background:#e5e7eb; border-radius:999px; overflow:hidden}
        .progress > div{height:100%; background:var(--rv-yellow); width:0}
        .meta{display:flex; justify-content:space-between; color:var(--muted); font-size:13px; margin-bottom:6px}
        /* flip card */
        .stage{aspect-ratio:16/10; perspective:1200px}
        .flip{position:relative; width:100%; height:100%; transform-style:preserve-3d; transition:transform .5s}
        .face{position:absolute; inset:0; padding:20px; display:flex; flex-direction:column; justify-content:space-between; backface-visibility:hidden}
        .front{background:var(--card); border:1px solid var(--line); border-radius:18px}
        .back{background:var(--card); border:1px solid var(--line); border-radius:18px; transform:rotateY(180deg)}
        .flip.showback{transform:rotateY(180deg)}
        .toprow{display:flex; justify-content:space-between; align-items:center}
        .badge{border:1px solid var(--rv-yellow); padding:4px 8px; border-radius:999px; font-size:12px}
        .muted{color:var(--muted); font-size:12px}
        .term{font-size:28px; font-weight:800; text-align:center; line-height:1.2; padding:10px}
        .def{font-size:18px; line-height:1.6; overflow:auto; padding-right:8px}
        .actions{display:grid; grid-template-columns:repeat(3,1fr); gap:10px}
        .footer-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:10px}
        @media(max-width:640px){.footer-grid{grid-template-columns:repeat(2,1fr)}}
        .stat{background:#f8fafc; border:1px solid var(--line); border-radius:14px; padding:12px}
        .stat .k{font-size:12px; color:var(--muted)}
        .stat .v{font-size:20px; font-weight:700}
        .hint{display:flex; justify-content:space-between; align-items:center; color:var(--muted); font-size:13px; margin-top:6px}
    </style>
</head>
<body>
<?php require_once 'navbar.php'?>

<!-- PROGRESS -->
<div class="container" style="margin-top:12px">
    <div class="card pad">
        <div class="meta"><span id="metaLearned">0/0 öğrenildi</span><span id="metaLeft">0 kaldı</span></div>
        <div class="progress"><div id="bar"></div></div>
    </div>
</div>

<!-- CARD AREA -->
<main class="container" style="padding-top:10px">
    <div id="stageWrap" class="card pad">
        <div class="stage">
            <div id="flip" class="flip">
                <section class="face front">
                    <div class="toprow">
                        <span id="catFront" class="badge"></span>
                        <span id="idxFront" class="muted"></span>
                    </div>
                    <div class="term" id="term"></div>
                    <div style="display:flex; justify-content:center; margin-bottom:6px">
                        <button id="flipBtn" class="btn primary">Çevir (Space)</button>
                    </div>
                </section>
                <section class="face back">
                    <div class="toprow">
                        <span id="catBack" class="badge"></span>
                        <span id="idxBack" class="muted"></span>
                    </div>
                    <div class="def" id="def"></div>
                    <div class="actions">
                        <button id="prevBtn" class="btn">← Geri</button>
                        <button id="againBtn" class="btn">Tekrar (A)</button>
                        <button id="goodBtn" class="btn primary">Biliyorum (G)</button>
                    </div>
                </section>
            </div>
        </div>
        <div class="hint">
            <span>İpucu: Kartı çevirip A/G ile puanla.</span>
            <div>
                <button id="navPrev" class="btn">Önceki</button>
                <button id="navNext" class="btn">Sonraki</button>
            </div>
        </div>
    </div>
</main>

<!-- SESSION SUMMARY + IMPORT/EXPORT -->
<div class="container" style="padding-bottom:24px">
    <div class="card pad">
        <h3 style="margin:8px 0 10px">Seans Özeti</h3>
        <div class="footer-grid">
            <div class="stat"><div class="k">Toplam</div><div id="stTotal" class="v">0</div></div>
            <div class="stat"><div class="k">Öğrenildi</div><div id="stLearned" class="v">0</div></div>
            <div class="stat"><div class="k">Kaldı</div><div id="stLeft" class="v">0</div></div>
            <div class="stat"><div class="k">Kuyruk</div><div id="stQueue" class="v">0</div></div>
        </div>
        <div style="margin-top:12px; display:flex; flex-wrap:wrap; gap:8px">

            <button id="shuffleBtn" class="btn">Karıştır</button>
            <button id="resetBtn" class="btn">Sıfırla</button>
        </div>
    </div>
</div>

<?php require_once 'footer.php'?>

<script>
    lucide.createIcons();
    const RAW = [
// Genel Terimler
        { term: "FIRST", category: "Genel Terimler", definition: "'For Inspiration and Recognition of Science and Technology' kelimelerinin baş harflerinden oluşan, gençleri bilim ve teknolojiye teşvik etmek amacıyla kurulmuş olan kâr amacı gütmeyen organizasyon." },
        { term: "FRC", category: "Genel Terimler", definition: "FIRST Robotics Competition. FIRST organizasyonunun lise öğrencilerine yönelik en büyük ve kapsamlı robotik yarışması." },
        { term: "Gracious Professionalism (Duyarlı Profesyonellik)", category: "Genel Terimler", definition: "Hem rekabetin hem de karşılıklı saygının bir arada yürüdüğü bir FRC felsefesi. Rakiplerinize yardım etmeyi ve onlardan öğrenmeyi teşvik eder." },
        { term: "Coopertition (İşbirlikçi Rekabet)", category: "Genel Terimler", definition: "'Cooperation' ve 'Competition' kelimelerinin birleşimi. Takımların hem rekabet edip hem de birbirlerine yardım ederek daha büyük başarılara ulaşmasını ifade eder." },
        { term: "Rookie", category: "Genel Terimler", definition: "FRC'ye ilk defa katılan takım veya üye." },
        { term: "Veteran", category: "Genel Terimler", definition: "FRC'de en az bir sezon tecrübesi olan takım veya üye." },


// Oyun ve Saha
        { term: "Alliance (İttifak)", category: "Oyun ve Saha", definition: "Maç sırasında birlikte hareket eden takımlar grubu. Genellikle 3 takımdan oluşan Kırmızı ve Mavi olmak üzere iki ittifak bulunur." },
        { term: "Autonomous Period (Otonom Dönem)", category: "Oyun ve Saha", definition: "Maçın ilk 15 saniyesi. Robotlar sürücü kontrolü olmadan önceden programlanmış görevleri yapar." },
        { term: "Tele-Op Period (Sürücü Kontrol Dönemi)", category: "Oyun ve Saha", definition: "Otonomdan sonraki 2 dakika 15 saniyelik kısım. Sürücüler robotları uzaktan kontrol eder." },
        { term: "Endgame (Oyun Sonu)", category: "Oyun ve Saha", definition: "Tele-Op’un genellikle son 30 saniyesi. Takımlar tırmanma gibi ekstra puanlı görevleri yapar." },
        { term: "Game Piece (Oyun Elemanı)", category: "Oyun ve Saha", definition: "Sezonun oyununda robotların manipüle ettiği nesneler (ör. top, küp, koni)." },
        { term: "Driver Station (Sürücü İstasyonu)", category: "Oyun ve Saha", definition: "Sürücülerin maçı yönettiği, bilgisayar ve kontrol cihazlarının bulunduğu alan." },
        { term: "Ranking Points (RP)", category: "Oyun ve Saha", definition: "Kvalifikasyon maçlarında elemelere kalmak için kazanılan puanlar; galibiyet, beraberlik ve belirli görevlerle verilir." },


// Robot Parçaları ve Mekanik
        { term: "Chassis / Drivetrain (Şasi / Aktarma)", category: "Robot Parçaları ve Mekanik", definition: "Robotun hareket etmesini sağlayan tekerlekler, motorlar, dişliler ve iskelet sisteminin bütünü." },
        { term: "Bumper (Tampon)", category: "Robot Parçaları ve Mekanik", definition: "Robotların etrafını saran, kırmızı/mavi renkli koruyucu köpükler. Hem robotu korur hem ittifak rengini gösterir." },
        { term: "Manipulator / Actuator", category: "Robot Parçaları ve Mekanik", definition: "Oyun elemanlarını toplama, taşıma, fırlatma gibi işlevleri yapan mekanizmalar." },
        { term: "roboRIO", category: "Robot Parçaları ve Mekanik", definition: "Robotun 'beyni' kabul edilen ana kontrolcü; motorları, sensörleri ve elektronik bileşenleri yönetir." },
        { term: "Motor Controller (Motor Sürücü)", category: "Robot Parçaları ve Mekanik", definition: "roboRIO sinyallerini motorlara uygun güce çeviren kart (ör. Talon, Spark MAX)." },
        { term: "Pneumatics (Pnömatik)", category: "Robot Parçaları ve Mekanik", definition: "Basınçlı hava ile pistonları hareket ettiren ve çeşitli mekanizmaları çalıştıran sistem." },
        { term: "Sensor (Sensör)", category: "Robot Parçaları ve Mekanik", definition: "Robotun çevresini algılamasına yardım eden bileşenler (kamera, ultrasonik, jiroskop vb.)." },


// Takım ve Roller
        { term: "Drive Team (Sürücü Ekibi)", category: "Takım ve Roller", definition: "Maçta robotu yöneten ekip: Sürücü, Operatör, Koç ve İnsan Oyuncu." },
        { term: "Pit (Pit Alanı)", category: "Takım ve Roller", definition: "Turnuvada takımlara ayrılan çalışma alanı; robot bakımının ve sosyalleşmenin yapıldığı yer." },
        { term: "Scouting", category: "Takım ve Roller", definition: "Diğer takımların robot yeteneklerini ve stratejilerini izleyip veri toplama ve analiz etme süreci." },
        { term: "Mentor", category: "Takım ve Roller", definition: "Takım öğrencilerine teknik/sosyal konularda rehberlik eden gönüllü yetişkin." },


// Ödüller ve Etkinlikler (uzun liste)
        { term: "Kickoff", category: "Ödüller ve Etkinlikler", definition: "Her yıl Ocak başında yeni FRC sezon oyununun ve kurallarının açıklandığı etkinlik." },
        { term: "Regional / District", category: "Ödüller ve Etkinlikler", definition: "Takımların Dünya Şampiyonası’na katılma hakkı için yarıştığı yerel/bölgesel turnuvalar." },
        { term: "Championship (Dünya Şampiyonası)", category: "Ödüller ve Etkinlikler", definition: "Sezon sonunda dünyanın dört bir yanından en iyi takımların yarıştığı final etkinliği." },
        { term: "FIRST Impact Award (Etki Ödülü)", category: "Ödüller ve Etkinlikler", definition: "FRC’deki en prestijli ödül; takımın FIRST misyonunu temsil etmesi, topluma etkisi ve rol model oluşu değerlendirilir." },
        { term: "Engineering Inspiration Award", category: "Ödüller ve Etkinlikler", definition: "Bilim ve teknolojiyi kutlamada üstün başarı, öğrencileri mühendisliğe özendirme." },
        { term: "Rookie All-Star Award", category: "Ödüller ve Etkinlikler", definition: "İlk senesinde güçlü performans ve FIRST felsefesini benimseyen çaylak takıma." },
        { term: "Excellence in Engineering Award", category: "Ödüller ve Etkinlikler", definition: "Sağlam, güvenilir ve yenilikçi mühendislik prensiplerini sergileyen robota." },
        { term: "Industrial Design Award", category: "Ödüller ve Etkinlikler", definition: "İşlevsellik, estetik ve üretim kolaylığını birleştiren endüstriyel tasarım başarısı." },
        { term: "Autonomous Award", category: "Ödüller ve Etkinlikler", definition: "Otonom modda güvenilir ve etkili performans gösteren tasarım ve yazılım." },
        { term: "Innovation in Control Award", category: "Ödüller ve Etkinlikler", definition: "Elektrik, yazılım ve kontrol sistemlerinde zarif/yenilikçi uygulamalar." },
        { term: "Creativity Award", category: "Ödüller ve Etkinlikler", definition: "Oyunu çözmek için alışılmışın dışında, akıllıca mekanik veya stratejik çözüm." },
        { term: "Quality Award", category: "Ödüller ve Etkinlikler", definition: "Dayanıklı, profesyonel görünümde, sağlam ve iyi üretilmiş robota." },
        { term: "Gracious Professionalism® Award", category: "Ödüller ve Etkinlikler", definition: "Rekabet ve saygıyı birleştirerek FRC felsefesini en iyi sergileyen takıma." },
        { term: "Team Spirit Award", category: "Ödüller ve Etkinlikler", definition: "Coşkusu, görünürlüğü ve etkinliği ile sahaya enerji katan takıma." },
        { term: "Judges’ Award", category: "Ödüller ve Etkinlikler", definition: "Kategorilere uymayan ama takdire değer benzersiz başarılara verilen jüri özel ödülü." },
        { term: "Winner (Kazanan)", category: "Ödüller ve Etkinlikler", definition: "Final maçlarını kazanarak şampiyon olan ittifaktaki takımlara verilen unvan." },
        { term: "Finalist", category: "Ödüller ve Etkinlikler", definition: "Final maçlarını kaybederek ikinci olan ittifaktaki takımlar." },
        { term: "FIRST Dean’s List Award", category: "Ödüller ve Etkinlikler", definition: "Liderlik ve teknik uzmanlık gösteren, FIRST’ün misyonunu benimsemiş olağanüstü öğrencilere bireysel ödül." },
        { term: "Woodie Flowers Finalist Award", category: "Ödüller ve Etkinlikler", definition: "İlham veren, liderlik eden olağanüstü mentorlara verilen bireysel ödül." },
        { term: "Volunteer of the Year Award", category: "Ödüller ve Etkinlikler", definition: "FRC etkinliklerine olağanüstü katkıda bulunan gönüllüye verilen bireysel ödül." },
        { term: "Digital Animation Award", category: "Ödüller ve Etkinlikler", definition: "FRC temasını veya bir bilim/teknoloji kavramını anlatan en iyi dijital animasyon." },
        { term: "Safety Animation Award", category: "Ödüller ve Etkinlikler", definition: "UL sponsorluğunda, FRC güvenliğini yaratıcı şekilde anlatan animasyon ödülü." },
        { term: "Imagery Award", category: "Ödüller ve Etkinlikler", definition: "Takım ruhunu, imajını ve özgünlüğünü en iyi yansıtan takıma." },
        { term: "Team Sustainability Award", category: "Ödüller ve Etkinlikler", definition: "Yapısını ve finansal/mentorluk modelini sürdürülebilir kılan takıma." },
    ];
    const CARDS = RAW.map((c)=> ({...c, id:`${c.category}-${c.term}`}));

    // ======= DURUM & STORAGE =======
    const store = {
        read(key, fallback){ try{ return JSON.parse(localStorage.getItem(key)) ?? fallback }catch{ return fallback }},
        write(key, val){ try{ localStorage.setItem(key, JSON.stringify(val)) }catch{} }
    };
    let seed = store.read('frcanki:seed', Date.now());
    let learnedIds = store.read('frcanki:learned', []);
    let againIds = store.read('frcanki:againQueue', []);
    let idx = store.read('frcanki:index', 0);
    let showBack = store.read('frcanki:showBack', false);

    // RNG & helpers
    function mulberry32(a){return function(){let t=(a+=0x6D2B79F5);t=Math.imul(t^(t>>>15),t|1);t^=t+Math.imul(t^(t>>>7),61|t);return ((t^(t>>>14))>>>0)/4294967296;}}
    function shuffleStable(arr, seed){ const r = mulberry32(seed); return [...arr].sort(()=> r()-0.5) }
    function clamp(n,a,b){return Math.max(a,Math.min(b,n))}
    function setSeed(s){ seed=s; store.write('frcanki:seed', seed) }

    // ======= KUYRUK =======
    function filtered(){ return shuffleStable(CARDS, seed); } // filtre yok
    function queue(){
        const base = filtered();
        const learned = new Set(learnedIds);
        const againSet = new Set(againIds);
        const fresh = base.filter(c=> !learned.has(c.id) && !againSet.has(c.id));
        const againCards = againIds.map(id=> base.find(c=> c.id===id)).filter(Boolean);
        return [...againCards, ...fresh];
    }

    // ======= DOM =======
    const el = (id)=> document.getElementById(id);
    const flipBox = el('flip');
    const termEl = el('term');
    const defEl = el('def');
    const catFront = el('catFront');
    const catBack = el('catBack');
    const idxFront = el('idxFront');
    const idxBack = el('idxBack');
    const flipBtn = el('flipBtn');
    const prevBtn = el('prevBtn');
    const againBtn = el('againBtn');
    const goodBtn = el('goodBtn');
    const navPrev = el('navPrev');
    const navNext = el('navNext');
    const shuffleBtn = el('shuffleBtn');
    const resetBtn = el('resetBtn');
    const metaLearned = el('metaLearned');
    const metaLeft = el('metaLeft');
    const bar = el('bar');
    const stTotal = el('stTotal');
    const stLearned = el('stLearned');
    const stLeft = el('stLeft');
    const stQueue = el('stQueue');

    // ======= LOGIC =======
    function current(){
        const q = queue();
        if(q.length===0) return null;
        return q[clamp(idx,0,q.length-1)];
    }

    function updateProgress(total, learned){
        metaLearned.textContent = `${learned}/${total} öğrenildi`;
        metaLeft.textContent = `${Math.max(total-learned,0)} kaldı`;
        bar.style.width = `${total? (learned/total)*100 : 0}%`;
        stTotal.textContent = total;
        stLearned.textContent = learned;
        stLeft.textContent = Math.max(total-learned,0);
        stQueue.textContent = queue().length;
    }

    function update(){
        const base = filtered();
        const learnedCount = base.filter(c=> learnedIds.includes(c.id)).length;
        updateProgress(base.length, learnedCount);

        const cur = current();
        if(!cur){
            termEl.textContent = 'Seans tamam! 🎉';
            defEl.textContent = 'Yeni bir karıştırma yapabilir veya yeni bir seansa başlayabilirsin.';
            catFront.textContent = '';
            catBack.textContent = '';
            idxFront.textContent = '';
            idxBack.textContent = '';
            flipBox.classList.remove('showback');
            [flipBtn, prevBtn, againBtn, goodBtn, navPrev, navNext].forEach(b=> b.disabled = true);
            return;
        }

        [flipBtn, prevBtn, againBtn, goodBtn, navPrev, navNext].forEach(b=> b.disabled = false);

        termEl.textContent = cur.term;
        defEl.textContent = cur.definition;
        catFront.textContent = cur.category;
        catBack.textContent = cur.category;

        const q = queue();
        const pos = clamp(idx,0,q.length-1);
        idxFront.textContent = `Kart ${pos+1} / ${q.length}`;
        idxBack.textContent = `Kart ${pos+1} / ${q.length}`;

        if(showBack) flipBox.classList.add('showback'); else flipBox.classList.remove('showback');
    }

    function flip(){ showBack = !showBack; store.write('frcanki:showBack', showBack); update(); }
    function next(){ showBack=false; store.write('frcanki:showBack', false); idx = clamp(idx+1,0,Math.max(queue().length-1,0)); store.write('frcanki:index', idx); update(); }
    function prev(){ showBack=false; store.write('frcanki:showBack', false); idx = clamp(idx-1,0,Math.max(queue().length-1,0)); store.write('frcanki:index', idx); update(); }

    function gradeGood(){
        const cur = current(); if(!cur) return;
        if(!learnedIds.includes(cur.id)) learnedIds=[...learnedIds, cur.id];
        againIds = againIds.filter(id=> id!==cur.id);
        store.write('frcanki:learned', learnedIds);
        store.write('frcanki:againQueue', againIds);
        next();
    }
    function gradeAgain(){
        const cur = current(); if(!cur) return;
        if(!againIds.includes(cur.id)) againIds=[...againIds, cur.id];
        store.write('frcanki:againQueue', againIds);
        next();
    }

    // keyboard
    window.addEventListener('keydown', (e)=>{
        const tag = (e.target && (e.target.tagName||'')).toLowerCase();
        if(tag==='input' || tag==='textarea') return;
        if(e.code==='Space'){ e.preventDefault(); flip(); }
        if(e.key==='ArrowRight'){ next(); }
        if(e.key==='ArrowLeft'){ prev(); }
        if(e.key.toLowerCase()==='g'){ gradeGood(); }
        if(e.key.toLowerCase()==='a'){ gradeAgain(); }
    });

    // buttons
    flipBtn.addEventListener('click', flip);
    prevBtn.addEventListener('click', prev);
    navPrev.addEventListener('click', prev);
    navNext.addEventListener('click', next);
    againBtn.addEventListener('click', gradeAgain);
    goodBtn.addEventListener('click', gradeGood);
    shuffleBtn.addEventListener('click', ()=>{ setSeed(Date.now()); idx=0; store.write('frcanki:index', idx); update(); });
    resetBtn.addEventListener('click', ()=>{
        learnedIds=[]; againIds=[]; idx=0; showBack=false; setSeed(Date.now());
        store.write('frcanki:learned', learnedIds);
        store.write('frcanki:againQueue', againIds);
        store.write('frcanki:index', idx);
        store.write('frcanki:showBack', showBack);
        update();
    });

    // import/export


    // init
    update();
</script>
</body>
</html>
