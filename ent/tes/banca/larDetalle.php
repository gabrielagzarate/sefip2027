<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class LayoutArchivoDetalle extends Entidad
{
    /*     * ****************************************************

    ATRIBUTOS DE LA CLASE

     * **************************************************** */

    protected $lay_id; //
    protected $lar_ejercicio; //
    protected $msc_id; //
    protected $lar_dia; //
    protected $usr_id; //
    protected $lad; //
    protected $cre_id; //
    protected $cbs_id; //
    protected $lad_importe; //
    protected $cbb_id; //
    protected $lad_referenciaAPagar; //
    protected $lad_cadena; //

    /*     * ****************************************************

    METODOS GET

     * **************************************************** */

    public function getLay_id()
    {
        return $this->lay_id->getVal();
    }

    public function getEjercicio()
    {
        return $this->lar_ejercicio->getVal();
    }

    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }

    public function getDia()
    {
        return $this->lar_dia->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    public function getLad()
    {
        return $this->lad->getVal();
    }

    public function getCre_id()
    {
        return $this->cre_id->getVal();
    }

    public function getCbs_id()
    {
        return $this->cbs_id->getVal();
    }

    public function getImporte()
    {
        return $this->lad_importe->getVal();
    }

    public function getCbb_id()
    {
        return $this->cbb_id->getVal();
    }

    public function getReferenciaAPagar()
    {
        return $this->lad_referenciaAPagar->getVal();
    }

    public function getCadena()
    {
        return $this->lad_cadena->getVal();
    }
    /*     * ****************************************************

    METODOS SET

     * **************************************************** */

    public function setLay_id($val)
    {
        $this->lay_id->setVal($val);
    }

    public function setEjercicio($val)
    {
        $this->lar_ejercicio->setVal($val);
    }

    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }

    public function setDia($val)
    {
        $this->lar_dia->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    public function setLad($val)
    {
        $this->lad->setVal($val);
    }

    public function setCre_id($val)
    {
        $this->cre_id->setVal($val);
    }

    public function setCbs_id($val)
    {
        $this->cbs_id->setVal($val);
    }

    public function setImporte($val)
    {
        $this->lad_importe->setVal($val);
    }

    public function setCbb_id($val)
    {
        $this->cbb_id->setVal($val);
    }

    public function setReferenciaAPagar($val)
    {
        $this->lad_referenciaAPagar->setVal($val);
    }

    public function setCadena($val)
    {
        $this->lad_cadena->setVal($val);
    }
    /*     * ****************************************************

    METODOS STA (SET sin persistencia)

     * **************************************************** */

    public function staLay_id($val)
    {
        $this->lay_id->staVal($val);
    }

    public function staEjercicio($val)
    {
        $this->lar_ejercicio->staVal($val);
    }

    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }

    public function staDia($val)
    {
        $this->lar_dia->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    public function staLad($val)
    {
        $this->lad->staVal($val);
    }

    public function staCre_id($val)
    {
        $this->cre_id->staVal($val);
    }

    public function staCbs_id($val)
    {
        $this->cbs_id->staVal($val);
    }

    public function staImporte($val)
    {
        $this->lad_importe->staVal($val);
    }

    public function staCbb_id($val)
    {
        $this->cbb_id->staVal($val);
    }

    public function staReferenciaAPagar($val)
    {
        $this->lad_referenciaAPagar->staVal($val);
    }

    public function staCadena($val)
    {
        $this->lad_cadena->staVal($val);
    }
    /*     * ****************************************************

    CONSTRUCTOR

     * **************************************************** */

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".lot_LayoutArchivoDetalle"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->lay_id = new Atributo("numero", "NO", "PRI", "lay_id");
        $this->lar_ejercicio = new Atributo("numero", "NO", "PRI", "lar_ejercicio");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lar_dia = new Atributo("numero", "NO", "PRI", "lar_dia");
        $this->usr_id = new Atributo("numero", "NO", "PRI", "usr_id");
        $this->lad = new Atributo("numero", "NO", "PRI", "lad");
        $this->cre_id = new Atributo("numero", "NO", "MUL", "cre_id");
        $this->cbs_id = new Atributo("numero", "NO", "MUL", "cbs_id");
        $this->lad_importe = new Atributo("numero", "NO", "", "lad_importe");
        $this->cbb_id = new Atributo("numero", "NO", "MUL", "cbb_id");
        $this->lad_referenciaAPagar = new Atributo("numero", "NO", "", "lad_referenciaAPagar");
        $this->lad_cadena = new Atributo("cadena", "NO", "", "lad_cadena");
    }
    /*     * ****************************************************

    FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $lar = new LayoutArchivoDetalle();
        if (isset($p->lay_id)) {
            $lar->setLay_id($p->lay_id);
        }

        if (isset($p->ejercicio)) {
            $lar->setEjercicio($p->ejercicio);
        }

        if (isset($p->msc_id)) {
            $lar->setMsc_id($p->msc_id);
        }

        if (isset($p->dia)) {
            $lar->setDia($p->dia);
        }

        if (isset($p->usr_id)) {
            $lar->setUsr_id($p->usr_id);
        }

        if (isset($p->lad)) {
            $lar->setLad($p->lad);
        }

        if (isset($p->cre_id)) {
            $lar->setCre_id($p->cre_id);
        }

        if (isset($p->cbs_id)) {
            $lar->setCbs_id($p->cbs_id);
        }

        if (isset($p->importe)) {
            $lar->setImporte($p->importe);
        }

        if (isset($p->cbb_id)) {
            $lar->setCbb_id($p->cbb_id);
        }

        if (isset($p->referenciaAPagar)) {
            $lar->setReferenciaAPagar($p->referenciaAPagar);
        }

        if (isset($p->cadena)) {
            $lar->setCadena($p->cadena);
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lar->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ';
                array_push($params, $bit_observacion);
                $lar->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lar->consultar($params);
                $lar->insert(false);
                return $lar->getUltimoId();
                break;
            case "b":$lar->delete();
                break;
            case "c":$lar->update();
                break;
        }
    }
}
