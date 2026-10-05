<?php

	//inicio de sesion y llamada a las librerias
	session_start();
	require_once dirname(__FILE__).'/../../lib/WebResponse.php';
	require_once dirname(__FILE__).'/../../ent/UnidadResponsable.php';
	require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';

	class ControladorBienesMuebles extends WebResponse{

		public function __construct( ){
			//mandar a llamar constructor base
			parent::__construct();
			if(isset($this->prm->opcion)){$_SESSION[_S]['reportes'] = $this->prm->opcion->sis_id.$this->prm->opcion->men_id.$this->prm->opcion->opc_id; }

					switch ($this->metodo)
					{
						case 'main': $this->main(); break;
						case 'mesHasta': $this->mesHasta(); break;
						default: $this->main(); break;
					}
			$this->returnJson();
		}

		private function main(){

			$ur = new UnidadResponsable();
			$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
			$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
			$ur->select();

			$tipoReporte = new stdClass();
    		// $tipoReporte->opciones = array(1=>"Solo la UR actual",2=>"Todas las UR de la Carga de Trabajo",3=>"Limitar que incluir y que no");
			switch($_SESSION[_S]['nvj_id']){
				case 3:
				// case 4:
					$tipoReporte->opciones = array(1=>"Solo la UR actual",2=>"Todas las UR de la Carga de Trabajo");
				break;
				default:
					$tipoReporte->opciones = array(1=>"Solo la UR actual",2=>"Todas las UR de la Carga de Trabajo",3=>"Limitar que incluir y que no");
				break;
			}
    		$tipoReporte->id = "tipoReporte";
    		$tipoReporte->label = "Tipo de Reporte";
    		$tipoReporte->selecciona = 1;
    		$tipoReporte->width = '150px';

			$comboMesDesde = new stdClass();
    		$comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable";
    		$comboMesDesde->id = "comboMesDesde";
    		$comboMesDesde->label = "Desde";
    		$comboMesDesde->comoAtr = 'msc_nombre';
    		$comboMesDesde->selecciona = 1;
    		$comboMesDesde->enDiv = true;
    		// $comboMesDesde->width = '150px';

			$comboMesHasta = new stdClass();
			$comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable;";
			$comboMesHasta->id = "comboMesHasta";
			$comboMesHasta->width = "150px";
			$comboMesHasta->label = "Hasta";
			$comboMesHasta->selecciona = 1;

			$grupos = new stdClass();
    		$grupos->disabled = true;
    		$grupos->id = "grupos";
    		$grupos->class = "paso";
    		$grupos->value = "1,2,3,7,8";
    		$grupos->enDiv = true;
    		$grupos->label = "Grupos a incluir";

			$excluir = new stdClass();
    		$excluir->disabled = true;
    		$excluir->id = "excluir";
    		$excluir->enDiv = true;
    		$excluir->class = "paso";
    		$excluir->label = "URs a excluir";

			$firmante1 = new stdClass();
			$firmante1->id = "firmante1";
			$firmante1->enDiv = true;
			$firmante1->value = $ur->getNombreFinancieros();
			$firmante1->label = "Firmante 1";

			$firmante2 = new stdClass();
			$firmante2->id = "firmante2";
			$firmante2->enDiv = true;
			$firmante2->value = $ur->getNombreAdministrativo();
			$firmante2->label = "Firmante 2";

			$firmante3 = new stdClass();
			$firmante3->id = "firmante3";
			$firmante3->enDiv = true;
			$firmante3->value = $ur->getNombreTitular();
			$firmante3->label = "Firmante 3";

			$cargo1 = new stdClass();
			$cargo1->id = "cargo1";
			$cargo1->enDiv = true;
			$cargo1->value = $ur->getCargoFinancieros();
			$cargo1->label = "Cargo 1";

			$cargo2 = new stdClass();
			$cargo2->id = "cargo2";
			$cargo2->enDiv = true;
			$cargo2->value = $ur->getCargoAdministrativo();
			$cargo2->label = "Cargo 2";

			$cargo3 = new stdClass();
			$cargo3->id = "cargo3";
			$cargo3->enDiv = true;
			$cargo3->value = $ur->getCargoTitular();
			$cargo3->label = "Cargo 3";

			$nombreEspecial = new stdClass();
    		$nombreEspecial->disabled = true;
    		$nombreEspecial->id = "nombreEspecial";
    		$nombreEspecial->enDiv = true;
    		$nombreEspecial->class = "paso";
    		$nombreEspecial->placeholder="Ejem: SECTOR CENTRAL";
    		$nombreEspecial->label = "Encabezado Personalizado";

			$ue = new stdClass();
    		$ue->id = "ue";
    		$ue->enDiv = true;
			switch($_SESSION[_S]['nvj_id']){
				case 1:
					$ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = '.$_SESSION[_S]['clasAdm-gpo'].' AND ur_id = '.$_SESSION[_S]['clasAdm-ur'].' AND ue_id ='.$_SESSION[_S]['s24-ue'].';';
				break;
				case 2:
					$ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = '.$_SESSION[_S]['clasAdm-gpo'].' AND ur_id = '.$_SESSION[_S]['clasAdm-ur'].';';
				break;
				case 3:
				case 4:
					$this->conectar();
					$this->setSql('
						SELECT ue_id,gpo_id FROM cat_CargaTrabajo WHERE
						gpo_id = '.$_SESSION[_S]['clasAdm-gpo'].'
						AND ur_id = '.$_SESSION[_S]['clasAdm-ur'].'
						AND usr_id = '.$_SESSION[_S]['s24-id'].';
					');
					$this->consultar();
					$this->desconectar();
					$tieneUe = $this->extraer();

					if($tieneUe['ue_id']!=''){
						$ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
						INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id,ue_id)
						WHERE gpo_id = '.$_SESSION[_S]['clasAdm-gpo'].'
						AND ur_id = '.$_SESSION[_S]['clasAdm-ur'].'
						AND c.usr_id = '.$_SESSION[_S]['s24-id'].';';

					}else if($tieneUe['gpo_id']!=''){
						$ue->sql = 'SELECT u.ue_id,ue_nombre FROM cat_UnidadEjecutora u
						INNER JOIN cat_CargaTrabajo c USING(gpo_id,ur_id)
						WHERE gpo_id = '.$_SESSION[_S]['clasAdm-gpo'].'
						AND ur_id = '.$_SESSION[_S]['clasAdm-ur'].'
						AND c.usr_id = '.$_SESSION[_S]['s24-id'].';';
					}else{
						$ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
						WHERE gpo_id = '.$_SESSION[_S]['clasAdm-gpo'].'
						AND ur_id = '.$_SESSION[_S]['clasAdm-ur'].';';
					}
				break;
				default:
					$ue->sql = 'SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora
								WHERE gpo_id = '.$_SESSION[_S]['clasAdm-gpo'].'
								AND ur_id = '.$_SESSION[_S]['clasAdm-ur'].';';
				break;
			}
    		$ue->label = "Unidad Ejecutora";

			$btnPDF = new stdClass();
			$btnPDF->id = "pdf";
			$btnPDF->class = "btnImp btn-success";
			$btnPDF->value = ". PDF";
			$btnPDF->icon='btn glyphicon-print';
			$btnPDF->type = "submit";
			$btnPDF->form = "formEdo";


			$btnEXCEL = new stdClass();
			$btnEXCEL->id = "excel";
			$btnEXCEL->class = "btnImp btn-info";
			$btnEXCEL->icon='btn glyphicon-list-alt';
			$btnEXCEL->value = ". EXCEL";
			$btnEXCEL->type = "submit";
			$btnEXCEL->form = "formEdo";

			$this->conectar();
			$this->setSql($ue->sql);
			$this->consultar();
			$countUEnum = $this->getNumeroRegistros();
			$this->desconectar();

			$html = '
				<legend align="center"></legend>
				<form id="formEdo" method="post" target="_blank">
					<div class="row">
						<fieldset class="col-md-2 col-xs-2">
							'.$this->ponCombo($comboMesDesde).'
						</fieldset>
						<fieldset id="mhasta" class="col-md-2 col-xs-2">
							'.$this->ponCombo($comboMesHasta).'
						</fieldset>
						<fieldset class="col-md-4 col-xs-4">
									<input type="hidden" id="numUe" name="numUe"  value='.$countUEnum.'>
							'.($_SESSION[_S]['nvj_id'] >=3 ? $this->ponCombo($tipoReporte) : '').'
							'.$this->ponCombo($ue).'
						</fieldset >
						<fieldset class="col-md-2 col-xs-2">
							<label>Tipo de Importe:</label><br/>
							<input type="checkbox" value="1" name="ckmiles" id="ckmiles"/> En miles de pesos<br/>
						</fieldset>
						<fieldset class="col-md-2 col-xs-2">
							<label>Leyenda:</label><br/>
							<input type="checkbox" value="" name="leyenda" id="leyenda"/> Con leyenda
						</fieldset>
					</div><br/>
					'.( ( $_SESSION[_S]['nvj_id'] >= 5 ) ?
						'<div class="row">
							<fieldset class="col-md-3 col-xs-3">
								'.$this->ponInput($grupos).'
							</fieldset>
							<fieldset class="col-md-3 col-xs-3">
								'.$this->ponInput($excluir).'
							</fieldset>
							<fieldset class="col-md-6 col-xs-6">
								'.$this->ponInput($nombreEspecial).'
							</fieldset>
						</div>'
						: '' ) .'
						<div class="row">
							<fieldset class="col-md-6 col-xs-6">
								'.$this->ponInput($firmante1).'
							</fieldset>
							<fieldset class="col-md-6 col-xs-6">
								'.$this->ponInput($cargo1).'
							</fieldset>
						</div>
						<div class="row">
							<fieldset class="col-md-6 col-xs-6">
								'.$this->ponInput($firmante2).'
							</fieldset>
							<fieldset class="col-md-6 col-xs-6">
								'.$this->ponInput($cargo2).'
							</fieldset>
						</div>
						<div class="row">
							<fieldset class="col-md-6 col-xs-6">
								'.$this->ponInput($firmante3).'
							</fieldset>
							<fieldset class="col-md-6 col-xs-6">
								'.$this->ponInput($cargo3).'
							</fieldset>
						</div>
						<br />
						<div class="row">
							<!--<fieldset class="col-xs-6" style="padding-right: 1px !important;">
								<legend>Niveles a Incluir</legend>
								<input id="checkNivel1" name="checkNivel1" type="checkbox" value=1 readonly="readonly" checked onclick="javascript: return false;">&nbsp;1</input>&nbsp;&nbsp;&nbsp;
								<input id="checkNivel2" name="checkNivel2" type="checkbox" value=1 checked>&nbsp;2</input>&nbsp;&nbsp;&nbsp;
								<input id="checkNivel3" name="checkNivel3" type="checkbox" value=1 checked>&nbsp;3</input>&nbsp;&nbsp;&nbsp;
								<input id="checkNivel4" name="checkNivel4" type="checkbox" value=1>&nbsp;4</input>&nbsp;&nbsp;&nbsp;
								<input id="checkNivel5" name="checkNivel5" type="checkbox" value=1>&nbsp;5</input>&nbsp;&nbsp;&nbsp;
							</fieldset>	-->
							<fieldset class="col-xs-6" style="padding-right: 1px !important;">
								<legend>Generación de Reporte</legend>
								<input id="ceros" name="ceros" type="checkbox" value=1>&nbsp;Cuentas sin importe</input>
							</fieldset>
						</div><br/>
						<div class="row">
							<fieldset class="col-md-3 col-xs-6">

							</fieldset>
						</div>
				</form>
			';

			$this->mixHtml($html);

			/*$this->agregaTools(( (isset($_SESSION[_S]['s24-prm']['C']['9']['10']['47']) || isset($_SESSION[_S]['s24-prm']['C']['6']['10']['47'])) ? $this->ponBoton($btnEXCEL):"").' '.( (isset($_SESSION[_S]['s24-prm']['C']['9']['10']['1']) || isset($_SESSION[_S]['s24-prm']['C']['6']['10']['1'])) ? $this->ponBoton($btnPDF):""));*/

			$this->agregaTools($this->ponBoton($btnPDF).' '.$this->ponBoton($btnEXCEL));

			$this->mixScript(
				ClasificacionAdministrativa::onChangeUr('
				comunica("'.$this->pantalla.'",{metodo:"main"});
			'));

			$this->mixScript('

				$("#comboMesDesde").change(function(){
					comunica("'.$this->pantalla.'", {
						metodo: "mesHasta",
						mes: $(this).val()
					});
				});

				$("#leyenda").click(function(){
					if($("#leyenda").is(":checked"))
						$("#leyenda").val("1");
					else
						$("#leyenda").val("");
				});

				$("#pdf").click(function(){
					$("#formEdo").attr("action","rep/doc/scg/cuentaPublica/rep_BienesMuebles.php");
					$("#formEdo").submit();
				});
				$("#excel").click(function(){
						$("#formEdo").attr("action","rep/excel/scg/cuentaPublica/exp_BienesMuebles.php");
					$("#formEdo").submit();
				});

				$("#tipoReporte").change(function(){
					var nOpcion = $(this).val();
					switch(nOpcion){
						case "1":
							$(".paso").attr("disabled", "disabled");
							$("#ue").removeAttr("disabled");
						break;
						case "2":
							$("#ue").attr("disabled",true);
							$(".paso").attr("disabled", "disabled");
						break;
						case "3":
							$(".paso").removeAttr("disabled");
							$("#ue").attr("disabled",true);
						break;
					}
				});

			');
		}

		private function mesHasta(){
			$this->setContenedor('mhasta');
			if(isset($this->prm->mes)){
				$mes = $this->prm->mes;

				$comboMesHasta = new stdClass();
				$comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable WHERE msc_id >= ".$mes;
				$comboMesHasta->id = "comboMesHasta";
				$comboMesHasta->width = "150px";
				$comboMesHasta->label = "hasta";
				$comboMesHasta->selecciona = $mes;

				$this->mixHtml( $this->ponCombo( $comboMesHasta ) );
			}
		}

	}

	 if( (isset( $_SESSION[_S]['s24-prm']['C']['9']['10']) ) ){
		new ControladorBienesMuebles( );
	 } else {
		 $error = new WebResponse();
		 $error->sinPermisos();
	 }

// Fin del código
