<?php
/**
 * Credenciales y valores sensibles por instalación (no versionados).
 *
 * Aquí se definen valores que cambian por entorno y no deben ir en el repositorio.
 * Actualmente incluye ruta de accesos XML, credenciales SMTP y contraseñas de PDF.
 *
 * INSTRUCCIONES:
 * 1. Copiar este archivo como lib/constantesCredenciales.php
 * 2. Completar los valores reales (ese archivo NO se sube al repositorio)
 * 3. Ver README.md → "Constantes credenciales (constantesCredenciales)"
 */

// --- Accesos a base de datos (XML) ---
// Ruta absoluta o relativa al archivo XML de conexiones. Distinta en desarrollo / QAS / UAT / producción.
// Ejemplo desarrollo: __DIR__ . '/conf/accesos.xml'
define('RUTA_ACCESOS_XML', __DIR__ . '/conf/accesos.xml');

// --- Correo (SMTP) ---

define('SMTP_HOST', 'correo.oaxaca.gob.mx');
define('SMTP_PORT', 587);

// Cuenta: sefip.notificaciones@finanzasoaxaca.gob.mx
define('SMTP_NOTIFICACIONES_USER', 'sefip.notificaciones@finanzasoaxaca.gob.mx');
define('SMTP_NOTIFICACIONES_PASS', 'CAMBIAR');

// Cuenta: sefip.sistema@finanzasoaxaca.gob.mx (credenciales de usuario / tickets)
define('SMTP_SISTEMA_USER', 'sefip.sistema@finanzasoaxaca.gob.mx');
define('SMTP_SISTEMA_PASS', 'CAMBIAR');

// --- Protección de PDF (SetProtection, tercer parámetro) — una constante por módulo ---

define('PDF_PASSWORD_OWNER_POA', 'CAMBIAR'); // POA
define('PDF_PASSWORD_OWNER_SCP', 'CAMBIAR'); // SCP
define('PDF_PASSWORD_OWNER_MGA', 'CAMBIAR'); // MGA
define('PDF_PASSWORD_OWNER_TES', 'CAMBIAR'); // Tesorería
define('PDF_PASSWORD_OWNER_SCG', 'CAMBIAR'); // SCG
define('PDF_PASSWORD_OWNER_MYSQL_REPORT', 'CAMBIAR'); // Librería compartida rep/lib/mysql_report.php (reporteador genérico SQL)
