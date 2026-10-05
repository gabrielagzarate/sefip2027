<?php

declare(strict_types=1);
session_start();
/*--------------------------------------------------------------------------------------------------------
 * ControladorCompromiso
 *
 * Esta clase permite generar los compromisos del presupuesto en el SCP, afecta solo este momento (COMPROMETIDO)
 *
 * @author        L.I. Gerardo López López <gerardo.ll@outlook.com>
 * @version:        1.6
- Se van a validar cuatro casos en los compromisos
- 1 Cuando el compromiso está  generado  puedo modificar o eliminar todos mis datos hasta el importe (Estado Generado)
- 2 Cuando el compromiso está  Autorizado pero no esta vinculado a ninguna Afectacion  solo puedo cancelar mi compromiso(Autorizado)
- 3 Cuando el compromiso se encuentre parcialmente devengado primero se tiene que cancelar el devengado,los numeros de control quedan cancelados
y el compromiso queda intacto y podria  cancelarlo
- 4 Puedo realizar una ampliacion o reduccion verificando el devengado cuando el compromiso este autorizado y esté devengado

- EL grupo 1 son las UR´S del contador Jacinto Salinas
- El grupo 2 son las princesas de la contadora Martha

Modifico 2017 : @JaphetMata      @version 1.9

 * SE SEPARA TODO EN MULTIPLES ARCHIVOS PARA MEJORAR LA VISIBILIDAD DEL CODIGO
(ESTO POR SI SE ROBAN NUESTRO CODIGO PUES NO LES CUESTE MUCHO A LOS PAISAS)
 * PARTE DEL CODIGO HTML Y GENERACION DE CONTROLES DE FORMULARIO EN lib/scp/vistaCompromiso.php en conjunto con vistaScp
 * CONSULTAS SQL Y OBTENCION DE REGISTROS FILTRADOS EN lib/scp/modelCompromisos.php en conjunto con modelScp
 *  SE VALIDA QUE EL USARIO QUE EDITA, ELIMINA CANCELA O AJUSTA EL COMPROMISO SI ES NIVEL 1 SEA DUEÑO DEL COMPROMISO
 * SE VALIDA QUE  CUALQUIER USUARIO CON PERMISOS PUEDA VER LOS COMPROMISOS, PERO SI ES NIVEL MAYOR A 2 NO PUEDE HACER NADA AUNQUE TENGA
PERMISOS DE ESCRITURA ( SOLO SERVIDOR DE PRODUCCION )

 * SE SIMPLIFICA Y REUTILIZA CODIGO DE VARIAS PARTES
 * SE USA UN VISOR/MODAL JMAVIEWER® (BETA) PARA EDITAR/AGREGAR COMPROMISOS, EVITANDO LA RECARGA Y CONSULTA DEL LISTADO DE COMPROMISOS

Modificaciones 2017 : @Japhet version 1.9.3
GARANTÍA: "SI LO ROMPES PUEDES QUEDARTE CON LOS PEDAZOS"
"THE QUIETER YOU BECOME THE MORE YOU ARE ABLE TO HEAR"
--------------------------------------------------------------------------------------------------------*/

/*
 *Autor @Jordy - Fecha de creacion 14-11-2018
 *Se cambiaron las variables de sesion para 2019 y se pasaron al constructor
 *Se actualiza en el metodo addcompromiso la varible del grupo, estaba aplicando mal la regla para las princesas
 *Se agrega la biblioteca de proovedores para el rfc al agregar un compromiso
 *Para los ajustes del compromiso no se muestran si el compromiso estan generados o cancelados esto a reserva por parte del usuario
 *Fecha de la ultima actualizacion 28-11-2018

 */

if ($_SERVER['SERVER_ADDR'] != '172.17.90.88') {
    error_reporting(E_ALL);
} else {
    error_reporting(E_ERROR);
}

require_once dirname(__FILE__) . '/../../ent/scp/Compromisos.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelCompromisos.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaCompromiso.php';
require_once dirname(__FILE__) . '/../../lib/funciones.php';
require_once dirname(__FILE__) . '/../../lib/funcionesGenericas.php';
require_once dirname(__FILE__) . '/../../lib/Math.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once dirname(__FILE__) . '/ValidaGarantias.php';

class ControladorCompromiso extends WebResponse
{
    public $escritura;
    public $urnoavalilable;
    public $gpo_id;
    public $ur_id;
    public $gpo_id2;
    public $ur_id2;
    public $usr_id;
    public $nvj;
    public $sUeId;
    public $clasAdmGpo;
    public $clasAdmUe;
    public $usuarioIp;
    public vistaCompromiso $vista;
    public $grupo2;
    public $princesa;
    public $clasAdmUr;
    public modelCompromisos $model;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/afectaciones/ControladorCompromiso.php';
        if ($_SERVER['SERVER_ADDR'] == '172.17.90.17') {
            $this->escritura = isset($_SESSION[_S]['prm']['S']['2']['3']['2']) && $_SESSION[_S]['nvj_id'] < 3 ? true : false;
        } else {
            $this->escritura = isset($_SESSION[_S]['prm']['S']['2']['3']['2']) ? true : false;
        }

        $this->urnoavalilable = [];
        $this->gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $this->gpo_id2 = $_SESSION[_S]['gpo_id'];
        $this->ur_id2 = $_SESSION[_S]['ur_id'];
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->sUeId = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '';
        $this->clasAdmGpo = $_SESSION[_S]['clasAdm-gpo'];
        $this->clasAdmUr = $_SESSION[_S]['clasAdm-ur'];
        $this->clasAdmUe = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : '';
        $this->usuarioIp = $_SESSION[_S]['ses_ip'];
        $this->vista = new vistaCompromiso();
        $this->grupo2 = [105, 530, 531, 540, 553];
        $this->vista->setPrincesas($this->grupo2);
        $this->princesa = in_array($this->gpo_id . $this->ur_id, $this->grupo2) ? true : false;
        $this->model = new modelCompromisos();

