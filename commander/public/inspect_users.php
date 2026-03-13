<?php
require_once __DIR__ . '/../../vendor/autoload.php';

// Load .env
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
}

use Olu\Commander\Core\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    $stmt = $pdo->query("SELECT id, username, password_hash FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Live User Inspection</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Username</th><th>Hash Length</th><th>Can Match 'Naveen@991217'?</th></tr>";
    
    foreach($users as $user) {
        $testPass = 'Naveen@991217';
        $match = password_verify($testPass, $user['password_hash']) ? "<span style='color:green'>YES</span>" : "<span style='color:red'>NO</span>";
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . strlen($user['password_hash']) . " chars</td>";
        echo "<td>$match</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
