<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Çerez Politikası - Rookieverse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
</head>
<body class="bg-white text-gray-800">

<?php require_once 'navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-20 space-y-8">
    <h1 class="text-4xl font-bold text-center mb-8">Çerez Politikası</h1>

    <p>
        Rookieverse, kullanıcı deneyiminizi geliştirmek ve site işlevselliğini artırmak için çerezleri kullanır.
        Bu sayfa çerezlerin ne olduğunu, nasıl kullanıldığını ve nasıl yönetileceğini açıklar.
    </p>

    <h2 class="text-2xl font-semibold mt-6">1. Çerez Nedir?</h2>
    <p>
        Çerezler, ziyaret ettiğiniz web siteleri tarafından tarayıcınıza kaydedilen küçük veri dosyalarıdır.
        Bu dosyalar, site tercihlerinizi hatırlamak ve istatistiksel bilgi toplamak için kullanılır.
    </p>

    <h2 class="text-2xl font-semibold mt-6">2. Kullandığımız Çerez Türleri</h2>
    <ul class="list-disc list-inside space-y-2">
        <li><b>Zorunlu çerezler:</b> Sitenin temel işlevleri için gereklidir.</li>
        <li><b>İşlevsel çerezler:</b> Tercihlerinizi kaydeder (ör. dil seçimi).</li>
        <li><b>Analitik çerezler:</b> Site performansını ve kullanım istatistiklerini analiz eder.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-6">3. Çerezleri Yönetme</h2>
    <p>
        Tarayıcı ayarlarınızdan çerezleri silebilir veya engelleyebilirsiniz.
        Ancak bazı site özellikleri çerezsiz doğru çalışmayabilir.
    </p>

    <p class="mt-8 text-sm text-gray-500">Son güncelleme: 30 Eylül 2025</p>
</main>

<?php require_once 'footer.php'; ?>

<script>lucide.createIcons();</script>
</body>
</html>
