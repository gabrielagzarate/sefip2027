<?php
/**
 * En esta clase se darán de alta las cuentas de municipios, asi como la vinculación
 * 26-02-18 Se modifico para tomar en cuenta las cuentas de fideicomiso, que tienen el mismo
 * comportamiento que mas de controversia.
 *   pintaAltaControversia
 * 09-03-18 Se agrego validacion en los campos de sucursal, cuenta y clabe
 * para que solo ingresen numeros en chrome.
 * 09-03-18 Se modificó la consulta para que no se muestren las cuentas de tipo de fondo
 * Trasferencia/Inversión que surgio a partir del cambio que se hizo en Bancos UR
 * 13-03-18 Se agregó la funcionalidad de editar las cuentas rechazadas.
 *    ¿Que pasa si nos llegan a comentar que se va a ocupar la misma informacion del año 2019?
 *    Vamos a comentar lo siguiente
Metodo: llenaTablaMunicipios
La razon porque en este metodo no habilita cbb_anio=2023: No se requiere porque la cuentas de controversia
y fideicomiso no tienen defino dicho valor como
SQL: AND cbb_anio = 2023

Metodo: llenaTablaCuentasMunicipio
SQL: AND cbb.cbb_anio = 2023

Metodo: rellenaTipoFondo
SQL: AND cbb.cbb_anio=2023

Metodo: llenaTablaValidaCuenta
SQL: AND cbb_anio = 2023

Metodo: deshabilitaControversia
SQL: cbb.cbb_anio=2023 AND

Metodo: pintaRehabilitaCuenta
SQL: AND cbb_anio=2023

Metodo: llenaTablaCuentasRechazadas
SQL: AND cbb.cbb_anio=2023
 */

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/CuentaBancariaBeneficiario.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/BeneficiarioMunicipio.php';

class ControladorVinBeneficiarioMunicipio extends WebResponse
{
    protected $escritura;
    protected $validar;
    protected $nvj;

