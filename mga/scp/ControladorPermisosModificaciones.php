<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

class ControladorPermisosModificaciones extends WebResponse
{
    public $ssc_id;
    public $escritura;

    public function __construct()
    { //Constructor
        parent::__construct();

        $this->ssc_id = $_SESSION[_S]['ssc_id']; // SUBSECRETARIA DEL USUARIO
        //VALIDAR PERMISO DE ESCRITURA
        $this->escritura = isset($_SESSION[_S]['prm']['M']['7']['1']['2']);

        switch ($this->metodo) {
            case 'abcPermisos':
                $this->abcPermisos();
                break; //METODO QUE REGISTRA LOS PERMISOS
            case 'ponElementos':
                $this->ponElementos();
                break; //METODO QUE MUESTRA LAS OPCIONES A HABILITAR/DESHABILITAR RH,RM
            case 'ponOpciones':
                $this->ponOpciones();
                break; //METODO QUE MUESTRA LAS OPCIONES A HABILITAR/DESHABILITAR
            case 'ponModal':
                $this->ponModal();
                break; //METODO QUE MUESTRA EL LISTADO DE OPCIONES HABILITADAS/DESHABILITADAS DE MODIFICAIONES PRESUPUESTALES
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        //UNIDAD RESPONSABLE
        $combo = new stdClass();
        $combo->id = 'unidadResponsable';
        $combo->label = 'Unidad Responsable';
        $combo->multiselec = true;
        $combo->mensaje = true;
        $combo->sql = 'SELECT ur.ur_clave,ur.ur_nombre,ur.gpo_id,ur.ur_id
                        FROM cat_UnidadResponsable ur
                            JOIN cat_Usuario u USING(gpo_id,ur_id)
                        WHERE CONCAT(gpo_id,ur_id) NOT IN (115)
                        GROUP BY gpo_id,ur_id;';
        $combo->comoAtr = 'gpo_id,ur_id';
        $combo->search = true;
        $combo->enDiv = true;

        //BOTON HABILITAR EL MODULO
        $habilitar = new stdClass();
        $habilitar->title = 'Habilitar el modulo';
        $habilitar->id = 'habilitar';
        $habilitar->icon = 'fa fa-circle-check me-2';
        $habilitar->type = 'button';
        $habilitar->value = 'Habilitar';
        $habilitar->class = 'btn-outline-success me-1';

        //BOTON DESHABILITAR EL MODULO
        $deshabilitar = new stdClass();
        $deshabilitar->title = 'Deshabilitar el modulo';
        $deshabilitar->id = 'deshabilitar';
        $deshabilitar->type = 'button';
        $deshabilitar->icon = 'fa fa-circle-half-stroke me-2';
        $deshabilitar->value = 'Deshabilitar';
        $deshabilitar->class = 'btn-outline-danger me-1';

        //DEFINE VARIABLE RECURSOS HUMANOS
        $RecHumanosRecMat = '';
        //VERIFICA EL TIPO DE SUBSECRETARIA, SI ES DE 3-EGRESOS MUESTRA LAS OPCIONES DE RECURSOS HUMANOS Y RECURSOS MATERIALES
        switch ($this->ssc_id) {
            case 3: //EGRESOS
                $RecHumanosRecMat = '
                    <fieldset  class="col-md-4 col-xs-6">
                            <input type="radio" name="es" id="es" value="C"/> <b>Dirección de Recursos Humanos (normativa)</b>
                    </fieldset>
                    <fieldset  class="col-md-4 col-xs-6">
                            <input type="radio" name="es" id="es" value="D"/> <b>Dirección de Recursos Materiales (normativa)</b>
                    </fieldset>
                ';
                break;
            case 2: //PLANEACION
                $RecHumanosRecMat = '';
                break;
        }
        //MUESTRA LA OPCION DE TODAS, EL COMBO DE RECURSOS HUMANOS Y EN SU CASO LAS OPCIONES DE RH Y RM
        $this->mixHtml('
            <div class="row mb-4">
                <label>Permisos de Modificaciones presupuestales por Unidad Responsable</label>
                <fieldset class="col-md-4 col-xs-12">
                    <input type="checkbox" name="todas" id="todas" value=""/> <b>Todas</b>
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset  class="col-md-4 col-xs-6">
                    ' . $this->ponCombo($combo) . '
                </fieldset>
                ' . $RecHumanosRecMat . '
            </div>
            <div id="ponerElementos" class="row">
            </div>
            <div class="row">
                <fieldset  class="col-md-6 col-xs-12">
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="informacion" class="col-md-12 col-xs-6">

                </fieldset>
            </div>
        ');

        //AGREGA LOS BOTONES A LA BARRA DE HERRAMIENTAS SOLO PARA USUARIOS CON PERMISO DE ESCRITURA
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($habilitar) . ' ' . $this->ponBoton($deshabilitar));
        }

        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
            $("#habilitar").removeClass("btn-default");
            $("#deshabilitar").removeClass("btn-default");
            var selec = new Array();
            function seleccionados(){
                var tf="";
                var mod="";
                var tr="";
                var ta="";
                selec = [];
                if($("input[name=\"es\"]").is(":checked")){
                    switch($("input[name=\"es\"]:checked").val()){
                        case "C":
                                tf="A";
                                mod="E";
                                selec.push({"mmd_id":"E","tfm_id":"A"});
                        break;
                        case "D":
                                tf="B";
                                mod="E";
                                selec.push({"mmd_id":"E","tfm_id":"B"});
                        break;
                    }

                }else{
                    var a=0;
                    var b=0;
                    var c=0;
                    mod = (($("input[name=\"mmd_id\"]:checked").attr("mmd_id")!=undefined) ? $("input[name=\"mmd_id\"]:checked").attr("mmd_id") :"");
                    subs = (($("input[name=\"mod_Subsecretaria\"]:checked").attr("mod_Subsecretaria")!=undefined) ? $("input[name=\"mod_Subsecretaria\"]:checked").attr("mod_Subsecretaria") :"");
                    tipof = (($("input[name=\"tmd_id\"]:checked").attr("tmd_id")!=undefined) ? $("input[name=\"tmd_id\"]:checked").attr("tmd_id") :"");
                    $("input[name=\"tfm_id[]\"]:checked").each(function(){
                        if(a!=0) tf+=",";
                        tf=tf+"\'"+$(this).attr("tfm_id")+"\'";
                        a++;
                    });
                    $("input[name=\"cff_id[]\"]:checked").each(function(){
                        if(b!=0) tr+=",";
                        tr = tr+"\'"+$(this).attr("cff_id")+"\'";
                        b++;
                    });
                    $("input[name=\"tmd_id[]\"]:checked").each(function(){
                        if(c!=0) ta+=",";
                        ta = ta+"\'"+$(this).attr("tmd_id")+"\'";
                        c++;
                    });
                    if(mod!="" && tf!="" && tr!="" && subs!="")
                        selec.push({"mmd_id":mod,"mod_Subsecretaria":subs,"tfm_id":tf,"cff_id":tr,"tmd_id":ta});
                }
                return selec;
            }

            function objPermisos(){
                var stringObj = "[";
                for(var index in selec) {
                    if(index>0)
                        stringObj += ",";
                    stringObj += "{";
                    var obj = selec[index];
                    c = 0;
                    for(var i in obj) {
                        if( c > 0 )
                            stringObj += ",";
                        stringObj += \'"\' + i + \'"\' + \':"\' + obj[i] + \'"\';
                        c++;
                    }
                        console.log();
                    stringObj += "}";
                }
                stringObj += "]";
                return stringObj;
            }

            $("#habilitar").click(function(){
                seleccionados();
                var ban=1;

                if($("#inicio").val()!=null){
                    if($("#inicio").val()!="" && $("#final").val()!=""){
                        if(!validarFechas($("#inicio").val(), $("#final").val(), $("#horaInicio").val(), $("#horaFin").val())){
                            ban=0;
                            notify("La fecha de termino no puede ser menor a la fecha de inicio", "warning");
                            return false;
                        }
                    }
                }

                if(ban==1){
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcPermisos",
                        permisos:objPermisos(),
                        sts_id:1,
                        todas:(($("input[name=todas]").val()==1) ? $("input[name=todas]").val():$("#unidadResponsable").val()),
                        normativa:(($("input[name=\"es\"]").is(":checked")) ? $("input[name=\"es\"]:checked").val():0),
                        fecIni:(($("#inicio").val()!="") ? $("#inicio").val():0),
                        horaIni:(($("#horaInicio").val()!="") ? $("#horaInicio").val():0),
                        fecFin:(($("#final").val()!="") ? $("#final").val():0),
                        horaFin:(($("#horaFin").val()!="") ? $("#horaFin").val():0)
                    });
                }
            });

            $("#deshabilitar").click(function(){
                seleccionados();

                comunica("' . $this->pantalla . '",{
                    metodo:"abcPermisos",
                    permisos:objPermisos(),
                    sts_id:2,
                    todas:(($("input[name=todas]").val()==1) ? $("input[name=todas]").val():$("#unidadResponsable").val()),
                    normativa:(($("input[name=\"es\"]").is(":checked")) ? $("input[name=\"es\"]:checked").val():0)
                });
            });

            $("input[name=todas]").click(function(){
                if($(this).is(":checked")==true){
                    $(this).val(1);
                    $("#unidadResponsable").val("").change();
                    $("#unidadResponsable").attr("disabled",true);
                    comunica("' . $this->pantalla . '",{
                            metodo:"ponElementos",
                            ur:1
                    });
                }else{
                    $(this).val(0);
                    $("#unidadResponsable").attr("disabled",false);
                }
            });

            $("#unidadResponsable").change(function(){
                $("input[name=\"es\"]:checked").prop("checked",false);
                comunica("' . $this->pantalla . '",{
                    metodo:"ponElementos",
                    ur:$(this).val()
                });
            });

            $("input[name=es]").change(function(){
                $("input[name=todas]").prop("checked",false);
                $("#unidadResponsable").attr("disabled",true);
                $("#unidadResponsable").val("");
                comunica("' . $this->pantalla . '",{
                    metodo:"ponElementos",
                    ur:118,
                    normativa:$("input[name=\"es\"]:checked").val()
                });
            });

            $("input[name=mmd_id]").change(function(){

                if($("input[name=mmd_id]:checked").attr("mmd_id")=="I"){
                    $("input[name=mod_Subsecretaria]").prop("disabled",true);
                    $("input[name=mod_Subsecretaria][value=\'1\']").prop("checked",true);
                }else{
                    $("input[name=mod_Subsecretaria]").attr("disabled",false);
                }

                comunica("' . $this->pantalla . '",{
                    metodo:"ponOpciones",
                    modalidad:$("input[name=mmd_id]:checked").val()
                });
            });

            $("input[name=tiempo]").click(function(){
                if($(this).is(":checked")==true){
                    $(this).val(1);
                }else{
                    $(this).val(0);
                    $("#horaInicio").val("00:00")
                    $("#horaFin").val("00:00");
                    $("#inicio").val("");
                    $("#final").val("");
                }
            });

            function validarFechas(fechaInicio, fechaTermino, horaInicio, horaFin){
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
                    if(mesFechaInicio < mesFechaTermino){
                        return true;
                    }else if(mesFechaInicio > mesFechaTermino){
                        return false;
                    }else{
                        if(diaFechaInicio < diaFechaTermino){
                            return true;
                        }else{
                            if(horaInicio < horaFin)
                                return true;
                            else
                                return false;
                        }
                    }
                }
            }
        ');
    }

    //METODO QUE MUESTRA LAS OPCIONES A HABILITAR/DESHABILITAR RH,RM
    private function ponElementos()
    {
        $this->setContenedor('ponerElementos');
        $modalidad = $tipoFolio = $tipoRecurso = $tipoAdecuacion = $sub = '';

        $inicio = new stdClass();
        $inicio->id = 'inicio';
        $inicio->class = 'fecha';
        $inicio->placeholder = 'Fecha Inicial';
        $inicio->enDiv = true;
        $inicio->formato = 'fecha';
        $inicio->labelClass = '_32 txtL';
        $inicio->required = true;
        $inicio->value = date('d/m/Y');

        $final = new stdClass();
        $final->id = 'final';
        $final->class = 'fecha';
        $final->placeholder = 'Fecha Final';
        $final->enDiv = true;
        $final->formato = 'fecha';
        $final->labelClass = '_32 txtL';
        $final->required = true;
        $final->value = date('d/m/Y');

        $btnVerListado = new stdClass();
        $btnVerListado->id = 'verListado';
        $btnVerListado->class = 'btn-outline-secondary me-1';
        $btnVerListado->icon = 'search me-2';
        $btnVerListado->type = 'button';
        $btnVerListado->value = '&nbsp;&nbsp;Información';

        switch ($this->ssc_id) {
            case 3: //Egresos
                $sub = '
                    <input type="radio" id="egresos" name="mod_Subsecretaria" mod_Subsecretaria="1" value= "1" checked/>
                    <b>Egresos</b><br>
                    ';
                break;
            case 2: //planeación
                $sub = '
                    <input type="radio" id="planeacion" name="mod_Subsecretaria" mod_Subsecretaria="2" value = "2" checked/>
                    <b>Planeación</b><br>
                ';
                break;
        }

        if (isset($this->prm->ur) && $this->prm->ur != '') {
            switch ($this->prm->ur) {
                case 118:
                    if (isset($this->prm->normativa)) {
                        switch ($this->prm->normativa) {
                            case 'C':
                                $modalidad = '<b>Externa</b>';
                                $tipoFolio = '<b>Servicios personales</b>';
                                $sub = '
                                        <input type="radio" id="egresos" name="mod_Subsecretaria" mod_Subsecretaria="1" value= "1" checked/> <b>Egresos</b><br>
                                ';
                                break;
                            case 'D':
                                $modalidad = '<b>Externa</b>';
                                $tipoFolio = '<b>Gastos de operación</b>';
                                $sub = '
                                        <input type="radio" id="egresos" name="mod_Subsecretaria" mod_Subsecretaria="1" value= "1" checked/> <b>Egresos</b><br>
                                ';
                                break;
                        }
                    } else {
                        switch ($this->ssc_id) {
                            case 3: //Egresos

                                $modalidad = '
                                    <input type="radio" id="mmd_id" name="mmd_id" mmd_id="I" /> <b>Interna</b><br>
                                    <input type="radio" id="mmd_id" name="mmd_id" mmd_id="E" checked/> <b>Externa</b><br>
                                ';
                                break;
                            case 2: //planeación
                                $modalidad = '
                                    <input type="radio" id="mmd_id" name="mmd_id" mmd_id="E" checked/> <b>Externa</b><br>
                                ';
                                break;
                        }

                        $tipoFolio = '
                            <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="A" /> <b>Servicios personales</b><br>
                            <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="B" /> <b>Gastos de operación</b><br>
                            <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="C" /> <b>Inversión pública</b><br>
                        ';
                        $tipoRecurso = '
                            <tr>
                                <td><b>Fuente de Financiamiento</b></td>
                                <td id="recurso">
                                    <input type="checkbox" id="cff_id" name="cff_id[]" cff_id="A"/> <b>No Etiquetado</b><br>
                                    <input type="checkbox" id="cff_id" name="cff_id[]" cff_id="B"/> <b>Etiquetado</b><br>
                                </td>
                            </tr>
                        ';
                        $tipoAdecuacion = '
                            <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="A" checked/> <b>TRASPASO INTRAINSTITUCIONAL</b><br>
                            <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="B" checked/> <b>TRASPASO INTERINSTITUCIONAL</b><br>
                            <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="C" checked/> <b>RECALENDARIZACIÓN</b><br>
                            <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="D" checked/> <b>AMPLIACIÓN</b><br>
                            <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="E" checked/> <b>REDUCCIÓN</b><br>
                        ';
                    }
                    break;
                default:
                    switch ($this->ssc_id) {
                        case 3: //Egresos

                            $modalidad = '
                                <input type="radio" id="mmd_id" name="mmd_id" mmd_id="I" /> <b>Interna</b><br>
                                <input type="radio" id="mmd_id" name="mmd_id" mmd_id="E" checked/> <b>Externa</b><br>
                            ';
                            break;
                        case 2: //planeación
                            $modalidad = '
                                <input type="radio" id="mmd_id" name="mmd_id" mmd_id="E" checked/> <b>Externa</b><br>
                            ';
                            break;
                    }
                    $tipoFolio = '
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="A" /> <b>Servicios personales</b><br>
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="B" /> <b>Gastos de operación</b><br>
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="C" /> <b>Inversión pública</b><br>
                    ';
                    $tipoRecurso = '
                        <tr>
                            <td><b>Fuente de Financiamiento</b></td>
                            <td id="recurso">
                                <input type="checkbox" id="cff_id" name="cff_id[]" cff_id="A"/> <b>No Etiquetado</b><br>
                                <input type="checkbox" id="cff_id" name="cff_id[]" cff_id="B"/> <b>Etiquetado</b><br>
                            </td>
                        </tr>
                    ';
                    $tipoAdecuacion = '
                        <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="A" checked/> <b>TRASPASO INTRAINSTITUCIONAL</b><br>
                        <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="B" checked/> <b>TRASPASO INTERINSTITUCIONAL</b><br>
                        <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="C" checked/> <b>RECALENDARIZACIÓN</b><br>
                        <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="D" checked/> <b>AMPLIACIÓN</b><br>
                        <input type="checkbox" id="tmd_id" name="tmd_id[]" tmd_id="E" checked/> <b>REDUCCIÓN</b><br>
                    ';
                    break;
            }

            $this->mixHtml('
                <fieldset id="elementos" class="col-md-12 col-xs-12">
                    <input type="hidden" id="todas" value=""/>
                    <input type="hidden" id="normativa" value=""/>
                    ' . $this->ponBoton($btnVerListado) . '<br/><br/>
                    <table id="tbl-encuadre" class="table table table-bordered table-hover" style="width:100%;">
                        <tr>
                            <td><b>Modalidad de adecuación</b></td>
                            <td id="modalidad">
                                ' . $modalidad . '
                            </td>
                        </tr>
                        <tr>
                            <td><b>Subsecretaría</b></td>
                            <td id="subsecretaria">
                                ' . $sub . '
                            </td>
                        </tr>
                        <tr>
                            <td><b>Tipo de Folio</b></td>
                            <td id="tipo">
                                ' . $tipoFolio . '
                            </td>
                        </tr>
                        ' . $tipoRecurso . '
                        <tr>
                        </tr>
                            <td><b>Tipo de Adecuacion</b></td>
                            <td id="tipoAdec">
                                ' . $tipoAdecuacion . '
                            </td>
                        <tr>
                            <td><b>Fecha inicial</b> (únicamente aplica para la apertura del módulo)</td>
                            <td id="fecha">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        ' . $this->ponInput($inicio) . '
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        <select id="horaInicio" name="hor">
            ');
            for ($i = 0; $i < 24; $i++) {
                $h1 = sprintf('%02d', $i) . ':00';
                $h2 = sprintf('%02d', $i) . ':30';
                $this->mixHtml('
                    <option value="' . $h1 . '">' . $h1 . '</option>
                    <option value="' . $h2 . '">' . $h2 . '</option>');
            }
            $this->mixHtml('
                                        </select>
                                        Hora con respecto a la zona horaria (GMT -6.0)
                                    </fieldset>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Fecha final</b> (únicamente aplica para la apertura del módulo)</td>
                            <td id="fecha">
                                <div class="row">
                                    <fieldset class="col-md-6 col-xs-6">
                                        ' . $this->ponInput($final) . '
                                    </fieldset>
                                    <fieldset class="col-md-6 col-xs-6">
                                        <select id="horaFin" name="hor">
            ');
            for ($i = 0; $i < 24; $i++) {
                $h1 = sprintf('%02d', $i) . ':00';
                $h2 = sprintf('%02d', $i) . ':30';
                $this->mixHtml('
                    <option value="' . $h1 . '">' . $h1 . '</option>
                    <option value="' . $h2 . '">' . $h2 . '</option>');
            }
            $this->mixHtml('
                                        </select>
                                        Hora con respecto a la zona horaria (GMT -6.0)
                                    </fieldset>
                                </div>
                            </td>
                        </tr>
                    </table>
                </fieldset><br/>
            ');

            $this->mixScript('
                $("#verListado").removeClass("btn-default");
                $("input[name=mmd_id]").change(function(){

                    if($("input[name=mmd_id]:checked").attr("mmd_id")=="I"){
                        $("input[name=mod_Subsecretaria]").prop("disabled",true);
                        $("input[name=mod_Subsecretaria][value=\'1\']").prop("checked",true);
                    }else{
                        $("input[name=mod_Subsecretaria]").attr("disabled",false);
                    }

                    comunica("' . $this->pantalla . '",{
                        metodo:"ponOpciones",
                        modalidad:$("input[name=mmd_id]:checked").attr("mmd_id")
                    });
                });

                $("#verListado").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"ponModal",
                        todas:(($("input[name=todas]").val()==1) ? $("input[name=todas]").val():$("#unidadResponsable").val()),
                        normativa:(($("input[name=\"es\"]").is(":checked")) ? $("input[name=\"es\"]:checked").val():0)
                    });
                });
            ');
        }
    }

    //METODO QUE MUESTRA EL LISTADO DE OPCIONES HABILITADAS/DESHABILITADAS DE MODIFICAIONES PRESUPUESTALES
    private function ponModal()
    {
        $this->setContenedor('informacion');
        $params = [];
        $ur = ' ';
        if ($this->prm->todas != '' || $this->prm->normativa == 'C' || $this->prm->normativa == 'D') {
            if ($this->prm->normativa == 'C' || $this->prm->normativa == 'D') {
                $ur = ' WHERE gpo_id=1 and ur_id=18 and omo_id=? ';
                $params = [$this->prm->normativa];
            } else {
                $gpo_id = ((isset($this->prm->todas) && $this->prm->todas == 1) ? '' : substr($this->prm->todas, 0, 1));
                $ur_id = ((isset($this->prm->todas) && $this->prm->todas == 1) ? '' : substr($this->prm->todas, 1, 2));

                switch ($this->ssc_id) {
                    case 3: //Egresos
                        if (isset($this->prm->todas) && $this->prm->todas == 1) {
                            $ur = ' WHERE mod_subsecretaria=1 ';
                        } else {
                            $ur = " WHERE gpo_id=? and ur_id=? and omo_id='A' AND mod_subsecretaria=1";
                            $params = [$gpo_id, $ur_id];
                        }
                        break;
                    case 2: //planeación
                        if (isset($this->prm->todas) && $this->prm->todas == 1) {
                            $ur = ' WHERE mod_subsecretaria=2 ';
                        } else {
                            $ur = " WHERE gpo_id=? and ur_id=? and omo_id='A' AND mod_subsecretaria=2";
                            $params = [$gpo_id, $ur_id];
                        }
                        break;
                }
            }
            $this->conectar();
            $sql = '
                SELECT omo_id,gpo_id,ur_id,ur_nombre,mmd_nombre,mod_subsecretaria,
                    tfm_nombre,cff_nombre,tm.tmd_nombre,p.sts_id,pum_fechaInicial,
                    pum_fechaFinal
                FROM cat_PermisosUrModificaciones p
                    INNER JOIN cat_ModalidadModificacion m USING(mmd_id)
                    INNER JOIN cat_TipoModificacion tm USING(tmd_id)
                    INNER JOIN cat_TipoFolioModificacion tfm USING(tfm_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_ClasificadorFuenteFinanciamiento tr USING(cff_id)
                    INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
                    ' . $ur . '
                ORDER BY gpo_id,ur_id,omo_id,mmd_id,tfm_id,cff_id;
            ';
            $this->setSql($sql);
            $this->consultar($params);
            $this->desconectar();

            $modal = '<div id="myModal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                        <div class="modal-dialog shadow-lg modal-xl">
                            <br /><br /><br /><br />
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">Listado de opciones habilitadas/deshabilitadas de modificaciones presupuestales</h4>
                                    <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                        <i class="fa fa-xmark"></i>
                                    </button>

                                </div>
                                <div class="modal-body" style="overflow-x:auto;">
                                    <table id="tbl-listado" class="table table table-bordered table-hover" style="width:100%;">
                                        <tr>
                                            <th class="success" style="text-align:center">Unidad Responsable</th>
                                            <th class="success" style="text-align:center">Modalidad</th>
                                            <th class="success" style="text-align:center">Subsecretaria</th>
                                            <th class="success" style="text-align:center">Tipo de folio</th>
                                            <th class="success" style="text-align:center">Fuente de Financiamiento</th>
                                            <th class="success" style="text-align:center">Tipo de Adecuación Presupuestaria</th>
                                            <th class="success" style="text-align:center">Habilitado</th>
                                            <th class="success" style="text-align:center">Fecha Inicial</th>
                                            <th class="success" style="text-align:center">Fecha Final</th>
                                        </tr>
            ';
            while ($row = $this->extraer()) {

                $origen = ($row['gpo_id'] == 1 && $row['ur_id'] == 18 && $row['omo_id'] == 'D') ? 'DIRECCIÓN DE RECURSOS MATERIALES(NORMATIVA)' : (($row['gpo_id'] == 1 && $row['ur_id'] == 18 && $row['omo_id'] == 'C') ? 'DIRECCIÓN DE RECURSOS HUMANOS(NORMATIVA)' : $row['ur_nombre']);

                $fechaInicial = date_format(date_create($row['pum_fechaInicial'] ?? ''), 'd/m/Y H:i:s');
                $fechaFinal = date_format(date_create($row['pum_fechaFinal'] ?? ''), 'd/m/Y H:i:s');

                $modal .= '
                    <tr>
                        <td>' . $row['gpo_id'] . $row['ur_id'] . '-' . $origen . '</td>
                        <td>' . $row['mmd_nombre'] . '</td>
                        <td>' . (($row['mod_subsecretaria']) == 1 ? 'Egresos' : 'Planeación') . '</td>
                        <td>' . $row['tfm_nombre'] . '</td>
                        <td>' . $row['cff_nombre'] . '</td>
                        <td>' . $row['tmd_nombre'] . '</td>
                        <td>' . (($row['sts_id'] == 1) ? 'SI' : 'NO') . '</td>
                        <td>' . $fechaInicial . '</td>
                        <td>' . $fechaFinal . '</td>
                    </tr>
                ';
            }

            $modal .= '         </table>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>';

            $this->mixHtml($modal);

            $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            });
        ');
        }
    }

    //METODO QUE MUESTRA LAS OPCIONES A HABILITAR/DESHABILITAR
    private function ponOpciones()
    {

        $this->setContenedor('tipo');
        if (isset($this->prm->modalidad)) {
            switch ($this->prm->modalidad) {
                case 'I': //Internas
                    $tipoFolio = '
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="B" /> <b>Gastos de operación</b><br>
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="C" /> <b>Inversión pública</b><br>
                    ';
                    break;
                case 'E': //Externas
                    $tipoFolio = '
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="A" /> <b>Servicios personales</b><br>
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="B" /> <b>Gastos de operación</b><br>
                        <input type="checkbox" id="tfm_id" name="tfm_id[]" tfm_id="C" /> <b>Inversión pública</b><br>
                    ';
                    break;
            }
            $this->mixHtml($tipoFolio);
        }
    }

    //METODO QUE REGISTRA LOS PERMISOS
    private function abcPermisos()
    {
        $permisos = json_decode($this->prm->permisos);

        $periodo = '';
        $actualizados = ''; //guarda el numero de registros modificados
        $params = [];

        if (isset($this->prm->fecIni) && isset($this->prm->horaIni) && isset($this->prm->fecFin) && isset($this->prm->horaFin)) {

            switch ($this->ssc_id) {
                case 3: //Egresos
                    if ($this->prm->fecIni != 0 && $this->prm->horaIni != 0 && $this->prm->fecFin != 0 && $this->prm->horaFin != 0) {
                        $inicio = $this->date_to_mysqldate($this->prm->fecIni);
                        $fin = $this->date_to_mysqldate($this->prm->fecFin);

                        $periodo = ' ,pum_fechaInicial = :inicio ,pum_fechaFinal = :fin ';
                        $params['inicio'] = $inicio . ' ' . $this->prm->horaIni . ':00';
                        $params['fin'] = $fin . ' ' . $this->prm->horaFin . ':00';
                    } else {
                        $periodo = ' ,pum_fechaInicial=null,pum_fechaFinal = null ';
                    }

                    break;
                case 2: //planeación

                    if ($this->prm->fecIni != 0 && $this->prm->fecFin != 0) {
                        $inicio = $this->date_to_mysqldate($this->prm->fecIni);
                        $fin = $this->date_to_mysqldate($this->prm->fecFin);

                        $periodo = ' ,pum_fechaInicial = :inicio ,pum_fechaFinal = :fin ';
                        $params['inicio'] = $inicio . ' ' . $this->prm->horaIni . ':00';
                        $params['fin'] = $fin . ' ' . $this->prm->horaFin . ':00';
                    } else {
                        $periodo = ' ,pum_fechaInicial=null,pum_fechaFinal = null ';
                    }

                    break;
            }
        } else {
            $periodo = ' ,pum_fechaInicial=null,pum_fechaFinal = null ';
        }

        if (isset($this->prm->permisos) && count($permisos) > 0 && ($this->prm->todas != '' || $this->prm->normativa == 'C' || $this->prm->normativa == 'D')) {
            if ($this->prm->normativa == 'C' || $this->prm->normativa == 'D') {

                $ur = ' gpo_id=1 and ur_id=18 and ';
                $psql = '';

                foreach ($permisos as $valor) {
                    $psql .= ' mmd_id = :mmd and mod_Subsecretaria = 1 and tfm_id in (:tfm) ';
                    $params['mmd'] = $valor->mmd_id;
                    $params['tfm'] = $valor->tfm_id;
                }
                $params['sts'] = $this->prm->sts_id;
                $params['tfm'] = $valor->tfm_id;
                $params['omo'] = $this->prm->normativa;
                $sql = 'UPDATE cat_PermisosUrModificaciones set sts_id=:sts ' . $periodo . ' WHERE ' . $ur . ' ' . $psql . ' AND omo_id=:omo;';

                $this->conectar();
                $this->setSql($sql);
                $this->consultar($params);
                $this->desconectar();

                $this->mixScript('notify("Acción realizada satisfactoriamente");');
            } else {
                if ($permisos[0]->tmd_id == '') {
                    $this->mixScript('notify("Falta información por complementar, favor de revisar", "error"); ');
                    return false;
                }
                $gpo_id = ((isset($this->prm->todas) && $this->prm->todas == 1) ? '' : substr($this->prm->todas, 0, 1));
                $ur_id = ((isset($this->prm->todas) && $this->prm->todas == 1) ? '' : substr($this->prm->todas, 1, 2));
                $ur = ' ';
                if ($gpo_id != '' && $ur_id != '') {
                    $ur = ' gpo_id=:gpo and ur_id=:ur and ';
                    $params['gpo'] = $gpo_id;
                    $params['ur'] = $ur_id;
                }

                $psql = '';
                foreach ($permisos as $valor) {
                    $psql .= ' mmd_id=:mmd and mod_Subsecretaria = :modSub and tfm_id in (' . $valor->tfm_id . ') and cff_id in (' . $valor->cff_id . ') and tmd_id in (' . $valor->tmd_id . ')';
                    $params['mmd'] = $valor->mmd_id;
                    $params['modSub'] = $valor->mod_Subsecretaria;
                }
                $params['sts'] = $this->prm->sts_id;
                $sql = 'UPDATE cat_PermisosUrModificaciones SET sts_id=:sts ' . $periodo . ' WHERE ' . $ur . ' ' . $psql . ' AND omo_id="A" ;';
                $this->conectar();
                $this->setSql($sql);
                $this->consultar($params);
                $actualizados = $this->getNumeroModificados();
                $this->desconectar();
                if ($actualizados > 0) {
                    $this->mixScript('notify("Acción realizada satisfactoriamente");');
                } else {
                    $this->mixScript('notify("Acción no realizada");');
                }
            }

            /*                 * ***************** LOG DE LOS PERMISOS DE UNIDAD RESPONSABLE*********************************** */
            $rutalog = 'LOGS/PermisosModificaciones-' . $this->ssc_id . '.txt';
            $file = fopen($rutalog, 'a');
            $tempdata = '';
            $hoy = date('Y-m-d H-i-s');

            foreach ($this->prm as $field => $value) {
                if (is_string($value)) {
                    $tempdata .= '|' . $field . '=' . $value;
                }
            }

            $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
            $tempdata .= '|IPADDRESS=' . $ip;
            $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];
            $tempdata .= '|CONSULTA=' . $sql . '';

            fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
            fclose($file);

            $peso = filesize($rutalog);

            $peso = ($peso / 1024) / 1024;

            if ($peso > 8) {
                $rutamodificada = 'LOGS/PermisosModificaciones-' . $_SESSION[_S]['usr_id'] . $hoy . '.txt';
                rename($rutalog, $rutamodificada);
                $file = fopen($rutalog, 'a');
                fwrite($file, 'Creado:' . $hoy . PHP_EOL);
                fclose($file);
            }
        } else {
            $this->mixScript('notify("Falta información por complementar, favor de revisar", "error"); ');
        }
    }
}

//Verifica si el usuario tiene los permisos
if (isset($_SESSION[_S]['prm']['M']['7']['1']['1'])) {
    new ControladorPermisosModificaciones();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
