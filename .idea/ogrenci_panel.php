<?php
session_start();
require 'db.php';

// Hata Raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Güvenlik
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'ogrenci') { header("Location: index.php"); exit; }

$user_id = $_SESSION['user']['id'];
$user_name = $_SESSION['user']['ad_soyad'];
$msg = "";
$msgType = "success";

// --- AJAX İŞLEMİ: ÖĞRENCİ BUL VE MASKELE ---
if (isset($_POST['islem']) && $_POST['islem'] == 'ogrenci_bul') {
    $okul_no = trim($_POST['okul_no']);
    
    if($okul_no == $_SESSION['user']['okul_no']) {
        echo json_encode(['status' => 'error', 'message' => 'Kendinizi ekleyemezsiniz.']); exit;
    }

    $stmt = $db->prepare("SELECT id, ad_soyad FROM kullanicilar WHERE okul_no = ? AND rol = 'ogrenci'");
    $stmt->execute([$okul_no]);
    $ogr = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ogr) {
        // İsim Maskeleme
        $parcalar = explode(" ", $ogr['ad_soyad']);
        $maskeliad = "";
        foreach($parcalar as $index => $parca) {
            $len = mb_strlen($parca, 'UTF-8');
            if ($index == count($parcalar) - 1) {
                if($len > 2) $maskeliad .= mb_substr($parca, 0, 1, 'UTF-8') . str_repeat("*", $len - 2) . mb_substr($parca, $len - 1, 1, 'UTF-8');
                else $maskeliad .= $parca;
            } else {
                $maskeliad .= mb_substr($parca, 0, 1, 'UTF-8') . str_repeat("*", $len - 1) . " ";
            }
        }
        echo json_encode(['status' => 'success', 'id' => $ogr['id'], 'ad_soyad' => $maskeliad]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Öğrenci bulunamadı.']);
    }
    exit;
}

// --- KAYIT İŞLEMİ ---
$bugun = date("Y-m-d");
$dortGunOnce = date("Y-m-d", strtotime("-4 days"));

if (isset($_POST['islem']) && $_POST['islem'] == 'talep_olustur') {
    $basTarihStr = $_POST['bas_tarih'];
    $bitTarihStr = $_POST['bit_tarih'];
    $proje_id = $_POST['proje_id'];
    $aciklama = $_POST['aciklama'];
    
    $hedef_ogrenciler = isset($_POST['ekip_uyeleri']) ? $_POST['ekip_uyeleri'] : [];
    if(!in_array($user_id, $hedef_ogrenciler)) array_push($hedef_ogrenciler, $user_id);

    $basTs = strtotime($basTarihStr);
    $bitTs = strtotime($bitTarihStr);
    $gunFarki = ($bitTs - $basTs) / (60 * 60 * 24) + 1; 

    if ($basTarihStr > $bugun || $bitTarihStr > $bugun) {
        $msg = "Hata: İleri bir tarihe faaliyet girişi yapamazsınız."; $msgType = "danger";
    } elseif ($basTarihStr < $dortGunOnce) {
        $msg = "Hata: 4 günden daha eski bir tarihe giriş yapamazsınız."; $msgType = "danger";
    } elseif (count($hedef_ogrenciler) > 1 && $gunFarki > 3) {
        $msg = "Hata: Toplu giriş yaparken en fazla 3 günlük kayıt girebilirsiniz."; $msgType = "danger";
    } else {
        
        // Sorumlu Yetki Kontrolü (YENİ TABLOYA GÖRE)
        $isSorumlu = false;
        if(count($hedef_ogrenciler) > 1) {
            $sorumluKontrol = $db->prepare("SELECT id FROM proje_sorumlulari WHERE proje_id = ? AND ogrenci_id = ?");
            $sorumluKontrol->execute([$proje_id, $user_id]);
            if($sorumluKontrol->fetch()) { $isSorumlu = true; } 
            else { $msg = "Hata: Bu proje için toplu giriş yetkiniz yok."; $msgType = "danger"; }
        } else { $isSorumlu = true; }

        if($isSorumlu && empty($msg)) {
            $bas = new DateTime($basTarihStr);
            $bit = new DateTime($bitTarihStr);
            
            foreach($hedef_ogrenciler as $ogrenciID) {
                $loopBas = clone $bas;
                $kayitAciklama = $aciklama;
                if($ogrenciID != $user_id) { $kayitAciklama .= " (Sorumlu $user_name tarafından atandı)"; } 
                elseif ($isSorumlu && count($hedef_ogrenciler) > 1) { $kayitAciklama .= " (Ekip adına toplu giriş)"; }

                while ($loopBas <= $bit) {
                    $curr = $loopBas->format('Y-m-d');
                    if ($curr <= $bugun && $curr >= $dortGunOnce) {
                        if ($loopBas->format('N') < 6) { 
                            $db->prepare("INSERT INTO faaliyetler (ogrenci_id, proje_id, faaliyet_tarihi, sure_tipi, aciklama, durum) VALUES (?, ?, ?, ?, ?, 'Bekliyor')")
                               ->execute([$ogrenciID, $proje_id, $curr, $_POST['sure'], $kayitAciklama]);
                        }
                    }
                    $loopBas->modify('+1 day');
                }
            }
            $msg = "Faaliyet talepleriniz başarıyla iletildi."; $msgType = "success";
        }
    }
}

