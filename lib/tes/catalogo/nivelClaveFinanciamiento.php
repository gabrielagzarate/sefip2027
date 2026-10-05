<?php    
/**
 * Description of function nivelClaveFinanciamiento
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 05-Sep-2019
 * Comment: -----
 * Objective: Ordena los datos consultados en niveles de acuerdo a la opcion que se requiera, claveFinanciamiento.
 * Regla de Negocio: -----
 * 
 * */

require_once dirname(__FILE__) . '/consultaClaveFinanciamiento.php';
    
function nivelClaveFinanciamiento($ccd_id, $primeraParteClave, $segundaParteClave, $tipoRecurso = false, $presupuesto = false, $tesoreria = false)
{
    
    $clave = consultaClaveFinanciamiento($ccd_id, $primeraParteClave, $segundaParteClave, $tipoRecurso );
    $nivel11 = '';
    $datos = array();
    if (!array_key_exists('nivel', $clave[0])) {
        foreach ($clave as $key => $val) {                
            // Definir el nivel 11
            switch ($ccd_id) {
                case 0:
                    if ($nivel11 != $clave[$key]['cff_id']) {
                        $dato = array();
                        $dato['clave'] = $clave[$key]['cff_id'];
                        $dato['nombre'] = $clave[$key]['cff_nombre'];
                        $dato['nombreColumna'] = 'CLASIFICADOR FUENTE FINANCIAMIENTO';
                        $dato['nivel'] = 11;
                        array_push( $datos, $dato );

                        $nivel11 = $clave[$key]['cff_id'];
                    }
                    break;
                case 1:
                    if ($nivel11 != $clave[$key]['fuente']) {
                        $dato = array();
                        $dato['clave'] = $clave[$key]['fuente'];
                        $dato['nombre'] = $clave[$key]['fte_nombre'];
                        $dato['nombreColumna'] = 'FUENTE';
                        $dato['nivel'] = 11;
                        array_push( $datos, $dato );

                        $nivel11 = $clave[$key]['fuente'];
                    }
                    break;
                case 2:
                    if ($nivel11 != $clave[$key]['subfuente']) {
                        $dato = array();
                        $dato['clave'] = $clave[$key]['subfuente'];
                        $dato['nombre'] = $clave[$key]['sft_nombre'];
                        $dato['nombreColumna'] = 'SUB-FUENTE';
                        $dato['nivel'] = 11;
                        array_push( $datos, $dato );
                        
                        $nivel11 = $clave[$key]['subfuente'];
                    }
                    break;
                case 3:
                    if ($nivel11 != $clave[$key]['origen']) {
                        $dato = array();
                        $dato['clave'] = $clave[$key]['origen'];
                        $dato['nombre'] = $clave[$key]['ori_nombre'];
                        $dato['nombreColumna'] = 'ORIGEN';
                        $dato['nivel'] = 11;
                        array_push( $datos, $dato );

                        $nivel11=$clave[$key]['origen'];
                    }
                    break;
                case 4:
                    if ($nivel11!=$clave[$key]['suborigen']) {
                        $dato = array();
                        $dato['clave'] = $clave[$key]['suborigen'];
                        $dato['nombre'] = $clave[$key]['sor_nombre'];
                        $dato['nombreColumna'] = 'SUB-ORIGEN';
                        $dato['nivel'] = 11;
                        array_push( $datos, $dato );
                        
                        $nivel11=$clave[$key]['suborigen'];
                    }
                    break;
            }

            // Definir el nivel 10
            switch($ccd_id){
                case 0:
                    $dato = array();
                    $dato['clave'] = $clave[$key]['fte_id'];
                    $dato['nombre'] = $clave[$key]['fte_nombre'];
                    $dato['nombreColumna'] = 'FUENTE';
                    $dato['nivel'] = 10;
                    array_push( $datos, $dato );
                break;
                case 1:
                    $dato = array();
                    $dato['clave'] = $clave[$key]['sft_id'];
                    $dato['nombre'] = $clave[$key]['sft_nombre'];
                    $dato['nombreColumna'] = 'SUB-FUENTE';
                    $dato['nivel'] = 10;
                    array_push( $datos, $dato );
                break;
                case 2:
                    $dato = array();
                    $dato['clave'] = $clave[$key]['ori_id'];
                    $dato['nombre'] = $clave[$key]['ori_nombre'];
                    $dato['nombreColumna'] = 'ORIGEN';
                    $dato['nivel'] = 10;
                    array_push( $datos, $dato );
                break;
                case 3:
                    $dato = array();
                    $dato['clave'] = $clave[$key]['sor_id'];
                    $dato['nombre'] = $clave[$key]['sor_nombre'];
                    $dato['nombreColumna'] = 'SUB-ORIGEN';
                    $dato['nivel'] = 10;
                    array_push( $datos, $dato );
                break;
                case 4:
                    $dato = array();
                    $dato['clave'] = $clave[$key]['cfi_id'].$clave[$key]['cfi_anio'];
                    $dato['nombre'] = $clave[$key]['cfi_nombre'];
                    $dato['nombreColumna'] = 'CLAVE FINANCIAMIENTO';
                    if ($presupuesto) {
                        $dato['ecf_poa'] = $clave[$key]['ecf_poa'];
                        $dato['ecf_division'] = $clave[$key]['ecf_division'];
                    }
                    if ($tesoreria) {
                        $dato['pdp_id'] = $clave[$key]['pdp_id'];
                    }
                    $dato['nivel'] = 10;
                    array_push( $datos, $dato );
                break;
                default:
                    echo "kym";
                    break;
            }
        }
    }
    return array_reverse($datos);
}
