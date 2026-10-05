<?php

/** ------------------------------------------------------------------------------------------------------------------
 * File name   : AccesoDatos.php
 * Version     : 2024
 * Begin       : --/--/--
 * Last Update : 03/05/2024

 * @autor ???
 * UPDATE @autor LI Japhet Mata Arellanes
 * UPDATE 2018-04-24  LI Japhet Mata Arellanes

/*  GARANTIA: SI LO ROMPES PUEDES CONSERVAR LOS PEDAZOS


NOTAS DE LA VERSION:
-SE VALIDAN LOS ERRORES DE CONEXION Y SE ENTREGAN AL USUARIO
-SE VALIDA EL ORIGEN DE LA PETICION PARA MOSTRAR DETALLES AL PROGRAMADOR O MENSAJE AL USUARIO COMUN
-SE GUARDA EN UNA CARPETA LOS ERRORES CON LA CLASE  QUE GENERA EL ERROR
esto si se una el mismo objeto $this->conectar $this->consultar
si se usa una nueva instancia $variable = new AccesoDatos no se puede identificar el controlador que lo usa
-SE GUARDA EN QUE FUNCION DE ESTA CLASE SE GENERA EL ERROR
-SE GUARDAN LOS ERRORES DE CONSULTA
-SE QUITA LA CLASE CONEXION DE ESTE SCRIPT
-SE AGREGA LA FUNCIONALIDAD QUE IMPIDE QUE EL SCRIPT MUERA Y EL ERROR SEA CONTROLADO POR EL PROGRAMADOR
-SE AGREGA LA CONEXION SQLSRV EN VES DE MSS_CONNECT
-SE AGREGA LA CONEXION Y CONSULTA A ORACLE
-SE AGREGA LA CONEXION Y CONSULTA A POSTGRESQL
2018-04-24
-SE QUITA DEL __CONSUTRUCT LA DECLARACIÓN DE CONEXIONES
-LAS CONEXIONES PASAN A UN XML LA ESTRUCTURA BÁSICA ES :
<?xml version="1.0" encoding="UTF-8"?>
<accesos>
<acceso name="sefip2019_local">
<servidor>172.17.90.24</servidor>
<puerto>5603</puerto>
<usuario>usuario</usuario>
<password>contraseña</password>
<basedatos>ctrl2019</basedatos>
<tipo>MySql</tipo>
</acceso>
</accesos>
-LA CLASE CONEXION ENCRIPTA DE MANERA BASICA LOS DATOS DE CADA SERVIDOR
UPDATE 2024-05-03  MC Japhet Mata Arellanes
Se implementa la clase nativa PDO para las conexxiones
Se extiende la funcionalidad de la clase Conexion para tener en si misma los recursos
referentes a su tipo de servidor mysql, sqlserver ...
ahora se requiere usar PDO para sanitizar los paramentros de entrada al SQL con prepareStatement.
 */

date_default_timezone_set("America/Mexico_City");
require_once dirname(__FILE__) . "/constantes.php";
require_once dirname(__FILE__) . "/Conexion.php";
require_once dirname(__FILE__) . "/controlErrorAccesoDatos.php";

class AccesoDatos
{
    private $acc_conexion_id = 0;
    private $acc_consulta_id = 0;
    private $acc_sql = '';
    private $acc_params = array();
    private $acc_errno = 0;
    private $acc_error = "";
    private $res = null;
    private $acc_sqlstate = '';
    private $serverDefault = 'sefip'. APP_EJERCICIO;
    private $acobservacion;
    public $serverProduccion;
    public $prm;
    public array $bindedParams = [];
    private $dataTypes = array(
        1 => "tinyint,boolean",
        2 => "smallint",
        3 => "int",
        4 => "float",
        5 => "double,real",
        7 => "timestamp",
        8 => "bigint,serial",
        9 => "mediumint",
        10 => "date",
        11 => "time",
        12 => "datetime",
        13 => "year",
        16 => "bit",
        246 => "decimal",
        252 => "text,tinytext,mediumtext,longtext,tinyblob,mediumblob,blob,longblob",
        253 => "varchar,varbinary",
        254 => "char,binary",
    );

