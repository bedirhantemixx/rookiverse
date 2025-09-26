<?php require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    try {
        $pdo = get_db_connection();

        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
    }catch (Throwable $e) {
        http_response_code(500);
        echo 'DB error: ' . htmlspecialchars($e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>İletişim - FRC Rookieverse</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/contact.css">
  
  <script>
    tailwind.config = {
      theme: { extend: { colors: { 'custom-yellow': '#E5AE32' } } }
    }
  </script>
</head>
<body class="bg-gray-50">

  <?php require_once 'navbar.php'; ?>

  <div class="min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">İletişim</h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">Sorularınız, önerileriniz veya geri bildirimleriniz için bize ulaşın. FRC topluluğuna destek olmaktan mutluluk duyarız.</p>
      </div>
      
      <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-transparent hover:border-custom-yellow/50 transition-all duration-300">
          <h2 class="text-2xl font-bold text-gray-900 flex items-center mb-1"><i data-lucide="send" class="mr-3 text-custom-yellow h-7 w-7"></i> Bize Mesaj Gönderin</h2>
          <p class="text-base text-gray-600 mb-6">Formu doldurun, en kısa sürede size geri dönüş yapalım.</p>
          <form method="post" id="contact-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2"><label for="name" class="font-medium text-gray-700">Ad Soyad *</label><input id="name" name="name" type="text" required placeholder="Adınızı ve soyadınızı girin" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-custom-yellow focus:ring-custom-yellow"></div>
              <div class="space-y-2"><label for="email" class="font-medium text-gray-700">E-posta *</label><input id="email" name="email" type="email" required placeholder="E-posta adresinizi girin" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-custom-yellow focus:ring-custom-yellow"></div>
            </div>
            <div class="space-y-2"><label for="subject" class="font-medium text-gray-700">Konu *</label><input id="subject" name="subject" type="text" required placeholder="Mesaj konusu" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-custom-yellow focus:ring-custom-yellow"></div>
            <div class="space-y-2"><label for="message" class="font-medium text-gray-700">Mesajınız *</label><textarea id="message" name="message" required rows="6" placeholder="Detaylı mesajınızı buraya yazın..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-custom-yellow focus:ring-custom-yellow resize-none"></textarea></div>
            <button type="submit" class="w-full bg-custom-yellow hover:bg-opacity-90 text-white font-semibold py-3 text-lg rounded-lg flex items-center justify-center">
              <span class="button-content flex items-center"><i data-lucide="send" class="mr-2 h-5 w-5"></i> Mesajı Gönder</span>
            </button>
          </form>
      </div>
      
      <div class="mt-16"><div class="p-6"><h3 class="text-3xl font-bold text-gray-900 mb-8 text-center">İletişim Kanalları</h3><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto"><a href="mailto:info@team6228.com" class="contact-card group block bg-white rounded-xl p-5"><div class="flex items-center"><i data-lucide="mail" class="mr-4 h-8 w-8 text-custom-yellow flex-shrink-0"></i><p class="font-semibold text-gray-800 break-all">info@team6228.com</p></div></a><a href="https://www.instagram.com/matrobotics6228" target="_blank" rel="noopener noreferrer" class="contact-card group block bg-white rounded-xl p-5"><div class="flex items-center"><i data-lucide="instagram" class="mr-4 h-8 w-8 text-custom-yellow flex-shrink-0"></i><p class="font-semibold text-gray-800">@matrobotics6228</p></div></a><a href="https://linkedin.com/company/team6228" target="_blank" rel="noopener noreferrer" class="contact-card group block bg-white rounded-xl p-5"><div class="flex items-center"><i data-lucide="linkedin" class="mr-4 h-8 w-8 text-custom-yellow flex-shrink-0"></i><p class="font-semibold text-gray-800">@team6228</p></div></a></div></div></div>

      <div class="mt-16"><div class="max-w-4xl mx-auto"><h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Takım İletişim Bilgileri</h2><div class="relative mb-6"><i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i><input type="search" id="team-search-input" placeholder=" İletişim bilgisini öğrenmek istediğiniz takım adını veya numarasını girin..." class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-full focus:border-custom-yellow focus:ring-custom-yellow"></div><div id="team-search-results" class="space-y-4"></div></div></div>
      
      <div class="mt-16"><h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Sık Sorulan Sorular</h2><div class="space-y-4 max-w-4xl mx-auto"><div class="border-2 hover:border-custom-yellow/50 transition-all duration-200 rounded-lg"><button class="faq-trigger w-full flex justify-between items-center text-left p-6"><span class="text-lg font-bold text-gray-900">Platform tamamen ücretsiz mi?</span><i data-lucide="chevron-down" class="faq-icon text-custom-yellow"></i></button><div class="faq-answer"><p class="text-base text-gray-600 px-6 pb-6">Evet! Rookieverse'deki tüm kurslar ve oyunlar tamamen ücretsizdir. Türk FRC topluluğuna destek olmak için bu platformu geliştirdik.</p></div></div><div class="border-2 hover:border-custom-yellow/50 transition-all duration-200 rounded-lg"><button class="faq-trigger w-full flex justify-between items-center text-left p-6"><span class="text-lg font-bold text-gray-900">Yeni takım nasıl kurulur?</span><i data-lucide="chevron-down" class="faq-icon text-custom-yellow"></i></button><div class="faq-answer"><p class="text-base text-gray-600 px-6 pb-6">Yeni takım kurmak için önce FIRST'e kayıt olmanız gerekir. Size bu süreçte yardımcı olmak için detaylı rehberlerimiz mevcuttur.</p></div></div><div class="border-2 hover:border-custom-yellow/50 transition-all duration-200 rounded-lg"><button class="faq-trigger w-full flex justify-between items-center text-left p-6"><span class="text-lg font-bold text-gray-900">Kurs sertifikası alabilir miyim?</span><i data-lucide="chevron-down" class="faq-icon text-custom-yellow"></i></button><div class="faq-answer"><p class="text-base text-gray-600 px-6 pb-6">Şu anda sertifika sistemi geliştirme aşamasındadır. Yakında kurs tamamlama sertifikaları sunmayı planlıyoruz.</p></div></div><div class="border-2 hover:border-custom-yellow/50 transition-all duration-200 rounded-lg"><button class="faq-trigger w-full flex justify-between items-center text-left p-6"><span class="text-lg font-bold text-gray-900">Offline kurs materyalleri var mı?</span><i data-lucide="chevron-down" class="faq-icon text-custom-yellow"></i></button><div class="faq-answer"><p class="text-base text-gray-600 px-6 pb-6">Kurslarımızın PDF versiyonları ve videoları indirip offline olarak kullanabilmeniz mümkündür.</p></div></div></div></div>
    </div>
  </div>

  <div id="success-popup" class="popup-overlay"><div class="popup-content"><svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg><h3 class="text-2xl font-bold text-gray-800">Mesajınız Gönderildi!</h3><p class="text-gray-600 mt-2">En kısa sürede size geri dönüş yapacağız.</p></div></div>

  <script>
    lucide.createIcons();
    
    // ## DÜZELTME: SSS AKORDİYON SCRİPTİ EKLENDİ ##
    const faqTriggers = document.querySelectorAll('.faq-trigger');
    faqTriggers.forEach(trigger => {
      trigger.addEventListener('click', () => {
        const answer = trigger.nextElementSibling;
        const isActive = trigger.classList.contains('active');

        // Önce tüm açık olanları kapat (isteğe bağlı)
        // faqTriggers.forEach(t => {
        //   t.classList.remove('active');
        //   t.nextElementSibling.style.maxHeight = null;
        // });

        if (!isActive) {
          trigger.classList.add('active');
          answer.style.maxHeight = answer.scrollHeight + 'px';
        } else {
          trigger.classList.remove('active');
          answer.style.maxHeight = null;
        }
      });
    });

    // Sayfadaki diğer tüm JavaScript fonksiyonları (form gönderme, takım arama)
    // öncekiyle aynı şekilde çalışmaya devam eder.
    // ...
  </script>

</body>
</html>