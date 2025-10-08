<?php
require_once "config.php";
session_start();
if(!isset($_GET['course'])){
    header("location: course_actions.php");
    exit();
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


$course = getCourseDetails($_GET['course']);

if ($course['status'] != 'approved'){
    header("location: courses.php");
}

$anonId = $_COOKIE['rv_anon'] ?? null;
$isEnrolled = false;
$pdo = get_db_connection();
if ($anonId) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM course_guest_enrollments 
        WHERE course_id = ? AND anon_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$course['id'], $anonId]);
    $isEnrolled = (bool)$stmt->fetchColumn();
}


?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs Detayı - <?=$course['title']?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">


    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-yellow': '#E5AE32',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
        }
        @font-face {
            font-family: "Sakana";
            src: url("/Sakana.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        .rookieverse {
            font-family: "Sakana", system-ui, sans-serif !important;
            font-weight: bold;
            font-size: 1.25rem;
            color: #E5AE32;
            line-height: 1;
            display: inline-block
        }
        *, ::before, ::after {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: #e5e7eb
        }
        .prose ul {
            list-style-position: inside;
            list-style-type: disc;
        }
        .prose ul li {
            margin-left: 1rem;
        }
        .hidden {
            display: none;
        }
        /* ## YENİ EKLENEN CSS ## */
        .skip-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }
        .skip-indicator.show {
            animation: skip-animation 0.8s ease-out forwards;
        }
        @keyframes skip-animation {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            20% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            80% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(1);
            }
        }
    </style>
</head>
<body class="bg-white">
<?php
require_once 'navbar.php';
?>

<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!$preview):?>
        <div class="mb-6">
            <a href="courses.php" class="inline-flex items-center text-custom-yellow hover:bg-custom-yellow/10 p-2 rounded-md">
                <i data-lucide="arrow-left" class="mr-2" style="width: 18px; height: 18px;"></i>
                Kurslara Geri Dön
            </a>
        </div>
        <?php endif;?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <?php if ($preview): ?>
                <!-- ÖNİZLEME HAVA PANELİ (SAĞ ÜST) -->
                <div
                        class="fixed top-16 left-3 z-50 w-[320px] max-w-[90vw] border-2 border-custom-yellow/30 rounded-xl p-4 bg-white shadow-xl"
                        role="region" aria-label="Önizleme Kontrolleri"
                >
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="shield-check"></i>
                        <h3 class="font-bold text-gray-900">Önizleme Modu</h3>
                    </div>

                    <p class="text-sm text-gray-700 mb-4">
                        Bu sayfa <span class="font-semibold text-custom-yellow">önizleme</span> modunda.
                    </p>

                    <div class="space-y-2">
                        <a href="admin/course_actions.php"
                           class="w-full inline-flex items-center justify-center rounded-md border-2 border-custom-yellow/40 px-4 py-2 font-semibold text-custom-yellow hover:bg-custom-yellow/10">
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
                            <input type="hidden" name="action" value="approve_course">
                            <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">
                                <i data-lucide="check-circle" class="mr-2" style="width:18px;height:18px;"></i>
                                Onayla
                            </button>
                        </form>

                        <!-- REDDET (POST) -->
                        <form method="post" action="admin/course_actions.php" class="space-y-0 mt-2">
                            <input type="hidden" name="csrf" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="reject_course">
                            <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700">
                                <i data-lucide="x-circle" class="mr-2" style="width:18px;height:18px;"></i>
                                Reddet
                            </button>
                        </form>

                    </div>
                </div>
            <?php endif; ?>


            <div class="lg:col-span-2 space-y-8">
                <div class="overflow-hidden border-2 border-custom-yellow/20 rounded-lg">

                    <div class="aspect-video relative bg-black">
                        <div id="video-thumbnail-container" class="absolute inset-0">
                            <img src="<?=$course['cover_image_url']?>" alt="Kurs tanıtım kapağı" class="w-full h-full object-cover">
                            <?php
                            if ($course['intro_video_url'] != ''):

                            ?>
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <button onclick="playIntroVideo()" class="inline-flex items-center justify-center bg-white text-custom-yellow hover:bg-gray-100 px-8 py-4 text-lg rounded-lg">
                                        <i data-lucide="film" class="mr-2" style="width: 24px; height: 24px;"></i>
                                        Kurs Tanıtımı
                                    </button>
                                </div>
                            <?php
                            endif;
                            ?>
                        </div>
                        <?php
                        if ($course['intro_video_url'] != ''):
                            if (str_contains($course['intro_video_url'], 'https://')):
                                $videoUrl = $course['intro_video_url'] ?? '';
                                $youtubeId = '';

