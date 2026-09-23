<?php

declare(strict_types=1);

function guardar_memorandum(array $archivo, int $reporteId): ?array
{
    $error = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir el archivo.');
    }

    $config = require dirname(__DIR__) . '/config/config.php';
    $maxSize = (int)$config['upload']['max_size'];

    if (($archivo['size'] ?? 0) > $maxSize) {
        throw new RuntimeException('El PDF supera el tamaño permitido.');
    }

    $tmp = $archivo['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('No se recibió correctamente el PDF seleccionado.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    if ($mime !== 'application/pdf') {
        throw new RuntimeException('El archivo seleccionado debe ser un PDF válido.');
    }

    $base = $config['upload']['directory'];
    $year = date('Y');
    $directory = "{$base}/{$year}/{$reporteId}";

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo crear el directorio para el PDF.');
    }

    $nombre = bin2hex(random_bytes(16)) . '.pdf';
    $destino = "{$directory}/{$nombre}";

    if (!move_uploaded_file($tmp, $destino)) {
        throw new RuntimeException('No se pudo guardar el PDF seleccionado.');
    }

    return [
        'nombre_original' => basename((string)$archivo['name']),
        'nombre_archivo' => $nombre,
        'ruta' => $destino,
        'tipo_mime' => $mime,
        'tamano' => (int)$archivo['size']
    ];
}
