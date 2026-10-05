<?php
/* --------------------------------------------------------------------------------------------------------
  Objective: Establecer las propiedades del documento a generar y los metodos de descargar Excel desde el na-
  			vegador o guardarlo en un directorio.
  Author : Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
  Created: 13-Agt-2018
  Collaborators : Israel Faustino Cruz  y Japhet Mata Arellanes
  Permits: Esta pantalla no pregunta por permisos de escritura.
  -------------------------------------------------------------------------------------------------------- */
require_once dirname(__FILE__) . '/../../lib/bootstrap_spreadsheet.php';

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Phpspreadsheet
{
	public $spreadsheet=null;
	function __construct() {		
		$this->spreadsheet = new Spreadsheet();
		$this->set_PropertiesDocument();
	}
	public function set_PropertiesDocument(){
		$this->spreadsheet->getProperties()->setCreator('Maarten Balliauw')
	    ->setLastModifiedBy('Maarten Balliauw')
	    ->setTitle('Office 2007 XLSX Test Document')
	    ->setSubject('Office 2007 XLSX Test Document')
	    ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
	    ->setKeywords('office 2007 openxml php')
	    ->setCategory('Test result file');
	}
	public function output_web_browser($nombre_archivo="Predeterminado.xlsx",$extencion="Xlsx"){
		// Redirect output to a client’s web browser (Xlsx)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$nombre_archivo.'"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = IOFactory::createWriter($this->spreadsheet, $extencion);
		$writer->save('php://output');
		exit;
	}
	public function output_servidor($nombre_archivo="Predeterminado.xlsx",$extencion="Xlsx"){
		$writer = IOFactory::createWriter($this->spreadsheet, $extencion);
		$writer->save($nombre_archivo);		
	}
}