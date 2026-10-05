<?php
require_once dirname(__FILE__) . '/../../lib/scp/modelScp.php';
require_once dirname(__FILE__) . '/../../lib/Math.php';

class modelAdecuacionMetasMir extends modelscp
{

    public function getConsultaTabla($quien, $prm)
    {
        $fechaActual = date("Y-m-d H:i:00", time());
        switch ($quien) {
            case 'ur':
                $sql = "
                    SELECT *,m.omo_id origenModificacion,'ADECUACION DE METAS MIR' AS tmd_nombre,
                    1 habilitado
                    FROM scp_ModificacionMeta m
                    INNER JOIN cat_EstadoModificacion e using(emo_id)
                    INNER JOIN cat_ModalidadModificacion c  using(mmd_id)
                    INNER JOIN cat_OrigenModificacion o using(omo_id)
                    WHERE m.mme_gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . "
                    AND m.mme_ur_id = " . $_SESSION[_S]['clasAdm-ur'] . "
                    AND m.emo_id NOT IN (14,15,16) AND tfm_id = 'C'
                    AND m.mme_subsecretaria=2;
                ";
                return $sql;
                break;
            case 'planeacion':
                $sql = "
                    SELECT *,m.omo_id origenModificacion,'ADECUACION DE METAS MIR' AS tmd_nombre,
                    1 habilitado
                    FROM scp_ModificacionMeta m
                    INNER JOIN cat_EstadoModificacion e using(emo_id)
                    INNER JOIN cat_ModalidadModificacion c using(mmd_id)
                    INNER JOIN cat_OrigenModificacion o using(omo_id)
                    WHERE m.mme_gpo_id = " . (isset($prm->mme_gpo_id) ? $prm->mme_gpo_id
                    : $_SESSION[_S]['clasAdm-gpo']) . "
                    AND m.mme_ur_id = " . (isset($prm->mme_ur_id) ? $prm->mme_ur_id
                    : $_SESSION[_S]['clasAdm-ur']) . "
                    AND m.emo_id IN (6,11,17) AND tfm_id = 'C'
                    ORDER BY m.mme_id;
                ";
                return $sql;
                break;
        }
    }

    public function getConsultaTablasURs($quien, $nivelRevision)
    {
        $nivelRevision = $nivelRevision != '' ? $nivelRevision : "";
        switch ($quien) {
            case 'planeacion':
                switch ($nivelRevision) {
                    case 'alta':
                    case 'revisionSEFIN':
                        $sql = "
                            SELECT *, COUNT(*) numFolios FROM(
                            SELECT  m.mme_gpo_id, m.mme_ur_id,u.ur_clave, u.ur_nombre
                            FROM scp_ModificacionMeta m
                            LEFT JOIN scp_DetalleModificacionMetaComponente d USING(omo_id, mme_gpo_id, mme_ur_id, mme_id)
                            JOIN cat_UnidadResponsable u ON mme_gpo_id=u.gpo_id AND mme_ur_id=u.ur_id
                            -- JOIN cat_EstadoModificacion USING(emo_id) -- Por si queremos ver el estado de la meta se utiliza esta tabla
                            WHERE m.emo_id IN (6,11, 17) and m.mme_subsecretaria=2 and m.tfm_id = 'C'
                            GROUP BY m.mme_gpo_id, m.mme_ur_id, m.mmd_id, m.mme_id,m.omo_id) m
                            GROUP BY m.mme_gpo_id,m.mme_ur_id;";
                        return $sql;
                        break;
                    case 'validacionSEFIN':
                        $sql = "
                            SELECT *, COUNT(*) numFolios FROM(
                            SELECT m.omo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, u.ur_clave, u.ur_nombre, emo_id, emo_nombre
                            FROM scp_ModificacionMeta m
                            INNER JOIN scp_DetalleModificacionMetaComponente d USING(omo_id, mme_gpo_id, mme_ur_id, mme_id)
                            JOIN cat_UnidadResponsable u ON m.mme_gpo_id= u.gpo_id AND m.mme_ur_id=u.ur_id
                            JOIN cat_EstadoModificacion USING(emo_id) -- Por si queremos ver el estado de la meta se utiliza esta tabla
                            WHERE m.emo_id IN (10, 13) and m.mme_subsecretaria=2 and m.tfm_id = 'C'
                            GROUP BY m.mme_gpo_id, m.mme_ur_id, m.mmd_id, m.mme_id,m.omo_id) m
                            GROUP BY m.mme_gpo_id,m.mme_ur_id;";
                        return $sql;
                        break;
                    case 'autorizacionSEFIN':
                        $sql = "
                            SELECT *, COUNT(*) numFolios FROM(
                            SELECT m.omo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, u.ur_clave, u.ur_nombre
                            FROM scp_ModificacionMeta m
                            INNER JOIN scp_DetalleModificacionMetaComponente d USING(omo_id, mme_gpo_id, mme_ur_id, mme_id)
                            JOIN cat_UnidadResponsable u ON m.mme_gpo_id= u.gpo_id AND m.mme_ur_id=u.ur_id
                            -- JOIN cat_EstadoModificacion USING(emo_id) -- Por si queremos ver el estado de la meta se utiliza esta tabla
                            WHERE m.emo_id = 12 and m.mme_subsecretaria=2 and m.tfm_id = 'C'
                            GROUP BY m.mme_gpo_id, m.mme_ur_id, m.mmd_id, m.mme_id,m.omo_id) m
                            GROUP BY m.mme_gpo_id, m.mme_ur_id;";
                        return $sql;
                        break;
                }
                break;
        } // fin case pricipal
    }

