<?php

require_once dirname(__FILE__) . '/../Entidad.php';

require_once dirname(__FILE__) . '/../../lib/constantes.php';

class PresupuestoVigente extends Entidad
{
    /*         * ****************************************************

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
    protected $pre_ampliacion; //Ampliaciones al presupuesto
    protected $pre_reduccion; //Reducciones al presupuesto
    protected $pre_vigente; //Monto presupuestal vigente
    protected $pre_omo; //
    protected $pre_mod; //
    protected $pre_mov; //Determina el tipo de modificacion que se esta realizando a la clave
    protected $pre_exenta; //Campo para identificar las claves presupuestales que se pueden sobregirar 1 - Si 2 - No
    protected $pre_planeacion; //Detarmina si la clave presupuestal de prevision puede ser usada por planeacion, en el módulo de modificaciones presupuestales. 1.- Indicará que si puede ser visualizada por Planeación. 2.- No la puede visualizar.
    protected $mesVigente;

    /*         * ****************************************************

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

    public function getAmpliacion()
    {
        return $this->pre_ampliacion->getVal();
    }

    public function getReduccion()
    {
        return $this->pre_reduccion->getVal();
    }

    public function getVigente()
    {
        return $this->pre_vigente->getVal();
    }

    public function getMesVigente()
    {
        return $this->mesVigente;
    }

    public function getOmo()
    {
        return $this->pre_omo->getVal();
    }

    public function getMod()
    {
        return $this->pre_mod->getVal();
    }

    public function getMov()
    {
        return $this->pre_mov->getVal();
    }

    public function getExenta()
    {
        return $this->pre_exenta->getVal();
    }

    public function getPlaneacion()
    {
        return $this->pre_planeacion->getVal();
    }
    /*         * ****************************************************

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

    public function setAmpliacion($val)
    {
        $this->pre_ampliacion->setVal($val);
    }

    public function setReduccion($val)
    {
        $this->pre_reduccion->setVal($val);
    }

    public function setVigente($val)
    {
        $this->pre_vigente->setVal($val);
    }

    public function setOmo($val)
    {
        $this->pre_omo->setVal($val);
    }

    public function setMod($val)
    {
        $this->pre_mod->setVal($val);
    }

    public function setMov($val)
    {
        $this->pre_mov->setVal($val);
    }

    public function setExenta($val)
    {
        $this->pre_exenta->setVal($val);
    }

    public function setPlaneacion($val)
    {
        $this->pre_planeacion->setVal($val);
    }
    /*         * ****************************************************

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

    public function staAmpliacion($val)
    {
        $this->pre_ampliacion->staVal($val);
    }

    public function staReduccion($val)
    {
        $this->pre_reduccion->staVal($val);
    }

    public function staVigente($val)
    {
        $this->pre_vigente->staVal($val);
    }

    public function staOmo($val)
    {
        $this->pre_omo->staVal($val);
    }

    public function staMod($val)
    {
        $this->pre_mod->staVal($val);
    }

    public function staMov($val)
    {
        $this->pre_mov->staVal($val);
    }

    public function staExenta($val)
    {
        $this->pre_exenta->staVal($val);
    }

    public function staPlaneacion($val)
    {
        $this->pre_planeacion->staVal($val);
    }
    /*         * ****************************************************

          CONSTRUCTOR

         * **************************************************** */

    public function __construct()
    {

        parent::__construct(['tabla' => 'presupuestoVigente']);

        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null;

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', 'PRI', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', 'PRI', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', 'PRI', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', 'PRI', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', 'PRI', 'ecf_id');
        $this->tcp_id = new Atributo('cadena', 'NO', 'PRI', 'tcp_id');
        $this->pre_ampliacion = new Atributo('moneda', 'NO', '', 'pre_ampliacion');
        $this->pre_reduccion = new Atributo('moneda', 'NO', '', 'pre_reduccion');
        $this->pre_vigente = new Atributo('moneda', 'NO', '', 'pre_vigente');
        $this->pre_omo = new Atributo('cadena', 'NO', '', 'pre_omo');
        $this->pre_mod = new Atributo('numero', 'YES', '', 'pre_mod');
        $this->pre_mov = new Atributo('cadena', 'NO', '', 'pre_mov');
        $this->pre_exenta = new Atributo('numero', 'NO', '', 'pre_exenta');
        $this->pre_planeacion = new Atributo('numero', 'NO', '', 'pre_planeacion');
        $this->mesVigente = new Meses(['prefix' => 'vig_', 'tipo' => 'moneda']);

        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $ue_id = (!isset($prm->ue_id)) ? '' : $prm->ue_id;
        $epg_id = (!isset($prm->epg_id)) ? '' : $prm->epg_id;
        $pg_id = (!isset($prm->pg_id)) ? '' : $prm->pg_id;
        $pe_id = (!isset($prm->pe_id)) ? '' : $prm->pe_id;
        $ecf_id = (!isset($prm->ecf_id)) ? '' : $prm->ecf_id;
        $tcp_id = (!isset($prm->tcp_id)) ? '' : $prm->tcp_id;

        if ($gpo_id != '' && $ur_id != '' && $ue_id != '' && $epg_id != '' && $pg_id != '' && $pe_id != '' && $ecf_id != '') {

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
    /*         * ****************************************************

          FUNCIONES ESTÁTICAS

         * **************************************************** */

    public static function abc($p)
    {
        $pre = new PresupuestoVigente();
        if (isset($p->gpo_id)) {
            $pre->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $pre->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $pre->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $pre->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $pre->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $pre->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $pre->setEcf_id($p->ecf_id);
        }
        if (isset($p->tcp_id)) {
            $pre->setTcp_id($p->tcp_id);
        }
        if (isset($p->ampliacion)) {
            $pre->setAmpliacion($p->ampliacion);
        }
        if (isset($p->reduccion)) {
            $pre->setReduccion($p->reduccion);
        }
        if (isset($p->vigente)) {
            $pre->setVigente($p->vigente);
        }
        if (isset($p->omo)) {
            $pre->setOmo($p->omo);
        }
        if (isset($p->mod)) {
            $pre->setMod($p->mod);
        }
        if (isset($p->mov)) {
            $pre->setMov($p->mov);
        }
        if (isset($p->exenta)) {
            $pre->setExenta($p->exenta);
        }
        if (isset($p->planeacion)) {
            $pre->setPlaneacion($p->planeacion);
        }
        switch ($p->abc) {
            case 'a':
                $pre->insert();
                return $pre->getUltimoId();
                break;
            case 'b':
                $pre->delete();
                break;
            case 'c':
                $pre->update();
                break;
        }
    }
}
