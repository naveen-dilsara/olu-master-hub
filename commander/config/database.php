<?php

// Load .env if exists
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
}

$driver = $_ENV['DB_CONNECTION'] ?? 'mysql'; // Default to MySQL for production if not set, or sqlite for local fallback if explicitly set

if ($driver === 'sqlite') {
    return [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../../database.sqlite',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];
} else {
    // MySQL Config
    return [
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? 'u123456789_masterhub', // Example Hostinger format
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];
}
