<?php

/**
 * Description of nivelCedula
 *
 * @author Israel Faustino Cruz
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Objective: Genera los niveles hasta el programa de las cedulas de costeo
 * Regla de Negocio:
 *
 * */

require_once dirname(__FILE__) . '/consultaCedula.php';

function nivelCedula($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id, $rla_id, $cve_id, $ced_id = '')
{
    $cedula = consultaCedula($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id, $rla_id, $cve_id, $ced_id);
    $cedula = array_reverse($cedula);

    $urAnterior = $cedula[0]['ur_clave'];
    $prgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'];
    $sprAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'];
    $ueAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'];
    $pyAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'];
    $oaAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'];
    $cfiAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'];
    $pgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'] . '.' . $cedula[0]['pg_id'];

    $urKey = 0;
    $prgKey = 0;
    $sprKey = 0;
    $ueKey = 0;
    $pyKey = 0;
    $oaKey = 0;
    $cfiKey = 0;
    $pgKey = 0;
    $urSuma = 0;
    $prgSuma = 0;
    $sprSuma = 0;
    $ueSuma = 0;
    $pySuma = 0;
    $oaSuma = 0;
    $cfiSuma = 0;
    $pgSuma = 0;
    $peSuma = 0;
    $pgUltima = '';
    $pg = '';
    $pe = '';

    switch ($ccd_id) {
        //case 20 = INVENTARIO DE PLAZAS POR UNIDAD EJECUTORA
        //case 21 = DESPLIEGUE POR RELACIÓN LABORAL
        case 1:
        case 3:
        case 5:
        case 6:
        case 7:
        case 9:
            $pg = true;
            $pe = true;
            $peAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'] . '.' . $cedula[0]['pg_id'] . '.' . $cedula[0]['pe_id'];
            $peKey = 0;
            $subtotal = false;
            break;
        case 2:
            break;
        case 4:
            $pe = false;
            $subtotal = true;
            break;
        case 20:
        case 21:
            $pg = false;
            $pe = false;
            $subtotal = false;
            break;
        default:
            echo "kym";
            break;
    }
    $datos = array();

    if ($cedula[0]['nivel'] != 1) {

        foreach ($cedula as $key => $val) {

            $dato = array();
            if ($pe) {
                if ($peAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id'] . '.' . $cedula[$key]['pe_id']) {
                    $dato['clave'] = $cedula[$peKey]['pe_id'];
                    $dato['nombre'] = $cedula[$peKey]['pe_nombre'];
                    $dato['importe'] = $peSuma;
                    $dato['nivel'] = 9;
                    array_push($datos, $dato);
                    $peAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id'] . '.' . $cedula[$key]['pe_id'];
                    $peKey = $key;
                    $peSuma = 0;
                }
            }
            if ($pg) {
                if ($pgAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id']) {
                    $dato['clave'] = $cedula[$key]['pg_id'];
                    $dato['nombre'] = $cedula[$key]['pg_nombre']; //*/
                    $dato['importe'] = $pgSuma;
                    $dato['nivel'] = 8;
                    array_push($datos, $dato);
                    $pgAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id'];
                    $pgKey = $key;
                    $pgSuma = 0;
                }
            }
            if ($cfiAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id']) {
                $dato['clave'] = $cedula[$cfiKey]['cfi_clave'];
                $dato['nombre'] = $cedula[$cfiKey]['cfi_nombre'];
                $dato['importe'] = $cfiSuma;
                $dato['nivel'] = 7;
                array_push($datos, $dato);
                $cfiAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'];
                $cfiKey = $key;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($oaAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id']) {
                $dato['clave'] = $cedula[$oaKey]['oa_id'];
                $dato['nombre'] = $cedula[$oaKey]['oa_nombre'];
                $dato['importe'] = $oaSuma;
                $dato['nivel'] = 6;
                array_push($datos, $dato);
                $oaAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'];
                $oaKey = $key;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($pyAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id']) {
                $dato['clave'] = $cedula[$pyKey]['py_id'];
                $dato['nombre'] = $cedula[$pyKey]['py_nombre'];
                $dato['importe'] = $pySuma;
                $dato['nivel'] = 5;
                array_push($datos, $dato);
                $pyAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'];
                $pyKey = $key;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($ueAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id']) {
                $dato['clave'] = $cedula[$ueKey]['ue_id'];
                $dato['nombre'] = $cedula[$ueKey]['ue_nombre'];
                $dato['importe'] = $ueSuma;
                $dato['nivel'] = 4;
                array_push($datos, $dato);
                $ueAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'];
                $ueKey = $key;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($sprAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id']) {
                $dato['clave'] = $cedula[$sprKey]['spr_id'];
                $dato['nombre'] = $cedula[$sprKey]['spr_nombre'];
                $dato['importe'] = $sprSuma;
                $dato['nivel'] = 3;
                array_push($datos, $dato);
                $sprAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'];
                $sprKey = $key;
                $sprSuma = 0;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($prgAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id']) {
                $dato['clave'] = $cedula[$prgKey]['prg_id'];
                $dato['nombre'] = $cedula[$prgKey]['prg_nombre'];
                $dato['nivel'] = 2;
                $dato['importe'] = $prgSuma;
                array_push($datos, $dato);
                $prgAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'];
                $prgKey = $key;
                $prgSuma = 0;
                $sprSuma = 0;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($urAnterior != $cedula[$key]['ur_clave']) {
                $dato['clave'] = $cedula[$urKey]['ur_clave'];
                $dato['nombre'] = $cedula[$urKey]['ur_nombre'];
                $dato['nivel'] = 1;
                $dato['importe'] = $urSuma;
                array_push($datos, $dato);
                $urAnterior = $cedula[$key]['ur_clave'];
                $urKey = $key;
                $urSuma = 0;
                $prgSuma = 0;
                $sprSuma = 0;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }

            $dato = array();
            $dato['cedula'] = $cedula[$key]['ced_id'];
            $dato['nivel'] = 10;
            $dato['total'] = $cedula[$key]['total'];
            switch ($ccd_id) {
                case 1:
                    $dato['asr_nombre'] = $cedula[$key]['asr_nombre'];
                    $dato['gbf_nombre'] = $cedula[$key]['gbf_nombre'];
                    $dato['ali_beneficiarios'] = $cedula[$key]['ali_beneficiarios'];
                    $dato['ali_cuotaDiaria'] = $cedula[$key]['ali_cuotaDiaria'];
                    $dato['ali_diasOtorgados'] = $cedula[$key]['ali_diasOtorgados'];
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $pgSuma += $cedula[$key]['total'];
                    $peSuma += $cedula[$key]['total'];
                    break;
                case 2:
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $dato['pe_nombre'] = $cedula[$key]['pe_nombre'];
                    $dato['epz_nombre'] = $cedula[$key]['epz_nombre'];
                    $dato['rla_nombre'] = $cedula[$key]['rla_nombre'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    break;
                case 3:
                    $dato['descripcion'] = $cedula[$key]['ccc_nombre'];
                    $dato['ubicacion'] = $cedula[$key]['ubicacion'];
                    $dato['telefono'] = $cedula[$key]['tcl_numeroCelular'];
                    $dato['mensualidad'] = $cedula[$key]['tcl_mensualidad'];
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $dato['pg_nombre'] = $cedula[$key]['pg_nombre'];
                    $pgSuma += $cedula[$key]['total'];
                    $peSuma += $cedula[$key]['total'];
                    break;
                case 4:
                    $dato['pqv_id'] = $cedula[$key]['pqv_id'];
                    $dato['uso'] = $cedula[$key]['usv_nombre'];
                    $dato['ubicacion'] = $cedula[$key]['ubicacion'];
                    $dato['tipo'] = $cedula[$key]['pqv_tipo'];
                    $dato['placas'] = $cedula[$key]['pqv_numeroPlacas'];
                    $dato['impuesto'] = $cedula[$key]['impuesto'];
                    $dato['seguro'] = $cedula[$key]['seguro'];
                    $dato['mantenimiento'] = $cedula[$key]['mantenimiento'];
                    $dato['combustible'] = $cedula[$key]['combustible'];
                    break;
                case 5:
                    $dato['arr_uso'] = $cedula[$key]['arr_uso'];
                    $dato['arr_ubicacion'] = $cedula[$key]['arr_ubicacion'];
                    $dato['arr_inicioPeriodo'] = $cedula[$key]['arr_inicioPeriodo'];
                    $dato['arr_terminoPeriodo'] = $cedula[$key]['arr_terminoPeriodo'];
                    $dato['arr_observaciones'] = $cedula[$key]['arr_observaciones'];
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $dato['pg_nombre'] = $cedula[$key]['pg_nombre'];
                    $pgSuma += $cedula[$key]['total'];
                    $peSuma += $cedula[$key]['total'];
                    break;
                case 6:
                    $dato['sef_concepto'] = $cedula[$key]['sef_concepto'];
                    $dato['sef_tipoPago'] = $cedula[$key]['sef_tipoPago'];
                    $dato['sef_inicioPeriodo'] = $cedula[$key]['sef_inicioPeriodo'];
                    $dato['sef_terminoPeriodo'] = $cedula[$key]['sef_terminoPeriodo'];
                    $dato['importe'] = $cedula[$key]['importe'];
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $pgSuma += $cedula[$key]['total'];
                    $peSuma += $cedula[$key]['total'];
                    break;
                case 7:
                    $dato['svg_area'] = $cedula[$key]['svg_area'];
                    $dato['tct_nombre'] = $cedula[$key]['tct_nombre'];
                    $dato['tct_costoMensual'] = $cedula[$key]['tct_costoMensual'];
                    $dato['cantidad'] = $cedula[$key]['cantidad'];
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $pgSuma += $cedula[$key]['total'];
                    $peSuma += $cedula[$key]['total'];
                    break;
                case 9:
                    $dato['otp_unidadMedida'] = $cedula[$key]['otp_unidadMedida'];
                    $dato['otp_costo'] = $cedula[$key]['otp_costo'];
                    $dato['otp_archivo'] = $cedula[$key]['otp_archivo'];
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $dato['pg_nombre'] = $cedula[$key]['pg_nombre'];
                    $pgSuma += $cedula[$key]['total'];
                    $peSuma += $cedula[$key]['total'];
                    break;
                case 20:
                    $dato['rla_nombre'] = $cedula[$key]['rla_nombre'];
                    $dato['Activas'] = $cedula[$key]['Activas'];
                    $dato['Vacantes'] = $cedula[$key]['Vacantes'];
                    $dato['Litigio'] = $cedula[$key]['Litigio'];
                    $dato['total'] = $cedula[$key]['total'];
                    break;
                case 21:
                    $dato['nrl_clave'] = $cedula[$key]['nrl_clave'];
                    $dato['nombre'] = $cedula[$key]['nombre'];
                    $dato['inp_activas'] = $cedula[$key]['inp_activas'];
                    $dato['inp_vacantes'] = $cedula[$key]['inp_vacantes'];
                    $dato['inp_litigio'] = $cedula[$key]['inp_litigio'];
                    $dato['total'] = $cedula[$key]['total'];
                    $dato['clave'] = $cedula[$key]['clave'];
                    $dato['nivel_relacion'] = $cedula[$key]['nivel_relacion'];
                    break;
                default:
                    echo "kym";
                    break;
            }

            array_push($datos, $dato);

            $urSuma += $cedula[$key]['total'];
            $prgSuma += $cedula[$key]['total'];
            $sprSuma += $cedula[$key]['total'];
            $ueSuma += $cedula[$key]['total'];
            $pySuma += $cedula[$key]['total'];
            $oaSuma += $cedula[$key]['total'];
            $cfiSuma += $cedula[$key]['total'];

            $pgUltima = $dato;

        }

        $dato = array();
        //telefonia imprime el nivel de partida especifica
        switch ($ccd_id) {
            case 1:
            case 3:
            case 5:
            case 6:
            case 7:
            case 9:
                $dato['clave'] = $cedula[$peKey]['pe_id'];
                $dato['nombre'] = $cedula[$peKey]['pe_nombre'];
                $dato['importe'] = $peSuma;
                $dato['nivel'] = 9;
                array_push($datos, $dato);
                $dato['clave'] = $cedula[$peKey]['pg_id'];
                $dato['nombre'] = $cedula[$peKey]['pg_nombre'];
                $dato['importe'] = $pgSuma;
                $dato['nivel'] = 8;
                array_push($datos, $dato);
                break;
            case 2:
            case 4:
            case 20:
            case 21:
                break;
            default:
                echo "kym";
                break;
        }

        if ($ccd_id != 20 && $ccd_id != 21) {
            $dato['clave'] = $cedula[$cfiKey]['cfi_clave'];
            $dato['nombre'] = $cedula[$cfiKey]['cfi_nombre'];
            $dato['importe'] = $cfiSuma;
            $dato['nivel'] = 7;
            array_push($datos, $dato);

            $dato['clave'] = $cedula[$oaKey]['oa_id'];
            $dato['nombre'] = $cedula[$oaKey]['oa_nombre'];
            $dato['importe'] = $oaSuma;
            $dato['nivel'] = 6;
            array_push($datos, $dato);

            $dato['clave'] = $cedula[$pyKey]['py_id'];
            $dato['nombre'] = $cedula[$pyKey]['py_nombre'];
            $dato['importe'] = $pySuma;
            $dato['nivel'] = 5;
            array_push($datos, $dato);

            $dato['clave'] = $cedula[$ueKey]['ue_id'];
            $dato['nombre'] = $cedula[$ueKey]['ue_nombre'];
            $dato['importe'] = $ueSuma;
            $dato['nivel'] = 4;
            array_push($datos, $dato);

            $dato['clave'] = $cedula[$sprKey]['spr_id'];
            $dato['nombre'] = $cedula[$sprKey]['spr_nombre'];
            $dato['importe'] = $sprSuma;
            $dato['nivel'] = 3;
            array_push($datos, $dato);

            $dato['clave'] = $cedula[$prgKey]['prg_id'];
            $dato['nombre'] = $cedula[$prgKey]['prg_nombre'];
            $dato['importe'] = $prgSuma;
            $dato['nivel'] = 2;
            array_push($datos, $dato);

            $dato['clave'] = $cedula[$urKey]['ur_clave'];
            $dato['nombre'] = $cedula[$urKey]['ur_nombre'];
            $dato['importe'] = $urSuma;
            $dato['nivel'] = 1;
            array_push($datos, $dato);
        }
    } else {
        $dato['clave'] = $cedula[0]['ur_clave'];
        $dato['nombre'] = $cedula[0]['ur_nombre'];
        $dato['importe'] = $urSuma;
        $dato['nivel'] = 1;
        $dato['rows'] = 0;
        array_push($datos, $dato);
    }

    return $datos;
}
