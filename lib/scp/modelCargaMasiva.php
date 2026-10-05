<?php

require_once dirname(__FILE__) . '/modelScp.php';
class modelCargaMasiva extends modelScp
{
    public function getInfoCarga($carga)
    {
        $dbBitacora = BD_BITACORA;
        $sql = <<<SQL
            SELECT c.tem_carga AS ID,
                CONCAT(c.gpo_id,c.ur_id) AS UR,
                DATE_FORMAT(c.usr_fecha,"%d/%m/%Y %H:%i:%s") AS Fecha,
                IF(ecr_id IN (99,100),"Completada","Error") AS Estado,
                usr_usuario  AS Usuario,
                TIMEDIFF(fin,ini) AS Duracion
            FROM cat_CargaMasiva c
            INNER JOIN cat_Usuario USING(usr_id)
            LEFT JOIN (
                SELECT crm_id,tem_carga, MIN(usr_fecha)ini,MAX(usr_fecha) fin
                FROM ' . BD_BITACORA . '.bit_CargaMasiva
                WHERE tem_carga ="' . $carga . '"
                    AND ecr_id NOT IN (99,100)
                    GROUP BY crm_id
            ) m USING ( crm_id,tem_carga)
            WHERE tem_carga ="{$carga}"
        SQL;

        $res = $this->getDatos($sql);
        $sql = 'SELECT cmp_id FROM scp_Compromiso WHERE tem_carga ="' . $carga . '"';
        $compromisos = $this->getDatos($sql, true);

        $sql = 'SELECT afe_id FROM scp_Afectacion WHERE tem_carga ="' . $carga . '"';
        $afectaciones = $this->getDatos($sql, true);

        $sql = 'SELECT clc_id FROM scp_Clc WHERE tem_carga ="' . $carga . '"';
        $clc = $this->getDatos($sql, true);

        $res['datos'][0]['compromisos'] = $compromisos['registros'];
        $res['datos'][0]['afectaciones'] = $afectaciones['registros'];
        $res['datos'][0]['clc'] = $clc['registros'];

        $head = ['ID', 'UR', 'Fecha', 'Estado', 'Usuario', 'Duraci&oacute;n', 'Compromisos', 'Afectaciones', 'CLC'];

        return ['cabecera' => $head, 'datos' => $res['datos'], 'registros' => $res['registros']];
    }

    public function getCompromisoCarga($carga)
    {
        $detalle = explode('-', $carga);
        $where = '';
        if ($detalle[0] != '118') {
            $where = ' gpo_id =' . substr($detalle[0], 0, 1) . ' AND ur_id =' . substr($detalle[0], 1, 2) . ' AND';
        }

        $sql = '
            SELECT 
                CONCAT(gpo_id,ur_id) AS UR,
                cmp_id AS Numero,
                cmp_importe AS Importe,
                pre_claveCompleta AS "Clave Presupuestal",
                ecm_nombre AS "Estado"
            FROM  scp_Compromiso
            INNER JOIN presupuesto USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN cat_EstadoCompromiso USING  ( ecm_id )
            WHERE ' . $where . '  tem_carga ="' . $carga . '"';
        $res = $this->getDatos($sql);
        return $res;
    }

    public function getAfectacionCarga($carga)
    {
        $detalle = explode('-', $carga);
        $where = '';
        if ($detalle[0] != '118') {
            $where = ' gpo_id =' . substr($detalle[0], 0, 1) . ' AND ur_id =' . substr($detalle[0], 1, 2) . ' AND';
        }

        $sql = '
            SELECT 
                CONCAT(gpo_id,ur_id) AS UR,
                tcl_id AS Tipo,
                stc_id AS Subtipo,
                afe_id AS Numero,
                eaf_nombre AS "Estado"
            FROM  scp_Afectacion
            INNER JOIN cat_EstadoAfectacion USING  ( eaf_id )
            WHERE ' . $where . '  tem_carga ="' . $carga . '"';
        $res = $this->getDatos($sql);
        return $res;
    }

