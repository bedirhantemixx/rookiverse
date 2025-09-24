<?php require_once 'config.php';
//a

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
          <div class="hero-slider-container">
            <div class="hero-slider-track">
                <div class="hero-slide"><img src="<?php echo BASE_URL; ?>/assets/images/guncel_takim.jpg" alt="FRC Robot 1"></div>
                <div class="hero-slide"><img src="<?php echo BASE_URL; ?>/assets/images/guncel_takim.jpg" alt="FRC Robot 2"></div>
                <div class="hero-slide"><img src="<?php echo BASE_URL; ?>/assets/images/guncel_takim.jpg" alt="FRC Robot 3"></div>
                <div class="hero-slide"><img src="<?php echo BASE_URL; ?>/assets/images/guncel_takim.jpg" alt="FRC Robot 4"></div>
            </div>
            <button class="hero-slider-arrow left"><i data-lucide="chevron-left"></i></button>
            <button class="hero-slider-arrow right"><i data-lucide="chevron-right"></i></button>
            <div class="hero-slider-dots"></div>
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
                                  <span class="text-sm text-gray-500">⏱️ 2 saat</span>
                                  <a href="courseDetails.php?course=<?=$course['course_uid']?>" class="px-3 py-1.5 text-sm bg-custom-yellow text-white rounded-md">Detayları Gör</a>
                              </div>
                          </div>
                      </div>
                  <?php endforeach;?>

              </div>

              <div class="text-center mt-12">
                  <a href="<?php echo BASE_URL; ?>/courses" class="inline-flex items-center justify-center border-2 border-custom-yellow text-custom-yellow hover:bg-custom-yellow hover:text-white px-8 py-3 text-lg font-semibold rounded-md">
                      Tüm Kursları Görüntüle
                  </a>
              </div>
          </div>
      </section>


      <section class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16"><h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Destekleyen Takımlar</h2><p class="text-xl text-gray-600">Bu platformun gelişimine katkıda bulunan değerli FRC takımları</p></div>
            <div class="logo-slider-container">
                <button id="prevBtn" class="slider-arrow left"><i data-lucide="chevron-left" class="text-gray-700"></i></button>
                <div class="logo-slider-track">
                    <?php
                    $contributors = getContributors();
                    foreach ($contributors as $cont):
                        $id = $cont['id']

                    ?>
                        <div class="logo-slide"><a href="<?= $cont['website'] ? $cont['website'] : "teamsCourses?id=$id]" ?>" target="_blank"><img src="<?= $cont['profile_pic_path'] ?>" alt="<?=$cont['team_name']?>"></a></div>
                    <?php endforeach;?>
                    </div>
                <button id="nextBtn" class="slider-arrow right"><i data-lucide="chevron-right" class="text-gray-700"></i></button>
            </div>
        </div>
    </section>

    <section class="bg-custom-yellow py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"><div class="space-y-6"><h2 class="text-3xl lg:text-4xl font-bold text-white">FRC Maceranıza İlk Adımı Atmaya Hazır mısınız?</h2><p class="text-xl text-white/90 max-w-2xl mx-auto">Robotik dünyasında yol gösterecek tüm kaynaklar burada. Ücretsiz eğitim içeriklerimiz ve takım destek programlarımızla hem bireysel katılımcıları hem de takımları başarıya taşıyoruz.</p><div class="flex flex-col sm:flex-row gap-4 justify-center"><a href="<?php echo BASE_URL; ?>/courses.php" class="inline-flex items-center justify-center bg-white text-custom-yellow hover:bg-gray-100 px-8 py-3 text-lg font-semibold rounded-md"><i data-lucide="book-open" class="mr-2 w-5 h-5"></i>Ücretsiz Başla</a><a href="<?php echo BASE_URL; ?>/team-login" class="inline-flex items-center justify-center border-2 border-white text-white hover:bg-white hover:text-custom-yellow px-8 py-3 text-lg font-semibold rounded-md"><i data-lucide="log-in" class="mr-3 w-5 h-5"></i>Takım Girişi</a></div></div></div>
    </section>
  </div>

  <script>
    lucide.createIcons();

    // ## GÜNCELLENMİŞ VE TAM SLIDER SCRIPT'İ ##
    // Hero Slider
    document.addEventListener('DOMContentLoaded', function () {
        // ... Hero Slider Script ...
    });

    // Logo Slider
    document.addEventListener('DOMContentLoaded', function () {
      const sliderContainer = document.querySelector('.logo-slider-container');
      if (!sliderContainer) return;
      const track = sliderContainer.querySelector('.logo-slider-track');
      const slides = Array.from(track.children);
      const nextButton = document.getElementById('nextBtn');
      const prevButton = document.getElementById('prevBtn');
      let currentIndex = 0;
      let autoPlayInterval;
      
      const goToSlide = (index) => {
        track.style.transform = 'translateX(' + (-100 * index) + '%)';
        currentIndex = index;
      };
      const nextSlide = () => {
        const nextIndex = (currentIndex + 1) % slides.length;
        goToSlide(nextIndex);
      };
      const prevSlide = () => {
        const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
        goToSlide(prevIndex);
      };
      const startAutoPlay = () => {
        stopAutoPlay();
        autoPlayInterval = setInterval(nextSlide, 1500);
      };
      const stopAutoPlay = () => clearInterval(autoPlayInterval);
      nextButton.addEventListener('click', () => { stopAutoPlay(); nextSlide(); startAutoPlay(); });
      prevButton.addEventListener('click', () => { stopAutoPlay(); prevSlide(); startAutoPlay(); });
      sliderContainer.addEventListener('mouseenter', stopAutoPlay);
      sliderContainer.addEventListener('mouseleave', startAutoPlay);
      startAutoPlay();
    });
  </script>
</body>
</html>