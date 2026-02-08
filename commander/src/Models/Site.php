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
        $sql = "INSERT INTO sites (url, public_key, status, wp_version, created_at) VALUES (?, ?, 'connected', ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['url'], $data['public_key'], $data['wp_version'], $now]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE sites SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function registerOrUpdate($data) {
        $url = $data['url'];
        $stmt = $this->pdo->prepare("SELECT id FROM sites WHERE url = ?");
        $stmt->execute([$url]);
        $site = $stmt->fetch();

        if ($site) {
            // Update existing
            $sql = "UPDATE sites SET public_key = ?, wp_version = ?, status = 'connected', last_heartbeat = ? WHERE id = ?";
            $update = $this->pdo->prepare($sql);
            $update->execute([$data['public_key'], $data['wp_version'], date('Y-m-d H:i:s'), $site['id']]);
            return $site['id'];
        } else {
            // Create New
            $this->create($data);
            return $this->pdo->lastInsertId();
        }
    }

    public function savePlugins($siteId, $plugins) {
        // First delete old entries to ensure clean sync (simple approach)
        // Or upsert. Let's do Insert Ignore / On Duplicate Key Update
        
        $sql = "INSERT INTO site_plugins (site_id, slug, name, version, is_active, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE name = VALUES(name), version = VALUES(version), is_active = VALUES(is_active), updated_at = VALUES(updated_at)";
        
        $stmt = $this->pdo->prepare($sql);
        $now = date('Y-m-d H:i:s');

        foreach ($plugins as $plugin) {
            $stmt->execute([
                $siteId,
                $plugin['slug'],
                $plugin['name'],
                $plugin['version'],
                $plugin['is_active'] ? 1 : 0,
                $now
            ]);
        }
    }
}
