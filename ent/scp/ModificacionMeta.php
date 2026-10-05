<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class ModificacionMeta extends Entidad
{
    /*     * ****************************************************

    ATRIBUTOS DE LA CLASE

     * **************************************************** */
    protected $omo_id; //cat_OrigenModificacion - Llave foránea - Clave del origen de la modificación
    protected $mme_gpo_id; //cat_UnidadResponsable - Llave foránea - Clave del grupo de la clasificación administrativa
    protected $mme_ur_id; //cat_UnidadResponsable - Llave foránea
    protected $mme_id; //Clave identificadora del numero de modificación de meta
    protected $mme_ue_id; //Unidad Ejecutora en la que se registra el folio, aplicable para finanzas-normativa
    protected $emo_id; //cat_EstadoModificacion - Llave foránea - Estado de la modificación
    protected $mme_clave; //Clave identificadora del folio, formado por el origen, ur y número de folio
    protected $mmd_id; //cat_ModalidadModificacion - Llave foránea - Modalidad del folio E - Externa
    protected $tfm_id; //Adecuación del folio M - Adecuación de metas
    protected $tmd_id; //Adecuación del folio M - Adecuación de metas
    protected $mme_concepto; //Concepto por el que se realiza la modificación de metas
    protected $mme_fechaFolio; //Fecha de creación del folio de metas
    protected $mme_oficioSefin; //Oficio de solicitud ejecutor de gasto, este es requisitado en la revisión modulo SEFIN
    protected $mme_oficioSolicitudSefin; //Oficio de solicitud ejecutor de gasto, este es requisitado en la revisión modulo SEFIN
    protected $mme_operacion; //Numero de operación del folio de modificación de metas
    protected $mme_fechaAutorizacion; //Fecha de autorización del folio de modificación de metas
    protected $mme_origen; //S = Sistema
    protected $mme_subsecretaria; //Describe la subsecretaria a la que se está solicitando la modificación de metas, si es 1 va a egresos
    protected $mme_vobo; //Visto bueno
    protected $prg_cero; //Identificador si el folio es generado en la opción Administrador de Metas =0 o Administrador de metas prg000=1
    protected $usr_id; //Identificador de usuario que registra el folio

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

    public function getMme_ue_id()
    {
        return $this->mme_ue_id->getVal();
    }

    public function getEmo_id()
    {
        return $this->emo_id->getVal();
    }

    public function getClave()
    {
        return $this->mme_clave->getVal();
    }

    public function getMmd_id()
    {
        return $this->mmd_id->getVal();
    }

    public function getTfm_id()
    {
        return $this->tfm_id->getVal();
    }

    public function getTmd_id()
    {
        return $this->tmd_id->getVal();
    }

    public function getConcepto()
    {
        return $this->mme_concepto->getVal();
    }

    public function getFechaFolio()
    {
        return $this->mme_fechaFolio->getVal();
    }

    public function getOficioSefin()
    {
        return $this->mme_oficioSefin->getVal();
    }

    public function getOficioSolicitudSefin()
    {
        return $this->mme_oficioSolicitudSefin->getVal();
    }

    public function getOperacion()
    {
        return $this->mme_operacion->getVal();
    }

    public function getFechaAutorizacion()
    {
        return $this->mme_fechaAutorizacion->getVal();
    }

    public function getOrigen()
    {
        return $this->mme_origen->getVal();
    }

    public function getSubsecretaria()
    {
        return $this->mme_subsecretaria->getVal();
    }

    public function getVobo()
    {
        return $this->mme_vobo->getVal();
    }

    public function getPrgCero()
    {
        return $this->prg_cero->getVal();
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

    public function setMme_ue_id($val)
    {
        $this->mme_ue_id->setVal($val);
    }

    public function setEmo_id($val)
    {
        $this->emo_id->setVal($val);
    }

    public function setClave($val)
    {
        $this->mme_clave->setVal($val);
    }

    public function setMmd_id($val)
    {
        $this->mmd_id->setVal($val);
    }

    public function setTfm_id($val)
    {
        $this->tfm_id->setVal($val);
    }

    public function setTmd_id($val)
    {
        $this->tmd_id->setVal($val);
    }

    public function setConcepto($val)
    {
        $this->mme_concepto->setVal($val);
    }

    public function setFechaFolio($val)
    {
        $this->mme_fechaFolio->setVal($val);
    }

    public function setOficioSefin($val)
    {
        $this->mme_oficioSefin->setVal($val);
    }

    public function setOficioSolicitudSefin($val)
    {
        $this->mme_oficioSolicitudSefin->setVal($val);
    }

    public function setOperacion($val)
    {
        $this->mme_operacion->setVal($val);
    }

    public function setFechaAutorizacion($val)
    {
        $this->mme_fechaAutorizacion->setVal($val);
    }

    public function setOrigen($val)
    {
        $this->mme_origen->setVal($val);
    }

    public function setSubsecretaria($val)
    {
        $this->mme_subsecretaria->setVal($val);
    }

    public function setVobo($val)
    {
        $this->mme_vobo->setVal($val);
    }

    public function setPrgCero($val)
    {
        $this->prg_cero->setVal($val);
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

    public function staMme_ue_id($val)
    {
        $this->mme_ue_id->staVal($val);
    }

    public function staEmo_id($val)
    {
        $this->emo_id->staVal($val);
    }

    public function staClave($val)
    {
        $this->mme_clave->staVal($val);
    }

    public function staMmd_id($val)
    {
        $this->mmd_id->staVal($val);
    }

    public function staTfm_id($val)
    {
        $this->tfm_id->staVal($val);
    }

    public function staTmd_id($val)
    {
        $this->tmd_id->staVal($val);
    }

    public function staConcepto($val)
    {
        $this->mme_concepto->staVal($val);
    }

    public function staFechaFolio($val)
    {
        $this->mme_fechaFolio->staVal($val);
    }

    public function staOficioSefin($val)
    {
        $this->mme_oficioSefin->staVal($val);
    }

    public function staOficioSolicitudSefin($val)
    {
        $this->mme_oficioSolicitudSefin->staVal($val);
    }

    public function staOperacion($val)
    {
        $this->mme_operacion->staVal($val);
    }

    public function staFechaAutorizacion($val)
    {
        $this->mme_fechaAutorizacion->staVal($val);
    }

    public function staOrigen($val)
    {
        $this->mme_origen->staVal($val);
    }

    public function staSubsecretaria($val)
    {
        $this->mme_subsecretaria->staVal($val);
    }

    public function staVobo($val)
    {
        $this->mme_vobo->staVal($val);
    }

    public function staPrgCero($val)
    {
        $this->prg_cero->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    /*     * **************************************************** CONSTRUCTOR    ***************************************************** */

    public function __construct()
    {

        parent::__construct(['tabla' => 'scp_ModificacionMeta']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->omo_id = new Atributo('cadena', 'NO', 'PRI', 'omo_id');
        $this->mme_gpo_id = new Atributo('numero', 'NO', 'PRI', 'mme_gpo_id');
        $this->mme_ur_id = new Atributo('numero', 'NO', 'PRI', 'mme_ur_id');
        $this->mme_id = new Atributo('numero', 'NO', 'PRI', 'mme_id');
        $this->mme_ue_id = new Atributo('numero', 'YES', '', 'mme_ue_id');
        $this->emo_id = new Atributo('numero', 'NO', '', 'emo_id');
        $this->mme_clave = new Atributo('cadena', 'NO', '', 'mme_clave');
        $this->mmd_id = new Atributo('cadena', 'NO', '', 'mmd_id');
        $this->tfm_id = new Atributo('cadena', 'NO', '', 'tfm_id');
        $this->tmd_id = new Atributo('cadena', 'NO', '', 'tmd_id');
        $this->mme_concepto = new Atributo('cadena', 'NO', '', 'mme_concepto');
        $this->mme_fechaFolio = new Atributo('fecha', 'NO', '', 'mme_fechaFolio');
        $this->mme_oficioSefin = new Atributo('cadena', 'YES', '', 'mme_oficioSefin');
        $this->mme_oficioSolicitudSefin = new Atributo(
            'cadena',
            'YES',
            '',
            'mme_oficioSolicitudSefin'
        );
        $this->mme_operacion = new Atributo('numero', 'YES', '', 'mme_operacion');
        $this->mme_fechaAutorizacion = new Atributo(
            'fecha',
            'YES',
            '',
            'mme_fechaAutorizacion'
        );
        $this->mme_origen = new Atributo('cadena', 'NO', '', 'mme_origen');
        $this->mme_subsecretaria = new Atributo(
            'numero',
            'NO',
            '',
            'mme_subsecretaria'
        );
        $this->mme_vobo = new Atributo('numero', 'NO', '', 'mme_vobo');
        $this->prg_cero = new Atributo('numero', 'NO', '', 'prg_cero');
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
        }
    }
    /*     * ****************************************************

    FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $mme = new ModificacionMeta();
        if (isset($p->omo_id)) {
            $mme->setOmo_id($p->omo_id);
        }
        if (isset($p->mme_gpo_id)) {
            $mme->setMme_gpo_id($p->mme_gpo_id);
        }
        if (isset($p->mme_ur_id)) {
            $mme->setMme_ur_id($p->mme_ur_id);
        }
        if (isset($p->mme_id)) {
            $mme->setMme_id($p->mme_id);
        }
        if (isset($p->mme_ue_id)) {
            $mme->setMme_ue_id($p->mme_ue_id);
        }
        if (isset($p->emo_id)) {
            $mme->setEmo_id($p->emo_id);
        }
        if (isset($p->clave)) {
            $mme->setClave($p->clave);
        }
        if (isset($p->mmd_id)) {
            $mme->setMmd_id($p->mmd_id);
        }
        if (isset($p->tfm_id)) {
            $mme->setTfm_id($p->tfm_id);
        }
        if (isset($p->tmd_id)) {
            $mme->setTmd_id($p->tmd_id);
        }
        if (isset($p->concepto)) {
            $mme->setConcepto($p->concepto);
        }
        if (isset($p->fechaFolio)) {
            $mme->setFechaFolio($p->fechaFolio);
        }
        if (isset($p->oficioSefin)) {
            $mme->setOficioSefin($p->oficioSefin);
        }
        if (isset($p->oficioSolicitudSefin)) {
            $mme->setOficioSolicitudSefin($p->oficioSolicitudSefin);
        }
        if (isset($p->operacion)) {
            $mme->setOperacion($p->operacion);
        }
        if (isset($p->fechaAutorizacion)) {
            $mme->setFechaAutorizacion($p->fechaAutorizacion);
        }
        if (isset($p->origen)) {
            $mme->setOrigen($p->origen);
        }
        if (isset($p->subsecretaria)) {
            $mme->setSubsecretaria($p->subsecretaria);
        }
        if (isset($p->vobo)) {
            $mme->setVobo($p->vobo);
        }
        if (isset($p->prg_cero)) {
            $mme->setPrgCero($p->prg_cero);
        }
        if (isset($p->usr_id)) {
            $mme->setUsr_id($p->usr_id);
        }
        $sql = '';
        $params = [];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1')
        ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = (isset($_SESSION[_S]['usr_id']) && $_SESSION[_S]['usr_id'] != '')
        ? $_SESSION[_S]['usr_id'] : '1';
        switch ($p->abc) {
            case 'a':
                $sql = 'SELECT @mme_id as mme_id;';
                $mme->conectar();
                $mme->insert(false, true);
                $mme->setSql($sql);
                $mme->consultar($params);
                $mme->desconectar();
                $mme_id = $mme->extraer();
                return $mme_id['mme_id'];
                break;
            case 'b':
                $mme->delete();
                break;
            case 'c':
                $sql = 'SET @usr_ip = sinpres.fn_get_ip(), @usr_id ="' . $usr_id . '",@bit_observacion="actualizado";';
                $mme->conectar();
                $mme->setSql($sql);
                $mme->consultar($params);
                $mme->update(false);
                break;
        }
    }
}
