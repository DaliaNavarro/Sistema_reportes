<?php

declare(strict_types=1);

function db(): PDO
{
    $host = getenv('DB_HOST') ?: 'db';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_NAME') ?: 'reportes';
    $username = getenv('DB_USER') ?: 'reportes';
    $password = getenv('DB_PASSWORD') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    try {
        return new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        throw new RuntimeException(
            'No se pudo conectar con la base de datos: ' . $e->getMessage(),
            0,
            $e
        );
    }
}
  
