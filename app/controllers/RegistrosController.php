<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RegistrosController
{
    public static function index(): void
    {
        Auth::requireLogin();
        view('registros/index');
    }

    public static function importarPersonas(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        try {
            $archivo = self::archivoSubido('archivo');

            $spreadsheet = IOFactory::load($archivo['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $db = Database::getConnection();
            $db->beginTransaction();

            $departamentos = [];
            $stmt = $db->query("SELECT id, nombre FROM departamentos");
            foreach ($stmt->fetchAll() as $departamento) {
                $departamentos[mb_strtolower(trim($departamento['nombre']))] = (int)$departamento['id'];
            }

            $insertados = 0;
            $actualizados = 0;
            $errores = [];

            foreach ($rows as $numero => $row) {
                if ($numero === 1) {
                    continue;
                }

                $nombre = trim((string)($row['A'] ?? ''));
                $departamento = trim((string)($row['B'] ?? ''));

                if ($nombre === '' && $departamento === '') {
                    continue;
                }

                if ($nombre === '' || $departamento === '') {
                    $errores[] = "Fila {$numero}: nombre y departamento son obligatorios.";
                    continue;
                }

                $claveDepartamento = mb_strtolower($departamento);

                if (!isset($departamentos[$claveDepartamento])) {
                    $errores[] = 'Fila ' . $numero . ': no existe el departamento "' . $departamento . '".';
                    continue;
                }

                $departamentoId = $departamentos[$claveDepartamento];

                $stmt = $db->prepare("
                    SELECT id
                    FROM personas
                    WHERE nombre = ?
                      AND departamento_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$nombre, $departamentoId]);
                $existente = $stmt->fetch();

                if ($existente) {
                    $stmt = $db->prepare("
                        UPDATE personas
                        SET activo = 1
                        WHERE id = ?
                    ");
                    $stmt->execute([(int)$existente['id']]);
                    $actualizados++;
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO personas (departamento_id, nombre)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$departamentoId, $nombre]);
                    $insertados++;
                }
            }

            if ($errores) {
                $db->rollBack();
                throw new RuntimeException(
                    'Importación cancelada. ' .
                    implode(' ', array_slice($errores, 0, 8))
                );
            }

            $db->commit();

            flash(
                'success',
                "Personas importadas: {$insertados} nuevas y {$actualizados} actualizadas."
            );

        } catch (Throwable $e) {
            if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }

            flash('error', $e->getMessage());
        }

        redirect('registros');
    }

    public static function exportarPersonas(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $rows = $db->query("
            SELECT
                p.nombre,
                d.nombre AS departamento
            FROM personas p
            INNER JOIN departamentos d
                ON d.id = p.departamento_id
            ORDER BY d.nombre, p.nombre
        ")->fetchAll();

        [$spreadsheet, $sheet] = crear_hoja_base('Personas');

        $sheet->fromArray(
            [['Nombre', 'Departamento']],
            null,
            'A1'
        );

        $fila = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(
                [[
                    $row['nombre'],
                    $row['departamento']
                ]],
                null,
                "A{$fila}"
            );
            $fila++;
        }

        estilo_encabezado($sheet, 'A1:B1');
        auto_ajustar_columnas($sheet, ['A', 'B']);

        descargar_xlsx($spreadsheet, 'personas.xlsx');
    }

    public static function importarAtenciones(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        try {
            $archivo = self::archivoSubido('archivo');

            $spreadsheet = IOFactory::load($archivo['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $db = Database::getConnection();
            $db->beginTransaction();

            $nuevas = 0;
            $actualizadas = 0;
            $errores = [];

            foreach ($rows as $numero => $row) {
                if ($numero === 1) {
                    continue;
                }

                $nombre = trim((string)($row['A'] ?? ''));
                $descripcion = trim((string)($row['B'] ?? ''));

                if ($nombre === '' && $descripcion === '') {
                    continue;
                }

                if ($nombre === '') {
                    $errores[] = "Fila {$numero}: la atención es obligatoria.";
                    continue;
                }

                $stmt = $db->prepare("
                    SELECT id
                    FROM tipos_problema
                    WHERE nombre = ?
                    LIMIT 1
                ");
                $stmt->execute([$nombre]);
                $existente = $stmt->fetch();

                if ($existente) {
                    $stmt = $db->prepare("
                        UPDATE tipos_problema
                        SET descripcion = ?,
                            activo = 1
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $descripcion !== '' ? $descripcion : null,
                        (int)$existente['id']
                    ]);
                    $actualizadas++;
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO tipos_problema (nombre, descripcion)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([
                        $nombre,
                        $descripcion !== '' ? $descripcion : null
                    ]);
                    $nuevas++;
                }
            }

            if ($errores) {
                $db->rollBack();
                throw new RuntimeException(
                    'Importación cancelada. ' .
                    implode(' ', array_slice($errores, 0, 8))
                );
            }

            $db->commit();

            flash(
                'success',
                "Atenciones importadas: {$nuevas} nuevas y {$actualizadas} actualizadas."
            );

        } catch (Throwable $e) {
            if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }

            flash('error', $e->getMessage());
        }

        redirect('registros');
    }

    public static function exportarAtenciones(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $rows = $db->query("
            SELECT nombre, descripcion
            FROM tipos_problema
            ORDER BY nombre
        ")->fetchAll();

        [$spreadsheet, $sheet] = crear_hoja_base('Atenciones');

        $sheet->fromArray(
            [['Atención', 'Descripción']],
            null,
            'A1'
        );

        $fila = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(
                [[
                    $row['nombre'],
                    $row['descripcion'] ?? ''
                ]],
                null,
                "A{$fila}"
            );
            $fila++;
        }

        estilo_encabezado($sheet, 'A1:B1');
        $sheet->getColumnDimension('B')->setWidth(70);
        $sheet->getStyle('B2:B' . max(2, $fila - 1))
            ->getAlignment()->setWrapText(true);

        auto_ajustar_columnas($sheet, ['A']);

        descargar_xlsx($spreadsheet, 'atenciones.xlsx');
    }

    public static function importarReportes(): void
    {
        Auth::requireAdmin();
        verify_csrf();

        try {
            $archivo = self::archivoSubido('archivo');
            $spreadsheet = IOFactory::load($archivo['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $db = Database::getConnection();
            $db->beginTransaction();

            $departamentos = [];
            foreach ($db->query('SELECT id, nombre FROM departamentos')->fetchAll() as $row) {
                $departamentos[mb_strtolower(trim($row['nombre']))] = (int)$row['id'];
            }

            $atenciones = [];
            foreach ($db->query('SELECT id, nombre FROM tipos_problema')->fetchAll() as $row) {
                $atenciones[mb_strtolower(trim($row['nombre']))] = (int)$row['id'];
            }

            $insertados = 0;
            $errores = [];

            foreach ($rows as $numero => $row) {
                if ($numero === 1) {
                    continue;
                }

                $folio = trim((string)($row['A'] ?? ''));
                $fechaTexto = trim((string)($row['B'] ?? ''));
                $departamento = trim((string)($row['C'] ?? ''));
                $persona = trim((string)($row['D'] ?? ''));
                $generalTexto = trim((string)($row['E'] ?? ''));
                $atencion = trim((string)($row['F'] ?? ''));
                $origen = strtoupper(trim((string)($row['G'] ?? 'INFORMAL')));
                $oficio = trim((string)($row['H'] ?? ''));
                $descripcion = trim((string)($row['I'] ?? ''));
                $estado = strtoupper(trim((string)($row['J'] ?? 'REGISTRADO')));
                $fechaAtencionTexto = trim((string)($row['K'] ?? ''));

                if ($folio === '' && $fechaTexto === '' && $departamento === '' && $persona === '' && $atencion === '' && $descripcion === '') {
                    continue;
                }

                if ($departamento === '' || $atencion === '' || $descripcion === '' || $fechaTexto === '') {
                    $errores[] = "Fila {$numero}: fecha, departamento, atención y descripción son obligatorios.";
                    continue;
                }

                $fecha = self::parseFechaExcel($fechaTexto, $row['B'] ?? null);
                if (!$fecha) {
                    $errores[] = "Fila {$numero}: la fecha no es válida.";
                    continue;
                }

                $claveDepartamento = mb_strtolower($departamento);
                $claveAtencion = mb_strtolower($atencion);
                if (!isset($departamentos[$claveDepartamento])) {
                    $errores[] = "Fila {$numero}: no existe el departamento \"{$departamento}\".";
                    continue;
                }
                if (!isset($atenciones[$claveAtencion])) {
                    $errores[] = "Fila {$numero}: no existe la atención \"{$atencion}\".";
                    continue;
                }

                $departamentoId = $departamentos[$claveDepartamento];
                $tipoId = $atenciones[$claveAtencion];
                $general = in_array(mb_strtolower($generalTexto), ['si', 'sí', '1', 'true', 'x', 'yes'], true) ? 1 : 0;
                $personaId = null;

                if (!$general) {
                    if ($persona === '') {
                        $errores[] = "Fila {$numero}: indique la persona o marque Todo el departamento.";
                        continue;
                    }
                    $stmt = $db->prepare('SELECT id FROM personas WHERE nombre = ? AND departamento_id = ? LIMIT 1');
                    $stmt->execute([$persona, $departamentoId]);
                    $personaRow = $stmt->fetch();
                    if (!$personaRow) {
                        $errores[] = "Fila {$numero}: no existe la persona \"{$persona}\" en el departamento indicado.";
                        continue;
                    }
                    $personaId = (int)$personaRow['id'];
                }

                if (!in_array($origen, ['MEMORANDUM', 'INFORMAL'], true)) {
                    $origen = 'INFORMAL';
                }
                if (!in_array($estado, ['REGISTRADO', 'EN_PROCESO', 'ATENDIDO', 'CANCELADO'], true)) {
                    $errores[] = "Fila {$numero}: estado inválido.";
                    continue;
                }

                if ($folio !== '') {
                    $stmt = $db->prepare('SELECT id FROM reportes WHERE folio = ? LIMIT 1');
                    $stmt->execute([$folio]);
                    if ($stmt->fetch()) {
                        $errores[] = "Fila {$numero}: el folio \"{$folio}\" ya existe.";
                        continue;
                    }
                } else {
                    $folio = generate_folio($db, $fecha);
                }

                $fechaAtencion = null;
                if ($fechaAtencionTexto !== '') {
                    $fechaAtencion = self::parseFechaExcel($fechaAtencionTexto, $row['K'] ?? null);
                    if (!$fechaAtencion) {
                        $errores[] = "Fila {$numero}: la fecha de atención no es válida.";
                        continue;
                    }
                } elseif ($estado === 'ATENDIDO') {
                    $fechaAtencion = $fecha;
                }

                $stmt = $db->prepare(''
                    . 'INSERT INTO reportes '
                    . '(folio, fecha_solicitud, departamento_id, persona_id, es_departamento_general, tipo_problema_id, origen, numero_oficio, descripcion, estado, fecha_atencion, usuario_registro_id) '
                    . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $folio,
                    $fecha,
                    $departamentoId,
                    $personaId,
                    $general,
                    $tipoId,
                    $origen,
                    $oficio !== '' ? $oficio : null,
                    $descripcion,
                    $estado,
                    $fechaAtencion,
                    Auth::user()['id']
                ]);

                $reporteId = (int)$db->lastInsertId();
                $stmt = $db->prepare('INSERT INTO historial_reportes (reporte_id, usuario_id, estado_anterior, estado_nuevo, comentario) VALUES (?, ?, NULL, ?, ?)');
                $stmt->execute([$reporteId, Auth::user()['id'], $estado, 'Importado desde archivo XLSX.']);
                $insertados++;
            }

            if ($errores) {
                $db->rollBack();
                throw new RuntimeException('Importación cancelada. ' . implode(' ', array_slice($errores, 0, 10)));
            }

            $db->commit();
            flash('success', "Reportes importados: {$insertados}.");
        } catch (Throwable $e) {
            if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', $e->getMessage());
        }

        redirect('registros');
    }

    public static function exportarReportes(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();
        $rows = $db->query("
            SELECT
                r.folio,
                r.fecha_solicitud,
                d.nombre AS departamento,
                CASE WHEN r.es_departamento_general = 1 THEN '' ELSE COALESCE(p.nombre, '') END AS persona,
                r.es_departamento_general,
                t.nombre AS atencion,
                r.origen,
                r.numero_oficio,
                r.descripcion,
                r.estado,
                r.fecha_atencion
            FROM reportes r
            INNER JOIN departamentos d ON d.id = r.departamento_id
            LEFT JOIN personas p ON p.id = r.persona_id
            INNER JOIN tipos_problema t ON t.id = r.tipo_problema_id
            ORDER BY r.fecha_solicitud, r.id
        ")->fetchAll();

        [$spreadsheet, $sheet] = crear_hoja_base('Reportes');
        $sheet->fromArray([[
            'Folio', 'Fecha', 'Departamento', 'Persona', 'Todo el departamento',
            'Atención', 'Origen', 'Número de oficio', 'Descripción', 'Estado', 'Fecha de atención'
        ]], null, 'A1');

        $fila = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([[
                $row['folio'],
                date('d/m/Y H:i', strtotime($row['fecha_solicitud'])),
                $row['departamento'],
                $row['persona'],
                $row['es_departamento_general'] ? 'Sí' : 'No',
                $row['atencion'],
                $row['origen'],
                $row['numero_oficio'] ?? '',
                $row['descripcion'],
                $row['estado'],
                $row['fecha_atencion'] ? date('d/m/Y H:i', strtotime($row['fecha_atencion'])) : ''
            ]], null, "A{$fila}");
            $fila++;
        }

        $ultima = max(2, $fila - 1);
        estilo_encabezado($sheet, 'A1:K1');
        $sheet->getStyle("I2:I{$ultima}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A1:K{$ultima}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (['A'=>18,'B'=>20,'C'=>28,'D'=>28,'E'=>20,'F'=>28,'G'=>16,'H'=>24,'I'=>60,'J'=>18,'K'=>20] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->freezePane('A2');
        descargar_xlsx($spreadsheet, 'reportes.xlsx');
    }

    private static function parseFechaExcel(string $texto, mixed $valor = null): ?string
    {
        if (is_numeric($valor) && (float)$valor > 0) {
            try {
                $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$valor);
                return $fecha->format('Y-m-d H:i:s');
            } catch (Throwable) {
                // Continúa con formatos de texto.
            }
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y', 'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y'] as $formato) {
            $fecha = DateTime::createFromFormat($formato, $texto);
            if ($fecha !== false) {
                return $fecha->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    public static function formTrimestral(): void
    {
        Auth::requireLogin();

        view('registros/trimestral', [
            'anioActual' => (int)date('Y')
        ]);
    }

    public static function exportarTrimestral(): void
    {
        Auth::requireLogin();

        $year = (int)($_GET['anio'] ?? date('Y'));
        $quarter = (int)($_GET['trimestre'] ?? 0);

        if ($year < 2000 || $year > 2100 || $quarter < 1 || $quarter > 4) {
            flash('error', 'El año o trimestre seleccionado no es válido.');
            redirect('registros/trimestral');
        }

        $inicioMes = (($quarter - 1) * 3) + 1;
        $inicio = sprintf('%04d-%02d-01 00:00:00', $year, $inicioMes);
        $fin = date('Y-m-d H:i:s', strtotime($inicio . ' +3 months'));

        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT
                r.folio,
                r.fecha_solicitud,
                r.fecha_atencion,
                CASE
                    WHEN r.es_departamento_general = 1
                        THEN COALESCE(encargado.nombre, 'Sin encargado')
                    ELSE COALESCE(p.nombre, 'Sin solicitante')
                END AS persona,
                d.nombre AS departamento,
                t.nombre AS atencion,
                r.descripcion
            FROM reportes r
            INNER JOIN departamentos d
                ON d.id = r.departamento_id
            LEFT JOIN personas p
                ON p.id = r.persona_id
            LEFT JOIN personas encargado
                ON encargado.id = d.encargado_id
            INNER JOIN tipos_problema t
                ON t.id = r.tipo_problema_id
            WHERE r.fecha_solicitud >= ?
              AND r.fecha_solicitud < ?
            ORDER BY r.fecha_solicitud, r.id
        ");

        $stmt->execute([$inicio, $fin]);
        $rows = $stmt->fetchAll();

        [$spreadsheet, $sheet] = crear_hoja_base('Trimestral');

        $sheet->fromArray([[
            'No.',
            'Folio',
            'Nombre',
            'Departamento',
            'Fecha de atención',
            'Actividad realizada',
            'Descripción de la actividad',
            'Firma'
        ]], null, 'A1');

        $fila = 2;
        $numero = 1;

        foreach ($rows as $row) {
            $fecha = $row['fecha_atencion'] ?: $row['fecha_solicitud'];

            $sheet->fromArray([[
                $numero,
                $row['folio'],
                $row['persona'],
                $row['departamento'],
                date('d/m/Y', strtotime($fecha)),
                $row['atencion'],
                $row['descripcion'],
                ''
            ]], null, "A{$fila}");

            $sheet->getRowDimension($fila)->setRowHeight(42);
            $numero++;
            $fila++;
        }

        $ultima = max(2, $fila - 1);

        estilo_encabezado($sheet, 'A1:H1');

        $sheet->freezePane('A2');
        $sheet->getStyle("A1:H{$ultima}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("G2:H{$ultima}")
            ->getAlignment()->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->getStyle("H2:H{$ultima}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('G')->setWidth(55);
        $sheet->getColumnDimension('H')->setWidth(25);

        descargar_xlsx(
            $spreadsheet,
            "reportes_{$year}_T{$quarter}.xlsx"
        );
    }

    public static function formAnual(): void
    {
        Auth::requireLogin();

        view('registros/anual', [
            'anioActual' => (int)date('Y')
        ]);
    }

    public static function exportarAnual(): void
    {
        Auth::requireLogin();

        $year = (int)($_GET['anio'] ?? date('Y'));

        if ($year < 2000 || $year > 2100) {
            flash('error', 'El año seleccionado no es válido.');
            redirect('registros/anual');
        }

        $db = Database::getConnection();

        $inicio = sprintf('%04d-01-01 00:00:00', $year);
        $fin = sprintf('%04d-01-01 00:00:00', $year + 1);

        $stmt = $db->prepare("
            SELECT
                d.id,
                d.nombre AS departamento,
                MONTH(r.fecha_solicitud) AS mes,
                COUNT(r.id) AS total
            FROM departamentos d
            LEFT JOIN reportes r
                ON r.departamento_id = d.id
                AND r.fecha_solicitud >= ?
                AND r.fecha_solicitud < ?
            WHERE d.activo = 1
            GROUP BY d.id, d.nombre, MONTH(r.fecha_solicitud)
            ORDER BY d.nombre, mes
        ");

        $stmt->execute([$inicio, $fin]);

        $datos = [];

        foreach ($stmt->fetchAll() as $row) {
            $id = (int)$row['id'];

            if (!isset($datos[$id])) {
                $datos[$id] = [
                    'departamento' => $row['departamento'],
                    'meses' => array_fill(1, 12, 0)
                ];
            }

            if ($row['mes'] !== null) {
                $datos[$id]['meses'][(int)$row['mes']] = (int)$row['total'];
            }
        }

        [$spreadsheet, $sheet] = crear_hoja_base('Concentrado anual');

        $encabezados = [
            'Departamento',
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre',
            'Total'
        ];

        $sheet->fromArray([$encabezados], null, 'A1');

        $fila = 2;

        foreach ($datos as $dato) {
            $meses = $dato['meses'];
            $total = array_sum($meses);

            $sheet->fromArray([array_merge(
                [$dato['departamento']],
                array_values($meses),
                [$total]
            )], null, "A{$fila}");

            $fila++;
        }

        $ultima = max(2, $fila - 1);

        estilo_encabezado($sheet, 'A1:N1');

        $sheet->getStyle("A1:N{$ultima}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(32);

        foreach (range('B', 'N') as $columna) {
            $sheet->getColumnDimension($columna)->setWidth(14);
        }

        $sheet->freezePane('B2');

        descargar_xlsx(
            $spreadsheet,
            "concentrado_anual_{$year}.xlsx"
        );
    }

    private static function archivoSubido(string $campo): array
    {
        if (
            !isset($_FILES[$campo]) ||
            ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        ) {
            throw new RuntimeException(
                'Seleccione un archivo XLSX válido.'
            );
        }

        $archivo = $_FILES[$campo];

        $extension = strtolower(
            pathinfo($archivo['name'], PATHINFO_EXTENSION)
        );

        if ($extension !== 'xlsx') {
            throw new RuntimeException(
                'El archivo debe tener formato .xlsx.'
            );
        }

        return $archivo;
    }
}
