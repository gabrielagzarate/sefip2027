<?php
function consultaReportePropuesta($det_prop=''){

	//averiguamos el año de la propuesta
    $sql='SELECT pro_ejercicio FROM tesoreria.pro_Propuesta where pro_id='.$det_prop;
    $conexion= new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();

    $anio = 2020;

    if($row=$conexion->extraer()){
        $anio = $row['pro_ejercicio'];
    }

    switch ($anio) {
        case 2017:
             $sql ="SELECT 
                        p.pro_id AS proDet_propuesta,
                        p.pro_ejercicio,
                        p.pro_fechaGeneracion,
                        dp.gpo_id ,
                        dp.ur_id ,
                        dp.clc_id ,
                        dp.pro_importeClc AS importe,
                        clc.clc_beneficiario ,
                        clc.clc_concepto ,
                        clc.clc_fechaAutorizacion AS proDet_fecha,
                        clc.ecf_id,
                        BN.bsf_id as cbs_id,
                        clc.bur_id,

                        CV.ecf_clave as cve_fin,
                        concat(clc.gpo_id,clc.ur_id) ur,
                        IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                        CX.clc_cereCo as folio,
                        UR.ur_nombre as responsable,
                        LE.loe_clave as lote_egreso,
                        CB.cbs_nombre as nombre_cuenta,
                        IB.iba_abreviatura as nombre_banco,
                        if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                    FROM
                        tesoreria.pro_Propuesta AS p
                    INNER JOIN tesoreria.pro_PropuestaClc AS dp USING (pro_id)
                    INNER JOIN ctrl2017.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                    INNER JOIN ctrl2017.scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN ctrl2017.scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    LEFT JOIN ctrl2017.scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN ctrl2017.enc_BancosSefinCveFin BN using (ecf_id)
                    INNER JOIN ctrl2017.enc_ClaveFinanciamiento CV using (ecf_id)
                    INNER JOIN tesoreria.ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                    INNER JOIN tesoreria.cat_InstitucionBancaria IB using (iba_id)
                    INNER JOIN ctrl2017.cat_UnidadResponsable UR USING(gpo_id,ur_id)
                    LEFT JOIN tesoreria.lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                    WHERE
                        pro_id=".$det_prop;

            break;
        case 2018:
            $sql ="SELECT
                p.pro_id AS proDet_propuesta,
                p.pro_ejercicio,
                p.pro_fechaGeneracion,      
                dp.gpo_id ,
                dp.ur_id ,
                dp.clc_id ,
                dp.pro_importeClc AS importe,
                clc.clc_beneficiario ,
                clc.clc_concepto ,
                clc.clc_fechaAutorizacion AS proDet_fecha,
                clc.ecf_id,
                BS.cbs_id,
                clc.bur_id,

                CV.ecf_clave as cve_fin,
                concat(clc.gpo_id,clc.ur_id) ur,
                IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                CX.clc_cereCo as folio,
                UR.ur_nombre as responsable,
                LE.loe_clave as lote_egreso,
                BS.cbs_nombre as nombre_cuenta,
                IB.iba_abreviatura as nombre_banco,
                if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
            FROM
                tesoreria.pro_Propuesta AS p
            INNER JOIN tesoreria.pro_PropuestaClc AS dp USING (pro_id)
            INNER JOIN ctrl2018.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ctrl2018.scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
            left JOIN ctrl2018.scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
            left JOIN ctrl2018.scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            INNER JOIN tesoreria.vin_SefinClaveFinanciamiento CF using(ecf_id)
            INNER JOIN tesoreria.ban_CuentaBancariaSefin BS using(cbs_id)
            INNER JOIN ctrl2018.cat_UnidadResponsable UR using(gpo_id,ur_id)
            INNER JOIN tesoreria.cat_InstitucionBancaria as IB using(iba_id)
            INNER JOIN ctrl2018.enc_ClaveFinanciamiento CV using (ecf_id)
            LEFT JOIN tesoreria.lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
            WHERE
                p.pro_id = ".$det_prop;
            break;
        case 2019:
            $sql ="SELECT
                p.pro_id AS proDet_propuesta,
                p.pro_ejercicio,
                p.pro_fechaGeneracion,      
                dp.gpo_id ,
                dp.ur_id ,
                dp.clc_id ,
                dp.pro_importeClc AS importe,
                clc.clc_beneficiario ,
                clc.clc_concepto ,
                clc.clc_fechaAutorizacion AS proDet_fecha,
                clc.ecf_id,
                BS.cbs_id,
                clc.bur_id,

                CV.ecf_clave as cve_fin,
                concat(clc.gpo_id,clc.ur_id) ur,
                IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                CX.clc_cereCo as folio,
                UR.ur_nombre as responsable,
                -- LE.loe_clave as lote_egreso,
                '' as lote_egreso,
                BS.cbs_nombre as nombre_cuenta,
                IB.iba_abreviatura as nombre_banco,
                if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
            FROM
                tesoreria.pro_Propuesta AS p
            INNER JOIN tesoreria.pro_PropuestaClc AS dp USING (pro_id)
            INNER JOIN ctrl2019.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ctrl2019.scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
            #left JOIN ctrl2019.scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
            #left JOIN ctrl2019.scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            INNER JOIN tesoreria.vin_SefinClaveFinanciamiento CF using(ecf_id)
            INNER JOIN tesoreria.ban_CuentaBancariaSefin BS using(cbs_id)
            INNER JOIN ctrl2019.cat_UnidadResponsable UR using(gpo_id,ur_id)
            INNER JOIN tesoreria.cat_InstitucionBancaria as IB using(iba_id)
            INNER JOIN ctrl2019.enc_ClaveFinanciamiento CV using (ecf_id)
            LEFT JOIN tesoreria.lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
            WHERE
                p.pro_id = ".$det_prop;
            break;
        case 2020:
        default:
            $sql ="SELECT
                p.pro_id AS proDet_propuesta,
                p.pro_ejercicio,
                p.pro_fechaGeneracion,      
                dp.gpo_id ,
                dp.ur_id ,
                dp.clc_id ,
                dp.pro_importeClc AS importe,
                clc.clc_beneficiario ,
                clc.clc_concepto ,
                clc.clc_fechaAutorizacion AS proDet_fecha,
                clc.ecf_id,
                BS.cbs_id,
                clc.bur_id,

                CV.ecf_clave as cve_fin,
                concat(clc.gpo_id,clc.ur_id) ur,
                IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                CX.clc_cereCo as folio,
                UR.ur_nombre as responsable,
                -- LE.loe_clave as lote_egreso,
                '' as lote_egreso,
                BS.cbs_nombre as nombre_cuenta,
                IB.iba_abreviatura as nombre_banco,
                if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
            FROM
                tesoreria.pro_Propuesta AS p
            INNER JOIN tesoreria.pro_PropuestaClc AS dp USING (pro_id)
            INNER JOIN ctrl2020.scp_Clc as clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ctrl2020.scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
            #left JOIN ctrl2020.scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
            #left JOIN ctrl2020.scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            INNER JOIN tesoreria.vin_SefinClaveFinanciamiento CF using(ecf_id)
            INNER JOIN tesoreria.ban_CuentaBancariaSefin BS using(cbs_id)
            INNER JOIN ctrl2020.cat_UnidadResponsable UR using(gpo_id,ur_id)
            INNER JOIN tesoreria.cat_InstitucionBancaria as IB using(iba_id)
            INNER JOIN ctrl2020.enc_ClaveFinanciamiento CV using (ecf_id)
            LEFT JOIN tesoreria.lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
            WHERE
                p.pro_id = ".$det_prop;
            break;
    }// fin switch

    $conexion= new AccesoDatos();
	$conexion->conectar();
	$conexion->setSql($sql);
	$conexion->consultar();
	$conexion->desconectar();

	$i=1;

	while ($row=$conexion->extraer())
	{
		$dato = array(
    		'contador' 		=> $i++,
    		'pro_fechaGeneracion' => $row['pro_fechaGeneracion'],
    		'lote_egreso' 	=> $row['lote_egreso'],
    		'ur' 			=> $row['ur'],
    		'clc_id' 		=> $row['clc_id'],
    		'cere' 			=> $row['cere'],
    		'folio'			=> $row['folio'],	
    		'responsable' 	=> $row['responsable'],
    		'clc_beneficiario' => $row['clc_beneficiario'],
    		'clc_concepto' 	=> $row['clc_concepto'],
    		'importe' 		=> number_format($row['importe']),
    		'nombre_banco' 	=> $row['nombre_banco'],
    		'cbs_id'		=> $row['cbs_id'],
    		'nombre_cuenta' => $row['nombre_cuenta'],
    		'bur_id' 		=> $row['bur_id'],
    		'cve_fin' 		=> $row['cve_fin'],
    		'contable'		=> $row['contable']
    	);
    	array_push( $result, $dato );
	}// fin while
	return $result;

}// fin function