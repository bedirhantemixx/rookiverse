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