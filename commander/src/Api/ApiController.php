<?php

namespace Olu\Commander\Api;

use Olu\Commander\Models\Site;

class ApiController {
    
    // POST /api/v1/handshake
    public function handshake() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Debug Logging
        $logFile = __DIR__ . '/../../storage/api_debug.log';
        $logEntry = date('Y-m-d H:i:s') . " - Handshake Request: " . print_r($input, true) . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        if (!$input || !isset($input['url']) || !isset($input['public_key'])) {
            $this->jsonResponse(['error' => 'Invalid Payload'], 400);
            return;
        }

        $siteModel = new Site();
        $siteId = $siteModel->registerOrUpdate($input);

        // Process Plugins if available
        if ($siteId && isset($input['plugins']) && is_array($input['plugins'])) {
            $siteModel->savePlugins($siteId, $input['plugins']);
        }

        $this->jsonResponse(['status' => 'success', 'site_id' => $siteId]);
    }
        
    // POST /api/v1/check-version
    public function checkVersion() {
        $input = json_decode(file_get_contents('php://input'), true);
        $slug = $input['slug'] ?? '';
        
        if (!$slug) {
            $this->jsonResponse(['error' => 'Missing slug'], 400);
        }

        $pluginModel = new \Olu\Commander\Models\Plugin();
        $plugin = $pluginModel->findBySlug($slug);

        if ($plugin) {
            $downloadUrl = 'https://masterhub.olutek.com/api/v1/download?file=' . basename($plugin['file_path']);
            $this->jsonResponse([
                'new_version' => $plugin['version'],
                'package' => $downloadUrl,
                'slug' => $slug
            ]);
        } else {
            $this->jsonResponse(['error' => 'Plugin not found'], 404);
        }
    }

    // GET /api/v1/download?file=filename.zip
    public function download() {
        $file = $_GET['file'] ?? '';
        
        // Basic Security: Prevent directory traversal
        if (!$file || strpos($file, '..') !== false || strpos($file, '/') !== false) {
             http_response_code(400);
             die('Invalid File Request');
        }

        $storageDir = __DIR__ . '/../../storage/gpl_repo/';
        $filePath = $storageDir . $file;

        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File Not Found');
        }

        // Serve File
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    // POST /api/v1/disconnect
    public function disconnect() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['url']) || !isset($input['public_key'])) {
            $this->jsonResponse(['error' => 'Invalid Payload'], 400);
            return;
        }

        $siteModel = new \Olu\Commander\Models\Site();
        // Find site by URL
        // We really should verify the key matches what we have, 
        // but for now we trust the URL + Key combo if we had a proper lookup.
        // For this MVP, we just find by URL and set status.
        
        $stmt = $siteModel->pdo->prepare("SELECT id FROM sites WHERE url = ?");
        $stmt->execute([$input['url']]);
        $site = $stmt->fetch();

        if ($site) {
             $siteModel->updateStatus($site['id'], 'disconnected');
             $this->jsonResponse(['status' => 'success']);
        } else {
             $this->jsonResponse(['error' => 'Site not found'], 404);
        }
    }

    // Helper
    private function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit; // API ends here
    }
}
