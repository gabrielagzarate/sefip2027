<?php

require_once dirname(__FILE__) . '/../lib/sessionSafeStart.php';
sessionSafeStart();

require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . "/../lib/constantes.php";
require_once dirname(__FILE__) . "/../lib/constantesTelegram.php";

define('TEMPLATE_ID', getenv('TXT_TEMPLATE_ID') ?: TEMPLATE);
define('REFRESH_MARGIN_SECONDS', 5 * 60);
define('TELEGRAM_JWT_SETTING', 'admin_jwt_token');
define('TELEGRAM_JWT_DURATION_SECONDS', 3600);
define('TELEGRAM_LOG_DIR', dirname(__FILE__) . '/log/telegram');

class Telegram extends WebResponse
{

    public function __construct()
    {
        parent::__construct();
    }

    public function logTelegram(string $nivel, string $mensaje, array $contexto = []): void
    {
        if (!is_dir(TELEGRAM_LOG_DIR)) {
            mkdir(TELEGRAM_LOG_DIR, 0777, true);
        }

        $archivo = TELEGRAM_LOG_DIR . '/telegram_' . date('Y-m-d') . '.log';
        $linea = date('Y-m-d H:i:s') . ' | ' . strtoupper($nivel) . ' | ' . $mensaje;

        if ($contexto !== []) {
            $linea .= ' | ' . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        file_put_contents($archivo, $linea . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function enmascararTelefono(?string $telefono): string
    {
        if (empty($telefono)) {
            return '(vacío)';
        }

        return '***' . substr($telefono, -3);
    }

    private function enmascararCorreo(?string $correo): string
    {
        if (empty($correo)) {
            return '(vacío)';
        }

        $correo = strtolower($correo);
        $partes = explode('@', $correo, 2);
        if (count($partes) !== 2) {
            return substr($correo, 0, 3) . '***';
        }

        return substr($partes[0], 0, 3) . '***@' . $partes[1];
    }

    public function logCorreoRespaldo2FA(bool $enviado, string $correo): void
    {
        $this->logTelegram(
            $enviado ? 'info' : 'error',
            $enviado
                ? 'Código 2FA enviado por correo (respaldo SMS)'
                : 'Fallo al enviar código 2FA por correo (respaldo SMS)',
            [
                'usr_id' => $_SESSION[_S]['usr_id'] ?? null,
                'correo' => $this->enmascararCorreo($correo),
            ]
        );
    }

    private function extraerCodigoErrorApi(array $response): ?int
    {
        $httpCode = $response['http_code'] ?? null;
        if ($httpCode !== null && (int) $httpCode > 0) {
            return (int) $httpCode;
        }

        return null;
    }

    private function existeJwtEnTabla(AccesoDatos $sql): bool
    {
        $sql->setSql(
            'SELECT 1 AS existe
            FROM ' . BD_SINPRES . '.cat_Telegram
            WHERE setting_name = :setting_name
            LIMIT 1'
        );
        $sql->consultar([':setting_name' => TELEGRAM_JWT_SETTING], false);
        if ($sql->getErrno() > 0) {
            $this->logTelegram('error', 'No se pudo verificar JWT en cat_Telegram', [
                'errno' => $sql->getErrno(),
                'error' => $sql->getError(),
            ]);
            return false;
        }

        return (bool) $sql->extraer();
    }


        function make_curl_request($url, $method = 'GET', $headers = [], $data = null)
        {
            $r = ['success' => false, 'message' => '', 'http_code' => null, 'debug_info' => '', 'raw_response' => '', 'data' => null, 'code' => null];
            $curlHeaders = [];
            $rawResponse = null;
            $responseData = null;
            $ch = curl_init($url);
            if ($ch === false) {
                $r['message'] = 'No se pudo inicializar cURL, puede ser BASE_URL o la conectividad.';
                return $r;
            }
            foreach ($headers as $key => $value) {
                $curlHeaders[] = "$key: $value";
            }
            curl_setopt_array(
                $ch,
                [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CUSTOMREQUEST  => strtoupper($method),
                    CURLOPT_HTTPHEADER     => $curlHeaders,
                    CURLOPT_CONNECTTIMEOUT => 20,
                    CURLOPT_TIMEOUT        => 120,
                ]
            );
            if ($data !== null) {
                try {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : json_encode($data, JSON_THROW_ON_ERROR));
                } catch (\JsonException $e) {
                    $r['message'] = 'ERROR DE FORMATO: No se pudo codificar los datos en JSON.';
                    $r['debug_info'] = 'Error: ' . $e->getMessage();
                    return $r;
                }
            }
            try {
                $rawResponse = curl_exec($ch);
            } catch (\Exception $e) {
                curl_close($ch);
                $r['message'] = 'Excepción durante la ejecución cURL';
                $r['debug_info'] = $e->getMessage();
                return $r;
            }
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $r['http_code'] = $httpCode;

            if ($rawResponse === false) {
                $r['message'] = 'Error cURL al consumir la API';
                $r['debug_info'] = $curlError ?: 'curl_exec devolvió false';
                return $r;
            }
            if ($rawResponse === '') {
                $r['message'] = "Respuesta vacía de la API";
                $r['debug_info'] = "URL:  $url";
                return $r;
            }
            try {
                $responseData = json_decode($rawResponse, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $r['message'] = "Respuesta no es JSON válido";
                $r['debug_info'] = substr($rawResponse, 0, 300);
                return $r;
            }
            if ($httpCode >= 200 && $httpCode < 300) {
                $r['success'] = true;
                $r['message'] = "Respuesta exitosa";
                $r['data'] = $responseData;
            } else {
                $r['message'] = ($responseData['message'] ? $responseData['message'] : 'Error de API');
                $r['debug_info'] = ($responseData['message'] ? $responseData['message'] : 'Error de API');
            }
            $r['raw_response'] = $rawResponse;

            return $r;
        }
        
        function store_new_jwt_token($token, $durationSeconds) {
            $sql = new AccesoDatos();
            $sql->conectar();

            $expiry_time = date('Y-m-d H:i:s', time() + $durationSeconds - REFRESH_MARGIN_SECONDS);
            $params = [
                ':setting_name' => TELEGRAM_JWT_SETTING,
                ':token' => $token,
                ':expiry_time' => $expiry_time,
            ];

            // Actualiza solo el registro más reciente; no modifica duplicados históricos ni otros settings.
            $sql->setSql('
                UPDATE ' . BD_SINPRES . '.cat_Telegram
                SET setting_value = :token, expiration_time = :expiry_time
                WHERE setting_name = :setting_name
                ORDER BY expiration_time DESC
                LIMIT 1
            ');
            $sql->consultar($params, false);
            if ($sql->getErrno() > 0) {
                $this->logTelegram('error', 'No se pudo actualizar JWT en cat_Telegram', [
                    'errno' => $sql->getErrno(),
                    'error' => $sql->getError(),
                ]);
                return false;
            }

            $operacion = 'actualizar';
            if ((int) $sql->getFilasAfectadas() === 0) {
                $sql->setSql('
                    INSERT INTO ' . BD_SINPRES . '.cat_Telegram (setting_name, setting_value, expiration_time)
                    VALUES (:setting_name, :token, :expiry_time)
                ');
                $sql->consultar($params, false);
                if ($sql->getErrno() > 0) {
                    $this->logTelegram('error', 'No se pudo insertar JWT en cat_Telegram', [
                        'errno' => $sql->getErrno(),
                        'error' => $sql->getError(),
                    ]);
                    return false;
                }
                $operacion = 'insertar';
            }

            $verificacion = new AccesoDatos();
            $verificacion->conectar();
            if (!$this->existeJwtEnTabla($verificacion)) {
                $this->logTelegram('error', 'JWT no persistido en cat_Telegram después del guardado', [
                    'operacion' => $operacion,
                    'filas_afectadas' => $sql->getFilasAfectadas(),
                ]);
                return false;
            }

            $this->logTelegram('info', 'JWT guardado en cat_Telegram', [
                'operacion' => $operacion,
                'expiration_time' => $expiry_time,
                'filas_afectadas' => $sql->getFilasAfectadas(),
            ]);

            return true;
        }
        
        function get_all_applications() {
            $auth_result = $this->renew_admin_jwt_token();
            if (!$auth_result['success']) return $auth_result;
        
            $get_all_url = BASE_URL . '/aplicacion/getAll';
            $headers = ['Authorization' => 'Bearer ' . $auth_result['token']];
            $response = $this->make_curl_request($get_all_url, 'GET', $headers);
        
            if ($response['success']) {
                
                $applications = $response['data']['data'] ?? $response['data'] ?? []; 
                $count = is_array($applications) ? count($applications) : 0;
                
                return [
                    'success' => true, 
                    'message' => "Lista de apps obtenida al millon. Total: $count.", 
                    'data' => $response['data']
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'Error al obtener la lista de apps: ' . htmlspecialchars($response['message']),
                    'debug_info' => $response['debug_info'] ?? 'Sin información del debugger.'
                ];
            }
        }
        
        function get_all_messages() {
            $auth_result = $this->renew_admin_jwt_token();
            if (!$auth_result['success']) return $auth_result;
        
            $get_all_url = BASE_URL . '/mensajes/getAll';
            $headers = ['Authorization' => 'Bearer ' . $auth_result['token']];
            $response = $this->make_curl_request($get_all_url, 'GET', $headers);
        
            if ($response['success']) {
                $messages = $response['data']['data'] ?? $response['data'] ?? []; 
                $count = is_array($messages) ? count($messages) : 0;
        
                return [
                    'success' => true, 
                    'message' => "Mensajes obtenidos al millon. Total: $count.", 
                    'data' => $response['data']
                ];
            } else {
                return ['success' => false, 'message' => 'Error al obtener la lista de mensajes: ' . htmlspecialchars($response['message'])];
            }
        }
        
        function renew_admin_jwt_token() {
            $rJwt = ['success' => false, 'message' => 'Error de conexión.', 'token' => null];
            $login_url = BASE_URL . '/auth/login';
            $login_data = [
                'usuario' => USERNAME,
                'password' => PASSWORD
            ];

            $headers = ['Content-Type' => 'application/json'];
            $response = $this->make_curl_request($login_url, 'POST', $headers, $login_data);
            if ($response['success']) {
                $data = $response['data'];
                if (isset($data['data']['token'])) {
                    $token = $data['data']['token'];
                    if (!$this->store_new_jwt_token($token, TELEGRAM_JWT_DURATION_SECONDS)) {
                        $this->logTelegram('warning', 'JWT obtenido pero no persistido en cat_Telegram');
                    }
                    $rJwt['success'] = true;
                    $rJwt['message'] = 'Nuevo Token JWT obtenido y guardado en la base.';
                    $rJwt['token'] = $token;
                } else {
                    $rJwt['message'] = 'Token vacío en la respuesta.';
                    $this->logTelegram('error', 'Login API sin token en respuesta', [
                        'url' => BASE_URL . '/auth/login',
                    ]);
                }
            } else {
                $rJwt['message'] = 'No se pudo renovar el JWT Token: ' . ($response['message'] ?? 'Error de conexión.');
                $rJwt['http_code'] = $response['http_code'] ?? 0;
                $this->logTelegram('error', 'Fallo al renovar JWT', [
                    'url' => BASE_URL . '/auth/login',
                    'codigo_error' => $this->extraerCodigoErrorApi($response) ?? 0,
                    'http_code' => $response['http_code'] ?? null,
                    'message' => $response['message'] ?? '',
                    'debug_info' => $response['debug_info'] ?? '',
                ]);
            }

            return $rJwt;
        }

        function get_admin_jwt_token_on_demand() {
            $pdo = '';
            $pdo = new AccesoDatos();
            $pdo->conectar();

            if (!$pdo) {
                return $this->renew_admin_jwt_token();
            }

            $sql = 'SELECT setting_value AS token, expiration_time
                    FROM ' . BD_SINPRES . '.cat_Telegram
                    WHERE setting_name = :setting_name
                    ORDER BY expiration_time DESC
                    LIMIT 1';
            $pdo->setSql($sql);
            $pdo->consultar([':setting_name' => TELEGRAM_JWT_SETTING], false);
            if ($pdo->getErrno() > 0) {
                return $this->renew_admin_jwt_token();
            }
            $config = $pdo->extraer();
        
            if ($config) {
                $expiration_timestamp = strtotime($config['expiration_time']);
                $time_left = $expiration_timestamp - time();
                if ($time_left > 0) {
                    return [
                        'success' => true,
                        'token' => $config['token'],
                        'message' => 'Token JWT recuperado de la DB (válido por ' . round($time_left / 60) . ' minutos más).'
                    ];
                } else {
                    $this->logTelegram('info', 'JWT expirado en BD, se renovará');
                }
            }
            return $this->renew_admin_jwt_token();
        }

        function get_or_renew_jwt_token() {
            return $this->get_admin_jwt_token_on_demand();
        }
        
        function register_app() {
            $auth_result = $this->get_admin_jwt_token_on_demand();
            if (!$auth_result['success']) return $auth_result;
            $jwtToken = $auth_result['token'];
        
            $url = BASE_URL . '/aplicacion/save';
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer ". $jwtToken
            ];
            $data = [
                'nombre' => 'SEFIP',
                'descripcion' => 'Aplicacion para autenticacion con Telegram'
            ];
            
            $response = $this->make_curl_request($url, 'POST', $headers, $data);
            
            if ($response['success'] && isset($response['data']['data']['key_api'])) {
                $response['data']['data']['key_api'] = $response['data']['data']['key_api'];
            }
        
            return $response;
        }
        
        function get_api_key($app_key_id) {
            $auth_result = $this->get_admin_jwt_token_on_demand();
            if (!$auth_result['success']) return $auth_result;
            $jwtToken = $auth_result['token'];
        
            $url = BASE_URL . '/aplicacion/getApiKey/' . $app_key_id;
            $headers = [
                'Authorization' => "Bearer ". $jwtToken
            ];
            $response = $this->make_curl_request($url, 'GET', $headers);
            
            if ($response['success'] && isset($response['data']['data']['key_api'])) {
                $response['message'] = "API Key obtenida exitosamente";
            }
            return $response;
        }
        
        function register_template($app_key_id) {
            $auth_result = $this->get_admin_jwt_token_on_demand();
            if (!$auth_result['success']) return $auth_result;
            $jwtToken = $auth_result['token'];
        
            $url = BASE_URL . '/plantilla/save';
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer ". $jwtToken
            ];
            $data = [
                'aplicacion' => $app_key_id,
                'nombre' => '2FA-Code-Verification',
                'contenido' => 'Tu codigo de verificacion es: [[codigo_2fa]]',
                'variables_json' => ['codigo_2fa']
            ];
        
            $response = $this->make_curl_request($url, 'POST', $headers, $data);
            
            if ($response['success'] && isset($response['data']['data']['id'])) {
                $response['message'] = "Plantilla registrada";
            }
        
            return $response;
        }

        function send_2fa_code() {
            $data = $_SESSION[_S]['usr_id'];

            $code = strval(rand(100000, 999999));
            $_SESSION['expected_code'] = $code;
            $_SESSION['code_generation_time'] = time();
            $apiKey = API_KEY;
            $templateId = TEMPLATE_ID;

            $this->logTelegram('info', 'Inicio envío código 2FA', [
                'usr_id' => $data,
                'template_id' => $templateId,
                'base_url' => BASE_URL,
            ]);

            $usr = '';
            $usr = new AccesoDatos();
            $usr->conectar();
            $sql = "select usr_nombre,usr_telefono
                    from ctrlUsuario.cat_Usuario
                    where usr_id = :usr_id";
            $usr->setSql($sql);
            $params =[
                ':usr_id' =>$data
            ];
            $usr->consultar($params);
            $row = $usr->extraer();

            if (!$row) {
                $this->logTelegram('error', 'Usuario no encontrado al enviar 2FA', [
                    'usr_id' => $data,
                    'codigo_error' => 'USUARIO_NO_ENCONTRADO',
                ]);
                return [
                    'success' => false,
                    'message' => 'ERROR: No se encontró el usuario',
                    'code' => $code
                ];
            }

            $dat = $row['usr_nombre'];
            $phone = $row['usr_telefono'];

            if (empty($phone)) {
                $this->logTelegram('error', 'Usuario sin teléfono; se usará correo como respaldo', [
                    'usr_id' => $data,
                    'codigo_error' => 'SIN_TELEFONO',
                ]);
                return [
                    'success' => false,
                    'message' => 'ERROR: El usuario no tiene un número de teléfono asignado',
                    'code' => $code
                ];
            }

            if (!$apiKey || $apiKey === 'TU_API_KEY_PERMANENTE' || !$templateId || $templateId === 'TU_TEMPLATE_ID_PERMANENTE') {
                $this->logTelegram('error', 'API Key o Template ID no configurados', [
                    'usr_id' => $data,
                    'telefono' => $this->enmascararTelefono($phone),
                    'codigo_error' => 'CONFIG_API',
                ]);
                return [
                    'success' => false,
                    'message' => 'ERROR: API Key o Template ID no configurados',
                    'code' => $code
                ];
            }

            $auth_result = $this->get_admin_jwt_token_on_demand();
            if (!$auth_result['success']) {
                $this->logTelegram('error', 'Error al obtener JWT para envío 2FA', [
                    'usr_id' => $data,
                    'telefono' => $this->enmascararTelefono($phone),
                    'message' => $auth_result['message'] ?? '',
                    'codigo_error' => $auth_result['http_code'] ?? 'JWT_ERROR',
                ]);
                return [
                    'success' => false,
                    'message' => 'Error JWT Admin ): ' . $auth_result['message'],
                    'code' => $code
                ];
            }

            $jwtToken = $auth_result['token'];
            $url = BASE_URL . '/mensajes/send';
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer ".$jwtToken
            ];

            $data = [
                'aplicacion' => $apiKey,
                'plantilla' => ['id' => $templateId],
                'telefono' => $phone,
                'variables' => [
                    
                    ['variable' => 'nombre', 'valor' => $dat], 
                    ['variable' => 'codigo', 'valor' => $code]
                ],
        
                'apiKey' => $apiKey
            ];

            $response = $this->make_curl_request($url, 'POST', $headers, $data);
            if ($response['success']) {
                $response['message'] = "Se ha enviado un código a su teléfono con terminación *** " . substr($phone, -3);
                $this->logTelegram('info', 'Código 2FA enviado por SMS', [
                    'usr_id' => $_SESSION[_S]['usr_id'],
                    'telefono' => $this->enmascararTelefono($phone),
                    'http_code' => $response['http_code'] ?? null,
                ]);
            } else {
                $codigoError = $this->extraerCodigoErrorApi($response);
                $this->logTelegram('error', 'Fallo API mensajes/send; se usará correo como respaldo', [
                    'usr_id' => $_SESSION[_S]['usr_id'],
                    'telefono' => $this->enmascararTelefono($phone),
                    'url' => $url,
                    'codigo_error' => $codigoError ?? 0,
                    'http_code' => $response['http_code'] ?? null,
                    'message' => $response['message'] ?? '',
                    'debug_info' => $response['debug_info'] ?? '',
                    'raw_response' => isset($response['raw_response']) ? substr((string) $response['raw_response'], 0, 500) : '',
                ]);
            }
            $response['code'] = $code;
        
            return $response;
        }
        
        function verify_2fa_code($inputCode) {
            if (!isset($_SESSION['expected_code']) || !isset($_SESSION['code_generation_time'])) {
                return ['success' => false];
            }

            $validity_duration_seconds = 3 * 60;
            if (time() > ($_SESSION['code_generation_time'] + $validity_duration_seconds)) {
                unset($_SESSION['expected_code']);
                unset($_SESSION['code_generation_time']);
                return ['success' => false];
            }

            if ($inputCode === $_SESSION['expected_code']) {
                unset($_SESSION['user_logged_in']);
                unset($_SESSION['user_phone']);
                unset($_SESSION['expected_code']);
                unset($_SESSION['code_generation_time']);
                return ['success' => true];
            } else {
                return ['success' => false];
            }
        }

        function valid($requestData) {

            $inputCode = isset($requestData['input_code']) ? $requestData['input_code'] : '';
            $result = $this->verify_2fa_code($inputCode);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }
    }
?>
