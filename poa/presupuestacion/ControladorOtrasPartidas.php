<?php
/**
 * Description of ControladorOtrasPartidas
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se estandarizan estilos botones : regresar, imprimir, guardar
 * Se corrigen validaciones de escritura.
 * Objective: Consultar y dar de alta nuevas cedulas de Otras Partidas por Unidad Responsable
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de escritura y lectura, se basa en el nivel jerarquico del usuario
 * 1. Si tiene permisos de escritura, puede grabar cambios a ésta pantalla
 * 2. Y sólo los de nivel jerárquico 1 y 2 pueden grabar.  Aunque tenga el permiso de escritura en la base de datos
 * Si no es de nivel 1 o 2 no podrá modificar nada, sólo consultar.
 *
 * */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../lib/recuperarUe.php';
require_once __DIR__ . '/../../ent/poa/OtrasPartidas/OtrasPartidas.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';
require_once __DIR__ . '/../../srv/ClasificacionFinanciamiento.php';
require_once __DIR__ . '/../../srv/ObjetoGasto.php';

class ControladorOtrasPartidas extends WebResponse
{
    public string $url;
    public UnidadResponsable $ur;
    public int $cierre;
    public int $planAutorizado;
    public int $estadoCosteo;
    public string $nvj;
    public string $ue;
    public bool $escritura;
    public bool $lectura;

    public function __construct()
    {
        parent::__construct();
        $this->url = $this->pantalla;
        //variables de session
        $this->nvj = (string) (getSession('nvj_id') ?? '');
        $this->ue = (string) (getSession('ue_id') ?? '');
        $gpoSesion = (string) (getSession('clasAdm-gpo') ?? '');
        $urSesion = (string) (getSession('clasAdm-ur') ?? '');

        //Instanciando la Ur
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($gpoSesion);
        $this->ur->setUr_id($urSesion);
        $this->ur->select();
        $this->cierre = (int) $this->ur->getEur_id();
        $this->planAutorizado = (int) $this->ur->getEur_id();
        $this->estadoCosteo = (int) $this->ur->getUr_estadoCosteo(); // 1 = Abierto ; 2 = Cerrado

        // Asignación de permisos de lectura y escritura
        $this->escritura = tienePermiso('A.3.8.' . TAREA_ESCRITURA)
            && ($this->nvj == 1 || $this->nvj == 2)
            && ($this->cierre == 4 && $this->estadoCosteo == 1);

        $this->lectura = tienePermiso('A.3.8.' . TAREA_LECTURA);

        switch ($this->metodo) {
            case 'guardar':
                $this->abcOtrasPartidas();
                break;
            case 'addModCedula':
                $this->addModCedula();
                break;
            case 'llenaTablaArticulos':
                $this->llenaTablaArticulos();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools(" ");
        $texto = "Ver";
        $icono = "eye";

        // Si la planeación NO está autorizada, no le permitimos modificar nada
        if ($this->cierre < 4) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>La Planeación NO ha sido autorizada. No se permite costear</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>');
        }
        // Si el costeo está cerrado, avisamos
        if ($this->estadoCosteo == 2) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>El costeo de la Unidad Responsable ha sido cerrado</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>');
        }

