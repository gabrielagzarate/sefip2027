<?php

// session_start();
require_once dirname(__FILE__) . '/../../lib/scp/modelScp.php';

class modelCompromisos extends modelscp
{
    public function getDatosObra($prm)
    {
        if (!isset($_SESSION[_S]['usr_id'])) {
            return false;
        }

        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? (int)$_SESSION[_S]['gpo_id'] : (int)$_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? (int)$_SESSION[_S]['ur_id'] : (int)$_SESSION[_S]['clasAdm-ur'];
        $ipu_id = $prm['ipu_id'];

        $sql = '
            SELECT ipu_numeroContrato, ipu_fechaContrato, rfc_id,rfc_nombre, rfc_paterno, rfc_materno, p.rfc_id habilitado, soc_nombre
            FROM sinpres.cat_InversionPublica a
                LEFT JOIN cat_Proveedor p USING (rfc_id)
                INNER JOIN sinpres.cat_Proveedores USING (rfc_id)
                INNER JOIN sinpres.cat_SociedadProveedor USING (soc_id)
                INNER JOIN sinpres.vin_UnidadResponsable vr USING (vur_id)
                INNER JOIN (SELECT
                        uur_id
                    FROM sinpres.vin_UnidadResponsable
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND vur_ejercicio = ?
                )v USING (uur_id )
            WHERE ipu_id = ?;
        ';
        $params = [$gpo_id, $ur_id, APP_EJERCICIO, $ipu_id];
        $datos = $this->getDatos($sql, false, $params);
        return $datos;
    }

    public function getPresupuestoComprometido($prm)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $paramas = [$gpo_id, $ur_id, $prm['epg_id'], $prm['ecf_id'], $prm['pg_id'], $prm['pe_id'], $prm['tcp_id'], 'C'];
        $com = '';
        if (isset($prm['cmp_id']) && $prm['cmp_id'] > 0) {
            $com = ' AND cmp_id != ?' . PHP_EOL;
            array_push($paramas, $prm['cmp_id']);
        }

