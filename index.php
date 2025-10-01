<?php require_once 'config.php';
//a
$index = true;
$courses = getTopCourses();
session_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Anasayfa - FRC Rookieverse</title>


  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
  
  <script>
    tailwind.config = {
      theme: { extend: { colors: { 'custom-yellow': '#E5AE32' } } }
    }
  </script>

  <style>
      /* Konteyner sabit çerçeve */
      .logo-slider-container { position: relative; overflow: hidden; }

      /* Track artık kaymaz—sahnede üst üste durur */
      .logo-slider-track {
          position: relative;
          width: 100%;
          height: 30vh;            /* logo yüksekliğine göre ayarla */
          overflow: hidden;         /* kaydırma kapalı */
      }

      /* Her slide sahnede aynı noktada */
      .logo-slide {
          position: absolute;
          inset: 0;                 /* top:0 right:0 bottom:0 left:0 */
          display: flex;
          align-items: center;
          justify-content: center;
          opacity: 0;               /* varsayılan görünmez */
          pointer-events: none;
          transition: opacity 300ms ease;
      }

      /* Sahnede görünen tek logo */
      .logo-slide.is-active {
          opacity: 1;
          pointer-events: auto;
      }

      /* Logonun ölçüsü */
      .logo-slide img {
          max-height: 100%;
          max-width: 80%;
          object-fit: contain;
      }

      /* Ok butonları */
      .slider-arrow {
          position: absolute;
          top: 50%;
          transform: translateY(-50%);
          background: white;
          border: 1px solid #e5e7eb;
          border-radius: 9999px;
          padding: .5rem;
          box-shadow: 0 2px 8px rgba(0,0,0,.06);
      }
      .slider-arrow.left  { left: 8px; }
      .slider-arrow.right { right: 8px; }
      .slider-arrow:hover { background: #f9fafb; }

      .hero-ss-track {
          scroll-snap-stop: always; /* slide ortasında mutlaka durur */
      }

      .hero-ss-container {
          position: relative;
          border-radius: 1rem;
          overflow: hidden;
      }
      .hero-ss-slide img {
          width: 100%;
          max-height: 400px;        /* sabit yükseklik buradan */
          object-fit: cover;    /* görüntü bozulmadan kırpılır */
          display: block;
      }

      .hero-ss-arrow {
          opacity: 0;
          transition: opacity 0.3s ease;
      }

      .hero-ss-container:hover .hero-ss-arrow {
          opacity: 1;
      }


      .hero-ss-track {
          display: flex;
          overflow-x: auto;
          scroll-snap-type: x mandatory;
          scroll-behavior: smooth;
          -webkit-overflow-scrolling: touch;
          scrollbar-width: none; /* Firefox */
      }
      .hero-ss-track::-webkit-scrollbar { display: none; } /* WebKit */

      .hero-ss-slide {
          flex: 0 0 100%;
          scroll-snap-align: center;
          position: relative;
      }
      .hero-ss-slide img {
          display: block;
          width: 100%;
          height: clamp(300px, 45vw, 480px); /* responsive yükseklik */
          object-fit: cover;
      }

      /* Oklar */
      .hero-ss-arrow {
          position: absolute; top: 50%; transform: translateY(-50%);
          background: rgba(17,24,39,.6); color:#fff;
          width:44px; height:44px; border-radius:9999px;
          display:grid; place-items:center; border:none; cursor:pointer;
      }
      .hero-ss-arrow.left { left: 10px; } .hero-ss-arrow.right { right: 10px; }
      .hero-ss-arrow:hover { background: rgba(17,24,39,.8); }

      /* Noktalar */
      .hero-ss-dots {
          position: absolute; left:50%; transform:translateX(-50%);
          bottom: 10px; display:flex; gap:8px;
      }
      .hero-ss-dot {
          width:10px; height:10px; border-radius:9999px;
          background: rgba(255,255,255,.6); border:1px solid rgba(0,0,0,.1);
          cursor:pointer;
      }
      .hero-ss-dot.active { background:#E5AE32; }


  </style>

</head>
<body class="bg-white">

  <?php require_once 'navbar.php'; ?>
  
  <div class="min-h-screen">
    <section class="bg-gradient-to-br from-custom-yellow/10 via-white to-custom-yellow/5 py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-8">
            <div class="space-y-4">
              <span class="inline-block bg-custom-yellow text-white px-4 py-1 rounded-md text-sm font-semibold">Rookie’lere özel video, doküman ve oyunlar.</span>
              <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 leading-tight">FRC Dünyasına <span class="text-custom-yellow block">Hoş Geldiniz</span></h1>
              <p class="text-xl text-gray-600 max-w-lg"><b class="block mb-4">Türk FRC takımlarının hazırladığı tüm Türkçe doküman ve eğitim videoları artık tek çatı altında!</b> Rookie üyeler, ihtiyacınız olan kaynaklara zahmetsizce ulaşarak robotik serüveninize güçlü bir başlangıç yapın.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
              <a href="<?php echo BASE_URL; ?>/courses.php" class="inline-flex items-center justify-center bg-custom-yellow hover:bg-opacity-90 text-white px-8 py-3 text-lg font-semibold rounded-lg"><i data-lucide="play" class="mr-2 w-5 h-5"></i>Kurslara Başla</a>
              <a href="<?php echo BASE_URL; ?>/games" class="inline-flex items-center justify-center border-2 border-custom-yellow text-custom-yellow hover:bg-custom-yellow hover:text-white px-8 py-3 text-lg font-semibold rounded-lg"><i data-lucide="gamepad-2" class="mr-2 w-5 h-5"></i>Oyunları Keşfet</a>
            </div>
          </div>
            <div class="hero-ss-container">
                <div class="hero-ss-track" id="heroSS">
                    <div class="hero-ss-slide"><img src="assets/images/frcexapmle.png" alt=""></div>
                    <div class="hero-ss-slide"><img src="assets/images/frcexampleimage2.jpg" alt=""></div>
                    <div class="hero-ss-slide"><img src="assets/images/frcexampleimage3.jpg" alt=""></div>
                    <div class="hero-ss-slide"><img src="assets/images/frcexample7.jpg" alt=""></div>
                    <div class="hero-ss-slide"><img src="assets/images/frcexampleimage4.jpg" alt=""></div>
                    <div class="hero-ss-slide"><img src="assets/images/frcexampleimage5.webp" alt=""></div>
                    <div class="hero-ss-slide"><img src="assets/images/frcexampleimage6.jpeg" alt=""></div>
                </div>

                <button class="hero-ss-arrow left" id="prevSS"><i data-lucide="chevron-left"></i></button>
                <button class="hero-ss-arrow right" id="nextSS"><i data-lucide="chevron-right"></i></button>
                <div class="hero-ss-dots" id="dotsSS"></div>
            </div>

        </div>
      </div>
    </section>

    <section class="py-20"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="text-center mb-16"><h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Neden RookieVerse?</h2><p class="text-xl text-gray-600 max-w-3xl mx-auto">Türk FRC takımlarının deneyimli öğrencilerin hazırladığı, interaktif içeriklerden ve gerçek dünya deneyimlerinden faydalanın.</p></div><div class="grid grid-cols-1 md:grid-cols-3 gap-8"><div class="border-2 hover:border-custom-yellow/50 rounded-lg p-6"><div class="text-center pb-4"><div class="mx-auto w-16 h-16 bg-custom-yellow/10 rounded-full flex items-center justify-center mb-4"><i data-lucide="book-open" class="text-custom-yellow w-8 h-8"></i></div><h3 class="text-xl font-bold">Kapsamlı Kurslar</h3></div><p class="text-center text-base text-gray-600">FRC'nin tüm alanlarını kapsayan detaylı kurs içerikleri. Başlangıçtan ileri seviyeye kadar öğrenin.</p></div><div class="border-2 hover:border-custom-yellow/50 rounded-lg p-6"><div class="text-center pb-4"><div class="mx-auto w-16 h-16 bg-custom-yellow/10 rounded-full flex items-center justify-center mb-4"><i data-lucide="gamepad-2" class="text-custom-yellow w-8 h-8"></i></div><h3 class="text-xl font-bold">İnteraktif Oyunlar</h3></div><p class="text-center text-base text-gray-600">Eğlenceli oyunlar ile öğrendiklerinizi pekiştirin. Öğrenme sürecini keyifli hale getirin.</p></div><div class="border-2 hover:border-custom-yellow/50 rounded-lg p-6"><div class="text-center pb-4"><div class="mx-auto w-16 h-16 bg-custom-yellow/10 rounded-full flex items-center justify-center mb-4"><i data-lucide="users" class="text-custom-yellow w-8 h-8"></i></div><h3 class="text-xl font-bold">Topluluk Desteği</h3></div><p class="text-center text-base text-gray-600">Projenin amacı doğrultusunda Türkiye'deki FRC takımlarına döküman veya videolara dair sorularınızı sorun.</p></div></div></div></section>

      <section class="bg-gray-50 py-20">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              <div class="text-center mb-16">
                  <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Öne Çıkan Kurslar</h2>
                  <p class="text-xl text-gray-600">En popüler ve etkili kurslarımızla FRC yolculuğunuza başlayın</p>
              </div>



              <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                  <?php
                  foreach ($courses as $course):
                  ?>
                      <div class="overflow-hidden hover:shadow-xl rounded-lg border">
                          <div class="aspect-video relative group">
                              <img src="<?=$course['cover_image_url']?>" class="w-full h-full object-cover">
                              <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                  <a href="courseDetails.php?course=<?=$course['course_uid']?>" class="inline-flex items-center px-4 py-2 bg-white text-custom-yellow rounded-md">
                                      <i data-lucide="play" class="mr-2 w-4 h-4"></i> Kursu Görüntüle
                                  </a>
                              </div>
                          </div>
                          <div class="p-4">
                              <div class="flex justify-between items-start mb-2">
                                  <h3 class="text-lg font-bold"><?=$course['title']?></h3>
                                  <span class="text-xs font-semibold py-1 px-2 uppercase rounded-full text-gray-700 bg-gray-200"><?=$course['level']?></span>
                              </div>
                              <p class="text-base text-gray-600 mb-4"><?=$course['goal_text']?></p>
                              <div class="flex justify-between items-center">
                                  <a href="courseDetails.php?course=<?=$course['course_uid']?>" class="px-3 py-1.5 text-sm bg-custom-yellow text-white rounded-md">Detayları Gör</a>
                              </div>
                          </div>
                      </div>
                  <?php endforeach;?>

              </div>

              <div class="text-center mt-12">
                  <a href="<?php echo BASE_URL; ?>/courses.php" class="inline-flex items-center justify-center border-2 border-custom-yellow text-custom-yellow hover:bg-custom-yellow hover:text-white px-8 py-3 text-lg font-semibold rounded-md">
                      Tüm Kursları Görüntüle
                  </a>
              </div>
          </div>
      </section>


      <section id="contributors" class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16"><h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Destekleyen Takımlar</h2><p class="text-xl text-gray-600">Bu platformun gelişimine katkıda bulunan değerli FRC takımları</p></div>
            <div class="logo-slider-container">
                <button id="prevBtn" class="slider-arrow left"><i data-lucide="chevron-left" class="text-gray-700"></i></button>
                <div class="logo-slider-track">
                    <?php
                    $contributors = getContributors();
                    $conts = [];
                    foreach ($contributors as $cont):
                        $id = $cont['id'];
                        if (in_array($id, $conts)) {
                            continue;
                        }
                        array_push($conts, $id);
                    ?>
                        <div class="logo-slide"><a href="teamCourses.php?team_number=<?=$cont['team_number']?>" target="_blank"><img src="<?= $cont['profile_pic_path'] ?>" alt="<?=$cont['team_name']?>"></a></div>
                    <?php
                    endforeach;?>
                    </div>
                <button id="nextBtn" class="slider-arrow right"><i data-lucide="chevron-right" class="text-gray-700"></i></button>
            </div>
        </div>
    </section>

    <section class="bg-custom-yellow py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"><div class="space-y-6"><h2 class="text-3xl lg:text-4xl font-bold text-white">FRC Maceranıza İlk Adımı Atmaya Hazır mısınız?</h2><p class="text-xl text-white/90 max-w-2xl mx-auto">Robotik dünyasında yol gösterecek tüm kaynaklar burada. Ücretsiz eğitim içeriklerimiz ve takım destek programlarımızla hem bireysel katılımcıları hem de takımları başarıya taşıyoruz.</p><div class="flex flex-col sm:flex-row gap-4 justify-center"><a href="<?php echo BASE_URL; ?>/courses.php" class="inline-flex items-center justify-center bg-white text-custom-yellow hover:bg-gray-100 px-8 py-3 text-lg font-semibold rounded-md"><i data-lucide="book-open" class="mr-2 w-5 h-5"></i>Ücretsiz Başla</a><a href="<?php echo BASE_URL; ?>/team-login.php" class="inline-flex items-center justify-center border-2 border-white text-white hover:bg-white hover:text-custom-yellow px-8 py-3 text-lg font-semibold rounded-md"><i data-lucide="log-in" class="mr-3 w-5 h-5"></i>Takım Girişi</a></div></div></div>
    </section>
  </div>
  <?php require_once 'footer.php'?>

  <script>
      document.addEventListener('DOMContentLoaded', () => {
          lucide.createIcons();




          const container = document.querySelector('.hero-ss-container');
          const track     = document.getElementById('heroSS');
          if (!container || !track) return;

          const slides = Array.from(track.children);
          const prev   = document.getElementById('prevSS');
          const next   = document.getElementById('nextSS');
          const dots   = Array.from(document.querySelectorAll('#dotsSS .hero-ss-dot'));

          const AUTO_MS = 4000;               // her 4 sn'de geç
          let timer = null;

          const width = () => track.clientWidth;
          const idx   = () => Math.round(track.scrollLeft / width());

          function goTo(i, smooth = true) {
              track.scrollTo({ left: i * width(), behavior: smooth ? 'smooth' : 'auto' });
              updateDots(i);
          }

          function updateDots(i = idx()) {
              dots.forEach((d, k) => d.classList.toggle('active', k === i));
          }

          function nextSlide() { goTo((idx() + 1) % slides.length); }
          function prevSlide() { goTo((idx() - 1 + slides.length) % slides.length); }

          function start() {
              stop();
              // motion azaltmayı tercih eden kullanıcıları otomatik oynatma ile yormayalım
              const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
              if (!prefersReduced) timer = setInterval(nextSlide, AUTO_MS);
          }
          function stop() { if (timer) clearInterval(timer); timer = null; }

          // Butonlar
          if (next) next.addEventListener('click', () => { stop(); nextSlide(); start(); });
          if (prev) prev.addEventListener('click', () => { stop(); prevSlide(); start(); });

          // Hover’da dur, çıkınca devam et
          container.addEventListener('mouseenter', stop);
          container.addEventListener('mouseleave', start);

          // Dokunmada sürüklerken durdur
          track.addEventListener('pointerdown', stop);
          track.addEventListener('pointerup',   start);

          // Sekme görünmezse durdur, geri gelince devam
          document.addEventListener('visibilitychange', () => {
              if (document.hidden) stop(); else start();
          });

          // Resize’da hizayı koru (sub-pixel kaçmasın)
          new ResizeObserver(() => goTo(idx(), false)).observe(track);

          // Başlat
          updateDots(0);
          start();
      });




  </script>
  <script>
      (function () {
          const section = document.getElementById('contributors');
          if (!section) return;

          const container = section.querySelector('.logo-slider-container');
          const track     = section.querySelector('.logo-slider-track');
          const prevBtn   = section.querySelector('#prevBtn, .slider-arrow.left');
          const nextBtn   = section.querySelector('#nextBtn, .slider-arrow.right');
          if (!container || !track || !prevBtn || !nextBtn) return;

          // Tüm logolar
          const slides = Array.from(track.querySelectorAll('.logo-slide'));
          if (slides.length === 0) return;

          // Resimler yüklenmeden başlamayalım (ölçü/yerleşim güvenli olsun)
          function imagesReady(el) {
              const imgs = Array.from(el.querySelectorAll('img'));
              if (imgs.length === 0) return Promise.resolve();
              return Promise.all(imgs.map(img => img.complete ? Promise.resolve() : new Promise(res => {
                  img.addEventListener('load',  res, { once:true });
                  img.addEventListener('error', res, { once:true });
              })));
          }

          // Durum
          const AUTO_MS = 3000;  // otomatik geçiş süresi
          let cur = 0;
          let timer = null;

          // Yardımcılar
          function setActive(i) {
              slides.forEach((s, k) => s.classList.toggle('is-active', k === i));
          }
          function goTo(i) {
              cur = (i + slides.length) % slides.length;
              setActive(cur);
          }
          function next() { goTo(cur + 1); }
          function prev() { goTo(cur - 1); }

          function start() {
              stop();
              const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
              if (!prefersReduced && slides.length > 1) {
                  timer = setInterval(next, AUTO_MS);
              }
          }
          function stop() { if (timer) clearInterval(timer); timer = null; }

          // Olaylar
          nextBtn.addEventListener('click', () => { stop(); next(); start(); });
          prevBtn.addEventListener('click', () => { stop(); prev(); start(); });

          container.addEventListener('mouseenter', stop);
          container.addEventListener('mouseleave', start);

          // Klavye desteği (opsiyonel)
          container.addEventListener('keydown', (e) => {
              if (e.key === 'ArrowRight') { stop(); next(); start(); }
              if (e.key === 'ArrowLeft')  { stop(); prev(); start(); }
          });
          container.tabIndex = 0; // klavye odaklanması için

          // Başlat
          imagesReady(track).then(() => {
              // İlkini görünür yap
              setActive(cur);

              // Tek logo varsa okları gizle
              const multi = slides.length > 1;
              prevBtn.style.display = multi ? '' : 'none';
              nextBtn.style.display = multi ? '' : 'none';

              start();
          });
      })();
  </script>




</body>
</html>