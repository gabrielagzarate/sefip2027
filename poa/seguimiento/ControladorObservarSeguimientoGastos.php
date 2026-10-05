<?php

/* *
 * Description of ControladorObservarSeguimientoGastos
 *
 * @author ?
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Modif: 04-Noviembre-2020
 * Comment: -----
 * Objective:
- Mostrar los gatos por mes
- Mostrar los gatos por trimestre
- Guardar una Observacion
- Enviar una observación
- Mostrar el estado de la observacion
- Mostrar historial
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de lectura.
 * */
session_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/poa/seguimiento/consultaGastosOperacion.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ControladorMensajes.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/MensajeSeguimiento.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/HistorialMensajeSeguimiento.php';

class ControladorObservarSeguimientoGastos extends WebResponse
{
    private $id_Usuario;
    private $adm_grupo;
    private $adm_ur;
    private $claseB;
    private $icono;

    public function __construct()
    {
        parent::__construct();
        //variables de session
        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->claseB = 'btn btn-outline-success';
        $this->icono = 'fa-solid fa-floppy-disk me-2';

        if ($_SESSION[_S]['nvj_id'] > 1) {
            $this->id_Usuario = $_SESSION[_S]['usr_id'];
            switch ($this->metodo) {
                case 'verActividades':
                    $this->verActividades();
                    break;
                case 'verActividadesMens':
                    $this->verActividadesMens();
                    break;
                case 'seleccionaCorte':
                    $this->seleccionaCorte();
                    break;
                case 'seleccionaperiodo':
                    $this->seleccionaperiodo();
                    break;
                case 'Observacion':
                    $this->Observacion();
                    break;
                case 'ObservacionPresupuesto':
                    $this->ObservacionPresupuesto();
                    break;
                case 'Historial':
                    $this->Historial();
                    break;
                case 'EnviarEliminarMensaje':
                    $this->EnviarEliminarMensaje();
                    break;
                case 'ActualizaObservacion':
                    $this->ActualizaObservacion();
                    break;
                case 'ModificarMensaje':
                    $this->ModificarMensaje();
                    break;
                default:$this->main();
                    break;
            }
        } else {
            $this->sinPermisos();
        }
        $this->returnJson();
    } // Fin construct

