<?php

require_once __DIR__ . '/src/autoload.php';

use Olu\Commander\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Read schema
    $sql = file_get_contents(__DIR__ . '/config/schema.sql');
    
    // SQLite doesn't support AUTO_INCREMENT, replace with AUTOINCREMENT for primary keys if strictly needed
    // But mostly "INTEGER PRIMARY KEY" is enough in SQLite (it auto increments).
    // MySQL schema uses "INT AUTO_INCREMENT PRIMARY KEY".
    // Let's do a quick regex replacement to make the SQL compatible with both if possible, 
    // or just rely on SQLite being lenient. 
    // Actually, SQLite is fine with "INT PRIMARY KEY" but "AUTO_INCREMENT" is MySQL specific.
    
    $sql = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
    $sql = str_replace('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
    $sql = str_replace('TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
    
    // Execute multiple queries
    $pdo->exec($sql);
    
    echo "Database migrated successfully.\n";
    
    // Seed Admin
    $userModel = new \Olu\Commander\Models\User();
    if ($userModel->ensureAdminExists('admin', 'password')) {
        echo "Admin user created (admin/password).\n";
    } else {
        echo "Admin user already exists.\n";
    }

} catch (Exception $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
}
