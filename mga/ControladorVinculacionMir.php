<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../srv/ClasificacionAdministrativaB.php';
set_time_limit(0);

class ControladorVinculacionMir extends WebResponse
{
    protected $usrIp;
    protected $usrId;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->pantalla = 'mga/ControladorVinculacionMir.php';

        //Variables de sesion
        $this->usrIp = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $this->usrId = $_SESSION[_S]['usr_id'];

        #si tiene el permiso de lectura y escritura se accede al controlador
        if ((isset($_SESSION[_S]['prm']['M']['8']['1']['1']) && $_SESSION[_S]['prm']['M']['8']['1']['1'] != '')
            && (isset($_SESSION[_S]['prm']['M']['8']['1']['2']) && $_SESSION[_S]['prm']['M']['8']['1']['2'] != '')
        ) {
            $_SESSION[_S]['opcion'] = $this->prm->opcion;
            switch ($this->metodo) {
                case 'main':
                    $this->main();
                    break;
                case 'ListaIndicadores':
                    $this->ListaIndicadores();
                    break;
                case 'llenaListaIndicadores':
                    $this->llenaListaIndicadores();
                    break;
                case 'VincularIndicador':
                    $this->VincularIndicador();
                    break;
                case 'IndicadoresVincualdos':
                    $this->IndicadoresVincualdos();
                    break;
                case 'llenaIndicadoresVincualdos':
                    $this->llenaIndicadoresVincualdos();
                    break;
                case 'ListaIntermedios':
                    $this->ListaIntermedios();
                    break;
                case 'ProductoFinal':
                    $this->ProductoFinal();
                    break;
                case 'ListaPrograma':
                    $this->ListaPrograma();
                    break;
                case 'ListaIntermediosDisponibles':
                    $this->ListaIntermediosDisponibles();
                    break;
                case 'llenaIntermediosDisponibles':
                    $this->llenaIntermediosDisponibles();
                    break;
                case 'VincularIntermedioFinal':
                    $this->VincularIntermedioFinal();
                    break;
                case 'IntermediosVinculados':
                    $this->IntermediosVinculados();
                    break;
                case 'llenaIntermediosVinculados':
                    $this->llenaIntermediosVinculados();
                    break;
                case 'DesVincularIntermedio':
                    $this->DesVincularIntermedio();
                    break;
                case 'DeshabilitarVinculacion':
                    $this->DeshabilitarVinculacion();
                    break;
                case 'DeshabilitarHabilitarIntermedio':
                    $this->DeshabilitarHabilitarIntermedio();
                    break;
                case 'ListaProductosFinales':
                    $this->ListaProductosFinales();
                    break;
                case 'llenaListaProductosFinales':
                    $this->llenaListaProductosFinales();
                    break;
                case 'PFSubProgramaVinculacion':
                    $this->PFSubProgramaVinculacion();
                    break;
                case 'llenaPFSubProgramaVinculacion':
                    $this->llenaPFSubProgramaVinculacion();
                    break;
                case 'AgregarPrograma':
                    $this->AgregarPrograma();
                    break;
                case 'VincularProductoSubprograma':
                    $this->VincularProductoSubprograma();
                    break;
                case 'eliminarVinculacion':
                    $this->eliminarVinculacion();
                    break;
            }
            $this->returnJson();
        } else {
            $this->sinPermisos();
        }
    }
    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
    ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function main()
    {
        $nivel = new stdClass();
        $nivel->id = 'nivel';
        $nivel->class = 'limpiar';
        $nivel->sql = '
            SELECT nim_id, nim_nombre
            FROM cat_NivelMir;';
        $nivel->label = 'Nivel';
        $nivel->enRow = 1;
        $nivel->search = true;
        $nivel->change = '
            if($(this).val() != "") {
                $("#tbl_Vin,#tbl_Conceptos").remove();
                $("#btnAgregar,#btnQuitar").remove();
                switch($(this).find("option:selected").val()){
                    case "1": case "2":
                        if($(this).val() != ""){
                            comunica("' . $this->pantalla . '",{
                                metodo:"ListaPrograma",
                                nivel: $("#nivel").val()
                            });
                        }
                        break;
                    case "3": case "4":
                        if($(this).val() != ""){
                            comunica("' . $this->pantalla . '",{
                                metodo:"ProductoFinal",
                                nivel: $("#nivel").val()
                            });
                        }
                        break;
                }
            }
        ';

        $pdf_id = new stdClass();
        $pdf_id->id = 'pdf_id';
        $pdf_id->class = 'limpiar';
        $pdf_id->sql = '
            SELECT pdf_id, pdf_plural
            FROM cat_ProductoFinal
            WHERE pdf_id != 0
                AND pdf_final = 1;';
        $pdf_id->label = 'Producto Final';
        $pdf_id->enRow = 1;
        $pdf_id->search = true;
        $pdf_id->change = '
            $("#btnAgregar").remove();
            if($(this).val() != ""){
                if($(this).val() != ""){
                    comunica("' . $this->pantalla . '",{
                        metodo:"ListaIntermediosDisponibles",
                        pdf_id: $("#pdf_id").val()
                    });
                }
            }
            if($(this).val() != ""){
                if($(this).val() != ""){
                    comunica("' . $this->pantalla . '",{
                        metodo:"IntermediosVinculados",
                        pdf_id: $("#pdf_id").val()
                    });
                }
            }
        ';

        $html = '';
        $html = '
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item uno"><a href="#catalogo" class="nav-link active" role="tab" data-toggle="tab"><b>Vinculación MIR</b></a></li>
                <li class="nav-item dos"><a href="#productos" class="nav-link" role="tab" data-toggle="tab"><b>Vinculación    Producto Final - Producto Intermedio</b></a></li>
                <li class="nav-item tres"><a href="#subprogramas" class="nav-link" role="tab" data-toggle="tab"><b>Vinculación Producto Final - Subprograma 0 </b></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="catalogo">
                    <br><br>
                    <div class="row col-md-12 col-xs-12" >
                        <fieldset class="col-md-4 col-xs-4" >
                            ' . $this->ponCombo($nivel) . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-4" id="prog">
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-4" id="intermedios">
                        </fieldset>
                    </div>
                    <br>
                    <div class="row col-md-12 col-xs-12" >
                        <fieldset id="divContenidoVinculacion" class="col-md-6 col-xs-12">
                        </fieldset>
                        <fieldset id="botones" class="col-md-1 col-xs-1">
                        </fieldset>
                        <fieldset id="divListaIndicadores" class="col-md-5 col-xs-12">
                        </fieldset>
                    </div>
                </div>
                <!-- FUNCIONES PARA LA SEPARACION DEL PRODUCTO FINAL E INTERMEDIO -->
                <div class="tab-pane" id="productos">
                    <br><br>
                        <div class="row col-md-12 col-xs-12" >
                            <fieldset class="col-md-4 col-xs-4 mb-4" >
                                ' . $this->ponCombo($pdf_id) . '
                            </fieldset>
                        </div>
                    <div class="row col-md-12 col-xs-12" >
                        <fieldset id="divFinales" class="col-md-5 col-xs-5">
                        </fieldset>
                        <fieldset id="botons" class="col-md-1 col-xs-1">
                        </fieldset>
                        <fieldset id="divIntermedios" class="col-md-6 col-xs-6">
                        </fieldset>
                    </div>
                </div>
                <div class="tab-pane" id="subprogramas">
                    <br><br>
                    <div class="row col-md-12 col-xs-12" >
                        <fieldset id="idprogramas" class="col-md-7 col-xs-7">
                        </fieldset>
                        <fieldset id="idFinales" class="tabla-contenedor col-md-5 col-xs-5 ">
                        </fieldset>
                    </div>
                    <div class="row col-md-12 col-xs-12" >
                        <fieldset id="catPrograma" class="col-md-12 col-xs-12">
                        </fieldset>
                    </div>
                </div>
            </div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $(".uno").click(function(){
                $("#catalogo").addClass("active");
                $("#productos").removeClass("active");
                $("#subprogramas").removeClass("active");
                $(".uno > a").addClass("active");
                $(".dos > a").removeClass("active");
                $(".tres > a").removeClass("active");
            });

            $(".dos").click(function(){
                $("#productos").addClass("active");
                $("#catalogo").removeClass("active");
                $("#subprogramas").removeClass("active");
                $(".dos > a").addClass("active");
                $(".uno > a").removeClass("active");
                $(".tres > a").removeClass("active");
            });

            $(".tres").click(function(){
                $("#subprogramas").addClass("active");
                $("#catalogo").removeClass("active");
                $("#productos").removeClass("active");
                $(".tres > a").addClass("active");
                $(".uno > a").removeClass("active");
                $(".dos > a").removeClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo:"ListaProductosFinales"
                });

                comunica("' . $this->pantalla . '",{
                    metodo:"PFSubProgramaVinculacion",
                    pdf_id: $("#pdf_id").val()
                });
            });
        ');
    }

    public function ListaIndicadores()
    {
        $this->setContenedor('divListaIndicadores');
        $this->prm->nivel = (isset($this->prm->nivel)) ? $this->prm->nivel : '';
        $this->prm->prg_id = (isset($this->prm->prg_id)) ? $this->prm->prg_id : '';
        $this->prm->producto = isset($this->prm->producto) ? $this->prm->producto : '';
        $this->prm->intermedio = isset($this->prm->intermedio) ? $this->prm->intermedio : '';

        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = ['ind_id', 'nim_id'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'nim_id';

        switch ($this->prm->nivel) {
            case 1:
                $tabla->casoSeleccionable->valor = '1';
                break;
            case 2:
                $tabla->casoSeleccionable->valor = '2';
                break;
            case 3:
                $tabla->casoSeleccionable->valor = '3';
                break;
            case 4:
                $tabla->casoSeleccionable->valor = '4';
                break;
        }

        $tabla->casoSeleccionable->ands = [];
        $and = new stdClass();
        $and->campo = 'nim_id';
        $and->operador = '!=';
        $and->valor = '0';
        $and->valorTipo = 'numero';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';

        $tabla->id = 'tbl_Conceptos';
        $tabla->titulo = 'Lista de Indicadores Nivel ' . $this->prm->nivel . ' ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->nivel = $this->prm->nivel;
        $tabla->comunica->prg_id = $this->prm->prg_id;
        $tabla->comunica->producto = $this->prm->producto;
        $tabla->comunica->intermedio = $this->prm->intermedio;
        $tabla->comunica->metodo = 'llenaListaIndicadores';
        $tabla->numFilas = 20;
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Indicador';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->campoTxt = 'ind_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ind_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml('
            <div class="input-group input-group-sm" >
                <input id="ind_buscar" name="ind_buscar" type="text" class="form-control" placeholder="BUSCAR POR INDICADOR/NOMBRE" >
                <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" onClick="filtraIndicadores()"  type="button">Buscar</button>
                </div>
            </div>
        ');
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript('
            function filtraIndicadores(){
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaListaIndicadores",
                    nivel:"' . $this->prm->nivel . '",
                    prg_id:"' . $this->prm->prg_id . '",
                    producto:"' . $this->prm->producto . '",
                    intermedio:"' . $this->prm->intermedio . '",
                    busqueda:$("#ind_buscar").val()
                });
            }
        ');

        $btnAgregar = new stdClass();
        $btnAgregar->id = 'btnAgregar';
        $btnAgregar->icon = 'fa-solid fa-chevron-left';
        $btnAgregar->color = 'verde';
        $btnAgregar->value = 'Agregar';

        switch ($this->prm->nivel) {
            case 1:
            case 2:
                $btnAgregar->click = '
                    if ($("#programa").val()!="" && $("#programa").val()!="NULL"){
                        if(seleccionados = $("#tbl_Conceptos").tabla("getFilaSeleccion")){
                            comunica("' . $this->pantalla . '",{
                            metodo:"VincularIndicador",
                            indicadores:seleccionados,
                            nivel:"' . $this->prm->nivel . '",
                            prg_id:"' . $this->prm->prg_id . '"
                            });
                        }
                    } else {
                        notify("Elige un programa para realizar la vinculación ");
                        return false;
                    }
                ';
                break;
            case 3:
                $btnAgregar->click = '
                    if($("#producto").val()!="" && $("#producto").val()!="NULL"){
                        if(seleccionados = $("#tbl_Conceptos").tabla("getFilaSeleccion")){
                            comunica("' . $this->pantalla . '",{
                            metodo:"VincularIndicador",
                            indicadores:seleccionados,
                            producto:$("#producto").val(),
                            nivel:"' . $this->prm->nivel . '"
                            });
                        }
                    }else{
                        notify("Elige un programa para realizar la vinculación ");
                        return false;
                    }
                ';

                break;
            case 4:
                $btnAgregar->click = '
                    if($("#producto").val()!="" && $("#producto").val()!="NULL"){
                        if($("#intermedio").val()!="" && $("#intermedio").val()!="NULL"){
                            if(seleccionados = $("#tbl_Conceptos").tabla("getFilaSeleccion")){
                                comunica("' . $this->pantalla . '",{
                                metodo:"VincularIndicador",
                                indicadores:seleccionados,
                                producto:$("#producto").val(),
                                intermedio:$("#intermedio").val(),
                                nivel:"' . $this->prm->nivel . '"
                                });
                            }
                        }else{
                            notify("Elige un Producto Intermedio para realizar la vinculación ");
                            return false;
                        }
                    }else{
                        notify("Elige un Producto Final para realizar la vinculación ");
                        return false;
                    }
                ';
                break;
        }

        $btnAgregar = $this->ponBoton($btnAgregar);
        $this->mixHtml('' . $btnAgregar . ' ');
        $this->mixScript(' $("#botones").append($("<br/>")).append($("#btnAgregar")).append($("<br/>")); ');

        $this->llenaListaIndicadores();
    }

    private function llenaListaIndicadores()
    {
        $this->prm->nivel = isset($this->prm->nivel) ? $this->prm->nivel : '';
        $this->prm->prg_id = isset($this->prm->prg_id) ? $this->prm->prg_id : '';
        $this->prm->producto = isset($this->prm->producto) ? $this->prm->producto : '';
        $this->prm->intermedio = isset($this->prm->intermedio) ? $this->prm->intermedio : '';
        $this->prm->busqueda = isset($this->prm->busqueda) ? $this->prm->busqueda : '';
        $left = '';
        $prms = [];

        switch ($this->prm->nivel) {
            case 1:
                $left = '
                    LEFT  JOIN(
                        SELECT
                            nim_id,
                            ind_id,
                            vpm_id AS valor
                        FROM vin_ProgramaMirIndicador AS pmi
                        INNER JOIN vin_ProgramasMir AS pm USING (vpm_id)
                        WHERE pm.prgs_id = ?
                    )x USING (nim_id,ind_id)
                ';
                array_push($prms, $this->prm->prg_id);
                break;

            case 2:
                $left = '
                    LEFT  JOIN(
                        SELECT
                            nim_id,
                            ind_id,
                            vpm_id AS valor
                        FROM vin_ProgramaMirIndicador AS pmi
                        INNER JOIN vin_ProgramasMir AS pm USING (vpm_id)
                        WHERE pm.prgs_id = ?
                    )x USING (nim_id,ind_id)
                ';
                array_push($prms, $this->prm->prg_id);
                break;

            case 3:
                $left = '
                    LEFT JOIN(
                        SELECT
                            nim_id,
                            ind_id,
                            pdf_id AS valor
                        FROM enc_ProductoFinalIndicador
                        WHERE pdf_id = ?
                    )x USING (nim_id,ind_id)
                ';
                array_push($prms, $this->prm->producto);
                break;

            case 4:
                $left = '
                    LEFT JOIN(
                        SELECT
                            nim_id,
                            ind_id,
                            pdf_id AS valor
                        FROM vin_ProductoIntermedioIndicador
                        WHERE pdf_id = ?
                            AND pdi_id = ?
                    )x USING (nim_id,ind_id)
                ';
                array_push($prms, $this->prm->producto);
                array_push($prms, $this->prm->intermedio);
                break;
        }

        $this->conectar();
        $sql = '
            SELECT
                nim_id,
                ind_id,
                ind_nombre,
                valor
            FROM cat_Indicador
                ' . $left . '
            WHERE nim_id = ?
                AND isnull(valor)
                AND sts_id=1 ;
        ';
        array_push($prms, $this->prm->nivel);
        if (!empty($this->prm->busqueda)) {
            $sql .= 'AND (ind_id = ? OR ind_nombre like ?)';
            array_push($prms, $this->prm->busqueda);
            array_push($prms, '%' . $this->prm->busqueda . '%');
        }
        $this->setSql($sql);
        $this->setParams($prms);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 20;
        $nav->idTabla = 'tbl_Conceptos';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }
    }

    public function VincularIndicador()
    {
        $this->prm->nivel = (isset($this->prm->nivel)) ? $this->prm->nivel : '';
        $this->prm->prg_id = (isset($this->prm->prg_id)) ? $this->prm->prg_id : '';
        $this->prm->producto = isset($this->prm->producto) ? $this->prm->producto : '';
        $this->prm->intermedio = isset($this->prm->intermedio) ? $this->prm->intermedio : '';
        $ip = $this->usrIp;
        $user = $this->usrId;
        $select = '';
        $prms = [];

        $hoy = date('Y-m-d H:i:s');
        $trim_sts = '';

        if ($hoy <= APP_EJERCICIO_ANTERIOR . '-12-31 23:59:59') {
            $trim_sts = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = '1,1,1,1,1,1';
            $tri_id = 0;
        } elseif ($hoy >= APP_EJERCICIO . '-01-01 00:00:00' && $hoy <= APP_EJERCICIO . '-03-31 23:59:59') {
            $trim_sts = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = '1,2,1,1,1,1';
            $tri_id = 1;
        } elseif ($hoy >= APP_EJERCICIO . '-04-01 00:00:00' && $hoy <= APP_EJERCICIO . '-07-30 23:59:59') {
            $trim_sts = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = '1,2,2,1,1,1';
            $tri_id = 2;
        } elseif ($hoy >= APP_EJERCICIO . '-07-01 00:00:00' && $hoy <= APP_EJERCICIO . '-09-30 23:59:59') {
            $trim_sts = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = '1,2,2,2,1,1';
            $tri_id = 3;
        } elseif ($hoy >= APP_EJERCICIO . '-10-01 00:00:00' && $hoy <= APP_EJERCICIO . '-12-31 23:59:59') {
            $trim_sts = 'sts_id,trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = '1,2,2,2,2,1';
            $tri_id = 4;
        }

        if (count($this->prm->indicadores) > 0 && $this->prm->indicadores != '') {
            foreach ($this->prm->indicadores as $key => $val) {
                if ($this->prm->nivel <= 2) {
                    $select = 'vpm_id AS valor ';
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql('
                        SELECT
                            ' . $select . ',
                            prgs_id AS prg_id
                        FROM  vin_ProgramasMir
                        WHERE prgs_id = ? ;');
                    $conexion->consultar([$this->prm->prg_id]);
                    $conexion->desconectar();
                    $row = $conexion->extraer();
                    unset($select);
                }
                $con = new AccesoDatos();
                $con->conectar();
                switch ($this->prm->nivel) {
                    case 1:
                        $con->setSql('
                            SELECT
                                ind_id
                            FROM vin_ProgramaMirIndicador
                            WHERE ind_id = ?
                                AND vpm_id = ?
                                AND nim_id = ?
                        ');
                        $con->consultar([$val->ind_id, $row['valor'], $this->prm->nivel]);
                        $row1 = $con->extraer();
                        if (isset($row1['ind_id']) && $row1['ind_id'] != null) {
                            $this->mixScript(' notify("El Indicador ' . $val->ind_id . ' ya está vinculado con el programa ' . $this->prm->prg_id . ' ","error"); ');
                            return false;
                        }
                        break;
                    case 2:
                        $con->setSql('
                            SELECT
                                ind_id
                            FROM vin_ProgramaMirIndicador
                            WHERE ind_id = ?
                                AND vpm_id = ?
                                AND nim_id = ?
                        ');
                        $con->consultar([$val->ind_id, $row['valor'], $this->prm->nivel]);
                        $row1 = $con->extraer();
                        if (isset($row1['ind_id']) && $row1['ind_id'] != null) {
                            $this->mixScript(' notify("El Indicador ' . $val->ind_id . ' ya está vinculado con el programa ' . $this->prm->prg_id . ' ","error"); ');
                            return false;
                        }
                        break;
                    case 3:
                        $con->setSql('
                            SELECT
                                ind_id
                            FROM enc_ProductoFinalIndicador
                            WHERE pdf_id = ?
                                AND ind_id = ?
                        ');
                        $con->consultar([$this->prm->producto, $val->ind_id]);
                        $row1 = $con->extraer();
                        if (isset($row1['ind_id']) && $row1['ind_id'] != null) {
                            $this->mixScript(' notify("El Indicador ' . $val->ind_id . ' ya está vinculado con el programa ' . $this->prm->producto . ' ","error"); ');
                            return false;
                        }
                        break;
                    case 4:
                        $con->setSql('
                            SELECT
                                ind_id
                            FROM vin_ProductoIntermedioIndicador
                            WHERE pdf_id = ?
                                AND  ind_id = ?
                                AND pdi_id = ?
                        ');
                        $con->consultar([$this->prm->producto, $val->ind_id, $this->prm->intermedio]);
                        $row1 = $con->extraer();
                        if (isset($row1['ind_id']) && $row1['ind_id'] != null) {
                            $this->mixScript(' notify("El Indicador ' . $val->ind_id . ' ya está vinculado  ","error"); ');
                            return false;
                        }
                        break;
                }
                $con->desconectar();

                $this->conectar();
                switch ($this->prm->nivel) {
                    case 1:
                        $this->setSql('
                            INSERT INTO vin_ProgramaMirIndicador(vpm_id, nim_id, ind_id, ' . $trim_sts . ', pmi_orden, usr_id)
                            VALUES(?, ?, ?,' . $sts . ', 0, ?)
                        ');
                        $prms = [$row['valor'], $val->nim_id, $val->ind_id, $user];
                        $this->consultar($prms);
                        $pmi_id = $this->getUltimoId();

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ProgramaMirIndicador
                                (bit_observacion, pmi_id, pmi_orden, vpm_id, nim_id, ind_id, ' . $trim_sts . ', usr_ip, usr_id)
                            VALUES
                                ("Se agregó la vinculación a nivel fin ", ?, 0, ?, ?, ?, ' . $sts . ', INET_ATON(?), ?)
                        ');
                        $prms = [$pmi_id, $row['valor'], $val->nim_id, $val->ind_id, $ip, $user];
                        $this->consultar($prms);

                        break;
                    case 2:
                        $this->setSql('
                            INSERT INTO vin_ProgramaMirIndicador
                                (vpm_id, nim_id, ind_id, ' . $trim_sts . ', pmi_orden, usr_id)
                            VALUES
                                ( ?, ?, ?, ' . $sts . ', 0, ?)
                        ');
                        $prms = [$row['valor'], $val->nim_id, $val->ind_id, $user];
                        $this->consultar($prms);
                        $pmi_id = $this->getUltimoId();

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ProgramaMirIndicador
                                (bit_observacion, pmi_id, pmi_orden, vpm_id, nim_id , ind_id, ' . $trim_sts . ', usr_ip, usr_id)
                            VALUES
                                ("Se agregó la vinculación a nivel Propósito ", ?, 0, ?, ?, ?, ' . $sts . ', INET_ATON(?), ?)
                        ');
                        $prms = [$pmi_id, $row['valor'], $val->nim_id, $val->ind_id, $ip, $user];
                        $this->consultar($prms);

                        break;
                    case 3:
                        $this->setSql('
                            INSERT INTO enc_ProductoFinalIndicador
                                (pdf_id, nim_id, ind_id, ' . $trim_sts . ', epfi_orden, usr_id)
                            VALUES
                                (?, ?, ?, ' . $sts . ', 0, ?)
                        ');
                        $prms = [$this->prm->producto, $val->nim_id, $val->ind_id, $user];
                        $this->consultar($prms);
                        $epfi_id = $this->getUltimoId();

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ProductoFinalIndicador
                                (bit_observacion, epfi_id, epfi_orden, pdf_id, nim_id, ind_id, ' . $trim_sts . ', usr_ip, usr_id)
                            VALUES
                                ("Se agregó la vinculación a nivel Componente ", ?, 0, ?, ?, ?, ' . $sts . ', INET_ATON(?), ?)
                        ');
                        $prms = [$epfi_id, $this->prm->producto, $val->nim_id, $val->ind_id, $ip, $user];
                        $this->consultar($prms);

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ActualizaIndicadorComponente(bit_observacion, epfi_id, pdf_id, tri_id, ind_id, usr_ip, usr_id)
                            VALUES("Se agregó vinculación de indicador", ' . $epfi_id . ', ' . $this->prm->producto . ', ' . $tri_id . ', ' . $val->ind_id . ', INET_ATON("' . $ip . '"), ' . $user . ')
                        ');
                        $this->consultar();
                        break;
                    case 4:
                        $this->setSql('
                            INSERT INTO vin_ProductoIntermedioIndicador
                                (pdf_id, pdi_id, nim_id, ind_id, ' . $trim_sts . ', pii_orden, usr_id)
                            VALUES
                                (?, ?, ?, ?, ' . $sts . ', 0, ?)
                        ');
                        $prms = [$this->prm->producto, $this->prm->intermedio, $val->nim_id, $val->ind_id, $user];
                        $this->consultar($prms);
                        $pii_id = $this->getUltimoId();

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ProductoIntermedioIndicador
                                (bit_observacion, pii_id, pdf_id, pdi_id, nim_id, ind_id, ' . $trim_sts . ', pii_orden, usr_ip, usr_id)
                            VALUES
                                ("Se agregó la vinculación a nivel Actividad ", ?, ?, ?, ?, ?, ' . $sts . ', 0, INET_ATON(?), ?)
                        ');
                        $prms = [$pii_id, $this->prm->producto, $this->prm->intermedio, $val->nim_id, $val->ind_id, $ip, $user];
                        $this->consultar($prms);

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ActualizaIndicadorActividad(bit_observacion, pii_id, pdf_id, pdi_id, tri_id, ind_id, usr_ip, usr_id)
                            VALUES("Se agregó la vinculación de indicador", ' . $pii_id . ', ' . $this->prm->producto . ', ' . $this->prm->intermedio . ', ' . $tri_id . ', ' . $val->ind_id . ', INET_ATON("' . $ip . '"), ' . $user . ')
                        ');
                        $this->consultar();
                        break;
                }

                $this->desconectar();
            }
            $this->mixScript(' notify("El Indicador ' . $val->ind_id . ' ha sido vinculado  correctamente ","success"); ');
            $this->llenaListaIndicadores();
            $this->llenaIndicadoresVincualdos();
        }
    }

    public function IndicadoresVincualdos()
    {
        $this->setContenedor('divContenidoVinculacion');
        $this->prm->nivel = isset($this->prm->nivel) ? $this->prm->nivel : '';
        $this->prm->prg_id = isset($this->prm->prg_id) ? $this->prm->prg_id : '';
        $this->prm->producto = isset($this->prm->producto) ? $this->prm->producto : '';
        $this->prm->intermedio = isset($this->prm->intermedio) ? $this->prm->intermedio : '';

        $tabla = new stdClass();
        $tabla->id = 'tbl_Vin';
        $tabla->titulo = 'Lista de Indicadores Vinculados Nivel ' . $this->prm->nivel . ' ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->nivel = $this->prm->nivel;
        $tabla->comunica->prg_id = $this->prm->prg_id;
        $tabla->comunica->producto = $this->prm->producto;
        $tabla->comunica->intermedio = $this->prm->intermedio;
        $tabla->comunica->metodo = 'llenaIndicadoresVincualdos';
        $tabla->numFilas = 20;
        $tr = [];

        switch ($this->prm->nivel) {
            case 1:
                $th = new stdClass();
                $th->texto = 'Indicador';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Nombre';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'ON/OFF';
                $th->width = '60px';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Eliminar';
                array_push($tr, $th);

                break;
            case 2:
                $th = new stdClass();
                $th->texto = 'Indicador';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Nombre';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'ON/OFF';
                $th->width = '60px';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Eliminar';
                array_push($tr, $th);

                break;
            case 3:
                $th = new stdClass();
                $th->texto = 'Producto Final';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Nombre Producto Final';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Indicador';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Nombre';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'ON/OFF';
                $th->width = '60px';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Eliminar';
                array_push($tr, $th);
                break;
            case 4:
                $th = new stdClass();
                $th->texto = 'Producto Final';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Producto Intermedio';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Nombre Producto Intermedio';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Indicador';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Nombre';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'ON/OFF';
                $th->width = '60px';
                array_push($tr, $th);
                $th = new stdClass();
                $th->texto = 'Eliminar';
                array_push($tr, $th);
                break;
        }

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        switch ($this->prm->nivel) {
            case 1:
                $td = new stdClass();
                $td->campoTxt = 'ind_id';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'ind_nombre';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = 'sts_id';
                $td->casos->valor = '1';
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->color = 'verde';
                $td->casos->verdadero->icono = 'fa-solid fa-power-off';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->pmi_id = 'pmi_id';
                $td->casos->verdadero->attr->vpm_id = 'vpm_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = 'detalle';
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'fa-solid fa-power-off';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->pmi_id = 'pmi_id';
                $td->casos->falso->attr->vpm_id = 'vpm_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'detalle';
                array_push($trbody, $td);

                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = "sts_id";
                $td->casos->valor = "1";
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = "boton";
                $td->casos->verdadero->color = "rojo";
                $td->casos->verdadero->icono = "trash-can";
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->pmi_id = 'pmi_id';
                $td->casos->verdadero->attr->vpm_id = 'vpm_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = "deleteVinculacion";
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'trash-can';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->pmi_id = 'pmi_id';
                $td->casos->falso->attr->vpm_id = 'vpm_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'deleteVinculacion';
                array_push($trbody, $td);

                break;
            case 2:
                $td = new stdClass();
                $td->campoTxt = 'ind_id';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'ind_nombre';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = 'sts_id';
                $td->casos->valor = '1';
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->color = 'verde';
                $td->casos->verdadero->icono = 'fa-solid fa-power-off';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->pmi_id = 'pmi_id';
                $td->casos->verdadero->attr->vpm_id = 'vpm_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = 'detalle';
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'fa-solid fa-power-off';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->pmi_id = 'pmi_id';
                $td->casos->falso->attr->vpm_id = 'vpm_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'detalle';
                array_push($trbody, $td);

                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = "sts_id";
                $td->casos->valor = "1";
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = "boton";
                $td->casos->verdadero->color = "rojo";
                $td->casos->verdadero->icono = "trash-can";
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->pmi_id = 'pmi_id';
                $td->casos->verdadero->attr->vpm_id = 'vpm_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = "deleteVinculacion";
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'trash-can';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->pmi_id = 'pmi_id';
                $td->casos->falso->attr->vpm_id = 'vpm_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'deleteVinculacion';
                array_push($trbody, $td);
                break;
            case 3:
                $td = new stdClass();
                $td->campoTxt = 'pdf_id';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'pdf_plural';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'ind_id';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'ind_nombre';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = 'sts_id';
                $td->casos->valor = '1';
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->color = 'verde';
                $td->casos->verdadero->icono = 'fa-solid fa-power-off';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->epfi_id = 'epfi_id';
                $td->casos->verdadero->attr->pdf_id = 'pdf_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = 'detalle';
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'fa-solid fa-power-off';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->epfi_id = 'epfi_id';
                $td->casos->falso->attr->pdf_id = 'pdf_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'detalle';
                array_push($trbody, $td);

                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = "sts_id";
                $td->casos->valor = "1";
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = "boton";
                $td->casos->verdadero->color = "rojo";
                $td->casos->verdadero->icono = "trash-can";
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->epfi_id = 'epfi_id';
                $td->casos->verdadero->attr->pdf_id = 'pdf_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = "deleteVinculacion";
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'trash-can';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->epfi_id = 'epfi_id';
                $td->casos->falso->attr->pdf_id = 'pdf_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'deleteVinculacion';
                array_push($trbody, $td);

                break;
            case 4:
                $td = new stdClass();
                $td->campoTxt = 'pdf_id';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'pdi_id';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'pdi_plural';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'ind_id';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->campoTxt = 'ind_nombre';
                array_push($trbody, $td);
                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = 'sts_id';
                $td->casos->valor = '1';
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = 'boton';
                $td->casos->verdadero->color = 'verde';
                $td->casos->verdadero->icono = 'fa-solid fa-power-off';
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->pii_id = 'pii_id';
                $td->casos->verdadero->attr->ipi_id = 'ipi_id';
                $td->casos->verdadero->attr->pdf_id = 'pdf_id';
                $td->casos->verdadero->attr->pdi_id = 'pdi_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = 'detalle';
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'fa-solid fa-power-off';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->pii_id = 'pii_id';
                $td->casos->falso->attr->ipi_id = 'ipi_id';
                $td->casos->falso->attr->pdf_id = 'pdf_id';
                $td->casos->falso->attr->pdi_id = 'pdi_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'detalle';
                array_push($trbody, $td);

                $td = new stdClass();
                $td->casos = new stdClass();
                $td->casos->campo = "sts_id";
                $td->casos->valor = "1";
                $td->casos->verdadero = new stdClass();
                $td->casos->verdadero->tipo = "boton";
                $td->casos->verdadero->color = "rojo";
                $td->casos->verdadero->icono = "trash-can";
                $td->casos->verdadero->attr = new stdClass();
                $td->casos->verdadero->attr->pii_id = 'pii_id';
                $td->casos->verdadero->attr->ipi_id = 'ipi_id';
                $td->casos->verdadero->attr->pdf_id = 'pdf_id';
                $td->casos->verdadero->attr->pdi_id = 'pdi_id';
                $td->casos->verdadero->attr->nim_id = 'nim_id';
                $td->casos->verdadero->attr->ind_id = 'ind_id';
                $td->casos->verdadero->attr->status = '1';
                $td->casos->verdadero->class = "deleteVinculacion";
                $td->casos->falso = new stdClass();
                $td->casos->falso->tipo = 'boton';
                $td->casos->falso->color = 'rojo';
                $td->casos->falso->icono = 'trash-can';
                $td->casos->falso->attr = new stdClass();
                $td->casos->falso->attr->pii_id = 'pii_id';
                $td->casos->falso->attr->ipi_id = 'ipi_id';
                $td->casos->falso->attr->pdf_id = 'pdf_id';
                $td->casos->falso->attr->pdi_id = 'pdi_id';
                $td->casos->falso->attr->nim_id = 'nim_id';
                $td->casos->falso->attr->ind_id = 'ind_id';
                $td->casos->falso->attr->status = '2';
                $td->casos->falso->class = 'deleteVinculacion';
                array_push($trbody, $td);

                break;
        }
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaIndicadoresVincualdos();
    }

    private function llenaIndicadoresVincualdos()
    {
        $nivel = isset($this->prm->nivel) ? $this->prm->nivel : '';
        $this->prm->prg_id = isset($this->prm->prg_id) ? $this->prm->prg_id : '';
        $this->prm->producto = isset($this->prm->producto) ? $this->prm->producto : '';
        $this->prm->intermedio = isset($this->prm->intermedio) ? $this->prm->intermedio : '';
        $wh = '';
        $prms = [];
        $hoy = date('Y-m-d H:i:s');
        $tri_id = '';
        if ($this->prm->prg_id != '') {
            $wh .= ' AND prgs_id = ?';
            array_push($prms, $this->prm->prg_id);
        }
        if ($this->prm->producto != '') {
            $wh .= ' AND pdf_id = ?';
            array_push($prms, $this->prm->producto);
        }
        if ($this->prm->producto != '' && $this->prm->intermedio != '') {
            $wh .= ' AND pdf_id = ? AND pdi_id = ?';
            array_push($prms, $this->prm->producto);
            array_push($prms, $this->prm->intermedio);
        }

        if ($hoy <= APP_EJERCICIO_ANTERIOR . '-12-31 23:59:59') {
            $tri_id = 'trim_0';
        } elseif ($hoy >= APP_EJERCICIO . '-01-01 00:00:00' && $hoy <= APP_EJERCICIO . '-03-31 23:59:59') {
            $tri_id = 'trim_1';
        } elseif ($hoy >= APP_EJERCICIO . '-04-01 00:00:00' && $hoy <= APP_EJERCICIO . '-07-30 23:59:59') {
            $tri_id = 'trim_2';
        } elseif ($hoy >= APP_EJERCICIO . '-07-01 00:00:00' && $hoy <= APP_EJERCICIO . '-09-30 23:59:59') {
            $tri_id = 'trim_3';
        } elseif ($hoy >= APP_EJERCICIO . '-10-01 00:00:00' && $hoy <= APP_EJERCICIO . '-12-31 23:59:59') {
            $tri_id = 'trim_4';
        }

        $this->conectar();

        switch ($nivel) {
            case 1:
                $sql = '
                    SELECT
                        vpm_id,nim_id,ind_id,pmi_id,ind_nombre,
                        if(MAX(mtf_id) IS NULL,1,0) AS SEL,
                        if(a.' . $tri_id . ' = 1,1,0) AS sts_id
                    FROM vin_ProgramaMirIndicador AS a
                        INNER JOIN cat_Indicador USING (nim_id,ind_id)
                        LEFT JOIN cat_MetaFin USING (pmi_id)
                        INNER JOIN vin_ProgramasMir USING (vpm_id)
                    WHERE nim_id = 1
                        ' . $wh . '
                    GROUP BY vpm_id, ind_id
                    ORDER BY vpm_id, ind_id;
                ';
                break;
            case 2:
                $sql = '
                    SELECT
                        vpm_id,nim_id,ind_id,pmi_id,ind_nombre,
                        if(MAX(mtf_id) IS NULL,1,0) AS SEL,
                        if(a.' . $tri_id . ' = 1,1,0) AS sts_id
                    FROM vin_ProgramaMirIndicador AS a
                        INNER JOIN cat_Indicador USING (nim_id,ind_id)
                        LEFT JOIN cat_MetaFin USING (pmi_id)
                        INNER JOIN vin_ProgramasMir USING (vpm_id)
                    WHERE nim_id = 2
                        ' . $wh . '
                    GROUP BY vpm_id, ind_id
                    ORDER BY vpm_id, ind_id;
                ';
                break;
            case 3:
                $sql = '
                    SELECT
                        pdf_id,nim_id,pdf_plural,
                        ind_id,epfi_id,ind_nombre,
                        if(epfi_id IS NULL,1,0) AS SEL,
                        if(a.' . $tri_id . ' = 1,1,0) AS sts_id
                    FROM enc_ProductoFinalIndicador a
                        INNER JOIN cat_Indicador USING (nim_id,ind_id)
                        INNER JOIN cat_ProductoFinal USING (pdf_id)
                    WHERE nim_id = 3
                        ' . $wh . '
                    GROUP BY epfi_id,pdf_id,ind_id
                    ORDER BY pdf_id,ind_id;
                ';
                break;
            case 4:
                $sql = '
                    SELECT
                        pdf_id,pdi_id,pdi_plural,nim_id,
                        ind_id,pii_id,ind_nombre,
                        if(pii_id IS NULL,1,0) AS SEL,
                        if(a.' . $tri_id . ' = 1,1,0) AS sts_id
                    FROM vin_ProductoIntermedioIndicador a
                        INNER JOIN cat_Indicador USING (nim_id,ind_id)
                        INNER JOIN cat_ProductoIntermedio USING (pdi_id)
                    WHERE nim_id = 4
                        ' . $wh . '
                    ORDER BY pdf_id,pdi_id,ind_id;
                    ';
                break;
        }
        $this->setSql($sql);
        $this->setParams($prms);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 20;
        $nav->idTabla = 'tbl_Vin';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }

        switch ($this->prm->nivel) {
            case 1:
                $this->mixScript('
                    $(".detalle").click(function() {
                        var texto = "HABILITAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var pmi_id =$(this).attr("pmi_id");
                        var vpm_id =$(this).attr("vpm_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var clase= "btn-outline-success";
                        var titulo= "Habilitar";
                        if(status==1){
                            texto= "DESHABILITAR LA VINCULACION";
                            clase= "btn-outline-danger";
                            titulo= "Deshabilitar";
                        }

                        alertify.confirm(
                            "HABILITAR Y DESHABILITAR VINCULACION A NIVEL 1",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '", {
                                    metodo:"DeshabilitarVinculacion",
                                    pmi_id:pmi_id,
                                    vpm_id:vpm_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    status: status,
                                    ' . ((isset($this->prm->prg_id) && $this->prm->prg_id != '') ? 'prg_id:' . $this->prm->prg_id . ', ' : '') . '
                                    nivel:1
                                    }
                                );
                            },
                            function(){
                                console.log("boton cancelar nivel 1");
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-primary");
                            btnAceptar.classList.add("btn-success");
                        }
                    });

                    $(".deleteVinculacion").click(function() {
                    
                        var texto = "ELIMINAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var pmi_id =$(this).attr("pmi_id");
                        var vpm_id =$(this).attr("vpm_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var clase= "btn--outline-success";
                        var titulo= "ELIMINAR";
                        alertify.confirm(
                            "ELIMINA VINCULACION",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"eliminarVinculacion",
                                    pmi_id:pmi_id,
                                    vpm_id:vpm_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    status: status,
                                    ' . ((isset($this->prm->prg_id) && $this->prm->prg_id != '') ? 'prg_id:' . $this->prm->prg_id . ', ' : '') . '
                                    nivel:1
                                });
                            },
                            function(){
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        return false;
                    });
                ');
                break;
            case 2:
                $this->mixScript('
                    $(".detalle").click(function() {
                        var texto = "HABILITAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var pmi_id =$(this).attr("pmi_id");
                        var vpm_id =$(this).attr("vpm_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var clase= "btn-outline-success";
                        var titulo= "Habilitar";
                        if(status==1){
                            texto= "DESHABILITAR LA VINCULACION";
                            clase= "btn-outline-danger";
                            titulo= "Deshabilitar";
                        }

                        alertify.confirm(
                            "HABILITAR Y DESHABILITAR VINCULACION A NIVEL 2",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '", {
                                    metodo:"DeshabilitarVinculacion",
                                    pmi_id:pmi_id,
                                    vpm_id:vpm_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    status: status,
                                    ' . ((isset($this->prm->prg_id) && $this->prm->prg_id != '') ? 'prg_id:' . $this->prm->prg_id . ', ' : '') . '
                                    nivel:2
                                    }
                                );
                            },
                            function(){
                                console.log("boton cancelar nivel 2");
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-primary");
                            btnAceptar.classList.add("btn-success");
                        }
                        return false;
                    });

                    $(".deleteVinculacion").click(function() {
                    
                        var texto = "ELIMINAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var pmi_id =$(this).attr("pmi_id");
                        var vpm_id =$(this).attr("vpm_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var clase= "btn--outline-success";
                        var titulo= "ELIMINAR";
                        alertify.confirm(
                            "ELIMINA VINCULACION",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"eliminarVinculacion",
                                    pmi_id:pmi_id,
                                    vpm_id:vpm_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    status: status,
                                    ' . ((isset($this->prm->prg_id) && $this->prm->prg_id != '') ? 'prg_id:' . $this->prm->prg_id . ', ' : '') . '
                                    nivel:2
                                });
                            },
                            function(){
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        return false;
                    });
                ');

                break;
            case 3:
                $this->mixScript('
                    $(".detalle").click(function() {
                        var texto = "HABILITAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var epfi_id =$(this).attr("epfi_id");
                        var pdf_id =$(this).attr("pdf_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var clase= "btn-outline-success";
                        var titulo= "Habilitar";
                        if(status==1){
                            texto= "DESHABILITAR LA VINCULACION";
                            clase= "btn-outline-danger";
                            titulo= "Deshabilitar";
                        }

                        alertify.confirm(
                            "HABILITAR Y DESHABILITAR VINCULACION A NIVEL 3",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"DeshabilitarVinculacion",
                                    epfi_id:epfi_id,
                                    pdf_id:pdf_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    status: status,
                                    ' . ((isset($this->prm->producto) && $this->prm->producto != '') ? 'producto:' . $this->prm->producto . ', ' : '') . '
                                    nivel:3
                                });
                            },
                            function(){
                                console.log("boton cancelar nivel 3");
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-primary");
                            btnAceptar.classList.add("btn-success");
                        }
                        return false;
                    });

                    $(".deleteVinculacion").click(function() {
                    
                        var texto = "ELIMINAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var epfi_id =$(this).attr("epfi_id");
                        var pdf_id =$(this).attr("pdf_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var clase= "btn--outline-success";
                        var titulo= "ELIMINAR";
                        alertify.confirm(
                            "ELIMINA VINCULACION",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"eliminarVinculacion",
                                    epfi_id:epfi_id,
                                    pdf_id:pdf_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    status: status,
                                    ' . ((isset($this->prm->producto) && $this->prm->producto != '') ? 'producto:' . $this->prm->producto . ', ' : '') . '
                                    nivel:3
                                });
                            },
                            function(){
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        return false;
                    });
                ');

                break;
            case 4:
                $this->mixScript('
                    $(".detalle").click(function() {
                        var texto = "HABILITAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var ipi_id =$(this).attr("ipi_id");
                        var pdf_id =$(this).attr("pdf_id");
                        var pdi_id =$(this).attr("pdi_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var pii_id =$(this).attr("pii_id");
                        var clase= "btn-outline-success";
                        var titulo= "Habilitar";
                        if(status==1){
                            texto= "DESHABILITAR LA VINCULACION";
                            clase= "btn-outline-danger";
                            titulo= "Deshabilitar";
                        }

                        alertify.confirm(
                            "HABILITAR Y DESHABILITAR VINCULACION A NIVEL 4",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"DeshabilitarVinculacion",
                                    ipi_id:ipi_id,
                                    pdf_id:pdf_id,
                                    pdi_id:pdi_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    pii_id:pii_id,
                                    status: status,
                                    ' . ((isset($this->prm->producto) && $this->prm->producto != '') ? 'producto:' . $this->prm->producto . ', ' : '') . '
                                    ' . ((isset($this->prm->intermedio) && $this->prm->intermedio != '') ? 'intermedio:' . $this->prm->intermedio . ', ' : '') . '
                                    nivel:4
                                });
                            },
                            function(){
                                console.log("boton cancelar nivel 4");
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-primary");
                            btnAceptar.classList.add("btn-success");
                        }
                        return false;
                    });

                    $(".deleteVinculacion").click(function() {

                        var texto = "ELIMINAR LA VINCULACION ";
                        var status =$(this).attr("status");
                        var ipi_id =$(this).attr("ipi_id");
                        var pdf_id =$(this).attr("pdf_id");
                        var pdi_id =$(this).attr("pdi_id");
                        var nim_id =$(this).attr("nim_id");
                        var ind_id =$(this).attr("ind_id");
                        var pii_id =$(this).attr("pii_id");
                        var clase= "btn--outline-success";
                        var titulo= "ELIMINAR";
                        alertify.confirm(
                            "ELIMINA VINCULACION",
                            "¿CONFIRMA QUE DESEA  " + texto +"?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"eliminarVinculacion",
                                    ipi_id:ipi_id,
                                    pdf_id:pdf_id,
                                    pdi_id:pdi_id,
                                    nim_id:nim_id,
                                    ind_id:ind_id,
                                    pii_id:pii_id,
                                    status: status,
                                    ' . ((isset($this->prm->producto) && $this->prm->producto != '') ? 'producto:' . $this->prm->producto . ', ' : '') . '
                                    ' . ((isset($this->prm->intermedio) && $this->prm->intermedio != '') ? 'intermedio:' . $this->prm->intermedio . ', ' : '') . '
                                    nivel:4
                                });
                            },
                            function(){
                            }
                        ).set("labels", {
                            ok: titulo, cancel: "Cancelar"
                        });
                        return false;
                    });
                ');
                break;
        }
    }
    // 20-05-2019 fin  -- metodos en desuso -- Comprobar, la función si es llamada
    public function DeshabilitarVinculacion()
    {
        $this->prm->nivel = (isset($this->prm->nivel)) ? $this->prm->nivel : '';
        $this->prm->prg_id = isset($this->prm->prg_id) ? $this->prm->prg_id : '';
        $this->prm->producto = isset($this->prm->producto) ? $this->prm->producto : '';
        $this->prm->intermedio = isset($this->prm->intermedio) ? $this->prm->intermedio : '';
        $this->prm->status = isset($this->prm->status) ? $this->prm->status : '';

        $ip = $this->usrIp;
        $user = $this->usrId;
        $sts_id = '';
        $observacion = '';
        $mensaje = '';
        $trim_sts = '';
        $hoy = date('Y-m-d H:i:s');
        $observacion2 = '';

        switch ($this->prm->status) {
            case 1:
                $observacion2 = 'Se inhabilito el indicador';
                $sts_id = 2;
                $observacion = 'Se inhabilito la Vinculación';
                $mensaje = 'inhabilitado ';
                break;
            case 2:
                $observacion2 = 'Se habilito el indicador';
                $sts_id = 1;
                $observacion = 'Se habilito la Vinculación';
                $mensaje = 'habilitado ';
                break;
        }

        if ($hoy <= APP_EJERCICIO_ANTERIOR . '-12-31 23:59:59') {
            $trim_sts = 'sts_id = ' . $sts_id . ',trim_0 = ' . $sts_id . ', trim_1 =' . $sts_id . ' , trim_2 =' . $sts_id . ', trim_3 =' . $sts_id . ', trim_4 =' . $sts_id;
            $trims = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = $sts_id . ','. $sts_id . ',0,0,0,0';
            $tri_id = 0;
            $observacion2 .= ' en MIR Inicial';
        } elseif ($hoy >= APP_EJERCICIO . '-01-01 00:00:00' && $hoy <= APP_EJERCICIO . '-03-31 23:59:59') {
            $trim_sts = 'sts_id = ' . $sts_id . ', trim_1 = ' . $sts_id . ' , trim_2 =' . $sts_id . ', trim_3 =' . $sts_id . ', trim_4 =' . $sts_id;
            $trims = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = $sts_id . ', 0,' . $sts_id . ',0,0,0';
            $tri_id = 1;
            $observacion2 .= ' en MIR trimestre 1';
        } elseif ($hoy >= APP_EJERCICIO . '-04-01 00:00:00' && $hoy <= APP_EJERCICIO . '-07-30 23:59:59') {
            $trim_sts = 'sts_id = ' . $sts_id . ', trim_2 = ' . $sts_id . ', trim_3 =' . $sts_id . ', trim_4 =' . $sts_id;
            $trims = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = $sts_id .', 0, 0,' . $sts_id . ',0,0';
            $tri_id = 2;
            $observacion2 .= ' en MIR trimestre 2';
        } elseif ($hoy >= APP_EJERCICIO . '-07-01 00:00:00' && $hoy <= APP_EJERCICIO . '-09-30 23:59:59') {
            $trim_sts = 'sts_id = ' . $sts_id . ', trim_3 = ' . $sts_id . ', trim_4 =' . $sts_id;
            $trims = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = $sts_id .', 0, 0, 0,' . $sts_id . ',0';
            $tri_id = 3;
            $observacion2 .= ' en MIR trimestre 3';
        } elseif ($hoy >= APP_EJERCICIO . '-10-01 00:00:00' && $hoy <= APP_EJERCICIO . '-12-31 23:59:59') {
            $trim_sts = 'sts_id = ' . $sts_id . ', trim_4 = ' . $sts_id;
            $trims = 'sts_id, trim_0, trim_1, trim_2, trim_3, trim_4';
            $sts = $sts_id .', 0, 0, 0, 0,' . $sts_id;
            $tri_id = 4;
            $observacion2 .= ' en MIR trimestre 4';
        }

        $this->conectar();

        switch ($this->prm->nivel) {
            case 1:
                $observacion2 .= ' nivel fin';
                $this->setSql('
                    UPDATE vin_ProgramaMirIndicador
                    SET ' . $trim_sts . ', usr_id= ' . $user . ', usr_fecha = now()
                    WHERE pmi_id = ?
                        AND nim_id = ?;
                ');
                $prms = [$this->prm->pmi_id, $this->prm->nivel];
                $this->consultar($prms);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ProgramaMirIndicador (bit_observacion, pmi_id, pmi_orden, vpm_id, nim_id, ind_id, ' . $trims . ', usr_ip, usr_id)
                    VALUES( ?, ?, 0, ?, ?, ?, ' . $sts . ', INET_ATON(?), ?)
                ');
                $prms = [$observacion2, $this->prm->pmi_id, $this->prm->vpm_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                $this->consultar($prms);

                break;
            case 2:
                $observacion2 .= ' nivel propósito';
                $this->setSql('
                    UPDATE  vin_ProgramaMirIndicador
                    SET ' . $trim_sts . ', usr_id= ' . $user . ', usr_fecha = now()
                    WHERE pmi_id = ?
                        AND nim_id = ?;
                ');
                $prms = [$this->prm->pmi_id, $this->prm->nivel];
                $this->consultar($prms);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ProgramaMirIndicador (bit_observacion, pmi_id, pmi_orden, vpm_id, nim_id, ind_id, ' . $trims . ', usr_ip, usr_id)
                    VALUES(?, ?, 0, ?, ?, ?, ' . $sts . ', INET_ATON(?), ?)
                ');
                $prms = [$observacion2, $this->prm->pmi_id, $this->prm->vpm_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                $this->consultar($prms);
                break;
            case 3:
                $this->setSql('
                    UPDATE  enc_ProductoFinalIndicador
                    SET ' . $trim_sts . ', usr_id= ' . $user . ', usr_fecha = now()
                    WHERE epfi_id = ?;
                ');
                $prms = [$this->prm->epfi_id];
                $this->consultar($prms);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ProductoFinalIndicador(bit_observacion, epfi_id, epfi_orden, pdf_id, nim_id, ind_id, ' . $trims . ', usr_ip, usr_id)
                    VALUES(?, ?, 0, ?, ?, ?, ' . $sts . ', INET_ATON(?), ?)
                ');
                $prms = [$observacion, $this->prm->epfi_id, $this->prm->pdf_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                $this->consultar($prms);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ActualizaIndicadorComponente(bit_observacion, epfi_id, pdf_id, tri_id, ind_id, usr_ip, usr_id)
                    VALUES("' . $observacion2 . '", ' . $this->prm->epfi_id . ', ' . $this->prm->pdf_id . ', ' . $tri_id . ', ' . $this->prm->ind_id . ', INET_ATON("' . $ip . '"), ' . $user . ')
                ');
                $this->consultar();
                break;
            case 4:
                $this->setSql('
                    UPDATE  vin_ProductoIntermedioIndicador
                    SET ' . $trim_sts . ', usr_id= ' . $user . ', usr_fecha = now()
                    WHERE  pii_id = ?;
                ');
                $prms = [$this->prm->pii_id];
                $this->consultar($prms);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ProductoIntermedioIndicador(bit_observacion, pii_id, pdf_id, pdi_id, nim_id, ind_id, ' . $trims . ', pii_orden, usr_ip, usr_id)
                    VALUES(?, ?, ?, ?, ?, ?, ' . $sts . ', 0, INET_ATON(?), ?)
                ');
                $prms = [$observacion, $this->prm->pii_id, $this->prm->pdf_id, $this->prm->pdi_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                $this->consultar($prms);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ActualizaIndicadorActividad(bit_observacion, pii_id, pdf_id, pdi_id, tri_id, ind_id, usr_ip, usr_id)
                    VALUES("' . $observacion2 . '", ' . $this->prm->pii_id . ', ' . $this->prm->pdf_id . ', ' . $this->prm->pdi_id . ', ' . $tri_id . ', ' . $this->prm->ind_id . ', INET_ATON("' . $ip . '"), ' . $user . ')
                ');
                $this->consultar();
                break;
        }
        $this->desconectar();

        $this->mixScript(' notify("El Indicador ' . $this->prm->ind_id . ' ha sido ' . $mensaje . '  correctamente ","success"); ');
        $this->llenaListaIndicadores();
        $this->llenaIndicadoresVincualdos();
    }

    private function ListaPrograma()
    {

        $this->setContenedor('prog');
        $this->prm->nivel = (isset($this->prm->nivel)) ? $this->prm->nivel : '';
        if ($this->prm->nivel != '') {
            $programa = new stdClass();
            $programa->id = 'programa';
            $programa->class = 'limpiar';
            $programa->container = 'body';
            $programa->sql = '
                SELECT
                    prgs_id AS prg_id,
                    prgs_nombre AS prg_nombre
                FROM cat_Programas
                WHERE prgs_id between 100 AND 899 ; ';
            $programa->label = 'Programa';
            $programa->search = true;
            $programa->change = '
                $("#intermedios").hide();
                $("#btnAgregar,#btnQuitar").remove();

                if($(this).val() != ""){
                    comunica("' . $this->pantalla . '",{
                        metodo:"ListaIndicadores",
                        nivel:"' . $this->prm->nivel . '",
                        prg_id:$("#programa").val()
                    });
                }
                if($(this).val() != ""){
                    comunica("' . $this->pantalla . '",{
                        metodo:"IndicadoresVincualdos",
                        nivel:"' . $this->prm->nivel . '",
                        prg_id:$("#programa").val()
                    });
                }
            ';
        } else {
            $this->mixScript(' notify("Elige el Nivel","error"); ');
        }

        $this->mixHtml($this->ponCombo($programa));
    }

    private function ProductoFinal()
    {
        $this->setContenedor('prog');
        $producto = new stdClass();
        $producto->id = 'producto';
        $producto->class = 'limpiar';
        $producto->sql = 'SELECT pdf_id,pdf_nombre from cat_ProductoFinal WHERE pdf_id!=0 AND pdf_final=1 ;';
        $producto->label = 'Producto Final';
        $producto->search = true;
        $producto->change = '
            $("#intermedios").hide();
            $("#btnAgregar,#btnQuitar").remove();
            if($(this).val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo:"ListaIndicadores",
                    nivel:"' . $this->prm->nivel . '",
                    producto:$("#producto").val()
                });
            }
            if($(this).val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo:"IndicadoresVincualdos",
                    nivel:"' . $this->prm->nivel . '",
                    producto:$("#producto").val()
                });
            }
        ';

        if ($this->prm->nivel == 4) {
            $producto->change = '
                $("#intermedios").show();
                if($(this).val() != ""){
                    comunica("' . $this->pantalla . '",{
                    metodo:"ListaIntermedios",
                        producto:$("#producto").val(),
                        nivel:"' . $this->prm->nivel . '"
                    });
                }
            ';
        }

        $this->mixHtml($this->ponCombo($producto));
    }

    private function ListaIntermedios()
    {
        $this->setContenedor('intermedios');
        $intermedio = new stdClass();
        $intermedio->id = 'intermedio';
        $intermedio->class = 'limpiar';
        $intermedio->sql = '
            SELECT pdi_id,pdi_nombre
            FROM cat_ProductoIntermedio
            INNER JOIN vin_ProductoFinalProductoIntermedio USING (pdi_id)
            WHERE pdf_id = ?
            AND vin_ProductoFinalProductoIntermedio.sts_id = 1;
        ';
        $intermedio->params = [$this->prm->producto];
        $intermedio->label = 'Producto Intermedio';
        $intermedio->search = true;
        $intermedio->change = '
            $("#btnAgregar,#btnQuitar").remove();
            if($(this).val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo:"ListaIndicadores",
                    nivel:"' . $this->prm->nivel . '",
                    producto:"' . $this->prm->producto . '",
                    intermedio:$("#intermedio").val()
                });
            }
            if($(this).val() != ""){
                comunica("' . $this->pantalla . '",{
                    metodo:"IndicadoresVincualdos",
                    nivel:"' . $this->prm->nivel . '",
                    producto:"' . $this->prm->producto . '",
                    intermedio:$("#intermedio").val(),
                    pii_id:$("#pii_id").val()
                });
            }
        ';

        $this->mixHtml($this->ponCombo($intermedio));
    }

    public function ListaIntermediosDisponibles()
    {
        $this->setContenedor('divIntermedios');
        $this->prm->pdf_id = (isset($this->prm->pdf_id)) ? $this->prm->pdf_id : '';

        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = ['pdi_id'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'pdf_sel';
        $tabla->casoSeleccionable->valor = '1';

        $tabla->casoSeleccionable->ands = [];
        $and = new stdClass();
        $and->campo = 'pdi_id';
        $and->operador = '!=';
        $and->valor = '0';
        $and->valorTipo = 'numero';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';

        $tabla->id = 'tbl_inter';
        $tabla->titulo = 'Lista de Productos Intermedio no Vinculados ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->pdf_id = $this->prm->pdf_id;
        $tabla->comunica->metodo = 'llenaIntermediosDisponibles';
        $tabla->numFilas = 20;
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Producto Intermedio';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->campoTxt = 'pdi_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pdi_plural';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml('
            <div class="input-group input-group-sm" >
                <input id="pi_buscar" name="pi_buscar" type="text" class="form-control" placeholder="BUSCAR POR PI/NOMBRE" >
                <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" onClick="filtraProductosIntermedios()"  type="button">Buscar</button>
                </div>
            </div>
        ');
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript('
            function filtraProductosIntermedios(){
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaIntermediosDisponibles",
                    pdf_id:"' . $this->prm->pdf_id . '",
                    busqueda:$("#pi_buscar").val()
                });
            }
        ');

        $btnAgregar = new stdClass();
        $btnAgregar->id = 'btnAgregar';
        $btnAgregar->icon = 'fa-solid fa-chevron-left';
        $btnAgregar->color = 'verde';
        $btnAgregar->value = 'Agregar';
        $btnAgregar->click = '
            if($("#pdf_id").val()!="" && $("#pdf_id").val()!="NULL"){
                if(seleccionados = $("#tbl_inter").tabla("getFilaSeleccion")){
                    comunica("' . $this->pantalla . '",{
                        metodo:"VincularIntermedioFinal",
                        datos:seleccionados,
                        pdf_id:"' . $this->prm->pdf_id . '"
                    });
                }
            }else{
                alert("Elige un Producto Final para realizar la Vinculación ");
                return false;
            }
        ';

        $btnAgregar = $this->ponBoton($btnAgregar);
        $this->mixHtml('' . $btnAgregar . ' ');
        $this->mixScript(' $("#botons").append($("<br/>")).append($("#btnAgregar")).append($("<br/>")); ');

        $this->llenaIntermediosDisponibles();
    }

    private function llenaIntermediosDisponibles()
    {
        $this->prm->pdf_id = isset($this->prm->pdf_id) ? $this->prm->pdf_id : '';
        $this->prm->busqueda = isset($this->prm->busqueda) ? $this->prm->busqueda : '';

        $this->conectar();
        $sql = '
            SELECT pdi_id,pdi_plural,pdf_id,1 pdf_sel
            FROM  cat_ProductoIntermedio
            LEFT JOIN (
                SELECT pdi_id,pdf_id
                FROM vin_ProductoFinalProductoIntermedio
                WHERE pdf_id = ?
            )x using (pdi_id)
            WHERE isnull(pdf_id) AND sts_id=1 ;
        ';
        $prms = [$this->prm->pdf_id];
        if (!empty($this->prm->busqueda)) {
            $sql .= 'AND (pdi_id = ? OR pdi_plural like ?)';
            array_push($prms, $this->prm->busqueda);
            array_push($prms, '%' . $this->prm->busqueda . '%');
        }
        $this->setSql($sql);
        $this->setParams($prms);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 20;
        $nav->idTabla = 'tbl_inter';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }
    }

    public function VincularIntermedioFinal()
    {
        $this->prm->pdf_id = (isset($this->prm->pdf_id)) ? $this->prm->pdf_id : '';
        $ip = $this->usrIp;
        $user = $this->usrId;

        if (count($this->prm->datos) > 0 && $this->prm->datos != '') {
            foreach ($this->prm->datos as $key => $val) {

                /* Validamos que no esté vinculado  */
                $con = new AccesoDatos();
                $con->conectar();
                $con->setSql('
                    SELECT pdf_id,pdi_id
                    FROM vin_ProductoFinalProductoIntermedio
                    WHERE pdi_id = ? AND pdf_id = ?
                ');
                $con->consultar([$val->pdi_id, $this->prm->pdf_id]);
                $row1 = $con->extraer();
                if (isset($row1['ind_id']) && $row1['ind_id'] != null) {
                    $this->mixScript(' notify("El Producto Intermedio ' . $val->pdi_id . ' ya está vinculado con el Producto Final ' . $this->prm->pdf_id . ' ","error"); ');
                    return false;
                }

                $this->conectar();
                $this->setSql('
                    INSERT INTO vin_ProductoFinalProductoIntermedio(pdf_id, pdi_id, sts_id, usr_id)
                    VALUES(?, ?, 1, ?)
                ');
                $this->consultar([$this->prm->pdf_id, $val->pdi_id, $user]);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ProductoFinalProductoIntermedio(bit_observacion, pdf_id, pdi_id, sts_id, usr_id, usr_ip)
                    VALUES("Se agregó la vinculación Producto Intermedio - Producto Final ", ?, ?, 1 , ?, INET_ATON(?))
                ');
                $this->consultar([$this->prm->pdf_id, $val->pdi_id, $user, $ip]);
                $this->desconectar();
            }
            $this->mixScript(' notify("El Producto Intermedio ' . $val->pdi_id . ' ha sido vinculado con el Producto Final ' . $this->prm->pdf_id . '  correctamente ","success"); ');
            $this->llenaIntermediosDisponibles();
            $this->llenaIntermediosVinculados();
        }
    }

    public function IntermediosVinculados()
    {

        $this->setContenedor('divFinales');
        $this->prm->pdf_id = isset($this->prm->pdf_id) ? $this->prm->pdf_id : '';
        $this->prm->pdi_id = isset($this->prm->pdi_id) ? $this->prm->pdi_id : '';

        $tabla = new stdClass();

        $tabla->id = 'tbl_Union';
        $tabla->titulo = 'Lista de Productos Intermedios Vinculados ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->pdf_id = $this->prm->pdf_id;
        $tabla->comunica->pdi_id = $this->prm->pdi_id;
        $tabla->comunica->metodo = 'llenaIntermediosVinculados';
        $tabla->numFilas = 20;
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Producto Final';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Producto Intermedio';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ON/OFF';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Eliminar';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->campoTxt = 'pdf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pdi_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pdi_plural';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'sts_id';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'verde';
        $td->casos->verdadero->icono = 'fa-solid fa-power-off';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->pdf_id = 'pdf_id';
        $td->casos->verdadero->attr->pdi_id = 'pdi_id';
        $td->casos->verdadero->attr->status = '1';
        $td->casos->verdadero->class = 'detalle';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->color = 'rojo';
        $td->casos->falso->icono = 'fa-solid fa-power-off';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->pdf_id = 'pdf_id';
        $td->casos->falso->attr->pdi_id = 'pdi_id';
        $td->casos->falso->attr->status = '2';
        $td->casos->falso->class = 'detalle';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = "sts_id";
        $td->casos->valor = "1";
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = "boton";
        $td->casos->verdadero->color = "rojo";
        $td->casos->verdadero->icono = "trash-can";
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->pdf_id = "pdf_id";
        $td->casos->verdadero->attr->pdi_id = "pdi_id";
        $td->casos->verdadero->attr->nivel = '5';
        $td->casos->verdadero->class = "deleteVinculacion";
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaIntermediosVinculados();
    }

    private function llenaIntermediosVinculados()
    {
        $this->prm->pdf_id = isset($this->prm->pdf_id) ? $this->prm->pdf_id : '';
        $this->prm->pdi_id = isset($this->prm->pdi_id) ? $this->prm->pdi_id : '';

        $this->conectar();
        $sql = '
            SELECT
                pdf_id,pdi_id,pdi_plural,
                if(vin_ProductoFinalProductoIntermedio.sts_id=1,1,0) AS sts_id
            FROM vin_ProductoFinalProductoIntermedio
                INNER JOIN cat_ProductoIntermedio USING (pdi_id)
                LEFT JOIN vin_ProductoIntermedioIndicador USING (pdf_id,pdi_id)
                LEFT JOIN cat_MetaActividad USING(pii_id)
            WHERE pdf_id = ?
            GROUP BY pdf_id,pdi_id
            ORDER BY pdf_id,pdi_id;
        ';
        $this->setSql($sql);
        $this->setParams([$this->prm->pdf_id]);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 20;
        $nav->idTabla = 'tbl_Union';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }

        $this->mixScript('
            $(".detalle").click(function() {
                var texto = "HABILITAR LA VINCULACION ";
                var status =$(this).attr("status");
                var pdf_id =$(this).attr("pdf_id");
                var pdi_id =$(this).attr("pdi_id");
                var clase= "btn--outline-success";
                var titulo= "Habilitar";
                if(status==1){
                    texto= "DESHABILITAR LA VINCULACION";
                    clase= "btn-outline-danger";
                    titulo= "Deshabilitar";
                }
                alertify.confirm(
                    "HABILITAR Y DESHABILITAR VINCULACION INTERMEDIOS VINCULADOS",
                    "¿CONFIRMA QUE DESEA  " + texto +"?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"DeshabilitarHabilitarIntermedio",
                            pdf_id:pdf_id,
                            pdi_id:pdi_id,
                            status:status
                        });
                    },
                    function(){
                        console.log("boton cancelar intermedios vinculados");
                    }
                ).set("labels", {
                    ok: titulo, cancel: "Cancelar"
                });
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-primary");
                    btnAceptar.classList.add("btn-success");
                }
                return false;
            });

            $(".deleteVinculacion").click(function() {
                var texto = "ELIMINAR LA VINCULACIÓN ";
                var status =$(this).attr("status");
                var pdf_id =$(this).attr("pdf_id");
                var pdi_id =$(this).attr("pdi_id");
                var clase= "btn--outline-success";
                var titulo= "ELIMINAR";
                alertify.confirm(
                    "ELIMINAR VINCULACIÓN PRODUCTO FINAL/INTERMEDIO",
                    "¿CONFIRMA QUE DESEA  " + texto +"?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"eliminarVinculacion",
                            pdf_id:pdf_id,
                            pdi_id:pdi_id,
                            status:status,
                            nivel:5
                        });
                    },
                    function(){
                    }
                ).set("labels", {
                    ok: titulo, cancel: "Cancelar"
                });
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-primary");
                    btnAceptar.classList.add("btn-success");
                }
                return false;
            });
        ');
    }

    public function DesVincularIntermedio()
    {
        $this->prm->pdf_id = (isset($this->prm->pdf_id)) ? $this->prm->pdf_id : '';
        $ip = $this->usrIp;
        $user = $this->usrId;

        if (count($this->prm->datos) > 0 && $this->prm->datos != '') {
            foreach ($this->prm->datos as $key => $val) {
                $this->conectar();
                $this->setSql('
                    DELETE FROM vin_ProductoFinalProductoIntermedio
                    WHERE pdf_id = ? AND pdi_id = ?;
                ');
                $this->consultar([$this->prm->pdf_id, $val->pdi_id]);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ProductoFinalProductoIntermedio(bit_observacion,pdf_id,pdi_id, sts_id, usr_id,usr_ip)
                    VALUES("Se eliminó la vinculación Producto Intermedio - Producto Final", ?, ?, 1, ?,INET_ATON(?))
                ');
                $prms = [$this->prm->pdf_id, $val->pdi_id, $user, $ip];
                $this->consultar($prms);
                $this->desconectar();
            }
            $this->mixScript(' notify("El Producto Intermedio ' . $val->pdi_id . ' ha sido desvinculado  correctamente ","success"); ');
            $this->llenaIntermediosDisponibles();
            $this->llenaIntermediosVinculados();
        }
    }

    public function eliminarVinculacion() {

        $observacion = '';
        $ip = $this->usrIp;
        $user = $this->usrId;
        switch ($this->prm->nivel) {
            case '1':
                $this->conectar();
                $this->setSql('
                    SELECT * 
                    FROM aux_HistoricoModificacionMetaFin
                    WHERE pmi_id = ?
                ');
                $this->consultar([$this->prm->pmi_id]);
                if ($this->getNumeroRegistros() > 0) {
                    # code...
                    $this->mixScript('
                        notify("La vinculación no puede ser eliminada ya que cuenta con metas registradas.","error");
                    ');
                } else {
                    $observacion .= 'Vinculación eliminada nivel fin';
                    $this->setSql('
                        DELETE FROM vin_ProgramaMirIndicador
                        WHERE pmi_id = ?
                            AND nim_id = ?;
                    ');
                    $prms = [$this->prm->pmi_id, $this->prm->nivel];
                    $this->consultar($prms);

                    $this->setSql('
                        INSERT INTO ' . BD_BITACORA . '.bit_ProgramaMirIndicador (bit_observacion, pmi_id, pmi_orden, vpm_id, nim_id, ind_id, sts_id, trim_0, trim_1, trim_2, trim_3, trim_4, usr_ip, usr_id)
                        VALUES( ?, ?, 0, ?, ?, ?, 0, 0, 0, 0, 0, 0, INET_ATON(?), ?)
                    ');
                    $prms = [$observacion, $this->prm->pmi_id, $this->prm->vpm_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                    $this->consultar($prms);

                        $this->mixScript('
                            notify("La vinculación fue eliminada.","success");
                        ');
                    }
                    $this->desconectar();
                    $this->llenaListaIndicadores();
                    $this->llenaIndicadoresVincualdos();
                break;
            case '2':
                $this->conectar();
                $this->setSql('
                    SELECT * 
                    FROM cat_MetaProposito m
                    LEFT JOIN aux_HistoricoModificacionMetaProposito USING(pmi_id)
                    WHERE pmi_id = ?
                ');
                $this->consultar([$this->prm->pmi_id]);
                if ($this->getNumeroRegistros() > 0) {
                    # code...
                    $this->mixScript('
                        notify("La vinculación no puede ser eliminada ya que cuenta con metas registradas.","error");
                    ');
                } else {
                    $observacion .= 'Vinculación eliminada nivel propósito';
                    $this->setSql('
                        DELETE FROM vin_ProgramaMirIndicador
                        WHERE pmi_id = ?
                            AND nim_id = ?;
                    ');
                    $prms = [$this->prm->pmi_id, $this->prm->nivel];
                    $this->consultar($prms);

                    $this->setSql('
                        INSERT INTO ' . BD_BITACORA . '.bit_ProgramaMirIndicador (bit_observacion, pmi_id, pmi_orden, vpm_id, nim_id, ind_id, sts_id, trim_0, trim_1, trim_2, trim_3, trim_4, usr_ip, usr_id)
                        VALUES( ?, ?, 0, ?, ?, ?, 0, 0, 0, 0, 0, 0, INET_ATON(?), ?)
                    ');
                    $prms = [$observacion, $this->prm->pmi_id, $this->prm->vpm_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                    $this->consultar($prms);
                    $this->mixScript('
                        notify("La vinculación fue eliminada.","success");
                    ');
                }
                $this->desconectar();
                $this->llenaListaIndicadores();
                $this->llenaIndicadoresVincualdos();
                break;
            case '3':
                $this->conectar();
                $this->setSql('
                    SELECT * 
                    FROM aux_HistoricoModificacionMetaComponente
                    WHERE epfi_id = ?
                ');
                $this->consultar([$this->prm->epfi_id]);
                if ($this->getNumeroRegistros() > 0) {
                    # code...
                    $this->mixScript('
                        notify("La vinculación no puede ser eliminada ya que cuenta con vinculaciones/metas activas.","error");
                    ');
                } else {
                    $this->conectar();
                    $this->setSql('
                        SELECT pdf_id, v.ind_id, v.pii_id, v.pdi_id, e.ind_id as vinculado
                        FROM  vin_ProductoIntermedioIndicador v 
                        INNER JOIN aux_HistoricoMetaActividadMir h USING(pii_id)
                        INNER JOIN enc_ProductoFinalIndicador e USING(pdf_id)
                        WHERE pdf_id = ?
                        GROUP BY v.ind_id;
                    ');
                    $this->consultar([$this->prm->pdf_id]);
                    while ($row = $this->extraer()) {
                        # code...
                        if ($row['vinculado'] == $this->prm->ind_id) {
                            # code...
                            $this->mixScript('
                                notify("La vinculación no puede ser eliminada ya que cuenta con vinculaciones/metas activas.","error");
                            ');
                        } else {
                            $observacion .= 'Vinculación eliminada nivel componente';
                            $this->conectar();
                            $this->setSql('
                                DELETE FROM enc_ProductoFinalIndicador
                                WHERE epfi_id = ?;
                            ');
                            $prms = [$this->prm->epfi_id];
                            $this->consultar($prms);
                            
                            $this->setSql('
                                INSERT INTO ' . BD_BITACORA . '.bit_ProductoFinalIndicador(bit_observacion, epfi_id, epfi_orden, pdf_id, nim_id, ind_id, sts_id, trim_0, trim_1, trim_2, trim_3, trim_4, usr_ip, usr_id)
                                VALUES(?, ?, 0, ?, ?, ?, 0,0,0,0,0,0, INET_ATON(?), ?)
                            ');
                            $prms = [$observacion, $this->prm->epfi_id, $this->prm->pdf_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                            $this->consultar($prms);
                            $this->mixScript('
                                notify("La vinculación fue eliminada.","success");
                            ');
                        }
                    }
                    $this->desconectar();
                }
                $this->llenaListaIndicadores();
                $this->llenaIndicadoresVincualdos();
                break;
            case '4':
                # code...
                $this->conectar();
                $this->setSql('
                    SELECT ' . BD_CTRL . ' .fn_VinculacionMir(?)
                ');
                $this->consultar([$this->prm->pii_id]);
                $registro = $this->extraer();
                $this->desconectar();
                if ($registro) {
                    # code...
                    $this->conectar();
                    $this->setSql('
                        SELECT * 
                        FROM aux_HistoricoMetaActividadMir
                        WHERE pii_id = ?
                    ');
                    $this->consultar([$this->prm->pii_id]);
                    if ($this->getNumeroRegistros() > 0) {
                        # code...
                        $this->mixScript('
                            notify("La vinculación no puede ser eliminada ya que cuenta con vinculaciones/metas activas.","error");
                        ');
                    } else {
                        $observacion .= 'Vinculación eliminada nivel componente';
                        $this->conectar();
                        $this->setSql('
                            DELETE FROM  vin_ProductoIntermedioIndicador
                            WHERE  pii_id = ?;
                        ');
                        $prms = [$this->prm->pii_id];
                        $this->consultar($prms);

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ProductoIntermedioIndicador(bit_observacion, pii_id, pdf_id, pdi_id, nim_id, ind_id, sts_id, trim_0, trim_1, trim_2, trim_3, trim_4, pii_orden, usr_ip, usr_id)
                            VALUES(?, ?, ?, ?, ?, ?, 0,0,0,0,0,0, 0, INET_ATON(?), ?)
                        ');
                        $prms = [$observacion, $this->prm->pii_id, $this->prm->pdf_id, $this->prm->pdi_id, $this->prm->nim_id, $this->prm->ind_id, $ip, $user];
                        $this->consultar($prms);
                        $this->mixScript('
                            notify("La vinculación fue eliminada.","success");
                        ');
                    }
                    $this->desconectar();
                } else {
                    $this->mixScript('
                        notify("La vinculación no puede ser eliminada ya que cuenta con vinculaciones/metas activas.","error");
                    ');
                }
                $this->llenaListaIndicadores();
                $this->llenaIndicadoresVincualdos();
                break;
            case '5':
                $this->conectar();
                    $this->setSql('
                        SELECT * 
                        FROM vin_ProductoFinalProductoIntermedio
                        INNER JOIN vin_ProductoIntermedioIndicador USING(pdf_id, pdi_id)
                        WHERE pdf_id = ? AND pdi_id = ?
                    ');
                    $this->consultar([$this->prm->pdf_id, $this->prm->pdi_id]);
                    if ($this->getNumeroRegistros() > 0) {
                        # code...
                        $this->mixScript('
                            notify("La vinculación no puede ser eliminada ya que cuenta con vinculaciones activas.","error");
                        ');
                    } else {
                        $observacion .= 'Vinculación eliminada nivel componente';
                        $this->conectar();
                        $this->setSql('
                            DELETE FROM vin_ProductoFinalProductoIntermedio
                            WHERE  pdf_id = ? AND pdi_id = ?;
                        ');
                        $prms = [$this->prm->pdf_id, $this->prm->pdi_id];
                        $this->consultar($prms);

                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_ProductoFinalProductoIntermedio(bit_observacion, pdf_id, pdi_id, sts_id, usr_id, usr_ip)
                            VALUES("Se agregó la vinculación Producto Intermedio - Producto Final ", ?, ?, 1 , ?, INET_ATON(?))
                        ');
                        $this->consultar([$this->prm->pdf_id, $this->prm->pdi_id, $user, $ip]);
                        $this->mixScript('
                            notify("La vinculación fue eliminada.","success");
                        ');
                    }
                    $this->llenaIntermediosDisponibles();
                    $this->llenaIntermediosVinculados();
                break;
        }
    }

    public function DeshabilitarHabilitarIntermedio()
    {
        $this->prm->pdf_id = (isset($this->prm->pdf_id)) ? $this->prm->pdf_id : '';
        $this->prm->pdi_id = (isset($this->prm->pdi_id)) ? $this->prm->pdi_id : '';
        $this->prm->status = isset($this->prm->status) ? $this->prm->status : '';
        $ip = $this->usrIp;
        $user = $this->usrId;

        $sts_id = '';
        $observacion = '';
        $mensaje = '';
        if ($this->prm->status == 1) {
            $sts_id = ' sts_id = 2 ';
            $observacion = ' Se inhabilito la Vinculación Producto Intermedio ';
            $mensaje = 'inhabilitado ';
        } else {
            $sts_id = ' sts_id = 1 ';
            $observacion = ' Se habilito la Vinculación Producto Intermedio ';
            $mensaje = 'habilitado ';
        }

        $this->conectar();
        $this->setSql('
            UPDATE  vin_ProductoFinalProductoIntermedio
            SET ' . $sts_id . '
            WHERE pdf_id = ? AND  pdi_id = ?;
        ');
        $this->consultar([$this->prm->pdf_id, $this->prm->pdi_id]);
        $this->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_ProductoFinalProductoIntermedio(bit_observacion,pdf_id,pdi_id, sts_id, usr_id,usr_ip)
            VALUES(?, ?, ?, ?, ?, INET_ATON(?))
        ');
        $prms = [$observacion, $this->prm->pdf_id, $this->prm->pdi_id, $this->prm->status, $user, $ip];
        $this->consultar($prms);
        $this->desconectar();

        $this->mixScript(' notify("El Producto Intermedio ' . $this->prm->pdi_id . ' ha sido ' . $mensaje . '  correctamente ","success"); ');
        $this->llenaIntermediosDisponibles();
        $this->llenaIntermediosVinculados();
    }

    public function ListaProductosFinales()
    {
        $this->setContenedor('idFinales');
        $this->prm->prgs_id = (isset($this->prm->prgs_id)) ? $this->prm->prgs_id : '';

        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = ['pdf_id'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'sel';
        $tabla->casoSeleccionable->valor = '1';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';

        $tabla->id = 'tbl_fin';
        $tabla->titulo = 'Lista de Productos Finales no Vinculados ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->prgs_id = $this->prm->prgs_id;
        $tabla->comunica->metodo = 'llenaListaProductosFinales';
        $tabla->numFilas = 20;
        $tr = [];

        $th = new stdClass();
        $th->texto = 'ASIGNAR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Producto Final';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'plus';
        $td->color = 'verde';
        $td->class = 'progs';
        $td->attr = new stdClass();
        $td->attr->pdf_id = 'pdf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pdf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pdf_plural';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaListaProductosFinales();
    }
    private function llenaListaProductosFinales()
    {

        $this->prm->prgs_id = isset($this->prm->prgs_id) ? $this->prm->prgs_id : '';

        $this->conectar();
        $sql = '
            SELECT pdf_id, pdf_plural, IF(prgs_id is null,1,0) AS sel
            FROM cat_ProductoFinal
            LEFT JOIN(
                SELECT pdf_id,if(ISNULL(pdf_id),null,prgs_id) prgs_id
                FROM cat_Subprogramas
            )yatiene USING(pdf_id)
            WHERE pdf_final = 1 AND ISNULL(prgs_id);
        ';
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 20;
        $nav->idTabla = 'tbl_fin';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }

        $this->mixScript('
            $(".progs").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"AgregarPrograma",
                    pdf_id:$(this).attr("pdf_id")
                });
                return false;
            });
        ');
    }
    private function AgregarPrograma()
    {
        $this->setContenedor('catPrograma');
        $this->prm->pdf_id = (isset($this->prm->pdf_id)) ? $this->prm->pdf_id : '';

        $prg_id = new stdClass();
        $prg_id->id = 'prg_id';
        $prg_id->class = 'limpiar';
        $prg_id->sql = '
            SELECT
                prgs_id,prgs_nombre
            FROM cat_Programas
                INNER JOIN cat_Subprogramas USING (prgs_id)
            WHERE sprs_id = 0
                AND pdf_id = 0;';
        $prg_id->label = 'Programas';
        $prg_id->search = true;
        $prg_id->enRow = 1;

        $modal = '';
        $modal = '
            <div class=" modal fade show " aria-hidden="true" tabindex="-1" role="dialog" style="display: block;" aria-modal="true" id="modalAgregarProductoFinal">
                <div class="modal-dialog" style="width: 45% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color:#9d2449;">
                            <h4 class="modal-title" id="myModalLabel">Productos Disponibles</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-outline-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="Mensaje">
                                <legend>Agregar producto Final al Programa</legend><br>
                                <div class="row col-md-12 col-xs-12">
                                    <fieldset  class="col-md-12 col-xs-12" style="color:#0431B4">
                                        ' . $this->ponCombo($prg_id) . '
                                    </fieldset>
                                </div>
                                <br>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger cerrar" data-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-outline-success vincular" >Agregar Vinculacion</button>
                                </div>
                            </form>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            </div>
        ';

        $this->mixHtml($modal);

        $this->mixScript('
            $("#modal").modal();
            $(".cerrar").click(function(){
                $("#modalAgregarProductoFinal").remove();
            });
            $(".vincular").click(function(){
                if($("#prg_id").val()!=""  &&  $("#prg_id").val()!="null" ){
                    var prgs_id= $("#prg_id").val();
                    var elim= confirm("Estas seguro de agregar el Producto Final ' . $this->prm->pdf_id . ' con el Programa" + prgs_id);
                    if(elim){
                        comunica("' . $this->pantalla . '",{
                            metodo:"VincularProductoSubprograma",
                            pdf_id:"' . $this->prm->pdf_id . '",
                            prgs_id: prgs_id
                        });

                        $("#modal").modal("hide");
                        return false;
                    }
                }else{
                    notify("Tienes que seleccionar un Programa","error");
                    return false;
                }
            });
        ');
    }
    public function PFSubProgramaVinculacion()
    {

        $this->setContenedor('idprogramas');
        $this->prm->prgs_id = isset($this->prm->prgs_id) ? $this->prm->prgs_id : '';

        $tabla = new stdClass();

        $tabla->id = 'tbl_Uni';
        $tabla->titulo = 'Lista de Productos Finales con Subprogramas Vinculados ';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->prgs_id = $this->prm->prgs_id;
        $tabla->comunica->metodo = 'llenaPFSubProgramaVinculacion';
        $tabla->numFilas = 20;
        $tr = [];

        $th = new stdClass();
        $th->texto = 'Programa';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Subprograma';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Producto Final';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->campoTxt = 'prgs_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prgs_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'sprs_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pdf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pdf_plural';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaPFSubProgramaVinculacion();
    }

    private function llenaPFSubProgramaVinculacion()
    {

        $this->prm->prgs_id = isset($this->prm->prgs_id) ? $this->prm->prgs_id : '';

        $this->conectar();
        $sql = '
            SELECT prgs_id,prgs_nombre,sprs_id,pdf_id,pdf_plural
            FROM  cat_Programas
            INNER JOIN cat_Subprogramas USING (prgs_id)
            INNER JOIN cat_ProductoFinal USING (pdf_id)
            WHERE sprs_id = 0
            AND pdf_id != 0;
        ';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 20;
        $nav->idTabla = 'tbl_Uni';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }
    }
    public function VincularProductoSubprograma()
    {

        $this->prm->prgs_id = (isset($this->prm->prgs_id)) ? $this->prm->prgs_id : '';
        $this->prm->pdf_id = (isset($this->prm->pdf_id)) ? $this->prm->pdf_id : '';

        $user = $this->usrId;

        if ($this->prm->prgs_id != '' && $this->prm->pdf_id != '') {

            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql('
                SELECT pdf_id
                FROM cat_Subprogramas
                WHERE sprs_id = 0
                AND prgs_id = ?
            ');
            $con->consultar([$this->prm->prgs_id]);
            $con->desconectar();
            $row1 = $con->extraer();

            if ($row1['pdf_id'] != 0) {
                $this->mixScript('
                    notify("El Producto Final ' . $this->prm->pdf_id . ' ya está vinculado con el Subprograma 0 del Programa ' . $this->prm->prgs_id . ' ","error");
                ');
                return false;
            }

            $this->conectar();
            $this->setSql('
                UPDATE cat_Subprogramas
                SET pdf_id = ?
                WHERE sprs_id = 0
                    AND prgs_id = ?
                    AND pdf_id = 0 ;
            ');
            $this->consultar([$this->prm->pdf_id, $this->prm->prgs_id]);
            $this->setSql(
                'SET @bit_observacion="Vinculacion Subprograma 0";'
            );
            $this->consultar();

            $this->setSql('
                INSERT INTO ' . BD_BITACORA . '.bit_Subprogramas(bit_observacion,prgs_id,sprs_id,esr_id,pdf_id,usr_id)
                VALUES("Vinculación Producto Final Subprograma 0 ", ?, 0, 1, ?, ?)
            ');
            $prms = [$this->prm->prgs_id, $this->prm->pdf_id, $user];
            $this->consultar($prms);
            $this->desconectar();

            $this->mixScript('
                notify("El Producto Final ' . $this->prm->pdf_id . ' ha sido vinculado con el Subprograma  0 del Programa ' . $this->prm->prgs_id . ' correctamente ","success");
            ');
            $this->llenaListaProductosFinales();
            $this->llenaPFSubProgramaVinculacion();
        }
    }
}
if (isset($_SESSION[_S]['prm']['M']['8']['1'])) {
    new ControladorVinculacionMir();
} else {
    WebResponse::sinPermisos();
}