// Farklı YouTube URL formatlarını destekle
                                if (preg_match('/^[A-Za-z0-9_-]{10,20}$/', $videoUrl)) {
                                    // Zaten sadece ID
                                    $youtubeId = $videoUrl;
                                } elseif (preg_match('/youtu\.be\/([A-Za-z0-9_-]+)/', $videoUrl, $m)) {
                                    // https://youtu.be/VIDEO_ID
                                    $youtubeId = $m[1];
                                } elseif (preg_match('/youtube\.com\/watch\?v=([A-Za-z0-9_-]+)/', $videoUrl, $m)) {
                                    // https://www.youtube.com/watch?v=VIDEO_ID
                                    $youtubeId = $m[1];
                                } elseif (preg_match('/youtube\.com\/embed\/([A-Za-z0-9_-]+)/', $videoUrl, $m)) {
                                    // https://www.youtube.com/embed/VIDEO_ID
                                    $youtubeId = $m[1];
                                } elseif (preg_match('/youtube\.com\/shorts\/([A-Za-z0-9_-]+)/', $videoUrl, $m)) {
                                    // https://www.youtube.com/shorts/VIDEO_ID
                                    $youtubeId = $m[1];
                                }
                                ?>

                                <?php if ($youtubeId): ?>
                                <!-- YouTube iframe -->
                                <iframe
                                        id="intro-video"
                                        class="w-full h-full hidden"
                                        src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId, ENT_QUOTES, 'UTF-8') ?>"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen>
                                </iframe>
                            <?php else: ?>
                                <!-- Normal video (MP4 vs.) -->
                                <video id="intro-video" class="w-full h-full hidden" controls>
                                    <source src="<?= htmlspecialchars($course['intro_video_url'], ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
                                    Tarayıcınız video etiketini desteklemiyor.
                                </video>
                            <?php endif; ?>
                        <?php
                            else:
                        ?>
                            <video id="intro-video" class="w-full h-full hidden" controls>
                                <source  src="<?=$course['intro_video_url']?>" type="video/mp4">
                                Tarayıcınız video etiketini desteklemiyor.
                            </video>
                        <?php
                            endif;

                        endif;
                        ?>
                        <div id="skip-backward-indicator" class="skip-indicator hidden">
                            <i data-lucide="rewind" class="w-8 h-8"></i>
                        </div>
                        <div id="skip-forward-indicator" class="skip-indicator hidden">
                            <i data-lucide="fast-forward" class="w-8 h-8"></i>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <?php
                            if ($course['level'] == 'Başlangıç'):
                                ?>
                                <span
                                        class="text-sm font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-800 bg-green-100 border-0 whitespace-nowrap">Başlangıç</span>

                            <?php
                            elseif ($course['level'] == 'Orta'):
                                ?>
                                <span
                                        class="text-sm font-semibold inline-block py-1 px-2 uppercase rounded-full text-yellow-800 bg-yellow-100 border-0 whitespace-nowrap">Orta</span>
                            <?php
                            elseif ($course['level'] == 'İleri'):
                                ?>
                                <span
                                        class="text-sm font-semibold inline-block py-1 px-2 uppercase rounded-full text-red-800 bg-red-100 border-0 whitespace-nowrap">İleri</span>
                            <?php endif;?>                            <div class="flex items-center text-sm text-gray-600">

                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i data-lucide="users" class="mr-1" style="width: 16px; height: 16px;"></i>
                                <?=$course['student']?> öğrenci
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">
                            <?=$course['title']?>
                        </h1>
                        <p class="text-lg text-gray-700">
                            <?=$course['goal_text']?>
                        </p>
                    </div>
                </div>

                <div class="border-2 hover:border-custom-yellow/50 transition-all duration-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Kurs Hakkında</h2>
                    <div class="prose max-w-none text-gray-700">
                        <p class="text-lg leading-relaxed mb-4">
                            <?=$course['about_text']?>

                        </p>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Kurs Amacı</h2>
                    <div class="prose max-w-none text-gray-700">
                        <p class="text-lg leading-relaxed mb-4">
                            <?=$course['goal_text']?>

                        </p>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Ne Öğreneceksiniz?</h2>
                    <div class="prose max-w-none text-gray-700">
                        <p class="text-lg leading-relaxed mb-4">
                            <?=$course['learnings_text']?>

                        </p>
                    </div>
                </div>

                <div class="border-2 hover:border-custom-yellow/50 transition-all duration-200 rounded-lg">


                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900">Kurs İçeriği</h2>
                        <?php
                            if (!$preview):
                            $modules = getModules($course['id']);
                            $count = is_array($modules) ? count($modules) : 0;

                            ?>

                            <p class="text-gray-500"><?=$count?> modül</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($preview): ?>
                        <!-- ÖNİZLEMEDE LİSTE YOK, UYARI + BUTON -->
                        <div class="p-6 pt-0">
                            <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="eye-off" class="mt-0.5" style="width:20px;height:20px;"></i>
                                    <div>
                                        <p class="text-sm text-yellow-800">
                                            Modülleri önizlemek için <span class="font-semibold">panele geri dön</span> ve
                                            oradan modül detayına gir.
                                        </p>
                                        <a href="admin/course_actions.php"
                                           class="mt-3 inline-flex items-center rounded-md border-2 border-yellow-300 px-3 py-2 text-sm font-semibold text-yellow-800 hover:bg-yellow-100">
                                            <i data-lucide="arrow-left" class="mr-2" style="width:16px;height:16px;"></i>
                                            Panele Geri Dön
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- NORMALDE MODÜL LİSTESİ -->
                        <div class="p-6 pt-0">
                            <div class="space-y-4">
                                <?php
                                $i = 0;
                                foreach ($modules as $module):
                                    $i++;
                                    ?>
                                    <div data-course="<?=$course['course_uid']?>"
                                         data-moduleid="<?=$module['id']?>"
                                         data-ord="<?=$i?>"
                                         class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors duration-200 modulePlayer">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center justify-center w-8 h-8 bg-custom-yellow/10 rounded-full flex-shrink-0">
                                                <span class="text-custom-yellow font-semibold text-sm"><?=$i?></span>
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900"><?=$module['title']?></h4>
                                                <div class="flex items-center text-sm text-gray-500 mt-1">
                                                    <i data-lucide="clock" class="mr-1" style="width:14px;height:14px;"></i>
                                                    30 dakika
                                                </div>
                                            </div>
                                        </div>
                                        <button class="p-2 text-custom-yellow hover:bg-custom-yellow/10 rounded-full">
                                            <i data-lucide="play" style="width:16px;height:16px;"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
                <!-- Q&A / SORU SOR -->
                <div class="border-2 border-custom-yellow/20 rounded-lg" id="qa-card">
                    <div class="p-6 border-b border-custom-yellow/20">
                        <h3 class="text-lg font-bold">Soru & Yorumlar</h3>
                        <p class="text-sm text-gray-600">Dersle ilgili aklına takılanı sor.</p>
                    </div>

                    <div class="p-6">
                        <!-- Form -->
                        <span id="character-limit" style="color: red; margin-left: 5px; display: none">Sorunuz 250 karakteri aşmamalı.</span>
                        <form id="ask-form" class="space-y-3">
      <textarea id="ask-body" rows="3" class="w-full border rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-custom-yellow"
                placeholder="Sorunuzu yazın..."></textarea>

                            <div  class="flex items-center gap-3">
                                <button id="ask-submit" <?= $isEnrolled ? '' : 'disabled'?> style="<?= $isEnrolled ? '' : 'background-color: Gray; color: white'?>" type="submit" class="bg-custom-yellow text-white font-semibold px-4 py-2 rounded-md hover:bg-opacity-90">
                                    Gönder
                                </button>
                                <span id="ask-hint" class="text-sm text-gray-500">
          <?php if(!$isEnrolled): ?>
              <span style="font-weight: 900">Soru sormak için önce kursa kaydolun.</span>
          <?php else: ?>
              Nazik ve anlaşılır yazmanız yanıt şansını artırır, yorumunuz takım tarafından onaylanana kadar gizli kalacak.
          <?php endif; ?>
        </span>
                            </div>
                        </form>

                        <!-- Liste -->
                        <div id="questions-list" class="mt-6 space-y-4">

                        </div>

                        <!-- Yükleniyor -->
                        <div id="qa-loading" class="hidden text-sm text-gray-500 mt-4">Yükleniyor...</div>
                    </div>
                </div>

            </div>






            <div class="space-y-6">
                <div class="border-2 border-custom-yellow/20 rounded-lg p-6" id="reactions-card">
                    <h3 class="text-lg font-bold mb-4">Bu kurs faydalı mı?</h3>
                    <div class="flex items-center gap-3">
                        <button id="btn-like" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border hover:bg-green-50">
                            <i data-lucide="thumbs-up"></i>
                            <span>Beğen</span>
                            <span id="like-count" class="ml-1 text-sm text-gray-600">0</span>
                        </button>

                        <button id="btn-dislike" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border hover:bg-red-50">
                            <i data-lucide="thumbs-down"></i>
                            <span>Beğenmedim</span>
                            <span id="dislike-count" class="ml-1 text-sm text-gray-600">0</span>
                        </button>

                        <span id="your-reaction" class="ml-3 text-sm text-gray-500"></span>
                    </div>
                </div>
                <?php if (!$isEnrolled): ?>
                    <div id="enrollment-card" class="border-2 border-custom-yellow bg-custom-yellow/5 rounded-lg">
                        <div class="p-6 text-center space-y-4">
                            <div>
                                <p class="text-gray-600">Tam erişim ile</p>
                            </div>

                            <div id="enroll-button-container">
                                <button id="enroll-btn" class="w-full bg-custom-yellow hover:bg-opacity-90 text-white font-semibold py-3 rounded-md flex items-center justify-center transition-colors duration-200">
                <span id="enroll-btn-content" class="flex items-center justify-center">
                    <i data-lucide="book-open" class="mr-2" style="width: 18px; height: 18px;"></i>
                    Ücretsiz Kayıt Ol
                </span>
                                    <span id="enroll-btn-loading" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                                </button>
                            </div>
                            <div id="enrolled-container" class="hidden space-y-3"> <div class="flex items-center justify-center text-green-600 font-semibold"> <i data-lucide="check-circle" class="mr-2" style="width: 20px; height: 20px;"></i> Kursa Kayıtlısınız </div> <button onclick="continueCourse()" class="w-full bg-custom-yellow hover:bg-opacity-90 text-white font-semibold py-3 rounded-md flex items-center justify-center"> <i data-lucide="play" class="mr-2" style="width: 18px; height: 18px;"></i> Kursa Başla </button> </div>

                            <div class="text-sm text-gray-600 space-y-1">
                                <div>✓ Sınırsız erişim</div>
                                <div>✓ Tüm materyaller dahil</div>
                                <div>✓ Topluluk desteği</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


                <div class="border-2 border-custom-yellow/20 rounded-lg">
                    <div class="p-6 border-b border-custom-yellow/20"><h3 class="text-lg font-bold">Kurs İstatistikleri</h3></div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between"><span class="text-gray-600">Seviye:</span><span class="font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-800 bg-green-100 border-0 text-xs">Başlangıç</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Öğrenci:</span><span class="font-medium"><?=$course['student']?></span></div>
                            </div>
                    </div>
                </div>

                <div class="border-2 border-custom-yellow/20 rounded-lg">
                    <div class="p-6 border-b border-custom-yellow/20"><h3 class="text-lg font-bold">Eğitim Veren Takım</h3></div>
                    <div class="p-6">
                        <?php
                        $team = getTeam($course['team_db_id'])
                        ?>
                        <div class="flex items-start space-x-4">
                            <?php
                            if (!empty($team['profile_pic_path'])):
                            ?>
                                <img src="<?= $team['profile_pic_path']?>" alt="Takım Logosu" class="w-16 h-16 rounded-full object-cover">
                            <?php endif;?>
                            <div>

                                <a id="team_panel_url" href="teamCourses.php?team_number=<?=$team['team_number']?>" class="font-semibold text-gray-900 hover:text-custom-yellow transition-colors">
                                    <h4 id="team_name_with_number"><?=$team['team_name']?></h4>
                                </a>

                                <a href="teamCourses.php?team_number=<?=$team['team_number']?>" class="text-sm text-gray-600 mt-2 block hover:text-gray-800 transition-colors">
                                    Bu takımın tüm kurslarını görmek için <span class="font-semibold text-custom-yellow underline">tıklayın</span>.
                                </a>

                                <div class="flex items-center space-x-3 mt-3">
                                    <?php if ($team['website']):?>
                                        <a id="team_linkedin_url" href="<?=$team['website']?>" target="_blank" class="text-gray-500 hover:text-green-700 transition-colors"><i data-lucide="globe"></i></a>
                                    <?php endif;?>

                                    <?php if ($team['linkedin']):?>
                                        <a id="team_linkedin_url" href="<?=$team['linkedin']?>" target="_blank" class="text-gray-500 hover:text-blue-700 transition-colors"><i data-lucide="linkedin"></i></a>
                                    <?php endif;?>

                                    <?php if ($team['instagram']):?>
                                        <a id="team_instagram_url" href="<?=$team['instagram']?>" target="_blank" class="text-gray-500 hover:text-pink-600 transition-colors"><i data-lucide="instagram"></i></a>
                                    <?php endif;?>

                                    <?php if ($team['youtube']):?>
                                        <a id="team_youtube_url" href="<?=$team['youtube']?>" target="_blank" class="text-gray-500 hover:text-red-600 transition-colors"><i data-lucide="youtube"></i></a>
                                    <?php endif;?>





                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- KAYIT GEREKİYOR MODALI -->
