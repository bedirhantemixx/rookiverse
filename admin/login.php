<?php
session_start();
require_once 'config.php';

// Hata mesajı için boş bir değişken tanımla
$error_message = null;

// Form gönderilmişse (kullanıcı "Giriş Yap" butonuna bastıysa)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $pdo = get_db_connection();

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // Kullanıcı bulunduysa VE şifre doğruysa
    if ($admin && password_verify($password, $admin['password_hash'])) {
        // GİRİŞ BAŞARILI
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: panel.php"); // Ana panele yönlendir
        exit();
    } else {
        // GİRİŞ BAŞARISIZ
        $error_message = "Kullanıcı adı veya şifre yanlış.";
    }
}