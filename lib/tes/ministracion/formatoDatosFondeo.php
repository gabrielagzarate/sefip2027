<?php    
/**
 * Description of function formatoDatosFondeo
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Ordena los datos consultados de los movimietos bancarios en estado autorizado y de tipo traspaso, inversion y fondeo.
 * Regla de Negocio: -----
 * 
 * */
    
require_once dirname(__FILE__).'/consultaFondeo.php';
    
function formatoDatosFondeo($tipo, $fecha, $fechaMax, $mbn_id){
    $cdatos = consultaFondeo($tipo, $fecha, $fechaMax, $mbn_id);

    $contador=1;
    $datos=array();

    foreach($cdatos as $key=>$value){
        $dato = array();
        $dato['contador'] = $contador;
        $dato['cargo'] = $cdatos[$key]['cargo'];
        $dato['cbs_id_cargo'] = "'".$cdatos[$key]['cbs_id_cargo'];
        $dato['abono'] = $cdatos[$key]['abono'];
        $dato['cbs_id_abono'] = "'".$cdatos[$key]['cbs_id_abono'];
        $dato['importe'] = $cdatos[$key]['importe'];
        $dato['mbn_observacion'] = $cdatos[$key]['mbn_observacion'];
        $dato['mbn_fechaAutorizacion'] = $cdatos[$key]['mbn_fechaAutorizacion'];
        $dato['mbn_fechaMovimiento'] = $cdatos[$key]['mbn_fechaMovimiento'];
        $dato['mbn_id'] = $cdatos[$key]['mbn_id'];

        array_push( $datos, $dato );

        $contador++;
    }
    return $datos;
}
?>