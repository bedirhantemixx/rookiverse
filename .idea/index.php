<?php
session_start();
require 'db.php';

if ($_POST) {
    $no = trim($_POST['no']);
    $sifre = $_POST['sifre'];
    
    $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE okul_no = ? AND sifre = ?");
    $sorgu->execute([$no, $sifre]);
    $user = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user'] = $user;
        switch ($user['rol']) {
            case 'admin': header("Location: admin_panel.php"); break;
            case 'mudur_yrd': header("Location: mudur_yrd_panel.php"); break;
            case 'ogretmen': header("Location: ogretmen_panel.php"); break;
            case 'ogrenci': header("Location: ogrenci_panel.php"); break;
        }
        exit;
    } else {
        $hata = "Giriş bilgileri hatalı.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kurumsal Giriş - Maçka MTAL</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-wrapper">
    <div class="login-box">
        <div class="school-header">
<img src="logo.png" alt="YTÜ Maçka MTAL" class="school-logo-lg" style="width:80px;">
        </div>

        <div class="login-logo" style="font-size:20px;">Faaliyet<span> Takip Sistemi</span></div>
        
        <?php if(isset($hata)): ?>
            <div class="alert alert-danger"><?php echo $hata; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div style="text-align:left;">
                <label>Okul / Kurum No</label>
                <input type="text" name="no" required autocomplete="off">
            </div>
            
            <div style="text-align:left;">
                <label>Şifre</label>
                <input type="password" name="sifre" required>
            </div>
            
            <button type="submit" class="btn" style="width:100%;">SİSTEME GİRİŞ</button>
        </form>
        
        <div style="margin-top:30px; border-top:1px solid #eee; padding-top:15px; text-align: center;">
            <div style="font-size:10px; color:#999; text-transform:uppercase; letter-spacing:1px;">Sistem Yöneticisi</div>
            <div style="font-weight:700; color:var(--text-dark); font-size:16px; margin:3px 0;">YTÜ MAÇKA MTAL </div>
            <div style="font-size:12px; color:var(--primary-gold); font-weight:600;">Yıldız Teknik Üniversitesi Maçka Mesleki ve Teknik Anadolu Lisesi</div>
        </div>
    </div>
</body>
</html>