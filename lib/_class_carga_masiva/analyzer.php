<?php

/**
 * @filesource : analyzer.php
 * @author : @rvargas
 * @copyright (c) 2018, rvargas
 * Commentary:
---------------------------------------------------
------------ NOMENCLATURE VALIDATION --------------
required, data type, length, min length, max length
date format : YYYY-MM-DD ó YYYY/MM/DD
---------------------------------------------------
---------------------------------------------------
 **/
require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();

ini_set('max_execution_time', 600); //300 seconds = 5 minutes
ini_set('post_max_size', '54000M');
ini_set('memory_limit', '54000M');
ini_set('upload_max_filesize', '54000M');

date_default_timezone_set('America/Mexico_City');
require_once dirname(__FILE__) . '/utils/pattern_validate.php';
require_once dirname(__FILE__) . '/../bootstrap_spreadsheet.php';
require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/FicheroSefip.php';

class AnalyzerData extends WebResponse
{
    public $ur;
    public $gpo_ur;
    public $ur_id;
    public $file_xls;
    public $type_carga;
    public $dirFailed;

    public function __construct()
    {
        $data = [];
        $data['success'] = '';
        $data['errores'] = '';

        parent::__construct();
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->gpo_ur = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        $this->ur_id = $_SESSION[_S]['usr_id'];
        $data['tipo'] = $_POST['type_carga'] ?? '';
        $data['name'] = $_FILES['file-1']['name'] ?? '';

        if (!empty($_FILES['file-1']) && isset($_POST['type_carga']) && isset($this->gpo_ur)) {
            $this->file_xls = $_FILES['file-1'];
            $this->type_carga = $_POST['type_carga'];
            $this->upload();
        } else {
            $data['errores'] = 'Seleccione un archivo';
            echo json_encode($data);
        }
    }