        $sql = '
            SELECT
                SUM(cmp_importe) AS cmp_importe
            FROM scp_Compromiso
            WHERE gpo_id = ?
                AND ur_id = ?
                AND epg_id = ?
                AND ecf_id = ?
                AND pg_id = ?
                AND pe_id = ?
                AND tcp_id = ?
                AND ecm_id != ? '
            . $com;
        $datos = $this->getDatos($sql, false, $paramas);
        return $datos;
    }

    public function getDatosCompromiso($cmp, $asarray = true)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $params = [$gpo_id, $ur_id, $cmp, $gpo_id, $ur_id, $cmp];
        $sql = '
            SELECT c.gpo_id, c.ur_id, cmp_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ecm_id, cmp_importe, tcl_id, stc_id,
                IF(ecm_id ="A"
                    ,1
                    ,0) AS Cancelable,
                IF(ecm_id IN("C","A","P","D")
                    ,"NO"
                    ,"SI") AS Editable,
                IF(importe IS NULL OR importe = 0
                    ,"NO"
                    ,"SI") AS Usado,
                importe,
                c.usr_id AS Usuario,
                ecr_id,
                cmp_tipoCompromiso
            FROM scp_Compromiso c
            LEFT JOIN(
                SELECT
                    gpo_id,ur_id,cmp_id,SUM(ncp_percepcionNeto) AS importe
                FROM scp_Presupuestal
                INNER JOIN scp_Afectacion USING ( gpo_id,ur_id,afe_id )
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND cmp_id = ?
                    AND eaf_id != "C"
                GROUP BY gpo_id,ur_id,cmp_id
            )afe USING ( gpo_id,ur_id,cmp_id)
            LEFT JOIN cat_CargaMasiva USING ( tem_carga )
            WHERE c.gpo_id = ?
                AND c.ur_id = ?
                AND cmp_id = ?';
        if ($asarray) {
            $datos = $this->getDatos($sql, false, $params);
        } else {
            $datos = $this->getDatosObj($sql, false, $params);
        }
        return $datos['datos'][0];
    }

    public function getUsoCompromiso($cmp_id)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $params = [$gpo_id, $ur_id, $cmp_id, $gpo_id, $ur_id, $cmp_id, 'C'];
        $sql = '
            SELECT cmp_id AS "N Compromiso",
                IF(ISNULL(percepciones) AND ISNULL(retenciones)
                    ,cmp_importe
                    ,cmp_importe + percepciones - retenciones) AS "Importe Compromiso",
                ncp_id AS "N Control",
                ncp_percepcionNeto AS "Importe N Control",
                afe_id AS "N Afectacion",
                (afe_percepcion-afe_retencion) AS "Importe Afectacion",
                clc_id AS "N CLC",
                clc_percepcion AS "Percepcion",
                clc_retencion AS "Retencion",
                clc_percepcion-clc_retencion AS "importe"
            FROM scp_Compromiso
                INNER JOIN scp_Presupuestal USING (gpo_id,ur_id,cmp_id)
                INNER JOIN scp_Afectacion USING (gpo_id,ur_id,afe_id,clc_id)
                INNER JOIN scp_Clc USING (gpo_id,ur_id,clc_id)
                LEFT JOIN (SELECT
                        gpo_id,ur_id,cmp_cmp_id as cmp_id,
                        SUM(if(cmp_tipoMovimiento="-"
                            ,cmp_importe
                            ,0)) AS retenciones,
                        SUM(if(cmp_tipoMovimiento="+"
                            ,cmp_importe
                            ,0)) AS percepciones
                    FROM scp_Compromiso
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND cmp_cmp_id = ?
                        AND ecm_id not IN ("G","C")
                        AND cmp_cmp_id IS NOT NULL
                    GROUP BY gpo_id,ur_id,cmp_cmp_id
                )ajustes USING (gpo_id,ur_id,cmp_id)
                WHERE gpo_id = ?
                    AND ur_id = ?
                     AND cmp_id = ?
                    AND eaf_id != ? ';
        $datos = $this->getDatos($sql, false, $params);
        return $datos;
    }

    public function getPresupuestoVigenteComprometido($val, $asarray = true)
    {
        $params = [$val->gpo_id, $val->ur_id, $val->ue_id, $val->epg_id, $val->ecf_id, $val->pg_id, $val->pe_id, $val->tcp_id];
        $sql = '
            SELECT
                SUM(pre_vigente) AS vigente,
                SUM(pre_comprometido) AS comprometido,
                SUM(pre_vigente) - SUM(pre_comprometido) AS disponible
            FROM presupuesto
                INNER JOIN presupuestoComprometido USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                AND ue_id = ?
                AND epg_id = ?
                AND ecf_id = ?
                AND pg_id = ?
                AND pe_id = ?
                AND tcp_id = ? ';

        if ($asarray) {
            $datos = $this->getDatos($sql, false, $params);
        } else {
            $datos = $this->getDatosObj($sql, false, $params);
        }

        return $datos['datos'][0];
    }

    public function getReduccionPresupuesto($val)
    {
        $params = [$val->gpo_id, $val->ur_id, $val->ue_id, $val->epg_id, $val->pg_id, $val->pe_id, $val->ecf_id, $val->tcp_id];
        $sql = '
            SELECT
                dmo_importe
            FROM scp_MaestroModificacion
            INNER JOIN scp_DetalleModificacion USING (omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
            WHERE  gpo_id = ?
                AND ur_id = ?
                AND ue_id = ?
                AND epg_id = ?
                AND  pg_id = ?
                AND pe_id = ?
                AND ecf_id = ?
                AND tcp_id = ?
                AND emo_id NOT IN(14,15,16)
                AND mov_id = "R";';
        $datos = $this->getDatos($sql, false, $params);
        return $datos;
    }

    public function revisaProveedor($rfc)
    {
        $params = [$rfc];
        $sql = '
            SELECT
                rfc_id
            FROM cat_Proveedor
            WHERE rfc_id = ?';
        $datos = $this->getDatos($sql, true, $params);
        if ($datos['registros'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}
