<?php
session_start();
error_reporting(E_ALL);
error_reporting(E_ERROR);
require_once dirname(__FILE__).'/../../lib/WebResponse.php';

	class ControladorMonitoreoControl extends WebResponse{


		public function __construct( ){
			parent::__construct();

            $this->nvj = $_SESSION[_S]['nvj_id'];

           switch ($this->nvj){
				case 1:case 2:
                    WebResponse::sinPermisos();
                break;
                default:
                    switch ($this->metodo) {
                        case 'capturarGpo':
                            $this->capturarGpo();
                            break;
                        case 'cambiaUR':
                            $this->cambiaUR();
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
        $html = '
        <form id="rep"></form>
           <table class="table table-bordered">
                <tr class="encabezadotabla">
                    <th colspan="3" style="text-align:center;">
                        Reportes de Monitoreo y Mejora
                    </th>
                </tr>
                <tr>
                    <td class="success"></td>
                    <td class="success">NOMBRE</td>
                    <td class="success">DESCRIPCI&Oacute;N</td>
                </tr>
                <tr>
                    <td>
                        <button id="btn1" class = "btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button></td>
                    <td>
                        1-REPORTE DE INVERSI&Oacute;N P&Uacute;BLICA META SIGNIFICATIVA</td>
                    <td>
                        INCLUYE TODA LA INVERSI&Oacute;N P&Uacute;BLICA A NIVEL OBRA. &Uacute;NICAMENTE SE REFLEJA UNA META POR OBRA. UN REGISTRO POR OBRA.
                    </td>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn2" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        2-REPORTE DE INVERSI&Oacute;N P&Uacute;BLICA TODAS LAS METAS 
                    </td>
                    <td>
                        INCLUYE TODA LA INVERSI&Oacute;N P&Uacute;BLICA A NIVEL OBRA. SE REFLEJAN TODAS LAS META POR OBRA. SE PUEDEN REPETIR LOS REGISTROS DE LA OBRA POR CADA UNA DE SUS METAS.
                    </td>
                </tr>               
                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn8" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        3-REPORTE DE INVERSION PÚBLICA CALENDARIO
                    </td>
                    <td>
                        INCLUYE EL CALENDARIO DE TODAS LAS ACTIVIDADES TIPO 2
                    </td>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn7" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        4-REPORTE DE INVERSION PUBLICA PARTIDAS
                    </td>
                    <td>
                        INCLUYE LAS PARTIDAS DE TODAS LAS ACTIVIDADES TIPO 2
                    </td>
                </tr>
                <tr bgcolor="#EAEAEA">
                <td>
                    <button id="btn11" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                </td>
                <td>
                    5-REPORTE TRIMESTRAL INDICADORES INVERSION
                </td>
                <td>
                    INCLUYE LAS ACTIVIDADES PRESUPUESTALES DE INVERSIÓN PÚBLICA
                </td>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn3" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        6-REPORTE GASTO OPERATIVO FINANCIERO (PRINCIPAL)
                    </td>
                    <td>
                        INCLUYE TODAS LAS ACTIVIDADES TIPO 1. ÚNICAMENTE SE REFLEJA UNA META POR ACTIVIDAD. UN REGISTRO POR ACTIVIDAD.
                    </td>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn4" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        7-REPORTE GASTO OPERATIVO TODAS LAS METAS
                    </td>
                    <td>
                        INCLUYE TODAS LAS ACTIVIDADES TIPO 1. SE REFLEJAN TODAS LAS METAS DE LA ACTIVIDAD. SE PUEDEN REPETIR LOS REGISTROS DE LAS ACTIVIDADES POR CADA UNA DE SUS METAS.
                    </td>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn5" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        8-REPORTE GASTO OPERATIVO CALENDARIO
                    </td>
                    <td>
                        INCLUYE EL CALENDARIO DE TODAS LAS ACTIVIDADES TIPO 1
                    </td>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn6" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        9-REPORTE GASTO OPERATIVO PARTIDAS
                    </td>
                    <td>
                        INCLUYE LAS PARTIDAS DE TODAS LAS ACTIVIDADES TIPO 1
                    </td>
                </tr>

                <tr bgcolor="#EAEAEA">
                    <td>
                        <button id="btn9" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                    </td>
                    <td>
                        10-REPORTE CONTRATOS
                    </td>
                    <td>
                        INCLUYE CONTRATOS, OBRAS - CONTRATOS, RETENCIONES CONTRATOS
                    </td>
                </tr>

                <tr bgcolor="#EAEAEA">
                <td>
                    <button id="btn10" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                </td>
                <td>
                    11-REPORTE TRIMESTRAL INDICADORES OPERATIVO
                </td>
                <td>
                    INCLUYE LAS ACTIVIDADES PRESUPUESTALES GASTO OPERATIVO
                </td>

                <tr bgcolor="#EAEAEA">
                <td>
                    <button id="btn12" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                </td>
                <td>
                    12-PERFIL PROGRAMAS
                </td>
                <td>
                    REPORTE DE PERFIL PROGRAMAS
                </td>

                <tr bgcolor="#EAEAEA">
                <td>
                    <button id="btn13" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                </td>
                <td>
                    13-PERFIL FIN
                </td>
                <td>
                    REPORTE DE PERFIL FIN
                </td>

                <tr bgcolor="#EAEAEA">
                <td>
                    <button id="btn14" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                </td>
                <td>
                    14-PERFIL PROPÓSITO
                </td>
                <td>
                    REPORTE DE PERFIL PROPÓSITO
                </td>
        
                <tr bgcolor="#EAEAEA">
                <td>
                    <button id="btn15" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                </td>
                <td>
                    15-PERFIL COMPONENTE
                </td>
                <td>
                    REPORTE DE PERFIL COMPONENTE
                </td>
            
                <tr bgcolor="#EAEAEA">
                <td>
                    <button id="btn16" class="btnExpo btn btn-default"><i class="glyphicon glyphicon-th"></i> Exportar </button>
                </td>
                <td>
                    16-PERFIL ACTIVIDADES
                </td>
                <td>
                    REPORTE DE PERFIL ACTIVIDADES
                </td>
                
                </tr>
            </table>
        ';
        $this->mixHtml($html);

        $this->agregaTools("");

        $this->mixScript('
            $("#btn1").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/MonitoreoMejora.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn2").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/MonitoreoMejoratodasmetas.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn3").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/GastoOpeFinanciero.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn4").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/GastoOperativoTM.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn5").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/GastoOpeCalendario.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn6").click(function(){
                comunica("scp/reportes/ControladorMonitoreoControl.php",{metodo:"capturarGpo", toa_id : "1", ruta: "rep/excel/scp/reportes/GastoOpePartidas.php"});
            });
            $("#btn7").click(function(){
                comunica("scp/reportes/ControladorMonitoreoControl.php",{metodo:"capturarGpo", toa_id : "2", ruta: "rep/excel/scp/reportes/GastoOpePartidas.php"});
            });
            $("#btn8").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/InvPublicaCalendario.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn9").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/RepContratos.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn10").click(function(){
                comunica("scp/reportes/ControladorMonitoreoControl.php",{metodo:"capturarGpo", toa_id : "1", ruta: "rep/excel/scp/reportes/RepTrimIndicador.php"});
            });
            $("#btn11").click(function(){
                comunica("scp/reportes/ControladorMonitoreoControl.php",{metodo:"capturarGpo", toa_id : "2", ruta: "rep/excel/scp/reportes/RepTrimIndicador.php"});
            });
            $("#btn12").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/PerfilProgramas.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn13").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/PerfilFin.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn14").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/PerfilProposito.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn15").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/PerfilComponente.php";
                m.target = "_blank";
                m.submit();
            });
            $("#btn16").click(function(){
                m = document.getElementById("rep");
                m.action = "rep/excel/scp/reportes/PerfilActividad.php";
                m.target = "_blank";
                m.submit();
            });
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

        ');
    }

    public function capturarGpo()
        {

           // print_r($this->prm);

            $combogpo		 	= new stdClass();
            $combogpo->sql= "
                SELECT 'X' as gpo_id, 'TODOS LOS GRUPOS' as gpo_nombre
                 UNION ALL
                SELECT gpo_id, gpo_nombre
                  FROM ctrl2023.cat_Grupo
                 WHERE gpo_id != 0
                ";
            $combogpo->id		= 'gpo_id';
            $combogpo->label = 'SELECCIONE EL GRUPO';
            $combogpo->name		= 'gpo_id';
            $combogpo->enDiv   = true;
            $combogpo->mensaje   = false;
            $combogpo->detonar   = false;

            $combogpo->selecciona=(isset($this->prm->gpo_id) && $this->prm->gpo_id != "") ? $this->prm->gpo_id : "X";
            $combogpo->change='
				if( $(this).val() != "" || $(this).val()!=null){	
					$("#ur_id").removeAttr("disabled");
					comunica("'.$this->pantalla.'",{
					metodo:"cambiaUR",
                    gpo_id:$("#gpo_id").val(),
                    ruta: "'.$this->prm->ruta .'",
					});
				}else{
				    $("#ur_id").val = "X";
					$("#ur_id").attr("disabled","disabled");
                }	
			';

            $combour		 	= new stdClass();
            $combour->sql= "SELECT 'X' AS ur_id,'TODOS LAS UR´S' as ur_nombre,'000' ur_clave
						UNION ALL
							SELECT concat(gpo_id,ur_id)ur_id,ur_nombre,ur_clave
							FROM enc_FiltroClc 
							INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
							INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)	
							GROUP BY gpo_id,ur_id,ur_nombre,ur_clave;";
            $combour->id		= 'ur_id';
            $combour->label = 'SELECCIONE LA UNIDAD RESPONSABLE';
            $combour->name		= 'ur_id';
            $combour->enDiv   = true;
            $combour->detonar   = false;
            $combour->mensaje   = false;
            $combour->comoAtr="ur_clave";
            $combour->selecciona=(isset($this->prm->ur_id) && $this->prm->ur_id != "") ? $this->prm->ur_id : "X";

            $btnExportar= new stdClass();
            $btnExportar->id='expExcel';
            $btnExportar->color='azul';
            $btnExportar->text=' Exportar';
            // $btnExportar->value=' Exportar';
            $btnExportar->form='filtros';
            $btnExportar->type='submit';
            $btnExportar->value = " Exportar a Excel";
            $btnExportar->icon='glyphicon glyphicon-list';
            $btnExportar->class = "btn btn-info ";

            $btnRegresar = new stdClass();
            $btnRegresar->id = "regresar";
            $btnRegresar->name = " regresar";
            $btnRegresar->value = " Regresar";
            $btnRegresar->icon = "hand-left";
            $btnRegresar->class = "btn btn-test ";


            $this->agregaTools($this->ponBoton($btnRegresar). " ".$this->ponBoton($btnExportar));
            /*
            '.$this->ponBoton($btnExportar).'
            '.(($this->prm->nomrep!="rep/excel/ExportaRepObraCveFin.php" && $this->prm->nomrep!="rep/excel/ExportaRepObraCveFin2.php") ? $this->ponBoton($btnLimpiar):"").'
            '.$this->ponBoton($btnRegresar).'
            */
            // mme_gpo_id:$(this).attr("mme_gpo_id"),

            //echo($this->prm) ;

            
            //var_dump(get_object_vars($this->prm));
            //print_r($this->prm);
            //die();
            $this->mixHtml('
				<form id="filtros" name="filtros" method="post" action="" target="_blank">
					<div class="row">
					        <input type="hidden" id="toa_id" name="toa_id" value="' . $this->prm->toa_id . '">
                            <fieldset class="col-md-12 col-xs-12">
                            ' . $this->ponCombo($combogpo) . '
                            </fieldset>
                            
                            <fieldset class="col-md-12 col-xs-12">
                            ' . $this->ponCombo($combour) . '
                            </fieldset>
                        	
					</div>

				</form>
			');

         //   echo $this->prm->ruta;


        $this->recargaCombo($combour);
        $this->recargaCombo($combogpo);
        $this->mixScript('
				$("#ur_id").attr("disabled","disabled");

				$("#expExcel").click(function(){
                    if($("#gpo_id").val()=="X" || $("#ur_id").val()=="X"){
                        notify("Elige un Grupo y una Unidad Responsable", "warning");
                        return false;
                    }else{
                         $("#filtros").attr("action","' . $this->prm->ruta . '");
                         $("#filtros").submit();
                         return false;
                         }
				});
				
				$("#limpiar").click(function(){
					 $(".clean").val("");
					 return false;
				});
				
		
				$("#regresar").click(function(){
					comunica( "scp/reportes/ControladorMonitoreoControl.php",
					{ 
						metodo:"main"
					});	
					return false;
				});
				
			');



        }

        private function cambiaUR()
        {
            //print_r($this->prm);
            $this->prm->gpo_id = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '';
            if ($this->prm->gpo_id != '') {
                $combour		 	= new stdClass();
                $combour->id		= 'ur_id';
                $combour->label = 'SELECCIONE LA UNIDAD RESPONSABLE';
                $combour->name		= 'ur_id';
                $combour->enDiv   = true;
                $combour->detonar   = false;
                $combour->mensaje   = false;
                if($this->prm->gpo_id != 'X'){

                    $combour->sql= ' SELECT "X" AS ur_id,"TODOS LAS UR´S" as ur_nombre,"000" ur_clave
						UNION ALL
							SELECT concat(gpo_id,ur_id)ur_id,ur_nombre,ur_clave
							FROM enc_FiltroClc
							INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
							INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
                           WHERE gpo_id= ' . $this->prm->gpo_id . ' 
							GROUP BY gpo_id,ur_id,ur_nombre,ur_clave;';

                    if($this->prm->gpo_id == '1' & $this->prm->ruta == "rep/excel/scp/reportes/GastoOpePartidas.php" ){
                        $combour->sql= ' SELECT "X" AS ur_id,"TODOS LOS REGISTROS" as ur_nombre,"000" ur_clave
						UNION ALL
							  SELECT *
                                FROM
                                     (
                                         SELECT concat(gpo_id, ur_id)ur_id, ur_nombre, ur_clave
                                           FROM enc_FiltroClc
                                                    INNER JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
                                                    INNER JOIN aux_UnidadResponsable USING (gpo_id, ur_id)
                                                     WHERE gpo_id= ' . $this->prm->gpo_id . ' 
                                          GROUP BY gpo_id, ur_id, ur_nombre, ur_clave
                                         UNION ALL
                                                        SELECT "115","INVERSIÓN, PREVISIÓN Y PARIPASSU","115"
                                                        ORDER BY ur_id
                                     ) A;
                                ';
                    }
                    $this->recargaCombo($combour);
                }else{
                    $combour->sql= ' SELECT "X" AS ur_id,"TODOS LOS REGISTROS" as ur_nombre,"000" ur_clave;';
                    $this->recargaCombo($combour);
                    $combour->selecciona=(isset($this->prm->ur_id) && $this->prm->ur_id != "") ? $this->prm->ur_id : "X";
                }
            } else {
                $this->mixScript(' notify("Elige el Grupo", "warning");');
                return;

            }

        }
}


if (isset($_SESSION[_S]['s24-prm']['S']['4']['8']['1'])){
	new ControladorMonitoreoControl();
}
else WebResponse::sinPermisos();

?>