    public function getClcCarga($carga)
    {
        $detalle = explode('-', $carga);
        $where = '';
        if ($detalle[0] != '118') {
            $where = ' gpo_id =' . substr($detalle[0], 0, 1) . ' AND ur_id =' . substr($detalle[0], 1, 2) . ' AND';
        }

        $sql = '
            SELECT 
                CONCAT(gpo_id,ur_id) AS UR,
                clc_id AS Numero,
                clc_concepto AS "Concepto",
                IF(ecl_id NOT IN (1,2,15,17,20),"En Finanzas",ecl_nombre) AS Estado
            FROM  scp_Clc
            INNER JOIN cat_EstadoClc USING  ( ecl_id )
            WHERE ' . $where . '  tem_carga ="' . $carga . '"';
        $res = $this->getDatos($sql);
        return $res;
    }

    public function fn_LiberaCarga($carga)
    {
        if (!isset($_SESSION[_S]['usr_id'])) {
            return false;
        }

        $usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        try {
            $this->conectar();

            // Tipo de carga
            $this->setSql('SELECT tcr_id FROM cat_CargaMasiva WHERE tem_carga = ?');
            if (!$this->ejecutar([$temCarga], 'consulta tipo de carga')) {
                return false;
            }
            $tipoCarga = $this->obtenerTodos();
            if (empty($tipoCarga)) {
                $this->logRechazoModel('ERROR: carga no encontrada ' . $temCarga);
                return false;
            }

        $this->setSql($sql);
        $this->consultar();
        $sql = '
            UPDATE presupuesto p
            INNER JOIN presupuestoVigente pv USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
            INNER JOIN ( 
                SELECT DISTINCT gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id 
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal 
                WHERE tem_carga = "' . $carga['tem_carga'] . '" 
                ) temporal USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id ) 
            SET p.pre_cargaMasiva = 0,
            pv.pre_mov = "" 
            WHERE
                pre_cargaMasiva = 1 
                AND pre_mov = "M";
            ';
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        return true;
    }

    public function cargaExiste($carga)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $sql = '
            SELECT * FROM cat_CargaMasiva
                WHERE gpo_id = ' . $gpo_id . ' AND ur_id = ' . $ur_id . '
                    AND tem_carga="' . $carga . '"';

        if ($this->getDatos($sql, true) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function validaCarga($carga_id)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $carga = explode('-', $carga_id);

        if (count($carga) != 4) {
            return ' ERROR EN EL FORMATO DE LA CARGA MASIVA ';
        }

        foreach ($carga as $detalle) {
            if (!is_numeric($detalle)) {
                return 'EL IDENTIFICADOR DE CARGA TIENE LETRAS';
            }
        }

        if ($carga[0] != $gpo_id . $ur_id) {
            return 'LA CARGA NO PERTENECE A LA UR';
        }

        return true;
    }

    public function getCompromisosCancelables($carga, $ex)
    {
        /*  OBTIENE EL LISTADO DE COMPROMISOS
            DE LA CARGA MASIVA
            CANCELABLES = COMPROMISO AUTORIZADO O DEVENGADO
            EN AFECTACION CANCELADA O SIN AFECTACION
            EL PARAMETRO EXTRA ES PARA NECESARIAMENTE EXTRAER
            LOS COMPROMISOS CANCELABLES */

        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $where = '';
        $where2 = '';
        if ($gpo_id . $ur_id == '118') {
            $where = ' WHERE p.tem_carga ="' . $carga . '"' . PHP_EOL;
            $where2 = 'WHERE c.tem_carga ="' . $carga . '"' . PHP_EOL;
        } else {
            $where = ' WHERE gpo_id =' . $gpo_id . ' AND ur_id =' . $ur_id . PHP_EOL;
            $where2 = 'WHERE gpo_id =' . $gpo_id . ' AND ur_id =' . $ur_id . ' 
                    AND c.tem_carga="' . $carga . '"' . PHP_EOL;
        }

        $sql = '
            SELECT CONCAT(gpo_id,ur_id) AS UR,cmp_id AS Compromiso,cmp_importe AS Importe,
                cmp_concepto AS Concepto,
                cmp_importe AS Importe,
                ecm_nombre AS "Estado Compromiso",
                clc_id AS CLC,
                afe_id AS Afectacion, ncp_id AS "#Control",
                eaf_nombre AS "Estado Afectacion",
                IF((eaf_id ="C" OR eaf_id IS NULL) AND ecm_id NOT IN("C","G") AND ( clc_id = 0 OR clc_id IS NULL ),"CANCELABLE","NO CANCELABLE") AS "Acción"
            FROM scp_Compromiso c
            INNER JOIN cat_EstadoCompromiso USING ( ecm_id )
            LEFT JOIN (
                SELECT gpo_id,ur_id,cmp_id,clc_id,afe_id,ncp_id,eaf_nombre ,eaf_id 
                FROM scp_Presupuestal p
                INNER JOIN scp_Afectacion USING ( gpo_id,ur_id,clc_id,afe_id )
                INNER JOIN cat_EstadoAfectacion USING (eaf_id)
                ' . $where . '
            )af  USING ( gpo_id,ur_id,cmp_id )
            ' . $where2 . '
            ' . PHP_EOL;

        if ($ex == true) {
            $sql .= 'HAVING "Acción" = "CANCELABLE"';
        }
        return $this->getDatos($sql);
    }

