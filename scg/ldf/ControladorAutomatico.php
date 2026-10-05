<?php
//inicio de sesion y llamada a las librerias
//Hazael Hernandez Julio 2018
session_start();
require_once dirname(__FILE__).'/../../lib/WebResponse.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__).'/../../lib/scg/ldf/SQLreportesLDF.php';

class ControladorAutomatico extends WebResponse{
    public function __construct(){
        //mandar a llamar constructor base
        parent::__construct();
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, $_POST);
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, $_POST);
        //$this->ReporteContables = $this->tienePermisos(95, $_POST);
        //$this->ReportePresupuestal = $this->tienePermisos(96, $_POST);
        
        $this->gpo_id = $_SESSION[_S]['s24-gpo'];
        $this->ur_id = $_SESSION[_S]['s24-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->sis_id = $_POST['opcion']['sis_id'];
        $this->men_id = $_POST['opcion']['men_id'];
        $this->opc_id = $_POST['opcion']['opc_id'];
        
        $this->usr_ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : 'ERROR';
        
        $sql = "
            SELECT ent_id
            FROM ldf.vin_EntePublicoLDF
            WHERE ejr_id = 2022
                AND gpo_id = ".$this->gpo_id." AND ur_id = ".$this->ur_id."
        ";
        $consultaLDF = new AccesoDatos();
        $consultaLDF->conectar();
        $consultaLDF->setSql($sql);
        $consultaLDF->consultar();
        $consultaLDF->desconectar();
        
        if($consultaLDF->getNumeroRegistros() != 0){
            $row = $consultaLDF->extraer();
            $this->repent_id = $row['ent_id'];
        }else{
            $this->repent_id = 0;
        }
        
        switch($this->metodo){
            case 'mostrarOpcionesDescarga':
                $this->mostrarOpcionesDescarga();
                break;
            case 'emitirReporte':
                if($this->escritura){
                    $this->emitirReporte();
                };
                break;
            case 'muestraReporte':
                $this->muestraReporte();
                break;
            case 'actualizaEdoReporte':
                if($this->escritura){
                    $this->actualizaEdoReporte();
                };
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }
    
    private function main(){
        $this->mixScript(
            ( ( $_SESSION[_S]['nvj_id'] < 3 ) ? '$(".cabecera").hide(); $("#toggleCA").hide(); '
                    : '')
        );
        /* $comboMesDesde = new stdClass();
          $comboMesDesde->sql = "SELECT msc_trimestre, msc_nombre FROM cat_MesContable";
          $comboMesDesde->id = "comboMesDesde";
          $comboMesDesde->label = "Desde";
          $comboMesDesde->comoAtr = 'msc_nombre';
          $comboMesDesde->selecciona = 1;
          $comboMesDesde->width = '150px';

          $btnEXCEL = new stdClass();
          $btnEXCEL->id = "excel";
          $btnEXCEL->class = "btnImp";
          $btnEXCEL->color = "azul";
          $btnEXCEL->icon='btn glyphicon-download';
          $btnEXCEL->type = "submit"; */
        
        $tabla = new stdClass();
        $tabla->id = 'TabLFD';
        $tr = array();
        
        $th = new stdClass();
        $th->texto = 'N°';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Trimestre';
        $th->align = 'center';
        $th->colspan = '8';
        array_push($tr, $th);
        
        $tr2 = array();
        $th = new stdClass();
        $th->texto = '';
        array_push($tr2, $th);
        $th = new stdClass();
        $th->texto = '';
        array_push($tr2, $th);
        /* PRIMER TRIMESTRE */
        $th = new stdClass();
        $th->texto = 'Enero - Marzo';
        array_push($tr2, $th);
        $th = new stdClass();
        $th->texto = 'N/A';
        array_push($tr2, $th);
        /* SEGUNDO TRIMESTRE */
        $th = new stdClass();
        $th->texto = 'Enero - Junio';
        array_push($tr2, $th);
        $th = new stdClass();
        $th->texto = 'N/A';
        array_push($tr2, $th);
        /* TERCER TRIMESTRE */
        $th = new stdClass();
        $th->texto = 'Enero - Septiembre';
        array_push($tr2, $th);
        $th = new stdClass();
        $th->texto = 'N/A';
        array_push($tr2, $th);
        /* CUARTO TRIMESTRE */
        $th = new stdClass();
        $th->texto = 'Enero - Diciembre';
        array_push($tr2, $th);
        $th = new stdClass();
        $th->texto = 'N/A';
        array_push($tr2, $th);
        
        $tabla->encabezado = array($tr, $tr2);
        
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaLFD';
        $tabla->numFilas = 10;
        
        $tabla->template = '
            <tr class="odd">
                <td class="sorting_1">
                    <label style="text-align:left; width:100%;">${rep_id}</label>
                </td>
                <td class="sorting_1">
                    <label style="text-align:left; width:100%;">${rep_observacion}</label>
                </td>
                
                <td class="sorting_1">
                    {{if edo_trimestre1=="A" && edo_trimestreC1=="C"}}
                        {{if Reporte1==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "1" rep_id = "${rep_id}"  rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            '.(($this->escritura) ? 
                            '<button type="button" name="abrir" class="btn btn-danger NoGenerado" msc_trimestre = "1" rep_id = "${rep_id}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">NO GENERADO</button>':
                            '<label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>').'
                        {{/if}}
                    {{else}}
                        {{if Reporte1==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "1" rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            <label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>
                        {{/if}}
                    {{/if}}
                </td>
                <td class="sorting_1">
                    '.(($this->escritura) ? 
                    '{{if edo_trimestre1=="A" && Reporte1==1 && edo_trimestreC1=="C"}}
                        <input id ="T1R${rep_id}" type="checkbox" class="checkEmitido"  />
                    {{/if}}':
                    '').'
                </td>
                <td class="sorting_1">
                    {{if edo_trimestre2=="A" && edo_trimestreC2=="C"}}
                        {{if Reporte2==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "2" rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            '.(($this->escritura) ? 
                            '<button type="button" name="abrir" class="btn btn-danger NoGenerado" msc_trimestre = "2" rep_id = "${rep_id}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">NO GENERADO</button>':
                            '<label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>').'
                        {{/if}}
                    {{else}}
                        {{if Reporte2==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "2" rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            <label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>
                        {{/if}}
                    {{/if}}
                </td>
                <td class="sorting_1">
                    '.(($this->escritura) ? 
                    '{{if edo_trimestre2=="A" && Reporte2==1 && edo_trimestreC2=="C"}}
                        <input id ="T2R${rep_id}" type="checkbox" class="checkEmitido" />
                    {{/if}}' : '').'
                </td>
                <td class="sorting_1">
                    {{if edo_trimestre3=="A"  && edo_trimestreC3=="C"}}
                        {{if Reporte3==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "3" rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            '.(($this->escritura) ? 
                            '<button type="button" name="abrir" class="btn btn-danger NoGenerado" msc_trimestre = "3" rep_id = "${rep_id}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">NO GENERADO</button>':
                            '<label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>').'
                        {{/if}}
                    {{else}}
                        {{if Reporte3==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "3" rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            <label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>
                        {{/if}}
                    {{/if}}
                </td>
                <td class="sorting_1">
                    '.(($this->escritura) ? 
                    '{{if edo_trimestre3=="A" && Reporte3==1 && edo_trimestreC3=="C"}}
                        <input id ="T3R${rep_id}" type="checkbox" class="checkEmitido" />
                    {{/if}}':
                    '').'
                </td>
                <td class="sorting_1">
                    {{if edo_trimestre4=="A" && edo_trimestreC4=="C"}}
                        {{if Reporte4==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "4" rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            '.(($this->escritura) ? 
                            '<button type="button" name="abrir" class="btn btn-danger NoGenerado" msc_trimestre = "4" rep_id = "${rep_id}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">NO GENERADO</button>':
                            '<label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>').'
                        {{/if}}
                    {{else}}
                        {{if Reporte4==5}}
                            <button type="button" name="abrir" class="btn btn-success Emitido" msc_trimestre = "4" rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" gpo_id = "'.$this->gpo_id.'" ur_id = "'.$this->ur_id.'">EMITIDO</button>
                        {{else}}
                            <label class="label label-danger" style="text-align:left; width:100%;">SIN DATOS</label>
                        {{/if}}
                    {{/if}}
                </td>
                <td class="sorting_1">
                    '.(($this->escritura) ? 
                    '{{if edo_trimestre4=="A" && Reporte4==1 && edo_trimestreC4=="C"}}
                        <input id ="T4R${rep_id}" type="checkbox" class="checkEmitido"  />
                    {{/if}}':
                    '').'
                </td>
            </tr>
        ';
        
        $this->agregaTools("");
        $this->mixHtml($this->wrPonTabla($tabla));
        
        $this->llenaTablaLFD();
    }
    
    private function llenaTablaLFD(){
        $rep = "1";
        $rep .= ', 6';
        $rep .= ', 7';
        $rep .= ', 8';
        $rep .= ', 9';
        
        $sql = SQLReportesLDF($this->repent_id, $rep);
        //echo "<pre>";print_r($sql);echo "</pre>";
        //die();
        $this->setSql($sql);
        $nav = ( isset($this->prm->nav) ) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'TabLFD';
        //$nav->nfDefault = 300;
        $this->wrLlenaTabla($nav);
        
        $this->mixScript('
            $(".Emitido").click(function(){
                var msc_trimestre = $(this).attr("msc_trimestre");
                var rep_id = $(this).attr("rep_id");
                var rep_observacion = $(this).attr("rep_observacion");
                comunica("'.$this->pantalla.'",{
                    metodo: "mostrarOpcionesDescarga",
                    msc_trimestre: msc_trimestre,
                    rep_id: rep_id,
                    rep_observacion: rep_observacion
                });
                return false;
            });
            
            $(".NoGenerado").click(function(){
                var generar = confirm("¿Se generará el Formato LDF con datos automáticamente?");
                if(generar){
                    var msc_trimestre = $(this).attr("msc_trimestre");
                    var rep_id = $(this).attr("rep_id");
                    var no_aplica = $("#T"+msc_trimestre+"R"+rep_id).is(":checked");
                    console.log(no_aplica);
                    comunica("'.$this->pantalla.'",{
                        metodo: "emitirReporte",
                        msc_trimestre: msc_trimestre,
                        rep_id: rep_id,
                        no_aplica: no_aplica
                    });
                    return false;
                }
            });
        ');
    }
    
    public function mostrarOpcionesDescarga()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = '&nbsp;&nbsp;Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'btn btn-test';
        $btnregresarindex->icon = 'glyphicon glyphicon-hand-left';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("'.$this->pantalla.'",{
                metodo: "main",
            });
        ';
        $this->agregaTools($this->ponboton($btnregresarindex));

        $pdf = '<button  msc_trimestre="'.$this->prm->msc_trimestre.'" tipo="1" rep_id="'.$this->prm->rep_id.'" formato="PDF" class="btn btn-xs imprimir abrirarchivo" title="Imprimir PDF"  target="_blank" style="font-size:14pt;"><i class="glyphicon glyphicon-print"></i> PDF</button>';
        $xls = '<button  msc_trimestre="'.$this->prm->msc_trimestre.'" tipo="2" rep_id="'.$this->prm->rep_id.'" formato="EXCEL" class="btn  btn-xs imprimir abrirarchivo" title="Imprimir Excel" target="_blank" style="font-size:14pt;"><i class="glyphicon glyphicon-list"></i> EXCEL</button>';

        //$this->mixHtml($pdf.' '.$xls);
        $periodoNombre = "";
        switch ($this->prm->msc_trimestre) {
            case 1: $periodoNombre = "ENERO - MARZO";
                break;
            case 2: $periodoNombre = "ENERO - JUNIO";
                break;
            case 3: $periodoNombre = "ENERO - SEPTIEMBRE";
                break;
            case 4: $periodoNombre = "ENERO - DICIEMBRE";
                break;
        }
        $html = '<hr><legend>  PERIODO '.$periodoNombre.'</legend></hr>'.
            '<hr><legend>'.$this->prm->rep_observacion.'</legend>
                <fieldset class="col-md-6 col-xs-6">
                    '.$pdf.'
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    '.$xls.'
                </fieldset>
            </hr>
            ';
        $this->mixHtml($html);
        /* $this->mixHtml('<html>
          </html>'); */
    
        $this->mixScript('
            $(".abrirarchivo").click(function(){
                var rep_id = $(this).attr("rep_id");
                var msc_trimestre = $(this).attr("msc_trimestre");
                var tipo = $(this).attr("tipo");
                comunica("'.$this->pantalla.'",{
                    metodo: "muestraReporte",
                    msc_trimestre: msc_trimestre,
                    rep_id: rep_id,
                    tipo: tipo
                });
                return false;
            });
        ');
    }
    
    public function actualizaEdoReporte(){
        /*$consultaEmitir = 'UPDATE ldf.ldf_EstadoReporte SET ere_id = 5 WHERE ent_id = '.$this->repent_id.' AND msc_trimestre = '.$this->prm->msc_trimestre.' AND rep_id = '.$this->prm->rep_id;
        $ip = $this->usr_ip;
        $emitirReporte = new AccesoDatos();
        $emitirReporte->conectar();
        $emitirReporte->setSql("SET @usr_id = ".$_SESSION[_S]['s19-id'].", @usr_ip = INET_ATON('$ip'), @bit_observacion = 'Actualizacion de reporte emitido' ");
        $emitirReporte->consultar();
        $emitirReporte->setSql($consultaEmitir);
        $emitirReporte->consultar();
        $emitirReporte->desconectar();*/
        $this->llenaTablaLFD();
    }
    
    public function emitirReporte(){
        $rep_id = $this->prm->rep_id;
        $msc_trimestre = $this->prm->msc_trimestre;
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        
        $ent_id = $this->repent_id;
        $no_aplica = (isset($this->prm->no_aplica) && $this->prm->no_aplica != "" ? $this->prm->no_aplica : false);
        
        switch($rep_id){
            case 1:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_SituacionFinanciera.php";
                break;
            case 6:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_AnaliticoPresupuesto6A.php";
                break;
            case 7:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_AnaliticoPresupuesto6B.php";
                break;
            case 8:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_AnaliticoPresupuesto6C.php";
                break;
            case 9:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_AnaliticoPresupuesto6D.php";
                break;
        }
        $reportePDF = "rep/doc/scg/ldf/rep_Automatico.php";
        
        $this->mixScript('
            function DetectaBloqueoPops(){ 
                if(!$.browser.mozilla){
                    msj = "Favor de Utilizar el Navegador Mozilla Firefox";
                    alert(msj);
                    return false;
                }
                
                var popup;
                popup = window.open("about:blank","_blank","width=1,height=1");
              
                if(popup == null || typeof(popup)=="undefined"){
                    msj = "Favor de verificar que el explorador permita Ventanas Emergentes";
                    alert(msj);
                    return false;
                }else{
                    popup.close();
                    var form = $("<form >")
                        .attr("method", "post")
                        .attr("action","'.$reporteEXCEL.'")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "14" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "msc_trimestre").attr("value", "'.$msc_trimestre.'" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "gpo_id" ).attr("value", "'.$gpo_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_id" ).attr("value", "'.$ur_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ent_id" ).attr("value", "'.$ent_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "noAplica" ).attr("value", '.$no_aplica.'));
                    $("body").append(form);
                    form.submit();
                    var form = $("<form >")
                        .attr("method", "post")
                        .attr("action","'.$reportePDF.'")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "14" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "msc_trimestre").attr("value", "'.$msc_trimestre.'" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "gpo_id" ).attr("value", "'.$gpo_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_id" ).attr("value", "'.$ur_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ent_id" ).attr("value", "'.$ent_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "noAplica" ).attr("value", '.$no_aplica.'))
                        .append($("<input>").attr("type", "hidden").attr("name", "rep_id" ).attr("value", '.$rep_id.'));
                    $("body").append(form);
                    form.submit();
                    
                    comunica("'.$this->pantalla.'",{
                        metodo: "actualizaEdoReporte",
                        msc_trimestre: "'.$msc_trimestre.'",
                        rep_id: "'.$rep_id.'"
                    });
                }
            }
            
            $(function(){
                DetectaBloqueoPops();
            });
            '
        );
    }
    
    public function muestraReporte(){
        $rep_id = $this->prm->rep_id;
        $msc_trimestre = $this->prm->msc_trimestre;
        $tipo = $this->prm->tipo;
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $ent_id = $this->repent_id;
        
        $this->mixScript('
            $(function () {
                var form = $("<form >")
                    .attr("method", "post")
                    .attr("action","lib/scg/ldf/abrir_archivo.php")
                    .attr("target", "_blank")
                    /*
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "14" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))
                    */
                    .append($("<input>").attr("type", "hidden").attr("name", "msc_trimestre").attr("value", "'.$msc_trimestre.'" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "gpo_id" ).attr("value", "'.$gpo_id.'" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id" ).attr("value", "'.$ur_id.'"))
                    .append($("<input>").attr("type", "hidden").attr("name", "tipo" ).attr("value", "'.$tipo.'"))
                    .append($("<input>").attr("type", "hidden").attr("name", "rep_id" ).attr("value", "'.$rep_id.'"))
                    .append($("<input>").attr("type", "hidden").attr("name", "ent_id" ).attr("value", "'.$ent_id.'"))
                $("body").append(form);
                form.submit();
            });
            '
        );
    }
}
//aqui	
if(isset($_SESSION[_S]['s24-prm']['C']['14']['1']['1'])){
    new ControladorAutomatico();
}else{
    $error = new WebResponse();
    $error->sinPermisos();
}
?>