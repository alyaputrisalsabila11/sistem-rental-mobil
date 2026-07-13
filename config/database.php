<?php
class Database {
    private static $host = 'localhost';
    private static $db_name = 'db_rental_mobil';
    private static $user = 'root';
    private static $password = '';
    private static $connection;

    public static function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    'mysql:host=' . self::$host . ';dbname=' . self::$db_name,
                    self::$user,
                    self::$password
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // SKRIP PEMULIHAN OTOMATIS: Mengubah seluruh pelanggan tanpa level loyalitas menjadi tingkat Regular (Level 1)
                self::$connection->query("UPDATE pelanggan SET id_level = 1 WHERE id_level IS NULL OR id_level = 0");

                } catch (PDOException $e) {
                die('Database Connection Error: ' . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>
