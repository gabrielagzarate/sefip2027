<?php
// Configuración de seguridad
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ERROR);

// Validar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}
// Inicializar sesión de forma segura
session_start();

// Cargar constantes
require_once dirname(__FILE__) . '/../../lib/constantes.php';

/**
 * Parsea headers HTTP de respuesta
 * @param array $headers
 * @return array
 */
function parseHeader($headers)
{
    $head = [];
    $allowed_headers = ['content-type', 'content-length', 'content-disposition', 'response_code'];

    foreach ($headers as $k => $v) {
        $t = explode(':', $v, 2);
        if (isset($t[1])) {
            $header_name = strtolower(trim($t[0]));
            // Solo permitir headers seguros
            if (in_array($header_name, $allowed_headers)) {
                $head[$header_name] = trim($t[1]);
            }
        } else {
            if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                $head['response_code'] = intval($out[1]);
            }
        }
    }
    return $head;
}

/**
 * Valida y sanitiza datos POST
 * @param array $post
 * @return array
 */
function validatePostData($post)
{
    $sanitized = [];

    foreach ($post as $name => $value) {
        // Validar nombre de parámetro
        if (!preg_match('/^[a-zA-Z0-9_\-]{1,50}$/', $name)) {
            throw new InvalidArgumentException('Parámetro inválido: ' . $name);
        }

        // Validar longitud del valor (máximo 10KB por campo)
        if (is_string($value) && strlen($value) > 10240) {
            throw new LengthException('Valor de parámetro demasiado largo');
        }

        $sanitized[$name] = $value;
    }

    return $sanitized;
}

/**
 * Valida permisos del usuario
 * @return bool
 */
function validateUserPermissions()
{
    if (!isset($_SESSION[_S]['usr_id'])) {
        return false;
    }

    // Validar que el usuario_id sea válido (numérico)
    if (!is_numeric($_SESSION[_S]['usr_id'])) {
        return false;
    }


    return true;
}

// Validar permisos del usuario
if (!validateUserPermissions()) {
    http_response_code(401);
    error_log('Unauthorized access attempt from ' . $_SERVER['REMOTE_ADDR']);
    die('No autorizado');
}

// Validar y sanitizar datos POST
try {
    $post = validatePostData($_POST);
} catch (Exception $e) {
    http_response_code(400);
    error_log('Invalid POST data: ' . $e->getMessage());
    die('Datos de solicitud inválidos');
}

$postdata = http_build_query($post);
// Validar IP del servidor y definir host
$host = '';
$allowed_ips = [IP_PRODUCCION, IP_UAT, '127.0.0.1', 'localhost'];
$ip_server = IP_SERVER;
if (!in_array($ip_server, $allowed_ips)) {
    error_log('Invalid server IP: ' . $ip_server);
}
$ssl = false;
switch ($ip_server) {
    case IP_PRODUCCION: // PRODUC
        $host = 'https://reportessefip.finanzasoaxaca.gob.mx/Reportes' . APP_EJERCICIO . '/';
        $ssl = true;
        break;
    case IP_UAT: // UAT
        $host = 'https://uat-reportessefip.finanzasoaxaca.gob.mx/Reportes' . APP_EJERCICIO . '/';
        $ssl = true;
        break;
    default:
        $host = 'https://uat-reportessefip.finanzasoaxaca.gob.mx/Reportes' . APP_EJERCICIO . '/';
        $ssl = false;
}

// Validar URL
$url_parts = parse_url($host);
if (!isset($url_parts['scheme']) || !in_array($url_parts['scheme'], ['http', 'https'])) {
    http_response_code(500);
    error_log('Invalid host URL configuration');
    die('Error de configuración del servidor');
}

// Configurar opciones de stream con SSL VERIFICADO
$opts = [
    'http' => [
        'method'        => 'POST',
        'header'        => 'Content-type: application/x-www-form-urlencoded',
        'content'       => $postdata,
        'timeout'       => 60, // Reducir timeout a 60 segundos máximo
        'max_redirects' => 3, // Limitar redirecciones
    ],
    'ssl' => [
        // SEGURIDAD: Verificar certificados SSL
        'verify_peer'       => $ssl,
        'verify_peer_name'  => $ssl,
        'allow_self_signed' => false,
        'ciphers'           => 'HIGH:!aNULL:!MD5',
    ],
];
$context = stream_context_create($opts);

// Limitar tamaño máximo de descarga (50MB)
$max_size = 52428800; // 50MB en bytes
$chunk_size = 8192; // 8KB chunks

try {
    // Obtener PDF con límite de tamaño
    $pdf = @file_get_contents($host, false, $context, 0, $max_size);

    if ($pdf === false) {
        throw new InvalidArgumentException('No se pudo obtener el archivo remoto');
    }

    if (strlen($pdf) >= $max_size) {
        throw new LengthException('Archivo excede tamaño máximo permitido');
    }

    $res = parseHeader($http_response_header);
    $response = (int) $res['response_code'];

    switch ($response) {
        case 200:
            // Solo enviar headers seguros

            // Enviar solo headers seguros
            foreach ($http_response_header as $value) {
                header($value);
            }

            // Enviar contenido
            echo $pdf;
            exit();
            break;

        case 400:
            http_response_code(400);
            error_log('Bad Request [400]');
            die('Solicitud inválida');
            break;

        case 401:
        case 403:
            http_response_code(403);
            error_log('Unauthorized access [' . $response . ']');
            die('No autorizado para generar este reporte');
            break;

        case 404:
            http_response_code(404);
            error_log('Reporte no encontrado [404]');
            die('Reporte no encontrado');
            break;

        case 408:
        case 504:
            http_response_code(504);
            error_log('Server timeout [' . $response . '] - Report too large or slow');
            die('Se superó el tiempo de espera');
            break;

        case 413:
            http_response_code(413);
            error_log('Payload too large [413]');
            die('El reporte es demasiado grande');
            break;

        case 429:
            http_response_code(429);
            error_log('Too many requests [429]');
            die('Demasiadas solicitudes. Intente más tarde');
            break;

        case 500:
        case 502:
        case 503:
            http_response_code(503);
            error_log('Server error [' . $response . ']');
            die('Error en el servidor de reportes');
            break;

        default:
            http_response_code(502);
            error_log('Unexpected response [' . $response . ']');
            die('Error al recuperar el reporte');
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in get_pdf.php: ' . $e->getMessage());
    die('Error procesando la solicitud');
}
