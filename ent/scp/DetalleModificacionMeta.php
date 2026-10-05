<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class DetalleModificacionMeta extends Entidad
{
    /*     * ****************************************************

    ATRIBUTOS DE LA CLASE

     * **************************************************** */
    protected $omo_id; //cat_OrigenModificacion - Llave foránea - Clave del origen de la modificación
    protected $mme_gpo_id; //cat_UnidadResponsable - Llave foránea - Clave del grupo de la clasificación administrativa
    protected $mme_ur_id; //cat_UnidadResponsable - Llave foránea
    protected $mme_id; //Clave identificadora del numero de modificación de meta
    protected $epg_id; //enc_Programatico - Llave foránea - Encuadre Programático
    protected $pii_id;
    protected $tri_id;
    protected $mact_modificado;
    protected $usr_id; //Identificador de usuario que registra el detalle

    /*     * ****************************************************

    METODOS GET

     * **************************************************** */

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

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /*     * ****************************************************

    METODOS SET

     * **************************************************** */

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

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /*     * ****************************************************

    METODOS STA (SET sin persistencia)

     * **************************************************** */

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
        $this->mact_modificado->staVal($val);
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

        parent::__construct(['tabla' => 'scp_DetalleModificacionMeta']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->omo_id = new Atributo('cadena', 'NO', 'PRI', 'omo_id');
        $this->mme_gpo_id = new Atributo('numero', 'NO', 'PRI', 'mme_gpo_id');
        $this->mme_ur_id = new Atributo('numero', 'NO', 'PRI', 'mme_ur_id');
        $this->mme_id = new Atributo('numero', 'NO', 'PRI', 'mme_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pii_id = new Atributo('numero', 'NO', '', 'pii_id');
        $this->tri_id = new Atributo('numero', 'NO', '', 'tri_id');
        $this->mact_modificado = new Atributo('numero', 'NO', '', 'mact_modificado');
        $this->usr_id = new Atributo('numero', 'YES', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) :
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $omo_id = (!isset($prm->omo_id)) ? '' : $prm->omo_id;
        $gpo_id = (!isset($prm->mme_gpo_id)) ? '' : $prm->mme_gpo_id;
        $ur_id = (!isset($prm->mme_ur_id)) ? '' : $prm->mme_ur_id;
        $mme_id = (!isset($prm->mme_id)) ? '' : $prm->mme_id;

        if ($mme_id != '' && $omo_id != '' && $gpo_id != '' && $ur_id != '') {
            $this->setOmo_id($omo_id);
            $this->setMme_gpo_id($gpo_id);
            $this->setMme_ur_id($ur_id);
            $this->setMme_id($mme_id);
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
        $dmm = new DetalleModificacionMeta();
        if (isset($p->omo_id)) {
            $dmm->setOmo_id($p->omo_id);
        }
        if (isset($p->mme_gpo_id)) {
            $dmm->setMme_gpo_id($p->mme_gpo_id);
        }
        if (isset($p->mme_ur_id)) {
            $dmm->setMme_ur_id($p->mme_ur_id);
        }
        if (isset($p->mme_id)) {
            $dmm->setMme_id($p->mme_id);
        }
        if (isset($p->epg_id)) {
            $dmm->setEpg_id($p->epg_id);
        }
        if (isset($p->pii_id)) {
            $dmm->setPii_id($p->pii_id);
        }
        if (isset($p->tri_id)) {
            $dmm->setTri_id($p->tri_id);
        }
        if (isset($p->mact_modificado)) {
            $dmm->setModificado($p->mact_modificado);
        }
        if (isset($p->usr_id)) {
            $dmm->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $sql = 'SELECT @mme_id as mme_id;';
                $dmm->conectar();
                $dmm->insert(false, true);
                $dmm->setSql($sql);
                $dmm->consultar($params);
                $dmm->desconectar();
                $mme_id = $dmm->extraer();
                return $mme_id['mme_id'];
                break;
            case 'b':
                $dmm->delete();
                break;
            case 'c':
                $dmm->update();
                break;
        }
    }
}
