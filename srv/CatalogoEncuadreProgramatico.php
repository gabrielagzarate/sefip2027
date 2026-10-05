<?php

DEFINE('LINEAS_POR_PAGINA', 99);
require_once dirname(__FILE__) . '/../lib/WebResponse.php';

const CONTENEDOR_ENCUADRE_PROGRAMATICO = 'CONTENEDOR_ENCUADRE_PROGRAMATICO';

if (!session_id()) {
    session_start();
}

class CatalogoEncuadreProgramatico extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'srv/CatalogoEncuadreProgramatico.php';
        if ($this->metodo === 'llenaTablaEncuadreProgramatico') {
            $this->llenaTablaEncuadreProgramatico();
        } else {
            $this->index();
        }
        return $this->returnJson();

    }

    public function index()
    {
        $this->setContenedor(CONTENEDOR_ENCUADRE_PROGRAMATICO);
        if (isset($this->prm->tipo) && $this->prm->tipo === 'ventana') {
            $this->setContenedor('det_fs' . CONTENEDOR_ENCUADRE_PROGRAMATICO);
        }
        $tabla = $this->ponTablaProgramatico();
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript($this->wrLlamaLlenaTabla($tabla->id));
    }

    public function ponTablaProgramatico(): object
    {

        $tabla = new stdClass();
        $tabla->id = 'TABLA_' . CONTENEDOR_ENCUADRE_PROGRAMATICO;
        $tabla->comunica = new stdClass();
        $tabla->tools = $this->ponFiltros();
        $tabla->inputFiltro = 'inp_cat_prog_fin_search';
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaEncuadreProgramatico';
        $tabla->comunica->filtro = isset($this->prm->filtro) ? $this->prm->filtro : '';
        $tabla->comunica->omo_id = isset($this->prm->omo_id) ? $this->prm->omo_id : '';
        $tabla->comunica->toa_id = isset($this->prm->toa_id) ? $this->prm->toa_id : '';
        $tabla->comunica->ipu_id = isset($this->prm->ipu_id) ? $this->prm->ipu_id : '';
        $tabla->comunica->mod_ue_id = isset($this->prm->mod_ue_id) ? $this->prm->mod_ue_id : '';
        $tabla->trDblClick = '_encProgFinFunction';
        $tabla->esCompacta = true;

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Encuadre';
        $th->width = '50%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre Obra/Actividad';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'prog';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'npro';
        $td->ajustable = true;
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
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


    public function llenaTablaEncuadreProgramatico()
    {

        $nInicial = 0;
        $select = '';
        $sql = '';
        $ejecutora = isset($_POST['ue_id']) ? $_POST['ue_id'] : '';
        $omo_id = isset($_POST['omo_d']) ? $_POST['omo_d'] : '';
        $params = [];

        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'presupuestoValidada':
                    $params = [];
                    $sql = '
                        FROM ' . BD_CTRL . '.cat_ObraActividad
                        INNER JOIN ' . BD_CTRL . '.enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                        INNER JOIN cat_MetaActividad USING(epg_id)
                        WHERE gpo_id = :gpo AND ur_id = :ur
                        AND eoa_id = :eoa AND toa_id = :toa AND oa_id!= :oa GROUP BY epg_id';
                    $params[':gpo'] = $_SESSION[_S]['clasAdm-gpo'];
                    $params[':ur'] = $_SESSION[_S]['clasAdm-ur'] . $ejecutora;
                    $params[':eoa'] = '4';
                    $params[':toa'] = '1';
                    $params[':oa'] = '00';
                    break;
                case 'presupuestoValidadaPRG000':
                    $params = [];
                    $sql = '
                        FROM ' . BD_CTRL . '.cat_ObraActividad
                        INNER JOIN ' . BD_CTRL . '.enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                        INNER JOIN cat_MetaActividad USING(epg_id)
                        WHERE gpo_id = :gpo AND ur_id = :ur
                        AND prg_id = :prg_id AND eoa_id = :eoa AND toa_id = :toa AND oa_id!= :oa GROUP BY epg_id';
                    $params[':gpo'] = $_SESSION[_S]['clasAdm-gpo'];
                    $params[':ur'] = $_SESSION[_S]['clasAdm-ur'] . $ejecutora;
                    $params[':eoa'] = '4';
                    $params[':toa'] = '1';
                    $params[':oa'] = '00';
                    $params[':prg_id'] = '000';
                    break;
                case 'planeacionValidada':
                    $params = [];
                    $sql = '
                        FROM ' . BD_CTRL . '.cat_ObraActividad
                        INNER JOIN ' . BD_CTRL . '.enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                        INNER JOIN cat_MetaActividad USING(epg_id)
                        WHERE gpo_id = :gpo AND ur_id = :ur
                        AND eoa_id = :eoa AND toa_id = :toa AND oa_id!= :oa GROUP BY epg_id';
                    $params[':gpo'] = $_SESSION[_S]['clasAdm-gpo'];
                    $params[':ur'] = $_SESSION[_S]['clasAdm-ur'] . $ejecutora;
                    $params[':eoa'] = '5';
                    $params[':toa'] = '2';
                    $params[':oa'] = '00';
                    break;
            }
        } else {
            $sql = '
                FROM ' . BD_CTRL . '.cat_ObraActividad
                INNER JOIN ' . BD_CTRL . '.enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id);
            ';
        }

        if (isset($_POST['filtro']) && $_POST['filtro'] != '') {
            switch ($_POST['filtro']) {
                case 'presupuestoValidada':
                    $select = "
                        SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id) prog,
                        oa_nombre as npro, GROUP_CONCAT(pii_id ORDER BY pii_id SEPARATOR ',') AS pii
                    ";
                    break;
                case 'presupuestoValidadaPRG000':
                    $select = "
                        SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id) prog,
                        oa_nombre as npro, GROUP_CONCAT(pii_id ORDER BY pii_id SEPARATOR ',') AS pii
                    ";
                    break;
                case 'planeacionValidada':
                    $select = "
                        SELECT epg_id,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id) prog,
                        oa_nombre as npro, GROUP_CONCAT(pii_id ORDER BY pii_id SEPARATOR ',') AS pii
                    ";
                    break;
                default:
                    $select = '';
                    $limit = '';
            }
        } else {
            $select = "SELECT epg_id prog,CONCAT(gpo_id,ur_id,'-',ue_id,'-',prg_id,'-',spr_id,'-',py_id,'-',oa_id,'-',oa_nombre) nprog";
            $limit = 'LIMIT ' . $nInicial . ', ' . LINEAS_POR_PAGINA;
        }



        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 80;
        $nav->idTabla = 'TABLA_' . CONTENEDOR_ENCUADRE_PROGRAMATICO;

        $this->setParams($params);
        $this->setSql($select . $sql);

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros","warning");
                $(".tcl_ida").removeClass("loadingclas");');
        }
    }

    public static function ponModalEncuadre($parametros): string
    {

        $prm = $parametros ?
            (
                is_array($parametros) ? (object) $parametros : (is_object($parametros) ? $parametros : new stdClass())
            ) : new stdClass();
        $epg_id = isset($prm->epg_id) ? $prm->epg_id : null;

        $filtro = isset($prm->filtro) ? $prm->filtro : '';
        //var_dump($filtro);
        //$datosProgramatico = ClasificacionAdministrativa::PonEnVariablesEpg($epg_id, true);
        $claveProgramatico = '';//$datosProgramatico['programatico'];
        //$nombreProgramatico = $datosProgramatico['oa_nombre'];
        $readOnly = true;

        $js = self::construyeJs($parametros);
        return <<<HTML

            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Agregar Actividad</h4>
                            <button type="button" class="btn-close btn-danger" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formEpg">
                                <label class="mb-2">Encuadre Programático</label>
                                <fieldset class="col-md-12 col-xs-12">
                                <input class="form-control F1Catalogo" id="encProg" 
                                    placeholder="Encuadre (F1)" required="1" 
                                    maxlength="20" 
                                    name="encProg" type="text" size="20"
                                    value ="{$claveProgramatico}"
                                    {$readOnly}
                                    autocomplete="off"
                                    >
                            </fieldset>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-outline-success" id="btnAceptar" form="">Aceptar</button>
                            <button type="button" class="btn btn-outline-danger cerrar" id="cerrar" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    {$js}
                </script>
            </div>
        HTML;
    }

    public static function construyeJs($parametros)
    {

        $programaticoBlur = '';
        $filtro = $parametros['filtro'];

        if (isset($parametros->$programaticoBlur)) {
            $programaticoBlur = <<<JS
                $("#encProg").on('keydown', function(){
                    {$parametros->$programaticoBlur}
                });
            JS;
        }


        $onSelect = '';

        if (isset($parametros->onSelect)) {
            $onSelect = $parametros->onSelect;
        }
        $idContainer = CONTENEDOR_ENCUADRE_PROGRAMATICO;

        $shootContainer = <<<JS
            $("#{$idContainer}").remove();
            $("body").append(
                $("<div/>").prop("id","{$idContainer}")
            );
            $("#{$idContainer}").dialog({
                modal: true,
                autoOpen: true,
                title: "Buscar y seleccionar Encuadre Programatico",
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
        $tipo = isset($parametros->tipo) && $parametros->tipo === 'ventana' ? 'ventana' : 'modal';

        $jsonComunica = [];
        $jsonComunica[] = "tipo: '" . $tipo . "'";
        if (isset($filtro)) {
            $jsonComunica[] = "filtro:'" . $filtro . "'";
        }

        if (isset($parametros->ejecutora)) {
            $jsonComunica[] = 'ue_id:"' . $parametros->ejecutora . '"';
        }

        $nivel = $parametros['nivelRevision'] != '' ? ',nivelRevision:"' . $parametros['nivelRevision'] . '"' : '';


        $jsonComunica = '{' . join(',', $jsonComunica) . '}';

        return <<<JS
            $(function(){
                $("#encProg").off('keydown').on('keydown', function(e){
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if ( key == 112 ) {
                        e.preventDefault();
                        {$shootContainer}
                        comunica('srv/CatalogoEncuadreProgramatico.php',{$jsonComunica});
                    } else if ( key == 113 ){ /* F2 muestra los combos de clasAdmin */
                        e.preventDefault();
                        $("#clasAdminSeccionCombos").toggle();
                    } else if( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                        notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                        e.preventDefault();
                        return false;
                    }
                });

                {$programaticoBlur}
            })
            function _encProgFinFunction (selection, event) {
                try {
                    if (!typeof(selection) !== 'object' || !event) {
                        $("#encProg").attr( "enc", selection.epg_id)
                            .attr( "ori", selection.prog)
                            .attr( "pii", selection.pii)
                            .val( selection.prog);
                        $("#nomProg").val( selection.nombre?.replace( /\./g, "" ) );
                        comunica( "lib/dameEncuadreProgramatico.php", {
                            
                            encProg: selection.epg_id?.trim(),
                            prog: selection.prog?.trim(),
                            pii: String(selection.pii ?? '').trim(),
                        });
                        $("#{$idContainer}close_jma_viewer").trigger('click');
                        $("#{$idContainer}").remove();
                        {$onSelect}

                        $("#btnAceptar").click(function(){
                            comunica( '{$parametros['pantalla']}', {
                                epg_id: selection.epg_id?.trim(),
                                metodo: '{$parametros['metodo']}',
                                omo_id: '{$parametros['omo_id']}',
                                mme_gpo_id: '{$parametros['mme_gpo_id']}',
                                mme_ur_id: '{$parametros['mme_ur_id']}',
                                mme_id: '{$parametros['mme_id']}',
                                pii_id: String(selection.pii ?? '').trim(),
                                tri_id: '{$parametros['tri_id']}',
                                mact_modificado: '{$parametros['mact_modificado']}',
                                abc: '{$parametros['abc']}'
                                {$nivel}
                            });
                        });
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

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    new CatalogoEncuadreProgramatico();
}
