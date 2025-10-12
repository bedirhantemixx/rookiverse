<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Gizlilik Politikası - Rookieverse</title>
    <link rel="icon" type="image/x-icon" href="assets/images/rokiverse_icon.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EDSVL8LRCY"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-EDSVL8LRCY');
    </script>
</head>
<body class="bg-white text-gray-800">

<?php require_once 'navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-20 space-y-8">
    <h1 class="text-4xl font-bold text-center mb-8">Gizlilik Politikası</h1>

    <p>
        Rookieverse olarak gizliliğinize önem veriyoruz.
        Bu politika, kişisel verilerinizin nasıl toplandığını, kullanıldığını ve korunduğunu açıklar.
    </p>

    <h2 class="text-2xl font-semibold mt-6">1. Topladığımız Veriler</h2>
    <ul class="list-disc list-inside space-y-2">
        <li>E-posta adresi (bülten aboneliği veya iletişim formları aracılığıyla)</li>
        <li>IP adresi ve tarayıcı bilgileri (güvenlik ve istatistiksel analiz amacıyla)</li>
        <li>Çerez verileri (tercihleri hatırlamak için)</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-6">2. Verilerin Kullanımı</h2>
    <p>
        Topladığımız veriler yalnızca platform hizmetlerini geliştirmek, kullanıcı deneyimini iyileştirmek
        ve gerektiğinde sizinle iletişime geçmek için kullanılır. Veriler üçüncü taraflarla izinsiz paylaşılmaz.
    </p>

    <h2 class="text-2xl font-semibold mt-6">3. Haklarınız</h2>
    <p>
        Kişisel verilerinize erişme, düzeltme veya silme hakkına sahipsiniz.
        Bu haklarınızı kullanmak için bizimle <a href="<?php echo BASE_URL; ?>/contact.php" class="text-custom-yellow underline">iletişime geçebilirsiniz</a>.
    </p>

    <p class="mt-8 text-sm text-gray-500">Son güncelleme: 30 Eylül 2025</p>
</main>

<?php require_once 'footer.php'; ?>

<script>lucide.createIcons();</script>
</body>
</html>
