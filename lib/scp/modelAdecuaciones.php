<?php

require_once dirname(__FILE__) . '/../../lib/scp/modelScp.php';
require_once dirname(__FILE__) . '/../../lib/Math.php';
require_once dirname(__FILE__) . '/../../lib/scp/ClaseGasto.php';

class modelAdecuaciones extends modelscp
{
    public function __construct()
    {
        parent::__construct();

        //variables de sesión
        $this->usuario_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id']
        : false;
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur']
        : false;
        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo']
        : false;
        $parametros = [];
    } // Fin __construct

    public function getTareas($prm)
    {
        if (!isset($this->usuario_id)) {
            return false;
        } //fin usuario_id

        $sql = '
            SELECT pe.sis_id, pe.men_id, pe.opc_id, t.tar_id, t.tar_nombre
            FROM cat_Tareas t
            INNER JOIN cat_Permisos pe USING(tar_id)
            INNER JOIN cat_PermisosUsuarios p USING(sis_id,men_id,opc_id,tar_id)
            WHERE p.sis_id = "S" AND p.men_id = 1 AND p.opc_id = 1 and p.usr_id = ' . $this->usuario_id . ' AND p.tar_id NOT IN (1,23) ;
        ';

        $datos = $this->getDatos($sql, false);
        return $datos;
    } // Fin getTareas

    /* VERIFICAR SI EL FOLIO YA TIENE MOVIMIENTOS */
    public function getEliminaFolio($mod)
    {
        if ($mod->getMod_id() == '') {
            $datos['registros'] = 1;
        }

        if ($mod->getMod_id() != '' && $mod->getOmo_id() != '' && $mod->getMod_gpo_id()
            != '' && $mod->getMod_ur_id() != '') {
            $sql = "
                SELECT m.mod_id as mod_id
                FROM scp_DetalleModificacion d
                INNER JOIN scp_MaestroModificacion m USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                WHERE m.omo_id = '{$mod->getOmo_id()}' AND
                m.mod_gpo_id = {$mod->getMod_gpo_id()} AND
                m.mod_ur_id = {$mod->getMod_ur_id()} AND
                m.mod_id = {$mod->getMod_id()}";
            $datos = $this->getDatos($sql, true);
        } // fin if
        return $datos;
    } //Fin getEliminaFolio

    public function getExcenta($mod)
    {
        $excenta = '';
        if ($mod->getMod_id() == '') {
            $datos['registros'] = 1;
        }

        if ($mod->getMod_id() != '' && $mod->getOmo_id() != '' && $mod->getMod_gpo_id()
            != '' && $mod->getMod_ur_id() != '') {
            $sql = '
                SELECT IF(exc.ecf_id=c.ecf_id,1,2) excenta from enc_ClaveFinanciamiento c
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                LEFT JOIN cat_ExcepcionClaveFinanciamiento exc USING(ecf_id)
                WHERE c.ecf_id = ' . $mod->getEcf_id() . ';';
            $datos = $this->getDatos($sql, false);
            $excenta = $datos['datos'][0];
        } // fin if
        return $excenta;
    } //Fin getEliminaFolio

    public function getConsultaBloqueoFolio($mod)
    {
        if ($mod->getOmo_id() != '') {
            $origenOperacion = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
            $sql = '
                SELECT so.cff_id as cff_id from enc_ClaveFinanciamiento c
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE c.ecf_id = ' . $mod->getEcf_id() . ';';
            $datos = $this->getDatos($sql, false);
            $cff_id = $datos['datos'][0];

            if ($mod->getOmo_id() == 'C') {
                $gpo_id = 1;
                $ur_id = 18;
            } else {
                $gpo_id = $mod->getMod_gpo_id();
                $ur_id = $mod->getMod_ur_id();
            } //fin getOmo_id

            $origen = $mod->getOmo_id();
            $sqlPermisosMod = "
                SELECT
                    pum_fechaInicial as pum_fechaInicial,pum_fechaFinal pum_fechaFinal,sts_id sts_id
                FROM cat_PermisosUrModificaciones
                WHERE omo_id = '" . $origen . "'
                AND gpo_id=" . $gpo_id . '
                AND ur_id=' . $ur_id . "
                AND mmd_id='" . $origenOperacion->getMmd_id() . "'
                AND mod_Subsecretaria='" . $mod->getSubsecretaria() . "'
                AND tfm_id='" . $origenOperacion->getTfm_id() . "'
                AND cff_id='" . $cff_id['cff_id'] . "'
                AND tmd_id='" . $origenOperacion->getTmd_id() . "'
                AND sts_id=1;";
            $datos = $this->getDatos($sqlPermisosMod, false);
            $fecha = $datos['datos'][0];

            if ($datos['registros'] > 0) {
                if ($fecha['pum_fechaInicial'] != null && $fecha['pum_fechaFinal']
                    != null) {
                    $fechaActual = strtotime(date('Y-m-d H:i:00', time()));
                    if ($fechaActual >= strtotime($fecha['pum_fechaInicial']) &&
                        $fechaActual <= strtotime($fecha['pum_fechaFinal'])) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return false;
                } // fin fecha
            } else {
                return true;
            } //fin registros
        } else {
            return false;
        } //fin getOmo_id
    } // Fin getConsultaBloqueoFolio

    public function getMotivoRechazo($mod, $quien)
    {
        $estado = null;
        $datos['datos'][0]['bit_observacion'] = 0;

        if ($mod->getMod_id() != '' && $mod->getOmo_id() != '' && $mod->getMod_gpo_id()
            != '' && $mod->getMod_ur_id() != '') {
            switch ($quien) {
                case 'ur':
                    switch ($mod->getEmo_id()) {
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
                    } //fin switch
                    break;
                case 'sefin':
                case 11:
                    $estado = ' and b.emo_id=11';
                    break;
                case 13:
                    $estado = ' and b.emo_id=13';
                    break;
            } // Fin switch

            if (!is_null($estado)) {
                $sql = '
                    SELECT m.mod_id as mod_id, e.emo_nombre as emo_nombre, trim(b.bit_observacion) as bit_observacion,
                    date (b.usr_fecha) as usr_fecha
                    FROM scp_MaestroModificacion m
                    JOIN cat_EstadoModificacion e using(emo_id)
                    JOIN ' . BD_BITACORA . '.bit_MaestroModificacion b using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                    WHERE m.omo_id="' . $mod->getOmo_id() . '"
                    AND m.mod_gpo_id=' . $mod->getMod_gpo_id() . '
                    AND m.mod_ur_id=' . $mod->getMod_ur_id() . '
                    AND m.mod_id=' . $mod->getMod_id() . ' ' . $estado . '
                    ORDER BY b.usr_fecha asc, b.bit_mod;
                ';
                $datos = $this->getDatos($sql, false);
            } else {
                $datos['datos'][0]['bit_observacion'] = 0;
            } // fin $estado
        } // FIN IF
        return $datos['datos'][0];
    } // Fin getMotivoRechazo

