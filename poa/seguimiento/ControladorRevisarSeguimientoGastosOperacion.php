<?php

/* *
* Description of ControladorRegistroModificaciones
*
* @author ?
* Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
* Modif: 22-Ene-2020
* Comment: -----
* Objective:
* Regla de Negocio: -----
* Permits: Esta pantalla tiene permisos de lectura.
* */
session_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/poa/seguimiento/consultaGastosOperacion.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/MensajeSeguimiento.php';
require_once dirname(__FILE__) . '/../../ent/poa/seguimiento/HistorialMensajeSeguimiento.php';
require_once dirname(__FILE__) . '/../../srv/ControladorMensajes.php';

class ControladorRevisarSeguimientoGastosOperacion extends WebResponse
{
    private $usr_id;
    private $nvj;
    private $adm_grupo;
    private $adm_ur;
    private $ssc;
    private $dir;
    private $uni;
    private $dep;
    private $opciones;

    public function __construct()
    {
        parent::__construct();

        //variables de session
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '';
        $this->nvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '';
        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->ssc = isset($_SESSION[_S]['ssc_id']) ? $_SESSION[_S]['ssc_id'] : '';
        $this->dir = isset($_SESSION[_S]['dir_id']) ? $_SESSION[_S]['dir_id'] : '';
        $this->uni = isset($_SESSION[_S]['uni_id']) ? $_SESSION[_S]['uni_id'] : '';
        $this->dep = isset($_SESSION[_S]['dep_id']) ? $_SESSION[_S]['dep_id'] : '';
        $this->opciones = 'Seleccione una opción de la lista';

        if ($this->nvj > 1) {
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
                case 'Historial':
                    $this->Historial();
                    break;
                case 'verResponder':
                    $this->verResponder();
                    break;
                case 'enviarRespuesta':
                    $this->enviarRespuesta();
                    break;
                case 'main':
                    $this->main();
                    break;
            }
        } else {
            $this->sinPermisos();
        }
        $this->returnJson();
    }

    //Método principal
    private function main()
    {
        //Selecciona el tipo de corte
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                    comunica("' . $this->pantalla . '", {
                        metodo:"seleccionaCorte"
                    });
                ')
        );
        $this->mixScript('$("#combour").change();');
    } //Fin main

    // Metodo que se usa para seleccionar el tipo de corte que se consultara.
    private function seleccionaCorte()
    {
        //Combo de selección de tipo de corte
        $comboPeriodo = new stdClass();
        $comboPeriodo->id = 'comboPeriodo';
        $comboPeriodo->name = 'comboPeriodo';
        $comboPeriodo->enDiv = true;
        $comboPeriodo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboPeriodo->label = 'Periodo';
        $comboPeriodo->opciones = ['' => $this->opciones, 1 => 'Mensual', 2 => 'Trimestral'];
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

    private function seleccionaperiodo()
    {
        //botón que regresa a la pantalla anterior
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'btn btn-outline-secondary me-2';
        $btnregresarindex->icon = 'circle-left me-1';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main",
            });
        ';

        $btnExportaPDF = new stdClass();
        $btnExportaPDF->id = 'btnExportaPDF';
        $btnExportaPDF->icon = 'fa fa-file-pdf me-1';
        $btnExportaPDF->class = 'btn btn-outline-secondary pdf';
        $btnExportaPDF->value = 'Pdf';
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
            $comboTipoPeriodo->opciones = ['' => $this->opciones, 1 => 'Trimestre', 2 => 'Trimestre', 3 => 'Trimestre', 4 => 'Trimestre'];
            $comboTipoPeriodo->width = '400px';
            $comboTipoPeriodo->selecciona = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 0;
            //Llamado al Script que carga la información del combdo trimestral
            $comboTipoPeriodo->change = '
                if( $(this).val() != "" ){
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividades",
                        trimestre:$("#comboTrimestre").val(),
                        gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                    });
                }
            ';

            $this->mixScript('$("#clasAdminSeccionCombos").hide();');
            //Nuevo objeto del tipo de unidad seleccionada
            $unidadR = new UnidadResponsable();
            $unidadR->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $unidadR->setUr_id($_SESSION[_S]['clasAdm-ur']);
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
                        gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                    });
                });
            ');
        } //Fin if opc_id

        //SI LA OPCION ES 1, MOSTRARA LAS OPCIONES MENSUALES
        if ($this->prm->opc_id == 1) {
            $comboTipoPeriodo = new stdClass();
            $comboTipoPeriodo->id = 'comboMes';
            $comboTipoPeriodo->label = 'Mes';
            $comboTipoPeriodo->mensaje = 'SELECCIONE EL MES A CONSULTAR';
            $comboTipoPeriodo->opciones = ['' => $this->opciones, 1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
            $comboTipoPeriodo->width = '400px';
            $comboTipoPeriodo->selecciona = (isset($this->prm->mes) && $this->prm->mes != '') ? $this->prm->mes : 0;
            //Llamado al Script que carga la información del combdo trimestral
            $comboTipoPeriodo->change = '
                if ($(this).val() != "" ) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"verActividadesMens",
                        mes:$("#comboMes").val(),
                        gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
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
                        gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                        ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                    });
                });
            ');
        } //Fin opc_id
    } //Fin seleccionaperiodo

    private function verActividades()
    {
        $titulo = '';
        $this->setContenedor('actividades');

        if (isset($this->prm->pepg_id) && $this->prm->pepg_id != '') {
            ClasificacionAdministrativa::ponEnVariablesEpg($this->prm->pepg_id);
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

        if (($this->ssc == 3 && $this->dir == 7 && $this->uni == 17)
            && ($this->dep == 44 || $this->dep == 45 || $this->dep == 46)
        ) {
            $filtro = ' and toa_id=1';
        } else {
            $filtro = ' ';
        }
        $estado = ' hms.eme_id >1 ';
        $result = consultaGastosOperacion('', $this->prm->trimestre, $this->prm->gpo_id, $this->prm->ur_id, $filtro, $estado);

        $htm = '
            <legend style="text-align:center">' . $titulo . '</legend><br>
            <table  class="table table table-bordered table-hover">
        ';

        $cvefin = '';
        $prg = '';
        $spr = '';
        $py = $estilo_btn_2 = '';
        $cambioClase = true;

        if (!empty($result)) {
            $this->mixScript('$(".pdf").show();');
        }

        while ($row = array_pop($result)) {

            /*SEMAFORIZACIÓN DE PRESUPUESTO SEGUN EL TRIMESTRE*/
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
                    </tr>
                ';
                $cvefin = $row['cfi_clave'];
            }
            if ($row['prg_id'] != $prg) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">PR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['prg_nombre'] . '</th>
                    </tr>
                ';
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">SPR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                        <td align="left" colspan="15">' . $row['spr_nombre'] . '</td>
                        <th class="success" style="text-align:center" align="left" colspan="1">SEMAFORIZACIÓN</th>
                    </tr>
                ';
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
                        <th class="success" align="center" colspan="1">MENSAJES</th>
                    </tr>
                ';
            }
            $clase = ($row['eme_id'] != '' ? 'class="bg-conobservacion"' : 'class="bg-sinobservacion"');
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
                    <td ' . $clase . ' align="center" colspan="1" >' . ($row['eme_id'] != '' ? "<b style='color: #FF1E2D;'>CON OBSERVACIÓN</b>" : "<b style='color: #00950A;'>SIN OBSERVACIÓN</b>") . '</td>
                    <td ' . $clase . ' align="center" colspan="1" >
                        ' . ($row['eme_id'] != '' ? '
                            <button type="button" id="verHistorial" title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                                <i class="fa fa-list-alt"></i>
                            </button>
                        ' : '') . '
                    </td>
                </tr>
                <tr class="default">
                    <th></th>
                    <th class ="divMetas" id="metas' . $row['epg_id'] . '" colspan = "20" > </th>
                </tr>
            ';
            $cambioClase = ($cambioClase) ? false : true;
            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
            $py = $row['py_id'];
        } //Fin while

        $htm .= '</table>';

        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($this->adm_grupo);
        $unidadR->setUr_id($this->adm_ur);
        $unidadR->select();
        $unidadResp = ($unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre());

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verActividad").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    epg_id:$(this).attr("epg_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . ',
                    trimestre:' . $this->prm->trimestre . '
                });
            });

            $(".verHistorial").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
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
                    .append($("<input>").attr("type", "hidden").attr("name", "filtro").attr("value", "' . $filtro . '"))
                $("body").append(form);
                form.submit();
            });
        ');
    } //Fin verActividades

    //Función que pinta el desglose del mes seleccionado
    private function verActividadesMens()
    {
        $unidadR = new UnidadResponsable();
        $unidadR->setGpo_id($this->adm_grupo);
        $unidadR->setUr_id($this->adm_ur);
        $unidadR->select();
        $unidadResp = ($unidadR->getGpo_id() . $unidadR->getUr_id() . ' - ' . $unidadR->getNombre());

        $this->setContenedor('actividades');
        $nombreMes = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];

        $titulo = $nombreMes[$this->prm->mes];
        $filtro = ' and toa_id=1';
        $estado = ' hms.eme_id NOT IN (1,3) ';

        $result = consultaGastosOperacion($this->prm->mes, '', $this->prm->gpo_id, $this->prm->ur_id, $filtro, $estado);
        $htm = '
            <legend style="text-align:center">' . $titulo . '</legend><br>
            <table  class="table table table-bordered table-hover">
        ';

        $cvefin = '';
        $prg = '';
        $spr = '';
        $py = $estilo_btn_2 =  '';
        $cambioClase = true;

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
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">CVFIN:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['cfi_clave'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['cfi_nombre'] . '</th>
                    </tr>
                ';
                $cvefin = $row['cfi_clave'];
            }
            if ($row['prg_id'] != $prg) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">PR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['prg_id'] . '</th>
                        <th style="text-align:left" colspan="10">' . $row['prg_nombre'] . '</th>
                    </tr>
                ';
            }

            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $htm .= '
                    <tr>
                        <th class="success" style="text-align:center" colspan="1">SPR:</th>
                        <th class="success" style="text-align:center" colspan="1">' . $row['spr_id'] . '</th>
                        <td align="left" colspan="15">' . $row['spr_nombre'] . '</td>
                        <th style="background-color: #5ED800; text-align:center; color:#000;" align="left" colspan="1">SEMAFORIZACIÓN</th>
                        <th style="background-color: #1C61EC; text-align:center; color:#000;" align="left" colspan="2">OBSERVACIONES</th>
                    </tr>
                ';
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
                        <th class="success" align="center" colspan="1">MENSAJES</th>
                    </tr>
                ';
            }
            $clase = ($row['eme_id'] != '' ? 'class="bg-conobservacion"' : 'class="bg-sinobservacion"');
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
                    <td ' . $clase . ' align="center" colspan="1" >' . ($row['eme_id'] != '' ? "<b style='color: #FF1E2D;'>CON OBSERVACIÓN</b>" : "<b style='color: #00950A;'>SIN OBSERVACIÓN</b>") . '</td>
                    <td ' . $clase . ' align="center" colspan="1" >
                        ' . ($row['eme_id'] != '' ? '
                            <button type="button" id="verHistorial" title="Opción para ver Historial de Mensajes" class="verHistorial btn btn-outline-secondary"  epg_id=' . $row['epg_id'] . '>
                                <i class="fa fa-list-alt"></i>
                            </button>
                        ' : '') . '

                    </td>
                </tr>
                <tr class="default">
                    <th></th>
                    <th class ="divMetas" id="metas' . $row['epg_id'] . '" colspan = "20" >
                    </th>
                </tr>
            ';
            $cambioClase = ($cambioClase) ? false : true;
            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
            $py = $row['py_id'];
        } // Fin while

        $htm .= '</table>';

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verActividad").click(function() {
                $(".divMetas").html("");

                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    epg_id:$(this).attr("epg_id"),
                    gpo_id:' . $this->prm->gpo_id . ',
                    ur_id:' . $this->prm->ur_id . ',
                    mes:' . $this->prm->mes . '
                });
            });

            $(".verHistorial").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"Historial",
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
    } //Fin verActividadesMens

    private function Historial()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $fechas = $titulo = '';

        //Condición cuando se requiere lo trimestral
        if ($this->prm->tipo == 'trimestral') {
            $trimestreSeleccionado = $this->prm->trimestre;
            switch ($trimestreSeleccionado) {
                case 1:
                    $fechas = ' AND hm.hms_trimestre=1';
                    $titulo = ' PRIMER TRIMESTRE';
                    break;
                case 2:
                    $fechas = ' AND hm.hms_trimestre=2';
                    $titulo = ' SEGUNDO TRIMESTRE';
                    break;
                case 3:
                    $fechas = ' AND hm.hms_trimestre=3';
                    $titulo = ' TERCER TRIMESTRE';
                    break;
                case 4:
                    $fechas = ' AND hm.hms_trimestre=4';
                    $titulo = ' CUARTO TRIMESTRE';
                    break;
            }

            $this->ActualizaVisto();
            $this->conectar();
            $this->setSql('
                SELECT hms_mensaje, usr_nombre, DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha, TIME(hm.usr_fecha) hora,
                    if(ssc_id =3 and dir_id=7 and uni_id=17 and (dep_id=44 or dep_id=45 or dep_id=46), "PRESUPUESTARIO", "DE METAS") tipoSeguimiento,
                    eme_nombre,eme_id, msj_id, eme_id, hms_mensajeRespuesta, hms_FechaRespuesta
                FROM vin_ProductoIntermedioIndicador 
                LEFT JOIN cat_MensajeSeguimiento using(pii_id)
                LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                LEFT JOIN cat_EstadoMensajeSeguimiento eme using(eme_id)
                INNER JOIN ctrlUsuario.cat_Usuario u  on hm.usr_id = u.usr_id
                INNER JOIN cat_Usuario us on u.usr_id = us.usr_id
                INNER JOIN enc_JerarquiaSefin using(ejs_id)
                WHERE epg_id= ? ' . $fechas . '
                AND hm.tim_id=2 and hms_trimestre <> 0
                AND (eme_id=2 OR eme_id=4 OR eme_id=5 OR eme_id=6 );
            ');
            $this->consultar([$this->prm->epg_id]);
            $this->desconectar();

            $html = '
                <table class="table table-hover table-bordered" >
                    <tr>
                        <th class="bg-conobservacion" style="text-align:center" align="left" colspan="3">SEFIN</th>
                        <th class="bg-conobservacion" style="text-align:center" align="left" colspan="4">RESPUESTA</th>
                    </tr>
                    <tr >
                        <th align="center">MENSAJE</th>
                        <th width="140px">USUARIO</th>
                        <th width="140px">FECHA</th>
                        <th>MENSAJE</th>
                        <th width="140px">FECHA</th>
                        <th width="95px">ESTADO</th>
                        <th width="90px">RESPONDER</th>
                    </tr>
                    <tr> 
            ';

            while ($row = $this->extraer()) {

                if ($row['eme_id'] == 2) {
                    $disabled = '';
                } elseif ($row['eme_id'] == 5) {
                    $disabled = '';
                } elseif ($row['eme_id'] == 4) {
                    $disabled = 'disabled';
                } elseif ($row['eme_id'] == 6) {
                    $disabled = 'disabled';
                } else {
                    $disabled = '';
                }
                if ($this->nvj > 2) {
                    $disabled = '';
                }

                $html .= '
                    <td>' . $row['hms_mensaje'] . '</td>
                    <td>' . $row['usr_nombre'] . '</td>
                    <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                    <td>' . $row['hms_mensajeRespuesta'] . '</td>
                    <td>' . $row['hms_FechaRespuesta'] . '</td>
                    <td>' . $row['eme_nombre'] . '</td>
                    <td  align="center" colspan="1" >
                        ' . ($row['hms_mensaje'] != '' ? '
                            <button type="button" id="solventacion" title="Opción para responder mensaje" class="solventacion btn btn-outline-secondary" msj_id=' . $row['msj_id'] . ' ' . $disabled . '>
                                <i class="fa fa-list-alt"></i>
                            </button>
                        ' : '') . '
                    </td>

                    </tr>
                    <tr class="warning">
                        <th class ="divResponder" id="responder' . $row['msj_id'] . '" colspan = "7" >
                        </th>
                    </tr>
                ';
            } // Fin while
            $html .= '<table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="false">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">REVISAR SEGUIMIENTO A GASTOS DE OPERACIÓN - ' . $titulo . '.-HISTORIAL</h4>
                            <button  id="close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                $(".solventacion").click(function(){
                    $(".divResponder").html("");
                    comunica("' . $this->pantalla . '",{
                        metodo:"verResponder",
                        msj_id:$(this).attr("msj_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        trimestre:' . $this->prm->trimestre . ',
                        tipo:"' . $this->prm->tipo . '"
                    });
                });
            ');
        } // fin if trimestral

        if ($this->prm->tipo == 'mensual') {
            $mesSeleccionado = $this->prm->mes;
            $nombreMes = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
            //Llamado a la función, para actualizar el estatus solo si es 5 a 6
            $this->ActualizaVisto();
            $this->conectar();
            $this->setSql('
                SELECT hms_mensaje, usr_nombre, DATE_FORMAT(hm.usr_fecha,"%d/%m/%y") fecha, TIME(hm.usr_fecha) hora,
                    if(ssc_id =3 and dir_id=7 and uni_id=17 and (dep_id=44 or dep_id=45 or dep_id=46), "PRESUPUESTARIO", "DE METAS") tipoSeguimiento,
                    eme_nombre,eme_id, msj_id, eme_id, hms_mensajeRespuesta, hms_FechaRespuesta
                FROM vin_ProductoIntermedioIndicador 
                LEFT JOIN cat_MensajeSeguimiento using(pii_id)
                LEFT JOIN cat_HistorialMensajeSeguimiento hm USING(msj_id)
                LEFT JOIN cat_EstadoMensajeSeguimiento eme using(eme_id)	
                INNER JOIN ctrlUsuario.cat_Usuario u  on hm.usr_id = u.usr_id
                INNER JOIN cat_Usuario us on u.usr_id = us.usr_id
                INNER JOIN enc_JerarquiaSefin using(ejs_id)
                WHERE epg_id=? AND hm.hms_mensual =?
                AND hm.tim_id=2 and hms_mensual <> 0
                AND (eme_id=2 OR eme_id=4 OR eme_id=5 OR eme_id=6 );
            ');
            $this->consultar([$this->prm->epg_id,$mesSeleccionado]);
            $this->desconectar();

            $html = '
                <table class="table table-hover table-bordered" >
                    <tr>
                        <th class="bg-conobservacion" style="text-align:center" align="left" colspan="3">SEFIN</th>
                        <th class="bg-conobservacion" style="text-align:center" align="left" colspan="4">RESPUESTA</th>
                    </tr>
                    <tr >
                        <th align="center">MENSAJE</th>
                        <th width="140px">USUARIO</th>
                        <th width="140px">FECHA</th>
                        <th>MENSAJE</th>
                        <th width="140px">FECHA</th>
                        <th width="95px">ESTADO</th>
                        <th width="90px">RESPONDER</th>
                    </tr><tr>
            ';

            while ($row = $this->extraer()) {
                if ($row['eme_id'] == 2) {
                    $disabled = '';
                } elseif ($row['eme_id'] == 5) {
                    $disabled = '';
                } elseif ($row['eme_id'] == 4) {
                    $disabled = 'disabled';
                } elseif ($row['eme_id'] == 6) {
                    $disabled = 'disabled';
                } else {
                    $disabled = '';
                }
                if ($this->nvj > 2) {
                    $disabled = '';
                }

                $html .= '
                        <td>' . $row['hms_mensaje'] . '</td>
                        <td>' . $row['usr_nombre'] . '</td>
                        <td>' . $row['fecha'] . ' ' . $row['hora'] . '</td>
                        <td>' . $row['hms_mensajeRespuesta'] . '</td>
                        <td>' . $row['hms_FechaRespuesta'] . '</td>
                        <td>' . $row['eme_nombre'] . '</td>
                        <td  align="center" colspan="1" >
                            ' . ($row['hms_mensaje'] != '' ? '
                                <button type="button" id="solventacion" title="Opción para responder mensaje" class="solventacion btn btn-outline-secondary" msj_id=' . $row['msj_id'] . ' ' . $disabled . '>
                                    <i class="fa fa-list-alt"></i>
                                </button>
                            ' : '') . '
                        </td>
                    </tr>
                    <tr class="bg-sinobservacion">
                        <td class ="divResponder" id="responder' . $row['msj_id'] . '" colspan = "7" >
                        </td>
                    </tr>
                ';
            } // fin while
            $html .= '<table>';

            $this->mixHtml('
                <div id="myModal" class="modal fade modal-al80porciento" role="dialog" data-bs-backdrop="false">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">REVISAR SEGUIMIENTO A GASTOS DE OPERACIÓN - ' . $nombreMes[$mesSeleccionado] . ' - HISTORIAL </h4>
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

                $(".solventacion").click(function(){
                    $(".divResponder").html("");
                    comunica("' . $this->pantalla . '",{
                        metodo:"verResponder",
                        msj_id:$(this).attr("msj_id"),
                        epg_id:"' . $this->prm->epg_id . '",
                        mes:' . $this->prm->mes . ',
                        tipo:"' . $this->prm->tipo . '"
                    });
                });
            ');
        } // Fin if mensual

        //Actualiza el num de mensajes
        $this->mixScript(ControladorMensajes::consultaMensajes());
    } // Fin Historial

    private function verResponder()
    {
        $this->setContenedor('responder' . $this->prm->msj_id . '');
        $botones = $tipoenvio = '';
        $btnEnviar = new stdClass();
        $btnEnviar->id = 'btnEnviar';
        $btnEnviar->type = 'button';
        $btnEnviar->title = 'Enviar respuesta';
        $btnEnviar->class = 'btn btn-outline-primary enviar';
        $btnEnviar->icon = 'fa fa-paper-plane';
        $btnEnviar->attr = new stdClass();

        $botones = '<td>' . $this->ponBoton($btnEnviar) . '</td>';

        $this->mixHtml('
            <table class="table table-hover table-bordered" >
                <tr class="sin-observacion"><th colspan=5>RESPONDER </th></tr> 
                <tr><td colspan=3><textarea id="respuesta" name="respuesta" rows="2" cols="130" maxlength="1000" placeholder="Escribir Respuesta"></textarea></td>
                <tr>' . $botones . '</tr>
                </tr>
            </table>
        ');

        //Condición del parámetro que se enviara al método enviar Respuesta
        if ($this->prm->tipo == 'trimestral') {
            $tipoenvio = '
                trimestre:' . $this->prm->trimestre . ',
                tipo:"trimestral"
            ';
        }
        if ($this->prm->tipo == 'mensual') {
            $tipoenvio = '
                mes:' . $this->prm->mes . ',
                tipo:"mensual"
            ';
        }

        $this->mixScript('
            $("#btnEnviar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"enviarRespuesta",
                    msj_id:' . $this->prm->msj_id . ',
                    epg_id:"' . $this->prm->epg_id . '",
                    respuesta: $("#respuesta").val().toUpperCase(),
                    ' . $tipoenvio . '
                });
            });

            $("#lbl_count_char").text("Caracteres restantes: " + $("#respuesta").attr("maxlength"));
            $("#respuesta").keyup(function () {
                var max = $(this).attr("maxlength");
                var len = $(this).val().length;
                if((max - len) == 0) {
                    $("#lbl_count_char").html("Caracteres restantes: <any style=\"color:red;\">" + (max - len) + "</any>");
                } else {
                    $("#lbl_count_char").text("Caracteres restantes: " + (max - len));
                }
            });
        ');
    } //Fin verResponder

    private function enviarRespuesta()
    {
        //Update para actualizar el estatus del mensaje
        $this->conectar();
        $this->setSql('
            UPDATE cat_HistorialMensajeSeguimiento SET hms_mensajeRespuesta="' . $this->prm->respuesta . '", 
            hms_FechaRespuesta=now(), hms_Respuesta=' . $this->usr_id . ',
            eme_id=4
            WHERE msj_id=? AND (eme_id=2 OR eme_id=5 ) ;
        ');
        $this->consultar([$this->prm->msj_id]);
        $registrosAfectados = $this->getNumeroModificados();
        $this->desconectar();
        if ($registrosAfectados > 0) {
            $this->mixScript('
                $("#myModal").modal("hide");
                notify("La respuesta ha sido enviada");
                comunica("' . $this->pantalla . '",{
                    metodo:"main"
                });
            ');
        } else {
            $this->mixScript('
                $("#myModal").modal("hide");
                notify("La respuesta NO ha sido enviada");
                comunica("' . $this->pantalla . '",{
                    metodo:"main"
                });
            ');
        }
    } //Fin enviarRespuesta

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
                    AND eme_id=2 and hms_trimestre=?
                ) as m
                set eme_id=5
                where  Uhi.msj_id=m.msj_id;
            ');
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
                        tipo:"trimestral"
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
                    AND eme_id=2 and hms_mensual=?
                ) as m
                set eme_id=5
                where  Uhi.msj_id=m.msj_id;
            ');
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
                        tipo:"mensual"
                    });
                ');
            }
        }
    } //Fin ActualizaVisto
} //fin clase

if (isset($_SESSION[_S]['prm']['A']['11']['11'])) {
    new ControladorRevisarSeguimientoGastosOperacion();
} else {
    WebResponse::sinPermisos();
}
