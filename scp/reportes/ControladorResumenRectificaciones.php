<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorResumenRectificaciones extends WebResponse
{
    //Constructor
    public $ur;

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        if (isset($_SESSION[_S]['prm']['S']['4']['5']['57'])) {
            switch ($this->metodo) {
                default:
                    $this->main();
                    break;
            }
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools('');

        $imprimir = new stdClass();
        $imprimir->id = 'imprimir';
        $imprimir->value = 'PDF';
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
        $tipo->sql = '
            SELECT
                tcl_id,
                tcl_nombre
            FROM cat_TipoClc';
        $tipo->label = 'Selecciona el tipo de CLC';
        $tipo->enRow = 1;
        $tipo->change = '
            if( $("#SelectAll").is(":checked") )
                $("#SelectAll").click();
        ';
        $tipo->search = true;

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

        $bxnombre = new stdClass();
        $bxnombre->id = 'bxnombre';
        $bxnombre->name = 'bxnombre';
        $bxnombre->label = 'Nombre';
        $bxnombre->class = 'limpiar';
        $bxnombre->placeholder = 'Ingresa el nombre';
        $bxnombre->enDiv = true;

        $bxrfc = new stdClass();
        $bxrfc->id = 'bxrfc';
        $bxrfc->name = 'bxrfc';
        $bxrfc->label = 'RFC';
        $bxrfc->class = 'limpiar';
        $bxrfc->placeholder = 'Ingresa el RFC';
        $bxrfc->enDiv = true;


        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->value = 'Regresar';

        $this->mixHtml('
            <form role="form" id="filtros" name="filtros" action="" method="post" target="_blank">
                <legend align="center"><b>Resumen de Rectificaciones</b></legend>
                <br>
                    <input type="checkbox" id="SelectAll" name="SelectAll" form="filtros"> Seleccionar Todas</input>
                    <br><br>
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-5 col-xs-5">
                        ' . $this->ponCombo($tipo) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $this->ponInput($inicio) . '
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $this->ponInput($termino) . '
                    </fieldset>
                </div>
                <br>
            </form>
        ');

        $this->agregaTools($this->ponBoton($imprimir) . ' ' . $this->ponBoton($exportar));

        $this->mixScript('
            $("input[name=tiporep]").click(function(){
                switch ($("input[name=tiporep]:checked").val()) {
                    case "1":
                        $("#filtros").attr("action","rep/excel/scp/reportes/ExportaResumenRectificaciones.php" );
                        console.info($(this).val());
                        break;
                    case "2":
                        $("#filtros").attr("action","rep/doc/scp/reportes/rep_resumenRectificaciones.php" );
                        console.info($(this).val());
                    break;
                }
            });

            $("#imprimir").click(function(){
                if(!validarFechasAnio($("#inicio").val(), $("#termino").val())){
                    alertify.alert("<b>Resumen de Rectificaciones</b>","El año de las fechas deber ser " +' . APP_EJERCICIO . ');
                    return false;
                }
                if(!validarFechas($("#inicio").val(), $("#termino").val())){
                    alertify.alert("<b>Resumen de Rectificaciones</b>","La fecha de termino no puede ser menor a la fecha de inicio");
                    return false;
                }
                if($("#SelectAll").is(":checked")){
                    $("#SelectAll").val("todas");
                    $("#tipo").val("");
                }else if(!$("#SelectAll").is(":checked")){
                    $("#SelectAll").val("");
                }
                if($("#inicio").val()=="" || $("#termino").val()==""){
                    alertify.alert("<b>Resumen de Rectificaciones</b>","Tienes que seleccionar el rango de fecha");
                }else{
                    $("#filtros").attr("action","rep/doc/scp/reportes/rep_resumenRectificaciones.php");
                    $("#filtros").submit();
                }
            });

            $("#exportar").click(function(){
                if(!validarFechasAnio($("#inicio").val(), $("#termino").val())){
                    alertify.alert("<b>Resumen de Rectificaciones</b>","El año de las fechas deber ser " +' . APP_EJERCICIO . ');
                    return false;
                }
                if(!validarFechas($("#inicio").val(), $("#termino").val())){
                    alertify.alert("<b>Resumen de Rectificaciones</b>","La fecha de termino no puede ser menor a la fecha de inicio");
                    return false;
                }
                if($("#SelectAll").is(":checked")){
                    $("#SelectAll").val("todas");
                    $("#tipo").val("");
                }else if(!$("#SelectAll").is(":checked")){
                    $("#SelectAll").val("");
                }
                if($("#inicio").val()=="" || $("#termino").val()==""){
                    alertify.alert("<b>Resumen de Rectificaciones</b>","Tienes que seleccionar el rango de fecha");
                }else{
                    $("#filtros").attr("action","rep/excel/scp/reportes/ExportaResumenRectificaciones.php");
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
                }else if(mesFechaInicio < mesFechaTermino){
                    return true;
                }else if(mesFechaInicio == mesFechaTermino){
                    if (diaFechaInicio <= diaFechaTermino)
                        return true;
                    else
                        return false;
                }
            }

            function validarFechasAnio(fechaInicio, fechaTermino){
                var arregloFechaInicio = fechaInicio.split("/");
                var arregloFechaTermino = fechaTermino.split("/");

                var anioFechaInicio = arregloFechaInicio[2];
                var anioFechaTermino = arregloFechaTermino[2];

                if(anioFechaInicio == ' . APP_EJERCICIO . ' && anioFechaTermino == ' . APP_EJERCICIO . '){
                    return true;
                } else {
                    return false;
                }
            }
        ');
    }
}

if (isset($_SESSION[_S]['prm']['S'][4][5])) {
    new ControladorResumenRectificaciones();
} else {
    WebResponse::sinPermisos();
}
