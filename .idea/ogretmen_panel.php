<?php
session_start();
require 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Güvenlik
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'ogretmen') { 
    header("Location: index.php"); exit; 
}

$user_id = $_SESSION['user']['id'];
$msg = "";
$msgType = "success"; 

// --- İŞLEMLER ---

// A. Proje Ekleme
if (isset($_POST['islem']) && $_POST['islem'] == 'proje_ekle') {
    $proje_adi = trim($_POST['proje_adi']);
    if(!empty($proje_adi)){
        $db->prepare("INSERT INTO projeler (proje_adi, ogretmen_id, aktif) VALUES (?, ?, 1)")->execute([$proje_adi, $user_id]);
        $msg = "Proje oluşturuldu.";
    }
}

// B. Proje Silme
if (isset($_GET['proje_sil'])) {
    $sil_id = $_GET['proje_sil'];
    $kontrol = $db->prepare("SELECT id FROM projeler WHERE id = ? AND ogretmen_id = ?");
    $kontrol->execute([$sil_id, $user_id]);
    
    if($kontrol->fetch()) {
        $db->prepare("UPDATE projeler SET aktif = 0 WHERE id = ?")->execute([$sil_id]);
        $db->prepare("DELETE FROM faaliyetler WHERE proje_id = ? AND durum != 'Onaylandı'")->execute([$sil_id]);
        $msg = "Proje arşivlendi.";
    } else {
        $msg = "Yetkisiz işlem."; $msgType = "danger";
    }
}

// C. SORUMLU ÖĞRENCİ EKLEME (Çoklu)
if (isset($_POST['islem']) && $_POST['islem'] == 'sorumlu_ata') {
    $ogr_no = trim($_POST['sorumlu_okul_no']);
    $proje_id = $_POST['sorumlu_proje_id'];

    $ogrBul = $db->prepare("SELECT id, ad_soyad FROM kullanicilar WHERE okul_no = ? AND rol = 'ogrenci'");
    $ogrBul->execute([$ogr_no]);
    $hedefOgr = $ogrBul->fetch(PDO::FETCH_ASSOC);

    if ($hedefOgr) {
        // Yetki Kontrolü
        $kontrol = $db->prepare("SELECT id FROM projeler WHERE id = ? AND ogretmen_id = ?");
        $kontrol->execute([$proje_id, $user_id]);
        
        if($kontrol->fetch()) {
            // Zaten ekli mi?
            $varMi = $db->prepare("SELECT id FROM proje_sorumlulari WHERE proje_id = ? AND ogrenci_id = ?");
            $varMi->execute([$proje_id, $hedefOgr['id']]);
            
            if($varMi->rowCount() > 0) {
                $msg = "Bu öğrenci zaten sorumlu listesinde."; $msgType = "warning";
            } else {
                $db->prepare("INSERT INTO proje_sorumlulari (proje_id, ogrenci_id) VALUES (?, ?)")->execute([$proje_id, $hedefOgr['id']]);
                $msg = $hedefOgr['ad_soyad'] . " sorumlu olarak eklendi.";
            }
        } else {
            $msg = "Bu projeye yetkiniz yok."; $msgType = "danger";
        }
    } else {
        $msg = "Öğrenci bulunamadı."; $msgType = "danger";
    }
}

// D. SORUMLU KALDIRMA
if (isset($_GET['sorumlu_sil']) && isset($_GET['pid'])) {
    $s_id = $_GET['sorumlu_sil'];
    $p_id = $_GET['pid'];
    
    // Yetki kontrolü (Sadece proje sahibi silebilir)
    $kontrol = $db->prepare("SELECT id FROM projeler WHERE id = ? AND ogretmen_id = ?");
    $kontrol->execute([$p_id, $user_id]);
    
    if($kontrol->fetch()) {
        $db->prepare("DELETE FROM proje_sorumlulari WHERE id = ?")->execute([$s_id]);
        $msg = "Sorumlu öğrenci listeden çıkarıldı.";
    }
    header("Location: ogretmen_panel.php"); exit;
}

