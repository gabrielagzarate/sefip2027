<?php
declare (strict_types = 1);

if (session_id() == PHP_SESSION_NONE && !isset($_SESSION[_S])) {
    session_start();
}
require_once dirname(__FILE__) . '/constantes.php';
/* ------------------------------------------------------------------------------------------------------------------ */
// File name   : RutaArchivoSefip.php
// Version     : 3.0.0
// Begin       : 2017-04-20
// Last Update : 2018-04-24
// -------------------------------------------------------------------
/* ****************************************************************************************************************** */
/*  @autor LI Japhet Mata Arellanes CodeJMA®  Class Protected by 9,565,768 US patent
/* ----   Copyright (C) 2002-2014 Japhet Mata - jmacorp.onion LTD ----------------------------------------------------- */
/* ----   External Clases Rights are property of their respective owners -------------------------------------------- */
/* ----   CodeJMA is an international brand of JMGlobal Corporation ------------------------------------------------- */
/* ---------------------------------    Notes for internal purpose only -------------------------------------------- */
/* ---------------------------------    --/02/2018 ------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------------------ */
/* ****************************************************************************************************************** */
/* NOTES:
- SE GENERALIZA LA CLASE PARA SE USADA EN EL GUARDADO DE PDFS EN EL ARCHIVO SEFIP SIN DISTINCIONES
- TODO PDF DEBERIA SER GUARDADO EN ESTE LUGAR
- SOLO EL APACHE GUARDA FICHEROS AQUÍ

GARANTIA: SI LO ROMPES PUEDES CONSERVAR LOS PEDAZOS
DESC:
SE INSTALA EN LOS SERVIDORES APACHE UNA CARPETA VIRTUAL DE ALMACENAMIENTO "EXTERNO"
ESTA CARPETA SOLO ES UNA REFERENCIA A UNA LOCACIÓN EN EL DATA CENTER QUE SE DESIGNO PARA LA CARGA DE FICHEROS QUE NO
SON NECESARIAMENTE CODIGO ( PDF,XLS,DOC,MP3, ETC)
ESTA CARPETA DEBE ALOJAR DE MANERA ESTRUCTURADA LOS FICHEROS QUE SE SUBEN A LOS DIFERENTES SISTEMAS
DE AHI MISMO SE DEBEN DE CONSULTAR
LA CARPETA SE ALOJA DIRECTAMENTE EN LA RAIZ DEL APACHE "/"
POR TANTO LA RUTA PUEDE SER ABSOLUTA  EJ: /ArchivosSefip/
DENTRO DE ELLA SE DEBE DE ACOMODAR SEGUN LA NATURALEZA
ESTA CLASE HACE EL TRABAJO RUDO DE LA ORGANIZACIÓN DE LOS DATOS

/ArchivosSefip/modulo/UR/rutadelprogramador
EN DONDE EL PROGAMADOR SOLO DEBE ENVIAR  "rutadel programador"
PORQUE? PORQUE LA CLASE HACE LA GESTION DE CREACION DE CARPETAS CONFORME SE USEN
EJEMPLO AL INDICAR RUTA:"DECRETOS" AÑO:2025 MODULO:POA LA CLASE REGRESA LA RUTA
/ArchivosSefip/poa2025/118/DECRETOS/
Y ES AHI DONDE GUARDA EL FICHERO

 */

class RutaArchivoSefip
{
    private $ini;
    private $error = false;
    private $base = "";
    private $path = "";
    private $urFichero = 0;
    private $anioFichero = 0;
    private $msg = "";
    protected $modulo;
    protected $ur;
    protected $anio;

