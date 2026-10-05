<?php

declare (strict_types=1);

require_once dirname(__FILE__) . '/../lib/sessionSafeStart.php';
sessionSafeStart();

if ($_SERVER['SERVER_ADDR'] == '172.17.90.88') {
    /* PRODUCCION  */
    error_reporting(E_ERROR);
} else {
    /* DESARROLLO  */
    error_reporting(E_ALL);
}

require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../lib/funciones.php';

class ClasificacionAdministrativa extends WebResponse
{
    // Si es para el POA pedimos todas las UEs que tengan definidas Obras/Actividades y que tengan el status de VALIDADA ( ( (toa_id = 1 AND eoa_id = 7) or (toa_id = 2 AND eoa_id = 6) ) )
    /*antes eoa_id=7*//*antes eoa_id=6 */
    public $estatusObra = ' and ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) ';
    public function __construct()
    { //Constructor
        parent::__construct();
        $metodo = (isset($this->prm->metodoClasAdmin)) ? $this->prm->metodoClasAdmin : '';
        switch ($metodo) {
            case 'cambioUr':
                $this->cambiaUr();
                break;
            case 'cambioUe':
                $this->cambioUe();
                break;
            case 'cambioPrg':
                $this->cambiaPrg();
                break;
            case 'cambioSpr':
                $this->cambiaSpr();
                break;
            case 'cambioPy':
                $this->cambiaPy();
                break;
            case 'cambioOa':
                $this->cambioOa();
                break;
            case 'limpiaClasAdmin':
                $this->limpiaClasAdmin();
                break;
        }
        $this->returnJson();
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    PONE A NULOS TODOS LOS VALORES DE LA CLASIFICACIÓN ADMINISTRATIVA Y LA VUELVE A DIBUJAR
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function limpiaClasAdmin()
    {

        // Determinamos si en la carga de trabajo tiene una UE definida ...
        // ¿ qué pasa si simplemente le asigno NULL a la variable de ue ?
        $ca = new WebResponse();
        $ca->setSql('
            SELECT car_id, ue_id
            FROM cat_CargaTrabajo
            WHERE usr_id = ' . $_SESSION[_S]['usr_id'] . '
            ORDER BY ue_id DESC
            LIMIT 1
        ');
        $ca->conectar();
        $ca->consultar();
        $ca->desconectar();

        if ($carga = $ca->extraer()) {
            if ($carga['ue_id'] == '' && $_SESSION[_S]['nvj_id'] > 1) {
                $_SESSION[_S]['clasAdm-ue'] = null;
            }
        }

        $_SESSION[_S]['clasAdm-prg'] = null;
        $_SESSION[_S]['clasAdm-spr'] = null;
        $_SESSION[_S]['clasAdm-py'] = null;
        $_SESSION[_S]['clasAdm-oa'] = null;
        $_SESSION[_S]['clasAdm-epg'] = 0;
        $this->mixScript(
            $this->onChangeUr() .
            (($_SESSION[_S]['nvj_id'] == 1) ? '' : '$("#clasadminue").empty();') . '
            $("#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
        '
        );
    }
    /*---------------------------------------------------------------------------------------------------------------------------
    Funciones de cambio en el combo
    ----------------------------------------------------------------------------------------------------------------------------*/
    private function cambiaUr()
    {
        $_SESSION[_S]['clasAdm-gpo'] = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : null;
        $_SESSION[_S]['clasAdm-ur'] = (isset($this->prm->ur_id)) ? $this->prm->ur_id : null;
        if (isset($this->prm->ue) && $this->prm->ue == 1) {
            $this->cargaUe();
        } elseif (isset($this->prm->pr) && $this->prm->pr == 1) {
            $this->cargaPrg();
        }
    }

    private function cambioUe()
    {
        $_SESSION[_S]['clasAdm-ue'] = ($this->prm->ue_id != '') ? $this->prm->ue_id : null;
        $filtro = (isset($this->prm->filtro) && $this->prm->filtro != '') ? $this->prm->filtro : '';
        if (isset($this->prm->pr) && $this->prm->pr == 1) {
            $this->cargaPrg($filtro != '');
        } elseif (isset($this->prm->py) && $this->prm->py == 1) {
            $this->cargaPy($filtro != '');
        }
    }

    private function cambiaPrg()
    {
        $_SESSION[_S]['clasAdm-prg'] = ($this->prm->prg_id != '') ? $this->prm->prg_id : null;
        if ($this->prm->sp == 1) {
            $this->cargaSpr();
        };
    }

    private function cambiaSpr()
    {
        $_SESSION[_S]['clasAdm-spr'] = ($this->prm->spr_id != '') ? $this->prm->spr_id : null;
        if ($this->prm->py == 1) {
            $this->cargaPy();
        }
    }

    private function cambiaPy()
    {
        $_SESSION[_S]['clasAdm-py'] = ($this->prm->py_id != '') ? $this->prm->py_id : null;
        if ($this->prm->oa == 1) {
            $this->cargaOa();
        }
    }

    private function cambioOa()
    {
        if ($this->prm->oa_id != '' && $this->prm->oa_id != null) {
            $_SESSION[_S]['clasAdm-oa'] = $this->prm->oa_id;
            $epg = ClasificacionAdministrativa::getEpg();
            if ($epg > 0) {
                $_SESSION[_S]['clasAdm-epg'] = $epg;
                $this->conectar();
                $this->setSql('SELECT REPLACE(oa_nombre ,\'"\',"") oa_nombre FROM cat_ObraActividad JOIN enc_Programatico USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id ) WHERE epg_id = ' . $epg);
                $this->consultar();
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $oaNombre = $this->extraer();
                    // Marcos: Otros valores que se graban al seleccionar un encuadre ...
                    // Y si seleccionan un nuevo encuadre programático ... limpiamos el encuadre de la clave de financiamiento
                    $this->mixScript('
                        if(typeof(progID) != "undefined"){
                            $("#" + progID  ).attr( "enc", "' . $_SESSION[_S]['clasAdm-epg'] . '");
                            $("#" + progID  ).attr( "ori", "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . $_SESSION[_S]['clasAdm-ue'] . '-' . $_SESSION[_S]['clasAdm-prg'] . '-' . $_SESSION[_S]['clasAdm-spr'] . '-' . $_SESSION[_S]['clasAdm-py'] . '-' . $_SESSION[_S]['clasAdm-oa'] . '" );
                            $("#encCveFin").val( "" );
                            $("#encCveFin").attr( "ori", "" );
                            $("#encCveFin").attr( "enc", "" );
                            $("#nomCveFin").val( "" );

                            $("#" + progID).val("' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . $_SESSION[_S]['clasAdm-ue'] . '-' . $_SESSION[_S]['clasAdm-prg'] . '-' . $_SESSION[_S]['clasAdm-spr'] . '-' . $_SESSION[_S]['clasAdm-py'] . '-' . $_SESSION[_S]['clasAdm-oa'] . '");
                            $("#" + progNom).val("' . preg_replace("/\r\n+|\r+|\n+|\t+/i", '', $oaNombre['oa_nombre']) . '");
                            if( $("#combooa").val() ) $("#" + progNom).val( $("#combooa").find("option:selected").text() );
                        }
                    ');
                }
            }
        } else {
            // Marcos: Otros valores que se graban al seleccionar un encuadre ...
            // Y si seleccionan un nuevo encuadre programático ... limpiamos el encuadre de la clave de financiamiento
            $this->mixScript('
                if(typeof(progID) != "undefined"){
                    $("#" + progID  ).attr( "enc", "");
                    $("#" + progID  ).attr( "ori", "" );
                    $("#encCveFin").val( "" );
                    $("#encCveFin").attr( "ori", "" );
                    $("#encCveFin").attr( "enc", "" );
                    $("#nomCveFin").val( "" );
                    $("#" + progID).val("");
                    $("#" + progNom).val("");
                }
            ');
        }
    }
    /*------------------------------------------------------------------------------------------------------------------------------
    Cargamos el combo de UEs
    -------------------------------------------------------------------------------------------------------------------------------*/
    private function cargaUe()
    {
        $filtro = (isset($this->prm->filtro) && $this->prm->filtro != '') ? $this->prm->filtro : '';
        $_SESSION[_S]['clasAdm-ue'] = null;
        $ue = new stdClass();
        $ue->id = 'comboue';
        switch ($filtro) {

            case 'poa':
                $ue->sql = '
                    SELECT DISTINCT ue_id,ue_nombre
                    FROM cat_UnidadEjecutora
                    JOIN cat_ObraActividad
                    USING(gpo_id,ur_id,ue_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . $this->estatusObra . '
                    ORDER BY ue_id
                ';
                break;
                // Si piden éste filtro, cargamos todas las UEs que hayan sido utilizadas para armar partidas presupuestales
            case 'porUrClaveFinanciamiento':
                $ue->sql = '
                    SELECT ue_id,ue_nombre
                    FROM cat_UnidadEjecutora
                    INNER JOIN presupuesto using(gpo_id,ur_id,ue_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . ' ';

                if (isset($this->prm->ecf_id)) {
                    $ue->sql .= ' and ecf_id=' . $this->prm->ecf_id . ' ';
                }

                if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 315 && isset($this->prm->ue_id)) {
                    $ue->sql .= ' and ue_id=' . $this->prm->ue_id . ' ';
                }

                $ue->sql .= ' GROUP BY gpo_id,ur_id,ue_id  ORDER BY ue_id';
                break;
                // Si piden éste filtro, mostramos todas las UEs que tengan definida carga de trabajo ( se usa actualmente sólo para la 315 )
            case 'ueXcarga':
                $ue->sql = '
                    SELECT ue_id, ue_nombre
                    FROM cat_UnidadEjecutora
                        INNER JOIN cat_UnidadResponsable USING( gpo_id, ur_id )
                        INNER JOIN cat_Grupo USING(gpo_id)
                        INNER JOIN cat_CargaTrabajo c USING( gpo_id, ur_id, ue_id )
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND c.usr_id=' . $_SESSION[_S]['usr_id'] . '
                    ORDER BY ue_id
                ';
                $ue->comoAtr = 'ue_id';
                $ue->mensaje = false;
                break;
                // Si no piden ningún filtro, cargamos TODAS las ejecutoras de la UR
            default:
                $ue->sql = '
                    SELECT ue_id,ue_nombre
                    FROM cat_UnidadEjecutora
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'];
                $ue->sql .= ' ORDER BY ue_id';
                break;
        }
        $ue->required = true;
        $ue->inputNoDiv = true;
        $ue->detonar = (isset($this->prm->pr) && !$this->prm->pr) ? true : false;
        $ue->textSinRegistro = ' - ';
        $this->recargaCombo($ue);
        $this->mixScript('$("#comboprg,#combosp,#combopy,#combooa").html("<option value=\'\'> - </option>");');

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['clasAdm-ue'] = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['clasAdm-ue'] = null;
        }

        if ($this->prm->pr == 1) {
            $this->cargaPrg();
        }
    }

    /*-------------------------------------------------------------------------------------------------------------------------
    Cargamos el combo de Programas
    ---------------------------------------------------------------------------------------------------------------------------*/
    private function cargaPrg($porUe = false)
    {
        $filtro = (isset($this->prm->filtro) && $this->prm->filtro != '') ? $this->prm->filtro : '';
        $_SESSION[_S]['clasAdm-prg'] = null;
        $prg = new stdClass();
        $prg->id = 'comboprg';
        switch ($filtro) {
            case 'poa':
                $prg->sql = 'SELECT DISTINCT prg_id,prg_nombre FROM cat_Programa JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id)
                WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' AND
                ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . $this->estatusObra . ' ';
                if (isset($this->prm->toa_id)) {
                    $prg->sql .= ' AND toa_id =' . $this->prm->toa_id;
                }
                if (isset($this->prm->oa_origen)) {
                    $prg->sql .= ' AND oa_origen ="' . $this->prm->oa_origen . '"';
                }
                if (isset($this->prm->oa_anticipo)) {
                    $prg->sql .= ' AND oa_anticipo >0';
                }
                break;
                // Si piden éste filtro, cargamos todos los programas que hayan sido utilizadas para armar partidas presupuestales
            case 'porUrClaveFinanciamiento':
                $prg->sql = '
                    SELECT prg_id,prg_nombre FROM cat_Programa
                    INNER JOIN presupuesto using(gpo_id,ur_id,prg_id)
                    where gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                    and ue_id=' . (($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S')) ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                    ' . (isset($this->prm->ecf_id) ? '
                    and ecf_id=' . $this->prm->ecf_id . ' ' : ' ') . '
                    GROUP BY gpo_id,ur_id,prg_id;';
                break;
                // Si no especifican ningún filtro ...
            default:
                // Si piden $porUe == true mostramos aquellos que tengan definidas Obras / Actividades, sin importar su status
                if ($porUe) {
                    $prg->sql = '
                    SELECT DISTINCT prg_id,prg_nombre
                    FROM cat_Programa JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' AND
                    ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . ' ';
                    // Si no, mostramos TOODS los programas de la UR
                } else {
                    $prg->sql = '
                    SELECT DISTINCT prg_id,prg_nombre
                    FROM cat_Programa
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ';
                }
                break;
        }
        $prg->required = true;
        $prg->inputNoDiv = true;
        $prg->detonar = (isset($this->prm->sp) && !$this->prm->sp) ? true : false;
        $prg->textSinRegistro = ' - ';
        $prg->class = 'clasAdminCombo';
        $this->recargaCombo($prg);
        $this->mixScript(' $("#combosp,#combopy,#combooa").html("<option value=\'\'> - </option>"); ');
        if (!is_null($this->comboValorUnico)) {

            $_SESSION[_S]['clasAdm-prg'] = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['clasAdm-prg'] = null;
        }

        if (isset($this->prm->sp) && $this->prm->sp == 1) {
            $this->cargaSpr($porUe);
        }
    }

    /*------------------------------------------------------------------------------------------------------------------------
    Cargamos el combo de Subprogramas
    --------------------------------------------------------------------------------------------------------------------------*/
    private function cargaSpr($porUe = false)
    {
        $filtro = (isset($this->prm->filtro) && $this->prm->filtro != '') ? $this->prm->filtro : '';

        $_SESSION[_S]['clasAdm-spr'] = null;
        $spr = new stdClass();
        $spr->id = 'combosp';

        $and = '';
        switch ($filtro) {
            case 'poa':
                $spr->sql = '
                SELECT DISTINCT spr_id,spr_nombre
                FROM cat_Subprograma
                JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id, spr_id)
                WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                AND ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . '
                AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . $this->estatusObra;
                if (isset($this->prm->toa_id)) {
                    $spr->sql .= ' AND toa_id =' . $this->prm->toa_id . ' AND spr_status = 1';
                }
                if (isset($this->prm->oa_origen)) {
                    $spr->sql .= ' AND oa_origen ="' . $this->prm->oa_origen . '"' . 'AND spr_status = 1';
                }
                if (isset($this->prm->oa_anticipo)) {
                    $spr->sql .= ' AND oa_anticipo > 0 AND spr_status = 1';
                }
                break;
                // Si piden éste filtro, cargamos todos los subprogramas que hayan sido utilizadas para armar partidas presupuestales
                // SI PASAN UN ID PARA EL ENCUADRE CON LA CLAVE DE FINANCIAMIENTO, FILTRAMOS ADEMAS QUE LA CLAVE PRESUPUESTAL SEA DE DICHO ENCUADRE
            case 'porUrClaveFinanciamiento':
                $spr->sql = '
                    SELECT spr_id,spr_nombre
                    FROM cat_Subprograma
                    INNER JOIN presupuesto USING(gpo_id,ur_id,prg_id,spr_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                ';
                if (isset($this->prm->ecf_id)) {
                    $spr->sql .= ' AND ecf_id=' . $this->prm->ecf_id . ' AND spr_status = 1';
                }

                $spr->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id';

                break;
            case 'actividades': // Si piden el filtro PROYECTO, mostramos aquellos que su status sea igual a validado ( 4 )
            case 'proyecto':
                $and = ' and (esr_id = 4 OR (sprs_id = 0 AND esr_id!=4)) ';
                // no break
            default:
                // Si piden $porUe == true mostramos aquellos que tengan definidas Obras / Actividades, sin importar su status
                if ($porUe) {
                    $spr->sql = '
                        SELECT DISTINCT spr_id,spr_nombre
                        FROM cat_Subprograma s
                        INNER JOIN cat_Subprogramas ss ON ss.prgs_id = s.prg_id AND ss.sprs_id = s.spr_id
                        JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id)
                        INNER JOIN (
                            SELECT gpo_id,ur_id,prgs_id AS prg_id,sprs_id AS spr_id
                            FROM enc_UrSubprogramas
                            WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                            AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                            AND prgs_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                        )EN USING ( gpo_id,ur_id,prg_id,spr_id )
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ' . (isset($_SESSION[_S]['ue_id']) ? '
                        AND ue_id=' . $_SESSION[_S]['ue_id'] : ' AND ue_id=' . $_SESSION[_S]['clasAdm-ue']) . '
                        AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                        AND spr_status = 1
                    ';
                    // Si no, mostramos todos los subprogramas de la UR + programa actualmente seleccionado
                } else {
                    $spr->sql = '
                        SELECT spr_id,spr_nombre
                        FROM cat_Subprograma s
                        INNER JOIN cat_Subprogramas ss ON ss.prgs_id = s.prg_id AND ss.sprs_id = s.spr_id
                        INNER JOIN (
                            SELECT gpo_id,ur_id,prgs_id AS prg_id,sprs_id AS spr_id
                            FROM enc_UrSubprogramas
                            WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                            AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                            AND prgs_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                        )EN USING ( gpo_id,ur_id,prg_id,spr_id )
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                        AND spr_status = 1
                    ';
                }
                break;
        }
        $spr->sql .= $and;
        $spr->search = true;
        $spr->mensaje = 'Seleccionar un Subprograma';
        $spr->textSinRegistro = ' - ';
        $spr->required = true;
        $spr->inputNoDiv = true;
        $spr->class = 'clasAdminCombo';
        $spr->detonar = (isset($this->prm->py) && !$this->prm->py) ? true : false;
        $this->recargaCombo($spr);
        $this->mixScript('$("#combopy,#combooa").html("<option value=\'\'> - </option>");');

        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['clasAdm-spr'] = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['clasAdm-spr'] = null;
        }

        if (isset($this->prm->py) && $this->prm->py == 1) {
            $this->cargaPy($porUe);
        }
    }