    public function fb_LiberaCompromisos($carga)
    {
        /* PASA A AUTORIZADOS TODOS LOS COMPROMISOS QUE
           ESTEN EN ESTADO DIFERENTE DE "A" Y QUE NO ESTEN CANCELADOS YA
           QUE SU AFECTACION YA ESTE CANCELADA O QUE NO EXISTA */

        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $where = '';
        $where2 = '';
        if ($gpo_id . $ur_id == '118' || $_SESSION[_S]['nvj_id'] > 2) {
            $where = ' WHERE p.tem_carga ="' . $carga . '"' . PHP_EOL;
            $where2 = 'WHERE c.tem_carga ="' . $carga . '"' . PHP_EOL;
        } else {
            $where = ' WHERE gpo_id =' . $gpo_id . ' AND ur_id =' . $ur_id . PHP_EOL;
            $where2 = 'WHERE gpo_id =' . $gpo_id . ' AND ur_id =' . $ur_id . ' 
                    AND c.tem_carga="' . $carga . '"' . PHP_EOL;
        }

        $sql = '
            UPDATE scp_Compromiso c
            INNER JOIN cat_EstadoCompromiso USING ( ecm_id)
            LEFT JOIN (
                SELECT gpo_id,ur_id,cmp_id,clc_id,afe_id,
                    ncp_id,eaf_nombre ,eaf_id 
                FROM scp_Presupuestal p
                INNER JOIN scp_Afectacion USING ( gpo_id,ur_id,clc_id,afe_id )
                INNER JOIN cat_EstadoAfectacion USING (eaf_id)
                ' . $where . '
            )af  USING ( gpo_id,ur_id,cmp_id )
            SET ecm_id = "A"
                ' . $where2 . '
                AND ecm_id NOT IN("G","A","C")
                AND ( af.clc_id = 0 OR af.clc_id IS NULL)
                AND (eaf_id NOT IN("A","G") OR eaf_id  IS NULL)';

        return $this->fnEjecutaSql($sql);
    }

    public function fb_LiberaAfectacion($carga)
    {
        /* PASAR TODAS LAS CLC DE ESTADO 2 A 1
           SEPARAR TODAS LAS AFECTACIONES DE LAS CLC QUE NO ESTEN EN ESTADO 3 */
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        if ($gpo_id . $ur_id == '118') {
            $sql = ' UPDATE scp_Clc SET ecl_id = 1 WHERE tem_carga ="' . $carga . '" AND ecl_id = 2';
            $this->fnEjecutaSql($sql);
        }

        $this->conectar();
        $this->setSql('
            SELECT gpo_id,ur_id,afe_id 
            FROM scp_Afectacion  a
            INNER JOIN scp_Clc USING ( gpo_id,ur_id,clc_id)
            WHERE a.tem_carga="' . $carga . '"
            AND clc_id > 0
            AND ecl_id = 1
        ');
        $this->logRechazoModel($this->getSql());
        $this->consultar();
        $afe = 0;
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];

        while ($res = $this->extraer()) {
            $sql = '
                UPDATE scp_Afectacion a 
                SET clc_id = 0 
                WHERE a.tem_carga="' . $carga . '"
                AND gpo_id  = ' . $res['gpo_id'] . '
                AND ur_id = ' . $res['ur_id'] . '
                AND afe_id = ' . $res['afe_id'] . '
            ';

            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql('SET @usr_id = ' . $user . ',@usr_ip = INET_ATON("' . $ip . '"),@bit_observacion="EJECUTADO DESDE EL SCP";');
            $con->consultar();
            $con->setSql($sql);
            $con->consultar();
            $afe += intval($con->getFilasAfectadas());
            $con->desconectar();
        }
        $this->desconectar();
        return $afe;
    }

