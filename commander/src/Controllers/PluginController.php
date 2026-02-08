<?php

namespace Olu\Commander\Controllers;

use Olu\Commander\Models\Plugin;

class PluginController {
    
    public function index() {
        $pluginModel = new Plugin();
        try {
            $plugins = $pluginModel->getAll();
        } catch (\Exception $e) {
            $plugins = [];
        }

        view('plugins/index', [
            'title' => 'GPL Repository',
            'plugins' => $plugins
        ]);
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['plugin_zip'])) {
            $file = $_FILES['plugin_zip'];
            $name = $_POST['name'] ?? 'Unknown Plugin';
            $version = $_POST['version'] ?? '1.0.0';
            $slug = $_POST['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            if ($file['error'] !== UPLOAD_ERR_OK) {
                // Handle error
                return;
            }

            // Storage Logic
            $uploadDir = __DIR__ . '/../../storage/gpl_repo/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $fileName = $slug . '-' . $version . '.zip';
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Save to DB
                $pluginModel = new Plugin();
                $data = [
                    'slug' => $slug,
                    'name' => $name,
                    'version' => $version,
                    'file_path' => $targetPath,
                    'file_hash' => hash_file('sha256', $targetPath)
                ];

                if ($pluginModel->findBySlug($slug)) {
                    $pluginModel->update($slug, $data);
                } else {
                    $pluginModel->create($data);
                }

                // Trigger Auto-Update
                $updater = new \Olu\Commander\Core\AutoUpdateService();
                $result = $updater->triggerUpdate($slug);

                // Ideally flash message the result
                // $_SESSION['flash'] = "Uploaded & Triggered update for " . count($result['results']) . " sites.";

                header('Location: /plugins');
                exit;
            }
        }
        
        view('plugins/upload', [
            'title' => 'Upload New Version'
        ]);
    }
}
