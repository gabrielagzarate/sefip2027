<?php

require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/Entidad.php';

class BitObraActividad extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $oa; //Clave identificadora de la bitacora
    protected $bit_observacion; //Modificacion realizada
    protected $gpo_id; //
    protected $ur_id; //
    protected $prg_id; //
    protected $spr_id; //
    protected $py_id; //
    protected $oa_id; //
    protected $ue_id; //
    protected $oa_nombre; //
    protected $oa_anticipo; //
    protected $oa_anticipo2026; //
    protected $oa_amortizacion; //
    protected $eug_id; //
    protected $eoa_id; //Estado de la actividad
    protected $toa_id; //
    protected $pbf_id; //
    protected $sts_id; //
    protected $usr_ip; //
    protected $usr_id; //

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getOa()
    {
        return $this->oa->getVal();
    }
    public function getBit_observacion()
    {
        return $this->bit_observacion->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getPrg_id()
    {
        return $this->prg_id->getVal();
    }
    public function getSpr_id()
    {
        return $this->spr_id->getVal();
    }
    public function getPy_id()
    {
        return $this->py_id->getVal();
    }
    public function getOa_id()
    {
        return $this->oa_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getOa_nombre()
    {
        return $this->oa_nombre->getVal();
    }
    public function getOa_anticipo()
    {
        return $this->oa_anticipo->getVal();
    }
    public function getOa_anticipo2026()
    {
        return $this->oa_anticipo2026->getVal();
    }
    public function getOa_amortizacion()
    {
        return $this->oa_amortizacion->getVal();
    }
    public function getEug_id()
    {
        return $this->eug_id->getVal();
    }
    public function getEoa_id()
    {
        return $this->eoa_id->getVal();
    }
    public function getToa_id()
    {
        return $this->toa_id->getVal();
    }
    public function getPbf_id()
    {
        return $this->pbf_id->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }
    public function getUsr_ip()
    {
        return $this->usr_ip->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setOa($val)
    {
        $this->oa->setVal($val);
    }
    public function setBit_observacion($val)
    {
        $this->bit_observacion->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setPrg_id($val)
    {
        $this->prg_id->setVal($val);
    }
    public function setSpr_id($val)
    {
        $this->spr_id->setVal($val);
    }
    public function setPy_id($val)
    {
        $this->py_id->setVal($val);
    }
    public function setOa_id($val)
    {
        $this->oa_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setOa_nombre($val)
    {
        $this->oa_nombre->setVal($val);
    }
    public function setOa_anticipo($val)
    {
        $this->oa_anticipo->setVal($val);
    }
    public function setOa_anticipo2026($val)
    {
        $this->oa_anticipo2026->setVal($val);
    }
    public function setOa_amortizacion($val)
    {
        $this->oa_amortizacion->setVal($val);
    }
    public function setEug_id($val)
    {
        $this->eug_id->setVal($val);
    }
    public function setEoa_id($val)
    {
        $this->eoa_id->setVal($val);
    }
    public function setToa_id($val)
    {
        $this->toa_id->setVal($val);
    }
    public function setPbf_id($val)
    {
        $this->pbf_id->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }
    public function setUsr_ip($val)
    {
        $this->usr_ip->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staOa($val)
    {
        $this->oa->staVal($val);
    }
    public function staBit_observacion($val)
    {
        $this->bit_observacion->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staPrg_id($val)
    {
        $this->prg_id->staVal($val);
    }
    public function staSpr_id($val)
    {
        $this->spr_id->staVal($val);
    }
    public function staPy_id($val)
    {
        $this->py_id->staVal($val);
    }
    public function staOa_id($val)
    {
        $this->oa_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staOa_nombre($val)
    {
        $this->oa_nombre->staVal($val);
    }
    public function staOa_anticipo($val)
    {
        $this->oa_anticipo->staVal($val);
    }
    public function staOa_anticipo2026($val)
    {
        $this->oa_anticipo2026->staVal($val);
    }
    public function staOa_amortizacion($val)
    {
        $this->oa_amortizacion->staVal($val);
    }
    public function staEug_id($val)
    {
        $this->eug_id->staVal($val);
    }
    public function staEoa_id($val)
    {
        $this->eoa_id->staVal($val);
    }
    public function staToa_id($val)
    {
        $this->toa_id->staVal($val);
    }
    public function staPbf_id($val)
    {
        $this->pbf_id->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }
    public function staUsr_ip($val)
    {
        $this->usr_ip->staVal($val);
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

        parent::__construct(['tabla' => BD_BITACORA . '.bit_ObraActividad']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->oa = new Atributo('numero', 'NO', 'PRI', 'bit_oa');
        $this->bit_observacion = new Atributo('cadena', 'NO', '', 'bit_observacion');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->prg_id = new Atributo('numero', 'NO', '', 'prg_id');
        $this->spr_id = new Atributo('numero', 'NO', '', 'spr_id');
        $this->py_id = new Atributo('numero', 'NO', '', 'py_id');
        $this->oa_id = new Atributo('numero', 'NO', '', 'oa_id');
        $this->ue_id = new Atributo('numero', 'YES', '', 'ue_id');
        $this->oa_nombre = new Atributo('cadena', 'NO', '', 'oa_nombre');
        $this->oa_anticipo = new Atributo('numero', 'NO', '', 'oa_anticipo');
        $this->oa_anticipo2026 = new Atributo('numero', 'NO', '', 'oa_anticipo2026');
        $this->oa_amortizacion = new Atributo('numero', 'NO', '', 'oa_amortizacion');
        $this->eug_id = new Atributo('numero', 'NO', '', 'eug_id');
        $this->eoa_id = new Atributo('numero', 'NO', '', 'eoa_id');
        $this->toa_id = new Atributo('numero', 'NO', '', 'toa_id');
        $this->pbf_id = new Atributo('numero', 'NO', '', 'pbf_id');
        $this->sts_id = new Atributo('numero', 'NO', '', 'sts_id');
        $this->usr_ip = new Atributo('ip', 'NO', '', 'usr_ip');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $bit = new BitObraActividad();
        if (isset($p->oa)) {
            $bit->setOa($p->oa);
        }

        if (isset($p->observacion)) {
            $bit->setBit_observacion($p->observacion);
        }

        if (isset($p->gpo_id)) {
            $bit->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $bit->setUr_id($p->ur_id);
        }

        if (isset($p->prg_id)) {
            $bit->setPrg_id($p->prg_id);
        }

        if (isset($p->spr_id)) {
            $bit->setSpr_id($p->spr_id);
        }

        if (isset($p->py_id)) {
            $bit->setPy_id($p->py_id);
        }

        if (isset($p->oa_id)) {
            $bit->setOa_id($p->oa_id);
        }

        if (isset($p->ue_id)) {
            $bit->setUe_id($p->ue_id);
        }

        if (isset($p->nombre)) {
            $bit->setOa_nombre($p->nombre);
        }

        if (isset($p->anticipo)) {
            $bit->setOa_anticipo($p->anticipo);
        }

        if (isset($p->anticipo2026)) {
            $bit->setOa_anticipo2026($p->anticipo2026);
        }

        if (isset($p->amortizacion)) {
            $bit->setOa_amortizacion($p->amortizacion);
        }

        if (isset($p->eug_id)) {
            $bit->setEug_id($p->eug_id);
        }

        if (isset($p->eoa_id)) {
            $bit->setEoa_id($p->eoa_id);
        }

        if (isset($p->toa_id)) {
            $bit->setToa_id($p->toa_id);
        }

        if (isset($p->pbf_id)) {
            $bit->setPbf_id($p->pbf_id);
        }

        if (isset($p->sts_id)) {
            $bit->setSts_id($p->sts_id);
        }

        if (isset($p->usr_id)) {
            $bit->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        switch ($p->abc) {
            case 'a':
                $bit->setUsr_ip($ip);
                $bit->insert();
                return $bit->getUltimoId();
                break;
                break;
            case 'b':
                $bit->delete();
                break;
            case 'c':
                $bit->update();
                break;
        }
    }
}
