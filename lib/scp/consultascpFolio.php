<?php
/**
 * Description of function consultascpFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 30-Nov-2018
 * Comment: -----
 * Objective: Consultar datos en la bd los folios registrados.
 * Regla de Negocio: -----
 *
 * */

function consultascpFolio($gpo_id, $ur_id, $ecf_id, $envia)
{
    $sql = '';
    $fecha = date("Y-m-d H:i:00", time());
    switch ($envia) {
        case 'ur':
            $sql = "
                SELECT folio.*
                    FROM (
                        SELECT mod_gpo_id,mod_ur_id,mod_gpo_id as gpo_id,mod_ur_id as ur_id,
                        omo_id origenModificacion,omo_id,tfm_id,
                        (mod_id*1) mod_id,mod_clave, mod_concepto,
                        enc_oop, mod_tipoMovimiento, ecf_id,
                        mmd_id,cff_id,mod_Subsecretaria,
                        tmd_nombre, emo_id, emo_nombre, emo_color,
                        cfi_clave,omo_nombre,mod_fechaFolio
                        FROM scp_MaestroModificacion
                        INNER JOIN enc_OrigenOperacion using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion using(omo_id)
                        INNER JOIN(
                            SELECT ecf_id,cfi_clave,so.cff_id from enc_ClaveFinanciamiento
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento".APP_EJERCICIO." using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso".APP_EJERCICIO." so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion using(tmd_id)
                        INNER JOIN cat_EstadoModificacion using(emo_id)
                        WHERE
                        mod_gpo_id = " . $gpo_id . "
                        AND mod_ur_id = " . $ur_id . "
                        AND emo_id NOT IN (14,15,16)
                        AND omo_id = 'A'
                        " . (!empty($ecf_id) ?
                        "AND ecf_id=" . $ecf_id . "" : "") . "
                        ORDER BY mod_id
                    )folio LEFT JOIN (
                        SELECT gpo_id, ur_id, omo_id, mmd_id, mod_Subsecretaria, tfm_id, cff_id, pum_fechaInicial,
                        pum_fechaFinal,
                        IF(not isnull(pum_fechaInicial) and not isnull(pum_fechaFinal) and sts_id=1,1,2) as siFecha,
                        IF(isnull(pum_fechaInicial) and isnull(pum_fechaFinal) and sts_id=1,1,2) as noFecha,
                        IF( ('" . $fecha . "' > date(pum_fechaFinal)
                        OR ('" . $fecha . "' <= date(pum_fechaFinal))
                        OR (sts_id=2 and isnull(pum_fechaFinal))),2,1) as habilitado
                        FROM cat_PermisosUrModificaciones
                    )activate USING(gpo_id,ur_id,omo_id,mmd_id,mod_Subsecretaria,tfm_id,cff_id)
                    GROUP BY mod_id;
                ";
            break;
        case 'drh':
            $sql = "
                SELECT folio.*
                    FROM (
                        SELECT mod_gpo_id,mod_ur_id,if(omo_id = 'C' or omo_id='D', 1,mod_gpo_id) gpo_id,
                        if(omo_id = 'C' or omo_id='D', 18,mod_ur_id) ur_id,
                        omo_id origenModificacion,omo_id,tfm_id,
                        (mod_id*1) mod_id,mod_clave, mod_concepto, enc_oop, mod_tipoMovimiento, ecf_id, mmd_id,cff_id,mod_Subsecretaria,
                        tmd_nombre, emo_id, emo_nombre, emo_color,cfi_clave,omo_nombre,mod_fechaFolio
                        FROM scp_MaestroModificacion
                        INNER JOIN enc_OrigenOperacion using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion using(omo_id)
                        INNER JOIN(
                            SELECT ecf_id,cfi_clave,so.cff_id from enc_ClaveFinanciamiento
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento".APP_EJERCICIO." using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso".APP_EJERCICIO." so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion using(tmd_id)
                        INNER JOIN cat_EstadoModificacion using(emo_id)
                        WHERE mod_gpo_id = " . $gpo_id . "
                        AND mod_ur_id = " . $ur_id . "
                        AND emo_id NOT IN (14,15,16)
                        AND tfm_id='A' AND mod_Subsecretaria = 1
                        AND omo_id = 'C'
                        " . (!empty($ecf_id) ?
                        "AND ecf_id=" . $ecf_id . "" : "") . "
                        ORDER BY mod_id
                    )folio LEFT JOIN (
                        SELECT gpo_id, ur_id, omo_id, mmd_id, mod_Subsecretaria, tfm_id, cff_id, pum_fechaInicial,
                        pum_fechaFinal,
                        IF(not isnull(pum_fechaInicial) and not isnull(pum_fechaFinal) and sts_id=1,1,2) as siFecha,
                        IF(isnull(pum_fechaInicial) and isnull(pum_fechaFinal) and sts_id=1,1,2) as noFecha,
                        IF( ('" . $fecha . "' > date(pum_fechaFinal) OR ('" . $fecha . "' <= date(pum_fechaFinal))
                        OR (sts_id=2 and isnull(pum_fechaFinal))),2,1) as habilitado
                        FROM cat_PermisosUrModificaciones
                        WHERE mod_Subsecretaria = 1
                    )activate USING(gpo_id,ur_id,omo_id,mmd_id,mod_Subsecretaria,tfm_id,cff_id);
                ";
            break;
        case 'presupuesto':
            $sql = "
                SELECT folio.*
                    FROM (
                        SELECT mod_gpo_id,mod_ur_id,mod_gpo_id as gpo_id,
                        mod_ur_id as ur_id,
                        omo_id origenModificacion,omo_id,tfm_id,
                        (mod_id*1) mod_id,mod_clave, mod_concepto, enc_oop, mod_tipoMovimiento, ecf_id,
                        mmd_id,cff_id,mod_Subsecretaria,
                        tmd_nombre, emo_id, emo_nombre, emo_color,cfi_clave,omo_nombre,mod_fechaFolio
                        FROM scp_MaestroModificacion
                        INNER JOIN enc_OrigenOperacion using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion using(omo_id)
                        INNER JOIN(
                            SELECT ecf_id,cfi_clave,so.cff_id from enc_ClaveFinanciamiento
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento".APP_EJERCICIO." using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso".APP_EJERCICIO." so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion using(tmd_id)
                        INNER JOIN cat_EstadoModificacion using(emo_id)
                        WHERE mod_gpo_id = " . $gpo_id . "
                        AND mod_ur_id = " . $ur_id . "
                        AND emo_id IN (6,8,11,17)
                        ORDER BY mod_id
                    )folio;
                ";
            break;
        case 'planeacion':
            $sql = "
                SELECT folio.*
                    FROM (
                        SELECT mod_gpo_id,mod_ur_id,mod_gpo_id as gpo_id,
                        mod_ur_id as ur_id,
                        omo_id origenModificacion,omo_id,tfm_id,
                        (mod_id*1) mod_id,mod_clave, mod_concepto, enc_oop, mod_tipoMovimiento, ecf_id,
                        mmd_id,cff_id,mod_Subsecretaria,
                        tmd_nombre, emo_id, emo_nombre, emo_color,cfi_clave,omo_nombre,mod_fechaFolio
                        FROM scp_MaestroModificacion
                        INNER JOIN enc_OrigenOperacion using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion using(omo_id)
                        INNER JOIN(
                            SELECT ecf_id,cfi_clave,so.cff_id from enc_ClaveFinanciamiento
                            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento".APP_EJERCICIO." using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso".APP_EJERCICIO." so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion using(tmd_id)
                        INNER JOIN cat_EstadoModificacion using(emo_id)
                        WHERE mod_gpo_id = " . $gpo_id . "
                        AND mod_ur_id = " . $ur_id . "
                        AND emo_id IN (6,11,17) AND mod_Subsecretaria=2
                        ORDER BY mod_id
                    )folio;
                ";
            break;
    } // fin switch

    $sqlconexion = new AccesoDatos();
    $sqlconexion->conectar();
    $sqlconexion->setSql($sql);
    $sqlconexion->consultar();
    $sqlconexion->desconectar();
    $result = array();

    while ($row = $sqlconexion->extraer()) {
        $dato = array(
            'mod_id' => $row['mod_id'],
            'mmd_id' => $row['mmd_id'],
            'tmd_nombre' => $row['tmd_nombre'],
            'mod_concepto' => $row['mod_concepto'],
            'mod_fechaFolio' => $row['mod_fechaFolio'],
            'emo_nombre' => $row['emo_nombre'],
        );
        array_push($result, $dato);
    } //fin while
    return $result;
}