    // *************** ************* *****************
    // OJO CON LA VARIABLE EJERCICIO EN ENT/USUARIO
    // *************** ************* *****************

    private $ipProgramadores = array(
        "172.17.94.96",
        "172.17.94.150",
        "127.0.0.1",
        "::1",
    );

    private $acc_server = array();
    private $ultimoId;

    public function __construct()
    {
        $this->serverProduccion = ($_SERVER['REMOTE_ADDR'] == '172.17.90.88') ? true : false;
        $prm = (func_num_args() > 0 ?
            (is_object(func_get_arg(0)) ?
                func_get_arg(0)
                : (is_array(func_get_arg(0)) ?
                    (object) func_get_arg(0)
                    :
                    null))
            : null);

        /* SE CONSTRUYEN LOS ACCESOS DESDE EL XML */
        $this->gtAccesos();

        if (isset($prm->server)) {
            $this->serverDefault = $prm->server;
        }
    }

    /**
     * prepara los parametros repetidos de una consulta para no tener que repetirlos en el arreglo
     * de consultar
     * @param string llave(nombre del parametro)
     * @param any valor del parametros
     *
     */
    public function bindParam($key, $value) : void
    {
        $this->bindedParams[$key] = $value;
    }

    /**
     * prepara los parametros repetidos de una consulta para no tener que repetirlos en el arreglo
     * de consultar
     * @param array arreglo associativo
     */
    public function bindParams(array $params): void
    {
        foreach ($params as $key => $value) {
            $this->bindedParams[$key] = $value;
        }
    }
    /*FUNCIONES SET*/
    public function setConexionId($val)
    {
        $this->setErrno(0);
        $this->setError("");
        $this->acc_conexion_id = $val;
    }
    public function setConsultaId($val)
    {
        $this->acc_consulta_id = $val;
    }
    public function setSql($val)
    {
        $this->acc_sql = $val;
    }
    public function setParams($val)
    {
        $this->acc_params = $val;
    }
    public function setErrno($val)
    {
        $this->acc_errno = $val;
    }
    public function setError($val)
    {
        $this->acc_error = $val;
    }
    public function setSqlstate($val)
    {
        $this->acc_sqlstate = $val;
    }
    public function setUltimoId($val)
    {
        $this->ultimoId = $val;
    }
    public function addServer($n, $c)
    {
        $this->acc_server[$n] = $c;
    }
    public function setServerDefault($val)
    {
        $this->serverDefault = $val;
    }

    /*FUNCIONES GET*/

    public function getDataTypes(): array
    {
        return $this->dataTypes;
    }

    public function getUltimoId()
    {
        return $this->ultimoId;
    }
    public function getServer($n): Conexion
    {
        return $this->acc_server[$n];
    }
    public function getConexionId()
    {
        return $this->acc_conexion_id;
    }
    public function getConsultaId()
    {
        return $this->acc_consulta_id;
    }
    public function getSql()
    {
        return $this->acc_sql;
    }
    public function getParams()
    {
        return $this->acc_params;
    }
    public function getErrno()
    {
        return $this->acc_errno;
    }
    public function getError()
    {
        return $this->acc_error;
    }
    public function getSqlstate()
    {
        return $this->acc_sqlstate;
    }
    public function getServerDefault()
    {
        return $this->serverDefault;
    }
    public function setObservacion($val)
    {
        $this->acobservacion = (string) $val;
    }

