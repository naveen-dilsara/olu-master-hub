<?php

namespace Olu\Commander\Controllers;

use Olu\Commander\Models\Site;

class SiteController {
    
    public function index() {
        // Mock data if DB fails or empty
        $siteModel = new Site();
        try {
            $sites = $siteModel->getAll();
        } catch (\Exception $e) {
            $sites = [];
        }

        view('sites/index', [
            'title' => 'Site Registry',
            'sites' => $sites
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $url = $_POST['url'] ?? '';
            $publicKey = $_POST['public_key'] ?? '';

            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $siteModel = new \Olu\Commander\Models\Site();
                $siteModel->create([
                    'url' => $url,
                    'public_key' => $publicKey
                ]);
                
                header('Location: /sites');
                exit;
            }
        }
        
        view('sites/add', [
            'title' => 'Connect New Agent'
        ]);
    }

    public function activate() {
        if (isset($_GET['id'])) {
            $siteModel = new \Olu\Commander\Models\Site();
            $siteModel->updateStatus($_GET['id'], 'active');
            header('Location: /sites');
            exit;
        }
    }

    public function manage() {
        if (!isset($_GET['id'])) {
            header('Location: /sites');
            exit;
        }

        $siteModel = new \Olu\Commander\Models\Site();
        $site = $siteModel->find($_GET['id']);
        
        if (!$site) {
            header('Location: /sites');
            exit;
        }

        // Get available GPL plugins for the "Push" dropdown
        $pluginModel = new \Olu\Commander\Models\Plugin();
        $repoPlugins = $pluginModel->getAll();

        // Get Installed Plugins on Site
        $installedPlugins = $siteModel->getPlugins($site['id']);

        view('sites/manage', [
            'title' => 'Manage Site: ' . parse_url($site['url'], PHP_URL_HOST),
            'site' => $site,
            'repo_plugins' => $repoPlugins,
            'installed_plugins' => $installedPlugins
        ]);
    }

    public function dispatch() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteId = $_POST['site_id'];
            $slug = $_POST['plugin_slug'];
            
            $siteModel = new \Olu\Commander\Models\Site();
            $site = $siteModel->find($siteId);
            
            if (!$site) {
                die('Site not found');
            }

            $downloadUrl = '';
            $version = '';

            // Check if it's a standard update (no local file needed)
            if (isset($_POST['is_standard_update']) && $_POST['is_standard_update'] == '1') {
                // For standard updates, we don't send a download URL. 
                // The Agent knows to use wp_upgrade.
                $downloadUrl = ''; 
                $version = 'latest';
            } else {
                // It's a Repository Push
                $pluginModel = new \Olu\Commander\Models\Plugin();
                $plugin = $pluginModel->findBySlug($slug);
                if ($plugin) {
                    $downloadUrl = 'https://masterhub.olutek.com/api/v1/download?file=' . basename($plugin['file_path']);
                    $version = $plugin['version'];
                } else {
                     die('Plugin not found in repo');
                }
            }

            // Call Agent API
            $endpoint = rtrim($site['url'], '/') . '/wp-json/olu/v1/update';
            
            $body = [
                'slug' => $slug,
                'download_url' => $downloadUrl,
                'version' => $version,
                'activate' => true,
                'signature' => 'TODO_SIGNATURE' 
            ];

            // Using curl if WP functions not available (Standalone PHP)
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Log activity
            $logFile = __DIR__ . '/../../storage/api_debug.log';
            $msg = "Manual Dispatch [{$slug}] to [{$site['url']}]. Code: $httpCode";
            file_put_contents($logFile, date('Y-m-d H:i:s') . " [Dispatcher] " . $msg . PHP_EOL, FILE_APPEND);

            header('Location: /sites/manage?id=' . $siteId);
            exit;
        }
    }
}