    public function getAfectacionesCancelables($carga, $ex)
    {
        $sql = '
            SELECT 
            CONCAT(gpo_id,ur_id) AS UR, 
            afe_id AS "Afectación",
            clc_id AS CLC,
            afe_percepcion AS Percepciones,
            afe_retencion AS Retenciones,
            IF ( (clc_id = 0 OR ecl_id IN( 1,20,21 ) ) AND eaf_id ="A" ,"CANCELABLE","NO CANCELABLE") AS "Acción",
            eaf_nombre AS "Estado"
        FROM scp_Afectacion a
        INNER JOIN scp_Clc USING ( gpo_id,ur_id,clc_id )
        INNER JOIN cat_EstadoAfectacion USING ( eaf_id )
        WHERE a.tem_carga = "' . $carga . '"' . PHP_EOL;

        if ($ex) {
            $sql .= 'HAVING "Acción" ="CANCELABLE"';
        }
        return $this->getDatos($sql);
    }

    public function getClcCancelables($carga, $ex)
    {
        if ($carga == '') {
            return false;
        }
        $sql = '
            SELECT 
                CONCAT(gpo_id,ur_id) AS UR,
                clc_id AS CLC,
                clc_concepto AS Concepto,
                IF(ecl_id>2,"ENVIADA A FINANZAS","EN LA UR") AS Estado,
                clc_percepcion AS Percepcion,
                clc_retencion AS Retencion,
                IF(ecl_id = 3 ,"CANCELABLE","NO CANCELABLE") AS "Acción"
            FROM scp_Clc
            WHERE tem_carga="' . $carga . '"';
        if ($ex) {
            $sql .= 'HAVING "Acción" ="CANCELABLE"';
        }
        return $this->getDatos($sql);
    }

    public function fn_InhabilitaClcCM($carga)
    {
        $sql = ' 
            SELECT gpo_id,ur_id,clc_id ,COUNT(afe_id) AS Afes
            FROM scp_Clc c
            LEFT JOIN  scp_Afectacion USING ( gpo_id,ur_id,clc_id)
            WHERE c.tem_carga="' . $carga . '"
            GROUP BY gpo_id,ur_id,clc_id
            HAVING Afes = 0';
        $datos = $this->getDatos($sql);

        if ($datos['registros'] > 0) {
            foreach ($datos['datos'] as $cl) {
                $sql = '
                    UPDATE scp_Clc
                    SET ecl_id = 20
                    WHERE gpo_id = ' . $cl['gpo_id'] . ' AND ur_id = ' . $cl['ur_id'] . ' AND clc_id = ' . $cl['clc_id'] . ' AND tem_carga="' . $carga . '" AND ecl_id = 1';
                $this->fnEjecutaSql($sql);
            }
        }
        return true;
    }

    public function crg_CompromisoNoAutorizado($carga)
    {
        $sql = '
            SELECT cmp_id
            FROM ' . BD_CTRL . '.scp_Compromiso
            WHERE tem_carga="' . $carga . '"
            AND ecm_id !="A"';
        $res = $this->getDatos($sql, true);
        return $res['registros'];
    }

