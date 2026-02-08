<?php

namespace Olu\Commander\Core;

use Olu\Commander\Models\Site;
use Olu\Commander\Models\Plugin;

class AutoUpdateService {

    private $db;
    private $siteModel;
    private $pluginModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->siteModel = new Site();
        $this->pluginModel = new Plugin();
    }

    public function triggerUpdate($pluginSlug) {
        // 1. Get the latest version info for this plugin
        $masterPlugin = $this->pluginModel->findBySlug($pluginSlug);
        if (!$masterPlugin) {
            return ['status' => 'error', 'message' => 'Plugin not found in repository'];
        }

        $latestVersion = $masterPlugin['version'];
        $latestVersion = $masterPlugin['version'];
        // Use the API download endpoint instead of direct file access
        $downloadUrl = 'https://masterhub.olutek.com/api/v1/download?file=' . basename($masterPlugin['file_path']);

        // 2. Find all sites that have this plugin installed BUT have an older version
        // We join sites and site_plugins
        $sql = "
            SELECT s.id, s.url, s.public_key, sp.version as site_version 
            FROM sites s
            JOIN site_plugins sp ON s.id = sp.site_id
            WHERE sp.slug = ? 
            AND s.status = 'connected'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pluginSlug]);
        $sites = $stmt->fetchAll();

        $results = [];

        foreach ($sites as $site) {
             // Compare versions
             if (version_compare($site['site_version'], $latestVersion, '<')) {
                 // Trigger Update
                 $result = $this->pushUpdateToSite($site, $pluginSlug, $downloadUrl, $latestVersion);
                 $results[] = [
                     'site' => $site['url'],
                     'status' => $result ? 'pushed' : 'failed'
                 ];
             }
        }

        return ['status' => 'completed', 'results' => $results];
    }


    public function processSiteHandshake($siteId, $plugins) {
        $site = $this->siteModel->find($siteId);
        if (!$site) return;

        $this->log("Checking updates for site: " . $site['url']);

        foreach ($plugins as $p) {
            $repoPlugin = $this->pluginModel->findBySlug($p['slug']);
            
            if ($repoPlugin) {
                 $siteVer = $p['version'];
                 $repoVer = $repoPlugin['version'];
                 $needsUpdate = version_compare($siteVer, $repoVer, '<');
                 
                 $this->log("Plugin [{$p['slug']}]: Site ($siteVer) vs Repo ($repoVer). Update? " . ($needsUpdate ? 'YES' : 'NO'));

                 if ($needsUpdate) {
                     $downloadUrl = 'https://masterhub.olutek.com/api/v1/download?file=' . basename($repoPlugin['file_path']);
                     
                     // Push the update
                     $success = $this->pushUpdateToSite($site, $repoPlugin['slug'], $downloadUrl, $repoPlugin['version']);
                     $this->log("Push Result: " . ($success ? 'SUCCESS' : 'FAILED'));
                 }
            }
        }
    }

    private function pushUpdateToSite($site, $slug, $url, $version) {
        $endpoint = rtrim($site['url'], '/') . '/wp-json/olu/v1/update';
        
        $this->log("Pushing update to: $endpoint");

        $body = [
            'slug' => $slug,
            'download_url' => $url,
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
        
        // Timeout is critical. If Agent takes too long, we might timeout here.
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $this->log("Curl Response: Code $httpCode. Error: $curlError. Body: " . substr($response, 0, 100));

        return ($httpCode >= 200 && $httpCode < 300);
    }

    private function log($msg) {
        $logFile = __DIR__ . '/../../storage/api_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " [AutoUpdate] " . $msg . PHP_EOL, FILE_APPEND);
    }
}