    /*-------------------------------------------------------------------------------------------------------------------------
    Cargamos el combo de Proyectos
    ---------------------------------------------------------------------------------------------------------------------------*/
    private function cargaPy($porUe = false)
    {
        $filtro = (isset($this->prm->filtro) && $this->prm->filtro != '') ? $this->prm->filtro : '';

        $_SESSION[_S]['clasAdm-py'] = null;
        $py = new stdClass();
        $py->id = 'combopy';

        switch ($filtro) {
            case 'poa':
                $py->sql = '
                    SELECT DISTINCT py_id,py_nombre FROM cat_Proyecto JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id, spr_id, py_id )
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . $this->estatusObra;
                if (isset($this->prm->toa_id)) {
                    $py->sql .= ' AND toa_id =' . $this->prm->toa_id;
                }
                if (isset($this->prm->oa_origen)) {
                    $py->sql .= ' AND oa_origen ="' . $this->prm->oa_origen . '"';
                }
                if (isset($this->prm->oa_anticipo)) {
                    $py->sql .= ' AND oa_anticipo >0';
                }
                break;
                // Si piden éste filtro, mostramos todos los proyectos de la UR habilitados (sts_id=1) y validado (epy_id=6)
            case 'actividades':
                $py->sql = '
                    SELECT py_id, py_nombre 
                    FROM cat_Proyecto
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND prg_id=' . ((is_null($_SESSION[_S]['clasAdm-prg']) ? 'null' : $_SESSION[_S]['clasAdm-prg'])) . '
                    AND spr_id=' . ((is_null($_SESSION[_S]['clasAdm-spr']) ? 'null' : $_SESSION[_S]['clasAdm-spr'])) . '
                    AND sts_id=1 ';
                break;
                // Si piden éste filtro, cargamos todos los subprogramas que hayan sido utilizadas para armar partidas presupuestales
                // SI PASAN UN ID PARA EL ENCUADRE CON LA CLAVE DE FINANCIAMIENTO, FILTRAMOS ADEMAS QUE LA CLAVE PRESUPUESTAL SEA DE DICHO ENCUADRE
            case 'porUrClaveFinanciamiento':
                $py->sql = '
                    SELECT py_id,py_nombre
                    FROM cat_Proyecto
                    INNER JOIN presupuesto using(gpo_id,ur_id,prg_id,spr_id,py_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . ' ';
                if (isset($this->prm->ecf_id)) {
                    $py->sql .= ' and ecf_id=' . $this->prm->ecf_id . ' ';
                }

                $py->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id';
                break;
            case 'pyNoCero':
                $py->sql = '
                    SELECT py_id,py_nombre
                    FROM cat_Proyecto
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND
                    ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' AND
                    prg_id=' . ((is_null($_SESSION[_S]['clasAdm-prg']) ? 'null' : $_SESSION[_S]['clasAdm-prg'])) . ' AND
                    spr_id=' . ((is_null($_SESSION[_S]['clasAdm-spr']) ? 'null' : $_SESSION[_S]['clasAdm-spr'])) . ' AND
                    py_id != 0';
                break;
            default:
                // Si piden $porUe == true mostramos aquellos que tengan definidas Obras / Actividades, sin importar su status
                if ($porUe) {
                    $py->sql = '
                        SELECT DISTINCT py_id,py_nombre
                        FROM cat_Proyecto
                        JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id)
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND ue_id=' . $_SESSION[_S]['clasAdm-ue'] . '
                        AND prg_id=' . ((is_null($_SESSION[_S]['clasAdm-prg']) ? 'null' : $_SESSION[_S]['clasAdm-prg'])) . '
                        AND spr_id=' . ((is_null($_SESSION[_S]['clasAdm-spr']) ? 'null' : $_SESSION[_S]['clasAdm-spr'])) . '
                    ';
                    // Si no, mostramos todos los proyectos de la ur + programa + subprograma actualmente seleccionados
                } else {
                    $py->sql = '
                        SELECT py_id,py_nombre
                        FROM cat_Proyecto
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND prg_id=' . ((is_null($_SESSION[_S]['clasAdm-prg']) ? 'null' : $_SESSION[_S]['clasAdm-prg'])) . '
                        AND spr_id=' . ((is_null($_SESSION[_S]['clasAdm-spr']) ? 'null' : $_SESSION[_S]['clasAdm-spr'])) . '
                    ';
                }
                break;
        }

