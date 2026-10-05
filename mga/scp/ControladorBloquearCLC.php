<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorBloquearCLC extends WebResponse
{
    public $usr_ip;
    public $usr_id;
    public $nvj;
    public $mst;
    public $uni;
    public $escritura;

    public function __construct()
    {
        parent::__construct();
        $this->usr_ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->mst = isset($_SESSION[_S]['mst']) && $_SESSION[_S]['mst'] != '' ? $_SESSION[_S]['mst'] : '';
        $this->uni = $_SESSION[_S]['uni_id'];
        $this->escritura = isset($_SESSION[_S]['prm']['M']['7']['2']['2']) ? true : false;
        $this->pantalla = 'mga/scp/ControladorBloquearCLC.php';

        if (isset($_SESSION[_S]['prm']['M']['7']['2']['77'])) {
            switch ($this->nvj) {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                    switch ($this->metodo) {
                        case 'ActualizarPermisos':
                            $this->ActualizarPermisos();
                            break;
                        case 'ListadoBloqueo':
                            $this->ListadoBloqueo();
                            break;
                        case 'llenarTabla':
                            $this->llenarTabla();
                            break;
                        case 'TipoClc':
                            $this->TipoClc();
                            break;
                        case 'SubtipoClc':
                            $this->SubtipoClc();
                            break;
                        case 'DatosUr':
                            $this->DatosUr();
                            break;
                        case 'ClavesFinanciamiento':
                            $this->ClavesFinanciamiento();
                            break;
                        default:
                            $this->main();
                            break;
                    }
                    break;
                default:
                    break;
            }
        }

        $this->returnJson();
    }

    public function main()
    {
        $this->conectar();
        $this->setSql('SET @usr_ip = INET_ATON( ? ), @usr_id = ?;');
        $params = [$this->usr_ip, $this->usr_id];
        $this->consultar($params);
        $this->setSql('DELETE FROM cat_BloqueoClc WHERE blc_inicio IS NOT NULL AND blc_fin IS NOT NULL AND blc_fin < NOW();');

        if (!$this->mst) {
            $this->consultar();
        }
        $this->desconectar();

        $this->agregaTools('');
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';

        /* BOTON DONDE SE GENERAN LAS CLC'S SOLO UR */
        $botones = '
            <div style="width:100%; padding-left:35%; padding-bottom:120px;">
                <div titpp="botonBloqueo" class="boton_opcion" id="bloquear">
                    <div class="boton_opcion_titulo" >
                        <strong>Bloqueo de CLC</strong>
                    </div>
                    <div>
                        <div >
                            <strong><img  src="img/scp/clc_generacion.png"><br/></strong>
                            <strong><small> </small></strong>
                        </div>
                    </div>
                </div>

                <div titpp="botonDesbloqueo" class="boton_opcion" id="desbloquear">
                    <div class="boton_opcion_titulo">
                        <strong>Desbloqueo CLC</strong>
                    </div>
                    <div>
                        <div >
                            <strong><img  src="img/scp/clc_generacion.png"><br/></strong>
                            <strong><small> </small></strong>
                        </div>
                    </div>
                </div>

                <div titpp="Reporte" class="boton_opcion" id="consultar">
                    <div class="boton_opcion_titulo" >
                        <strong>Reporte</strong>
                    </div>
                    <div>
                        <div >
                            <strong><img  src="img/scp/clc_generacion.png"><br/></strong>
                            <strong><small> </small></strong>
                        </div>
                    </div>
                </div>
            </div>
        ';

        $this->mixHtml($botones);
        $this->mixScript('
            $("#desbloquear").click(function(){
                comunica("mga/scp/ControladorBloquearCLC.php",{
                    metodo: "ListadoBloqueo",
                    tarea: "desbloquear"
                });
                return false;
            });

            $("#bloquear").click(function(){
                comunica("mga/scp/ControladorBloquearCLC.php",{
                    metodo: "ListadoBloqueo",
                    tarea: "bloquear"
                });
                return false;
            });

            $("#consultar").click(function(){
                comunica("mga/scp/ControladorBloquearCLC.php",{
                    metodo: "ListadoBloqueo",
                    tarea: "consultar"
                });
                    return false;
            });
        ');
    }

    private function ListadoBloqueo()
    {
        $tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $html = '';
        $tmp = '';
        $inicioFecha = new stdClass();
        $inicioFecha->id = 'inicioFecha';
        $inicioFecha->name = 'inicioFecha';
        $inicioFecha->label = 'Fecha Inicio';
        $inicioFecha->class = 'datepicker';
        $inicioFecha->enDiv = true;
        $inicioFecha->required = true;
        $inicioFecha->formato = 'fecha';
        $inicioFecha->placeholder = date('Y-d-m');

        $finFecha = new stdClass();
        $finFecha->id = 'finFecha';
        $finFecha->name = 'finFecha';
        $finFecha->label = 'Fecha Final';
        $finFecha->class = 'datepicker';
        $finFecha->enDiv = true;
        $finFecha->required = true;
        $finFecha->formato = 'fecha';
        $finFecha->placeholder = date('Y-d-m');

        $script = $this->getScript();
        $wh = '';
        switch ($this->nvj) {
            case 6:
            case 7:
                $wh = 'dep_id IN (44, 45, 46, 48, 49)';
                $select = '
                    CASE dep_id
                    WHEN 44 THEN "NATIVIDAD"
                    WHEN 45 THEN "GABRIELA"
                    WHEN 46 THEN "ALBERTO"
                    WHEN 48 THEN "AMARILÍS"
                    WHEN 49 THEN "ALEJANDRINO"
                    ';
                break;
            default:
                if ($this->uni == 17) {
                    $wh = 'dep_id IN (44, 45, 46)';
                    $select = '
                        CASE dep_id
                        WHEN 44 THEN "NATIVIDAD"
                        WHEN 45 THEN "GABRIELA"
                        WHEN 46 THEN "ALBERTO"
                        ';
                } else {
                    $wh = 'dep_id IN (48, 49)';
                    $select = '
                        CASE dep_id
                        WHEN 48 THEN "AMARILÍS"
                        WHEN 49 THEN "ALEJANDRINO"
                        ';
                }
                break;
        }

        $depto = new stdClass();
        $depto->id = 'depto';
        $depto->class = 'limpiar';
        $depto->sql = 'select dep_id,' . $select . ' END AS dep_nombre FROM cat_Departamento WHERE ' . $wh . ';';
        $depto->label = 'SELECCIONE EL DEPARTAMENTO';
        $depto->enRow = 1;
        $depto->search = true;
        $depto->change = '
            if( $(this).val() != "" ){
                $("#tcl_id").removeAttr("disabled");
                comunica("' . $this->pantalla . '",{
                    metodo: "TipoClc",
                    dep_id: $("#depto").val(),
                    tarea: "' . $tarea . '"
                });
            }
        ';

        $recurso = new stdClass();
        $recurso->id = 'recurso';
        $recurso->mensaje = false;
        $recurso->class = 'limpiar';
        $recurso->search = true;
        $recurso->sql = 'SELECT "O" AS ter_id,"NINGUNO" as ter_nombre UNION ALL SELECT ter_id,ter_nombre FROM ' . BD_SINPRES . '.cat_TipoRecurso UNION ALL SELECT "N" AS ter_id,"TODOS" as ter_nombre';
        if ($tarea == 'bloquear') {
            $recurso->label = 'BLOQUEAR EL TIPO DE RECURSO';
        } else {
            $recurso->label = 'DESBLOQUEAR EL TIPO DE RECURSO';
        }
        $recurso->enRow = 1;

        $filrecurso = new stdClass();
        $filrecurso->id = 'filrecurso';
        $filrecurso->mensaje = false;
        $filrecurso->class = 'limpiar';
        $filrecurso->search = true;
        $filrecurso->label = 'MOSTRAR CLAVES DE FINANCIAMIENTO';
        $filrecurso->sql = 'SELECT "C" AS ter_id,"TODOS " as ter_nombre UNION ALL SELECT ter_id,ter_nombre FROM ' . BD_SINPRES . '.cat_TipoRecurso;';
        $filrecurso->enRow = 1;
        $filrecurso->change = '
            if( $(this).val() != "" ){
                $("#cvefin").removeAttr("disabled");
                comunica("' . $this->pantalla . '",{
                    metodo:"ClavesFinanciamiento",
                    tipoRecurso:$("#filrecurso").val()
                });
                $("#cvefin").selectpicker("refresh");
            }
        ';

        $combo = new stdClass();
        $combo->sql = "
            SELECT 'X' AS tcl_id, 'SELECCIONAR TODOS LOS REGISTROS' AS tcl_nombre
            UNION ALL
            SELECT tcl_id, tcl_nombre
            FROM enc_FiltroClc
            INNER JOIN cat_TipoClc USING(tcl_id)
            GROUP BY tcl_id, dep_id
        ";
        $combo->label = 'SELECCIONE EL TIPO DE CLC';
        $combo->id = 'tcl_id';
        $combo->name = 'tcl_id[]';
        $combo->enDiv = true;
        $combo->mensaje = false;
        $combo->detonar = false;
        $combo->multiple = 'multiple';
        $combo->comoAtr = 'tcl_nombre';
        $combo->search = true;
        $combo->require = true;
        $combo->change = '
            if( $(this).val() != "" ){
                if($("#tcl_id").val()=="X"){
                    $("#tcl_id").attr("disabled","disabled");
                }else{
                    $("#tcl_id").find("option[value=X]").attr("disabled","disabled");
                }
                $("#stc_id").removeAttr("disabled");
                comunica("' . $this->pantalla . '",{
                    metodo: "SubtipoClc",
                    tcl_id: $("#tcl_id").val(),
                    dep_id: $("#depto").val(),
                    tarea: "' . $tarea . '"
                });
                $("#tcl_id").selectpicker("refresh");
            }
        ';
        $comboClc = $this->templateCombo($combo);

        $combo = new stdClass();
        $combo->sql = "
            SELECT 'X' AS stc_id, 'SELECCIONAR TODOS LOS REGISTROS' AS stc_nombre
            UNION ALL
            SELECT CONCAT(tcl_id, '_', stc_id) AS stc_id, stc_nombre
            FROM enc_FiltroClc
            INNER JOIN cat_SubtipoClc USING(tcl_id, stc_id)
            GROUP BY tcl_id, stc_id, stc_nombre
            ";
        $combo->label = 'SELECCIONE EL SUBTIPO DE CLC';
        $combo->id = 'stc_id';
        $combo->name = 'stc_id[]';
        $combo->mensaje = false;
        $combo->enDiv = true;
        $combo->detonar = false;
        $combo->multiple = 'multiple';
        $combo->search = true;
        $combo->change = '
            if($("#stc_id").val()=="X"){
                $("#stc_id").attr("disabled","disabled");
            }else{
                $("#stc_id").find("option[value=X]").attr("disabled","disabled");
            }
            $("#stc_id").selectpicker("refresh");
        ';
        $comboSubtipos = $this->templateCombo($combo);

        /* COMBO DE LAS UNIDADES REPONSABLES */
        $combour = new stdClass();
        $combour->sql = "
            SELECT 0 AS ur_id, 'TODOS LOS REGISTROS' AS ur_nombre, '000' AS ur_clave
            UNION ALL
            SELECT CONCAT(gpo_id,ur_id) AS ur_id, ur_nombre, ur_clave
            FROM enc_FiltroClc
            INNER JOIN cat_UnidadResponsable USING(gpo_id, ur_id)
            INNER JOIN aux_UnidadResponsable USING(gpo_id, ur_id)
            GROUP BY gpo_id, ur_id, ur_nombre, ur_clave
            ";
        $combour->id = 'ur_id';
        $combour->label = 'SELECCIONE LA UNIDAD RESPONSABLE';
        $combour->name = 'ur_id[]';
        $combour->enDiv = true;
        $combour->detonar = false;
        $combour->mensaje = false;
        $combour->multiple = 'multiple';
        $combour->comoAtr = 'ur_clave';
        $combour->search = true;
        $combour->change = '
            if( $(this).val() != "" ){
                if($("#ur_id").val()=="0"){
                    $("#ur_id").attr("disabled","disabled");
                }else{
                    $("#ur_id").find("option[value=0]").attr("disabled","disabled");
                }
                $("#ur_id").selectpicker("refresh");
            }
        ';

        $cvefin = new stdClass();
        $cvefin->id = 'cvefin';
        $cvefin->class = 'limpiar';
        $cvefin->sql = '
            SELECT 0 AS ecf_clave,"NINGUNA" as cfi_nombre
            UNION ALL
            SELECT ecf_clave,cfi_nombre FROM
            enc_ClaveFinanciamiento
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
            ';
        $cvefin->label = 'AGREGAR CLAVE DE FINANCIAMIENTO ';
        $cvefin->id = 'cvefin';
        $cvefin->change = '
                if( $(this).val() != "" ){
                    if($("#cvefin").val()=="0"){
                        $("#cvefin").attr("disabled","disabled");
                    }else{
                        $("#cvefin").find("option[value=0]").attr("disabled","disabled");
                    }
                    $("#cvefin").selectpicker("refresh");
                }
            ';
        $cvefin->name = 'ecf_clave[]';
        $cvefin->enDiv = true;
        $cvefin->detonar = false;
        $cvefin->mensaje = false;
        $cvefin->multiple = 'multiple';
        $cvefin->comoAtr = 'ecf_id';
        $cvefin->search = true;
        $cvefin = $this->templateCombo($cvefin);

        $momentoContable = new stdClass();
        $momentoContable->id = 'mmc_id';
        $momentoContable->class = 'limpiar';
        $momentoContable->opciones = ['A' => 'TODOS', 'B' => 'COMPROMISOS', 'C' => 'AFECTACIONES', 'D' => "CLC'S"];
        $momentoContable->label = 'MÓDULO';
        if ($tarea == 'bloquear') {
            $momentoContable->label = 'MÓDULO A BLOQUEAR';
        } elseif ($tarea == 'desbloquear') {
            $momentoContable->label = 'MÓDULO A DESBLOQUEAR';
        }
        $momentoContable->change = '
                if( $(this).val() != "" ){
                    if($("#mmc_id").val()=="T"){
                        $("#mmc_id").attr("disabled","disabled");
                    }else{
                        $("#mmc_id").find("option[value=0]").attr("disabled","disabled");
                    }
                    $("#mmc_id").selectpicker("refresh");
                }
            ';
        $momentoContable->name = 'mmc[]';
        $momentoContable->enDiv = true;
        $momentoContable->detonar = false;
        $momentoContable->mensaje = true;
        $momentoContable->multiple = 'multiple';

        $btnMostrar = $Bloquear = $btnConsulta = '';
        if ($this->prm->tarea != 'consultar') {
            $btnConsulta = new stdClass();
            $btnConsulta->id = 'btnConsulta';
            $btnConsulta->class = 'Buscar';
            $btnConsulta->value = 'Mostrar Registros ';
            $btnConsulta->text = 'Buscar';
            $btnConsulta->color = 'verde';
            $btnConsulta->icon = 'solid fa-eye me-1';
            $btnConsulta = $this->ponBoton($btnConsulta);

            $Bloquear = new stdClass();
            $Bloquear->id = 'Bloquear';
            $Bloquear->color = 'rojo';
            if ($tarea == 'bloquear') {
                $Bloquear->value = 'Bloquear';
                $Bloquear->icon = 'solid fa-ban me-1';
            } else {
                $Bloquear->value = 'Desbloquear';
                $Bloquear->icon = 'circle-check me-1';
            }
            $Bloquear = $this->ponBoton($Bloquear);
        }

        if ($this->prm->tarea == 'consultar') {
            $btnMostrar = new stdClass();
            $btnMostrar->id = 'btnMostrar';
            $btnMostrar->class = 'Buscar';
            $btnMostrar->value = 'Mostrar Registros ';
            $btnMostrar->text = 'Buscar';
            $btnMostrar->color = 'verde';
            $btnMostrar->icon = 'solid fa-eye me-1';
            $btnMostrar = $this->ponBoton($btnMostrar);
        }

        $this->mixHtml($tmp);

        $html = '';
        $html .= '
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->templateCombo($depto) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->templateCombo($recurso) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->templateCombo($filrecurso) . '
                </fieldset>
            </div>

            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-12 col-xs-12">
                ' . $this->templateCombo($combour) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $cvefin . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponCombo($momentoContable) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $comboClc . '
                </fieldset>
            </div>

            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $comboSubtipos . '
                </fieldset>
            </div>

        ';
        if ($tarea == 'bloquear') {
            $html .= '
                <div class="row col-md-12 col-xs-12">
                    <div id="tiempo">
                        <div class="row">
                            <fieldset class="col-md-2 col-xs-2">
                                ' . $this->ponInput($inicioFecha) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                <label>Hora de Inicio</label>
                                <select   style=" width: 50% !important;" class="clean form-control" id="horaInicio" name="horaI">
            ';
            for ($i = 0; $i < 24; $i++) {
                $h1 = sprintf('%02d', $i) . ':00';
                $h2 = sprintf('%02d', $i) . ':30';
                $html .= '<option value="' . $h1 . '">' . $h1 . '</option><option value="' . $h2 . '">' . $h2 . '</option>';
            }
            $html .= '
                                </select>
                            </fieldset>
                            <fieldset class="col-md-2 col-xs-2">
                            ' . $this->ponInput($finFecha) . '
                            </fieldset>
                            <fieldset class="col-md-3 col-xs-3">
                                <label>Hora Final</label>
                                <select style=" width: 50% !important;" class="clean form-control" id="horaFin" name="horaF">
            ';
            for ($i = 0; $i < 24; $i++) {
                $h1 = sprintf('%02d', $i) . ':00';
                $h2 = sprintf('%02d', $i) . ':30';
                $html .= '<option value="' . $h1 . '">' . $h1 . '</option> <option value="' . $h2 . '">' . $h2 . '</option>';
            }
            $html .= '
                                </select>
                            </fieldset>
                        </div>
                    </div>
                </div>';
        }

        $html .= '
            <div align="right"; class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-12 col-xs-12">
                        ' . $btnConsulta . '
                        ' . $Bloquear . '
                        ' . $btnMostrar . '
                    </fieldset>
            </div>
        ';

        $html .= '
            <div align="right"; class="row col-md-12 col-xs-12">
                <fieldset class="col-md-12 col-xs-12">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <td style="color:#FF4000">
                                <input  disabled type="checkbox" style="margin-right:7px;" name="check1" id="myCheck"><strong>SELECCIONAR TODOS LOS REGISTROS</strong></input>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </div>
        ';
        $this->mixHtml($html);

        $script = $this->getScript();
        $tabla = new stdClass();

        $tabla->id = 'tbl_ListCLC';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->tarea = $tarea;
        $tabla->comunica->metodo = 'llenarTabla';
        $tabla->comunica->inicioFecha = '{$("#inicioFecha").val()}';
        $tabla->comunica->dep_id = '{$("#depto").val()}';
        $tabla->comunica->tipo = '{$("#tcl_id").val()}';
        $tabla->comunica->stc_id = '{$("#stc_id").val()}';
        $tabla->comunica->ur_id = '{$("#ur_id").val()}';
        $tabla->comunica->horaInicio = '{$("#horaInicio").val()}';
        $tabla->comunica->finFecha = '{$("#finFecha").val()}';
        $tabla->comunica->horaFin = '{$("#horaFin").val()}';
        $tabla->comunica->ur_id = '{$("#ur_id").val()}';
        $tabla->comunica->ecf_clave = '{$("#cvefin").val()}';
        $tabla->comunica->ter_id = '{$("#recurso").val()}';
        $tabla->comunica->mmc_id = '{$("#mmc_id").val()}';
        $tabla->multiseleccionable = true;

        $tabla->numFilas = 150;

        $tr = [];

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBTIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLAVE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'COMPROMISOS';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'AFECTACIONES';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLC\'S';
        array_push($tr, $th);

        if ($this->prm->tarea == 'desbloquear' || $this->prm->tarea == 'consultar') {
            $th = new stdClass();
            $th->texto = 'INICIO';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'FIN';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'ALTA';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'BAJA';
            array_push($tr, $th);
        }

        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'tcl_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'stc_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cfi_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'blc_compromiso';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'blc_afectacion';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'blc_clc';
        array_push($tabla->cuerpo, $td);
        if ($this->prm->tarea == 'desbloquear' || $this->prm->tarea == 'consultar') {
            $td = new stdClass();
            $td->campoTxt = 'blc_inicio';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'blc_fin';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'FechaAlta';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'FechaBaja';
            array_push($tabla->cuerpo, $td);
        }
        if ($this->prm->tarea == 'bloquear') {

            $td = new stdClass();
            $td->campoTxt = 'tienebloqueo';
            array_push($tabla->cuerpo, $td);
        }
        $tabla->encabezado = [$tr];
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixStyle('.tienebloqueo{display:none;}');
        $this->recargaCombo($depto);
        $this->recargaCombo($recurso);
        $this->recargaCombo($filrecurso);
        $this->recargaCombo($combour);

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->icon = 'solid fa-circle-left me-2';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->text = 'Regresar';

        $this->mixScript('
            $("#btnRegresarIndex").click (function(){
                comunica("mga/scp/ControladorBloquearCLC.php",{metodo:"main"});
                return false;
            });
        ');

        $ListadoExcel = '';
        if ($this->prm->tarea == 'consultar') {
            $ListadoExcel = new stdClass();
            $ListadoExcel->id = 'ListadoExcel';
            $ListadoExcel->color = 'verde';
            $ListadoExcel->icon = 'fa-solid fa-file-excel me-2';
            $ListadoExcel->value = 'Excel';
            $ListadoExcel->click = '
                if($("#depto").val()=="" || $("#depto").val()==null){
                    notify("Elige un Departamento", "warning");
                    return false;
                } else if ($("#ur_id").val()=="" || $("#ur_id").val()==null){
                    notify("Elige una Unidad Responsable", "warning");
                    return false;
                } else if ($("#cvefin").val()=="" || $("#cvefin").val()==null){
                    notify("Elige una Clave de Financiamiento", "warning");
                    return false;
                } else if ($("#mmc_id").val()=="" || $("#mmc_id").val()==null){
                    notify("Elige un módulo", "warning");
                    return false;
                } else if ($("#tcl_id").val()=="" || $("#tcl_id").val()==null){
                    notify("Elige un Tipo de CLC", "warning");
                    return false;
                } else if ($("#stc_id").val()=="" || $("#stc_id").val()==null){
                    notify("Elige un Subtipo de CLC", "warning");
                    return false;
                } else {
                    var form = $("<form>")
                    .attr("action", "rep/excel/mga/scp/exp_BloqueosCLC.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .attr("id", "enviaPost")
                    .append($("<input>").attr("type", "hidden").attr("name", "depto").val( $("#depto").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "stc_id").val( $("#stc_id").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( $("#ur_id").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "cvefin").val( $("#cvefin").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo").val( $("#tcl_id").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").val( $("#mmc_id").val()));
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }
            ';
            $ListadoExcel = $this->ponBoton($ListadoExcel);
        }

        $validacion = '';
        if ($tarea == 'bloquear') {
            $validacion = '
                if(!validarFechas($("#inicioFecha").val(), $("#finFecha").val(), $("#horaInicio").val(), $("#horaFin").val())){
                    notify("La fecha de termino no puede ser menor a la fecha de inicio", "error");
                    return false;
                }
                if($("#inicioFecha").val()==""){
                    notify("Elige la Fecha de inicio para el Bloqueo", "warning");
                    return false;
                }
                if($("#finFecha").val()==""){
                    notify("Elige la Fecha de Fin para el Bloqueo", "warning");
                    return false;
                }
            ';
        }
        $this->agregaTools('' . $this->ponBoton($btnRegresarIndex) . '  ' . $ListadoExcel . '');
        $this->mixScript($script . '
            $("#tiempo").hide();
            $("#Bloquear").click(function(){
                if( seleccionados = $("#tbl_ListCLC").tabla("getFilaSeleccion") ){
                    if($("#myCheck").is(":checked")){
                        if($("tr.multiseleccionable.seleccionable").length==$("tr.multiseleccionable.seleccionable.seleccionado").length){
                            $("#myCheck").attr("checked",true);
                        }else{
                            $("#myCheck").removeAttr("checked");
                        }
                    };
                    if($("#cvefin").val()=="" || $("#cvefin").val()==null){
                        notify("Elige una Clave de Financiamiento", "warning");
                        return false;
                    }
                    if($("#depto").val()=="" || $("#depto").val()==null){
                        notify("Elige un Departamento", "warning");
                        return false;
                    }
                    if($("#ur_id").val()=="" || $("#ur_id").val()==null){
                        notify("Elige una Unidad Responsable", "warning");
                        return false;
                    }
                    if($("#tcl_id").val()=="" || $("#tcl_id").val()==null){
                        notify("Elige un Tipo de CLC", "warning");
                        return false;
                    }
                    if($("#stc_id").val()=="" || $("#stc_id").val()==null){
                        notify("Elige un Subtipo de CLC", "warning");
                        return false;
                    }
                    if($("#mmc_id").val()=="" || $("#mmc_id").val()==null){
                        notify("Elige un módulo", "warning");
                        return false;
                    }
                    if($("#recurso").val()=="" || $("#recurso").val()=="O"){
                        notify("Elige un Tipo de recurso", "warning");
                        return false;
                    }
                    ' . $validacion . '

                    comunica("' . $this->pantalla . '",{
                        metodo: "ActualizarPermisos",
                        registros: $("#myCheck").is(":checked"),
                        depto: $("#depto").val(),
                        inicioFecha: $("#inicioFecha").val(),
                        horaI: $("#horaInicio").val(),
                        finFecha: $("#finFecha").val(),
                        horaF: $("#horaFin").val(),
                        tipo: $("#tcl_id").val(),
                        stc_id: $("#stc_id").val(),
                        ur_id: $("#ur_id").val(),
                        recurso: $("#recurso").val(),
                        ecf_clave: $("#cvefin").val(),
                        mmc_id: $("#mmc_id").val(),
                        tarea: "' . $tarea . '",
                        datos: seleccionados
                    });
                }
            });

            $("#myCheck").click(function(){
                $("tr.multiseleccionable.seleccionable.seleccionado td:first-child").click();
                if($("#myCheck").is(":checked")){
                    $("tr.multiseleccionable.seleccionable td:first-child").click();
                }
            });

            $("#btnConsulta").click(function(){

                if($("#depto").val()=="" || $("#depto").val()==null){
                    notify("Elige un Departamento", "warning");
                    return false;
                }
                if($("#ur_id").val()=="" || $("#ur_id").val()==null){
                    notify("Elige una Unidad Responsable", "warning");
                    return false;
                }
                if($("#cvefin").val()=="" || $("#cvefin").val()==null){
                    notify("Elige una Clave de Financiamiento", "warning");
                    return false;
                }
                if($("#mmc_id").val()=="" || $("#mmc_id").val()==null){
                    notify("Elige un módulo", "warning");
                    return false;
                }
                if($("#tcl_id").val()=="" || $("#tcl_id").val()==null){
                    notify("Elige un Tipo de CLC", "warning");
                    return false;
                }
                if($("#stc_id").val()=="" || $("#stc_id").val()==null){
                    notify("Elige un Subtipo de CLC", "warning");
                    return false;
                }

                if($("#recurso").val()=="" || $("#recurso").val()=="O"){
                    notify("Elige un Tipo de recurso", "warning");
                    return false;
                }

                ' . $this->wrLlamaLlenaTabla('tbl_ListCLC') . '
            });

            $("#btnMostrar").click(function(){
           if($("#depto").val()=="" || $("#depto").val()==null){
                    notify("Elige un Departamento", "warning");
                    return false;
                }
                if($("#ur_id").val()=="" || $("#ur_id").val()==null){
                    notify("Elige una Unidad Responsable", "warning");
                    return false;
                }
                if($("#cvefin").val()=="" || $("#cvefin").val()==null){
                    notify("Elige una Clave de Financiamiento", "warning");
                    return false;
                }
                if($("#mmc_id").val()=="" || $("#mmc_id").val()==null){
                    notify("Elige un módulo", "warning");
                    return false;
                }
                if($("#tcl_id").val()=="" || $("#tcl_id").val()==null){
                    notify("Elige un Tipo de CLC", "warning");
                    return false;
                }
                if($("#stc_id").val()=="" || $("#stc_id").val()==null){
                    notify("Elige un Subtipo de CLC", "warning");
                    return false;
                }

                if($("#recurso").val()=="" || $("#recurso").val()=="O"){
                    notify("Elige un Tipo de recurso", "warning");
                    return false;
                }
               
                    ' . $this->wrLlamaLlenaTabla('tbl_ListCLC') . '
                
            });

            function validarFechas(fechaInicio, fechaTermino, horaInicio, horaFin){
                var arregloFechaInicio = fechaInicio.split("/");
                var arregloFechaTermino = fechaTermino.split("/");

                var anioFechaInicio = arregloFechaInicio[2];
                var mesFechaInicio = arregloFechaInicio[1];
                var diaFechaInicio = arregloFechaInicio[0];

                var anioFechaTermino = arregloFechaTermino[2];
                var mesFechaTermino = arregloFechaTermino[1];
                var diaFechaTermino = arregloFechaTermino[0];

                if(anioFechaInicio < anioFechaTermino){
                    return true;
                }else if(anioFechaInicio > anioFechaTermino){
                    return false;
                }else{
                    if(mesFechaInicio < mesFechaTermino){
                        return true;
                    }else if(mesFechaInicio > mesFechaTermino){
                        return false;
                    }else{
                        if(diaFechaInicio < diaFechaTermino){
                            return true;
                        }else{
                            if(horaInicio < horaFin)
                                return true;
                            else
                                return false;
                        }
                    }
                }
            }

        ');
    }

    private function llenarTabla()
    {
        $filtro = $select = $inner = $group = ''; //consultas
        $wf = '';
        $select2 = '';
        $w2 = '';
        $w4 = '';
        $inner2 = ''; // subconsultas
        $usingbloqueo = 'efc_id';
        $selectclaveblo = '';
        $rjoinclaveblo = '';
        $havclaveblo = '';
        $wherebloefc_id = ' isnull(ecf_id) ';
        $paramswf = [];
        $paramsw2 = [];
        $paramsw4 = [];
        $arrayMerge = [];
        $paramsfiltro = [];

        $this->prm->dep_id = (isset($this->prm->dep_id)) ? $this->prm->dep_id : '';

        if ($this->prm->dep_id != '') { //El departamento es obligatorio
            $wf .= ($wf == '') ? 'WHERE dep_id = ? ' : ' AND dep_id = ? ';
            array_push($paramswf, $this->prm->dep_id);
        }

        if ($this->prm->stc_id != '' && $this->prm->stc_id[0] == 'X') { // Si eligieron todos los sibtipo de CLC
            if (is_array($this->prm->tipo) && count($this->prm->tipo) > 0) {

                if ($this->prm->tipo[0] == 'X') { // Si eligieron todos los tipos el filtro de tipo y subtipo se va en "" (vacio)
                    $filtro = '';
                } else {
                    $bind = '';
                    foreach ($this->prm->tipo as $key => $value) {
                        $bind .= '?, ';
                        array_push($paramswf, $value);
                    }
                    $bind = substr($bind, 0, -2);
                    $wf .= ($wf == '') ? ' WHERE tcl_id in ( ' . $bind . ' ) ' : ' AND tcl_id in ( ' . $bind . ' ) ';
                }
            }
        } else {
            if (is_array($this->prm->stc_id) && count($this->prm->stc_id) > 0 && $this->prm->stc_id != '') {
                foreach ($this->prm->stc_id as $dato) {
                    $d = explode('_', $dato);
                    $filtro .= '(? ,? ),';
                    array_push($paramswf, $d[0], $d[1]);
                }
                $wf .= ($wf == '') ? '  AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ' : '  AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ';
            }
        }

        if (is_array($this->prm->ur_id) && count($this->prm->ur_id) > 0 and $this->prm->ur_id != '') {
            if ($this->prm->ur_id[0] == '0') {
                $wf .= '';
            } else {
                $bind = '';
                foreach ($this->prm->ur_id as $key => $value) {
                    $bind .= '?, ';
                    array_push($paramswf, $value);
                }
                $bind = substr($bind, 0, -2);
                $wf .= ($wf == '') ? 'WHERE  CONCAT(gpo_id,ur_id) in(' . $bind . ') ' : 'AND CONCAT(gpo_id,ur_id) in (' . $bind . ') ';
            }
        }

        if (is_array($this->prm->ecf_clave) && count($this->prm->ecf_clave) > 0 and $this->prm->ecf_clave != '') {

            if ($this->prm->ecf_clave[0] == '0') { // Cuando el valor es 0 quiere decir que no se va a bloquear por una Clave de Financiamiento
                $select2 = ' ,efc_id as excepcion'; // como no se hace la multiplicacion con el encuadre de clave de financiamiento solo se multiplica por el bloque de clc
                $select = ',NULL AS ecf_id';
                $wf .= '';
            } else {
                if ($this->prm->ter_id != 'N') {
                    $this->mixScript('notify("Bloqueo incorrecto, solo puedes  bloquear por Clave de Financiamiento", "error");');
                    return false;
                }
                $select = ',enc_ClaveFinanciamiento.ecf_id,cfi_clave,cfi_nombre'; // cuando existen los bloqueos por Clave de financiamientose se hace la seleecion de los campos
                $inner = '
                    ,enc_ClaveFinanciamiento
                    INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                    ';
                $usingbloqueo = 'ecf_id';
                $selectclaveblo = ' ,b.blc_id ';
                $rjoinclaveblo = ' left join cat_BloqueoClc  	b	using (efc_id) ';
                $havclaveblo = ' OR isnull(b.blc_id) ';
                $wherebloefc_id = '';
                $select2 = ' ,ecf_id as excepcion'; // cuando se quiere agregar un nuevo registro y ya esta en la tabla de bloqueo de clc nos permite que ya no se duplique el registro
                $inner2 = 'INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)'; //para validar que no se agregue una misma clave de financiamiento
                $group = ' ,ecf_id,efc_id,cfi_clave,cfi_nombre,blc_id,blc_compromisoexe,blc_afectacionexe,blc_clcexe';

                $bind = '';
                foreach ($this->prm->ecf_clave as $key => $value) {
                    $bind .= '?, ';
                    array_push($paramswf, $value);
                    array_push($paramsw2, $value);
                }
                $bind = substr($bind, 0, -2);
                $wf .= ($wf == '') ? 'WHERE  ecf_clave  in (' . $bind . ') ' : ' AND ecf_clave in (' . $bind . ') ';
                $w2 = ' WHERE  ecf_clave  in (' . $bind . ')';
            }
        }
        //si viene el valor del momento contable
        $cmp = $afe = $clc = '""';
        $w3 = '';
        $dbloq = '';
        if (isset($this->prm->mmc_id) && is_array($this->prm->mmc_id) && count($this->prm->mmc_id) > 0) {
            $valor = '';
            switch ($this->prm->tarea) {
                case 'consultar':
                case 'desbloquear':
                    $valor = 'X';
                    break;
                case 'bloquear':
                    $dbloq = 'X';
                    break;
            }
            if ($this->prm->mmc_id != '' && $this->prm->mmc_id[0] == 'A') {
                $wf .= '';
                $cmp = $afe = $clc = 'X';
            } else {

                $arrayMmc = $this->prm->mmc_id != '' ? $this->prm->mmc_id : [];
                foreach ($arrayMmc as $key => $val) {
                    switch ($arrayMmc[$key]) {
                        case 'B':
                            $cmp = '"X"';
                            if ($valor != '') {
                                $wf .= (($wf == '') ? ' WHERE a.blc_compromiso = ?' : ' AND a.blc_compromiso = ?');
                                array_push($paramswf, $valor);
                                $w2 .= (($w2 == '') ? ' WHERE a.blc_compromiso = ?' : ' AND a.blc_compromiso = ?');
                                array_push($paramsw2, $valor);
                            }
                            if ($dbloq != '') {

                                $w3 .= (($w3 == '') ? 'or (blc_compromisoexe="" ) ' : ' OR (blc_compromisoexe="" )');
                            }

                            break;
                        case 'C':
                            $afe = '"X"';
                            if ($valor != '') {
                                array_push($paramswf, $valor);
                                $wf .= (($wf == '') ? ' WHERE a.blc_afectacion = ?' : ' AND a.blc_afectacion = ?');
                                array_push($paramsw2, $valor);
                                $w2 .= (($w2 == '') ? ' WHERE a.blc_afectacion = ?' : ' AND a.blc_afectacion = ?');
                            }
                            if ($dbloq != '') {

                                $w3 .= (($w3 == '') ? 'or (blc_afectacionexe="" ) ' : ' OR  (blc_afectacionexe="" ) ');
                            }
                            break;
                        case 'D':

                            $clc = '"X"';

                            if ($valor != '') {
                                array_push($paramswf, $valor);
                                $wf .= (($wf == '') ? ' WHERE a.blc_clc = ?' : ' AND a.blc_clc = ?');
                                array_push($paramsw2, $valor);
                                $w2 .= (($w2 == '') ? ' WHERE a.blc_clc = ?' : ' AND a.blc_clc = ?');
                            }
                            if ($dbloq != '') {

                                $w3 .= (($w3 == '') ? 'or (blc_clcexe="" ) ' : ' OR  (blc_clcexe="" )');
                            }
                            break;
                    }
                }
            }
        }

        if ($wherebloefc_id != '') {
            $w2 .= (($w2 == '') ? ' WHERE ' . $wherebloefc_id . ' ' : ' AND ' . $wherebloefc_id . ' ');
        }
        if ($this->prm->ter_id != 'N' || '') {
            $w2 .= (($w2 == '') ? ' WHERE ter_id = ? ' : ' AND ter_id = ? ');
            array_push($paramsw2, $this->prm->ter_id);
            $w4 .= 'and ter_id = ?';
            array_push($paramsw4, $this->prm->ter_id);
        }
        $this->conectar();
        switch ($this->prm->tarea) {
            case 'bloquear':

                $sql = '
                    SELECT enc.efc_id,gpo_id,ur_id,ur_clave,ur_nombre,dep_id,tcl_id,
                    tcl_nombre,stc_id,stc_nombre,excepcion,
                    "' . $cmp . '" blc_compromiso, "' . $afe . '" blc_afectacion, "' . $clc . '" blc_clc,blc_compromisoexe,blc_afectacionexe,
                    blc_clcexe,IF(ISNULL(excepcion)' . $havclaveblo . ',"N","S") tienebloqueo
                    ' . $select . ' ' . $selectclaveblo . '
                    FROM enc_FiltroClc enc
                    INNER JOIN cat_TipoClc using(tcl_id)
                    INNER JOIN cat_SubtipoClc using(tcl_id,stc_id)
                    INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
                    ' . $rjoinclaveblo . '
                    ' . $inner . '
                    LEFT  JOIN (
                    SELECT efc_id, blc_compromiso blc_compromisoexe,blc_afectacion blc_afectacionexe,blc_clc blc_clcexe ' . $select2 . ',ecf_id
                    FROM cat_BloqueoClc
                    ' . $inner2 . '
                    ' . $w2 . '
                    )ex using(' . $usingbloqueo . ')

                    ' . $wf . '
                    GROUP BY gpo_id,ur_id,tcl_id,stc_id,ex.blc_compromisoexe,ex.blc_afectacionexe,ex.blc_clcexe,enc.efc_id' . $group . '
                    HAVING isnull(excepcion)' . $w3 . ';';
                $arrayMerge = array_merge($paramsw2, $paramswf);
                break;
            case 'desbloquear':
                $sql = '
                    SELECT efc_id,blc_id,gpo_id,ur_id,ur_clave,ur_nombre,tcl_id,tcl_nombre,stc_id,stc_nombre,
                    dep_id,blc_inicio,blc_fin,ter_id,ecf_id,cfi_clave,cfi_nombre,"' . $cmp . '" blc_compromiso,"' . $afe . '" blc_afectacion,"' . $clc . '" blc_clc
                    FROM  cat_BloqueoClc a
                    LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                    INNER JOIN enc_FiltroClc USING(efc_id)
                    INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
                    INNER JOIN cat_TipoClc using(tcl_id)
                    INNER JOIN cat_SubtipoClc using(tcl_id,stc_id)
                    ' . $wf . ' ' . $w4 . '
                    GROUP BY blc_id,cfi_clave,cfi_nombre;';
                $arrayMerge = array_merge($paramswf, $paramsw4);
                break;
            case 'consultar':
                $sql = '
                    SELECT gpo_id,ur_id,ur_nombre,ur_clave,tcl_id,tcl_nombre,stc_id,stc_nombre,dep_id,blc_inicio,blc_fin,ter_id,a.ecf_id,ecf_clave,cfi_clave,cfi_nombre,
                    a.blc_compromiso, a.blc_afectacion, a.blc_clc,
                    max( if(bit_observacion="SE INSERTA EL BLOQUEO", DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S"),"") )as FechaAlta,
                    max(if(bit_observacion="SE ELIMINA EL BLOQUEO",DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S"),"") )as FechaBaja
                    FROM cat_BloqueoClc a
                    INNER JOIN enc_FiltroClc using (efc_id)
                    INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
                    INNER JOIN cat_TipoClc using(tcl_id)
                    INNER JOIN cat_SubtipoClc using(tcl_id,stc_id)
                    LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                    LEFT JOIN ' . BD_BITACORA . '.bit_BloqueoClc b using(efc_id,blc_id,blc_inicio,blc_fin,ter_id)
                    ' . $wf . ' ' . $w4 . '
                    GROUP BY a.ecf_id,blc_id,cfi_clave,cfi_nombre
                    ORDER BY gpo_id,ur_id,tcl_id,stc_id,a.ecf_id,dep_id;';
                $arrayMerge = array_merge($paramswf, $paramsw4);
                break;

        }

        $this->setSql($sql);
        $this->setParams($arrayMerge);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();

        $nav->idTabla = 'tbl_ListCLC';
        $nav->nfDefault = 150;

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros", "warning");
                $("#tiempo").hide();
                ');
        } else {
            $this->mixScript('
                $("#tiempo").show();
                $("#myCheck").removeAttr("disabled");
            ');
        }
    }

    public function ActualizarPermisos()
    {
        $select = $inner = $group = ''; //consultas
        $select2 = $w2 = $inner2 = ''; // subconsultas
        $wh = $wf = $periodo = '';
        $selectclaveblo = '';
        $rjoinclaveblo = '';
        $havclaveblo = '';
        $usingbloqueo = 'efc_id';
        $elimbloqueo = 0;
        $seg = ':00';
        $seg = ':00';
        $ip = $this->usr_ip;
        $this->prm->registros = isset($this->prm->registros) && $this->prm->registros == 1 ? $this->prm->registros : 0;
        $this->prm->tarea = isset($this->prm->tarea) ? $this->prm->tarea : '';
        $this->prm->recurso = isset($this->prm->recurso) ? $this->prm->recurso : 'C';
        $this->prm->depto = isset($this->prm->depto) ? $this->prm->depto : '';
        $inicioFecha = isset($this->prm->inicioFecha) ? $this->prm->inicioFecha : '';
        $horaInicio = isset($this->prm->horaI) ? $this->prm->horaI . $seg : '00:00:00';
        $finFecha = isset($this->prm->finFecha) ? $this->prm->finFecha : '';
        $horaFin = isset($this->prm->horaF) ? $this->prm->horaF . $seg : '00:00:00';
        $user = $this->usr_id;
        $filtro = '';
        $cmp = $afe = $clc = '" "';

        if ($inicioFecha != '' && $finFecha != '' && $this->prm->tarea == 'bloquear') {
            $inicioFecha = $this->date_to_mysqldate($inicioFecha);
            $finFecha = $this->date_to_mysqldate($finFecha);
            $periodo = 'efc_inicio = "' . $inicioFecha . ' ' . $horaInicio . '",efc_fin = "' . $finFecha . ' ' . $horaFin . '",efc_tipoRecurso="' . $this->prm->recurso . '"  ';
            $wh = ' AND efc_inicio is NULL and efc_fin is NULL';
        } else {
            $periodo = 'efc_inicio =NULL,efc_fin =NULL, efc_tipoRecurso="' . $this->prm->recurso . '" ';
            $wh = ' AND efc_inicio is NOT NULL and efc_fin is NOT NULL';
        }

        if ($this->prm->depto != '') {
            $wf .= ($wf == '') ? 'WHERE dep_id=' . $this->prm->depto . ' ' : ' AND dep_id=' . $this->prm->depto . ' ';
        }

        if (isset($this->prm->mmc_id) && is_array($this->prm->mmc_id) && count($this->prm->mmc_id)) {
            $valor = ' IS NULL';
            switch ($this->prm->tarea) {
                case 'desbloquear':
                    $valor = '="X"';
                    break;
                case 'bloquear':
                case 'consultar':
                    break;
            }
            //todos
            if ($this->prm->mmc_id[0] == 'A') {
                $wf .= '';
                $cmp = $afe = $clc = '"X" ';
                $elimbloqueo = 1;
            } else {
                $arrayMmc = $this->prm->mmc_id;
                foreach ($arrayMmc as $key => $val) {
                    switch ($arrayMmc[$key]) {
                        case 'B':
                            $cmp = 'X';

                            $wf .= (($wf == '') ? ' WHERE blc_compromiso' . $valor . ' ' : ' AND blc_compromiso' . $valor . ' ');
                            break;
                        case 'C':
                            $afe = 'X';
                            $wf .= (($wf == '') ? ' WHERE blc_afectacion' . $valor . ' ' : ' AND blc_afectacion' . $valor . '');

                            break;
                        case 'D':
                            $clc = 'X';
                            $wf .= (($wf == '') ? ' WHERE blc_clc' . $valor . ' ' : ' AND blc_clc' . $valor . ' ');

                            break;
                    }
                }
                if ($this->prm->tarea == 'bloquear') {
                    $wf = '';
                }
            }
        }

        if ($this->prm->registros == 1) {
            $whereupdateblo = '';
            if ($this->prm->stc_id[0] == 'X' && $this->prm->stc_id != '') {

                if (is_array($this->prm->tipo) && count($this->prm->tipo) > 0) {

                    if ($this->prm->tipo[0] == 'X' && $this->prm->tipo != '') {
                        $filtro = '';
                        $whereupdateblo = '';
                    } else {
                        $wf .= ($wf == '') ? ' WHERE tcl_id in("' . implode('","', $this->prm->tipo) . '") ' : ' AND tcl_id in("' . implode('","', $this->prm->tipo) . '") ';
                        $whereupdateblo .= ($whereupdateblo == '') ? ' WHERE tcl_id in("' . implode('","', $this->prm->tipo) . '") ' : ' AND tcl_id in("' . implode('","', $this->prm->tipo) . '") ';
                    }
                }
            } else {

                if (is_array($this->prm->stc_id) && count($this->prm->stc_id) > 0 && $this->prm->stc_id != '') {
                    foreach ($this->prm->stc_id as $dato) {
                        $d = explode('_', $dato);
                        $filtro .= '("' . $d[0] . '",' . $d[1] . '),';
                    }

                    $wf .= ($wf == '') ? '  WHERE (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ' : '  AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ';
                    $whereupdateblo .= ($whereupdateblo == '') ? '  WHERE (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ' : '  AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ';
                }
            }

            if (count($this->prm->ur_id) > 0 and $this->prm->ur_id != '') {
                if ($this->prm->ur_id[0] == '0') {
                    $wf .= '';
                    $whereupdateblo .= '';
                } else {
                    $wf .= ($wf == '') ? 'WHERE  CONCAT(gpo_id,ur_id) in("' . implode('","', $this->prm->ur_id) . '") ' : 'AND CONCAT(gpo_id,ur_id) in("' . implode('","', $this->prm->ur_id) . '") ';
                    $whereupdateblo .= ($whereupdateblo == '') ? 'WHERE  CONCAT(gpo_id,ur_id) in("' . implode('","', $this->prm->ur_id) . '") ' : 'AND CONCAT(gpo_id,ur_id) in("' . implode('","', $this->prm->ur_id) . '") ';
                }
            }

            if (is_array($this->prm->ecf_clave) && count($this->prm->ecf_clave) > 0 and $this->prm->ecf_clave != '') {
                if ($this->prm->ecf_clave[0] == '0') {
                    $select2 = ' ,efc_id as excepcion'; //como no se hace la multiplicacion con el encuadre de clave de financiamiento solo se multiplica por el bloque de clc
                    $select = ' ,NULL AS ecf_id'; // cuando existen los bloqueos por Clave de financiamientose se hace la seleecion de los campos
                    $wf .= '';
                    $whereupdateblo .= ($whereupdateblo == '') ? 'WHERE IFNULL(ecf_id,0)=0' : ' AND IFNULL(ecf_id,0)=0 ';
                } else {
                    $select = ' ,ecf_id'; // cuando existen los bloqueos por Clave de financiamientose se hace la seleecion de los campos
                    $inner = '
                        ,enc_ClaveFinanciamiento
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                        ';
                    $selectclaveblo = ' ,b.blc_id ';
                    $rjoinclaveblo = ' LEFT JOIN( select blc_id,efc_id from cat_BloqueoClc ) b USING (efc_id)';
                    $usingbloqueo = 'ecf_id';
                    $select2 = ' ,ecf_id as excepcion'; // cuando se quiere agregar un nuevo registro y ya esta en la tabla de bloqueo de clc nos permite que ya no se duplique el registro
                    $inner2 = 'INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)'; //para validar que no se agregue una misma clave de financiamiento
                    $group = ' ,ecf_id,efc_id';
                    $havclaveblo = ' OR ISNULL(b.blc_id) ';
                    $wf .= ($wf == '') ? 'WHERE  ecf_clave  in("' . implode('","', $this->prm->ecf_clave) . '") ' : ' AND ecf_clave in("' . implode('","', $this->prm->ecf_clave) . '") ';
                    $w2 = ' WHERE  ecf_clave  in("' . implode('","', $this->prm->ecf_clave) . '")';
                    $whereupdateblo .= ($whereupdateblo == '') ? 'WHERE  ecf_clave  in("' . implode('","', $this->prm->ecf_clave) . '") ' : ' AND ecf_clave in("' . implode('","', $this->prm->ecf_clave) . '") ';
                }
            }

            if ($this->prm->tarea == 'bloquear') {
                $this->conectar();
                $this->setSql('SET @usr_ip=INET_ATON("' . $ip . '"), @usr_id = ' . $this->usr_id . ', @bit_observacion = "Bloqueo de CLC desde la pantalla M 7 2";');
                $this->consultar();

                $actubloqueo = '';
                foreach ($this->prm->mmc_id as $key => $val) {
                    if ($val == 'B') { //actualiza compromisos
                        $actubloqueo .= (($actubloqueo == '') ? '  blc_compromiso="X" ' : ', blc_compromiso="X" ');
                    }
                    if ($val == 'C') { //actualiza afectaciones
                        $actubloqueo .= (($actubloqueo == '') ? '  blc_afectacion="X" ' : ', blc_afectacion="X" ');
                    }
                    if ($val == 'D') { //actualiza clc
                        $actubloqueo .= (($actubloqueo == '') ? '  blc_clc="X" ' : ', blc_clc="X" ');
                    }
                }

                if ($actubloqueo != '') {
                    $this->setSql('
                        UPDATE cat_BloqueoClc
                        LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                        LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                        INNER JOIN enc_FiltroClc USING(efc_id)
                        SET ' . $actubloqueo . ' ' . $whereupdateblo . ';
                        ');

                    $this->consultar();
                }
                $this->setSql('
                    INSERT INTO  ' . BD_CTRL . '.cat_BloqueoClc (efc_id , blc_inicio , blc_fin , ter_id , ecf_id ,blc_compromiso ,blc_afectacion ,blc_clc , usr_id , usr_fecha )
                    SELECT efc_id,"' . $inicioFecha . ' ' . $horaInicio . '"  AS blc_inicio, "' . $finFecha . ' ' . $horaFin . '" AS blc_fin, "' . $this->prm->recurso . '" ter_id,ecf_id,' . $cmp . ',' . $afe . ',' . $clc . ', ' . $user . ' AS usr_id, NOW() AS usr_fecha
                    FROM(
                    SELECT enc.efc_id,excepcion ' . $select . ' ' . $selectclaveblo . '
                    FROM enc_FiltroClc enc
                    INNER JOIN cat_TipoClc using(tcl_id)
                    INNER JOIN cat_SubtipoClc using(tcl_id,stc_id)
                    INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                    ' . $rjoinclaveblo . '
                    ' . $inner . '
                    LEFT  JOIN (
                    SELECT efc_id ' . $select2 . ',blc_compromiso,blc_clc,blc_afectacion  ,ecf_id, blc_id
                    FROM cat_BloqueoClc
                    ' . $inner2 . '
                    ' . $w2 . '
                    )ex using(' . $usingbloqueo . ')

                    ' . $wf . '
                    group by gpo_id,ur_id,tcl_id,stc_id,blc_id  ' . $group . '
                    HAVING isnull(excepcion) ' . $havclaveblo . '
                    )a;
                    ');
                $this->consultar();

                $this->desconectar();
                $this->mixScript(' notify( "Los Bloqueos  se han ingresado correctamente", "success");');
            }

            if ($this->prm->tarea == 'desbloquear') {

                $this->conectar();
                $this->setSql('SET @usr_ip=INET_ATON("' . $ip . '"), @usr_id = ' . $this->usr_id . ', @bit_observacion = "Desbloqueo de CLC desde la pantalla M 7 2";');
                $this->consultar();

                //Consultamos si los filtros dados corresponden especificamente
                $this->setSql('
                    SELECT * FROM  cat_BloqueoClc
                    LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                    INNER JOIN enc_FiltroClc USING(efc_id)
                    ' . $wf . '
                    ' . ($cmp != '" "' ? ' AND blc_compromiso = "x" ' : ' AND blc_compromiso = "" ') . '
                    ' . ($afe != '" "' ? ' AND blc_afectacion = "x" ' : ' AND blc_afectacion = "" ') . '
                    ' . ($clc != '" "' ? ' AND blc_clc = "x" ' : ' AND blc_clc = "" ') . '
                    ');

                $this->consultar();

                if ($this->getNumeroRegistros() == 0 && $elimbloqueo != 1) {
                    $this->setSql('
                        UPDATE cat_BloqueoClc
                        LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                        LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                        INNER JOIN enc_FiltroClc USING(efc_id)
                        SET ' . ($cmp != '" "' ? ' blc_compromiso = "", ' : ' blc_compromiso = "X", ') . '
                        ' . ($afe != '" "' ? ' blc_afectacion = "", ' : ' blc_afectacion = "X", ') . '
                        ' . ($clc != '" "' ? ' blc_clc = "" ' : ' blc_clc = "X" ') . '
                        ' . $wf . '
                        ');

                    $this->consultar();
                } else {
                    $this->setSql('
                        DELETE cat_BloqueoClc
                        FROM  cat_BloqueoClc
                        LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                        LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                        INNER JOIN enc_FiltroClc USING(efc_id)
                        ' . $wf . '
                        ');

                    $this->consultar();
                }

                $this->desconectar();
                $this->mixScript(' notify( "Los Bloqueos  se han eliminado  correctamente", "success");');
            }

            $this->main();
        } else {
            if ($this->prm->tarea == 'bloquear') {
                if (count($this->prm->datos) > 0 && $this->prm->datos != '') {
                    foreach ($this->prm->datos as $key => $val) {
                        $this->conectar();
                        $this->setSql('SET @usr_ip=INET_ATON("' . $ip . '"), @usr_id = ' . $this->usr_id . ', @bit_observacion = "Bloqueo de CLC desde la pantalla M 7 2";');
                        $this->consultar();

                        $whereecf_id = ' AND IFNULL(ecf_id,0)=0 ';
                        if ($val->tienebloqueo == 'S') {
                            $actubloqueo = '';

                            if ($val->blc_compromiso != '') {
                                $actubloqueo .= (($actubloqueo == '') ? '  blc_compromiso="X" ' : ', blc_compromiso="X" ');
                            }

                            if ($val->blc_afectacion != '') {
                                $actubloqueo .= (($actubloqueo == '') ? '  blc_afectacion="X" ' : ', blc_afectacion="X" ');
                            }

                            if ($val->blc_clc != '') {
                                $actubloqueo .= (($actubloqueo == '') ? '  blc_clc="X" ' : ', blc_clc="X" ');
                            }

                            if ($val->ecf_id != '') {
                                $whereecf_id = ' AND ecf_id=' . $val->ecf_id . ' ';
                            }

                            $this->setSql('
                                UPDATE cat_BloqueoClc
                                LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                                LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                                INNER JOIN enc_FiltroClc USING(efc_id)
                                SET ' . $actubloqueo . '
                                WHERE efc_id=' . $val->efc_id . ' ' . $whereecf_id . ' ;
                                ');

                            $this->consultar();
                            $this->desconectar();
                        } else {
                            $this->conectar();
                            $this->setSql('
                                SET @usr_ip=INET_ATON("' . $ip . '"), @usr_id = ' . $this->usr_id . ', @bit_observacion = "Bloqueo de CLC desde la pantalla M 7 2";
                                ');
                            $this->consultar();
                            $this->setSql('
                                INSERT INTO  ' . BD_CTRL . '.cat_BloqueoClc (efc_id , blc_inicio , blc_fin , ter_id , ecf_id, blc_compromiso, blc_afectacion, blc_clc, usr_id , usr_fecha )
                                VALUES (' . $val->efc_id . ',"' . $inicioFecha . ' ' . $horaInicio . '", "' . $finFecha . ' ' . $horaFin . '", "' . $this->prm->recurso . '",' . ($val->ecf_id == '' ? 'NULL' : $val->ecf_id) . ',' . ($val->blc_compromiso == '' ? "' '" : '"' . $val->blc_compromiso . '"') . ',' . ($val->blc_afectacion == '' ? "' '" : '"' . $val->blc_afectacion . '"') . ',' . ($val->blc_clc == '' ? "' '" : '"' . $val->blc_clc . '"') . ',' . $user . ', NOW())
                                ');
                            $this->consultar();
                            $this->desconectar();
                        }
                    }
                }

                $this->mixScript(' notify( "Los Bloqueos  se han ingresado correctamente", "success");');
            }

            if ($this->prm->tarea == 'desbloquear') {
                if (count($this->prm->datos) > 0 && $this->prm->datos != '') {
                    $this->conectar();
                    $this->setSql('SET @usr_ip=INET_ATON("' . $ip . '"), @usr_id = ' . $this->usr_id . ', @bit_observacion = "Desbloqueo de CLC desde la pantalla M 7 2";');
                    $this->consultar();
                    foreach ($this->prm->datos as $key => $val) {
                        $this->setSql('
                            SELECT * FROM  cat_BloqueoClc
                            LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                            INNER JOIN enc_FiltroClc USING(efc_id)
                            ' . $wf . '
                            ' . ($cmp != '" "' ? ' AND blc_compromiso = "x" ' : ' AND blc_compromiso = "" ') . '
                            ' . ($afe != '" "' ? ' AND blc_afectacion = "x" ' : ' AND blc_afectacion = "" ') . '
                            ' . ($clc != '" "' ? ' AND blc_clc = "x" ' : ' AND blc_clc = "" ') . '
                            ');
                        $this->consultar();
                        if ($this->getNumeroRegistros() == 0 && $elimbloqueo != 1) {
                            $this->setSql('
                                    UPDATE cat_BloqueoClc
                                    LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
                                    LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                                    INNER JOIN enc_FiltroClc USING(efc_id)
                                    SET ' . ($cmp != '" "' ? ' blc_compromiso = "", ' : ' blc_compromiso = "X", ') . '
                                    ' . ($afe != '" "' ? ' blc_afectacion = "", ' : ' blc_afectacion = "X", ') . '
                                    ' . ($clc != '" "' ? ' blc_clc = "" ' : ' blc_clc = "X" ') . '
                                    WHERE efc_id=' . $val->efc_id . ' AND  blc_id =' . $val->blc_id . '
                                ');
                            $this->consultar();
                        } else {

                            $were = '';
                            if ($elimbloqueo != 1) {
                                $were .= ($val->blc_compromiso == '' ? '' : ' AND blc_compromiso = "' . $val->blc_compromiso . '"');
                                $were .= ($val->blc_afectacion == '' ? '' : ' AND blc_afectacion = "' . $val->blc_afectacion . '"');
                                $were .= ($val->blc_clc == '' ? '' : ' AND blc_clc = "' . $val->blc_clc . '"');
                            }

                            $this->setSql(
                                '
                                DELETE FROM cat_BloqueoClc
                                WHERE efc_id=' . $val->efc_id . ' AND  blc_id =' . $val->blc_id . ' ' . $were . ';'
                            );
                            $this->consultar();
                        }
                    }
                    $this->desconectar();
                }

                $this->mixScript(' notify( "Los  Bloqueos se han eliminado correctamente", "success");');
            }

            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                metodo:"ListadoBloqueo",
                "tarea":"' . $this->prm->tarea . '"

                });

            ');
        }
    }

    private function ClavesFinanciamiento()
    {
        $this->prm->tipoRecurso = (isset($this->prm->tipoRecurso)) ? $this->prm->tipoRecurso : '';
        $where = '';
        $params = [];
        if ($this->prm->tipoRecurso != '') {
            if ($this->prm->tipoRecurso == 'C') {
                $where = '';
            } else {
                $where = ' AND ter_id = ?';
                $params = [$this->prm->tipoRecurso];
            }

            $cvefin = new stdClass();
            $cvefin->id = 'cvefin';
            $cvefin->sql = '
                SELECT 0 AS ecf_clave,"NINGUNA" as cfi_nombre
                UNION ALL
                SELECT ecf_clave,cfi_nombre FROM
                enc_ClaveFinanciamiento
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id)
                WHERE sts_id=1 ' . $where . '
                ';
            $cvefin->label = 'AGREGAR CLAVE DE FINANCIAMIENTO ';
            $cvefin->id = 'cvefin';

            $cvefin->name = 'ecf_clave[]';
            $cvefin->detonar = false;
            $cvefin->mensaje = false;
            $cvefin->params = $params;

            $this->recargaCombo($cvefin);
        } else {
            $this->mixScript(' notify("Elige de que tipo de Recurso deseas ver las Claves de Financiamiento", "warning");');
            return;
        }
    }

    private function TipoClc()
    {
        $this->prm->dep_id = (isset($this->prm->dep_id)) ? $this->prm->dep_id : '';
        if ($this->prm->dep_id != '') {
            $combo = new stdClass();
            $combo->sql = '
                SELECT "X" tcl_id,"SELECCIONAR TODOS LOS REGISTROS" as tcl_nombre
                UNION ALL
                SELECT tcl_id,tcl_nombre FROM enc_FiltroClc
                INNER JOIN cat_TipoClc USING(tcl_id)
                WHERE dep_id = ?
                GROUP BY tcl_id,dep_id
                ';
            $combo->id = 'tcl_id';
            $combo->detonar = false;
            $combo->mensaje = false;
            $combo->name = 'tcl_id[]';
            $combo->params = [$this->prm->dep_id];
            $this->recargaCombo($combo);
        } else {
            $this->mixScript(' notify("Elige el departamento", "warning");');
            return;
        }
    }

    private function SubtipoClc()
    {
        $this->prm->dep_id = (isset($this->prm->dep_id)) ? $this->prm->dep_id : '';
        $filtro = '';
        $params = [$this->prm->dep_id];

        if (is_array($this->prm->tcl_id) && count($this->prm->tcl_id) > 0) { // verificamos si es arreglo y que tenga al menos una opcion seleccionada
            if ($this->prm->tcl_id != '' && $this->prm->tcl_id[0] == 'X') {
                $filtro = '';
            } else {
                $bind = '';
                foreach ($this->prm->tcl_id as $key => $value) {
                    $bind .= '?, ';
                    array_push($params, $value);
                }
                $bind = substr($bind, 0, -2);
                $filtro = ' AND tcl_id in(' . $bind . ') ';
            }
            $combo = new stdClass();
            $combo->sql = '
                SELECT "X" stc_id,"SELECCIONAR TODOS LOS REGISTROS" as stc_nombre
                UNION ALL
                SELECT concat(tcl_id,"_",stc_id) as stc_id,stc_nombre
                FROM enc_FiltroClc
                INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
                WHERE dep_id = ? ' . $filtro . '
                GROUP BY tcl_id,stc_id,stc_nombre;';
            $combo->id = 'stc_id';
            $combo->mensaje = false;
            $combo->detonar = false;
            $combo->name = 'stc_id[]';
            $combo->params = $params;
            $this->recargaCombo($combo);
        } else {
            $this->mixScript(' notify("Elige un Tipo CLC", "warning");');
            return;
        }
    }

    private function DatosUr()
    {
        $where = $filtro = '';
        $this->prm->dep_id = (isset($this->prm->dep_id)) ? $this->prm->dep_id : '';

        if ($this->prm->tarea == 'bloquear') {
            $where = ' AND efc_inicio is null and efc_fin is null ';
        } else {
            $where = ' AND efc_inicio is not null and efc_fin is not null ';
        }
        if ($this->prm->stc_id[0] == 'X') {

            if (count($this->prm->tcl_id) > 0) {

                if ($this->prm->tcl_id[0] == 'X') {
                    $filtro = '';
                } else {
                    $filtro = ' AND tcl_id in("' . implode('","', $this->prm->tcl_id) . '") ';
                }
            }
        } else {

            foreach ($this->prm->stc_id as $dato) {
                $d = explode('_', $dato);
                $filtro .= '("' . $d[0] . '",' . $d[1] . '),';
            }

            $filtro = ' AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ') ';
        }

        $combo = new stdClass();
        $combo->sql = '
            SELECT 0 AS ur_id,"TODOS LOS REGISTROS" as ur_nombre
            UNION ALL
            SELECT concat(gpo_id,ur_id)ur_id,ur_nombre
            FROM enc_FiltroClc
            INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            WHERE dep_id=' . $this->prm->dep_id . ' ' . $filtro . ' ' . $where . '
            GROUP BY gpo_id,ur_id;
            ';
        $combo->id = 'ur_id';
        $combo->detonar = false;
        $combo->detonar = false;
        $combo->name = 'ur_id[]';
        $this->recargaCombo($combo);
    }
}
// fin funcion
//Verifica si el usuario tiene los permisos
if (isset($_SESSION[_S]['prm']['M']['7']['2']['77'])) {
    new ControladorBloquearCLC();
} else {
    WebResponse::sinPermisos();
}
