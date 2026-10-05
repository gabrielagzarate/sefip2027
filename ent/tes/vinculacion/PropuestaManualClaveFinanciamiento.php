<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class PropuestaManualClaveFinanciamiento extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lom_id; //
    protected $ecf_id; //sinpres.enc_ClaveFinanciamiento - Llave foranea - Identificador de la Clave de Financiamiento
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getLom_id()
    {return $this->lom_id->getVal();}
    public function getEcf_id()
    {return $this->ecf_id->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setLom_id($val)
    {$this->lom_id->setVal($val);}
    public function setEcf_id($val)
    {$this->ecf_id->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staLom_id($val)
    {$this->lom_id->staVal($val);}
    public function staEcf_id($val)
    {$this->ecf_id->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".vin_PropuestaManualClaveFinanciamiento"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lom_id = new Atributo("numero", "NO", "PRI", "lom_id");
        $this->ecf_id = new Atributo("numero", "NO", "MUL", "ecf_id");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $vPmcf = new PropuestaManualClaveFinanciamiento();

        $bit_observacion = '';

        if (isset($p->lom_id)) {
            $vPmcf->setLom_id($p->lom_id);
            $bit_observacion .= ' lom_id: ' . $p->lom_id;
        }
        if (isset($p->ecf_id)) {
            $vPmcf->setEcf_id($p->ecf_id);
            $bit_observacion .= ' ecf_id: ' . $p->ecf_id;
        }
        if (isset($p->usr_id)) {
            $vPmcf->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $vPmcf->conectar();
                $bit_observacion = ' REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $vPmcf->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $vPmcf->consultar($params);
                $vPmcf->insert(false);
                return $vPmcf->getUltimoId();
                break;
            case "b":
                $vPmcf->conectar();
                $bit_observacion = ' REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $vPmcf->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $vPmcf->consultar($params);
                $vPmcf->delete(false);
                break;
            case "c":
                $vPmcf->conectar();
                $bit_observacion = ' REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $vPmcf->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $vPmcf->consultar($params);
                $vPmcf->update(false);
                break;
        }
    }

}
