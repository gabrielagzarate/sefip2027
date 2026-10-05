<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class MaestroModificacion extends Entidad
{
    /*     * ****************************************************

    ATRIBUTOS DE LA CLASE

     * **************************************************** */
    protected $omo_id; //enc_OrigenOperacion - Llave foranea Clave del origen de la modificacion
    protected $mod_gpo_id; //cat_UnidadResponsable - Llave foranea Clave del grupo de la clasificacion administrativa
    protected $mod_ur_id; //cat_UnidadResponsable - Llave foranea
    protected $mod_id; //Identificador del numero de modificacion
    protected $mod_ue_id; //Unidad Ejecutora en la que se registra el folio, aplicable para finanzas-normativa
    protected $enc_oop; //enc_OrigenOperacion - Llave foranea
    protected $ecf_id; //Identificador del encuadre de Clave de Financiamiento
    protected $emo_id; //Estado de la modificacion presupuestal - Foranea -> cat_EstadoModificacion
    protected $mod_tipoMovimiento; //Indica el tipo de Movimiento A Ampliacion R Reduccion
    protected $mod_clave; //Clave identificadora del folio, formado por el origen, ur y número de folio
    protected $mod_concepto; //Concepto por el que se realiza la modificacion presupuestal
    protected $mod_fechaFolio; //Fecha de creacion del folio de mofidificacion presupuestal
    protected $mod_oficioSefin; //Oficio de autorizacion de SEFIN
    protected $mod_oficioSolicitudSefin; //Oficio de solicitud ejecutor de gasto, este es requisitado en la revisión modulo SEFIN
    protected $mod_oficioArchivo; //Nombre del archivo que contiene el oficio de autorización del folio interno en formato PDF.
    protected $mod_operacion; //Numero de operación del folio de modificacion presupuestal
    protected $mod_fechaAutorizacion; //Fecha de autorizacion del folio de modificacion presupuestal
    protected $msc_id_autorizacion; //
    protected $mod_origen; //Origen:R = Refrendo I = Importacion de obras C = Carga Masiva S = Sistema
    protected $mod_reporteInterno; //Nombre del archivo que contiene el reporte de la modificación presupuestal interna en formato PDF.
    protected $mod_Subsecretaria; //Describe la subsecretaria a la que se está solicitando la modificación presupuestal, si es 1 va a egresos, si es 2 a planeación.
    protected $mod_Vobo; //
    protected $tem_carga; //Numero de carga masiva a partir de la que se generó el folio de modificación
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /*     * ****************************************************

    METODOS GET

     * **************************************************** */

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

    public function getMod_ue_id()
    {
        return $this->mod_ue_id->getVal();
    }

    public function getOop()
    {
        return $this->enc_oop->getVal();
    }

    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }

    public function getEmo_id()
    {
        return $this->emo_id->getVal();
    }

    public function getTipoMovimiento()
    {
        return $this->mod_tipoMovimiento->getVal();
    }

    public function getClave()
    {
        return $this->mod_clave->getVal();
    }

    public function getConcepto()
    {
        return $this->mod_concepto->getVal();
    }

    public function getFechaFolio()
    {
        return $this->mod_fechaFolio->getVal();
    }

    public function getOficioSefin()
    {
        return $this->mod_oficioSefin->getVal();
    }

    public function getOficioSolicitudSefin()
    {
        return $this->mod_oficioSolicitudSefin->getVal();
    }

    public function getOficioArchivo()
    {
        return $this->mod_oficioArchivo->getVal();
    }

    public function getOperacion()
    {
        return $this->mod_operacion->getVal();
    }

    public function getFechaAutorizacion()
    {
        return $this->mod_fechaAutorizacion->getVal();
    }

    public function getMsc_id_autorizacion()
    {
        return $this->msc_id_autorizacion->getVal();
    }

    public function getOrigen()
    {
        return $this->mod_origen->getVal();
    }

    public function getReporteInterno()
    {
        return $this->mod_reporteInterno->getVal();
    }

    public function getSubsecretaria()
    {
        return $this->mod_Subsecretaria->getVal();
    }

    public function getVobo()
    {
        return $this->mod_Vobo->getVal();
    }

    public function getCarga()
    {
        return $this->tem_carga->getVal();
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

    public function setMod_ue_id($val)
    {
        $this->mod_ue_id->setVal($val);
    }

    public function setOop($val)
    {
        $this->enc_oop->setVal($val);
    }

    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }

    public function setEmo_id($val)
    {
        $this->emo_id->setVal($val);
    }

    public function setTipoMovimiento($val)
    {
        $this->mod_tipoMovimiento->setVal($val);
    }

    public function setClave($val)
    {
        $this->mod_clave->setVal($val);
    }

    public function setConcepto($val)
    {
        $this->mod_concepto->setVal($val);
    }

    public function setFechaFolio($val)
    {
        $this->mod_fechaFolio->setVal($val);
    }

    public function setOficioSefin($val)
    {
        $this->mod_oficioSefin->setVal($val);
    }

    public function setOficioSolicitudSefin($val)
    {
        $this->mod_oficioSolicitudSefin->setVal($val);
    }

    public function setOficioArchivo($val)
    {
        $this->mod_oficioArchivo->setVal($val);
    }

    public function setOperacion($val)
    {
        $this->mod_operacion->setVal($val);
    }

    public function setFechaAutorizacion($val)
    {
        $this->mod_fechaAutorizacion->setVal($val);
    }

    public function setMsc_id_autorizacion($val)
    {
        $this->msc_id_autorizacion->setVal($val);
    }

    public function setOrigen($val)
    {
        $this->mod_origen->setVal($val);
    }

    public function setReporteInterno($val)
    {
        $this->mod_reporteInterno->setVal($val);
    }

    public function setSubsecretaria($val)
    {
        $this->mod_Subsecretaria->setVal($val);
    }

    public function setVobo($val)
    {
        $this->mod_Vobo->setVal($val);
    }

    public function setCarga($val)
    {
        $this->tem_carga->setVal($val);
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

    public function staMod_ue_id($val)
    {
        $this->mod_ue_id->staVal($val);
    }

    public function staOop($val)
    {
        $this->enc_oop->staVal($val);
    }

    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }

    public function staEmo_id($val)
    {
        $this->emo_id->staVal($val);
    }

    public function staTipoMovimiento($val)
    {
        $this->mod_tipoMovimiento->staVal($val);
    }

    public function staClave($val)
    {
        $this->mod_clave->staVal($val);
    }

    public function staConcepto($val)
    {
        $this->mod_concepto->staVal($val);
    }

    public function staFechaFolio($val)
    {
        $this->mod_fechaFolio->staVal($val);
    }

    public function staOficioSefin($val)
    {
        $this->mod_oficioSefin->staVal($val);
    }

    public function staOficioSolicitudSefin($val)
    {
        $this->mod_oficioSolicitudSefin->staVal($val);
    }

    public function staOficioArchivo($val)
    {
        $this->mod_oficioArchivo->staVal($val);
    }

    public function staOperacion($val)
    {
        $this->mod_operacion->staVal($val);
    }

    public function staFechaAutorizacion($val)
    {
        $this->mod_fechaAutorizacion->staVal($val);
    }

    public function staMsc_id_autorizacion($val)
    {
        $this->msc_id_autorizacion->staVal($val);
    }

    public function staOrigen($val)
    {
        $this->mod_origen->staVal($val);
    }

    public function staReporteInterno($val)
    {
        $this->mod_reporteInterno->staVal($val);
    }

    public function staSubsecretaria($val)
    {
        $this->mod_Subsecretaria->staVal($val);
    }

    public function staVobo($val)
    {
        $this->mod_Vobo->staVal($val);
    }

    public function staCarga($val)
    {
        $this->tem_carga->staVal($val);
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

        parent::__construct(['tabla' => 'scp_MaestroModificacion']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->omo_id = new Atributo('cadena', 'NO', 'PRI', 'omo_id');
        $this->mod_gpo_id = new Atributo('numero', 'NO', 'PRI', 'mod_gpo_id');
        $this->mod_ur_id = new Atributo('numero', 'NO', 'PRI', 'mod_ur_id');
        $this->mod_id = new Atributo('numero', 'NO', 'PRI', 'mod_id');
        $this->mod_ue_id = new Atributo('numero', 'YES', '', 'mod_ue_id');
        $this->enc_oop = new Atributo('numero', 'NO', 'MUL', 'enc_oop');
        $this->ecf_id = new Atributo('numero', 'NO', 'MUL', 'ecf_id');
        $this->emo_id = new Atributo('numero', 'NO', 'MUL', 'emo_id');
        $this->mod_tipoMovimiento = new Atributo(
            'cadena',
            'NO',
            '',
            'mod_tipoMovimiento'
        );
        $this->mod_clave = new Atributo('cadena', 'NO', '', 'mod_clave');
        $this->mod_concepto = new Atributo('cadena', 'NO', '', 'mod_concepto');
        $this->mod_fechaFolio = new Atributo('fecha', 'NO', '', 'mod_fechaFolio');
        $this->mod_oficioSefin = new Atributo('cadena', 'NO', '', 'mod_oficioSefin');
        $this->mod_oficioSolicitudSefin = new Atributo(
            'cadena',
            'NO',
            '',
            'mod_oficioSolicitudSefin'
        );
        $this->mod_oficioArchivo = new Atributo(
            'cadena',
            'NO',
            '',
            'mod_oficioArchivo'
        );
        $this->mod_operacion = new Atributo('numero', 'YES', '', 'mod_operacion');
        $this->mod_fechaAutorizacion = new Atributo(
            'fecha',
            'YES',
            '',
            'mod_fechaAutorizacion'
        );
        $this->msc_id_autorizacion = new Atributo(
            'numero',
            'YES',
            '',
            'msc_id_autorizacion'
        );
        $this->mod_origen = new Atributo('cadena', 'NO', '', 'mod_origen');
        $this->mod_reporteInterno = new Atributo(
            'cadena',
            'NO',
            '',
            'mod_reporteInterno'
        );
        $this->mod_Subsecretaria = new Atributo(
            'numero',
            'NO',
            '',
            'mod_Subsecretaria'
        );
        $this->mod_Vobo = new Atributo('numero', 'NO', '', 'mod_Vobo');
        $this->tem_carga = new Atributo('cadena', 'YES', '', 'tem_carga');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0)
            :
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null;

        $omo_id = (!isset($prm->omo_id)) ? '' : $prm->omo_id;
        $gpo_id = (!isset($prm->mod_gpo_id)) ? '' : $prm->mod_gpo_id;
        $ur_id = (!isset($prm->mod_ur_id)) ? '' : $prm->mod_ur_id;
        $mod_id = (!isset($prm->mod_id)) ? '' : $prm->mod_id;

        if ($mod_id != '' && $omo_id != '' && $gpo_id != '' && $ur_id != '') {
            $this->setOmo_id($omo_id);
            $this->setMod_gpo_id($gpo_id);
            $this->setMod_ur_id($ur_id);
            $this->setMod_id($mod_id);

            if (!isset($prm->altafolio)) {
                $this->select();
            }
        }
    }
    /*     * ****************************************************

    FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $enc = new MaestroModificacion();
        if (isset($p->omo_id)) {
            $enc->setOmo_id($p->omo_id);
        }

        if (isset($p->mod_gpo_id)) {
            $enc->setMod_gpo_id($p->mod_gpo_id);
        }

        if (isset($p->mod_ur_id)) {
            $enc->setMod_ur_id($p->mod_ur_id);
        }

        if (isset($p->mod_id)) {
            $enc->setMod_id($p->mod_id);
        }

        if (isset($p->mod_ue_id)) {
            $enc->setMod_ue_id($p->mod_ue_id);
        }

        if (isset($p->oop)) {
            $enc->setOop($p->oop);
        }

        if (isset($p->ecf_id)) {
            $enc->setEcf_id($p->ecf_id);
        }

        if (isset($p->emo_id)) {
            $enc->setEmo_id($p->emo_id);
        }

        if (isset($p->tipoMovimiento)) {
            $enc->setTipoMovimiento($p->tipoMovimiento);
        }

        if (isset($p->clave)) {
            $enc->setClave($p->clave);
        }

        if (isset($p->concepto)) {
            $enc->setConcepto($p->concepto);
        }

        if (isset($p->fechaFolio)) {
            $enc->setFechaFolio($p->fechaFolio);
        }

        if (isset($p->oficioSefin)) {
            $enc->setOficioSefin($p->oficioSefin);
        }

        if (isset($p->oficioSolicitudSefin)) {
            $enc->setOficioSolicitudSefin($p->oficioSolicitudSefin);
        }

        if (isset($p->oficioArchivo)) {
            $enc->setOficioArchivo($p->oficioArchivo);
        }

        if (isset($p->operacion)) {
            $enc->setOperacion($p->operacion);
        }

        if (isset($p->fechaAutorizacion)) {
            $enc->setFechaAutorizacion($p->fechaAutorizacion);
        }

        if (isset($p->msc_id_autorizacion)) {
            $enc->setMsc_id_autorizacion($p->msc_id_autorizacion);
        }

        if (isset($p->origen)) {
            $enc->setOrigen($p->origen);
        }

        if (isset($p->reporteInterno)) {
            $enc->setReporteInterno($p->reporteInterno);
        }

        if (isset($p->Subsecretaria)) {
            $enc->setSubsecretaria($p->Subsecretaria);
        }

        if (isset($p->Vobo)) {
            $enc->setVobo($p->Vobo);
        }

        if (isset($p->carga)) {
            $enc->setCarga($p->carga);
        }

        if (isset($p->usr_id)) {
            $enc->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1')
            ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $usr_id = (isset($_SESSION[_S]['usr_id']) && $_SESSION[_S]['usr_id'] != '')
            ? $_SESSION[_S]['usr_id'] : '1';

        switch ($p->abc) {
            case 'a':
                $enc->conectar();
                $enc->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id =?,@bit_observacion=?;');
                $parametros = [
                    $usr_id,
                    $p->observacion,
                ];
                $enc->consultar($parametros);
                $enc->insert(false, true);
                $enc->setSql('SELECT @mod_id as mod_id;');
                $enc->consultar();
                $enc->desconectar();
                $mod_id = $enc->extraer();
                return $mod_id['mod_id'];
                break;
            case 'b':
                $enc->conectar();
                $enc->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id =?,@bit_observacion=?;');
                $parametros = [
                    $usr_id,
                    $p->observacion,
                ];
                $enc->consultar($parametros);
                $enc->delete(false);
                break;
            case 'c':
                $enc->conectar();
                $enc->setSql('SET @usr_ip = sinpres.fn_get_ip(), @usr_id =?,@bit_observacion= ?;');
                $parametros = [
                    $usr_id,
                    $p->observacion,
                ];
                $enc->consultar($parametros);
                $enc->update(false);
                $enc->desconectar();
                break;
        }
    }
}
