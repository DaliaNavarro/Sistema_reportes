<?php

return [
    'app_name' => 'Sistema de Reportes TI',

    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Mexico_City',

    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'reportes',
        'user' => getenv('DB_USER') ?: 'reportes',
        'pass' => getenv('DB_PASSWORD') ?: 'reportes_secret',
        'charset' => 'utf8mb4'
    ],

    'upload' => [
        'max_size' => 10 * 1024 * 1024,
        'directory' => dirname(__DIR__, 2) . '/storage/memorandums'
    ]
];