<?php
require_once 'config.php';
session_start();
/**
 * module_view.php — JS'siz, PHP ile basılan kurs modül sayfası
 * - Notlar yerine DÜMDÜZ METİN alanı var (nl2br + htmlspecialchars)
 * - Dokümanlar PHP döngüsü ile basılıyor
 * - Sağda içerik listesi
 * - İkon kütüphanesi yok; sade HTML
 *
 * Kullanım:
 * - $course, $module, $video_url, $plain_text, $documents, $modules (sidebar) değişkenlerini
 *   controller’dan doldurup bu dosyayı include edebilirsin.
 */

// Yardımcı güvenli kaçış
function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$content = getModuleContent($_GET['id'], $_GET['ord']);
$course = getCourseDetails($_GET['course']);
$video_url = $content['data_vid'];
$h = getCourseDetails($_GET['course']);

$modules = getModules($h['id']);
$count = count($modules);

if ($count == ($_GET['ord'] + 1)) {
    $isLast = true;
}
else{
    $isLast = false;
}

$next_url = "moduleDetails.php?course=" . $_GET['course'] . "&id=" . $_GET['id'] . "&ord=" . ($_GET['ord'] + 1);
$prev_url = "moduleDetails.php?course=" . $_GET['course'] . "&id=" . $_GET['id'] . "&ord=" . ($_GET['ord'] - 1);


// ---- ÖRNEK / FALLBACK VERİLER (Controller doldurmadıysa) ----
if (!isset($course)) {
    $course = [
        'id'    => 1,
        'title' => 'Robotik ve Yapay Zeka Başlangıç Kursu',
        'back_url' => '#', // kurs detayına dön linki
    ];
}
if (!isset($module)) {
    $module = [
        'id'          => 2,
        'title'       => 'Modül 2: Robotik Nedir?',
        'description' => 'Bu modülde robotik biliminin temel prensiplerini ve modern robotların nasıl çalıştığını öğreneceksiniz.',
        'prev_url'    => '#',
        'next_url'    => '#',
    ];
}
if (!isset($video_url)) {
    $video_url = 'https://www.w3schools.com/html/mov_bbb.mp4';
}
if (!isset($plain_text)) {
    // DÜMDÜZ METİN — zengin içerik istemiyorsan bu alanı düz metin olarak tut
    $plain_text = "Robotik; algılama → karar verme → eyleme geçme döngüsüne dayanır.\n".
        "Bu modülde sensör türleri, aktüatör temelleri ve basit kontrol şemalarını göreceksiniz.\n\n".
        "- Açık çevrim vs kapalı çevrim\n- Temel sensör sınıfları\n- Basit kontrol şeması";
}
/*
if (!isset($documents)) {
    $documents = [
        ['title' => 'Robotik Giriş Slaytları (PDF)', 'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'size' => '1.2 MB'],
        ['title' => 'Şema - Basit Sensör Yerleşimi (PNG)', 'url' => 'https://upload.wikimedia.org/wikipedia/commons/3/3f/Fronalpstock_big.jpg', 'size' => '820 KB'],
    ];
}
*/
if (!isset($zip_url)) {
    $zip_url = null; // örn: "/download/module-2.zip"
}
/*
if (!isset($modules)) {
    // Sağdaki içerik listesi
    $modules = [
        ['title' => 'Giriş ve Kurs Tanıtımı', 'url' => '#', 'active' => false],
        ['title' => 'Robotik Nedir?',         'url' => '#', 'active' => true],
        ['title' => 'Yapay Zeka Temelleri',   'url' => '#', 'active' => false],
        ['title' => 'İlk Robotunu Kodla',     'url' => '#', 'active' => false],
    ];
}
*/
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">

    <title><?= e($course['title']) ?> - Modül Görünümü</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body{font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif}
    </style>
</head>
<body class="bg-gray-100">
<!-- NAVBAR -->
<?php
require_once 'navbar.php';
?>

