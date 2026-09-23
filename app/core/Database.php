<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {

            $config = require dirname(__DIR__) . '/config/config.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['db']['host'],
                $config['db']['port'],
                $config['db']['name'],
                $config['db']['charset']
            );

            self::$connection = new PDO(
                $dsn,
                $config['db']['user'],
                $config['db']['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        }

        return self::$connection;
    }
}