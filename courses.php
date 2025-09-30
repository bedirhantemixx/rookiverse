<!DOCTYPE html>
<html lang="tr">
<?php require_once 'config.php';
session_start();
$courses = getCourses();
$categories = getApprovedCategories();
$approvedCourses = 0;
foreach ($courses as $course) {
    if ($course['status'] == 'approved') {
        $approvedCourses++;
    }
}


?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kurslar - RookieVerse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">

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
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
        }

        @font-face {
            font-family: "Sakana";
            src: url("/Sakana.ttf") format("truetype");
        }

        .rookieverse {
            font-family: "Sakana", system-ui, sans-serif !important;
            font-weight: bold;
            font-size: 1.25rem;
            color: #E5AE32;
        }
    </style>
</head>
<body class="bg-white">

<!--navbar-->
<?php
    require_once("navbar.php");
?>

<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Kurslar</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                FRC'de ihtiyacınız olan tüm bilgileri kategorilere ayrılmış kapsamlı
                kurslarımızda bulabilirsiniz.
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-4 mb-12">
            <button data-filter="all" class="px-6 py-2 font-medium bg-custom-yellow text-white rounded-md">Tümü</button>
            <?php
            foreach ($categories as $category):
            ?>
                <button data-filter="<?=$category['id']?>"
                        class="px-6 py-2 font-medium border border-custom-yellow text-custom-yellow hover:bg-custom-yellow hover:text-white rounded-md"><?=$category['name']?>
                </button>
            <?php endforeach;?>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-2 mb-8 px-4">
            <input type="text" placeholder="Kurslarda ara..."
                   class="w-full sm:w-auto flex-1 max-w-lg px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-yellow" />
            <button
                class="inline-flex items-center justify-center px-6 py-2 bg-custom-yellow text-white font-semibold rounded-full shadow hover:bg-custom-yellow/90 transition">
                <i data-lucide="search" class="mr-2 size-5"></i> Ara
            </button>
        </div>

        <!-- Mevcut Metrikler -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="text-center border-2 border-custom-yellow/20 p-6 rounded-lg">
                <h3 class="text-3xl font-bold text-custom-yellow"><?=$approvedCourses?></h3>
                <p class="text-lg text-gray-500">Toplam Kurs</p>
            </div>
            <div class="text-center border-2 border-custom-yellow/20 p-6 rounded-lg">
                <h3 class="text-3xl font-bold text-custom-yellow"><?=count($categories)?></h3>
                <p class="text-lg text-gray-500">Kategori</p>
            </div>
            <div class="text-center border-2 border-custom-yellow/20 p-6 rounded-lg">
                <?php
                $studentcount = getStudents();
                ?>
                <h3 class="text-3xl font-bold text-custom-yellow"><?=$studentcount?></h3>
                <p class="text-lg text-gray-500">Toplam Öğrenci</p>
            </div>
        </div>

        <!-- KURS KARTLARI -->
        <div class="space-y-12">

            <?php
            foreach ($categories as $category):
            ?>
            <div data-category="<?=$category['id']?>">
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-8 flex items-center">
                    <div class="w-1 h-8 bg-custom-yellow mr-4 rounded-full"> <?= $category['name']?></div>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    foreach ($courses as $course):
                        if ($course['category_id'] != $category['id']):
                            continue;
                        elseif ($course['status'] != "approved"):
                            continue;
                        else:
                    ?>
                        <div class="overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-2 hover:border-custom-yellow/50 rounded-lg">
                            <div class="aspect-video relative overflow-hidden group">
                                <img src="<?=$course['cover_image_url']?>" alt="<?=$course['title']?>"
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                <div
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="courseDetails.php?course=<?=$course['course_uid']?>"
                                       class="inline-flex items-center justify-center px-4 py-2 bg-white text-custom-yellow hover:bg-gray-100 rounded-md">
                                        <i data-lucide="play" class="mr-2" style="width: 16px; height: 16px;"></i> Kurs Tanıtımı
                                    </a>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-xl font-bold text-gray-900 line-clamp-2"><?=$course['title']?></h3>
                                    <?php
                                    if ($course['level'] == 'Başlangıç'):
                                        ?>
                                        <span
                                                class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-800 bg-green-100 border-0 whitespace-nowrap">Başlangıç</span>

                                    <?php
                                    elseif ($course['level'] == 'Orta'):
                                        ?>
                                        <span
                                                class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-yellow-800 bg-yellow-100 border-0 whitespace-nowrap">Orta</span>
                                    <?php
                                    elseif ($course['level'] == 'İleri'):
                                        ?>
                                        <span
                                                class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-red-800 bg-red-100 border-0 whitespace-nowrap">İleri</span>
                                    <?php endif;?>
                                    </div>
                                <p class="text-gray-600 line-clamp-3 mb-4"><?=$course['goal_text']?></p>
                                <div class="flex items-center justify-between mb-4">


                                        <div class="flex items-center text-sm text-gray-500"><i data-lucide="bar-chart-3" class="mr-1"
                                                                                                style="width: 16px; height: 16px;"></i> <?= $course['level']?></div>

                                </div>
                                <a href="courseDetails.php?course=<?=$course['course_uid']?>"
                                   class="w-full inline-block text-center bg-custom-yellow hover:bg-opacity-90 text-white font-semibold py-2 transition-all duration-200 rounded-md">Kursa
                                    Başla</a>
                            </div>
                        </div>
                        <?php endif;?>
                    <?php endforeach;?>

                </div>
            </div>
            <?php endforeach;?>





        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>


<script>
    lucide.createIcons();
    const buttons = document.querySelectorAll('button[data-filter]');
    const sections = document.querySelectorAll('div[data-category]');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.getAttribute('data-filter');

            if (filter === 'all') {
                sections.forEach(section => section.classList.remove('hidden'));
            } else {
                sections.forEach(section => {
                    if (section.getAttribute('data-category') === filter) {
                        section.classList.remove('hidden');
                    } else {
                        section.classList.add('hidden');
                    }
                });
            }

            buttons.forEach(btn => {
                btn.classList.remove('bg-custom-yellow', 'text-white');
                btn.classList.add('border', 'border-custom-yellow', 'text-custom-yellow');
            });
            button.classList.add('bg-custom-yellow', 'text-white');
            button.classList.remove('border', 'text-custom-yellow');
        });
    });

    // Arama input ve kurs kartlarını seç
    const searchInput = document.querySelector('input[type="text"][placeholder="Kurslarda ara..."]');
    const allSections = document.querySelectorAll('div[data-category]');

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.trim().toLowerCase();

        allSections.forEach(section => {
            // O kategorideki tüm kurs kartlarını seçiyoruz (grid içindeki kartlar)
            const cards = section.querySelectorAll('.grid > div');

            let anyVisible = false; // O kategoride aramaya uyan kurs var mı kontrolü

            cards.forEach(card => {
                // Karttaki başlık: h3 (örneğin)
                const title = card.querySelector('h3');
                if (!title) return;

                const titleText = title.textContent.toLowerCase();

                if (titleText.includes(searchTerm)) {
                    card.classList.remove('hidden');
                    anyVisible = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Eğer kategori içinde aramaya uyan en az bir kart varsa kategori görünür,
            // yoksa kategori gizlenir
            if (anyVisible) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        });

        // Eğer arama boşsa, tüm kursları ve kategorileri göster
        if (searchTerm === '') {
            allSections.forEach(section => {
                section.classList.remove('hidden');
                section.querySelectorAll('.grid > div').forEach(card => card.classList.remove('hidden'));
            });
        }
    });
</script>
</body>
</html>
