<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorFlujoEfectivo extends WebResponse
{
    private $gpo_id;
    private $ur_id;
    private $ue_id;

    public function __construct()
    {

        parent::__construct();

        $this->pantalla = 'scg/cuentaPublica/ControladorFlujoEfectivo.php';

        if (isset($this->prm->opcion)) {
            $_SESSION[_S]['reportes'] = $this->prm->opcion->sis_id . $this->prm->opcion->men_id . $this->prm->opcion->opc_id;
        }
        $this->gpo_id = ($_SESSION[_S]['nvj_id'] > 2) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->ur_id = ($_SESSION[_S]['nvj_id'] > 2) ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }
        $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE('m');

        switch ($_SESSION[_S]['nvj_id']) {

            case 2: case 3: case 4: case 5: case 6: case 7: case 8: case 9:

                switch ($this->metodo) {
                    case 'mesHasta':
                        $this->mesHasta();
                        break;
                    case 'reporteEfe':
                        $this->reporteEfe();
                        break;
                    case 'capturaEfe':
                        $this->capturaEfe();
                        break;
                    case 'pestanas':
                        $this->pestanas();
                        break;
                    case 'guardar':
                        $this->guardar();
                        break;
                    case 'buscandoUr':
                        $this->buscandoUr();
                        break;
                    case 'main':
                        $this->main();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;

            default:
                $this->sinPermisos();
                break;

        }
        $this->returnJson();

    }

    public function main()
    {
        switch (APP_EJERCICIO) {
            case 2025:
                if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 115) {
                    $this->reporteEfe();
                } else {
                    $this->pestanas();
                }
                break;
            default:
                $this->reporteEfe();
                break;
        }
    }
    public function pestanas()
    {
        // dibujamos los Tabuladores
        $pestana = '';
        $contenedor = '';
        $inicio = '';

        $pestana = str_replace('ultimo', '', $pestana);
        $pestana = str_replace('lastdiv', '', $pestana);

        $pestana .= '
                    <li class="nav-item reporte" opcion="reporte" ultimo>
                        <a class="nav-link ' . $inicio . 'reportes" mes="reporte" estado="reporte"> Reporte</a> 
                    </li>
					<li class="nav-item captura" opcion="captura" ultimo>
                        <a class="nav-link ' . $inicio . ' capturas" mes="captura" estado="captura"> Captura</a> 
                    </li>
                ';

        $contenedor .= '
                    <div id="contenedor" style="display:none" class="contenedores"></div>
                ';

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
                <div id="page-tabs"><ul class="nav nav-tabs">
                ' . $pestana . '
                </ul></div>
            ');

        $this->mixScript('
			$(".reporte").click(function(){
			   comunica("' . $this->pantalla . '",{metodo:"reporteEfe"});
			   $(".reportes").addClass("active");
			   $(".capturas").removeClass("active");
		    });

			$(".captura").click(function(){
			   comunica("' . $this->pantalla . '",{metodo:"capturaEfe"});
			   $(".capturas").addClass("active");
			   $(".reportes").removeClass("active");
		    });
        ');

        $this->agregaTools('');

    }

    /***********************CAPTURA DE EFE *******************/
    private function capturaEfe()
    {

        if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] != 115) {
            $this->pestanas();
        }

        $mes = $this->prm->mes;

        $sql = '
            SELECT *
            FROM 
                ' . BD_CTRL . '.cg_FlujoEfectivo' . APP_EJERCICIO_ANTERIOR . '
            WHERE 
                gpo_id = ? AND ur_id = ? AND msc_id= ?
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'],12]);
        $regs = $this->extraer();
        if ($this->getNumeroRegistros() > 0) {
            $r = $regs;
            $disabled = true;
        } else {
            $r = ['suma_FEAO_O'        => '0.00',
                       'importe_411'   => '0.00',
                       'importe_412'   => '0.00',
                       'importe_413'   => '0.00',
                       'importe_414'   => '0.00',
                       'importe_415'   => '0.00',
                       'importe_416'   => '0.00',
                       'importe_417'   => '0.00',
                       'importe_421'   => '0.00',
                       'importe_422'   => '0.00',
                       'importe_OOO'   => '0.00',
                       'suma_FEAO_A'   => '0.00',
                       'importe_511'   => '0.00',
                       'importe_512'   => '0.00',
                       'importe_513'   => '0.00',
                       'importe_521'   => '0.00',
                       'importe_522'   => '0.00',
                       'importe_523'   => '0.00',
                       'importe_524'   => '0.00',
                       'importe_525'   => '0.00',
                       'importe_526'   => '0.00',
                       'importe_527'   => '0.00',
                       'importe_528'   => '0.00',
                       'importe_529'   => '0.00',
                       'importe_531'   => '0.00',
                       'importe_532'   => '0.00',
                       'importe_533'   => '0.00',
                       'importe_OAO'   => '0.00',
                       'suma_FEAO'     => '0.00',
                       'suma_FEAI_O'   => '0.00',
                       'importe_123_O' => '0.00',
                       'importe_124_O' => '0.00',
                       'importe_OOI'   => '0.00',
                       'suma_FEAI_A'   => '0.00',
                       'importe_123_A' => '0.00',
                       'importe_124_A' => '0.00',
                       'importe_OOA'   => '0.00',
                       'suma_FNEAI'    => '0.00',
                       'suma_FEAF_O'   => '0.00',
                       'suma_OEN'      => '0.00',
                       'importe_OEN_I' => '0.00',
                       'importe_OEN_E' => '0.00',
                       'importe_OEF'   => '0.00',
                       'suma_FEAF_A'   => '0.00',
                       'suma_ASD'      => '0.00',
                       'importe_ASD_I' => '0.00',
                       'importe_ASD_E' => '0.00',
                       'importe_OAF'   => '0.00',
                       'suma_FNEAF'    => '0.00',
                       'suma_IDNEEE'   => '0.00',
                       'importe_EEEIE' => '0.00',
                       'suma_EEEFE'    => '0.00',
                    ];
        }
        $this->desconectar();
        $css = <<<CSS
            .scrollCaptura{
                height: 30em;
                line-height: 1em;
                overflow-x: hidden;
                overflow-y: scroll;
                width: 100%;
                border: 1px solid;
            }
        CSS;
        $this->mixstyle($css);

        $html = '
		    <br>
			<br>
			<br>
            <span style="height:6px; display:block"></span>
            <div class="well">
			    <div class="row" style="background-color:#d4d4d4;">
                    <fieldset class="col-md-10 col-xs-10" align="center" style="margin-top: 10px; margin-bottom: 10px;">
                        &nbsp;&nbsp;&nbsp; <b>CONCEPTO</b>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2" align="center" style="margin-top: 10px; margin-bottom: 10px;">
				        <b>DICIEMBRE ' . APP_EJERCICIO_ANTERIOR . '</b>
                    </fieldset> 
                </div>
            </div>
            <div class="well scrollCaptura">
            <form class="grid_12 no-box" action="" id="registroFlujoEfectivo" method="post" target="_self" style="font-size: .9rem;">
            <input id="metodo" name="metodo" value="guardar" type="hidden" "disabled" />
            <input id="msc" name="msc" value="12" type="hidden" "disabled" />
        ';

        $html .= $this->agrega_separador('Flujos de Efectivo de las Actividades de Operación', '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Origen', 'suma_FEAO_O', $r);
        $html .= $this->agrega_linea('Impuestos', 'importe_411', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Cuotas y Aportaciones de Seguridad Social', 'importe_412', $r);
        $html .= $this->agrega_linea('Contribuciones de Mejoras', 'importe_413', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Derechos', 'importe_414', $r);
        $html .= $this->agrega_linea('Productos', 'importe_415', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Aprovechamientos', 'importe_416', $r);
        $html .= $this->agrega_linea('Ingresos por Venta de Bienes y Prestación de Servicios', 'importe_417', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Participaciones, Aportaciones, Convenios, Incentivos Derivados de la Colaboración Fiscal y Fondos Distintos de Aportaciones', 'importe_421', $r);
        $html .= $this->agrega_linea('Transferencias, Asignaciones, Subsidios y Subvenciones, y Pensiones y Jubilaciones', 'importe_422', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Otros Orígenes de Operación', 'importe_OOO', $r);
        $html .= $this->agrega_grupo_sumatoria('Aplicación', 'suma_FEAO_A', $r, 5, '#f5f5f5');
        $html .= $this->agrega_linea('Servicios Personales', 'importe_511', $r);
        $html .= $this->agrega_linea('Materiales y Suministros', 'importe_512', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Servicios Generales', 'importe_513', $r);
        $html .= $this->agrega_linea('Transferencias Internas y Asignaciones al Sector Público', 'importe_521', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Transferencias al Resto del Sector Público', 'importe_522', $r);
        $html .= $this->agrega_linea('Subsidios y Subvenciones', 'importe_523', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Ayudas Sociales', 'importe_524', $r);
        $html .= $this->agrega_linea('Pensiones y Jubilaciones', 'importe_525', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Transferencias a Fideicomisos, Mandatos y Contratos Análogos', 'importe_526', $r);
        $html .= $this->agrega_linea('Transferencias a la Seguridad Social', 'importe_527', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Donativos', 'importe_528', $r);
        $html .= $this->agrega_linea('Transferencias al Exterior', 'importe_529', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Participaciones', 'importe_531', $r);
        $html .= $this->agrega_linea('Aportaciones', 'importe_532', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Convenios', 'importe_533', $r);
        $html .= $this->agrega_linea('Otras Aplicaciones de Operación', 'importe_OAO', $r, 10, '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Flujos Netos de Efectivo por Actividades de Operación', 'suma_FEAO', $r);
        $html .= '<br />';

        $html .= $this->agrega_separador('Flujos de Efectivo de las Actividades de Inversión', '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Origen', 'suma_FEAI_O', $r);
        $html .= $this->agrega_linea('Bienes Inmuebles, Infraestructura y Construcciones en Proceso', 'importe_123_O', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Bienes Muebles', 'importe_124_O', $r);
        $html .= $this->agrega_linea('Otros Orígenes de Inversión', 'importe_OOI', $r, 10, '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Aplicación', 'suma_FEAI_A', $r);
        $html .= $this->agrega_linea('Bienes Inmuebles, Infraestructura y Construcciones en Proceso', 'importe_123_A', $r, 10, '#f5f5f5');
        $html .= $this->agrega_linea('Bienes Muebles', 'importe_124_A', $r);
        $html .= $this->agrega_linea('Otras Aplicaciones de Inversión', 'importe_OOA', $r, 10, '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Flujos Netos de Efectivo por Actividades de Inversión', 'suma_FNEAI', $r);
        $html .= '<br />';

        $html .= $this->agrega_separador('Flujos de Efectivo de las Actividades de Financiamiento', '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Origen', 'suma_FEAF_O', $r);
        $html .= $this->agrega_grupo_sumatoria('Endeudamiento Neto', 'suma_OEN', $r, 5, '#f5f5f5');
        $html .= $this->agrega_linea('Interno', 'importe_OEN_I', $r, 10);
        $html .= $this->agrega_linea('Externo', 'importe_OEN_E', $r, 15, '#f5f5f5');
        $html .= $this->agrega_linea('Otros orígenes de Financiamiento', 'importe_OEF', $r, 10, );
        $html .= $this->agrega_grupo_sumatoria('Aplicación', 'suma_FEAF_A', $r, 10, '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Servicios de la Deuda', 'suma_ASD', $r, 10);
        $html .= $this->agrega_linea('Interno', 'importe_ASD_I', $r, 15, '#f5f5f5');
        $html .= $this->agrega_linea('Externo', 'importe_ASD_E', $r, 15);
        $html .= $this->agrega_linea('Otras Aplicaciones de Financiamiento', 'importe_OAF', $r, 10, '#f5f5f5');
        $html .= $this->agrega_grupo_sumatoria('Flujos Netos de Efectivo por Actividades de Financiamiento', 'suma_FNEAF', $r);
        $html .= '<br />';

        $html .= $this->agrega_grupo_sumatoria('Incremento/Disminución Neta en el Efectivo y Equivalentes al Efectivo', 'suma_IDNEEE', $r, 5, '#f5f5f5');
        $html .= '<br />';

        $html .= $this->agrega_linea('Efectivo y Equivalentes al Efectivo al Inicio del Ejercicio', 'importe_EEEIE', $r, 0);
        $html .= '<br />';

        $html .= $this->agrega_grupo_sumatoria('Efectivo y Equivalentes al Efectivo al Final del Ejercicio', 'suma_EEEFE', $r, 0, '#f5f5f5');
        $html .= '<br />';

        $html .= '
            </form>
            </div>
        ';

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'guardar';
        $btnGuardar->icon = 'fa-solid fa-floppy-disk me-2';
        if (isset($disabled)) {
            $btnGuardar->disabled = $disabled;
            $btnGuardar->class = 'me-1';
            $btnGuardar->color = 'gris';
        } else {
            $btnGuardar->class = 'btn btn-outline-default btn-outline-success me-2';
        }
        $btnGuardar->text = 'Guardar';
        $btnGuardar->value = 'Guardar';

        $btnInformativo = new stdClass();
        $btnInformativo->id = 'ayuda';
        $btnInformativo->icon = 'fa fa-solid fa-circle-question me-2';
        $btnInformativo->class = 'btn btn-outline-default btn-outline-warning me-1';
        $btnInformativo->title = 'Instructivo';
        $btnInformativo->text = '¿Cómo Funciona?';
        $btnInformativo->value = '¿Cómo Funciona?';

        $btnDerecha = new stdClass();
        $btnDerecha->id = 'derecha';
        $btnDerecha->icon = 'fa fa-solid fa-arrow-right me-2';
        $btnDerecha->class = 'btn btn-outline-default btn-outline-warning me-1';
        $btnDerecha->title = 'Conceptos a la derecha';
        $btnDerecha->text = '';
        $btnDerecha->value = '';

        $this->agregaTools($this->ponBoton($btnGuardar) . ' ' . $this->ponBoton($btnInformativo) . ' ' . $this->ponBoton($btnDerecha));
        $this->mixHtml($html);

        $this->mixScript('	   
            function get_importe(valor) {
                if ( $.isNumeric(valor)) {
                    return valor; 
                } else {
                    return 0;
                }
            }

            // Ponemos el focus en el primer campo al cargar la forma
            $("#suma_FEAO_O").focus().parent().prev().css({"color": "red"});


            $(document).ready(function() {
                $(".capturas").addClass("active");
			    $(".reportes").removeClass("active");

                $(".input_importe").on("input", function() {
                    var inputValue = $(this).val();
                    // Eliminar caracteres no válidos
                    inputValue = inputValue.replace(/[^0-9.]/g, "");
                    // Asegurar que solo haya un punto decimal
                    var parts = inputValue.split(".");
                    if (parts.length > 2) {
                        inputValue = parts[0] + "." + parts.slice(1).join("");
                    }
                    if (parts[0].length > 14) {
                        inputValue = parts[0].substring(0, 14) + (parts.length > 1 ? "." + parts[1] : "");
                    }
                    // Limitar a dos decimales
                    if (parts.length === 2 && parts[1].length > 2) {
                        inputValue = parts[0] + "." + parts[1].substring(0, 2);
                    }
                    $(this).val(inputValue);
                 });
            });

            
            $(".input_importe").blur(function(event){ 
                $(this).parent().prev().css({"color": "black"});
            });

            $(".input_importe").focus(function(event){ 
                var importe = $(this).val();
                $(this).val(unmaskDinero(importe)); 
                $(this).parent().prev().css({"color": "red"});
            });

            $(".input_importe").on("change", function () {
                this.value = maskDinero(unmaskDinero(this.value));
            });

            function unmaskDinero(numeroString) {
                return +(numeroString.replace(/[^0-9.-]+/g,""));
            }
            function maskDinero(numeroInt) {
                return parseFloat(numeroInt).toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }

            function importesEfe(){
                var c = 0;  
                var q = 0;
                var oen = 0;
                var oef = 0;
                var as = 0;  
                var oaf = 0;              
                var io = 0;
                var ia = 0;
                var ee = 0;
                $(".input_importe").each(function() {
                   if(!isNaN(this.value) || (this.value != "")){
                        var campo = this.name;
                        var importe = this.value;
                        switch(campo.substring(0, 9)){
                            case "importe_4":
                                c += parseFloat(unmaskDinero(importe));
                                break;
                            case "importe_5":
                                q += parseFloat(unmaskDinero(importe));
                                break;
                        }

                        switch(campo.substring(0, 11)){
                            case "importe_OOO":
                                c += parseFloat(unmaskDinero(importe));
                                break;
                            case "importe_OAO":
                                q += parseFloat(unmaskDinero(importe));
                                break;
                            case "importe_OOI":
                                io += parseFloat(unmaskDinero(importe));
                                break;
                            case "importe_OOA":
                                ia += parseFloat(unmaskDinero(importe));
                                break; 
                            case "importe_OEN":
                                oen += parseFloat(unmaskDinero(importe));
                                break;                                
                            case "importe_OEF":
                                oef += parseFloat(unmaskDinero(importe));
                                break; 
                            case "importe_ASD":
                                as += parseFloat(unmaskDinero(importe));
                                break;
                            case "importe_OAF":
                                oaf += parseFloat(unmaskDinero(importe));
                                break;
                        }                        
                        
                        switch(campo){                            
                            case "importe_123_O":
                            case "importe_124_O":
                                io += parseFloat(unmaskDinero(importe));
                                break;
                            case "importe_123_A":
                            case "importe_124_A":
                                ia += parseFloat(unmaskDinero(importe));
                                break;
                            case "importe_EEEIE":
                                ee = parseFloat(unmaskDinero(importe));
                                break;
                        }
                    }else{
                        $(this).val("0.00");
                    }
                });
                
                if(!isNaN(c)){
                    c = c;
                }else{
                    c = 0;
                }
                $("#suma_FEAO_O").val(maskDinero(c));
                if(!isNaN(q)){
                    q = q;
                }else{
                    q = 0;
                }
                $("#suma_FEAO_A").val(maskDinero(q));
                var feao = parseFloat(c)-parseFloat(q);
                $("#suma_FEAO").val(maskDinero(feao));

                if(!isNaN(io)){
                    io = io;
                }else{
                    io = 0;
                }
                $("#suma_FEAI_O").val(maskDinero(io));
                if(!isNaN(ia)){
                    ia = ia;
                }else{
                    ia = 0;
                }
                $("#suma_FEAI_A").val(maskDinero(ia));
                var fneai = parseFloat(io)-parseFloat(ia);
                $("#suma_FNEAI").val(maskDinero(fneai));

                var feaf = oen+oef;
                $("#suma_FEAF_O").val(maskDinero(feaf));
                if(!isNaN(oen)){
                    oen = oen;
                }else{
                    oen = 0;
                }
                $("#suma_OEN").val(maskDinero(oen));

                var feafa = parseFloat(as)+parseFloat(oaf);
                $("#suma_FEAF_A").val(maskDinero(feafa));
                if(!isNaN(as)){
                    as = as;
                }else{
                    as = 0;
                }
                $("#suma_ASD").val(maskDinero(as));
                var fneaf = (parseFloat(oen)+parseFloat(oef))-(parseFloat(as)+parseFloat(oaf));
                $("#suma_FNEAF").val(maskDinero(fneaf));

                var idne = (parseFloat(c)-parseFloat(q))+(parseFloat(io)-parseFloat(ia))+((parseFloat(oen)+parseFloat(oef))-(parseFloat(as)+parseFloat(oaf)));
                $("#suma_IDNEEE").val(maskDinero(idne));
                var eeefe =  ((parseFloat(c)-parseFloat(q))+(parseFloat(io)-parseFloat(ia))+((parseFloat(oen)+parseFloat(oef))-(parseFloat(as)+parseFloat(oaf)))) + parseFloat(ee);
                $("#suma_EEEFE").val(maskDinero(eeefe));
            }
        
            $(".input_importe").keyup(function(event){ 
               importesEfe();
            });           

            $("#ayuda").click(function(){ 
                if ( $("#mainContainer").hasClass("panel-expand") ) {
                    $("#mainContainer").hide();
                }       

                alertify.alert("<b>INSTRUCCIONES :</b>",
                               "Debes capturar <b>TODOS</b> los importes anuales del Flujo de Efectivo que hayas elaborado en el <b>ejercicio ' . APP_EJERCICIO_ANTERIOR . '</b>, para que estés en posibilidades de emitir el <b>Estado de Flujos de Efectivo</b>, ya que se necesitan los importes reales que hayan sido reportados.<br /><br />Una vez que hayas capturado y revisado todos los importes anuales, debes dar clic en el botón <strong>Guardar</strong> para que se incluya  en la columna de <b>DICIEMBRE ' . APP_EJERCICIO_ANTERIOR . '</b>.<br /><br />Para facilitar la lectura de la pantalla, se han marcado en azul los campos que corresponden a sumatorias, y puedes alinear a la derecha los conceptos o regresarlos a la normalidad presionando el botón de flecha a la derecha en la barra de botones."
                               ).set("labels", {ok:"Aceptar"});               
                
            });

            $("#derecha").click(function(){ 
                if ( $(".alineacion").css("text-align") == "right" ) {
                    $(".alineacion").css("text-align", "left");
                } else {
                    $(".alineacion").css("text-align", "right");
                }
            });

            //Función para comprobar los campos de texto
            function checkCampos(obj) {
                var camposRellenados = true;
                obj.find("input").each(function() {
                    var $this = $(this);
                    if( $this.val().length <= 0 ) {
                        camposRellenados = false;
                        return false;
                    }
                });
                if(camposRellenados == false) {
                    return false;
                }else {
                    return true;
                }
            }

            $("#guardar").click(function(e){ 
                e.preventDefault();

                // Validamos que todos los campos tengan importe 0.00
                var form = $(this).parents("#registroFlujoEfectivo");
                var check = checkCampos(form); 
                    
                importesEfe();

                // Si registraron importes en campos y no coincide con su campo de sumatoria, sólo avisamos
                    bolAvisoNoCoincidencia = false;                    

                    if ( ( get_importe("importe_411") + get_importe("importe_412") + get_importe("importe_413") + 
                           get_importe("importe_414") + get_importe("importe_415") + get_importe("importe_416") + 
                           get_importe("importe_417") + get_importe("importe_421") + get_importe("importe_422") + get_importe("importe_OOO")
                         ) != get_importe("suma_FEAO_O") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("importe_511") + get_importe("importe_512") + get_importe("importe_513") + get_importe("importe_521") + 
                           get_importe("importe_522") + get_importe("importe_523") + get_importe("importe_524") + get_importe("importe_525") + 
                           get_importe("importe_526") + get_importe("importe_527") + get_importe("importe_528") + get_importe("importe_529") +
                           get_importe("importe_531") + get_importe("importe_532") + get_importe("importe_533") + get_importe("importe_OAO")
                         ) != get_importe("suma_FEAO_A") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("suma_FEAO_O") - get_importe("suma_FEAO_A") ) != get_importe("suma_FEAO") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("importe_123_O") + get_importe("importe_124_O") + get_importe("importe_OOI") 
                         ) != get_importe("suma_FEAI_O") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("importe_123_A") + get_importe("importe_124_A") + get_importe("importe_OOA") 
                         ) != get_importe("suma_FEAI_A") ) { 
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("suma_FEAI_O") - get_importe("suma_FEAI_A") ) != get_importe("suma_FNEAI") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("importe_OEN_I") + get_importe("importe_OEN_E") ) != get_importe("suma_OEN") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("suma_OEN") + get_importe("importe_OEF") ) != get_importe("suma_FEAF_O") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("importe_ASD_I") + get_importe("importe_ASD_E") ) != get_importe("suma_ASD") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("suma_ASD") + get_importe("importe_OAF") ) != get_importe("suma_FEAF_A") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("suma_ASD") + get_importe("suma_FEAF_A") ) != get_importe("suma_FNEAF") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("suma_FEAO") + get_importe("suma_FNEAF") + get_importe("suma_FNEAF") ) != get_importe("suma_IDNEEE") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    if ( ( get_importe("suma_IDNEEE") + get_importe("importe_EEEIE") ) != get_importe("suma_EEEFE") ) {
                        bolAvisoNoCoincidencia = true;
                    }

                    // Si hay diferencias SÓLO AVISAMOS... en tanto que ellos capturan, las URs son responsables de los montos
                    if ( bolAvisoNoCoincidencia ) {

                        if ( $("#mainContainer").hasClass("panel-expand") ) {
                            $("#mainContainer").hide();
                        }

                        alertify.alert("AVISO INFORMATIVO",
                                        "El sistema ha detectado que al menos una sumatoria no coincide con la suma de cada uno de sus correspondientes importes anuales.<br /><br />Esto puede deberse a que no haz capturado el algún importe de esa sección.<br /><br />En cualquier caso, como debes capturar exactamente los importes que reportaste."
                                      ).set("labels", {ok:"Aceptar"});       

                    }

                    var datos = {};
                    $.each( $("#registroFlujoEfectivo").serializeArray(), function() {
                        datos[this.name] = this.value;
                    });

                    if (check) {  
                        alertify.confirm(
                            "Registro de Flujo de Efectivo", 
                            "A continuación se guardarán los importes y ya no se podrá realizar ningún cambio , ¿ Desea continuar ?",
                            function(){                            
                                comunica( "' . $this->pantalla . '", {
                                    "metodo" : "guardar",
                                    "data"  : datos
                                }); 
                            }, 
                            function(){}
                        ).set("labels", {ok : "Aceptar", cancel :  "Cancelar"});                 
                    } else {
                        notify( "Debes registrar los importes para poder guardar", "error" );
                    }

                    e.stopPropagation();
                    return false;

                });            
            ');
    }

    // Agregamos una línea que sólo es agrupador, no tiene sumatorias ni importes
    private function agrega_separador($concepto, $background)
    {

        return '
            <div class="row" style="background-color:' . $background . '">
                <fieldset class="col-md-10 col-xs-10" style="margin-top: 10px; margin-bottom: 10px;">
                    <strong>
                        ' . $concepto . '
                    </strong>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">&nbsp;</fieldset>                
                <br>
                <br>
            </div>
            <span style="height:6px; display:block"></span>
        ';

    }

    // Agregamos un agrupador, que incluye la suma de un grupo de conceptos
    private function agrega_grupo_sumatoria($concepto, $nombre_campo, $registro, $espacios = 5, $background = null)
    {

        $readonly = 'readonly';

        return '
            <div class="row" style="background-color:' . $background . '">
                <fieldset class="col-md-10 col-xs-10 alineacion" style="margin-top: 10px; margin-bottom: 10px;">
                    <strong>
                        ' . str_repeat('&nbsp;', $espacios) . $concepto . '
                    </strong>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <input type="text" id="' . $nombre_campo . '" name="' . $nombre_campo . '" class="form-control wr-input moneda input_importe" value="' . number_format($registro[$nombre_campo], 2) . '" style="text-align:right; background-color: #97cfe5 !important;" ' . $readonly . ' />
                </fieldset>
            </div>
            <span style="height:6px; display:block"></span>
        ';

    }

    private function agrega_linea($concepto, $nombre_campo, &$registro, $espacios = 10, $background = null)
    {

        $readonly = (isset($registro['fe_autorizado']) == 1) ? 'readonly' : '';

        return '
            <div class="row" style="background-color:' . $background . '">
                <fieldset class="col-md-10 col-xs-10 alineacion" style="margin-top: 10px; margin-bottom: 10px;">
                    ' . str_repeat('&nbsp;', $espacios) . $concepto . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <input type="text" id="' . $nombre_campo . '" name="' . $nombre_campo . '" step="any" class="form-control wr-input moneda input_importe" value="' . number_format($registro[$nombre_campo], 2) . '"style="text-align:right" ' . $readonly . ' />
                </fieldset>    
            </div>
            <span style="height:6px; display:block"></span>
        ';

    }

    private function guardar()
    {

        // Armamos la consulta para insertar o actualizar si ya existe el registro
        $campos = 'gpo_id, ur_id, msc_id, fe_autorizado, fe_fechaAutorizacion, ';
        $fecha = "'" . date('Y-m-d h:i:s') . "'";
        $valores = $_SESSION[_S]['clasAdm-gpo'] . ', ' . $_SESSION[_S]['clasAdm-ur'] . ', ' . $this->prm->data->msc . ', ' . '1' . ', ' . $fecha . ', ';
        $updates = '';
        $suma = 0;
        foreach ($this->prm->data as $clave => $valor) {
            if ($clave != 'metodo' && $clave != 'msc') {
                $importe = (float) str_replace(',', '', $valor);
                if (is_float($importe)) {
                    $campos .= $clave . ', ';
                    $valores .= $importe . ', ';
                    $updates .= $clave . ' = ' . str_replace(',', '', $importe) . ', ';
                    $suma += str_replace(',', '', $importe);
                }
            }
        }

        $campos .= 'usr_id';
        $valores .= $_SESSION[_S]['usr_id'];
        $updates = substr($updates, 0, -2);

        $sql = 'INSERT INTO ' . BD_CTRL . '.cg_FlujoEfectivo' . APP_EJERCICIO_ANTERIOR . "( $campos ) VALUES( $valores ) ON DUPLICATE KEY UPDATE $updates;";

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        $this->mixScript('
            notify( "Se han registrado los importes del Flujo de Efectivo de DICIEMBRE ' . APP_EJERCICIO_ANTERIOR . '", "success" );
            comunica("' . $this->pantalla . '",{metodo:"pestanas"});
        ');
    }

    private function reporteEfe()
    {
        if (($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] != 115) and (APP_EJERCICIO == 2025)) {
            $this->pestanas();
        }
        $html = '';

        $ur = new AuxiliarUnidadResponsable();
        $ur->setGpo_id($this->gpo_id);
        $ur->setUr_id($this->ur_id);
        $ur->select();

        $tipoReporte = new stdClass();
        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
                $tipoReporte->opciones = [1 => 'Solo la UR actual', 2 => 'Todas las UR de la Carga de Trabajo'];
                break;
            default:
                $tipoReporte->opciones = [1 => 'Solo la UR actual', 2 => 'Todas las UR de la Carga de Trabajo', 3 => 'Limitar que incluir y que no'];
                break;
        }

        $tipoReporte->id = 'tipoReporte';
        $tipoReporte->label = 'Tipo de Reporte';
        $tipoReporte->selecciona = 1;
        $tipoReporte->width = '150px';

        $comboMes = new stdClass();
        $comboMes->sql = 'SELECT msc_id, msc_nombre FROM cat_MesContable';
        $comboMes->id = 'comboMes';
        $comboMes->name = 'comboMes';
        $comboMes->required = true;
        $comboMes->enDiv = true;
        $comboMes->camposVal = 'msc_id';
        $comboMes->agrupaPor = 'msc_id';
        $comboMes->comoAtr = 'msc_id';
        $comboMes->selecciona = 1;
        $comboMes->label = 'Desde ';

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = 'SELECT msc_id, msc_nombre FROM cat_MesContable;';
        $comboMesHasta->id = 'comboMesHasta';
        $comboMesHasta->width = '150px';
        $comboMesHasta->label = 'Hasta ';
        $comboMesHasta->selecciona = 1;

        $grupos = new stdClass();
        $grupos->disabled = true;
        $grupos->id = 'grupos';
        $grupos->class = 'paso';
        $grupos->value = '1,2,3,7,8';
        $grupos->enDiv = true;
        $grupos->label = 'Grupos a incluir';

        $excluir = new stdClass();
        $excluir->disabled = true;
        $excluir->id = 'excluir';
        $excluir->enDiv = true;
        $excluir->class = 'paso';
        $excluir->label = 'URs a excluir';

        $nombreEspecial = new stdClass();
        $nombreEspecial->disabled = true;
        $nombreEspecial->id = 'nombreEspecial';
        $nombreEspecial->enDiv = true;
        $nombreEspecial->class = 'paso';
        $nombreEspecial->placeholder = 'Ejem: SECTOR CENTRAL';
        $nombreEspecial->label = 'Encabezado Personalizado';

        $firmante1 = new stdClass();
        $firmante1->id = 'firmante1';
        $firmante1->enDiv = true;
        if ($_SESSION[_S]['nvj_id'] == 1) {
            $firmante1->value = '';
        } else {
            $firmante1->value = $ur->getNombreFinancieros();
        }
        $firmante1->label = 'Firmante 1';

        $firmante2 = new stdClass();
        $firmante2->id = 'firmante2';
        $firmante2->enDiv = true;
        if ($_SESSION[_S]['nvj_id'] == 1) {
            $firmante2->value = 'aaaaa';
        } else {
            $firmante2->value = $ur->getNombreAdministrativo();
        }
        $firmante2->label = 'Firmante 2';

        $firmante3 = new stdClass();
        $firmante3->id = 'firmante3';
        $firmante3->enDiv = true;
        if ($_SESSION[_S]['nvj_id'] == 1) {
            $firmante3->value = '';
        } else {
            $firmante3->value = $ur->getNombreTitular();
        }
        $firmante3->label = 'Firmante 3';

        $cargo1 = new stdClass();
        $cargo1->id = 'cargo1';
        $cargo1->enDiv = true;
        if ($_SESSION[_S]['nvj_id'] == 1) {
            $cargo1->value = '';
        } else {
            $cargo1->value = $ur->getCargoFinancieros();
        }
        $cargo1->label = 'Cargo 1';
        $cargo1->label = 'Cargo 1';

        $cargo2 = new stdClass();
        $cargo2->id = 'cargo2';
        $cargo2->enDiv = true;
        if ($_SESSION[_S]['nvj_id'] == 1) {
            $cargo2->value = '';
        } else {
            $cargo2->value = $ur->getCargoAdministrativo();
        }
        $cargo2->label = 'Cargo 2';

        $cargo3 = new stdClass();
        $cargo3->id = 'cargo3';
        $cargo3->enDiv = true;
        if ($_SESSION[_S]['nvj_id'] == 1) {
            $cargo3->value = '';
        } else {
            $cargo3->value = $ur->getCargoTitular();
        }
        $cargo3->label = 'Cargo 3';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->search = true;
        $ue->enDiv = true;
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur AND ue_id = :ue ;';
                $ue->params = [':gpo' => $_SESSION[_S]['clasAdm-gpo'],':ur' => $_SESSION[_S]['clasAdm-ur'],':ue' => $_SESSION[_S]['ue_id']];
                break;
            case 2:
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ';';
                break;
            case 3:
            case 4:
                $this->conectar();
                $this->setSql('
						SELECT ue_id,gpo_id FROM cat_CargaTrabajo WHERE 
						gpo_id = :gpo 
						AND ur_id = :ur
						AND usr_id = :usr ;
					');
                $this->consultar([':gpo' => $_SESSION[_S]['clasAdm-gpo'],':ur' => $_SESSION[_S]['clasAdm-ur'],':usr' => $_SESSION[_S]['usr_id']]);
                $this->desconectar();
                $tieneUe = $this->extraer();

                if ($tieneUe['ue_id'] != '') {
                    $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora 
						INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id,ue_id)
						WHERE gpo_id = :gpo 
						AND ur_id = :ur
						AND c.usr_id = :usr ;';
                    $ue->params = [':gpo' => $_SESSION[_S]['clasAdm-gpo'],':ur' => $_SESSION[_S]['clasAdm-ur'],':usr' => $_SESSION[_S]['usr_id']];

                } elseif ($tieneUe['gpo_id'] != '') {
                    $ue->sql = 'SELECT u.ue_id,ue_nombre FROM cat_UnidadEjecutora u
						INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id)
						WHERE gpo_id = :gpo 
						AND ur_id = :ur
						AND c.usr_id = :usr ;';
                    $ue->params = [':gpo' => $_SESSION[_S]['clasAdm-gpo'],':ur' => $_SESSION[_S]['clasAdm-ur'],':usr' => $_SESSION[_S]['usr_id']];
                } else {
                    $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora 
						WHERE gpo_id = :gpo 
						AND ur_id = :ur ;';
                    $ue->params = [':gpo' => $_SESSION[_S]['clasAdm-gpo'],':ur' => $_SESSION[_S]['clasAdm-ur']];
                }
                break;
            default:
                $ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora 
								WHERE gpo_id = :gpo
								AND ur_id = :ur ;';
                $ue->params = [':gpo' => $_SESSION[_S]['clasAdm-gpo'],':ur' => $_SESSION[_S]['clasAdm-ur']];
                break;
        }
        $ue->label = 'Unidad Ejecutora';

        //CONSULTA PARA VERIFICACION DE CAPTURA DICIEMBRE EFE
        $sql = 'SELECT msc_id as meses 
                    FROM ' . BD_CTRL . '.cg_FlujoEfectivo' . APP_EJERCICIO_ANTERIOR . '
                    wHERE gpo_id=? AND ur_id=? AND msc_id =?;';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar([$this->gpo_id,$this->ur_id,12]);
        $con->desconectar();

        $buttonFlujoPDF = new StdClass();
        $buttonFlujoPDF->id = 'buttonFlujoPDF';
        $buttonFlujoPDF->type = 'button';
        $buttonFlujoPDF->value = ' PDF';
        $buttonFlujoPDF->class = 'btnImp btn btn-outline-danger me-2';
        $buttonFlujoPDF->icon = ' fa-solid fa-file-pdf me-2';
        $buttonFlujoPDF->diabled = false;
        $buttonFlujoPDF->form = 'formFlujoEfvo';

        $buttonFlujoExcel = new StdClass();
        $buttonFlujoExcel->id = 'buttonFlujoExcel';
        $buttonFlujoExcel->value = 'Excel';
        $buttonFlujoExcel->icon = 'fa-solid fa-file-excel me-2';
        $buttonFlujoExcel->type = 'button';
        $buttonFlujoExcel->class = 'btnImp btn-outline-success';
        $buttonFlujoExcel->diabled = false;
        $buttonFlujoExcel->form = 'formFlujoEfvo';

        $this->conectar();
        $this->setSql($ue->sql);
        $this->consultar($ue->params);
        $countUEnum = $this->getNumeroRegistros();
        $this->desconectar();

        $html = '
			    <br>
				<br>
				<br>
				<legend align="center"></legend>
				<form class="grid_12 no-box" id="formFlujoEfvo" method="post" target="_blank" >
					<div class="row">
						<fieldset class="col-md-2 col-xs-2">
							' . $this->ponCombo($comboMes) . '
						</fieldset>
						<fieldset id="mhasta" class="col-md-2 col-xs-2">
							' . $this->ponCombo($comboMesHasta) . '	
						</fieldset>
                        <fieldset class="col-md-4 col-xs-4">
                        <input type="hidden" id="numUe" name="numUe"  value=' . $countUEnum . '>
                        ' . ($_SESSION[_S]['nvj_id'] >= 3 ? $this->ponCombo($tipoReporte) : '') . '
                        </fieldset >
                        <fieldset class="col-md-4 col-xs-4">
                        ' . $this->ponCombo($ue) . '
                        </fieldset >
					</div>
                    ' . (($_SESSION[_S]['nvj_id'] >= 5) ?
                '<div class="row">
                        <fieldset class="col-md-3 col-xs-3">
                            ' . $this->ponInput($grupos) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-3">
                            ' . $this->ponInput($excluir) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($nombreEspecial) . '
                        </fieldset>
                    </div>'
                : '') . '
					<div class="row">								
						<fieldset class="col-md-6 col-xs-6">
							' . $this->ponInput($firmante1) . '
						</fieldset>									
						<fieldset class="col-md-6 col-xs-6">
							' . $this->ponInput($cargo1) . '
						</fieldset>									
					</div>
					<div class="row">								
						<fieldset class="col-md-6 col-xs-6">
							' . $this->ponInput($firmante2) . '
						</fieldset>									
						<fieldset class="col-md-6 col-xs-6">
							' . $this->ponInput($cargo2) . '
						</fieldset>									
					</div>
					<div class="row">								
						<fieldset class="col-md-6 col-xs-6">
							' . $this->ponInput($firmante3) . '
						</fieldset>									
						<fieldset class="col-md-6 col-xs-6">
							' . $this->ponInput($cargo3) . '
						</fieldset>									
					</div>
					<br />
					<div class="row">
						<fieldset class="col-md-2 col-xs-2">
							<label>Tipo de Importe:</label><br/>
							<input type="checkbox" value="1" name="chkEnMiles" id="chkEnMiles"/> En miles de pesos
						</fieldset>
						<fieldset class="col-md-2 col-xs-2">
							<label>Leyenda:</label><br/>
								<input type="checkbox" value="1" name="leyenda" id="leyenda" checked/> Con leyenda									
						</fieldset >					
                        <fieldset class="col-md-8 col-xs-8" style="padding-right: 1px !important;">
                            <legend>Modo Validación</legend>
                            <input id="zebra" name="zebra" type="checkbox">&nbsp;Zebra</input>&nbsp;&nbsp;&nbsp;
                            <input id="interlinea" name="interlinea" type="checkbox" checked>&nbsp;Interlineado Estrecho</input>&nbsp;&nbsp;&nbsp;
                        	&nbsp;&nbsp;&nbsp;Tamaño Fuente : <input id="font_size" name="font_size" type="text" value="6.8" data-placement="bottom" title="Tamaño de la fuente" size=3 />
                        </fieldset>
					</div>	
                    <div id="resultado"></div>
				</form>
            ';

        $this->agregaTools($this->ponBoton($buttonFlujoPDF) . ' ' . $this->ponBoton($buttonFlujoExcel));
        $this->mixHtml($html);

        $this->mixScript('
                ' . ClasificacionAdministrativa::onChangeUr("comunica('" . $this->pantalla . "',{metodo:'main'})") . '
                $("#buttonFlujoPDF").on("click",function(){
                    switch(' . APP_EJERCICIO . '){
                        case 2024:
                            if(' . $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] . ' == 115){
                                comunica("' . $this->pantalla . '",{metodo:"buscandoUr","urs":combour.value,"tipo":"PDF"});
                                ' . ClasificacionAdministrativa::onChangeUr("comunica('" . $this->pantalla . "',{metodo:'buscandoUr','urs':combour.value,'tipo':'PDF'});") . ';
                            }else{
                                if(' . $con->getNumeroRegistros() . ' == 0){
                                    notify("No existen importes capturados a DICIEMBRE ' . APP_EJERCICIO_ANTERIOR . '", "error");
                                    comunica("' . $this->pantalla . '",{metodo:"reporteEfe"});
                                }else{
                                    $("#buttonFlujoPDF").prop("disabled", true);
                                    $("#formFlujoEfvo").attr("action","rep/doc/scg/cuentaPublica/rep_Flujo.php");
                                    $("#formFlujoEfvo").submit();
                                    alertify.alert("Aviso", "Se esta generando el Edo Financiero Flujos de Efectivo, por favor espere!!").set("labels", {ok:"Aceptar"});
                                }
                            }
                            break;
                        default:                        
                            $("#buttonFlujoPDF").prop("disabled", true);
                            $("#formFlujoEfvo").attr("action","rep/doc/scg/cuentaPublica/rep_Flujo.php");
                            $("#formFlujoEfvo").submit();
                            alertify.alert("Aviso", "Se esta generando el Edo Financiero Flujos de Efectivo, por favor espere!!").set("labels", {ok:"Aceptar"});
                            break;
                    }
                });

                $("#buttonFlujoExcel").on("click",function(){
                    switch(' . APP_EJERCICIO . '){
                        case 2024:
                            if(' . $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] . ' == 115){
                                comunica("' . $this->pantalla . '",{metodo:"buscandoUr","urs":combour.value,"tipo":"EXCEL"});
                                ' . ClasificacionAdministrativa::onChangeUr("comunica('" . $this->pantalla . "',{metodo:'buscandoUr','urs':combour.value,'tipo':'EXCEL'});") . ';
                            }else{
                                if(' . $con->getNumeroRegistros() . ' == 0){
                                    notify("No existen importes capturados a DICIEMBRE ' . APP_EJERCICIO_ANTERIOR . '", "error");
                                    comunica("' . $this->pantalla . '",{metodo:"reporteEfe"});
                                }else{
                                    $("#buttonFlujoExcel").prop("disabled", true);
                                    $("#formFlujoEfvo").attr("action","rep/excel/scg/cuentaPublica/exp_Flujo.php");
                                    $("#formFlujoEfvo").submit();
                                    alertify.alert("Aviso", "Se esta generando el Edo Financiero Flujos de Efectivo, por favor espere!!").set("labels", {ok:"Aceptar"});
                                }
                            }
                            break;
                        default:
                            $("#buttonFlujoExcel").prop("disabled", true);
                            $("#formFlujoEfvo").attr("action","rep/excel/scg/cuentaPublica/exp_Flujo.php");
                            $("#formFlujoEfvo").submit();
                            alertify.alert("Aviso", "Se esta generando el Edo Financiero Flujos de Efectivo, por favor espere!!").set("labels", {ok:"Aceptar"});
                            break;
                    }
                });

			    $(document).ready(function() {
                    $(".reportes").addClass("active");
			        $(".capturas").removeClass("active");
                });

				$("nav").hide();

				$(document).on("click", "#homeMenuPM", function (e) {
					e.preventDefault();
					$("nav").show();
					$(".navbar").show();
					$(".modulos").show();
					$("#sticky-anchor").hide();
					$("#sticky").hide();
					$(".cabecera").show();
					$("#mainContainer").css( "margin-top","40px" );
					
					// Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
					$("div[aria-describedby=cargaDatosCuentas]").html("");
					$("div[aria-describedby=cargaDatosProveedor]").html("");
					
					$("#ayuda").addClass("gris");
					$("#tip").addClass("gris");
					
					$("#contacto").text( $("#contacto").attr("original") );
					
					$("#cgPantallaCompleta").empty(); 
					$("#cgPantallaCompleta").html("");
					
					$(document).off("click", "#homeMenuPM" );
					
					return false;
				});

				$("#comboMes").change(function(){
					comunica("' . $this->pantalla . '", {
						metodo: "mesHasta",
						mes: $(this).val()
					});
				});

	            $("#tipoReporte").change(function(){
	                var nOpcion = $(this).val();
	                switch(nOpcion){
	                    case "1":
	                        $(".paso").attr("disabled", "disabled");
	                        $("#ue").removeAttr("disabled");
	                        break;
	                    case "2":
	                        $("#ue").attr("disabled",true);
	                        $(".paso").attr("disabled", "disabled");
	                        break;
	                    case "3":
	                        $(".paso").removeAttr("disabled");
	                        $("#ue").attr("disabled",true);
	                        break;
	                }
	            });

			
				$("#leyenda").click(function(){
					if($("#leyenda").is(":checked"))
						$("#leyenda").val("1");
					else 
						$("#leyenda").val("");
				});
			');

    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');
        if (isset($this->prm->mes)) {
            $mes = $this->prm->mes;

            $comboMesHasta = new stdClass();
            $comboMesHasta->sql = 'SELECT msc_id, msc_nombre FROM cat_MesContable WHERE msc_id >= :mes ;';
            $comboMesHasta->params = [':mes' => $mes];
            $comboMesHasta->id = 'comboMesHasta';
            $comboMesHasta->width = '150px';
            $comboMesHasta->label = 'hasta';
            $comboMesHasta->selecciona = $mes;

            $this->mixHtml($this->ponCombo($comboMesHasta));
        }
    }

    public function buscandoUr()
    {
        $ur = (isset($this->prm->urs)) ? $this->prm->urs : '';
        $tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';

        //CONSULTA PARA VERIFICACION DE CAPTURA DICIEMBRE
        $sql = 'SELECT msc_id as meses 
                    FROM ' . BD_CTRL . '.cg_FlujoEfectivo' . APP_EJERCICIO_ANTERIOR . ' 
                    wHERE CONCAT(gpo_id,ur_id) = ? AND msc_id = ?;';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar([$ur,12]);
        $con->desconectar();

        if ($con->getNumeroRegistros() == 0) {
            $this->mixScript('
                    notify("No existen importes capturados a DICIEMBRE ' . APP_EJERCICIO_ANTERIOR . '", "error");
                    comunica("' . $this->pantalla . '",{metodo:"reporteEfe"});
                ');
        } else {
            $this->mixScript('
                    switch("' . $tipo . '"){
                        case "PDF":                            
                            $("#buttonFlujoPDF").prop("disabled", true);
                            $("#formFlujoEfvo").attr("action","rep/doc/scg/cuentaPublica/rep_Flujo.php");
                            $("#formFlujoEfvo").submit();
                            alertify.alert("Aviso", "Se esta generando el Edo Financiero Flujos de Efectivo, por favor espere!!").set("labels", {ok:"Aceptar"});
                            break;
                        case "EXCEL":
                            $("#buttonFlujoExcel").prop("disabled", true);
                            $("#formFlujoEfvo").attr("action","rep/excel/scg/cuentaPublica/exp_Flujo.php");
                            $("#formFlujoEfvo").submit();
                            alertify.alert("Aviso", "Se esta generando el Edo Financiero Flujos de Efectivo, por favor espere!!").set("labels", {ok:"Aceptar"});
                            break;
                        default:
                            if(' . $con->getNumeroRegistros() . ' == 0){
                                notify("No existen importes capturados a DICIEMBRE ' . APP_EJERCICIO_ANTERIOR . '", "error");
                                comunica("' . $this->pantalla . '",{metodo:"reporteEfe"});
                            }else{
                               $("##buttonFlujoPDF").focus();
                                $("##buttonFlujoPDF").focus();
                            }
                            break;
                    }
                ');
        }
    }
}

if (isset($_SESSION[_S]['prm']['C']['11']['11'])) {
    new ControladorFlujoEfectivo();
} else {
    new ControladorFlujoEfectivo();
    $error->sinPermisos();
}
