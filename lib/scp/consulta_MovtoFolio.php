<?php
/**
 * Description of function consultascpFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 18-Dic-2018
 * Migration: 03-Oct-2019
 * Comment: -----
 * Objective: Consulta los datos en la bd de los movimientos de un folio.
 * Regla de Negocio: -----
 *
 * */

function consulta_MovtoFolio($omo_id, $mod_gpo_id, $mod_ur_id, $mod_id, $envia, $bandera)
{
    $nivelJerarquico = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : "";
    $sql = '';

    if ($bandera == 'PDF') {
        switch ($envia) {
            case 'ur':
                $sql = "
                    SELECT m.omo_id, m.mod_gpo_id, m.mod_ur_id, m.mod_id, p.gpo_id, p.ur_id, p.ue_id, p.epg_id,
                    p.pg_id,p.pe_id,p.ecf_id, p.tcp_id,
                    pe.pe_nombre, t.mov_nombre, d.dmo_importe,m.emo_id, p.pre_claveCompleta
                    FROM scp_DetalleModificacion d
                    INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN scp_MaestroModificacion m using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                    INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                    INNER JOIN cat_TipoMovimiento t using(mov_id)
                    " . ($omo_id != '' ?
                " WHERE m.omo_id='" . $omo_id . "' " : "") . "
                    " . ($mod_gpo_id != '' ?
                " AND m.mod_gpo_id=" . $mod_gpo_id . " " : "") . "
                    " . ($mod_ur_id != '' ?
                " AND m.mod_ur_id=" . $mod_ur_id . " " : "") . "
                    " . ($mod_id != '' ?
                " AND m.mod_id=" . $mod_id . "" : "") . "
                    AND p.tcp_id != 'P';
                ";
                break;
            case 'drh':
                $sql = "
                    SELECT m.omo_id, m.mod_gpo_id, m.mod_ur_id, m.mod_id, p.gpo_id, p.ur_id, p.ue_id, p.epg_id,
                        p.pg_id,p.pe_id,p.ecf_id, p.tcp_id,
                        pe.pe_nombre, t.mov_nombre, d.dmo_importe,m.emo_id, p.pre_claveCompleta
                        FROM scp_DetalleModificacion d
                        INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        INNER JOIN scp_MaestroModificacion m using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                        INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                        INNER JOIN cat_TipoMovimiento t using(mov_id)
                        " . ($omo_id != '' ?
                    " WHERE m.omo_id='" . $omo_id . "' " : "") . "
                        " . ($mod_gpo_id != '' ?
                    " AND m.mod_gpo_id=" . $mod_gpo_id . " " : "") . "
                        " . ($mod_ur_id != '' ?
                    " AND m.mod_ur_id=" . $mod_ur_id . " " : "") . "
                        " . ($mod_id != '' ?
                    " AND m.mod_id=" . $mod_id . "" : "") . "
                        AND p.tcp_id = 'D'
                        AND p.tcp_id != 'P';
                    ";
                break;
            case 'sefin':
                $sql = "
                    SELECT d.omo_id, d.mod_gpo_id, d.mod_ur_id, d.mod_id, p.gpo_id, p.ur_id, p.ue_id, p.epg_id,
                    p.pg_id,p.pe_id,p.ecf_id, p.tcp_id, pe.pe_nombre, t.mov_nombre, d.dmo_importe,emo_id, p.pre_claveCompleta
                    FROM scp_DetalleModificacion d
                    INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN
                    (
                        SELECT omo_id,mod_gpo_id,mod_ur_id,mod_id,emo_id
                        FROM scp_MaestroModificacion
                        " . ($omo_id != '' ?
                        " WHERE omo_id='" . $omo_id . "' " : "") . "
                            " . ($mod_gpo_id != '' ?
                        " AND mod_gpo_id=" . $mod_gpo_id . " " : "") . "
                            " . ($mod_ur_id != '' ?
                        " AND mod_ur_id=" . $mod_ur_id . " " : "") . "
                            " . ($mod_id != '' ?
                        " AND mod_id=" . $mod_id . "" : "") . "
                    )FOLIO USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                    INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                    INNER JOIN cat_TipoMovimiento t using(mov_id)
                        " . ($omo_id != '' ?
                        " WHERE d.omo_id='" . $omo_id . "' " : "") . "
                            " . ($mod_gpo_id != '' ?
                        " AND d.mod_gpo_id=" . $mod_gpo_id . " " : "") . "
                            " . ($mod_ur_id != '' ?
                        " AND d.mod_ur_id=" . $mod_ur_id . " " : "") . "
                            " . ($mod_id != '' ?
                        " AND d.mod_id=" . $mod_id . "" : "") . "
                    ;
                ";
                break;
        }

        $sqlconexion = new AccesoDatos();
        $sqlconexion->conectar();
        $sqlconexion->setSql($sql);
        $sqlconexion->consultar();
        $sqlconexion->desconectar();

        $result = array();

        while ($row = $sqlconexion->extraer()) {
            $dato = array(
                'tipo' => $row['tcp_id'],
                'descripcion' => $row['pe_nombre'],
                'movimiento' => $row['mov_nombre'],
                'importe' => $row['dmo_importe'],
                'clave' => $row['pre_claveCompleta'],
            );
            array_push($result, $dato);
        } //fin while

        return $result;
    } else {
        $tipo = '';

        switch ($nivelJerarquico) {
            case 1: //UR
            case 2: //UE
                $tipo = "and tcp_id!='P'";
                break;
            default:
                $tipo = "";
                break;
        } //fin switch

        $sql = '
            SELECT
                gpo_id,ur_id,ue_id, prg_id,spr_id,py_id,oa_id,pg_id,pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                tcp_id,dmo_importe,dmo_ene,dmo_feb,dmo_mar,dmo_abr,dmo_may,dmo_jun,dmo_jul,dmo_ago,dmo_sep,dmo_oct,dmo_nov,dmo_dic
            FROM
            (
                SELECT b.omo_id, b.mod_id, b.mod_concepto,b.emo_id,a.mov_id, a.dmo_importe,b.enc_oop,b.ecf_id as clavefin,
                    a.dmo_ene, a.dmo_feb, a.dmo_mar, a.dmo_abr, a.dmo_may, a.dmo_jun, a.dmo_jul,
                    a.dmo_ago, a.dmo_sep, a.dmo_oct, a.dmo_nov, a.dmo_dic,
                    a.gpo_id, a.ur_id, a.ue_id, a.epg_id, a.pg_id, a.pe_id, a.ecf_id,a.tcp_id,
                    a.vig_ene pa_ene, a.vig_feb pa_feb, a.vig_mar pa_mar, a.vig_abr pa_abr, a.vig_may pa_may,
                    a.vig_jun pa_jun, a.vig_jul pa_jul, a.vig_ago pa_ago, a.vig_sep pa_sep, a.vig_oct pa_oct,
                    a.vig_nov pa_nov, a.vig_dic pa_dic, a.pre_vigente pa_importe,b.mod_ue_id
                    FROM scp_MaestroModificacion b
                JOIN scp_DetalleModificacion a USING (omo_id,mod_gpo_id,mod_ur_id,mod_id)
                WHERE omo_id = "' . $omo_id . '" AND mod_gpo_id=' . $mod_gpo_id . ' AND mod_ur_id="' . $mod_ur_id . '"
                    AND mod_id=' . $mod_id . ' ' . $tipo . '
            ) AS modificaciones
            JOIN cat_PartidaEspecifica d USING(pe_id,pg_id)
            JOIN presupuesto p USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
            JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
            JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
            JOIN cat_TipoModificacion f using(tmd_id);
        ';

        $sqlconexion = new AccesoDatos();
        $sqlconexion->conectar();
        $sqlconexion->setSql($sql);
        $sqlconexion->consultar();
        $sqlconexion->desconectar();

        $result = array();

        while ($row = $sqlconexion->extraer()) {
            $dato = array(
                'gpo_id' => $row['gpo_id'],
                'ur_id' => $row['ur_id'],
                'ue_id' => $row['ue_id'],
                'prg_id' => $row['prg_id'],
                'spr_id' => $row['spr_id'],
                'py_id' => $row['py_id'],
                'oa_id' => $row['oa_id'],
                'pg_id' => $row['pg_id'],
                'pe_id' => $row['pe_id'],
                'cff_id' => $row['cff_id'],
                'fte_id' => $row['fte_id'],
                'sft_id' => $row['sft_id'],
                'ori_id' => $row['ori_id'],
                'sor_id' => $row['sor_id'],
                'spr_id' => $row['spr_id'],
                'cfi_id' => $row['cfi_id'],
                'cfi_anio' => $row['cfi_anio'],
                'tcp_id' => $row['tcp_id'],

                'dmo_importe' => $row['dmo_importe'],
                'dmo_ene' => $row['dmo_ene'],
                'dmo_feb' => $row['dmo_feb'],
                'dmo_mar' => $row['dmo_mar'],
                'dmo_abr' => $row['dmo_abr'],
                'dmo_may' => $row['dmo_may'],
                'dmo_jun' => $row['dmo_jun'],
                'dmo_jul' => $row['dmo_jul'],
                'dmo_ago' => $row['dmo_ago'],
                'dmo_sep' => $row['dmo_sep'],
                'dmo_oct' => $row['dmo_oct'],
                'dmo_nov' => $row['dmo_nov'],
                'dmo_dic' => $row['dmo_dic'],
            );
            array_push($result, $dato);
        } //fin while
        return $result;
    }
} //FIN consulta_MovtoFolio