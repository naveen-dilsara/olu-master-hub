<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Olu\Commander\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Add update_interval column (default 86400 = 1 day)
    $sql = "ALTER TABLE sites ADD COLUMN update_interval INTEGER DEFAULT 86400";
    $pdo->exec($sql);
    
    echo "Migration Successful: Added update_interval column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Migration Failed: " . $e->getMessage() . "\n";
    }
}
