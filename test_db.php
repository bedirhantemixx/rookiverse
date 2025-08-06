<?php
// Hataları en başta gösterelim ki hiçbir şey kaçmasın
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Config Dosyası Kontrol Testi</h1>";
echo "<p>Bu test, çalışan kodun hangi ayarları kullandığını bize gösterecek.</p>";
echo "<p><b>Çalıştırılan Dosya Yolu:</b> " . __FILE__ . "</p>";

echo "<hr>";

// config.php dosyasını dahil etmeyi deneyelim
echo "<p>config.php dosyası aranıyor ve yükleniyor...</p>";
if (file_exists('config.php')) {
    require_once 'config.php';
    echo "<p style='color:green; font-weight:bold;'>config.php dosyası bulundu ve yüklendi.</p>";
} else {
    die("<p style='color:red; font-weight:bold;'>HATA: config.php dosyası bu dizinde bulunamadı!</p>");
}

echo "<hr>";

echo "<h2>Okunan `config.php` İçindeki Gerçek Ayarlar:</h2>";
echo "<ul style='font-family: monospace; font-size: 16px; background-color: #f0f0f0; padding: 15px; border: 1px solid #ccc;'>";
echo "<li><b>DB_HOST:</b> " . (defined('DB_HOST') ? DB_HOST : "<b style='color:red;'>TANIMLANMAMIŞ!</b>") . "</li>";
echo "<li><b>DB_NAME:</b> " . (defined('DB_NAME') ? DB_NAME : "<b style='color:red;'>TANIMLANMAMIŞ!</b>") . "</li>";
echo "<li><b>DB_USER:</b> " . (defined('DB_USER') ? DB_USER : "<b style='color:red;'>TANIMLANMAMIŞ!</b>") . "</li>";
echo "<li><b>DB_PASS (Şifre):</b> '" . (defined('DB_PASS') ? DB_PASS : "<b style='color:red;'>TANIMLANMAMIŞ!</b>") . "'</li>";
echo "</ul>";

echo "<hr>";

// Şimdi bu ayarlarla veritabanına bağlanmayı deneyelim
try {
    echo "<p>Veritabanına bağlanılıyor...</p>";
    $db = connectDB();
    echo "<p style='color:green; font-weight:bold;'>BAĞLANTI BAŞARILI! Sorun config dosyasında değil.</p>";
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>BAĞLANTI HATASI!</p>";
    echo "<p><b>Alınan Hata:</b> " . $e->getMessage() . "</p>";
}
?>