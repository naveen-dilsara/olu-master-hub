<?php
// debug_force_update.php
require_once __DIR__ . '/../vendor/autoload.php';

use Olu\Commander\Core\AutoUpdateService;
use Olu\Commander\Models\Site;

header('Content-Type: text/plain');

echo "=== OLU Auto-Update Debugger ===\n";

// 1. Find Site
$siteModel = new Site();
// We assume ID 1 is the main site, or we find it.
$sites = $siteModel->getAll();
if (empty($sites)) {
    die("No sites found in DB.");
}
$site = $sites[0]; // Pick first site
echo "Target Site: " . $site['url'] . " (ID: " . $site['id'] . ")\n";

// 2. Prepare Mock Payload (Outdated Elementor Pro)
$mockPlugins = [
    [
        'slug' => 'elementor-pro',
        'version' => '3.0.0', // Deliberately old to force update
        'name' => 'Elementor Pro (Mock Old)'
    ]
];

echo "Simulating Handshake with outdated 'elementor-pro' v3.0.0...\n";

// 3. Trigger Service
$updater = new AutoUpdateService();

// We need to capture the internal log, or just rely on the return.
// Since processSiteHandshake is void, we depend on api_debug.log
// Let's print the log file before and after.

$logFile = __DIR__ . '/../storage/api_debug.log';
$initialSize = file_exists($logFile) ? filesize($logFile) : 0;

$updater->processSiteHandshake($site['id'], $mockPlugins);

echo "Execution Complete.\n";

// 4. Show New Logs
if (file_exists($logFile)) {
    clearstatcache();
    $finalSize = filesize($logFile);
    if ($finalSize > $initialSize) {
        echo "\n=== New Log Entries ===\n";
        $handle = fopen($logFile, "r");
        fseek($handle, $initialSize);
        echo fread($handle, $finalSize - $initialSize);
        fclose($handle);
    } else {
        echo "\n[WARNING] No new log entries created. Logic might not have triggered.\n";
    }
}
