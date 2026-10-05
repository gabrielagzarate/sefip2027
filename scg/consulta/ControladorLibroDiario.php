<?php

/*
  Libro Diario
 */
declare (strict_types=1);
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../lib/ComboUsuarios.php';
require_once dirname(__FILE__) . '/../../lib/ComboOrdenInfo.php';
require_once dirname(__FILE__) . '/../../lib/ComboAgrupacionPolizaGlobal.php';
require_once dirname(__FILE__) . '/../../lib/ComboUnidadesEjecutoras.php';
require_once dirname(__FILE__) . '/../../lib/mysql_escape_mimic.php';
require_once dirname(__FILE__) . '/../../lib/funciones.php';

class ControladorLibroDiario extends WebResponse
{
    private $lectura;
    private $escritura;
    private $gpo_id;
    private $ur_id;
    //Constructor
    public function __construct()
    {
        // Determinamos si el usuario actual tiene permisos de lectura / escritura en la base de datos
        // $_SESSION[_S]['prm'][SISTEMA][MENU][OPCION][PERMISO]
        $this->setPermisos();
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        //mandar a llamar constructor base
        parent::__construct();

        switch ($this->metodo) {
            case 'dibujaPantalla':
                $this->dibujaPantalla();
                break;
            case 'cancelar':
                $this->cancelar();
                break;
            case 'recalc':
                $this->recalc();
                break;
            case 'refrescaLinea':
                $this->refrescaLinea();
                break;
            case 'modDescrOrden':
                $this->modDescrOrden();
                break;
            case 'verHistorial':
                $this->verHistorial();
                break;
            case 'llenaHistorial':
                $this->llenaHistorial();
                break;
            case 'llenaTablaLibroDiario':
                $this->llenaTablaLibroDiario();
                break;
            default:
                // $this->ponBarraBotones();
                $this->main();
                break;
        }

        $this->returnJson();
    }

