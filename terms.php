<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanım Şartları - Rookieverse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
</head>
<body class="bg-white text-gray-800">

<?php require_once 'navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-20 space-y-8">
    <h1 class="text-4xl font-bold text-center mb-8">Kullanım Şartları</h1>

    <p>
        Rookieverse platformunu kullanarak aşağıdaki şartları kabul etmiş sayılırsınız.
        Bu şartları kabul etmiyorsanız lütfen platformu kullanmayınız.
    </p>

    <h2 class="text-2xl font-semibold mt-6">1. Hizmetin Amacı</h2>
    <p>
        Rookieverse, FRC topluluğuna yönelik ücretsiz eğitim kaynakları, dokümanlar ve oyunlar sunan bir platformdur.
        Hizmetler tamamen eğitim amaçlıdır.
    </p>

    <h2 class="text-2xl font-semibold mt-6">2. Kullanıcı Sorumlulukları</h2>
    <ul class="list-disc list-inside space-y-2">
        <li>Platformu yalnızca yasal amaçlarla kullanmayı kabul edersiniz.</li>
        <li>Paylaştığınız içeriklerin doğruluğundan ve telif haklarına uygunluğundan siz sorumlusunuz.</li>
        <li>Platformu kötüye kullanmanız durumunda erişiminiz engellenebilir.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-6">3. Değişiklik Hakkı</h2>
    <p>
        Rookieverse, kullanım şartlarını önceden bildirmeksizin değiştirme hakkını saklı tutar.
        Güncel şartlar bu sayfada yayımlandığı andan itibaren geçerlidir.
    </p>

    <p class="mt-8 text-sm text-gray-500">Son güncelleme: 30 Eylül 2025</p>
</main>

<?php require_once 'footer.php'; ?>

<script>lucide.createIcons();</script>
</body>
</html>
