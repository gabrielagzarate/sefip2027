<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class BitacoraMaestro extends Entidad
{
    /*     * ****************************************************
        ATRIBUTOS DE LA CLASE
     * **************************************************** */
    protected $mod;
    protected $omo_id;
    protected $mod_gpo_id;
    protected $mod_ur_id;
    protected $mod_id;
    protected $mod_ue_id;
    protected $emo_id;
    protected $bit_observacion;
    protected $enc_oop;
    protected $ecf_id;
    protected $mod_tipoMovimiento;
    protected $mod_clave;
    protected $mod_concepto;
    protected $mod_fechaFolio;
    protected $mod_oficioSefin;
    protected $mod_oficioSolicitudSefin;
    protected $mod_oficioArchivo;
    protected $mod_operacion;
    protected $mod_fechaAutorizacion;
    protected $msc_id_autorizacion;
    protected $mod_origen;
    protected $mod_reporteInterno;
    protected $mod_Subsecretaria;
    protected $mod_Vobo;
    protected $tem_carga;
    protected $usr_ip;

    /*     * ****************************************************
        METODOS GET
     * **************************************************** */

    public function getMod()
    {
        return $this->mod->getVal();
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

    public function getMod_ue_id()
    {
        return $this->mod_ue_id->getVal();
    }

    public function getEmo_id()
    {
        return $this->emo_id->getVal();
    }

    public function getObservacion()
    {
        return $this->bit_observacion->getVal();
    }

    public function getOop()
    {
        return $this->enc_oop->getVal();
    }

    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
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

    public function setMod($val)
    {
        $this->mod->setVal($val);
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

    public function setMod_ue_id($val)
    {
        $this->mod_ue_id->setVal($val);
    }

    public function setEmo_id($val)
    {
        $this->emo_id->setVal($val);
    }

    public function setObservacion($val)
    {
        $this->bit_observacion->setVal($val);
    }

    public function setOop($val)
    {
        $this->enc_oop->setVal($val);
    }

    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
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

    public function staMod($val)
    {
        $this->mod->staVal($val);
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

    public function staMod_ue_id($val)
    {
        $this->mod_ue_id->staVal($val);
    }

    public function staEmo_id($val)
    {
        $this->emo_id->staVal($val);
    }

    public function staObservacion($val)
    {
        $this->bit_observacion->staVal($val);
    }

    public function staOop($val)
    {
        $this->enc_oop->staVal($val);
    }

    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
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

        parent::__construct(['tabla' => '' . BD_BITACORA . '.bit_MaestroModificacion']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->mod = new Atributo('numero', 'NO', 'PRI', 'bit_mod');
        $this->omo_id = new Atributo('cadena', 'NO', 'MUL', 'omo_id');
        $this->mod_gpo_id = new Atributo('numero', 'NO', '', 'mod_gpo_id');
        $this->mod_ur_id = new Atributo('numero', 'NO', '', 'mod_ur_id');
        $this->mod_id = new Atributo('numero', 'NO', '', 'mod_id');
        $this->mod_ue_id = new Atributo('numero', 'YES', '', 'mod_ue_id');
        $this->emo_id = new Atributo('numero', 'NO', '', 'emo_id');
        $this->bit_observacion = new Atributo('cadena', 'NO', '', 'bit_observacion');
        $this->enc_oop = new Atributo('numero', 'NO', '', 'enc_oop');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->mod_tipoMovimiento = new Atributo(
            'cadena',
            'YES',
            '',
            'mod_tipoMovimiento'
        );
        $this->mod_clave = new Atributo('cadena', 'NO', '', 'mod_clave');
        $this->mod_concepto = new Atributo('cadena', 'NO', '', 'mod_concepto');
        $this->mod_fechaFolio = new Atributo('fecha', 'NO', '', 'mod_fechaFolio');
        $this->mod_oficioSefin = new Atributo('cadena', 'YES', '', 'mod_oficioSefin');
        $this->mod_oficioSolicitudSefin = new Atributo(
            'cadena',
            'YES',
            '',
            'mod_oficioSolicitudSefin'
        );
        $this->mod_oficioArchivo = new Atributo(
            'cadena',
            'YES',
            '',
            'mod_oficioArchivo'
        );
        $this->mod_operacion = new Atributo('numero', 'NO', '', 'mod_operacion');
        $this->mod_fechaAutorizacion = new Atributo(
            'fecha',
            'YES',
            '',
            'mod_fechaAutorizacion'
        );
        $this->msc_id_autorizacion = new Atributo(
            'numero',
            'NO',
            '',
            'msc_id_autorizacion'
        );
        $this->mod_origen = new Atributo('cadena', 'NO', '', 'mod_origen');
        $this->mod_reporteInterno = new Atributo(
            'cadena',
            'YES',
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
        $this->usr_ip = new Atributo('numero', 'NO', '', 'usr_ip');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }
    /*     * ****************************************************
        FUNCIONES ESTÁTICAS
     * **************************************************** */

    public static function abc($p)
    {
        $bit = new BitacoraMaestro();
        if (isset($p->mod)) {
            $bit->setMod($p->mod);
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
        if (isset($p->mod_ue_id)) {
            $bit->setMod_ue_id($p->mod_ue_id);
        }
        if (isset($p->emo_id)) {
            $bit->setEmo_id($p->emo_id);
        }
        if (isset($p->bit_observacion)) {
            $bit->setObservacion($p->bit_observacion);
        }
        if (isset($p->enc_oop)) {
            $bit->setOop($p->enc_oop);
        }
        if (isset($p->ecf_id)) {
            $bit->setEcf_id($p->ecf_id);
        }
        if (isset($p->mod_tipoMovimiento)) {
            $bit->setTipoMovimiento($p->mod_tipoMovimiento);
        }
        if (isset($p->mod_clave)) {
            $bit->setClave($p->mod_clave);
        }
        if (isset($p->mod_concepto)) {
            $bit->setConcepto($p->mod_concepto);
        }
        if (isset($p->mod_fechaFolio)) {
            $bit->setFechaFolio($p->mod_fechaFolio);
        }
        if (isset($p->mod_oficioSefin)) {
            $bit->setOficioSefin($p->mod_oficioSefin);
        }
        if (isset($p->mod_oficioSolicitudSefin)) {
            $bit->setOficioSolicitudSefin($p->mod_oficioSolicitudSefin);
        }
        if (isset($p->mod_oficioArchivo)) {
            $bit->setOficioArchivo($p->mod_oficioArchivo);
        }
        if (isset($p->mod_operacion)) {
            $bit->setOperacion($p->mod_operacion);
        }
        if (isset($p->mod_fechaAutorizacion)) {
            $bit->setFechaAutorizacion($p->mod_fechaAutorizacion);
        }
        if (isset($p->msc_id_autorizacion)) {
            $bit->setMsc_id_autorizacion($p->msc_id_autorizacion);
        }
        if (isset($p->mod_origen)) {
            $bit->setOrigen($p->mod_origen);
        }
        if (isset($p->mod_reporteInterno)) {
            $bit->setReporteInterno($p->mod_reporteInterno);
        }
        if (isset($p->mod_Subsecretaria)) {
            $bit->setSubsecretaria($p->mod_Subsecretaria);
        }
        if (isset($p->mod_Vobo)) {
            $bit->setVobo($p->mod_Vobo);
        }
        if (isset($p->tem_carga)) {
            $bit->setCarga($p->tem_carga);
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
