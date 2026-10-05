<?php

session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);
set_time_limit(0);

//librerias
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../lib/scg/UrNombre.php';
require_once '../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/disciplinaFinanciera/lib_situacionFinanciera.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';


class Exp_SituacionFinancieraDetallado extends generaPhpspreadsheet
{
    public $longitud;
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public  $encabLin4;
    public  $encabLin5;
    public  $encabLin6;
    public  $encabLin7;
    public $datos;
    public $nombreColumnas;
    public $nombreArchivoReporte;
    public $corte;
    public $texto;
    public $texto2;
    public $mesHasta;
    public $ejercicioAnterior;
    public $gpoId;
    public $urId;
    public $ur;
    public $linea1;
    public $periodo;
    public function __construct()
    {
        parent::__construct();
        if (empty($_POST['msc_trimestre']) || !in_array($_POST['msc_trimestre'], array(1, 2, 3, 4))) {
            echo "<html><head><script>alert('No se ha seleccionado un trimestre válido.'); window.close();</script></head></html>";
            exit();
        }
        $ejercicioActual = $_SESSION[_S]['ejercicioActual'];

        $this->mesHasta = 0;
        switch ($_POST['msc_trimestre']) {
            case 1:
                $this->mesHasta = 3;
                $this->corte = "Al 31 de Marzo de " .$ejercicioActual;
                $this->periodo = "Enero - Marzo _".$ejercicioActual;
                break;
            case 2:
                $this->mesHasta = 6;
                $this->corte = "Al 30 de Junio de " .$ejercicioActual;
                $this->periodo = "Abril - Junio _".$ejercicioActual;                
                break;
            case 3:
                $this->mesHasta = 9;
                $this->corte = "Al 30 de Septiembre de " .$ejercicioActual;
                $this->periodo = "Julio - Septiembre _".$ejercicioActual;
                break;
            case 4:
                $this->mesHasta = 12;
                $this->corte = "Al 31 de Diciembre de " .$ejercicioActual;
                $this->periodo = "Octubre - Diciembre _".$ejercicioActual;
                break;
        }

        $this->gpoId = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->urId = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
        $enteId = null;

        $this->longitud = 80;

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        $ue = (isset($_POST['ue']) ? $_POST['ue'] : "");
        if($ue!=''){
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur  AND ue_id = :ue ;');
            $ejecutora->consultar(array(':gpo'=>$this->gpoId,':ur'=>$this->urId,':ue'=>$ue));
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();            
            $this->texto2 =  'UE: '.$ue_id['ue_id'].' - '.$ue_id['ue_nombre'].'';
        } 

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($_POST['tipoReporte']) {
                case 2: // Todas las URs de la carga de trabajo
                    $this->texto = "";
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $this->texto = utf8Decode(isset($_POST['nombrEspecial']) ? $_POST['nombrEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO");
                    break;
                default:
                    if( $ejercicioActual == "" ){
                        $this->texto = $this->ur->getClave()." - ".$this->ur->getNombre();
                    }else{
                        $this->texto =  $this->ur->getGpo_id().$this->ur->getUr_id()." - ".$this->ur->getNombre();
                    }
                    break;
            } 
        }else{
            if (mb_detect_encoding($this->ur->getNombre(), "auto")=="UTF-8"){
                $this->texto = "UR: ".$this->ur->getClave()." - ".$this->ur->getNombre();
            }else{
                $this->texto = "UR: ".$this->ur->getClave()." - ". $this->ur->getNombre();
            }
        }

        $tipoReporte = isset($_POST['tipoReporte']) ? $_POST['tipoReporte'] : 1;
        $checkMiles = empty($_POST['ckmiles']) ? '(Cifras en Pesos)' : '(Cifras en Miles de Pesos)';
        $this->ejercicioAnterior = $ejercicioActual - 1;

        $sqlMes = sprintf("SELECT msc_nombre, msc_dias FROM cat_MesContable WHERE msc_id = %d;", $this->mesHasta);
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes);
        $con2->consultar();
        $con2->desconectar();
        $rowMes = $con2->extraer();

        $this->datos = lib_situacionFinanciera($enteId, $_POST['msc_trimestre'],false,'','','','',array("tipoReporte" => $_POST['tipoReporte'], "ue" => $_POST['ue'], "grupos" => $_POST['grupos'], "excluir" => $_POST['excluir']));
   
        $linea1 = TruncateCadena(trim($this->texto),$this->longitud);
        $this->encabLin1 = isset($_POST['nombrEspecial']) ? $_POST['nombrEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO";
        $this->encabLin2 =  substr($linea1, 0, $this->longitud);
        if (strlen($linea1) > $this->longitud) {
            $this->encabLin3 = substr($linea1, $this->longitud, $this->longitud);
            $this->encabLin4 = 'Estado de Situación Financiera Detallado - LDF';
            $this->encabLin5 = 'Al ' . $rowMes['msc_dias'] . ' de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' de ' . $ejercicioActual. ' y al 31 de Diciembre de '. $this->ejercicioAnterior;
            if(!isset($_POST['ue']) && $_SESSION[_S]['nvj_id'] > 2){           
                $this->encabLin6 = $this->texto2;
            }else{
                $this->encabLin6 = "";
            } 
            $this->encabLin7 =  $checkMiles;
        }else{
            $this->encabLin3 =  'Estado de Situación Financiera Detallado - LDF';
            $this->encabLin4 =  $this->encabLin6 = 'Al ' . $rowMes['msc_dias'] . ' de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' de ' . $ejercicioActual. ' y al 31 de Diciembre de '. $this->ejercicioAnterior;
            if(!isset($_POST['ue']) && $_SESSION[_S]['nvj_id'] > 2){           
                $this->encabLin5 = $this->texto2;
            }else{
                $this->encabLin5 = "";
            } 
            $this->encabLin6 =  $checkMiles;
        }            
        if (strlen($linea1) > $this->longitud * 2) {
            $this->encabLin4 = substr($linea1, $this->longitud * 2, $this->longitud);      
            $this->encabLin5 = 'Estado de Situación Financiera Detallado - LDF';
            $this->encabLin6 = 'Al ' . $rowMes['msc_dias'] . ' de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' de ' . $ejercicioActual. ' y al 31 de Diciembre de '. $this->ejercicioAnterior;
            if(!isset($_POST['ue']) && $_SESSION[_S]['nvj_id'] > 2){           
                $this->encabLin7 = $this->texto2;
            }else{
                $this->encabLin7 = "";
            } 
            $this->encabLin8 =  $checkMiles;
        }
     
        $this->contenido();
    }

    public function as_number( $str_importe ) {
        return (float) str_replace( ",", "", $str_importe );
    }

    public function contenido()
    {
        $estado = array();
        foreach ($this->datos as $value) {
            $importeActualIzquierda = $value['importeActualIzquierda'] == '' || in_array($value['nombreIzquierda'], ['ACTIVO', 'Activo Circulante', 'Activo No Circulante']) ? '' : $value['importeActualIzquierda'];
            $importeAnteriorIzquierda = $value['importeAnteriorIzquierda'] == '' || in_array($value['nombreIzquierda'], ['ACTIVO', 'Activo Circulante', 'Activo No Circulante']) ? '' : $value['importeAnteriorIzquierda'];
            $importeActualDerecha = $value['importeActualDerecha'] == '' || in_array($value['nombreDerecha'], ['PASIVO', 'Pasivo Circulante', 'Pasivo No Circulante', utf8Decode('HACIENDA PÚBLICA/PATRIMONIO')]) ? '' : $value['importeActualDerecha'];
            $importeAnteriorDerecha = $value['importeAnteriorDerecha'] == '' || in_array($value['nombreDerecha'], ['PASIVO', 'Pasivo Circulante', 'Pasivo No Circulante', utf8Decode('HACIENDA PÚBLICA/PATRIMONIO')]) ? '' : $value['importeAnteriorDerecha'];

            array_push( $estado, array( "nombreIzquierda" =>  $value['nombreIzquierda']  , "importeActualIzquierda" => $importeActualIzquierda, "importeAnteriorIzquierda" => $importeAnteriorIzquierda, "nombreDerecha" =>  UConverter::transcode($value['nombreDerecha'], 'UTF8', 'ISO-8859-1'), "importeActualDerecha" => $importeActualDerecha, "importeAnteriorDerecha" => $importeAnteriorDerecha) );
        }

        $encabezado = array(
            'encabLin1' => $this->encabLin1,
            'encabLin2' => $this->encabLin2,
            'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4,
            'encabLin5' => $this->encabLin5,
            'encabLin6' => $this->encabLin6
        );

        $nombreColumnas = array(
            "CONCEPTO",
            $this->corte,
            "Al 31 de Diciembre de ".$this->ejercicioAnterior,
            "CONCEPTO",
            $this->corte,
            "Al 31 de Diciembre de ".$this->ejercicioAnterior
        );

        $this->nombreColumnas = array(
            "CONCEPTO",
            $this->corte,
            "Al 31 de Diciembre de ".$this->ejercicioAnterior,
            "CONCEPTO",
            $this->corte,
            "Al 31 de Diciembre de ".$this->ejercicioAnterior
        );

        $incluirColumnas = array(
            "nombreIzquierda",
            "importeActualIzquierda",
            "importeAnteriorIzquierda",
            "nombreDerecha",
            "importeActualDerecha",
            "importeAnteriorDerecha",
        );
        
        $tipoColumnas = ['cI','i','i','cI','i','i'];
        $anchoColumnas = [
            0 => 80,
            1 => 25,
            2 => 25,
            3 => 80,
            4 => 25,
            5 => 25
        ];

        $table = array( );
        foreach( $incluirColumnas as $key=>$val ){
            $table['nombre'][$val] = $nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $table['tipoTabla'] = "M";
        $table[''] = 'S';
        $table['color'] = 1;
        $table['encabezadocolor'] = "BFB7B6";
        $table['line1color'] = "FFFFFF";
        $table['line2color'] = "FFFFFF";
        $table['textoCompleto'] = true;

        $tablaimg['imagen'] = 1;
        $tablaimg[0]['nombre'] = "Logo";

        $tablaimg[0]['ruta'] = dirname(__FILE__) . '/../../../../img/'.LOGO_COLOR;

        $tablaimg[0]['colImg'] = "A1";
        $tablaimg[0]['alingXimg'] = "10";

        $tablaimg[0]['altura'] = "75";
        $tablaimg[0]['rotacion'] = "0";
        if (!is_file($tablaimg[0]['ruta'])) {
            $tablaimg['imagen'] = 0;
            unset($tablaimg[0]);
        }

        $ur = $this->gpoId.$this->urId;
        $tablaimg['imagen'] = 1;
        $tablaimg[1]['nombre'] = "Logo";

        $tablaimg[1]['ruta'] = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $ur . '.png';

        $tablaimg[1]['colImg'] = "E1";
        $tablaimg[1]['alingXimg'] = "10";

        $tablaimg[1]['altura'] = "95";
        $tablaimg[1]['rotacion'] = "0";
        if (!is_file($tablaimg[1]['ruta'])) {
            $tablaimg['imagen'] = 0;
            unset($tablaimg[1]);
        }

        $this->generaExcel($estado, $encabezado, $table, array('comprimir'=>false,'nombre' => "Edo_Situación_Financiera_Detallado_LDF-".$this->periodo, 'tablaimg' => $tablaimg),false,false);
    }

    public function generaExcel(
        &$estado,
        $encabezado,
        $table = array(),
        $otrosParametros = array(),
        $comprimir = false,
        $guardar = array()
    ) {
        $size_datos = sizeof($estado);
        $avance_fila = 9;
        $avance_columna = "A";
        for ($i = 1; $i <= $size_datos; $i++) {
            $avance_fila++;
        }
        foreach ($this->nombreColumnas as $value) {
            $avance_columna++;
        }
        $avance_columna--;

        $styleArray = [
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $this->spreadsheet->getActiveSheet()->setCellValue('C' . $avance_fila+2, "1/1")->getStyle('C'.$avance_fila+2)->applyFromArray($styleArray);
        parent::generaExcel($estado, $encabezado, $table, $otrosParametros);
    }
}
$exp = new Exp_SituacionFinancieraDetallado();