<?php

namespace Olu\Commander\Controllers;

use Olu\Commander\Models\Site;

class DashboardController {
    
    public function index() {
        $siteModel = new \Olu\Commander\Models\Site();
        $pluginModel = new \Olu\Commander\Models\Plugin();

        $sites = $siteModel->getAll();
        $plugins = $pluginModel->getAll();

        $stats = [
            'total_sites' => count($sites),
            'premium_plugins' => count($plugins),
            'system_status' => 'Operational'
        ];

        // Use recent sites as activity feed for now
        $recent_activity = array_slice($sites, 0, 5);

        view('dashboard', [
            'title' => 'Command Center',
            'stats' => $stats,
            'activity' => $recent_activity
        ]);
    }
}
