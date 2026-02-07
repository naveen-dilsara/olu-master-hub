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
        $plugins = $pluginModel->getAll();

        view('sites/manage', [
            'title' => 'Manage Site: ' . parse_url($site['url'], PHP_URL_HOST),
            'site' => $site,
            'repo_plugins' => $plugins
        ]);
    }
}
