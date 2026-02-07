<?php

namespace Olu\Commander\Controllers;

use Olu\Commander\Models\Site;

class DashboardController {
    
    public function index() {
        // In a real scenario, we would inject the model or use a container
        // $siteModel = new Site(); 
        // $sites = $siteModel->getAll(); 
        
        // For now, pass dummy data if DB is not set up
        $stats = [
            'total_sites' => 0,
            'premium_plugins' => 0,
            'system_status' => 'Operational'
        ];

        view('dashboard', [
            'title' => 'Command Center',
            'stats' => $stats
        ]);
    }
}
