<?php

require_once dirname(__FILE__) . '/../../lib/scp/modelScp.php';
require_once dirname(__FILE__) . '/../../lib/Math.php';

class modelAdecuacionMetasInversion extends modelscp
{
    public function getConsultaTabla($quien, $prm)
    {
        switch ($quien) {
            case 'ur':
                $sql = "
                    SELECT *,m.omo_id origenModificacion,'ADECUACION DE METAS' AS tmd_nombre,
                    1 habilitado
                    FROM scp_ModificacionMeta m
                    INNER JOIN cat_EstadoModificacion e using(emo_id)
                    INNER JOIN cat_ModalidadModificacion c  using(mmd_id)
                    INNER JOIN cat_OrigenModificacion o using(omo_id)
                    WHERE m.mme_gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND m.mme_ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . "
                    AND m.emo_id NOT IN (14,15,16)
                    #AND m.omo_id = 'A'
                    AND m.mme_subsecretaria=2
                    AND tfm_id='M';
                ";
                return $sql;
                break;
            case 'planeacion':
                $sql = "
                    SELECT *,m.omo_id origenModificacion,'ADECUACION DE METAS' AS tmd_nombre,
                    1 habilitado
                    FROM scp_ModificacionMeta m
                    INNER JOIN cat_EstadoModificacion e using(emo_id)
                    INNER JOIN cat_ModalidadModificacion c using(mmd_id)
                    INNER JOIN cat_OrigenModificacion o using(omo_id)
                    WHERE m.mme_gpo_id = " . (isset($prm->mme_gpo_id) ? $prm->mme_gpo_id
                    : $_SESSION[_S]['clasAdm-gpo']) . '
                    AND m.mme_ur_id = ' . (isset($prm->mme_ur_id) ? $prm->mme_ur_id
                    : $_SESSION[_S]['clasAdm-ur']) . "
                    AND m.emo_id IN (6,11,17)
                    AND tfm_id='M'
                    ORDER BY m.mme_id;
                ";
                return $sql;
                break;
        }
    }

    public function getConsultaTablasURs($quien, $nivelRevision)
    {
        $nivelRevision = $nivelRevision != '' ? $nivelRevision : '';
        switch ($quien) {
            case 'planeacion':
                switch ($nivelRevision) {
                    case 'alta':
                    case 'revisionSEFIN':
                        $sql = "
                            SELECT *, COUNT(*) numFolios FROM(
                            SELECT  m.mme_gpo_id, m.mme_ur_id,u.ur_clave, u.ur_nombre
                            FROM scp_ModificacionMeta m
                            LEFT JOIN scp_DetalleModificacionMeta d USING(omo_id, mme_gpo_id, mme_ur_id, mme_id)
                            JOIN cat_UnidadResponsable u ON mme_gpo_id=gpo_id AND mme_ur_id=ur_id
                            -- JOIN cat_EstadoModificacion USING(emo_id) -- Por si queremos ver el estado de la meta se utiliza esta tabla
                            WHERE m.emo_id IN (6, 11, 17) and m.mme_subsecretaria=2 AND tfm_id='M'
                            GROUP BY m.mme_gpo_id, m.mme_ur_id, m.mmd_id, m.mme_id,m.omo_id) m
                            GROUP BY m.mme_gpo_id,m.mme_ur_id";
                        return $sql;
                        break;
                    case 'validacionSEFIN':
                        $sql = "
                            SELECT *, COUNT(*) numFolios FROM(
                            SELECT m.omo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, u.ur_clave, u.ur_nombre
                            FROM scp_ModificacionMeta m
                            INNER JOIN scp_DetalleModificacionMeta d USING(omo_id, mme_gpo_id, mme_ur_id, mme_id)
                            JOIN cat_UnidadResponsable u ON mme_gpo_id=gpo_id AND mme_ur_id=ur_id
                            -- JOIN cat_EstadoModificacion USING(emo_id) -- Por si queremos ver el estado de la meta se utiliza esta tabla
                            WHERE m.emo_id IN (10, 13) and m.mme_subsecretaria=2 AND tfm_id='M'
                            GROUP BY m.mme_gpo_id, m.mme_ur_id, m.mmd_id, m.mme_id,m.omo_id) m
                            GROUP BY m.mme_gpo_id, m.mme_ur_id,m.omo_id,m.mme_id;";
                        return $sql;
                        break;
                    case 'autorizacionSEFIN':
                        $sql = "
                            SELECT *, COUNT(*) numFolios FROM(
                            SELECT m.omo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, u.ur_clave, u.ur_nombre
                            FROM scp_ModificacionMeta m
                            INNER JOIN scp_DetalleModificacionMeta d USING(omo_id, mme_gpo_id, mme_ur_id, mme_id)
                            JOIN cat_UnidadResponsable u ON mme_gpo_id=gpo_id AND mme_ur_id=ur_id
                            -- JOIN cat_EstadoModificacion USING(emo_id) -- Por si queremos ver el estado de la meta se utiliza esta tabla
                            WHERE m.emo_id = 12 and m.mme_subsecretaria=2 AND tfm_id='M'
                            GROUP BY m.mme_gpo_id, m.mme_ur_id, m.mmd_id, m.mme_id,m.omo_id) m
                            GROUP BY m.mme_gpo_id, m.mme_ur_id,m.omo_id,m.mme_id;";
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
                    SELECT m.omo_id,m.emo_id,om.omo_nombre,m.mme_gpo_id,m.mme_ur_id,m.mme_id,e.epg_id,
                    e.gpo_id,e.ur_id,e.prg_id,e.spr_id,e.py_id,e.oa_id,o.oa_nombre
                    FROM scp_ModificacionMeta m
                    INNER JOIN scp_DetalleModificacionMeta dm USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
                    INNER JOIN enc_Programatico e USING(epg_id)
                    INNER JOIN cat_ObraActividad o USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                    INNER JOIN cat_OrigenModificacion om USING (omo_id)
                    ' . ($mme->getOmo_id() != '' ?
                    " WHERE omo_id='" . $mme->getOmo_id() . "' " : '') . '
                    ' . ($mme->getMme_gpo_id() != '' ?
                    ' AND mme_gpo_id=' . $mme->getMme_gpo_id() . ' ' : '') . '
                    ' . ($mme->getMme_ur_id() != '' ?
                    ' AND mme_ur_id=' . $mme->getMme_ur_id() . ' ' : '') . '
                    ' . ($mme->getMme_id() != '' ?
                    ' AND mme_id=' . $mme->getMme_id() . '' : '') . ';
                ';
                return $sql;
                break;
            case 'ur':
                $sql = '
                    SELECT m.omo_id,m.emo_id,m.mme_gpo_id,m.mme_ur_id,m.mme_id,e.epg_id,
                    e.gpo_id,e.ur_id,e.prg_id,e.spr_id,e.py_id,e.oa_id,o.oa_nombre
                    FROM scp_ModificacionMeta m
                    INNER JOIN scp_DetalleModificacionMeta dm USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
                    INNER JOIN enc_Programatico e USING(epg_id)
                    INNER JOIN cat_ObraActividad o USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                    ' . ($mme->getOmo_id() != '' ?
                    " WHERE omo_id='" . $mme->getOmo_id() . "' " : '') . '
                    ' . ($mme->getMme_gpo_id() != '' ?
                    ' AND mme_gpo_id=' . $mme->getMme_gpo_id() . ' ' : '') . '
                    ' . ($mme->getMme_ur_id() != '' ?
                    ' AND mme_ur_id=' . $mme->getMme_ur_id() . ' ' : '') . '
                    ' . ($mme->getMme_id() != '' ?
                    ' AND mme_id=' . $mme->getMme_id() . '' : '') . ';
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
                INNER JOIN scp_DetalleModificacionMeta d USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
                WHERE m.omo_id = '{$mme->getOmo_id()}' AND
                m.mme_gpo_id = {$mme->getMme_gpo_id()} AND
                m.mme_ur_id = {$mme->getMme_ur_id()} AND
                m.mme_id = {$mme->getMme_id()}";
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
        $datos = [0, ''];
        $error = [];
        $noErrores = 0;

        //VERIFICAMOS SI EL FOLIO TIENE REGISTROS
        $tieneMovimientos = $this->getEliminaFolio($mme);
        $tieneMovimientos = $tieneMovimientos['registros'] ? true : false;

        if ($mme->getOmo_id() != '' && $mme->getMme_gpo_id() != '' && $mme->getMme_ur_id()
            != '' && $mme->getMme_id() != '') {
            if (!$tieneMovimientos) {
                $noErrores++;
                $error[$noErrores] = ['numero' => 22, 'mensaje' => 'El folio no tiene movimientos '];
            }

            //VALIDAR QUE LAS OBRAS/ACTIVIDADES TENGAN MODIFICACION DE METAS
            $sql = "
                SELECT d.omo_id,d.mme_gpo_id,d.mme_ur_id,d.mme_id,d.epg_id,
                o.prg_id,o.spr_id,o.py_id,o.oa_id,o.oa_nombre
                FROM scp_DetalleModificacionMeta d
                INNER JOIN enc_Programatico e USING(epg_id)
                INNER JOIN cat_ObraActividad o USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                WHERE d.omo_id = '" . $mme->getOmo_id() . "'
                AND d.mme_gpo_id = " . $mme->getMme_gpo_id() . '
                AND d.mme_ur_id = ' . $mme->getMme_ur_id() . '
                AND d.mme_id = ' . $mme->getMme_id() . ';
            ';
            $datos = $this->getDatos($sql, false);
            $numRegistros = $datos['registros'];
            $datos = $datos['datos'];
            if ($numRegistros > 0) {
                $y = '';
                $i = 0;
                while ($i < count($datos)) {
                    $sqlMeta = '
                        SELECT epg_id FROM ' . BD_TEMPORAL . ".tem_ModificacionMetaTrimestral
                        WHERE omo_id = '" . $mme->getOmo_id() . "'
                        AND mme_gpo_id = " . $mme->getMme_gpo_id() . '
                        AND mme_ur_id = ' . $mme->getMme_ur_id() . '
                        AND mme_id = ' . $mme->getMme_id() . '
                        AND epg_id = ' . $datos[$i]['epg_id'] . ';
                    ';
                    $consultaMetas = $this->getDatos($sqlMeta, false);
                    $numReg = $consultaMetas['registros'];
                    if ($numReg == 0) {
                        $y = $datos[$i]['prg_id'] . '-' . $datos[$i]['spr_id'] . '-' . $datos[$i]['py_id'] . '-' .
                            $datos[$i]['oa_id'] . '-' . $datos[$i]['oa_nombre'];
                        //No hay modificacion de metas temporal
                        $noErrores++;
                        $error[$noErrores] = ['numero' => 23, 'mensaje' => "La actividad: $y no tiene modificacion de metas, elimine el movimiento o registre la modificacion de la meta",
                        ];
                    }
                    $i++;
                }
            }
        }
        return $error;
    }
}
// fin clase
