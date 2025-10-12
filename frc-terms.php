<?php require_once 'config.php';
//a
$page_title = "FRC Terimleri Sözlüğü"; // Sayfa başlığını belirliyoruz
session_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - FRC Rookieverse</title>
    <link rel="icon" type="image/x-icon" href="assets/images/rokiverse_icon.png">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EDSVL8LRCY"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-EDSVL8LRCY');
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    
    <script>
        tailwind.config = {
            theme: { 
                extend: { 
                    colors: { 
                        'custom-yellow': '#E5AE32',
                        'fyk-purple': '#7333B3' // Fikret Yüksel Vakfı moru
                    } 
                } 
            }
        }
    </script>
    
    <style>
        /* Daha akıcı kaydırma için */
        html {
            scroll-behavior: smooth;
        }
        
        /* Sol Menü - Ana Stil */
        .term-menu-container {
            /* Kart gibi görünmesi için: beyaz arka plan, gölge ve yuvarlak kenarlar */
            background-color: white;
            padding: 1rem;
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); /* shadow-md */
            border: 1px solid #e5e7eb; /* Hafif border */
        }
        
        /* Listeden noktaları kaldırma */
        .term-menu-container nav ul, .term-menu-container nav {
            list-style: none;
            padding-left: 0; /* Gerekli olabilir */
        }

        /* Aktif anchor link için stil */
        .term-link.active {
            /* Sarı çizgi isteği */
            border-left: 4px solid #E5AE32; /* custom-yellow */
            font-weight: 600; 
            color: #1f2937; /* Koyu gri metin */
            background-color: #E5AE3233; /* Hafif sarı arka plan */
            /* Karttaki gibi yuvarlak kenar */
            border-radius: 0.375rem; /* rounded-md */
        }
        
        /* Normal link stilini temizleyip yeni yapıyı uyguluyoruz */
        .term-link {
            transition: all 150ms ease-in-out;
            border-left: 4px solid transparent; /* Varsayılan olarak şeffaf çizgi */
            padding-left: 1rem !important; /* pl-4 */
            padding-right: 1rem !important; /* px-4 */
        }

        /* Aktif olmayan hover durumuna soluk çizgi */
        .term-link:not(.active):hover {
            border-left: 4px solid #E5AE3266; /* Soluk sarı çizgi */
            background-color: #E5AE321A; /* Çok hafif sarı arka plan */
            color: #1f2937; /* Koyu metin */
            border-radius: 0.375rem; /* rounded-md */
        }
        
        /* Sözlük terim başlıkları (Term & Açıklama) */
        .term-item {
            display: block; 
        }
    </style>

