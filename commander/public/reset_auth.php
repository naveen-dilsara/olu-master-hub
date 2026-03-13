<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Olu\Commander\Core\Database;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Delete any existing user with this username
            $stmt_del = $pdo->prepare("DELETE FROM users WHERE username = ?");
            $stmt_del->execute([$username]);
            
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $created_at = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $hash, $created_at]);
            
            $message = "<div style='color: green; font-weight: bold; margin-bottom: 15px;'>Success! Username '$username' was created with your new password. <a href='/login'>Click here to login</a>.</div>";
        } catch (Exception $e) {
            $message = "<div style='color: red; margin-bottom: 15px;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div style='color: red; margin-bottom: 15px;'>Please enter both username and password.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Master Hub Password Reset</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 50px; text-align: center; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 400px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .warning { color: red; font-size: 0.9em; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Set Your New Password</h2>
        <?php echo $message; ?>
        <form method="POST">
            <input type="email" name="username" placeholder="Email Address (e.g. admin@olutk.com)" required>
            <input type="text" name="password" placeholder="New Password" required>
            <button type="submit">Update Password</button>
        </form>
        <p class="warning">⚠️ Delete this file (reset_auth.php) from your server immediately after use for security!</p>
    </div>
</body>
</html>
