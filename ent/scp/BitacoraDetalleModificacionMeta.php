<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class BitacoraDetalleModificacionMeta extends Entidad
{
    /*     * ****************************************************

    ATRIBUTOS DE LA CLASE

     * **************************************************** */
    protected $bit_dmm; //Identificador de la tabla Detalle Modificación Metas
    protected $bit_observacion; //Observación
    protected $omo_id; //cat_OrigenModificacion - Llave foránea - Clave del origen de la modificación
    protected $mme_gpo_id; //cat_UnidadResponsable - Llave foránea - Clave del grupo de la clasificación administrativa
    protected $mme_ur_id; //cat_UnidadResponsable - Llave foránea
    protected $mme_id; //Clave identificadora del número de modificación de metas
    protected $epg_id; //enc_Programatico - Llave foránea - Encuadre Programático
    protected $pii_id;
    protected $tri_id;
    protected $mact_modificado;
    protected $usr_ip; //IP del usuario
    protected $usr_id; //Identificador de usuario que registra el detalle

    /*     * ****************************************************

    METODOS GET

     * **************************************************** */

    public function getDmm()
    {
        return $this->bit_dmm->getVal();
    }

    public function getObservacion()
    {
        return $this->bit_observacion->getVal();
    }

    public function getOmo_id()
    {
        return $this->omo_id->getVal();
    }

    public function getMme_gpo_id()
    {
        return $this->mme_gpo_id->getVal();
    }

    public function getMme_ur_id()
    {
        return $this->mme_ur_id->getVal();
    }

    public function getMme_id()
    {
        return $this->mme_id->getVal();
    }

    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }

    public function getPii_id()
    {
        return $this->pii_id->getVal();
    }

    public function getTri_id()
    {
        return $this->tri_id->getVal();
    }

    public function getModificado()
    {
        return $this->mact_modificado->getVal();
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

    public function setDmm($val)
    {
        $this->bit_dmm->setVal($val);
    }

    public function setObservacion($val)
    {
        $this->bit_observacion->setVal($val);
    }

    public function setOmo_id($val)
    {
        $this->omo_id->setVal($val);
    }

    public function setMme_gpo_id($val)
    {
        $this->mme_gpo_id->setVal($val);
    }

    public function setMme_ur_id($val)
    {
        $this->mme_ur_id->setVal($val);
    }

    public function setMme_id($val)
    {
        $this->mme_id->setVal($val);
    }

    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }

    public function setPii_id($val)
    {
        $this->pii_id->setVal($val);
    }

    public function setTri_id($val)
    {
        $this->tri_id->setVal($val);
    }

    public function setModificado($val)
    {
        $this->mact_modificado->setVal($val);
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

    public function staDmm($val)
    {
        $this->bit_dmm->staVal($val);
    }

    public function staObservacion($val)
    {
        $this->bit_observacion->staVal($val);
    }

    public function staOmo_id($val)
    {
        $this->omo_id->staVal($val);
    }

    public function staMme_gpo_id($val)
    {
        $this->mme_gpo_id->staVal($val);
    }

    public function staMme_ur_id($val)
    {
        $this->mme_ur_id->staVal($val);
    }

    public function staMme_id($val)
    {
        $this->mme_id->staVal($val);
    }

    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }

    public function staPii_id($val)
    {
        $this->pii_id->staVal($val);
    }

    public function staTri_id($val)
    {
        $this->tri_id->staVal($val);
    }

    public function staModificado($val)
    {
        $this->mact_modificado->staVal();
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

        parent::__construct(['tabla' => BD_CTRL . 'Bitacora.bit_DetalleModificacionMeta']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->bit_dmm = new Atributo('numero', 'NO', 'PRI', 'bit_dmm');
        $this->bit_observacion = new Atributo('cadena', 'NO', '', 'bit_observacion');
        $this->omo_id = new Atributo('cadena', 'NO', '', 'omo_id');
        $this->mme_gpo_id = new Atributo('numero', 'NO', '', 'mme_gpo_id');
        $this->mme_ur_id = new Atributo('numero', 'NO', '', 'mme_ur_id');
        $this->mme_id = new Atributo('numero', 'NO', '', 'mme_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pii_id = new Atributo('numero', 'NO', '', 'pii_id');
        $this->tri_id = new Atributo('numero', 'NO', '', 'tri_id');
        $this->mact_modificado = new Atributo('numero', 'NO', '', 'mact_modificado');
        $this->usr_ip = new Atributo('numero', 'NO', '', 'usr_ip');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) :
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $bit_dmm = (!isset($prm->dmm)) ? '' : $prm->dmm;

        if ($bit_dmm != '') {
            $this->setDmm($bit_dmm);
            $this->select();
        }
    }
    /*     * ****************************************************

    FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $sql = '';
        $params = [];
        $bit = new BitacoraDetalleModificacionMeta();
        if (isset($p->dmm)) {
            $bit->setDmm($p->dmm);
        }
        if (isset($p->observacion)) {
            $bit->setObservacion($p->observacion);
        }
        if (isset($p->omo_id)) {
            $bit->setOmo_id($p->omo_id);
        }
        if (isset($p->mme_gpo_id)) {
            $bit->setMme_gpo_id($p->mme_gpo_id);
        }
        if (isset($p->mme_ur_id)) {
            $bit->setMme_ur_id($p->mme_ur_id);
        }
        if (isset($p->mme_id)) {
            $bit->setMme_id($p->mme_id);
        }
        if (isset($p->epg_id)) {
            $bit->setEpg_id($p->epg_id);
        }
        if (isset($p->pii_id)) {
            $bit->setPii_id($p->pii_id);
        }
        if (isset($p->tri_id)) {
            $bit->setTri_id($p->tri_id);
        }
        if (isset($p->mact_modificado)) {
            $bit->setModificado($p->mact_modificado);
        }
        if (isset($p->ip)) {
            $bit->setIp(ip2long($p->ip));
        }
        if (isset($p->usr_id)) {
            $bit->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $sql = 'SELECT @dmm as dmm;';
                $bit->conectar();
                $bit->insert(false, true);
                $bit->setSql($sql);
                $bit->consultar($params);
                $bit->desconectar();
                $dmm = $bit->extraer();
                return $dmm['dmm'];
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