</head>
<body class="bg-gray-50">

    <?php require_once 'navbar.php'; ?>
    
    <div class="min-h-screen">
        <section class="bg-gradient-to-br from-custom-yellow/10 via-white to-custom-yellow/5 pt-24 pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="flex justify-center items-center gap-4">
                    <i data-lucide="book-marked" class="w-12 h-12 text-custom-yellow"></i>
                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900">FRC Terimleri Sözlüğü</h1>
                </div>
                <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">FRC dünyasında sıkça karşılaşacağınız terimlerin ve kısaltmaların açıklamalarını burada bulabilirsiniz.</p>

                <div class="mt-8 max-w-lg mx-auto relative">
                    <input type="text" id="term-search" placeholder="Terim ara (Örn: Rookie, Alliance, roboRIO)" 
                           class="w-full pl-12 pr-4 py-3 border-2 border-custom-yellow/50 rounded-full focus:ring-2 focus:ring-custom-yellow focus:border-custom-yellow transition duration-150 shadow-md placeholder-gray-500">
                    <i data-lucide="search" class="w-6 h-6 absolute left-4 top-1/2 transform -translate-y-1/2 text-custom-yellow"></i>
                </div>
                </div>
        </section>

        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
                    
                    <aside class="lg:col-span-1 lg:sticky lg:top-24 h-max">
                        <div class="term-menu-container">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Kategoriler</h3>
                            <nav id="term-menu">
                                <ul class="space-y-1">
                                    <li><a href="#genel-terimler" class="term-link block py-2 text-gray-600">Genel Terimler</a></li>
                                    <li><a href="#oyun-ve-saha" class="term-link block py-2 text-gray-600">Oyun ve Saha</a></li>
                                    <li><a href="#robot-parcalari" class="term-link block py-2 text-gray-600">Robot Parçaları ve Mekanik</a></li>
                                    <li><a href="#takim-ve-roller" class="term-link block py-2 text-gray-600">Takım ve Roller</a></li>
                                    <li><a href="#oduller-ve-etkinlikler" class="term-link block py-2 text-gray-600">Ödüller ve Etkinlikler</a></li>
                                </ul>
                            </nav>
                        </div>
                    </aside>
                    
                    <main class="lg:col-span-3 space-y-12" id="glossary-main">
                        
                        <div id="genel-terimler" class="space-y-6 scroll-mt-24">
                            <h2 class="text-3xl font-bold text-gray-900 border-l-4 border-custom-yellow pl-4">Genel Terimler</h2>
                            <div class="bg-white p-6 rounded-lg shadow-sm border space-y-4 term-category">
                                <p class="term-item"><strong>FIRST:</strong> "For Inspiration and Recognition of Science and Technology" kelimelerinin baş harflerinden oluşan, gençleri bilim ve teknolojiye teşvik etmek amacıyla kurulmuş olan kâr amacı gütmeyen organizasyon.</p>
                                <p class="term-item"><strong>FRC:</strong> FIRST Robotics Competition. FIRST organizasyonunun lise öğrencilerine yönelik en büyük ve kapsamlı robotik yarışması.</p>
                                <p class="term-item"><strong>Gracious Professionalism (Duyarlı Profesyonellik):</strong> Hem rekabetin hem de karşılıklı saygının bir arada yürüdüğü bir FRC felsefesi. Rakiplerinize yardım etmeyi ve onlardan öğrenmeyi teşvik eder.</p>
                                <p class="term-item"><strong>Coopertition (İşbirlikçi Rekabet):</strong> "Cooperation" (iş birliği) ve "Competition" (rekabet) kelimelerinin birleşimidir. Takımların aynı anda hem rekabet edip hem de birbirlerine yardım ederek daha büyük başarılara ulaşmasını ifade eder.</p>
                                <p class="term-item"><strong>Rookie:</strong> FRC'ye ilk defa katılan takım veya üye.</p>
                                <p class="term-item"><strong>Veteran:</strong> FRC'de en az bir sezon tecrübesi olan takım veya üye.</p>
                            </div>
                        </div>

                        <div id="oyun-ve-saha" class="space-y-6 scroll-mt-24">
                            <h2 class="text-3xl font-bold text-gray-900 border-l-4 border-custom-yellow pl-4">Oyun ve Saha</h2>
                            <div class="bg-white p-6 rounded-lg shadow-sm border space-y-4 term-category">
                                <p class="term-item"><strong>Alliance (İttifak):</strong> Maç sırasında birlikte hareket eden takımlar grubu. Genellikle 3 takımdan oluşan Kırmızı (Red) ve Mavi (Blue) olmak üzere iki ittifak bulunur.</p>
                                <p class="term-item"><strong>Autonomous Period (Otonom Dönem):</strong> Maçın ilk 15 saniyesidir. Bu sürede robotlar, sürücü kontrolü olmadan önceden programlanmış görevleri yerine getirir.</p>
                                <p class="term-item"><strong>Tele-Op Period (Sürücü Kontrol Dönemi):</strong> Maçın otonomdan sonraki 2 dakika 15 saniyelik kısmıdır. Bu sürede sürücüler robotları uzaktan kontrol eder.</p>
                                <p class="term-item"><strong>Endgame (Oyun Sonu):</strong> Tele-Op periyodunun genellikle son 30 saniyesidir. Bu bölümde takımlar genellikle tırmanma gibi ekstra puan kazandıran özel görevleri yaparlar.</p>
                                <p class="term-item"><strong>Game Piece (Oyun Elemanı):</strong> O sezonun oyununda robotların manipüle ettiği (topladığı, taşıdığı, attığı) nesneler. (Örn: top, küp, koni).</p>
                                <p class="term-item"><strong>Driver Station (Sürücü İstasyonu):</strong> Sürücülerin maçı yönettikleri, bilgisayar ve kontrol cihazlarının (joystick, gamepad) bulunduğu alan.</p>
                                <p class="term-item"><strong>Ranking Points (RP - Sıralama Puanı):</strong> Takımların eleme turlarına kalmak için kvalifikasyon maçlarında kazandıkları puanlar. Genellikle galibiyete, beraberliğe ve belirli görevleri başarmaya göre verilir.</p>
                            </div>
                        </div>

                        <div id="robot-parcalari" class="space-y-6 scroll-mt-24">
                            <h2 class="text-3xl font-bold text-gray-900 border-l-4 border-custom-yellow pl-4">Robot Parçaları ve Mekanik</h2>
                            <div class="bg-white p-6 rounded-lg shadow-sm border space-y-4 term-category">
                                <p class="term-item"><strong>Chassis / Drivetrain (Şasi / Aktarma Organı):</strong> Robotun hareket etmesini sağlayan tekerlekler, motorlar, dişliler ve iskelet sisteminin bütünü.</p>
                                <p class="term-item"><strong>Bumper (Tampon):</strong> Robotların etrafını saran, genellikle kırmızı ve mavi renkli koruyucu köpüklerdir. Hem robotları korur hem de ittifak rengini belirtir.</p>
                                <p class="term-item"><strong>Manipulator / Actuator:</strong> Robotun oyun elemanlarını toplama, taşıma, fırlatma gibi işlevleri yerine getiren mekanizmalarına verilen genel ad.</p>
                                <p class="term-item"><strong>roboRIO:</strong> Robotun "beyni" olarak kabul edilen ana kontrolcüdür. Tüm motorları, sensörleri ve diğer elektronik bileşenleri yönetir.</p>
                                <p class="term-item"><strong>Motor Controller (Motor Sürücü):</strong> roboRIO'dan gelen sinyalleri motorların anlayacağı elektrik gücüne dönüştüren elektronik kart. (Örn: Talon, Spark MAX).</p>
                                <p class="term-item"><strong>Pneumatics (Pnömatik Sistem):</strong> Basınçlı hava kullanarak pistonları hareket ettiren ve robotta çeşitli mekanizmaları çalıştırmaya yarayan sistem.</p>
                                <p class="term-item"><strong>Sensor (Sensör):</strong> Robotun çevresini algılamasına yardımcı olan bileşenler. (Örn: Kamera, ultrasonik mesafe sensörü, jiroskop).</p>
                            </div>
                        </div>

                        <div id="takim-ve-roller" class="space-y-6 scroll-mt-24">
                            <h2 class="text-3xl font-bold text-gray-900 border-l-4 border-custom-yellow pl-4">Takım ve Roller</h2>
                            <div class="bg-white p-6 rounded-lg shadow-sm border space-y-4 term-category">
                                <p class="term-item"><strong>Drive Team (Sürücü Ekibi):</strong> Maç sırasında robotu yöneten ekip. Genellikle Sürücü (Driver), Operatör (Operator), Koç (Coach) ve İnsan Oyuncu (Human Player)'dan oluşur.</p>
                                <p class="term-item"><strong>Pit (Pit Alanı):</strong> Turnuvalarda her takıma ayrılan çalışma alanı. Robotların tamir edildiği, geliştirildiği ve diğer takımlarla sosyalleşilen yerdir.</p>
                                <p class="term-item"><strong>Scouting:</strong> Diğer takımların robotlarının yeteneklerini, stratejilerini ve performanslarını maçlar sırasında izleyerek veri toplama ve analiz etme süreci.</p>
                                <p class="term-item"><strong>Mentor:</strong> Takım öğrencilerine teknik veya sosyal konularda rehberlik eden gönüllü yetişkinler.</p>
                            </div>
                        </div>

                        <div id="oduller-ve-etkinlikler" class="space-y-6 scroll-mt-24">
                            <h2 class="text-3xl font-bold text-gray-900 border-l-4 border-custom-yellow pl-4">Ödüller ve Etkinlikler</h2>
                            <div class="bg-white p-6 rounded-lg shadow-sm border space-y-4 term-category">
                                <p class="term-item"><strong>Kickoff:</strong> Her yıl Ocak ayının başında yeni FRC sezonunun oyununun ve kurallarının tüm dünyaya duyurulduğu etkinlik.</p>
                                <p class="term-item"><strong>Regional / District:</strong> Takımların Dünya Şampiyonası'na katılma hakkı kazanmak için yarıştığı yerel veya bölgesel turnuvalar.</p>
                                <p class="term-item"><strong>Championship (Dünya Şampiyonası):</strong> Sezonun sonunda, dünyanın dört bir yanından gelen en iyi takımların yarıştığı final etkinliği.</p>
                                <hr class="my-4">
                                <p class="term-item"><strong>FIRST Impact Award (Etki Ödülü):</strong> FRC'deki en prestijli ödüldür. Robot performansının ötesinde, takımın FIRST misyonunu ne kadar iyi temsil ettiğine, topluma olan etkisine ve rol model olmasına bakılarak verilir.</p>
                                <p class="term-item"><strong>Engineering Inspiration Award (Mühendislik İlhamı Ödülü):</strong> Bilim ve teknolojiyi kutlama konusunda üstün başarı gösteren, öğrencileri mühendislik mesleğine özendiren takımlara verilir.</p>
                                <p class="term-item"><strong>Rookie All-Star Award (Çaylak Yıldız Ödülü):</strong> İlk senesinde hem güçlü bir robot performansı sergileyen hem de FIRST felsefesini benimseyerek gelecekte güçlü bir etki yaratma potansiyeline sahip çaylak takıma verilir.</p>
                                <p class="term-item"><strong>Excellence in Engineering Award (Mühendislikte Mükemmellik Ödülü):</strong> Robotun tasarımı ve yapımında sağlam, güvenilir ve yenilikçi mühendislik prensiplerini sergileyen takıma verilir.</p>
                                <p class="term-item"><strong>Industrial Design Award (Endüstriyel Tasarım Ödülü):</strong> Robotun işlevselliğini, estetiğini ve üretim kolaylığını birleştiren endüstriyel tasarımda üstün başarı gösteren takıma verilir.</p>
                                <p class="term-item"><strong>Autonomous Award (Otonom Ödülü):</strong> Otonom modda güvenilir, tutarlı ve etkili performans gösteren robot tasarımını ve programlamasını takdir eden ödüldür.</p>
                                <p class="term-item"><strong>Innovation in Control Award (Kontrolde Yenilik Ödülü):</strong> Robotun hareketini, manipülasyonunu veya karar vermesini sağlayan elektrik, yazılım ve kontrol sistemlerinde zarif ve yenilikçi uygulamalar kullanan takıma verilir.</p>
                                <p class="term-item"><strong>Creativity Award (Yaratıcılık Ödülü):</strong> Oyunu çözmek veya özel bir görevi yerine getirmek için alışılmışın dışında, yenilikçi ve akıllıca bir mekanik veya stratejik çözüm sergileyen takıma verilir.</p>
                                <p class="term-item"><strong>Quality Award (Kalite Ödülü):</strong> Robotun işlevselliği, dayanıklılığı ve profesyonel görünümü ile öne çıkan, sağlam ve iyi üretilmiş bir robota sahip takıma verilir.</p>
                                <p class="term-item"><strong>Gracious Professionalism® Award (Duyarlı Profesyonellik Ödülü):</strong> Rekabetçi ruhu ve karşılıklı saygıyı birleştirerek FRC felsefesini sahada ve pit alanında en iyi şekilde sergileyen takıma verilir.</p>
                                <p class="term-item"><strong>Team Spirit Award (Takım Ruhu Ödülü):</strong> Coşkusu, pozitifliği, görünürlüğü ve etkinliği ile sahaya ve turnuvaya enerji katan, benzersiz bir takım ruhuna sahip takıma verilir.</p>
                                <p class="term-item"><strong>Judges’ Award (Jüri Özel Ödülü):</strong> Jürilerin, yukarıdaki kategorilere tam olarak uymayan, ancak takdir etmeye değer benzersiz başarıları, çabaları veya zorlukların üstesinden gelmesi nedeniyle öne çıkan bir takıma verdiği ödüldür.</p>
                                <p class="term-item"><strong>Winner (Kazanan):</strong> Turnuvanın final maçlarını kazanarak şampiyon olan ittifaktaki takımlara verilen unvandır.</p>
                                <p class="term-item"><strong>Finalist:</strong> Turnuvanın final maçlarını kaybederek ikinci olan ittifaktaki takımlara verilen unvandır.</p>
                                <hr class="my-4">
                                <p class="term-item"><strong>FIRST Dean’s List Award (Dean's List Ödülü):</strong> Takım içinde liderlik ve teknik uzmanlık gösteren, FIRST'ün misyonunu benimsemiş, olağanüstü öğrencilere verilen bireysel bir ödüldür.</p>
                                <p class="term-item"><strong>Woodie Flowers Finalist Award (Woodie Flowers Finalist Ödülü):</strong> Takım öğrencilerine rehberlik eden, ilham veren ve liderlik eden olağanüstü mentorlara verilen bireysel bir ödüldür.</p>
                                <p class="term-item"><strong>Volunteer of the Year Award (Yılın Gönüllüsü Ödülü):</strong> FRC etkinliklerinin başarısına önemli katkılarda bulunmuş, olağanüstü bir gönüllüye verilen bireysel bir ödüldür.</p>
                                <p class="term-item"><strong>Digital Animation Award (Dijital Animasyon Ödülü):</strong> FRC temasını veya bir bilim/teknoloji kavramını anlatan en iyi dijital animasyon videosunu hazırlayan takıma verilir.</p>
                                <p class="term-item"><strong>Safety Animation Award (Güvenlik Animasyon Ödülü):</strong> UL sponsorluğunda, FRC güvenliğini eğlenceli ve yaratıcı bir şekilde anlatan en iyi animasyon videosunu hazırlayan takıma verilir.</p>
                                <p class="term-item"><strong>Imagery Award (İmaj Ödülü):</strong> Takım ruhunu, imajını ve özgünlüğünü en iyi şekilde yansıtan takıma verilir.</p>
                                <p class="term-item"><strong>Team Sustainability Award (Takım Sürdürülebilirlik Ödülü):</strong> Takım yapısını, finansal modelini ve mentorluk sistemini gelecek yıllara taşıyabilecek sürdürülebilir bir model oluşturmayı başaran takıma verilir.</p>
                                <p class="term-item"><strong>Rising All-Star Award (Yükselen Yıldız Ödülü):</strong> Bölgesel/Bölgesel turnuvalarda, ikinci yılını dolduran ve Rookie All-Star ödülünden sonra önemli ölçüde gelişme gösteren takımlara verilen ödüldür.</p>
                                <p class="term-item"><strong>Founder’s Award (Kurucu Ödülü):</strong> Organizasyonda veya camiada, FIRST misyonuna önemli katkılarda bulunan kişi veya kurumlara verilen ödüldür.</p>
                                <hr class="my-4">
                                <div class="bg-fyk-purple/10 p-4 rounded-lg border-l-4 border-fyk-purple text-gray-700 mb-4">
    <p class="font-semibold text-fyk-purple">Daha fazla bilgi almak için FRC Türkiye Sayfasını ziyaret edin:</p>
    <a href="https://www.frcturkiye.org" target="_blank" class="text-fyk-purple font-bold hover:text-fyk-purple/80 underline transition duration-150 ease-in-out">FRC Türkiye Resmi Web Sitesi</a>
</div>

                            </div>
                        </div>

                    </main>
                </div>
            </div>
        </section>

    </div>
    
    <?php require_once 'footer.php'?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            // 1. ANCHOR VE SCROLL İŞLEVİ
            const menuLinks = document.querySelectorAll('#term-menu a');
            const sections = document.querySelectorAll('main > div[id]');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        menuLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href').substring(1) === entry.target.id) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            }, { rootMargin: "-50% 0px -50% 0px" });

            sections.forEach(section => observer.observe(section));

            // 2. ARAMA İŞLEVİ
            const searchInput = document.getElementById('term-search');
            const termItems = document.querySelectorAll('.term-item');
            
            searchInput.addEventListener('keyup', (e) => {
                const searchValue = e.target.value.toLowerCase().trim();

                termItems.forEach(item => {
    
                    const termText = item.querySelector('strong') ? item.querySelector('strong').textContent.toLowerCase() : item.textContent.toLowerCase();
                    
                    if (termText.includes(searchValue)) {
                        item.style.display = 'block'; // Göster
                    } else {
                        item.style.display = 'none'; // Gizle
                    }
                });


                sections.forEach(section => {
                    const categoryBox = section.querySelector('.term-category');
                    const categoryItems = categoryBox ? categoryBox.querySelectorAll('.term-item') : [];
                    let visibleItemsCount = 0;

                    categoryItems.forEach(item => {
                        if (item.style.display === 'block' || item.style.display === '') {
                            visibleItemsCount++;
                        }
                    });


                    if (visibleItemsCount > 0 || searchValue === '') { 
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });
    </script>

</body>
</html>