    public function getConsultaTabla($quien, $prm)
    {
        $fechaActual = date('Y-m-d H:i:00', time());

        switch ($quien) {
            case 'ur':
                $sql = "
                    SELECT
                        folio.*,
                        IF((activate.siFecha=1 and '" . $fechaActual . "' between activate.pum_fechaInicial and activate.pum_fechaFinal) OR
                        activate.noFecha=1 ,1,2) as habilitado,
                        IF(habilitado=2  AND emo_id in(1,3),'FOLIO BLOQUEADO','') AS revisar,
                        IF(habilitado=2  AND emo_id in(1,3),1,2) AS blocked, excenta
                    FROM(
                        SELECT
                            m.mod_gpo_id,m.mod_ur_id,m.mod_gpo_id as gpo_id,m.mod_ur_id as ur_id, tmd_id,
                            om.omo_id origenModificacion,om.omo_id,op.tfm_id,
                            (m.mod_id*1) mod_id,m.mod_clave, m.mod_concepto,
                            op.enc_oop, m.mod_tipoMovimiento, m.ecf_id,
                            op.mmd_id,cff_id,m.mod_Subsecretaria,
                            t.tmd_nombre, e.emo_id, e.emo_nombre, e.emo_color,
                            cfi_clave,om.omo_nombre,m.mod_fechaFolio,
                            IF(exc.ecf_id=m.ecf_id,1,2) as excenta
                        FROM scp_MaestroModificacion m
                        INNER JOIN enc_OrigenOperacion op using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion om using(omo_id)
                        INNER JOIN(
                            SELECT
                                cf.ecf_id,scf.cfi_clave,so.cff_id
                            FROM enc_ClaveFinanciamiento cf
                            INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion t using(tmd_id)
                        INNER JOIN cat_EstadoModificacion e using(emo_id)
                        LEFT JOIN cat_ExcepcionClaveFinanciamiento exc USING(ecf_id)
                        WHERE m.mod_gpo_id = ' . $this->adm_grupo . '
                        AND m.mod_ur_id = ' . $this->adm_ur . "
                        AND e.emo_id NOT IN (14,15,16)
                        AND om.omo_id = 'A'
                        " . (isset($prm->ecf_id) ?
                    'AND m.ecf_id=' . $prm->ecf_id . '' : '') . "
                        ORDER BY m.mod_id
                        ) folio LEFT JOIN (
                            SELECT
                                gpo_id, ur_id, omo_id, mmd_id, mod_Subsecretaria, tfm_id, cff_id, tmd_id,
                                pum_fechaInicial,pum_fechaFinal,
                                IF(not isnull(pum_fechaInicial) and not isnull(pum_fechaFinal) and sts_id=1,1,2) as siFecha,
                                IF(isnull(pum_fechaInicial) and isnull(pum_fechaFinal) and sts_id=1,1,2) as noFecha,
                                IF( ('" . $fechaActual . "' > date(pum_fechaFinal)
                                OR ('" . $fechaActual . "' <= date(pum_fechaFinal))
                                OR (sts_id=2 and isnull(pum_fechaFinal))),2,1) as habilitado
                            FROM cat_PermisosUrModificaciones
                        )activate USING(gpo_id,ur_id,omo_id,mmd_id,mod_Subsecretaria,tfm_id,cff_id,tmd_id) order by mod_id;
                    ";
                return $sql;
                break;
            case 'drh':
                $sql = "
                    SELECT
                        folio.*,if((activate.siFecha=1 and '" . $fechaActual . "' between activate.pum_fechaInicial and activate.pum_fechaFinal) or activate.noFecha=1 ,1,2) as habilitado,
                        if(habilitado=2  AND emo_id in(1,3),'FOLIO BLOQUEADO','') AS revisar,if(habilitado=2  AND emo_id in(1,3),1,2) AS blocked
                    FROM (
                        SELECT
                            m.mod_gpo_id,m.mod_ur_id,if(om.omo_id = 'C' or om.omo_id='D', 1,mod_gpo_id) gpo_id,
                            if(om.omo_id = 'C' or om.omo_id='D', 18,mod_ur_id) ur_id, tmd_id,
                            om.omo_id origenModificacion,om.omo_id,op.tfm_id,
                            (m.mod_id*1) mod_id,m.mod_clave, m.mod_concepto, op.enc_oop, m.mod_tipoMovimiento, m.ecf_id, op.mmd_id,cff_id,m.mod_Subsecretaria,
                            t.tmd_nombre, e.emo_id, e.emo_nombre, e.emo_color,cfi_clave,om.omo_nombre,m.mod_fechaFolio,
                            IF(exc.ecf_id=m.ecf_id,1,2) as excenta
                        FROM scp_MaestroModificacion m
                        INNER JOIN enc_OrigenOperacion op using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion om using(omo_id)
                        INNER JOIN(
                            SELECT cf.ecf_id,scf.cfi_clave,so.cff_id
                            FROM enc_ClaveFinanciamiento cf
                            INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion t using(tmd_id)
                        INNER JOIN cat_EstadoModificacion e using(emo_id)
                        LEFT JOIN cat_ExcepcionClaveFinanciamiento exc USING(ecf_id)
                        WHERE mod_gpo_id = ' . $this->adm_grupo . '
                        AND m.mod_ur_id = ' . $this->adm_ur . "
                        AND e.emo_id NOT IN (14,15,16)
                        AND op.tfm_id='A' AND mod_Subsecretaria = 1
                        AND om.omo_id = 'C'
                        " . (isset($prm->ecf_id) ?
                    'AND m.ecf_id=' . $prm->ecf_id . '' : '') . "
                            ORDER BY m.mod_id
                        )folio LEFT JOIN (
                            SELECT gpo_id, ur_id, omo_id, mmd_id, mod_Subsecretaria, tfm_id, cff_id, tmd_id, pum_fechaInicial,
                            pum_fechaFinal,
                            IF(not isnull(pum_fechaInicial) and not isnull(pum_fechaFinal) and sts_id=1,1,2) as siFecha,
                            IF(isnull(pum_fechaInicial) and isnull(pum_fechaFinal) and sts_id=1,1,2) as noFecha,
                            IF( ('" . $fechaActual . "' > date(pum_fechaFinal) OR ('" . $fechaActual . "' <= date(pum_fechaFinal))
                            OR (sts_id=2 and isnull(pum_fechaFinal))),2,1) as habilitado
                            FROM cat_PermisosUrModificaciones
                            WHERE mod_Subsecretaria = 1
                        )activate USING(gpo_id,ur_id,omo_id,mmd_id,mod_Subsecretaria,tfm_id,cff_id,tmd_id);
                    ";
                return $sql;
                break;
            case 'presupuesto':
                $sql = '
                    SELECT folio.*
                    FROM (
                        SELECT
                            m.mod_gpo_id,m.mod_ur_id,m.mod_gpo_id as gpo_id,
                            m.mod_ur_id as ur_id,om.omo_id origenModificacion,
                            om.omo_id,op.tfm_id,(m.mod_id*1) mod_id, m.mod_clave,
                            m.mod_concepto, op.enc_oop, m.mod_tipoMovimiento, m.ecf_id,op.mmd_id,cff_id,
                            m.mod_Subsecretaria,t.tmd_nombre, e.emo_id, e.emo_nombre,
                            e.emo_color,cfi_clave,om.omo_nombre,m.mod_fechaFolio,
                            IF(exc.ecf_id=m.ecf_id,1,2) as excenta
                        FROM scp_MaestroModificacion m
                        INNER JOIN enc_OrigenOperacion op using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion om using(omo_id)
                        INNER JOIN(
                            SELECT
                                cf.ecf_id,scf.cfi_clave,so.cff_id
                            FROM enc_ClaveFinanciamiento cf
                            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion t using(tmd_id)
                        INNER JOIN cat_EstadoModificacion e using(emo_id)
                        LEFT JOIN cat_ExcepcionClaveFinanciamiento exc USING(ecf_id)
                        WHERE m.mod_gpo_id = ' . (isset($prm->mod_gpo_id) ? $prm->mod_gpo_id
                    : $this->adm_grupo) . '
                        AND m.mod_ur_id = ' . (isset($prm->mod_ur_id) ? $prm->mod_ur_id
                    : $this->adm_ur) . '
                        AND e.emo_id IN (6,8,11,17)
                        ORDER BY m.mod_id
                    )folio;
                ';
                return $sql;
                break;
            case 'planeacion':
                $sql = '
                    SELECT folio.*
                    FROM(
                        SELECT
                            m.mod_gpo_id,m.mod_ur_id,m.mod_gpo_id as gpo_id,m.mod_ur_id as ur_id,
                            om.omo_id origenModificacion,om.omo_id,op.tfm_id,(m.mod_id*1) mod_id,
                            m.mod_clave, m.mod_concepto, op.enc_oop, m.mod_tipoMovimiento, m.ecf_id,
                            op.mmd_id,cff_id,m.mod_Subsecretaria,t.tmd_nombre, e.emo_id,
                            e.emo_nombre, e.emo_color,cfi_clave,om.omo_nombre,m.mod_fechaFolio,
                            IF(exc.ecf_id=m.ecf_id,1,2) as excenta
                        FROM scp_MaestroModificacion m
                        INNER JOIN enc_OrigenOperacion op using(enc_oop,omo_id)
                        INNER JOIN cat_OrigenModificacion om using(omo_id)
                        INNER JOIN(
                            SELECT
                                cf.ecf_id,scf.cfi_clave,so.cff_id
                            FROM enc_ClaveFinanciamiento cf
                            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so using(cff_id,fte_id,sft_id,ori_id,sor_id)
                        )as cfi using(ecf_id)
                        INNER JOIN cat_TipoModificacion t using(tmd_id)
                        INNER JOIN cat_EstadoModificacion e using(emo_id)
                        LEFT JOIN cat_ExcepcionClaveFinanciamiento exc USING(ecf_id)
                        WHERE m.mod_gpo_id = ' . (isset($prm->mod_gpo_id) ? $prm->mod_gpo_id : $this->adm_grupo) . '
                        AND m.mod_ur_id = ' . (isset($prm->mod_ur_id) ? $prm->mod_ur_id : $this->adm_ur) . '
                        AND e.emo_id IN (6,11,17) AND mod_Subsecretaria=2
                        ORDER BY m.mod_id
                    )folio;
                ';
                return $sql;
                break;
        } // Fin $quien
    } // Fin getConsultaTabla

    public function getConsultaTablasURs($quien, $nivelRevision)
    {
        $nivelRevision = $nivelRevision != '' ? $nivelRevision : '';

        switch ($quien) {
            case 'drh':
                switch ($nivelRevision) {
                    case 'alta':
                        $sql = "
                            SELECT
                                gpo_id AS mod_gpo_id,ur_id AS mod_ur_id,ur_clave,ur_nombre,'C' omo_id
                            FROM cat_UnidadResponsable
                            WHERE crh_id = 1 AND gpo_id != 0
                            GROUP BY gpo_id, ur_id
                            ORDER BY gpo_id, ur_id;";

                        return $sql;
                        break;
                    case 'validacionUR':
                        $sql = "
                            SELECT
                                r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id,
                                COUNT( r.mod_id ) num_folios, ur.ur_clave,
                                ur.ur_nombre,min(r.mod_Subsecretaria)mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,
                                    mod_Subsecretaria,tcp_id
                                FROM scp_MaestroModificacion
                                LEFT JOIN (
                                    SELECT
                                        d.omo_id,
                                        d.mod_gpo_id,
                                        d.mod_ur_id ,
                                        d.mod_id,d.tcp_id
                                    FROM scp_DetalleModificacion d
                                    INNER JOIN enc_Programatico p USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE d.tcp_id='D'
                                    GROUP BY d.omo_id , d.mod_gpo_id , d.mod_ur_id , d.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id IN (2,5) AND omo_id='C'
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            WHERE crh_id = 1 and tfm_id='A'
                            # GROUP BY gpo_id, ur_id
                            GROUP BY r.gpo_id, ur.ur_id
                            ORDER BY r.gpo_id, ur.ur_id;";

                        return $sql;
                        break;
                    case 'autorizacionUR':
                        $sql = "
                            SELECT
                                r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id, COUNT( r.mod_id ) num_folios,
                                ur.ur_clave, ur.ur_nombre,min(r.mod_Subsecretaria) mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,
                                    mod_Subsecretaria,tcp_id
                                FROM  scp_MaestroModificacion
                                LEFT JOIN (
                                    SELECT
                                        d.omo_id,
                                        d.mod_gpo_id,
                                        d.mod_ur_id ,
                                        d.mod_id,d.tcp_id
                                    FROM scp_DetalleModificacion d
                                    INNER JOIN enc_Programatico p USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE d.tcp_id='D'
                                    GROUP BY d.omo_id , d.mod_gpo_id , d.mod_ur_id , d.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id IN (4,9) AND omo_id='C'
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            WHERE crh_id = 1 and tfm_id='A'
                            # GROUP BY r.gpo_id, r.ur_id
                            GROUP BY r.gpo_id, ur.ur_id
                            ORDER BY r.gpo_id, ur.ur_id;";

                        return $sql;
                        break;
                } // fin switch nivelRevision
                break;
            case 'presupuesto':
                switch ($nivelRevision) {
                    case 'alta':
                    case 'revisionSEFIN':
                        $sql = '
                            SELECT
                                r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id, COUNT( r.mod_id )
                                num_folios, ur.ur_clave, ur.ur_nombre,min(mod_Subsecretaria) mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,mod_Subsecretaria,tcp_id
                                FROM  scp_MaestroModificacion
                                LEFT JOIN (
                                    SELECT  m.omo_id,m.mod_gpo_id,m.mod_ur_id ,m.mod_id,min(tcp_id) tcp_id
                                    FROM scp_DetalleModificacion m
                                    INNER JOIN enc_Programatico p USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE c.toa_id IN ( 1,2 )
                                    GROUP BY m.omo_id , m.mod_gpo_id , m.mod_ur_id , m.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id in (6,8,11,17)
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            GROUP BY r.gpo_id, ur.ur_id
                            HAVING num_folios>0
                            ORDER BY r.gpo_id, ur.ur_id;';
                        return $sql;
                        break;
                    case 'validacionSEFIN':
                        $sql = '
                            SELECT
                                r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id, COUNT( r.mod_id ) num_folios, ur.ur_clave,ur.ur_nombre,min(r.mod_Subsecretaria)mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,
                                    mod_Subsecretaria,tcp_id
                                FROM  scp_MaestroModificacion
                                LEFT JOIN (
                                    SELECT
                                        d.omo_id,
                                        d.mod_gpo_id,
                                        d.mod_ur_id ,
                                        d.mod_id,min(d.tcp_id) tcp_id
                                    FROM scp_DetalleModificacion d
                                    INNER JOIN enc_Programatico p USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE c.toa_id IN ( 1,2 )
                                    GROUP BY d.omo_id , d.mod_gpo_id , d.mod_ur_id , d.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id in (10,13)
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            GROUP BY r.gpo_id, ur.ur_id
                            HAVING num_folios>0
                            ORDER BY r.gpo_id, ur.ur_id;';
                        return $sql;
                        break;
                    case 'autorizacionSEFIN':
                        $sql = '
                            SELECT
                            r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id, COUNT( r.mod_id )num_folios, ur.ur_clave, ur.ur_nombre,min(r.mod_Subsecretaria)mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,
                                    mod_Subsecretaria,tcp_id
                                FROM  scp_MaestroModificacion
                                LEFT JOIN (
                                    select  dm.omo_id,
                                    dm.mod_gpo_id,
                                    dm.mod_ur_id ,
                                    dm.mod_id,min(dm.tcp_id) tcp_id
                                    from
                                    scp_DetalleModificacion dm
                                    INNER JOIN enc_Programatico e USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE c.toa_id IN ( 1,2 )
                                    GROUP BY dm.omo_id , dm.mod_gpo_id , dm.mod_ur_id , dm.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id in (12)
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            GROUP BY r.gpo_id, ur.ur_id
                            HAVING num_folios>0
                            ORDER BY r.gpo_id, ur.ur_id;';
                        return $sql;
                        break;
                } // fin switch nivelRevision
                break;
            case 'planeacion':
                switch ($nivelRevision) {
                    case 'alta':
                    case 'revisionSEFIN':
                        $sql = '
                            SELECT r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id, COUNT( r.mod_id ) num_folios, ur.ur_clave,ur.ur_nombre,r.mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,
                                    mod_Subsecretaria,tcp_id
                                FROM  scp_MaestroModificacion
                                LEFT JOIN (
                                    SELECT
                                        dm.omo_id,
                                        dm.mod_gpo_id,
                                        dm.mod_ur_id ,
                                        dm.mod_id,min(dm.tcp_id)tcp_id
                                    FROM scp_DetalleModificacion dm
                                    INNER JOIN enc_Programatico p USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE  c.toa_id IN ( 2 )
                                    GROUP BY dm.omo_id , dm.mod_gpo_id , dm.mod_ur_id , dm.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id in (6,11,17) and mod_Subsecretaria = 2
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            GROUP BY r.gpo_id, ur.ur_id
                            HAVING num_folios > 0
                            ORDER BY r.gpo_id, ur.ur_id;';
                        return $sql;
                        break;
                    case 'validacionSEFIN':
                        $sql = '
                            SELECT
                                r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id, COUNT( r.mod_id ) num_folios,ur.ur_clave,ur.ur_nombre,r.mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,
                                    mod_Subsecretaria,tcp_id
                                FROM  scp_MaestroModificacion
                                LEFT JOIN (
                                    SELECT
                                        dm.omo_id,
                                        dm.mod_gpo_id,
                                        dm.mod_ur_id ,
                                        dm.mod_id,min(dm.tcp_id)tcp_id
                                    FROM scp_DetalleModificacion dm
                                    INNER JOIN enc_Programatico USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE c.toa_id IN ( 2 )
                                    GROUP BY dm.omo_id , dm.mod_gpo_id , dm.mod_ur_id , dm.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id in (10,13) and mod_Subsecretaria=2
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            GROUP BY r.gpo_id, ur.ur_id
                            HAVING num_folios>0
                            ORDER BY r.gpo_id, ur.ur_id;';
                        return $sql;
                        break;
                    case 'autorizacionSEFIN':
                        $sql = '
                            SELECT
                                r.gpo_id as mod_gpo_id, ur.ur_id as mod_ur_id, COUNT(r.mod_id) num_folios,
                                    ur.ur_clave,ur.ur_nombre,r.mod_Subsecretaria
                            FROM (
                                SELECT
                                    omo_id, mod_gpo_id AS gpo_id, mod_ur_id AS ur_id, mod_id, enc_oop,
                                    mod_Subsecretaria,tcp_id
                                FROM  scp_MaestroModificacion
                                LEFT JOIN (
                                    SELECT
                                        dm.omo_id,
                                        dm.mod_gpo_id,
                                        dm.mod_ur_id ,
                                        dm.mod_id,min(dm.tcp_id)tcp_id
                                    FROM
                                    scp_DetalleModificacion dm
                                    INNER JOIN enc_Programatico USING (epg_id , gpo_id , ur_id)
                                    INNER JOIN cat_ObraActividad c USING (gpo_id , ur_id , prg_id , spr_id , py_id , oa_id)
                                    WHERE c.toa_id IN ( 2 )
                                    GROUP BY dm.omo_id , dm.mod_gpo_id , dm.mod_ur_id , dm.mod_id
                                ) d USING (omo_id , mod_gpo_id , mod_ur_id , mod_id)
                                WHERE emo_id in (12) and mod_Subsecretaria=2
                                GROUP BY omo_id, mod_gpo_id, mod_ur_id, mod_id
                            )r
                            INNER JOIN enc_OrigenOperacion op USING(enc_oop,omo_id)
                            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                            GROUP BY r.gpo_id, ur.ur_id
                            HAVING num_folios>0
                            ORDER BY r.gpo_id, ur.ur_id;';
                        return $sql;
                        break;
                } // FIN switch nivelRevision
                break;
        } //FIN switch
    } // Fin getConsultaTablasURs

    public function getConsultaTablaMovimientosFolio($quien, $mod)
    {
        $sql = '';

        switch ($quien) {
            case 'ur':
                $sql = '
                    SELECT
                        m.omo_id, m.mod_gpo_id, m.mod_ur_id, m.mod_id, p.gpo_id, p.ur_id, p.ue_id,
                        p.epg_id, p.pg_id,p.pe_id,p.ecf_id, p.tcp_id,pe.pe_nombre, t.mov_nombre,
                        d.dmo_importe,m.emo_id, p.pre_claveCompleta
                    FROM scp_DetalleModificacion d
                    INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN scp_MaestroModificacion m using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                    INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                    INNER JOIN cat_TipoMovimiento t using(mov_id)
                    ' . ($mod->getOmo_id() != '' ?
                    " WHERE m.omo_id='" . $mod->getOmo_id() . "' " : '') . '
                    ' . ($mod->getMod_gpo_id() != '' ?
                    ' AND m.mod_gpo_id=' . $mod->getMod_gpo_id() . ' ' : '') . '
                    ' . ($mod->getMod_ur_id() != '' ?
                    ' AND m.mod_ur_id=' . $mod->getMod_ur_id() . ' ' : '') . '
                    ' . ($mod->getMod_id() != '' ?
                    ' AND m.mod_id=' . $mod->getMod_id() . '' : '') . "
                    AND p.tcp_id != 'P';";
                return $sql;
                break;
            case 'drh':
                $sql = '
                    SELECT
                        m.omo_id, m.mod_gpo_id, m.mod_ur_id, m.mod_id, p.gpo_id, p.ur_id, p.ue_id,
                        p.epg_id,p.pg_id,p.pe_id,p.ecf_id, p.tcp_id,pe.pe_nombre, t.mov_nombre,
                        d.dmo_importe,m.emo_id, p.pre_claveCompleta
                    FROM scp_DetalleModificacion d
                    INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN scp_MaestroModificacion m using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                    INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                    INNER JOIN cat_TipoMovimiento t using(mov_id)
                    ' . ($mod->getOmo_id() != '' ?
                    " WHERE m.omo_id='" . $mod->getOmo_id() . "' " : '') . '
                    ' . ($mod->getMod_gpo_id() != '' ?
                    ' AND m.mod_gpo_id=' . $mod->getMod_gpo_id() . ' ' : '') . '
                    ' . ($mod->getMod_ur_id() != '' ?
                    ' AND m.mod_ur_id=' . $mod->getMod_ur_id() . ' ' : '') . '
                    ' . ($mod->getMod_id() != '' ?
                    ' AND m.mod_id=' . $mod->getMod_id() . '' : '') . "
                    AND p.tcp_id = 'D'
                    AND p.tcp_id != 'P';";
                return $sql;
                break;
            case 'sefin':
                $sql = '
                    SELECT
                        d.omo_id, d.mod_gpo_id, d.mod_ur_id, d.mod_id, p.gpo_id, p.ur_id, p.ue_id,
                        p.epg_id,p.pg_id,p.pe_id,p.ecf_id, p.tcp_id,pe.pe_nombre, t.mov_nombre,
                        d.dmo_importe,emo_id, p.pre_claveCompleta
                    FROM scp_DetalleModificacion d
                    INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN (
                        SELECT omo_id,mod_gpo_id,mod_ur_id,mod_id,emo_id
                        FROM scp_MaestroModificacion
                        ' . ($mod->getOmo_id() != '' ?
                        " WHERE omo_id='" . $mod->getOmo_id() . "' " : '') . '
                        ' . ($mod->getMod_gpo_id() != '' ?
                        ' AND mod_gpo_id=' . $mod->getMod_gpo_id() . ' ' : '') . '
                        ' . ($mod->getMod_ur_id() != '' ?
                        ' AND mod_ur_id=' . $mod->getMod_ur_id() . ' ' : '') . '
                        ' . ($mod->getMod_id() != '' ?
                        ' AND mod_id=' . $mod->getMod_id() . '' : '') . '
                    )FOLIO USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                    INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                    INNER JOIN cat_TipoMovimiento t using(mov_id)
                    ' . ($mod->getOmo_id() != '' ?
                    " WHERE d.omo_id='" . $mod->getOmo_id() . "' " : '') . '
                    ' . ($mod->getMod_gpo_id() != '' ?
                    ' AND d.mod_gpo_id=' . $mod->getMod_gpo_id() . ' ' : '') . '
                    ' . ($mod->getMod_ur_id() != '' ?
                    ' AND d.mod_ur_id=' . $mod->getMod_ur_id() . ' ' : '') . '
                    ' . ($mod->getMod_id() != '' ?
                    ' AND d.mod_id=' . $mod->getMod_id() . '' : '') . ' ;';
                return $sql;
                break;
        } //fin switch
    } // Fin getConsultaTablaMovimientosFolio

    public function getValidaModificaciones($mov = null, $mod = null, $cusr = null, $cvp = null)
    {
        $retorno = new stdClass();
        $retorno->valido = true;

        //Verifica que los parametros no esten vacios
        if ($mov == null || $mod == null || $cusr == null || $cvp == null) {
            $retorno->valido = false;
            $retorno->error = 0;
            $retorno->mensaje = 'Faltan parametros en la consulta de validación';
            return $retorno;
        } //fin if

        $mes = date('m') * 1;
        $trim = null;
        $ti = null;

        $meses = [
            'ene',
            'feb',
            'mar',
            'abr',
            'may',
            'jun',
            'jul',
            'ago',
            'sep',
            'oct',
            'nov',
            'dic',
        ];
        $mesesd = [
            'ENERO',
            'FEBRERO',
            'MARZO',
            'ABRIL',
            'MAYO',
            'JUNIO',
            'JULIO',
            'AGOSTO',
            'SEPTIMEBRE',
            'OCTUBRE',
            'NOVIEMBRE',
            'DICIEMBRE',
        ];

        $trimestre = [
            'ENERO-FEBRERO-MARZO',
            'ABRIL-MAYO-JUNIO',
            'JULIO-AGOSTO-SEPTIEMBRE',
            'OCTUBRE-NOVIEMBRE-DICIEMBRE',
        ];

        //Obtenemos el mes y el trimestre actual dependiendo del $mes
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
        } // fin switch

        //Obtenemos el presupuesto de la clave presupuestal
        $d = $this->getPresupuestoModificaciones($cvp, 'valida', $mov, '');
        //Sacamos los resultados del presupuesto y los guardamos para sus validaciones
        $pres = new stdClass();
        $pres->com = Math::redondear($d['pre_comprometido'], 2);
        $pres->vig = Math::redondear($d['pre_vigente'], 2);
        $pres->dev = Math::redondear($d['pre_devengado'], 2);

        //Presupuesto devengado
        $pres->devdet = new stdClass();
        //Presupuesto devengado anual
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

        //Presupuesto vigente
        $pres->vigdet = new stdClass();
        //Presupuesto vigente anual
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

        //Calendario trimestre 1
        $trimUsuario1 = Math::redondear(
            ($cusr->ene + $cusr->feb + $cusr->mar),
            2
        );
        $trimVigente1 = Math::redondear(($pres->vigdet->ene + $pres->vigdet->feb
            + $pres->vigdet->mar), 2);
        $trimDevengado1 = Math::redondear(($pres->devdet->ene + $pres->devdet->feb
            + $pres->devdet->mar), 2);

        //Calendario trimestre 2
        $trimUsuario2 = Math::redondear(
            ($cusr->abr + $cusr->may + $cusr->jun),
            2
        );
        $trimVigente2 = Math::redondear(($pres->vigdet->abr + $pres->vigdet->may
            + $pres->vigdet->jun), 2);
        $trimDevengado2 = Math::redondear(($pres->devdet->abr + $pres->devdet->may
            + $pres->devdet->jun), 2);

        //Calendario trimestre 3
        $trimUsuario3 = Math::redondear($cusr->jul + $cusr->ago + $cusr->sep, 2);
        $trimVigente3 = Math::redondear(($pres->vigdet->jul + $pres->vigdet->ago
            + $pres->vigdet->sep), 2);
        $trimDevengado3 = Math::redondear(($pres->devdet->jul + $pres->devdet->ago
            + $pres->devdet->sep), 2);

        //Calendario trimestre 3
        $trimUsuario4 = Math::redondear(
            ($cusr->oct + $cusr->nov + $cusr->dic),
            2
        );
        $trimVigente4 = Math::redondear(($pres->vigdet->oct + $pres->vigdet->nov
            + $pres->vigdet->dic), 2);
        $trimDevengado4 = Math::redondear(($pres->devdet->oct + $pres->devdet->nov
            + $pres->devdet->dic), 2);

        //Disponble trimestral
        $disponibleT1 = Math::redondear(($trimVigente1 - $trimDevengado1), 2);
        $disponibleT2 = Math::redondear(($trimVigente2 - $trimDevengado2), 2);
        $disponibleT3 = Math::redondear(($trimVigente3 - $trimDevengado3), 2);
        $disponibleT4 = Math::redondear(($trimVigente4 - $trimDevengado4), 2);

        //bccomp, si son iguales el valor es 0, si el valor 1 es mayor al valor 2 es = 1, de lo contrario es -1
        //Verifica el presupuesto devengado
        if (bccomp($pres->dev, $pres->devdetanua, 2) != 0) {
            $retorno->valido = false;
            $retorno->error = 1;
            $retorno->mensaje = 'Error en el devengo de la clave presupuestal';
            return $retorno;
        }

        //Verifica el presupuesto vigente
        if (bccomp($pres->vig, $pres->vigdetanua, 2) !== 0) {
            $retorno->valido = false;
            $retorno->error = 2;
            $retorno->mensaje = 'Error en el vigente de la clave presupuestal';
            return $retorno;
        }

        // Importe total de la modificación realizada por el usuario
        $usrImporte = Math::redondear($cusr->importe, 2);
        // Sumanos el calendario del usuario
        $usranual = Math::redondear(
            $cusr->ene + $cusr->feb + $cusr->mar + $cusr->abr + $cusr->may + $cusr->jun
            + $cusr->jul +
            $cusr->ago + $cusr->sep + $cusr->oct + $cusr->nov + $cusr->dic,
            2
        );

        if (stristr($usranual, 'e')) {
            $usranual = Math::exp_to_dec($usranual);
        } // Fin if

        // Verifica que ningun mes se encuentre en negativo
        for ($i = 0; $i <= 11; $i++) {
            if ($cusr == '') {
                if (bccomp($cusr->$meses[$i], 0, 2) < 0) {
                    $retorno->valido = false;
                    $retorno->error = 46;
                    $retorno->mensaje = ' El importe del mes de ' . $mesesd[$i] .
                        ' no puede ser negativo, en la clave ' . $d['pre_claveCompleta'] . '';
                    return $retorno;
                } // Fin if
            }
        } // Fin for

        // Verifica que el importe total coincida con la suma del calendario
        if (bccomp($usranual, $usrImporte, 2) != 0) {
            $retorno->valido = false;
            $retorno->error = 3;
            $retorno->mensaje = 'El importe no coincide con la suma del calendario en la clave: ' .
                $d['pre_claveCompleta'] . ' ';
            return $retorno;
        } // fin if

        // Valida segun el tipo de movimiento: R-reducción, C-calendarización, R-ampliación
        switch ($mov) {
            case 'R':
                //Presupuesto disponible comprometido
                $disponibleComprometido = Math::redondear(
                    $pres->vig - $pres->com,
                    2
                );
                //Verifica que la reducción no exceda el comprometido
                if (bccomp($disponibleComprometido, $usranual, 2) < 0) {
                    $retorno->valido = false;
                    $retorno->error = 4;
                    $retorno->mensaje = 'La reducción excede el comprometido en la clave presupuestal ' . $d['pre_claveCompleta'] . ' ';
                    return $retorno;
                } //fin if
                //Presupuesto disponible
                $disponible = Math::redondear($pres->vig - $pres->dev, 2);
                //Verifica que la reducción no supere el disponible anual
                if (bccomp($disponible, $usranual, 2) < 0) {
                    $retorno->valido = false;
                    $retorno->error = 5;
                    $retorno->mensaje = 'La reducción supera el disponible por reducir de manera anual';
                    return $retorno;
                } //fin if
                //Valida que no se redusca mas de lo que se tiene disponible en el trimestre y despues por mes
                if (bccomp($disponibleT1, $trimUsuario1, 2) < 0 ||
                    bccomp($disponibleT2, $trimUsuario2, 2) < 0 ||
                    bccomp($disponibleT3, $trimUsuario3, 2) < 0 ||
                    bccomp($disponibleT4, $trimUsuario4, 2) < 0) {
                    $retorno->valido = false;
                    $retorno->error = 7;
                    $retorno->mensaje = 'No hay presupuesto disponible para reducir, en la clave presupuestal ' .
                        $d['pre_claveCompleta'] . '';
                    return $retorno;
                } else {
                    //Valida que no se redusca mas de lo disponible por mes
                    for ($i = 0; $i <= 11; $i++) {
                        if ($cusr == '') {
                            if ($cusr->$meses[$i] != 0 && bccomp(
                                Math::redondear(
                                    $cusr->$meses[$i],
                                    2
                                ),
                                Math::redondear(
                                    $pres->vigdet->$meses[$i] - $pres->devdet->$meses[$i],
                                    2
                                ),
                                2
                            ) == 1) {
                                $retorno->valido = false;
                                $retorno->error = 7;
                                $retorno->mensaje = 'No se puede reducir del mes ' . ($mesesd[$i]) .
                                    ' no hay presupuesto para reducir, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                        }
                    }
                } //fin if

                //Verifica si el tipo de modalidad de la adecuación es Interna
                if ($mod == 'I') {
                    //Valida que la partida no sea intransferible
                    if ($d['pe_reductible'] == 2 || $d['pe_reductible'] == 3) {
                        $retorno->valido = false;
                        $retorno->error = 36;
                        $retorno->mensaje = 'La partida es intransferible';
                        return $retorno;
                    }
                    //Valida queno haya reducciones en meses posteriores al actual
                    for ($i = $mes; $i <= 11; $i++) {
                        if ($cusr == '') {
                            if ($cusr->$meses[$i] != 0) {
                                $retorno->valido = false;
                                $retorno->error = 6;
                                $retorno->mensaje = ' No se puede reducir del mes ' .
                                    $mesesd[$i] . ' por ser posterior al actual, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } //fin if
                        }
                    } //fin for
                } //fin if $mod =="I"

                break;
            case 'C':
                //Valida que los importes coincidan con el vigente
                if (bccomp($usranual, $pres->vig, 2) != 0) {
                    $retorno->valido = false;
                    $retorno->error = 9;
                    $retorno->mensaje = 'El importe del calendario es incorrecto, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                    return $retorno;
                } //fin if
                //Valida que no deje sin cobertura el devengado del mes
                for ($i = 0; $i <= 11; $i++) {
                    if ($cusr == '') {
                        if (bccomp(
                            Math::redondear($cusr->$meses[$i], 2),
                            Math::redondear($pres->devdet->$meses[$i], 2),
                            2
                        ) < 0) {
                            $retorno->valido = false;
                            $retorno->error = 10;
                            $retorno->mensaje = 'El importe del mes ' . ($mesesd[$i]) . ' es invalido deja sin cobertura el devengado, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                            return $retorno;
                        } //fin if
                    }
                } //fin for
                //Verifica si el tipo de modalidad de la adecuación es Interna
                //Valida que no mueva importes de otros meses
                if ($mod == 'I') {
                    $sobrante = 0;
                    $totUsr = 0;
                    $totVig = 0;

                    //Calendario trimestre 1
                    $c1 = Math::redondear(
                        $cusr->ene + $cusr->feb + $cusr->mar,
                        2
                    );
                    $t1 = Math::redondear($pres->vigdet->ene + $pres->vigdet->feb
                        + $pres->vigdet->mar, 2);
                    $d1 = Math::redondear($pres->devdet->ene + $pres->devdet->feb
                        + $pres->devdet->mar, 2);
                    $s1 = Math::redondear($t1 - $d1, 2);

                    //Calendario trimestre 2
                    $c2 = Math::redondear(
                        $cusr->abr + $cusr->may + $cusr->jun,
                        2
                    );
                    $t2 = Math::redondear($pres->vigdet->abr + $pres->vigdet->may
                        + $pres->vigdet->jun, 2);
                    $d2 = Math::redondear($pres->devdet->abr + $pres->devdet->may
                        + $pres->devdet->jun, 2);
                    $s2 = Math::redondear($t2 - $d2, 2);

                    //Calendario trimestre 3
                    $c3 = Math::redondear(
                        $cusr->jul + $cusr->ago + $cusr->sep,
                        2
                    );
                    $t3 = Math::redondear($pres->vigdet->jul + $pres->vigdet->ago
                        + $pres->vigdet->sep, 2);
                    $d3 = Math::redondear($pres->devdet->jul + $pres->devdet->ago
                        + $pres->devdet->sep, 2);
                    $s3 = Math::redondear($t3 - $d3, 2);

                    //Calendario trimestre 4
                    $c4 = Math::redondear(
                        $cusr->oct + $cusr->nov + $cusr->dic,
                        2
                    );
                    $t4 = Math::redondear($pres->vigdet->oct + $pres->vigdet->nov
                        + $pres->vigdet->dic, 2);
                    $d4 = Math::redondear($pres->devdet->oct + $pres->devdet->nov
                        + $pres->devdet->dic, 2);
                    $s4 = Math::redondear($t4 - $d4, 2);

                    //Valida que anticipe recursos en el trimestre 1
                    if ($c1 > $t1) {
                        $retorno->valido = false;
                        $retorno->error = 11;
                        $retorno->mensaje = 'No puede anticipar recursos en el trimestre 1 , en la clave presupuestal ' .
                            $d['pre_claveCompleta'] . '';
                        return $retorno;
                    }

                    //Valida la transeferencia de recursos en los trimestres 2,3 y 4
                    switch ($ti) {
                        case 2:
                            //Valida que no pase recursos del trimestre 4 a trimestres anteriores
                            if ($c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 12;
                                $retorno->mensaje = 'No puede pasar recursos del trimestre 4 a trimestres anteriores, en' .
                                    ' la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } //fin if c4
                            //Valida que no pase recurso del trimestre 3 al trimestre 1
                            if ($c2 == $t2 && $c3 < $t3 && $c4 == $t4) {
                                $retorno->valido = false;
                                $retorno->error = 13;
                                $retorno->mensaje = 'No puede pasar recursos del trimestre 3 a trimestres anteriores, ' .
                                    'en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } // fin if
                            //Valida que no se pase el recurso del trimestre 3 al trimestre 2
                            if ($c2 > $t2 && $c3 < $t3 && $c4 == $t4) {
                                $retorno->valido = false;
                                $retorno->error = 14;
                                $retorno->mensaje = 'No puede pasar recursos del trimestre 3 a trimestres anteriores, ' .
                                    'en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } //fin if c2
                            break;
                        case 3:
                            //Valida que no se pase el recurso del trimestre 3 al trimestre 1 o 2
                            if ($c3 < $t3 && $c4 == $t4) {
                                $retorno->valido = false;
                                $retorno->error = 15;
                                $retorno->mensaje = 'No pueder pasar el recurso del trimestre 3 a trimestres anteriores' .
                                    ', en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } //fin if
                            //Valida que no se pase el recurso del trimestre 4 a trimestres anteriores al trimestre 3
                            if ($c3 == $t3 && $c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 16;
                                $retorno->mensaje = 'No pueder pasar el recurso del trimestre 4 a trimestres anteriores' .
                                    ' al 3, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } //fin if
                            //Valida que no se pase el recurso del trimestre 4 al trimestre 3
                            if ($c3 > $t3 && $c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 17;
                                $retorno->mensaje = 'No pueder pasar el recurso del trimestre 4 al trimestre 3,' .
                                    ' en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } //fin if
                            break;
                        case 4:
                            //Valida el paso de recursos en trimestres anteriores al 4
                            if ($c4 == $t4) {
                                //Valida el paso de recursos en el trimestre 1
                                if ($c1 > $t1) {
                                    $retorno->valido = false;
                                    $retorno->error = 18;
                                    $retorno->mensaje = 'No puede pasar recursos al trimestre 1, ' .
                                        'en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                    return $retorno;
                                } //fin if
                                //Valida el paso de recursos del trimestre 3 al trimestre 2
                                if ($c2 > $t2 && $c3 < $t3) {
                                    $retorno->valido = false;
                                    $retorno->error = 19;
                                    $retorno->mensaje = 'No puede pasar recurso del trimestre 3 al 2, ' .
                                        'en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                    return $retorno;
                                } //fi if
                            }
                            //Valida que no pase recurso del trimestre 4 a trimestres anteriores
                            if ($c4 < $t4) {
                                $retorno->valido = false;
                                $retorno->error = 20;
                                $retorno->mensaje = 'No pueder pasar recursos del trimestre 4 a trimestres anteriores,' .
                                    ' en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            } //fin if
                            break;
                    } //fin switch
                } //fin $mod =="I"
                break;
            case 'A':
                //Verifica si el tipo de modalidad de la adecuación es Interna
                if ($mod == 'I') {
                    //Valida si la partida es reductible
                    if ($d['pe_reductible'] != 3 && $d['pe_reductible'] != 1) {
                        $retorno->valido = false;
                        $retorno->error = 35;
                        $retorno->mensaje = 'No es posible utilizar esta partida para realizar la ampliación en esta modalidad';
                        return $retorno;
                    } //fin if pe_reductible

                    if ($cusr == '') {
                        for ($i = $mes - 2; $i >= 0; $i--) {
                            if ($cusr->$meses[$i] != 0) {
                                $retorno->valido = false;
                                $retorno->error = 21;
                                $retorno->mensaje = 'No se puede Ampliar en el mes de ' . $mesesd[$i] .
                                    ' por ser un mes ya pasado, en la clave presupuestal ' . $d['pre_claveCompleta'] . '';
                                return $retorno;
                            }
                        } //fin for
                    }
                } //fin
                break;
        } // fin switch
        //Regresa validación
        return $retorno;
    } // Fin getValidaModificaciones

    public function getPresupuestoModificaciones($cvp, $tipo, $modificacion)
    {
        $sql = '
            SELECT
                pre_comprometido,pre_vigente,pre_devengado,dev_ene,dev_feb,dev_mar,dev_abr,
                dev_may,dev_jun,dev_jul,dev_ago,dev_sep,dev_oct,dev_nov,dev_dic,vig_ene,vig_feb,
                vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
                (SUM(vig_ene+vig_feb+vig_mar) - SUM(dev_ene+dev_feb+dev_mar)) as dis_ene,0 dis_feb, 0 dis_mar,
                (SUM(vig_abr+vig_may+vig_jun) - SUM(dev_abr+dev_may+dev_jun)) as dis_abr,0 dis_may, 0 dis_jun,
                (SUM(vig_jul+vig_ago+vig_sep) - SUM(dev_jul+dev_ago+dev_sep)) as dis_jul,0 dis_ago, 0 dis_sep,
                (SUM(vig_oct+vig_nov+vig_dic) - SUM(dev_oct+dev_nov+dev_dic)) as dis_oct,0 dis_nov, 0 dis_dic,
                pre_claveCompleta,pe_reductible
            FROM presupuesto p
            INNER JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN presupuestoDevengado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
            WHERE gpo_id =' . $cvp->gpo_id . ' AND ur_id=' . $cvp->ur_id . ' AND p.ue_id=' . $cvp->ue_id .
            ' AND epg_id=' . $cvp->epg_id;

        $sql .= '
            AND pg_id =  ' . $cvp->pg_id . '
            AND pe_id = ' . $cvp->pe_id .
            ' AND ecf_id = ' . $cvp->ecf_id . ' ' . ((isset($cvp->tcp_id) && $cvp->tcp_id != '') ? '
            AND tcp_id="' . $cvp->tcp_id . '"' : '') . '';
        $sql .= 'group by gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id';

        $datos = $this->getDatos($sql, false);

        if ($tipo == 'valida') {
            return $datos['datos'][0];
        } // fin if "valida"

        $datos = $datos['datos'][0];
        $meses = [
            'ene',
            'feb',
            'mar',
            'abr',
            'may',
            'jun',
            'jul',
            'ago',
            'sep',
            'oct',
            'nov',
            'dic',
        ];
        $mesesd = [
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre',
        ];
        $pre = [];

        //Obtiene información
        for ($i = 0; $i <= 11; $i++) {
            $n = new stdClass();
            $n->mes = $mesesd[$i];
            $n->devengado = $datos['dev_' . $meses[$i]];
            $n->vigente = $datos['vig_' . $meses[$i]];
            $n->disp = (Math::redondear(
                $datos['vig_' . $meses[$i]] - $datos['dev_' . $meses[$i]],
                2
            )) < 0 ? 0 :
            (Math::redondear(
                $datos['vig_' . $meses[$i]] - $datos['dev_' . $meses[$i]],
                2
            ));
            $n->disponible2 = $datos['dis_' . $meses[$i]];
            $n->modificacion = $modificacion[$i];
            $n->nmes = $i;
            array_push($pre, $n);
        } //fin for
        return $pre; //Regresa datos
    } // Fin getPresupuestoModificaciones

    public function getValidaFolio($mod, $folioMasivo = false, $temCarga = '')
    {
        $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);
        $folioMasivo = ($folioMasivo) ? true : false;
        $tem_carga = ($temCarga != '') ? $temCarga : '';
        $datos = [0, ''];
        $error = [];
        $noErrores = 0;
        //VERIFICAMOS SI EL FOLIO TIENE REGISTROS
        $tieneMovimientos = $this->getEliminaFolio($mod);
        $tieneMovimientos = $tieneMovimientos['registros'] ? true : false;

        if ($mod->getOmo_id() != '' && $mod->getMod_gpo_id() != '' && $mod->getMod_ur_id() != '' && $mod->getMod_id() != '') {
            if (!$tieneMovimientos && !$folioMasivo) {
                $noErrores++;
                $error[$noErrores] = ['numero' => 22, 'mensaje' => 'El folio no tiene movimientos '];
            } //fin if

            //VALIDAR QUE LAS CLAVES PRESUPUESTALES QUE ESTÁN EN UNA ADECUACION EN PROCESO SE ENCUENTREN MARCADAS
            if (!$folioMasivo) {
                $sql = "
                    SELECT
                        p.gpo_id,p.ur_id,p.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id,p.pg_id,p.pe_id,
                        p.cff_id,p.fte_id,p.sft_id,p.ori_id,p.sor_id,p.cfi_id,p.cfi_anio,v.pre_mov,
                        p.pre_claveCompleta
                    FROM presupuesto p
                    INNER JOIN presupuestoVigente v USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN scp_DetalleModificacion d USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    WHERE d.omo_id = '{$mod->getOmo_id()}'
                    AND d.mod_gpo_id = {$mod->getMod_gpo_id()}
                    AND d.mod_ur_id = {$mod->getMod_ur_id()}
                    AND d.mod_id = {$mod->getMod_id()}
                    AND d.tcp_id != 'P'
                    AND v.pre_mov IS NULL";

                $datos = $this->getDatos($sql, false);
                $numRegistros = $datos['registros'];
                $datos = $datos['datos'];

                if ($numRegistros > 0) {
                    $y = '';
                    $i = 0;
                    while ($i < count($datos)) {
                        $y = $datos[$i]['gpo_id'] . $datos[$i]['ur_id'] . $datos[$i]['ue_id'] . $datos[$i]['prg_id'] . $datos[$i]['spr_id'] . $datos[$i]['py_id']
                            . $datos[$i]['oa_id'] . $datos[$i]['pg_id'] . $datos[$i]['pe_id'] . $datos[$i]['cff_id'] . $datos[$i]['fte_id'] . $datos[$i]['sft_id']
                            . $datos[$i]['ori_id'] . $datos[$i]['sor_id'] . $datos[$i]['cfi_id'] . $datos[$i]['cfi_anio'];
                        $noErrores++;
                        $error[$noErrores] = ['numero' => 23, 'mensaje' => " La clave : $y no esta marcada para una adecuación presupuestal, elimine el movimiento y capturelo nuevamente. "];
                        $i++;
                    } //fin while
                } //fin numRegistros
            } //fin if folioMasivo

            //Validar que los movimientos correspondan al tipo de subsecretaria que eligio
            $sql = '
                SELECT
                    m.omo_id,m.mod_gpo_id,m.mod_ur_id, m.mod_id,m.mod_Subsecretaria,c.toa_id,
                    p.gpo_id,p.ur_id,d.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id,p.pg_id,p.pe_id,
                    p.cff_id,p.fte_id,p.sft_id,p.ori_id,p.sor_id,p.cfi_id,p.cfi_anio
                FROM
                    ' . (($folioMasivo) ? BD_TEMPORAL . '.tem_DetalleModificacion'
                : 'scp_DetalleModificacion') . ' d
                INNER JOIN scp_MaestroModificacion m USING (omo_id , mod_gpo_id, mod_ur_id, mod_id, ecf_id)
                INNER JOIN presupuesto p ' . (($folioMasivo) ? 'using(gpo_id,ur_id,ue_id,epg_id,prg_id,spr_id,py_id,oa_id,
                    pg_id,pe_id,ecf_id,tcp_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)'
                    :
                ' using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)') . "
                INNER JOIN enc_Programatico e USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_ObraActividad c USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                WHERE omo_id = '{$mod->getOmo_id()}'
                AND m.mod_gpo_id = {$mod->getMod_gpo_id()}
                AND m.mod_ur_id = {$mod->getMod_ur_id()}
                AND m.mod_id = {$mod->getMod_id()} " . (($folioMasivo) ?
                " AND d.tem_carga='{$tem_carga}'" : '') . "
                AND d.tcp_id!='P'
                AND c.toa_id != {$mod->getSubsecretaria()}";
            $datos = $this->getDatos($sql, false);
            $numRegistros = $datos['registros'];
            $datos = $datos['datos'];

            if ($numRegistros > 0) {
                $y = '';
                $i = 0;

                while ($i < count($datos)) {
                    $y = $datos[$i]['gpo_id'] . $datos[$i]['ur_id'] . $datos[$i]['ue_id'] . $datos[$i]['prg_id'] . $datos[$i]['spr_id'] . $datos[$i]['py_id']
                        . $datos[$i]['oa_id'] . $datos[$i]['pg_id'] . $datos[$i]['pe_id'] . $datos[$i]['cff_id'] . $datos[$i]['fte_id'] . $datos[$i]['sft_id']
                        . $datos[$i]['ori_id'] . $datos[$i]['sor_id'] . $datos[$i]['cfi_id'] . $datos[$i]['cfi_anio'];
                    $noErrores++;
                    $error[$noErrores] = ['numero' => 23, 'mensaje' => " La clave : $y no corresponde al tipo de Subsecretaria que elegiste en el folio. "];
                    $i++;
                } //FIN while
            } //FIN numRegistros

            //validar que la clave de financiamiento sea la misma del folio.
            $sql2 = '
                SELECT
                    p.gpo_id,p.ur_id,p.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id,
                    p.pg_id,p.pe_id,p.cff_id,p.fte_id,p.sft_id,p.ori_id,p.sor_id,
                    p.cfi_id,p.cfi_anio
                FROM ' . (($folioMasivo) ? BD_TEMPORAL . '.tem_DetalleModificacion ' : 'scp_DetalleModificacion') . " b
                INNER JOIN presupuesto p using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN scp_MaestroModificacion a using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                WHERE a.omo_id = '{$mod->getOmo_id()}'
                AND a.mod_gpo_id = {$mod->getMod_gpo_id()}
                AND a.mod_ur_id = {$mod->getMod_ur_id()}
                AND a.mod_id = {$mod->getMod_id()} " . (($folioMasivo) ? " AND b.tem_carga='{$tem_carga}'" : '') . "
                AND a.ecf_id!=b.ecf_id and b.tcp_id!='P';";
            $datos = $this->getDatos($sql2, false);
            $numRegistros = $datos['registros'];
            $datos = $datos['datos'];

            if ($numRegistros > 0) {
                $cve = '';
                $i = 0;
                while ($i < count($datos)) {
                    $cve = $datos[$i]['gpo_id'] . $datos[$i]['ur_id'] . $datos[$i]['ue_id'] . $datos[$i]['prg_id'] . $datos[$i]['spr_id']
                        . $datos[$i]['py_id'] . $datos[$i]['oa_id'] . $datos[$i]['pg_id'] . $datos[$i]['pe_id'] . $datos[$i]['fte_id']
                        . $datos[$i]['sft_id'] . $datos[$i]['ori_id'] . $datos[$i]['sor_id'] . $datos[$i]['cfi_id']
                        . $datos[$i]['cfi_anio'];
                    $noErrores++;
                    $error[$noErrores] = ['numero' => 24,
                        'mensaje'                  => " La clave de financiamiento no corresponde a la del folio en la clave: $cve. "];
                    $i++;
                } // Fin while
            } // Fin numRegistros

            // Consultar movimientos del folio e información de presupuesto.
            $sql = '
                SELECT
                    a.gpo_id, a.ur_id, a.ue_id, a.epg_id, a.pg_id, a.pe_id,
                    a.ecf_id, a.tcp_id,
                    a.dmo_importe, a.mov_id,
                    a.dmo_ene, a.dmo_feb, a.dmo_mar, a.dmo_abr, a.dmo_may, a.dmo_jun,
                    a.dmo_jul, a.dmo_ago, a.dmo_sep, a.dmo_oct, a.dmo_nov, a.dmo_dic,
                    pv.pre_vigente,pc.pre_comprometido,pv.pre_exenta,p.pre_clave,p.pre_claveCompleta,
                    pv.vig_ene, pv.vig_feb, pv.vig_mar, pv.vig_abr, pv.vig_may, pv.vig_jun,
                    pv.vig_jul, pv.vig_ago, pv.vig_sep, pv.vig_oct, pv.vig_nov, pv.vig_dic,
                    pd.dev_ene, pd.dev_feb, pd.dev_mar, pd.dev_abr, pd.dev_may, pd.dev_jun,
                    pd.dev_jul, pd.dev_ago, pd.dev_sep, pd.dev_oct, pd.dev_nov, pd.dev_dic,
                    idp_id, cap_id, pe.pe_id, pe_reductible,pe.clg_id
                FROM (
                    SELECT
                        gpo_id,ur_id, ue_id, epg_id, pg_id, pe_id,
                        ecf_id, tcp_id, dmo_importe, mov_id,
                        dmo_ene, dmo_feb, dmo_mar, dmo_abr, dmo_may, dmo_jun,
                        dmo_jul, dmo_ago, dmo_sep, dmo_oct, dmo_nov, dmo_dic
                    FROM ' . ($folioMasivo ? BD_TEMPORAL . '.tem_DetalleModificacion' : 'scp_DetalleModificacion') . "
                    WHERE omo_id = '{$mod->getOmo_id()}'
                    AND mod_gpo_id = {$mod->getMod_gpo_id()}
                    AND mod_ur_id = {$mod->getMod_ur_id()}
                    AND mod_id = {$mod->getMod_id()} " . (($folioMasivo) ? " and tem_carga='{$tem_carga}'" : '') . '
                ) as a
                JOIN presupuesto p using(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                JOIN presupuestoComprometido pc USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado pd USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                JOIN cat_PartidaGenerica using(pg_id)
                JOIN cat_Concepto using(cto_id,cap_id);';

            $datos = $this->getDatos($sql, false);
            $numRegistros = $datos['registros'];
            $datos = $datos['datos'];
            $totC = $totA = $totR = 0;

            $mes = [
                'ene' => 'Enero', 'feb' => 'Febrero', 'mar' => 'Marzo',
                'abr' => 'Abril', 'may' => 'Mayo', 'jun' => 'Junio',
                'jul' => 'Julio', 'ago' => 'Agosto', 'sep' => 'Septiembre',
                'oct' => 'Octubre', 'nov' => 'Noviembre', 'dic' => 'Diciembre'];

            if ($numRegistros > 0) {
                $i = 0;

                while ($i < count($datos)) {
                    $clave = new stdClass();
                    $clave->gpo_id = $datos[$i]['gpo_id'];
                    $clave->ur_id = $datos[$i]['ur_id'];
                    $clave->ue_id = $datos[$i]['ue_id'];
                    $clave->epg_id = $datos[$i]['epg_id'];
                    $clave->pg_id = $datos[$i]['pg_id'];
                    $clave->pe_id = $datos[$i]['pe_id'];
                    $clave->ecf_id = $datos[$i]['ecf_id'];
                    $clave->tcp_id = $datos[$i]['tcp_id'];

                    $calendarioUsuario = new stdClass();
                    $calendarioUsuario->ene = $datos[$i]['dmo_ene'];
                    $calendarioUsuario->feb = $datos[$i]['dmo_feb'];
                    $calendarioUsuario->mar = $datos[$i]['dmo_mar'];
                    $calendarioUsuario->abr = $datos[$i]['dmo_abr'];
                    $calendarioUsuario->may = $datos[$i]['dmo_may'];
                    $calendarioUsuario->jun = $datos[$i]['dmo_jun'];
                    $calendarioUsuario->jul = $datos[$i]['dmo_jul'];
                    $calendarioUsuario->ago = $datos[$i]['dmo_ago'];
                    $calendarioUsuario->sep = $datos[$i]['dmo_sep'];
                    $calendarioUsuario->oct = $datos[$i]['dmo_oct'];
                    $calendarioUsuario->nov = $datos[$i]['dmo_nov'];
                    $calendarioUsuario->dic = $datos[$i]['dmo_dic'];
                    $calendarioUsuario->importe = $datos[$i]['dmo_importe'];

                    //VALIDA LA LOGICA DE LOS MOVIMIENTOS
                    $validaMod = $this->getValidaModificaciones(
                        $datos[$i]['mov_id'],
                        $enc_oop->getMmd_id(),
                        $calendarioUsuario,
                        $clave
                    );

                    if ($validaMod->valido != true) {
                        $noErrores++;
                        $error[$noErrores] = ['numero' => $validaMod->error, 'mensaje' => $validaMod->mensaje];
                    }

                    //VALIDA MOVIMIENTOS DE ACUERDO AL TIPO DE MODIFICACION
                    $validaMovimientos = $this->getValidaMovimiento($enc_oop, $datos[$i]);
                    if ($validaMovimientos->valido != true) {
                        $noErrores++;
                        $error[$noErrores] = ['numero' => $validaMovimientos->error, 'mensaje' => $validaMovimientos->mensaje];
                    }

                    /* DE ACUERDO AL TIPO DE MOVIMIENTOS SE OBTIENE EL TOTAL DE AMPLIACIONES/REDUCCIONES Y SE VERIFICA
                    QUE NO SE DEJE SIN COBERTURA UN MES */
                    foreach ($mes as $key => $nombreMes) {
                        switch ($datos[$i]['mov_id']) {
                            case 'R':
                                if ($datos[$i]['dmo_' . $key] != 0 && $datos[$i]['pre_exenta']
                                    == 2 &&
                                    bccomp(
                                        Math::redondear(
                                            $datos[$i]['dmo_' . $key],
                                            2
                                        ),
                                        Math::redondear(
                                            $datos[$i]['vig_' . $key] - $datos[$i]['dev_' . $key],
                                            2
                                        ),
                                        2
                                    ) > 0) {
                                    $noErrores++;
                                    $error[$noErrores] = [
                                        'numero'  => 29,
                                        'mensaje' => 'Se esta dejando sin cobertura el mes de ' . $nombreMes . ' en la clave: ' . $datos[$i]['pre_claveCompleta'],
                                    ];
                                } //fin if
                                break;
                            case 'C':
                                if (bccomp(
                                    Math::redondear(
                                        $datos[$i]['dmo_' . $key],
                                        2
                                    ),
                                    Math::redondear(
                                        $datos[$i]['dev_' . $key],
                                        2
                                    ),
                                    2
                                ) < 0) {
                                    $noErrores++;
                                    $error[$noErrores] = [
                                        'numero'  => 30,
                                        'mensaje' => 'Se esta dejando sin cobertura el mes de ' . $nombreMes . ' en la clave: ' . $datos[$i]['pre_claveCompleta'],
                                    ];
                                } //fin if
                                break;
                        } //fin switch
                    } //fin foreach

                    //VALIDA LA MODALIDAD DE LA MODIFICACION
                    $validaModalidadAdecuacion = $this->getValidaModalidadAdecuacion($mod, $enc_oop, $datos[$i]);
                    if ($validaModalidadAdecuacion->valido != true) {
                        $noErrores++;
                        $error[$noErrores] = ['numero' => $validaModalidadAdecuacion->error,
                            'mensaje'                  => $validaModalidadAdecuacion->mensaje];
                    } //fin if valido

                    /* VERIFICA QUE LOS FOLIOS DE DRH SOLO SEAN DE SERVICIOS PERSONALES Y CON TIPO DE COMPRA D-DIRECTA */
                    $validaFoliosDRH = $this->getValidaFoliosDRH($enc_oop, $datos[$i]);
                    if ($validaFoliosDRH->valido != true) {
                        $noErrores++;
                        $error[$noErrores] = ['numero' => $validaFoliosDRH->error, 'mensaje' => $validaFoliosDRH->mensaje];
                    } //fin if valido

                    // PARA LA SUMATORIA DE AMPLIACION Y REDUCCIONES
                    switch ($datos[$i]['mov_id']) {
                        case 'A':
                            $totA += Math::redondear(
                                $datos[$i]['dmo_importe'],
                                2
                            );
                            break;
                        case 'R':
                            $totR += Math::redondear(
                                $datos[$i]['dmo_importe'],
                                2
                            );
                            break;
                    } // Fin switch
                    $i++;
                } // Fin while

                //VERIFICAR QUE LOS MOVIMIENTOS ESTEN COMPENSADOS ANTES DE ENVIAR A SEFIN Y EN LA VALIDACION SEFIN
                $validaCompensacionFolios = $this->getValidaCompensacionFolio(
                    $mod,
                    $enc_oop,
                    $totA,
                    $totR,
                    $folioMasivo
                );
                if ($validaCompensacionFolios->valido != true) {
                    $noErrores++;
                    $error[$noErrores] = ['numero' => $validaCompensacionFolios->error,
                        'mensaje'                  => $validaCompensacionFolios->mensaje];
                } //fin valida compensacion
            } else {
                $noErrores++;
                $error[$noErrores] = ['numero' => 42, 'mensaje' => 'No existen claves presupuestales'];
            } //fin numRegistros

            /*Para las obras de Inversion, Si el tipo de movimiento no es Calendario o Ampliacio, Valida que los movimientos no dejen sin cobertura a los anticipos por amortizar*/
            if ($mod->getSubsecretaria() == 2 && !in_array($enc_oop->getTmd_id(), ['C','D'])) {
                $tabla = ($folioMasivo) ? BD_TEMPORAL . '.tem_DetalleModificacion ' : 'scp_DetalleModificacion';
                $error = array_merge($error, $this->getValidaAnticipo($mod, $tabla));
                //por el array_merge $error tiene indice 0, si solo tiene un elemento la linea 4341 de ControladorRegistroModificaciones marcara error, por ello obligamos a tener en el indice cero un valor que no ocupamos para despues quitarlo del arreglo
                $error = array_merge([0], $error);
                unset($error[0]);
            }
        } //fin if
        return $error;
    } // Fin getValidaFolio

    public function getValidaMovimiento($enc_oop, $datos)
    {
        $retorno = new stdClass();
        $retorno->valido = true;

        if ($datos == null || $enc_oop == null) {
            $retorno->valido = false;
            $retorno->error = 0;
            $retorno->mensaje = 'Faltan parametros en la consulta de validación';
            return $retorno;
        } // Fin if

        switch ($enc_oop->getTmd_id()) { // Tipo de Folio
            case 'A': //traspaso intrainstitucional
            case 'B': //traspaso interinstitucional
                if ($datos['mov_id'] == 'C') {
                    $retorno->valido = false;
                }
                break;
            case 'C': //calendarizacion
                if ($datos['mov_id'] != 'C') {
                    $retorno->valido = false;
                }
                break;
            case 'D': //ampliación
                if ($datos['mov_id'] == 'C' || $datos['mov_id'] == 'R') {
                    $retorno->valido = false;
                }
                break;
            case 'E': //reducción
                if ($datos['mov_id'] == 'C' || $datos['mov_id'] == 'A') {
                    $retorno->valido = false;
                }
                break;
        } //fin switch
        if (!$retorno->valido) {
            $retorno->error = 25;
            $retorno->mensaje = 'Existe un movimiento que no corresponde al folio en la clave: ' . $datos['pre_claveCompleta'];
            return $retorno;
        } //fin if valido
        return $retorno;
    } // Fin getValidaMovimiento

    public function getValidaModalidadAdecuacion($mod, $enc_oop, $datos)
    {
        $sql_opp = '
            SELECT
                omo_id, tmd_id, mmd_id, tfm_id
            FROM enc_OrigenOperacion
            WHERE omo_id = ?
            AND tmd_id = ?
            AND tfm_id = ?
            AND mmd_id = ?
        ';
        $parametros = [
            $enc_oop->getOmo_id(),
            $enc_oop->getTmd_id(),
            $enc_oop->getTfm_id(),
            $enc_oop->getMmd_id(),
        ];
        $this->conectar();
        $this->setSql($sql_opp);
        $this->consultar($parametros);
        $this->desconectar();
        $folio = $this->extraer();

        $retorno = new stdClass();
        $retorno->valido = true;

        if ($mod == null || $datos == null || $enc_oop == null) {
            $retorno->valido = false;
            $retorno->error = 0;
            $retorno->mensaje = 'Faltan parametros en la consulta de validación';
            return $retorno;
        } // fin if

        // Modificación para 2020
        switch ($enc_oop->getMmd_id()) {
            case 'I':
                $error = RevisionPartidasporClaseGastos($folio, $datos);
                if ($error == 31) {
                    $retorno->valido = false;
                    $retorno->error = $error;
                    $retorno->mensaje = "La partida {$datos['pre_claveCompleta']} no se puede autorizar en un folio Interno de Traspaso Intrainstitucional.";
                    return $retorno;
                }
                if ($error == 32) {
                    $retorno->valido = false;
                    $retorno->error = $error;
                    $retorno->mensaje = "La partida {$datos['pre_claveCompleta']} no se puede autorizar en un folio Interno.";
                    return $retorno;
                }
                if ($error == 33) {
                    $retorno->valido = false;
                    $retorno->error = $error;
                    $retorno->mensaje = 'El tipo de Adecuación presupuestal no esta permitido en esta modalidad';
                    return $retorno;
                }

                // Verifica el tipo de compra solo debe ser D-Directa
                switch ($datos['tcp_id']) {
                    case 'D':
                        break;
                    default:
                        $retorno->valido = false;
                        $retorno->error = 34;
                        $retorno->mensaje = 'El tipo de compra no esta permitido en esta modalidad';
                        return $retorno;
                        break;
                } //fin switch

                // Verificar partidas Trnasferibles, Intransferibles y que se puedan ampliar de acuerdo al movimiento
                switch ($datos['mov_id']) {
                    case 'A': //Ampliacion
                        if ($datos['pe_reductible'] != 3 && $datos['pe_reductible']
                            != 1) {
                            $retorno->valido = false;
                            $retorno->error = 35;
                            $retorno->mensaje = "El folio tiene la partida {$datos['pre_claveCompleta']} y no es posible realizar la ampliación en esta modalidad, favor de eliminar el movimiento. ";
                            return $retorno;
                        }
                        break;
                    case 'R': //Reduccion
                        if ($datos['pe_reductible'] == 2 || $datos['pe_reductible']
                            == 3) {
                            $retorno->valido = false;
                            $retorno->error = 36;
                            $retorno->mensaje = "El folio tiene la partida {$datos['pre_claveCompleta']} y es intransferible, favor de eliminar el movimiento. ";
                            return $retorno;
                        }
                        break;
                } //fin switch
                break;
            case 'E':
                break;
        } //fin switch

        // Verifica la modalidad de la modificacion E-Externa, I-Interna
        switch ($enc_oop->getMmd_id()) {
            case 'E':
                break;
            case 'I':
                $mesActual = date('m');
                $mesFolio = substr($mod->getFechaFolio(), 3, 2);
                //Obtiene el arreglo del trimestre
                switch ($mesActual) {
                    case 1:
                    case 2:
                    case 3:
                        $trim = [1, 2, 3];
                        break;
                    case 4:
                    case 5:
                    case 6:
                        $trim = [4, 5, 6];
                        break;
                    case 7:
                    case 8:
                    case 9:
                        $trim = [7, 8, 9];
                        break;
                    case 10:
                    case 11:
                    case 12:
                        $trim = [10, 11, 12];
                        break;
                } //fin switch
                //Valida que la recalendarización corresponda al trimestre
                if (!in_array($mesFolio, $trim) && $enc_oop->getTmd_id() == 'C') {
                    $retorno->valido = false;
                    $retorno->error = 43;
                    $retorno->mensaje = 'La fecha del Folio no corresponde al trimestre actual. Debe crear nuevamente el folio en el trimestre en curso.';
                    return $retorno;
                }
                //Solo la dependencia puede realizar folios internos
                if ($mod->getOmo_id() != 'A') {
                    $retorno->valido = false;
                    $retorno->error = 44;
                    $retorno->mensaje = 'El origen de la modificación no esta permitido en esta modalidad';
                    return $retorno;
                }
                break;
        } //fin switch
        return $retorno;
    } // Fin getValidaModalidadAdecuacion

    public function getValidaCompensacionFolio($mod, $enc_oop, $totA, $totR, $folioMasivo)
    {
        $retorno = new stdClass();
        $retorno->valido = true;

        if (!is_object($mod) || !is_object($enc_oop)) {
            $retorno->valido = false;
            $retorno->error = 0;
            $retorno->mensaje = 'Faltan parametros en la consulta de validación';
            return $retorno;
        } //fin if

        switch ($mod->getEmo_id()) {
            case 1:
            case 2:
            case 4:
                //Se verifica que el movimiento este compensado antes de validacion SEFIN
                switch ($enc_oop->getTmd_id()) {
                    case 'A': //solo traspasos
                        if (bccomp(
                            Math::redondear($totA, 2),
                            Math::redondear($totR, 2),
                            2
                        ) != 0) {
                            $retorno->valido = false;
                            $retorno->error = 39;
                            $retorno->mensaje = "El Folio no esta compensado, Ampliaciones: $totA Reducciones: $totR";
                            return $retorno;
                        }
                        break;
                } //fin switch
                break;
            case 6:
            case 8:
            case 10:
            case 12:
            case 17:
                //Se verifica que el movimiento este compensado para validacion SEFIN
                if ($folioMasivo) { //si es de carga masiva verifica que este compensado para traspasos intrainstitucionales
                    switch ($enc_oop->getTmd_id()) {
                        case 'A': //traspasos intrainstitucionales
                            if (bccomp(
                                Math::redondear($totA, 2),
                                Math::redondear($totR, 2),
                                2
                            ) != 0) {
                                $retorno->valido = false;
                                $retorno->error = 40;
                                $retorno->mensaje = "El Folio no esta compensado, Ampliaciones: $totA Reducciones: $totR";
                                return $retorno;
                            }
                            break;
                    } //fin switch
                } else {
                    switch ($enc_oop->getTmd_id()) {
                        case 'A': //traspasos intrainstitucionales
                        case 'B': //traspasos interinstitucionales
                            if (bccomp(
                                Math::redondear($totA, 2),
                                Math::redondear($totR, 2),
                                2
                            ) != 0) {
                                $retorno->valido = false;
                                $retorno->error = 41;
                                $retorno->mensaje = "El Folio no esta compensado, Ampliaciones: $totA Reducciones: $totR";
                                return $retorno;
                            }
                            break;
                    } //fin switch
                } //fin folioMasivo
                break;
        } //fin switch

        return $retorno;
    } // Fin getValidaCompensacionFolio

    public function getValidaFoliosDRH($enc_oop, $mod)
    {
        $retorno = new stdClass();
        $retorno->valido = true;
        if ($mod == null || $enc_oop == null) {
            $retorno->valido = false;
            $retorno->error = 0;
            $retorno->mensaje = 'Faltan parametros en la consulta de validación';
            return $retorno;
        } //fin if

        switch ($enc_oop->getOmo_id() . $enc_oop->getTfm_id()) {
            case 'CA': //  Folio de Servicios personales - Dirección de Recursos Humanos
                switch ($mod['tcp_id']) {
                    case 'C': //Solo debe agregar movimientos con tipo de compra D
                        $retorno->valido = false;
                        $retorno->error = 37;
                        $retorno->mensaje = 'El folio solo puede tener movimientos de compras directas.';
                        return $retorno;
                        break;
                    default:
                        break;
                } //fin switch
                break;
            default:
                break;
        } // Fin switch
        return $retorno;
    } // Fin getValidaFoliosDRH

    public function getConsultaFolioPNE($mod)
    {
        if ($mod->getOmo_id() != '' && $mod->getMod_gpo_id() != '' && $mod->getMod_ur_id() != '' && $mod->getMod_id() != '') {
            $sql = '
                SELECT
                    m.omo_id,m.mod_gpo_id,m.mod_ur_id, m.mod_id,m.mod_Subsecretaria,o.toa_id,p.gpo_id,
                    p.ur_id,d.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id,p.pg_id,p.pe_id, p.cff_id,
                    p.fte_id,p.sft_id,p.ori_id,p.sor_id,p.cfi_id,p.cfi_anio
                FROM scp_MaestroModificacion m
                INNER JOIN scp_DetalleModificacion d USING(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                INNER JOIN enc_OrigenOperacion op USING(omo_id, enc_oop)
                INNER JOIN presupuesto p USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN cat_ObraActividad o USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
                INNER JOIN enc_ClaveFinanciamiento c USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . " USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE omo_id = '" . $mod->getOmo_id() . "'
                AND mod_gpo_id = " . $mod->getMod_gpo_id() . '
                AND mod_ur_id = ' . $mod->getMod_ur_id() . '
                AND mod_id = ' . $mod->getMod_id() . "
                AND c.ecf_id = 006434
                AND tmd_id='B'
                AND cff_id='A'
                AND toa_id=2 ;";
            $datos = $this->getDatos($sql, true);
            $numRegistros = $datos['registros'];

            if ($numRegistros > 0) {
                return true;
            } else {
                return false;
            } // fin numRegistros
        } else {
            return false;
        } // fin else
    } // Fin getConsultaFolioPNE

    public function getValidaAnticipo($mod, $tablaMod)
    {
        $nError = 47;
        if ($mod == null || $tablaMod == null) {
            return [[
                    'numero'  => $nError,
                    'mensaje' => 'Faltan parametros en la consulta de validación de anticipo',
                ]];
        } //fin if
        $errors = [];
        $sql = '
            WITH 
                obrasCfiInversion AS(
                    SELECT
                        gpo_id, ur_id, epg_id, ecf_id, epg_2025, oa_origen,
                        CONCAT(gpo_id, ur_id, oa.ue_id,"-", prg_id,"-", spr_id,"-", py_id,"-", oa_id)AS epg_Clave,
                        vigente - ejercido AS porEjercer, SUM( dmo_importe * ( IF(mov_id="R",-1,1) ) )AS modificacion
                    FROM cat_ObraActividad AS oa
                    JOIN enc_Programatico USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
                    JOIN poa_OrigenRecurso ore USING( gpo_id, ur_id, epg_id )
                    JOIN( 
                        SELECT epg_id, ecf_id, SUM(pre_vigente)AS vigente, SUM(pre_ejercido)AS ejercido
                        FROM presupuestoVigente
                        JOIN presupuestoEjercido USING(epg_id, ecf_id, pg_id, pe_id, tcp_id)
                        GROUP BY epg_id, ecf_id 
                    )AS p USING(epg_id, ecf_id)
                    JOIN ' . $tablaMod . ' USING(gpo_id, ur_id, epg_id, ecf_id)
                    WHERE omo_id = :omo_id AND mod_gpo_id = :mod_gpo_id AND mod_ur_id = :mod_ur_id AND mod_id  = :mod_id
                    GROUP BY epg_id, ecf_id
                ),
                clcsContable AS(
                    SELECT DISTINCT
                        gpo_id, ur_id, epg_id, ecf_id,
                        clc_id, afe_id, ncc_id, tcl_id, ecl_id,
                        cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,cgc_id, cta_id, cta_cuenta, cta_nombre, 
                        ncc_percepcion, ncc_retencion, ncc_concepto
                    FROM obrasCfiInversion
                    JOIN scp_Afectacion USING( gpo_id, ur_id, epg_id, ecf_id)
                    JOIN scp_Clc USING( gpo_id, ur_id, clc_id, ecf_id, tcl_id )
                    JOIN scp_Contable USING( gpo_id, ur_id, clc_id, afe_id )
                    JOIN cg_PlanCuentas USING(cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,cgc_id, cta_id)
                    WHERE ( ecl_id BETWEEN 6 AND 19 ) AND tcl_id = "H" AND ncc_percepcion > 0 
                    OR( (tcl_id != "H" AND ncc_retencion > 0) AND (ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) IN ( (2,1,1,9,4,005), (2,1,1,9,4,001), (1,1,3,4,2,001), (1,1,3,1,0,001) ) )    
                ),
                rectificacionAnticipos AS(
                    SELECT epg_id, ecf_id, SUM(rc.ncc_percepcion) AS anticiposRectificado
                    FROM clcsContable
                    JOIN scp_RectificacionContable AS rc USING(gpo_id, ur_id, clc_id, afe_id, ncc_id)
                    JOIN scp_MaestroRectificacion AS mr USING(gpo_id, ur_id, rct_id, clc_id)
                    WHERE tcl_id = "H" AND mr.ecl_id = 6
                    GROUP BY epg_id, ecf_id
                ),
                anticipos AS(
                    SELECT epg_id, ecf_id, SUM(ncc_percepcion)AS anticipo, SUM(ncc_retencion)AS retencion, IFNULL(anticiposRectificado,0) AS anticiposRectificado
                    FROM clcsContable
                    LEFT JOIN rectificacionAnticipos USING( epg_id, ecf_id )
                    WHERE tcl_id = "H"
                    GROUP BY epg_id, ecf_id
                ),
                rectificacionAmortizaciones AS(
                    SELECT epg_id, ecf_id, SUM(rc.ncc_retencion) AS amortizacionRectificada
                    FROM clcsContable
                    JOIN scp_RectificacionContable AS rc USING(gpo_id, ur_id, clc_id, afe_id, ncc_id)
                    JOIN scp_MaestroRectificacion AS mr USING(gpo_id, ur_id, rct_id, clc_id)
                    WHERE tcl_id != "H" AND mr.ecl_id = 6
                    GROUP BY epg_id, ecf_id
                ),
                amortizaciones AS(
                    SELECT epg_id, ecf_id, SUM(ncc_percepcion)AS percepcion, SUM(ncc_retencion)AS amortizacion, IFNULL(amortizacionRectificada,0) AS amortizacionRectificada
                    FROM clcsContable
                    LEFT JOIN rectificacionAmortizaciones USING( epg_id, ecf_id )
                    WHERE tcl_id != "H" 
                    GROUP BY epg_id, ecf_id
                )

            SELECT 
                epg_Clave,
                ( ( IFNULL(anticipo, 0)- IFNULL(anticiposRectificado,0) ) + IFNULL(coo_anticipo,0) ) - ( ( IFNULL(amortizacion,0) - IFNULL(amortizacionRectificada,0) ) - IFNULL(reembolso,0) ) AS porAmortizar,
                porEjercer + modificacion AS saldoParaAmortizar
            FROM obrasCfiInversion
            LEFT JOIN cat_ComplementoObra USING(gpo_id, ur_id, epg_id, ecf_id)
            LEFT JOIN anticipos USING(epg_id, ecf_id)
            LEFT JOIN amortizaciones USING(epg_id, ecf_id)
            LEFT JOIN(
                SELECT epg_id, ecf_id, SUM(rao_importe)AS reembolso
                FROM scp_ReintegroAnticipoObra
                GROUP BY epg_id, ecf_id
            )rem USING(epg_id, ecf_id)
            HAVING saldoParaAmortizar < porAmortizar;
        ';
        $parametros = [
            ':omo_id'     => $mod->getOmo_id(),
            ':mod_gpo_id' => $mod->getMod_gpo_id(),
            ':mod_ur_id'  => $mod->getMod_ur_id(),
            ':mod_id'     => $mod->getMod_id(),
        ];
        $datos = $this->getDatos($sql, false, $parametros);
        if ($datos['registros'] > 0) {
            foreach ($datos['datos'] as $row) {
                array_push($errors, [
                    'numero'  => $nError,
                    'mensaje' => 'No es posible realizar la reducción solicitada para la obra ' . $row['epg_Clave'] . ', debido a que existe un anticipo de obra pendiente de amortizar por: ' . number_format($row['porAmortizar'],2),
                ]);
                $nError++;
            }
        } //fin if
        return $errors;
    } // Fin getValidaFoliosDRH

}
