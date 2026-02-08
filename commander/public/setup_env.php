<?php
// Script to automatically create the .env file
// Run this ONCE then delete it.

$targetFile = __DIR__ . '/../.env';

$content = "DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=u845683626_masterhub
DB_USERNAME=u845683626_master
DB_PASSWORD=Naveen@991217
";

if (file_put_contents($targetFile, $content)) {
    echo "<h1 style='color:green'>Success!</h1>";
    echo "<p>.env file has been created at: " . realpath($targetFile) . "</p>";
    echo "<p>Content written:</p>";
    echo "<pre>$content</pre>";
    echo "<br>";
    echo "<p><a href='/install.php'>Click here to Run Installer</a></p>";
} else {
    echo "<h1 style='color:red'>Failed</h1>";
    echo "<p>Could not write to: $targetFile</p>";
    echo "<p>Check permissions.</p>";
}
