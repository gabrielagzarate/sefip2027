<?php

$credencialesFile = __DIR__ . '/constantesCredenciales.php';
if (!is_readable($credencialesFile)) {
    trigger_error(
        'Falta lib/constantesCredenciales.php. Copie lib/constantesCredenciales.php.example y configure los valores del entorno.',
        E_USER_ERROR
    );
}
require_once $credencialesFile;

define("_S", "prod27");

define("MUNICIPIO", 2);
define("DEUDA", 4);
define("JURIDICO", 5);

// Para las tareas que puede hacer un usuario
define("TAREA_LECTURA", 1);
define("TAREA_ESCRITURA", 2);

// Ejercicio actual y anterior
define('APP_EJERCICIO', 2027);
define('APP_EJERCICIO_ANTERIOR', intval(APP_EJERCICIO) - 1);

// Bases de datos
define('BD_CTRL', 'ctrl2027');
define('BD_BITACORA', 'ctrl2027Bitacora');
define('BD_TEMPORAL', 'ctrl2027Temporal');
define('BD_SINPRES', 'sinpres');
define('BD_TESORERIA', 'tesoreria');
define('BD_TESORERIABIT', 'tesoreriaBitacora');
define('BD_TESORERITEM', 'tesoreriaTemporal');
define('BD_USUARIO', 'ctrlUsuario');
define('BD_USUARIOBIT', 'ctrlUsuarioBitacora');
define('BD_CTRLANT', 'ctrlAnterior');
define('BD_CTRL_2015', 'ctrl2015');
define('BD_CTRL_2016', 'ctrl2016');
define('BD_CTRL_2017', 'ctrl2017');
define('BD_CTRL_2018', 'ctrl2018');
define('BD_CTRL_2019', 'ctrl2019');
define('BD_CTRL_2020', 'ctrl2020');
define('BD_CTRL_2021', 'ctrl2021');
define('BD_CTRL_2022', 'ctrl2022');
define('BD_CTRL_2023', 'ctrl2023');
define('BD_CTRL_2024', 'ctrl2024');
define('BD_CTRL_2025', 'ctrl2025');
define('BD_CTRL_2026', 'ctrl2026');

// Imagenes
define('LOGO_GRIS', 'logoOaxaca-color-fblanco.png');
define('LOGO_COLOR', 'logoOaxaca-color-fblanco_v2.png');
define('LOGO_ESCUDO', 'escudo2.jpg');
define('LOGO_INSTITUCIONAL', 'Logo_Institucional.jpg');
define('LOGO_SOLICITUD_ACCESO', 'solacceso.png');
define('LOGO_MIR_CONGRESO', 'Legislativo_PNG.png');
define('LOGO_MIR_LEGISLATIVO', 'LXVI Legislatura.jpg');
define('LOGO_MIR_PERIODICO', 'Legislativo_PNG3.png');
define('LOGO_SOLICITUD_MEMBRETE', 'agencia_membretada.png'); 

// Contenedores
define('CONTENEDOR_CATALOGO_PROVEEDORES', 'CTP_2025_ID');

// Constantes para contraseñas
define('PASSWORD_HASH', 'sha256');
define('DIAS_VALIDOS_PASSWORD', 30);
define('MINUTOS_BLOQUEO', 10);
define('INTENTOS_RECUPERACION', 6);
define('SEGUNDOS_VALIDEZ_CODIGO', 60);

// IP del servidor
$serverAddr = $_SERVER['SERVER_ADDR'] ?? ($_SERVER['LOCAL_ADDR'] ?? '127.0.0.1');
define('IP_SERVER', $serverAddr);
define('SERVER_PRODUCCION', (IP_SERVER ?? '') === '172.17.90.88');

// Autenticación de dos factores (2FA): activa en producción; en QAS/UAT solo si HABILITAR_2FA_PRUEBAS = true.
// Para deshabilitar en producción momentáneamente, reemplazar la línea de DOBLE_FACTOR_DISPONIBLE por: define('DOBLE_FACTOR_DISPONIBLE', false);
// Para reactivarlo, restaurar: define('DOBLE_FACTOR_DISPONIBLE', SERVER_PRODUCCION || HABILITAR_2FA_PRUEBAS);
define('HABILITAR_2FA_PRUEBAS', false);
define('DOBLE_FACTOR_DISPONIBLE', SERVER_PRODUCCION || HABILITAR_2FA_PRUEBAS);

define('HABILITAR_CAMBIO_PASSWORD_PRUEBAS', false);
define('CAMBIO_PASSWORD_OBLIGATORIO', SERVER_PRODUCCION || HABILITAR_CAMBIO_PASSWORD_PRUEBAS);

// Aviso en la pantalla de login para el usuario
define('AVISO_LOGIN', false);
define('IP_PRODUCCION', '172.17.90.88');
define('IP_UAT', '172.17.90.87');

// Configuración de errores de PHP (omitida en respuestas binarias como el captcha)
require_once __DIR__ . '/helpers.php';
if (!defined('SEFIP_CAPTCHA_IMAGE')) {
    configurarErroresPhp();
}