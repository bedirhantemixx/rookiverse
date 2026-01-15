<?php
session_start();
require 'db.php';

// Güvenlik Kontrolü
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'ogretmen') { 
    header("Location: index.php"); exit; 
}

$user = $_SESSION['user'];
$msg = "";
$msgType = "";

// ŞİFRE DEĞİŞTİRME İŞLEMİ
if ($_POST) {
    $eski_sifre = $_POST['eski_sifre'];
    $yeni_sifre = $_POST['yeni_sifre'];
    $yeni_tekrar = $_POST['yeni_tekrar'];

    // 1. Eski şifre doğru mu?
    $stmt = $db->prepare("SELECT sifre FROM kullanicilar WHERE id = ?");
    $stmt->execute([$user['id']]);
    $mevcut = $stmt->fetchColumn();

    if ($mevcut != $eski_sifre) {
        $msg = "Hata: Eski şifrenizi yanlış girdiniz.";
        $msgType = "danger"; 
    } elseif ($yeni_sifre != $yeni_tekrar) {
        $msg = "Hata: Yeni şifreler birbiriyle uyuşmuyor.";
        $msgType = "danger";
    } elseif (strlen($yeni_sifre) < 4) {
        $msg = "Hata: Yeni şifre en az 4 karakter olmalıdır.";
        $msgType = "danger";
    } else {
        $update = $db->prepare("UPDATE kullanicilar SET sifre = ? WHERE id = ?");
        $update->execute([$yeni_sifre, $user['id']]);
        
        $msg = "Başarılı: Şifreniz güncellendi.";
        $msgType = "success"; 
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ayarlar - Öğretmen Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
</head>
<body>

   <div class="navbar">
        <div class="nav-left">
            <img src="logo.png" alt="Logo" class="nav-logo">
            <div class="admin-signature-block">
                <div class="sig-name"><?php echo $user['ad_soyad']; ?></div>
                <div class="sig-role">
                    <?php echo !empty($user['brans']) ? mb_strtoupper($user['brans']) : 'ÖĞRETMEN'; ?>
                </div>
            </div>
        </div>

        <div class="nav-right">
            <div class="nav-actions">
                <a href="ogretmen_panel.php" class="nav-icon" data-tooltip="Ana Sayfa"><i class="fa-solid fa-house"></i></a>
                <a href="ogretmen_ayarlar.php" class="nav-icon" style="background:var(--navy); color:white;" data-tooltip="Ayarlar"><i class="fa-solid fa-gear"></i></a>
            </div>

            <div class="user-profile-box">
                <div class="profile-img">
                    <?php echo mb_substr($user['ad_soyad'], 0, 1); ?>
                </div>
                <div class="user-info">
                    <span class="u-name"><?php echo $user['ad_soyad']; ?></span>
                    <span class="u-role">Öğretmen</span>
                </div>
                <a href="index.php" class="logout-btn-sm" title="Güvenli Çıkış"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 800px;">
        
        <div style="margin-bottom: 20px; display:flex; align-items:center; gap:10px;">
            <a href="ogretmen_panel.php" class="btn btn-outline" style="width:40px; height:40px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:50%;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 style="margin:0; color:var(--navy);">Hesap Ayarları</h2>
        </div>

        <?php if($msg): ?>
            <div class="card" style="padding:15px; margin-bottom:20px; display:flex; align-items:center; gap:10px; 
                <?php echo ($msgType=='success') ? 'background:#dcfce7; color:#166534; border:1px solid #bbf7d0;' : 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;'; ?>">
                <i class="fa-solid <?php echo ($msgType=='success') ? 'fa-check-circle' : 'fa-circle-exclamation'; ?>"></i> 
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-id-card"></i> Profil Bilgileri</h3>
            </div>
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <div style="flex:1; min-width:200px;">
                    <label style="font-size:12px; color:#64748b; font-weight:600; display:block; margin-bottom:5px;">AD SOYAD</label>
                    <div style="padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-weight:600; color:#334155;">
                        <?php echo $user['ad_soyad']; ?>
                    </div>
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-size:12px; color:#64748b; font-weight:600; display:block; margin-bottom:5px;">KULLANICI ADI / NO</label>
                    <div style="padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-weight:600; color:#334155;">
                        <?php echo $user['okul_no']; ?>
                    </div>
                </div>
            </div>
            <div style="margin-top:15px;">
                <label style="font-size:12px; color:#64748b; font-weight:600; display:block; margin-bottom:5px;">BRANŞ / GÖREV</label>
                <div style="padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-weight:600; color:#334155;">
                    <?php echo !empty($user['brans']) ? $user['brans'] : 'Branş Belirtilmemiş (Öğretmen)'; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-lock"></i> Şifre Değiştir</h3>
            </div>
            <form method="post">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#475569;">Eski Şifreniz</label>
                    <input type="password" name="eski_sifre" required placeholder="Mevcut şifrenizi girin">
                </div>
                
                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#475569;">Yeni Şifre</label>
                        <input type="password" name="yeni_sifre" required placeholder="En az 4 karakter">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#475569;">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="yeni_tekrar" required placeholder="Yeni şifreyi doğrulayın">
                    </div>
                </div>

                <div style="text-align:right;">
                    <button type="submit" class="btn" style="width:auto; padding:0 40px;">
                        <i class="fa-solid fa-save"></i> GÜNCELLE
                    </button>
                </div>
            </form>
        </div>

    </div>
</body>
</html>