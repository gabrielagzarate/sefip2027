<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class Compromiso extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
    ******************************************************/

    protected $gpo_id; //Identificador del Grupo de la Unidad Responsable
    protected $ur_id; //Identificador de la Unidad Responsable
    protected $cmp_id; //Identificador unico del comprpmiso
    protected $ue_id; //Identificador de la Unidad Ejecutora, Depende de presupuesto
    protected $epg_id; //Identificador del encuadre programatico. Depende de presupuesto.
    protected $pg_id; //Identificador de la partida Generica.. Depende de presupuesto.
    protected $pe_id; //
    protected $ecf_id; //
    protected $tcp_id; //
    protected $tcl_id; //
    protected $stc_id; //
    protected $tno_id; //
    protected $mno_id; //
    protected $smn_id; //
    protected $rfc_id; //
    protected $cmp_fechaInicio; //
    protected $cmp_fechaFin; //
    protected $cmp_numOrden; //
    protected $cmp_fechaOrden; //
    protected $cmp_concepto; //Concepto del Compromiso
    protected $cmp_tipoMovimiento; //identifica si el movimiento + normal/aumenta- disminuye
    protected $cmp_importe; //
    protected $iva_id; //
    protected $ipu_numeroContrato; //
    protected $ipu_id; //
    protected $tpd_id; //
    protected $cmp_otroDocumento; //Guarda el tipo de documento en caso de que sea "Otro"
    protected $ecm_id; //Estado del compromiso
    protected $cmp_fechaAutorizado; //Fecha de autorizacion del compromiso(comprometido)
    protected $cmp_fechaCancelado; //Fecha de cancepacion del compromiso(descomprometido)
    protected $cmp_tipoCompromiso; //Identifica si el compromiso esN - compromiso NormalA - compromiso que ajuste
    protected $cmp_cmp_id; //numero de compromiso que modifica
    protected $tem_carga; //id de carga temporal
    protected $cmp_temporal; //id temporal para vincular tablas y poder actualizar usando UPDATE
    protected $usr_id; //

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getCmp_id()
    {
        return $this->cmp_id->getVal();
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
    public function getTcl_id()
    {
        return $this->tcl_id->getVal();
    }
    public function getStc_id()
    {
        return $this->stc_id->getVal();
    }
    public function getTno_id()
    {
        return $this->tno_id->getVal();
    }
    public function getMno_id()
    {
        return $this->mno_id->getVal();
    }
    public function getSmn_id()
    {
        return $this->smn_id->getVal();
    }
    public function getRfc_id()
    {
        return $this->rfc_id->getVal();
    }
    public function getFechaInicio()
    {
        return $this->cmp_fechaInicio->getVal();
    }
    public function getFechaFin()
    {
        return $this->cmp_fechaFin->getVal();
    }
    public function getNumOrden()
    {
        return $this->cmp_numOrden->getVal();
    }
    public function getFechaOrden()
    {
        return $this->cmp_fechaOrden->getVal();
    }
    public function getConcepto()
    {
        return $this->cmp_concepto->getVal();
    }
    public function getTipoMovimiento()
    {
        return $this->cmp_tipoMovimiento->getVal();
    }
    public function getImporte()
    {
        return $this->cmp_importe->getVal();
    }
    public function getIva_id()
    {
        return $this->iva_id->getVal();
    }
    public function getNumeroContrato()
    {
        return $this->ipu_numeroContrato->getVal();
    }
    public function getIpu_id()
    {
        return $this->ipu_id->getVal();
    }
    public function getTpd_id()
    {
        return $this->tpd_id->getVal();
    }
    public function getOtroDocumento()
    {
        return $this->cmp_otroDocumento->getVal();
    }
    public function getEcm_id()
    {
        return $this->ecm_id->getVal();
    }
    public function getFechaAutorizado()
    {
        return $this->cmp_fechaAutorizado->getVal();
    }
    public function getFechaCancelado()
    {
        return $this->cmp_fechaCancelado->getVal();
    }
    public function getTipoCompromiso()
    {
        return $this->cmp_tipoCompromiso->getVal();
    }
    public function getCmp_cmp_id()
    {
        return $this->cmp_cmp_id->getVal();
    }
    public function getCarga()
    {
        return $this->tem_carga->getVal();
    }
    public function getTemporal()
    {
        return $this->cmp_temporal->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setCmp_id($val)
    {
        $this->cmp_id->setVal($val);
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
    public function setTcl_id($val)
    {
        $this->tcl_id->setVal($val);
    }
    public function setStc_id($val)
    {
        $this->stc_id->setVal($val);
    }
    public function setTno_id($val)
    {
        $this->tno_id->setVal($val);
    }
    public function setMno_id($val)
    {
        $this->mno_id->setVal($val);
    }
    public function setSmn_id($val)
    {
        $this->smn_id->setVal($val);
    }
    public function setRfc_id($val)
    {
        $this->rfc_id->setVal($val);
    }
    public function setFechaInicio($val)
    {
        $this->cmp_fechaInicio->setVal($val);
    }
    public function setFechaFin($val)
    {
        $this->cmp_fechaFin->setVal($val);
    }
    public function setNumOrden($val)
    {
        $this->cmp_numOrden->setVal($val);
    }
    public function setFechaOrden($val)
    {
        $this->cmp_fechaOrden->setVal($val);
    }
    public function setConcepto($val)
    {
        $this->cmp_concepto->setVal($val);
    }
    public function setTipoMovimiento($val)
    {
        $this->cmp_tipoMovimiento->setVal($val);
    }
    public function setImporte($val)
    {
        $this->cmp_importe->setVal($val);
    }
    public function setIva_id($val)
    {
        $this->iva_id->setVal($val);
    }
    public function setNumeroContrato($val)
    {
        $this->ipu_numeroContrato->setVal($val);
    }
    public function setIpu_id($val)
    {
        $this->ipu_id->setVal($val);
    }
    public function setTpd_id($val)
    {
        $this->tpd_id->setVal($val);
    }
    public function setOtroDocumento($val)
    {
        $this->cmp_otroDocumento->setVal($val);
    }
    public function setEcm_id($val)
    {
        $this->ecm_id->setVal($val);
    }
    public function setFechaAutorizado($val)
    {
        $this->cmp_fechaAutorizado->setVal($val);
    }
    public function setFechaCancelado($val)
    {
        $this->cmp_fechaCancelado->setVal($val);
    }
    public function setTipoCompromiso($val)
    {
        $this->cmp_tipoCompromiso->setVal($val);
    }
    public function setCmp_cmp_id($val)
    {
        $this->cmp_cmp_id->setVal($val);
    }
    public function setCarga($val)
    {
        $this->tem_carga->setVal($val);
    }
    public function setTemporal($val)
    {
        $this->cmp_temporal->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staCmp_id($val)
    {
        $this->cmp_id->staVal($val);
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
    public function staTcl_id($val)
    {
        $this->tcl_id->staVal($val);
    }
    public function staStc_id($val)
    {
        $this->stc_id->staVal($val);
    }
    public function staTno_id($val)
    {
        $this->tno_id->staVal($val);
    }
    public function staMno_id($val)
    {
        $this->mno_id->staVal($val);
    }
    public function staSmn_id($val)
    {
        $this->smn_id->staVal($val);
    }
    public function staRfc_id($val)
    {
        $this->rfc_id->staVal($val);
    }
    public function staFechaInicio($val)
    {
        $this->cmp_fechaInicio->staVal($val);
    }
    public function staFechaFin($val)
    {
        $this->cmp_fechaFin->staVal($val);
    }
    public function staNumOrden($val)
    {
        $this->cmp_numOrden->staVal($val);
    }
    public function staFechaOrden($val)
    {
        $this->cmp_fechaOrden->staVal($val);
    }
    public function staConcepto($val)
    {
        $this->cmp_concepto->staVal($val);
    }
    public function staTipoMovimiento($val)
    {
        $this->cmp_tipoMovimiento->staVal($val);
    }
    public function staImporte($val)
    {
        $this->cmp_importe->staVal($val);
    }
    public function staIva_id($val)
    {
        $this->iva_id->staVal($val);
    }
    public function staNumeroContrato($val)
    {
        $this->ipu_numeroContrato->staVal($val);
    }
    public function staIpu_id($val)
    {
        $this->ipu_id->staVal($val);
    }
    public function staTpd_id($val)
    {
        $this->tpd_id->staVal($val);
    }
    public function staOtroDocumento($val)
    {
        $this->cmp_otroDocumento->staVal($val);
    }
    public function staEcm_id($val)
    {
        $this->ecm_id->staVal($val);
    }
    public function staFechaAutorizado($val)
    {
        $this->cmp_fechaAutorizado->staVal($val);
    }
    public function staFechaCancelado($val)
    {
        $this->cmp_fechaCancelado->staVal($val);
    }
    public function staTipoCompromiso($val)
    {
        $this->cmp_tipoCompromiso->staVal($val);
    }
    public function staCmp_cmp_id($val)
    {
        $this->cmp_cmp_id->staVal($val);
    }
    public function staCarga($val)
    {
        $this->tem_carga->staVal($val);
    }
    public function staTemporal($val)
    {
        $this->cmp_temporal->staVal($val);
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
        parent::__construct(['tabla' => 'scp_Compromiso']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->cmp_id = new Atributo('numero', 'NO', 'PRI', 'cmp_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', '', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', '', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->tcp_id = new Atributo('cadena', 'NO', '', 'tcp_id');
        $this->tcl_id = new Atributo('cadena', 'NO', 'MUL', 'tcl_id');
        $this->stc_id = new Atributo('numero', 'NO', '', 'stc_id');
        $this->tno_id = new Atributo('numero', 'YES', 'MUL', 'tno_id');
        $this->mno_id = new Atributo('numero', 'NO', 'MUL', 'mno_id');
        $this->smn_id = new Atributo('numero', 'NO', '', 'smn_id');
        $this->rfc_id = new Atributo('cadena', 'NO', 'MUL', 'rfc_id');
        $this->cmp_fechaInicio = new Atributo('fecha', 'YES', '', 'cmp_fechaInicio');
        $this->cmp_fechaFin = new Atributo('fecha', 'YES', '', 'cmp_fechaFin');
        $this->cmp_numOrden = new Atributo('cadena', 'NO', '', 'cmp_numOrden');
        $this->cmp_fechaOrden = new Atributo('fecha', 'YES', '', 'cmp_fechaOrden');
        $this->cmp_concepto = new Atributo('cadena', 'NO', '', 'cmp_concepto');
        $this->cmp_tipoMovimiento = new Atributo('cadena', 'NO', '', 'cmp_tipoMovimiento');
        $this->cmp_importe = new Atributo('numero', 'NO', '', 'cmp_importe');
        $this->iva_id = new Atributo('numero', 'NO', 'MUL', 'iva_id');
        $this->ipu_numeroContrato = new Atributo('cadena', 'YES', '', 'ipu_numeroContrato');
        $this->ipu_id = new Atributo('numero', 'YES', 'MUL', 'ipu_id');
        $this->tpd_id = new Atributo('numero', 'NO', 'MUL', 'tpd_id');
        $this->cmp_otroDocumento = new Atributo('cadena', 'NO', '', 'cmp_otroDocumento');
        $this->ecm_id = new Atributo('numero', 'NO', 'MUL', 'ecm_id');
        $this->cmp_fechaAutorizado = new Atributo('fecha', 'YES', '', 'cmp_fechaAutorizado');
        $this->cmp_fechaCancelado = new Atributo('fecha', 'YES', '', 'cmp_fechaCancelado');
        $this->cmp_tipoCompromiso = new Atributo('numero', 'NO', '', 'cmp_tipoCompromiso');
        $this->cmp_cmp_id = new Atributo('numero', 'YES', '', 'cmp_cmp_id');
        $this->tem_carga = new Atributo('cadena', 'NO', '', 'tem_carga');
        $this->cmp_temporal = new Atributo('numero', 'NO', '', 'cmp_temporal');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public function select($servidor = '')
    {
        if ($this->getGpo_id() == '' && $this->getUr_id() == '' && $this->getCmp_id() != '') {
            $consulta = new AccesoDatos();
            $consulta->conectar();
            $consulta->setSql('
                SELECT gpo_id, ur_id, cmp_id
                FROM scp_Compromiso
                WHERE cmp_id = ?
            ');
            $params = [$this->getCmp_id()];
            $consulta->consultar($params);
            $consulta->desconectar();
            if ($dato = $consulta->extraer()) {
                $this->setGpo_id($dato['gpo_id']);
                $this->setUr_id($dato['ur_id']);
                $this->setCmp_id($dato['cmp_id']);
            }
        } elseif ($this->getGpo_id() == '' && $this->getUr_id() == '' && $this->getCmp_id() == '' && $this->getRfc_id() != '' && $this->getNumOrden() != '') {
            $consulta = new AccesoDatos();
            $consulta->conectar();
            $consulta->setSql('
                SELECT gpo_id, ur_id, cmp_id
                FROM scp_Compromiso
				WHERE rfc_id = ?
				AND cmp_numOrden = ?
            ');
            $params = [$this->getRfc_id(), $this->getNumOrden()];
            $consulta->consultar($params);
            $consulta->desconectar();
            if ($dato = $consulta->extraer()) {
                $this->setGpo_id($dato['gpo_id']);
                $this->setUr_id($dato['ur_id']);
                $this->setCmp_id($dato['cmp_id']);
            }
        }
        parent::select();
    }

    public static function abc($p)
    {
        $cmp = new Compromiso();
        if (isset($p->gpo_id)) {
            $cmp->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $cmp->setUr_id($p->ur_id);
        }
        if (isset($p->cmp_id)) {
            $cmp->setCmp_id($p->cmp_id);
        }
        if (isset($p->ue_id)) {
            $cmp->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $cmp->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $cmp->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $cmp->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $cmp->setEcf_id($p->ecf_id);
        }
        if (isset($p->tcp_id)) {
            $cmp->setTcp_id($p->tcp_id);
        }
        if (isset($p->tcl_id)) {
            $cmp->setTcl_id($p->tcl_id);
        }
        if (isset($p->stc_id)) {
            $cmp->setStc_id($p->stc_id);
        }
        if (isset($p->tno_id)) {
            $cmp->setTno_id($p->tno_id);
        }
        if (isset($p->mno_id)) {
            $cmp->setMno_id($p->mno_id);
        }
        if (isset($p->smn_id)) {
            $cmp->setSmn_id($p->smn_id);
        }
        if (isset($p->rfc_id)) {
            $cmp->setRfc_id($p->rfc_id);
        }
        if (isset($p->fechaInicio)) {
            $cmp->setFechaInicio($p->fechaInicio);
        }
        if (isset($p->fechaFin)) {
            $cmp->setFechaFin($p->fechaFin);
        }
        if (isset($p->numOrden)) {
            $cmp->setNumOrden($p->numOrden);
        }
        if (isset($p->fechaOrden)) {
            $cmp->setFechaOrden($p->fechaOrden);
        }
        if (isset($p->concepto)) {
            $cmp->setConcepto($p->concepto);
        }
        if (isset($p->tipoMovimiento)) {
            $cmp->setTipoMovimiento($p->tipoMovimiento);
        }
        if (isset($p->importe)) {
            $cmp->setImporte($p->importe);
        }
        if (isset($p->iva_id) && $p->iva_id != '') {
            $cmp->setIva_id($p->iva_id);
        } else {
            $cmp->setIva_id('0');
        }
        if (isset($p->numeroContrato)) {
            $cmp->setNumeroContrato($p->numeroContrato);
        }
        if (isset($p->ipu_id)) {
            $cmp->setIpu_id($p->ipu_id);
        }
        if (isset($p->tpd_id)) {
            $cmp->setTpd_id($p->tpd_id);
        }
        if (isset($p->otroDocumento)) {
            $cmp->setOtroDocumento($p->otroDocumento);
        }
        if (isset($p->ecm_id)) {
            $cmp->setEcm_id($p->ecm_id);
        }
        if (isset($p->fechaAutorizado)) {
            $cmp->setFechaAutorizado($p->fechaAutorizado);
        }
        if (isset($p->fechaCancelado)) {
            $cmp->setFechaCancelado($p->fechaCancelado);
        }
        if (isset($p->tipoCompromiso)) {
            $cmp->setTipoCompromiso($p->tipoCompromiso);
        }
        if (isset($p->cmp_cmp_id)) {
            $cmp->setCmp_cmp_id($p->cmp_cmp_id);
        }
        if (isset($p->carga)) {
            $cmp->setCarga($p->carga);
        }
        if (isset($p->Temporal)) {
            $cmp->setTemporal($p->Temporal);
        }
        if (isset($p->usr_id)) {
            $cmp->setUsr_id($p->usr_id);
        }

        $ip = $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $params = [$_SESSION[_S]['usr_id'], $ip];

        $cmp->conectar();
        $sql = '
            SET 
            @usr_id = ?, 
            @bit_observacion = "INSERTA COMPROMISO DESDE CAPTURA MANUAL",
            @usr_ip = INET_ATON(?);
        ';
        $cmp->setSql($sql);
        $cmp->consultar($params);

        switch ($p->abc) {
            case 'a':
                $cmp->insert(false);
                return $cmp->getUltimoId();
                $cmp->desconectar();
                break;
            case 'b':
                $cmp->delete();
                break;
            case 'c':
                $cmp->conectar();
                $cmp->setSql('
                    SET 
                    @usr_id = ?,
                    @bit_observacion = "COMPROMISO ACTUALIZADO",
                    @usr_ip = INET_ATON(?);
                ');
                $cmp->consultar($params);
                $cmp->update(false);
                break;
        }
    }
}
