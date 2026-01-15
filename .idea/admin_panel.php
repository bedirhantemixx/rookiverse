<?php
session_start();
require 'db.php';

// Yetki Kontrolü
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') { header("Location: index.php"); exit; }

$msg = ""; 
$msgType = "";
$duzenle_modu = false;
$kullanici = [];
$mevcut_sorumlu_siniflar = [];

// --- 1. GÜNCELLEME İŞLEMİ ---
if (isset($_POST['islem']) && $_POST['islem'] == 'guncelle') {
    $id = $_POST['user_id'];
    $okul_no = trim($_POST['no']);
    $ad_soyad = mb_strtoupper(trim($_POST['ad']));
    $rol = $_POST['rol'];
    
    // Değişkenleri hazırla
    $sinif = $_POST['sinif'] ?? null;
    $sube = $_POST['sube'] ?? null;
    $alan = $_POST['alan'] ?? null;
    $program = $_POST['program_tipi'] ?? null;
    $brans = $_POST['brans'] ?? null;

    if ($sinif == 'Hazırlık') { $alan = 'KARIŞIK'; $program = ''; }

    try {
        $db->beginTransaction();

        // Kullanıcıyı Güncelle
        $sql = "UPDATE kullanicilar SET okul_no=?, ad_soyad=?, rol=?, sinif=?, sube=?, alan=?, program_tipi=?, brans=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$okul_no, $ad_soyad, $rol, $sinif, $sube, $alan, $program, $brans, $id]);

        // Eğer Müdür Yardımcısı ise Sorumlu Sınıfları Güncelle (Önce sil, sonra ekle)
        if ($rol == 'mudur_yrd') {
            $db->prepare("DELETE FROM sinif_yoneticileri WHERE mudur_yrd_id = ?")->execute([$id]);
            
            if (!empty($_POST['sorumlu_siniflar'])) {
                $stmt_sinif = $db->prepare("INSERT INTO sinif_yoneticileri (mudur_yrd_id, sinif_seviyesi) VALUES (?, ?)");
                foreach ($_POST['sorumlu_siniflar'] as $seviye) {
                    $stmt_sinif->execute([$id, $seviye]);
                }
            }
        }

        $db->commit();
        $msg = "Bilgiler başarıyla güncellendi."; $msgType = "success";
        // Modu sıfırla
        $duzenle_modu = false; 

    } catch (PDOException $e) {
        $db->rollBack();
        $msg = "Hata: Güncelleme yapılamadı. " . $e->getMessage(); $msgType = "danger";
    }
}

// --- 2. EKLEME İŞLEMİ ---
if (isset($_POST['islem']) && $_POST['islem'] == 'ekle') {
    $okul_no = trim($_POST['no']);
    $ad_soyad = mb_strtoupper(trim($_POST['ad']));
    $rol = $_POST['rol'];
    $sifre = '1234'; 

    $sinif = $_POST['sinif'] ?? null;
    $sube = $_POST['sube'] ?? null;
    $alan = $_POST['alan'] ?? null;
    $program = $_POST['program_tipi'] ?? null;
    $brans = $_POST['brans'] ?? null;

    if ($sinif == 'Hazırlık') { $alan = 'KARIŞIK'; $program = ''; }

    try {
        $db->beginTransaction();
        $sql = "INSERT INTO kullanicilar (okul_no, ad_soyad, rol, sifre, sinif, sube, alan, program_tipi, brans) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$okul_no, $ad_soyad, $rol, $sifre, $sinif, $sube, $alan, $program, $brans]);
        $yeni_user_id = $db->lastInsertId();

        if ($rol == 'mudur_yrd' && !empty($_POST['sorumlu_siniflar'])) {
            $stmt_sinif = $db->prepare("INSERT INTO sinif_yoneticileri (mudur_yrd_id, sinif_seviyesi) VALUES (?, ?)");
            foreach ($_POST['sorumlu_siniflar'] as $seviye) { $stmt_sinif->execute([$yeni_user_id, $seviye]); }
        }
        $db->commit();
        $msg = "Kullanıcı oluşturuldu. (Şifre: 1234)"; $msgType = "success";
    } catch (PDOException $e) {
        $db->rollBack();
        $msg = "Hata: Kullanıcı zaten var olabilir."; $msgType = "danger";
    }
}

