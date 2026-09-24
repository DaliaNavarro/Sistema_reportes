<?php

class ReporteController
{
    public static function index(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $estado = strtoupper(trim((string)($_GET['estado'] ?? '')));
        $departamentoId = (int)($_GET['departamento_id'] ?? 0);
        $tipoId = (int)($_GET['tipo_problema_id'] ?? 0);
        $origen = strtoupper(trim((string)($_GET['origen'] ?? '')));
        $solicitante = trim((string)($_GET['solicitante'] ?? ''));

        $estadosPermitidos = ['REGISTRADO', 'EN_PROCESO', 'ATENDIDO', 'CANCELADO'];
        $origenesPermitidos = ['MEMORANDUM', 'INFORMAL'];

        $sql = "
            SELECT
                r.id,
                r.folio,
                r.fecha_solicitud,
                r.origen,
                r.estado,
                r.es_departamento_general,
                d.nombre AS departamento,
                p.nombre AS persona,
                encargado.nombre AS encargado,
                t.nombre AS tipo
            FROM reportes r
            INNER JOIN departamentos d ON d.id = r.departamento_id
            LEFT JOIN personas p ON p.id = r.persona_id
            LEFT JOIN personas encargado ON encargado.id = d.encargado_id
            INNER JOIN tipos_problema t ON t.id = r.tipo_problema_id
            WHERE 1 = 1
        ";

        $params = [];

        if (in_array($estado, $estadosPermitidos, true)) {
            $sql .= ' AND r.estado = ?';
            $params[] = $estado;
        } else {
            $estado = '';
        }

        if ($departamentoId > 0) {
            $sql .= ' AND r.departamento_id = ?';
            $params[] = $departamentoId;
        }

        if ($tipoId > 0) {
            $sql .= ' AND r.tipo_problema_id = ?';
            $params[] = $tipoId;
        }

        if (in_array($origen, $origenesPermitidos, true)) {
            $sql .= ' AND r.origen = ?';
            $params[] = $origen;
        } else {
            $origen = '';
        }

        if ($solicitante !== '') {
            $sql .= ' AND (p.nombre LIKE ? OR (r.es_departamento_general = 1 AND encargado.nombre LIKE ?))';
            $busqueda = '%' . $solicitante . '%';
            $params[] = $busqueda;
            $params[] = $busqueda;
        }

        $sql .= ' ORDER BY r.fecha_solicitud DESC, r.id DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $departamentos = $db->query(
            "SELECT id, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre"
        )->fetchAll();

        $tipos = $db->query(
            "SELECT id, nombre FROM tipos_problema WHERE activo = 1 ORDER BY nombre"
        )->fetchAll();