    public function getConexionActiva($n = '')
    {
        $this->serverDefault = ($n == "") ? $this->serverDefault : $n;

        if ($this->serverDefault == '') {
            $this->res->comunica->script = ' notify("El servidor se ha perdido ' . $n . '","error");';
            $texto = "FUNCTION[" . __FUNCTION__ . "]:TEXTO[El servidor se ha perdido " . $n . "]";
            $this->logErrores(array("tipo" => "C", "texto" => $texto));
            echo json_encode($this->res);
        } else {
            return $this->getServer($this->serverDefault);
        }
    }
    public function conectar(string $n = '', bool $show = true): void
    {
        //error_reporting( E_ALL );
        $c = $this->getConexionActiva($n);

        if (!($c instanceof Conexion)) {
            $this->res->comunica->script = ' notify("Error al instanciar una clase !!","error");';
            $texto = "FUNCTION[" . __FUNCTION__ . "]:TEXTO[Error al instanciar una clase]";
            $this->logErrores(array("tipo" => "C", "texto" => $texto));
            echo json_encode($this->res);
            die();
        }

        try {
            $c->setResource(new PDO($c->getPDOConnectionString(), $c->getUsuario(), $c->getPassword()));
            // $this->setConexionId($c->getResource()->id);
            if ($c->getType() == "MySql") {
                $this->set_char('utf8');
            }

            if ($n != '') {
                $this->serverDefault = $n;
            }

            // return $this->getConexionId();
        } catch (Exception $e) {
            $this->setErrno($e->getCode());
            $this->setError($e->getMessage());

            /*  GUARDAMOS EN REGISTRO DE ESTOS ERRORES EN UN LOG DE TEXTO */
            $this->logErrores(array("tipo" => "C", "texto" => "FUNCION[" . __FUNCTION__ . "]"));

            if ($show) {
                $res = $this->errorMessage(1);
                echo json_encode($res);
                die();
            }
        }
    }
    public function desconectar(): void
    {
        $c = $this->getConexionActiva($this->serverDefault);
        if ($c->getResource() instanceof PDO) {
            $c->setResource(null);
        }
    }
    public function set_char($char)
    {
        $c = $this->getConexionActiva($this->serverDefault);
        $c->getResource()->exec("SET NAMES '$char'");
    }
    public function consultar($params = [], $show = true, int $mode = PDO::CURSOR_FWDONLY)
    {
        $numargs = func_num_args();
        if ($numargs > 0) {
            if (is_string(func_get_arg(0))) {
                $this->setSql(func_get_arg(0));
            }
        }
        if ($this->getSql() == '') {
            $this->setError("No ha especificado consulta sql");
            $this->setErrno(100);
            $this->logErrores(array("tipo" => "Q", "texto" => "No ha especificado consulta sql"));
            goto controlErrorres;
        }

        $c = $this->getConexionActiva($this->serverDefault);
        if (!($c instanceof Conexion)) {
            $this->res->comunica->script = ' notify("Error al instanciar una clase !!","error");';
            $texto = "FUNCTION[" . __FUNCTION__ . "]:TEXTO[Error al instanciar una clase]";
            $this->logErrores(array("tipo" => "C", "texto" => $texto, 'parametros' => $params));
            echo json_encode($this->res);
            die();
        }
        /* VALIDAR QUE SI EL SERVIDOR ES DE PRODUCCION EL USUARIO MAESTRO NO AFECTE REGISTROS */
        $this->validaMaster($params);

        /* EJECUTAR LA CONSULTA */
        if (!empty((string) $this->acobservacion)) {
            /* @bit_observacion,@usr_id,@usr_ip */
            $c->getResource()->query($this->getBitStatement());
        }

        try {
            $statement = $c->getResource()->prepare($this->getSql(), [PDO::ATTR_CURSOR => $mode]);
            if (count($this->bindedParams) > 0) {
                // no pueden coexistir los dos en un statement
                $params = null;
                foreach ($this->bindedParams as $key => $value) {
                    $statement->bindValue($key, $value);
                }

            }
            if ($this->bindedParams && count($this->bindedParams) > 0) {
                $statement->execute();
            } else {
                $statement->execute($params);
            } 
            $c->setResult($statement);
            $this->bindedParams = [];
            $this->setError("");
            $this->setErrno(0);

            if (strpos(strtoupper($this->acc_sql), 'INSERT') !== false) {
                $this->setUltimoId($c->getResource()->lastInsertId());
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            $this->setErrno($e->getCode());
        }

        /*  SI OCURRIO UN ERROR GUARDAMOS EN BITACORA Y MOSTRAMOS UN MENSAJE O ENVIAMOS SEÑAL AL PROGRAMADOR PARA QUE LO MANEJE COMO MEJOR LE PAREZCA  */
        controlErrorres:
        if ($this->getErrno() > 0) {
            /************  BITACORA DE ERRORES EN LA CONSULTA ******************/
            $opcion = isset($this->prm->opcion->claveOpcion) ? $this->prm->opcion->claveOpcion : "SIN OPCION";
            $metodo = isset($this->prm->metodo) ? $this->prm->metodo : "SIN METODO";
            $texto = "[" . $opcion . "][" . $metodo . "]";
            $this->logErrores(array("tipo" => "Q", "texto" => $texto, 'parametros' => $params));

            if (!$show) {
                return $this->getConsultaId();
            }
            $response = $this->errorMessage(2);
            echo json_encode($response);
            die();
        } else {
            //return $this->getConsultaId();
            return true;
        }
    }
    public function getSqlSlash()
    {
        $sql = $this->acc_sql;
        $sql = str_replace("'", '"', $sql);
        return $sql;
    }
    public function extraer(int $curOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = PDO::FETCH_ORI_ABS)
    {
        $c = $this->getServer($this->serverDefault);
        return $c->getResult()->fetch(PDO::FETCH_ASSOC, $curOrientation, $cursorOffset);
    }

    public function obtener()
    {
        $c = $this->getServer($this->serverDefault);
        if ($c->getResult() instanceof PDOStatement) {
            return $c->getResult()->fetch(PDO::FETCH_OBJ);
        }
        return [];
    }

    public function obtenerTodos()
    {
        $c = $this->getServer($this->serverDefault);

        if ($c->getResult() instanceof PDOStatement) {
            return $c->getResult()->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }
    
    public function sacar(int $curOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = PDO::FETCH_ORI_ABS)
    {
        $c = $this->getServer($this->serverDefault);
        return $c->getResult()->fetch(PDO::FETCH_BOTH, $curOrientation, $cursorOffset);
    }
    public function getFilasAfectadas()
    {
        $c = $this->getServer($this->serverDefault);
        return $c->getResult()->rowCount();
    }
    public function getNumeroCampos(): int
    {
        $c = $this->getServer($this->serverDefault);
        if ($c instanceof Conexion && $c->getResult() instanceof PDOStatement) {
            return $c->getResult()->columnCount();
        }

        return 0;
    }
    public function getNumeroRegistros(): int
    {
        $c = $this->getServer($this->serverDefault);
        if ($c instanceof Conexion && $c->getResult() instanceof PDOStatement) {
            return $c->getResult()->rowCount();
        }

        return 0;
    }

    /**
     * @deprecated
     * esta función no se usa en ningún lado y no sera migrada a PDO
     */
    public function getNombreCampo($n)
    {
        $n = intval($n);
        $c = $this->getServer($this->serverDefault);
        if ($this->getConexionId() != null) {
            switch ($c->getType()) {
                case 'MySql':
                    $res = mysqli_fetch_fields($this->getConsultaId());
                    return $res[$n]->name;
                    break;
                case 'ODBC':
                    return mssql_field_name($this->getConsultaId(), $n);
                    break;
                case 'SqlServer':
                    $res = sqlsrv_field_metadata($this->getConsultaId());
                    return $res[$n]['Name'];
                    break;
                case 'Oracle':
                    return oci_field_name($this->getConsultaId(), $n);
                    break;
                case 'PostgreSQL':
                    return pg_field_name($this->getConsultaId(), $n);
                    break;
            }
        }
    }
    public function getTipoCampo(int $n): string | null
    {
        $c = $this->getServer($this->serverDefault);
        if ($c instanceof Conexion && $c->getResult() instanceof PDOStatement) {
            $col = $c->getResult()->getColumnMeta($n);
            return $col['native_type'];
        }
        return null;
    }
    public function getNumeroModificados(): int
    {
        $c = $this->getServer($this->serverDefault);
        if ($c instanceof Conexion && $res = $c->getResult()) {
            return $res->rowCount();
        }

        return 0;
    }
    public function mysqldate_to_date($fecha)
    {
        date_default_timezone_set('America/Mexico_City');

        if ($fecha == '' or $fecha == '0000-00-00') {
            return "";
        } else {
            return date("d/m/Y", strtotime($fecha));
        }

    }
    public function date_to_mysqldate($fecha)
    {
        $fecha = str_replace("-", "/", $fecha);
        if (!strcmp($fecha, "now()")) {
            return $fecha;
        } else if ($fecha != "") {

            $trozos = "";

            if (strpos($fecha, '/') !== false) {
                $trozos = explode('/', $fecha, 3);
            } elseif (strpos($fecha, '-') !== false) {
                $trozos = explode('-', $fecha, 3);
            } else {
                echo "error en la fecha" . $fecha;
                return false;
            }

            if (count($trozos) != 3) {
                echo "error en la fecha" . $fecha;
                return false;
            }

            if (strlen($trozos[0]) == 2) {
                return $trozos[2] . "/" . $trozos[1] . "/" . $trozos[0];
            } else {
                return $trozos[0] . "/" . $trozos[1] . "/" . $trozos[2];
            }
        }
        return "NULL";
    }
    public static function getIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_VIA'])) {
            $ip = $_SERVER['HTTP_VIA'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "unknown";
        }
        if ($ip == "::1") {
            $ip = '127.0.0.1';
        }
        return $ip;
    }

    /**
     * @deprecated
     */
    public function get_DetalleError($sqlstate, $mensaje)
    {
        if ($sqlstate == "") {
            $sqlstate = $this->getSqlstate();
        }

        if ($mensaje == "") {
            $sqlstate = $this->getError();
        }

        $sql = 'SELECT * FROM sinpres.cat_SqlState  WHERE sqs_codigo="' . $sqlstate . '" ';

        if ($mensaje != "") {
            $sql .= ' AND sqs_mensaje="' . $mensaje . '"';
        }

        $con = new AccesoDatos();
        $con->setSql($sql);
        $con->conectar();
        $con->consultar();
        $con->desconectar();

        $return = false;
        if ($con->getNumeroRegistros() == 0) {
            return false;
        } else {
            if ($con->getNumeroRegistros() == 1) {
                $data = $con->extraer();
                $return = array(
                    "tabla" => $data['sqs_tabla'],
                    "observaciones" => $data['sqs_observacion'],
                    "evento" => $data['sqs_evento'],
                );
            } else {
                $obs = '';
                $tbl = '';
                $eve = '';
                while ($data = $this->extraer()) {
                    $tbl .= ',' . $data['sqs_tabla'];
                    $obs .= $data['sqs_observacion'] . ' ';
                    $eve .= ',' . $data['sqs_evento'];
                }
                $return = array(
                    "tabla" => $tbl,
                    "observaciones" => $obs,
                    "evento" => $eve,
                );
            }
        }
        return $return;
    }
    private function escribeLogArchivo(string $rutalog, string $texto): void
    {
        $dir = dirname($rutalog);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $log = $rutalog . ".log";
        $file = fopen($log, "a");
        if ($file === false || fwrite($file, $texto . PHP_EOL) === false) {
            if (is_resource($file)) {
                fclose($file);
            }
            return;
        }
        fclose($file);

        $peso = @filesize($log);
        if ($peso === false) {
            return;
        }
        $peso = intval(($peso / 1024) / 1024);
        $hoy = date("d-m-Y");
        if ($peso > 8) {
            $rutamodificada = $rutalog . $hoy . ".txt";
            rename($log, $rutamodificada);
            $file = fopen($log, "a");
            if ($file !== false) {
                fwrite($file, "CREADO:" . $hoy . PHP_EOL);
                fclose($file);
            }
        }
    }

    private function logErrores(array $data)
    {
        /* ----- LOG DE ERROROES DE CONEXION -------- */
        error_reporting(E_ALL);
        if (!file_exists("./log")) {
            try {
                mkdir("./log", 0777, true);
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $parametros = isset($data['parametros']) ? $data['parametros'] : null;
        $bindedSQL = $this->getSql();
        if ($parametros && is_array($parametros)) {
            $values = $parametros;
            $bindedSQL = preg_replace_callback('/\?/', function ($matches) use (&$values) {
                return array_shift($values);
            }, $bindedSQL);
        }

        if ($this->bindedParams && is_array($this->bindedParams) && count($this->bindedParams) > 0) {
            $findings = array_map(function ($i) {return "/\\" . $i . "\b/";}, array_keys($this->bindedParams));
            $values = array_values($this->bindedParams);
            $bindedSQL = preg_replace($findings, $values, $bindedSQL);
        }

        $rutalog = '';
        $USR = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : 0;
        $GPO = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : "NOGPO";
        $UR = isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : "NOUR";

        unset($reflec);
        switch ($data["tipo"]) {
            case "B":
                $rutalog = "./log/bitacoraConsultas" . $GPO . $UR;
                break;
            case "Q":
                //QUERY
                $rutalog = "./log/errorConsulta" . $GPO . $UR;
                break;
            case "C":
                // CONEXION
                $rutalog = "./log/errorConexion" . $GPO . $UR;
                break;
            case "P":
                // CUANDO SE INTENTA AFECTAR UN REGISTRO CON USUARIO MAESTRO
                $rutalog = "./log/errorMaster" . $GPO . $UR;
                break;
            default:
                //UNDEFINED
                $rutalog = "./log/errorAccesoDatos" . $GPO . $UR;
                break;
        }
        $texto = date("d-m-Y H:i:s")
        . "CLASE[" . get_Class($this) . "]||"
        . "CLIENTE[" . $_SERVER['REMOTE_ADDR'] . "]||"
        . "USRSES[" . $USR . "]||"
        . "NOERROR[" . $this->getErrno() . "]||"
        . "ERROR[" . $this->getError() . "]||"
        . "CONSULTA[" . $this->getSql() . "]||"
        . "PARSEDQUERY[" . $bindedSQL . "]||";

        if ($parametros) {
            $texto .= "PARAMETROS[" . json_encode($parametros, JSON_UNESCAPED_UNICODE) . "]||";
        }
        if ($this->bindedParams && is_array($this->bindedParams) && count($this->bindedParams) > 0) {
            $texto .= "BINDEDPARAMS[" . json_encode($this->bindedParams) . "]||";
        }
        $texto .= "ESTADO[" . $this->getSqlstate() . "]||"
            . $data["texto"];

        // Log por módulo/UR (comportamiento existente)
        $this->escribeLogArchivo($rutalog, $texto);

        // Log general diario: ORIGEN[BASE] + controlador/método para ubicar el error
        if ($data["tipo"] !== 'B') {
            $tipoBd = match ($data["tipo"]) {
                'Q' => 'CONSULTA',
                'C' => 'CONEXION',
                'P' => 'MASTER',
                default => 'ACCESO_DATOS',
            };
            $contexto = function_exists('contextoLogError')
                ? contextoLogError($this)
                : ('CONTROLADOR[' . get_class($this) . ']');
            $lineaGeneral = 'ORIGEN[BASE]||TIPO[' . $tipoBd . ']||' . $contexto
                . '||NOERROR[' . $this->getErrno() . ']'
                . '||ERROR[' . $this->getError() . ']'
                . '||ESTADO[' . $this->getSqlstate() . ']'
                . '||CONSULTA[' . $this->getSql() . ']'
                . '||' . $data['texto'];
            if (function_exists('escribirLogProyecto')) {
                escribirLogProyecto('errorGeneral', $lineaGeneral);
            } else {
                $rutaGeneral = dirname(__DIR__) . '/log/errorGeneral-' . date('Y-m-d');
                $this->escribeLogArchivo($rutaGeneral, date('d-m-Y H:i:s') . ' ' . $lineaGeneral);
            }
        }
        /************************************/
        return $data;
    }
    public function replace_novalid_char($string, $espacios = false)
    {
        if ($espacios) {
            $novalidos = array(
                ' ', "'", '!', '#', '$', '%', '&', '(', ')', '=',
                '¡', '¿', '+', '}', '{', '[', ']', '°', '¬', '~', '`', '^', "\\", '"',
            );
            return str_replace($novalidos, "", $string, $count);
        } else {
            $novalidos = array(
                "'", '!', '#', '$', '%', '&', '(', ')', '=', '¡',
                '¿', '+', '}', '{', '[', ']', '°', '¬', '~', '`', '^', "\\", '"',
            );
            return str_replace($novalidos, "", $string, $count);
        }
    }
    private function gtAccesos()
    {
        // Ruta por ambiente en constantesCredenciales.php (RUTA_ACCESOS_XML); fallback al conf local.
        $access = defined('RUTA_ACCESOS_XML') && RUTA_ACCESOS_XML !== ''
            ? RUTA_ACCESOS_XML
            : (__DIR__ . '/conf/accesos.xml');
        if (!file_exists($access)) {

            $res = new stdClass();
            $res->comunica = new stdClass();
            $res->comunica->html['cuerpoSinpres'] = '<contenedorScript></contenedorScript>';
            $res->comunica->script = '
                alertify.alert("ERROR DE CONFIGURACIÓN, SIN RUTAS DE BASE DE DATOS, CONFIGURE LOS ACCESOS").setHeader("<h3 class=\"btn-outline-danger\">Error! </h3>");';
            echo json_encode($res);
            die();
        }

        $xml = simplexml_load_file($access);
        foreach ($xml as $val) {
            $res = $this->xml2array($val);
            $name = $res["@attributes"]["name"];
            $servidor = $res["servidor"];
            $puerto = $res["puerto"];
            $usuario = $res["usuario"];
            $pass = $res["password"];
            $basedatos = $res["basedatos"];
            $tipo = $res["tipo"];

            $con = new Conexion();
            $con->setServidor($servidor);
            $con->setPuerto($puerto);
            $con->setUsuario($usuario);
            $con->setPassword($pass);
            $con->setBaseDatos($basedatos);
            $con->setType($tipo);
            $this->addServer($name, $con);
        }
    }
    private function xml2array($xmlObject, $out = array())
    {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? $this->xml2array($node) : $node;
        }

        return $out;
    }
    private function errorMessage($t)
    {
        $res = new stdClass();
        $res->comunica = new stdClass();

        switch ($t) {
            case 1:
                $mensaje = '';
                if (in_array($_SERVER['REMOTE_ADDR'], $this->ipProgramadores)) {
                    $mensaje = "<br/>[" . $this->getError() . "]";
                }
                $res->comunica->script = '
                    alertify.alert("El error ha ocurrido en la base de datos, el numero de error es:'
                . $this->getErrno() . '<br>Levante un ticket al soporte del sistema con los datos del modulo,'
                    . ' pantalla, opcion y el numero de error.'
                    . $mensaje . '")
                        .setHeader("<h3 class=\"btn-outline-danger\">Ha Ocurrido un Error! </h3>");
                ';
                break;
            case 2:
                $mensaje = "";
                $titulo = "";

                if (
                    (isset($_SESSION[_S]['usr_id']) && $_SESSION[_S]['usr_id'] != ""
                        && isset($_SESSION[_S]['mst']) && $_SESSION[_S]['mst'] == true)
                    || in_array($_SERVER['REMOTE_ADDR'], $this->ipProgramadores)
                ) {
                    $sql = $this->getSql();
                    $error = $this->getError();
                    $chrSearch = array('/"/', "/'/", "/\r\n/", "/\n/", "/\r/");
                    $chrReplace = array('&quot;', "&#39;", "|", "|", "|");
                    $chrReplaceHtml = array('&quot;', "&#39;", "<br/>", "<br/>", "<br/>");

                    $sql = preg_replace($chrSearch, $chrReplace, $sql);
                    $error = preg_replace($chrSearch, $chrReplaceHtml, $error);

                    $res->comunica->script = '
                        var n = $(".mysql").length;
                        if (n == undefined || isNaN(n)) n = 0;
                        else n = n + 1;
                        otro = "code" + n;
                        notify2(
                            "<div class=\"db-error-panel\">"
                            + "<p class=\"db-error-line\"><strong>Error numero:</strong> ' . $this->getErrno() . '</p>"
                            + "<p class=\"db-error-line\"><strong>Motor respondio:</strong> ' . $error . '</p>"
                            + "<p class=\"db-error-line\"><strong>Tu consulta:</strong></p>"
                            + "<textarea class=\"mysql\" id=\"" + otro + "\">' . htmlspecialchars($sql) . '</textarea>"
                            + "</div>",
                            "error",
                            "ERROR DE BASE DE DATOS",
                            false
                        );
                        var $box = $(".notify_message").last();
                        $box.addClass("db-error-notify").stop(true, true).css({
                            left: "50%",
                            top: "60px",
                            width: "90%",
                            maxWidth: "960px",
                            transform: "translateX(-50%)",
                            opacity: 1,
                            overflow: "auto"
                        });
                        setTimeout(function () {
                            text_formatSql(otro);
                            if (window.editor && typeof window.editor.refresh === "function") {
                                window.editor.setSize("100%", "220px");
                                window.editor.refresh();
                            }
                        }, 250);
                        ';
                } else {
                    $titulo = "HA OCURRIDO UN ERROR CONTROLADO";
                    // Detalle técnico (SQLSTATE, usuario BD, procedimiento, etc.) solo en logErrores();
                    // al usuario final solo se muestra el número de error para el ticket.
                    $mensaje = 'El error ha ocurrido en la base de datos, el numero de error es:' . $this->getErrno()
                    . '<br>Levante un ticket con los datos del modulo, pantalla, opcion'
                    . ' y el numero de error.';
                    if ($this->getErrno() == 1644) {
                        $mensaje = $this->getError();
                        $titulo = "HA OCURRIDO UN ERROR EN SU REGISTRO";
                    }

                    $res->comunica->script = '
                        alertify.alert( "' . $mensaje . ' ")
                        .setHeader("<h3 class=\"btn-outline-danger\">' . $titulo . '</h3>");';
                }
                break;
        }
        return $res;
    }
    private function validaMaster($params)
    {
        if (
            (strpos(strtoupper($this->acc_sql), 'INSERT ') !== false)
            || (strpos(strtoupper($this->acc_sql), 'UPDATE ') !== false)
            || (strpos(strtoupper($this->acc_sql), 'DELETE ') !== false)
            || (strpos(strtoupper($this->acc_sql), 'CALL ') !== false)
        ) {
            $this->logErrores(array("tipo" => "B", "texto" => " BITACORA DE AFECTACION DE TABLAS ", 'parametros' => $params));

            if (
                $_SERVER['SERVER_ADDR'] == "172.17.90.88" && 
                isset($_SESSION[_S]['mst']) && 
                $_SESSION[_S]['mst'] == true && 
                (strpos($this->acc_sql, BD_USUARIOBIT . '.log_Conexion') === false)
            ) {
                $opcion = isset($this->prm->opcion->claveOpcion) ? $this->prm->opcion->claveOpcion : "SIN OPCION";
                $metodo = isset($this->prm->metodo) ? $this->prm->metodo : "SIN METODO";
                $texto = "[" . $opcion . "][" . $metodo . "]" . $this->getSql() . PHP_EOL;
                $this->logErrores(array("tipo" => "P", "texto" => $texto));
                $res = new stdClass();
                $res->comunica = new stdClass();
                $res->comunica->html = "";
                $res->comunica->script = '
                    alertify.alert( "NO PUEDE AFECTAR REGISTROS CON ESTE USUARIO")
                        .setHeader("<h3 class=\"btn-outline-danger\">VERBOTEN!!</h3>");';
                header("HTTP/1.0 401 DataBase Error [Este usuario no esta autorizado a modificar registros]");
                echo json_encode($res);
                die();
            }
        }
    }
    public function getBitStatement()
    {
        $sql = '';
        if (!empty((string) $this->acobservacion)) {
            $id = (isset($_SESSION[_S]['usr_id']) ?
                $_SESSION[_S]['usr_id']
                : 0);
            $sql = 'SET @bit_observacion="' .
            $this->acobservacion .
            '" ,@usr_ip=INET_ATON("' . $this->getIp() . '"),@usr_id=' .
                $id;
            return $sql;
        } else {
            return false;
        }
    }
}
