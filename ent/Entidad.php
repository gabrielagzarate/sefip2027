<?php

require_once dirname(__FILE__) . "/../lib/constantes.php";
include_once dirname(__FILE__) . '/../lib/AccesoDatos.php';
include_once dirname(__FILE__) . '/../lib/Atributo.php';
include_once dirname(__FILE__) . '/../lib/Meses.php';

class Entidad extends AccesoDatos
{
    /*******************************************************
     * DECLARACIÓN DE VARIABLES
     ******************************************************/
    private $me;
    private $table;
    private $servidorDefault;
    public $excepcionesMayusculas = [
            'ur_correoElectronicoTitular',
            'ur_correoElectronicoAdministrativo',
            'ur_correoElectronicoPlaneacion',
            'ur_correoElectronicoEvaluacion',
            'ur_correoElectronicoContacto'
            ];

    /*******************************************************
     * DECLARACIÓN DE FUNCIONES GET
     ******************************************************/
    public function getTable()
    {
        return $this->table;
    }

    protected function getMe()
    {
        return $this->me;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
    }

    /*******************************************************
     * DECLARACIÓN DE FUNCIONES SET
     ******************************************************/
    public function setTable($val)
    {
        $this->table = $val;
    }

    protected function setMe($val)
    {
        $this->me = $val;
    }

    public function __set($name, $val)
    {
        if (isset($this->$name)) {
            $this->$name = $val;
        }
    }

    /*******************************************************
     * MAGIC __call: emula getX(), setX($v), staX($v)
     * delegando al Atributo (o devolviendo el Meses).
     ******************************************************/
    public function __call($name, $args)
    {
        $prefix = substr($name, 0, 3);
        if ($prefix !== 'get' && $prefix !== 'set' && $prefix !== 'sta') {
            return null;
        }
        $prop = lcfirst(substr($name, 3));
        if (!property_exists($this, $prop)) {
            return null;
        }
        $attr = $this->$prop;
        if ($attr instanceof Atributo) {
            if ($prefix === 'get') {
                return $attr->getVal();
            }
            if ($prefix === 'set') {
                $attr->setVal($args[0] ?? null);
                return null;
            }
            $attr->staVal($args[0] ?? null);
            return null;
        }
        if ($attr instanceof Meses && $prefix === 'get') {
            return $attr;
        }
        return null;
    }

    /*******************************************************
     * fill: copia los valores de $p hacia los Atributos
     * y Meses cuyo nombre coincide con una llave de $p.
     * $stable = true usa staVal (sin marcar cambio).
     ******************************************************/
    public function fill($p, $stable = false)
    {
        if (!is_object($p) && !is_array($p)) {
            return;
        }
        $p = (object) $p;
        $properties = $this->me->getDefaultProperties();
        foreach ($properties as $key => $default) {
            if (!isset($p->$key)) {
                continue;
            }
            if ($this->$key instanceof Atributo) {
                $stable ? $this->$key->staVal($p->$key) : $this->$key->setVal($p->$key);
            } elseif ($this->$key instanceof Meses) {
                $this->$key->setArrayMeses($p->$key);
            }
        }
    }

