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
            } catch (PDOException $e) {
                die('Database Connection Error: ' . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>
