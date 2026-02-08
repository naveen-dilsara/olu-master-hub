<?php
// fix_permissions.php
header('Content-Type: text/plain');

$storage = __DIR__ . '/../storage';
if (!is_dir($storage)) {
    mkdir($storage, 0755, true);
}

// Typ to make it writable
chmod($storage, 0777); 
chmod($storage . '/api_debug.log', 0666);
chmod($storage . '/gpl_repo', 0777);

echo "Attempted to chmod 777 on: $storage\n";
echo "Writable? " . (is_writable($storage) ? "YES" : "NO") . "\n";
