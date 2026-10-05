<?php

require_once dirname(__FILE__).'/../lib/WebResponse.php';

const CONTENEDOR_CLAVES_FINANCIAMIENTO = 'CONTENEDOR_CLAVES_FINANCIAMIENTO';

if (!session_id()) {
    session_start();
}
class CatalogoClavesFinanciamiento extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'srv/CatalogoClavesFinanciamiento.php';
        if ($this->metodo==='llenaTablaClavesFinanciamiento') {
            $this->llenaTablaClavesFinanciamiento();
        } else {
            $this->index();
        }
        return $this->returnJson();
    }

    public function index()
    {
        $this->setContenedor(CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO);
        if (isset($this->prm->tipo) && $this->prm->tipo ==='ventana') {
            $this->setContenedor('det_fs'.CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO);
        }
        $tabla = $this->ponTablaFinanciamiento();
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript($this->wrLlamaLlenaTabla($tabla->id));
    }

    public function ponTablaFinanciamiento(): object
    {
        $tabla = new stdClass();
        $tabla->id = "TABLA_" . CONTENEDOR_CLAVES_FINANCIAMIENTO;
        $tabla->comunica = new stdClass();
        $tabla->tools = $this->ponFiltros();
        $tabla->inputFiltro = "inp_cat_prog_fin_search";
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClavesFinanciamiento';
        $tabla->comunica->filtro = isset($this->prm->filtro)  ? $this->prm->filtro : '';
        $tabla->comunica->paso = isset($this->prm->paso)  ? $this->prm->paso : '';
        $tabla->comunica->toa_id = isset($this->prm->toa_id)  ? $this->prm->toa_id : '';
        $tabla->comunica->ipu_id = isset($this->prm->ipu_id)  ? $this->prm->ipu_id : '';
        $tabla->comunica->mod_ue_id = isset($this->prm->mod_ue_id)  ? $this->prm->mod_ue_id : '';
        $tabla->trDblClick = '_encProgFinFunction';
        $tabla->esCompacta = true;

        $tr = array();
        $th = new stdClass();
        $th->texto = 'Clave Presupuestal';
        $th->width = '10%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Obra/Actividad';
        array_push($tr, $th);

        // $th = new stdClass();
        // $th->texto = 'Nombre Obra/Actividad';
        // array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Clave Financiamiento';
        $th->width = '8%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre de la Clave de Financiamiento';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'prog';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        $td->ajustable = true;
        array_push($trbody, $td);

        // $td = new stdClass();
        // $td->campoTxt = 'npy';
        // $td->ajustable = true;
        // array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cveF';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ncve';
        $td->ajustable = true;
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $tabla;
    }

    private function ponFiltros()
    {
        return <<<HTML
            <div class="py-4 d-flex align-items-center gap-2">
                <input class="form-control w-50" id="inp_cat_prog_fin_search" name="inp_cat_prog_fin_search" placeholder="Filtro" />
            </div>
        HTML;
    }

    public static function ponF1($parametros = null) : string
    {
        $prm = $parametros ? 
            (is_array($parametros) ? (object) $parametros : ( is_object($parametros) ? $parametros :  new stdClass())
        ): new stdClass();

        $ecf_id = isset($prm->ecf_id) ? $prm->ecf_id : null;
        $epg_id = isset($prm->epg_id) ? $prm->epg_id : null;
        $filtro = isset($prm->filtro) ? $prm->filtro : '';
        
        $claveProgramatico = '';
        $nombreProgramatico = '';
        $claveFinanciamiento = '';
        $nombreFinanciamiento = '';
        $readOnly = '';

        if ($ecf_id) {
            $datosFin = self::formateaDatosFinanciamiento(ClasificacionFinanciamiento::PonEnVariablesEcf($ecf_id));
            $claveFinanciamiento = $datosFin['claveFinanciamiento'];
            $nombreFinanciamiento = $datosFin['nombreFinanciamiento'];
            $readOnly = 'disabled';
        }
        if ($epg_id) {
            $datosProgramatico = ClasificacionAdministrativa::PonEnVariablesEpg($epg_id, true);
            $claveProgramatico = $datosProgramatico['programatico'];
            $nombreProgramatico = $datosProgramatico['oa_nombre'];
            $readOnly = 'disabled';
        }

        $js = self::construyeJs($parametros);
        return <<<HTML
            <div class="row mb-2" id="div_programa_financiamiento_container" aria-filtro="{$filtro}">
                <fieldset class="col-md-12 col-xs-12">
                    <legend  style="color:#FF4000" class="mb-2">Encuadre Programático y Clave de Financiamiento
                        <span id="comoSeUsa" style="text-decoration: underline; color:red; cursor: pointer;">¿Cómo se usa ?</span>
                    </legend>
                    <br>
                    <label class="mb-2">Encuadre Programático</label>
                        <div class="row mb-2" >
                            <fieldset class="col-md-3 col-xs-12">
                                <input class="form-control F1Catalogo" id="encProg" 
                                    placeholder="Encuadre (F1/F2)" required="1" 
                                    maxlength="20" 
                                    name="encProg" type="text" size="20"
                                    value ="{$claveProgramatico}"
                                    {$readOnly}
                                    autocomplete="off"
                                    >
                            </fieldset>
                            <fieldset class="col-md-9 col-xs-12">
                                <input readonly="" class="form-control " id="nomProg" 
                                    placeholder="Nombre Obra/Actividad"
                                    name="nomProg" 
                                    value="{$nombreProgramatico}"
                                    disabled="1" 
                                    type="text"
                                >
                            </fieldset>
                        </div>
                    <label class="mb-2">Clave de Financiamiento</label>
                    <div class="row mb-2" >
                        <fieldset class="col-md-3 col-xs-12">
                            <input class="form-control F1Catalogo" id="encCveFin" 
                                placeholder="Clave (F1/F2)" 
                                required="1" maxlength="12" 
                                name="encCveFin" type="text" size="12"
                                value="{$claveFinanciamiento}"
                                {$readOnly}
                                autocomplete="off"
                                />
                        </fieldset>
                        <fieldset class="col-md-9 col-xs-12">
                            <input class="form-control " id="nomCveFin" 
                                placeholder="Nombre de la Clave"
                                name="nomCveFin"
                                disabled="1" type="text"
                                value="{$nombreFinanciamiento}"
                                >
                        </fieldset>
                    </div>
                </fieldset>
                <script type="text/javascript">
                    {$js}
                </script>
            </div>
        HTML;
    }

    public static function construyeJs($parametros)
    {
        $cveFinblurEvent = '';
        if (isset($parametros->cvefinBlur)) {
            $cveFinblurEvent =<<<JS
                $("#encCveFin").on('blur', function(){
                    {$parametros->cvefinBlur}
                });
            JS;
        }
        $programaticoBlur = '';
        if (isset($parametros->$programaticoBlur)) {
            $programaticoBlur =<<<JS
                $("#encCveFin").on('keydown', function(){
                    {$parametros->$programaticoBlur}
                });
            JS;
        }

        $onSelect = '';

        if (isset($parametros->onSelect)) {
            $onSelect = $parametros->onSelect;
        }
        $idContainer = CONTENEDOR_PROGRAMATICO_FINANCIAMIENTO;

        $shootContainer  = <<<JS
            $("#{$idContainer}").remove();
            $("body").append(
                $("<div/>").prop("id","{$idContainer}")
            );
            $("#{$idContainer}").dialog({
                modal: true,
                autoOpen: true,
                title: "Seleccionar Obra y Clave de Financiamiento",
                dragable: false,
                resizable: false,
                width: Math.abs(window.innerWidth * .8),
                position: {my: "center center", at: "center top"},
                open: function(e) {
                    $(".ui-widget-overlay").css("z-index", 10000);
                    $("#{$idContainer}").parent().css("z-index", 10001);
                    $(".ui-dialog-titlebar-close").addClass("btn btn-outline-danger btn-small").append("X");
                }
            })
        JS;
        $tipo = isset($parametros->tipo) && $parametros->tipo ==='ventana' ? 'ventana' : 'modal';
        if (isset($parametros->tipo) && $parametros->tipo ==='ventana') {
            $shootContainer =<<<JS
                load_viewer('{$idContainer}','Seleccionar Obra y Clave de Financiamiento', 'div','', 1031, true);
            JS;
        }

        $jsonComunica = [];
        $jsonComunica[] = "tipo: '".$tipo."'";
        if (isset($parametros->filtro)) {
            $jsonComunica[] = "filtro:'".$parametros->filtro."'";
        }
        if (isset($parametros->paso)) {
            $jsonComunica[] = 'paso:'.$parametros->paso;
        }
        if (isset($parametros->toa_id)) {
            $jsonComunica[] = 'toa_id:"'.$parametros->toa_id.'"';
        }

        if (isset($parametros->filtroObra)) {
            $jsonComunica[] = 'ipu_id:"'.$parametros->filtroId.'"';
        }
        if (isset($parametros->mod_ue_id)) {
            $jsonComunica[] = 'mod_ue_id: "'.$parametros->mod_ue_id.'"';
        }

        $jsonComunica ='{'. join(',',$jsonComunica).'}';
        return <<<JS
            $(function(){
                $("#encProg").off('keydown').on('keydown', function(e){
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if ( key == 112 ) {
                        e.preventDefault();
                        {$shootContainer}
                        comunica('srv/CatalogoProgramaticoFinanciamiento.php',{$jsonComunica});
                    } else if ( key == 113 ){ /* F2 muestra los combos de clasAdmin */
                        e.preventDefault();
                        $("#clasAdminSeccionCombos").toggle();
                    } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                        notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                        e.preventDefault();
                        return false;
                    }
                });

                $("#encCveFin").on('keydown', function(event){
                    const key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;
                    if (key == 112) {
                        event.preventDefault();
                        {$shootContainer}
                        comunica('srv/CatalogoProgramaticoFinanciamiento.php', {$jsonComunica});
                    } else if( key == 113 ) {
                        if ( $("#encProg").val() != "" ) {
                            event.preventDefault();
                            comunica( "srv/ClasificacionFinanciamiento.php", {
                                metodoCveFin: "muestraCveFin"
                            });
                        } else {
                            notify("Debes seleccionar primero el Encuadre Programático", "warning");
                            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
                        }
                    } else if (( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 )) {
                        notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                        e.preventDefault();
                        return false;
                    }
                })

                {$cveFinblurEvent}
                {$programaticoBlur}
            })
            function _encProgFinFunction (selection, event) {
                try {
                    if (!typeof(selection) !== 'object' || !event) {
                        $("#encProg").attr( "enc", selection.epg_id)
                            .attr( "ori", selection.prog)
                            .val( selection.prog);
                        $("#nomProg").val( selection.nombre?.replace( /\./g, "" ) );
                        $("#encCveFin").attr( "enc", selection.ecf_id )
                            .attr( "ori", selection.cveF )
                            .val( selection.cveF );
                        $("#nomCveFin").val( selection.ncve?.replace( /\./g, "" ) );
                        comunica( "srv/ClasificacionFinanciamiento.php", {
                            metodoCveFin: "selEncProgFin",
                            encProg: selection.epg_id?.trim(),
                            prog: selection.prog?.trim(),
                            encCveFin: selection.ecf_id?.trim(),
                            cveFin: selection.cveF?.trim(),
                            nomFin: selection.ncve?.replace( /\./g, "" )
                        });
                        $("#{$idContainer}close_jma_viewer").trigger('click');
                        $("#{$idContainer}").remove();
                        {$onSelect}
                    }
                } catch (error) {
                    if (window.serverData.environment !=='production') {
                        console.log(error)
                    }
                }
            }
        JS;
    }
}