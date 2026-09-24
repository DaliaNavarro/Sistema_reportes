<?php

session_start();

$config = require dirname(__DIR__) . '/app/config/config.php';

date_default_timezone_set($config['timezone']);

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/app/core/Database.php';
require dirname(__DIR__) . '/app/core/Auth.php';
require dirname(__DIR__) . '/app/core/helpers.php';
require dirname(__DIR__) . '/app/services/ArchivoService.php';
require dirname(__DIR__) . '/app/services/SpreadsheetService.php';

require dirname(__DIR__) . '/app/controllers/AuthController.php';
require dirname(__DIR__) . '/app/controllers/DashboardController.php';
require dirname(__DIR__) . '/app/controllers/ReporteController.php';
require dirname(__DIR__) . '/app/controllers/PersonaController.php';
require dirname(__DIR__) . '/app/controllers/DepartamentoController.php';
require dirname(__DIR__) . '/app/controllers/TipoProblemaController.php';
require dirname(__DIR__) . '/app/controllers/RegistrosController.php';
require dirname(__DIR__) . '/app/controllers/UsuarioController.php';


$route = $_GET['route'] ?? 'dashboard';


switch ($route) {

    /*
     * AUTENTICACIÓN
     */

    case 'login':
        AuthController::login();
        break;

    case 'logout':
        AuthController::logout();
        break;


    /*
     * DASHBOARD
     */

    case 'dashboard':
        Auth::requireLogin();

        DashboardController::index();
        break;


    /*
     * REPORTES
     */

    case 'reportes':
        ReporteController::index();
        break;

    case 'reportes/nuevo':
        ReporteController::nuevo();
        break;

    case 'reportes/guardar':
        ReporteController::guardar();
        break;

    case 'reportes/ver':
        ReporteController::ver();
        break;

    case 'reportes/estado':
        ReporteController::cambiarEstado();
        break;

    case 'reportes/adjuntar':
        ReporteController::adjuntarArchivo();
        break;

    case 'reportes/eliminar':
        ReporteController::eliminar();
        break;

    case 'archivo/ver':
        ReporteController::archivo();
        break;


    /*
     * PERSONAS
     */

    case 'personas':
        PersonaController::index();
        break;

    case 'personas/guardar':
        PersonaController::guardar();
        break;

    case 'personas/estado':
        PersonaController::cambiarEstado();
        break;

    case 'personas/eliminar':
        PersonaController::eliminar();
        break;

    /*
    USUARIOS
    */
    case 'usuarios':
    UsuarioController::index();
    break;

    case 'usuarios/guardar':
        UsuarioController::guardar();
        break;

    case 'usuarios/estado':
        UsuarioController::cambiarEstado();
        break;

    case 'usuarios/eliminar':
        UsuarioController::eliminar();
        break;

    /*
     * DEPARTAMENTOS
     */

    case 'departamentos':
        DepartamentoController::index();
        break;

    case 'departamentos/guardar':
        DepartamentoController::guardar();
        break;

    case 'departamentos/estado':
        DepartamentoController::cambiarEstado();
        break;


    /*
     * TIPOS DE SERVICIO
     */

    case 'tipos':
        TipoProblemaController::index();
        break;

    case 'tipos/guardar':
        TipoProblemaController::guardar();
        break;

    case 'tipos/estado':
        TipoProblemaController::cambiarEstado();
        break;

    case 'tipos/eliminar':
        TipoProblemaController::eliminar();
        break;

    /*
     * REGISTROS / IMPORTACIÓN Y EXPORTACIÓN
     */

    case 'registros':
        RegistrosController::index();
        break;

    case 'registros/personas/importar':
        RegistrosController::importarPersonas();
        break;

    case 'registros/personas/exportar':
        RegistrosController::exportarPersonas();
        break;

    case 'registros/atenciones/importar':
        RegistrosController::importarAtenciones();
        break;

    case 'registros/atenciones/exportar':
        RegistrosController::exportarAtenciones();
        break;

    case 'registros/reportes/importar':
        RegistrosController::importarReportes();
        break;

    case 'registros/reportes/exportar':
        RegistrosController::exportarReportes();
        break;

    case 'registros/trimestral':
        RegistrosController::formTrimestral();
        break;

    case 'registros/trimestral/exportar':
        RegistrosController::exportarTrimestral();
        break;

    case 'registros/anual':
        RegistrosController::formAnual();
        break;

    case 'registros/anual/exportar':
        RegistrosController::exportarAnual();
        break;


    /*
     * RUTA NO ENCONTRADA
     */

    default:

        http_response_code(404);

        echo 'Ruta no encontrada.';

        break;
}