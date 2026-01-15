<?php
require_once __DIR__ . '/vendor/autoload.php';
require 'db.php';
session_start();

// Güvenlik
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['rol'], ['ogretmen', 'admin', 'mudur_yrd'])) { die("Yetkisiz erişim"); }

$user_id = $_SESSION['user']['id'];

// --- FİLTRELEME (Panel ile Birebir Aynı) ---
$where = "WHERE 1=1"; 
$params = [];

// Öğretmen ise sadece kendi projeleri
if($_SESSION['user']['rol'] == 'ogretmen') {
    $where .= " AND p.ogretmen_id = $user_id";
}

if (!empty($_GET['ara'])) { $where .= " AND k.ad_soyad LIKE ?"; $params[] = "%".$_GET['ara']."%"; }
if (!empty($_GET['sinif'])) { $where .= " AND k.sinif = ?"; $params[] = $_GET['sinif']; }
if (!empty($_GET['proje'])) { $where .= " AND p.id = ?"; $params[] = $_GET['proje']; }
if (!empty($_GET['bas_tarih'])) { $where .= " AND f.faaliyet_tarihi >= ?"; $params[] = $_GET['bas_tarih']; }
if (!empty($_GET['bit_tarih'])) { $where .= " AND f.faaliyet_tarihi <= ?"; $params[] = $_GET['bit_tarih']; }

// Sadece ONAYLI olanları yazdır (Genelde PDF onaysızları içermez, isteğe bağlı kaldırabilirsin)
$where .= " AND f.durum = 'Onaylandı'";

// Verileri Çek
$sql = "SELECT f.id as fid, f.faaliyet_tarihi, f.sure_tipi, f.aciklama, p.proje_adi, k.ad_soyad, k.okul_no, k.sinif, k.sube, k.alan
        FROM faaliyetler f 
        JOIN projeler p ON f.proje_id = p.id 
        JOIN kullanicilar k ON f.ogrenci_id = k.id 
        $where ORDER BY k.sinif ASC, k.sube ASC, k.ad_soyad ASC, f.faaliyet_tarihi ASC";

$sorgu = $db->prepare($sql);
$sorgu->execute($params);
$kayitlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// --- VERİTABANI GÜNCELLEME (ÇIKTI ALINDI İŞARETLEMESİ) ---
// PDF oluşturulmadan önce bu kayıtların 'cikti_alindi' sütununu 1 yapıyoruz.
if(count($kayitlar) > 0) {
    $ids = array_column($kayitlar, 'fid'); // Çekilen ID'leri al
    $idList = implode(',', $ids);
    // Toplu güncelleme
    $db->query("UPDATE faaliyetler SET cikti_alindi = 1 WHERE id IN ($idList)");
}

// --- HTML OLUŞTURMA (Tasarım Kısmı) ---
$logo_html = file_exists('logo.png') ? '<img src="logo.png" style="height: 50px;">' : '';
$tarih_araligi = (!empty($_GET['bas_tarih']) ? date("d.m.Y", strtotime($_GET['bas_tarih'])) : '...') . " - " . (!empty($_GET['bit_tarih']) ? date("d.m.Y", strtotime($_GET['bit_tarih'])) : '...');

$html = '
<table style="width: 100%; border-bottom: 2px solid #333; margin-bottom: 20px; font-family: sans-serif;">
    <tr>
        <td style="width: 25%; font-size: 11px;">
            <strong>UYGUNDUR</strong><br>'.date("d.m.Y").'<br><br><br>
            ...................................<br>Okul Müdürü
        </td>
        <td style="width: 50%; text-align: center;">
            '.$logo_html.'
            <h3 style="margin:5px 0;">YILDIZ TEKNİK ÜNİVERSİTESİ<br>MAÇKA MTAL</h3>
            <h4 style="margin:0;">Proje Faaliyet Takip Listesi</h4>
            <div style="font-size:11px; margin-top:5px;">('.$tarih_araligi.')</div>
        </td>
        <td style="width: 25%; text-align: right; font-size: 10px; vertical-align: bottom;">
            Rapor Tarihi: '.date("d.m.Y H:i").'
        </td>
    </tr>
</table>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; font-family: sans-serif; font-size:11px;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th width="10%">Tarih</th>
            <th width="20%">Öğrenci</th>
            <th width="10%">Sınıf</th>
            <th width="15%">Proje</th>
            <th width="35%">Yapılan İş / Açıklama</th>
            <th width="10%">Süre</th>
        </tr>
    </thead>
    <tbody>';

if (count($kayitlar) > 0) {
    foreach ($kayitlar as $row) {
        // Öğrenci ise açıklamayı maskeleyebilirsin, öğretmen ise görebilir. 
        // Şimdilik öğretmen aldığı için her şeyi gösteriyoruz.
        $html .= '<tr>
                    <td style="text-align:center;">' . date("d.m.Y", strtotime($row['faaliyet_tarihi'])) . '</td>
                    <td>' . $row['ad_soyad'] . '<br><span style="color:#555; font-size:9px;">' . $row['okul_no'] . '</span></td>
                    <td style="text-align:center;">' . $row['sinif'] . ' - ' . $row['sube'] . '</td>
                    <td style="text-align:center;">' . $row['proje_adi'] . '</td>
                    <td>' . nl2br(htmlspecialchars($row['aciklama'])) . '</td>
                    <td style="text-align:center;">' . $row['sure_tipi'] . '</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" style="text-align:center; padding:20px;">Onaylı kayıt bulunamadı.</td></tr>';
}

$html .= '</tbody></table>

<br><br>
<table style="width:100%; font-family: sans-serif; font-size: 12px; text-align: center;">
    <tr>
        <td style="width: 50%;">
            ...................................<br><strong>Koordinatör Öğretmen</strong>
        </td>
        <td style="width: 50%;">
            Altuğ ÖNAL<br><strong>Elektrik-Elektronik Alan Şefi</strong>
        </td>
    </tr>
</table>';

$dosyaAdi = 'faaliyet-raporu-' . date('d-m-Y') . '.pdf';

try {
    $mpdf = new \Mpdf\Mpdf(['orientation' => 'L', 'margin_top' => 10, 'margin_bottom' => 10]);
    $mpdf->WriteHTML($html);
    $mpdf->Output($dosyaAdi, 'D');
} catch (\Mpdf\MpdfException $e) { echo "PDF Hatası: " . $e->getMessage(); }
?>