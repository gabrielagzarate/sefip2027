<?php

// Marcos: 01/feb/2017
// Se elimina el código del método cargaEncuadreCONAC()
// Porque no se utiliza en ninguna parte del código

require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../lib/mysql_escape_mimic.php';
require_once dirname(__FILE__) . '/../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../ent/UnidadResponsable.php';

class ControladorAltaClave extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();
        //se cambia el valor de pantalla para que no tome como referencia el modulo donde se encuentra
        $this->pantalla = 'srv/ControladorAltaClave.php';
        switch ($this->metodo) {
            case 'cargaDatos1':
                $this->cargaCveFin();
                //no break
            case 'cargaDatos2':
                $this->cargaClaveFinanciamiento();
                break;
            case 'ponDatosEncuadreActualCF':
                $this->ponDatosEncuadreActualCF();
                break;
            case 'ponDatosEncuadreActualPgPe':
                $this->ponDatosEncuadreActualPgPe();
                break;
            case 'abcCosteo':
                $this->abcCosteo();
                break;
            case 'agregarCVeFin':
                $this->agregarCVeFin();
                break;
            case 'cargaTipoCompra':
                $this->cargaTipoCompra();
                break;
            default:$this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $clasAdmin = new stdClass();
        $clasAdmin->required = true;
        $clasAdmin->filtro = 'poa';

        $clasAdmin->change = 'comunica("' . $this->pantalla . '",{ metodo:"cargaDatos1" });';
        $this->mixScript(ClasificacionADministrativa::muestraClasAdmin($clasAdmin));
        $this->mixScript(ClasificacionAdministrativa::onChangeUr('comunica("' . $this->pantalla . '");'));
        $epg = ClasificacionAdministrativa::getEpg();

        $combo = new stdClass();
        $combo->id = 'comboCveFin';
        $combo->class = 'comboCveFin';
        $combo->sql = '
            SELECT DISTINCT cfi_clave, cfi_nombre, ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
                JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento e USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE ecf_id IN(SELECT ecf_id FROM poa_OrigenRecurso WHERE sts_id = 1 AND epg_id = :epg);';
        $combo->params = [':epg' => is_null($epg) ? 'null' : $epg];
        $combo->comoAtr = 'ecf_id';

        $newCveFin = new stdClass();
        $newCveFin->color = 'verde';
        $newCveFin->title = 'Agregar nueva clave de financiamiento';
        $newCveFin->value = 'Clave de Financiamiento';
        $newCveFin->icon = 'solid fa-plus me-2';
        $newCveFin->attr = new stdClass();
        $newCveFin->class = 'me-1 addCveFin';
        $newCveFin->id = 'newCveFin';

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->value = ' Regresar';
        $btnRegresar->color = 'gris';
        $btnRegresar->icon = 'solid fa-circle-left me-2';
        $btnRegresar->class = 'btn-outline-secondary me-1';
        $btnRegresar->click = 'comunica("mga/complementos/ControladorAjuste.php", { metodo: "main"});';

        $this->agregaTools('
            <div class="row col-md-12 col-xs-12">
                <div class="col-md-2 col-xs-2 mb-1">' . $this->ponBoton($btnRegresar) . '</div>
                <div class="col-md-4 col-xs-4 mb-1">' . $this->ponCombo($combo) . '</div>
                <div class="col-md-1 col-xs-1 mb-1">
                    <a id="ayuda2"  data-placement="bottom" title="Pasos a seguir" role="button" class="btn btn-outline-warning me-1" data-toggle="modal">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </a>
                </div>
                <div class="col-md-2 col-xs-2 text-center mb-1">
                    <input class="form-check-input" type="checkbox" title="Mostrar Claves de Financiamiento sin Objetos de Gasto" name="chkVacias" id="chkVacias" checked>
                    <label class="form-check-label" for="chkVacias">
                        Mostrar Vacías
                    </label>
                </div>
                <div class="col-md-3 col-xs-3 mb-1">' . $this->ponBoton($newCveFin) . '</div>
            </div>
        ');
        $this->mixHtml('
            <div class="panel panel-warning">
                <div class="panel-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>clave financiamiento</th>
                                <th colspan = "2">Objeto del Gasto</th>
                            </tr>
                        </thead>
                        <tbody id="listaCveFin">
                            ' . $this->cargaClaveFinanciamiento(true) . '
                        </tbody>
                    </table>
                </div>
            </div>
        ');

        $this->dibujaFormaAgregarPgPe();
        $this->dibujaFormaAgregarClaveFinanciamiento();

        if ($this->prm->opcion->claveOpcion == 'S_1_2') {
            $this->mixScript('$("#regresar").hide();');
        }

        $this->mixScript('
            $("#regresar").removeClass("btn-default");

            $("#ayuda2").click(function() {
                alertify.alert("<b>PASOS A SEGUIR:</b><h5><ol><li> Seleccionar la estructura Programática</li><li> Seleccionar la Clave de Financiamiento</li><li>En el ícono + agregar Partida Genérica, Específica y Tipo de Compra </li></ol></h5>").setHeader("Ayuda");
            });

            $("#chkVacias").click(function() {
                if ($("#chkVacias").prop("checked")) {
                    comunica("' . $this->pantalla . '", {
                        metodo: "cargaDatos2",
                        vacias: 1,
                        filtroCF: $("#comboCveFin").find("option:selected").attr("ecf_id")
                    });
                } else {
                    comunica("' . $this->pantalla . '",{
                        metodo:"cargaDatos2",
                        vacias: 0,
                        filtroCF: $("#comboCveFin").find("option:selected").attr("ecf_id")
                    });
                }
            });

            $("#comboCveFin").change(function () {
                comunica("' . $this->pantalla . '", {
                    metodo: "cargaDatos2",
                    vacias: $("#chkVacias").prop("checked"),
                    filtroCF: $("#comboCveFin").find("option:selected").attr("ecf_id")
                });
            });

            $("#newCveFin").click(function(e){
                comunica("' . $this->pantalla . '",{ metodo : "ponDatosEncuadreActualCF" });
            });
        ');
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // RELLENAMOS EL COMBO DE CLAVES DE FINANCIAMIENTO, que usaremos para el filtrado en pantalla
    // Obtenemos todas las claves de financiamiento y su encuadre, que tengan su status
    // 1 = Habilitada para su uso
    // y que además pertenezcan al encuadre programático actualmente seleccionado
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function cargaCveFin()
    {
        $epg = ClasificacionAdministrativa::getEpg();

        $combo = new stdClass();
        $combo->id = 'comboCveFin';
        $combo->sql = '
            SELECT DISTINCT cfi_clave, cfi_nombre, ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
                JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento e USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE sts_id = 1 AND ecf_id IN(SELECT ecf_id FROM poa_OrigenRecurso WHERE epg_id = :epg);';
        $combo->params = [':epg' => is_null($epg) ? 'null' : $epg];
        $combo->comoAtr = 'ecf_id';
        $this->recargaCombo($combo);
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // EL ANTERIOR ERA PARA RELLENAR EL COMBO DE FILTRADO, AQUI ES PARA RELLENAR LA PANTALLA QUE VE EL USUARIO
    // Obtenemos todas las claves de financiamiento y su encuadre, que tengan su status
    // 1 = Habilitada para su uso
    // y que además pertenezcan al encuadre programático actualmente seleccionado
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function cargaClaveFinanciamiento($retorna = false)
    {
        // Por default, podrán ver las claves de financiamiento "vacías", es decir que no tengan ningun objeto del gasto
        // asignado, pero con un checkbox en pantalla pueden pedir ocultarlas
        // Para no mostrar las "vacias" simplemente buscamos en la tabla presupuesto aquellas que ya existan
        if (!isset($this->prm->vacias)) {
            $this->prm->vacias = 1;
        }

        // El combo de la pantalla principal permitirá filtrar o mostrar sólo la clave de financiamiento seleccionada
        // y eso lo controlamos si viene el parámetro "filtroCF", que debe traer la clave de financiamiento
        if (!isset($this->prm->filtroCF)) {
            $this->prm->filtroCF = '';
        }

        $cC = new accesoDatos();
        $epg = ClasificacionAdministrativa::getEpg();

        $parametros = [];

        $sql = '
            SELECT cfi_clave, cfi_nombre, ecf_id
            FROM poa_OrigenRecurso AS por
                JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento USING(ecf_id)
                JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                ' . (($this->prm->vacias == 0) ? 'JOIN presupuesto USING(gpo_id, ur_id, ecf_id)' : '') . '
            WHERE sts_id = 1 AND por.epg_id = :epg';

        $parametros[':epg'] = is_null($epg) ? 'null' : $epg;

        if ($this->prm->filtroCF != '') {
            $sql .= ' AND ecf_id = :ecf';
            $parametros[':ecf'] = $this->prm->filtroCF;
        }

        if ($this->prm->vacias == 0) {
            $sql .= ' GROUP BY ecf_id, cfi_clave, cfi_nombre';
        }

        $sql .= ';';

        $cC->setSql($sql);
        $cC->conectar();
        $cC->consultar($parametros);
        $cC->desconectar();
        $h = '';
        while ($cveFin = $cC->extraer()) {
            $boton = new stdClass();
            $boton->color = 'amarillo';
            $boton->title = 'Agregar objeto del gasto';
            $boton->icon = 'plus';
            $boton->class = 'addObjGto btn-xs';

            $boton->attr = new stdClass();
            $boton->attr->ecf_id = $cveFin['ecf_id'];
            $boton->attr->cveFin = $cveFin['cfi_clave'];
            $boton->attr->cveNom = $cveFin['cfi_nombre'];

            $boton->id = 'newObjGto_' . $cveFin['ecf_id'];
            $this->prm->ecf_id = $cveFin['ecf_id'];
            $h .= '
            <tr>
                <td><span title="' . $cveFin['cfi_nombre'] . '">' . $cveFin['cfi_clave'] . '</span></td>
                <td>' . $this->ponBoton($boton) . '</td>
                <td>
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="10%">P. Generica</th>
                                <th width="10%">P. especifica</th>
                                <th width="30%">Clave Presupuestal</th>
                                <th width="50%">Descripción</th>
                            </tr>
                        </thead>
                        <tbody id="objetGasto' . $cveFin['ecf_id'] . '">
                            ' . $this->cargaObjGas(true) . '
                        </tbody>
                    </table>
                </td>
            </tr>';
        }
        $this->mixScript('
            $(".addObjGto").unbind("click");

            $(".addObjGto").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo: "ponDatosEncuadreActualPgPe",
                    ecf_id: $(this).attr("ecf_id"),
                    cveFin: $(this).attr("cveFin"),
                    cveNom: $(this).attr("cveNom")
                });
            });
        ');
        if ($retorna) {
            return $h;
        } else {
            $this->setContenedor('listaCveFin');
            if ($h == '') {
                $this->mixScript('$("#listaCveFin").empty();');
            } else {
                $this->mixHtml($h);
            }
        }
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // Obtenemos todos los Objetos de Gasto ( Partida Genérica y Específica ) del encuadre programático
    // y de clave de financiamiento actual.   Y armamos el codigo HTML para dibujarlo dentro de una tabla
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function cargaObjGas($retorna = false)
    {
        $cC = new accesoDatos();
        $h = '';

        $epg = ClasificacionAdministrativa::getEpg();
        $sql = 'SELECT p.pg_id, pe_id, p.pre_claveCompleta, p.tcp_id, pe.pe_nombre
            FROM ' . BD_CTRL . '.presupuesto p
            JOIN cat_PartidaEspecifica AS pe USING(pg_id, pe_id)
            WHERE p.epg_id = :epgId AND p.ecf_id = :ecfId;';
        $params = [':epgId' => is_null($epg) ? 'null' : $epg, ':ecfId' => $this->prm->ecf_id];
        $cC->setSql($sql);
        $cC->conectar();
        $cC->consultar($params);
        $cC->desconectar();
        while ($objGto = $cC->extraer()) {
            $h .= '
            <tr>
                <td>' . $objGto['pg_id'] . '</td>
                <td>' . $objGto['pe_id'] . '</td>
                <td>' . $objGto['pre_claveCompleta'] . '-' . $objGto['tcp_id'] . '</td>
                <td>' . $objGto['pe_nombre'] . '</td>
            </tr>';
        }
        if ($retorna) {
            return $h;
        } else {
            $this->setContenedor('objetGasto' . $this->prm->ecf_id);
            if ($h == '') {
                $this->mixScript('$("#objetGasto' . $this->prm->ecf_id . '").empty();');
            } else {
                $this->mixHtml($h);
            }
        }
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // Pantalla para agregar Partida Genérica y Específica, para el encuadre Administrativo y Programático actual
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function dibujaFormaAgregarClaveFinanciamiento()
    {
        $comboCF = new stdClass();
        $comboCF->search = true;
        $comboCF->label = 'Clave de financiamiento';
        $comboCF->id = 'cmbClaveFinanciamiento';
        $comboCF->class = 'cmbClaveFinanciamiento';
        $comboCF->sql = 'SELECT "" AS cfi_clave, "" AS cfi_nombre, 0 AS ecf_id';
        $comboCF->comoAtr = 'ecf_id';
        $this->recargaCombo($comboCF);
        $this->setContenedor('divContenedorGeneralModal');
        $frmCaptura = '
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Clasificación Administrativa y Estructura Programática</legend>
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="row mb-4">
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="cf_ur_info">Unidad Responsable</label>
                                <input id="cf_ur_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="cf_prg_info">Programa</label>
                                <input id="cf_prg_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                        </div>
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="row mb-4">
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="cf_sp_info">Subprograma</label>
                                <input id="cf_sp_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="cf_py_info">Proyecto</label>
                                <input id="cf_py_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                        </div>
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="row mb-4">
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="cf_oa_info">Obra / Actividad</label>
                                <input id="cf_oa_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                        </div>
                    </fieldset>
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponCombo($comboCF) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    <div class="row mb-4">
                        <fieldset class="col-md-4 col-xs-4">
                        </fieldset>
                        <fieldset class="col-md-8 col-xs-8" align="right">
                            <button id="btnGuardarCF" class="btn btn-outline-success me-1">Aceptar</button>
                            <button id="btnCerrar" type="button" class="btn btn-outline-secondary cerrarCF" data-dismiss="modal">Cerrar</button>
                        </fieldset>
                    </div>
                </fieldset>
            </div>
        ';

        $this->mixHtml('
            <div id="myClaveFinanciamiento" class="modal" data-keyboard="false" role="dialog" aria-labelledby="modalNewClaveFinanciamiento" aria-hidden="false" style="padding-top: 70px;">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p>' . $frmCaptura . '</p>
                        </div>
                    </div>
                </div>
            </div>
        ');
        $this->mixScript('
            $(".cerrarCF").click( function() {
                $("#myClaveFinanciamiento").hide();
            });
            $("#btnCerrarCF").click( function() {
                $("#myModalCosteo").hide();
            });

            $("#btnGuardarCF").click(function () {
                if ($("#cmbClaveFinanciamiento").val() != "") {
                    alertify.confirm("REGISTRAR NUEVA CLAVE DE FINANCIAMIENTO", "¿ Confirmas que deseas registrar la Clave de Financiamiento ?",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"agregarCVeFin",
                                ecf_id:$("#cmbClaveFinanciamiento option:selected").attr("ecf_id")
                            });
                        },
                        function(){
                            // Cancelar Operación
                        }
                    ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                } else
                    notify("Selecciona la Clave de Financiamiento","warning");
            });
        ');
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // Pantalla para agregar Partida Genérica y Específica, para el encuadre Administrativo y Programático actual
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function dibujaFormaAgregarPgPe()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $form = new stdClass();
        $form->id = 'datosCosteo';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcCosteo';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'a';
        $form = new form($form);
        $objGasto = new stdClass();
        $objGasto->filtro = 'encuadre';
        $objGasto->changePe = 'comunica("' . $this->pantalla . '",{metodo:"cargaTipoCompra",pg:$("#pg_id").val(),pe:$("#pe_id").val()});';

        // Para que en la llamada del main() no marque error, ya que éstos valores no se le envían
        if (!isset($this->prm->cveFin) || $this->prm->cveFin == null) {
            $this->prm->cveFin = '';
        }
        if (!isset($this->prm->cveNom) || $this->prm->cveNom == null) {
            $this->prm->cveNom = '';
        }
        $combo = new stdClass();
        $combo->label = 'Tipo de Compra';
        $combo->id = 'tcp_id';

        $form->mixHtml('
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Clasificación Administrativa y Estructura Programática</legend>
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="row mb-4">
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="ur_info">Unidad Responsable</label>
                                <input id="ur_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="prg_info">Programa</label>
                                <input id="prg_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                        </div>
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="row mb-4">
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="sp_info">Subprograma</label>
                                <input id="sp_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="py_info">Proyecto</label>
                                <input id="py_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                        </div>
                    </fieldset>
                    <fieldset class="col-md-12 col-xs-12">
                        <div class="row mb-4">
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="oa_info">Obra / Actividad</label>
                                <input id="oa_info" type="text" class="form-control" value="" disabled>
                            </fieldset>
                            <fieldset class="col-md-6 col-xs-6">
                                <label for="cf_info">Clave de Financiamiento</label>
                                <input id="cf_info" type="text" class="form-control" value = "' . $this->prm->cveFin . ' - ' . $this->prm->cveNom . '" disabled>
                            </fieldset>
                        </div>
                    </fieldset>
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . ObjetoGasto::ponObjetoGasto($objGasto) . '
                </fieldset>
            </div>
            <input type="hidden" id="ecf_costeo" name="ecf_costeo" data_id = "ecf_costeo">
                <div class="row mb-4">
                    <fieldset class="col-md-12 col-xs-12">
                        <fieldset class="col-md-4 col-xs-4">
                            ' . $this->templateCombo($combo) . '
                        </fieldset>
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="d-flex justify-content-center">                      
                        <button type="submit" class="btn btn-outline-success me-1" id="acepta-modalNewCosteo" form="datosCosteo">Aceptar</button>
                        <button id="btnCerrarPgPe" type="button" class="btn btn-outline-secondary me-1 cerrar" data-dismiss="modal">Cerrar</button>
                    </fieldset>
                </div>
        ');
        $this->mixHtml('
            <div id="myModalCosteo" class="modal" data-keyboard="false" role="dialog" aria-labelledby="modalNewCosteo" aria-hidden="false" style="padding-top: 70px;">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p>' . $form->ponForm() . '</p>
                        </div>
                    </div>
                </div>
            </div>
        ');
        $this->mixScript('
            $(".cerrarPgPe").click( function() {
                $("#myModalCosteo").hide();
            });
            $("#btnCerrarPgPe").click( function() {
                $("#myModalCosteo").hide();
            });
            $("#acepta-modalNewCosteo").click( function(e) {
                e.preventDefault();
                // Checamos que no se mande ningun dato vacío
                if ( $("#pg_id").val() == "" ) {
                    notify("Debes seleccionar la partida genérica", "error");
                    return false;
                } else if ( $("#pe_id").val() == "" ) {
                    notify("Debes seleccionar la partida especifica", "error");
                    return false;
                } else {
                    alertify.confirm("REGISTRAR NUEVO OBJETO DEL GASTO", "¿ Confirmas que deseas registrar el Objeto del Gasto ?",
                        function(){
                            $("#datosCosteo").submit();
                        },
                        function(){
                            // Cancelar Operación
                        }).set("labels", {ok:"Aceptar", cancel:"Cancelar"}).set("buttonFocus", "cancel");
                }
                e.stopPropagation();
            });
        ');
    }

    private function abcCosteo($cargar = true)
    {
        $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->prm->ecf_id = $this->prm->ecf_costeo;
        $this->prm->total = 0;

        $nr = 0;
        $elimina = '';
        $avisaleTipoCompra = false;

        switch ($this->prm->abc) {
            case 'a':
                // Marcos: 16/mzo/2017
                // Se adiciona la validación de que, si NO tiene permisos para compras consolidadas, NO le permitiremos dar
                // de alta partidas de éste tipo, y a fuerza serán directas
                if ($this->prm->tcp_id != 'D' && UnidadResponsable::excentaComprasConsolidadas(['gpo_id' => $_SESSION[_S]['clasAdm-gpo'], 'ur_id' => $_SESSION[_S]['clasAdm-ur'], 'pe_id' => $this->prm->pe_id, 'ecf_id' => $this->prm->ecf_id])) {
                    $this->prm->tcp_id = 'D';
                    $avisaleTipoCompra = true;
                }

                // Buscamos en la tabla presupuesto si ya existe el registro...
                $this->setSql('
                    SELECT gpo_id
                    FROM ' . BD_CTRL . '.presupuesto
                    WHERE epg_id = :epgId AND ecf_id = :ecfId AND pg_id = :pgId AND pe_id = :peId AND tcp_id = :tcpId;
                ');
                $this->conectar();
                $this->consultar([
                    ':epgId' => ClasificacionAdministrativa::getEpg(),
                    ':ecfId' => $this->prm->ecf_id,
                    ':pgId'  => $this->prm->pg_id,
                    ':peId'  => $this->prm->pe_id,
                    ':tcpId' => $this->prm->tcp_id,
                ]);
                $this->desconectar();
                // Sólo si aún no existe, lo insertamos...
                if ($this->getNumeroRegistros() == 0) {
                    $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

                    $this->conectar();

                    $this->setSql('SET @usr_ip = INET_ATON(:ip);');
                    $this->consultar([':ip' => $ip]);

                    $this->setSql('SET @usr_id = :usrId;');
                    $this->consultar([':usrId' => $_SESSION[_S]['usr_id']]);

                    $this->setSql('SET @bit_observacion = "REGISTRO INSERTADO DESDE PANTALLA DE AJUSTES";');
                    $this->consultar();

                    $this->setSql('
                        INSERT presupuesto (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, prg_id, spr_id, py_id, oa_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio, usr_id, usr_fecha)
                        SELECT gpo_id, ur_id, ue_id, epg_id, :pgId AS pg_id, :peId AS pe_id, ecf_id, :tcpId AS tcp_id, prg_id, spr_id, py_id, oa_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio, :usrId, now()
                        FROM ' . BD_CTRL . '.enc_Programatico
                        JOIN ' . BD_CTRL . '.cat_ObraActividad USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                        JOIN ' . BD_CTRL . '.poa_OrigenRecurso USING(gpo_id, ur_id, epg_id)
                        JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento AS cf USING(ecf_id)
                        WHERE cf.sts_id = 1 AND epg_id = :epgId AND ecf_id = :ecfId;
                    ');
                    $this->consultar([
                        ':pgId'  => $this->prm->pg_id,
                        ':peId'  => $this->prm->pe_id,
                        ':tcpId' => $this->prm->tcp_id,
                        ':usrId' => $_SESSION[_S]['usr_id'],
                        ':epgId' => ClasificacionAdministrativa::getEpg(),
                        ':ecfId' => $this->prm->ecf_id,
                    ]);

                    $this->desconectar();

                    if ($avisaleTipoCompra == true) {
                        $this->mixScript('notify("La UR está exenta de Compras Consolidadas. Se grabará como DIRECTA","warning");');
                    }
                    $this->mixScript('$("#myModalCosteo").hide();');
                } else {
                    $this->mixScript('notify("Objeto del gasto ya agregado","error");');
                }
                // no break
            case 'c':
                break;
            case 'b':
                break;
        }
        $this->cargaObjGas();
    }

    private function cargaTipoCompra()
    {
        $this->prm->pg = (empty($this->prm->pg)) ? '0' : $this->prm->pg;
        $this->prm->pe = (empty($this->prm->pe)) ? '0' : $this->prm->pe;
        $this->conectar();
        $this->setSql('
            SELECT ur_compraDirecta directa
            FROM cat_UnidadResponsable
            WHERE gpo_id = :gpo AND ur_id = :ur;
        ');
        $this->consultar([':gpo' => $_SESSION[_S]['clasAdm-gpo'], ':ur' => $_SESSION[_S]['clasAdm-ur']]);
        $compraUr = $this->extraer();
        if ($compraUr['directa'] == '2') {
            $this->setSql('
                SELECT pe_comprasDirectas = 1 directa
                FROM cat_PartidaEspecifica
                WHERE pg_id = :pg AND pe_id = :pe;
            ');
            $this->consultar([':pg' => $this->prm->pg, ':pe' => $this->prm->pe]);
            $compra = $this->extraer();
            if (!$compra) {
                $this->mixScript('notify("Por favor seleccione una partida específica","warning");');
                return;
            }

            $where = ($compra['directa'] == '1') ? 'WHERE tcp_id = "D"' : (($this->getNumeroRegistros() == 0) ? 'WHERE tcp_id = NULL' : '');
            $where = ($_SESSION[_S]['nvj_id'] > 2 && $_SESSION[_S]['ssc_id'] == 3) ? $where : ' WHERE tcp_id != "P"; ';
        } else {
            $where = 'WHERE tcp_id ="D"';
        }
        $this->desconectar();
        $combo = new stdClass();
        $combo->label = 'Tipo de Compra';
        $combo->id = 'tcp_id';
        $combo->sql = ' SELECT tcp_id, tcp_nombre FROM cat_TipoCompra ' . $where;
        $this->recargaCombo($combo);
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // Dibujamos en la pantalla de alta de Clave de Financiamiento los datos del encuadre actual
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function ponDatosEncuadreActualCF()
    {
        $ejercicio = '';
        // Si no están todos los encuadres seleccionados, no hacemos nada y avisamos
        if (
            (!isset($_SESSION[_S]['clasAdm-prg']) || $_SESSION[_S]['clasAdm-prg'] == '' || $_SESSION[_S]['clasAdm-prg'] == null) ||
            (!isset($_SESSION[_S]['clasAdm-spr']) || $_SESSION[_S]['clasAdm-spr'] == '' || $_SESSION[_S]['clasAdm-spr'] == null) ||
            (!isset($_SESSION[_S]['clasAdm-py']) || $_SESSION[_S]['clasAdm-py'] == '' || $_SESSION[_S]['clasAdm-py'] == null) ||
            (!isset($_SESSION[_S]['clasAdm-oa']) || $_SESSION[_S]['clasAdm-oa'] == '' || $_SESSION[_S]['clasAdm-oa'] == null)
        ) {
            $this->mixScript('notify("Selecciona todos los encuadres ANTES de adicionar Claves de Financiamiento", "error");');
            return false;
        }
        $fechaActual = date('Y-m-d');
        $anio = date('Y', strtotime($fechaActual));
        $epg = ClasificacionAdministrativa::getEpg();
        $parametrosCfi = [':epg' => is_null($epg) ? 'null' : $epg];
        if ($anio == APP_EJERCICIO_ANTERIOR) {
            $ejercicio .= ' AND e.cfi_anio = :ejer ;';
            $parametrosCfi[':ejer'] = substr(APP_EJERCICIO, -2);
        } else {
            $ejercicio .= ';';
        }

        $sqlCfi = 'SELECT DISTINCT cfi_clave, cfi_nombre, ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento e USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE sts_id = 1 AND ecf_id NOT IN (
                SELECT ecf_id FROM ' . BD_CTRL . '.poa_OrigenRecurso WHERE epg_id = :epg )
            ' . $ejercicio;

        $combo = new stdClass();
        $combo->id = 'cmbClaveFinanciamiento';
        $combo->sql = $sqlCfi;
        $combo->params = $parametrosCfi;
        $combo->comoAtr = 'ecf_id';
        $this->recargaCombo($combo);

        // Marcos: 01/feb/2017
        // Obtenemos los nombres de los encuadres que aplican para el objeto del gasto que van a dar de alta
        $sql = '
        SELECT ur.ur_nombre, p.prg_nombre, sp.spr_nombre, py.py_nombre, oa.oa_nombre
        FROM enc_Programatico ep
            JOIN cat_UnidadResponsable ur USING(gpo_id, ur_id)
            JOIN cat_Programa p USING(gpo_id, ur_id, prg_id)
            JOIN cat_Subprograma sp USING(gpo_id, ur_id, prg_id, spr_id)
            JOIN cat_Proyecto py USING(gpo_id, ur_id, prg_id, spr_id, py_id)
            JOIN cat_ObraActividad oa USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
        WHERE gpo_id = :gpo AND ur_id = :ur AND prg_id = :prg AND spr_id = :spr AND py_id = :py AND oa_id = :oa;';

        $parametros = [
            ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur'  => $_SESSION[_S]['clasAdm-ur'],
            ':prg' => $_SESSION[_S]['clasAdm-prg'],
            ':spr' => $_SESSION[_S]['clasAdm-spr'],
            ':py'  => $_SESSION[_S]['clasAdm-py'],
            ':oa'  => $_SESSION[_S]['clasAdm-oa'],
        ];

        $in = new AccesoDatos();
        $in->conectar();
        $in->setSql($sql);
        $in->consultar($parametros);
        $info = $in->extraer();
        $in->desconectar();

        $this->mixScript('
            $("#cf_ur_info").val("' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . ' - ' . mysql_escape_mimic($info['ur_nombre']) . '");
            $("#cf_prg_info").val("' . $_SESSION[_S]['clasAdm-prg'] . ' - ' . mysql_escape_mimic($info['prg_nombre']) . '");
            $("#cf_sp_info").val("' . $_SESSION[_S]['clasAdm-spr'] . ' - ' . mysql_escape_mimic($info['spr_nombre']) . '");
            $("#cf_py_info").val("' . $_SESSION[_S]['clasAdm-py'] . ' - ' . mysql_escape_mimic($info['py_nombre']) . '");
            $("#cf_oa_info").val("' . $_SESSION[_S]['clasAdm-oa'] . ' - ' . mysql_escape_mimic($info['oa_nombre']) . '");
            $("#cf_oa_info").val("' . $_SESSION[_S]['clasAdm-oa'] . ' - ' . mysql_escape_mimic($info['oa_nombre']) . '");
            $("#myClaveFinanciamiento").show();
        ');
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // Dibujamos en la pantalla de alta de Objeto de Gasto los datos del encuadre actual
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function ponDatosEncuadreActualPgPe()
    {
        // Si no están todos los encuadres seleccionados, no hacemos nada y avisamos
        if (
            (!isset($_SESSION[_S]['clasAdm-prg']) || $_SESSION[_S]['clasAdm-prg'] == '' || $_SESSION[_S]['clasAdm-prg'] == null) ||
            (!isset($_SESSION[_S]['clasAdm-spr']) || $_SESSION[_S]['clasAdm-spr'] == '' || $_SESSION[_S]['clasAdm-spr'] == null) ||
            (!isset($_SESSION[_S]['clasAdm-py']) || $_SESSION[_S]['clasAdm-py'] == '' || $_SESSION[_S]['clasAdm-py'] == null) ||
            (!isset($_SESSION[_S]['clasAdm-oa']) || $_SESSION[_S]['clasAdm-oa'] == '' || $_SESSION[_S]['clasAdm-oa'] == null)
        ) {
            $this->mixScript('notify("Selecciona todos los encuadres ANTES de adicionar Objetos de Gasto", "error");');
            return false;
        }

        // Marcos: 01/feb/2017
        // Obtenemos los nombres de los encuadres que aplican para el objeto del gasto que van a dar de alta
        $sql = '
        SELECT ur.ur_nombre, p.prg_nombre, sp.spr_nombre, py.py_nombre, oa.oa_nombre
        FROM enc_Programatico ep
            JOIN cat_UnidadResponsable ur USING(gpo_id, ur_id)
            JOIN cat_Programa p USING(gpo_id, ur_id, prg_id)
            JOIN cat_Subprograma sp USING(gpo_id, ur_id, prg_id, spr_id)
            JOIN cat_Proyecto py USING(gpo_id, ur_id, prg_id, spr_id, py_id)
            JOIN cat_ObraActividad oa USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
        WHERE gpo_id = :gpo AND ur_id = :ur AND prg_id = :prg AND spr_id = :spr AND py_id = :py AND oa_id = :oa;';

        $parametros = [
            ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur'  => $_SESSION[_S]['clasAdm-ur'],
            ':prg' => $_SESSION[_S]['clasAdm-prg'],
            ':spr' => $_SESSION[_S]['clasAdm-spr'],
            ':py'  => $_SESSION[_S]['clasAdm-py'],
            ':oa'  => $_SESSION[_S]['clasAdm-oa'],
        ];

        $in = new AccesoDatos();
        $in->conectar();
        $in->setSql($sql);
        $in->consultar($parametros);
        $info = $in->extraer();
        $in->desconectar();

        // Marcos: 01/mzo/2017
        // La descripción de un proyecto tenía un caracter 10 (soft return) y daba problemas, por eso depuramos los nombres
        $this->mixScript('
            $("#ur_info").val("' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . ' - ' . str_replace(["\r\n", "\r", "\n"], '', addslashes($info['ur_nombre'])) . '");
            $("#prg_info").val("' . $_SESSION[_S]['clasAdm-prg'] . ' - ' . str_replace(["\r\n", "\r", "\n"], '', addslashes($info['prg_nombre'])) . '");
            $("#sp_info").val("' . $_SESSION[_S]['clasAdm-spr'] . ' - ' . str_replace(["\r\n", "\r", "\n"], '', addslashes($info['spr_nombre'])) . '");
            $("#py_info").val("' . $_SESSION[_S]['clasAdm-py'] . ' - ' . str_replace(["\r\n", "\r", "\n"], '', addslashes($info['py_nombre'])) . '");
            $("#oa_info").val("' . $_SESSION[_S]['clasAdm-oa'] . ' - ' . str_replace(["\r\n", "\r", "\n"], '', addslashes($info['oa_nombre'])) . '");
            $("#cf_info").val("' . $this->prm->cveFin . ' - ' . str_replace(["\r\n", "\r", "\n"], '', addslashes($this->prm->cveNom)) . '");
            $("#ecf_costeo").val("' . $this->prm->ecf_id . '");
            $("#myModalCosteo").show();
        ');
    }

    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    // Agregamos la nueva Clave de Financiamiento
    // ------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------
    private function agregarCVeFin()
    {
        $this->conectar();

        $this->setSql('SET @usr_ip  = ' . BD_SINPRES . '.fn_get_ip();');
        $this->consultar();

        $this->setSql('SET @usr_id = :usrId;');
        $this->consultar([':usrId' => $_SESSION[_S]['usr_id']]);

        $this->setSql('SET @bit_observacion = "REGISTRO INSERTADO DESDE PANTALLA DE AJUSTES";');
        $this->consultar();

        $this->setSql('INSERT INTO poa_OrigenRecurso (gpo_id, ur_id, epg_id, ecf_id, ore_folioModif, ore_folioLib, ore_oficioLib, ore_oficioAut, ore_idObra, usr_id, usr_fecha)
                        VALUES (:gpo, :ur, :epg, :ecf, 0, 0, "", "", "", :usr, now());');
        $this->consultar([
            ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur'  => $_SESSION[_S]['clasAdm-ur'],
            ':epg' => ClasificacionAdministrativa::getEpg(),
            ':ecf' => $this->prm->ecf_id,
            ':usr' => $_SESSION[_S]['usr_id'],
        ]);

        $this->desconectar();

        $this->cargaClaveFinanciamiento();
        $this->cargaCveFin();

        $this->mixScript('$("#myClaveFinanciamiento").hide();');
    }
}

new ControladorAltaClave();
