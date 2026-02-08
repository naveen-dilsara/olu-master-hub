<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Olu\Commander\Core\Database;

// Security: Only allow running if explicitly accessed, maybe add a simple token or just rely on obscurity for this one-time fix.
// Ideally, this should be run via CLI, but for Hostinger web access is easier.

try {
    $pdo = Database::getInstance()->getConnection();
    
    // 1. Delete insecure 'admin' user
    $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $deleted = $stmt->rowCount();
    
    // 2. Delete existing 'naveen' user to ensure fresh hash
    $stmt->execute(['naveen@olutk.com']);
    
    // 3. Create new user with correct hash
    $username = 'naveen@olutk.com';
    $password = 'Naveen@991217';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $created_at = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hash, $created_at]);
    
    echo "<h1>Authentication Reset Successful</h1>";
    echo "<p>Deleted old 'admin' user (count: $deleted).</p>";
    echo "<p>Created user: <strong>$username</strong></p>";
    echo "<p>Password set to: <strong>$password</strong></p>";
    echo "<p style='color:red'>Please delete this file (reset_auth.php) from your server after use.</p>";
    echo "<p><a href='/login'>Go to Login</a></p>";

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo $e->getMessage();
}