// --- 3. DÜZENLEME MODUNU AÇMA ---
if (isset($_GET['duzenle'])) {
    $duzenle_modu = true;
    $id = $_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
    $stmt->execute([$id]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

    if($kullanici && $kullanici['rol'] == 'mudur_yrd') {
        $stmt2 = $db->prepare("SELECT sinif_seviyesi FROM sinif_yoneticileri WHERE mudur_yrd_id = ?");
        $stmt2->execute([$id]);
        $mevcut_sorumlu_siniflar = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    }
}

// SİLME İŞLEMİ
if (isset($_GET['sil'])) {
    $db->prepare("DELETE FROM kullanicilar WHERE id = ?")->execute([$_GET['sil']]);
    header("Location: admin_panel.php"); exit;
}

// LİSTELEME
$kullanicilar = $db->query("SELECT * FROM kullanicilar ORDER BY rol ASC, ad_soyad ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hidden-field { display: none; }
        .form-section { border: 1px solid #eee; padding: 15px; border-radius: 5px; margin-top: 10px; background: #fafafa; }
        .checkbox-group label { display: inline-block; margin-right: 15px; cursor: pointer; }
        
        /* Düzenleme Modu için Stil */
        .edit-mode-active { border: 2px solid var(--primary-gold); box-shadow: 0 0 10px rgba(212, 175, 55, 0.2); }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="brand">SİSTEM<span>YÖNETİCİSİ</span></div>
        <div class="user-menu"><a href="index.php" class="logout">Çıkış</a></div>
    </div>

    <div class="container">
        <?php if($msg) echo "<div class='alert alert-$msgType'>$msg</div>"; ?>
        
        <div class="card <?php echo $duzenle_modu ? 'edit-mode-active' : ''; ?>">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3><?php echo $duzenle_modu ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı Oluştur'; ?></h3>
                <?php if($duzenle_modu): ?>
                    <a href="admin_panel.php" class="btn" style="background:#666; padding:5px 10px; font-size:12px;">İptal</a>
                <?php endif; ?>
            </div>
            
            <form method="post">
                <input type="hidden" name="islem" value="<?php echo $duzenle_modu ? 'guncelle' : 'ekle'; ?>">
                <?php if($duzenle_modu): ?>
                    <input type="hidden" name="user_id" value="<?php echo $kullanici['id']; ?>">
                <?php endif; ?>
                
                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:1;">
                        <label>Rol Seçimi</label>
                        <select name="rol" id="rolSecimi" onchange="formDuzenle()" required>
                            <option value="">Seçiniz...</option>
                            <option value="ogrenci" <?php echo ($duzenle_modu && $kullanici['rol']=='ogrenci') ? 'selected' : ''; ?>>Öğrenci</option>
                            <option value="ogretmen" <?php echo ($duzenle_modu && $kullanici['rol']=='ogretmen') ? 'selected' : ''; ?>>Öğretmen</option>
                            <option value="mudur_yrd" <?php echo ($duzenle_modu && $kullanici['rol']=='mudur_yrd') ? 'selected' : ''; ?>>Müdür Yardımcısı</option>
                            <option value="admin" <?php echo ($duzenle_modu && $kullanici['rol']=='admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Okul No / T.C. / Kullanıcı Adı</label>
                        <input type="text" name="no" required value="<?php echo $duzenle_modu ? $kullanici['okul_no'] : ''; ?>">
                    </div>
                    <div style="flex:2;">
                        <label>Ad Soyad</label>
                        <input type="text" name="ad" required style="text-transform:uppercase;" value="<?php echo $duzenle_modu ? $kullanici['ad_soyad'] : ''; ?>">
                    </div>
                </div>

                <div id="ogrenci-alanlari" class="hidden-field form-section">
                    <h4 style="margin-top:0; color:var(--primary-gold);">Öğrenci Bilgileri</h4>
                    <div style="display:flex; gap:15px; flex-wrap:wrap;">
                        <div style="flex:1;">
                            <label>Sınıf</label>
                            <select name="sinif" id="sinifSecimi" onchange="sinifKontrol()">
                                <option value="Hazırlık" <?php echo ($duzenle_modu && $kullanici['sinif']=='Hazırlık') ? 'selected' : ''; ?>>Hazırlık</option>
                                <option value="9" <?php echo ($duzenle_modu && $kullanici['sinif']=='9') ? 'selected' : ''; ?>>9</option>
                                <option value="10" <?php echo ($duzenle_modu && $kullanici['sinif']=='10') ? 'selected' : ''; ?>>10</option>
                                <option value="11" <?php echo ($duzenle_modu && $kullanici['sinif']=='11') ? 'selected' : ''; ?>>11</option>
                                <option value="12" <?php echo ($duzenle_modu && $kullanici['sinif']=='12') ? 'selected' : ''; ?>>12</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label>Şube</label>
                            <select name="sube">
                                <?php foreach(['A','B','C','D','E'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo ($duzenle_modu && $kullanici['sube']==$s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div id="alan-program-div" style="display:flex; gap:15px; margin-top:10px;">
                        <div style="flex:2;">
                            <label>Alan</label>
                            <select name="alan">
                                <option value="BİLİŞİM TEKNOLOJİLERİ" <?php echo ($duzenle_modu && $kullanici['alan']=='BİLİŞİM TEKNOLOJİLERİ') ? 'selected' : ''; ?>>BİLİŞİM TEKNOLOJİLERİ</option>
                                <option value="ELEKTRİK ELEKTRONİK" <?php echo ($duzenle_modu && $kullanici['alan']=='ELEKTRİK ELEKTRONİK') ? 'selected' : ''; ?>>ELEKTRİK ELEKTRONİK</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label>Program Türü</label>
                            <select name="program_tipi">
                                <option value="AMP" <?php echo ($duzenle_modu && $kullanici['program_tipi']=='AMP') ? 'selected' : ''; ?>>AMP</option>
                                <option value="ATP" <?php echo ($duzenle_modu && $kullanici['program_tipi']=='ATP') ? 'selected' : ''; ?>>ATP</option>
                            </select>
                        </div>
                    </div>
                    <div id="hazirlik-info" class="hidden-field" style="color:#666; font-size:13px; margin-top:5px;">
                        * Hazırlık sınıfı için alan "KARIŞIK" olarak ayarlanacaktır.
                    </div>
                </div>

                <div id="ogretmen-alanlari" class="hidden-field form-section">
                    <h4 style="margin-top:0; color:var(--primary-gold);">Öğretmen Bilgileri</h4>
                    <label>Branş</label>
                    <input type="text" name="brans" value="<?php echo $duzenle_modu ? $kullanici['brans'] : ''; ?>" placeholder="Örn: Elektrik, Bilişim, Fizik...">
                </div>

                <div id="mudur-alanlari" class="hidden-field form-section">
                    <h4 style="margin-top:0; color:var(--primary-gold);">Sorumlu Olduğu Sınıf Seviyeleri</h4>
                    <div class="checkbox-group">
                        <?php 
                        $seviyeler = ['Hazırlık', '9', '10', '11', '12'];
                        foreach($seviyeler as $s): 
                            $checked = in_array($s, $mevcut_sorumlu_siniflar) ? 'checked' : '';
                        ?>
                            <label><input type="checkbox" name="sorumlu_siniflar[]" value="<?php echo $s; ?>" <?php echo $checked; ?>> <?php echo $s; ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn" style="margin-top:15px;">
                    <?php echo $duzenle_modu ? 'GÜNCELLE' : 'KAYDET'; ?>
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header"><h3>Kayıtlı Kullanıcılar</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>No / Kull. Adı</th>
                            <th>Ad Soyad</th>
                            <th>Detaylar</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($kullanicilar as $k): ?>
                        <tr style="<?php echo ($duzenle_modu && $k['id'] == $id) ? 'background:#fffde7;' : ''; ?>">
                            <td><span class="badge" style="background:#eee;"><?php echo $k['rol']; ?></span></td>
                            <td><?php echo $k['okul_no']; ?></td>
                            <td><?php echo $k['ad_soyad']; ?></td>
                            <td>
                                <?php 
                                if($k['rol'] == 'ogrenci') {
                                    echo $k['sinif']."-".$k['sube']." | ".$k['alan']." (".$k['program_tipi'].")";
                                } elseif($k['rol'] == 'ogretmen') {
                                    echo "Branş: " . $k['brans'];
                                } elseif($k['rol'] == 'mudur_yrd') {
                                    $stmt = $db->prepare("SELECT sinif_seviyesi FROM sinif_yoneticileri WHERE mudur_yrd_id = ?");
                                    $stmt->execute([$k['id']]);
                                    $siniflar = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                    echo "Sorumlu: " . implode(', ', $siniflar);
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="?duzenle=<?php echo $k['id']; ?>" class="btn-icon" style="color:blue; margin-right:10px;">✏️ Düzenle</a>
                                <a href="?sil=<?php echo $k['id']; ?>" class="btn-icon" style="color:red;" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">🗑️ Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function formDuzenle() {
            var rol = document.getElementById('rolSecimi').value;
            
            document.getElementById('ogrenci-alanlari').style.display = 'none';
            document.getElementById('ogretmen-alanlari').style.display = 'none';
            document.getElementById('mudur-alanlari').style.display = 'none';

            if (rol === 'ogrenci') {
                document.getElementById('ogrenci-alanlari').style.display = 'block';
                sinifKontrol();
            } else if (rol === 'ogretmen') {
                document.getElementById('ogretmen-alanlari').style.display = 'block';
            } else if (rol === 'mudur_yrd') {
                document.getElementById('mudur-alanlari').style.display = 'block';
            }
        }

        function sinifKontrol() {
            var sinif = document.getElementById('sinifSecimi').value;
            var alanDiv = document.getElementById('alan-program-div');
            var hazirlikInfo = document.getElementById('hazirlik-info');

            if (sinif === 'Hazırlık') {
                alanDiv.style.display = 'none';
                hazirlikInfo.style.display = 'block';
            } else {
                alanDiv.style.display = 'flex';
                hazirlikInfo.style.display = 'none';
            } 
        }

        // Sayfa yüklendiğinde düzenleme modu varsa formu ona göre aç
        <?php if($duzenle_modu): ?>
            window.onload = function() {
                formDuzenle();
            };
        <?php endif; ?>
    </script>
</body>
</html>