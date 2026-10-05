<?php

/**
 * Description of ControladorReporteDetalladoClcDeudaPublica
 *
 * @author Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Collaborators:
 * Update: 03-Mar-2025
 * Comment: -----
 * Objective:
 * Regla de Negocio:

 * Permits: Esta pantalla tiene permisos de lectura.
 * */

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/reportes/consultaDetalleClcDeudaPublica.php';

class ControladorReporteDetalladoClcDeudaPublica extends WebResponse
{
    public $pantalla;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/reportes/ControladorReporteDetalladoClcDeudaPublica.php';

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                switch ($this->metodo) {
                    case 'llenaTablaClcs':
                        $this->llenaTablaClcs();
                        break;
                    case 'limpiarPantalla':
                        $this->limpiarPantalla();
                        break;
                    default:
                        $this->main();
                        break;
                }
        }
        $this->returnJson();
    }

    public function main()
    {
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = 'me-2';
        $btnLimpiar->color = 'amarillo';
        $btnLimpiar->icon = 'solid fa-trash-can me-2';
        $btnLimpiar->click = '
            $("#inicio").val("");
            $("#termino").val("");
            comunica("' . $this->pantalla . '",{
                metodo: "limpiarPantalla",
                tipo: $("#tipo").val(),
                limpiar: true
            });
        ';

        $btnExportar = new stdClass();
        $btnExportar->id = 'expExcel';
        $btnExportar->color = 'verde';
        $btnExportar->text = 'Exportar';
        $btnExportar->value = 'Descargar';
        $btnExportar->type = 'button';
        $btnExportar->class = 'me-2';
        $btnExportar->color = 'verde';
        $btnExportar->icon = 'a-solid fa-file-excel me-1';
        $btnExportar->click = '
            if($("#inicio").val() == "" && $("#termino").val() == ""){
                notify("Introduzca un rango de fechas","warning");
                return false;
            }

            if(!validarFechas($("#inicio").val(), $("#termino").val())){
                notify("La fecha de termino no puede ser menor a la fecha de inicio", "warning");
                return false;
            }

            $("#filtros").attr("action","rep/excel/scp/reportes/exp_reporteDetalladoClcDeudaPublica.php" );
            $("#filtros").submit();

            function validarFechas(fechaInicio, fechaTermino){
                var arregloFechaInicio = fechaInicio.split("/");
                var arregloFechaTermino = fechaTermino.split("/");

                var anioFechaInicio = arregloFechaInicio[2];
                var mesFechaInicio = arregloFechaInicio[1];
                var diaFechaInicio = arregloFechaInicio[0];

                var anioFechaTermino = arregloFechaTermino[2];
                var mesFechaTermino = arregloFechaTermino[1];
                var diaFechaTermino = arregloFechaTermino[0];

                if(anioFechaInicio < anioFechaTermino){
                    return true;
                }else if(anioFechaInicio > anioFechaTermino){
                    return false;
                }else{
                    if(mesFechaInicio < mesFechaTermino || mesFechaInicio == mesFechaTermino){
                        return true;
                    }else if(mesFechaInicio > mesFechaTermino){
                        return false;
                    }
                }
            }
        ';

        $this->agregaTools($this->ponBoton($btnLimpiar));
        $this->agregaTools($this->ponBoton($btnExportar));

        $tipo = new stdClass();
        $tipo->id = 'tipo';
        $tipo->name = 'tipo';
        $tipo->width = '400px';
        $tipo->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc WHERE tcl_id = "J"';
        $tipo->label = 'Tipo de CLC';
        $tipo->enRow = 1;
        $tipo->change = '
            if( $("#SelectAll").is(":checked") )
                $("#SelectAll").click();
        ';

        /*INICIO DEL PERIODO*/
        $inicio = new stdClass();
        $inicio->id = 'inicio';
        $inicio->name = 'inicio';
        $inicio->class = 'fecha';
        $inicio->label = 'Del';
        $inicio->placeholder = 'Fecha inicial';
        $inicio->enDiv = true;
        $inicio->formato = 'fecha';
        $inicio->labelClass = '_32 txtL';
        $inicio->required = true;

        /*TERMINO DEL PERIODO*/
        $termino = new stdClass();
        $termino->id = 'termino';
        $termino->name = 'termino';
        $termino->class = 'fecha';
        $termino->label = 'Al';
        $termino->placeholder = 'Fecha final';
        $termino->enDiv = true;
        $termino->formato = 'fecha';
        $termino->labelClass = '_32 txtL';
        $termino->required = true;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar';
        $btnBuscar->name = 'btnBuscar';
        $btnBuscar->tex = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->type = 'button';
        $btnBuscar->class = 'me-2';
        $btnBuscar->color = 'gris';
        $btnBuscar->icon = 'fa fa-search me-1';
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaClcs",
                tipo: $("#tipo").val(),
                inicio: $("#inicio").val(),
                termino: $("#termino").val()
            });
        ';

        $imprimir = new stdClass();
        $imprimir->value = 'Imprimir';
        $imprimir->id = 'imprimir';
        $imprimir->name = 'imprimir';
        $imprimir->class = 'btn btn-test';
        $imprimir->color = 'gris';
        $imprimir->icon = 'solid fa-print me-2';

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->value = '&nbsp;&nbsp;Regresar';
        $btnRegresar->class = 'btn btn-test';
        $btnRegresar->icon = 'glyphicon glyphicon-hand-left';

        $tabla = new stdClass();
        $tabla->id = 'tbl-clc';
        $tabla->titulo = 'LISTADO DE CLC´s';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClcs';
        $tabla->comunica->tipo = '{$("#tipo").val()}';
        $tabla->comunica->inicio = '{$("#inicio").val()}';
        $tabla->comunica->termino = '{$("#termino").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR NOMBRE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AFECTACION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NUMERO DE CONTROL';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CERECO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC CERECO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODO DE PAGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARTIDA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARTIDA GENERICA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARTIDA ESPECIFICA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLAVE FINANCIAMIENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC FECHA DE ELABORACION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RECEPTORA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ORDENANTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PERCEPCION BRUTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PERCEPCION IVA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PERCEPCION NETO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RETENCION NETO';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'afectacion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'numcontrol';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cereco';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'modo_pago';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'partida';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'partida_generica';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'partida_especifica';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clave_financiamiento';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'referencia';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_fecha_elaboracion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rfc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'receptora';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ordenante';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'percepcion_bruto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'percepcion_iva';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'percepcion_neto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'retencion_neto';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <form role="form" id="filtros" name="filtros" action="" method="post" target="_blank">
                <div class="alert">
                    <h2>Reporte Detallado de CLC´S Deuda Pública<h2>
                </div>
                <div class="row">
                    <fieldset class="col-md-3">
                        ' . $this->ponCombo($tipo) . '
                    </fieldset>
                    <fieldset class="col-md-3">
                        ' . $this->ponInput($inicio) . '
                    </fieldset>
                    <fieldset class="col-md-3">
                        ' . $this->ponInput($termino) . '
                    </fieldset>
                    <div class="col-md-3 pt-3">                        
                        ' . $this->ponBoton($btnBuscar) . '
                    </div>
                </div>
            </form>
            </br>
            </br>
            <div class="row">
                <fieldset class="cold-md-12 col-xs-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ');
    }

    private function llenaTablaClcs()
    {
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $this->prm->inicio = isset($this->prm->inicio) ? $this->prm->inicio : '';
        $this->prm->termino = isset($this->prm->termino) ? $this->prm->termino : '';

        if ($this->prm->inicio == '' && $this->prm->termino == '') {
            $this->mixScript('notify("Introduzca un rango de fechas", "warning")');
            return;
        }

        if ($this->prm->inicio == '' || $this->prm->termino == '') {
            if ($this->prm->inicio == '') {
                $this->mixScript('notify("Ingresa la fecha Inicial", "warning")');
                return;
            } elseif ($this->prm->termino == '') {
                $this->mixScript('notify("Ingresa la fecha Final", "warning")');
                return;
            }
        }

        $inicio = explode('/', $this->prm->inicio);
        $termino = explode('/', $this->prm->termino);
        $inicio = $inicio[2] . '-' . $inicio[1] . '-' . $inicio[0];
        $termino = $termino[2] . '-' . $termino[1] . '-' . $termino[0];
        $fechaInicio = strtotime($inicio);
        $fechaTermino = strtotime($termino);
        if ($fechaTermino < $fechaInicio) {
            $this->mixScript('
                notify("La fecha de termino no puede ser menor a la fecha de inicio", "warning")
            ');
        } else {
            $fechaMin = DateTime::createFromFormat('d/m/Y', $this->prm->inicio);
            $fechaMin = $fechaMin->format('Y-m-d');
            $fechaMax = DateTime::createFromFormat('d/m/Y', $this->prm->termino);
            $fechaMax = $fechaMax->format('Y-m-d');
            $params[':tipo'] = $this->prm->tipo;
            if ($fechaMin == $fechaMax) {
            } else {
                $params[':fmin'] = $fechaMin;
                $params[':fmax'] = $fechaMax;
            }

            $sql = consultaDetalleClcDeudaPublica($this->prm->tipo, $this->prm->inicio, $this->prm->termino, 'Si');
            $this->setSql($sql);
            $this->setParams($params);
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();

            $nav->idTabla = 'tbl-clc';
            $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
            $this->wrLlenaTabla($nav);
        }
    }

    private function limpiarPantalla()
    {
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $this->prm->limpiar = isset($this->prm->limpiar) ? $this->prm->limpiar : '';

        if ($this->prm->limpiar == true) {
            $params[':tipo'] = $this->prm->tipo;

            $sql = consultaDetalleClcDeudaPublica('J', '00/00/2025', '00/00/2025', 'Si');
            $this->setSql($sql);
            $this->setParams($params);
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();

            $nav->idTabla = 'tbl-clc';
            $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
            $this->wrLlenaTabla($nav);
        }
    }
}

if (isset($_SESSION[_S]['prm']['S']['4']['9']['1'])) {
    new ControladorReporteDetalladoClcDeudaPublica();
} else {
    WebResponse::sinPermisos();
}