// PROJELERİ ÇEK (YENİ TABLOYA GÖRE DÜZELTİLDİ)
// proje_sorumlulari tablosunu kontrol ederek 'sorumlu_mu' alanını 1 veya 0 yapıyoruz.
$projeler = $db->query("
    SELECT p.id, p.proje_adi, k.ad_soyad as hoca,
           (SELECT COUNT(*) FROM proje_sorumlulari ps WHERE ps.proje_id = p.id AND ps.ogrenci_id = $user_id) as sorumlu_mu
    FROM projeler p 
    JOIN kullanicilar k ON p.ogretmen_id = k.id
    WHERE p.aktif = 1
")->fetchAll();

function maskele($isim) {
    $parcalar = explode(' ', $isim); $maskeli = "";
    foreach($parcalar as $p) { $maskeli .= mb_substr($p, 0, 1, "UTF-8") . str_repeat("*", mb_strlen($p, "UTF-8")-1) . " "; }
    return trim($maskeli);
}

$gecmisTalepler = $db->prepare("SELECT f.*, p.proje_adi FROM faaliyetler f JOIN projeler p ON f.proje_id = p.id WHERE f.ogrenci_id = ? ORDER BY f.faaliyet_tarihi DESC LIMIT 5");
$gecmisTalepler->execute([$user_id]);
$talepler = $gecmisTalepler->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    
    <style>
        .team-selection-box { display: none; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .input-group { display: flex; gap: 10px; margin-bottom: 10px; }
        .add-input { flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .add-btn { background: var(--navy); color: white; border: none; padding: 0 20px; border-radius: 6px; cursor: pointer; }
        #eklenenOgrenciler { display: flex; flex-wrap: wrap; gap: 10px; }
        .student-tag { background: white; border: 1px solid #bfdbfe; color: #1e40af; padding: 8px 12px; border-radius: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .remove-tag { color: #ef4444; cursor: pointer; font-weight: bold; }
        .remove-tag:hover { color: #b91c1c; }
    </style>

    <script>
        function projeDegisti() {
            var select = document.getElementById('projeSecim');
            var selectedOption = select.options[select.selectedIndex];
            var isSorumlu = selectedOption.getAttribute('data-sorumlu'); // 1 veya 0
            
            var infoBox = document.getElementById('sorumluInfo');
            var teamBox = document.getElementById('teamBox');

            // Eğer sorumluysa kutuları aç (1 ise true)
            if(isSorumlu == "1") {
                infoBox.style.display = "block";
                teamBox.style.display = "block";
            } else {
                infoBox.style.display = "none";
                teamBox.style.display = "none";
                document.getElementById('eklenenOgrenciler').innerHTML = ''; // Temizle
            }
        }

        function ogrenciEkle() {
            var okulNo = document.getElementById('okulNoInput').value;
            if(okulNo === "") { alert("Lütfen okul numarası giriniz."); return; }

            var formData = new FormData();
            formData.append('islem', 'ogrenci_bul');
            formData.append('okul_no', okulNo);

            fetch('ogrenci_panel.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    if(document.getElementById('st_' + data.id)) { alert("Zaten listede."); return; }
                    var tag = document.createElement('div');
                    tag.className = 'student-tag';
                    tag.id = 'st_' + data.id;
                    tag.innerHTML = `<i class="fa-solid fa-user"></i> ${data.ad_soyad} (${okulNo})<input type="hidden" name="ekip_uyeleri[]" value="${data.id}"><i class="fa-solid fa-xmark remove-tag" onclick="removeStudent('${data.id}')"></i>`;
                    document.getElementById('eklenenOgrenciler').appendChild(tag);
                    document.getElementById('okulNoInput').value = '';
                } else { alert(data.message); }
            });
        }

        function removeStudent(id) { document.getElementById('st_' + id).remove(); }

        function onayModalAc() {
            var select = document.getElementById('projeSecim');
            if(select.value === "") { alert("Lütfen bir proje seçiniz."); return; }
            
            var selectedOption = select.options[select.selectedIndex];
            var hocaAdi = selectedOption.getAttribute('data-hoca'); 
            var projeAdi = selectedOption.text;
            var basTarih = document.getElementById('basTarih').value;
            var bitTarih = document.getElementById('bitTarih').value;
            var aciklama = document.getElementById('aciklama').value;
            
            if(basTarih === "" || bitTarih === "") { alert("Lütfen tarih aralığı seçin."); return; }
            if(aciklama.trim() === "") { alert("Lütfen açıklama giriniz."); return; }

            var bugun = new Date("<?php echo $bugun; ?>");
            var dortGunOnce = new Date("<?php echo $dortGunOnce; ?>");
            var secilenBas = new Date(basTarih);
            var secilenBit = new Date(bitTarih);
            var dayDiff = (secilenBit - secilenBas) / (1000 * 3600 * 24) + 1;
            var ekipSayisi = document.querySelectorAll('input[name="ekip_uyeleri[]"]').length;

            bugun.setHours(0,0,0,0); dortGunOnce.setHours(0,0,0,0); secilenBas.setHours(0,0,0,0); secilenBit.setHours(0,0,0,0);

            if (secilenBas > bugun || secilenBit > bugun) { alert("HATA: Gelecek tarih seçilemez."); return; }
            if (secilenBas < dortGunOnce) { alert("HATA: 4 günden eski tarih seçilemez."); return; }
            if (ekipSayisi > 0 && dayDiff > 3) { alert("⚠️ UYARI: Toplu girişte en fazla 3 GÜN seçilebilir."); return; }

            var kisiMesaji = (ekipSayisi > 0) ? ("\n• İşlem: Siz + " + ekipSayisi + " Öğrenci") : "\n• İşlem: Sadece Siz";
            if(confirm("📌 ÖZET:\n• Proje: " + projeAdi + kisiMesaji + "\n• Gün: " + dayDiff + "\n\nOnaylıyor musunuz?")) { document.getElementById('talepForm').submit(); }
        }
    </script>
</head>
<body>

   <div class="navbar">
        <div class="nav-left">
            <img src="logo.png" alt="Logo" class="nav-logo">
            <div class="admin-signature-block">
                <div class="sig-name"><?php echo $user_name; ?></div>
                <div class="sig-role">ÖĞRENCİ</div>
            </div>
        </div>
        <div class="nav-right">
            <div class="user-profile-box">
                <div class="profile-img" style="background:#e0f2fe; color:#0284c7;"><?php echo mb_substr($user_name, 0, 1); ?></div>
                <div class="user-info"><span class="u-name"><?php echo $user_name; ?></span><span class="u-role">Öğrenci</span></div>
                <a href="index.php" class="logout-btn-sm" title="Güvenli Çıkış"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 900px;">
        <?php if($msg): ?>
            <div class="card" style="padding:15px; display:flex; align-items:center; gap:10px; margin-bottom:20px;
                <?php echo ($msgType=='danger') ? 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;' : 'background:#dcfce7; color:#166534; border:1px solid #bbf7d0;'; ?>">
                <i class="fa-solid <?php echo ($msgType=='danger') ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-pen-to-square"></i> Faaliyet Talebi Oluştur</h3></div>
            <div style="background:#fff7ed; border:1px solid #ffedd5; padding:15px; border-radius:8px; margin-bottom:20px; font-size:13px; color:#9a3412;">
                <i class="fa-solid fa-triangle-exclamation"></i> <strong>Kural:</strong> Sadece <b>BUGÜN</b> ve geriye dönük en fazla <b>4 GÜN</b> öncesine faaliyet girebilirsiniz.
            </div>

            <form id="talepForm" method="post">
                <input type="hidden" name="islem" value="talep_olustur">
                
                <div style="margin-bottom:15px;">
                    <label style="font-weight:600; color:#475569; display:block; margin-bottom:5px;">Proje Seçimi</label>
                    <select name="proje_id" id="projeSecim" required style="width:100%; height:45px;" onchange="projeDegisti()">
                        <option value="">-- Bir Proje Seçiniz --</option>
                        <?php foreach($projeler as $p): ?>
                            <option value="<?php echo $p['id']; ?>" data-hoca="<?php echo maskele($p['hoca']); ?>" data-sorumlu="<?php echo $p['sorumlu_mu']; ?>">
                                <?php echo $p['proje_adi']; ?> <?php echo ($p['sorumlu_mu'] > 0) ? '⭐ (Sorumlu)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="sorumluInfo" style="display:none; background:#ecfdf5; border:1px solid #6ee7b7; color:#065f46; padding:12px; border-radius:8px; margin-bottom:15px; font-size:13px;">
                    <i class="fa-solid fa-user-graduate"></i> <strong>Sorumlu Öğrenci Yetkisi:</strong> Okul numarası girerek diğer öğrencileri ekleyebilirsiniz.
                </div>

                <div id="teamBox" class="team-selection-box">
                    <label style="font-weight:600; color:#0369a1; display:block; margin-bottom:5px;">
                        <i class="fa-solid fa-user-plus"></i> Diğer Öğrencileri Ekle
                    </label>
                    <div class="input-group">
                        <input type="text" id="okulNoInput" class="add-input" placeholder="Öğrenci Okul No Giriniz (Örn: 1453)">
                        <button type="button" class="add-btn" onclick="ogrenciEkle()">EKLE</button>
                    </div>
                    <div id="eklenenOgrenciler"></div>
                </div>

                <div style="display:flex; gap:20px; margin-bottom:15px;">
                    <div style="flex:1;">
                        <label style="font-weight:600; color:#475569; display:block; margin-bottom:5px;">Başlangıç Tarihi</label>
                        <input type="date" name="bas_tarih" id="basTarih" required max="<?php echo $bugun; ?>" min="<?php echo $dortGunOnce; ?>" style="width:100%; height:45px;">
                    </div>
                    <div style="flex:1;">
                        <label style="font-weight:600; color:#475569; display:block; margin-bottom:5px;">Bitiş Tarihi</label>
                        <input type="date" name="bit_tarih" id="bitTarih" required max="<?php echo $bugun; ?>" min="<?php echo $dortGunOnce; ?>" style="width:100%; height:45px;">
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:600; color:#475569; display:block; margin-bottom:5px;">Süre Tipi</label>
                    <div style="display:flex; gap:15px;">
                        <label style="cursor:pointer; display:flex; align-items:center; gap:5px; background:#f8fafc; padding:10px 20px; border:1px solid #e2e8f0; border-radius:8px; flex:1;"><input type="radio" name="sure" value="Tam Gün" checked> Tam Gün</label>
                        <label style="cursor:pointer; display:flex; align-items:center; gap:5px; background:#f8fafc; padding:10px 20px; border:1px solid #e2e8f0; border-radius:8px; flex:1;"><input type="radio" name="sure" value="Yarım Gün"> Yarım Gün</label>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-weight:600; color:#475569; display:block; margin-bottom:5px;">Yapılan İşin Açıklaması</label>
                    <textarea name="aciklama" id="aciklama" rows="4" required placeholder="Bugün projede neler yaptığınızı kısaca anlatınız..." style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit;"></textarea>
                </div>

                <button type="button" class="btn" onclick="onayModalAc()" style="width:100%; height:50px; font-size:16px;">
                    <i class="fa-solid fa-paper-plane"></i> TALEBİ GÖNDER
                </button>
            </form>
        </div>

        <div class="card" style="margin-top:30px;">
            <div class="card-header"><h3><i class="fa-solid fa-clock-rotate-left"></i> Son Hareketleriniz</h3></div>
            <div class="table-responsive">
                <table style="width:100%;">
                    <thead><tr><th align="left">Tarih</th><th align="left">Proje</th><th align="left">Durum</th></tr></thead>
                    <tbody>
                        <?php 
                        if(count($talepler) == 0) echo "<tr><td colspan='3' style='text-align:center; color:#999; padding:20px;'>Henüz kayıt yok.</td></tr>";
                        foreach($talepler as $t): 
                            $renk = 'badge-warning'; $ikon='fa-clock';
                            if($t['durum']=='Onaylandı') { $renk='badge-success'; $ikon='fa-check'; }
                            if($t['durum']=='Reddedildi') { $renk='badge-danger'; $ikon='fa-xmark'; }
                        ?>
                        <tr>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo date("d.m.Y", strtotime($t['faaliyet_tarihi'])); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #eee; font-weight:600; color:var(--navy);"><?php echo $t['proje_adi']; ?></td>
                            <td style="padding:10px; border-bottom:1px solid #eee;"><span class="badge <?php echo $renk; ?>"><i class="fa-solid <?php echo $ikon; ?>"></i> <?php echo $t['durum']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>