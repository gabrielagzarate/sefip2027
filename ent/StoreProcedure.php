<?php
/**
 * Author: Israel Faustino Cruz
 * Created: Jul 28, 2017
 * Comment: Clase Entidad para la ejecución de procedimientos almacenados
 * Class Parameters:
 *     ATRIBUTOS DE LA CLASE
 *     METODOS GET
 *     METODOS SET
 *     METODOS STA (SET sin persistencia)
 *     CONSTRUCTOR
 *     FUNCIONES ESTÁTICAS
 * Considerations: actualmente solo se ha validado para procedimientos con parametros de entrada
 * Update:
 * Manuel Cih: Ene 26, 2018: Se modifica linea 107 para mandar error cuando la fecha no sea valida
 * Israel: Ene26, 2018: Ajuste de los mensajes
 */

include_once dirname(__FILE__) . '/../lib/AccesoDatos.php';
include_once dirname(__FILE__) . '/../lib/Atributo.php';
include_once dirname(__FILE__) . '/../lib/validaFecha.php';

class StoreProcedure extends AccesoDatos
{
    /******************************************************
    DECLARACION DE VARIABLES
     ******************************************************/
    private $me;
    private $storeProcedure;
    private $servidorDefault;
    protected $usr_ip; //
    protected $usr_id; //
    private $bandera; //
    private $mensaje; //

    /******************************************************
    DECLARACION FUNCIONES GET
     ******************************************************/
    public function getStoreProcedure()
    {return $this->storeProcedure;}
    protected function getMe()
    {return $this->me;}
    public function __get($name)
    {if (isset($this->$name)) {
        return $this->$name;
    }
    }
    public function getUsr_ip()
    {return $this->usr_ip;}
    public function getUsr_id()
    {return $this->usr_id;}
    public function getBandera()
    {return $this->bandera;}
    public function getMensaje()
    {return $this->mensaje;}

    /******************************************************
    DECLARACION FUNCIONES SET
     ******************************************************/
    public function setStoreProcedure($val)
    {$this->storeProcedure = $val;}
    protected function setMe($val)
    {$this->me = $val;}
    public function __set($name, $val)
    {if (isset($this->$name)) {
        $this->$name = $val;
    }
    }
    public function setUsr_id()
    {$this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : 0;}
    public function setBandera($val)
    {$this->bandera = $val;}
    public function setMensaje($val)
    {$this->mensaje = $val;}
    public function setUsr_ip()
    {
        $this->usr_ip = ip2long((isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1"
            && $_SESSION[_S]['ses-ip'] != '127.0.0.1') ? $_SESSION[_S]['ses-ip'] : 'ERROR');
    }

