<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class BitacoraDetalle extends Entidad
{
    /*     * ****************************************************
        ATRIBUTOS DE LA CLASE
     * **************************************************** */
    protected $bit_dmo;
    protected $bit_observacion;
    protected $omo_id;
    protected $mod_gpo_id;
    protected $mod_ur_id;
    protected $mod_id;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $epg_id;
    protected $pg_id;
    protected $pe_id;
    protected $ecf_id;
    protected $tcp_id;
    protected $mov_id;
    protected $dmo_importe;
    protected $dmo_ene;
    protected $dmo_feb;
    protected $dmo_mar;
    protected $dmo_abr;
    protected $dmo_may;
    protected $dmo_jun;
    protected $dmo_jul;
    protected $dmo_ago;
    protected $dmo_sep;
    protected $dmo_oct;
    protected $dmo_nov;
    protected $dmo_dic;
    protected $usr_ip;
    protected $usr_id;

    /*     * ****************************************************
        METODOS GET
     * **************************************************** */

    public function getDmo()
    {
        return $this->dmo->getVal();
    }

    public function getObservacion()
    {
        return $this->observacion->getVal();
    }

    public function getOmo_id()
    {
        return $this->omo_id->getVal();
    }

    public function getMod_gpo_id()
    {
        return $this->mod_gpo_id->getVal();
    }

    public function getMod_ur_id()
    {
        return $this->mod_ur_id->getVal();
    }

    public function getMod_id()
    {
        return $this->mod_id->getVal();
    }

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

    public function getMov_id()
    {
        return $this->mov_id->getVal();
    }

    public function getImporte()
    {
        return $this->dmo_importe->getVal();
    }

    public function getEne()
    {
        return $this->dmo_ene->getVal();
    }

    public function getFeb()
    {
        return $this->dmo_feb->getVal();
    }

    public function getMar()
    {
        return $this->dmo_mar->getVal();
    }

    public function getAbr()
    {
        return $this->dmo_abr->getVal();
    }

    public function getMay()
    {
        return $this->dmo_may->getVal();
    }

    public function getJun()
    {
        return $this->dmo_jun->getVal();
    }

    public function getJul()
    {
        return $this->dmo_jul->getVal();
    }

    public function getAgo()
    {
        return $this->dmo_ago->getVal();
    }

    public function getSep()
    {
        return $this->dmo_sep->getVal();
    }

    public function getOct()
    {
        return $this->dmo_oct->getVal();
    }

    public function getNov()
    {
        return $this->dmo_nov->getVal();
    }

    public function getDic()
    {
        return $this->dmo_dic->getVal();
    }

    public function getIp_Bit()
    {
        return $this->usr_ip->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /*     * ****************************************************
        METODOS SET
     * **************************************************** */

    public function setDmo($val)
    {
        $this->dmo->setVal($val);
    }

    public function setObservacion($val)
    {
        $this->observacion->setVal($val);
    }

    public function setOmo_id($val)
    {
        $this->omo_id->setVal($val);
    }

    public function setMod_gpo_id($val)
    {
        $this->mod_gpo_id->setVal($val);
    }

    public function setMod_ur_id($val)
    {
        $this->mod_ur_id->setVal($val);
    }

    public function setMod_id($val)
    {
        $this->mod_id->setVal($val);
    }

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

    public function setMov_id($val)
    {
        $this->mov_id->setVal($val);
    }

    public function setImporte($val)
    {
        $this->dmo_importe->setVal($val);
    }

    public function setEne($val)
    {
        $this->dmo_ene->setVal($val);
    }

    public function setFeb($val)
    {
        $this->dmo_feb->setVal($val);
    }

    public function setMar($val)
    {
        $this->dmo_mar->setVal($val);
    }

    public function setAbr($val)
    {
        $this->dmo_abr->setVal($val);
    }

    public function setMay($val)
    {
        $this->dmo_may->setVal($val);
    }

    public function setJun($val)
    {
        $this->dmo_jun->setVal($val);
    }

    public function setJul($val)
    {
        $this->dmo_jul->setVal($val);
    }

    public function setAgo($val)
    {
        $this->dmo_ago->setVal($val);
    }

    public function setSep($val)
    {
        $this->dmo_sep->setVal($val);
    }

    public function setOct($val)
    {
        $this->dmo_oct->setVal($val);
    }

    public function setNov($val)
    {
        $this->dmo_nov->setVal($val);
    }

    public function setDic($val)
    {
        $this->dmo_dic->setVal($val);
    }

    public function setIp($val)
    {
        $this->usr_ip->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /*     * ****************************************************
        METODOS STA (SET sin persistencia)
     * **************************************************** */

    public function staDmo($val)
    {
        $this->dmo->staVal($val);
    }

    public function staObservacion($val)
    {
        $this->observacion->staVal($val);
    }

    public function staOmo_id($val)
    {
        $this->omo_id->staVal($val);
    }

    public function staMod_gpo_id($val)
    {
        $this->mod_gpo_id->staVal($val);
    }

    public function staMod_ur_id($val)
    {
        $this->mod_ur_id->staVal($val);
    }

    public function staMod_id($val)
    {
        $this->mod_id->staVal($val);
    }

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

    public function staMov_id($val)
    {
        $this->mov_id->staVal($val);
    }

    public function staImporte($val)
    {
        $this->dmo_importe->staVal($val);
    }

    public function staEne($val)
    {
        $this->dmo_ene->staVal($val);
    }

    public function staFeb($val)
    {
        $this->dmo_feb->staVal($val);
    }

    public function staMar($val)
    {
        $this->dmo_mar->staVal($val);
    }

    public function staAbr($val)
    {
        $this->dmo_abr->staVal($val);
    }

    public function staMay($val)
    {
        $this->dmo_may->staVal($val);
    }

    public function staJun($val)
    {
        $this->dmo_jun->staVal($val);
    }

    public function staJul($val)
    {
        $this->dmo_jul->staVal($val);
    }

    public function staAgo($val)
    {
        $this->dmo_ago->staVal($val);
    }

    public function staSep($val)
    {
        $this->dmo_sep->staVal($val);
    }

    public function staOct($val)
    {
        $this->dmo_oct->staVal($val);
    }

    public function staNov($val)
    {
        $this->dmo_nov->staVal($val);
    }

    public function staDic($val)
    {
        $this->dmo_dic->staVal($val);
    }

    public function staIp($val)
    {
        $this->usr_ip->staVal($val);
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

        parent::__construct(['tabla' => '' . BD_BITACORA . '.bit_DetalleModificacion']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->dmo = new Atributo('numero', 'NO', 'PRI', 'bit_dmo');
        $this->observacion = new Atributo('cadena', 'NO', '', 'bit_observacion');
        $this->omo_id = new Atributo('cadena', 'NO', '', 'omo_id');
        $this->mod_gpo_id = new Atributo('numero', 'NO', '', 'mod_gpo_id');
        $this->mod_ur_id = new Atributo('numero', 'NO', '', 'mod_ur_id');
        $this->mod_id = new Atributo('numero', 'NO', '', 'mod_id');
        $this->gpo_id = new Atributo('numero', 'NO', '', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', '', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', '', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->tcp_id = new Atributo('cadena', 'NO', '', 'tcp_id');
        $this->mov_id = new Atributo('cadena', 'NO', '', 'mov_id');
        $this->dmo_importe = new Atributo('numero', 'NO', '', 'dmo_importe');
        $this->dmo_ene = new Atributo('numero', 'NO', '', 'dmo_ene');
        $this->dmo_feb = new Atributo('numero', 'NO', '', 'dmo_feb');
        $this->dmo_mar = new Atributo('numero', 'NO', '', 'dmo_mar');
        $this->dmo_abr = new Atributo('numero', 'NO', '', 'dmo_abr');
        $this->dmo_may = new Atributo('numero', 'NO', '', 'dmo_may');
        $this->dmo_jun = new Atributo('numero', 'NO', '', 'dmo_jun');
        $this->dmo_jul = new Atributo('numero', 'NO', '', 'dmo_jul');
        $this->dmo_ago = new Atributo('numero', 'NO', '', 'dmo_ago');
        $this->dmo_sep = new Atributo('numero', 'NO', '', 'dmo_sep');
        $this->dmo_oct = new Atributo('numero', 'NO', '', 'dmo_oct');
        $this->dmo_nov = new Atributo('numero', 'NO', '', 'dmo_nov');
        $this->dmo_dic = new Atributo('numero', 'NO', '', 'dmo_dic');
        $this->usr_ip = new Atributo('numero', 'NO', '', 'usr_ip');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }
    /*     * ****************************************************
        FUNCIONES ESTÁTICAS
     * **************************************************** */

    public static function abc($p)
    {
        $bit = new BitacoraDetalle();
        if (isset($p->dmo)) {
            $bit->setDmo($p->dmo);
        }
        if (isset($p->observacion)) {
            $bit->setObservacion($p->observacion);
        }
        if (isset($p->omo_id)) {
            $bit->setOmo_id($p->omo_id);
        }
        if (isset($p->mod_gpo_id)) {
            $bit->setMod_gpo_id($p->mod_gpo_id);
        }
        if (isset($p->mod_ur_id)) {
            $bit->setMod_ur_id($p->mod_ur_id);
        }
        if (isset($p->mod_id)) {
            $bit->setMod_id($p->mod_id);
        }
        if (isset($p->gpo_id)) {
            $bit->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $bit->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $bit->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $bit->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $bit->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $bit->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $bit->setEcf_id($p->ecf_id);
        }
        if (isset($p->tcp_id)) {
            $bit->setTcp_id($p->tcp_id);
        }
        if (isset($p->mov_id)) {
            $bit->setMov_id($p->mov_id);
        }
        if (isset($p->dmo_importe)) {
            $bit->setImporte($p->dmo_importe);
        }
        if (isset($p->dmo_ene)) {
            $bit->setEne($p->dmo_ene);
        }
        if (isset($p->dmo_feb)) {
            $bit->setFeb($p->dmo_feb);
        }
        if (isset($p->dmo_mar)) {
            $bit->setMar($p->dmo_mar);
        }
        if (isset($p->dmo_abr)) {
            $bit->setAbr($p->dmo_abr);
        }
        if (isset($p->dmo_may)) {
            $bit->setMay($p->dmo_may);
        }
        if (isset($p->dmo_jun)) {
            $bit->setJun($p->dmo_jun);
        }
        if (isset($p->dmo_jul)) {
            $bit->setJul($p->dmo_jul);
        }
        if (isset($p->dmo_ago)) {
            $bit->setAgo($p->dmo_ago);
        }
        if (isset($p->dmo_sep)) {
            $bit->setSep($p->dmo_sep);
        }
        if (isset($p->dmo_oct)) {
            $bit->setOct($p->dmo_oct);
        }
        if (isset($p->dmo_nov)) {
            $bit->setNov($p->dmo_nov);
        }
        if (isset($p->dmo_dic)) {
            $bit->setDic($p->dmo_dic);
        }
        if (isset($p->ip)) {
            $bit->setIp(ip2long($p->ip));
        }
        if (isset($p->usr_id)) {
            $bit->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {

            case 'a':
                $bit->insert();
                return $bit->getUltimoId();
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