    /*******************************************************
     * CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {
        // Recibiendo los parametros de la Clase Entidad
        $parametros = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        // parametros de conexion
        parent::__construct($parametros);

        // instanciando un espejo
        $this->setMe(new ReflectionClass($this));

        $tabla = (!isset($parametros->tabla)) ? '' : $parametros->tabla;
        $this->servidorDefault = $this->getServerDefault();
        $this->setTable($tabla);
    }

    public function insert($conectar = true, $error = true, $servidor = "")
    {
        $sql = "INSERT INTO " . $this->getTable() . "( ";
        $values = " ) VALUES ( ";
        $properties = $this->me->getDefaultProperties();
        $params = [];

        foreach ($properties as $key => $atributo) {
            if ($this->$key instanceof Atributo && $this->$key->getVal() != '') {
                $sql .= $this->$key->getName() . ", ";
                $nombreCampo = $this->$key->getName();
                $values .= " :{$this->$key->getName()}";
                switch ($this->$key->getTipo()) {
                    case 'fecha':
                    case 'date':
                    case 'datetime':
                        if ($this->$key->getVal() == 'now()') {
                            $params[$nombreCampo] = ($this->$key->getVal() != "") ? $this->date_to_mysqldate($this->$key->getVal()) : "";
                        } else {
                            $params[$nombreCampo] = ($this->$key->getVal() != "") ? $this->date_to_mysqldate($this->$key->getVal()) : "0000-00-00 00:00:00";
                        }

                        break;
                    case 'string':
                    case 'cadena':
                        if (in_array($nombreCampo,$this->excepcionesMayusculas))
                        {
                            $params[$nombreCampo] = $this->$key->getVal();
                        }else
                            {
                            $params[$nombreCampo] = mb_strtoupper($this->$key->getVal(), 'UTF-8');
                            }
                        break;
                    case 'sensitive':
                        $params[$nombreCampo] = $this->$key->getVal();
                        break;
                    case 'md5':
                        $params[$nombreCampo] = md5(strtoupper($this->$key->getVal()));
                        break;
                    case 'ip':
                        $params[$nombreCampo] = ip2long($this->$key->getVal());
                        break;
                    case 'email':
                        $params[$nombreCampo] = $this->$key->getVal();
                        break;
                    default:
                        // $values .= $this->$key->getVal();
                        $params[$nombreCampo] = $this->$key->getVal();
                        break;
                }
                $values .= ", ";
                /* cuando se instancia un objeto de tipo Meses, se hace un tratamiento especial, que recorre los
            elementos, es decir los meses, y los procesa*/
            } else if ($this->$key instanceof Meses) {
                $meses = $this->$key->__get("me")->getDefaultProperties();
                //se recorren todos los miembros, del atributo
                foreach ($meses as $idMes => $mes) {
                    /*se procesan solo los que son atributo*/
                    if ($this->$key->__get($idMes) instanceof Atributo && $this->$key->$idMes->getVal() != '') {
                        $sql .= $this->$key->$idMes->getName() . ", ";
                        $values .= " :{$this->$key->$idMes->getName()}";
                        switch ($this->$key->$idMes->getTipo()) {
                            case 'cadena':
                            case 'string':
                                $params[$this->$key->$idMes->getName()] = mb_strtoupper($this->$key->$idMes->getVal(), 'UTF-8');
                                break;
                            default:
                                $params[$this->$key->$idMes->getName()] = $this->$key->$idMes->getVal();
                                break;
                        }
                        $values .= ", ";
                    }
                }
            }
        }

        switch ($this->getTable()) {
            case BD_BITACORA . ".bit_Usuario":
                $sql .= " usu_id, usr_fecha ";
                $params['usu_id'] = $_SESSION[_S]['usr_id'];
                $values .= ' :usu_id ';
                break;
            case "cat_Usuario":
                $sql .= " usr_usu, usr_fecha ";
                $params['usu_usu'] = $_SESSION[_S]['usr_id'];
                $values .= ' :usu_usu ';
                break;
            default:
                $sql .= " usr_id,usr_fecha ";
                $params['usr_id'] = $_SESSION[_S]['usr_id'];
                $values .= ' :usr_id ';
                break;
        }
        $values .= ", now() ) ";

        if ($conectar) {
            // Marcos: 07/dic/2017
            // Para deuda publica, adicionamos la opción de consultar a otro servidor
            if ($servidor != "") {
                $this->conectar($servidor);
            } else {
                $this->conectar($this->servidorDefault);
            }
        }
        
        $this->setSql($sql . $values);
        if ($error) {
            $this->consultar($params);
        } else {
            $this->consultar($params, false);
        }
    }

    public function update($conectar = true, $servidor = "")
    {
        switch ($this->getTable()) {
            case 'gen_Mensaje':
                $sql = "UPDATE " . $this->getTable() . " SET mje_idRemitente=" . $_SESSION[_S]['usr_id'] . ", usu_fecha=now(), ";
                break;
            case 'cat_Usuario':
                $sql = "UPDATE " . $this->getTable() . " SET usr_fecha=now(), ";
                break;
            case 'presupuesto':
                $sql = "UPDATE " . $this->getTable() . " SET ";
                break;
            default:
                $sql = "UPDATE " . $this->getTable() . " SET ";
                break;
        }
        $where = " WHERE ";
        $params = [];
        $properties = $this->me->getDefaultProperties();
        $coma = false;
        $and = false;
        foreach ($properties as $key => $atributo) {
            if ($this->$key instanceof Atributo) { //si es atributo
                if ($this->$key->getKeys() == "PRI") { //si es llave primaria, se forma el where
                    $params[$this->$key->getName()] = $this->$key->getVal();
                    $where .= $this->$key->getName() . " = :" . $this->$key->getName() . " AND ";
                    $and = true;
                } else if ($this->$key->getChange()) {
                    $sql .= $this->$key->getName() . " = :" . $this->$key->getName();
                    switch ($this->$key->getTipo()) {
                        case 'fecha':
                        case 'date':
                        case 'datetime':
                            if ($this->$key->getVal() == 'now()') {
                                $params[$this->$key->getName()] = ($this->$key->getVal() != "") ? $this->date_to_mysqldate($this->$key->getVal()) : "";
                            } else {
                                $params[$this->$key->getName()] = ($this->$key->getVal() != "") ? $this->date_to_mysqldate($this->$key->getVal()) : null;
                            }
                            break;
                        case 'cadena':
                        case 'string':
                            //Esta codificacion, utf-8, convierte a mayusculas las vocales acentuadas y ñ's
                            if (in_array($this->$key->getName(), $this->excepcionesMayusculas))
                            {
                                $params[$this->$key->getName()] = $this->$key->getVal();
                            }else
                            {
                                $params[$this->$key->getName()] = mb_strtoupper($this->$key->getVal(), 'UTF-8');
                            }
                            break;
                        case 'sensitive':
                            $params[$this->$key->getName()] = $this->$key->getVal();
                            break;
                        case 'md5':
                            $params[$this->$key->getName()] = md5(strtoupper($this->$key->getVal()));
                            break;
                        case 'ip':
                            $params[$this->$key->getName()] = ip2long($this->$key->getVal());
                            break;
                        case 'email':
                            $params[$this->$key->getName()] = $this->$key->getVal();
                            break;
                        default:
                            $params[$this->$key->getName()] = $this->$key->getVal();
                            break;
                    }
                    $sql .= ", ";
                    $coma = true;
                }
            } else if ($this->$key instanceof Meses) {
                $meses = $this->$key->__get("me")->getDefaultProperties();
                //se recorren todos los miembros, del atributo
                foreach ($meses as $idMes => $mes) {
                    /*se procesan solo los que son atributo*/
                    if ($this->$key->__get($idMes) instanceof Atributo) {
                        if ($this->$key->$idMes->getChange()) {
                            $sql .= $this->$key->$idMes->getName() . " = :" . $this->$key->$idMes->getName();
                            switch ($this->$key->$idMes->getTipo()) {
                                case 'cadena':
                                case 'string':
                                    $params[$this->$key->$idMes->getName()] = mb_strtoupper($this->$key->$idMes->getVal(), 'UTF-8');
                                    break;
                                default:
                                    if ($this->$key->$idMes->getVal() == '') {
                                        $params[$this->$key->$idMes->getName()] = ' 0 ';
                                    } else {
                                        $params[$this->$key->$idMes->getName()] = $this->$key->$idMes->getVal();
                                    }
                                    break;
                            }
                            $sql .= ", ";
                            $coma = true;
                        }
                    }
                }
            }
        }

        if ($coma) {
            $sql = substr($sql, 0, -2);
        }
        //si hay, se quita la ', '
        else {
            $this->setError("No hay campos que actualizar, {$this->getMe()->getName()}->setTable()");
        }

        if ($and) {
            $where = substr($where, 0, -5); //si hay se quita el ' AND '
        } else {
            $this->setError("No ha especificado llave primaria para la actualizacion de la tabla, {$this->getMe()->getName()}->setTable()");
        }

        if ($coma) {
            if ($conectar) {
                // Marcos: 07/dic/2017
                // Para deuda publica, adicionamos la opción de consultar a otro servidor
                if ($servidor != "") {
                    $this->conectar($servidor);
                } else {
                    $this->conectar($this->servidorDefault);
                }
            }

            $this->setSql($sql . $where);
            $this->consultar($params);
        }
    }

    public function delete($conectar = true, $servidor = "")
    {
        $sql = "DELETE FROM " . $this->getTable();
        $where = " WHERE ";
        $params = [];
        $properties = $this->me->getDefaultProperties();
        $and = false;
        foreach ($properties as $key => $atributo) {
            if ($this->$key instanceof Atributo) { //si es atributo
                if ($this->$key->getKeys() == "PRI") { //si es llave primaria, se forma el where
                    $params[$this->$key->getName()] = $this->$key->getVal();
                    $where .= $this->$key->getName() . " = :" . $this->$key->getName() . " and ";
                    $and = true;
                }
            }
        }
        if ($and) {
            $where = substr($where, 0, -5);
        }
        //si hay se quita el ' and '

        if ($conectar) {
            // Marcos: 07/dic/2017
            // Para deuda publica, adicionamos la opción de consultar a otro servidor
            if ($servidor != "") {
                $this->conectar($servidor);
            } else {
                $this->conectar($this->servidorDefault);
            }
        }
        $this->setSql($sql . $where);
        $this->consultar($params);
        $this->desconectar();
    }

    public function select($servidor = '')
    {
        $sql = "SELECT ";
        $from = " FROM " . $this->getTable();
        $where = " WHERE ";
        $properties = $this->me->getDefaultProperties();
        $coma = false;
        $and = false;
        $params = [];
        foreach ($properties as $key => $atributo) {
            if ($this->$key instanceof Atributo) { //si es atributo
                if ($this->$key->getKeys() == "PRI") { //si es llave primaria, se forma el where
                    $params[$this->$key->getName()] = $this->$key->getVal();
                    $where .= $this->$key->getName() . " = :" . $this->$key->getName() . " AND ";
                    $and = true;
                } else {
                    $sql .= $this->$key->getName() . ", ";
                    $coma = true;
                }
                /*cargando elementos de tipo Meses*/
            } else if ($this->$key instanceof Meses) {
                $meses = $this->$key->__get("me")->getDefaultProperties();
                foreach ($meses as $idMes => $mes) {
                    if ($this->$key->__get($idMes) instanceof Atributo) {
                        $sql .= $this->$key->$idMes->getName() . ", ";
                        $coma = true;
                    }
                }
            }
        }

        if ($coma) {
            $sql = substr($sql, 0, -2);
        }
        //si hay, se quita la ', '
        if ($and) {
            $where = substr($where, 0, -5);
        }
        //si hay se quita el ' AND '

        // Marcos: 07/dic/2017
        // Para deuda publica, adicionamos la opción de consultar a otro servidor
        if ($servidor != "") {
            $this->conectar($servidor);
        } else {
            $this->conectar($this->servidorDefault);
        }

        $this->setSql($sql . $from . $where);
        $this->consultar($params);
        $row = $this->extraer();
        $this->desconectar();

        foreach ($properties as $key => $atributo) {
            if ($this->$key instanceof Atributo) {
                if ($this->$key->getKeys() != "PRI") { //si no es llave primaria
                    switch ($this->$key->getTipo()) {
                        case 'date':
                        case 'fecha':
                            $this->$key->staVal($this->mysqldate_to_date($row[$this->$key->getName()] ?? null));
                            break;
                        case 'datetime':
                            $this->$key->staVal($this->mysqldate_to_date($row[$this->$key->getName()], true));
                            break;
                        case 'ip':
                            $this->$key->staVal(long2ip($row[$this->$key->getName()]));
                            break;
                        default:
                            if (is_array($row)) {
                                $this->$key->staVal($row[$this->$key->getName()]);
                            }
                            break;
                    }
                }
            } else if ($this->$key instanceof Meses) {
                foreach ($meses as $idMes => $mes) {
                    if ($this->$key->__get($idMes) instanceof Atributo) {
                        switch ($this->$key->$idMes->getTipo()) {
                            default:
                                if (is_array($row)) {
                                    $this->$key->$idMes->staVal($row[$this->$key->$idMes->getName()]);
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

    /*******************************************************
     * PERSISTENCIA DE DATOS
     *******************************************************/
    public function mysqldate_to_date($fecha, $hora = false)
    {
        date_default_timezone_set('America/Mexico_City');

        if ($fecha == '' or $fecha == '0000-00-00') {
            return "";
        } elseif ($hora) {
            return date("d/m/Y H:i:s", strtotime($fecha));
        } else {
            return date("d/m/Y", strtotime($fecha));
        }
    }

    public function date_to_mysqldate($fecha)
    {
        if (!strcmp($fecha, "now()")) {
            return $fecha;
        } elseif ($fecha != "") {
            $trozos = explode("/", $fecha, 3);
            if (count($trozos) < 3) {
                $trozos = explode("-", $fecha, 3);
            }
            if (count($trozos) == 3) {
                if (strlen($trozos[0]) == 2) {
                    return $trozos[2] . "/" . $trozos[1] . "/" . $trozos[0];
                } else { /*si ya viene en formato año, mes, dia*/
                    return $trozos[0] . "/" . $trozos[1] . "/" . $trozos[2];
                }
            }
            return 'NULL';
        }
        return "NULL";
    }
}