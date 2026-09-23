<?php

class DashboardController
{
    public static function index(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $total = (int)$db->query('SELECT COUNT(*) FROM reportes')->fetchColumn();
        $pendientes = (int)$db->query("SELECT COUNT(*) FROM reportes WHERE estado = 'REGISTRADO'")->fetchColumn();
        $proceso = (int)$db->query("SELECT COUNT(*) FROM reportes WHERE estado = 'EN_PROCESO'")->fetchColumn();
        $atendidos = (int)$db->query("SELECT COUNT(*) FROM reportes WHERE estado = 'ATENDIDO'")->fetchColumn();

        view('dashboard/index', compact('total', 'pendientes', 'proceso', 'atendidos'));
    }
}