    public function __construct($prm = null)
    {
        if (is_array($prm)) {
            $this->setPrm($prm);
        }
        $server = $_SERVER['SERVER_ADDR'];
        switch ($server) {
            case '172.17.90.19':
            case '172.17.90.18':
            case '172.17.90.87':
            case '172.17.90.88':
                $this->ini = '/ArchivosSefip/';
                break;
            case "::1":
            case "127.0.0.1":
            case "172.17.94.96":
                $this->ini = $_SERVER['DOCUMENT_ROOT'] . '/ArchivosSefip/';
                break;
            default:
                $this->ini = $_SERVER['DOCUMENT_ROOT'] . '/ArchivosSefip/';
                break;
        }
    }
    public function setBase($val)
    {
        $this->base = strtoupper($val);
    }
    public function getBase()
    {
        return $this->base;
    }
    public function getPath()
    {
        return $this->path;
    }
    public function setAnioFichero($val)
    {
        $this->anioFichero = $val;
    }
    public function getAnioFichero()
    {
        return $this->anioFichero;
    }
    public function setUrFichero($val)
    {
        $this->urFichero = $val;
    }
    public function getUrFichero()
    {
        return $this->urFichero;
    }
    public function getModulo()
    {
        return $this->modulo;
    }
    public function setMsg($val)
    {
        $this->msg = $val;
    }
    public function getMsg()
    {
        return $this->msg;
    }
    public function setError($val)
    {
        $this->error = $val;
    }
    public function getError()
    {
        return $this->error;
    }
    public function setPath($val)
    {
        if (substr($val, -1) != "/") {
            $this->path = $val . "/";
        } else {
            $this->path = $val;
        }
    }
    protected function setPrm($prm)
    {
        if (is_array($prm)) {

            $this->base = isset($prm["mod"]) ? $prm['mod'] : '';
            if (isset($prm["mod"]) && $prm["mod"] != "") {
                $this->setPath($prm['ruta']);
            }
            if (isset($prm["ruta"]) && $prm["ruta"] != "") {
                $this->setPath($prm['ruta']);
            }
            $this->ur = isset($prm["ur"]) ? (Integer) $prm['ur'] : '';
            $this->anio = isset($prm["anio"]) ? (Integer) $prm['anio'] : date('Y');
        }
    }
    public function setModulo($val)
    {
        $this->modulo = (String) $val;
        $this->setBase((String) $val);
    }

    protected function validaPrm()
    {
        if (preg_match('/[^a-z0-9 ]+\/+/i', $this->path)) {
            $this->error = true;
            $this->msg = "LA RUTA TIENE CARACTERES INVALIDOS";
            return false;
        }
        if ($this->base == "") {
            $this->error = true;
            $this->msg = "LA BASE NO PUEDE IR VACIA";
            return false;
        }

        if (($this->getUrFichero() == 0 || $this->getUrFichero() > 999)
            && $this->getModulo() !== "IP"
        ) {
            $this->error = true;
            $this->msg = "LA UR NO PUEDE ESTAR VACIA O SER 0 O MAYOR A 999 " . $this->getModulo();
            return false;
        }

        if ($this->getAnioFichero() == 0 || $this->getAnioFichero() > APP_EJERCICIO) {
            $this->error = true;
            $this->msg = "EL AÑO NO PUEDE SER 0 O MAYOR A " . APP_EJERCICIO;
            return false;
        }
        return true;
    }
    public function getRutaMod($prm = null)
    {
        if (is_array($prm)) {
            $this->setPrm($prm);
        }

        if (!$this->validaPrm()) {
            return (boolean) false;
        }
        $ruta = $this->ini;
        /* VERIFICAR POA */
        if ($this->getModulo() != "IP") {
            $ruta .= $this->base . $this->anioFichero . "/";
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
        } else {

            $ruta .= $this->getModulo() . "/" . $this->anioFichero . "/";
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
        }
        /* VERIFICAR CARPETA DE LA UR */
        if ($this->getModulo() != "IP") {
            $ruta .= $this->getUrFichero() . "/";
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
        } else {
            if (!file_exists($ruta) && $this->getModulo() != "IP") {
                mkdir($ruta, 0777, true);
            }
        }
        /* VERIFICAR CARPETA DE LA UR */
        $ruta .= $this->path != "" ? $this->path . (substr($this->path, -1) != "/" ? "/" : "") : '';
        if (!file_exists($ruta) && $this->getModulo() != "IP") {
            mkdir($ruta, 0777, true);
        }
        return $ruta;
    }
    public function exb_buildPath($banco, $msc_id, $anio)
    {
        $banco = preg_replace("/\.| |,/", "", $banco);
        $meses = array("NA", "ENERO", "FEBRERO", "MARZO",
            "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE",
            "OCTUBRE", "NOVIEMBRE", "DICIEMBRE");
        $mes = $meses[intval($msc_id)];
        $inicial = $this->ini;

        if (!file_exists($inicial)) {
            mkdir($inicial, 0777, true);
        }

        if (!file_exists($inicial . $anio . "/" . $banco)) {
            mkdir($inicial . $anio . "/" . $banco, 0777, true);
        }

        if (!file_exists($inicial . $anio . "/" . $banco)) {
            mkdir($inicial . $anio . "/" . $banco, 0777, true);
        }

        if (!file_exists($inicial . $anio . "/" . $banco . "/" . $mes)) {
            mkdir($inicial . $anio . "/" . $banco . "/" . $mes, 0777, true);
        }

        return $inicial . $anio . "/" . $banco . "/" . $mes . "/";
    }
}
