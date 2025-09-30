<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRC Kelime Bulma - FRC Rookieverse</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">

    <style>
        .correct-permanent {
            background-color: #16a34a !important; /* Tailwind green-600 */
            color: white !important;
            border: none;
        }

        .wrong-permanent {
            background-color: #dc2626 !important; /* Tailwind red-600 */
            color: white !important;
            border: none;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .shake {
            animation: shake 0.4s;
        }

        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .pop {
            animation: pop 0.3s ease-out;
        }

        /* Tailwind CSS'e özel renk tanımı */
        :root {
            --custom-yellow: #E5AE32;
        }

        /* Geri sayım çubuğu animasyonu */
        #timer-bar {
            transition: width 1s linear;
            background-color: var(--custom-yellow);
        }

        /* Harf kutuları için stil */
        .letter-box {
            width: 3rem;
            height: 3rem;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 2px solid var(--custom-yellow);
            margin: 0 0.25rem;
        }

        /* Yanlış tahmin için harf butonlarına stil */
        .wrong-guess {
            background-color: #ef4444 !important;
            color: white;
        }
        .correct-guess {
            background-color: #00ff32 !important;
            color: white;
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
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">FRC Kelime Bulma</h1>
                <p class="text-gray-600 mt-2">Tanımlanan FRC terimini bulmaya çalış!</p>
            </div>

            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div class="flex items-center space-x-2">
                    <i data-lucide="clock" class="text-custom-yellow"></i>
                    <span class="text-xl font-bold text-gray-800" id="timer-display">60</span><span class="text-gray-600 ml-1">saniye</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i data-lucide="award" class="text-custom-yellow"></i>
                    <span class="text-xl font-bold text-gray-800" id="score-display">0</span><span class="text-gray-600 ml-1">puan</span>
                </div>
            </div>

            <div class="w-full bg-gray-200 h-2 rounded-full mb-8 overflow-hidden">
                <div id="timer-bar" class="h-full" style="width: 100%;"></div>
            </div>

            <div id="game-container" class="text-center">
                <p class="text-xl font-semibold text-gray-700 mb-6" id="word-clue">Yükleniyor...</p>
                <div id="word-container" class="flex justify-center mb-8">
                </div>
                <p class="text-lg text-gray-600 mb-4">Bir harf seçin veya kelimeyi tahmin edin:</p>
                <div id="alphabet-container" class="flex flex-wrap justify-center gap-2 mb-6">
                </div>
                <div class="flex justify-center gap-4">
                    <button id="skip-button" class="px-6 py-3 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400">Geç</button>
                </div>
            </div>

            <div id="results-container" class="text-center hidden">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Yarışma Tamamlandı!</h2>
                <p class="text-xl text-gray-700 mb-6">Toplam Puanınız: <span class="font-bold text-custom-yellow" id="final-score">0</span></p>
                <button onclick="window.location.reload()" class="px-6 py-3 border-2 border-custom-yellow text-custom-yellow font-semibold rounded-lg hover:bg-custom-yellow hover:text-white">Tekrar Oyna</button>
                <button id="goBack" class="px-6 py-3 border-2 border-custom-yellow text-custom-yellow font-semibold rounded-lg hover:bg-custom-yellow hover:text-white">Oyunlara Geri Dön</button>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'?>


<script>
    lucide.createIcons();

    document.getElementById('goBack').addEventListener('click', () => {
        window.location = 'games.php'
    })

    const wordClueDisplay = document.getElementById('word-clue');
    const wordContainer = document.getElementById('word-container');
    const alphabetContainer = document.getElementById('alphabet-container');
    const skipButton = document.getElementById('skip-button');
    const timerDisplay = document.getElementById('timer-display');
    const scoreDisplay = document.getElementById('score-display');
    const timerBar = document.getElementById('timer-bar');
    const gameContainer = document.getElementById('game-container');
    const resultsContainer = document.getElementById('results-container');

    // FRC terimleri ve tanımları
    const words = [
        { word: "DRIVETRAIN", clue: "Robotun hareket etmesini sağlayan alt sistem." },
        { word: "PNEUMATICS", clue: "Hava basıncı kullanarak çalışan bir sistem. Genellikle robot kollarında kullanılır." },
        { word: "AUTONOMOUS", clue: "Yarışmanın ilk 15 saniyesinde, robotun önceden programlanmış görevleri kendi kendine tamamladığı aşama." },
        { word: "TELEOP", clue: "Yarışmanın robotun sürücüler tarafından kontrol edildiği ikinci ve uzun aşaması." },
        { word: "INTAKE", clue: "Oyun objelerini (top, küp, vb.) toplamaya yarayan mekanizma." },
        { word: "SHOOTER", clue: "Oyun objelerini belirli bir mesafeye fırlatmaya yarayan mekanizma." },
        { word: "ALLIANCE", clue: "Bir maçı birlikte oynayan 3 takımdan oluşan grup." }
    ];

    let currentWordObj = {};
    let guessedLetters = [];
    let score = 0;
    let timeLeft = 60;
    let timerInterval;
    let gameActive = true;

    // Harf kutularını oluşturur
    function createLetterBoxes() {
        wordContainer.innerHTML = '';
        const word = currentWordObj.word;
        for (let i = 0; i < word.length; i++) {
            const letterBox = document.createElement('div');
            letterBox.classList.add('letter-box');
            letterBox.id = 'letter-box-' + i;
            wordContainer.appendChild(letterBox);
        }
    }

    // Harf butonlarını oluşturur
    function createAlphabetButtons() {
        alphabetContainer.innerHTML = '';
        for (let i = 65; i <= 90; i++) {
            const letter = String.fromCharCode(i);
            const button = document.createElement('button');
            button.textContent = letter;
            button.setAttribute('data-letter', letter);
            button.classList.add('px-3', 'py-2', 'bg-gray-200', 'text-gray-800', 'font-semibold', 'rounded-md', 'hover:bg-custom-yellow/20', 'transition-colors', 'duration-200');
            button.onclick = () => handleGuess(letter);
            alphabetContainer.appendChild(button);
        }
    }

    // Yeni bir kelime yükler
    function loadNewWord() {
        if (!gameActive || words.length === 0) {
            endGame();
            return;
        }
        const randomIndex = Math.floor(Math.random() * words.length);
        currentWordObj = words[randomIndex];
        guessedLetters = [];
        wordClueDisplay.textContent = currentWordObj.clue;
        createLetterBoxes();
        createAlphabetButtons();
    }

    // Zamanlayıcıyı başlatır
    function startTimer() {
        timerBar.style.width = '100%';
        timerInterval = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = timeLeft;
            timerBar.style.width = (timeLeft / 60) * 100 + '%';
            if (timeLeft <= 0) {
                endGame();
            }
        }, 1000);
    }

    // Harf tahmini kontrolü
    function handleGuess(letter) {
        if (!gameActive || guessedLetters.includes(letter)) return;

        const letterButton = document.querySelector(`[data-letter='${letter}']`);
        if (letterButton) letterButton.disabled = true;

        guessedLetters.push(letter);

        let found = false;
        let wordGuessed = true;

        for (let i = 0; i < currentWordObj.word.length; i++) {
            const box = document.getElementById(`letter-box-${i}`);
            if (!box) continue;

            if (currentWordObj.word[i] === letter) {
                box.textContent = letter;
                score += 10;
                found = true;
            }

            if (box.textContent === "") {
                wordGuessed = false;
            }
        }

        if (!found) {
            score -= 4;
            if (letterButton) {
                letterButton.classList.add('wrong-permanent'); // 🔴 stays red
                letterButton.classList.add('shake'); // optional shake animation
                setTimeout(() => letterButton.classList.remove('shake'), 400);
            }
        } else {
            if (letterButton) {
                letterButton.classList.add('correct-permanent'); // 🟢 stays green
                letterButton.classList.add('pop'); // optional pop animation
                setTimeout(() => letterButton.classList.remove('pop'), 300);
            }
        }

        if (score < 0) score = 0;
        scoreDisplay.textContent = score;

        if (wordGuessed) {
            setTimeout(loadNewWord, 500);
        }
    }



    // Oyunu sonlandırır ve sonuçları gösterir
    function endGame() {
        gameActive = false;
        clearInterval(timerInterval);
        gameContainer.classList.add('hidden');
        resultsContainer.classList.remove('hidden');
        document.getElementById('final-score').textContent = score;
    }

    // Klavyeden harf girişini dinler
    document.addEventListener('keydown', (event) => {
        const pressedKey = event.key.toUpperCase();
        if (pressedKey.length === 1 && pressedKey >= 'A' && pressedKey <= 'Z' && gameActive) {
            handleGuess(pressedKey);
        }
    });

    // Buton event listener'ları
    skipButton.addEventListener('click', loadNewWord);

    // Yarışmayı başlat
    startTimer();
    loadNewWord();
</script>

</body>
</html>