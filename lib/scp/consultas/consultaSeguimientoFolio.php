<?php

/**
 * Description of ControladorSeguimientoPrevisiones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 29-Sep-2020
 * Comment: -----
 * Objective: Consulta en la bd para mostrar los datos seguimiento de folios.
 * Regla de Negocio: -----
 **/
function consultaSeguimientoFolio($nvj = '', $perfil = '', $CvePres = '', $gpo = '', $ur = '')
{
    $and = '';
    switch ($nvj) {
        case 1: //Unidad Ejecutora
        case 2: //Unidad Responsable
            //Verifica si el perfil es Supervisor o Supervisor de Compras Consolidadas
            if ($perfil == 'S' || $perfil == 'U') {
                //Obtiene el grupo y la UR de la clave presupuestal
                $ur = substr($CvePres, 0, 3);
                $and .= ' and b.gpo_id = "' . substr($ur, 0, 1) . '" and b.ur_id = "' . substr($ur, 1, 2) . '" ';
                $and .= ' and ur_rh=1 ';
            } else {
                //Obtiene el grupo y la ur de la sesion
                $and .= ' and b.gpo_id = "' . $gpo . '" and b.ur_id = "' . $ur . '" ';
            }
            break;
        default:
            //Obtiene el grupo y la UR de la clave presupuestal
            $ur = substr($CvePres, 0, 3);
            $and .= ' and b.gpo_id = "' . substr($ur, 0, 1) . '" and b.ur_id = "' . substr($ur, 1, 2) . '" ';
            break;
    }
    //Unidad ejecutora
    $and .= ' and b.ue_id = "' . substr($CvePres, 3, 3) . '" ';
    ///Programa
    $and .= ' and prg_id = "' . substr($CvePres, 6, 3) . '" ';
    //Subprograma
    $and .= ' and spr_id = "' . substr($CvePres, 9, 2) . '" ';
    //Proyecto
    $and .= ' and py_id = "' . substr($CvePres, 11, 3) . '" ';
    //Obra actividad
    $and .= ' and oa_id = "' . substr($CvePres, 14, 3) . '" ';
    //Partida generica
    $and .= ' and b.pg_id = "' . substr($CvePres, 17, 3) . '" ';
    //Partida de especifica
    $and .= ' and b.pe_id = "' . substr($CvePres, 20, 3) . '" ';

    $cff_id = substr($CvePres, 23, 1);
    //Fuente de financiamiento
    $fte_id = substr($CvePres, 24, 1);
    //Sub-fuente de financiamiento
    $sft_id = substr($CvePres, 25, 1);
    //Origen de financiamiento
    $ori_id = substr($CvePres, 26, 1);
    //Suborigen de financiamiento
    $sor_id = substr($CvePres, 27, 1);
    //Indentificador de la clave de financiamineto
    $cfi_id = substr($CvePres, 28, 2);
    //Indentificador del año de la clave de financiamiento
    $cfi_anio = substr($CvePres, 30, 2);

    $and .= (strlen($cff_id) == 1) ? ' and cff_id = "' . $cff_id . '" ' : '';
    $and .= (strlen($fte_id) == 1) ? ' and fte_id = "' . $fte_id . '" ' : '';
    $and .= (strlen($sft_id) == 1) ? ' and sft_id = "' . $sft_id . '" ' : '';
    $and .= (strlen($ori_id) == 1) ? ' and ori_id = "' . $ori_id . '" ' : '';
    $and .= (strlen($sor_id) == 1) ? ' and sor_id = "' . $sor_id . '" ' : '';
    $and .= (strlen($cfi_id) == 2) ? ' and cfi_id = "' . $cfi_id . '" ' : '';
    $and .= (strlen($cfi_anio) == 2) ? ' and cfi_anio = "' . $cfi_anio . '" ' : '';

    $sql = '
        SELECT
            a.omo_id,a.mod_gpo_id,a.mod_ur_id,a.mod_id, c.ur_nombre,
            pre_claveCompleta,b.gpo_id,b.ur_id,
            f.emo_nombre, f.emo_color, a.emo_id,b.tcp_id,omo_nombre
        FROM scp_MaestroModificacion a
        JOIN scp_DetalleModificacion b using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
        JOIN presupuesto using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN cat_PartidaEspecifica d using(pg_id, pe_id)
        JOIN cat_EstadoModificacion f using (emo_id)
        JOIN cat_UnidadResponsable c on c.gpo_id = a.mod_gpo_id and c.ur_id = a.mod_ur_id
        JOIN cat_OrigenModificacion e using(omo_id)
        JOIN enc_OrigenOperacion using(omo_id,enc_oop)
        where a.emo_id not in(14,15,16) ' . $and . ';
    ';

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();

    $result = [];

    while ($row = $conexion->extraer()) {
        $cvePresupuestal = $row['pre_claveCompleta'] . '_' . $row['tcp_id'];
        $unidadR = $row['gpo_id'] . $row['ur_id'] . '_' . $row['ur_nombre'];
        $dato = [
            'cvePresupuestal' => $cvePresupuestal,
            'unidadR'         => $unidadR,
            'mod_id'          => $row['mod_id'],
            'omo_nombre'      => $row['omo_nombre'],
            'emo_id'          => $row['emo_id'],
            'emo_nombre'      => $row['emo_nombre'],
        ];
        array_push($result, $dato);
    }
    return array_reverse($result);
}
