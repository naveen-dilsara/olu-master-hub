<?php

namespace Olu\Commander\Models;

use Olu\Commander\Core\Database;
use PDO;

class Plugin {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM plugins ORDER BY updated_at DESC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO plugins (slug, name, version, file_path, file_hash, updated_at) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['slug'],
            $data['name'],
            $data['version'],
            $data['file_path'],
            $data['file_hash'],
            $now
        ]);
    }
    
    public function update($slug, $data) {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE plugins SET version = ?, file_path = ?, file_hash = ?, updated_at = ? WHERE slug = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['version'],
            $data['file_path'],
            $data['file_hash'],
            $now,
            $slug
        ]);
    }

    public function findBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM plugins WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM plugins WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM plugins WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