        switch ($this->metodo) {
            case 'llenaTablaCompromisos':
                $this->llenaTablaCompromisos();
                break;
            case 'addCompromiso':
                $this->addCompromiso();
                break;
            case 'comboNomina':
                $this->comboNomina();
                break;
            case 'ObjetoGasto':
                $this->ObjetoGasto();
                break;
            case 'GraficaPresupuesto':
                $this->GraficaPresupuesto();
                break;
            case 'TipoCompra':
                $this->TipoCompra();
                break;
            case 'Proveedor':
                $this->Proveedor();
                break;
            case 'DocumentoSoporte':
                $this->DocumentoSoporte();
                break;
            case 'abcCompromiso':
                $this->abcCompromiso();
                break;
            case 'AutorizarCompromiso':
                $this->AutorizarCompromiso();
                break;
            case 'CancelarCompromiso':
                $this->CancelarCompromiso();
                break;
            case 'AjusteCompromiso':
                $this->AjusteCompromiso();
                break;
            case 'ValidarAjuste':
                $this->ValidarAjuste();
                break;
            case 'DatosObra':
                $this->DatosObra();
                break;
            case 'Contratos':
                $this->Contratos();
                break;
            case 'llenaDetalleCompromiso':
                $this->llenaDetalleCompromiso();
                break;
            case 'DetalleCompromiso':
                $this->DetalleCompromiso();
                break;
            case 'ActualizarCompromisos':
                $this->ActualizarCompromisos();
                break;
            case 'DameNombreProveedor':
                $this->DameNombreProveedor();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->mixScript('$("' . (($this->nvj == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();');
        $this->prm->cmp = (isset($this->prm->cmp_id)) ? $this->prm->cmp_id : '';
        $this->mixJs('js/charts/highcharts.src.js');
        $this->mixJs('js/charts/highcharts-3d.src.js');
        $this->mixJs('js/charts/modules/data.js');
        $this->mixJs('js/charts/modules/drilldown.js');
        $this->mixScript(
            <<<JS
                    function validaTip(){
                        var s = "";
                        $(".tcl_idfont").each(function(){
                            if($(this).hasClass("tcl_ida")){s =$(this).attr("id");}
                        });
                        if (s =="") {
                            notify("Debe seleccionar un tipo de Compromiso!!","warning");
                            return false
                        } else return s;
                    }
                JS
        );

        $estado = $this->vista->getControlesBuscar('estado');
        $estado = $this->ponCombo($estado);

        $mes = $this->vista->getControlesBuscar('mes');
        $mes = $this->ponCombo($mes);

        $concepto = $this->vista->getControlesBuscar('concepto');
        $concepto = $this->ponInput($concepto);

        $importe = $this->vista->getControlesBuscar('importe');
        $importe = $this->ponInput($importe);

        // Marcos: 08/mar/2018
        // Agregamos algunos filtros
        $filtroRfc = $this->vista->getControlesBuscar('filtroRfc');
        $filtroRfc = $this->ponInput($filtroRfc);
        $filtroOrden = $this->vista->getControlesBuscar('filtroOrden');
        $filtroOrden = $this->ponInput($filtroOrden);

        $cmp = $this->vista->getControlesBuscar('cmp');
        if ($this->prm->cmp != '') {
            $cmp->value = $this->prm->cmp;
        }

        $cmp = $this->ponInput($cmp);

        $clean = $this->vista->getControlesBuscar('clean');
        $clean = $this->ponBoton($clean);

        $btnConsulta = $this->vista->getControlesBuscar('btnConsulta');
        $btnConsulta = $this->ponBoton($btnConsulta);

        $script = $this->getScript();

        $sql = '';
        $ur315 = '';
        $ue_id = '';

        if (isset($this->sUeId) || isset($this->clasAdmUe)) {
            $ue_id = $this->nvj == 1 ? $this->sUeId : $this->clasAdmUe;
        }
        $params = [$this->gpo_id, $this->ur_id];
        if (isset($urclave) && $urclave == 116) {
            $val = '';
            switch ($ue_id) {
                //003 FIDEICOMISOS, APORTACIONES Y OTROS
                //004 SUBSIDIOS, ESTIMULOS FISCALES Y OTROS
                case '003':
                case '004':
                    $ur315 = 'AND tcl_id = ?';
                    $val = 'L';
                    break;
                //002 DEUDA PUBLICA
                case '002':
                    $ur315 = 'AND tcl_id = ?';
                    $val = 'J';
                    break;
                //001 INGRESOS Y DERECHOS AFECTOS A DIFEICOMISOS
                case '001':
                    $ur315 = 'AND tcl_id = ?';
                    $val = 'K';
                    break;
                //005 UNIDAD DE PLANEACION FINANCIERA PAGOS
                //006 RECAUDACION
                default:
                    $ur315 = 'AND tcl_id = ?';
                    $val = 'x';
                    break;
            }
            array_push($params, $val);
        }
        /* PONER LAS HERRAMIENTAS DE LA TABLA DE COMPROMISOS  */
        $tipo = '';

        if (isset($this->prm->tipo)) {
            $tipo = $this->prm->tipo;
        }

        // @Jordy 21-11-2018 consulta para los subtipos de CLC
        $sql = '
            SELECT
                gpo_id,ur_id,tcl_id,tcl_nombre
            FROM cat_TipoClc
            INNER JOIN enc_FiltroClc using(tcl_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                ' . $ur315 . '
                AND tcl_id NOT IN ("C","D","H","E","M")
            GROUP BY gpo_id,ur_id,tcl_id
            ORDER BY gpo_id,ur_id,tcl_id;
        ';

        $tcl_id = $this->model->getDatos($sql, false, $params);
        if ($tcl_id['registros'] == 1) {
            $tipo = $tcl_id['datos'][0]['tcl_id'];
        }

        $radios = $this->vista->vwTipoClc($tcl_id, $tipo);
        // Marcos: 08/mar/2018
        // Agregamos algunos filtros ( rfc y No. Orden )
        $radios .= $this->vista->vwToolsTablaCompromiso([
            'estado'      => $estado,
            'cmp'         => $cmp,
            'mes'         => $mes,
            'importe'     => $importe,
            'filtroRfc'   => $filtroRfc,
            'filtroOrden' => $filtroOrden,
            'concepto'    => $concepto,
            'btnConsulta' => $btnConsulta,
            'clean'       => $clean,
        ]);

        /* GENERAR LA TABLA DE COMPROMISOS   */
        $tabla = new stdClass();
        $tabla->tools = $radios;

        $tabla->multiseleccionable = true;
        /**SE MANDA LOS DATOS PARA LOS COMPROMISOS SELECCIONADOS EN LA AUTORIZACION*/
        $tabla->prmSeleccion = ['cmp_id', 'programatico', 'partida'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'ecm_id';
        $tabla->casoSeleccionable->valor = 'G';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
        $tabla->casoSeleccionable->ands = [];
        $and = new stdClass();
        $and->campo = 'cmp_importe';
        $and->operador = '!=';
        $and->valor = '0';
        $and->valorTipo = 'numero';
        array_push($tabla->casoSeleccionable->ands, $and);
        $and = new stdClass();
        $and->campo = 'ecr_id';
        $and->operador = '!=';
        $and->valor = '0';
        $and->valorTipo = 'numero';
        array_push($tabla->casoSeleccionable->ands, $and);

        $tabla->casosEstado = new stdClass();
        $tabla->casosEstado->campo = 'ecm_id';
        $tabla->casosEstado->cases = [];
        $caso = new stdClass();
        $caso->valor = 'A';
        $caso->color = 'rgba(8, 255, 18, 0.20)';
        array_push($tabla->casosEstado->cases, $caso);

        $caso = new stdClass();
        $caso->valor = 'C';
        $caso->color = 'rgba(255, 0, 0, 0.20)';
        array_push($tabla->casosEstado->cases, $caso);

        $caso = new stdClass();
        $caso->valor = 'D';
        $caso->color = 'rgba(85, 50, 118, 0.15)';
        array_push($tabla->casosEstado->cases, $caso);

        $caso = new stdClass();
        $caso->valor = 'P';
        $caso->color = 'rgba(205, 88, 31, 0.20)';
        array_push($tabla->casosEstado->cases, $caso);

        if (date('Y') <= APP_EJERCICIO) {
            $tabla->accion = new stdClass();
            $tabla->accion->editar = true;
            $tabla->accion->metodoEditar = 'addCompromiso';
            $tabla->accion->prm = ['cmp_id'];
        }

        $tabla->id = 'tbl_Compromisos';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->concepto = '{$("#concepto").val()}';
        $tabla->comunica->importe = '{$("#importe").val()}';
        $tabla->comunica->estado = '{$("#estado").val()}';
        $tabla->comunica->cmp = '{$("#cmp").val()}';
        $tabla->comunica->mes = '{$("#mes").val()}';
        // Marcos: 08/mar/2018
        // Agregamos algunos filtros
        $tabla->comunica->filtroRfc = '{$("#filtroRfc").val()}';
        $tabla->comunica->filtroOrden = '{$("#filtroOrden").val()}';
        $tabla->comunica->chkGraph = '{ ( $("#graficoSiNo").prop("checked" ) ? 1 : 0 ) }';
        $tabla->comunica->metodo = 'llenaTablaCompromisos';
        $tabla->comunica->tipo = '{validaTip()}';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [20, 50, 100, 300, 400, 500];
        $tr = [];

        $th = new stdClass();
        $th->texto = 'N° Compro.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha Inicio';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha Fin';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Concepto';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ENCUADRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CVEFIN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Partida';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Subtipo';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Estado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Importe';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Origen';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Rfc';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Orden';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->campoTxt = 'cmp_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_fechaInicio';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_fechaFin';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_concepto';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programatico';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'partida';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ecm_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Origen';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rfc_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_numOrden';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $html = $this->wrPonTabla($tabla);
        $html .= <<<HTML
                    <div class="mb-2"></div>
                    <table class="table table-bordered mb-2">
                        <tr><td class="warning" colspan = "4">CÓDIGO DE COLOR/ESTADO</td><tr>
                        <tr>
                            <td width="200" style="background-color:rgba(8, 255, 18, 0.20); padding:10px;"> Autorizado</td>
                            <td >Significa que el compromiso ya fue autorizado y su importe ha sido comprometido, pero aun no se usa en alguna afectación</td>
                            <td width="200" style="background-color:rgba(255, 0, 0, 0.20); padding:10px;">Cancelado</td>
                            <td >Significa que el compromiso fue cancelado y su importe no debería afectar el presupuesto Comprometido</td>
                        </tr>
                        <tr>
                            <td width="200" style="background-color:rgba(205, 88, 31, 0.20); padding:10px;">Parcialmente Devengado</td>
                            <td >Significa que una parte del importe del compromiso ya fue Devengado en alguna afectación</td>
                            <td width="200" style="background-color:rgba(85, 50, 118, 0.15); padding:10px;">Devengado</td>
                            <td >Significa que la totalidad del compromiso ya fue Devengado en una o mas Afectaciones</td>
                        </tr>
                    </table>
                HTML;
        $html .= '<script type="text/javascript" languaje="javascript" src="js/jma_viewer.js"></script>';
        $this->mixHtml($html);

        $graficoSiNo = '<div class="me-1"><input class="me-2" id="graficoSiNo" type="checkbox" title="¿Deseas mostrar los gráficos presupuestales en el compromiso?"  checked>¿Gráficos?</div>';

        $botones = $this->ponbotonesmain();
        $this->agregaTools($botones . $graficoSiNo);
        $this->mixScript(
            <<<JS
                {$script}
                function show(id,id_name){
                    if(!$("#"+id).hasClass("tcl_ida")){
                        $(".tcl_idfont").removeClass("tcl_ida");
                        $(".tcl_idfont").removeClass("loadingclas");
                        $(".tcl_idfont").addClass("tcl_idn");
                        $(".tcl_idfont").removeAttr("checked");
                        $("#"+ id).removeClass("tcl_idn");
                        $("#"+id).addClass("loadingclas");
                        $("#"+id).addClass("tcl_ida");
                        {$this->wrLlamaLlenaTabla('tbl_Compromisos')}
                    }
                }
                $(".tcl_idfont").click(function(){
                    if($(this).hasClass("tcl_idb"))
                    {
                        notify("Este tipo de Compromiso no esta disponible","error");
                    }
                    else{ show($(this).attr("id"),$(this).attr("nombre")); }
                });

                $("#btnConsulta").click(function(){{$this->wrLlamaLlenaTabla('tbl_Compromisos')}});
            JS
        );

        if ($this->prm->cmp != '') {
            $this->mixScript($this->wrLlamaLlenaTabla('tbl_Compromisos'));
        }
        if (isset($this->prm->tipo)) {
            $this->mixScript('comunica("' . $this->pantalla . '",{
                metodo:"llenaTablaCompromisos",
                tipo:"' . $this->prm->tipo . '"
            });');
        }
    }

    private function llenaTablaCompromisos()
    {
        $wf = ' WHERE cmp_id  IS NOT NULL ' . PHP_EOL;
        $wh = '';

        $mes = isset($this->prm->mes) ? $this->prm->mes : '';
        $estado = isset($this->prm->estado) ? $this->prm->estado : '';
        $cmp = isset($this->prm->cmp) ? $this->prm->cmp : '';
        $concepto = isset($this->prm->concepto) ? $this->prm->concepto : '';
        $this->prm->importe = (isset($this->prm->importe)) ? $this->prm->importe : '';
        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';

        // Marcos: 08/mar/2018
        // Agregamos algunos filtros
        $filtroRfc = isset($this->prm->filtroRfc) ? $this->prm->filtroRfc : '';
        $filtroOrden = isset($this->prm->filtroOrden) ? $this->prm->filtroOrden : '';
        $params = [$this->gpo_id, $this->ur_id];
        $wf .= ' AND a.gpo_id = ? AND a.ur_id = ?' . PHP_EOL;
        $paramswf = [$this->gpo_id, $this->ur_id];
        $paramswh = [];
        if ($mes != '' && $mes > 0) {
            $wf .= ' AND MONTH(a.usr_fecha) = ? ' . PHP_EOL;
            array_push($paramswf, $mes);
        }

        if ($estado != '') {
            $wf .= 'AND  ecm_id = ?' . PHP_EOL;
            array_push($paramswf, $estado);
        }

        if ($this->prm->importe != '') {
            $this->prm->importe = str_replace(',', '', $this->prm->importe);
            $wf .= 'AND  cmp_importe = ?' . PHP_EOL;
            array_push($paramswf, $this->prm->importe);
        }

        if ($cmp != '') {
            $wf .= ' AND cmp_id = ?' . PHP_EOL;
            array_push($paramswf, $cmp);
            $wh = ' AND  cmp_cmp_id = ?' . PHP_EOL;
            array_push($paramswh, $cmp);
        }

        if (strlen($concepto) > 0) {
            $wf .= 'AND cmp_concepto LIKE  ? ' . PHP_EOL;
            array_push($paramswf, '%' . $concepto . '%');
        }

        if ($this->prm->tipo != '') {
            $wf .= 'AND tcl_id = ?' . PHP_EOL;
            array_push($paramswf, $this->prm->tipo);
        }

        // Marcos: 08/mar/2018
        // Agregamos algunos filtros
        if ($filtroRfc != '') {
            $wf .= 'AND rfc_id = ?' . PHP_EOL;
            array_push($paramswf, $filtroRfc);
        }
        if ($filtroOrden != '') {
            $wf .= 'AND cmp_numOrden = ?' . PHP_EOL;
            array_push($paramswf, $filtroOrden);
        }

        $this->conectar();
        $sql = '
            SELECT
                cmp_id,
                a.gpo_id,a.ur_id,epg_id,ecf_id,
                tcl_id,tcl_nombre,
                stc_id,smn_id,stc_nombre,
                cmp_concepto,
                DATE_FORMAT( cmp_fechaInicio,"%d/%m/%Y") AS cmp_fechaInicio,
                cfi_clave,
                CONCAT(a.gpo_id,a.ur_id, "-",ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,
                pg_id,pe_id,
                CONCAT(pg_id,"-",pe_id) AS partida,
                tcp_id,ue_id,
                DATE_FORMAT( cmp_fechaFin,"%d/%m/%Y") AS cmp_fechaFin ,
                ecm_id,ecm_nombre,
                IF(ISNULL(percepciones) AND ISNULL(retenciones)
                    ,cmp_importe
                    ,cmp_importe + percepciones-retenciones) AS cmp_importe,
                    cmp_tipoCompromiso,a.usr_fecha,
                IF(a.tem_carga =""
                    ,"CAPTURA"
                    ,"CARGA MASIVA") AS Origen,
                IF(ecr_id IN( 100,99 ) OR ecr_id IS NULL ,1,0) AS ecr_id,
                "CARGA MASIVA" AS mensaje, a.rfc_id, a.cmp_numOrden
            FROM scp_Compromiso a
                INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN enc_Programatico USING (gpo_id,ur_id,epg_id)
                INNER JOIN cat_TipoClc USING (tcl_id)
                INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                INNER JOIN cat_EstadoCompromiso USING (ecm_id)
                LEFT  JOIN ( SELECT
                        gpo_id,ur_id,
                        cmp_cmp_id as cmp_id,
                        SUM(if(cmp_tipoMovimiento="-"
                            ,cmp_importe
                            ,0)) AS retenciones,
                        SUM(if(cmp_tipoMovimiento="+"
                            ,cmp_importe
                            ,0)) AS percepciones
                    FROM scp_Compromiso
                    WHERE gpo_id = ?
                        AND ur_id= ?
                        ' . $wh . '
                        AND ecm_id NOT IN ("G","C")
                        AND cmp_cmp_id IS NOT NULL
                    GROUP BY gpo_id,ur_id,cmp_cmp_id
                )ajustes USING (gpo_id,ur_id,cmp_id)
                LEFT JOIN cat_CargaMasiva USING (tem_carga )' . PHP_EOL
            . $wf . '
                AND cmp_tipoCompromiso != "A"
            ORDER BY gpo_id,ur_id,cmp_id DESC ,usr_fecha
        ';
        $params = array_merge($params, $paramswh, $paramswf);
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $nav->idTabla = 'tbl_Compromisos';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }

        $this->mixScript(
            <<<JS
                $(".autorizar").click(function() {
                    comunica({$this->pantalla},{
                    metodo:"GraficaPresupuesto",
                        cmp_id:$(this).attr("cmp_id"),
                        ecm_id:$(this).attr("ecm_id"),
                        epg_id:$(this).attr("epg_id"),
                        ecf_id:$(this).attr("ecf_id"),
                        pg_id:$(this).attr("pg_id"),
                        pe_id:$(this).attr("pe_id"),
                        tipo:$(this).attr("tcl_id"),
                        cmp_importe:$(this).attr("cmp_importe")
                    });
                    return false;
                });
                $(".tcl_ida").removeClass("loadingclas");
        JS
        );
    }

    public function addCompromiso()
    {
        /*$this->setContenedor('det_fsaddcompromiso');*/
        $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";
            $("#viewer_extra").append($("#clasAdminSeccionCombos"));
            $("#encProg").attr("graph", "' . (isset($this->prm->chkGraph) ? $this->prm->chkGraph : '1') . '");
        ');

        // @Jordy 26-11-2018 se verifico con Japhet con respecto al tratamiento de las princesas
        // se corrigio la validacion para que llenaTablaCompromisosbtno agreguen el mno_id en las princesas
        $grupo = $this->princesa ? '0' : '1';
        $ur = $this->ur_id;
        $ur_clave = $grupo . $ur;
        $urgpo = $this->gpo_id . $this->ur_id;

        $mostrar = !in_array($urgpo, $this->grupo2);

        $this->prm->cmp_importe = (isset($this->prm->cmp_importe)) ? $this->prm->cmp_importe : 0;
        $this->prm->cmp_id = (isset($this->prm->cmp_id)) ? $this->prm->cmp_id : '';
        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : $this->prm->tcl_id;

        $ecm2 = '';
        $tipoCompromiso = '';
        $bandera = false;
        $banderaEliminar = false;
        $banderaCancelar = false;
        $banderaAjuste = false;
        $abc = '';
        $cmp = new Compromiso();

        if (!empty($this->prm->cmp_id)) {
            $cmp->setCmp_id($this->prm->cmp_id);
            $cmp->setGpo_id($this->gpo_id);
            $cmp->setUr_id($this->ur_id);
            $cmp->select();
            $this->prm->tpd_id = $cmp->getTpd_id();
            $this->prm->stc_id = $cmp->getStc_id();
            $this->prm->tcl_id = $cmp->getTcl_id();
            $this->prm->tipo = $cmp->getTcl_id();
            $abc = 'c';
            $EstadoCompromiso = $cmp->getEcm_id();
            $tipoCompromiso = $cmp->getTipoCompromiso();
            $compromiso = $this->model->getDatosCompromiso($this->prm->cmp_id);
            $ecm2 = $cmp->getEcm_id();

            if ($compromiso['Usado'] == 'SI') {
                $this->mixScript('
                notify("El compromiso ' . $this->prm->cmp_id
                    . ' no puede ser editado ya esta vinculado a una afectación Presupuestal","error");');
            }
            switch ($EstadoCompromiso) {
                case 'G':
                    $bandera = true;
                    $banderaEliminar = true;
                    if ($tipoCompromiso == 'N') {
                        $this->mixScript(' $(".habilitar").attr("disabled",false); ');
                    } else {
                        $this->mixScript(' $(".habilitar").attr("disabled","disabled"); ');
                        $bandera = false;
                    }
                    break;
                case 'A':
                    if ($compromiso['Usado'] == 'SI') {
                        $this->mixScript('$(".habilitar").attr("disabled","disabled"); ');
                        if ($tipoCompromiso == 'N') {
                            $banderaAjuste = true;
                        } else {
                            $banderaAjuste = false;
                        }
                        $bandera = false;
                    } else {
                        $banderaCancelar = true;
                        $this->mixScript('$(".habilitar").attr("disabled","disabled"); ');
                    }
                    break;
                case 'P':
                    if ($compromiso['Usado'] == 'SI') {
                        $this->mixScript('$(".habilitar").attr("disabled","disabled"); ');
                        if ($tipoCompromiso == 'N') {
                            $banderaAjuste = true;
                        } else {
                            $banderaAjuste = false;
                        }
                        $bandera = false;
                    } else {
                        if ($tipoCompromiso == 'N') {
                            $banderaAjuste = true;
                        } else {
                            $banderaAjuste = false;
                        }
                        $this->mixScript('$(".habilitar").attr("disabled","disabled"); ');
                    }
                    break;
                case 'D':
                    if ($tipoCompromiso == 'N') {
                        $banderaAjuste = true;
                    } else {
                        $banderaAjuste = false;
                    }
                    break;
            }
        } else {
            $cmp->setCmp_id(0);
            $abc = 'a';
            $bandera = true;
        }
        // Marcos: 18/ene/2018
        // Para Deuda Pública... usuario 116 - DEUDAPUB que es de nivel UE...
        // la llamada a getUe_id()  pone en vacío el valor de UE
        // Por lo que - cuando sea un usuario de nivel UE, NO llamaremos a la función, ya que
        // sólo tienen una y ésa ya la traen en la variable de sesión
        if ($this->nvj == 1) {
            // No hacemos nada, porque la UE de su compromiso será exactamente igual a la que
            //  debería retornar la entidad
        } else {
            $this->clasAdmUe = $cmp->getUe_id();
        }
        $this->prm->abc = $abc;
        switch ($this->prm->tipo) {
            case 'F':
            case 'G':
                $this->mixScript('
                    $("#encProg").attr("disabled","disabled");
                    $("#encCveFin").attr("disabled","disabled");
                ');
                break;
        }

        /* CONTROLES A USAR EN EL FORMULARIO  */
        /* SUB TIPO DE CLC */
        $prm = [
            'tipo'        => $this->prm->tipo,
            'cmp_id'      => $this->prm->cmp_id,
            'cmp_importe' => $this->prm->cmp_importe,
        ];

        $subtipo = $this->vista->getComboSubtipoClc($prm);
        if ($cmp->getStc_id() != '') {
            $subtipo->selecciona = $cmp->getStc_id();
        }
        $subtipo = $this->ponCombo($subtipo);

        $modalidad = $this->vista->getComboModalidad($prm);

        if ($cmp->getMno_id() != '') {
            $modalidad->sql = '
                SELECT
                    mno_id,mno_nombre
                FROM cat_ModalidadNomina
                INNER JOIN enc_ModalidadSubtipoClc USING(mno_id)
                WHERE tcl_id = ?
                    AND stc_id = ?
                    ' . ($ur_clave != 104 ? ' AND mno_id != ? ' : '');

            $params = [$this->prm->tipo, $cmp->getStc_id()];
            $ur_clave != 104 ? array_push($params, 6) : '';
            $modalidad->params = $params;
            $modalidad->selecciona = $cmp->getMno_id();
            $modalidad = $this->ponCombo($modalidad);
        } else {
            $modalidad = $this->templateCombo($modalidad);
        }

        /* TIPO DE NOMINA  */
        $tipoNom = $this->vista->getComboVarios('tipoNom');
        $tipoNom->selecciona = $cmp->getTno_id();

        /* SUMMODALIDAD DE HABERES */
        $submod = $this->vista->getComboVarios('submod');
        $submod->selecciona = $cmp->getSmn_id();

        /* FECHA DE INICIO */
        $inicioFecha = $this->vista->getComboVarios('inicioFecha');
        $inicioFecha->value = $cmp->getFechaInicio();

        /* FECHA DE FIN */
        $finFecha = $this->vista->getComboVarios('finFecha');
        $finFecha->value = $cmp->getFechaFin();

        /* FECHA DE LA ORDEN */
        $FechaOrden = $this->vista->getComboVarios('FechaOrden');
        $FechaOrden->value = $cmp->getFechaOrden();

        /* NUMERO DE PEDIDO U ORDEN */
        $ordenPedido = $this->vista->getComboVarios('ordenPedido');
        $ordenPedido->value = $cmp->getNumOrden();

        $proveedorRfc = new stdClass();
        $proveedorRfc->id = 'rfc_id';
        $proveedorRfc->placeholder = 'F1: Catalogo';
        $proveedorRfc->required = true;
        $proveedorRfc->maxlength = 20;
        $proveedorRfc->class = 'F1Catalogo habilitar';
        $proveedorRfc->value = $cmp->getRfc_id();

        $nomProv = new stdClass();
        $nomProv->id = 'nomProv';
        $nomProv->placeholder = 'Nombre/Denominación';
        $nomProv->lectura = true;

        /* CONCEPTO DE PAGO  */
        $concepto = $this->vista->getComboVarios('concepto');
        $concepto->text = $cmp->getConcepto();

        /*   IMPORTE DEL COMPROMISO  */
        $importe = $this->vista->getComboVarios('importe');
        $importe->value = $cmp->getImporte();
        $importe = $this->ponInput($importe);

        /* INPUT DE OTRO DOCUMENTO */
        $otroDoc = $this->vista->getComboVarios('otroDoc');
        $otroDoc->value = $cmp->getOtroDocumento();
        $otroDoc = $this->ponInput($otroDoc);

        /* TIPO DE CONTRATO   */
        $contrato = $this->vista->getComboContrato($cmp);

        if ($cmp->getTpd_id() != '' && $cmp->getNumeroContrato() != '') {
            $contrato = $this->ponCombo($contrato);
        } else {
            $contrato = $this->templateCombo($contrato);
        }
        /* FIN TIPO CONTRATO */

        /* TIPO DOCUMENTO CAT_TIPODOCUMENTO PARA MOMENTOS CONTABLES UNI 17 Y 18 */

        $comboDoc = $this->vista->getComboDoc($this->prm->tipo, $cmp);
        $comboDoc->change = $this->vista->getScriptcomboDoc($this->prm->tipo);

        if ($cmp->getTpd_id() != '' || $this->prm->tipo == 'F' || $this->prm->tipo == 'G') {
            $comboDoc = $this->ponCombo($comboDoc);
        } else {
            $comboDoc = $this->templateCombo($comboDoc);
        }

        /* BOTONES */
        $ajuste = '';
        $cancelar = '';
        $eliminar = '';
        $btnGuardar = '';

        /* EXTRAS */
        $orden = '';
        $nomina = '';
        $periodos = '';
        $TipoCompra = '';

        /* BOTON DE ELIMINAR  */
        if ($banderaEliminar == true && $abc != 'a') {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $cmp->getUsr_id()) {
                    $eliminar = $this->vista->vwSoloLectura(' ELIMINAR:EL COMPROMISO ES DE OTRO USUARIO');
                } else {
                    $prm = ['cmp' => $cmp->getCmp_id(), 'tipo' => $this->prm->tipo];
                    $elb = $this->vista->getBotonesVarios('btnEliminar', $prm);
                    $eliminar = $this->ponBoton($elb);
                }
            } else {
                $eliminar = $this->vista->vwSoloLectura('ELIMINAR:SOLO LECTURA');
            }
        }

        /* BOTON DE CANCELAR */
        if ($banderaCancelar == true && $abc != 'a') {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $cmp->getUsr_id()) {
                    $cancelar = $this->vista->vwSoloLectura(' CANCELAR:EL COMPROMISO ES DE OTRO USUARIO');
                } else {
                    $prm = ['cmp' => $cmp->getCmp_id(), 'tipo' => $this->prm->tipo];
                    $btnCancelar = $this->vista->getBotonesVarios('btnCancelar', $prm);
                    $cancelar = $this->ponBoton($btnCancelar);
                }
            } else {
                $cancelar = $this->vista->vwSoloLectura('CANCELAR:SOLO LECTURA');
            }
        }

        /* BOTON DE AJUSTAR  */
        if ($banderaAjuste == true && $abc != 'a') {
            if ($this->escritura) {
                if ($this->nvj == 1 && $this->usr_id != $cmp->getUsr_id()) {
                    $ajuste = $this->vista->vwSoloLectura('AJUSTAR:EL COMPROMISO ES DE OTRO USUARIO');
                } else {
                    $prm = ['cmp' => $this->prm->cmp_id];
                    $btnAjuste = $this->vista->getBotonesVarios('btnAjuste', $prm);
                    $ajuste = $this->ponBoton($btnAjuste);
                }
            } else {
                $ajuste = $this->vista->vwSoloLectura('AJUSTE:SOLO LECTURA');
            }
        }

        /* ################################################################################### */
        /* ################################################################################### */
        /* BOTON DE GUARDAR  CON VALIDACIONES */
        if ($bandera == true) {
            if ($this->escritura) {
                $prm = [
                    'tipo'   => $this->prm->tipo,
                    'abc'    => $abc,
                    'cmp_id' => $this->prm->cmp_id,
                    'smn_id' => $cmp->getSmn_id(),
                    'grupo'  => $grupo,
                ];
                if ($abc == 'c') {
                    if ($this->nvj == 1 && $this->usr_id != $cmp->getUsr_id()) {
                        $btnGuardar = $this->vista->vwSoloLectura(' EL COMPROMISO ES DE OTRO USUARIO');
                    } else {
                        $btnGuardar = $this->vista->getBotonGuardar($prm);
                        $btnGuardar = $this->ponBoton($btnGuardar);
                    }
                } else {
                    $btnGuardar = $this->vista->getBotonGuardar($prm);
                    $btnGuardar = $this->ponBoton($btnGuardar);
                }
            } else {
                $btnGuardar = $this->vista->vwSoloLectura('GUARDAR:SOLO LECTURA');
            }
        }
        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresar->class = 'btn btn-outline-secondary me-1';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"main",
                tipo:"' . $this->prm->tipo . '"
            });

        ';
        $btnRegresar = is_object($btnRegresar) ? $this->ponBoton($btnRegresar) : '';

        $this->agregaTools($btnRegresar . $btnGuardar . $eliminar . $cancelar . $ajuste);

        $filtro = '';
        $paso = '0';
        $filtroObr = '';
        $toa_id = '';
        switch ($this->prm->tipo) {
            case 'F':
                $filtro = 'enc_InversionObra';
                $filtroObr = '($("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO DE OBRA") ? $("#ipu_id").val(): "" ';
                $paso = '($("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO DE OBRA") ? 1 : 2 ';
                $toa_id = '2';
                break;
            case 'G':
                $filtro = 'enc_InversionObra';
                $filtroObr = '($("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO") ? $("#ipu_id").val(): "" ';
                $paso = '($("#tpd_id").find("option:selected").attr("tpd_nombre") =="CONTRATO") ? 1 : 2 ';
                $toa_id = '2';
                break;
            default:
                $filtro = 'porUrClaveFinanciamiento';
                break;
        }

        /* CLASIFICACION ADMINISTRATIVA  */
        $extras = null;
        if ($abc == 'c') {
            $sql = '
                SELECT
                    prg_id,spr_id,py_id,oa_id
                FROM enc_Programatico
                WHERE epg_id = ?';
            $params = [$cmp->getEpg_id()];
            $datos = $this->model->getDatos($sql, false, $params);
            $extras = $datos['datos'][0];
        }
        $cveAdmitiva = $this->vista->getClasificacionAdministrativa($this->prm->tipo, $abc, $extras);
        $cveAdmitiva->ue_id = $cmp->getUe_id();

        unset($extras);
        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));

        $html = '';
        switch ($this->prm->tipo) {
            case 'F':
            case 'G':
                $html .= '
                    <legend align="center" style="color:#FF4000">Contrato</legend><br>
                    <div class="row">
                        <fieldset class="col-md-4 col-xs-4">
                            ' . $comboDoc . '
                        </fieldset>
                        <fieldset class="col-md-8" col-xs-8 id="otro" style="display:' . ($cmp->getOtroDocumento() != '' ? 'block' : 'none') . '">
                            ' . $otroDoc . '
                        </fieldset>
                        <fieldset class="col-md-4" col-xs-4 id="contratos" style="display:' . ($cmp->getIpu_id() != 0 ? 'block' : 'none') . '">
                            ' . $contrato . '
                        </fieldset>
                    </div>
                    <br>
                ';
                break;
        }

        /**
         * IMPLEMETACIÓN DE CAMBIO DE SELECCION DE ENCUADRE PROGRAMATICO Y CLAVE DE FINANCIAMIENTO
         * A MODAL CON PON TABLA
         */
        $progFin = new stdClass();
        $progFin->filtro = $filtro;
        $progFin->paso = $paso;
        $progFin->filtroObr = $filtroObr;
        $progFin->toa_id = $toa_id;

        if ($this->prm->abc === 'c') {
            $progFin->epg_id = $cmp->epg_id->getVal();
            $progFin->ecf_id = $cmp->ecf_id->getVal();
        } else {
            $progFin->onSelect = $this->getOnSelectJs($this->prm);
            $progFin->cvefinBlur = $this->getOnSelectJs($this->prm);
        }
        $html .= CatalogoProgramaticoFinanciamiento::ponF1($progFin);

        switch ($this->prm->tipo) {
            case 'A':
                $nomina .= '
                    <legend align="center" style="color:#FF4000" class="mb-2">Datos Nomina</legend>
                        <div class="row mb-2">
                            <fieldset class="col-md-6 col-xs-6 mb-2">
                                ' . $subtipo . '
                            </fieldset>';
                if ($mostrar == true) {
                    $nomina .= '
                        <fieldset class="col-md-6 mb-2" col-xs-6 id="mod">
                            ' . $modalidad . '
                        </fieldset>';
                }

                $nomina .= '
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponCombo($tipoNom) . '
                    </fieldset>';

                if ($mostrar == true && $ur_clave == 304) {
                    $nomina .= '
                        <fieldset class="col-md-6" col-xs-6 id="submod" style="display:' . ($cmp->getSmn_id() != '' && $cmp->getSmn_id() != 0 ? 'block' : 'none') . '">
                            ' . $this->ponCombo($submod) . '
                        </fieldset>';
                }

                $nomina .= '</div>';

                $periodos .= '
                    <legend align="center" style="color:#FF4000" class="mb-2">Periodos y datos del Proveedor</legend>
                    <div class="row mb-2">
                        <fieldset class="col-md-4 col-xs-4">
                            ' . $comboDoc . '
                        </fieldset>
                        <fieldset class="col-md-4" col-xs-4 id="otro" style="display:' . ($cmp->getOtroDocumento() != '' ? 'block' : 'none') . '">
                            ' . $otroDoc . '
                        </fieldset>
                        <fieldset class="col-md-2 col-xs-2">
                            ' . $this->ponInput($inicioFecha) . '
                        </fieldset>
                            <fieldset class="col-md-2 col-xs-2">
                            ' . $this->ponInput($finFecha) . '
                        </fieldset>
                    </div>';
                break;
            case 'B':
                $TipoCompra .= '
                    <div class="row">
                        <fieldset class="col-md-4 col-xs-4" id="compra">
                        </fieldset>
                    </div>';

                $orden .= '
                    <div class="row">
                        <fieldset class="col-md-4 col-xs-4">
                            ' . $comboDoc . '
                        </fieldset>
                        <fieldset class="col-md-3" col-xs-3 id="otro" style="display:' . ($cmp->getOtroDocumento() != '' ? 'block' : 'none') . '">
                            ' . $otroDoc . '
                        </fieldset>
                        <fieldset class="col-md-2 col-xs-2">
                            ' . $this->ponInput($ordenPedido) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-3">
                            ' . $this->ponInput($FechaOrden) . '
                        </fieldset>
                    </div>
                    <div class="row">
                    </div>';
                break;
            case 'F':
            case 'G':
                $orden .= '
                    <div class="row">
                        <fieldset class="col-md-3 col-xs-3">
                            ' . $this->ponInput($ordenPedido) . '
                        </fieldset>
                        <fieldset class="col-md-3 col-xs-3">
                            ' . $this->ponInput($FechaOrden) . '
                        </fieldset>
                    </div>';
                break;
            case 'I':
            case 'J':
            case 'K':
            case 'L':
                $nomina .= '
                    <legend align="center" style="color:#FF4000">Datos Nomina</legend><br>
                    <div class="row">
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $subtipo . '
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $comboDoc . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-12" col-xs-12 id="otro" style="display:' . ($cmp->getOtroDocumento() != '' ? 'block' : 'none') . '">
                            ' . $otroDoc . '
                        </fieldset>
                    </div>';
                break;
        }

        $html .= '
            <form id="com">
                ' . $nomina . '
                <div class="row row mb-2">
                    <fieldset class="col-md-12 col-xs-12" id="tr_objeto">
                    </fieldset>
                </div>
                ' . $TipoCompra . '
                <div class="row row mb-2">
                    <fieldset class="col-md-12 col-xs-12" id="tr_grafica">
                    </fieldset>
                </div>
                ' . $periodos . '
                <div class="row mb-2">
                    <fieldset class="col-md-12 col-xs-12">
                        <label class="mb-2">R.F.C del Proveedor Beneficiario o Institución</label>
                        <div class="row mb-2">
                            <fieldset class="col-md-4 col-xs-4">
                                ' . $this->ponInput($proveedorRfc) . '
                            </fieldset>
                            <fieldset class="col-md-8 col-xs-8">
                                ' . $this->ponInput($nomProv) . '
                            </fieldset>
                        </div>
                    </fieldset class="col-md-12 col-xs-12">
                </div>
                ' . $orden . '
                <legend align="center" style="color:#FF4000">Concepto e Importe</legend><br>
                <div class="row">
                    <fieldset class="col-md-6 col-xs-6" >
                    ' . $this->ponTextarea($concepto) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $importe . '
                    </fieldset>
                    <br>
                </div>
                <div id="contenedorCFModal"></div>
                <br><br>
        ';

        if ($tipoCompromiso == 'N' || (isset($this->prm->es) and $this->prm->es == 'ajuste')) {
            if (isset($ecm2) && $ecm2 != '' && $ecm2 != 'G' && $ecm2 != 'C') {
                $html .= '
                    <legend align="center" style="color:#FF4000">Ajustes realizados al Compromiso # ' . $this->prm->cmp_id . '</legend><br>
                    <div class="row">
                        <fieldset class="col-md-12 col-xs-12">
                            <div  id="listaObras">
                                ' . $this->DetalleCompromiso() . '
                            </div>
                        </fieldset>
                    </div>
                ';
            }
        }

        $INFOCOM = '';

        if ($tipoCompromiso == 'N') {
            $INFOCOM = ' EDICI&Oacute;N DEL COMPROMISO # ' . $this->prm->cmp_id;
            $datos = $this->model->getUsoCompromiso($this->prm->cmp_id);
            $html .= '<div class="col-md-12" style="overflow-x: auto">
                <legend>Devengado del compromiso </legend>';
            $html .= $this->model->fn_pintaTabla($datos);
            $html .= '</div>';
        } else {
            $tp = $cmp->getTipoMovimiento() == '+' ? 'AMPLIACI&Oacute;N' : 'REDUCCI&Oacute;N';
            $INFOCOM = 'EDICI&Oacute;N DEL COMPROMISO DE AJUSTE # ' . $this->prm->cmp_id
                . '<span style="color:#888">( ' . $tp . ' DEL COMPROMISO N°' . $cmp->getCmp_cmp_id() . ')</span>';
        }
        if ($abc == 'c') {
            $this->agregaTools($btnGuardar . ' ' . $eliminar . ' ' . $cancelar . ' ' . $ajuste . ' ' . $INFOCOM . '', 'addcompromisotoolsviewer');
        } else {
            $this->agregaTools($btnGuardar . ' ' . $eliminar . ' ' . $cancelar . ' ' . $ajuste, 'addcompromisotoolsviewer');
        }

        $this->mixHtml($html);
        $deshabilitarCampos = (($this->nvj >= 3) ? '$(".deshabilitar").attr("disabled",true);' : '');
        $u = '';
        $g = '';
        $metodoRegresar = '';
        $varRegreso = '';

        if (isset($this->prm->comp) && $this->prm->comp != '' && $this->prm->comp == 'ajuste' && $this->prm->original != '') {
            $metodoRegresar = ' metodo:"addCompromiso", ';
            $varRegreso = ' "cmp_id":"' . $this->prm->original . '", ';
            $g = ' "gpo_id":"' . $this->prm->gpo_id . '", ';
            $u = ' "ur_id":"' . $this->prm->ur_id . '", ';
        } else {
            $metodoRegresar = ' metodo:"main", ';
            $varRegreso = ' "cmp_id":"' . $this->prm->cmp_id . '", ';
        }

        $miniScript = '';

        if (isset($abc) && $abc == 'a') {
            $miniScript = '
                $("#rfc_id").blur(function(){
                    if ($(this).length != 0 || ("' . $this->prm->tipo . '" == "F" && $("#ipu_id").val() == "")
                        || ("' . $this->prm->tipo . '" == "G" && $("#ipu_id").val() == "")) {
                        comunica("' . $this->pantalla . '",{
                            metodo:"Proveedor",
                            rfc:$(this).val(),
                            abc:"' . $abc . '"
                        });
                    }
                });
            ';
        } elseif (isset($ecm2) && $ecm2 == 'G') {
            $miniScript = '
                $("#rfc_id").blur(function(){
                    if ($(this).length != 0 || ("' . $this->prm->tipo . '" == "F" && $("#ipu_id").val() == "")
                        || ("' . $this->prm->tipo . '" == "G" && $("#ipu_id").val() == "")) {
                        comunica("' . $this->pantalla . '",{
                            metodo:"Proveedor",
                            rfc:$(this).val(),
                            abc:"' . $abc . '"
                        });
                    }
                });
            ';
        }

        $this->mixScript('
            $(document).ready(function(){
                if ($("#rfc_id").val() != ""){
                    console.log ($("#rfc_id").val());
                        comunica( "' . $this->pantalla . '", {
                        "metodo" : "DameNombreProveedor",
                        "rfc": $("#rfc_id").val()
                    });
                }
            });

            $("#rfc_id,#addConcepto,#otroDoc,#ordenPedido").keyup(function(){
                this.value = this.value.toUpperCase();
            });

            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    ' . $metodoRegresar . '
                    ' . $varRegreso . '
                    ' . $g . '
                    ' . $u . '
                    "tipo":"' . $this->prm->tipo . '"
                });
            });

            ' . $miniScript . '

            $("#rfc_id").focus( function(e) {
                rfcCampo = "rfc_id"; // id de cmapo de texto donde va el rfc
                rfcBeneficiario = "nomProv"; //id dee cmapo de texto donde va a poner el nombre
            });

            ' . $this->ponF1Proveedores('rfc_id', [], 'nomProv') . '

            $("#addImporte").blur(function(){
                var disponible = parseFloat(unformatNumber($("#disponible").text()).toFixed(2));
                var capturado  = parseFloat(unformatNumber($("#importe").val()).toFixed(2));
                if(disponible<capturado){
                    notify( "El importe no puede ser mayor que el disponible ", "error");
                    $("#importe").val("");
                }
            });

            ' . $deshabilitarCampos . '

            function validarFechas(fechaInicio, fechaTermino){
                var arregloFechaInicio = fechaInicio.split("/");
                var arregloFechaTermino = fechaTermino.split("/");

                var anioFechaInicio = arregloFechaInicio[2];
                var mesFechaInicio = arregloFechaInicio[1];
                var diaFechaInicio = arregloFechaInicio[0];

                var anioFechaTermino = arregloFechaTermino[2];
                var mesFechaTermino = arregloFechaTermino[1];
                var diaFechaTermino = arregloFechaTermino[0];

                if(anioFechaInicio < anioFechaTermino || anioFechaInicio == anioFechaTermino){
                    return true;
                }else if(anioFechaInicio > anioFechaTermino){
                    return false;
                }else{
                    if(mesFechaInicio < mesFechaTermino || mesFechaInicio == mesFechaTermino){
                        return true;
                    }else if(mesFechaInicio > mesFechaTermino){
                        return false;
                    }else{
                        if(diaFechaInicio < diaFechaTermino){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }

            }
        ');

        if ($abc == 'c') {
            if ($this->prm->tipo != 'B') {
                $this->prm->stc_id = $cmp->getStc_id();
                $this->prm->mno_id = $cmp->getMno_id();
            }

            $this->prm->tpd_id = $cmp->getTpd_id();
            $this->prm->otroDoc = $cmp->getOtroDocumento();
            $this->prm->filtro = 'ModNomPartida';
            $this->prm->abc = 'c';
            $this->ObjetoGasto();
        }
    }

    private function comboNomina()
    {
        $ur = $this->gpo_id2 . $this->ur_id2;
        $wh = '';
        if ($ur != '304') {
            $wh = 'AND mno_id!=6';
        } else {
            $wh = '';
        }
        $combo = new stdClass();
        switch ($this->prm->id) {
            case 1:
                $combo->sql = '
                    SELECT
                        smn_id,smn_nombre
                    FROM cat_SubmodalidadNomina
                    WHERE mno_id = ?
                ';
                $combo->params = [$this->prm->mno_id];
                break;
            case 2:
                $combo->sql = '
                    SELECT
                        mno_id,mno_nombre
                    FROM cat_ModalidadNomina
                    INNER JOIN enc_ModalidadSubtipoClc USING (mno_id)
                    WHERE stc_id = ? ' . $wh . '
                ';
                $combo->params = [((empty($this->prm->stc_id)) ? 0 : $this->prm->stc_id)];
                break;
        }
        $combo->comoAtr = 'mno_id';
        $combo->id = $this->prm->combo;
        $this->recargaCombo($combo);
        if ($this->prm->tipo == 'A') {
            if (isset($this->prm->cmp_id) && $this->prm->cmp_id != '') {
                $this->prm->cmp_id;
            }
            $this->DocumentoSoporte();
        }
    }

    private function ObjetoGasto()
    {
        $pg = '';
        $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
        $this->prm->epg_id = ClasificacionAdministrativa::getEpg();

        if (!$this->prm->epg_id) {
            $this->mixScript('notify("Debe Seleccionar una Obra y Clave de Financiamiento", "error");');
            return;
        }
        $this->prm->gpo_id = $this->gpo_id;
        $this->prm->ur_id = $this->ur_id;

        $this->prm->cmp_id = (isset($this->prm->cmp_id)) ? $this->prm->cmp_id : '';
        $this->prm->cmp_importe = (isset($this->prm->cmp_importe)) ? $this->prm->cmp_importe : 0;

        if ($this->princesa) {
            $grupo = 2;
            //PARA LAS UNIDADDES RESPONSABLES DEL GRUPO 2  CUANDO ES NUEVO SE DEBE MANDAR A RECARGAR EL COMBO DE ACUERDO AL SUBTIPO DE LA CLC
            if ($this->prm->cmp_id == '' && $this->prm->tipo != 'G' && $this->prm->tipo != 'H') {
                $this->DocumentoSoporte();
            }
        }
        $cmp = new Compromiso();
        if ($this->prm->cmp_id != '') {

            $cmp->setCmp_id($this->prm->cmp_id);
            $cmp->setGpo_id($this->prm->gpo_id);
            $cmp->setUr_id($this->prm->ur_id);
            $cmp->select();
            $pg = $cmp->getPg_id();
        }

        $objGto = new stdClass();
        $objGto->tipo = $this->prm->tipo;
        $objGto->ecf_id = $this->prm->ecf_id;
        $objGto->epg_id = $this->prm->epg_id;
        $objGto->filtro = $this->prm->filtro;
        $objGto->required = true;
        $objGto->detonar = empty($this->prm->cmp_id);

        if ($this->prm->tipo == 'A') {
            $objGto->stc_id = $this->prm->stc_id;
            $objGto->mno_id = $this->prm->mno_id;
        }
        if (isset($this->prm->mno_id) && $this->prm->mno_id != '') {
            $objGto->mno_id = $this->prm->mno_id;
        }

        if ($pg != '') {
            //CUANDO ES EDICION
            $objGto->lectura = true;
            $objGto->pg_id = $cmp->getPg_id();
            $objGto->pe_id = $cmp->getPe_id();
            $objGto->lectura = true;
            $objGto->detonar = false;

            if ($this->prm->tipo == 'B') {
                $objGto->changePe = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"TipoCompra",
                        ecf_id:"' . $this->prm->ecf_id . '",
                        epg_id:"' . $this->prm->epg_id . '",
                        pg_id:$("#pg_id").val(),
                        graph: $("#encProg").attr("graph"),
                        tcp_id:"' . $cmp->getTcp_id() . '",
                        ' . ($this->prm->cmp_importe > 0 ? 'cmp_importe:' . $this->prm->cmp_importe . ',' : '') . '
                        pe_id:$("#pe_id").val()
                    });
                ';
            } else {
                $objGto->changePe = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"GraficaPresupuesto",
                        ecf_id:"' . $this->prm->ecf_id . '",
                        epg_id:"' . $this->prm->epg_id . '",
                        graph: $("#encProg").attr("graph"),
                        pg_id:"' . $cmp->getPg_id() . '",
                        tcp_id:"' . $cmp->getTcp_id() . '",
                        ' . ($this->prm->cmp_importe > 0 ? 'cmp_importe:' . $this->prm->cmp_importe . ',' : '') . '
                        pe_id:"' . $cmp->getPe_id() . '"
                    });
                ';
            }
        } else {
            // CUANDO ES NUEVO
            if ($this->prm->tipo == 'B') {
                $objGto->changePe = '
                    if( $(this).val() != "" )
                        comunica("' . $this->pantalla . '",{
                            metodo:"TipoCompra",
                            ecf_id:"' . $this->prm->ecf_id . '",
                            epg_id:"' . $this->prm->epg_id . '",
                            graph: $("#encProg").attr("graph"),
                            pg_id:$("#pg_id").val(),
                            ' . ($this->prm->cmp_importe > 0 ? 'cmp_importe:' . $this->prm->cmp_importe . ',' : '') . '
                            pe_id:$("#pe_id").val()
                        });
                ';
            } else {
                $objGto->changePe = '
                    if( $(this).val() != "" )
                        comunica("' . $this->pantalla . '",{
                            metodo:"GraficaPresupuesto",
                            ecf_id:"' . $this->prm->ecf_id . '",
                            graph: $("#encProg").attr("graph"),
                            ' . ($this->prm->cmp_importe > 0 ? 'cmp_importe:' . $this->prm->cmp_importe . ',' : '') . '
                            pg_id:$("#pg_id").val(),
                            pe_id:$("#pe_id").val(),
                            tcp_id:"D"
                    });
                ';
            }
        }

        if (isset($this->prm->abc) && $this->prm->abc == 'c') {
            // Marcos: 09/mar/2018
            // Para permitir seleccionar si se visualiza o no la gráfica
            $graficos = (isset($this->prm->chkGraph) ? $this->prm->chkGraph : '1');

            $objGto->detonar = false;
            $this->prm->pg_id = $cmp->getPg_id();
            $this->prm->pe_id = $cmp->getPe_id();
            $this->prm->tcp_id = $cmp->getTcp_id();
            if ($this->prm->tipo == 'B') {
                $this->TipoCompra($graficos);
            } else {
                $this->GraficaPresupuesto($graficos);
            }
        }
        $this->setContenedor('tr_objeto');
        $html = '
            <div class="row mb-2">
                <fieldset class="col-md-12 col-xs-12">' . ObjetoGasto::ponObjetoGasto($objGto) . '</fieldset>
            </div>
        ';
        $this->mixHtml($html);
        if ($this->prm->tipo != 'A' && $this->prm->tipo != 'F' && $this->prm->tipo != 'G') {
            $this->DocumentoSoporte();
        }
    }

    // Marcos: 09/03/2018
    // Para permitir seleccionar si quieren ver o no las gráficas, por default es 1 = SI
    private function TipoCompra($graficos = '1')
    {
        $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
        $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
        $this->setContenedor('compra');
        $this->conectar();
        $params = [$this->clasAdmGpo, $this->clasAdmUr, $this->prm->epg_id, $this->prm->ecf_id, $this->prm->pg_id, $this->prm->pe_id];
        $sql = '
            SELECT tcp_id FROM presupuesto
            WHERE gpo_id = ?
                AND ur_id = ?
                AND epg_id = ?
                AND ecf_id = ?
                AND pg_id = ?
                AND pe_id = ?
            GROUP BY tcp_id;
        ';

        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $registros = $this->getNumeroRegistros();
        $j = $this->extraer();

        $TipoCompra = new stdClass();
        $TipoCompra->sql = '
            SELECT tcp_id,tcp_nombre FROM cat_TipoCompra
            WHERE tcp_id != "P"
                ' . (($registros > 1) ? ';' : 'AND tcp_id = ?;');
        $registros > 1 ? '' : $TipoCompra->params = [$j['tcp_id']];
        $TipoCompra->id = 'TipoCompra';
        $TipoCompra->name = 'TipoCompra';
        $TipoCompra->enDiv = true;
        $TipoCompra->mensaje = 'Selecciona';
        $TipoCompra->label = 'Tipo de Compra';
        //$TipoCompra->comoAtr = "ur_clave";
        $TipoCompra->class = 'habilitar mb-2';
        $TipoCompra->search = true;
        $TipoCompra->change = '
            if ($(this).val() != "")
                comunica("' . $this->pantalla . '",{
                    metodo:"GraficaPresupuesto",
                    ecf_id:"' . $this->prm->ecf_id . '",
                    graph: $("#encProg").attr("graph"),
                    pg_id:$("#pg_id").val(),
                    pe_id:$("#pe_id").val(),
                    ' . ((isset($this->prm->cmp_importe) && $this->prm->cmp_importe != '') ?
            'cmp_importe:' . $this->prm->cmp_importe . ',' : '') . '
                    tcp_id:$("#TipoCompra").val()
                });
        ';

        if (isset($this->prm->abc) && $this->prm->abc == 'c') {
            $TipoCompra->detonar = false;
            $TipoCompra->selecciona = $this->prm->tcp_id;
            $this->mixHtml($this->ponCombo($TipoCompra));
            // Marcos: 09/mar/2018
            // Para elegir si se dibujan o no las gráficas
            $this->GraficaPresupuesto($graficos);
        } else {
            $this->mixHtml($this->ponCombo($TipoCompra));
        }
    }

    public function AutorizarCompromiso()
    {
        $ip = (isset($this->usuarioIp) && $this->usuarioIp != '::1') ? $this->usuarioIp : '127.0.0.1';
        $user = $this->usr_id;
        $autorizados = 0;

        if (count($this->prm->compromisos) > 0 && $this->prm->compromisos != '') {
            $cm = '';
            $params = [$this->gpo_id, $this->ur_id];
            $sql = '
                SELECT
                    gpo_id,
                    ur_id,
                    cmp_id,
                    tcl_id,
                    stc_id,
                    ue_id,
                    epg_id,
                    pg_id,
                    pg_id,
                    pe_id,
                    ecf_id,
                    tcp_id,
                    ecm_id,
                    cmp_importe,
                    IF(cmp_tipoCompromiso = "A" , IF(cmp_tipoMovimiento = "+" ,"A","R"),"" ) AS movimiento,
                    cmp_tipoCompromiso
                FROM scp_Compromiso
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND cmp_id IN
            ';

            foreach ($this->prm->compromisos as $key => $val) {
                if (isset($val->ajuste)) {
                    $this->prm->ajuste = true;
                }
                if ($cm == '') {
                    $cm .= $val->cmp_id;
                } else {
                    $cm .= ',' . $val->cmp_id;
                }
            }

            $sql .= '(' . $cm . ')';
            //OBTIENE LA INFORMACIÓN DE TODO EL COMPROMISO
            $tcl = '';
            $datos = $this->model->getDatosObj($sql, false, $params);
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $tcl = $this->extraer();
            $registros = 0; // SE UTILIZA EN LA VERIFICACIÓN DE FOLIOS DE ADECUACIÓN
            foreach ($datos['datos'] as $val) {
                /**SE ESTA VERIFICANDO EL BLOQUEO DE COMPROMISO (MODULO DE BLOQUEO DE CLC) POR CADA REGISTRO DE COMPROMISO */
                if (($val->movimiento != '' && $val->movimiento != 'R') || !isset($val->movimiento)) {
                    $bloqueo = $this->model->gtBloqueos($val->tcl_id, $val->stc_id, $val->ecf_id, $this->prm->opcion);
                    if ($bloqueo->valido == 0) {
                        $this->mixScript(' notify("' . $bloqueo->mensaje . ' ","error",false); ');
                        return false;
                    }
                }
                /**SE ESTA VERIFICADO QUE NINGUN FOLIO ADECUACIONES PRESUPUESTALES ESTE EN TRANSITO 08-07-2019 TICKET 550148*/
                if (isset($this->prm->tipo) && ($this->prm->tipo == 'A' or $this->prm->tipo == 'B')) {
                    $clavepresupuestal = $this->prm->compromisos[$registros]->programatico . ' - ' . $this->prm->compromisos[$registros]->partida;
                    //SE MANDAN LOS ID'S CORRESPONDIENTES DE LA CLAVE PRESUPUESTAL, PARTE DE LA CLAVE PRESUPUESTAL EN CADENA DE CARACTERES,
                    //EL TIPO DE ACCIÓN QUE SE ESTA REALIZANDO (COMPROMISO,AUTORIZACIÓN)
                    $tieneFolio = $this->model->getFolioDevengadoComprometido(
                        $val->gpo_id,
                        $val->ur_id,
                        $val->ue_id,
                        $val->epg_id,
                        $val->pg_id,
                        $val->pe_id,
                        $val->ecf_id,
                        $val->tcp_id,
                        $clavepresupuestal,
                        'C',
                        'A'
                    );
                    //EN CASO QUE SE ENCUENTRE ALGÚN FOLIO SE MANDARÁ EL MENSAJE DE ERROR
                    if ($tieneFolio->estado == true) {
                        $this->mixScript('
                            notify("' . $tieneFolio->mensaje . '","error");
                        ');
                        return false;
                    }
                }
                $registros++;
                $modificaciones = 0;
                $disponible = 0;
                $preVigente = $this->model->getPresupuestoVigenteComprometido($val);
                /* SI TIENE DISPONIBLE LA CLAVE PRESUPUESTAL EN EL PRESUPUESTO VERIFICAMOS QUE NO TENGA UNA REDUCCIÓN
                EN PROCESO SI LA TIENE RESTAMOS EL MONTO AL DISPONIBLE */
                $reduccion = $this->model->getReduccionPresupuesto($val);
                if ($reduccion['registros'] > 0) {
                    $modificaciones = $reduccion['datos'][0]['dmo_importe'];
                }
                $disponible = $preVigente['disponible'] - $modificaciones;
                /* SE VALIDA QUE EL IMPORTE DEL COMPROMISO SEA MENOR O IGUAL AL PRESUPUESTO DISPONIBLE */
                if (Math::redondear($val->cmp_importe, 2) <= Math::redondear($disponible, 2) || $val->cmp_tipoCompromiso == 'A') {
                    $params = [$user, 'COMPROMISO AUTORIZADO CAPTURA MANUAL - S 2 3 2'];
                    $this->conectar();
                    $this->setSql(' SET @usr_id = ? ,@bit_observacion = ? ,@usr_ip = INET_ATON("' . $ip . '"); ');
                    $this->consultar($params);
                    $params = [$val->gpo_id, $val->ur_id, $val->cmp_id, $ip, $user];
                    $this->setSql(' CALL sp_AutorizarCompromiso' . APP_EJERCICIO . '(? ,? ,? , INET_ATON( ? ), ?)');
                    $this->consultar($params);
                    $this->setSql('SELECT @mensajePresupuesto AS mensaje,@banderaPresupuesto AS bandera,@mensajeContabilidad AS mensajeContable,@banderaContabilidad AS banderaContable');
                    $this->consultar();
                    $rowmensaje = $this->extraer();
                    if ($rowmensaje['bandera'] != 1) {
                        $this->mixScript(' notify(" ' . $rowmensaje['mensaje'] . ' ","error"); ');
                    } else {
                        $autorizados++;
                        if ($rowmensaje['banderaContable'] != 1) {
                            $this->mixScript(' notify("El compromiso fue autorizado pero la poliza no se generó de manera correcta","warning"); ');
                        } else {
                            $this->mixScript(' notify("El compromiso ' . $val->cmp_id . ' fue autorizado correctamente ","success"); ');
                        }
                    }
                } else {
                    $this->mixScript('alert("El compromiso ' . $val->cmp_id . ' no puede ser autorizado por que no tienes presupuesto disponible para comprometer");');
                }
            }
            if ($autorizados > 0) {
                if (!isset($this->prm->ajuste)) {
                    $this->mixScript($this->wrLlamaLlenaTabla('tbl_Compromisos'));
                } else {
                    $this->mixScript('
                        comunica("' . $this->pantalla . '",{
                            metodo:"addCompromiso",
                            cmp_id:"' . $this->prm->compromisos[0]->cmp_id . '",
                            "tipo":"' . $tcl['tcl_id'] . '",
                            chkGraph: ( $("#graficoSiNo").prop("checked" ) ? 1 : 0 )
                        });
                    ');
                }
            }
        }
    }

    // Marcos: 09/mar/2018
    // Para permitir seleccionar si quieren ver las gráficas o no... por default es 1 = SI
    private function GraficaPresupuesto($graficos = '1')
    {
        $this->setContenedor('tr_grafica');
        $this->prm->cmp_importe = (isset($this->prm->cmp_importe)) ? $this->prm->cmp_importe : 0;
        $this->prm->cmp_id = (isset($this->prm->cmp_id)) ? $this->prm->cmp_id : '';

        if ($this->prm->cmp_id != '') {
            $datos = $this->model->getDatosCompromiso($this->prm->cmp_id);
            $this->prm->pg_id = $datos['pg_id'];
            $this->prm->pe_id = $datos['pe_id'];
            $this->prm->tcp_id = $datos['tcp_id'];
            $this->prm->ecf_id = $datos['ecf_id'];
            $this->prm->epg_id = $datos['epg_id'];
            $this->prm->tipo = $datos['tcl_id'];
        } else {
            $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
            $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
        }
        $params = [$this->gpo_id, $this->ur_id, $this->prm->epg_id, $this->prm->ecf_id, $this->prm->pg_id, $this->prm->pe_id, $this->prm->tcp_id];
        $sql = '
            SELECT
            SUM(cmp_importe) AS cmp_importe
            FROM scp_Compromiso
            WHERE gpo_id = ?
                AND ur_id = ?
                AND epg_id = ?
                AND ecf_id = ?
                AND  pg_id = ?
                AND pe_id = ?
                AND tcp_id = ?
                AND ecm_id != "C"
        ';

        $prescapturado = $this->model->getDatos($sql, false, $params);
        $prescapturado = $prescapturado['datos'][0];
        $prescapturado['cmp_importe'] += 0;
        $params = [$this->gpo_id, $this->ur_id, $this->prm->epg_id, $this->prm->ecf_id, $this->prm->pg_id, $this->prm->pe_id, $this->prm->tcp_id];
        $sql = '
            SELECT
                SUM(pre_vigente) AS vigente,
                SUM(pre_comprometido) AS comprometido,
                SUM(pre_vigente) - SUM(pre_comprometido) AS  disponible,
                vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,
                vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
                com_ene,com_feb,com_mar,com_abr,com_may,com_jun,
                com_jul,com_ago,com_sep,com_oct,com_nov,com_dic
            FROM presupuesto
            INNER JOIN presupuestoVigente USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN presupuestoComprometido USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                AND epg_id = ?
                AND ecf_id = ?
                AND pg_id = ?
                AND pe_id = ?
                AND tcp_id = ?
            GROUP BY vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,
                vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
                com_ene,com_feb,com_mar,com_abr,com_may,com_jun,
                com_jul,com_ago,com_sep,com_oct,com_nov,com_dic
        ';

        $presupuesto = $this->model->getDatos($sql, false, $params);
        $registros = $presupuesto['registros'];
        $presupuesto = $presupuesto['datos'][0];
        $params = [$this->gpo_id, $this->ur_id, $this->prm->epg_id, $this->prm->epg_id, $this->prm->pg_id, $this->prm->pe_id, $this->prm->tcp_id, $this->clasAdmUe];
        $sql = '
            SELECT
                dmo_importe
            FROM scp_MaestroModificacion
            INNER JOIN scp_DetalleModificacion USING (omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                AND epg_id = ?
                AND ecf_id = ?
                AND  pg_id = ?
                AND pe_id = ?
                AND tcp_id = ?
                AND ue_id = ?
                AND emo_id not in(14,15,16)
                AND mov_id = "R";
        ';
        $modificacionesData = $this->model->getDatos($sql, false, $params);
        $registrosMod = $modificacionesData['registros'];
        $modificaciones = $modificacionesData['datos'][0];

        if ($registrosMod == 0) {


            $modificacionesData['dmo_importe'] = 0;
        }

        $presupuesto['disponible'] += 0;
        $presupuesto['comprometido'] += 0;
        $presupuesto['vigente'] += 0;
        $presupuesto['disponible'] = ($presupuesto['disponible'] - $modificacionesData['dmo_importe']);
        $suma = $prescapturado['cmp_importe'] + $this->prm->cmp_importe;
        $division = $suma * 100;
        if ($division == 0) {
            $capturado = 0;
        } else {
            if ($presupuesto['vigente'] == 0) {
                $this->mixScript('notify("La Clave Presupuestal no cuenta con presupuesto vigente en el mes","error");');
            } else {
                $capturado = ($division) / $presupuesto['vigente'];
            }
        }
        $capturado = ($capturado > .01) ? round($capturado, 2) : $capturado;

        /* PINTAR LOS RESULTADOS DE  LAS CONSULTAS EN LAS DIFERENTES GRAFICAS  */

        $prms = [
            'vigente'      => $presupuesto['vigente'],
            'capturado'    => $capturado,
            'disponible'   => $presupuesto['disponible'],
            'comprometido' => $presupuesto['comprometido'],
        ];
        $contenedor = $this->vista->vwPintaContenedorGrafica($prms);
        $this->mixHtml($contenedor);

        // Si viene desde javascript es graph, si viene desde php es graficos
        if (isset($this->prm->graph)) {
            $mostrarGraficas = $this->prm->graph;
        } elseif (isset($this->prm->chkGraph)) {
            $mostrarGraficas = $this->prm->chkGraph;
        } elseif (isset($this->prm->graficos)) {
            $mostrarGraficas = $this->prm->graficos;
        } else {
            $mostrarGraficas = '1';
        }

        // Marcos: 09/mar/2018
        // Para poder elegir si mostrar o no los gráficos

        if ($mostrarGraficas == '1') {
            $grafica = $this->vista->vwGraficaBarra($presupuesto, $capturado);
            $grafica2 = $this->vista->vwGraficaPastel($presupuesto, $modificacionesData);
            $grafica3 = $this->vista->vwGraficaLinear($presupuesto);

            $this->mixScript($grafica);
            $this->mixScript($grafica2);
            $this->mixScript($grafica3);
        }

        $this->mixScript('
            $("#cmp_hideGraph").click(function(){
                $("#cmp_graph").toggle("slow");
                $("#cmp_ShowGraph").toggle("slow");
                $(this).slideToggle("slow");
            });

            $("#cmp_ShowGraph").click(function(){
                $("#cmp_hideGraph").toggle("slow");
                $("#cmp_graph").toggle("slow");
                $(this).slideToggle("slow");
            });

            $(".cerrar").click(function(){
                $(".modal-backdrop").remove();
                $("#modal").remove();
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    "cmp_id":"' . $this->prm->cmp_id . '"
                    ' . (isset($tipo) ? ',"tipo":"' . $this->prm->tipo . '"' : '') . '
                });
            });
        ');
    }

    private function Proveedor()
    {
        $sql = '
            SELECT
                rfc_id,rfc_nombre,rfc_paterno,rfc_materno, soc_nombre
            FROM cat_Proveedor
            INNER JOIN sinpres.cat_Proveedores	USING (rfc_id)
            INNER JOIN sinpres.cat_SociedadProveedor USING (soc_id)
            WHERE  rfc_id = ?
        ';
        $params = [$this->prm->rfc];
        $datos = $this->model->getDatos($sql, false, $params);
        $row = $datos['datos'][0];

        if ($datos['registros'] > 0) {
            $this->mixScript('$("#nomProv").val("' . str_replace('"', '\"', $row['rfc_nombre']) . ' '
                . $row['rfc_paterno'] . ' '
                . $row['rfc_materno'] . ' '
                . $row['soc_nombre'] . '" );');
        } else {
            $this->mixScript('
                $("#nomProv").val("");
                $("#NumReferencia").attr("readonly","readonly");
                notify("Atención El Proveedor con RFC ' . $this->prm->rfc . ' no existe en la base de datos!!","warning");');
            return false;
        }
    }

    private function DameNombreProveedor()
    {
        // Obtenemos el nombre del proveedo dado un rfc
        $this->conectar();
        $this->setSql("
            SELECT
                CONCAT(rfc_nombre, ' ', rfc_paterno, ' ', rfc_materno) AS nombre
            FROM cat_Proveedor
            INNER JOIN sinpres.cat_Proveedores	USING (rfc_id)
            INNER JOIN sinpres.cat_SociedadProveedor USING (soc_id)
            WHERE  rfc_id = ?;
        ");
        $params = [$this->prm->rfc];
        $this->consultar($params);
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            $dato = $this->extraer();
            $this->mixScript('
                $("#nomProv").val("' . trim(mysql_escape_mimic($dato['nombre'])) . '" );
            ');
        } else {
            $this->mixScript('
                $("#nomProv").val("EL RFC NO EXISTE EN LA BASE DE DATOS" );
            ');
        }
    }

    private function DocumentoSoporte()
    {
        if (isset($this->prm->abc) && $this->prm->abc == 'c') {
            return false;
        }

        $this->prm->stc_id = (isset($this->prm->stc_id)) ? $this->prm->stc_id : 0;

        if (
            isset($this->prm->stc_id) && $this->prm->stc_id != ''
            || isset($this->prm->tipo) && $this->prm->tipo != ''
        ) {
            $comboDoc = new stdClass();
            $comboDoc->comoAtr = 'tpd_nombre';
            $comboDoc->id = 'tpd_id';
            $comboDoc->params = [$this->prm->tipo, $this->prm->stc_id];
            $comboDoc->sql = '
                SELECT
                    tpd_id,tpd_nombre
                FROM cat_TipoDocumento
                WHERE tcl_id = ?
                    AND stc_id = ?
                    AND tpd_momento = "C"
                    AND uni_id = ' . ($this->princesa ? '18' : ' 17 ');
            $this->recargaCombo($comboDoc);
        }
    }

    private function Contratos()
    {

        $grupo = $this->clasAdmGpo;
        $ur = $this->clasAdmUr;

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $params = [$grupo, (int)$ur];

        $conexion->setSql('
            SELECT
                uur_id
            FROM  sinpres.vin_UnidadResponsable
            WHERE gpo_id = ?
                AND ur_id = ?
                AND vur_ejercicio = ' . APP_EJERCICIO . '
            LIMIT 1;
        ');
        $conexion->consultar($params);
        $conexion->desconectar();
        $datos = $conexion->extraer();

        $c2 = new AccesoDatos();
        $c2->conectar();
        $c2->setSql('
            SELECT
                vur_id
            FROM  sinpres.vin_UnidadResponsable
            WHERE uur_id = ?
        ');
        $params = [$datos['uur_id']];
        $c2->consultar($params);
        $c2->desconectar();

        $in_vur = '';
        while ($row = $c2->extraer()) {
            $in_vur .= $row['vur_id'] . ',';
        }
        $in_vur = trim($in_vur, ',');
        if ($in_vur == '') {
            $in_vur = 0;
        }

        $contrato = new stdClass();
        $contrato->id = 'ipu_id';
        $contrato->sql = '
            SELECT
                ipu_id,
                ipu_numeroContrato
            FROM sinpres.cat_InversionPublica
            WHERE vur_id IN(' . $in_vur . ')
                AND eip_id = 2
        ';
        $contrato->comoAtr = 'ipu_numeroContrato';
        $this->recargaCombo($contrato);
    }

    private function abcCompromiso()
    {

        $desc2 = '- S 2 3 2';
        $user = $this->usr_id;
        $ip = (isset($this->usuarioIp) && $this->usuarioIp != '::1') ? $this->usuarioIp : '127.0.0.1';
        $this->prm->numOrden = (isset($this->prm->numOrden)) ? $this->prm->numOrden : ' ';
        $this->prm->tipo = (isset($this->prm->tcl_id)) ? $this->prm->tcl_id : $this->prm->tipo;
        $this->prm->ecf_id = ClasificacionFinanciamiento::getEcf();
        $this->prm->epg_id = ClasificacionAdministrativa::getEpg();
        $this->prm->gpo_id = $this->gpo_id;
        $this->prm->ur_id = $this->ur_id;
        $this->prm->ue_id = $this->clasAdmUe;

        if ($this->prm->abc != 'b') {
            $bloqueo = $this->model->gtBloqueos($this->prm->tipo, $this->prm->stc_id, $this->prm->ecf_id, $this->prm->opcion);
            if ($bloqueo->valido == 0) {
                $this->mixScript(' notify("' . $bloqueo->mensaje . ' ","error",false); ');
                return false;
            }
        }

        switch ($this->prm->abc) {
            case 'a':
                $rfc = $this->model->revisaProveedor($this->prm->rfc_id);
                if ($rfc == false) {
                    $this->mixScript('notify("El Proveedor no existe o no esta habilitado, por favor revise el catálogo de proveedores","error")');
                    return false;
                }

                switch ($this->prm->tipo) {
                    case 'F':
                    case 'G':
                        if (isset($this->prm->ipu_id) && $this->prm->ipu_id != '') {
                            $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->ipu_id, $this->prm->epg_id];
                            $sql = '
                                SELECT
                                    epg_id
                                FROM enc_InversionObra
                                WHERE  gpo_id = ?
                                    AND ur_id = ?
                                    AND ipu_id = ?
                                    AND epg_id = ?;';
                            $reg = $this->model->getDatos($sql, true, $params);

                            if ($reg['registros'] == 0) {
                                $this->mixScript(' notify("El contrato ' . $this->prm->numeroContrato . ' no tiene agregada la obra  ","error");');
                                return false;
                            }
                        }
                        break;
                }

                $compromiso_id = Compromiso::abc($this->prm);

                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_Compromiso (gpo_id,ur_id,cmp_id,ecm_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES (?,?,?,?,?,INET_ATON(?),?,NOW())';
                $params = [$this->prm->gpo_id, $this->prm->ur_id, $compromiso_id, 'G', "COMPROMISO GENERADO POR LA UR  $desc2", $ip, $user];
                $this->model->fnEjecutaSql($sql, $params);

                $this->mixScript('
                    notify("El Compromiso ' . $compromiso_id . ' ha sido ingresado correctamente","success");
                    ' . $this->wrLlamaLlenaTabla('tbl_Compromisos') . '');
                break;
            case 'c':
                Compromiso::abc($this->prm);
                $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->cmp_id, 'G', "COMPROMISO MODIFICADO POR LA UR  $desc2", $ip, $user];
                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_Compromiso (gpo_id,ur_id,cmp_id,ecm_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES (? ,? ,? ,? ,? ,INET_ATON( ? ) ,? ,NOW())
                ';
                $this->model->fnEjecutaSql($sql, $params);
                $this->mixScript('
                    notify("El Compromiso  ' . $this->prm->cmp_id . ' Se actualizó correctamente","success");
                    ' . $this->wrLlamaLlenaTabla('tbl_Compromisos') . '');
                break;
            case 'b':
                $com = $this->model->getDatosCompromiso($this->prm->cmp_id);
                if ($com['ecm_id'] != 'G') {
                    $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->cmp_id, $com['ecm_id'], "SE INTENTA ELIMINAR UN COMPROMISO $desc2", $ip, $user];
                    $sql = '
                        INSERT INTO ' . BD_BITACORA . '.bit_Compromiso(gpo_id,ur_id,cmp_id,ecm_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                        VALUES (?,?,?,?,INET_ATON( ? ),? ,NOW())
                    ';

                    $this->model->fnEjecutaSql($sql, $params);
                    $this->mixScript('notify("El compromiso no se puede eliminar","error");');
                    return false;
                }
                $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->cmp_id, 'G', "COMPROMISO ELIMINADO POR LA UR  $desc2", $ip, $user];
                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_Compromiso(gpo_id,ur_id,cmp_id,ecm_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES (? ,? ,? ,? ,? ,INET_ATON( ? ) , ?,NOW())
                ';

                $this->model->fnEjecutaSql($sql, $params);
                Compromiso::abc($this->prm);
                $this->mixScript('notify("El Compromiso ' . $this->prm->cmp_id . ' ha sido eliminado correctamente ","success");
                    ' . $this->wrLlamaLlenaTabla('tbl_Compromisos') . '');
                break;
        }
        $this->mixScript('
            /*$("#fsaddcompromiso").animate({opacity:0,width:"80%",height:"80%"},300,function(){ $("#fsaddcompromiso").remove() });*/
            comunica("' . $this->pantalla . '",{
                metodo:"main",
                tipo: "' . $this->prm->tipo . '"
            });
        ');
    }

    private function CancelarCompromiso()
    {
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $ip = (isset($this->usuarioIp) && $this->usuarioIp != '::1') ? $this->usuarioIp : '127.0.0.1';
        $user = $this->usr_id;
        $this->prm->cmp_id = (isset($this->prm->cmp_id)) ? $this->prm->cmp_id : '';

        $conex = new AccesoDatos();
        $params = [$user, $ip];
        $conex->conectar();
        $conex->setSql(' SET @usr_id = ? ,@bit_observacion ="COMPROMISO CANCELADO CAPTURA MANUAL S 2 3 2",@usr_ip = INET_ATON(?);');
        $conex->consultar($params);
        $params = [$gpo_id, $ur_id, $this->prm->cmp_id, $ip, $user];
        $conex->setSql(' CALL sp_CancelarCompromiso' . APP_EJERCICIO . '(?, ?, ?, INET_ATON( ? ), ? ) ');
        $conex->consultar($params);
        $conex->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
        $conex->consultar();
        $rowmensaje = $conex->extraer();
        if ($rowmensaje['bandera'] != 1) {
            $this->mixScript(' notify(" ' . $rowmensaje['mensaje'] . ' ","error"); ');
            return false;
        }

        $conex->desconectar();
        $this->mixScript('
            notify("El Compromiso  ' . $this->prm->cmp_id . ' ha sido cancelado ","success");
            /*$("#fsaddcompromiso").animate({opacity:0,width:"80%",height:"80%"},300,function(){ $("#fsaddcompromiso").remove() });*/
            ' . $this->wrLlamaLlenaTabla('tbl_Compromisos') . '
            comunica("' . $this->pantalla . '",{
                metodo:"main"
            });
        ');
    }

    private function AjusteCompromiso()
    {

        $compromiso = $this->model->getDatosCompromiso($this->prm->cmp_id, false);
        $bloqueo = $this->model->gtBloqueos($compromiso->tcl_id, $compromiso->stc_id, $compromiso->ecf_id, $this->prm->opcion);

        if ($compromiso->cmp_tipoCompromiso == 'N') {
            $this->setContenedor('divContenedorGeneralModal');
            $tipo = new StdClass();
            //Si está bloqueado para compromisos
            if ($bloqueo->valido == 0) {
                $tipo->values = [2 => 'Reduccion'];
            } else {
                $tipo->values = [1 => 'Ampliacion', 2 => 'Reduccion'];
            }
            $tipo->class = 'radio rango limpiar';
            $tipo->name = 'tipoP';
            $tipo->cols = 3;
            $params = [$this->gpo_id, $this->ur_id, $this->prm->cmp_id, $this->gpo_id, $this->ur_id, $this->prm->cmp_id, $this->gpo_id, $this->ur_id, $this->prm->cmp_id];
            $sql = '
                SELECT
                    ((cmp_importe + if(percepciones is null,0,SUM(percepciones)) - if(retenciones is null,0,SUM(retenciones))) - SUM(IF(m.percepcion IS NULL , 0 ,m.percepcion))) AS  importe
                FROM scp_Compromiso
                LEFT  JOIN (
                    SELECT
                        gpo_id,ur_id,cmp_cmp_id AS cmp_id,
                        SUM( if(cmp_tipoMovimiento="-",cmp_importe,0) ) retenciones,
                        SUM( if(cmp_tipoMovimiento="+",cmp_importe,0) ) percepciones
                    FROM scp_Compromiso
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND  cmp_cmp_id = ?
                        AND ecm_id not in ("G","C") and cmp_cmp_id is not null
                    GROUP BY gpo_id,ur_id,cmp_cmp_id
                )ajustes using(gpo_id,ur_id,cmp_id)
                LEFT JOIN (
                    SELECT
                        gpo_id,ur_id,cmp_id,SUM(ncp_percepcionNeto) AS percepcion
                    FROM scp_Presupuestal
                    INNER JOIN scp_Afectacion using(gpo_id,ur_id,afe_id)
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND  cmp_id = ?
                        AND eaf_id != "C"
                    GROUP BY gpo_id,ur_id,cmp_id
                )m USING ( gpo_id,ur_id,cmp_id)
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND  cmp_id = ?
                    AND ecm_id != "C"
                GROUP BY gpo_id, ur_id, cmp_id
            ';

            $importeA = $this->model->getDatos($sql, false, $params);
            $importeA = $importeA['datos'][0];

            if ($importeA['importe'] <= 0) {
                $importeA['importe'] = 0;
            }

            $mensaje = '';
            if ($importeA['importe'] == $compromiso->cmp_importe) {
                $mensaje = '<div class="row alert alert-warning">El compromiso no ha sido usado, ya sea que reduzca '
                    . 'o aumente, se sugiere que lo mas prudente es Cancelar el compromiso y generar uno nuevo con el importe que necesita,'
                    . 'asi evitara una cantidad innecesaria de Compromisos y polizas del Comprometido</div>';
            }

            $importAjuste = new StdClass();
            $importAjuste->id = 'importAjuste';
            $importAjuste->class = 'habilitar';
            $importAjuste->label = 'Captura el Importe ';
            $importAjuste->enDiv = true;
            $importAjuste->formato = 'moneda';
            $importAjuste = $this->ponInput($importAjuste);

            $modal = '';
            $imp = number_format((float) $importeA['importe'], 2);
            $modal =
                <<<HTML
                    <div id="modal" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; padding-top: 70px;">
                        <div class="modal-dialog" style="width: 45% !IMPORTANT;">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color:#9d2449">
                                    <h4 class="modal-title" id="myModalLabel">Ajuste del Compromiso</h4>
                                    <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="Mensaje">
                                    {$mensaje}
                                    <div class="row">
                                        <fieldset  class="col-md-12 col-xs-12">
                                            {$this->ponRadio($tipo)}
                                        </fieldset>
                                    </div>
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-12">
                                            {$importAjuste}
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6">
                                            <label class=" btn btn-outline-danger me-1">Importe maximo a disminuir:  {$imp}</label>
                                        </fieldset>
                                    </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger cerrar" data-be-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-outline-success ajustar" >Aceptar</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div>
                HTML;

            $this->mixHtml($modal);
            $this->mixScript('
                $("#modal").modal();
                $(".cerrar").click(function(){
                    $(".modal-backdrop").remove();
                    $("#modal").remove();
                });

                $("input[name=tipoP]").click(function(){
                    switch ($("input[name=tipoP]:checked").val())
                    {
                        case "1":
                            $("#importAjuste").val("");
                        break;
                        case "2":
                            $("#importAjuste").val(formatNumber(' . $importeA['importe'] . '));
                        break;
                    }
                });

                $("#importAjuste").blur(function(){
                    if($("input[name=tipoP]:checked").val()==2){
                        var importe = unformatNumber($("#importAjuste").val());
                        if(importe >' . $importeA['importe'] . '){
                                notify("El importe excede el presupuesto a ajustar","error");
                        }
                    }
                });

                $(".ajustar").click(function(){
                    if(!$("input[name=tipoP]").is(":checked")){
                        notify("Elige el tipo de movimiento para realizar el ajuste","error");
                    } else {
                        var importe = unformatNumber($("#importAjuste").val());
                        if($("input[name=tipoP]:checked").val()==2){
                            if(importe >' . $importeA['importe'] . '){
                                notify("El importe excede el presupuesto a ajustar","error");
                                return false;
                            }
                        }

                        if(importe==0){
                            notify("No se puede hacer un ajuste con la cantidad 0 ","error");
                            return false;
                        }
                        alertify.confirm(
                            "Autorizar Compromiso",  // Título
                            "¿Estás seguro de autorizar el Compromiso ' . $this->prm->cmp_id . '?",
                            function() {
                                // Si el usuario acepta:
                                comunica("' . $this->pantalla . '", {
                                    metodo: "ValidarAjuste",
                                    cmp_id: "' . $this->prm->cmp_id . '",
                                    tipoMovimiento: $("input[name=tipoP]:checked").val(),
                                    importe: parseFloat(unformatNumber($("#importAjuste").val()).toFixed(2)),
                                    tipo: "' . $compromiso->tcl_id . '",
                                });
                                $(".modal-backdrop").remove();
                                $("#modal").remove();
                            },
                            function() {
                                // Si el usuario cancela:
                                console.log("Cancelado");
                            }
                        ).set("labels", { ok: "Autorizar", cancel: "Cancelar" })
                        .set("onshow", function() {
                            document.querySelector(".ajs-ok").classList.add("btn btn-outline-success");
                            document.querySelector(".ajs-cancel").classList.add("btn btn-outline-danger");
                        });
                    }
                });
            ');
        } else {
            $this->mixScript('
                notify("No puedes ajustar un compromiso de Ajuste","error",false);
            ');
        }
    }

    private function ValidarAjuste()
    {
        if (!$this->escritura) {
            $this->mixScript('
                notify("NO tiene permisos de escritura o su nivel jerarquico no es el adecuado","error");');
            return;
        }

        $user = $this->usr_id;
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;

        if ($this->prm->tipoMovimiento == 1) {
            $movimiento = '+';
        } else {
            $movimiento = '-';
        }
        $this->conectar();

        $usr_id = $this->usr_id;

        $ip = (isset($this->usuarioIp) && $this->usuarioIp != '::1') ? $this->usuarioIp : '127.0.0.1';
        $params = [$usr_id, $ip];
        $this->setSql('SET @bit_observacion ="", @usr_id = ? , @usr_ip = INET_ATON( ? );');
        $this->consultar($params);
        $this->setSql('
            INSERT INTO scp_Compromiso (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,rfc_id,cmp_fechaInicio, cmp_fechaFin,cmp_numOrden,cmp_fechaOrden,cmp_concepto,cmp_tipoMovimiento,cmp_importe,tpd_id,ecm_id,cmp_tipoCompromiso,cmp_cmp_id,usr_id,usr_fecha)
            SELECT
                gpo_id,
                ur_id,
                ue_id,
                epg_id,
                pg_id,
                pe_id,
                ecf_id,
                tcp_id,
                tcl_id,
                stc_id,
                tno_id,
                mno_id,
                smn_id,
                rfc_id,
                cmp_fechaInicio,
                cmp_fechaFin,
                cmp_numOrden,
                cmp_fechaOrden,
                cmp_concepto,
                "' . $movimiento . '" AS cmp_tipoMovimiento,
                ' . $this->prm->importe . ' AS cmp_importe,
                tpd_id,"G" AS ecm_id,
                "A" AS cmp_tipoCompromiso,
                ' . $this->prm->cmp_id . ' AS cmp_cmp_id,
                ' . $user . ' AS usr_id,
                now() as usr_fecha
            FROM  scp_Compromiso
            WHERE gpo_id = ?
            AND ur_id = ?
            AND cmp_id = ?;
        ');
        $params = [$gpo_id, $ur_id, $this->prm->cmp_id];
        $this->consultar($params);
        $this->desconectar();
        $this->mixScript('
            notify("Se ha realizado el ajuste del compromiso ' . $this->prm->cmp_id . '  ","success");
            comunica("' . $this->pantalla . '",{
                metodo:"addCompromiso",
                "cmp_id":"' . $this->prm->cmp_id . '",
                "chkGraph": ( $("#graficoSiNo").prop("checked" ) ? 1 : 0 ),
                "tipo":"' . $this->prm->tipo . '",
                "es":"ajuste"
            });
        ');
    }

    private function DatosObra()
    {

        if (!isset($this->prm->ipu_id)) {
            return false;
        }

        $datos = $this->model->getDatosObra(['ipu_id' => $this->prm->ipu_id]);

        if ($datos['registros'] > 0) {
            $row = $datos['datos'][0];

            if (!is_null($row['habilitado'])) {
                $this->mixScript('
                    $("#rfc_id").val("' . str_replace('"', '', $row['rfc_id']) . '").attr("readonly",true);
                    $("#nomProv").val("' . str_replace('"', '\"', $row['rfc_nombre']) . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre'] . '" );
                    $("#ordenPedido").val("' . str_replace('"', '', $row['ipu_numeroContrato']) . '").attr("readonly",true);
                    $("#FechaOrden").val("' . str_replace('"', '', $row['ipu_fechaContrato']) . '").attr("readonly",true);
                ');
            } else {
                $this->mixScript('
                    alertify.alert("Proveedor",""El proveedor ' . $row['rfc_id'] . ' está inhabilitado, para utilizarlo es necesario que esté habilitado' . ', esto lo puede realizar en la opción catálogo de proveedores",function(){}
                ');
            }
        } else {
            $this->mixScript('
                alertify.alert("Contrato","El contrato ' . $this->prm->ipu_id . ' no corresponde a la UR", function(){
                });
            ');
        }
    }

    public function DetalleCompromiso()
    {

        $script = '';
        $tabla = new stdClass();
        $datos = $this->model->getDatosCompromiso($this->prm->cmp_id);

        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = ['cmp_id', 'movimiento', 'ajuste'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'ecm_id';
        $tabla->casoSeleccionable->valor = 'G';
        $tabla->casoSeleccionable->ands = [];
        $and = new stdClass();
        $and->campo = 'cmp_importe';
        $and->operador = '!=';
        $and->valor = '0';
        $and->valorTipo = 'numero';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
        array_push($tabla->casoSeleccionable->ands, $and);

        $tabla->casosEstado = new stdClass();
        $tabla->casosEstado->campo = 'ecm_id';
        $tabla->casosEstado->cases = [];
        $caso = new stdClass();
        $caso->valor = 'A';
        $caso->color = 'rgba(8, 255, 18, 0.20)';
        array_push($tabla->casosEstado->cases, $caso);
        $caso = new stdClass();
        $caso->valor = 'C';
        $caso->color = 'rgba(255, 0, 0, 0.20)';
        array_push($tabla->casosEstado->cases, $caso);

        $tabla->accion = new stdClass();
        $tabla->accion->editar = true;
        $tabla->accion->metodoEditar = 'addCompromiso';
        $tabla->accion->prm = ['cmp_id', 'tcl_id', 'gpo_id', 'ur_id', 'smn_id', 'cmp_importe', 'comp', 'original'];

        $tabla->id = 'tbl_Detalle';
        $tabla->titulo = 'Ajustes del Compromiso ' . $this->prm->cmp_id . '';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->cmp = $this->prm->cmp_id;
        $tabla->comunica->metodo = 'llenaDetalleCompromiso';
        $tabla->comunica->tipo = '{$(\'.selected-afe\').attr(\'id\')}';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [20, 50, 100, 300, 400, 500];
        $tr = [];

        $th = new stdClass();
        $th->texto = 'N° Compro.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Importe';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Movimiento';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Estado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha';
        array_push($tr, $th);

        $trbody = []; // cuerpo de la tabla en un arreglo de tr y tds

        $td = new stdClass();
        $td->campoTxt = 'cmp_Ajuste';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Movimiento';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ecm_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'cmp_fechaAutorizado';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $html = $this->wrPonTabla($tabla);
        $this->mixScript($script);
        $html .= '<div class="mb-2"></div>';

        $btnautorizar = new stdClass();
        $btnautorizar->id = 'btnAutoAjuste';
        $btnautorizar->class = 'me-1';
        $btnautorizar->icon = 'solid fa-check me-2';
        $btnautorizar->color = 'verde';
        $btnautorizar->value = 'Autorizar Ajuste';
        $btnautorizar->click = '
            if (seleccionados = $("#tbl_Detalle").tabla("getFilaSeleccion")) {
                    comunica("' . $this->pantalla . '",{
                    metodo:"AutorizarCompromiso",
                    tipo:$(".selected-afe").attr("id"),
                    compromisos:seleccionados
                });
            }
        ';

        // Verifica si el usuario tiene los permisos para autorizar
        if ($this->escritura) {
            if ($this->nvj == 1 && $datos['Usuario'] != $this->usr_id) {
                $btnautorizar = $this->vista->vwSoloLectura('AUTORIZAR:COMPROMISO DE OTRO USUARIO');
            } else {
                $btnautorizar = $this->ponBoton($btnautorizar);
            }
        } else {
            $btnautorizar = $this->vista->vwSoloLectura('SOLO LECTURA');
        }

        $html .= $btnautorizar;

        $this->mixScript('
            $("#btnRegresarIndex").click(function(){
                $("nav").menusinpres("clickMenu");
            });
            ' . $this->wrLlamaLlenaTabla('tbl_Detalle') . '
            ');
        return $html;
    }

    private function llenaDetalleCompromiso()
    {

        $this->prm->importe = (isset($this->prm->importe)) ? $this->prm->importe : '';
        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';

        $this->conectar();
        $sql = '
            SELECT gpo_id, ur_id, cmp_id AS cmp_Ajuste, cmp_importe ,
                ecm_id, cmp_id, epg_id, ecf_id, tcl_id, stc_id,
                cmp_importe,
                if(cmp_tipoMovimiento="+"
                    ,"A"
                    ,"R") AS movimiento,
                if(cmp_tipoMovimiento="+"
                    ,"AMPLIACION"
                    ,"REDUCCION") AS Movimiento,ecm_nombre,
                pg_id, pe_id, tcp_id, ue_id,
                cmp_tipoCompromiso,
                "ajuste" AS comp,
                cmp_cmp_id AS original,
                "ajuste" AS ajuste,
                DATE_FORMAT(cmp_fechaAutorizado,"%d/%m/%Y") AS cmp_fechaAutorizado
            FROM scp_Compromiso
            INNER JOIN cat_EstadoCompromiso USING(ecm_id)
            WHERE gpo_id = ?
                AND ur_id = ?
                AND cmp_cmp_id = ?
        ';
        $params = [$this->gpo_id, $this->ur_id, $this->prm->cmp];
        $this->setSql($sql);
        $this->setParams($params);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 25;
        $nav->idTabla = 'tbl_Detalle';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay Ajustes para el compromiso ' . $this->prm->cmp . '","warning");');
        }

        $this->mixScript('
            $(".btnAutoAjuste").click(function() {
            comunica("' . $this->pantalla . '",{
                metodo:"GraficaPresupuesto",
                    cmp_id:$(this).attr("cmp_id"),
                    cmp_importe:$(this).attr("cmp_importe")
                });
                return false;
            });
        ');
    }

    private function ActualizarCompromisos()
    {
        $gpo_id = $this->gpo_id2;
        $ur_id = $this->ur_id2;
        $cmp4 = 0;
        $cmp3 = 0;
        $cmp2 = 0;
        $cmp = 0;

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->class = 'btn btn-outline-secondary regresar me-1';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->icon = 'solid fa-circle-left me-2';

        ini_set('memory_limit', '500M');
        set_time_limit(0);
        $reg = new AccesoDatos();
        $reg->conectar();
        $reg->setSql('
            SELECT cmp_id
            FROM scp_Compromiso
            WHERE gpo_id = ? 
                AND ur_id = ?
                AND  ecm_id not IN ("G","C")
                AND month(usr_fecha) = ?');
        $params = [$gpo_id, $ur_id, $this->prm->mes];
        $reg->consultar($params);

        while ($datos = $reg->extraer()) {
            $compromiso = ValidaGarantias::gtCompromiso($datos['cmp_id']);

            $importe = $compromiso->cmp_importe + $compromiso->ajustado;
            if ($compromiso->ecm_id != 'G' && $compromiso->ecm_id != 'C' && $importe > 0) {
                $sql = '';
                $params = [];
                if ((Math::redondear($importe, 2) == Math::redondear($compromiso->devengado, 2)) && $compromiso->ecm_id != 'D') {
                    $sql = 'UPDATE scp_Compromiso  SET ecm_id ="D" WHERE gpo_id = ? and ur_id = ? and cmp_id = ?;';
                    array_push($params, $gpo_id, $ur_id, $datos['cmp_id']);
                    $cmp++;
                } elseif (
                    (Math::redondear($importe, 2) > Math::redondear($compromiso->devengado, 2))
                    && Math::redondear($compromiso->devengado, 2) > 0
                    && $compromiso->ecm_id != 'P'
                ) {
                    $sql = 'UPDATE scp_Compromiso  SET ecm_id ="P" WHERE gpo_id = ? and ur_id = ? and cmp_id = ?';
                    array_push($params, $gpo_id, $ur_id, $datos['cmp_id']);
                    $cmp2++;
                    //cambias estado a P
                } elseif (Math::redondear($compromiso->devengado, 2) == 0 && $compromiso->ecm_id != 'A' && $compromiso->ecm_id != 'C') {
                    $sql = 'UPDATE scp_Compromiso  SET ecm_id ="A" WHERE gpo_id = ? and ur_id = ? and cmp_id = ?;';
                    array_push($params, $gpo_id, $ur_id, $datos['cmp_id']);
                    $cmp3++;
                } elseif (Math::redondear($importe, 2) < Math::redondear($compromiso->devengado, 2)) {
                    $importeAjuste = $compromiso->devengado - $importe;
                    $cmp4++;
                }
                if ($sql != '') {
                    $conex = new AccesoDatos();
                    $conex->conectar();
                    $conex->setSql($sql);
                    $conex->consultar($params);
                    $conex->desconectar();
                }
            }
        }
        $this->agregaTools($this->ponBoton($btnRegresarIndex));
        $this->mixHtml(
            <<<HTML
                <div>
                    <fieldset class="col-md-12 col-xs-12">
                        <h6 style="text-align:center"><b><font color="#3D362D">COMPROMISOS ACTUALIZADOS</font><b></h6>
                        <table class="table table-bordered table-hover">
                            <tr >
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">Compromisos actualizados al estado Devengado { $cmp }</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">Compromisos actualizados al estado Parcialmente Devengado  { $cmp2 }</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">Compromisos actualizados al estado Autorizados { $cmp3 }</font></b></td>
                            </tr>
                            <tr >
                                <td bgcolor="#FFFFFF"><b><font color="#2C2416">Compromisos que necesitan un ajuste { $cmp4 }</font></b></td>
                            </tr>
                        </table>
                    </fieldset>
                </div>
            HTML
        );

        $this->mixScript('
            $("#btnRegresarIndex").click(function(){
                $("nav").menusinpres("clickMenu");
            });
        ');
    }

    private function ponbotonesmain($cmp = false)
    {
        /* BOTON DE CONSULTA  */
        $btnConsulta = new stdClass();
        $btnConsulta->id = 'btnConsulta';
        $btnConsulta->class = 'btn btn-outline-secondary me-1';
        $btnConsulta->value = 'Buscar';
        $btnConsulta->text = 'Buscar';
        $btnConsulta->icon = 'search me-2';
        $btnConsulta = $this->ponBoton($btnConsulta);

        /* BOTON DE REGRESAR A LA PANTALLA  */
        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->class = ' btn btn-outline-secondary regresar me-1';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->icon = 'solid fa-circle-left me-2';
        $btnRegresarIndex->click = '$("nav").menusinpres("clickMenu");';
        $btnRegresarIndex = $this->ponBoton($btnRegresarIndex);
        $btnRegresarIndex = '';

        /* BOTON DE GENERAR NUEVO COMPROMISO  */

        $btnnuevo = new stdClass();
        $btnnuevo->id = 'btnnuevo';
        $btnnuevo->class = 'btn btn-outline-primary btnnuevo me-1';
        $btnnuevo->icon = 'solid fa-plus me-2';
        $btnnuevo->name = 'nuevo';
        $btnnuevo->text = 'Nuevo';
        $btnnuevo->value = 'Nuevo';
        $btnnuevo->click = '
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            if(s=validaTip()){
                /*load_viewer("addcompromiso","COMPROMISO","div","",1030,true);*/
                comunica("' . $this->pantalla . '",{
                    metodo:"addCompromiso",
                    tcl_id:s,
                    chkGraph: ( $("#graficoSiNo").prop("checked" ) ? 1 : 0 )
                });
            }
        ';

        if ($this->escritura) {
            $btnnuevo = $this->ponBoton($btnnuevo);
        } else {
            $btnnuevo = $this->vista->vwSoloLectura('NUEVO - SOLO LECTURA');
        }

        $btnautorizar = new stdClass();
        $btnautorizar->id = 'btnautorizar';
        $btnautorizar->class = ' btn btn-outline-success me-1';
        $btnautorizar->icon = 'solid fa-check me-2';
        $btnautorizar->color = 'verde';
        $btnautorizar->value = 'Autorizar';
        $btnautorizar->click = '
            if (s = validaTip()) {
                if( seleccionados = $("#tbl_Compromisos").tabla("getFilaSeleccion") ){
                    alertify.confirm("AUTORIZAR COMPROMISO(S)","¿ Confirma que desea autorizar el (los) Compromiso(s) seleccionado(s)",
                        function(){
                        comunica("' . $this->pantalla . '",{metodo:"AutorizarCompromiso",tipo:s,compromisos:seleccionados});
                        },
                        undefined
                    ).set("defaultFocus","cancel")
                    .set("labels", {
                        ok: "Aceptar", cancel: "Cancelar"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-success");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-success");
                        btnAceptar.classList.add("btn-outline-success");
                    }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                    if (cancela) {
                        cancela.classList.remove("btn-danger");
                        cancela.classList.add("btn-outline-danger");
                    }
                }
            }
        ';

        if ($this->escritura) {
            $btnautorizar = $this->ponBoton($btnautorizar);
        } else {
            $btnautorizar = $this->vista->vwSoloLectura('AUTORIZAR -SOLO LECTURA');
        }

        $ListadoExcel = new stdClass();
        $ListadoExcel->id = 'ListadoExcel';
        $ListadoExcel->class = 'btn btn-outline-success me-1';
        $ListadoExcel->value = 'Excel';
        $ListadoExcel->icon = 'solid fa-file-excel  me-2';
        $ListadoExcel->click = '
            printCmp();
        ';

        $this->mixScript('
            function printCmp(){
                var form = $("<form>")
                .attr("action", "rep/excel/scp/afectaciones/rep_Compromisos.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                .append($("<input>").attr("type", "hidden").attr("name", "estado").val( $("#estado").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "mes").val( $("#mes").val()));
                $("body").append(form);

                form.submit();
                form.remove();
            }
        ');

        $ListadoExcel = $this->ponBoton($ListadoExcel);
        return $btnRegresarIndex . $btnnuevo . $btnautorizar . $ListadoExcel;
    }

    private function getOnSelectJs($prm)
    {
        $onSelect = '';
        switch ($prm->tipo) {
            case 'B':
                $onSelect = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"ObjetoGasto",
                        tipo:"' . $prm->tipo . '",
                        graph: $("#encProg").attr("graph"),
                        ' . ($prm->cmp_importe > 0 ? 'cmp_importe:' . $prm->cmp_importe . ',' : '') . '
                        filtro:"ModNomPartida",
                        cmp_id:"' . $this->prm->cmp_id . '"
                    },false);
                ';
                break;
            case 'I':
                $onSelect = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"ObjetoGasto",
                        tipo:"' . $prm->tipo . '",
                        graph: $("#encProg").attr("graph"),
                        stc_id:$("#stc_id").val(),
                        ' . ($prm->cmp_importe > 0 ? 'cmp_importe:' . $prm->cmp_importe . ',' : '') . '
                        filtro:"ModNomPartida",
                        cmp_id:"' . $prm->cmp_id . '"
                    },false);
                ';
                break;
            case 'J':
            case 'K':
            case 'F':
            case 'G':
                $onSelect = '
                    comunica("' . $this->pantalla . '",{
                        metodo:"ObjetoGasto",
                        tipo:"' . $prm->tipo . '",
                        graph: $("#encProg").attr("graph"),
                        stc_id:$("#stc_id").val(),
                        ' . ($prm->cmp_importe > 0 ? 'cmp_importe:' . $prm->cmp_importe . ',' : '') . '
                        filtro:"ModNomPartida",
                        cmp_id:"' . $prm->cmp_id . '"
                    },false);
                ';
                break;
        }
        return $onSelect;
    }
}
if (isset($_SESSION[_S]['prm']['S']['2']['3']['2']) && date('Y') <= APP_EJERCICIO) {
    new ControladorCompromiso();
} elseif (isset($_SESSION[_S]['prm']['S']['2']['3']['1']) && date('Y') > APP_EJERCICIO) {
    new ControladorCompromiso();
} else {
    WebResponse::sinPermisos();
}
