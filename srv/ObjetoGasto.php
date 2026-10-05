<?php

require_once dirname(__FILE__) . '/../lib/constantes.php';
if (session_status() == PHP_SESSION_NONE && !isset($_SESSION[_S])) {
    session_start();
}
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../lib/claveFinRecursoFederal.php';
require_once dirname(__FILE__) . '/../lib/scp/ClaseGasto.php';

/**
 * Objeto del Gasto
 *
 * Esta clase permite mostrar/seleccionar el objeto del gasto (Partida Generica y Partida Especifica)
 * y almacenar en una variable sesion las partidas seleccionadas
 *
 * @author       L.I. Anuar López Morgan <anuar.lopez@finanzasoaxaca.gob.mx>
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * @version:     1
 *
 * Modificación por cedula de costeo - Israel
 * 1.- Todas las cedulas de costeo deberan enviar la variable ->filtro=costeo
 * 2.- Todas las cedulas de costeo deberan enviar la variable ->ccd_id=(numero de cedula de costeo)
 *   2.1.- Las cedulas de arrendamiento, telefonia y vehiculos, deberan enviar la variable ->cve_id=(numero de la cedula de costeo interna)
 * Modificación por cedula de costeo - RVG
 * Se actualiza el pe_id 355 por 353 para evitar duplicidad en el catalogo de mantenimiento.
 * Se quita la condicion AND pg_id != 296 para retornar partida generica.
 * Se quita la condicion AND pe_id != 259 para retornar partida especifica.
 */

