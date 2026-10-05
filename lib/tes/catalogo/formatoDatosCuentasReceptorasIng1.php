<?php    
/**
 * Description of function formatoDatosCuentasReceptorasIng1
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 05-Nov-2018
 * Comment: -----
 * Objective: Ordena los datos consultados para mostrar la relación de cuentas para configurar.
 * Regla de Negocio: -----
 * 
 * */

require_once dirname(__FILE__).'/consultaCuentasReceptorasIng1.php';

function formatoDatosCuentasReceptorasIng1()
{
    $cdatos = consultaCuentasReceptorasIng1();

    $b = "";
    $zbc = array(
        '0' => 'ZBCOEGR',
        '1' => 'ZBCOING',
        '2' => 'ZBCOINT',
        '3' => 'ZINTS',
        '4' => 'ZINV',
    );

    $datos = array();
    if (sizeof($cdatos)>0) {
        foreach ($cdatos as $key=>$value) {
            if ($cdatos[$key]['iba_abreviatura'] != $b) {
                $dato = array('iba_abreviatura' => '', 'cbs_cuenta' => '', 'mxn' => '', 'egr_ing' => '', 'sap_inversion' => '', 'cbs_nombre'=>'');
                $b = $cdatos[$key]['iba_abreviatura'];
                $dato['iba_abreviatura'] = $b;
                array_push( $datos, $dato );
            }
            foreach ($zbc as $key2=> $rowArray) {
                $dato = array('iba_abreviatura'=>'','cbs_cuenta'=>'','mxn'=>'','egr_ing'=>'','sap_inversion'=>'','cbs_nombre'=>'','cta_cheques'=>'');
                if ($cdatos[$key]['iba_id'] == 1 or $cdatos[$key]['iba_id'] == 3) {
                    $dato['iba_abreviatura'] = $rowArray;
                    if ($key2 != 3) {
                        $dato['cbs_cuenta'] = "'" . $cdatos[$key]['inversion'];
                    }
                    $dato['mxn'] = 'MXN';
                    if ($key2 != 3) {
                        $dato['egr_ing'] = "'" . $cdatos[$key]['sap_inversion'];
                        $dato['cbs_nombre'] = $cdatos[$key]['cbs_nombre'];
                    } else {
                        $dato['cta_cheques'] = "CTA. DE CHEQUES";
                    }
                    if ($key2 == 4) {
                        $dato['cta_cheques'] = "'".$cdatos[$key]["cbs_cuenta"];
                    }
                    array_push( $datos, $dato );
                } else {
                    $dato['iba_abreviatura'] = $rowArray;
                    if ($key2 != 3 && $key2 !=4) {
                        $dato['cbs_cuenta'] = "'".$cdatos[$key]["cbs_cuenta"];
                    } else if ($key2 == 4) {
                        $dato['cbs_cuenta'] = "'".$cdatos[$key]["inversion"];
                    }
                    $dato['mxn'] = "MXN";
                    if ($key2 == 0) {
                        $dato['egr_ing'] = "'" . $cdatos[$key]["egr"];
                    }
                    if ($key2 == 1 || $key2 == 2) {
                        $dato['egr_ing'] = "'" . $cdatos[$key]["ing"];
                    }
                    if ($key2 == 4) {
                        $dato['egr_ing'] = "'" . $cdatos[$key]["sap_inversion"];
                    }
                    if ($key2 != 3) {
                        $dato['cbs_nombre'] = $cdatos[$key]["cbs_nombre"];
                    }
                    array_push( $datos, $dato );
                }
            }
        }
    }
    return $datos;
}