<div id="enroll-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <!-- arkaplan -->
    <div id="enroll-modal-backdrop" class="absolute inset-0 bg-black/40"></div>

    <!-- modal kutu -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl border-2 border-custom-yellow/30 bg-white shadow-xl">
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <div class="mt-1 inline-flex h-10 w-10 items-center justify-center rounded-full bg-custom-yellow/10">
                        <i data-lucide="lock" class="text-custom-yellow" style="width:20px;height:20px;"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Önce ücretsiz kayıt ol</h3>
                        <p class="mt-1 text-gray-600">
                            Bu modülü görmek için önce kursa <span class="font-semibold text-custom-yellow">ücretsiz kayıt</span> olman gerekiyor.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button id="modal-enroll-btn"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-custom-yellow px-4 py-2 font-semibold text-white hover:bg-opacity-90">
                        <i data-lucide="book-open" class="mr-2" style="width:18px;height:18px;"></i>
                        Ücretsiz Kayıt Ol
                    </button>
                    <button id="modal-cancel-btn"
                            class="flex-1 inline-flex items-center justify-center rounded-md border-2 border-gray-200 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50">
                        Vazgeç
                    </button>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    Kayıttan sonra tüm modüllere sınırsız erişim sağlanır.
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'?>


<script>
    // --- Modal referansları ---
    const enrollModal = document.getElementById('enroll-modal');
    const enrollModalBackdrop = document.getElementById('enroll-modal-backdrop');
    const modalEnrollBtn = document.getElementById('modal-enroll-btn');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');

    function openEnrollModal() {
        enrollModal.classList.remove('hidden');
        enrollModal.setAttribute('aria-hidden', 'false');
        lucide.createIcons();
    }
    function closeEnrollModal() {
        enrollModal.classList.add('hidden');
        enrollModal.setAttribute('aria-hidden', 'true');
    }

    // Dışına tıklayınca kapat
    enrollModalBackdrop?.addEventListener('click', closeEnrollModal);
    // ESC ile kapat
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !enrollModal.classList.contains('hidden')) closeEnrollModal();
    });

    // Modal içindeki "Ücretsiz Kayıt Ol"
    modalEnrollBtn?.addEventListener('click', async () => {
        await handleEnroll();
        closeEnrollModal();
    });
    // Vazgeç
    modalCancelBtn?.addEventListener('click', closeEnrollModal);

    lucide.createIcons();

    let isEnrolled = <?php echo $isEnrolled ? 'true' : 'false'; ?>;

    // İlk modüle git
    function continueCourse() {
        if (!isEnrolled) {
            openEnrollModal();
            return;
        }
        const firstModule = document.querySelector('.modulePlayer');
        if (!firstModule) return;

        const moduleId = firstModule.dataset.moduleid;
        const courseUid = firstModule.dataset.course || '<?= htmlspecialchars($_GET['course'] ?? '', ENT_QUOTES) ?>';
        const ordZero = Math.max(0, (Number(firstModule.dataset.ord) || 1) - 1);

        window.location.href = `moduleDetails.php?course=${encodeURIComponent(courseUid)}&id=${encodeURIComponent(moduleId)}&ord=${encodeURIComponent(ordZero)}`;
    }

    // --- Video / tanıtım kısmı: null-safe ---
    const thumbnailContainer = document.getElementById('video-thumbnail-container');
    const introVideo = document.getElementById('intro-video'); // yoksa null
    const videoContainer = introVideo?.parentElement || document.querySelector('.aspect-video'); // fallback
    const skipBackwardIndicator = document.getElementById('skip-backward-indicator');
    const skipForwardIndicator  = document.getElementById('skip-forward-indicator');
    let skipIndicatorTimeout;

    function playIntroVideo() {
        if (!introVideo) return; // video yoksa sessizce çık
        thumbnailContainer?.classList.add('hidden');
        introVideo.classList.remove('hidden');
        introVideo.play().catch(()=>{});
    }

    function showSkipIndicator(indicator) {
        clearTimeout(skipIndicatorTimeout);
        (indicator === skipForwardIndicator ? skipBackwardIndicator : skipForwardIndicator)?.classList.remove('show');

        indicator?.classList.remove('hidden');
        indicator?.classList.add('show');
        lucide.createIcons();
        skipIndicatorTimeout = setTimeout(() => {
            indicator?.classList.remove('show');
            indicator?.classList.add('hidden');
        }, 800);
    }

    // Klavye kısayolları (yalnızca video varken)
    document.addEventListener('keydown', (event) => {
        if (!introVideo || introVideo.classList.contains('hidden')) return;

        const activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable)) return;

        switch (event.key.toLowerCase()) {
            case ' ':
            case 'k':
                event.preventDefault();
                introVideo.paused ? introVideo.play() : introVideo.pause();
                break;
            case 'f':
                if (!document.fullscreenElement) {
                    videoContainer?.requestFullscreen?.();
                } else {
                    document.exitFullscreen?.();
                }
                break;
            case 'l':
                introVideo.currentTime = Math.min(introVideo.duration || Infinity, introVideo.currentTime + 5);
                showSkipIndicator(skipForwardIndicator);
                break;
            case 'j':
                introVideo.currentTime = Math.max(0, introVideo.currentTime - 5);
                showSkipIndicator(skipBackwardIndicator);
                break;
        }
    });

    // Modül tıklama — KAYNAK SORUN: JS hata alırsa burası hiç çalışmıyordu.
    document.querySelectorAll('.modulePlayer').forEach(el => {
        el.addEventListener('click', () => {
            if (isEnrolled) {
                const id = el.dataset.moduleid;
                const course = el.dataset.course || '<?= htmlspecialchars($_GET['course'] ?? '', ENT_QUOTES) ?>';
                const ordZero = Math.max(0, (Number(el.dataset.ord) || 1) - 1);
                window.location.href = `moduleDetails.php?course=${encodeURIComponent(course)}&id=${encodeURIComponent(id)}&ord=${encodeURIComponent(ordZero)}`;
            } else {
                openEnrollModal();
            }
        });
    });

    // Enroll butonu null olabilir (kayıtlı kullanıcıda kart gösterilmiyor) → guard
    const enrollBtn = document.getElementById('enroll-btn');
    enrollBtn?.addEventListener('click', handleEnroll);

    async function handleEnroll() {
        // Q&A butonunu aktifleştir
        const askSubmitEl = document.getElementById('ask-submit');
        const askHintEl   = document.getElementById('ask-hint');
        if (askSubmitEl) {
            askSubmitEl.disabled = false;
            askSubmitEl.style.backgroundColor = '';
            askSubmitEl.style.color = '';
        }
        if (askHintEl) {
            askHintEl.textContent = 'Nazik ve anlaşılır yazmanız yanıt şansını artırır, yorumunuz takım tarafından onaylanana kadar gizli kalacak.';
        }

        const enrollBtnContent  = document.getElementById('enroll-btn-content');
        const enrollBtnLoading  = document.getElementById('enroll-btn-loading');
        const enrollButtonBox   = document.getElementById('enroll-button-container');
        const enrolledBox       = document.getElementById('enrolled-container');

        const course_id = <?= (int)$course['id'] ?>;
        try {
            // backend’e kayıt isteği
            await fetch('addStudent.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${encodeURIComponent(course_id)}`
            });
        } catch(e) {
            console.error('Kayıt hatası', e);
        }

        if (enrollBtn) {
            enrollBtn.disabled = true;
            enrollBtn.classList.add('cursor-not-allowed');
        }
        enrollBtnContent?.classList.add('hidden');
        enrollBtnLoading?.classList.remove('hidden');

        await new Promise(r => setTimeout(r, 800));

        isEnrolled = true;
        enrollButtonBox?.classList.add('hidden');
        enrolledBox?.classList.remove('hidden');
    }

    // ==== REACTIONS ====
    const COURSE_ID = <?= (int)$course['id'] ?>;
    const likeBtn = document.getElementById('btn-like');
    const dislikeBtn = document.getElementById('btn-dislike');
    const likeCountEl = document.getElementById('like-count');
    const dislikeCountEl = document.getElementById('dislike-count');
    const yourReactionEl = document.getElementById('your-reaction');

    async function fetchReactions() {
        try {
            const res = await fetch('reactions_api.php?action=get&course_id=' + COURSE_ID);
            const data = await res.json();
            likeCountEl.textContent = data.likes ?? 0;
            dislikeCountEl.textContent = data.dislikes ?? 0;

            likeBtn.classList.remove('bg-green-50','border-green-300');
            dislikeBtn.classList.remove('bg-red-50','border-red-300');

            if (data.your_reaction === 'like') {
                likeBtn.classList.add('bg-green-50','border-green-300');
            } else if (data.your_reaction === 'dislike') {
                dislikeBtn.classList.add('bg-red-50','border-red-300');
            }
        } catch(e) { console.error(e); }
    }

    async function sendReaction(type) {
        try {
            const res = await fetch('reactions_api.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'toggle', course_id: COURSE_ID, reaction: type })
            });
            const data = await res.json();
            if (data.ok) fetchReactions();
        } catch(e) { console.error(e); }
    }

    likeBtn?.addEventListener('click', () => sendReaction('like'));
    dislikeBtn?.addEventListener('click', () => sendReaction('dislike'));

    // ==== Q&A ====
    const askForm = document.getElementById('ask-form');
    const askBody = document.getElementById('ask-body');
    const questionsList = document.getElementById('questions-list');
    const qaLoading = document.getElementById('qa-loading');

    function escapeHtml(str) {
        return (String(str || '')).replace(/[&<>"']/g, m => (
            { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]
        ));
    }

    function renderReplies(container, replies) {
        container.innerHTML = '';
        if (!Array.isArray(replies) || replies.length === 0) return;
        const header = document.createElement('div');
        header.className = 'text-sm text-gray-600 font-semibold mt-3';
        header.textContent = `Yanıtlar (${replies.length})`;
        container.appendChild(header);
        replies.forEach(r => {
            const row = document.createElement('div');
            row.className = 'p-3 rounded-md border bg-gray-50';
            row.innerHTML = `
        <div class="text-xs text-gray-500 mb-1">
          ${escapeHtml(r.created_at || '')} • ${escapeHtml(r.responder_name || 'Eğitmen')}
        </div>
        <div class="whitespace-pre-line text-gray-800">${escapeHtml(r.body || '')}</div>
      `;
            container.appendChild(row);
        });
    }

    async function fetchReplies(questionId, container) {
        try {
            const res = await fetch('questions_api.php?action=replies&question_id=' + encodeURIComponent(questionId));
            if (!res.ok) return;
            const data = await res.json();
            renderReplies(container, data.items || []);
        } catch (_e) {}
    }

    async function loadQuestions() {
        qaLoading.classList.remove('hidden');
        const res = await fetch('questions_api.php?action=list&with_replies=1&course_id=' + COURSE_ID);
        const data = await res.json();
        qaLoading.classList.add('hidden');
        questionsList.innerHTML = '';

        const items = data.items || [];
        if (items.length === 0) {
            questionsList.innerHTML = `<div class="text-sm text-gray-500">Henüz soru yok. İlk soruyu sen sor!</div>`;
            return;
        }

        items.forEach(q => {
            const item = document.createElement('div');
            item.className = 'p-4 border rounded-lg';
            item.innerHTML = `
        <div class="text-sm text-gray-500 mb-1">${escapeHtml(q.created_at)}</div>
        <div class="whitespace-pre-line text-gray-800">${escapeHtml(q.body)}</div>
      `;
            const repliesWrap = document.createElement('div');
            repliesWrap.className = 'mt-3 space-y-3';
            repliesWrap.id = 'q-replies-' + q.id;
            item.appendChild(repliesWrap);

            if (Array.isArray(q.replies)) {
                renderReplies(repliesWrap, q.replies);
            } else {
                fetchReplies(q.id, repliesWrap);
            }
            questionsList.appendChild(item);
        });
    }

    function removeAlertText(){
        const el = document.querySelector('#character-limit');
        if (el) el.style.display = 'none';
    }

    askForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = (askBody?.value || '').trim();
        if (!body) return;
        if (body.length > 250){
            const el = document.querySelector('#character-limit');
            if (el) {
                el.style.display = 'flex';
                setTimeout(removeAlertText, 3000);
            }
            return;
        }
        const res = await fetch('questions_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ action: 'add', course_id: COURSE_ID, body })
        });
        const data = await res.json();
        if (data.ok) {
            if (askBody) askBody.value = '';
            loadQuestions();
        } else {
            alert(data.error || 'Gönderilemedi.');
        }
    });

    // İlk yükleme
    fetchReactions();
    loadQuestions();
</script>



</body>
</html>