    public function fn_RevisaCveFin($tem_carga)
    {
        /*  REVISA QUE LAS CLAFES DE FINANCIAMIENTO CAPTURADAS
            EXISTAN EN EL CATÁLOGO DE CLAVES DE FINANCIAMIENTO */
        $valida = $this->validaCarga($tem_carga);

        if ($valida !== true) {
            return  ['html' => $valida, 'sql' => ''];
        }

        $this->conectar();
        $this->setSql('
            SELECT 
                CONCAT(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) AS Clave 
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal 
            LEFT JOIN enc_ClaveFinanciamiento e USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE tem_carga= "' . $tem_carga . '"
            AND e.ecf_id IS NULL
            UNION ALL
            SELECT 
                CONCAT(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) AS Clave 
                FROM ' . BD_TEMPORAL . '.tem_Contable
            LEFT JOIN enc_ClaveFinanciamiento e USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE tem_carga= "' . $tem_carga . '"
            AND e.ecf_id IS NULL;');

        $this->consultar();
        if ($this->getNumeroRegistros() > 0) {
            $html = '
                <table class="table table-bordered">
                    <tr>
                        <th class="warning">CLAVE(S) DE FINANCIAMIENTO QUE NO EXISTE(N)
                        </th>
                    <tr>';
            while ($res = $this->extraer()) {
                $html .= '<tr><td>' . $res['Clave'] . '</td></tr>';
            }
            $html .= '</table>';
            return  ['html' => $html, 'sql' => $this->getSql()];
        } else {
            return (bool) true;
        }
    }

    public function fn_RevisaObra($tem_carga)
    {
        $valida = $this->validaCarga($tem_carga);
        if ($valida !== true) {
            return  ['html' => $valida, 'sql' => ''];
        }
        $this->conectar();
        $this->setSql('
            SELECT 
                CONCAT(gpo_id,ur_id,"-",LPAD(prg_id,3,0),"-",LPAD(spr_id,2,0),"-",py_id,"-",oa_id) AS Clave 
            FROM ' . BD_TEMPORAL . '.tem_Presupuestal 
            LEFT JOIN enc_Programatico e USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            WHERE tem_carga= "' . $tem_carga . '"
            AND e.epg_id IS NULL
            UNION ALL
            SELECT 
                CONCAT(gpo_id,ur_id,"-",prg_id,"-",spr_id,"-",py_id,"-",oa_id) AS Clave 
                FROM ' . BD_TEMPORAL . '.tem_Contable
            LEFT JOIN enc_Programatico e USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            WHERE tem_carga= "' . $tem_carga . '"
            AND e.epg_id IS NULL;
        ');

        $this->consultar();

        if ($this->getNumeroRegistros() > 0) {
            $html = '
                <table class="table table-bordered">
                    <tr>
                        <th class="warning">OBRAS QUE NO EXISTE(N)</th>
                    <tr>';
            while ($res = $this->extraer()) {
                $html .= '<tr><td>' . $res['Clave'] . '</td></tr>';
            }
            $html .= '</table>';

            return  ['html' => $html, 'sql' => $this->getSql()];
        } else {
            return (bool) true;
        }
        return (bool) true;
    }

    public function fn_RevisaBloqueos($tem_carga, $opc = null)
    {
        $valida = $this->validaCarga($tem_carga);
        if ($valida !== true) {
            return  ['html' => $valida, 'sql' => ''];
        }
        $this->conectar();
        $sql = '
            SELECT 
                gpo_id,ur_id,SUM(abierto) AS abierto,tcl_id,tcl_nombre,t.stc_id,t.ecf_id,ter_id,
                blc_compromiso,blc_afectacion,blc_clc
            FROM(
                SELECT gpo_id,ur_id,tcl_id,stc_id,tcl_nombre,blc_compromiso,blc_afectacion,blc_clc,
                    ( ( blc_inicio IS NULL ) OR  ( NOW() < blc_inicio ) OR ( NOW() > blc_fin ) ) AS abierto,
                    blc_fin,ter_id,ecf_id
                FROM cat_TipoClc
                INNER JOIN enc_FiltroClc USING ( tcl_id )
                INNER JOIN (
                    SELECT gpo_id,ur_id,tcl_id,stc_id
                    FROM ' . BD_TEMPORAL . '.tem_Afectacion
                    WHERE tem_carga ="' . $tem_carga . '"
                    GROUP BY gpo_id,ur_id,tcl_id,stc_id
                )t USING (gpo_id,ur_id,tcl_id,stc_id)
                LEFT JOIN cat_BloqueoClc USING ( efc_id )
                GROUP BY gpo_id,ur_id,tcl_id,stc_id,blc_compromiso,blc_afectacion,blc_clc,blc_fin,ter_id,ecf_id,blc_inicio
                ORDER BY gpo_id,ur_id,tcl_id,stc_id
            )ASA
            INNER JOIN (
                SELECT gpo_id,ur_id,tcl_id,stc_id,ecf_id
                FROM ' . BD_TEMPORAL . '.tem_Afectacion
                WHERE tem_carga ="' . $tem_carga . '"
                GROUP BY gpo_id,ur_id,tcl_id,stc_id,ecf_id
            )t USING (gpo_id,ur_id,tcl_id)
            GROUP BY gpo_id,ur_id,tcl_id,blc_compromiso,blc_afectacion,blc_clc,blc_fin,ter_id,ecf_id,t.stc_id,t.ecf_id
            HAVING abierto = 0;';

        $this->setSql($sql);
        $this->consultar();

        // Se agrega el for para cada caso   27/02/19
        if ($this->getNumeroRegistros() > 0) {
            while ($res2 = $this->extraer()) {
                $v1 = $this->gtBloqueos($res2['tcl_id'], $res2['stc_id'], $res2['ecf_id'], $opc);
                if (!$v1->valido) {
                    $html = '
                        <table class="table table-bordered">
                            <tr>
                                <th class="warning" colspan="4">EXISTE UN BLOQUEO PARA EL TIPO'
                                . ' DE CLC DE ESTA CARGA, CONSULTE A SU ANALISTA DE LA DIRECCIÓN DE PRESUPUESTO
                                </th>
                            <tr>
                            <tr>
                                <td>Tipo de CLC</td>
                                <td>CLC</td>
                                <td>Afectaciones</td>
                                <td>Compromisos</td>
                            </tr>
                            <tr>
                                <td>' . $res2['tcl_nombre'] . '</td>
                                <td>' . $res2['blc_clc'] . '</td>
                                <td>' . $res2['blc_afectacion'] . '</td>
                                <td>' . $res2['blc_compromiso'] . '</td>
                            </tr>
                        </table>';
                    return  ['html' => $html, 'sql' => $this->getSql()];
                }
                return (bool) true;
            }
        } else {
            $this->desconectar();
            return (bool) true;
        }
    }

    /**METODO PARA VERIFICAR SI LA CARGA MASIVA CUENTA CON ASPECTOS PRESUPUESTALES EN TRANSITO
     * SE PIDE EL NÚMERO DE LA CARGA MASIVA
     * Ticket #550148 */
    public function fn_RevisaFoliosAdecuaciones($tem_carga)
    {
        $valida = $this->validaCarga($tem_carga);

        if ($valida !== true) {
            return  ['html' => $valida, 'sql' => ''];
        }

        $this->conectar();
        $sql = '
            SELECT 
                CONCAT(p.gpo_id, p.ur_id) AS ur ,
                afe.tcl_id AS tipo,
                CONCAT(p.cff_id,p.fte_id,p.sft_id,p.ori_id,p.sor_id,p.cfi_id,p.cfi_anio) AS claveFinanciamiento,
                CONCAT(p.prg_id,"-",p.spr_id,"-",p.py_id,"-",p.oa_id,"-",afe.pg_id,"-",afe.pe_id) AS programatico
            FROM ' . BD_CTRL . '.presupuesto p
            INNER JOIN ' . BD_CTRL . '.scp_DetalleModificacion d USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN ' . BD_CTRL . '.scp_MaestroModificacion m USING (omo_id, mod_gpo_id, mod_ur_id, mod_id)
            INNER JOIN ' . BD_CTRL . '.cat_ObraActividad o USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
            INNER JOIN ' . BD_CTRL . '.enc_Programatico ep USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id,oa_id)
            INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING (omo_id,enc_oop)
            INNER JOIN ' . BD_CTRL . '.cat_TipoFolioModificacion tf USING (tfm_id)
            INNER JOIN ' . BD_CTRL . '.cat_TipoModificacion t USING (tmd_id)
            INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scl USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
            INNER JOIN sinpres.cat_TipoRecurso tr USING (ter_id)
            INNER JOIN ' . BD_TEMPORAL . '.tem_Presupuestal afe ON afe.gpo_id = p.gpo_id 
                AND afe.ur_id = p.ur_id AND afe.ue_id = p.ue_id 
                AND afe.epg_id = p.epg_id AND afe.pg_id = p.pg_id 
                AND afe.pe_id = p.pe_id AND afe.ecf_id = p.ecf_id 
                AND afe.tcp_id = p.tcp_id
            WHERE toa_id = 1 
                AND m.emo_id not in(14,15,16) #Folios de Egresos
                AND mod_Subsecretaria = 1
                AND t.tmd_id in ("A","B","C","E") # A - Intra, B - Inter, C - recalendarizacion, E - Reducción
                AND mov_id != "A"
                AND afe.tem_carga = "' . $tem_carga . '" 
                AND afe.tcl_id IN ("A", "B")
            GROUP BY p.ur_id,p.ue_id,p.epg_id,p.pg_id,p.pe_id,p.ecf_id,ep.epg_id,
                scl.cff_id,scl.fte_id,scl.sft_id,scl.ori_id,scl.sor_id,scl.cfi_id,scl.cfi_anio,
                mod_id,afe.afe_id,afe.tcl_id;
            ';
        $this->setSql($sql);
        $this->consultar();
        $html = '
            <table class="table table-bordered">
            <tr>
                <th class="warning" colspan="4">EXISTEN FOLIOS DE ADECUACIONES PRESUPUESTARIAS EN PROCESO'
                . ', CONSULTE A SU ANALISTA DE LA DIRECCIÓN DE PRESUPUESTO
                </th>
            <tr>
            <tr> 
                <td>UR</td>
                <td>TIPO DE COMPROMISO/AFECTACIÓN</td>
                <td>CLAVE PRESUPUESTAL</td>
            </tr>';
        if ($this->getNumeroRegistros() > 0) {
            while ($folios = $this->extraer()) {

                $html .= '
                    <tr>
                        <td>' . $folios['ur'] . '</td>
                        <td>' . $folios['tipo'] . '</td>
                        <td>' . $folios['programatico'] . '-' . $folios['claveFinanciamiento'] . '</td>
                    </tr>';
            }
            $html .= '</table>';
            return  ['html' => $html, 'sql' => $this->getSql()];
            $this->desconectar();
            return (bool) true;
        } else {
            $this->desconectar();
            return (bool) true;
        }
    }

    private function logRechazoModel($data)
    {
        /* ----- LOG DE ERROROES DE CONEXION -------- */
        if (!file_exists('./log')) {
            mkdir('./log', 0777, true);
        }

        $rutalog = './log/logModel' . $_SESSION[_S]['usr_id'];
        $texto = date('d-m-Y H:i:s')
            . 'CLASE[' . get_Class($this) . ']||'
            . 'CLIENTE[' . $_SERVER['REMOTE_ADDR'] . ']||'
            . 'OBSERVACION[' . $data . ']';

        $file = fopen($rutalog . '.log', 'a');
        fwrite($file, $texto . PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog . '.log');
        $peso = intval(($peso / 1024) / 1024);
        $hoy = date('d-m-Y');
        if ($peso > 8) {
            rename($rutalog . '.log', $rutalog . $hoy . '.txt');
            $file = fopen($rutalog . '.log', 'a');
            fwrite($file, 'CREADO:' . $hoy . PHP_EOL);
            fclose($file);
        }
        unset($file);
    }

    private function fn_CancelarAfectacion($data = [])
    {
        if (!file_exists("log")) {
                mkdir("log", 0777, true);
        }
        if (!file_exists("log/carga")) {
            mkdir("log/carga", 0777, true);
        }

        $this->conectar();
        $this->setObservacion("RECHAZO DE LA CARGA MASIVA POR ERROR");
        $rutalog = "log/carga/RechazoCarga" . $data['tem_carga'] . ".log";
        $file = fopen($rutalog, "a");
        fwrite($file, 'L:' . $this->getSql() . PHP_EOL);
        fclose($file);

        $sql = 'CALL sp_CancelarAfectacion' . APP_EJERCICIO . '(?,?,?,INET_ATON(?),?);';
        $this->setSql($sql);
        $params = [
            $data['gpo_id'],
            $data['ur_id'],
            $data['afe_id'],
            getSession('ses_ip'),
            getSession('usr_id')
        ];
        $this->consultar($params);

        $rutalog = "log/carga/RechazoCarga" . $data['tem_carga'] . ".log";
        $file = fopen($rutalog, "a");
        fwrite($file, 'L:' . $sql . PHP_EOL);
        fclose($file);

        $this->setSql('SELECT @banderaPresupuesto AS bandera,@mensajePresupuesto AS mensaje, @banderaContable as bc, @mensajeContable AS mc');
        $this->consultar();
        $men = $this->extraer();
        $this->desconectar();
        return $men;
    }
}
