<?php
// OLU Master Hub - Commander Entry Point

require_once __DIR__ . '/../vendor/autoload.php';

use Olu\Commander\Controllers\DashboardController;
use Olu\Commander\Controllers\AuthController;
use Olu\Commander\Controllers\SiteController;
use Olu\Commander\Controllers\PluginController;
use Olu\Commander\Api\ApiController;

session_start();

// Simple Router (Temporary)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Basic "View" Helper
function view($name, $data = []) {
    extract($data);
    ob_start();
    require __DIR__ . "/../views/$name.php";
    $content = ob_get_clean();
    require __DIR__ . "/../views/layouts/master.php";
}

// Auth Routes
if ($path === '/login') {
    (new AuthController())->login();
    exit;
}
if ($path === '/logout') {
    (new AuthController())->logout();
    exit;
}

// ... (previous routes)

// ... (previous routes)

// Public API Routes (No Auth Session Required)
if ($path === '/api/v1/handshake') {
    (new ApiController())->handshake();
}

// Protected Routes Middleware
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

if ($path === '/' || $path === '/index.php') {
    (new DashboardController())->index();
} elseif ($path === '/sites') {
    (new SiteController())->index();
} elseif ($path === '/sites/add') {
    (new SiteController())->create();
} elseif ($path === '/sites/activate') {
    (new SiteController())->activate();
} elseif ($path === '/sites/manage') {
    (new SiteController())->manage();
} elseif ($path === '/plugins') {
    (new PluginController())->index();
} elseif ($path === '/plugins/upload') {
    (new PluginController())->upload();
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
}
