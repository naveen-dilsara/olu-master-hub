<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Olu\Commander\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Create site_plugins table
    $sql = "CREATE TABLE IF NOT EXISTS site_plugins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        site_id INT NOT NULL,
        slug VARCHAR(100) NOT NULL,
        name VARCHAR(255),
        version VARCHAR(50),
        is_active BOOLEAN DEFAULT FALSE,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
        UNIQUE KEY unique_site_plugin (site_id, slug)
    )";
    
    $pdo->exec($sql);
    echo "Migration successful: site_plugins table created.\n";
    
} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
