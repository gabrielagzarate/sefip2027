<?php
set_time_limit(0);

//librerias
session_start();
require_once dirname(__FILE__).'/../../lib/getDocumento.php';
require_once dirname(__FILE__).'/../../lib/opcionConteo.php';
require_once dirname(__FILE__).'/../../lib/pintaClc.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaMinistrado.php';
require_once dirname(__FILE__).'/../../lib/scg/vinculacion/2024/vinculacionMinistrado2024.php';
require_once dirname(__FILE__).'/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__).'/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__).'/../../lib/validaFecha.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__).'/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoAutorizado2024.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoCancelado2024.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereEgresoAutorizado2024.php';
require_once dirname(__FILE__) . "/../../lib/constantes.php";
class ControladorMinistrado2024 extends WebResponse
{
    protected $escritura;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $gpo_id_2024;
    protected $ur_id_2024;
    protected $usr_ip;
    protected $usr_id;
    protected $origen;

    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['12']['22']['2']) ? TRUE
                : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = FALSE;
        }
        $sql = "SELECT
                gpo_id_2024 AS gpo_id, ur_id_2024 AS ur_id
                FROM ".BD_SINPRES.".vin_UnidadResponsable_2024_2025 
				WHERE gpo_id_2025 = ? AND ur_id_2025 = ?;
            ";
        $p1 = [$this->gpo_id,$this->ur_id];
        $conexion = new AccesoDatos();
        $conexion->setSql($sql);
        $conexion->conectar();
        $conexion->consultar($p1);
        $data = $conexion->extraer();
        $conexion->desconectar();
        $this->gpo_id_2024 = $data['gpo_id'];
        $this->ur_id_2024 = $data['ur_id'];
        unset($conexion);

        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? 
            $_SESSION[_S]['ses_ip'] : 
            'ERROR';

        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
                $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

        switch ($this->metodo) {
            case 'DibujaPantalla': 
                $this->DibujaPantalla();
                break;
            case 'MuestraDatos': 
                $this->MuestraDatos();
                break;
            case 'Documento':
                $this->Documento();
                break;
            case 'GeneraPoliza':
                $this->GeneraPoliza();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
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

    public function main()
    {
        $html = $this->getTablaMinistrado2024();
        $this->ponBarraBotones();
        $this->setContenedor('contenedor');
        $es116 = $this->gpo_id . $this->ur_id == 116;
        $this->mixHtml($html);
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
        $btnLimpiar->class = "btn btn-outline-info";
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
        $inputGur->value = (isset($this->prm->gur) ? $this->prm->gur : "");
        $inputGur->maxlength = '3';

        $inputClc = new StdClass();
        $inputClc->id = 'inputClc_id';
        $inputClc->name = 'inputClc_id';
        $inputClc->placeholder = 'CLC';
        $inputClc->enDiv = true;
        $inputClc->class = 'form-control';
        $inputClc->value = ($clcFiltro ? $this->prm->clc_id : "");
        $inputClc->maxlength = '6';

        $inputBdo = new StdClass();
        $inputBdo->id = 'inputBdo_id';
        $inputBdo->name = 'inputBdo_id';
        $inputBdo->placeholder = '# Documento';
        $inputBdo->enDiv = true;
        $inputBdo->value = ($bdoFiltro ? $this->prm->bdo_id : "");
        $inputBdo->maxlength = '7';

        $inputRfc = new StdClass();
        $inputRfc->id = 'inputRfc_id';
        $inputRfc->name = 'inputRfc_id';
        $inputRfc->placeholder = 'RFC';
        $inputRfc->enDiv = true;
        $inputRfc->value = ($bdoFiltro ? $this->prm->rfc_id : "");
        $inputRfc->maxlength = '20';

        $inputReferencia = new StdClass();
        $inputReferencia->id = 'inputBdo_referencia';
        $inputReferencia->name = 'inputBdo_referencia';
        $inputReferencia->placeholder = 'Referencia';
        $inputReferencia->enDiv = true;
        $inputReferencia->value = ($bdoFiltro ? $this->prm->bdo_referencia : "");
        $inputReferencia->maxlength = '100';

        $botones = '
            <div class="row mb-4 class="col-md-12 col-xs-12">
                ' . ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 ?
            '<fieldset class="col-md-3 col-xs-3">' .
            $this->ponInput($inputGur) .
            '</fieldset>' : '') . '
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($inputClc) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($inputBdo) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($inputRfc) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputReferencia) . '
                </fieldset>
            </div>';

        $this->agregaTools($botones);
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
                SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
                FROM cat_MesContable
                    INNER JOIN cg_MesContable USING( msc_id )
                WHERE gpo_id = ? AND ur_id = ?
                    AND msc_id NOT IN ( 0, 13 )
            ');
        $p2 = [$this->gpo_id,$this->ur_id];
        $this->consultar($p2);
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
                            $("#btnRegresa").attr("origen", "")
                            $(".navbar").hide();
                            $("#vclcPantallaCompleta").show();
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
                    comunica("' . $this->pantalla . '", {
                        metodo: "Filtro",
                        origen: "' . $this->origen . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
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
                            origen: "'.$this->origen.'",
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

                $("#excelMinistrado2024").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/fondos/exp_Ministrado2024.php")
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

                $("#imprimirMinistrado2024").click( function(e) {
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

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value",  "inputGur").attr("value", '.$this->gpo_id_2024.$this->ur_id_2024.'))
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
        $html = $this->getTablaMinistrado2024();
        $this->mixHtml($html);
        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $this->mixScript('
            $("#page-tabs a").click( function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li").each (function (index) { $(this).removeClass("active"); });
                seleccionado.closest("li").addClass("active");

                comunica(
                    "'.$this->pantalla.'",
                    {
                        metodo: "MuestraDatos",
                        origen: "'.$this->origen.'",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
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

    private function MuestraDatos()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id_2024.$this->ur_id_2024 );
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
        $ClaveFinanciamiento['ecf_id'] = '';// busqueda por clave de financiamiento
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

        $this->MuestraDatosPagina($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'B', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
            "bdo_referencia" => $bdo_referencia));

        return false;
    }

    private function MuestraDatosPagina(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        $mmc_id,
        $niveles,
        $parametroExtra = array()
    )
    {
        switch ($mmc_id) {
        case 'B':
            $tablaAuxiliar = 'ctrlAnterior.min_BancoDocumentoMinistrado';
            $letraAuxiliar = 'm';
            $polizaSql = 'CASE tmi_id WHEN 2 THEN tmi_nombre ELSE pa.polizaAutorizado END AS polizaAutorizado, max(pa.fechaAutorizado) as fechaAutorizado, ';
            break;
        case 'T':
            $tablaAuxiliar = 'ctrlAnterior.min_BancoDocumentoMinistrado';
            $letraAuxiliar = 'm';
            $polizaSql = 'CASE tmi_id WHEN 1 THEN pa.polizaAutorizado ELSE tmi_nombre END AS polizaAutorizado, max(pa.fechaAutorizado) as fechaAutorizado, ';
            break;
        default:
            echo 'kym';
            break;
        }

        $sqlFiltro = '';
        if ($parametroExtra['gur'] != '') {
            $sqlFiltro = "v.gpo_id = " . substr($parametroExtra['gur'], 0, 1) . " AND v.ur_id = " .
            substr($parametroExtra['gur'], 1, 2) . " ";
        }
        if ($parametroExtra['clc_id'] != '') {
            $sqlFiltro .= (($sqlFiltro != '' ? "AND " : "") . "v.clc_id = " . $parametroExtra['clc_id'] . " ");
        }

        if ($parametroExtra['bdo_id'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "v.bdo_id = " . $parametroExtra['bdo_id'] . " ";
        }

        if ($parametroExtra['rfc_id'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "b.rfc_id = '" . $parametroExtra['rfc_id'] . "' ";
        }

        if ($parametroExtra['bdo_referencia'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
        }

        if (isset($parametroExtra['pensiones']) && $parametroExtra['pensiones']) {
            $pensiones = "AND v.tmi_id = 7 ";
        } else {
            $pensiones = "";
        }
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "v.msc_id = " . $periodo['mesDesde'] . " ";
        }
        $sqlFiltro .= ($sqlFiltro != '' ? " AND " : " ") . "v.ejr_id_clc = 2024 AND ejr_id_autorizado = 2025 ";

        $sql = "
            SELECT
                v.gpo_id, v.ur_id, v.clc_id, v.afe_id,
                v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                v.tdf_id AS tdf_id_ministrado, v.bdm_referencia,
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                (b.bdo_percepcionPresupuestal + bdo_percepcionContable - bdo_retencionContable) as importe,
                v.msc_id, CONCAT(gpo_id, ur_id) as gur,
                v.bd" . $letraAuxiliar . "_estado AS estado, DATE( v.bd" . $letraAuxiliar . "_fechaAutorizado ) AS fechaAutorizacion, DATE( v.bd" . $letraAuxiliar . "_fechaCancelado ) AS fechaCancelacion,
                v.bd" . $letraAuxiliar . "_agrupador AS agrupador,
                $polizaSql
                max(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, max(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, max(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                max(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, max(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, max(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
                max(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,

                pc.polizaCancelado, MAX(pc.fechaCancelado) AS fechaCancelado,
                MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado ,
                MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,

                CONCAT( v.gpo_id, v.ur_id, '.', LPAD( v.msc_id, 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ) ) AS id
            FROM " . BD_CTRL_2024 . ".cat_BancoDocumentos b
                INNER JOIN $tablaAuxiliar v USING( gpo_id, ur_id, bdo_id )
                INNER JOIN " . BD_CTRL . ".cg_MesContable ms USING( gpo_id, ur_id, msc_id )
                INNER JOIN ctrlAnterior.cat_TipoMinistracion USING( tmi_id )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, m.ue_id AS ue_id_polizaAutorizado,
                        m.msc_id AS msc_id_polizaAutorizado, m.pot_id AS pot_id_polizaAutorizado, m.prf_id AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, m.pma_fecha AS fechaAutorizado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaAutorizado,
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_afe_id_vinculacion) AS afe_id, MAX(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MAX(x.pdx_agrupador) AS agrupador
                    FROM " . BD_CTRL . ".cg_PolizaMaestro m
                        INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = ? AND m.ur_id = ?
                        AND m.mmc_id = ?
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = 2024
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,ue_id_polizaAutorizado,msc_id_polizaAutorizado,
                    pot_id_polizaAutorizado,prf_id_polizaAutorizado,pma_id_polizaAutorizado
                )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, m.ue_id AS ue_id_polizaCancelado,
                        m.msc_id AS msc_id_polizaCancelado, m.pot_id AS pot_id_polizaCancelado, m.prf_id AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado, m.pma_fecha AS fechaCancelado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaCancelado,
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_afe_id_vinculacion) AS afe_id, MAX(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MAX(x.pdx_agrupador) AS agrupador
                    FROM " . BD_CTRL . ".cg_PolizaMaestro m
                        INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = ? AND m.ur_id = ?
                        AND m.mmc_id = ?
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = 2024
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,ue_id_polizaCancelado,msc_id_polizaCancelado,
                    pot_id_polizaCancelado,prf_id_polizaCancelado,pma_id_polizaCancelado
                )pc USING( gpo_id, ur_id, afe_id, bdo_id )
            WHERE $sqlFiltro
                $pensiones
            GROUP BY ejr_id_clc,gpo_id, ur_id,msc_id, clc_id,afe_id, bdo_id ,polizaAutorizado,polizaCancelado
        ";
        $parametrosSQL = [$gpo_id,$ur_id,$mmc_id,$gpo_id,$ur_id,$mmc_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametrosSQL);
        $this->desconectar();
        $this->setParams($parametrosSQL);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = $this->getNumeroRegistros();
        $nav->idTabla = 'tbl-ministrado2024';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        } else {
            $this->mixScript('
                $("#tbl-ministrado2024 tr").on("dblclick", function (ev){
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }
                    const prm = $("#tbl-ministrado2024").tabla("getFilaSeleccion");

                    comunica("'.$this->pantalla.'", {
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        clc_id: $("#inputClc_id").val(),
                        clcFiltro: var_clcFiltro,
                        gur: prm.gur,
                        afe_id: prm.afe_id,
                        bdo_id: prm.bdo_id,
                        tdf_id: prm.tdf_id,
                        rfc_id: prm.rfc_id,
                        bdo_referencia: prm.bdo_referencia,
                        bdoFiltro: var_bdoFiltro,
                        agrupador: prm.agrupador
                    });

                    ev.stopPropagation();
                });
            ');

            $this->mixScript('
                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove();'
            );
        }
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id_2024.$this->ur_id_2024 );
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
        $ClaveFinanciamiento['ecf_id'] = '';// busqueda por clave de financiamiento
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

        $datos = vinculacionMinistrado2024($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'B', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
            "bdo_referencia" => $bdo_referencia));

        return json_encode($datos);
    }

    public function getTablaMinistrado2024(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->inputFiltro = "buscar";
        $tabla->id = 'tbl-ministrado2024';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMinistrado2024';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AFE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DOC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA MINISTRADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AUTORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CANCELACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POL AUT.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POL CAN.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gur'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['afe_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['rfc_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_referencia'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_concepto'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id_ministrado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdm_referencia'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['importe'];
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['estado'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizacion'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelacion'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaAutorizado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizado'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaCancelado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelado'];
        $td->align = 'center';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $this->wrPonTabla($tabla);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $p3 = [$this->gpo_id, $this->ur_id];
        $conexion->consultar($p3);

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
        $existeFechaCan = TRUE;
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
            array("ejercicio" => 2024, "tdf_id" => $this->prm->tdf_id, "mmc_id" => "B"));
        $html = '<legend>Análisis del documento 2024 a generar la póliza del ministrado</legend>';
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
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->percepcionPresupuestal,
                2).'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->percepcionContable,
                2).'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->retencionContable,
                2).'</td>
                                <td>'.$documento->fechaDocumento.'</td>
                            </tr>
                        </table>
                    </fieldset><br/>
            ';

        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion

        if(!empty($documento->detalleMinistrado2024)){
            foreach ($documento->detalleMinistrado2024 as $key => $value) {
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
            if (!isset($estadoMesCancelado) || $estadoMesCancelado == "") {
                $existeFechaCan = FALSE;
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
                        array("ejercicio" => 2024, "bdo_id" => $bdo_id_documento, "todo" => TRUE));
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
                array("ejercicio" => 2024, "clc_id" => $clc_id_documento,));
            $html .= $ministrado->html;
            $html .= '
                    </fieldset></br>
                ';
            $banderaCuentas = FALSE;
            if ($tipoministrado == 5) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql("
                    SELECT ncc_id
                    FROM ".BD_CTRL_2024.".`scp_Clc` c
                        INNER JOIN ".BD_CTRLANT.".scp_Clc AS c using(gpo_id,ur_id,clc_id)
                        INNER JOIN ".BD_CTRL_2024.".`scp_Contable` n USING(gpo_id, ur_id, clc_id)
                        INNER JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, t.bdo_id
                            FROM ".BD_CTRLANT.". min_BancoDocumentoMinistrado t
                            WHERE gpo_id = :gpo AND ur_id = :ur
                                AND bdm_agrupador = :bdm
                                AND ejr_id_clc= :ejr AND ejr_id_autorizado= :ejr
                            GROUP BY gpo_id, ur_id, clc_id, afe_id, t.bdo_id
                        )bdm USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    WHERE n.gpo_id = :gpo AND n.ur_id = :ur  AND ejr_id_clc= :ejr
                        AND ((n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 1)
                        OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 2)
                        OR (n.ger_id = 2 AND n.cgp_id = 1 AND n.rub_id = 1 AND n.cgcu_id = 9 AND n.cgsc_id = 4));");
                $p4 = [':gpo'=>$gpo_id_documento,':ur'=>$ur_id_documento,':bdm'=>$this->prm->agrupador,':ejr'=>2024];
                $conexion->consultar($p4);
                $dataRow = $conexion->extraer();
    
                if ($dataRow["ncc_id"]) {
                    $banderaCuentas = TRUE;
                }
                $conexion->desconectar();
            }
            if ($banderaCuentas == FALSE && ( $tipoministrado == 1 || $tipoministrado
                == 4 || $tipoministrado == 5 || $tipoministrado == 6 || $tipoministrado
                == 7 || $tipoministrado == 8 || $tipoministrado == 9)) {
    
                /*             * *********************************** Información de la Póliza Contable ************************************************************** */
                $polizas = getPolizaAgrupador(
                    $this->gpo_id,
                    $this->ur_id,
                    $this->prm->agrupador,
                    '',
                    2024
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
                                    $botonGeneraPa = $this->ponBoton($btnGenera);
    
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
                                                            tmc_id: "A",
                                                            tipoP: "A"
                                                        });
                                                }, 
                                                function(){ }
                                            ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                        });
                                    ');
                                } elseif ($existeFechaCan == TRUE) {
                                    $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                                }
                            } elseif ($estadoMesAutorizado == 'A') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                            } elseif ($estadoMesAutorizado == 'C') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
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
                                                        tmc_id: "C",
                                                        tipoP: "C"
                                                    });
                                                }, 
                                                function(){ }
                                            ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                        });
                                    ');
                                } else {
                                    $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                                }
                            } elseif ($estadoMesCancelado == 'A') {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                            } elseif ($estadoMesCancelado == 'C') {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                            }
                        }
                    }
                }
    
                $botones = '';
                if ($this->escritura) {
                    //si deberia generar la póliza y NO existe
                    if ($generaPolizaAutorizado && !$polizaAut->valido) {
                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->text = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->value = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';
    
                        $botones = $this->ponBoton($btnGeneraPoliza);
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
                                        tmc_id: "A",
                                        tipoP: "A"
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
    
                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes del Ministrado 2024 esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                        '.$this->ponInput($inputFecha).'
                                        </center>
                                        <hr>
                                        '.$this->ponBoton($btnGeneraPolizaCerrado).'
                                        '.$this->ponBoton($btnCancelar).'
                                    </div>
                                </div>
                            ';
    
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
                                        tmc_id: "A",
                                        tipoP: "A"
                                    });
                                });
    
                                $("#btnCancelar").click(function() {
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    }
                } elseif ($estadoMesAutorizado == 'A') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
                } elseif ($estadoMesAutorizado == 'C') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
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
    
                        $botones = $this->ponBoton($btnGeneraPoliza);
    
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
                                        tmc_id: "C",
                                        tipoP: "C"
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
    
                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes de cancelación del Ministrado 2024 esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                        '.$this->ponInput($inputFecha).'
                                        </center>
                                        <hr>
                                        '.$this->ponBoton($btnGeneraPolizaCerrado).'
                                        '.$this->ponBoton($btnCancelar).'
                                    </div>
                                </div>
                            ';
    
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
                                        tmc_id: "C",
                                        tipoP: "C"
                                    });
                                });
    
                                $("#btnCancelar").click(function() {
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    }
                } elseif ($estadoMesCancelado == 'A') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
                } elseif ($estadoMesCancelado == 'C') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                }
    
                $this->agregaTools($botones);
            } //fin ministrado
        }
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
            $fechaValidar->anio = 2024;
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

        $this->conectar();
        $this->setSql('
                SELECT clc_tipoCereCo
                FROM '.BD_CTRLANT.'. min_BancoDocumentoMinistrado AS bdm
		    INNER JOIN '.BD_CTRL_2024.'.scp_Clc AS c using(gpo_id,ur_id,clc_id)
                    INNER JOIN '.BD_CTRLANT.'.scp_Clc AS c using(gpo_id,ur_id,clc_id,ejr_id_clc)
                WHERE gpo_id = :gpo AND ur_id = :ur AND bdm.bdo_id = :bdo
                     '.((isset($this->prm->clc_id)&& $this->prm->clc_id!='')? 'and clc_id= '.$this->prm->clc_id.' ':'').'
                         AND ejr_id_clc= :ejr_clc AND ejr_id_autorizado= :ejr_aut
                    GROUP BY clc_id LIMIT 1;
            ');

        $p5 = [':gpo'=>$gpo_id_documento,':ur'=>$ur_id_documento,':bdo'=>$this->prm->bdo_id,':ejr_clc'=>2024,':ejr_aut'=>2025];
        $this->consultar($p5);
        $this->desconectar();
        $rowCereCo = $this->extraer();

        if ($tipoP == "C") {
            $generaPolizaMinistrado = new sp_CereCoEgresoCancelado2024();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN 2024");
            $generaPolizaMinistrado->setEjercicio(2024);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        } else {

            if ($rowCereCo["clc_tipoCereCo"] == 1) {
                $generaPolizaMinistrado = new sp_CereEgresoAutorizado2024();
                $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
                $generaPolizaMinistrado->setUr_id($ur_id_documento);
                $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
                $generaPolizaMinistrado->setPma_fecha($fecha);
                $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
                $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN 2024");
                $generaPolizaMinistrado->setEjercicio(2024);
                $generaPolizaMinistrado->setSis_id("SCG");
                $generaPolizaMinistrado->execute(false, false);
                $bandera = $generaPolizaMinistrado->getBandera();
                $mensajesp = $generaPolizaMinistrado->getMensaje();
            } else if ($rowCereCo["clc_tipoCereCo"] == 2) {
                $generaPolizaMinistrado = new sp_CereCoEgresoAutorizado2024();
                $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
                $generaPolizaMinistrado->setUr_id($ur_id_documento);
                $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
                $generaPolizaMinistrado->setPma_fecha($fecha);
                $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
                $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN 2024");
                $generaPolizaMinistrado->setEjercicio(2024);
                $generaPolizaMinistrado->setSis_id("SCG");
                $generaPolizaMinistrado->execute(false, false);
                $bandera = $generaPolizaMinistrado->getBandera();
                $mensajesp = $generaPolizaMinistrado->getMensaje();
            }
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
if (isset($_SESSION[_S]['prm']['C']['12']['22']['1'])) {
    new ControladorMinistrado2024();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
?>
