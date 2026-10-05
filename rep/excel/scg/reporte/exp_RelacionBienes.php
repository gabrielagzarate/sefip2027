<?php

use relacionBienes as GlobalRelacionBienes;

session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/consulta/relacionBienes.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/UrNombre.php';
class RelacionBienes extends generaPhpspreadsheet
{
    private $encabezadoLin1;
    private $encabezadoLin2;
    private $encabezadoLin3;
    private $encabezadoLin4;
    private $encabezadoLin5;
    private $encabezadoLin6;    
    private $encabezadoLin7;
    public $ur;
    public $cuentaInmuble;
    public $cuentaMueble;
    public $tipoBien;
    public $trimestre;
    public $trimConcepto;
    public $ckmiles;
    public $tipoReporte;
    public $ue;
    public $grupos;
    public $excluir;
    public $nombreEspecial;    
    public $bien;
    public $pesos;
    public $hasta;
    public $mesHasta;
    public $nombreColumnas;
    public $datos;
    public $avance;
    public $longitud = 90;
    public $linea2;
    public $linea3;
    public $linea4;
    public $linea5;
    public $linea6;

    public function __construct()
    {
        parent::__construct();
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->tipoBien = $_POST['radcta'];
        $this->trimestre = $_POST['trimestre'];
        $this->tipoReporte = $_POST['tipoReporte'];
        $this->ue = (isset($_POST['ue']) ? $_POST['ue'] : "");
        $this->grupos = (isset($_POST['grupos']) ? $_POST['grupos'] : '');
        $this->excluir = (isset($_POST['excluir']) ? $_POST['excluir'] : '');
        $this->nombreEspecial = (isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO");
        $this->ckmiles = (empty($_POST['ckmiles']))? 0 : $_POST['ckmiles'];
        $this->bien = $this->tipoBien == 'A' ? 'Muebles' : 'Inmuebles';

        switch ($this->trimestre) {
            case 1:
                $this->avance = "Primer Informe de Avance de Gestión Financiera Enero - Marzo " .APP_EJERCICIO;
                $this->trimConcepto = "Enero - Marzo_".APP_EJERCICIO;
                $this->hasta = 3;
                $this->mesHasta = "Marzo";
                break;
            case 2:
                $this->avance = "Segundo Informe de Avance de Gestión Financiera Enero - Junio " .APP_EJERCICIO;
                $this->trimConcepto = "Enero - Junio_".APP_EJERCICIO;
                $this->hasta = 6;
                $this->mesHasta = "Junio";
                break;
            case 3:
                $this->avance = "Tercer Informe de Avance de Gestión Financiera Enero - Septiembre  " .APP_EJERCICIO;
                $this->trimConcepto = "Enero - Septiembre_".APP_EJERCICIO;
                $this->hasta = 9;
                $this->mesHasta = "Septiembre";
                break;
            case 4:
                $this->avance = "Cuarto Informe de Avance de Gestión Financiera Enero - Diciembre " .APP_EJERCICIO;
                $this->trimConcepto = "Enero - Diciembre_".APP_EJERCICIO;
                $this->hasta = 12;
                $this->mesHasta = "Diciembre";
                break;
            case 5:
                $this->avance = "Cuenta Pública del Estado de Oaxaca del Ejercicio Fiscal  " .APP_EJERCICIO;
                $this->trimConcepto = "Cuenta Pública_".APP_EJERCICIO;
                $this->hasta = 12;
                $this->mesHasta = "Diciembre";
                break;
        }

        if ($this->ckmiles== 0) {
            $this->pesos = '(Cifras en Pesos)';
        } else {            
            $this->pesos = '(Cifras en Miles de Pesos)';
        }
    }
    public function header()
    {
        

        if($this->ue!=''){
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
                SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur  AND ue_id = :ue ;
            ');
            $ejecutora->consultar(array(':gpo'=>$this->ur->getGpo_id(),':ur'=>$this->ur->getUr_id(),':ue'=>$this->ue));
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            if($_SESSION[_S]['nvj_id'] > 2) { 
                $this->linea3 =  'UE: '.$ue_id['ue_id'].' - '.$ue_id['ue_nombre'].'';
            }else{
                $this->linea3 = "";
            }
        } 

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($this->tipoReporte) {
                case 2: // Todas las URs de la carga de trabajo
                    $this->encabezadoLin1 = "";
                    $this->linea2 = "Dirección de Contabilidad Gubernamental";
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $this->encabezadoLin1  = "Dirección de Contabilidad Gubernamental";
                    $this->linea2 = $this->nombreEspecial;
                    break;
                default:
                    $this->encabezadoLin1 = "Dirección de Contabilidad Gubernamental";
                    $this->linea2 = $this->ur->getGpo_id().$this->ur->getUr_id()." - ". UrNombre::titleCase($this->ur->getNombre());
                    break;
            }
        }else{           
           $this->encabezadoLin1 = "Gobierno del Estado de Oaxaca";
           $this->linea2 = "UR: " . $this->ur->getClave() . " - " . UrNombre::titleCase($this->ur->getNombre());
        }

        $dataMes = $this->GetDiasMes($this->hasta);
        $this->linea4 = 'Relación de Bienes ' . $this->bien . ' que Componen el Patrimonio';
        $this->linea5 = ' Al ' . $dataMes['msc_dias'] . ' de ' . $this->mesHasta . ' de ' . APP_EJERCICIO; 
        $this->linea6 = utf8Decode($this->pesos); 

    }
    public function GetDiasMes($mes)
    {
        $sqlMes2 = 'SELECT msc_nombre, msc_dias FROM cat_MesContable WHERE msc_id= ? ;';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar([$mes]);
        $con2->desconectar();
        $rowMes2 = $con2->extraer();
        return $rowMes2;
    }

