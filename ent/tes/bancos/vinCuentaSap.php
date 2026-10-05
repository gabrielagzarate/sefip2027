<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class CuentaSap extends Entidad
{
    /****************************************************** 

        ATRIBUTOS DE LA CLASE

    ******************************************************/ 
    protected $cbs_id;	//ban_CuentaBancariaSefin - Llave foranea - Identificador de la Cuenta Bancaria de SeFin
    protected $cbs_cuentaBanco;	//Cuenta que proporciona la Institución Bancaria
    protected $ger_id;	//cat_GeneroConac - Llave foranea - Identificador del Genero CONAC SAP
    protected $cgp_id;	//cat_GrupoConac - Llave foranea - Identificador del Grupo CONAC SAP
    protected $rub_id;	//cat_RubroConac - Llave foranea - Identificador del Rubro CONAC SAP
    protected $cgcu_id;	//cat_CuentaConac - Llave foranea - Identificador de la Cuenta CONAC SAP
    protected $cgsc_id;	//cat_SubcuentaConac - Llave foranea - Identificador de la Subcuenta CONAC SAP
    protected $cgc_id;	//cgt_CuentaCuarto - Llave foranea - Identificador de la Cuenta Maestro SAP(subnivel-ibs_id)
    protected $cta_id;	//Identificador del Nivel de Registro de la Cuenta Contable SAP
    protected $ibs_id;	//cat_InstitucionBancariaSap - Llave foranea - Identificador del Tipo de Cuenta SAP
    protected $tcs_id;	//cat_TipoCuentaSap - Llave foranea - Identificador del Tipo de Cuenta SAP
    protected $cbs_consecutivo;	//Número consecutivo de cuenta SAP
    protected $usr_id;	//Identificador de usuario que captura/actualiza el registro

    /****************************************************** 

        METODOS GET

    ******************************************************/ 
    public function getCbs_id() { return $this->cbs_id->getVal(); }
    public function getCuentaBanco() { return $this->cbs_cuentaBanco->getVal(); }
    public function getGer_id() { return $this->ger_id->getVal(); }
    public function getCgp_id() { return $this->cgp_id->getVal(); }
    public function getRub_id() { return $this->rub_id->getVal(); }
    public function getCgcu_id() { return $this->cgcu_id->getVal(); }
    public function getCgsc_id() { return $this->cgsc_id->getVal(); }
    public function getCgc_id() { return $this->cgc_id->getVal(); }
    public function getCta_id() { return $this->cta_id->getVal(); }
    public function getIbs_id() { return $this->ibs_id->getVal(); }
    public function getTcs_id() { return $this->tcs_id->getVal(); }
    public function getConsecutivo() { return $this->cbs_consecutivo->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /****************************************************** 

        METODOS SET

    ******************************************************/ 
    public function setCbs_id($val) { $this->cbs_id->setVal($val); }
    public function setCuentaBanco($val) { $this->cbs_cuentaBanco->setVal($val); }
    public function setGer_id($val) { $this->ger_id->setVal($val); }
    public function setCgp_id($val) { $this->cgp_id->setVal($val); }
    public function setRub_id($val) { $this->rub_id->setVal($val); }
    public function setCgcu_id($val) { $this->cgcu_id->setVal($val); }
    public function setCgsc_id($val) { $this->cgsc_id->setVal($val); }
    public function setCgc_id($val) { $this->cgc_id->setVal($val); }
    public function setCta_id($val) { $this->cta_id->setVal($val); }
    public function setIbs_id($val) { $this->ibs_id->setVal($val); }
    public function setTcs_id($val) { $this->tcs_id->setVal($val); }
    public function setConsecutivo($val) { $this->cbs_consecutivo->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /****************************************************** 

        METODOS STA (SET sin persistencia)

    ******************************************************/ 
    public function staCbs_id($val)
    { 
        $this->cbs_id->staVal($val); 
    }
    public function staCuentaBanco($val)
    { 
        $this->cbs_cuentaBanco->staVal($val); 
    }
    public function staGer_id($val)
    { 
        $this->ger_id->staVal($val); 
    }
    public function staCgp_id($val)
    { 
        $this->cgp_id->staVal($val); 
    }
    public function staRub_id($val)
    { 
        $this->rub_id->staVal($val); 
    }
    public function staCgcu_id($val)
    { 
        $this->cgcu_id->staVal($val); 
    }
    public function staCgsc_id($val)
    { 
        $this->cgsc_id->staVal($val); 
    }
    public function staCgc_id($val)
    { 
        $this->cgc_id->staVal($val); 
    }
    public function staCta_id($val)
    { 
        $this->cta_id->staVal($val); 
    }
    public function staIbs_id($val)
    { 
        $this->ibs_id->staVal($val); 
    }
    public function staTcs_id($val)
    { 
        $this->tcs_id->staVal($val); 
    }
    public function staConsecutivo($val)
    { 
        $this->cbs_consecutivo->staVal($val); 
    }
    public function staUsr_id($val)
    { 
        $this->usr_id->staVal($val); 
    }

    /****************************************************** 

        CONSTRUCTOR

    ******************************************************/ 

    public function  __construct()
    {
        parent::__construct(array("tabla" => BD_TESORERIA . ".vin_CuentaSap"));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cbs_id = new Atributo("numero","NO","PRI","cbs_id");
        $this->cbs_cuentaBanco = new Atributo("numero","NO","","cbs_cuentaBanco");
        $this->ger_id = new Atributo("numero","NO","MUL","ger_id");
        $this->cgp_id = new Atributo("numero","NO","","cgp_id");
        $this->rub_id = new Atributo("numero","NO","","rub_id");
        $this->cgcu_id = new Atributo("numero","NO","","cgcu_id");
        $this->cgsc_id = new Atributo("numero","NO","","cgsc_id");
        $this->cgc_id = new Atributo("numero","NO","","cgc_id");
        $this->cta_id = new Atributo("numero","NO","","cta_id");
        $this->ibs_id = new Atributo("numero","NO","MUL","ibs_id");
        $this->tcs_id = new Atributo("numero","NO","PRI","tcs_id");
        $this->cbs_consecutivo = new Atributo("numero","NO","","cbs_consecutivo");
        $this->usr_id = new Atributo("numero","NO","MUL","usr_id");
    }

    /****************************************************** 

        FUNCIONES ESTÁTICAS

    ******************************************************/ 

    public static function abc($p)
    {
        $cbs =  new CuentaSap();
        $bit_observacion = '';

        if (isset($p->cbs_id)) {
            $cbs->setCbs_id($p->cbs_id);
            $bit_observacion .= ' cbs_id: ' . $p->cbs_id;
        }
        if (isset($p->cbs_cuentaBanco)) {
            $cbs->setCuentaBanco($p->cbs_cuentaBanco);
            $bit_observacion .= ' cbs_cuentaBanco: ' . $p->cbs_cuentaBanco;
        }
        if (isset($p->ger_id)) {
            $cbs->setGer_id($p->ger_id);
            $bit_observacion .= ' ger_id: ' . $p->ger_id;
        }
        if (isset($p->cgp_id)) {
            $cbs->setCgp_id($p->cgp_id);
            $bit_observacion .= ' cgp_id: ' . $p->cgp_id;
        }
        if (isset($p->rub_id)) {
            $cbs->setRub_id($p->rub_id);
            $bit_observacion .= ' rub_id: ' . $p->rub_id;
        }
        if (isset($p->cgcu_id)) {
            $cbs->setCgcu_id($p->cgcu_id);
            $bit_observacion .= ' cgcu_id: ' . $p->cgcu_id;
        }
        if (isset($p->cgsc_id)) {
            $cbs->setCgsc_id($p->cgsc_id);
            $bit_observacion .= ' cgsc_id: ' . $p->cgsc_id;
        }
        if (isset($p->cgc_id)) {
            $cbs->setCgc_id($p->cgc_id);
            $bit_observacion .= ' cgc_id: ' . $p->cgc_id;
        }
        if (isset($p->cta_id)) {
            $cbs->setCta_id($p->cta_id);
            $bit_observacion .= ' cta_id: ' . $p->cta_id;
        }
        if (isset($p->ibs_id)) {
            $cbs->setIbs_id($p->ibs_id);
            $bit_observacion .= ' ibs_id: ' . $p->ibs_id;
        }
        if (isset($p->tcs_id)) {
            $cbs->setTcs_id($p->tcs_id);
            $bit_observacion .= ' tcs_id: ' . $p->tcs_id;
        }
        if (isset($p->consecutivo)) {
            $cbs->setConsecutivo($p->consecutivo);
            $bit_observacion .= ' consecutivo: ' . $p->consecutivo;
        }
        if (isset($p->usr_id)) {
            $cbs->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }
        $bit_usr_id =  $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip']:'127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a": 
                $cbs->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cbs->setSql(' SET @usr_ip = INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
                $cbs->consultar($params);
                $cbs->insert(false); 
                return $cbs->getUltimoId();
                break;
            case "b": 
                $cbs->delete();
                break;
            case "c": 
                $cbs->update();
                $cbs->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cbs->setSql(' SET @usr_ip = INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
                $cbs->consultar($params);
                $cbs->update(false);
            break;
        }
    }
}
