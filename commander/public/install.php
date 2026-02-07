<?php
// OLU Master Hub - Web Installer
// Use this to initialize the database on shared hosting (Hostinger)

require_once __DIR__ . '/../../vendor/autoload.php';

use Olu\Commander\Core\Database;

try {
    echo "<h1>OLU Master Hub Installer</h1>";
    
    // 1. Connect to DB (will create sqlite file if missing)
    echo "<p>Connecting to database...</p>";
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "<p style='color:green'>Database connected/created.</p>";
    
    // 2. Read Schema
    $schemaPath = __DIR__ . '/../config/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Schema file not found at: $schemaPath");
    }
    
    $sql = file_get_contents($schemaPath);
    
    // Fix replacements (same as migrate.php)
    $sql = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
    $sql = str_replace('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
    $sql = str_replace('TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
    
    // 3. Execute Schema
    echo "<p>Running migration...</p>";
    // Split by semicolon via PDO usually works, or execute raw
    $pdo->exec($sql);
    echo "<p style='color:green'>Tables created.</p>";
    
    // 4. Create Admin
    $userModel = new \Olu\Commander\Models\User();
    if ($userModel->ensureAdminExists('admin', 'password')) {
        echo "<p style='color:green'><strong>Admin user created.</strong><br>User: admin<br>Pass: password</p>";
    } else {
        echo "<p>Admin user already exists.</p>";
    }
    
    echo "<h3>Installation Complete!</h3>";
    echo "<p><a href='/login'>Go to Login</a></p>";
    echo "<p><em>Please delete this install.php file after use for security.</em></p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Installation Failed</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
