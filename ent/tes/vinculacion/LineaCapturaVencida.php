<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LineaCapturaVencida extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lca_id_siox; //cat_FormaPago - Llave foranea - Identificador de la Forma de Pago
    protected $lca_id_pago; //cat_Mes - Llave foranea - Identificador del Mes

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getlca_id_siox()
    {
        return $this->lca_id_siox->getVal();
    }
    public function getlca_id_pago()
    {
        return $this->lca_id_pago->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setlca_id_siox($val)
    {
        $this->lca_id_siox->setVal($val);
    }
    public function setlca_id_pago($val)
    {
        $this->lca_id_pago->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function stalca_id_siox($val)
    {
        $this->lca_id_siox->staVal($val);
    }
    public function stalca_id_pago($val)
    {
        $this->lca_id_pago->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".vin_LineaCaptura"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lca_id_siox = new Atributo("numero", "NO", "", "lca_id_siox");
        $this->lca_id_pago = new Atributo("numero", "NO", "", "lca_id_pago");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lot = new LineaCapturaVencida();
        $bit_observacion = "";

        if (isset($p->lca_id_siox)) {
            $lot->setlca_id_siox($p->lca_id_siox);
            $bit_observacion .= "lca_id_siox:" . $p->lca_id_siox;
        }

        if (isset($p->lca_id_pago)) {
            $lot->setlca_id_pago($p->lca_id_pago);
            $bit_observacion .= "lca_id_pago:" . $p->lca_id_pago;
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
                $bit_observacion = 'REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->delete(false);
                break;
            case "c":
                $lot->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->update(false);
                break;
        }
    }
}
