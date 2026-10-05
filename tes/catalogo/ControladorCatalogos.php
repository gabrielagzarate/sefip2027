<?php

//ingres@s
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/EntidadCuentaBancariaSefin.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/vinCuentaSap.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/SefinPlanCuentas.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/EntidadClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/PlanCuentas.php';

class ControladorCatalogos extends WebResponse
{
    protected $lectura;
    protected $escritura;
    protected $validar;
    protected $prms;
    protected $prm2;
    protected $prm3;
    protected $prm4;
    protected $prm5;
    protected $prm6;
    protected $prm7;
    protected $up;

    public function __construct()
    {
        parent::__construct();
        $this->lectura = tienePermiso('T.6.1.1');
        $this->escritura = tienePermiso('T.6.1.2');
        $this->validar = tienePermiso('T.6.1.3');

        switch ($this->metodo) {
            case 'llenaTabla':
                $this->llenaTabla();
                break;
            case 'pintaNuevaCuentaBancaria':
                $this->pintaNuevaCuentaBancaria();
                break;
            case 'insertar':
                $this->insertar();
                break;
            case 'generaInversionSap':
                $this->generaInversionSap();
                break;
            case 'generaInteres':
                $this->generaInteres();
                break;
            case 'agrega':
                $this->agrega();
                break;
            case 'autoriza':
                $this->autoriza();
                break;
            case 'rechaza':
                $this->rechaza();
                break;
            case 'edita':
                $this->edita();
                break;
            case 'validaEditar':
                $this->validaEditar();
                break;
            case 'busqueda':
                $this->busqueda();
                break;
            case 'validacion':
                $this->validacion();
                break;
            case 'llenaTablaValidacion':
                $this->llenaTablaValidacion();
                break;
            case 'vinculacionCuenta':
                $this->vinculacionCuenta();
                break;
            case 'validaVinculacion':
                $this->validaVinculacion();
                break;
            case 'tablaVinculacion':
                $this->tablaVinculacion();
                break;
            case 'llenaTablaVinculacion':
                $this->llenaTablaVinculacion();
                break;
            case 'deshabilitarCtaBancaria':
                $this->deshabilitarCtaBancaria();
                break;
            case 'desvincularClave':
                $this->desvincularClave();
                break;
            case 'consultaBanco':
                $this->consultaBanco();
                break;
            case 'consultaPlaza':
                $this->consultaPlaza();
                break;
            case 'cuentaBancariaAnterior':
                $this->cuentaBancariaAnterior();
                break;
            case 'insertarTablaAnterior':
                $this->insertarTablaAnterior();
                break;
            case 'inversionAnterior':
                $this->inversionAnterior();
                break;
            case 'TablaBitacora':
                $this->TablaBitacora();
                break;
            case 'llenaTablaBitacora':
                $this->llenaTablaBitacora();
                break;
            case 'main':
                // el mismo metodo predeterminado
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->mixHtml('
            <div class="row">
                <legend>Cuentas Receptoras</legend>
            </div>
        ');

        $this->mixHtml('
            <div style="width:100%; padding-left:30%; padding-bottom:120px;">
                <div titpp="Buscar Cuentas" class="boton_opcion" id="buscarcuentas">
                    <div class="boton_opcion_titulo">Cuentas</div>
                    <div>
                        <div>
                            <strong><img src="img/consultacuenta.png"></strong>
                        </div>
                        <small>Cuentas</small>
                    </div>
                </div>
                <div style="display:table-cell;width:5px;"></div>
                <div style="width:5px; display:table-cell"></div>
        ');
        if ($this->validar) {
            $this->mixHTML('
                <div titpp="Validacion" class="boton_opcion" id="validacion">
                    <div class="boton_opcion_titulo" >Validación</div>
                    <div>
                        <div>
                            <strong><img src="img/generalizacuenta.png"></strong>
                        </div>
                        <small>Validación</small>
                    </div>
                </div>
                <div style="display:table-cell;width:5px;"></div>
                <div style="width:5px; display:table-cell"></div>
            ');
        }
        $this->mixHTML('
            </div>
        ');

        $this->mixScript('
            $("#toolsPantalla").html("");
            $("#buscarcuentas").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"busqueda"
                });
            });

            $("#validacion").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"validacion"
                    }
                );
            });
        ');
    }

    private function validacion()
    {
        if (!$this->validar) {
            $this->mixScript('notify("No tiene permitido validar", "error");');
        }

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnreporteExcel = new stdClass();
        $btnreporteExcel->id = 'btnreporteExcel';
        $btnreporteExcel->name = 'btnreporteExcel';
        $btnreporteExcel->title = 'Reportes de cuentas a validar';
        $btnreporteExcel->value = 'Excel';
        $btnreporteExcel->type = 'button';
        $btnreporteExcel->color = 'verde';
        $btnreporteExcel->icon = 'solid fa-file-excel me-2';
        $btnreporteExcel->click = 'printExcelCuentasValidar()';

        $this->agregaTools($this->ponBoton($btnregresarindex) . '' . $this->ponBoton($btnreporteExcel));

        $tabla = new stdClass();
        $tabla->id = 'tablavalidacion';
        $tabla->titulo = 'Cuentas a validar';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tabla->comunica->metodo = 'llenaTablaValidacion';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'VER';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'F. APERTURA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLABE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO DE LA CUENTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE DE LA CUENTA';
        array_push($tr, $th);

        if ($this->validar) {
            $th = new stdClass();
            $th->texto = 'AUTORIZAR';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'RECHAZAR';
            array_push($tr, $th);
        }

        $trbody = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-magnifying-glass';
        $td->class = 'Visualizar';
        $td->name = 'ver';
        $td->id = 'ver';
        $td->attr = new stdClass();
        $td->attr->CLABE = 'CLABE';

        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'F_Apertura';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CLABE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'TIPO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'E_Cuenta';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'N_Cuenta';
        array_push($trbody, $td);

        if ($this->validar) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-thumbs-up';
            $td->class = 'autorizacion';
            $td->name = 'autorizacion';
            $td->id = 'autorizacion';
            $td->attr = new stdClass();
            $td->attr->CLABE = 'CLABE';

            array_push($trbody, $td);

            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'solid fa-thumbs-down';
            $td->class = 'rechazar';
            $td->name = 'rechazar';
            $td->id = 'rechazar';
            $td->attr = new stdClass();
            $td->attr->CLABE = 'CLABE';

            array_push($trbody, $td);
        }

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript('
            function printExcelCuentasValidar() {
                var form = $("<form>")
                .attr("action", "rep/excel/tes/catalogo/exp_CuentasReceptorasCuentasValidar.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost");
                $("body").append(form);
                form.submit();
                form.remove();
            }
        ');
        $this->llenaTablaValidacion();
    }

    private function busqueda()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        //Dibuja el boton
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnNuevaCuentaBancaria = new stdClass();
        $btnNuevaCuentaBancaria->id = 'btnNuevaCuentaBancaria';
        $btnNuevaCuentaBancaria->name = 'btnNuevaCuentaBancaria';
        $btnNuevaCuentaBancaria->text = 'Nueva Cuenta Bancaria';
        $btnNuevaCuentaBancaria->value = 'Nueva Cuenta Bancaria';
        $btnNuevaCuentaBancaria->type = 'button';
        $btnNuevaCuentaBancaria->class = 'me-1';
        $btnNuevaCuentaBancaria->icon = 'solid fa-share-from-square me-2';
        $btnNuevaCuentaBancaria->color = 'verde';
        $btnNuevaCuentaBancaria->click =
        'comunica("' . $this->pantalla . '",{
                metodo:"pintaNuevaCuentaBancaria"
            });';
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnNuevaCuentaBancaria));
        }

        $btnCuentaBancariaAnterior = new stdClass();
        $btnCuentaBancariaAnterior->id = 'btnCuentaBancariaAnterior';
        $btnCuentaBancariaAnterior->name = 'btnCuentaBancariaAnterior';
        $btnCuentaBancariaAnterior->text = 'Nueva Cuenta Bancaria';
        $btnCuentaBancariaAnterior->value = 'Cuenta Bancaria Anterior';
        $btnCuentaBancariaAnterior->type = 'button';
        $btnCuentaBancariaAnterior->color = 'gris';
        $btnCuentaBancariaAnterior->class = 'me-1';
        $btnCuentaBancariaAnterior->icon = 'solid fa-share-from-square me-2';
        $btnCuentaBancariaAnterior->click = '
            var cbs_id="";
            if (seleccion = $("#tabla").tabla("getFilaSeleccion")) {
                cbs_id=seleccion.CLABE;
            }
            comunica("' . $this->pantalla . '",{
                metodo:"cuentaBancariaAnterior",
                cbs_id:cbs_id
            });
        ';
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnCuentaBancariaAnterior));
        }
        $btnExcel = new stdClass();
        $btnExcel->id = 'ListadoExcel';
        $btnExcel->color = 'verde';
        $btnExcel->title = 'Catálogos de claves de financiamiento';
        $btnExcel->value = 'Excel';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->name = 'prueba';
        $btnExcel->click = 'printCmp();';

        $this->agregaTools($this->ponBoton($btnExcel));

        $inputClabe = new StdClass();
        $inputClabe->id = 'clabe';
        $inputClabe->name = 'clabe';
        $inputClabe->placeholder = 'NÚMERO';
        $inputClabe->enDiv = true;
        $inputClabe->label = 'CLABE';
        $inputClabe->formato = 'entero';
        $inputClabe->maxlength = 18;

        $inputFechaAperturaBMin = new StdClass();
        $inputFechaAperturaBMin->id = 'fechaAperturaBMin';
        $inputFechaAperturaBMin->name = 'fechaAperturaBMin';
        $inputFechaAperturaBMin->enDiv = true;
        $inputFechaAperturaBMin->label = 'Mínima';
        $inputFechaAperturaBMin->formato = 'fecha';

        $inputFechaAperturaBMax = new StdClass();
        $inputFechaAperturaBMax->id = 'fechaAperturaBMax';
        $inputFechaAperturaBMax->name = 'fechaAperturaBMax';
        $inputFechaAperturaBMax->enDiv = true;
        $inputFechaAperturaBMax->label = 'Máxima';
        $inputFechaAperturaBMax->formato = 'fecha';

        $inputFechaCancelacionMin = new StdClass();
        $inputFechaCancelacionMin->id = 'fechaCancelacionMin';
        $inputFechaCancelacionMin->name = 'fechaCancelacionMin';
        $inputFechaCancelacionMin->enDiv = true;
        $inputFechaCancelacionMin->label = 'Mínima';
        $inputFechaCancelacionMin->formato = 'fecha';

        $inputFechaCancelacionMax = new StdClass();
        $inputFechaCancelacionMax->id = 'fechaCancelacionMax';
        $inputFechaCancelacionMax->name = 'fechaCancelacionMax';
        $inputFechaCancelacionMax->enDiv = true;
        $inputFechaCancelacionMax->label = 'Máxima';
        $inputFechaCancelacionMax->formato = 'fecha';

        $inputEstadoCuenta = new StdClass();
        $inputEstadoCuenta->sql = 'SELECT eba_id,eba_nombre FROM ' . BD_TESORERIA . '.cat_EstadoCuenta';
        $inputEstadoCuenta->id = 'estadocuenta';
        $inputEstadoCuenta->name = 'estadocuenta';
        $inputEstadoCuenta->label = 'Estado de la Cuenta';
        $inputEstadoCuenta->class = 'limpiar';
        $inputEstadoCuenta->search = 'true';
        $inputEstadoCuenta->camposVal = 'eba_id';
        $inputEstadoCuenta->camposText = 'eba_nombre';
        $inputEstadoCuenta->comoAtr = 'eba_nombre';

        $inputTipo = new StdClass();
        $inputTipo->sql = 'SELECT ter_id, ter_nombre FROM ' . BD_TESORERIA . '.cat_TipoRecurso;';
        $inputTipo->id = 'tipo';
        $inputTipo->name = 'tipo';
        $inputTipo->label = 'Tipo Recurso';
        $inputTipo->camposVal = 'ter_id';
        $inputTipo->camposText = 'ter_nombre';
        $inputTipo->comoAtr = 'ter_nombre';

        $inputAliasCuenta = new StdClass();
        $inputAliasCuenta->id = 'aliasCuenta';
        $inputAliasCuenta->name = 'aliasCuenta';
        $inputAliasCuenta->placeholder = 'Alias de la Cuenta';
        $inputAliasCuenta->enDiv = true;
        $inputAliasCuenta->label = 'Alias de la Cuenta';

        $inputNombreCuenta = new StdClass();
        $inputNombreCuenta->id = 'nombreCuenta';
        $inputNombreCuenta->name = 'nombreCuenta';
        $inputNombreCuenta->placeholder = 'Nombre de la Cuenta';
        $inputNombreCuenta->enDiv = true;
        $inputNombreCuenta->label = 'Nombre de la Cuenta';

        /*el # debe ser modificado por un inner join cuando el modelo de bancos UR
        ya esté en base de datos*/
        $inputNombreBanco = new StdClass();
        $inputNombreBanco->sql = '
            SELECT iba_numero, iba_abreviatura
            FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria
            INNER JOIN ' . BD_CTRL . '.cat_InstitucionesBancarias using (iba_id)
            WHERE sts_id = 1;
        ';
        $inputNombreBanco->id = 'nombreBanco';
        $inputNombreBanco->name = 'nombreBanco';
        $inputNombreBanco->label = 'Nombre del Banco';
        $inputNombreBanco->class = 'limpiar';
        $inputNombreBanco->search = true;
        $inputNombreBanco->camposVal = 'iba_numero';
        $inputNombreBanco->camposText = 'iba_abreviatura';
        $inputNombreBanco->comoAtr = 'iba_abreviatura';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'buscar';
        $btnBuscar->name = 'buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = 'me-1';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"llenaTabla",
                clabe:$("#clabe").val(),
                fechaAperturaBMin:$("#fechaAperturaBMin").val(),
                fechaAperturaBMax:$("#fechaAperturaBMax").val(),
                fechaCancelacionMin:$("#fechaCancelacionMin").val(),
                fechaCancelacionMax:$("#fechaCancelacionMax").val(),
                estadocuenta:$("#estadocuenta option:selected").attr("eba_nombre"),
                tipo:$("#tipo option:selected").attr("ter_nombre"),
                aliasCuenta:$("#aliasCuenta").val(),
                nombreCuenta:$("#nombreCuenta").val(),
                nombreBanco:$("#nombreBanco option:selected").attr("iba_abreviatura")
            })
        ';

        $btnExcelBusqueda = new stdClass();
        $btnExcelBusqueda->id = 'ListadoBusqueda';
        $btnExcelBusqueda->color = 'azul';
        $btnExcelBusqueda->class = 'me-1';
        $btnExcelBusqueda->title = 'Reporte de cuentas bancarias';
        $btnExcelBusqueda->value = 'Tabla';
        $btnExcelBusqueda->icon = 'solid fa-list me-2';
        $btnExcelBusqueda->name = 'ListadoBusqueda';
        $btnExcelBusqueda->click = 'tabla();';

        $btnExcelDatosHoy = new stdClass();
        $btnExcelDatosHoy->id = 'datosHoy';
        $btnExcelDatosHoy->color = 'azul';
        $btnExcelDatosHoy->class = 'me-1';
        $btnExcelDatosHoy->title = 'Relación de cuentas para dar de alta para conciliar';
        $btnExcelDatosHoy->value = 'Datos Hoy';
        $btnExcelDatosHoy->icon = 'solid fa-list me-2';
        $btnExcelDatosHoy->name = 'datosHoy';
        $btnExcelDatosHoy->click = 'datos_hoy();';

        $btnExcelING1 = new stdClass();
        $btnExcelING1->id = 'ing1';
        $btnExcelING1->color = 'azul';
        $btnExcelING1->class = 'me-1';
        $btnExcelING1->title = 'Relación  de cuentas  para configuración';
        $btnExcelING1->value = 'ing1';
        $btnExcelING1->icon = 'solid fa-list me-2';
        $btnExcelING1->name = 'ing1';
        $btnExcelING1->click = 'ing1();';

        $btnExcelING2 = new stdClass();
        $btnExcelING2->id = 'ing2';
        $btnExcelING2->color = 'azul';
        $btnExcelING2->class = 'me-1';
        $btnExcelING2->value = 'ing2';
        $btnExcelING2->icon = 'solid fa-list me-2';
        $btnExcelING2->name = 'ing2';
        $btnExcelING2->click = 'ing2();';

        $this->mixScript('
            $("#aliasCuenta").keypress(function(e) {
                var evento = window.event || e;
                var keynum = evento.keyCode;
                if ((keynum == 8) || (keynum == 32))
                    return true;

                return /\w/.test(String.fromCharCode(evento.charCode));
            });

            $("#nombreCuenta").keypress(function(e) {
                var evento = window.event || e;
                var keynum = evento.keyCode;
                if ((keynum == 8) || (keynum == 32))
                    return true;

                return /\w/.test(String.fromCharCode(evento.charCode));
            });

            function printCmp() {
                var form = $("<form>")
                .attr("action", "rep/excel/tes/catalogo/exp_CuentasReceptoras.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                .append($("<input>").attr("type", "hidden").attr("name", "clabe").val($("#clabe").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "fechaAperturaBMin").val($("#fechaAperturaBMin").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "fechaAperturaBMax").val($("#fechaAperturaBMax").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "estadocuenta").val($("#estadocuenta option:selected").attr("eba_nombre")))
                .append($("<input>").attr("type", "hidden").attr("name", "tipo").val($("#tipo option:selected").attr("ter_nombre")))
                .append($("<input>").attr("type", "hidden").attr("name", "aliasCuenta").val($("#aliasCuenta").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "nombreCuenta").val($("#nombreCuenta").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "nombreBanco").val($("#nombreBanco option:selected").attr("iba_abreviatura")))
                $("body").append(form);
                form.submit();
                form.remove();
            }

            function tabla() {
                var form = $("<form>")
                .attr("action", "rep/excel/tes/catalogo/exp_CuentasReceptorasTabla.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                .append($("<input>").attr("type", "hidden").attr("name", "clabe").val($("#clabe").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "fechaAperturaB").val($("#fechaAperturaB").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "estadocuenta").val($("#estadocuenta option:selected").attr("eba_nombre")))
                .append($("<input>").attr("type", "hidden").attr("name", "tipo").val($("#tipo option:selected").attr("ter_nombre")))
                .append($("<input>").attr("type", "hidden").attr("name", "aliasCuenta").val($("#aliasCuenta").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "nombreCuenta").val($("#nombreCuenta").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "nombreBanco").val($("#nombreBanco option:selected").attr("iba_abreviatura")))
                $("body").append(form);
                form.submit();
                form.remove();
            }

            function datos_hoy() {
                var form = $("<form>")
                .attr("action", "rep/excel/tes/catalogo/exp_CuentasReceptorasDatosHoy.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                $("body").append(form);
                form.submit();
                form.remove();
            }

            function ing1() {
                var form = $("<form>")
                .attr("action", "rep/excel/tes/catalogo/exp_CuentasReceptorasIng1.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                $("body").append(form);
                form.submit();
                form.remove();
            }

            function ing2() {
                var form = $("<form>")
                .attr("action", "rep/excel/tes/catalogo/exp_CuentasReceptorasIng2.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                $("body").append(form);
                form.submit();
                form.remove();
            }
        ');
        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
            <fieldset>
                <legend>CRITERIOS DE BUSQUEDA</legend>
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4 " id="">
                        ' . $this->ponInput($inputClabe) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4" id="">
                        ' . $this->ponCombo($inputEstadoCuenta) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4" id="">
                        ' . $this->ponCombo($inputTipo) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4" id="">
                        ' . $this->ponInput($inputAliasCuenta) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4" id="">
                        ' . $this->ponInput($inputNombreCuenta) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4" id="">
                        ' . $this->ponCombo($inputNombreBanco) . '
                    </fieldset>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <legend>RANGO DE FECHAS DE APERTURA</legend>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($inputFechaAperturaBMin) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6" id="">
                            ' . $this->ponInput($inputFechaAperturaBMax) . '
                        </fieldset>
                    </div>
                    <div class="col-md-6">
                        <legend>RANGO DE FECHAS DE CANCELACIÓN</legend>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($inputFechaCancelacionMin) . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponInput($inputFechaCancelacionMax) . '
                        </fieldset>
                    </div>
                </div>
                <div class="row my-4">
                    <fieldset class="col-md-2 my-1">
                        ' . $this->ponBoton($btnBuscar) . '
                    </fieldset>
                    <fieldset class="col-lg-5 col-md-12">
                    </fieldset>
                    <fieldset class="col-lg-2 col-md-12 text-lg-end text-md-start my-1">
                        ' . $this->ponBoton($btnExcelDatosHoy) . '
                    </fieldset>
                    <fieldset class="col-lg-1 col-md-12 text-lg-end text-md-start my-1">
                        ' . $this->ponBoton($btnExcelING1) . '
                    </fieldset>
                    <fieldset class="col-lg-1 col-md-12 text-lg-end text-md-start my-1">
                        ' . $this->ponBoton($btnExcelING2) . '
                    </fieldset>
                    <fieldset class="col-lg-1 col-md-12 text-lg-end text-md-start my-1">
                        ' . $this->ponBoton($btnExcelBusqueda) . '
                    </fieldset>
                </div>
            </fieldset>
        ';
        $this->mixHtml($html);

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tabla';
        $tabla->titulo = 'Cuentas Bancarias';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tabla->comunica->metodo = 'llenaTabla';
        $tabla->comunica->clabe = '{$("#clabe").val()}';
        $tabla->comunica->fechaAperturaBMin = '{$("#fechaAperturaBMin").val()}';
        $tabla->comunica->fechaAperturaBMax = '{$("#fechaAperturaBMax").val()}';
        $tabla->comunica->estadocuenta = '{$("#estadocuenta option:selected").attr("eba_nombre")}';
        $tabla->comunica->tipo = '{$("#tipo option:selected").attr("ter_nombre")}';
        $tabla->comunica->aliasCuenta = '{$("#aliasCuenta").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'EDITAR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE DE LA CUENTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONTRATO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CHEQUES';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA INVERSIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLABE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CARGO CHEQUE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA CHEQUE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CARGO INVERSIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA INVERSIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'F. APERTURA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'F. CANCELACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO DE LA CUENTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA INTERÉS';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BAN PROP';
        array_push($tr, $th);

        if ($this->validar) {
            $th = new stdClass();
            $th->texto = 'VALIDAR';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'VINCULAR';
            array_push($tr, $th);
        }

        $trbody = [];

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-pencil';
        $td->class = 'Editar';
        $td->name = 'editar';
        $td->id = 'editar';
        $td->color = 'gris';
        $td->attr = new stdClass();
        $td->attr->CLABE = 'CLABE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ban';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'Nom_Cuenta';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'contra';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CUENTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cta_inversion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CLABE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'C_CHEQUE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'A_CHEQUE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'I_CHEQUE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'AI_CHEQUE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'TIPO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'F_Apertura';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'F_Cancelacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'E_Cuenta';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cta_interes';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'BAN_PROP';
        array_push($trbody, $td);

        if ($this->validar) {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'eba_id';
            $td->casos->valor = '1,2,3,5';
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->icono = 'solid fa-share';
            $td->casos->verdadero->class = 'addExport';
            $td->casos->verdadero->color = 'gris';
            $td->casos->verdadero->name = 'export';
            $td->casos->verdadero->id = 'export';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->F_Apertura = 'F_Apertura';
            $td->casos->verdadero->attr->CLABE = 'CLABE';
            $td->casos->verdadero->attr->TIPO = 'TIPO';
            $td->casos->verdadero->attr->E_Cuenta = 'E_Cuenta';
            $td->casos->verdadero->attr->N_Cuenta = 'N_Cuenta';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'eba_id';
            $td->casos->valor = '6';
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->icono = 'solid fa-share';
            $td->casos->verdadero->class = 'vinculacioncuenta';
            $td->casos->verdadero->color = 'gris';
            $td->casos->verdadero->name = 'vincular';
            $td->casos->verdadero->id = 'vincular';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->CLABE = 'CLABE';
            array_push($trbody, $td);
        }

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTabla();
    }

    private function vinculacionCuenta()
    {
        $clabe = $this->prm->clabe;
        $btnRegresarBusqueda = new stdClass();
        $btnRegresarBusqueda->id = 'Rbusqueda';
        $btnRegresarBusqueda->name = 'Rbusqueda';
        $btnRegresarBusqueda->text = 'Regresar';
        $btnRegresarBusqueda->value = 'Regresar';
        $btnRegresarBusqueda->type = 'button';
        $btnRegresarBusqueda->color = 'gris';
        $btnRegresarBusqueda->icon = 'solid fa-circle-left me-2';
        // Regresa a la pantalla anterior
        $btnRegresarBusqueda->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "busqueda"
            });
        ';
        $this->agregaTools($this->ponBoton($btnRegresarBusqueda));

        $btnVincular = new stdClass();
        $btnVincular->id = 'vincula';
        $btnVincular->name = 'vincula';
        $btnVincular->text = 'Vincular';
        $btnVincular->value = 'Vincular';
        $btnVincular->type = 'button';
        $btnVincular->color = 'azul';
        $btnVincular->icon = 'solid fa-up-right-from-square me-2';
        $btnVincular->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"validaVinculacion",
                clabe: "' . $this->prm->clabe . '",
                cf:$("#CFinanciamiento option:selected").attr("ecf_id")
            });
        ';
        $this->agregaTools($this->ponBoton($btnVincular));

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('SELECT cbs_alias FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin where cbs_id =  ?;');
        $conexion->consultar([$clabe]);
        $aliasCBS = $conexion->extraer();
        $conexion->desconectar();

        $inputClabe = new StdClass();
        $inputClabe->id = 'ClabeV';
        $inputClabe->name = 'ClabeV';
        $inputClabe->value = $this->prm->clabe . ' - ' . $aliasCBS['cbs_alias'];
        $inputClabe->enDiv = true;
        $inputClabe->label = 'CLABE INTERBANCARIA';
        $inputClabe->disabled = true;

        $inputCFinanciamiento = new StdClass();
        $sql = 'SELECT
                e.ecf_id, c.cfi_clave, c.cfi_nombre
            FROM ' . BD_SINPRES . '.enc_ClaveFinanciamiento as e
            LEFT JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v using(ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' as c USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE
                cfi_ejercicio2 = ? AND e.ecf_ejercicio = ?
                AND cbs_id is null;';
        $inputCFinanciamiento->sql = $sql;
        $inputCFinanciamiento->params = [APP_EJERCICIO, APP_EJERCICIO];
        $inputCFinanciamiento->id = 'CFinanciamiento';
        $inputCFinanciamiento->name = 'CFinanciamiento';
        $inputCFinanciamiento->label = 'Clave de Financiamiento';
        $inputCFinanciamiento->required = true;
        $inputCFinanciamiento->camposVal = 'cfi_clave';
        $inputCFinanciamiento->camposText = 'cfi_nombre';
        $inputCFinanciamiento->comoAtr = 'ecf_id';
        $inputCFinanciamiento->search = true;

        $html = '
            <fieldset>
                <legend>VINCULACIÓN</legend>
                <div class="row col-md-12 col-xs-12">
                    <div class="row">
                        <fieldset class="col-md-6 col-xs-6 mb-4">
                            ' . $this->ponInput($inputClabe) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6 col-xs-6 mb-4">
                            ' . $this->ponCombo($inputCFinanciamiento) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-12 col-xs-12 " id="idTVinculacion">
                        </fieldset>
                    </div>
                </div>
            </fieldset>
        ';
        $this->mixHtml($html);
        $this->tablaVinculacion();
    }

    private function tablaVinculacion()
    {
        $this->setContenedor('idTVinculacion');

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tablaFinanciamiento';
        $tabla->titulo = 'Claves de Financiamiento';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaVinculacion';
        $tabla->comunica->clabe = $this->prm->clabe;

        $tr = [];
        $th = new stdClass();
        $th->texto = 'CLABE INTERBANCARIA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLAVE DE FINANCIAMIENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DESVINCULAR';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'CLABE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'C_F';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ECF';

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-trash';
        $td->color = 'rojo';
        $td->align = 'center';
        $td->class = 'DESV';
        $td->attr = new stdClass();
        $td->attr->clabe = 'CLABE';
        $td->attr->claveF = 'ECF';

        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaVinculacion();
    }

    private function validaVinculacion()
    {
        $this->prm->clabe = isset($this->prm->clabe) ? $this->prm->clabe : '';
        $this->prm->cf = isset($this->prm->cf) ? $this->prm->cf : '';

        if ($this->prm->cf == '') {
            $this->mixScript("notify('Falta ingresar la Clave de Financiamiento', 'warning');");
            return 0;
        }
        $this->prm2 = new stdClass();
        $this->prm2->cbs_id = $this->prm->clabe;
        $this->prm2->ecf_id = $this->prm->cf;
        $params = [$this->prm->clabe, $this->prm->cf];

        //Validamos si la clabe ha sido ya ha sido vinculada previamente a la cuenta
        $clabe = new AccesoDatos();
        $clabe->conectar();
        $clabe->setSql('SELECT cbs_id, ecf_id FROM ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento
            WHERE cbs_id = ? AND ecf_id = ?
        ');
        $clabe->consultar($params);
        $clabe->desconectar();

        if ($clabe->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La clabe ya ha sido vinculada previamente', 'error');");
        } else {
            //Procedemos con la vinculación si no hay registros que coincidan
            $this->prm2->abc = 'a';
            SefinClaveFinanciamiento::abc($this->prm2);
            $this->mixScript('
                comunica("' . $this->pantalla . '",
                {
                    metodo:"llenaTablaVinculacion",
                    clabe: "' . $this->prm->clabe . '"
                });
            ');
        }
    }

    private function desvincularClave()
    {
        $sql = 'SELECT ecf_id FROM ' . BD_CTRL . '.scp_Clc WHERE ecf_id = ? ;';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->claveF]);
        $datos = $this->extraer();
        $this->desconectar();

        if ($datos && $datos['ecf_id'] != '') {
            $this->mixScript('notify("La clave de Financiamineto está siendo utilizada por CLC ", "error");');
            return false;
        } else {
            $this->prm2 = new stdClass();
            $this->prm2->cbs_id = $this->prm->clabe;
            $this->prm2->ecf_id = $this->prm->claveF;
            $this->prm2->abc = 'b';
            SefinClaveFinanciamiento::abc($this->prm2);
            $this->mixScript('notify("La Clave de Financiamiento ha sido desvinculada", "success");');
        }
        $this->mixScript('
            comunica("' . $this->pantalla . '", {
                metodo:"llenaTablaVinculacion",
                clabe: "' . $this->prm->clabe . '"
            });
        ');
    }

    private function llenaTablaVinculacion()
    {
        $params = [APP_EJERCICIO, APP_EJERCICIO, $this->prm->clabe];
        $sql = "
            SELECT
                vsc.cbs_id CLABE,
                CONCAT(ec.ecf_clave,' - ',cf.cfi_nombre) C_F,
                vsc.ecf_id ECF
            FROM " . BD_TESORERIA . '.vin_SefinClaveFinanciamiento vsc
            INNER JOIN ' . BD_SINPRES . '.enc_ClaveFinanciamiento ec on ec.ecf_id = vsc.ecf_id
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE
                cf.cfi_ejercicio2 = ? AND ec.ecf_ejercicio = ?
                AND vsc.cbs_id = ?;';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaFinanciamiento';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".DESV").click(function() {
                var Clabe = $(this).attr("clabe");
                var ClaveF = $(this).attr("claveF");

                alertify.confirm("Desvincular Clave",
                    "¿Confirma que desea desvinular la Calve de Financiamiento?",
                    function(){
                        comunica("' . $this->pantalla . '", {
                            metodo:"desvincularClave",
                            clabe: Clabe,
                            claveF: ClaveF
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    private function llenaTablaValidacion()
    {
        $sql = '
            SELECT cbs_fechaApertura F_Apertura, cbs_id CLABE,
                ter_nombre TIPO, eba_nombre E_Cuenta, cbs_nombre N_Cuenta
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoCuenta USING (eba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoRecurso USING (ter_id)
            WHERE eba_id = 4';
        $this->setSql($sql);

        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablavalidacion';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".autorizacion").click(function() {
                var Clabe = $(this).attr("CLABE");
                alertify.confirm(
                    "Autorizar Cuenta",
                    "¿Confirma que desea autorizar la cuenta?",
                    function(){
                        notify("La cuenta ha sido autorizada", "success");
                        comunica("' . $this->pantalla . '", {
                            metodo:"autoriza",
                            clabe: Clabe
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".rechazar").click(function() {
                var Clabe = $(this).attr("CLABE");
                alertify.confirm(
                    "Rechazar Cuenta",
                    "¿Confirma que desea rechazar la cuenta?",
                    function(){
                        notify("La cuenta ha sido rechazada", "success");
                        comunica("' . $this->pantalla . '", {
                            metodo:"rechaza",
                            clabe: Clabe
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".Visualizar").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo:"edita",
                    clabe: $(this).attr("CLABE"),
                    confirmacion: "si"
                });
            });
        ');
    }

    private function llenaTabla()
    {
        $this->prm->clabe = isset($this->prm->clabe) ? $this->prm->clabe : '';
        $this->prm->fechaAperturaBMin = isset($this->prm->fechaAperturaBMin) ? $this->prm->fechaAperturaBMin : '';
        $this->prm->fechaAperturaBMax = isset($this->prm->fechaAperturaBMax) ? $this->prm->fechaAperturaBMax : '';
        $this->prm->fechaCancelacionMin = isset($this->prm->fechaCancelacionMin) ? $this->prm->fechaCancelacionMin : '';
        $this->prm->fechaCancelacionMax = isset($this->prm->fechaCancelacionMax) ? $this->prm->fechaCancelacionMax : '';
        $this->prm->estadocuenta = isset($this->prm->estadocuenta) ? $this->prm->estadocuenta : '';
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $this->prm->aliasCuenta = isset($this->prm->aliasCuenta) ? $this->prm->aliasCuenta : '';
        $this->prm->nombreCuenta = isset($this->prm->nombreCuenta) ? $this->prm->nombreCuenta : '';
        $this->prm->nombreBanco = isset($this->prm->nombreBanco) ? $this->prm->nombreBanco : '';

        $extraSql = '';
        $params = [];
        // Ojo: Del ticket #518893, se agrega la funcion CONCAT() para que muestre correctamente la cuenta bancaria
        //          si pertenece a "Banco Azteca, S.A."
        $sql = '
            SELECT
                iba_abreviatura ban, cbs_nombre Nom_Cuenta, cbs_id CLABE, cbs_contrato contra,
                (
                    SELECT if (SUBSTRING(cbs_id,1,3)=127,CONCAT(cbs_cuentaBanco),SUBSTRING(cbs_cuentaBanco,4))
                    FROM ' . BD_TESORERIA . '.vin_CuentaSap
                    WHERE tcs_id = 2 and cbs_id = CLABE
                ) CUENTA,
                (
                    SELECT cbs_cuentaBanco
                    FROM ' . BD_TESORERIA . '.vin_CuentaSap
                    WHERE tcs_id = 1 and cbs_id = CLABE
                ) cta_inversion,
                (
                    SELECT concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    FROM ' . BD_TESORERIA . ".vin_CuentaSap
                    WHERE tcs_id = 2 and cbs_id = CLABE
                ) C_CHEQUE,
                (
                    SELECT CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id, '.',cta_id)
                    FROM " . BD_TESORERIA . '.vin_SefinPlanCuenta
                    WHERE tcs_id = 2 and cbs_id = CLABE
                ) as A_CHEQUE,
                (
                    SELECT concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    FROM ' . BD_TESORERIA . ".vin_CuentaSap
                    WHERE tcs_id = 1 and cbs_id = CLABE
                ) I_CHEQUE,
                (
                    SELECT CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id, '.', cta_id)
                    FROM " . BD_TESORERIA . '.vin_SefinPlanCuenta
                    WHERE tcs_id = 1 and cbs_id = CLABE
                ) as AI_CHEQUE,
                ter_nombre TIPO, eba_nombre E_Cuenta, cbs_fechaApertura F_Apertura, cbs_fechaCancelacion F_Cancelacion,
                iba_id, eba_id,
                (
                    SELECT cbs_cuentaBanco
                    FROM ' . BD_TESORERIA . '.vin_CuentaSap
                    WHERE tcs_id = 3 and cbs_id = CLABE
                ) cta_interes,
                (
                    SELECT concat(ibs_propio, cbs_consecutivo)
                    FROM ' . BD_TESORERIA . '.vin_CuentaSap
                    INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancariaSap as c using (ibs_id)
                    WHERE cbs_id = CLABE and tcs_id = 2
                ) BAN_PROP
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria using (iba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoRecurso using (ter_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoCuenta USING (eba_id)
            WHERE 1 = 1 ';
        if (
            $this->prm->clabe == ''
            && $this->prm->fechaAperturaBMin == ''
            && $this->prm->fechaAperturaBMax == ''
            && $this->prm->fechaCancelacionMin == ''
            && $this->prm->fechaCancelacionMax == ''
            && $this->prm->estadocuenta == ''
            && $this->prm->tipo == ''
            && $this->prm->aliasCuenta == ''
            && $this->prm->nombreCuenta == ''
            && $this->prm->nombreBanco == ''
        ) {
            $this->setSql($sql . ' ORDER BY iba_id;');
        } else {
            if ($this->prm->clabe != '') {
                $extraSql .= 'AND cbs_id like ? ';
                array_push($params, '%' . $this->prm->clabe . '%');
            }
            if ($this->prm->fechaAperturaBMin != '' && $this->prm->fechaAperturaBMax != '') {
                $fechaMin = DateTime::createFromFormat('d/m/Y', $this->prm->fechaAperturaBMin);
                $fechaMax = DateTime::createFromFormat('d/m/Y', $this->prm->fechaAperturaBMax);
                array_push($params, $fechaMin->format('Y-m-d'), $fechaMax->format('Y-m-d'));
                $extraSql .= 'AND cbs_fechaApertura BETWEEN ? AND ? ';
            } elseif ($this->prm->fechaAperturaBMin != '' && $this->prm->fechaAperturaBMax == '') {
                $fechaMin = DateTime::createFromFormat('d/m/Y', $this->prm->fechaAperturaBMin);
                array_push($params, $fechaMin->format('Y-m-d'));
                $extraSql .= 'AND cbs_fechaApertura = ? ';
            } elseif ($this->prm->fechaAperturaBMin == '' && $this->prm->fechaAperturaBMax != '') {
                $fechaMax = DateTime::createFromFormat('d/m/Y', $this->prm->fechaAperturaBMax);
                array_push($params, $fechaMax->format('Y-m-d'));
                $extraSql .= 'AND cbs_fechaApertura = ? ';
            }
            if ($this->prm->fechaCancelacionMin != '' && $this->prm->fechaCancelacionMax != '') {
                $fechaMin = DateTime::createFromFormat('d/m/Y', $this->prm->fechaCancelacionMin);
                $fechaMax = DateTime::createFromFormat('d/m/Y', $this->prm->fechaCancelacionMax);
                array_push($params, $fechaMin->format('Y-m-d'), $fechaMax->format('Y-m-d'));
                $extraSql .= 'AND cbs_fechaCancelacion BETWEEN ? AND ? ';
            } elseif ($this->prm->fechaCancelacionMin != '' && $this->prm->fechaCancelacionMax == '') {
                $fechaMin = DateTime::createFromFormat('d/m/Y', $this->prm->fechaCancelacionMin);
                array_push($params, $fechaMin->format('Y-m-d'));
                $extraSql .= 'AND cbs_fechaCancelacion = ? ';
            } elseif ($this->prm->fechaCancelacionMin == '' && $this->prm->fechaCancelacionMax != '') {
                $fechaMax = DateTime::createFromFormat('d/m/Y', $this->prm->fechaCancelacionMax);
                array_push($params, $fechaMax->format('Y-m-d'));
                $extraSql .= 'AND cbs_fechaCancelacion = ? ';
            }
            if ($this->prm->estadocuenta != '') {
                array_push($params, $this->prm->estadocuenta);
                $extraSql .= 'AND eba_nombre = ? ';
            }
            if ($this->prm->tipo != '') {
                array_push($params, $this->prm->tipo);
                $extraSql .= 'AND ter_nombre = ? ';
            }
            if ($this->prm->aliasCuenta != '') {
                array_push($params, '%' . $this->prm->aliasCuenta . '%');
                $extraSql .= 'AND cbs_alias like ? ';
            }
            if ($this->prm->nombreCuenta != '') {
                array_push($params, '%' . $this->prm->nombreCuenta . '%');
                $extraSql .= 'AND cbs_nombre like ? ';
            }
            if ($this->prm->nombreBanco != '') {
                array_push($params, $this->prm->nombreBanco);
                $extraSql .= 'AND iba_abreviatura = ? ';
                $extraSql .= 'order by C_CHEQUE desc ';
            }
            $this->setSql($sql . ' ' . $extraSql . ';');
            $this->setParams($params);
        }

        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tabla';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".addExport").click(function() {
                var Clabe = $(this).attr("CLABE");

                alertify.confirm(
                    "Enviar a Validación",
                    "¿Desea enviar a validación?",
                    function(){
                        notify("La cuenta se ha envaido a validación", "success");
                        comunica("' . $this->pantalla . '", {
                            metodo:"agrega",
                            clabe: Clabe
                        });
                        comunica("' . $this->pantalla . '", {
                            metodo:"llenaTabla",
                            clabe: Clabe
                        });
                    },
                    function(){}
                ).set("defaultFocus","cancel")
                .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".Editar").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo:"edita",
                    clabe: $(this).attr("CLABE"),
                    Edit: "editar"
                });
            });

            $(".vinculacioncuenta").click(function() {
                comunica("' . $this->pantalla . '", {
                    metodo:"vinculacionCuenta",
                    clabe: $(this).attr("CLABE")
                });
                comunica("' . $this->pantalla . '", {
                    metodo:"tablaVinculacion",
                    clabe: $(this).attr("CLABE")
                });
                comunica("' . $this->pantalla . '", {
                    metodo:"llenaTablaVinculacion",
                    clabe: $(this).attr("CLABE")
                });
            });
        ');
    }

    private function agrega()
    {
        $this->prm2 = new StdClass();
        $this->prm2->eba_id = 4;
        $this->prm2->cbs_id = $this->prm->clabe;
        $this->prm2->abc = 'c';
        CuentaBancariaSefin::abc($this->prm2);
    }

    private function autoriza()
    {
        $this->prm2 = new StdClass();
        $this->prm2->eba_id = 6;
        $this->prm2->cbs_id = $this->prm->clabe;
        $this->prm2->abc = 'c';
        CuentaBancariaSefin::abc($this->prm2);
        $this->validacion();
    }

    private function rechaza()
    {
        $this->prm2 = new StdClass();
        $this->prm2->eba_id = 5;
        $this->prm2->cbs_id = $this->prm->clabe;
        $this->prm2->abc = 'c';
        CuentaBancariaSefin::abc($this->prm2);
        $this->validacion();
    }

    private function edita()
    {
        if (!$this->escritura) {
            $this->mixScript('notify("No tiene permitido editar las cuentas", "error");');
        }

        $btnRegresarBusqueda = new stdClass();
        $btnRegresarBusqueda->id = 'btnregresarbusqueda';
        $btnRegresarBusqueda->name = 'btnregresarbusqueda';
        $btnRegresarBusqueda->text = 'Regresar';
        $btnRegresarBusqueda->value = 'Regresar';
        $btnRegresarBusqueda->type = 'button';
        $btnRegresarBusqueda->color = 'gris';
        $btnRegresarBusqueda->icon = 'solid fa-circle-left me-2';
        // Regresa a la pantalla anterior
        $btnRegresarBusqueda->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo:"busqueda"
            });
        ';

        $btnRegresarValidacion = new stdClass();
        $btnRegresarValidacion->id = 'btnregresarvalidacion';
        $btnRegresarValidacion->name = 'btnregresarvalidacion';
        $btnRegresarValidacion->text = 'Regresar';
        $btnRegresarValidacion->value = 'Regresar';
        $btnRegresarValidacion->type = 'button';
        $btnRegresarValidacion->color = 'gris';
        $btnRegresarValidacion->icon = 'solid fa-circle-left me-2';
        // Regresa a la pantalla anterior
        $btnRegresarValidacion->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo:"validacion"
            });
        ';

        $btnEditar = new stdClass();
        $btnEditar->id = 'editar';
        $btnEditar->name = 'editar';
        $btnEditar->text = 'Guardar';
        $btnEditar->value = 'Guardar';
        $btnEditar->type = 'button';
        $btnEditar->class = 'me-1';
        $btnEditar->color = 'verde';
        $btnEditar->icon = 'solid fa-floppy-disk me-2';
        $btnEditar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"validaEditar",
                fecha:$("#fecha").val(),
                sucursal:$("#sucursal").val(),
                nombre:$("#nombre").val(),
                alias:$("#alias").val(),
                cliente:$("#cliente").val(),
                contrato:$("#contrato").val(),
                obs:$("#obs").val(),
                clabe:$("#clabeI").val(),
                ebaID:$("#ebaID").val(),
                CtaCargo:$("#CtaCargo").val(),
                cbs_cuentaBanco:$("#cuentaInver").val(),
                armoniChec:$("#armoniChec").val(),
                armoniInver:$("#armoniInver").val(),
                CtaCargoInver:$("#CtaCargoInver").val(),
                cbs_cuenta:$("#cbs_cuenta").val()
            });';

        $btnDeshabilitar = new stdClass();
        $btnDeshabilitar->id = 'deshabilitar';
        $btnDeshabilitar->name = 'deshabilitar';
        $btnDeshabilitar->text = 'deshabilitar';
        $btnDeshabilitar->value = 'Deshabilitar';
        $btnDeshabilitar->type = 'button';
        $btnDeshabilitar->class = 'me-1';
        $btnDeshabilitar->color = 'rojo';
        $btnDeshabilitar->icon = 'solid fa-trash-can me-2';
        $btnDeshabilitar->click = '
            var ClabeI = $("#clabeI").val();
            alertify.confirm("Deshabilitar Cuenta",
                "¿Desea deshabilitar esta cuenta bancaria?",
                function(){
                    $("#myModal").modal("show");
                },
                function(){}
            ).set("defaultFocus","cancel")
            .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';
        $btnBitacora = new stdClass();
        $btnBitacora->id = 'bitacora';
        $btnBitacora->name = 'bitacora';
        $btnBitacora->text = 'Bitacora';
        $btnBitacora->value = 'Bitácora';
        $btnBitacora->type = 'button';
        $btnBitacora->class = 'me-1';
        $btnBitacora->color = 'verde';
        $btnBitacora->icon = 'solid fa-eye me-2';
        $btnBitacora->click = '
            alertify.alert("Bitacora",
                "<div id=\'mostrar_bitacora\'>Cargando contenido...</div>",
                function(){}
            ).set("label", "Aceptar");

            setTimeout(function() {
                var Clabe = $("#clabeI").val();
                comunica("' . $this->pantalla . '", {metodo:"TablaBitacora", clabe: Clabe });
            }, 500);
        ';

        $this->mixHtml('
            <div id="myModal" class="modal" role="dialog" >
                <div class="modal-dialog modal-mg">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Fecha de Cancelación</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h4>Introduzca la fecha de cancelación</h4>
                            <div class="input-group date" data-provide="datepicker">
                                <input type="text" class="form-control" id="datepicke">
                                <div class="input-group-addon">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="display: block;">
                            <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal" id="botonAceptarDate" onclick="desha()">Aceptar</button>
                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->mixScript('
            $("#datepicke").datepicker();
            function desha()
            {
                $(".modal-backdrop").remove();
                var Clabe = $("#clabeI").val();
                comunica("' . $this->pantalla . '", {
                    metodo:"deshabilitarCtaBancaria",
                    clabe: Clabe,
                    fechaDes: $("#datepicke").val()
                });
            }
        ');

        $btnAutoriza = new stdClass();
        $btnAutoriza->id = 'autoriza';
        $btnAutoriza->name = 'autoriza';
        $btnAutoriza->text = 'Autorizar';
        $btnAutoriza->value = 'Autorizar';
        $btnAutoriza->type = 'button';
        $btnAutoriza->class = 'me-1';
        $btnAutoriza->color = 'verde';
        $btnAutoriza->icon = 'solid fa-thumbs-up me-2';
        $btnAutoriza->click = '
            var Clabe = $("#clabeI").val();
            alertify.confirm("Autorizar Cuenta",
                "¿Confirma que desea autorizar la cuenta?",
                function(){
                    notify("La cuenta ha sido autorizada", "success");
                    comunica("' . $this->pantalla . '", {
                        metodo:"autoriza",
                        clabe: Clabe
                    });
                },
                function(){}
            ).set("defaultFocus","cancel")
            .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        $btnRechaza = new stdClass();
        $btnRechaza->id = 'rechaza';
        $btnRechaza->name = 'rechaza';
        $btnRechaza->text = 'Rechazar';
        $btnRechaza->value = 'Rechazar';
        $btnRechaza->type = 'button';
        $btnRechaza->class = 'me-1';
        $btnRechaza->color = 'rojo';
        $btnRechaza->icon = 'solid fa-thumbs-down me-2';
        $btnRechaza->click = '
            var Clabe = $("#clabeI").val();
            alertify.confirm("Rechazar Cuenta",
                "¿Confirma que desea rechazar la cuenta?",
                function(){
                    notify("La cuenta ha sido rechazada", "success");
                    comunica("' . $this->pantalla . '", {
                        metodo:"rechaza",
                        clabe: Clabe
                    });
                },
                function(){}
            ).set("defaultFocus","cancel")
            .set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        $btnEditaCuentaBancariaAnterior = new stdClass();
        $btnEditaCuentaBancariaAnterior->id = 'editaCuentaBancariaAnterior';
        $btnEditaCuentaBancariaAnterior->name = 'editaCuentaBancariaAnterior';
        $btnEditaCuentaBancariaAnterior->text = '';
        $btnEditaCuentaBancariaAnterior->value = '';
        $btnEditaCuentaBancariaAnterior->type = 'button';
        $btnEditaCuentaBancariaAnterior->color = 'gris';
        $btnEditaCuentaBancariaAnterior->icon = 'fa-solid fa-pencil';
        $btnEditaCuentaBancariaAnterior->click = '
            comunica("' . $this->pantalla . '", {
                metodo: "cuentaBancariaAnterior",
                cbs_id:"' . $this->prm->clabe . '"
            });
        ';

        $params = [$this->prm->clabe];
        $this->setSql('SELECT * FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin WHERE cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $row = $this->extraer();
        $this->prm->Edit = (isset($this->prm->Edit)) ? $this->prm->Edit : '';
        $this->prm->confirmacion = (isset($this->prm->confirmacion)) ? $this->prm->confirmacion : '';
        //Dibuja el boton

        $vEditar = $this->prm->Edit;
        $vConfirmacion = $this->prm->confirmacion;
        if ($vEditar == 'editar') {
            $this->agregaTools($this->ponBoton($btnRegresarBusqueda));
            if ($this->escritura && $row['eba_id'] != 7 && $row['eba_id'] != 4) {
                $this->agregaTools($this->ponBoton($btnEditar));
            }
            //cambio aqui
            if ($this->escritura && $row['eba_id'] == 6) {
                $this->agregaTools($this->ponBoton($btnDeshabilitar));
            }
        }

        if ($vConfirmacion == 'si') {
            $this->agregaTools($this->ponBoton($btnRegresarValidacion));
            $this->agregaTools($this->ponBoton($btnAutoriza));
            $this->agregaTools($this->ponBoton($btnRechaza));
        }
        $this->agregaTools($this->ponBoton($btnBitacora));

        $inputEbaID = new stdClass();
        $inputEbaID->id = 'ebaID';
        $inputEbaID->value = $row['eba_id'];

        $inputClabe = new StdClass();
        $inputClabe->id = 'clabeI';
        $inputClabe->name = 'clabeI';
        $inputClabe->value = $row['cbs_id'];
        $inputClabe->enDiv = true;
        $inputClabe->label = 'Clabe';
        $inputClabe->disabled = true;

        $inputCuenta = new StdClass();
        $inputCuenta->id = 'cbs_cuenta';
        $inputCuenta->name = 'cbs_cuenta';
        $this->setSql('
            SELECT cbs_cuentaBanco as cbs_cuenta
            FROM ' . BD_TESORERIA . '.vin_CuentaSap
            WHERE tcs_id = 2
            AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();
        $inputCuenta->value = substr($valor['cbs_cuenta'], 3);
        $inputCuenta->enDiv = true;
        $inputCuenta->label = 'Cuenta Cheque';
        $inputCuenta->formato = 'entero';

        $this->setSql('
            SELECT concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) CtaCargo
            FROM ' . BD_TESORERIA . '.vin_CuentaSap
            WHERE tcs_id = 2
                AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();
        $existeCuentaSap = $valor['CtaCargo'];

        $inputCtaCargo = new StdClass();
        $inputCtaCargo->id = 'CtaCargo';
        $inputCtaCargo->name = 'CtaCargo';
        $inputCtaCargo->value = $valor['CtaCargo'];
        $inputCtaCargo->enDiv = true;
        $inputCtaCargo->label = 'Cuenta Cargo';
        $inputCtaCargo->formato = 'entero';

        $this->setSql('
            SELECT concat(ger_id, ".", cgp_id, ".", rub_id, ".", cgcu_id, ".", cgsc_id, ".", cgc_id, ".", cta_id) armoniChec
            FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta
            WHERE tcs_id = 2
                AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = null;
        $existeCuentaArmonizada = '';
        if ($this->getNumeroRegistros() > 0) {
            $valor = $this->extraer();
            $existeCuentaArmonizada = $valor['armoniChec'];
        }

        $inputArmoniChec = new StdClass();
        $inputArmoniChec->id = 'armoniChec';
        $inputArmoniChec->name = 'armoniChec';
        $inputArmoniChec->value = $existeCuentaArmonizada;
        $inputArmoniChec->enDiv = true;
        $inputArmoniChec->label = 'Armonizada Cheque';

        if ($vEditar == 'editar') {
            if ($this->escritura && $row['eba_id'] != 7 && $row['eba_id'] != 4) {
                if (empty($existeCuentaSap) || empty($existeCuentaArmonizada)) {
                    $this->agregaTools($this->ponBoton($btnEditaCuentaBancariaAnterior));
                }
            }
        }

        $this->setSql('SELECT cbs_cuentaBanco, concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) CtaCargoInver
            FROM ' . BD_TESORERIA . '.vin_CuentaSap
            WHERE tcs_id = 1
                AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();

        $inputCuentaInver = new StdClass();
        $inputCuentaInver->id = 'cuentaInver';
        $inputCuentaInver->name = 'cuentaInver';
        $inputCuentaInver->value = ($valor) ? $valor['cbs_cuentaBanco'] : '';
        $inputCuentaInver->enDiv = true;
        $inputCuentaInver->label = 'Cuenta Inversión';
        $inputCuentaInver->formato = 'entero';

        $inputCtaCargoInver = new StdClass();
        $inputCtaCargoInver->id = 'CtaCargoInver';
        $inputCtaCargoInver->name = 'CtaCargoInver';
        $inputCtaCargoInver->value = ($valor) ? $valor['CtaCargoInver'] : '';
        $inputCtaCargoInver->enDiv = true;
        $inputCtaCargoInver->label = 'Cuenta Cargo Inversión';
        $inputCtaCargoInver->formato = 'entero';

        $this->setSql('SELECT concat(ger_id, ".", cgp_id, ".", rub_id, ".", cgcu_id, ".", cgsc_id, ".", cgc_id, ".", cta_id) armoniInver
            FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta
            WHERE tcs_id = 1
                AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();

        $inputArmoniInver = new StdClass();
        $inputArmoniInver->id = 'armoniInver';
        $inputArmoniInver->name = 'armoniInver';
        $inputArmoniInver->value = ($valor) ? $valor['armoniInver'] : '';
        $inputArmoniInver->enDiv = true;
        $inputArmoniInver->label = 'Armonizada Inversión';

        $this->setSql('
            SELECT cbs_cuentaBanco
            FROM ' . BD_TESORERIA . '.vin_CuentaSap
            WHERE tcs_id = 3
                AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();

        $inputCuentaInter = new StdClass();
        $inputCuentaInter->id = 'cuentaInter';
        $inputCuentaInter->name = 'cuentaInter';
        $inputCuentaInter->value = (isset($this->prm->sucursal)) ? $this->prm->sucursal : '';
        $inputCuentaInter->enDiv = true;
        $inputCuentaInter->label = 'Cuenta Interes';
        $inputCuentaInter->disabled = true;
        $inputCuentaInter->formato = 'entero';

        $inputFecha = new StdClass();
        $inputFecha->id = 'fecha';
        $inputFecha->name = 'fecha';
        $inputFecha->value = substr($row['cbs_fechaApertura'], -2) . '/' . substr($row['cbs_fechaApertura'], -5, 2) . '/' . substr($row['cbs_fechaApertura'], 0, 4);
        $inputFecha->enDiv = true;
        $inputFecha->label = 'Fecha Apertura';
        $inputFecha->formato = 'fecha';
        if ($row['eba_id'] == 4 || $row['eba_id'] == 6 || $row['eba_id'] == 7 || !$this->escritura || $vConfirmacion == 'si') {
            $inputFecha->disabled = true;
        }

        $inputFechaCancelacion = new StdClass();
        $inputFechaCancelacion->id = 'fechaCancelacion';
        $inputFechaCancelacion->name = 'fechaCancelacion';
        if (!is_null($row['cbs_fechaCancelacion'])) {
            $inputFechaCancelacion->value = substr($row['cbs_fechaCancelacion'], -2) . '/' . substr($row['cbs_fechaCancelacion'], -5, 2) . '/' . substr($row['cbs_fechaCancelacion'], 0, 4);
        }
        $inputFechaCancelacion->enDiv = true;
        $inputFechaCancelacion->label = 'Fecha Cancelación';
        $inputFechaCancelacion->formato = 'fecha';
        $inputFechaCancelacion->disabled = true;

        $this->setSql('SELECT iba_abreviatura FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria WHERE iba_id = ? ;');
        $this->conectar();
        $this->consultar([$row['iba_id']]);
        $banco = $this->extraer();

        $inputNBanco = new StdClass();
        $inputNBanco->id = 'nbanco';
        $inputNBanco->name = 'nbanco';
        $inputNBanco->value = $banco['iba_abreviatura'];
        $inputNBanco->enDiv = true;
        $inputNBanco->label = 'Nombre del Banco';
        $inputNBanco->disabled = true;

        $this->setSql('
            SELECT CONCAT(c.ibs_propio,b.cbs_consecutivo) as BANPROP
            FROM ' . BD_TESORERIA . '.vin_CuentaSap as b
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancariaSap as c using (ibs_id)
            WHERE cbs_id = ?
            AND b.tcs_id = 2;');
        $this->conectar();
        $this->consultar($params);
        $bancoP = $this->extraer();

        $inputBancoP = new StdClass();
        $inputBancoP->id = 'bancoP';
        $inputBancoP->name = 'bancoP';
        $inputBancoP->value = $bancoP['BANPROP'];
        $inputBancoP->enDiv = true;
        $inputBancoP->label = 'Banco Propio';
        $inputBancoP->disabled = true;

        $inputSucursal = new StdClass();
        $inputSucursal->id = 'sucursal';
        $inputSucursal->name = 'sucursal';
        $inputSucursal->value = $row['cbs_sucursal'];
        $inputSucursal->enDiv = true;
        $inputSucursal->label = 'Sucursal';
        $inputSucursal->maxlength = '5';
        $inputSucursal->formato = 'entero';
        if ($row['eba_id'] == 4 || $row['eba_id'] == 6 || $row['eba_id'] == 7 || !$this->escritura || $vConfirmacion == 'si') {
            $inputSucursal->disabled = true;
        }

        $inputNombre = new StdClass();
        $inputNombre->id = 'nombre';
        $inputNombre->name = 'nombre';
        $inputNombre->value = $row['cbs_nombre'];
        $inputNombre->enDiv = true;
        $inputNombre->label = 'Nombre';
        $inputNombre->maxlength = 500;
        if ($row['eba_id'] == 4 || $row['eba_id'] == 7 || !$this->escritura || $vConfirmacion == 'si') {
            $inputNombre->disabled = true;
        }

        $inputAlias = new StdClass();
        $inputAlias->id = 'alias';
        $inputAlias->name = 'alias';
        $inputAlias->value = $row['cbs_alias'];
        $inputAlias->enDiv = true;
        $inputAlias->label = 'Alias';
        $inputAlias->maxlength = 20;
        if ($row['eba_id'] == 4 || $row['eba_id'] == 7 || !$this->escritura || $vConfirmacion == 'si') {
            $inputAlias->disabled = true;
        }

        $inputCliente = new StdClass();
        $inputCliente->id = 'cliente';
        $inputCliente->name = 'cliente';
        $inputCliente->value = $row['cbs_cliente'];
        $inputCliente->enDiv = true;
        $inputCliente->label = 'Cliente';
        $inputCliente->maxlength = '10';
        $inputCliente->formato = 'entero';
        if ($row['eba_id'] == 4 || $row['eba_id'] == 6 || $row['eba_id'] == 7 || !$this->escritura || $vConfirmacion == 'si') {
            $inputCliente->disabled = true;
        }

        $inputContrato = new StdClass();
        $inputContrato->id = 'contrato';
        $inputContrato->name = 'contrato';
        $inputContrato->value = $row['cbs_contrato'];
        $inputContrato->enDiv = true;
        $inputContrato->formato = 'entero';
        $inputContrato->label = 'Contrato';
        $inputContrato->maxlength = 10;
        $numeroBx = substr($row['cbs_id'], 0, 3);
        if ($numeroBx != 002 || $row['eba_id'] == 4 || $row['eba_id'] == 6 || $row['eba_id'] == 7 || !$this->escritura || $vConfirmacion == 'si') {
            $inputContrato->disabled = true;
        }

        $inputObservacion = new StdClass();
        $inputObservacion->id = 'obs';
        $inputObservacion->name = 'obs';
        $inputObservacion->value = $row['cbs_observacion'];
        $inputObservacion->enDiv = true;
        $inputObservacion->label = 'Observaciones';
        $inputObservacion->rows = 2;
        $inputObservacion->cols = 12;
        $inputObservacion->maxlength = 200;
        if ($row['eba_id'] == 4 || $row['eba_id'] == 7 || !$this->escritura || $vConfirmacion == 'si') {
            $inputObservacion->disabled = true;
        }

        $this->setSql('SELECT ter_nombre FROM ' . BD_TESORERIA . '.cat_TipoRecurso WHERE ter_id = ?;');
        $this->consultar([$row['ter_id']]);
        $valor = $this->extraer();

        $inputTRecurso = new StdClass();
        $inputTRecurso->id = 'trecurso';
        $inputTRecurso->name = 'trecurso';
        $inputTRecurso->value = $valor['ter_nombre'];
        $inputTRecurso->enDiv = true;
        $inputTRecurso->label = 'Tipo de Recurso';
        $inputTRecurso->disabled = true;

        $this->setSql('SELECT eba_nombre FROM ' . BD_TESORERIA . '.cat_EstadoCuenta where eba_id = ?;');
        $this->consultar([$row['eba_id']]);
        $valor = $this->extraer();

        $inputECuenta = new StdClass();
        $inputECuenta->id = 'ecuenta';
        $inputECuenta->name = 'ecuenta';
        $inputECuenta->value = $valor['eba_nombre'];
        $inputECuenta->enDiv = true;
        $inputECuenta->label = 'Estado de Cuenta';
        $inputECuenta->disabled = true;

        $html = '
            <fieldset>
                <legend>Editar</legend>
                <div class="row">
                    <fieldset class="col-md-3 col-xs-3 mb-2" id="">
                        ' . $this->ponInput($inputFecha) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3 mb-2" id="">
                        ' . $this->ponInput($inputFechaCancelacion) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 mb-2" id="">
                        ' . $this->ponInput($inputClabe) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3 mb-2" id="">
                        ' . $this->ponInput($inputNBanco) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3 mb-2" id="">
                        ' . $this->ponInput($inputBancoP) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputCuenta) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputCtaCargo) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputArmoniChec) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputCuentaInver) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputCtaCargoInver) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputArmoniInver) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputCuentaInter) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputSucursal) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputAlias) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4 mb-2" id="">
                        ' . $this->ponInput($inputNombre) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 mb-2" id="">
                        ' . $this->ponInput($inputCliente) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6 mb-2" id="">
                        ' . $this->ponInput($inputContrato) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6 mb-2" id="">
                        ' . $this->ponInput($inputTRecurso) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6 mb-2" id="">
                        ' . $this->ponInput($inputECuenta) . '
                    </fieldset>
                </div>
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12" id="">
                        ' . $this->ponInput($inputObservacion) . '
                    </fieldset>
                </div>
            </fieldset>
        ';
        $this->desconectar();
        $this->mixHtml($html);
        $this->mixScript('
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ\u00f1\u00d1\d]/;
            $("#alias").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#obs").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
            $("#nombre").keypress(function(e) {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;
                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            });
        ');
    }

    private function deshabilitarCtaBancaria()
    {
        $this->prm->fechaDes = (isset($this->prm->fechaDes)) ? $this->prm->fechaDes : '';
        $conexion = new AccesoDatos();
        $conexion->conectar();

        $params = [$this->prm->clabe];
        $conexion->setSql('SELECT cbs_fechaApertura FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
        WHERE cbs_id = ?;');
        $conexion->consultar($params);
        $row = $conexion->extraer();

        //2001-04-25
        $fechaAp = $row['cbs_fechaApertura'];

        if ($this->prm->fechaDes == '') {
            $this->mixScript("notify('No se insertó la fecha de cancelación', 'warning');");
            return 0;
        }
        $hoy = getdate();
        if ($hoy['year'] < substr($this->prm->fechaDes, -4) || ($hoy['year'] == substr($this->prm->fechaDes, -4) && $hoy['mon'] < substr($this->prm->fechaDes, 0, 2)) || ($hoy['year'] == substr($this->prm->fechaDes, -4) && $hoy['mon'] == substr($this->prm->fechaDes, 0, 2) && $hoy['mday'] < substr($this->prm->fechaDes, 3, 2))) {
            $this->mixScript("notify('Fecha no Válida', 'warning');");
            return 0;
        }

        if (substr($this->prm->fechaDes, -4) < substr($fechaAp, 0, 4) || (substr($this->prm->fechaDes, -4) == substr($fechaAp, 0, 4) && substr($this->prm->fechaDes, 0, 2) < substr($fechaAp, 5, 2)) || (substr($this->prm->fechaDes, -4) == substr($fechaAp, 0, 4) && substr($this->prm->fechaDes, 0, 2) == substr($fechaAp, 5, 2) && substr($this->prm->fechaDes, 3, 2) < substr($fechaAp, -2))) {
            $this->mixScript("notify('Fecha no Válida', 'warning');");
            return 0;
        }

        $this->prm->fechaDes = substr($this->prm->fechaDes, -4) . '-' . substr($this->prm->fechaDes, 0, 2) . '-' . substr($this->prm->fechaDes, 3, 2);

        $this->prm2 = new stdClass();
        $this->prm2->cbs_id = $this->prm->clabe;
        $this->prm2->fechaCancelacion = $this->prm->fechaDes;
        $this->prm2->eba_id = 7;
        $this->prm2->abc = 'c';
        CuentaBancariaSefin::abc($this->prm2);

        $this->mixScript(
            '
            notify("La cuenta se ha deshabilitado", "success");
            comunica("' . $this->pantalla . '", {
                metodo: "busqueda"
            })'
        );
    }

    private function validaEditar()
    {
        $this->prm->fecha = (isset($this->prm->fecha)) ? $this->prm->fecha : '';
        $this->prm->sucursal = (isset($this->prm->sucursal)) ? $this->prm->sucursal : '';
        $this->prm->alias = (isset($this->prm->alias)) ? $this->prm->alias : '';
        $this->prm->nombre = (isset($this->prm->nombre)) ? $this->prm->nombre : '';
        $this->prm->cliente = (isset($this->prm->cliente)) ? $this->prm->cliente : '';
        $this->prm->contrato = (isset($this->prm->contrato)) ? $this->prm->contrato : '';
        $this->prm->obs = (isset($this->prm->obs)) ? $this->prm->obs : '';
        $this->prm->clabe = (isset($this->prm->clabe)) ? $this->prm->clabe : '';
        $this->prm->ebaID = (isset($this->prm->ebaID)) ? $this->prm->ebaID : '';
        $this->prm->CtaCargo = (isset($this->prm->CtaCargo)) ? $this->prm->CtaCargo : '';
        $this->prm->cgc_id = substr(strval($this->prm->CtaCargo), 5, -3);
        $this->prm->cta_id = substr(strval($this->prm->CtaCargo), 7, 8);
        $this->prm->cbs_cuenta = (isset($this->prm->cbs_cuenta)) ? $this->prm->cbs_cuenta : ''; //cuenta cheques

        //Cuenta Cargo Inversión
        $this->prm->CtaCargoInver = (isset($this->prm->CtaCargoInver)) ? $this->prm->CtaCargoInver : '';
        $this->prm->CtaCargoInver_cgc_id = substr(strval($this->prm->CtaCargoInver), 5, -3);
        $this->prm->CtaCargoInver_cta_id = substr(strval($this->prm->CtaCargoInver), 7, 8);

        $this->prm->tcs_id = (isset($this->prm->tcs_id)) ? $this->prm->tcs_id : 2;
        $cuentaBx = substr($this->prm->clabe, 0, 3);
        //Cuenta Inversión
        $this->prm->cbs_cuentaBanco = (isset($this->prm->cbs_cuentaBanco)) ? $this->prm->cbs_cuentaBanco : '';
        //Armonizada Cheque
        $this->prm->armoniChec = (isset($this->prm->armoniChec)) ? $this->prm->armoniChec : '';
        $this->prm->armoniChec_cta_id = substr(strval($this->prm->armoniChec), 14, 13);
        //Armonizada Inversión
        $this->prm->armoniInver = (isset($this->prm->armoniInver)) ? $this->prm->armoniInver : '';
        $this->prm->armoniInver_cta_id = substr(strval($this->prm->armoniInver), 14, 13);

        if ($this->prm->fecha == '') {
            $this->mixScript("notify('Falta Fecha', 'warning');");
            return 0;
        }
        $hoy = getdate();
        if (
            (
                $hoy['mday'] < substr($this->prm->fecha, 0, 2)
                && $hoy['mon'] == substr($this->prm->fecha, 3, 2)
                && $hoy['year'] < substr($this->prm->fecha, -4)
            )
            || (
                $hoy['mon'] < substr($this->prm->fecha, 3, 2)
                && $hoy['year'] < substr($this->prm->fecha, -4)
            )
            || $hoy['year'] < substr($this->prm->fecha, -4)
        ) {
            $this->mixScript("notify('Fecha no Válida', 'warning');");
            return 0;
        }
        if ($this->prm->nombre == '') {
            $this->mixScript("notify('Falta el Nombre de la Cuenta', 'warning');");
            return 0;
        }
        if ($this->prm->alias == '') {
            $this->mixScript("notify('Falta el Alias de la Cuenta', 'warning');");
            return 0;
        }
        if ($this->prm->cliente == '') {
            $this->mixScript("notify('Falta el Número de Cliente', 'warning');");
            return 0;
        }
        if (strlen($this->prm->cliente) < 6) {
            $this->mixScript("notify('Número de Cliente No Válido', 'warning');");
            return 0;
        }
        if ($this->prm->sucursal == '') {
            $this->mixScript("notify('Falta Sucursal', 'warning');");
            return 0;
        }
        if ($this->prm->contrato == '' && $cuentaBx == 002) {
            $this->mixScript("notify('Falta el Número de Contrato', 'warning');");
            return 0;
        }
        if (strlen($this->prm->contrato) < 10 && $cuentaBx == 002) {
            $this->mixScript("notify('Número de Contrato No Válido', 'warning');");
            return 0;
        }

        $this->prm2 = new stdClass();
        if ($cuentaBx == 002 && $this->prm->ebaID != 6) {
            $this->prm2->contrato = $this->prm->contrato;
        }
        if ($this->prm->ebaID != 6) {
            $this->prm2->sucursal = $this->prm->sucursal;
            $this->prm2->cliente = $this->prm->cliente;
            $this->prm2->fechaApertura = $this->prm->fecha;
        }
        $this->prm2->alias = $this->prm->alias;
        $this->prm2->nombre = $this->prm->nombre;
        $this->prm2->cbs_id = $this->prm->clabe;
        $this->prm2->observacion = $this->prm->obs;
        $this->prm2->bit_complementario = $this->prm->obs;
        $this->prm2->abc = 'c';
        CuentaBancariaSefin::abc($this->prm2);
        $params = [$this->prm->cgc_id, $this->prm->cta_id, $this->prm->clabe, $this->prm->cbs_cuenta];
        //update vin_CuentaSap con tcs_id=2 [CHEQUES]
        //Revisamos que sea necesario el update, sino es necesario se salta
        $this->setSql('SELECT cbs_cuentaBanco
            FROM ' . BD_TESORERIA . '.vin_CuentaSap
            WHERE tcs_id = 2
            AND cgc_id = ?
            AND cta_id = ?
            AND cbs_id = ?
            AND cbs_cuentaBanco = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();

        if ($this->getNumeroRegistros() < 1 || is_null($valor)) {
            $this->prm3 = new stdClass();
            $this->prm3->tcs_id = $this->prm->tcs_id;
            $this->prm3->cbs_id = $this->prm->clabe;
            $this->prm3->cgc_id = $this->prm->cgc_id;
            $this->prm3->cta_id = $this->prm->cta_id;
            $this->prm3->cbs_cuentaBanco = $this->prm->cbs_cuenta;
            $this->prm3->abc = 'c';
            CuentaSap::abc($this->prm3);
        }

        $params = [$this->prm->clabe];
        //update vin_CuentaSap con tcs_id=1 -- Cuenta inversión y Cuenta Cargo InversióN
        //Revisamos que exista un registro para editar, sino, se insertará el que ingrese el usuario
        $this->setSql('SELECT cbs_cuentaBanco, concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) CtaCargoInver
            from ' . BD_TESORERIA . '.vin_CuentaSap
            WHERE tcs_id = 1
            AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();

        if ($this->getNumeroRegistros() < 1 || is_null($valor)) {
            //insert - Cuenta inversión, Cuenta cargo inversión
            if (
                $this->prm->cbs_cuentaBanco != ''
                && !empty($this->prm->CtaCargoInver)
            ) {
                //Comprobamos que exista un valor para guardar
                $this->prm4 = new stdClass();
                $this->prm4->cbs_id = $this->prm->clabe;
                $this->prm4->cbs_cuentaBanco = $this->prm->cbs_cuentaBanco;
                $this->prm4->ger_id = 1;
                $this->prm4->cgp_id = 1;
                $this->prm4->rub_id = 2;
                $this->prm4->cgcu_id = '0';
                $this->prm4->cgsc_id = 4;
                $this->prm4->cgc_id = $this->prm->CtaCargoInver_cgc_id;
                $this->prm4->cta_id = $this->prm->CtaCargoInver_cta_id;
                $this->prm4->ibs_id = 1;
                $this->prm4->tcs_id = 1;
                $this->prm4->consecutivo = 795;
                $this->prm4->abc = 'a';
                if (!empty($this->prm->cbs_cuentaBanco)) {
                    CuentaSap::abc($this->prm4);
                }
            } else {
                /**En caso de que el usuario no ingrese una cuenta cargo de inversion, pero si ingrese una cuenta de inversion
                 * el sistema invoca al metodo genera cuenta inversion sap, para que genere la cuenta cargo así como la armonizada.
                 */
                $params = [$this->prm->clabe];
                $this->setSql('SELECT cbs_cuenta 
                    FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
                    WHERE cbs_id = ?;');
                $this->conectar();
                $this->consultar($params);
                $valor = $this->extraer();
                $this->prm->cuenta = $valor['cbs_cuenta'];
                $this->prm->cuentain = $this->prm->cbs_cuentaBanco;
                //Pedimos que no se llame el llena tabla
                $this->generaInversionSap(false);
            }

        } else {
            $params = [
                $this->prm->cbs_cuentaBanco,
                $this->prm->CtaCargoInver_cgc_id,
                $this->prm->CtaCargoInver_cta_id,
                $this->prm->clabe,
            ];
            //update - cuenta inversión y cuenta cargo inversión
            //Revisamos que sea necesario el update, sino es necesario se salta
            $this->setSql('SELECT cbs_cuentaBanco
                FROM ' . BD_TESORERIA . '.vin_CuentaSap
                WHERE tcs_id = 1
                AND cbs_cuentaBanco = ?
                AND cgc_id = ?
                AND cta_id = ?
                AND cbs_id= ?;');
            $this->conectar();
            $this->consultar($params);
            $valor = $this->extraer();
            if ($this->getNumeroRegistros() < 1 || is_null($valor)) {
                $this->prm4 = new stdClass();
                $this->prm4->tcs_id = 1;
                $this->prm4->cbs_id = $this->prm->clabe;
                $this->prm4->cbs_cuentaBanco = $this->prm->cbs_cuentaBanco;
                $this->prm4->cgc_id = $this->prm->CtaCargoInver_cgc_id;
                $this->prm4->cta_id = $this->prm->CtaCargoInver_cta_id;
                $this->prm4->abc = 'c';
                CuentaSap::abc($this->prm4);
            }
        }
        //update armonizada cheque
        //Revisamos que se cuente con el registro en cg_PlanCuentas
        $aux = $this->prm->armoniChec;
        $this->setSql('SELECT cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id
            cgsc_id, cgc_id, cta_id, cta_cuenta, cta_nombre, cta_descripcion, agc_id, tem_carga, usr_id
            FROM ' . BD_CTRL . '.cg_PlanCuentas
            WHERE cta_cuenta = ? ;');
        $this->conectar();
        $this->consultar([$aux]);
        $valor = $this->extraer();

        if ($this->getNumeroRegistros() < 1 || is_null($valor)) {
            //insert cg_PlanCuentas
            $this->prms = new stdClass();
            $this->prms->abc = 'a';
            $this->prms->cta_gpo_id = 1;
            $this->prms->cta_ur_id = 16;
            $this->prms->cta_ue_id = 005;
            $this->prms->ger_id = 1;
            $this->prms->cgp_id = 1;
            $this->prms->rub_id = 1;
            $this->prms->cgcu_id = 2;
            $this->prms->cgsc_id = 1;
            $this->prms->cgc_id = substr($aux, 10, -5);
            $this->prms->cta_id = '' . substr($aux, 14) . '';
            $this->prms->cuenta = $this->prms->ger_id . '.' . $this->prms->cgp_id . '.' . $this->prms->rub_id . '.' . $this->prms->cgcu_id . '.' . $this->prms->cgsc_id . '.' . $this->prms->cgc_id . '.' . $this->prms->cta_id;
            $this->prms->nombre = 'CTA-' . substr($this->prm->clabe, -12, -1);
            $this->prms->descripcion = '';
            $this->prms->agc_id = 'GRAL';
            $this->prms->carga = '';
            LanCuentas::abc($this->prms);

            //update vin_SefinPlanCuenta
            $this->prm6 = new stdClass();
            $this->prm6->tcs_id = 2;
            $this->prm6->cta_ue_id = 005;
            $this->prm6->cbs_id = $this->prm->clabe;
            $this->prm6->cta_id = $this->prm->armoniChec_cta_id;
            $this->prm6->abc = 'c';
            SefinPlanCuenta::abc($this->prm6);
        } else {
            //update vin_SefinPlanCuenta
            $this->prm5 = new stdClass();
            $this->prm5->tcs_id = 2;
            $this->prm5->cta_ue_id = 005;
            $this->prm5->cbs_id = $this->prm->clabe;
            $this->prm5->cta_id = $this->prm->armoniChec_cta_id;
            $this->prm5->abc = 'c';
            SefinPlanCuenta::abc($this->prm5);
        }
        $params = [$this->prm->clabe];
        //update armonizada inversión
        //Revisamos que se cuente con el registro en cg_PlanCuentas
        $aux = $this->prm->armoniInver;
        $this->setSql('
            SELECT concat(ger_id, ".", cgp_id, ".", rub_id, ".", cgcu_id, ".", cgsc_id, ".", cgc_id, ".", cta_id) armoniInver
            FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta
            WHERE tcs_id = 1
            AND cbs_id = ?;');
        $this->conectar();
        $this->consultar($params);
        $valor = $this->extraer();

        if ($this->getNumeroRegistros() < 1 || is_null($valor)) {
            //insert en cg_PlanCuentas
            $this->prms = new stdClass();
            $this->prms->cta_gpo_id = 1;
            $this->prms->cta_ur_id = 16;
            $this->prms->cta_ue_id = 005;
            $this->prms->ger_id = 1;
            $this->prms->cgp_id = 1;
            $this->prms->rub_id = 1;
            $this->prms->cgcu_id = 4;
            $this->prms->cgsc_id = 1;
            $this->prms->cgc_id = substr($aux, 10, -5);
            $this->prms->cta_id = '' . substr($aux, 14) . '';
            $this->prms->abc = 'a';
            $this->prms->cuenta = $this->prms->ger_id . '.' . $this->prms->cgp_id . '.' . $this->prms->rub_id . '.' .
            $this->prms->cgcu_id . '.' . $this->prms->cgsc_id . '.' . $this->prms->cgc_id . '.' . $this->prms->cta_id;
            $this->prms->agc_id = 'GRAL';
            $alia = (isset($alia)) ? $alia : '';
            $this->prms->nombre = 'CTA-' . substr($this->prm->clabe, -12, -1) . '-' . $alia;
            $this->prms->descripcion = '';
            $this->prms->carga = '';
            $params = [$this->prms->cuenta];
            $this->setSql(' SELECT cta_cuenta,cta_nombre
                FROM ' . BD_CTRL . '.cg_PlanCuentas
                WHERE cta_cuenta = ?;');
            $this->conectar();
            $this->consultar($params);
            $valor = $this->extraer();
            if (($this->getNumeroRegistros() < 1 || is_null($valor)) && !empty($aux)) {
                LanCuentas::abc($this->prms);
            }
            $params = [$this->prm->clabe];
            $this->setSql('SELECT concat(ger_id, ".", cgp_id, ".", rub_id, ".", cgcu_id, ".", cgsc_id, ".", cgc_id, ".", cta_id) armoniInver
                FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta
                WHERE tcs_id = 1
                AND cbs_id = ?;');
            $this->conectar();
            $this->consultar($params);
            $valor = $this->extraer();
            if (($this->getNumeroRegistros() < 1 || is_null($valor)) && !empty($aux)) {
                $this->prms->cbs_id = $this->prm->clabe;
                $this->prms->tcs_id = 1;
                SefinPlanCuenta::abc($this->prms);
            } else {
                //update vin_SefinPlanCuenta
                if (!is_null($valor)) {
                    $this->prm7 = new stdClass();
                    $this->prm7->tcs_id = 1;
                    $this->prm7->cta_ue_id = 005;
                    $this->prm7->cbs_id = $this->prm->clabe;
                    $this->prm7->ger_id = substr(strval($this->prm->armoniInver), 0, 1);
                    $this->prm7->cgp_id = substr(strval($this->prm->armoniInver), 2, 1);
                    $this->prm7->rub_id = substr(strval($this->prm->armoniInver), 4, 1);
                    $this->prm7->cgcu_id = substr(strval($this->prm->armoniInver), 6, 1);
                    $this->prm7->cgcs_id = substr(strval($this->prm->armoniInver), 8, 1);
                    $this->prm7->cgc_id = substr(strval($this->prm->armoniInver), 10, 3);
                    $this->prm7->cta_id = $this->prm->armoniInver_cta_id;
                    $this->prm7->abc = 'c';
                    SefinPlanCuenta::abc($this->prm7);
                }
            }
        } else {
            $params = [$this->prm->armoniInver];
            if (!empty($this->prm->cbs_cuentaBanco)) {
                $params = [$this->prm->armoniInver ?? $valor['armoniInver']];
                $this->prm->armoniInver = $this->prm->armoniInver != '' ? $this->prm->armoniInver : $valor['armoniInver'];
            }

            //Revisamos que se cuente con el registro en cg_PlanCuentas
            $this->setSql('SELECT cta_cuenta
                FROM ' . BD_CTRL . '.cg_PlanCuentas
                WHERE cta_cuenta = ? ;');
            $this->conectar();
            $this->consultar($params);
            $this->extraer();

            if ($this->getNumeroRegistros() < 1) {
                //insert en cg_PlanCuentas
                $this->prms = new stdClass();
                $this->prms->cta_gpo_id = 1;
                $this->prms->cta_ur_id = 16;
                $this->prms->cta_ue_id = 005;
                $this->prms->ger_id = substr(strval($this->prm->armoniInver), 0, 1);
                $this->prms->cgp_id = substr(strval($this->prm->armoniInver), 2, 1);
                $this->prms->rub_id = substr(strval($this->prm->armoniInver), 4, 1);
                $this->prms->cgcu_id = substr(strval($this->prm->armoniInver), 6, 1);
                $this->prms->cgsc_id = substr(strval($this->prm->armoniInver), 8, 1);
                $this->prms->cgc_id = substr(strval($this->prm->armoniInver), 10, 3);
                $this->prms->cta_id = $this->prm->armoniInver_cta_id;
                $this->prms->abc = 'a';
                $this->prms->cuenta = $this->prm->armoniInver;
                $this->prms->agc_id = 'GRAL';
                $this->prms->nombre = 'CTA-' . substr($this->prm->clabe, -12, -1);
                $this->prms->descripcion = '';
                $this->prms->carga = '';
                LanCuentas::abc($this->prms); //
            }
            //update vin_SefinPlanCuenta
            $this->prm7 = new stdClass();
            $this->prm7->tcs_id = 1;
            $this->prm7->cta_gpo_id = 1;
            $this->prm7->cta_ur_id = 16;
            $this->prm7->cta_ue_id = 005;
            $this->prm7->cbs_id = $this->prm->clabe;
            $this->prm7->ger_id = substr(strval($this->prm->armoniInver), 0, 1);
            $this->prm7->cgp_id = substr(strval($this->prm->armoniInver), 2, 1);
            $this->prm7->rub_id = substr(strval($this->prm->armoniInver), 4, 1);
            $this->prm7->cgcu_id = substr(strval($this->prm->armoniInver), 6, 1);
            $this->prm7->cgsc_id = substr(strval($this->prm->armoniInver), 8, 1);
            $this->prm7->cgc_id = substr(strval($this->prm->armoniInver), 10, 3);
            $this->prm7->cta_id = $this->prm->armoniInver_cta_id;
            $this->prm7->abc = 'c';
            SefinPlanCuenta::abc($this->prm7);
        }
        $this->busqueda();
    }

    private function pintaNuevaCuentaBancaria()
    {
        if (!$this->escritura) {
            $this->mixScript('notify("No tiene permitido dar de alta ninguna cuenta", "error");');
        }

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '", {
                metodo: "busqueda"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'guardar';
        $btnGuardar->name = 'guardar';
        $btnGuardar->text = 'Guardar';
        $btnGuardar->value = 'Guardar';
        $btnGuardar->type = 'button';
        $btnGuardar->class = 'me-1';
        $btnGuardar->color = 'verde';
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->click = '
            comunica("' . $this->pantalla . '", {
                metodo:"insertar",
                clabeinterbancaria:$("#clabeinterbancaria").val(),
                clabeinterbancariabn:$("#clabeinterbancariabn").val(),
                fechaApertura:$("#fechaApertura").val(),
                nombrebanco:$("#nombrebanco").val(),
                numerocontratobx:$("#numerocontratobx").val(),
                tiporecurso:$("#tiporecurso").val(),
                sucursal:$("#sucursal").val(),
                nombreCuenta:$("#nombreCuenta").val(),
                aliasCuenta:$("#aliasCuenta").val(),
                numerocliente:$("#numerocliente").val(),
                observacion:$("#observacion").val(),
                tipofondo:$("#tipofondo").val(),
                recaudadora:$("#recaudadora:checked").val(),
                concentradora:$("#concentradora:checked").val(),
                pagadora:$("#pagadora:checked").val(),
                receptora:$("#receptora:checked").val(),
                inversion:$("#inversion:checked").val()
            })';
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardar));
        }

        $inputFechaApertura = new StdClass();
        $inputFechaApertura->id = 'fechaApertura';
        $inputFechaApertura->name = 'fechaApertura';
        $inputFechaApertura->enDiv = true;
        $inputFechaApertura->label = 'Fecha de Apertura';
        $inputFechaApertura->formato = 'fecha';
        $inputFechaApertura->required = true;

        $inputNombreBanco = new StdClass();
        $inputNombreBanco->id = 'nombrebanco';
        $inputNombreBanco->name = 'nombrebanco';
        $inputNombreBanco->label = 'Nombre del Banco';
        $inputNombreBanco->class = 'limpiar';
        $inputNombreBanco->search = true;
        $inputNombreBanco->required = true;
        $inputNombreBanco->disabled = true;

        $inputNumeroPlaza = new StdClass();
        $inputNumeroPlaza->id = 'numeroplaza';
        $inputNumeroPlaza->name = 'numeroplaza';
        $inputNumeroPlaza->enDiv = true;
        $inputNumeroPlaza->label = 'Plaza';
        $inputNumeroPlaza->disabled = true;

        $inputSucursal = new StdClass();
        $inputSucursal->id = 'sucursal';
        $inputSucursal->name = 'sucursal';
        $inputSucursal->placeholder = 'Número 2 - 5 Dígitos';
        $inputSucursal->enDiv = true;
        $inputSucursal->label = 'Sucursal';
        $inputSucursal->maxlength = '5';
        $inputSucursal->minlength = '2';
        $inputSucursal->formato = 'entero';
        $inputSucursal->required = true;

        $inputNumeroContratoBx = new StdClass();
        $inputNumeroContratoBx->id = 'numerocontratobx';
        $inputNumeroContratoBx->name = 'numerocontratobx';
        $inputNumeroContratoBx->placeholder = 'Máximo 16 dígitos';
        $inputNumeroContratoBx->enDiv = true;
        $inputNumeroContratoBx->maxlength = '16';
        $inputNumeroContratoBx->formato = 'entero';
        $inputNumeroContratoBx->disabled = true;

        $inputTipoRecurso = new StdClass();
        $inputTipoRecurso->sql = 'SELECT ter_nombre FROM ' . BD_TESORERIA . '.cat_TipoRecurso;';
        $inputTipoRecurso->id = 'tiporecurso';
        $inputTipoRecurso->name = 'tiporecurso';
        $inputTipoRecurso->label = 'Tipo de Recurso';
        $inputTipoRecurso->required = true;

        $inputTipoFondo = new StdClass();
        $inputTipoFondo->sql = 'SELECT tfn_nombre FROM ' . BD_TESORERIA . '.cat_TipoFondo;';
        $inputTipoFondo->id = 'tipofondo';
        $inputTipoFondo->name = 'tipofondo';
        $inputTipoFondo->label = 'Tipo de Fondo';
        $inputTipoFondo->required = true;

        $inputObservacion = new StdClass();
        $inputObservacion->id = 'observacion';
        $inputObservacion->name = 'observacion';
        $inputObservacion->placeholder = 'Observaciones';
        $inputObservacion->enDiv = true;
        $inputObservacion->label = 'Observaciones';
        $inputObservacion->rows = 2;
        $inputObservacion->cols = 12;
        $inputObservacion->required = true;

        $inputCuentaCheques = new StdClass();
        $inputCuentaCheques->id = 'cuentacheques';
        $inputCuentaCheques->name = 'cuentacheques';
        $inputCuentaCheques->placeholder = '';
        $inputCuentaCheques->enDiv = true;
        $inputCuentaCheques->label = 'Cuenta de Cheques';
        $inputCuentaCheques->formato = 'entero';
        $inputCuentaCheques->maxlength = 11;

        $inputCuentaInversion = new StdClass();
        $inputCuentaInversion->id = 'cuentainversion';
        $inputCuentaInversion->name = 'cuentainversion';
        $inputCuentaInversion->placeholder = '';
        $inputCuentaInversion->enDiv = true;
        $inputCuentaInversion->formato = 'entero';
        $inputCuentaInversion->disabled = true;
        $inputCuentaInversion->maxlength = 12;

        $inputCuentaChequesIN = new StdClass();
        $inputCuentaChequesIN->id = 'cuentachequesIN';
        $inputCuentaChequesIN->name = 'cuentachequesIN';
        $inputCuentaChequesIN->placeholder = '';
        $inputCuentaChequesIN->enDiv = true;
        $inputCuentaChequesIN->label = 'Cuenta de Cheques';
        $inputCuentaChequesIN->formato = 'entero';
        $inputCuentaChequesIN->maxlength = 11;

        $inputCuentaInversionIN = new StdClass();
        $inputCuentaInversionIN->id = 'cuentainversionIN';
        $inputCuentaInversionIN->name = 'cuentainversionIN';
        $inputCuentaInversionIN->placeholder = '';
        $inputCuentaInversionIN->enDiv = true;
        $inputCuentaInversionIN->formato = 'entero';
        $inputCuentaInversionIN->disabled = true;
        $inputCuentaInversionIN->maxlength = 10;

        $btnCuentaInversion = new stdClass();
        $btnCuentaInversion->id = 'capturaInformacion';
        $btnCuentaInversion->name = 'capturaInformacion';
        $btnCuentaInversion->value = 'Capturar Información';
        $btnCuentaInversion->icon = 'solid fa-share-from-square me-2';
        $btnCuentaInversion->style = 'display:none';
        $btnCuentaInversion->color = 'verde';
        $btnCuentaInversion->click = '
            comunica("' . $this->pantalla . '",{
            metodo:"generaInversionSap",
            cuenta:$("#cuentacheques").val(),
            cuentain:$("#cuentainversion").val()
        })';

        $btnCuentaInversionIN = new stdClass();
        $btnCuentaInversionIN->id = 'capturaInformacionIN';
        $btnCuentaInversionIN->name = 'capturaInformacionIN';
        $btnCuentaInversionIN->value = 'Capturar Información';
        $btnCuentaInversionIN->color = 'verde';
        $btnCuentaInversionIN->class = 'mt-4';
        $btnCuentaInversionIN->icon = 'solid fa-share-from-square me-2';
        $btnCuentaInversionIN->style = 'display:none';
        $btnCuentaInversionIN->click = '
            comunica("' . $this->pantalla . '",{
            metodo:"generaInteres",
            cuenta:$("#cuentachequesIN").val(),
            cuentain:$("#cuentainversionIN").val()
        })';

        $html = '
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <legend>Nueva Cuenta</legend>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Clabe Interbancaria</label>
                            <input type="text"
                                maxlength="18"
                                placeholder="Número 18 Dígitos"
                                name="clabeinterbancaria"
                                id="clabeinterbancaria"
                                onkeypress="return justNumbers(event);"
                                onkeyup="escribe()"
                                onblur="getBancoAndPlaza()"
                                class="form-control"
                            />
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputNombreBanco) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputNumeroPlaza) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Nombre de la Cuenta (Descripción)</label>
                            <textarea rows="2"
                                cols="12" maxlength="500"
                                placeholder="Nombre de la Cuenta (Descripción)"
                                name="nombreCuenta"
                                id="nombreCuenta"
                                onkeypress="return alfanumNombreCuenta(event);"
                                class="form-control"></textarea>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Alias de la Cuenta</label>
                            <input type="text"
                                maxlength="20"
                                placeholder="Alias de la Cuenta"
                                name="aliasCuenta" id="aliasCuenta"
                                onkeypress="return alfanum(event);"
                                class="form-control"
                            />
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Número de Cliente</label>
                            <input type="text"
                                maxlength="10"
                                minlength="6"
                                placeholder="Alfanumérico 6 - 10 Dígitos"
                                name="numerocliente" id="numerocliente"
                                onkeypress="return alfanum(event);"
                                class="form-control"
                            />
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputSucursal) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                            <table class="col-md-6 col-xs-6 ">
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="inversion" value="1">
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Inversora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="recaudadora" value="1">
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Recaudadora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="concentradora" value="1">
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Concentradora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="pagadora" value="1">
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Pagadora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="receptora" value="1">
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Receptora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-11 col-xs-11 ">
                            ' . $this->ponInput($inputFechaApertura) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                            <table class="col-md-11 col-xs-11 ">
                                <tr>
                                    <th colspan="2">
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Número de Contrato (Solo Banamex)</label>
                                        </fieldset>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="check_apertura" >
                                        </fieldset>
                                    </td>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            ' . $this->ponInput($inputNumeroContratoBx) . '
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11 " id="">
                            ' . $this->ponCombo($inputTipoRecurso) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11 " id="">
                            ' . $this->ponCombo($inputTipoFondo) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11 " id="">
                            <label>Observaciones</label>
                            <textarea rows="2"
                                cols="12"
                                maxlength="200"
                                placeholder="Observaciones"
                                name="observacion" id="observacion"
                                onkeypress="return alfanum(event);"
                                class="form-control"></textarea>
                        </fieldset>
                    </div>
                </div>

                <div class="col-md-6 col-xs-12">
                    <div class="row mb-2">
                        <legend>Cuenta Contable</legend>
                        <fieldset class="col-md-12 col-xs-12" id="idTcuentaContable">
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <legend>Capturar Cuenta de Inversión</legend>
                        <fieldset class="col-md-12 col-xs-12">
                            ' . $this->ponInput($inputCuentaCheques) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                            <table class="col-md-12 col-xs-12">
                                <tr>
                                    <th colspan="2">
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Cuenta de Inversión</label>
                                        </fieldset>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="check_aperturaInver">
                                        </fieldset>
                                    </td>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            ' . $this->ponInput($inputCuentaInversion) . '
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                            ' . $this->ponBoton($btnCuentaInversion) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <legend>Cuenta Contable</legend>
                        <fieldset class="col-md-12 col-xs-12 " id="idTcuentaContableII">
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <legend>Capturar Cuenta de Interes</legend>
                        <fieldset class="col-md-12 col-xs-12">
                            ' . $this->ponInput($inputCuentaChequesIN) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                            <table class="col-md-12 col-xs-12">
                                <tr>
                                    <th colspan="2">
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Cuenta de Interes</label>
                                        </fieldset>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="check_aperturaInverIN" >
                                        </fieldset>
                                    </td>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            ' . $this->ponInput($inputCuentaInversionIN) . '
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </fielset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                            ' . $this->ponBoton($btnCuentaInversionIN) . '
                        </fieldset>
                    </div>
                </div>
            </div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ\u00f1\u00d1\d]/;
            var permitirCaracteresAlfanumericosNombreCuenta = /^[a-zA-ZÀ-ÿ°!¡"#$%&/()=¿?\.\;\:\-\_\*\,\[\]\¨\u00f1\u00d1\d]/;
            function alfanum(e)
            {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            }
            function alfanumNombreCuenta(e)
            {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return permitirCaracteresAlfanumericosNombreCuenta.test(String.fromCharCode(keynum));
            }

            function justNumbers(e)
            {
                var evento = window.event || e;
                if ((evento.keyCode == 8))
                    return true;

                return /\d/.test(String.fromCharCode(evento.charCode));
            }
            function escribe() {
                if (document.getElementById("clabeinterbancaria").value.length == 3) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaBanco",
                        param: $("#clabeinterbancaria").val()
                    });
                }
                if (document.getElementById("clabeinterbancaria").value.length == 6) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaPlaza",
                        param: $("#clabeinterbancaria").val().substring(3)
                    });
                }
            }
            function getBancoAndPlaza() {
                clabeInterbancaria = document.getElementById("clabeinterbancaria").value;
                if (clabeInterbancaria.length >= 6) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaBanco",
                        param: clabeInterbancaria.substring(0,3),
                        clabeinterbancaria: clabeInterbancaria
                    });
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaPlaza",
                        param: clabeInterbancaria.substring(3,6)
                    });
                }
            }

            var z = $("#check_apertura");
            var y = $("#check_aperturaInver");
            var x = $("#check_aperturaInverIN");
            z.click(habilita);
            y.click(habilitaIn);
            x.click(habilitaInII);
            function habilita()
            {
                if (document.getElementById("nombrebanco").value == "BANAMEX, S.A.") {
                    var x = $("#numerocontratobx");
                    x.attr("disabled",!this.checked);
                }
            }
            function habilitaIn()
            {
                var x = $("#cuentainversion");
                x.attr("disabled",!this.checked);
                if (document.getElementById("capturaInformacion").style.display == "initial") {
                    document.getElementById("capturaInformacion").style.display = "none"
                } else {
                    document.getElementById("capturaInformacion").style.display = "initial";
                }
            }
            function habilitaInII()
            {
                var x = $("#cuentainversionIN");
                x.attr("disabled",!this.checked);
                if (document.getElementById("capturaInformacionIN").style.display == "initial") {
                    document.getElementById("capturaInformacionIN").style.display = "none"
                } else {
                    document.getElementById("capturaInformacionIN").style.display = "initial";
                }
            }
        ');

        //TablaContable1
        $this->setContenedor('idTcuentaContable');

        $tablaCC = new stdClass();
        $tablaCC->id = 'TcuentaContable';
        $tablaCC->titulo = 'Cuenta Contable';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CTA CHEQUES';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO PROPIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'CTA_CHEQUES';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'BAN_PROP';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ARMONI';
        array_push($trbody, $td);

        $tablaCC->encabezado = [$tr];
        $tablaCC->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tablaCC));

        //TablaContable2
        $this->setContenedor('idTcuentaContableII');

        $tablaCCII = new stdClass();
        $tablaCCII->id = 'TcuentaContableII';
        $tablaCCII->titulo = 'Cuenta Contable';

        $tr = [];

        $th = new stdClass();
        $th->texto = '#';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Cuenta de Inversión';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'Cuenta_Inversion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ARMONIZ';
        array_push($trbody, $td);

        $tablaCCII->encabezado = [$tr];
        $tablaCCII->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tablaCCII));
    }

    private function cuentaBancariaAnterior()
    {
        $this->prm->cbs_id = (isset($this->prm->cbs_id)) ? $this->prm->cbs_id : '';

        if (!$this->escritura) {
            $this->mixScript('notify("No tiene permitido dar de alta ninguna cuenta", "error");');
        }

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        // Regresa a la pantalla anterior
        $btnregresarindex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "busqueda"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'guardar';
        $btnGuardar->name = 'guardar';
        $btnGuardar->text = 'Guardar';
        $btnGuardar->value = 'Guardar';
        $btnGuardar->type = 'button';
        $btnGuardar->class = 'me-1';
        $btnGuardar->color = 'verde';
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"insertarTablaAnterior",
                clabeinterbancaria:$("#clabeinterbancaria").val(),
                fechaApertura:$("#fechaApertura").val(),
                nombrebanco:$("#nombrebanco").val(),
                numerocontratobx:$("#numerocontratobx").val(),
                tiporecurso:$("#tiporecurso").val(),
                sucursal:$("#sucursal").val(),
                nombreCuenta:$("#nombreCuenta").val(),
                aliasCuenta:$("#aliasCuenta").val(),
                numerocliente:$("#numerocliente").val(),
                observacion:$("#observacion").val(),
                tipofondo:$("#tipofondo").val(),
                recaudadora:$("#recaudadora:checked").val(),
                concentradora:$("#concentradora:checked").val(),
                pagadora:$("#pagadora:checked").val(),
                receptora:$("#receptora:checked").val(),
                cargoCheque:$("#CargoCheque").val(),
                armonizadaCheque:$("#ArmonizadaCheque").val(),
                inversion:$("#inversion:checked").val()
            })';
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardar));
        }

        $filtroSql = '';
        $params = [];
        if (!empty($this->prm->cbs_id)) {
            $filtroSql = 'WHERE cbs.cbs_id = ? ';
            $params = [$this->prm->cbs_id];
        }
        $sql = '
            SELECT
                cbs_fechaApertura, iba_id, cbs_id, cbs_sucursal, cbs_contrato, ter_id, tfn_id, cbs_nombre, cbs_alias, cbs_cliente, cbs_inversion, cbs_recaudadora, cbs_concentradora, cbs_pagadora, cbs_receptora, cbs_observacion
            FROM
                ' . BD_TESORERIA . '.ban_CuentaBancariaSefin cbs
            ' . $filtroSql . '
            ORDER BY cbs.usr_fecha DESC LIMIT 0,1
        ';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $row = $conexion->extraer();

        $inputFechaApertura = new StdClass();
        $inputFechaApertura->id = 'fechaApertura';
        $inputFechaApertura->name = 'fechaApertura';
        $inputFechaApertura->enDiv = true;
        $inputFechaApertura->label = 'Fecha de Apertura';
        $inputFechaApertura->formato = 'fecha';
        $inputFechaApertura->required = true;
        $inputFechaApertura->value = substr($row['cbs_fechaApertura'], -2) . '/' . substr($row['cbs_fechaApertura'], -5, 2) . '/' . substr($row['cbs_fechaApertura'], 0, 4);

        $conexion->setSql('SELECT iba_abreviatura FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria WHERE iba_id = ? ');
        $conexion->consultar([$row['iba_id']]);
        $banco = $conexion->extraer();

        $inputNombreBanco = new StdClass();
        $inputNombreBanco->id = 'nombrebanco';
        $inputNombreBanco->name = 'nombrebanco';
        $inputNombreBanco->label = 'Nombre del Banco';
        $inputNombreBanco->class = 'limpiar';
        $inputNombreBanco->search = true;
        $inputNombreBanco->required = true;
        $inputNombreBanco->disabled = true;
        $inputNombreBanco->value = $banco['iba_abreviatura'];

        $conexion->setSql('SELECT pbn_nombre FROM ' . BD_TESORERIA . '.cat_PlazaBancaria WHERE pbn_numero = ? ');
        $this->prm->clabeinterbancaria = $row['cbs_id'];

        $conexion->consultar([substr($row['cbs_id'], 3, 3)]);
        $plaza = $conexion->extraer();

        $inputNumeroPlaza = new StdClass();
        $inputNumeroPlaza->id = 'numeroplaza';
        $inputNumeroPlaza->name = 'numeroplaza';
        $inputNumeroPlaza->enDiv = true;
        $inputNumeroPlaza->label = 'Plaza';
        $inputNumeroPlaza->disabled = true;
        $inputNumeroPlaza->value = $plaza['pbn_nombre'];

        $inputSucursal = new StdClass();
        $inputSucursal->id = 'sucursal';
        $inputSucursal->name = 'sucursal';
        $inputSucursal->placeholder = 'Número 2 - 5 Dígitos';
        $inputSucursal->enDiv = true;
        $inputSucursal->label = 'Sucursal';
        $inputSucursal->maxlength = '5';
        $inputSucursal->minlength = '2';
        $inputSucursal->formato = 'entero';
        $inputSucursal->required = true;
        $inputSucursal->value = $row['cbs_sucursal'];

        $inputNumeroContratoBx = new StdClass();
        $inputNumeroContratoBx->id = 'numerocontratobx';
        $inputNumeroContratoBx->name = 'numerocontratobx';
        $inputNumeroContratoBx->placeholder = 'Máximo 16 dígitos';
        $inputNumeroContratoBx->enDiv = true;
        $inputNumeroContratoBx->maxlength = '16';
        $inputNumeroContratoBx->formato = 'entero';
        (($row['cbs_contrato'] != '') ? '' : $inputNumeroContratoBx->{'disabled'} = true);
        $inputNumeroContratoBx->value = ($row['cbs_contrato'] != '') ? $row['cbs_contrato'] : '';

        $conexion->setSql('SELECT ter_nombre,ter_id FROM ' . BD_TESORERIA . '.cat_TipoRecurso WHERE ter_id = ? ');
        $conexion->consultar([$row['ter_id']]);
        $tipo_recurso = $conexion->extraer();

        $inputTipoRecurso = new StdClass();
        $inputTipoRecurso->sql = 'SELECT ter_nombre FROM ' . BD_TESORERIA . '.cat_TipoRecurso;';
        $inputTipoRecurso->id = 'tiporecurso';
        $inputTipoRecurso->name = 'tiporecurso';
        $inputTipoRecurso->label = 'Tipo de Recurso';
        $inputTipoRecurso->required = true;
        $inputTipoRecurso->selecciona = $tipo_recurso['ter_nombre'];

        $conexion->setSql('SELECT tfn_nombre FROM ' . BD_TESORERIA . '.cat_TipoFondo WHERE tfn_id = ? ');
        $conexion->consultar([$row['tfn_id']]);
        $tipo_fondo = $conexion->extraer();

        $inputTipoFondo = new StdClass();
        $inputTipoFondo->sql = 'SELECT tfn_nombre FROM ' . BD_TESORERIA . '.cat_TipoFondo;';
        $inputTipoFondo->id = 'tipofondo';
        $inputTipoFondo->name = 'tipofondo';
        $inputTipoFondo->label = 'Tipo de Fondo';
        $inputTipoFondo->required = true;
        $inputTipoFondo->selecciona = $tipo_fondo['tfn_nombre'];

        $inputObservacion = new StdClass();
        $inputObservacion->id = 'observacion';
        $inputObservacion->name = 'observacion';
        $inputObservacion->placeholder = 'Observaciones';
        $inputObservacion->enDiv = true;
        $inputObservacion->label = 'Observaciones';
        $inputObservacion->rows = 2;
        $inputObservacion->cols = 12;
        $inputObservacion->required = true;

        $inputCargoCheque = new StdClass();
        $inputCargoCheque->id = 'CargoCheque';
        $inputCargoCheque->name = 'CargoCheque';
        $inputCargoCheque->label = 'Cargo Cheque';
        $inputCargoCheque->placeholder = '1120301010';
        $inputCargoCheque->enDiv = true;
        $inputCargoCheque->maxlength = '10';
        $inputCargoCheque->formato = 'entero';

        $inputArmonizadaCheque = new StdClass();
        $inputArmonizadaCheque->id = 'ArmonizadaCheque';
        $inputArmonizadaCheque->name = 'ArmonizadaCheque';
        $inputArmonizadaCheque->label = 'Armonizada Cheque';
        $inputArmonizadaCheque->placeholder = '111211160010010001';
        $inputArmonizadaCheque->enDiv = true;
        $inputArmonizadaCheque->maxlength = '18';
        $inputArmonizadaCheque->formato = 'entero';

        $tabla = new stdClass();
        $tabla->seleccionable = true;
        $tabla->id = 'tablaAnterior';
        $tabla->titulo = 'Cuentas Bancarias';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLABE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CUENTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CARGO CHEQUE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA CHEQUE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA CUENTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA NOMBRE';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'cbs_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_cuenta';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'SAP';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'armonizada';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cta_cuenta';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cta_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $inputCuentaInversion = new StdClass();
        $inputCuentaInversion->id = 'CuentaInversion';
        $inputCuentaInversion->name = 'CuentaInversion';
        $inputCuentaInversion->label = 'Cuenta Inversión';
        $inputCuentaInversion->enDiv = true;
        $inputCuentaInversion->maxlength = '11';
        $inputCuentaInversion->formato = 'entero';

        $inputCargoChequeInversion = new StdClass();
        $inputCargoChequeInversion->id = 'CargoChequeInversion';
        $inputCargoChequeInversion->name = 'CargoChequeInversion';
        $inputCargoChequeInversion->label = 'Cargo Inversión';
        $inputCargoChequeInversion->placeholder = '1120401010';
        $inputCargoChequeInversion->enDiv = true;
        $inputCargoChequeInversion->maxlength = '10';
        $inputCargoChequeInversion->formato = 'entero';

        $inputArmonizadaChequeInversion = new StdClass();
        $inputArmonizadaChequeInversion->id = 'ArmonizadaChequeInversion';
        $inputArmonizadaChequeInversion->name = 'ArmonizadaChequeInversion';
        $inputArmonizadaChequeInversion->label = 'Armonizada Inversión';
        $inputArmonizadaChequeInversion->placeholder = '111411160010010001';
        $inputArmonizadaChequeInversion->enDiv = true;
        $inputArmonizadaChequeInversion->maxlength = '18';
        $inputArmonizadaChequeInversion->formato = 'entero';

        $btnCuentaInver = new stdClass();
        $btnCuentaInver->id = 'capturaInv';
        $btnCuentaInver->name = 'capturaInv';
        $btnCuentaInver->value = 'Capturar Información';
        $btnCuentaInver->color = 'verde';
        $btnCuentaInver->icon = 'solid fa-share-from-square me-2';
        $btnCuentaInver->click = '
            comunica("' . $this->pantalla . '",{
                clabeinterbancaria:$("#clabeinterbancaria").val(),
                metodo:"inversionAnterior",
                cuenta:$("#CuentaInversion").val(),
                cargoin:$("#CargoChequeInversion").val(),
                armoniin:$("#ArmonizadaChequeInversion").val()
            });
            ';

        $tablaI = new stdClass();
        $tablaI->seleccionable = true;
        $tablaI->id = 'tablaAnteriorInver';
        $tablaI->titulo = 'Cuentas Bancarias';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLABE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CUENTA INVERSIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CARGO INVERSIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARMONIZADA INVERSIÓN';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'cbs_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_cuenta';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'SAP';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'armonizada';
        array_push($trbody, $td);

        $tablaI->encabezado = [$tr];
        $tablaI->cuerpo = $trbody;

        $html = '
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <legend>Nueva Cuenta</legend>
                    <div class="row">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputFechaApertura) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Clabe Interbancaria</label>
                            <input type="text"
                                maxlength="18"
                                value="' . $row['cbs_id'] . '"
                                placeholder="Número 18 Dígitos"
                                name="clabeinterbancaria"
                                id="clabeinterbancaria"
                                onkeypress="return justNumbers(event);"
                                onkeyup="escribe()"
                                class="form-control"
                            />
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputNombreBanco) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputNumeroPlaza) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Nombre de la Cuenta (Descripción)</label>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <textarea rows="2"
                                cols="12"
                                maxlength="500"
                                placeholder="Nombre de la Cuenta (Descripción)"
                                name="nombreCuenta"
                                id="nombreCuenta"
                                onkeypress="return alfanumNombreCuenta(event);"
                                class="form-control">' . $row['cbs_nombre'] . '</textarea>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Alias de la Cuenta</label>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <input type="text"
                                value="' . $row['cbs_alias'] . '"
                                maxlength="20"
                                placeholder="Alias de la Cuenta"
                                name="aliasCuenta" id="aliasCuenta"
                                onkeypress="return alfanum(event);"
                                class="form-control"/>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Número de Cliente</label>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <input type="text"
                                value="' . $row['cbs_cliente'] . '"
                                maxlength="10" minlength="6"
                                placeholder="Alfanumérico 6 - 10 Dígitos"
                                name="numerocliente" id="numerocliente"
                                onkeypress="return alfanum(event);"
                                class="form-control"/>
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputSucursal) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                            <table class="col-md-6 col-xs-6 ">
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="inversion" value="1" ' . (($row['cbs_inversion'] == 1) ? 'checked="true"' : '') . '>
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Inversora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="recaudadora" value="1" ' . (($row['cbs_recaudadora'] == 1) ? 'checked="true"' : '') . '>
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Recaudadora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="concentradora" value="1" ' . (($row['cbs_concentradora'] == 1) ? 'checked="true"' : '') . '>
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Concentradora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="pagadora" value="1" ' . (($row['cbs_pagadora'] == 1) ? 'checked="true"' : '') . '>
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Pagadora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="receptora" value="1" ' . (($row['cbs_receptora'] == 1) ? 'checked="true"' : '') . '>
                                        </fieldset>
                                    </th>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12 " id="">
                                            <label>Receptora</label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <table class="col-md-12 col-xs-12 ">
                                <tr>
                                    <th colspan="2">
                                        <fieldset class="col-md-12 col-xs-12">
                                            <label>Número de Contrato (Solo Banamex)</label>
                                        </fieldset>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <fieldset class="col-md-1 col-xs-1">
                                            <input type="checkbox" id="check_apertura" ' . (($row['cbs_contrato'] != '') ? 'checked="true"' : '') . '>
                                        </fieldset>
                                    </td>
                                    <td>
                                        <fieldset class="col-md-12 col-xs-12">
                                            ' . $this->ponInput($inputNumeroContratoBx) . '
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponCombo($inputTipoRecurso) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponCombo($inputTipoFondo) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            <label>Observaciones</label>
                            <textarea rows="2"
                                cols="12"
                                maxlength="200"
                                placeholder="Observaciones"
                                name="observacion"
                                id="observacion"
                                onkeypress="return alfanum(event);"
                                class="form-control" >' . $row['cbs_observacion'] . '</textarea>
                        </fieldset>
                    </div>
                </div>

                <div class="col-md-6 col-xs-12">
                    <div class="row">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputCargoCheque) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputArmonizadaCheque) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <legend>Cuenta Inversión</legend>
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputCuentaInversion) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputCargoChequeInversion) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponInput($inputArmonizadaChequeInversion) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->ponBoton($btnCuentaInver) . '
                        </fieldset>
                    </div>
                    <div class="row mb-2">
                        <fieldset class="col-md-11 col-xs-11">
                            ' . $this->wrPonTabla($tablaI) . '
                        </fieldset>
                    </div>
                </div>
            </div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            var permitirCaracteresAlfanumericos = /^[a-zA-ZÀ-ÿ\u00f1\u00d1\d]/;
            var permitirCaracteresAlfanumericosNombreCuenta = /^[a-zA-ZÀ-ÿ°!¡"#$%&/()=¿?\.\;\:\-\_\*\,\[\]\¨\u00f1\u00d1\d]/;
            function alfanum(e)
            {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return permitirCaracteresAlfanumericos.test(String.fromCharCode(keynum));
            }
            function alfanumNombreCuenta(e)
            {
                var keynum = e.which || window.event.keyCode;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return permitirCaracteresAlfanumericosNombreCuenta.test(String.fromCharCode(keynum));
            }

            function justNumbers(e)
            {
                var evento = window.event || e;
                var keynum = evento.keyCode;
                if ((keynum == 8))
                    return true;

                return /\d/.test(String.fromCharCode(evento.charCode));
            }
            function escribe() {
                if (document.getElementById("clabeinterbancaria").value.length == 3) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaBanco",
                        param: $("#clabeinterbancaria").val()
                    });
                }
                if (document.getElementById("clabeinterbancaria").value.length == 6) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaPlaza",
                        param: $("#clabeinterbancaria").val().substring(3)
                    });
                }
            }

            var z=$("#check_apertura");
            z.click(habilita);
            function habilita() {
                if (document.getElementById("nombrebanco").value == "BANAMEX, S.A.") {
                    var x=$("#numerocontratobx");
                    x.attr("disabled",!this.checked);
                }
            }
        ');
        $this->llenaTablaAnterior();
        $this->llenaTablaAnteriorInversion();
    }

    private function llenaTablaAnterior()
    {
        $prms = [$this->prm->clabeinterbancaria];
        $sql = '
            SELECT cbs.cbs_id, if (SUBSTRING(cbs.cbs_id,1,3)=127,CONCAT(cbs.cbs_cuenta),
                SUBSTRING(cbs.cbs_cuenta,4)) cbs_cuenta,
                (
                    SELECT
                        CONCAT(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    FROM ' . BD_TESORERIA . '.vin_CuentaSap
                    WHERE
                        tcs_id = 2 AND cbs_id = cbs.cbs_id
                ) SAP,
                (
                    SELECT
                        CONCAT(sp.ger_id, sp.cgp_id, sp.rub_id, sp.cgcu_id, sp.cgsc_id, sp.cta_gpo_id, sp.cta_ur_id,
                        sp.cta_ue_id, sp.cgc_id, sp.cta_id)
                    FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta sp
                    WHERE
                        sp.tcs_id = 2 AND sp.cbs_id = cbs.cbs_id
                ) armonizada,
                (
                    SELECT
                        cg.cta_cuenta
                    FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta sp
                    INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas cg on cg.cta_gpo_id = sp.cta_gpo_id AND cg.cta_ur_id = sp.cta_ur_id
                        AND cg.cta_ue_id = sp.cta_ue_id AND cg.ger_id = sp.ger_id AND cg.cgp_id = sp.cgp_id
                        AND cg.rub_id = sp.rub_id AND cg.cgcu_id = sp.cgcu_id AND cg.cgsc_id = sp.cgsc_id
                        AND cg.cgc_id = sp.cgc_id AND cg.cta_id = sp.cta_id
                    WHERE
                        sp.tcs_id = 2 AND sp.cbs_id = cbs.cbs_id
                )  cta_cuenta,
                (
                    SELECT
                        cg.cta_nombre
                    FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta sp
                    INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas cg on cg.cta_gpo_id = sp.cta_gpo_id AND cg.cta_ur_id = sp.cta_ur_id
                        AND cg.cta_ue_id = sp.cta_ue_id AND cg.ger_id = sp.ger_id AND cg.cgp_id = sp.cgp_id
                        AND cg.rub_id = sp.rub_id AND cg.cgcu_id = sp.cgcu_id AND cg.cgsc_id = sp.cgsc_id
                        AND cg.cgc_id = sp.cgc_id AND cg.cta_id = sp.cta_id
                    WHERE
                        sp.tcs_id=2 AND sp.cbs_id=cbs.cbs_id
                )  cta_nombre
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin cbs
            WHERE  cbs.cbs_id = ?;
        ';
        $this->setSql($sql);
        $this->setParams($prms);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaAnterior';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }
    private function llenaTablaAnteriorInversion()
    {
        $prms = [$this->prm->clabeinterbancaria];
        $sql = '
            SELECT cbs.cbs_id,
                if (SUBSTRING(cbs.cbs_id,1,3) = 127,CONCAT(cbs.cbs_cuenta), SUBSTRING(cbs.cbs_cuenta,4)) cbs_cuenta,
                (
                    SELECT
                        concat(s.ger_id, s.cgp_id, s.rub_id, s.cgcu_id, s.cgsc_id, s.cgc_id, s.cta_id)
                    FROM ' . BD_TESORERIA . '.vin_CuentaSap s
                    WHERE s.cbs_id = cbs.cbs_id AND s.tcs_id = 1
                ) SAP,
                (
                    SELECT
                        concat(sp.ger_id, sp.cgp_id, sp.rub_id, sp.cgcu_id, sp.cgsc_id, sp.cta_gpo_id, sp.cta_ur_id,
                        sp.cta_ue_id, sp.cgc_id, sp.cta_id)
                    FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta sp
                    WHERE sp.cbs_id = cbs.cbs_id AND sp.tcs_id = 1
                ) armonizada
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin cbs
            WHERE  cbs.cbs_id = ?;
        ';
        $this->setSql($sql);
        $this->setParams($prms);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaAnteriorInver';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    private function insertarTablaAnterior()
    {
        $this->prm->fechaApertura = (isset($this->prm->fechaApertura)) ? $this->prm->fechaApertura : '';
        $this->prm->nombrebanco = (isset($this->prm->nombrebanco)) ? $this->prm->nombrebanco : '';
        $this->prm->nombreCuenta = (isset($this->prm->nombreCuenta)) ? $this->prm->nombreCuenta : '';
        $this->prm->aliasCuenta = (isset($this->prm->aliasCuenta)) ? $this->prm->aliasCuenta : '';
        $this->prm->numerocliente = (isset($this->prm->numerocliente)) ? $this->prm->numerocliente : '';
        $this->prm->sucursal = (isset($this->prm->sucursal)) ? $this->prm->sucursal : '';
        $this->prm->clabeinterbancaria = (isset($this->prm->clabeinterbancaria)) ? $this->prm->clabeinterbancaria : '';
        $this->prm->numerocontratobx = (isset($this->prm->numerocontratobx)) ? $this->prm->numerocontratobx : '';
        $this->prm->tiporecurso = (isset($this->prm->tiporecurso)) ? $this->prm->tiporecurso : '';
        $this->prm->tipofondo = (isset($this->prm->tipofondo)) ? $this->prm->tipofondo : '';
        $this->prm->observacion = (isset($this->prm->observacion)) ? $this->prm->observacion : '';
        $this->prm->pagadora = (isset($this->prm->pagadora)) ? $this->prm->pagadora : '';
        $this->prm->recaudadora = (isset($this->prm->recaudadora)) ? $this->prm->recaudadora : '';
        $this->prm->receptora = (isset($this->prm->receptora)) ? $this->prm->receptora : '';
        $this->prm->concentradora = (isset($this->prm->concentradora)) ? $this->prm->concentradora : '';
        $this->prm->inversion = (isset($this->prm->inversion)) ? $this->prm->inversion : '';
        $this->prm->cargoCheque = (isset($this->prm->cargoCheque)) ? $this->prm->cargoCheque : '';
        $this->prm->armonizadaCheque = (isset($this->prm->armonizadaCheque)) ? $this->prm->armonizadaCheque : '';

        $contador = 0;
        $cuenta = substr($this->prm->clabeinterbancaria, -12, -1);
        if ($this->prm->clabeinterbancaria == '') {
            $this->mixScript("notify('Falta Clabe Interbancaria', 'warning');");
            return 0;
        }

        if (strlen($this->prm->clabeinterbancaria) < 18) {
            $this->mixScript("notify('Clabe Interbancaria No Válida', 'warning');");
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [substr($this->prm->clabeinterbancaria, 0, 3)];
        $conexion->setSql('SELECT iba_numero, iba_abreviatura
            FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria
            WHERE iba_numero = ?;');
        $conexion->consultar($params);
        $valor = $conexion->extraer();
        $ClabeBanco = substr($this->prm->clabeinterbancaria, 0, 3);
        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabeBanco no se reconoce.', 'warning');");
            return 0;
        }
        $abreviatura = $valor['iba_abreviatura'];
        if ($abreviatura != $this->prm->nombrebanco) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabeBanco no corresponde al Banco Seleccionado.', 'warning');");
            return 0;
        }
        $ClabePlaza = substr($this->prm->clabeinterbancaria, 3, 3);
        $params = [$ClabePlaza];
        $conexion->setSql('SELECT pbn_numero FROM ' . BD_TESORERIA . '.cat_PlazaBancaria
            WHERE pbn_numero = ?;');
        $conexion->consultar($params);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabePlaza no se reconoce.', 'warning');");
            return 0;
        }

        if ($this->prm->cargoCheque == '') {
            $this->mixScript("notify('Falta la Cuenta Cheque', 'warning');");
            return 0;
        }

        if (strlen($this->prm->cargoCheque) < 10) {
            $this->mixScript("notify('Cuenta Cheque No Válida', 'warning');");
            return 0;
        }

        if (substr($this->prm->cargoCheque, 0, 5) != '11203') {
            $this->mixScript("notify('Cuenta Cheque No Válida', 'warning');");
            return 0;
        }

        if ($this->prm->armonizadaCheque == '') {
            $this->mixScript("notify('Falta la Cuenta Armonizada', 'warning');");
            return 0;
        }

        if (strlen($this->prm->armonizadaCheque) < 18) {
            $this->mixScript("notify('Cuenta Armonizada No Válida', 'warning');");
            return 0;
        }

        if (substr($this->prm->armonizadaCheque, 0, 11) != '11121116005') {
            $this->mixScript("notify('Cuenta Armonizada No Válida', 'warning');");
            return 0;
        }

        $params = [$this->prm->clabeinterbancaria];
        $conexion->setSql('SELECT cbs_id FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
            WHERE cbs_id = ?;');
        $conexion->consultar($params);
        if ($conexion->getNumeroRegistros() == 0) {
            if ($this->prm->fechaApertura == '') {
                $this->mixScript("notify('Falta Fecha', 'warning');");
                return 0;
            }
            $hoy = getdate();
            if (
                $hoy['year'] < substr($this->prm->fechaApertura, -4)
                || (
                    $hoy['year'] == substr($this->prm->fechaApertura, -4)
                    && $hoy['mon'] < substr($this->prm->fechaApertura, 3, 2)
                ) || (
                    $hoy['year'] == substr($this->prm->fechaApertura, -4)
                    && $hoy['mon'] == substr($this->prm->fechaApertura, 3, 2)
                    && $hoy['mday'] < substr($this->prm->fechaApertura, 0, 2)
                )
            ) {
                $this->mixScript("notify('Fecha no Válida', 'warning');");
                return 0;
            }
            if ($this->prm->nombrebanco == '') {
                $this->mixScript("notify('Falta Banco', 'warning');");
                return 0;
            }
            if ($this->prm->nombreCuenta == '') {
                $this->mixScript("notify('Falta el Nombre de la Cuenta', 'warning');");
                return 0;
            }
            if ($this->prm->aliasCuenta == '') {
                $this->mixScript("notify('Falta el Alias de la Cuenta', 'warning');");
                return 0;
            }
            if ($this->prm->numerocliente == '') {
                $this->mixScript("notify('Falta el Número de Cliente', 'warning');");
                return 0;
            }
            if (strlen($this->prm->numerocliente) < 6) {
                $this->mixScript("notify('Número de Cliente No Válido', 'warning');");
                return 0;
            }
            if ($this->prm->sucursal == '') {
                $this->mixScript("notify('Falta Sucursal', 'warning');");
                return 0;
            }
            if (strlen($this->prm->sucursal) < 2) {
                $this->mixScript("notify('Número de Sucursal No Válido', 'warning');");
                return 0;
            }
            if ($this->prm->numerocontratobx == '' && $this->prm->nombrebanco == 'BANAMEX, S.A.') {
                $this->mixScript("notify('Falta el Número de Contrato', 'warning');");
                return 0;
            }
            if (strlen($this->prm->numerocontratobx) < 10 && $this->prm->nombrebanco == 'BANAMEX, S.A.') {
                $this->mixScript("notify('Número de Contrato No Válido', 'warning');");
                return 0;
            }
            if ($this->prm->tiporecurso == '') {
                $this->mixScript("notify('Falta el Tipo de Recurso', 'warning');");
                return 0;
            }
            if ($this->prm->tipofondo == '') {
                $this->mixScript("notify('Falta el Tipo de Fondo', 'warning');");
                return 0;
            }
            if ($this->prm->observacion == '') {
                $this->mixScript("notify('Faltan las Observaciones', 'warning');");
                return 0;
            }

            $anio = substr($this->prm->fechaApertura, -4);
            $conexion->setSql('SELECT iba_id FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria
                WHERE iba_abreviatura = ? ');
            $conexion->consultar([$this->prm->nombrebanco]);
            $valor = $conexion->extraer();
            $ibaID = $valor['iba_id'];

            $conexion->setSql('SELECT ter_id FROM ' . BD_TESORERIA . '.cat_TipoRecurso
                WHERE ter_nombre = ?');
            $conexion->consultar([$this->prm->tiporecurso]);
            $valor = $conexion->extraer();
            $terID = $valor['ter_id'];

            $conexion->setSql('SELECT tfn_id FROM ' . BD_TESORERIA . '.cat_TipoFondo
                WHERE tfn_nombre = ?');
            $conexion->consultar([$this->prm->tipofondo]);
            $valor = $conexion->extraer();
            $tfnID = $valor['tfn_id'];
            $params = [$ClabePlaza];
            $conexion->setSql('SELECT pbn_id FROM ' . BD_TESORERIA . '.cat_PlazaBancaria
                WHERE pbn_numero = ? ');
            $conexion->consultar($params);
            $valor = $conexion->extraer();
            $pbnID = $valor['pbn_id'];

            $this->prm2 = new stdClass();
            $this->prm2->cbs_id = $this->prm->clabeinterbancaria;
            $this->prm2->cuenta = $cuenta;
            $this->prm2->fechaApertura = $this->prm->fechaApertura;
            $this->prm2->anio = $anio;
            $this->prm2->sucursal = $this->prm->sucursal;
            $this->prm2->nombre = $this->prm->nombreCuenta;
            $this->prm2->alias = $this->prm->aliasCuenta;
            $this->prm2->cliente = $this->prm->numerocliente;
            $this->prm2->inversion = ($this->prm->inversion != '') ? $this->prm->inversion : 2;
            $this->prm2->recaudadora = ($this->prm->recaudadora != '') ? $this->prm->recaudadora : 2;
            $this->prm2->concentradora = ($this->prm->concentradora != '') ? $this->prm->concentradora : 2;
            $this->prm2->pagadora = ($this->prm->pagadora != '') ? $this->prm->pagadora : 2;
            $this->prm2->receptora = ($this->prm->receptora != '') ? $this->prm->receptora : 2;
            $this->prm2->observacion = $this->prm->observacion;
            $this->prm2->iba_id = $ibaID;
            $this->prm2->ter_id = $terID;
            $this->prm2->pbn_id = $pbnID;
            $this->prm2->eba_id = 1;
            $this->prm2->tfn_id = $tfnID;
            if ($this->prm->nombrebanco == 'BANAMEX, S.A.') {
                $this->prm2->contrato = $this->prm->numerocontratobx;
            }
            $this->prm2->abc = 'a';
            CuentaBancariaSefin::abc($this->prm2);
        } else {
            $contador++;
        }
        $params = [$this->prm->clabeinterbancaria];
        $conexion->setSql('SELECT * FROM ' . BD_TESORERIA . '.vin_CuentaSap
            WHERE cbs_id = ?;');
        $conexion->consultar($params);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->prm3 = new stdClass();
            $this->prm3->cbs_id = $this->prm->clabeinterbancaria;
            $this->prm3->cuentaBanco = $cuenta;
            $this->prm3->ger_id = 1;
            $this->prm3->cgp_id = 1;
            $this->prm3->rub_id = 2;
            $this->prm3->cgcu_id = '0';
            $this->prm3->cgsc_id = 3;
            $this->prm3->cgc_id = substr($this->prm->cargoCheque, 5, 2);
            $this->prm3->cta_id = substr($this->prm->cargoCheque, -3);
            $this->prm3->ibs_id = substr($this->prm3->cgc_id, -1);
            $this->prm3->tcs_id = 2;
            $this->prm3->consecutivo = '0';
            $this->prm3->abc = 'a';
            CuentaSap::abc($this->prm3);
        } else {
            $contador++;
        }

        $conexion->setSql(' SELECT cbs_alias FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
            WHERE cbs_id = ?;');
        $conexion->consultar($params);
        $reow = $conexion->extraer();
        $alia = $reow['cbs_alias'];

        $params = [substr($this->prm->armonizadaCheque, -7, -4), substr($this->prm->armonizadaCheque, -4)];
        $conexion->setSql(' SELECT * FROM ' . BD_CTRL . '.cg_PlanCuentas
            WHERE cta_gpo_id = 1
            AND cta_ur_id = 16
            AND cta_ue_id = 005
            AND ger_id = 1
            AND cgp_id = 1
            AND rub_id = 1
            AND cgcu_id = 2
            AND cgsc_id = 1
            AND cgc_id = ?
            AND cta_id = ? ');
        $conexion->consultar($params);

        $this->prms = new stdClass();
        $this->prms->cbs_id = $this->prm->clabeinterbancaria;
        $this->prms->tcs_id = 2;
        $this->prms->cta_gpo_id = 1;
        $this->prms->cta_ur_id = 16;
        $this->prms->cta_ue_id = 005;
        $this->prms->ger_id = 1;
        $this->prms->cgp_id = 1;
        $this->prms->rub_id = 1;
        $this->prms->cgcu_id = 2;
        $this->prms->cgsc_id = 1;
        $this->prms->cgc_id = substr($this->prm->armonizadaCheque, -7, -4);
        $this->prms->cta_id = substr($this->prm->armonizadaCheque, -4);
        $this->prms->abc = 'a';
        $this->prms->cuenta = $this->prms->ger_id . '.' . $this->prms->cgp_id . '.' . $this->prms->rub_id . '.' .
        $this->prms->cgcu_id . '.' . $this->prms->cgsc_id . '.' . $this->prms->cgc_id . '.' . $this->prms->cta_id;
        $this->prms->agc_id = 'GRAL';
        $this->prms->nombre = 'CTA-' . substr($this->prm->clabeinterbancaria, -12, -1) . '-' . $alia;
        $this->prms->descripcion = '';
        $this->prms->carga = '';

        if ($conexion->getNumeroRegistros() == 0) {
            LanCuentas::abc($this->prms);
        } else {
            $contador++;
        }

        $params = [$this->prm->clabeinterbancaria];
        $conexion->setSql('SELECT * from ' . BD_TESORERIA . '.vin_SefinPlanCuenta
            WHERE cbs_id = ? AND tcs_id = 2;');
        $conexion->consultar($params);

        if ($conexion->getNumeroRegistros() == 0) {
            SefinPlanCuenta::abc($this->prms);
        } else {
            $contador++;
        }

        $conexion->desconectar();
        $this->up = new stdClass();
        $this->up->eba_id = 3;
        $this->up->cbs_id = $this->prm->clabeinterbancaria;
        $this->up->abc = 'c';

        if ($contador < 4) {
            CuentaBancariaSefin::abc($this->up);
        } else {
            $this->mixScript('notify("La clabe interbancaria ya se encuentra vinculada a su cuenta SAP y ARMONIZADA","warning");');
        }

        $getCuentas = '
            SELECT cbs_id, cbs_cuenta,
                concat(s.ger_id, s.cgp_id, s.rub_id, s.cgcu_id, s.cgsc_id, s.cgc_id, s.cta_id) SAP,
                concat(sp.ger_id, sp.cgp_id, sp.rub_id, sp.cgcu_id, sp.cgsc_id, sp.cta_gpo_id, sp.cta_ur_id,
                sp.cta_ue_id, sp.cgc_id, sp.cta_id) armonizada,
                cg.cta_cuenta cta_cuenta, cg.cta_nombre cta_nombre
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
            INNER JOIN ' . BD_TESORERIA . '.vin_CuentaSap s using (cbs_id)
            INNER JOIN ' . BD_TESORERIA . '.vin_SefinPlanCuenta sp using (cbs_id)
            INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas cg on cg.cta_gpo_id = sp.cta_gpo_id
                AND cg.cta_ur_id = sp.cta_ur_id AND cg.cta_ue_id = sp.cta_ue_id AND cg.ger_id = sp.ger_id
                AND cg.cgp_id = sp.cgp_id AND cg.rub_id = sp.rub_id AND cg.cgcu_id = sp.cgcu_id
                AND cg.cgsc_id = sp.cgsc_id AND cg.cgc_id = sp.cgc_id AND cg.cta_id = sp.cta_id
            WHERE sp.tcs_id = 2
                AND s.tcs_id = 2
                AND cbs_id = ?;
        ';
        $this->conectar();
        $this->setSql($getCuentas);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaAnterior';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    private function inversionAnterior()
    {
        $this->prm->clabeinterbancaria = (isset($this->prm->clabeinterbancaria)) ? $this->prm->clabeinterbancaria : '';
        $this->prm->cuenta = (isset($this->prm->cuenta)) ? $this->prm->cuenta : '';
        $this->prm->cargoin = (isset($this->prm->cargoin)) ? $this->prm->cargoin : '';
        $this->prm->armoniin = (isset($this->prm->armoniin)) ? $this->prm->armoniin : '';

        if ($this->prm->cuenta == '') {
            $this->mixScript("notify('Falta la Cuenta de Inversión', 'warning');");
            return 0;
        }

        if (strlen($this->prm->cuenta) < 11) {
            $this->mixScript("notify('La Cuenta de Inversión no es valida', 'warning');");
            return 0;
        }

        if ($this->prm->cargoin == '') {
            $this->mixScript("notify('Falta Cargo Inversión', 'warning');");
            return 0;
        }

        if (strlen($this->prm->cargoin) < 10) {
            $this->mixScript("notify('Cargo Inversión No Válida', 'warning');");
            return 0;
        }

        if (substr($this->prm->cargoin, 0, 5) != '11204') {
            $this->mixScript("notify('Cargo Inversión No Válida', 'warning');");
            return 0;
        }

        if ($this->prm->armoniin == '') {
            $this->mixScript("notify('Falta Armonizada de Inversión', 'warning');");
            return 0;
        }

        if (strlen($this->prm->armoniin) < 18) {
            $this->mixScript("notify('Cuenta Armonizada No Válida', 'warning');");
            return 0;
        }

        if (substr($this->prm->armoniin, 0, 11) != '11141116005') {
            $this->mixScript("notify('Cuenta Armonizada No Válida', 'warning');");
            return 0;
        }

        if ($this->prm->clabeinterbancaria == '') {
            $this->mixScript("notify('Falta Clabe Interbancaria', 'warning');");
            return 0;
        }

        if (strlen($this->prm->clabeinterbancaria) < 18) {
            $this->mixScript("notify('Clabe Interbancaria No Válida', 'warning');");
            return 0;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();

        $prm = [substr($this->prm->clabeinterbancaria, 0, 3)];
        $conexion->setSql('SELECT iba_numero, iba_abreviatura FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria where iba_numero = ?;');
        $conexion->consultar($prm);
        $conexion->extraer();
        $ClabeBanco = substr($this->prm->clabeinterbancaria, 0, 3);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabeBanco no se reconoce.', 'warning');");
            return 0;
        }

        $ClabePlaza = substr($this->prm->clabeinterbancaria, 3, 3);
        $prm = [$ClabePlaza];
        $conexion->setSql('SELECT pbn_numero FROM ' . BD_TESORERIA . '.cat_PlazaBancaria where pbn_numero = ?;');
        $conexion->consultar($prm);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabePlaza no se reconoce.', 'warning');");
            return 0;
        }

        $prm = [$this->prm->clabeinterbancaria];
        $conexion->setSql('SELECT * from ' . BD_TESORERIA . '.ban_CuentaBancariaSefin where cbs_id = ?;');
        $conexion->consultar($prm);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('La Clabe Interbancaria no existe', 'warning');");
            return 0;
        }

        $conexion->setSql('SELECT cbs_inversion from ' . BD_TESORERIA . '.ban_CuentaBancariaSefin where cbs_id = ?;');
        $conexion->consultar($prm);
        $row = $conexion->extraer();
        $inver = $row['cbs_inversion'];

        if ($inver != 1) {
            $this->mixScript("notify('La Clabe Interbancaria no esta autorizada para darle de alta una Cuenta de Inversión', 'warning');");
            return 0;
        }

        $conexion->setSql('SELECT * from ' . BD_TESORERIA . '.vin_CuentaSap where tcs_id = 2 and cbs_id = ?;');
        $conexion->consultar($prm);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('La Clabe Interbancaria no tiene Cuenta Cheque', 'warning');");
            return 0;
        }

        $conexion->setSql('SELECT * from ' . BD_TESORERIA . '.vin_CuentaSap where tcs_id = 1 and cbs_id = ?;');
        $conexion->consultar($prm);

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La Clabe Interbancaria ya tiene Cuenta de Inversión', 'warning');");
            return 0;
        }

        $conexion->setSql('SELECT cbs_consecutivo from ' . BD_TESORERIA . '.vin_CuentaSap where tcs_id = 2 and cbs_id = ?;');
        $conexion->consultar($prm);
        $row = $conexion->extraer();
        $conse = $row['cbs_consecutivo'];

        $conexion->setSql('SELECT cbs_id,ibs_id,iba_id,cbs_consecutivo
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin as a
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria as b using (iba_id)
            INNER JOIN ' . BD_TESORERIA . '.vin_CuentaSap using (cbs_id)
            WHERE cbs_id = ?
            GROUP BY cbs_id, ibs_id, cbs_consecutivo;');
        $conexion->conectar();
        $conexion->consultar($prm);
        $row = $conexion->extraer();
        $ibs = $row['ibs_id'];

        $this->prm3 = new stdClass();
        $this->prm3->cbs_id = $this->prm->clabeinterbancaria;
        $this->prm3->cbs_cuentaBanco = $this->prm->cuenta;
        $this->prm3->ger_id = 1;
        $this->prm3->cgp_id = 1;
        $this->prm3->rub_id = 2;
        $this->prm3->cgcu_id = '0';
        $this->prm3->cgsc_id = 4;
        $this->prm3->cgc_id = substr($this->prm->cargoin, 5, 2);
        $this->prm3->cta_id = substr($this->prm->cargoin, -3);
        $this->prm3->ibs_id = $ibs;
        $this->prm3->tcs_id = 1;
        $this->prm3->consecutivo = $conse;
        $this->prm3->abc = 'a';
        CuentaSap::abc($this->prm3);

        $conexion->setSql(' SELECT cbs_alias FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
                    WHERE cbs_id = ?;');
        $conexion->consultar($prm);
        $reow = $conexion->extraer();
        $alia = $reow['cbs_alias'];

        $prm = [substr($this->prm->armoniin, -7, -4), substr($this->prm->armoniin, -4)];
        $conexion->setSql(' SELECT * FROM ' . BD_CTRL . '.cg_PlanCuentas
                WHERE cta_gpo_id = 1
                and cta_ur_id = 16
                and cta_ue_id = 005
                and ger_id = 1
                and cgp_id = 1
                and rub_id = 1
                and cgcu_id = 4
                and cgsc_id = 1
                and cgc_id = ?
                and cta_id = ?;');
        $conexion->consultar($prm);

        $this->prms = new stdClass();
        $this->prms->cbs_id = $this->prm->clabeinterbancaria;
        $this->prms->tcs_id = 1;
        $this->prms->cta_gpo_id = 1;
        $this->prms->cta_ur_id = 16;
        $this->prms->cta_ue_id = 005;
        $this->prms->ger_id = 1;
        $this->prms->cgp_id = 1;
        $this->prms->rub_id = 1;
        $this->prms->cgcu_id = 4;
        $this->prms->cgsc_id = 1;
        $this->prms->cgc_id = substr($this->prm->armoniin, -7, -4);
        $this->prms->cta_id = '' . substr($this->prm->armoniin, -4) . '';
        $this->prms->abc = 'a';
        $this->prms->cuenta = $this->prms->ger_id . '.' . $this->prms->cgp_id . '.' . $this->prms->rub_id . '.' .
        $this->prms->cgcu_id . '.' . $this->prms->cgsc_id . '.' . $this->prms->cgc_id . '.' . $this->prms->cta_id;
        $this->prms->agc_id = 'GRAL';
        $this->prms->nombre = 'CTA-' . substr($this->prm->clabeinterbancaria, -12, -1) . '-' . $alia;
        $this->prms->descripcion = '';
        $this->prms->carga = '';

        if ($conexion->getNumeroRegistros() == 0) {
            LanCuentas::abc($this->prms);
        }

        $prm = [$this->prm->clabeinterbancaria];
        $conexion->setSql('SELECT * FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta
            where cbs_id = ? and tcs_id = 1;');
        $conexion->consultar($prm);

        if ($conexion->getNumeroRegistros() == 0) {
            SefinPlanCuenta::abc($this->prms);
        }

        $conexion->desconectar();

        $getCuentas = '
            SELECT cbs_id, cbs_cuenta,
                concat(s.ger_id, s.cgp_id, s.rub_id, s.cgcu_id, s.cgsc_id, s.cgc_id, s.cta_id) SAP,
                concat(sp.ger_id, sp.cgp_id, sp.rub_id, sp.cgcu_id, sp.cgsc_id, sp.cta_gpo_id, sp.cta_ur_id,
                sp.cta_ue_id, sp.cgc_id, sp.cta_id) armonizada
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
            INNER JOIN ' . BD_TESORERIA . '.vin_CuentaSap s using (cbs_id)
            INNER JOIN ' . BD_TESORERIA . '.vin_SefinPlanCuenta sp using (cbs_id)
            INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas cg on
                cg.cta_gpo_id = sp.cta_gpo_id AND cg.cta_ur_id = sp.cta_ur_id
                AND cg.cta_ue_id = sp.cta_ue_id AND cg.ger_id = sp.ger_id AND cg.cgp_id = sp.cgp_id AND cg.rub_id = sp.rub_id
                AND cg.cgcu_id = sp.cgcu_id AND cg.cgsc_id = sp.cgsc_id AND cg.cgc_id = sp.cgc_id AND cg.cta_id = sp.cta_id
            WHERE sp.tcs_id = 1
                AND s.tcs_id = 1
                AND cbs_id = ? ;
        ';

        $this->conectar();
        $this->setSql($getCuentas);
        $this->setParams($prm);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaAnteriorInver';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    private function consultaBanco()
    {
        /* el # debe ser removido cuando el modelo de bancos UR ya esté en la base de datos 2019*/
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [$this->prm->param];
        $conexion->setSql('SELECT iba_abreviatura FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria
            INNER JOIN ' . BD_CTRL . '.cat_InstitucionesBancarias using (iba_id)
            WHERE iba_numero = ?;');
        $conexion->consultar($params);
        $valor = $conexion->extraer();

        $this->mixScript('$("#nombrebanco").val("' . $valor['iba_abreviatura'] . '");');
        $this->prm->clabeinterbancaria = (isset($this->prm->clabeinterbancaria)) ? $this->prm->clabeinterbancaria : '';

        $params = [$this->prm->clabeinterbancaria];
        $conexion->setSql('SELECT cbs_id FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
            WHERE cbs_id = ?;');
        $conexion->consultar($params);

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La Clabe Interbancaria ya Existe.', 'error');");
            return 0;
        }
        $conexion->desconectar();
    }

    private function consultaPlaza()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [$this->prm->param];
        $conexion->setSql('SELECT pbn_nombre FROM ' . BD_TESORERIA . '.cat_PlazaBancaria
            WHERE pbn_numero = ?;');
        $conexion->consultar($params);
        $valor = $conexion->extraer();
        $conexion->desconectar();
        $this->mixScript('$("#numeroplaza").val("' . $valor['pbn_nombre'] . '");');
    }

    //nota: meter todo el insert en un procedimiento almacenado
    private function insertar()
    {
        $params = [];
        $this->prm->fechaApertura = (isset($this->prm->fechaApertura)) ? $this->prm->fechaApertura : '';
        $this->prm->nombrebanco = (isset($this->prm->nombrebanco)) ? $this->prm->nombrebanco : '';
        $this->prm->nombreCuenta = (isset($this->prm->nombreCuenta)) ? $this->prm->nombreCuenta : '';
        $this->prm->aliasCuenta = (isset($this->prm->aliasCuenta)) ? $this->prm->aliasCuenta : '';
        $this->prm->numerocliente = (isset($this->prm->numerocliente)) ? $this->prm->numerocliente : '';
        $this->prm->sucursal = (isset($this->prm->sucursal)) ? $this->prm->sucursal : '';
        $this->prm->clabeinterbancaria = (isset($this->prm->clabeinterbancaria)) ? $this->prm->clabeinterbancaria : '';
        $this->prm->numerocontratobx = (isset($this->prm->numerocontratobx)) ? $this->prm->numerocontratobx : '';
        $this->prm->tiporecurso = (isset($this->prm->tiporecurso)) ? $this->prm->tiporecurso : '';
        $this->prm->tipofondo = (isset($this->prm->tipofondo)) ? $this->prm->tipofondo : '';
        $this->prm->observacion = (isset($this->prm->observacion)) ? $this->prm->observacion : '';
        $this->prm->pagadora = (isset($this->prm->pagadora)) ? $this->prm->pagadora : '';
        $this->prm->recaudadora = (isset($this->prm->recaudadora)) ? $this->prm->recaudadora : '';
        $this->prm->receptora = (isset($this->prm->receptora)) ? $this->prm->receptora : '';
        $this->prm->concentradora = (isset($this->prm->concentradora)) ? $this->prm->concentradora : '';
        $this->prm->inversion = (isset($this->prm->inversion)) ? $this->prm->inversion : '';

        if ($this->prm->fechaApertura == '') {
            $this->mixScript("notify('Falta Fecha', 'warning');");
            return 0;
        }
        $hoy = getdate();
        if (
            $hoy['year'] < substr($this->prm->fechaApertura, -4)
            || (
                $hoy['year'] == substr($this->prm->fechaApertura, -4)
                && $hoy['mon'] < substr($this->prm->fechaApertura, 3, 2)
            )
            || (
                $hoy['year'] == substr($this->prm->fechaApertura, -4)
                && $hoy['mon'] == substr($this->prm->fechaApertura, 3, 2)
                && $hoy['mday'] < substr($this->prm->fechaApertura, 0, 2)
            )
        ) {
            $this->mixScript("notify('Fecha no Válida', 'warning');");
            return 0;
        }
        if ($this->prm->nombrebanco == '') {
            $this->mixScript("notify('Falta Banco', 'warning');");
            return 0;
        }
        if ($this->prm->nombreCuenta == '') {
            $this->mixScript("notify('Falta el Nombre de la Cuenta', 'warning');");
            return 0;
        }
        if ($this->prm->aliasCuenta == '') {
            $this->mixScript("notify('Falta el Alias de la Cuenta', 'warning');");
            return 0;
        }
        if ($this->prm->numerocliente == '') {
            $this->mixScript("notify('Falta el Número de Cliente', 'warning');");
            return 0;
        }
        if (strlen($this->prm->numerocliente) < 6) {
            $this->mixScript("notify('Número de Cliente No Válido', 'warning');");
            return 0;
        }
        if ($this->prm->sucursal == '') {
            $this->mixScript("notify('Falta Sucursal', 'warning');");
            return 0;
        }
        if (strlen($this->prm->sucursal) < 2) {
            $this->mixScript("notify('Número de Sucursal No Válido', 'warning');");
            return 0;
        }
        if ($this->prm->clabeinterbancaria == '') {
            $this->mixScript("notify('Falta Clabe Interbancaria', 'warning');");
            return 0;
        }
        if (strlen($this->prm->clabeinterbancaria) < 18) {
            $this->mixScript("notify('Clabe Interbancaria No Válida', 'warning');");
            return 0;
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();

        $ClabeBanco = substr($this->prm->clabeinterbancaria, 0, 3);
        $conexion->setSql('SELECT iba_numero, iba_abreviatura FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria WHERE iba_numero = ? ');
        $conexion->consultar([$ClabeBanco]);
        $valor = $conexion->extraer();

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabeBanco no se reconoce.', 'warning');");
            return 0;
        }
        $abreviatura = $valor['iba_abreviatura'];
        if ($abreviatura != $this->prm->nombrebanco) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabeBanco no corresponde al Banco Seleccionado.', 'warning');");
            return 0;
        }

        $clabe = $this->prm->clabeinterbancaria;
        $conexion->setSql('SELECT cbs_id FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin WHERE cbs_id = ? ;');
        $conexion->consultar([$clabe]);

        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript("notify('La Clabe Interbancaria ya Existe.', 'warning');");
            return 0;
        }

        $ClabePlaza = substr($this->prm->clabeinterbancaria, 3, 3);
        $conexion->setSql('SELECT pbn_numero FROM ' . BD_TESORERIA . '.cat_PlazaBancaria WHERE pbn_numero = ? ;');
        $conexion->consultar([$ClabePlaza]);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript("notify('Clabe Interbancaria No Válida. El Número $ClabePlaza no se reconoce.', 'warning');");
            return 0;
        }

        if ($this->prm->numerocontratobx == '' && $this->prm->nombrebanco == 'BANAMEX, S.A.') {
            $this->mixScript("notify('Falta el Número de Contrato', 'warning');");
            return 0;
        }

        if (strlen($this->prm->numerocontratobx) < 10 && $this->prm->nombrebanco == 'BANAMEX, S.A.') {
            $this->mixScript("notify('Número de Contrato No Válido', 'warning');");
            return 0;
        }

        if ($this->prm->tiporecurso == '') {
            $this->mixScript("notify('Falta el Tipo de Recurso', 'warning');");
            return 0;
        }

        if ($this->prm->tipofondo == '') {
            $this->mixScript("notify('Falta el Tipo de Fondo', 'warning');");
            return 0;
        }

        if ($this->prm->observacion == '') {
            $this->mixScript("notify('Faltan las Observaciones', 'warning');");
            return 0;
        }

        $cuenta = substr($this->prm->clabeinterbancaria, -12, -1);
        $anio = substr($this->prm->fechaApertura, -4);

        $conexion->setSql('SELECT iba_id FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria where iba_abreviatura = ? ');
        $conexion->consultar([$this->prm->nombrebanco]);
        $valor = $conexion->extraer();
        $ibaID = $valor['iba_id'];

        $conexion->setSql('SELECT ter_id FROM ' . BD_TESORERIA . '.cat_TipoRecurso where ter_nombre = ? ');
        $conexion->consultar([$this->prm->tiporecurso]);
        $valor = $conexion->extraer();
        $terID = $valor['ter_id'];

        $conexion->setSql('SELECT tfn_id FROM ' . BD_TESORERIA . '.cat_TipoFondo where tfn_nombre = ? ');
        $conexion->consultar([$this->prm->tipofondo]);
        $valor = $conexion->extraer();
        $tfnID = $valor['tfn_id'];

        $params = [substr($this->prm->clabeinterbancaria, 3, 3)];
        $conexion->setSql('SELECT pbn_id FROM ' . BD_TESORERIA . '.cat_PlazaBancaria where pbn_numero = ? ');
        $conexion->consultar($params);
        $valor = $conexion->extraer();
        $pbnID = $valor['pbn_id'];

        // Ojo: nueva regla de negocio, si es la institucion bancaria "Banco Azteca, S.A." entonces a la
        //      cuenta de cheques se colocamos los digitos "0172"
        if (substr($this->prm->clabeinterbancaria, 0, 3) == '127') {
            $cuenta = '0172' . substr($cuenta, 1);
        }

        $this->prm2 = new stdClass();
        $this->prm2->cbs_id = $this->prm->clabeinterbancaria;
        $this->prm2->cuenta = $cuenta;
        $this->prm2->fechaApertura = $this->prm->fechaApertura;
        $this->prm2->anio = $anio;
        $this->prm2->sucursal = $this->prm->sucursal;
        $this->prm2->nombre = $this->prm->nombreCuenta;
        $this->prm2->alias = $this->prm->aliasCuenta;
        $this->prm2->cliente = $this->prm->numerocliente;
        $this->prm2->inversion = ($this->prm->inversion != '') ? $this->prm->inversion : 2;
        $this->prm2->recaudadora = ($this->prm->recaudadora != '') ? $this->prm->recaudadora : 2;
        $this->prm2->concentradora = ($this->prm->concentradora != '') ? $this->prm->concentradora : 2;
        $this->prm2->pagadora = ($this->prm->pagadora != '') ? $this->prm->pagadora : 2;
        $this->prm2->receptora = ($this->prm->receptora != '') ? $this->prm->receptora : 2;
        $this->prm2->observacion = $this->prm->observacion;
        $this->prm2->iba_id = $ibaID;
        $this->prm2->ter_id = $terID;
        $this->prm2->pbn_id = $pbnID;
        $this->prm2->eba_id = 1;
        $this->prm2->tfn_id = $tfnID;
        if ($this->prm->nombrebanco == 'BANAMEX, S.A.') {
            $this->prm2->contrato = $this->prm->numerocontratobx;
        }
        $this->prm2->abc = 'a';
        CuentaBancariaSefin::abc($this->prm2);

        $this->mixScript('document.getElementById("cuentacheques").value = "' . $cuenta . '";');

        $conexion->desconectar();

        $this->generarSap($this->prm->nombrebanco, $this->prm->clabeinterbancaria, $cuenta);
        $this->generaArmonizada($ibaID, $this->prm->clabeinterbancaria, 2);
        $this->up = new stdClass();
        $this->up->eba_id = 3;
        $this->up->cbs_id = $this->prm->clabeinterbancaria;
        $this->up->abc = 'c';
        CuentaBancariaSefin::abc($this->up);

        $params = [$this->prm->clabeinterbancaria];
        $getCuentas = '
            SELECT a.cbs_id, a.cbs_cuenta,
                CONCAT(b.ger_id,b.cgp_id,b.rub_id,b.cgcu_id,b.cgsc_id,b.cgc_id,b.cta_id)as CTA_CHEQUES,
                CONCAT(c.ibs_propio,b.cbs_consecutivo) as BAN_PROP,
                CONCAT(d.ger_id,d.cgp_id,d.rub_id,d.cgcu_id,d.cgsc_id,d.cta_gpo_id,d.cta_ur_id,d.cta_ue_id,d.cgc_id,d.cta_id) as ARMONI
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin as a
            INNER JOIN ' . BD_TESORERIA . '.vin_CuentaSap as b using (cbs_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancariaSap as c using (ibs_id)
            INNER JOIN ' . BD_TESORERIA . '.vin_SefinPlanCuenta as d using (cbs_id)
            WHERE a.cbs_id = ? and b.tcs_id = 2;';
        $this->conectar();
        $this->setSql($getCuentas);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'TcuentaContable';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    private function generarSap($banco, $clabe, $cuenta)
    {
        if (substr($banco, -6) == ', S.A.') {
            $banco = substr($banco, 0, -6);
        }

        $conexion = new AccesoDatos();
        $conexion->setSql('SELECT ibs_id FROM ' . BD_TESORERIA . '.cat_InstitucionBancariaSap WHERE ibs_observacion = ? ');
        $conexion->conectar();
        $conexion->consultar([$banco]);
        $ibs = $conexion->extraer();
        $ibs = $ibs['ibs_id'];
        $conexion->desconectar();

        $sql2 = 'SELECT * FROM ' . BD_TESORERIA . '.vin_CuentaSap WHERE ibs_id = ? and tcs_id = 2';

        $conexion->setSql($sql2);
        $conexion->conectar();
        $conexion->consultar([$ibs]);
        $n2 = 0;
        $cta = '';
        $consecutivo = 0;
        $conexion->desconectar();
        if ($conexion->getNumeroRegistros() > 0) {
            $sql3 = 'SELECT cta_id as cta, ibs_id, cbs_consecutivo as consecutivo, cgc_id FROM ' . BD_TESORERIA . '.vin_CuentaSap WHERE ibs_id = ? and tcs_id=2 ORDER BY cbs_consecutivo desc limit 1 for update';
            $conexion->setSql($sql3);
            $conexion->conectar();
            $conexion->consultar([$ibs]);
            $conexion->desconectar();
            $row = $conexion->extraer();

            $cta .= $row['cta'] + 10;
            $consecutivo = $row['consecutivo'] + 1;

            if ($cta == 1000) {
                $cta = '000';
                $cgc = $row['cgc_id'] + 10;
            } else {
                $cgc = $row['cgc_id'];
            }

        } else {
            switch ($ibs) {
                case 1:
                case 2:
                case 7:
                case 9:
                    $n2 = 0;
                    break;
                case 0:
                case 3:
                case 8:
                case 6:
                case 13:
                    $n2 = 1;
                    break;
                case 4:
                    $n2 = 2;
                    break;
                case 5:
                    $n2 = 3;
                    break;
                default:
                    $this->mixScript("notify('El banco no esta registrado para dar de alta una cuenta de cheque', 'warning');");
                    return 0;
                    break;
            };
            $cgc = $n2 + $ibs;
            $cta = '000';
            $consecutivo = 001;
        }
        $this->prm3 = new stdClass();
        $this->prm3->cbs_id = $clabe;
        $this->prm3->cbs_cuentaBanco = $cuenta;
        $this->prm3->ger_id = 1;
        $this->prm3->cgp_id = 1;
        $this->prm3->rub_id = 2;
        $this->prm3->cgcu_id = '0';
        $this->prm3->cgsc_id = 3;
        $this->prm3->cgc_id = $cgc;
        $this->prm3->cta_id = $cta;
        $this->prm3->ibs_id = $ibs;
        $this->prm3->tcs_id = 2;
        $this->prm3->consecutivo = $consecutivo;
        $this->prm3->abc = 'a';
        CuentaSap::abc($this->prm3);
    }
    private function generaInversionSap($llenaTabla = true)
    {
        $this->prm->cuenta = (isset($this->prm->cuenta)) ? $this->prm->cuenta : '';
        $this->prm->cuentain = (isset($this->prm->cuentain)) ? $this->prm->cuentain : '';

        if ($this->prm->cuentain == '') {
            $this->mixScript('notify("Falta Cuenta de Inversión", "warning");');
            return 0;
        }
        if (strlen($this->prm->cuentain) < 12) {
            $this->mixScript('notify("La Cuenta de Inversión es Invalida", "warning");');
            return 0;
        }
        if ($this->prm->cuenta == '') {
            $this->mixScript('notify("Falta Cuenta Bancaria", "warning");');
            return 0;
        }
        if (strlen($this->prm->cuenta) < 11) {
            $this->mixScript('notify("La Cuenta de cheques es Invalida, necesita al menos 11 dígitos", "warning");');
            return 0;
        }

        $cuentaSap = $this->prm->cuenta; //clabe
        $cuentainv = $this->prm->cuentain; //cuenta de Inversion
        $conexion = new AccesoDatos();
        $params = [$cuentaSap];
        $conexion->setSql('SELECT cbs_cuenta FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin WHERE cbs_cuenta = ?;');
        $conexion->conectar();
        $conexion->consultar($params);

        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript('notify("La Cuenta Bancaria que ingresó no Existe", "warning");');
            return 0;
        }

        $conexion->setSql('SELECT cbs_inversion FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin WHERE cbs_cuenta = ?;');
        $conexion->conectar();
        $conexion->consultar($params);
        $row = $conexion->extraer();
        $cbs_inversion = $row['cbs_inversion'];

        if ($cbs_inversion == 2) {
            $this->mixScript('notify("La Cuenta Bancaria que ingresó no está autorizada para tener una cuenta inversora", "warning");');
            return 0;
        }

        $conexion->desconectar();
        $conexion->setSql('SELECT cbs_id,ibs_id,iba_id,cbs_consecutivo
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin as a
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria as b using (iba_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_CuentaSap using (cbs_id)
            WHERE cbs_cuenta = ?
            GROUP BY cbs_id,ibs_id,cbs_consecutivo;');
        $conexion->conectar();
        $conexion->consultar($params);
        $row = $conexion->extraer();

        $iba = $row['iba_id'];
        $consecutivo = $row['cbs_consecutivo'];
        $clabe = $row['cbs_id'];
        $ibs = $row['ibs_id'];
        switch ($ibs) {
            case 1:
            case 2:
            case 7:
            case 9: //banco Azteca
            case 13: //BANCO VE POR MAS, S.A.
                $n2 = 0;
                break;
            case 3:
            case 4:
            case 0:
            case 8:
            case 6:
                $n2 = 1;
                break;
            default:
                $this->mixScript("notify('El banco no esta registrado para dar de alta una cuenta de cheque', 'warning');");
                return 0;
                break;
        }
        $n3 = 0;
        switch ($ibs) {
            case 1:
                $n3 = 1;
                break;
            case 2:
            case 3:
                $n3 = 2;
                break;
            case 7:
            case 8:
                $n3 = 8;
                break;
            case 6:
                $n3 = 6;
                break;
            case 4:
            case 9:
                //banco Azteca
                $n3 = 9;
                break;
            case 13:
                //BANCO VE POR MAS, S.A.
                $n3 = 23;
                break;
            case 0:
                $n3 = 0;
                break;
            default:
                $this->mixScript("notify('El banco no esta registrado para dar de alta una cuenta de cheque', 'warning');");
                return 0;
                break;
        }
        $cgc = $n2 . $n3;
        $params = [$ibs];
        $sql2 = 'SELECT * FROM ' . BD_TESORERIA . '.vin_CuentaSap WHERE ibs_id = ? AND tcs_id = 1;';
        $conexion->setSql($sql2);
        $conexion->conectar();
        $conexion->consultar($params);
        if ($conexion->getNumeroRegistros() > 0) {
            #hay cuentas de inversion
            $sql3 = 'SELECT max(cta_id) as cta, ibs_id, max(cbs_consecutivo) as consecutivo, cgc_id FROM ' . BD_TESORERIA .
                '.vin_CuentaSap WHERE ibs_id = ? AND tcs_id = 1 GROUP BY cgc_id for update';
            $conexion->setSql($sql3);
            $conexion->conectar();
            $conexion->consultar($params);
            $row = $conexion->extraer();
            $cta = $row['cta'] + 1;
            if ($cta == 1000) {
                $cgc = $cgc + 10;
                $cta = 001;
            }
        } else {
            # es la primera
            $cta = 001;
        }
        $this->prm3 = new stdClass();
        $this->prm3->cbs_id = $clabe;
        $this->prm3->cbs_cuentaBanco = $cuentainv;
        $this->prm3->ger_id = 1;
        $this->prm3->cgp_id = 1;
        $this->prm3->rub_id = 2;
        $this->prm3->cgcu_id = '0';
        $this->prm3->cgsc_id = 4;
        $this->prm3->cgc_id = $cgc;
        $this->prm3->cta_id = $cta;
        $this->prm3->ibs_id = $ibs;
        $this->prm3->tcs_id = 1;
        $this->prm3->consecutivo = $consecutivo;
        $this->prm3->abc = 'a';
        CuentaSap::abc($this->prm3);
        $this->generaArmonizada($iba, $clabe, 1);
        $this->mixScript('notify("Se ha generado correctamente la cuenta de Inversión", "success");');
        // En caso de ser false, no manda a llamar al llena tabla
        if ($llenaTabla) {
            $params = [$clabe];
            $getinversion = '
                SELECT cbs_consecutivo as id,
                CONCAT(b.ger_id, b.cgp_id, b.rub_id, b.cgcu_id, b.cgsc_id, b.cgc_id, b.cta_id)as Cuenta_Inversion,
                CONCAT(d.ger_id, d.cgp_id, d.rub_id, d.cgcu_id, d.cgsc_id, d.cta_gpo_id, d.cta_ur_id, d.cta_ue_id, d.cgc_id,
                d.cta_id) as ARMONIZ
                FROM ' . BD_TESORERIA . '.vin_CuentaSap as b
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinPlanCuenta as d USING (cbs_id, tcs_id)
                WHERE cbs_id = ? AND b.tcs_id = 1;';
            $this->conectar();
            $this->setSql($getinversion);
            $this->setParams($params);
            $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
            $nav->idTabla = 'TcuentaContableII';
            $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
            $this->wrLlenaTabla($nav);
        }
    }

    private function generaInteres()
    {
        $this->prm->cuenta = (isset($this->prm->cuenta)) ? $this->prm->cuenta : '';
        $this->prm->cuentain = (isset($this->prm->cuentain)) ? $this->prm->cuentain : '';

        if ($this->prm->cuentain == '') {
            $this->mixScript('notify("Falta Cuenta de Interés", "warning");');
            return 0;
        }
        if (strlen($this->prm->cuentain) < 10) {
            $this->mixScript('notify("La Cuenta de Interés es Invalida", "warning");');
            return 0;
        }
        if ($this->prm->cuenta == '') {
            $this->mixScript('notify("Falta Cuenta Bancaria", "warning");');
            return 0;
        }
        if (strlen($this->prm->cuenta) < 11) {
            $this->mixScript('notify("La Cuenta Bancaria es Invalida", "warning");');
            return 0;
        }

        $cuentaSap = $this->prm->cuenta;
        $cuentaint = $this->prm->cuentain;
        $conexion = new AccesoDatos();
        $params = [$cuentaSap];
        $conexion->setSql('SELECT cbs_cuenta FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin where cbs_cuenta = ?;');
        $conexion->conectar();
        $conexion->consultar($params);
        if ($conexion->getNumeroRegistros() == 0) {
            $this->mixScript('notify("La Cuenta Bancaria que ingresó no Existe", "warning");');
            return 0;
        }
        $conexion->desconectar();

        $conexion->setSql('SELECT cbs_id, ibs_id, iba_id, cbs_consecutivo
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin as a
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria as b using (iba_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_CuentaSap using (cbs_id)
            WHERE cbs_cuenta = ?
            GROUP BY cbs_id, ibs_id, cbs_consecutivo;');
        $conexion->conectar();
        $conexion->consultar($params);
        $row = $conexion->extraer();
        $iba = $row['iba_id'];
        $consecutivo = $row['cbs_consecutivo'];
        $clabe = $row['cbs_id'];
        $ibs = $row['ibs_id'];
        $this->prm3 = new stdClass();
        $this->prm3->cbs_id = $clabe;
        $this->prm3->cbs_cuentaBanco = $cuentaint;
        $this->prm3->ger_id = '0';
        $this->prm3->cgp_id = '0';
        $this->prm3->rub_id = '0';
        $this->prm3->cgcu_id = '0';
        $this->prm3->cgsc_id = '0';
        $this->prm3->cgc_id = '0';
        $this->prm3->cta_id = '0';
        $this->prm3->ibs_id = $ibs;
        $this->prm3->tcs_id = 3;
        $this->prm3->consecutivo = $consecutivo;
        $this->prm3->abc = 'a';
        CuentaSap::abc($this->prm3);
        $this->mixScript('notify("Se ha generado la cuenta de Interes", "success");');

    }

    private function generaArmonizada($iba, $clabe, $tipo)
    {
        $conexion = new AccesoDatos();
        $cta = '0';

        $conexion->setSql('SELECT iba_abreviatura FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria WHERE iba_id = ?;');
        $conexion->conectar();
        $conexion->consultar([$iba]);
        $row = $conexion->extraer();
        $ibaAbr = $row['iba_abreviatura'];

        if (substr($ibaAbr, -6) == ', S.A.') {
            $ibaAbr = substr($ibaAbr, 0, -6);
        }

        $params = [$iba, $tipo];
        $conexion->setSql('SELECT * FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta WHERE cgc_id = ? and tcs_id = ?;');
        $conexion->conectar();
        $conexion->consultar($params);
        if ($conexion->getNumeroRegistros() > 0) {
            // consecutivo de cta_id del plan de cuentas
            $conexion->setSql('SELECT max(cta_id) as cta FROM ' . BD_TESORERIA . '.vin_SefinPlanCuenta WHERE cgc_id = ? and tcs_id = ?;');
            $conexion->conectar();
            $conexion->consultar($params);
            $row = $conexion->extraer();
            $cta = $row['cta'];
            $cta = $cta + 1;
            if (strlen($cta) < 4) {
                if (strlen($cta) < 3) {
                    if (strlen($cta) < 2) {
                        $cta = '000' . $cta;
                    } else {
                        $cta = '00' . $cta;
                    }
                } else {
                    $cta = '0' . $cta;
                }
            }
        } else {
            $cta = '0001';
        }

        $ban = false;
        $this->prms = new stdClass();
        $this->prms->cbs_id = $clabe;
        $this->prms->tcs_id = $tipo;
        $this->prms->cta_gpo_id = 1;
        $this->prms->cta_ur_id = 16;
        $this->prms->cta_ue_id = 005;
        $this->prms->ger_id = 1;
        $this->prms->cgp_id = 1;
        $this->prms->rub_id = 1;
        switch ($tipo) {
            case 1:
                $this->prms->cgcu_id = 4;
                $cuartoId = $this->getCuentaCuartoId('%' . $ibaAbr . '%', '%1.1.1.4%');
                $iba = $cuartoId['cgc_id'];
                $params = [$iba, $cta];
                $conexion->setSql(' SELECT * FROM ' . BD_CTRL . '.cg_PlanCuentas
                    WHERE cta_gpo_id = 1
                    AND cta_ur_id = 16
                    AND cta_ue_id = 005
                    AND ger_id = 1
                    AND cgp_id = 1
                    AND rub_id = 1
                    AND cgcu_id = 4
                    AND cgsc_id = 1
                    AND cgc_id = ?
                    AND cta_id = ?;');
                $conexion->conectar();
                $conexion->consultar($params);
                if ($conexion->getNumeroRegistros() > 0) {
                    $ban = true;
                }
                break;
            case 2:
                $this->prms->cgcu_id = 2;
                $cuartoId = $this->getCuentaCuartoId('%' . $ibaAbr . '%', '%1.1.1.2%');
                $iba = $cuartoId['cgc_id'];
                $params = [$iba, $cta];
                $conexion->setSql(' SELECT cta_gpo_id FROM ' . BD_CTRL . '.cg_PlanCuentas
                    WHERE cta_gpo_id = 1
                    AND cta_ur_id = 16
                    AND cta_ue_id = 005
                    AND ger_id = 1
                    AND cgp_id = 1
                    AND rub_id = 1
                    AND cgcu_id = 2
                    AND cgsc_id = 1
                    AND cgc_id = ?
                    AND cta_id = ?;');
                $conexion->conectar();
                $conexion->consultar($params);
                if ($conexion->getNumeroRegistros() > 0) {
                    $ban = true;
                }
                break;
            default:
                break;
        }
        $this->prms->cgsc_id = 1;
        $this->prms->cgc_id = $iba;
        $this->prms->cta_id = '' . $cta . '';
        $this->prms->abc = 'a';
        $this->prms->cuenta =
            $this->prms->ger_id . '.' . $this->prms->cgp_id . '.' . $this->prms->rub_id . '.' .
            $this->prms->cgcu_id . '.' . $this->prms->cgsc_id . '.' . $this->prms->cgc_id . '.' .
            $this->prms->cta_id;
        $this->prms->agc_id = 'GRAL';

        $conexion->setSql('SELECT cbs_alias FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin WHERE cbs_id = ?;');
        $conexion->consultar([$clabe]);
        $row = $conexion->extraer();
        $alias = $row['cbs_alias'];

        $this->prms->nombre = 'CTA-' . substr($clabe, -12, -1) . '-' . $alias;
        $this->prms->descripcion = '';
        $this->prms->carga = '';

        if (!$ban) {
            LanCuentas::abc($this->prms);
        }
        SefinPlanCuenta::abc($this->prms);

    }

    private function TablaBitacora()
    {
        $this->setContenedor('mostrar_bitacora');

        $tablaCC = new stdClass();
        $tablaCC->id = 'TBitacora';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'OBSERVACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'USUARIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'observacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usuario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_fecha';
        array_push($trbody, $td);

        $tablaCC->encabezado = [$tr];
        $tablaCC->cuerpo = $trbody;
        $tablaCC->comunica = new StdClass();
        $tablaCC->comunica->metodo = 'llenaTablaBitacora';
        $tablaCC->comunica->clabe = '{$("#clabeI").val()}';
        $this->mixHtml($this->wrPonTabla($tablaCC));
        // Quitamos la info de una paginación que no usaremos en ésta pantalla
        $this->mixScript('
            $(".tabla-footer").remove();
            $("#tabla-TBitacora").parent().removeClass("col-md-12");
            $("#tabla-TBitacora").parent().removeAttr("style");
        ');

        $this->llenaTablaBitacora();
    }

    private function llenaTablaBitacora()
    {
        $this->prm->clabe = isset($this->prm->clabe) ? $this->prm->clabe : '';
        $sql = 'SELECT
                bit.bit_observacion AS observacion,usr_usuario AS usuario, bit.usr_fecha
            FROM ' . BD_TESORERIABIT . '.bit_CuentaBancariaSefin bit
            INNER JOIN cat_Usuario u using(usr_id)
            WHERE
        ';
        $params = ['%' . substr($this->prm->clabe, 2) . '%'];
        if ($this->prm->clabe != '') {
            $sql .= 'cbs_id like ? ';
        }
        $this->setSql($sql . ';');
        $this->setParams($params);

        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'TBitacora';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }

    private function getCuentaCuartoId($nombre = '', $cuenta = '')
    {
        $sql = 'SELECT cgc_id FROM ' . BD_CTRL . '.cg_CuentaCuarto WHERE cgc_nombre like ? AND cgc_cuenta like ?';
        $params = [$nombre, $cuenta];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        if ($this->getNumeroRegistros() > 0) {
            return $this->extraer();
        }

        return [];
    }
}
if (tienePermiso('T.6.1.1')) {
    new ControladorCatalogos();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
