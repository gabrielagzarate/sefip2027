<?php

if (!function_exists('nombreLogDiario')) {
    /**
     * Convierte "errorGeneral" / "errorGeneral.log" en "errorGeneral-2026-08-13.log".
     */
    function nombreLogDiario(string $base): string
    {
        $base = preg_replace('/\.log$/i', '', $base) ?? $base;

        return $base . '-' . date('Y-m-d') . '.log';
    }
}

if (!function_exists('rutaLogProyecto')) {
    /**
     * Ruta absoluta a un archivo dentro de /log del proyecto.
     * Si $porDia es true, agrega la fecha al nombre (errorGeneral-Y-m-d.log).
     */
    function rutaLogProyecto(string $nombreArchivo, bool $porDia = false): string
    {
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'log';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if ($porDia) {
            $nombreArchivo = nombreLogDiario($nombreArchivo);
        }

        return $dir . DIRECTORY_SEPARATOR . $nombreArchivo;
    }
}

if (!function_exists('valorLogProyecto')) {
    /** Deja un valor de log en una sola línea, sin romper el formato ||. */
    function valorLogProyecto(mixed $valor): string
    {
        $texto = str_replace(["\r", "\n", '||'], [' ', ' ', '|'], (string) $valor);

        return trim($texto);
    }
}

if (!function_exists('campoLogProyecto')) {
    /** Campo de log en formato NOMBRE[valor]. */
    function campoLogProyecto(string $nombre, mixed $valor): string
    {
        return $nombre . '[' . valorLogProyecto($valor) . ']';
    }
}

if (!function_exists('claseIgnoradaEnContextoLog')) {
    function claseIgnoradaEnContextoLog(string $cls): bool
    {
        return $cls === '' || $cls === 'AccesoDatos' || str_starts_with($cls, 'PHPMailer\\');
    }
}

if (!function_exists('esControladorEnContextoLog')) {
    function esControladorEnContextoLog(string $cls): bool
    {
        return str_contains($cls, 'Controlador')
            || (class_exists('WebResponse', false) && is_subclass_of($cls, 'WebResponse'));
    }
}

if (!function_exists('contextoLogDesdeObjeto')) {
    /** @return array{0: string, 1: string} */
    function contextoLogDesdeObjeto(?object $desde): array
    {
        if ($desde === null) {
            return ['', ''];
        }

        $controlador = get_class($desde);
        if ($controlador === 'AccesoDatos') {
            $controlador = '';
        }

        $metodo = '';
        if (isset($desde->metodo) && is_string($desde->metodo) && $desde->metodo !== '') {
            $metodo = $desde->metodo;
        }

        return [$controlador, $metodo];
    }
}

if (!function_exists('contextoLogDesdeBacktrace')) {
    /** @return array{0: string, 1: string, 2: string} */
    function contextoLogDesdeBacktrace(string $controlador, string $metodo): array
    {
        $claseFrame = '';
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30) as $frame) {
            $cls = $frame['class'] ?? '';
            $fn = $frame['function'] ?? '';
            if (claseIgnoradaEnContextoLog($cls)) {
                continue;
            }
            if ($claseFrame === '') {
                $claseFrame = $cls;
            }
            if ($controlador === '' && esControladorEnContextoLog($cls)) {
                $controlador = $cls;
                if ($metodo === '' && $fn !== '' && $fn !== '__construct') {
                    $metodo = $fn;
                }
                break;
            }
        }

        return [$controlador, $metodo, $claseFrame];
    }
}

if (!function_exists('contextoLogError')) {
    /**
     * Controlador, método y script de la petición para ubicar el error en errorGeneral.
     */
    function contextoLogError(?object $desde = null): string
    {
        [$controlador, $metodo] = contextoLogDesdeObjeto($desde);
        [$controlador, $metodo, $claseFrame] = contextoLogDesdeBacktrace($controlador, $metodo);

        if ($controlador === '') {
            $controlador = $claseFrame;
        }
        if ($metodo === '') {
            $metodo = $_POST['metodo'] ?? $_GET['metodo'] ?? '';
        }

        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''));
        if ($controlador === '') {
            $controlador = $script !== '' ? basename($script, '.php') : 'desconocido';
        }

        $usr = (defined('_S') && isset($_SESSION[_S]['usr_id'])) ? (string) $_SESSION[_S]['usr_id'] : '';
        $partes = [
            campoLogProyecto('CONTROLADOR', $controlador),
            campoLogProyecto('METODO', $metodo !== '' ? $metodo : 'n/d'),
            campoLogProyecto('SCRIPT', $script),
            campoLogProyecto('URI', $_SERVER['REQUEST_URI'] ?? ''),
        ];
        if ($usr !== '') {
            $partes[] = campoLogProyecto('USRSES', $usr);
        }

        return implode('||', $partes);
    }
}

if (!function_exists('nombreNivelPhp')) {
    function nombreNivelPhp(int $tipo): string
    {
        return match ($tipo) {
            E_ERROR, E_USER_ERROR, E_CORE_ERROR, E_COMPILE_ERROR => 'FATAL',
            E_PARSE => 'PARSE',
            E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING => 'WARNING',
            E_NOTICE, E_USER_NOTICE => 'NOTICE',
            E_DEPRECATED, E_USER_DEPRECATED => 'DEPRECATED',
            E_STRICT => 'STRICT',
            default => 'PHP_' . $tipo,
        };
    }
}

