<?php

function URsConsolidadoAvanceGestion()
{
	$conexion = new AccesoDatos();
	$conexion->setSql("
		SELECT concat(gpo_id,ur_id) AS UR
            FROM (
                SELECT gpo_id,ur_id,fca_trimestre1,fca_trimestre2,fca_trimestre3,fca_trimestre4,
                SUM(contador) total,
                    SUM(IF(a.usr_fecha<='2026-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),contador,0)) total11,
                SUM(IF(a.usr_fecha<='2026-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),contador,0)) total22,
                SUM(IF(a.usr_fecha<='2026-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),contador,0)) total33,
                SUM(IF(a.usr_fecha<='2026-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),contador,0)) total44,
                SUM(IF(a.usr_fecha<='2026-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),cap_trimestre1,0)) total1,
                SUM(IF(a.usr_fecha<='2026-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),cap_trimestre2,0))total2,
                SUM(IF(a.usr_fecha<='2026-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),cap_trimestre3,0))total3,
                SUM(IF(a.usr_fecha<='2026-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),cap_trimestre4,0)) total4,
                SUM(alc_trimestre1) alc_trimestre1,SUM(alc_trimestre2) alc_trimestre2,
                SUM(alc_trimestre3) alc_trimestre3,SUM(alc_trimestre4) alc_trimestre4,
                SUM(mem_alcanzado) mem_alcanzado, SUM(mem_modificado) mem_modificado,ur_clave,ur_nombre,
                est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                MAX(cap_trimestre1) AS cap_trimestre1, MAX(cap_trimestre2) AS cap_trimestre2,
                MAX(cap_trimestre3) AS cap_trimestre3, MAX(cap_trimestre4) AS cap_trimestre4,
                SUM(mac_original) AS mac_original, SUM(pre_vigente) AS pre_vigente
                FROM (
                    SELECT pii_id,1 contador, gpo_id, ur_id, ur_clave,ur_nombre,m.usr_fecha,
                    epg_id,mac_alcanzado AS mem_alcanzado,mac_modificado AS mem_modificado,
                    alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                    mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                    mac_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                    cap_trimestre1,cap_trimestre2,cap_trimestre3,cap_trimestre4,
                    fca_trimestre1,fca_trimestre2,fca_trimestre3,fca_trimestre4,
                    est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                    SUM(vig_trimestre1) AS vig_trimestre1,SUM(vig_trimestre2) AS vig_trimestre2,
                    SUM(vig_trimestre3) AS vig_trimestre3,SUM(vig_trimestre4) AS vig_trimestre4 ,
                    mac_original,SUM(pre_vigente) AS pre_vigente
                    FROM cat_UnidadResponsable
                    INNER JOIN cat_ObraActividad USING(gpo_id,ur_id)
                    INNER JOIN enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_trimestre1,
                        MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_trimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_trimestre3,
                        MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_trimestre4
                        FROM mir_TrimestreUr
                        WHERE nim_id=4
                        GROUP BY gpo_id, ur_id
                    )est USING (gpo_id,ur_id)
                    LEFT JOIN cat_MetaActividad m USING (epg_id)
                    LEFT JOIN (
                        SELECT pii_id, epg_id, SUM(IF(tri_id=1,mact_alcanzado,NULL)) alc_trimestre1,
                        SUM(IF(tri_id=2,mact_alcanzado,NULL)) alc_trimestre2, SUM(IF(tri_id=3,mact_alcanzado,NULL)) alc_trimestre3,
                        SUM(IF(tri_id=4,mact_alcanzado,NULL)) alc_trimestre4,  SUM(IF(tri_id=1,mact_modificado,NULL)) mod_trimestre1,
                        SUM(IF(tri_id=2,mact_modificado,NULL)) mod_trimestre2, SUM(IF(tri_id=3,mact_modificado,NULL)) mod_trimestre3,
                        SUM(IF(tri_id=4,mact_modificado,NULL)) mod_trimestre4, SUM(IF(tri_id=1,mact_original,NULL)) ori_trimestre1,
                        SUM(IF(tri_id=2,mact_original,NULL)) ori_trimestre2, SUM(IF(tri_id=3,mact_original,NULL)) ori_trimestre3,
                        SUM(IF(tri_id=4,mact_original,NULL)) ori_trimestre4, SUM(IF(tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1,
                        SUM(IF(tri_id=2,mact_capturaAvance,NULL)) cap_trimestre2, SUM(IF(tri_id=3,mact_capturaAvance,NULL)) cap_trimestre3,
                        SUM(IF(tri_id=4,mact_capturaAvance,NULL)) cap_trimestre4
                        FROM cat_MetaActividadTrimestre
                        GROUP BY pii_id,epg_id
                    )mat USING (pii_id,epg_id)
                    LEFT JOIN (
                        SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id, SUM(pre_vigente) AS pre_vigente,epg_id,
                        SUM(vig_ene+vig_feb+vig_mar) vig_trimestre1, SUM(vig_abr+vig_may+vig_jun) vig_trimestre2,
                        SUM(vig_jul+vig_ago+vig_sep) vig_trimestre3, SUM(vig_oct+vig_nov+vig_dic)vig_trimestre4
                        FROM presupuesto
                        INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id
                    ) AS temp USING(gpo_id,ur_id,epg_id)
                    WHERE gpo_id!=0

                    AND ((eoa_id=5 AND toa_id=2) OR (eoa_id=4 AND toa_id=1))
                    GROUP BY epg_id,pii_id
                )a
                WHERE (mac_original>0 || mem_modificado>0 ||pre_vigente>0)
                GROUP BY gpo_id,ur_id
                ORDER BY gpo_id,ur_id
            )b;
	");
	
	$conexion->conectar();
    $conexion->consultar();
    $numRegistros = $conexion->getNumeroRegistros();
    $conexion->desconectar();
    $contador = 0;
    if ($numRegistros > 0) {
        while ($rowURs = $conexion->extraer()) {
            $datos[$contador] = $rowURs['UR'];
            $contador++;
        }
    } else {
        $datos[0] = '';
        
    }

    return $datos;
}
?>