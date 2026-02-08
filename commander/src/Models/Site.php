<?php

namespace Olu\Commander\Models;

use Olu\Commander\Core\Database;
use PDO;

class Site {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM sites ORDER BY last_heartbeat DESC");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM sites WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO sites (url, public_key, status, created_at) VALUES (?, ?, 'pending', ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['url'], $data['public_key'], $now]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE sites SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