if (!function_exists('escribirLogProyecto')) {
    /**
     * Append a una línea en log/errorGeneral-Y-m-d.log o log/errorEmail-Y-m-d.log.
     */
    function escribirLogProyecto(string $archivo, string $texto): void
    {
        $ruta = rutaLogProyecto($archivo, true);
        $linea = date('d-m-Y H:i:s') . ' ' . $texto . PHP_EOL;
        @file_put_contents($ruta, $linea, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('configurarErroresPhp')) {
    /**
     * Oculta errores en producción y los registra en log/errorGeneral-Y-m-d.log
     * con ORIGEN[BASE|EXCEPCION|PHP], controlador y método.
     */
    function configurarErroresPhp(): void
    {
        ini_set('log_errors', '1');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            escribirLogProyecto(
                'errorGeneral',
                campoLogProyecto('ORIGEN', 'PHP') . '||'
                . campoLogProyecto('NIVEL', nombreNivelPhp($severity)) . '||'
                . contextoLogError() . '||'
                . campoLogProyecto('MENSAJE', $message) . '||'
                . campoLogProyecto('ARCHIVO', $file . ':' . $line)
            );

            return false;
        });

        set_exception_handler(static function (Throwable $e): void {
            $origen = $e instanceof Error ? 'ERROR' : 'EXCEPCION';
            escribirLogProyecto(
                'errorGeneral',
                campoLogProyecto('ORIGEN', $origen) . '||'
                . campoLogProyecto('CLASE', get_class($e)) . '||'
                . contextoLogError() . '||'
                . campoLogProyecto('MENSAJE', $e->getMessage()) . '||'
                . campoLogProyecto('ARCHIVO', $e->getFile() . ':' . $e->getLine()) . '||'
                . campoLogProyecto('TRACE', $e->getTraceAsString())
            );
            http_response_code(500);
            if (!(defined('SERVER_PRODUCCION') && SERVER_PRODUCCION)) {
                echo 'Uncaught ' . get_class($e) . ': ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
            exit(1);
        });

        register_shutdown_function(static function (): void {
            $err = error_get_last();
            if ($err === null) {
                return;
            }
            $fatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
            if (!in_array($err['type'], $fatales, true)) {
                return;
            }
            escribirLogProyecto(
                'errorGeneral',
                campoLogProyecto('ORIGEN', 'PHP') . '||'
                . campoLogProyecto('NIVEL', 'FATAL') . '||'
                . contextoLogError() . '||'
                . campoLogProyecto('MENSAJE', $err['message']) . '||'
                . campoLogProyecto('ARCHIVO', $err['file'] . ':' . $err['line'])
            );
        });

        if (defined('SERVER_PRODUCCION') && SERVER_PRODUCCION) {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            return;
        }

        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }
}

if (!function_exists('getSession')) {
    /**
     * la funcion retorna el valor de una variable de sesión
     * @param string $key la llave o nombre de la variable de sesión
     * @return mixed
     */
    function getSession($key, $validateEmpty = false)
    {
        $val = $_SESSION[_S][$key] ?? null;
        return ($validateEmpty && empty($val)) ? null : $val;
    }
}

if (!function_exists('sesionActiva')) {
    /**
     * Indica si hay un usuario autenticado en la sesión actual.
     */
    function sesionActiva(): bool
    {
        return !empty($_SESSION[_S]['usr_id']);
    }
}

if (!function_exists('tienePermiso')) {
    /**
     * busca en la sesión el permiso
     * @param string $s_m_o_t sistema enu opcion tarea 
     * @return boolean
     */
    function tienePermiso(string $s_m_o_t): bool
    {
        if (strpos($s_m_o_t, '.') === false) {
            return false;
        }

        $split = explode('.', $s_m_o_t);
        $s = strtoupper($split[0]);
        $prm = getSession('prm');

        if (count($split) === 3) {
            return isset($prm[$s][$split[1]][$split[2]]);
        }

        if (count($split) === 4) {
            return isset($prm[$s][$split[1]][$split[2]][$split[3]]);
        }

        return false;
    }
}

if (!function_exists('utf8Decode')) {
    /**
     * Reemplazar function nativa obsoleta utf8_decode 
     * @param string $cadena
     * @return string
     * */
    function utf8Decode(string|null $cadena): string
    {
        if (!$cadena) {
            return '';
        }
        return mb_convert_encoding($cadena, 'ISO-8859-1', 'UTF-8');
    }
}

if (! function_exists('utf8Encode')) {
    /**
     * Reemplazar function nativa obsoleta utf8_encode
     * @param string $cadena
     * @return string
     * */
    function utf8Encode(string | null $cadena): string | null
    {
        if (! $cadena) {
            return null;
        }

        $encoding = mb_detect_encoding($cadena, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            return mb_convert_encoding($cadena, 'UTF-8', $encoding);
        }

        return $cadena;
    }
}
if (!function_exists('claveClasAdm')) {
    /**
     * funcion que retorna la clave de la ur 000 
     * de la clasificación administrativa
     * @return string $clavedeUr
     */
    function claveClasAdm(): string
    {
        return $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'];
    }
}

if (!function_exists('claveUr')) {
    /**
     * funcion que retorna la clave de la ur 000 
     * del usuario de la sesión
     * @return string 114
     */
    function claveUr(): string
    {
        return $_SESSION[_S]['gpo_id'].$_SESSION[_S]['ur_id'];
    }
}

if (!function_exists('array_is_list')) {
    function array_is_list(array $arr): bool {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
