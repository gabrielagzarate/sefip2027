<?php
require_once dirname(__FILE__) . "/../Entidad.php";

class PresupuestoComprometido extends Entidad
{
    /*     * ****************************************************

      ATRIBUTOS DE LA CLASE

     * **************************************************** */
    protected $gpo_id; //Identificador del Grupo de la Unidad Responsable. Depende de cat_UnidadEjecutora
    protected $ur_id; //Identificador de la Unidad Responsable. Depende de cat_UnidadEjecutora
    protected $ue_id; //Identificador de la Unidad Ejecutora. Depende de cat_UnidadEjecutora
    protected $epg_id; //Identificador del encuadre progamatico. Depende de enc_Programatico
    protected $pg_id; //Identificador de la Partida Genérica. Depende de cat_PartidaEspecifica
    protected $pe_id; //Identificador de la Partida Específica. Depende de cat_PartidaEspecifica
    protected $ecf_id; //Identificador del encuadre de Clave de Fianciamiento. Depende de enc_ClaveFinaciamiento.
    protected $tcp_id; //Identificador del Tipo de Compra. Depende de cat_TipoCompra.
    protected $comprometido; //Monto presupuestal comprometido Compromisos autorizados
    protected $cmp; //
    protected $mesComprometido;

    /*     * ****************************************************

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

    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }

    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }

    public function getPg_id()
    {
        return $this->pg_id->getVal();
    }

    public function getPe_id()
    {
        return $this->pe_id->getVal();
    }

    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }

    public function getTcp_id()
    {
        return $this->tcp_id->getVal();
    }

    public function getComprometido()
    {
        return $this->comprometido->getVal();
    }

    public function getMesComprometido()
    {
        return $this->mesComprometido;
    }

    public function getCmp()
    {
        return $this->cmp->getVal();
    }
    /*     * ****************************************************

      METODOS SET

     * **************************************************** */

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }

    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }

    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }

    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }

    public function setPg_id($val)
    {
        $this->pg_id->setVal($val);
    }

    public function setPe_id($val)
    {
        $this->pe_id->setVal($val);
    }

    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }

    public function setTcp_id($val)
    {
        $this->tcp_id->setVal($val);
    }

    public function setComprometido($val)
    {
        $this->comprometido->setVal($val);
    }

    public function setCmp($val)
    {
        $this->cmp->setVal($val);
    }
    /*     * ****************************************************

      METODOS STA (SET sin persistencia)

     * **************************************************** */

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }

    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }

    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }

    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }

    public function staPg_id($val)
    {
        $this->pg_id->staVal($val);
    }

    public function staPe_id($val)
    {
        $this->pe_id->staVal($val);
    }

    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }

    public function staTcp_id($val)
    {
        $this->tcp_id->staVal($val);
    }

    public function staComprometido($val)
    {
        $this->comprometido->staVal($val);
    }

    public function staCmp($val)
    {
        $this->cmp->staVal($val);
    }
    /*     * ****************************************************

      CONSTRUCTOR

     * **************************************************** */

    public function __construct()
    {

        parent::__construct(array("tabla" => "presupuestoComprometido"));

        $prm = ( func_num_args() > 0 ) ? ((( is_object(func_get_arg(0)) ) ? func_get_arg(0)
                :
            ( is_array(func_get_arg(0))  )) ? (object) func_get_arg(0) : null ) : null;

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->ue_id = new Atributo("numero", "NO", "PRI", "ue_id");
        $this->epg_id = new Atributo("numero", "NO", "PRI", "epg_id");
        $this->pg_id = new Atributo("numero", "NO", "PRI", "pg_id");
        $this->pe_id = new Atributo("numero", "NO", "PRI", "pe_id");
        $this->ecf_id = new Atributo("numero", "NO", "PRI", "ecf_id");
        $this->tcp_id = new Atributo("cadena", "NO", "PRI", "tcp_id");
        $this->comprometido = new Atributo("moneda", "NO", "",
            "pre_comprometido");
        $this->cmp = new Atributo("numero", "YES", "", "pre_cmp");
        $this->mesComprometido = new Meses(array("prefix" => "com_", "tipo" => "moneda"));

        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $ue_id = (!isset($prm->ue_id)) ? '' : $prm->ue_id;
        $epg_id = (!isset($prm->epg_id)) ? '' : $prm->epg_id;
        $pg_id = (!isset($prm->pg_id)) ? '' : $prm->pg_id;
        $pe_id = (!isset($prm->pe_id)) ? '' : $prm->pe_id;
        $ecf_id = (!isset($prm->ecf_id)) ? '' : $prm->ecf_id;
        $tcp_id = (!isset($prm->tcp_id)) ? '' : $prm->tcp_id;

        if ($gpo_id != '' && $ur_id != '' && $ue_id != '' && $epg_id != '' && $pg_id
            != '' && $pe_id != '' && $ecf_id != '') {

            $this->setGpo_id($gpo_id);
            $this->setUr_id($ur_id);
            $this->setUe_id($ue_id);
            $this->setEpg_id($epg_id);
            $this->setPg_id($pg_id);
            $this->setPe_id($pe_id);
            $this->setEcf_id($ecf_id);

            $this->setTcp_id($tcp_id);
            $this->select();
        }
    }
    /*     * ****************************************************

      FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $pre = new PresupuestoComprometido();
        if (isset($p->gpo_id)) $pre->setGpo_id($p->gpo_id);
        if (isset($p->ur_id)) $pre->setUr_id($p->ur_id);
        if (isset($p->ue_id)) $pre->setUe_id($p->ue_id);
        if (isset($p->epg_id)) $pre->setEpg_id($p->epg_id);
        if (isset($p->pg_id)) $pre->setPg_id($p->pg_id);
        if (isset($p->pe_id)) $pre->setPe_id($p->pe_id);
        if (isset($p->ecf_id)) $pre->setEcf_id($p->ecf_id);
        if (isset($p->tcp_id)) $pre->setTcp_id($p->tcp_id);
        if (isset($p->comprometido)) $pre->setComprometido($p->comprometido);
        if (isset($p->cmp)) $pre->setCmp($p->cmp);
        switch ($p->abc) {
            case "a":
                $pre->insert();
                return $pre->getUltimoId();
                break;
            case "b":
                $pre->delete();
                break;
            case "c":
                $pre->update();
                break;
        }
    }
}