    public function __construct()
    {
        parent::__construct();

        $this->escritura = isset($_SESSION[_S]['prm']['T']['6']['2']['2']) ? true : false;
        $this->validar = isset($_SESSION[_S]['prm']['T']['6']['2']['3']) ? true : false;
        $this->nvj = $_SESSION[_S]['nvj_id'];

        switch ($this->nvj) {
            case 1:case 2:case 3:case 4:case 5:case 6:case 7:case 8:
                switch ($this->metodo) {
                    case 'pintaAltaCuenta':
                        $this->pintaAltaCuenta();
                        break;
                    case 'consultaBanco':
                        $this->consultaBanco();
                        break;
                    case 'consultaPlaza':
                        $this->consultaPlaza();
                        break;
                    case 'checaCuenta':
                        $this->checaCuenta();
                        break;
                    case 'altaCuenta':
                        $this->altaCuenta();
                        break;
                    case 'pintaValidaCuenta':
                        $this->pintaValidaCuenta();
                        break;
                    case 'llenaTablaValidaCuenta':
                        $this->llenaTablaValidaCuenta();
                        break;
                    case 'validaCuenta':
                        $this->validaCuenta();
                        break;
                    /* FUNCIONES NUEVAS */
                    case 'pintaMunicipios':
                        $this->pintaMunicipios();
                        break;
                    case 'llenaTablaMunicipios':
                        $this->llenaTablaMunicipios();
                        break;
                    case 'detalleMunicipio':
                        $this->detalleMunicipio();
                        break;
                    case 'rellenaTipoFondo':
                        $this->rellenaTipoFondo();
                        break;
                    case 'deshabilitaCuenta':
                        $this->deshabilitaCuenta();
                        break;
                    case 'pintaControversia':
                        $this->pintaControversia();
                        break;
                    case 'llenaTablaControversias':
                        $this->llenaTablaControversias();
                        break;
                    case 'enviaValidacion':
                        $this->enviaValidacion();
                        break;
                    case 'pintaAltaCuentaControversia':
                        $this->pintaAltaCuentaControversia();
                        break;
                    case 'altaCuentaControversia':
                        $this->altaCuentaControversia();
                        break;
                    case 'pintaVinculaControversia':
                        $this->pintaVinculaControversia();
                        break;
                    case 'vinculaControversia':
                        $this->vinculaControversia();
                        break;
                    case 'deshabilitaControversia':
                        $this->deshabilitaControversia();
                        break;
                    case 'cuentaExiste':
                        $this->cuentaExiste(0);
                        break;
                    case 'pintaRehabilitaCuenta':
                        $this->pintaRehabilitaCuenta();
                        break;
                    case 'rehabilitaCuenta':
                        $this->rehabilitaCuenta();
                        break;
                    case 'pintaCuentasRechazadas':
                        $this->pintaCuentasRechazadas();
                        break;
                    case 'llenaTablaCuentasRechazadas':
                        $this->llenaTablaCuentasRechazadas();
                        break;
                    case 'pintaEditaCuenta':
                        $this->pintaEditaCuenta();
                        break;
                    case 'editaCuenta':
                        $this->editaCuenta();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        //Crea el boton de regreso
        $this->mixScript('
            $("#toolsPantalla").html("");
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $this->mixHtml('
            <div class="row">
                <legend>CUENTAS MUNICIPALES</legend>
                <div class="row justify-content-evenly">
                    <fieldset class="col-md-2 col-xs-2" style="display: inline-block;">
                        <div titpp="Cuentas" class="boton_opcion" id="buscarcuentas">
                            <div class="boton_opcion_titulo">Cuentas</div>
                                <strong><img height="60" width="60" src="img/Revision_Folio	.png" alt="" ></strong>
                                <span>Municipios</span>
                        </div>
                    </fieldset>
        ');

        if ($this->escritura) {
            $this->mixHtml('
                <fieldset class="col-md-2 col-xs-2" style="display: inline-block;">
                    <div titpp="Controversias" class="boton_opcion" id="controversias">
                        <div class="boton_opcion_titulo">Otras cuentas</div>
                            <strong><img height="60" width="60" src="img/Alta_Folio.png"></strong>
                            <span>Municipios</span>
                    </div>
                </fieldset>
            ');
        }

        if ($this->validar) {
            $this->mixHtml('
                <fieldset class="col-md-2 col-xs-2" style="display: inline-block;">
                    <div titpp="Validar cuentas" class="boton_opcion" id="validacuenta">
                        <div class="boton_opcion_titulo">Validar cuentas</div>
                        <strong><img height="60" width="60" src="img/Validacion_Folio.png"></strong>
                        <span>Municipios</span>
                    </div>
                </fieldset>
            ');
        }

        $this->mixHtml('
                </div>
            </div>
        ');

        //Ejecuación de las funciones
        $this->mixScript('
            $("#toolsPantalla").html("");

            $("#buscarcuentas").click(function() {
                $("#SitemaActivo").hide();
                $("#contenedor").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaMunicipios"
                });
            });

            $("#validacuenta").click(function() {
                $("#SitemaActivo").hide();
                $("#contenedor").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaValidaCuenta"
                });
            });

            $("#controversias").click(function() {
                $("#SitemaActivo").hide();
                $("#contenedor").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaControversia"
                });
            });
        ');
    }

    // FUNCIONES PARA MUNICIPIOS
    private function pintaMunicipios()
    {
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnAltaCuenta = new stdClass();
        $btnAltaCuenta->id = 'btnAltaCuenta';
        $btnAltaCuenta->name = 'btnAltaCuenta';
        $btnAltaCuenta->text = 'Nueva cuenta';
        $btnAltaCuenta->value = 'Nueva cuenta';
        $btnAltaCuenta->type = 'button';
        $btnAltaCuenta->class = 'me-1';
        $btnAltaCuenta->color = 'azul';
        $btnAltaCuenta->icon = 'solid fa-plus me-2';
        $btnAltaCuenta->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaAltaCuenta",
                origen: "pintaMunicipios"
            });
        ';

        $btnExcel = new stdClass();
        $btnExcel->id = 'ListadoExcel';
        $btnExcel->class = 'btn-info';
        $btnExcel->title = 'Reporte Municipios';
        $btnExcel->value = 'Excel';
        $btnExcel->color = 'verde';
        $btnExcel->class = 'me-1';
        $btnExcel->icon = 'solid fa-file-excel me-2';
        $btnExcel->name = 'excMunicipios';
        $btnExcel->click = '
            var form = $("<form>")
            .attr("action", "rep/excel/tes/catalogo/exp_CuentasMunicipios.php")
            .attr("method", "post")
            .attr("target", "_blank")
            .attr("id", "enviaPost")
            $("body").append(form);
            form.submit();
            form.remove();
        ';

        //Dibuja el boton
        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnAltaCuenta));
        }
        $this->agregaTools($this->ponBoton($btnExcel));

        $inputMunicipio = new stdClass();
        $inputMunicipio->id = 'inputMunicipio';
        $inputMunicipio->name = 'inputMunicipio';
        $inputMunicipio->placeholder = 'MUNICIPIO';
        $inputMunicipio->enDiv = true;
        if (isset($this->prm->mun_busqueda)) {
            $inputMunicipio->value = $this->prm->mun_busqueda;
        }

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar';
        $btnBuscar->name = 'btnBuscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = 'me-1';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"llenaTablaMunicipios",
                mun_busqueda: $("#inputMunicipio").val()
            })';

        // TABLA
        $tabla = new stdClass();
        $tabla->id = 'tablaMunicipios';
        $tabla->titulo = 'LISTADO DE MUNICIPIOS';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMunicipios';
        $tabla->comunica->mun_busqueda = '{$("#inputMunicipio").val()}';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);

        // CABECERA DE LA TABLA
        $tr = array();

        $th = new stdClass();
        $th->texto = 'MUNICIPIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'VER CUENTAS';
        $th->align = 'center';
        $th->width = '10rem';
        array_push($tr, $th);

        // CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'mun_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->class = 'd-grid gap-2 col-3 mx-auto btnTooltip btnVer';
        $td->icono = 'solid fa-eye';
        $td->attr = new stdClass();
        $td->attr->mun_id = 'mun_id';
        $td->attr->mun_nombre = 'mun_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <legend>CUENTAS MUNICIPIOS</legend>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputMunicipio) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
        $this->llenaTablaMunicipios();

        $this->pintaCuentasRechazadas();
        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            $("#btnregresarindex").removeClass("btn-default");
        ');
    }

    private function llenaTablaMunicipios()
    {
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';

        $tiposCuentas = array();
        array_push($tiposCuentas, '(.*)controversia(.*)');
        array_push($tiposCuentas, '(.*)fideicomiso(.*)');

        $stringCuentas = implode('|', $tiposCuentas);
        // Desabilito el filtro de cbb_anio=2023 porque se esta consultando que existe controversia y fideicomiso
        // y estas son cuentas especiales y no tienen asignado el año como 2023 aun.

        $params = [];

        $sql = '
            SELECT mun.mun_id, mun.mun_nombre,
                CASE WHEN mun.mun_id IN (
                    SELECT DISTINCT mun_id
                    FROM ' . BD_TESORERIA . '.cat_MunicipioProveedor AS mun
                    LEFT JOIN ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS vbm USING (mun_id)
                    WHERE vbm.cbb_id NOT IN (
                        SELECT cbb_id
                        FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
                        WHERE tbe_id = "MUN"
                        #AND cbb_anio = ' . APP_EJERCICIO . '
                        AND cbb_nombre LIKE "%controversia%"
                    )
                    OR mun.mun_id NOT IN (
                        SELECT DISTINCT mun_id
                        FROM ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio
                    )
                )
                THEN 1
                ELSE 2 END AS mun_asociar_controversia,

                CASE WHEN mun.mun_id IN (
                    SELECT DISTINCT mun_id
                    FROM ' . BD_TESORERIA . '.cat_MunicipioProveedor AS mun
                    LEFT JOIN ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS vbm USING (mun_id)
                    WHERE vbm.cbb_id NOT IN (
                        SELECT cbb_id
                        FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
                        WHERE tbe_id = "MUN"
                        #AND cbb_anio = ' . APP_EJERCICIO . '
                        AND cbb_nombre LIKE "%fideicomiso%"
                    )
                    OR mun.mun_id NOT IN (
                        SELECT DISTINCT mun_id
                        FROM ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio
                    )
                )
                THEN 1
                ELSE 2 END AS mun_asociar_fideicomiso,

                CASE WHEN mun.mun_id IN (
                    SELECT DISTINCT mun_id
                    FROM ' . BD_TESORERIA . '.cat_MunicipioProveedor AS mun
                    LEFT JOIN ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS vbm USING (mun_id)
                    WHERE vbm.cbb_id IN (
                        SELECT cbb_id
                        FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
                        WHERE tbe_id = "MUN"
                        #AND cbb_anio = ' . APP_EJERCICIO . '
                        AND cbb_nombre REGEXP "' . $stringCuentas . '"
                    )
                )
                THEN 1
                ELSE 2 END AS mun_restaurar
            FROM ' . BD_TESORERIA . '.cat_MunicipioProveedor AS mun
        ';

        if (!empty($this->prm->mun_busqueda)) {
            array_push($params, '%' . $this->prm->mun_busqueda . '%');
            $sql .= 'WHERE mun_nombre LIKE ?';
        }
        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaMunicipios";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".btnTooltip").attr("data-toggle", "tooltip");
            $(".btnTooltip").attr("data-placement", "left");
            $(".btnEliminar").attr("title", "Deshabilitar");
            $(".btnAsociarCon").attr("title", "Asociar a cuenta de controversia");
            $(".btnAsociarFid").attr("title", "Asociar a cuenta de fideicomiso");
            $(".btnRestaurarC").attr("title", "Restaurar cuentas anteriores");
            $(".btnVer").attr("title", "Ver");
            $("[data-toggle=\"tooltip\"]").tooltip();
            const tooltip = document.querySelectorAll(\'.tooltip\');
            tooltip.forEach(el => el.remove()); // elimina del DOM

            $(".btnAlta").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "altaCuentaMun",
                    mun_id: $(this).attr("mun_id"),
                    mun_busqueda: "' . $this->prm->mun_busqueda . '"

                });
            });

            $(".btnAlta").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "altaCuentaMun",
                    mun_id: $(this).attr("mun_id"),
                    mun_busqueda: "' . $this->prm->mun_busqueda . '"

                });
            });

            $(".btnVer").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "detalleMunicipio",
                    mun_id: $(this).attr("mun_id"),
                    mun_nombre: $(this).attr("mun_nombre"),
                    mun_busqueda: "' . $this->prm->mun_busqueda . '"
                });
            });

            $(".btnEliminar").click(function() {
                alertify.confirm(
                    "<h5>Deshabilitar cuenta</h5",
                    "¿Confirma que desea deshabilitar la cuenta?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo: "deshabilitaControversia",
                            cbb_id: $(this).attr("cbb_id")
                        });
                    },
                    function(){
                    },
                ).set("defaultFocus", "cancel").set("labels", {ok:"Autorizar", cancel:"Cancelar"});
            });

            $(".btnAsociarCon").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaVinculaControversia",
                    mun_id: $(this).attr("mun_id"),
                    mun_nombre: $(this).attr("mun_nombre"),
                    mun_busqueda: "' . $this->prm->mun_busqueda . '",
                    mun_tipo_cta: "controversia"
                });
            });

            $(".btnAsociarFid").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaVinculaControversia",
                    mun_id: $(this).attr("mun_id"),
                    mun_nombre: $(this).attr("mun_nombre"),
                    mun_busqueda: "' . $this->prm->mun_busqueda . '",
                    mun_tipo_cta: "fideicomiso"
                });
            });

            $(".btnRestaurarC").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaRehabilitaCuenta",
                    mun_id: $(this).attr("mun_id"),
                    mun_nombre: $(this).attr("mun_nombre"),
                    mun_busqueda: "' . $this->prm->mun_busqueda . '"
                });
            });
        ');
    }

    private function detalleMunicipio()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';

        if (empty($this->prm->mun_id) || empty($this->prm->mun_nombre)) {
            $this->mixScript('
                notify("Faltan datos", "error");
            ');
        }

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaMunicipios",
                mun_busqueda: "' . $this->prm->mun_busqueda . '"
            });
        ';

        $btnAltaCuenta = new stdClass();
        $btnAltaCuenta->id = 'btnAltaCuenta';
        $btnAltaCuenta->name = 'btnAltaCuenta';
        $btnAltaCuenta->text = 'Nueva cuenta';
        $btnAltaCuenta->value = 'Nueva cuenta';
        $btnAltaCuenta->type = 'button';
        $btnAltaCuenta->class = 'me-1';
        $btnAltaCuenta->color = 'azul';
        $btnAltaCuenta->icon = 'fa-solid fa-plus me-2';
        $btnAltaCuenta->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaAltaCuenta",
                mun_id: "' . $this->prm->mun_id . '",
                mun_busqueda: "' . $this->prm->mun_busqueda . '",
                mun_nombre: "' . $this->prm->mun_nombre . '",
                origen: "detalleMunicipio"
            });
        ';

        //Dibuja el boton
        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnAltaCuenta));
        }

        // TABLA
        $tabla = new stdClass();
        $tabla->id = 'tablaCuentasMunicipio';
        $tabla->titulo = 'LISTADO DE CUENTAS DEL ' . $this->prm->mun_nombre;

        // CABECERA DE LA TABLA
        $tr = array();

        $th = new stdClass();
        $th->texto = 'NOMBRE CTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLABE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FONDO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTATUS';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'OPCIONES';
        $th->align = 'center';
        array_push($tr, $th);

        $tabla->encabezado = array($tr);
        //Template
        $tabla->template = '
            <tr class="odd">
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${cbb_nombre}</label>
                </td>
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${cbb_id}</label>
                </td>
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${cbb_cuenta}</label>
                </td>
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${iba_abreviatura}</label>
                </td>
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${tfn_nombre}</label>
                </td>
                <td class="sorting_1">
                    <label class="" style="text-align:center; width:100%;">${eba_nombre}</label>
                </td>

                <td class="sorting_1">
                    {{if eba_id == 6}}
                        {{if tcb_id =="C" || tcb_id =="F"}}
                            <button title=""
                                class="btn btn-outline-danger btn-sm button small  boton btnTooltip btnEliminar d-grid gap-2 mx-auto flat table-tooltip"
                                mun_id="${mun_id}"
                                tfn_id="${tfn_id}"
                                cbb_id="${cbb_id}"
                                tcb_id="${tcb_id}"
                                data-toggle="tooltip"
                                data-placement="left"
                                data-original-title="Desvincular">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        {{else}}
                            <button title=""
                                class="btn btn-outline-danger btn-sm button small  boton btnTooltip btnEliminar d-grid gap-2 mx-auto flat table-tooltip"
                                mun_id="${mun_id}"
                                tfn_id="${tfn_id}"
                                cbb_id="${cbb_id}"
                                tcb_id="${tcb_id}"
                                data-toggle="tooltip"
                                data-placement="left"
                                data-original-title="Deshabilitar">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        {{/if}}
                    {{/if}}
                    {{if eba_id == 3}}
                        <button title=""
                            class="btn btn-outline-success btn-sm button small  boton btnTooltip btnAutorizar d-grid gap-2 mx-auto flat table-tooltip"
                            mun_id="${mun_id}"
                            tfn_id="${tfn_id}"
                            cbb_id="${cbb_id}"
                            tcb_id="${tcb_id}"
                            data-toggle="tooltip"
                            data-placement="left"
                            data-original-title="Enviar para autorización">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    {{/if}}
                </td>
            </tr>
        ';

        $html = '
            <div class="row">
                <legend>CUENTAS MUNICIPIOS</legend>
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            // Quitamos la info de una paginación que no usaremos en ésta pantalla
            $(".tabla-footer").remove();
        ');
        $this->llenaTablaCuentasMunicipio();
    }

    private function llenaTablaCuentasMunicipio()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $params = [];

        if (empty($this->prm->mun_id)) {
            $this->mixScript('
                notify("Falta mun_id", "error");
            ');
            return;
        } elseif (empty($this->prm->mun_nombre)) {
            $this->mixScript('
                notify("Falta mun_nombre", "error");
            ');
            return;
        }
        array_push($params, $this->prm->mun_id);

        $sql = '
            SELECT
                cbb_nombre,
                cbb_id,
                cbb_cuenta,
                cbb_sucursal,
                iba_abreviatura,
                tfn_nombre,
                eba_id,
                eba_nombre,
                mun_id,
                tfn_id,
                eba_id
                tcb_id
            FROM ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS vbm
            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario AS cbb USING (cbb_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoCuenta AS eba USING (eba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor AS mun USING (mun_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoFondo AS tfn USING (tfn_id)
            WHERE tbe_id = "MUN"
            AND tfn_id IN (2, 3, 4)
            AND cbb.cbb_anio = ' . APP_EJERCICIO . '
            AND mun_id = ? ';

        $this->setSql($sql);
        $this->setParams($params);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaCuentasMunicipio";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".btnTooltip").attr("data-toggle", "tooltip");
            $(".btnTooltip").attr("data-placement", "left");
            var tcbx = $(this).attr("tcb_id");
            $(".btnAutorizar").attr("title", "Enviar para autorización");
            $("[data-toggle=\"tooltip\"]").tooltip();

            $(".btnEliminar").click(function() {
                var mun = $(this).attr("mun_id");
                var tfn = $(this).attr("tfn_id");
                var cbb = $(this).attr("cbb_id");
                var tcb = $(this).attr("tcb_id");
                var accion = "";
                var tituloAccion = "";
                if (tcb == "F" || tcb == "C") {
                    tituloAccion = "Desvincular";
                    accion = "desvincular";
                } else {
                    tituloAccion = "Deshabilitar";
                    accion = "deshabilitar";
                }
                alertify.confirm(
                    "<h5>"+tituloAccion+" cuenta</h5>",
                    "¿Confirma que desea "+accion+" la cuenta?",
                    function() {
                        comunica("' . $this->pantalla . '", {
                            metodo: "deshabilitaCuenta",
                            mun_id: mun,
                            tfn_id: tfn,
                            cbb_id: cbb,
                            mun_nombre: "' . $this->prm->mun_nombre . '",
                            mun_busqueda: "' . $this->prm->mun_busqueda . '",
                            origen: "detalleMunicipio"
                        });
                    },
                    function() {
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".btnAutorizar").click( function() {
                var mun = $(this).attr("mun_id");
                var tfn = $(this).attr("tfn_id");
                var cbb = $(this).attr("cbb_id");
                alertify.confirm(
                    "<h5>Enviar cuenta</h5>",
                    "¿Confirma que desea mandar la cuenta a validación?",
                    function() {
                        comunica("' . $this->pantalla . '", {
                            metodo: "enviaValidacion",
                            mun_id: mun,
                            tfn_id: tfn,
                            cbb_id: cbb,
                            mun_nombre: "' . $this->prm->mun_nombre . '",
                            mun_busqueda: "' . $this->prm->mun_busqueda . '",
                            origen: "detalleMunicipio"
                        });
                    },
                    function() {
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            const tooltipList = document.querySelectorAll(\'.tooltip\');
            tooltipList.forEach(el => el.remove()); // elimina del DOM
        ');
    }

    private function rellenaTipoFondo()
    {
        $this->setContenedor('tipoFondo');
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $params = [(int) $this->prm->mun_id];

        $sql = ' SELECT tfn_id, tfn_nombre
            FROM ' . BD_TESORERIA . '.cat_TipoFondo AS tfn
            WHERE tfn_id IN (2,3,4)
            AND tfn_id NOT IN
                (SELECT vbm.tfn_id
                FROM ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS vbm
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario cbb USING(cbb_id)
                WHERE mun_id = ' . $this->prm->mun_id . ' AND cbb.cbb_anio = ' . APP_EJERCICIO . ')
        ';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar();
        $numReg = $con->getNumeroRegistros();

        if ($numReg == 0) {
            $this->mixScript('
                notify("Este municipio tiene cuentas con todos los tipos de fondo");
            ');
            $comboTipoFondo = new stdClass();
            $comboTipoFondo->id = 'comboTipoFondo';
            $comboTipoFondo->name = 'comboTipoFondo';
            $comboTipoFondo->enDiv = true;
            $comboTipoFondo->mensaje = 'SELECCIONE UNA OPCIÓN';
            $comboTipoFondo->label = 'TIPO DE FONDO';
            $comboTipoFondo->sql = array();
        }

        $comboTipoFondo = new stdClass();
        $comboTipoFondo->id = 'comboTipoFondo';
        $comboTipoFondo->name = 'comboTipoFondo';
        $comboTipoFondo->enDiv = true;
        $comboTipoFondo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboTipoFondo->label = 'TIPO DE FONDO';
        $comboTipoFondo->sql = $sql;

        $this->mixHtml($this->ponCombo($comboTipoFondo));
    }

    private function pintaAltaCuenta()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $this->prm->origen = isset($this->prm->origen) ? $this->prm->origen : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "' . $this->prm->origen . '",
                mun_busqueda: "' . $this->prm->mun_busqueda . '",
                mun_nombre: "' . $this->prm->mun_nombre . '",
                mun_id: "' . $this->prm->mun_id . '"
            });
        ';

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'btnGuardar';
        $btnGuardar->name = 'btnGuardar';
        $btnGuardar->text = 'Guardar';
        $btnGuardar->value = 'Guardar';
        $btnGuardar->type = 'button';
        $btnGuardar->class = 'me-1';
        $btnGuardar->color = 'verde';
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->click = '
            if ($("#comboMunicipio").val() == "") {
                notify("Por favor, seleccione un municipio", "warning");
            } else if ($("#inputMunicipio").val() == "") {
                notify("Por favor, seleccione un municipio", "warning");
            } else if ($("#inputAlias").val() == "") {
                notify("Por favor, agregue un alias", "warning");
            } else if ($("#comboInstitucionBancaria").val() == "") {
                notify("Por favor, seleccione un banco", "warning");
            } else if ($("#inputClabe").val() == "") {
                notify("Por favor, ingrese la CLABE interbancaria", "warning");
            } else if ($("#inputClabe").val().length != 18) {
                notify("La CLABE interbancaria debe contener 18 caracteres", "warning");
            } else if ($("#inputCuenta").val() == "") {
                notify("Por favor, ingrese la cuenta bancaria", "warning");
            } else if ($("#inputFechaApertura").val() == "") {
                notify("Por favor, seleccione la fecha de apertura", "warning");
            } else if ($("#comboPlazaBancaria").val() == "") {
                notify("Por favor, seleccione una plaza bancaria", "warning");
            } else if ($("#comboTipoFondo").val() == "") {
                notify("Por favor, seleccione un tipo de fondo", "warning");
            } else {
                alertify.confirm(
                    "<h5>Alta de cuenta</h5>",
                    "¿Confirma que desea dar de alta la cuenta?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo:"altaCuenta",
                            mun_id: $("#comboMunicipio").val(),
                            nombre: $("#inputMunicipio").val(),
                            alias: $("#inputAlias").val(),
                            iba_id: $("#comboInstitucionBancaria").val(),
                            cbb_id: $("#inputClabe").val(),
                            cuenta: $("#inputCuenta").val(),
                            fechaApertura: $("#inputFechaApertura").val(),
                            pbn_id: $("#comboPlazaBancaria").val(),
                            sucursal: $("#inputSucursal").val(),
                            tfn_id: $("#comboTipoFondo").val(),
                            mun_busqueda: "' . $this->prm->mun_busqueda . '",
                            mun_nombre: "' . $this->prm->mun_nombre . '",
                            origen: "' . $this->prm->origen . '"
                        });
                    },
                    function(){
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            }
        ';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = 'me-1';
        $btnLimpiar->color = 'rojo';
        $btnLimpiar->icon = 'solid fa-trash-can me-2';
        $btnLimpiar->click = '
            $("#comboMunicipio").val("");
            $("#inputMunicipio").val("");
            $("#inputAlias").val("");
            $("#inputClabe").val("");
            $("#inputCuenta").val("");
            $("#inputSucursal").val("");
            $("#inputFechaApertura").val("");
            $("#comboInstitucionBancaria").val("");
            $("#comboPlazaBancaria").val("");
            $("#comboTipoFondo").val("");
            $(".selectpicker").selectpicker("refresh");
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardar));
        }
        $this->agregaTools($this->ponBoton($btnLimpiar));

        $comboMunicipio = new stdClass();
        $comboMunicipio->id = 'comboMunicipio';
        $comboMunicipio->name = 'comboMunicipio';
        $comboMunicipio->enDiv = true;
        $comboMunicipio->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboMunicipio->label = 'MUNICIPIO';
        $comboMunicipio->search = true;
        $comboMunicipio->sql = ' SELECT mun_id, mun_nombre FROM ' . BD_TESORERIA . '.cat_MunicipioProveedor; ';
        $comboMunicipio->change = '
            if ($(this).val() != "") {
                comunica("' . $this->pantalla . '",{
                    metodo : "rellenaTipoFondo",
                    mun_id : $("#comboMunicipio").val()
                })
            }
        ';

        $inputMunicipio = new stdClass();
        $inputMunicipio->id = 'inputMunicipio';
        $inputMunicipio->name = 'inputMunicipio';
        $inputMunicipio->enDiv = true;
        $inputMunicipio->label = 'NOMBRE DE LA CUENTA';

        $inputAlias = new StdClass();
        $inputAlias->id = 'inputAlias';
        $inputAlias->name = 'inputAlias';
        $inputAlias->enDiv = true;
        $inputAlias->maxlength = 20;
        $inputAlias->label = 'ALIAS DE LA CUENTA';

        $inputSucursal = new StdClass();
        $inputSucursal->id = 'inputSucursal';
        $inputSucursal->name = 'inputSucursal';
        $inputSucursal->enDiv = true;
        $inputSucursal->formato = 'entero';
        $inputSucursal->maxlength = 10;
        $inputSucursal->label = 'SUCURSAL';
        $inputSucursal->class = '';

        $comboInstitucionBancaria = new stdClass();
        $comboInstitucionBancaria->id = 'comboInstitucionBancaria';
        $comboInstitucionBancaria->name = 'comboInstitucionBancaria';
        $comboInstitucionBancaria->enDiv = true;
        $comboInstitucionBancaria->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboInstitucionBancaria->label = 'BANCO';
        $comboInstitucionBancaria->disabled = true;
        $comboInstitucionBancaria->sql = ' SELECT iba_id, iba_abreviatura
            FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria
            WHERE sts_id = 1
        ';

        $inputClabe = new StdClass();
        $inputClabe->id = 'inputClabe';
        $inputClabe->name = 'inputClabe';
        $inputClabe->enDiv = true;
        $inputClabe->formato = 'entero';
        $inputClabe->maxlength = 18;
        $inputClabe->label = 'CLABE INTERBANCARIA';
        $inputClabe->class = '';

        $inputCuenta = new StdClass();
        $inputCuenta->id = 'inputCuenta';
        $inputCuenta->name = 'inputCuenta';
        $inputCuenta->enDiv = true;
        $inputCuenta->formato = 'entero';
        $inputCuenta->maxlength = 11;
        $inputCuenta->label = 'CUENTA BANCARIA';
        $inputCuenta->class = '';
        $inputCuenta->readonly = true;

        $inputFechaApertura = new stdClass();
        $inputFechaApertura->id = 'inputFechaApertura';
        $inputFechaApertura->name = 'inputFechaApertura';
        $inputFechaApertura->label = 'FECHA DE APERTURA';
        $inputFechaApertura->enDiv = true;
        $inputFechaApertura->formato = 'fecha';
        $inputFechaApertura->value = date("d/m/Y");

        $comboPlazaBancaria = new StdClass();
        $comboPlazaBancaria->id = 'comboPlazaBancaria';
        $comboPlazaBancaria->name = 'comboPlazaBancaria';
        $comboPlazaBancaria->enDiv = true;
        $comboPlazaBancaria->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboPlazaBancaria->label = 'PLAZA BANCARIA';
        $comboPlazaBancaria->disabled = true;
        $comboPlazaBancaria->sql = ' SELECT pbn_id, pbn_nombre
            FROM ' . BD_TESORERIA . '.cat_PlazaBancaria
            WHERE pbn_id>0;
        ';

        $comboTipoFondo = new stdClass();
        $comboTipoFondo->id = 'comboTipoFondo';
        $comboTipoFondo->name = 'comboTipoFondo';
        $comboTipoFondo->enDiv = true;
        $comboTipoFondo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboTipoFondo->label = 'TIPO DE FONDO';
        $comboTipoFondo->opciones = array();

        $this->mixHTML('
            <div class="row">
                <legend>ALTA DE CUENTA</legend>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboInstitucionBancaria) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputClabe) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputCuenta) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4" id="tipoFondo">
                    ' . $this->ponCombo($comboTipoFondo) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputSucursal) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboPlazaBancaria) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponCombo($comboMunicipio) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputMunicipio) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputAlias) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputFechaApertura) . '
                </fieldset>
            </div>
        ');

        if (!empty($this->prm->mun_id)) {
            $this->mixScript('
                $("#comboMunicipio").val("' . $this->prm->mun_id . '");
                $(".selectpicker").selectpicker("refresh");
                $("#inputMunicipio").val($("#comboMunicipio option:selected").text().split("-")[1].trim());
                $("#inputAlias").val($("#comboMunicipio option:selected").text().split("-")[1].trim().substring(13, 33));

                comunica("' . $this->pantalla . '",{
                    metodo : "rellenaTipoFondo",
                    mun_id : $("#comboMunicipio").val()
                });
            ');
        }

        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            $(".soloNumeros").keydown(function(evt) {
                var charCode = (evt.which) ? evt.which : event.keyCode
                if ((charCode != 8) && (charCode < 48 || charCode > 57))
                    return false;
                return true;
            });

            $("#comboMunicipio").blur(function() {
                if ($("#comboMunicipio").val() == "") {
                    notify("Por favor, seleccione un municipio", "warning");
                }
            });

            $("#inputMunicipio").blur(function() {
                if ($("#inputMunicipio").val() == "") {
                    notify("Por favor, agregue un municipio", "warning");
                }
            });

            $("#inputAlias").blur(function() {
                if ($("#inputAlias").val() == "") {
                    notify("Por favor, agregue un alias", "warning");
                }
            });

            $("#comboMunicipio").change(function() {
                if ($("#comboMunicipio").val()!="")
                {
                    $("#inputMunicipio").val($("#comboMunicipio option:selected").text().split("-")[1].trim());
                    $("#inputAlias").val($("#comboMunicipio option:selected").text().split("-")[1].trim().substring(13, 33));
                } else {
                    $("#inputMunicipio").val("");
                    $("#inputAlias").val("");
                }
            });

            $("#inputMunicipio").keyup(function() {
                $("#inputAlias").val($("#inputMunicipio").val().trim().substring(0, 20));
            });

            var pcaCI = /^[\d]/;
            $("#inputClabe").keyup(function(e) {
                escribe();
            });
            $("#inputClabe").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return pcaCI.test(String.fromCharCode(keynum));
            });
            $("#inputClabe").blur(function() {
                if ($(this).val().length !=18) {
                    notify("La CLABE no tiene los dígitos necesarios (18)", "error");
                }
                else{
                    comunica("' . $this->pantalla . '",{metodo:"checaCuenta","clabe":$(this).val()});
                }
            });
            $("#inputSucursal").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return pcaCI.test(String.fromCharCode(keynum));
            });
            function escribe() {
                if (document.getElementById("inputClabe").value.length == 3) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaBanco",
                        param: $("#inputClabe").val()
                    });
                }
                if (document.getElementById("inputClabe").value.length == 6) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaPlaza",
                        param: $("#inputClabe").val().substring(3)
                    });
                }
            }
        ');
    }
    private function consultaBanco()
    {
        /* el # debe ser removido cuando el modelo de bancos UR ya esté en la base de datos 2023*/
        $mensaje = '';
        $params = [$this->prm->param];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT iba_id FROM " . BD_TESORERIA . ".cat_InstitucionBancaria WHERE iba_numero = ?;");
        $conexion->consultar($params);
        $valor = $conexion->extraer();

        if ($conexion->getNumeroRegistros() > 0) {
            $mensaje = '
                $("#comboInstitucionBancaria > option[value=\'' . $valor['iba_id'] . '\']").attr("selected", "selected");
                $("#comboInstitucionBancaria").selectpicker("refresh");
            ';
        } else {
            $mensaje = '
                notify("La institución bancaria que intenta buscar no existe","warning");
                $("#comboInstitucionBancaria > option[value=\'\']").attr("selected", "selected");
                $("#comboInstitucionBancaria").selectpicker("refresh");
            ';
        }

        $this->mixScript($mensaje);
        $conexion->desconectar();
    }

    private function consultaPlaza()
    {
        $mensaje = '';
        $params = [$this->prm->param];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('SELECT pbn_id,pbn_nombre FROM ' . BD_TESORERIA . '.cat_PlazaBancaria WHERE pbn_numero = ? AND pbn_id>0;');
        $conexion->consultar($params);
        $valor = $conexion->extraer();

        if ($conexion->getNumeroRegistros() > 0) {
            $mensaje = '
                $("#comboPlazaBancaria > option[value=\'' . $valor['pbn_id'] . '\']").attr("selected", "selected");
                $("#comboPlazaBancaria").selectpicker("refresh");
            ';
        } else {
            $mensaje = '
                notify("La plaza bancaria que intenta buscar no se encuentra","warning");
                $("#comboPlazaBancaria > option[value=\'\']").attr("selected", "selected");
                $("#comboPlazaBancaria").selectpicker("refresh");
            ';
        }
        $this->mixScript($mensaje);
        $conexion->desconectar();
    }
    //**********************************Verifica que la cuenta bancaria (CLABE) no exista ya en la base de datos *****************************/
    private function checaCuenta($cbb = null)
    {
        $params = [];
        if ($this->prm->clabe !== trim($this->prm->clabe)) {
            $this->mixScript('
                 notify("Quitar de la CLABE INTERBANCARIA los espacios al principio o al final","warning");
            ');
            return;
        }
        if (is_null($cbb)) {
            array_push($params, '%' . $this->prm->clabe . '%');
        } else {
            array_push($params, '%' . $cbb . '%');
        }
        $sql = 'SELECT cbb_id FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario WHERE
            cbb_id like ?';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $params = [];

        if ($this->getNumeroRegistros() == 1 && !is_null($cbb)) {
            return false;
        }
        if ($this->getNumeroRegistros() == 1 && !is_null($cbb)) {
            $this->mixScript('notify("La CLABE bancaria ' . $this->prm->clabe . ' ya existe en la base de datos","error");');
        } else {
            $this->conectar();
            if (is_null($cbb)) {
                array_push($params, substr($this->prm->clabe, 0, 3));
            } else {
                array_push($params, substr($cbb, 0, 3));
            }
            $this->setSql("SELECT iba_id FROM " . BD_TESORERIA . ".cat_InstitucionBancaria WHERE iba_numero = ? ;");
            $this->consultar($params);
            $valorIntitucionBancaria = $this->extraer();

            $params = [];
            if (is_null($cbb)) {
                array_push($params, substr($this->prm->clabe, 3, 3));
            } else {
                array_push($params, substr($cbb, 3, 3));
            }
            $this->setSql("SELECT pbn_id,pbn_nombre FROM " . BD_TESORERIA . ".cat_PlazaBancaria WHERE pbn_numero = ? ;");
            $this->consultar($params);
            $valor_plaza = $this->extraer();
            $this->desconectar();

            $cuenta = substr(($cbb === null ? $this->prm->clabe : $cbb), -12, -1);
            $this->mixScript('
                $("#inputCuenta").val("' . $cuenta . '");

                $("#comboInstitucionBancaria > option[value=\'' . $valorIntitucionBancaria['iba_id'] . '\']").attr("selected", "selected");
                $("#comboInstitucionBancaria").selectpicker("refresh");

                $("#comboPlazaBancaria > option[value=\'' . $valor_plaza['pbn_id'] . '\']").attr("selected", "selected");
                $("#comboPlazaBancaria").selectpicker("refresh");
            ');
        }
        return true;
    }

    private function altaCuenta()
    {
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->origen = isset($this->prm->origen) ? $this->prm->origen : '';

        $cbb = new stdClass();
        $cbb->abc = 'a';
        $cbb->nombre = isset($this->prm->nombre) ? $this->prm->nombre : '';
        $cbb->alias = isset($this->prm->alias) ? $this->prm->alias : '';
        $cbb->iba_id = isset($this->prm->iba_id) ? $this->prm->iba_id : '';
        $cbb->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';
        $cbb->cuenta = isset($this->prm->cuenta) ? $this->prm->cuenta : '';
        $fecha = isset($this->prm->fechaApertura) ? $this->prm->fechaApertura : '';
        $cbb->pbn_id = isset($this->prm->pbn_id) ? $this->prm->pbn_id : '';
        $cbb->sucursal = isset($this->prm->sucursal) ? $this->prm->sucursal : '';

        $vbm = new stdClass();
        $vbm->abc = 'a';
        $vbm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $vbm->tfn_id = isset($this->prm->tfn_id) ? $this->prm->tfn_id : '';
        $vbm->cbb_id = $cbb->cbb_id;

        if (empty($cbb->nombre) || empty($cbb->alias) || empty($cbb->iba_id) || empty($cbb->cbb_id) || empty($cbb->cuenta) || empty($fecha) || strlen($cbb->pbn_id) <= 0 || strlen($cbb->sucursal) <= 0 || empty($vbm->mun_id) || empty($vbm->tfn_id)) {
            $this->mixScript('
                notify("Faltan datos", "error");
            ');
            return;
        }

        $this->conectar();
        $this->setSql('
            SELECT mun_id
            FROM tesoreria.vin_BeneficiarioMunicipio
            WHERE mun_id = ? AND tfn_id = ? AND sts_id = 1;
        ');
        $this->consultar([$vbm->mun_id,$vbm->tfn_id]);

        if ($this->getNumeroRegistros() > 0) {
            $this->mixScript('
                notify("NO PUEDE EXISTIR MAS DE UNA CUENTA ACTIVA POR TIPO FONDO", "error")
            ');
            return;
        }
        
        $fechaApertura = DateTime::createFromFormat('d/m/Y', $fecha);

        $cbb->fechaApertura = $fechaApertura->format('Y-m-d');
        $cbb->anio = APP_EJERCICIO;
        $cbb->tbe_id = 'MUN';
        $cbb->eba_id = 3;
        $cbb->cliente = '0';
        $cbb->observacion = 'ALTA DE CUENTA DESDE MODULO DE MUNICIPIOS';
        $cbb->plazaBancaria = $cbb->pbn_id;

        if ($this->cuentaExiste($cbb->cbb_id)) {
            $this->mixScript('notify("La cuenta que esta tratando de ingresar ya existe", "error");');
            return;
        }

        CuentaBancariaBeneficiario::abc($cbb);
        BeneficiarioMunicipio::abc($vbm);

        $this->mixScript('notify("Se ha insertado la cuenta bancaria ' . $cbb->cuenta . '", "success");');

        $this->mixScript('
            comunica("' . $this->pantalla . '",{
                metodo: "' . $this->prm->origen . '",
                mun_busqueda: "' . $this->prm->mun_busqueda . '",
                mun_nombre: "' . $this->prm->mun_nombre . '",
                mun_id: ' . $vbm->mun_id . '
            });
        ');
    }

    private function deshabilitaCuenta()
    {

        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $this->prm->origen = isset($this->prm->origen) ? $this->prm->origen : '';

        $this->prm->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';
        $this->prm->tfn_id = isset($this->prm->tfn_id) ? $this->prm->tfn_id : '';

        $mensaje = "deshabilitada";
        if (empty($this->prm->cbb_id)) {
            $this->mixScript('
            notify("Clabe no encontrada", "error");
            ');
            return;
        } elseif (empty($this->prm->tfn_id)) {
            $this->mixScript('
            notify("Tipo de fondo no encontrado", "error");
            ');
            return;
        }

        $vbm = new stdClass();
        $vbm->abc = 'b';
        $vbm->mun_id = $this->prm->mun_id;
        $vbm->cbb_id = $this->prm->cbb_id;
        $vbm->tfn_id = $this->prm->tfn_id;

        $params = [$this->prm->cbb_id];

        BeneficiarioMunicipio::abc($vbm);
        //Verifica si es la cuenta de fideicomiso o de controversia
        $sql = ' SELECT cbb_id
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
            WHERE (tcb_id = "F" || tcb_id = "C")
            AND eba_id = 6
            AND cbb_id = ?';

        $conCont = new AccesoDatos();
        $conCont->conectar();
        $conCont->setSql($sql);
        $conCont->consultar($params);
        $numReg = $conCont->getNumeroRegistros();
        $conCont->desconectar();

        //Valida que no se trate de la cuenta de fideicomiso o controversia
        if ($numReg == 0) {

            $cbb = new stdClass();
            $cbb->abc = 'c';
            $cbb->cbb_id = $vbm->cbb_id;
            $cbb->eba_id = '7';
            $cbb->observacion = 'CUENTA DESHABILITADA DESDE MODULO DE MUNICIPIOS';

            CuentaBancariaBeneficiario::abc($cbb);

        }

        if ($numReg > 0) {
            $mensaje = "desvinculada";
        }

        $this->mixScript('
            notify("Cuenta ' . $vbm->cbb_id . ' ' . $mensaje . '", "success");

            comunica("' . $this->pantalla . '", {
                metodo: "' . $this->prm->origen . '",
                mun_nombre: "' . $this->prm->mun_nombre . '",
                mun_busqueda: "' . $this->prm->mun_busqueda . '",
                mun_id: "' . $this->prm->mun_id . '"
            });
        ');
    }

    //FUNCIONES PARA VALIDACION
    private function enviaValidacion()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $this->prm->origen = isset($this->prm->origen) ? $this->prm->origen : '';

        $this->prm->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';

        if (empty($this->prm->cbb_id)) {
            $this->mixScript('
                notify("Clabe no encontrada", "error");
            ');
            return;
        }

        $cbb = new stdClass();
        $cbb->abc = 'c';
        $cbb->cbb_id = $this->prm->cbb_id;
        $cbb->eba_id = '4';
        $cbb->observacion = 'CUENTA ENVIADA A VALIDACIÓN DESDE MODULO DE MUNICIPIOS';

        CuentaBancariaBeneficiario::abc($cbb);

        $this->mixScript('
            notify("Cuenta ' . $cbb->cbb_id . ' enviada a validacion", "success");

            comunica("' . $this->pantalla . '", {
                metodo: "' . $this->prm->origen . '",
                mun_nombre: "' . $this->prm->mun_nombre . '",
                mun_busqueda: "' . $this->prm->mun_busqueda . '",
                mun_id: "' . $this->prm->mun_id . '"
            });
        ');
    }

    private function pintaValidaCuenta()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));

        //Declaración y llamado de la tabla de valores
        $tabla = new stdClass();
        $tabla->id = 'tablaValidacion';
        $tabla->titulo = 'Cuentas Bancarias pendientes de validación';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaValidaCuenta';

        //Declaración del cabecero de la tabla
        $tr = array();

        $th = new stdClass();
        $th->texto = 'CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLABE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE DE LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ALIAS DE LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'INSTITUCIÓN BANCARIA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NO PLAZA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE PLAZA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA AP.';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'VALIDAR';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RECHAZAR';
        $th->align = 'center';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'CTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbb_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NOMBRECTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ALIASCTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'INSTBAN';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NOPZA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'PZANOMBRE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'FECHAAP';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ESTATUS';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->class = 'btn-validar';
        $td->color = 'verde';
        $td->icono = 'solid fa-check';
        $td->title = 'Autorizar';
        $td->attr = new stdClass();
        $td->attr->mun_id = 'mun_id';
        $td->attr->tfn_id = 'tfn_id';
        $td->attr->cbb_id = 'cbb_id';
        $td->attr->cbb_nombre = 'NOMBRECTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->class = 'btn-rechazar';
        $td->color = 'rojo';
        $td->icono = 'solid fa-times';
        $td->title = 'Autorizar';
        $td->attr = new stdClass();
        $td->attr->mun_id = 'mun_id';
        $td->attr->tfn_id = 'tfn_id';
        $td->attr->cbb_id = 'cbb_id';
        $td->attr->cbb_nombre = 'NOMBRECTA';
        array_push($trbody, $td);

        //Paso de parámetros a la tabla
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
        ');

        //llamado a la tabla
        $this->llenaTablaValidaCuenta();
    }

    private function llenaTablaValidaCuenta()
    {
        $sql = 'SELECT
                cbb_alias AS ALIASCTA,
                cbb_nombre AS NOMBRECTA,
                cbb_cuenta AS CTA,
                iba_abreviatura AS INSTBAN,
                pbn_numero AS NOPZA,
                pbn_nombre AS PZANOMBRE,
                cbb_fechaApertura AS FECHAAP,
                eba_nombre AS ESTATUS,
                cbb_id,
                eba_id
            FROM
            ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario AS cbb
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS cib USING (iba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_PlazaBancaria AS cpb USING (pbn_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoCuenta AS cec USING (eba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoBeneficiario AS ctb USING (tbe_id)
            WHERE tbe_id = "MUN"
            AND sts_id = 1
            AND eba_id = 4
            AND cbb_anio = ' . APP_EJERCICIO . '
        ';

        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaValidacion";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".btn-validar").click(function() {
                var municipio = $(this).attr("mun_id");
                var fondo = $(this).attr("tfn_id");
                var clabe = $(this).attr("cbb_id");
                alertify.confirm(
                    "<h5>Validar cuenta</h5>",
                    "¿Confirma que desea validar la cuenta " + clabe + " del " + $(this).attr("cbb_nombre") + "?",
                    function(){
                        comunica("' . $this->pantalla . '",
                        {
                            metodo:"validaCuenta",
                            mun_id: municipio,
                            tfn_id: fondo,
                            cbb_id: clabe,
                            ac_id: "1"
                        });
                    },
                    function(){
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".btn-rechazar").click(function() {
                var municipio = $(this).attr("mun_id");
                var fondo = $(this).attr("tfn_id");
                var clabe = $(this).attr("cbb_id");
                alertify.confirm(
                    "<h5>Rechazar cuenta</h5>",
                    "¿Confirma que desea rechazar la cuenta " + clabe + " del " + $(this).attr("cbb_nombre") + "?",
                    function() {
                        comunica("' . $this->pantalla . '",
                        {
                            metodo:"validaCuenta",
                            mun_id: municipio,
                            tfn_id: fondo,
                            cbb_id: clabe,
                            ac_id: "2"
                        });
                    },
                    function(){
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }

    private function validaCuenta()
    {
        $mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $tfn_id = isset($this->prm->tfn_id) ? $this->prm->tfn_id : '';
        $cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';
        $accion = isset($this->prm->ac_id) ? $this->prm->ac_id : '';

        if (empty($accion)) {
            $this->mixScript('
                notify("El parámetro de acción no existe", "error");
            ');
            return;
        }

        $cuenta = new stdClass();
        $cuenta->abc = 'c';
        $cuenta->cbb_id = $cbb_id;

        if ($accion == 1) {

            if (empty($cbb_id)) {
                $this->mixScript('
                    notify("El parámetro de clabe no existe", "error");
                ');
                return;
            }

            $cuenta->eba_id = '6';

            CuentaBancariaBeneficiario::abc($cuenta);

            $this->mixScript('
                notify("Cuenta validada exitosamente", "success");
            ');
        } elseif ($accion == 2) {

            if (empty($cbb_id)) {
                $this->mixScript('
                    notify("El parámetro de clabe no existe", "error");
                ');
                return;
            }

            if (!empty($mun_id) && !empty($tfn_id)) {
                //Quitar vinculación
                $vbm = new stdClass();
                $vbm->abc = 'b';
                $vbm->cbb_id = $cbb_id;
                $vbm->mun_id = $mun_id;
                $vbm->tfn_id = $tfn_id;
                BeneficiarioMunicipio::abc($vbm);
            }

            $cuenta->eba_id = '5';
            $cuenta->observacion = 'CUENTA RECHAZADA DESDE MODULO DE MUNICIPIOS';

            CuentaBancariaBeneficiario::abc($cuenta);

            $this->mixScript('
                notify("Cuenta rechazada exitosamente", "success");
            ');
        } else {
            $this->mixScript('
                notify("Accion incorrecta", "error");
            ');
            return;
        }
        $this->pintaValidaCuenta();
    }

    private function cuentaExiste($clabe)
    {
        $sql = ' SELECT cbb_id
        FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
        WHERE cbb_id = ? ';
        $params = [$clabe];

        $conC = new AccesoDatos();
        $conC->conectar();
        $conC->setSql($sql);
        $conC->consultar($params);

        $numReg = $conC->getNumeroRegistros();

        $conC->desconectar();

        if ($numReg > 0) {
            return true;
        }

        return false;
    }

    // FUNCIONES PARA CONTROVERSIAS (SE MODIFICÓ PARA OTROS TIPOS DE CUENTAS QUE TIENEN ESE MISMO COMPORTAMIENTO)
    //HASTA EL MOMENTO SE MANEJAN CUENTAS DE CONTROVERSIA Y FIDEICOMISO
    private function pintaControversia()
    {
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnAltaCuenta = new stdClass();
        $btnAltaCuenta->id = 'btnAltaCuenta';
        $btnAltaCuenta->name = 'btnAltaCuenta';
        $btnAltaCuenta->text = 'Nueva cuenta';
        $btnAltaCuenta->value = 'Nueva cuenta';
        $btnAltaCuenta->type = 'button';
        $btnAltaCuenta->class = 'me-1';
        $btnAltaCuenta->color = 'azul';
        $btnAltaCuenta->icon = 'solid fa-plus me-2';
        $btnAltaCuenta->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaAltaCuentaControversia",
                origen: "pintaControversia"
            });
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnAltaCuenta));
        }

        //TABLA
        $tabla = new stdClass();
        $tabla->id = 'tablaCuentasControversia';
        $tabla->titulo = 'LISTADO DE CUENTAS ESPECIALES MUNICIPALES';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaControversias';

        //CABECERA DE LA TABLA
        $tr = array();

        $th = new stdClass();
        $th->texto = 'NOMBRE CTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLABE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTATUS';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DESHABILITAR';
        $th->align = 'center';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'cbb_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbb_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbb_cuenta';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'iba_abreviatura';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'eba_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();

        $td->casos = new stdClass();
        $td->casos->campo = 'eba_id';
        $td->casos->valor = '6';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'rojo';
        $td->casos->verdadero->class = 'btn btn-outline-danger btnTooltip btnEliminarC d-grid gap-2 col-4 mx-auto';
        $td->casos->verdadero->icono = 'solid fa-trash-can';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->cbb_id = 'cbb_id';

        if ($this->escritura) {
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        //FILTRO DE MUNICIPIOS
        $inputMunicipio = new stdClass();
        $inputMunicipio->id = 'inputMunicipio';
        $inputMunicipio->name = 'inputMunicipio';
        $inputMunicipio->placeholder = 'MUNICIPIO';
        $inputMunicipio->enDiv = true;
        //$inputMunicipio->label = 'MUNICIPIO';
        if (isset($this->prm->mun_busqueda)) {
            $inputMunicipio->value = $this->prm->mun_busqueda;
        }

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar';
        $btnBuscar->name = 'btnBuscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = 'me-1';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'solid fa-magnifying-glass me-2';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"llenaTablaMunicipios",
                mun_busqueda: $("#inputMunicipio").val()
            })';

        //TABLA MUNICIPIOS
        $tabla2 = new stdClass();
        $tabla2->id = 'tablaMunicipios';
        $tabla2->titulo = 'LISTADO DE MUNICIPIOS';
        $tabla2->comunica = new stdClass();
        $tabla2->comunica->pantalla = $this->pantalla;
        $tabla2->comunica->metodo = 'llenaTablaMunicipios';
        $tabla2->comunica->mun_busqueda = '{$("#inputMunicipio").val()}';
        $tabla2->numFilas = 100;
        $tabla2->filasAmostrar = array(5, 10, 50, 100, 150);

        //CABECERA DE LA TABLA
        $tr = array();

        $th = new stdClass();
        $th->texto = 'MUNICIPIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ASOCIAR CONTROVERSIA';
        $th->align = 'center';
        $th->width = '12rem';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ASOCIAR FIDEICOMISO';
        $th->align = 'center';
        $th->width = '12rem';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REASIGNAR CUENTAS';
        $th->align = 'center';
        $th->width = '12rem';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'mun_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'mun_asociar_controversia';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->id = 'boton';
        $td->casos->verdadero->color = 'azul';
        $td->casos->verdadero->class = 'btnTooltip btnAsociarCon d-grid gap-2 col-3 mx-auto';
        $td->casos->verdadero->icono = 'solid fa-link';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->mun_id = 'mun_id';
        $td->casos->verdadero->attr->mun_nombre = 'mun_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'mun_asociar_fideicomiso';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->id = 'boton';
        $td->casos->verdadero->color = 'azul';
        $td->casos->verdadero->class = 'boton btnTooltip btnAsociarFid d-grid gap-2 col-3 mx-auto';
        $td->casos->verdadero->icono = 'solid fa-link';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->mun_id = 'mun_id';
        $td->casos->verdadero->attr->mun_nombre = 'mun_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'mun_restaurar';
        $td->casos->valor = '1';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->id = 'boton';
        $td->casos->verdadero->color = 'verde';
        $td->casos->verdadero->class = 'boton btnTooltip btnRestaurarC d-grid gap-2 col-4 mx-auto';
        $td->casos->verdadero->icono = 'solid fa-check';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->mun_id = 'mun_id';
        $td->casos->verdadero->attr->mun_nombre = 'mun_nombre';
        array_push($trbody, $td);

        $tabla2->encabezado = array($tr);
        $tabla2->cuerpo = $trbody;

        $html = '
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12 mb-4">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
            <hr>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputMunicipio) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->wrPonTabla($tabla2) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
        $this->llenaTablaControversias();
        $this->llenaTablaMunicipios();

        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            $(".btnTooltip").attr("data-toggle", "tooltip");
            $(".btnTooltip").attr("data-placement", "left");
            $(".btnAsociar").attr("title", "Asociar");
            $("[data-toggle=\"tooltip\"]").tooltip();

            $(".btnAsociar").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaVinculaControversia",
                    mun_id: $(this).attr("mun_id"),
                    mun_nombre: $(this).attr("mun_nombre")
                });
            });
        ');
    }

    private function llenaTablaControversias()
    {
        $sql = ' SELECT cbb_id, cbb_nombre, cbb_cuenta, iba_abreviatura, eba_nombre, cbb.eba_id
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario AS cbb
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING(iba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoCuenta AS eba USING(eba_id)
            WHERE (cbb_nombre LIKE "%controversia%"
            OR cbb_nombre LIKE "%fideicomiso%")
            AND cbb.tbe_id = "MUN"
            AND cbb.eba_id NOT IN (5,7)
        ';

        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaCuentasControversia";
        $nav->nf = -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".btnTooltip").attr("data-toggle", "tooltip");
            $(".btnTooltip").attr("data-placement", "left");
            $(".btnEliminar").attr("title", "Deshabilitar");
            $("[data-toggle=\"tooltip\"]").tooltip();

            $(".btnEliminarC").click(function() {
                var clabe = $(this).attr("cbb_id");
                alertify.confirm(
                    "<h5>Deshabilitar cuenta</h5>",
                    "¿Confirma que desea deshabilitar la cuenta?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo: "deshabilitaControversia",
                            cbb_id: clabe
                        });
                    },
                    function(){
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            const tooltipList = document.querySelectorAll(\'.tooltip\');
            tooltipList.forEach(el => el.remove()); // elimina del DOM
        ');
    }

    private function pintaAltaCuentaControversia()
    {
        $this->prm->origen = isset($this->prm->origen) ? $this->prm->origen : '';

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "' . $this->prm->origen . '"
            });
        ';

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'btnGuardar';
        $btnGuardar->name = 'btnGuardar';
        $btnGuardar->text = 'Guardar';
        $btnGuardar->value = 'Guardar';
        $btnGuardar->type = 'button';
        $btnGuardar->class = 'me-1';
        $btnGuardar->color = 'verde';
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->click = '
            if ($("#inputMunicipio").val() == "") {
                notify("Por favor, ingrese el nombre de la cuenta", "warning");
            } else if ($("#inputAlias").val() == "") {
                notify("Por favor, agregue un alias", "warning");
            } else if ($("#comboInstitucionBancaria").val() == "") {
                notify("Por favor, seleccione un banco", "warning");
            } else if ($("#inputClabe").val() == "") {
                notify("Por favor, ingrese la CLABE interbancaria", "warning");
            } else if ($("#inputClabe").val().length != 18) {
                notify("La CLABE interbancaria debe contener 18 caracteres", "warning");
            } else if ($("#inputCuenta").val() == "") {
                notify("Por favor, ingrese la cuenta bancaria", "warning");
            } else if ($("#inputFechaApertura").val() == "") {
                notify("Por favor, seleccione la fecha de apertura", "warning");
            } else if ($("#comboPlazaBancaria").val() == "") {
                notify("Por favor, seleccione una plaza bancaria", "warning");
            } else {
                alertify.confirm(
                    "<h5>Alta de cuenta</h5>",
                    "¿Confirma que desea dar de alta la cuenta?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo:"altaCuentaControversia",
                            nombre: $("#inputMunicipio").val(),
                            alias: $("#inputAlias").val(),
                            iba_id: $("#comboInstitucionBancaria").val(),
                            cbb_id: $("#inputClabe").val(),
                            cuenta: $("#inputCuenta").val(),
                            fechaApertura: $("#inputFechaApertura").val(),
                            pbn_id: $("#comboPlazaBancaria").val(),
                            sucursal: $("#inputSucursal").val(),
                            origen: "' . $this->prm->origen . '"
                        });
                    },
                    function(){
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            }
        ';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = 'me-1';
        $btnLimpiar->color = 'rojo';
        $btnLimpiar->icon = 'solid fa-trash-can me-2';
        $btnLimpiar->click = '
            $("#inputMunicipio").val("");
            $("#inputAlias").val("");
            $("#inputClabe").val("");
            $("#inputCuenta").val("");
            $("#inputSucursal").val("");
            $("#inputFechaApertura").val("");
            $("#comboInstitucionBancaria").val("");
            $("#comboPlazaBancaria").val("");
            $(".selectpicker").selectpicker("refresh");
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardar));
        }
        $this->agregaTools($this->ponBoton($btnLimpiar));

        $inputMunicipio = new stdClass();
        $inputMunicipio->id = 'inputMunicipio';
        $inputMunicipio->name = 'inputMunicipio';
        $inputMunicipio->enDiv = true;
        $inputMunicipio->label = 'NOMBRE DE LA CUENTA';

        $inputAlias = new StdClass();
        $inputAlias->id = 'inputAlias';
        $inputAlias->name = 'inputAlias';
        $inputAlias->enDiv = true;
        $inputAlias->maxlength = 20;
        $inputAlias->label = 'ALIAS DE LA CUENTA';

        $inputSucursal = new StdClass();
        $inputSucursal->id = 'inputSucursal';
        $inputSucursal->name = 'inputSucursal';
        $inputSucursal->enDiv = true;
        $inputSucursal->formato = 'entero';
        $inputSucursal->maxlength = 10;
        $inputSucursal->label = 'SUCURSAL';
        $inputSucursal->class = '';

        $comboInstitucionBancaria = new stdClass();
        $comboInstitucionBancaria->id = 'comboInstitucionBancaria';
        $comboInstitucionBancaria->name = 'comboInstitucionBancaria';
        $comboInstitucionBancaria->enDiv = true;
        $comboInstitucionBancaria->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboInstitucionBancaria->disabled = true;
        $comboInstitucionBancaria->label = 'BANCO';
        $comboInstitucionBancaria->sql = '
            SELECT iba_id, iba_abreviatura
            FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria
            WHERE sts_id = 1
        ';

        $inputClabe = new StdClass();
        $inputClabe->id = 'inputClabe';
        $inputClabe->name = 'inputClabe';
        $inputClabe->enDiv = true;
        $inputClabe->formato = 'entero';
        $inputClabe->maxlength = 18;
        $inputClabe->label = 'CLABE INTERBANCARIA';
        $inputClabe->class = '';

        $inputCuenta = new StdClass();
        $inputCuenta->id = 'inputCuenta';
        $inputCuenta->name = 'inputCuenta';
        $inputCuenta->enDiv = true;
        $inputCuenta->formato = 'entero';
        $inputCuenta->maxlength = 11;
        $inputCuenta->label = 'CUENTA BANCARIA';
        $inputCuenta->disabled = true;
        $inputCuenta->class = '';

        $inputFechaApertura = new stdClass();
        $inputFechaApertura->id = 'inputFechaApertura';
        $inputFechaApertura->name = 'inputFechaApertura';
        $inputFechaApertura->label = 'FECHA DE APERTURA';
        $inputFechaApertura->enDiv = true;
        $inputFechaApertura->formato = 'fecha';
        $inputFechaApertura->value = date("d/m/Y");

        $comboPlazaBancaria = new StdClass();
        $comboPlazaBancaria->id = 'comboPlazaBancaria';
        $comboPlazaBancaria->name = 'comboPlazaBancaria';
        $comboPlazaBancaria->enDiv = true;
        $comboPlazaBancaria->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboPlazaBancaria->disabled = true;
        $comboPlazaBancaria->label = 'PLAZA BANCARIA';
        $comboPlazaBancaria->sql = 'SELECT pbn_id, pbn_nombre FROM ' . BD_TESORERIA . '.cat_PlazaBancaria WHERE pbn_id>0; ';

        $this->mixHTML('
            <div class="row">
                <legend>ALTA DE CUENTA</legend>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboInstitucionBancaria) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputClabe) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputCuenta) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputSucursal) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboPlazaBancaria) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputFechaApertura) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputMunicipio) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputAlias) . '
                </fieldset>
            </div>
        ');

        if (!empty($this->prm->mun_id)) {
            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo : "rellenaTipoFondo",
                    mun_id : $("#comboMunicipio").val()
                })
            ');
        }

        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
            $(".soloNumeros").keydown(function(evt) {
                var charCode = (evt.which) ? evt.which : event.keyCode
                if ((charCode != 8) && (charCode < 48 || charCode > 57))
                    return false;
                return true;
            });

            $("#comboMunicipio").change(function() {
                $("#inputMunicipio").val($("#comboMunicipio option:selected").text().split("-")[1].trim());
                $("#inputAlias").val($("#comboMunicipio option:selected").text().split("-")[1].trim().substring(13, 33));
            });

            $("#inputMunicipio").keyup(function() {
                    $("#inputAlias").val($("#inputMunicipio").val().trim().substring(0, 20));
            });

            var pcaCI = /^[\d]/;
            $("#inputClabe").keyup(function(e) {
                escribe();
            });
            $("#inputClabe").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return pcaCI.test(String.fromCharCode(keynum));
            });
            $("#inputClabe").blur(function() {
                if ($(this).val().length !=18) {
                    notify("La CLABE no tiene los dígitos necesarios (18)", "error");
                }
                else{
                    comunica("' . $this->pantalla . '",{metodo:"checaCuenta","clabe":$(this).val()});
                }
            });
            $("#inputSucursal").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return pcaCI.test(String.fromCharCode(keynum));
            });
            function escribe() {
                if (document.getElementById("inputClabe").value.length == 3) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"consultaBanco",
                        param: $("#inputClabe").val()
                    });
                }
                if (document.getElementById("inputClabe").value.length == 6) {
                  comunica("' . $this->pantalla . '",{
                        metodo:"consultaPlaza",
                        param: $("#inputClabe").val().substring(3)
                    });
                }
            }
        ');
    }

    private function altaCuentaControversia()
    {

        $cbb = new stdClass();
        $cbb->abc = 'a';
        $cbb->nombre = isset($this->prm->nombre) ? $this->prm->nombre : '';
        $cbb->alias = isset($this->prm->alias) ? $this->prm->alias : '';
        $cbb->iba_id = isset($this->prm->iba_id) ? $this->prm->iba_id : '';
        $cbb->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';
        $cbb->cuenta = isset($this->prm->cuenta) ? $this->prm->cuenta : '';
        $fecha = isset($this->prm->fechaApertura) ? $this->prm->fechaApertura : '';
        $cbb->pbn_id = isset($this->prm->pbn_id) ? $this->prm->pbn_id : '';
        $cbb->sucursal = isset($this->prm->sucursal) ? $this->prm->sucursal : '';

        if (empty($cbb->nombre) || empty($cbb->alias) || empty($cbb->iba_id) || empty($cbb->cbb_id) || empty($cbb->cuenta) || empty($fecha) || strlen($cbb->pbn_id) <= 0 || strlen($cbb->sucursal) <= 0) {
            $this->mixScript('
                notify("Faltan datos", "error");
            ');
            return;
        }

        $fechaApertura = DateTime::createFromFormat('d/m/Y', $fecha);

        $cbb->fechaApertura = $fechaApertura->format('Y-m-d');
        $cbb->anio = 2018;
        $cbb->tbe_id = 'MUN';
        $cbb->eba_id = 4;
        $cbb->cliente = '0';
        $cbb->observacion = 'ALTA DE CUENTA DESDE MODULO DE MUNICIPIOS _CONTROVERSIA';
        $cbb->plazaBancaria = $cbb->pbn_id;

        if ($this->cuentaExiste($cbb->cbb_id)) {
            $this->mixScript('
                notify("La cuenta que esta tratando de ingresar ya existe", "error");
            ');
            return;
        }

        CuentaBancariaBeneficiario::abc($cbb);

        $this->mixScript('
            notify("Se ha insertado la cuenta bancaria ' . $cbb->cuenta . '", "success");
        ');

        $this->pintaControversia();
    }

    private function deshabilitaControversia()
    {
        $this->prm->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';

        if (empty($this->prm->cbb_id)) {
            $this->mixScript('
                notify("No se encontro la clabe de la cuenta a eliminar","error");
            ');
            return;
        }

        $params = [$this->prm->cbb_id];
        $sql = ' SELECT *
            FROM ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio
            LEFT JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario cbb USING (cbb_id)
            WHERE cbb.cbb_anio = ' . APP_EJERCICIO . ' AND cbb_id = ? ';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $numReg = $con->getNumeroRegistros();
        $con->desconectar();

        if ($numReg > 0) {
            $this->mixScript('
                notify("No se puede deshabilitar la cuenta debido a que se encuentra asociada con otros municipios","error");
            ');
            return;
        } elseif ($numReg == 0) {
            $cbb = new stdClass();
            $cbb->abc = 'c';
            $cbb->cbb_id = $this->prm->cbb_id;
            $cbb->eba_id = '7';
            $cbb->observacion = 'CUENTA DE CONTROVERSIA DESHABILITADA DESDE MODULO';

            CuentaBancariaBeneficiario::abc($cbb);
        }

        $this->pintaControversia();
    }

    private function pintaVinculaControversia()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $this->prm->mun_tipo_cta = isset($this->prm->mun_tipo_cta) ? $this->prm->mun_tipo_cta : '';

        if (empty($this->prm->mun_id)) {
            $this->mixScript('
                notify("No se encuentra el id del municipio", "error");
            ');
            return;
        } elseif (empty($this->prm->mun_nombre)) {
            $this->mixScript('
                notify("No se encuentra el nombre del municipio", "error");
            ');
            return;
        } elseif (empty($this->prm->mun_tipo_cta)) {
            $this->mixScript('
                notify("No se encuentra el tipo de cuenta a asociar", "error");
            ');
            return;
        }

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaControversia",
                mun_busqueda: "' . $this->prm->mun_busqueda . '"
            });
        ';

        $btnVincular = new stdClass();
        $btnVincular->id = 'btnVincular';
        $btnVincular->name = 'btnVincular';
        $btnVincular->text = 'Regresar';
        $btnVincular->value = 'Vincular';
        $btnVincular->type = 'button';
        $btnVincular->class = 'me-1';
        $btnVincular->color = 'verde';
        $btnVincular->icon = 'solid fa-link me-2';
        $btnVincular->click = '
            if ($("#checkRamo").is(":checked") || $("#checkInfra").is(":checked") || $("#checkForta").is(":checked")) {
                if ($("#comboCuenta").val() == "") {
                    notify("Por favor, seleccione una cuenta", "warning");
                } else {
                    alertify.confirm(
                        "<h5>Vincular cuenta</h5>",
                        "¿Confirma que desea vincular la cuenta?",
                        function() {
                            comunica("' . $this->pantalla . '",{
                                metodo: "vinculaControversia",
                                mun_id: "' . $this->prm->mun_id . '",
                                mun_nombre: "' . $this->prm->mun_nombre . '",
                                cbb_id: $("#comboCuenta").val(),
                                ramo: $("#checkRamo").prop("checked"),
                                infra: $("#checkInfra").prop("checked"),
                                forta: $("#checkForta").prop("checked")
                            });
                        },
                        function(){
                        }
                    ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }
            } else{
                notify("Por favor, seleccione un tipo de fondo", "warning");
            }
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnVincular));
        }

        $comboCuenta = new stdClass();
        $comboCuenta->id = 'comboCuenta';
        $comboCuenta->name = 'comboCuenta';
        $comboCuenta->enDiv = true;
        $comboCuenta->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboCuenta->label = 'CUENTA';
        $comboCuenta->sql = ' SELECT cbb_id, cbb_nombre
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
            WHERE cbb_nombre LIKE ?
            AND tbe_id = "MUN"
            AND eba_id = 6
        ';
        $params = ['%' . $this->prm->mun_tipo_cta . '%'];
        $comboCuenta->params = $params;

        $html = '
            <div class="row">
                <legend>CUENTAS PARA EL ' . $this->prm->mun_nombre . '</legend>
            </div>
            <br>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12 mb-2">
                    <label>MARQUE LOS TIPOS DE FONDO CON LOS QUE DESEA ASOCIAR A LA CUENTA</label>
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-xs-4 col-md-4 mb-2">
                    <input type="checkbox" class="form-check-input" id="checkRamo">
                    <label class="form-check-label" for="checkRamo">RAMO 28</label>
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-xs-4 col-md-4 mb-2">
                    <input type="checkbox" class="form-check-input" id="checkInfra">
                    <label class="form-check-label" for="checkInfra">INFRAESTRUCTURA</label>
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-xs-4 col-md-4">
                    <input type="checkbox" class="form-check-input" id="checkForta">
                    <label class="form-check-label" for="checkForta">FORTALECIMIENTO</label>
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponCombo($comboCuenta) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
        ');
    }

    private function vinculaControversia()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $ramo = isset($this->prm->ramo) ? $this->prm->ramo : false;
        $infraestructura = isset($this->prm->infra) ? $this->prm->infra : false;
        $fortalecimiento = isset($this->prm->forta) ? $this->prm->forta : false;
        $params = [];
        $sig = [];

        if (empty($this->prm->mun_id)) {
            $this->mixScript('
                notify("No se encuentra el id del municipio", "error");
            ');
            return;
        } elseif (empty($this->prm->mun_nombre)) {
            $this->mixScript('
                notify("No se encuentra el nombre del municipio", "error");
            ');
            return;
        } elseif (empty($this->prm->cbb_id)) {
            $this->mixScript('
                notify("No se encuentra la clabe interbancaria", "error");
            ');
            return;
        }

        $tfn = array();

        if ($ramo) {
            array_push($params, 2);
            array_push($sig, '?');
        }

        if ($infraestructura) {
            array_push($params, 3);
            array_push($sig, '?');
        }

        if ($fortalecimiento) {
            array_push($params, 4);
            array_push($sig, '?');
        }

        $tipoFondo = implode(',', $sig);

        array_unshift($params, $this->prm->mun_id);

        $sql = ' SELECT mun_id, tfn_id, cbb_id
            FROM ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio AS vbm
            WHERE mun_id = ?
            AND tfn_id IN (' . $tipoFondo . ')';

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);

        while ($fila = $con->extraer()) {

            $sql2 = ' SELECT cbb_id, cbb_nombre
                FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
                WHERE cbb_nombre LIKE "%controversia%"
                OR cbb_nombre LIKE "%fideicomiso%"
                AND cbb_id = ? ';
            $params = [$this->prm->cbb_id];

            $con2 = new AccesoDatos();
            $con2->conectar();
            $con2->setSql($sql2);
            $con2->consultar($params);
            $numReg2 = $con2->getNumeroRegistros();

            $con2->desconectar();

            if ($numReg2 == 0) {
                $cbb = new stdClass();
                $cbb->abc = 'c';
                $cbb->cbb_id = $fila['cbb_id'];
                $cbb->eba_id = '7';
                CuentaBancariaBeneficiario::abc($cbb);
            }

            $vbm = new stdClass();
            $vbm->abc = 'b';
            $vbm->mun_id = $this->prm->mun_id;
            $vbm->tfn_id = $fila['tfn_id'];
            $vbm->cbb_id = $fila['cbb_id'];

            BeneficiarioMunicipio::abc($vbm);
        }

        $vbm = new stdClass();
        $vbm->abc = 'a';
        $vbm->mun_id = $this->prm->mun_id;
        $vbm->cbb_id = $this->prm->cbb_id;

        if ($ramo) {
            $vbm->tfn_id = '2';
            BeneficiarioMunicipio::abc($vbm);
        }

        if ($infraestructura) {
            $vbm->tfn_id = '3';
            BeneficiarioMunicipio::abc($vbm);
        }

        if ($fortalecimiento) {
            $vbm->tfn_id = '4';
            BeneficiarioMunicipio::abc($vbm);
        }

        $this->pintaControversia();
    }

    // FUNCIONES PARA REHABILITAR CUENTAS EXISTENTES ASOCIADAS A CUENTAS ESPECIALES
    private function pintaRehabilitaCuenta()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->mun_nombre = isset($this->prm->mun_nombre) ? $this->prm->mun_nombre : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $this->prm->controversia = isset($this->prm->controversia) ? $this->prm->controversia : false;

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaControversia",
                mun_busqueda: "' . $this->prm->mun_busqueda . '"
            });
        ';

        $btnRehabilita = new stdClass();
        $btnRehabilita->id = 'btnRehabilita';
        $btnRehabilita->name = 'btnRehabilita';
        $btnRehabilita->text = 'Habilitar cuenta';
        $btnRehabilita->value = 'Habilitar cuenta';
        $btnRehabilita->type = 'button';
        $btnRehabilita->color = 'verde';
        $btnRehabilita->class = 'me-1';
        $btnRehabilita->icon = 'solid fa-plus me-2';
        $btnRehabilita->click = '
            if ($("#comboRamo").val() != "" || $("#comboForta").val() != "" || $("#comboInfra").val() != "") {
                alertify.confirm(
                    "<h5>Habilitar cuentas</h5>",
                    "¿Confirma que desea habilitar las cuentas?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo: "rehabilitaCuenta",
                            mun_id: "' . $this->prm->mun_id . '",
                            cbb_ramo: $("#comboRamo").val(),
                            cbb_forta: $("#comboForta").val(),
                            cbb_infra: $("#comboInfra").val(),
                            mun_busqueda: "' . $this->prm->mun_busqueda . '"
                        });
                    },
                    function(){
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            } else {
                notify("Seleccione al menos una cuenta", "warning");
            }
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnRehabilita));
        }

        if (empty($this->prm->mun_id)) {
            $this->mixScript('
                notify("Falta la clave del municipio", "error");
            ');
            return;
        }

        $mun_sbr = substr($this->prm->mun_nombre, 13);

        $sqlCtas = 'SELECT cbb_id, cbb_nombre
            FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario
            WHERE tbe_id = "MUN"
            AND eba_id <> 5 AND cbb_anio = ' . APP_EJERCICIO . '
            AND cbb_nombre LIKE ?
        ';
        $params = ['%' . $mun_sbr . '%'];

        $comboRamo = new stdClass();
        $comboRamo->id = 'comboRamo';
        $comboRamo->name = 'comboRamo';
        $comboRamo->enDiv = true;
        $comboRamo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboRamo->label = 'RAMO 28';
        $comboRamo->sql = $sqlCtas;
        $comboRamo->params = $params;
        $comboRamo->change = '
            if ($("#comboRamo").val() != "") {
                if ($("#comboForta").val() != "" && $("#comboRamo").val() == $("#comboForta").val()) {
                    notify("La cuenta de Ramo 28 no puede ser la misma que la de Fortalecimiento", "error");
                    $("#comboRamo").val("");
                    $(".selectpicker").selectpicker("refresh");
                } else if ($("#comboInfra").val() != "" && $("#comboRamo").val() == $("#comboInfra").val()) {
                    notify("La cuenta de Ramo 28 no puede ser la misma que la de Infraestructura", "error");
                    $("#comboRamo").val("");
                    $(".selectpicker").selectpicker("refresh");
                }
            }
        ';

        $comboForta = new stdClass();
        $comboForta->id = 'comboForta';
        $comboForta->name = 'comboForta';
        $comboForta->enDiv = true;
        $comboForta->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboForta->label = 'FORTALECIMIENTO';
        $comboForta->sql = $sqlCtas;
        $comboForta->params = $params;
        $comboForta->change = '
            if ($("#comboForta").val() != "") {
                if ($("#comboRamo").val() != "" && $("#comboForta").val() == $("#comboRamo").val()) {
                    notify("La cuenta de Fortalecimiento no puede ser la misma que la de Ramo 28", "error");
                    $("#comboForta").val("");
                    $(".selectpicker").selectpicker("refresh");
                } else if ($("#comboInfra").val() != "" && $("#comboForta").val() == $("#comboInfra").val()) {
                    notify("La cuenta de Fortalecimiento no puede ser la misma que la de Infraestructura", "error");
                    $("#comboForta").val("");
                    $(".selectpicker").selectpicker("refresh");
                }
            }
        ';

        $comboInfra = new stdClass();
        $comboInfra->id = 'comboInfra';
        $comboInfra->name = 'comboInfra';
        $comboInfra->enDiv = true;
        $comboInfra->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboInfra->label = 'INFRAESTRUCTURA';
        $comboInfra->sql = $sqlCtas;
        $comboInfra->params = $params;
        $comboInfra->change = '
            if ($("#comboInfra").val() != "") {
                if ($("#comboRamo").val() != "" && $("#comboRamo").val() == $("#comboInfra").val()) {
                    notify("La cuenta de Infraestructura no puede ser la misma que la de Ramo 28", "error");
                    $("#comboInfra").val("");
                    $(".selectpicker").selectpicker("refresh");
                } else if ($("#comboForta").val() != "" && $("#comboForta").val() == $("#comboInfra").val()) {
                    notify("La cuenta de Infraestructura no puede ser la misma que la de Fortalecimiento", "error");
                    $("#comboInfra").val("");
                    $(".selectpicker").selectpicker("refresh");
                }
            }
        ';

        $html = '
            <div class="row">
                <legend>CUENTAS PARA ' . $this->prm->mun_nombre . '</legend>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponCombo($comboRamo) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponCombo($comboForta) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponCombo($comboInfra) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarindex").removeClass("btn-default");
        ');
    }

    private function rehabilitaCuenta()
    {
        $this->prm->mun_id = isset($this->prm->mun_id) ? $this->prm->mun_id : '';
        $this->prm->cbb_ramo = isset($this->prm->cbb_ramo) ? $this->prm->cbb_ramo : '';
        $this->prm->cbb_forta = isset($this->prm->cbb_forta) ? $this->prm->cbb_forta : '';
        $this->prm->cbb_infra = isset($this->prm->cbb_infra) ? $this->prm->cbb_infra : '';
        $this->prm->mun_busqueda = isset($this->prm->mun_busqueda) ? $this->prm->mun_busqueda : '';
        $mensaje = '';

        if (empty($this->prm->mun_id)) {
            $this->mixScript('
                notify("Identificador de municipio no encontrado", "error");
            ');
        }

        if (empty($this->prm->cbb_ramo) && empty($this->prm->cbb_forta) && empty($this->prm->cbb_infra)) {
            $this->mixScript('
                notify("Ningún tipo de fondo encontrado", "error");
            ');
        }

        $paramsTipoFondo = [];
        $sig = [];

        if (!empty($this->prm->cbb_ramo)) {
            array_push($paramsTipoFondo, '2'); //Identificador del tipo de fondo tfn_id para Ramo 28
            array_push($sig, '?');
        }

        if (!empty($this->prm->cbb_infra)) {
            array_push($paramsTipoFondo, '3'); //Identificador del tipo de fondo tfn_id para Infraestructura
            array_push($sig, '?');
        }

        if (!empty($this->prm->cbb_forta)) {
            array_push($paramsTipoFondo, '4'); //Identificador del tipo de fondo tfn_id para Fortalecimiento
            array_push($sig, '?');
        }
        array_unshift($paramsTipoFondo, $this->prm->mun_id);

        $sql = ' SELECT cbb_id, mun_id, tfn_id
            FROM ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio
            WHERE mun_id = ?
            AND tfn_id IN (' . implode(',', $sig) . ')';

        $conR = new AccesoDatos();
        $conR->conectar();
        $conR->setSql($sql);
        $conR->consultar($paramsTipoFondo);

        while ($fila = $conR->extraer()) {
            $vbm = new stdClass();
            $vbm->abc = 'b';
            $vbm->mun_id = $this->prm->mun_id;
            $vbm->tfn_id = $fila['tfn_id'];
            $vbm->cbb_id = $fila['cbb_id'];

            BeneficiarioMunicipio::abc($vbm);
        }
        if ($this->prm->cbb_ramo == $this->prm->cbb_forta) {
            $mensaje =
                'notify("La cuenta de Ramo 28 no puede ser la misma que la de Fortalecimiento", "error");
                $("#comboRamo").val("");
                $(".selectpicker").selectpicker("refresh");';
        } else if ($this->prm->cbb_ramo == $this->prm->cbb_infra) {
            $mensaje = 'notify("La cuenta de Ramo 28 no puede ser la misma que la de Infraestructura", "error");
                    $("#comboRamo").val("");
                    $(".selectpicker").selectpicker("refresh");';
        }
        if (empty($mensaje)) {
            //CUENTA DE RAMO 28
            if (!empty($this->prm->cbb_ramo)) {
                $vbm = new stdClass();
                $vbm->abc = 'a';
                $vbm->mun_id = $this->prm->mun_id;
                $vbm->cbb_id = $this->prm->cbb_ramo;
                $vbm->tfn_id = '2';
                BeneficiarioMunicipio::abc($vbm);

                $cbb = new stdClass();
                $cbb->abc = 'c';
                $cbb->cbb_id = $vbm->cbb_id;
                $cbb->eba_id = 6;
                $cbb->observacion = 'CUENTA REHABILITADA DESDE MÓDULO DE MUNICIPIOS';
                CuentaBancariaBeneficiario::abc($cbb);
            }

            //CUENTA DE INFRAESTRCUTURA
            if (!empty($this->prm->cbb_infra)) {
                $vbm = new stdClass();
                $vbm->abc = 'a';
                $vbm->mun_id = $this->prm->mun_id;
                $vbm->cbb_id = $this->prm->cbb_infra;
                $vbm->tfn_id = '3';
                BeneficiarioMunicipio::abc($vbm);

                $cbb = new stdClass();
                $cbb->abc = 'c';
                $cbb->cbb_id = $vbm->cbb_id;
                $cbb->eba_id = 6;
                $cbb->observacion = 'CUENTA REHABILITADA DESDE MÓDULO DE MUNICIPIOS';
                CuentaBancariaBeneficiario::abc($cbb);
            }

            //CUENTA DE FORTALECIMIENTO
            if (!empty($this->prm->cbb_forta)) {
                $vbm = new stdClass();
                $vbm->abc = 'a';
                $vbm->mun_id = $this->prm->mun_id;
                $vbm->cbb_id = $this->prm->cbb_forta;
                $vbm->tfn_id = '4';
                BeneficiarioMunicipio::abc($vbm);

                $cbb = new stdClass();
                $cbb->abc = 'c';
                $cbb->cbb_id = $vbm->cbb_id;
                $cbb->eba_id = 6;
                $cbb->observacion = 'CUENTA REHABILITADA DESDE MÓDULO DE MUNICIPIOS';
                CuentaBancariaBeneficiario::abc($cbb);
            }
            $this->pintaControversia();
        } else {
            $this->mixScript($mensaje);
        }
    }

    // FUNCIONES PARA EDICIÓN DE CUENTAS RECHAZADAS
    private function pintaCuentasRechazadas()
    {
        //Declaración y llamado de la tabla de valores
        $tabla = new stdClass();
        $tabla->id = 'tablaCuentasRechazadas';
        $tabla->titulo = 'Cuentas Bancarias Rechazadas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaCuentasRechazadas';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);

        //Declaración del cabecero de la tabla
        $tr = array();

        $th = new stdClass();
        $th->texto = 'CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLABE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE DE LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ALIAS DE LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'INSTITUCIÓN BANCARIA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NO PLAZA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE PLAZA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA AP.';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EDITAR';
        $th->align = 'center';
        array_push($tr, $th);

        // CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'CTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbb_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NOMBRECTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ALIASCTA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'INSTBAN';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NOPZA';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'PZANOMBRE';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'FECHAAP';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ESTATUS';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->class = 'btnEditar';
        $td->icono = 'solid fa-pencil';
        $td->title = 'Editar';
        $td->attr = new stdClass();
        $td->attr->cbb_id = 'cbb_id';
        array_push($trbody, $td);

        //Paso de parámetros a la tabla
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row mt-4 mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';
        $this->mixHtml($html);
        //llamado a la tabla
        $this->llenaTablaCuentasRechazadas();
    }

    private function llenaTablaCuentasRechazadas()
    {
        $sql = '
            SELECT
                cbb_alias AS ALIASCTA,
                cbb_nombre AS NOMBRECTA,
                cbb_cuenta AS CTA,
                iba_abreviatura AS INSTBAN,
                pbn_numero AS NOPZA,
                pbn_nombre AS PZANOMBRE,
                cbb_fechaApertura AS FECHAAP,
                eba_nombre AS ESTATUS,
                cbb_id,
                eba_id
            FROM
                ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario AS cbb
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS cib USING (iba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_PlazaBancaria AS cpb USING (pbn_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoCuenta AS cec USING (eba_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoBeneficiario AS ctb USING (tbe_id)
            WHERE tbe_id = "MUN"
            AND sts_id = 1
            AND eba_id = 5
            AND cbb.cbb_anio = ' . APP_EJERCICIO . '
        ';

        $this->setSql($sql);
        $this->setParams(null);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tablaCuentasRechazadas";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".btnEditar").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaEditaCuenta",
                    cbb_id: $(this).attr("cbb_id")
                });
            });
        ');
    }

    private function pintaEditaCuenta()
    {
        $this->prm->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';

        if (empty($this->prm->cbb_id)) {
            $this->mixScript('
                notify("Clabe no especificada", "error");
            ');
            return;
        }

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaMunicipios"
            });
        ';

        $btnGuardar = new stdClass();
        $btnGuardar->id = 'btnGuardar';
        $btnGuardar->name = 'btnGuardar';
        $btnGuardar->text = 'Guardar';
        $btnGuardar->value = 'Guardar';
        $btnGuardar->type = 'button';
        $btnGuardar->class = 'me-1';
        $btnGuardar->color = 'verde';
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->click = '
            if ($("#inputMunicipio").val() == "") {
                notify("Por favor, ingrese el nombre de la cuenta", "warning");
            } else if ($("#inputAlias").val() == "") {
                notify("Por favor, agregue un alias", "warning");
            } else if ($("#comboInstitucionBancaria").val() == "") {
                notify("Por favor, seleccione un banco", "warning");
            } else if ($("#inputClabe").val() == "") {
                notify("Por favor, ingrese la CLABE interbancaria", "warning");
            } else if ($("#inputClabe").val().length != 18) {
                notify("La CLABE interbancaria debe contener 18 caracteres", "warning");
            } else if ($("#inputCuenta").val() == "") {
                notify("Por favor, ingrese la cuenta bancaria", "warning");
            } else if ($("#inputFechaApertura").val() == "") {
                notify("Por favor, seleccione la fecha de apertura", "warning");
            } else if ($("#comboPlazaBancaria").val() == "") {
                notify("Por favor, seleccione una plaza bancaria", "warning");
            } else {
                alertify.confirm(
                    "<h5>Edición de cuenta</h5>",
                    "¿Confirma que desea modificar la cuenta?",
                    function() {
                        comunica("' . $this->pantalla . '",{
                            metodo:"editaCuenta",
                            nombre: $("#inputMunicipio").val(),
                            alias: $("#inputAlias").val(),
                            iba_id: $("#comboInstitucionBancaria").val(),
                            cbb_id: $("#inputClabe").val(),
                            fechaApertura: $("#inputFechaApertura").val(),
                            pbn_id: $("#comboPlazaBancaria").val(),
                            sucursal: $("#inputSucursal").val()
                        });
                    },
                    function(){
                    }
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            }
        ';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = 'me-1';
        $btnLimpiar->color = 'rojo';
        $btnLimpiar->icon = 'solid fa-trash-can me-2';
        $btnLimpiar->click = '
            $("#inputMunicipio").val("");
            $("#inputAlias").val("");
            $("#inputClabe").val("");
            $("#inputCuenta").val("");
            $("#inputSucursal").val("");
            $("#inputFechaApertura").val("");
            $("#comboInstitucionBancaria").val("");
            $("#comboPlazaBancaria").val("");
            $(".selectpicker").selectpicker("refresh");
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardar));
        }
        $this->agregaTools($this->ponBoton($btnLimpiar));

        $inputMunicipio = new stdClass();
        $inputMunicipio->id = 'inputMunicipio';
        $inputMunicipio->name = 'inputMunicipio';
        $inputMunicipio->enDiv = true;
        $inputMunicipio->label = 'NOMBRE DE LA CUENTA';

        $inputAlias = new StdClass();
        $inputAlias->id = 'inputAlias';
        $inputAlias->name = 'inputAlias';
        $inputAlias->enDiv = true;
        $inputAlias->maxlength = 20;
        $inputAlias->label = 'ALIAS DE LA CUENTA';

        $inputSucursal = new StdClass();
        $inputSucursal->id = 'inputSucursal';
        $inputSucursal->name = 'inputSucursal';
        $inputSucursal->enDiv = true;
        $inputSucursal->formato = 'entero';
        $inputSucursal->maxlength = 10;
        $inputSucursal->label = 'SUCURSAL';
        $inputSucursal->class = '';

        $comboInstitucionBancaria = new stdClass();
        $comboInstitucionBancaria->id = 'comboInstitucionBancaria';
        $comboInstitucionBancaria->name = 'comboInstitucionBancaria';
        $comboInstitucionBancaria->enDiv = true;
        $comboInstitucionBancaria->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboInstitucionBancaria->label = 'BANCO';
        $comboInstitucionBancaria->disabled = true;
        $comboInstitucionBancaria->sql = '
            SELECT iba_id, iba_abreviatura
            FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria
            WHERE sts_id = 1
        ';

        $inputClabe = new StdClass();
        $inputClabe->id = 'inputClabe';
        $inputClabe->name = 'inputClabe';
        $inputClabe->enDiv = true;
        $inputClabe->formato = 'entero';
        $inputClabe->maxlength = 18;
        $inputClabe->label = 'CLABE INTERBANCARIA';
        $inputClabe->disabled = true;
        $inputClabe->class = 'soloNumeros';

        $inputCuenta = new StdClass();
        $inputCuenta->id = 'inputCuenta';
        $inputCuenta->name = 'inputCuenta';
        $inputCuenta->enDiv = true;
        $inputCuenta->formato = 'entero';
        $inputCuenta->maxlength = 11;
        $inputCuenta->label = 'CUENTA BANCARIA';
        $inputCuenta->disabled = true;
        $inputCuenta->class = 'soloNumeros';

        $inputFechaApertura = new stdClass();
        $inputFechaApertura->id = 'inputFechaApertura';
        $inputFechaApertura->name = 'inputFechaApertura';
        $inputFechaApertura->label = 'FECHA DE APERTURA';
        $inputFechaApertura->enDiv = true;
        $inputFechaApertura->formato = 'fecha';
        $inputFechaApertura->value = date("d/m/Y");
        $inputFechaApertura->disabled = true;

        $comboPlazaBancaria = new StdClass();
        $comboPlazaBancaria->id = 'comboPlazaBancaria';
        $comboPlazaBancaria->name = 'comboPlazaBancaria';
        $comboPlazaBancaria->enDiv = true;
        $comboPlazaBancaria->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboPlazaBancaria->label = 'PLAZA BANCARIA';
        $comboPlazaBancaria->disabled = true;
        $comboPlazaBancaria->sql = ' SELECT pbn_id, pbn_nombre
            FROM ' . BD_TESORERIA . '.cat_PlazaBancaria;
        ';

        $sql = 'SELECT * FROM ' . BD_TESORERIA . '.ban_CuentaBancariaBeneficiario WHERE cbb_id = ? ';
        $params = [$this->prm->cbb_id];
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);

        $institucionBancaria = '';
        $plazaBancaria = '';

        if ($fila = $con->extraer()) {
            $inputMunicipio->value = $fila['cbb_nombre'];
            $inputAlias->value = $fila['cbb_alias'];
            $inputSucursal->value = $fila['cbb_sucursal'];
            $institucionBancaria = $fila['iba_id'];
            $inputClabe->value = $fila['cbb_id'];
            $inputCuenta->value = $fila['cbb_cuenta'];
            $inputFechaApertura->value = $fila['cbb_fechaApertura'];
            $plazaBancaria = $fila['pbn_id'];
        }

        $this->mixHTML('
            <div class="row">
                <legend>ALTA DE CUENTA</legend>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboInstitucionBancaria) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputClabe) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputCuenta) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputSucursal) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponCombo($comboPlazaBancaria) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputFechaApertura) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputMunicipio) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputAlias) . '
                </fieldset>
            </div>
        ');

        if (!empty($this->prm->mun_id)) {
            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo : "rellenaTipoFondo",
                    mun_id : $("#comboMunicipio").val()
                })
            ');
        }

        $this->mixScript('
            var pcaCI = /^[\d]/;

            $(".soloNumeros").keydown(function(evt) {
                var charCode = (evt.which) ? evt.which : event.keyCode
                if ((charCode != 8) && (charCode < 48 || charCode > 57))
                    return false;
                return true;
            });
            $("#inputSucursal").keypress(function(e) {
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 32) || (keynum == 9) || (keynum == 127) || (keynum == 0))
                    return true;

                return pcaCI.test(String.fromCharCode(keynum));
            });

            $("#comboInstitucionBancaria").val("' . $institucionBancaria . '");
            $("#comboPlazaBancaria").val("' . $plazaBancaria . '");
            $(".selectpicker").selectpicker("refresh");

            $("#comboMunicipio").change(function() {
                $("#inputMunicipio").val($("#comboMunicipio option:selected").text().split("-")[1].trim());
                $("#inputAlias").val($("#comboMunicipio option:selected").text().split("-")[1].trim().substring(13, 33));
            });

            $("#inputClabe").keyup(function() {
                if ($("#inputClabe").val().length == 18) {
                    $("#inputCuenta").val($("#inputClabe").val().substring(6, 17));
                }
            });

            $("#inputMunicipio").keyup(function() {
                $("#inputAlias").val($("#inputMunicipio").val().trim().substring(0, 20));
            });
        ');
    }

    private function editaCuenta()
    {
        $cbb = new stdClass();
        $cbb->abc = 'c';
        $cbb->nombre = isset($this->prm->nombre) ? $this->prm->nombre : '';
        $cbb->alias = isset($this->prm->alias) ? $this->prm->alias : '';
        $cbb->iba_id = isset($this->prm->iba_id) ? $this->prm->iba_id : '';
        $cbb->cbb_id = isset($this->prm->cbb_id) ? $this->prm->cbb_id : '';
        $cbb->pbn_id = isset($this->prm->pbn_id) ? $this->prm->pbn_id : '';
        $cbb->sucursal = isset($this->prm->sucursal) ? $this->prm->sucursal : '';

        if (empty($cbb->nombre) || empty($cbb->alias) || empty($cbb->iba_id) || empty($cbb->cbb_id) || strlen($cbb->pbn_id) <= 0 || strlen($cbb->sucursal) <= 0) {
            $this->mixScript('
                notify("Faltan datos", "error");
            ');
            return;
        }
        $cbb->eba_id = 4;
        $cbb->observacion = 'EDICIÓN DE CUENTA MEDIANTE EL MÓDULO DE MUNICIPIOS';

        CuentaBancariaBeneficiario::abc($cbb);

        $this->mixScript('
            notify("Se ha modificado la clabe bancaria ' . $cbb->cbb_id . '", "success");
        ');
        $this->pintaMunicipios();
    }

}
if (isset($_SESSION[_S]['prm']['T']['6']['2']['1'])) {
    new ControladorVinBeneficiarioMunicipio();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
