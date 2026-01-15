<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['rol'], ['admin', 'super_admin'])) { header("Location: index.php"); exit; }

$msg = "";
$user_id = $_SESSION['user']['id'];

// Şifre Değiştirme
if ($_POST) {
    $yeni_sifre = $_POST['sifre'];
    if(!empty($yeni_sifre)) {
        $db->prepare("UPDATE ogrenciler SET sifre = ? WHERE id = ?")->execute([$yeni_sifre, $user_id]);
        $_SESSION['user']['sifre'] = $yeni_sifre;
        $msg = "Şifreniz başarıyla güncellendi.";
    }
}

// Sorumlu Olduğu Sınıfları Çek
$sorumlu_q = $db->prepare("SELECT sinif_seviyesi FROM sinif_yoneticileri WHERE yonetici_id = ?");
$sorumlu_q->execute([$user_id]);
$siniflar = $sorumlu_q->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ayarlarım</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">Kişisel<span>Ayarlar</span></div>
        <div class="user-menu"><a href="ogretmen_panel.php">PANELE DÖN</a></div>
    </div>

    <div class="container" style="max-width:600px;">
        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="card">
            <div class="card-header"><h3>Profil Bilgileri</h3></div>
            
            <div style="margin-bottom:20px; padding:15px; background:#f9f9f9; border-radius:5px;">
                <label>Sorumlu Olduğunuz Sınıflar</label>
                <div style="font-size:18px; font-weight:bold; color:var(--primary-gold);">
                    <?php echo empty($siniflar) ? "Genel Yönetici (Tümü)" : implode(', ', $siniflar) . ". Sınıflar"; ?>
                </div>
                <div style="font-size:12px; color:#666;">* Sınıf atamalarını değiştirmek için Alan Şefi ile görüşünüz.</div>
            </div>

            <form method="post">
                <label>Ad Soyad</label>
                <input type="text" value="<?php echo $_SESSION['user']['ad_soyad']; ?>" disabled style="background:#eee;">
                
                <label>Yeni Şifre Belirle</label>
                <input type="text" name="sifre" value="<?php echo $_SESSION['user']['sifre']; ?>">
                
                <button type="submit" class="btn">GÜNCELLE</button>
            </form>
        </div>
    </div>
</body>
</html>