        view('reportes/index', [
            'reportes' => $stmt->fetchAll(),
            'estadoFiltro' => $estado,
            'departamentoFiltro' => $departamentoId,
            'tipoFiltro' => $tipoId,
            'origenFiltro' => $origen,
            'solicitanteFiltro' => $solicitante,
            'departamentos' => $departamentos,
            'tipos' => $tipos
        ]);
    }

    public static function nuevo(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $departamentos = $db->query("SELECT id, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre")->fetchAll();
        $personas = $db->query("SELECT id, nombre, departamento_id FROM personas WHERE activo = 1 ORDER BY nombre")->fetchAll();
        $tipos = $db->query("SELECT id, nombre, descripcion FROM tipos_problema WHERE activo = 1 ORDER BY nombre")->fetchAll();

        view('reportes/form', [
            'departamentos' => $departamentos,
            'personas' => $personas,
            'tipos' => $tipos
        ]);
    }

    public static function guardar(): void
    {
        Auth::requireLogin();
        verify_csrf();

        $db = Database::getConnection();

        $departamentoId = (int)($_POST['departamento_id'] ?? 0);
        $personaId = !empty($_POST['persona_id']) ? (int)$_POST['persona_id'] : null;
        $general = isset($_POST['es_departamento_general']) ? 1 : 0;
        $tipoId = (int)($_POST['tipo_problema_id'] ?? 0);
        $origen = $_POST['origen'] ?? 'INFORMAL';
        $numeroOficio = trim($_POST['numero_oficio'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fechaSolicitudInput = trim($_POST['fecha_solicitud'] ?? '');
        $estado = $_POST['estado'] ?? 'REGISTRADO';

        $permitidos = ['REGISTRADO', 'EN_PROCESO', 'ATENDIDO', 'CANCELADO'];

        if ($fechaSolicitudInput === '') {
            $fechaSolicitudInput = date('Y-m-d\\TH:i');
        }

        $fechaSolicitud = DateTime::createFromFormat('Y-m-d\\TH:i', $fechaSolicitudInput);
        if (!$fechaSolicitud || $fechaSolicitud->format('Y-m-d\\TH:i') !== $fechaSolicitudInput) {
            $fechaSolicitud = DateTime::createFromFormat('Y-m-d H:i:s', $fechaSolicitudInput);
        }

        if (!$fechaSolicitud) {
            flash('error', 'La fecha del reporte no es válida.');
            redirect('reportes/nuevo');
        }

        if (
            $departamentoId <= 0 ||
            $tipoId <= 0 ||
            $descripcion === '' ||
            !in_array($origen, ['MEMORANDUM', 'INFORMAL'], true) ||
            !in_array($estado, $permitidos, true)
        ) {
            flash('error', 'Complete los campos obligatorios.');
            redirect('reportes/nuevo');
        }

        if ($general) {
            $personaId = null;
        } elseif ($personaId === null) {
            flash('error', 'Seleccione una persona o marque "Para todo el departamento".');
            redirect('reportes/nuevo');
        }

        $fechaSolicitudSql = $fechaSolicitud->format('Y-m-d H:i:s');
        $fechaAtencion = $estado === 'ATENDIDO' ? $fechaSolicitudSql : null;
        $archivoSubido = $_FILES['memorandum'] ?? null;

        try {
            $db->beginTransaction();

            $folio = generate_folio($db, $fechaSolicitudSql);

            $stmt = $db->prepare("
                INSERT INTO reportes
                (
                    folio, fecha_solicitud, departamento_id, persona_id,
                    es_departamento_general, tipo_problema_id, origen,
                    numero_oficio, descripcion, estado, fecha_atencion,
                    usuario_registro_id
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $folio,
                $fechaSolicitudSql,
                $departamentoId,
                $personaId,
                $general,
                $tipoId,
                $origen,
                $numeroOficio ?: null,
                $descripcion,
                $estado,
                $fechaAtencion,
                Auth::user()['id']
            ]);

            $reporteId = (int)$db->lastInsertId();

            if ($archivoSubido !== null) {
                $archivo = guardar_memorandum($archivoSubido, $reporteId);

                if ($archivo !== null) {
                    $stmt = $db->prepare("
                        INSERT INTO archivos_reportes
                        (reporte_id, nombre_original, nombre_archivo, ruta, tipo_mime, tamano)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $reporteId,
                        $archivo['nombre_original'],
                        $archivo['nombre_archivo'],
                        $archivo['ruta'],
                        $archivo['tipo_mime'],
                        $archivo['tamano']
                    ]);
                }
            }

            $stmt = $db->prepare("
                INSERT INTO historial_reportes
                (reporte_id, usuario_id, estado_anterior, estado_nuevo, comentario)
                VALUES (?, ?, NULL, ?, ?)
            ");
            $stmt->execute([
                $reporteId,
                Auth::user()['id'],
                $estado,
                'Reporte registrado.'
            ]);

            $db->commit();

            flash('success', "Reporte $folio registrado correctamente.");
            redirect('reportes');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            flash('error', $e->getMessage());
            redirect('reportes/nuevo');
        }
    }

    public static function ver(): void
    {
        Auth::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT
                r.*, d.nombre AS departamento, p.nombre AS persona,
                encargado.nombre AS encargado, t.nombre AS tipo,
                t.descripcion AS tipo_descripcion, u.nombre AS usuario_registro
            FROM reportes r
            INNER JOIN departamentos d ON d.id = r.departamento_id
            LEFT JOIN personas p ON p.id = r.persona_id
            LEFT JOIN personas encargado ON encargado.id = d.encargado_id
            INNER JOIN tipos_problema t ON t.id = r.tipo_problema_id
            INNER JOIN usuarios_sistema u ON u.id = r.usuario_registro_id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $reporte = $stmt->fetch();

        if (!$reporte) {
            http_response_code(404);
            exit('Reporte no encontrado.');
        }

        $stmt = $db->prepare("SELECT * FROM archivos_reportes WHERE reporte_id = ? ORDER BY fecha_subida DESC");
        $stmt->execute([$id]);
        $archivos = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT h.*, u.nombre AS usuario
            FROM historial_reportes h
            LEFT JOIN usuarios_sistema u ON u.id = h.usuario_id
            WHERE h.reporte_id = ?
            ORDER BY h.fecha DESC
        ");
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll();

        view('reportes/ver', [
            'reporte' => $reporte,
            'archivos' => $archivos,
            'historial' => $historial
        ]);
    }

    public static function adjuntarArchivo(): void
    {
        Auth::requireLogin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        $archivoSubido = $_FILES['memorandum'] ?? null;

        if ($id <= 0) {
            flash('error', 'Reporte inválido.');
            redirect('reportes');
        }

        if ($archivoSubido === null) {
            flash('error', 'Seleccione un archivo PDF.');
            header('Location: index.php?route=reportes/ver&id=' . $id);
            exit;
        }

        $db = Database::getConnection();
        $rutaGuardada = null;

        try {
            $stmt = $db->prepare('SELECT id FROM reportes WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);

            if (!$stmt->fetch()) {
                throw new RuntimeException('Reporte no encontrado.');
            }

            $archivo = guardar_memorandum($archivoSubido, $id);
            if ($archivo === null) {
                throw new RuntimeException('Seleccione un archivo PDF.');
            }

            $rutaGuardada = $archivo['ruta'];

            $stmt = $db->prepare("
                INSERT INTO archivos_reportes
                (reporte_id, nombre_original, nombre_archivo, ruta, tipo_mime, tamano)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $archivo['nombre_original'],
                $archivo['nombre_archivo'],
                $archivo['ruta'],
                $archivo['tipo_mime'],
                $archivo['tamano']
            ]);

            flash('success', 'PDF adjuntado correctamente.');
        } catch (Throwable $e) {
            if ($rutaGuardada !== null && is_file($rutaGuardada)) {
                @unlink($rutaGuardada);
            }
            flash('error', $e->getMessage());
        }

        header('Location: index.php?route=reportes/ver&id=' . $id);
        exit;
    }

    public static function cambiarEstado(): void
    {
        Auth::requireLogin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        $permitidos = ['REGISTRADO', 'EN_PROCESO', 'ATENDIDO', 'CANCELADO'];

        if ($id <= 0 || !in_array($estado, $permitidos, true)) {
            flash('error', 'Datos de estado inválidos.');
            redirect('reportes');
        }

        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT estado, fecha_solicitud FROM reportes WHERE id = ?");
            $stmt->execute([$id]);
            $reporte = $stmt->fetch();

            if (!$reporte) {
                throw new RuntimeException('Reporte no encontrado.');
            }

            $estadoAnterior = $reporte['estado'];
            $fechaAtencion = $estado === 'ATENDIDO' ? date('Y-m-d H:i:s') : null;

            $stmt = $db->prepare("UPDATE reportes SET estado = ?, fecha_atencion = ? WHERE id = ?");
            $stmt->execute([$estado, $fechaAtencion, $id]);

            $stmt = $db->prepare("
                INSERT INTO historial_reportes
                (reporte_id, usuario_id, estado_anterior, estado_nuevo, comentario)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                Auth::user()['id'],
                $estadoAnterior,
                $estado,
                'Cambio de estado'
            ]);

            $db->commit();

            header('Location: index.php?route=reportes/ver&id=' . $id);
            exit;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', $e->getMessage());
            header('Location: index.php?route=reportes/ver&id=' . $id);
            exit;
        }
    }

    public static function eliminar(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Reporte inválido.');
            redirect('reportes');
        }

        $db = Database::getConnection();
        $archivos = [];

        try {
            $stmt = $db->prepare('SELECT ruta FROM archivos_reportes WHERE reporte_id = ?');
            $stmt->execute([$id]);
            $archivos = $stmt->fetchAll();

            $stmt = $db->prepare('SELECT folio FROM reportes WHERE id = ?');
            $stmt->execute([$id]);
            $reporte = $stmt->fetch();

            if (!$reporte) {
                flash('error', 'Reporte no encontrado.');
                redirect('reportes');
            }

            $db->beginTransaction();
            $stmt = $db->prepare('DELETE FROM reportes WHERE id = ?');
            $stmt->execute([$id]);
            $db->commit();

            foreach ($archivos as $archivo) {
                if (!empty($archivo['ruta']) && is_file($archivo['ruta'])) {
                    @unlink($archivo['ruta']);
                }
            }

            flash('success', 'Reporte ' . $reporte['folio'] . ' eliminado.');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', 'No se pudo eliminar el reporte: ' . $e->getMessage());
        }

        redirect('reportes');
    }

    public static function archivo(): void
    {
        Auth::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT * FROM archivos_reportes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $archivo = $stmt->fetch();

        if (!$archivo || !is_file($archivo['ruta'])) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        header('Content-Type: ' . ($archivo['tipo_mime'] ?: 'application/pdf'));
        header('Content-Disposition: inline; filename="' . basename($archivo['nombre_original']) . '"');
        header('Content-Length: ' . filesize($archivo['ruta']));
        readfile($archivo['ruta']);
        exit;
    }
}
