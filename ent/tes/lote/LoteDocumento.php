<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LoteDocumento extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $fpa_id; //cat_FormaPago - Llave foranea - Identificador de la Forma de Pago
    protected $msc_id; //cat_Mes - Llave foranea - Identificador del Mes
    protected $lot_ejercicio; //lot_Lote - Llave Foranea - Identificador del ejercicio del Documento
    protected $lot_id; //lot_Lote - Llave foranea - Identificador del Lote
    protected $gpo_id; //cat_Grupo - Llave foranea - Identificador de Grupo de la Clasificación Administrativa
    protected $ur_id; //cat_UnidadResponsable - Llave foranea - Identificador de la Unidad Responsable de la Clasificación Administrativa
    protected $clc_id; //scp_Clc - Llave foranea - Identificador de la CLC
    protected $bdo_id; //cat_BancoDocumentos - Llave foranea - Referencia del documento de comprobación
    protected $rfc_id; //cat_Proveedor - Llave foranea - Identificador del Proveedor
    protected $lot_importeBdo; //Importe del Documento
    protected $bur_id; //cat_BancosUr - Llave foranea - Identificador de la Cuenta Bancaria donde se depositara el importe de la CLC
    protected $usr_id; //Identificador de usuario que captura el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getFpa_id()
    {
        return $this->fpa_id->getVal();
    }
    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->lot_ejercicio->getVal();
    }
    public function getLot_id()
    {
        return $this->lot_id->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getClc_id()
    {
        return $this->clc_id->getVal();
    }
    public function getBdo_id()
    {
        return $this->bdo_id->getVal();
    }
    public function getRfc_id()
    {
        return $this->rfc_id->getVal();
    }
    public function getImporteBdo()
    {
        return $this->lot_importeBdo->getVal();
    }
    public function getBur_id()
    {
        return $this->bur_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setFpa_id($val)
    {
        $this->fpa_id->setVal($val);
    }
    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->lot_ejercicio->setVal($val);
    }
    public function setLot_id($val)
    {
        $this->lot_id->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setClc_id($val)
    {
        $this->clc_id->setVal($val);
    }
    public function setBdo_id($val)
    {
        $this->bdo_id->setVal($val);
    }
    public function setRfc_id($val)
    {
        $this->rfc_id->setVal($val);
    }
    public function setImporteBdo($val)
    {
        $this->lot_importeBdo->setVal($val);
    }
    public function setBur_id($val)
    {
        $this->bur_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staFpa_id($val)
    {
        $this->fpa_id->staVal($val);
    }
    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->lot_ejercicio->staVal($val);
    }
    public function staLot_id($val)
    {
        $this->lot_id->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staClc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function staBdo_id($val)
    {
        $this->bdo_id->staVal($val);
    }
    public function staRfc_id($val)
    {
        $this->rfc_id->staVal($val);
    }
    public function staImporteBdo($val)
    {
        $this->lot_importeBdo->staVal($val);
    }
    public function staBur_id($val)
    {
        $this->bur_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".lot_LoteDocumento"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->fpa_id = new Atributo("numero", "NO", "PRI", "fpa_id");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lot_ejercicio = new Atributo("numero", "NO", "PRI", "lot_ejercicio");
        $this->lot_id = new Atributo("numero", "NO", "PRI", "lot_id");
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->bdo_id = new Atributo("numero", "NO", "PRI", "bdo_id");
        $this->rfc_id = new Atributo("cadena", "NO", "MUL", "rfc_id");
        $this->lot_importeBdo = new Atributo("numero", "NO", "", "lot_importeBdo");
        $this->bur_id = new Atributo("numero", "NO", "MUL", "bur_id");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $bit_observacion = "";
        $lot = new LoteDocumento();
        if (isset($p->fpa_id)) {
            $lot->setFpa_id($p->fpa_id);
            $bit_observacion .= 'fpa_id: ' . $p->fpa_id;
        }
        if (isset($p->msc_id)) {
            $lot->setMsc_id($p->msc_id);
            $bit_observacion .= ' msc_id: ' . $p->msc_id;
        }
        if (isset($p->ejercicio)) {
            $lot->setEjercicio($p->ejercicio);
            $bit_observacion .= ' ejercicio: ' . $p->ejercicio;
        }
        if (isset($p->lot_id)) {
            $lot->setLot_id($p->lot_id);
            $bit_observacion .= ' lot_id: ' . $p->lot_id;
        }
        if (isset($p->gpo_id)) {
            $lot->setGpo_id($p->gpo_id);
            $bit_observacion .= ' gpo_id: ' . $p->gpo_id;
        }
        if (isset($p->ur_id)) {
            $lot->setUr_id($p->ur_id);
            $bit_observacion .= ' ur_id: ' . $p->ur_id;
        }
        if (isset($p->clc_id)) {
            $lot->setClc_id($p->clc_id);
            $bit_observacion .= ' clc_id: ' . $p->clc_id;
        }
        if (isset($p->bdo_id)) {
            $lot->setBdo_id($p->bdo_id);
            $bit_observacion .= ' bdo_id: ' . $p->bdo_id;
        }
        if (isset($p->rfc_id)) {
            $lot->setRfc_id($p->rfc_id);
            $bit_observacion .= ' rfc_id: ' . $p->rfc_id;
        }
        if (isset($p->importeBdo)) {
            $lot->setImporteBdo($p->importeBdo);
            $bit_observacion .= ' importeBdo: ' . $p->importeBdo;
        }
        if (isset($p->bur_id)) {
            $lot->setBur_id($p->bur_id);
            $bit_observacion .= ' bur_id: ' . $p->bur_id;
        }
        if (isset($p->usr_id)) {
            $lot->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {

            case "a":
                $lot->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->insert(false);
                return $lot->getUltimoId();
                break;
            case "b":
                $lot->conectar();
                $bit_observacion = 'REGISTRO ELIMINADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->delete(false);
                break;
            case "c":
                $lot->conectar();
                $bit_observacion = 'REGISTRO MODIFICADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->update(false);
                break;
        }
    }
}
