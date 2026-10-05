<?php
/**
 * Description of function nivelFondeo
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Ordena los datos consultados en niveles, para hacer los movimietos bancarios en estado autorizado y de tipo traspaso, inversion y fondeo.
 * Regla de Negocio: -----
 *
 * */

require_once dirname(__FILE__) . '/consultaFondeo.php';

function nivelFondeo($tipo, $fecha, $fechaMax, $mbn_id)
{

    $clave = consultaFondeo($tipo, $fecha, $fechaMax, $mbn_id);
    $clave[0]['nivel'] = '';
    $datos = array();
    if ($clave[0]['nivel'] != 1) {
        $contador = 1;
        $cadena = "";
        $fechaAut = '';
        foreach ($clave as $key => $val) {
            if ($clave[$key]['cargo'] != '') {
                $dato = array();
                $dato['contador'] = $contador;
                $dato['cargo'] = $clave[$key]['cargo'];
                $dato['cbs_id_cargo'] = $clave[$key]['cbs_id_cargo'];
                $dato['abono'] = $clave[$key]['abono'];
                $dato['cbs_id_abono'] = $clave[$key]['cbs_id_abono'];
                $dato['importe'] = $clave[$key]['importe'];
                $dato['mbn_observacion'] = $clave[$key]['mbn_observacion'];
                $dato['mbn_fechaAutorizacion'] = $clave[$key]['mbn_fechaAutorizacion'];
                $dato['mbn_fechaMovimiento'] = $clave[$key]['mbn_fechaMovimiento'];
                $dato['mbn_id'] = $clave[$key]['mbn_id'];

                $dato['nivel'] = 10;
                array_push($datos, $dato);

                $contador++;

                $cadena .= $clave[$key]['cbs_id_abono'] . "|" . $clave[$key]['cbs_id_cargo'] . "|" . $clave[$key]['importe'] . "|" . $clave[$key]['mbn_id'];
                $fechaAut = $clave[$key]['mbn_fechaMovimiento'];
            }

        }

        $dato = array();
        $dato['cadena_original'] = md5($cadena);

        $arrFechaA = explode("-", $fechaAut);

        $mes = "";
        switch ($arrFechaA[1]) {
            case 1:
                $mes = "Enero";
                break;
            case 2:
                $mes = "Febrero";
                break;
            case 3:
                $mes = "Marzo";
                break;
            case 4:
                $mes = "Abril";
                break;
            case 5:
                $mes = "Mayo";
                break;
            case 6:
                $mes = "Junio";
                break;
            case 7:
                $mes = "Julio";
                break;
            case 8:
                $mes = "Agosto";
                break;
            case 9:
                $mes = "Septiembre";
                break;
            case 10:
                $mes = "Octubre";
                break;
            case 11:
                $mes = "Noviembre";
                break;
            case 12:
                $mes = "Diciembre";
                break;
            default:
                $mes = "s/m";
                break;
        }
        $dato['lugar_DeProcedencia'] = 'Reyes Mantecón, Oaxaca a ' . $arrFechaA[2] . ' de ' . $mes . ' del ' . $arrFechaA[0];

        $dato['nivel'] = 11;
        array_push($datos, $dato);
    }
    return array_reverse($datos);
}
