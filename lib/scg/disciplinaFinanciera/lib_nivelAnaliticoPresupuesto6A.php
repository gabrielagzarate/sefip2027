<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lib_nivelAnaliticoPresupuesto6A
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 25-abr-2019
 * Comment: 
 * 
 * */
function lib_nivelAnaliticoPresupuesto6A($datos, $nivel){
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
            case 3:
                //reiniciamos las variables
                $cap_capitulo = '';
                $sumaCapituloAprobado = 0;
                $sumaCapituloAmpRed = 0;
                $sumaCapituloModificado = 0;
                $sumaCapituloDevengado = 0;
                $sumaCapituloPagado = 0;
                $sumaCapituloSub = 0;
                $registros = array();
                $registro = '';
                
                //generamos el segundo nivel
                foreach($datos as $key => $value){
                    $var = explode('.', $key);
                    //echo '<pre>';print_r($var);echo '</pre></br>';
                    //echo $var[1].'.'.$var[1].'-'.$cap_capitulo.'</br>';
                    /* echo "2-<pre>";
                      print_r($var);
                      echo "</pre></br>";// */
                    $cuentaNivel = $var[0].'.'.$var[1].'.'.$var[2];
                    //echo $cuentaNivel.'-'.$cap_capitulo.'</br>';
                    if($cuentaNivel != $cap_capitulo && $cap_capitulo != ''){
                        $registros[$cap_capitulo] = $registro;
                        $registros[$cap_capitulo]['aprobado'] = $sumaCapituloAprobado;
                        $registros[$cap_capitulo]['ampred'] = $sumaCapituloAmpRed;
                        $registros[$cap_capitulo]['modificado'] = $sumaCapituloModificado;
                        $registros[$cap_capitulo]['devengado'] = $sumaCapituloDevengado;
                        $registros[$cap_capitulo]['pagado'] = $sumaCapituloPagado;
                        $registros[$cap_capitulo]['subejercicio'] = $sumaCapituloSub;
                        
                        $sumaCapituloAprobado = 0;
                        $sumaCapituloAmpRed = 0;
                        $sumaCapituloModificado = 0;
                        $sumaCapituloDevengado = 0;
                        $sumaCapituloPagado = 0;
                        $sumaCapituloSub = 0;
                    }
                    
                    $registro = $datos[$key];
                    //$cuenta = $datos[$key]['ger_id'].'.'.$datos[$key]['cgp_id'];
                    //$registro['id'] = 'C'.$cuenta;
                    $registro['cuenta'] = $datos[$key]['cap_cuenta'];
                    //$registro['cuenta'] = $cuenta;
                    $registro['nombre'] = $datos[$key]['cap_nombre'];
                    $registro['nivel'] = 2;
                    
                    if($datos[$key]['nivel'] == $nivel){
                        $sumaCapituloAprobado = $sumaCapituloAprobado + $datos[$key]['aprobado'];
                        $sumaCapituloAmpRed = $sumaCapituloAmpRed + $datos[$key]['ampred'];
                        $sumaCapituloModificado = $sumaCapituloModificado + $datos[$key]['modificado'];
                        $sumaCapituloDevengado = $sumaCapituloDevengado + $datos[$key]['devengado'];
                        $sumaCapituloPagado = $sumaCapituloPagado + $datos[$key]['pagado'];
                        $sumaCapituloSub = $sumaCapituloSub + $datos[$key]['subejercicio'];
                    }
                    
                    $registros[$key] = $datos[$key];
                    
                    #$cap_capitulo = substr( $key, 0, 3 );
                    $cap_capitulo = 'P.'.$registro['cuenta'];
                    //$cap_capitulo = $registro['cta_gpo_id'].$registro['cta_ur_id'].'.'.$registro['cuenta'];
                }
                
                $registros[$cap_capitulo] = $registro;
                $registros[$cap_capitulo]['aprobado'] = $sumaCapituloAprobado;
                $registros[$cap_capitulo]['ampred'] = $sumaCapituloAmpRed;
                $registros[$cap_capitulo]['modificado'] = $sumaCapituloModificado;
                $registros[$cap_capitulo]['devengado'] = $sumaCapituloDevengado;
                $registros[$cap_capitulo]['pagado'] = $sumaCapituloPagado;
                $registros[$cap_capitulo]['subejercicio'] = $sumaCapituloSub;
                
                //guardamos el resultado en el arreglo original
                $datos = $registros;
                
                //echo 'N2<pre>';print_r($datos);echo '</pre>';die();
                //break;
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
    $datos['total']['cap_cuenta'] = '';
    $datos['total']['cap_nombre'] = '';
    $datos['total']['cto_cuenta'] = '';
    $datos['total']['cto_nombre'] = '';
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