    public function upload()
    {
        $file_array = explode('.', $this->file_xls['name']);
        $extension = end($file_array);
        $module = $this->type_carga;
        $hoy = getDate();

        switch ($module) {
            case 'CLC':
                $SheetByName = 'AFECTACION';
                $directory = 'afectaciones';
                $tipo = 2;
                $modulo = 'scp';
                break;
            case 'AFECTACION':
                $SheetByName = 'AFECTACION';
                $directory = 'afectaciones';
                $tipo = 1;
                $modulo = 'scp';
                break;
            case 'ADECUACIONES':
                $SheetByName = 'Datos';
                $directory = 'adecuaciones';
                $tipo = 3;
                $modulo = 'scp';
                break;
            case 'POLIZA':
                $SheetByName = 'POLIZA';
                $directory = 'poliza';
                $tipo = 4;
                $modulo = 'scg';
                break;
            case 'CUENTAS':
                $SheetByName = 'CUENTAS';
                $directory = 'cuentascontable';
                $tipo = 5;
                $modulo = 'scg';
                break;
            case 'VINCULACIONSAP':
                $SheetByName = 'VINCULACIONSAP';
                $directory = 'sap';
                $tipo = 6;
                $modulo = 'scg';
                break;
            case 'MINISTRADOCLC':
                $SheetByName = 'MINISTRADOCLC';
                $directory = 'marcadoministradoclc';
                $tipo = 7;
                $_SESSION['tipo'] = $tipo;
                $modulo = 'tes';
                break;
            case 'MINISTRADODOC':
                $SheetByName = 'MINISTRADODOC';
                $directory = 'marcadoministradodoc';
                $tipo = 8;
                $_SESSION['tipo'] = $tipo;
                $modulo = 'tes';
                break;
        }

        $tem_carga = $this->gpo_ur . '-' . date('y') . date('m') . date('d') . '-' . $this->ur_id . '-' . str_pad($hoy['seconds'], 2, '0', STR_PAD_LEFT) . str_pad($hoy['minutes'], 2, '0', STR_PAD_LEFT) . str_pad($hoy['hours'], 2, '0', STR_PAD_LEFT);

        $conex = new AccesoDatos();
        $conex->conectar();
        $sql = '
            SET @usr_ip = INET_ATON("' . ($_SERVER['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR']) . '"), 
            @usr_id = ?, 
            @bit_observacion= ?;
        ';
        $pm = [$this->ur_id, 'iniciando carga masiva'];
        $conex->setSql($sql);
        $conex->consultar($pm);

        $sql = 'INSERT INTO ' . BD_CTRL . '.cat_CargaMasiva(gpo_id,ur_id,ecr_id,tcr_id,tem_carga,crm_hora,crm_fecha,usr_id ) 
            VALUES(?,?,?,?,?,TIME(NOW()),DATE(NOW()),?)';
        $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], 1, $tipo, $tem_carga, $this->ur_id];
        $conex->setSql($sql);
        $conex->consultar($parametros);
        $data['tem_last'] = $conex->getUltimoId();
        $conex->desconectar();

        if ($extension == 'xls' || $extension == 'xlsx') {

            $this->createDirectory($directory);
            $this->update_carga(2, 'TIPO DE ARCHIVO VÁLIDO', $tem_carga, $tipo);

            $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->file_xls['tmp_name']);
            $sheetNames = $objPHPExcel->getSheetNames();
            $nameSheetCorrectos = true;
            $validate = false;

            // GET JSON DATA DICTIONARY
            $str_datos = file_get_contents(dirname(__FILE__) . '/utils/dictionary.json');
            $this->update_carga(3, 'LEYENDO EL ARCHIVO', $tem_carga, $tipo);
            $dictionary = json_decode($str_datos, true);

            // GET SHEET_NAME ELEMENT BY MODULE
            $element_sheet = $dictionary[$module]['sheet_name'];
            $sheets_names = explode(',', $element_sheet);

            // GET ROW INIT NUMBER
            $element_row_start = $dictionary[$module]['row_start'];

            foreach ($sheets_names as $hoja) {
                if (!in_array(strtolower($hoja), array_map('strtolower', $sheetNames))) {
                    $data['errores'] = 'El archivo no contiene la hoja ' . $hoja;
                    $nameSheetCorrectos = false;
                    $this->update_carga(92, 'ERROR AL LEER EL ARCHIVO', $tem_carga, $tipo);
                }
            }

            if ($nameSheetCorrectos) {
                $afectaciones = $objPHPExcel->getSheetByName($SheetByName);
                $highestColumn = $dictionary[$module]['sheets_name_last'];
                $highestRow = $afectaciones->getHighestRow();
                $numberColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                if ($numberColumn != $dictionary[$module]['number_column']) {
                    $data['errores'] = 'El número de columnas no es la solicitada, deberían ser: ' . $dictionary[$module]['number_column'];
                    $this->update_carga(92, 'ERROR AL LEER EL ARCHIVO', $tem_carga, $tipo);
                } elseif ($highestRow < $element_row_start) {
                    $data['errores'] = 'El archivo no contiene ningún registro';
                    $this->update_carga(92, 'ERROR AL LEER EL ARCHIVO', $tem_carga, $tipo);
                } else {
                    $validate = true;
                }
            }

            if ($validate) {
                $this->update_carga(4, 'ARCHIVO PRECARGADO', $tem_carga, $tipo);
                $headerCell = $dictionary[$module]['sheet_column_header'];
                $cellHeader = explode(',', $headerCell);
                $columnCell = $dictionary[$module]['sheet_column'];
                $cellColumn = explode(',', $columnCell);

                $txt_write = '';
                $arrayJson = [];
                $arrayData = [];
                $limite = $numberColumn - 1;
                for ($fila = $element_row_start; $fila <= $highestRow; $fila++) {
                    if ($afectaciones->getCell('A' . $fila)->getValue() != '') {
                        for ($j = 0; $j < $numberColumn; $j++) {

                            $element_validate = $dictionary[$module]['validator'][0][$afectaciones->getCell($cellHeader[$j])->getValue()];
                            $type_data = explode(',', $element_validate);
                            $msg_type = $this->getTypeData($type_data[1]);

                            $indexColumn = $cellColumn[$j];
                            $isValid = validateColumn($element_validate, $afectaciones->getCell($cellColumn[$j] . $fila)->getValue());
                            if ($isValid) {
                                // (string) evita deprecations de PHP 8.1+ cuando la celda viene vacía (null)
                                $head_json = (string) $afectaciones->getCell($cellHeader[$j])->getValue();
                                $head_json = str_replace(' ', '_', $head_json);
                                $head_json = str_replace('ñ', 'ni', $head_json);
                                $valueCell = $afectaciones->getCell($cellColumn[$j] . $fila)->getValue();
                                if ($type_data[1] == 'date') {
                                    $valueCell = str_replace("'", '', (string) $valueCell);
                                }

                                if (!isset($arrayData[$head_json])) {
                                    $arrayData[$head_json] = '';  // Inicializas el valor si no existe
                                }

                                $arrayData[$head_json] .= $valueCell;
                                if ($j == $limite) {
                                    $arrayJson['Data'][] = $arrayData;
                                    $arrayData = null;
                                }
                            } else {
                                $txt_write .= 'La columna ' . $afectaciones->getCell($cellHeader[$j])->getValue() . ' en la fila ' . $fila . " tiene el valor '" . $afectaciones->getCell($cellColumn[$j] . $fila)->getValue() . "' debe ser solamente " . $msg_type . " ó la longitud permitida no es válida.  \r\n";
                            }
                        }
                    }
                } // end for

                if ($txt_write != '') {
                    $filename = $this->dirFailed . '/' . $tem_carga . '.txt';
                    file_put_contents($filename, $txt_write);
                    $data['txt'] = $tem_carga;
                    $data['errores'] = 'Hay errores en el archivo, favor de descargar el archivo para revisarlos';
                    $this->update_carga(93, 'ERROR DE DATOS', $tem_carga, $tipo);
                } elseif ($validate) {
                    $this->update_carga(6, 'DATOS DEL ARCHIVO REVISADO', $tem_carga, $tipo);

                    $nombre_archivo = $tem_carga . '.' . $extension;
                    $claveunica = $directory . '/' . date('m');
                    $claveunica2 = '';

                    $rutatem = dirname(__FILE__) . '/../../scp/tmpdoctos/';
                    $exists = is_dir($rutatem);
                    if (!$exists) {
                        mkdir($rutatem, 0777, true);
                    }
                    $name = $rutatem . $nombre_archivo;
                    if (!move_uploaded_file($this->file_xls['tmp_name'], $name)) {
                        $data['errores'] = 'Ocurrio un problema al guardar archivo temporal';
                    }

                    $up = new FicheroSefip();
                    $up->setTipos([$extension]);
                    $up->setModulo($modulo);
                    $up->setfile($nombre_archivo);
                    $up->setRuta(($claveunica !== '' ? $claveunica . '/Carga_Masiva/' : '') .
                        ($claveunica2 !== '' ? $claveunica2 . '/' : ''));
                    $up->setUrFichero($this->gpo_ur);
                    $up->setAnioFichero(APP_EJERCICIO);
                    $file = ['name' => $name, 'tmp_name' => $name, 'eslocal' => true];
                    $up->setFichero($file);

                    if ($up->upload()) {
                        $data['success'] = 'Se completó la validación de Carga Masiva';
                        $data['xls'] = $tem_carga . '.' . $extension;
                        if ($directory == 'adecuaciones') {
                            $data['json'] = json_encode($arrayJson);
                        }
                        $this->update_carga(7, 'CARGANDO LOS DATOS', $tem_carga, $tipo);
                    } else {
                        $data['errores'] = 'No se logró cargar el archivo';
                        $this->update_carga(92, 'NO SE LOGRO CARGAR EL ARCHIVO', $tem_carga, $tipo);
                    }
                } else {
                    $data['errores'] = 'Ocurrio un problema, intente de nuevo por favor';
                    $this->update_carga(93, 'ERROR DE DATOS', $tem_carga, $tipo);
                }
            } // end if validate

        } else {
            $data['errores'] = 'Tipo de archivo erróneo';
            $this->update_carga(91, 'TIPO DE ARCHIVO ERRÓNEO', $tem_carga, $tipo);
        }

        echo json_encode($data);
    }

    public function getTypeData($type_data)
    {
        $typeData = '';
        switch ($type_data) {
            case 'int':
                $typeData = 'númerico';
                break;
            case 'char':
                $typeData = 'letra';
                break;
            case 'string':
                $typeData = 'alfanumerico';
                break;
            case 'decimal':
                $typeData = 'decimal';
                break;
            case 'date':
                $typeData = 'formato fecha YYYY-MM-DD';
                break;
            case 'rfc':
                $typeData = 'formato RFC';
                break;
        }
        return  $typeData;
    }

    // Actualizacion de las cargas másivas
    public function update_carga($estado, $observacion, $tem_carga, $tipo)
    {
        $conex = new AccesoDatos();
        $conex->conectar();
        $sql = 'SET @usr_id = ? , @usr_ip = INET_ATON("127.0.0.1"), @bit_observacion= ?;';
        $pr = [$this->ur_id,  $observacion];
        $conex->setSql($sql);
        $conex->consultar($pr);
        $sql = '
            UPDATE cat_CargaMasiva 
            SET ecr_id = ?
            WHERE 
                gpo_id = ? AND ur_id = ?
            AND tcr_id = ?
            AND tem_Carga = ?
        ';
        $params = [$estado, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $tipo, $tem_carga];
        $conex->setSql($sql);
        $conex->consultar($params);
        $conex->desconectar();
    }

    public function createDirectory($directory)
    {
        $this->dirFailed = dirname(__FILE__) . '/../../archivos/failedCargaMasiva/' . $directory . '/' . date('m') . '/' . $this->gpo_ur;
        if (!file_exists($this->dirFailed)) {
            mkdir($this->dirFailed, 0777, true);
        }
    }
}

//Verifica si el usuario tiene los permisos
if (
    isset($_SESSION[_S]['prm']['S']['2']['1']['1']) || isset($_SESSION[_S]['prm']['S']['1']['1']['1'])
    || isset($_SESSION[_S]['prm']['S']['9']['1']['23']) || isset($_SESSION[_S]['prm']['S']['2']['1']['23'])
    || isset($_SESSION[_S]['prm']['C']['3']['2']['23'])
    || isset($_SESSION[_S]['prm']['C']['1']['4']['23'])
    || isset($_SESSION[_S]['prm']['C']['12']['10']['23'])
    || isset($_SESSION[_S]['prm']['T']['4']['6'])
) {
    new AnalyzerData();
} else {
    WebResponse::sinPermisos();
}
