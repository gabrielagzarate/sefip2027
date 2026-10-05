<?php

	session_start();
	// ini_set('memory_limit','0');

	set_time_limit(0);
	//librerias
	require_once('../../../lib/reportetabla.1.8.php');
	require_once('../../../lib/conversionNumLetras.php');
    require_once('../../../../lib/MesNombre.php');
	require_once('../../../../lib/AccesoDatos.php');
	require_once('../../../../ent/UnidadResponsable.php');
	// require_once('../../../../lib/funcionesSCG.php');

	if (!function_exists('u8d')) {
		function u8d($value)
		{
			return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
		}
	}

	class reportetabla extends PDF {

		public $mes;
		public $texto;
		private $longitud = 80;
        public $longitud2 = 65;
        public $ultBorde = 0;
        var $widths = '';

		public function  __construct(){
			parent::__construct();

			$this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['s24-gpo'];
            $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['s24-ur'];
			$this->mesDesde = isset( $_POST['comboMesDesde'] ) ?  $_POST['comboMesDesde'] : -1;//Informacion de acuerdo al mes
			$this->mesHasta = isset( $_POST['comboMesHasta'] ) ?  $_POST['comboMesHasta'] : -1;//Informacion de acuerdo al mes
			$this->firmante1 = isset( $_POST['firmante1'] ) ?  $_POST['firmante1'] : "";
			$this->firmante2 = isset( $_POST['firmante2'] ) ?  $_POST['firmante2'] : "";
			$this->firmante3 = isset( $_POST['firmante3'] ) ?  $_POST['firmante3'] : "";
			$this->cargo1 = isset( $_POST['cargo1'] ) ?  $_POST['cargo1'] : "";
			$this->cargo2 = isset( $_POST['cargo2'] ) ?  $_POST['cargo2'] : "";
			$this->cargo3 = isset( $_POST['cargo3'] ) ?  $_POST['cargo3'] : "";
			$this->leyenda = isset( $_POST['leyenda'] ) ?  $_POST['leyenda'] : "";
			$this->tipoReporte = isset( $_POST['tipoReporte'] ) ?  $_POST['tipoReporte'] : "";
			$this->grupos = isset( $_POST['grupos'] ) ? $_POST['grupos'] : "";
			$this->excluir = isset( $_POST['excluir'] ) ? $_POST['excluir'] : "";
			$this->checkMiles = isset( $_POST['ckmiles'] ) ? 1000 : 1;
			$this->ceros = isset( $_POST['ceros'] ) ?  $_POST['ceros'] : "";
			$this->ue = isset( $_POST['ue'] ) ?  $_POST['ue'] : "";
            $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : "";

			$_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte']:1);

			$this->nivel[1] = isset( $_POST['checkNivel1'] ) ? $_POST['checkNivel1'] : -1;
            $this->nivel[2] = isset( $_POST['checkNivel2'] ) ? $_POST['checkNivel2'] : -1;
            $this->nivel[3] = isset( $_POST['checkNivel3'] ) ? $_POST['checkNivel3'] : -1;
            $this->nivel[4] = isset( $_POST['checkNivel4'] ) ? $_POST['checkNivel4'] : -1;
            $this->nivel[5] = isset( $_POST['checkNivel5'] ) ? $_POST['checkNivel5'] : -1;

			$this->ejercicio = "";

			if( $this->ejercicio == "" ){
                $this->ur = new UnidadResponsable( );
                $this->ur->setGpo_id( $this->gpo_id );
                $this->ur->setUr_id( $this->ur_id );
                $this->ur->select();
            }else{
                //obtenemos los datos de la UR del ejercicio solicitado
            }
		}
		function Footer( $lastPage = true){

			$this->AddFont("univiapro","");
			$this->AddFont("univiapro","B");
			$this->AddFont("univiapro");
			$row = $this->getDatos();
			$tmpb=$this->GetBorders();
			if($this->leyenda==1 || ($this->cargo1!='' && $this->cargo2!='' && $this->firmante1!='' && $this->firmante2!='')){
				$this->SetY(-35);
				$this->Cell(190,2,u8d(''),0,0,'C');
				$this->Ln(5);
			}
			else
				$this->SetY(-20);

			if ($lastPage){
				if($this->leyenda==1){
					$this->SetFont('univiapro','',7);
					if($_SESSION[_S]['reportes']=='C911' && $this->mesDesde>=1 && $this->mesHasta<=3 && ($_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur']==122 && $_SESSION[_S]['nvj_id']==2))
						$this->Cell(200,2,u8d("Lugar y fecha de generación: Reyes Mantecón, Oaxaca"),0,0,'C');
					else
					$this->Cell(200,2,u8d("Lugar y fecha de generación: Reyes Mantecón, Oaxaca; a ".date("d")." de ".MesNombre( date("m") )." de ".date("Y")),0,0,'C');
					$this->Ln(2);
					$this->Cell(200,2,u8d("Bajo protesta de decir verdad declaramos que los estados financieros y sus Notas son razonablemente correctos y responsabilidad del emisor."),0,0,'C');
					$this->Ln(1);
				}
				$this->SetFont('univiapro','B',7);
				$y=$this->GetY();

				$this->Ln(2);

				$y = $this->getY();
				$x = $this->getX();
				$x=5;
				$this->setX(10);
				$this->multicell(57,3,u8d($this->cargo1),0,'C');
				$this->setY($y);
				$this->setX($x+70);
				$x = $this->getX();
				$this->multicell(57,3,u8d($this->cargo2),0,'C');
				$this->setY($y);
				$this->setX($x+70);
				$this->multicell(57,3,u8d($this->cargo3),0,'C');


				// $this->Cell(60,4,u8d($this->cargo1), 0,0,'C');
				// $this->Cell(60,4,u8d($this->cargo2),0,0,'C');
				// $this->Cell(60,4,u8d($this->cargo3),0,1,'C');
				$this->Ln(5);
				$this->SetFont('univiapro','',7);
				$y = $this->getY();
				$x = $this->getX();
				$x=5;

				$this->setX(10);
				$this->multicell(57,3,u8d($this->firmante1),0,'C');
				$this->setY($y);
				$this->setX($x+70);
				$x = $this->getX();
				$this->multicell(57,3,u8d($this->firmante2),0,'C');
				$this->setY($y);
				$this->setX($x+70);
				$this->multicell(57,3,u8d($this->firmante3),0,'C');

				// $this->Cell(60,2,u8d($this->firmante1),0,0,'C');
				// $this->Cell(60,2,u8d($this->firmante2),0,0,'C');
				// $this->Cell(60,2,u8d($this->firmante3),0,1,'C');
			}

			$this->SetY(-11);
			$this->SetFont('univiapro','',5);
			$this->Cell(200,18,u8d('Página').$this->PageNo().'/{nb}',0,0,'C');
		}

		// Marcos: 22/mar/2018
		// Intentaremos estandarizar la impresión del logo y las líneas de encabezado, para no estar inventando
		// el hilo negro en cada reporte.php
		// $orientacion: P para portrait, L para landscape
		function drawHeadersAndLogo( $orientacion, $linea1 = "", $linea2 = "", $linea3 = "", $linea4 = "", $linea5 = "", $linea6 = "", $linea7 = "" ){

            $this->ancho = 200;

            $this->anchoImagen = 74;
            $this->altoImagen = 22;
            $this->logo = ( $this->ancho - $this->anchoImagen ); //213;

	        $ix = $this->GetX();
	        $iy = $this->GetY();
	        $this->SetMargins(20, 10, 20);
	        $this->SetXY(20, 10);
	        $this->SetTextColor(0, 0, 0);
	        $this->SetFillColor(255, 255, 255);
	        $this->SetBorders(0);

	        $nLineas = 0;


	        // Dibujamos el logo
	        //$this->Image('../../../../img/logosefin-color-fblanco.jpg', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'jpg');
                $this->Image('../../../../img/logoOaxaca-color-fblanco.png', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'png');
			// Dibujamos las líneas de encabezado
			$this->SetFont('univiapro','B',7);

            if( trim( $linea1 ) != "" ) {
            	$this->SetX( 10 );
                $this->Cell( 235, 5, $linea1, 0, 0, 'L' );
                $this->SetY( $this->GetY() + 4 );
                $nLineas++;
            }
            if( trim( $linea2 ) != "" ) {
            	$this->SetX( 10 );
                $this->Cell( 235, 5, $linea2, 0, 0, 'L' );
                $this->SetY( $this->GetY() + 4 );
                $nLineas++;
            }
            if( trim( $linea3 ) != "" ) {
            	$this->SetX( 10 );
                $this->Cell( 235, 5, $linea3, 0, 0, 'L' );
                $this->SetY( $this->GetY() + 4 );
                $nLineas++;
            }
            if( trim( $linea4 ) != "" ) {
            	$this->SetX( 10 );
            	$this->Cell( 235, 5, $linea4, 0, 0, 'L' );
                $this->SetY( $this->GetY() + 4 );
                $nLineas++;
            }
            if( trim( $linea5 ) != "" ) {
            	$this->SetX( 10 );
                $this->Cell( 235, 5, $linea5, 0, 0, 'L' );
                $this->SetY( $this->GetY() + 4 );
                $nLineas++;
            }
            if( trim( $linea6 ) != "" ) {
            	$this->SetX( 10 );
                $this->Cell( 235, 5, $linea6, 0, 0, 'L' );
                $this->SetY( $this->GetY() + 4 );
                $nLineas++;
            }
            if( trim( $linea7 ) != "" ) {
            	$this->SetX( 10 );
                $this->Cell( 235, 5, $linea7, 0, 0, 'L' );
                $this->SetY( $this->GetY() + 4 );
                $nLineas++;
            }

            // Dejamos un espacio para la siguiente sección
            if ( $nlineas <= 4 ) {
            	$this->SetY( $this->GetY() + 2 );
            	$this->ln(5);
			}

			// Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
			// Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
			$this->setY(35);

		}














		function Header(){//Encabezado del documento

			$this->AddFont("univiapro","");
			$this->AddFont("univiapro","B");
			$this->AddFont("univiapro");

			$row = $this->getDatos();

			$ix=$this->GetX();
			$iy=$this->GetY();
			$this->SetFont('Arial','B',50);
			$this->SetTextColor(200,200,200);
			$this->RotatedText($ix+30,$iy+160,u8d('EN REVISIÓN'),45);
			$this->RotatedText($ix+150,$iy+160,u8d('EN REVISIÓN'),45);
			$this->RotatedText($ix,$iy+80,u8d('EN REVISIÓN'),45);
			$this->RotatedText($ix+150,$iy+80,u8d('EN REVISIÓN'),45);

			if($_SESSION[_S]['reportes']!='C911'){
				$linea1 = u8d("Cuenta de la Hacienda Pública Estatal ".substr( $_SESSION[_S]['ejercicio'], 0, 4 ));
			}
			if($_SESSION[_S]['nvj_id']>2)
				$linea2 = u8d("Secretaría de Finanzas del Gobierno del Estado de Oaxaca");
			else
				$linea2 = u8d("Gobierno del Estado de Oaxaca");

			if( $_SESSION[_S]['nvj_id'] > 2 ){

                switch ( $_POST['tipoReporte'] ){
                    case 2:   // Todas las URs de la carga de trabajo
                        $texto = "Dirección de Contabilidad Gubernamental";
                    break;
                    case 3:   // Determinar qué incluir y qué excluir
						$this->SetXY($ix+15,$iy+6);
						$ytmp=$this->GetY();
						$this->Cell(160,3,u8d("Dirección de Contabilidad Gubernamental"),0,0,'L');
                        $texto = isset( $_POST['nombreEspecial'] ) ? $_POST['nombreEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO";
                    break;
                    default:
                        if( $this->ejercicio == "" )
                            $texto = "Dirección de Contabilidad Gubernamental: ".$this->ur->getClave()." - ".$this->ur->getNombre();
                        else
                            $texto = "Dirección de Contabilidad Gubernamental: ".$this->ur['gpo_id'].$this->ur['ur_id']." - ".$this->ur['ur_nombre'];
                    break;

                }


            }else{
				 if( $this->ejercicio == "" )
                    $texto = "UR: ".$this->ur->getClave()." - ".$this->ur->getNombre();
                else
                    $texto = "UR: ".$this->ur['gpo_id'].$this->ur['ur_id']." - ".$this->ur['ur_nombre'];
			}

			$texto2='';
			if($this->ue!='' && $this->numUe>1){
				$ejecutora = new AccesoDatos();
				$ejecutora->conectar();
				$ejecutora->setSql('
					SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = '.$this->gpo_id.' AND ur_id = '.$this->ur_id.' AND ue_id = '.$this->ue.';
				');
				$ejecutora->consultar();
				$ejecutora->desconectar();
				$ue_id = $ejecutora->extraer();
				$texto2 = 'UE: '.$ue_id['ue_id'].' - '.$ue_id['ue_nombre'].'';
			}

			if($_POST['tipoReporte']!=3 && $_SESSION[_S]['nvj_id']>2) {
				$linea3 = u8d($texto);
			}

			if($this->ue!='' && $_SESSION[_S]['nvj_id']>2){
				$linea4 = u8d($texto2);
			}

			$linea5 = u8d('Relación de Bienes Inmuebles que Componen al Patrimonio');

			$mesId = ( isset( $_POST['comboMesDesde'] ) && $_POST['comboMesDesde'] != "" ) ? $_POST['comboMesDesde'] : '';

			$sqlMes =' select msc_nombre, msc_dias from cat_MesContable where msc_id='.$mesId.';';
			$con = new AccesoDatos();
			$con->conectar();
			$con->setSql($sqlMes);
			$con->consultar();
			$con->desconectar();
			$rowMes = $con->extraer();

			$mesHasta = ( isset( $_POST['comboMesHasta'] ) && $_POST['comboMesHasta'] != "" ) ? $_POST['comboMesHasta'] : '';

			$sqlMes2 =' select msc_nombre, msc_dias from cat_MesContable where msc_id='.$mesHasta.';';
			$con2 = new AccesoDatos();
			$con2->conectar();
			$con2->setSql($sqlMes2);
			$con2->consultar();
			$con2->desconectar();
			$rowMes2 = $con2->extraer();

			// if($_SESSION[_S]['reportes']=='C911'){
			if($this->mesDesde==0 and $this->mesHasta==0){
				$linea6 = u8d('Del mes de '.strtolower($rowMes['msc_nombre']).' '.substr( $_SESSION[_S]['ejercicio'], 0, 4 ));
			}elseif( $this->mesHasta == $this->mesDesde ){
				$linea6 = u8d('Del 1° al '.$rowMes['msc_dias'].' de '.strtolower($rowMes['msc_nombre']).' de '.substr( $_SESSION[_S]['ejercicio'], 0, 4 ));
			}else{
				if($this->mesDesde == 0)
					$linea6 = u8d('Del mes de '.strtolower($rowMes['msc_nombre']).' al '.$rowMes2['msc_dias'].' de '.strtolower($rowMes2['msc_nombre']).' de '.substr( $_SESSION[_S]['ejercicio'], 0, 4 ));
				else
					$linea6 = u8d('Del 1° de '.strtolower($rowMes['msc_nombre']).' al '.$rowMes2['msc_dias'].' de '.strtolower($rowMes2['msc_nombre']).' de '.substr( $_SESSION[_S]['ejercicio'], 0, 4 ));
			}

			if(isset($_POST['ckmiles']))
				$linea7 = '(Miles)';
			else
				$linea7 = '(Pesos)';

			if($_POST['tipoReporte']==3 || $_SESSION[_S]['nvj_id']<=2){
				$linea3 = u8d($texto);

				if($this->ue!='' && $_SESSION[_S]['nvj_id']<=2){
					$linea4 = u8d($texto2);
				}
			}

    		// Dibujamos el logo y las líneas del encabezado
    		$this->drawHeadersAndLogo( 'L', $linea1, $linea2, $linea3, $linea4, $linea5, $linea6, $linea7 );

			$this->setRelleno("si");
			$this->SetFont('univiapro','B',7);
			$this->SetTextColor(0,0,0);
			$this->setX(10);
			$this->SetDrawColor(180,180,180);
			$this->SetFillColor(220,220,220);
			$this->SetBorders(1);
			$this->setAltoLinea(5);
			$this->SetWidths(array(20,140,30));
			$this->SetAligns(array('C','C','C'));
			$this->Row(array(
				u8d('CÓDIGO'),
				u8d('DESCRIPCIÓN DEL BIEN'),
				u8d('IMPORTE')
			));
			$this->setX(10);
			$this->setRelleno("no");
			$this->SetBorders(0);

		}
	}
// Iniciamos el reporte
// --------------------------------------------------------------------------------------------------------------------------------

	$pdf=new Reportetabla('P','mm','Letter');
	$pdf->SetProtection(array("print","annot-forms"), "", PDF_PASSWORD_OWNER_SCG);
	if( $pdf->checkMiles == 1000 )
        $texto = "(en miles de pesos)";
	else
        $texto = "(pesos)";

	$pdf->setOrientation('P');
	$pdf->AliasNbPages();
	$pdf->texto = $texto;
	$pdf->AddPage();
	$pdf->setRelleno("no");
	$pdf->AddFont("univiapro","");
	$pdf->AddFont("univiapro","B");
	$pdf->AddFont("univiapro");
	$pdf->SetFont('univiapro','',7);
	$pdf->SetWidths(array(20,140,30));
	$pdf->SetAligns(array('C','L','R'));

	$otroWhere = "";
	if ( $pdf->tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5 ){
		// Si limitaron por grupo o excluyeron alguna UR
		if ( $pdf->grupos != "" ){
			$otroWhere .= "  WHERE gpo_id IN ( " . rtrim( $pdf->grupos ) . " ) AND ";

			// Si excluyeron a alguna UR
			if ( $pdf->excluir != "" )
				$otroWhere .= " ".($pdf->grupos != "" ? "": " WHERE ")." CONCAT( gpo_id,ur_id ) NOT IN ( " . rtrim( $pdf->excluir ) . " ) AND ";
		// Si no usaron las campos especiales, es un where estandar
		}
	}

	$sql='
	   SELECT ger_id, cgp_id, rub_id,
		cgcu_id, cgsc_id, cgsc_nombre,
		SUM( pdx_debe ) - SUM( pdx_haber ) AS pdx_importe
		FROM cat_Genero
		INNER JOIN cat_CgGrupo USING( ger_id )
		INNER JOIN cat_Rubro USING( ger_id, cgp_id )
		INNER JOIN cat_CgCuenta USING( ger_id, cgp_id, rub_id )
		INNER JOIN cat_CgSubcuenta USING( ger_id, cgp_id, rub_id, cgcu_id )
		INNER JOIN cg_PolizaDetalleAuxiliar USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
		INNER JOIN cg_PolizaMaestro USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
		INNER JOIN cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
		'.( $pdf->tipoReporte == 2 ? ( $_SESSION[_S]['nvj_id'] < 6 ? " INNER JOIN cat_CargaTrabajo t USING(gpo_id,ur_id)
		":"")." WHERE ":(( $pdf->tipoReporte == 3) ? $otroWhere :"
		WHERE gpo_id = $pdf->gpo_id AND ur_id = $pdf->ur_id ".( $pdf->ue!='' ? " AND ue_id = ".$pdf->ue."":"")."
		AND " )).'
		ger_id = 1 AND cgp_id = 2 AND rub_id = 3
		AND epl_id NOT IN ( "C", "H" )
		'.( $pdf->mesHasta < 0 ? " AND msc_id < $pdf->mesDesde":( $pdf->mesDesde == $pdf->mesHasta ? " AND msc_id = $pdf->mesDesde":" AND ( msc_id BETWEEN $pdf->mesDesde AND $pdf->mesHasta )" ) ).'
		'.( $pdf->tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? "AND t.usr_id = ".$_SESSION[_S]['s24-id']:"").'
		GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id;
	';

	// print_r($sql); die();
	//$pdf->Row(array($sql));
	$miconexion = new AccesoDatos();
	$miconexion->conectar();
	$miconexion->setSql($sql);
	$miconexion->consultar();
	$miconexion->desconectar();

	$f= new stdClass();
	$f->font = 'univiapro';
	$f->style = 'B';
	$f->size = '6';
	$fuentes = array($f);

	$f= new stdClass();
	$f->font = 'univiapro';
	$f->style = '';
	$f->size = '6';
	array_push( $fuentes, $f );

	$f= new stdClass();
	$f->font = 'univiapro';
	$f->style = '';
	$f->size = '6';
	array_push( $fuentes, $f );

	$total = 0;
	$pdf->setX(10);
	while($row = $miconexion->extraer()){
		$total+=$row['pdx_importe'];
		$cuenta = $row['ger_id'].$row['cgp_id'].$row['rub_id'].$row['cgcu_id'].$row['cgsc_id'];
		$importe = $row['pdx_importe'];
		$importe =  (isset($importe) ? number_format(round($importe/$pdf->checkMiles,0),0):"");
		$pdf->setFuentes( $fuentes );
		$pdf->Row(array($cuenta,u8d($row['cgsc_nombre']),"$".$importe),'');

		if($pdf->getY() > 180 ){
			$pdf->addPage();
		}
	}

	$pdf->Row(array('','TOTAL',"$".number_format(round($total/$pdf->checkMiles,0),0)),'');
	$pdf->Output();

// Fin del código fuente
