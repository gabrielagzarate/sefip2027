<?php

/**
 * Description of ControladorTelefonia
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se estandarizan estilos botones : regresar, imprimir, guardar
 * Objective: Consultar y dar de alta nuevas cedulas de Telefonia por Unidad Responsable
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 * */

//inicio de sesion y llamada a las librerias
session_start();
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/poa/Telefonia/Telefonia.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../ent/Entidad.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';
require_once __DIR__ . '/../../srv/ClasificacionFinanciamiento.php';
require_once __DIR__ . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once __DIR__ . '/../../srv/ObjetoGasto.php';
require_once __DIR__ . '/../../lib/formatoTexto.php';
require_once __DIR__ . '/../../lib/limpia_espacios.php';
require_once __DIR__ . '/../../lib/recuperarUe.php';

class ControladorTelefonia extends WebResponse
{
    public $nvj;
    public $adm_gpo;
    public $adm_ur;
    public $adm_ue;
    public $ur;
    public $cierre;
    public $estadoCosteo;
    public $escritura;
    public $lectura;
    public $cve_id;
    public $telefonia;

    public function __construct()
    { //Constructor
        //mandar a llamar constructor base
        parent::__construct();

        //variables de session
        $this->nvj = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] != '') ? $_SESSION[_S]['nvj_id'] : '';
        $this->adm_gpo = (isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '') ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '') ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->adm_ue = (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') ? $_SESSION[_S]['clasAdm-ue'] : '';

        //Instanciando la Ur
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->adm_gpo);
        $this->ur->setUr_id($this->adm_ur);
        $this->ur->select();
        $this->cierre = $this->ur->getEur_id();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo(); // 1 = Abierto ; 2 = Cerrado

        // Asignación de permisos de lectura y escritura
        if (isset($_SESSION[_S]['prm']['A']['3']['3']['2']) && ($this->nvj == 1 || $this->nvj == 2) && ($this->cierre == 4 && $this->estadoCosteo == 1)) {
            $this->escritura = true; //si tiene permisos de escritura
        } else {
            $this->escritura = false; //si no tiene permisos de escritura
        }
        if (isset($_SESSION[_S]['prm']['A']['3']['3']['1'])) {
            $this->lectura = true; //si tiene permisos de lectura
        } else {
            $this->lectura = false; //si no tiene permisos de lectura
        }

        $this->cve_id = (isset($this->prm->cve_id) ? $this->prm->cve_id * 1 : 0);
        switch ($this->cve_id) {
            case 1:
                $this->telefonia = 'Convencional';
                break;
            case 2:
                $this->telefonia = 'Celular';
                break;
            default:
                $this->telefonia = '';
                break;
        } // fin switch

        switch ($this->metodo) { //evaluar el metodo para saber que funcion hay que ejecutar
            case 'selecciona':
                $this->selecciona();
                break;
            case 'inicial':
                $this->inicial();
                break;
            case 'llenaTabla':
                $this->llenaTabla();
                break;
            case 'formulario':
                $this->formulario();
                break;
            case 'checkNumero':
                $this->checkNumero();
                break;
            case 'guardar':
                $this->guardar();
                break;
            case 'muestraCveFin':
                ClasificacionFinanciamiento::muestraCveFin();
                break;
            case 'validaCveFinModal':
                ClasificacionFinanciamiento::validaCveFinModal();
                break;
            default:
                $this->selecciona();
                break;
        } //fin switch

        $this->returnJson();
    } //fin __construct

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Primera pantalla, donde seleccionan si es cve_id convencional o celular
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function selecciona()
    { //Pantalla de Seleccion
        $this->agregaTools(' ');
        $this->mixScript('
            $("#techos").show();
            limpiaClasAdmin();
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e){
                $(".breadcrumbs > li.current").remove();
                e.preventDefault();
                $("#homeMenu").click();
                e.stopPropagation();
            });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        if ($this->estadoCosteo == 2) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                                    <strong>El Estado del costeo de la Unidad Responsable ha sido cerrado</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>');
        }

        $cardInvetario = new stdClass();
        $cardInvetario->id = 'tel_fija';
        $cardInvetario->titulo = 'Telefonía';
        $cardInvetario->img = 'img/Convencional.png';
        $cardInvetario->label = 'Telefonía convencional';
        $cardInvetario->title = 'Telefonía convencional';

        $cardPlazas = new stdClass();
        $cardPlazas->id = 'tel_cel';
        $cardPlazas->titulo = 'Telefonía';
        $cardPlazas->img = 'img/Celular.png';
        $cardPlazas->label = 'Telefonía celular';
        $cardPlazas->title = 'Telefonía celular';

        $this->mixHtml($this->ponCards([$cardInvetario, $cardPlazas]));

        $this->mixScript('
                $("#SitemaActivo").show();

                DesactivaAtajosMenu();
                Mousetrap.bind("esc esc", function(e){
                    $(".breadcrumbs > li.current").remove();
                    e.preventDefault();
                    $("#btnregresarindex").click();
                    e.stopPropagation();
                });

                $("#tel_fija").click(function(){
                    console.log("Convencional");
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"inicial",
                        cve_id:1
                    });
                });

                $("#tel_cel").click(function(){
                    console.log("Celular");
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"inicial",
                        cve_id:2
                    });
                });

                // Regresa a la pantalla principal y activa los atajos de menus
                $("#homeMenu").click( function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
                });

            ');
    } // fin selecciona

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Función principal de Telefonía Convencional y Celular
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function inicial()
    {
        $botones = '';
        $btnNuevoTelefono = new stdClass();
        $btnNuevoTelefono->id = 'btnNuevoTelefono';
        $btnNuevoTelefono->name = 'btnNuevoTelefono';
        $btnNuevoTelefono->title = 'Registrar nuevo telefono';
        $btnNuevoTelefono->value = 'Nuevo';
        $btnNuevoTelefono->type = 'button';
        $btnNuevoTelefono->color = 'azul';
        $btnNuevoTelefono->class = 'me-1';
        $btnNuevoTelefono->icon = 'fa-solid fa-plus me-2';
        $btnNuevoTelefono->click = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"formulario",
                        cve_id:"' . $this->cve_id . '",
                        abc:"a"
                    });

                    $("#tituloPantalla").text("Telefonía ' . $this->telefonia . ' - Agregar nueva cédula");
                ';

        $this->ponBreadcrumbs('TELEFONIA ' . $this->telefonia);

        $this->agregaTools('<button class="btn btn-outline-secondary me-1" value="Regresar" name="btnregresarindex" id="btnregresarindex">
                                    <span class="fa fa-circle-left me-2"></span>
                                        Regresar
                                </button>');

        // Si la planeación NO está autorizada, no le permitimos modificar nada
        if ($this->cierre < 4) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                                    <strong>La Planeación NO ha sido autorizada. No se permite costear</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>');
        }
        // Si el costeo está cerrado, avisamos
        if ($this->estadoCosteo == 2) {
            $this->mixHtml('<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                                    <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>');
        }
        // Si tiene derechos de escritura, puede registrar una nueva cédula de Telefonia
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnNuevoTelefono));
        } elseif ($this->lectura) {
            $this->mixHtml('
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                        <strong>Solo tiene permiso de lectura</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                ');
        }

        $botones .= '<a id="imprimirTodo" target="_blank" class="btn btn-test" title="Imprimir Cédula de Telefonía" firma="0" style="padding: 0" >
                                <button type="button" class="btn btn-outline-danger me-1" >
                                    <i class="fa fa-file-pdf me-2"></i>PDF
                                </button>
                        </a>
                        <input type="search" width=50 id="filtroTabla" style="display: inline; width:250px; height: 30px;
                        " class="form-control me-1" placeholder="Filtrar Cédulas de Telefonia" />';
        $this->agregaTools($botones);

        $tabla = new stdClass();
        $tabla->id = 'tbl-Conceptos';
        $tabla->titulo = 'Conceptos Ingresados';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTabla';
        $tabla->comunica->cve_id = $this->cve_id;

        $tr = [];
        if ($this->escritura) {
            $tmp = 'Ver/Editar';
        } elseif ($this->lectura) {
            $tmp = 'Ver';
        }

        $th = new stdClass();
        $th->texto = $tmp;
        $th->width = '10%';
        array_push($tr, $th); //AGREGA UN th al array  tr  que viene siendo la primera fila
        // Datos del Programa, Subprograma, Proyecto, Obra/Actividad
        $th = new stdClass();
        $th->texto = 'Programático';
        array_push($tr, $th);
        // Datos de la Clave de Financiamiento
        $th = new stdClass();
        $th->texto = 'Clave Financiamiento';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Número de teléfono';
        $th->width = '21%';
        $th->align = 'left';
        array_push($tr, $th); /// y th son las celdas de cabecera
        $th = new stdClass();
        $th->texto = 'Ubicación';
        $th->width = '21%';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Renta mensual';
        $th->width = '21%';
        $th->align = 'right';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Importe anual';
        $th->width = '21%';
        $th->align = 'right';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->class = 'addModCedula';
        $td->name = 'ced_id';
        $td->id = 'ced_id';

        if ($this->escritura) {
            $td->icono = 'pencil';
        } elseif ($this->lectura) {
            $td->icono = 'eye';
        }

        $td->attr = new stdClass();
        $td->attr->ced_id = 'ced_id';
        $td->attr->ccd_id = 'ccd_id';
        array_push($trbody, $td);

        //Datos del Programa, Subprograma, Proyecto, Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'programatico';
        array_push($trbody, $td);

        //Datos de la Clave de Financiamiento
        $td = new stdClass();
        $td->campoTxt = 'cvefin';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_numeroCelular';
        $td->align = 'left';
        $td->formato = 'telefono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'ccc_id';
        $td->casos->valor = '0';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->campoTxt = 'tcl_ubicacion';
        $td->casos->falso = new stdClass();
        $td->casos->falso->campoTxt = 'utf_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tcl_mensualidad';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla)); //Manda a escribir la tabla en html(solo la tabla)
        $this->llenaTabla();

        $this->mixScript('
                var progID = "encProg";
                var progNom = "nomProg";
                var cveFID = "encCveFin";
                var cveFNom = "nomCveFin";
                limpiaClasAdmin();

                setTimeout( function() {
                        $( "#filtroTabla" ).focus()
                    },
                    300
                );

                $("#imprimirTodo").click(function(){
                    epg_id= $(this).attr("epg_id");
                    ecf_id= $(this).attr("ecf_id");
                    firma= $(this).attr("firma");

                    var form = $("<form>")
                        .attr("action", "rep/doc/poa/rep_Cedula.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                        .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 3))
                        .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))
                        .append($("<input>").attr("type", "hidden").attr("name", "cve_id").attr("value", ' . $this->cve_id . '))

                    $("body").append(form);
                    form.submit();
                    form.remove();

                });


                $("#btnregresarindex").click(function(){
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '",{metodo:"presupuestacion"});
                    return false;
                });

            '); //fin mixScript
    } //fin inicial

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Mostramos todos los teléfonos registrados de la telefonía actual
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function llenaTabla()
    {
        $and = '';
        $params = [
            'gpo' => $this->adm_gpo,
            'ur'  => $this->adm_ur,
            'cve' => $this->cve_id,
        ];
        if ($this->nvj == 1) {
            $params['ue'] = $this->adm_ue;
            $and = ' AND ue_id = :ue ';
        }
        $sql = "
                SELECT t.ccd_id, t.ced_id, t.gpo_id, t.ur_id, t.ue_id, t.epg_id, t.pg_id, t.pe_id, t.ecf_id, t.tcl_ubicacion, t.tcl_mensualidad, t.tcl_numeroCelular, ubi.utf_nombre,
                CONCAT( t.gpo_id, t.ur_id, '-', t.ue_id, '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id ) AS programatico,
                CONCAT( c.cff_id, '-', c.fte_id, c.sft_id, c.ori_id, c.sor_id, '-', c.cfi_id, c.cfi_anio ) AS cvefin,
                cargo.ccc_id, cargo.ccc_nombre, cargo.ccc_montoMaximo, ubi.utf_id, ubi.utf_nombre,
                c.fte_id, c.sft_id, c.ori_id, c.sor_id, c.cfi_id, c.cfi_anio,
                ( t.mon_ene + t.mon_feb + t.mon_mar + t.mon_abr + t.mon_may + t.mon_jun + t.mon_jul + t.mon_ago + t.mon_sep + t.mon_oct + t.mon_nov + t.mon_dic ) AS total
                FROM poa_TelefoniaCelular t
                INNER JOIN cat_CargoCuotaCelular cargo USING( ccc_id )
                INNER JOIN cat_UbicacionTelefonia ubi USING( utf_id )
                INNER JOIN enc_ClaveFinanciamiento c USING( ecf_id )
                INNER JOIN enc_Programatico p USING( epg_id, gpo_id, ur_id )
                WHERE gpo_id = :gpo AND ur_id = :ur
                    " . $and . '
                    AND cve_id = :cve
            ';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Conceptos';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;

        $this->wrLlenaTabla($nav);

        $this->mixScript('

                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").remove();

                $(".addModCedula").click(function() {
                    console.log("Telefonía . - Actualizar cedula");
                    comunica("' . $this->pantalla . '",{
                        metodo:"formulario",
                        cve_id:"' . $this->cve_id . '",
                        ced_id:$(this).attr("ced_id"),
                        abc:"c"
                    });

                    return false;
                });

                $(document).ready(function(){
                    $("#tabla-tbl-Conceptos").filterTable({
                        inputSelector: "#filtroTabla"
                    });
                });

            ');
    } //fin llenaTabla

    /*-------------------------------------------------------------------------------------------------------------------------------------------
    Pantalla para adicionar o modificar cédula de telefonía
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    public function formulario()
    {
        $ced_id = isset($this->prm->ced_id) ? 'ced_id:"' . $this->prm->ced_id . '",' : '';

        if ($this->prm->abc == 'c') {
            $telefono = new TelefoniaCelular($this->prm);
        } elseif ($this->escritura) {
            $telefono = new TelefoniaCelular();
        }

        $this->agregaTools('<button class="btn btn-outline-secondary me-1" value="Regresar" id="btnregresarindex">
                                    <span class="fa-solid fa-circle-left me-2"></span>
                                        Regresar
                                    </button>');

        $tmpguar = '';

        if ($this->cierre == 5 || $this->estadoCosteo == 2) {
            $this->mixHtml('
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                        <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                ');
        } elseif ($this->escritura) {
            $btnguardar = new stdClass();
            $btnguardar->id = 'btnguardar';
            $btnguardar->name = 'btnguardar';
            $btnguardar->text = 'Guardar';
            $btnguardar->value = 'Guardar';
            $btnguardar->type = 'button';
            $btnguardar->color = 'verde';
            $btnguardar->class = 'me-1';

            $btnguardar->icon = 'floppy-disk me-2';
            $btnguardar->click = '
                    var unidades = new Array();
                    $(".mesu").each(function(index,val){
                        unidades[index]= unformatNumber($(this).val());
                    });

                    var montos = new Array();
                    $(".mesm").each(function(index,val){
                        montos[index]= unformatNumber($(this).val());
                    });

                    if (!validarPresupuesto()) {
                    e.preventDefault(); // Detiene el envío
                    }

                    var objArr = {
                        metodo:"guardar",
                        abc:"' . $this->prm->abc . '",
                        ' . $ced_id . '
                        pg_id:$("#pg_id").val(),
                        pe_id:$("#pe_id").val(),
                        utf_id:' . ($this->cve_id == 1 ? '1' : '$("#utf_id").val()') . ',
                        ccc_id:' . ($this->cve_id == 1 ? '0' : '$("#ccc_id").val()') . ',
                        tcl_numeroCelular:replaceAll( replaceAll( replaceAll( $("#tcl_numero").val(), "(", "" ), ")", "" ), "-", "" ),
                        tcl_ubicacion:' . ($this->cve_id == 1 ? '$("#tcl_ubicacion").val()' : '""') . ',
                        tcl_mensualidad:' . ($this->cve_id == 1 ? 'unformatNumber($("#tcl_mensualidad").val())' : '$("#ccc_id").find("option:selected").attr("ccc_montoMaximo")') . ',
                        montos:montos,
                        cve_id:"' . $this->cve_id . '"
                    }
                    ' . ($this->cve_id == 2 ? '
                    if( $.trim($("#tcl_numero").val()) == "" || $.trim($("#ccc_id").val()) == "" || $.trim($("#utf_id").val()) == "" ){
                        document.getElementById("tcl_numero").style.border="#F00 1px solid";
                        document.getElementById("ccc_id").style.border="#F00 1px solid";
                        document.getElementById("utf_id").style.border="#F00 1px solid";
                        notify("Atención \n La información del Registro es obligatoria", "error" );
                    ' : '
                    if( $.trim($("#tcl_numero").val()) == "" || $.trim($("#tcl_ubicacion").val()) == "" || $.trim($("#tcl_mensualidad").val()) == ""){
                        document.getElementById("tcl_numero").style.border="#F00 1px solid";
                        document.getElementById("tcl_ubicacion").style.border="#F00 1px solid";
                        document.getElementById("tcl_mensualidad").style.border="#F00 1px solid";
                        notify("Advertencia \n La información del Registro es obligatoria", "error");
                    ') . '
                    }else if( $.trim($("#tcl_numero").val()).length < 10 ){
                        notify("El número debe tener por lo menos 10 dígitos", "error" );
                    } else if ($("#nomProg").val()=="" ) {
                        notify("Debe seleccionar un encuadre programático","error");
                    } else if ( $("#nomCveFin").val()=="" ) {
                        notify("Debe seleccionar una clave de financiamiento","error");
                    } else {
                        comunica(\'' . $this->pantalla . '\',objArr, true);
                    }
                ';

            $btneliminar = new stdClass();
            $btneliminar->id = 'btneliminar';
            $btneliminar->name = 'btneliminar';
            $btneliminar->text = 'Eliminar';
            $btneliminar->value = 'Eliminar';
            $btneliminar->type = 'button';
            $btneliminar->class = 'me-1';
            $btneliminar->color = 'rojo';
            $btneliminar->icon = 'fa-solid fa-trash me-2';
            $btneliminar->click = '
                    alertify.confirm("ELIMINAR CÉDULA DE TELEFONÍA", "¿ Confirma que desea eliminar la cédula? <h5 style=\"display:inline\"></h5>",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                    metodo:"guardar",
                                    abc:"b",
                                    ' . $ced_id . '
                                    tcl_numeroCelular:replaceAll( replaceAll( replaceAll( $("#tcl_numero").val(), "(", "" ), ")", "" ), "-", "" ),
                                    cve_id:"' . $this->cve_id . '"
                            });
                        },
                        function(){},
                    ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
                ';

            if ($this->nvj <= 2) {
                $tmpguar = $this->ponBoton($btnguardar);
                if ($this->prm->abc == 'c') {
                    $tmpguar .= '&nbsp;&nbsp;' . $this->ponBoton($btneliminar);
                }
            }
        } else {
            $this->mixHtml('
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                        <strong>Solo tiene permiso de lectura</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                ');
        }

        $this->agregaTools($tmpguar . '<a id="imprimir"  cve_id="' . $this->cve_id . '" epg_id="' . $telefono->getEpg_id() . '" ecf_id="' . $telefono->getEcf_id() .
            '" target="_blank" class="btn btn-test" title="Imprimir Cédula de Telefonía" style="padding: 0">
                                            <button type="button" class="btn btn-outline-danger me-1" >
                                                <i class="fa fa-file-pdf me-2"></i>PDF
                                            </button>
                                        </a>');

        $valorEpg = '';
        $nombreEpg = '';
        $valorCF = '';
        $nombreCF = '';

        $objGto = new stdClass();
        $objGto->ccd_id = 3;
        $objGto->cve_id = ($this->cve_id == 1 ? 1 : 2);
        $objGto->filtro = 'costeo';

        $tcl_numero = new stdClass(); //
        $tcl_numero->formato = 'entero';
        $tcl_numero->id = 'tcl_numero'; //indentifier
        $tcl_numero->maxlength = 13;
        $tcl_numero->required = true; //tells if this is required ... i think so
        $tcl_numero->enDiv = true;
        $tcl_numero->label = 'Número de teléfono';
        $tcl_numero->placeholder = '(###)###-####'; //text to show inside the input

        $tcl_ubicacion = new stdClass();
        $tcl_ubicacion->id = 'tcl_ubicacion';
        $tcl_ubicacion->placeholder = 'Calle, Núm, Colonia, Municipio, C.P.';
        $tcl_ubicacion->required = true;
        $tcl_ubicacion->label = 'Especifique una ubicación (Calle, Núm, Colonia, Municipio, C.P.)';
        $tcl_ubicacion->enDiv = true;
        $tcl_ubicacion->maxLength = 100;
        $tcl_ubicacion->maxfocus = '
                    document.getElementById("tcl_ubicacion").style.border="#CCC 1px solid";
            ';

        $tcl_mensualidad = new stdClass();
        $tcl_mensualidad->id = 'tcl_mensualidad';
        $tcl_mensualidad->placeholder = 'Renta mensual';
        $tcl_mensualidad->required = true;
        $tcl_mensualidad->label = 'Renta mensual';
        $tcl_mensualidad->enDiv = true;
        $tcl_mensualidad->maxLength = 9;
        $tcl_mensualidad->formato = 'moneda';
        $tcl_mensualidad->focus = '
                    document.getElementById("tcl_mensualidad").style.border="#CCC 1px solid";
            ';
        $tcl_mensualidad->blur = '
                    if( unformatNumber( $(this).val() ) < 0 ){
                        $(this).val("0");
                        $(this).blur();
                    }

                    var meses = Array("ene","feb","mar","abr","may","jun","jul","ago","sep","oct","nov","dic");
                    var mon = new Array();
                    var importe = unformatNumber( $("#tcl_mensualidad").val() );

                    $.each(meses,function(index,value){
                        mon.push( {"mes":value,"value":importe});
                        $("."+value).attr("disabled","disabled");
                    });

                    $("#mes").calendarioMontos({ montos:mon });
                    $(".mes").blur();
            ';

        $ccc_id = new stdClass();
        $ccc_id->id = 'ccc_id';
        $ccc_id->required = true;
        $ccc_id->enDiv = true;
        $ccc_id->camposVal = 'ccc_id';
        $ccc_id->agrupaPor = 'ccc_id';
        $ccc_id->campoLabelAgrupacion = 'ccc_id';
        $ccc_id->mensaje = 'Selecciona';
        $ccc_id->label = 'Cargo del servidor';
        $ccc_id->comoAtr = 'ccc_montoMaximo';
        $ccc_id->sql = 'SELECT ccc_id, ccc_nombre, ccc_montoMaximo FROM cat_CargoCuotaCelular WHERE ccc_id <>0';
        $ccc_id->focus = '
                    document.getElementById("ccc_id").style.border="#CCC 1px solid";
            ';

        $ccc_id->change = '
                    var meses = Array("ene","feb","mar","abr","may","jun","jul","ago","sep","oct","nov","dic");
                    var mon = new Array();
                    var importe = unformatNumber( $("#ccc_id").find("option:selected").attr("ccc_montoMaximo") );

                    if(importe > 0) {
                        $.each(meses,function(index,value){
                            mon.push( {"mes":value,"value":importe});
                            $("."+value).attr("disabled","disabled");
                        });

                        $("#mes").calendarioMontos({ montos:mon });
                        $(".mes").blur();
                    }
            ';

        $utf_id = new stdClass();
        $utf_id->id = 'utf_id';
        $utf_id->label = 'Ubicación';
        $utf_id->required = true;
        $utf_id->enDiv = true;
        $utf_id->camposVal = 'utf_id';
        $utf_id->agrupaPor = 'utf_id';
        $utf_id->campoLabelAgrupacion = 'utf_nombre';
        $utf_id->mensaje = 'Selecciona una ubicación';
        $utf_id->sql = 'SELECT utf_id, utf_nombre FROM cat_UbicacionTelefonia WHERE utf_id <> 0';
        $utf_id->focus = '
                    document.getElementById("utf_id").style.border="#CCC 1px solid";
            ';

        if ($this->prm->abc == 'c') {

            // Ponemos la clasificación administrativa registrada en la cédula
            $nombreEpg = ClasificacionAdministrativa::PonEnVariablesEpg($telefono->getEpg_id());
            $valorEpg = $telefono->getGpo_id() . $telefono->getUr_id() . '-' . $telefono->getUe_id() . '-' . $_SESSION[_S]['clasAdm-prg'] . '-' . $_SESSION[_S]['clasAdm-spr'] . '-' . $_SESSION[_S]['clasAdm-py'] . '-' . $_SESSION[_S]['clasAdm-oa'];

            //Objeto del gasto
            $objGto->pg_id = $telefono->getPg_id();
            $objGto->pe_id = $telefono->getPe_id();
            $objGto->lectura = true;

            // Ponemos la clave de financiamiento registrada en la cédula
            ClasificacionFinanciamiento::PonEnVariablesEcf($telefono->getEcf_id());
            $cso = isset($_SESSION[_S]['cvefin']->cso) ? $_SESSION[_S]['cvefin']->cso : '';
            $valorCF = $_SESSION[_S]['cvefin']->cff . '-' . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . $cso . '-' . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            $nombreCF = $_SESSION[_S]['cvefin']->nombre;

            $encLectura = ' disabled ';

            $tcl_numero->value = '(' . substr($telefono->getTcl_numeroCelular(), 0, 3) . ')' . substr($telefono->getTcl_numeroCelular(), 3, 3) . '-' . substr($telefono->getTcl_numeroCelular(), 6);
            $tcl_ubicacion->value = $telefono->getTcl_ubicacion();
            $tcl_mensualidad->value = $telefono->getTcl_mensualidad();
            $ccc_id->selecciona = $telefono->getCcc_id();
            $utf_id->selecciona = $telefono->getUtf_id();

            $tcl_numero->readOnly = true;

            $this->mixScript('
                $("#comoSeUsa").click(function(){
                    alertify.alert(
                        "<h5>Cómo seleccionar encuadres...</h5>",
                        "<h5>Cuando te coloques sobre los campos de <b>Encuadre Programático</b> y <b>Clave de Financiamiento</b> verás que el campo cambia de color a verde.   Esto significa que puedes presionar la tecla <b>F1</b> o <b>F2</b> para seleccionar los encuadres.<b>Método 1</b>:Si presionas F1 verás la lista de todas las combinaciones de encuadre programático y claves de financiamiento existentes.Selecciona con doble click la que quieras utilizar y se rellenarán ambos cambos (programático y clave de financiamiento).<b>Método 2:</b>Si presionas F2 activarás la seleccion por combos.En éste caso debes colocarte sobre el campo de <b>Encuadre Programático</b> y presionar F2 para seleccionar cada combo del encuadre.Cuando hayas seleccionado todo el encuadre (hasta Obra/Actividad) cierra la ventana y colócate en el campo de <b>Clave de Financiamiento</b>.   Presiona F2 para mostrar la ventana que te permite seleccionar la Clave de Financiamento.</h5>",
                    );
                }); ');

            $encuadre = '<fieldset class="col-md-12 col-xs-12">
                                <legend>Encuadre Programático y Clave de Financiamiento   <span id="comoSeUsa" style="text-decoration: underline; color:red; cursor: pointer;">¿ cómo se usa ?</span></legend>
                                <label>Encuadre Programático</label>
                                <div class="row mb-4">
                                    <fieldset class="col-md-3 col-xs-3">
                                            <input type="text" value="' . $valorEpg . '" ori="' . $valorEpg . '" enc="' . $telefono->getEpg_id() . '" maxlength="20" ' . $encLectura . ' placeholder="Encuadre (F1/F2)" name="encProg" id="encProg" class=" form-control F1Catalogo" required="true">
                                    </fieldset>
                                    <fieldset class="col-md-9 col-xs-9">
                                            <input type="text" value="' . $nombreEpg . '" disabled placeholder="Nombre Obra/Actividad" name="nomProg" id="nomProg" class=" form-control">
                                    </fieldset>
                                </div>

                                <label>Clave de Financiamiento</label>
                                <div class="row mb-4">
                                    <fieldset class="col-md-3 col-xs-3">
                                            <input type="text" value="' . $valorCF . '" ori="' . $valorCF . '" enc="' . $telefono->getEcf_id() . '" maxlength="12" ' . $encLectura . ' placeholder="Clave (F1/F2)" name="encCveFin" id="encCveFin" class=" form-control F1Catalogo" required="true">
                                    </fieldset>

                                    <fieldset class="col-md-9 col-xs-9">
                                            <input type="text" value="' . $nombreCF . '" disabled placeholder="Nombre de la Clave" name="nomCveFin" id="nomCveFin" class=" form-control">
                                    </fieldset>
                                </div>

                            </fieldset>';
        } elseif ($this->escritura) {
            $clasAdmin = new stdClass();
            $clasAdmin->required = true;
            $clasAdmin->filtro = 'poa';
            $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));

            $this->mixHtml(DibujaPantallaBuscarProgramaFinanciamientoHTML());
            $this->mixScript(DibujaPantallaBuscarProgramaFinanciamientoJS());

            $encLectura = '';

            $tcl_numero->keyup = '
                    var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;
                    switch( event.keyCode ){
                        case 8://backspack
                            return true;
                            break;

                        case 9://tab
                            return true;
                            break;

                        case 46://sup
                            return true;
                            break;

                        default:

                            switch( event.key ){
                                case "("://(
                                    if( $(this).val().length == 0 )
                                        $(this).val("(");

                                    return true;
                                    break;

                                case ")"://)
                                    if( $(this).val().length == 4 )
                                        $(this).val($(this).val()+")");

                                    return true;
                                    break;

                                case "-"://-
                                    if( $(this).val().length == 8 )
                                        $(this).val($(this).val()+"-");

                                    return true;
                                    break;

                                default:

                                    var fon = replaceAll( replaceAll( replaceAll( $("#tcl_numero").val(), "(", "" ), ")", "" ), "-", "" );

                                    if( event.key >= 0 && event.key <= 9 && fon.length <= 10 ){

                                        if( fon.length < 3 ){
                                            txt = "(" + fon.substr( 0 );
                                        }else if( fon.length < 6 ){
                                            txt = "(" + fon.substr( 0, 3 ) + ")" + fon.substr( 3 );
                                        }else{
                                            txt = "(" + fon.substr( 0, 3 ) + ")" + fon.substr( 3, 3 ) + "-" + fon.substring( 6 );
                                        }

                                        $(this).val( txt );

                                        return true;

                                    }else{

                                        $(this).val( $(this).val() );

                                        return false;

                                    }
                                    break;
                            }
                            return false;
                            break;
                    }
                ';
            $tcl_numero->keypress = '
                    var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;
                    switch( key ){
                        case 8://backspack
                            return true;
                            break;

                        case 9://tab
                            return true;
                            break;

                        case 46://sup
                            return true;
                            break;

                        default:

                            var fon = replaceAll( replaceAll( replaceAll( $("#tcl_numero").val(), "(", "" ), ")", "" ), "-", "" );

                            if( fon.length < 3 ){
                                txt = "(" + fon.substr( 0 );
                            }else if( fon.length < 6 ){
                                txt = "(" + fon.substr( 0, 3 ) + ")" + fon.substr( 3 );
                            }else{
                                txt = "(" + fon.substr( 0, 3 ) + ")" + fon.substr( 3, 3 ) + "-" + fon.substring( 6 );
                            }
                            $(this).val( txt );

                            if( key >= 48 && key <= 57 && fon.length <= 10 ){
                                return true;
                            }

                            console.log("tecla de funcion incorrecta -" + key + "-" );
                            return false;
                            break;
                    }
                ';
            $tcl_numero->click = '
                    $("#tcl_numero").selectRange( $("#tcl_numero").val().length );
                    return false;
                ';
            $tcl_numero->blur = '
                    if( $(this).val().length < 13 ){
                        notify("El número debe tener por lo menos 10 dígitos", "warning" );
                    }

                    if( $.trim($("#tcl_numero").val()) != "" ){
                        comunica("' . $this->pantalla . '",{
                            metodo:"checkNumero",
                            cve_id:"' . $this->cve_id . '",
                            abc:"' . $this->prm->abc . '",
                            numero:replaceAll( replaceAll( replaceAll( $("#tcl_numero").val(), "(", "" ), ")", "" ), "-", "" )
                        });
                    }
                ';
            $encuadre = CatalogoProgramaticoFinanciamiento::ponF1();
        }

        $mon = $telefono->getMontos()->getArrayMeses();

        //inicial
        $html = '
                <form role="form">
                    <div class="bs-example">
                        <div class="row mb-4">
                            ' . $encuadre . '
                        </div>

                        <div class="row mb-4">
                            <fieldset class="col-md-12 col-xs-12">
                            ' . ObjetoGasto::ponObjetoGasto($objGto) . '
                            </fieldset>
                        </div>

                        <div class="row mb-4">
                            <fieldset class="col-md-12 col-xs-12">
                                <legend>Informaci&oacute;n del registro
                                    <span style="float: right;">
                                        <input width=50 id="saldoDisponible" style="display: inline; width:300px; height: 30px; text-align:right;" class="form-control poaSaldoDisponible" tabindex="-1" placeholder="Techo Presupuestal Disponible" />
                                    </span>
                                </legend>
                                <div class="row mb-4">
                                    <fieldset class="col-md-2 col-xs-2">' . $this->ponInput($tcl_numero) . '</fieldset>
                                    ' . ($this->cve_id == 1 ? '
                                    <fieldset class="col-md-2 col-xs-2">' . $this->ponInput($tcl_mensualidad) . '</fieldset>
                                    <fieldset class="col-md-8 col-xs-8">' . $this->ponInput($tcl_ubicacion) . '</fieldset>
                                    ' : '
                                    <fieldset class="col-md-5 col-xs-5">' . $this->ponCombo($utf_id) . '</fieldset>
                                    <fieldset class="col-md-5 col-xs-5">' . $this->ponCombo($ccc_id) . '</fieldset>') . '
                                </div>
                            </fieldset>
                        </div>
                        <fieldset class="col-md-12">
                            <legend>Calendarizaci&oacute;n</legend>
                            <Calendario id="mes"></Calendario><!-- Elemento calendario que se pintara con Telefonia.php -->
                        </fieldset>
                    </div>
                    <br>
                    <br>
                </form>
                <div id="contenedorCFModal"></div>
            ';

        $this->mixHtml($html);

        $this->mixScript('
            function validarPresupuesto() {
                // Obtener valores del DOM
                let saldo = $("#saldoDisponible").val();
                let total = $(".totm").val();

                // Limpiar y convertir los valores a número
                let totalCosteo = parseFloat(total.replace("$", "").replace(/\s/g, "").replace(/,/g, ""));
                let saldoDisponible = parseFloat(saldo.replace(/[^0-9.]/g, ""));

                // Validar si se excede el techo presupuestal
                if (totalCosteo > saldoDisponible) {
                    notify("Se excede el techo presupuestal", "warning");
                    return false;
                }

                // Todo OK
                return true;
            }
        ');

        $this->mixScript('
                $("#imprimir").click(function(){
                    epg_id= $(this).attr("epg_id");
                    ecf_id= $(this).attr("ecf_id");
                    cve_id= $(this).attr("cve_id");

                    var form = $("<form>")
                        .attr("action", "rep/doc/poa/rep_Cedula.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                        .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", ' . $objGto->ccd_id . '))
                        .append($("<input>").attr("type", "hidden").attr("name", "cve_id").attr("value", ' . $this->cve_id . '))

                    $("body").append(form);
                    form.submit();
                    form.remove();

                });

                // Atajos de ésta pantalla
                Mousetrap.bind("ctrl+enter", function(e){
                    e.preventDefault();
                    $("#btnguardar").click();
                });

                $.fn.selectRange = function(start, end) {
                    if(!end) end = start;
                    return this.each(function() {
                        if (this.setSelectionRange) {
                            this.focus();
                            this.setSelectionRange(start, end);
                        } else if (this.createTextRange) {
                            var range = this.createTextRange();
                            range.collapse(true);
                            range.moveEnd("character", end);
                            range.moveStart("character", start);
                            range.select();
                        }
                    });
                };

                $("#mes").calendarioMontos({
                    montos:' . (json_encode($mon)) . ',
                });

                Mousetrap.bind("esc esc", function(e) {
                    $(".breadcrumbs > li.current").remove();
                    e.preventDefault();
                    Mousetrap.bind("esc esc", function(e) {
                        e.preventDefault();
                        $("#homeMenu").click();
                        e.stopPropagation();
                    });
                    Mousetrap.unbind("ctrl+enter");
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '");
                    e.stopPropagation();
                });

                // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
                $(".breadcrumbs").click( function(e) {
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                });

                $("#saldoDisponible").keydown(false);

                $("#encProg, #encCveFin, button[data-id=pg_id], button[data-id=pe_id]").blur(function() {
                    comunica("lib/ControladorFunciones.php",{
                        metodo:"muestraDisponible",
                        ecf: $("#encCveFin").attr("enc"),
                        pg: $("#pg_id").val(),
                        pe: $("#pe_id").val()
                    });
                });

                $("#btnregresarindex").click(function(){
                    $(".breadcrumbs > li.current").remove();
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("' . $this->pantalla . '",{metodo:"inicial", cve_id:"' . $this->cve_id . '"});
                    return false;
                });

            '); // fin mixScript

        if ($this->prm->abc == 'c') {
            $this->mixScript('
                    $("#tcl_mensualidad").blur();
                    $("#ccc_id").change();
                    setTimeout( function() { $( "#tcl_ubicacion" ).focus() }, 100 );
                    $("#encCveFin").blur();
                ');
        } else {
            $this->mixScript('setTimeout( function() { $( "#encProg" ).focus() }, 100 );');
        }
    } //fin formulario

    public function guardar()
    { //Funcion al estilo abc
        $this->prm->tcl_numeroCelular = limpia_espacios($this->prm->tcl_numeroCelular);

        if (strlen($this->prm->tcl_numeroCelular) == 10) {
            switch ($this->prm->abc) {
                case 'a': // guardar
                    $this->prm->gpo_id = $this->adm_gpo;
                    $this->prm->ur_id = $this->adm_ur;

                    //Recupera la UE de la obra actividad
                    $this->prm->ue_id = recuperarUe(ClasificacionAdministrativa::getEpg());

                    $this->prm->epg_id = $_SESSION[_S]['clasAdm-epg'];
                    $this->prm->ecf_id = $_SESSION[_S]['cvefin']->ecf;

                    $this->prm->bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA';

                    $this->prm->ced_id = TelefoniaCelular::abc($this->prm);

                    $this->mixScript('
                            notify("Cedula de telefonía insertada exitosamente ");
                            $(".breadcrumbs > li.current").remove();

                            $("div[aria-describedby=cargaDatosProgFin]").html("");

                            comunica("' . $this->pantalla . '",{
                                metodo:"inicial",
                                cve_id:"' . $this->cve_id . '"
                            });

                        ');
                    break;

                case 'b': // Eliminar
                    $this->prm->bit_observacion = 'REGISTRO ELIMINADO DESDE PANTALLA';
                    TelefoniaCelular::abc($this->prm);

                    $this->mixScript('
                            notify("Cedula eliminada exitosamente");
                            $(".breadcrumbs > li.current").remove();

                            comunica("' . $this->pantalla . '",{
                                metodo:"inicial",
                                cve_id:"' . $this->cve_id . '"
                            });

                        ');
                    break;

                case 'c':
                    $this->prm->bit_observacion = 'REGISTRO MODIFICADO DESDE PANTALLA';
                    TelefoniaCelular::abc($this->prm);
                    $this->mixScript('
                            notify("Cedula de telefonía actualizada exitosamente ");
                            $(".breadcrumbs > li.current").remove();

                            comunica("' . $this->pantalla . '",{
                                metodo:"inicial",
                                cve_id:"' . $this->cve_id . '"
                            });

                        ');
                    break;
            } // fin switch
        } else {
            $this->mixScript('
                    notify("El numero ' . FormatoTexto($this->prm->tcl_numeroCelular, 'telefono') . ' es incorrecto", "error");
                    $("#tcl_numero").focus();
                ');
        }
    } //end of guardar function

    private function checkNumero()
    {
        $params = [$this->prm->numero];
        $sql = '
                SELECT tcl_numeroCelular AS Numero
                FROM poa_TelefoniaCelular
                WHERE tcl_numeroCelular = ?
            ';

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);

        if ($conexion->getNumeroRegistros() > 0) {
            $row = $conexion->extraer();
            if ($row['Numero'] == $this->prm->numero) {
                $this->mixScript('
                        notify("El numero ' . formatoTexto($this->prm->numero, 'telefono') . ' ya existe", "error");
                        $("#tcl_numero").focus();
                    ');
                return false;
            }
        }
    } // fin checkNumero
} // fin class

if (isset($_SESSION[_S]['prm']['A']['3']['3']['1'])) {
    new ControladorTelefonia();
} else {
    WebResponse::sinPermisos();
}
