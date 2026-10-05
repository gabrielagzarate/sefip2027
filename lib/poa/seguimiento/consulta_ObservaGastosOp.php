<?php
/**
    * Description of ControladorRegistroModificaciones
    *
    * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
    * Created: 07-Mayo-2019
     * Comment: -----
     * Objective: Consulta para los reporte observaciones trimestral.
     * Regla de Negocio: -----
**/
function consulta_ObservaGastosOp($ur='', $ue='', $prg='', $spr='', $oa='', $py ='' ,$trim='', $desde='', $hasta=''){

    $filtroFechas = "";

    if($desde !='' || $hasta != '')
    {
        
        if($desde != '' && $hasta !=''){
            $filtroFechas = " AND DATE_FORMAT(chms.usr_fecha,'%d/%m/%y') BETWEEN '".$desde. "' AND '".$hasta."' " ;
        }
        else if ($desde != ''){
            $filtroFechas = " AND DATE_FORMAT(chms.usr_fecha,'%d/%m/%y') >='".$desde."' ";
        }
        else if ($hasta != ''){
            $filtroFechas = " AND DATE_FORMAT(chms.usr_fecha,'%d/%m/%y') <='".$hasta."' ";
        }
    }

    $sql='
        SELECT
            CONCAT(ur.gpo_id,ur.ur_id) AS ur_clave,ur_nombre,ue_id,OA.prg_id,OA.spr_id,OA.py_id,oa_id,oa_nombre,
            MAX(CONCAT(ci.nim_id,"-",ci.ind_id)) AS ind_id,toa_nombre, MAX(m.pii_id) AS meta,
            MAX(mact_original) as programada,MAX(mact_modificado) as actualizada,
            (mact_alcanzado) as alcanzada,
            IF(mact_alcanzado !=0 , ROUND((mact_alcanzado/mact_modificado)*100,2), 0.00) as alcanzado,
            hms_trimestre,MAX(hms_mensaje)hms_mensaje,DATE(chms.usr_fecha) AS fecha, TIME(chms.usr_fecha) as hora,
            MAX(hms_mensajeRespuesta)hms_mensajeRespuesta
        FROM cat_MetaActividad m
            INNER JOIN cat_MetaActividadTrimestre mt using(pii_id, epg_id)
            INNER JOIN enc_Programatico as e using(epg_id)
            INNER JOIN cat_ObraActividad OA using(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_TipoObraActividad USING (toa_id)
            INNER JOIN vin_ProductoIntermedioIndicador pii USING(pii_id)
            INNER JOIN cat_Indicador ci USING(nim_id,ind_id)
            INNER JOIN cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN cat_MensajeSeguimiento cms ON e.epg_id = cms.epg_id
            INNER JOIN cat_HistorialMensajeSeguimiento chms on cms.msj_id = chms.msj_id
            INNER JOIN cat_Proyecto PR ON (PR.gpo_id=OA.gpo_id AND PR.ur_id=OA.ur_id AND PR.prg_id=OA.prg_id AND PR.spr_id=OA.spr_id AND PR.py_id=OA.py_id)
        WHERE chms.tim_id = 2  AND IFNULL(hms_mensaje,"")!=""
            '.$ur.$ue.$prg.$spr.$oa.$py.$trim.$filtroFechas.'
        GROUP BY cms.msj_id,mact_alcanzado,mact_modificado,chms.usr_fecha
        ORDER BY ur.gpo_id,ur.ur_id,ind_id,oa_id;
    ';

    $sqlconexion = new AccesoDatos();
    $sqlconexion->conectar();
    $sqlconexion->setSql($sql);
    $sqlconexion->consultar(); 
    $sqlconexion->desconectar();

    $result = array();

    while( $row = $sqlconexion->extraer() ){
            $dato = array(
            'ur_clave' => $row['ur_clave'],
            'ur_nombre' => $row['ur_nombre'],
            'ue_id' => $row['ue_id'],
            'prg_id' => $row['prg_id'],
            'spr_id' => $row['spr_id'],
            'oa_id' => $row['oa_id'],
            'oa_nombre' => $row['oa_nombre'],
            'fecha' => $row['fecha'],
            'hora' => $row['hora'],
            'hms_mensaje' => $row['hms_mensaje'],
            'hms_mensajeRespuesta' => $row['hms_mensajeRespuesta']
            );
            array_push( $result, $dato );
    }

    return $result;
}