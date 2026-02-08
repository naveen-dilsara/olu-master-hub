<?php
// add_update_flag.php
require_once __DIR__ . '/../vendor/autoload.php';

use Olu\Commander\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $columns = $pdo->query("DESCRIBE site_plugins")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('has_update', $columns)) {
        echo "Adding 'has_update' column to site_plugins table...\n";
        $pdo->exec("ALTER TABLE site_plugins ADD COLUMN has_update TINYINT(1) DEFAULT 0 AFTER is_active");
        echo "Column added successfully.\n";
    } else {
        echo "Column 'has_update' already exists.\n";
    }

} catch (Exception $e) {
    die("Migration Failed: " . $e->getMessage() . "\n");
}