// fin funcion getConsultaTablasURs

    public function getConsultaTablaMovimientosFolio($quien, $mme)
    {
        $sql = '';

        switch ($quien) {
            case 'sefin':
                $sql = '
                    SELECT m.omo_id, m.emo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, prgs_id, sprs_id, d.epfi_id, tri_id, ind_id, pdf_id,tri_id, CONCAT(prgs_id," - ",prg_nombre) programa,
                    CONCAT(sprs_id," - ",sprs_nombre) subprograma, CONCAT(ind_id," - ",ind_nombre) indicador, CONCAT(mme_gpo_id,mme_ur_id) ur
                    FROM '. BD_CTRL .'.scp_ModificacionMeta m
                    INNER JOIN '. BD_CTRL .'.scp_DetalleModificacionMetaComponente d USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
                    INNER JOIN '. BD_CTRL .'.enc_ProductoFinalIndicador pi USING (epfi_id)
                    INNER JOIN '. BD_CTRL .'.cat_Programa USING(gpo_id, ur_id)
                    INNER JOIN '. BD_CTRL .'.cat_Subprogramas USING (prgs_id, sprs_id, pdf_id)
                    INNER JOIN '. BD_CTRL .'.cat_Indicador USING(nim_id, ind_id)
                    WHERE omo_id="' . $mme->getOmo_id() . '" 
                    AND mme_gpo_id=' . $mme->getMme_gpo_id() . ' 
                    AND mme_ur_id=' . $mme->getMme_ur_id() . '
                    AND mme_id=' . $mme->getMme_id() . ' 
                    AND prgs_id=prg_id;
                ';
                return $sql;
                break;
            case 'ur':
                $sql = '
                    SELECT m.omo_id, m.emo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, prgs_id, sprs_id, d.epfi_id, tri_id, ind_id, pdf_id,tri_id, CONCAT(prgs_id," - ",prg_nombre) programa,
                    CONCAT(sprs_id," - ",sprs_nombre) subprograma, CONCAT(ind_id," - ",ind_nombre) indicador, CONCAT(mme_gpo_id,mme_ur_id) ur
                    FROM '. BD_CTRL .'.scp_ModificacionMeta m
                    INNER JOIN '. BD_CTRL .'.scp_DetalleModificacionMetaComponente d USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
                    INNER JOIN '. BD_CTRL .'.enc_ProductoFinalIndicador pi USING (epfi_id)
                    INNER JOIN '. BD_CTRL .'.cat_Programa USING(gpo_id, ur_id)
                    INNER JOIN '. BD_CTRL .'.cat_Subprogramas USING (prgs_id, sprs_id, pdf_id)
                    INNER JOIN '. BD_CTRL .'.cat_Indicador USING(nim_id, ind_id)
                    WHERE omo_id="' . $mme->getOmo_id() . '" 
                    AND mme_gpo_id=' . $mme->getMme_gpo_id() . ' 
                    AND mme_ur_id=' . $mme->getMme_ur_id() . '
                    AND mme_id=' . $mme->getMme_id() . ' 
                    AND prgs_id=prg_id;
                ';
                return $sql;
                break;
        }
    }
    /* VERIFICAR SI EL FOLIO YA TIENE MOVIMIENTOS */

    public function getEliminaFolio($mme)
    {
        if ($mme->getMme_id() == '') {
            $datos['registros'] = 1;
        }

        if ($mme->getMme_id() != '' && $mme->getOmo_id() != '' && $mme->getMme_gpo_id()
            != '' && $mme->getMme_ur_id() != '') {
            $sql = "SELECT m.mme_id
                FROM scp_ModificacionMeta m
                INNER JOIN scp_DetalleModificacionMetaComponente d USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
                WHERE m.omo_id = '{$mme->getOmo_id()}' AND
                m.mme_gpo_id = {$mme->getMme_gpo_id()} AND
                m.mme_ur_id = {$mme->getMme_ur_id()} AND
                m.mme_id = {$mme->getMme_id()} AND 
                m.tfm_id = 'C'";

            $datos = $this->getDatos($sql, true);
        }
        return $datos;
    }

