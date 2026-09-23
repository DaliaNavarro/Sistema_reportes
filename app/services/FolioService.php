<?php

declare(strict_types=1);


function generar_folio(PDO $db): string
{
    $year = (int)date('Y');

    $stmt = $db->prepare(
        "
        SELECT COUNT(*)
        FROM reportes
        WHERE YEAR(fecha_registro) = ?
        "
    );

    $stmt->execute([
        $year
    ]);

    $numero =
        ((int)$stmt->fetchColumn()) + 1;

    do {

        $folio =
            sprintf(
                'TI-%d-%06d',
                $year,
                $numero
            );

        $check = $db->prepare(
            "
            SELECT COUNT(*)
            FROM reportes
            WHERE folio = ?
            "
        );

        $check->execute([
            $folio
        ]);

        if (
            (int)$check->fetchColumn() === 0
        ) {

            return $folio;
        }

        $numero++;

    } while (true);
}