<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs Modül Oynatıcı - Şablon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif; }
        .hidden { display: none; }
        .skip-indicator {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: rgba(0, 0, 0, 0.6);
            color: white; padding: 1rem 1.5rem; border-radius: 9999px; display: flex; align-items: center;
            gap: 0.5rem; font-size: 1.125rem; font-weight: 600; pointer-events: none; opacity: 0;
            transition: opacity 0.5s ease-out;
        }
        .skip-indicator.show { animation: skip-animation 0.8s ease-out forwards; }
        @keyframes skip-animation {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
            20% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1); }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'custom-yellow': '#E5AE32' }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
<nav class="bg-white shadow-md p-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="#" class="rookieverse font-bold text-xl" style="font-family: 'Sakana', system-ui, sans-serif !important; color: #E5AE32;">ROBOTİCTR</a>
    </div>
</nav>

<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <a href="#" class="inline-flex items-center text-custom-yellow hover:bg-custom-yellow/10 p-2 rounded-md">
                <i data-lucide="arrow-left" class="mr-2" style="width: 18px; height: 18px;"></i>
                Kurs Detayına Geri Dön
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Robotik ve Yapay Zeka Başlangıç Kursu</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3">
                <div class="bg-black rounded-lg overflow-hidden relative aspect-video">
                    <video id="module-video" class="w-full h-full" controls autoplay>
                        <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                        Tarayıcınız video etiketini desteklemiyor.
                    </video>
                    <div id="skip-backward-indicator" class="skip-indicator hidden"><i data-lucide="rewind" class="w-8 h-8"></i></div>
                    <div id="skip-forward-indicator" class="skip-indicator hidden"><i data-lucide="fast-forward" class="w-8 h-8"></i></div>
                </div>

                <div class="mt-6 p-6 bg-white rounded-lg shadow-md">
                    <div class="flex items-center space-x-2 mb-4">
                        <i data-lucide="play-circle" class="text-custom-yellow w-6 h-6"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Modül 2: Robotik Nedir?</h2>
                    </div>
                    <p class="text-lg leading-relaxed text-gray-700">Bu modülde, robotik biliminin temel prensiplerini ve modern robotların nasıl çalıştığını öğreneceksiniz.</p>

                    <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <a href="#" class="flex items-center text-gray-600 hover:text-custom-yellow transition-colors">
                            <i data-lucide="chevrons-left" class="w-5 h-5 mr-2"></i>
                            Önceki Modül
                        </a>
                        <a href="#" class="flex items-center text-custom-yellow hover:text-custom-yellow/80 transition-colors">
                            Sonraki Modül
                            <i data-lucide="chevrons-right" class="w-5 h-5 ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Kurs İçeriği</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors hover:bg-gray-50">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-gray-200 text-gray-600">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-gray-900">Giriş ve Kurs Tanıtımı</h4></div>
                        </a>
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors bg-custom-yellow/10 border-l-4 border-custom-yellow">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-custom-yellow text-white">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-custom-yellow">Robotik Nedir?</h4></div>
                        </a>
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors hover:bg-gray-50">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-gray-200 text-gray-600">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-gray-900">Yapay Zeka Temelleri</h4></div>
                        </a>
                        <a href="#" class="flex items-center space-x-4 p-3 rounded-lg transition-colors hover:bg-gray-50">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0 bg-gray-200 text-gray-600">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div><h4 class="font-medium text-gray-900">İlk Robotunu Kodla</h4></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    const video = document.getElementById('module-video');
    const videoContainer = video.parentElement;
    const skipBackwardIndicator = document.getElementById('skip-backward-indicator');
    const skipForwardIndicator = document.getElementById('skip-forward-indicator');
    let skipIndicatorTimeout;

    function showSkipIndicator(indicator) {
        clearTimeout(skipIndicatorTimeout);
        (indicator === skipForwardIndicator ? skipBackwardIndicator : skipForwardIndicator).classList.remove('show');
        indicator.classList.remove('hidden');
        indicator.classList.add('show');
        lucide.createIcons();
        skipIndicatorTimeout = setTimeout(() => {
            indicator.classList.remove('show');
            indicator.classList.add('hidden');
        }, 800);
    }

    document.addEventListener('keydown', (event) => {
        const activeEl = document.activeElement;
        if (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable) {
            return;
        }

        switch (event.key.toLowerCase()) {
            case ' ':
            case 'k':
                event.preventDefault();
                video.paused ? video.play() : video.pause();
                break;
            case 'f':
                if (!document.fullscreenElement) {
                    videoContainer.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
                break;
            case 'l':
                video.currentTime += 5;
                showSkipIndicator(skipForwardIndicator);
                break;
            case 'j':
                video.currentTime -= 5;
                showSkipIndicator(skipBackwardIndicator);
                break;
        }
    });
</script>
</body>
</html>