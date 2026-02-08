<?php

namespace Olu\Commander\Controllers;

use Olu\Commander\Models\Site;

class SettingsController {
    
    public function index() {
        // For now, we use the first site's interval as the "global" one, or default 1 day
        // In a real global system, we'd have a settings table.
        // Quick Fix: Fetch one value
        $siteModel = new Site();
        $pdo = \Olu\Commander\Core\Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT update_interval FROM sites LIMIT 1");
        $val = $stmt->fetchColumn();
        
        $currentInterval = $val ?: 86400;

        view('settings/index', [
            'title' => 'Global Settings',
            'current_interval' => $currentInterval
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $interval = (int)$_POST['update_interval'];
            
            // Global Update: Set this interval for ALL sites
            $siteModel = new Site();
            $pdo = \Olu\Commander\Core\Database::getInstance()->getConnection();
            
            $stmt = $pdo->prepare("UPDATE sites SET update_interval = ?");
            $stmt->execute([$interval]);
            
            // Trigger Agent Config Push for ALL sites (Background job ideally, but loop for now)
            // Just updated DB. Agents will pick it up next heartbeat if we implement "pull config"?
            // Current Agent implementation waits for Push or checks DB?
            // Agent checks `olu_agent_update_interval` option.
            // We MUST push this new config to all agents.
            
            $sites = $siteModel->getAll();
            foreach ($sites as $site) {
                // We can use the SiteController's logic or duplicate it here.
                // Let's call a helper.
                $this->pushConfig($site, $interval);
            }

            header('Location: /settings?msg=saved');
            exit;
        }
    }

    private function pushConfig($site, $interval) {
        $endpoint = rtrim($site['url'], '/') . '/wp-json/olu/v1/configure';
        
        $body = [
            'update_interval' => $interval,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Fast timeout for loop
        
        curl_exec($ch);
        curl_close($ch);
    }
}