// E. Davet Gönderme
if (isset($_POST['islem']) && $_POST['islem'] == 'davet_gonder') {
    $hedef_no = trim($_POST['hedef_okul_no']);
    $proje_id = $_POST['davet_proje_id'];

    $hocaBul = $db->prepare("SELECT id, ad_soyad FROM kullanicilar WHERE okul_no = ? AND rol = 'ogretmen'");
    $hocaBul->execute([$hedef_no]);
    $hedefHoca = $hocaBul->fetch(PDO::FETCH_ASSOC);

    if ($hedefHoca) {
        if ($hedefHoca['id'] == $user_id) { $msg = "Kendinize davet gönderemezsiniz."; $msgType = "warning"; } 
        else {
            $kontrol = $db->prepare("SELECT id FROM proje_davetleri WHERE proje_id = ? AND alici_id = ?");
            $kontrol->execute([$proje_id, $hedefHoca['id']]);
            if ($kontrol->rowCount() > 0) { $msg = "Zaten davet gönderilmiş."; $msgType = "warning"; } 
            else {
                $db->prepare("INSERT INTO proje_davetleri (proje_id, gonderen_id, alici_id) VALUES (?, ?, ?)")->execute([$proje_id, $user_id, $hedefHoca['id']]);
                $msg = "Davet gönderildi.";
            }
        }
    } else { $msg = "Öğretmen bulunamadı."; $msgType = "danger"; }
}

// F. Davet Cevap / G. Onay Red / H. Toplu İşlem (Aynı Kalıyor)
if (isset($_GET['davet_cevap']) && isset($_GET['davet_id'])) {
    $cevap = $_GET['davet_cevap']; $davet_id = $_GET['davet_id'];
    $chk = $db->prepare("SELECT id FROM proje_davetleri WHERE id = ? AND alici_id = ?");
    $chk->execute([$davet_id, $user_id]);
    if($chk->fetch()) {
        if($cevap == 'Reddedildi') { $db->prepare("DELETE FROM proje_davetleri WHERE id = ?")->execute([$davet_id]); } 
        else { $db->prepare("UPDATE proje_davetleri SET durum = 'Onaylandi' WHERE id = ?")->execute([$davet_id]); }
    }
    header("Location: ogretmen_panel.php"); exit;
}
if (isset($_GET['durum']) && isset($_GET['fid'])) {
    $db->prepare("UPDATE faaliyetler SET durum = ? WHERE id = ?")->execute([$_GET['durum'], $_GET['fid']]);
    $q = $_GET; unset($q['durum'], $q['fid']); header("Location: ogretmen_panel.php?" . http_build_query($q)); exit;
}
if (isset($_POST['toplu_islem']) && !empty($_POST['secilenler'])) {
    $yeni_durum = $_POST['toplu_islem']; $ids = implode(',', array_map('intval', $_POST['secilenler']));
    if($ids) { $db->query("UPDATE faaliyetler SET durum = '$yeni_durum' WHERE id IN ($ids)"); $msg = count($_POST['secilenler']) . " kayıt güncellendi."; }
}

