<?php
// OLU Master Hub - Diagnostic Tool
header('Content-Type: text/plain');

require_once __DIR__ . '/../vendor/autoload.php';
use Olu\Commander\Core\Database;

echo "=== OLU System Check ===\n\n";

// 1. Check Database Connection
try {
    $pdo = Database::getInstance()->getConnection();
    echo "[PASS] Database Connected\n";
} catch (Exception $e) {
    die("[FAIL] Database Connection Error: " . $e->getMessage() . "\n");
}

// 2. Check Tables
$tables = ['users', 'sites', 'plugins', 'site_plugins'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "[PASS] Table '$table' exists. Rows: $count\n";
    } catch (Exception $e) {
        echo "[FAIL] Table '$table' MISSING or Error: " . $e->getMessage() . "\n";
    }
}

// 3. Check Storage Permissions
$storageDir = __DIR__ . '/../storage';
if (!is_dir($storageDir)) {
    echo "\n[WARN] Storage directory missing. Attempting to create...\n";
    @mkdir($storageDir, 0755, true);
}

if (is_writable($storageDir)) {
    echo "[PASS] Storage directory is writable.\n";
    file_put_contents($storageDir . '/test_write.txt', 'test');
    unlink($storageDir . '/test_write.txt');
} else {
    echo "[FAIL] Storage directory is NOT writable. Check permissions for $storageDir\n";
    // Try to get current user
    echo "Current Web User: " . exec('whoami') . "\n";
}

// 4. Check Recent API Logs
$logFile = __DIR__ . '/../storage/api_debug.log';
if (file_exists($logFile)) {
    echo "\n=== Recent handshake.log (Last 2KB) ===\n";
    echo "Path: $logFile\n";
    $content = file_get_contents($logFile);
    echo substr($content, -2000) . "\n"; 
} else {
    echo "\n[WARN] No API log file found at $logFile\n";
}


// 4. Check Plugin Repository
$repoDir = __DIR__ . '/../storage/gpl_repo/';
if (is_dir($repoDir)) {
    echo "\n=== GPL Repository Files ===\n";
    $files = scandir($repoDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo " - $file (" . filesize($repoDir . $file) . " bytes)\n";
        }
    }
} else {
    echo "\n[WARN] storage/gpl_repo directory missing!\n";
}

echo "\n=== End Report ===\n";
