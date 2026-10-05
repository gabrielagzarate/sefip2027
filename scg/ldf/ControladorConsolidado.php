<?php
//inicio de sesion y llamada a las librerias
//Hazael Hernandez Julio 2019
session_start();
require_once dirname(__FILE__).'/../../lib/WebResponse.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';

class ControladorConsolidado extends WebResponse{
    public function __construct(){
        //mandar a llamar constructor base
        parent::__construct();
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, $_POST);
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, $_POST);
        $this->central = $this->tienePermisos(93, $_POST);
        $this->paraestatal = $this->tienePermisos(94, $_POST);

        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->niv_j = $_SESSION[_S]['nvj_id'];
        $this->sis_id = $_POST['opcion']['sis_id'];
        $this->men_id = $_POST['opcion']['men_id'];
        $this->opc_id = $_POST['opcion']['opc_id'];
        //$this->niv_j;
        $this->sector = "SN";
        $this->usr_ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : 'ERROR';

        if($this->paraestatal){
            $this->sector = "P";
        }
        if($this->central){
            $this->sector = "C";
        }
        if($this->central && $this->paraestatal){
            $this->sector = "G";
        }
        if($this->niv_j >= 6){
            $this->sector = "G";
        }
        switch($this->metodo){
            case 'mostrarPantallaFiltros': $this->mostrarPantallaFiltros();
                break;
            case 'emitirReporte':
                if($this->escritura){
                    $this->emitirReporte();
                };
                break;
            case 'cargaTrimestre': $this->cargaTrimestre();
                break;
            case 'muestraReporte': $this->muestraReporte();
                break;
            case 'actualizaEdoReporte':
                if($this->escritura){
                    $this->actualizaEdoReporte();
                };
                break;
            default: $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main(){
        //$unidadResponsable = $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'];
        //$this->nivel = $_SESSION[_S]['s19-nvj'];
        $this->mixScript(
            ( ( $_SESSION[_S]['nvj_id'] < 3 ) ? '$(".cabecera").hide(); $("#toggleCA").hide(); '
                    : '')
        );
        /* $comboMesDesde = new stdClass();
          $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable";
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
        $th->texto = 'Generar';
        array_push($tr, $th);
        /* $th = new stdClass();
          $th->texto = 'Excel';
          array_push($tr, $th); */
        $th = new stdClass();
        $th->texto = 'Periodo de Emisión';
        array_push($tr, $th);

        $tabla->encabezado = array($tr);

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConsolidado';
        $tabla->numFilas = 20;

        $tabla->template = '
			<tr class="odd">
				<td class="sorting_1">
					<label style="text-align:left; width:100%;">${rep_id}</label>
				</td>
				<td class="sorting_1">
					<label style="text-align:left; width:100%;">${rep_observacion}</label>
				</td>
				<td class="sorting_1 ">
					<button type="button" class="generaRep"  rep_id = "${rep_id}" rep_observacion = "${rep_observacion}" ><i class="glyphicon glyphicon-print"></i></button>
				</td>
				<td class="sorting_1">
					Trimestre
				</td>
			</tr>
		';

        $this->agregaTools("");
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaConsolidado();
        
    }

    private function llenaTablaConsolidado(){
        $rep = "1";
        $rep .= ', 6';
        $rep .= ', 7';
        $rep .= ', 8';
        $rep .= ', 9';
        $rep .= ', 2,3,4,5,10,11,12,13,14,15 ';
        $sql = '
            SELECT tr.rep_id, tr.rep_nombre, tr.rep_observacion
            FROM ldf.cat_TipoReporte AS tr
            WHERE tr.rep_id IN ('.$rep.')
        ';
            //WHERE tr.rep_id in (1,6,7,8)
        //echo $sql; die();
        $this->setSql($sql);
        $nav = ( isset($this->prm->nav) ) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'TabLFD';
        //$nav->nfDefault = 300;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".generaRep").click(function(){
                var rep_id = $(this).attr("rep_id");
                var rep_observacion = $(this).attr("rep_observacion");
                comunica("'.$this->pantalla.'",{
                    metodo: "mostrarPantallaFiltros",
                    rep_id: rep_id,
                    rep_observacion: rep_observacion
                });
            });

             $(".tabla-footer").hide();
             $(".tabla-contenedor").removeAttr("style")');
    }

    public function mostrarPantallaFiltros()
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

        $btnImprimir = new stdClass();
        $btnImprimir->id = 'btnImprimir';
        $btnImprimir->name = 'btnImprimir';
        $btnImprimir->text = 'Generar';
        $btnImprimir->value = '&nbsp;&nbsp;Generar';
        $btnImprimir->type = 'button';
        $btnImprimir->disabled = true;
        $btnImprimir->class = 'btn btn-test';

        $btnPDF = new stdClass();
        $btnPDF->id = "pdf";
        $btnPDF->class = "btnImp";
        $btnPDF->icon = 'btn glyphicon-print';
        $btnPDF->color = "azul";
        $btnPDF->disabled = true;
        $btnPDF->type = "button";

        $btnEXCEL = new stdClass();
        $btnEXCEL->id = "excel";
        $btnEXCEL->class = "btnImp";
        $btnEXCEL->color = "verde";
        $btnEXCEL->icon = 'btn glyphicon-download';
        $btnEXCEL->type = "button";
        $btnEXCEL->disabled = true;

        $this->agregaTools($this->ponboton($btnregresarindex)."&nbsp;&nbsp;".(($this->escritura) ? $this->ponboton($btnImprimir) : "")."&nbsp;&nbsp;".$this->ponboton($btnPDF)."&nbsp;&nbsp;".$this->ponboton($btnEXCEL));

        $entePublico = new stdClass();
        $entePublico->sql = '
            SELECT ent_id,ent_nombre
            FROM ldf.vin_EntePublicoLDF
                INNER JOIN ldf.cat_EntePublicoLDF USING(ent_id)
            '.($this->sector == "G" ? 'WHERE ejr_id = 2022
                AND ent_id != 0':'
                INNER JOIN ldf.cat_SectorLDF USING(sec_id)
            WHERE ejr_id = 2022
                AND tsc_id = "'.$this->sector.'" AND ent_id != 0 ').'
            GROUP BY ent_id
        ';
        $entePublico->id = 'ent_id';
        $entePublico->name = 'ent_id';
        $entePublico->required = true;
        $entePublico->mensaje = 'Selecciona Ente Publico';
        $entePublico->label = 'Ente Publico';
        //$entePublico->selecciona = $sp->getEpz_id();

        $grupo = new stdClass();
        $grupo->sql = '
            SELECT gpo_id, gpo_nombre
            FROM ldf.cat_SectorLDF
                INNER JOIN ctrl2023.cat_Grupo USING(gpo_id)
            '.($this->sector == "G" ? 'WHERE sec_id != 0':'
            WHERE tsc_id = "'.$this->sector.'" AND sec_id != 0').'
            GROUP BY gpo_id
        ';
        $grupo->id = 'gpo_id';
        $grupo->name = 'gpo_id';
        $grupo->required = true;
        $grupo->mensaje = 'Selecciona Grupo';
        $grupo->label = 'Grupo';

        $sector = new stdClass();
        $sector->id = 'sec_id';
        $sector->name = 'sec_id';
        $sector->required = true;
        $sector->mensaje = 'Selecciona Sector';
        $sector->label = 'Sector';
        if($this->sector == 'C'){
            $sector->opciones = array("" => "Selecciona Sector", "C" => "Consolidado Sector Central");
        }else if($this->sector == 'P'){
            $sector->opciones = array("" => "Selecciona Sector", "P" => "Consolidado Sector Paraestatal");
        }else if($this->sector == 'G'){
            $sector->opciones = array("" => "Selecciona Sector", "C" => "Consolidado Sector Central", "P" => "Consolidado Sector Paraestatal");
        }

        $periodo = new stdClass();
        $periodo->id = 'msc_trimestre';
        // $periodo->name = 'msc_trimestre';
        $periodo->required = true;
        $periodo->comoAtr = 'msc_trimestre, ere_id';
        $periodo->mensaje = 'Selecciona Periodo';
        $periodo->label = 'Periodo';
        $periodo->opciones = array("" => "Selecciona Periodo");

        $consolidado = new stdClass();
        $consolidado->id = 'consolidado';
        $consolidado->name = 'consolidado';
        $consolidado->required = true;
        $consolidado->mensaje = 'Selecciona Consolidado';
        $consolidado->label = 'Consolidado';
        $consolidado->opciones = array("" => "Selecciona Consolidado", "G" => "CONSOLIDADO DEL GOBIERNO DEL ESTADO DE OAXACA");
        //echo "<pre>";print_r($periodo);echo "</pre>";
        $html = '
            <hr><legend>'.$this->prm->rep_observacion.'</legend>
                <row class="col-md-12 col-xs-12">
                    <fieldset class="col-md-1 col-xs-1"></fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        '.$this->ponCombo($entePublico).'
                    </fieldset>
                    <fieldset class="col-md-1 col-xs-1"></fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        '.$this->ponCombo($grupo).'
                    </fieldset>
                </row>
                <row class="col-md-12 col-xs-12">
                    <fieldset class="col-md-1 col-xs-1"></fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        '.$this->ponCombo($sector).'
                    </fieldset>
                    <fieldset class="col-md-1 col-xs-1"></fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        '.$this->templateCombo($periodo).'
                    </fieldset>
                </row>
                '.( $this->sector == 'G' ?
                '<row  class="col-md-12 col-xs-12">
                    <fieldset class="col-md-1 col-xs-1"></fieldset>
                    <fieldset class="col-md-4 col-xs-4">'.$this->ponCombo($consolidado).'</fieldset> </row>'
                : '').'
            </hr>
        ';

        $this->mixHtml($html);

        $this->mixScript('
            $("#gpo_id").change(function(){
                if($("#gpo_id").val() == ""){
                    $("#ent_id").attr("disabled", false);
                    $("#sec_id").attr("disabled", false);
                    $("#consolidado").attr("disabled", false);
                    $("#btnImprimir").attr("disabled", true);
                    $("#pdf").attr("disabled", true);
                    $("#excel").attr("disabled", true);
                    comunica("'.$this->pantalla.'",{
                        metodo: "cargaTrimestre",
                        vacio: true
                    });
                }else{
                    $("#ent_id").attr("disabled", true);
                    $("#sec_id").attr("disabled", true);
                    $("#consolidado").attr("disabled", true);
                    if($(this).val() != ""){
                        var rep_id = "'.$this->prm->rep_id.'";
                        comunica("'.$this->pantalla.'",{
                            metodo: "cargaTrimestre",
                            gpo_id: $(this).val(),
                            rep_id: rep_id
                        });
                    }
                    if($("#msc_trimestre").val()!=""){
                        var ere_id = $("option:selected", "#msc_trimestre").attr("ere_id");
                        if(ere_id == 5){
                            $("#pdf").attr("disabled", false);
                            $("#excel").attr("disabled", false);
                        }else{
                            $("#btnImprimir").attr("disabled", false);
                        }
                    }
                }
            });
            $("#ent_id").change(function(){
                if($("#ent_id").val() == ""){
                    $("#gpo_id").attr("disabled", false);
                    $("#sec_id").attr("disabled", false);
                    $("#consolidado").attr("disabled", false);
                    $("#btnImprimir").attr("disabled", true);
                    $("#pdf").attr("disabled", true);
                    $("#excel").attr("disabled", true);
                     comunica("'.$this->pantalla.'",{
                        metodo: "cargaTrimestre",
                        vacio: true
                    });
                } else{
                    $("#gpo_id").attr("disabled", true);
                    $("#sec_id").attr("disabled", true);
                    $("#consolidado").attr("disabled", true);
                    if($(this).val() != ""){
                        var rep_id = "'.$this->prm->rep_id.'";
                        comunica("'.$this->pantalla.'",{
                            metodo:"cargaTrimestre",
                            ent_id:$(this).val(),
                            rep_id:rep_id
                        });
                    }
                    if($("#msc_trimestre").val()!=""){
                        var ere_id = $("option:selected", "#msc_trimestre").attr("ere_id");
                        if(ere_id ==5){
                            $("#pdf").attr("disabled", false);
                            $("#excel").attr("disabled", false);
                        }else{
                            $("#btnImprimir").attr("disabled", false);
                        }
                    }
                }
            });
            $("#sec_id").change(function(){
                if($("#sec_id").val()==""){
                    $("#gpo_id").attr("disabled", false);
                    $("#ent_id").attr("disabled", false);
                    $("#consolidado").attr("disabled", false);
                    $("#btnImprimir").attr("disabled", true);
                        $("#pdf").attr("disabled", true);
                        $("#excel").attr("disabled", true);
                        comunica("'.$this->pantalla.'",{
                            metodo:"cargaTrimestre",
                            vacio:true
                        });
                }else{
                    $("#gpo_id").attr("disabled", true);
                    $("#ent_id").attr("disabled", true);
                    $("#consolidado").attr("disabled", true);
                    var rep_id = "'.$this->prm->rep_id.'";
                    if($(this).val()!=""){
                        comunica("'.$this->pantalla.'",{
                            metodo:"cargaTrimestre",
                            sec_id:$(this).val(),
                            rep_id:rep_id
                        });
                    }
                    if($("#msc_trimestre").val()!=""){
                       var ere_id = $("option:selected", "#msc_trimestre").attr("ere_id");
                        if(ere_id ==5){
                            $("#pdf").attr("disabled", false);
                            $("#excel").attr("disabled", false);
                        }else{
                            $("#btnImprimir").attr("disabled", false);
                        }
                    }
                }
            });
            $("#consolidado").change(function(){
                if($("#consolidado").val()==""){
                    $("#gpo_id").attr("disabled", false);
                    $("#ent_id").attr("disabled", false);
                    $("#sec_id").attr("disabled", false);
                    $("#btnImprimir").attr("disabled", true);
                        $("#pdf").attr("disabled", true);
                        $("#excel").attr("disabled", true);
                        comunica("'.$this->pantalla.'",{
                            metodo:"cargaTrimestre",
                            vacio:true
                        });
                }else{
                    $("#gpo_id").attr("disabled", true);
                    $("#ent_id").attr("disabled", true);
                    $("#sec_id").attr("disabled", true);
                    var rep_id = "'.$this->prm->rep_id.'";
                    if($(this).val()!=""){
                        comunica("'.$this->pantalla.'",{
                            metodo:"cargaTrimestre",
                            con_id:$(this).val(),
                            rep_id:rep_id
                        });
                    }
                    if($("#msc_trimestre").val()!=""){
                        var ere_id = $("option:selected", "#msc_trimestre").attr("ere_id");
                         if(ere_id ==5){
                             $("#pdf").attr("disabled", false);
                             $("#excel").attr("disabled", false);
                         }else{
                             $("#btnImprimir").attr("disabled", false);
                         }
                    }
                }
            });
            $("#msc_trimestre").change(function(){
                if($("#msc_trimestre").val()==""){
                    $("#btnImprimir").attr("disabled", true);
                    $("#pdf").attr("disabled", true);
                    $("#excel").attr("disabled", true);
                }else if( $("#gpo_id").val()!="" || $("#ent_id").val()!="" || $("#sec_id").val()!="" || $("#consolidado").val()!="" ){
                    var ere_id = $("option:selected", "#msc_trimestre").attr("ere_id");
                    if(ere_id ==5){
                        $("#btnImprimir").attr("disabled", true);
                        $("#pdf").attr("disabled", false);
                        $("#excel").attr("disabled", false);
                        $( "#btnImprimir").unbind( "click" );
                    }else{
                            $("#pdf").attr("disabled", true);
                            $("#excel").attr("disabled", true);
                            $( "#btnImprimir").unbind( "click" );
                            $("#btnImprimir").click(function(){
                            var ent_id = $("#ent_id").val();
                            var gpo_id = $("#gpo_id").val();
                            var sec_id = $("#sec_id").val();
                            var msc_trimestre = $("#msc_trimestre").val();
                            '.($this->sector == "G" ? 'var con_id = $("#consolidado").val();':'').'
                            var rep_id = "'.$this->prm->rep_id.'";
                            comunica("'.$this->pantalla.'",{
                                metodo: "emitirReporte",
                                msc_trimestre:msc_trimestre,
                                ent_id:ent_id,
                                gpo_id:gpo_id,
                                sec_id:sec_id,
                                rep_id:rep_id
                                '.($this->sector == "G" ? ',con_id:con_id' : '').'
                            });
                        });
                        $("#btnImprimir").attr("disabled", false);
                    }
                }
            });
            $("#pdf").click(function(){
                var ent_id = $("#ent_id").val();
                var gpo_id = $("#gpo_id").val();
                var sec_id = $("#sec_id").val();
                var msc_trimestre = $("#msc_trimestre").val();
                var rep_id = "'.$this->prm->rep_id.'";
                '.($this->sector == "G" ? 'var con_id = $("#consolidado").val();': '').'
                comunica("'.$this->pantalla.'",{
                    metodo: "muestraReporte",
                    msc_trimestre:msc_trimestre,
                    ent_id:ent_id,
                    gpo_id:gpo_id,
                    sec_id:sec_id,
                    rep_id:rep_id,
                    tipo:1
                    '.($this->sector == "G" ? ',con_id:con_id' : '').'
                });
            });
            $("#excel").click(function(){
                var ent_id = $("#ent_id").val();
                var gpo_id = $("#gpo_id").val();
                var sec_id = $("#sec_id").val();
                var msc_trimestre = $("#msc_trimestre").val();
                var rep_id = "'.$this->prm->rep_id.'";
                '.($this->sector == "G" ? 'var con_id = $("#consolidado").val();': '').'
                comunica("'.$this->pantalla.'",{
                    metodo: "muestraReporte",
                    msc_trimestre:msc_trimestre,
                    ent_id:ent_id,
                    gpo_id:gpo_id,
                    sec_id:sec_id,
                    rep_id:rep_id,
                    tipo:2
                    '.($this->sector == "G" ? ',con_id:con_id' : '').'
                });
            });
        ');
    }

    private function cargaTrimestre(){
        $ent_id = (isset($this->prm->ent_id) ? $this->prm->ent_id : '');
        $sec_id = (isset($this->prm->sec_id) ? $this->prm->sec_id : '');
        $gpo_id = (isset($this->prm->gpo_id) ? $this->prm->gpo_id : '');
        $con_id = (isset($this->prm->con_id) ? $this->prm->con_id : '');
        $vacio = (isset($this->prm->vacio) ? $this->prm->vacio : false);
        $rep_id = (isset($this->prm->rep_id) ? $this->prm->rep_id : 0);
        $obt = new stdClass();
        $obt->id = 'msc_trimestre';
        if($sec_id != "" && $this->sector != 'G'){
            $sec_id = $this->sector;
        }

        if($ent_id != ""){
            $where = 'WHERE nir_id = 2 AND nii_id = "'.$ent_id.'" AND rep_id = "'.$rep_id.'" AND ejr_id = 2022';
        }
        if($sec_id != ""){
            $where = 'WHERE nir_id = 3 AND nii_id = "'.$sec_id.'" AND rep_id = "'.$rep_id.'" AND ejr_id = 2022';
        }
        if($gpo_id != ""){
            $where = 'WHERE nir_id = 1 AND nii_id = "'.$gpo_id.'" AND rep_id = "'.$rep_id.'" AND ejr_id = 2022';
        }
        if($con_id != ""){
            $where = 'WHERE nir_id = 4 AND nii_id = "'.$con_id.'" AND rep_id = "'.$rep_id.'" AND ejr_id = 2022';
        }
        if($vacio){
            $obt->sql = '
                SELECT "" AS msc_trimestre,
                    "Selecciona Perido"  AS trimestre, "" AS ere_id
                    ;';
            $obt->comoAtr = 'msc_trimestre,ere_id';
        } else {
            $obt->sql = '
                SELECT msc_trimestre,
                    CASE msc_trimestre
                        WHEN 1 THEN
                            "ENERO-MARZO"
                        WHEN 2 THEN
                            "ENERO-JUNIO"
                        WHEN 3 THEN
                            "ENERO-SEPTIEMBRE"
                        WHEN 4 THEN
                            "ENERO-DICIEMBRE"
                    END AS trimestre, ere_id
                FROM ldf.ldf_EstadoReporteConsolidado
                '.$where;
            $obt->comoAtr = 'msc_trimestre,ere_id';
            /* if ($msc_trimestre != '') {
              $obt->selecciona = $msc_trimestre;
              } */
        }

        $this->recargaCombo($obt);
        // $this->cargaObj();
    }

    public function emitirReporte(){
        //print_r($this->prm);
        // die();
        $rep_id = (isset($this->prm->rep_id) ? $this->prm->rep_id : "");
        $msc_trimestre = (isset($this->prm->msc_trimestre) ? $this->prm->msc_trimestre : "");
        $ent_id = (isset($this->prm->ent_id) ? $this->prm->ent_id : "");
        $sec_id = (isset($this->prm->sec_id) ? $this->prm->sec_id : "");
        $gpo_id = (isset($this->prm->gpo_id) ? $this->prm->gpo_id : "");
        $con_id = (isset($this->prm->con_id) ? $this->prm->con_id : "");
        $ere_id = (isset($this->prm->ere_id) ? $this->prm->ere_id : 1);

        switch($rep_id){
            case 1:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_SituacionFinanciera.php";
                break;
            case 2:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_DeudaPublica.php";
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
                        .append($("<input>").attr("type", "hidden").attr("name", "sec_id" ).attr("value", "'.$sec_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ent_id" ).attr("value", "'.$ent_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "con_id" ).attr("value", "'.$con_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "consolidado" ).attr("value", true));
                    $("body").append(form);
                    console.log(form.submit());
                    var form = $("<form >")
                        .attr("method", "post")
                        .attr("action","'.$reportePDF.'")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "14" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "msc_trimestre").attr("value", "'.$msc_trimestre.'" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "gpo_id" ).attr("value", "'.$gpo_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "sec_id" ).attr("value", "'.$sec_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "ent_id" ).attr("value", "'.$ent_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "con_id" ).attr("value", "'.$con_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "rep_id" ).attr("value", "'.$rep_id.'"))
                        .append($("<input>").attr("type", "hidden").attr("name", "consolidado" ).attr("value",true));
                    $("body").append(form);
                    form.submit();

                    comunica("'.$this->pantalla.'",{
                        metodo: "actualizaEdoReporte",
                        msc_trimestre: "'.$msc_trimestre.'",
                        rep_id: "'.$rep_id.'",
                        ent_id: "'.$ent_id.'",
                        gpo_id: "'.$gpo_id.'",
                        sec_id: "'.$sec_id.'",
                        con_id: "'.$con_id.'"
                    });
                         setTimeout( function() { $("#btnImprimir").attr("disabled", true);
                        $("#pdf").attr("disabled", true);
                        $("#excel").attr("disabled", true); }, 100 );
                  }
            }

            $(function () {
                DetectaBloqueoPops();
                /*comunica("'.$this->pantalla.'",{
                    metodo: "cargaTrimestre",
                    rep_id: "'.$rep_id.'",
                    ent_id: "'.$ent_id.'",
                    gpo_id: "'.$gpo_id.'",
                    sec_id: "'.$sec_id.'",
                    con_id: "'.$con_id.'"
                });
                $("#msc_trimestre").val("'.$msc_trimestre.'");*/
            });
            '
        );
    }

    public function muestraReporte(){
        $rep_id = (isset($this->prm->rep_id) ? $this->prm->rep_id : "");
        $msc_trimestre = (isset($this->prm->msc_trimestre) ? $this->prm->msc_trimestre : "");
        $ent_id = (isset($this->prm->ent_id) ? $this->prm->ent_id : "");
        $gpo_id = (isset($this->prm->gpo_id) ? $this->prm->gpo_id : "");
        $sec_id = (isset($this->prm->sec_id) ? $this->prm->sec_id : "");
        $con_id = (isset($this->prm->con_id) ? $this->prm->con_id : "");
        $rep_id = (isset($this->prm->rep_id) ? $this->prm->rep_id : "");
        $tipo = (isset($this->prm->tipo) ? $this->prm->tipo : 0);

        switch($rep_id){
            case 1:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_SituacionFinanciera.php";
                break;
            case 2:
                $reporteEXCEL = "rep/excel/scg/ldf/exp_DeudaPublica.php";
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
        $reportePDF = "rep/doc/scg/ldf/rep_ldfAutomatico.php";

        $this->mixScript('
            $(function (){
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
            });
            '
        );
    }

    public function actualizaEdoReporte(){
        $rep_id = (isset($this->prm->rep_id) ? $this->prm->rep_id : "");
        $msc_trimestre = (isset($this->prm->msc_trimestre) ? $this->prm->msc_trimestre : "");
        $ent_id = (isset($this->prm->ent_id) ? $this->prm->ent_id : "");
        $gpo_id = (isset($this->prm->gpo_id) ? $this->prm->gpo_id : "");
        $sec_id = (isset($this->prm->sec_id) ? $this->prm->sec_id : "");
        $con_id = (isset($this->prm->con_id) ? $this->prm->con_id : "");

        if ($ent_id != "") {
            $nivelrep = 2;
            $ente = $ent_id;
        }
        if ($gpo_id != "") {

            $nivelrep = 1;
            $ente = $gpo_id;
        }
        if ($sec_id != "") {
            $nivelrep = 3;
            $ente = $sec_id;
        }
        if ($con_id != "") {
            $nivelrep = 4;
            $ente = $con_id;
        }
        /*$consultaEmitir = '
            UPDATE ldf.ldf_EstadoReporteConsolidado
            SET ere_id = "5"
            WHERE ejr_id = "2019"
                AND nir_id = "'.$nivelrep.'"
                AND nii_id = "'.$ente.'"
                AND msc_trimestre = "'.$msc_trimestre.'"
                AND rep_id = '.$rep_id.'
        ';
        $ip = $this->usr_ip;
        $emitirReporte = new AccesoDatos();
        $emitirReporte->conectar();
        $emitirReporte->setSql("SET @usr_id = ".$_SESSION[_S]['s19-id'].", @usr_ip = INET_ATON('$ip'), @bit_observacion = 'Actualizacion de reporte emitido' ");
        $emitirReporte->consultar();
        $emitirReporte->setSql($consultaEmitir);
        $emitirReporte->consultar();
        $emitirReporte->desconectar();
        */
        $this->mixScript('
            comunica("'.$this->pantalla.'",{
                metodo: "cargaTrimestre",
                rep_id: "'.$rep_id.'",
                ent_id: "'.$ent_id.'",
                gpo_id: "'.$gpo_id.'",
                sec_id: "'.$sec_id.'",
                con_id: "'.$con_id.'"
            });
        ');
    }
}

//aqui
if(isset($_SESSION[_S]['s24-prm']['C']['14']['2']['1'])){
    new ControladorConsolidado();
}else{
    $error = new WebResponse();
    $error->sinPermisos();
}
?>
