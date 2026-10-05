<?php
require_once dirname(__FILE__)."/Entidad.php";

class PartidaEspecifica extends Entidad
{
    /*     * ****************************************************
      ATRIBUTOS DE LA CLASE
     * **************************************************** */
    protected $pg_id; //cat_PartidaGenerica - Llave Foranea Clave identificadora Partida Generica
    protected $pe_id; //
    protected $idp_id; //cat_IdentificadorPartida - Llave Foranea
    protected $nombre; //
    protected $descripcion; //
    protected $comprasDirectas; //
    protected $clave; //
    protected $reductible; //
    protected $cedula; //Habilitada para costeo
    protected $clc; //Habilitada para CLC
    protected $tg_id; //cat_ObjetoGasto - Llave Foranea
    protected $og_id; //cat_ObjetoGasto - Llave Foranea
    protected $tno_id; //campo del ejercicio 2013 tentativo a eliminar
    protected $usr_id; //Clave identificadora del usuario que inserto el registro

    /*     * ****************************************************
      METODOS GET
     * **************************************************** */

    public function getPg_id()
    {
        return $this->pg_id->getVal();
    }

    public function getPe_id()
    {
        return $this->pe_id->getVal();
    }

    public function getIdp_id()
    {
        return $this->idp_id->getVal();
    }

    public function getNombre()
    {
        return $this->nombre->getVal();
    }

    public function getDescripcion()
    {
        return $this->descripcion->getVal();
    }

    public function getComprasDirectas()
    {
        return $this->comprasDirectas->getVal();
    }

    public function getComprasConsolidadas()
    {
        return 1 - $this->comprasDirectas->getVal();
    }

    public function getClave()
    {
        return $this->clave->getVal();
    }

    public function getReductible()
    {
        return $this->reductible->getVal();
    }

    public function getCedula()
    {
        return $this->cedula->getVal();
    }

    public function getClc()
    {
        return $this->clc->getVal();
    }

    public function getTg_id()
    {
        return $this->tg_id->getVal();
    }

    public function getOg_id()
    {
        return $this->og_id->getVal();
    }

    public function getTno_id()
    {
        return $this->tno_id->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /*     * ****************************************************
      METODOS SET
     * **************************************************** */

    public function setPg_id($val)
    {
        $this->pg_id->setVal($val);
    }

    public function setPe_id($val)
    {
        $this->pe_id->setVal($val);
    }

    public function setIdp_id($val)
    {
        $this->idp_id->setVal($val);
    }

    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }

    public function setDescripcion($val)
    {
        $this->descripcion->setVal($val);
    }

    public function setComprasDirectas($val)
    {
        $this->comprasDirectas->setVal($val);
    }

    public function setClave($val)
    {
        $this->clave->setVal($val);
    }

    public function setReductible($val)
    {
        $this->reductible->setVal($val);
    }

    public function setCedula($val)
    {
        $this->cedula->setVal($val);
    }

    public function setClc($val)
    {
        $this->clc->setVal($val);
    }

    public function setTg_id($val)
    {
        $this->tg_id->setVal($val);
    }

    public function setOg_id($val)
    {
        $this->og_id->setVal($val);
    }

    public function setTno_id($val)
    {
        $this->tno_id->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /*     * ****************************************************
      METODOS STA (SET sin persistencia)
     * **************************************************** */

    public function staPg_id($val)
    {
        $this->pg_id->staVal($val);
    }

    public function staPe_id($val)
    {
        $this->pe_id->staVal($val);
    }

    public function staIdp_id($val)
    {
        $this->idp_id->staVal($val);
    }

    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }

    public function staDescripcion($val)
    {
        $this->descripcion->staVal($val);
    }

    public function staComprasDirectas($val)
    {
        $this->comprasDirectas->staVal($val);
    }

    public function staClave($val)
    {
        $this->clave->staVal($val);
    }

    public function staReductible($val)
    {
        $this->reductible->staVal($val);
    }

    public function staCedula($val)
    {
        $this->cedula->staVal($val);
    }

    public function staClc($val)
    {
        $this->clc->staVal($val);
    }

    public function staTg_id($val)
    {
        $this->tg_id->staVal($val);
    }

    public function staOg_id($val)
    {
        $this->og_id->staVal($val);
    }

    public function staTno_id($val)
    {
        $this->tno_id->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    /*     * ****************************************************
      CONSTRUCTOR
     * **************************************************** */

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_PartidaEspecifica"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->pg_id = new Atributo("numero", "NO", "PRI", "pg_id");
        $this->pe_id = new Atributo("numero", "NO", "PRI", "pe_id");
        $this->idp_id = new Atributo("cadena", "NO", "MUL", "idp_id");
        $this->nombre = new Atributo("cadena", "NO", "", "pe_nombre");
        $this->descripcion = new Atributo("cadena", "NO", "", "pe_descripcion");
        $this->comprasDirectas = new Atributo("numero", "NO", "",
            "pe_comprasDirectas");
        $this->clave = new Atributo("numero", "NO", "UNI", "pe_clave");
        $this->reductible = new Atributo("numero", "NO", "", "pe_reductible");
        $this->cedula = new Atributo("numero", "NO", "", "pe_cedula");
        $this->clc = new Atributo("numero", "NO", "", "pe_clc");
        $this->tg_id = new Atributo("numero", "NO", "MUL", "tg_id");
        $this->og_id = new Atributo("cadena", "NO", "", "og_id");
        $this->tno_id = new Atributo("cadena", "NO", "", "tno_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $parametros = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : 
        (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        $pg_id = (!isset($parametros->pg_id) ) ? '' : $parametros->pg_id;
        $pe_id = (!isset($parametros->pe_id) ) ? '' : $parametros->pe_id;

        $this->staPg_id($pg_id);
        $this->staPe_id($pe_id);
        if ($this->getPe_id() != '' && $this->getPg_id() != '') $this->select();
    }
}
?>