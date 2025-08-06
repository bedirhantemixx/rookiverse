<?php
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
// Session'ı başlat.
session_start();
header('Content-Type: application/json'); // Bu dosyanın JSON döndüreceğini belirtiyoruz

// --- GÜVENLİK VE KADEMELİ ENGELLEME ---
if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = 0; }
if (!isset($_SESSION['lockout_time'])) { $_SESSION['lockout_time'] = 0; }

// Kullanıcı kilitli mi diye kontrol et
$remaining_time = $_SESSION['lockout_time'] - time();
if ($remaining_time > 0) {
    // Eğer kilitli ise, hiçbir işlem yapma ve hata ile geri dön.
    echo json_encode(['status' => 'locked', 'time' => $remaining_time]);
    exit();
}

// Veritabanı bağlantısı
try {
    $pdo = get_db_connection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$team_number = $data['team_number'] ?? '';
$team_id = $data['team_id'] ?? '';
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM teams WHERE team_number = ? AND team_id_generated = ?");
$stmt->execute([$team_number, $team_id]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);

if ($team && password_verify($password, $team['password_hash'])) {
    // --- GİRİŞ BAŞARILI ---
    session_regenerate_id(true);
    $_SESSION['team_logged_in'] = true;
    $_SESSION['team_db_id'] = $team['id'];
    $_SESSION['team_number'] = $team['team_number'];
    unset($_SESSION['login_attempts'], $_SESSION['lockout_time']); // Sayaçları sıfırla
    echo json_encode(['status' => 'success', 'redirect_url' => 'team/panel.php']);
} else {
    // --- GİRİŞ BAŞARISIZ ---
    $_SESSION['login_attempts']++;
    $lock_time = 0;
    if ($_SESSION['login_attempts'] == 3) { $lock_time = 30; }
    elseif ($_SESSION['login_attempts'] == 4) { $lock_time = 60; }
    elseif ($_SESSION['login_attempts'] >= 5) { $lock_time = 90; }

    if ($lock_time > 0) {
        $_SESSION['lockout_time'] = time() + $lock_time;
        echo json_encode(['status' => 'locked', 'time' => $lock_time]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lütfen bilgilerinizi kontrol edin.']);
    }
}
?>