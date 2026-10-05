<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class EntidadPropuestaDocumento extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $pro_id; //Identificador de Lote
    protected $gpo_id; //cat_Grupo - Llave foranea - Identificador de Grupo de la Clasificación Administrativa
    protected $ur_id; //cat_UnidadResponsable - Llave foranea - Identificador de la Unidad Responsable de la Clasificación Administrativa
    protected $clc_id; //scp_Clc - Llave foranea - Identificador de la CLC
    protected $bdo_id; //cat_BancoDocumentos - Llave foranea - Referencia del documento de comprobación
    protected $pro_importeBdo; //Importe del Documento de Comprobación
    protected $rfc_id; //cat_Proveedor - Llave foranea - Identificador del Proveedor
    protected $bur_id; //cat_BancosUr - Llave foranea - Identificador de la Cuenta Bancaria donde se depositara el importe del documento
    protected $usr_id; //Identificador de usuario que captura el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getPro_id()
    {
        return $this->pro_id->getVal();
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
    public function getImporteBdo()
    {
        return $this->pro_importeBdo->getVal();
    }
    public function getRfc_id()
    {
        return $this->rfc_id->getVal();
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

    public function setPro_id($val)
    {
        $this->pro_id->setVal($val);
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
    public function setImporteBdo($val)
    {
        $this->pro_importeBdo->setVal($val);
    }
    public function setRfc_id($val)
    {
        $this->rfc_id->setVal($val);
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

    public function staPro_id($val)
    {
        $this->pro_id->staVal($val);
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
    public function staImporteBdo($val)
    {
        $this->pro_importeBdo->staVal($val);
    }
    public function staRfc_id($val)
    {
        $this->rfc_id->staVal($val);
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

        parent::__construct(array("tabla" => BD_TESORERIA.".pro_PropuestaDocumento"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->pro_id = new Atributo("numero", "NO", "PRI", "pro_id");
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->bdo_id = new Atributo("numero", "NO", "PRI", "bdo_id");
        $this->pro_importeBdo = new Atributo("numero", "NO", "", "pro_importeBdo");
        $this->rfc_id = new Atributo("cadena", "NO", "MUL", "rfc_id");
        $this->bur_id = new Atributo("numero", "NO", "MUL", "bur_id");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $pro = new EntidadPropuestaDocumento();
        $bit_observacion = '';

        if (isset($p->pro_id)) {
            $pro->setPro_id($p->pro_id);
            $bit_observacion .= 'pro_id: ' . $p->pro_id;
        }
        if (isset($p->gpo_id)) {
            $pro->setGpo_id($p->gpo_id);
            $bit_observacion .= ' gpo_id: ' . $p->gpo_id;
        }
        if (isset($p->ur_id)) {
            $pro->setUr_id($p->ur_id);
            $bit_observacion .= ' ur_id: ' . $p->ur_id;
        }
        if (isset($p->clc_id)) {
            $pro->setClc_id($p->clc_id);
            $bit_observacion .= ' clc_id: ' . $p->clc_id;
        }
        if (isset($p->bdo_id)) {
            $pro->setBdo_id($p->bdo_id);
            $bit_observacion .= ' bdo_id: ' . $p->bdo_id;
        }
        if (isset($p->importeBdo)) {
            $pro->setImporteBdo($p->importeBdo);
            $bit_observacion .= ' importeBdo: ' . $p->importeBdo;
        }
        if (isset($p->rfc_id)) {
            $pro->setRfc_id($p->rfc_id);
            $bit_observacion .= ' rfc_id: ' . $p->rfc_id;
        }
        if (isset($p->bur_id)) {
            $pro->setBur_id($p->bur_id);
            $bit_observacion .= ' bur_id: ' . $p->bur_id;
        }
        if (isset($p->usr_id)) {
            $pro->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $pro->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $pro->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $pro->consultar($params);
                $pro->insert(false);
                return $pro->getUltimoId();
                break;
            case "b":
                $pro->conectar();
                $bit_observacion = 'REGISTRO ELIMINADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $pro->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $pro->consultar($params);
                $pro->delete(false);
                break;
            case "c":
                $pro->conectar();
                $bit_observacion = 'REGISTRO MODIFICADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $pro->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $pro->consultar($params);
                $pro->update(false);
                break;
        }
    }
}