// --- VERİ ÇEKME ---
// Projeleri Çek
$projelerim = $db->query("
    SELECT p.*, 'Sahibi' as rol_durumu 
    FROM projeler p WHERE p.ogretmen_id = $user_id AND p.aktif = 1
    UNION
    SELECT p.*, 'Ortak' as rol_durumu 
    FROM projeler p JOIN proje_davetleri d ON p.id = d.proje_id WHERE d.alici_id = $user_id AND d.durum = 'Onaylandi' AND p.aktif = 1
    ORDER BY id DESC
")->fetchAll();

// Her projenin sorumlularını çekip diziye ekle
foreach($projelerim as $key => $proje) {
    $sorumlular = $db->query("
        SELECT ps.id as kayit_id, k.ad_soyad 
        FROM proje_sorumlulari ps 
        JOIN kullanicilar k ON ps.ogrenci_id = k.id 
        WHERE ps.proje_id = {$proje['id']}
    ")->fetchAll(PDO::FETCH_ASSOC);
    $projelerim[$key]['sorumlular'] = $sorumlular;
}

$bekleyenDavetler = $db->query("SELECT d.id as davet_id, p.proje_adi, k.ad_soyad as gonderen_ad FROM proje_davetleri d JOIN projeler p ON d.proje_id = p.id JOIN kullanicilar k ON d.gonderen_id = k.id WHERE d.alici_id = $user_id AND d.durum = 'Bekliyor'")->fetchAll();

// Filtreleme
$projeIDs = []; foreach($projelerim as $pr) $projeIDs[] = $pr['id'];
$projeIDList = empty($projeIDs) ? '0' : implode(',', $projeIDs);
$where = "WHERE f.proje_id IN ($projeIDList)";
$params = [];
$buHaftaBas = date("Y-m-d", strtotime('monday this week'));
$buHaftaBit = date("Y-m-d", strtotime('sunday this week'));
$ozelFiltreVarMi = !empty($_GET['ara']) || !empty($_GET['sinif']) || !empty($_GET['proje']) || !empty($_GET['bas_tarih']);
$tumuIstendiMi = isset($_GET['tumunu_goster']);

if (!$ozelFiltreVarMi && !$tumuIstendiMi) { $where .= " AND f.faaliyet_tarihi BETWEEN ? AND ?"; $params[] = $buHaftaBas; $params[] = $buHaftaBit; $varsayilanMod = true; } 
else {
    $varsayilanMod = false;
    if (!empty($_GET['bas_tarih'])) { $where .= " AND f.faaliyet_tarihi >= ?"; $params[] = $_GET['bas_tarih']; }
    if (!empty($_GET['bit_tarih'])) { $where .= " AND f.faaliyet_tarihi <= ?"; $params[] = $_GET['bit_tarih']; }
}
if (!empty($_GET['ara'])) { $where .= " AND k.ad_soyad LIKE ?"; $params[] = "%".$_GET['ara']."%"; }
if (!empty($_GET['sinif'])) { $where .= " AND k.sinif = ?"; $params[] = $_GET['sinif']; }
if (!empty($_GET['proje'])) { $where .= " AND p.id = ?"; $params[] = $_GET['proje']; }

$sql = "SELECT f.id as fid, f.*, p.proje_adi, k.ad_soyad as ogr_ad, k.sinif, k.sube, k.okul_no
        FROM faaliyetler f JOIN projeler p ON f.proje_id = p.id JOIN kullanicilar k ON f.ogrenci_id = k.id
        $where ORDER BY f.faaliyet_tarihi DESC";
$talepler = $db->prepare($sql); $talepler->execute($params); $sonucListesi = $talepler->fetchAll();

$ciktiUyarisiVarMi = false;
foreach($sonucListesi as $row) { if(($row['cikti_alindi'] ?? 0) == 1 && $row['durum'] == 'Onaylandı') { $ciktiUyarisiVarMi = true; break; } }

function getActionUrl($fid, $durum) { $params = $_GET; $params['fid'] = $fid; $params['durum'] = $durum; return "?" . http_build_query($params); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğretmen Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        .filter-card-header { cursor: pointer; user-select: none; display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; transition: 0.2s; }
        .filter-card-header:hover { background-color: #f8fafc; }
        .filter-content { display: none; padding: 20px; border-top: 1px solid #e2e8f0; background: #fff; border-radius: 0 0 12px 12px; animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .toggle-icon { font-size: 16px; color: var(--gold); transition: transform 0.3s; }
        .rotate-icon { transform: rotate(180deg); }
        .row-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: var(--navy); vertical-align: middle; }
        .bulk-actions-bar { background-color: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 0 0 12px 12px; display: flex; justify-content: space-between; align-items: center; }
        .bulk-btn-group { display: flex; gap: 10px; }
        tr.selected-row { background-color: #f0f9ff !important; }
        
        /* GÜNCELLENMİŞ PROJE KARTI TASARIMI */
        .project-wrapper { 
            position: relative; 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 15px;
            transition: 0.2s;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .project-wrapper:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .project-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .project-actions {
            display: flex;
            gap: 5px;
        }

        .project-role-badge { 
            font-size: 10px; padding: 3px 8px; border-radius: 4px; font-weight: 700; text-transform: uppercase; 
            display: inline-block; width: fit-content;
        }
        .role-owner { background: var(--navy); color: white; }
        .role-partner { background: #10b981; color: white; }

        .btn-action-circle { 
            width: 32px; height: 32px; border-radius: 8px; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 14px; text-decoration: none; 
            transition: all 0.2s; 
            background: #f1f5f9; color: #475569;
        }
        .btn-del:hover { background: #fee2e2; color: #ef4444; }
        .btn-invite:hover { background: #dbeafe; color: #2563eb; }
        .btn-assign:hover { background: #fef3c7; color: #d97706; }

        /* SORUMLU LİSTESİ */
        .sorumlu-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 5px;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
        }
        .sorumlu-item {
            font-size: 11px; background: #fffbeb; color: #b45309; border: 1px solid #fcd34d;
            padding: 3px 8px; border-radius: 4px; display: flex; align-items: center; gap: 5px;
        }
        .sorumlu-remove { color: #ef4444; cursor: pointer; font-weight: bold; }
        .sorumlu-remove:hover { color: #b91c1c; }

        .project-card-link {
            text-decoration: none; color: #1e293b; font-weight: 600; font-size: 15px;
            display: flex; align-items: center; gap: 8px;
        }
        .project-card-link i { color: var(--gold); font-size: 18px; }

        .btn-pdf-custom { border: 2px solid #ef4444; color: #ef4444; background: transparent; height: 38px; padding: 0 20px; font-size: 12px; font-weight: 600; border-radius: 8px; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease; text-decoration: none; }
        .btn-pdf-custom:hover { background-color: #ef4444; color: #ffffff; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); }
        .deleted-project-tag { font-size: 10px; background: #fee2e2; color: #991b1b; padding: 2px 5px; border-radius: 4px; margin-left: 5px; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 30px; border: none; width: 100%; max-width: 450px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: modalSlide 0.3s ease-out; }
        @keyframes modalSlide { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .close-modal { color: #94a3b8; float: right; font-size: 24px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .close-modal:hover { color: #ef4444; }
        
        .notification-wrapper { position: relative; margin-right: 20px; cursor: pointer; }
        .notification-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 50%; border: 2px solid #fff; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
    </style>

    <script>
        function toggleFilter() {
            var content = document.getElementById('filterContent');
            var icon = document.getElementById('toggleIcon');
            if (content.style.display === "none" || content.style.display === "") {
                content.style.display = "block"; icon.classList.add("rotate-icon");
            } else {
                content.style.display = "none"; icon.classList.remove("rotate-icon");
            }
        }
        function pdfIndir(event) {
            var uyariVar = <?php echo $ciktiUyarisiVarMi ? 'true' : 'false'; ?>;
            var url = "pdf_indir.php?<?php echo http_build_query($_GET); ?>";
            if(uyariVar) { if(!confirm("DİKKAT: Listelediğiniz kayıtlarda daha önce PDF çıktısı aldığınız öğrenci faaliyetleri var. Devam edilsin mi?")) { event.preventDefault(); return; } }
            window.open(url, '_blank'); setTimeout(function(){ location.reload(); }, 3000);
        }
        function buHaftayiSec() { document.getElementById('basTarih').value = "<?php echo $buHaftaBas; ?>"; document.getElementById('bitTarih').value = "<?php echo $buHaftaBit; ?>"; var content = document.getElementById('filterContent'); if(content.style.display === 'none') toggleFilter(); }
        function tumunuSec(source) { var checkboxes = document.querySelectorAll('input[name="secilenler[]"]'); for(var i=0; i<checkboxes.length; i++) { checkboxes[i].checked = source.checked; var row = checkboxes[i].closest('tr'); if(source.checked) row.classList.add('selected-row'); else row.classList.remove('selected-row'); } }
        function satirSec(source) { var row = source.closest('tr'); if(source.checked) row.classList.add('selected-row'); else row.classList.remove('selected-row'); }

        // MODAL FONKSİYONLARI
        function modalKapat(modalId) { document.getElementById(modalId).style.display = "none"; }
        window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.style.display = "none"; } }

        function davetEt(projeId, projeAdi) {
            document.getElementById('davetModal').style.display = "block";
            document.getElementById('davetProjeAdi').innerText = projeAdi;
            document.getElementById('davet_proje_id').value = projeId;
        }

        // SORUMLU ATA MODAL AÇ
        function sorumluAta(projeId, projeAdi) {
            document.getElementById('sorumluModal').style.display = "block";
            document.getElementById('sorumluProjeAdi').innerText = projeAdi;
            document.getElementById('sorumlu_proje_id').value = projeId;
        }
    </script>
</head>
<body>

   <div class="navbar">
        <div class="nav-left">
            <img src="logo.png" alt="Logo" class="nav-logo">
            <div class="admin-signature-block">
                <div class="sig-name"><?php echo $_SESSION['user']['ad_soyad']; ?></div>
                <div class="sig-role"><?php echo !empty($_SESSION['user']['brans']) ? mb_strtoupper($_SESSION['user']['brans']) : 'ÖĞRETMEN'; ?></div>
            </div>
        </div>
        <div class="nav-right">
            <?php if(count($bekleyenDavetler) > 0): ?>
            <div class="notification-wrapper" title="Bekleyen Davetler">
                <i class="fa-regular fa-bell" style="font-size:22px; color:var(--text-light);"></i>
                <span class="notification-badge"><?php echo count($bekleyenDavetler); ?></span>
            </div>
            <?php endif; ?>
            <div class="nav-actions">
                <a href="ogretmen_panel.php" class="nav-icon" data-tooltip="Ana Sayfa"><i class="fa-solid fa-house"></i></a>
                <a href="ogretmen_ayarlar.php" class="nav-icon" data-tooltip="Ayarlar"><i class="fa-solid fa-gear"></i></a>
            </div>
            <div class="user-profile-box">
                <div class="profile-img"><?php echo mb_substr($_SESSION['user']['ad_soyad'], 0, 1); ?></div>
                <div class="user-info"><span class="u-name"><?php echo $_SESSION['user']['ad_soyad']; ?></span><span class="u-role">Öğretmen</span></div>
                <a href="index.php" class="logout-btn-sm" title="Güvenli Çıkış"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if(count($bekleyenDavetler) > 0): ?>
        <div class="card" style="border-left: 4px solid #2563eb; background:#eff6ff;">
            <div class="card-header" style="border:none; padding-bottom:10px;"><h3 style="color:#1e40af; font-size:16px;"><i class="fa-solid fa-envelope-open-text"></i> Proje Davetleriniz Var!</h3></div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <?php foreach($bekleyenDavetler as $davet): ?>
                <div style="background:white; padding:15px 20px; border-radius:12px; border:1px solid #bfdbfe; display:flex; justify-content:space-between; align-items:center;">
                    <div style="color:#1e293b;"><span><?php echo $davet['gonderen_ad']; ?></span> sizi <strong style="color:var(--gold);"><?php echo $davet['proje_adi']; ?></strong> projesine davet etti.</div>
                    <div style="display:flex; gap:10px;">
                        <a href="?davet_cevap=Onaylandi&davet_id=<?php echo $davet['davet_id']; ?>" class="btn" style="height:32px; font-size:12px; background:#10b981; padding:0 20px;">KABUL ET</a>
                        <a href="?davet_cevap=Reddedildi&davet_id=<?php echo $davet['davet_id']; ?>" class="btn" style="height:32px; font-size:12px; background:#ef4444; padding:0 20px;">REDDET</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($msg): ?>
            <div class="card" style="padding:15px; display:flex; align-items:center; gap:12px; border-radius:12px; <?php echo ($msgType=='danger') ? 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;' : (($msgType=='warning') ? 'background:#fef3c7; color:#92400e; border:1px solid #fcd34d;' : 'background:#dcfce7; color:#166534; border:1px solid #bbf7d0;'); ?>">
                <i class="fa-solid <?php echo ($msgType=='success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>" style="font-size:18px;"></i> 
                <span style="font-weight:500;"><?php echo $msg; ?></span>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-rocket"></i> Proje Yönetimi</h3></div>
            <form method="post" class="form-row" style="margin-bottom: 25px;">
                <input type="hidden" name="islem" value="proje_ekle">
                <input type="text" name="proje_adi" placeholder="Yeni Proje Adı Giriniz (Örn: FRC 2026 Robotik)" required style="flex:1;">
                <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> OLUŞTUR</button>
            </form>
            
            <h4 style="margin:0 0 20px 0; color:#64748b; font-size:12px; text-transform:uppercase; border-bottom:1px solid #eee; padding-bottom:10px;">
                <i class="fa-regular fa-folder-open"></i> Projeleriniz
            </h4>
            
            <div class="project-grid">
                <?php foreach($projelerim as $p): ?>
                    <div class="project-wrapper">
                        
                        <div class="project-header">
                            <div class="project-info">
                                <span class="project-role-badge <?php echo ($p['rol_durumu']=='Sahibi') ? 'role-owner' : 'role-partner'; ?>">
                                    <?php echo $p['rol_durumu']; ?>
                                </span>
                                <a href="proje_detay.php?id=<?php echo $p['id']; ?>" class="project-card-link">
                                    <i class="fa-solid fa-folder"></i>
                                    <span><?php echo $p['proje_adi']; ?></span>
                                </a>
                            </div>

                            <div class="project-actions">
                                <?php if($p['rol_durumu'] == 'Sahibi'): ?>
                                    <a href="javascript:void(0)" onclick="sorumluAta(<?php echo $p['id']; ?>, '<?php echo addslashes($p['proje_adi']); ?>')" class="btn-action-circle btn-assign" title="Sorumlu Öğrenci Ekle">
                                        <i class="fa-solid fa-user-graduate"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="davetEt(<?php echo $p['id']; ?>, '<?php echo addslashes($p['proje_adi']); ?>')" class="btn-action-circle btn-invite" title="Öğretmen Davet Et">
                                        <i class="fa-solid fa-user-plus"></i>
                                    </a>
                                    <a href="?proje_sil=<?php echo $p['id']; ?>" class="btn-action-circle btn-del" onclick="return confirm('UYARI: Projeyi arşivlemek üzeresiniz. Devam edilsin mi?');" title="Projeyi Arşivle">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if(!empty($p['sorumlular'])): ?>
                            <div class="sorumlu-list">
                                <?php foreach($p['sorumlular'] as $sorumlu): ?>
                                    <div class="sorumlu-item">
                                        <i class="fa-solid fa-user-graduate"></i> <?php echo $sorumlu['ad_soyad']; ?>
                                        <?php if($p['rol_durumu'] == 'Sahibi'): ?>
                                            <a href="?sorumlu_sil=<?php echo $sorumlu['kayit_id']; ?>&pid=<?php echo $p['id']; ?>" class="sorumlu-remove" title="Kaldır">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="border-left: 5px solid var(--gold); padding:0; overflow:hidden;">
            <div class="card-header filter-card-header" onclick="toggleFilter()" style="margin:0; border:none;">
                <div style="display:flex; align-items:center; gap:10px;"><i class="fa-solid fa-filter" style="color:var(--gold);"></i><h3 style="margin:0; font-size:15px; color:#475569;">Detaylı Faaliyet Arama</h3></div>
                <i id="toggleIcon" class="fa-solid fa-chevron-down toggle-icon"></i>
            </div>
            <div id="filterContent" class="filter-content">
                <div style="display:flex; justify-content:flex-end; margin-bottom:15px;">
                    <a href="#" onclick="pdfIndir(event)" class="btn-pdf-custom"><i class="fa-solid fa-file-pdf"></i> PDF RAPOR AL (Seçilenleri Yazdır)</a>
                </div>
                <form method="get" id="filtreForm" class="form-row">
                    <div style="flex:1; min-width: 130px;" class="date-wrapper"><input type="date" id="basTarih" name="bas_tarih" value="<?php echo $_GET['bas_tarih']??''; ?>"></div>
                    <div style="flex:1; min-width: 130px;" class="date-wrapper"><input type="date" id="bitTarih" name="bit_tarih" value="<?php echo $_GET['bit_tarih']??''; ?>"></div>
                    <button type="button" onclick="buHaftayiSec()" class="btn" style="background:#3b82f6; width:auto; padding:0 20px; font-size:12px;">BU HAFTA</button>
                    <input type="text" name="ara" value="<?php echo $_GET['ara']??''; ?>" placeholder="Öğrenci Adı..." style="flex:2;">
                    <select name="sinif" style="flex:1;"><option value="">Tüm Sınıflar</option><option>Hazırlık</option><option>9</option><option>10</option><option>11</option><option>12</option></select>
                    <select name="proje" style="flex:1;"><option value="">Tüm Projeler</option><?php foreach($projelerim as $p): ?><option value="<?php echo $p['id']; ?>" <?php echo ($_GET['proje']??'')==$p['id']?'selected':''; ?>><?php echo $p['proje_adi']; ?></option><?php endforeach; ?></select>
                    <button type="submit" class="btn btn-outline" style="min-width: 100px;">ARA</button>
                    <a href="ogretmen_panel.php" class="btn" style="background:#cbd5e1; width:50px; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-rotate-left"></i></a>
                </form>
            </div>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <div class="card-header" style="padding: 20px 25px; border-bottom:1px solid #e2e8f0; margin:0;">
                <h3><i class="fa-solid fa-list-check"></i> Faaliyet Talepleri</h3>
                <div style="margin-left:auto; display:flex; align-items:center; gap:10px;">
                    <?php if($varsayilanMod): ?>
                        <span class="badge badge-warning" style="font-weight:400; font-size:11px; padding:6px 12px;"><i class="fa-regular fa-clock"></i> Sadece Bu Hafta</span>
                        <a href="?tumunu_goster=1" class="btn" style="height:30px; font-size:11px; padding:0 15px; background:#64748b;">Tüm Geçmişi Göster</a>
                    <?php else: ?><span class="badge badge-gray"><i class="fa-solid fa-filter"></i> Filtreli Sonuçlar</span><?php endif; ?>
                    <span class="badge badge-gray">Toplam: <?php echo count($sonucListesi); ?></span>
                </div>
            </div>
            <form method="post">
                <div class="table-responsive">
                    <table style="margin:0;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="width: 40px; text-align: center;"><input type="checkbox" class="row-checkbox" onclick="tumunuSec(this)"></th>
                                <th>ÖĞRENCİ</th><th>PROJE</th><th>TARİH</th><th width="30%">AÇIKLAMA</th><th>DURUM</th><th>İŞLEM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(count($sonucListesi) == 0) echo "<tr><td colspan='7' style='text-align:center; padding:40px; color:#94a3b8;'><i class='fa-regular fa-folder-open' style='font-size:24px; display:block; margin-bottom:10px;'></i>Kayıt bulunamadı.</td></tr>";
                            foreach($sonucListesi as $t): 
                                $badgeClass = 'badge-warning'; $iconClass = 'fa-clock';
                                if($t['durum']=='Onaylandı') { $badgeClass = 'badge-success'; $iconClass = 'fa-check'; }
                                if($t['durum']=='Reddedildi') { $badgeClass = 'badge-danger'; $iconClass = 'fa-xmark'; }
                                $isPrinted = ($t['cikti_alindi'] ?? 0) == 1;
                            ?>
                            <tr style="<?php echo $isPrinted ? 'background-color:#f8fafc;' : ''; ?>" class="<?php echo $isPrinted ? 'printed-row' : ''; ?>">
                                <td style="text-align: center;"><input type="checkbox" name="secilenler[]" value="<?php echo $t['fid']; ?>" class="row-checkbox" onclick="satirSec(this)"></td>
                                <td><div style="font-weight:700; color:#1e293b;"><?php echo $t['ogr_ad']; ?></div><div style="font-size:11px; color:#64748b;"><?php echo $t['sinif']."-".$t['sube']; ?></div>
                                <?php if($isPrinted): ?><span style="font-size:10px; color:#64748b; background:#e2e8f0; padding:2px 6px; border-radius:4px; display:inline-flex; align-items:center; gap:3px; margin-top:2px;"><i class="fa-solid fa-print"></i> Yazdırıldı</span><?php endif; ?></td>
                                <td><span style="font-weight:600; color:var(--gold);"><?php echo $t['proje_adi']; ?></span><?php if(($t['proje_aktif'] ?? 1) == 0): ?><span class="deleted-project-tag"><i class="fa-solid fa-archive"></i> Arşiv</span><?php endif; ?></td>
                                <td><div style="display:flex; align-items:center; gap:5px; color:#475569;"><i class="fa-regular fa-calendar"></i> <?php echo date("d.m.Y", strtotime($t['faaliyet_tarihi'])); ?></div></td>
                                <td style="color:#475569; line-height:1.4;"><?php echo htmlspecialchars($t['aciklama']); ?></td>
                                <td><span class="badge <?php echo $badgeClass; ?>"><i class="fa-solid <?php echo $iconClass; ?>"></i> <?php echo $t['durum']; ?></span></td>
                                <td><?php if($t['durum'] == 'Bekliyor'): ?><div style="display:flex; gap:8px;"><a href="<?php echo getActionUrl($t['fid'], 'Onaylandı'); ?>" class="action-btn btn-approve" title="Onayla"><i class="fa-solid fa-check"></i></a><a href="<?php echo getActionUrl($t['fid'], 'Reddedildi'); ?>" class="action-btn btn-reject" title="Reddet"><i class="fa-solid fa-xmark"></i></a></div><?php else: ?><div style="color:#cbd5e1; font-size:12px;"><i class="fa-solid fa-lock"></i> İşlem Tamam</div><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bulk-actions-bar">
                    <div class="bulk-info"><i class="fa-solid fa-arrow-turn-up" style="transform: rotate(90deg);"></i> Seçilenler İçin Toplu İşlem:</div>
                    <div class="bulk-btn-group">
                        <button type="submit" name="toplu_islem" value="Reddedildi" class="btn" style="height:38px; padding:0 20px; font-size:12px; background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;"><i class="fa-solid fa-xmark"></i> REDDET</button>
                        <button type="submit" name="toplu_islem" value="Onaylandı" class="btn" style="height:38px; padding:0 20px; font-size:12px; background:#dcfce7; color:#166534; border:1px solid #86efac;"><i class="fa-solid fa-check"></i> ONAYLA</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="davetModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="modalKapat('davetModal')">&times;</span>
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:60px; height:60px; background:#eff6ff; color:#2563eb; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 15px auto;"><i class="fa-solid fa-user-plus"></i></div>
                <h3 style="margin:0; color:var(--navy);">Öğretmen Davet Et</h3>
                <p style="color:#64748b; font-size:14px; margin-top:5px;"><strong style="color:var(--gold);" id="davetProjeAdi"></strong> projesine ortak etmek istediğiniz öğretmenin bilgilerini giriniz.</p>
            </div>
            <form method="post">
                <input type="hidden" name="islem" value="davet_gonder">
                <input type="hidden" name="davet_proje_id" id="davet_proje_id">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Öğretmen Okul No / Kullanıcı Adı</label>
                <div style="position:relative;"><i class="fa-solid fa-user" style="position:absolute; left:15px; top:14px; color:#94a3b8;"></i><input type="text" name="hedef_okul_no" required placeholder="Örn: Fatih Hoca" style="width:100%; box-sizing:border-box; padding-left:40px;"></div>
                <button type="submit" class="btn" style="width:100%; margin-top:20px; height:45px;">DAVET GÖNDER</button>
            </form>
        </div>
    </div>

    <div id="sorumluModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="modalKapat('sorumluModal')">&times;</span>
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:60px; height:60px; background:#fef3c7; color:#d97706; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 15px auto;"><i class="fa-solid fa-user-graduate"></i></div>
                <h3 style="margin:0; color:var(--navy);">Sorumlu Öğrenci Ata</h3>
                <p style="color:#64748b; font-size:14px; margin-top:5px;"><strong style="color:var(--gold);" id="sorumluProjeAdi"></strong> projesi için bir öğrenciyi sorumlu olarak atayın.</p>
            </div>
            <form method="post">
                <input type="hidden" name="islem" value="sorumlu_ata">
                <input type="hidden" name="sorumlu_proje_id" id="sorumlu_proje_id">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#475569;">Öğrenci Okul No</label>
                <div style="position:relative;"><i class="fa-solid fa-id-card" style="position:absolute; left:15px; top:14px; color:#94a3b8;"></i><input type="text" name="sorumlu_okul_no" required placeholder="Örn: 1054" style="width:100%; box-sizing:border-box; padding-left:40px;"></div>
                <button type="submit" class="btn" style="width:100%; margin-top:20px; height:45px; background:#d97706;">ÖĞRENCİYİ EKLE</button>
            </form>
        </div>
    </div>
    
    <?php if(!empty($_GET['ara']) || !empty($_GET['sinif']) || !empty($_GET['proje']) || !empty($_GET['bas_tarih'])): ?>
    <script> document.getElementById('filterContent').style.display = 'block'; document.getElementById('toggleIcon').classList.add('rotate-icon'); </script>
    <?php endif; ?>
</body>
</html>