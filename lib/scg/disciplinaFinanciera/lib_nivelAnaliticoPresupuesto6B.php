<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lib_nivelAnaliticoPresupuesto6B
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 25-abr-2019
 * Comment: 
 * 
 * */
function lib_nivelAnaliticoPresupuesto6B($datos, $nivel){
    //put your code here
    if(isset($datos)){
        //if( $niveles[0] != 1 )
        $datos = array_reverse($datos, true);
        
        $sumaAprobado = 0;
        $sumaAmpRed = 0;
        $sumaModificado = 0;
        $sumaDevengado = 0;
        $sumaPagado = 0;
        $sumaSub = 0;
        
        switch($nivel){
            case 2:
                //reiniciamos las variables
                $cff_clasificador = '';
                $sumaClasificadorAprobado = 0;
                $sumaClasificadorAmpRed = 0;
                $sumaClasificadorModificado = 0;
                $sumaClasificadorDevengado = 0;
                $sumaClasificadorPagado = 0;
                $sumaClasificadorSub = 0;
                $registros = array();
                $registro = '';
                
                //generamos el primer nivel
                foreach($datos as $key => $value){
                    $var = explode('.', $key);
                    /* echo "1-<pre>";
                      print_r($var);
                      echo "</pre></br>";die();// */
                    $cuentaNivel = $var[0].'.'.$var[1];
                    //$cuentaNivel = ( $agrupador == 1 ? $var[0].'.':'').$var[1];
                    //echo $cuentaNivel.'-'.$cff_clasificador.'</br>';
                    if($cuentaNivel != $cff_clasificador && $cff_clasificador != ''){
                        $registros[$cff_clasificador] = $registro;
                        $registros[$cff_clasificador]['aprobado'] = $sumaClasificadorAprobado;
                        $registros[$cff_clasificador]['ampred'] = $sumaClasificadorAmpRed;
                        $registros[$cff_clasificador]['modificado'] = $sumaClasificadorModificado;
                        $registros[$cff_clasificador]['devengado'] = $sumaClasificadorDevengado;
                        $registros[$cff_clasificador]['pagado'] = $sumaClasificadorPagado;
                        $registros[$cff_clasificador]['subejercicio'] = $sumaClasificadorSub;
                        
                        $sumaClasificadorAprobado = 0;
                        $sumaClasificadorAmpRed = 0;
                        $sumaClasificadorModificado = 0;
                        $sumaClasificadorDevengado = 0;
                        $sumaClasificadorPagado = 0;
                        $sumaClasificadorSub = 0;
                    }
                    
                    $registro = $datos[$key];
                    //$cuenta = $datos[$key]['ger_id'];
                    //$registro['id'] = 'C'.$cuenta;
                    $registro['cuenta'] = $datos[$key]['cff_cuenta'];
                    //$registro['cuenta'] = $cuenta;
                    $registro['nombre'] = $datos[$key]['cff_nombre'];
                    $registro['nivel'] = 1;
                    
                    if($datos[$key]['nivel'] == $nivel){
                        $sumaClasificadorAprobado = $sumaClasificadorAprobado + $datos[$key]['aprobado'];
                        $sumaClasificadorAmpRed = $sumaClasificadorAmpRed + $datos[$key]['ampred'];
                        $sumaClasificadorModificado = $sumaClasificadorModificado + $datos[$key]['modificado'];
                        $sumaClasificadorDevengado = $sumaClasificadorDevengado + $datos[$key]['devengado'];
                        $sumaClasificadorPagado = $sumaClasificadorPagado + $datos[$key]['pagado'];
                        $sumaClasificadorSub = $sumaClasificadorSub + $datos[$key]['subejercicio'];
                        
                        $sumaAprobado = $sumaAprobado + $datos[$key]['aprobado'];
                        $sumaAmpRed = $sumaAmpRed + $datos[$key]['ampred'];
                        $sumaModificado = $sumaModificado + $datos[$key]['modificado'];
                        $sumaDevengado = $sumaDevengado + $datos[$key]['devengado'];
                        $sumaPagado = $sumaPagado + $datos[$key]['pagado'];
                        $sumaSub = $sumaSub + $datos[$key]['subejercicio'];
                    }
                    
                    $registros[$key] = $datos[$key];
                    
                    #$cff_clasificador = substr( $key, 0, 1 );
                    $cff_clasificador = 'P.'.$registro['cuenta'];
                    //$cff_clasificador = $registro['cta_gpo_id'].$registro['cta_ur_id'].'.'.$registro['cuenta'];
                }
                
                $registros[$cff_clasificador] = $registro;
                $registros[$cff_clasificador]['aprobado'] = $sumaClasificadorAprobado;
                $registros[$cff_clasificador]['ampred'] = $sumaClasificadorAmpRed;
                $registros[$cff_clasificador]['modificado'] = $sumaClasificadorModificado;
                $registros[$cff_clasificador]['devengado'] = $sumaClasificadorDevengado;
                $registros[$cff_clasificador]['pagado'] = $sumaClasificadorPagado;
                $registros[$cff_clasificador]['subejercicio'] = $sumaClasificadorSub;
                
                //guardamos el resultado en el arreglo original
                $datos = $registros;
                
                //echo 'N1<pre>';print_r($datos);echo '</pre>';die();
                break;
            case 1:
                // nothing;
                break;
            default:
                echo 'kym 4 ';
                break;
        }
    }
    
    $datos = array_reverse($datos, true);
    
    $datos['total']['id'] = 'total';
    $datos['total']['cff_cuenta'] = '';
    $datos['total']['cff_nombre'] = '';
    $datos['total']['ur_clave'] = '';
    $datos['total']['ur_nombre'] = '';
    $datos['total']['cuenta'] = '';
    $datos['total']['nombre'] = 'TOTAL';
    $datos['total']['cgm_naturaleza'] = '';
    $datos['total']['aprobado'] = redondear($sumaAprobado, 2);
    $datos['total']['ampred'] = redondear($sumaAmpRed, 2);
    $datos['total']['modificado'] = redondear($sumaModificado, 2);
    $datos['total']['devengado'] = redondear($sumaDevengado, 2);
    $datos['total']['pagado'] = redondear($sumaPagado, 2);
    $datos['total']['subejercicio'] = redondear($sumaSub, 2);
    $datos['total']['nivel'] = 1;
    
    return isset($datos) ? $datos:FALSE;
}

?>