<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRC Hafıza Oyunu - FRC Rookieverse</title>
    <link rel="icon" type="image/x-icon" href="assets/images/rokiverse_icon.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EDSVL8LRCY"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-EDSVL8LRCY');
    </script>
    <style>
        :root {
            --custom-yellow: #E5AE32;
        }

        #game-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 1rem;
            perspective: 1000px;
            max-width: 600px;
        }

        .card {
            position: relative;
            width: 100%;
            height: 150px;
            cursor: pointer;
            transform-style: preserve-3d;
            transition: transform 0.5s ease;
        }

        .card.flipped {
            transform: rotateY(180deg);
        }

        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 8px;
            border: 3px solid var(--custom-yellow);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            color: #fff;
            padding: 0.5rem;
        }

        .card-front {
            background-color: #f7f7f7;
            transform: rotateY(180deg);
        }

        .card-front img {
            max-width: 90%;
            max-height: 70%;
            object-fit: contain;
        }

        .card-front .item-name {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            font-weight: bold;
            color: #333;
        }

        .card-back {
            background-color: #E5AE32;
        }

        /* Eşleşen kartlar için stil */
        .matched {
            border: 3px solid #16a34a;
        }
    </style>

    <script>
        tailwind.config = {
            theme: { extend: { colors: { 'custom-yellow': '#E5AE32' } } }
        }
    </script>
</head>
<body class="bg-gray-100">

<?php require_once 'navbar.php'; ?>

<div class="min-h-screen pt-20 pb-10 flex flex-col items-center">
    <div class="max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">FRC Hafıza Oyunu</h1>
            <p class="text-gray-600 mb-4">Aynı kartların çiftlerini bul!</p>

            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div class="flex items-center space-x-2">
                    <i data-lucide="clock" class="text-custom-yellow"></i>
                    <span class="text-xl font-bold text-gray-800" id="timer-display">0</span><span class="text-gray-600 ml-1">saniye</span>
                </div>
            </div>

            <div id="game-board" class="mx-auto"></div>

            <div id="results-container" class="hidden mt-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Tebrikler, Oyunu Bitirdin!</h2>
                <p class="text-xl text-gray-700 mb-6">Süren: <span class="font-bold text-custom-yellow" id="final-time">0</span> saniye</p>
                <button onclick="window.location.reload()" class="px-6 py-3 border-2 border-custom-yellow text-custom-yellow font-semibold rounded-lg hover:bg-custom-yellow hover:text-white transition-colors duration-200">Tekrar Oyna</button>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'?>

<script>
    lucide.createIcons();

    const gameBoard = document.getElementById('game-board');
    const timerDisplay = document.getElementById('timer-display');
    const resultsContainer = document.getElementById('results-container');
    const finalTimeDisplay = document.getElementById('final-time');

    // Kart resimleri ve isimleri
    const cardItems = [
        { name: "ROBOT", image: "https://pics.firstinspires.org/FIRST/FIRST-Robotics-Competition-Robot-2023-Charged-Up.png" },
        { name: "LOGO", image: "https://pics.firstinspires.org/FIRST/FIRST-Robotics-Competition-Logo.png" },
        { name: "FTC", image: "https://pics.firstinspires.org/FIRST/FIRST-Tech-Challenge-Logo.png" },
        { name: "FLL", image: "https://pics.firstinspires.org/FIRST/FIRST-Lego-League-Logo.png" },
        { name: "INSPIRES", image: "https://pics.firstinspires.org/FIRST/FIRST-Inspires-Logo.png" },
        { name: "GLOBAL", image: "https://pics.firstinspires.org/FIRST/FIRST-Global-Logo.png" },
        { name: "CHAMPIONSHIP", image: "https://pics.firstinspires.org/FIRST/FIRST-Championship-Logo.png" },
        { name: "VOLUNTEERS", image: "https://pics.firstinspires.org/FIRST/FIRST-Volunteers-Logo.png" }
    ];

    let cards = [];
    let flippedCards = [];
    let matchedPairs = 0;
    let time = 0;
    let timerInterval;

    // Oyunu başlatma fonksiyonu
    function startGame() {
        cards = [...cardItems, ...cardItems];
        cards.sort(() => Math.random() - 0.5);

        gameBoard.innerHTML = '';
        cards.forEach(item => {
            const cardElement = document.createElement('div');
            cardElement.classList.add('card');
            cardElement.dataset.name = item.name;

            const cardFront = document.createElement('div');
            cardFront.classList.add('card-front');
            const img = document.createElement('img');
            img.src = item.image;
            const nameText = document.createElement('div');
            nameText.classList.add('item-name');
            nameText.textContent = item.name;
            cardFront.appendChild(img);
            cardFront.appendChild(nameText);

            const cardBack = document.createElement('div');
            cardBack.classList.add('card-back');
            cardBack.textContent = '?';

            cardElement.appendChild(cardFront);
            cardElement.appendChild(cardBack);

            cardElement.addEventListener('click', () => flipCard(cardElement));
            gameBoard.appendChild(cardElement);
        });

        startTimer();
    }

    // Zamanlayıcıyı başlatır
    function startTimer() {
        timerInterval = setInterval(() => {
            time++;
            timerDisplay.textContent = time;
        }, 1000);
    }

    // Kartı çevirme mantığı
    function flipCard(card) {
        if (flippedCards.length < 2 && !card.classList.contains('flipped')) {
            card.classList.add('flipped');
            flippedCards.push(card);

            if (flippedCards.length === 2) {
                setTimeout(checkMatch, 1000);
            }
        }
    }

    // Eşleşme kontrolü
    function checkMatch() {
        const [card1, card2] = flippedCards;
        const name1 = card1.dataset.name;
        const name2 = card2.dataset.name;

        if (name1 === name2) {
            card1.classList.add('matched');
            card2.classList.add('matched');
            matchedPairs++;

            if (matchedPairs === cardItems.length) {
                endGame();
            }
        } else {
            card1.classList.remove('flipped');
            card2.classList.remove('flipped');
        }

        flippedCards = [];
    }

    // Oyunu bitirme
    function endGame() {
        clearInterval(timerInterval);
        finalTimeDisplay.textContent = time;
        resultsContainer.classList.remove('hidden');
        gameBoard.style.display = 'none';
    }

    // Oyunu başlat
    startGame();
</script>

</body>
</html>