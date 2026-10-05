<?php
require_once dirname(__FILE__) . '/vistaScp.php';

class vistaAfectacion extends vistasScp
{
    public function ponBotonesVarios($n, $p = null)
    {
        switch ($n) {
            case "regresan1":
                $btnRegresarIndex = new stdClass();
                $btnRegresarIndex->id = 'btnRegresarIndex';
                $btnRegresarIndex->text = 'Regresar';
                $btnRegresarIndex->value = 'Regresar';
                $btnRegresarIndex->color = 'gris';
                $btnRegresarIndex->icon = 'circle-left me-2';
                switch ($p['num']) {
                    default:
                        $btnRegresarIndex->click = '$("nav").menusinpres("clickMenu");';
                        break;
                    case 1:
                        $btnRegresarIndex->click = '
                            $("div[aria-describedby=cargaDatosProgFin]").html("");
                            comunica("scp/afectaciones/ControladorAfectacion.php",{
                                metodo:"main",
                                tipo:"' . $p['tipo'] . '",
                                nombre:"' . $p['nombre'] . '"}
                            );
                        ';
                        break;
                    case 2:
                        $btnRegresarIndex->click = '
                            comunica("scp/afectaciones/ControladorAfectacion.php",{
                                metodo:"afectacion",
                                afe_id:"' . $p['afe_id'] . '",
                                tipo:"' . $p['tipo'] . '" }); ';
                        break;
                }
                return $btnRegresarIndex;
                break;
            case "nuevon1":
                $btnnuevo = new stdClass();
                $btnnuevo->id = 'btnnuevo';
                $btnnuevo->class = 'me-1';
                $btnnuevo->icon = 'plus me-2';
                $btnnuevo->name = 'nuevo';
                $btnnuevo->color = 'azul';
                $btnnuevo->text = 'Nuevo';
                $btnnuevo->value = 'Nuevo';
                $btnnuevo->click = '
                    var s = "";
                    $(".tcl_idfont").each(function(){
                        if($(this).hasClass("tcl_ida")) {
                            s =$(this).attr("id");
                        }
                    });
                    if (s =="") {
                        notify("Debe seleccionar un tipo de afectación!!","warning");
                    } else {
                        comunica("scp/afectaciones/ControladorAfectacion.php",{metodo:"nuevo",tipo:s});
                    }';
                return $btnnuevo;
                break;
            case "autorizan1":
                $btnautoriza = new stdClass();
                $btnautoriza->id = 'btnautoriza';
                $btnautoriza->icon = 'check me-2';
                $btnautoriza->name = 'autoriza';
                $btnautoriza->color = 'verde';
                $btnautoriza->text = 'Autorizar';
                $btnautoriza->value = 'Autorizar';
                $btnautoriza->class = "me-1";
                $btnautoriza->click = '
                    var s = "";
                    n="";
                    $(".tcl_idfont").each(function(){
                        if($(this).hasClass("tcl_ida")){
                            s =$(this).attr("id");
                            nombre=$(this).attr("nombre");
                        }
                    });
                    if (s == "") {
                        notify("Debe seleccionar un tipo de afectación!!","warning");
                    } else {
                        var afes = $("#tbl_Conceptos").tabla("getFilaSeleccion");

                        if ( ! afes )
                            return false;

                        var mensaje = "";
                        if (afes.length > 1 )
                            mensaje = " las afectaciones seleccionadas ";
                        else
                            mensaje = " la afectación seleccionada";

                        alertify.confirm(
                            "AUTORIZAR AFECTACION", 
                            "Confirma que desea autorizar " + mensaje ,
                            function(){
                                    comunica(
                                        "scp/afectaciones/ControladorAfectacion.php",{
                                            metodo:"AutorizaAfectacion",
                                            data:$("#tbl_Conceptos").tabla("getFilaSeleccion"),
                                            tipo:s,
                                            nombre:nombre
                                        },false,{
                                            "mostrar":true,"mensaje":"Procesando su(s) Afectacion(es), espere unos momentos ..."
                                        });
                                },
                            function(){},
                        ).set("defaultFocus","cancel").set("labels", {ok:"Autorizar", cancel:"Cancelar"});
                    }';
                return $btnautoriza;
                break;
            case "imprimen1":
                $btnimprime = new stdClass();
                $btnimprime->id = 'btnimprime';
                $btnimprime->class = 'me-1';
                $btnimprime->icon = 'fa-solid fa-file-pdf';
                $btnimprime->color = 'rojo';

                $btnimprime->click = '
                    var s = "";
                    n = "";
                    $(".tcl_idfont").each(function() {
                        if ($(this).hasClass("tcl_ida")) {
                            s = $(this).attr("id");
                            nombre = $(this).attr("nombre");
                        }
                    });
                    if (s == "") {
                        notify("Debe seleccionar un tipo de afectación!!","warning");
                    } else {
                        comunica("scp/afectaciones/ControladorAfectacion.php",{
                            metodo:"imprimir",
                            tipo:s,
                            nombre:nombre
                        },false,true);
                    }
                ';
                return $btnimprime;
                break;
            case "cargaMasiva":
                $btncargamasiva = new stdClass();
                $btncargamasiva->id = "btn_carga";
                $btncargamasiva->name = "btn_carga";
                $btncargamasiva->color = "rojo";
                $btncargamasiva->value = "Carga masiva";
                $btncargamasiva->class = "me-1";
                $btncargamasiva->click = 'comunica("scp/admon/ControladorCargaMasiva.php",{ tc : "1"});';
                return $btncargamasiva;
                break;
            case "btnEliminar":
                $btnEliminar = new stdClass();
                $btnEliminar->id = 'btnEliminar';
                $btnEliminar->color = "rojo";
                $btnEliminar->value = 'Eliminar';
                $btnEliminar->icon = 'trash me-2';
                $btnEliminar->click = '
                    alertify.confirm(
                        "BORRAR LA AFECTACION", 
                        "Confirme que desea eliminar la Afectación ' . $p['afe_id'] . ' ",
                        function(){
                            comunica("scp/afectaciones/ControladorAfectacion.php",{
                                metodo:"abcAfe",
                                abc:"b",
                                afe_id:"' . $p['afe_id'] . '",
                                tipo:"' . $p['tipo'] . '"
                            });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                ';
                return $btnEliminar;
                break;
            case "eliminaDetalleAfe":
                $btnEliminar = new stdClass();
                $btnEliminar->id = 'btnEliminarDetalle';
                $btnEliminar->color = "rojo";
                $btnEliminar->value = 'Eliminar Todos los Registros';
                $btnEliminar->icon = 'trash me-2';
                $btnEliminar->click = '
                    alertify.confirm(
                        "BORRAR DETALLES DE LA AFECTACION", 
                        "Confirme que desea borrar todos los numeros de control del a Afectacion ' . $p['afe_id'] . ' ",
                        function(){
                            comunica("scp/afectaciones/ControladorAfectacion.php",{
                                metodo:"eliminaDetalleAfe",
                                abc:"b",
                                afe_id:"' . $p['afe_id'] . '",
                                tipo:"' . $p['tipo'] . '",
                            });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                ';
                return $btnEliminar;
                break;
            case "cancelarAfe":
                $btnCancelar = new stdClass();
                $btnCancelar->id = 'btnCancelar';
                $btnCancelar->name = 'btnCancelar';
                $btnCancelar->color = "gris";
                $btnCancelar->value = 'Cancelar';
                $btnCancelar->icon = 'times me-2';
                $btnCancelar->click = '
                    alertify.confirm(
                        "CANCELAR AFECTACION", 
                        "La Afectación ya no será Visible ni usable! ¿Confirma que desea Cancelar la Afectación?",
                        function(){
                            comunica("scp/afectaciones/ControladorAfectacion.php",{metodo:"CancelaAfectacion",afe_id:' . $p . '
                            });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                ';
                return $btnCancelar;
                break;
            case "guardarAfe":
                $btnGuardar = new stdClass();
                $btnGuardar->id = 'btn_guardar';
                $btnGuardar->class = 'me-1';
                $btnGuardar->color = "verde";
                $btnGuardar->icon = "floppy-disk me-2";
                if ($p['abc'] == "a") {
                    $btnGuardar->value = 'Guardar';
                } else {
                    $btnGuardar->value = 'Actualizar';
                }
                $btnGuardar->click = ' anticipo = true;';
                //Aqui es donde estoy validando la afe de tipo G y le añadi a la tipo H la validacion del stc_id, osea para que quedara igual que la "G"
                if ($p['tipo'] == "G") {
                    $btnGuardar->click .= '
                        if ($("#stc_id").val() != "00") {
                            if ($("#ipu_id").val() == "" || $("#ipu_id").val() == undefined) {
                                notify("Es necesario un contrato para la Afectación de Anticipo de Obra","error");
                                return false;
                            }
                        }' . PHP_EOL;
                }

                if ($p['tipo'] == "F") {
                    $btnGuardar->click .= '
                        if ($("#stc_id").val() != "02") {
                            if ($("#ipu_id").val() == "" || $("#ipu_id").val() == undefined) {
                                notify("Es necesario un contrato para la Afectación de Anticipo de Obra","error");
                                return false;
                            }
                        }' . PHP_EOL;
                }

                if ($p['tipo'] == "H") {
                    $btnGuardar->click .= '
                        if ($("#stc_id").val() != "00") {
                            if( $("#ipu_id").val() == "" || $("#ipu_id").val() == undefined) {
                                notify("Es necesario un contrato para la Afectación de Anticipo de Obra","error");
                                return false;
                            }
                        }' . PHP_EOL;
                }

                $btnGuardar->click .= '
                    var smn = $("#smn_id").find("option:selected").val();
                    if(smn=="")
                        smn="0"' . PHP_EOL;
                if ($p['abc'] == "A") {
                    $btnGuardar->click .= '
                        if(!validarFechas($("#fecha_in").val(),$("#fecha_fin").val())){
                            notify("El periodo es incorrecto La fecha final debe ser mayor'
                        . ' o igual a la fecha de incio","error");
                            return false;
                        }' . PHP_EOL;
                }

                $btnGuardar->click .= '
                    fondoR = "N";
                    if ($("#squaredTwo").length != 0) {
                        if ($("#squaredTwo").is(":checked")){
                            fondoR="S";
                        } else {
                            fondoR="N";
                        }
                    }' . PHP_EOL;
                $tcp_id = '';

                if ($p['tipo'] == "B") {
                    $tcp_id = 'tcp_id:$("#tcp_id").find("option:selected").val(),';
                }

                $btnGuardar->click .= '
                    if (anticipo == true) {
                        comunica("scp/afectaciones/ControladorAfectacion.php",{
                            metodo:"abcAfe",
                            tcl_id:"' . $p['tipo'] . '",
                            ' . $tcp_id . '
                            abc:"' . $p['abc'] . '",
                            afe_id:"' . $p['afe_id'] . '",
                            stc_id:$("#stc_id").find("option:selected").val(),
                            tno_id:$("#tno_id").find("option:selected").val(),
                            ' . ($p['princesa'] ? '' : 'mno_id:$("#mno_id").find("option:selected").val(),') . '
                            fechaInicio:$("#fecha_in").val(),
                            fechaFin:$("#fecha_fin").val(),
                            fondoRotatorio:fondoR,
                            afe_clc:$("#clc_pag").val(),
                            smn:smn,
                            ecf_id:$("#cveFin_id").find("option:selected").val(),
                            afe_ipu_id:$("#ipu_id").val()
                        },true);
                    }
                ';
                return $btnGuardar;
                break;
            case "botonNvoPresupuestal":
                $btnpresupuestal = new stdClass();
                $btnpresupuestal->id = 'presupuestal';
                $btnpresupuestal->name = 'presupuestal';
                $btnpresupuestal->class = "info me-1";
                $btnpresupuestal->text = '+ Presupuestal';
                $btnpresupuestal->color = 'verde';
                $btnpresupuestal->value = '+ Presupuestal';
                $btnpresupuestal->click = '
                    comunica("scp/afectaciones/ControladorAfectacion.php",{
                        metodo:"nuevoPresupuestal",
                        afe_id:"' . $p['afe_id'] . '",
                        type:"presupuestal",
                        tipo:"' . $p['tipo'] . '"
                    });';
                return $btnpresupuestal;
                break;
            case "botonNvoContable":
                $btncontable = new stdClass();
                $btncontable->id = 'contable';
                $btncontable->name = 'contable';
                $btncontable->class = "info me-1";
                $btncontable->color = 'verde';
                $btncontable->text = '+ Contable';
                $btncontable->value = '+ Contable';
                $btncontable->click = '
                    comunica("scp/afectaciones/ControladorAfectacion.php",{
                        metodo:"nuevoContable",
                        afe_id:"' . $p['afe_id'] . '",
                        type:"contable",
                        tipo:"' . $p['tipo'] . '"
                    });';
                return $btncontable;
                break;
            default:
                print_r("MEGA ERROR " . $n);
                die();
                break;
        }
    }

    public function getControlVarios($n, $prm = null)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        switch ($n) {
            case "tipopag":
                $tipopag = new stdClass();
                $tipopag->id = "stc_id";
                $tipopag->name = "control";
                $tipopag->required = true;
                $tipopag->enDiv = true;
                $tipopag->camposVal = "stc_id";
                $tipopag->agrupaPor = "stc_id";
                $tipopag->campoLabelAgrupacion = "stc_nombre";
                $tipopag->mensaje = "Selecciona";
                $tipopag->label = "SubTipo CLC";
                $tipopag->comoAtr = "stc_id";
                if ($prm['tipo'] == "K" && isset($prm['ue_id']) && $prm['ue_id'] != "003") {
                    $tipopag->disabled = "disabled";
                }

                switch ($gpo_id . $ur_id) {
                    case 901:
                        if ($prm['tipo'] == "C") {
                            $tipopag->sql = '
                                SELECT stc_id,stc_nombre
                                FROM cat_SubtipoClc WHERE tcl_id = :tcl AND stc_id = 05;';
                        } else {
                            $tipopag->sql = '
                                SELECT stc_id,stc_nombre FROM cat_SubtipoClc
                                WHERE tcl_id = :tcl';
                        }
                        $params = [
                            ':tcl' => $prm['tipo'],
                        ];
                        $tipopag->params = $params;
                        break;
                    case 902:
                        if ($prm['tipo']) {
                            $tipopag->sql = '
                                SELECT stc_id,stc_nombre
                                FROM cat_SubtipoClc
                                WHERE tcl_id = :tcl
                                    AND stc_id in(03,04);';
                        } else {
                            $tipopag->sql = '
                                SELECT stc_id,stc_nombre
                                FROM cat_SubtipoClc WHERE tcl_id = :tcl';
                        }
                        $params = [
                            ':tcl' => $prm['tipo'],
                        ];
                        $tipopag->params = $params;
                        break;
                    default:
                        $tipopag->sql = '
                            SELECT stc_id,stc_nombre FROM cat_SubtipoClc
                            WHERE tcl_id = :tcl ' . ($prm['tipo'] == "C" ? ' AND stc_id!=05' : '') . ';';

                        if ($prm['tipo'] == "H") {
                            $tipopag->sql = '
                                SELECT stc_id,"NO APLICA " AS stc_nombre
                                FROM cat_SubtipoClc WHERE tcl_id = :tcl AND stc_id=00;';
                        }
                        $params = [
                            ':tcl' => $prm['tipo'],
                        ];
                        $tipopag->params = $params;
                        break;
                }
                return $tipopag;
                break;
            case "obra":
                $obra = new stdClass();
                $obra->id = "ipu_id";
                $obra->name = "control";
                $obra->required = true;
                $obra->enDiv = true;
                $obra->camposVal = "ipu_id";
                $obra->agrupaPor = "ipu_id";
                $obra->campoLabelAgrupacion = "ipu_numeroContrato";
                $obra->mensaje = "Selecciona";
                $obra->label = "Contrato de Obra";
                $obra->comoAtr = "ipu_id";
                return $obra;
                break;
            case "tipocompra":
                $tipocompra = new stdClass();
                $tipocompra->id = "tcp_id";
                $tipocompra->name = "control";
                $tipocompra->required = true;
                $tipocompra->enDiv = true;
                $tipocompra->camposVal = "tcp_id";
                $tipocompra->agrupaPor = "tcp_id";
                $tipocompra->campoLabelAgrupacion = "tcp_nombre";
                $tipocompra->mensaje = "Selecciona";
                $tipocompra->label = "Tipo de compra";
                $tipocompra->comoAtr = "tcp_id";
                $tipocompra->sql = '
                    SELECT tcp_id,tcp_nombre
                    FROM cat_TipoCompra WHERE tcp_id!="P";
                ';
                return $tipocompra;
                break;
            case "tipoNom":
                $tipoNom = new stdClass();
                $tipoNom->id = "tno_id";
                $tipoNom->name = "control";
                $tipoNom->required = true;
                $tipoNom->enDiv = true;
                $tipoNom->camposVal = 'tno_id';
                $tipoNom->agrupaPor = 'tno_id';
                $tipoNom->campoLabelAgrupacion = 'tno_nombre';
                $tipoNom->mensaje = 'Selecciona  un tipo de nómina';
                $tipoNom->label = 'Tipo de Nómina';
                $tipoNom->comoAtr = "tno_id";
                return $tipoNom;
                break;
            case "modalidad":
                $modalidad = new stdClass();
                $modalidad->id = 'mno_id';
                $modalidad->name = "control";
                $modalidad->required = true;
                $modalidad->enDiv = true;
                $modalidad->camposVal = 'mno_id';
                $modalidad->mensaje = 'Selecciona una Modalidad';
                $modalidad->label = 'Modalidad';
                $modalidad->comoAtr = "mno_id";
                return $modalidad;
                break;
            case "submod":
                $submod = new stdClass();
                $submod->id = "smn_id";
                $submod->name = "control";
                $submod->enDiv = true;
                $submod->camposVal = "smn_id";
                $submod->agrupaPor = "smn_id";
                $submod->campoLabelAgrupacion = "smn_nombre";
                $submod->mensaje = "Selecciona";
                $submod->label = "Tipo de Haberes";
                $submod->comoAtr = "smn_id";
                $submod->sql = '
                    SELECT smn_id,smn_nombre
                    FROM cat_SubmodalidadNomina WHERE mno_id=6;';
                return $submod;
                break;
            case "fechainicio":
                $fechainicio = new stdClass();
                $fechainicio->id = "fecha_in";
                $fechainicio->name = "control";
                $fechainicio->class = 'fecha';
                $fechainicio->label = 'Inicio';
                $fechainicio->enDiv = true;
                $fechainicio->formato = 'fecha';
                $fechainicio->labelClass = '_32 txtL';
                $fechainicio->required = true;
                return $fechainicio;
                break;
            case "fechafin":
                $fechafin = new stdClass();
                $fechafin->id = "fecha_fin";
                $fechafin->name = "control";
                $fechafin->class = 'fecha';
                $fechafin->label = 'Fin';
                $fechafin->enDiv = true;
                $fechafin->formato = 'fecha';
                $fechafin->labelClass = '_32 txtL';
                $fechafin->required = true;
                return $fechafin;
                break;
            default:
                print_r("OPCION INVALIDA");
                break;
        }
    }

    public function vwClasificacionAdministrativa($prm)
    {
        $cveAdmitiva = new stdClass();
        $cveAdmitiva->filtro = "poa";
        $cveAdmitiva->tipo = $prm['tipo'];
        if ($prm['abc'] == 'c' && count($prm['extra']) > 0) {
            $cveAdmitiva->prg_id = $prm['extra']['prg_id'];
            $cveAdmitiva->spr_id = $prm['extra']['spr_id'];
            $cveAdmitiva->py_id = $prm['extra']['py_id'];
            $cveAdmitiva->oa_id = $prm['extra']['oa_id'];
            $cveAdmitiva->ue_id = $prm['ue_id'];
            $cveAdmitiva->lectura = true;
        } else {
            switch ($prm['tipo']) {
                case "E":
                case "D":
                case "C":
                case "K":
                    break;
                default:
                    $cveAdmitiva->change = '
                        comunica("srv/ClasificacionFinanciamiento.php",{
                            metodoCveFin:"cargaComboCveFin",
                            filtro:"Afectaciones",
                            tipo:"' . $prm['tipo'] . '"
                        });
                    ';
                    break;
            }
        }

        return $cveAdmitiva;
    }

    public function vwDetalleAfectacion($da)
    {
        $html = '
            <table class="table table-bordered table-striped">
                <tr>
                    <td colspan="3">
                        Programa:' . $da['prg_id'] . '-' . $da['prg_nombre'] . '
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        Subprograma:' . $da['spr_id'] . '-' . $da['spr_nombre'] . '
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        Proyecto:' . $da['py_id'] . '-' . $da['py_nombre'] . '
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        Obra:' . $da['oa_id'] . '-' . $da['oa_nombre'] . '
                    </td>
                </tr>
                <tr>
                    <td>Presupuesto:$' . number_format($da['Presupuesto'], 2) . '</td>
                    <td>Comprometido:$' . number_format($da['Comprometido'], 2) . '</td>
                    <td>Devengado:$' . number_format($da['Devengado'], 2) . '</td>
                </tr>
            ';
        return $html;
    }

    public function vwDetalleContable($datos)
    {
        $percepciones = 0;
        $retenciones = 0;
        $nombre = "";
        $cuenta = "";
        $cols = 0;

        $detalle = '<tr><td class="warning">Mes</td>';
        $detalle1 = '<tr><td class="warning">Registros</td>';
        $detalle2 = '<tr><td class="warning">Tipo</td>';
        $detalle3 = '<tr><td class="warning">Importe</td>';
        foreach ($datos as $data) {
            if ($data) {
                $cols += 2;
                $detalle .= '<td colspan="2" align="center">' . $data['msc_nombre'] . '</td>';
                $detalle1 .= '<td colspan="2" align="center">' . $data['Registros'] . '</td>';
                $detalle2 .= '<td align="center">+</td><td style="border-left:1px dotted #000 !important"  align="center">-</td>';
                $detalle3 .= '<td align="right">$' . number_format($data['per'], 2) . '</td><td style="border-left:1px dotted #000 !important" align="right">$ ' . number_format($data['ret'], 2) . '</td>';
                $nombre = $data['cta_nombre'];
                $cuenta = $data['cta_cuenta'];
                $percepciones += $data['per'];
                $retenciones += $data['ret'];
            }
        };
        $detalle .= "</tr>";
        $detalle1 .= "</tr>";
        $detalle2 .= "</tr>";
        $detalle3 .= "</tr>";
        $html = '
            <table class="table table-bordered table-striped">
                <tr>
                    <th class="warning" style="color:#000 !important;background-color:rgba(32, 167, 242, 0.5) !important"  colspan="' . ($cols + 1) . '">Importes y movimientos de la cuenta ' . $cuenta . ' - ' . $nombre . '</th>
                </tr>' . $detalle . $detalle1 . $detalle2 . $detalle3 . '
            </table>';
        return $html;
    }

    public function vwDetalleClavePresupuestal($data)
    {
        $vig1 = $data['vig_ene'] + $data['vig_feb'] + $data['vig_mar'];
        $vig2 = $data['vig_abr'] + $data['vig_may'] + $data['vig_jun'];
        $vig3 = $data['vig_jul'] + $data['vig_ago'] + $data['vig_sep'];
        $vig4 = $data['vig_oct'] + $data['vig_nov'] + $data['vig_dic'];

        $dev1 = $data['dev_ene'] + $data['dev_feb'] + $data['dev_mar'];
        $dev2 = $data['dev_abr'] + $data['dev_may'] + $data['dev_jun'];
        $dev3 = $data['dev_jul'] + $data['dev_ago'] + $data['dev_sep'];
        $dev4 = $data['dev_oct'] + $data['dev_nov'] + $data['dev_dic'];

        $html = '
            <table class="table table-bordered table-striped">
                <tr>
                    <th colspan="13" style="color:#000 !important;background-color:rgba(32,'
            . ' 167, 242, 0.5) !important" class="warning">Estadisticas de la Clave Presupuestal '
            . $data['pre_ClaveCompleta'] . ' <br/> ' . $data['pe_id'] . "-" . $data['pe_nombre']
            . "<br>" . $data['cfi_clave'] . "-" . $data['cfi_nombre'] . '</th>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="2"  class="warning">Presupuesto</td>
                    <td colspan="2">$' . number_format($data['pre_vigente'], 2) . '</td>
                    <td colspan="2" class="warning">Comprometido</td>
                    <td colspan="2" >$' . number_format($data['pre_comprometido'], 2) . '</td>
                    <td colspan="2" class="warning">Devengado</td>
                    <td colspan="2">$' . number_format($data['pre_devengado'], 2) . '</td>
                </tr>
                <tr>
                    <td class="warning">Mes</td>
                    <td>Enero</td><td>Febrero</td><td>Marzo</td>
                    <td style="border-left:1px solid #444 !important">Abril</td><td>Mayo</td><td>Junio</td>
                    <td style="border-left:1px solid #444 !important">Julio</td><td>Agosto</td> <td>Septiembre</td>
                    <td style="border-left:1px solid #444 !important">Octubre</td><td>Noviembre</td><td>Diciembre</td>
                </tr>
                <tr>
                    <td class="warning">Vigente</td>
                    <td>' . number_format($data['vig_ene'], 2) . '</td>
                    <td>' . number_format($data['vig_feb'], 2) . '</td>
                    <td>' . number_format($data['vig_mar'], 2) . '</td>
                    <td style="border-left:1px solid #444 !important">$' . number_format($data['vig_abr'], 2) . '</td>
                    <td>' . number_format($data['vig_may'], 2) . '</td>
                    <td>' . number_format($data['vig_jun'], 2) . '</td>
                    <td style="border-left:1px solid #444 !important">$' . number_format($data['vig_jul'], 2) . '</td>
                    <td>' . number_format($data['vig_ago'], 2) . '</td>
                    <td>' . number_format($data['vig_sep'], 2) . '</td>
                    <td style="border-left:1px solid #444 !important">$' . number_format($data['vig_oct'], 2) . '</td>
                    <td>' . number_format($data['vig_nov'], 2) . '</td>
                    <td>' . number_format($data['vig_dic'], 2) . '</td>
                </tr>
                <tr>
                    <td class="warning">Devengado</td>
                    <td>' . number_format($data['dev_ene'], 2) . '</td>
                    <td>' . number_format($data['dev_feb'], 2) . '</td>
                    <td>' . number_format($data['dev_mar'], 2) . '</td>
                    <td style="border-left:1px solid #444 !important">' . number_format($data['dev_abr'], 2) . '</td>
                    <td>' . number_format($data['dev_may'], 2) . '</td>
                    <td>' . number_format($data['dev_jun'], 2) . '</td>
                    <td style="border-left:1px solid #444 !important">' . number_format($data['dev_jul'], 2) . '</td>
                    <td>' . number_format($data['dev_ago'], 2) . '</td>
                    <td>' . number_format($data['dev_sep'], 2) . '</td>
                    <td style="border-left:1px solid #444 !important" >' . number_format($data['dev_oct'], 2) . '</td>
                    <td>' . number_format($data['dev_nov'], 2) . '</td>
                    <td>' . number_format($data['dev_dic'], 2) . '</td>
                </tr>
                <tr>
                    <td class="warning">Vig. Trimestral</td>
                    <td colspan="3"  align="right">$' . number_format($vig1, 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format($vig2, 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format($vig3, 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format($vig4, 2) . '</td>
                </tr>
                <tr>
                    <td class="warning">Dev. Trimestral</td>
                    <td colspan="3"  align="right">$' . number_format($dev1, 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format($dev2, 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format($dev3, 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format($dev4, 2) . '</td>
                </tr>

                <tr>
                    <td class="warning">Disponible</td>
                    <td colspan="3"  align="right">$' . number_format(($vig1 - $dev1), 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format(($vig2 - $dev2), 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format(($vig3 - $dev3), 2) . '</td>
                    <td colspan="3" style="border-left:1px solid #444 !important" align="right">$' . number_format(($vig4 - $dev4), 2) . '</td>
                </tr>
                <tr>
                    <td class="warning">Registros</td>
                    <td>' . $data['ene'] . '</td>
                    <td>' . $data['feb'] . '</td>
                    <td>' . $data['mar'] . '</td>
                    <td style="border-left:1px solid #444 !important">' . $data['abr'] . '</td>
                    <td>' . $data['may'] . '</td>
                    <td>' . $data['jun'] . '</td>
                    <td style="border-left:1px solid #444 !important">' . $data['jul'] . '</td>
                    <td>' . $data['ago'] . '</td>
                    <td>' . $data['sep'] . '</td>
                    <td style="border-left:1px solid #444 !important">' . $data['oct'] . '</td>
                    <td>' . $data['nov'] . '</td>
                    <td>' . $data['dic'] . '</td>
                </tr>
            </table>';
        return $html;
    }

    public function pintaCabeceraAfectacion($AFECTACION)
    {
        $estilo = $AFECTACION['eaf_id'] == "A" ? "success" : "warning";
        $meses = array(
            "ERROR",
            'ENERO',
            'FEBRERO',
            'MARZO',
            'ABRIL',
            'MAYO',
            'JUNIO',
            'JULIO',
            'AGOSTO',
            'SEPTIEMBRE',
            'OCTUBRE',
            'NOVIEMBRE',
            'DICIEMBRE'
        );
        $row = 3;
        if ($AFECTACION['tcl_id'] == "A") {
            $row = 5;
        }
        $html = '';
        $html .= '
            <div style="background-color:#FFF;">
            <div id="afectacion-info">
                <table class="table table-bordered">
                <tr>
                    <th style="text-align: center;
                        width: 130px;
                        word-wrap: break-word;
                        vertical-align: middle;"
                        rowspan="' . $row . '" class="' . $estilo . '">
                        Información de la Afectación <strong> ' . $AFECTACION['afe_id'] . ' </strong>'
            . ($AFECTACION['eaf_id'] == "A" ? '- AUTORIZADA' : '') . '
                    </th>
                </tr>
                <tr>
                    <th class="' . $estilo . '" colspan="2">Tipo y Subtipo</th>
                    <th class="' . $estilo . '">Clave de Financiamiento</th>
                    <th class="' . $estilo . '">Tipo de Compra</th>
                    <th class="' . $estilo . '">Mes</th>
                </tr>
                <tr>
                    <td class="">' . $AFECTACION['tcl_id'] . '-' . $AFECTACION['tcl_nombre'] . '</td>
                    <td class="">' . $AFECTACION['stc_id'] . '-' . $AFECTACION['stc_nombre'] . '</td>
                    <td class="">' . $AFECTACION['cfi_clave'] . " : " . $AFECTACION['cfi_nombre'] . '</td>
                    <td class="">' . (!isset($AFECTACION['tcp_id']) ? "-" : $AFECTACION['tcp_id'] . "-" . $AFECTACION['tcp_nombre']) . '</td>
                    <td class="">' . $meses[$AFECTACION['Mes']] . '</td>

                </tr>
        ';
        if ($AFECTACION['tcl_id'] == "A") {
            $html .= '
                <tr><th class="' . $estilo . '" colspan="5">Tipo Nomina y Modalidad</th></tr>
                <tr><td colspan="5">' . $AFECTACION['tno_nombre'] . ' - ' . $AFECTACION['mno_nombre'] . '</td></tr>';
        }
        $html .= '</table></div>';
        if ($AFECTACION['afe_fondoRotatorio'] == "S" && $AFECTACION['tcl_id'] == "B") {
            $html .= '<div style="padding:5px;" class="alert alert-warning">La afectación es para recuperación de Fondo Rotatorio</div>';
        }

        return $html;
    }

    public function getPonTablaNumerosControl($p)
    {
        $tabla = new stdClass();
        $tabla->id = "tabla_nums_control";
        $tabla->titulo = 'Números de control registrados para la Afectación número ' . $p['afe_id'];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = "scp/afectaciones/ControladorAfectacion.php";
        $tabla->comunica->metodo = "llenaTablaNumerosdeControl";
        $tabla->comunica->afe_id = $p['afe_id'];
        $tabla->comunica->tipo = $p['tipo'];

        $tabla->numFilas = 10;
        $tabla->filasAmostrar = array(20, 30, 50, 100, 200, 300);
        $tr = array();

        $th = new stdclass();
        $th->texto = "Ver/Editar";
        $th->width = "3%";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "#Control";
        $th->width = "5%";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "Compromiso";
        $th->width = "8%";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "Tipo";
        $th->width = "5%";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "Clave/Cuenta";
        $th->width = "10%";
        $th->align = "center";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "Num. Ref.";
        $th->width = "18%";
        $th->align = "Center";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "Concepto";
        $th->width = "35%";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "+/-";
        $th->width = "2%";
        array_push($tr, $th);
        $th = new stdclass();
        $th->texto = "Importe";
        $th->width = "20%";
        $th->align = "center";
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->tipo = "boton";
        $td->attr = new stdClass();

        /* ATRIBUTOS DE LOS BOTONES DE LA TABLA */
        $td->attr->num_id = "num_id";
        $td->attr->tipo = "tipo";
        $td->attr->afe_id = "afe_id";
        $td->attr->clc_id = "clc_id";
        $td->attr->epg_id = "epg_id";
        $td->attr->ecf_id = "ecf_id";
        $td->attr->ue_id = "ue_id";
        $td->class = "addModCedula btn-xs";
        $td->name = "num_id";
        $td->icono = "pencil";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'num_id';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_id';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'tipo';
        $td->align = "left";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = "link";
        $td->campoTxt = 'clave';
        $td->align = "left";
        $td->class = "clave_link";
        $td->attr = new stdClass();
        $td->attr->num = "num_id";
        $td->attr->tipo = "tipo";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NumRef';
        $td->ajustable = true;
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'concepto';
        $td->ajustable = true;
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Movimiento';
        $td->align = "center";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Importe';
        $td->align = "right";
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $tabla;
    }

    public function getControlesPresupuestal($n, $p = null)
    {
  
        switch ($n) {
            case "compromiso":
                $compromiso = new stdClass();
                $compromiso->id = "cmp_id";
                $compromiso->name = "control";
                $compromiso->required = true;
                $compromiso->enDiv = true;
                $compromiso->camposVal = "cmp_id";
                $compromiso->agrupaPor = "cmp_id";
                $compromiso->campoLabelAgrupacion = "cmp_id";
                $compromiso->mensaje = "Selecciona el compromiso";
                $compromiso->label = "Compromiso a Devengar";
                $compromiso->comoAtr = "cmp_id";
                $compromiso->change = '
                    cmpid = $(this).val();
                    if(cmpid == 0 && cmpid != "" )
                        $("#div_info_compro").css("display","none");
                    else
                        $("#div_info_compro").css("display","table-cell");
                    if (cmpid == 0 && $("#pg_id").find("option:selected").val() != ""
                        && $("#pe_id").find("option:selected").val() != ""
                    )
                        $("#pe_id").change();

                    comunica("scp/afectaciones/ControladorAfectacion.php",{metodo:"getCompromiso",
                        cmp_id:$(this).find("option:selected").val(),
                        afe_id:"' . $p['afe_id'] . '"
                    });
                ';

                $and = '';
                if ($p['tcl_id'] == "F") {
                    if ($p['stc_id'] == 02) {
                        $and = ' stc_id = "' . $p['stc_id'] . '" ';
                    } else {
                        $and = ' stc_id IN ("00","01") ';
                    }
                } else {
                    $and = ' stc_id = "' . $p['stc_id'] . '" ';
                }

                $sql = '';
                $params = [];
                if ($p['eaf_id'] == "A") {
                    $sql = '
                        SELECT cmp_id,cmp_concepto
                        FROM scp_Compromiso
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                        AND epg_id = :epg_id AND ecf_id= :ecf_id
                        AND tcl_id = :tcl_id AND ' . $and . '
                        AND cmp_id = :cmp_id AND tcp_id= :tcp_id
                        AND cmp_tipoCompromiso != "A" ';
                    $params += [
                        ':cmp_id' => $p['cmp_id']
                    ];
                } else {
                    if ($p['tcl_id'] != "F" && $p['tcl_id'] != "G") {
                        $sql = '
                            SELECT "0" AS cmp_id,"GENERAR NUEVO COMPROMISO" AS cmp_concepto UNION ALL' . PHP_EOL;
                    }
                    $sql .= '
                        SELECT cmp_id,cmp_concepto
                        FROM scp_Compromiso
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                        AND epg_id = :epg_id AND ecf_id= :ecf_id
                        AND tcl_id = :tcl_id AND ' . $and . '
                        AND tcp_id= :tcp_id AND ecm_id IN("A","P")
                        AND cmp_tipoCompromiso != "A"
                        ORDER BY cmp_id ASC ';
                }
                $params += [
                    ':gpo_id' => $p['gpo_id'],
                    ':ur_id' => $p['ur_id'],
                    ':epg_id' => $p['epg_id'],
                    ':ecf_id' => $p['ecf_id'],
                    ':tcl_id' => $p['tcl_id'],
                    ':tcp_id' => $p['tcp_id']
                ];
                $compromiso->params = $params;
                $compromiso->sql = $sql;
                return $compromiso;
                break;
            case "documentoComprobatorio":
                $documento = new stdClass();
                $documento->id = "tpd_id";
                $documento->name = "control";
                $documento->required = true;
                $documento->enDiv = true;
                $documento->camposVal = "tpd_id";
                $documento->agrupaPor = "tpd_id";
                $documento->campoLabelAgrupacion = "tpd_id";
                $documento->mensaje = "Selecciona el tipo de documento";
                $documento->label = "Tipo de Documento Comprobatorio";
                $documento->comoAtr = "tpd_id,tpd_nombre";
                $documento->sql = '
                    SELECT tpd_id,tpd_nombre
                    FROM cat_TipoDocumento
                    WHERE tcl_id= :tcl_id AND stc_id= :stc_id
                        AND tpd_momento ="D"
                    AND uni_id = :uni_id ';
                $params = [
                    ':tcl_id' => $p['tcl'],
                    ':stc_id' => $p['stc_id'],
                    ':uni_id' => ($p['pri'] ? "18" : "17")
                ];
                $documento->params = $params;
                $documento->change = '
                    if ($(this).find("option:selected").attr("tpd_nombre") == "OTRO"){
                        $("#otroDocumento").show();
                    } else {
                        $("#otroDocumento").hide();
                        $("#otroDocumento").val("");
                    }
                ';
                return $documento;
                break;
            case "otroDocto":
                $otroDocto = new stdClass();
                $otroDocto->id = "otr_doc";
                $otroDocto->name = "control";
                $otroDocto->placeholder = "Especifique el nombre del documento";
                return $otroDocto;
                break;
            case "rfcPresupuestal":
                $rfc = new stdClass();
                $rfc->id = "rfc_id";
                $rfc->camposText = 'rfc_nombre';
                $rfc->required = true;
                $rfc->tabla = 'cat_Proveedor';
                $rfc->name = "control";
                $rfc->autocomplete = true;
                $rfc->placeholder = "RFC del proveedor";
                $rfc->campoVal = "rfc_id";
                $rfc->label = "R.F.C del proveedor";
                $rfc->desdecaracter = 4;
                $rfc->maxlength = 13;
                $rfc->enDiv = true;
                $rfc->tablas = " INNER JOIN sinpres.cat_Proveedores USING ( rfc_id )";
                return $rfc;
                break;
            case "rfcNombre":
                $rfc_nombre = new stdClass();
                $rfc_nombre->id = "rfc_nombre";
                $rfc_nombre->name = "control";
                $rfc_nombre->required = true;
                $rfc_nombre->placeholder = "Nombre del proveedor";
                $rfc_nombre->label = "Nombre del proveedor";
                $rfc_nombre->enDiv = true;
                $rfc_nombre->lectura = true;
                $rfc_nombre->readonly = "readonly";
                return $rfc_nombre;
                break;
            case "numeReferencia";
                $refer = new stdClass();
                $refer->id = "NumReferencia";
                $refer->name = "NumReferencia";
                $refer->required = true;
                $refer->enDiv = true;
                $refer->placeholder = "Escriba su referencia o Presione F1";
                $refer->maxlength = 80;
                $refer->label = "Número de Referencia";
                $refer->readonly = "readonly";
                $refer->autocompletar = true;
                $refer->tabla = 'cat_BancoDocumentos';
                $refer->campoVal = "bdo_referencia";
                $refer->desdecaracter = 1;
                $refer->camposText = 'bdo_referencia';
                $refer->filtroUr = true;
                $refer->class = 'F1Catalogo';
                return $refer;
                break;
            case "fechaReferencia":
                $fecha_referencia = new StdClass();
                $fecha_referencia->id = "fecha_referencia";
                $fecha_referencia->name = "fecha_referencia";
                $fecha_referencia->required = true;
                $fecha_referencia->placeholder = "Fecha Referencia";
                $fecha_referencia->label = "Fecha de Referencia";
                $fecha_referencia->enDiv = true;
                return $fecha_referencia;
                break;
            case "conceptoNcp":
                $concepto = new stdClass();
                $concepto->id = "concepto";
                $concepto->name = "control";
                $concepto->required = true;
                $concepto->placeholder = "Concepto";
                $concepto->label = "Concepto";
                $concepto->enDiv = true;
                $concepto->width = "100%";
                return $concepto;
                break;
            case "importeNcp":
                $importe = new stdClass();
                $importe->id = "importe";
                $importe->name = "ncc_percepciones";
                $importe->maxlength = 13;
                $importe->required = true;
                $importe->placeholder = "Importe $";
                $importe->label = "Importe $";
                $importe->enDiv = true;
                $importe->formato = "moneda";
                return $importe;
                break;
            case "ivaNcp":
                $iva = new stdClass();
                $iva->id = "ncp_iva";
                $iva->required = true;
                $iva->placeholder = "Importe IVA$";
                $iva->label = "Importe IVA $";
                $iva->enDiv = true;
                $iva->formato = "moneda";
                return $iva;
                break;
            case "importeTotalNcp":
                $importetotal = new stdClass();
                $importetotal->id = "ncp_neto";
                $importetotal->name = "ncp_neto";
                $importetotal->required = true;
                $importetotal->placeholder = "Importe total $";
                $importetotal->label = "Importe total $";
                $importetotal->enDiv = true;
                $importetotal->formato = "moneda";
                $importetotal->readonly = "readonly";
                return $importetotal;
                break;
            case "nominaNcp":
                $nomina = new stdClass();
                $nomina->id = "id_nomina";
                $nomina->name = "id_nomina";
                $nomina->required = true;
                $nomina->placeholder = "Id de la nomina";
                $nomina->label = "Id de la nomina";
                $nomina->enDiv = true;
                return $nomina;
                break;
            case "btnRegresaNcp":
                $btnregresarindex = new stdClass();
                $btnregresarindex->id = 'btnregresar';
                $btnregresarindex->value = 'Regresar';
                $btnregresarindex->color = 'gris';
                $btnregresarindex->icon = 'circle-left';
                return $btnregresarindex;
                break;
            case "btnEliminaNcp":
                $btnEliminar = new stdClass();
                $btnEliminar->id = 'btnElimina';
                $btnEliminar->name = 'btnElimina';
                $btnEliminar->color = "rojo";
                $btnEliminar->text = 'Eliminar';
                $btnEliminar->value = 'Eliminar';
                $btnEliminar->icon = 'trash me-2';
                if ($p['tipo'] == "C") {
                    $btnEliminar->click = '
                        $(this).attr("disabled","disabled");
                        alertify.confirm(
                            "Atención",
                            "Desea eliminar el número de control contable ?",
                            function () {
                                $("#btnElimina").hide();
                                comunica(
                                    "scp/afectaciones/ControladorAfectacion.php", {
                                    metodo: "abcContable",
                                    abc: "b",
                                    afe_id: "' . $p['afe_id'] . '",
                                    ncc_id: "' . $p['ncc_id'] . '",
                                });
                            },
                            function () { },
                        ).set("defaultFocus", "cancel").set("labels", { ok: "Autorizar", cancel: "Cancelar" });
                        $(this).show();
                        $(this).removeAttr("disabled");
                    ';
                }
                if ($p['tipo'] == "P") {
                    $btnEliminar->click = '
                        $(this).attr("disabled","disabled");

                        alertify.confirm(
                            "Atención",
                            "Desea eliminar el número de control contable ?",
                            function () {
                                $("#btnElimina").hide();
                                comunica(
                                    "scp/afectaciones/ControladorAfectacion.php", {
                                    metodo: "abcPresupuestal",
                                    abc: "b",
                                    afe_id: "' . $p['afe_id'] . '",
                                    ncp_id: "' . $p['ncp_id'] . '",
                                });
                            },
                            function () { 
                             $(this).removeAttr("disabled");},
                        ).set("defaultFocus", "cancel").set("labels", { ok: "Autorizar", cancel: "Cancelar" });
                    ';
                }
                return $btnEliminar;
                break;
            case "btnGuardarNcp":
                $btnGuardar = new stdClass();
                $btnGuardar->id = 'btn_guarda';
                $btnGuardar->name = 'btn_guarda';
                $btnGuardar->class = 'btn_default me-1';
                $btnGuardar->color = "verde";
                $btnGuardar->text = 'Guardar';
                $btnGuardar->value = 'Guardar';
                $btnGuardar->icon = 'floppy-disk me-2';
                return $btnGuardar;
                break;
            case "imprimeDetalleNcp":
                $imprimeDetalle = new stdClass();
                $imprimeDetalle->id = 'imprimeDetalle';
                $imprimeDetalle->name = 'imprimeDetalle';
                $imprimeDetalle->icon = "printer";
                $imprimeDetalle->color = "azul";
                $imprimeDetalle->text = 'Imprimir detalle';
                $imprimeDetalle->value = 'Imprimir detalle';
                $imprimeDetalle->target = "_blank";
                return $imprimeDetalle;
                break;
            case "--":
                break;
            case "--":
                break;
            case "--":
                break;
        }
    }

    public function getControlesContable($n, $p = null)
    {
        switch ($n) {
            case "cuentaContable":
                $cuenta = new stdClass();
                $cuenta->id = "cuentacontapre";
                $cuenta->name = "control";
                $cuenta->required = true;
                $cuenta->placeholder = "Cuenta Contable  ";
                $cuenta->label = "Cuenta Contable";
                $cuenta->tabla = 'cg_PlanCuentas';
                $cuenta->camposText = 'cta_nombre';
                $cuenta->campoVal = "cta_cuenta";
                $cuenta->autocomplete = false;
                $cuenta->desdecaracter = 9;
                $cuenta->enDiv = true;
                $cuenta->class = "F1Catalogo";
                if ($p['gpo_id'] . $p['ur_id'] == 116) {
                    $cuenta->maxlength = 25;
                } else {
                    $cuenta->maxlength = 22;
                }

                return $cuenta;
                break;
            case "ctaDesc":
                $cuenta_descripcion = new stdClass();
                $cuenta_descripcion->id = "cuenta_descripcion";
                $cuenta_descripcion->name = "control";
                $cuenta_descripcion->required = true;
                $cuenta_descripcion->placeholder = "Descripción de la cuenta ";
                $cuenta_descripcion->label = "Descripción de la cuenta ";
                $cuenta_descripcion->enDiv = true;
                $cuenta_descripcion->readonly = "readonly";
                return $cuenta_descripcion;
                break;
        }
    }

    public function pintaGrafica2($vig, $dev, $com)
    {
        $meses = array(
            'ENERO',
            'FEBRERO',
            'MARZO',
            'ABRIL',
            'MAYO',
            'JUNIO',
            'JULIO',
            'AGOSTO',
            'SEPTIEMBRE',
            'OCTUBRE',
            'NOVIEMBRE',
            'DICIEMBRE'
        );

        $dias = -1;
        $mes = date("m");
        $dias = ($mes + .5) - 2;
        $d = date("d");
        $d = ($d * 1) / 30;
        $mes = $dias + round($d, 1);
        $categories = '';
        $vigente = '';
        $devengado = '';
        $compro = '';

        for ($i = 0; $i <= 11; $i++) {
            $categories .= "'" . $meses[$i] . "',";
            $pr = strtolower(substr($meses[$i], 0, 3));
            $vigente .= $vig->$pr . ",";
            $devengado .= $dev->$pr . ",";
            $compro .= $com->$pr . ",";
        }

        $return = "
            Highcharts.chart('grafica2', {
                chart: {
                    type: 'areaspline',
                },
                title: {
                    text: 'Presupuesto Devengado por Mes'
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    verticalAlign: 'top',
                    x: 150,
                    y: 100,
                    floating: true,
                    borderWidth: 1,
                    backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
                },
                xAxis: {
                    categories: [" . $categories . "],
                    plotBands: [{ // visualize the weekend
                        from: " . $mes . ",
                        to: 12,
                        color: 'rgba(68, 213, 0, .2)'
                    }]
                },
                yAxis: {
                    title: {
                        text: 'Importe'
                    }
                },
                tooltip: {
                    shared: true,
                    valueSuffix: ' pesos',
                    pointFormat: '<span style=\"color:{point.color}\">{series.name}</span>: <b>$ {point.y:.2f}</b> <br/>'
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.5
                    }
                },
                series: [{
                    name: 'Devengado',
                    data: [" . $devengado . "],
                    color:'#C4131B'
                }, {
                    name: 'Vigente',
                    data: [" . $vigente . "],
                    color:'#9DBF3B'
                }
                , {
                    name: 'Comprometido',
                    data: [" . $compro . "],
                    color:'#553276'
                }]
            });
        ";
        return $return;
    }

    public function pintaGrafica1($extra1, $extra2)
    {
        $graph = "
            Highcharts.chart('grafica',{
                chart : {type: 'column' },
                colors : ['#553276','#C43131','#1F934C','#9DBF3B','#8D3B88','#E6AD2F','#545555','#818282'],
                title : {text: '' },
                subtitle: {text: '' },
                xAxis : {type: 'category' },
                yAxis : {title: { text: 'Total Presupuesto' } },
                legend : {enabled: false },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {enabled: true, format: '$ {point.y:.2f}' }
                    }
                },
                tooltip: {
                    headerFormat: '<span style=\"font-size:11px\">{series.name}</span><br>',
                    pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>$ {point.y:.2f}</b> <br/>'
                },
                series: [{
                    name: 'Importes', colorByPoint: true,
                    data: [ " . $extra1 . "    ]
                }],
                drilldown: {series: [" . $extra2 . "]}
            });";
        return $graph;
    }

    public function getScriptContable($p = null)
    {
        /* EVITAR SALTOS DE LINEA EN EL CONCEPTO */
        $scri = '
            $("#concepto").keypress(function(event){'
            . 'if (event.keyCode == 13){'
            . '  return false; event.preventDefault();}' . '
                });';

        /* INFORMACION DE LA REFERENCIA   */
        $scri .= '
            $("#link_info_ref").click(function(){
                if($("#rfc_id").val().trim()!="" && $("#NumReferencia").val().trim()!="") {
                    load_viewer($("#rfc_id").val(),"INFORMACION DE LA REFERENCIA","div");

                    comunica("scp/afectaciones/ControladorAfectacion.php",{
                        metodo:"infoReferencia",
                        rfc_id:$("#rfc_id").val(),
                        referencia:$("#NumReferencia").val(),
                        tdf_id:$("#NumReferencia").attr("tdf")
                    });
                }else{
                    notify("El proveedor y  la referencia son necesarias","error");
                }
            });';
        /* AÑOS PERMITIDOS   */

        $anio = 2;
        $scri .= '
            function importes2(){};
            function agregacomas(num){x = num.split(".");x1 = x[0];x2 = x.length > 1 ? "." + x[1] : "";var rgx = /(\d+)(\d{3})/;while (rgx.test(x1)) {x1 = x1.replace(rgx, "$1" + "," + "$2");}return x1 + x2;}
            function validaAnio(fecha){'
            . 'var valido = "false";'
            . 'var arregloFecha = fecha.split("/");'
            . 'var anio = arregloFecha[2];'
            . 'var currentTime = new Date();'
            . 'var year = currentTime.getFullYear();'
            . 'if($("#cta_2013").is(":checked") && anio==2013){'
            . 'valido="true";'
            . '}else{'
            . 'if(anio<(year-' . $anio . ') || anio>(year)){'
            . 'notify ("El año de la fecha es incorrecto,'
            . 'solo se permiteel año " + (year-' . $anio . ') + " y hasta el año "+ (year));'
            . '}else{valido = "true";}'
            . '}return valido;}';

        /* IMPRIMIR DETALLE DE LA AMORTIZACION  */
        $scri .= '
            $("#NumReferencia").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    var attr = $(this).attr("readonly");
                    if (typeof attr === typeof undefined && attr !== true) {
                        comunica("scp/afectaciones/ControladorBancoDocumentos.php", {
                            rfc : $("#rfc_id").val(),fr:"' . $p['fr'] . '"
                        } );
                    }
                }
            });';
        /* IMPRIMIR DETALLE DE LA AMORTIZACION  */
        $scri .= '
            $("#imprimeDetalle").click(function(){
                var a = document.createElement(\'a\');
                a.href=\'rep/doc/DetalleAmortiza.php ? afe_id ='
            . $p['afe_id'] . ' '
            . ($p['ncc_id'] > 0 ? ' & ncc_id=' . $p['ncc_id'] : '')
            . '\';
                a.target = \'_blank\';
                document.body.appendChild(a);
            });';

        /* CONSULTAR PROVEEDOR */
        $scri .= '
            $("#rfc_id").blur(function(){
                if ($(this).length!=0){
                    comunica("scp/afectaciones/ControladorAfectacion.php",{
                        metodo:"proveedor",
                        rfc:$(this).val(),
                        abc:"' . $p['abc'] . '"
                    },false,false);
                }
            });';
        /* DAR FORMATO A LA CUENTA CONTABLE */
        $scri .= '
            $("#cuentacontapre").keypress(function(e){
                if($("#seisniveles").length==1 && $("#seisniveles").is(":checked")){
                    switch ($(this).val().length){
                        case 5: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                        case 9: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                        case 13: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                        case 17: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                        case 20: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                    }
                } else {
                    switch ($(this).val().length){
                        case 5: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                        case 9: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                        case 13: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                        case 17: $("#cuentacontapre").val($("#cuentacontapre").val()+".");    break;
                    }
                }
            });';
        return $scri;
    }

    public function getScriptGuardaNCP($p = null)
    {
        return '
            $("#btn_guarda").click(function(){
                $("#btn_guarda").attr("disabled","disabled");
                var fecha = "";

                if ($("#tpd_id").find("option:selected").val() == "") {
                    notify("Es necesario un tipo de Documento Comprobatorio","warning")
                    $("#btn_guarda").removeAttr("disabled");
                    return false;
                } else {
                    if ($("#tpd_id").find("option:selected").attr("tpd_nombre") == "OTRO" && $("#otr_doc").val() == "") {
                        notify("Es necesario especificar el documento ","warning")
                        $("#otr_doc").focus();
                        $("#btn_guarda").removeAttr("disabled");
                        return false;
                    }
                }

                if ($("#fecha_referencia").length == 1) {
                    fecha = validaAnio($("#fecha_referencia").val(),' . ($p['anioactual'] ? 'true' : 'false') . ');
                } else {
                    fecha = "true";
                }
                if (fecha == "false") {
                    alertify.alert("Aviso", "La fecha no es válida").set("labels", {ok:"Aceptar"});
                } else if ($("#concepto").val() == "") {

                     alertify.alert("Aviso", "Debe escribir un concepto").set("labels", {ok:"Aceptar"});
                } else if (parseFloat(unformatNumber( $("#importe").val() ).toFixed(2))> parseFloat(unformatNumber( $("#porcom").val() ).toFixed(2))) {
                    notify("El importe sobre pasa el disponible a devengar","warning")
                    $("#btn_guarda").removeAttr("disabled");
                    return false;
                } else {
                    ivaid = $(\'input[name="radioIVA"]:checked\').attr("id");
                    if (ivaid == undefined || ivaid =="")
                        ivaid = 0;

                    $("#btn_guarda").hide();
                    mn = comunica("scp/afectaciones/ControladorAfectacion.php",{
                        metodo :"abcPresupuestal",
                        referencia :$("#NumReferencia").val(),
                        fechaReferencia:' . ($p['ponFecha'] ? '$("#fecha_referencia").val()' : '"' . (date("d/m/Y")) . '"') . ',
                        ncp_id:"' . $p['ncp_id'] . '",
                        afe_id :"' . $p['afe_id'] . '",
                        abc :"' . $p['abc'] . '",
                        bdo_id :$("#NumReferencia").attr("bdo_id"),
                        cmp_id :$("#cmp_id").find("option:selected").val(),
                        tpd_id :$("#tpd_id").find("option:selected").val(),
                        tipo :"' . $p['tipo'] . '",
                        tdf_id:$("#NumReferencia").attr("tdf"),
                        rfc_id :$("#rfc_id").val(),
                        iva_id :' . ($p['llevaiva'] ? 'ivaid ' : '0') . ',
                        pg_id :$("#pg_id").find("option:selected").val(),
                        pe_id :$("#pe_id").find("option:selected").val(),
                        id_nomina:$("#id_nomina").val(),
                        percepcionBruto:unformatNumber($("#importe").val()),
                        percepcionIva:' . ($p['llevaiva'] ? 'unformatNumber($("#ncp_iva").val())' : '0') . ',
                        percepcionNeto:' . ($p['llevaiva'] ? 'unformatNumber($("#ncp_neto").val()),' : 'unformatNumber($("#importe").val()),') . '
                        concepto:$("#concepto").val(),
                        otrdoc:$("#otr_doc").val()
                    },true);
                    if (mn == false) {
                        $("#btn_guarda").show();
                        $("#btn_guarda").removeAttr("disabled");
                    }
                }
                $("#btn_guarda").removeAttr("disabled");
            });
        ';
    }

    public function getScriptGuardarNCC($p)
    {
        $gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];

        $scr = '
            /* SAVING BUTTON   */
            $("#btn_guarda").click(function(){
                if ($("#cuentacontapre").val() == "" ) {
                    notify("CUENTA INVALIDA","error");
                    return false;
                }

                var retDoc = parseFloat( $("#NumReferencia").attr("retenciones") );
                var perDoc = parseFloat( $("#NumReferencia").attr("percepciones") );
                var s=""; var amortizar=""; var tipoamor=""; var nccpaamor="";
                var guardar="true";
                var fecha="";
                var nccgpo = ""; nccur=""; var afeclc="";
                var ciclos = 0;
                var datos = new Array();
                var obraid = 0;
                importe = parseFloat( unformatNumber($("#importe").val()));

                if ($("#fecha_referencia").length == 1) {
                    fecha = validaAnio($("#fecha_referencia").val(),"true");
                    if (! fecha) {
                        notify ("fecha invalida","warning");
                        return false;
                    }
                } else {
                    guardar = "true";
                }

                /*  SI ES DE ANTICIPO VALIDARA IMPORTES   */

                if ($("#porcom").length == 1  && $("#porcom").val() != "") {
                    poc = parseFloat( unformatNumber( $("#porcom").val() ) );
                    if (importe > poc) {
                        notify("el importe sobre pasa el importe disponible por anticipar","error");
                        return false;
                    }
                }

                if ($("#percepciones").is(":checked")) {
                    s = "percepcion";
                    if (importe > perDoc) {
                        notify("El importe supera las percepciones del documento","error");
                        return  false;
                    }
                }
                if ($("#retenciones").is(":checked")) {
                    s = "retencion";
                    if (importe > retDoc) {
                        notify("El importe supera las retenciones del documento","error");
                        return  false;
                    }
                }

                if (s == "") {
                    notify("Debe seleccionar Retención o Percepción","warning");
                    guardar="false";
                    return false;
                }

                if ($("#concepto").val() == "") {
                    notify("Debe escribir un conceptos","warning");
                    guardar="false";
                    return false;
                }

                if ($("#squaredTwo").is(":checked") && ($("#squaredTwo").attr("tcl_id")=="C" && $("#squaredTwo").attr("stc_id")!="03" )) {
                    if ($("#ncp_control").find("option:selected").val() == "") {
                        notify("No hay registros para amortizar","error");
                        amortizar = "false";
                        guardar="false";
                        return false;
                    }

                    if ($("#clc_pag_ur").length == 1) {
                        var tmp = $("#clc_pag_ur").val();
                        nccgpo= tmp.substr(0,1);
                        nccur = tmp.substr(1,2);
                    } else {
                        nccgpo=' . $gpo_id . ';
                        nccur =' . $ur_id . ';
                    }

                    tipoamor  = "aclc";
                    nccpaamor = $("#ncp_control").find("option:selected").val();
                    amortizar = "true";
                    afeclc = $("#clc_pago").val();
                    guardar="true";


                    if ($("#importeporamortizar").attr("tipo") == s) {

                        alertify.alert("Aviso", "Debe seleccionar un movimiento opuesto al del número de control a amortizar \n Tip:El número a amortizar es "+$("#importeporamortizar").attr("tipo")).set("labels", {ok:"Aceptar"});
                        amortizar = "false";
                        guardar="false";
                        return false;
                    }

                    if ($("#importeporamortizar").val() < unformatNumber($("#importe").val())) {
                        var no = $("#importeporamortizar").val();
                        notify("No puede amortizar una importe mayor a $" + agregacomas(parseFloat(no, 10).toFixed(2)) + "","error");
                        amortizar="false";
                        guardar="false";
                        return false;
                    }
                    if ($("#importeporamortizar").val() == 0) {
                        notify("No hay importe disponible para amortizar/pagar","error");
                        amortizar = "false";
                        guardar="false";
                        return false;
                    } else {
                        amortizar="true";
                        guardar="true";
                    }
                }
                if ($("#importe").length==1 && $("#squaredTwo").is(":checked")&& ($("#squaredTwo").attr("tcl_id")=="C" && $("#squaredTwo").attr("stc_id")=="03" ) && !$(".checkamortizar").length) {
                    // PARA LA AMORTIZACION DE OBRA 2016
                    var importeobra = unformatNumber($("#importe").val());
                    var importeobraresta =0;
                    if ($("#porcom").length == 1) {
                        unformatNumber($("#porcom").val());
                    }
                    if ($("#coo_id").find("option:selected").val() == "") {
                         alertify.alert("Aviso", "Es necesario seleccionar una obra para amortizar!!").set("labels", {ok:"Aceptar"});
                        guardar = "false";
                        return false;
                    } else if (importeobra>importeobraresta) {
                        alert();
                          alertify.alert("Aviso", "No puede amortizar mas de " + $("#porcom").val()).set("labels", {ok:"Aceptar"});
                        guardar = "false";
                        return false;
                    }
                    obraid = $("#coo_id").find("option:selected").val();
                    guardar = "true";
                } else if ($("#squaredTwo").is(":checked") && $("#opcion20142013").length!=1 && $(".checkamortizar").length>0 ) {
                    // para los de contrato y amortizacion en masa, aqui generaremos el arreglo de datos  num control clc
                    var seleccionados = 0;
                    $(".checkamortizar").each(function() {
                        if ($(this).is(":checked")) {
                            seleccionados ++;
                            ciclos ++;
                            datos.push([{"ncc_id":$(this).attr("ncc_id")},{"clc_id":$(this).attr("clc_id")},{"percepcion":$(this).attr("percepcion")},{"retencion":$(this).attr("retencion")}]);
                        }
                    });
                    if (seleccionados = 0) {
                       
                           alertify.alert("Aviso", "No hay registros seleccionados para Amortizar").set("labels", {ok:"Aceptar"});
                        guardar = "false";
                    }
                } else if ($("#squaredTwo").is(":checked") && $("#opcion20142013").css("display") == "block") {
                    var opcion  = false;
                    if ($("#pagaClc").is(":checked")) { opcion =true;}
                    if ($("#cta_2013").is(":checked")) { opcion =true;}
                    if ($("#amortizaObra").is(":checked")) { opcion =true;}
                    if (opcion) {
                        guardar="true";
                    } else {
                        alert();
                         alertify.alert("Aviso", "Debe seleccionar una opcion  CLC 2014 |  AMORTIZAR OBRA 2014").set("labels", {ok:"Aceptar"});
                        guardar="false";
                        return false;
                    }
                }
                if (guardar=="true"){ //aqui va el comunica despues de todas las validaciones
                    $("#btn_guarda").hide();
                    mm = comunica("scp/afectaciones/ControladorAfectacion.php",{
                        metodo : "abcContable",
                        ' . ($p['ncc_id'] > 0 ? 'ncc_id:"' . $p['ncc_id'] . '",' : '') . '
                        afe_id : "' . $p['afe_id'] . '",
                        abc : "' . $p['abc'] . '",
                        tipo : "' . $p['tipo'] . '",
                        rfc_id : $("#rfc_id").val(),
                        cuenta : $("#cuentacontapre").val(),
                        importe : unformatNumber($("#importe").val()),
                        tipoimporte : s,
                        nccamortiza : nccpaamor,
                        tipoamortiza: tipoamor,
                        aclc : afeclc,
                        agpo : nccgpo,
                        coo_id : obraid,
                        aur : nccur ,
                        concepto: $("#concepto").val(),
                        ciclos : ciclos,
                        datos : datos,
                        bdo_id : $("#NumReferencia").attr("bdo_id"),
                        tdf_id : $("#NumReferencia").attr("tdf"),
                        referencia : $("#NumReferencia").val()
                    },true);
                    if(mm == false) {
                        $("#btn_guarda").show();
                        console.log("algo fallo");
                    }
                }
            });
        ';
        return $scr;
    }

    public function getControlPagadora($n, $p = null)
    {
        switch ($n) {
            case "clc_pag":
                $clc_pag = new stdClass();
                $clc_pag->id = "clc_pago";
                $clc_pag->name = "control";
                $clc_pag->placeholder = "Número de CLC";
                $clc_pag->label = "Número de CLC";
                $clc_pag->enDiv = true;
                $clc_pag->formato = "entero";
                if ($p == "c") {
                    $clc_pag->lectura = true;
                }

                return $clc_pag;
                break;
            case "clc_pag_ur":
                $clc_pag_ur = new stdClass();
                $clc_pag_ur->id = "clc_pag_ur";
                $clc_pag_ur->name = "control";
                $clc_pag_ur->required = true;
                $clc_pag_ur->enDiv = true;
                $clc_pag_ur->camposVal = "gpoid";
                $clc_pag_ur->agrupaPor = "gpoid";
                $clc_pag_ur->campoLabelAgrupacion = "ur_nombre";
                $clc_pag_ur->mensaje = "Selecciona";
                $clc_pag_ur->label = "Unidad Responsable";
                $clc_pag_ur->comoAtr = "gpoid";
                $clc_pag_ur->sql = '
                    SELECT CONCAT(gpo_id,ur_id) AS gpoid, ur_nombre
                    FROM cat_UnidadResponsable
                    WHERE  ur_clave IN (' . implode(",", $p) . ')';
                return $clc_pag_ur;
                break;
            case "nums_control":
                $nums_control = new stdClass();
                $nums_control->id = "ncp_control";
                $nums_control->name = "control";
                $nums_control->enDiv = true;
                $nums_control->camposVal = "ncc_id";
                $nums_control->agrupaPor = "importe";
                $nums_control->mensaje = "Selecciona";
                $nums_control->label = "Número de Control a pagar";
                $nums_control->campoLabelAgrupacion = "concepto";
                $nums_control->comoAtr = "ncc_id";
                return $nums_control;
                break;
            case "cbo_anio":
                $anio = new stdClass();
                $anio->id = "ncc_ejercicio";
                $anio->name = "control";
                $anio->required = true;
                $anio->enDiv = true;
                $anio->camposVal = "anio";
                $anio->agrupaPor = "anio";
                $anio->campoLabelAgrupacion = "anio";
                $anio->mensaje = "Selecciona";
                $anio->label = "Ejercicio/Año";
                $anio->comoAtr = "anio";
                $anio->sql = '
                    SELECT 2014 AS anio
                    UNION ALL SELECT
                    2015 AS anio
                    UNION ALL
                    SELECT 2016 AS anio
                    UNION ALL
                    SELECT 2017 AS anio';
                $anio->selecciona = 2017;
                return $anio;
                break;
        }
    }

    public function vwRechazoAutoAfe($afe, $mensaje)
    {
        $html = '
            <tr style="background-color:rgba(235,185,38,.5)!important;">
                <td style="width:5%;text-align:center">
                    ' . $afe['afe_id'] . '</td>
                <td>
                    ' . $afe['ue_id'] . '</td>
                <td>
                    ' . $afe['AAC'] . '</td>
                <td>
                    ' . $afe['clavefin'] . '</td>
                <td>
                    ' . $afe['afe_fechaElaboracion'] . '</td>
                <td align="right">
                    <span style="float:left">$</span>
                    ' . number_format($afe['afe_percepcion'], 2) . '</td>
                <td align="right">
                    <span style="float:left">$</span>
                    ' . number_format($afe['afe_retencion'], 2) . '</td>
                <td align="center">
                    Rechazada</td>
            </tr>
            <tr>
                <td colspan="8" style="padding-left:20px; font-size:10px;background-color:rgba(220,220,220,.6) !important;color:#F00; font-family:Arial;">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                    ERROR:' . $mensaje . '
                </td>
            </tr>
            ';
        return $html;
    }

    public function getTablaPrincipal($radios)
    {
        $tabla = new stdClass();
        $tabla->tools = $radios;
        $tabla->id = 'tbl_Conceptos';
        $tabla->titulo = 'Afectaciones registradas por la UR';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'scp/afectaciones/ControladorAfectacion.php';
        $tabla->comunica->metodo = 'llenaTablaAfectaciones';
        $tabla->comunica->tipo = '{getId()}';
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = array("afe_id");
        $tabla->numFilas = 10;
        $tabla->filasAmostrar = array(20, 50, 100, 250, 300);

        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = "eaf_id";
        $tabla->casoSeleccionable->valor = "A";
        $tabla->casoSeleccionable->operador = "!=";
        $tabla->casoSeleccionable->colorEnFalso = "rgba(8, 255, 18, 0.20)";
        $tabla->casoSeleccionable->ands = array();

        $and = new stdClass();
        $and->campo = "ecr_id";
        $and->operador = '==';
        $and->valor = 'SI';
        $and->valorTipo = 'string';
        array_push($tabla->casoSeleccionable->ands, $and);

        $and = new stdClass();
        $and->campo = "Detalle";
        $and->operador = '==';
        $and->valor = 'SI';
        $and->valorTipo = 'string';
        array_push($tabla->casoSeleccionable->ands, $and);

        $tr = array();
        $th = new stdClass();
        $th->texto = "Ver/Editar";
        $th->width = '5%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = "Detalle";
        $th->width = '4%';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Número';
        $th->width = '5%';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UE';
        $th->width = '5%';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Obra/Actividad';
        $th->width = '29%';
        $th->align = 'left';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Cve Fin.';
        $th->width = '8%';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha';
        $th->width = '10%';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Percepciones';
        $th->width = '13%';
        $th->align = 'right';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Retenciones';
        $th->width = '13%';
        $th->align = 'right';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Importe Neto';
        $th->width = '13%';
        $th->align = 'right';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Estado';
        $th->width = '13%';
        $th->align = 'center';
        array_push($tr, $th);

        $trbody = array(); // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = "ecr_id";
        $td->casos->operador = "==";
        $td->casos->valor = "SI";

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = "boton";
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->afe_id = 'afe_id';
        $td->casos->verdadero->attr->ur_id = 'ur_id';
        $td->casos->verdadero->attr->gpo_id = 'gpo_id';
        $td->casos->verdadero->attr->clc_id = 'clc_id';
        $td->casos->verdadero->class = "addModCedula btn-xs";
        $td->casos->verdadero->name = 'afe_id';
        $td->casos->verdadero->icono = "pencil";
        $td->casos->verdadero->id = 'afe_id';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = "texto";
        $td->casos->falso->campoTxt = "mensaje";
        $td->casos->falso->texto = "CARGA MASIVA";

        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = "ecr_id";
        $td->casos->operador = "==";
        $td->casos->valor = "SI";

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = "boton";
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->afe_id = 'afe_id';
        $td->casos->verdadero->attr->ur_id = 'ur_id';
        $td->casos->verdadero->attr->gpo_id = 'gpo_id';
        $td->casos->verdadero->attr->clc_id = 'clc_id';
        $td->casos->verdadero->class = "addModCedula2 btn-xs";
        $td->casos->verdadero->name = 'afe_id';
        $td->casos->verdadero->icono = "pencil";
        $td->casos->verdadero->id = 'afe_id';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = "texto";
        $td->casos->falso->campoTxt = "mensaje";
        $td->casos->falso->texto = "CARGA MASIVA";

        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        $td->align = "center";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ue_id';
        $td->align = "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = "link";
        $td->campoTxt = 'oa_nombre';
        $td->align = 'left';
        $td->class = 'oa_nombre_click';
        $td->attr = new stdClass();
        $td->attr->epg_id = "epg_id";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = "link";
        $td->campoTxt = 'cfi_clave';
        $td->align = 'center';
        $td->campoTitle = "cfi_nombre";
        $td->attr = new stdClass();
        $td->attr->ecf_id = "ecf_id";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'afe_fechaElaboracion';
        $td->align = 'center';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Percepciones';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Retenciones';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Importe';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = "semaforo";
        $td->campoTxt = 'eaf_nombre';
        $td->color = "eaf_color";
        $td->align = 'center';
        $td->lbl = "eaf_nombre";
        $td->campoTitle = "Fecha";
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        return $tabla;
    }
}
