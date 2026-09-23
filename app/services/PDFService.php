<?php

declare(strict_types=1);


use Dompdf\Dompdf;
use Dompdf\Options;


function crear_dompdf(): Dompdf
{
    require_once
        dirname(__DIR__, 2)
        . '/vendor/autoload.php';


    $options =
        new Options();


    $options->set(
        'defaultFont',
        'DejaVu Sans'
    );


    $options->set(
        'isRemoteEnabled',
        false
    );


    return new Dompdf(
        $options
    );
}


/*
PDF 1
REPORTE TRIMESTRAL
*/

function generar_pdf_trimestral(
    PDO $db,
    int $year,
    int $quarter
): void {

    if (
        $quarter < 1 ||
        $quarter > 4
    ) {

        exit(
            'Trimestre inválido.'
        );
    }


    $mesInicio =
        (($quarter - 1) * 3) + 1;


    $fechaInicio =
        sprintf(
            '%04d-%02d-01 00:00:00',
            $year,
            $mesInicio
        );


    $fechaFin =
        date(
            'Y-m-d H:i:s',
            strtotime(
                $fechaInicio .
                ' +3 months'
            )
        );


    /*
     * Aparecen también los departamentos
     * que tuvieron CERO solicitudes.
     */

    $sql = "
        SELECT
            d.id,
            d.nombre,

            COUNT(r.id)
                AS solicitudes

        FROM departamentos d

        LEFT JOIN reportes r

            ON r.departamento_id = d.id

            AND r.fecha_registro >= :inicio

            AND r.fecha_registro < :fin

        WHERE d.activo = 1

        GROUP BY
            d.id,
            d.nombre

        ORDER BY
            d.nombre
    ";


    $stmt =
        $db->prepare($sql);


    $stmt->execute([

        ':inicio' =>
            $fechaInicio,

        ':fin' =>
            $fechaFin
    ]);


    $departamentos =
        $stmt->fetchAll();


    $total = 0;


    foreach (
        $departamentos
        as $departamento
    ) {

        $total +=
            (int)$departamento[
                'solicitudes'
            ];
    }


    $html = '

    <html>

    <head>

    <meta charset="UTF-8">

    <style>

    body {
        font-family: DejaVu Sans;
        font-size: 11px;
    }

    h1 {
        text-align: center;
        margin-bottom: 5px;
    }

    .periodo {
        text-align: center;
        margin-bottom: 25px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #eeeeee;
        font-weight: bold;
    }

    th, td {
        border: 1px solid #333333;
        padding: 9px;
    }

    .numero {
        text-align: center;
        width: 150px;
    }

    .total {
        font-weight: bold;
    }

    </style>

    </head>

    <body>

    <h1>
        REPORTE TRIMESTRAL
    </h1>

    <div class="periodo">

        Solicitudes de servicios de TI

        <br>

        Trimestre ' .
        $quarter .
        ' del año ' .
        $year .

    '</div>

    <table>

        <thead>

            <tr>

                <th>
                    Departamento
                </th>

                <th class="numero">
                    Número de solicitudes
                </th>

            </tr>

        </thead>

        <tbody>
    ';


    foreach (
        $departamentos
        as $departamento
    ) {

        $html .= '

            <tr>

                <td>' .

                htmlspecialchars(
                    $departamento[
                        'nombre'
                    ]
                )

                . '</td>

                <td class="numero">' .

                (int)
                $departamento[
                    'solicitudes'
                ]

                . '</td>

            </tr>

        ';
    }


    $html .= '

            <tr class="total">

                <td>
                    TOTAL
                </td>

                <td class="numero">' .

                $total .

                '</td>

            </tr>

        </tbody>

    </table>

    </body>

    </html>
    ';


    $pdf =
        crear_dompdf();


    $pdf->loadHtml(
        $html,
        'UTF-8'
    );


    $pdf->setPaper(
        'letter',
        'portrait'
    );


    $pdf->render();


    $pdf->stream(
        "reporte-trimestral-"
        . "{$year}-T{$quarter}.pdf",

        [
            'Attachment' => false
        ]
    );


    exit;
}


/*
PDF 2
DEPARTAMENTOS / ENCARGADOS / FIRMAS
*/

function generar_pdf_firmas(
    PDO $db
): void {

    $sql = "

        SELECT

            d.nombre AS departamento,

            COALESCE(
                s.nombre,
                'Sin encargado registrado'
            ) AS encargado

        FROM departamentos d

        LEFT JOIN solicitantes s

            ON s.id =
               d.encargado_id

        WHERE d.activo = 1

        ORDER BY d.nombre

    ";


    $departamentos =
        $db->query($sql)
           ->fetchAll();


    $html = '

    <html>

    <head>

    <meta charset="UTF-8">

    <style>

    body {
        font-family: DejaVu Sans;
        font-size: 10px;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #333333;
        padding: 8px;
    }

    th {
        background: #eeeeee;
    }

    .firma {
        height: 65px;
        vertical-align: bottom;
    }

    .firma-linea {
        margin-top: 45px;
        text-align: center;
    }

    </style>

    </head>

    <body>

    <h1>
        DEPARTAMENTOS Y ENCARGADOS
    </h1>

    <table>

        <thead>

            <tr>

                <th>
                    Departamento
                </th>

                <th>
                    Encargado
                </th>

                <th>
                    Firma
                </th>

            </tr>

        </thead>

        <tbody>
    ';


    foreach (
        $departamentos
        as $departamento
    ) {

        $html .= '

        <tr>

            <td>
                ' .
                htmlspecialchars(
                    $departamento[
                        'departamento'
                    ]
                )
                . '
            </td>

            <td>
                ' .
                htmlspecialchars(
                    $departamento[
                        'encargado'
                    ]
                )
                . '
            </td>

            <td class="firma">

                <div class="firma-linea">
                    ______________________
                </div>

            </td>

        </tr>

        ';
    }


    $html .= '

        </tbody>

    </table>

    </body>

    </html>
    ';


    $pdf =
        crear_dompdf();


    $pdf->loadHtml(
        $html,
        'UTF-8'
    );


    $pdf->setPaper(
        'letter',
        'portrait'
    );


    $pdf->render();


    $pdf->stream(
        'departamentos_firmas.pdf',

        [
            'Attachment' => false
        ]
    );


    exit;
}