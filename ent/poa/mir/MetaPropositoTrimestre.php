<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class MetaPropositoTrimestre extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ***************************************************** */

    protected $pmi_id; //Programa Mir Indicador
    protected $mpr_id; //ID de la meta
    protected $tri_id; //Numero del trimestre
    protected $mprt_original; //
    protected $mprt_numerador; //
    protected $mprt_denominador; //
    protected $mprt_modificado; //
    protected $mprt_alcanzado; //
    protected $usr_id; //
    

    /******************************************************

    METODOS GET

     ***************************************************** */

    public function getPmi_id()
    {
        return $this->pmi_id->getVal();
    }

    public function getMpr_id()
    {
        return $this->mpr_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getOriginal()
    {
        return $this->mprt_original->getVal();
    }

    public function getNumerador()
    {
        return $this->mprt_numerador->getVal();
    }

    public function getDenominador()
    {
        return $this->mprt_denominador->getVal();
    }

    public function getModificado()
    {
        return $this->mprt_modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->mprt_alcanzado->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ***************************************************** */

    public function setPmi_id($val)
    {
        $this->pmi_id->setVal($val);
    }

    public function setMpr_id($val)
    {
        $this->mpr_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->mprt_original->setVal($val);
    }

    public function setNumerador($val)
    {
        $this->mprt_numerador->setVal($val);
    }

    public function setDenominador($val)
    {
        $this->mprt_denominador->setVal($val);
    }

    public function setModificado($val)
    {
        $this->mprt_modificado->setVal($val);
    }

    public function setAlcanzado($val)
    {
        $this->mprt_alcanzado->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ***************************************************** */

    public function staPmi_id($val)
    {
        $this->pmi_id->staVal($val);
    }

    public function staMpr_id($val)
    {
        $this->mpr_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->mprt_original->staVal($val);
    }

    public function staNumerador($val)
    {
        $this->mprt_numerador->staVal($val);
    }

    public function staDenominador($val)
    {
        $this->mprt_denominador->staVal($val);
    }

    public function staModificado($val)
    {
        $this->mprt_modificado->staVal($val);
    }

    public function staAlcanzado($val)
    {
        $this->mprt_alcanzado->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ***************************************************** */

    public function __construct()
    {
        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        parent::__construct(array("tabla" => "cat_MetaPropositoTrimestre"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->pmi_id = new Atributo("numero", "NO", "PRI", "pmi_id");
        $this->mpr_id = new Atributo("numero", "NO", "PRI", "mpr_id");
        $this->tri_id = new Atributo("numero", "NO", "PRI", "tri_id");
        $this->mprt_original = new Atributo("numero", "NO", "", "mprt_original");
        $this->mprt_numerador = new Atributo("numero", "NO", "", "mprt_numerador");
        $this->mprt_denominador = new Atributo("numero", "NO", "", "mprt_denominador");
        $this->mprt_modificado = new Atributo("numero", "NO", "", "mprt_modificado");
        $this->mprt_alcanzado = new Atributo("numero", "NO", "", "mprt_alcanzado");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        if (!empty($prm->pmi_id) && !empty($prm->mpr_id)) {

            $this->setPmi_id($prm->pmi_id);
            $this->setMpr_id($prm->mpr_id);
            $this->setTri_id('4');
            $this->select();
        }
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $mprt = new MetaPropositoTrimestre();
        if (isset($p->pmi_id)) {
            $mprt->setPmi_id($p->pmi_id);
        }

        if (isset($p->mpr_id)) {
            $mprt->setMpr_id($p->mpr_id);
        }

        $mprt->setTri_id(4);
        if (isset($p->ori_trimestre4)) {
            $mprt->setOriginal($p->ori_trimestre4);
        }

        if (isset($p->num_trimestre4)) {
            $mprt->setNumerador($p->num_trimestre4);
        }

        if (isset($p->den_trimestre4)) {
            $mprt->setDenominador($p->den_trimestre4);
        }

        if (isset($p->modificado)) {
            $mprt->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $mprt->setAlcanzado($p->alcanzado);
        }

        switch ($p->abc) {
            case "a":
                $mprt->setMpr_id(1);
                $mprt->insert();
                return $mprt->getUltimoId();
                break;
            case "b":
                $mprt->conectar();
                $mprt->delete();
                break;
            case "c":
                $mprt->update();
                break;
        }
    }

}
