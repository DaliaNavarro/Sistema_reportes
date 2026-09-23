// Ejecutar para crear rol administrador
// docker compose exec app php cli/create_admin.php "Nombre del usuario" "usuario" "contraseña"

<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("Este script debe ejecutarse desde la consola.\n");
}

if ($argc !== 3) {
    echo "Uso:\n";
    echo 'php cli/create_admin.php "NombreUsuario" "Contraseña"' . "\n";
    exit(1);
}

$nombre = trim($argv[1]);
$password = $argv[2];

if ($nombre === '' || $password === '') {
    exit("El nombre de usuario y la contraseña son obligatorios.\n");
}

require dirname(__DIR__) . '/app/config/config.php';
require dirname(__DIR__) . '/app/core/Database.php';

try {
    $pdo = Database::getConnection();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $insertar = $pdo->prepare('
        INSERT INTO usuarios_sistema
        (
            nombre,
            password_hash,
            rol,
            activo
        )
        VALUES
        (
            :nombre,
            :password_hash,
            :rol,
            :activo
        )
    ');

    $insertar->execute([
        ':nombre' => $nombre,
        ':password_hash' => $passwordHash,
        ':rol' => 'Administrador',
        ':activo' => 1
    ]);

    $id = $pdo->lastInsertId();

    echo "\n";
    echo "========================================\n";
    echo "Administrador creado correctamente.\n";
    echo "========================================\n";
    echo "ID:       {$id}\n";
    echo "Usuario:  {$nombre}\n";
    echo "Rol:      Administrador\n";
    echo "Activo:   Sí\n";
    echo "========================================\n";
    echo "\n";

} catch (PDOException $e) {
    echo "Error de base de datos:\n";
    echo $e->getMessage() . "\n";
    exit(1);
} catch (Throwable $e) {
    echo "Error:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
