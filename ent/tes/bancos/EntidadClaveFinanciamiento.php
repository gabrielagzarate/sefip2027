<?php

require_once dirname(__FILE__)."/../../Entidad.php";

class SefinClaveFinanciamiento extends Entidad
{
    /****************************************************** 

        ATRIBUTOS DE LA CLASE

    ******************************************************/ 
    protected $cbs_id;	//ban_CuentaBancariaSefin - Llave foranea - Identificador de la Cuenta Bancaria de SeFin
    protected $ecf_id;	//sinpres.enc_ClaveFinanciamiento - Llave foranea - Identificador de la Clave de Financiamiento
    protected $usr_id;	//Identificador de usuario que captura/actualiza el registro
    /****************************************************** 

        METODOS GET

    ******************************************************/ 

    public function getCbs_id() { return $this->cbs_id->getVal(); }
    public function getEcf_id() { return $this->ecf_id->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }
    /****************************************************** 

        METODOS SET

    ******************************************************/ 
    public function setCbs_id($val) { $this->cbs_id->setVal($val); }
    public function setEcf_id($val) { $this->ecf_id->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }
    /****************************************************** 

        METODOS STA (SET sin persistencia)

    ******************************************************/ 
    public function staCbs_id($val) { $this->cbs_id->staVal($val); }
    public function staEcf_id($val) { $this->ecf_id->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }
    /****************************************************** 

        CONSTRUCTOR

    ******************************************************/ 

    public function  __construct()
    {
        parent::__construct(array("tabla" => BD_TESORERIA . ".vin_SefinClaveFinanciamiento"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->cbs_id = new Atributo("numero","NO","PRI","cbs_id");
        $this->ecf_id = new Atributo("numero","NO","PRI","ecf_id");
        $this->usr_id = new Atributo("numero","NO","MUL","usr_id");
    }

    /****************************************************** 

        FUNCIONES ESTÁTICAS

    ******************************************************/ 

    public static function abc($p)
    {
        $cf =  new SefinClaveFinanciamiento();
        $bit_observacion = '';
        if (isset($p->cbs_id)) {
            $cf->setCbs_id($p->cbs_id);
        }
        if (isset($p->ecf_id)) {
            $cf->setEcf_id($p->ecf_id);
            $bit_observacion = "ECF: ".$p->ecf_id;		
        } 
        if (isset($p->usr_id)) {
            $cf->setUsr_id($p->usr_id);
        }

        $bit_usr_id =  $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip']:'127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a": 
                $cf->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cf->setSql(' SET @usr_ip =INET_ATON(?), @usr_id = ? , @bit_observacion = ?;');
                $cf->consultar($params);
                $cf->insert(false);  
                return $cf->getUltimoId(); 
                break;
            case "b":
                $cf->conectar();
                $bit_observacion = 'REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cf->setSql(' SET @usr_ip =INET_ATON(?), @usr_id = ? , @bit_observacion = ?;');
                $cf->consultar($params);
                $cf->delete(); 
                break;
            case "c": 
                $cf->update(); 
                break;
        }
    }
}
