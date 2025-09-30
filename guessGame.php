<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRC Takım Bilmece - FRC Rookieverse</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">

    <style>
        :root {
            --custom-yellow: #E5AE32;
        }

        #team-name-display {
            min-height: 4rem;
        }

        .game-over-card {
            border: 2px dashed var(--custom-yellow);
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: pop-up 0.5s ease-out;
        }

        @keyframes pop-up {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">FRC Takım Bilmece</h1>
            <p class="text-gray-600 mb-4">Takım ismine bakarak doğru numarayı bul!</p>

            <div id="game-options" class="mb-6 border-b pb-4">
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                    <select id="country-select" class="p-2 border rounded-md">
                        <option value="all">Tüm Takımlar</option>
                        <option value="global">Global Takımlar</option>
                        <option value="turkish">Türk Takımları</option>
                    </select>
                    <select id="difficulty-select" class="p-2 border rounded-md">
                        <option value="easy">Kolay</option>
                        <option value="medium">Orta</option>
                        <option value="hard">Zor</option>
                    </select>
                    <button id="start-button" class="px-6 py-2 bg-custom-yellow text-white font-semibold rounded-lg hover:bg-custom-yellow/80 transition-colors">Oyunu Başlat</button>
                </div>
            </div>

            <div id="game-container" class="hidden">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="award" class="text-custom-yellow"></i>
                        <span class="text-xl font-bold text-gray-800" id="score-display">0</span><span class="text-gray-600 ml-1">puan</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="clock" class="text-custom-yellow"></i>
                        <span class="text-xl font-bold text-gray-800" id="timer-display">60</span><span class="text-gray-600 ml-1">saniye</span>
                    </div>
                </div>

                <p class="text-xl font-semibold text-gray-700 mb-6" id="team-name-display">Hazırlanıyor...</p>
                <div class="w-full flex justify-center mb-6">
                    <input type="number" id="team-number-input" class="p-4 text-center text-4xl font-bold border-2 border-custom-yellow rounded-lg w-48 transition-colors focus:outline-none focus:ring-2 focus:ring-custom-yellow focus:border-transparent" placeholder="????">
                </div>
                <p id="feedback-display" class="text-lg font-semibold h-8"></p>
                <div class="flex justify-center gap-4 mt-6">
                    <button id="guess-button" class="px-6 py-3 bg-custom-yellow text-white font-semibold rounded-lg hover:bg-custom-yellow/80 transition-colors">Tahmin Et</button>
                    <button id="skip-button" class="px-6 py-3 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400">Geç</button>
                </div>
            </div>

            <div id="results-container" class="hidden mt-8 text-center">
                <div class="game-over-card">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Oyun Bitti!</h2>
                    <p class="text-xl text-gray-700 mb-6">Toplam Skorun: <span class="font-bold text-custom-yellow" id="final-score">0</span></p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <button onclick="window.location.reload()" class="px-6 py-3 border-2 border-custom-yellow text-custom-yellow font-semibold rounded-lg hover:bg-custom-yellow hover:text-white transition-colors duration-200">Yeniden Başlat</button>
                        <a href="index.php" class="px-6 py-3 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400 flex items-center justify-center">Ana Sayfaya Dön</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    const teamNameDisplay = document.getElementById('team-name-display');
    const teamNumberInput = document.getElementById('team-number-input');
    const guessButton = document.getElementById('guess-button');
    const skipButton = document.getElementById('skip-button');
    const feedbackDisplay = document.getElementById('feedback-display');
    const startButton = document.getElementById('start-button');
    const countrySelect = document.getElementById('country-select');
    const difficultySelect = document.getElementById('difficulty-select');
    const gameOptions = document.getElementById('game-options');
    const gameContainer = document.getElementById('game-container');
    const resultsContainer = document.getElementById('results-container');
    const finalScoreDisplay = document.getElementById('final-score');
    const scoreDisplay = document.getElementById('score-display');
    const timerDisplay = document.getElementById('timer-display');

    // Takım verileri
    const allTeams = [
        { number: 4145, name: "Sultans of Türkiye", country: "turkish", difficulty: "easy" },
        { number: 3646, name: "Robo-Tigers", country: "turkish", difficulty: "easy" },
        { number: 6025, name: "Adroit Androids", country: "turkish", difficulty: "medium" },
        { number: 6062, name: "Fiziksel Engelliler Takımı", country: "turkish", difficulty: "hard" },
        { number: 5635, name: "Robo-Vipers", country: "turkish", difficulty: "medium" },
        { number: 6942, name: "Tech-Anka", country: "turkish", difficulty: "medium" },
        { number: 8158, name: "Technos", country: "turkish", difficulty: "hard" },
        { number: 8171, name: "A-Team", country: "turkish", difficulty: "hard" },
        { number: 254, name: "The Cheesy Poofs", country: "global", difficulty: "easy" },
        { number: 1114, name: "Simbotics", country: "global", difficulty: "easy" },
        { number: 1678, name: "Citrus Circuits", country: "global", difficulty: "easy" },
        { number: 148, name: "Robowranglers", country: "global", difficulty: "easy" },
        { number: 971, name: "Spartan Robotics", country: "global", difficulty: "medium" },
        { number: 195, name: "CyberKnights", country: "global", difficulty: "medium" },
        { number: 1241, name: "Theoretically Possible", country: "global", difficulty: "hard" },
        { number: 1538, name: "The Holy Cows", country: "global", difficulty: "hard" },
        { number: 2056, name: "OP Robotics", country: "global", difficulty: "medium" }
    ];

    let availableTeams = [];
    let currentTeam = null;
    let score = 0;
    let timeLeft = 60;
    let timerInterval;

    function startGame() {
        const selectedCountry = countrySelect.value;
        const selectedDifficulty = difficultySelect.value;

        availableTeams = allTeams.filter(team => {
            const countryMatch = selectedCountry === 'all' || team.country === selectedCountry;
            const difficultyMatch = team.difficulty === selectedDifficulty;
            return countryMatch && difficultyMatch;
        });

        if (availableTeams.length === 0) {
            alert("Seçili kriterlere uygun takım bulunamadı. Lütfen farklı bir seçim yapın.");
            return;
        }

        gameOptions.classList.add('hidden');
        gameContainer.classList.remove('hidden');

        startTimer();
        loadNewTeam();
    }

    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = timeLeft;
            if (timeLeft <= 0) {
                endGame("Süren Doldu!");
            }
        }, 1000);
    }

    function loadNewTeam() {
        if (availableTeams.length === 0) {
            endGame("Tüm takımları tamamladın!");
            return;
        }

        const randomIndex = Math.floor(Math.random() * availableTeams.length);
        currentTeam = availableTeams.splice(randomIndex, 1)[0];

        teamNameDisplay.textContent = currentTeam.name;
        teamNumberInput.value = '';
        feedbackDisplay.textContent = '';
        teamNumberInput.focus();
    }

    function checkAnswer() {
        const inputNumber = parseInt(teamNumberInput.value, 10);

        if (isNaN(inputNumber)) {
            feedbackDisplay.textContent = 'Lütfen geçerli bir sayı girin!';
            feedbackDisplay.classList.remove('text-green-600');
            feedbackDisplay.classList.add('text-red-600');
            return;
        }

        if (inputNumber === currentTeam.number) {
            feedbackDisplay.textContent = 'Doğru!';
            feedbackDisplay.classList.remove('text-red-600');
            feedbackDisplay.classList.add('text-green-600');
            score += 10;
            scoreDisplay.textContent = score;
            loadNewTeam();
        } else {
            feedbackDisplay.textContent = "Yanlış! Doğru numara: " + currentTeam.number;
            feedbackDisplay.classList.remove('text-green-600');
            feedbackDisplay.classList.add('text-red-600');
            setTimeout(loadNewTeam, 1000);
        }
    }

    function skipQuestion() {
        feedbackDisplay.textContent = "Geçildi. Doğru numara: " + currentTeam.number;
        feedbackDisplay.classList.remove('text-green-600');
        feedbackDisplay.classList.add('text-red-600');
        setTimeout(loadNewTeam, 1000);
    }

    function endGame(message) {
        clearInterval(timerInterval);
        gameContainer.classList.add('hidden');
        resultsContainer.classList.remove('hidden');
        resultsContainer.querySelector('h2').textContent = message;
        finalScoreDisplay.textContent = score;
    }

    startButton.addEventListener('click', startGame);
    guessButton.addEventListener('click', checkAnswer);
    skipButton.addEventListener('click', skipQuestion);
    teamNumberInput.addEventListener('keyup', (event) => {
        if (event.key === 'Enter') {
            checkAnswer();
        }
    });

</script>
<?php require_once 'footer.php'?>

</body>
</html>