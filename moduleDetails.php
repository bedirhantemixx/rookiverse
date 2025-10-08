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



$pdo = get_db_connection();
$details = getCourseDetails($_GET['course']);
$course_id = (int)($details['id'] ?? 0);
if ($course_id < 1) {
    http_response_code(400);
    exit('Geçersiz kurs.');
}




$preview = false;
if (isset($_GET['preview'])) {
    if ($_GET['preview'] == '1'){
        if (!isset($_SESSION['admin_logged_in'])){
            header("location: courses.php");
        }
        else{
            $preview = true;
        }
    }
}


/**
 * Eğer sisteminde normal hesaplı kullanıcı girişi de varsa,
 * önce “hesaplı yetki”yi kontrol edebilirsin. Değilse anonim akışa bak.
 */

// 1) Hızlı yol: kurs cookie’si varsa içeri al

if (!$preview){
    $courseCookie = "access_course_{$course_id}";
    if (!isset($_COOKIE[$courseCookie])) {
        // 2) Cookie yoksa anon_id ile DB’de kayıt var mı bak
        $anonId = $_COOKIE['rv_anon'] ?? null;
        if ($anonId) {
            $stmt = $pdo->prepare("
          SELECT 1 FROM course_guest_enrollments
          WHERE course_id = ? AND anon_id = ? LIMIT 1
        ");
            $stmt->execute([$course_id, $anonId]);
            if ($stmt->fetchColumn()) {
                // Tekrar cookie bas (kullanıcıyı hatırla)
                setcookie($courseCookie, "1", time() + 3600 * 24 * 365, "/", "", false, true);
            } else {
                http_response_code(403);
                header('location: courseDetails.php?course='.$course_id);
                exit('Bu kursa erişiminiz yok. Önce kaydolmanız gerekiyor.');
            }
        } else {
            http_response_code(403);
            header('location: courseDetails.php?course='.$course_id);
            exit('Bu kursa erişiminiz yok. Önce kaydolmanız gerekiyor.');
        }
    }
}


// Buradan sonrası: kurs içeriğini render et (örn. modüller, videolar vs.)
// İstersen burada last_seen_at güncelle:
$anonId = $_COOKIE['rv_anon'] ?? null;
if ($anonId) {
    $upd = $pdo->prepare("
    UPDATE course_guest_enrollments SET last_seen_at = NOW()
    WHERE course_id = ? AND anon_id = ?
  ");
    $upd->execute([$course_id, $anonId]);
}




// Yardımcı güvenli kaçış
function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$content = getModuleContent($_GET['id'], $_GET['ord']);
$course = getCourseDetails($_GET['course']);
$h = getCourseDetails($_GET['course']);

if($preview){
    $modules = getModulesPreviewSafe($h['id']);
}
else{
    $modules = getModules($h['id']);

}

$count = count($modules);
$thisModule = null;
foreach($modules as $m){
    if($m['sort_order'] == $_GET['ord']){
        $thisModule = $m;
    }
}
foreach($modules as $m){
    if($m['sort_order'] == ($thisModule['sort_order'] - 1)){
        $prev_url = "moduleDetails.php?course=" . $_GET['course'] . "&id=" . $m['id'] . "&ord=" . ($_GET['ord'] - 1);
    }
    else if($m['sort_order'] == ($thisModule['sort_order'] + 1)){
        $next_url = "moduleDetails.php?course=" . $_GET['course'] . "&id=" . $m['id'] . "&ord=" . ($_GET['ord'] + 1);

    }
}

if ($thisModule['status'] != 'approved'){
    header("location: courses.php");
}

if ($count == ($_GET['ord'] + 1)) {
    $isLast = true;
}
else{
    $isLast = false;
}



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

    <title><?= e($thisModule['title']) ?> - Modül Görünümü</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body{font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans',sans-serif}
    </style>
</head>
<body class="bg-gray-100">
<!-- NAVBAR -->
<?php
if (!$preview){
    require_once 'navbar.php';
}


?>

<div class="min-h-screen py-8">
    <?php

    if ($preview): ?>
        <!-- ÖNİZLEME HAVA PANELİ (SAĞ ÜST) -->
        <div
                class="fixed top-16 left-3 z-50 w-[320px] max-w-[90vw] border-2 border-[#E5AE32]/30 rounded-xl p-4 bg-white shadow-xl"
                role="region" aria-label="Önizleme Kontrolleri"
        >
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="shield-check"></i>
                <h3 class="font-bold text-gray-900">Önizleme Modu</h3>
            </div>

            <p class="text-sm text-gray-700 mb-4">
                Bu sayfa <span class="font-semibold text-[#E5AE32]">önizleme</span> modunda.
            </p>

            <div class="space-y-2">
                <a href="admin/course_actions.php"
                   class="w-full inline-flex items-center justify-center rounded-md border-2 border-[#E5AE32]/40 px-4 py-2 font-semibold text-[#E5AE32] hover:bg-[#E5AE32]/10">
                    <i data-lucide="arrow-left" class="mr-2" style="width:18px;height:18px;"></i>
                    Panele Geri Dön
                </a>

                <?php
                // CSRF yoksa üret
                if (empty($_SESSION['csrf'])) {
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                }
                $csrf = htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8');
                ?>

                <!-- ONAYLA (POST) -->
                <form method="post" action="admin/course_actions.php" class="space-y-0">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="approve_module">
                    <input type="hidden" name="module_id" value="<?= (int)$thisModule['id'] ?>">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">
                        <i data-lucide="check-circle" class="mr-2" style="width:18px;height:18px;"></i>
                        Onayla
                    </button>
                </form>

                <!-- REDDET (POST) -->
                <form method="post" action="admin/course_actions.php" class="space-y-0 mt-2">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="reject_module">
                    <input type="hidden" name="module_id" value="<?= (int)$thisModule['id'] ?>">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700">
                        <i data-lucide="x-circle" class="mr-2" style="width:18px;height:18px;"></i>
                        Reddet
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


        <!-- Üst Başlık ve Geri Dön -->
        <?php
        if (!$preview):
        ?>
            <div class="mb-6 flex justify-between items-center">
                <a href="courseDetails.php?course=<?=$course['course_uid']?>" class="inline-flex items-center text-[#E5AE32] hover:bg-yellow-100/50 p-2 rounded-md">
                    <span class="mr-2">←</span> Kurs Detayına Geri Dön
                </a>
                <h1 class="text-2xl font-bold text-gray-900"><?= $course['title'] ?></h1>
            </div>
        <?php
        endif;
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- SOL / ANA İÇERİK -->
            <div class="lg:col-span-3 space-y-6">



                <!-- Modül başlık / açıklama -->
                <div class="p-6 bg-white rounded-lg shadow-md">
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold text-gray-900"><?=$thisModule['title']?></h2>
                    </div>
                    <?php

                    ?>
                    <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <?php
                        if (!$preview):
                        ?>
                            <a href="<?= e($prev_url) ?>" class="flex items-center <?= $_GET['ord'] == 0 ? 'pointer-events-none text-gray-700' : 'text-[#E5AE32] hover:opacity-80'?> transition-colors">
                                <span class="mr-2">«</span> Önceki Modül
                            </a>
                            <a href="<?= e($next_url) ?>" class="flex items-center <?= $isLast ? 'pointer-events-none text-gray-700' : 'text-[#E5AE32] hover:opacity-80'?> transition-colors">
                                Sonraki Modül <span class="ml-2">»</span>
                            </a>
                        <?php endif;?>
                    </div>
                </div>

                <?php
                foreach ($content as $cont):
                    if($cont['type'] == 'text'):
                        $empty = false;

                        ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">Metin</h3>
                        </div>
                        <div class="p-6 text-gray-800 leading-relaxed whitespace-pre-line">
                            <?= $cont['data'] ?>
                        </div>
                    </div>
                <?php
                    elseif ($cont['type'] == 'video'):
                        $empty = false;

                ?>
                    <div class="bg-black rounded-lg overflow-hidden relative aspect-video">
                        <video class="w-full h-full" controls autoplay>
                            <source src="<?=$cont['data']?>" type="video/mp4">
                            Tarayıcınız video etiketini desteklemiyor.
                        </video>
                    </div>
                    <?php
                    elseif ($cont['type'] == 'youtube'):
                    ?>
                        <?php
                        $raw = trim((string)$cont['data']); // DB'de tuttuğun değer (örn: https://youtu.be/dQw4w9WgXcQ)

// ID'yi çek
                        $ytId = '';
                        if (preg_match('~^[A-Za-z0-9_-]{10,20}$~', $raw)) {
                            $ytId = $raw; // zaten çıplak ID
                        } elseif (preg_match('~youtu\.be/([^/?#]+)~', $raw, $m)) {
                            $ytId = $m[1];
                        } elseif (preg_match('~v=([^&#/]+)~', $raw, $m)) {
                            $ytId = $m[1];
                        } elseif (preg_match('~/embed/([^/?#]+)~', $raw, $m)) {
                            $ytId = $m[1];
                        } elseif (preg_match('~/shorts/([^/?#]+)~', $raw, $m)) {
                            $ytId = $m[1];
                        }
                        ?>

                        <?php if ($ytId): ?>
                        <div class="aspect-video">
                            <iframe
                                    class="w-full h-full"
                                    src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId, ENT_QUOTES) ?>"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                            </iframe>
                        </div>
                    <?php endif; ?>

                    <?php

                    elseif ($cont['type'] == 'doc'):
                        $empty = false;

                ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900 truncate"><?= e($doc['title'] ?? 'Doküman') ?></div>
                                <?php if (!empty($doc['size'])): ?>
                                    <div class="text-xs text-gray-500"><?= e($doc['size']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="<?= $cont['data'] ?>" download class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">
                                    İndir
                                </a>
                                <a href="<?= $cont['data'] ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">
                                    Yeni Sekmede Aç
                                </a>
                            </div>
                        </div>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>

            </div>

            <!-- SAĞ / KURS İÇERİĞİ -->
            <!-- SAĞ / KURS İÇERİĞİ -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Kurs İçeriği</h3>
                    </div>

                    <?php if ($preview): ?>
                        <!-- ÖNİZLEMEDE LİSTE YOK -->
                        <div class="p-4">
                            <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="eye-off" class="mt-0.5" style="width:20px;height:20px;"></i>
                                    <div>
                                        <p class="text-sm text-yellow-800">
                                            Diğer modülleri önizlemek için <span class="font-semibold">panele geri dön</span> ve oradan modül detayına gir.
                                        </p>
                                        <a href="admin/course_actions.php"
                                           class="mt-3 inline-flex items-center rounded-md border-2 border-yellow-300 px-3 py-1.5 text-sm font-semibold text-yellow-800 hover:bg-yellow-100">
                                            <i data-lucide="arrow-left" class="mr-2" style="width:16px;height:16px;"></i>
                                            Panele Geri Dön
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- NORMALDE MODÜL LİSTESİ -->
                        <div class="p-4 space-y-2">
                            <?php $i = 0; foreach ($modules as $m): ?>
                                <a href="moduleDetails.php?course=<?= e($_GET['course']) ?>&id=<?= (int)$m['id'] ?>&ord=<?= $i ?>"
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
                                <?php $i++; endforeach; ?>
                        </div>
                    <?php endif; ?>
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
<?php
if (!$preview){
    require_once 'footer.php';
}
?>

</body>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // DOM hazır olduğunda ikonları çiz
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    });
</script>

</html>
