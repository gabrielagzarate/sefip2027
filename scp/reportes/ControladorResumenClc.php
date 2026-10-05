<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorResumenClc extends WebResponse
{
    public $ur;

    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();

        //Instanciando la Ur
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

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
        $this->agregaTools('');

        $imprimir = new stdClass();
        $imprimir->id = 'imprimir';
        $imprimir->value = 'PDF';
        $imprimir->type = 'submit';
        $imprimir->class = 'btn btn-outline-danger me-1';
        $imprimir->icon = 'btn fa-solid fa-file-pdf me-2';
        $imprimir->form = 'filtros';
        $imprimir->type = 'submit';

        $exportar = new stdClass();
        $exportar->id = 'exportar';
        $exportar->value = 'Excel';
        $exportar->icon = 'btn fa-solid fa-file-excel me-2';
        $exportar->class = 'btnImp btn-outline-success';
        $exportar->form = 'filtros';
        $exportar->type = 'submit';

        $tipo = new stdClass();
        $tipo->id = 'tipo';
        $tipo->name = 'tipo';
        $tipo->width = '400px';
        $tipo->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc';
        $tipo->label = 'Selecciona el tipo de CLC';
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

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->value = '&nbsp;&nbsp;Regresar';
        $btnRegresar->class = 'btn btn-test';
        $btnRegresar->icon = 'glyphicon glyphicon-hand-left';

        $this->mixHtml('
            <form role="form" id="filtros" name="filtros" action="" method="post" target="_blank">
            <br>
                <legend align="center"><b>Resumen de CLC´S</b></legend>
                <input type="checkbox" id="SelectAll" name="SelectAll" form="filtros"> Seleccionar Todas</input>
                <br><br>
                <div class="row">
                    <fieldset class="col-md-5">
                        ' . $this->ponCombo($tipo) . '
                    </fieldset>
                    <fieldset class="col-md-3">
                        ' . $this->ponInput($inicio) . '
                    </fieldset>
                    <fieldset class="col-md-3">
                        ' . $this->ponInput($termino) . '
                    </fieldset>
                </div>
                <br>
            </form>
        ');


        $this->agregaTools($this->ponBoton($imprimir) . ' ' . $this->ponBoton($exportar));

        $this->mixScript('
            $("#imprimir").click(function(){
                if(!validarFechas($("#inicio").val(), $("#termino").val())){
                    alertify.alert("<b>Resumen de CLC´s</b>","El año de las fechas deber ser " +' . APP_EJERCICIO . ');
                    return false;
                }
                if($("#SelectAll").is(":checked")){
                    $("#SelectAll").val("todas");
                    $("#tipo").val("");
                }else if(!$("#SelectAll").is(":checked")){
                    $("#SelectAll").val("");
                }
                if($("#inicio").val()=="" || $("#termino").val()==""){
                    alertify.alert("<b>Resumen de CLC´s</b>","Tienes que seleccionar el rango de fecha");
                }else{
                    $("#filtros").attr("action","rep/doc/scp/reportes/rep_resumenClc.php");
                    $("#filtros").submit();
                }
                
            });

            $("#exportar").click(function(){
                if(!validarFechas($("#inicio").val(), $("#termino").val())){
                    alertify.alert("<b>Resumen de CLC´s</b>","El año de las fechas deber ser " +' . APP_EJERCICIO . ');
                    return false;
                }
                if($("#SelectAll").is(":checked")){
                    $("#SelectAll").val("todas");
                    $("#tipo").val("");
                }else if(!$("#SelectAll").is(":checked")){
                    $("#SelectAll").val("");
                }
                if($("#inicio").val()=="" || $("#termino").val()==""){
                    alertify.alert("<b>Resumen de CLC´s</b>","Tienes que seleccionar el rango de fecha");
                }else{
                    $("#filtros").attr("action","rep/excel/scp/reportes/exp_resumenClc.php");
                    $("#filtros").submit();
                }              
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
        ');
    }
}

if (isset($_SESSION[_S]['prm']['S']['4']['2']['1'])) {
    new ControladorResumenClc();
} else {
    WebResponse::sinPermisos();
}
