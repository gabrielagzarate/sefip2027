<?php
set_time_limit(0);

//librerias
session_start();
require_once dirname(__FILE__).'/../../lib/getDocumento.php';
require_once dirname(__FILE__).'/../../lib/opcionConteo.php';
require_once dirname(__FILE__).'/../../lib/pintaClc.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaMinistrado.php';
require_once dirname(__FILE__).'/../../lib/scg/vinculacion/2023/vinculacionMinistrado2023.php';
require_once dirname(__FILE__).'/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__).'/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__).'/../../lib/validaFecha.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__).'/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoAutorizado2023.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoCancelado2023.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereEgresoAutorizado2023.php';
class ControladorMinistrado2023 extends WebResponse
{
    protected $escritura;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $gpo_id_2023;
    protected $ur_id_2023;
    protected $usr_ip;
    protected $usr_id;
    protected $origen;

    //Constructor
    public function __construct()
    {
        
        //mandar a llamar constructor base
        parent::__construct();
        
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['12']['21']['2']) ? TRUE
                : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = FALSE;
        }

        $parametro = [$this->gpo_id,$this->ur_id];

        $sql = "SELECT
                gpo_id_2023 AS gpo_id, ur_id_2023 AS ur_id
                FROM ".BD_SINPRES.".vin_UnidadResponsable_2024_2025 vur2025
                INNER JOIN ".BD_SINPRES.".vin_UnidadResponsable_2023_2024 vur2024
                    ON vur2025.gpo_id_2024 = vur2024.gpo_id_2024 AND vur2025.ur_id_2024 = vur2024.ur_id_2024
                WHERE vur2025.gpo_id_2024 = ? AND vur2025.ur_id_2024 = ? ;
            ";
        $conexion = new AccesoDatos();
        $conexion->setSql($sql);
        $conexion->conectar();
        $conexion->consultar($parametro);
        $data = $conexion->extraer();
        $conexion->desconectar();
        $this->gpo_id_2023 = $data['gpo_id'];
        $this->ur_id_2023 = $data['ur_id'];
        unset($conexion);

        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? 
            $_SESSION[_S]['ses_ip'] : 
            'ERROR';

        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
                $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

        switch ($this->metodo) {
            case 'DibujaPantalla': $this->DibujaPantalla();
                break;
            case 'MuestraDatos': $this->MuestraDatos();
                break;
            case 'tablaVinculacionMin2023': $this->tablaVinculacionMin2023();
                 break;
            case 'Documento': $this->Documento();
                break;
            case 'GeneraPoliza': $this->GeneraPoliza();
                break;
            case 'PonBarraBotones': $this->PonBarraBotones();
                break;
            default:
                opcionConteo(
                    $_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'], 
                    $_POST['opcion']['opc_id']
                );
                if ($this->origen == 'clc') {
                    $this->PonBarraBotones();
                } else {
                    $this->PonBarraBotones();
                    $this->prm->seleccion = "clc";
                }
                break;
        }

        $this->returnJson();
    }

    private function PonBarraBotones()
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] < 3) {
            $this->mixScript('$("#toggleCA").hide();'); // ocultamos la unidad responsable si el nivel de usuario es menor a 3
        }
        // Botones y opciones que se dibujarán en la barra de botones
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = " btn btn-outline-info";
        $btnLimpiar->icon = "fa fa-refresh";

        $clcFiltro = FALSE;
        $bdoFiltro = FALSE;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = TRUE;
                break;
            case "bdo":
                $bdoFiltro = TRUE;
                break;
            default:
                if (isset($this->prm->clcFiltro) && $this->prm->clcFiltro == 1) {
                    $clcFiltro = TRUE;
                }
                if (isset($this->prm->bdoFiltro) && $this->prm->bdoFiltro == 1) {
                    $bdoFiltro = TRUE;
                }
                break;
        }

        $inputGur = new StdClass();
        $inputGur->id = 'inputGur';
        $inputGur->name = 'inputGur';
        $inputGur->placeholder = 'UR';
        $inputGur->enDiv = true;
        $inputGur->class = 'form-control';
        $inputGur->value = (isset($this->prm->gur)? $this->prm->gur : "" );
        $inputGur->maxlength = '3';

        $inputClc_id = new StdClass();
        $inputClc_id->id = 'inputClc_id';
        $inputClc_id->name = 'inputClc_id';
        $inputClc_id->placeholder = 'CLC';
        $inputClc_id->enDiv = true;
        $inputClc_id->class = 'form-control';
        $inputClc_id->value = ($clcFiltro ? $this->prm->clc_id : "" );
        $inputClc_id->maxlength = '6';

        $inputBdo_id = new StdClass();
        $inputBdo_id->id = 'inputBdo_id';
        $inputBdo_id->name = 'inputBdo_id';
        $inputBdo_id->placeholder = '# Documento';
        $inputBdo_id->enDiv = true;
        $inputBdo_id->class = 'form-control';
        $inputBdo_id->value = ( $bdoFiltro ? $this->prm->bdo_id : "" );
        $inputBdo_id->maxlength = '7';

        $inputRfc_id = new StdClass();
        $inputRfc_id->id = 'inputRfc_id';
        $inputRfc_id->name = 'inputRfc_id';
        $inputRfc_id->placeholder = 'RFC';
        $inputRfc_id->enDiv = true;
        $inputRfc_id->class = 'form-control';
        $inputRfc_id->value = ( $bdoFiltro ? $this->prm->rfc_id : "" );
        $inputRfc_id->maxlength = '20';

        $inputBdo_referencia = new StdClass();
        $inputBdo_referencia->id = 'inputBdo_referencia';
        $inputBdo_referencia->name = 'inputBdo_referencia';
        $inputBdo_referencia->placeholder = 'Referencia';
        $inputBdo_referencia->enDiv = true;
        $inputBdo_referencia->class = 'form-control';
        $inputBdo_referencia->value = ( $bdoFiltro ? $this->prm->bdo_referencia : "" );
        $inputBdo_referencia->maxlength = '20';

        $botones = '
                <div class="row mb-4 class="col-md-12 col-xs-12">
                    '.($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 ? '<fieldset class="col-md-3 col-xs-3" id="">
                        '.$this->ponInput($inputGur).'
                    </fieldset>' : '').'
                    <fieldset class="col-md-3 col-xs-3" id="">
                        '.$this->ponInput($inputClc_id).'
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3" id="">
                        '.$this->ponInput($inputBdo_id).'
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3" id="">
                        '.$this->ponInput($inputRfc_id).'
                    </fieldset>
                    <fieldset class="col" id="">
                        '.$this->ponInput($inputBdo_referencia).'
                    </fieldset>
                    <!-- <fieldset class="col-xs-2" style="padding-right: 1px !important;">
                        <button id="excelMinistrado2023" class=" btn btn-outline-info" data-style="slide-left"><i class="fa fa-cloud-download"></i></button>
                    </fieldset> -->
                </div>
                </div>
            ';

        $this->agregaTools($botones);
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $paraMes = [$this->gpo_id,$this->ur_id];
        $this->conectar();
        $this->setSql('
                SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
                FROM cat_MesContable
                    INNER JOIN cg_MesContable USING( msc_id )
                WHERE gpo_id = ? AND ur_id = ?
                    AND msc_id NOT IN ( 0, 13 )
            ');
        $this->consultar($paraMes);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) $inicio = 'class="active"';
            else $inicio = '';

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li opcion="'.$row['msc_id'].'" '.$inicio.' ultimo>
                        <div></div><a class="meses nav-link" mes="'.$row['msc_id'].'" estado="'.$row['estado'].'"> '.$row['msc_alias'].' </a> lastdiv
                    </li>
                ';

            $contenedor .= '
                    <div id="contenedor'.$row['msc_id'].'" style="display:none" class="contenedores"></div>
                ';
            
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);


        $this->mixHtml('
                <div id="page-tabs">
                    <ul class="nav nav-tabs">
                        '.$pestana.'
                    </ul>
                </div>
            ');

        

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript('
                        $("#btnRegresa").click(function(e) {
                            $("nav").menusinpres("clickMenu");
                        });
                        '.( ( $_SESSION[_S]['nvj_id'] < 3 ) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                            : '').'
                    ');

                $this->mixScript('
                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
                            mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val()
                        });
                    ');
                break;
            case 'clc':
                $this->mixScript('
                        $("#btnRegresa").click(function(e) {
                            e.preventDefault();
                            /* al parecer para vinculación ya no se habilitarán las opciones
                            $("#btnRegresa").attr("origen", "")
                            $(".navbar").hide();
                            $("#vclcPantallaCompleta").show(); */
                            e.stopPropagation();
                        });

                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val()
                        });
                    ');
                break;
        }

        $this->mixScript('
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : '.$_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'].'" );
                $("#opciones").click( function(e) {
                    comunica("'.$this->pantalla.'", {
                        metodo: "Filtro",
                        origen: "'.$this->origen.'",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                });

                $("#btnLimpiar").click( function(e) {
                    $("#inputGur").val("");
                    $("#inputClc_id").val("");
                    $("#inputBdo_id").val("");
                    $("#inputRfc_id").val("");
                    $("#inputBdo_referencia").val("");

                    comunica(
                        "' . $this->pantalla . '",
                        {
                            metodo: "MuestraDatos",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val()
                        }
                    );

                    e.stopPropagation();
                    return false;
                });

                $("#excelMinistrado2023").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/fondos/exp_Ministrado2023.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "'.(isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "" ).'" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                    $("body").append(form);
                    form.submit();
                });

                $("#imprimirMinistrado2023").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))
                            .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "'.(isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "" ).'" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value",  "inputGur").attr("value", '.$this->gpo_id_2023.$this->ur_id_2023.'))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                        $("body").append(form);
                        form.submit();
                });

                // Programamos una combinación de ESC ESC para disparar el botón REGRESAR
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#btnHomeMenu").click();
                    e.stopPropagation();
                });

            ');

        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $this->tablaVinculacionMin2023();

        $this->mixScript('
                $("#page-tabs a").click( function(e) {
                    e.preventDefault();
                    var seleccionado = $(this);
                    $("#page-tabs li").each (function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("li").addClass("active");

                    comunica("'.$this->pantalla.'",{
                        metodo: "MuestraDatos",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });

                    e.stopPropagation();
                    return false;
                });
            ');

        $this->mixScript('
                $("#tbody-tabla-tbl_Ministrado2023").on("dblclick", "tr.seleccionable", function (e) {
                e.preventDefault();
                e.stopPropagation();

                const row = $("#tbl_Ministrado2023").tabla("getFilaSeleccion");

                    // Si no hay nada que selecionar, no hacemos nada
                    if ( !row) {
                        e.stopPropagation();
                        return false;
                    }
                    
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if ( $("#inputClc_id").val() == "" ) {
                        var_clcFiltro = 2;
                    }
                    if ( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ) {
                        var_bdoFiltro = 2;
                    }

                    comunica("'.$this->pantalla.'", {
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        clc_id: $("#inputClc_id").val(),
                        clcFiltro: var_clcFiltro,
                        gur: row.gur,
                        afe_id: row.afe,
                        bdo_id: row.bdo,
                        tdf_id: row.tdf,
                        rfc_id: row.rfc,
                        bdo_referencia: row.referencia,
                        bdoFiltro: var_bdoFiltro,
                        agrupador: row.agrupador
                    });

                    e.stopPropagation();
                });

            ');

        if ($this->origen == 'clc') {
            $this->mixScript('
                $("#page-tabs").hide();
                $("#inputBdo_id").hide();
                $("#inputRfc_id").hide();
                $("#inputBdo_referencia").hide();
            ');
        }
    }

    private function tablaVinculacionMin2023 ()
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl_Ministrado2023';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'MuestraDatos';
        $tabla->esCompacta = true;
        $tabla->seleccionable = true;

        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'AFE';
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'DOC';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TDF';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'RFC';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = "center";
        $th->width = '25px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TDF';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'REFERENCIA MINISTRADO';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ESTADO';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'AUTORIZACIÓN';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CANCELACIÓN';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'POL. AUT.';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'POL. CANC.';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->align = "center";
        $th->width = '15px';
        array_push($tr, $th);

        //cuerpo tabla

        $trbody = array();
        
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gur']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['afe']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['rfc']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['referencia']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['concepto']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id_ministrado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['referenciaMinistrado']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['importe'];
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['estado']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizacion']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelacion']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaAutorizado']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizado']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaCancelado']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelado']; 
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
    }

    private function MuestraDatos()
    {
        $data = $this->DameInformacion();

        return false;
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id_2023.$this->ur_id_2023);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode("," , str_replace(', ', ',', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = ''; // busqueda por clave de financiamiento
        $ClaveFinanciamiento['ter_id'] = ''; 

        $niveles[9] = 9; 
        $niveles[8] = 0;
        $niveles[7] = 0;
        $niveles[6] = 6;
        $niveles[5] = 0;
        $niveles[4] = 0;
        $niveles[3] = 0;
        $niveles[2] = 0;
        $niveles[1] = 1;

        $detalle = FALSE;
        $sql_bandera= TRUE;

        $datos = vinculacionMinistrado2023($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'B', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
            "bdo_referencia" => $bdo_referencia, "sql_bandera" => $sql_bandera));

        $this->conectar();
        $this->setSql($datos['sql']);
        $this->setParams($datos['params']);
        $this->consultar($datos['params']);
        $this->desconectar();
        
        //crear variable nav
        
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : -1;
        $nav->idTabla = 'tbl_Ministrado2023';

         if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }

        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $paraConta = [$this->gpo_id,$this->ur_id];
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $conexion->consultar($paraConta);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');

        $clc_array = array();
        $generaPolizaAutorizado = FALSE;
        $generaPolizaCancelado = FALSE;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = FALSE;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->class = 'me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->icon = 'circle-left me-2';

        $botones = $this->ponBoton($btnRegresa); 
        $this->agregaTools($botones);

        $documento = getDocumento($gpo_id_documento, $ur_id_documento,
            $bdo_id_documento, $this->prm->rfc_id, $this->prm->bdo_referencia,
            array("ejercicio" => 2023, "tdf_id" => $this->prm->tdf_id, "mmc_id" => "B"));
        $html = '<legend>Análisis del documento 2023 a generar la póliza del ministrado</legend>';
        $html .= '
                <div>
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Información del Documento</legend>
                        <table class="table table-bordered">
                            <tr class="success">
                                <th>Proveedor</th>
                                <th>Referencia</th>
                                <th>Concepto</th>
                                <th>Percepción Presupuestal</th>
                                <th>Percepción Contable</th>
                                <th>Retención Contable</th>
                                <th>Fecha</th>
                            </tr>
                            <tr>
                                <td>'.$documento->rfc_id."-".$documento->nombreProveedor.'</td>
                                <td>'.$documento->tipoDocumentoFuente."-".$documento->referencia.'</td>
                                <td>'.$documento->concepto.'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->percepcionPresupuestal,2).'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->percepcionContable,2).'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->retencionContable,2).'</td>
                                <td>'.$documento->fechaDocumento.'</td>
                            </tr>
                        </table>
                    </fieldset><br/>
            ';

        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion

        if(!empty($documento->detalleMinistrado2023)){
            foreach ($documento->detalleMinistrado2023 as $key => $value) {
                if ($clc_id_documento != '') {
                    if ($clc_id_documento == $value->clc_id) {
                        $clc_array[$value->clc_id] = $value->clc_id;
                    }
                } else {
                    $clc_array[$value->clc_id] = $value->clc_id;
                }
    
                if ($this->prm->agrupador == $value->agrupador) {
                    $tipoministrado = $value->tipoMinistracion;
                    if ($value->tipoMinistracion != 2) {
                        $generaPolizaAutorizado = TRUE;
                        $fechaAutorizado = $value->fechaAutorizado;
                    }
                    $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado,
                            2, '0', STR_PAD_LEFT)]['ems_id'];
                    if ($value->estado == 'C') {
                        if ($generaPolizaAutorizado) {
                            $generaPolizaCancelado = TRUE;
                            $fechaCancelado = $value->fechaCancelado;
                        }
                        $estadoMesCancelado = $mesContable[str_pad($value->mesCancelado,
                                2, '0', STR_PAD_LEFT)]['ems_id'];
                    }
                }
            }
    
            /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
            if (count($clc_array) != 0) {
                $html .= '
                        <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                            <legend>CLC en la que esta el documento
                                <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                            </legend>
                    ';
                foreach ($clc_array AS $clc_id) {
                    $clcDocumento = pintaClc($gpo_id_documento, $ur_id_documento,
                        $clc_id,
                        array("ejercicio" => 2023, "bdo_id" => $bdo_id_documento, "todo" => TRUE));
                    $html .= $clcDocumento->html;
                }
                $html .= '
                        </fieldset></br>
                    ';
            }
    
            /* DATOS DE LA MINISTRACIÓN */
            $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>Datos de la Ministración
                            <span id="ministrado" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                ';
            $ministrado = pintaMinistrado($gpo_id_documento, $ur_id_documento,
                $bdo_id_documento,
                array("ejercicio" => 2023, "clc_id" => $clc_id_documento,));
            $html .= $ministrado->html;
            $html .= '
                    </fieldset></br>
                ';
            if ($tipoministrado == 1 || $tipoministrado == 4 || $tipoministrado == 5
                || $tipoministrado == 6 || $tipoministrado == 7 || $tipoministrado == 8
                || $tipoministrado == 9) {
                /*             * *********************************** Información de la Póliza Contable ************************************************************** */
                $polizas = getPolizaAgrupador(
                    $this->gpo_id, 
                    $this->ur_id,
                    $this->prm->agrupador,
                    '',
                    2023
                );
    
                $polizaAut = new stdClass;
                $botonGeneraPa = '';
    
                //preguntamos los datos de la póliza contable de autorización
                if ($polizas->valido) {
                    foreach ($polizas->polizas as $key => $value) {
                        if ($value->tipoMomento == 'A') {
                            $polizaAutorizado[$key] = getPoliza($value->gpo_id,
                                $value->ur_id, $value->ue_id, $value->msc_id,
                                $value->pot_id, $value->prf_id, $value->pma_id);
                            $polizaAut->valido = $polizaAutorizado[$key]->valido;
                        }
                    }
                }
                if (!isset($polizaAut->valido)) {
                    $polizaAut->valido = FALSE;
                }
                //si existe una póliza
                if (isset($polizaAutorizado)) {
                    foreach ($polizaAutorizado as $key => $value) {
                        if ($value->valido) {
                            $polizaAutorizadoHtml = pintaPoliza($polizaAutorizado[$key]);
                            $html .= $polizaAutorizadoHtml->html;
                            //si el mes NO esta cerrado
                            if ($this->escritura) {
                                if ($estadoMesAutorizado == 'A') {
                                    //podra regenerar la póliza contable
                                    $btnGenera = new stdClass();
                                    $btnGenera->id = 'btnReGeneraPolizaA';
                                    $btnGenera->name = 'btnReGeneraPolizaA';
                                    $btnGenera->text = 'Re-Genera Póliza de Autorización';
                                    $btnGenera->value = 'Re-Genera Póliza de Autorización';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
    
                                    $this->mixScript('
                                        $("#btnReGeneraPolizaA").click(function() {
                                            alertify.confirm("RE HACER LA POLIZA '.(isset($value->poliza) ? $value->poliza : '').'","Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                                function(){
                                                    comunica("'.$this->pantalla.'",{
                                                        metodo: "GeneraPoliza",
                                                        origen: "'.$this->origen.'",
                                                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                        mes: '.$this->prm->mes.',
                                                        gur: '.$this->prm->gur.',
                                                        clc_id: "'.$this->prm->clc_id.'",
                                                        clcFiltro: '.$this->prm->clcFiltro.',
                                                        bdo_id: '.$this->prm->bdo_id.',
                                                        tdf_id: "'.$this->prm->tdf_id.'",
                                                        rfc_id: "'.$this->prm->rfc_id.'",
                                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                                        gpo_id: "'.$value->gpo_id.'",
                                                        ur_id: "'.$value->ur_id.'",
                                                        ue_id: "'.$value->ue_id.'",
                                                        fecha: "'.$fechaAutorizado.'",
                                                        agrupador: "'.$this->prm->agrupador.'",
                                                        tmc_id: "A"
                                                    });
                                                }, 
                                                function(){ }
                                            ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                        });
                                    ');
                                } else {
                                    $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado </div>';
                                }
                            } 
                        }
                    }
                }
    
                $polizaCan = new stdClass;
                $botonGeneraPc = '';
    
                //preguntamos los datos de la póliza contable de cancelación
                if ($polizas->valido) {
                    foreach ($polizas->polizas as $key => $value) {
                        if ($value->tipoMomento == 'C') {
                            $polizaCancelado[$key] = getPoliza($value->gpo_id,
                                $value->ur_id, $value->ue_id, $value->msc_id,
                                $value->pot_id, $value->prf_id, $value->pma_id);
                            $polizaCan->valido = $polizaCancelado[$key]->valido;
                        }
                    }
                }
                if (!isset($polizaCan->valido)) {
                    $polizaCan->valido = FALSE;
                }
    
                //si existe tal póliza
                if (isset($polizaCancelado)) {
                    foreach ($polizaCancelado as $key => $value) {
                        if ($value->valido) {
                            $polizaCanceladoHtml = pintaPoliza($polizaCancelado[$key]);
                            $html .= $polizaCanceladoHtml->html;
                            //si el mes cpntable NO esta cerrado
                            if ($this->escritura) {
                                if ($estadoMesCancelado == 'A') {
                                    //podra regenerar la póliza contable
                                    $btnGenera = new stdClass();
                                    $btnGenera->id = 'btnReGeneraPolizaC';
                                    $btnGenera->name = 'btnReGeneraPolizaC';
                                    $btnGenera->text = 'Re-Genera Póliza de Cancelación';
                                    $btnGenera->value = 'Re-Genera Póliza de Cancelación';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
                                    $botonGeneraPc = $this->ponBoton($btnGenera);
    
                                    $this->mixScript('
                                        $("#btnReGeneraPolizaC").click(function() {
                                            alertify.confirm("RE HACER LA POLIZA '.(isset($value->poliza) ? $value->poliza : '').'","Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                                function(){
                                                    comunica("'.$this->pantalla.'",{
                                                        metodo: "GeneraPoliza",
                                                        origen: "'.$this->origen.'",
                                                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                        mes: '.$this->prm->mes.',
                                                        gur: '.$this->prm->gur.',
                                                        clc_id: "'.$this->prm->clc_id.'",
                                                        clcFiltro: '.$this->prm->clcFiltro.',
                                                        bdo_id: '.$this->prm->bdo_id.',
                                                        tdf_id: "'.$this->prm->tdf_id.'",
                                                        rfc_id: "'.$this->prm->rfc_id.'",
                                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                                        gpo_id: "'.$value->gpo_id.'",
                                                        ur_id: "'.$value->ur_id.'",
                                                        ue_id: "'.$value->ue_id.'",
                                                        fecha: "'.$fechaCancelado.'",
                                                        agrupador: "'.$this->prm->agrupador.'",
                                                        tmc_id: "C"
                                                    });
                                                }, 
                                                function(){ }
                                            ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                        });
                                    ');
                                } else {
                                    $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                                }
                            }
                        }
                    }
                }
    
                $botones = '';
                if ($this->escritura) {
                    //si deberia generar la póliza y NO existe
                    if ($generaPolizaAutorizado && !$polizaAut->valido) {
                         $tipoministrado = 0;
                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->text = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->value = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';
    
                        //si el mes contable se encuentra abierto
                        if ($estadoMesAutorizado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function() {
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.(isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        bdo_id: '.$this->prm->bdo_id.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        fecha: "'.$fechaAutorizado.'",
                                        agrupador: "'.$this->prm->agrupador.'",
                                        tmc_id: "A"
                                    });
                                });
                            ');
                        } else {
                            //en caso contrario, debera pedir la fecha donde debe generarse
                            $btnGeneraPolizaCerrado = new stdClass();
                            $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->text = 'Genera Póliza de Autorización';
                            $btnGeneraPolizaCerrado->value = 'Genera Póliza de Autorización';
                            $btnGeneraPolizaCerrado->color = 'verde';
                            $btnGeneraPolizaCerrado->icon = 'ok';
    
                            $inputFecha = new stdClass();
                            $inputFecha->id = "cap_fecha";
                            $inputFecha->name = "control";
                            $inputFecha->class = 'fecha';
                            $inputFecha->label = '';
                            $inputFecha->enDiv = TRUE;
                            $inputFecha->formato = 'fecha';
                            $inputFecha->labelClass = '_32 txtL';
                            $inputFecha->required = TRUE;
    
                            $btnCancelar = new stdClass();
                            $btnCancelar->id = 'btnCancelar';
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';
    
                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function() {
                                    if ($("#bg_capfecha").length != 0 ) {
                                        $(this).hide();
                                        $("#bg_capfecha").show();
                                    }
                                });
    
                                $("#btnGeneraPolizaCerrado").click(function() {
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.(isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        bdo_id: '.$this->prm->bdo_id.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        agrupador: "'.$this->prm->agrupador.'",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "A"
                                    });
                                });
    
                                $("#btnCancelar").click(function() {
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    }
                }
    
                if ($this->escritura) {
                    if ($generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado
                        && !$polizaCan->valido) {
    
                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                        $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';
    
                        if ($estadoMesCancelado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaC").click(function() {
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.(isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        bdo_id: '.$this->prm->bdo_id.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        agrupador: "'.$this->prm->agrupador.'",
                                        fecha: "'.$fechaCancelado.'",
                                        tmc_id: "C"
                                    });
                                });
                            ');
                        } else {
                            $btnGeneraPolizaCerrado = new stdClass();
                            $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->text = 'Genera Póliza de Cancelación';
                            $btnGeneraPolizaCerrado->value = 'Genera Póliza de Cancelación';
                            $btnGeneraPolizaCerrado->color = 'verde';
                            $btnGeneraPolizaCerrado->icon = 'ok';
    
                            $inputFecha = new stdClass();
                            $inputFecha->id = "cap_fecha";
                            $inputFecha->name = "control";
                            $inputFecha->class = 'fecha';
                            $inputFecha->label = '';
                            $inputFecha->enDiv = TRUE;
                            $inputFecha->formato = 'fecha';
                            $inputFecha->labelClass = '_32 txtL';
                            $inputFecha->required = TRUE;
    
                            $btnCancelar = new stdClass();
                            $btnCancelar->id = 'btnCancelar';
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';
    
                            $this->mixScript('
                                $("#btnGeneraPolizaC").click(function() {
                                    if ($("#bg_capfecha").length != 0 ) {
                                        $(this).hide();
                                        $("#bg_capfecha").show();
                                    }
                                });
    
                                $("#btnGeneraPolizaCerrado").click(function() {
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.(isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
                                        clc_id: "'.$this->prm->clc_id.'",
                                        clcFiltro: '.$this->prm->clcFiltro.',
                                        bdo_id: '.$this->prm->bdo_id.',
                                        tdf_id: "'.$this->prm->tdf_id.'",
                                        rfc_id: "'.$this->prm->rfc_id.'",
                                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                        bdoFiltro: '.$this->prm->bdoFiltro.',
                                        agrupador: "'.$this->prm->agrupador.'",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "C"
                                    });
                                });
    
                                $("#btnCancelar").click(function() {
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    }
                }
    
                $this->agregaTools($botones);
            } //fin ministrado
        } // fin de if de $documento_>detalleMinistrado2023
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        if(isset($botonGeneraPa)){
            $this->mixHtml($botonGeneraPa);
        }
        $this->setContenedor('botonC');
        if(isset($botonGeneraPc)){
            $this->mixHtml($botonGeneraPc);
        }

        $this->mixScript('
            $("#btnRegresa").click(function() {
                comunica("'.$this->pantalla.'", {
                    metodo: "PonBarraBotones",
                    origen: "'.$this->origen.'",
                    seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                : "" ).'",
                    mes: '.$this->prm->mes.',
                    gur: '.$this->prm->gur.',
                    clc_id: "'.$this->prm->clc_id.'",
                    clcFiltro: '.$this->prm->clcFiltro.',
                    bdo_id: '.$this->prm->bdo_id.',
                    tdf_id: "'.$this->prm->tdf_id.'",
                    rfc_id: "'.$this->prm->rfc_id.'",
                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                    bdoFiltro: '.$this->prm->bdoFiltro.'
                });
            });
        ');
    }

    private function GeneraPoliza()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);

        $tipoP = (isset($this->prm->tipoP) ? $this->prm->tipoP : 'A');
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = 2023;
            $res = validaFecha($fechaValidar);
            if (!$res->valido) {
                $this->mixScript('notify("error:'.$res->mensaje.'","error");');
                return false;
            }
            if (!$res->anio) {
                $this->mixScript('notify("El año es incorrecto","error");');
                return false;
            }
            if ($res->edoMesContable == 'C') {
                $this->mixScript('notify("El mes esta cerrado","error");');
                return false;
            }
            $fecha = $res->fechaMysql;
        } else {
            $this->mixScript('notify("Error en la fecha","error");');
            return false;
        }


        $bandera = 0;
        $mensajesp = "ERROR";

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $generaPolizaMinistrado = new sp_CereCoEgresoCancelado2023();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN 2023");
            $generaPolizaMinistrado->setEjercicio(2023);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->setSql('SET @usr_ip = INET_ATON(?), @usr_id = ?, @bit_observacion = "PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS DE LA MINISTRACIÓN 2023"');
            $paraCancelado =[$this->usr_ip,$this->usr_id];
            $generaPolizaMinistrado->consultar($paraCancelado);
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        } else {

            $generaPolizaMinistrado = new sp_CereCoEgresoAutorizado2023();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN 2023");
            $generaPolizaMinistrado->setEjercicio(2023);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->setSql('SET @usr_ip = INET_ATON( ? ), @usr_id = ?, @bit_observacion = "PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS DE LA MINISTRACIÓN 2018"');
            $parAutorizado =[$this->usr_ip,$this->usr_id];
            $generaPolizaMinistrado->consultar($parAutorizado);
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        }

        $mensaje = "";
        $tipo = "";
        if ($bandera != 1) {
            $mensaje = "ERROR:".$mensajesp;
            $tipo = "error";
        } else {
            $mensaje = $mensajesp;
            $tipo = "success";
        }

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        bdo_id: '.$this->prm->bdo_id.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "C"
                    });
                ');
        } else {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        bdo_id: '.$this->prm->bdo_id.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "A"
                    });
                ');
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['12']['21']['1'])) {
    new ControladorMinistrado2023();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}