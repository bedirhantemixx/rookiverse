<?php
session_start();
require 'db.php';

// Güvenlik ve ID Kontrolü
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'ogretmen') { header("Location: index.php"); exit; }
if (!isset($_GET['id']) || empty($_GET['id'])) { header("Location: ogretmen_panel.php"); exit; }

$proje_id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

// 1. PROJE BİLGİSİNİ VE YETKİYİ KONTROL ET
// Projeyi çek
$stmt = $db->prepare("SELECT * FROM projeler WHERE id = ?");
$stmt->execute([$proje_id]);
$proje = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proje) { echo "Proje bulunamadı."; exit; }

// YETKİ KONTROLÜ (Sahibi mi VEYA Ortak mı?)
$is_sahibi = ($proje['ogretmen_id'] == $user_id);
$is_ortak = false;

if (!$is_sahibi) {
    // Sahibi değilse, onaylanmış bir daveti var mı diye bak
    $davet_kontrol = $db->prepare("SELECT id FROM proje_davetleri WHERE proje_id = ? AND alici_id = ? AND durum = 'Onaylandi'");
    $davet_kontrol->execute([$proje_id, $user_id]);
    if ($davet_kontrol->fetch()) {
        $is_ortak = true;
    }
}

// Eğer ne sahibi ne de ortak ise erişimi engelle
if (!$is_sahibi && !$is_ortak) {
    echo "<div style='padding:50px; text-align:center; font-family:sans-serif;'>
            <h2 style='color:red;'>Yetkisiz Erişim!</h2>
            <p>Bu projeyi görüntüleme yetkiniz yok veya davetiniz onaylanmamış.</p>
            <a href='ogretmen_panel.php'>Panele Dön</a>
          </div>";
    exit;
}

// Rolü belirle
$kullanici_rolu = $is_sahibi ? "Proje Sahibi" : "Ortak Öğretmen";
$rol_rengi = $is_sahibi ? "background:var(--navy);" : "background:#10b981;";

// 2. İstatistikler
$toplam_faaliyet = $db->query("SELECT COUNT(*) FROM faaliyetler WHERE proje_id = $proje_id")->fetchColumn();
$onayli_sayi = $db->query("SELECT COUNT(*) FROM faaliyetler WHERE proje_id = $proje_id AND durum = 'Onaylandı'")->fetchColumn();
$ogrenci_sayisi = $db->query("SELECT COUNT(DISTINCT ogrenci_id) FROM faaliyetler WHERE proje_id = $proje_id")->fetchColumn();

// 3. Faaliyetleri Çek
$sql = "SELECT f.*, k.ad_soyad, k.sinif, k.sube, k.okul_no 
        FROM faaliyetler f 
        JOIN kullanicilar k ON f.ogrenci_id = k.id 
        WHERE f.proje_id = ? 
        ORDER BY f.faaliyet_tarihi DESC";
$faaliyetler = $db->prepare($sql);
$faaliyetler->execute([$proje_id]);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?php echo $proje['proje_adi']; ?> - Detaylar</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
</head>
<body>

   <div class="navbar">
        <div class="nav-left">
            <img src="logo.png" alt="Logo" class="nav-logo">
            <div class="admin-signature-block">
                <div class="sig-name"><?php echo $_SESSION['user']['ad_soyad']; ?></div>
                <div class="sig-role">ÖĞRETMEN / ALAN ŞEFİ</div>
            </div>
        </div>
        <div class="nav-right">
            <div class="nav-actions">
                <a href="ogretmen_panel.php" class="nav-icon" data-tooltip="Ana Sayfa"><i class="fa-solid fa-house"></i></a>
                <a href="ogretmen_ayarlar.php" class="nav-icon" data-tooltip="Ayarlar"><i class="fa-solid fa-gear"></i></a>
            </div>
            <div class="user-profile-box">
                <div class="profile-img"><?php echo mb_substr($_SESSION['user']['ad_soyad'], 0, 1); ?></div>
                <div class="user-info">
                    <span class="u-name"><?php echo $_SESSION['user']['ad_soyad']; ?></span>
                    <span class="u-role">Öğretmen</span>
                </div>
                <a href="index.php" class="logout-btn-sm" title="Güvenli Çıkış"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </div>

    <div class="container">
        
        <div style="margin-bottom:20px;">
            <a href="ogretmen_panel.php" class="btn btn-outline" style="height:35px; font-size:12px; display:inline-flex; align-items:center; gap:5px; margin-bottom:15px; border-radius:30px;">
                <i class="fa-solid fa-arrow-left"></i> Panele Dön
            </a>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="margin:0; color:var(--navy); font-size:24px; display:flex; align-items:center; gap:10px;">
                        <i class="fa-solid fa-folder-open" style="color:var(--gold);"></i> 
                        <?php echo $proje['proje_adi']; ?>
                        
                        <span style="font-size:11px; color:white; padding:4px 10px; border-radius:20px; vertical-align:middle; <?php echo $rol_rengi; ?>">
                            <?php echo $kullanici_rolu; ?>
                        </span>
                    </h2>
                    <p style="margin:5px 0 0 0; color:#64748b;">Proje Faaliyet ve Performans Raporu</p>
                </div>
                <button class="btn btn-outline" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Sayfayı Yazdır
                </button>
            </div>
        </div>

        <div style="display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div class="stat-box">
                <div class="stat-icon" style="background:#dbeafe; color:#2563eb;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <h4>Görevli Öğrenci</h4>
                    <p><?php echo $ogrenci_sayisi; ?></p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:#dcfce7; color:#166534;">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h4>Onaylanan Faaliyet</h4>
                    <p><?php echo $onayli_sayi; ?></p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon" style="background:#f3f4f6; color:#4b5563;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div class="stat-info">
                    <h4>Toplam Kayıt</h4>
                    <p><?php echo $toplam_faaliyet; ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-clipboard-list"></i> Faaliyet Hareketleri</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Öğrenci</th>
                            <th>Sınıf</th>
                            <th width="40%">Açıklama</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($faaliyetler as $f): ?>
                        <tr>
                            <td><?php echo date("d.m.Y", strtotime($f['faaliyet_tarihi'])); ?></td>
                            <td>
                                <b><?php echo $f['ad_soyad']; ?></b><br>
                                <small style="color:#999;"><?php echo $f['okul_no']; ?></small>
                            </td>
                            <td><?php echo $f['sinif']."-".$f['sube']; ?></td>
                            <td><?php echo htmlspecialchars($f['aciklama']); ?></td>
                            <td>
                                <?php 
                                    $renk = 'badge-warning';
                                    $ikon = 'fa-clock';
                                    
                                    if($f['durum']=='Onaylandı') { $renk = 'badge-success'; $ikon = 'fa-check'; }
                                    if($f['durum']=='Reddedildi') { $renk = 'badge-danger'; $ikon = 'fa-xmark'; }
                                    
                                    echo "<span class='badge $renk'><i class='fa-solid $ikon'></i> ".$f['durum']."</span>";
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if($faaliyetler->rowCount() == 0): ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">Henüz bu projeye ait kayıt yok.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>