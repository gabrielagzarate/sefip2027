<?php

require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';

class modelscp extends AccesoDatos
{
    public $nvj;
    public $grupo;
    public $ur;
    public $unidadEjecutora;
    public $usuario_id;
    public $ip;
    public $adm_grupo;
    public $adm_ur;
    public $adm_Programa;
    public $adm_subPrograma;
    public $adm_proyecto;
    public $adm_obraActividad;
    public $adm_unidadEjecutora;
    public $cve_financiamiento;

    public function __construct()
    {
        parent::__construct();
        //Variables de sesión
        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->grupo = $_SESSION[_S]['gpo_id'];
        $this->ur = $_SESSION[_S]['ur_id'];
        $this->unidadEjecutora = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '';
        $this->usuario_id = $_SESSION[_S]['usr_id'];
        $this->ip = $_SESSION[_S]['ses_ip'];
        $this->adm_grupo = $_SESSION[_S]['clasAdm-gpo'];
        $this->adm_ur = $_SESSION[_S]['clasAdm-ur'];
        $this->adm_Programa = isset($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : null;
        $this->adm_subPrograma = isset($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : null;
        $this->adm_proyecto = isset($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : '';
        $this->adm_obraActividad = isset($_SESSION[_S]['clasAdm-oa']) ? $_SESSION[_S]['clasAdm-oa'] : null;
        $this->adm_unidadEjecutora = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : null;
        $this->cve_financiamiento = isset($_SESSION[_S]['cvefin']) ? $_SESSION[_S]['cvefin'] : null;
    } // Fin __construct

    public function getDatos($sql, $registros = false, $params = []): array
    {
        $ret = [];
        $this->conectar();
        $this->setSql($sql);
        if ($params) {
            $this->consultar($params);
        } else {
            $this->consultar();
        }

        $ret['registros'] = $this->getNumeroRegistros();
        $this->desconectar();
        if ($registros) {
            return $ret;
        } // Fin registro

        $datos = [];
        $row = $this->extraer();
        array_push($datos, $row);

        $cabecera = [];
        if ($ret['registros'] > 0) {
            foreach ($row as $th => $val) {
                array_push($cabecera, $th);
            } // fin foreach

            while ($row = $this->extraer()) {
                array_push($datos, $row);
            } // Fin while
        } // fin Fin

        $ret['cabecera'] = $cabecera;
        $ret['datos'] = $datos;
        $ret['registros'] = $this->getNumeroRegistros();

        return $ret ?? [];
    } // Fin getDatos

    public function getDatosObj($sql, $registros = false, $params = []): array
    {
        $ret = [];
        $this->conectar();
        $this->setSql($sql);
        if ($params) {
            $this->consultar($params);
        } else {
            $this->consultar();
        }
        $ret['registros'] = $this->getNumeroRegistros();
        $this->desconectar();
        if ($registros) {
            return $ret;
        } // Fin registro

        $datos = [];
        $row = $this->obtener();
        array_push($datos, $row);

        $cabecera = [];
        if ($ret['registros'] > 0) {
            foreach ($row as $th => $val) {
                array_push($cabecera, $th);
            } // Fin foreach

            while ($row = $this->obtener()) {
                array_push($datos, $row);
            } // Fin while
        } // Fin registros
        $ret['cabecera'] = $cabecera;
        $ret['datos'] = $datos;
        $ret['registros'] = $this->getNumeroRegistros();

        return $ret;
    } // Fin getDatosObj

    public function fn_pintaTabla($datos, $attr = null)
    {
        if (!is_array($datos)) {
            return '<table><tr><td>Sin datos</td></tr></table>';
        }
        $idtabla = '';
        if (is_array($attr)) {
            if (isset($attr['idtabla'])) {
                $idtabla = 'id=" ' . $attr['idtabla'] . ' " ';
            }
        }

        $tabla = '<table ' . $idtabla . ' class=" table table-bordered">';
        if (isset($datos['cabecera'])) {
            $tabla .= '<tr>';
            foreach ($datos['cabecera'] as $th) {
                $tabla .= '<th class="success"> ' . $th . '</th>';
            }
            $tabla .= '</tr>';
        }

        if (isset($datos['datos'])) {
            $c = 1;
            foreach ($datos['datos'] as $valores) {
                $class = ($c % 2) == 1 ? 'FFFFFF' : 'EAEAEA';
                $attributos = '';
                /* ATRIBUTOS DEL TR PARA CONSULTA SE PUED PONER COMO ID */
                if (is_array($attr)) {
                    if (isset($attr['ids']) && is_array($attr['ids'])) {
                        foreach ($attr['ids'] as $row) {
                            $attributos .= $row . ' ="' . $valores[$row] . '"';
                        }
                    }
                }

                $align = null;
                if (isset($attr['alineaciones']) && is_array($attr['alineaciones'])) {
                    $align = $this->bld_alienaciontabla($attr['alineaciones']);
                }

                $formato = null;
                if (isset($attr['formatos']) && is_array($attr['formatos'])) {
                    $formato = $attr['formatos'];
                }

                $tabla .= '<tr ' . $attributos . '>';
                $index = 0;
                if (is_array($valores)) {
                    foreach ($valores as $td) {
                        $ali = '';
                        if ($align != null && isset($align[$index]) && $align != '') {
                            $ali = 'align="' . $align[$index] . '"';
                        }

                        /* ETIQUETA TD */
                        $tabla .= '<td style="background-color:#' . $class . '" '
                            . $ali . '>';

                        /* CONTENIDO DEL TD */
                        $tipo = is_array($formato) ? $formato[$index] : 'TEXTO';

                        $valor = $this->ret_formato($tipo, $td);
                        $tabla .= $valor;

                        /* CIERRE ETIQUETA TD */
                        $tabla .= '</td>'; // clase del td
                        $index++;
                    } // Fin foreach
                }
                $tabla .= '</tr>';
                $c++;
            } // Fin foreach
        } // Fin datos

        $tabla .= '</table>';
        return $tabla;
    } // Fin fn_pintaTabla

    private function ret_formato($tipo, $valor)
    {
        switch (strtoupper($tipo)) {
            case 'TEXTO':
                return '<span>' . $valor . '</span>';
                break;
            case 'ENTERO':
                return '<span>' . intval($valor) . '</span>';
                break;
            case 'MONEDA':
                return '<span style="float:left">$</span>' . number_format(floatval($valor), 2) . '';
                break;
            default:
                return '<span>' . $valor . '</span>';
                break;
        } // Fin switch
    } // Fin ret_formato

    private function bld_alienaciontabla($arreglo)
    {
        $arr = [];

        foreach ($arreglo as $val) {
            switch (strtoupper($val)) {
                case 'C':
                    array_push($arr, 'center');
                    break;
                case 'R':
                    array_push($arr, 'right');
                    break;
                case 'L':
                default:
                    array_push($arr, 'left');
                    break;
            }
        } // Fin foreach
        return $arr;
    } // Fin bld_alienaciontabla

    public function getClaveFinF1($ecf)
    {
        if (!isset($this->usuario_id)) {
            return 'SIN SESION';
        } // Fin usuario_id

        $encuadre = '';
        $nombre = '';

        if (!isset($this->cve_financiamiento)) {
            $params = [$ecf];
            $sql = '
                SELECT ter_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,cfi_nombre
                FROM enc_ClaveFinanciamiento
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE ecf_id = ?';

            $datos = $this->getDatos($sql, false, $params);
            $datos = $datos['datos'][0];

            $encuadre = $datos['ter_id'] . '-'
                . $datos['cff_id']
                . $datos['fte_id']
                . $datos['sft_id']
                . $datos['ori_id']
                . $datos['sor_id'] . '.'
                . $datos['cfi_id']
                . $datos['cfi_anio'];
            $nombre = $datos['cfi_nombre'];
        } else {
            $encuadre = $this->cve_financiamiento->ter
            . '-' . $this->cve_financiamiento->cff
            . $this->cve_financiamiento->fte
            . $this->cve_financiamiento->sft
            . $this->cve_financiamiento->ori
            . $this->cve_financiamiento->sor . '.'
            . $this->cve_financiamiento->cfi
            . $this->cve_financiamiento->anio;
            $nombre = $this->cve_financiamiento->nombre;
        } // fin cve_financiamiento

        return ['encuadre' => $encuadre, 'nombre' => $nombre];
    } // Fin getClaveFinF1

    public function getEpgF1($epg)
    {
        if (!isset($this->usuario_id)) {
            return 'SIN SESION';
        }
        $gpo_id = $this->nvj < 2 ? $this->grupo : $this->adm_grupo;
        $ur_id = $this->nvj < 2 ? $this->ur : $this->adm_ur;

        if (
            $this->adm_Programa != null
            || $this->adm_subPrograma != null
            || $this->adm_proyecto != null
            || $this->adm_obraActividad != null
        ) {
            $params = [$epg];
            $sql = '
                SELECT * FROM enc_Programatico
                INNER JOIN cat_ObraActividad USING ( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                WHERE epg_id = ?';

            $datos = $this->getDatos($sql, false, $params);
            $datos = $datos['datos'][0];
            return $datos['gpo_id'] . $datos['ur_id'] . '.'
                . $datos['ue_id'] . '.'
                . $datos['prg_id'] . '.'
                . $datos['spr_id'] . '.'
                . $datos['py_id'] . '.'
                . $datos['oa_id'];
        } else {
            return $gpo_id . $ur_id . '.'
            . $this->adm_unidadEjecutora . '.'
            . $this->adm_Programa . '.'
            . $this->adm_subPrograma . '.'
            . $this->adm_proyecto . '.'
            . $this->adm_obraActividad;
        } // Fin adm_Programa
    } // Fin getEpgF1

    public function gtBloqueos($tipo = '', $stc_id = '', $ecf_id = '', $opc = null)
    {
        $gpo_id = $this->nvj < 2 ? $this->grupo : $this->adm_grupo;
        $ur_id = $this->nvj < 2 ? $this->ur : $this->adm_ur;
        $momento = '';

        if (!is_null($opc)) {
            $opcion = $opc->sis_id . $opc->men_id . $opc->opc_id;

            switch ($opcion) {
                case 'S23':
                    $momento = 'blc_compromiso';
                    break;
                case 'S21':
                    $momento = 'blc_afectacion';
                    break;
                case 'S22':
                    $momento = 'blc_clc';
                    break;
            } // Fin switch
        } // Fin opc

        $subtipo = '';
        if ($gpo_id == '' || $ur_id == '') {
            return false;
        } // Fin if

        $bloqueo = new stdClass();
        $bloqueo->valido = true;

        /* ------------  VALIDAR LOS DATOS DE ENTRADA NECESARIOS PARA EL BLOQUEO ---------------- */

        if ($tipo == '' || $stc_id == '' || $ecf_id == '' || $opc == null || $momento == '') {
            $bloqueo->valido = false;
            $bloqueo->error = 1;
            $bloqueo->mensaje = 'NO ES POSIBLE VERIFICAR SI EXISTE UN BLOQUEO DE CLC';
            return $bloqueo;
        } // Fin if
        /* EVALUAMOS QUE EL TIPO NO ESTÉ BLOQUEADO POR EGRESOS */
        if ($tipo != '') {
            $params = [$gpo_id, $ur_id, $tipo, $gpo_id, $ur_id, $tipo];
            $sql = '
                SELECT
                    SUM(ASA.abierto) AS abierto, ASA.tcl_nombre, ASA.ter_id, ASA.ecf_id, ASA.momento
                FROM (SELECT
                    fc.gpo_id,
                    fc.ur_id,
                    tc.tcl_id,
                    fc.stc_id,
                    tc.tcl_nombre,
                    bc.' . $momento . ' AS momento,
                    ((bc.blc_inicio IS NULL) OR (NOW() < bc.blc_inicio) OR (NOW() > bc.blc_fin)) AS abierto,
                    bc.blc_fin,
                    bc.ter_id,
                    bc.ecf_id
                FROM cat_TipoClc tc
                    INNER JOIN enc_FiltroClc fc USING (tcl_id)
                    LEFT JOIN cat_BloqueoClc bc USING (efc_id)
                WHERE fc.gpo_id = ?
                AND fc.ur_id = ?
                AND tcl_id = ?
                GROUP BY fc.gpo_id, fc.ur_id, tc.tcl_id, fc.stc_id, tc.tcl_nombre, bc.blc_afectacion,
                    bc.blc_inicio, bc.blc_fin, bc.ter_id, bc.ecf_id,bc.' . $momento . '
                ORDER BY gpo_id, ur_id, tcl_id, stc_id)ASA
                WHERE gpo_id = ?
                AND ur_id = ?
                AND tcl_id = ?
                GROUP BY ASA.tcl_id, ASA.tcl_nombre, ASA.ter_id, ASA.ecf_id, ASA.momento;
            ';

            $datos = $this->getDatos($sql, false, $params);
            $dat = $datos['datos'][0];

            // Se consulta el tipo de recurso de la clave de financiamiento que quiere afectar, si es el mismo tipo de recurso bloqueado no podrá hacer clcs
            $params = [$ecf_id];
            $claveFin = '
                SELECT ter_id
                FROM enc_ClaveFinanciamiento ecf
                JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                where ecf.ecf_id = ?';
            $tipoRecurso = $this->getDatos($claveFin, false, $params);
            $ter_id = $tipoRecurso['datos'][0];

            /*SI TODOS LOS SUBTIPOS DEL TIPO DE CLC ESTÁN BLOQUEADOS SE BLOQUEA COMPLETAMENTE LA GENERAÍON DE CLC*/
            if (isset($dat['abierto']) == 0) {
                if ((
                    is_null(isset($dat['ecf_id'])) &&
                    in_array(isset($dat['ter_id']), ['A', 'B', 'N']) &&
                    $dat['momento'] == 'X' && $dat['ter_id'] == $ter_id['ter_id']
                ) || (
                    is_null(isset($dat['ecf_id'])) &&
                    isset($dat['ter_id']) == 'N' &&
                    $dat['momento'] == 'X'
                )) {
                    $bloqueo->valido = false;
                    $bloqueo->error = 2;
                    $bloqueo->mensaje = 'LA CLC DE ' . $dat['tcl_nombre'] . ' ( ' . $tipo . ' )' .
                        'SE ENCUENTRA BLOQUEADA, CUALQUIER DUDA O COMENTARIO FAVOR DE COMUNICARSE CON SU ANALISTA';
                    return $bloqueo;
                }
            } // Fin abierto
            /* VALIDAMOS QUE ESTE ABIERTO PARA EL MÓDULO REQUERIDO */
        } // Fin tipo
        /* EVALUAMOS QUE EL SUBTIPO QUE ELEGIÓ LA UR NO ESTÉ BLOQUEADO POR EGRESOS SI REGRESA UN VALOR EN TER_ID
        DIFENTE DE N O UN VALOR EN CLAVE DE FINANCIAMIENTO NO REGRESAMOS UN ERROR  */
        if ($tipo != '' && $stc_id != '') {
            //Se actualizo la consulta por el group by
            $params = [$gpo_id, $ur_id, $tipo, $stc_id];
            $sql = '
                SELECT
                    st.stc_id,
                    st.stc_nombre,
                    (bc.blc_inicio IS NULL OR (now() < bc.blc_inicio OR now() > bc.blc_fin)) abierto,
                    bc.ter_id,
                    bc.ecf_id,
                    bc.' . $momento . ' AS  momento
                FROM cat_SubtipoClc st
                        INNER JOIN enc_FiltroClc fc USING (tcl_id, stc_id)
                        LEFT JOIN cat_BloqueoClc bc using (efc_id)
                WHERE fc.gpo_id = ?
                AND fc.ur_id = ?
                AND st.tcl_id = ?
                AND st.stc_id = ?
                GROUP BY st.tcl_id, st.stc_id, st.stc_nombre, bc.blc_inicio, bc.blc_fin, bc.ter_id, bc.ecf_id, bc.' . $momento . ';
            ';

            $dat2 = $this->getDatos($sql, false, $params);
            $dat2 = $dat2['datos'][0];

            if (
                $dat2 &&
                ($dat2['abierto'] == 0
                    && $dat2['ter_id'] == 'N'
                    && is_null($dat2['ecf_id'])
                    && $dat2['momento'] == 'X')
            ) {
                $bloqueo->valido = false;
                $bloqueo->error = 3;
                $bloqueo->mensaje = 'EL SUBTIPO ' . $dat2['stc_nombre'] . ' DEL TIPO ' . $tipo .
                    'SE ENCUENTRA BLOQUEADO, CUALQUIER DUDA O COMENTARIO FAVOR DE COMUNICARSE CON SU ANALISTA';
                return $bloqueo;
            } // Fin if abierto
        } // Fin if

        if ($tipo != '' && $stc_id != '' && $ecf_id != '') {
            /*  PARA LOS TIPO DE INVERSION PUBLICA DEBIDO A QUE NO SE MANEJAN SUBTIPOS CUANDO
            SE GENERE UN BLOQUEO SE VA A GENERAR POR TODO EL TIPO DE CLC Y NUNCA VA A TRAER EL SUBTIPO
            DE CLC YA QUE QUIEN DETONA LAS CLAVES DE FINANCIAMIENTO DISPONIBLES ES LA SELECCÓN DEL
            ENCUADRE PROGRAMATICO.  */
            $valueSubtipo = '';
            switch ($tipo) {
                case 'A':
                case 'I':
                case 'C':
                case 'D':
                case 'E':
                case 'M':
                case 'J':
                case 'K':
                case 'L':
                    $subtipo = ' AND stc_id = ?';
                    $valueSubtipo = $stc_id;
                    break;
                case 'F':
                case 'G':
                case 'H':
                    $subtipo = '';
                    break;
            } // Fin switch

            /* VERIFICAMOS QUE TIPO DE RECURSO ES LA CLAVE DE FINANCIAMIENTO QUE ESTÁN UTILIZANDO */
            $params = [$ecf_id];
            $sql = '
                SELECT ecf_id,ter_id,ecf_clave
                FROM enc_ClaveFinanciamiento
                INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING( cff_id,fte_id, sft_id, ori_id, sor_id)
                WHERE ecf_id= ?';

            $dat3 = $this->getDatos($sql, false, $params);
            $dat3 = $dat3['datos'][0];

            /* VERIFICAMOS QUE NO EXISTA UN BLOQUEO POR TIPO DE RECURSO EN EL MÓDULO DE EGRESOS */
            //Se actualizo la consulta por el group by
            $params = [$gpo_id, $ur_id, $tipo];
            if ($valueSubtipo) {
                array_push($params, $valueSubtipo);
            }
            $sql = '
                SELECT gpo_id,
                        ur_id,
                        tcl_id,
                        stc_id,
                        (blc_inicio IS NULL OR (now() < blc_inicio OR now() > blc_fin)) abierto,
                        ter_id,
                        ecf_id,
                        ' . $momento . ' AS momento
                FROM enc_FiltroClc
                        INNER JOIN cat_BloqueoClc USING (efc_id)
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND tcl_id = ? ' . $subtipo . '
                GROUP BY gpo_id, ur_id, tcl_id, stc_id, blc_inicio, blc_fin, ter_id, ecf_id, ' . $momento . ';
            ';

            $dat4 = $this->getDatos($sql, false, $params);
            $dat4 = $dat4['datos'][0];

            if (isset($dat4['abierto']) && $dat4['abierto'] == 0) { /* VERIFICAMOS QUE EL BLOQUEO ESTE ACTIVO */
                /*$bloqueo->prueba = 'Si entro al caso de 0';
                return $bloqueo;*/
                if (
                    isset($dat3['ter_id']) && isset($dat4['ter_id'])
                    && strcmp($dat3['ter_id'], $dat4['ter_id']) === 0
                    && $dat4['momento'] == 'X'
                ) {

                    $bloqueo->valido = false;
                    $bloqueo->error = 4;
                    $bloqueo->mensaje = ' EL TIPO DE RECURSO PARA LA CLAVE ' . $dat3['ecf_clave'] .
                        'SE ENCUENTRA BLOQUEADO, CUALQUIER DUDA O COMENTARIO FAVOR DE COMUNICARSE CON SU ANALISTA';
                    return $bloqueo;
                }
            } // Fin abierto

            /* VERIFICAMOS QUE NO EXISTA UN BLOQUEO CON LA CLAVE DE FINANCIAMIENTO */
            // Se actualizo una consulta, por lo del group by
            array_push($params, $ecf_id);
            $sql = '
                SELECT gpo_id,
                        ur_id,
                        tcl_id,
                        stc_id,
                        (blc_inicio IS NULL OR (now() < blc_inicio OR now() > blc_fin)) abierto,
                        ter_id,
                        ecf_id,
                        ' . $momento . ' AS  momento
                FROM enc_FiltroClc
                INNER JOIN cat_BloqueoClc bc using (efc_id)
                WHERE gpo_id = ?
                AND ur_id = ?
                AND tcl_id= ? '
                . $subtipo . '
                AND ecf_id = ?
                GROUP BY gpo_id, ur_id, tcl_id, stc_id, bc.blc_inicio, bc.blc_fin, bc.' . $momento . ', bc.ter_id, bc.ecf_id;
            ';

            $dat5 = $this->getDatos($sql, false, $params);
            $dat5 = $dat5['datos'][0];

            if (isset($dat5['abierto']) && $dat5['abierto'] == 0) { /*  VERIFICAMOS QUE EL BLOQUEO ESTE ACTIVO */
                if (isset($dat5['ecf_id']) && strcmp($ecf_id, $dat5['ecf_id']) === 0 && $dat5['momento'] == 'X') {
                    $bloqueo->valido = false;
                    $bloqueo->error = 5;
                    $bloqueo->mensaje = 'LA CLAVE ' . $dat3['ecf_clave'] .
                        'SE ENCUENTRA BLOQUEADA, CUALQUIER DUDA O COMENTARIO FAVOR DE COMUNICARSE CON SU ANALISTA';
                    return $bloqueo;
                } // Fin if
            } // Fin if abierto
        } // Fin if
        return $bloqueo;
    } // Fin gtBloqueos

    public function setTcls()
    {
        if (isset($_SESSION[_S]['tcl_id'])) {
            return false;
        }

        $_SESSION[_S]['tcl_id'] = [];
        $sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc';
        $datos = $this->getDatos($sql);
        foreach ($datos['datos'] as $tcl) {
            $_SESSION[_S]['tcl_id'][$tcl['tcl_id']] = $tcl['tcl_nombre'];
        } // Fin foreach
    } // Fin setTcls

    public function setUrAdministracion()
    {
        if (isset($_SESSION[_S]['ursAdministracion'])) {
            return false;
        }
        $_SESSION[_S]['ursAdmon'] = [];

        $sql = 'SELECT cua_urClave FROM cat_UrAdministracion WHERE sts_id = 1';
        $datos = $this->getDatos($sql);
        foreach ($datos['datos'] as $tcl) {
            array_push($_SESSION[_S]['ursAdmon'], $tcl['cua_urClave']);
        } // Fin foreach
    } // Fin setUrAdministracion

    /**
     *@param string $sql
     *@param array $params
     *@return array
     */
    public function fnEjecutaSql($sql, $paramsprm = [])
    {
        $ip = (isset($this->ip) && $this->ip != '::1') ? $this->ip : '127.0.0.1';
        $user = $this->usuario_id;
        $this->conectar();
        $this->setSql('
            SET
            @usr_id = ?,
            @usr_ip = INET_ATON(?),
            @bit_observacion="EJECUTADO DESDE PANTALLA DEL SCP";
        ');
        $params = [$user, $ip];
        $this->consultar($params);
        $this->setSql($sql);

        if ($paramsprm) {
            $this->consultar($paramsprm);
        } else {
            $this->consultar();
        }
        if ($this->getErrno() > 0) {
            $this->desconectar();
            return $this->getError();
        }
        $filas = $this->getFilasAfectadas();
        $this->desconectar();
        return $filas;
    }

    public function getTiposClc()
    {
        if (!isset($this->usuario_id)) {
            return false;
        }

        $gpo_id = $this->nvj == 1 ? $this->grupo : $this->adm_grupo;
        $ur_id = $this->nvj == 1 ? $this->ur : $this->adm_ur;
        $ue_id = $this->nvj == 1 && isset($this->unidadEjecutora) && $this->unidadEjecutora != ''
        ? $this->unidadEjecutora : null;
        $ur315 = '';
        $join = '';
        $and = '';
        $params = [$gpo_id, $ur_id];
        if ($gpo_id . $ur_id == 116) {
            switch ($ue_id) {
                // 005 UNIDAD DE PLANEACION FINACIERA PAGOS
                // 006 RECAUDACION
                case '005':
                case '006':
                    $ur315 = 'AND tcl_id = "M"';
                    break;
                    // 003 FIDEICOMISOS, APORTACIONES Y OTROS
                    // 004 SUBIDOS, ESTIMULOS FISCALES Y OTROS
                case '003':
                case '004':
                    $ur315 = 'AND tcl_id = "L"';
                    break;
                    //002 DEUDA PUBLICA
                case '002':
                    $ur315 = 'AND tcl_id = "J"';
                    break;
                    //001 INGRESOS Y DERECHOS AFECTOS A FIDEICOMISOS
                case '001':
                    $ur315 = 'AND tcl_id = "K"';
                    break;
                default:
                    $ur315 = '';
                    break;
            } // Fin switch
            if ($this->usuario_id == 1) {
                $join = ' JOIN cat_CargaTrabajo ct using(gpo_id,ur_id,ue_id)';
                $and = ' AND ue_id= ?';
                array_push($params, $this->adm_unidadEjecutora);
            } // Fin usuario_id
        }

        $sql = '
            SELECT gpo_id,ur_id,tcl_id,tcl_nombre
            FROM cat_TipoClc
            JOIN enc_FiltroClc using(tcl_id)
            ' . $join . '
            WHERE gpo_id= ? AND ur_id= ? ' . $ur315 . '
            ' . $and . '
            GROUP BY gpo_id,ur_id,tcl_id
            ORDER BY gpo_id,ur_id,tcl_id;
        ';

        $datos = $this->getDatos($sql, false, $params);
        return $datos;
    } // Fin getTiposClc

    public function getClaveBursa($clave)
    {
        if ($clave == '') {
            return '00';
        } // Fin clave

        $vehicularNomina = [
            'AAABA0119',
            'AAABA0219',
            'AAABA0319',
            'AAABA0419',
            'AAABA0519',
            'AAABA0619',
            'AAABA0719',
            'AAABA0819',
            'AAABB0119',
            'AAABB0219',
            'AAABB0319',
            'AAABB0419',
            'AAABB0519',
            'AAABB0619',
            'AAABB0719',
            'AACBA0119',
            'AACBA0219',
            'AACBA0319',
            'AACBA0419',
            'AACBA0519',
            'AACBA0619',
            'AACBA0719',
            'AACBA0819',
            'AACBA0S819',
            'AAAAA0119',
            'AALAA0419',
        ];

        if (in_array($clave, $vehicularNomina, true)) {
            return '01';
        } // Fin if

        $tenencia = [
            'AAAAA0219',
            'AAAAA0319',
            'AAAAA0419',
            'AAAAA0519',
            'AAAAA0619',
            'AAAAA0719',
            'AAAAA0819',
            'AAAAA0919',
            'AAKAA0119',
            'AAKAA0219',
            'AAKAA0319',
            'AAKAA0419',
            'AAKAA0519',
            'AAKAA0619',
            'AAKAA0719',
            'AAKAA0819',
            'AAKAA0919',
        ];

        if (in_array($clave, $tenencia, true)) {
            return '02';
        } else {
            return '00';
        } // Fin if
    } // Fin getClaveBursa

    /**METODO PARA OBTENER LOS FOLIOS DE ADEACIONES PRESUPUESTALES EN TRANSITO
     * SE PIDEN LOS ID'S NECESARIOS DE LA CLAVE PRESUPUESTAL, EL TIPO DE PAGO
     * UNA PARTE DE LA CLAVE PRESUPUESTAL
     * EL TIPO (COMPROMISO O AFECTACIÓN)
     * LA ACCIÓN REALIZADA (AUTORIZACIÓN, O GENERACIÓN)
     * Ticket #550148
     */
    final public function getFolioDevengadoComprometido(
        $gpo_id = null,
        $ur_id = null,
        $ue_id = null,
        $epg_id = null,
        $pg_id = null,
        $pe_id = null,
        $ecf_id = null,
        $tcp_id = null,
        $clavepresupuestal = null,
        $tipo = null,
        $accion = null,
        $numAccion = null
    ) {
        $tieneFolio = new stdClass();
        $tieneFolio->estado = false;
        $tieneFolio->mensaje = '';

        if (($gpo_id != null && trim($gpo_id) != '') && ($ur_id != null && trim($ur_id) != '')
            && ($ue_id != null && trim($ue_id) != '') && ($epg_id != null && trim($epg_id) != '')
            && ($pg_id != null && trim($pg_id) != '') && ($pe_id != null && trim($pe_id) != '')
            && ($ecf_id != null && trim($ecf_id) != '') && ($tcp_id != null && trim($tcp_id) != '')
            && ($tipo != null && trim($tipo) != '') && ($accion != null && trim($accion) != '')
            && ($clavepresupuestal != null && trim($clavepresupuestal) != '')
        ) {
            /**10-07-2019 SI SON DE TIPO AFECTACIONES EN EL APARTADO DE AUTORIZACIÓN SE VERIFICARÁ LA CONSULTA
             * CON LOS ASPECTOS PRESUPUESTALES SI NO ES EL CASO SE CHECARÁ DE MANERA NORMAL CON LA CLAVE PRESUPUESTAL*/
            $select = $tipo == 'A' && $accion == 'A' ? '
                    CONCAT(p.cff_id,p.fte_id,p.sft_id,p.ori_id,p.sor_id,p.cfi_id,p.cfi_anio) AS claveFinanciamiento,
                    CONCAT(p.prg_id,"-",p.spr_id,"-",p.py_id,"-",p.oa_id,"-",afe.pg_id,"-",afe.pe_id) AS programatico
                    ' :
            '
                    CONCAT(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) AS claveFinanciamiento
                ';
            $innerJoin = $tipo == 'A' && $accion == 'A' ? '
                INNER JOIN ' . BD_CTRL . '.scp_Presupuestal afe ON afe.gpo_id = p.gpo_id
                    AND afe.ur_id = p.ur_id
                    AND afe.ue_id = p.ue_id
                    AND afe.epg_id = p.epg_id
                    AND afe.pg_id = p.pg_id
                    AND afe.pe_id = p.pe_id
                    AND afe.ecf_id = p.ecf_id
                    AND afe.tcp_id = p.tcp_id'
            : '';
            $params = ($tipo == 'A' && $accion == 'A') ? [$gpo_id, $ur_id, $numAccion] : [$gpo_id, $ur_id, $ue_id, $epg_id, $pg_id, $pe_id, $ecf_id, $tcp_id];
            $where = $tipo == 'A' && $accion == 'A' ? '
                AND afe.gpo_id = ?
                AND afe.ur_id = ?
                AND afe.afe_id = ? '
            : '
                AND p.gpo_id = ?
                AND p.ur_id = ?
                AND p.ue_id = ?
                AND p.epg_id = ?
                AND p.pg_id = ?
                AND p.pe_id = ?
                AND p.ecf_id = ?
                AND p.tcp_id = ?
            ';
            $groupBy = $tipo == 'A' && $accion == 'A' ? '
                    GROUP BY p.ur_id, p.ue_id , p.epg_id, p.pg_id, p.pe_id, p.ecf_id, ep.epg_id,
                        cff_id, fte_id, sft_id , ori_id, sor_id, cfi_id, cfi_anio,
                        mod_id, afe.afe_id, afe.ncp_id'
            :
            '
                    GROUP BY  p.ur_id, p.ue_id , p.epg_id, p.pg_id, p.pe_id, p.ecf_id, ep.epg_id, mod_id
                ';
            /**---------------------------------------------------------------------------------------------- */
            $this->conectar();
            $this->setSql('
                SELECT
                    ' . $select . '
                FROM ' . BD_CTRL . '.presupuesto p
                    INNER JOIN ' . BD_CTRL . '.scp_DetalleModificacion d USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN ' . BD_CTRL . '.scp_MaestroModificacion m USING (omo_id, mod_gpo_id, mod_ur_id, mod_id)
                    INNER JOIN ' . BD_CTRL . '.cat_ObraActividad o USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
                    INNER JOIN ' . BD_CTRL . '.enc_Programatico ep USING (epg_id, gpo_id, ur_id, prg_id, spr_id, py_id,oa_id)
                    INNER JOIN ' . BD_CTRL . '.enc_OrigenOperacion op USING (omo_id,enc_oop)
                    INNER JOIN ' . BD_CTRL . '.cat_TipoFolioModificacion tf USING (tfm_id)
                    INNER JOIN ' . BD_CTRL . '.cat_TipoModificacion t USING (tmd_id)
                    INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                    INNER JOIN sinpres.cat_TipoRecurso tr USING (ter_id)
                    ' . $innerJoin . '
                WHERE toa_id = 1
                    AND m.emo_id not in(14,15,16)
                    AND mod_Subsecretaria = 1 #Folios de Egresos
                    AND t.tmd_id in ("A","B","C","E") # A - Intra, B - Inter, C - recalendarizacion, E - Reducción
                    AND mov_id != "A"
                    /*Empieza los filtros para clave presupuestal*/
                    ' . $where . '
                    ' . $groupBy . '
                    LIMIT 1;
                ');
            $this->consultar($params);
            $num = $this->getNumeroRegistros();

            if ($num > 0) {
                $claveFinanciamiento = $this->extraer();
                $mensaje1 = $tipo == 'C' ? 'el compromiso' : 'la afectación';
                $mensaje2 = $accion == 'A' ? 'autorizar' : 'generar';
                $mensaje3 = $num <= 1 ? 'un folio de adecuación' : 'varios folios de adecuaciones';
                /**11-07-2019 PARA EL CASO DE LA AFECTACION  EN SU AUTORIZACIÓN*/
                $mensaje4 = $tipo == 'A' && $accion == 'A' ?
                $claveFinanciamiento['programatico'] . '-' . $claveFinanciamiento['claveFinanciamiento'] :
                $clavepresupuestal . '-' . $claveFinanciamiento['claveFinanciamiento'];
                $tieneFolio->estado = true;
                $tieneFolio->mensaje = 'No se puede ' . $mensaje2 . ' ' . $mensaje1 . ' por que tiene ' .
                    $mensaje3 . ' para la siguiente clave de presupuestal: ' . $mensaje4;
            }
            $this->desconectar();
        } else {
            $tieneFolio->estado = true;
            $tieneFolio->mensaje = 'No se puede proseguir con la verificación de folios';
        }
        return $tieneFolio;
    }
}
