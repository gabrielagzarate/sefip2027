<?php
require_once dirname(__FILE__) . "/../Entidad.php";

class OrigenOperacion extends Entidad
{
    /*     * ****************************************************

    ATRIBUTOS DE LA CLASE

     * **************************************************** */
    protected $enc_oop; //Identificador numerico del encuadre de Origen de Operacion
    protected $omo_id; //Identificador del Origen de la modificacion. Foranea dependiente de cat_OrigenModificacion
    protected $tmd_id; //Identificador del tipo  de Modificacion, Foranea dependiente de cat_TipoModificacion
    protected $mmd_id; //Identificador de la modalidad de Modificacion. Foranea dependiente de cat_ModalidadModificacion
    protected $tfm_id; //Identificador del tipo de folio de modificacion. Foranea dependiente de cat_TipoFolioModificacion

    /*     * ****************************************************

    METODOS GET

     * **************************************************** */

    public function getOop()
    {
        return $this->enc_oop->getVal();
    }

    public function getOmo_id()
    {
        return $this->omo_id->getVal();
    }

    public function getTmd_id()
    {
        return $this->tmd_id->getVal();
    }

    public function getMmd_id()
    {
        return $this->mmd_id->getVal();
    }

    public function getTfm_id()
    {
        return $this->tfm_id->getVal();
    }
    /*     * ****************************************************

    METODOS SET

     * **************************************************** */

    public function setOop($val)
    {
        $this->enc_oop->setVal($val);
    }

    public function setOmo_id($val)
    {
        $this->omo_id->setVal($val);
    }

    public function setTmd_id($val)
    {
        $this->tmd_id->setVal($val);
    }

    public function setMmd_id($val)
    {
        $this->mmd_id->setVal($val);
    }

    public function setTfm_id($val)
    {
        $this->tfm_id->setVal($val);
    }
    /*     * ****************************************************

    METODOS STA (SET sin persistencia)

     * **************************************************** */

    public function staOop($val)
    {
        $this->enc_oop->staVal($val);
    }

    public function staOmo_id($val)
    {
        $this->omo_id->staVal($val);
    }

    public function staTmd_id($val)
    {
        $this->tmd_id->staVal($val);
    }

    public function staMmd_id($val)
    {
        $this->mmd_id->staVal($val);
    }

    public function staTfm_id($val)
    {
        $this->tfm_id->staVal($val);
    }
    /*     * ****************************************************

    CONSTRUCTOR

     * **************************************************** */

    public function __construct()
    {

        parent::__construct(array("tabla" => "enc_OrigenOperacion"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->enc_oop = new Atributo("numero", "NO", "PRI", "enc_oop");
        $this->omo_id = new Atributo("cadena", "NO", "MUL", "omo_id");
        $this->tmd_id = new Atributo("cadena", "NO", "MUL", "tmd_id");
        $this->mmd_id = new Atributo("cadena", "NO", "MUL", "mmd_id");
        $this->tfm_id = new Atributo("cadena", "NO", "MUL", "tfm_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) :
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $enc_oop = (!isset($prm->enc_oop)) ? '' : $prm->enc_oop;

        if ($enc_oop != '') {
            $this->setOop($enc_oop);
            $this->select();
        }
    }
    /*     * ****************************************************

    FUNCIONES EST�TICAS

     * **************************************************** */

    public static function abc($p)
    {
        $enc = new OrigenOperacion();
        if (isset($p->oop)) {
            $enc->setOop($p->oop);
        }
        if (isset($p->omo_id)) {
            $enc->setOmo_id($p->omo_id);
        }
        if (isset($p->tmd_id)) {
            $enc->setTmd_id($p->tmd_id);
        }
        if (isset($p->mmd_id)) {
            $enc->setMmd_id($p->mmd_id);
        }
        if (isset($p->tfm_id)) {
            $enc->setTfm_id($p->tfm_id);
        }
        switch ($p->abc) {
            case "a":
                $enc->insert();
                return $enc->getUltimoId();
                break;
            case "b":
                $enc->delete();
                break;
            case "c":
                $enc->update();
                break;
        }
    }
}
