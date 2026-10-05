<?php

require_once dirname(__FILE__) . '/../Entidad.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';

class MaestroRectificacion extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $gpo_id;//cat_Grupo - Llave foranea Clave identificadora del Grupo
    protected $ur_id;//cat_UnidadResponsable - Llave foranea Clave identificadora de la UR
    protected $rct_id;//Clave identificadora del numero de rectificacion
    protected $clc_id;//scp_Clc - Llave foranea
    protected $ue_id;//
    protected $rct_justificacion;//Concepto de rectificacion de la clc
    protected $rct_fechaComprobante;//Fecha de la ficha de deposito o del memo
    protected $rct_numeroComprobante;//Si es de reintegro fecha de la ficha de deposito si es sin reintegro memo
    protected $rct_importe;//rct_importe a reintegrar
    protected $rct_reintegro;//1 reintegro  2 sin reintegro
    protected $rct_fechaElaboracion;//Fecha de elaboracion de la rectificacion
    protected $rct_fechaAutorizacion;//Fecha de autorizacion de la clc
    protected $ecl_id;//Estado de la rectificacion
    protected $rct_gpo;//Grupo de la UR que hizo la rectificacion
    protected $rct_ur;//Ur que realiza la rectificacion
    protected $rct_federal; // Si la rectificacion es rct_federal
    protected $usr_id;//

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
    public function getRct_id()
    {
        return $this->rct_id->getVal();
    }
    public function getClc_id()
    {
        return $this->clc_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getJustificacion()
    {
        return $this->rct_justificacion->getVal();
    }
    public function getFechaComprobante()
    {
        return $this->rct_fechaComprobante->getVal();
    }
    public function getNumeroComprobante()
    {
        return $this->rct_numeroComprobante->getVal();
    }
    public function getImporte()
    {
        return $this->rct_importe->getVal();
    }
    public function getReintegro()
    {
        return $this->rct_reintegro->getVal();
    }
    public function getFechaElaboracion()
    {
        return $this->rct_fechaElaboracion->getVal();
    }
    public function getFechaAutorizacion()
    {
        return $this->rct_fechaAutorizacion->getVal();
    }
    public function getEcl_id()
    {
        return $this->ecl_id->getVal();
    }
    public function getGpo()
    {
        return $this->rct_gpo->getVal();
    }
    public function getUr()
    {
        return $this->rct_ur->getVal();
    }
    public function getFederal()
    {
        return $this->rct_federal->getVal();
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
    public function setRct_id($val)
    {
        $this->rct_id->setVal($val);
    }
    public function setClc_id($val)
    {
        $this->clc_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setJustificacion($val)
    {
        $this->rct_justificacion->setVal($val);
    }
    public function setFechaComprobante($val)
    {
        $this->rct_fechaComprobante->setVal($val);
    }
    public function setNumeroComprobante($val)
    {
        $this->rct_numeroComprobante->setVal($val);
    }
    public function setImporte($val)
    {
        $this->rct_importe->setVal($val);
    }
    public function setReintegro($val)
    {
        $this->rct_reintegro->setVal($val);
    }
    public function setFechaElaboracion($val)
    {
        $this->rct_fechaElaboracion->setVal($val);
    }
    public function setFechaAutorizacion($val)
    {
        $this->rct_fechaAutorizacion->setVal($val);
    }
    public function setEcl_id($val)
    {
        $this->ecl_id->setVal($val);
    }
    public function setGpo($val)
    {
        $this->rct_gpo->setVal($val);
    }
    public function setUr($val)
    {
        $this->rct_ur->setVal($val);
    }
    public function setFederal($val)
    {
        $this->rct_federal->setVal($val);
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
    public function staRct_id($val)
    {
        $this->rct_id->staVal($val);
    }
    public function staClc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staJustificacion($val)
    {
        $this->rct_justificacion->staVal($val);
    }
    public function staFechaComprobante($val)
    {
        $this->rct_fechaComprobante->staVal($val);
    }
    public function staNumeroComprobante($val)
    {
        $this->rct_numeroComprobante->staVal($val);
    }
    public function staImporte($val)
    {
        $this->rct_importe->staVal($val);
    }
    public function staReintegro($val)
    {
        $this->rct_reintegro->staVal($val);
    }
    public function staFechaElaboracion($val)
    {
        $this->rct_fechaElaboracion->staVal($val);
    }
    public function staFechaAutorizacion($val)
    {
        $this->rct_fechaAutorizacion->staVal($val);
    }
    public function staEcl_id($val)
    {
        $this->ecl_id->staVal($val);
    }
    public function staGpo($val)
    {
        $this->rct_gpo->staVal($val);
    }
    public function staUr($val)
    {
        $this->rct_ur->staVal($val);
    }
    public function staFederal($val)
    {
        $this->rct_federal->staVal($val);
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

        parent::__construct(['tabla' => 'scp_MaestroRectificacion']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->rct_id = new Atributo('numero', 'NO', 'PRI', 'rct_id');
        $this->clc_id = new Atributo('numero', 'NO', '', 'clc_id');
        $this->ue_id = new Atributo('numero', 'YES', '', 'ue_id');
        $this->rct_justificacion = new Atributo('cadena', 'NO', '', 'rct_justificacion');
        $this->rct_fechaComprobante = new Atributo('fecha', 'YES', '', 'rct_fechaComprobante');
        $this->rct_numeroComprobante = new Atributo('cadena', 'NO', '', 'rct_numeroComprobante');
        $this->rct_importe = new Atributo('numero', 'NO', '', 'rct_importe');
        $this->rct_reintegro = new Atributo('numero', 'NO', '', 'rct_reintegro');
        $this->rct_fechaElaboracion = new Atributo('fecha', 'NO', '', 'rct_fechaElaboracion');
        $this->rct_fechaAutorizacion = new Atributo('fecha', 'YES', '', 'rct_fechaAutorizacion');
        $this->ecl_id = new Atributo('numero', 'NO', 'MUL', 'ecl_id');
        $this->rct_gpo = new Atributo('numero', 'YES', '', 'rct_gpo');
        $this->rct_ur = new Atributo('numero', 'YES', '', 'rct_ur');
        $this->rct_federal = new Atributo('numero', 'NO', '', 'rct_federal');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

    }

    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $rct = new MaestroRectificacion();
        if (isset($p->gpo_id)) {
            $rct->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $rct->setUr_id($p->ur_id);
        }
        if (isset($p->rct_id)) {
            $rct->setRct_id($p->rct_id);
        }
        if (isset($p->clc_id)) {
            $rct->setClc_id($p->clc_id);
        }
        if (isset($p->ue_id)) {
            $rct->setUe_id($p->ue_id);
        }
        if (isset($p->justificacion)) {
            $rct->setJustificacion($p->justificacion);
        }
        if (isset($p->fechaComprobante)) {
            $rct->setFechaComprobante($p->fechaComprobante);
        }
        if (isset($p->numeroComprobante)) {
            $rct->setNumeroComprobante($p->numeroComprobante);
        }
        if (isset($p->importe)) {
            $rct->setImporte($p->importe);
        }
        if (isset($p->reintegro)) {
            $rct->setReintegro($p->reintegro);
        }
        if (isset($p->fechaElaboracion)) {
            $rct->setFechaElaboracion($p->fechaElaboracion);
        }
        if (isset($p->fechaAutorizacion)) {
            $rct->setFechaAutorizacion($p->fechaAutorizacion);
        }
        if (isset($p->ecl_id)) {
            $rct->setEcl_id($p->ecl_id);
        }
        if (isset($p->gpo)) {
            $rct->setGpo($p->gpo);
        }
        if (isset($p->ur)) {
            $rct->setUr($p->ur);
        }
        if (isset($p->federal)) {
            $rct->setFederal($p->federal);
        }
        if (isset($p->usr_id)) {
            $rct->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $rct->setRct_id(1);

                if (!isset($p->importe)) {
                    $rct->setImporte('0.00');
                }
                if (!isset($p->numeroComprobante)) {
                    $rct->setNumeroComprobante('0');
                }
                if (isset($p->fechaComprobante) && $p->fechaComprobante == 'NULL') {
                    $rct->setFechaComprobante(null);
                }

                $rct->conectar();
                $rct->insert(false);
                $rct->setSql('SELECT @rct_id AS rct_id;');
                $rct->consultar();
                $rct_id = $rct->extraer();
                $rct->desconectar();
                return $rct_id['rct_id'];
                break;
            case 'b':
                $rct->delete();
                break;
            case 'c':
                if (isset($p->fechaComprobante) && $p->fechaComprobante == 'NULL') {
                    $rct->setFechaComprobante(null);
                }
                $rct->update();
                break;
        }
    }

    /* *********************************************************

        VALIDACIONES DE LAS RECTIFICACIONES

    *************************************************************/
    public static function validarRectificacion()
    {
        $prm = func_num_args() > 0 ? (is_object((func_get_arg(0))) ? func_get_arg(0) : $prm = is_array((func_get_arg(0))) ? (object)func_get_arg(0) : null) : null;

        $clc_id = (!isset($prm->clc_id)) ? '' : $prm->clc_id;
        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $rct_id = (!isset($prm->rct_id)) ? '' : $prm->rct_id;
        $amortizado = (!isset($prm->amortizado)) ? '' : $prm->amortizado;
        $rct_reintegro = (!isset($prm->rct_reintegro)) ? '' : $prm->rct_reintegro;
        $tareaclc = (!isset($prm->tareaclc)) ? '' : $prm->tareaclc;

        $amortizado = explode(',', $amortizado);
        $normal = in_array('NORMAL', $amortizado);
        $origen = in_array('ORIGEN', $amortizado);
        $pagada = in_array('PAGADORA', $amortizado);

        if ($normal == true && $origen == true) {
            $amortizado = 'ORIGEN';
        } elseif ($normal == true && $origen == false && $pagada == true) {
            $amortizado = 'PAGADORA';
        } elseif ($normal == true && $origen == false && $pagada == false) {
            $amortizado = 'NORMAL';
        } elseif ($normal == false && $origen == true && $pagada == false) {
            $amortizado = 'ORIGEN';
        } elseif ($normal == false && $origen == false && $pagada == true) {
            $amortizado = 'PAGADORA';
        }

        $np = $nc = $reintegro = '';
        switch ($rct_reintegro) {
            case 1:
                $reintegro .= ($reintegro == '') ?
                'and rct_reintegro= 1 ' : ' and rct_reintegro=1';
                $nc = ' and rcc_editado=1';
                $np = ' and rcp_editado=1';
                break;
            case 2:
                $reintegro .= ($reintegro == '') ?
                'and rct_reintegro= 2 ' : ' and rct_reintegro=2';
                $nc = ' and rcc_editado=1';
                $np = ' and rcp_editado=1';
                break;
            case 3:
                $reintegro .= ($reintegro == '') ?
                'and rct_reintegro= 3 ' : ' and rct_reintegro=3';
                $nc = ' and rcc_editado=3';
                $np = ' and rcp_editado=3';
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave as clave,ncp_id as numcontrol,
            ncp_percepcionImporteBruto as percepciones,0 retenciones,
                "P" as movimiento,"+" tipo_Movimiento
            FROM scp_RectificacionPresupuestal
            WHERE gpo_id = :gpo_id and ur_id = :ur_id and rct_id = :rct_id ' . $np . '
            UNION ALL
            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave as clave,ncc_id as numcontrol,
            ncc_percepcion as percepciones,ncc_retencion as retenciones,
                "C" as movimiento,ncc_tipoMovimiento as tipo_Movimiento
            FROM scp_RectificacionContable
            WHERE gpo_id = :gpo_id and ur_id = :ur_id and rct_id = :rct_id ' . $nc . ' 
        
        ');
        $params1 = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':rct_id' => $rct_id];
        $conexion->consultar($params1);
        $conexion->desconectar();
        $wd = $inC = $inP = '';

        while ($row = $conexion->extraer()) {
            if ($row['movimiento'] == 'P') {
                $inP .= $row['numcontrol'] . ',';
            } else {
                $inC .= $row['numcontrol'] . ',';
            }
        }

        $inP = trim($inP, ',');
        $inC = trim($inC, ',');
        if ($inP == '') {
            $inP = 0;
        }
        if ($inC == '') {
            $inC = 0;
        }

        if ($conexion->getNumeroRegistros() != 0) {

            switch ($amortizado) {
                case 'ORIGEN':
                    /* Esta conexion regresa los numeros de control que ya tienen pagadora */
                    $conexion2 = new AccesoDatos();
                    $conexion2->conectar();
                    $conexion2->setSql('
                        SELECT
                        ORI.gpo_id as gpo_idO,LPAD(ORI.ur_id,2,0) as ur_idO,ORI.clc_id AS clc_idO,ORI.afe_id as afe_idO,
                        ORI.ncc_nccamortiza AS numcontrolO,ORI.percepciones AS percepcionesO,ORI.retenciones AS retencionesO,
                        ORI.tipoMovimiento as tipoMovimientoO,ORI.Movimiento AS MovimientoO,
                        RCT.gpo_id as gpo_idR,LPAD(RCT.ur_id,2,0) as ur_idR,RCT.clc_id as clc_idR,RCT.afe_id as afe_idR,
                        ncc_nccamortiza AS numcontrolR,RCT.percepciones as percepcionesR,RCT.retenciones AS retencionesR,
                        RCT.tipoMovimiento as tipoMovimientoR,RCT.Movimiento AS MovimientoR,
                        PAG.gpo_id as gpo_idP,LPAD(PAG.ur_id,2,0) as ur_idP,PAG.clc_id as clc_idP,PAG.afe_id as afe_idP,
                        PAG.ncc_id as numcontrolP ,PAG.ncc_percepcion as percepcionesP,PAG.ncc_retencion as retencionesP,
                        PAG.tipoMovimiento as tipoMovimientoP,PAG.Movimiento AS MovimientoP
                        FROM (
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,ncp_id AS `ncc_nccamortiza`, 
                            ncp_percepcionImporteBruto AS percepciones,0 retenciones,"+" as tipoMovimiento,"P" Movimiento
                            FROM scp_RectificacionPresupuestal
                            WHERE gpo_id=:gpo_id and ur_id=:ur_id and rct_id=:rct_id and ncp_id in (' . $inP . ')
                            
                            UNION ALL
                            
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,ncc_id AS ncc_nccamortiza, 
                            ncc_percepcion AS percepciones,ncc_retencion AS retenciones,ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento
                            FROM scp_RectificacionContable
                            WHERE gpo_id=:gpo_id and ur_id=:ur_id and rct_id=:rct_id and ncc_id in (' . $inC . ')
                            )RCT
                        LEFT JOIN 
                        (
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncp_id AS ncc_nccamortiza,ncp_percepcionNeto AS percepciones,
                            0 AS retenciones,"+" as tipoMovimiento,"P" Movimiento
                            FROM scp_Presupuestal
                            WHERE gpo_id=:gpo_id and ur_id=:ur_id and clc_id=:clc_id and ncp_id in(' . $inP . ')
                            UNION ALL
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,
                            ncc_retencion retenciones,ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento
                                FROM scp_Contable
                                WHERE gpo_id=:gpo_id and ur_id=:ur_id and clc_id=:clc_id and ncc_id in(' . $inC . ')
                        )ORI USING (gpo_id,ur_id,ncc_nccamortiza)
                        LEFT JOIN
                        (
                        SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id,ncc_percepcion,ncc_retencion,ncc_nccamortiza,
                        ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento
                            FROM scp_Contable
                            JOIN scp_Clc using(gpo_id,ur_id,clc_id)
                            WHERE ncc_gpo=:gpo_id 
                            AND ncc_ur=:ur_id 
                            AND ecl_id!=15 
                            AND ncc_nccamortiza not in(' . $inP . ')
                        )PAG USING(ncc_nccamortiza)
                        WHERE RCT.gpo_id=:gpo_id 
                        AND RCT.ur_id=:ur_id 
                        AND rct_id =:rct_id
                    ');
                    $params2 = [
                        ':gpo_id' => $gpo_id,
                        ':ur_id'  => $ur_id,
                        ':rct_id' => $rct_id,
                        ':clc_id' => $clc_id];

                    $conexion2->consultar($params2);
                    $conexion2->desconectar();
                    $html = '';
                    $cont = 0;
                    $clase = $clase2 = $clase3 = $clase4 = '';
                    $rectificado = $porP = $pag = $dif = $cont = 0;
                    $html .= '
                    <div class="col-md-12" style="overflow-x: auto">
                    <table class="table table-hover table-bordered">' .
                    '<tr>
                        <th style="text-align:center" class="info" colspan="6">ORIGEN</th>
                        <th style="text-align:center" class="warning" colspan="6">RECTIFICACIONES</th>
                        <th style="text-align:center" class="info" colspan="6">PAGADORA</th>
                        <th style="text-align:center" class="warning" colspan="3">DIFERENCIA</th>
                    </tr>
                    <tr class="success">
                        <th>UR </th>
                        <th>CLC</th>
                        <th>AFEC.</th>
                        <th>No CONTROL</th>
                        <th>PERCEPCION</th>
                        <th>RETENCION</th>
                            
                        <th>UR </th>
                        <th>CLC</th>
                        <th>AFEC.</th>
                        <th>No CONTROL</th>
                        <th>PERCEPCION</th>
                        <th>RETENCION</th>
                            
                        <th>UR </th>
                        <th>CLC</th>
                        <th>AFEC.</th>
                        <th>No CONTROL</th>
                        <th>PERCEPCION</th>
                        <th>RETENCION</th>
                        
                        <th>ORIGINAL - RECTIFICADO</th>
                        <th>PAGADO</th>
                        <th>IMPORTE A RECTIFICAR</th>
                        
                    </tr>';

                    while ($row = $conexion2->extraer()) {

                        /*ESTE CONEXION ES PARA VERIFICAR LOS IMPORTES RECTIFICADOS DE LA CLC DE PAGOS A TERCEROS */
                        if ($row['gpo_idP'] != '' && $row['ur_idP'] != '' && $row['clc_idP'] != '') {
                            //se hace una validación dependiendo del tipo de movimiento contable que venga en el registro
                            if ($row['tipoMovimientoP'] == '+') {
                                //esta conexion se hace para extraer el número de rectificacion de la clc de pagos a terceros
                                $coneX = new AccesoDatos();
                                $coneX->conectar();
                                $coneX->setSql('
                                    SELECT group_concat(rct_id) as rct_id
                                    FROM scp_MaestroRectificacion 
                                    where gpo_id = ?
                                    and ur_id = ?
                                    and clc_id = ?
                                    and ecl_id = 6 
                                    GROUP BY gpo_id,ur_id,clc_id;
                                ');
                                $coneX->consultar([$row['gpo_idP'], $row['ur_idP'], $row['clc_idP']]);
                                $coneX->desconectar();
                                $detalleP = $coneX->extraer();

                                if ($coneX->getNumeroRegistros() != 0) {
                                    //una vez que obtiene el número de rectificacion se pregunta por el monto que se rectifico
                                    $conex_Rectificar = new AccesoDatos();
                                    $conex_Rectificar->conectar();
                                    $conex_Rectificar->setSql('
                                        SELECT ncc_percepcion as rct_importe
                                        FROM scp_MaestroRectificacion 
                                        join scp_RectificacionContable using(gpo_id,ur_id,rct_id,clc_id)
                                        WHERE gpo_id=' . $row['gpo_idP'] . ' 
                                        AND ur_id = ' . $row['ur_idP'] . ' 
                                        AND rct_id in (' . $detalleP['rct_id'] . ') 
                                        and ncc_id=' . $row['numcontrolP'] . '  
                                        and clc_id= ' . $row['clc_idP'] . ' 
                                        and ecl_id=6 ;
                                    ');
                                    $conex_Rectificar->consultar();
                                    $conex_Rectificar->desconectar();
                                    $detalle = $conex_Rectificar->extraer();

                                    if ($detalle['rct_importe'] != 0 && $detalle['rct_importe'] != null) {
                                        $rectificado = $detalle['rct_importe'];
                                    } else {
                                        $rectificado = 0;
                                    }
                                } else {
                                    $rectificado = 0;
                                }

                            } else {

                                //esta conexion se hace para extraer el número de rectificacion de la clc de pagos a terceros
                                $coneX = new AccesoDatos();
                                $coneX->conectar();
                                $coneX->setSql('
                                    SELECT group_concat(rct_id) as rct_id
                                    FROM scp_MaestroRectificacion 
                                    where gpo_id=' . $row['gpo_idP'] . '
                                    and ur_id = ' . $row['ur_idP'] . '
                                    and clc_id= ' . $row['clc_idP'] . '
                                    and ecl_id=6 
                                    GROUP BY gpo_id,ur_id,clc_id;
                                ');
                                $coneX->consultar();
                                $coneX->desconectar();
                                $detalleP = $coneX->extraer();

                                if ($coneX->getNumeroRegistros() != 0) {
                                    $conex_Rectificar = new AccesoDatos();
                                    $conex_Rectificar->conectar();
                                    $conex_Rectificar->setSql('
                                        SELECT ncc_retencion as rct_importe
                                        FROM scp_MaestroRectificacion 
                                        join scp_RectificacionContable using(gpo_id,ur_id,rct_id,clc_id)
                                        where gpo_id=' . $row['gpo_idP'] . ' 
                                        and ur_id = ' . $row['ur_idP'] . ' 
                                        and rct_id in(' . $detalleP['rct_id'] . ') 
                                        and ncc_id=' . $row['numcontrolP'] . '
                                        and clc_id= ' . $row['clc_idP'] . '
                                        and ecl_id=6 ;
                                    ');
                                    $conex_Rectificar->consultar();
                                    $conex_Rectificar->desconectar();
                                    $detalle = $conex_Rectificar->extraer();

                                    if ($detalle['rct_importe'] != 0 && $detalle['rct_importe'] != null) {
                                        $rectificado = $detalle['rct_importe'];
                                    } else {
                                        $rectificado = 0;
                                    }
                                } else {
                                    $rectificado = 0;
                                }
                            }
                        }

                        switch ($row['MovimientoO']) {
                            case 'P':
                                if ($row['percepcionesO'] - $row['percepcionesR'] < $row['percepcionesP'] - $rectificado) {
                                    $clase = $clase2 = $clase3 = $clase4 = 'BGCOLOR="#FF0000"';
                                    $cont += 1;
                                    $dif = $row['percepcionesO'] - $row['percepcionesR'];
                                    $pag = $row['percepcionesP'];
                                    $porP = $row['percepcionesR'];
                                } else {
                                    $clase = 'class="info"';
                                    $clase2 = 'class="warning"';
                                    $clase3 = 'class="info"';
                                    $clase4 = 'class="warning"';
                                    $dif = $row['percepcionesO'] - $row['percepcionesR'];
                                    $pag = $row['percepcionesP'];
                                    $porP = 0;
                                }

                                break;
                            case 'C':
                                switch ($row['tipoMovimientoO']) {
                                    case '+':
                                        if ($row['percepcionesO'] - $row['percepcionesR'] < $row['retencionesP'] - $rectificado) {
                                            $clase = $clase2 = $clase3 = $clase4 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                            $dif = $row['percepcionesO'] - $row['percepcionesR'];
                                            $pag = $row['retencionesP'];
                                            $porP = $row['percepcionesR'];
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                            $clase3 = 'class="info"';
                                            $clase4 = 'class="warning"';
                                            $dif = $row['percepcionesO'] - $row['percepcionesR'];
                                            $pag = $row['retencionesP'];
                                            $porP = 0;
                                        }
                                        break;
                                    case '-':
                                        if (Math::redondear($row['retencionesO'] - $row['retencionesR'], 2) < Math::redondear($row['percepcionesP'] - $rectificado, 2)) {
                                            $clase = $clase2 = $clase3 = $clase4 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                            $dif = $row['retencionesO'] - $row['retencionesR'];
                                            $pag = $row['percepcionesP'];
                                            $porP = $row['retencionesR'];

                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                            $clase3 = 'class="info"';
                                            $clase4 = 'class="warning"';
                                            $dif = $row['retencionesO'] - $row['retencionesR'];
                                            $pag = $row['percepcionesP'];
                                            $porP = 0;
                                        }
                                        break;
                                }
                                break;
                        }

                        $html .= '
                            <tr>
                                <td class="info"><b>' . $row['gpo_idO'] . $row['ur_idO'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idO'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idO'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolO'] . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['percepcionesO'], 2) . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['retencionesO'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . $row['gpo_idR'] . $row['ur_idR'] . '</b></td>
                                <td class="warning"><b>' . $row['clc_idR'] . '</b></td>
                                <td class="warning"><b>' . $row['afe_idR'] . '</b></td>
                                <td class="warning"><b>' . $row['numcontrolR'] . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['percepcionesR'], 2) . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['retencionesR'], 2) . '</b></td>
                                
                                <td class="info"><b>' . $row['gpo_idP'] . $row['ur_idP'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idP'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idP'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolP'] . '</b></td>
                                <td ' . $clase3 . '><b>' . number_format($row['percepcionesP'], 2) . '</b></td>
                                <td ' . $clase3 . '><b>' . number_format($row['retencionesP'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . number_format($dif, 2) . '</b></td>
                                <td ' . $clase4 . '><b>' . number_format($pag, 2) . '</b></td>
                                <td ' . $clase4 . '><b>' . number_format($porP, 2) . '</b></td>
                            
                            </tr>
                        ';
                    }

                    $html .= '
                        <tr class="success"><td colspan="21"><strong>Tiene(s) ' . $cont . ' número(s) de control que debe(n) ser rectificados en la clc (s) de pagos a terceros </strong></td></tr></table>
                        </div>
                    ';

                    if ($cont > 0) {
                        return $html;
                    } else {
                        return '';
                    }

                    break;
                case 'NORMAL':

                    $conexionN = new AccesoDatos();
                    $conexionN->conectar();
                    $conexionN->setSql('
                        SELECT 
                            ORI.gpo_id AS gpo_idO,LPAD(ORI.ur_id,2,0) AS ur_idO,ORI.clc_id AS clc_idO,ORI.afe_id AS afe_idO,
                            ORI.ncc_nccamortiza AS numcontrolO,ORI.percepciones AS percepcionesO,ORI.retenciones AS retencionesO,
                            ORI.tipoMovimiento AS tipoMovimientoO,ORI.Movimiento AS MovimientoO,
                            RCT.gpo_id AS gpo_idR,LPAD(RCT.ur_id,2,0) AS ur_idR,RCT.clc_id AS clc_idR,RCT.afe_id AS afe_idR,
                            ncc_nccamortiza AS numcontrolR,RCT.percepciones AS percepcionesR,RCT.retenciones AS retencionesR,
                            RCT.tipoMovimiento AS tipoMovimientoR,RCT.Movimiento AS MovimientoR
                        FROM(
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,ncp_id AS `ncc_nccamortiza`, 
                            ncp_percepcionImporteBruto AS percepciones,0 retenciones,"+" AS tipoMovimiento,"P" Movimiento
                            FROM scp_RectificacionPresupuestal
                            WHERE gpo_id=' . $gpo_id . ' and ur_id=' . $ur_id . ' and rct_id=' . $rct_id . ' and ncp_id in (' . $inP . ')
                            UNION ALL 
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,ncc_id AS ncc_nccamortiza, 
                            ncc_percepcion AS percepciones,ncc_retencion AS retenciones,ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento
                            FROM scp_RectificacionContable
                            WHERE gpo_id=' . $gpo_id . ' and ur_id=' . $ur_id . ' and rct_id=' . $rct_id . ' and ncc_id in (' . $inC . ')
                            )RCT
                        LEFT JOIN 
                        (
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncp_id AS ncc_nccamortiza,ncp_percepcionNeto AS percepciones,
                            0 AS retenciones,"+" AS tipoMovimiento,"P" Movimiento
                            FROM scp_Presupuestal
                            WHERE gpo_id=' . $gpo_id . ' and ur_id=' . $ur_id . ' and clc_id=' . $clc_id . ' and ncp_id in(' . $inP . ')
                            UNION ALL
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,
                            ncc_retencion retenciones,ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento
                            FROM scp_Contable
                            WHERE gpo_id=' . $gpo_id . ' and ur_id=' . $ur_id . ' and clc_id=' . $clc_id . ' and ncc_id in(' . $inC . ') 
                        )ORI USING (gpo_id,ur_id,ncc_nccamortiza)
                        WHERE RCT.gpo_id=' . $gpo_id . ' 
                        AND RCT.ur_id=' . $ur_id . ' 
                        AND rct_id =' . $rct_id . '
                    ');

                    $conexionN->consultar();
                    $conexionN->desconectar();
                    $html = '';
                    $cont = 0;
                    $clase = $clase2 = $clase3 = '';
                    $html .= '
                        <div class="col-md-12" style="overflow-x: auto">
                        <table class="table table-hover table-bordered">' .
                        '<tr>
                            <th style="text-align:center" class="info" colspan="6">ORIGEN</th>
                            <th style="text-align:center" class="warning" colspan="6">RECTIFICACIONES</th>
                        </tr>
                        <tr class="success">
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>No CONTROL</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                                
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>No CONTROL</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>

                        </tr>
                    ';

                    while ($row = $conexionN->extraer()) {

                        switch ($row['MovimientoO']) {
                            case 'P':
                                if ($row['percepcionesR'] > $row['percepcionesO']) {
                                    $clase = $clase2 = $clase3 = 'BGCOLOR="#FF0000"';
                                    $cont += 1;
                                } else {
                                    $clase = 'class="info"';
                                    $clase2 = 'class="warning"';
                                    $clase3 = 'class="info"';
                                }

                                break;
                        }

                        $html .= '
                            <tr>
                                <td class="info"><b>' . $row['gpo_idO'] . $row['ur_idO'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idO'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idO'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolO'] . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['percepcionesO'], 2) . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['retencionesO'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . $row['gpo_idR'] . $row['ur_idO'] . '</b></td>
                                <td class="warning"><b>' . $row['clc_idR'] . '</b></td>
                                <td class="warning"><b>' . $row['afe_idR'] . '</b></td>
                                <td class="warning"><b>' . $row['numcontrolR'] . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['percepcionesR'], 2) . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['retencionesR'], 2) . '</b></td>
                            </tr>
                        ';
                    }

                    $html .= '
                        <tr class="success">
                            <td colspan="18">Tiene(s) ' . $cont . ' número(s) de control que debes rectificar </td>
                        </tr>
                        </table></div>
                    ';

                    if ($cont > 0) {
                        return $html;
                    } else {
                        return '';
                    }

                    break;
                case 'PAGADORA':
                    $conexion3 = new AccesoDatos();
                    $conexion3->conectar();
                    $conexion3->setSql('
                        SELECT ncc_gpo,ncc_ur,GROUP_CONCAT(ncc_clc) as clc_id, GROUP_CONCAT(ncc_nccamortiza) as numcontrol
                        FROM scp_Contable
                        WHERE gpo_id=' . $gpo_id . ' and ur_id=' . $ur_id . ' and clc_id=' . $clc_id . ' and ncc_id in (' . $inC . ')
                        GROUP BY ncc_gpo,ncc_ur,clc_id
                    ');

                    $conexion3->consultar();
                    $conexion3->desconectar();
                    $where1 = $where2 = '';
                    while ($row = $conexion3->extraer()) {
                        $where1 .= ($where1 == '') ? '' : ' OR ';
                        $where1 .= '
                            ( gpo_id =' . $row['ncc_gpo'] . ' 
                            AND ur_id = ' . $row['ncc_ur'] . ' 
                            AND clc_id IN (' . $row['clc_id'] . ') 
                            AND ncc_id IN(' . $row['numcontrol'] . ') )
                        ';
                        $where2 .= ($where2 == '') ? '' : ' OR ';
                        $where2 .= '
                            ( gpo_id =' . $row['ncc_gpo'] . ' 
                            AND ur_id = ' . $row['ncc_ur'] . ' 
                            AND clc_id IN (' . $row['clc_id'] . ') 
                            AND ncp_id IN(' . $row['numcontrol'] . ') )
                        ';
                    }

                    $encabezado = $pagadora = $whp = $whc = '';
                    if ($conexion3->getNumeroRegistros() != 0) {
                        $encabezado = '
                            ORI.gpo_id as gpo_idO,LPAD(ORI.ur_id,2,0) as ur_idO,ORI.clc_id AS clc_idO,
                            ORI.afe_id as afe_idO,ORI.ncc_nccamortiza AS numcontrolO,ORI.percepciones AS percepcionesO,
                            ORI.retenciones AS retencionesO,ORI.tipoMovimiento as tipoMovimientoR ,ORI.Movimiento AS MovimientoO,
                        ';
                        $pagadora = '
                            LEFT JOIN
                            ( 
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncp_id AS numcontrol,ncp_percepcionNeto AS percepciones,0 AS retenciones,"" ncc_nccamortiza,"+" as tipoMovimiento,"P" Movimiento
                                FROM scp_Presupuestal
                                WHERE ' . $where2 . '
                                UNION ALL
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_id ncc_nccamortiza,ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento
                                FROM scp_Contable
                                WHERE ' . $where1 . '
                            )ORI USING(ncc_nccamortiza)
                        ';
                    }

                    $conexion4 = new AccesoDatos();
                    $conexion4->conectar();
                    $conexion4->setSql('
                        SELECT
                            ' . $encabezado . '
                            PAG.gpo_id as gpo_idP,LPAD(PAG.ur_id,2,0) as ur_idP,PAG.clc_id as clc_idP,
                            PAG.afe_id as afe_idP,PAG.numcontrol as numcontrolP ,PAG.percepciones as percepcionesP,
                            PAG.retenciones as retencionesP,PAG.tipoMovimiento as tipoMovimientoP ,PAG.Movimiento AS MovimientoP,
                            RCT.gpo_id as gpo_idR,LPAD(RCT.ur_id,2,0) as ur_idR,RCT.clc_id as clc_idR,
                            RCT.afe_id as afe_idR,RCT.numcontrol AS numcontrolR,RCT.percepciones as percepcionesR,
                            RCT.retenciones AS retencionesR,RCT.tipoMovimiento as tipoMovimientoR ,RCT.Movimiento AS MovimientoR 

                        FROM (
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_id AS numcontrol,ncc_percepcion AS percepciones,
                            ncc_retencion AS retenciones,ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento
                            FROM scp_RectificacionContable
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' AND ncc_id IN (' . $inC . ')
                        )RCT 
                        LEFT JOIN
                        (
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS numcontrol, ncc_percepcion AS percepciones,
                                ncc_retencion AS retenciones,
                                ncc_nccamortiza,ncc_tipoMovimiento as tipoMovimiento,"C" Movimiento	
                            FROM scp_Contable
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND clc_id=' . $clc_id . ' AND ncc_id IN (' . $inC . ')
                        )PAG USING(numcontrol)
                        ' . $pagadora . '
                        WHERE RCT.gpo_id=' . $gpo_id . ' AND RCT.ur_id=' . $ur_id . ' AND rct_id =' . $rct_id . '
                    ');

                    $conexion4->consultar();
                    $conexion4->desconectar();
                    $html = '';
                    $cont = 0;
                    $clase = $clase2 = $clase3 = $clase4 = '';
                    $porP = $pag = $dif = $cont = 0;
                    $html .= '
                    <div class="col-md-12" style="overflow-x: auto">
                    <table class="table table-hover table-bordered">' .
                        '<tr>
                            <th style="text-align:center" class="info" colspan="6">PAGADORA</th>
                            <th style="text-align:center" class="warning" colspan="6">RECTIFICACIONES</th>
                            <th style="text-align:center" class="info" colspan="6">ORIGEN</th>
                            <th style="text-align:center" class="info" colspan="3">DIFERENCIA</th>
                        </tr>
                        <tr class="success">
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>No CONTROL</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                                
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>No CONTROL</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                                
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>No CONTROL</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                            
                            <th>PAGADO - RECTIFICADO</th>
                            <th>ORIGINAL</th>
                            <th>IMPORTE A RECTIFICAR</th>
                    </tr>';

                    while ($row = $conexion4->extraer()) {

                        if ($row['percepcionesP'] - $row['percepcionesR'] > $row['retencionesO'] ||
                        ($row['retencionesP'] - $row['retencionesR'] > $row['percepcionesO'])) {

                            if ($row['percepcionesP'] != 0 && $row['percepcionesR'] != 0) {
                                $dif = $row['percepcionesP'] - $row['percepcionesR'];
                                $pag = $row['retencionesO'];
                                $porP = $row['percepcionesR'];
                            } else {
                                $dif = $row['retencionesP'] - $row['retencionesR'];
                                $pag = $row['percepcionesO'];
                                $porP = $row['retencionesR'];
                            }
                            $clase = $clase2 = $clase3 = $clase4 = 'BGCOLOR="#FF0000"';
                            $cont += 1;

                        } else {
                            $clase = 'class="info"';
                            $clase2 = 'class="warning"';
                            $clase3 = 'class="info"';
                            $clase4 = 'class="warning"';

                            if ($row['percepcionesP'] != 0 && $row['percepcionesR'] != 0) {
                                $dif = $row['percepcionesP'] - $row['percepcionesR'];
                                $pag = $row['retencionesO'];
                                $porP = 0;
                            } else {
                                $dif = $row['retencionesP'] - $row['retencionesR'];
                                $pag = $row['percepcionesO'];
                                $porP = 0;
                            }
                        }

                        $html .= '
                            <tr>
                                <td class="info"><b>' . $row['gpo_idP'] . $row['ur_idP'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idP'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idP'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolP'] . '</b></td>
                                <td ' . $clase3 . '><b>' . number_format($row['percepcionesP'], 2) . '</b></td>
                                <td ' . $clase3 . '><b>' . number_format($row['retencionesP'], 2) . '</b></td>
                                                                    
                                <td class="warning"><b>' . $row['gpo_idR'] . $row['ur_idO'] . '</b></td>
                                <td class="warning"><b>' . $row['clc_idR'] . '</b></td>
                                <td class="warning"><b>' . $row['afe_idR'] . '</b></td>
                                <td class="warning"><b>' . $row['numcontrolR'] . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['percepcionesR'], 2) . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['retencionesR'], 2) . '</b></td>
                                
                                <td class="info"><b>' . $row['gpo_idO'] . $row['ur_idO'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idO'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idO'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolO'] . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['percepcionesO'], 2) . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['retencionesO'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . number_format($dif, 2) . '</b></td>
                                <td ' . $clase4 . '><b>' . number_format($pag, 2) . '</b></td>
                                <td ' . $clase4 . '><b>' . number_format($porP, 2) . '</b></td>
                                
                            </tr>
                        ';

                    }

                    $html .= '
                        <tr class="success">
                        <td colspan="21">Tiene(s) ' . $cont . ' Número(s) de control que debes rectificar </td>
                        </tr>
                        </table></div>
                    ';

                    if ($cont > 0) {
                        return $html;
                    } else {
                        return '';
                    }
                    break;
            }
            //if del numero de registros
        } else {

            $html = '
                <div class="alert alert-danger"  style="height: 100px;"><p class="text-center">
                    <strong>LA RECTIFICACIÓN ' . $rct_id . ' NO TIENE NÚMEROS DE CONTROL EDITADOS</strong>
                    </p>
                </div>
            ';
            return $html;
        }
    }

    public static function validarRectificacionesObra()
    {

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        $clc_id = (!isset($prm->clc_id)) ? '' : $prm->clc_id;
        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $rct_id = (!isset($prm->rct_id)) ? '' : $prm->rct_id;
        $amortizado = (!isset($prm->amortizado)) ? '' : $prm->amortizado;
        $rct_reintegro = (!isset($prm->rct_reintegro)) ? '' : $prm->rct_reintegro;
        $tipo = (!isset($prm->tipo)) ? '' : $prm->tipo;
        $stc_id = (!isset($prm->stc_id)) ? '' : $prm->stc_id;

        $np = $nc = $reintegro = '';
        switch ($rct_reintegro) {
            case 1:
                $reintegro .= ($reintegro == '') ? 'and rct_reintegro= 1 ' : ' and rct_reintegro=1';
                $nc = ' and rcc_editado=1';
                $np = ' and rcp_editado=1';
                break;
            case 2:
                $reintegro .= ($reintegro == '') ? 'and rct_reintegro= 2 ' : ' and rct_reintegro=2';
                $nc = ' and rcc_editado=1';
                $np = ' and rcp_editado=1';
                break;
            case 3:
                $reintegro .= ($reintegro == '') ? 'and rct_reintegro= 3 ' : ' and rct_reintegro=3';
                $nc = ' and rcc_editado=3';
                $np = ' and rcp_editado=3';
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave as clave,ncp_id as numcontrol,
                ncp_percepcionImporteBruto as percepciones,0 retenciones
            FROM scp_RectificacionPresupuestal
            WHERE gpo_id=' . $gpo_id . ' and ur_id=' . $ur_id . ' and rct_id=' . $rct_id . ' ' . $np . '
            UNION ALL
            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave as clave,ncc_id as numcontrol,
                ncc_percepcion as percepciones,ncc_retencion as retenciones
            FROM scp_RectificacionContable
            WHERE gpo_id=' . $gpo_id . ' and ur_id=' . $ur_id . ' and rct_id=' . $rct_id . ' ' . $nc . ' 
        ');

        $conexion->consultar();
        $conexion->desconectar();
        $wd = $in = '';
        while ($row = $conexion->extraer()) {
            $in .= $row['numcontrol'] . ',';
        }
        $in = trim($in, ',');

        if ($conexion->getNumeroRegistros() != 0) {
            switch ($tipo) {
                case 'F':
                    $conexion2 = new AccesoDatos();
                    $conexion2->conectar();
                    $conexion2->setSql('
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,"+" as tipoMovimiento,ncp_id AS numcontrol,
                        ncp_percepcionImporteBruto AS percepciones,0 retenciones,
                        "NORMAL " amortizado,"" ger_id,"" cgp_id,"" rub_id,"" cgcu_id,"" cgsc_id,"" AMORT_ANTICIPO,
                        ""ORIGEN_MILLAR,"" ORIGEN_SANCIONES,"" AMORT_CREDITO 
                        FROM scp_RectificacionPresupuestal 
                        WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' and ncp_id in (' . $in . ') ' . $np . ' 
                        UNION ALL 
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,c.ncc_tipoMovimiento as tipoMovimiento,
                        ncc_id AS numcontrol,b.ncc_percepcion AS percepciones,b.ncc_retencion AS retenciones,
                            IF(ncc_amortizado=1,"ORIGEN",IF(ncc_nccamortiza>0,"PAGADORA","NORMAL")) AS amortizado,
                            ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,
                            IF( b.ncc_tipoMovimiento="-",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 9 , IF(cgsc_id = 4 , c.ncc_retencion ,0) ,0) ,0)  ,0) ,0 ),0) AMORT_ANTICIPO,
                            IF( b.ncc_tipoMovimiento="-",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 7 , IF(cgsc_id = 1 , c.ncc_retencion ,0) ,0) ,0)  ,0) ,0 ),0) ORIGEN_MILLAR,
                            IF( b.ncc_tipoMovimiento="-",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 7 , IF(cgsc_id = 9 , c.ncc_retencion ,0) ,0) ,0)  ,0) ,0 ),0) ORIGEN_SANCIONES,
                            IF( b.ncc_tipoMovimiento="-",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 9 , IF(cgsc_id = 2 , c.ncc_retencion ,0) ,0) ,0)  ,0) ,0 ),0) AMORT_CREDITO
                        FROM scp_RectificacionContable b
                        LEFT JOIN scp_Contable c  USING(gpo_id,ur_id,clc_id,afe_id,ncc_id) 
                        WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' and ncc_id in (' . $in . ') ' . $nc . '
                    ');

                    $conexion2->consultar();
                    $conexion2->desconectar();

                    $presupuestales = $credito = $sanciones = $millar = $anticipo = '';

                    $registros = 0;
                    while ($row = $conexion2->extraer()) {
                        $registros++;
                        switch ($row['tipoMovimiento'] . $row['ger_id'] . $row['cgp_id'] . $row['rub_id'] . $row['cgcu_id'] . $row['cgsc_id']) {
                            case '-21194' :
                                //amortizadora
                                if ($row['AMORT_ANTICIPO'] <> 0) {
                                    $anticipo .= $row['numcontrol'] . ',';
                                }
                                break;
                            case '-21171' :
                                //origen
                                if ($row['ORIGEN_MILLAR'] <> 0) {
                                    $millar .= $row['numcontrol'] . ',';
                                }
                                break;
                            case '-21179' :
                                //origen
                                if ($row['ORIGEN_SANCIONES'] <> 0) {
                                    $millar .= $row['numcontrol'] . ',';
                                }
                                break;
                            case '-21192' :
                                //amortizadora
                                if ($row['AMORT_CREDITO'] <> 0) {
                                    $anticipo .= $row['numcontrol'] . ',';
                                }
                                break;
                            default:
                                //origen
                                $millar .= $row['numcontrol'] . ',';
                                break;

                        }
                    }


                    /*Los numeros de control que se van a concatenar  a la consulta de originales son todos a quellos que sean originales
                        millar, sanciones, presupuestales
                    */
                    $anticipo = trim($anticipo, ',');
                    $millar = trim($millar, ',');
                    if ($anticipo == '') {
                        $anticipo = 0;
                    }
                    if ($millar == '') {
                        $millar = 0;
                    }

                    $conexion3 = new AccesoDatos();
                    $conexion3->conectar();
                    $conexion3->setSql('
                        SELECT 
                            ORI.gpo_id AS gpo_idO,ORI.ur_id AS ur_idO,ORI.clc_id AS clc_idO,ORI.afe_id AS afe_idO,ORI.ncc_nccamortiza AS numcontrolO,ORI.percepciones AS percepcionesO,ORI.retenciones AS retencionesO,ORI.tipoMovimiento AS tipoMovimientoO,ORI.Movimiento AS MovimientoO,ORI.cuenta as cuentaO,
                            PAG.gpo_id AS gpo_idP,PAG.ur_id AS ur_idP,PAG.clc_id AS clc_idP,PAG.afe_id AS afe_idP,PAG.ncc_id AS numcontrolP ,PAG.ncc_percepcion AS percepcionesP,PAG.ncc_retencion AS retencionesP,PAG.tipoMovimiento AS tipoMovimientoP,PAG.Movimiento AS MovimientoP,PAG.cuenta as cuentaP,
                            RCT.gpo_id AS gpo_idR,RCT.ur_id AS ur_idR,RCT.clc_id AS clc_idR,RCT.afe_id AS afe_idR,ncc_nccamortiza AS numcontrolR,RCT.percepciones AS percepcionesR,RCT.retenciones AS retencionesR,RCT.tipoMovimiento AS tipoMovimientoR,RCT.Movimiento AS MovimientoR,RCT.clave as claveR,
                            AMT.gpo_id AS gpo_idA,AMT.ur_id AS ur_idA,AMT.clc_id AS clc_idA,AMT.afe_id AS afe_idA,ncc_nccamortiza AS numcontrolA,AMT.percepciones AS percepcionesA,AMT.retenciones AS retencionesA,AMT.tipoMovimiento AS tipoMovimientoA,AMT.Movimiento AS MovimientoA,AMT.cuenta as cuentaA,
                            AMD.gpo_id AS gpo_idD,AMD.ur_id AS ur_idD,AMD.clc_id AS clc_idD,AMD.afe_id AS afe_idD,ncc_nccamortiza AS numcontrolD,AMD.percepciones AS percepcionesD,AMD.retenciones AS retencionesD,AMD.tipoMovimiento AS tipoMovimientoD,AMD.Movimiento AS MovimientoD,AMD.cuenta as cuentaD
                                
                        FROM ( 
                            #EN ESTA PARTE SE ESTAN MOSTRANDO LOS NUMEROS DE CONTROL ORIGINALES 
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncp_id AS ncc_nccamortiza,ncp_percepcionNeto AS percepciones,0 AS retenciones,"+" AS tipoMovimiento,"P" Movimiento,"" cuenta
                                FROM scp_Presupuestal
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND clc_id=' . $clc_id . ' AND ncp_id IN(' . $millar . ')
                            UNION ALL
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                FROM scp_Contable
                                WHERE gpo_id=' . $gpo_id . '  AND ur_id=' . $ur_id . ' AND clc_id=' . $clc_id . '  AND ncc_id IN(' . $millar . ')
                        )ORI
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRAN LOS PAGADOS 
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id,ncc_percepcion,ncc_retencion,ncc_nccamortiza,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                FROM scp_Contable
                                WHERE ncc_gpo=' . $gpo_id . ' AND ncc_ur=' . $ur_id . ' AND ncc_id IN(' . $in . ')
                        )PAG USING(ncc_nccamortiza)
                        RIGHT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRAN LOS RECTIFICADOS 
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,ncp_id AS `ncc_nccamortiza`, ncp_percepcionImporteBruto AS percepciones,0 retenciones,"+" AS tipoMovimiento,"P" Movimiento
                                FROM scp_RectificacionPresupuestal
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' AND ncp_id IN (' . $in . ')
                            UNION ALL
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,ncc_id AS ncc_nccamortiza, ncc_percepcion AS percepciones,ncc_retencion AS retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento
                                FROM scp_RectificacionContable
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' AND ncc_id IN (' . $in . ')
                        )RCT USING(ncc_nccamortiza)
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ESTAN AMORTIZANDO
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                            FROM scp_Contable WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND ncc_id IN(' . $anticipo . ')
                        )AMT USING(ncc_nccamortiza)
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ES EL ANTICIPO O AMORTIZACION
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                            FROM scp_Contable WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND ncc_nccamortiza IN(' . $anticipo . ')
                        )AMD USING(ncc_nccamortiza)
                
                    ');

                    $conexion3->consultar();
                    $conexion3->desconectar();
                    $clase = $clase2 = $clase3 = $clase4 = $html = '';
                    $cont2 = $cont = 0;
                    $html .= '
                        <div class="col-md-12" style="overflow-x: auto">
                        <table class="table table-hover table-bordered">' .
                        '<tr>
                            <th style="text-align:center" class="info" colspan="7">ORIGEN</th>
                            <th style="text-align:center" class="warning" colspan="7">PAGADORA</th>
                            <th style="text-align:center" class="info" colspan="7">RECTIFICACIONES</th>
                            <th style="text-align:center" class="info" colspan="7">AMORTIZADORA</th>
                            <th style="text-align:center" class="info" colspan="7">ANTICIPO/CRÉDITO</th>
                        </tr>
                        <tr class="success">
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>CONTROL</th>
                            <th>CUENTA</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                                
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>CONTROL</th>
                            <th>CUENTA</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                                
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>CONTROL</th>
                            <th>CLAVE</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                            
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>CONTROL</th>
                            <th>CUENTA</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>
                                
                            <th>UR </th>
                            <th>CLC</th>
                            <th>AFEC.</th>
                            <th>CONTROL</th>
                            <th>CUENTA</th>
                            <th>PERCEPCION</th>
                            <th>RETENCION</th>

                        </tr>
                    ';

                    while ($row = $conexion3->extraer()) {

                        switch ($row['MovimientoO']) {
                            case 'P':
                                if ($row['percepcionesO'] - $row['percepcionesR'] < $row['percepcionesP']) {
                                    $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                    $cont += 1;
                                } else {
                                    $clase = 'class="info"';
                                    $clase2 = 'class="warning"';
                                }

                                break;
                            case 'C':
                                switch ($row['tipoMovimientoO']) {
                                    case '+':
                                        if ($row['percepcionesO'] - $row['percepcionesR'] < $row['retencionesP']) {
                                            $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                        }
                                        break;
                                    case '-':
                                        if ($row['retencionesO'] - $row['retencionesR'] < $row['percepcionesP']) {
                                            $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                        }
                                        break;
                                }
                                break;
                        }

                        /*Para validar lo amortizado con los anticipos*/
                        switch ($row['cuentaA']) {
                            case 21194:
                                if ($row['retencionesA'] >= $row['percepcionesD'] && $row['percepcionesD'] != 0) {
                                    $clase3 = $clase4 = 'BGCOLOR="#FFFF00"';
                                    $cont2 += 1;
                                } else {
                                    $clase3 = 'class="info"';
                                    $clase4 = 'class="active"';
                                }

                                break;
                            case 21192:
                                if ($row['retencionesA'] >= $row['percepcionesD'] && $row['percepcionesD'] != 0) {
                                    $clase3 = $clase4 = 'BGCOLOR="#FFFF00"';
                                    $cont2 += 1;
                                } else {
                                    $clase3 = 'class="info"';
                                    $clase4 = 'class="active"';
                                }
                                break;

                        }

                        $html .= '
                            <tr>
                                <td class="info"><b>' . $row['gpo_idO'] . $row['ur_idO'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idO'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idO'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolO'] . '</b></td>
                                <td class="info"><b>' . $row['cuentaO'] . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['percepcionesO'], 2) . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['retencionesO'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . $row['gpo_idP'] . $row['ur_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['clc_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['afe_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['numcontrolP'] . '</b></td>
                                <td class="warning"><b>' . $row['cuentaP'] . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['percepcionesP'], 2) . '</b></td>
                                <td ' . $clase2 . '><b>' . number_format($row['retencionesP'], 2) . '</b></td>
                                
                                <td class="info"><b>' . $row['gpo_idR'] . $row['ur_idR'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idR'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idR'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolR'] . '</b></td>
                                <td class="info"><b>' . $row['claveR'] . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['percepcionesR'], 2) . '</b></td>
                                <td ' . $clase . '><b>' . number_format($row['retencionesR'], 2) . '</b></td>
                                
                                <td class="active"><b>' . $row['gpo_idA'] . $row['ur_idA'] . '</b></td>
                                <td class="active"><b>' . $row['clc_idA'] . '</b></td>
                                <td class="active"><b>' . $row['afe_idA'] . '</b></td>
                                <td class="active"><b>' . $row['numcontrolA'] . '</b></td>
                                <td class="active"><b>' . $row['cuentaA'] . '</b></td>
                                <td ' . $clase4 . '><b>' . number_format($row['percepcionesA'], 2) . '</b></td>
                                <td ' . $clase4 . '><b>' . number_format($row['retencionesA'], 2) . '</b></td>
                                
                                <td class="info"><b>' . $row['gpo_idD'] . $row['ur_idD'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idD'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idD'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolD'] . '</b></td>
                                <td class="info"><b>' . $row['cuentaD'] . '</b></td>
                                <td ' . $clase3 . '><b>' . number_format($row['percepcionesD'], 2) . '</b></td>
                                <td ' . $clase3 . '><b>' . number_format($row['retencionesD'], 2) . '</b></td>
                            </tr>
                        ';
                    }

                    $html .= '<tr class="success"><td colspan="21"><strong> Tiene(s) ' . $cont . '  número(s) de control que debes rectificar </strong></td><td colspan="14"><strong>Tienes (s) ' . $cont2 . ' números de control por corregir a las amortizaciones </strong></td></tr></table>
                    </div>';

                    if ($cont > 0 || $cont2 > 0) {
                        return $html;
                    } else {
                        return '';
                    }

                    break;
                case 'G':
                    $conex = new AccesoDatos();
                    $conex->conectar();
                    $conex->setSql('
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,"+" as tipoMovimiento,ncp_id AS numcontrol,ncp_percepcionImporteBruto AS percepciones,0 retenciones,
                            "NORMAL" amortizado,"" ger_id,"" cgp_id,"" rub_id,"" cgcu_id,"" cgsc_id,"" AMORT_CREDITO,""ORIGEN_CRE
                            FROM scp_RectificacionPresupuestal 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' ' . $np . ' 
                        UNION ALL 
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,c.ncc_tipoMovimiento as tipoMovimiento,ncc_id AS numcontrol,b.ncc_percepcion AS percepciones,b.ncc_retencion AS retenciones,
                            IF(ncc_amortizado=1,"ORIGEN",IF(ncc_nccamortiza>0,"PAGADORA","NORMAL")) AS amortizado,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,
                            IF( b.ncc_tipoMovimiento="-",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 9 , IF(cgsc_id = 2 , c.ncc_retencion ,0) ,0) ,0)  ,0) ,0 ),0) AMORT_CREDITO,
                            IF( b.ncc_tipoMovimiento="-",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 7 , IF(cgsc_id = 1 , c.ncc_retencion ,0) ,0) ,0)  ,0) ,0 ),0) ORIGEN_CRE
                        FROM scp_RectificacionContable b
                        LEFT JOIN scp_Contable c  USING(gpo_id,ur_id,clc_id,afe_id,ncc_id) 
                        WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' ' . $nc . '
                    ');

                    $conex->consultar();
                    $conex->desconectar();
                    $presupuestales = $retenciones = $credito = '';
                    $registros = 0;
                    while ($row = $conex->extraer()) {
                        $registros++;
                        switch ($row['tipoMovimiento'] . $row['ger_id'] . $row['cgp_id'] . $row['rub_id'] . $row['cgcu_id'] . $row['cgsc_id']) {
                            case '-21192' :
                                //amortizadora
                                if ($row['AMORT_CREDITO'] <> 0) {
                                    $credito .= $row['numcontrol'] . ',';
                                }
                                break;
                            case '-21171' :
                                //origen
                                if ($row['ORIGEN_CRE'] <> 0) {
                                    $presupuestales .= $row['numcontrol'] . ',';
                                }
                                break;
                            default:
                                //origen
                                $presupuestales .= $row['numcontrol'] . ',';
                                break;
                        }
                    }

                    $credito = trim($credito, ',');
                    if ($credito == '') {
                        $credito = 0;
                    }

                    $presupuestales = trim($presupuestales, ',');
                    if ($presupuestales == '') {
                        $presupuestales = 0;
                    }
                    $conex2 = new AccesoDatos();
                    $conex2->conectar();
                    $conex2->setSql('
                        SELECT 
                            ORI.gpo_id AS gpo_idO,ORI.ur_id AS ur_idO,ORI.clc_id AS clc_idO,ORI.afe_id AS afe_idO,
                            ORI.ncc_nccamortiza AS numcontrolO,ORI.percepciones AS percepcionesO,ORI.retenciones AS retencionesO,
                            ORI.tipoMovimiento AS tipoMovimientoO,ORI.Movimiento AS MovimientoO,ORI.cuenta as cuentaO,
                            PAG.gpo_id AS gpo_idP,PAG.ur_id AS ur_idP,PAG.clc_id AS clc_idP,PAG.afe_id AS afe_idP,
                            PAG.ncc_id AS numcontrolP ,PAG.ncc_percepcion AS percepcionesP,PAG.ncc_retencion AS retencionesP,
                            PAG.tipoMovimiento AS tipoMovimientoP,PAG.Movimiento AS MovimientoP,PAG.cuenta as cuentaP,
                            RCT.gpo_id AS gpo_idR,RCT.ur_id AS ur_idR,RCT.clc_id AS clc_idR,RCT.afe_id AS afe_idR,
                            ncc_nccamortiza AS numcontrolR,RCT.percepciones AS percepcionesR,RCT.retenciones AS retencionesR,
                            RCT.tipoMovimiento AS tipoMovimientoR,RCT.Movimiento AS MovimientoR,RCT.clave as claveR,
                            AMT.gpo_id AS gpo_idA,AMT.ur_id AS ur_idA,AMT.clc_id AS clc_idA,AMT.afe_id AS afe_idA,
                            ncc_nccamortiza AS numcontrolA,AMT.percepciones AS percepcionesA,AMT.retenciones AS retencionesA,
                            AMT.tipoMovimiento AS tipoMovimientoA,AMT.Movimiento AS MovimientoA,AMT.cuenta as cuentaA,
                            AMD.gpo_id AS gpo_idD,AMD.ur_id AS ur_idD,AMD.clc_id AS clc_idD,AMD.afe_id AS afe_idD,
                            ncc_nccamortiza AS numcontrolD,AMD.percepciones AS percepcionesD,AMD.retenciones AS retencionesD,
                            AMD.tipoMovimiento AS tipoMovimientoD,AMD.Movimiento AS MovimientoD,AMD.cuenta as cuentaD
                                
                        FROM ( 
                                #EN ESTA PARTE SE ESTAN MOSTRANDO LOS NUMEROS DE CONTROL ORIGINALES 
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncp_id AS ncc_nccamortiza,ncp_percepcionNeto AS percepciones,
                                0 AS retenciones,"+" AS tipoMovimiento,"P" Movimiento,"" cuenta
                                    FROM scp_Presupuestal
                                    WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND clc_id=' . $clc_id . ' AND ncp_id IN(' . $presupuestales . ')
                                UNION ALL
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,
                                ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,
                                concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                    FROM scp_Contable
                                    WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND clc_id=' . $clc_id . '  AND ncc_id IN(' . $presupuestales . ')
                            )ORI
                            LEFT JOIN
                            (
                                #EN ESTA PARTE SE MUESTRAN LOS PAGADOS 
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id,ncc_percepcion,ncc_retencion,ncc_nccamortiza,
                                ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                    FROM scp_Contable
                                    WHERE ncc_gpo=' . $gpo_id . ' AND ncc_ur=' . $ur_id . ' AND ncc_id IN(' . $in . ')
                            )PAG USING(ncc_nccamortiza)
                            RIGHT JOIN
                            (
                                #EN ESTA PARTE SE MUESTRAN LOS RECTIFICADOS 
                                SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,ncp_id AS `ncc_nccamortiza`, 
                                ncp_percepcionImporteBruto AS percepciones,0 retenciones,"+" AS tipoMovimiento,"P" Movimiento
                                    FROM scp_RectificacionPresupuestal
                                    WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' AND ncp_id IN (' . $in . ')
                                UNION ALL
                                SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,ncc_id AS ncc_nccamortiza, 
                                ncc_percepcion AS percepciones,ncc_retencion AS retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento
                                    FROM scp_RectificacionContable
                                    WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' AND ncc_id IN (' . $in . ')
                            )RCT USING(ncc_nccamortiza)
                            LEFT JOIN
                            (
                                #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ESTAN AMORTIZANDO
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,
                                ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,
                                concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                FROM scp_Contable 
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND ncc_id IN(' . $credito . ')
                            )AMT USING(ncc_nccamortiza)
                            LEFT JOIN
                            (
                                #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ES EL ANTICIPO
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncc_nccamortiza,ncc_percepcion AS percepciones,
                                ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,
                                concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                FROM scp_Contable 
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND ncc_nccamortiza IN(' . $credito . ')
                            )AMD USING(ncc_nccamortiza)
                    ');
                    $conex2->consultar();
                    $conex2->desconectar();
                    $cont2 = $cont = 0;
                    $html = '';
                    $html .= '
                        <div class="col-md-12" style="overflow-x: auto">
                        <table class="table table-hover table-bordered">' .
                            '<tr>
                                <th style="text-align:center" class="info" colspan="7">ORIGEN</th>
                                <th style="text-align:center" class="warning" colspan="7">PAGADORA</th>
                                <th style="text-align:center" class="info" colspan="7">RECTIFICACIONES</th>
                                <th style="text-align:center" class="info" colspan="7">AMORTIZADORA</th>
                                <th style="text-align:center" class="info" colspan="7">ACUERDOS/CREDITO</th>
                            </tr>
                            <tr class="success">
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                    
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                    
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                    
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>

                            </tr>
                    ';

                    while ($row = $conex2->extraer()) {

                        switch ($row['MovimientoO']) {
                            case 'P':
                                if ($row['percepcionesO'] - $row['percepcionesR'] < $row['percepcionesP']) {
                                    $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                    $cont += 1;
                                } else {
                                    $clase = 'class="info"';
                                    $clase2 = 'class="warning"';
                                }
                                break;
                            case 'C':
                                switch ($row['tipoMovimientoO']) {
                                    case '+':
                                        if ($row['percepcionesO'] - $row['percepcionesR'] < $row['retencionesP']) {
                                            $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                        }
                                        break;
                                    case '-':
                                        if ($row['retencionesO'] - $row['retencionesR'] < $row['percepcionesP']) {
                                            $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                        }

                                        break;
                                }
                                break;
                        }
                        /*Para validar lo amortizado con los anticipos*/
                        switch ($row['cuentaA']) {
                            case 21192:
                                if ($row['retencionesA'] >= $row['percepcionesD'] && $row['percepcionesD'] != 0) {
                                    $clase3 = $clase4 = 'BGCOLOR="#FFFF00"';
                                    $cont2 += 1;
                                } else {
                                    $clase3 = 'class="info"';
                                    $clase4 = 'class="active"';
                                }
                                break;

                        }

                        $html .= '
                            <tr>
                                <td class="info"><b>' . $row['gpo_idO'] . $row['ur_idO'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idO'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idO'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolO'] . '</b></td>
                                <td class="info"><b>' . $row['cuentaO'] . '</b></td>
                                <td class="info"><b>' . number_format($row['percepcionesO'], 2) . '</b></td>
                                <td class="info"><b>' . number_format($row['retencionesO'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . $row['gpo_idP'] . $row['ur_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['clc_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['afe_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['numcontrolP'] . '</b></td>
                                <td class="warning"><b>' . $row['cuentaP'] . '</b></td>
                                <td class="warning"><b>' . number_format($row['percepcionesP'], 2) . '</b></td>
                                <td class="warning"><b>' . number_format($row['retencionesP'], 2) . '</b></td>
                                
                                <td class="info"><b>' . $row['gpo_idR'] . $row['ur_idR'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idR'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idR'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolR'] . '</b></td>
                                <td class="info"><b>' . $row['claveR'] . '</b></td>
                                <td class="info"><b>' . number_format($row['percepcionesR'], 2) . '</b></td>
                                <td class="info"><b>' . number_format($row['retencionesR'], 2) . '</b></td>
                                
                                <td class="active"><b>' . $row['gpo_idA'] . $row['ur_idA'] . '</b></td>
                                <td class="active"><b>' . $row['clc_idA'] . '</b></td>
                                <td class="active"><b>' . $row['afe_idA'] . '</b></td>
                                <td class="active"><b>' . $row['numcontrolA'] . '</b></td>
                                <td class="active"><b>' . $row['cuentaA'] . '</b></td>
                                <td class="active"><b>' . number_format($row['percepcionesA'], 2) . '</b></td>
                                <td class="active"><b>' . number_format($row['retencionesA'], 2) . '</b></td>
                                
                                <td><b>' . $row['gpo_idD'] . $row['ur_idD'] . '</b></td>
                                <td><b>' . $row['clc_idD'] . '</b></td>
                                <td><b>' . $row['afe_idD'] . '</b></td>
                                <td><b>' . $row['numcontrolD'] . '</b></td>
                                <td><b>' . $row['cuentaD'] . '</b></td>
                                <td><b>' . number_format($row['percepcionesD'], 2) . '</b></td>
                                <td><b>' . number_format($row['retencionesD'], 2) . '</b></td>
                            </tr>
                        ';

                    }
                    $html .= '<tr class="success"><td colspan="21"><strong>Tiene(s) ' . $cont . '  número(s) de control que debes rectificar </strong></td><td colspan="21"><strong>Tienes ' . $cont2 . ' números amortizados que debes de rectificar </strong></td></tr>
                    </table></div>';
                    if ($cont > 0 || $cont2 > 0) {
                        return $html;
                    } else {
                        return '';
                    }

                    break;
                case 'H':
                    $conexAnt = new AccesoDatos();
                    $conexAnt->conectar();
                    $conexAnt->setSql('
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,"+" as tipoMovimiento,ncp_id AS numcontrol,ncp_percepcionImporteBruto AS percepciones,0 retenciones,
                            "NORMAL " amortizado,"" ger_id,"" cgp_id,"" rub_id,"" cgcu_id,"" cgsc_id,"" ORIGEN_ANT
                            FROM scp_RectificacionPresupuestal 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' ' . $np . ' 
                        UNION ALL 
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,c.ncc_tipoMovimiento as tipoMovimiento,ncc_id AS numcontrol,b.ncc_percepcion AS percepciones,b.ncc_retencion AS retenciones,
                            IF(ncc_amortizado=1,"ORIGEN",IF(ncc_nccamortiza>0,"PAGADORA","NORMAL")) AS amortizado,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,
                            IF( b.ncc_tipoMovimiento="+",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 9 , IF(cgsc_id = 4 , c.ncc_percepcion ,0) ,0) ,0)  ,0) ,0 ),0)ORIGEN_ANT
                            FROM scp_RectificacionContable b
                            LEFT JOIN scp_Contable c  USING(gpo_id,ur_id,clc_id,afe_id,ncc_id) 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' ' . $nc . '
                    ');

                    $conexAnt->consultar();
                    $conexAnt->desconectar();
                    $anticipos = '';
                    $registros = 0;
                    while ($row = $conexAnt->extraer()) {
                        $registros++;
                        switch ($row['tipoMovimiento'] . $row['ger_id'] . $row['cgp_id'] . $row['rub_id'] . $row['cgcu_id'] . $row['cgsc_id']) {
                            case '+21194':
                                //origen
                                if ($row['ORIGEN_ANT'] <> 0) {
                                    $anticipos .= $row['numcontrol'] . ',';
                                }
                                break;
                        }
                    }
                    $anticipos = trim($anticipos, ',');

                    $conexAntipos = new AccesoDatos();
                    $conexAntipos->conectar();
                    $conexAntipos->setSql('
                        SELECT 
                            ORI.gpo_id AS gpo_idO,ORI.ur_id AS ur_idO,ORI.clc_id AS clc_idO,ORI.afe_id AS afe_idO,ORI.ncc_nccamortiza AS numcontrolO,ORI.percepciones AS percepcionesO,ORI.retenciones AS retencionesO,ORI.tipoMovimiento AS tipoMovimientoO,ORI.Movimiento AS MovimientoO,ORI.cuenta as cuentaO,
                            PAG.gpo_id AS gpo_idP,PAG.ur_id AS ur_idP,PAG.clc_id AS clc_idP,PAG.afe_id AS afe_idP,PAG.ncc_id AS numcontrolP ,PAG.ncc_percepcion AS percepcionesP,PAG.ncc_retencion AS retencionesP,PAG.tipoMovimiento AS tipoMovimientoP,PAG.Movimiento AS MovimientoP,PAG.cuenta as cuentaP,
                            RCT.gpo_id AS gpo_idR,RCT.ur_id AS ur_idR,RCT.clc_id AS clc_idR,RCT.afe_id AS afe_idR,ncc_nccamortiza AS numcontrolR,RCT.percepciones AS percepcionesR,RCT.retenciones AS retencionesR,RCT.tipoMovimiento AS tipoMovimientoR,RCT.Movimiento AS MovimientoR,RCT.clave as claveR,
                            AMT.gpo_id AS gpo_idA,AMT.ur_id AS ur_idA,AMT.clc_id AS clc_idA,AMT.afe_id AS afe_idA,ncc_nccamortiza AS numcontrolA,AMT.percepciones AS percepcionesA,AMT.retenciones AS retencionesA,AMT.tipoMovimiento AS tipoMovimientoA,AMT.Movimiento AS MovimientoA,AMT.cuenta as cuentaA,
                            AMD.gpo_id AS gpo_idD,AMD.ur_id AS ur_idD,AMD.clc_id AS clc_idD,AMD.afe_id AS afe_idD,ncc_nccamortiza AS numcontrolD,AMD.percepciones AS percepcionesD,AMD.retenciones AS retencionesD,AMD.tipoMovimiento AS tipoMovimientoD,AMD.Movimiento AS MovimientoD,AMD.cuenta as cuentaD
                        FROM ( 
                                #EN ESTA PARTE SE ESTAN MOSTRANDO LOS NUMEROS DE CONTROL ORIGINALES 
                                SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                    FROM scp_Contable
                                    WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND clc_id=' . $clc_id . '  AND ncc_id IN(' . $anticipos . ')
                        )ORI
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRAN LOS PAGADOS 
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id,ncc_percepcion,ncc_retencion,ncc_nccamortiza,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                FROM scp_Contable
                                WHERE ncc_gpo=' . $gpo_id . ' AND ncc_ur=' . $ur_id . ' AND ncc_id IN(' . $in . ')
                        )PAG USING(ncc_nccamortiza)
                        RIGHT JOIN
                        (
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,ncc_id AS ncc_nccamortiza, ncc_percepcion AS percepciones,ncc_retencion AS retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento
                                FROM scp_RectificacionContable
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' AND ncc_id IN (' . $anticipos . ')
                        )RCT USING(ncc_nccamortiza)
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ESTAN AMORTIZANDO
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                            FROM scp_Contable 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $gpo_id . ' AND ncc_id IN(' . $anticipos . ')
                        )AMT USING(ncc_nccamortiza)
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ES EL ANTICIPO
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                            FROM scp_Contable 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND ncc_nccamortiza IN(' . $anticipos . ')
                        )AMD USING(ncc_nccamortiza)
                    ');
                    $conexAntipos->consultar();
                    $conexAntipos->desconectar();
                    $cont2 = $cont = 0;
                    $html = '';
                    $html .= '
                        <div class="col-md-12" style="overflow-x: auto">
                        <table class="table table-hover table-bordered">' .
                            '<tr>
                                <th style="text-align:center" class="info" colspan="7">ORIGEN</th>
                                <th style="text-align:center" class="info" colspan="7">PAGADORA</th>
                                <th style="text-align:center" class="info" colspan="7">RECTIFICACIONES</th>
                                <th style="text-align:center" class="info" colspan="7">ANTICIPO</th>
                                <th style="text-align:center" class="info" colspan="7">AMORTIZADORA</th>
                            </tr>
                            <tr class="success">
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                    
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                    
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>

                            </tr>
                        ';
                    while ($row = $conexAntipos->extraer()) {

                        switch ($row['MovimientoO']) {
                            case 'P':
                                if ($row['percepcionesO'] - $row['percepcionesR'] < $row['percepcionesP']) {
                                    $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                    $cont += 1;
                                } else {
                                    $clase = 'class="info"';
                                    $clase2 = 'class="warning"';
                                }

                                break;
                            case 'C':
                                switch ($row['tipoMovimientoO']) {
                                    case '+':
                                        if ($row['percepcionesO'] - $row['percepcionesR'] < $row['retencionesP']) {
                                            $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                        }
                                        break;
                                    case '-':
                                        if ($row['percepcionesO'] - $row['percepcionesR'] < $row['retencionesP']) {
                                            $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                        }

                                        break;
                                }
                                break;
                        }
                        /*Para validar lo amortizado con los anticipos*/
                        switch ($row['cuentaA']) {
                            case 21194:
                                if ($row['percepcionesA'] >= $row['retencionesD'] && $row['percepcionesD'] != 0) {
                                    $clase3 = $clase4 = 'BGCOLOR="#FFFF00"';
                                    $cont2 += 1;
                                } else {
                                    $clase3 = 'class="info"';
                                    $clase4 = 'class="active"';
                                }
                                break;
                        }

                        $html .= '
                            <tr>
                                <td class="info"><b>' . $row['gpo_idO'] . $row['ur_idO'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idO'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idO'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolO'] . '</b></td>
                                <td class="info"><b>' . $row['cuentaO'] . '</b></td>
                                <td class="info"><b>' . number_format($row['percepcionesO'], 2) . '</b></td>
                                <td class="info"><b>' . number_format($row['retencionesO'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . $row['gpo_idP'] . $row['ur_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['clc_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['afe_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['numcontrolP'] . '</b></td>
                                <td class="warning"><b>' . $row['cuentaP'] . '</b></td>
                                <td class="warning"><b>' . number_format($row['percepcionesP'], 2) . '</b></td>
                                <td class="warning"><b>' . number_format($row['retencionesP'], 2) . '</b></td>
                                
                    
                                <td class="info"><b>' . $row['gpo_idR'] . $row['ur_idR'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idR'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idR'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolR'] . '</b></td>
                                <td class="info"><b>' . $row['claveR'] . '</b></td>
                                <td class="info"><b>' . number_format($row['percepcionesR'], 2) . '</b></td>
                                <td class="info"><b>' . number_format($row['retencionesR'], 2) . '</b></td>
                                
                                <td class="active"><b>' . $row['gpo_idA'] . $row['ur_idA'] . '</b></td>
                                <td class="active"><b>' . $row['clc_idA'] . '</b></td>
                                <td class="active"><b>' . $row['afe_idA'] . '</b></td>
                                <td class="active"><b>' . $row['numcontrolA'] . '</b></td>
                                <td class="active"><b>' . $row['cuentaA'] . '</b></td>
                                <td class="active"><b>' . number_format($row['percepcionesA'], 2) . '</b></td>
                                <td class="active"><b>' . number_format($row['retencionesA'], 2) . '</b></td>
                                
                                <td><b>' . $row['gpo_idD'] . $row['ur_idD'] . '</b></td>
                                <td><b>' . $row['clc_idD'] . '</b></td>
                                <td><b>' . $row['afe_idD'] . '</b></td>
                                <td><b>' . $row['numcontrolD'] . '</b></td>
                                <td><b>' . $row['cuentaD'] . '</b></td>
                                <td><b>' . number_format($row['percepcionesD'], 2) . '</b></td>
                                <td><b>' . number_format($row['retencionesD'], 2) . '</b></td>
                            </tr>
                        ';
                    }

                    $html .= '<tr class="success"><td colspan="21"><strong>Tiene(s) ' . $cont . ' número(s) de control que debes rectificar </strong></td><td colspan="21"><strong>Tienes ' . $cont2 . ' números de control donde el anticipo es mayor a lo amortizado</strong></td></tr>
                    </table></div>';
                    if ($cont > 0 || $cont2 > 0) {
                        return $html;
                    } else {
                        return '';
                    }

                    break;
                    /*Este tipo de CLC ACUERDOS DE MINISTRACION GENERA LOS CREDITOS PUENTE  */
                case 'D':
                    $conexMinis = new AccesoDatos();
                    $conexMinis->conectar();
                    $conexMinis->setSql('
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,"+" as tipoMovimiento,ncp_id AS numcontrol,ncp_percepcionImporteBruto AS percepciones,0 retenciones,
                            "NORMAL " amortizado,"" ger_id,"" cgp_id,"" rub_id,"" cgcu_id,"" cgsc_id,"" ORIGEN_CRE
                            FROM scp_RectificacionPresupuestal 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' ' . $np . '
                        UNION ALL 
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,c.ncc_tipoMovimiento as tipoMovimiento,ncc_id AS numcontrol,b.ncc_percepcion AS percepciones,b.ncc_retencion AS retenciones,
                            IF(ncc_amortizado=1,"ORIGEN",IF(ncc_nccamortiza>0,"PAGADORA","NORMAL")) AS amortizado,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,
                            IF( b.ncc_tipoMovimiento="+",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 9 , IF(cgsc_id = 2 , c.ncc_percepcion ,0) ,0) ,0)  ,0) ,0 ),0)ORIGEN_CRE
                            FROM scp_RectificacionContable b
                            LEFT JOIN scp_Contable c  USING(gpo_id,ur_id,clc_id,afe_id,ncc_id) 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . '  AND rct_id=' . $rct_id . ' ' . $nc . '
                    ');

                    $conexMinis->consultar();
                    $conexMinis->desconectar();

                    $credito = '';
                    $registros = 0;
                    while ($row = $conexMinis->extraer()) {
                        $registros++;
                        switch ($row['tipoMovimiento'] . $row['ger_id'] . $row['cgp_id'] . $row['rub_id'] . $row['cgcu_id'] . $row['cgsc_id']) {
                            case '+21192':
                                //amortizadora
                                if ($row['ORIGEN_CRE'] <> 0) {
                                    $credito .= $row['numcontrol'] . ',';
                                }
                                break;
                        }
                    }
                    $credito = trim($credito, ',');
                    if ($credito == '') {
                        $credito = 0;
                    }
                    $conexCred = new AccesoDatos();
                    $conexCred->conectar();
                    $conexCred->setSql('
                        SELECT 
                            ORI.gpo_id AS gpo_idO,ORI.ur_id AS ur_idO,ORI.clc_id AS clc_idO,ORI.afe_id AS afe_idO,ORI.ncc_nccamortiza AS numcontrolO,ORI.percepciones AS percepcionesO,ORI.retenciones AS retencionesO,ORI.tipoMovimiento AS tipoMovimientoO,ORI.Movimiento AS MovimientoO,ORI.cuenta as cuentaO,
                            PAG.gpo_id AS gpo_idP,PAG.ur_id AS ur_idP,PAG.clc_id AS clc_idP,PAG.afe_id AS afe_idP,PAG.ncc_id AS numcontrolP ,PAG.ncc_percepcion AS percepcionesP,PAG.ncc_retencion AS retencionesP,PAG.tipoMovimiento AS tipoMovimientoP,PAG.Movimiento AS MovimientoP,PAG.cuenta as cuentaP,
                            RCT.gpo_id AS gpo_idR,RCT.ur_id AS ur_idR,RCT.clc_id AS clc_idR,RCT.afe_id AS afe_idR,ncc_nccamortiza AS numcontrolR,RCT.percepciones AS percepcionesR,RCT.retenciones AS retencionesR,RCT.tipoMovimiento AS tipoMovimientoR,RCT.Movimiento AS MovimientoR,RCT.clave as claveR,
                            AMT.gpo_id AS gpo_idA,AMT.ur_id AS ur_idA,AMT.clc_id AS clc_idA,AMT.afe_id AS afe_idA,ncc_nccamortiza AS numcontrolA,AMT.percepciones AS percepcionesA,AMT.retenciones AS retencionesA,AMT.tipoMovimiento AS tipoMovimientoA,AMT.Movimiento AS MovimientoA,AMT.cuenta as cuentaA,
                            AMD.gpo_id AS gpo_idD,AMD.ur_id AS ur_idD,AMD.clc_id AS clc_idD,AMD.afe_id AS afe_idD,ncc_nccamortiza AS numcontrolD,AMD.percepciones AS percepcionesD,AMD.retenciones AS retencionesD,AMD.tipoMovimiento AS tipoMovimientoD,AMD.Movimiento AS MovimientoD,AMD.cuenta as cuentaD
                                                
                        FROM ( 
                            #EN ESTA PARTE SE ESTAN MOSTRANDO LOS NUMEROS DE CONTROL ORIGINALES 
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                FROM scp_Contable
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . '  AND clc_id=' . $clc_id . '  AND ncc_id IN(' . $credito . ')
                        )ORI
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRAN LOS PAGADOS 
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id,ncc_percepcion,ncc_retencion,ncc_nccamortiza,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                                FROM scp_Contable
                                WHERE ncc_gpo=' . $gpo_id . ' AND ncc_ur=' . $ur_id . ' AND ncc_id IN(' . $in . ')
                        )PAG USING(ncc_nccamortiza)
                        RIGHT JOIN
                        (
                            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,ncc_id AS ncc_nccamortiza, ncc_percepcion AS percepciones,ncc_retencion AS retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento
                                FROM scp_RectificacionContable
                                WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND rct_id=' . $rct_id . ' AND ncc_id IN (' . $credito . ')
                        )RCT USING(ncc_nccamortiza)
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ESTAN AMORTIZANDO
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_id AS ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                            FROM scp_Contable 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND ncc_id IN(' . $credito . ')
                        )AMT USING(ncc_nccamortiza)
                        LEFT JOIN
                        (
                            #EN ESTA PARTE SE MUESTRA LOS NUMEROS DE CONTROL QUE ES EL ANTICIPO
                            SELECT gpo_id,ur_id,clc_id,afe_id,ncc_nccamortiza,ncc_percepcion AS percepciones,ncc_retencion retenciones,ncc_tipoMovimiento AS tipoMovimiento,"C" Movimiento,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as cuenta
                            FROM scp_Contable 
                            WHERE gpo_id=' . $gpo_id . ' AND ur_id=' . $ur_id . ' AND ncc_nccamortiza IN(' . $credito . ')
                        )AMD USING(ncc_nccamortiza)
                    ');
                    $conexCred->consultar();
                    $conexCred->desconectar();
                    $cont2 = $cont = 0;
                    $html = '';
                    $html .= '
                        <div class="col-md-12" style="overflow-x: auto">
                        <table class="table table-hover table-bordered">' .
                            '<tr>
                                <th style="text-align:center" class="info" colspan="7">ORIGEN</th>
                                <th style="text-align:center" class="info" colspan="7">PAGADORA</th>
                                <th style="text-align:center" class="info" colspan="7">RECTIFICACIONES</th>
                                <th style="text-align:center" class="info" colspan="7">ANTICIPO</th>
                                <th style="text-align:center" class="info" colspan="7">AMORTIZADORA</th>
                            </tr>
                            <tr class="success">
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                    
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CLAVE</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>
                                    
                                <th>UR </th>
                                <th>CLC</th>
                                <th>AFEC.</th>
                                <th>CONTROL</th>
                                <th>CUENTA</th>
                                <th>PERCEPCION</th>
                                <th>RETENCION</th>

                            </tr>
                    ';

                    while ($row = $conexCred->extraer()) {
                        switch ($row['MovimientoO']) {
                            case 'P':
                                if ($row['percepcionesO'] - $row['percepcionesR'] < $row['percepcionesP']) {
                                    $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                    $cont += 1;
                                } else {
                                    $clase = 'class="info"';
                                    $clase2 = 'class="warning"';
                                }
                                break;
                            case 'C':
                                switch ($row['tipoMovimientoO']) {
                                    case '-':
                                        if ($row['percepcionesO'] - $row['percepcionesR'] < $row['retencionesP']) {
                                            $clase = $clase2 = 'BGCOLOR="#FF0000"';
                                            $cont += 1;
                                        } else {
                                            $clase = 'class="info"';
                                            $clase2 = 'class="warning"';
                                        }

                                        break;
                                }
                                break;
                        }
                        /*Para validar lo amortizado con los anticipos*/
                        switch ($row['cuentaA']) {
                            case 21192:
                                if ($row['percepcionesA'] >= $row['retencionesD'] && $row['percepcionesD'] != 0) {
                                    $clase3 = $clase4 = 'BGCOLOR="#FFFF00"';
                                    $cont2 += 1;
                                } else {
                                    $clase3 = 'class="info"';
                                    $clase4 = 'class="active"';
                                }
                                break;
                        }

                        $html .= '
                            <tr>
                                <td class="info"><b>' . $row['gpo_idO'] . $row['ur_idO'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idO'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idO'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolO'] . '</b></td>
                                <td class="info"><b>' . $row['cuentaO'] . '</b></td>
                                <td class="info"><b>' . number_format($row['percepcionesO'], 2) . '</b></td>
                                <td class="info"><b>' . number_format($row['retencionesO'], 2) . '</b></td>
                                
                                <td class="warning"><b>' . $row['gpo_idP'] . $row['ur_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['clc_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['afe_idP'] . '</b></td>
                                <td class="warning"><b>' . $row['numcontrolP'] . '</b></td>
                                <td class="warning"><b>' . $row['cuentaP'] . '</b></td>
                                <td class="warning"><b>' . number_format($row['percepcionesP'], 2) . '</b></td>
                                <td class="warning"><b>' . number_format($row['retencionesP'], 2) . '</b></td>
                                
                                                        
                                <td class="info"><b>' . $row['gpo_idR'] . $row['ur_idR'] . '</b></td>
                                <td class="info"><b>' . $row['clc_idR'] . '</b></td>
                                <td class="info"><b>' . $row['afe_idR'] . '</b></td>
                                <td class="info"><b>' . $row['numcontrolR'] . '</b></td>
                                <td class="info"><b>' . $row['claveR'] . '</b></td>
                                <td class="info"><b>' . number_format($row['percepcionesR'], 2) . '</b></td>
                                <td class="info"><b>' . number_format($row['retencionesR'], 2) . '</b></td>
                                
                                <td class="active"><b>' . $row['gpo_idA'] . $row['ur_idA'] . '</b></td>
                                <td class="active"><b>' . $row['clc_idA'] . '</b></td>
                                <td class="active"><b>' . $row['afe_idA'] . '</b></td>
                                <td class="active"><b>' . $row['numcontrolA'] . '</b></td>
                                <td class="active"><b>' . $row['cuentaA'] . '</b></td>
                                <td class="active"><b>' . number_format($row['percepcionesA'], 2) . '</b></td>
                                <td class="active"><b>' . number_format($row['retencionesA'], 2) . '</b></td>
                                
                                <td><b>' . $row['gpo_idD'] . $row['ur_idD'] . '</b></td>
                                <td><b>' . $row['clc_idD'] . '</b></td>
                                <td><b>' . $row['afe_idD'] . '</b></td>
                                <td><b>' . $row['numcontrolD'] . '</b></td>
                                <td><b>' . $row['cuentaD'] . '</b></td>
                                <td><b>' . number_format($row['percepcionesD'], 2) . '</b></td>
                                <td><b>' . number_format($row['retencionesD'], 2) . '</b></td>
                            </tr>
                        ';
                    }

                    $html .= '
                        <tr class="success"><td colspan="21"><strong>Tiene(s) ' . $cont . ' número(s) de control que debes rectificar </strong></td><td colspan="21"><strong>Tienes ' . $cont2 . ' números de control donde la amortización es mayor al anticipo</strong></td></tr>
                        </table></div>
                    ';

                    if ($cont > 0 || $cont2 > 0) {
                        return $html;
                    } else {
                        return '';
                    }

                    break;
                case 'W':
                    $conexMinis = new AccesoDatos();
                    $conexMinis->conectar();
                    $conexMinis->setSql('
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave AS clave,"+" as tipoMovimiento,ncp_id AS numcontrol,ncp_percepcionImporteBruto AS percepciones,0 retenciones,
                            "NORMAL " amortizado,"" ger_id,"" cgp_id,"" rub_id,"" cgcu_id,"" cgsc_id,"" ORIGEN_CRE
                            FROM scp_RectificacionPresupuestal 
                            WHERE gpo_id=4 AND ur_id=70 AND rct_id=1 AND rcp_editado=1 
                        UNION ALL 
                        SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave AS clave,c.ncc_tipoMovimiento as tipoMovimiento,ncc_id AS numcontrol,b.ncc_percepcion AS percepciones,b.ncc_retencion AS retenciones,
                            IF(ncc_amortizado=1,"ORIGEN",IF(ncc_nccamortiza>0,"PAGADORA","NORMAL")) AS amortizado,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,
                            IF( b.ncc_tipoMovimiento="+",  IF(ger_id = 2,IF(cgp_id = 1 , IF(rub_id = 1 ,IF(cgcu_id = 9 , IF(cgsc_id = 2 , c.ncc_percepcion ,0) ,0) ,0)  ,0) ,0 ),0)ORIGEN_CRE
                            FROM scp_RectificacionContable b
                            LEFT JOIN scp_Contable c  USING(gpo_id,ur_id,clc_id,afe_id,ncc_id) 
                            WHERE gpo_id=4 AND ur_id=70 AND rct_id=1 AND rcc_editado=1
                    ');
                    $conexMinis->consultar();
                    $conexMinis->desconectar();
                    break;
                case 'X':
                    break;
            }

        } else {
            $html = '
                <div class="alert alert-danger"  style="height: 100px;">
                <p class="text-center">
                <strong>
                    LA RECTIFICACIÓN ' . $rct_id . ' NO TIENE NÚMEROS DE CONTROL EDITADOS
                </strong></p>
                </div>
            ';
            return $html;
        }
    } // fin funcion
}