<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Üst Başlık ve Geri Dön -->
        <div class="mb-6 flex justify-between items-center">
            <a href="courseDetails.php?course=<?=$course['course_uid']?>" class="inline-flex items-center text-[#E5AE32] hover:bg-yellow-100/50 p-2 rounded-md">
                <span class="mr-2">←</span> Kurs Detayına Geri Dön
            </a>
            <h1 class="text-2xl font-bold text-gray-900"><?= e($course['title']) ?></h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- SOL / ANA İÇERİK -->
            <div class="lg:col-span-3 space-y-6">

                <!-- Video -->
                <?php if (!empty($video_url)): ?>
                    <div class="bg-black rounded-lg overflow-hidden relative aspect-video">
                        <video class="w-full h-full" controls autoplay>
                            <source src="<?=$video_url?>" type="video/mp4">
                            Tarayıcınız video etiketini desteklemiyor.
                        </video>
                    </div>
                <?php endif; ?>

                <!-- Modül başlık / açıklama -->
                <div class="p-6 bg-white rounded-lg shadow-md">
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold text-gray-900"><?= e($course['title']) ?></h2>
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <a href="<?= e($prev_url) ?>" class="flex items-center <?= $_GET['ord'] == 0 ? 'pointer-events-none text-gray-700' : 'text-[#E5AE32] hover:opacity-80'?> transition-colors">
                            <span class="mr-2">«</span> Önceki Modül
                        </a>
                        <a href="<?= e($next_url) ?>" class="flex items-center <?= $isLast == 0 ? 'pointer-events-none text-gray-700' : 'text-[#E5AE32] hover:opacity-80'?> transition-colors">
                            Sonraki Modül <span class="ml-2">»</span>
                        </a>
                    </div>
                </div>

                <!-- DÜMDÜZ METİN (Not değil) -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Metin</h3>
                    </div>
                    <div class="p-6 text-gray-800 leading-relaxed whitespace-pre-line">
                        <?= $content['data'] ?>
                    </div>
                </div>

                <!-- DOKÜMANLAR -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Dokümanlar</h3>
                        <div class="flex items-center gap-3">
                            <?php if ($zip_url): ?>
                                <a href="<?= e($zip_url) ?>" class="text-sm font-semibold text-[#E5AE32] underline">Tümünü İndir (.zip)</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    /*
                     * <?php if (!empty($documents)): ?>
                        <div class="p-6 space-y-3">
                            <?php foreach ($documents as $doc): ?>
                                <div class="flex items-center justify-between p-3 border rounded-lg">
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 truncate"><?= e($doc['title'] ?? 'Doküman') ?></div>
                                        <?php if (!empty($doc['size'])): ?>
                                            <div class="text-xs text-gray-500"><?= e($doc['size']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="<?= $content['data_file'] ?>" download class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">
                                            İndir
                                        </a>
                                        <a href="<?= $content['data_file'] ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">
                                            Yeni Sekmede Aç
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-gray-600">Bu modül için doküman bulunmuyor.</div>
                    <?php endif; ?>
                     */


                    ?>

                    <?php if (!empty($content['data_file'])): ?>
                        <div class="p-6 space-y-3">
                            <div class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 truncate"><?= e($doc['title'] ?? 'Doküman') ?></div>
                                    <?php if (!empty($doc['size'])): ?>
                                        <div class="text-xs text-gray-500"><?= e($doc['size']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="<?= $content['data_file'] ?>" download class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">
                                        İndir
                                    </a>
                                    <a href="<?= $content['data_file'] ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">
                                        Yeni Sekmede Aç
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-gray-600">Bu modül için doküman bulunmuyor.</div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- SAĞ / KURS İÇERİĞİ -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Kurs İçeriği</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <?php
                        $i = 0;
                        foreach ($modules as $m):
                            ?>
                            <a href="moduleDetails.php?course=<?=$_GET['course']?>&id=<?=$_GET['id']?>&ord=<?=$i?>"
                               class="flex items-center space-x-4 p-3 rounded-lg transition-colors
                               <?= $i == $_GET['ord'] ? 'bg-yellow-100/60 border-l-4 border-yellow-500' : 'hover:bg-gray-50' ?>">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0
                                    <?= $i == $_GET['ord'] ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-600' ?>">
                                    ▶
                                </div>
                                <div>
                                    <h4 class="font-medium <?= $i == $_GET['ord'] ? 'text-yellow-700' : 'text-gray-900' ?>">
                                        <?= e($m['title']) ?>
                                    </h4>
                                </div>
                            </a>
                        <?php
                            $i++;
                        endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['print'])): ?>
            <style>
                @media print {
                    nav, .lg\:col-span-1, a[href*="print=1"] { display:none !important; }
                    .lg\:col-span-3 { grid-column: 1 / -1 !important; }
                    body { background: #fff !important; }
                    .shadow-md, .shadow { box-shadow: none !important; }
                }
            </style>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
