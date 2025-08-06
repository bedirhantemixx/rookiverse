
<?php
// Session'ı başlat. Bu, sayfanın en başında olmalı.
session_start();
require_once 'config.php'; // Ayar dosyasını dahil et

// Hata mesajı için boş bir değişken tanımla
$error_message = null;

// Form gönderilmişse (kullanıcı "Giriş Yap" butonuna bastıysa)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $pdo = connectDB(); // Veritabanına bağlan

    // Formdan gelen verileri al
    $team_number = $_POST['team_number'];
    $team_id_generated = $_POST['team_id'];
    $password = $_POST['password'];

    // Veritabanında takımı ara
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_number = ? AND team_id_generated = ?");
    $stmt->execute([$team_number, $team_id_generated]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    // Takım bulunduysa VE şifre doğruysa
    if ($team && password_verify($password, $team['password_hash'])) {
        // GİRİŞ BAŞARILI
        session_regenerate_id(true); // Güvenlik için session ID'sini yenile
        $_SESSION['team_logged_in'] = true;
        $_SESSION['team_db_id'] = $team['id'];
        $_SESSION['team_number'] = $team['team_number'];
        
        // Takımı kendi paneline yönlendir
        header("Location: team/panel.php");
        exit();
    } else {
        // GİRİŞ BAŞARISIZ
        $error_message = "Hatalı giriş. Lütfen bilgilerinizi kontrol edin.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Takım Paneli Girişi - FRC Rookieverse</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background-color: #f7f7f7; }
    @font-face { font-family: "Sakana"; src: url("assets/fonts/Sakana.ttf") format("truetype"); }
    .rookieverse { font-family: "Sakana", sans-serif !important; font-weight: bold; font-size: 2.5rem; color: #E5AE32; text-decoration: none; }
    .password-wrapper { position: relative; }
    .password-toggle-icon { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; color: #6b7280; }
  </style>
</head>
<body class="flex items-center justify-center min-h-screen">

  <div class="w-full max-w-md p-4">
    <div class="flex justify-center mb-8">
        <a href="index.php"><span class="rookieverse">FRC ROOKIEVERSE</span></a>
    </div>

    <div class="bg-white border-2 border-gray-200 rounded-xl shadow-lg p-8">
      <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Takım Paneli Girişi</h1>
      <p class="text-center text-gray-500 mb-6">Panele erişmek için bilgilerinizi girin.</p>
      
      <?php if ($error_message): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
        <strong class="font-bold">Hata!</strong>
        <span class="block sm:inline"><?php echo $error_message; ?></span>
      </div>
      <?php endif; ?>

      <form action="team-login.php" method="POST" class="space-y-6">
        <div><label for="team-number" class="font-medium text-gray-700">Takım Numarası</label><input id="team-number" name="team_number" type="number" required class="mt-2 w-full px-4 py-2.5 border rounded-lg"></div>
        <div><label for="team-id" class="font-medium text-gray-700">Takım ID</label><input id="team-id" name="team_id" type="text" required maxlength="6" class="mt-2 w-full px-4 py-2.5 border rounded-lg"></div>
        <div><label for="password" class="font-medium text-gray-700">Şifre</label><div class="password-wrapper"><input id="password" name="password" type="password" required class="mt-2 w-full px-4 py-2.5 border rounded-lg"><span id="password-toggle" class="password-toggle-icon"><i data-lucide="eye"></i></span></div></div>
        <div><button type="submit" class="w-full bg-[#E5AE32] hover:bg-[#c4952b] text-white font-bold py-3 text-lg rounded-lg">Giriş Yap</button></div>
      </form>
    </div>
  </div>

  <script>
    lucide.createIcons();
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    passwordToggle.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        passwordToggle.querySelector('i').setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
        lucide.createIcons();
    });
  </script>

</body>
</html>