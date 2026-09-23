<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function descargar_xlsx(
    Spreadsheet $spreadsheet,
    string $filename
): never {
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

    header(
        'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );
    header(
        'Content-Disposition: attachment; filename="' .
        basename($filename) .
        '"'
    );
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

function crear_hoja_base(string $titulo): array
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($titulo);

    return [$spreadsheet, $sheet];
}

function estilo_encabezado($sheet, string $rango): void
{
    $sheet->getStyle($rango)->getFont()->setBold(true);
    $sheet->getStyle($rango)->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->getStyle($rango)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('D9E1F2');

    $sheet->getStyle($rango)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
}

function auto_ajustar_columnas($sheet, array $columnas): void
{
    foreach ($columnas as $columna) {
        $sheet->getColumnDimension($columna)->setAutoSize(true);
    }
}
