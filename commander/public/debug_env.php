<?php
// Debug script to check .env loading
require_once __DIR__ . '/../vendor/autoload.php';

echo "<h1>Environment Debugger</h1>";

$envDir = __DIR__ . '/../';
$envFile = $envDir . '.env';

echo "<p>Looking for .env in: " . $envDir . "</p>";

if (file_exists($envFile)) {
    echo "<p style='color:green'><strong>.env file FOUND.</strong></p>";
    
    try {
        $dotenv = Dotenv\Dotenv::createImmutable($envDir);
        $dotenv->load();
        echo "<p>Dotenv loaded successfully.</p>";
        
        echo "<h3>Loaded Variables:</h3>";
        echo "DB_CONNECTION: " . ($_ENV['DB_CONNECTION'] ?? 'Not Set') . "<br>";
        echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'Not Set') . "<br>";
        echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'Not Set') . "<br>";
        echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'Not Set') . "<br>";
        // Hide password for security, just check if set
        echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '[SET]' : 'Not Set') . "<br>";
        
    } catch (Exception $e) {
        echo "<p style='color:red'>Error loading Dotenv: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'><strong>.env file NOT FOUND.</strong></p>";
    echo "<p>Contents of directory:</p>";
    echo "<pre>";
    print_r(scandir($envDir));
    echo "</pre>";
}
