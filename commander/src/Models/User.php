<?php

namespace Olu\Commander\Models;

use Olu\Commander\Core\Database;
use PDO;

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function create($username, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hash, $now]);
    }
    
    // Helper to create an initial admin if none exists
    public function ensureAdminExists($username, $password) {
        if (!$this->findByUsername($username)) {
            $this->create($username, $password);
            return true;
        }
        return false;
    }
}
