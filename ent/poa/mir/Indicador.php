<?php

require_once dirname(__FILE__) . '/../../Entidad.php';

class Indicador extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $nim_id; //
    protected $ind_id; //Identificador numerico del indicador
    protected $nombre; //Nombre del indicador
    protected $definicion; //Definicio del indicador
    protected $formulaCalculo; //se almacena una supuesta formula de operacion entre numerador y denominador

    protected $objetivo; //
    protected $tpi_id; //Indica la tipologia del indicador.-Depende de cat_TipoIndicador
    protected $dim_id; //Indica el tipo de dimension del indicador depende de la tabla cat_Dimension

    protected $frm_id; //indica la frecuencia de medicion.- Depende de cat_FrecuenciaMedicion
    protected $ses_id; //Indica el "Sentido Esperado" para el indicador.- Depende de cat_SentidoEsperado
    protected $tiv_id; //Indica el tipo de valor para el indicador, depende de cat_TipoValor
    protected $umi_id; //Llave foránea a cat_UnidadMedidaIndicador.
    // Esta primer llave inidica el valor que se habia cargado
    // pero segun si esta mal usaran el otro campo
    protected $umv_id; //Llave foránea a cat_UnidadMedidaIndicador.
    // Esta segunda llave inidica el valor que se cargara porque el anterior umi_id esta mal en algunos casos, pero quieren conserver la antigua informacion.
    protected $nombreMIR; //
    protected $definicionMIR; //
    protected $limInfVer; //
    protected $limInfAmar; //
    protected $limSupAmar; //
    protected $limSupRojo; //
    protected $usr_id; //

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getNim_id()
    {
        return $this->nim_id->getVal();
    }
    public function getInd_id()
    {
        return $this->ind_id->getVal();
    }
    public function getNombre()
    {
        return $this->nombre->getVal();
    }
    public function getDefinicion()
    {
        return $this->definicion->getVal();
    }
    public function getFormulaCalculo()
    {
        return $this->formulaCalculo->getVal();
    }
    public function getObjetivo()
    {
        return $this->objetivo->getVal();
    }
    public function getTpi_id()
    {
        return $this->tpi_id->getVal();
    }
    public function getDim_id()
    {
        return $this->dim_id->getVal();
    }
    public function getFrm_id()
    {
        return $this->frm_id->getVal();
    }
    public function getSes_id()
    {
        return $this->ses_id->getVal();
    }
    public function getTiv_id()
    {
        return $this->tiv_id->getVal();
    }
    public function getUmi_id()
    {
        return $this->umi_id->getVal();
    }
    public function getumv_id()
    {
        return $this->umv_id->getVal();
    }
    public function getNombreMIR()
    {
        return $this->nombreMIR->getVal();
    }
    public function getDefinicionMIR()
    {
        return $this->definicionMIR->getVal();
    }
    public function getLimInfVer()
    {
        return $this->limInfVer->getVal();
    }
    public function getLimInfAmar()
    {
        return $this->limInfAmar->getVal();
    }
    public function getLimSupAmar()
    {
        return $this->limSupAmar->getVal();
    }
    public function getLimSupRojo()
    {
        return $this->limSupRojo->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setNim_id($val)
    {
        $this->nim_id->setVal($val);
    }
    public function setInd_id($val)
    {
        $this->ind_id->setVal($val);
    }
    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }
    public function setDefinicion($val)
    {
        $this->definicion->setVal($val);
    }
    public function setFormulaCalculo($val)
    {
        $this->formulaCalculo->setVal($val);
    }
    public function setObjetivo($val)
    {
        $this->objetivo->setVal($val);
    }
    public function setTpi_id($val)
    {
        $this->tpi_id->setVal($val);
    }
    public function setDim_id($val)
    {
        $this->dim_id->setVal($val);
    }
    public function setFrm_id($val)
    {
        $this->frm_id->setVal($val);
    }
    public function setSes_id($val)
    {
        $this->ses_id->setVal($val);
    }
    public function setTiv_id($val)
    {
        $this->tiv_id->setVal($val);
    }
    public function setUmi_id($val)
    {
        $this->umi_id->setVal($val);
    }
    public function setumv_id($val)
    {
        $this->umv_id->setVal($val);
    }
    public function setNombreMIR($val)
    {
        $this->nombreMIR->setVal($val);
    }
    public function setDefinicionMIR($val)
    {
        $this->definicionMIR->setVal($val);
    }
    public function setLimInfVer($val)
    {
        $this->limInfVer->setVal($val);
    }
    public function setLimInfAmar($val)
    {
        $this->limInfAmar->setVal($val);
    }
    public function setLimSupAmar($val)
    {
        $this->limSupAmar->setVal($val);
    }
    public function setLimSupRojo($val)
    {
        $this->limSupRojo->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staNim_id($val)
    {
        $this->nim_id->staVal($val);
    }
    public function staInd_id($val)
    {
        $this->ind_id->staVal($val);
    }
    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }
    public function staDefinicion($val)
    {
        $this->definicion->staVal($val);
    }
    public function staFormulaCalculo($val)
    {
        $this->formulaCalculo->staVal($val);
    }
    public function staObjetivo($val)
    {
        $this->objetivo->staVal($val);
    }
    public function staTpi_id($val)
    {
        $this->tpi_id->staVal($val);
    }
    public function staDim_id($val)
    {
        $this->dim_id->staVal($val);
    }
    public function staFrm_id($val)
    {
        $this->frm_id->staVal($val);
    }
    public function staSes_id($val)
    {
        $this->ses_id->staVal($val);
    }
    public function staTiv_id($val)
    {
        $this->tiv_id->staVal($val);
    }
    public function staUmi_id($val)
    {
        $this->umi_id->staVal($val);
    }
    public function staumv_id($val)
    {
        $this->umv_id->staVal($val);
    }
    public function staNombreMIR($val)
    {
        $this->nombreMIR->staVal($val);
    }
    public function staDefinicionMIR($val)
    {
        $this->definicionMIR->staVal($val);
    }
    public function staLimInfVer($val)
    {
        $this->limInfVer->staVal($val);
    }
    public function staLimInfAmar($val)
    {
        $this->limInfAmar->staVal($val);
    }
    public function staLimSupAmar($val)
    {
        $this->limSupAmar->staVal($val);
    }
    public function staLimSupRojo($val)
    {
        $this->limSupRojo->staVal($val);
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

        parent::__construct(['tabla' => 'cat_Indicador']);
        $ind = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass();

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->nim_id = new Atributo('numero', 'NO', 'PRI', 'nim_id');
        $this->ind_id = new Atributo('numero', 'NO', 'PRI', 'ind_id');
        $this->nombre = new Atributo('cadena', 'NO', '', 'ind_nombre');
        $this->definicion = new Atributo('cadena', 'NO', '', 'ind_definicion');
        $this->formulaCalculo = new Atributo('cadena', 'NO', '', 'ind_formulaCalculo');
        $this->objetivo = new Atributo('cadena', 'NO', '', 'ind_objetivo');
        $this->tpi_id = new Atributo('numero', 'NO', 'MUL', 'tpi_id');
        $this->dim_id = new Atributo('numero', 'NO', 'MUL', 'dim_id');
        $this->frm_id = new Atributo('numero', 'NO', 'MUL', 'frm_id');
        $this->ses_id = new Atributo('numero', 'NO', 'MUL', 'ses_id');
        $this->tiv_id = new Atributo('numero', 'NO', 'MUL', 'tiv_id');
        $this->umi_id = new Atributo('numero', 'NO', 'MUL', 'umi_id');
        $this->umv_id = new Atributo('numero', 'NO', 'MUL', 'umv_id');
        $this->nombreMIR = new Atributo('cadena', 'NO', '', 'ind_nombreMIR');
        $this->definicionMIR = new Atributo('cadena', 'NO', '', 'ind_definicionMIR');
        $this->limInfVer = new Atributo('numero', 'NO', '', 'ind_limInfVer');
        $this->limInfAmar = new Atributo('numero', 'NO', '', 'ind_limInfAmar');
        $this->limSupAmar = new Atributo('numero', 'NO', '', 'ind_limSupAmar');
        $this->limSupRojo = new Atributo('numero', 'NO', '', 'ind_limSupRojo');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        if (!empty($ind->nim_id) && !empty($ind->ind_id)) {
            $this->setNim_id($ind->nim_id);
            $this->setInd_id($ind->ind_id);
            $this->select();
        }
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $ind = new Indicador();
        if (isset($p->nim_id)) {
            $ind->setNim_id($p->nim_id);
        }

        if (isset($p->ind_id)) {
            $ind->setInd_id($p->ind_id);
        }

        if (isset($p->nombre)) {
            $ind->setNombre($p->nombre);
        }

        if (isset($p->definicion)) {
            $ind->setDefinicion($p->definicion);
        }

        if (isset($p->formulaCalculo)) {
            $ind->setFormulaCalculo($p->formulaCalculo);
        }

        if (isset($p->objetivo)) {
            $ind->setObjetivo($p->objetivo);
        }

        if (isset($p->tpi_id)) {
            $ind->setTpi_id($p->tpi_id);
        }

        if (isset($p->dim_id)) {
            $ind->setDim_id($p->dim_id);
        }

        if (isset($p->frm_id)) {
            $ind->setFrm_id($p->frm_id);
        }

        if (isset($p->ses_id)) {
            $ind->setSes_id($p->ses_id);
        }

        if (isset($p->tiv_id)) {
            $ind->setTiv_id($p->tiv_id);
        }

        if (isset($p->umi_id)) {
            $ind->setUmi_id($p->umi_id);
        }

        if (isset($p->umv_id)) {
            $ind->setumv_id($p->umv_id);
        }

        if (isset($p->nombreMIR)) {
            $ind->setNombreMIR($p->nombreMIR);
        }

        if (isset($p->definicionMIR)) {
            $ind->setDefinicionMIR($p->definicionMIR);
        }

        if (isset($p->limInfVer)) {
            $ind->setLimInfVer($p->limInfVer);
        }

        if (isset($p->limInfAmar)) {
            $ind->setLimInfAmar($p->limInfAmar);
        }

        if (isset($p->limSupAmar)) {
            $ind->setLimSupAmar($p->limSupAmar);
        }

        if (isset($p->limSupRojo)) {
            $ind->setLimSupRojo($p->limSupRojo);
        }

        if (isset($p->usr_id)) {
            $ind->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $ind->insert();
                return $ind->getUltimoId();
                break;
            case 'b':
                $ind->delete();
                break;
            case 'c':
                $ind->update();
                break;
        }
    }
}
