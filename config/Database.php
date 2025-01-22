<?php
namespace Config;

class Database {
    private static ?self $instance = null;
    private \PDO $connection;
    private array $connectionPool = [];
    private const POOL_SIZE = 5;

    private function __construct() {
        $this->initializeConnectionPool();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeConnectionPool(): void {
        for ($i = 0; $i < self::POOL_SIZE; $i++) {
            $this->connectionPool[] = $this->createConnection();
        }
    }

    private function createConnection(): \PDO {
        $host = env('DB_HOST') ?: 'localhost';
        $dbname = env('DB_DATABASE') ?: 'event_management';
        $username = env('DB_USERNAME') ?: 'root';
        $password = env('DB_PASSWORD') ?: '';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => true
        ];

        try {
            return new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection(): \PDO {
        if (empty($this->connectionPool)) {
            return $this->createConnection();
        }
        return array_pop($this->connectionPool);
    }

    public function releaseConnection(\PDO $connection): void {
        if (count($this->connectionPool) < self::POOL_SIZE) {
            $this->connectionPool[] = $connection;
        }
    }
}