    private function setPermisos(): void
    {
        $this->lectura = !empty($_SESSION[_S]['prm']['C']['4']['1']['1']) ? 1 : 0;
        $this->escritura = !empty($_SESSION[_S]['prm']['C']['4']['1']['2']) ? 1 : 0;
    }
    public function main()
    {
        $this->setPermisos();
        $html = $this->getTablaLibroDiario();
        $this->ponBarraBotones();
        $this->setContenedor('contenedor');
        $es116 = $this->gpo_id . $this->ur_id == 116;
        $clc = ((isset($this->prm->clc) && $this->prm->clc != '') ? $this->prm->clc : '');
        $mesActual = ((isset($this->prm->mesActual) && $this->prm->mesActual != '') ? $this->prm->mesActual : '0');
        //Nombre del Mes
        $arrMeses = ['Apertura' => '0','Enero' => '1', 'Febrero' => '2', 'Marzo' => '3', 'Abril' => '4', 'Mayo' => '5', 'Junio' => '6', 'Julio' => '7', 'Agosto' => '8', 'Septiembre' => '9', 'Octubre' => '10', 'Noviembre' => '11', 'Diciembre' => '12', 'Cierre' => '13'];
        $nombreMes = array_search($mesActual, $arrMeses, true);


        $this->mixHtml(DibujaPantallaBuscarCgClaveFinaciamientoHTML(false, $es116));
        $this->mixScript(DibujaPantallaBuscarCgClaveFinaciamientoJS());
        $manual = ($this->prm->manual ?? null) === 'si';

        if ($clc != '' || $manual) {

            // Valores dinámicos
            $t = $clc != ''
                ? '$("#tipoActual").attr("ta")'
                : '"' . $this->prm->t . '"';

            $n = $clc != ''
                ? '$("#numPoliza").val()'
                : '"' . $this->prm->n . '"';

            $this->mixScript('
                $(document).ready(function() {
                    $("#mesActual")
                        .attr("ma", "' . $mesActual . '")
                        .attr("edomes", "C")
                        .html("' . $nombreMes . '" + "<span class=\"caret\"></span>");
                });

                comunica("' . $this->pantalla . '", {
                    metodo: "llenaTablaLibroDiario",
                    msc_id: $("#msc_id").find("option:selected").val(),
                    m: "' . $mesActual . '",
                    t: ' . $t . ',
                    o: $("#ordenActual").find("option:selected").val(),
                    n: ' . $n . ',
                    v: $("#numVinculacion").val(),
                    nc: $("#numCLC").val(),
                    ue: $("#ueFiltro option:selected").val(),
                    tclc: $("#tclc_id option:selected").val(),
                    u: $("#usr_id option:selected").val(),
                    tb: $("#nombreBuscar").val(),
                    p: $("#buscar").attr("ps"),
                    po: $("#chkOrden").prop("checked"),
                    rfc: $("#rfcFiltro").val(),
                    apg: $("#apg_id option:selected").val(),
                    clc_v: $("#clc_vinculacion").val(),
                    afe_v: $("#afe_vinculacion").val(),
                    cmp_v: $("#cmp_vinculacion").val(),
                    bdo_v: $("#bdo_vinculacion").val(),
                    ref_v: $("#ref_vinculacion").val(),
                    eje_v: $("#ref_ejercicio").val(),
                    clc_o: $("#clc_origen").val(),
                    afe_o: $("#afe_origen").val(),
                    cmp_o: $("#cmp_origen").val(),
                    bdo_o: $("#bdo_origen").val(),
                    vinClc: $("#desdeVinClc").val(),
                    andOr: $("input[name=andOr]:checked").val(),
                    rfc: $("#rfcFiltro").val(),
                    ecf: $("#ClaveFinan").val()
                });
            ');
        }
        $this->mixHtml($html);
    }

    public function ponBarraBotones()
    {
        $this->mixStyle('
            .form-group{margin-bottom:12px!important}
            legend{font-size:18px!important;margin-bottom:10px!important}
            .modal-header{padding:8px!important; background: #b6c2c9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:white; opacity: 0.8;};
        ');

        // Marcos: 21/abr/2016
        // Desde la vinculación por CLC, llamaremos a todas las pólizas que tengan que ver con una CLC
        // determinada, y también buscaremos sus # compromisos para mostrar tambén ésas pólizas
        $clc = ((isset($this->prm->clc) && $this->prm->clc != '') ? $this->prm->clc : '');
        $pantallaVinc = ((isset($this->prm->pantallaVin) && $this->prm->pantallaVin) ? $this->prm->pantallaVin : false);
        $tipo = ((isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '');
        $edo = ((isset($this->prm->edo) && $this->prm->edo != '') ? $this->prm->edo : '');
        $buscarVinculacion = ((isset($this->prm->p) && $this->prm->p != '') ? $this->prm->p : '');
        $cmp = '';

        // Marcos: 24/mayo/2016
        // No mostraremos las pólizas del compromiso, se modificará la consulta
        // ya que está mostrando pólizas de más
        // Que dice mi mamá que siempre no, que sí las mostraremos :D :D :D

        if ($clc != '') {
            $this->conectar();
            $params = [
                getSession('clasAdm-gpo'),
                getSession('clasAdm-ur'),
                $clc,
            ];
            $sql = 'SELECT GROUP_CONCAT( DISTINCT CONCAT( cmp_id ) ORDER BY cmp_id ) AS cmp
                FROM scp_Presupuestal
                WHERE gpo_id = ? AND ur_id = ? AND clc_id = ?;';
            $this->setSql($sql);
            $this->consultar($params);
            $row = $this->extraer();
            if (empty($row['cmp'])) {
                $cmp = '0';
            } else {
                $cmp = $row['cmp'];
            }
        } else {
            $cmp = '';
        }

        $agrupadores = '';
        if ($clc != '') {
            $this->conectar();
            $this->bindParam(':gpo', $_SESSION[_S]['clasAdm-gpo']);
            $this->bindParam(':ur', $_SESSION[_S]['clasAdm-ur']);
            $this->bindParam(':clc', $clc);

            $sql = '
                SELECT GROUP_CONCAT( DISTINCT CONCAT( agrupador ) ORDER BY agrupador ) AS agrupadores
                FROM
                (
                    SELECT bdd_agrupador AS agrupador FROM scp_BancoDocumentoDevengado
                        WHERE gpo_id = :gpo AND ur_id = :ur AND clc_id = :clc
                    UNION ALL
                    SELECT bde_agrupador AS agrupador FROM scp_BancoDocumentoEjercido
                        WHERE gpo_id = :gpo AND ur_id = :ur AND clc_id = :clc
                    UNION ALL
                    SELECT bdm_agrupador AS agrupador FROM tesoreria.min_BancoDocumentoMinistrado_2025
                        WHERE gpo_id = :gpo AND ur_id = :ur AND clc_id = :clc
                    UNION ALL
                    SELECT bdp_agrupador AS agrupador FROM cg_BancoDocumentoPagado
                        WHERE gpo_id = :gpo AND ur_id = :ur AND clc_id = :clc
                ) AS base
            ;';

            $this->setSql($sql);
            $this->consultar();
            $row = $this->extraer();
            $agrupadores = $row['agrupadores'];
        } else {
            $agrupadores = '';
        }

        // Determinamos el status de los meses
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(
            '
            SELECT msc_id, LOWER( msc_nombre ) AS nombre, ems_id
            FROM cat_MesContable
            LEFT OUTER JOIN
                (SELECT * FROM cg_MesContable
                WHERE gpo_id = ? AND ur_id = ? ) AS s
                USING ( msc_id ) ORDER BY msc_id'
        );
        $conexion->consultar([$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']]);
        $conexion->desconectar();

        $liMeses = '';
        $strStatusMes = '';

        while ($row = $conexion->extraer()) {
            // En una variable meteremos el status para ponerlo en el DOM y usarlo desde ahí
            $strStatusMes .= (($row['ems_id'] == 'A') ? '.' : ' ');
            $edomes = strtoupper($row['ems_id'] ? $row['ems_id'] : '');
            // Seguimos con el dibujado de la pagina normal
            $icono = ($row['ems_id'] == 'A' ? '<i class="fa-solid fa-pencil me-1"></i>' : '');
            $nombre = ucfirst($row['nombre']);
            $currmsc = intval($row['msc_id']);

            $liMeses .=
                '<li class="dropdown-item mesContable"  mes="' . $currmsc . '" ems="' . $edomes . '">
                    ' . $icono . '
                    <a href="#" mes="' . $currmsc . '" class="mesContable" ems="' . $edomes . '">' . $nombre . '</a>
                </li>';

            if (intval($row['msc_id']) == 0 or intval($row['msc_id']) == 12) {
                $liMeses .= '<div class="dropdown-divider"></div>';
            }
        }


        $tituloHomeMenu = $clc == '' ? 'Terminar y regresar al menú' : 'Regresar a Vinculación Clc';
        $textoHomeMenu = $clc == '' ? 'Menú' : 'Regresar';
        $htmlHomeMenu = $clc != ''
            ? '<div class="w-100">
                    <button id="homeMenuLD" data-placement="bottom" title="' . $tituloHomeMenu . '" class="btn btn-outline-secondary me-1"><i class="fa-solid fa-circle-left me-2"></i>' . $textoHomeMenu . '</button>
                </div>'
            : '';

        $botones = '
            <div class="row gap-1 row-cols-auto">
                <fieldset class="col" style="padding-right: 1px !important;">
                        ' . $htmlHomeMenu . ' </br>
                    <div class="w-100">
                        <input id="numPoliza" lastSearch="" name="numPoliza" type="text" placeholder="Póliza" maxlength=6 class="form-control" tabindex="10" style="float: left; width:80px">
                    </div>
                </fieldset>
                <fieldset class="col" style="padding-right: 1px !important;">
                    <div class="mb-2" id="ld_mesActual" >
                    <button id="mesActual" ma=0 class="btn btn-outline-secondary dropdown-toggle me-1 mb-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="mes actual">
                        Apertura <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu scrollable-menu" style="height: auto; max-height: 300px; overflow-x: hidden; border: 2px solid #c62e5c" aria-labelledby="mesActual">
                        ' . $liMeses . '
                    </ul>
                    <div id="ld_filtros">
                        <button class="btn btn-outline-secondary dropdown-toggle me-1" data-bs-toggle="dropdown" cat=""><i class="fa-solid fa-filter me-2"></i>Filtros<span class="caret"></span></button>
                        <ul class="dropdown-menu miDivFloat" style="padding-left: 10px; padding-right: 30px; width: 800px; border: 2px solid #c62e5c">
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-5 col-xs-5" >
                                        <legend>Unidad Ejecutora ...</legend>' . ComboUnidadesEjecutoras() .
                                    '</fieldset>
                                    <fieldset class="col-md-5 col-xs-5" >
                                        <legend>Usuario ...</legend>' . ComboUsuarios() .
                                    '</fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-5 col-xs-5" >
                                        <legend>Ordenado por ...</legend>' . ComboOrdenInfo() .
                                    '</fieldset>
                                    <fieldset class="col-md-5 col-xs-5" >
                                        <legend>RFC (devengado)</legend>
                                            <input type="text" name="rfcFiltro"  id="rfcFiltro" maxlength="20" value="" class="form-control F1Catalogo" placeholder="F1: Catalogo" />
                                            <input id="nombreFiltro" type="text" class="form-control" style="display:none;" readonly />
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-5 col-xs-5" >
                                        <legend>Agrupación de Póliza Global</legend>' . ComboAgrupacionPolizaGlobal() .
                                    '</fieldset>
                                    <fieldset class="col-md-5 col-xs-5" >
                                        <legend>Clave de Financiamiento</legend>
                                        <div class="input-group input-group-sm">
                                            <input id="ClaveFinan" lastSearch="" name="ClaveFinan" type="text" placeholder="Clave de Financamiento" maxlength=12 class="form-select F1Catalogo" tabindex="10" disabled>
                                            <span class="input-group-btn">
                                            <button id="mostrarClafinan" type="submit" class="btn btn-outline-secondary me-1"><i class="fa-solid fa-list-ul"></i></button>
                                            </span>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12">
                                <fieldset class="col-md-10 col-xs-10">
                                    <button id="mostrar1"  style="float:right;" data-placement="bottom" title="Buscar y mostrar el catálogo solicitado" class="btn btn-outline-secondary me-1 muestraPlanCuentas ladda-button" data-style="expand-left" cat="" tabindex="30"><i class="fa-solid fa-magnifying-glass me-2"></i>Mostrar</button>
                                </fieldset>
                            </div>
                        </ul>
                    </div>
                </fieldset>
                <fieldset class="col" style="padding-right: 1px !important;">
                    <div id="ld_tipoActual">
                        <button id="tipoActual" class="btn btn-outline-secondary dropdown-toggle me-2 mb-2 w-100"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Tipos de pólizas">
                            Pólizas de Diario<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu scrollable-menu" style="height: auto; max-height: 300px; overflow-x: hidden; border: 2px solid #c62e5c">
                            <li class="dropdown-item"><a href="tp_D" class="tipoDePoliza">Pólizas de Diario</a></li>
                            <li class="dropdown-item"><a href="tp_I" class="tipoDePoliza">Pólizas de Ingresos</a></li>
                            <li class="dropdown-item"><a href="tp_E" class="tipoDePoliza">Pólizas de Egresos</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-item"><a href="mc_A" class="tipoDePoliza">Autorizado</a></li>
                            <li class="dropdown-item"><a href="mc_C" class="tipoDePoliza">Comprometido</a></li>
                            <li class="dropdown-item"><a href="mc_N" class="tipoDePoliza">Provisi&oacuten</a></li>
                            <li class="dropdown-item"><a href="mc_D" class="tipoDePoliza">Devengado</a></li>
                            <li class="dropdown-item"><a href="mc_E" class="tipoDePoliza">Ejercido</a></li>
                            <li class="dropdown-item"><a href="mc_T" class="tipoDePoliza">Pagado - Transferido</a></li>
                            <li class="dropdown-item"><a href="mc_P" class="tipoDePoliza">Pagado - Pagado</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="list.group-item"><a href="mc_O" class="tipoDePoliza">Pagos a Terceros</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-item"><a href="mc_M" class="tipoDePoliza">Modificado</a></li>
                            <li class="dropdown-item"><a href="mc_R" class="tipoDePoliza">Rectificado</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-item"><a href="mc_L" class="tipoDePoliza">Captura Manual</a></li>
                            <li class="dropdown-item"><a href="mc_V" class="tipoDePoliza">Carga Masiva</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-item"><a href="st_C" class="tipoDePoliza">Canceladas</a></li>
                            <li class="dropdown-item"><a href="st_H" class="tipoDePoliza">Cheque Cancelado</a></li>
                            <li class="dropdown-item"><a href="st_X" class="tipoDePoliza">Generadas con Mes Cerrado</a></li>
                            <li class="dropdown-item"><a href="st_P" class="tipoDePoliza">Generadas con Mes sin Abrir</a></li>
                        </ul>
                    </div>
                    <div id="ld_filtroVinculacion">
                        <button class="btn btn-outline-secondary dropdown-toggle w-100" data-bs-toggle="dropdown" cat="">
                            <i class="fa-solid fa-filter me-2"></i>
                            Vinculación<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu miDivFloatFiltro" style="padding-left: 10px; padding-right: 30px; width: 600px; border: 2px solid #c62e5c">
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        <legend title="Vinculación presupuestal que da origen a la póliza (*_vinculacion)">Vinculación Presupuestal</legend>
                                        <input type="text" name="clc_vinculacion" id="clc_vinculacion" maxlength="10" value="' . $clc . '" class="form-control" placeholder="Número de CLC" />
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        <legend title="Cuando se referencía a otra vinculación precedente, por ejemplo en Rectificaciones (*_origen)">Vinculación Precedente</legend>
                                        <input type="text" name="clc_origen"  id="clc_origen" maxlength="10" value="' . $clc . '" class="form-control" placeholder="Número de CLC" />
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="afe_vinculacion" id="afe_vinculacion" maxlength="10" value="" class="form-control" placeholder="Número de Afectación" />
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="afe_origen"  id="afe_origen" maxlength="10" value="" class="form-control" placeholder="Número de Afectación" />
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="cmp_vinculacion" id="cmp_vinculacion" maxlength="10" value="' . $cmp . '" class="form-control" placeholder="Número de Compromiso" />
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="cmp_origen"  id="cmp_origen" maxlength="10" value="' . $cmp . '" class="form-control" placeholder="Número de Compromiso" />
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="bdo_vinculacion" id="bdo_vinculacion" maxlength="10" value="" class="form-control" placeholder="ID del Documento" title="OJO: NO es la columna VINCULACION de la impresión de la póliza.   Eso va en el campo REFERENCIA"/>
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="bdo_origen"  id="bdo_origen" maxlength="10" value="' . $agrupadores . '" class="form-control" placeholder="ID del Documento" title="OJO: NO es la columna VINCULACION de la impresión de la póliza.   Eso va en el campo REFERENCIA"/>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="ref_vinculacion" id="ref_vinculacion" maxlength="50" value="" class="form-control" placeholder="Referencia del Documento" title="Lo que aparece en la columna VINCULACION de la impresión de la póliza"/>
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="text" name="ref_ejercicio" id="ref_ejercicio" maxlength="50" value="" class="form-control" placeholder="Ejercicio" title="Ejercicio al que corresponde la vinculación"/>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="col-md-12 col-xs-12 mb-4">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        <input type="radio" name="andOr" value="0" ' . ($clc == '' ? 'checked' : '') . '> Los campos deben coincidir EXACTAMENTE con TODOS los valores solicitados<br>
                                        <input type="radio" name="andOr" value="1" ' . ($clc == '' ? '' : 'checked') . '> Basta con que un campo coincida para incluirlo en el resultado<br>
                                    </fieldset>
                                    <input type="hidden" name="desdeVinClc" id="desdeVinClc" value="' . ($clc == '' ? '0' : '1') . '">
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        <button id="mostrar3"  style="float:right;" data-placement="bottom" title="Buscar aplicando los filtros solicitados" class="btn btn-outline-secondary me-1 muestraPlanCuentas ladda-button" data-style="expand-left" cat="" tabindex="30"><i class="fa-solid fa-magnifying-glass me-2"></i>Mostrar</button>
                                    </div>
                                </div>
                            </div>
                        </ul>
                    </div>
                    <i id="avisoFiltro" style="display:none" class="fa-solid fa-asterisk"></i>
                </fieldset>
                <fieldset class="col" style="padding-right: 1px !important;">
                    <div>
                        <div class="dropdown">
                            <button id="imprimirReportes" class="btn btn-outline-danger dropdown-toggle me-1 mb-2 w-100"
                                data-bs-toggle="dropdown" title="Imprimir" cat="">
                                <i class="fa-solid fa-file-pdf me-2"></i><span class="caret"></span></button>
                            <ul class="dropdown-menu"  aria-labelledby="imprimirReportes" style="border: 2px solid #c62e5c">
                                <li class="dropdown-item"><a  href="#" id="poliza" class="impDiario">Póliza Actual</a></li>
                                <li class="dropdown-item"><a  href="#" id="seleccion" class="impDiario">Pólizas de la Selección Actual</a></li>
                                <li class="dropdown-item"><a  href="#" id="rango">Rango de Pólizas</a></li>
                                <li class="dropdown-divider"></li>
                                <li class="dropdown-item"><a href="#" id="pdflibroDiario1" class="libroDiario1 impDiario">Libro Diario Selección Actual</a></li>
                                <li class="dropdown-item"><a href="#" id="pdflibroDiario2" class="libroDiario2 impDiario">Libro Diario Todo el Mes</a></li>
                            </ul>
                        </div>
                        <button id="draCorazon" modoPoliza="1" data-placement="bottom"
                            title="Modificar comportamiento al imprimir la póliza" class="btn btn-outline-info me-1 w-100">
                                <i class="fa-solid fa-heart me-2"></i>Normal</button>' . (($clc != '') ? '
                        <button id="tMayor" data-placement="bottom" title="Consultar en Ts de Mayor los movimientos de las pólizas"
                            class="btn btn-outline-info me-1 w-100"><i class="fa-solid fa-heart me-2"></i>Ts de Mayor</button>' : '') . '
                    </div>
                </fieldset>
                <fieldset class="col" style="padding-right: 1px !important;">
                        <button class="btn btn-outline-success dropdown-toggle me-1 mb-2 w-100" data-bs-toggle="dropdown" title="excel" cat="">
                        <i class="fa-solid fa-file-excel me-2"></i><span class="caret"></span></button>
                        <ul class="dropdown-menu" style="border: 2px solid #c62e5c">
                            <li class="dropdown-item"><a href="#" id="xlspoliza" class="impDiario">Póliza Actual</a></li>
                            <li class="dropdown-divider"></li>
                            <li class="dropdown-item"><a href="#" id="xlslibroDiario1" class="libroDiario1 expDiario">Libro Diario Selección Actual</a></li>
                            <li class="dropdown-item"><a href="#" id="xlslibroDiario2" class="libroDiario2 expDiario">Libro Diario Todo el Mes</a></li>
                            <li class="dropdown-item"><a  href="#" id="rangoExcel">Rango de Pólizas</a></li>
                        </ul>
                        <button id="mostrar2" data-placement="bottom" title="Buscar y mostrar el catálogo solicitado" class="w-100 btn btn-outline-secondary me-1 muestraPlanCuentas ladda-button" data-style="slide-left" cat="" tabindex="30"><i class="fa-solid fa-magnifying-glass me-2"></i>Mostrar</button>

                </fieldset>
                <fieldset class="col" style="padding-right: 1px !important;">
                    ' . (($this->escritura) ? '
                    <a id="adicionar" data-placement="bottom" title="Capturar nueva póliza" role="button" class="btn btn-outline-primary me-1" >
                        <i class="fa-solid fa-plus"></i>
                    </a>
                    <a id="modificar" data-placement="bottom" title="Modificar póliza seleccionada" role="button" class="btn btn-outline-secondary me-1" >
                        <i class="fa-solid fa-pencil"></i>
                    </a>
                    <a id="cancelar" data-placement="bottom" title="Cancelar póliza actual" class="btn btn-outline-danger me-1">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                    <a id="recalc" data-placement="bottom" title="Rehacer la suma del debe y haber de la póliza" class="btn btn-outline-primary me-1">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                    ' : '') . '
                </fieldset>
                <fieldset class="col" style="padding-right: 1px !important;">
                    <fieldset class="mb-2">
                            <button id="mostrar" data-placement="bottom" title="Mostrar la póliza seleccionada" class="btn btn-outline-secondary me-1"><i class="fa-solid fa-eye"></i></button>
                            <button id="conceptoVacioOrden" data-placement="bottom" title="Modificar descripción vacía en pólizas de Cuentas de Orden" class="btn btn-outline-secondary me-1"><i class="fa-solid fa-wrench"></i></button>
                            <button id="verHistorial"  type="button" class="btn btn-outline-secondary me-1"  title="Ver historial de la Póliza"><i class="fa-solid fa-list-ul"></i></button>
                        </fieldset>
                        <input name="chkOrden" id="chkOrden" title="Mostrar las pólizas que sólo contengan cuentas de orden" type="checkbox" checked>Orden</input>
                        <input name="chkRevision" id="chkRevision" title="La impresión de la póliza muestra campos adicionales para la revisión" type="checkbox" >p/Revisión</input>
                    </fieldset>
                    <fieldset class="col-md-2 col-xs-2" style="padding-right: 1px !important;">
                        <div class="input-group input-group-sm">
                        <input id="buscar" tp="0" type="text" class="form-control"  placeholder="Filtro ..." >
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass me-2"></i></span>
                    </fieldset>

                </fieldset>
            ';

        $this->setContenedor('toolsPantalla');
        $this->mixScript('$("#toolsPantalla").removeClass("d-flex");');

        $this->mixStyle(' .slick-group-title[level="0"] { display:inline; font-weight:bold; }');
        $this->mixHtml($botones);

        // Dibujamos la pantalla que permite buscar un proveedor
        // --------------------------------------------------------------------------------------------------------------
        $this->mixHtml(DibujaPantallaBuscarProveedorHTML());
        $this->mixScript(DibujaPantallaBuscarProveedorJS());

        $jsEscritura = '';
        if ($this->escritura) {
            $jsEscritura = <<<JS
                // Modificamos la póliza actualmente seleccionada
                $("#modificar").on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');
                    if (!row) {
                        return false;
                    }

                    // Si sólo son afectaciones a cuentas de orden, los mandamos a la visualizacion ... y no a la edición
                    if ( row.apg == "ORD" || row.e == "H" || row.e == "C" || row.em != "A" ) {
                        comunica("scg/poliza/ControladorMuestraPoliza.php",{
                            "m"   : $("#mesActual").attr( "ma" ),
                            "mpa" : row.mes,
                            "p"   : row.pot_id,
                            "r"   : row.prf_id,
                            "n"   : row.numero,
                            "ue"  : row.ue,
                            "mp": $("#draCorazon").attr( "modoPoliza" )
                        });
                    } else {
                        comunica("scg/poliza/ControladorPolizaManual.php",{
                            "m"   : $("#mesActual").attr( "ma" ),
                            "mpa" : row.mes,
                            "p"   : row.pot_id,
                            "r"   : row.prf_id,
                            "n"   : row.numero,
                            "ue"  : row.ue,
                            "ori" : "LibroDiario"
                        });
                    }
                });

                // Cuando la póliza sea de Orden, la descripción esté vacía, el mes esté abierto ...
                $("#conceptoVacioOrden").on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');

                    // Si sólo son afectaciones a cuentas de orden, los mandamos a la visualizacion ... y no a la edición
                    if ( row.apg == "ORD" && row.e !== "H" && row.e !== "C") {
                        alertify.prompt( "Modificar Póliza","En algunos casos se generan pólizas automáticas sin descripción. Sólo para éstos casos, y tratándose de pólizas de Cuentas de Orden, debes utilizar éste botón. LA MODIFICACIÓN DE LA DESCRIPCIÓN DE UNA PÓLIZA ES RESPONSABILIDAD DEL USUARIO, Y EL SISTEMA LLEVA UNA BITÁCORA DETALLADA DE ÉSTOS CAMBIOS PARA FUTURAS ACLARACIONES.Escribe la descripción de la póliza"
                            ,""
                            , function(evt, value) {
                                if (value == ""){
                                    return false;
                                } else {
                                    comunica("scg/consulta/ControladorLibroDiario.php",{
                                        "metodo" : "modDescrOrden",
                                        "m"   : $("#mesActual").attr( "ma" ),
                                        "mpa" : row.mes,
                                        "p"   : row.pot_id,
                                        "r"   : row.prf_id,
                                        "n"   : row.numero,
                                        "ue"  : row.ue,
                                        "do"  : row.c,
                                        "dp"  : descripcion
                                    });
                                }
                            }, undefined).set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                    } else {
                        notify("Sólo para pólizas de Orden, con descripción vacía, y meses abiertos")
                    }
                    e.stopPropagation();
                });

                // Adicionar una póliza desde el libro diario
                $("#adicionar").click(function(e) {
                    comunica("scg/poliza/ControladorPolizaManual.php",{ "altaLD" : "D", "ori": "LibroDiario"});
                    e.preventDefault();
                    e.stopPropagation();
                });

                // Si una póliza tiene el status N ... la cancela
                // Si una póliza tiene el status C ... la vuelve "normal"
                $("#cancelar").click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');
                    // Si no hay ningún documento seleccionado, no hacemos nada
                    if ( !row ) { return false };

                    // Sólo usaremos el status C para volverla Normal ... y N para volverla Cancelada
                    if ( row.e == "C" || row.e == "N" ) {
                        // Si la póliza que quieren cancelar es de otro mes, por el filtro presupuestal
                        // Avisamos del mes al que pertenece
                        strMesPol = "";
                        if ( $("#mesActual").attr("ma") != row.mes ) {
                            strMesPol = "<font color=\"red\"> (" + dameMesCorto( row.mes ) + ")</font>";
                        }

                        // Marcos: 18/oct/2016
                        // Israel solicita aplicar la siguiente regla:
                        // 1. Cuando quieran CANCELAR o REACTIVAR una póliza que NO SEA L o V (captura manual o carga masiva)
                        //    debemos mostrarles un mensaje de advertencia de que no muevan esos tipos de póliza.   El usuario
                        //    sí podrá continuar con el proceso, pero debemos advertirle
                        if ( row.mc.substring( 0, 1 ) != "L" && row.mc.substring( 0, 1 ) != "V" ) {
                            alertify.confirm( "INTENTAS MODIFICAR EL STATUS DE UNA PÓLIZA AUTOMÁTICA",
                                "La póliza tiene el status actual derivado de la aplicación de las reglas emitidas por la <b>Dirección de Contabilidad</b>, a través de los denominados <b>hechos generadores</b>.Se recomienda encarecidamente <b>NO MODIFICAR</b> el estado de éste tipo de pólizas."
                                , function() {
                                    alertify.prompt("Confirmar",
                                        "<h4>Puedes continuar con el proceso bajo tu completa responsabilidad, ya que cambiar manualmente el status afecta los importes de la contabilidad.Escribe <b>ENTERADO</b> para cambiar el status de la póliza " + row.pot_id + "-" + row.prf_id + row.numero + strMesPol + ".</h4>",
                                        "",
                                        function (evt, word) {
                                            if (word === "ENTERADO") {
                                                comunica("scg/consulta/ControladorLibroDiario.php",{
                                                "metodo" : "cancelar",
                                                "m"   : $("#mesActual").attr( "ma" ),
                                                "mpa" : row.mes,
                                                "p"   : row.pot_id,
                                                "r"   : row.prf_id,
                                                "n"   : row.numero,
                                                "ue"  : row.ue
                                                });
                                            } else {
                                                notify( "Escribiste: " + palabra + ".   Debes escribir ENTERADO", "warning" );
                                            }
                                        },
                                        undefined
                                    ).set("labels", {ok: "Proceder", cancel: "Cancelar"});
                                },undefined).set("labels", {ok: "Yo se lo que hago", cancel: "Cancelar"});

                        // Si NO es una póliza automática, seguimos el proceso normal
                        } else {
                            alertify.confirm("CANCELAR / REACTIVAR PÓLIZA",
                                "¿ Confirmas que deseas cambiar el status de la póliza " + row.pot_id + "-" + row.prf_id + row.numero + strMesPol + " ?",
                                function(){
                                    comunica("scg/consulta/ControladorLibroDiario.php",{
                                        "metodo" : "cancelar",
                                        "m"   : $("#mesActual").attr( "ma" ),
                                        "mpa" : row.mes,
                                        "p"   : row.pot_id,
                                        "r"   : row.prf_id,
                                        "n"   : row.numero,
                                        "ue"  : row.ue
                                    });
                                },
                                undefined
                            ).set("labels", {ok: "CAMBIAR STATUS", cancel: "Cancelar Operación"});
                        }

                    // Las de cheque cancelado se pueden habilitar nuevamente, despues de un aviso
                    } else if ( row.e == "H" ) {
                        alertify.prompt(
                            "Activar Póliza",
                            "<h4>La póliza de cheque cancelado NO TIENE validaciones.Puedes tener una póliza sin importes, que no cuadre, etc.Es TU RESPONSABILIDAD verificar que después de activarlasexista una póliza válida en su lugar.Escribe <b>ENTERADO</b> para activar la póliza nuevamente.</h4>",
                            "",
                            function (event, palabra) {
                                if (palabra == "ENTERADO") {
                                    comunica("scg/consulta/ControladorLibroDiario.php",{
                                        "metodo" : "cancelar",
                                        "m" : $("#mesActual").attr( "ma" ),
                                        "p" : row.pot_id,
                                        "r" : row.prf_id,
                                        "n" : row.numero,
                                        "ue": row.ue
                                    });
                                } else {
                                    if (palabra.length > 0 ) {
                                        notify( "Escribiste " + palabra + ".   Debes escribir ENTERADO", "warning" );
                                    }
                                }
                            }, undefined
                        ).set("labels", { ok: 'Proceder', cancel: "Cancelar"})
                    } else {
                        notify("Sólo puedes CANCELAR pólizas Normales, o volver NORMAL pólizas Canceladas", "warning" );
                    }
                    e.stopPropagation();
                });

                // Forzamos el cálculo nuevamente de la suma del debe y haber de la póliza
                // para actualizar el maestro (Libro Diario)
                $("#recalc").click(function(e) {

                    e.preventDefault();
                    e.stopPropagation();
                    // Si no hay ningún documento seleccionado, no hacemos nada
                    const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');
                    if (!row || row.pot_id == "") {
                        return false;
                    }

                    // Si la póliza que quieren cancelar es de otro mes, por el filtro presupuestal
                    // Avisamos del mes al que pertenece
                    strMesPol = "";
                    if ( $("#mesActual").attr("ma") != row.mes ) {
                        strMesPol = "<font color=\"red\"> (" + dameMesCorto( row.mes ) + ")</font>";
                    }

                    var var_chequeCancelado = "";

                    if( row.e == "H" ){
                        alertify.confirm(
                            "REHACER SALDO",
                            "La póliza " + row.pot_id + "-" + row.prf_id + row.numero + " " + strMesPol + " es de cheque cancelado, ¿Deseas eliminar los importes?",
                            function(){
                                comunica("scg/consulta/ControladorLibroDiario.php",{
                                    "metodo" : "recalc",
                                    "m"   : $("#mesActual").attr( "ma" ),
                                    "mpa" : row.mes,
                                    "p"   : row.pot_id,
                                    "r"   : row.prf_id,
                                    "n"   : row.numero,
                                    "ue"  : row.ue,
                                    "cc"  : "H"
                                });
                                e.stopPropagation();
                            },
                            function () {
                                comunica("scg/consulta/ControladorLibroDiario.php",{
                                    "metodo" : "recalc",
                                    "m"   : $("#mesActual").attr( "ma" ),
                                    "mpa" : row.mes,
                                    "p"   : row.pot_id,
                                    "r"   : row.prf_id,
                                    "n"   : row.numero,
                                    "ue"  : row.ue,
                                    "cc"  : ""
                                });
                                e.stopPropagation();
                            }
                        ).set("labels", {ok: "Eliminar Importes", cancel: "Cancelar Operación"});
                    } else {

                        // Rehacemos el saldo de la póliza
                        comunica("scg/consulta/ControladorLibroDiario.php",{
                            "metodo" : "recalc",
                            "m"   : $("#mesActual").attr( "ma" ),
                            "mpa" : row.mes,
                            "p"   : row.pot_id,
                            "r"   : row.prf_id,
                            "n"   : row.numero,
                            "ue"  : row.ue,
                            "cc"  : var_chequeCancelado
                        });
                        e.stopPropagation();
                    }
                });

            JS;
        }

        $cabecera = (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '');
        $urActual = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        $assistance = intval(date('m')) > 2 ? '' :
            <<<JS
            $.getScript( "https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js", 
                function( data, textStatus, jqxhr ) {
                $.getScript( "js/reading.min.js", function(data, textStatus, jqxhr){
                    startRenderingTips();
                });
            });
            JS;

        $js2 = <<<JS
            setLoading();

            $.getScript( "js/scg/libro-diario.min.js", function(data, textStatus, jqxhr){ 
                $("div[aria-describedby=cargaDatosMayor]").html("");
                $("div[aria-describedby=cargaDatosCgCveFin]").html("");

                if ( $( ".container" ).css( "max-width" ) == "1170px" ) {
                    $( ".container" ).css( "max-width", "100%" );
                }
                $("#otraInfoFooter").html( "" );
                $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : {$urActual}" );
                $(".imgheader").attr("alt", "{$strStatusMes}" );
                chkMscId(0);
                $("#tipoActual").attr( "ta", "tp_D" );
                $(".miDivFloat").click(function(e) {
                    e.stopPropagation();
                });

                $(".miDivFloatFiltro").click(function(e) {
                    e.stopPropagation();
                });
                hideLoading();
            })
            {$assistance}
            {$cabecera}
            {$jsEscritura}

            // Mostramos el historial de la póliza
            $("#verHistorial").click(function(e) {
                const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');
                // Si no hay ningún documento seleccionado, no hacemos nada
                if (!row) { return false };

                comunica("scg/consulta/ControladorLibroDiario.php",{
                    "metodo" : "verHistorial",
                    "m"   : $("#mesActual").attr( "ma" ),
                    "mpa" : row.mes,
                    "p"   : row.pot_id,
                    "r"   : row.prf_id,
                    "n"   : row.numero,
                    "ue"  : row.ue
                });
                e.stopPropagation();
            });

            // Mostramos la póliza actualmente seleccionada
            $("#mostrar").click(function(e) {
                e.preventDefault();
                const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');
                if (!row) {
                    return false;
                }

                comunica("scg/poliza/ControladorMuestraPoliza.php",{
                    "m" : $("#mesActual").attr( "ma" ),
                    "mpa" : row.mes,
                    "p" : row.pot_id,
                    "r" : row.prf_id,
                    "n" : row.numero,
                    "ue": row.ue,
                    "mp": $("#draCorazon").attr( "modoPoliza" )
                });
                e.stopPropagation();
            });

            // Cambiamos el comportamiento al imprimir una póliza: normal, mayor, sumarizada
            $("#draCorazon").click(function(e) {
                strModoPoliza = $(this).attr("modoPoliza");
                switch ( strModoPoliza ) {
                    case "1":
                        $(this).html( "<i class=\"fa-solid fa-heart me-2\"></i>Registro" );
                        $(this).attr("modoPoliza", "2");
                        break;
                    case "2":
                        $(this).html( "<i class=\"fa-solid fa-heart me-2\"></i>Mayor" );
                        $(this).attr("modoPoliza", "3");
                        break;
                    case "3":
                        $(this).html( "<i class=\"fa-solid fa-heart me-2\"></i>Sumarizada V" );
                        $(this).attr("modoPoliza", "4");
                        break;
                    case "4":
                        $(this).html( "<i class=\"fa-solid fa-heart me-2\"></i>Sumarizada V+A" );
                        $(this).attr("modoPoliza", "5");
                        break;
                    case "5":
                        $(this).html( "<i class=\"fa-solid fa-heart me-2\"></i>Cuarto Nivel" );
                        $(this).attr("modoPoliza", "6");
                        break;
                    default:
                        $(this).html( "<i class=\"fa-solid fa-heart me-2\"></i>Normal" );
                        $(this).attr("modoPoliza", "1");
                        break;
                }
            });

            // Consultamos en Ts de mayor
            $("#tMayor").click(function(e) {
                e.preventDefault();
                // tiene que tener un valor de vinculación...
                if ( $("#clc_vinculacion").val().trim() != "" || $("#afe_vinculacion").val() != "" || $("#cmp_vinculacion").val() != "" || $("#bdo_vinculacion").val() != "" || $("#ref_vinculacion").val() != "" || $("#clc_origen").val() != "" || $("#afe_origen").val() != "" || $("#cmp_origen").val() != "" || $("#bdo_origen").val() != "" || $("#ref_ejercicio").val() != "" ) {
                    comunica("lib/demoTMayor.php", {
                        clc_v 	: $("#clc_vinculacion").val().trim(),
                        afe_v 	: $("#afe_vinculacion").val(),
                        cmp_v 	: $("#cmp_vinculacion").val(),
                        bdo_v 	: $("#bdo_vinculacion").val(),
                        ref_v 	: $("#ref_vinculacion").val(),
                        clc_o 	: $("#clc_origen").val(),
                        afe_o 	: $("#afe_origen").val(),
                        cmp_o 	: $("#cmp_origen").val(),
                        bdo_o 	: $("#bdo_origen").val(),
                        eje_v 	: $("#ref_ejercicio").val(),
                        "mp"  	: $("#draCorazon").attr( "modoPoliza" ),
                        "vinClc": $("#desdeVinClc").val()
                    });
                } else {
                    notify("Sólo puedes ver el resúmen de Ts de Mayor si tienes filtros de vinculación activados", "warning");
                }

                e.stopPropagation();
                return false;
            });


            $(".mesContable").on("click", function(e) {
                $("#mesActual").html( $(this).text().trim() + "<span class=\"caret\"></span>" );
                $("#mesActual").attr( "ma", $(this).attr("mes") ).attr('edomes', $(this).attr('ems'));
            });
            $(".tipoDePoliza").click( function(e) {
                $("#tipoActual").html( $(this).text().trim() + "<span class=\"caret\"></span>" );
                $("#tipoActual").attr( "ta", $(this).attr("href") );
                e.preventDefault();
            });
            $(".ordenadoPor").click( function(e) {
                $("#ordenActual").attr( "oa", $("#ordenActual option:selected").val() );
                e.preventDefault();
            });

            // Distintas opciones de impresión del libro diario
            $("#poliza").on("click", function(e) {
                e.preventDefault();
                const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');
                if (!row) { 
                    return false;
                }
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/poliza/cgPoliza.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "actual"))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", row.ue))
                    .append($("<input>").attr("type", "hidden").attr("name", "pot").attr("value", row.pot_id ))
                    .append($("<input>").attr("type", "hidden").attr("name", "prf").attr("value", row.prf_id ))
                    .append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value", $("#mesActual").attr( "ma" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mscLinAct").attr("value", row.mes ))
                    .append($("<input>").attr("type", "hidden").attr("name", "num").attr("value", row.numero ))
                    .append($("<input>").attr("type", "hidden").attr("name", "modoPoliza").attr("value", $("#draCorazon").attr( "modoPoliza" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "v").attr("value", $("#numVinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "rev").attr("value", ( $("#chkRevision").prop("checked" ) ? 1 : 0 ) ))
                $("body").append(form);
                form.submit();
                e.stopPropagation();
            });

            $("#xlspoliza").click( function(e) {
                e.preventDefault();
                const row = $("#tbl-libro-diario").tabla('getFilaSeleccion');

                // Si no hay nada seleccionado, no hacemos nada
                if (!row) { e.stopPropagation();   return false; }

                var form = $("<form>")
                    .attr("action", "rep/excel/scg/consulta/exp_cgPoliza.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "actual"))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", row.ue))
                    .append($("<input>").attr("type", "hidden").attr("name", "pot").attr("value", row.pot_id ))
                    .append($("<input>").attr("type", "hidden").attr("name", "prf").attr("value", row.prf_id ))
                    .append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value", $("#mesActual").attr( "ma" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mscLinAct").attr("value", row.mes ))
                    .append($("<input>").attr("type", "hidden").attr("name", "num").attr("value", row.numero ))
                    .append($("<input>").attr("type", "hidden").attr("name", "modoPoliza").attr("value", $("#draCorazon").attr( "modoPoliza" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "v").attr("value", $("#numVinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "rev").attr("value", ( $("#chkRevision").prop("checked" ) ? 1 : 0 ) ))
                $("body").append(form);
                form.submit();
                e.stopPropagation();
            });

            // Si quieren imprimir todas las pólizas de la selección actual ..
            $("#seleccion").click( function(e) {
                e.preventDefault();

                const rows = $("#tbl-libro-diario").tabla('getTotalFilas');
                if ( rows <= 0 ) { e.stopPropagation();   return false; }

                var form = $("<form>")
                    .attr("action", "rep/doc/scg/poliza/cgPoliza.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "sel"))
                    .append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value", $("#mesActual").attr( "ma" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", $("#ueFiltro option:selected").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "pot").attr("value", $("#tipoActual").attr( "ta" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "num").attr("value", $("#numPoliza").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "v").attr("value", $("#numVinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "usr").attr("value", $("#usr_id option:selected").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "modoPoliza").attr("value", $("#draCorazon").attr( "modoPoliza" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "tb").attr("value", $("#nombreBuscar").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "clc_v").attr("value", $("#clc_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "afe_v").attr("value", $("#afe_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "cmp_v").attr("value", $("#cmp_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "bdo_v").attr("value", $("#bdo_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ref_v").attr("value", $("#ref_vinculacion").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "clc_o").attr("value", $("#clc_origen").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "afe_o").attr("value", $("#afe_origen").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "cmp_o").attr("value", $("#cmp_origen").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "bdo_o").attr("value", $("#bdo_origen").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "andOr").attr("value", $("input[name=andOr]:checked").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "rev").attr("value", ( $("#chkRevision").prop("checked" ) ? 1 : 0 ) ))

                $("body").append(form);
                form.submit();
                e.stopPropagation();
            });

            // Imprimir por rango de póliza, siguiendo la sintaxis de word al imprimir páginas específicas
            $("#rango").click( function(e) {
                alertify.prompt("Escribe el rango de Pólizas a imprimir", 
                    "<p><font color=\"red\">El tipo de Póliza y el Mes son los que estén actualmente seleccionados.</font></p><p>Escribe Rangos separados por comas.<t>Ejemplos válidos:</p><p>24 = Imprime la póliza 24 solamente</p><p>1,10,23,102 = Imprime ésas pólizas</p><p>10,14-20,108-110 = Imprime la 10, de la 14 a la 20, y de la 108 a la 110</p>",
                    "", 
                    function(event, rango){
                        if (rango !== null) {
                            var form = $("<form>")
                                .attr("action", "rep/doc/scg/poliza/cgPoliza.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "rango"))
                                .append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value", $("#mesActual").attr( "ma" ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", $("#ueFiltro option:selected").val() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "pot").attr("value", $("#tipoActual").attr( "ta" ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "modoPoliza").attr("value", $("#draCorazon").attr( "modoPoliza" ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "num").attr("value", rango ))
                                .append($("<input>").attr("type", "hidden").attr("name", "rev").attr("value", ( $("#chkRevision").prop("checked" ) ? 1 : 0 ) ))
                            $("body").append(form);
                            form.submit();
                            e.stopPropagation();
                        }
                    }, 
                    undefined )
            });

            // Imprimir por rango de póliza, siguiendo la sintaxis de word al imprimir páginas específicas
            $("#rangoExcel").click( function(e) {
                alertify.prompt("Escribe el rango de Pólizas a imprimir", 
                    "<p><font color=\"red\">El tipo de Póliza y el Mes son los que estén actualmente seleccionados.</font></p><p>Escribe Rangos separados por comas.<t>Ejemplos válidos:</p><p>24 = Imprime la póliza 24 solamente</p><p>1,10,23,102 = Imprime ésas pólizas</p><p>10,14-20,108-110 = Imprime la 10, de la 14 a la 20, y de la 108 a la 110</p>",
                    "", 
                    function(event, rango){
                        if (rango !== null) {
                            var form = $("<form>")
                                .attr("action", "rep/excel/scg/consulta/exp_cgPolizaPorRango.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "rango"))
                                .append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value", $("#mesActual").attr( "ma" ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", $("#ueFiltro option:selected").val() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "pot").attr("value", $("#tipoActual").attr( "ta" ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "modoPoliza").attr("value", $("#draCorazon").attr( "modoPoliza" ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "num").attr("value", rango ))
                                .append($("<input>").attr("type", "hidden").attr("name", "rev").attr("value", ( $("#chkRevision").prop("checked" ) ? 1 : 0 ) ))
                            $("body").append(form);
                            form.submit();
                            e.stopPropagation();
                        }
                    }, 
                    undefined )
            });

            // Libro Diario de la Selección actual
            $(".libroDiario1").click( function(e) {
                e.preventDefault();

                // Para saber si lo quieren en pdf o en excel
                if ( $(this).hasClass("expDiario") ) {
                    strUrl = "rep/excel/scg/consulta/exp_LibroDiario.php";
                } else {
                    strUrl = "rep/doc/scg/consulta/rep_TodoLibroDiario.php";
                }

                var form = $("<form>")
                    .attr("action", strUrl)
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value", $("#mesActual").attr( "ma" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ord").attr("value", $("#ordenActual option:selected").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", $("#ueFiltro option:selected").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "pot").attr("value", $("#tipoActual").attr( "ta" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "num").attr("value", $("#numPoliza").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "vin").attr("value", $("#numVinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "usr").attr("value", $("#usr_id option:selected").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "tB").attr("value", $("#nombreBuscar").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "clc_v").attr("value", $("#clc_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "afe_v").attr("value", $("#afe_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "cmp_v").attr("value", $("#cmp_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "bdo_v").attr("value", $("#bdo_vinculacion").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ref_v").attr("value", $("#ref_vinculacion").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "clc_o").attr("value", $("#clc_origen").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "afe_o").attr("value", $("#afe_origen").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "cmp_o").attr("value", $("#cmp_origen").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "bdo_o").attr("value", $("#bdo_origen").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "andOr").attr("value", $("input[name=andOr]:checked").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "txt").attr("value", $("#buscar").val() ))

                $("body").append(form);
                form.submit();
                e.stopPropagation();
            });

            // Libro Diario de todo el mes
            $(".libroDiario2").click( function(e) {
                e.preventDefault();

                // Para saber si lo quieren en pdf o en excel
                if ( $(this).hasClass("expDiario") ) {
                    strUrl = "rep/excel/scg/consulta/exp_LibroDiario.php";
                } else {
                    strUrl = "rep/doc/scg/consulta/rep_TodoLibroDiario.php";
                }

                var form = $("<form>")
                    .attr("action", strUrl)
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value", $("#mesActual").attr( "ma" ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ord").attr("value", $("#ordenActual option:selected").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", $("#ueFiltro option:selected").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "txt").attr("value", $("#buscar").val() ))
                $("body").append(form);
                form.submit();
                e.stopPropagation();
            });

            $("#rfcFiltro").focus( function(e) {
                rfcCampo = "rfcFiltro";
                rfcBeneficiario = "nombreFiltro";
            });

            // F1 muestra el catálogo */
            $("#mostrarClafinan").click( function(e) {
                cveFinID= "ClaveFinan";
                cveFinNom = "";
                $("#ClaveFinan").val("");
                $("#ClaveFinan").attr("ecf","");
                e.preventDefault();
                dlgCveFin.dialog("open");
            });

        JS;

        $this->mixScript($js2);
        $this->mixScript('' . $this->ponF1Proveedores('rfcFiltro', [], 'nombreFiltro') . '');

        // Si viene desde el menú principal...
        if ($clc != '') {
            $this->mixScript('
                $(document).on("click", "#sefipSidebar li", function (e) {
                    e.preventDefault();

                    $("nav").show();
                    $("#sticky-anchor").hide();
                    $("#sticky").hide();

                    $(".cabecera").show();
                    $("#mainContainer").css( "margin-top","35px" );

                    // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                    $("div[aria-describedby=cargaDatosMayor]").html("");
                                $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                    $("#vclcPantallaCompleta").show();

                    e.stopPropagation();
                });

                $(document).on("click", "#homeMenuLD", function (e) {
                    e.preventDefault();
                    $(document).ready(function() { 
                        comunica("scg/vinculacion/ControladorVinculacionClc.php",{ mes: $("#mesActual").attr( "ma" ), tipo: "' . $tipo . '",  edo: "' . $edo . '", buscar: "' . $buscarVinculacion . '"});
                    });
                    e.stopPropagation();
                });

                $("#ld_mesActual").hide();
                $("#ld_tipoActual").hide();
                $("#ld_filtros").hide();
                $("#ld_filtroVinculacion").hide();
            ');
        } else {
            $this->mixScript('
                $(document).on("click", "#sefipSidebar li", function (e) {
                    e.preventDefault();
                    $("nav").show();
                    $(".modulos").show();' .
                (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                    : '') . '
                    $("#sticky-anchor").hide();
                    $("#sticky").hide();
                    $("#mainContainer").css( "margin-top","40px" );

                    // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                    $("div[aria-describedby=cargaDatosMayor]").html("");
                    $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                    $("#ayuda").addClass("gris");
                    $("#tip").addClass("gris");

                    $("#otraInfoFooter").text( $("#otraInfoFooter").attr("original") );

                    $("#cargaDatosCuentas").remove();

                    $("#contenedor").html("");

                    $("nav").menusinpres("clickMenu");

                    $(document).off("click", "#sefipSidebar li" );

                    return false;
                });
            ');
        }
    }

    public function dibujaPantalla()
    {
        $clc = ((isset($this->prm->clc) && $this->prm->clc != '') ? $this->prm->clc : '');

        $html = '
        <div style="position:relative">
            <div id="LibroDiarioPaginador" style="float:right;position:absolute;right:0;top:0;z-index:100;"></div></div>
            <span style="height:35px; display:block"></span>
        </div>
        <div id="myGridLibroDiario"></div>

        ';
        $this->setContenedor('contenedor');
        $this->mixHtml($html);
        $this->mixScript(DibujaPantallaLibroDiarioJS());

        // Dibujamos la pantalla que permite buscar y seleccionar Claves de Financiamiento
        // --------------------------------------------------------------------------------------------------------------
        $es116 = $this->gpo_id . $this->ur_id == 116;
        $this->mixHtml(DibujaPantallaBuscarCgClaveFinaciamientoHTML(false, $es116));
        $this->mixScript(DibujaPantallaBuscarCgClaveFinaciamientoJS());
        $this->mixScript('
            $("#tituloPantalla").hide();
            // Para que el GRID se ajuste al espacio disponible en pantalla
            var style =$(".grid-canvas").attr("style");
            style += ";height: 350px !important;";
            $(".grid-canvas").attr("style",style);
            $("#myGridLibroDiario").height($(myGridLibroDiario).height() + 500);

        ');

        // Marcos: 22/abr/2016
        // Si viene desde la vinculación por CLC, ya rellenamos los filtros, así que "pulsamos"
        // el botón MOSTRAR
        if ($clc != '') {
            $this->mixScript('
                setTimeout( function() {
                    $("#mostrar2").trigger("mousedown");
                }, 100 );
            ');
        }
    }
    /* Cancelamos una póliza
        Si la póliza tiene un status Normal ... le ponemos un status Cancelada
        Si la póliza tiene un status Cancelada ... le ponemos un status Normal
        Si tiene un status distinto, no hacemos nada
      ---------------------------------------------------------------------------------------------------- */

    private function cancelar()
    {

        $msc_id = ((isset($this->prm->m) && $this->prm->m != '') ? $this->prm->m : '');
        $pot_id = ((isset($this->prm->p) && $this->prm->p != '') ? $this->prm->p : '');
        $prf_id = ((isset($this->prm->r) && $this->prm->r != '') ? $this->prm->r : '');
        $numero = ((isset($this->prm->n) && $this->prm->n != '') ? $this->prm->n : '');
        $ue = ((isset($this->prm->ue) && $this->prm->ue != '') ? $this->prm->ue : '');

        $mesPolizaActual = ((isset($this->prm->mpa) && $this->prm->mpa != '') ? $this->prm->mpa : '99'); // Numero inexistente de mes en BD para cuando venga vacío y así nunca lo encuentre
        // Si el mes de la línea de la póliza es diferente al que tienen seleccionado en
        // el combo, el de la línea toma prioridad
        if ($mesPolizaActual != 99 && $mesPolizaActual != $msc_id) {
            $msc_id = $mesPolizaActual;
        }

        // Primero verificamos que el mes esté abierto ...
        $this->conectar();
        $this->setSql(
            'SELECT ems_id FROM cg_MesContable
            WHERE gpo_id = ? and ur_id = ? AND msc_id = ? ;'
        );
        $this->consultar([$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $msc_id]);
        $this->desconectar();
        $row = $this->extraer();

        if ($row['ems_id'] != 'A') {
            $this->mixScript('
                notify("El mes está cerrado", "error" );
            ');
            return false;
        }

        // Verificamos que la póliza tenga un status N o C, por si nos quieren meter gol
        // modificando el DOM de la pantalla
        $this->conectar();
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $ue,
            $pot_id, $prf_id, $msc_id, $numero,
        ];
        $this->setSql('SELECT epl_id, mmc_id FROM cg_PolizaMaestro
            WHERE gpo_id = ? and ur_id = ? AND ue_id =  ?
                AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ? ');
        $this->consultar($params);
        $this->desconectar();
        $row = $this->extraer();

        if ($row['epl_id'] != 'C' && $row['epl_id'] != 'N' && $row['epl_id'] != 'H') {
            $this->mixScript('
                notify("Sólo puedes CANCELAR pólizas Normales, o volver NORMAL pólizas Canceladas / Cheque Cancelado", "warning" );
            ');
            return false;
        }

        $nuevoStatus = ($row['epl_id'] == 'N') ? 'C' : 'N';
        $mensaje = ($row['epl_id'] == 'N') ? 'Se ha CANCELADO la póliza' : 'Se ha REACTIVADO la póliza';

        // Marcos: 17/oct/2016
        // Israel solicita que se validen lo siguiente ANTES de permitirle reactivar una póliza
        // 1. Obviamente, debe estar cancelada, ya que lo que impediremos es su reactivación
        // 2. Si incluye al menos una cuenta de orden o de gasto ( 8 o 5 ), NO permitiremos su reactivación
        // 3. Debe estar marcada como mmc_id = "L" ... ya que son las que Japhet genera para el pagado
        //    y éstas se generan como captura manual
        // 4. Se exceptuarán de la validación de cuentas de gasto las cuentas 56110 y 55490, ya que éstas
        //    NO son presupuestales, y las controla totalmente el usuario
        if ($row['epl_id'] == 'C') {
            $this->conectar();
            $this->setSql('SELECT   ger_id FROM
                cat_CgSubcuenta
                INNER JOIN (
                    SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                        FROM cg_PolizaDetalle
                        JOIN cg_PolizaMaestro USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
                    WHERE gpo_id = ? and ur_id = ? AND ue_id = ?
                    AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ?
                    AND mmc_id = "L"
                    group by  ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                        ) B  USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)
                        WHERE ecu_id = 2;
                ;');
            $this->consultar($params);
            $this->desconectar();
            $row = $this->getNumeroRegistros();

            if ($row > 0) {
                $this->mixScript('
                    notify("NO SE PERMITE cancelar pólizas generadas desde la pantalla del Pagado", "error" );
                ');
                return false;
            }
        }

        // Ahora sí, cambiamos su status en la base de datos
        $this->conectar();
        $this->setSql('START TRANSACTION;');
        $this->consultar();

        $this->setSql('SET @usr_ip = INET_ATON( "' . $this->getIp() . '" ), @usr_id = ' . getSession('usr_id') . ', @bit_observacion = "' . $mensaje . '"');
        $this->consultar();

        $params = [
            $nuevoStatus,
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
            $ue, $pot_id, $prf_id, $msc_id, $numero,
        ];
        $this->setSql('UPDATE cg_PolizaMaestro SET epl_id = ?
            WHERE gpo_id = ? AND ur_id = ?
                AND ue_id = ? AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ? ;');
        $this->consultar($params);
        $this->setSql('COMMIT;');
        $this->consultar();
        $this->desconectar();

        $this->mixScript('
            var nLinActual = gridLibroDiario.getSelectedRows();
            dataLibroDiario[nLinActual].e = "' . $nuevoStatus . '";
            gridLibroDiario.invalidateRow(nLinActual);
            gridLibroDiario.render();
            notify("' . $mensaje . '", "success");
        ');
        return false;
    }

    /* Cuando llamamos a la edición de una póliza, actualizamos los datos de esa póliza en la línea que le
      corresponde del libro diario, por si varío alguno de sus datos
      ---------------------------------------------------------------------------------------------------- */

    private function refrescaLinea()
    {

        $msc_id = ((isset($this->prm->m) && $this->prm->m != '') ? $this->prm->m : '');
        $pot_id = ((isset($this->prm->p) && $this->prm->p != '') ? $this->prm->p : '');
        $prf_id = ((isset($this->prm->r) && $this->prm->r != '') ? $this->prm->r : '');
        $numero = ((isset($this->prm->n) && $this->prm->n != '') ? $this->prm->n : '');
        $ue = ((isset($this->prm->ue) && $this->prm->ue != '' && $this->prm->ue != '%') ? $this->prm->ue : '');

        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $ue,
            $pot_id, $prf_id, $msc_id, $numero,
        ];
        // Obtenemos los datos ACTUALES de la base de datos
        $this->conectar();
        $this->setSql("SELECT pot_id, prf_id, pma_id AS numero, DAY( pma_fecha ) AS dia, pma_concepto AS c, pma_debe AS d, pma_haber AS h, mmc_id AS mc,
                '' AS v, epl_id AS e, ue_id AS ue FROM cg_PolizaMaestro
                WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?
                AND pot_id = ? AND prf_id = ? AND msc_id = ?  AND pma_id = ? ");
        $this->consultar($params);
        $this->desconectar();
        $row = $this->extraer();

        $this->mixScript('
            var nLinActual = gridLibroDiario.getSelectedRows();
            dataLibroDiario[nLinActual].pot_id = "' . $row['pot_id'] . '";
            dataLibroDiario[nLinActual].prf_id = "' . $row['prf_id'] . '";
            dataLibroDiario[nLinActual].numero = "' . $row['numero'] . '";
            dataLibroDiario[nLinActual].p = "' . $row['pot_id'] . '-' . $row['prf_id'] . $row['numero'] . '";
            dataLibroDiario[nLinActual].d = "' . $row['dia'] . '";
            dataLibroDiario[nLinActual].c = "' . mysql_escape_mimic($row['c']) . '";
            dataLibroDiario[nLinActual].i = "' . $row['d'] . '";
            dataLibroDiario[nLinActual].h = "' . $row['h'] . '";
            dataLibroDiario[nLinActual].mc= "' . $row['mc'] . '";
            dataLibroDiario[nLinActual].v = "' . $row['v'] . '";
            dataLibroDiario[nLinActual].e = "' . $row['e'] . '";
            dataLibroDiario[nLinActual].ue= "' . $row['ue'] . '";
            gridLibroDiario.invalidateRow(nLinActual);
            gridLibroDiario.render();
        ');
        return false;
    }

    // Para pólizas de Orden, con la descripción vacía, el mes abierto, y permisos de escritura
    // Modificamos la descripción de la póliza ya que por alguna razón se generó vacía

    private function modDescrOrden()
    {

        $ue = ((isset($this->prm->ue) && $this->prm->ue != '') ? $this->prm->ue : '');
        $p = ((isset($this->prm->p) && $this->prm->p != '') ? $this->prm->p : '');
        $r = ((isset($this->prm->r) && $this->prm->r != '') ? $this->prm->r : '');
        $n = ((isset($this->prm->n) && $this->prm->n != '') ? $this->prm->n : '');
        $m = ((isset($this->prm->m) && $this->prm->m != '') ? $this->prm->m : '');
        $dp = (isset($this->prm->dp) ? $this->prm->dp : '');

        // Marcos: 08/abr/2015
        // Por el filtro en el libro diario por clc, afectacion, etc., es probable que el
        // mes de la línea de la póliza sea diferente al mes en el que están trabajando (combo)
        $mesPolizaActual = ((isset($this->prm->mpa) && $this->prm->mpa != '')
            ? $this->prm->mpa : '99'); // Numero inexistente de mes en BD para cuando venga vacío y así nunca lo encuentre
        // Si el mes de la línea de la póliza es diferente al que tienen seleccionado en
        // el combo, el de la línea toma prioridad
        if ($mesPolizaActual != 99 && $mesPolizaActual != $m) {
            $m = $mesPolizaActual;
        }

        // Si la descripción viene vacía, no hacemos nada
        if ($dp == '') {
            $this->mixScript('notify("La descripción de la póliza no puede quedar vacía");');
            return false;
        }

        // Validamos que el mes está abierto
        $this->setSql('SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? AND msc_id = ?');
        $this->conectar();
        $this->consultar([
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $m,
        ]);
        $this->desconectar();
        $row = $this->extraer();

        $valido = ($this->getNumeroRegistros() > 0 && $row['ems_id'] == 'A');

        if (! $valido) {
            $this->mixScript('
                notify( "El mes de ' . MesNombre($m) . ' está cerrado", "warning" );
            ');
            return false;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();

        $conexion->setSql('SET @usr_ip = INET_ATON( "' . $this->getIp() . '" )');
        $conexion->consultar();
        $conexion->setSql('SET @usr_id = ' . $_SESSION[_S]['usr_id']);
        $conexion->consultar();
        $conexion->setSql('SET @bit_observacion = "SE GENERÓ LA DESCRIPCIÓN VACÍA EN PÓLIZA DE ORDEN AUTOMATICA"');
        $conexion->consultar();

        // Ponemos la descripción en el maestro
        $params = [
            mysql_escape_mimic($dp),
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $ue, $p, $r, $m, $n,
        ];
        $sql = 'UPDATE cg_PolizaMaestro SET pma_concepto = ?
            WHERE gpo_id = ? AND ur_id = ?
            AND ue_id = ? AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ? ';
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();

        $this->mixScript('
            comunica("scg/consulta/ControladorLibroDiario.php",{ metodo : "refrescaLinea",
                ue   : "' . $ue . '",
                p    : "' . $p . '",
                r    : "' . $r . '",
                m    : "' . $m . '",
                n    : "' . $n . '"
            });
            window.espera.stop();
        ');
        return false;
    }

    // Rehacemos la suma del debe y haber de la póliza y lo grabamos en cg_Maestro (Libro Diario)
    private function recalc()
    {
        $ue = ((isset($this->prm->ue) && $this->prm->ue != '') ? $this->prm->ue : '');
        $p = ((isset($this->prm->p) && $this->prm->p != '') ? $this->prm->p : '');
        $r = ((isset($this->prm->r) && $this->prm->r != '') ? $this->prm->r : '');
        $n = ((isset($this->prm->n) && $this->prm->n != '') ? $this->prm->n : '');
        $m = ((isset($this->prm->m) && $this->prm->m != '') ? $this->prm->m : '');
        $cc = (isset($this->prm->cc) ? $this->prm->cc : '');

        // Marcos: 08/abr/2015
        // Por el filtro en el libro diario por clc, afectacion, etc., es probable que el
        // mes de la línea de la póliza sea diferente al mes en el que están trabajando (combo)
        $mesPolizaActual = ((isset($this->prm->mpa) && $this->prm->mpa != '') ? $this->prm->mpa : '99'); // Numero inexistente de mes en BD para cuando venga vacío y así nunca lo encuentre

        // Si el mes de la línea de la póliza es diferente al que tienen seleccionado en
        // el combo, el de la línea toma prioridad
        if ($mesPolizaActual != 99 && $mesPolizaActual != $m) {
            $m = $mesPolizaActual;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();

        // Si quieren poner en ceros una poliza de cheque cancelado, lo hacemos en el detalle
        $params = [
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
            $ue, $p, $r, $m, $n,
        ];

        if ($cc == 'H') {
            $ip = $this->getIp();
            $usrId = getSession('usr_id');
            $conexion->setSql('SET @usr_ip = INET_ATON( "' . $ip . '" ), @usr_id = ' . $usrId . ', @bit_observacion = "REHACER SALDOS MANUALMENTE H";');
            $conexion->consultar();

            $sql = 'UPDATE cg_PolizaDetalleAuxiliar SET pdx_debe = 0, pdx_haber = 0 WHERE gpo_id = ? AND ur_id = ? AND ue_id = ? AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ? ;';
            $conexion->setSql($sql);
            $conexion->consultar($params);
        }

        $ip = $this->getIp();
        $usrId = getSession('usr_id');
        $conexion->setSql('SET @usr_ip = INET_ATON( "' . $ip . '" ), @usr_id = ' . $usrId . ', @bit_observacion = "REHACER SALDOS MANUALMENTE";');
        $conexion->consultar();

        $sql = 'UPDATE cg_PolizaDetalle d SET pdt_debe  = 0, pdt_haber = 0
            WHERE gpo_id = ? AND ur_id = ? AND ue_id = ? AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ? ;';
        $conexion->setSql($sql);
        $conexion->consultar($params);

        // Colocamos en el maestro la suma deudora y acreedora de todos sus detalles
        $sql = 'UPDATE cg_PolizaDetalle d
            INNER JOIN (
                SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id,
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, nat_id, pdt_id,
                SUM( pdx_debe ) AS debe, SUM( pdx_haber ) AS haber
                FROM cg_PolizaDetalleAuxiliar
                WHERE gpo_id = ? AND ur_id = ? AND ue_id = ? AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ?
                GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ue_id, pot_id, prf_id,pdt_id, nat_id
            ) AS a
            ON
                d.gpo_id = a.gpo_id AND
                d.ur_id = a.ur_id AND
                d.ue_id = a.ue_id AND
                d.pot_id = a.pot_id AND
                d.prf_id = a.prf_id AND
                d.msc_id = a.msc_id AND
                d.pma_id = a.pma_id AND
                d.ger_id = a.ger_id AND
                d.cgp_id = a.cgp_id AND
                d.rub_id = a.rub_id AND
                d.cgcu_id = a.cgcu_id AND
                d.cgsc_id = a.cgsc_id AND
                d.nat_id = a.nat_id AND
                d.pdt_id = a.pdt_id
            SET
            pdt_debe  = a.debe,
            pdt_haber = a.haber;';

        $conexion->setSql($sql);
        $conexion->consultar($params);

        // Validamos que no queden huerfanos :(
        $sql = 'DELETE cg_PolizaDetalle
            FROM cg_PolizaDetalle
            LEFT JOIN cg_PolizaDetalleAuxiliar da
                USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id, pdt_id )
            WHERE gpo_id = ? AND ur_id = ? AND ue_id = ? AND pot_id = ?
                AND prf_id = ? AND msc_id = ? AND pma_id = ?
                AND da.pdt_id IS NULL;';

        $conexion->setSql($sql);
        $conexion->consultar($params);

        $sql = 'UPDATE cg_PolizaMaestro m
                INNER JOIN (
                SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id,
                    SUM( pdt_debe ) AS debe, SUM( pdt_haber ) AS haber
                    FROM cg_PolizaDetalle
                    WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?
                        AND pot_id = ? AND prf_id = ? AND msc_id = ? AND pma_id = ?
                ) AS a
                ON
                    m.gpo_id = a.gpo_id AND
                    m.ur_id = a.ur_id AND
                    m.ue_id = a.ue_id AND
                    m.pot_id = a.pot_id AND
                    m.prf_id = a.prf_id AND
                    m.msc_id = a.msc_id AND
                    m.pma_id = a.pma_id
            SET
            pma_debe  = a.debe,
            pma_haber = a.haber;';
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();
        $this->mixScript('$("#mostrar2").trigger("click");');
    }

    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Mostramos el historial de la póliza actualmente seleccionada
    ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function verHistorial()
    {

        $tabla = new stdClass();
        $tabla->id = 'tblHistorial';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaHistorial';
        $tabla->comunica->m = (string) $this->prm->m;
        $tabla->comunica->mpa = (string) $this->prm->mpa;
        $tabla->comunica->p = (string) $this->prm->p;
        $tabla->comunica->r = (string) $this->prm->r;
        $tabla->comunica->n = (string) $this->prm->n;
        $tabla->comunica->ue = (string) $this->prm->ue;
        $tabla->comunica->pos = '{$("#input_tab").val()}';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Usuario';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Observaciones';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Observaciones';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha';
        $th->width = '180px';
        array_push($tr, $th);

        $trbody = [];
        //contenido clave
        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'bit_observacion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'pma_concepto';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'usr_fecha';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->setContenedor('scpPantallaCompleta');

        $this->mixHtml('
            <div id="myModalMeta" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; ">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header" style="background:#9d2449; color:white;">
                            <h4 class="modal-title" id="myModalLabel">Historial de la Póliza</h4>
                            <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="page-tabs">
                                <input type="hidden"  id="input_tab" value="1"/>
                                <ul class="nav nav-tabs">
                                    <li class="nav-item "><a class="nav-link active" href="1"> Maestro </a></li>
                                    <li class="nav-item" ><a class="nav-link" href="2">Detalle</a></li>
                                    <li class="nav-item "><a class="nav-link" href="3">Auxiliar</a><div class="last"></li>
                                </ul>
                            </div>
                            <div class="row">' . $this->wrPonTabla($tabla) . '</div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                $("#homeMenu").click();
                $("#myModalMeta").remove();
                e.stopPropagation();
            });

            $(".cerrar").click(function(){ $("#myModalMeta").remove(); });

            $("#page-tabs a").click( function(e) {
                e.preventDefault();
                $("#input_tab").val($(this).attr("href"));
                $("#page-tabs a").each(function (index) { $(this).removeClass("active"); });
                $(this).closest("a").addClass("active");
                ' . $this->wrLlamaLlenaTabla('tblHistorial') . '
                return false;
                e.stopPropagation();
            });
        ');

        $this->llenaHistorial();
    }

    /*------------------------------------------------------------------------------------------------------------------------------------
        Llenamos la tabla del historial
    ------------------------------------------------------------------------------------------------------------------------------------- */

    private function llenaHistorial()
    {

        $pos = ((isset($this->prm->pos) && $this->prm->pos != 0) ? $this->prm->pos : 1);

        $mesPolizaActual = ((isset($this->prm->mpa) && $this->prm->mpa != '')
            ? $this->prm->mpa : '99'); // Numero inexistente de mes en BD para cuando venga vacío y así nunca lo encuentre
        // Si el mes de la línea de la póliza es diferente al que tienen seleccionado en
        // el combo, el de la línea toma prioridad
        if ($mesPolizaActual != 99 && $mesPolizaActual != $this->prm->m) {
            $this->prm->m = $mesPolizaActual;
        }

        // El campo de concepto varía de tabla en tabla
        switch ($pos) {
            case 2:
                $tabla = 'Detalle';
                $sql = "SELECT usr_usuario, bit_observacion, CONCAT( 'cuenta :', ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ' | debe :', FORMAT( pdt_debe, '#,##0.00' ), ' | haber:', FORMAT( pdt_haber, '#,##0.00' ), ' | nat :', nat_id ) AS pma_concepto, sinpres.fn_NombreMes(b.usr_fecha) AS usr_fecha ";
                break;
            case 3:
                $tabla = 'DetalleAuxiliar';
                $sql = "SELECT usr_usuario, bit_observacion, CONCAT( 'cuenta :', ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, '.', cgc_id, '.', cta_id, ' | debe :', FORMAT( pdx_debe, '#,##0.00' ), ' | haber:', FORMAT( pdx_haber, '#,##0.00' ), ' | nat :', nat_id ) AS pma_concepto, sinpres.fn_NombreMes(b.usr_fecha) AS usr_fecha ";
                break;
            default:
                $tabla = 'Maestro';
                $sql = 'SELECT usr_usuario, bit_observacion, pma_concepto, sinpres.fn_NombreMes(b.usr_fecha) AS usr_fecha ';
                break;
        }

        $sql .= '
            FROM ' . BD_BITACORA . '.bit_Poliza' . $tabla . ' b
            INNER JOIN cat_Usuario  USING (usr_id)
            WHERE b.gpo_id = ? AND b.ur_id = ?
            AND b.ue_id = ?
            AND b.pot_id = ? AND b.prf_id = ? AND b.msc_id = ? AND b.pma_id = ?
            ORDER BY b.usr_fecha;';
        $params = [
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
            $this->prm->ue,
            $this->prm->p,
            $this->prm->r,
            $this->prm->m,
            $this->prm->n,
        ];

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblHistorial';
        $nav->nf = '999'; // No usaremos paginación en ésta pantalla
        $this->setParams($params);
        $this->wrLlenaTabla($nav);

        $this->mixScript(
            '
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            $(".tabla-footer").remove();'
        );
    }

    private function getTablaLibroDiario(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->inputFiltro = 'buscar';
        $tabla->id = 'tbl-libro-diario';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaLibroDiario';

        //filters
        $tabla->comunica->msc_id = '{$("#msc_id").find("option:selected").val()}';
        $tabla->comunica->m = '{$("#mesActual").attr("ma")}';
        $tabla->comunica->t = '{$("#tipoActual").attr("ta")}';
        $tabla->comunica->o = '{$("#ordenActual").find("option:selected").val()}';
        $tabla->comunica->n = '{$("#numPoliza").val()}';
        $tabla->comunica->v = '{$("#numVinculacion").val()}';
        $tabla->comunica->nc = '{$("#numCLC").val()}';
        $tabla->comunica->ue = '{$("#ueFiltro option:selected").val()}';

        $tabla->comunica->tclc = '{$("#tclc_id option:selected").val()}';
        $tabla->comunica->u = '{$("#usr_id option:selected").val()}';
        $tabla->comunica->tb = '{$("#nombreBuscar").val()}';
        $tabla->comunica->p = '{$("#buscar").attr("ps")}';
        $tabla->comunica->po = '{$("#chkOrden").prop("checked")}';
        $tabla->comunica->rfc = '{$("#rfcFiltro").val()}';
        $tabla->comunica->apg = '{$("#apg_id option:selected").val()}';

        $tabla->comunica->clc_v = '{$("#clc_vinculacion").val()}';
        $tabla->comunica->afe_v = '{$("#afe_vinculacion").val()}';
        $tabla->comunica->cmp_v = '{$("#cmp_vinculacion").val()}';
        $tabla->comunica->bdo_v = '{$("#bdo_vinculacion").val()}';
        $tabla->comunica->ref_v = '{$("#ref_vinculacion").val()}';
        $tabla->comunica->eje_v = '{$("#ref_ejercicio").val()}';
        $tabla->comunica->clc_o = '{$("#clc_origen").val()}';
        $tabla->comunica->afe_o = '{$("#afe_origen").val()}';
        $tabla->comunica->cmp_o = '{$("#cmp_origen").val()}';
        $tabla->comunica->bdo_o = '{$("#bdo_origen").val()}';
        $tabla->comunica->vinClc = '{ $("#desdeVinClc").val()}';
        $tabla->comunica->andOr = '{$("input[name=andOr]:checked").val()}';
        $tabla->comunica->rfc = '{$("#rfcFiltro").val()}';
        $tabla->comunica->ecf = '{$("#ClaveFinan").val()}';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Póliza';
        $th->width = '90px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Día';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Descripción';
        $th->width = '50%';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Importe';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Vinculación';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Edo';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UE';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['poliza', 'apg'];
        $td->jsCallback = 'PolFormatter';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['dia', 'mes'];
        $td->ajustable = true;
        $td->jsCallback = 'FormatoDia';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['c', 'e'];
        $td->ajustable = true;
        $td->jsCallback = 'ldFormatearTexto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['d', 'h'];
        $td->jsCallback = 'LDFormatoMoneda';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mc';
        $td->align = 'center';
        $td->jsCallback = 'MCFormatter';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'e';
        $td->jsCallback = 'CheckmarkFormatter';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ue';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        return $this->wrPonTabla($tabla);
    }

    public function llenaTablaLibroDiario(): void
    {
        $escritura = (isset($_SESSION[_S]['prm']['C']['4']['1']['2']) ? 0 : 1);
        $msc_id = ((isset($this->prm->m)) ? $this->prm->m : '99');
        $tipo = ((isset($this->prm->t)) ? $this->prm->t : 'tp_D');
        $orden = ((isset($this->prm->o)) ? $this->prm->o : 'or_N');
        $numero = ((isset($this->prm->n) && $this->prm->n != '') ? $this->prm->n
                : 0);
        $ue = ((isset($this->prm->ue) && $this->prm->ue != '') ? $this->prm->ue
                : '');
        $usuario = ((isset($this->prm->u)) ? $this->prm->u : '');
        $textoBuscar = ((isset($this->prm->tb)) ? $this->prm->tb : '');
        $paginaActual = ((isset($this->prm->p) && $this->prm->p != '0') ? $this->prm->p
                : '');
        $polizasOrden = ((isset($this->prm->po)) ? $this->prm->po : '1');   // Por default incluimos las pólizas que sólo contengan cuentas de orden
        $rfc = ((isset($this->prm->rfc)) ? $this->prm->rfc : '');

        // Marcos: 06/abr/2017
        // Se agrega el filtro para Agrupación de Póliza Global
        $apg = ((isset($this->prm->apg)) ? $this->prm->apg : '');

        // Para la vinculación ( pdx_*_origen )
        $clc_v = trim(((isset($this->prm->clc_v)) ? $this->prm->clc_v : ''));
        $afe_v = trim(((isset($this->prm->afe_v)) ? $this->prm->afe_v : ''));
        $cmp_v = trim(((isset($this->prm->cmp_v)) ? $this->prm->cmp_v : ''));
        $bdo_v = trim(((isset($this->prm->bdo_v)) ? $this->prm->bdo_v : ''));

        // 2017: Se agrega el filtro por ejercicio
        $eje_v = trim(((isset($this->prm->eje_v)) ? $this->prm->eje_v : ''));

        // Para la vinculación ( pdx_*_vinculacion )
        $clc_o = trim(((isset($this->prm->clc_o)) ? $this->prm->clc_o : ''));
        $afe_o = trim(((isset($this->prm->afe_o)) ? $this->prm->afe_o : ''));
        $cmp_o = trim(((isset($this->prm->cmp_o)) ? $this->prm->cmp_o : ''));
        $bdo_o = trim(((isset($this->prm->bdo_o)) ? $this->prm->bdo_o : ''));

        $vinClc = trim(((isset($this->prm->vinClc)) ? $this->prm->vinClc : '0'));

        // La referencia sólo es una
        $ref_v = trim(((isset($this->prm->ref_v)) ? $this->prm->ref_v : ''));
        $ecf = trim(((isset($this->prm->ecf)) ? $this->prm->ecf : ''));
        $andOr = trim(((isset($this->prm->andOr)) ? $this->prm->andOr : '0'));
        $andOr = ($andOr == '0' ? 'AND' : 'OR');

        $filtroTexto = trim(((isset($this->prm->txt)) ? $this->prm->txt : ''));

        // Bandera para indicar si pidieron filtros por vinculación, ya que en éstos casos
        // NO limitamos por tipo de póliza
        if ($clc_v != '' || $afe_v != '' || $cmp_v != '' || $bdo_v != '' || $clc_o
            != '' || $afe_o != '' || $cmp_o != '' || $bdo_o != '' || $ref_v != ''
            || $eje_v != '') {
            $bolPidieronVinculacion = true;
        } else {
            $bolPidieronVinculacion = false;
        }

        // Si pidieron el bdo_v o bdo_o ( documento fuente ) por referencia, primero determinamos
        // su ID
        if ($vinClc == '0' && ($bdo_o != '' || $bdo_v != '')) {
            $this->conectar();

            // Obtenemos el de vinculación
            // total es para asegurarnos que nos regrese un registro
            $this->setSql(
                'SELECT bdo_id, count(*) AS total FROM cat_BancoDocumentos
                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . "
                    AND TRIM( bdo_referencia ) = '" . $bdo_v . "';"
            );
            $this->consultar();
            $row = $this->extraer();
            if ($row['total'] > 0) {
                $bdo_v = $row['bdo_id'];
            }

            // Obtenemos el de origen
            // total es para asegurarnos que nos regrese un registro
            $this->setSql(
                'SELECT bdo_id, count(*) AS total FROM cat_BancoDocumentos
                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . "
                    AND TRIM( bdo_referencia ) = '" . $bdo_o . "';"
            );
            $this->consultar();
            $row = $this->extraer();
            if ($row['total'] > 0) {
                $bdo_o = $row['bdo_id'];
            }
        }

        // Si estamos paginando, obtenemos la página actual
        $nInicial = 0;
        if ($paginaActual != '') {
            $nInicial = (($paginaActual - 1) * LINEAS_POR_PAGINA);
        }

        // El orden que pueden escoger es: or_N: por numero de póliza   or_F: por fecha   or_I: por importe
        // -------------------------------------------------------------------------------------------
        switch ($orden) {
            case 'or_F':
                $order = ' ORDER BY pma_fecha';
                break;
            case 'or_I':
                $order = ' ORDER BY pma_debe';
                break;
            case 'or_U':
                $order = ' ORDER BY usr_usuario';
                break;
            default:
                if ($vinClc == '0') {
                    $order = ' ORDER BY pot_id, prf_id, pma_id, m.ue_id';
                } else {
                    $order = ' ORDER BY pot_id, prf_id, numero, ue';
                }
                break;
        }

        $where = '';

        // Si estàn pidiendo ver sólo una ue ... filtramos
        // -------------------------------------------------------------------------------------------
        if ($ue != '' && $ue != '%') {
            $where = ' AND m.ue_id = ' . $ue . ' ';
        }

        // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
        // -------------------------------------------------------------------------------------------
        $opcion = substr($tipo, 0, 2);
        $valor = substr($tipo, 3, 1);
        switch ($opcion) {
            case 'tp':
                // Si filtran por vinculacion presupuestal, no pelamos éste filtro
                if ($bolPidieronVinculacion == false) {
                    $where .= " AND pot_id = '" . $valor . "' ";
                }
                break;
            case 'mc':
                // Si es de pagos a terceros, hay una validación extra
                if ($valor == 'O') {
                    $where .= " AND ( mmc_id = 'P' AND tmc_id = 'C' ) ";
                    // Y por lo tanto el Pagado Pagado normal también
                } elseif ($valor == 'P') {
                    $where .= " AND ( mmc_id = '" . $valor . "' AND tmc_id <> 'C' ) ";
                } else {
                    $where .= " AND mmc_id = '" . $valor . "' ";
                }
                break;
            case 'st':
                $where .= " AND epl_id = '" . $valor . "' ";
                break;
        }

        // Para el filtro por nùmero de pòliza, mostramos las que sean iguales o mayores al numero pedido
        // -------------------------------------------------------------------------------------------
        if ($numero != 0) {
            $where .= ' AND pma_id >= "' . $numero . '" ';
        }

        // Si desean mostrar sólo las pólizas que incluyan determinado texto en el concepto ...
        // -------------------------------------------------------------------------------------------
        if (trim($textoBuscar) != '') {
            $where .= " AND pma_concepto like '%" . $textoBuscar . "%' ";
        }

        // Marcos: 06/abr/2017
        // Se adiciona el filtro por Agrupación de Póliza Global
        // -------------------------------------------------------------------------------------------
        if (trim($apg) != '') {
            $where .= " AND apg_id = '" . $apg . "' ";
        }

        // Para filtrar si un usuario en especial registro o modifico una póliza
        // -------------------------------------------------------------------------------------------
        if (trim($usuario) != '') {
            $where .= " AND ( m.usr_id = '" . $usuario . "' ) ";
        }

        // Limitamos mostrar o no aquellas pólizas que sólo contengan cuentas de orden
        if ($polizasOrden == '0') {
            $where .= " AND  apg_id != 'ORD' ";
        }

        // Para filtrar por RFC (en las pólizas del devengado se guarda el RFC en el maestro)
        // -------------------------------------------------------------------------------------------
        if ($rfc != '') {
            $where .= ' AND rfc_id = "' . $rfc . '" ';
        }

        // Marcos: 05/abr/2016
        // Adicionamos filtros para los campos *_origen y *_vinculacion de la tabla
        // cg_PolizaDetalleAuxiliar
        // Marcos: 23/mayo/2016
        // sólo validamos el campo ORIGEN, VINCULACION ya no

        if ($bolPidieronVinculacion) {
            $where .= ' AND (';
        }

        if ($vinClc == '0') {

            if ($clc_v != '') {
                $where .= " $andOr d.pdx_clc_id_vinculacion = '$clc_v' ";
            }
            if ($afe_v != '') {
                $where .= " $andOr d.pdx_afe_id_vinculacion = '$afe_v' ";
            }
            if ($cmp_v != '') {
                $where .= " $andOr d.pdx_cmp_id_vinculacion IN ( $cmp_v )";
            }
            if ($bdo_v != '') {
                $where .= " $andOr d.pdx_bdo_id_vinculacion = '$bdo_v' ";
            }

            if ($clc_o != '') {
                $where .= " $andOr d.pdx_clc_id_origen = '$clc_o' ";
            }
            if ($afe_o != '') {
                $where .= " $andOr d.pdx_afe_id_origen = '$afe_o' ";
            }
            if ($cmp_o != '') {
                $where .= " $andOr d.pdx_cmp_id_origen IN ( $cmp_o ) ";
            }
            if ($bdo_o != '') {
                $where .= " $andOr d.pdx_bdo_id_origen = '$bdo_o' ";
            }

            if ($eje_v != '') {
                $where .= " $andOr d.pdx_ejercicio = '$eje_v' ";
            }

            // Marcos: 25/mayo/2016
            // Cuando el libro diario se llame desde la pantalla de vinculación por CLC, debemos
            // enviará en bdo_origen, y debemos buscar en los agrupadores de documentos de cada momento
            // contable para llegar correctamente a las pólizas
        } else {
            // $bdo_o = str_replace( ",", "','", $bdo_o );
            // if( $bdo_o != "" ) $where .= " AND d.pdx_agrupador IN ( '$bdo_o' ) ";
            // Nop, buscaremos por clc_id_origen.   Israel revisará que incluya todo menos el comprometido
            // ya que el comprometido usa una consulta aparte
            if ($clc_o != '') {
                $where .= " AND d.pdx_clc_id_origen = $clc_o ";
            }
        }

        // El campo de REFERENCIA, si lo escriben tal y como se imprime... es tdf_id-tdf_referencia
        // así que lo separamos
        if ($ref_v != '') {
            $arrDocto = explode('-', $ref_v);
            // DEBE ser un array de 2 posiciones.   En caso contrario dejamos vacío
            if (count($arrDocto) == 2) {
                $where .= " $andOr d.tdf_id = '" . $arrDocto[0] . "' $andOr d.pdx_referencia = '" . $arrDocto[1] . "' ";
            }
        }

        if ($bolPidieronVinculacion) {
            $where .= ' )';
        }

        // Marcos: 21/marzo/2017
        // Adicionamos el filtro por texto en el cuadro de busqueda
        if ($filtroTexto != '') {
            $where = " AND m.pma_concepto LIKE '%" . $filtroTexto . "%' ";
        }

        // Aunque hasta abril del 2016 el mes era campo fijo, si pedimos filtros por
        // vinculación presupuestal, no pelaremos el mes
        if ($bolPidieronVinculacion == false) {
            $where .= ' AND msc_id = "' . $msc_id . '" ';
        } else {
            // Sin embargo, si piden de la vinculación sólo el ejercicio, y todo lo demás está vacío
            // entonces sí lo activamos
            if ($clc_v == '' || $afe_v == '' || $cmp_v == '' || $bdo_v == '' || $clc_o
                == '' || $afe_o == '' || $cmp_o == '' || $bdo_o == '' || $ref_v == ''
                || $eje_v != '') {
                $where .= ' AND msc_id = "' . $msc_id . '" ';
            }
        }

        // Para corregir un error en la lógica del armado
        $where = str_replace('AND ( AND ', 'AND ( ', $where);
        $where = str_replace('AND ( OR ', 'AND ( ', $where);

        // DETERMINAMOS LAS PAGINAS A USAR, SOLO SI ESTAN ENTRANDO LA PRIMERA VEZ
        /*
        if ($paginaActual == "") {

            $sql = "SELECT msc_id, pot_id, prf_id, pma_id FROM cg_PolizaMaestro m
                    JOIN cg_PolizaDetalleAuxiliar d USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND ur_id = ".$_SESSION[_S]['clasAdm-ur'].
                $where."
                    GROUP BY m.msc_id, m.pot_id, m.prf_id, m.pma_id, m.ue_id";

            if ($vinClc == "1") {

                $strBusca = " AND ( d.pdx_clc_id_origen = $clc_o ";

                $sqlCmp = str_replace(
                    $strBusca,
                    " AND ( d.pdx_cmp_id_origen IN ( $cmp_o ) AND mmc_id = 'C' ",
                    $sql);

                $sql .= " UNION ALL ".$sqlCmp;
            }

            $sql = "SELECT COUNT(*) AS total FROM
                ( ".
                $sql.
                " ) d;";

            // Determinamos cuántas páginas utilizaremos para mostrar el catálogo solicitado
            // -----------------------------------------------------------------------------------------------------------------------
            $this->conectar();
            $this->setSql($sql);
            $this->consultar();
            $this->desconectar();

            $row = $this->extraer();
            $nTotalPages = ceil($row['total'] / LINEAS_POR_PAGINA);
        } else {
            $nTotalPages = 0;
        }
        */
        // Marcos: cuando se trate de la UR 116, SIN unidades ejecutoras, tendrá un comportamiento especial
        // diferente del estándar para todas las URs, ya que es un usuario de tesorería
        if (($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == '116')
            &&
            (!isset($_SESSION[_S]['clasAdm-ue']) || (isset($_SESSION[_S]['clasAdm-ue'])
            && $_SESSION[_S]['clasAdm-ue'] == '005'))
        ) {
            $campoVinculacion = 'max(d.pdx_clc_cereco_vinculacion)';
        } else {
            $campoVinculacion = "''";
        }

        $cvefinSql = (($ecf != '') ? " AND ecf_id = '" . $ecf . "' " : '');

        // Marcos: 26/mayo/2016
        // Para el comprometido, volvemos a ejecutar la misma consulta comparándo ahora sobre
        // los cmp_id, pero limitando al momento contable del comprometido.
        $gpo = $_SESSION[_S]['clasAdm-gpo'];
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $sql = "SELECT
            CONCAT(m.pot_id,'-',m.prf_id, m.pma_id) as poliza, 
            m.pot_id, m.prf_id, m.pma_id AS numero, 
            DAY( m.pma_fecha ) AS dia,
            IF( msc_id = 0 or msc_id = 13, msc_id, MONTH( m.pma_fecha ) ) AS mes,
            m.pma_concepto AS c, 
            m.pma_debe AS d, 
            m.pma_haber AS h, 
            m.mmc_id, 
            CONCAT(m.mmc_id, m.tmc_id) AS mc, 
            {$campoVinculacion} AS v, 
            m.epl_id AS e, 
            m.ue_id AS ue, m.apg_id AS apg, ems_id AS em, 
            usr_usuario AS u
            FROM cg_PolizaMaestro m
                LEFT JOIN cg_PolizaDetalleAuxiliar d USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                LEFT JOIN cg_MesContable USING( gpo_id, ur_id, msc_id )
                LEFT JOIN cat_Usuario u ON ( m.usr_id = u.usr_id )
            WHERE m.gpo_id = {$gpo} AND m.ur_id = {$ur}
                {$where}
                {$cvefinSql}
            GROUP BY m.pot_id, m.prf_id, m.pma_id, m.ue_id
            ";

        if ($vinClc == '1') {

            $strBusca = " AND ( d.pdx_clc_id_origen = $clc_o ";

            $sqlCmp = str_replace(
                $strBusca,
                " AND ( d.pdx_cmp_id_origen IN ( $cmp_o ) AND mmc_id = 'C' ",
                $sql
            );

            $sql .= ' UNION ALL ' . $sqlCmp;
        }

        $sql .= $order;

        // print_r($sql);
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $nav->idTabla = 'tbl-libro-diario';

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        } else {

            $js = <<<JS
                $(".btnNavNLtbl-libro-diario").removeClass("disabled");
                $("#tbl-libro-diario tr").on('dblclick', function (ev){
                    ev.stopPropagation();
                    const prm = $("#tbl-libro-diario").tabla('getFilaSeleccion');
                    const request = {
                        "m" : $("#mesActual").attr( "ma" ),
                        "p" : prm.pot_id,
                        "r" : prm.prf_id,
                        "n" : prm.numero,
                        "ue": prm.ue,
                        "ori": "LibroDiario",
                        "mp": $("#draCorazon").attr( "modoPoliza" )
                    }
                    if ( prm.apg == "ORD" || prm.e == "H" 
                        || prm.e == "C" || 
                        prm.em != "A" || {$escritura}
                    ) {
                        request.mp = $("#draCorazon").attr( "modoPoliza" );
                        comunica("scg/poliza/ControladorMuestraPoliza.php",request);
                    } else {
                        request.orig = 'LibroDiario';
                        comunica("scg/poliza/ControladorPolizaManual.php", request);
                    }
                })
            JS;

            $this->mixScript($js);
        }

    }
}
new ControladorLibroDiario();
