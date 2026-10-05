<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class MetaComponenteTrimestre extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $gpo_id; //
    protected $ur_id; //
    protected $prgs_id; //
    protected $sprs_id; //
    protected $epfi_id; //
    protected $tri_id; //
    protected $mcot_original; //
    protected $mcot_numerador; //
    protected $mcot_denominador; //
    protected $mcot_modificado; //
    protected $mcot_alcanzado; //

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getPrgs_id()
    {
        return $this->prgs_id->getVal();
    }
    public function getSprs_id()
    {
        return $this->sprs_id->getVal();
    }
    public function getEpfi_id()
    {
        return $this->epfi_id->getVal();
    }
    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }
    public function getOriginal()
    {
        return $this->mcot_original->getVal();
    }
    public function getNumerador()
    {
        return $this->mcot_numerador->getVal();
    }
    public function getDenominador()
    {
        return $this->mcot_denominador->getVal();
    }
    public function getModificado()
    {
        return $this->mcot_modificado->getVal();
    }
    public function getAlcanzado()
    {
        return $this->mcot_alcanzado->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setPrgs_id($val)
    {
        $this->prgs_id->setVal($val);
    }
    public function setSprs_id($val)
    {
        $this->sprs_id->setVal($val);
    }
    public function setEpfi_id($val)
    {
        $this->epfi_id->setVal($val);
    }
    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }
    public function setOriginal($val)
    {
        $this->mcot_original->setVal($val);
    }
    public function setNumerador($val)
    {
        $this->mcot_numerador->setVal($val);
    }
    public function setDenominador($val)
    {
        $this->mcot_denominador->setVal($val);
    }
    public function setModificado($val)
    {
        $this->mcot_modificado->setVal($val);
    }
    public function setAlcanzado($val)
    {
        $this->mcot_alcanzado->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staPrgs_id($val)
    {
        $this->prgs_id->staVal($val);
    }
    public function staSprs_id($val)
    {
        $this->sprs_id->staVal($val);
    }
    public function staEpfi_id($val)
    {
        $this->epfi_id->staVal($val);
    }
    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }
    public function staOriginal($val)
    {
        $this->mcot_original->staVal($val);
    }
    public function staNumerador($val)
    {
        $this->mcot_numerador->staVal($val);
    }
    public function staDenominador($val)
    {
        $this->mcot_denominador->staVal($val);
    }
    public function staModificado($val)
    {
        $this->mcot_modificado->staVal($val);
    }
    public function staAlcanzado($val)
    {
        $this->mcot_alcanzado->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => "cat_MetaComponenteTrimestre"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->prgs_id = new Atributo("numero", "NO", "PRI", "prgs_id");
        $this->sprs_id = new Atributo("numero", "NO", "PRI", "sprs_id");
        $this->epfi_id = new Atributo("numero", "NO", "PRI", "epfi_id");
        $this->tri_id = new Atributo("numero", "NO", "PRI", "tri_id");
        $this->mcot_original = new Atributo("numero", "NO", "", "mcot_original");
        $this->mcot_numerador = new Atributo("numero", "NO", "", "mcot_numerador");
        $this->mcot_denominador = new Atributo("numero", "NO", "", "mcot_denominador");
        $this->mcot_modificado = new Atributo("numero", "NO", "", "mcot_modificado");
        $this->mcot_alcanzado = new Atributo("numero", "NO", "", "mcot_alcanzado");
    }

    /******************************************************

    FUNCIONES EST�TICAS

     ******************************************************/

    public static function abc($p)
    {
        $mcot = new MetaComponenteTrimestre();
        if (isset($p->gpo_id)) {
            $mcot->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $mcot->setUr_id($p->ur_id);
        }

        if (isset($p->prgs_id)) {
            $mcot->setPrgs_id($p->prgs_id);
        }

        if (isset($p->sprs_id)) {
            $mcot->setSprs_id($p->sprs_id);
        }

        if (isset($p->epfi_id)) {
            $mcot->setEpfi_id($p->epfi_id);
        }

        if (isset($p->tri_id)) {
            $mcot->setTri_id($p->tri_id);
        }

        if (isset($p->original)) {
            $mcot->setOriginal($p->original);
        }

        if (isset($p->numerador)) {
            $mcot->setNumerador($p->numerador);
        }

        if (isset($p->denominador)) {
            $mcot->setDenominador($p->denominador);
        }

        if (isset($p->modificado)) {
            $mcot->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $mcot->setAlcanzado($p->alcanzado);
        }

        switch ($p->abc) {
            case "a":
                $mcot->insert();
                return $mcot->getUltimoId();
                break;
            case "b":
                $mcot->delete();
                break;
            case "c":
                $mcot->update();
                break;
        }
    }
}
