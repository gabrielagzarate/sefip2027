<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class MetaActividadTrimestreMir extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $gpo_id; //
    protected $ur_id; //
    protected $pii_id; //Identificador del Producto Intermedio Indicador
    protected $macm_id; //
    protected $tri_id; //
    protected $macmt_original; //
    protected $macmt_modificado; //
    protected $macmt_alcanzado; //
    protected $macmt_numerador;
    protected $macmt_denominador;
    protected $usr_id; //Usuario que inserto el registro

    /******************************************************

    METODOS GET

     * **************************************************** */

    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }

    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }

    public function getPii_id()
    {
        return $this->pii_id->getVal();
    }

    public function getMacm_id()
    {
        return $this->macm_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getOriginal()
    {
        return $this->macmt_original->getVal();
    }

    public function getModificado()
    {
        return $this->macmt_modificado->getVal();
    }

    public function getAlcanzado()
    {
        return $this->macmt_alcanzado->getVal();
    }

    public function getNumerador()
    {
        return $this->macmt_numerador->getVal();
    }

    public function getDenominador()
    {
        return $this->macmt_denominador->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************

    METODOS SET

     ***************************************************** */

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }

    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }

    public function setPii_id($val)
    {
        $this->pii_id->setVal($val);
    }

    public function setMacm_id($val)
    {
        $this->macm_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setOriginal($val)
    {
        $this->macmt_original->setVal($val);
    }

    public function setModificado($val)
    {
        $this->macmt_modificado->setVal($val);
    }

    public function setAlcanzado($val)
    {
        $this->macmt_alcanzado->setVal($val);
    }

    public function setNumerador($val)
    {
        $this->macmt_numerador->setVal($val);
    }

    public function setDenominador($val)
    {
        $this->macmt_denominador->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************

    METODOS STA (SET sin persistencia)

     ***************************************************** */

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }

    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }

    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }

    public function staMacm_id($val)
    {
        $this->macm_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staOriginal($val)
    {
        $this->macmt_original->staVal($val);
    }

    public function staModificado($val)
    {
        $this->macmt_modificado->staVal($val);
    }

    public function staAlcanzado($val)
    {
        $this->macmt_alcanzado->staVal($val);
    }

    public function staNumerador($val)
    {
        $this->macmt_numerador->staVal($val);
    }

    public function staDenominador($val)
    {
        $this->macmt_denominador->staVal($val);
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
        $macmt = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        parent::__construct(array("tabla" => "cat_MetaActividadTrimestreMir"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->pii_id = new Atributo("numero", "NO", "PRI", "pii_id");
        $this->macm_id = new Atributo("numero", "NO", "PRI", "macm_id");
        $this->tri_id = new Atributo("numero", "NO", "PRI", "tri_id");
        $this->macmt_original = new Atributo("numero", "NO", "", "macmt_original");
        $this->macmt_modificado = new Atributo("numero", "NO", "", "macmt_modificado");
        $this->macmt_alcanzado = new Atributo("numero", "NO", "", "macmt_alcanzado");
        $this->macmt_numerador = new Atributo("numero", "NO", "", "macmt_numerador");
        $this->macmt_denominador = new Atributo("numero", "NO", "", "macmt_denominador");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        if (!empty($macmt->eri_id) && !empty($macmt->macm_id)) {
            $this->setGpo_id($macmt->gpo_id);
            $this->setUr_id($macmt->ur_id);
            $this->setPii_id($macmt->eri_id);
            $this->setMacm_id($macmt->macm_id);
            $this->setTri_id(1);
            $this->select();
        }
    }
    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $macmt = new MetaActividadTrimestreMir();
        if (isset($p->gpo_id)) {
            $macmt->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $macmt->setUr_id($p->ur_id);
        }

        if (isset($p->pii_id)) {
            $macmt->setPii_id($p->pii_id);
        }

        if (isset($p->macm_id)) {
            $macmt->setMacm_id($p->macm_id);
        }

        if (isset($p->tri_id)) {
            $macmt->setTri_id($p->tri_id);
        }

        if (isset($p->original)) {
            $macmt->setOriginal($p->original);
        }

        if (isset($p->modificado)) {
            $macmt->setModificado($p->modificado);
        }

        if (isset($p->alcanzado)) {
            $macmt->setAlcanzado($p->alcanzado);
        }

        if (isset($p->numerador)) {
            $macmt->setNumerador($p->numerador);
        }

        if (isset($p->denominador)) {
            $macmt->setDenominador($p->denominador);
        }

        if (isset($p->usr_id)) {
            $macmt->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        // ASIGNAR VARIBLES
        $macmt->conectar();
        $macmt->setSql('SET @pass_procedure = "casc9n3cd930dc0", @bit_observacion="Registro desde entidad MetaActividadMIR",@usr_id = ' . $_SESSION[_S]['usr_id'] . ',@usr_ip = INET_ATON( "' . $ip . '" );');
        $macmt->consultar();

        switch ($p->abc) {
            case "a":$macmt->insert(false);
                return $macmt->getUltimoId();
                break;
            case "b":$macmt->delete();
                break;
            case "c":$macmt->update(false);
                break;
        }
    }
}
