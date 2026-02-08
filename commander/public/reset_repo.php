<?php
// reset_repo.php
require_once __DIR__ . '/../vendor/autoload.php';
use Olu\Commander\Core\Database;

header('Content-Type: text/plain');

echo "=== OLU Repository Reset Tool ===\n";
echo "WARNING: This will delete ALL plugins from the database and storage.\n";

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "To confirm, please append ?confirm=yes to the URL.\n";
    exit;
}

$pdo = Database::getInstance()->getConnection();

// 1. Truncate Database
$pdo->exec("DELETE FROM plugins");
// Reset ID auto-increment (MySQL syntax)
$pdo->exec("ALTER TABLE plugins AUTO_INCREMENT = 1");

echo "[OK] Database table 'plugins' cleared.\n";

// 2. Delete Files
$dir = __DIR__ . '/../storage/gpl_repo';
$files = glob($dir . '/*');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
        echo "[OK] Deleted: " . basename($file) . "\n";
    }
}

echo "\n=== Repository is Empty ===\n";
echo "You can now upload 'Elementor Pro' again from the Dashboard.\n";
