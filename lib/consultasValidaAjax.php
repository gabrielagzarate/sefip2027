<?php
require_once dirname(__FILE__) . "/constantes.php";
function consultasValidaAjax($datos)
{
    /* Esta validacion es para la ur 318 ya que ellos pueden acceder como cualquier Unidad Responsable */
    $ur = '';
    $grupo = '';
    $numeroRct = '';

    if (isset($datos->Unidad) && $datos->Unidad != "") {
        $grupo = substr($datos->Unidad, 0, 1);
        $ur = substr($datos->Unidad, 1, 2);
    }
    $validacion = new StdClass();
    switch ($datos->validaCon) {
        case 'folio':
            $datos->omo_id = isset($_SESSION[_S]['ssc_id']) ? ($_SESSION[_S]['ssc_id'] == 2 ? 'E' : ($_SESSION[_S]['ssc_id'] == 3 ? 'B' : $datos->omo_id)) : $datos->omo_id;
            switch ($datos->omo_id) {
                case 'A':
                    $validacion->sql = "select mod_id from scp_MaestroModificacion where omo_id = '{$datos->omo_id}' and mod_gpo_id = {$_SESSION[_S]['s18-gpo']} and mod_ur_id = {$_SESSION[_S]['s18-ur']} and mod_id = {$datos->valor}";
                    break;
                case 'B':
                case 'E':
                case 'C':
                    $validacion->sql = "select mod_id from scp_MaestroModificacion where omo_id = '{$datos->omo_id}' and mod_id = {$datos->valor}";
                    break;
            }
            $validacion->mensajeValido = $datos->mensajeValido;
            $validacion->mensajeNoValido = $datos->mensajeNoValido;
            break;
        case 'clc':
            $numeroRct = '';
            switch ($_SESSION[_S]['s18-gpo'] . $_SESSION[_S]['s18-ur']) {
                case '118':
                    $filtro = "WHERE gpo_id = {$grupo} AND ur_id = {$ur} AND clc_id = {$datos->valor} AND c.ecl_id in (12,13, 16) AND tcl_id='{$datos->tcl_id}' ";
                    break;
                default:
                    $numeroRct = 'AND rectificacionConcluida < 2';
                    $filtro = "WHERE gpo_id = {$_SESSION[_S]['s18-gpo']} AND ur_id = {$_SESSION[_S]['s18-ur']} AND clc_id = {$datos->valor} AND c.ecl_id in (12,13, 16) AND tcl_id='{$datos->tcl_id}'  ";

                    break;
            }
            $validacion->sql = "
                SELECT clc_id,SUM( if(r.ecl_id = 6,1,0) ) rectificacionConcluida,SUM( if(r.ecl_id != 6,1,0) ) rectificacionEnTramite
                FROM scp_Clc c
                LEFT JOIN scp_MaestroRectificacion r using (gpo_id,ur_id,clc_id)
                {$filtro}
                HAVING  rectificacionEnTramite = 0 {$numeroRct}
            ";
            $validacion->mensajeValido = $datos->mensajeValido;
            $validacion->mensajeNoValido = $datos->mensajeNoValido;
            break;
        case 'folioAdecuacionMeta': // modulo de adecuacion de metas
            $datos->omo_id = isset($_SESSION[_S]['ssc_id']) ? ($_SESSION[_S]['ssc_id'] == 2 ? 'E' : ($_SESSION[_S]['ssc_id'] == 3 ? 'B' : $datos->omo_id)) : $datos->omo_id;
            switch ($datos->omo_id) {
                case 'B':
                    $validacion->sql = "SELECT mme_id FROM scp_ModificacionMeta WHERE omo_id = '{$datos->omo_id}' AND mme_id = {$datos->valor}";
                    break;
                case 'E':
                    $validacion->sql = "SELECT mme_id FROM scp_ModificacionMeta WHERE omo_id = '{$datos->omo_id}' AND mme_id = {$datos->valor}";
                    break;
            }
            $validacion->mensajeValido = $datos->mensajeValido;
            $validacion->mensajeNoValido = $datos->mensajeNoValido;
            break;
    }
    return $validacion;
}