        $py->search = true;
        $py->mensaje = 'Seleccionar un Proyecto';
        $py->textSinRegistro = ' - ';
        $py->required = true;
        $py->detonar = (isset($this->prm->oa) && !$this->prm->oa) ? true : false;
        $py->inputNoDiv = true;
        $py->class = 'clasAdminCombo';
        $this->recargaCombo($py);
        $this->mixScript('$("#combooa").html("<option value=\'\'> - </option>");');
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['clasAdm-py'] = $this->comboValorUnico;
        } else {
            $_SESSION[_S]['clasAdm-py'] = null;
        }
        if (isset($this->prm->oa) && $this->prm->oa == 1) {
            $this->cargaOa($porUe);
        }
    }

    /*--------------------------------------------------------------------------------------------------------------------------
    Cargamos el combo de Obras / Actividades
    ----------------------------------------------------------------------------------------------------------------------------*/
    private function cargaOa($porUe = false)
    {
        $filtro = (isset($this->prm->filtro) && $this->prm->filtro != '') ? $this->prm->filtro : '';
        $_SESSION[_S]['clasAdm-oa'] = null;
        $oa = new stdClass();
        $oa->id = 'combooa';

        switch ($filtro) {
            case 'poa':
                $oa->sql = '
                    SELECT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre FROM cat_ObraActividad
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . '
                    AND py_id=' . (!is_null($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : 'null') . $this->estatusObra;
                if (isset($this->prm->toa_id)) {
                    $oa->sql .= ' AND toa_id =' . $this->prm->toa_id;
                }
                if (isset($this->prm->oa_origen)) {
                    $oa->sql .= ' AND oa_origen ="' . $this->prm->oa_origen . '"';
                }
                if (isset($this->prm->oa_anticipo)) {
                    $oa->sql .= ' AND oa_anticipo-oa_amortizacion >0';
                }
                break;
                // Si piden éste filtro, cargamos todos los subprogramas que hayan sido utilizadas para armar partidas presupuestales
                // SI PASAN UN ID PARA EL ENCUADRE CON LA CLAVE DE FINANCIAMIENTO, FILTRAMOS ADEMAS QUE LA CLAVE PRESUPUESTAL SEA DE DICHO ENCUADRE
                // SI PASAN UN ID PARA EL TIPO DE OBRA / ACTIVIDAD, TAMBIEN FILTRAMOS POR DICHO VALOR
            case 'porUrClaveFinanciamiento':
                $oa->sql = 'SELECT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre FROM cat_ObraActividad INNER JOIN presupuesto using(gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
                where gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . $this->estatusObra . ' AND
                prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . ' AND
                spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . ' AND
                py_id=' . (!is_null($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : 'null') . ' ';
                if (isset($this->prm->ecf_id)) {
                    $oa->sql .= ' and ecf_id=' . $this->prm->ecf_id . ' ';
                }

                if (isset($this->prm->toa_id)) {
                    $oa->sql .= ' and toa_id=' . $this->prm->toa_id . ' ';
                }

                $oa->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id,ue_id,py_id,oa_id';
                break;
                // Si no pasan ningún filtro, mostramos todas las Obras / Actividades de la ur + programa + subprograma + proyecto
                // + ue si tuviera algún valor la variable clasAdm-ue
            default:
                $oa->sql = '
                    SELECT oa_id,REPLACE(oa_nombre ,\'"\',"")oa_nombre
                    FROM cat_ObraActividad
                    WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND
                            ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' AND
                            prg_id = ' . ((is_null($_SESSION[_S]['clasAdm-prg'])) ? 'null' : $_SESSION[_S]['clasAdm-prg']) . ' AND
                            spr_id = ' . ((is_null($_SESSION[_S]['clasAdm-spr'])) ? 'null' : $_SESSION[_S]['clasAdm-spr']) . ' AND
                            py_id = ' . ((is_null($_SESSION[_S]['clasAdm-py'])) ? 'null' : $_SESSION[_S]['clasAdm-py']) . '  ';

                $oa->sql .= ' AND ue_id = ' . ((!is_null($_SESSION[_S]['clasAdm-ue'])) ? $_SESSION[_S]['clasAdm-ue'] : 'null');
                break;
        }
        $oa->search = true;
        $oa->mensaje = 'Seleccionar una Obra/actividad';
        $oa->textSinRegistro = ' - ';
        $oa->required = true;
        $oa->detonar = true;
        $oa->inputNoDiv = true;
        $oa->class = 'clasAdminCombo';
        $this->recargaCombo($oa);
        if (!is_null($this->comboValorUnico)) {
            $_SESSION[_S]['clasAdm-oa'] = $this->comboValorUnico;
            $_SESSION[_S]['clasAdm-epg'] = ClasificacionAdministrativa::getEpg();
            $this->conectar();
            $this->setSql('SELECT REPLACE(oa_nombre ,\'"\',"") oa_nombre FROM cat_ObraActividad JOIN enc_Programatico USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id ) WHERE epg_id = ' . $_SESSION[_S]['clasAdm-epg']);
            $this->consultar();
            $this->desconectar();
            $oaNombre = $this->extraer();
            $this->mixScript('
                if(typeof(progID) != "undefined"){

                    // Marcos: Otros valores que se graban al seleccionar un encuadre ...
                    $("#" + progID  ).attr( "enc", "' . $_SESSION[_S]['clasAdm-epg'] . '");
                    $("#" + progID  ).attr( "ori", "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . $_SESSION[_S]['clasAdm-ue'] . '-' . $_SESSION[_S]['clasAdm-prg'] . '-' . $_SESSION[_S]['clasAdm-spr'] . '-' . $_SESSION[_S]['clasAdm-py'] . '-' . $_SESSION[_S]['clasAdm-oa'] . '" );
                    // Y si seleccionan un nuevo encuadre programático ... limpiamos el encuadre de la clave de financiamiento
                    $("#encCveFin").val( "" );
                    $("#encCveFin").attr( "ori", "" );
                    $("#encCveFin").attr( "enc", "" );
                    $("#nomCveFin").val( "" );

                    $("#" + progID).val("' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . $_SESSION[_S]['clasAdm-ue'] . '-' . $_SESSION[_S]['clasAdm-prg'] . '-' . $_SESSION[_S]['clasAdm-spr'] . '-' . $_SESSION[_S]['clasAdm-py'] . '-' . $_SESSION[_S]['clasAdm-oa'] . '");
                    $("#" + progNom).val("' . preg_replace("/\r\n+|\r+|\n+|\t+/i", '', $oaNombre['oa_nombre']) . '");
                    if( $("#combooa").val() ) $("#" + progNom).val( $("#combooa").find("option:selected").text() );
                }
            ');
        } else {
            $_SESSION[_S]['clasAdm-oa'] = null;
        }
    }

    public static function ActualizaCombos()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $porUe = true;
        $ca = new WebResponse();
        $filtro = '';
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $ue = new stdClass();
            $paramUe = [];
            $ue->id = 'comboue';
            switch ($filtro) {
                // Si es para el POA pedimos todas las UEs que tengan definidas Obras/Actividades y que tengan el status de VALIDADA ( ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) )
                case 'poa':
                    array_push($paramUe, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                    $ue->sql = '
                        SELECT DISTINCT ue_id,ue_nombre
                        FROM cat_UnidadEjecutora
                        JOIN cat_ObraActividad
                        USING(gpo_id,ur_id,ue_id)
                        WHERE gpo_id = ? AND ur_id = ? and ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) )
                        ORDER BY ue_id
                    ';
                    $ue->params = $paramUe;
                    break;
                    // Si piden éste filtro, cargamos todas las UEs que hayan sido utilizadas para armar partidas presupuestales
                case 'porUrClaveFinanciamiento':
                    $ue->sql = '
                        SELECT ue_id,ue_nombre
                        FROM cat_UnidadEjecutora
                        INNER JOIN presupuesto using(gpo_id,ur_id,ue_id)
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . ' ';

                    if (isset($prm->ecf_id)) {
                        $ue->sql .= ' AND ecf_id=' . $prm->ecf_id . ' ';
                    }

                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 315 && isset($prm->ue_id)) {
                        $ue->sql .= ' and ue_id=' . $prm->ue_id . ' ';
                    }
                    $ue->sql .= ' GROUP BY gpo_id,ur_id,ue_id  ORDER BY ue_id';
                    break;
                    // Si piden éste filtro, mostramos todas las UEs que tengan definida carga de trabajo ( se usa actualmente sólo para la 315 )
                case 'ueXcarga':
                    $ue->sql = '
                        SELECT ue_id, ue_nombre
                        FROM cat_UnidadEjecutora
                        INNER JOIN cat_UnidadResponsable USING( gpo_id, ur_id )
                        INNER JOIN cat_Grupo USING(gpo_id)
                        INNER JOIN cat_CargaTrabajo c USING( gpo_id, ur_id, ue_id )
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND c.usr_id=' . $_SESSION[_S]['usr_id'] . '
                        ORDER BY ue_id
                    ';
                    $ue->comoAtr = 'ue_id';
                    $ue->mensaje = false;
                    break;
                    // Si no piden ningún filtro, cargamos TODAS las ejecutoras de la UR
                default:
                    array_push($paramUe, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                    $ue->sql = '
                        SELECT ue_id,ue_nombre
                        FROM cat_UnidadEjecutora
                        WHERE gpo_id= ? AND ur_id = ?
                        ORDER BY ue_id
                    ';
                    $ue->params = $paramUe;
                    break;
            }
            $ue->detonar = false;
            $ue->selecciona = $_SESSION[_S]['clasAdm-ue'];
            $ue->textSinRegistro = ' - ';
            $ca->mixScript('$("#comboue,#comboprg,#combosp,#combopy,#combooa").html("<option value=\'\'> - </option>");');
            $ca->mixScript('$("#combour").val("' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '").selectpicker("refresh");');
            $ca->recargaCombo($ue);
        } else {
            $ca->mixScript('$("#comboue").val("' . $_SESSION[_S]['clasAdm-ue'] . '").selectpicker("refresh");');
        }
        $prg = new stdClass();
        $prg->id = 'comboprg';
        $paramsPrg = [];
        switch ($filtro) {
            // Si es para el POA pedimos todos los programas que tengan definidas Obras/Actividades y que tengan el status de VALIDADA ( ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) )
            case 'poa':
                $prg->sql = 'SELECT DISTINCT prg_id,prg_nombre FROM cat_Programa JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id)
                WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' AND
                ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . ' and ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) ';
                break;
                // Si piden éste filtro, cargamos todos los programas que hayan sido utilizadas para armar partidas presupuestales
            case 'porUrClaveFinanciamiento':
                $prg->sql = 'SELECT prg_id,prg_nombre FROM cat_Programa
                        INNER JOIN presupuesto USING(gpo_id,ur_id,prg_id)
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . ' AND
                        ue_id =
                        ' . (($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S'))
                    ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                    ' . (isset($prm->ecf_id) ? ' AND ecf_id=' . $prm->ecf_id . ' ' : ' ') . '
                    GROUP BY gpo_id,ur_id,prg_id;';
                break;
                // Si no especifican ningún filtro ...
            default:
                // Si piden $porUe == true mostramos aquellos que tengan definidas Obras / Actividades, sin importar su status
                array_push($paramsPrg, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                if ($porUe) {
                    $prg->sql = 'SELECT DISTINCT prg_id,prg_nombre 
                        FROM cat_Programa 
                        JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id)
                        WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?';
                    if (is_null($_SESSION[_S]['clasAdm-ue'])) {
                        array_push($paramsPrg, 'null');
                    } else {
                        array_push($paramsPrg, $_SESSION[_S]['clasAdm-ue']);
                    }
                } else {
                    // Si no, mostramos TOODS los programas de la UR
                    $prg->sql = 'SELECT DISTINCT prg_id,prg_nombre FROM cat_Programa
                    WHERE gpo_id = ? AND ur_id = ? ';
                }
                $prg->params = $paramsPrg;
                break;
        }
        $prg->detonar = false;
        $prg->selecciona = $_SESSION[_S]['clasAdm-prg'];
        $prg->textSinRegistro = ' - ';
        $ca->recargaCombo($prg);

        $spr = new stdClass();
        $spr->id = 'combosp';
        $and = '';
        $paramsSpr = [];
        switch ($filtro) {
            // Si es para el POA pedimos todos los proyectos que tengan definidas Obras/Actividades y que tengan el status de VALIDADA ( ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) )
            case 'poa':
                $spr->sql = '
                    SELECT DISTINCT spr_id,spr_nombre
                    FROM cat_Subprograma
                    JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id, spr_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND ((toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5)) ';
                break;
                // SI PIDEN ÉSTE FILTRO, CARGAMOS TODOS LOS SUBPROGRAMAS QUE HAYAN SIDO UTILIZADAS PARA ARMAR PARTIDAS PRESUPUESTALES
                // SI PASAN UN ID PARA EL ENCUADRE CON LA CLAVE DE FINANCIAMIENTO, FILTRAMOS ADEMAS QUE LA CLAVE PRESUPUESTAL SEA DE DICHO ENCUADRE
            case 'porUrClaveFinanciamiento':
                $spr->sql = '
                    SELECT spr_id,spr_nombre
                    FROM cat_Subprograma
                    INNER JOIN presupuesto using(gpo_id,ur_id,prg_id,spr_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                ';
                if (isset($prm->ecf_id)) {
                    $spr->sql .= ' and ecf_id=' . $prm->ecf_id . ' ';
                }
                $spr->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id';
                break;
            case 'actividades':
            case 'proyecto':
                // SI PIDEN EL FILTRO PROYECTO, MOSTRAMOS AQUELLOS QUE SU STATUS SEA IGUAL A VALIDADO ( 4 )
                $and = ' and (esr_id = 4 OR (sprs_id = 0 AND esr_id !=4))';
                // no break
            default:
                // SI PIDEN $PORUE == TRUE MOSTRAMOS AQUELLOS QUE TENGAN DEFINIDAS OBRAS / ACTIVIDADES, SIN IMPORTAR SU STATUS
                array_push($paramsSpr, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                if (!is_null($_SESSION[_S]['clasAdm-prg'])) {
                    array_push($paramsSpr, $_SESSION[_S]['clasAdm-prg']);
                } else {
                    array_push($paramsSpr, 'null');
                }
                if ($porUe) {
                    $spr->sql = '
                        SELECT DISTINCT spr_id,spr_nombre
                        FROM cat_Subprograma s
                        JOIN cat_Subprogramas ss ON ss.prgs_id = s.prg_id AND ss.sprs_id = s.spr_id
                        JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id)
                        WHERE 
                            gpo_id = ? AND ur_id = ? AND prg_id = ?
                            AND ue_id = ?
                    ';

                    if (isset($_SESSION[_S]['ue_id'])) {
                        array_push($paramsSpr, $_SESSION[_S]['ue_id']);
                    } else {
                        array_push($paramsSpr, $_SESSION[_S]['clasAdm-ue']);
                    }
                } else {
                    // SI NO, MOSTRAMOS TODOS LOS SUBPROGRAMAS DE LA UR + PROGRAMA ACTUALMENTE SELECCIONADO
                    $spr->sql = '
                        SELECT spr_id,spr_nombre
                        FROM cat_Subprograma s
                        JOIN cat_Subprogramas ss ON ss.prgs_id = s.prg_id AND ss.sprs_id = s.spr_id
                        WHERE 
                            gpo_id = ? AND ur_id = ? AND prg_id= ?
                    ';
                }
                $spr->params = $paramsSpr;
                break;
        }
        $spr->sql .= $and;
        $spr->search = true;
        $spr->mensaje = 'Seleccionar un Subprograma';
        $spr->textSinRegistro = ' - ';
        $spr->detonar = false;
        $spr->selecciona = $_SESSION[_S]['clasAdm-spr'];
        $ca->recargaCombo($spr);

        $py = new stdClass();
        $py->id = 'combopy';
        $paramsPy = [];
        switch ($filtro) {
            // Si es para el POA pedimos todos los proyectos que tengan definidas Obras/Actividades y que tengan el status de VALIDADA ( ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) )
            case 'poa':
                $py->sql = '
                    SELECT DISTINCT py_id,py_nombre
                    FROM cat_Proyecto
                    JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id, spr_id, py_id )
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . '
                    AND ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) ';
                break;
                // Si piden éste filtro, mostramos todos los proyectos de la UR habilitados (sts_id=1) y validado (epy_id=6)
            case 'actividades':
                $py->sql = '
                    SELECT py_id,py_nombre
                    FROM cat_Proyecto
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND prg_id=' . ((is_null($_SESSION[_S]['clasAdm-prg']) ? 'null' : $_SESSION[_S]['clasAdm-prg'])) . '
                    AND spr_id=' . ((is_null($_SESSION[_S]['clasAdm-spr']) ? 'null' : $_SESSION[_S]['clasAdm-spr'])) . '
                    AND sts_id=1 ';
                break;
                // SI PIDEN ÉSTE FILTRO, CARGAMOS TODOS LOS SUBPROGRAMAS QUE HAYAN SIDO UTILIZADAS PARA ARMAR PARTIDAS PRESUPUESTALES
                // SI PASAN UN ID PARA EL ENCUADRE CON LA CLAVE DE FINANCIAMIENTO, FILTRAMOS ADEMAS QUE LA CLAVE PRESUPUESTAL SEA DE DICHO ENCUADRE
            case 'porUrClaveFinanciamiento':
                $py->sql = '
                    SELECT py_id,py_nombre
                    FROM cat_Proyecto
                    INNER JOIN presupuesto using(gpo_id,ur_id,prg_id,spr_id,py_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . ' ';
                if (isset($prm->ecf_id)) {
                    $py->sql .= ' and ecf_id=' . $prm->ecf_id . ' ';
                }
                $py->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id';
                break;
            default:
                // SI PIDEN $PORUE == TRUE MOSTRAMOS AQUELLOS QUE TENGAN DEFINIDAS OBRAS / ACTIVIDADES, SIN IMPORTAR SU STATUS
                array_push($paramsPy, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                if (is_null($_SESSION[_S]['clasAdm-prg'])) {
                    array_push($paramsPy, 'null');
                } else {
                    array_push($paramsPy, $_SESSION[_S]['clasAdm-prg']);
                }
                if (is_null($_SESSION[_S]['clasAdm-spr'])) {
                    array_push($paramsPy, 'null');
                } else {
                    array_push($paramsPy, $_SESSION[_S]['clasAdm-spr']);
                }
                if ($porUe) {
                    $py->sql = '
                        SELECT DISTINCT py_id,py_nombre
                        FROM cat_Proyecto 
                        JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id)
                        WHERE 
                            gpo_id = ? AND ur_id = ?
                            AND prg_id= ?
                            AND spr_id= ?
                            AND ue_id = ?
                        ';
                    array_push($paramsPy, $_SESSION[_S]['clasAdm-ue']);
                } else {
                    // SI NO, MOSTRAMOS TODOS LOS PROYECTOS DE LA UR + PROGRAMA + SUBPROGRAMA ACTUALMENTE SELECCIONADOS
                    $py->sql = '
                        SELECT py_id,py_nombre
                        FROM cat_Proyecto
                        WHERE 
                            gpo_id = ? AND ur_id = ?
                            AND prg_id = ?
                            AND spr_id = ?
                        ';
                }
                $py->params = $paramsPy;
                break;
        }

        $py->search = true;
        $py->mensaje = 'Seleccionar un Proyecto';
        $py->textSinRegistro = ' - ';
        $py->detonar = false;
        $py->selecciona = $_SESSION[_S]['clasAdm-py'];
        $py->class = 'clasAdminCombo';
        $ca->recargaCombo($py);

        $oa = new stdClass();
        $oa->id = 'combooa';
        $paramsOa = [];
        switch ($filtro) {
            case 'poa':
                // Si es para el POA pedimos todos los proyectos que tengan definidas Obras/Actividades y que tengan el status de VALIDADA ( ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) )
                $oa->sql = '
                    SELECT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre
                    FROM cat_ObraActividad
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . '
                    AND py_id=' . (!is_null($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : 'null') . '
                    AND ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) ';
                break;
                // Si piden éste filtro, cargamos todos los subprogramas que hayan sido utilizadas para armar partidas presupuestales
                // SI PASAN UN ID PARA EL ENCUADRE CON LA CLAVE DE FINANCIAMIENTO, FILTRAMOS ADEMAS QUE LA CLAVE PRESUPUESTAL SEA DE DICHO ENCUADRE
                // SI PASAN UN ID PARA EL TIPO DE OBRA / ACTIVIDAD, TAMBIEN FILTRAMOS POR DICHO VALOR
            case 'porUrClaveFinanciamiento':
                $oa->sql = '
                    SELECT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre
                    FROM cat_ObraActividad
                    INNER JOIN presupuesto using(gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                    AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                    AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . '
                    AND py_id=' . (!is_null($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : 'null') . ' ';
                if (isset($prm->ecf_id)) {
                    $oa->sql .= ' and ecf_id=' . $prm->ecf_id . ' ';
                }

                if (isset($prm->toa_id)) {
                    $oa->sql .= ' and toa_id=' . $prm->toa_id . ' ';
                }

                $oa->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id,ue_id,py_id,oa_id';
                break;
            default:
                // SI NO PASAN NINGÚN FILTRO, MOSTRAMOS TODAS LAS OBRAS / ACTIVIDADES DE LA UR + PROGRAMA + SUBPROGRAMA + PROYECTO
                // + UE SI TUVIERA ALGÚN VALOR LA VARIABLE CLASADM-UE
                array_push($paramsOa, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                if (is_null($_SESSION[_S]['clasAdm-prg'])) {
                    array_push($paramsOa, 'null');
                } else {
                    array_push($paramsOa, $_SESSION[_S]['clasAdm-prg']);
                }
                if (is_null($_SESSION[_S]['clasAdm-spr'])) {
                    array_push($paramsOa, 'null');
                } else {
                    array_push($paramsOa, $_SESSION[_S]['clasAdm-spr']);
                }
                if (is_null($_SESSION[_S]['clasAdm-py'])) {
                    array_push($paramsOa, 'null');
                } else {
                    array_push($paramsOa, $_SESSION[_S]['clasAdm-py']);
                }
                if (isset($_SESSION[_S]['clasAdm-ue'])) {
                    array_push($paramsOa, $_SESSION[_S]['clasAdm-ue']);
                } else {
                    array_push($paramsOa, 'null');
                }
                $oa->sql = '
                    SELECT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre
                    FROM cat_ObraActividad
                    WHERE 
                        gpo_id = ? AND ur_id = ?
                    AND prg_id = ?
                    AND spr_id = ?
                    AND py_id = ?
                    AND ue_id = ?
                ';
                $oa->params = $paramsOa;
                break;
        }
        $oa->search = true;
        $oa->mensaje = 'Seleccionar una Obra/actividad';
        $oa->textSinRegistro = ' - ';
        $oa->detonar = false;
        $oa->class = 'clasAdminCombo';
        $oa->selecciona = $_SESSION[_S]['clasAdm-oa'];
        $ca->recargaCombo($oa);
        return $ca->getScript();
    }
    /*--------------------------------------------------------------------------------------------------------------------
    Preparamos cada uno de los combos que se dibujarán en pantalla, de la clasificación administrativa
    -----------------------------------------------------------------------------------------------------------------------*/
    public static function muestraClasAdmin()
    {
        $clasAdmin = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass());

        $ca = new WebResponse();
        $ultimoNivel = '';
        $unicaUe = $unicoProyecto = $unicaAo = null;
        $filtro = (!empty($clasAdmin->filtro)) ? $clasAdmin->filtro : '';
        $change = '';
        $change .= (!empty($clasAdmin->change)) ? $clasAdmin->change : '';
        if (!isset($clasAdmin->comboUe)) {
            $clasAdmin->comboUe = true;
        } elseif (!is_bool($clasAdmin->comboUe)) {
            $clasAdmin->comboUe = true;
        }

        if (!isset($clasAdmin->comboPrg)) {
            $clasAdmin->comboPrg = true;
        } elseif (!is_bool($clasAdmin->comboPrg)) {
            $clasAdmin->comboPrg = true;
        }

        if (!isset($clasAdmin->comboSpr)) {
            $clasAdmin->comboSpr = true;
        } elseif (!is_bool($clasAdmin->comboSpr)) {
            $clasAdmin->comboSpr = true;
        }

        if (!isset($clasAdmin->comboPy)) {
            $clasAdmin->comboPy = true;
        } elseif (!is_bool($clasAdmin->comboPy)) {
            $clasAdmin->comboPy = true;
        }

        if (!isset($clasAdmin->comboOa)) {
            $clasAdmin->comboOa = true;
        } elseif (!is_bool($clasAdmin->comboOa)) {
            $clasAdmin->comboOa = true;
        }

        // Si pasan un id de encuadre programático, filtramos sólo el programa, subprograma, proyecto, Obra/actividad que correspondan a dicho encuadre
        // -------------------------------------------------------------------------------------------------------------------
        if (isset($clasAdmin->epg_id)) {
            $ca->setSql('
                SELECT ue_id,prg_id,spr_id,py_id,oa_id,epg_id
                FROM enc_Programatico
                JOIN cat_ObraActividad
                USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                WHERE epg_id=' . $clasAdmin->epg_id . ';
            ');
            $ca->conectar();
            $ca->consultar();
            $encuadre = $ca->extraer();
            $clasAdmin->ue_id = $encuadre['ue_id'];
            $clasAdmin->prg_id = $encuadre['prg_id'];
            $clasAdmin->spr_id = $encuadre['spr_id'];
            $clasAdmin->py_id = $encuadre['py_id'];
            $clasAdmin->oa_id = $encuadre['oa_id'];
            $_SESSION[_S]['clasAdm-epg'] = $encuadre['epg_id'];
        }
        // Si comboUe == true y el nivel jerárquico NO ES de Unidad Ejecutora, el combo de UE será el último que se muestre
        // --------------------------------------------------------------------------------------------------------------------
        if ($clasAdmin->comboUe && $_SESSION[_S]['nvj_id'] > 1) {

            $ultimoNivel = 'comboue';
            $combo = new stdClass();
            $combo->id = 'comboue';
            $combo->label = 'Unidad Ejecutora';
            $paramsUe = [];
            switch ($filtro) {
                case 'poa':
                    /* Se agrego la validacion para que el costeo de la UR  esté cerrado, ya que cuando hacen cambio de titulares el estado de la ur regrea a un estado 2 */
                    array_push($paramsUe, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                    $sql = '
                        SELECT DISTINCT ue_id,ue_nombre
                        FROM cat_UnidadEjecutora
                        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                        JOIN cat_ObraActividad using(gpo_id, ur_id, ue_id)
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) )
                    ';
                    if (isset($clasAdmin->toa_id)) {
                        $sql .= ' AND toa_id = ?';
                        array_push($paramsUe, $clasAdmin->toa_id);
                    }
                    if (isset($clasAdmin->oa_origen)) {
                        $sql .= ' AND oa_origen = ?';
                        array_push($paramsUe, $clasAdmin->oa_origen);
                    }
                    if (isset($clasAdmin->oa_anticipo)) {
                        $sql .= ' AND ((oa_anticipo + oa_anticipo'.APP_EJERCICIO_ANTERIOR.') - oa_amortizacion) > 0 ';
                    }
                    $combo->sql = $sql;
                    break;
                case 'porUrClaveFinanciamiento':
                    $combo->sql = '
                        SELECT ue_id,ue_nombre
                        FROM cat_UnidadEjecutora
                        INNER JOIN presupuesto using(gpo_id,ur_id,ue_id)
                        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND eur_id=5
                    ';

                    if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 315) {
                        $clasAdmin->ue_id = $clasAdmin->ue_id;
                        $combo->sql .= ' and ue_id=' . $clasAdmin->ue_id;
                    }
                    break;
                default:
                    $combo->sql = '
                        SELECT ue_id,ue_nombre
                        FROM cat_UnidadEjecutora
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    ';
                    break;
            }

            $combo->enTd = 2;
            $combo->inputNoDiv = true;
            $combo->detonar = !$clasAdmin->comboPrg;
            $combo->class = 'clasAdminCombo';
            $combo->search = true;
            $combo->mensaje = 'Seleccionar una Ejecutora';
            $combo->textSinRegistro = 'UR sin Unidad Ejecutora';
            if ($filtro != '') {
                $combo->filtro = $filtro;
            }
            if (isset($clasAdmin->ue_id)) {
                $combo->selecciona = $clasAdmin->ue_id;
                $_SESSION[_S]['clasAdm-ue'] = $clasAdmin->ue_id;
            }
            $combo->required = true;
            if (isset($clasAdmin->form)) {
                $combo->form = $clasAdmin->form;
            }
            if (isset($clasAdmin->lectura) && is_bool($clasAdmin->lectura) && $clasAdmin->lectura) {
                $combo->sql .= ' AND ue_id = ?';
                array_push($paramsUe, $clasAdmin->ue_id);
            }
            if (isset($clasAdmin->ecf_id)) {
                $combo->sql .= ' AND ecf_id = ?';
                array_push($paramsUe, $clasAdmin->ecf_id);
            }

            if ($filtro == 'porUrClaveFinanciamiento') {
                $combo->sql .= 'GROUP BY gpo_id,ur_id,ue_id';
            }

            $combo->sql .= ' ORDER BY ue_id';
            $clasAdmin->prgDeUe = (isset($clasAdmin->prgDeUe) && is_bool($clasAdmin->prgDeUe)) ? $clasAdmin->prgDeUe : true;
            $callback = ($clasAdmin->comboPrg) ? '' : 'callbackComunica = function(){' . $change . '};';
            $combo->change = '
                ' . $callback . '
                comunica("srv/ClasificacionAdministrativaB.php",{
                    metodoClasAdmin:"cambioUe",
                    ' . (($filtro != '') ? 'filtro:$(this).attr("filtro"),' : '') . '
                    ' . (isset($clasAdmin->ecf_id) ? 'ecf_id:"' . $clasAdmin->ecf_id . '",' : '') . '
                    ' . (isset($clasAdmin->toa_id) ? 'toa_id:"' . $clasAdmin->toa_id . '",' : '') . '
                    ' . (isset($clasAdmin->oa_origen) ? 'oa_origen:"' . $clasAdmin->oa_origen . '",' : '') . '
                    ' . (isset($clasAdmin->oa_anticipo) ? 'oa_anticipo:"' . $clasAdmin->oa_anticipo . '",' : '') . '
                    ue_id:$(this).val(),
                    pr:$("#comboprg").length,
                    sp:$("#combosp").length,
                    py:$("#combopy").length,
                    oa:$("#combooa").length
                });
            ';
            $combo->params = $paramsUe;
            $ca->preScript('$("#clasadminue").html(\'' . $ca->ponCombo($combo) . '\');');
            $unicaUe = $ca->comboValorUnico;
            if (!is_null($unicaUe)) {
                $_SESSION[_S]['clasAdm-ue'] = $unicaUe;
            }

            // Si es de nivel 1, limitamos a una sola UE
            // --------------------------------------------------------------------------------------------------------------
        } else {
            $ultimoNivel = 'comboue';
            if (empty($_SESSION[_S]['ue_id'])) {
                $ca->preScript('$("#clasadminue").empty();');
            }
            $unicaUe = (empty($_SESSION[_S]['ue_id'])) ? null : $_SESSION[_S]['ue_id'];
        }

        // Si comboPrg == true ... será el último nivel a dibujar
        // -------------------------------------------------------------------------------------------------------------------
        if ($clasAdmin->comboPrg) {
            $ultimoNivel = 'comboprg';
            $combo = new stdClass();
            $combo->id = 'comboprg';
            $combo->label = 'Programa';
            $paramsPrg = [];
            $combo->search = true;
            $combo->enTd = 2;
            $combo->mensaje = 'Seleccionar un Programa';
            $combo->textSinRegistro = 'No tiene Programas Registrados';
            if ($filtro != '') {
                $combo->filtro = $filtro;
            }

            $combo->inputNoDiv = true;
            $combo->detonar = !$clasAdmin->comboSpr;
            $combo->class = 'clasAdminCombo';
            if (isset($clasAdmin->prg_id)) {
                $combo->selecciona = $clasAdmin->prg_id;
                $_SESSION[_S]['clasAdm-prg'] = $clasAdmin->prg_id;
            }
            if (isset($clasAdmin->form)) {
                $combo->form = $clasAdmin->form;
            }
            $and = '';
            switch ($filtro) {
                case 'poa':
                    array_push($paramsPrg, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                    $and = 'JOIN cat_ObraActividad using(gpo_id, ur_id,prg_id) ' .
                        ' WHERE gpo_id = ? AND ur_id = ? 
                            AND ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) ) ';
                    if (isset($clasAdmin->toa_id)) {
                        $and .= ' AND toa_id = ?';
                        array_push($paramsPrg, $clasAdmin->toa_id);
                    }
                    if (isset($clasAdmin->oa_origen)) {
                        $and .= ' AND oa_origen = ? ';
                        array_push($paramsPrg, $clasAdmin->oa_origen);
                    }
                    if (isset($clasAdmin->oa_anticipo)) {
                        $and .= ' AND ((oa_anticipo + oa_anticipo'.APP_EJERCICIO_ANTERIOR.') - oa_amortizacion) > 0';
                    }
                    break;
                case 'proyecto':
                case 'actividades':
                    //los que estan validados
                    $and = '
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND epr_id = 4
                    ';
                    break;
                case 'porUrClaveFinanciamiento':
                    $and = '
                        INNER JOIN presupuesto using(gpo_id,ur_id,prg_id)
                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                        AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                        ' . ((isset($_SESSION[_S]['clasAdm-ue']) || isset($_SESSION[_S]['ue_id'])) ? '
                        AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '  ' : ' ') . '
                    ';

                    break;
                default:
                    $and = 'WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ';
                    break;
            }
            if (isset($clasAdmin->lectura) && is_bool($clasAdmin->lectura) && $clasAdmin->lectura) {
                $and .= ' AND prg_id = ?';
                array_push($paramsPrg, $clasAdmin->prg_id);
            }

            $combo->required = true;
            $callback = ($clasAdmin->comboSpr) ? '' : 'callbackComunica = function(){' . $change . '};';
            $combo->change = '
                    ' . $callback . '
                comunica("srv/ClasificacionAdministrativaB.php",{
                    metodoClasAdmin:"cambioPrg",
                    ' . (($filtro != '') ? 'filtro:$(this).attr("filtro"),' : '') . '
                    ' . (isset($clasAdmin->ecf_id) ? 'ecf_id:"' . $clasAdmin->ecf_id . '",' : '') . '
                    ' . (isset($clasAdmin->toa_id) ? 'toa_id:"' . $clasAdmin->toa_id . '",' : '') . '
                    ' . (isset($clasAdmin->oa_origen) ? 'oa_origen:"' . $clasAdmin->oa_origen . '",' : '') . '
                    ' . (isset($clasAdmin->oa_anticipo) ? 'oa_anticipo:"' . $clasAdmin->oa_anticipo . '",' : '') . '
                    prg_id:$(this).val(),
                    sp:$("#combosp").length,
                    py:$("#combopy").length,
                    oa:$("#combooa").length
                });
            ';
            if (isset($clasAdmin->ecf_id)) {
                $and .= ' AND ecf_id = ?';
                array_push($paramsPrg, $clasAdmin->ecf_id);
            }

            $combo->sql = '
                SELECT DISTINCT prg_id,prg_nombre
                FROM cat_Programa p
                    INNER JOIN cat_Programas ps ON ps.prgs_id = p.prg_id ' . $and;

            if (isset($clasAdmin->prgDeUe) && is_bool($clasAdmin->prgDeUe) && $clasAdmin->prgDeUe && !isset($combo->selecciona)) {
                if (is_null($unicaUe)) {
                    if ($filtro == 'porUrClaveFinanciamiento') {
                        $combo->sql .= ' GROUP BY gpo_id,ur_id,prg_id';
                    }

                    $ca->preScript('$("#clasadminprg").html(\'' . $ca->templateCombo($combo) . '\');');
                    $unicoPrograma = null;
                } else {
                    if ($filtro == 'poa') {
                        $combo->sql .= ' AND ue_id = ?';
                        array_push($paramsPrg, $unicaUe);
                    } elseif ($filtro != 'porUrClaveFinanciamiento' && !empty($filtro)) {
                        $combo->sql = '
                            SELECT DISTINCT prg_id,prg_nombre 
                            FROM cat_Programa 
                            JOIN cat_ObraActividad USING(gpo_id, ur_id,prg_id) ' . $and . '
                            AND ue_id = ' . $unicaUe;
                    }

                    if ($filtro == 'porUrClaveFinanciamiento') {
                        $combo->sql .= ' GROUP BY gpo_id,ur_id,prg_id';
                    }

                    $combo->params = $paramsPrg;
                    $ca->preScript('$("#clasadminprg").html(\'' . $ca->ponCombo($combo) . '\');');
                    $unicoPrograma = $ca->comboValorUnico;
                }
            } else {
                if ($filtro == 'porUrClaveFinanciamiento') {
                    $combo->sql .= ' GROUP BY gpo_id,ur_id,prg_id';
                }

                $combo->params = $paramsPrg;
                $ca->preScript('$("#clasadminprg").html(\'' . $ca->ponCombo($combo) . '\');');
                $unicoPrograma = $ca->comboValorUnico;
            }
            if (!is_null($unicoPrograma)) {
                $_SESSION[_S]['clasAdm-prg'] = $unicoPrograma;
            }

            // Si comboSpr == true ... será el último nivel a dibujar
            // --------------------------------------------------------------------------------------------------------------
            if ($clasAdmin->comboSpr) {
                $ultimoNivel = 'combosp';
                $combo = new stdClass();
                $combo->id = 'combosp';
                $combo->label = 'Subprograma';
                $paramsSpr = [];
                $combo->enTd = 2;
                $combo->search = true;
                $combo->mensaje = 'Seleccionar un Subprograma';
                $combo->textSinRegistro = 'No tiene subprogramas Registrados';
                if ($filtro != '') {
                    $combo->filtro = $filtro;
                }

                $combo->required = true;
                $combo->detonar = !$clasAdmin->comboPy;
                $combo->inputNoDiv = true;
                $combo->class = 'clasAdminCombo';
                $callback = ($clasAdmin->comboPy) ? '' : 'callbackComunica = function(){' . $change . '};';
                $combo->change = '
                    ' . $callback . '
                    comunica("srv/ClasificacionAdministrativaB.php",{
                        metodoClasAdmin:"cambioSpr",
                        ' . (($filtro != '') ? 'filtro:$(this).attr("filtro"),' : '') . '
                        ' . (isset($clasAdmin->ecf_id) ? 'ecf_id:"' . $clasAdmin->ecf_id . '",' : '') . '
                        ' . (isset($clasAdmin->toa_id) ? 'toa_id:"' . $clasAdmin->toa_id . '",' : '') . '
                        ' . (isset($clasAdmin->oa_origen) ? 'oa_origen:"' . $clasAdmin->oa_origen . '",' : '') . '
                        ' . (isset($clasAdmin->oa_anticipo) ? 'oa_anticipo:"' . $clasAdmin->oa_anticipo . '",' : '') . '
                        spr_id:$(this).val(),
                        py:$("#combopy").length,
                        oa:$("#combooa").length
                    });
                ';
                if (isset($clasAdmin->spr_id)) {
                    $combo->selecciona = $clasAdmin->spr_id;
                    $_SESSION[_S]['clasAdm-spr'] = $clasAdmin->spr_id;
                }
                if (isset($clasAdmin->form)) {
                    $combo->form = $clasAdmin->form;
                }
                if (is_null($unicoPrograma) && !isset($combo->selecciona)) {
                    $ca->preScript('$("#clasadminspr").html(\'' . $ca->templateCombo($combo) . '\');');
                    $unicoSubprograma = null;
                } else {
                    $and = '';
                    switch ($filtro) {
                        case 'poa':
                            array_push($paramsSpr, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $_SESSION[_S]['clasAdm-ue']);
                            $combo->sql = 'SELECT DISTINCT spr_id,spr_nombre 
                                FROM cat_Subprograma 
                                JOIN cat_ObraActividad using(gpo_id, ur_id, prg_id, spr_id)  
                                WHERE gpo_id = ? AND ur_id = ?';
                            $combo->sql .= ' AND ((toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5))
                                AND ue_id = ?';
                            if (isset($clasAdmin->toa_id)) {
                                $combo->sql .= ' AND toa_id = ?';
                                array_push($paramsSpr, $clasAdmin->toa_id);
                            }
                            if (isset($clasAdmin->oa_origen)) {
                                $combo->sql .= ' AND oa_origen = ? ';
                                array_push($paramsSpr, $clasAdmin->oa_origen);
                            }
                            if (isset($clasAdmin->oa_anticipo)) {
                                $combo->sql .= ' AND ((oa_anticipo + oa_anticipo'.APP_EJERCICIO_ANTERIOR.') - oa_amortizacion) > 0';
                            }
                            break;
                        case 'proyecto':
                        case 'actividades':
                            //los que estan validados
                            $combo->sql = '
                                SELECT spr_id,spr_nombre FROM cat_Subprograma s
                                JOIN cat_Subprogramas ss ON ss.prgs_id = s.prg_id AND ss.sprs_id = s.spr_id
                                WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                                AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                                AND prg_id=' . $_SESSION[_S]['clasAdm-prg'] . '
                                AND spr_status = 1
                            ';
                            $and = ' and (esr_id = 4  OR (sprs_id = 0 AND esr_id != 4))';
                            break;
                        case 'porUrClaveFinanciamiento':
                            $combo->sql = '
                                SELECT spr_id,spr_nombre
                                FROM cat_Subprograma
                                INNER JOIN presupuesto USING(gpo_id,ur_id,prg_id,spr_id)
                                WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                                AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                                AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                                AND prg_id=' . $_SESSION[_S]['clasAdm-prg'] . '
                            ';
                            break;
                        default:
                            $combo->sql = '
                                SELECT spr_id,spr_nombre
                                FROM cat_Subprograma WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                                AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                                AND prg_id=' . $_SESSION[_S]['clasAdm-prg'] . '
                            ';
                            break;
                    }
                    $combo->sql .= $and;
                    if (isset($clasAdmin->lectura) && is_bool($clasAdmin->lectura) && $clasAdmin->lectura) {
                        $combo->sql .= ' AND spr_id = ?';
                        array_push($paramsSpr, $clasAdmin->spr_id);
                    }
                    $combo->sql .= ' AND prg_id=' . $_SESSION[_S]['clasAdm-prg'];
                    if (isset($clasAdmin->ecf_id)) {
                        $combo->sql .= ' and ecf_id = ?';
                        array_push($paramsSpr, $clasAdmin->ecf_id);
                    }

                    if ($filtro == 'porUrClaveFinanciamiento') {
                        $combo->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id';
                    }

                    $combo->params = $paramsSpr;
                    $ca->preScript('$("#clasadminspr").html(\'' . $ca->ponCombo($combo) . '\');');
                    $unicoSubprograma = $ca->comboValorUnico;
                }
                if (!is_null($unicoSubprograma)) {
                    $_SESSION[_S]['clasAdm-spr'] = $unicoSubprograma;
                }

                // Si comboPy == true ... será el último nivel a dibujar
                // ------------------------------------------------------------------------------------
                if ($clasAdmin->comboPy) {
                    $ultimoNivel = 'combopy';
                    $combo = new stdClass();
                    $combo->id = 'combopy';
                    $combo->label = 'Proyecto';
                    $paramsPy = [];
                    $combo->enTd = 2;
                    $combo->search = true;
                    $combo->mensaje = 'Seleccionar un Proyecto';
                    $combo->textSinRegistro = 'No tiene Proyectos Registrados';
                    if ($filtro != '') {
                        $combo->filtro = $filtro;
                    }

                    $combo->required = true;
                    $combo->detonar = !$clasAdmin->comboOa;
                    $combo->inputNoDiv = true;
                    $combo->class = 'clasAdminCombo';
                    $callback = ($clasAdmin->comboOa) ? '' : 'callbackComunica = function(){' . $change . '};';
                    $combo->change = '
                        ' . $callback . '
                        comunica("srv/ClasificacionAdministrativaB.php",{
                            metodoClasAdmin:"cambioPy",
                            ' . (($filtro != '') ? 'filtro:$(this).attr("filtro"),' : '') . '
                            ' . (isset($clasAdmin->ecf_id) ? 'ecf_id:"' . $clasAdmin->ecf_id . '",' : '') . '
                            ' . (isset($clasAdmin->toa_id) ? 'toa_id:"' . $clasAdmin->toa_id . '",' : '') . '
                            ' . (isset($clasAdmin->oa_origen) ? 'oa_origen:"' . $clasAdmin->oa_origen . '",' : '') . '
                            ' . (isset($clasAdmin->oa_anticipo) ? 'oa_anticipo:"' . $clasAdmin->oa_anticipo . '",' : '') . '
                            py_id:$(this).val(),
                            oa:$("#combooa").length
                        });
                    ';
                    if (isset($clasAdmin->py_id)) {
                        $combo->selecciona = $clasAdmin->py_id;
                        $_SESSION[_S]['clasAdm-py'] = $clasAdmin->py_id;
                    }
                    if (isset($clasAdmin->form)) {
                        $combo->form = $clasAdmin->form;
                    }
                    if (is_null($unicoSubprograma) && !isset($combo->selecciona)) {
                        $ca->preScript('$("#clasadminpy").html(\'' . $ca->templateCombo($combo) . '\');');
                    } else {
                        $paramsPy = [];
                        array_push($paramsPy, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                        $combo->sql = '
                            SELECT py_id,py_nombre
                            FROM cat_Proyecto
                            WHERE gpo_id = ? AND ur_id = ?';
                        $sql = $and = '';
                        switch ($filtro) {
                            case 'poa':
                                $combo->sql = '
                                    SELECT DISTINCT py_id,py_nombre 
                                    FROM cat_Proyecto 
                                    JOIN cat_ObraActividad using(gpo_id, ur_id, prg_id, spr_id, py_id) 
                                    WHERE gpo_id = ? AND ur_id = ?
                                    AND ((toa_id = 1 AND eoa_id = 4) or (toa_id = 2
                                    AND eoa_id = 5))
                                ';
                                $combo->sql .= ' AND ue_id = ?';
                                array_push($paramsPy, $_SESSION[_S]['clasAdm-ue']);
                                break;
                                //los que estan validados y que tienen actividades u obras
                            case 'actividades':
                                $and = ' and sts_id = 1 ';
                                break;
                            case 'pyNoCero':
                                $and = ' and py_id != 0 ';
                                break;
                            case 'porUrClaveFinanciamiento':
                                $combo->sql = '
                                    SELECT py_id,py_nombre
                                    FROM cat_Proyecto
                                    INNER JOIN presupuesto using(gpo_id,ur_id,prg_id,spr_id,py_id)
                                    WHERE gpo_id = ?
                                    AND ur_id = ?
                                    AND ue_id = ' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                                    AND prg_id = ' . $_SESSION[_S]['clasAdm-prg'] . '
                                    AND spr_id = ' . $_SESSION[_S]['clasAdm-spr'] . '
                                ';
                                break;
                            default:
                                $combo->sql = '
                                    SELECT py_id,py_nombre
                                    FROM cat_Proyecto
                                    WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                                    AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                                    AND prg_id=' . $_SESSION[_S]['clasAdm-prg'] . '
                                    AND spr_id=' . $_SESSION[_S]['clasAdm-spr'] . '
                                ';
                                break;
                        }
                        $combo->sql .= $and;
                        if (isset($clasAdmin->lectura) && is_bool($clasAdmin->lectura) && $clasAdmin->lectura) {
                            $combo->sql .= ' AND py_id = ?';
                            array_push($paramsPy, $clasAdmin->py_id);
                        }
                        $combo->sql .= ' AND prg_id = ?';
                        array_push($paramsPy, $_SESSION[_S]['clasAdm-prg']);
                        $combo->sql .= ' AND spr_id = ?';
                     
                        array_push($paramsPy, $_SESSION[_S]['clasAdm-spr']);
                        if (isset($clasAdmin->ecf_id)) {
                            $combo->sql .= ' AND ecf_id = ?';
                            array_push($paramsPy, $clasAdmin->ecf_id);
                        }

                        if ($filtro == 'porUrClaveFinanciamiento') {
                            $combo->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id';
                        }
                        $combo->params = $paramsPy;
                        $ca->preScript('$("#clasadminpy").html(\'' . $ca->ponCombo($combo) . '\');');
                        $unicoProyecto = (isset($combo->selecciona)) ? $_SESSION[_S]['clasAdm-py'] : $ca->comboValorUnico;
                    }
                    if (!is_null($unicoProyecto)) {
                        $_SESSION[_S]['clasAdm-py'] = $unicoProyecto;
                    }

                    // Si comboOa == true ... será el último nivel a dibujar
                    // -------------------------------------------------------------------------------------------------
                    if ($clasAdmin->comboOa) {
                        $ultimoNivel = 'combooa';
                        $combo = new stdClass();
                        $combo->id = 'combooa';
                        $combo->label = 'Obra / Actividad';
                        $paramsOa = [];
                        $combo->enTd = 2;
                        $combo->search = true;
                        $combo->mensaje = 'Seleccionar una Obra/Actividad';
                        $combo->textSinRegistro = 'No tiene Obra/Actividad Registradas';
                        if ($filtro != '') {
                            $combo->filtro = $filtro;
                        }

                        $combo->required = true;
                        $combo->detonar = true;
                        $combo->inputNoDiv = true;
                        $combo->class = 'clasAdminCombo';
                        $combo->change = '
                            if($(this).val())
                                callbackComunica = function(){' . $change . '};
                            if($(this).children().length > 1){
                                comunica("srv/ClasificacionAdministrativaB.php",{
                                    metodoClasAdmin:"cambioOa",
                                    oa_id:$(this).val()
                                });
                            }else{
                                callbackComunica();
                                callbackComunica = function(){};
                            }
                        ';
                        if (isset($clasAdmin->oa_id)) {
                            $combo->selecciona = $clasAdmin->oa_id;
                            $_SESSION[_S]['clasAdm-oa'] = $clasAdmin->oa_id;
                        }
                        if (isset($clasAdmin->form)) {
                            $combo->form = $clasAdmin->form;
                        }
                        if (is_null($unicoProyecto) && !isset($combo->selecciona)) {
                            $ca->preScript('$("#clasadminoa").html(\'' . $ca->templateCombo($combo) . '\');');
                        } else {
                            switch ($filtro) {
                                case 'poa':
                                    array_push($paramsOa, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);
                                    $combo->sql = '
                                        SELECT DISTINCT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre
                                        FROM cat_ObraActividad
                                        WHERE gpo_id = ?
                                            AND ur_id = ?
                                            AND ((toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5))
                                    ';
                                    break;
                                case 'porUrClaveFinanciamiento':
                                    $combo->sql = '
                                        SELECT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre
                                        FROM cat_ObraActividad
                                        INNER JOIN presupuesto using(gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
                                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                                        AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                                        AND ue_id=' . ($_SESSION[_S]['nvj_id'] >= 2 || ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['ue_id']) . '
                                        AND prg_id=' . $_SESSION[_S]['clasAdm-prg'] . '
                                        AND spr_id=' . $_SESSION[_S]['clasAdm-spr'] . '
                                        AND py_id=' . $_SESSION[_S]['clasAdm-py'] . ' ';
                                    break;
                                default:
                                    $combo->sql = '
                                        SELECT oa_id,REPLACE(oa_nombre ,\'"\',"") oa_nombre
                                        FROM cat_ObraActividad
                                        WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '
                                        AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                                        AND prg_id=' . $_SESSION[_S]['clasAdm-prg'] . '
                                        AND spr_id=' . $_SESSION[_S]['clasAdm-spr'] . '
                                        AND py_id=' . $_SESSION[_S]['clasAdm-py'] . '
                                    ';
                                    break;
                            }

                            if (isset($clasAdmin->lectura) && is_bool($clasAdmin->lectura) && $clasAdmin->lectura) {
                                $combo->sql .= ' AND oa_id = ?';
                                array_push($paramsOa, $clasAdmin->oa_id);
                            }

                            $combo->sql .= ' AND prg_id = ?';
                            array_push($paramsOa, $_SESSION[_S]['clasAdm-prg']);
                            $combo->sql .= ' AND spr_id = ?';
                            array_push($paramsOa, $_SESSION[_S]['clasAdm-spr']);
                            $combo->sql .= ' AND py_id = ?';
                            array_push($paramsOa, $_SESSION[_S]['clasAdm-py']);
                            $combo->sql .= ' AND ue_id = ?';
                            array_push($paramsOa, $_SESSION[_S]['clasAdm-ue']);
                            if (isset($clasAdmin->ecf_id)) {
                                $combo->sql .= ' AND ecf_id = ?';
                                array_push($paramsOa, $clasAdmin->ecf_id);
                            }

                            if (isset($clasAdmin->toa_id)) {
                                $combo->sql .= ' AND toa_id = ?';
                                array_push($paramsOa, $clasAdmin->toa_id);
                            }

                            if ($filtro == 'porUrClaveFinanciamiento') {
                                $combo->sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id,ue_id,py_id,oa_id';
                            }

                            $combo->params = $paramsOa;
                            $ca->preScript('$("#clasadminoa").html(\'' . $ca->ponCombo($combo) . '\');');
                            $unicaAo = $ca->comboValorUnico;
                        }

                        if (!is_null($unicaAo)) {
                            $_SESSION[_S]['clasAdm-oa'] = $unicaAo;
                            $_SESSION[_S]['clasAdm-epg'] = ClasificacionAdministrativa::getEpg();
                        }
                    } else {
                        $ca->preScript('$("#clasadminoa").empty();');
                    }
                } else {
                    $ca->preScript('$("#clasadminpy").empty();');
                }
            } else {
                $ca->preScript('$("#clasadminspr").empty();');
            }
        } else {
            $ca->preScript('$("#clasadminprg").empty();');
        }

        // MARCOS: Intentamos pasar a éste lado toda la nueva funcionalidad del F1 y F2

        $ca->mixScript('
            $("#comoSeUsa").click(function(){
                alertify.alert("<h4>Cómo seleccionar encuadres...<h4>","Cuando te coloques sobre los campos de <b>Encuadre Programático</b> y <b>Clave de Financiamiento</b> verás que el campo cambia de color a verde, esto significa que puedes presionar la tecla <b>F1</b> o <b>F2</b> para seleccionar los encuadres.<br /><br /><b>Método 1</b>:<br/><br/>Si presionas F1 verás la lista de todas las combinaciones de encuadre programático y claves de financiamiento existentes.<br/><br />Selecciona con doble click la que quieras utilizar y se rellenarán ambos cambos (programático y clave de financiamiento).<br /><br /><b>Método 2:</b><br/><br/>Si presionas F2 activarás la seleccion por combos.<br /><br />En éste caso debes colocarte sobre el campo de <b>Encuadre Programático</b> y presionar F2 para seleccionar cada combo del encuadre.<br/><br/>Cuando hayas seleccionado todo el encuadre (hasta Obra/Actividad) cierra la ventana y colócate en el campo de <b>Clave de Financiamiento</b>. Presiona F2 para mostrar la ventana que te permite seleccionar la Clave de Financiamento.");
            });

            $("#' . $ultimoNivel . '").attr("data-required","funcion[validaClasADmin]");
            if(typeof(progID) != "undefined"){
                $("#" + progID).keydown(function(e){
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    /* F1 llama a la pantalla altenar para escoger encuadres */
                    if ( key == 112 ) {
                        e.preventDefault();
                        dlgC.dialog("open");
                    }else if ( key == 113 ){ /* F2 muestra los combos de clasAdmin */
                        e.preventDefault();
                        $("#clasAdminSeccionCombos").toggle();
                    } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                            notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                            e.preventDefault();
                            return false;

                    }
                });
            }

        ');

        return $ca->getScript();
    }

    /*----------------------------------------------------------------------------------------------------------------------------
    Dibujamos en pantalla la clasificación administrativa
    --------------------------------------------------------------------------------------------------------------------------*/
    public static function ponClasificacionAdministrativa()
    {
        $ca = new WebResponse();
        $ca->mixHtml('<table class="table" id="tablaCombosClasAdmin">');
        $script = '';
        $ue = '';
        $ueScript = '';

        $ur = new stdClass();
        $comboUe = '';
        $ueCarga = '';
        $ur->sql = 'SELECT gpo_id, ur_id, ur_nombre, gpo_nombre FROM cat_UnidadResponsable JOIN cat_Grupo USING(gpo_id)';

        $ca->setSql('
            SELECT car_id, ue_id
            FROM cat_CargaTrabajo
            WHERE usr_id = ' . $_SESSION[_S]['usr_id'] . ' and sis_id="' . $_SESSION[_S]['mod_id'] . '"
            ORDER BY ue_id DESC
            LIMIT 1
        ');

        $ca->conectar();
        $ca->consultar();
        $ca->desconectar();

        if ($carga = $ca->extraer()) {
            $ur->sql .= ' JOIN cat_CargaTrabajo c USING( gpo_id, ur_id )
            WHERE c.usr_id=' . $_SESSION[_S]['usr_id'] . ' and sis_id="' . $_SESSION[_S]['mod_id'] . '"
            GROUP BY gpo_id, ur_id
            ';
            if ($carga['ue_id'] != '') {
                $ueCarga = 'filtro:"ueXcarga",';
            }
        } else {

            if ($_SESSION[_S]['nvj_id'] < 3) {
                #si es usuario de recursos humanos que es el único con perfil "S", mostrará las ur's que tengan 1 en ur_rh de cat_UnidadResponsable.
                if ($_SESSION[_S]['prf_id'] != 'S') {
                    $_SESSION[_S]['clasAdm-gpo'] = $_SESSION[_S]['gpo_id'];
                    $_SESSION[_S]['clasAdm-ur'] = $_SESSION[_S]['ur_id'];
                    $ur->sql .= 'WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . ' AND ur_id=' . $_SESSION[_S]['ur_id'] . ';';
                } else {
                    $ur->sql .= 'WHERE crh_id=1;';
                }
            } else {
                $ur->sql .= 'WHERE gpo_id!=0;';
            }

            // Si es un usuario de nivel UE, solo mostramos en el combo su Unidad Ejecutora
            if ($_SESSION[_S]['nvj_id'] == 1) {
                $_SESSION[_S]['clasAdm-ue'] = $_SESSION[_S]['ue_id'];
                $ue = '<td width="250px"><label>Unidad Ejecutora</label></td><td><div class="btn-group bootstrap-select show-tick clasAdminCombo"><button data-original-title="313 - SECRETARIA DE FINANZAS" title="" data-id="combour" disabled="" type="button"class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown"><span class="filter-option pull-left noDisabled">' . $_SESSION[_S]['ue_id'] . ' - ' . $_SESSION[_S]['nombre_ue'] . '</span></button></div></td>';
            } else {
                $_SESSION[_S]['clasAdm-ue'] = null;
            }
        }

        $ur->change = '
            var comboUr = {
                    metodoClasAdmin:"cambioUr",
                    gpo_id:$("#combour").find("option:selected").attr("gpo_id"),
                    ur_id:$("#combour").find("option:selected").attr("ur_id"),
                    ' . $ueCarga . '
                    ue:$("#comboue").length,
                    pr:$("#comboprg").length,
                    sp:$("#combosp").length,
                    py:$("#combopy").length,
                    oa:$("#combooa").length
            };
            if( $("#comboue").length && typeof( $("#comboue").attr("filtro") ) != "undefined" &&  $("#comboue").attr("filtro") !== false )
                    comboUr.filtro = $("#comboue").attr("filtro");
            else if( $("#comboprg").length && typeof( $("#comboprg").attr("filtro") ) != "undefined" &&  $("#comboprg").attr("filtro") !== false )
                    comboUr.filtro = $("#comboprg").attr("filtro");
            comunica("srv/ClasificacionAdministrativaB.php",comboUr);
        ';
        $ur->id = 'combour';
        $ur->label = 'Unidad Responsable';
        $ur->camposVal = 'gpo_id,ur_id';
        $ur->camposText = 'ur_nombre';
        $ur->comoAtr = 'gpo_id,ur_id';
        $ur->agrupaPor = 'gpo_id';
        $ur->campoLabelAgrupacion = 'gpo_nombre';
        $ur->enTd = 2;
        $ur->inputNoDiv = true;
        $ur->mensaje = false;
        $ur->class = 'clasAdminCombo';
        $ur->textSinRegistro = 'Sin carga de trabajo';
        $ur->search = true;
        $ca->mixHtml($ca->ponCombo($ur));

        if (!is_null($ca->valorSeleccionado)) {
            $_SESSION[_S]['clasAdm-gpo'] = substr($ca->valorSeleccionado, 0, 1);
            $_SESSION[_S]['clasAdm-ur'] = substr($ca->valorSeleccionado, 1, 2);
        }

        if ($ueCarga != '') {
            $ue_combo = new stdClass();
            $ue_combo->sql = '
                SELECT ue_id, ue_nombre
                FROM cat_UnidadEjecutora
                    INNER JOIN cat_UnidadResponsable USING( gpo_id, ur_id )
                    INNER JOIN cat_Grupo USING(gpo_id)
                    INNER JOIN cat_CargaTrabajo c USING( gpo_id, ur_id, ue_id )
                WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND c.usr_id=' . $_SESSION[_S]['usr_id'] . '
                ORDER BY ue_id
            ';
            $ue_combo->id = 'comboue';
            $ue_combo->label = 'Unidad Ejecutora';
            $ue_combo->comoAtr = 'ue_id';
            $ue_combo->campoLabelAgrupacion = 'ue_nombre';
            $ue_combo->enTd = 2;
            $ue_combo->inputNoDiv = true;
            $ue_combo->mensaje = false;
            $ue_combo->class = 'clasAdminCombo uePorCarga';
            $ue_combo->change = '
                var comboUe = {
                    metodoClasAdmin:"cambioUe",
                    ue_id:$("#comboue").find("option:selected").attr("ue_id")
                };
                comboUe.filtro = "ueXcarga";
                comunica("srv/ClasificacionAdministrativaB.php",comboUe);
            ';
            $comboUe = $ca->ponCombo($ue_combo);
            $ueScript = '$("#comboue").change();';

            $ca->mixHtml('<tr id="clasadminue">' . $comboUe . '</tr>');
        } else {
            $ca->mixHtml('<tr id="clasadminue">' . $ue . '</tr>');
        }

        $ca->mixHtml('
                <tr id="clasadminprg"></tr>
                <tr id="clasadminspr"></tr><tr id="clasadminpy"></tr>
                <tr id="clasadminoa"></tr>
            </table>
            <button id="toggleCAx" class="btn btn-danger btn-small "
                style="float:right; padding: 2px 6px;">
                <i class="fa fa-xmark"></i>
            </button>
        ');
        $ca->mixScript($script);

        return $ca->getHtml() . '<script language="javascript" >' . $ca->getScript()
            . $ueScript . '$("#tablaCombosClasAdmin  tr  td:first").width("120px");</script>';
    }

    public static function clasAdminUe()
    {
        $clasAdmin = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : new stdClass()) : new stdClass());
        $ca = new WebResponse();

        /**
        EDGAR: 18/05/2016 Se copío el código de funciones SCG y se le incluye al funcionamiento del combo el $joinUE, la ventaja de usarlo aquí es que actualiza los valores en la sesión, por lo que es mas transparente el funcionamiento de los reportes.
         */
        // Si en cat_CargaTrabajo tiene ue_id == null entonces NO está limitado por UE
        // Pero si sí está definido un valor en ése campo, quiere decir que sí debemos limitarlo a dichas UEs
        $ca->setSql('SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = ' . $_SESSION[_S]['usr_id']);
        $ca->conectar();
        $ca->consultar();
        $numRegistros = $ca->getNumeroRegistros();
        $row = $ca->extraer();
        $ca->desconectar();

        if ($numRegistros == 0) {
            $joinUE = '';
        } else {
            if (is_null($row['ue_id'])) {
                $joinUE = '';
            } else {
                $joinUE = 'JOIN ( SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = ' . $_SESSION[_S]['usr_id'] . ' ) ct USING( ue_id )';
            }
        }

        $ultimoNivel = 'comboue';
        $combo = new stdClass();
        $combo->id = 'comboue';
        $combo->label = (isset($clasAdmin->label)) ? $clasAdmin->label : 'UE';
        $combo->sql = 'SELECT ue_id, ue_nombre FROM cat_UnidadEjecutora ' . $joinUE . ' WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'];
        $combo->search = true;

        $combo->sql .= ($_SESSION[_S]['nvj_id'] == 1) ? ' AND ue_id=' . $_SESSION[_S]['ue_id'] . ' ' : ' ';
        $combo->inputNoDiv = true;
        $combo->comoAtr = 'ue_id';
        $combo->detonar = false;
        $combo->mensaje = true;
        $combo->textSinRegistro = 'UR sin Unidad Ejecutora';
        $combo->required = true;
        if (isset($clasAdmin->enTd)) {
            $combo->enTd = $clasAdmin->enTd;
        }

        if (isset($clasAdmin->horizontal)) {
            $combo->horizontal = $clasAdmin->horizontal;
        }

        if (isset($clasAdmin->form)) {
            $combo->form = $clasAdmin->form;
        }

        if (isset($clasAdmin->ue_id)) {
            if (isset($clasAdmin->lectura) && is_bool($clasAdmin->lectura) && $clasAdmin->lectura) {
                $combo->sql .= ' AND ue_id=' . $clasAdmin->ue_id;
            }

            $combo->selecciona = $clasAdmin->ue_id;
            $_SESSION[_S]['clasAdm-ue'] = $clasAdmin->ue_id;
        }
        $combo->sql .= '  ORDER BY ue_id';
        $combo->change = '
                comunica("srv/ClasificacionAdministrativaB.php",{
                    metodoClasAdmin:"cambioUe",
                    ue_id:$(this).val()
                });
        ';
        $comboUe = $ca->ponCombo($combo);
        if (!empty($ca->comboValorUnico)) {
            $_SESSION[_S]['clasAdm-ue'] = $ca->comboValorUnico;
        }

        return $comboUe . '<script language="javascript" >' . $ca->getScript() . '</script>';
    }

    /*-----------------------------------------------------------------------------------------------------------------------

    -------------------------------------------------------------------------------------------------------------------------*/
    public static function clasAdminPrg()
    {
        $prg_id = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $ca = new WebResponse();
        $prg = new stdClass();
        $prg->id = 'comboprg';
        $prg->sql = 'SELECT prg_id,prg_nombre FROM cat_Programa WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ';
        $prg->sql .= (isset($prg_id->id)) ? 'AND prg_id=' . $prg_id->id . ';' : ';';
        $prg->label = 'PRG';
        if (isset($prg_id->form)) {
            $prg->form = $prg_id->form;
            $prg->required = isset($prg_id->required);
        }
        $prg->enTd = 2;
        $prg->search = true;
        $prg->mensaje = 'Seleccionar un Programa';
        $prg->textSinRegistro = 'UR sin Programa';

        $ca->mixScript('
            if($("#comboprg").length == 0){
                $("#clasadminprg").html(\'' . $ca->ponCombo($prg) . '\');
                $("#comboprg").change(function(){

                });
            }
        ');
        $_SESSION[_S]['clasAdm-prg'] = (!is_null($_SESSION[_S]['clasAdm-prg'])) ? $_SESSION[_S]['clasAdm-prg'] : $ca->comboValorUnico;
        return $ca->getScript();
    }

    /*---------------------------------------------------------------------------------------------------------------------

    ----------------------------------------------------------------------------------------------------------------------*/
    public static function clasAdminSpr()
    {
        $spr_id = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $ca = new WebResponse();
        $spr = new stdClass();
        $spr->id = 'combosp';
        $spr->sql = 'SELECT spr_id,spr_nombre FROM cat_Subprograma WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' AND prg_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ';
        $spr->sql .= (isset($spr_id->id)) ? 'AND spr_id=' . $spr_id->id . ';' : ';';
        if (isset($spr_id->form)) {
            $spr->form = $spr_id->form;
            $spr->required = isset($spr_id->required);
        }
        $spr->label = 'SPR';
        $spr->enTd = 2;
        $spr->search = true;
        $spr->mensaje = 'Seleccionar un Subprograma';
        $ca->mixScript('
            if($("#combosp").length == 0){
                $("#clasadminspr").html(\'' . $ca->ponCombo($spr) . '\');
                $("#combosp").change(function(){
                    comunica("srv/ClasificacionAdministrativaB.php",{
                        metodoClasAdmin:"cambioSpr",
                        spr_id:$(this).val(),
                        py:$("#combopy").length,
                        oa:$("#combooa").length
                        });
                });
            }
        ');
        $_SESSION[_S]['clasAdm-spr'] = (!is_null($_SESSION[_S]['clasAdm-spr'])) ? $_SESSION[_S]['clasAdm-spr'] : $ca->comboValorUnico;
        return $ca->getScript();
    }

    /*------------------------------------------------------------------------------------------------------------------------

    -------------------------------------------------------------------------------------------------------------------------*/
    public static function clasAdminPy()
    {
        $py_id = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $ca = new WebResponse();
        $py = new stdClass();
        $py->id = 'comboPy';
        $py->sql = 'SELECT py_id,py_nombre FROM cat_Proyecto WHERE gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ';
        $py->sql .= (!is_null($_SESSION[_S]['clasAdm-prg'])) ? ' AND prg_id=' . $_SESSION[_S]['clasAdm-prg'] . ' ' : '';
        $py->sql .= (!is_null($_SESSION[_S]['clasAdm-spr'])) ? '  AND prg_id=' . $_SESSION[_S]['clasAdm-spr'] . ' ' : '';
        $py->sql .= (isset($py_id->id)) ? 'AND py_id=' . $py_id->id . ';' : ';';
        $py->label = 'PRY';
        $py->enTd = 2;
        $py->search = true;
        $py->mensaje = 'Seleccionar un Proyecto';
        $ca->mixScript('
            if( $("#comboPy").length == 0){
                $("#clasadminpy").html(\'' . $ca->ponCombo($py) . '\');
                $("#comboPy").change(function(){
                    comunica("srv/ClasificacionAdministrativaB.php",{
                        metodoClasAdmin:"cambioPy",
                        py_id:$(this).val(),
                        oa:$("#combooa").length
                        });
                });
            }
        ');
        $_SESSION[_S]['clasAdm-py'] = $ca->comboValorUnico;
        return $ca->getScript();
    }

    public static function clasAdminOa()
    {
    }

    /*-----------------------------------------------------------------------------------------------------------------------
    Pone todos los combos bloqueados
    ------------------------------------------------------------------------------------------------------------------------*/
    public static function bloquearClasAdmin()
    {
        return '
            if( $("#combour").length ) $("#combour").attr("disabled","disabled").hide().before( $("<input />").addClass("form-group clasAdmin-bloqueado").attr("type","text").css("width","100%").val( $("#combour option:selected").text() ));
            if( $("#inputCombo-combour").length ) $("#inputCombo-combour").attr("disabled","disabled").hide()

            if( $("#comboue").length ) $("#comboue").attr("disabled","disabled").hide().before( $("<input />").addClass("form-group clasAdmin-bloqueado").attr("type","text").css("width","100%").val( $("#comboue option:selected").text() ));
            if( $("#inputCombo-comboue").length ) $("#inputCombo-comboue").attr("disabled","disabled").hide()

            if( $("#comboprg").length) $("#comboprg").attr("disabled","disabled").hide().before( $("<input />").addClass("form-group clasAdmin-bloqueado").attr("type","text").css("width","100%").val( $("#comboprg option:selected").text() ));
            if( $("#inputCombo-comboprg").length ) $("#inputCombo-comboprg").attr("disabled","disabled").hide()

            if( $("#combosp").length ) $("#combosp").attr("disabled","disabled").hide().before( $("<input />").addClass("form-group clasAdmin-bloqueado").attr("type","text").css("width","100%").val( $("#combosp option:selected").text() ));
            if( $("#inputCombo-combosp").length ) $("#inputCombo-combosp").attr("disabled","disabled").hide()

            if( $("#combopy").length ) $("#combopy").attr("disabled","disabled").hide().before( $("<input />").addClass("form-group clasAdmin-bloqueado").attr("type","text").css("width","100%").val( $("#combopy option:selected").text() ));
            if( $("#inputCombo-combopy").length ) $("#inputCombo-combopy").attr("disabled","disabled").hide()

            if( $("#combooa").length ) $("#combooa").attr("disabled","disabled").hide().before( $("<input />").addClass("form-group clasAdmin-bloqueado").attr("type","text").css("width","100%").val( $("#combooa option:selected").text() ));
            if( $("#inputCombo-combooa").length ) $("#inputCombo-combooa").attr("disabled","disabled").hide()

        ';
    }

    /*-------------------------------------------------------------------------------------------------------------------------
    Quita el atributo de bloqueado a todos los combos de la clasificación administrativa
    --------------------------------------------------------------------------------------------------------------------------*/
    public static function desbloquearClasAdmin()
    {
        return '
            if( $("#combour").length ){
                $("#combour").removeAttr("disabled").show();
                if( $("#inputCombo-combour").length ){
                    $("#inputCombo-combour").removeAttr("disabled").show()
                    $("#combour").hide()
                }
            }
            if( $("#comboue").length ){
                $("#comboue").removeAttr("disabled").show();
                if( $("#inputCombo-comboue").length ){
                    $("#inputCombo-comboue").removeAttr("disabled").show()
                    $("#comboue").hide()
                }
            }
            if( $("#comboprg").length){
                $("#comboprg").removeAttr("disabled").show();
                if( $("#inputCombo-comboprg").length ){
                    $("#inputCombo-comboprg").removeAttr("disabled").show()
                    $("#comboprg").hide()
                }
            }
            if( $("#combosp").length ){
                $("#combosp").removeAttr("disabled").show();
                if( $("#inputCombo-combosp").length ){
                    $("#inputCombo-combosp").removeAttr("disabled").show()
                    $("#combosp").hide()
                }
            }
            if( $("#combopy").length ){
                $("#combopy").removeAttr("disabled").show();
                if( $("#inputCombo-combopy").length ){
                    $("#inputCombo-combopy").removeAttr("disabled").show()
                    $("#combopy").hide()
                }
            }
            if( $("#combooa").length ){
                $("#combooa").removeAttr("disabled").show();
                if( $("#inputCombo-combooa").length ){
                    $("#inputCombo-combooa").removeAttr("disabled").show()
                    $("#combooa").hide()
                }
            }
        ';
    }

    /*-----------------------------------------------------------------------------------------------------------------------
    Para disparar un script cuando cambie la Unidad Responsable
    -------------------------------------------------------------------------------------------------------------------------*/
    public static function onChangeUr($Script = '')
    {
        return '
            $("#combour").unbind("change");
            $("#combour").bind("change",function(){
                $("#combour").selectpicker("refresh");
                var comboUr = {
                    metodoClasAdmin:"cambioUr",
                    gpo_id:$(this).find("option:selected").attr("gpo_id"),
                    ur_id:$(this).find("option:selected").attr("ur_id"),
                    ue:$("#comboue").length,
                    pr:$("#comboprg").length,
                    sp:$("#combosp").length,
                    py:$("#combopy").length,
                    oa:$("#combooa").length
                };

                if( $("#comboue.uePorCarga").length != 0  ){
                    comboUr.filtro = "ueXcarga";
                }

                if( $("#comboue").length && typeof( $("#comboue").attr("filtro") ) != "undefined" &&  $("#comboue").attr("filtro") !== false )
                    comboUr.filtro = $("#comboue").attr("filtro");
                else if( $("#comboprg").length && typeof( $("#comboprg").attr("filtro") ) != "undefined" &&  $("#comboprg").attr("filtro") !== false )
                    comboUr.filtro = $("#comboprg").attr("filtro");

                callbackComunica = function(){$("#combour").selectpicker("refresh");' . $Script . '};
                comunica("srv/ClasificacionAdministrativaB.php", comboUr );
            });
        ';
    }

    public static function getEpg($Script = '')
    {
        $ca = new WebResponse();
        if (
            (!isset($_SESSION[_S]['clasAdm-prg']) || is_null($_SESSION[_S]['clasAdm-prg']))
            || (!isset($_SESSION[_S]['clasAdm-spr']) || is_null($_SESSION[_S]['clasAdm-spr']))
            || (!isset($_SESSION[_S]['clasAdm-py']) || is_null($_SESSION[_S]['clasAdm-py']))
            || (!isset($_SESSION[_S]['clasAdm-oa']) || is_null($_SESSION[_S]['clasAdm-oa']))
        ) {
            return null;
        }

        $ca->setSql('
            SELECT epg_id
            FROM enc_Programatico
            WHERE gpo_id =' . $_SESSION[_S]['clasAdm-gpo'] . '
                AND ur_id  =' . $_SESSION[_S]['clasAdm-ur'] . '
                AND prg_id =' . $_SESSION[_S]['clasAdm-prg'] . '
                AND spr_id =' . $_SESSION[_S]['clasAdm-spr'] . '
                AND py_id  =' . $_SESSION[_S]['clasAdm-py'] . '
                AND oa_id  =' . $_SESSION[_S]['clasAdm-oa'] . ';
        ');

        $ca->conectar();
        $ca->consultar();
        $ca->desconectar();
        $epg = $ca->extraer();
        $epg = (empty($epg)) ? 0 : $epg['epg_id'];
        return $epg;
    }
    /*--------------------------------------------------------------------------------------------------------------------------
    Al pasarle el valor del encuadre programatico, pone en las variables de sesión todo lo que corresponda a dicho encuadre
    ----------------------------------------------------------------------------------------------------------------------------*/
    public static function PonEnVariablesEpg($encPg, $regresaProgramatico = false)
    {
        #26-08-14 -> Modificacion: Israel
        #Si es un proyecto sin actividad, se debe mostrar el nombre del proyecto
        // Obtenemos la clasificación administrativa
        $encPg = (empty($encPg)) ? 0 : $encPg;
        $ep = new WebResponse();
        $ep->setSql('
            SELECT gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, oa_nombre, ue_id, py_nombre
            FROM enc_Programatico
                INNER JOIN cat_ObraActividad USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
                INNER JOIN cat_Proyecto USING( gpo_id, ur_id, prg_id, spr_id, py_id )
            WHERE epg_id = ' . $encPg);
        $ep->conectar();
        $ep->consultar();
        $ep->desconectar();
        if ($ep->getNumeroRegistros() > 0) {
            $row = $ep->extraer();
            $_SESSION[_S]['clasAdm-prg'] = $row['prg_id'];
            $_SESSION[_S]['clasAdm-spr'] = $row['spr_id'];
            $_SESSION[_S]['clasAdm-py'] = $row['py_id'];
            $_SESSION[_S]['clasAdm-oa'] = $row['oa_id'];
            $_SESSION[_S]['clasAdm-epg'] = $encPg;
            $_SESSION[_S]['clasAdm-ue'] = $row['ue_id'];
            if ($row['oa_id'] != 0) {
                $oa_nombre = $row['oa_nombre'];
            } else {
                $oa_nombre = $row['py_id'] . ' - ' . $row['py_nombre'];
            }
        } else {
            $oa_nombre = 'error';
        }
        if (!$regresaProgramatico) {
            return $oa_nombre;

        } else {
            $programatico =
            $row['gpo_id'] . $row['ur_id'] . '.'
                . $row['ue_id'] . '.'
                . $row['prg_id'] . '.'
                . $row['spr_id'] . '.'
                . $row['py_id'] . '.'
                . $row['oa_id'];
            return ['oa_nombre' => $oa_nombre, 'programatico' => $programatico];
        }

    }

    public static function listarEpg()
    {
        $parametros = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $filtro = (isset($parametros->filtro) && $parametros->filtro != '') ? $parametros->filtro : '';
        $lectura = (isset($parametros->lectura)) ? true : false;
        $_SESSION[_S]['clasAdm-oa'] = null;
        $oa = new WebResponse();
        switch ($filtro) {
            case 'poa':
                $sql = "
                    SELECT oa_id,REPLACE(oa_nombre ,'\"','' ) oa_nombre
                    FROM cat_ObraActividad
                    WHERE gpo_id=" . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND ue_id=' . (!is_null($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : 'null') . '
                        AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                        AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . '
                        AND py_id=' . (!is_null($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : 'null') . '
                        and ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) )
                ';
                if (isset($parametros->toa_id)) {
                    $sql .= ' AND toa_id =' . $parametros->toa_id;
                }
                if (isset($parametros->oa_origen)) {
                    $sql .= ' AND oa_origen ="' . $parametros->oa_origen . '"';
                }
                if (isset($parametros->oa_anticipo)) {
                    $sql .= ' AND oa_anticipo-oa_amortizacion >0';
                }
                break;
                // Si piden éste filtro, cargamos todos los subprogramas que hayan sido utilizadas para armar partidas presupuestales
                // SI PASAN UN ID PARA EL ENCUADRE CON LA CLAVE DE FINANCIAMIENTO, FILTRAMOS ADEMAS QUE LA CLAVE PRESUPUESTAL SEA DE DICHO ENCUADRE
                // SI PASAN UN ID PARA EL TIPO DE OBRA / ACTIVIDAD, TAMBIEN FILTRAMOS POR DICHO VALOR
            case 'porUrClaveFinanciamiento':
                $sql = "
                    SELECT oa_id,REPLACE(oa_nombre ,'\"','') oa_nombre
                    FROM cat_ObraActividad
                        INNER JOIN presupuesto using(gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
                    where gpo_id=" . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND ue_id=' . ($_SESSION[_S]['s16-nvj'] >= 2 || ($_SESSION[_S]['s16-nvj'] == 2 && $_SESSION[_S]['s16-prf'] == 'S') ? $_SESSION[_S]['clasAdm-ue'] : $_SESSION[_S]['s16-ue']) . '
                        and ( (toa_id = 1 AND eoa_id = 4) or (toa_id = 2 AND eoa_id = 5) )
                        AND prg_id=' . (!is_null($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : 'null') . '
                        AND spr_id=' . (!is_null($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : 'null') . '
                        AND py_id=' . (!is_null($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : 'null');
                if (isset($parametros->ecf_id)) {
                    $sql .= ' and ecf_id=' . $parametros->ecf_id . ' ';
                }

                if (isset($parametros->toa_id)) {
                    $sql .= ' and toa_id=' . $parametros->toa_id . ' ';
                }

                $sql .= ' GROUP BY gpo_id,ur_id,prg_id,spr_id,ue_id,py_id,oa_id';
                break;
            case 'programatico':

                $sql = '
                    SELECT epg_id, oa_nombre, epg_clave
                    FROM poa_OrigenRecurso
                        INNER JOIN enc_Programatico epg USING( gpo_id, ur_id, epg_id )
                        INNER JOIN cat_ObraActividad p USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
                    WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                        ' . ((isset($parametros->ue) && $parametros->ue != '') ? $parametros->ue : '') . '
                    GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
                ';
                break;
            default:
                // SI NO PASAN NINGÚN FILTRO, MOSTRAMOS TODAS LAS OBRAS / ACTIVIDADES DE LA UR + PROGRAMA + SUBPROGRAMA + PROYECTO
                // + UE SI TUVIERA ALGÚN VALOR LA VARIABLE CLASADM-UE
                $sql = "
                    SELECT oa_id,REPLACE(oa_nombre ,'\"','')oa_nombre
                    FROM cat_ObraActividad
                    WHERE gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                        AND prg_id = ' . ((is_null($_SESSION[_S]['clasAdm-prg'])) ? 'null' : $_SESSION[_S]['clasAdm-prg']) . '
                        AND spr_id = ' . ((is_null($_SESSION[_S]['clasAdm-spr'])) ? 'null' : $_SESSION[_S]['clasAdm-spr']) . '
                        AND py_id = ' . ((is_null($_SESSION[_S]['clasAdm-py'])) ? 'null' : $_SESSION[_S]['clasAdm-py']);
                $sql .= ' AND ue_id = ' . ((!is_null($_SESSION[_S]['clasAdm-ue'])) ? $_SESSION[_S]['clasAdm-ue'] : 'null');
                break;
        }

        $combo = new stdClass();
        $combo->id = 'comboprogramatico';
        $combo->sql = $sql;
        $combo->enDiv = true;
        $combo->required = true;
        $combo->soloText = true;
        $combo->label = 'Obra/Actividad';
        $combo->mensaje = 'Seleccione una obra/actividad de la lista';
        $combo->camposText = 'epg_clave,oa_nombre';
        $combo->camposVal = 'epg_id';
        $combo->comoAtr = 'epg_id';

        //agregar el change en los parametros
        if (isset($parametros->change) && $parametros->change) {
            $combo->change .= '
            comunica("srv/ClasificacionAdministrativaB.php",{
                metodoClasAdmin:"PonEnVariablesEpg",
                epg_id:$("#comboprogramatico").val()
            });
            ';
        }

        $combo->selecciona = (isset($parametros->selecciona) && $parametros->selecciona != '') ? $parametros->selecciona : '';

        if (isset($parametros->inicial)) {
            $programatico = $oa->templateCombo($combo);
        } else {
            if ($lectura) {
                $combo->lectura = true;
            }

            $programatico = $oa->ponCombo($combo);
        }

        $script = '';
        $script = $oa->getScript();

        $comboprogramatico = '
            <fieldset class="col-md-12 col-xs-12" id="comboProg">
                ' . $programatico . '
            </fieldset>' . '
        ';

        return $comboprogramatico . '<script language="javascript" >' . $script . '</script>';
    }
}
if (isset($_POST['metodoClasAdmin'])) {
    new ClasificacionAdministrativa();
}
