<?php

namespace Olu\Commander\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        
        try {
            if (isset($config['driver']) && $config['driver'] === 'sqlite') {
                $dsn = "sqlite:{$config['database']}";
                // Ensure the file exists
                if (!file_exists($config['database'])) {
                    touch($config['database']);
                }
                $this->pdo = new PDO($dsn, null, null, $config['options']);
            } else {
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            }
        } catch (PDOException $e) {
            // For production, log error and show generic message
            // For dev, show error
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
