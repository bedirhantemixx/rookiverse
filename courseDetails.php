<?php
require_once "config.php";
session_start();
if(!isset($_GET['course'])){
    header("location: courses.php");
    exit();
}
$course = getCourseDetails($_GET['course'])
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs Detayı - RobotikTR</title>
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
        <div class="mb-6">
            <a href="courses.php" class="inline-flex items-center text-custom-yellow hover:bg-custom-yellow/10 p-2 rounded-md">
                <i data-lucide="arrow-left" class="mr-2" style="width: 18px; height: 18px;"></i>
                Kurslara Geri Dön
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="overflow-hidden border-2 border-custom-yellow/20 rounded-lg">

                    <div class="aspect-video relative bg-black">
                        <div id="video-thumbnail-container" class="absolute inset-0">
                            <img src="<?=$course['cover_image_url']?>" alt="Kurs tanıtım kapağı" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <button onclick="playIntroVideo()" class="inline-flex items-center justify-center bg-white text-custom-yellow hover:bg-gray-100 px-8 py-4 text-lg rounded-lg">
                                    <i data-lucide="film" class="mr-2" style="width: 24px; height: 24px;"></i>
                                    Kurs Tanıtımı
                                </button>
                            </div>
                        </div>
                        <video id="intro-video" class="w-full h-full hidden" controls>
                            <source  src="<?=$course['intro_video_url']?>" type="video/mp4">
                            Tarayıcınız video etiketini desteklemiyor.
                        </video>
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
                                <i data-lucide="clock" class="mr-1" style="width: 16px; height: 16px;"></i>
                                8 saat
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
                </div>

                <div class="border-2 hover:border-custom-yellow/50 transition-all duration-200 rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900">Kurs İçeriği</h2>
                        <?php
                        $modules = getModules($course['id']);
                        $count = count($modules);
                        ?>
                        <p class="text-gray-500"><?=$count?> modül • Toplam 8 saat</p>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="space-y-4">
                            <?php
                            $i = 0;
                            foreach ($modules as $module):
                                $i++
                            ?>
                                <div data-course="<?=$course['course_uid']?>" data-moduleid="<?=$module['id']?>" data-ord="<?=$i?>" class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors duration-200 modulePlayer">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center justify-center w-8 h-8 bg-custom-yellow/10 rounded-full flex-shrink-0">
                                            <span class="text-custom-yellow font-semibold text-sm"><?=$i?></span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?=$module['title']?></h4>
                                            <div class="flex items-center text-sm text-gray-500 mt-1"><i data-lucide="clock" class="mr-1" style="width: 14px; height: 14px;"></i>30 dakika</div>
                                        </div>
                                    </div>
                                    <button class="p-2 text-custom-yellow hover:bg-custom-yellow/10 rounded-full"><i data-lucide="play" style="width: 16px; height: 16px;"></i></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div id="enrollment-card" class="border-2 border-custom-yellow bg-custom-yellow/5 rounded-lg">
                    <div class="p-6 text-center space-y-4">
                        <div>

                            <p class="text-gray-600">Tam erişim ile</p>
                        </div>

                        <div id="enroll-button-container">
                            <button id="enroll-btn" onclick="handleEnroll()" class="w-full bg-custom-yellow hover:bg-opacity-90 text-white font-semibold py-3 rounded-md flex items-center justify-center transition-colors duration-200">
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

                        <div id="enrolled-container" class="hidden space-y-3">
                            <div class="flex items-center justify-center text-green-600 font-semibold">
                                <i data-lucide="check-circle" class="mr-2" style="width: 20px; height: 20px;"></i>
                                Kursa Kayıtlısınız
                            </div>
                            <button onclick="continueCourse()" class="w-full bg-custom-yellow hover:bg-opacity-90 text-white font-semibold py-3 rounded-md flex items-center justify-center">
                                <i data-lucide="play" class="mr-2" style="width: 18px; height: 18px;"></i>
                                Kursa Başla
                            </button>
                        </div>

                        <div class="text-sm text-gray-600 space-y-1">
                            <div>✓ Sınırsız erişim</div>
                            <div>✓ Tüm materyaller dahil</div>
                            <div>✓ Topluluk desteği</div>
                        </div>
                    </div>
                </div>

                <div class="border-2 border-custom-yellow/20 rounded-lg">
                    <div class="p-6 border-b border-custom-yellow/20"><h3 class="text-lg font-bold">Kurs İstatistikleri</h3></div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between"><span class="text-gray-600">Seviye:</span><span class="font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-800 bg-green-100 border-0 text-xs">Başlangıç</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Süre:</span><span class="font-medium">8 saat</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Öğrenci:</span><span class="font-medium"><?=$course['student']?></span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Dil:</span><span class="font-medium">Türkçe</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Sertifika:</span><span class="font-medium text-custom-yellow">Mevcut</span></div>
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
                            <img src="<?= $team['profile_pic_path']?>" alt="Takım Logosu" class="w-16 h-16 rounded-full object-cover">
                            <div>

                                <a id="team_panel_url" href="teampanel/6228.html" class="font-semibold text-gray-900 hover:text-custom-yellow transition-colors">
                                    <h4 id="team_name_with_number"><?=$team['team_name']?></h4>
                                </a>

                                <a href="#" class="text-sm text-gray-600 mt-2 block hover:text-gray-800 transition-colors">
                                    Bu takımın tüm kurslarını görmek için <span class="font-semibold text-custom-yellow underline">tıklayın</span>.
                                </a>

                                <div class="flex items-center space-x-3 mt-3">
                                    <?php if ($team['instagram']):?>
                                        <a id="team_instagram_url" href="<?=$team['instagram']?>" target="_blank" class="text-gray-500 hover:text-pink-600 transition-colors"><i data-lucide="instagram"></i></a>
                                    <?php elseif ($team['youtube']):?>
                                        <a id="team_youtube_url" href="<?=$team['youtube']?>" target="_blank" class="text-gray-500 hover:text-red-600 transition-colors"><i data-lucide="youtube"></i></a>
                                    <?php elseif ($team['linkedin']):?>
                                        <a id="team_linkedin_url" href="<?=$team['linkedin']?>" target="_blank" class="text-gray-500 hover:text-blue-700 transition-colors"><i data-lucide="linkedin"></i></a>
                                    <?php elseif ($team['website']):?>
                                        <a id="team_linkedin_url" href="<?=$team['website']?>" target="_blank" class="text-gray-500 hover:text-green-700 transition-colors"><i data-lucide="link"></i></a>
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
    enrollModalBackdrop.addEventListener('click', closeEnrollModal);
    // ESC ile kapat
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !enrollModal.classList.contains('hidden')) closeEnrollModal();
    });

    // Modal içindeki "Ücretsiz Kayıt Ol" butonu: mevcut handleEnroll'i çağırır
    modalEnrollBtn.addEventListener('click', async () => {
        await handleEnroll();
        closeEnrollModal();
    });

    // Vazgeç
    modalCancelBtn.addEventListener('click', closeEnrollModal);

    lucide.createIcons();

    let isEnrolled = false;


    let video = document.querySelector('#intro-video')




    const enrollButtonContainer = document.getElementById('enroll-button-container');
    const enrolledContainer = document.getElementById('enrolled-container');
    const thumbnailContainer = document.getElementById('video-thumbnail-container');

    // ## DEĞİŞİKLİK: TÜM VİDEO KONTROL İŞLEMLERİ BURADA ##
    const introVideo = document.getElementById('intro-video');
    const videoContainer = introVideo.parentElement; // Videonun etrafındaki relative container
    const skipBackwardIndicator = document.getElementById('skip-backward-indicator');
    const skipForwardIndicator = document.getElementById('skip-forward-indicator');
    let skipIndicatorTimeout;

    document.querySelectorAll('.modulePlayer').forEach(el => {
        el.addEventListener('click', (e) => {
            if (isEnrolled){
                let id = el.dataset.moduleid; // <div class="modulePlayer" data-moduleid="123">
                let course = el.dataset.course; // <div class="modulePlayer" data-moduleid="123">
                let i = el.dataset.ord; // <div class="modulePlayer" data-moduleid="123">
                i = i - 1;
                window.location.href = `moduleDetails.php?course=${course}&id=${id}&ord=${i}`;
            }
            else{
                openEnrollModal()
            }
        });
    });


    // İleri/geri sarma animasyonunu gösteren fonksiyon
    function showSkipIndicator(indicator) {
        clearTimeout(skipIndicatorTimeout);

        // Diğerini gizle, animasyonun sıfırlanması için
        (indicator === skipForwardIndicator ? skipBackwardIndicator : skipForwardIndicator).classList.remove('show');

        indicator.classList.remove('hidden');
        indicator.classList.add('show');

        lucide.createIcons(); // Animasyon içindeki ikonu tekrar render et

        skipIndicatorTimeout = setTimeout(() => {
            indicator.classList.remove('show');
            indicator.classList.add('hidden');
        }, 800); // Animasyon süresiyle eşleşmeli
    }

    // Klavye kısayollarını dinle
    document.addEventListener('keydown', (event) => {
        // Kısayollar sadece video görünür durumdayken çalışsın
        if (introVideo.classList.contains('hidden')) return;

        // Kullanıcı bir input alanına yazıyorsa kısayolları devre dışı bırak
        const activeEl = document.activeElement;
        if (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable) {
            return;
        }

        // Tuşlara göre işlem yap
        switch (event.key.toLowerCase()) {
            case ' ':
            case 'k':
                event.preventDefault(); // Boşluk tuşunun sayfayı kaydırmasını engelle
                introVideo.paused ? introVideo.play() : introVideo.pause();
                break;
            case 'f':
                if (!document.fullscreenElement) {
                    videoContainer.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
                break;
            case 'l':
                introVideo.currentTime += 5;
                showSkipIndicator(skipForwardIndicator);
                break;
            case 'j':
                introVideo.currentTime -= 5;
                showSkipIndicator(skipBackwardIndicator);
                break;
        }
    });
    // ## VİDEO KONTROL DEĞİŞİKLİKLERİNİN SONU ##


    function playIntroVideo() {
        thumbnailContainer.classList.add('hidden');
        introVideo.classList.remove('hidden');
        introVideo.play();
    }

    async function handleEnroll() {
        const enrollBtn = document.getElementById('enroll-btn');
        const enrollBtnContent = document.getElementById('enroll-btn-content');
        const enrollBtnLoading = document.getElementById('enroll-btn-loading');
        let course_id = <?=$course['id']?>;
        fetch('addStudent.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${course_id}`
        })
            .then(res => res.json())
            .then(data => {
            })
            .catch(err => {
                console.error("Sabitleme hatası", err);
            });

        enrollBtn.disabled = true;
        enrollBtn.classList.add('cursor-not-allowed');
        enrollBtnContent.classList.add('hidden');
        enrollBtnLoading.classList.remove('hidden');

        await new Promise(resolve => setTimeout(resolve, 1500));

        isEnrolled = true;

        enrollButtonContainer.classList.add('hidden');
        enrolledContainer.classList.remove('hidden');
    }



    function continueCourse() {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = res.json();
        console.log('Created:', data);

        const teamNameElement = document.getElementById('team_name_with_number');
        const teamId = "team" + teamNameElement.textContent.split('#')[1].trim();

        const courseNameElement = document.querySelector('h1');
        const courseName = courseNameElement.textContent.trim();

        function createCourseSlug(name) {
            return name.toLowerCase()
                .replace(/ /g, '-')
                .replace(/ı/g, 'i')
                .replace(/ğ/g, 'g')
                .replace(/ü/g, 'u')
                .replace(/ş/g, 's')
                .replace(/ö/g, 'o')
                .replace(/ç/g, 'c')
                .replace(/[^\w-]+/g, '');
        }

        const courseSlug = createCourseSlug(courseName);
        const finalURL = `${teamId}/${courseSlug}.html`;

        console.log("Yönlendiriliyor:", finalURL);
        window.location.href = finalURL;
    }
</script>

</body>
</html>
