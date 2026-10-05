<?php

// session_start();
require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';
class ValidaGarantias extends AccesoDatos
{
    /*------------------------- VALIDA LAS GARANTIAS DE UN CONTRATO-------------------------------------------------------------------------*/
    public static function ValidaGarantia($epg_id, $tcl_id, $stc_id, $ipu_id = '')
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $retorno = new stdClass();
        $oa_nombre = '';
        $anticipos = null;
        $importeAnticipo = '';
        $importeAnticipado = '';

        if ($epg_id == '') {
            $retorno->mensaje = 'Error en el encuadre programatico';
            $retorno->valido = false;
            $retorno->error = 0;
            return $retorno;
        }

        if ($tcl_id != 'F' && $tcl_id != 'G' && $tcl_id != 'H') {
            $retorno->mensaje = 'El tipo de CLC no es de contrato';
            $retorno->valido = false;
            $retorno->error = 1;
            return $retorno;
        }

        if ($tcl_id == 'H' && $stc_id == '01') {
            $retorno->mensaje = 'No necesita Garantias';
            $retorno->valido = true;
            $retorno->error = 0;
            return $retorno;
        }

        if ($ipu_id == '') {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $sql = '
                SELECT
                    ipu_id,
                    oa_nombre,
                    ipu_porcentajeAnticipo,
                    ipu_importeAnticipo
                FROM enc_InversionObra
                    INNER JOIN sinpres.cat_InversionPublica USING (ipu_id)
                    INNER JOIN enc_Programatico USING (gpo_id,ur_id,epg_id)
                    INNER JOIN cat_ObraActividad USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND epg_id =  ?';
            $params = [$gpo_id, $ur_id, $epg_id];
            $conexion->setSql($sql);
            $conexion->consultar($params);
            switch ($conexion->getNumeroRegistros()) {
                case 0:
                    $retorno->mensaje = 'La obra no tiene Contratos Vinculados';
                    $retorno->valido = false;
                    $retorno->error = 2;
                    $conexion->desconectar();
                    unset($conexion);
                    return $retorno;
                    break;
                case 1:
                    $n = $conexion->extraer();
                    $ipu_id = $n['ipu_id'];
                    $oa_nombre = $n['oa_nombre'];
                    if ($n['ipu_porcentajeAnticipo'] > 0 && $n['ipu_importeAnticipo'] > 0) {
                        $anticipos = 1;
                        $importeAnticipo = $n['ipu_importeAnticipo'];
                    }
                    $conexion->desconectar();
                    unset($conexion);
                    break;
                default:
                    $retorno->mensaje = 'No especifico el contrato y la obra tiene mas de un contrato vinculados';
                    $retorno->valido = false;
                    $retorno->error = 3;
                    $conexion->desconectar();
                    unset($conexion);
                    return $retorno;
                    break;
            }
        } else {
            $sql = '
                SELECT
                    ipu_id,
                    oa_nombre,
                    ipu_porcentajeAnticipo,
                    ipu_importeAnticipo
                FROM enc_InversionObra
                    INNER JOIN sinpres.cat_InversionPublica USING (ipu_id)
                    INNER JOIN enc_Programatico USING (gpo_id,ur_id,epg_id)
                    INNER JOIN cat_ObraActividad USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND ipu_id = ? ';
            $params = [$gpo_id, $ur_id, $ipu_id];
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($params);
            $n = $conexion->extraer();
            $oa_nombre = $n['oa_nombre'];
            if ($n['ipu_porcentajeAnticipo'] > 0 && $n['ipu_importeAnticipo'] > 0) {
                $anticipos = 1;
                $importeAnticipo = $n['ipu_importeAnticipo'];
            }
            $conexion->desconectar();
        }
        /************* VER SI TIENE ANTICIPOS *******************/
        $sql = '
            SELECT
                gpo_id,
                ur_id,
                ipu_id,
                SUM(clc_percepcion) AS Anticipo
            FROM scp_Clc
                INNER JOIN cat_ObraClc USING (gpo_id,ur_id,clc_id)
                INNER JOIN enc_InversionObra USING (gpo_id,ur_id,epg_id,ipu_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                AND ipu_id = ?
                AND tcl_id = "H"
                AND ecl_id NOT IN(15,17);
        ';
        $params = [$gpo_id, $ur_id, $ipu_id];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $m = $conexion->extraer();
        $importeAnticipado = $m['Anticipo'];
        $conexion->desconectar();
        unset($conexion);

        if ($importeAnticipo < $importeAnticipado) {
            $retorno->advertencia = 'LOS ANTICIPOS SOLICITADOS SON MAYORES A LOS DECLARADOS EN EL CONTRATO<br> Anticipo del Contrato $';
            $retorno->advertencia .= number_format(floatval($importeAnticipo), 2) . ' - Anticipos Solicitados $' . number_format(floatval($importeAnticipado), 2);
            $retorno->advertencia .= ' Diferencia: $' . number_format((floatval($importeAnticipado) - floatval($importeAnticipo)), 2);
        } else {
            $retorno->advertencia = false;
        }
        /************** VALIDACION DE GARANTIAS DEL CONTRATO    *********/
        $Cum = new stdClass();
        $Ant = new stdClass();
        $Vic = new stdClass();

        $sql2 = '
            SELECT ipu_id, tga_id, stg_id, COUNT(*) AS Cantidad
            FROM sinpres.enc_ContratoGarantia
                INNER JOIN sinpres.cat_BancoGarantia USING (bga_id)
            WHERE ipu_id = ?
            GROUP BY tga_id,stg_id;
        ';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql2);
        $conexion->consultar([$ipu_id]);
        $dat = $conexion->extraer();
        do {
            switch ($dat['tga_id']) {
                case 'C':
                    $Cum->{$dat['stg_id']} = 1;
                    //$Cum->$dat = ['stg_id' => 1];
                    break;
                case 'A':
                    $Ant->{$dat['stg_id']} = 1;
                    //$Ant->$dat = ['stg_id' => 1];
                    break;
                case 'V':
                    $Vic->{$dat['stg_id']} = 1;
                    //$Vic->$dat = ['stg_id' => 1];
                    break;
            }
        } while ($dat = $conexion->extraer());
        $conexion->desconectar();
        unset($conexion);

        /*--------------- VALIDAR EL CONTEO DE LAS GARANTIAS  -----------------------*/
        $c = count((array) $Cum) == 0 ? false : count((array) $Cum);
        $v = count((array) $Vic) == 0 ? false : count((array) $Vic);
        $a = count((array) $Ant) == 0 ? false : count((array) $Ant);

        $retorno->Anticipo = $a;
        $retorno->Cumplimiento = $c;
        $retorno->ViciosOcultos = $v;

        if ($c == false && $a == false && $v == false) {
            $retorno->mensaje = 'EL CONTRATO ' . $ipu_id . ' NO TIENE GARANTIAS';
            $retorno->error = 5;
            $retorno->valido = false;
            return $retorno;
        } else {
            $retorno->Anticipo = $a != false ? true : false;
            $retorno->Cumplimiento = $c != false ? true : false;
            $retorno->ViciosOcultos = $v != false ? true : false;
        }
        /************ VALIDAR LAS GARANTIAS *********************/
        if ($a != false && $a > 1) {
            $retorno->mensaje = 'EL CONTRATO ' . $ipu_id . ' DE LA OBRA ' . $oa_nombre . ' TIENE MAS DE UNA GARANTIA DE ANTICIPO, SOLO PUEDE TENER POLIZA DE FIANZA O BILLETE DE DEPOSITO O CHEQUE CERTIFICADO';
            $retorno->error = 8;
            $retorno->valido = false;
            return $retorno;
        }
        if ($c != false && $c > 1) {
            $retorno->mensaje = 'EL CONTRATO ' . $ipu_id . ' DE LA OBRA ' . $oa_nombre . ' TIENE MAS DE UNA GARANTIA DE CUMPLIMIENTO, SOLO PUEDE TENER POLIZA DE FIANZA O BILLETE DE DEPOSITO O CHEQUE CERTIFICADO O EXIMICIÓN DE FIANZA';
            $retorno->error = 9;
            $retorno->valido = false;
            return $retorno;
        }
        if ($v != false && $v > 1) {
            $retorno->mensaje = 'EL CONTRATO ' . $ipu_id . ' DE LA OBRA ' . $oa_nombre . ' TIENE MAS DE UNA GARANTIA DE VICIOS OCULTOS, SOLO PUEDE TENER POLIZA DE FIANZA O BILLETE DE DEPOSITO O CHEQUE CERTIFICADO O EXIMICIÓN DE FIANZA';
            $retorno->error = 10;
            $retorno->valido = false;
            return $retorno;
        }
        /******************** VALIDA SUBTIPO DE CLC CON GARANTIAS ***************/
        switch ($tcl_id) {
            case 'G':
                if ($stc_id != 01) {
                    $retorno->valido = false;
                    $retorno->error = 14;
                    $retorno->mensaje = 'EL SUBTIPO USADO  NO NECESITA DE UN CONTRATO';
                    return $retorno;
                } elseif ($c == false) {
                    $retorno->valido = false;
                    $retorno->error = 13;
                    $retorno->mensaje = 'EL CONTRATO NO TIENE LA GARANTIA DE CUMPLIMIENTO NECESARIO PARA LA CLC DE ADMINISTRACIÓN DIRECTA';
                    return $retorno;
                } elseif ($c != false && $stc_id == 01) {
                    $retorno->valido = true;
                    $retorno->mensaje = 'LAS GARANTIAS SON CORRECTAS';
                    return $retorno;
                }
                break;
            case 'F':
                switch ($stc_id) {
                    case 00:
                        if ($anticipos == 0) {
                            if ($c == false) {
                                $retorno->valido = false;
                                $retorno->error = 11;
                                $retorno->mensaje = 'EL CONTRATO NO CUMPLE CON LA GARANTIA DE CUMPLIMIENTO';
                                return $retorno;
                            } else {
                                $retorno->valido = true;
                                $retorno->mensaje = 'EL CONTRATO TIENE GARANTIA DE CUMPLIMIENTO';
                            }
                        } else {
                            if ($c != false && $a != false) {
                                $retorno->valido = true;
                                $retorno->mensaje = 'EL CONTRATO CUMPLE CON LAS GARANTIAS PARA ESTIMACION -Anticipo,Cumplimiento';
                                return $retorno;
                            } else {
                                $retorno->valido = false;
                                $retorno->error = 12;
                                $retorno->mensaje = 'EL CONTRATO NO CUMPLE CON LAS GARANTIAS PARA ESTIMACION -Anticipo,Cumplimiento';
                                return $retorno;
                            }
                        }
                        break;
                    case 01:
                        if ($anticipos == 0) {
                            if ($c == false || $v == false) {
                                $retorno->valido = false;
                                $retorno->error = 6;
                                $retorno->mensaje = 'EL CONTRATO NO CUMPLE CON LAS GARANTIAS PARA UN FINIQUITO';
                                return $retorno;
                            } else {
                                $retorno->valido = true;
                                $retorno->mensaje = 'EL CONTRATO TIENE GARANTIA DE CUMPLIMIENTO Y VICIOS OCULTOS';
                            }
                        } else {
                            if ($c == false || $v == false || $a == false) {
                                $retorno->valido = false;
                                $retorno->error = 7;
                                $retorno->mensaje = 'EL CONTRATO NO CUMPLE CON LAS GARANTIAS PARA UN FINIQUITO -Anticipo,Cumplimiento y Vicios Ocultos';
                                return $retorno;
                            } else {
                                $retorno->valido = true;
                                $retorno->mensaje = 'EL CONTRATO TIENE GARANTIA DE CUMPLIMIENTO,VICIOS OCULTOS Y ANTICIPO';
                            }
                        }
                        break;
                    default:
                        $retorno->mensaje = 'ERROR EN EL SUBTIPO DE CLC, NO APLICA LAS GARANTIAS';
                        $retorno->valido = false;
                        $retorno->error = 4;
                        return $retorno;
                        break;
                }
                break;
            case 'H':
                if ($stc_id == 00 && ($c == false || $a == false)) {
                    $retorno->mensaje = 'EL CONTRATO NO CUMPLE CON LAS GARANTIAS DE CUMPLIMIENTO Y ANTICIPO';
                    $retorno->valido = false;
                    $retorno->error = 15;
                    return $retorno;
                } elseif ($stc_id != 00) {
                    $retorno->mensaje = 'EL SUBTIPO DE CLC NO APLICA PARA VALIDAR GARANTIAS';
                    $retorno->valido = false;
                    $retorno->error = 15;
                    return $retorno;
                } elseif ($stc_id == 01 && $c != false && $a != false) {
                    $retorno->mensaje = 'LA VALIDACION ES CORRECTA';
                    $retorno->valido = true;
                    return $retorno;
                }
                break;
        }
        return $retorno;
    }
    /*------------------------- OBTIENE INFORMACIÓN TOTAL DE UN COMPROMISO-------------------------------------------------------------------------*/
    public static function gtCompromiso($cmp_id, $act = false, $gpo_id = '', $ur_id = '')
    {
        $compromiso = new stdClass();
        if (empty($gpo_id) && empty($ur_id)) {
            $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
            $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        }

        if ($cmp_id == '' || empty($cmp_id)) {
            $compromiso->error = true;
            return $compromiso;
        }
        $sql = '
            SELECT
                gpo_id,ur_id,
                cmp_id,ue_id,
                epg_id,pg_id,
                pe_id,ecf_id,
                tcp_id,ecm_id,
                rfc_id,rfc_nombre,
                rfc_paterno,rfc_materno,
                cmp_importe,
                tcl_id,stc_id,mno_id
            FROM scp_Compromiso C
                INNER JOIN cat_Proveedor P USING(rfc_id)
                INNER JOIN sinpres.cat_Proveedores USING ( rfc_id )
            WHERE C.gpo_id = ?
                AND C.ur_id = ?
                AND C.cmp_id = ?';
        $params = [$gpo_id, $ur_id, $cmp_id];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $data = $conexion->extraer();
        $conexion->desconectar();

        $compromiso->ue_id = $data['ue_id'];
        $compromiso->epg_id = $data['epg_id'];
        $compromiso->tcl_id = $data['tcl_id'];
        $compromiso->stc_id = $data['stc_id'];
        $compromiso->pg_id = $data['pg_id'];
        $compromiso->pe_id = $data['pe_id'];
        $compromiso->ecf_id = $data['ecf_id'];
        $compromiso->tcp_id = $data['tcp_id'];
        $compromiso->ecm_id = $data['ecm_id'];
        $compromiso->mno_id = $data['mno_id'];

        $compromiso->cmp_importe = round($data['cmp_importe'], 2);

        $compromiso->rfc_id = $data['rfc_id'];
        $compromiso->rfc_nombre = $data['rfc_nombre'];
        $compromiso->rfc_paterno = $data['rfc_paterno'];
        $compromiso->rfc_materno = $data['rfc_materno'];

        $sql = '
            SELECT
                SUM(CASE
                    WHEN cmp_tipoMovimiento = "-"
                        THEN cmp_importe*-1
                        ELSE cmp_importe
                    END) AS importeAjustado
            FROM scp_Compromiso
            WHERE gpo_id = ?
                AND ur_id = ?
                AND cmp_cmp_id = ?
                AND cmp_tipoCompromiso = "A"
                AND ecm_id = "A";
        ';
        $params = [$gpo_id, $ur_id, $cmp_id];
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $data2 = $conexion->extraer();
        $conexion->desconectar();

        $compromiso->ajustado = round($data2['importeAjustado'], 2);

        $sql = '
            SELECT
                SUM(ncp_percepcionNeto) AS Devengado,
                #SUM(IF (imRect IS NULL ,0 ,imRect)) AS Rectificado
                0 AS Rectificado
            FROM scp_Presupuestal
                INNER JOIN scp_Afectacion USING (gpo_id,ur_id,afe_id)
                /* LEFT JOIN (
                    SELECT  gpo_id,ur_id,ncp_id,SUM(ncp_percepcionNeto) AS imRect,rct_id,rct_fechaAutorizacion,rct_justificacion,rct_importe
                    FROM scp_RectificacionPresupuestal
                    INNER JOIN scp_MaestroRectificacion USING(gpo_id,ur_id,rct_id)
                    WHERE gpo_id =' . $gpo_id . ' AND ur_id =' . $ur_id . ' AND ecl_id = 6
                    GROUP BY gpo_id,ur_id,ncp_id
                )R USING  ( gpo_id ,ur_id, ncp_id ) */
            WHERE gpo_id = ?
                AND ur_id = ?
                AND cmp_id = ?
                AND eaf_id = "A";';
        $params = [$gpo_id, $ur_id, $cmp_id];
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $data3 = $conexion->extraer();

        $sql = '
            SELECT
                SUM(ncp_percepcionNeto) AS Usado
            FROM scp_Presupuestal
                INNER JOIN scp_Afectacion USING (gpo_id,ur_id,afe_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                AND cmp_id = ?
                AND eaf_id = "G";
        ';
        $params = [$gpo_id, $ur_id, $cmp_id];
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $data4 = $conexion->extraer();

        $compromiso->devengado = round($data3['Devengado'], 2);
        $compromiso->usado = round($data4['Usado'], 2);
        $compromiso->rectificado = round($data3['Rectificado'], 2);
        $conexion->desconectar();
        return $compromiso;
    }
    /***********************************************************************************************************************************************/
    public static function gtDocumento($bdo_id = '', $rfc_id = '', $referencia = '', $tipo = '', $anio = APP_EJERCICIO)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $table = 'ctrl' . $anio . '.cat_BancoDocumentos';

        if ($gpo_id == '' || $ur_id == '') {
            return false;
        }
        $documento = new stdClass();

        /*------#VALIDAR QUE ENVIEN EL DOCUMENTO POR LO MENOS RFC Y REFERENCIA */
        if ($bdo_id == '' && ($rfc_id == '' || $referencia == '')) {
            $documento->valido = false;
            $documento->error = 1;
            $documento->mensaje = 'NO ES POSIBLE VALIDAR EL DOCUMENTO FALTA LA REFERENCIA O EL RFC';
            return $documento;
        }

        if ($bdo_id == '' && $rfc_id != '' && $referencia != '') {
            $params = [$gpo_id, $ur_id, $rfc_id, $referencia];
            $sql = '
                SELECT
                    *
                FROM ' . $table . '
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND rfc_id = ?
                    AND bdo_referencia = ?';

            if ($tipo != '') {
                $sql .= ' AND tdf_id = ? ';
                array_push($params, $tipo);
            }
        } elseif ($bdo_id != '' && ($rfc_id == '' || $referencia == '')) {
            $params = [$bdo_id];
            $sql = '
                SELECT
                    *
                FROM ' . $table . '
                WHERE bdo_id = ?';
        } elseif ($bdo_id != '' && $rfc_id != '' && $referencia != '') {
            $params = [$gpo_id, $ur_id, $rfc_id, $referencia, $bdo_id];
            $sql = '
                SELECT
                    *
                FROM ' . $table . '
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND rfc_id = ?
                    AND bdo_referencia = ?
                    AND bdo_id = ?';
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);

        /*------#VALIDAR SI EXISTE UN DOCUMENTO QUE SOLO SE DIFERENCIA POR EL TIPO DE DOCUMENTO  */
        if ($conexion->getNumeroRegistros() > 1) {
            $documento->valido = false;
            $documento->error = 2;
            $documento->mensaje = 'EXISTE MAS DE UN DOCUMENTO  DEL PROVEEDOR "' . $rfc_id . '" CON REFERENCIA "' . $referencia . '" POR FAVOR ESPECIFIQUE UN TIPO DE DOCUMENTO';
            $conexion->desconectar();
            unset($conexion);
            return $documento;
        } elseif ($conexion->getNumeroRegistros() == 0) {
            $documento->valido = false;
            $documento->error = 3;
            $documento->mensaje = 'EL DOCUMENTO NO EXISTE CON LOS DATOS PROPORCIONADOS';
            $conexion->desconectar();
            unset($conexion);
            return $documento;
        }

        $documento->valido = true;
        /*------#DATOS GENERALES DEL DOCUMENTO      */
        $n = $conexion->extraer();
        $documento->gpo_id = $n['gpo_id'];
        $documento->ur_id = $n['ur_id'];
        $documento->bdo_id = $n['bdo_id'];
        $documento->sts_id = $n['sts_id'];
        $documento->referencia = $n['bdo_referencia'];
        $documento->importe = $n['bdo_percepcionPresupuestal']; //$n['bdo_importe'] == 0 ? $n['bdo_percepcionPresupuestal'] : $n['bdo_importe'] ;
        $documento->concepto = $n['bdo_concepto'];
        $documento->tdf_id = $n['tdf_id'];
        $documento->percepcionPresupuestal = $n['bdo_percepcionPresupuestal'];
        $documento->percepcionContable = $n['bdo_percepcionContable'];
        $documento->retencionContable = $n['bdo_retencionContable'];
        $documento->estadoPago = $n['bdo_estadoPago'];

        /**
         * Usuario: evenegass
         * Fecha Actualización: 14/11/2018 12:51 PM
         * Descripcion: Comente las siguientes lineas ya en la consulta al asignar el resultado a $n no existe "bdo_fechaPago" ...
         */
        /*            $f = explode("-",substr($n['bdo_fechaPago'],0,10));
        if(count($f)==3)
        $documento->fechaPago     = $f[2]."/".$f[1]."/".$f[0];*/
        $f = explode('-', substr($n['bdo_fechaDocumento'], 0, 10));
        if (count($f) == 3) {
            $documento->fechaDocumento = $f[2] . '/' . $f[1] . '/' . $f[0];
        }

        $documento->fondoRotatorio = $n['bdo_fondoRotatorio'];
        $documento->rectificaciones = $n['bdo_rectificaciones'];
        $documento->estadoSat = $n['bdo_estadoSat'];
        $documento->retenciones = 0; //$n['bdo_retenciones'];
        $documento->impuestos = 0; //$n['bdo_impuestos'];
        $documento->importeRectificado = 0;
        $documento->devengado = 0;
        $documento->referenciado = 0;
        $documento->refPer = 0;
        $documento->refRet = 0;
        $documento->detalleDevengado = [];
        $conexion->desconectar();
        $documento->importeRetR = 0;
        $documento->importePerR = 0;
        unset($conexion);

        /*------# DETALLE DEL PROVEEDOR      --------*/
        $sql = '
            SELECT
                *
            FROM cat_Proveedor
                INNER JOIN sinpres.cat_Proveedores USING (rfc_id)
            WHERE rfc_id = ?';
        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar([$n['rfc_id']]);
        $d = $c->extraer();

        $documento->rfc_id = $n['rfc_id'];
        $documento->nombreProveedor = $d['rfc_nombre'] . ' ' . $d['rfc_paterno'] . ' ' . $d['rfc_materno'];

        unset($d);
        unset($c);
        $recpsql = '';
        $reccsql = '';
        switch ($anio) {
            case 2016:
                $recpsql = '
                        LEFT JOIN (
                            SELECT  gpo_id,ur_id,ncp_id,ncp_percepcionNeto AS rct_importe,rct_id,rct_fechaAutorizacion,rct_justificacion
                                FROM ctrl' . $anio . '.scp_RectificacionPresupuestal
                            INNER JOIN ctrl' . $anio . '.scp_MaestroRectificacion USING(gpo_id,ur_id,rct_id)
                            WHERE 	(gpo_id,ur_id ) IN ( (1,18),(8,01),(' . $gpo_id . ',' . $gpo_id . ') ) AND ecl_id = 6
                        )R USING ( gpo_id,ur_id,ncp_id )';
                $reccsql = '
                        LEFT JOIN (
                            SELECT  gpo_id,ur_id,ncc_id,rct_id,rct_fechaAutorizacion,rct_justificacion,ncc_retencion as RetR, ncc_percepcion AS PerR
                                FROM ctrl' . $anio . '.scp_RectificacionContable
                            INNER JOIN ctrl' . $anio . '.scp_MaestroRectificacion USING(gpo_id,ur_id,rct_id)
                            WHERE 	(gpo_id,ur_id ) IN ( (1,18),(8,01),(' . $gpo_id . ',' . $gpo_id . ') ) AND ecl_id = 6
                        )R USING ( gpo_id,ur_id,ncc_id )';
                break;
            case 2017:
                $recpsql = '
                        LEFT JOIN (
                            SELECT  0 gpo_id, 0 ur_id, 0 ncp_id, 0  AS rct_importe,0 rct_id,"" rct_fechaAutorizacion,"" rct_justificacion
                        )R USING ( gpo_id,ur_id,ncp_id )';
                $reccsql = '
                    LEFT JOIN (
                        SELECT  0 gpo_id, 0 ur_id,0 ncc_id,0 rct_id,"" rct_fechaAutorizacion,"" rct_justificacion,0 AS RetR, 0  AS PerR
                    )R USING ( gpo_id,ur_id,ncc_id )';
                break;
        }

        /*------#CALCULAR EL DEVENGADO DEL DOCUMENTO Y SUS DETALLES    */

        $sql = '
            SELECT
                gpo_id,ur_id,clc_id,afe_id,
                tipo,num_id,concepto,
                ncp_percepcionNeto,Ret,Per,rct_id,rct_importe,RetR,PerR,
                afe_fechaAutorizado,rct_fechaAutorizacion,rct_justificacion,eaf_id,afe_fechaElaboracion
            FROM ctrl' . $anio . '.scp_Afectacion
                INNER JOIN (
                    SELECT
                        gpo_id,ur_id,afe_id,ncp_id AS num_id,"P" AS tipo,
                        ncp_concepto AS concepto, ncp_percepcionNeto,
                        0 Ret,0 Per,0 rct_id, 0 rct_importe,0 RetR,0 PerR, 0 rct_fechaAutorizacion,0 rct_justificacion
                    FROM ctrl' . $anio . '.scp_Presupuestal
                        ' . $recpsql . '
                    WHERE  bdo_id = ' . $documento->bdo_id . '
                    UNION ALL
                    SELECT
                        gpo_id,ur_id,afe_id,ncc_id AS num_id,"C" AS tipo,
                        ncc_concepto AS concepto, 0 ncp_percepcionNeto,
                        ncc_retencion AS Ret,ncc_percepcion AS Per,
                        0 rct_id,0 AS rct_importe,0 RetR, 0 PerR,0 rct_fechaAutorizacion, 0 rct_justificacion
                    FROM ctrl' . $anio . '.scp_Contable
                        ' . $reccsql . '
                    WHERE  bdo_id = ' . $documento->bdo_id . '
                )N USING (gpo_id ,ur_id,afe_id)
            WHERE eaf_id != "C"';

        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar();
        $documento->rectificaciones = false;

        $rct = '';

        /*------#LISTADO DE RECTIFICACIONES INVOLUCRADAS EN EL DOCUMENTO   */
        $documento->devengado = 0;
        $documento->retenD = 0;
        $documento->perceD = 0;

        while ($d = $c->extraer()) {

            $datos = new stdClass();
            $datos->tipo = $d['tipo'];
            $datos->num_id = $d['num_id'];
            $datos->afe_id = $d['afe_id'];
            $datos->clc_id = $d['clc_id'];
            $datos->concepto = $d['concepto'];
            $f = explode('-', substr($d['afe_fechaAutorizado'], 0, 10));
            if (count($f) > 1) {
                $datos->fechaAutorizado = $f[2] . '/' . $f[1] . '/' . $f[0];
            }

            $f = explode('-', substr($d['afe_fechaElaboracion'], 0, 10));
            if (count($f) > 1) {
                $datos->fechaCreacion = $f[2] . '/' . $f[1] . '/' . $f[0];
            }

            $datos->importe = $d['ncp_percepcionNeto'];
            $datos->Percepcion = $d['Per'];
            $datos->Retencion = $d['Ret'];

            $datos->rct_id = $d['rct_id'];
            $datos->Rectificado = $d['rct_importe'];
            $datos->PercepcionR = $d['PerR'];
            $datos->RetencionR = $d['RetR'];

            $datos->estado = $d['eaf_id'];

            $documento->referenciado += ($d['ncp_percepcionNeto'] - $d['rct_importe']);
            $documento->refPer += ($d['Per'] - $d['PerR']);
            $documento->refRet += ($d['Ret'] - $d['RetR']);

            $documento->importeRectificado += round($d['rct_importe'], 2);
            $documento->importeRetR += round($d['RetR'], 2);
            $documento->importePerR += round($d['PerR'], 2);

            if ($d['eaf_id'] == 'A') {
                $documento->devengado += round($d['ncp_percepcionNeto'] - $d['rct_importe'], 2);
                $documento->retenD += round($d['Ret'] - $d['RetR'], 2);
                $documento->perceD += round($d['Per'] - $d['PerR'], 2);
            }

            array_push($documento->detalleDevengado, $datos);
        }
        $c->desconectar();
        unset($c);

        return $documento;
    }
    /***********************************************************************************************************************************************/
    public static function getModificaciones($mov = null, $mod = null, $cusr = null, $cvp = null)
    {
        $retorno = new stdClass();
        $retorno->valido = true;

        if ($mov == null || $mod == null || $cusr == null || $cvp == null) {
            $retorno->valido = false;
            $retorno->error = 0;
            $retorno->mensaje = 'Faltan parametros en la consulta de validación';
            return $retorno;
        }
        $mes = date('m') * 1;
        $trim = null;
        $ti = null;
        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $mesesd = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIMEBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        $trimestre = ['ENERO-FEBRERO-MARZO', 'ABRIL-MAYO-JUNIO', 'JULIO-AGOSTO-SEPTIEMBRE', 'OCTUBRE-NOVIEMBRE-DICIEMBRE'];

        /* OBTENEMOS EL MES Y EL TRIMESTRE ACTUAL DEPENDIENDO DEL $mes */
        switch ($mes) {
            case 1:
            case 2:
            case 3:
                $trim = ['ene', 'feb', 'mar'];
                $ti = 1;
                break;
            case 4:
            case 5:
            case 6:
                $trim = ['abr', 'may', 'jun'];
                $ti = 2;
                break;
            case 7:
            case 8:
            case 9:
                $trim = ['jul', 'ago', 'sep'];
                $ti = 3;
                break;
            case 10:
            case 11:
            case 12:
                $trim = ['oct', 'nov', 'dic'];
                $ti = 4;
                break;
        }
        /* OBTENETMOS EL PRESUPUESTO DE LA CLAVE PRESUPUESTAL DE UNA FUNCION LOCAL  Sol es en este mismo controlador */
        $d = ValidaGarantias::getPresupuestoModificaciones($cvp, 'valida', $mov, '');

        /* SACAMOS LOS RESULTADOS DEL PRESUPUESTO Y LOS GUARDAMOS PARA  SUS VALIDACIONES */

        $pres = new stdClass();
        $pres->com = Math::redondear($d['pre_comprometido'], 2);
        $pres->vig = Math::redondear($d['pre_vigente'], 2);
        $pres->dev = Math::redondear($d['pre_devengado'], 2);

        $pres->devdet = new stdClass();
        $pres->devdetanua = 0;
        $pres->devdet->ene = $d['dev_ene'];
        $pres->devdetanua += $d['dev_ene'];
        $pres->devdet->feb = $d['dev_feb'];
        $pres->devdetanua += $d['dev_feb'];
        $pres->devdet->mar = $d['dev_mar'];
        $pres->devdetanua += $d['dev_mar'];
        $pres->devdet->abr = $d['dev_abr'];
        $pres->devdetanua += $d['dev_abr'];
        $pres->devdet->may = $d['dev_may'];
        $pres->devdetanua += $d['dev_may'];
        $pres->devdet->jun = $d['dev_jun'];
        $pres->devdetanua += $d['dev_jun'];
        $pres->devdet->jul = $d['dev_jul'];
        $pres->devdetanua += $d['dev_jul'];
        $pres->devdet->ago = $d['dev_ago'];
        $pres->devdetanua += $d['dev_ago'];
        $pres->devdet->sep = $d['dev_sep'];
        $pres->devdetanua += $d['dev_sep'];
        $pres->devdet->oct = $d['dev_oct'];
        $pres->devdetanua += $d['dev_oct'];
        $pres->devdet->nov = $d['dev_nov'];
        $pres->devdetanua += $d['dev_nov'];
        $pres->devdet->dic = $d['dev_dic'];
        $pres->devdetanua += $d['dev_dic'];

        $pres->vigdet = new stdClass();
        $pres->vigdetanua = 0;
        $pres->vigdet->ene = $d['vig_ene'];
        $pres->vigdetanua += $d['vig_ene'];
        $pres->vigdet->feb = $d['vig_feb'];
        $pres->vigdetanua += $d['vig_feb'];
        $pres->vigdet->mar = $d['vig_mar'];
        $pres->vigdetanua += $d['vig_mar'];
        $pres->vigdet->abr = $d['vig_abr'];
        $pres->vigdetanua += $d['vig_abr'];
        $pres->vigdet->may = $d['vig_may'];
        $pres->vigdetanua += $d['vig_may'];
        $pres->vigdet->jun = $d['vig_jun'];
        $pres->vigdetanua += $d['vig_jun'];
        $pres->vigdet->jul = $d['vig_jul'];
        $pres->vigdetanua += $d['vig_jul'];
        $pres->vigdet->ago = $d['vig_ago'];
        $pres->vigdetanua += $d['vig_ago'];
        $pres->vigdet->sep = $d['vig_sep'];
        $pres->vigdetanua += $d['vig_sep'];
        $pres->vigdet->oct = $d['vig_oct'];
        $pres->vigdetanua += $d['vig_oct'];
        $pres->vigdet->nov = $d['vig_nov'];
        $pres->vigdetanua += $d['vig_nov'];
        $pres->vigdet->dic = $d['vig_dic'];
        $pres->vigdetanua += $d['vig_dic'];

        $trimUsuario1 = Math::redondear(($cusr->ene + $cusr->feb + $cusr->mar), 2);
        $trimVigente1 = Math::redondear(($pres->vigdet->ene + $pres->vigdet->feb + $pres->vigdet->mar), 2);
        $trimDevengado1 = Math::redondear(($pres->devdet->ene + $pres->devdet->feb + $pres->devdet->mar), 2);

        $trimUsuario2 = Math::redondear(($cusr->abr + $cusr->may + $cusr->jun), 2);
        $trimVigente2 = Math::redondear(($pres->vigdet->abr + $pres->vigdet->may + $pres->vigdet->jun), 2);
        $trimDevengado2 = Math::redondear(($pres->devdet->abr + $pres->devdet->may + $pres->devdet->jun), 2);

        $trimUsuario3 = Math::redondear($cusr->jul + $cusr->ago + $cusr->sep, 2);
        $trimVigente3 = Math::redondear(($pres->vigdet->jul + $pres->vigdet->ago + $pres->vigdet->sep), 2);
        $trimDevengado3 = Math::redondear(($pres->devdet->jul + $pres->devdet->ago + $pres->devdet->sep), 2);

        $trimUsuario4 = Math::redondear(($cusr->oct + $cusr->nov + $cusr->dic), 2);
        $trimVigente4 = Math::redondear(($pres->vigdet->oct + $pres->vigdet->nov + $pres->vigdet->dic), 2);
        $trimDevengado4 = Math::redondear(($pres->devdet->oct + $pres->devdet->nov + $pres->devdet->dic), 2);

        $disponibleT1 = Math::redondear(($trimVigente1 - $trimDevengado1), 2);
        $disponibleT2 = Math::redondear(($trimVigente2 - $trimDevengado2), 2);
        $disponibleT3 = Math::redondear(($trimVigente3 - $trimDevengado3), 2);
        $disponibleT4 = Math::redondear(($trimVigente4 - $trimDevengado4), 2);
        #validar el estado de la clave presupuestal

        #bccomp, si son iguales el valor es 0, si el valor 1 es mayor al valor 2 es = 1 , de lo contrario es -1

        if (bccomp($pres->dev, $pres->devdetanua, 2) != 0) {
            $retorno->valido = false;
            $retorno->error = 1;
            $retorno->mensaje = 'Error en el devengo de la clave presupuestal';
            return $retorno;
        }
        if (bccomp($pres->vig, $pres->vigdetanua, 2) != 0) {
            $retorno->valido = false;
            $retorno->error = 2;
            $retorno->mensaje = 'Error en el vigente de la clave presupuestal';
            return $retorno;
        }

        /*IMPORTE TOTAL DE LA MODIFICACION*/

        $usrImporte = Math::redondear($cusr->importe, 2);

        /* SUMAMOS EL CALENDARIO DEL USUARIO  */
        $usranual = Math::redondear($cusr->ene + $cusr->feb + $cusr->mar + $cusr->abr + $cusr->may + $cusr->jun + $cusr->jul + $cusr->ago + $cusr->sep + $cusr->oct + $cusr->nov + $cusr->dic, 2);

        if (stristr($usranual, 'e')) {
            $usranual = Math::exp_to_dec($usranual);
        }

        //VERIFICAMOS QUE NINGUN MES SE ENCUENTRE EN NEGATIVO
        for ($i = 0; $i <= 11; $i++) {

            if (bccomp($cusr->$meses[$i], 0, 2) < 0) {
                $retorno->valido = false;
                $retorno->error = 46;
                $retorno->mensaje = ' El importe del mes de ' . $mesesd[$i] . ' no puede ser negativo, en la clave ' . $d['pre_claveCompleta'] . '';
                return $retorno;
            }
        }

        //VERIFICAMOS QUE EL IMPORTE TOTAL COINCIDA CON LA SUMA DEL CALENDARIO
        if (bccomp($usranual, $usrImporte, 2) != 0) {
            $retorno->valido = false;
            $retorno->error = 3;
            $retorno->mensaje = 'El importe no coincide con la suma del calendario en la clave: ' . $d['pre_claveCompleta'] . ' ';
            return $retorno;
        }

        /* VALIDAMOS SEGUN EL TIPO DE MOVIMIENTO REDUCCION AMPLIACION CALENDARIZACION   */

        switch ($mov) {
            case 'R':
                $disponibleComprometido = Math::redondear($pres->vig - $pres->com, 2);

                if (bccomp($disponibleComprometido, $usranual, 2) < 0) {
                    $retorno->valido = false;
                    $retorno->error = 4;
                    $retorno->mensaje = 'La reducción excede el comprometido en la clave presupuestal ' . $d['pre_claveCompleta'] . ' ';
                    return $retorno;
                }

                $disponible = Math::redondear($pres->vig - $pres->dev, 2);
                if (bccomp($disponible, $usranual, 2) < 0) {
                    $retorno->valido = false;
                    $retorno->error = 5;
                    $retorno->mensaje = 'La reducción supera el disponible por reducir de manera anual';
                    return $retorno;
                }

                //VALIDAR QUE NO SE REDUSCA MAS DE LO QUE SE TIENE DISPONIBLE EN EL TRIMESTRE Y DESPUES POR MES

                if (
                    bccomp($disponibleT1, $trimUsuario1, 2) < 0 ||
                    bccomp($disponibleT2, $trimUsuario2, 2) < 0 ||
                    bccomp($disponibleT3, $trimUsuario3, 2) < 0 ||
                    bccomp($disponibleT4, $trimUsuario4, 2) < 0
                ) {

                    $retorno->valido = false;
                    $retorno->error = 7;
                    $retorno->mensaje = 'No hay presupuesto disponible para reducir, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                    return $retorno;
                } else { //VALIDAR QUE NO REDUSCA MAS DE LO DISPONIBLE POR MES
                    for ($i = 0; $i <= 11; $i++) {
                        if ($cusr->$meses[$i] != 0 && bccomp(Math::redondear($cusr->$meses[$i], 2), Math::redondear($pres->vigdet->$meses[$i] - $pres->devdet->$meses[$i], 2), 2) == 1) {
                            $retorno->valido = false;
                            $retorno->error = 7;
                            $retorno->mensaje = 'No se puede reducir del mes ' . ($mesesd[$i]) . ' no hay presupuesto para reducir, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                            return $retorno;
                        }
                    }
                }

                /* INTERNAS   */
                if ($mod == 'I') {

                    if ($d['pe_reductible'] == 2 || $d['pe_reductible'] == 3) {

                        $retorno->valido = false;
                        $retorno->error = 36;
                        $retorno->mensaje = 'La partida es intransferible';
                        return $retorno;
                    }

                    /* VALIDAMOS QUE NO HAYA REDUCCIONES EN MESES POSTERIORES AL ACTUAL  */
                    for ($i = $mes; $i <= 11; $i++) {

                        if ($cusr->$meses[$i] != 0) {
                            $retorno->valido = false;
                            $retorno->error = 6;
                            $retorno->mensaje = ' No se puede reducir del mes ' . $mesesd[$i] . ' por ser posterior al actual, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                            return $retorno;
                        }
                    }
                }
                break;
            case 'C':
                /*VALIDAMOS QUE LOS IMPORTES COINCIDAN CON EL VIGENTE */

                if (bccomp($usranual, $pres->vig, 2) != 0) {
                    $retorno->valido = false;
                    $retorno->error = 9;
                    $retorno->mensaje = 'El importe del calendario es incorrecto, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                    return $retorno;
                }

                /*      VALIDAMOS QUE NO DEJE SIN COBERTURA EL DEVENGADO DEL MES */
                for ($i = 0; $i <= 11; $i++) {
                    if (bccomp(Math::redondear($cusr->$meses[$i], 2), Math::redondear($pres->devdet->$meses[$i], 2), 2) < 0) {
                        $retorno->valido = false;
                        $retorno->error = 10;
                        $retorno->mensaje = 'El importe del mes ' . ($mesesd[$i]) . ' es invalido deja sin cobertura el devengado, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                        return $retorno;
                    }
                }
                /* VALIDAMOS QUE NO MUEVA IMPORTES DE OTROS MESES */
                if ($mod == 'I') {
                    $sobrante = 0;
                    $totUsr = 0;
                    $totVig = 0;

                    $c1 = Math::redondear($cusr->ene + $cusr->feb + $cusr->mar, 2);
                    $t1 = Math::redondear($pres->vigdet->ene + $pres->vigdet->feb + $pres->vigdet->mar, 2);
                    $d1 = Math::redondear($pres->devdet->ene + $pres->devdet->feb + $pres->devdet->mar, 2);
                    $s1 = Math::redondear($t1 - $d1, 2);

                    $c2 = Math::redondear($cusr->abr + $cusr->may + $cusr->jun, 2);
                    $t2 = Math::redondear($pres->vigdet->abr + $pres->vigdet->may + $pres->vigdet->jun, 2);
                    $d2 = Math::redondear($pres->devdet->abr + $pres->devdet->may + $pres->devdet->jun, 2);
                    $s2 = Math::redondear($t2 - $d2, 2);

                    $c3 = Math::redondear($cusr->jul + $cusr->ago + $cusr->sep, 2);
                    $t3 = Math::redondear($pres->vigdet->jul + $pres->vigdet->ago + $pres->vigdet->sep, 2);
                    $d3 = Math::redondear($pres->devdet->jul + $pres->devdet->ago + $pres->devdet->sep, 2);
                    $s3 = Math::redondear($t3 - $d3, 2);

                    $c4 = Math::redondear($cusr->oct + $cusr->nov + $cusr->dic, 2);
                    $t4 = Math::redondear($pres->vigdet->oct + $pres->vigdet->nov + $pres->vigdet->dic, 2);
                    $d4 = Math::redondear($pres->devdet->oct + $pres->devdet->nov + $pres->devdet->dic, 2);
                    $s4 = Math::redondear($t4 - $d4, 2);

                    if ($c1 > $t1) {
                        $retorno->valido = false;
                        $retorno->error = 11;
                        $retorno->mensaje = 'No puede anticipar recursos en el trimestre 1 , en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                        return $retorno;
                    }
                    switch ($ti) {
                        case 2:
                            if ($c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 12;
                                $retorno->mensaje = 'No puede pasar recursos del trimestre 4 a trimestres anteriores, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                            if ($c2 == $t2 && $c3 < $t3 && $c4 == $t4) {
                                $retorno->valido = false;
                                $retorno->error = 13;
                                $retorno->mensaje = 'No puede pasar recursos del trimestre 3 a trimestres anteriores, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                            if ($c2 > $t2 && $c3 < $t3 && $c4 == $t4) {
                                $retorno->valido = false;
                                $retorno->error = 14;
                                $retorno->mensaje = 'No puede pasar recursos del trimestre 3 a trimestres anteriores, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                            break;
                        case 3:
                            if ($c3 < $t3 && $c4 == $t4) {
                                $retorno->valido = false;
                                $retorno->error = 15;
                                $retorno->mensaje = 'No pueder pasar el recurso del trimestre 3 a trimestres anteriores, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                            if ($c3 == $t3 && $c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 16;
                                $retorno->mensaje = 'No pueder pasar el recurso del trimestre 4 a trimestres anteriores al 3, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                            if ($c3 > $t3 && $c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 17;
                                $retorno->mensaje = 'No pueder pasar el recurso del trimestre 4 al trimestre 3, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }

                            break;
                        case 4:
                            if ($c4 == $t4) {
                                if ($c1 > $t1) {
                                    $retorno->valido = false;
                                    $retorno->error = 18;
                                    $retorno->mensaje = 'No puede pasar recursos al trimestre 1, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                    return $retorno;
                                }
                                if ($c2 > $t2 && $c3 < $t3) {
                                    $retorno->valido = false;
                                    $retorno->error = 19;
                                    $retorno->mensaje = 'No puede pasar recurso del trimestre 3 al 2, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                    return $retorno;
                                }
                            }
                            if ($c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 20;
                                $retorno->mensaje = 'No pueder pasar recursos del trimestre 4 a trimestres anteriores, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                            break;
                    }
                }
                break;
            case 'A':
                if ($mod == 'I') {

                    if ($d['pe_reductible'] != 3 && $d['pe_reductible'] != 1) {

                        $retorno->valido = false;
                        $retorno->error = 35;
                        $retorno->mensaje = 'No es posible utilizar esta partida para realizar la ampliación en esta modalidad';
                        return $retorno;
                    }

                    for ($i = $mes - 2; $i >= 0; $i--) {
                        if ($cusr->$meses[$i] != 0) {

                            $retorno->valido = false;
                            $retorno->error = 21;
                            $retorno->mensaje = 'No se puede Ampliar en el mes de ' . $mesesd[$i] . ' por ser un mes ya pasado, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                            return $retorno;
                        }
                    }
                }
                break;
        }

        return $retorno;
    }
    /***********************************************************************************************************************************************/
    public static function getPresupuestoModificaciones($cvp, $tipo, $mov, $modificacion)
    {
        $sql = '
            SELECT
                pre_comprometido,pre_vigente,pre_devengado,dev_ene,dev_feb,dev_mar,dev_abr,
                dev_may,dev_jun,dev_jul,dev_ago,dev_sep,dev_oct,dev_nov,dev_dic,vig_ene,vig_feb,
                vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
                (SUM(vig_ene+vig_feb+vig_mar) - SUM(dev_ene+dev_feb+dev_mar)) AS dis_ene,0 dis_feb, 0 dis_mar,
                (SUM(vig_abr+vig_may+vig_jun) - SUM(dev_abr+dev_may+dev_jun)) AS dis_abr,0 dis_may, 0 dis_jun,
                (SUM(vig_jul+vig_ago+vig_sep) - SUM(dev_jul+dev_ago+dev_sep)) AS dis_jul,0 dis_ago, 0 dis_sep,
                (SUM(vig_oct+vig_nov+vig_dic) - SUM(dev_oct+dev_nov+dev_dic)) AS dis_oct,0 dis_nov, 0 dis_dic,
                pre_claveCompleta,pe_reductible
            FROM presupuesto
                INNER JOIN cat_PartidaEspecifica USING (pg_id,pe_id)
            WHERE gpo_id = ' . $cvp->gpo_id . '
                AND ur_id = ' . $cvp->ur_id . '
                AND ue_id = ' . $cvp->ue_id . '
                AND epg_id = ' . $cvp->epg_id . '
        ';

        $sql .= ' AND pg_id =  ' . $cvp->pg_id . '
                AND pe_id = ' . $cvp->pe_id . '
                AND ecf_id = ' . $cvp->ecf_id . '
                ' . (isset($cvp->tcp_id) ? ' AND tcp_id = "' . $cvp->tcp_id . '"' : '') . '
        ';

        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar();
        $d = $c->extraer();
        unset($c);
        if ($tipo == 'valida') {
            return $d;
        }

        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $mesesd = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $pre = [];

        for ($i = 0; $i <= 11; $i++) {
            $n = new stdClass();
            $n->mes = $mesesd[$i];
            $n->devengado = $d['dev_' . $meses[$i]];
            $n->vigente = $d['vig_' . $meses[$i]];
            $n->disp = (Math::redondear($d['vig_' . $meses[$i]] - $d['dev_' . $meses[$i]], 2)) < 0 ? 0 : (Math::redondear($d['vig_' . $meses[$i]] - $d['dev_' . $meses[$i]], 2));
            $n->disponible2 = $d['dis_' . $meses[$i]];
            $n->modificacion = $modificacion[$i];
            $n->nmes = $i;

            array_push($pre, $n);
        }
        return $pre;
    }
}