    /******************************************************
    CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {
        //Recibiendo los parametros de la Clase Entidad
        $parametros = (func_num_args() > 0) ? ((is_object(func_get_arg(0))) ? func_get_arg(0) : ((is_array(func_get_arg(0))) ? (object) func_get_arg(0) : null)) : null;

        //parametros de conexion
        parent::__construct($parametros);

        //instanciando un espejo
        $this->setMe(new ReflectionClass($this));

        $procedimientoAlmacenado = (!isset($parametros->procedimientoAlmacenado)) ? '' : $parametros->procedimientoAlmacenado;
        $this->servidorDefault = $this->getServerDefault();
        $this->setStoreProcedure($procedimientoAlmacenado);

        $this->usr_ip = new Atributo("ip", "NO", "PRI", "in_usr_ip");
        $this->usr_id = new Atributo("usr", "NO", "PRI", "in_usr_id");

        $this->setUsr_ip();
        $this->setUsr_id();
    }

    public function execute($conectar = true, $error = false)
    { //consideramos que no hay error
        $sql = "CALL " . $this->getStoreProcedure();
        $values = "";
        $properties = $this->me->getDefaultProperties();
        foreach ($properties as $key => $atributo) {
            if ($this->$key instanceof Atributo) {
                switch ($this->$key->getTipo()) {
                    case 'fecha':
                    case 'date':
                    case 'datetime':
                        $valor = ($this->$key->getVal() != '') ? $this->$key->getVal() : '';
                        $fechaEntrada = new stdClass();
                        $fechaEntrada->fecha = $valor;
                        $fechaSalida = validaFecha($fechaEntrada);
                        if (!$fechaSalida->valido) {
                            $error = true;
                            $this->setBandera(0);
                            $this->setMensaje("STOREPROCEDURE: " . $key . "->'" . $valor . "'->" . $fechaSalida->mensaje);
                        } else {
                            $valor = "'" . $fechaSalida->fechaMysql . "'";
                        }
                        break;
                    case 'string':
                    case 'cadena':
                        $valor = strtoupper($this->$key->getVal());
                        $longitud = $this->$key->getLongitud();
                        if (is_numeric($longitud) && $longitud > 0) {
                            if (strlen($valor) > $longitud) {
                                //AQUI EVALUO LA LONGITUD DE LA CADENA ENTRATE
                                $error = true;
                                $this->setBandera(0);
                                $this->setMensaje("STOREPROCEDURE: " . $key . "->'" . $valor . "' LA LONGITUD DEL TEXTO EXCEDE EL RANGO PERMITIDO(>" . $longitud . ") ");
                            }
                        } elseif (is_array(explode(',', str_replace('\'', '', $longitud)))) {
                            //SI ES ENUM EVALUO SI EL VALOR ENTRANTE ESTA DENTRO DEL ARREGLO
                            $array = explode(',', str_replace('\'', '', $longitud));
                            if (!in_array($valor, $array)) {
                                $error = true;
                                $this->setBandera(0);
                                $this->setMensaje("STOREPROCEDURE: " . $key . "->'" . $valor . "' EL VALOR SE ENCUENTRA FUERA DE RANGO");
                            }
                        } else {
                            //SI NO NO SE QUE HACER
                            $error = true;
                            $this->setBandera(0);
                            $this->setMensaje("STOREPROCEDURE: " . $key . "->'" . $valor . "' TIPO DE VALOR NO RECONOCIDO");
                        }
                        $valor = "'" . $valor . "'";
                        break;
                    case 'caracter':
                        // Esta codificacion, utf-8, convierte a mayusculas las vocales acentuadas y ñ's
                        $valor = mb_strtoupper("'" . $this->$key->getVal() . "'", 'UTF-8');
                        if (strlen($valor) > 3) {
                            $error = true;
                            $this->setBandera(0);
                            $this->setMensaje("STOREPROCEDURE: " . $key . "->'" . $valor . "'");
                        }
                        break;
                    default:
                        $valor = $this->$key->getVal();
                        if (strlen($valor) > $this->$key->getLongitud()) {
                            $error = true;
                            $this->setBandera(0);
                            $this->setMensaje("STOREPROCEDURE: " . $key . "->'" . $valor . "'-> LONGITUD DE ENTRADA(" . strlen($valor) . ") > LONGITUD PERMITIDA(" . $this->$key->getLongitud() . ")");
                        }
                        break;
                }

                switch ($this->$key->getKeys()) {
                    case 'PRI':
                        if (($valor == '\'\'' || $valor == '') && $valor != 0) {
                            $error = true;
                            $this->setBandera(0);
                            $this->setMensaje("STOREPROCEDURE: " . $key . "->" . $valor . "");
                        }
                        break;
                    default:
                        break;
                }
                $values .= $valor . ", ";
            } elseif (!($this->$key instanceof Atributo) && $key == 'sis_id') {
                $sis_id = mb_strtoupper($this->$key, 'UTF-8');
                switch ($sis_id) {
                    case 'POA':
                        $sis_id = 'Poa';
                        break;
                    case 'SCP':
                        $sis_id = 'Presupuesto';
                        break;
                    case 'SCG':
                        $sis_id = 'Contabilidad';
                        break;
                    case 'TES':
                        $sis_id = 'Tesoreria';
                        break;
                    default:
                        $error = true;
                        $this->setBandera(0);
                        $this->setMensaje('STOREPROCEDURE: SISTEMA->' . $sis_id);
                }
            } elseif (!($this->$key instanceof Atributo) && $key == 'bit_observacion') {
                if ($conectar) {
                    $this->conectar($this->servidorDefault);
                }
                if ($this->$key != '') {
                    $this->setSql("SET @usr_ip =INET_ATON('" . AccesoDatos::getIp() . "'), @usr_id = " . $this->getUsr_id() . ", @bit_observacion = '" . $this->$key . "';");
                    $this->consultar();
                } else {
                    $error = true;
                    $this->setBandera(0);
                    $this->setMensaje("STOREPROCEDURE: OBSERVACIÓN");
                }
            } elseif (!($this->$key instanceof Atributo) && $key == 'ejercicio') {
                $sql .= $this->$key . "( ";
            }
        }

        $values .= "INET_ATON('" . AccesoDatos::getIp() . "'), " . $this->getUsr_id() . " ) ";
        $this->setSql($sql . $values);

        if (!$error) {
            $this->consultar();
            $this->setSql('SELECT @bandera' . $sis_id . ' AS bandera, @mensaje' . $sis_id . ' AS mensaje');
            $this->consultar();
            $rowMensaje = $this->extraer();

            $this->setBandera($rowMensaje['bandera']);
            $this->setMensaje($rowMensaje['mensaje']);

        }

        $this->desconectar();
    }
}
