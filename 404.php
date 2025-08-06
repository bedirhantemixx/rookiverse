<?php
// config.php is included to ensure consistency with the rest of the site,
// though it's not strictly necessary for this page's functionality.
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Sayfa Bulunamadı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <style>
        /* General Page Styles */
        :root {
            --primary-color: #E5AE32; /* Main team color */
            --primary-hover: #c4952b;
            --dark-metal: #4b5563;   /* Dark gray for gears */
            --light-metal: #d1d5db;  /* Light gray for gears */
            --text-dark: #1f2937;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            overflow: hidden; /* Prevents scrollbars during animation */
        }

        /* Main container for the 404 content */
        .container-404 {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        /* The scene where the animation happens */
        .gear-scene {
            position: relative;
            width: 500px;
            height: 200px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Individual gear styles */
        .gear {
            position: absolute;
            width: 160px;
            height: 160px;
            fill: var(--light-metal);
            stroke: var(--dark-metal);
            stroke-width: 2;
            transform-origin: center;
            opacity: 0; /* Start hidden */
        }
        
        .gear.gear-1 {
            left: 0;
            animation: spin-and-settle 3.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        
        .gear.gear-2 {
            width: 200px;
            height: 200px;
            fill: var(--primary-color); /* Use team color for the center gear */
            animation: spin-and-settle-rev 3.5s cubic-bezier(0.25, 1, 0.5, 1) 0.2s forwards;
        }
        
        .gear.gear-3 {
            right: 0;
            animation: spin-and-settle 3.5s cubic-bezier(0.25, 1, 0.5, 1) 0.4s forwards;
        }

        /* The "404" text that appears on the gears */
        .gear-number {
            position: absolute;
            font-weight: 900;
            font-size: 8rem;
            color: white;
            -webkit-text-stroke: 4px var(--text-dark);
            opacity: 0;
            transform: scale(0.5);
            animation: reveal-text 0.8s ease-out 2.5s forwards;
        }

        /* The text content that appears after the animation */
        .info-text {
            opacity: 0;
            transform: translateY(20px);
            animation: fade-in-up 1s ease-out 3s forwards;
        }

        /* Keyframe Animations */
        @keyframes spin-and-settle {
            0% {
                opacity: 0;
                transform: rotate(0deg) scale(0.5);
            }
            50% {
                opacity: 1;
                transform: rotate(1080deg) scale(1.1);
            }
            100% {
                opacity: 1;
                transform: rotate(1080deg) scale(1);
            }
        }
        @keyframes spin-and-settle-rev {
            0% {
                opacity: 0;
                transform: rotate(0deg) scale(0.5);
            }
            50% {
                opacity: 1;
                transform: rotate(-1080deg) scale(1.1);
            }
            100% {
                opacity: 1;
                transform: rotate(-1080deg) scale(1);
            }
        }
        @keyframes reveal-text {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        @keyframes fade-in-up {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php require_once 'navbar.php'; ?>

    <div class="container-404">
        <div class="bg-white p-8 sm:p-12 rounded-lg shadow-2xl">
            <div class="gear-scene">
                <!-- Gears SVG -->
                <svg class="gear gear-1" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M 50,10 A 40,40 0 1,1 49.99,10.01 M 50,10 L 50,18 A 32,32 0 1,1 49.99,18.01 M 50,10 L 58,10 A 40,40 0 0,0 79,21 L 73,26 A 32,32 0 0,1 56,18 M 50,10 L 79,21 L 90,50 L 79,79 L 50,90 L 21,79 L 10,50 L 21,21 Z"></path></svg>
                <svg class="gear gear-2" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M 50,10 A 40,40 0 1,1 49.99,10.01 M 50,10 L 50,18 A 32,32 0 1,1 49.99,18.01 M 50,10 L 58,10 A 40,40 0 0,0 79,21 L 73,26 A 32,32 0 0,1 56,18 M 50,10 L 79,21 L 90,50 L 79,79 L 50,90 L 21,79 L 10,50 L 21,21 Z"></path></svg>
                <svg class="gear gear-3" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M 50,10 A 40,40 0 1,1 49.99,10.01 M 50,10 L 50,18 A 32,32 0 1,1 49.99,18.01 M 50,10 L 58,10 A 40,40 0 0,0 79,21 L 73,26 A 32,32 0 0,1 56,18 M 50,10 L 79,21 L 90,50 L 79,79 L 50,90 L 21,79 L 10,50 L 21,21 Z"></path></svg>
                
                <!-- 404 Text -->
                <div class="gear-number" style="left: 12%;">4</div>
                <div class="gear-number" style="font-size: 11rem;">0</div>
                <div class="gear-number" style="right: 12%;">4</div>
            </div>

            <div class="info-text">
                <h1 class="text-4xl font-bold text-text-dark">Sayfa Bulunamadı</h1>
                <p class="mt-4 text-lg text-gray-600">
                    Aradığınız parça bu montajda yok gibi görünüyor.
                </p>
                <div class="mt-8">
                    <a href="index.php" class="btn">
                        <i data-lucide="home" class="mr-2"></i> Ana Sayfaya Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>