<?php
class Database {
    private static $instance = null;
    private $connection;
    private const HOST = 'mysql';
    private const PORT = 3306;
    private const DBNAME = 'supermarket_app';
    private const USERNAME = 'dev';
    private const PASSWORD = 'dev';
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                ""mysql:host="" . self::HOST . "";port="" . self::PORT . "";dbname="" . self::DBNAME . "";charset=utf8mb4"",
                self::USERNAME,
                self::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new Exception(""データベース接続エラー: "" . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

function getDB() {
    return Database::getInstance();
}
?>
