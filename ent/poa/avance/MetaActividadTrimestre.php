<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class MetaActividadTrimestre extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $pii_id; //
    protected $epg_id; //
    protected $tri_id; //
    protected $original; //
    protected $modificado; //
    protected $alcanzado; //
    protected $capturaAvance; //Indica si hay captura en avance de gestión 0 - No Capturado, 1 - Capturado
    protected $usr_id; //

    /******************************************************

    METODOS GET

    ******************************************************/

    public function getPii_id()
    {
        return $this->pii_id->getVal();
    }

    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getOriginal()
    {
        return $this->original->getVal();
    }

    public function getModificado()
    {
        return $this->modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->alcanzado->getVal();
    }

    public function getCapturaAvance()
    {
        return $this->capturaAvance->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************

    METODOS SET

    ******************************************************/

    public function setPii_id($val)
    {
        $this->pii_id->setVal($val);
    }

    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->original->setVal($val);
    }

    public function setModificado($val)
    {
        $this->modificado->setVal($val);
    }

    public function setAlcanzado($val)
    {
        $this->alcanzado->setVal($val);
    }

    public function setCapturaAvance($val)
    {
        $this->capturaAvance->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************

    METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }

    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->original->staVal($val);
    }

    public function staModificado($val)
    {
        $this->modificado->staVal($val);
    }

    public function staAlcanzado($val)
    {
        $this->alcanzado->staVal($val);
    }

    public function staCapturaAvance($val)
    {
        $this->capturaAvance->staVal($val);
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
        $mac = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        parent::__construct(array("tabla" => "cat_MetaActividadTrimestre"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->pii_id = new Atributo("numero", "NO", "PRI", "pii_id");
        $this->epg_id = new Atributo("numero", "NO", "PRI", "epg_id");
        $this->tri_id = new Atributo("numero", "NO", "PRI", "tri_id");
        $this->original = new Atributo("numero", "NO", "", "mact_original");
        $this->modificado = new Atributo("numero", "NO", "", "mact_modificado");
        $this->alcanzado = new Atributo("numero", "NO", "", "mact_alcanzado");
        $this->capturaAvance = new Atributo("numero", "NO", "", "mact_capturaAvance");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        if (!empty($mac->pii_id) && !empty($mac->epg_id)) {
            $this->setPii_id($mac->pii_id);
            $this->setEpg_id($mac->epg_id);
            $this->setTri_id(1);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $mact = new MetaActividadTrimestre();
        if (isset($p->pii_id)) {
            $mact->setPii_id($p->pii_id);
        }

        if (isset($p->epg_id)) {
            $mact->setEpg_id($p->epg_id);
        }

        if (isset($p->tri_id)) {
            $mact->setTri_id($p->tri_id);
        }

        if (isset($p->original)) {
            $mact->setOriginal($p->original);
        }

        if (isset($p->modificado)) {
            $mact->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $mact->setAlcanzado($p->alcanzado);
        }

        if (isset($p->capturaAvance)) {
            $mact->setCapturaAvance($p->capturaAvance);
        }

        if (isset($p->usr_id)) {
            $mact->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $mact->insert();
                return $mact->getUltimoId();
                break;
            case "b":
                $mact->delete();
                break;
            case "c":
                $mact->update();
                break;
        }
    }
}