class ObjetoGasto extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();

        $metodo = ((isset($this->prm->metodoObjGto)) ? $this->prm->metodoObjGto : '');

        switch ($metodo) {
            case 'cambioPg':
                $this->cambioPg();
                break;
            case 'cambioPe':
                $this->cambioPe();
                break;
            case 'cargaPe':
                $this->cargaPe();
                break;
            case 'cargaPg':
                $this->cargaPg();
                break;
        }

        $this->returnJson();
    } // __construct

    /**
     * Coloca la estructura y template's para seleccionar o mostrar la clave de financiamiento
     *
     * @param object     $p La funcion puede o no tener este parametro.
     * @param boolean    $p->required La funcion puede o no tener este parametro.
     * @return string    Devuelve en una cadena de caracteres el html donde muestra la clasificacion de financiamiento
     */

    public static function ponObjetoGasto($objGto = null)
    {
        if (is_array($objGto)) {
            $objGto = (object) $objGto;
        } elseif (!is_object($objGto)) {
            $objGto = null;
        }
        $_SESSION[_S]['objGto'] = new stdClass();

        $sql = '
            SELECT DISTINCT pg_id, pg_nombre, ' . (isset($objGto->vienDe) ? '' : 'cto_nombre,') . 'cto_id
            FROM cat_PartidaGenerica
            JOIN cat_Concepto USING(cto_id)
        ';

        $peSql = '';

        $lectura = (isset($objGto->lectura)) ? $objGto->lectura : false;

        $datosFiltro = '';

        if (isset($objGto->filtro)) {
            switch ($objGto->filtro) {
                case 'encuadre':
                    $sql = '
                        SELECT DISTINCT pg_id, pg_nombre
                        FROM cat_PartidaGenerica
                        JOIN enc_PartGenUr USING(pg_id)
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                        ';
                    break;
                case 'ModNomPartida':
                    if ($objGto->tipo == 'A') {
                        $sql = '
                            SELECT DISTINCT pg_id, pg_nombre
                            FROM cat_PartidaGenerica
                            INNER JOIN enc_PartidaPersonales USING(pg_id)
                            INNER JOIN presupuestoVigente USING(pg_id)';

                        if (isset($objGto->mno_id)) {
                            $sql .= 'WHERE mno_id =' . $objGto->mno_id;
                            $sql .= ' AND gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'];
                            if (isset($objGto->ecf_id)) {
                                $sql .= ' AND ecf_id=' . $objGto->ecf_id;
                            }
                            if (isset($objGto->epg_id)) {
                                $sql .= ' AND epg_id = ' . $objGto->epg_id;
                            }
                            $sql .= ' AND pre_vigente !=0';
                        }
                    } else {
                        $adminDirecta = '';
                        if ($objGto->tipo == 'G') {
                            $adminDirecta = ' WHERE pe_id NOT IN (394,014) ';
                        }
                        $sql = '
                            SELECT DISTINCT pg_id,pg_nombre
                            FROM cat_PartidaGenerica
                            INNER JOIN presupuestoVigente USING(pg_id)
                            WHERE  pe_id not IN (
                                SELECT
                                    DISTINCT pe_id
                                FROM enc_PartidaPersonales
                                ' . $adminDirecta . ')';
                        $sql .= ' AND gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'];
                        $sql .= ' AND pre_vigente !=0 ';
                        if (isset($objGto->ecf_id)) {
                            $sql .= ' AND ecf_id=' . $objGto->ecf_id;
                        }
                        if (isset($objGto->epg_id)) {
                            $sql .= ' AND epg_id = ' . $objGto->epg_id;
                        }
                        $peSql = '';
                    }

                    if (isset($objGto->lectura) && $objGto->lectura == true) {
                        $sql = ' SELECT DISTINCT pg_id,pg_nombre FROM cat_PartidaGenerica
                                    WHERE pg_id = ' . $objGto->pg_id . '';
                        $peSql = 'SELECT DISTINCT pe_id, pe_nombre
                                        FROM cat_PartidaEspecifica
                                        WHERE pe_id = ' . $objGto->pe_id . ' AND pg_id = ' . $objGto->pg_id . '
                                    ';
                    }

                    break;
                case 'serviciosPersonales':
                case 'otraspartidas':
                    $sql = '
                        SELECT DISTINCT pg_id, pg_nombre
                        FROM cat_PartidaGenerica
                        JOIN cat_PartidaEspecifica USING(pg_id)
                        JOIN enc_PartGenUr USING(pg_id)
                        WHERE pe_cedula = 2
                    ';

                    if (isset($objGto->pg_id)) {
                        $sql .= ($lectura) ? ' AND pg_id=' . $objGto->pg_id : ';';

                        $peSql = '
                            SELECT DISTINCT pe_id, pe_nombre
                            FROM cat_PartidaEspecifica
                            JOIN enc_PartGenUr USING(pg_id)
                            WHERE pe_cedula = 2 AND pg_id = ' . $objGto->pg_id . '
                            ';

                        $peSql .= ($lectura) ? ' AND pe_id=' . $objGto->pe_id : ';';

                    }
                    break;
                case 'costeo':
                    //ccd_id el tipo de cedula
                    //cve_id cuando se divide en varias( arrendamiento, telefonia, vehiculos )
                    if (isset($objGto->ccd_id)) {
                        if ($objGto->ccd_id != 9 && $objGto->ccd_id != 2) {
                            /*se realizo validacion para evitar duplicidad en el catalogo de mantenimiento cuando las partidas son 296 y 355*/
                            $sql = '
                                SELECT DISTINCT pg_id, pg_nombre
                                FROM enc_PartEspCed
                                    INNER JOIN enc_PartGenUr USING( pg_id )
                                    INNER JOIN cat_PartidaGenerica USING( pg_id )
                                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] .
                            ' AND ccd_id = ' . $objGto->ccd_id .
                                (isset($objGto->cve_id) ? ' AND cve_id = ' . $objGto->cve_id : '') .
                                (isset($objGto->pve_id) ?
                                //((($objGto->cve_id == 6) && ($objGto->pve_id == 2 || $objGto->pve_id == 3)) ? ' AND pg_id != 296'
                                (
                                    (($objGto->cve_id == 6) && ($objGto->pve_id == 2 || $objGto->pve_id == 3)) ? ' '
                                    : ((($objGto->cve_id == 6) && ($objGto->pve_id == 1 || $objGto->pve_id == 4)) ? ' AND pg_id != 355' : '')
                                )
                                : '');
                            $sql .= ($lectura) ? ' AND pg_id = ' . $objGto->pg_id : '';

                            $peSql = '
                            SELECT DISTINCT pe_id, pe_nombre
                            FROM enc_PartEspCed
                                INNER JOIN enc_PartGenUr USING( pg_id )
                                INNER JOIN cat_PartidaEspecifica USING( pg_id, pe_id )
                            WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] .
                            ' AND ccd_id = ' . $objGto->ccd_id .
                                (isset($objGto->cve_id) ? ' AND cve_id = ' . $objGto->cve_id : '') .
                                (isset($objGto->pve_id) ?

                                (
                                    (($objGto->cve_id == 6) && ($objGto->pve_id == 2 || $objGto->pve_id == 3)) ? ' '
                                    : ((($objGto->cve_id == 6) && ($objGto->pve_id == 1 || $objGto->pve_id == 4)) ? ' AND pe_id != 353' : '')
                                )
                                : '');

                            $peSql .= ($lectura) ? ' AND pg_id = ' . $objGto->pg_id . ' AND pe_id = ' . $objGto->pe_id : '';
                        } else {
                            $sql = '
                                SELECT DISTINCT pg_id, pg_nombre
                                FROM cat_PartidaGenerica
                                    INNER JOIN cat_PartidaEspecifica e USING( pg_id )
                                    INNER JOIN enc_PartGenUr USING( pg_id )
                                    LEFT JOIN (
                                        SELECT  pg_id, pe_id
                                        FROM enc_PartEspCed
                                        GROUP BY pg_id, pe_id
                                    )c USING( pg_id, pe_id )
                                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                                    AND tno_id = 0
                                    AND c.pg_id IS NULL
                            ';

                            $sql .= ($lectura) ? ' AND e.pg_id = ' . $objGto->pg_id . ' AND e.pe_id = ' . $objGto->pe_id : '';

                            $peSql = '
                                SELECT DISTINCT pe_id, pe_nombre
                                FROM cat_PartidaGenerica
                                    INNER JOIN cat_PartidaEspecifica e USING( pg_id )
                                    INNER JOIN enc_PartGenUr USING( pg_id )
                                    LEFT JOIN (
                                        SELECT pg_id, pe_id
                                        FROM enc_PartEspCed
                                        GROUP BY pg_id, pe_id
                                    )c USING( pg_id, pe_id )
                                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                                    AND tno_id = 0
                                    AND c.pg_id IS NULL
                            ';

                            $peSql .= ($lectura) ? ' AND e.pg_id = ' . $objGto->pg_id . ' AND e.pe_id = ' . $objGto->pe_id : '';

                        }

                    } else {
                        $sql = 'SELECT null pg_id,"No especificó la Cedula" pg_nombre;';
                    }
                    //echo "<pre>".$sql."</pre>";
                    break;
                case 'modificaciones': //Modificaciones presupuestales
                    if (isset($objGto->pg_id) && isset($objGto->pe_id)) {
                        $sql = '
                            SELECT DISTINCT pg_id, pg_nombre
                            FROM cat_PartidaGenerica pg
                            WHERE pg_id = ' . $objGto->pg_id . '
                        ';
                        $peSql = '
                            SELECT DISTINCT pe_id,pe_nombre
                            FROM cat_PartidaEspecifica pe
                            WHERE pg_id = ' . $objGto->pg_id . ' AND pe_id = ' . $objGto->pe_id . '
                        ';
                    } else {
                        $where1 = '
                            WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] .
                        ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] .
                        ' AND ue_id=' . (!empty($_SESSION[_S]['clasAdm-ue']) ?
                            $_SESSION[_S]['clasAdm-ue'] : 0) . '
                            ' . (isset($_SESSION[_S]['clasAdm-epg']) && !empty($_SESSION[_S]['clasAdm-epg'])
                            && $_SESSION[_S]['clasAdm-epg'] !== 0 ?
                            '  AND epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '' : '') . '
                            AND ecf_id=' . $objGto->ecf_id . '
                        ';
                        $where1 .= (isset($objGto->tcp_id) && $objGto->tcp_id != '' ?
                            ' AND tcp_id="' . $objGto->tcp_id . '" ' : '');
                        $where2 = '
                            WHERE m.mod_gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] .
                        ' AND m.mod_ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                            ' . (isset($_SESSION[_S]['clasAdm-epg']) && !empty($_SESSION[_S]['clasAdm-epg'])
                            && $_SESSION[_S]['clasAdm-epg'] !== 0 ?
                            '  AND epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '' : '') .
                        ' AND m.ecf_id=' . $objGto->ecf_id . '
                        ';
                        $where2 .= (isset($objGto->tcp_id) && $objGto->tcp_id != '' ?
                            ' AND tcp_id="' . $objGto->tcp_id . '" ' : '');

                        # $idp = '';

                        $clg = PartidasporClaseGastos($objGto->omo_id, $objGto->tfm_id, $objGto->mmd_id, $objGto->tmd_id);
                        $usar = AgrupacionSql($objGto->tmd_id, $objGto->tipoMovimiento);

                        /*//Verifica el tipo de folio de modificacion
                        switch ($objGto->tfm_id) {
                        case 'A'://Servicios Personales
                        $idp = " (idp_id = 'A' or pe.pe_id in(394,395,396,397,398,399,411)) AND ";
                        break;
                        case 'B'://Gastos de Operación
                        $idp = "idp_id IN ('B','C','D','E','F','H','J','K','R','S','T','Y','N','P','U','X') AND ".
                        "pe.pe_id NOT IN(394,395,396,397,398,399,411) AND "
                        ;
                        break;
                        case 'C'://Inversión Pública
                        switch ($objGto->mmd_id.$objGto->tmd_id) {
                        case 'IC':  // Calendario interno de inversión, unicamente idp M,X
                        $idp = " idp_id IN ('M','X') and ";
                        break;
                        default:
                        $idp = " idp_id IN ('M','V','X') and ";
                        break;
                        }
                        break;
                        }
                        //Valida el tipo de movimiento
                        switch($objGto->tmd_id){
                        case 'B'://Traspaso Interinstitucional
                        //Valida el tipo de movimiento es Ampliacion
                        if ($objGto->tipoMovimiento=='A') {
                        $usar = "USING(gpo_id, ur_id, epg_id, pg_id, pe_id, ecf_id)";
                        } else {
                        $usar = "USING(gpo_id, ur_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)";
                        }
                        break;
                        default:
                        $usar = "USING(gpo_id, ur_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)";
                        break;
                        }*/

                        $sql = "
                            SELECT DISTINCT p.pg_id, pg_nombre
                            FROM
                            (
                                SELECT
                                    DISTINCT  gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id,ecf_id, tcp_id
                                FROM presupuesto
                                $where1
                            ) as p
                            JOIN cat_PartidaGenerica pg using(pg_id)
                            JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                            join enc_PartGenUr using(gpo_id,ur_id,pg_id)
                            LEFT JOIN
                            (
                                SELECT
                                    DISTINCT  m.mod_gpo_id, m.mod_ur_id, gpo_id, ur_id, ue_id,
                                    epg_id, pg_id, pe_id, m.ecf_id, tcp_id
                                FROM scp_MaestroModificacion m
                                    JOIN scp_DetalleModificacion d using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                                $where2 AND emo_id not in (14,15,16)
                            ) as t $usar
                            WHERE $clg t.pe_id is null";
                        #WHERE $idp t.pe_id is null";
                    }
                    break;
            } // fin switch

            $_SESSION[_S]['objGto']->filtro = $objGto->filtro;
        } else {
            if (isset($objGto->pg_id)) {
                $sql .= ($lectura) ? ' WHERE pg_id=' . $objGto->pg_id : ';';

                $peSql = '
                SELECT DISTINCT pe_id, pe_nombre
                FROM cat_PartidaEspecifica
                JOIN enc_PartGenUr USING(pg_id)
                WHERE pg_id = ' . $objGto->pg_id . '
                ';

                $peSql .= ($lectura) ? ' AND pe_id=' . $objGto->pe_id : ';';
            }
        }

        $og = new WebResponse();

        $combo = new stdClass();
        $combo->id = 'pg_id';
        $combo->label = 'Partida Genérica';
        $combo->required = true;
        $combo->enDiv = true;
        $combo->camposVal = 'pg_id';
        $combo->agrupaPor = 'cto_id';
        $combo->campoLabelAgrupacion = 'cto_nombre';
        $combo->mensaje = 'Lista de Partida Generica';
        $combo->search = true;

        if (isset($objGto->form)) {
            $combo->required = $objGto->form;
        }

        $combo->sql = $sql . ' GROUP BY pg_id ORDER BY pg_id';
        $combo->detonar = false;
        $combo->change = '
            comunica("srv/ObjetoGasto.php",{
                metodoObjGto:"cambioPg",
                pg_id:$(this).val()
                ' . (isset($objGto->ecf_id) ? ', ecf_id: "' . $objGto->ecf_id . '"' : '') . '
                ' . (isset($objGto->omo_id) ? ', omo_id: "' . $objGto->omo_id . '"' : '') . '
                ' . (isset($objGto->tcp_id) ? ', tcp_id: "' . $objGto->tcp_id . '"' : '') . '
                ' . (isset($objGto->tfm_id) ? ', tfm_id: "' . $objGto->tfm_id . '"' : '') . '
                ' . (isset($objGto->ccd_id) ? ', ccd_id: ' . $objGto->ccd_id : '') . '
                ' . (isset($objGto->cve_id) ? ', cve_id: ' . $objGto->cve_id : '') . '
                ' . (isset($objGto->pe_id) ? ',  pe_id: ' . $objGto->pe_id : '') . '
                ' . (isset($objGto->tmd_id) ? ",  tmd_id: '" . $objGto->tmd_id . "'" : '') . '
                ' . (isset($objGto->mmd_id) ? ",  mmd_id: '" . $objGto->mmd_id . "'" : '') . '
                ' . (isset($objGto->tipoMovimiento) ? ",  tipoMovimiento: '" . $objGto->tipoMovimiento . "'" : '') . '
                ' . ($lectura ? ', lectura: ' . $lectura : '') . '
            });
        ';

        if (isset($objGto->pg_id)) {
            $combo->selecciona = $objGto->pg_id;
        }

        if ((isset($_SESSION[_S]['clasAdm-epg']) && !is_null($_SESSION[_S]['clasAdm-epg'])) || isset($objGto->pg_id)) {
            $pg = $og->ponCombo($combo);
        } elseif (isset($objGto->filtro) && ($objGto->filtro == 'ModNomPartida' || $objGto->filtro == 'costeo' || $objGto->filtro == 'encuadre')) {
            $pg = $og->ponCombo($combo);
        } elseif ($objGto == null) {
            /* para cuando se desea poner el objeto del gasto, sin nigun filtro*/
            $pg = $og->ponCombo($combo);
        } else {
            $pg = $og->templateCombo($combo);
        }

        //parametros para el crear el combo del origen
        if (!is_null($og->comboValorUnico)) {
            $objGto->pg_id = $og->comboValorUnico;

            switch ($objGto->filtro) {
                case 'costeo': //la consulta de la partida especifica ya esta armada en el switch anterior
                    break;
                case 'ModNomPartida':
                    $claves = claveFinRecursoFederal(true);
                    $unidades = ['541', '530', '540', '531']; // 2016
                    // $unidades=array("305","430","439","431"); 2015
                    $ex = '';
                    if (in_array($objGto->ecf_id, $claves) && in_array($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'], $unidades)) {
                        $ex = ' AND (pg_id,pe_id) NOT IN ((411,167),(411,168),(411,169),(411,170),(411,171),(411,264),(411,417))';
                    }

                    $peSql = '
                        SELECT DISTINCT pe_id, pe_nombre
                        FROM cat_PartidaEspecifica
                        INNER JOIN presupuestoVigente USING(pg_id,pe_id)
                        WHERE pg_id = ' . $objGto->pg_id . '
                        AND gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                        ' . (isset($objGto->epg_id) ? 'AND epg_id=' . $objGto->epg_id : ' ') . '
                        ' . (isset($objGto->ecf_id) ? 'AND ecf_id=' . $objGto->ecf_id : ' ') . '
                        AND pre_vigente!=0';
                    $peSql .= $ex;
                    if (isset($objGto->lectura) && $objGto->lectura == true) {
                        $peSql = '
                            SELECT DISTINCT pe_id, pe_nombre
                            FROM cat_PartidaEspecifica
                            WHERE pe_id = ' . $objGto->pe_id . ' AND pg_id = ' . $objGto->pg_id . '
                            ';
                    }
                    break;
                case 'modificaciones': //Adecuaciones presupuestales
                    $peSql = '
                        SELECT DISTINCT pe_id, pe_nombre
                        FROM cat_PartidaEspecifica
                        WHERE pg_id = ' . $objGto->pg_id . '
                        ' . (isset($objGto->pe_id) ? 'AND pe_id=' . $objGto->pe_id : ' ') . ';';
                    break;
                default:
                    $peSql = '
                        SELECT DISTINCT pe_id, pe_nombre
                        FROM cat_PartidaEspecifica
                        WHERE pg_id = ' . $objGto->pg_id . ' ';
                    break;
            }
        }

        $combo = new stdClass();
        $combo->id = 'pe_id';
        $combo->label = 'Partida Específica';
        $combo->mensaje = 'Lista de Partida Específica';
        $combo->search = true;
        $combo->required = true;
        $combo->detonar = (isset($objGto->detonar) && is_bool($objGto->detonar)) ? $objGto->detonar : false;

        if (isset($objGto->changePe)) {
            $combo->change = $objGto->changePe;
        }

        $combo->enDiv = true;

        if (isset($objGto->required)) {
            $combo->required = $objGto->required;
        }

        if (isset($objGto->form)) {
            $combo->required = $objGto->form;
        }

        if ($peSql != '') {

            if (isset($objGto->pe_id)) {
                $combo->selecciona = $objGto->pe_id;
            }

            $combo->sql = $peSql;

            $pe = $og->ponCombo($combo);

        } else {
            $pe = $og->templateCombo($combo);
        }

        return '
            <legend>Objeto del Gasto</legend>
            <div class="row">
                <fieldset class="col-md-6 col-xs-6" id="objGto">
                ' . $pg . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6" id="objGto">
                ' . $pe . '
                <div id="combosUg"></div>
                </fieldset>
            </div>
            <script language="javascript" >
            ' . $og->getScript() . '
            </script>';
    } // fin ponObjetoGasto

    private function cargaPg()
    {
        $lectura = false;
        if (isset($this->prm->lectura) && $this->prm->lectura === true) {
            $lectura = true;
        }

        $combo = new stdClass();
        $combo->id = 'pg_id';

        if ($lectura === true) {
            $combo->sql = 'SELECT DISTINCT pg_id, pg_nombre FROM cat_PartidaGenerica pg '
            . ' WHERE pg_id = ' . $this->prm->pg_id;
        } else {
            switch ($this->prm->filtro) {
                case '':
                    break;
                case 'modificaciones':
                    $where1 = '
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo']
                        . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur']
                        . ' AND ue_id=' . (isset($_SESSION[_S]['clasAdm-ue']) && !empty($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 0)
                        . (isset($_SESSION[_S]['clasAdm-epg']) && !is_null($_SESSION[_S]['clasAdm-epg']) && $_SESSION[_S]['clasAdm-epg'] !== 0 ?
                        ' AND epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '' : '')
                        . (isset($this->prm->ecf_id) && !empty($this->prm->ecf_id) ?
                        ' AND ecf_id=' . $this->prm->ecf_id . '' : '') . '
                    ';
                    $where1 .= (
                        isset($this->prm->tcp_id) && $this->prm->tcp_id != '' ?
                        ' AND tcp_id="' . $this->prm->tcp_id . '" '
                        : ''
                    );
                    $where2 = '
                        WHERE m.mod_gpo_id=' . $_SESSION[_S]['clasAdm-gpo']
                        . ' AND m.mod_ur_id=' . $_SESSION[_S]['clasAdm-ur']
                        . (isset($_SESSION[_S]['clasAdm-epg']) && !is_null($_SESSION[_S]['clasAdm-epg']) && $_SESSION[_S]['clasAdm-epg'] !== 0 ?
                        ' AND epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '' : '')
                        . (isset($this->prm->ecf_id) && !empty($this->prm->ecf_id) ?
                        ' AND m.ecf_id=' . $this->prm->ecf_id . '' : '');
                    $where2 .= (isset($this->prm->tcp_id) && $this->prm->tcp_id != '' ?
                        ' AND tcp_id="' . $this->prm->tcp_id . '" ' : '');

                    if (!isset($this->prm->mmd_id)) {
                        $this->prm->mmd_id = '';
                    }

                    // Separación para mostrar las partidas de acuerdo a clasess de gastos
                    $clg = PartidasporClaseGastos($this->prm->omo_id, $this->prm->tfm_id, $this->prm->mmd_id, $this->prm->tmd_id);
                    $usar = AgrupacionSql($this->prm->tmd_id, $this->prm->tipoMovimiento);



                    $sql = "
                        SELECT DISTINCT p.pg_id, pg_nombre, clg_id
                        FROM
                        (
                            SELECT
                                DISTINCT  gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id,ecf_id, tcp_id
                            FROM presupuesto
                            $where1
                        ) as p
                        INNER JOIN cat_PartidaGenerica pg using(pg_id)
                        INNER JOIN cat_PartidaEspecifica pe using(pg_id,pe_id)
                        INNER JOIN enc_PartGenUr using(gpo_id,ur_id,pg_id)
                        LEFT JOIN
                        (
                            select
                                DISTINCT  m.mod_gpo_id, m.mod_ur_id, gpo_id, ur_id,ue_id, epg_id,
                                pg_id, pe_id, m.ecf_id, tcp_id
                            FROM scp_MaestroModificacion m
                                JOIN scp_DetalleModificacion d using(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                            $where2 AND emo_id NOT IN (14,15,16)
                        ) as t $usar
                        WHERE $clg t.pe_id is null ORDER BY pg_id;";
                    $combo->sql = $sql;
                    break;
            }
        }
        $this->recargaCombo($combo);
        if (is_null($this->comboValorUnico)) {
            $_SESSION[_S]['objGto']->filtro = 'modificaciones';
            $this->prm->pg_id = 0;
        } else {
            $_SESSION[_S]['objGto']->filtro = 'modificaciones';
            $this->prm->pg_id = $this->comboValorUnico;
        }
        $this->cambioPg();
    }

    private function cambioPg()
    {
        $this->setContenedor('pe_id');
        $_SESSION[_S]['objGto']->pg_id = (empty($this->prm->pg_id)) ? 0 : $this->prm->pg_id;
        $_SESSION[_S]['objGto']->pe_id = null;

        $this->cargaPe();
        $_SESSION[_S]['objGto']->pg_id = (empty($this->prm->pg_id)) ? null : $this->prm->pg_id;
    }

    private function cargaPe()
    {
        $combo = new stdClass();
        $combo->id = 'pe_id';
        $filtro = (isset($_SESSION[_S]['objGto']->filtro)) ? $_SESSION[_S]['objGto']->filtro : '';

        switch ($filtro) {
            case 'ModNomPartida':
                $unidades = ['541', '530', '540', '531']; // 2016
                //$unidades=array("305","430","439","431"); //2015
                $ex = '';
                if (in_array($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'], $unidades)) {
                    $ex = ' AND (pg_id,pe_id) NOT IN ((411,167),(411,168),(411,169),(411,170),(411,171),(411,264),(411,417))';

                }
                $combo->sql = '
                    SELECT DISTINCT pe_id,pe_nombre FROM cat_PartidaEspecifica
                    JOIN  presupuestoVigente USING(pg_id,pe_id)
                    WHERE pg_id = ' . $_SESSION[_S]['objGto']->pg_id . '
                        ' . (isset($_SESSION[_S]['objGto']->pe_id) ? ' AND pe_id=' . $_SESSION[_S]['objGto']->pe_id : '') . '
                    AND gpo_id =' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id =' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND pre_vigente!=0 ' . $ex . '
                    ORDER by pe_id;';
                break;
            case 'otraspartidas':
                $combo->sql = 'SELECT DISTINCT pe_id,pe_nombre FROM cat_PartidaEspecifica WHERE pg_id='
                . $_SESSION[_S]['objGto']->pg_id . ' AND pe_Cedula = 2 ORDER by pe_id;';
                break;
            case 'costeo':
                //ccd_id el tipo de cedula
                //cve_id cuando se divide en varias( arrendamiento, telefonia, vehiculos )
                if (isset($this->prm->ccd_id)) {
                    if ($this->prm->ccd_id != 9 && $this->prm->ccd_id != 2) {
                        $combo->sql = '
                            SELECT DISTINCT pe_id, pe_nombre
                            FROM enc_PartEspCed
                            INNER JOIN enc_PartGenUr USING( pg_id )
                            INNER JOIN cat_PartidaEspecifica USING( pg_id, pe_id )
                            WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo']
                        . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur']
                        . ' AND ccd_id = ' . $this->prm->ccd_id
                        . (isset($this->prm->cve_id) ? ' AND cve_id = ' . $this->prm->cve_id : '')
                        . ' AND pg_id = ' . $_SESSION[_S]['objGto']->pg_id . '';
                        $combo->sql .= isset($this->prm->lectura) ? ' AND pe_id = ' . $this->prm->pe_id : '';
                        $combo->sql .= ' ORDER by pe_id';

                    } else {
                        $combo->sql = '
                            SELECT DISTINCT pe_id, pe_nombre
                            FROM cat_PartidaGenerica
                            INNER JOIN cat_PartidaEspecifica e USING( pg_id )
                            INNER JOIN enc_PartGenUr USING( pg_id )
                            LEFT JOIN (
                                SELECT pg_id, pe_id
                                FROM enc_PartEspCed
                                GROUP BY pg_id, pe_id
                            )c USING( pg_id, pe_id )
                            WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                            AND tno_id = 0
                            AND c.pg_id IS NULL
                            AND pg_id = ' . $_SESSION[_S]['objGto']->pg_id . '
                            ';
                        $combo->sql .= isset($this->prm->lectura) ?
                        ' AND e.pg_id = ' . $this->prm->pg_id . ' AND e.pe_id = ' . $this->prm->pe_id
                        : '';
                        $combo->sql .= ' ORDER by pe_id';
                    }
                }
                break;
            case 'modificaciones':
                if (isset($this->prm->lectura)) {
                    $combo->sql = '
                        SELECT DISTINCT pe_id,pg_id, pe_nombre
                        FROM cat_PartidaEspecifica pe WHERE pe_id = '
                    . $this->prm->pe_id . ' AND pg_id = ' .
                    $this->prm->pg_id . ' ORDER by pe_id';
                } elseif (isset($_SESSION[_S]['objGto']->pg_id)) {
                    $where1 = '
                        WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo']
                        . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur']
                        . ' AND ue_id = ' . (!empty($_SESSION[_S]['clasAdm-ue']) ?
                        $_SESSION[_S]['clasAdm-ue'] : 0)
                        . (isset($_SESSION[_S]['clasAdm-epg']) && !is_null($_SESSION[_S]['clasAdm-epg']) && $_SESSION[_S]['clasAdm-epg'] !== 0 ?
                        ' AND epg_id = ' . $_SESSION[_S]['clasAdm-epg'] . '' : '')
                        . ' AND pg_id = ' . (!empty($_SESSION[_S]['objGto']->pg_id) ?
                        $_SESSION[_S]['objGto']->pg_id : 'null')
                        . (isset($this->prm->ecf_id) && !empty($this->prm->ecf_id) ? ' AND ecf_id = ' . $this->prm->ecf_id : '');

                    $where1 .= (
                        isset($_SESSION[_S]['objGto']->tcp_id)
                        && $_SESSION[_S]['objGto']->tcp_id != '' ?
                        ' AND tcp_id = "' . $_SESSION[_S]['objGto']->tcp_id . '" '
                        : ''
                    );
                    $where2 = ' WHERE m.mod_gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND m.mod_ur_id = '
                        . $_SESSION[_S]['clasAdm-ur'] . ' AND epg_id = ' . (!empty($_SESSION[_S]['clasAdm-epg']) ?
                        $_SESSION[_S]['clasAdm-epg'] : 'null') . ' AND pg_id = '
                        . (!empty($_SESSION[_S]['objGto']->pg_id) ? $_SESSION[_S]['objGto']->pg_id : 'null')
                        . (isset($this->prm->ecf_id) && !empty($this->prm->ecf_id) ?
                        ' AND m.ecf_id=' . $this->prm->ecf_id . '' : '');
                    $where2 .= (
                        isset($_SESSION[_S]['objGto']->tcp_id) && $_SESSION[_S]['objGto']->tcp_id != '' ?
                        ' AND tcp_id = "' . $_SESSION[_S]['objGto']->tcp_id . '" '
                        : ''
                    );
                    #$idp2 = '';
                    if (!isset($this->prm->tfm_id)) {
                        $this->prm->tfm_id = '';
                    }
                    if (!isset($this->prm->mmd_id)) {
                        $this->prm->mmd_id = '';
                    }
                    if (!isset($this->prm->tmd_id)) {
                        $this->prm->tmd_id = '';
                    }

                    $clg = PartidasporClaseGastos($this->prm->omo_id, $this->prm->tfm_id, $this->prm->mmd_id, $this->prm->tmd_id);
                    $usar = AgrupacionSql($this->prm->tmd_id, $this->prm->tipoMovimiento);

                    /*switch ($this->prm->tfm_id) {
                    case 'A':
                    $idp2 = " (idp_id = 'A' or pe.pe_id in(394,395,396,397,398,399,411)) and ";
                    break;
                    case 'B':
                    $idp2 = "idp_id in ('B','C','D','E','F','H','J','K','R','S','T','Y','N','P','U','X') "
                    ." and pe.pe_id NOT IN(394,395,396,397,398,399,411) AND ";
                    break;
                    case 'C':
                    if (!isset($this->prm->mmd_id)) $this->prm->mmd_id = '';
                    switch ($this->prm->mmd_id.$this->prm->tmd_id) {
                    case 'IC':  // Calendario interno de inversión, unicamente idp M,X
                    $idp2 = " idp_id in ('M','X') and ";
                    break;
                    default:
                    $idp2 = " idp_id in ('M','V','X') and ";
                    break;
                    }
                    break;
                    }

                    if (!isset($this->prm->tmd_id)) {
                    $this->prm->tmd_id = '';
                    }
                    switch ($this->prm->tmd_id) {
                    case 'B':
                    if ($this->prm->tipoMovimiento=='A') {
                    $usar = "USING( gpo_id, ur_id,epg_id, pg_id, pe_id, ecf_id)";
                    } else {
                    $usar = "USING( gpo_id, ur_id,epg_id, pg_id, pe_id, ecf_id,tcp_id)";
                    }
                    break;
                    default:
                    $usar = "USING( gpo_id, ur_id,epg_id, pg_id, pe_id, ecf_id,tcp_id)";
                    break;
                    }*/

                    $combo->sql = "
                        SELECT DISTINCT (p.pe_id), pe_nombre
                        FROM
                        (
                            SELECT
                                DISTINCT  gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id,ecf_id, tcp_id
                            FROM presupuesto
                            $where1
                        ) as p
                        INNER JOIN cat_PartidaEspecifica pe USING(pg_id,pe_id)
                        INNER JOIN enc_PartGenUr USING(gpo_id,ur_id,pg_id)
                        LEFT JOIN
                        (
                            SELECT
                                DISTINCT  m.mod_gpo_id, m.mod_ur_id, gpo_id, ur_id, ue_id,
                                epg_id, pg_id, pe_id, m.ecf_id, tcp_id
                            FROM scp_MaestroModificacion m
                                INNER JOIN scp_DetalleModificacion d USING(omo_id,mod_gpo_id,mod_ur_id,mod_id)
                            $where2 AND emo_id NOT IN (14,15,16)
                        ) as t USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        WHERE $clg t.pe_id IS NULL ORDER BY pe_id;";
                }
                break;
            default:
                $combo->sql = 'SELECT DISTINCT pe_id,pe_nombre
                    FROM cat_PartidaEspecifica WHERE pg_id = '
                . $_SESSION[_S]['objGto']->pg_id . ' ORDER BY pe_id;';
                break;
        }
        $this->recargaCombo($combo);
    }

    private function cambioPe()
    {
        $_SESSION[_S]['objGto']->pe_id = $this->prm->pe_id;
    }
}

//-------------------------------//
if (isset($_POST['metodoObjGto'])) {
    new ObjetoGasto();
}