    //Método principal
    private function main()
    {
        //Selecciona el tipo de corte
        $this->mixScript(
            '$("#combosp").unbind("change");
            $(".trim").unbind("click");' .
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {
                    metodo:"seleccionaCorte"
                });
            ')
        );
        $this->mixScript('$("#combour").change();');
    } // Fin main

    //Metodo que se usa para seleccionar el tipo de corte que se consultara.
    private function seleccionaCorte()
    {
        //Combo de selección de tipo de corte
        $comboPeriodo = new stdClass();
        $comboPeriodo->id = 'comboPeriodo';
        $comboPeriodo->name = 'comboPeriodo';
        $comboPeriodo->enDiv = true;
        $comboPeriodo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboPeriodo->label = 'Periodo';
        $comboPeriodo->search = true;
        $comboPeriodo->opciones = ['' => 'Seleccione una opción de la lista', 1 => 'Mensual', 2 => 'Trimestral'];
        $comboPeriodo->change = '
            if( $(this).val() != "" ){
                comunica("' . $this->pantalla . '",{
                    metodo : "seleccionaperiodo",
                    opc_id : $("#comboPeriodo").val()
                })
            }
        ';
        $comboTipoPeriodo = new stdClass();
        $comboTipoPeriodo->id = 'comboTipoPeriodo';
        $comboTipoPeriodo->name = 'comboTipoPeriodo';
        $comboTipoPeriodo->enDiv = true;
        $comboTipoPeriodo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboTipoPeriodo->opciones = [];
        $comboTipoPeriodo->search = true;
        //Inicialización del combbo
        $this->mixHtml('
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponCombo($comboPeriodo) . '
                </fieldset>
            </div>
        ');
        //Java para mostrar la información del corte seleccionado
        if (!empty($this->prm->mun_id)) {
            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo : "seleccionaperiodo",
                    opc_id : $("#comboPeriodo").val()
                });
            ');
        }
    } //Fin seleccionaCorte

    //Función que rellena la información del combo de acuerdo al corte seleccionado
    private function seleccionaperiodo()
    {
        //botón que regresa a la pantalla anterior
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'btn btn-outline-secondary me-1';
        $btnregresarindex->icon = 'fa-solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main",
            });
        ';

        $btnExportaPDF = new stdClass();
        $btnExportaPDF->id = 'btnExportaPDF';
        $btnExportaPDF->icon = 'fa-solid fa-file-pdf me-1';
        $btnExportaPDF->class = 'btn btn-outline-danger me-2 pdf';
        $btnExportaPDF->value = 'PDF';
        $btnExportaPDF->text = 'PDF';

        //Se declara el botón en el panel de herramientas
        $this->agregaTools($this->ponboton($btnregresarindex));
        $this->agregaTools($this->ponBoton($btnExportaPDF));
        $this->mixScript('
            $(".pdf").hide();
        ');

        //condicíón para evitar valores nulos
        $this->prm->opc_id = isset($this->prm->opc_id) ? $this->prm->opc_id : '';
        //SI LA OPCION ES 2, MOSTRARA LAS OPCIONES TRIMESTRALES
        if ($this->prm->opc_id == 2) {
            //Se llena la información del combo de opciones trimestrales
            $comboTipoPeriodo = new stdClass();
            $comboTipoPeriodo->id = 'comboTrimestre';
            $comboTipoPeriodo->label = 'Trimestre';
            $comboTipoPeriodo->mensaje = 'SELECCIONE EL TRIMESTRE A CONSULTAR';
            $comboTipoPeriodo->opciones = ['' => 'Seleccione una opción de la lista', 1 => 'Trimestre', 2 => 'Trimestre', 3 => 'Trimestre', 4 => 'Trimestre'];
            $comboTipoPeriodo->width = '400px';
            $comboTipoPeriodo->search = true;
            $comboTipoPeriodo->class = 'mb-4';
            $comboTipoPeriodo->selecciona = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 0;

            $ur = new stdClass();
            $ur->id = 'ur';
            $ur->size = 3;
            $ur->class = ' limpiar';
            $inputUr = '<legend>Ur</legend>' . $this->ponInput($ur);

            $html = '
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $inputUr . '
                        </fieldset>
                </div>
            ';

            //Llamado al Script que carga la información del combdo trimestral
            $comboTipoPeriodo->change = '
                if( $(this).val() != "" ){
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividades",
                        trimestre:$("#comboTrimestre").val(),
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . ',
                    });
                }
            ';
            $this->mixScript('$("#clasAdminSeccionCombos").hide();');

            //Nuevo objeto del tipo de unidad seleccionada
            $unidadR = new UnidadResponsable();
            $unidadR->setGpo_id($this->adm_grupo);
            $unidadR->setUr_id($this->adm_ur);
            $unidadR->select();

            if (isset($this->prm->ur_id) && $this->prm->ur_id != '' && isset($this->prm->pepg_id) && $this->prm->pepg_id != '') {
                ClasificacionAdministrativa::ponEnVariablesEpg($this->prm->pepg_id);
                $this->adm_ur = $this->prm->ur_id;
                $unidadR->setGpo_id($this->prm->gpo_id);
                $unidadR->setUr_id($this->prm->ur_id);
                $unidadR->select();
            }

            $tri = '<div><h4><b>' . $unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre() . '</b></h4></div>';
            $tri .= '<br/><br/><div style="width:100%; padding-left:30%; padding-bottom:50px;">';
            //Se repinta el combo de opciones trimestrales
            $this->mixHtml($this->ponCombo($comboTipoPeriodo));
            //Div que muestra el detalle de las actividades seleccionadas
            $this->mixHtml($tri . '</div><div class="row" id="actividades"></div>');
            //Script
            $this->mixScript('
                //función que llama a la tabla del trimestre seleccionado
                $(".trim").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividades",
                        trimestre:$(this).attr("trimestre"),
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . ',
                    });
                });
            ');
        }

        //SI LA OPCION ES 1, MOSTRARA LAS OPCIONES MENSUALES
        if ($this->prm->opc_id == 1) {
            $comboTipoPeriodo = new stdClass();
            $comboTipoPeriodo->id = 'comboMes';
            $comboTipoPeriodo->label = 'Mes';
            $comboTipoPeriodo->mensaje = 'SELECCIONE EL MES A CONSULTAR';
            $comboTipoPeriodo->opciones = ['' => 'Seleccione una opción de la lista', 1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
            $comboTipoPeriodo->width = '400px';
            $comboTipoPeriodo->class = 'mb-4';
            $comboTipoPeriodo->search = true;
            $comboTipoPeriodo->selecciona = (isset($this->prm->mes) && $this->prm->mes != '') ? $this->prm->mes : 0;
            //Llamado al Script que carga la información del combdo trimestral
            $comboTipoPeriodo->change = '
                if( $(this).val() != "" ){
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividadesMens",
                        mes:$("#comboMes").val(),
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . ',
                    });
                }';
            $this->mixScript('$("#clasAdminSeccionCombos").hide();');

            //Nuevo objeto del tipo de unidad seleccionada
            $unidadR = new UnidadResponsable();
            $unidadR->setGpo_id($this->adm_grupo);
            $unidadR->setUr_id($this->adm_ur);
            $unidadR->select();

            if (isset($this->prm->ur_id) && $this->prm->ur_id != '' && isset($this->prm->pepg_id) && $this->prm->pepg_id != '') {
                ClasificacionAdministrativa::ponEnVariablesEpg($this->prm->pepg_id);
                $this->adm_ur = $this->prm->ur_id;
                $unidadR->setGpo_id($this->prm->gpo_id);
                $unidadR->setUr_id($this->prm->ur_id);
                $unidadR->select();
            }
            $men = '<div><h4><b>' . $unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre() . '</b></h4></div>';
            $men .= '<br/><br/><div style="width:100%; padding-left:30%; padding-bottom:50px;">';
            //Muestra el combo de periodo mensual
            $this->mixHtml($this->ponCombo($comboTipoPeriodo));
            //Div que muestra el detalle de las actividades seleccionadas
            $this->mixHtml($men . '</div><div class="row" id="actividades"></div>');
            //Script
            $this->mixScript('
                //función que llama a la tabla del mes seleccionado
                $(".trim").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividadesMens",
                        mes:$(this).attr("mes"),
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . ',
                    });
                });
            ');
        }
    } // Fin seleccionaperiodo

    //Función que pinta el desglose del trimestre seleccionado
    private function verActividades()
    {
        $this->setContenedor('actividades');

        $titulo = '';
        $trimestreActivo = '';
        // $estadoBoton = 'disabled';
        $trimestreSeleccionado = $this->prm->trimestre;
        $fechaActual = date('Y-m-d');

        //Obtiene el trimestre activo
        if ($fechaActual == APP_EJERCICIO . '-01-01' || $fechaActual <= APP_EJERCICIO . '-03-31') {
            $trimestreActivo = 1;
        } elseif ($fechaActual == APP_EJERCICIO . '-04-01' || $fechaActual <= APP_EJERCICIO . '-06-30') {
            $trimestreActivo = 2;
        } elseif ($fechaActual == APP_EJERCICIO . '-07-01' || $fechaActual <= APP_EJERCICIO . '-09-30') {
            $trimestreActivo = 3;
        } elseif ($fechaActual == APP_EJERCICIO . '-10-01' || $fechaActual <= APP_EJERCICIO . '-12-31') {
            $trimestreActivo = 4;
        }
        //Verifica si el trimestre seleccionado es el activo
        if ($trimestreSeleccionado == $trimestreActivo) {
            //$estadoBoton = 'enabled';
        }

        switch ($this->prm->trimestre) {
            case 1:
                $titulo = 'PRIMER TRIMESTRE';
                break;
            case 2:
                $titulo = 'SEGUNDO TRIMESTRE';
                break;
            case 3:
                $titulo = 'TERCER TRIMESTRE';
                break;
            case 4:
                $titulo = 'CUARTO TRIMESTRE';
                break;
        }

        $filtro = ' and toa_id=1';
        $estado = ' hms.eme_id NOT IN (3) ';
        $claseEvento = 'verModalMensaje';
        $htm = '
            <legend style="text-align:center">' . $titulo . '</legend><br>
            <table  class="table table table-bordered table-hover">';

        $result = consultaGastosOperacion('', $this->prm->trimestre, $this->prm->gpo_id, $this->prm->ur_id, $filtro, $estado);
        $cvefin = '';
        $prg = '';
        $spr = '';
        $py = $estilo_btn_2 = $observa = '';
        $cambioClase = true;

        if (!empty($result)) {
            $this->mixScript('$(".pdf").show();');
        }

        while ($row = array_pop($result)) {
            /*SEMAFORIZACIÓN DE PRESUPUESTO*/
            //Establece los colores de la semaforización de presupuesto
            if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] <= 49) {
                $estilo_btn_2 = 'style="background-color: #b20000;"'; //Rojo
            }
            if ($row['porcentajePresupuesto'] > 50 && $row['porcentajePresupuesto'] <= 79) {
                $estilo_btn_2 = 'style="background-color: #f1ee0b;"'; //Amarillo
            }
            if ($row['porcentajePresupuesto'] > 80 && $row['porcentajePresupuesto'] <= 100) {
                $estilo_btn_2 = 'style="background-color: #35B200;"'; //Verde
            }

            //Muestra la clave de financiamiento
            if ($row['cfi_clave'] != $cvefin) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">CVFIN:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['cfi_clave'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['cfi_nombre'] . '</th>
                    </tr>';
                $cvefin = $row['cfi_clave'];
            }
            if ($row['prg_id'] != $prg) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">PR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['prg_nombre'] . '</th>
                    </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">SPR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                        <td align="left" colspan="15">' . $row['spr_nombre'] . '</td>
                        <th style="background-color: #5ED800; text-align:center; color:#000;" align="left" colspan="1">SEMAFORIZACIÓN</th>
                        <th style="background-color: #1C61EC; text-align:center; color:#000;" align="left" colspan="3">OBSERVACIONES</th>
                    </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">PYT:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['py_id'] . '</th>
                        <td align="left" colspan="5">' . $row['py_nombre'] . '</td>
                        <th class="success" align="center" colspan="2">APROBADO</th>
                        <th class="success" align="center" colspan="2">MODIFICADO</th>
                        <th class="success" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="success" align="center" colspan="2">DEVENGADO</th>
                        <th class="success" align="center" colspan="2">EJERCIDO</th>
                        <th class="success" align="center" colspan="1">PRESUPUESTO</th>
                        <th class="success" align="center" colspan="1">ESTADO</th>
                        <th class="success" align="center" colspan="1">OBSERVAR</th>
                        <th class="success" align="center" colspan="1">HISTORIAL</th>
                    </tr>';
            }
            $clase = ($row['eme_id'] != '' ? 'class="bg-conobservacion"' : 'class="bg-sinobservacion"');
            if ($row['eme_id'] == 1) {
                $observa = "<b style='color: #ffd700;'>OBSERVACIÓN GUARDADA</b>";
            } elseif ($row['eme_id'] != '') {
                $observa = "<b style='color: #FF1E2D;'>CON OBSERVACIÓN</b>";
            } else {
                $observa = "<b style='color: #00950A;'>SIN OBSERVACIÓN</b>";
            }
            $htm .= '
                <tr class="success" >
                    <td ' . $clase . ' align="center" colspan="1"></td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                    <td ' . $clase . ' align="left" colspan="2">' . $row['oa_nombre'] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['aprobado'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['vigente'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['comprometido'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['devengado'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['ejercido'], 2) . '</td>
                    <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small" ' . $estilo_btn_2 . ' title="Porcentaje de PRESUPUESTO ' . $row['porcentajePresupuesto'] . ' %"></button></td>
                    <td ' . $clase . ' align="center" colspan="1" >' . $observa . '</td>
                    <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button" id="verActividad" title="Opción para capturar observaciones" class="' . $claseEvento . ' btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                            <i class="fa-solid fa-comment"></i>
                        </button>
                    </td>
                    <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button" id="verHistorial" title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                            <i class="fa fa-list-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr class="default">
                    <th></th>
                        <th class ="divMetas" id="metas' . $row['epg_id'] . '" colspan = "20" >
                        </th>
                </tr>';
            $cambioClase = ($cambioClase) ? false : true;

            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
            $py = $row['py_id'];
        } //fin while

        $htm .= '</table>';

        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($this->adm_grupo);
        $unidadR->setUr_id($this->adm_ur);
        $unidadR->select();
        $unidadResp = ($unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre());

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verHistorial").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    epg_id:$(this).attr("epg_id"),
                    trimestre:' . $this->prm->trimestre . ',
                    tipo:"trimestral"
                });
            });

            $(".verModalMensaje").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"ObservacionPresupuesto",
                    epg_id:$(this).attr("epg_id"),
                    trimestre:' . $this->prm->trimestre . ',
                    tipo:"trimestral"
                });
            });

            $("#btnExportaPDF").click(function(){//exporta a pdf
                var form = $("<form>")
                    .attr("action","rep/doc/poa/seguimiento/rep_GastosOperacion.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "trimestre").attr("value", "' . $this->prm->trimestre . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "gpo").attr("value", "' . $this->prm->gpo_id . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur").attr("value", "' . $this->prm->ur_id . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "unidadResp").attr("value", "' . $unidadResp . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "estado").attr("value", "' . $estado . '"))
                    .append($("<input>").attr("type", "hi.dden").attr("name", "filtro").attr("value", "' . $filtro . '"))
                $("body").append(form);
                form.submit();
            });
        ');
    } // Fin verActividades

    //Función que pinta el desglose del mes seleccionado
    private function verActividadesMens()
    {
        $this->setContenedor('actividades');

        $nombreMes = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        $titulo = $nombreMes[$this->prm->mes];
        $claseEvento = 'verModalMensaje';
        $cvefin = '';
        $prg = '';
        $spr = '';
        $py = $estilo_btn_2 = '';

        $htm = '
            <legend style="text-align:center">' . $titulo . '</legend><br>
            <table  class="table table table-bordered table-hover">';

        $filtro = ' and toa_id=1';
        $estado = ' hms.eme_id NOT IN (3) ';
        $cambioClase = true;

        $result = consultaGastosOperacion($this->prm->mes, '', $this->prm->gpo_id, $this->prm->ur_id, $filtro, $estado);

        if (!empty($result)) {
            $this->mixScript('$(".pdf").show();');
        }

        while ($row = array_pop($result)) {
            /*SEMAFORIZACIÓN DE PRESUPUESTO*/
            //Establece los colores de la semaforización de presupuesto
            if ($row['porcentajePresupuesto'] >= 0 && $row['porcentajePresupuesto'] < 49) {
                $estilo_btn_2 = 'style="background-color: #b20000;"'; //Rojo
            }
            if ($row['porcentajePresupuesto'] >= 50 && $row['porcentajePresupuesto'] < 79) {
                $estilo_btn_2 = 'style="background-color: #f1ee0b;"'; //Amarillo
            }
            if ($row['porcentajePresupuesto'] >= 80 && $row['porcentajePresupuesto'] < 100) {
                $estilo_btn_2 = 'style="background-color: #35B200;"'; //Verde
            }
            //Muestra la clave de financiamiento
            if ($row['cfi_clave'] != $cvefin) {
                $htm .= '
                    <tr><th class="success" style="text-align:center" colspan="1">CVFIN:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['cfi_clave'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['cfi_nombre'] . '</th>
                    </tr>';
                $cvefin = $row['cfi_clave'];
            }
            if ($row['prg_id'] != $prg) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">PR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['prg_nombre'] . '</th>
                    </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">SPR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                        <td align="left" colspan="15">' . $row['spr_nombre'] . '</td>
                        <th style="background-color: #5ED800; text-align:center; color:#000;" align="left" colspan="1">SEMAFORIZACIÓN</th>
                        <th style="background-color: #1C61EC; text-align:center; color:#000;" align="left" colspan="3">OBSERVACIONES</th>
                    </tr>';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">PYT:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['py_id'] . '</th>
                        <td align="left" colspan="5">' . $row['py_nombre'] . '</td>
                        <th class="success" align="center" colspan="2">APROBADO</th>
                        <th class="success" align="center" colspan="2">MODIFICADO</th>
                        <th class="success" align="center" colspan="2">COMPROMETIDO</th>
                        <th class="success" align="center" colspan="2">DEVENGADO</th>
                        <th class="success" align="center" colspan="2">EJERCIDO</th>
                        <th class="success" align="center" colspan="1">PRESUPUESTO</th>
                        <th class="success" align="center" colspan="1">ESTADO</th>
                        <th class="success" align="center" colspan="1">OBSERVAR</th>
                        <th class="success" align="center" colspan="1">HISTORIAL</th>
                    </tr>';
            }
            $clase = ($row['eme_id'] != '' ? 'class="bg-conobservacion"' : 'class="bg-sinobservacion"');
            if ($row['eme_id'] == 1) {
                # code...
                $observa = "<b style='color: #f39f18;'>OBSERVACIÓN GUARDADA</b>";
            } elseif ($row['eme_id'] != '') {
                # code...
                $observa = "<b style='color: #FF1E2D;'>CON OBSERVACIÓN</b>";
            } else {
                $observa = "<b style='color: #00950A;'>SIN OBSERVACIÓN</b>";
            }
            $htm .= '
                <tr class="success">
                    <td ' . $clase . ' align="center" colspan="1"></td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['prg_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['spr_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['py_id'] . '</td>
                    <td ' . $clase . ' align="center" colspan="1">' . $row['oa_id'] . '</td>
                    <td ' . $clase . ' align="left" colspan="2">' . $row['oa_nombre'] . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['aprobado'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['vigente'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['comprometido'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['devengado'], 2) . '</td>
                    <td ' . $clase . ' align="right" colspan="2">' . number_format($row['ejercido'], 2) . '</td>
                    <td ' . $clase . ' align="center" colspan="1" ><button type="button" class=" btn btn-small" ' . $estilo_btn_2 . ' title="Porcentaje de PRESUPUESTO ' . $row['porcentajePresupuesto'] . ' %"></button></td>
                    <td ' . $clase . ' align="center" colspan="1" >' . $observa . '</td>
                    <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button"  id="verActividad" title="Opción para capturar observaciones" class="' . $claseEvento . ' btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                            <i class="fa-solid fa-comment"></i>
                        </button>
                    </td>
                    <td ' . $clase . ' align="center" colspan="1" >
                        <button type="button" id="verHistorial" title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                            <i class="fa fa-list-alt"></i>
                        </button>
                    </td>
                </tr>
                <tr class="default">
                    <th></th>
                    <th class ="divMetas" id="metas' . $row['epg_id'] . '" colspan = "20" >
                    </th>
                </tr>';
            $cambioClase = ($cambioClase) ? false : true;
            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
            $py = $row['py_id'];
        } //fin while

        $htm .= '</table>';
        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($this->adm_grupo);
        $unidadR->setUr_id($this->adm_ur);
        $unidadR->select();
        $unidadResp = ($unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre());

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verHistorial").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    epg_id:$(this).attr("epg_id"),
                    mes:' . $this->prm->mes . ',
                    tipo:"mensual"
                });
            });

            $(".verModalMensaje").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"ObservacionPresupuesto",
                    epg_id:$(this).attr("epg_id"),
                    mes:' . $this->prm->mes . ',
                    tipo:"mensual"
                });
            });

            $("#btnExportaPDF").click(function(){//exporta a pdf
                var form = $("<form>")
                    .attr("action","rep/doc/poa/seguimiento/rep_GastosOperacion.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "mes").attr("value", "' . $this->prm->mes . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "gpo").attr("value", "' . $this->prm->gpo_id . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur").attr("value", "' . $this->prm->ur_id . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "unidadResp").attr("value", "' . $unidadResp . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "estado").attr("value", "' . $estado . '"))
                    .append($("<input>").attr("type", "hidden").attr("name", "filtro").attr("value", "' . $filtro . '"))
                $("body").append(form);
                form.submit();
            });
        ');
    } // Fin verActividadesMens

    //***************************Funciones que se ocupan para realizar las Observaciones*******************************
    //*****************************************************************************************************************
    private function Observacion()
    {
        //Realiza guardado cuando es tipo trimestral
        if ($this->prm->tipo == 'trimestral') {
            $this->prm->epf_id = '000000';
            $this->prm->hms_mensual = '0';
            $this->prm->hms_mensajeRespuesta = ' ';
            $this->prm->mensajeEstado = (isset($this->prm->mensajeEstado) ? $this->prm->mensajeEstado . ' satisfactoriamente' : '');
            $this->prm->nim_id = 4;
            $this->prm->tim_id = (isset($this->prm->hms_tipo) ? $this->prm->hms_tipo : '');
            $this->prm->eme_id = (isset($this->prm->hms_estado) ? $this->prm->hms_estado : '');
            $this->prm->ese_id = 1;
            $this->prm->abc = 'a';
            $this->prm->msj_id = MensajeSeguimiento::abc($this->prm);
            HistorialMensajeSeguimiento::abc($this->prm);
            $this->mixScript('
                $("#myModal").modal("hide");
                notify("' . $this->prm->mensajeEstado . '");
                if (' . $this->prm->hms_estado . ' == 2 || ' . $this->prm->hms_estado . ' == 1) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividades",
                        trimestre:' . $this->prm->hms_trimestre . ',
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . ',
                    });
                }
            ');
        }
        //Realiza guardado cuando es tipo mensual
        if ($this->prm->tipo == 'mensual') {
            $this->prm->epf_id = '000000';
            $this->prm->hms_trimestre = '0';
            $this->prm->hms_mensajeRespuesta = ' ';
            $this->prm->mensajeEstado = (isset($this->prm->mensajeEstado) ? $this->prm->mensajeEstado . ' satisfactoriamente' : '');
            $this->prm->nim_id = 4;
            $this->prm->tim_id = (isset($this->prm->hms_tipo) ? $this->prm->hms_tipo : '');
            $this->prm->eme_id = (isset($this->prm->hms_estado) ? $this->prm->hms_estado : '');
            $this->prm->ese_id = 1;
            $this->prm->abc = 'a';
            $this->prm->msj_id = MensajeSeguimiento::abc($this->prm);
            HistorialMensajeSeguimiento::abc($this->prm);
            $this->mixScript('
                $("#myModal").modal("hide");
                notify("' . $this->prm->mensajeEstado . '");
                if (' . $this->prm->hms_estado . ' == 2 || ' . $this->prm->hms_estado . ' == 1) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividadesMens",
                        mes:' . $this->prm->hms_mensual . ',
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . ',
                    });
                }
            ');
        }
    } // Fin Observacion

    private function ObservacionPresupuesto()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //Realiza guardado cuando es tipo trimestral
        if ($this->prm->tipo == 'trimestral') {
            $this->conectar();
            $this->setSql('
                SELECT  pdi_id,ind_id, pii_id, ind_nombre
                FROM cat_MetaActividad
                INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                INNER JOIN cat_Indicador USING(ind_id,nim_id)
                INNER JOIN enc_Programatico USING(epg_id)
                WHERE gpo_id=' . $this->adm_grupo . ' AND ur_id=' . $this->adm_ur . ' AND epg_id=' . $this->prm->epg_id . ';
            ');
            //Valida que la obra/actividad tenga metas
            $this->consultar();
            $this->desconectar();
            $datos = $this->extraer();

            $this->prm->ind_id = $datos['ind_id'];

            if ($datos == null || count($datos) == 0) {
                $this->mixScript('
                    $("#myModal").modal("hide");
                    notify("Para enviar una observación se requiere que la obra/actividad tenga una meta","warning");
                ');
                return false;
            }

            $btnGuardar = new stdClass();
            $btnGuardar->id = 'btnGuardar';
            $btnGuardar->type = 'button';
            $btnGuardar->title = 'Guardar mensaje';
            $btnGuardar->class = $this->claseB;
            $btnGuardar->icon = $this->icono;
            $btnGuardar->value = 'Guardar';

            $btnEnviar = new stdClass();
            $btnEnviar->id = 'btnEnviar';
            $btnEnviar->type = 'button';
            $btnEnviar->title = 'Enviar Mensaje';
            $btnEnviar->class = $this->claseB;
            $btnEnviar->icon = 'fa fa-paper-plane me-2';
            $btnEnviar->value = 'Enviar';

            $descripcion = new stdClass();
            $descripcion->id = 'descripcion';
            $descripcion->required = true;
            $descripcion->enDiv = true;
            $descripcion->maxlength = 1000;
            $descripcion->width = '300px';
            $descripcion->height = '100px';

            $this->conectar();
            $this->setSql('
                SELECT
                    prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre
                FROM cat_ObraActividad
                INNER JOIN  enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                WHERE epg_id = ?
                ;
            ');

            $this->consultar([$this->prm->epg_id]);
            $this->desconectar();
            $row = $this->extraer();

            $por = new AccesoDatos();
            $por->conectar();
            $por->setSql('
                SELECT tri_id
                FROM cat_MetaActividadTrimestre mt
                INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                WHERE
                epg_id = ?;
            ');
            $por->consultar([$this->prm->epg_id]);
            $tri = $por->extraer();
            $por->desconectar();
            foreach ($tri as $v) {
                if ($tri = 1) {
                    $t1 = new AccesoDatos();
                    $t1->conectar();
                    $t1->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=1 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje1
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id= ?
                        AND tri_id=1
                    ');
                    $t1->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat1 = $t1->extraer();
                    $t1->desconectar();

                }
                if ($tri = 2) {
                    $t2 = new AccesoDatos();
                    $t2->conectar();
                    $t2->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=2 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje2
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id=?
                        AND tri_id=2
                    ');
                    $t2->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat2 = $t2->extraer();
                    $t2->desconectar();

                }
                if ($tri = 3) {
                    $t3 = new AccesoDatos();
                    $t3->conectar();
                    $t3->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=3 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje3
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id= ?
                        AND tri_id=3
                    ');
                    $t3->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat3 = $t3->extraer();
                    $t3->desconectar();
                }
                if ($tri = 4) {
                    $t4 = new AccesoDatos();
                    $t4->conectar();
                    $t4->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=4 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje4
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id= ?
                        AND tri_id=4
                    ');
                    $t4->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat4 = $t4->extraer();
                    $t4->desconectar();
                }
            }

            //Semaforizacion
            $color1 = '';
            $color2 = '';
            $color3 = '';
            $color4 = '';
            if ($dat1['porcentaje1'] >= 0 && $dat1['porcentaje1'] < 59.99) {
                $color1 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat1['porcentaje1'] >= 60 && $dat1['porcentaje1'] < 84.99) {
                $color1 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat1['porcentaje1'] >= 85 && $dat1['porcentaje1'] <= 115) {
                $color1 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat1['porcentaje1'] > 115) {
                $color1 = 'color="white" style="background-color: #b20000;"'; //rojo
            }
            //tri 2
            if ($dat2['porcentaje2'] >= 0 && $dat2['porcentaje2'] < 59.99) {
                $color2 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat2['porcentaje2'] >= 60 && $dat2['porcentaje2'] < 84.99) {
                $color2 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat2['porcentaje2'] >= 85 && $dat2['porcentaje2'] <= 115) {
                $color2 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat2['porcentaje2'] > 115) {
                $color2 = 'color="white" style="background-color: #b20000;"'; //rojo
            }

            //tri 3
            if ($dat3['porcentaje3'] >= 0 && $dat3['porcentaje3'] < 59.99) {
                $color3 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat3['porcentaje3'] >= 60 && $dat3['porcentaje3'] < 84.99) {
                $color3 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat3['porcentaje3'] >= 85 && $dat3['porcentaje3'] <= 115) {
                $color3 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat3['porcentaje3'] > 115) {
                $color3 = 'color="white" style="background-color: #b20000;"'; //rojo
            }

            //tri 4
            if ($dat4['porcentaje4'] >= 0 && $dat4['porcentaje4'] < 59.99) {
                $color4 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat4['porcentaje4'] >= 60 && $dat4['porcentaje4'] < 84.99) {
                $color4 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat4['porcentaje4'] >= 85 && $dat4['porcentaje4'] <= 115) {
                $color4 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat4['porcentaje4'] > 115) {
                $color4 = 'color="white" style="background-color: #b20000;"'; //rojo
            }

            $html = '
                <table class="mb-4">
                    <table  class="table table table-bordered table-hover">
                        <tr>
                            <tr>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">PR:</th>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                                <th style="text-align:left" colspan="17">' . $row['prg_nombre'] . '</th>
                            </tr>
                            <tr>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">SPR:</th>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                                <td align="left" colspan="17">' . $row['spr_nombre'] . '</td>
                            </tr>
                            <tr>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">PYT:</th>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['py_id'] . '</th>
                                <td align="left" colspan="5">' . $row['py_nombre'] . '</td>
                                <th class="bg-encabezado" align="center" colspan="2">APROBADO</th>
                                <th class="bg-encabezado" align="center" colspan="2">MODIFICADO</th>
                                <th class="bg-encabezado" align="center" colspan="2">COMPROMETIDO</th>
                                <th class="bg-encabezado" align="center" colspan="2">DEVENGADO</th>
                                <th class="bg-encabezado" align="center" colspan="2">EJERCIDO</th>
                            </tr>
                            <tr>
                                <td align="center" colspan="1"></td>
                                <td align="center" colspan="1">' . $row['prg_id'] . '</td>
                                <td align="center" colspan="1">' . $row['spr_id'] . '</td>
                                <td align="center" colspan="1">' . $row['py_id'] . '</td>
                                <td align="center" colspan="1">' . $row['oa_id'] . '</td>
                                <td align="left" colspan="2">' . $row['oa_nombre'] . '</td>
                                <td align="right" colspan="2">' . ((isset($row['aprobado']) && $row['aprobado'] != '') ? number_format($row['aprobado'], 2) : number_format(0, 2)) . '</td>
                                <td align="right" colspan="2">' . ((isset($row['vigente']) && $row['vigente'] != '') ? number_format($row['vigente'], 2) : number_format(0, 2)) . '</td>
                                <td align="right" colspan="2">' . ((isset($row['comprometido']) && $row['comprometido'] != '') ? number_format($row['comprometido'], 2) : number_format(0, 2)) . '</td>
                                <td align="right" colspan="2">' . ((isset($row['devengado']) && $row['devengado'] != '') ? number_format($row['devengado'], 2) : number_format(0, 2)) . '</td>
                                <td align="right" colspan="2">' . ((isset($row['ejercido']) && $row['ejercido'] != '') ? number_format($row['ejercido'], 2) : number_format(0, 2)) . '</td>
                            </tr>
                    </table>
                    <tr>
                        <td colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="black">
                        INDICADOR: ' . $datos['ind_id'] . ' ' . $datos['ind_nombre'] . '</font></b></td>
                    </tr>
                    <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color1 . '>
                            PORCENTAJE EJERCIDO EN EL PRIMER TRIMESTRE : ' . number_format($dat1['porcentaje1'], 2) . '%</b>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color2 . '>
                            PORCENTAJE EJERCIDO EN EL SEGUNDO TRIMESTRE: ' . number_format($dat2['porcentaje2'], 2) . '%</b>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color3 . '>
                            PORCENTAJE EJERCIDO EN EL TERCER TRIMESTRE: ' . number_format($dat3['porcentaje3'], 2) . '%</b>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color4 . '>
                            PORCENTAJE EJERCIDO EN EL CUARTO TRIMESTRE: ' . number_format($dat4['porcentaje4'], 2) . '%</b>
                        </font>
                    </td>
                    </tr>
                </table>
                <table class="table table table-bordered table-hover" >
                    <tr class="warning"><br>
                        <th colspan="2">OBSERVACIÓN</th>
                    </tr>
                    <tr>
                        <td colspan="2"><label id="lbl_count_char"></label>' . $this->ponTextArea($descripcion) . '</td>
                    </tr>
                    <tr>
                        <td>' . $this->ponBoton($btnEnviar) . '</td>
                        <td style="text-align:right;">' . $this->ponBoton($btnGuardar) . '</td>
                    </tr>
                <table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Historial</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").modal("show");
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });

                $("#lbl_count_char").text("Caracteres restantes: " + $("#descripcion").attr("maxlength"));
                $("#descripcion").keyup(function () {
                    var max = $(this).attr("maxlength");
                    var len = $(this).val().length;
                    if((max - len) == 0) {
                        $("#lbl_count_char").html("Caracteres restantes: <any style=\"color:red;\">" + (max - len) + "</any>");
                    } else {
                        $("#lbl_count_char").text("Caracteres restantes: " + (max - len));
                    }
                });

                $("#btnGuardar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"Observacion",
                        hms_mensaje:$("#descripcion").val(),
                        pii_id:' . $datos['pii_id'] . ',
                        epg_id:"' . $this->prm->epg_id . '",
                        hms_trimestre:' . $this->prm->trimestre . ',
                        hms_tipo: 2,
                        hms_estado: 1,
                        mensajeEstado: "Observacion guardada",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

                $("#btnEnviar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"Observacion",
                        hms_mensaje:$("#descripcion").val(),
                        pii_id:' . $datos['pii_id'] . ',
                        epg_id:"' . $this->prm->epg_id . '",
                        hms_trimestre:' . $this->prm->trimestre . ',
                        hms_tipo: 2,
                        hms_estado: 2,
                        mensajeEstado: "Observacion enviada",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });
            ');
        } //FIN IF

        //Realiza guardado cuando es tipo mensual
        if ($this->prm->tipo == 'mensual') {
            $this->conectar();
            $this->setSql('
                select  pdi_id,ind_id, pii_id, ind_nombre
                from cat_MetaActividad
                INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                INNER JOIN cat_Indicador USING(ind_id,nim_id)
                INNER JOIN enc_Programatico USING(epg_id)
                where gpo_id= ? and ur_id= ? and epg_id= ?;
            ');
            $params = [
                $this->adm_grupo,
                $this->adm_ur,
                $this->prm->epg_id,
            ];
            //Valida que la obra/actividad tenga metas
            $this->consultar($params);
            $this->desconectar();
            $datos = $this->extraer();
            if ($datos == null || count($datos) == 0) {
                $this->mixScript('
                    $("#myModal").modal("hide");
                    notify("Para enviar una observación se requiere que la obra/actividad tenga una meta","warning");
                ');
                return false;
            }

            $btnGuardar = new stdClass();
            $btnGuardar->id = 'btnGuardar';
            $btnGuardar->type = 'button';
            $btnGuardar->title = 'Guardar mensaje';
            $btnGuardar->class = $this->claseB;
            $btnGuardar->icon = $this->icono;
            $btnGuardar->value = 'Guardar';

            $btnEnviar = new stdClass();
            $btnEnviar->id = 'btnEnviar';
            $btnEnviar->type = 'button';
            $btnEnviar->title = 'Enviar Mensaje';
            $btnEnviar->class = $this->claseB;
            $btnEnviar->icon = 'fa fa-paper-plane me-2';
            $btnEnviar->value = 'Enviar';

            $descripcion = new stdClass();
            $descripcion->id = 'descripcion';
            $descripcion->required = true;
            $descripcion->enDiv = true;
            $descripcion->maxlength = 500;
            $descripcion->maxlength = 1000;
            $descripcion->width = '300px';
            $descripcion->height = '100px';

            $this->conectar();
            $this->setSql('
                SELECT
                    prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre
                FROM cat_ObraActividad
                INNER JOIN  enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                WHERE epg_id = ?;
            ');

            $this->consultar([$this->prm->epg_id]);
            $this->desconectar();
            $row = $this->extraer();

            $html = '
                <table class="mb-4">
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;PR:</td>
                        <td>' . $row['prg_id'] . ' ' . $row['prg_nombre'] . '</td>
                    </tr>
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;SPR:</td>
                        <td>' . $row['spr_id'] . ' ' . $row['spr_nombre'] . '</td>
                    </tr>
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;PYT:</td>
                        <td>' . $row['py_id'] . ' ' . $row['py_nombre'] . '</td>
                    </tr>
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;ACT:</td>
                        <td>' . $row['oa_id'] . ' ' . $row['oa_nombre'] . '</td>
                    </tr>
                        <td colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="black">
                        INDICADOR: ' . $datos['ind_id'] . ' ' . $datos['ind_nombre'] . '</font></b></td>
                    </tr>
                    <br>
                </table>

                <table class="table table table-bordered table-hover" >
                    <tr class="warning"><br>
                        <th colspan="2">OBSERVACIÓN</th>
                    </tr>
                    <tr>
                        <td colspan="2"><label id="lbl_count_char"></label>' . $this->ponTextArea($descripcion) . '</td>
                    </tr>
                    <tr>
                        <td>' . $this->ponBoton($btnEnviar) . '</td>
                        <td style="text-align:right;" >' . $this->ponBoton($btnGuardar) . '</td>
                    </tr>
                <table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Historial</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');

            $this->mixScript('
                $("#myModal").modal("show");
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });

                $("#lbl_count_char").text("Caracteres restantes: " + $("#descripcion").attr("maxlength"));
                $("#descripcion").keyup(function () {
                    var max = $(this).attr("maxlength");
                    var len = $(this).val().length;
                    if((max - len) == 0) {
                        $("#lbl_count_char").html("Caracteres restantes: <any style=\"color:red;\">" + (max - len) + "</any>");
                    } else {
                        $("#lbl_count_char").text("Caracteres restantes: " + (max - len));
                    }
                });

                $("#btnGuardar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"Observacion",
                        hms_mensaje:$("#descripcion").val(),
                        pii_id:' . $datos['pii_id'] . ',
                        epg_id:"' . $this->prm->epg_id . '",
                        hms_mensual:' . $this->prm->mes . ',
                        hms_tipo: 2,
                        hms_estado: 1,
                        mensajeEstado: "Observacion guardada",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

                $("#btnEnviar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"Observacion",
                        hms_mensaje:$("#descripcion").val(),
                        pii_id:' . $datos['pii_id'] . ',
                        epg_id:"' . $this->prm->epg_id . '",
                        hms_mensual:' . $this->prm->mes . ',
                        hms_tipo: 2,
                        hms_estado: 2,
                        mensajeEstado: "Observacion enviada",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });
            ');
        } // Fin if
    } // Fin ObservacionPresupuesto

    private function ActualizaObservacion()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //Realiza guardado cuando es tipo trimestral
        if ($this->prm->tipo == 'trimestral') {
            $this->conectar();
            $this->setSql('
                SELECT  pdi_id,ind_id, pii_id, ind_nombre
                FROM cat_MetaActividad
                INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                INNER JOIN cat_Indicador USING(ind_id,nim_id)
                INNER JOIN enc_Programatico USING(epg_id)
                WHERE gpo_id= ? AND ur_id= ? AND epg_id= ?;
            ');
            $params = [
                $this->adm_grupo,
                $this->adm_ur,
                $this->prm->epg_id,
            ];
            //Valida que la obra/actividad tenga metas
            $this->consultar($params);
            $this->desconectar();
            $datos = $this->extraer();

            $this->prm->ind_id = $datos['ind_id'];

            if (count($datos) == 0) {
                $this->mixScript('
                    $("#myModal").modal("hide");
                    notify("Para enviar una observación se requiere que la obra/actividad tenga una meta","warning");
                ');
                return false;
            }

            $hms = new AccesoDatos();
            $hms->conectar();
            $hms->setSql('
                SELECT hms_id, msj_id, hms_mensaje, eme_id, hms_mensajeRespuesta, hms_trimestre
                FROM cat_HistorialMensajeSeguimiento
                WHERE
                hms_id= ?
                AND hms_trimestre= ?
            ');
            $hms->consultar([$this->prm->hms_id,$this->prm->trimestre]);
            $men = $hms->extraer();
            $hms->desconectar();

            $btnModificar = new stdClass();
            $btnModificar->id = 'btnModificar';
            $btnModificar->type = 'button';
            $btnModificar->title = 'Actualizar mensaje';
            $btnModificar->class = $this->claseB;
            $btnModificar->icon = $this->icono;
            $btnModificar->value = 'Actualizar';

            $descripcion = new stdClass();
            $descripcion->id = 'descripcion';
            $descripcion->required = true;
            $descripcion->enDiv = true;
            $descripcion->maxlength = 1000;
            $descripcion->width = '300px';
            $descripcion->height = '100px';
            $descripcion->texto = $men['hms_mensaje'];

            $this->conectar();
            $this->setSql('
                SELECT
                    prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre
                FROM cat_ObraActividad
                INNER JOIN  enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                WHERE epg_id = ?
                ;
            ');

            $this->consultar([$this->prm->epg_id]);
            $this->desconectar();
            $row = $this->extraer();

            $por = new AccesoDatos();
            $por->conectar();
            $por->setSql('
                SELECT tri_id
                FROM cat_MetaActividadTrimestre mt
                INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                WHERE
                epg_id = ?;
            ');
            $por->consultar([$this->prm->epg_id]);
            $tri = $por->extraer();
            $por->desconectar();
            foreach ($tri as $v) {
                if ($tri = 1) {
                    $t1 = new AccesoDatos();
                    $t1->conectar();
                    $t1->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=1 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje1
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id= ?
                        AND tri_id=1
                    ');
                    $t1->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat1 = $t1->extraer();
                    $t1->desconectar();

                }
                if ($tri = 2) {
                    $t2 = new AccesoDatos();
                    $t2->conectar();
                    $t2->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=2 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje2
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id= ?
                        AND tri_id=2
                    ');
                    $t2->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat2 = $t2->extraer();
                    $t2->desconectar();

                }
                if ($tri = 3) {
                    $t3 = new AccesoDatos();
                    $t3->conectar();
                    $t3->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=3 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje3
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id= ?
                        AND tri_id=3
                    ');
                    $t3->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat3 = $t3->extraer();
                    $t3->desconectar();
                }
                if ($tri = 4) {
                    $t4 = new AccesoDatos();
                    $t4->conectar();
                    $t4->setSql('
                        SELECT pii_id,epg_id,tri_id,mact_original,mact_modificado,mact_alcanzado,ind_id,
                        (CASE
                            WHEN tri_id=4 THEN (mact_alcanzado*100)/mact_modificado
                        END) AS porcentaje4
                        From cat_MetaActividadTrimestre mt
                        INNER JOIN vin_ProductoIntermedioIndicador pi USING (pii_id)
                        WHERE
                        epg_id = ?
                        AND ind_id= ?
                        AND tri_id=4
                    ');
                    $t4->consultar([$this->prm->epg_id,$this->prm->ind_id]);
                    $dat4 = $t4->extraer();
                    $t4->desconectar();
                }
            }

            //Semaforizacion
            $color1 = '';
            $color2 = '';
            $color3 = '';
            $color4 = '';
            if ($dat1['porcentaje1'] >= 0 && $dat1['porcentaje1'] < 59.99) {
                $color1 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat1['porcentaje1'] >= 60 && $dat1['porcentaje1'] < 84.99) {
                $color1 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat1['porcentaje1'] >= 85 && $dat1['porcentaje1'] <= 115) {
                $color1 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat1['porcentaje1'] > 115) {
                $color1 = 'color="white" style="background-color: #b20000;"'; //rojo
            }
            //tri 2
            if ($dat2['porcentaje2'] >= 0 && $dat2['porcentaje2'] < 59.99) {
                $color2 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat2['porcentaje2'] >= 60 && $dat2['porcentaje2'] < 84.99) {
                $color2 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat2['porcentaje2'] >= 85 && $dat2['porcentaje2'] <= 115) {
                $color2 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat2['porcentaje2'] > 115) {
                $color2 = 'color="white" style="background-color: #b20000;"'; //rojo
            }

            //tri 3
            if ($dat3['porcentaje3'] >= 0 && $dat3['porcentaje3'] < 59.99) {
                $color3 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat3['porcentaje3'] >= 60 && $dat3['porcentaje3'] < 84.99) {
                $color3 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat3['porcentaje3'] >= 85 && $dat3['porcentaje3'] <= 115) {
                $color3 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat3['porcentaje3'] > 115) {
                $color3 = 'color="white" style="background-color: #b20000;"'; //rojo
            }

            //tri 4
            if ($dat4['porcentaje4'] >= 0 && $dat4['porcentaje4'] < 59.99) {
                $color4 = 'color="white" style="background-color: #b20000;"'; // ROjo
            }
            if ($dat4['porcentaje4'] >= 60 && $dat4['porcentaje4'] < 84.99) {
                $color4 = 'color="black" style="background-color: #f1ee0b;"'; // amarillo
            }
            if ($dat4['porcentaje4'] >= 85 && $dat4['porcentaje4'] <= 115) {
                $color4 = 'color="white" style="background-color: #35B200;"'; //verde
            }
            if ($dat4['porcentaje4'] > 115) {
                $color4 = 'color="white" style="background-color: #b20000;"'; //rojo
            }

            $html = '
                <table class="mb-4">
                <table  class="table table table-bordered table-hover">
                        <tr>
                            <tr>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">PR:</th>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                                <th style="text-align:left" colspan="17">' . $row['prg_nombre'] . '</th>
                            </tr>
                            <tr>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">SPR:</th>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                                <td align="left" colspan="17">' . $row['spr_nombre'] . '</td>
                            </tr>
                            <tr>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">PYT:</th>
                                <th class="bg-encabezado" style="text-align:center" colspan="1">' . $row['py_id'] . '</th>
                                <td align="left" colspan="5">' . $row['py_nombre'] . '</td>
                                <th class="bg-encabezado" align="center" colspan="2">APROBADO</th>
                                <th class="bg-encabezado" align="center" colspan="2">MODIFICADO</th>
                                <th class="bg-encabezado" align="center" colspan="2">COMPROMETIDO</th>
                                <th class="bg-encabezado" align="center" colspan="2">DEVENGADO</th>
                                <th class="bg-encabezado" align="center" colspan="2">EJERCIDO</th>
                            </tr>
                            <tr>
                                <td align="center" colspan="1"></td>
                                <td align="center" colspan="1">' . $row['prg_id'] . '</td>
                                <td align="center" colspan="1">' . $row['spr_id'] . '</td>
                                <td align="center" colspan="1">' . $row['py_id'] . '</td>
                                <td align="center" colspan="1">' . $row['oa_id'] . '</td>
                                <td align="left" colspan="2">' . $row['oa_nombre'] . '</td>
                                <td align="right" colspan="2">' . number_format($row['aprobado'], 2) . '</td>
                                <td align="right" colspan="2">' . number_format($row['vigente'], 2) . '</td>
                                <td align="right" colspan="2">' . number_format($row['comprometido'], 2) . '</td>
                                <td align="right" colspan="2">' . number_format($row['devengado'], 2) . '</td>
                                <td align="right" colspan="2">' . number_format($row['ejercido'], 2) . '</td>
                            </tr>
                    </table>
                    <tr>
                        <td colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="black">
                        INDICADOR: ' . $datos['ind_id'] . ' ' . $datos['ind_nombre'] . '</font></b></td>
                    </tr>
                    <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color1 . '>
                            PORCENTAJE EJERCIDO EN EL PRIMER TRIMESTRE : ' . number_format($dat1['porcentaje1'], 2) . '%</b>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color2 . '>
                            PORCENTAJE EJERCIDO EN EL SEGUNDO TRIMESTRE: ' . number_format($dat2['porcentaje2'], 2) . '%</b>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color3 . '>
                            PORCENTAJE EJERCIDO EN EL TERCER TRIMESTRE: ' . number_format($dat3['porcentaje3'], 2) . '%</b>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <font ' . $color4 . '>
                            PORCENTAJE EJERCIDO EN EL CUARTO TRIMESTRE: ' . number_format($dat4['porcentaje4'], 2) . '%</b>
                        </font>
                    </td>
                    </tr>
                </table>
                <table class="table table table-bordered table-hover" >
                    <tr class="warning"><br>
                        <th colspan="2">OBSERVACIÓN</th>
                    </tr>
                    <tr>
                        <td colspan="2"><label id="lbl_count_char"></label>' . $this->ponTextArea($descripcion) . '</td>
                    </tr>
                    <tr>
                        <td style="text-align:right;">' . $this->ponBoton($btnModificar) . '</td>
                    </tr>
                <table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Historial</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").modal("show");
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });

                $("#lbl_count_char").text("Caracteres restantes: " + $("#descripcion").attr("maxlength"));
                $("#descripcion").keyup(function () {
                    var max = $(this).attr("maxlength");
                    var len = $(this).val().length;
                    if((max - len) == 0) {
                        $("#lbl_count_char").html("Caracteres restantes: <any style=\"color:red;\">" + (max - len) + "</any>");
                    } else {
                        $("#lbl_count_char").text("Caracteres restantes: " + (max - len));
                    }
                });

                $("#btnModificar").click(function(){
                    $("#myModal").modal("hide");
                    comunica("' . $this->pantalla . '",{
                        metodo:"ModificarMensaje",
                        hms_mensaje:$("#descripcion").val(),
                        epg_id:"' . $this->prm->epg_id . '",
                        hms_trimestre:' . $this->prm->trimestre . ',
                        msj_id:$(this).attr("msj_id"),
                        hms_id: ' . $this->prm->hms_id . ',
                        mensajeEstado: "Observacion guardada",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

            ');
        } //FIN IF

        //Realiza guardado cuando es tipo mensual
        if ($this->prm->tipo == 'mensual') {
            $this->conectar();
            $this->setSql('
                select  pdi_id,ind_id, pii_id, ind_nombre
                from cat_MetaActividad
                INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                INNER JOIN cat_Indicador USING(ind_id,nim_id)
                INNER JOIN enc_Programatico USING(epg_id)
                where gpo_id= ? and ur_id= ? and epg_id= ?;
            ');
            $params = [
                $this->adm_grupo,
                $this->adm_ur,
                $this->prm->epg_id,
            ];
            //Valida que la obra/actividad tenga metas
            $this->consultar($params);
            $this->desconectar();
            $datos = $this->extraer();
            if ($datos == null || count($datos) == 0) {
                $this->mixScript('
                    $("#myModal").modal("hide");
                    notify("Para enviar una observación se requiere que la obra/actividad tenga una meta","warning");
                ');
                return false;
            }

            $hms = new AccesoDatos();
            $hms->conectar();
            $hms->setSql('
                SELECT hms_id, msj_id, hms_mensaje, eme_id, hms_mensajeRespuesta, hms_trimestre
                FROM cat_HistorialMensajeSeguimiento
                WHERE
                hms_id= ?
                AND hms_mensual= ?
            ');
            $hms->consultar([$this->prm->hms_id,$this->prm->mes]);
            $men = $hms->extraer();
            $hms->desconectar();


            $btnModificar = new stdClass();
            $btnModificar->id = 'btnModificar';
            $btnModificar->type = 'button';
            $btnModificar->title = 'Actualizar mensaje';
            $btnModificar->class = $this->claseB;
            $btnModificar->icon = $this->icono;
            $btnModificar->value = 'Actualizar';

            $descripcion = new stdClass();
            $descripcion->id = 'descripcion';
            $descripcion->required = true;
            $descripcion->enDiv = true;
            $descripcion->maxlength = 1000;
            $descripcion->width = '300px';
            $descripcion->height = '100px';
            $descripcion->texto = $men['hms_mensaje'];

            $this->conectar();
            $this->setSql('
                SELECT
                    prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre
                FROM cat_ObraActividad
                INNER JOIN  enc_Programatico USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                WHERE epg_id = ?;
            ');

            $this->consultar([$this->prm->epg_id]);
            $this->desconectar();
            $row = $this->extraer();

            $html = '
                <table class="mb-4">
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;PR:</td>
                        <td>' . $row['prg_id'] . ' ' . $row['prg_nombre'] . '</td>
                    </tr>
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;SPR:</td>
                        <td>' . $row['spr_id'] . ' ' . $row['spr_nombre'] . '</td>
                    </tr>
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;PYT:</td>
                        <td>' . $row['py_id'] . ' ' . $row['py_nombre'] . '</td>
                    </tr>
                    <tr>
                        <td width="40">&nbsp;&nbsp;&nbsp;&nbsp;ACT:</td>
                        <td>' . $row['oa_id'] . ' ' . $row['oa_nombre'] . '</td>
                    </tr>
                        <td colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="black">
                        INDICADOR: ' . $datos['ind_id'] . ' ' . $datos['ind_nombre'] . '</font></b></td>
                    </tr>
                    <br>
                </table>

                <table class="table table table-bordered table-hover" >
                    <tr class="warning"><br>
                        <th colspan="2">OBSERVACIÓN</th>
                    </tr>
                    <tr>
                        <td colspan="2"><label id="lbl_count_char"></label>' . $this->ponTextArea($descripcion) . '</td>
                    </tr>
                    <tr>
                        <td style="text-align:right;" >' . $this->ponBoton($btnModificar) . '</td>
                    </tr>
                <table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Historial</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');

            $this->mixScript('
                $("#myModal").modal("show");
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });

                $("#lbl_count_char").text("Caracteres restantes: " + $("#descripcion").attr("maxlength"));
                $("#descripcion").keyup(function () {
                    var max = $(this).attr("maxlength");
                    var len = $(this).val().length;
                    if((max - len) == 0) {
                        $("#lbl_count_char").html("Caracteres restantes: <any style=\"color:red;\">" + (max - len) + "</any>");
                    } else {
                        $("#lbl_count_char").text("Caracteres restantes: " + (max - len));
                    }
                });

                $("#btnModificar").click(function(){
                    $("#myModal").modal("hide");
                    comunica("' . $this->pantalla . '",{
                        metodo:"ModificarMensaje",
                        hms_mensaje:$("#descripcion").val(),
                        epg_id:"' . $this->prm->epg_id . '",
                        hms_mensual:' . $this->prm->mes . ',
                        msj_id:$(this).attr("msj_id"),
                        hms_id: ' . $this->prm->hms_id . ',
                        mensajeEstado: "Observacion guardada",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });
            ');
        } // Fin if
    } // Fin ActualizaObservacion


    // Funcion que muestra el historial de mensajes que se han enviado para revisión de SEFIN a UR
    private function Historial()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $nombreTrimestre = '';

        if ($this->prm->tipo == 'trimestral') {
            $trimestreSeleccionado = $this->prm->trimestre;
            //Selecciona trimestre
            switch ($trimestreSeleccionado) {
                case 1:
                    $nombreTrimestre = 'PRIMER TRIMESTRE';
                    break;
                case 2:
                    $nombreTrimestre = 'SEGUNDO TRIMESTRE';
                    break;
                case 3:
                    $nombreTrimestre = 'TERCER TRIMESTRE';
                    break;
                case 4:
                    $nombreTrimestre = 'CUARTO TRIMESTRE';
                    break;
            } // fin switch

            //Llamado a la función, para actualizar el estatus solo si es 5 a 6
            $this->ActualizaVisto();
            //Consulta que muestra el detalle del historial del movimiento seleccionado
            $this->conectar();
            $this->setSql('
                SELECT hms_mensaje, eme_id,hm.usr_id AS usrCaptura,usr_nombre, DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha, TIME(hm.usr_fecha) hora,eme_nombre,
                    msj_id,hms_id,hms_mensajeRespuesta,(SELECT cu.usr_nombre FROM ctrlUsuario.cat_Usuario cu WHERE cu.usr_id =hm.hms_Respuesta) usuarioRespuesta,
                    IF(hm.hms_FechaRespuesta IS NULL," ",DATE_FORMAT(hm.hms_FechaRespuesta,"%d/%m/%y")) AS fechaRespuesta,
                    IF(hm.hms_FechaRespuesta IS NULL," ",TIME(hm.hms_FechaRespuesta)) AS horaRespuesta
                FROM vin_ProductoIntermedioIndicador
                LEFT JOIN cat_MensajeSeguimiento using(pii_id)
                LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                LEFT JOIN cat_EstadoMensajeSeguimiento eme using(eme_id)
                INNER JOIN ctrlUsuario.cat_Usuario u  on hm.usr_id = u.usr_id
                WHERE epg_id= ? AND  hm.eme_id not in(3)
                AND  hm.tim_id = 2 AND hm.hms_trimestre = ?
                ORDER BY  hm.usr_fecha DESC
            ');
            $this->consultar([$this->prm->epg_id,$trimestreSeleccionado]);
            $this->desconectar();

            $html = '
                <table class="table table table-bordered table-hover" >
                    <tr class="warning">
                        <th class="warning" style="text-align:center" align="left" colspan="4">SEFIN</th>
                        <th class="warning" style="text-align:center" align="left" colspan="3">UR</th>
                        <th class="warning" style="text-align:center" align="left" colspan="2">ACCION</th>
                    </tr>
                    <tr class="">
                        <th align="center">MENSAJE</th>
                        <th>ESTADO</th>
                        <th>USUARIO</th>
                        <th>FECHA</th>
                        <th align="center">RESPUESTA</th>
                        <th>USUARIO</th>
                        <th>FECHA</th>
                        <th colspan="2"></th>
                    </tr>
                    <tr> ';

            while ($row = $this->extraer()) {
                $botones = '';
                $btnEnviar = new stdClass();
                $btnEnviar->id = 'btnEnviar';
                $btnEnviar->type = 'button';
                $btnEnviar->title = 'Enviar observación';
                $btnEnviar->class = $this->claseB . ' enviar me-1';
                $btnEnviar->icon = 'fa fa-paper-plane';
                $btnEnviar->attr = new stdClass();
                $btnEnviar->attr->msj_id = $row['msj_id'];
                $btnEnviar->attr->hms_id = $row['hms_id'];

                $btnEliminar = new stdClass();
                $btnEliminar->id = 'btnEliminar';
                $btnEliminar->type = 'button';
                $btnEliminar->title = 'Eliminar mensaje';
                $btnEliminar->class = 'btn btn-outline-danger eliminar me-1';
                $btnEliminar->icon = 'fa-solid fa-trash';
                $btnEliminar->attr = new stdClass();
                $btnEliminar->attr->msj_id = $row['msj_id'];
                $btnEliminar->attr->hms_id = $row['hms_id'];

                $btnEditar = new stdClass();
                $btnEditar->id = 'btnEditar';
                $btnEditar->type = 'button';
                $btnEditar->title = 'Actualizar mensaje';
                $btnEditar->class = 'btn btn-outline-secondary editar';
                $btnEditar->icon = 'fa-solid fa-pencil';
                $btnEditar->attr = new stdClass();
                $btnEditar->attr->msj_id = $row['msj_id'];
                $btnEditar->attr->hms_id = $row['hms_id'];

                //Obtiene el estado de los mensajes
                switch ($row['eme_id']) {
                    case 1:
                        //Verifica si el trimestre seleccionado es el activo
                        $botones = '<td>' . $this->ponBoton($btnEnviar) . $this->ponBoton($btnEliminar) . $this->ponBoton($btnEditar) . '</td>';
                        break;
                }
                //Informacion de la consulta
                $html .= '
                        <td>' . $row['hms_mensaje'] . '</td>
                        <td>' . $row['eme_nombre'] . '</td>
                        <td>' . $row['usr_nombre'] . '</td>
                        <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                        <td>' . $row['hms_mensajeRespuesta'] . '</td>
                        <td>' . $row['usuarioRespuesta'] . '</td>
                        <td>' . $row['fechaRespuesta'] . ' ' . $row['horaRespuesta'] . '</td>
                        ' . $botones . '
                    </tr>
                ';
            } //Fin while
            $html .= '<table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">SEGUIMIENTO A GASTOS DE OPERACIÓN - ' . $nombreTrimestre . ' - HISTORIAL</h4>
                                <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->mixScript('
                $("#myModal").modal("show");
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });

                $(".enviar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"EnviarEliminarMensaje",
                        tarea:"c",
                        msj_id:$(this).attr("msj_id"),
                        hms_id:$(this).attr("hms_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        trimestre:"' . $trimestreSeleccionado . '",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

                $(".eliminar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"EnviarEliminarMensaje",
                        tarea:"b",
                        msj_id:$(this).attr("msj_id"),
                        hms_id:$(this).attr("hms_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        trimestre:"' . $trimestreSeleccionado . '",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

                $(".editar").click(function(){
                    $("#myModal").modal("hide");
                    comunica("' . $this->pantalla . '",{
                        metodo:"ActualizaObservacion",
                        msj_id:$(this).attr("msj_id"),
                        hms_id:$(this).attr("hms_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        trimestre:"' . $trimestreSeleccionado . '",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

                // se agrega para recargar los datos al cerrar la modal tipo trimestral
                $(".cerrar").click( function(){
                    comunica("' . $this->pantalla . '",{
                        metodo   : "verActividades",
                        trimestre:' . $this->prm->trimestre . ',
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . '
                    });
                });
            ');

        } //FIN IF trimestral
        if ($this->prm->tipo == 'mensual') {
            $mesSeleccionado = $this->prm->mes;
            $nombreMes = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
            //Llamado a la función, para actualizar el estatus solo si es 5 a 6
            $this->ActualizaVisto();
            //Consulta que muestra el detalle del historial del movimiento seleccionado
            $this->conectar();
            $this->setSql('
                SELECT hms_mensaje, eme_id,hm.usr_id AS usrCaptura,usr_nombre, DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha, TIME(hm.usr_fecha) hora,eme_nombre,
                    msj_id,hms_id,hms_mensajeRespuesta,(SELECT cu.usr_nombre FROM ctrlUsuario.cat_Usuario cu WHERE cu.usr_id =hm.hms_Respuesta) usuarioRespuesta,
                    IF(hm.hms_FechaRespuesta IS NULL," ",DATE_FORMAT(hm.hms_FechaRespuesta,"%d/%m/%y")) AS fechaRespuesta,
                    IF(hm.hms_FechaRespuesta IS NULL," ",TIME(hm.hms_FechaRespuesta)) AS horaRespuesta
                FROM vin_ProductoIntermedioIndicador
                LEFT JOIN cat_MensajeSeguimiento using(pii_id)
                LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                LEFT JOIN cat_EstadoMensajeSeguimiento eme using(eme_id)
                INNER JOIN ctrlUsuario.cat_Usuario u  on hm.usr_id = u.usr_id
                WHERE epg_id= ? AND  hm.eme_id not in(3)
                AND hm.tim_id = 2 AND hm.hms_mensual = ?
                ORDER BY hm.usr_fecha DESC
            ');
            $this->consultar([$this->prm->epg_id,$mesSeleccionado]);
            $this->desconectar();
            $html = '
                <table class="table table table-bordered table-hover" >
                    <tr class="warning">
                        <th class="warning" style="text-align:center" align="left" colspan="4">SEFIN </th>
                        <th class="warning" style="text-align:center" align="left" colspan="3">UR</th>
                        <th class="warning" style="text-align:center" align="left" colspan="2">ACCION</th>
                    </tr>
                    <tr class="">
                        <th align="center">MENSAJE</th>
                        <th>ESTADO</th>
                        <th>USUARIO</th>
                        <th>FECHA</th>
                        <th align="center">RESPUESTA</th>
                        <th>USUARIO</th>
                        <th>FECHA</th>
                        <th colspan="2"></th>
                    </tr><tr> ';

            while ($row = $this->extraer()) {
                $botones = '';

                $btnEnviar = new stdClass();
                $btnEnviar->id = 'btnEnviar';
                $btnEnviar->type = 'button';
                $btnEnviar->title = 'Enviar observación';
                $btnEnviar->class = $this->claseB . ' enviar me-1';
                $btnEnviar->icon = 'fa fa-paper-plane';
                $btnEnviar->attr = new stdClass();
                $btnEnviar->attr->msj_id = $row['msj_id'];
                $btnEnviar->attr->hms_id = $row['hms_id'];

                $btnEliminar = new stdClass();
                $btnEliminar->id = 'btnEliminar';
                $btnEliminar->type = 'button';
                $btnEliminar->title = 'Eliminar mensaje';
                $btnEliminar->class = 'btn btn-outline-danger eliminar me-1';
                $btnEliminar->icon = 'fa-solid fa-trash';
                $btnEliminar->attr = new stdClass();
                $btnEliminar->attr->msj_id = $row['msj_id'];
                $btnEliminar->attr->hms_id = $row['hms_id'];

                $btnEditar = new stdClass();
                $btnEditar->id = 'btnEditar';
                $btnEditar->type = 'button';
                $btnEditar->title = 'Actualizar mensaje';
                $btnEditar->class = 'btn btn-outline-secondary editar';
                $btnEditar->icon = 'fa-solid fa-pencil';
                $btnEditar->attr = new stdClass();
                $btnEditar->attr->msj_id = $row['msj_id'];
                $btnEditar->attr->hms_id = $row['hms_id'];

                //Obtiene el estado de los mensajes
                switch ($row['eme_id']) {
                    case 1:
                        //Verifica si el trimestre seleccionado es el activo
                        $botones = '<td>' . $this->ponBoton($btnEnviar) . $this->ponBoton($btnEliminar) . $this->ponBoton($btnEditar) . '</td>';

                        break;
                }
                //Informacion de la consulta
                $html .= '
                        <td>' . $row['hms_mensaje'] . '</td>
                        <td>' . $row['eme_nombre'] . '</td>
                        <td>' . $row['usr_nombre'] . '</td>
                        <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                        <td>' . $row['hms_mensajeRespuesta'] . '</td>
                        <td>' . $row['usuarioRespuesta'] . '</td>
                        <td>' . $row['fechaRespuesta'] . ' ' . $row['horaRespuesta'] . '</td>
                        ' . $botones . '
                    </tr>
                ';
            } //fin while
            $html .= '<table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">SEGUIMIENTO A GASTOS DE OPERACIÓN - ' . $nombreMes[$mesSeleccionado] . ' - HISTORIAL</h4>
                                <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');

            $this->mixScript('
                $("#myModal").modal("show");
                $("#myModal").on("hidden.bs.modal", function (e) {
                    $("#myModal").remove();
                });

                $(".enviar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"EnviarEliminarMensaje",
                        tarea:"c",
                        msj_id:$(this).attr("msj_id"),
                        hms_id:$(this).attr("hms_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        mes:"' . $mesSeleccionado . '",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

                $(".eliminar").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"EnviarEliminarMensaje",
                        tarea:"b",
                        msj_id:$(this).attr("msj_id"),
                        hms_id:$(this).attr("hms_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        mes:"' . $mesSeleccionado . '",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });

                $(".editar").click(function(){
                    $("#myModal").modal("hide");
                    comunica("' . $this->pantalla . '",{
                        metodo:"ActualizaObservacion",
                        msj_id:$(this).attr("msj_id"),
                        hms_id:$(this).attr("hms_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        mes:"' . $mesSeleccionado . '",
                        tipo:"' . $this->prm->tipo . '"
                    });
                });
            ');

            // se agrega para recargar los datos al cerrar la modal tipo mensual
            $this->mixScript('
                $(".cerrar").click( function(){
                    comunica("' . $this->pantalla . '",{
                        metodo   : "verActividadesMens",
                        mes:' . $this->prm->mes . ',
                        gpo_id:' . $this->adm_grupo . ',
                        ur_id:' . $this->adm_ur . '
                    });
                });
            ');
        } //fin if
        //Actualiza el num de mensajes
        $this->mixScript(ControladorMensajes::consultaMensajes());
    } //Fin Historial

    private function ModificarMensaje()
    {
        $params = [];
        $this->prm->hms_mensaje = strtoupper($this->prm->hms_mensaje);
        //cuando es trimestral
        if ($this->prm->tipo == 'trimestral') {
            $this->conectar();
            $this->setSql('
                UPDATE cat_HistorialMensajeSeguimiento
                set hms_mensaje = "' . $this->prm->hms_mensaje . '"
                WHERE
                hms_id = ? AND hms_trimestre = ?
            ');
            $params = [$this->prm->hms_id,$this->prm->hms_trimestre];

            $this->mixScript('
                $("#myModal").modal("hide");
                notify("El mensaje ha sido modificado.");
                comunica("' . $this->pantalla . '",{
                    metodo:"verActividades",
                    trimestre:' . $this->prm->hms_trimestre . ',
                    gpo_id:' . $this->adm_grupo . ',
                    ur_id:' . $this->adm_ur . ',
                });
            ');
        }
        //cuando es mensual
        if ($this->prm->tipo == 'mensual') {
            $this->conectar();
            $this->setSql('
                UPDATE cat_HistorialMensajeSeguimiento
                set hms_mensaje = "' . $this->prm->hms_mensaje . '"
                WHERE
                hms_id = ? AND hms_mensual = ?
            ');
            $params = [$this->prm->hms_id,$this->prm->hms_mensual];
            $this->mixScript('
                $("#myModal").modal("hide");
                notify("El mensaje ha sido modificado.");
                comunica("' . $this->pantalla . '",{
                    metodo:"verActividadesMens",
                    mes:' . $this->prm->hms_mensual . ',
                    gpo_id:' . $this->adm_grupo . ',
                    ur_id:' . $this->adm_ur . ',
                });
            ');
        }

        $this->consultar($params);
    }

    private function EnviarEliminarMensaje()
    {
        $estado = $mensaje = '';
        switch ($this->prm->tarea) {
            case 'b': //Eliminar
                $estado = '3';
                $mensaje = 'ELIMINADO';
                break;
            case 'c': //Enviar
                $estado = '2';
                $mensaje = 'ENVIADO';
                break;
        }

        $this->conectar();
        $this->setSql('
            UPDATE cat_HistorialMensajeSeguimiento SET eme_id=' . $estado . ' WHERE msj_id=? AND hms_id=?;
        ');
        $this->consultar([$this->prm->msj_id,$this->prm->hms_id]);
        $this->desconectar();

        //cuando es trimestral
        if ($this->prm->tipo == 'trimestral') {
            $this->mixScript('
                $("#myModal").modal("hide");
                notify("El mensaje ha sido ' . $mensaje . '");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    trimestre:"' . $this->prm->trimestre . '",
                    epg_id:"' . $this->prm->epg_id . '",
                    tipo:"' . $this->prm->tipo . '"
                });
            ');
        }
        //cuando es mensual
        if ($this->prm->tipo == 'mensual') {
            $this->mixScript('
                $("#myModal").modal("hide");
                notify("El mensaje ha sido ' . $mensaje . '");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
                    mes:"' . $this->prm->mes . '",
                    epg_id:"' . $this->prm->epg_id . '",
                    tipo:"' . $this->prm->tipo . '"
                });
            ');
        }
    }

    private function ActualizaVisto()
    {
        if ($this->prm->tipo == 'trimestral') {
            $trimestreSeleccionado = $this->prm->trimestre;
            $this->conectar();
            $this->setSql('
                UPDATE cat_HistorialMensajeSeguimiento Uhi,(
                    SELECT msj_id
                FROM vin_ProductoIntermedioIndicador
                    LEFT JOIN cat_MensajeSeguimiento using(pii_id)
                    LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                    INNER JOIN cat_Usuario u  on hm.usr_id = u.usr_id
                WHERE epg_id=?
                    AND hm.tim_id=2
                    AND eme_id=4
                    AND hms_trimestre=?) as m
                    SET eme_id=6
                where  Uhi.msj_id=m.msj_id;');
            $this->consultar([$this->prm->epg_id,$trimestreSeleccionado]);
            $registrosAfectados = $this->getNumeroModificados();
            $this->desconectar();
            if ($registrosAfectados < 0) {
                $this->mixScript('
                    notify("Estatus de Mensaje no actualizados");
                    comunica("' . $this->pantalla . '",{
                        metodo:"Historial",
                        epg_id:"' . $this->prm->epg_id . '",
                        trimestre:' . $this->prm->trimestre . ',
                        tipo:"' . $this->prm->tipo . '"
                    });
                ');
            }
        }
        if ($this->prm->tipo == 'mensual') {
            $mesSeleccionado = $this->prm->mes;
            $this->conectar();
            $this->setSql('
                UPDATE cat_HistorialMensajeSeguimiento Uhi,(
                    SELECT msj_id
                    FROM vin_ProductoIntermedioIndicador
                    LEFT JOIN cat_MensajeSeguimiento using(pii_id)
                    LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                    INNER JOIN cat_Usuario u  on hm.usr_id = u.usr_id
                    WHERE epg_id=?
                    AND hm.tim_id=2
                    AND eme_id=4
                AND hms_mensual=?) as m
                SET eme_id=6
                where  Uhi.msj_id=m.msj_id;');
            $this->consultar([$this->prm->epg_id,$mesSeleccionado]);
            $registrosAfectados = $this->getNumeroModificados();
            $this->desconectar();
            if ($registrosAfectados < 0) {
                $this->mixScript('
                    notify("Estatus de Mensaje no actualizados");
                    comunica("' . $this->pantalla . '",{
                        metodo:"Historial",
                        epg_id:"' . $this->prm->epg_id . '",
                        mes:' . $this->prm->mes . ',
                        tipo:"' . $this->prm->tipo . '"
                    });
                ');
            }
        }
    } // Fin ActualizaVisto

} //fin clase

if (isset($_SESSION[_S]['prm']['A']['11']['10'])) {
    new ControladorObservarSeguimientoGastos();
} else {
    WebResponse::sinPermisos();
}