    public function content()
    {

        $this->linea2 = TruncateCadena(trim($this->linea2 ),$this->longitud);
        $this->encabezadoLin2 =  substr($this->linea2, 0, $this->longitud);
        if (strlen($this->linea2) > $this->longitud) {
            $this->encabezadoLin3 = substr($this->linea2, $this->longitud, $this->longitud);
            $this->encabezadoLin4 = $this->linea4;
            $this->encabezadoLin5 = $this->linea5;
            if(!isset($_POST['ue']) && $_SESSION[_S]['nvj_id'] > 2){           
                $this->encabezadoLin6 = $this->linea5;
            }else{
                $this->encabezadoLin6 = "";
            }
            $this->encabezadoLin7 = $this->linea6;
        }else{
            $this->encabezadoLin3 = $this->linea3;
            $this->encabezadoLin4 =  $this->linea4;
            if(!isset($_POST['ue']) && $_SESSION[_S]['nvj_id'] > 2){           
                $this->encabezadoLin5 = $this->linea5;
            }else{
                $this->encabezadoLin5 = "";
            }
            $this->encabezadoLin6 = $this->linea6;
        }            

        if (strlen($this->linea2) > $this->longitud * 2) {
            $this->encabezadoLin3 = substr($this->linea2, $this->longitud * 2, $this->longitud);      
            $this->encabezadoLin4 = $this->linea3;
            $this->encabezadoLin5 = $this->linea4;
            if(!isset($_POST['ue']) && $_SESSION[_S]['nvj_id'] > 2){           
                $this->encabezadoLin6 = $this->linea5;
            }else{
                $this->encabezadoLin6 = "";
            }      
            $this->encabezadoLin7 = $this->linea6;
        }

        $encabezado = array(
            'encabLin1' => $this->encabezadoLin1,
            'encabLin2' => $this->encabezadoLin2,
            'encabLin3' => $this->encabezadoLin3,
            'encabLin4' => $this->encabezadoLin4,
            'encabLin5' => $this->encabezadoLin5,
            'encabLin6' => $this->encabezadoLin6,
            'encabLin7' => $this->encabezadoLin7

        );

        $tabla = array();
        $nombreColumnas = array(
            "CÓDIGO",
            "DESCRIPCIÓN DEL BIEN",
            "IMPORTE"
        );

        $this->nombreColumnas = array(
            "CÓDIGO",
            "DESCRIPCIÓN DEL BIEN",
            "IMPORTE"
        );

        $incluirColumnas = array(
            "cuenta",
            "nombre",
            "saldofinal_debe"
        );
        $tipoColumnas = ['c','c','n'];
        $AnchoColumnas = [
            0 => 20,
            1 => 80,
            2 => 20
        ];

        foreach ($incluirColumnas as $contador => $valor) {
            $tabla['nombre'][$valor] = $nombreColumnas[$contador];
            $tabla['campo'][$valor] = $contador;
            isset($tipoColumnas[$contador]) ? $tabla['tipo'][$valor] = $tipoColumnas[$contador] : '';
            $tabla['tipoTabla'] = "A";
            $tabla['ancho'][$valor] = isset($AnchoColumnas[$contador]) ? $AnchoColumnas[$contador] : 20;
            $tabla['color'] = 1;
            $tabla['encabezado'] = "S";
            $tabla['encabezadocolor'] = "BFB7B6";
            $tabla['line1color'] = "FFFFFF";
            $tabla['line2color'] = "FFFFFF";
        }
        
        $tablaimg['imagen'] = 1;
        $tablaimg[0]['nombre'] = "Logo";

        $tablaimg[0]['ruta'] = dirname(__FILE__) . '/../../../../img/escudocolor_derecha.png';

        $tablaimg[0]['colImg'] = "C1";
        $tablaimg[0]['alingXimg'] = "10";

        $tablaimg[0]['altura'] = "75";
        $tablaimg[0]['rotacion'] = "0";
        if (!is_file($tablaimg[0]['ruta'])) {
            $tablaimg['imagen'] = 0;
            unset($tablaimg[0]);
        }

        $this->datos = relacionMuebleInmueble::GetBalanza($this->ur->getGpo_id(), $this->ur->getUr_id(), $this->tipoBien, $this->trimestre, $parametrosExtra=array("tipoReporte" => $this->tipoReporte, "ue" => $this->ue, "grupos" => $this->grupos, "excluir" => $this->excluir, "ckmiles" => $this->ckmiles));
        $otrosParametros = array( 'comprimir'=>false, 'nombre'=> "Reporte de Bienes ". mb_strtolower($this->bien)."_".$this->trimConcepto , 'tablaimg' => $tablaimg );
        $this->generaExcel($this->datos, $encabezado, $tabla, $otrosParametros);        
    }
    
    public function generaExcel(
        &$dato,
        $encabezado,
        $table = array(),
        $otrosParametros = array(),
        $comprimir = false,
        $guardar = array()
    ) {
        $size_datos = sizeof($this->datos);
        $avance_fila = 10;
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
            
        $this->spreadsheet->getActiveSheet()->setCellValue('B' . $avance_fila+2, "1/1")->getStyle('B'.$avance_fila+2)->applyFromArray($styleArray);
        $this->spreadsheet->getActiveSheet()->setCellValue('B' . $avance_fila+3, '"'.$this->avance.'"')->getStyle('B'.$avance_fila+3)->applyFromArray($styleArray);
       
        parent::generaExcel($this->datos, $encabezado, $table, $otrosParametros);
    }
}
$val = new RelacionBienes();
$val->header();
$val->content();
