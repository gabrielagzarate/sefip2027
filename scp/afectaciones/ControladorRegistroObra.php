<?php

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/Entidad.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../../ent/InversionPublica.php';
require_once dirname(__FILE__) . '/../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';

class ControladorRegistroObra extends WebResponse
{
    protected $ur;
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/afectaciones/ControladorRegistroObra.php';
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['gpo_id']);
        $this->ur->setUr_id($_SESSION[_S]['ur_id']);
        $this->ur->select();
        switch ($_SESSION[_S]['nvj_id']) {
            case 1: case 2: case 3:case 4:case 5:case 6:case 7:case 8:case 9:case 10: case 11: case 12:
                switch ($this->metodo) {
                    case 'Finiquitar':
                        $this->Finiquitar();
                        break;
                    case 'llenaTablaContrato':
                        $this->llenaTablaContrato();
                        break;
                    case 'addContrato':
                        $this->addContrato();
                        break;
                    case 'Proveedor':
                        $this->Proveedor();
                        break;
                    case 'ValidaCuenta':
                        $this->ValidaCuenta();
                        break;
                    case 'abcContrato':
                        $this->abcContrato();
                        break;
                    case 'CargaMunicipio':
                        $this->CargaMunicipio();
                        break;
                    case 'ObraActividad':
                        $this->ObraActividad();
                        break;
                    case 'llenaObraActividad':
                        $this->llenaObraActividad();
                        break;
                    case 'llenaObraActividadAgregadas':
                        $this->llenaObraActividadAgregadas();
                        break;
                    case 'ObraActividadAgregadas':
                        $this->ObraActividadAgregadas();
                        break;
                    case 'GuardarObraActividad':
                        $this->GuardarObraActividad();
                        break;
                    case 'SeparaObraActividad':
                        $this->SeparaObraActividad();
                        break;
                    case 'AgregarOficio':
                        $this->AgregarOficio();
                        break;
                    case 'AutorizarObra':
                        $this->AutorizarObra();
                        break;
                    case 'DesAutorizarObra':
                        $this->DesAutorizarObra();
                        break;
                    case 'ValidarContrato':
                        $this->ValidarContrato();
                        break;
                    case 'ClavesFinanciamiento':
                        $this->ClavesFinanciamiento();
                        break;
                    case 'FiniquitarContrato':
                        $this->FiniquitarContrato();
                        break;
                    case 'Contratos':
                        $this->Contratos();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        /*
            La pantalla de contrato se ha dividido por ejercicios, ahora podemos ver 2015,2016,2017 y 2018, para que yo pueda utilizar un contrato en una CLCL´S
            necesito agregar una obra al contrato solo puedo agregar una obra al contrato que ya esta autorizado, fala definir los estados de los contrato ya
            que hasta el momento no hay contratos finiquitados solo hay contratos autorizados, cuando se verifique este detalle hay que corroborar las reglas con
            andrea ya que si esta programado

            A un contrato solo se pueden agregar obras una vez que esta autorizado y en estado 1 solo es generado ahi se puede editar los contratos.

            EL REGISTRO DE CONTRATO FUNCIONA PARA TODAS LAS CLCL´S DE INVERSION PUBLICA POR INVERSIÓN PUBLICA SE CONOCE COMO
            ( F ) Contrato
            ( G ) Administraci+on directa
            ( H ) Anticipo de Obra solo son contables
        */

        $contratos = [
            ['id' => 'dieciseis', 'anio' => '2016', 'img' => 'img/contrato2016.png'],
            ['id' => 'diecisiete', 'anio' => '2017', 'img' => 'img/contratos2017.png'],
            ['id' => 'dieciocho', 'anio' => '2018', 'img' => 'img/contratos2018.png'],
            ['id' => 'diecinueve', 'anio' => '2019', 'img' => 'img/contratos2018.png'],
            ['id' => 'veinte', 'anio' => '2020', 'img' => 'img/contratos2018.png'],
            ['id' => 'veintiuno', 'anio' => '2021', 'img' => 'img/contratos2018.png'],
            ['id' => 'veintidos', 'anio' => '2022', 'img' => 'img/contratos2018.png'],
            ['id' => 'veintitres', 'anio' => '2023', 'img' => 'img/contratos2018.png'],
            ['id' => 'veinticuatro', 'anio' => '2024', 'img' => 'img/contratos2018.png'],
            ['id' => 'veinticinco', 'anio' => '2025', 'img' => 'img/contratos2018.png'],
            ['id' => 'veintiseis', 'anio' => '2026', 'img' => 'img/contratos2018.png'],
        ];

        $cards = [];
        foreach ($contratos as $contrato) {
            $c = new stdClass();
            $c->id = $contrato['id'];
            $c->titulo = 'Contratos ' . $contrato['anio'];
            $c->img = $contrato['img'];
            $c->label = $contrato['anio'];
            $c->title = 'contrato' . $contrato['anio'];
            $cards[] = $c;
        }

        $this->mixHtml($this->ponCards($cards));
        $this->mixScript('
            $("#toolsPantalla").html("");
            $("#quince").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "1"
                });
                return false;
            });

            $("#dieciseis").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "2"
                });
                return false;
            });

            $("#diecisiete").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "3"
                });
                return false;
            });

            $("#dieciocho").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "4"
                });
                return false;
            });

            $("#diecinueve").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "5"
                });
                return false;
            });

            $("#veinte").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "6"
                });
                return false;
            });

            $("#veintiuno").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "7"
                });
                return false;
            });

            $("#veintidos").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "8"
                });
                return false;
            });

            $("#veintitres").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "9"
                });
                return false;
            });

            $("#veinticuatro").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "10"
                });
                return false;
            });

            $("#veinticinco").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "11"
                });
                return false;
            });

            $("#veintiseis").click(function(){
                comunica("scp/afectaciones/ControladorRegistroObra.php",{
                    metodo: "Contratos",
                    tarea: "12"
                });
                return false;
            });
        ');
    }

    /*************************************FUNCION PRINCIPAL - CONTRATOS **********************************/
    public function Contratos()
    {
        $this->prm->gpo_id = $_SESSION[_S]['gpo_id'];
        $this->prm->ur_id = $_SESSION[_S]['ur_id'];
        $this->mixScript('$("' . (($_SESSION[_S]['nvj_id'] == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();');
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $this->prm->vur_id = (isset($this->prm->vur_id)) ? $this->prm->vur_id : '';
        $color = '';
        $ejercicio = '0';
        $paso = '';
        $banderaPagina = '';

        switch ($this->prm->tarea) {
            case 1: $ejercicio = '2015';
                $color = ' <font color="#B40404">';
                $banderaPagina = '1';
                break;
            case 2: $ejercicio = '2016';
                $color = ' <font color="#FFBF00">';
                $banderaPagina = '2';
                break;
            case 3: $ejercicio = '2017';
                $color = ' <font color="#31B404">';
                $banderaPagina = '3';
                break;
            case 4: $ejercicio = '2018';
                $color = ' <font color="#73c6b6">';
                $banderaPagina = '4';
                break;
            case 5: $ejercicio = '2019';
                $color = ' <font color="#7dcea0">';
                $banderaPagina = '5';
                break;
            case 6: $ejercicio = '2020';
                $color = ' <font color="#82e0aa">';
                $banderaPagina = '6';
                break;
            case 7: $ejercicio = '2021';
                $color = ' <font color="#f7dc6f">';
                $banderaPagina = '7';
                break;
            case 8: $ejercicio = '2022';
                $color = ' <font color="#f8c471">';
                $banderaPagina = '8';
                break;
            case 9: $ejercicio = '2023';
                $color = ' <font color="#e59866">';
                $banderaPagina = '9';
                break;
            case 10: $ejercicio = '2024';
                $color = '<font color="#dc7633">';
                $banderaPagina = '10';
                break;
            case 11: $ejercicio = '2025';
                $color = '<font color="#eb984e">';
                $banderaPagina = '11';
                break;
            case 12: $ejercicio = '2026';
                $color = '<font color="#eb984e">';
                $banderaPagina = '12';
                break;
        }

        /* @Jordy la variable de sesion de banderaPagina sirve para el controlador Garantinas,en especial para el listado de pdfs*/
        $_SESSION[_S]['banderaPagina'] = $banderaPagina;
        // Existe un identificador único de la Unidad Responsable que identifica a la Ur en los diferentes ejercicios
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $parametrosConsulta = [$this->prm->gpo_id, (int) $this->prm->ur_id, $ejercicio];
        $conexion->setSql(' 
            SELECT uur_id 
            FROM  ' . BD_SINPRES . '.vin_UnidadResponsable 
            WHERE gpo_id = ? AND ur_id = ?
                AND vur_ejercicio = ?
        ');
        $conexion->consultar($parametrosConsulta);
        $datos = $conexion->extraer();
        $conexion->desconectar();

        if ($conexion->getNumeroRegistros() < 1 || is_null($datos) || empty($datos['uur_id']) || trim($datos['uur_id']) == '') {
            $this->mixScript(' notify("No Hay datos para mostrar");');
            $this->mixScript('
                $(".habilitar").attr("disabled","disabled"); 
            ');
        } else {
            $cx = new AccesoDatos();
            $prmCon = [$datos['uur_id'], $ejercicio];
            $cx->conectar();
            $cx->setSql(' 
                SELECT uur_id,vur_id 
                FROM  ' . BD_SINPRES . '.vin_UnidadResponsable 
                WHERE uur_id = ? AND vur_ejercicio = ?
            ');
            $cx->consultar($prmCon);
            $cx->desconectar();
            $datosx = $cx->extraer();
            $this->prm->uur_id = $datosx['uur_id'];
            $this->prm->vur_id = $datosx['vur_id'];

            $radios = '';

            $script = $this->getScript();

            $contratista = new stdClass();
            $contratista->id = 'contratista';
            $contratista->name = 'contratista';
            $contratista->label = 'Nombre del Contratista';
            $contratista->class = 'limpiar';
            $contratista->placeholder = 'Contratista';
            $contratista->enDiv = true;

            $concepto = new stdClass();
            $concepto->id = 'concepto';
            $concepto->name = 'concepto';
            $concepto->label = 'Objeto  del Contrato';
            $concepto->class = 'limpiar';
            $concepto->placeholder = 'Objeto';
            $concepto->enDiv = true;

            $importe = new stdClass();
            $importe->id = 'importe';
            $importe->name = 'importe';
            $importe->label = 'Importe';
            $importe->class = 'limpiar';
            $importe->placeholder = 'Importe';
            $importe->enDiv = true;

            $rfc = new stdClass();
            $rfc->id = 'rfc';
            $rfc->name = 'rfc';
            $rfc->label = 'RFC';
            $rfc->class = 'limpiar';
            $rfc->placeholder = 'RFC';
            $rfc->maxlength = 13;
            $rfc->enDiv = true;

            $idcontrato = new stdClass();
            $idcontrato->id = 'idcontrato';
            $idcontrato->name = 'idcontrato';
            $idcontrato->label = 'Id del Contrato';
            $idcontrato->class = 'limpiar';
            $idcontrato->placeholder = 'id Contrato';
            $idcontrato->enDiv = true;

            $contrato = new stdClass();
            $contrato->id = 'contrato';
            $contrato->name = 'contrato';
            $contrato->label = 'N° Contrato';
            $contrato->class = 'limpiar';
            $contrato->placeholder = 'N°';
            $contrato->enDiv = true;

            $clean = new stdClass();
            $clean->class = 'btn btn-outline-warning me-1';
            $clean->value = 'Limpiar';
            $clean->id = 'clean';
            $clean->icon = 'solid fa-trash me-2';

            $btnConsulta = new stdClass();
            $btnConsulta->id = 'btnConsulta';
            $btnConsulta->value = 'Buscar';
            $btnConsulta->text = 'Buscar';
            $btnConsulta->class = 'btn btn-outline-secondary me-1';
            $btnConsulta->icon = 'search me-2';

            $radios .= '
                <h3 style align="center"><b>' . $color . ' CONTRATOS  ' . $ejercicio . '</b></font></h3><br>
                <div class="row col-md-12 col-xs-12 mb-4">
                    <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($contratista) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($contrato) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($importe) . '
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($rfc) . '
                    </fieldset>
                </div>
                <div class="row col-md-12 col-xs-12 mb-4">
                    <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($idcontrato) . '
                    </fieldset>
                    <fieldset class="col-md-8 col-xs-8">
                    ' . $this->ponInput($concepto) . '
                    </fieldset>
                    <fieldset class="d-flex justify-content-end align-items-end col-md-2">
                    ' . $this->ponBoton($btnConsulta) . ' ' . $this->ponBoton($clean) . '
                    </fieldset>
                </div>
                <br>
            ';

            /*** Listado de afectaciones segun tipo A,B,C... etc***/
            $tabla = new stdClass();
            $tabla->tools = $radios;

            // Los contratos de 2015, 2016, 2017, 2018 y 2019- Ya no seran seleccionables
            if ($this->prm->tarea != 12) {
                $tabla->multiseleccionable = false;
            } else {
                $tabla->multiseleccionable = true;
            }

            $tabla->prmSeleccion = ['ipu_id','ipu_numeroContrato','gpo_id','ur_id','ipu_importeTotal','rfc_id'];
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = 'eip_id';
            $tabla->casoSeleccionable->valor = '1';
            $tabla->casoSeleccionable->ands = [];
            $and = new stdClass();
            $and->campo = 'ipu_importeTotal';
            $and->operador = '!=' ;
            $and->valor = '0' ;
            $and->valorTipo = 'numero' ;
            $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
            array_push($tabla->casoSeleccionable->ands, $and);

            $tabla->casosEstado = new stdClass();
            $tabla->casosEstado->campo = 'eip_id';
            $tabla->casosEstado->cases = [];
            $caso = new stdClass() ;
            $caso->valor = '2' ;
            $caso->color = 'rgba(8, 255, 18, 0.20)';
            array_push($tabla->casosEstado->cases, $caso);
            $caso = new stdClass() ;
            $caso->valor = '2' ;
            $caso->color = 'rgba(255, 0, 0, 0.20)';
            array_push($tabla->casosEstado->cases, $caso);

            $tabla->accion = new stdClass();
            $tabla->accion->editar = true;

            if ($this->prm->tarea == 12) {// Revisar, 2026
                // Si son contratos 2026 entonces puede editar o modificar un contrato de lo contrario solo puede finiquitar el mismo
                $tabla->accion->metodoEditar = 'addContrato';
            } else {
                $tabla->accion->metodoEditar = 'Finiquitar';
            }

            $tabla->accion->prm = ['ipu_id','ipu_numeroContrato','gpo_id','ur_id','ipu_importeTotal','rfc_id','epg_id','tarea','vur_id','eip_id'];

            $tabla->desplegable = new stdClass();
            $tabla->desplegable->metodo = 'ObraActividad';
            $tabla->desplegable->titulo = '+Obras';
            $tabla->desplegable->encabezado = 'Agregar Obras al Contrato';

            $tabla->id = 'tbl_Conceptos';
            $tabla->comunica = new stdClass();
            $tabla->comunica->pantalla = $this->pantalla;
            $tabla->comunica->concepto = '{$("#concepto").val()}';
            $tabla->comunica->importe = '{$("#importe").val()}';
            $tabla->comunica->contratista = '{$("#contratista").val()}';
            $tabla->comunica->contrato = '{$("#contrato").val()}';
            $tabla->comunica->rfc = '{$("#rfc").val()}';
            $tabla->comunica->id_ipu = '{$("#idcontrato").val()}';
            $tabla->comunica->uur_id = $this->prm->uur_id;
            $tabla->comunica->vur_id = $this->prm->vur_id;
            $tabla->comunica->tarea = $this->prm->tarea;
            $tabla->comunica->metodo = 'llenaTablaContrato';
            $tabla->numFilas = 150;
            $tabla->filasAmostrar = [20,50,100,150,300,400,500];
            $tr = [];

            $th = new stdClass();
            $th->texto = 'id Contrato';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'N° Contrato.';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Tipo';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Modalidad';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Contratista';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Fecha Contrato';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Total';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'Estado';
            array_push($tr, $th);

            $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

            $td = new stdClass();
            $td->campoTxt = 'ipu_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'ipu_numeroContrato';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'tci_nombre';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'mdc_nombre';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'nombre';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'ipu_fechaContrato';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'ipu_importeTotal';
            $td->formato = 'moneda';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'eip_nombre';
            array_push($trbody, $td);

            $tabla->encabezado = [$tr];
            $tabla->cuerpo = $trbody;
            $this->mixHtml($this->wrPonTabla($tabla) . '
                <div class="row col-md-12 col-xs-12" >
                    <fieldset id="div_contratos" class="col-md-12 col-xs-12">
                    </fieldset>
                </div>
            ');
            $this->mixScript($script);

            $btnRegresarIndex = new stdClass();
            $btnRegresarIndex->id = 'btnRegresarIndex';
            $btnRegresarIndex->class = 'regresar me-1';
            $btnRegresarIndex->color = 'gris';
            $btnRegresarIndex->text = 'Regresar';
            $btnRegresarIndex->value = 'Regresar';
            $btnRegresarIndex->icon = 'solid fa-circle-left me-2';
            $btnRegresarIndex = $this->ponBoton($btnRegresarIndex);

            $btnnuevo = $btnautorizar = '';
            if ($this->prm->tarea == 12) {
                // Solo para los contratos del año correspondiente a 2026
                $btnnuevo = new stdClass();
                $btnnuevo->id = 'btnnuevo';
                $btnnuevo->class = 'btn btn-outline-primary me-1';
                $btnnuevo->icon = 'solid fa-plus me-2';
                $btnnuevo->name = 'nuevo';
                $btnnuevo->text = 'Nuevo';
                $btnnuevo->value = 'Nuevo';
                $btnnuevo = $this->ponBoton($btnnuevo);

                $btnautorizar = new stdClass();
                $btnautorizar->id = 'btnautorizar';
                $btnautorizar->class = 'me-1';
                $btnautorizar->icon = 'solid fa-check me-2';
                $btnautorizar->color = 'verde';
                $btnautorizar->value = 'Autorizar';
                $btnautorizar->click = '
                    if ( seleccionados = $("#tbl_Conceptos").tabla("getFilaSeleccion") ){
                        comunica("' . $this->pantalla . '",{
                            metodo: "AutorizarObra",
                            tarea: "' . $this->prm->tarea . '",
                            vur_id: "' . $this->prm->vur_id . '",
                            contratos: seleccionados,
                            banderaPagina: "' . $banderaPagina . '"
                        });
                    }
                ';
                $btnautorizar = $this->ponBoton($btnautorizar);
            }

            $btnGarantias = new stdClass();
            $btnGarantias->id = 'btnGarantias';
            $btnGarantias->class = 'btn btn-outline-warning me-1';
            $btnGarantias->icon = 'solid fa-briefcase me-2';
            $btnGarantias->color = 'amarillo';
            $btnGarantias->value = 'Garantias';
            $btnGarantias->click = '
                comunica("scp/afectaciones/ControladorGarantias.php",{
                    metodo: "Contratos",
                    banderaPagina: "' . $banderaPagina . '"
                });
            ';
            $btnGarantias = $this->ponBoton($btnGarantias);

            $ListadoPDFS = new stdClass();
            $ListadoPDFS->id = 'ListadoPDFS';
            $ListadoPDFS->color = 'gris';
            $ListadoPDFS->icon = 'solid fa-list me-2';
            $ListadoPDFS->value = ' Listado PDFs';
            $ListadoPDFS->click = '
                comunica("scp/afectaciones/ControladorGarantias.php",{
                    metodo: "pdfs", 
                    banderaPagina: "' . $banderaPagina . '",
                    regPagPrin: "si"
                });
            ';
            $ListadoPDFS = $this->ponBoton($ListadoPDFS);

            $this->agregaTools(' ' . $btnRegresarIndex . ' ' . $btnnuevo . ' ' . $btnautorizar . '  ' . $btnGarantias . ' ' . $ListadoPDFS . ' ');

            $this->mixScript('
                $("#contrato").keypress(function(e){
                    var keynum = window.event ? window.event.keyCode : e.which;
                    if (keynum == 8){
                        return true;
                    } else if (keynum==95){
                        return false;
                    } else if (keynum==45){
                        return true;
                    } else if (keynum==47){
                        return true;
                    } else if (keynum==241 || keynum==209){
                        return true;
                    }
                    if (/\w/.test(String.fromCharCode(keynum))){
                        return true;
                    } else {
                        return false;
                    }
                });

                $("#rfc").keypress(function(e) {
                    var keynum = window.event ? window.event.keyCode : e.which;
                    if (keynum == 8){
                        return true;
                    } else if (keynum==95){
                        return false;
                    } else if (keynum==241 || keynum==209){
                        return true;
                    }
                    if (/\w/.test(String.fromCharCode(keynum))){
                        return true;
                    } else {
                        return false;
                    }
                });

                $("#idcontrato").keypress(function(e){
                    var keynum = window.event ? window.event.keyCode : e.which;
                    if (keynum == 8){
                        return true;
                    } else if (keynum==95){
                        return false;
                    } else if ((keynum>=65 && keynum<=90)||(keynum>=97 && keynum<=122)){
                        return false;
                    }
                    if (/\w/.test(String.fromCharCode(keynum))){
                        return true;
                    } else {
                        return false;
                    }
                });

                $("#importe").keypress(function(e){
                    var keynum = window.event ? window.event.keyCode : e.which;
                    if (keynum == 8){
                        return true;
                    } else if (keynum==95){
                        return false;
                    } else if ((keynum>=65 && keynum<=90)||(keynum>=97 && keynum<=122)){
                        return false;
                    } else if (keynum == 46){
                        var importe=$("#importe").val();
                        if (importe.indexOf(".") != -1){
                            return false;
                        } else {
                            return true;
                        }
                    }
                    if (/\w/.test(String.fromCharCode(keynum))){
                        return true;
                    } else {
                        return false;
                    }
                });

                $("#btnRegresarIndex").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "main",
                        banderaPagina: "' . $banderaPagina . '"
                    });
                    $("#btnRegresarIndex").remove();
                    $("#btnGarantias").remove();
                    $("#ListadoPDFS").remove();
                    $("#btnnuevo").remove();
                    $("#btnautorizar").remove();
                });

                $("#clean").click(function(){
                    $(".limpiar").val("");
                    $("#estado").change();
                    $("#estado").val("");
                    comunica("' . $this->pantalla . '",{
                        metodo:"Contratos",
                        tarea:"' . $banderaPagina . '"
                    });
                    return false;
                });

                $("#btnConsulta").click(function(){
                    ' . $this->wrLlamaLlenaTabla('tbl_Conceptos') . '
                });

                $("#btnnuevo").click(function(){
                    $("div[aria-describedby=cargaDatosProgFin]").html("");
                    
                    var progID = "encProg";
                    var progNom = "nomProg";
                    var cveFID = "encCveFin";
                    var cveFNom = "nomCveFin";
                    comunica("' . $this->pantalla . '",{
                        metodo:"addContrato",
                        vur_id:"' . $this->prm->vur_id . '",
                        tarea:"' . $this->prm->tarea . '",
                    });
                });
            ');
            $this->llenaTablaContrato();
        }
    }//fin Contratos

    private function llenaTablaContrato()
    {

        $contratista = isset($this->prm->contratista) ? $this->prm->contratista : '';
        $contrato = isset($this->prm->contrato) ? $this->prm->contrato : '';
        $concepto = isset($this->prm->concepto) ? $this->prm->concepto : '';
        $rfc = isset($this->prm->rfc) ? $this->prm->rfc : '';
        $this->prm->uur_id = (isset($this->prm->uur_id)) ? $this->prm->uur_id : '';
        $this->prm->vur_id = (isset($this->prm->vur_id)) ? $this->prm->vur_id : '';
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $this->prm->importe = (isset($this->prm->importe)) ? $this->prm->importe : '';
        $this->prm->id_ipu = (isset($this->prm->id_ipu)) ? $this->prm->id_ipu : '';

        $wf = '';
        $parametrosConsulta = [];
        if ($contratista != '') {
            array_push($parametrosConsulta, "%$contratista%");
            $wf = 'WHERE  concat(rfc_nombre," ",coalesce(rfc_paterno,"")," ",coalesce(rfc_materno,"")," ",coalesce(soc_nombre,"")) like ? ';
        }

        if (strlen($contrato) > 0) {
            array_push($parametrosConsulta, "%$contrato%");
            $wf .= ($wf == '') ? 'WHERE ' : ' AND ';
            $wf .= ' a.ipu_numeroContrato LIKE ? ';
        }

        if ($this->prm->importe != '') {
            $this->prm->importe = str_replace(',', '', $this->prm->importe);
            array_push($parametrosConsulta, $this->prm->importe);
            $wf .= ($wf == '') ? 'WHERE ' : ' AND ';
            $wf .= 'a.ipu_importeTotal = ? ';
        }

        if (strlen($concepto) > 0) {
            array_push($parametrosConsulta, "%$concepto%");
            $wf .= ($wf == '') ? 'WHERE ' : ' AND ';
            $wf .= 'a.ipu_objetoContrato like  ? ';
        }

        if ($rfc != '') {
            array_push($parametrosConsulta, "%$rfc%");
            $wf .= ($wf == '') ? 'WHERE ' : ' AND ';
            $wf .= 'a.rfc_id LIKE ? ';
        }

        if ($this->prm->id_ipu != '') {
            array_push($parametrosConsulta, $this->prm->id_ipu);
            $wf .= ($wf == '') ? 'WHERE ' : ' AND ';
            $wf .= 'a.ipu_id = ? ';
        }

        if ($this->prm->vur_id != '') {
            array_push($parametrosConsulta, $this->prm->vur_id);
            $wf .= ($wf == '') ? 'WHERE ' : ' AND ';
            $wf .= 'vur_id = ? ';
        }

        $this->conectar();
        switch ($this->prm->tarea) {
            case 1: case 2:  case 3: case 4: case 5: case 6: case 7:case 8:case 9:case 10:case 11:case 12:
                array_unshift($parametrosConsulta, $this->prm->uur_id);
                array_unshift($parametrosConsulta, $this->prm->tarea);
                $sql = '
                    SELECT 
                        u.gpo_id,u.ur_id,ipu_numeroContrato,ipu_id,tci_id,
                        tci_nombre,mdc_id,mdc_nombre,ipu_fechaContrato,a.rfc_id,ipu_importeTotal,
                        concat(rfc_nombre," ",coalesce(rfc_paterno,"")," ",coalesce(rfc_materno,"")," ",coalesce(soc_nombre,"")) as nombre,
                        eip_id,eip_nombre,MAX(epg_id),
                        ? as tarea, ? as uur_id,vur_id
                    FROM  ' . BD_SINPRES . '.cat_InversionPublica a
                        LEFT JOIN ' . BD_SINPRES . '.enc_InversionObras USING(ipu_id,vur_id)
                        INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable u  using(vur_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_Proveedores using(rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor using(soc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_TipoContratoInversion using(tci_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_ModalidadContratacion using(mdc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_EstadoInversion using(eip_id)
                    ' . $wf . ' 
                    GROUP BY ipu_id
                ';
                break;
        }

        $this->setSql($sql);
        $this->setParams($parametrosConsulta);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 150;
        $nav->idTabla = 'tbl_Conceptos';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros ","warning");');
        }
    }

    /************************************* PROCESO PARA AGREGAR CONTRATOS *************************************/
    public function addContrato()
    {
        $this->prm->vur_id = (isset($this->prm->vur_id)) ? $this->prm->vur_id : '';
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';

        $this->mixScript(' 
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";
            function PlazoEjecucion (f1,f2){
                var aFecha1 = f1.split("/"); 
                var aFecha2 = f2.split("/"); 
                var fFecha1 = Date.UTC(aFecha1[2],aFecha1[1]-1,aFecha1[0]); 
                var fFecha2 = Date.UTC(aFecha2[2],aFecha2[1]-1,aFecha2[0]); 
                var dif = fFecha2 - fFecha1;
                var dias = Math.floor(dif / (1000 * 60 * 60 * 24)); 
                $("#PlazoEjecucion").val(dias);
                return dias;
            }
        ');

        $this->prm->ipu_id = (isset($this->prm->ipu_id)) ? $this->prm->ipu_id : '';
        $this->prm->ipu_numeroContrato = (isset($this->prm->ipu_numeroContrato)) ? $this->prm->ipu_numeroContrato : '';
        $this->prm->gpo_id = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id)) ? $this->prm->ur_id : '';

        $banderaGuardar = false;
        $banderaGuardarEliminar = false;
        $banderaGuardarCancelar = false;
        $banderaDesautorizar = false;
        $abc = '';

        $rfc = new Proveedores();
        if (!empty($this->prm->rfc_id)) {
            $rfc->setRfc_id($this->prm->rfc_id);
            $rfc->select();
            $abc = 'c';
        } else {
            $rfc->setRfc_id('');
            $abc = 'a';
            $banderaGuardar = true;
        }

        /*
            1- Cuando un contrato está en estado Generado puedo eliminar y modificar los datos pero no puedo z
            2- Cuando el Contrato está en estado Autorizado puedo agregar obras pero ya no puedo hacer modificaciones, para poder modificalo tengo
                que separar las obras y desautorizar el contrato para poder modificarlo o eliminarlo simepre y cuando no este en un compromiso
            3.- No se peude capturar el mismo contrato
            4.- Garantias
                a.- Las garantias solo se pueden agregar a Contratos Autorizados.
                b.- Un contrato no puede ser usado en un compromiso  si no tiene su garantia de Cumpliemnto. *falta verifcar con Andrea
                c.- Las garantias se pueden editar si no estan en un contrato
                d.- Se pueden agregar/quitar las garantias en un contrato autorizado y usado en un compromiso??? *falta verifcar con Andrea
                e.- cuales son las Reglas para cada tipo de Garantia???
        */

        $ipu = new InversionPublica();

        $EstadoContrato = 0;
        if (!empty($this->prm->ipu_id)) {
            $ipu->setIpu_id($this->prm->ipu_id);
            $ipu->setGpo($this->prm->gpo_id);
            $ipu->setUr($this->prm->ur_id);
            $ipu->setNumeroContrato($this->prm->ipu_numeroContrato);
            $ipu->select();
            $abc = 'c';
            $EstadoContrato = $ipu->getEip_id();
            $parametrosConsulta = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->ipu_id, $this->prm->gpo_id, $this->prm->ur_id, $this->prm->ipu_id];
            switch ($EstadoContrato) {
                case 1:
                    $banderaGuardar = true;
                    $banderaGuardarEliminar = true;
                    break;
                case 2:
                    $encPro = '';
                    if (!empty($this->prm->epg_id)) {
                        array_push($parametrosConsulta, $this->prm->epg_id);
                        $encPro = 'AND enc.epg_id = ?';
                    }
                    $banderaGuardar = false;
                    $this->conectar();
                    $this->setSql('
                        SELECT gpo_id,ur_id
                        FROM enc_InversionObra 
                        LEFT JOIN scp_Compromiso using(gpo_id,ur_id,ipu_id)
                        WHERE gpo_id = ? and ur_id = ? and ipu_id = ?  and ecm_id != "C"
                        UNION ALL
                        SELECT i.gpo,i.ur 
                        FROM ' . BD_SINPRES . '.cat_InversionPublica i
                        INNER JOIN enc_InversionObra enc USING(ipu_id)
                        LEFT JOIN scp_Afectacion af ON enc.gpo_id=af.gpo_id AND enc.ur_id=af.ur_id AND enc.ipu_id=af.afe_ipu_id AND  enc.epg_id=af.epg_id 
                        WHERE i.gpo = ? AND i.ur = ? AND ipu_id = ?  AND eaf_id != "C" AND tcl_id = "H"  ' . $encPro . '
                    ');

                    $this->consultar($parametrosConsulta);
                    $row = $this->extraer();
                    $this->desconectar();
                    if ($this->getNumeroRegistros() > 0) {
                        if (is_null($row['gpo_id'])) {
                            $banderaDesautorizar = true;
                        } else {
                            $this->mixScript('
                                $(".habilitar").attr("disabled","disabled"); 
                                notify("El Contrato  ' . $this->prm->ipu_id .
                                ' no puede ser modificado ya está en un compromiso o  tiene Afectaciones de anticipo","error");
                            ');
                            $banderaDesautorizar = false;
                        }
                    } else {
                        $banderaDesautorizar = true;
                    }
                    break;
                case 3: case 4: case 5:
                    $banderaGuardar = false;
                    $banderaGuardarEliminar = false;
                    $banderaGuardarCancelar = false;
                    $banderaDesautorizar = false;
                    break;
            }
        } else {
            $ipu->setIpu_id('');
            $abc = 'a';
            $banderaGuardar = true;
        }

        $TipoContrato = new stdClass();
        $TipoContrato->id = 'tci_id';
        $TipoContrato->name = 'control';
        $TipoContrato->required = true;
        $TipoContrato->enDiv = true;
        $TipoContrato->search = true;
        $TipoContrato->class = 'habilitar';
        $TipoContrato->sql = 'SELECT tci_id, tci_nombre from ' . BD_SINPRES . '.cat_TipoContratoInversion ';
        $TipoContrato->mensaje = 'Selecciona un tipo de contrato';
        $TipoContrato->label = 'Tipo de Contrato';
        $TipoContrato->selecciona = $ipu->getTci_id();

        $ModalidadContrato = new stdClass();
        $ModalidadContrato->id = 'mdc_id';
        $ModalidadContrato->name = 'control';
        $ModalidadContrato->required = true;
        $ModalidadContrato->enDiv = true;
        $ModalidadContrato->search = true;
        $ModalidadContrato->class = 'habilitar';
        $ModalidadContrato->sql = 'SELECT mdc_id,mdc_nombre FROM ' . BD_SINPRES . '.cat_ModalidadContratacion ';
        $ModalidadContrato->mensaje = 'Selecciona la modalidad';
        $ModalidadContrato->label = 'Modalidad de Contrato';
        $ModalidadContrato->selecciona = $ipu->getMdc_id();

        $NumContrato = new StdClass();
        $NumContrato->id = 'NumContrato';
        $NumContrato->label = 'Número de Contrato';
        $NumContrato->placeholder = 'Número de Contrato';
        $NumContrato->class = 'habilitar';
        $NumContrato->enDiv = true;
        $NumContrato->required = true;
        $NumContrato->value = $ipu->getNumeroContrato();

        $PlazoEjecucion = new StdClass();
        $PlazoEjecucion->id = 'PlazoEjecucion';
        $PlazoEjecucion->label = 'Plazo de Ejecución en dias';
        $PlazoEjecucion->placeholder = 'Plazo de Ejecución en dias';
        $PlazoEjecucion->class = 'habilitar';
        $PlazoEjecucion->enDiv = true;
        $PlazoEjecucion->readonly = true;
        $PlazoEjecucion->value = $ipu->getPlazoEjecucion();

        $FechaContrato = new stdClass();
        $FechaContrato->id = 'FechaContrato';
        $FechaContrato->name = 'FechaContrato';
        $FechaContrato->label = 'Fecha del Contrato';
        $FechaContrato->class = 'datepicker habilitar';
        $FechaContrato->enDiv = true;
        $FechaContrato->required = true;
        $FechaContrato->formato = 'fecha';
        $FechaContrato->cambioFecha = 'PlazoEjecucionC($("#FechaContrato").val(), $("#inicioFecha").val());';
        $FechaContrato->placeholder = date('Y-d-m');
        $FechaContrato->value = $ipu->getFechaContrato();

        $inicioFecha = new stdClass();
        $inicioFecha->id = 'inicioFecha';
        $inicioFecha->name = 'inicioFecha';
        $inicioFecha->label = 'Periodo Inicial';
        $inicioFecha->class = 'datepicker habilitar';
        $inicioFecha->enDiv = true;
        $inicioFecha->required = true;
        $inicioFecha->formato = 'fecha';
        $inicioFecha->cambioFecha = 'PlazoEjecucion($("#inicioFecha").val(), $("#finFecha").val());';
        $inicioFecha->placeholder = date('Y-d-m');
        $inicioFecha->value = $ipu->getInicio();

        $finFecha = new stdClass();
        $finFecha->id = 'finFecha';
        $finFecha->name = 'finFecha';
        $finFecha->label = 'Periodo Final';
        $finFecha->class = 'datepicker habilitar';
        $finFecha->enDiv = true;
        $finFecha->required = true;
        $finFecha->formato = 'fecha';
        $finFecha->cambioFecha = 'PlazoEjecucion($("#inicioFecha").val(), $("#finFecha").val());';
        $finFecha->placeholder = date('Y-d-m');
        $finFecha->value = $ipu->getTermino();

        $rfc_id = new stdClass();
        $rfc_id->value = $rfc->getRfc_id();
        ;
        $rfc_id->id = 'rfc_id';
        $rfc_id->camposText = 'rfc_nombre';
        $rfc_id->required = true;
        $rfc_id->tabla = BD_SINPRES . '.cat_Proveedores';
        $rfc_id->name = 'control';
        $rfc_id->class = 'habilitar';
        $rfc_id->autocomplete = true;
        $rfc_id->placeholder = 'R.F.C. del Contratista';
        $rfc_id->campoVal = 'rfc_id';
        $rfc_id->label = 'R.F.C. del Contratista';
        $rfc_id->desdecaracter = 4;
        $rfc_id->maxlength = 13;
        $rfc_id->enDiv = true;

        $localidad = new stdClass();
        $localidad->label = 'Localidad';
        $localidad->placeholder = 'Localidad';
        $localidad->id = 'localidad';
        $localidad->class = 'habilitar';
        $localidad->enDiv = true;
        $localidad->maxlength = 50;
        $localidad->value = $rfc->getLocalidad();

        $colonia = new stdClass();
        $colonia->placeholder = 'Colonia';
        $colonia->label = 'Colonia';
        $colonia->id = 'colonia';
        $colonia->class = 'habilitar';
        $colonia->enDiv = true;
        $colonia->maxlength = 55;
        $colonia->value = $rfc->getColonia();

        $calle = new stdClass();
        $calle->placeholder = 'Calle';
        $calle->label = 'Calle';
        $calle->id = 'calle';
        $calle->class = 'habilitar';
        $calle->enDiv = true;
        $calle->maxlength = 50;
        $calle->value = $rfc->getCalle();

        $referencia = new stdClass();
        $referencia->label = 'Referencia';
        $referencia->placeholder = 'Referencia';
        $referencia->id = 'referencia';
        $referencia->class = 'habilitar';
        $referencia->enDiv = true;
        $referencia->required = true;
        $referencia->maxlength = 100;
        $referencia->value = $rfc->getReferencia();

        $telefono = new stdClass();
        $telefono->id = 'telefono';
        $telefono->label = 'Telefono';
        $telefono->placeholder = 'Telefono';
        $telefono->enDiv = true;
        $telefono->maxlength = 10;
        $telefono->formato = 'telefono';
        $telefono->value = $rfc->getTelefono();
        $telefono->class = 'habilitar';

        $Imms = new StdClass();
        $Imms->id = 'Imms';
        $Imms->label = 'IMSS';
        $Imms->placeholder = 'IMSS';
        $Imms->class = 'habilitar';
        $Imms->enDiv = true;
        $Imms->value = $rfc->getImss();

        $numero = new stdClass();
        $numero->id = 'numero';
        $numero->label = 'Exterior';
        $numero->placeholder = 'Exterior';
        $numero->enDiv = true;
        $numero->maxlength = 5;
        $numero->class = 'habilitar';
        $numero->value = $rfc->getExterior();

        $interior = new stdClass();
        $interior->id = 'interior';
        $interior->label = ' Interior';
        $interior->placeholder = 'Interior';
        $interior->class = 'habilitar';
        $interior->enDiv = true;
        $interior->value = $rfc->getInterior();

        $edo_id = new stdClass();
        $edo_id->sql = 'select edo_id,edo_nombre FROM ' . BD_SINPRES . '.cat_Entidad where edo_id != 0';
        $edo_id->id = 'edo_id';
        $edo_id->name = 'edo_id';
        $edo_id->required = true;
        $edo_id->mensaje = '';
        $edo_id->change = '
            if ( $(this).val() != "" ){
                comunica("' . $this->pantalla . '",{
                    metodo:"CargaMunicipio",
                    edo_id:$("#edo_id").val(),
                    rfc_id:$("#rfc_id").val()
                });
            }
        ';

        $edo_id->label = 'Seleccion el estado';
        $edo_id->selecciona = $rfc->getEdo_id();
        $edo_id->class = 'habilitar';
        $edo_id->search = true;

        $mun_id = new stdClass();
        $mun_id->id = 'mun_id';
        $mun_id->search = true;
        $mun_id->width = '400px';
        $mun_id->label = 'Selecciona Municipio/Delegación';
        $mun_id->enRow = 1;
        $mun_id->required = true;
        $mun_id->class = 'habilitar';
        $mun_id->selecciona = $rfc->getMun_id();
        if ($rfc->getEdo_id() == '') {
            $mun_id = $this->templateCombo($mun_id);
        } else {
            $parametrosConsulta = [$rfc->getEdo_id()];
            $mun_id->sql = 'SELECT mun_id,mun_nombre FROM ' . BD_SINPRES . '.cat_Municipios WHERE edo_id = ? and mun_id != 0';
            $mun_id->params = $parametrosConsulta;
            $mun_id = $this->ponCombo($mun_id);
        }

        $codigo = new stdClass();
        $codigo->id = 'codigo';
        $codigo->label = 'C.P.';
        $codigo->placeholder = 'C.P.';
        $codigo->enDiv = true;
        $codigo->required = true;
        $codigo->formato = 'entero';
        $codigo->maxlength = 5;
        $codigo->value = $rfc->getCp();
        $codigo->class = 'habilitar';

        $email = new stdClass();
        $email->id = 'email';
        $email->label = 'Correo Electrónico del Contratista';
        $email->placeholder = 'Correo Electrónico';
        $email->enDiv = true;
        $email->type = 'email';
        $email->maxlength = 50;
        $email->value = $rfc->getEmail();
        $email->class = 'habilitar';

        $nombre = new stdClass();
        $nombre->label = 'Nombre';
        $nombre->placeholder = 'Nombre';
        $nombre->id = 'nombre';
        $nombre->name = 'nombre';
        $nombre->maxLength = 130;
        $nombre->readonly = 'readonly';
        $nombre->enDiv = true;
        $nombre->value = $rfc->getNombre();

        $paterno = new stdClass();
        $paterno->id = 'paterno';
        $paterno->readonly = 'readonly';
        $paterno->label = 'Apellido paterno';
        $paterno->placeholder = 'Apellido paterno';
        $paterno->enDiv = true;
        $paterno->value = $rfc->getPaterno();

        $materno = new stdClass();
        $materno->id = 'materno';
        $materno->readonly = 'readonly';
        $materno->label = 'Apellido materno';
        $materno->placeholder = 'Apellido materno';
        $materno->enDiv = true;
        $materno->value = $rfc->getMaterno();

        /* CONCEPTO DE PAGO  */
        $Descripcion = new stdClass();
        $Descripcion->id = 'Descripcion';
        $Descripcion->name = 'Descripcion';
        $Descripcion->label = 'Descripción';
        $Descripcion->placeholder = 'Descripción ';
        $Descripcion->enDiv = true;
        $Descripcion->required = true;
        $Descripcion->width = '400px';
        $Descripcion->class = 'anulaenter habilitar';
        $Descripcion->height = '150px';
        $Descripcion->text = $ipu->getObjetoContrato();

        $TotalContrato = new StdClass();
        $TotalContrato->id = 'TotalContrato';
        $TotalContrato->class = 'habilitar';
        $TotalContrato->label = 'Monto total del Contrato';
        $TotalContrato->required = true;
        $TotalContrato->enDiv = true;
        $TotalContrato->formato = 'moneda';
        $TotalContrato->value = $ipu->getImporteTotal();
        $TotalContrato = $this->ponInput($TotalContrato);

        $Iva = new StdClass();
        $Iva->values = [1 => '0.00',2 => '0.16',3 => 'Exento'];
        $Iva->class = 'radio rango limpiar';
        $Iva->name = 'Iva';
        $Iva->selecciona = 1;

        if ($abc == 'c') {
            $Iva->selecciona = $ipu->getIva_id();
        }
        $Iva->enDiv = true;
        $Iva->cols = 3;

        $ImporteIva = new StdClass();
        $ImporteIva->id = 'ImporteIva';
        $ImporteIva->class = 'habilitar';
        $ImporteIva->label = 'Importe Iva';
        $ImporteIva->required = true;
        $ImporteIva->enDiv = true;
        $ImporteIva->formato = 'moneda';
        $ImporteIva->readonly = 'readonly';
        $ImporteIva->value = $ipu->getImporteIva();
        $ImporteIva = $this->ponInput($ImporteIva);

        $retencionOtra = new stdClass();
        $retencionOtra->placeholder = '';
        $retencionOtra->label = 'Especificar';
        $retencionOtra->id = 'retencionOtra';
        $retencionOtra->class = 'habilitar';
        $retencionOtra->enDiv = true;
        $retencionOtra->maxlength = 100;

        $Bruto = new StdClass();
        $Bruto->id = 'Bruto';
        $Bruto->class = 'habilitar';
        $Bruto->label = 'Importe Bruto';
        $Bruto->required = true;
        $Bruto->enDiv = true;
        $Bruto->formato = 'moneda';
        $Bruto->readonly = 'readonly';
        $Bruto->value = $ipu->getImporteBruto();
        $Bruto = $this->ponInput($Bruto);

        $AnticipoObra = new StdClass();
        $AnticipoObra->values = [1 => 'No Aplica',2 => 'Aplica'];
        $AnticipoObra->class = 'radio rango limpiar';
        if ($abc == 'c') {
            if ($ipu->getImporteAnticipo() != '' && $ipu->getImporteAnticipo() != 0) {
                $AnticipoObra->selecciona = 2;
            }
        } else {
            $AnticipoObra->selecciona = 1;
        }
        $AnticipoObra->name = 'AnticipoObra';
        $AnticipoObra->cols = 3;

        $valor = '';
        $valor1 = '';
        $valor2 = '';
        $valor3 = '';
        $valor4 = '';
        $valor5 = '';
        $valor6 = '';
        $valor7 = '';
        $valor8 = '';
        $valor9 = '';
        $valor10 = '';
        $Retenciones = new StdClass();
        $Retenciones->values = [1 => 'No Aplica', 2 => 'Aplica'];
        $Retenciones->class = 'radio rango limpiar';
        if ($abc == 'c') {
            $this->conectar();
            $parametrosConsulta = [$this->prm->ipu_id];
            $this->setSql('
                SELECT ipu_id,tre_id,eir_descripcion,rcu_id,concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id) as nivel1,cgc_id 
                FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion 
                    JOIN ' . BD_SINPRES . '.cat_RetencionCuenta USING(tre_id,rcu_id)
                WHERE ipu_id = ?;
            ');
            $this->consultar($parametrosConsulta);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                $Retenciones->selecciona = 2;
            } else {
                $Retenciones->selecciona = 1;
            }
            $valor7 = '';
            while ($registros = $this->extraer()) {
                switch ($registros['tre_id']) {
                    case 1:
                        $valor = 'checked';
                        $valor1 = ' $("input[type=radio][name=option_1][value=' . $registros['tre_id'] . ']").click();';
                        break;
                    case 2:
                        $valor2 = 'checked';
                        break;
                    case 3:
                        $valor3 = 'checked';
                        break;
                    case 4:
                        $valor4 = 'checked';
                        break;
                    case 5:
                        $valor5 = 'checked';
                        break;
                    case 6:
                        $valor6 = 'checked';
                        $retencionOtra->value = $registros['eir_descripcion'];
                        break;
                    case 8:
                        $valor8 = 'checked';
                        break;
                    case 9:
                        $valor9 = 'checked';
                        break;
                    case 10:
                        $valor10 = 'checked';
                        break;
                }
            }
        }
        $Retenciones->name = 'Retenciones';
        $Retenciones->cols = 3;
        $retencionOtra = $this->ponInput($retencionOtra);
        $Porcentaje = new StdClass();
        $Porcentaje->id = 'Porcentaje';
        $Porcentaje->label = '% de Anticipo';
        $Porcentaje->placeholder = '% de Anticipo';
        $Porcentaje->class = 'habilitar';
        $Porcentaje->enDiv = true;
        $Porcentaje->formato = 'numero';
        $Porcentaje->value = $ipu->getPorcentajeAnticipo();

        $ImporteAnticipo = new StdClass();
        $ImporteAnticipo->id = 'ImporteAnticipo';
        $ImporteAnticipo->class = 'habilitar';
        $ImporteAnticipo->label = 'Importe de Anticipo';
        $ImporteAnticipo->required = true;
        $ImporteAnticipo->enDiv = true;
        $ImporteAnticipo->formato = 'moneda';
        $ImporteAnticipo->readonly = 'readonly';
        $ImporteAnticipo->value = $ipu->getImporteAnticipo();
        $ImporteAnticipo = $this->ponInput($ImporteAnticipo);

        $cancelar = '';
        $eliminar = '';
        $Desautorizar = '';
        $btnGuardar = '';
        $btnRegresar = '';

        if (($banderaGuardarEliminar) && ($_SESSION[_S]['nvj_id'] <= 2) && ($abc != 'a')) {
            $btnEliminar = new stdClass();
            $btnEliminar->id = 'delete';
            $btnEliminar->class = 'delete me-1';
            $btnEliminar->color = 'rojo';
            $btnEliminar->text = 'Eliminar';
            $btnEliminar->value = 'Eliminar';
            $btnEliminar->icon = 'solid fa-trash me-2';
            $eliminar = $this->ponBoton($btnEliminar);
        }

        if ($banderaGuardarCancelar && $_SESSION[_S]['nvj_id'] <= 2 && $abc != 'a') {
            $btnCancelar = new stdClass();
            $btnCancelar->id = 'btnCancelar';
            $btnCancelar->class = ' btn btn-outline-secondary delete me-1';
            $btnCancelar->text = 'Cancelar';
            $btnCancelar->value = 'Cancelar';
            $btnCancelar->icon = 'times me-2';
            $cancelar = $this->ponBoton($btnCancelar);
        }

        if ($banderaDesautorizar && $_SESSION[_S]['nvj_id'] <= 2 && $abc != 'a') {
            $btnDesautorizar = new stdClass();
            $btnDesautorizar->id = 'btnDesautorizar';
            $btnDesautorizar->class = 'me-1';
            $btnDesautorizar->color = 'rojo';
            $btnDesautorizar->text = 'Desautorizar';
            $btnDesautorizar->value = 'Desautorizar';
            $btnDesautorizar->icon = 'left-long me-2';
            $Desautorizar = $this->ponBoton($btnDesautorizar);
        }

        if ($banderaGuardar) {
            $btnGuardar = new stdClass();
            $btnGuardar->icon = 'solid fa-floppy-disk me-2';
            $btnGuardar->id = 'guardar';
            $btnGuardar->text = 'Guardar';
            $btnGuardar->value = 'Guardar';
            $btnGuardar->class = 'me-1';
            $btnGuardar->color = 'verde';
            $btnGuardar = $this->ponBoton($btnGuardar);
        }

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->color = 'gris';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->icon = 'solid fa-circle-left me-2';

        $btnFiniquitar = '';
        /*-------------------------- Para el finiquito del contrato ----------------------*/
        if ($EstadoContrato == 2) {
            $btnFiniquitar = new stdClass();
            $btnFiniquitar->icon = 'solid fa-power-off me-2';
            $btnFiniquitar->id = 'btnFiniquitar';
            $btnFiniquitar->text = 'Finiquitar';
            $btnFiniquitar->value = 'Finiquitar Contrato';
            $btnFiniquitar->class = 'me-1';
            $btnFiniquitar->color = 'rojo';
            $btnFiniquitar = $this->ponBoton($btnFiniquitar);
        }

        $html = '';
        $html .= '<form id="com">';
        $html .= '
            <legend align="center" style="color:#FF4000" class="mb-2">TIPO Y MODALIDAD DEL CONTRATO</legend>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponCombo($TipoContrato) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponCombo($ModalidadContrato) . '
                </fieldset>
            </div>
        ';

        $html .= '
            <legend align="center" style="color:#FF4000" class="mb-2">DOCUMENTO DE REFERENCIA</legend>
            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-12">
                    ' . $this->ponInput($NumContrato) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-4">
                    ' . $this->ponInput($FechaContrato) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-4">
                    ' . $this->ponInput($inicioFecha) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-4">
                    ' . $this->ponInput($finFecha) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-12">
                    ' . $this->ponInput($PlazoEjecucion) . '
                </fieldset>
            </div>
        ';

        $html .= '
            <legend align="center" style="color:#FF4000" class="mb-2">DATOS DEL CONTRATISTA</legend>

            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($rfc_id) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($nombre) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($paterno) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($materno) . '
                </fieldset>
            </div>

            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($edo_id) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4" id="tr_municipio">
                    ' . $mun_id . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($localidad) . '
                </fieldset>
            </div>

            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($colonia) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($calle) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    <div class="row">
                        <fieldset class="col-md-4 col-xs-4">
                            ' . $this->ponInput($numero) . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-4">
                            ' . $this->ponInput($interior) . '
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-4">
                            ' . $this->ponInput($codigo) . '
                        </fieldset>
                    </div>
                </fieldset>
            </div>

            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($Imms) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($referencia) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($telefono) . '
                </fieldset>	
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($email) . '
                </fieldset>
            </div>
        ';

        $html .= '
            <legend align="center" style="color:#FF4000" class="mb-2">OBJETO</legend>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponTextarea($Descripcion) . '
                </fieldset>
            </div>
        ';

        $html .= '
            <legend align="center" style="color:#FF4000" class="mb-2">IMPORTES TOTALES</legend>
            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-3">
                    ' . $TotalContrato . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    <label>Porcentaje de iva</label>
                    ' . $this->ponRadioFlex($Iva) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="divBruto">
                    ' . $Bruto . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="divIva">
                    ' . $ImporteIva . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-3 col-xs-3">
                    <label>Anticipo de Obra</label>
                    ' . $this->ponRadioFlex($AnticipoObra) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="divPorcent">
                    ' . $this->ponInput($Porcentaje) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="divAnticipo">
                    ' . $ImporteAnticipo . '
                </fieldset>
            </div>
        ';

        $html .= '
            <legend align="center" style="color:#FF4000" class="mb-2">RETENCIONES</legend>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    <label>Elegir Retenciones</label>
                    ' . $this->ponRadioFlex($Retenciones) . '
                </fieldset>
            </div>

            <div class="row mb-4" id="retenciones">
                <fieldset class="col-md-12 col-xs-12">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th bgcolor="#000000" style="text-align:center" class="info"><font color="#FFFFFF">Retenciones</font></th>
                        </tr>
                        <tr >
                            <td colspan="3"; bgcolor="#CBF093">
                                <input  ' . $valor . ' class="habilitar" type="checkbox" id="option1" name="option1" value="1">
                                <b> 1- Secretaría de la Contraloría y Transparencia Gubernamental <font color="#FF2626">(Órgano de Control)</font><b>
                            </td>
                        </tr>
                            <tr>
                                <td width="50%">
                                    <input class="habilitar" style="margin-left:5em;" type="radio"  name="option_1" value="1"><b> 5 al millar<b>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="habilitar" style="margin-left:5em;" type="radio"  name="option_1" value="2"><b> 2 al millar<b><br>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="habilitar" style="margin-left:5em;" type="radio" name="option_1" value="3"><b> 1 al millar<b><br>
                                </td>
                            </tr>
                        <tr>
                            <td bgcolor="#CBF093">
                                <input ' . $valor2 . ' class="habilitar" type="checkbox" id="option2" name="option2" value="1"><b> 
                                2-  Órgano Superior de Fiscalización del Estado de Oaxaca <font color="#FF2626">(1 al millar Órgano Fiscalizador).</font><br>
                            </td>
                        </tr>
                        <!--
                        <tr> 
                            <td bgcolor="#CBF093">
                                <input ' . $valor3 . ' class="habilitar" type="checkbox" id="option3" name="option3" value="1">
                                <b> 3- Supervisión de Obra <font color="#FF2626">(1.5 % Ley Estatal de Derechos).</font><br>
                            </td>
                            <td  align="left"; colspan="2"; bgcolor="#CBF093" align="center";><b></b></td>
                        </tr>
                        -->
                        <tr>
                            <td bgcolor="#CBF093">
                                <input  ' . $valor4 . ' class="habilitar" type="checkbox" id="option4" name="option4" value="1">
                                <b> 3-   Instituto de Capacitación de la Industria de la Construcción   <font color="#FF2626"> (2 al millar ICIC)</font><b><br>
                            </td>
                        </tr>
                        <tr>
                            <td  bgcolor="#CBF093">
                                <input ' . $valor5 . ' class="habilitar" type="checkbox" id="option5" name="option5" value="1">
                                <b> 4- Fundación de los Empresarios Constructores de Oaxaca  <font color="#FF2626">(5 al millar FUNDECO / CMIC)</font><b><br>
                            </td>
                        </tr>
                        <!--
                        <tr>
                            <td  bgcolor="#CBF093">
                                <input ' . $valor7 . ' class="habilitar" type="checkbox" id="option7" name="option7" value="1">
                                <b> 5- Servicios de inspección, vigilancia y control <font color="#FF2626">(2.5% ART. 82 LED)</font><b><br>
                            </td>

                        </tr>
                        -->
                        <tr>
                            <td  bgcolor="#CBF093">
                                <input ' . $valor8 . ' class="habilitar" type="checkbox" id="option8" name="option8" value="1">
                                <b> 5- Supervisión de obra  <font color="#FF2626">(3 % ART. 24 FRACC. VI LED)</font><b><br>
                            </td>

                        </tr>
                            
                        <tr>
                            <td  bgcolor="#CBF093">
                                <input ' . $valor9 . ' class="habilitar" type="checkbox" id="option9" name="option9" value="1">
                                <b> 6- Impuesto por Remuneraciones al Trabajo Personal <font color="#FF2626">(3% Artículos 64 al 66 LEH)</font><b><br>
                            </td>

                        </tr>
                        <tr>
                            <td colspan="3"; bgcolor="#CBF093">
                                <input ' . $valor6 . ' class="habilitar" type="checkbox" id="option6" name="option6"><b> 7-  Otras<b><br>
                            </td>

                        </tr>

                        <tr>
                            <td width="50%">
                                <div class="row">
                                    <fieldset class="col-md-4 col-xs-4">
                                        ' . $retencionOtra . '
                                    </fieldset>
                                    <fieldset class="col-md-4 col-xs-4">
                                        
                                    </fieldset>
                                    <fieldset class="col-md-4 col-xs-4">
                                        
                                    </fieldset>
                                </div>
                            </td>
                        </tr>
        ';
        /*La numeración de la visualización no es correcto porque se ha comentado la 3 para visualización solo se recorrio la vusalización pero el abc siguen siendo el mismo de la opción  */
        $html .= '
                    </table>
                </fieldset>
            </div>
        ';

        $this->agregaTools(' ' . $this->ponBoton($btnRegresar) . ' ' . $btnGuardar . ' ' . $eliminar . ' ' . $cancelar . ' ' . $Desautorizar . ' ' . $btnFiniquitar . ' ');
        $this->mixHtml($html);
        $this->mixScript('
            $("#rfc_id").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8){
                    return true;
                } else if (keynum==95){
                    return false;
                } else if (keynum==241 || keynum==209){
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))){
                    return true;
                } else {
                    return false;
                }
            });

            $("#NumContrato").keypress(function(e) {
                console.log("Estoy entrando a la funcion");
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8){
                    return true;
                } else if (keynum==95){
                    return false;
                } else if (keynum==45){
                    return true;
                } else if (keynum==47){
                    return true;
                } else if (keynum==241 || keynum==209){
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))){
                    return true;
                } else {
                    return false;
                }
            });
        ');

        $ipu_id = (isset($this->prm->ipu_id) && $this->prm->ipu_id != '') ? 'obj.ipu_id = ' . $this->prm->ipu_id . '; ' : '';

        $this->mixScript('
            ' . $valor1 . '

            if ($("#option1").is(":checked")){
                $("input[name=option_1]").prop("disabled",false);
            } else {
                $("input[name=option_1]").prop("disabled",true);
                $("input[name=option_1]:checked").prop("checked",false);
            }

            $("#option1").click(function(){
                if ($("#option1").is(":checked")){
                    $("input[name=option_1]").prop("disabled",false);
                } else {
                    $("input[name=option_1]").prop("disabled",true);
                    $("input[name=option_1]:checked").prop("checked",false);
                }
            });

            $(".otroCheck").prop("disabled",true);

            $("#option6").click(function(){
                if ( $(this).is(":checked") ) {
                    $("#retencionOtra").attr("disabled",false);
                    $(".otroCheck").prop("disabled",false);
                } else {
                    $("#retencionOtra").attr("disabled",true);
                    $(".otroCheck:checked").click();
                    $(".otroCheck").prop("disabled",true);
                }
            });

            $(".otroCheck").click(function(){
                if ( $("#option6").is(":checked") ){
                    if ( $(".otroCheck:checked").length == 3 ){
                        console.log("entra");
                        $(".otroCheck").each(function(){
                            if ( !$(this).is(":checked") ) $(this).prop("disabled",true);
                        });
                    } else $(".otroCheck").prop("disabled",false);
                    if ( $(".otroCheck:checked").length > 3 ) $(this).click();
                } else {
                    $("#"+$(this).prop("id") + "nivel1").prop("disabled",true);
                    $("#"+$(this).prop("id") + "nivel4").prop("disabled",true);
                }
            });

        ');

        $clic = '';
        if ($abc == 'c') {
            $clic .= '
            $("input[name=Iva]:checked").click(); ';
        }
        //-----------------Validacion Importes -------------------------*
        $this->mixScript('
            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "Contratos",
                    "ipu_id":"' . $this->prm->ipu_id . '",
                    "tarea":"' . $this->prm->tarea . '",
                    "vur_id":"' . $this->prm->vur_id . '"
                });
            });

            $("#rfc_id").blur(function(){
                if ($(this).length!=0){
                    comunica("' . $this->pantalla . '",{metodo:"Proveedor",	rfc:$(this).val(), abc:"' . $abc . '" });
                }
            });

            $("#NumContrato").blur(function(){
                if ($(this).length!=0){
                    comunica("' . $this->pantalla . '",{metodo:"ValidarContrato", ipu_numeroContrato:$(this).val() });
                }
            });

            $("#importe").blur(function(){
                var disponible = parseFloat(unformatNumber($("#disponible").text()).toFixed(2));
                var capturado  = parseFloat(unformatNumber($("#importe").val()).toFixed(2));
                if (disponible<capturado){
                    notify( "El importe no puede ser mayor que el disponible ", "error");
                    $("#importe").val("");
                }
            }); 

            $("#finFecha").blur(function(){
                if (!validarFechas($("#inicioFecha").val(), $("#finFecha").val())){
                    notify("La fecha de termino no puede ser menor a la fecha de inicio","error");
                    return false;
                }
                PlazoEjecucion($("#inicioFecha").val(), $("#finFecha").val());
            });

            $("#TotalContrato").blur(function(){
                importes();
                $("#Porcentaje").blur();
            }); 

            function importes(){
                var importecontrato = parseFloat(unformatNumber($("#TotalContrato").val()).toFixed(2));
                if (importecontrato!="" && importecontrato!=0){
                    $("input[name=AnticipoObra]:checked").click();
                    $("input[name=Iva]:checked").click();
                }
            }

            $("input[name=Retenciones]").click(function(){
                switch ($("input[name=Retenciones]:checked").val()){
                    case "1": 
                        $("#retenciones").hide();
                    break;
                    case "2": 
                        $("#retenciones").show();
                    break;
                }
            });

            $("input[name=Retenciones]:checked").click();

            $("input[name=Iva]").click(function(){
                importesIva();
            });

            $("input[name=AnticipoObra]").click(function(){
                Importeanticipo();
            });

            function importesIva(){
                switch ($("input[name=Iva]:checked").val()){
                    case "1": 
                        $("#divBruto").show();
                        $("#divIva").show();
                        var importeBruto = parseFloat(unformatNumber($("#TotalContrato").val()).toFixed(2));
                        $("#Bruto").val(formatNumber(importeBruto));
                        $("#ImporteIva").val(0);

                    break;
                    case "2": 
                        $("#divBruto").show();
                        $("#divIva").show();
                        var calculo = parseFloat(unformatNumber($("#TotalContrato").val()).toFixed(2))/ 1.16;
                        var importeBruto = parseFloat(calculo.toFixed(2)) * (.16);
                        $("#Bruto").val(formatNumber(calculo));	
                        $("#ImporteIva").val(formatNumber(importeBruto));	

                    break;
                    case "3": 
                        $("#divBruto").hide();
                        $("#divIva").hide();
                        $("#Bruto").val("");
                        $("#ImporteIva").val(0);
                    break;
                }
            }
            ' . $clic . '

            function Importeanticipo(){
                switch ($("input[name=AnticipoObra]:checked").val()){
                    case "1": 
                        $("#divAnticipo").hide();
                        $("#divPorcent").hide();
                        var anticipo = parseFloat(unformatNumber($("#TotalContrato").val()).toFixed(2));
                        $("#Porcentaje").val("");
                        $("#ImporteAnticipo").val("");

                    break;
                    case "2": 
                        $("#divPorcent").show();
                        var anticipo = parseFloat(unformatNumber($("#TotalContrato").val()).toFixed(2));
                        $("#Porcentaje").blur(function(){
                            var porcentaje = parseFloat(unformatNumber($("#Porcentaje").val()).toFixed(2));
                            if (porcentaje>50){
                                alert("El porcentaje no puede ser mayor que el 50% del contrato");
                                $("#Porcentaje").val("");
                            } else {
                                if (porcentaje=="" && porcentaje==0){
                                    alert("Debes ingresar un porcentaje de Anticipo");
                                    return false;
                                } else {	
                                    $("#divAnticipo").show();
                                    var anticipoFinal= parseFloat(((anticipo *porcentaje)/100).toFixed(2));
                                    $("#ImporteAnticipo").val(formatNumber(anticipoFinal));
                                }
                            }

                        });
                    break;
                }
            }
        ');

        $this->mixScript('
            $("#btnRegresarIndex").click(function(){
                $("nav").menusinpres("clickMenu");
            });

            $("#guardar").click(function(){
                if (!validarFechas($("#inicioFecha").val(), $("#finFecha").val())){
                    notify("La fecha de termino no puede ser menor o igual a la fecha de inicio","error");
                    return false;
                }

                if (!validarFechas2($("#FechaContrato").val(), $("#inicioFecha").val())){
                    notify("La fecha de inicio no puede ser menor a la fecha de contrato","error");
                    return false;
                }

                // Frase es el la cadena de caracteres que se tomara antes de de llenar a la base de datos 
                // y se le quitaran las comillas simpres y dobles

                var frase =$("#Descripcion").val();
                var frase2= frase;
                var contador=0;

                do{
                    frase2 = frase2.replace("\'"," ");
                }while(frase2.indexOf("\'") != -1);
                do{
                    frase2 = frase2.replace("\""," ");
                }while(frase2.indexOf("\"") != -1);

                var frase3=frase2;

                while(frase3.indexOf(" ") != -1){
                    frase3 = frase3.replace(" ","x");
                    contador++;
                }
                //if (contador==frase2.length){
                    //frase2=$("#NumContrato").val();
                //}

                var obj = {"metodo":"abcContrato"};
                obj.abc = "' . $abc . '";
                ' . $ipu_id . '
                obj.tci_id = $("#tci_id").val();
                obj.mdc_id = $("#mdc_id").val();
                obj.numeroContratoNuevo = $("#NumContrato").val();
                obj.numeroContrato = ' . (($abc == 'a') ? '$("#NumContrato").val()' : '"' . $ipu->getNumeroContrato() . '"') . ';
                obj.plazoEjecucion = $("#PlazoEjecucion").val();
                obj.fechaContrato = $("#FechaContrato").val();
                obj.inicio = $("#inicioFecha").val();
                obj.termino = $("#finFecha").val();
                obj.objetoContrato = frase2;
                obj.importeTotal =parseFloat(unformatNumber($("#TotalContrato").val()).toFixed(2));
                obj.iva_id = $("input[name=Iva]:checked").val();	
                if ($("input[name=Iva]:checked").val()!="3"){
                    // obj.iva_id = ($("input[name=Iva]:checked").val()==1)? 0 :1;	
                    obj.importeBruto =parseFloat(unformatNumber($("#Bruto").val()).toFixed(2));
                    obj.importeIva =parseFloat(unformatNumber($("#ImporteIva").val()).toFixed(2));
                }
                if ($("input[name=AnticipoObra]:checked").val()=="2"){
                    obj.porcentajeAnticipo = $("#Porcentaje").val();
                    obj.importeAnticipo =parseFloat(unformatNumber($("#ImporteAnticipo").val()).toFixed(2));
                } else {
                    obj.importeAnticipo=0;
                    obj.porcentajeAnticipo=0; 
                }
                obj.cp = $("#codigo").val();
                obj.rfc_id = $("#rfc_id").val();
                obj.calle = $("#calle").val();
                obj.nombre = $("#nombre").val();
                obj.paterno = $("#paterno").val();
                obj.materno = $("#materno").val();
                obj.exterior = $("#numero").val();
                obj.interior = $("#interior").val();
                obj.localidad = $("#localidad").val();
                obj.colonia = $("#colonia").val();
                obj.referencia = $("#referencia").val();
                obj.telefono = $("#telefono").val();
                obj.email = $("#email").val();
                obj.edo_id = $("#edo_id").val();
                obj.mun_id = $("#mun_id").val();
                obj.imss = $("#Imms").val();
                obj.importeAcomprometer = 0;
                obj.vur_id = "' . $this->prm->vur_id . '";
                obj.tarea = "' . $this->prm->tarea . '";

                if ($("input[name=Retenciones]:checked").val()=="2"){
                    if ($("#option1").is(":checked")){
                        obj.opcion1 = $("input[name=option_1]:checked").val();
                    }
                    if ($("#option2").is(":checked")){
                        obj.opcion2 = 1;
                    }
                    if ($("#option3").is(":checked")){
                        obj.opcion3 = 1;
                    }
                    if ($("#option4").is(":checked")){
                        obj.opcion4 = 1;
                    }
                    if ($("#option5").is(":checked")){
                        obj.opcion5 = 1;
                    }

                    if ($("#option6").is(":checked")){
                        obj.opcion6 = 1;
                        obj.retencionOtra = $("#retencionOtra").val();
                    }

                    if ($("#option7").is(":checked")){
                        obj.opcion7 = 1;
                    }

                    if ($("#option8").is(":checked")){
                    obj.opcion8 = 1;
                    }
                    if ($("#option9").is(":checked")){
                    obj.opcion9 = 1;
                    }
                    if ($("#option10").is(":checked")){
                    obj.opcion10 = 1;
                    }
                    if ($("#option11").is(":checked")){
                    obj.opcion11 = 1;
                    }
                    if ($("#option12").is(":checked")){
                    obj.opcion12 = 1;
                    }
                }

                if (!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                console.log(obj);
                return false;
            });

            function validarFechas(fechaInicio, fechaTermino){
                var arregloFechaInicio = fechaInicio.split("/");
                var arregloFechaTermino = fechaTermino.split("/");
                
                var anioFechaInicio = arregloFechaInicio[2];
                var mesFechaInicio = arregloFechaInicio[1];
                var diaFechaInicio = arregloFechaInicio[0];
                
                var anioFechaTermino = arregloFechaTermino[2];
                var mesFechaTermino = arregloFechaTermino[1];
                var diaFechaTermino = arregloFechaTermino[0];
                
                if (anioFechaInicio < anioFechaTermino ){
                    return true;
                } else if (anioFechaInicio > anioFechaTermino){
                    return false;
                } else {
                    if (mesFechaInicio < mesFechaTermino){
                        return true;
                    } else if (mesFechaInicio > mesFechaTermino){
                        return false;
                    } else {
                        if (diaFechaInicio < diaFechaTermino){
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        
            function validarFechas2(fechaInicio, fechaTermino){
                var arregloFechaInicio = fechaInicio.split("/");
                var arregloFechaTermino = fechaTermino.split("/");
                
                var anioFechaInicio = arregloFechaInicio[2];
                var mesFechaInicio = arregloFechaInicio[1];
                var diaFechaInicio = arregloFechaInicio[0];
                
                var anioFechaTermino = arregloFechaTermino[2];
                var mesFechaTermino = arregloFechaTermino[1];
                var diaFechaTermino = arregloFechaTermino[0];
                
                if (anioFechaInicio < anioFechaTermino ){
                    return true;
                } else if (anioFechaInicio > anioFechaTermino){
                    return false;
                } else {
                    if (mesFechaInicio < mesFechaTermino){
                        return true;
                    } else if (mesFechaInicio > mesFechaTermino){
                        return false;
                    } else {
                        if (diaFechaInicio <= diaFechaTermino){
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        
            $("#delete").click(function(){
                alertify.confirm(
                    "ELIMINAR CONTRATO",
                    "¿ Confirma que desea eliminar el Contrato ' . ($ipu->getIpu_id()) . '?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo: "abcContrato",
                            abc: "b",
                            ipu_id: "' . ($ipu->getIpu_id()) . '",
                            numeroContrato: "' . ($ipu->getNumeroContrato()) . '",
                            vur_id: "' . $this->prm->vur_id . '",
                            tarea: "' . $this->prm->tarea . '"
                        })
                    },undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Eliminar", cancel: "Cancelar"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                    }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                    if (cancela) {
                        cancela.classList.remove("btn-danger");
                        cancela.classList.add("btn-outline-danger");
                    }
            });

            $("#btnDesautorizar").click(function(){
                alertify.confirm(
                    "DESAUTORIZAR CONTRATO",
                    "¿ Confirma que desea Desautorizar  el Contrato ' . ($ipu->getIpu_id()) . ' <h5 style=\"display:inline\"></h5> ?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo: "DesAutorizarObra",
                            ipu_id: "' . ($ipu->getIpu_id()) . '",
                            numeroContrato: "' . ($ipu->getNumeroContrato()) . '",
                            vur_id: "' . $this->prm->vur_id . '",
                            tarea: "' . $this->prm->tarea . '"
                        })
                    },undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Desautorizar", cancel: "Cancelar"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                    }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                    if (cancela) {
                        cancela.classList.remove("btn-danger");
                        cancela.classList.add("btn-outline-danger");
                    }
            });

            $("#btnFiniquitar").click(function(){
                alertify.confirm(
                    "FINIQUITAR CONTRATO",
                    "¿ Confirma que desea Finiquitar  el Contrato ' . ($ipu->getNumeroContrato()) . ' con el ID  ' . ($ipu->getIpu_id()) . ' <h5 style=\"display:inline\"></h5> ? </br> Recuerde que una vez finiquitado ya no se pueden generar CLC´s ",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo: "FiniquitarContrato",
                            ipu_id: "' . ($ipu->getIpu_id()) . '",
                            ipu_numeroContrato: "' . ($ipu->getNumeroContrato()) . '",
                            tarea: "' . $this->prm->tarea . '"
                        })
                    },undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Finiquitar", cancel: "Cancelar Operación"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                    }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                    if (cancela) {
                        cancela.classList.remove("btn-danger");
                        cancela.classList.add("btn-outline-danger");
                    }
            });
        ');

        if ($abc == 'c') {
            $this->prm->rfc = $rfc->getRfc_id();
            $this->Proveedor();
        }
    }

    private function Proveedor()
    {
        $parametrosConsulta = [$this->prm->rfc];
        $sql = '
            SELECT
                rfc_id,rfc_nombre,rfc_paterno,rfc_materno, soc_nombre,
                edo_id,edo_nombre,mun_id,mun_nombre,rfc_localidad,rfc_colonia,
                rfc_calle,rfc_exterior,rfc_interior,rfc_cp,rfc_telefono,rfc_email,rfc_imss,rfc_referencia	
            FROM ' . BD_SINPRES . '.cat_Proveedores 
            INNER JOIN cat_Proveedor USING (rfc_id)
            INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor using(soc_id) 
            INNER JOIN ' . BD_SINPRES . '.cat_Entidad using(edo_id)
            INNER JOIN ' . BD_SINPRES . '.cat_Municipios using(edo_id,mun_id)
            WHERE  rfc_id = ? AND sts_id = 1
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametrosConsulta, );
        $row = $this->sacar();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            $this->mixScript('
                $("#nombre").val("' . str_replace('"', '', $row['rfc_nombre']) . '");
                $("#materno").val("' . str_replace('"', '', $row['rfc_materno']) . '");
                $("#paterno").val("' . str_replace('"', '', $row['rfc_paterno']) . '");
                $("#edo_id").val("' . str_replace('"', '', $row['edo_id']) . '");
                $("#localidad").val("' . str_replace('"', '', $row['rfc_localidad']) . '");
                $("#colonia").val("' . str_replace('"', '', $row['rfc_colonia']) . '");
                $("#calle").val("' . str_replace('"', '', $row['rfc_calle']) . '");
                $("#telefono").val("' . str_replace('"', '', $row['rfc_telefono']) . '");
                $("#referencia").val("' . str_replace('"', '', $row['rfc_referencia']) . '");
                $("#numero").val("' . str_replace('"', '', $row['rfc_exterior']) . '");
                $("#interior").val("' . str_replace('"', '', $row['rfc_interior']) . '");
                $("#codigo").val("' . str_replace('"', '', $row['rfc_cp']) . '");
                $("#email").val("' . str_replace('"', '', $row['rfc_email']) . '");
                callbackComunica = function(){
                    $("#mun_id").val("' . str_replace('"', '', $row['mun_id']) . '");
                    $("#mun_id").selectpicker("refresh");
                };
                $("#edo_id").change();
                $("#edo_id").selectpicker("refresh");
            ');
        } else {
            $this->mixScript('$("#rfc_nombre").val("");
                    notify("Atención \n El Proveedor con RFC ' . $this->prm->rfc . ' no existe en la base de datos!!","error");
                ');
            return false;
        }
    }

    private function ValidaCuenta()
    {
        /*
            1-Tenemos que validar que no se capturen las cuentas del 1 al 5-
            2-Tenemos que validar que empice por 2 la cuenta
            3-Tenemos que validar que exista en cg cuarto
            4-Aunque ya exista la cuenta en el catalogo debemos dejarlos capturar en otro contrato de la misma UR o diferentes
        */
        $this->prm->cg_cuarto = (isset($this->prm->cg_cuarto)) ? $this->prm->cg_cuarto : '';
        $this->prm->cuarto = (isset($this->prm->cuarto)) ? $this->prm->cuarto : '';

        if (strlen($this->prm->cg_cuarto) == 5 || strlen($this->prm->cuarto) == 3) {
            $ger = substr($this->prm->cg_cuarto, 0, 1);
            $cgp = substr($this->prm->cg_cuarto, 1, 1);
            $rub = substr($this->prm->cg_cuarto, 2, 1);
            $cgcu = substr($this->prm->cg_cuarto, 3, 1);
            $cgsc = substr($this->prm->cg_cuarto, 4, 1);
            $nivel = $mensaje = $where = '';
            $parametrosConsulta = [$ger,$cgp,$rub,$cgcu,$cgsc];
            $where = ' WHERE ger_id = ? AND  cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? ';
            if ($this->prm->cuarto != '' && strlen($this->prm->cuarto) == 3) {
                array_push($parametrosConsulta, $this->prm->cuarto);
                $where .= ' AND cgc_id = ? ';
            }

            $this->conectar();
            $this->setSql('
                SELECT 
                    tre_id,rcu_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id 
                FROM cg_CuentaCuarto
                    LEFT JOIN cat_RetencionCuenta USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id)
                ' . $where . ';
            ');
            $this->consultar($parametrosConsulta);
            $this->desconectar();
            $cuentas = $this->extraer();
            if ($this->getNumeroRegistros() != 0) {
                if ($cuentas['tre_id'] == '' && is_null($cuentas['tre_id'])) {
                    if ($this->prm->cg_cuarto != '' && $this->prm->cuarto != '') {
                        switch ($this->prm->cuenta) {
                            case 1:
                                $nivel = '$("#otro1nivel4").addClass("success");';
                                break;
                            case 2:
                                $nivel = '$("#otro2nivel4").addClass("success");';
                                break;
                            case 3:
                                $nivel = '$("#otro3nivel4").addClass("success");';
                                break;
                        }
                    } else {
                        switch ($this->prm->cuenta) {
                            case 1:
                                $nivel = '$("#otro1nivel1").addClass("success");';
                                break;
                            case 2:
                                $nivel = '$("#otro2nivel1").addClass("success");';
                                break;
                            case 3:
                                $nivel = '$("#otro3nivel1").addClass("success");';
                                break;
                        }
                    }
                    $this->mixScript(' ' . $nivel . ' ');
                } else {
                    $this->mixScript('alert("La Cuenta ya existe");');
                }
            } else {
                if ($this->prm->cg_cuarto != '' && $this->prm->cuarto != '') {
                    switch ($this->prm->cuenta) {
                        case 1:
                            $mensaje = 'alert("El cuarto nivel de ' . $this->prm->cuarto . ' no está en el catálogo de Cuenta Contables"); ';
                            $nivel = '$("#otro1nivel4").val("");';
                            break;
                        case 2:
                            $mensaje = 'alert("El cuarto nivel de ' . $this->prm->cuarto . ' no está en el catálogo de Cuenta Contables"); ';
                            $nivel = '$("#otro2nivel4").val("");';
                            break;
                        case 3:
                            $mensaje = 'alert("El cuarto nivel de ' . $this->prm->cuarto . ' no está en el catálogo de Cuenta Contables"); ';
                            $nivel = '$("#otro2nivel4").val("");';
                            break;
                    }
                } else {
                    switch ($this->prm->cuenta) {
                        case 1:
                            $mensaje = 'alert("El primer nivel de ' . $this->prm->cg_cuarto . ' no está en el catálogo de Cuenta Contables"); ';
                            $nivel = '$("#otro1nivel1").val("")';
                            break;
                        case 2:
                            $mensaje = 'alert("El primer nivel de ' . $this->prm->cg_cuarto . ' no está en el catálogo de Cuenta Contables"); ';
                            $nivel = '$("#otro2nivel1").val("");';
                            break;
                        case 3:
                            $mensaje = 'alert("El primer nivel de ' . $this->prm->cg_cuarto . ' no está en el catálogo de Cuenta Contables"); ';
                            $nivel = '$("#otro3nivel1").val("");';
                            break;
                    }
                }
                $this->mixScript('
                    ' . $mensaje . '
                    ' . $nivel . '
                ');
            }
        }
    }

    //-------METODO ABC (agrega,elimina,update) esto de manera general----------------
    private function abcContrato()
    {
        $this->prm->ejercicio = APP_EJERCICIO;
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->prm->gpo = $_SESSION[_S]['gpo_id'];
        $this->prm->ur = $_SESSION[_S]['ur_id'];
        $this->prm->tarea = (isset($this->prm->tcl_id)) ? $this->prm->tarea : $this->prm->tarea;
        $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
        $this->prm->epg_id = ClasificacionAdministrativa::getEpg();

        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $this->prm->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $this->prm->ur_id = $_SESSION[_S]['clasAdm-ur'];

        } else {
            $this->prm->gpo_id = $_SESSION[_S]['gpo_id'];
            $this->prm->ur_id = $_SESSION[_S]['ur_id'];
        }

        $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'] ?? null;
        $this->prm->eip_id = 1;
        $this->prm->opcion1 = (isset($this->prm->opcion1)) ? $this->prm->opcion1 : '';
        $this->prm->opcion2 = (isset($this->prm->opcion2)) ? $this->prm->opcion2 : '';
        $this->prm->opcion3 = (isset($this->prm->opcion3)) ? $this->prm->opcion3 : '';
        $this->prm->opcion4 = (isset($this->prm->opcion4)) ? $this->prm->opcion4 : '';
        $this->prm->opcion5 = (isset($this->prm->opcion5)) ? $this->prm->opcion5 : '';
        $this->prm->opcion6 = (isset($this->prm->opcion6)) ? $this->prm->opcion6 : '';
        $this->prm->opcion7 = (isset($this->prm->opcion7)) ? $this->prm->opcion7 : '';
        $this->prm->opcion8 = (isset($this->prm->opcion8)) ? $this->prm->opcion8 : '';
        $this->prm->opcion9 = (isset($this->prm->opcion9)) ? $this->prm->opcion9 : '';
        $this->prm->opcion10 = (isset($this->prm->opcion10)) ? $this->prm->opcion10 : '';
        $this->prm->opcion11 = (isset($this->prm->opcion11)) ? $this->prm->opcion11 : '';

        switch ($this->prm->abc) {
            case 'a':
                /* Validacion para que no  se repita el número de contrato */
                $this->conectar();
                $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->numeroContrato];
                $this->setSql('
                    SELECT
                        gpo as gpo_id,ur as ur_id,ipu_numeroContrato
                    FROM ' . BD_SINPRES . '.cat_InversionPublica
                    WHERE gpo = ? and ur = ? and ipu_numeroContrato = ? ;
                ');

                $this->consultar($parametrosConsulta);
                $this->desconectar();
                if ($this->getNumeroRegistros() != 0) {
                    $this->mixScript('notify("El Contrato ' . $this->prm->numeroContrato . ' ya ha sido capturado ","error");');
                    return;
                }
                /* Validacion para que el proveedor que exista se encuentre en la base de datos */
                $parametrosConsulta = [$this->prm->rfc_id];
                $this->conectar();
                $this->setSql('
                    SELECT 
                        rfc_id,tcy_id 
                    FROM ' . BD_SINPRES . '.cat_Proveedores 
                    INNER JOIN cat_Proveedor USING (rfc_id)
                    WHERE rfc_id = ?; ');

                $this->consultar($parametrosConsulta);
                $this->desconectar();
                $prov = $this->extraer();
                if ($this->getNumeroRegistros() == 0) {
                    $this->mixScript('notify("El RFC ' . $this->prm->rfc_id . ' no existe en la Base de Datos","error");');
                    return;
                } else {
                    switch ($prov['tcy_id']) {
                        case 1: case 2:
                            $mensaje = '';
                            $conex = new AccesoDatos();
                            $parametrosConsulta = [$this->prm->rfc_id];
                            $conex->conectar();
                            $conex->setSql('
                                SELECT 
                                    rfc_id 
                                FROM ' . BD_SINPRES . '.cat_ProveedorSat  
                                WHERE rfc_id = ?;
                            ');

                            $conex->consultar($parametrosConsulta);
                            $conex->desconectar();
                            if ($conex->getNumeroRegistros() != 0) {
                                $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->rfc_id,$user];
                                $this->conectar();
                                $this->setSql('
                                INSERT INTO ' . BD_BITACORA . '.bit_Proveedores(gpo_id,ur_id,rfc_id,bit_observacion,usr_id,usr_fecha)
                                values (?,?,?,"Proveedor modificado por la UR",?,NOW())
                                ');
                                $this->consultar($parametrosConsulta);
                                $this->desconectar();

                            } else {
                                $mensaje = 'El RFC  ' . $this->prm->rfc_id . ' , No Existe en la lista de Contribuyentes con obligación del SAT (LCO)';
                                $this->mixScript('notify("' . $mensaje . '","warning"); ');

                            }
                            break;
                        default:
                            $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->rfc_id,$user];
                            $this->conectar();
                            $this->setSql('
                                INSERT INTO ' . BD_BITACORA . '.bit_Proveedores(gpo_id,ur_id,rfc_id,bit_observacion,usr_id,usr_fecha)
                                values (?,?,?,"Proveedor modificado por la UR",?,NOW())
                            ');
                            $this->consultar($parametrosConsulta);
                            $this->desconectar();
                            break;
                    }
                }
                $contrato = InversionPublica::abc($this->prm);
                $this->prm->abc = 'c';

                if (isset($this->prm->imss) && $this->prm->imss != '') {
                    $rfcProvedor = new stdClass();
                    $rfcProvedor->rfc_id = $this->prm->rfc_id;
                    $rfcProvedor->rfc_imss = $this->prm->imss;
                    $rfcProvedor->abc = $this->prm->abc;
                    Proveedores::abc($rfcProvedor);
                }

                $this->conectar();

                if ($this->prm->opcion1 != '') {
                    $parametrosConsulta = [$contrato,$this->prm->opcion1,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,1,?,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion2 != '') {
                    $parametrosConsulta = [$contrato,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,2,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion3 != '') {
                    $parametrosConsulta = [$contrato,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,3,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion4 != '') {
                    $parametrosConsulta = [$contrato, APP_EJERCICIO, (int)$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,4,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion5 != '') {
                    $parametrosConsulta = [$contrato,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,5,1,?,?)
                    ');

                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion7 != '') {
                    $parametrosConsulta = [$contrato,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,7,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion8 != '') {
                    $parametrosConsulta = [$contrato,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,8,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }
                if ($this->prm->opcion9 != '') {
                    $parametrosConsulta = [$contrato,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,9,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }
                /*Si vienen otras cuentas verificamos cuantas vienen*/
                if ($this->prm->opcion6 != '') {
                    $parametrosConsulta = [$contrato,$this->prm->retencionOtra,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_descripcion,eir_ejercicio,usr_id)
                        values (?,6,1,?,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                $this->desconectar();
                $this->conectar();
                $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->numeroContrato,$contrato,$user,$ip];
                $this->setSql('
                INSERT INTO ' . BD_BITACORA . '.bit_InversionPublica(gpo_id,ur_id,ipu_numeroContrato,ipu_id,bit_observacion,usr_id,usr_ip,usr_fecha)
                values (?,?,?,?,"Contrato generado por la  UR",?,INET_ATON(?),NOW())
                ');
                $this->consultar($parametrosConsulta);
                $this->desconectar();
                $this->mixScript('notify("El Contrato ' . $contrato . ' ha sido ingresado correctamente","success");
                    comunica("' . $this->pantalla . '",{
                        metodo:"Contratos",
                        "ipu_id":"' . $contrato . '",
                        "tarea":"' . $this->prm->tarea . '",
                        "vur_id":"' . $this->prm->vur_id . '",

                    });
                ');
                break;
            case 'c':
                $parametrosConsulta = [$this->prm->rfc_id];
                $this->conectar();
                $this->setSql('SELECT rfc_id,tcy_id FROM ' . BD_SINPRES . '.cat_Proveedores WHERE rfc_id = ?; ');
                $this->consultar($parametrosConsulta);
                $this->desconectar();
                $prov = $this->extraer();

                if ($this->getNumeroRegistros() == 0) {
                    $this->mixScript('notify("El RFC ' . $this->prm->rfc_id . ' no existe en la Base de Datos","error");');
                    return;
                } else {
                    switch ($prov['tcy_id']) {
                        case 1: case 2:
                            $mensaje = '';
                            $conex = new AccesoDatos();
                            $parametrosConsulta = [$this->prm->rfc_id];
                            $conex->conectar();
                            $conex->setSql('
                                SELECT 
                                    rfc_id from ' . BD_SINPRES . '.cat_ProveedorSat  
                                WHERE rfc_id = ?;
                            ');

                            $conex->consultar($parametrosConsulta);
                            $conex->desconectar();
                            if ($conex->getNumeroRegistros() > 0) {
                                $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->rfc_id,$user];
                                $this->conectar();
                                $this->setSql('
                                INSERT INTO ' . BD_BITACORA . '.bit_Proveedores(gpo_id,ur_id,rfc_id,bit_observacion,usr_id,usr_fecha)
                                values (?,?,?,"Proveedor modificado por la UR",?,NOW())
                                ');
                                $this->consultar($parametrosConsulta);
                                $this->desconectar();
                            } else {
                                $mensaje = 'El RFC  ' . $this->prm->rfc_id . ' , No Existe en la lista de Contribuyentes con obligación del SAT (LCO)';
                                $this->mixScript('notify("' . $mensaje . '","warning"); ');
                                return;
                            }
                            break;
                        default:
                            $this->conectar();
                            $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->rfc_id,$user];
                            $this->setSql('
                                INSERT INTO ' . BD_BITACORA . '.bit_Proveedores(gpo_id,ur_id,rfc_id,bit_observacion,usr_id,usr_fecha)
                                values (?,?,?,"Proveedor modificado por la UR",?,NOW())
                            ');
                            $this->consultar($parametrosConsulta);
                            $this->desconectar();
                            break;
                    }
                }

                InversionPublica::abc($this->prm);
                if (strcmp($this->prm->numeroContrato, $this->prm->numeroContratoNuevo) != 0) {
                    $this->conectar();
                    $parametrosConsulta = [$this->prm->numeroContratoNuevo,$this->prm->gpo_id,$this->prm->ur_id,$this->prm->ipu_id];
                    $this->setSql('
                        UPDATE ' . BD_SINPRES . '.cat_InversionPublica SET  ipu_numeroContrato = ?
                        WHERE gpo = ? and ur = ? and ipu_id = ?
                    ');
                    $this->consultar($parametrosConsulta);
                    $this->desconectar();
                }

                if (isset($this->prm->imss) && $this->prm->imss != '') {
                    $rfcProvedor = new stdClass();
                    $rfcProvedor->rfc_id = $this->prm->rfc_id;
                    $rfcProvedor->rfc_imss = $this->prm->imss;
                    $rfcProvedor->abc = $this->prm->abc;
                    Proveedores::abc($rfcProvedor);
                }

                /* Consultamos que retenciones ya están en el contrato*/

                $conexion2 = new AccesoDatos();
                $conexion2->conectar();
                $parametrosConsulta = [$this->prm->ipu_id];
                $conexion2->setSql('
                    SELECT tre_id,rcu_id 
                    FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion 
                    WHERE ipu_id = ? ');
                $conexion2->consultar($parametrosConsulta);
                $conexion2->desconectar();

                while ($edicion = $conexion2->extraer()) {
                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 1) {
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->conectar();
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 1
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 2) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 2
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 3) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 3
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 4) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 4
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 5) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE  ipu_id = ? and tre_id = 5
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 7) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 7
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 8) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 8
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }
                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 9) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 9
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 10) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 10
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 11) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 11
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }

                    if ($edicion['tre_id'] != '' && $edicion['tre_id'] == 6) {
                        $this->conectar();
                        $parametrosConsulta = [$this->prm->ipu_id];
                        $this->setSql('
                            DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                            WHERE ipu_id = ? and tre_id = 6
                        ');

                        $this->consultar($parametrosConsulta);
                        $this->desconectar();
                    }
                }

                /* Siempre insertamos los registros de las cuentas que vengan */
                $this->conectar();
                if ($this->prm->opcion1 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,$this->prm->opcion1,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,1,?,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }
                if ($this->prm->opcion2 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,2,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion3 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,3,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion4 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,4,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion5 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,5,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion7 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,7,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion8 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,8,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion9 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,9,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion10 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,10,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion11 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_ejercicio,usr_id)
                        values (?,11,1,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                if ($this->prm->opcion6 != '') {
                    $parametrosConsulta = [$this->prm->ipu_id,$this->prm->retencionOtra,APP_EJERCICIO,$user];
                    $this->setSql('
                        INSERT INTO ' . BD_SINPRES . '.enc_InversionPublicaRetencion(ipu_id,tre_id,rcu_id,eir_descripcion,eir_ejercicio,usr_id)
                        values (?,6,1,?,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                }

                $this->desconectar();
                $this->conectar();
                $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->numeroContratoNuevo,$this->prm->ipu_id,$user,$ip];
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_InversionPublica(gpo_id,ur_id,ipu_numeroContrato,ipu_id,bit_observacion,usr_id,usr_ip,usr_fecha)
                    values (?,?,?,?,"Contrato modificado por la  UR",?,INET_ATON(?),NOW())
                ');
                $this->consultar($parametrosConsulta);
                $this->desconectar();

                $this->mixScript('notify("El Contrato  ' . $this->prm->ipu_id . ' Se actualizó correctamente","success");
                    comunica("' . $this->pantalla . '",{
                        metodo:"Contratos",
                        "ipu_id":"' . $this->prm->ipu_id . '",
                        "tarea":"' . $this->prm->tarea . '",
                        "vur_id":"' . $this->prm->vur_id . '"
                    });
                ');
                break;

            case 'b':
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $parametrosConsulta = [$this->prm->ipu_id];
                $conexion->setSql('
                    SELECT 
                        tre_id,rcu_id 
                    FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion 
                    WHERE ipu_id = ? ');
                $conexion->consultar($parametrosConsulta);
                $conexion->desconectar();

                if ($conexion->getNumeroRegistros() != 0) {
                    $this->conectar();
                    $parametrosConsulta = [$this->prm->ipu_id];
                    $this->setSql('
                        DELETE FROM ' . BD_SINPRES . '.enc_InversionPublicaRetencion
                        WHERE ipu_id = ?
                    ');

                    $this->consultar($parametrosConsulta);
                    $this->desconectar();
                }

                InversionPublica::abc($this->prm);

                $this->conectar();
                $parametrosConsulta = [$this->prm->gpo_id,$this->prm->ur_id,$this->prm->numeroContrato,$this->prm->ipu_id,$user,$ip];
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_InversionPublica(gpo_id,ur_id,ipu_numeroContrato,ipu_id,bit_observacion,usr_id,usr_ip,usr_fecha)
                    values (?,?,?,?,"Contrato eliminado por la  UR",?,INET_ATON(?),NOW())
                ');
                $this->consultar($parametrosConsulta);
                $this->desconectar();

                $this->mixScript('notify("El Contrato  ' . $this->prm->ipu_id . ' ha sido eliminado correctamente ","succes");
                    comunica("' . $this->pantalla . '",{
                        metodo:"Contratos",
                        "ipu_id":"' . $this->prm->ipu_id . '",
                        "tarea":"' . $this->prm->tarea . '",
                        "vur_id":"' . $this->prm->vur_id . '"
                    });
                ');
                break;
        }
    }

    private function CargaMunicipio()
    {
        $this->setContenedor('tr_municipio');
        $abc = '';
        $rfc = new Proveedores();
        if (!empty($this->prm->rfc_id)) {
            $rfc->setRfc_id($this->prm->rfc_id);
            $rfc->select();
            $abc = 'c';
        } else {
            $rfc->setRfc_id(0);
            $abc = 'a';
        }
        $mun_id = new stdClass();
        $parametrosConsulta = [$this->prm->edo_id];
        $mun_id->sql = 'SELECT mun_id,mun_nombre FROM ' . BD_SINPRES . '.cat_Municipios WHERE edo_id = ? AND mun_id  !=  0';
        $mun_id->id = 'mun_id';
        $mun_id->width = '400px';
        $mun_id->label = 'Selecciona Municipio/Delegación';
        $mun_id->enRow = 1;
        $mun_id->params = $parametrosConsulta;
        $mun_id->selecciona = $rfc->getMun_id();
        $this->recargaCombo($mun_id);
    }

    private function ObraActividad()
    {
        $this->prm->ipu_numeroContrato = (isset($this->prm->ipu_numeroContrato)) ? $this->prm->ipu_numeroContrato : '';
        $this->prm->ipu_id = (isset($this->prm->ipu_id)) ? $this->prm->ipu_id : '';
        $this->prm->epg_id = (isset($this->prm->epg_id)) ? $this->prm->epg_id : '';
        $this->prm->uur_id = (isset($this->prm->uur_id)) ? $this->prm->uur_id : '';
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $radios = '';
        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->name = 'ue';
        $ue->maxlength = 3;
        $ue->class = 'borrar';
        $ue->placeholder = 'UE';
        $ue->enDiv = true;

        $prog = new stdClass();
        $prog->id = 'prog';
        $prog->maxlength = 3;
        $prog->name = 'prog';
        $prog->class = 'borrar';
        $prog->placeholder = 'Programa';
        $prog->enDiv = true;

        $subprog = new stdClass();
        $subprog->id = 'subprog';
        $subprog->maxlength = 2;
        $subprog->name = 'subprog';
        $subprog->class = 'borrar';
        $subprog->placeholder = 'SP';
        $subprog->enDiv = true;

        $py = new stdClass();
        $py->id = 'py';
        $py->maxlength = 3;
        $py->name = 'py';
        $py->class = 'borrar';
        $py->placeholder = 'PY';
        $py->enDiv = true;

        $oa = new stdClass();
        $oa->maxlength = 3;
        $oa->id = 'oa';
        $oa->name = 'oa';
        $oa->class = 'borrar';
        $oa->placeholder = 'OA';
        $oa->enDiv = true;

        $concepto = new stdClass();
        $concepto->id = 'concepto_obra';
        $concepto->name = 'concepto';
        $concepto->class = 'borrar';
        $concepto->placeholder = 'Concepto de la Obra';
        $concepto->enDiv = true;

        $borrar = new stdClass();
        $borrar->class = 'btn btn-outline-warning me-1';
        $borrar->value = 'Limpiar';
        $borrar->id = 'borrar';
        $borrar->icon = 'trash me-2';

        $buscar = new stdClass();
        $buscar->id = 'buscar';
        $buscar->class = 'btn btn-outline-secondary me-1';
        $buscar->value = 'Buscar';
        $buscar->text = 'Buscar';
        $buscar->icon = 'search me-2';

        $radios .= ' 
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-2 col-xs-2" style="color:#0431B4">
                    ' . $this->ponInput($ue) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="color:#0431B4">
                    ' . $this->ponInput($prog) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="color:#0431B4">
                    ' . $this->ponInput($subprog) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="color:#0431B4">
                    ' . $this->ponInput($py) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="color:#0431B4">
                    ' . $this->ponInput($oa) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-12 col-xs-12" style="color:#0431B4">
                    ' . $this->ponInput($concepto) . '
                </fieldset>
            </div>
            <div class="col-md-12 col-xs-12">
                <fieldset class=" d-flex" style="color:#0431B4">
                    ' . $this->ponBoton($buscar) . ' ' . $this->ponBoton($borrar) . '
                </fieldset>
            </div>
            <br>
        ';

        $tabla = new stdClass();
        $tabla->tools = $radios;
        $tabla->titulo = 'LISTADO DE OBRAS';
        $tabla->id = 'listObra';
        $tr = [];

        $th = new stdClass();
        $th->texto = 'ASIGNAR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ENCUADRE PROG..';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OBRA NOMBRE';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaObraActividad';
        $tabla->comunica->ipu_numeroContrato = $this->prm->ipu_numeroContrato;
        $tabla->comunica->uur_id = $this->prm->uur_id;
        $tabla->comunica->ipu_id = $this->prm->ipu_id;
        $tabla->comunica->tarea = $this->prm->tarea;
        $tabla->comunica->tarea = $this->prm->tarea;
        $tabla->comunica->ue = '{$("#ue").val()}';
        $tabla->comunica->prog = '{$("#prog").val()}';
        $tabla->comunica->subprog = '{$("#subprog").val()}';
        $tabla->comunica->py = '{$("#py").val()}';
        $tabla->comunica->oa = '{$("#oa").val()}';
        $tabla->comunica->concepto = '{$("#concepto_obra").val()}';
        $tabla->numFilas = 150;
        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'check';
        $td->class = 'oficio';
        $td->attr = new stdClass();
        $td->attr->epg_id = 'epg_id';
        $td->attr->oa_id = 'oa_id';
        $td->attr->ecf_id = 'ecf_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'programatico';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($tabla->cuerpo, $td);

        if ($this->prm->eip_id != 1) {
            $this->mixHtml('
                <div class="row col-md-12 col-xs-12 ">
                    <fieldset class="col-md-6 col-xs-6">
                        <div  id="listaObras">
                            ' . $this->ObraActividadAgregadas() . '
                        </div>
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->wrPonTabla($tabla) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="oficios" style="z-index:999999;">
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="tipo" style="z-index:999999;">
                    </fieldset>
                </div>
            ');

            $this->mixScript('
                $("#listaObras").ready(function(){
                    ' . $this->wrLlamaLlenaTabla('listObra') . '
                });
                $("#buscar").click(function(){
                    ' . $this->wrLlamaLlenaTabla('listObra') . '
                });
                $("#borrar").click(function(){
                    $(".borrar").val("");
                });
            ');
            $this->llenaObraActividad();
        } else {
            $this->mixScript('$("#modalDeTablatbl_Conceptos").remove();');
            $this->mixScript('$(".modal-backdrop").remove();');
            $this->mixScript('notify("Debes autorizar el Contrato para poder agregar Obras","error");');
        }
    }

    private function llenaObraActividad()
    {
        $encuadre = (isset($this->prm->epg_id)) ? $this->prm->epg_id : '';
        $this->prm->uur_id = (isset($this->prm->uur_id)) ? $this->prm->uur_id : '';
        $this->prm->ipu_numeroContrato = (isset($this->prm->ipu_numeroContrato)) ? $this->prm->ipu_numeroContrato : '';
        $partidas = $gastos = $wh = '';

        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $grupo = $_SESSION[_S]['clasAdm-gpo'];
            $ur = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $grupo = $_SESSION[_S]['gpo_id'];
            $ur = $_SESSION[_S]['ur_id'];
        }

        $wd = '';
        $ue = (isset($this->prm->ue)) ? $this->prm->ue : '';
        $prog = (isset($this->prm->prog)) ? $this->prm->prog : '';
        $subprog = (isset($this->prm->subprog)) ? $this->prm->subprog : '';
        $py = (isset($this->prm->py)) ? $this->prm->py : '';
        $oa = (isset($this->prm->oa)) ? $this->prm->oa : '';
        $cvefin = (isset($this->prm->cvefin)) ? $this->prm->cvefin : '';
        $concepto = (isset($this->prm->concepto)) ? $this->prm->concepto : '';

        $parametrosConsulta = [$grupo, (int)$ur];
        $wf = 'where gpo_id = ? and ur_id = ?';

        if ($ue != '') {
            array_push($parametrosConsulta, $ue);
            $wf .= ' and ue_id = ? ';
        }

        if ($prog != '') {
            array_push($parametrosConsulta, $prog);
            $wf .= ' and prg_id = ? ';
        }

        if ($subprog != '') {
            array_push($parametrosConsulta, $subprog);
            $wf .= ' and spr_id = ? ';
        }

        if ($py != '') {
            array_push($parametrosConsulta, $py);
            $wf .= ' and py_id = ? ';
        }

        if ($oa != '') {
            array_push($parametrosConsulta, $oa);
            $wf .= ' and oa_id = ? ';
        }

        if ($encuadre != '') {
            // Original condition: $wd =' and epg_id!='.$encuadre.'';
        }
        // Query added for get epg_id
        $this->conectar();
        $param = [$grupo, (int)$ur, $this->prm->ipu_id, $grupo, (int)$ur, $this->prm->ipu_id];
        $this->setSql(' 
            SELECT
                COUNT(cmp_id)>0 nCmp,gpo_id,ur_id,oa_nombre,CONCAT(gpo_id,ur_id, "-",oa.ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,
                ipu_id,epg_id,dur_id,dur_nombreOriginal,oa_id
            FROM  enc_Programatico
            INNER JOIN enc_InversionObra a USING(epg_id,gpo_id,ur_id)
            INNER JOIN cat_ObraActividad oa USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN ' . BD_SINPRES . '.cat_DocumentosUr USING (dur_id)
            LEFT JOIN (
                SELECT
                    gpo_id,ur_id,epg_id,ipu_id,cmp_id
                FROM scp_Compromiso
                WHERE gpo_id = ? AND ur_id = ? AND ipu_id = ? AND ecm_id != "C"
            )cmp using(gpo_id,ur_id,epg_id,ipu_id)
            WHERE gpo_id = ? AND ur_id = ? AND ipu_id = ?
            GROUP BY epg_id,gpo_id,ur_id,oa_nombre,programatico,ipu_id,epg_id,dur_id,dur_nombreOriginal,oa_id;
        ');

        $this->consultar($param);
        $this->desconectar();

        if ($concepto != '') {
            array_push($parametrosConsulta, "%$concepto%");
            $wf .= ' and oa_nombre LIKE ? ';
        }
        array_push($parametrosConsulta, $this->prm->ipu_id);
        while ($rows = $this->extraer()) {
            array_push($parametrosConsulta, $rows['epg_id']);
            $wd .= ' and epg_id != ?';
        }
        $this->conectar();
        $sql = '
            SELECT
                gpo_id,ur_id,CONCAT(gpo_id,ur_id, "-",ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,oa_id,
                epg_id,oa_nombre,pre.ecf_id,cfi_clave,cfi_nombre, SUM(pv.pre_vigente)-SUM(pc.pre_comprometido) as pre_vigente 
            FROM presupuesto pre
            INNER JOIN enc_Programatico USING(epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN enc_InversionObra a USING(gpo_id,ur_id,epg_id)
            INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) 
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
            INNER JOIN presupuestoVigente pv USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            INNER JOIN presupuestoComprometido pc USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
            ' . $wf . '
            AND toa_id=2
            AND ( (a.epg_id is null AND a.dur_id is null) OR ( a.ipu_id != ? ' . $wd . ' ))
            AND pv.pre_vigente - pc.pre_comprometido > 0 
            GROUP BY gpo_id,ur_id,epg_id,oa_id,epg_id,oa_nombre,pre.ecf_id,cfi_clave,cfi_nombre
        ';
        $this->setParams($parametrosConsulta);
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'listObra';

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","error");');
        }

        $this->mixScript('
            $(".oficio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"AgregarOficio",
                    ipu_numeroContrato:"' . $this->prm->ipu_numeroContrato . '",	
                    uur_id:"' . $this->prm->uur_id . '",
                    ipu_id:"' . $this->prm->ipu_id . '",
                    tarea:"' . $this->prm->tarea . '",
                    oa_id:$(this).attr("oa_id"),
                    epg_id:$(this).attr("epg_id")
                });
                return false;
            });
        ');
    }

    private function ClavesFinanciamiento()
    {
        $this->setContenedor('tipo');
        $tipoOficio = new StdClass();
        $tipoOficio->class = 'radio habilitar';
        $tipoOficio->titulo = '¿Oficio de Autorización por Clave de Financiamiento?';
        $tipoOficio->name = 'tipoOficio';
        $tipoOficio->values = [1 => 'SI',2 => 'NO'];
        $tipoOficio->cols = 3;
        $tipoOficio->required = true;
        $tipoOficio = $this->ponRadio($tipoOficio);

        $modal = '';
        $modal = '<div id="modal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="width: 45% !IMPORTANT;" data-backdrop="false">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true" >x</button>
                            <h4 class="modal-title" id="myModalLabel">Tipo de Vinculación para el Oficio de Autorización</h4>
                        </div>
                        <div class="modal-body">
                            <form id="Mensaje">
                            <div class="row col-md-12 col-xs-12">
                                <fieldset  class="col-md-12 col-xs-12" style="color:#FF2626">
                                    ' . $tipoOficio . '
                                </fieldset>
                            </div>
                            <br>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-default btn-sm cerrar" data-dismiss="modal" ><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>Cerrar</button>
                            <button type="button" class="btn btn-outline-success btn-sm continuar" ><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>Continuar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ';

        $this->mixHtml($modal);
        $this->mixScript('
            $("#modal").modal();

            $(".cerrar").click(function(){ 
                $("#modal").remove();
                $("body").removeClass("modal-open");
                $(".modal-backdrop").remove();
            });

            $(".continuar").click(function(){
                if ($("input[name=tipoOficio]").is(":checked")){
                    comunica("' . $this->pantalla . '",{
                        metodo:"AgregarOficio",
                        ipu_numeroContrato:"' . $this->prm->ipu_numeroContrato . '",
                        ipu_id:"' . $this->prm->ipu_id . '",
                        epg_id:"' . $this->prm->epg_id . '",
                        ecf_id:"' . $this->prm->ecf_id . '",
                        oa_id:"' . $this->prm->oa_id . '",
                        tipo:$("input[name=tipoOficio]:checked").val()
                    });
                } else {
                    alert("Tienes que seleccionar una opción");
                }
                $("#modal").remove();
                return false;
            });
        ');
    }

    private function AgregarOficio()
    {
        $this->setContenedor('oficios');
        $this->prm->ipu_numeroContrato = (isset($this->prm->ipu_numeroContrato)) ? $this->prm->ipu_numeroContrato : '';
        $this->prm->ipu_id = (isset($this->prm->ipu_id)) ? $this->prm->ipu_id : '';
        $this->prm->oa_id = (isset($this->prm->oa_id)) ? $this->prm->oa_id : '';
        $this->prm->epg_id = (isset($this->prm->epg_id)) ? $this->prm->epg_id : '';
        $this->prm->accion = (isset($this->prm->accion)) ? $this->prm->accion : 0 ;
        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $this->prm->uur_id = (isset($this->prm->uur_id)) ? $this->prm->uur_id : '';

        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $ur_id = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $gpo_id = $_SESSION[_S]['gpo_id'];
            $ur_id = $_SESSION[_S]['ur_id'];
        }

        $ejer_in = '';
        $vur_in = '';

        $ejer_in = ' AND vur_ejercicio  IN (2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026)' ;
        $parametrosConsulta = [$this->prm->uur_id];
        $c2 = new AccesoDatos();
        $c2->conectar();
        $c2->setSql('
            SELECT
                vur_id
            FROM  ' . BD_SINPRES . '.vin_UnidadResponsable 
            WHERE uur_id = ?
            ' . $ejer_in . '
        ');
        $c2->consultar($parametrosConsulta);

        $c2->desconectar();
        while ($row = $c2->extraer()) {
            $vur_in .= $row['vur_id'] . ',';
        }
        $vur_in = trim($vur_in, ',');
        if ($vur_in == '') {
            $vur_in = 0;
        }

        $using = $indur = $in = $where = $where2 = '';
        //$parametrosConsulta = [$gpo_id, $ur_id, $this->prm->epg_id];
        $wf = 'WHERE gpo_id = ? AND ur_id = ? AND epg_id = ? ';

        /* Funcion que me sirve para agregar oficios a claves de financiamiento una vez que la obra ya fue agregada al contrato */
        if ($this->prm->accion == 1) {
            /*Con esta consulta se restringe para que no se muestren las claves de financiamiento que ya tienen oficios y se restringe para
            que no se muestren los oficios que ya fueron utilizados
            */
            $encuadre = new AccesoDatos();
            $encuadre->conectar();
            $parametrosConsulta = [$gpo_id, $ur_id, $this->prm->epg_id, $this->prm->ipu_id];
            $encuadre->setSql('
                SELECT
                    ecf_id,dur_id
                FROM enc_InversionObra
                WHERE gpo_id = ? and ur_id = ? and epg_id = ? and ipu_id = ? ;
            ');
            $encuadre->consultar($parametrosConsulta);
            $encuadre->desconectar();

            $ultimo = array_pop($parametrosConsulta);
            while ($reg = $encuadre->extraer()) {
                array_push($parametrosConsulta, $reg['ecf_id']);
                $in .= '?,';
            }

            $in = trim($in, ',');

            if ($encuadre->getNumeroRegistros() > 0) {
                $where = ' AND pre.ecf_id NOT IN (' . $in . ')';
            }

            $sql = '
                SELECT
                    gpo_id,ur_id,CONCAT(gpo_id,ur_id, "-",ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,oa_id,
                    epg_id,oa_nombre,pre.ecf_id,cfi_clave,cfi_nombre, SUM(pv.pre_vigente)-(pc.pre_comprometido) as pre_vigente 
                FROM presupuesto pre
                INNER JOIN enc_Programatico USING(epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                LEFT JOIN enc_InversionObra a USING(gpo_id,ur_id,epg_id,ecf_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento2026 USING(cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) 
                INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
                INNER JOIN presupuestoVigente pv USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido pc USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                ' . $wf . '  and toa_id=2  
                AND pv.pre_vigente - pc.pre_comprometido > 0 ' . $where . '
                GROUP BY gpo_id,ur_id,oa_id,epg_id,cfi_clave,cfi_nombre,pre.ecf_id,cfi_clave,pre_vigente,pre_comprometido
            ';

            $conexion = new AccesoDatos();
            $conexion->conectar();

            $conexion->setSql($sql);
            $conexion->consultar($parametrosConsulta);
            $conexion->desconectar();
            $registros = $conexion->getNumeroRegistros();

        } else {
            $wd = '';
            $this->conectar();
            $parametrosConsulta2 = [$gpo_id, $ur_id, $this->prm->epg_id, $this->prm->ipu_id];
            $this->setSql('
                SELECT
                    dur_id from enc_InversionObra
                WHERE gpo_id = ? and ur_id = ? and epg_id = ? 
                AND ipu_id != ?
            ');
            $this->consultar($parametrosConsulta2);
            $this->desconectar();
            $numOficio = $this->extraer();
            if ($this->getNumeroRegistros()) {
                $using = ' ,ecf_id';
            }

            array_push($parametrosConsulta, $this->prm->ipu_id);
            $sql = '
                SELECT
                    gpo_id,ur_id,CONCAT(gpo_id,ur_id, "-",ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,oa_id,
                    epg_id,oa_nombre,pre.ecf_id,cfi_clave,cfi_nombre, SUM(pv.pre_vigente)-SUM(pc.pre_comprometido) as pre_vigente,dur_id 
                FROM presupuesto pre
                INNER JOIN enc_Programatico USING(epg_id,gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                LEFT JOIN enc_InversionObra a USING(gpo_id,ur_id,epg_id ' . $using . ')
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento2026 USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) 
                INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
                INNER JOIN presupuestoVigente pv USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
                INNER JOIN presupuestoComprometido pc USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,tcp_id)
                ' . $wf . '
                AND toa_id=2 
                AND ( (a.epg_id is null and a.dur_id is null) OR ( a.ipu_id != ? ' . $wd . ' )) 
                AND pv.pre_vigente - pc.pre_comprometido > 0 
                GROUP BY gpo_id,ur_id,programatico,oa_id,epg_id,oa_nombre,pre.ecf_id,cfi_clave,cfi_nombre,dur_id
            ';
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar($parametrosConsulta2);
            $conexion->desconectar();
            $registros = $conexion->getNumeroRegistros();
        }

        $oficio = new stdClass();
        $oficio->class = 'limpiar habilitar dur';
        $oficio->sql = '
            SELECT
                dur_id,dur_nombreOriginal,bga_id,tan_id
            FROM ' . BD_SINPRES . '.cat_DocumentosUr
            LEFT JOIN  ' . BD_SINPRES . '.enc_GarantiaDocumento USING(dur_id)
            WHERE vur_id in(' . $vur_in . ') AND  bga_id is null AND tan_id is null
        ';

        $oficio->enRow = 1;
        $cont = 0;
        if ($registros > 0) {
            $modal = '';
            $modal = '
                <div id="modal" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                    <div class="modal-dialog modal-lg" style="width: 75% !IMPORTANT;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close cerrar btn btn-outline-secondary" data-dismiss="modal" aria-hidden="true">x</button>
                                <h4 class="modal-title" id="myModalLabel">Claves de Financiamiento de la Obra</h4>
                            </div>
                            <div class="modal-body">
                                <form id="Mensaje">
                                    <legend>AGREGAR OBRA AL CONTRATO</legend><br>
                                    <div class="row col-md-12 col-xs-12">
                                        <fieldset  class="col-md-4 col-xs-4" style="color:#0431B4">
                                            <label>Obra ' . $this->prm->oa_id . ' </label>
                                        </fieldset>	
                                        <fieldset  class="col-md-4 col-xs-4" style="color:#0431B4">
                                            <label>id Contrato ' . $this->prm->ipu_id . ' </label>
                                        </fieldset>
                                        <fieldset  class="col-md-4 col-xs-4" style="color:#0431B4">
                                            <label>N° Contrato ' . $this->prm->ipu_numeroContrato . ' </label>
                                        </fieldset>	
                                    </div>
                                    <br>
            ';

            $modal .= '
                <div class="row table-responsive">
                    <fieldset class="col-md-12">
                        <table class="table align-middle table-striped table-bordered">
                            <tr>
                                <th colspan="4" style="text-align:center" class="info">CLAVES DE FINANCIAMIENTO</th>
                            </tr>
                            <tr>
                                <th style="text-align:center" class="success">CLAVE DE FINANCIAMIENTO</th>
                                <th style="text-align:center" class="success">NOMBRE</th>
                                <th style="text-align:center" class="success">OFICIO AUTORIZACIÓN</th>
                            </tr>
            ';
            while ($presupuesto = $conexion->extraer()) {
                if ($presupuesto['dur_id'] != '' && !is_null($presupuesto['dur_id'])) {
                    $oficio->selecciona = $presupuesto['dur_id'];
                    $this->prm->dur_id = $presupuesto['dur_id'];
                    $this->mixScript('$(".habilitar").attr("disabled","disabled"); ');
                }

                $oficio->id = 'dur_id' . $presupuesto['ecf_id'];
                $oficio->name = 'dur_id[]';
                $oficio->change = '
                    var seleccionados = [];
                    var id = $(this).prop("id");
                    let seleccion = $(this).find("option:selected").val();
                    $("select.dur option").removeAttr("disabled");

                    $("select.dur").each(function(){
                        seleccionados.push(seleccion);
                    });

                    $("select.dur").each(function(){
                        if ($(this).prop("id") == id)
                        {
                            for(v in seleccionados){
                            if ( seleccionados[v] != $("#"+id).val() )
                                $("#".id).find("option[value="+ seleccionados[v] +"]").attr("disabled","disabled");
                            }
                        } else {
                            if (!seleccion)
                                $(this).find("option:selected").attr("disabled","disabled");
                            else
                                $(this).find("option[value="+ seleccion +"]").attr("disabled","disabled");
                        }
                    });

                    $("select.dur").selectpicker("refresh");

                ';

                $modal .= '
                    <tr class="info col-md-6 col-xs-6">
                        <td style="text-align:center">' . $presupuesto['cfi_clave'] . '</td>
                        <td style="text-align:left">' . $presupuesto['cfi_nombre'] . '</td>
                        <td style="text-align:center" ecf_id ="' . $presupuesto['ecf_id'] . '">' . $this->ponCombo($oficio) . '</td>
                        
                    </tr>
                ';
                $cont++;
            }

            $modal .= '</div></table></fieldset><br>';

            $modal .= '
                <div class="row mb-2">
                    <fieldset  class="col-md-12 col-xs-12">
                        <div class="alert alert-info mb-2" role="alert">
                            <b>Recuerda que tienes que agregar Oficio de Autorización por Clave de Financiamiento y estos deben ser diferentes y deben existir en el Sistema</b> 
                        </div>
                    </fieldset>
                </div>
            ';
            $modal .= '
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-danger cerrar" data-dismiss="modal">Cerrar</button>
                                        <button type="button" class="btn btn-outline-primary vincular" >AgregarObra</button>
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
                $("#modal").on("shown.bs.modal",function(e){
                $(".modal-backdrop").remove();
                });
                $(".cerrar").click(function(){ 
                    $("#modal").remove();
                    $("body").removeClass("modal-open");
                    $(".modal-backdrop").remove();
                });

                $(".vincular").click(function(){
                    var arregloDur = [];
                    $("select.dur").each(function(){
                        arregloDur.push({
                            dur_id:$(this).val(),
                            ecf_id:$(this).parent().parent().attr("ecf_id"),
                            ipu_id:"' . $this->prm->ipu_id . '"
                        });
                    });

                    alertify.confirm("Confirmación","Estas seguro de agregar la obra  al Contrato ' . $this->prm->ipu_numeroContrato . ' ",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo:"GuardarObraActividad",
                                ipu_numeroContrato:"' . $this->prm->ipu_numeroContrato . '",	
                                ipu_id:"' . $this->prm->ipu_id . '",
                                epg_id:"' . $this->prm->epg_id . '",
                                claves:arregloDur,
                                tipo:"' . $this->prm->tipo . '",
                                uur_id:"' . $this->prm->uur_id . '",
                                tarea:"' . $this->prm->tarea . '"
                            });
                            $("#modal").remove(); 
                        },undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Aceptar", cancel: "Cancelar"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                    }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                    if (cancela) {
                        cancela.classList.remove("btn-danger");
                        cancela.classList.add("btn-outline-danger");
                    }
                });
            ');
        } else {
            $this->mixScript('alert("No hay Claves de Financiamiento adicionales para la Obra");');
            return false;
        }
    }

    private function GuardarObraActividad()
    {
        $contratosAut = '';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];

        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $ur_id = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $gpo_id = $_SESSION[_S]['gpo_id'];
            $ur_id = $_SESSION[_S]['ur_id'];
        }

        $this->prm->ipu_numeroContrato = (isset($this->prm->ipu_numeroContrato) && $this->prm->ipu_numeroContrato != '') ? $this->prm->ipu_numeroContrato : '';
        $this->prm->ipu_id = (isset($this->prm->ipu_id) && $this->prm->ipu_id != '') ? $this->prm->ipu_id : '';
        $this->prm->dur_id = (isset($this->prm->dur_id) && $this->prm->dur_id != '') ? $this->prm->dur_id : '';
        $this->prm->epg_id = (isset($this->prm->epg_id) && $this->prm->epg_id != '') ? $this->prm->epg_id : '';
        $this->prm->ecf_id = (isset($this->prm->ecf_id) && $this->prm->ecf_id != '') ? $this->prm->ecf_id : null;
        $this->prm->uur_id = (isset($this->prm->uur_id) && $this->prm->uur_id != '') ? $this->prm->uur_id : '';
        $this->prm->tarea = (isset($this->prm->tarea) && $this->prm->tarea != '') ? $this->prm->tarea : '';

        $parametrosConsulta = [];
        if (count($this->prm->claves) > 0 && $this->prm->claves != '') {
            foreach ($this->prm->claves as $key => $val) {
                $contratosAut .= $val->ipu_id . ',';
                if ($val->dur_id != '') {
                    $parametrosConsulta = [$gpo_id, $ur_id, $this->prm->ipu_id, $this->prm->epg_id, $val->dur_id, $val->ecf_id, $user];
                    $this->conectar();
                    $this->setSql('
                        INSERT INTO ' . BD_CTRL . '.enc_InversionObra (gpo_id,ur_id,ipu_id,epg_id,dur_id,ecf_id,usr_id)
                        values (?,?,?,?,?,?,?)
                    ');
                    $this->consultar($parametrosConsulta);
                    $this->desconectar();
                }
            }
        } else {
            $this->mixScript(' alert("No hay oficios de Autorización");  ');
            return false;
        }
        $this->llenaObraActividad();
        $this->llenaObraActividadAgregadas();
    }

    private function ObraActividadAgregadas()
    {
        $radios = '';

        $this->prm->cg_cuarto = (isset($this->prm->cg_cuarto)) ? $this->prm->cg_cuarto : '';
        $this->prm->cuarto = (isset($this->prm->cuarto)) ? $this->prm->cuarto : '';
        $this->prm->cmp_id = (isset($this->prm->cmp_id)) ? $this->prm->cmp_id : '';
        $this->prm->uur_id = (isset($this->prm->uur_id)) ? $this->prm->uur_id : '';
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';

        $separar = new stdClass();
        $separar->id = 'separar';
        $separar->name = 'separar';
        $separar->class = 'me-1';
        $separar->value = 'Separar';
        $separar->color = 'rojo';
        $separar->icon = 'solid fa-xmark me-2';

        $tabla = new stdClass();
        $tabla->tools = $radios;
        $tabla->titulo = 'LISTADO DE OBRAS AGREGADAS AL CONTRATO';
        $tabla->id = 'listObra2';
        $tr = [];

        $th = new stdClass();
        $th->texto = 'SEPARAR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OFICIOS';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ENCUADRE PROG.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OBRA NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OFICIO';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaObraActividadAgregadas';
        $tabla->comunica->tarea = $this->prm->tarea;
        $tabla->comunica->ipu_id = $this->prm->ipu_id;
        $tabla->comunica->uur_id = $this->prm->uur_id;
        $tabla->numFilas = 100;
        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'nCmp';
        $td->casos->valor = '0' ;

        $td->casos->verdadero = new stdClass() ;
        $td->casos->verdadero->tipo = 'check';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->ipu_id = 'ipu_id';
        $td->casos->verdadero->attr->ipu_numeroContrato = 'ipu_numeroContrato';
        $td->casos->verdadero->attr->epg_id = 'epg_id';
        $td->casos->verdadero->attr->dur_id = 'dur_id';
        $td->casos->verdadero->attr->oa_id = 'oa_id';
        $td->casos->verdadero->class = 'seleccionar';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        $td->color = 'rojo';
        $td->class = 'detalle';
        $td->attr = new stdClass();
        $td->attr->ipu_id = 'ipu_id';
        $td->attr->ipu_numeroContrato = 'ipu_numeroContrato';
        $td->attr->epg_id = 'epg_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'programatico';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'dur_nombreOriginal';
        array_push($tabla->cuerpo, $td);

        $html = '<div class="mb-4">' . $this->wrPonTabla($tabla) . '</div>';
        $html .= $this->ponBoton($separar);

        $this->mixScript('
            $("#separar").click(function() {
                var carga = new Array();
                $(".seleccionar:checked").each(function(){
                    var reg = [$(this).attr("ipu_id"),$(this).attr("ipu_numeroContrato"),$(this).attr("epg_id"),$(this).attr("dur_id"),$(this).attr("oa_id")];		
                    carga.push( reg );
                });
                if (carga.length > 0){
                    comunica("' . $this->pantalla . '",{
                        metodo:"SeparaObraActividad",
                        obras:carga,
                        ipu_id:"' . $this->prm->ipu_id . '",	
                        tarea:"' . $this->prm->tarea . '",
                        uur_id:"' . $this->prm->uur_id . '"
                    });

                    $(".habilitar").attr("disabled","disabled");
                    // $("#cveFin_id").add("lectura").css("display","none");	
                }
                else
                notify( "Debes seleccionar un número de Obra", "error");

                return false;
            });

            $("#SelectAll").click(function(){
            if ($(this).is(":checked")){
            $(".seleccionar").prop( "checked", true );
            } else {
            $(".seleccionar").prop( "checked", false );
            }
            });

        ');
        $this->llenaObraActividadAgregadas();
        return $html;
    }

    private function llenaObraActividadAgregadas()
    {
        $this->prm->uur_id = (isset($this->prm->uur_id)) ? $this->prm->uur_id : '';
        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $grupo = $_SESSION[_S]['clasAdm-gpo'];
            $ur = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $grupo = $_SESSION[_S]['gpo_id'];
            $ur = $_SESSION[_S]['ur_id'];
        }
        $partidas = $gastos = $wh = '';

        $parametrosConsulta = [$grupo, $ur, $this->prm->ipu_id, $grupo, $ur, $this->prm->ipu_id];
        $this->conectar();
        $sql = '
            SELECT
                COUNT(cmp_id)>0 nCmp,gpo_id,ur_id,oa_nombre,CONCAT(gpo_id,ur_id, "-",oa.ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,
                ipu_id,epg_id,dur_id,dur_nombreOriginal,oa_id
            FROM  enc_Programatico
            INNER JOIN enc_InversionObra a USING(epg_id,gpo_id,ur_id)
            INNER JOIN cat_ObraActividad oa USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN ' . BD_SINPRES . '.cat_DocumentosUr USING (dur_id)
            LEFT JOIN (
                SELECT 
                    gpo_id,ur_id,epg_id,ipu_id,cmp_id
                FROM scp_Compromiso
                WHERE gpo_id = ? AND ur_id = ? AND ipu_id = ? AND ecm_id != "C"	
            )cmp using(gpo_id,ur_id,epg_id,ipu_id)
            WHERE gpo_id = ? AND ur_id = ? AND ipu_id = ? 
            GROUP BY epg_id,gpo_id,ur_id,oa_nombre,programatico,ipu_id,epg_id,dur_id,dur_nombreOriginal,oa_id;
        ';

        $this->setParams($parametrosConsulta);
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'listObra2';
        $nav->nf = 100;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","error");');
        }

        $this->mixScript('
            $(".detalle").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"AgregarOficio",
                    uur_id:"' . $this->prm->uur_id . '",	
                    ipu_id:"' . $this->prm->ipu_id . '",
                    oa_id:$(this).attr("oa_id"),
                    epg_id:$(this).attr("epg_id"),
                    accion:1
                });
                return false;
            });
        ');
    }

    private function SeparaObraActividad()
    {
        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $ur_id = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $gpo_id = $_SESSION[_S]['gpo_id'];
            $ur_id = $_SESSION[_S]['ur_id'];
        }

        $obrasS = '';
        $ejerc = '';
        switch ($this->prm->tarea) {
            case 1:  $ejerc = ' AND ipu_ejercicio=2015';
                break;
            case 2:  $ejerc = ' AND ipu_ejercicio=2016';
                break;
            case 3:  $ejerc = ' AND ipu_ejercicio=2017';
                break;
            case 4:  $ejerc = ' AND ipu_ejercicio=2018';
                break;
            case 5:  $ejerc = ' AND ipu_ejercicio=2019';
                break;
            case 6:  $ejerc = ' AND ipu_ejercicio=2020';
                break;
            case 7:  $ejerc = ' AND ipu_ejercicio=2021';
                break;
            case 8:  $ejerc = ' AND ipu_ejercicio=2022';
                break;
            case 9:  $ejerc = ' AND ipu_ejercicio=2023';
                break;
            case 10:  $ejerc = ' AND ipu_ejercicio=2024';
                break;
            case 11:  $ejerc = ' AND ipu_ejercicio=2025';
                break;
            case 12:  $ejerc = ' AND ipu_ejercicio=2026';
                break;
        }

        if (count($this->prm->obras) > 0 && $this->prm->obras != '') {
            foreach ($this->prm->obras as $key => $obras) {
                $this->conectar();
                $parametrosConsulta = [$gpo_id, $ur_id, $obras[0], $obras[2],$gpo_id, $ur_id, $obras[0], $obras[2]];
                $this->setSql('
                    SELECT 
                        gpo,ur 
                    FROM ' . BD_SINPRES . '.cat_InversionPublica
                    INNER JOIN scp_Compromiso using(ipu_id) 
                    WHERE gpo = ? AND  ur = ? and ipu_id = ?  and ecm_id != "C" AND epg_id = ? ' . $ejerc . ' 
                    UNION ALL
                    SELECT 
                        i.gpo,i.ur
                    FROM ' . BD_SINPRES . '.cat_InversionPublica i
                    INNER JOIN enc_InversionObra enc USING(ipu_id)
                    LEFT JOIN scp_Afectacion af ON enc.gpo_id=af.gpo_id AND enc.ur_id=af.ur_id AND enc.ipu_id=af.afe_ipu_id AND  enc.epg_id=af.epg_id 
                    WHERE i.gpo = ? and i.ur = ? and ipu_id = ? AND eaf_id!="C" AND tcl_id="H" AND month(afe_fechaElaboracion)>3 AND enc.epg_id = ? ' . $ejerc . ' ;
                ');

                $this->consultar($parametrosConsulta);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $this->mixScript('alert("No puedes separar el contrato ' . $obras[1] . ' ya tiene compromisos  o Afectaciones  ");');
                    return false;
                }

                $obrasS .= $obras[4] . ',';
                $this->conectar();
                $parametrosConsulta = [$gpo_id, $ur_id, $obras[0], $obras[2], $obras[3]];
                $this->setSql('
                    DELETE FROM enc_InversionObra
                    WHERE gpo_id = ? and  ur_id = ? and ipu_id = ? and epg_id = ? and dur_id = ?
                ');
                $this->consultar($parametrosConsulta);
                $this->desconectar();
            }
            $this->mixScript('alert( "Las Obra (s) ' . $obrasS . ' ha (n)  sido sepárada (s)  correctamente ");');
        }
        $this->llenaObraActividad();
        $this->llenaObraActividadAgregadas();
    }

    private function AutorizarObra()
    {
        $contratosAut = '';
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $parametrosConsulta = [];
        if (count($this->prm->contratos) > 0 && $this->prm->contratos != '') {
            foreach ($this->prm->contratos as $key => $val) {
                $contratosAut .= $val->ipu_id . ',';
                $this->conectar();
                $parametrosConsulta = [$val->ipu_id, $val->gpo_id, $val->ur_id];
                $this->setSql('
                    UPDATE ' . BD_SINPRES . '.cat_InversionPublica SET eip_id=2 
                    WHERE ipu_id = ? AND eip_id = 1 AND gpo = ? AND ur = ?
                ');
                $this->consultar($parametrosConsulta);
                $this->desconectar();

                $parametrosConsulta = [$val->gpo_id, $val->ur_id, $val->ipu_numeroContrato, $val->ipu_id, $user, $ip];
                $this->conectar();
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_InversionPublica(gpo_id,ur_id,ipu_numeroContrato,ipu_id,usr_id,usr_ip,bit_observacion,usr_fecha)
                    values (?,?,?,?,?,INET_ATON(?),"Contrato autorizado por la  UR",NOW())
                ');
                $this->consultar($parametrosConsulta);
                $this->desconectar();
            }
        }

        $this->mixScript('
            notify("Contrato (s)  ' . $contratosAut . ' autorizado (s)  correctamente ","success");
            comunica("' . $this->pantalla . '",{
                metodo:"Contratos",
                "tarea":"' . $this->prm->tarea . '",
                "vur_id":"' . $this->prm->vur_id . '"
                });
        ');
    }

    private function DesAutorizarObra()
    {
        $user = $_SESSION[_S]['usr_id'];

        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $ur_id = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $gpo_id = $_SESSION[_S]['gpo_id'];
            $ur_id = $_SESSION[_S]['ur_id'];
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $this->prm->ipu_numeroContrato = (isset($this->prm->numeroContrato)) ? $this->prm->numeroContrato : '';
        $this->prm->ipu_id = (isset($this->prm->ipu_id)) ? $this->prm->ipu_id : '';
        $this->prm->vur_id = (isset($this->prm->vur_id)) ? $this->prm->vur_id : '';
        $this->prm->tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';

        if ($this->prm->ipu_numeroContrato != '' && $this->prm->ipu_id != '') {
            $this->conectar();
            $parametrosConsulta = [$gpo_id, $ur_id, $this->prm->ipu_id];
            $this->setSql('
                UPDATE ' . BD_SINPRES . '.cat_InversionPublica SET eip_id=1 
                WHERE gpo = ? AND ur = ? AND ipu_id = ? AND eip_id=2
            ');

            $this->consultar($parametrosConsulta);
            $this->desconectar();

            $this->conectar();
            $parametrosConsulta = [$gpo_id, $ur_id, $this->prm->ipu_numeroContrato, $this->prm->ipu_id, $user, $ip];
            $this->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_InversionPublica(gpo_id,ur_id,ipu_numeroContrato,ipu_id,bit_observacion,usr_id,usr_ip,usr_fecha)
            values (?,?,?,?,"Contrato Desaautorizado por la  UR",?,INET_ATON(?),NOW())
            ');
            $this->consultar($parametrosConsulta);
            $this->desconectar();

            $this->mixScript('
                notify("El contrato ' . $this->prm->ipu_id . ' ha sido Desautorizado correctamente ","success");
                comunica("' . $this->pantalla . '",{
                    metodo:"Contratos",
                    "vur_id":"' . $this->prm->vur_id . '",
                    "tarea":"' . $this->prm->tarea . '"	
                });
            ');
        } else {
            $this->mixScript('notify("error al desautorizar el contrato","error"); ');
        }
    }

    private function ValidarContrato()
    {
        if (isset($_SESSION[_S]['prm']['S']['2']['8']['72'])) {
            $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
            $ur_id = $_SESSION[_S]['clasAdm-ur'];
        } else {
            $gpo_id = $_SESSION[_S]['gpo_id'];
            $ur_id = $_SESSION[_S]['ur_id'];
        }

        $this->prm->ipu_numeroContrato = (isset($this->prm->ipu_numeroContrato)) ? $this->prm->ipu_numeroContrato : '';

        if ($this->prm->ipu_numeroContrato != '') {
            $this->conectar();
            $parametrosConsulta = [$gpo_id, $ur_id, $this->prm->ipu_numeroContrato];
            $this->setSql('
                SELECT 
                    gpo,ur,ipu_numeroContrato  
                FROM ' . BD_SINPRES . '.cat_InversionPublica 
                WHERE gpo = ? AND ur = ? AND ipu_numeroContrato = ? ;
            ');

            $this->consultar($parametrosConsulta);
            $this->desconectar();
            if ($this->getNumeroRegistros() != 0) {
                $this->mixScript('notify("El Contrato ' . $this->prm->ipu_numeroContrato . ' ya ha sido capturado ","error");');
            } else {
                $this->mixScript('$("#NumContrato").addClass("success"); ');
            }
        }
    }

    private function Finiquitar()
    {
        $this->setContenedor('div_contratos');
        $modal = '';
        $modal = '
            <div id="modalFiniquito" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" aria-modal = "true" >
                <div class="modal-dialog" style="width: 45% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Finiquitar Contrato </h4>
                            <button type="button" class="cerrar" data-dismiss="modalFiniquito" aria-hidden="true">x</button>
                        </div>
                        <div class="modal-body">
                            <form id="Mensaje">
                                <legend>Contrato ' . $this->prm->ipu_numeroContrato . '</legend><br>	
                            <br>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-success finiquitar" >Finiquitar Contrato</button>
                                <button type="button" class="btn btn-outline-secondary cerrar" data-dismiss="modal">Cerrar</button>
                            </div>
                            </form>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            </div>
        ';

        $this->mixHtml($modal);
        $this->mixScript('
            $("#modalFiniquito").modal();
            $(".cerrar").click(function(){ 
                $("#modalFiniquito").remove();
            });

            $(".finiquitar").click(function(){
                
                $("#modalFiniquito").remove();
                alertify.confirm(
                    "FINIQUITAR CONTRATO",
                    "¿ Confirma que desea Finiquitar  el Contrato ' . $this->prm->ipu_numeroContrato . ' con el ID  ' . $this->prm->ipu_id . ' <h5 style=\"display:inline\"></h5> ? </br> Recuerde que una vez finiquitado ya no se pueden generar CLC´s ",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo: "FiniquitarContrato",
                            "ipu_id":"' . $this->prm->ipu_id . '",
                            "ipu_numeroContrato":"' . $this->prm->ipu_numeroContrato . '",
                            "vur_id":"' . $this->prm->vur_id . '",
                            "tarea":"' . $this->prm->tarea . '"
                        });
                    },undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Aceptar", cancel: "Cancelar Operación"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-outline-success");
                    }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                    if (cancela) {
                        cancela.classList.remove("btn-danger");
                        cancela.classList.add("btn-outline-danger");
                    }
                return false;
            });

            $("#btnRegresarIndex").click(function(){
                $("nav").menusinpres("clickMenu");
            });
        ');
    }

    private function FiniquitarContrato()
    {
        $gpo_id = $_SESSION[_S]['gpo_id'];
        $ur_id = $_SESSION[_S]['ur_id'];
        $this->prm->ipu_numeroContrato = (isset($this->prm->ipu_numeroContrato)) ? $this->prm->ipu_numeroContrato : '';

        if ($this->prm->ipu_id != '') {
            $this->conectar();
            $parametrosConsulta = [$gpo_id, $ur_id, $this->prm->ipu_id];
            $this->setSql('
                SELECT 
                    eip_id 
                FROM ' . BD_SINPRES . '.cat_InversionPublica 
                WHERE gpo = ? and ur = ?
                AND ipu_id = ? ;
            ');

            $this->consultar($parametrosConsulta);
            $this->desconectar();
            $con = $this->extraer();
            $alert = '';
            if ($this->getNumeroRegistros() != 0) {
                if ($con['eip_id'] == 2) {
                    $this->conectar();
                    $parametrosConsulta = [$gpo_id, $ur_id, $this->prm->ipu_numeroContrato, $this->prm->ipu_id];
                    $this->setSql('
                        UPDATE ' . BD_SINPRES . '.cat_InversionPublica SET eip_id = 3 
                        WHERE gpo = ? and ur = ? 
                        AND ipu_numeroContrato = ? 
                        AND ipu_id = ? 
                        AND eip_id = 2
                    ');
                    $this->consultar($parametrosConsulta);
                    $this->desconectar();
                    $this->mixScript('
                        notify("El Contrato ' . $this->prm->ipu_numeroContrato . ' ha sido Finiquitado ","success");
                        comunica("scp/afectaciones/ControladorRegistroObra.php",{
                            metodo:"Contratos",
                            tarea:"' . $this->prm->tarea . '"
                        });
                    ');
                } else {
                    switch ($con['eip_id']) {
                        case 1:
                            $alert = ' debe ser Autorizado';
                            break;
                        case 3:
                            $alert = 'ya ha sido finiquitado por la UR';
                            break;
                        case 4:
                            $alert = 'ya ha sido rechazado su finiquito por Egresos, favor de comunicarse con Andrea a la extensión 23650 ';
                            break;
                        case 5:
                            $alert = 'ha sido validado su finiquito por Egresos.';
                            break;
                    }
                    $this->mixScript('notify("El Contrato ' . $this->prm->ipu_numeroContrato . ' ' . $alert . '  ","error");');
                }
            } else {
                $this->mixScript('notify("El Contrato ' . $this->prm->ipu_numeroContrato . ' No existe ","error");');
            }
        }
    }
}

if (isset($_SESSION[_S]['prm']['S']['2']['8']['64']) || isset($_SESSION[_S]['prm']['S']['2']['8']['72']) || $_SESSION[_S]['prm']['S']['2']['8']['73']) {
    new ControladorRegistroObra();
} else {
    WebResponse::sinPermisos();
}
