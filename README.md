# Sistema_reportes
El siguiente repositorio contiene una aplicación web desarrollada en php y contenida en docker. No es tan robusta, ideal para usos prácticos.  
## Sistema de registro reportes TI
Esta aplicación funciona como un registro para poder llevar un control de reportes de atención a los usuarios. Puede ser modificada para atender algunos otros registros.

### Herramientas
- PHP
- HTML
- CSS
- SQL
## Estructura
La aplicación cuenta con inicio de sesión y solo dos roles, uno de administrador con control total y otro de técnico (asignado por defecto para futuras altas) que tiene limitación en cuestión de editar campos.
Tiene 6 secciones partiendo del dashboard general.
Toda la aplicación se encuentra desglosada en un árbol de carpetas.

### Funcionalidades
- Permite una vista general de los reportes registrados y su estado
- El formulario de registro solicita campos como el departamento, el usuario que solicitó la atención y una descripción
- Base de datos para los registros, usuario y departamentos
- Permite guardar archivos como evidencia o memorandums (todo en pdf)
- Tiene la función de importar y exportar información por medio de un xsl
- Genera concentrados anuales y trimestrales
- Gestión de usuarios
