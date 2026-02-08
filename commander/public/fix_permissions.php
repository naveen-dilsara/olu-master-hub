<?php
// fix_permissions.php
header('Content-Type: text/plain');

$storage = __DIR__ . '/../storage';
if (!is_dir($storage)) {
    mkdir($storage, 0755, true);
}
$repo = $storage . '/gpl_repo';
if (!is_dir($repo)) {
    mkdir($repo, 0755, true);
}
$log = $storage . '/api_debug.log';
if (!file_exists($log)) {
    file_put_contents($log, "Log started.\n");
}

// Typ to make it writable
chmod($storage, 0777); 
chmod($log, 0666);
chmod($repo, 0777);

echo "Attempted to chmod 777 on: $storage\n";
echo "Writable? " . (is_writable($storage) ? "YES" : "NO") . "\n";