        // Si tiene derechos de escritura, puede registrar una nueva cédula de Otras Partidas
        if ($this->escritura) {
            $boton = new stdClass();
            $boton->id = 'nuevo';
            $boton->icon = 'solid fa-plus me-2';
            $boton->name = 'nuevo';
            $boton->class = 'me-1';
            $boton->color = 'azul';
            $boton->value = ' Nuevo';
            $boton->title = 'Nuevo Registro de Otras Partidas';
            $this->agregaTools($this->ponBoton($boton));
            $texto = "Ver/Editar";
            $icono = "pencil";
        } elseif ($this->lectura) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>Solo tiene permiso de lectura</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            ');
        }

        $this->agregaTools('<a id="imprimirTodo" target="_blank" class="btn btn-test" title="Imprimir Cédula de Otras Partidas" style="padding: 0"><button type="button" class="btn btn-outline-danger me-1" ><i class="fa fa-file-pdf me-2"></i>PDF</button></a>');

        // Cuadro de búsqueda (filtra las Unidades Responsables a mostrar)
        $this->agregaTools('<input type="search" width=50 id="filtroTabla" style="display: inline; width:250px; height: 30px;"class="form-control" placeholder="Filtrar Cédulas de Otras Partidas" /> ');
        $tabla = new stdClass();
        $tabla->id = 'tbl-Conceptos';
        $tabla->titulo = 'Conceptos Ingresados';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaArticulos';
        //Encabezado tabla
        $tr = [];
        $th = new stdClass();
        $th->texto = $texto;
        $th->width = '5px';
        array_push($tr, $th); //AGREGA UN th al array  tr  que viene siendo la primera fila
        $th = new stdClass();
        $th->texto = 'Programatico';
        $th->width = '5px';
        $th->align = 'center';
        array_push($tr, $th); /// y th son las celdas de cabecera
        $th = new stdClass();
        $th->texto = 'Clave de Financiamiento';
        $th->width = '5px';
        $th->align = 'center';
        array_push($tr, $th); /// y th son las celdas de cabecera
        $th = new stdClass();
        $th->texto = 'Concepto';
        $th->align = 'center';
        array_push($tr, $th); /// y th son las celdas de cabecera
        $th = new stdClass();
        $th->texto = 'Unidad de medida';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Precio Unitario';
        $th->align = 'right';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Cantidad';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Importe anual';
        $th->align = 'right';
        array_push($tr, $th);
        $trbody = [];

        // cuerpo de la tabla en un arreglo de tr y tds
        $td = new stdClass();
        $td->tipo = "boton";
        $td->icono = $icono;
        $td->attr = new stdClass();
        $td->attr->ced_id = 'ced_id';
        $td->attr->epg_id = "epg_id";
        $td->attr->ecf_id = "ecf_id";
        $td->attr->pg_id = "pg_id";
        $td->attr->pe_id = "pe_id";
        $td->class = "addModCedula";
        $td->name = 'ced_id';
        $td->id = 'ced_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'programatico';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cf';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'otp_archivo';
        $td->align = "left";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'umv_nombre';
        $td->align = "center";
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'otp_costo';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cantidad';
        $td->align = 'center';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";

            $("#techos").show();
            limpiaClasAdmin();
            $("#nuevo").click(function(){ comunica("' . $this->pantalla . '",{metodo:"addModCedula"},true); });

            // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });
            setTimeout( function() { $( "#filtroTabla" ).focus() }, 300 );

            $("#cmC").click(function(){
                comunica("poa/ControladorCargaMasivaOtrasPartidas.php");
            });

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
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 9))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))

                $("body").append(form);
                form.submit();
                form.remove();
            });

            $(".cargaMasiva").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/doc/rep_Reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "encabLin1").attr("value", "SECRETARIA DE FINANZAS.   EJERCICIO ' . substr($_SESSION[_S]["ejercicio"], 0, 4) . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "encabLin2").attr("value", "Catálogos de información válida para cargas masivas" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "xls" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "sql").attr("value", $(this).attr("id") ));
                $("body").append(form);
                form.submit();
            });

            // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });
        ');
        $this->llenaTablaArticulos();
    } //fin main

    private function llenaTablaArticulos()
    { //llenar Tabla de Articulos

        $params = [
            ':gpo' => (string) (getSession('clasAdm-gpo') ?? ''),
            ':ur'  => (string) (getSession('clasAdm-ur') ?? ''),
        ];

        $filtroUE = '';
        if ($this->nvj == 1) {
            $filtroUE = ' AND ue_id = :ue ';
            $params[':ue'] = $this->ue;
        }

        //consulta de la tabla
        $this->setSql('
            SELECT a.ced_id, a.ccd_id, a.epg_id, a.ecf_id,
            CONCAT( gpo_id, ur_id, "-", ue_id, "-", prg_id, "-", spr_id, "-", py_id, "-", oa_id ) AS programatico,
            cf.ecf_clave AS cf,
            a.otp_archivo, a.pg_id, a.pe_id, u.umv_nombre,
            a.otp_costo,fte_id,sft_id,ori_id,sor_id,0 as cso_id,cfi_id,cfi_anio,
            format((a.uni_ene+ a.uni_feb+ a.uni_mar+ a.uni_abr+ a.uni_may+ a.uni_jun+ a.uni_jul+ a.uni_ago+ a.uni_sep+ a.uni_oct+ a.uni_nov+ a.uni_dic),0)  cantidad,
            (a.uni_ene+ a.uni_feb+ a.uni_mar+ a.uni_abr+ a.uni_may+ a.uni_jun+ a.uni_jul+ a.uni_ago+ a.uni_sep+ a.uni_oct+ a.uni_nov+ a.uni_dic) * a.otp_costo  total
            FROM poa_OtrasPartidas a
            INNER JOIN enc_Programatico ep USING( gpo_id,ur_id,epg_id )
            INNER JOIN enc_ClaveFinanciamiento cf using(ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING( fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            LEFT JOIN cat_UnidadMedidaVariable u ON u.umv_id = a.umv_id
            WHERE gpo_id = :gpo AND ur_id = :ur ' . $filtroUE . '
            GROUP BY a.ccd_id, a.ced_id, a.gpo_id, a.ur_id, a.ue_id;');

        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Conceptos';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        if ($this->wrLlenaTabla($nav) > ((isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50)) {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").show();
                $(".panel-footer").show();
            ');
        } else {
            $this->mixScript('
                // Quitamos la info de una paginación que no usaremos en ésta pantalla
                $(".panel-heading.header").hide();
                $(".panel-footer").hide();
            ');
        }

        $this->mixScript('
            $(".addModCedula").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addModCedula",
                    ced_id:$(this).attr("ced_id"),
                    },true
                );
                return false;
            });

            $(document).ready(function() {
                $("#tabla-tbl-Conceptos").filterTable({ // apply filterTable to all tables on this page
                    inputSelector: "#filtroTabla" // use the existing input instead of creating a new one
                });
            });
        ');
    }

    public function addModCedula()
    {
        $clasAdm = new stdClass();
        $clasAdm->filtro = 'poa';

        // TODO PENDIENTE: el catálogo cat_UnidadMedidaVariable aún no tiene un registro con umv_nombre='OTRO'.
        //   Mientras no se defina, $idOtro queda en 0 → el handler JS de "Especifique unidad" nunca dispara
        //   y el input adicional permanece oculto (degradación segura).
        //   Cuando el equipo apruebe el alta, basta con INSERTar la fila (o ajustar el WHERE si optan por
        //   otro nombre canónico como 'OTRO (ESPECIFICAR)') y el flujo se habilita automáticamente.
        $catUm = new AccesoDatos();
        $catUm->setSql('SELECT umv_id FROM cat_UnidadMedidaVariable WHERE umv_nombre = :nombre LIMIT 1');
        $catUm->conectar();
        $catUm->consultar([':nombre' => 'OTRO']);
        $rowOtro = $catUm->sacar();
        $catUm->desconectar();
        $idOtro = (int) ($rowOtro['umv_id'] ?? 0);

        // Si pasaron el número de cédula, es un cambio.. si no, le ponemos 0 como número
        $cedula = $this->prm->ced_id ?? 0;
        // Si es una nueva cédula, podrán elegir el encuadre programático y la clave de financiamiento
        if ($cedula == 0) {
            
            $this->mixHtml(CatalogoProgramaticoFinanciamiento::ponF1());

            $otp = new OtrasPartidas();
            $abc = 'a';
            $this->ponBreadcrumbs('Alta de Cedula');
            // Si no, sólo podrán ver lo que está registrado en la base de datos
        } else {
            $clasAdm->lectura = true;
            $this->prm->ccd_id = 9;
            $otp = new OtrasPartidas($this->prm);
            $abc = 'c';
            $this->ponBreadcrumbs('Modificación de Cedula');
        }

        $this->mixScript('
            $("#btnregresarindex").click(function(){
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            $(".breadcrumbs > li.current").remove();
                comunica("' . $this->pantalla . '",{metodo:"main"});
                return false;
            });
        ');

        $this->agregaTools('<button class="btn btn-outline-secondary me-1" value="Regresar" id="btnregresarindex"><span class="fa fa-circle-left me-2"></span>Regresar</button>');

        $encProg = new stdClass();
        $encProg->id = 'encProg';
        $encProg->placeholder = "Encuadre (F1/F2)";
        $encProg->required = true;
        $encProg->maxlength = 20;
        $encProg->class = 'F1Catalogo';
        $nomProg = new stdClass();
        $nomProg->id = 'nomProg';
        $nomProg->placeholder = "Nombre Obra/Actividad";
        $nomProg->lectura = true;
        $encCveFin = new stdClass();
        $encCveFin->id = 'encCveFin';
        $encCveFin->placeholder = "Clave (F1/F2)";
        $encCveFin->required = true;
        $encCveFin->maxlength = 12;
        $encCveFin->class = 'F1Catalogo';
        $nomCveFin = new stdClass();
        $nomCveFin->id = 'nomCveFin';
        $nomCveFin->placeholder = "Nombre de la Clave";
        $nomCveFin->lectura = true;
        $otp_archivo = new stdClass();
        $otp_archivo->id = 'otp_archivo';
        $otp_archivo->placeholder = "Concepto";
        $otp_archivo->required = true;
        $otp_archivo->label = 'Concepto';
        $otp_archivo->enDiv = true;
        $otp_archivo->maxlength = 310;
        $otp_archivo->value = $otp->getOtp_archivo();
        //Combo alimentado del catálogo cat_UnidadMedidaVariable
        $umv_id = new stdClass();
        $umv_id->sql = 'SELECT umv_id, umv_nombre FROM cat_UnidadMedidaVariable WHERE umv_muestraCedula = 1 ORDER BY umv_id ASC';
        $umv_id->id = 'umv_id';
        $umv_id->name = 'umv_id';
        $umv_id->label = 'Unidad de Medida';
        $umv_id->mensaje = 'Selecciona una unidad de medida';
        $umv_id->required = true;
        $umv_id->enDiv = true;
        $umv_id->search = true;
        $umv_id->selecciona = $otp->getUmv_id();

        //Input solo visible cuando seleccionan OTRO en el combo de unidad de medida
        //TODO PENDIENTE: depende del alta de 'OTRO' en cat_UnidadMedidaVariable (ver bloque anterior).
        $otp_unidadMedida = new stdClass();
        $otp_unidadMedida->id = 'otp_unidadMedida';
        $otp_unidadMedida->name = 'otp_unidadMedida';
        $otp_unidadMedida->placeholder = 'Especifique unidad de medida';
        $otp_unidadMedida->label = 'Especifique unidad';
        $otp_unidadMedida->enDiv = true;
        $otp_unidadMedida->maxlength = 50;
        $otp_unidadMedida->value = $otp->getOtp_unidadMedida();
        $otp_costo = new stdClass();
        $otp_costo->id = 'otp_costo';
        $otp_costo->placeholder = "Precio Unitario";
        $otp_costo->required = true;
        $otp_costo->label = "Precio Unitario"; // Select tct_costoMensual
        $otp_costo->formato = 'moneda';
        $otp_costo->class = 'unitario';
        $otp_costo->maxLength = 11;
        $otp_costo->enDiv = true;
        $otp_costo->value = $otp->getotp_costo();
        $uni = $otp->getUnidades()->getArrayMeses();
        $mon = $otp->getMontos()->getArrayMeses();
        $ced_id = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? 'ced_id:' . $this->prm->ced_id . ',' : '';
        $ced = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? ',ced_id:' . $this->prm->ced_id . '' : '';
        $cedula = (isset($this->prm->ced_id) && $this->prm->ced_id != '') ? $this->prm->ced_id : '';
        $objGto = new stdClass();
        $objGto->pg_id = $otp->getPg_id();
        $objGto->pe_id = $otp->getPe_id();
        $objGto->ccd_id = 9;
        $objGto->filtro = "costeo";
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
        if ($this->escritura) {
            $btnguardar = new stdClass();
            $btnguardar->id = 'btnguardar';
            $btnguardar->value = 'Guardar';
            $btnguardar->type = 'button';
            $btnguardar->class = 'me-1';
            $btnguardar->color = 'verde';
            $btnguardar->icon = "solid fa-floppy-disk me-2";
            $btnguardar->click = (($abc == 'a') ? '
                // Los encuadres no pueden quedar vacíos
                if ( !$("#encProg").is("[enc]") ) {
                    notify( "Debe seleccionar un Encuadre Programático", "error");
                    return false;
                }
                if ($("#nomProg").val()=="") {
                    notify("Debe seleccionar un Encuadre Programático","error");
                    return false;
                }
                if ( !$("#encCveFin").is("[enc]") ) {
                    notify( "Debe seleccionar una Clave de Financiamiento", "error");
                    return false;
                }
                if ($("#nomCveFin").val()=="") {
                    notify("Debe seleccionar una Clave de Financiamiento","error");
                    return false;
                }
                if ( $("#pg_id").val().trim() == "" ) {
                    notify( "Debe de seleccionar una Partida Genérica", "warning");
                    return false;
                }
                if ( $("#pe_id").val().trim() == "" ) {
                    notify( "Debe de seleccionar una Partida Específica", "warning");
                    return false;
                }

                // checamos que hayan costeado al menos un mes:
                if ( ( $(".totu.entero").val().trim() == "0" || $(".totu.entero").val().trim() == "" ) ) {
                    notify( "Debes costear al menos un mes", "warning");
                    return false;
                }
                // El precio unitario no puede ser cero
                if ( $("#otp_costo").val().trim() == "$ 0.00" ){
                    notify("El precio unitario debe contener un importe", "warning");
                    return false;
                }
                if (!validarPresupuesto()) {
                    e.preventDefault(); // Detiene el envío
                }
            ' : '') . '
                var unidades = new Array();
                $(".mesu").each(function(index,val){
                    unidades[index]= unformatNumber($(this).val());
                });
                var montos = new Array();
                $(".mesm").each(function(index,val){
                    montos[index]= unformatNumber($(this).val());
                });
                var umvIdSel    = $("#umv_id").val();
                var esOtro      = parseInt(umvIdSel, 10) === ' . $idOtro . ';
                var otroTexto   = esOtro ? $.trim($("#otp_unidadMedida").val()) : "";
                var objArr = {
                    metodo:"guardar",
                    abc: "' . $abc . '",
                    ' . $ced_id . '
                    pg_id:$("#pg_id").val(),
                    pe_id:$("#pe_id").val(),
                    gbt_id:$("#btr_id").find("option:selected").attr("gbt_id"),
                    btr_id:$("#btr_id").find("option:selected").attr("btr_id"),
                    otp_archivo:$("#otp_archivo").val(),
                    umv_id:umvIdSel,
                    otp_unidadMedida: esOtro ? otroTexto : "",
                    otp_costo:unformatNumber($("#otp_costo").val()),
                    unidades:unidades,
                    montos:montos
                }
                if (!validarPresupuesto()) {
                    e.preventDefault(); // Detiene el envío
                }
                if ($.trim($("#otp_archivo").val())=="" || $.trim(umvIdSel)=="" || $.trim($("#otp_costo").val())=="" || (esOtro && otroTexto=="")) {
                    document.getElementById("otp_archivo").style.border="#F00 1px solid";
                    document.getElementById("umv_id").style.border="#F00 1px solid";
                    document.getElementById("otp_costo").style.border="#F00 1px solid";
                    if (esOtro) {
                        document.getElementById("otp_unidadMedida").style.border="#F00 1px solid";
                    }
                    notify("Advertencia \n La información del Registro es obligatoria", "warning");
                } else {
                    comunica("' . $this->pantalla . '",objArr,true);
                    document.getElementById("btnguardar").disabled="disabled";
                }
            ';

            $this->agregaTools($this->ponBoton($btnguardar));

            if (isset($this->prm->ced_id)) {
                $btneliminar = new stdClass();
                $btneliminar->id = 'btneliminar';
                $btneliminar->color = 'rojo';
                $btneliminar->value = 'Eliminar';
                $btneliminar->type = 'button';
                $btneliminar->class = "me-1";
                $btneliminar->icon = 'solid fa-trash me-2';
                $btneliminar->click = '
                    alertify.confirm("ELIMINAR CEDULA", "¿Confirma que desea eliminar el registro?",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"guardar",
                                abc:"b"
                                ' . $ced . '
                            });
                        },
                        function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
                    return false;
                ';

                $this->agregaTools($this->ponBoton($btneliminar));

                $this->agregaTools('<a id="imprimirTodo" epg_id="' . $otp->getEpg_id() . '" ecf_id="' . $otp->getEcf_id() . '" ced_id="' . $otp->getCed_id() . '" target="_blank" class="btn btn-test" title="Imprimir Cédula de Otras Partidas" style="padding: 0" ><button type="button" class="btn btn-outline-danger me-1" ><i class="fa fa-file-pdf me-2"></i>PDF</button></a>');
            } else {
                $encCveFin->keydown = '
                    var key = event.charCode ? event.charCode : event.keyCode ? event.keyCode : 0;
                    /* F1 llama a la pantalla alternar para escoger encuadres */
                    if ( key == 112 ) {
                        event.preventDefault();
                        dlgC.dialog("open");
                    } else if( key == 113 ) {
                        /* F2 llama a la pantalla modal para escoger clave de financiamiento */
                        // Solo seleccionamos éste encuadre cuando YA exista el programatico
                        if ( $("#encProg").val() != "" ) {
                            event.preventDefault();
                            comunica( "srv/ClasificacionFinanciamiento.php", {
                                metodoCveFin: "muestraCveFin"
                            });
                        } else {
                            notify("Debes seleccionar primero el Encuadre Programático", "warning");
                            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
                        }
                    } else if ( ( key >= 48 && key <= 57) || ( key >= 65 && key <= 90) || ( key >= 97 && key <= 122 ) ) {
                        notify("F1 para mostrar catálogo y  doble click para seleccionar, o F2 para combos", "warning");
                        e.preventDefault();
                        return false;
                    }
                ';
            }
            $otp_costo->blur = '
                if(unformatNumber($(this).val())<0){
                    $(this).val("0");
                    $(this).blur();
                }
                $("#mes").calendario({
                    unitario : parseFloat( unformatNumber( $("#otp_costo").val() ).toFixed(2) )
                });
            ';
        }
        
        if (isset($this->prm->ced_id)) {
            $_SESSION[_S]['clasAdm-ue'] = $otp->getUe_id();
            $nomProg->value = ClasificacionAdministrativa::PonEnVariablesEpg($otp->getEpg_id());
            $encProg->value = $otp->getGpo_id() . $otp->getUr_id() . "." . $otp->getUe_id() . "." . $_SESSION[_S]['clasAdm-prg'] . "." . $_SESSION[_S]['clasAdm-spr'] . "." . $_SESSION[_S]['clasAdm-py'] . "." . $_SESSION[_S]['clasAdm-oa'];
            ClasificacionFinanciamiento::PonEnVariablesEcf($otp->getEcf_id());
            $cso = isset($_SESSION[_S]['cvefin']->cso) ? $_SESSION[_S]['cvefin']->cso : '';
            $encCveFin->value = $_SESSION[_S]['cvefin']->ter . "-" . $_SESSION[_S]['cvefin']->fte . $_SESSION[_S]['cvefin']->sft . $_SESSION[_S]['cvefin']->ori . $_SESSION[_S]['cvefin']->sor . $cso . "." . $_SESSION[_S]['cvefin']->cfi . $_SESSION[_S]['cvefin']->anio;
            $nomCveFin->value = $_SESSION[_S]['cvefin']->nombre;
            $clasAdm->epg_id = $otp->getEpg_id();
            $encProg->lectura = true;
            $encCveFin->lectura = true;
            $objGto->lectura = true;
            $clasAdm->lectura = true;
        }

        $this->mixHtml('
            <form role="form"><div class="bs-example">
                <fieldset class="col-md-12 mb-4">
                    ' . ObjetoGasto::ponObjetoGasto($objGto) . '
                </fieldset>

                <div class="row">
                    <fieldset class="col-md-12">
                        <legend>Descripci&oacute;n del Insumo
                            <span style="float: right;">
                                <input width=50
                                    id="saldoDisponible"
                                    style="display: inline; width:300px; height: 30px; text-align:right;"
                                    class="form-control poaSaldoDisponible"
                                    tabindex="-1"
                                    placeholder="Techo Presupuestal Disponible" />
                            </span>
                        </legend>
                        <div class="col-md-12 mb-4">' . $this->ponInput($otp_archivo) . '</div>
                        <div class="row">
                            <fieldset class="col-md-3">' . $this->ponCombo($umv_id) . '</fieldset>
                            <fieldset class="col-md-3" id="divOtroUnidad" style="display:none;">' . $this->ponInput($otp_unidadMedida) . '</fieldset>
                            <fieldset class="col-md-6 mb-4">' . $this->ponInput($otp_costo) . '</fieldset>
                        </div>
                    </fieldset>
                </div>
                <fieldset class="col-md-12">
                    <legend>Calendarizaci&oacute;n</legend>
                    <Calendario id="mes"></Calendario><!-- Elemento calendario que se pintara con Otraspartidas.php -->
                </fieldset></div>
            </form>
            <div id="contenedorCFModal"></div>
        ');
        $this->mixScript('
            $("#imprimirTodo").click(function(){
                epg_id= $(this).attr("epg_id");
                ecf_id= $(this).attr("ecf_id");
                ced_id= $(this).attr("ced_id");
                firma= $(this).attr("firma");

                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_Cedula.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "epg_id").attr("value", epg_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", ecf_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ced_id").attr("value", ced_id))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 9))
                    .append($("<input>").attr("type", "hidden").attr("name", "firma").attr("value", firma))
                    

                $("body").append(form);
                form.submit();
                form.remove();
            });

            ' . ClasificacionAdministrativa::muestraClasAdmin($clasAdm) . ((!$this->lectura) ? '
            $(".mes").attr("maxlength","8");' : '') . '
            $("#mes").calendario({
                unidades:' . (json_encode($uni)) . ',
                montos:' . (json_encode($mon)) . ',
                unitario:parseFloat(unformatNumber($("#otp_costo").val()).toFixed(2))
            });
            $("#otp_archivo").focus(function(){document.getElementById("otp_archivo").style.border="#CCC 1px solid";});
            $("#umv_id").focus(function(){document.getElementById("umv_id").style.border="#CCC 1px solid";});
            $("#otp_costo").focus(function(){document.getElementById("otp_costo").style.border="#CCC 1px solid";});
            $("#otp_unidadMedida").focus(function(){document.getElementById("otp_unidadMedida").style.border="#CCC 1px solid";});

            //Mostrar/ocultar input "Especifique unidad" según selección del combo
            //NOTA: si umvIdOtro queda en 0 (porque OTRO aún no existe en cat_UnidadMedidaVariable)
            //el handler nunca detecta OTRO y el input se mantiene oculto. Es degradación segura.
            var umvIdOtro = ' . $idOtro . ';
            $("#umv_id").on("change", function () {
                var esOtro = parseInt($(this).val(), 10) === umvIdOtro;
                $("#divOtroUnidad").toggle(esOtro);
                if (!esOtro) {
                    $("#otp_unidadMedida").val("");
                }
            }).trigger("change");

            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                $(".breadcrumbs > li.current").remove();
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '",{metodo:"main"});
                e.stopPropagation();
            });

            $("#saldoDisponible").keydown(false);

            $("#encProg, #encCveFin,button[data-id=pg_id], button[data-id=pe_id]").blur(function() {
                if($("#encProg").val() != "" && $("#encCveFin").val() != "" && $("#pg_id").val() != "" && $("#pe_id").val() != ""){
                    comunica("lib/ControladorFunciones.php",{
                        metodo:"muestraDisponible",
                        ecf: $("#encCveFin").attr("enc"),
                        pg: $("#pg_id").val(),
                        pe: $("#pe_id").val()
                    });
                }
            });

            setTimeout( function() { $( "#encProg" ).focus() }, 100 );

        '); // fin mixScript

        if (isset($this->prm->ced_id)) {
            $this->mixScript('$("#encProg").blur();');
        }
    } //fin form nueva

    public function abcOtrasPartidas()
    { // Funcion al estilo abc
        $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
        $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
        $this->prm->ccd_id = 9;
        $accion = '';

        switch ($this->prm->abc) {
            case 'a': // guardar
                $this->prm->gpo_id = (string) (getSession('clasAdm-gpo') ?? '');
                $this->prm->ur_id = (string) (getSession('clasAdm-ur') ?? '');
                $this->prm->ue_id = recuperarUe(ClasificacionAdministrativa::getEpg());
                $this->prm->bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA A.3.8';
                $accion = 'agregada exitosamente';
                break;
            case 'b': // Eliminar
                $this->prm->bit_observacion = 'REGISTRO ELIMINADO DESDE PANTALLA A.3.8';
                $accion = 'eliminada exitosamente';
                break;
            case 'c':
                $this->prm->bit_observacion = 'REGISTRO ACTUALIZADO DESDE PANTALLA A.3.8';
                $accion = 'actualizada exitosamente';
                break;
        }

        $ced_id = OtrasPartidas::abc($this->prm);
        if ($this->prm->abc != 'a') {
            $ced_id = $this->prm->ced_id;
        }

        $this->mixScript(' notify("Cedula ' . $ced_id . ' de Otras Partidas ' . $accion . '"); ');
        $this->mixScript('
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            $(".breadcrumbs > li.current").remove();
            comunica("' . $this->pantalla . '",{metodo:"main"});
        ');
    } //end of guardar function

}

if (tienePermiso('A.3.8.' . TAREA_LECTURA)) {
    new ControladorOtrasPartidas();
} else {
    WebResponse::sinPermisos();
}
