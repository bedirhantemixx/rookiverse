<?php
session_start();
require 'db.php';
// Güvenlik: Sadece Müdür Yardımcısı girebilir
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'mudur_yrd') { header("Location: index.php"); exit; }

// Bağlı olduğu sınıfları bul
$uid = $_SESSION['user']['id'];
$siniflar = $db->query("SELECT sinif_seviyesi FROM sinif_yoneticileri WHERE mudur_yrd_id = $uid")->fetchAll(PDO::FETCH_COLUMN);

// Eğer sınıfı varsa sorguyu hazırla
if ($siniflar) {
    $sinifList = implode("','", $siniflar); // '9','10' gibi
    // DİKKAT: aciklama sütunu SEÇİLMİYOR! Gizlilik için. Sadece ONAYLI olanlar.
    $sql = "
        SELECT f.faaliyet_tarihi, f.sure_tipi, p.proje_adi, k.ad_soyad, k.sinif, k.sube, ogretmen.ad_soyad as sorumlu_hoca
        FROM faaliyetler f
        JOIN projeler p ON f.proje_id = p.id
        JOIN kullanicilar k ON f.ogrenci_id = k.id
        JOIN kullanicilar ogretmen ON p.ogretmen_id = ogretmen.id
        WHERE f.durum = 'Onaylandı' 
        AND k.sinif IN ('$sinifList')
        ORDER BY f.faaliyet_tarihi DESC
    ";
    $kayitlar = $db->query($sql)->fetchAll();
} else {
    $kayitlar = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Müdür Yrd. Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
</head>
<body>

   <div class="navbar">
        <div class="nav-left">
            <img src="logo.png" alt="Logo" class="nav-logo">
            
            <div class="admin-signature-block">
                <div class="sig-name" style="font-size:16px; color:var(--navy);"><?php echo $_SESSION['user']['ad_soyad']; ?></div>
                <div class="sig-role" style="font-size:12px;">MÜDÜR YARDIMCISI</div>
            </div>
        </div>

        <div class="nav-right">
            <div class="user-profile-box">
                <div class="profile-img">
                    <?php echo mb_substr($_SESSION['user']['ad_soyad'], 0, 1); ?>
                </div>
                <div class="user-info">
                    <span class="u-name"><?php echo $_SESSION['user']['ad_soyad']; ?></span>
                    <span class="u-role">Yönetici</span>
                </div>
                <a href="index.php" class="logout-btn-sm" title="Güvenli Çıkış"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </div>

    <div class="container">
        
        <div class="card" style="border-left: 4px solid var(--gold);">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3><i class="fa-solid fa-file-shield"></i> Onaylı Öğrenci Listesi (Gizli Mod)</h3>
                
                <a href="pdf_indir.php" target="_blank" class="btn btn-outline" style="height:40px; font-size:13px; color:#ef4444; border-color:#ef4444;">
                    <i class="fa-solid fa-file-pdf"></i> PDF RAPOR AL
                </a>
            </div>
            
            <div style="background:#f0f9ff; color:#0369a1; padding:15px; border-radius:8px; border:1px solid #b3e5fc; display:flex; gap:10px; align-items:center;">
                <i class="fa-solid fa-circle-info" style="font-size:20px;"></i>
                <div>
                    <strong>Bilgilendirme:</strong> Bu ekranda sadece sorumlu olduğunuz sınıfların (<?php echo implode(', ', $siniflar); ?>) <b>Onaylanmış</b> faaliyetleri görünür. 
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-list"></i> Faaliyet Kayıtları</h3>
                <span class="badge badge-gray" style="margin-left:auto;">Toplam: <?php echo count($kayitlar); ?> Kayıt</span>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>TARİH</th>
                            <th>ÖĞRENCİ</th>
                            <th>SINIF</th>
                            <th>PROJE / GÖREV</th>
                            <th>SORUMLU ÖĞRETMEN</th>
                            <th>SÜRE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(count($kayitlar) == 0) {
                            echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#999;'>Sorumlu olduğunuz sınıflarda henüz onaylanmış faaliyet yok.</td></tr>";
                        }
                        foreach($kayitlar as $k): 
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:5px; font-weight:500; color:#475569;">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?php echo date("d.m.Y", strtotime($k['faaliyet_tarihi'])); ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight:700; color:#1e293b;"><?php echo $k['ad_soyad']; ?></span>
                            </td>
                            <td>
                                <span class="badge badge-gray"><?php echo $k['sinif']."-".$k['sube']; ?></span>
                            </td>
                            <td>
                                <span style="font-weight:600; color:var(--gold);">
                                    <i class="fa-regular fa-folder-open"></i> <?php echo $k['proje_adi']; ?>
                                </span>
                            </td>
                            <td><?php echo $k['sorumlu_hoca']; ?></td>
                            <td>
                                <span class="badge badge-success"><i class="fa-solid fa-clock"></i> <?php echo $k['sure_tipi']; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>