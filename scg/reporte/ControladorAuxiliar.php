<?php
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
//require_once dirname(__FILE__).'/../../lib/funcionesSCG.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../lib/scg/reporte/cuentasRegistro.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorAuxiliar extends WebResponse
{
    public $reportemasivo;
    public $reporteNivelRegistro;
    public $gpo_id;
    public $ur_id;
    public $nivel;
    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();
        $this->reportemasivo = isset($_SESSION[_S]['prm']['C']['10']['1']['104']) ? true : false;
        $this->reporteNivelRegistro = isset($_SESSION[_S]['prm']['C']['10']['1']['105']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        switch ($this->metodo) {
            case 'muestraRangoCtas':$this->muestraRangoCtas();
                break;
            case 'CargaHasta':$this->CargaHasta();
                break;
            case 'mesHasta':$this->mesHasta();
                break;
            case 'ValidaCuenta':$this->ValidaCuenta();
                break;

            case 'exportaExcelMasivo':if ($this->reportemasivo) {
                    $this->exportaExcelMasivo();
                }
                break;
            default:$this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $es116 = false;
        if ($this->gpo_id . $this->ur_id == 116) {
            $es116 = true;
        }
        $this->mixHtml(DibujaPantallaBuscarCgClaveFinaciamientoHTML(false, $es116));
        $this->mixScript(DibujaPantallaBuscarCgClaveFinaciamientoJS());
        $unidadResponsable = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        //Define radio button
        $radcta = new StdClass();
        $radcta->id = "radcta";
        $radcta->name = "radcta";
        if ($this->reporteNivelRegistro && $this->reportemasivo) {
            $radcta->values = array('C' => "Cuenta", 'R' => "Rango de Cuentas", 'M' => "Nivel de registro separado",
                'N' => "Rango - Nivel de registro");
        } elseif ($this->reporteNivelRegistro) {
            $radcta->values = array('C' => "Cuenta", 'R' => "Rango de Cuentas",
                'N' => "Rango - Nivel de registro");
        } elseif ($this->reportemasivo) {
            $radcta->values = array('C' => "Cuenta", 'R' => "Rango de Cuentas", 'M' => "Nivel de registro separado");
        } else {
            $radcta->values = array('C' => "Cuenta", 'R' => "Rango de Cuentas");
        }
        $radcta->cols = 2;
        $radcta->height = '20px';
        $radcta->align = 'left';
        $radcta->selecciona = 'C';
        //Define combo tipo poliza
        $tipoPoliza = new stdClass();
        $tipoPoliza->sql = "select  pot_id, pot_nombre from cat_TipoPoliza;";
        $tipoPoliza->id = "pot_id";
        $tipoPoliza->label = "Tipo de Poliza";
        $tipoPoliza->width = '200px';

        $cmbAgrupacion = new stdClass();
        $cmbAgrupacion->sql = "select adf_id,adf_nombre from cat_AgrupacionDocumentoFuente;";
        $cmbAgrupacion->label = "Agrupación";
        $cmbAgrupacion->id = "adf_id";
        $cmbAgrupacion->width = '200px';

        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable";
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = 1;
        $comboMesDesde->width = '150px';

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable;";
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->width = "150px";
        $comboMesHasta->label = "Hasta";
        $comboMesHasta->selecciona = 1;

        $btnPDF = new stdClass();
        $btnPDF->id = "pdf";
        $btnPDF->class = "btnImp btn-outline-danger me-2";
        $btnPDF->value = " PDF";
        $btnPDF->icon = 'fa-solid fa-file-pdf';
        $btnPDF->type = "submit";

        $btnEXCEL = new stdClass();
        $btnEXCEL->id = "excel";
        $btnEXCEL->class = "btnImp btn-outline-success me-2";
        $btnEXCEL->value = " Excel";
        $btnEXCEL->icon = 'btn fa-solid fa-file-excel';
        $btnEXCEL->type = "submit";

        $mmc = new StdClass();
        $mmc->name = "mmc-id[]";
        $mmc->id = "mmc-id[]";
        $mmc->class = "mmc_class";
        $mmc->label = "Momento Contable";
        $mmc->sql = "select mmc_id, mmc_nombre from cat_MomentoContable";
        $mmc->camposVal = "mmc_id";
        $mmc->camposText = "mmc_nombre";
        $mmc->vertical = true;

        $ue = new stdClass();

        $ur = new stdClass();

        $html = '
            <form id="formAuxiliar" method="post" target="_blank">
                <input type="hidden" name="sis_id" id="sis_id" value="c">
                <input type="hidden" name="men_id" id="men_id" value="10">
                <input type="hidden" name="opc_id" id="opc_id" value="1">
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend id ="leyenda">Reporte Auxiliar </legend>
                        <div class="col-md-12 col-xs-12">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="linkPrincipal-tab" data-bs-toggle="pill" data-bs-target="#principal" type="button" role="tab" aria-controls="principal" aria-selected="true">Principal</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="linkPoliza-tab" data-bs-toggle="pill" data-bs-target="#poliza" type="button" role="tab" aria-controls="poliza" aria-selected="false">Información momento contable</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="linkExt ra-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Información extra</button>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="principal" role="tabpanel" aria-labelledby="principal-tab">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col col-md-6 ">
                                                <div class="">
                                                    <legend>Unidad Ejecutora</legend>
                                                </div>
                                                <div class="">
                                                    ' . ClasificacionAdministrativa::clasAdminUe($ue) . '
                                                </div>
                                            </div>
                                            <div class="col col-md-6 col-xs-6">
                                                <div class="">
                                                    <legend>Periodo</legend>
                                                </div>
                                                <div class="row col-md-12 col-xs-12 mb-4">
                                                    <fieldset class="col-md-6 col-xs-6">
                                                        ' . $this->ponCombo($comboMesDesde) . '
                                                    </fieldset>
                                                    <fieldset id="mhasta" class="col-md-6 col-xs-6">
                                                        ' . $this->ponCombo($comboMesHasta) . '
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 col-xs-12">
                                                    <div class="col-md-12 col-xs-12">
                                                        <legend>Consultar por:</legend>
                                                        ' . $this->ponRadio($radcta) . '
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="ctaRango" class="row col-md-12 col-xs-12 mb-4">

                                            </div>
                                            <div class="row">
                                                <fieldset class="col-md-6 col-xs-6">
                                                    <legend>Información de poliza</legend>
                                                    <fieldset class="col-md-6 col-xs-6">
                                                        ' . $this->ponCombo($tipoPoliza) . '
                                                    </fieldset>
                                                <!--<legend>Información de poliza</legend>
                                                    <fieldset class="col-md-6 col-xs-6">
                                                        ' . $this->ponCombo($tipoPoliza) . '
                                                    </fieldset>
                                                    <fieldset class="col-md-6 col-xs-6">
                                                        ' . $this->ponCombo($cmbAgrupacion) . '
                                                    </fieldset> -->
                                                </fieldset>
                                                    ' . (($this->gpo_id . $this->ur_id == 116) ? '
                                                <fieldset id="tRec" class="col-md-4 mb-4" style="padding-right: 1px !important;" style="display:inline">
                                                        <legend>Tipo de Recurso</legend>
                                                        <div class="input-group input-group-sm mb-3">
                                                        <input id="checkFederal" name="checkFederal" type="checkbox" value=1>&nbsp;Federal</input>&nbsp;&nbsp;&nbsp;
                                                        <input id="checkEstatal" name="checkEstatal" type="checkbox" value=1>&nbsp;Estatal</input>&nbsp;&nbsp;&nbsp;
                                                        <input id="cfi" name="cfi" type="hidden" value=""></input>&nbsp;&nbsp;&nbsp;
                                                        <input id="cfi_clave" name="cfi_clave" type="hidden" value=""></input>&nbsp;&nbsp;&nbsp;
                                                        </div>

                                                        <div class="input-group input-group-md">
                                                            <input id="cfi_campo" lastSearch="" name="cfi_campo" type="text"  placeholder="Clave de Financamiento" maxlength=12 class="form-control F1Catalogo" tabindex="10" disabled>
                                                            <span class="input-group-btn">
                                                                <button id="mostrarClaveFin" type="submit" class="btn btn-outline-secondary"><i class="fa fa-list-alt"></i></button>
                                                            </span>
                                                        </div>

                                                    </fieldset>' : '') . '
                                            </div>
                                            </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="poliza" role="tabpanel" aria-labelledby="poliza-tab">
                                    <div class="row">
                                        <fieldset class="col-md-12 col-xs-12">
                                            <fieldset class="col-md-12 col-xs-12">
                                            <br>
                                            <legend>Momento contable:</legend>
                                                ' . $this->ponCheck($mmc) . '
                                            </fieldset>
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                                    <div class="row">
                                        <fieldset class="col-md-3 col-xs-12">
                                            <fieldset class="col-md-12 col-xs-12">
                                                <legend>Documento fuente</legend>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="df_tdf"> Tipo Documento Fuente
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="df_id"> Identificador documento
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="df_rfc"> Rfc proveedor
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="df_ref"> Referencia
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="df_fref"> Fecha de la Referencia
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </fieldset>
                                         <fieldset class="col-md-3 col-xs-12">
                                            <fieldset class="col-md-12 col-xs-12">
                                                <legend>Agrupación</legend>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="fe" id="fe" value=""/> Flujo de Efectivo
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </fieldset>   
                                        <fieldset class="col-md-3 col-xs-12">
                                            <fieldset class="col-md-12 col-xs-12">
                                                <legend>Clave presupuestal</legend>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="cve_ur"> Ur
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="cve_ue"> Ue
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="cve_epg"> Encuadre programático
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="cve_pg"> Partida Generica
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="cve_pe" > Partida Espec&iacute;fica
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="cve_cfi"> Clave de Financiamiento
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </fieldset>
                                    </div>
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-12">
                                            <fieldset class="col-md-12 col-xs-12">
                                                <legend>Origen</legend>
                                                <div class="row ">
                                                    <div class="col-md-1 col-xs-1">
                                                        <div class="checkbox" >
                                                            <label>
                                                                <input type="checkbox" name="ori_ur" id="ori_ur"> Ur

                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 col-xs-2">
                                                        <input type="numero" id="ori_ur_id" name="ori_ur_id" class="form-control" maxlength="3" size="3"  style="display : none;">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-2 col-xs-2">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox"  id="ori_clc" name="ori_clc" > Clc
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 col-xs-2" >
                                                        <input type="numero" id="oriclc_id" name="oriclc_id" class="form-control"  maxlength="4" size="4" style="display : none;">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-2 col-xs-1">
                                                        <div class="checkbox" id="ori_cerecodiv" ' . (($unidadResponsable != 116) ? ' style="display : none;" ' : '') . '>
                                                            <label>
                                                                <input type="checkbox"  name="ori_cereco"  id="ori_cereco"> CERE-CO
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"  name="ori_afe"> Afectaci&oacute;n
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"  name="ori_cmp"> No. de compromiso
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </fieldset>
                                        <fieldset class="col-md-4 col-xs-12">
                                            <fieldset class="col-md-12 col-xs-12">
                                                <legend>Vinculaci&oacute;n</legend>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"  name="vin_ur"> Ur
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"  name="vin_clc"> Clc
                                                    </label>
                                                </div>
                                                <div class="checkbox" id="vin_cerecodiv" ' . (($unidadResponsable != 116) ? ' style="display : none;" ' : '') . '>
                                                    <label>
                                                        <input type="checkbox"  name="vin_cereco" id="vin_cereco"> CERE-CO
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"  name="vin_afe"> Afectaci&oacute;n
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"  name="vin_cmp"> No. de compromiso
                                                    </label>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"  name="vin_id"> Identificador documento
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </fieldset>
                                    </div>
                                    <div class="row">
                                        <fieldset class="col-md-12 col-xs-12">
                                            <fieldset class="col-md-6 col-xs-6">
                                                <div class="checkbox">
                                                    <label style="display: inline-block; width: 157px; text-align: right;">                                                        
                                                        <b>Conceptos Completos</b>
                                                        <input type="checkbox" name="cc" id="cc" value=""/>
                                                        <input type="hidden" name="ccm" id="ccm" value=""/>
                                                    </label>
                                                </div>
                                            </fieldset>
                                            <fieldset class="col-md-6 col-xs-6">
                                                <div class="checkbox">
                                                    <label style="display: inline-block; width: 157px; text-align: right;"> 
                                                        <b>Ordenar por fecha</b>
                                                        <input type="checkbox" name="of" id="of" value=""/>
                                                        <input type="hidden" name="ofe" id="ofe" value=""/>
                                                    </label>
                                                </div>
                                            </fieldset>
                                            <fieldset class="col-md-6 col-xs-6">
                                                <div class="checkbox">
                                                    <label style="display: inline-block; width: 157px; text-align: right;"> 
                                                        <b>Pólizas Canceladas</b>
                                                        <input type="checkbox" name="pc" id="pc" value=""/>
                                                        <input type="hidden" name="pc_" id="pc_" value=""/>
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        ';

        $html = str_replace(' />', ' />&nbsp;&nbsp;', $html);

        $this->mixHtml($html);
        $this->mixHtml(DibujaPantallaBuscarCuentaHTML());
        $this->mixScript(DibujaPantallaBuscarCuentaJS());
        $regresar = ''; 

        $this->agregaTools($regresar . $this->ponBoton($btnPDF) . ' ' . $this->ponBoton($btnEXCEL));
        $this->mixStyle('
            .mmc_class{
                width: 250px;
            }
        ');
        $this->mixScript('
            $("#regresarBtn").click(function(){
                $("div[aria-describedby=cargaDatosCuentas]").html("");
                $("div[aria-describedby=cargaDatosProveedor]").html("");
                $("nav").menusinpres("clickMenu");
            }	);
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                comunica("' . $this->pantalla . '", {
                    metodo: "muestraRangoCtas",
                    tipoBusqueda: $("input[name=radcta]:checked").val()
                });

            $("input[name=radcta]").change(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "muestraRangoCtas",
                    tipoBusqueda: $("input[name=radcta]:checked").val()
                });
            });

            $("#comboMesDesde").change(function(){
                if($(this).val()){
                    comunica("' . $this->pantalla . '", {
                        metodo: "mesHasta",
                        mes: $(this).val()
                    });
                }
            });

            $("#of").click(function(){
                if($("#of").is(":checked"))
                    $("#ofe").val("on");
                else
                    $("#ofe").val("");
            });

            $("#cc").click(function(){
                if($("#cc").is(":checked"))
                    $("#ccm").val("on");
                else
                    $("#ccm").val("");
            });

            $("#pc").click(function(){
                if($("#pc").is(":checked"))
                    $("#pc_").val("on");
                else
                    $("#pc_").val("");
            });

            $("#ori_ur").click(function(){
                if($("#ori_ur").is(":checked")   && ' . $unidadResponsable . '==116)
                    $("#ori_ur_id").show();
                else
                    $("#ori_ur_id").hide();
            });

            $("#ori_clc").click(function(){
                if($("#ori_clc").is(":checked"))
                    $("#oriclc_id").show();
                else
                    $("#oriclc_id").hide();
            });

            $(".btnImp").click(function(){
                var ban=0;
                var separar_nivelderegistro=0;
                ' . (($this->gpo_id . $this->ur_id == 116) ? '
                $("#cfi").val( $("#cfi_campo").attr("ecf") );
                $("#cfi_clave").val( $("#cfi_campo").val() );' : '') . '
                switch($("input[name=radcta]:checked").val()){
                    case "C":
                        if($("#comboMesDesde").val() == "" || $("#comboMesHasta").val() == ""){
                            notify("Debe seleccionar periodo","error");
                            return false;
                        }
                        else if($("#nombreCuenta").val() == "" && $("#cta").val().length >=5)
                        {
                            notify("La cuenta no existe en el Plan de Cuentas","error");
                            return false;
                        }
                        else if($("#cta").val()!="" && $("#cta").val().length >=5){
                            ban=1;
                            if( $("#nombreCuenta").attr("deue") != "" )
                                $("#deue").val($("#nombreCuenta").attr("deue"));
                        }
                        else{
                            notify("La cuenta debe tener por lo menos 5 digitos","warning");
                            return false;
                        }



                    break;
                                        case "M":
                                                separar_nivelderegistro=1;
                                                if($("#comboDesde").val()!="")
                            ban=1;
                        else
                            notify("Elegir un rango de cuentas","warning");
                                        break;
                    default:
                        if($("#comboDesde").val()!="")
                            ban=1;
                        else
                            notify("Elegir un rango de cuentas","warning");

                    break;
                }
                if(ban==1 && separar_nivelderegistro==0){
                    console.log(this.id);
                    switch(this.id){
                        case "pdf":
                            $("#formAuxiliar").attr("action","rep/doc/scg/reporte/reporte.php");
                        break;
                        case "excel":
                            $("#formAuxiliar").attr("action","rep/excel/scg/reporte/exp_Auxiliar.php");
                        break;
                    }

                    $(".mmc_class input:checked").each(function(){
                        this.value = this.id.split("_")[1];

                    });

                    $("#formAuxiliar").submit();
                }else if(ban==1 && separar_nivelderegistro==1){
                    var var_tipo=$(this).attr("id");
                    $("#tipo_rep").val(var_tipo);
                    console.log(var_tipo+"//");
                    comunica("' . $this->pantalla . '", {
                        metodo: "exportaExcelMasivo",
                        ctainicial:$("#cta").val(),
                        ctafinal:$("#cta2").val(),
                        deue:$("#comboue").val(),
                        tipo:var_tipo

                            });

                                }
                else
                    return false;
            });

            $("#linkPrincipal").click();

                ' . (($this->gpo_id . $this->ur_id == 116) ? '
                $("#cfi_campo").focus( function(e) {
                    cveFinID= "cfi_campo";
                    cveFinNom = "NomFin";
                });

                // F1 muestra el catálogo */
                $("#cfi_campo").keydown( function(e) {
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if ( key == 112 ) {
                        e.preventDefault();
                        dlgCveFin.dialog("open");
                    }
                });
                $("#mostrarClaveFin").click( function(e) {
                    cveFinID= "cfi_campo";
                    cveFinNom = "NomFin";
                    e.preventDefault();
                    dlgCveFin.dialog("open");
                });
                ' : '') . '


                $(document).on("click", "#sefipSidebar li", function (e){
                    e.preventDefault();


                    // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                    $("div[aria-describedby=cargaDatosMayor]").html("");
                    $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                    $(document).off("click", "#sefipSidebar li" );
                    return false;
                });
        ');
    }

    private function muestraRangoCtas()
    {

        $this->setContenedor('ctaRango');

        $comboDesde = new stdClass();
        $comboDesde->sql = "SELECT cgsc_cuenta FROM cat_CgSubcuenta";
        $comboDesde->id = $comboDesde->name = "comboDesde";
        $comboDesde->label = "Desde";
        $comboDesde->selecciona = 1;
        $comboDesde->required = true;
        $comboDesde->width = '220px';

        $comboHasta = new stdClass();
        $comboHasta->sql = "SELECT cgsc_cuenta FROM cat_CgSubcuenta";
        $comboHasta->id = $comboHasta->name = "comboHasta";
        $comboHasta->width = '220px';
        $comboHasta->required = true;
        $comboHasta->label = "Hasta";
        $comboHasta->selecciona = 1;

        $cuenta = new stdClass();
        $cuenta->id = 'cta';
        $cuenta->name = "cta";
        $cuenta->class = "F1Catalogo";
        $cuenta->label = "Cuenta";
        if ($this->prm->tipoBusqueda == "M") {
            $cuenta->blur = "ValidaCuenta2(1);";
        } else {
            $cuenta->blur = "ValidaCuenta();";
        }
        $cuenta->placeholder = "F1: Catalogo";
        $cuenta->maxlength = "24";
        $cuenta->enDiv = true;

        $cuenta2 = new stdClass();
        $cuenta2->id = 'cta2';
        $cuenta2->name = "cta2";
        $cuenta2->class = "F1Catalogo";
        $cuenta2->label = "Cuenta";
        $cuenta2->blur = "ValidaCuenta2(2);";
        $cuenta2->placeholder = "F1: Catalogo";
        $cuenta2->maxlength = "24";
        $cuenta2->enDiv = true;

        $nombreCuenta = new stdClass();
        $nombreCuenta->id = 'nombreCuenta';
        $nombreCuenta->name = "nombreCuenta";
        $nombreCuenta->label = "Nombre de la Cuenta";
        $nombreCuenta->maxlength = "100";
        $nombreCuenta->disabled = "disabled";
        $nombreCuenta->enDiv = true;
        $nombreCuenta->attr = new stdClass();
        $nombreCuenta->attr->deUE = "";

        $nombreCuenta2 = new stdClass();
        $nombreCuenta2->id = 'nombreCuenta2';
        $nombreCuenta2->name = "nombreCuenta2";
        $nombreCuenta2->label = "Nombre de la Cuenta";
        $nombreCuenta2->maxlength = "100";
        $nombreCuenta2->disabled = "disabled";
        $nombreCuenta2->enDiv = true;
        $nombreCuenta2->attr = new stdClass();
        $nombreCuenta2->attr->deUE = "";

        $claveFin = new stdClass();
        $claveFin->id = 'cfi';
        $claveFin->name = "cfi";
        $claveFin->label = "Clave Financiamiento";
        $claveFin->maxlength = "9";
        $claveFin->enDiv = true;
        $claveFin->autocomplete = true;
        $claveFin->tabla = 'enc_ClaveFinanciamiento';
        $claveFin->campoVal = "ecf_clave";
        $claveFin->desdecaracter = 4;
        $claveFin->value = '';
        $htm = '';

        switch ($this->prm->tipoBusqueda) {

            case 'R':
                $htm .= '
                    <div class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponCombo($comboDesde) . '
                        </fieldset>
                        <fieldset id="cHasta" class="col-md-3 col-xs-6">
                            ' . $this->ponCombo($comboHasta) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                    </div>
                ';
                break;
            case 'M':
                $htm .= '
                    <fieldset class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($cuenta) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($nombreCuenta) . '
                            <input type="hidden" name="deue" id="deue" value="">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">

                        </fieldset>
                    </fieldset>
                    <fieldset class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($cuenta2) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($nombreCuenta2) . '
                            <input type="hidden" name="deue" id="deue" value="">
                        </fieldset>

                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            <input id="tipo_rep" name="tipo_rep" type="hidden" >
                            <input id="masivo" name="masivo" type="hidden" value="1">
                        </fieldset>
                    </fieldset>
                ';
                break;
            case 'N':
                $htm .= '
                    <fieldset class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($cuenta) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($nombreCuenta) . '
                            <input type="hidden" name="deue" id="deue" value="">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">

                        </fieldset>
                    </fieldset>
                    <fieldset class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($cuenta2) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($nombreCuenta2) . '
                            <input type="hidden" name="deue" id="deue" value="">
                        </fieldset>
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                    </fieldset>
                ';
                break;

            case 'C':
                $htm .= '
                    <fieldset class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-3 col-xs-6">
                            ' . $this->ponInput($cuenta) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($nombreCuenta) . '
                            <input type="hidden" name="deue" id="deue" value="">
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-6">
                        </fieldset>
                    </fieldset>
                ';
                break;
        }

        $this->mixHtml($htm . '<fieldset id="contenedorBuscar" class="col-md-12 col-xs-12"></fieldset>');

        $this->mixScript('
            $("#cta").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                /* F1 llama a la pantalla para mostrar el catalogo de cuentas */
                if ( key == 112 ) {
                    e.preventDefault();

                    dlgC.dialog("open");
                }
            });

            $("#cta").focus( function(e) {
                ctaCuenta = "cta";
                ctaNombre = "nombreCuenta";
                ctaUe = "cta.ue";
            });

            $("#cta2").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                /* F1 llama a la pantalla para mostrar el catalogo de cuentas */
                if ( key == 112 ) {
                    e.preventDefault();

                    dlgC.dialog("open");
                }
            });

            $("#cta2").focus( function(e) {
                ctaCuenta = "cta2";
                ctaNombre = "nombreCuenta2";
                ctaUe = "cta.ue";
            });

            $("#comboDesde").change(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "CargaHasta",
                    numero: $(this).val()
                });
            });
            $("#combour").change(function(){
                var unidad = ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . ';
                if ( unidad == 116 ){
                    $("#vin_cerecodiv").show();
                    $("#ori_cerecodiv").show();
                }else{
                        $("#ori_cereco").attr("checked",false);
                        $("#vin_cereco").attr("checked",false);
                    if( $("#combour").val() == 116 ){
                        $("#vin_cerecodiv").show();
                        $("#ori_cerecodiv").show();
                    } else {
                        $("#vin_cerecodiv").hide();
                        $("#ori_cerecodiv").hide();
                    }
            }
                })
            ;

            function ValidaCuenta(){
                var valorCuenta = $("#cta").val();
                
                if (valorCuenta != ""){
                    if(!valorCuenta.includes(".")){
                        $("#cta").val( preparaCuentaContable( $("#cta").val(), 1 ) );
                        valorCuenta = $("#cta").val();
                    }
                    var regexSubCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1}\.\d{1,2}$/;
                    var regexCuentaCuarto = /^\d{1}\.\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{3}$/;
                    var regexCuentaQuinto = /^\d{1}\.\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{3}\.\d{2}$/;
                    var regexCuentaQuintoCta = /^\d{1}\.\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{3}\.\d{2}\.\d{4}$/;
                    var regexPlanCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{3}\.(?:[a-zA-Z0-9]){4,9}$/;

                    if( regexSubCuenta.test(valorCuenta) || regexCuentaCuarto.test(valorCuenta)
                    || regexPlanCuenta.test(valorCuenta) || regexCuentaQuinto.test(valorCuenta)
                    || regexCuentaQuintoCta.test(valorCuenta)){
                        if ($("#cta").val()!= "" && $("#cta").val().length >= 5) {
                                comunica("' . $this->pantalla . '",{
                                    metodo: "ValidaCuenta",
                                    cta_id: $("#cta").val(),
                                    });
                        } else {
                                if( $("#cta").val() == "" ) {
                                        /* notify( "La cuenta no puede quedar vacia", "warning" ); */
                                } else {
                                        $("#nombreCuenta").val("");
                                        notify( "La cuenta no es valida", "error" );
                                }
                        }
                    } else {
                        $("#nombreCuenta").val("");
                        notify( "La cuenta no es valida", "error" );
                    }
                }
            }
            function ValidaCuenta2(numero){
                                var numcampo="";

                                if(numero==2){
                                    numcampo="2";
                                }
                var valorCuenta = $("#cta"+numcampo).val();
                                console.log(valorCuenta+"adasd" );
                                if (valorCuenta != ""){
                                    if(!valorCuenta.includes(".")){
                                            $("#cta"+numcampo).val( preparaCuentaContable( $("#cta"+numcampo).val(), 1 ) );
                                            valorCuenta = $("#cta"+numcampo).val();
                                    }
                                
                                    var regexPlanCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{3}\.(?:[a-zA-Z0-9]){4,9}$/;

                                    if( regexPlanCuenta.test(valorCuenta)){
                                            if ($("#cta"+numcampo).val()!= "" && $("#cta"+numcampo).val().length >= 5) {
                                                    comunica("' . $this->pantalla . '",{ metodo: "ValidaCuenta",
                                                        cta_id: $("#cta"+numcampo).val(),
                                                        campo2:numcampo
                                                        });
                                            } else {
                                                    if( $("#cta"+numcampo).val() == "" ) {
                                                            /* notify( "La cuenta no puede quedar vacia", "warning" ); */
                                                    } else {
                                                            $("#nombreCuenta"+numcampo).val("");
                                                            notify( "La cuenta no es valida", "error" );
                                                    }
                                            }
                                    } else {
                                            $("#nombreCuenta"+numcampo).val("");
                                            notify( "La cuenta no es valida, se requiere la cuenta a nivel de Registro", "error" );
                                    }
                                }
            }
        ');
    }

    private function CargaHasta()
    {

        $this->setContenedor('chasta');
        if (isset($this->prm->numero)) {
            $numero = $this->prm->numero;
            $comboHasta = new stdClass();
            $comboHasta->sql = "SELECT cgsc_cuenta FROM cat_CgSubcuenta WHERE cgsc_cuenta >= ? ";
            $comboHasta->params = array($numero);
            $comboHasta->id = "comboHasta";
            $comboHasta->width = "150px";
            $comboHasta->label = "Hasta";
            $comboHasta->selecciona = $numero;

            $this->mixHtml($this->ponCombo($comboHasta));
        }
        $this->mixScript('

        ');
    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');
        if (isset($this->prm->mes)) {
            $mes = $this->prm->mes;

            $comboMesHasta = new stdClass();
            $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable WHERE msc_id >= ? ";
            $comboMesHasta->params = array($mes);
            $comboMesHasta->id = "comboMesHasta";
            $comboMesHasta->width = "150px";
            $comboMesHasta->label = "Hasta";
            $comboMesHasta->selecciona = $mes;

            $this->mixHtml($this->ponCombo($comboMesHasta));
        }
    }

    private function ValidaCuenta()
    {

        $campo2 = (isset($this->prm->campo2) ? $this->prm->campo2 : 0);
        $ger = '';
        $cgp = '';
        $rub = '';
        $cgcu = '';
        $cgsc = '';
        $cgq = '';
        $cgc = '';
        $cta = '';

        $arrayCuenta = explode('.', $this->prm->cta_id);
        $cgm = $arrayCuenta[0] . $arrayCuenta[1] . $arrayCuenta[2] . $arrayCuenta[3] . $arrayCuenta[4];

        if (strlen($cgm) >= 5) {

            $ger = substr($cgm, 0, 1);
            $cgp = substr($cgm, 1, 1);
            $rub = substr($cgm, 2, 1);
            $cgcu = substr($cgm, 3, 1);
            $cgsc = substr($cgm, 4);

            if (isset($arrayCuenta[5])) {
                $cgc = $arrayCuenta[5];
            }

            if (isset($arrayCuenta[6])) {
                $cgq = $arrayCuenta[6];
                if (strlen($cgq) > 2) {
                    $cta = $cgq;
                }

            }

            if (isset($arrayCuenta[7])) {
                $cta = $arrayCuenta[7];
            }

            // Todos los elementos DEBEN ser números, si no truena el SQL
            if (!is_numeric($ger) || !is_numeric($cgp) || !is_numeric($rub) || !is_numeric($cgcu)
                || !is_numeric($cgsc)) {
                $this->mixScript('
                    notify("Todos los elementos de la cuenta de mayor DEBEN ser números");
                ');
                return false;
            }

            $sql = '';
            $params = [];
            if (strlen($cgq . $cta) == 6) {
                $cta = $cgq . $cta;
            }

            if (strlen($cta) >= 4) {
                $sql = '
                select cta_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id, cgc_id, cta_id, cta_ue_id
                from cg_PlanCuentas
                where cta_gpo_id = :gpo AND cta_ur_id = :ur
                    ' . ((isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "")
                    ? ' AND cta_ue_id = ' . $_SESSION[_S]['clasAdm-ue'] : '') . '
                    AND ger_id = :ger AND cgp_id = :cgp AND rub_id = :rub AND cgcu_id = :cgcu AND cgsc_id = :cgsc
                    AND cgc_id = :cgc
                    AND cta_id = :cta
                ';
                $params = [':gpo'=>$_SESSION[_S]['clasAdm-gpo'],':ur' =>  $_SESSION[_S]['clasAdm-ur'],':ger'=>$ger,':cgp' => $cgp,':rub' => $rub,
                            ':cgcu' => $cgcu,':cgsc' => $cgsc, ':cgc' => $cgc, ':cta' => $cta];
            } elseif (strlen($cgq) == 2) {

                $sql = '
                select cgq_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id, cgq_id
                from cg_CuentaQuinto
                where ger_id = :ger AND cgp_id = :cgp AND rub_id = :rub AND cgcu_id = :cgcu AND cgsc_id = :cgsc
                    AND cgc_id = :cgc
                    AND cgq_id = :cgq
                ';
                $params = [':ger' => $ger,':cgp' => $cgp,':rub' => $rub,':cgcu' => $cgcu,':cgsc' => $cgsc, ':cgc' => $cgc, ':cgq' => $cgq];
            } elseif (strlen($cgc) == 3) {
                $sql = '
                select cgc_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id, cgc_id
                from cg_CuentaCuarto
                where ger_id = :ger AND cgp_id = :cgp AND rub_id = :rub AND cgcu_id = :cgcu AND cgsc_id = :cgsc
                    AND cgc_id = :cgc 
                ';
                $params = [':ger' => $ger,':cgp' => $cgp,':rub' => $rub,':cgcu' => $cgcu,':cgsc' => $cgsc, ':cgc' => $cgc];
            } elseif (strlen($cgm) >= 5) {
                $sql = '
                select cgsc_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id
                from cat_CgSubcuenta
                where ger_id = :ger AND cgp_id = :cgp AND rub_id = :rub AND cgcu_id = :cgcu AND cgsc_id = :cgsc
                ';
                $params = [':ger' => $ger,':cgp' => $cgp,':rub' => $rub,':cgcu' => $cgcu,':cgsc' => $cgsc];
            }
            if ($sql != '') {
                $this->conectar();
                $this->setSql($sql);
                $this->consultar($params);
                $this->desconectar();
                $indicecampo = "";
                if ($campo2 == 2) {
                    $indicecampo = "2";
                }
                if (!($row = $this->extraer())) {
                    $this->mixScript('
                        $("#nombreCuenta' . $indicecampo . '").val("");
                        notify( "La cuenta no existe en el Plan de Cuentas", "error" );
                    ');
                } else {
                    $cta = $row['cuenta'] ?? '';
                    $cta_ue_id = $row['cta_ue_id'] ?? '';
                    $this->mixScript('
                        $("#nombreCuenta' . $indicecampo . '").val("' . $cta . '");
                        $("#nombreCuenta' . $indicecampo . '").attr("deue", "' . $cta_ue_id . '");
                        $("#deue").val("' . $cta_ue_id . '");
                        if($("#deue").val()){
                            $("#comboue").selectpicker("val", "' . $cta_ue_id . '");
                        } else {
                            $("#comboue").prop("disabled", false);
                        }
                        $("#mySelect").selectpicker("render");
                    ');
                }
            } else {
                $this->mixScript('notify("Por favor, escribe una cuenta contable con el formato X.X.X.X.X.XXX.XXXX o utiliza el buscador de cuentas con F1");');
            }
        } else {
            $this->mixScript('notify("Por favor, escribe una cuenta contable válida");');
        }
    }

    private function exportaExcelMasivo()
    {
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $ctainicial = isset($this->prm->ctainicial) ? $this->prm->ctainicial : '';
        $ctafinal = isset($this->prm->ctafinal) ? $this->prm->ctafinal : '';

        if (!$this->reportemasivo) {
            $this->mixScript('notify("No tiene Permisos para generar reportes masivos","error")');
            return false;
        }

        if ($ctafinal == "" || $ctainicial == "") {
            $this->mixScript('notify("Los campos de cuenta no pueden ir vacios","error")');
            return false;
        }
        if ($ctafinal < $ctainicial) {
            $this->mixScript('notify("Favor de revisar el orden de las cuentas","error")');
            return false;
        }
        $data = $this->DameCuentaRegistro();

        if (!isset($data[0]) || $data[0] == "") {
            $this->mixScript('notify("No cuenta con nivel de registro","error")');
            return false;
        }

        $this->mixScript('

            $(function () {
                var num_reg = ' . count($data) . ';
                if (num_reg > 0 )    {
                    alertify.confirm(
                        "REPORTE AUXILIAR MASIVO",
                        "Se <b>generarán</b> un total de <b> '.count($data).' reportes auxiliares </b> de la <b>Cuenta Inicial ' . $ctainicial . '</b> a la <b>Cuenta Final '.$ctafinal.'</b> , ¿está seguro de continuar?",
                        function(){
                            $("#formAuxiliar").attr("action","rep/excel/scg/reporte/exp_AuxiliarMasivo.php");
                            $("#formAuxiliar").submit();
                        },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }else {
                    alertify.alert("REPORTE AUXILIAR MASIVO", "No existen niveles de registros en este rango.").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }
            });
        ');

        return false;
    }

    private function DameCuentaRegistro()
    {

        $ctainicial = isset($this->prm->ctainicial) ? $this->prm->ctainicial : '';
        $ctafinal = isset($this->prm->ctafinal) ? $this->prm->ctafinal : 0;
        $deue = (isset($this->prm->deue) && $this->prm->deue != "") ? $this->prm->deue : "";

        $gpo_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $ur_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
        $ue_id = '';

        if ($_SESSION[_S]['nvj_id'] == 1) {
            $ue_id = $_SESSION[_S]['ue_id'];
        } else {
            $ue_id = (($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur']
                == 116 && isset($_SESSION[_S]['clasAdm-ue']) && !is_null($_SESSION[_S]['clasAdm-ue']))
                ? $_SESSION[_S]['clasAdm-ue'] : '%');
        }

        if ($deue != "" && $ue_id == '%') {
            $ue_id = $deue;
        }
        $ctainicial = isset($this->prm->ctainicial) ? $this->prm->ctainicial : '';
        $ctafinal = isset($this->prm->ctafinal) ? $this->prm->ctafinal : 0;
        if ($this->reportemasivo) {
            $datos = cuentasRegistro($gpo_id, $ur_id, $ue_id, $ctainicial,
                $ctafinal);
        } else {
            $datos[0] = '';
        }

        return $datos;
    }
}
//aqui
if (isset($_SESSION[_S]['prm']['C']['10']['1']['1'])) {
    new ControladorAuxiliar();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
