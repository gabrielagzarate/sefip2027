<?php
require_once dirname(__FILE__)."/../../Entidad.php";

class BeneficiarioMunicipio extends Entidad
{
    /****************************************************** 
        ATRIBUTOS DE LA CLASE
    ******************************************************/ 

    protected $mun_id;	//
    protected $tfn_id;	//
    protected $cbb_id;	//ban_CuentaBancariaBeneficiario - Llave foranea - Identificador de la Cuenta Bancaria del Beneficiario
    protected $usr_id;	//Identificador de usuario que captura/actualiza el registro

    /****************************************************** 
        METODOS GET
    ******************************************************/ 

    public function getMun_id() { return $this->mun_id->getVal(); }
    public function getTfn_id() { return $this->tfn_id->getVal(); }
    public function getCbb_id() { return $this->cbb_id->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /****************************************************** 
        METODOS SET
    ******************************************************/ 

    public function setMun_id($val) { $this->mun_id->setVal($val); }
    public function setTfn_id($val) { $this->tfn_id->setVal($val); }
    public function setCbb_id($val) { $this->cbb_id->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /****************************************************** 
        METODOS STA (SET sin persistencia
    ******************************************************/ 

    public function staMun_id($val) { $this->mun_id->staVal($val); }
    public function staTfn_id($val) { $this->tfn_id->staVal($val); }
    public function staCbb_id($val) { $this->cbb_id->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }

    /****************************************************** 
        CONSTRUCTOR
    ******************************************************/ 

    public function  __construct()
    {
        parent::__construct(array("tabla"=>BD_TESORERIA.".vin_BeneficiarioMunicipio"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->mun_id = new Atributo("numero","NO","PRI","mun_id");
        $this->tfn_id = new Atributo("numero","NO","PRI","tfn_id");
        $this->cbb_id = new Atributo("numero","NO","PRI","cbb_id");
        $this->usr_id = new Atributo("numero","NO","MUL","usr_id");
    }

    /****************************************************** 
        FUNCIONES ESTÁTICAS
    ******************************************************/ 

    public static function abc($p)
    {
        $vbm =  new BeneficiarioMunicipio();
        $bit_observacion = '';

        if (isset($p->mun_id)) {
            $vbm->setMun_id($p->mun_id);
            $bit_observacion .= ' mun_id: ' . $p->mun_id;
        }
        if (isset($p->tfn_id)) {
            $vbm->setTfn_id($p->tfn_id);
            $bit_observacion .= ' tfn_id: ' . $p->tfn_id;
        }
        if (isset($p->cbb_id)) {
            $vbm->setCbb_id($p->cbb_id);
            $bit_observacion .= ' cbb_id: ' . $p->cbb_id;
        }
        if (isset($p->usr_id)) {
            $vbm->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip']:'127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];

        switch ($p->abc) {
            case "a":
                $vbm->conectar();
                $bit_observacion = 'REGISTRO INSERTADO '.$bit_observacion;
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $vbm->setSql(' SET @usr_ip =INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
                $vbm->consultar($params);
                $vbm->insert(false);
                return $vbm->getUltimoId();
                break;
            case "b":
                $vbm->conectar();
                $bit_observacion = 'REGISTRO BORRADO '.$bit_observacion;
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $vbm->setSql(' SET @usr_ip =INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
                $vbm->consultar($params);
                $vbm->delete(false);
                break;
            case "c":
                $vbm->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO '.$bit_observacion;
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $vbm->setSql(' SET @usr_ip =INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
                $vbm->consultar($params);
                $vbm->update(false);
                break;
        }
    }
}