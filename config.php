<?php
// Hata raporlamayı açarak, olası sorunları net bir şekilde görmemizi sağlar.
ini_set('display_errors', 1);
error_reporting(E_ALL);
$https = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
);
$protocol = $https ? 'https://' : 'http://';
$domain   = $_SERVER['HTTP_HOST'];

// FS yollarını normalize et
$docRoot  = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']), '/');
$projRoot = rtrim(str_replace('\\','/', __DIR__), '/'); // config.php'nin klasörü (proje kökü)
$basePath = str_replace($docRoot, '', $projRoot);       // web köküne göre relatif path, ör: /rookiverse/rookiverse

define('BASE_URL', $protocol . $domain . $basePath);
// İstersen trailing slash'lı sabit:
// define('BASE_URL_SLASH', rtrim(BASE_URL, '/') . '/');


function get_db_connection() {
    static $db = null;

    if ($db === null) {
        $host = 'localhost';
        $dbname = 'frc_rookieverse';
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        try {
            $db = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Veritabanı bağlantı hatası: " . $e->getMessage());
        }
    }

    return $db;
}

function getCourseDetails($id)
{
    $db = get_db_connection();

    $sql = "SELECT * FROM courses WHERE course_uid = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);

}
function getTopCourses()
{
    $db = get_db_connection();

    $sql = "SELECT * FROM courses ORDER BY student DESC LIMIT 3";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}
function getCourses()
{
    $db = get_db_connection();

    $sql = "SELECT * FROM courses";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}
function getApprovedCategories()
{
    $db = get_db_connection();

    $sql = "SELECT * FROM categories WHERE status = 'approved'";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

function getModules($id)
{
    $db = get_db_connection();

    $sql = "SELECT * FROM course_modules WHERE course_id = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}
function getContributors()
{
    $db = get_db_connection();

    $sql = "SELECT t.team_name, t.profile_pic_path, t.website, t.id
FROM teams AS t
INNER JOIN courses AS c ON c.team_db_id = t.id
WHERE c.status = 'approved';
";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

function getModuleContent($id, $i)
{
    $db = get_db_connection();

    $sql = "SELECT * FROM module_contents WHERE module_id = ? AND sort_order = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id, $i]);

    return $stmt->fetch(PDO::FETCH_ASSOC);

}

function getTeam($id)
{
    $db = get_db_connection();

    $sql = "SELECT * FROM teams WHERE id = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);

}

/**
 * Veritabanına bağlanmak için GÜVENLİ ve HATALARI GÖSTEREN fonksiyon.
 * @return PDO Veritabanı bağlantı objesi
 */
function connectDB() {
    // MAMP için standart bağlantı ayarları
    $dsn = "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=frc_rookieverse;charset=utf8mb4";
    $user = 'root';
    $pass = 'root'; // MAMP varsayılan şifresi
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Yeni bir veritabanı bağlantısı oluştur ve geri döndür
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // EĞER BAĞLANTI BAŞARISIZ OLURSA, ekrana anlasılır bir hata mesajı yaz ve işlemi durdur.
        // "500 Internal Server Error" yerine bu mesajı göreceksin.
        die("<h1>Veritabanı Bağlantı Hatası!</h1><p>Mesaj: " . $e->getMessage() . "</p><p><b>Kontrol Et:</b><br>1. MAMP sunucun (Apache & MySQL) çalışıyor mu?<br>2. `config.php` dosyasındaki veritabanı adı (`frc_rookieverse`) doğru mu?</p>");
    }
}
?>