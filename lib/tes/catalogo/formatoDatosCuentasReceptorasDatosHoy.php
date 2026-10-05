<?php    
/**
 * Description of function formatoDatosCuentasReceptorasDatosHoy
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 06-Nov-2018
 * Comment: -----
 * Objective: Ordena los datos consultados para mostrar cuentas que se daran de alta para conciliar.
 * Regla de Negocio: -----
 * 
 * */

require_once dirname(__FILE__).'/consultaCuentasReceptorasDatosHoy.php';

function formatoDatosCuentasReceptorasDatosHoy()
{
    $cdatos = consultaCuentasReceptorasDatosHoy();
    $b = "";

    $datos = array();
    foreach ($cdatos as $key=>$value) {
        if ($cdatos[$key]['iba_abreviatura'] != $b) {
            $dato = array('cff_id' => '', 'fte_id' => '', 'sft_id' => '');
            $b = $cdatos[$key]['iba_abreviatura'];
            array_push($datos, $dato);
            $dato['cff_id'] = $b;
            array_push($datos, $dato);
            $dato['cff_id'] = 'CUENTA BANCARIA';
            $dato['fte_id'] = 'CLAVE DEL BANCO';
            $dato['sft_id'] = 'NOMBRE DE LA CUENTA';
            array_push($datos, $dato);
        }
        $dato = array('cff_id' => '', 'fte_id' => '', 'sft_id' => '');
        // Ojo: Del ticket #518893, para que muestre correctamente la cuenta bancaria si pertenece 
        //      a "Banco Azteca, S.A."
        $dato['cff_id'] = (substr($cdatos[$key]['cbs_id'], 0, 3) == 127) ?
            $cdatos[$key]['cbs_cuenta'] :
            substr($cdatos[$key]['cbs_cuenta'], 3);
        $dato['fte_id'] = $cdatos[$key]['claveBanco'];
        $dato['sft_id'] = $cdatos[$key]['cbs_nombre'];
        array_push( $datos, $dato );
    }
    return $datos;
}