// fin getEliminaFolio

    public function getMotivoRechazo($mme, $quien)
    {
        $estado = null;
        $datos['datos'][0]['bit_observacion'] = 0;
        if ($mme->getMme_id() != '' && $mme->getOmo_id() != '' && $mme->getMme_gpo_id()
            != '' && $mme->getMme_ur_id() != '') {
            switch ($quien) {
                case 'ur':
                    switch ($mme->getEmo_id()) {
                        case 3:
                            $estado = ' and b.emo_id=3';
                            break;
                        case 5:
                            $estado = ' and b.emo_id=5';
                            break;
                        case 7:
                            $estado = ' and b.emo_id=7';
                            break;
                        case 9:
                            $estado = ' and b.emo_id=9';
                            break;
                    }
                    break;
                case 'sefin':
                case 11:
                    $estado = ' and b.emo_id=11';
                    break;
                case 13:
                    $estado = ' and b.emo_id=13';
                    break;
            }

            if (!is_null($estado)) {
                $sql = '
                    SELECT " FALTA HACER EL QUERY PARA LA OBSERVACION" as bit_observacion;
                ';
                $datos = $this->getDatos($sql, false);
            } else {
                $datos['datos'][0]['bit_observacion'] = 0;
            }
        }
        return $datos['datos'][0];
    }

// fin getMotivo rechazo

    public function getValidaFolio($mme)
    {
        $datos = array(0, "");
        $error = array();
        $noErrores = 0;

        //VERIFICAMOS SI EL FOLIO TIENE REGISTROS
        $tieneMovimientos = $this->getEliminaFolio($mme);
        $tieneMovimientos = $tieneMovimientos['registros'] ? true : false;

        if ($mme->getOmo_id() != '' && $mme->getMme_gpo_id() != '' && $mme->getMme_ur_id()
            != '' && $mme->getMme_id() != '') {
            if (!$tieneMovimientos) {
                $noErrores++;
                $error[$noErrores] = array("numero" => 22, "mensaje" => "El folio no tiene movimientos ");
            }
        }
        return $error;
    }
}
// fin clase
