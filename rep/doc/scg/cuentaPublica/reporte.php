<?php

session_start();

DEFINE('MENU_CUENTA_PUBLICA', 11);

define('ACTIVO1', 1);
define('PASIVO2', 2);
define('HACIENDA', 3);
define('NIVEL', 0);
define('CUENTA', 1);
define('IMPORTE', 2);
define('SALDO', 3);

define('Ingresos', 4);
define('Gastos', 5);

DEFINE('TAMANO_FUENTE_MINUSCULAS', 5.3);
DEFINE('TAMANO_FUENTE_MAYUSCULAS', 6.3);

//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/conciliacion/consolidacion.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../lib/Math.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/UrNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

// Se incluyen los PHP necesarios en función de lo que se imprime
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/compararCadenas.php';

class reportetabla extends PDF
{
    public $firmante1;
    public $firmante2;
    public $firmante3;
    public $cargo1;
    public $cargo2;
    public $cargo3;
    public $leyenda;
    public $ceros;
    public $incCta;
    public $intLin;
    public $numUe;
    public $tipoReporte;
    public $mesDesde;
    public $mesHasta;
    public $consolidado;
    public $genero;
    public $chkEnMiles = 0;
    public $grupos;
    public $excluir;
    public $no;
    public $ur;
    public $ue;
    public $ue_id;
    public $nivel;
    public $men_id;
    public $opc_id;
    public $ejercicio;
    public $gpo_id;
    public $ur_id;
    public $linea7;
    public $sis_id;
    public $ancho;
    public $imagen;
    public $logo;
    public $texto;
    private $longitud = 100;
    public $longitud2 = 75;
    public $ultBorde = 0;
    public $encabezadoTabla = true;
    public $widths = '';
    public $nombreReporte = 'doc';

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        $this->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
        $this->AddFont('Montserrat', '', 'Montserrat-Regular.php');

        if (isset($_SESSION[_S]['clasAdm-ue'])) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
            $this->ue->select();
        } elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id(trim($_POST['ue']));
            $this->ue->select();
        } else {
            $this->ue = false;
        }

        $this->ue_id = ($this->ue ? $this->ue->getUe_id() : false);

        $this->sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
        $this->men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
        $this->opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

        // -----------------------------------------------------------------------------------------
        // Paso 1: Aquí preparamos las opciones globales y variables que usaremos en la sección CONTENT()
        // -----------------------------------------------------------------------------------------
        // Variables comunes
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;

        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->ceros = isset($_POST['ceros']) ? $_POST['ceros'] : '';
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $this->intLin = isset($_POST['intLin']) ? $_POST['intLin'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';

        // En el ticket #291730, definen que SIEMPRE se incluirán todas las cuentas
        // incluyendo aquellas cuyo saldo es cero
        $this->ceros = 1;

        $_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte'] : 1);
        $this->tipoReporte = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte'] : 1);

        switch ($this->men_id) {
            //menu Cuenta Pública
            case MENU_CUENTA_PUBLICA:
                switch ($this->opc_id) {
                    case 1: // Estado de Situación Financiera
                    case 3: // Estado de Variación en la Hacienda Pública
                    case 4: // Estado de Cambios en la Situación Financiera
                        $this->mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde'] : -1; //Informacion de acuerdo al mes
                        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1; //Informacion de acuerdo al mes
                        $this->ejercicio = '';
                        if ($this->ejercicio == '') {
                            $this->ur = new UnidadResponsable();
                            $this->ur->setGpo_id($this->gpo_id);
                            $this->ur->setUr_id($this->ur_id);
                            $this->ur->select();
                        }
                        break;

                    case 2: // Estado de Actividades
                        $this->mesDesde = isset($_POST['comboMes']) ? $_POST['comboMes'] : -1; //Informacion de acuerdo al mes
                        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1; //Informacion de acuerdo al mes
                        $this->consolidado = isset($_POST['consolidado']) ? $_POST['consolidado'] : '';
                        break;

                    case 6: // Analítico
                        $this->mesDesde = isset($_POST['comboMes']) ? $_POST['comboMes'] : -1; //Informacion de acuerdo al mes
                        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1; //Informacion de acuerdo al mes

                        $this->ejercicio = '';
                        if ($this->ejercicio == '') {
                            $this->ur = new UnidadResponsable();
                            $this->ur->setGpo_id($this->gpo_id);
                            $this->ur->setUr_id($this->ur_id);
                            $this->ur->select();
                        }
                        $this->genero = isset($_POST['comboGenero']) ? ($_POST['comboGenero'] != '' ? $_POST['comboGenero'] : 9) : 9;
                        $this->chkEnMiles = isset($_POST['chkEnMiles']) ? 1000 : 1;
                        $this->grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
                        $this->excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                        break;

                    case 7: // Deuda Pública
                        $this->mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde'] : -1; //Informacion de acuerdo al mes
                        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1; //Informacion de acuerdo al mes

                        $this->ejercicio = '';
                        if ($this->ejercicio == '') {
                            $this->ur = new UnidadResponsable();
                            $this->ur->setGpo_id($this->gpo_id);
                            $this->ur->setUr_id($this->ur_id);
                            $this->ur->select();
                        }

                        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
                        $this->no = isset($_POST['no']) ? $_POST['no'] : '';
                        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }

        // Si no viene un número en el interlineado, ponemos el default
        if (!is_numeric($this->intLin)) {
            $this->intLin = 2.5;
        }
    }

    // Encabezado del documento
    public function Header()
    {
        $ix = 0;
        $iy = 0;

        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 60;
                $this->ancho = 175;
                $this->imagen = 195;
                $this->logo = 122;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 239;
                $this->imagen = 259;
                $this->logo = 186;
                break;
        }

        // Analítico de Deuda
        if ($this->opc_id == 7) {
            if ($this->no == 1) {
                $this->SetFont('univiapro', 'B', 50);
                $this->SetTextColor(200, 200, 200);
                $this->RotatedText($ix + 30, $iy + 160, utf8Decode('NO APLICA'), 45);
                $this->RotatedText($ix + 150, $iy + 160, utf8Decode('NO APLICA'), 45);
                $this->RotatedText($ix, $iy + 80, utf8Decode('NO APLICA'), 45);
                $this->RotatedText($ix + 150, $iy + 80, utf8Decode('NO APLICA'), 45);
            }
        }

        // -----------------------------------------------------------------------------------------
        // Paso 2: Configuramos el texto del encabezado
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
            // NOTA: Cambios en los encabezados de todos los reportes solicitado en el Ticket #200644
            case MENU_CUENTA_PUBLICA:
                switch ($this->opc_id) {
                    case 1: // Estado de Situación Financiera
                        $this->nombreReporte = 'Edo_SitFin';
                        $linea1 = utf8Decode('Gobierno del Estado de Oaxaca');

                        if ($_SESSION[_S]['nvj_id'] > 2) {

                            switch ($_POST['tipoReporte']) {
                                case 2: // Todas las URs de la carga de trabajo
                                    $linea2 = utf8Decode('Dirección de Contabilidad Gubernamental');
                                    break;
                                case 3: // Determinar qué incluir y qué excluir
                                    $linea2 = utf8Decode(isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO');
                                    break;
                                default:
                                    if ($this->ejercicio == '') {
                                        $linea2 = utf8Decode($this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                    } else {
                                        $linea2 = utf8Decode($this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                    }
                                    break;
                            }
                        } else {
                            if ($this->ejercicio == '') {
                                $linea2 = 'UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre());
                                if (mb_detect_encoding($linea2, 'UTF-8') != 'UTF-8') {
                                    $linea2 = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                } else {
                                    if (preg_replace('/[Ü]/u', 'U', ($linea2)) != '') {
                                        $linea2 = utf8Decode($linea2);
                                    }
                                }
                            } else {
                                $linea2 = 'UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']);
                                if (mb_detect_encoding($linea2, 'UTF-8') != 'UTF-8') {
                                    $linea2 = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                } else {
                                    if (preg_replace('/[Ü]/u', 'U', ($linea2)) != '') {
                                        $linea2 = utf8Decode($linea2);
                                    }
                                }
                            }
                        }

                        $linea3 = '';
                        if ($this->ue != '' && $this->numUe > 1) {
                            $ejecutora = new AccesoDatos();
                            $ejecutora->conectar();
                            $ejecutora->setSql('
                                SELECT ue_id,ue_nombre
                                FROM cat_UnidadEjecutora
                                WHERE gpo_id = ?
                                    AND ur_id = ?
                                    AND ue_id = ? ;
                            ');
                            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
                            $ejecutora->desconectar();
                            $ue_id = $ejecutora->extraer();

                            $linea3 = 'UE: ' . $ue_id['ue_id'] . ' - ' . utf8Decode($ue_id['ue_nombre']) . '';
                        }

                        $linea4 = utf8Decode('Estado de Situación Financiera');

                        $mesId = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';

                        $sqlMes = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql($sqlMes);
                        $con->consultar([$mesId]);
                        $con->desconectar();
                        $rowMes = $con->extraer();

                        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

                        $sqlMes2 = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con2 = new AccesoDatos();
                        $con2->conectar();
                        $con2->setSql($sqlMes2);
                        $con2->consultar([$mesHasta]);
                        $con2->desconectar();
                        $rowMes2 = $con2->extraer();

                        if ($this->mesHasta == 13) {
                            $linea5 = utf8Decode('Al mes de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                        } elseif ($this->mesHasta == 0) {
                            $linea5 = utf8Decode('Al mes de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                        } else {
                            $linea5 = utf8Decode('Al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                        }

                        if (isset($_POST['ckmiles'])) {
                            $linea6 = '(Cifras en Miles de Pesos)';
                        } else {
                            $linea6 = '(Cifras en Pesos)';
                        }

                        break;
                    case 2: // Estado de Actividades
                        $this->nombreReporte = 'Edo_Actividades';
                        $linea2 = utf8Decode('Gobierno del Estado de Oaxaca');

                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            switch ($_POST['tipoReporte']) {
                                case 2: // Todas las URs de la carga de trabajo
                                    $texto = utf8Encode('Dirección de Contabilidad Gubernamental');
                                    break;
                                case 3: // Determinar qué incluir y qué excluir
                                    $texto = utf8Decode(isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO');
                                    break;
                                default:
                                    if ($this->ejercicio == '') {
                                        $texto = utf8Decode($this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                    } else {
                                        $texto = utf8Decode($this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                    }
                                    break;
                            }
                        } else {
                            if (isset($this->ejercicio) == '') {
                                $texto = ('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                }
                            } else {
                                $texto = ('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                }
                            }
                        }

                        $texto2 = '';
                        if ($this->ue != '' && $this->numUe > 1) {
                            $ejecutora = new AccesoDatos();
                            $ejecutora->conectar();
                            $ejecutora->setSql('
                                SELECT ue_id,ue_nombre
                                FROM cat_UnidadEjecutora
                                WHERE gpo_id = ?
                                    AND ur_id = ?
                                    AND ue_id = ? ;
                            ');
                            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
                            $ejecutora->desconectar();
                            $ue_id = $ejecutora->extraer();
                            if (mb_detect_encoding($ue_id['ue_nombre'], 'auto') == 'UTF-8') {
                                $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'];
                            } else {
                                $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . utf8Decode($ue_id['ue_nombre']);
                            }
                        }

                        // No entiendo la lógica de ésta sección... de momento se dejará tal y como está el código
                        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea3 = $texto;
                        }

                        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea4 = utf8Decode($texto2);
                        }

                        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
                            $linea3 = utf8Decode($texto);
                            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                                $linea4 = utf8Decode($texto2);
                            }
                        }

                        $linea5 = ($this->consolidado) ? utf8Decode('Estado de Actividades Consolidado') : utf8Decode('Estado de Actividades');

                        $mesDesde = (isset($_POST['comboMes']) && $_POST['comboMes'] != '') ? $_POST['comboMes'] : '';

                        $sqlMes = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql($sqlMes);
                        $con->consultar([$mesDesde]);
                        $con->desconectar();
                        $rowMes = $con->extraer();

                        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

                        $sqlMes2 = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con2 = new AccesoDatos();
                        $con2->conectar();
                        $con2->setSql($sqlMes2);
                        $con2->consultar([$mesHasta]);
                        $con2->desconectar();
                        $rowMes2 = $con2->extraer();

                        // Marcos: 15/feb/2018
                        // C92 es sistemaMenuOpcion... donde es Estados Financieros->Estado de Actividades
                        //     para diferenciarlo del reporte que sale del menú de Cuenta Pública
                        // Se cambiará el texto cuando sea el mes de cierre
                        // ticket #598470

                        if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
                            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                // Marcos: 15/feb/2018 adicionamos esta condicion
                            } elseif ($this->mesDesde == 13 and $this->mesHasta == 13) {
                                $linea6 = utf8Decode('al cierre del ejercicio ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } elseif ($this->mesHasta == $this->mesDesde) {
                                $linea6 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } else {
                                if ($this->mesDesde == 0) {
                                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                } elseif ($this->mesHasta == 13) {
                                    $linea6 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al cierre del ejercicio ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                } else {
                                    $linea6 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                }
                            }
                        } else {
                            if ($this->mesDesde == 0 and $this->mesHasta == 13) {
                                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } elseif (($this->mesDesde != 0 and $this->mesDesde != 13) and $this->mesHasta == 13) {
                                $linea6 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } elseif ($this->mesDesde == 0 and $this->mesHasta == 0) {
                                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } elseif ($this->mesHasta == $this->mesDesde) {
                                if ($this->mesDesde == 13 and $this->mesHasta == 13) {
                                    $linea6 = utf8Decode('Al ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                                } else {
                                    $linea6 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                }
                            } else {
                                if ($this->mesDesde == 0) {
                                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                                } else {
                                    $linea6 = utf8Decode('Del 01 de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' al ' . $rowMes2['msc_dias'] . ' de ' . ucwords(strtolower($rowMes2['msc_nombre'])) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                }
                            }
                        }

                        if (isset($_POST['MilPes'])) {
                            $linea7 = '(Cifras en Miles de Pesos)';
                        } else {
                            $linea7 = '(Cifras en Pesos)';
                        }

                        break;
                    case 3: // Estado de Variación en la Hacienda Pública
                        $linea1 = utf8Decode('Gobierno del Estado de Oaxaca');

                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            switch ($_POST['tipoReporte']) {
                                case 2: // Todas las URs de la carga de trabajo
                                    $texto = 'Dirección de Contabilidad Gubernamental';
                                    break;
                                case 3: // Determinar qué incluir y qué excluir
                                    $texto = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO';
                                    break;
                                default:
                                    if ($this->ejercicio == '') {
                                        $texto = $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre());
                                    } else {
                                        $texto = $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']);
                                    }
                                    break;
                            }
                        } else {
                            if ($this->ejercicio == '') {
                                $texto = 'UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre());
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                }
                            } else {
                                $texto = 'UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']);
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                }
                            }
                        }

                        $texto2 = '';
                        if ($this->ue != '' && $this->numUe > 1) {
                            $ejecutora = new AccesoDatos();
                            $ejecutora->conectar();
                            $ejecutora->setSql('
                                SELECT ue_id,ue_nombre
                                FROM cat_UnidadEjecutora
                                WHERE gpo_id = ?
                                    AND ur_id = ?
                                    AND ue_id = ? ;
                            ');
                            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
                            $ejecutora->desconectar();
                            $ue_id = $ejecutora->extraer();
                            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'];
                        }

                        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea2 = utf8Decode($texto);
                        }

                        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea3 = utf8Decode($texto2);
                        }

                        $linea4 = utf8Decode('Estado de Variación en la Hacienda Pública');

                        $mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';

                        $sqlMes = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql($sqlMes);
                        $con->consultar([$mesDesde]);
                        $con->desconectar();
                        $rowMes = $con->extraer();

                        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

                        $sqlMes2 = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con2 = new AccesoDatos();
                        $con2->conectar();
                        $con2->setSql($sqlMes2);
                        $con2->consultar([$mesHasta]);
                        $con2->desconectar();
                        $rowMes2 = $con2->extraer();

                        if ($_SESSION[_S]['reportes'] == 'C93') {
                            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                                $linea5 = utf8Decode('Del mes de ' . $rowMes['msc_nombre'] . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } elseif ($this->mesHasta == $this->mesDesde) {
                                $linea5 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . $rowMes['msc_nombre'] . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } else {
                                if ($this->mesDesde == 0) {
                                    $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                } else {
                                    $linea5 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                }
                            }
                        } else {
                            if ($this->mesDesde == 0 and $this->mesHasta == 13) {
                                $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } elseif (($this->mesDesde != 0 and $this->mesDesde != 13) and $this->mesHasta == 13) {
                                $linea5 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } elseif ($this->mesDesde == 0 and $this->mesHasta == 0) {
                                $linea5 = utf8Decode('Del mes de ' . $rowMes['msc_nombre'] . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } elseif ($this->mesHasta == $this->mesDesde) {
                                if ($this->mesDesde == 13 and $this->mesHasta == 13) {
                                    $linea5 = utf8Decode('Al ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                                } else {
                                    $linea5 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . $rowMes['msc_nombre'] . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                                }
                            } else {
                                if ($this->mesDesde == 0) {
                                    $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                                } else {
                                    $linea5 = utf8Decode('Del 01 de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' al ' . $rowMes2['msc_dias'] . ' de ' . ucwords(strtolower($rowMes2['msc_nombre'])) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                                }
                            }
                        }

                        if (isset($_POST['MilPes'])) {
                            $linea6 = '(Cifras en Miles de Pesos)';
                        } else {
                            $linea6 = '(Cifras en Pesos)';
                        }

                        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
                            $linea2 = utf8Decode($texto);

                            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                                $linea3 = utf8Decode($texto2);
                            }
                        }

                        break;
                    case 4: // Cambios en la Situación Financiera

                        $linea2 = utf8Decode('Gobierno del Estado de Oaxaca');

                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            switch ($_POST['tipoReporte']) {
                                case 2: // Todas las URs de la carga de trabajo
                                    $texto = 'Dirección de Contabilidad Gubernamental';
                                    break;
                                case 3: // Determinar qué incluir y qué excluir
                                    $texto = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO';
                                    break;
                                default:
                                    if ($this->ejercicio == '') {
                                        $texto = $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre());
                                    } else {
                                        $texto = $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']);
                                    }
                                    break;
                            }
                        } else {
                            if ($this->ejercicio == '') {
                                $texto = ('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                }
                            } else {
                                $texto = ('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                }
                            }
                        }

                        $texto2 = '';
                        if ($this->ue != '' && $this->numUe > 1) {
                            $ejecutora = new AccesoDatos();
                            $ejecutora->conectar();
                            $ejecutora->setSql('
                                SELECT ue_id,ue_nombre
                                FROM cat_UnidadEjecutora
                                WHERE gpo_id = ?
                                    AND ur_id = ?
                                    AND ue_id = ? ;
                            ');
                            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
                            $ejecutora->desconectar();
                            $ue_id = $ejecutora->extraer();
                            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
                        }

                        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea3 = utf8Decode($texto);
                        }

                        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea4 = utf8Decode($texto2);
                        }

                        $linea5 = utf8Decode('Estado de Cambios en la Situación Financiera');

                        $mesId = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';

                        $sqlMes = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ?;';
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql($sqlMes);
                        $con->consultar([$mesId]);
                        $con->desconectar();
                        $rowMes = $con->extraer();

                        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

                        $sqlMes2 = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con2 = new AccesoDatos();
                        $con2->conectar();
                        $con2->setSql($sqlMes2);
                        $con2->consultar([$mesHasta]);
                        $con2->desconectar();
                        $rowMes2 = $con2->extraer();

                        if ($_SESSION[_S]['reportes'] == 'C94') {
                            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } elseif ($this->mesHasta == $this->mesDesde) {
                                $linea6 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } else {
                                if ($this->mesDesde == 0) {
                                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                } else {
                                    $linea6 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                }
                            }
                        } else {
                            if ($this->mesDesde == 0 and $this->mesHasta == 13) {
                                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } elseif (($this->mesDesde != 0 and $this->mesDesde != 13) and $this->mesHasta == 13) {
                                $linea6 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } elseif ($this->mesDesde == 0 and $this->mesHasta == 0) {
                                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } elseif ($this->mesHasta == $this->mesDesde) {
                                if ($this->mesHasta == 13 and $this->mesDesde == 13) {
                                    $linea6 = utf8Decode('Al ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                } else {
                                    $linea6 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                }
                            } else {
                                if ($this->mesDesde == 0) {
                                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                } else {
                                    $linea6 = utf8Decode('Del 01 de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                                }
                            }
                        }

                        if (isset($_POST['ckmiles'])) {
                            $linea7 = '(Cifras en Miles de Pesos)';
                        } else {
                            $linea7 = '(Cifras en Pesos)';
                        }

                        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
                            $linea3 = utf8Decode($texto);
                            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                                $linea4 = utf8Decode($texto2);
                            }
                        }
                        break;
                    case 6: // Analítico

                        $linea2 = utf8Decode('Gobierno del Estado de Oaxaca');

                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            switch ($this->tipoReporte) {
                                case 2: // Todas las URs de la carga de trabajo
                                    $texto = utf8Decode('Dirección de Contabilidad Gubernamental');
                                    break;
                                case 3: // Determinar qué incluir y qué excluir
                                    $this->SetXY($ix + 15, $iy + 6);
                                    $this->SetFont('univiapro', 'B', 7);
                                    $this->Cell(235, 3, utf8Decode('Dirección de Contabilidad Gubernamental'), 0, 0, 'C');
                                    $texto = utf8Decode(isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO');
                                    break;
                                default:
                                    if ($this->ejercicio == '') {
                                        $texto = $this->ur->getClave() . ' - ' . utf8Decode(UrNombre::titleCase($this->ur->getNombre()));
                                    } else {
                                        $texto = $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']);
                                    }
                                    break;
                            }
                        } else {
                            if ($this->ejercicio == '') {
                                $texto = ('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                }
                            } else {
                                $texto = ('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                }
                            }
                        }

                        $texto2 = '';
                        if ($this->ue != '' && $this->numUe > 1) {
                            $ejecutora = new AccesoDatos();
                            $ejecutora->conectar();
                            $ejecutora->setSql('
                                SELECT ue_id,ue_nombre
                                FROM cat_UnidadEjecutora
                                WHERE gpo_id = ?
                                    AND ur_id = ?
                                    AND ue_id = ? ;
                            ');
                            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
                            $ejecutora->desconectar();
                            $ue_id = $ejecutora->extraer();
                            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'];
                        }

                        switch ($this->genero) {
                            case 1:
                                $linea5 = utf8Decode('Estado Analítico del Activo');
                                break;
                            case 2:
                                $linea5 = utf8Decode('Estado Analítico del Pasivo');
                                break;
                            case 3:
                                $linea5 = utf8Decode('Estado Analítico de la Hacienda Pública/Patrimonio');
                                break;
                            case 4:
                                $linea5 = utf8Decode('Estado Analítico de Ingresos y Otros beneficios');
                                break;
                            case 5:
                                $linea5 = utf8Decode('Estado Analítico de Gastos y Otras Perdidas');
                                break;
                            case 6:
                                $linea5 = utf8Decode('Estado Analítico de Cuentas de Cierre Contable');
                                break;
                            case 7:
                                $linea5 = utf8Decode('Estado Analítico de Cuentas de Orden Contables');
                                break;
                            case 8:
                                $linea5 = utf8Decode('Estado Analítico de Cuentas de Orden Presupuestarias');
                                break;
                            case 9:
                                $linea5 = utf8Decode('Estado Analítico de Cuentas de Cierre Presupuestario');
                                break;
                        }

                        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea3 = $texto;
                        }

                        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea4 = utf8Decode($texto2);
                        }

                        $mesDesde = (isset($_POST['comboMes']) && $_POST['comboMes'] != '') ? $_POST['comboMes'] : '';

                        $sqlMes = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql($sqlMes);
                        $con->consultar([$mesDesde]);
                        $con->desconectar();
                        $rowMes = $con->extraer();

                        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

                        $sqlMes2 = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con2 = new AccesoDatos();
                        $con2->conectar();
                        $con2->setSql($sqlMes2);
                        $con2->consultar([$mesHasta]);
                        $con2->desconectar();
                        $rowMes2 = $con2->extraer();

                        if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                            $linea6 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . '');
                        } elseif ($this->mesHasta == $this->mesDesde) {
                            if ($this->mesHasta == 13 and $this->mesDesde == 13) {
                                $linea6 = utf8Decode('Al ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } else {
                                $linea6 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . '');
                            }
                        } elseif ($this->mesDesde == 0 and $this->mesHasta == 13) {
                            $linea6 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                        } elseif (($this->mesDesde != 0 and $this->mesDesde != 13) and $this->mesHasta == 13) {
                            $linea6 = utf8Decode('Del 01 de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                        } else {
                            if ($this->mesDesde == 0) {
                                $linea6 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . '');
                            } else {
                                $linea6 = utf8Decode('Del 01 de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . '');
                            }
                        }

                        if ($this->chkEnMiles == 1000) {
                            $linea7 = '(Cifras en Miles de Pesos)';
                        } else {
                            $linea7 = '(Cifras en Pesos)';
                        }

                        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
                            $linea3 = utf8Decode($texto);
                            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                                $linea4 = utf8Decode($texto2);
                            }
                        }

                        break;
                    case 7: // Deuda Pública
                        //Letra
                        $this->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
                        $this->AddFont('Montserrat', '', 'Montserrat-Regular.php');

                        $linea2 = utf8Decode('Gobierno del Estado de Oaxaca');

                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            switch ($_POST['tipoReporte']) {
                                case 2: // Todas las URs de la carga de trabajo
                                    $texto = utf8Decode('Dirección de Contabilidad Gubernamental');
                                    break;
                                case 3: // Determinar qué incluir y qué excluir
                                    $texto = utf8Decode('Dirección de Contabilidad Gubernamental');
                                    break;
                                default:
                                    if ($this->ejercicio == '') {
                                        $texto = $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre());
                                    } else {
                                        $texto = $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']);
                                    }
                                    break;
                            }
                        } else {
                            if ($this->ejercicio == '') {
                                $texto = 'UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre());
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . UrNombre::titleCase($this->ur->getNombre()));
                                }
                            } else {
                                $texto = 'UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']);
                                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                                    $texto = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::titleCase($this->ur['ur_nombre']));
                                }
                            }
                        }

                        $texto2 = '';
                        if ($this->ue != '' && $this->numUe > 1) {
                            $ejecutora = new AccesoDatos();
                            $ejecutora->conectar();
                            $ejecutora->setSql('
                                SELECT ue_id,ue_nombre
                                FROM cat_UnidadEjecutora
                                WHERE gpo_id = ?
                                    AND ur_id = ?
                                    AND ue_id = ? ;
                            ');
                            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
                            $ejecutora->desconectar();
                            $ue_id = $ejecutora->extraer();
                            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
                        }

                        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea3 = utf8Decode($texto);
                        }

                        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
                            $linea4 = utf8Decode($texto2);
                        }

                        $linea5 = utf8Decode('Estado Analítico de la Deuda y Otros Pasivos');

                        $mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';

                        $sqlMes = 'SELECT msc_nombre, msc_dias
                                    FROM cat_MesContable
                                    WHERE msc_id= ? ;';
                        $con = new AccesoDatos();
                        $con->conectar();
                        $con->setSql($sqlMes);
                        $con->consultar([$mesDesde]);
                        $con->desconectar();
                        $rowMes = $con->extraer();

                        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

                        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
                        $con2 = new AccesoDatos();
                        $con2->conectar();
                        $con2->setSql($sqlMes2);
                        $con2->consultar([$mesHasta]);
                        $con2->desconectar();
                        $rowMes2 = $con2->extraer();

                        if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                            $linea6 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                        } elseif ($this->mesHasta == $this->mesDesde) {
                            if ($this->mesHasta == 13 and $this->mesDesde == 13) {
                                $linea6 = utf8Decode('Al ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                            } else {
                                $linea6 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            }
                        } elseif ($this->mesDesde == 0 and $this->mesHasta == 13) {
                            $linea6 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                        } elseif (($this->mesDesde != 0 and $this->mesDesde != 13) and $this->mesHasta == 13) {
                            $linea6 = utf8Decode('Del 01 de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                        } else {
                            if ($this->mesDesde == 0) {
                                $linea6 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            } else {
                                $linea6 = utf8Decode('Del 01 de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                            }
                        }

                        if (isset($_POST['MilPes'])) {
                            $linea7 = '(Cifras en Miles de Pesos)';
                        } else {
                            $linea7 = '(Cifras en Pesos)';
                        }

                        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
                            $linea3 = utf8Decode($texto);

                            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                                $linea4 = utf8Decode($texto2);
                            }
                        }
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }

        // Dibujamos el logo y las líneas del encabezado
        switch ($this->men_id) {
            // NOTA: Cambios en los encabezados de todos los reportes solicitado en el Ticket #200644
            case MENU_CUENTA_PUBLICA:
                switch ($this->opc_id) {
                    case 6:
                        $this->SetMargins(15, 10, 15);
                        break;
                    default:
                        $this->SetMargins(20, 10, 20);
                        break;
                }
                break;
            default:
                break;
        }
        //$this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $nLineas = 0;

        // Dibujamos el logo
        $this->Image('../../../../img/escudocolor_derecha.png', $this->logo, $iy, 74, 22, 'png');

        // Dibujamos las líneas de encabezado
        $this->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);

        if (trim(isset($linea1)) != '') {
            if ($this->getOrientation() == 'P') {
                $this->SetX(10);
            } else {
                $this->SetX(40);
            }

            $this->Cell(195, 5, $linea1, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea2) != '') {
            if ($this->getOrientation() == 'P') {
                $this->SetX(10);
            } else {
                $this->SetX(40);
            }

            $this->Cell(195, 5, $linea2, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea3) != '') {
            if ($this->getOrientation() == 'P') {
                $this->SetX(10);
            } else {
                $this->SetX(40);
            }

            $this->Cell(195, 5, $linea3, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim(isset($linea4)) != '') {
            if ($this->getOrientation() == 'P') {
                $this->SetX(10);
            } else {
                $this->SetX(40);
            }

            $this->Cell(195, 5, $linea4, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea5) != '') {
            if ($this->getOrientation() == 'P') {
                $this->SetX(10);
            } else {
                $this->SetX(40);
            }

            $this->Cell(195, 5, $linea5, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea6) != '') {
            if ($this->getOrientation() == 'P') {
                $this->SetX(10);
            } else {
                $this->SetX(40);
            }

            $this->Cell(195, 5, $linea6, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim(isset($linea7)) != '') {
            if ($this->getOrientation() == 'P') {
                $this->SetX(10);
            } else {
                $this->SetX(40);
            }

            $this->Cell(195, 5, $linea7, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }

        // Dejamos un espacio para la siguiente sección
        if ($nLineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

        // Dejamos un espacio para la siguiente sección
        if ($nLineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

        $this->SetBorders(true);
        $this->SetRelleno(true);
        $this->SetFont('Montserrat-b', '', 8);
        //$this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $altoLinea = $this->setAltoLinea(5);
        $espacio = 0;
        $fill = false;

        // -----------------------------------------------------------------------------------------
        // Paso 3: Configuramos el nombre y ancho de cada columna del reporte
        // El ancho total para PDF en modo "portrait" es 175
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {

            case MENU_CUENTA_PUBLICA:
                switch ($this->opc_id) {
                    case 1: // Estado de Situación Financiera
                        if ($this->encabezadoTabla) {
                            $this->SetFont('Univiapro', 'B', 6);
                            $this->SetWidths([47, 20, 20, 47, 20, 20]);
                            $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C']);
                            $this->SetDrawColor(0, 0, 0);
                            $this->Row([
                                utf8Decode('Concepto'),
                                utf8Decode(UrNombre::titleCase($rowMes2['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4)),
                                utf8Decode('Diciembre ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)),
                                utf8Decode('Concepto'),
                                utf8Decode(UrNombre::titleCase($rowMes2['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4)),
                                utf8Decode('Diciembre ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)),
                            ]);
                        } else {
                            $this->SetFont('Montserrat-b', '', 6);
                            if (isset($_POST['checkCuentaOrden']) == 1) {
                                $this->SetWidths([47, 20, 20, 47, 20, 20]);
                                $this->Cell(101.5, $altoLinea, '' . 'CUENTAS DE ORDEN', 0, 0, 'L', $fill);
                            } elseif (isset($_POST['cerosOrden']) == 1) {
                                $this->SetWidths([47, 20, 20, 47, 20, 20]);
                                $this->Cell(101.5, $altoLinea, '' . 'CUENTAS DE ORDEN SIN IMPORTE', 0, 0, 'L', $fill);
                            }
                            $this->Ln();
                            $this->Ln();
                            $this->Ln();
                            $this->SetY($this->GetY() + 5);
                        }
                        break;
                    case 2: // Estado de Actividades
                        // NOTA: Cambio de formato solicitado en el Ticket #364716
                        $this->SetFont('univiapro', 'B', 6);
                        $this->SetWidths([115, 30, 30]);
                        $this->SetAligns(['C', 'C', 'C']);
                        $this->SetDrawColor(0, 0, 0);
                        $this->Row([
                            utf8Decode('Concepto'),
                            utf8Decode(UrNombre::titleCase($rowMes2['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4)),
                            utf8Decode('Diciembre ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)),
                        ]);

                        break;

                    case 3: // Estado de Variación en la Hacienda Pública
                        // Si quieren visualizar el número de cuenta...
                        if (isset($_POST['cc']) && $_POST['cc'] == 1) {
                            $this->Cell(20, 5, '', 'LRT', 0, 'C', true);
                            $this->Cell(50, 5, '', 'LRT', 0, 'C', true);
                            $this->Cell(96, 5, utf8Decode('Hacienda Pública / Patrimonio'), 'LRT', 0, 'C', true);
                            $this->Cell(40, 5, '', 'LRT', 0, 'C', true);
                            $this->Cell(32, 5, '', 'LRT', 1, 'C', true);

                            $this->setX(20);
                            $this->Cell(20, 5, 'Cuenta', 'LR', 0, 'C', true);
                            $this->Cell(50, 5, 'Concepto', 'LR', 0, 'C', true);
                            $this->Cell(32, 5, utf8Decode('Hacienda Pública/'), 'LRT', 0, 'C', true);
                            $this->Cell(64, 5, 'Generado', 1, 0, 'C', true);
                            $this->Cell(40, 5, 'Ajustes por Cambios de Valor', 'LR', 0, 'C', true);
                            $this->Cell(32, 5, 'Total', 'LR', 1, 'C', true);

                            $this->setX(20);
                            $this->Cell(20, 5, '', 'LRB', 0, 'C', true);
                            $this->Cell(50, 5, '', 'LRB', 0, 'C', true);
                            $this->Cell(32, 5, 'Patrimonio Contribuido', 'LR', 0, 'C', true);
                            $this->Cell(32, 5, 'Ejerc. Ant.', 1, 0, 'C', true);
                            $this->Cell(32, 5, 'Del Ejercicio', 1, 0, 'C', true);
                            $this->Cell(40, 5, '', 'LRB', 0, 'C', true);
                            $this->Cell(32, 5, '', 'LRB', 1, 'C', true);
                        } else {
                            $this->Cell(50, 5, '', 'LRT', 0, 'C', true);
                            $this->Cell(37, 5, '', 'LRT', 0, 'C', true);
                            $this->Cell(37, 5, utf8Decode('Hacienda Pública/'), 'LRT', 0, 'C', true);
                            $this->Cell(37, 5, utf8Decode('Hacienda Pública/'), 'LRT', 0, 'C', true);
                            $this->Cell(40, 5, 'Exceso o Insuficiencia en la', 'LRT', 0, 'C', true);
                            $this->Cell(37, 5, '', 'LRT', 1, 'C', true);

                            $this->setX(20);
                            $this->Cell(50, 5, 'Concepto', 'LR', 0, 'C', true);
                            $this->Cell(37, 5, utf8Decode('Hacienda Pública/'), 'LR', 0, 'C', true);
                            $this->Cell(37, 5, 'Patrimonio Generado de', 'LR', 0, 'C', true);
                            $this->Cell(37, 5, 'Patrimonio Generado', 'LR', 0, 'C', true);
                            $this->Cell(40, 5, utf8Decode('Actualización de la Hacienda'), 'LR', 0, 'C', true);
                            $this->Cell(37, 5, 'Total', 'LR', 1, 'C', true);

                            $this->setX(20);
                            $this->Cell(50, 5, '', 'LRB', 0, 'C', true);
                            $this->Cell(37, 5, 'Patrimonio Contribuido', 'LR', 0, 'C', true);
                            $this->Cell(37, 5, 'Ejercicios Anteriores', 'LRB', 0, 'C', true);
                            $this->Cell(37, 5, 'del Ejercicio', 'LRB', 0, 'C', true);
                            $this->Cell(40, 5, utf8Decode('Pública/Patrimonio'), 'LRB', 0, 'C', true);
                            $this->Cell(37, 5, '', 'LRB', 1, 'C', true);
                        }
                        $this->setRelleno('no');
                        $this->SetBorders('LR');
                        break;
                    case 4: // Cambios en la Situación Financiera
                        $this->SetBorders(1);
                        $this->SetWidths([110, 35, 35]);
                        $this->SetAligns(['C', 'C', 'C']);

                        $this->Row([
                            utf8Decode('Concepto'),
                            utf8Decode('Origen'),
                            utf8Decode('Aplicación'),
                        ]);
                        $this->setRelleno('no');
                        $this->SetBorders(0);

                        break;

                    case 6: // Analítico

                        $this->SetWidths([82, 22, 22, 22, 22, 22]);

                        $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C']);
                        $this->Row([utf8Decode('Descripción'),
                                utf8Decode('Saldo Inicial'),
                                utf8Decode('Cargos del Periodo'),
                                utf8Decode('Abono del Periodo'),
                                utf8Decode('Saldo Final'),
                                utf8Decode('Variación del Periodo'),
                            ]);
                        break;
                    case 7: // Deuda Pública
                        $this->SetFont('Montserrat-b', '', 7);
                        $this->SetWidths([70, 25, 25, 30, 30]);
                        $this->SetAligns(['C', 'C', 'C', 'C', 'C']);
                        $this->SetDrawColor(0, 0, 0);
                        $this->Row([utf8Decode('Denominación de las Deudas'),
                            utf8Decode('Moneda de Contratación'),
                            utf8Decode('Institución o País Acreedor'),
                            utf8Decode('Saldo Inicial del Periodo'),
                            utf8Decode('Saldo Final del Periodo'),
                        ]);

                        $this->setRelleno('no');
                        $this->SetBorders('LR');
                        break;
                }
                // no break
            default:
                break;
        }
        // $this->ln(1);
    }

    public function Footer($lastPage = true)
    {
        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1 != '' && $this->firmante2 != '')) {
            $this->SetY(-40);
            $this->Cell(190, 2, utf8Decode(''), 0, 0, 'C');
            $this->Ln(3);
        } else {
            $this->SetY(-35);
        }

        if ($lastPage) {

            $this->SetFont('univiapro', 'B', 7);

            $this->Ln(1);
            if ($this->men_id == MENU_CUENTA_PUBLICA && $this->getOrientation() == 'P') {
                $cargos = array_values(array_filter([$this->cargo1, $this->cargo2, $this->cargo3], 'strlen'));
                $nombres = array_values(array_filter([$this->firmante1, $this->firmante2, $this->firmante3], 'strlen'));
                $ancho = $this->GetPageWidth() - 20;
                $anchoCelda = $ancho / count($cargos);
                $this->SetY(- 30);
                $this->SetX(15);
                $this->SetFont('Montserrat', '', TAMANO_FUENTE_MAYUSCULAS);
                for ($i = 0; $i < count($cargos); $i++) {
                    $this->MultiAlignCell($anchoCelda, 3, utf8Decode($nombres[$i]), 0, 0, 'C');
                }
                if ($this->opc_id == 4) {
                    $this->SetY($this->GetY() - 10);
                } else {
                    $this->SetY($this->GetY() - 20);
                }

                $this->SetX(15);
                $this->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                for ($i = 0; $i < count($cargos); $i++) {
                    $this->MultiAlignCell($anchoCelda, 3, utf8Decode($cargos[$i]), 0, 0, 'C');
                }
            } else {
                $this->SetFont('Montserrat', '', TAMANO_FUENTE_MAYUSCULAS);
                $this->MultiAlignCell(78, 3, utf8Decode($this->cargo2), 0, 0, 'C');
                $this->MultiAlignCell(80, 3, utf8Decode($this->cargo3), 0, 1, 'C');
                $this->Ln(4);
                $this->MultiAlignCell(78, 3, utf8Decode($this->firmante2), 0, 0, 'C');
                $this->MultiAlignCell(80, 3, utf8Decode($this->firmante3), 0, 1, 'C');
            }
        }

        $this->SetY(-9);
        $this->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
        if ($this->men_id == MENU_CUENTA_PUBLICA && $this->getOrientation() == 'P') {
            $this->Cell(178, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        } else {
            $this->Cell(238, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    public function consultaCuentas($gpo_id, $ur_id, $ue, $mesDesde, $mesHasta, $ejercicio, $tipoReporte, $grupos, $excluir, $incCta)
    {
        if ($tipoReporte == '') {
            $tipoReporte = 1;
        }

        $ejercicio = (($ejercicio != '') ? 'ctrl' . $ejercicio . '.' : '');

        $otroWhere = '';
        if ($tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
            // Si limitaron por grupo o excluyeron alguna UR
            if ($grupos != '') {
                $otroWhere .= '  WHERE gpo_id IN ( ' . rtrim($grupos) . ' ) AND ';

                // Si excluyeron a alguna UR
                if ($excluir != '') {
                    $otroWhere .= ' ' . ($grupos != '' ? '' : ' WHERE ') . ' CONCAT( gpo_id, ur_id ) NOT IN ( ' . rtrim($excluir) . ' ) AND ';
                }

                // Si no usaron las campos especiales, es un where estandar
            }
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("
            SELECT cuenta, debe, haber, cgm_naturaleza FROM (
                SELECT IFNULL(cuenta, '2131') cuenta, ger_id, cgp_id, rub_id, cgcu_id, IFNULL(debe, 0) debe, IFNULL(haber, 0) haber, MIN(sc.nat_id) cgm_naturaleza
                FROM " . $ejercicio . "cat_CgSubcuenta sc
                LEFT JOIN (
                    SELECT '2131' cuenta, ger_id, cgp_id, rub_id, cgcu_id, SUM(pdx_debe) debe, SUM(pdx_haber) haber, MAX(nat_id) nat_id
                    FROM " . $ejercicio . 'cg_PolizaDetalleAuxiliar d
                    INNER JOIN ' . $ejercicio . 'cg_PolizaMaestro USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
                    ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? ' INNER JOIN ' . $ejercicio . 'cat_CargaTrabajo t USING(gpo_id,ur_id)
                    ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                    AND ')) . '
                    ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "(msc_id BETWEEN $mesDesde AND $mesHasta)")) . "
                    AND ger_id=2 AND cgp_id=1 AND rub_id=3  AND cgcu_id=1
                    AND epl_id NOT IN ('C','H')
                    " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . "
                    GROUP BY ger_id, cgp_id, rub_id, cgcu_id
                ) d USING (ger_id, cgp_id, rub_id, cgcu_id)
                WHERE ger_id = 2 AND cgp_id = 1 AND rub_id = 3 AND cgcu_id = 1
                GROUP BY ger_id, cgp_id, rub_id, cgcu_id
                UNION ALL
                SELECT IFNULL(cuenta, '223') cuenta, ger_id, cgp_id, rub_id, '' cgcu_id, IFNULL(debe, 0) debe, IFNULL(haber, 0) haber, MIN(sc.nat_id) cgm_naturaleza
                FROM " . $ejercicio . "cat_CgSubcuenta sc
                LEFT JOIN (
                    SELECT '223' cuenta, ger_id, cgp_id, rub_id, MIN(cgcu_id) AS cgcu_id, SUM(pdx_debe) debe, SUM(pdx_haber) haber, MIN(nat_id) nat_id
                    FROM " . $ejercicio . 'cg_PolizaDetalleAuxiliar d
                    INNER JOIN ' . $ejercicio . 'cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                    ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? ' INNER JOIN cat_CargaTrabajo t USING(gpo_id,ur_id)' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                    AND')) . '
                    ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                    AND ger_id = 2 AND cgp_id = 2 AND rub_id = 3 AND cgcu_id = 3
                    AND epl_id NOT IN ('C','H')
                    " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . "
                    GROUP BY ger_id,cgp_id,rub_id
                ) d USING (ger_id, cgp_id, rub_id)
                WHERE ger_id = 2 AND cgp_id = 2 AND rub_id = 3
                GROUP BY ger_id, cgp_id, rub_id
                UNION ALL
                SELECT IFNULL(cuenta, '2116') cuenta, ger_id, cgp_id, rub_id, cgcu_id, IFNULL(debe, 0) debe, IFNULL(haber, 0) haber, MIN(sc.nat_id) AS cgm_naturaleza
                FROM " . $ejercicio . "cat_CgSubcuenta sc
                LEFT JOIN (
                    SELECT '2116' cuenta, 2 as ger_id, 1 as cgp_id, 1 as rub_id, 6 as cgcu_id, SUM(pdx_debe) debe, SUM(pdx_haber) haber, MAX(nat_id) nat_id
                    FROM " . $ejercicio . 'cg_PolizaDetalleAuxiliar d
                    INNER JOIN ' . $ejercicio . 'cg_PolizaMaestro USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
                    ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? ' INNER JOIN ' . $ejercicio . 'cat_CargaTrabajo t USING(gpo_id,ur_id)
                    ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue
                    != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                    AND')) . '
                    ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde
                    == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                    # AND ger_id=2 and cgp_id=1 and rub_id=1 and cgcu_id=6
                    AND (
                                (ger_id=2 and cgp_id=1 and rub_id in (1,2,5,6,7,9) ) # and cgcu_id=6
                            OR  (ger_id=2 and cgp_id=2 and rub_id in (1,2,4,5,6) )
                    ) # and cgcu_id=6
                    AND epl_id NOT IN ('C','H')
                    " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . '
                    GROUP BY  ger_id#,cgp_id,rub_id,cgcu_id
                )d USING (ger_id , cgp_id , rub_id , cgcu_id)
                WHERE ger_id=2 and cgp_id=1 and rub_id=1 and cgcu_id=6
                GROUP BY ger_id,cgp_id,rub_id,cgcu_id
            ) cuenta;
        ');
        $conexion->consultar();
        $conexion->desconectar();

        $registro = [];

        while ($rowRegistro = $conexion->extraer()) {
            $key = $rowRegistro['cuenta'];

            $registro[$key]['cuenta'] = $rowRegistro['cuenta'];
            $registro[$key]['debe'] = $rowRegistro['debe'];
            $registro[$key]['haber'] = $rowRegistro['haber'];
            $registro[$key]['naturalezaCuenta'] = $rowRegistro['cgm_naturaleza'];
            $registro[$key]['resultado'] = ($registro[$key]['naturalezaCuenta'] == 'H') ? $registro[$key]['haber'] - $registro[$key]['debe'] : $registro[$key]['debe'] - $registro[$key]['haber'];
        }
        return $registro;
    }

    public function compararCuentas($array2020, $array2021)
    {

        $arrayGlobal = [];
        //recuperamos la posicion inicial en el arreglo.
        reset($array2020);
        reset($array2021);

        $key2021 = key($array2021);
        $key2020 = key($array2020);

        while ($key2021 || $key2020) {
            //si las cadenas son iguales
            if (CompararCadenas((string) $key2020, (string) $key2021) == 0) {
                $arrayGlobal[$key2021]['cuenta'] = $array2021[$key2021]['cuenta'];
                $arrayGlobal[$key2021]['nombre'] = $array2021[$key2021]['nombre'];
                $arrayGlobal[$key2021]['nivel'] = $array2021[$key2021]['nivel'];
                $arrayGlobal[$key2021]['importe2021'] = $array2021[$key2021]['importe'];
                $arrayGlobal[$key2021]['importe2020'] = $array2020[$key2021]['importe'];

                next($array2021);
                $key2021 = key($array2021);

                next($array2020);
                $key2020 = key($array2020);
                //si no existe 2018 pero si en 2020
            } elseif (!$key2020 || CompararCadenas(
                (string) $key2021,
                (string) $key2020
            ) < 0) { //cuentas anteriores
                $arrayGlobal[$key2021]['cuenta'] = $array2021[$key2021]['cuenta'];
                $arrayGlobal[$key2021]['nombre'] = $array2021[$key2021]['nombre'];
                $arrayGlobal[$key2021]['nivel'] = $array2021[$key2021]['nivel'];
                $arrayGlobal[$key2021]['importe2021'] = $array2021[$key2021]['importe'];
                $arrayGlobal[$key2021]['importe2020'] = (isset($arrayGlobal[$key2021]['importe2020']) ? $arrayGlobal[$key2021]['importe2020'] : 0.00);

                next($array2021);
                $key2021 = key($array2021);
            } else {
                //si no existe en 2020 pero si en 2018
                $arrayGlobal[$key2020]['cuenta'] = $array2020[$key2020]['cuenta'];
                $arrayGlobal[$key2020]['nombre'] = $array2020[$key2020]['nombre'];
                $arrayGlobal[$key2020]['nivel'] = $array2020[$key2020]['nivel'];
                $arrayGlobal[$key2020]['importe2021'] = (isset($arrayGlobal[$key2020]['importe2021']) ? $arrayGlobal[$key2020]['importe2021'] : 0.00);
                $arrayGlobal[$key2020]['importe2020'] = $array2020[$key2020]['importe'];

                next($array2020);
                $key2020 = key($array2020);
            }
        }

        return $arrayGlobal;
    }

    //FUNCION PARA DEVOLVER ARRAY ORDENADO
    public function devuelveArrayOrdenado($array, $p)
    {
        $arreglo = [];
        if (is_array($array)) {
            //naturaleza de la cuenta (activo)
            $naturalezaActivo = '';

            foreach ($array as $key => $val) {
                if ($p[$array[$key]['nivel']] == 1) {
                    //Agregamos a la llave del arreglo 0's a la derecha
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT)] = $array[$key];
                }
            }

            //ordenamos arreglo
            ksort($arreglo);

            $debe = $haber = 0;
            foreach ($arreglo as $indice => $val) {
                if ($arreglo[$indice]['nivel'] == 1 && $arreglo[$indice]['ger_id'] == 1) {
                    $naturalezaActivo = $arreglo[$indice]['naturalezaCuenta'];
                }

                //obtenemos los importes, si la naturaleza de la cuenta es Debe entonces se resta debe - haber, sino lo contrario
                $debe = (is_numeric($arreglo[$indice]['debe']) ? $arreglo[$indice]['debe'] : 0.00);
                $haber = (is_numeric($arreglo[$indice]['haber']) ? $arreglo[$indice]['haber'] : 0.00);
                $arreglo[$indice]['importe'] = (($arreglo[$indice]['naturalezaCuenta'] == 'D') ? ($debe - $haber) : ($haber - $debe));
                //Si es cuenta del activo y la naturaleza de la cuenta es diferente a la del genero se pone el importe en negativo.
                if ($arreglo[$indice]['ger_id'] == 1 && $arreglo[$indice]['naturalezaCuenta'] != $naturalezaActivo && $arreglo[$indice]['nivel'] >= 3) {
                    $arreglo[$indice]['importe'] = ($arreglo[$indice]['importe'] * -1);
                }
            }

            //regresamos arreglo ordenado de manera ascendente
            return $arreglo;
        }
    }

    public function ajusteejerciciosAnte2021(&$hacienda)
    {
        $debeahorro = $hacienda['321']['debe'];
        $haberahorro = $hacienda['321']['haber'];
        foreach ($hacienda as $key => $val) {
            if (strlen($key) >= 3) {
                if (substr($key, 0, 3) == 322) {
                    $debeEjerciciosA = $hacienda[$key]['debe'];
                    $habeEjerciciosA = $hacienda[$key]['haber'];
                    $hacienda[$key]['debe'] = $debeEjerciciosA + $debeahorro;
                    $hacienda[$key]['haber'] = $habeEjerciciosA + $haberahorro;
                }
            }
        }
    }

    public function ajusteCuentasAhorro2021(&$hacienda, $saldo2021, $saldo2020)
    {

        // Como las sumas del patrimonio generado están en el encabezado, las ajustamos
        // para incluir el resultado del ejercicio del Estado de Actividades
        $key_32 = array_search('32', array_column($hacienda, 'cuenta'));
        $hacienda[$key_32]['importe2021'] += $saldo2021;
        $hacienda[$key_32]['importe2020'] += $saldo2020;

        // Ponemos el total de Hacienda Pública
        $key_31 = array_search('31', array_column($hacienda, 'cuenta'));
        $key_32 = array_search('32', array_column($hacienda, 'cuenta'));
        $key_33 = array_search('33', array_column($hacienda, 'cuenta'));

        $saldo_ejercicio_actual = $hacienda[$key_31]['importe2021'] +
            $hacienda[$key_32]['importe2021'] +
            $hacienda[$key_33]['importe2021'];

        $saldo_ejercicio_anterior = $hacienda[$key_31]['importe2020'] +
            $hacienda[$key_32]['importe2020'] +
            $hacienda[$key_33]['importe2020'];

        $max = count($hacienda);

        $hacienda[$max - 2] = [
            'cuenta'      => '35999', 'nombre' => 'TOTAL HACIENDA PÚBLICA/PATRIMONIO',
            'importe2021' => $saldo_ejercicio_actual, 'importe2020' => $saldo_ejercicio_anterior,
            'nivelCuenta' => 5,
        ];


        $totalhacienda2021 = 0;
        $totalhacienda2020 = 0;
        foreach ($hacienda as $key => $val) {
            if (substr($val['cuenta'], 0, 5) == 31999) {
                // cuenta acreedora
                $totalhacienda2021 += $hacienda[$key]['importe2021'];
                $totalhacienda2020 += $hacienda[$key]['importe2020'];
            }
            if (substr($val['cuenta'], 0, 3) == 321) {
                // cuenta acreedora
                $hacienda[$key]['importe2021'] = $saldo2021;
                $hacienda[$key]['importe2020'] = $saldo2020;
            }

            if (substr($val['cuenta'], 0, 5) == 32999) {
                // cuenta acreedora
                $hacienda[$key]['importe2021'] = $hacienda[$key]['importe2021'] + $saldo2021;
                $hacienda[$key]['importe2020'] = $hacienda[$key]['importe2020'] + $saldo2020;
                $totalhacienda2021 += $hacienda[$key]['importe2021'];
                $totalhacienda2020 += $hacienda[$key]['importe2020'];
            }
        }

        foreach ($hacienda as $key => $val) {
            if (substr($val['cuenta'], 0, 5) == 33999) {
                // cuenta acreedora
                $hacienda[$key]['importe2021'] = $totalhacienda2021;
                $hacienda[$key]['importe2020'] = $totalhacienda2020;
                $hacienda[$key]['nombre'] = 'TOTAL HACIENDA PÚBLICA/PATRIMONIO';
            }
        }

    }

    public function dame_plural_del_nombre($nombre)
    {

        switch (trim($nombre)) {
            case 'ACTIVO CIRCULANTE':
                return 'ACTIVOS CIRCULANTES';
            case 'ACTIVO NO CIRCULANTE':
                return 'ACTIVOS NO CIRCULANTES';
                break;
            case 'PASIVO CIRCULANTE':
                return 'PASIVOS CIRCULANTES';
                break;
            case 'PASIVO NO CIRCULANTE':
                return 'PASIVOS NO CIRCULANTES';
                break;
                break;
            default:
                return $nombre;
        }
    }

    public function textoConFormatoEnMinusculas($cadena): String
    {
        $cadenaFormateada = ucwords(mb_strtolower($cadena, 'UTF-8'));
        $subStrBuscar = [' Y/O ', ' Y/o ', ' A ', ' Y ', ' O ', ' De ', ' La ', ' Del ', ' En ', ' Con ', ' Por ', ' Para ', 's/t', 's/d', 'a/p'];
        $reemplazaSubStr = [' y/o ', ' y/o ', ' a ', ' y ', ' o ', ' de ', ' la ', ' del ', ' en ', ' con ', ' por ', ' para ', 's/T', 's/D', 'a/P'];
        $cadenaFormateada = str_replace($subStrBuscar, $reemplazaSubStr, $cadenaFormateada);
        return $cadenaFormateada;
    }
}
$pdf = new reportetabla('L', 'mm', 'letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
$pdf->AliasNbPages();
// -----------------------------------------------------------------------------------------
// Paso 5: Obtenemos los datos a imprimir... DE OTRA FUENTE... Es decir, en éste PHP NO estará
// el código para obtenerlo, sino en otro lugar (generalmente llamado en un #include)
// Lo haremos así porque si no éste código crecería muchísimo
// -----------------------------------------------------------------------------------------
switch ($pdf->men_id) {

    case MENU_CUENTA_PUBLICA:
        switch ($pdf->opc_id) {
            case 1: // Estado de Situacion Financiera
                // * inicio
                // Iniciamos el reporte
                // --------------------------------------------------------------------------------------------------------------------------------
                $mesId = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';
                $mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
                $tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : '';
                $checkMiles = (isset($_POST['ckmiles'])) ? 1000 : 1;
                $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
                $excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                $ue = isset($_POST['ue']) ? $_POST['ue'] : '';
                $checkCuentaOrden = isset($_POST['checkCuentaOrden']) ? $_POST['checkCuentaOrden'] : -1;
                $cerosOrden = (isset($_POST['cerosOrden']) && $_POST['cerosOrden'] != '') ? $_POST['cerosOrden'] : 0;
                $decimales = (isset($_POST['decimales']) && $_POST['decimales'] != '') ? 2 : 0;
                $desde = intval($mesHasta) + 1;
                $hasta = -1;
                $texto = ($checkMiles == 1000) ? '(Cifras en Miles de Pesos)' : '(Cifras en Pesos)';

                list($activo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 1, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($pasivo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 2, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($hacienda) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 3, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                if ($checkCuentaOrden) {
                    list($ctasOrden8) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 8, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), isset($chkEnMiles), $grupos, $excluir, $cerosOrden, isset($incCta));
                    list($ctasOrden7) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 7, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), isset($chkEnMiles), $grupos, $excluir, $cerosOrden, isset($incCta));
                }

                if ($_SESSION[_S]['reportes'] == 'C111') {
                    list($activo2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 1, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                    list($pasivo2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 2, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                    list($hacienda2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 3, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                    $pdf->ajusteejerciciosAnte2021($hacienda);
                    $pdf->ajusteejerciciosAnte2021($hacienda2020);

                    $llavesAPH2020 = array_merge(array_keys($activo2020), array_keys($pasivo2020), array_keys($hacienda2020)); // Respaldamos las llaves/indices de las 3 listas
                    $aPH2020 = array_combine($llavesAPH2020, array_merge($activo2020, $pasivo2020, $hacienda2020)); // Combinamos los arreglos conservando sus llaves de origen

                    list($ingresos2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                    list($gastos2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                }

                $llavesAPH = array_merge(array_keys($activo), array_keys($pasivo), array_keys($hacienda)); // Respaldamos las llaves/indices de las 3 listas
                $aPH = array_combine($llavesAPH, array_merge($activo, $pasivo, $hacienda)); // Combinamos los arreglos conservando sus llaves de origen

                list($ingresos2021) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($gastos2021) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                $pdf->setOrientation('P');
                $pdf->AliasNbPages();
                $pdf->texto = $texto;
                $pdf->AddPage();
                $pdf->setRelleno('no');
                $pdf->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
                $pdf->AddFont('Montserrat', '', 'Montserrat-Regular.php');
                $pdf->SetFont('Montserrat-b', '', 7);
                $pdf->SetTitle('Edo_Situacion_Financiera');

                $pdf->SetWidths([110, 35, 35]);
                $pdf->SetAligns(['L', 'R', 'R']);

                $totalA = 0;
                $totalP = 0;
                $totalH = 0;

                $totalActivo2020 = $totalActivo2021 = $totalPasivo2020 = $totalPasivo2021 = $totalHacienda2020 = $totalHacienda2021 = 0;

                $cuentasTotalesA = [11999, 12999, 13999];
                $cuentasTotalesP = [21999, 22999, 23999, 31999, 32999, 33999, 34999, 35999, 36999];

                $totalActivo2021 = (($activo[1]['naturalezaCuenta'] == 'D') ? $activo[1]['debe'] - $activo[1]['haber'] : $activo[1]['haber'] - $activo[1]['debe']);
                $totalPasivo2021 = (($pasivo[2]['naturalezaCuenta'] == 'D') ? $pasivo[2]['debe'] - $pasivo[2]['haber'] : $pasivo[2]['haber'] - $pasivo[2]['debe']);
                $totalHacienda2021 = (($hacienda[3]['naturalezaCuenta'] == 'D') ? $hacienda[3]['debe'] - $hacienda[3]['haber'] : $hacienda[3]['haber'] - $hacienda[3]['debe']);

                if ($_SESSION[_S]['reportes'] == 'C111') {
                    $totalActivo2020 = (($activo2020[1]['naturalezaCuenta'] == 'D') ? $activo2020[1]['debe'] - $activo2020[1]['haber'] : $activo2020[1]['haber'] - $activo2020[1]['debe']);
                    $totalPasivo2020 = (($pasivo2020[2]['naturalezaCuenta'] == 'D') ? $pasivo2020[2]['debe'] - $pasivo2020[2]['haber'] : $pasivo2020[2]['haber'] - $pasivo2020[2]['debe']);
                    $totalHacienda2020 = (($hacienda2020[3]['naturalezaCuenta'] == 'D') ? $hacienda2020[3]['debe'] - $hacienda2020[3]['haber'] : $hacienda2020[3]['haber'] - $hacienda2020[3]['debe']);
                }

                $aPH[23999] = [
                    'cuenta'            => '23999',
                    'nombre'            => 'TOTAL DEL PASIVO',
                    'naturalezaCuenta'  => $pasivo[2]['naturalezaCuenta'],
                    'naturalezaDetalle' => '23999',
                    'debe'              => $pasivo[2]['debe'], 'haber' => $pasivo[2]['haber'],
                    'ger_id'            => 2, 'nivel' => 5,
                ];

                $cuentasNoValidas = [11999, 12999, 13999, 21999, 22999, 23999, 31999, 32999, 33999, 34999, 35999, 36999]; // creamos array de las cuentas ficticias creadas.

                foreach ($aPH as $key => $value) {

                    if ($aPH[$key]['nivel'] == 2) {
                        switch ($aPH[$key]['ger_id']) {
                            case '1':
                                $aPH[$aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999'] = [
                                    'cuenta'            => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                                    'nombre'            => 'TOTAL DE ' . $pdf->dame_plural_del_nombre($aPH[$key]['nombre']),
                                    'naturalezaCuenta'  => $aPH[$key]['naturalezaCuenta'],
                                    'naturalezaDetalle' => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                                    'debe'              => $aPH[$key]['debe'], 'haber' => $aPH[$key]['haber'],
                                    'ger_id'            => 1, 'nivel' => 5,
                                ];
                                break;
                            case '2':
                                $aPH[$aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999'] = [
                                    'cuenta'            => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                                    'nombre'            => 'TOTAL DE ' . $pdf->dame_plural_del_nombre($aPH[$key]['nombre']),
                                    'naturalezaCuenta'  => $aPH[$key]['naturalezaCuenta'],
                                    'naturalezaDetalle' => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                                    'debe'              => $aPH[$key]['debe'], 'haber' => $aPH[$key]['haber'],
                                    'ger_id'            => 2, 'nivel' => 5,
                                ];
                                break;
                            case '3':
                                $aPH[$aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999'] = [
                                    'cuenta'            => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                                    'nombre'            => 'TOTAL DE ' . $pdf->dame_plural_del_nombre($aPH[$key]['nombre']),
                                    'naturalezaCuenta'  => $aPH[$key]['naturalezaCuenta'],
                                    'naturalezaDetalle' => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                                    'debe'              => $aPH[$key]['debe'], 'haber' => $aPH[$key]['haber'],
                                    'ger_id'            => 3, 'nivel' => 5,
                                ];
                                break;
                        }
                    }
                    if(!empty($aPH[$key]['cuenta'])){
                        $aPH[$key]['naturalezaDetalle'] = str_replace('.', '', $aPH[$key]['cuenta']); //Respaldamos en naturalezaDetalle el la cuenta sin puntos
                        $aPH[$key]['cuenta'] = str_replace('.', '', $aPH[$key]['cuenta']); // A cuenta le quitamos los puntos.
                        $aPH[$key]['cuenta'] = str_pad($aPH[$key]['cuenta'], 5, '0', STR_PAD_RIGHT); // llenamos de 0's a la derecha en el campo cuenta para poder ordenarlo de acuerdo a ese criterio
                    }
                }

                $estadoConsolidado = []; //array para informacion de activo
                $estadoConsolidadoP = []; // array para informacion de pasivo y hacienda.

                $naturalezaGenero = $aPH[1]['naturalezaCuenta'];
                $debe = 0;
                $haber = 0;

                foreach ($aPH as $i => $v) {
                    if (isset($v['ger_id'])) {
                        switch ($v['ger_id']) {
                            case 1: //ACTIVO
                                if ($pdf->nivel[$v['nivel']] == 1 || in_array($v['cuenta'], $cuentasTotalesA)) {
                                    $estadoConsolidado[$i]['cuenta'] = $v['naturalezaDetalle'];
                                    $estadoConsolidado[$i]['orden'] = $v['cuenta'];
                                    $estadoConsolidado[$i]['nivelCuenta'] = $v['nivel'];
                                    $estadoConsolidado[$i]['nombre'] = $v['nombre'];

                                    $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                                    $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                                    $estadoConsolidado[$i]['importe'] = (($v['naturalezaCuenta'] == 'D') ? $debe - $haber : $haber - $debe);

                                    if ($v['nivel'] >= 3) {
                                        $estadoConsolidado[$i]['importe'] = (($v['naturalezaCuenta'] != $naturalezaGenero) ? $estadoConsolidado[$i]['importe'] * -1 : $estadoConsolidado[$i]['importe']);
                                    }

                                    $estadoConsolidado[$i]['tipo'] = 'A';
                                }
                                break;
                            case 2: //PASIVO
                            case 3: //HACIENDA
                                $debe = 0;
                                $haber = 0;
                                if ($pdf->nivel[$v['nivel']] == 1 || in_array($v['cuenta'], $cuentasTotalesP)) {
                                    $estadoConsolidadoP[$i]['cuenta'] = $v['naturalezaDetalle'];
                                    $estadoConsolidadoP[$i]['orden'] = $v['cuenta'];
                                    $estadoConsolidadoP[$i]['nivelCuenta'] = $v['nivel'];
                                    $estadoConsolidadoP[$i]['nombre'] = $v['nombre'];

                                    $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                                    $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                                    $estadoConsolidadoP[$i]['importe'] = (($v['naturalezaCuenta'] == 'D') ? $debe - $haber : $haber - $debe);
                                    $estadoConsolidadoP[$i]['tipo'] = 'PH';
                                }
                                break;
                        }
                    }
                }

                $estadoConsolidado2020 = []; //array para informacion de activo
                $estadoConsolidadoP2020 = []; // array para informacion de pasivo y hacienda.
                $debe = 0;
                $haber = 0;

                if ($_SESSION[_S]['reportes'] == 'C111') {

                    $aPH2020[23999] = [
                        'cuenta'            => '23999',
                        'nombre'            => 'TOTAL PASIVO',
                        'naturalezaCuenta'  => $pasivo2020[2]['naturalezaCuenta'],
                        'naturalezaDetalle' => '23999',
                        'debe'              => $pasivo2020[2]['debe'], 'haber' => $pasivo2020[2]['haber'],
                        'ger_id'            => 2, 'nivel' => 5,
                    ];
                    foreach ($aPH2020 as $key => $value) {
                        if ($aPH2020[$key]['nivel'] == 2) {
                            switch ($aPH2020[$key]['ger_id']) {
                                case '1': // Activo
                                    $aPH2020[$aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999'] = [
                                        'cuenta'            => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                                        'nombre'            => 'TOTAL DE ' . $pdf->dame_plural_del_nombre($aPH2020[$key]['nombre']),
                                        'naturalezaCuenta'  => $aPH2020[$key]['naturalezaCuenta'],
                                        'naturalezaDetalle' => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                                        'debe'              => $aPH2020[$key]['debe'], 'haber' => $aPH2020[$key]['haber'],
                                        'ger_id'            => 1, 'nivel' => 5,
                                    ];
                                    break;
                                case '2': //Pasivo
                                    $aPH2020[$aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999'] = [
                                        'cuenta'            => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                                        'nombre'            => 'TOTAL DE ' . $pdf->dame_plural_del_nombre($aPH2020[$key]['nombre']),
                                        'naturalezaCuenta'  => $aPH2020[$key]['naturalezaCuenta'],
                                        'naturalezaDetalle' => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                                        'debe'              => $aPH2020[$key]['debe'], 'haber' => $aPH2020[$key]['haber'],
                                        'ger_id'            => 2, 'nivel' => 5,
                                    ];
                                    break;
                                case '3': //Hacienda
                                    $aPH2020[$aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999'] = [
                                        'cuenta'            => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                                        'nombre'            => 'TOTAL DE ' . $pdf->dame_plural_del_nombre($aPH2020[$key]['nombre']),
                                        'naturalezaCuenta'  => $aPH2020[$key]['naturalezaCuenta'],
                                        'naturalezaDetalle' => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                                        'debe'              => $aPH2020[$key]['debe'], 'haber' => $aPH2020[$key]['haber'],
                                        'ger_id'            => 3, 'nivel' => 5,
                                    ];
                                    break;
                            }
                        }
                        if(!empty($aPH2020[$key]['cuenta'])){
                            $aPH2020[$key]['naturalezaDetalle'] = str_replace('.', '', $aPH2020[$key]['cuenta']); //Respaldamos en naturalezaDetalle el la cuenta sin puntos
                            $aPH2020[$key]['cuenta'] = str_replace('.', '', $aPH2020[$key]['cuenta']); // A cuenta le quitamos los puntos.
                            $aPH2020[$key]['cuenta'] = str_pad($aPH2020[$key]['cuenta'], 5, '0', STR_PAD_RIGHT); // llenamos de 0's a la derecha en el campo cuenta para poder ordenarlo de acuerdo a ese criterio
                        }
                    }

                    $naturalezaGenero2020 = $aPH2020[1]['naturalezaCuenta'];

                    foreach ($aPH2020 as $i => $v) {

                        if (isset($v['ger_id'])) {
                            switch ($v['ger_id']) {
                                case 1:
                                    if ($pdf->nivel[$v['nivel']] == 1 || in_array($v['cuenta'], $cuentasTotalesA)) {
                                        $estadoConsolidado2020[$i]['cuenta'] = $v['naturalezaDetalle'];
                                        $estadoConsolidado2020[$i]['orden'] = $v['cuenta'];
                                        $estadoConsolidado2020[$i]['nivelCuenta'] = $v['nivel'];
                                        $estadoConsolidado2020[$i]['nombre'] = $v['nombre'];

                                        $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                                        $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                                        $estadoConsolidado2020[$i]['importe'] = (($v['naturalezaCuenta'] == 'D') ? $debe - $haber : $haber - $debe);

                                        if ($v['nivel'] >= 3) {
                                            $estadoConsolidado2020[$i]['importe'] = (($v['naturalezaCuenta'] != $naturalezaGenero2020) ? $estadoConsolidado2020[$i]['importe'] * -1 : $estadoConsolidado2020[$i]['importe']);
                                        }

                                        $estadoConsolidado2020[$i]['tipo'] = 'A';
                                    }
                                    break;
                                case 2:
                                case 3:
                                    $debe = 0;
                                    $haber = 0;

                                    if ($pdf->nivel[$v['nivel']] == 1 || in_array($v['cuenta'], $cuentasTotalesP)) {
                                        $estadoConsolidadoP2020[$i]['cuenta'] = $v['naturalezaDetalle'];
                                        $estadoConsolidadoP2020[$i]['orden'] = $v['cuenta'];
                                        $estadoConsolidadoP2020[$i]['nivelCuenta'] = $v['nivel'];
                                        $estadoConsolidadoP2020[$i]['nombre'] = $v['nombre'];

                                        $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                                        $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                                        $estadoConsolidadoP2020[$i]['importe'] = (($v['naturalezaCuenta'] == 'D') ? $debe - $haber : $haber - $debe);

                                        $estadoConsolidadoP2020[$i]['tipo'] = 'PH';
                                    }
                                    break;
                            }
                        }
                    }

                    $keyActivo2020 = key($estadoConsolidado2020);
                }

                $keyActivo2021 = key($estadoConsolidado);
                $finalAct = [];

                while ($keyActivo2021 || $keyActivo2020) {
                    if (CompararCadenas((string) $keyActivo2021, (string) $keyActivo2020) == 0) {
                        $finalAct[$keyActivo2021]['cuenta'] = $estadoConsolidado[$keyActivo2021]['cuenta'];
                        $finalAct[$keyActivo2021]['nombre'] = $estadoConsolidado[$keyActivo2021]['nombre'];
                        $finalAct[$keyActivo2021]['orden'] = $estadoConsolidado[$keyActivo2021]['orden'];
                        $finalAct[$keyActivo2021]['nivelCuenta'] = $estadoConsolidado[$keyActivo2021]['nivelCuenta'];
                        $finalAct[$keyActivo2021]['importe2021'] = $estadoConsolidado[$keyActivo2021]['importe'];
                        $finalAct[$keyActivo2021]['importe2020'] = $estadoConsolidado2020[$keyActivo2020]['importe'];

                        next($estadoConsolidado);
                        $keyActivo2021 = key($estadoConsolidado);

                        next($estadoConsolidado2020);
                        $keyActivo2020 = key($estadoConsolidado2020);
                    } elseif (!$keyActivo2020 || CompararCadenas((string) $keyActivo2021, (string) $keyActivo2020) < 0) { //cuentas anteriores
                        $finalAct[$keyActivo2021]['cuenta'] = $estadoConsolidado[$keyActivo2021]['cuenta'];
                        $finalAct[$keyActivo2021]['nombre'] = $estadoConsolidado[$keyActivo2021]['nombre'];
                        $finalAct[$keyActivo2021]['orden'] = $estadoConsolidado[$keyActivo2021]['orden'];
                        $finalAct[$keyActivo2021]['nivelCuenta'] = $estadoConsolidado[$keyActivo2021]['nivelCuenta'];
                        $finalAct[$keyActivo2021]['importe2021'] = $estadoConsolidado[$keyActivo2021]['importe'];
                        $finalAct[$keyActivo2021]['importe2020'] = (isset($finalAct[$keyActivo2021]['importe2020']) ? $finalAct[$keyActivo2021]['importe2020'] : 0.00);

                        next($estadoConsolidado);
                        $keyActivo2021 = key($estadoConsolidado);
                    } else {
                        $finalAct[$keyActivo2020]['cuenta'] = $estadoConsolidado2020[$keyActivo2020]['cuenta'];
                        $finalAct[$keyActivo2020]['nombre'] = $estadoConsolidado2020[$keyActivo2020]['nombre'];
                        $finalAct[$keyActivo2020]['orden'] = $estadoConsolidado2020[$keyActivo2020]['orden'];
                        $finalAct[$keyActivo2020]['nivelCuenta'] = $estadoConsolidado2020[$keyActivo2020]['nivelCuenta'];
                        $finalAct[$keyActivo2020]['importe2021'] = (isset($finalAct[$keyActivo2020]['importe2021']) ? $finalAct[$keyActivo2020]['importe2021'] : 0.00);
                        $finalAct[$keyActivo2020]['importe2020'] = $estadoConsolidado2020[$keyActivo2020]['importe'];

                        next($estadoConsolidado2020);
                        $keyActivo2020 = key($estadoConsolidado2020);
                    }
                }

                $keyPH2021 = key($estadoConsolidadoP);
                $keyPH2020 = key($estadoConsolidadoP2020);
                $finalPH = [];

                while ($keyPH2021 || $keyPH2020) {
                    if (CompararCadenas((string) $keyPH2021, (string) $keyPH2020) == 0) {
                        $finalPH[$keyPH2021]['cuenta'] = $estadoConsolidadoP[$keyPH2021]['cuenta'];
                        $finalPH[$keyPH2021]['nombre'] = $estadoConsolidadoP[$keyPH2021]['nombre'];
                        $finalPH[$keyPH2021]['orden'] = $estadoConsolidadoP[$keyPH2021]['orden'];
                        $finalPH[$keyPH2021]['nivelCuenta'] = $estadoConsolidadoP[$keyPH2021]['nivelCuenta'];
                        $finalPH[$keyPH2021]['importe2021'] = $estadoConsolidadoP[$keyPH2021]['importe'];
                        $finalPH[$keyPH2021]['importe2020'] = $estadoConsolidadoP2020[$keyPH2020]['importe'];

                        next($estadoConsolidadoP);
                        $keyPH2021 = key($estadoConsolidadoP);

                        next($estadoConsolidadoP2020);
                        $keyPH2020 = key($estadoConsolidadoP2020);
                    } elseif (!$keyPH2020 || CompararCadenas((string) $keyPH2021, (string) $keyPH2020) < 0) { //cuentas anteriores
                        $finalPH[$keyPH2021]['cuenta'] = $estadoConsolidadoP[$keyPH2021]['cuenta'];
                        $finalPH[$keyPH2021]['nombre'] = $estadoConsolidadoP[$keyPH2021]['nombre'];
                        $finalPH[$keyPH2021]['orden'] = $estadoConsolidadoP[$keyPH2021]['orden'];
                        $finalPH[$keyPH2021]['nivelCuenta'] = $estadoConsolidadoP[$keyPH2021]['nivelCuenta'];
                        $finalPH[$keyPH2021]['importe2021'] = $estadoConsolidadoP[$keyPH2021]['importe'];
                        $finalPH[$keyPH2021]['importe2020'] = (isset($finalPH[$keyPH2021]['importe2020']) ? $finalPH[$keyPH2021]['importe2020'] : 0.00);

                        next($estadoConsolidadoP);
                        $keyPH2021 = key($estadoConsolidadoP);
                    } else {
                        $finalPH[$keyPH2020]['cuenta'] = $estadoConsolidadoP2020[$keyPH2020]['cuenta'];
                        $finalPH[$keyPH2020]['nombre'] = $estadoConsolidadoP2020[$keyPH2020]['nombre'];
                        $finalPH[$keyPH2020]['orden'] = $estadoConsolidadoP2020[$keyPH2020]['orden'];
                        $finalPH[$keyPH2020]['nivelCuenta'] = $estadoConsolidadoP2020[$keyPH2020]['nivelCuenta'];
                        $finalPH[$keyPH2020]['importe2021'] = (isset($finalPH[$keyPH2020]['importe2021']) ? $finalPH[$keyPH2020]['importe2021'] : 0.00);
                        $finalPH[$keyPH2020]['importe2020'] = $estadoConsolidadoP2020[$keyPH2020]['importe'];

                        next($estadoConsolidadoP2020);
                        $keyPH2020 = key($estadoConsolidadoP2020);
                    }
                }

                $sort = [];
                $sort['indice'] = [];

                foreach ($finalAct as $indice => $val) {
                    $sort['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
                }
                array_multisort($sort['indice'], SORT_ASC, $finalAct); //Ordenamos de acuerdo al valor de la cuenta 2020.

                $sort2 = [];
                $sort2['indice'] = [];

                foreach ($finalPH as $indice => $val) {
                    $sort2['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
                }
                array_multisort($sort2['indice'], SORT_ASC, $finalPH); //Ordenamos de acuerdo al valor de la cuenta 2018.

                // Ponemos el total en el encabezado para Hacienda Pública, solicitado por contabilidad porque
                // así está el formato del CONAC
                $key_31999 = array_search('31999', array_column($finalPH, 'cuenta'));
                $key_32999 = array_search('32999', array_column($finalPH, 'cuenta'));
                $key_33999 = array_search('33999', array_column($finalPH, 'cuenta'));

                // Quitamos la fila de los totales
                unset($finalPH[$key_33999]);
                unset($finalPH[$key_32999]);
                unset($finalPH[$key_31999]);
                $finalPH = array_values($finalPH);


                $max = max(count($finalAct), count($finalPH));

                $max = $max += 3; // incrementamos a 2 el valor de max para pintar los totales globales.

                if (count($finalAct) < $max) {
                    $ini = count($finalAct);
                    for ($i = $ini; $i < $max; $i++) {
                        $finalAct[$i] = ['cuenta' => '', 'nombre' => '', 'importe2021' => '',
                            'importe2020'         => '', 'nivelCuenta' => 5]; // se rellenan con arreglos vacios, esto para igualar los arreglos.
                    }
                }
                // si el pasivo+hacienda tiene menos elementos que el activo, lo igualamos
                if (count($finalPH) < $max) {
                    $ini = count($finalPH);
                    for ($i = $ini; $i < $max; $i++) {
                        $finalPH[$i] = ['cuenta' => '', 'nombre' => '', 'importe2021' => '',
                            'importe2020'        => '', 'nivelCuenta' => 5]; // se rellenan con arreglos vacios, esto para igualar los arreglos.
                    }
                }

                // Ponemos los totales globales
                if ($_SESSION[_S]['reportes'] == 'C111') {
                    $finalAct[21] = [
                        'cuenta'      => '13999', 'nombre' => 'TOTAL DEL ACTIVO',
                        'importe2021' => $totalActivo2021, 'importe2020' => $totalActivo2020,
                        'nivelCuenta' => 5,
                    ];
                }
                // Se guardan en la ultima posicion el total del activo
                else {
                    $finalAct[21] = [
                        'cuenta'      => '13999', 'nombre' => 'TOTAL DEL ACTIVO',
                        'importe2021' => $totalActivo2021, 'nivelCuenta' => 5,
                    ];
                }
                // Se guardan en la ultima posicion el total del activo

                $ad2021I = (($ingresos2021[4]['naturalezaCuenta'] == 'D') ? ($ingresos2021[4]['debe'] - $ingresos2021[4]['haber']) : ($ingresos2021[4]['haber'] - $ingresos2021[4]['debe']));
                $ad2021G = (($gastos2021[5]['naturalezaCuenta'] == 'D') ? ($gastos2021[5]['debe'] - $gastos2021[5]['haber']) : ($gastos2021[5]['haber'] - $gastos2021[5]['debe']));

                if ($_SESSION[_S]['reportes'] == 'C111') {
                    $ad2020I = (($ingresos2020[4]['naturalezaCuenta'] == 'D') ? ($ingresos2020[4]['debe'] - $ingresos2020[4]['haber']) : ($ingresos2020[4]['haber'] - $ingresos2020[4]['debe']));
                    $ad2020G = (($gastos2020[5]['naturalezaCuenta'] == 'D') ? ($gastos2020[5]['debe'] - $gastos2020[5]['haber']) : ($gastos2020[5]['haber'] - $gastos2020[5]['debe']));
                }
                $ad2021 = $ad2021I - $ad2021G;
                $ad2020 = $ad2020I - $ad2020G;

                if ($_SESSION[_S]['reportes'] == 'C111') {
                    $pdf->ajusteCuentasAhorro2021($finalPH, $ad2021, $ad2020);
                    $finalPH[$max - 1] = [
                        'cuenta'      => '36999', 'nombre' => 'TOTAL DEL PASIVO Y HACIENDA PÚBLICA/PATRIMONIO',
                        'importe2021' => round($totalPasivo2021 + $totalHacienda2021 + $ad2021),
                        'importe2020' => round($totalPasivo2020 + $totalHacienda2020 + $ad2020),
                        'nivelCuenta' => 5,
                    ]; // Se guardan en la ultima posicion el total del pasivo + hacienda.
                } else {
                    $finalPH[$max - 2] = [
                        'cuenta'      => '35999', 'nombre' => 'Resultados del Ejercicio (Ahorro/Desahorro)',
                        'importe2021' => $ad2021, 'nivelCuenta' => 5,
                    ]; // Se guardan en la ultima posicion el total del pasivo + hacienda.
                    $finalPH[$max - 1] = [
                        'cuenta'      => '36999', 'nombre' => 'TOTAL DEL PASIVO Y HACIENDA PÚBLICA/PATRIMONIO',
                        'importe2021' => $totalPasivo2021 + $totalHacienda2021 + $ad2021,
                        'nivelCuenta' => 5,
                    ];
                }

                $fill = false;
                $altoLinea = $pdf->intLin + 0.5;
                $relleno = false; // Para determinar si se colorea de gris el fondo de la línea

                $pdf->SetFillColor(192, 192, 192);

                $mini_fuente = 4;
                $ancho_concepto = 47;
                $ancho_importe = 20;

                $pdf->SetY($pdf->GetY());

                $yFinal2 = null;
                $yFinEncabezado = $yOriginal = $pdf->GetY();

                for ($x = 0; $x <= $max; $x++) {

                    $nivelA = '';
                    $cuentaA = '';
                    $importeA = '';
                    $importeA2020 = '';
                    $nivelP = '';
                    $cuentaP = '';
                    $importeP = '';
                    $importeP2020 = '';

                    if (isset($finalAct[$x])) {
                        if (substr($finalAct[$x]['cuenta'], $finalAct[$x]['nivelCuenta'] - 1, 1) != 0) {
                            if ($pdf->nivel[$finalAct[$x]['nivelCuenta']] == 1 || in_array($finalAct[$x]['cuenta'], $cuentasTotalesA)) {
                                $nivelA = ((!in_array($finalAct[$x]['cuenta'], $cuentasNoValidas)) ? $finalAct[$x]['cuenta'] : '');
                                $cuentaA = $finalAct[$x]['nombre'];
                                if ($_SESSION[_S]['reportes'] == 'C111') {
                                    $importeA = (($finalAct[$x]['importe2021'] != 0) ? number_format(round(floatval($finalAct[$x]['importe2021']) / $checkMiles, $decimales), $decimales) : (($finalAct[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                    $importeA2020 = (($finalAct[$x]['importe2020'] != 0) ? number_format(round(floatval($finalAct[$x]['importe2020']) / $checkMiles, $decimales), $decimales) : (($finalAct[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                } else {
                                    $importeA = (($finalAct[$x]['importe2021'] != 0) ? number_format($finalAct[$x]['importe2021'] / $checkMiles, $decimales) : (($finalAct[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                    $importeA2020 = (($finalAct[$x]['importe2020'] != 0) ? number_format($finalAct[$x]['importe2020'] / $checkMiles, $decimales) : (($finalAct[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                }
                            }

                            $importeA = (($finalAct[$x]['nivelCuenta'] != 1 && $finalAct[$x]['nivelCuenta'] != 2 && $cuentaA != '') ? $importeA : '');
                            $importeA2020 = (($finalAct[$x]['nivelCuenta'] != 1 && $finalAct[$x]['nivelCuenta'] != 2 && $cuentaA != '') ? $importeA2020 : '');
                        }
                    }

                    if (isset($finalPH[$x])) {
                        if (substr($finalPH[$x]['cuenta'], $finalPH[$x]['nivelCuenta'] - 1, 1) != 0) {
                            if ($pdf->nivel[$finalPH[$x]['nivelCuenta']] == 1 || in_array($finalPH[$x]['cuenta'], $cuentasTotalesP)) {
                                $nivelP = ((!in_array($finalPH[$x]['cuenta'], $cuentasNoValidas)) ? $finalPH[$x]['cuenta'] : '');
                                $cuentaP = $finalPH[$x]['nombre'];
                                if ($_SESSION[_S]['reportes'] == 'C111') {
                                    $importeP = (($finalPH[$x]['importe2021'] != 0) ? number_format(round(floatval($finalPH[$x]['importe2021']) / $checkMiles, $decimales), $decimales) : (($finalPH[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                    $importeP2020 = (($finalPH[$x]['importe2020'] != 0) ? number_format(round(floatval($finalPH[$x]['importe2020']) / $checkMiles, $decimales), $decimales) : (($finalPH[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                } else {
                                    $importeP = (($finalPH[$x]['importe2021'] != 0) ? number_format($finalPH[$x]['importe2021'] / $checkMiles, $decimales) : (($finalPH[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                    $importeP2020 = (($finalPH[$x]['importe2020'] != 0) ? number_format($finalPH[$x]['importe2020'] / $checkMiles, $decimales) : (($finalPH[$x]['cuenta'] != '') ? number_format(0, $decimales) : ''));
                                }
                            }

                            // Para Hacienda Pública/Patrimonio, sí imprimeremos el total en el encabezado del grupo
                            if ($finalPH[$x]['cuenta'] != '31' && $finalPH[$x]['cuenta'] != '32' && $finalPH[$x]['cuenta'] != '33') {
                                $importeP = (($finalPH[$x]['nivelCuenta'] != 1 && $finalPH[$x]['nivelCuenta'] != 2 && $cuentaP != '') ? $importeP : '');
                                $importeP2020 = (($finalPH[$x]['nivelCuenta'] != 1 && $finalPH[$x]['nivelCuenta'] != 2 && $cuentaP != '') ? $importeP2020 : '');
                            }

                        }
                    }

                    if ($cuentaA != '' || $cuentaP != '') {
                        $arrayDeTotalesA = ['TOTAL DE ACTIVOS CIRCULANTES', 'TOTAL DE ACTIVOS NO CIRCULANTES', 'TOTAL DEL ACTIVO'];
                        $arrayCuentasTotalesP = ['TOTAL DE PASIVOS CIRCULANTES', 'TOTAL DE PASIVOS NO CIRCULANTES', 'TOTAL DEL PASIVO', 'TOTAL HACIENDA PÚBLICA/PATRIMONIO', 'TOTAL DEL PASIVO Y HACIENDA PÚBLICA/PATRIMONIO'];
                        if (in_array($cuentaA, $arrayDeTotalesA)) {
                            $finalAct[$x]['nivelCuenta'] = 2;
                        }
                        if (in_array($cuentaP, $arrayCuentasTotalesP)) {
                            $finalPH[$x]['nivelCuenta'] = 2;
                        }
                        switch ($finalAct[$x]['nivelCuenta']) {
                            case 1:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                                break;
                            case 2:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            case 3:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            case 4:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            case 5:
                                if (in_array($finalAct[$x]['cuenta'], $cuentasTotalesA)) {
                                    $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                } else {
                                    $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                }
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            default:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                break;
                        }

                        $anchoTextoCuentaA = $pdf->GetStringWidth(utf8Decode($cuentaA));
                        $numLineasCuentaA = ceil($anchoTextoCuentaA / ($ancho_concepto - ($finalAct[$x]['nivelCuenta'])));

                        switch ($finalPH[$x]['nivelCuenta']) {
                            case 1:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                                break;
                            case 2:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            case 3:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            case 4:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            case 5:
                                if (in_array($finalPH[$x]['cuenta'], $cuentasTotalesP)) {
                                    $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                } else {
                                    $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                }
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            default:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                break;
                        }

                        $anchoTextoCuentaP = $pdf->GetStringWidth(utf8Decode($cuentaP));
                        $numLineasCuentaP = ceil($anchoTextoCuentaP / ($ancho_concepto - ($finalPH[$x]['nivelCuenta'])));

                        switch ($finalAct[$x]['nivelCuenta']) {
                            case 1:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                                break;
                            case 2:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            case 3:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            case 4:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            case 5:
                                if (in_array($finalAct[$x]['cuenta'], $cuentasTotalesA)) {
                                    $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                } else {
                                    $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                }
                                $cuentaA = $pdf->textoConFormatoEnMinusculas($cuentaA);
                                break;
                            default:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                break;
                        }

                        $yOriginal = $pdf->GetY();
                        $yParaCentrarCuentaA = $yOriginal + ((max($numLineasCuentaA, $numLineasCuentaP) - $numLineasCuentaA) * ($altoLinea / 2));
                        $yParaCentrarImportes = $yOriginal + ((max($numLineasCuentaA, $numLineasCuentaP) - 1) * ($altoLinea / 2));
                        $pdf->SetY($yParaCentrarCuentaA);
                        $pdf->SetX(20 + ($finalAct[$x]['nivelCuenta']));
                        $pdf->MultiCell($ancho_concepto - ($finalAct[$x]['nivelCuenta']), $altoLinea, utf8Decode($cuentaA), 0, 'L', $fill);
                        $yFinal1 = $pdf->GetY();
                        $pdf->SetXY($pdf->GetX() + $ancho_concepto, $yParaCentrarImportes);
                        $pdf->Cell($ancho_importe, $altoLinea, $importeA, 0, 0, 'R', $fill);
                        $pdf->Cell($ancho_importe, $altoLinea, $importeA2020, 0, 0, 'R', $fill);

                        switch ($finalPH[$x]['nivelCuenta']) {
                            case 1:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                                break;
                            case 2:
                                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            case 3:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            case 4:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            case 5:
                                if (in_array($finalPH[$x]['cuenta'], $cuentasTotalesP)) {
                                    $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                                } else {
                                    $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                }
                                $cuentaP = $pdf->textoConFormatoEnMinusculas($cuentaP);
                                break;
                            default:
                                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                                break;
                        }

                        $yParaCentrarCuentaP = $yOriginal + ((max($numLineasCuentaA, $numLineasCuentaP) - $numLineasCuentaP) * ($altoLinea / 2));
                        $pdf->SetY($yParaCentrarCuentaP);
                        $pdf->SetX(107 + ($finalPH[$x]['nivelCuenta']));
                        $pdf->MultiCell($ancho_concepto - ($finalPH[$x]['nivelCuenta']), $altoLinea, utf8Decode($cuentaP), 0, 'L', $fill);
                        $yFinal2 = $pdf->GetY();
                        $pdf->SetXY(154, $yParaCentrarImportes);
                        $pdf->Cell($ancho_importe, $altoLinea, $importeP, 0, 0, 'R', $fill);
                        $pdf->Cell($ancho_importe, $altoLinea, $importeP2020, 0, 0, 'R', $fill);
                        $pdf->SetY(max($yFinal1, $yFinal2));
                    }
                    if ($pdf->GetY() > 240) {
                        $pdf->Line(20, $yFinEncabezado, 20, $pdf->getY());
                        $pdf->Line(67, $yFinEncabezado, 67, $pdf->getY());
                        $pdf->Line(87, $yFinEncabezado, 87, $pdf->getY());
                        $pdf->Line(107, $yFinEncabezado, 107, $pdf->getY());
                        $pdf->Line(154, $yFinEncabezado, 154, $pdf->getY());
                        $pdf->Line(174, $yFinEncabezado, 174, $pdf->getY());
                        $pdf->Line(194, $yFinEncabezado, 194, $pdf->getY());
                        $pdf->Line(20, $pdf->getY(), 194, $pdf->getY());
                        $pdf->encabezadoTabla = true;
                        $pdf->addPage();
                    } elseif ($x == $max) {
                        $pdf->Line(20, $yFinEncabezado, 20, $pdf->getY());
                        $pdf->Line(67, $yFinEncabezado, 67, $pdf->getY());
                        $pdf->Line(87, $yFinEncabezado, 87, $pdf->getY());
                        $pdf->Line(107, $yFinEncabezado, 107, $pdf->getY());
                        $pdf->Line(154, $yFinEncabezado, 154, $pdf->getY());
                        $pdf->Line(174, $yFinEncabezado, 174, $pdf->getY());
                        $pdf->Line(194, $yFinEncabezado, 194, $pdf->getY());
                        $pdf->Line(20, $pdf->getY(), 194, $pdf->getY());
                        $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                        $pdf->SetX(19);
                        ($checkCuentaOrden == 1 || $cerosOrden == 1) ? '' : $pdf->MultiCell(174, 5, 'Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.', 0, 'L', false);


                    }
                }

                if ($checkCuentaOrden == 1) {
                    $pdf->Ln();
                    $pdf->SetFont('Montserrat-b', '', 6);
                    $espacio = '            ';
                    $espacio = '';
                    $pdf->setX(20);

                    $pdf->Cell(115, $altoLinea, $espacio . 'CUENTAS DE ORDEN', 0, 0, 'L', $fill);
                    $pdf->Ln();
                    $nivelesOrden[1] = 0;
                    $nivelesOrden[2] = 0;
                    $nivelesOrden[3] = 0;
                    $nivelesOrden[4] = 0;
                    $nivelesOrden[5] = 1;
                    $ctasOrden = array_merge($pdf->devuelveArrayOrdenado($ctasOrden7, $nivelesOrden), $pdf->devuelveArrayOrdenado($ctasOrden8, $nivelesOrden));

                    foreach ($ctasOrden as $cuentas) {
                        $pdf->SetFont('Montserrat', '', 6);
                        $pdf->setX(25);
                        if ($cuentas['nivel'] == 5) {
                            $cuentaOrden = $cuentas['cgsc_cuenta'];
                            if ($cuentaOrden != '8.1.1.0.0') {
                                $pdf->Cell(110, $altoLinea, utf8Decode($espacio . $cuentas['cgsc_nombre']), 0, 0, 'L', $fill);

                                $importeCta0rden = (($cuentas['importe'] != 0) ? number_format($cuentas['importe'] / $checkMiles, 2) : (($cuentas['importe'] != '') ? number_format(0, 0) : '0'));

                                //$pdf->Cell(110, $altoLinea, utf8Decode($espacio . $cuentas['cgsc_nombre']), 0, 0, 'L', $fill);
                                $pdf->Cell(60, $altoLinea, $espacio . ($importeCta0rden), 0, 0, 'R', $fill);
                                $pdf->Ln();
                            }
                        }

                        if ($pdf->getY() > 243) {
                            $pdf->encabezadoTabla = false;
                            $pdf->addPage();
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                    $pdf->MultiCell(174, 5, 'Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.', 'T', 'L', false);

                } elseif ($cerosOrden) {
                    $pdf->Ln();
                    $pdf->SetFont('Montserrat-b', '', 6);
                    $espacio = '            ';
                    $espacio = '';
                    $pdf->setX(20);

                    $pdf->Cell(115, $altoLinea, $espacio . 'CUENTAS DE ORDEN SIN IMPORTE', 0, 0, 'L', $fill);
                    $pdf->Ln();
                    $nivelesOrden[1] = 0;
                    $nivelesOrden[2] = 0;
                    $nivelesOrden[3] = 0;
                    $nivelesOrden[4] = 0;
                    $nivelesOrden[5] = 1;
                    $ctasOrden = array_merge($pdf->devuelveArrayOrdenado($ctasOrden7, $nivelesOrden), $pdf->devuelveArrayOrdenado($ctasOrden8, $nivelesOrden));

                    foreach ($ctasOrden as $cuentas) {
                        $pdf->SetFont('Montserrat', '', 6);
                        $pdf->setX(25);
                        if ($cuentas['nivel'] == 5) {
                            $cuentaOrden = $cuentas['cgsc_cuenta'];
                            if ($cuentaOrden != '8.1.1.0.0') {
                                $pdf->Cell(110, $altoLinea, utf8Decode($espacio . $cuentas['cgsc_nombre']), 0, 0, 'L', $fill);
                                $pdf->Ln();
                            }
                        }

                        if ($pdf->getY() > 243) {
                            $pdf->encabezadoTabla = false;
                            $pdf->addPage();
                        }
                    }
                    $pdf->Ln();
                    $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                    $pdf->MultiCell(174, 5, 'Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.', 'T', 'L', false);
                }
                // * final
                break;
            case 2: // Estado de Actividades
                $mesDesde = (isset($_POST['comboMes']) != '') ? $_POST['comboMes'] : -1;
                $mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
                $tipoReporte = (isset($_POST['tipoReporte']) != '') ? $_POST['tipoReporte'] : 1;
                $MilPesos = (isset($_POST['MilPes']) != '') ? 1000 : 1;
                $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
                $excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

                if (!($pdf->consolidado == 1)) {
                    $desde = $pdf->mesDesde;
                    $hasta = $pdf->mesHasta;
                } else {
                    $desde = $mesHasta + 1;
                    $hasta = -1;
                }

                list($ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($ingresos2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($gastos2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                //cuerpo de la tabla
                $pdf->setOrientation('P');
                $pdf->AliasNbPages();
                $pdf->AddPage();

                $pdf->AddFont('univiapro', '');
                $pdf->AddFont('univiapro', 'B');

                $pdf->SetFont('univiapro', '', 9);
                $pdf->SetTitle('Edo_Actividades');
                $pdf->SetWidths([115, 30, 30]);
                $pdf->SetAligns(['L', 'R', 'R']);

                $cuentasTotalesI = [43998];
                $cuentasTotalesG = [57998, 57999, 58000];

                $cuentaIn = '';
                $nivelIn = '';
                $importeIn = 0;
                $cuentaGa = '';
                $nivelGa = '';
                $importeGa = 0;
                $totalIn = 0;
                $totalGa = 0;

                $ing = []; //array para informacion de ingresos  2020
                $ga = []; // array para informacion de gastos 2020.

                $ing2 = []; //array para informacion de ingresos 2018
                $ga2 = []; // array para informacion de gastos 2018.
                if ($ingresos || $gastos) {
                    foreach ($ingresos as $key => $valor) {
                        if ($valor['nivel'] >= 1) {
                            $cuentaIn = $valor['cuenta'];
                            $nivelIn = utf8Decode($valor['nombre']);
                            if ($valor['naturalezaCuenta'] == 'D') {
                                $importeIn = ($valor['debe'] - $valor['haber']) / $MilPesos;
                            } else {
                                $importeIn = ($valor['haber'] - $valor['debe']) / $MilPesos;
                            }
                        } else {
                            $cuentaIn = $valor['cuenta'];
                            $nivelIn = utf8Decode($valor['nombre']);
                        }
                        if ($valor['nivel'] == 1) {
                            $totalIn = $totalIn + $importeIn;
                            $ing[$key]['cuenta'] = $cuentaIn;
                            $ing[$key]['nivel'] = $nivelIn;
                            $ing[$key]['importe'] = $importeIn;
                            $ing[$key]['nivelCuenta'] = $valor['nivel'];
                        } else {
                            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesI)) {
                                $ing[$key]['cuenta'] = $cuentaIn;
                                $ing[$key]['nivel'] = $nivelIn;
                                $ing[$key]['importe'] = $importeIn;
                                $ing[$key]['nivelCuenta'] = $valor['nivel'];
                            }
                        }
                    }
                    $ing[43998]['cuenta'] = 43998;
                    $ing[43998]['nivel'] = 'Total de Ingresos y Otros Beneficios';
                    $ing[43998]['importe'] = $totalIn;
                    $ing[43998]['nivelCuenta'] = 5;

                    foreach ($gastos as $key => $valor) {
                        if ($valor['nivel'] >= 1) {
                            $cuentaGa = $valor['cuenta'];
                            $nivelGa = utf8Decode($valor['nombre']);

                            if ($valor['naturalezaCuenta'] == 'D') {
                                $importeGa = ($valor['debe'] - $valor['haber']) / $MilPesos;
                            } else {
                                $importeGa = ($valor['haber'] - $valor['debe']) / $MilPesos;
                            }
                        } else {
                            $cuentaGa = $valor['cuenta'];
                            $nivelGa = utf8Decode($valor['nombre']);
                        }
                        if ($valor['nivel'] == 1) {
                            $totalGa = $totalGa + $importeGa;
                            $ga[$key]['cuenta'] = $cuentaGa;
                            $ga[$key]['nivel'] = $nivelGa;
                            $ga[$key]['importe'] = $importeGa;
                            $ga[$key]['nivelCuenta'] = $valor['nivel'];
                        } else {
                            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesG)) {
                                $ga[$key]['cuenta'] = $cuentaGa;
                                $ga[$key]['nivel'] = $nivelGa;
                                $ga[$key]['importe'] = $importeGa;
                                $ga[$key]['nivelCuenta'] = $valor['nivel'];
                            }
                        }
                    }

                    $ga[57998]['cuenta'] = 57998;
                    $ga[57998]['nivel'] = utf8Decode('Total de Gastos y Otras Pérdidas');
                    $ga[57998]['importe'] = $totalGa;
                    $ga[57998]['nivelCuenta'] = 5;

                    $ga[57999]['cuenta'] = 57999;
                    $ga[57999]['nivel'] = 'Espacio';
                    $ga[57999]['importe'] = '';
                    $ga[57999]['nivelCuenta'] = 5;

                    // Definición en el ticket #291730, en el comentario del 05/03/2024 6:27 pm
                    $ga[58000]['cuenta'] = 58000;
                    $ga[58000]['nivel'] = 'Resultado del Ejercicio (Ahorro/Desahorro)';
                    $ga[58000]['importe'] = $totalIn - $totalGa;
                    $ga[58000]['nivelCuenta'] = 5;
                }

                $cuentaIn = '';
                $nivelIn = '';
                $importeIn = 0;
                $cuentaGa = '';
                $nivelGa = '';
                $importeGa = 0;
                $totalIn = 0;
                $totalGa = 0;

                if ($ingresos2020 || $gastos2020) {
                    foreach ($ingresos2020 as $key => $valor) {
                        if ($valor['nivel'] >= 1) {
                            $cuentaIn = $valor['cuenta'];
                            $nivelIn = utf8Decode($valor['nombre']);
                            if ($valor['naturalezaCuenta'] == 'D') {
                                $importeIn = ($valor['debe'] - $valor['haber']) / $MilPesos;
                            } else {
                                $importeIn = ($valor['haber'] - $valor['debe']) / $MilPesos;
                            }
                        } else {
                            $cuentaIn = $valor['cuenta'];
                            $nivelIn = utf8Decode($valor['nombre']);
                        }
                        if ($valor['nivel'] == 1) {
                            $totalIn = $totalIn + $importeIn;
                            $ing2[$key]['cuenta'] = $cuentaIn;
                            $ing2[$key]['nivel'] = $nivelIn;
                            $ing2[$key]['importe'] = $importeIn;
                            $ing2[$key]['nivelCuenta'] = $valor['nivel'];
                        } else {
                            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesI)) {
                                $ing2[$key]['cuenta'] = $cuentaIn;
                                $ing2[$key]['nivel'] = $nivelIn;
                                $ing2[$key]['importe'] = $importeIn;
                                $ing2[$key]['nivelCuenta'] = $valor['nivel'];
                            }
                        }
                    }
                    $ing2[43998]['cuenta'] = 43998;
                    $ing2[43998]['nivel'] = 'Total de Ingresos y Otros Beneficios';
                    $ing2[43998]['importe'] = $totalIn;
                    $ing2[43998]['nivelCuenta'] = 5;

                    foreach ($gastos2020 as $key => $valor) {
                        if ($valor['nivel'] >= 1) {
                            $cuentaGa = $valor['cuenta'];
                            $nivelGa = utf8Decode($valor['nombre']);

                            if ($valor['naturalezaCuenta'] == 'D') {
                                $importeGa = ($valor['debe'] - $valor['haber']) / $MilPesos;
                            } else {
                                $importeGa = ($valor['haber'] - $valor['debe']) / $MilPesos;
                            }
                        } else {
                            $cuentaGa = $valor['cuenta'];
                            $nivelGa = utf8Decode($valor['nombre']);
                        }
                        if ($valor['nivel'] == 1) {
                            $totalGa = $totalGa + $importeGa;
                            $ga2[$key]['cuenta'] = $cuentaGa;
                            $ga2[$key]['nivel'] = $nivelGa;
                            $ga2[$key]['importe'] = $importeGa;
                            $ga2[$key]['nivelCuenta'] = $valor['nivel'];
                        } else {
                            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesG)) {
                                $ga2[$key]['cuenta'] = $cuentaGa;
                                $ga2[$key]['nivel'] = $nivelGa;
                                $ga2[$key]['importe'] = $importeGa;
                                $ga2[$key]['nivelCuenta'] = $valor['nivel'];
                            }
                        }
                    }

                    $ga2[57998]['cuenta'] = 57998;
                    $ga2[57998]['nivel'] = utf8Decode('Total de Gastos y Otras Pérdidas');
                    $ga2[57998]['importe'] = $totalGa;
                    $ga2[57998]['nivelCuenta'] = 5;

                    $ga2[57999]['cuenta'] = 57999;
                    $ga2[57999]['nivel'] = 'Espacio';
                    $ga2[57999]['importe'] = '';
                    $ga2[57999]['nivelCuenta'] = 5;

                    // Definición en el ticket #291730, en el comentario del 05/03/2024 6:27 pm
                    $ga2[58000]['cuenta'] = 58000;
                    $ga2[58000]['nivel'] = 'Resultado del Ejercicio (Ahorro/Desahorro)';
                    $ga2[58000]['importe'] = $totalIn - $totalGa;
                    $ga2[58000]['nivelCuenta'] = 5;
                }

                $keyIngresos2021 = key($ing);
                $keyIngresos2020 = key($ing2);
                $finalIng = [];

                while ($keyIngresos2021 || $keyIngresos2020) {

                    if (CompararCadenas((string) $keyIngresos2021, (string) $keyIngresos2020) == 0) {
                        $finalIng[$keyIngresos2021]['cuenta'] = $ing[$keyIngresos2021]['cuenta'];
                        $finalIng[$keyIngresos2021]['nivel'] = $ing[$keyIngresos2021]['nivel'];
                        $finalIng[$keyIngresos2021]['nivelCuenta'] = $ing[$keyIngresos2021]['nivelCuenta'];
                        $finalIng[$keyIngresos2021]['importe2021'] = $ing[$keyIngresos2021]['importe'];
                        $finalIng[$keyIngresos2021]['importe2020'] = $ing2[$keyIngresos2020]['importe'];

                        next($ing);
                        $keyIngresos2021 = key($ing);

                        next($ing2);
                        $keyIngresos2020 = key($ing2);
                    } elseif (!$keyIngresos2020 || CompararCadenas((string) $keyIngresos2021, (string) $keyIngresos2020) < 0) { //cuentas anteriores
                        $finalIng[$keyIngresos2021]['cuenta'] = $ing[$keyIngresos2021]['cuenta'];
                        $finalIng[$keyIngresos2021]['nivel'] = $ing[$keyIngresos2021]['nivel'];
                        $finalIng[$keyIngresos2021]['nivelCuenta'] = $ing[$keyIngresos2021]['nivelCuenta'];
                        $finalIng[$keyIngresos2021]['importe2021'] = $ing[$keyIngresos2021]['importe'];
                        $finalIng[$keyIngresos2021]['importe2020'] = (isset($finalIng[$keyIngresos2021]['importe2020']) ? $finalIng[$keyIngresos2021]['importe2020'] : 0.00);

                        next($ing);
                        $keyIngresos2021 = key($ing);
                    } else {
                        $finalIng[$keyIngresos2020]['cuenta'] = $ing2[$keyIngresos2020]['cuenta'];
                        $finalIng[$keyIngresos2020]['nivel'] = $ing2[$keyIngresos2020]['nivel'];
                        $finalIng[$keyIngresos2020]['nivelCuenta'] = $ing2[$keyIngresos2020]['nivelCuenta'];
                        $finalIng[$keyIngresos2020]['importe2021'] = (isset($finalIng[$keyIngresos2020]['importe2021']) ? $finalIng[$keyIngresos2020]['importe2021'] : 0.00);
                        $finalIng[$keyIngresos2020]['importe2020'] = $ing2[$keyIngresos2020]['importe'];

                        next($ing2);
                        $keyIngresos2020 = key($ing2);
                    }
                }

                $keyGastos2021 = key($ga);
                $keyGastos2020 = key($ga2);
                $finalGA = [];

                while ($keyGastos2021 || $keyGastos2020) {
                    if (CompararCadenas((string) $keyGastos2021, (string) $keyGastos2020) == 0) {
                        $finalGA[$keyGastos2021]['cuenta'] = $ga[$keyGastos2021]['cuenta'];
                        $finalGA[$keyGastos2021]['nivel'] = $ga[$keyGastos2021]['nivel'];
                        $finalGA[$keyGastos2021]['nivelCuenta'] = $ga[$keyGastos2021]['nivelCuenta'];
                        $finalGA[$keyGastos2021]['importe2021'] = $ga[$keyGastos2021]['importe'];
                        $finalGA[$keyGastos2021]['importe2020'] = $ga2[$keyGastos2020]['importe'];

                        next($ga);
                        $keyGastos2021 = key($ga);

                        next($ga2);
                        $keyGastos2020 = key($ga2);
                    } elseif (!$keyGastos2020 || CompararCadenas((string) $keyGastos2021, (string) $keyGastos2020) < 0) { //cuentas anteriores
                        $finalGA[$keyGastos2021]['cuenta'] = $ga[$keyGastos2021]['cuenta'];
                        $finalGA[$keyGastos2021]['nivel'] = $ga[$keyGastos2021]['nivel'];
                        $finalGA[$keyGastos2021]['nivelCuenta'] = $ga[$keyGastos2021]['nivelCuenta'];
                        $finalGA[$keyGastos2021]['importe2021'] = $ga[$keyGastos2021]['importe'];

                        if (!isset($finalGA[$keyGastos2021]['importe2020'])) {
                            $finalGA[$keyGastos2021]['importe2020'] = 0.00;
                        }
                        next($ga);
                        $keyGastos2021 = key($ga);
                    } else {
                        $finalGA[$keyGastos2020]['cuenta'] = $ga2[$keyGastos2020]['cuenta'];
                        $finalGA[$keyGastos2020]['nivel'] = $ga2[$keyGastos2020]['nivel'];
                        $finalGA[$keyGastos2020]['nivelCuenta'] = $ga2[$keyGastos2020]['nivelCuenta'];
                        $finalGA[$keyGastos2020]['importe2021'] = (isset($finalGA[$keyGastos2020]['importe2021']) ? $finalGA[$keyGastos2020]['importe2021'] : 0.00);
                        $finalGA[$keyGastos2020]['importe2020'] = $ga2[$keyGastos2020]['importe'];

                        next($ga2);
                        $keyGastos2020 = key($ga2);
                    }
                }


                /*****ORDEN************************** */
                $sortIng = [];
                $sortIng['indice'] = [];
                foreach ($finalIng as $indice => $val) {
                    $sortIng['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
                }
                array_multisort($sortIng['indice'], SORT_ASC, $finalIng);

                $sortGa = [];
                $sortGa['indice'] = [];
                foreach ($finalGA as $indice => $val) {
                    $sortGa['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
                }
                array_multisort($sortGa['indice'], SORT_ASC, $finalGA);
                /*                 * ****************************fin orden *********************************** */

                $in = array_merge($finalIng); //ingresos 2020
                $g = array_merge($finalGA); //gastos 2020
                $max = max(count($in), count($g));
                $max = $max += 2; // incrementamos a 2 el valor de max para pintar los totales globales.

                if (count($in) < $max) {
                    $ini = count($in);
                    for ($i = $ini; $i < $max; $i++) {
                        $in[$i] = [
                            'cuenta'      => '', 'nivel' => '', 'nivelCuenta' => 5,
                            'importe2021' => '', 'importe2020' => '',
                        ]; // se rellenan con arreglos vacios, esto para igualar los arreglos.
                    }
                }
                // si gastos tiene menos elementos que el activo, lo igualamos
                if (count($g) < $max) {
                    $ini2 = count($g);
                    for ($i = $ini2; $i < $max; $i++) {
                        $g[$i] = [
                            'cuenta'      => '', 'nivel' => '', 'nivelCuenta' => 5,
                            'importe2021' => '', 'importe2020' => '',
                        ]; // se rellenan con arreglos vacios, esto para igualar los arreglos.
                    }
                }

                $relleno = false; // Para determinar si se colorea de gris el fondo de la línea
                $altoLinea = $pdf->intLin;
                $fill = false;

                for ($x = 0; $x < $max; $x++) {
                    $nivelI = '';
                    $cuentaI = '';
                    $importeIngresos2021 = '';
                    $importeIngresos2020 = '';
                    $nivelG = '';
                    $cuentaG = '';
                    $importeG = '';

                    if (isset($in[$x])) {

                        if ($pdf->nivel[$in[$x]['nivelCuenta']] == 1 || in_array($in[$x]['cuenta'], $cuentasTotalesI)) {
                            $nivelI = $in[$x]['cuenta'];
                            if ($in[$x]['nivelCuenta'] == 1) {
                                $cuentaI = $in[$x]['nivel'];
                            } else {
                                $cuentaI = UrNombre::conceptosPlan($in[$x]['nivel']);
                            }
                            if ($_SESSION[_S]['reportes'] == 'C112') {
                                $importeIngresos2021 = (($in[$x]['importe2021'] != '') ? number_format(round($in[$x]['importe2021'], 2), 0) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                                $importeIngresos2020 = (($in[$x]['importe2020'] != '') ? number_format(round($in[$x]['importe2020'], 2), 0) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                            } else {
                                $importeIngresos2021 = (($in[$x]['importe2021'] != '') ? number_format($in[$x]['importe2021'], 2) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                                $importeIngresos2020 = (($in[$x]['importe2020'] != '') ? number_format($in[$x]['importe2020'], 2) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                            }
                        }

                        $importeIngresos2021 = (($in[$x]['nivelCuenta'] != 1 && $cuentaI != '') ? $importeIngresos2021 : '');
                        $importeIngresos2020 = (($in[$x]['nivelCuenta'] != 1 && $cuentaI != '') ? $importeIngresos2020 : '');
                    }

                    if ($cuentaI != '' || $cuentaG != '') {
                        if ($_SESSION[_S]['reportes'] == 'C112') {
                            $pdf->longitud2 = 110;
                        }

                        $espacio = '';
                        switch ($in[$x]['nivelCuenta']) {
                            case 1:
                                $pdf->SetFont('univiapro', 'B', 6);
                                break;
                            case 2:
                                $pdf->SetFont('univiapro', 'B', 5.4);
                                $espacio = '   ';
                                break;
                            case 3:
                                $pdf->SetFont('univiapro', '', 6);
                                $espacio = '      ';
                                break;
                            case 4:
                                $pdf->SetFont('univiapro', '', 6);
                                $espacio = '         ';
                                break;
                            case 5:
                                if (in_array($in[$x]['cuenta'], $cuentasTotalesI)) {
                                    $pdf->SetFont('univiapro', 'B', 6);
                                }
                                break;
                            default:
                                $pdf->SetFont('univiapro', '', 6);
                                break;
                        }

                        $pdf->SetFillColor(192, 192, 192);

                        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
                        // independientemente de la configuración que traiga
                        // VALIDAMOS LA PARTE DE INGRESOS
                        if (trim($cuentaI) != '') {
                            $fill = $relleno;
                        } else {
                            $fill = false;
                        }
                        $pdf->setX(20);

                        $pdf->Cell(115, $altoLinea, $espacio . substr($cuentaI, 0, $pdf->longitud2), 'LR', 0, 'L', $fill);
                        $pdf->Cell(30, $altoLinea, $importeIngresos2021, 'LR', 0, 'R', $fill);
                        $pdf->Cell(30, $altoLinea, $importeIngresos2020, 'LR', 1, 'R', $fill);

                        switch ($g[$x]['nivelCuenta']) {
                            case 1:
                                $pdf->SetFont('univiapro', 'B', 6);
                                break;
                            case 2:
                                $pdf->SetFont('univiapro', 'B', 5.4);
                                $espacio = '   ';
                                break;
                            case 3:
                                $pdf->SetFont('univiapro', '', 6);
                                $espacio = '      ';
                                break;
                            case 4:
                                $pdf->SetFont('univiapro', '', 6);
                                $espacio = '         ';
                                break;
                            case 5:
                                if (in_array($g[$x]['cuenta'], $cuentasTotalesG)) {
                                    $pdf->SetFont('univiapro', 'B', 6);
                                }
                                break;
                            default:
                                $pdf->SetFont('univiapro', '', 6);
                                break;
                        }

                        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
                        // independientemente de la configuración que traiga
                        // VALIDAMOS LA PARTE DE GASTOS
                        if (trim($cuentaG) != '') {
                            $fill = $relleno;
                        } else {
                            $fill = false;
                        }

                        $con2 = $con = 1;
                        if (strlen($cuentaI) > $pdf->longitud2 * $con2 || strlen($cuentaG) > $pdf->longitud2 * $con) {
                            switch ($in[$x]['nivelCuenta']) {
                                case 1:
                                    $pdf->SetFont('univiapro', 'B', 6);
                                    break;
                                case 2:
                                    $pdf->SetFont('univiapro', 'B', 5.4);
                                    $espacio = '   ';
                                    break;
                                case 3:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '      ';
                                    break;
                                case 4:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '         ';
                                    break;
                                case 5:
                                    if (in_array(
                                        $in[$x]['cuenta'],
                                        $cuentasTotalesI
                                    )) {
                                        $pdf->SetFont('univiapro', 'B', 6);
                                    }
                                    $espacio = '            ';
                                    break;
                                default:
                                    $pdf->SetFont('univiapro', '', 6);
                                    break;
                            }

                            $pdf->Cell(115, $altoLinea, $espacio . substr($cuentaI, $pdf->longitud2 * $con2, $pdf->longitud2), 'LR', 0, 'L', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'R', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'R', $fill);

                            switch ($g[$x]['nivelCuenta']) {
                                case 1:
                                    $pdf->SetFont('univiapro', 'B', 6);
                                    break;
                                case 2:
                                    $pdf->SetFont('univiapro', 'B', 5.4);
                                    $espacio = '   ';
                                    break;
                                case 3:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '      ';
                                    break;
                                case 4:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '         ';
                                    break;
                                case 5:
                                    if (in_array(
                                        $g[$x]['cuenta'],
                                        $cuentasTotalesG
                                    )) {
                                        $pdf->SetFont('univiapro', 'B', 6);
                                    }
                                    $espacio = '            ';
                                    break;
                                default:
                                    $pdf->SetFont('univiapro', '', 6);
                                    break;
                            }

                            $pdf->Cell(115, $altoLinea, $espacio . substr($cuentaG, $pdf->longitud2 * $con, $pdf->longitud2), 'LR', 0, 'L', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'R', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 1, 'C', $fill);

                            $con2++;
                            $con++;
                        }
                    }

                    $relleno = ($pdf->incCta == '') ? false : !$relleno; // Para determinar si se colorea de gris el fondo de la línea
                }

                $pdf->Cell(115, $altoLinea, '', 'LR', 0, 'L', false);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', false);
                $pdf->Cell(30, $altoLinea, '', 'LR', 1, 'L', false);

                for ($x = 0; $x < $max; $x++) {
                    $nivelI = '';
                    $cuentaI = '';
                    $nivelG = '';
                    $cuentaG = '';
                    $importeG = '';
                    $importeGastos2021 = '';
                    $importeGastos2020 = '';

                    if (isset($g[$x])) {
                        if ($pdf->nivel[$g[$x]['nivelCuenta']] == 1 || in_array($g[$x]['cuenta'], $cuentasTotalesG)) {
                            $nivelG = $g[$x]['cuenta'];

                            if ($g[$x]['nivelCuenta'] == 1) {
                                $cuentaG = $g[$x]['nivel'];
                            } else {
                                $cuentaG = UrNombre::conceptosPlan($g[$x]['nivel']);
                            }
                            if ($_SESSION[_S]['reportes'] == 'C112') {
                                $importeGastos2021 = (($g[$x]['importe2021'] != '') ? number_format(round($g[$x]['importe2021'], 0), 0) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                                $importeGastos2020 = (($g[$x]['importe2020'] != '') ? number_format(round($g[$x]['importe2020'], 0), 0) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                            } else {
                                $importeGastos2021 = (($g[$x]['importe2021'] != '') ? number_format($g[$x]['importe2021'], 2) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                                $importeGastos2020 = (($g[$x]['importe2020'] != '') ? number_format($g[$x]['importe2020'], 2) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                            }
                        }
                        $importeGastos2021 = (($g[$x]['nivelCuenta'] != 1 && $cuentaG != '') ? $importeGastos2021 : '');
                        $importeGastos2020 = (($g[$x]['nivelCuenta'] != 1 && $cuentaG != '') ? $importeGastos2020 : '');
                    }

                    if ($cuentaI != '' || $cuentaG != '') {
                        if ($_SESSION[_S]['reportes'] == 'C112') {
                            $pdf->longitud2 = 85;
                        }

                        $espacio = '';

                        $pdf->SetFillColor(192, 192, 192);

                        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
                        // independientemente de la configuración que traiga
                        // VALIDAMOS LA PARTE DE INGRESOS
                        if (trim($cuentaI) != '') {
                            $fill = $relleno;
                        } else {
                            $fill = false;
                        }

                        $pdf->setX(20);

                        switch ($g[$x]['nivelCuenta']) {
                            case 1:
                                $pdf->SetFont('univiapro', 'B', 6);
                                break;
                            case 2:
                                $pdf->SetFont('univiapro', 'B', 5.4);
                                $espacio = '   ';
                                break;
                            case 3:
                                $pdf->SetFont('univiapro', '', 6);
                                $espacio = '      ';
                                break;
                            case 4:
                                $pdf->SetFont('univiapro', '', 6);
                                $espacio = '         ';
                                break;
                            case 5:
                                if (in_array($g[$x]['cuenta'], $cuentasTotalesG)) {
                                    $pdf->SetFont('univiapro', 'B', 6);
                                }
                                break;
                            default:
                                $pdf->SetFont('univiapro', '', 6);
                                break;
                        }

                        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
                        // independientemente de la configuración que traiga
                        // VALIDAMOS LA PARTE DE GASTOS
                        if (trim($cuentaG) != '') {
                            $fill = $relleno;
                        } else {
                            $fill = false;
                        }

                        if ($cuentaG != 'Espacio') {
                            $pdf->Cell(115, $altoLinea, $espacio . substr($cuentaG, 0, $pdf->longitud2), 'LR', 0, 'L', $fill);
                            $pdf->Cell(30, $altoLinea, $importeGastos2021, 'LR', 0, 'R', $fill);
                            $pdf->Cell(30, $altoLinea, $importeGastos2020, 'LR', 1, 'R', $fill);
                        } else {
                            $pdf->Cell(115, $altoLinea, $espacio, 'LR', 0, 'L', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'R', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 1, 'R', $fill);
                        }

                        $con2 = $con = 1;
                        if (strlen($cuentaI) > $pdf->longitud2 * $con2 || strlen($cuentaG) > $pdf->longitud2 * $con) {
                            switch ($in[$x]['nivelCuenta']) {
                                case 1:
                                    $pdf->SetFont('univiapro', 'B', 6);
                                    break;
                                case 2:
                                    $pdf->SetFont('univiapro', 'B', 5.4);
                                    $espacio = '   ';
                                    break;
                                case 3:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '      ';
                                    break;
                                case 4:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '         ';
                                    break;
                                case 5:
                                    if (in_array(
                                        $in[$x]['cuenta'],
                                        $cuentasTotalesI
                                    )) {
                                        $pdf->SetFont('univiapro', 'B', 6);
                                    }
                                    break;
                                default:
                                    $pdf->SetFont('univiapro', '', 6);
                                    break;
                            }

                            switch ($g[$x]['nivelCuenta']) {
                                case 1:
                                    $pdf->SetFont('univiapro', 'B', 6);
                                    break;
                                case 2:
                                    $pdf->SetFont('univiapro', 'B', 5.4);
                                    $espacio = '   ';
                                    break;
                                case 3:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '      ';
                                    break;
                                case 4:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '         ';
                                    break;
                                case 5:
                                    if (in_array(
                                        $g[$x]['cuenta'],
                                        $cuentasTotalesG
                                    )) {
                                        $pdf->SetFont('univiapro', 'B', 6);
                                    }
                                    break;
                                default:
                                    $pdf->SetFont('univiapro', '', 6);
                                    break;
                            }

                            $pdf->Cell(115, $altoLinea, $espacio . substr($cuentaG, $pdf->longitud2 * $con, $pdf->longitud2), 'LR', 0, 'L', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'R', $fill);
                            $pdf->Cell(30, $altoLinea, '', 'LR', 1, 'C', $fill);

                            $con2++;
                            $con++;
                        }
                    }

                    $relleno = ($pdf->incCta == '') ? false : !$relleno; // Para determinar si se colorea de gris el fondo de la línea
                }
                $pdf->cell(175, 0, '', 'B', 0, 'C', $fill);
                $pdf->Ln(2);
                $pdf->SetFont('univiapro', 'B', 6);
                $pdf->Cell(178, 2, utf8Decode('Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.'), 0, 0, 'L', $fill);
                // NOTA: Cambio en la orientación de la página solicitado en el Ticket #364716
                if ($pdf->getY() > 190) {
                    //$pdf->addPage();
                }
                break;

            case 3: // Estado de Variación de la Hacienda
                $mesDesde = (isset($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1;
                $mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
                $tipoReporte = (isset($_POST['tipoReporte']) != '') ? $_POST['tipoReporte'] : '';
                $MilPesos = (isset($_POST['MilPes']) != '') ? 1000 : 1;
                $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
                $excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                $cc = isset($_POST['cc']) && $_POST['cc'] == 1 ? $_POST['cc'] : '';
                $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

                list($vhApeDesde2021) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 3, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($vhDesdeHasta2021) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, 3, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                list($vhApeDesde2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 3, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($vhDesdeHasta2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, 3, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                //cuerpo de la tabla
                $pdf->setOrientation('L');
                $pdf->AddFont('univiapro', '');
                $pdf->AddFont('univiapro', 'B');
                $pdf->SetFont('univiapro', '', 7);
                $pdf->AliasNbPages();
                $pdf->AddPage();
                if ($cc != '') {
                    $pdf->SetWidths([20, 50, 32, 32, 32, 40, 32]);
                    $pdf->SetAligns(['L', 'L', 'R', 'R', 'R', 'R', 'R']);
                } else {
                    $pdf->SetWidths([50, 37, 37, 37, 40, 37]);
                    $pdf->SetAligns(['L', 'R', 'R', 'R', 'R', 'R']);
                }

                $keyApeDesde2021 = key($vhApeDesde2021);
                $keyDesdeHasta2021 = key($vhDesdeHasta2021);
                $final2021 = [];

                while ($keyApeDesde2021 || $keyDesdeHasta2021) {
                    if (CompararCadenas((string) $keyApeDesde2021, (string) $keyDesdeHasta2021) == 0) {
                        $final2021[$keyApeDesde2021]['cuenta'] = $vhApeDesde2021[$keyApeDesde2021]['cuenta'];
                        $final2021[$keyApeDesde2021]['nombre'] = $vhApeDesde2021[$keyApeDesde2021]['nombre'];
                        $final2021[$keyApeDesde2021]['nivelCuenta'] = $vhApeDesde2021[$keyApeDesde2021]['nivel'];
                        $final2021[$keyApeDesde2021]['naturalezaCuenta'] = $vhApeDesde2021[$keyApeDesde2021]['naturalezaCuenta'];
                        $final2021[$keyApeDesde2021]['debe'] = $vhApeDesde2021[$keyApeDesde2021]['debe'] + $vhDesdeHasta2021[$keyApeDesde2021]['debe'];
                        $final2021[$keyApeDesde2021]['haber'] = $vhApeDesde2021[$keyApeDesde2021]['haber'] + $vhDesdeHasta2021[$keyApeDesde2021]['haber'];
                        $final2021[$keyApeDesde2021]['resultado'] = (($final2021[$keyApeDesde2021]['naturalezaCuenta'] == 'H') ? $final2021[$keyApeDesde2021]['haber'] - $final2021[$keyApeDesde2021]['debe'] : $final2021[$keyApeDesde2021]['debe'] - $final2021[$keyApeDesde2021]['haber']);

                        next($vhApeDesde2021);
                        $keyApeDesde2021 = key($vhApeDesde2021);

                        next($vhDesdeHasta2021);
                        $keyDesdeHasta2021 = key($vhDesdeHasta2021);

                        // Marcos: 28/abr/2016
                        // Se adicionó éste ELSE ya que si la consulta anterior no regresa nada nunca,
                        // entonces nunca se ajecuta un NEXT y se queda en un ciclo
                    } else {

                        // Marcos: 28/abr/2016
                        // Como el propósito era asignar valores, se valida que exista la variable y
                        // si existe se asignan los valores correspondients
                        if (isset($keyApeDesde2021) || (string) $keyApeDesde2021 != '') {
                            $final2021[$keyApeDesde2021]['cuenta'] = $vhApeDesde2021[$keyApeDesde2021]['cuenta'];
                            $final2021[$keyApeDesde2021]['nombre'] = $vhApeDesde2021[$keyApeDesde2021]['nombre'];
                            $final2021[$keyApeDesde2021]['nivelCuenta'] = $vhApeDesde2021[$keyApeDesde2021]['nivel'];
                            $final2021[$keyApeDesde2021]['naturalezaCuenta'] = $vhApeDesde2021[$keyApeDesde2021]['naturalezaCuenta'];
                            $final2021[$keyApeDesde2021]['debe'] = $vhApeDesde2021[$keyApeDesde2021]['debe'];
                            $final2021[$keyApeDesde2021]['haber'] = $vhApeDesde2021[$keyApeDesde2021]['haber'];
                            $final2021[$keyApeDesde2021]['resultado'] = (($final2021[$keyApeDesde2021]['naturalezaCuenta'] == 'H') ? $final2021[$keyApeDesde2021]['haber'] - $final2021[$keyApeDesde2021]['debe'] : $final2021[$keyApeDesde2021]['debe'] - $final2021[$keyApeDesde2021]['haber']);

                            next($vhApeDesde2021);
                            $keyApeDesde2021 = key($vhApeDesde2021);
                        }
                        if (isset($keyDesdeHasta2021) || (string) $keyDesdeHasta2021 != '') {
                            $final2021[$keyDesdeHasta2021]['cuenta'] = $vhDesdeHasta2021[$keyDesdeHasta2021]['cuenta'];
                            $final2021[$keyDesdeHasta2021]['nombre'] = $vhDesdeHasta2021[$keyDesdeHasta2021]['nombre'];
                            $final2021[$keyDesdeHasta2021]['nivelCuenta'] = $vhDesdeHasta2021[$keyDesdeHasta2021]['nivel'];
                            $final2021[$keyDesdeHasta2021]['naturalezaCuenta'] = $vhDesdeHasta2021[$keyDesdeHasta2021]['naturalezaCuenta'];
                            $final2021[$keyDesdeHasta2021]['debe'] = $vhDesdeHasta2021[$keyDesdeHasta2021]['debe'];
                            $final2021[$keyDesdeHasta2021]['haber'] = $vhDesdeHasta2021[$keyDesdeHasta2021]['haber'];
                            $final2021[$keyDesdeHasta2021]['resultado'] = (($final2021[$keyDesdeHasta2021]['naturalezaCuenta'] == 'H') ? $final2021[$keyDesdeHasta2021]['haber'] - $final2021[$keyDesdeHasta2021]['debe'] : $final2021[$keyDesdeHasta2021]['debe'] - $final2021[$keyDesdeHasta2021]['haber']);

                            next($vhDesdeHasta2021);
                            $keyDesdeHasta2021 = key($vhDesdeHasta2021);
                        }
                    }
                }

                $keyApeDesde2020 = key($vhApeDesde2020);
                $keyDesdeHasta2020 = key($vhDesdeHasta2020);
                $final2020 = [];

                while ($keyApeDesde2020 || $keyDesdeHasta2020) {
                    if (CompararCadenas((string) $keyApeDesde2020, (string) $keyDesdeHasta2020) == 0) {
                        $final2020[$keyApeDesde2020]['cuenta'] = $vhApeDesde2020[$keyApeDesde2020]['cuenta'];
                        $final2020[$keyApeDesde2020]['nombre'] = $vhApeDesde2020[$keyApeDesde2020]['nombre'];
                        $final2020[$keyApeDesde2020]['nivelCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['nivel'];
                        $final2020[$keyApeDesde2020]['naturalezaCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['naturalezaCuenta'];
                        $final2020[$keyApeDesde2020]['debe'] = $vhApeDesde2020[$keyApeDesde2020]['debe'] + $vhDesdeHasta2020[$keyApeDesde2020]['debe'];
                        $final2020[$keyApeDesde2020]['haber'] = $vhApeDesde2020[$keyApeDesde2020]['haber'] + $vhDesdeHasta2020[$keyApeDesde2020]['haber'];
                        $final2020[$keyApeDesde2020]['resultado'] = (($final2020[$keyApeDesde2020]['naturalezaCuenta'] == 'H') ? $final2020[$keyApeDesde2020]['haber'] - $final2020[$keyApeDesde2020]['debe'] : $final2020[$keyApeDesde2020]['debe'] - $final2020[$keyApeDesde2020]['haber']);

                        next($vhApeDesde2020);
                        $keyApeDesde2020 = key($vhApeDesde2020);

                        next($vhDesdeHasta2020);
                        $keyDesdeHasta2020 = key($vhDesdeHasta2020);

                        // Marcos: 28/abr/2016
                        // Se adicionó éste ELSE ya que si la consulta anterior no regresa nada nunca,
                        // entonces nunca se ajecuta un NEXT y se queda en un ciclo
                    } else {

                        // Marcos: 28/abr/2016
                        // Como el propósito era asignar valores, se valida que exista la variable y
                        // si existe se asignan los valores correspondients
                        if (isset($keyApeDesde2020) || (string) $keyApeDesde2020 != '') {

                            $final2020[$keyApeDesde2020]['cuenta'] = $vhApeDesde2020[$keyApeDesde2020]['cuenta'];
                            $final2020[$keyApeDesde2020]['nombre'] = $vhApeDesde2020[$keyApeDesde2020]['nombre'];
                            $final2020[$keyApeDesde2020]['nivelCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['nivel'];
                            $final2020[$keyApeDesde2020]['naturalezaCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['naturalezaCuenta'];
                            $final2020[$keyApeDesde2020]['debe'] = $vhApeDesde2020[$keyApeDesde2020]['debe'];
                            $final2020[$keyApeDesde2020]['haber'] = $vhApeDesde2020[$keyApeDesde2020]['haber'];
                            $final2020[$keyApeDesde2020]['resultado'] = (($final2020[$keyApeDesde2020]['naturalezaCuenta'] == 'H') ? $final2020[$keyApeDesde2020]['haber'] - $final2020[$keyApeDesde2020]['debe'] : $final2020[$keyApeDesde2020]['debe'] - $final2020[$keyApeDesde2020]['haber']);

                            next($vhApeDesde2020);
                            $keyApeDesde2020 = key($vhApeDesde2020);
                        }
                        if (isset($keyDesdeHasta2020) || (string) $keyDesdeHasta2020 != '') {

                            $final2020[$keyDesdeHasta2020]['cuenta'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['cuenta'];
                            $final2020[$keyDesdeHasta2020]['nombre'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['nombre'];
                            $final2020[$keyDesdeHasta2020]['nivelCuenta'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['nivel'];
                            $final2020[$keyDesdeHasta2020]['naturalezaCuenta'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['naturalezaCuenta'];
                            $final2020[$keyDesdeHasta2020]['debe'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['debe'];
                            $final2020[$keyDesdeHasta2020]['haber'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['haber'];
                            $final2020[$keyDesdeHasta2020]['resultado'] = (($final2020[$keyDesdeHasta2020]['naturalezaCuenta'] == 'H') ? $final2020[$keyDesdeHasta2020]['haber'] - $final2020[$keyDesdeHasta2020]['debe'] : $final2020[$keyDesdeHasta2020]['debe'] - $final2020[$keyDesdeHasta2020]['haber']);

                            next($vhDesdeHasta2020);
                            $keyDesdeHasta2020 = key($vhDesdeHasta2020);
                        }
                    }
                }

                $c1 = [31100, 31200, 31300];
                $c2 = [32510, 32520];
                $c3 = [32100, 32200, 323, 324];

                $arrayColum11 = [32500 => ['resultado11' => 0, 'resultado44' => 0]];
                $arrayColum22 = [31000 => ['resultado22' => 0, 'resultado44' => 0]];
                $arrayColum33 = [32000 => ['resultado33' => 0, 'resultado44' => 0]];

                foreach ($final2020 as $i => $v) {
                    if (in_array($i, $c2)) {
                        $arrayColum11[32500]['cuenta'] = '';
                        $arrayColum11[32500]['nombre'] = 'Rectificaciones de Resultados de Ejercicios Anteriores';
                        $arrayColum11[32500]['nivelCuenta'] = 1;
                        $arrayColum11[32500]['resultado11'] += $v['resultado'];
                        $arrayColum11[32500]['resultado44'] += $v['resultado'];
                        $arrayColum11[32500]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                        $arrayColum11[$i]['cuenta'] = $v['cuenta'];
                        $arrayColum11[$i]['nombre'] = $v['nombre'];
                        $arrayColum11[$i]['nivelCuenta'] = $v['nivelCuenta'];
                        $arrayColum11[$i]['resultado11'] = $v['resultado'];
                        $arrayColum11[$i]['resultado44'] = $v['resultado'];
                        $arrayColum11[$i]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;
                    } elseif (in_array($i, $c1)) {
                        $arrayColum22[31000]['cuenta'] = '';
                        $arrayColum22[31000]['nombre'] = 'Patrimonio Neto Inicial Ajustado del Ejercicio';
                        $arrayColum22[31000]['nivelCuenta'] = 1;
                        $arrayColum22[31000]['resultado22'] += $v['resultado'];
                        $arrayColum22[31000]['resultado44'] += $v['resultado'];
                        $arrayColum22[31000]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                        $arrayColum22[$i]['cuenta'] = $v['cuenta'];
                        $arrayColum22[$i]['nombre'] = $v['nombre'];
                        $arrayColum22[$i]['nivelCuenta'] = $v['nivelCuenta'];
                        $arrayColum22[$i]['resultado22'] = $v['resultado'];
                        $arrayColum22[$i]['resultado44'] = $v['resultado'];
                        $arrayColum22[$i]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;
                    } elseif (in_array($i, $c3)) {
                        $arrayColum33[32000]['cuenta'] = '';
                        $arrayColum33[32000]['nombre'] = 'Variaciones de la Hacienda Pública / Patrimonio Neto del Ejercicio';
                        $arrayColum33[32000]['nivelCuenta'] = 1;
                        $arrayColum33[32000]['resultado33'] += $v['resultado'];
                        $arrayColum33[32000]['resultado44'] += $v['resultado'];
                        $arrayColum33[32000]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                        $arrayColum33[$i]['cuenta'] = $v['cuenta'];
                        $arrayColum33[$i]['nombre'] = $v['nombre'];
                        $arrayColum33[$i]['nivelCuenta'] = $v['nivelCuenta'];
                        $arrayColum33[$i]['resultado33'] = $v['resultado'];
                        $arrayColum33[$i]['resultado44'] = $v['resultado'];
                        $arrayColum33[$i]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;
                    }
                }

                $arrayColum11[32600]['cuenta'] = '';
                $arrayColum11[32600]['nombre'] = '';
                $arrayColum11[32600]['nivelCuenta'] = 1;
                $arrayColum11[32600]['resultado11'] = '';
                $arrayColum11[32600]['resultado44'] = '';
                $arrayColum11[32600]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                $arrayColum22[31400]['cuenta'] = '';
                $arrayColum22[31400]['nombre'] = '';
                $arrayColum22[31400]['nivelCuenta'] = 1;
                $arrayColum22[31400]['resultado22'] = '';
                $arrayColum22[31400]['resultado44'] = '';
                $arrayColum22[31400]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                $arrayColum33[32500]['cuenta'] = '';
                $arrayColum33[32500]['nombre'] = '';
                $arrayColum33[32500]['nivelCuenta'] = 1;
                $arrayColum33[32500]['resultado33'] = '';
                $arrayColum33[32500]['resultado44'] = '';
                $arrayColum33[32500]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                $arrayColum33[33000]['cuenta'] = '';
                $arrayColum33[33000]['nombre'] = 'Hacienda Pública / Patrimonio Neto Final del Ejercicio ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);
                $arrayColum33[33000]['nivelCuenta'] = 1;
                $arrayColum33[33000]['resultado11'] = $arrayColum11[32500]['resultado11'];
                $arrayColum33[33000]['resultado22'] = $arrayColum22[31000]['resultado22'];
                $arrayColum33[33000]['resultado33'] = $arrayColum33[32000]['resultado33'];
                $arrayColum33[33000]['resultado44'] = $arrayColum22[31000]['resultado22'] + $arrayColum11[32500]['resultado11'] + $arrayColum33[32000]['resultado33'];
                $arrayColum33[33000]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                $arrayColum33[33100]['cuenta'] = '';
                $arrayColum33[33100]['nombre'] = '';
                $arrayColum33[33100]['nivelCuenta'] = 1;
                $arrayColum33[33100]['resultado11'] = '';
                $arrayColum33[33100]['resultado22'] = '';
                $arrayColum33[33100]['resultado33'] = '';
                $arrayColum33[33100]['resultado44'] = '';
                $arrayColum33[33100]['anio'] = substr($_SESSION[_S]['ejercicio'], 0, 4) - 1;

                $total_array2020 = array_merge($arrayColum11, $arrayColum22, $arrayColum33);

                $ajustes2021 = 0;

                // Marcos: 28/abr/2016
                // Como no existía la variable, se adicionó un isset() y un else para asignarle un valor default
                if (isset($final2021[3251])) {
                    $cuenta3251 = (($final2021[3251]['naturalezaCuenta'] == 'D') ? $final2021[3251]['debe'] - $final2021[3251]['haber'] : $final2021[3251]['haber'] - $final2021[3251]['debe']);
                } else {
                    $cuenta3251 = 0;
                }

                // Marcos: 28/abr/2016
                // Como no existía la variable, se adicionó un isset() y un else para asignarle un valor default
                if (isset($final2021[3252])) {
                    $cuenta3252 = (($final2021[3252]['naturalezaCuenta'] == 'D') ? $final2021[3252]['debe'] - $final2021[3252]['haber'] : $final2021[3252]['haber'] - $final2021[3252]['debe']);
                } else {
                    $cuenta3252 = 0;
                }

                $ajustes2021 = ($cuenta3251 + $cuenta3252);

                $arrayColum1 = [31000 => ['resultado22' => 0, 'resultado44' => 0]];

                $arrayColum3 = [32000 => ['resultado33' => 0, 'resultado44' => 0]];

                // Marcos: 28/abr/2016
                // Como no existía la variable, se adicionó un isset() y un else para asignarle un valor default
                if (!isset($arrayColum3[323])) {
                    $arrayColum3[323]['resultado44'] = 0;
                }

                foreach ($final2021 as $i => $v) {
                    if (in_array($i, $c1)) {
                        $arrayColum1[31000]['cuenta'] = '';
                        $arrayColum1[31000]['nombre'] = 'Cambios en la Hacienda Pública / Patrimonio Neto del Ejercicio ' . substr($_SESSION[_S]['ejercicio'], 0, 4);
                        $arrayColum1[31000]['nivelCuenta'] = 1;
                        $arrayColum1[31000]['resultado22'] += $v['resultado'];
                        $arrayColum1[31000]['resultado44'] += $v['resultado'];
                        $arrayColum1[31000]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

                        $arrayColum1[$i]['cuenta'] = $v['cuenta'];
                        $arrayColum1[$i]['nombre'] = $v['nombre'];
                        $arrayColum1[$i]['nivelCuenta'] = $v['nivelCuenta'];
                        $arrayColum1[$i]['resultado22'] = $v['resultado'];
                        $arrayColum1[$i]['resultado44'] = $v['resultado'];
                        $arrayColum1[$i]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);
                    } elseif (in_array($i, $c3)) {
                        $arrayColum3[32000]['cuenta'] = '3.2.0.0.0';
                        $arrayColum3[32000]['nombre'] = 'Variaciones de la Hacienda Pública / Patrimonio Neto del Ejercicio';
                        $arrayColum3[32000]['nivelCuenta'] = 1;
                        $arrayColum3[32000]['resultado33'] += $v['resultado'];
                        $arrayColum3[32000]['resultado44'] += $v['resultado'];
                        $arrayColum3[32000]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

                        $arrayColum3[$i]['cuenta'] = $v['cuenta'];
                        $arrayColum3[$i]['nombre'] = $v['nombre'];
                        $arrayColum3[$i]['nivelCuenta'] = $v['nivelCuenta'];
                        $arrayColum3[$i]['resultado33'] = $v['resultado'];
                        $arrayColum3[$i]['resultado44'] = $v['resultado'];
                        $arrayColum3[$i]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);
                    }
                }

                $arrayColum3[32000]['resultado44'] += $ajustes2021;
                $arrayColum3[323]['resultado44'] += $ajustes2021;

                $arrayColum1[31400]['cuenta'] = '';
                $arrayColum1[31400]['nombre'] = '';
                $arrayColum1[31400]['nivelCuenta'] = 1;
                $arrayColum1[31400]['resultado22'] = '';
                $arrayColum1[31400]['resultado44'] = '';
                $arrayColum1[31400]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

                $arrayColum3[32500]['cuenta'] = '';
                $arrayColum3[32500]['nombre'] = '';
                $arrayColum3[32500]['nivelCuenta'] = 1;
                $arrayColum3[32500]['resultado22'] = '';
                $arrayColum3[32500]['resultado44'] = '';
                $arrayColum3[32500]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

                $arrayColum3[33000]['cuenta'] = '3.3.0.0.0';
                $arrayColum3[33000]['nombre'] = 'Saldo Neto en la Hacienda Pública / Patrimonio ' . substr($_SESSION[_S]['ejercicio'], 0, 4);
                $arrayColum3[33000]['nivelCuenta'] = 1;
                $arrayColum3[33000]['resultado22'] = $arrayColum1[31000]['resultado22'];
                $arrayColum3[33000]['resultado11'] = 0;
                $arrayColum3[33000]['resultado33'] = $arrayColum3[32000]['resultado33'];
                $arrayColum3[33000]['resultado44'] = $arrayColum3[33000]['resultado11'] + $arrayColum3[33000]['resultado22'] + $arrayColum3[33000]['resultado33'] + $ajustes2021;
                $arrayColum3[33000]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

                $total_array2021 = array_merge($arrayColum1, $arrayColum3);

                if ($_SESSION[_S]['reportes'] != 'C93') {
                    $total_array = array_merge($total_array2020, $total_array2021);
                } else {
                    $total_array = $total_array2021;
                }

                $max = count($total_array);

                $cuentasTotales = [32500, 31000, 32000, 33000];
                for ($x = 0; $x <= $max; $x++) {
                    $cuenta = '';
                    $concepto = '';
                    $columna1 = '';
                    $columna2 = '';
                    $columna3 = '';
                    $columna4 = '';
                    $columna5 = '';

                    if (isset($total_array[$x])) {
                        // Marcos: 28/abr/2016
                        // Como no existía la variable, se adicionó un isset() para asignarle un valor default
                        if (!isset($total_array[$x]['cuenta'])) {
                            $total_array[$x]['cuenta'] = '';
                        }

                        if ($total_array[$x]['cuenta'] == '' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') {
                            $pdf->SetFont('univiapro', 'B', 7);
                        } else {
                            $pdf->SetFont('univiapro', '', 7);
                        }

                        if ($_SESSION[_S]['reportes'] == 'C93') {
                            if ($total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) {
                                $cuenta = $total_array[$x]['cuenta'];
                                $concepto = utf8Decode($total_array[$x]['nombre']);

                                if ($total_array[$x]['nombre'] != '') {
                                    if ($_SESSION[_S]['reportes'] == 'C113') {
                                        $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format(round($total_array[$x]['resultado22'] / $MilPesos, 0), 0) : number_format(0, 0));
                                        $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format(round($total_array[$x]['resultado11'] / $MilPesos, 0), 0) : number_format(0, 0));
                                        $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format(round($total_array[$x]['resultado33'] / $MilPesos, 0), 0) : number_format(0, 0));
                                        $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format(round($ajustes2021 / $MilPesos, 0), 0) : number_format(0, 0));
                                        $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format(round($total_array[$x]['resultado44'] / $MilPesos, 0), 0) : number_format(0, 0));
                                    } else {
                                        $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format($total_array[$x]['resultado22'] / $MilPesos, 2) : number_format(0, 0));
                                        $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format($total_array[$x]['resultado11'] / $MilPesos, 2) : number_format(0, 0));
                                        $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format($total_array[$x]['resultado33'] / $MilPesos, 2) : number_format(0, 0));
                                        $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format($ajustes2021 / $MilPesos, 2) : number_format(0, 0));
                                        $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format($total_array[$x]['resultado44'] / $MilPesos, 2) : number_format(0, 0));
                                    }
                                } else {
                                    $columna1 = '';
                                    $columna2 = '';
                                    $columna3 = '';
                                    $columna4 = '';
                                    $columna5 = '';
                                }
                            }
                        } else {
                            $cuenta = $total_array[$x]['cuenta'];

                            // Marcos: 28/abr/2016
                            // Como no existía la variable, se adicionó un isset() para asignarle un valor default
                            if (!isset($total_array[$x]['nombre'])) {
                                $total_array[$x]['nombre'] = '';
                            }

                            $concepto = utf8Decode($total_array[$x]['nombre']);

                            if ($total_array[$x]['nombre'] != '') {
                                if ($_SESSION[_S]['reportes'] == 'C113') {
                                    $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format(round($total_array[$x]['resultado22'] / $MilPesos, 0), 0) : number_format(0, 0));
                                    $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format(round($total_array[$x]['resultado11'] / $MilPesos, 0), 0) : number_format(0, 0));
                                    $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format(round($total_array[$x]['resultado33'] / $MilPesos, 0), 0) : number_format(0, 0));
                                    $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format(round($ajustes2021 / $MilPesos, 0), 0) : number_format(0, 0));
                                    $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format(round($total_array[$x]['resultado44'] / $MilPesos, 0), 0) : number_format(0, 0));
                                } else {
                                    $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format($total_array[$x]['resultado22'] / $MilPesos, 2) : number_format(0, 0));
                                    $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format($total_array[$x]['resultado11'] / $MilPesos, 2) : number_format(0, 0));
                                    $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format($total_array[$x]['resultado33'] / $MilPesos, 2) : number_format(0, 0));
                                    $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format($ajustes2021 / $MilPesos, 2) : number_format(0, 0));
                                    $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format($total_array[$x]['resultado44'] / $MilPesos, 2) : number_format(0, 0));
                                }
                            } else {
                                $columna1 = '';
                                $columna2 = '';
                                $columna3 = '';
                                $columna4 = '';
                                $columna5 = '';
                            }
                        }
                    }
                    if ($pdf->getY() > 175) {
                        $xtm = $pdf->getX();
                        $ytm = $pdf->getY();
                        $pdf->Line($xtm, $ytm, 258, $ytm);
                        $pdf->addPage();
                    }

                    $pdf->setAltoLinea($pdf->intLin);
                    $pdf->SetFillColor(192, 192, 192);

                    $pdf->setX(20);

                    if ($cc != '') {
                        $pdf->Row([$cuenta, $concepto, $columna1, $columna2, $columna3, $columna4, $columna5], 'LR');
                    } else {
                        $pdf->Row([$concepto, $columna1, $columna2, $columna3, $columna4, $columna5], 'LR');
                    }
                }

                $pdf->setX(20);

                $xtm = $pdf->getX();
                $ytm = $pdf->getY();
                $pdf->Line($xtm, $ytm, 258, $ytm);

                break;

            case 4: // Cambios en la Situación Financiera
                $mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';
                $mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
                $tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : '';
                $checkMiles = (isset($_POST['ckmiles'])) ? 1000 : 1;
                $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
                $excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

                $pdf->SetTitle('Edo_Cambios_Situacion_Financiera');
                $pdf->setOrientation('P');
                $pdf->AliasNbPages();
                $pdf->AddPage();
                $pdf->setRelleno('no');
                $pdf->AddFont('univiapro', '');
                $pdf->AddFont('univiapro', 'B');
                $pdf->AddFont('univiapro');
                $pdf->SetFont('univiapro', '', 7);
                $pdf->SetWidths([110, 35, 35]);
                $pdf->SetAligns(['L', 'R', 'R']);

                $activo2020 = [];
                $arrayPH2020 = [];

                $ejercicioActual = $_SESSION[_S]['ejercicioActual'];

                list($activo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 1, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($pasivo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 2, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($hacienda) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 3, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                list($ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 4, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 5, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                //obtenemos resultado del ejercicio (ahorro/desahorro)
                $result2021 = 0;
                if (count($ingresos) > 0 && count($gastos) > 0) {
                    $natIng2021 = $ingresos[4]['naturalezaCuenta'];
                    $natGas2021 = $gastos[5]['naturalezaCuenta'];

                    $debeI2021 = $ingresos[4]['debe'];
                    $haberI2021 = $ingresos[4]['haber'];
                    $debeG2021 = $gastos[5]['debe'];
                    $haberG2021 = $gastos[5]['haber'];

                    if ($natIng2021 == 'D') {
                        $result2021 = $debeI2021 - $haberI2021;
                    } else {
                        $result2021 = $haberI2021 - $debeI2021;
                    }

                }

                list($activo15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 1, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($pasivo15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 2, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                list($hacienda15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 3, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                // AUNQUE LA REGLA ES QUITAR ÉSTOS COMENTARIOS, SE PIDE ESPERAR AL SEGUNDO TRIMESTRE
                // DEL 2024, YA QUE HAYA PASADO LA REVISIÓN DE CONTABILIDAD, PARA NO PERDER
                // EL HILO DE CÓMO SE OBTIENE EL RESULTADO DEL EJERCICIO DEL PERIODO ANTERIOR

                // list($ingresos15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesHasta -1, 4, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
                // list($gastos15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesHasta -1, 5, $tipoReporte, $ejercicioActual, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

                $result2020 = 0;

                $activo2020 = $pdf->devuelveArrayOrdenado($activo15, $pdf->nivel);
                $pasivo2020 = $pdf->devuelveArrayOrdenado($pasivo15, $pdf->nivel);
                $hacienda2020 = $pdf->devuelveArrayOrdenado($hacienda15, $pdf->nivel);


                $arrayPH2020 = $pasivo2020 + $hacienda2020;

                $activo2021 = $pdf->devuelveArrayOrdenado($activo, $pdf->nivel);
                $pasivo2021 = $pdf->devuelveArrayOrdenado($pasivo, $pdf->nivel);
                $hacienda2021 = $pdf->devuelveArrayOrdenado($hacienda, $pdf->nivel);

                #SE UNEN LOS ARREGLOS
                $arrayPH2021 = $pasivo2021 + $hacienda2021;

                $arrayActivo = $pdf->compararCuentas($activo2020, $activo2021);
                $arrayPasivoHacienda = $pdf->compararCuentas($arrayPH2020, $arrayPH2021);

                /*
                REALIZAMOS LAS OPERACIONES CORRESPONDIENTES PARA OBTENER EL ORIGEN Y LA APLICACION
                Para el activo, será origen cuando la diferencia de 2020-2018 sea negativo, en caso contrario será aplicacion
                Para el Pasivo y Hacienda, cuando la diferencia de 2020-2018 sea negativo será aplicación, caso contrario será origen.

                Los totales de los niveles 2 será la sumatoria que le corresponda a cada grupo
                 */

                //activo
                $diferencia = $totalOrigenActivo = $totalAplicacionActivo = $totalOrigenPH = $totalAplicacionPH = $sumaO = $sumaA = 0;
                $grupo = '';

                foreach ($arrayActivo as $act => $val) {
                    $arrayActivo[$act] = $arrayActivo[$act];
                    $diferencia = ($arrayActivo[$act]['importe2021'] - $arrayActivo[$act]['importe2020']);
                    if ($diferencia < 0) {
                        $arrayActivo[$act]['origen'] = ($diferencia) * -1;
                        $arrayActivo[$act]['aplicacion'] = 0;
                    } else {
                        $arrayActivo[$act]['origen'] = 0;
                        $arrayActivo[$act]['aplicacion'] = $diferencia;
                    }

                    //reseteamos los valores del nivel 2 para obtener la suma original del origen y aplicacion
                    if ($arrayActivo[$act]['nivel'] <= 2) {
                        $arrayActivo[$act]['origen'] = 0;
                        $arrayActivo[$act]['aplicacion'] = 0;
                    }
                    //obtenemos los totales del activo
                    $totalOrigenActivo += $arrayActivo[$act]['origen'];
                    $totalAplicacionActivo += $arrayActivo[$act]['aplicacion'];
                }

                //obtenemos los totales de los grupos del activo

                foreach ($arrayActivo as $tot => $val) {
                    //obtenemos las llaves de los grupos
                    if ($arrayActivo[$tot]['nivel'] == 3) {
                        $reemplazaPunto = str_replace(['.'], [''], $arrayActivo[$tot]['cuenta']);
                        $subs = substr($reemplazaPunto, 0, 2);
                        $llaveGrupo = str_pad($subs, 5, '0', STR_PAD_RIGHT) + 000;
                        $llaveRubro = $tot;
                    }
                    //ponemos el total global del activo
                    if ($arrayActivo[$tot]['nivel'] == 1) {
                        $arrayActivo[$tot]['origen'] = $totalOrigenActivo;
                        $arrayActivo[$tot]['aplicacion'] = $totalAplicacionActivo;
                    }

                    //obtenemos el total de cada grupo.
                    if ($arrayActivo[$tot]['nivel'] == 3 && substr($llaveGrupo, 0, 2) == substr($llaveRubro, 0, 2)) {
                        $arrayActivo[$llaveGrupo]['origen'] += $arrayActivo[$tot]['origen'];
                        $arrayActivo[$llaveGrupo]['aplicacion'] += $arrayActivo[$tot]['aplicacion'];
                    }
                }

                //obteniendo origen y aplicacion de todos los niveles de pasivo + hacienda
                foreach ($arrayPasivoHacienda as $pas => $val) {
                    $arrayPasivoHacienda[$pas] = $arrayPasivoHacienda[$pas];
                    $diferencia = ($arrayPasivoHacienda[$pas]['importe2021'] - $arrayPasivoHacienda[$pas]['importe2020']);
                    if ($diferencia < 0) {
                        $arrayPasivoHacienda[$pas]['origen'] = 0;
                        $arrayPasivoHacienda[$pas]['aplicacion'] = ($diferencia) * -1;
                    } else {
                        $arrayPasivoHacienda[$pas]['origen'] = $diferencia;
                        $arrayPasivoHacienda[$pas]['aplicacion'] = 0;
                    }
                    //reseteamos el valor de los niveles 1 y 2, menos el registro de resultado del ejercicio.
                    if ($arrayPasivoHacienda[$pas]['nivel'] <= 2 && $pas != 49000) {
                        $arrayPasivoHacienda[$pas]['origen'] = 0;
                        $arrayPasivoHacienda[$pas]['aplicacion'] = 0;
                    }
                }

                //obtenemos los totales de los grupos del pasivo + hacienda
                foreach ($arrayPasivoHacienda as $tot => $val) {
                    //obtenemos las llaves de los grupos nivel 2
                    if ($arrayPasivoHacienda[$tot]['nivel'] == 3) {
                        $reemplazaPunto = str_replace(['.'], [''], $arrayPasivoHacienda[$tot]['cuenta']);
                        $subs = substr($reemplazaPunto, 0, 2);
                        $llaveGrupo = str_pad($subs, 5, '0', STR_PAD_RIGHT) + 000;
                        $llaveRubro = $tot;
                    }
                    //obtenemos el total de cada grupo. nivel 2
                    if ($arrayPasivoHacienda[$tot]['nivel'] == 3 && substr($llaveGrupo, 0, 2) == substr($llaveRubro, 0, 2)) {
                        $arrayPasivoHacienda[$llaveGrupo]['origen'] += $arrayPasivoHacienda[$llaveRubro]['origen'];
                        $arrayPasivoHacienda[$llaveGrupo]['aplicacion'] += $arrayPasivoHacienda[$llaveRubro]['aplicacion'];
                    }

                    //obtenemos el total global de pasivo + hacienda
                    $totalOrigenPH += $arrayPasivoHacienda[$tot]['origen'];
                    $totalAplicacionPH += $arrayPasivoHacienda[$tot]['aplicacion'];
                }

                //obtenemos los totales de los generos
                foreach ($arrayPasivoHacienda as $key => $val) {
                    //obtenemos las llaves de los generos nivel 1
                    if ($arrayPasivoHacienda[$key]['nivel'] == 2) {
                        $reemplazaPunto2 = str_replace(['.'], [''], $arrayPasivoHacienda[$key]['cuenta']);
                        $subs2 = substr($reemplazaPunto2, 0, 1);
                        $llaveGenero = str_pad($subs2, 5, '0', STR_PAD_RIGHT) + 0000;
                        $llaveGrupo2 = $key;
                    }
                    //obtenemos el total de cada genero. nivel 1
                    if ($arrayPasivoHacienda[$key]['nivel'] == 2 && substr($llaveGenero, 0, 1) == substr($llaveGrupo2, 0, 1)) {
                        $arrayPasivoHacienda[$llaveGenero]['origen'] += $arrayPasivoHacienda[$llaveGrupo2]['origen'];
                        $arrayPasivoHacienda[$llaveGenero]['aplicacion'] += $arrayPasivoHacienda[$llaveGrupo2]['aplicacion'];
                    }
                }

                // Para incluir el resultado del ejercicio tomando la información
                // siguiendo las reglas del Estado de Actividades, según requerimiento
                // del ticket #SDS-611738
                $saldo_final = $hacienda2021[32100]['importe'];
                $saldo_inicial = $hacienda2020[32100]['importe'];
                $resultado_periodo = ($haberI2021 - $debeG2021);
                $importe_final = ($saldo_final - $saldo_inicial) + $resultado_periodo;
                if ($importe_final >= 0) {
                    $origen_321 = $importe_final;
                    $aplicacion_321 = 0;
                } else {
                    $origen_321 = 0;
                    $aplicacion_321 = $importe_final;
                }

                // Si tenemos la cuenta desde la balanza, ignoramos ese importe y ponemos
                // el que solicitan
                if (isset($arrayPasivoHacienda[32100])) {
                    $arrayPasivoHacienda[32100]['origen'] = $origen_321;
                    $arrayPasivoHacienda[32100]['aplicacion'] = $aplicacion_321;
                    // Si no la tenemos, la creamos
                } else {
                    $arrayPasivoHacienda[32100] = [
                        'cuenta'      => '3.2.1',
                        'nombre'      => 'Resultados del Ejercicio (Ahorro/Desahorro)' ,
                        'nivel'       => 3,
                        'importe2021' => 0,
                        'importe2020' => 0,
                        'origen'      => $origen_321,
                        'aplicacion'  => $aplicacion_321,
                    ];
                }

                // Actualizamos las sumas de origen y aplicación de la 32000
                $suma_origen_32 = 0;
                $suma_aplicacion_32 = 0;

                foreach ($arrayPasivoHacienda as $key => $val) {
                    if (substr($key, 0, 2) == '32' &&
                        substr($key, 2, 1) != '0'
                    ) {
                        $suma_origen_32 += $val['origen'];
                        $suma_aplicacion_32 += $val['aplicacion'];
                    }
                }

                $arrayPasivoHacienda[32000]['origen'] = $suma_origen_32;
                $arrayPasivoHacienda[32000]['aplicacion'] = $suma_aplicacion_32;

                reset($arrayActivo);
                reset($arrayPasivoHacienda);
                $keyActivo = key($arrayActivo);
                $keyPasivoHacienda = key($arrayPasivoHacienda);

                for ($i = 0; $i < 2; $i++) {
                    if ($i == 0) {
                        $key = $keyActivo;
                        $array = $arrayActivo;
                    } else {
                        $key = $keyPasivoHacienda;
                        $array = $arrayPasivoHacienda;
                    }

                    while ($key) {
                        $nivel = $array[$key]['nivel'];
                        if ($nivel != 1) {
                            $nombre = utf8Decode(UrNombre::titleCase($array[$key]['nombre']));
                        } else {
                            $nombre = utf8Decode($array[$key]['nombre']);
                        }
                        $origen = $array[$key]['origen'];
                        $origen = (isset($origen) ? number_format(round($origen / $checkMiles, 0), 0) : number_format(0, 0));
                        $aplicacion = $array[$key]['aplicacion'];
                        $aplicacion = (isset($aplicacion) ? number_format(round($aplicacion / $checkMiles, 0), 0) : number_format(0, 0));

                        //banderas para controlar el estilo de la fuente.
                        $bandera_fuente = ($nivel == 1 || $nivel == 2);

                        //Banderas para controlar el tamaño de la fuente
                        $bandera_size = ($nivel == 2);

                        $f = new stdClass();
                        $f->font = 'univiapro';
                        $f->size = ($bandera_size ? '5.4' : '6');
                        $f->style = ($bandera_fuente ? 'B' : '');
                        $fuentes = [$f];

                        $pdf->setFuentes($fuentes);
                        $pdf->setAltoLinea($pdf->intLin);

                        if ($nivel == 1) {
                            $nombre = $nombre;
                        } elseif ($nivel == 2) {
                            $nombre = chr(160) . '   ' . $nombre;
                        } else {
                            $nombre = chr(160) . '       ' . $nombre;
                        }

                        //   $pdf->setX(20);
                        $pdf->SetAligns(['L', 'R', 'R']);
                        $pdf->SetArrayBorders(['LR', '', 'RL']);
                        $pdf->Row([$nombre, $origen, $aplicacion]);

                        // Modificamos el interlineado porque en el ticket #SDS-611738
                        // quieren que todo quede en una sola página
                        $pdf->setY($pdf->getY() - .5);

                        if ($pdf->getY() > 240) {
                            $pdf->addPage();
                        }

                        next($array);
                        $key = key($array);
                    }
                }
                $pdf->Ln(.5);
                $pdf->cell(180, 0, '', 'B', 0, 'C', false);
                $pdf->Ln(1);
                $pdf->SetFont('univiapro', 'B', 6);
                $pdf->Cell(180, 2, utf8Decode('Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.'), 0, 0, 'L', false);
                break;

            case 6: // Analítico
                $pdf->ceros = 1;
                $pdf->SetTitle('Edo_Analitico_Del_Activo');
                $pdf->setOrientation('P');
                $pdf->AliasNbPages();
                $pdf->AddPage();
                $pdf->AddFont('univiapro', '');
                $pdf->AddFont('univiapro', 'B');
                $pdf->SetFont('univiapro', '', 9);
                $pdf->SetFillColor(255, 255, 255);

                $pdf->setBorders(true);

                $fill = false;

                $lineas = 1;
                list($datos) = balanzaConsolidacion($pdf->gpo_id, $pdf->ur_id, $pdf->ue, $pdf->mesDesde, $pdf->mesHasta, $pdf->genero, $pdf->tipoReporte, $pdf->ejercicio, '', $pdf->grupos, $pdf->excluir, $pdf->ceros, $pdf->incCta);

                $naturalezaGenero = $datos[$pdf->genero]['naturalezaCuenta'];

                $sort = [];
                $sort['indice'] = [];
                foreach ($datos as $i => $val) {
                    $sort['indice'][$i] = str_pad($i, 5, '0', STR_PAD_RIGHT);
                }
                array_multisort($sort['indice'], SORT_ASC, $datos);
                if ($datos) {
                    foreach ($datos as $indice => $valor) {
                        if ($pdf->nivel[$valor['nivel']] == 1) {
                            if ($valor['nivel'] != 1) {
                                $valor['nombre'] = utf8Decode(UrNombre::titleCase(TruncateCadena(trim($valor['nombre']), $pdf->longitud2)));
                            } else {
                                $valor['nombre'] = TruncateCadena(utf8Decode(trim($valor['nombre'])), $pdf->longitud2);
                            }
                            $espacio = '';
                            switch ($valor['nivel']) {
                                case 1:
                                    $pdf->SetFont('univiapro', 'B', 5.4);
                                    break;
                                case 2:
                                    $pdf->SetFont('univiapro', 'B', 5.4);
                                    $espacio = '   ';
                                    break;
                                case 3:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '      ';
                                    break;
                                case 4:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '         ';
                                    break;
                                case 5:
                                    $pdf->SetFont('univiapro', '', 6);
                                    $espacio = '            ';
                                    break;
                                default:
                                    $pdf->SetFont('univiapro', '', 6);
                                    break;
                            }

                            $pdf->SetX(15);
                            if ($valor['cuenta'] != '') {
                                $pdf->Cell(82, $pdf->intLin, $espacio . substr($valor['nombre'], 0, $pdf->longitud2), 'LR', 0, 'L', $fill);
                            }

                            if ($valor['naturalezaCuenta'] == 'D') {
                                $inicial = ($valor['debeI'] - $valor['haberI']) / $pdf->chkEnMiles;
                                $final = $inicial + ($valor['debeM'] - $valor['haberM']) / $pdf->chkEnMiles;
                                $flujo = $final - $inicial;
                            } else {
                                $inicial = ($valor['haberI'] - $valor['debeI']) / $pdf->chkEnMiles;
                                $final = $inicial + ($valor['haberM'] - $valor['debeM']) / $pdf->chkEnMiles;
                                $flujo = $final - $inicial;
                            }

                            if ($valor['nivel'] >= 3) {
                                $inicial = (($valor['naturalezaCuenta'] != $naturalezaGenero) ? $inicial * -1 : $inicial);
                                $final = (($valor['naturalezaCuenta'] != $naturalezaGenero) ? $final * -1 : $final);
                                $flujo = $final - $inicial; //cambio por Ticket #211673
                            }

                            if ($valor['nivel'] == 1 || $valor['nivel'] == 2) {
                                $pdf->SetFont('univiapro', 'B', 6);
                            } else {
                                $pdf->SetFont('univiapro', '', 6);
                            }

                            if ($_SESSION[_S]['reportes'] == 'C116') {
                                // $pdf->Cell(22, $pdf->intLin, ((bccomp($inicial, 0, 2)) ? number_format(round($inicial, 0), 0) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                // $pdf->Cell(22, $pdf->intLin, ((bccomp($valor['debeM'], 0, 2)) ? number_format(round($valor['debeM'] / $pdf->chkEnMiles, 0), 0) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                // $pdf->Cell(22, $pdf->intLin, ((bccomp($valor['haberM'], 0, 2)) ? number_format(round($valor['haberM'] / $pdf->chkEnMiles, 0), 0) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                // $pdf->Cell(22, $pdf->intLin, ((bccomp($final, 0, 2)) ? number_format(round($final, 0), 0) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                // $pdf->Cell(22, $pdf->intLin, ((bccomp($flujo, 0, 2)) ? number_format(round($flujo, 0), 0) : number_format(0, 0)), 'LR', 1, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, number_format(round($inicial, 0), 0), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, number_format(round($valor['debeM'] / $pdf->chkEnMiles, 0), 0), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, number_format(round($valor['haberM'] / $pdf->chkEnMiles, 0), 0), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, number_format(round($final, 0), 0), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, number_format(round($flujo, 0), 0), 'LR', 1, 'R', $fill);
                            } else {
                                $pdf->Cell(22, $pdf->intLin, ((bccomp($inicial, 0, 2)) ? number_format($inicial, 2) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, ((bccomp($valor['debeM'], 0, 2)) ? number_format($valor['debeM'] / $pdf->chkEnMiles, 2) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, ((bccomp($valor['haberM'], 0, 2)) ? number_format($valor['haberM'] / $pdf->chkEnMiles, 2) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, ((bccomp($final, 0, 2)) ? number_format($final, 2) : number_format(0, 0)), 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, ((bccomp($flujo, 0, 2)) ? number_format($flujo, 2) : number_format(0, 0)), 'LR', 1, 'R', $fill);
                            }
                            $lineas++;
                            for ($con = 1; strlen($valor['nombre']) > $pdf->longitud2 * $con; $con++) {
                                if ($lineas > 29) {
                                    $pdf->Cell(238, 1, '', 'T', 1);
                                    $pdf->SetFillColor(230, 230, 230);
                                    $pdf->SetTextColor(0);
                                    $pdf->AddPage();
                                    $lineas = 1;
                                }

                                switch ($valor['nivel']) {
                                    case 2:
                                        $pdf->SetFont('univiapro', 'B', 5.4);
                                        $espacio = '   ';
                                        break;
                                    case 3:
                                        $pdf->SetFont('univiapro', '', 6);
                                        $espacio = '      ';
                                        break;
                                    case 4:
                                        $pdf->SetFont('univiapro', '', 6);
                                        $espacio = '         ';
                                        break;
                                    case 5:
                                        $pdf->SetFont('univiapro', '', 6);
                                        $espacio = '            ';
                                        break;
                                    default:
                                        $pdf->SetFont('univiapro', '', 6);
                                        break;
                                }

                                $pdf->SetX(15);
                                $pdf->Cell(82, $pdf->intLin, $espacio . substr($valor['nombre'], $pdf->longitud2 * $con, $pdf->longitud2), 'LR', 0, 'L', $fill);
                                $pdf->Cell(22, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                                $pdf->Cell(22, $pdf->intLin, '', 'LR', 1, 'C', $fill);
                                $lineas++;
                            }

                            if ($lineas > 29) {
                                $pdf->Cell(192, 1, '', 'T', 1);
                                $pdf->SetFillColor(230, 230, 230);
                                $pdf->SetTextColor(0);
                                $pdf->AddPage();
                                $lineas = 1;
                            }

                            $fill = !$fill;
                        }
                    }
                } else {
                    $pdf->SetX(10);
                    $pdf->Cell(192, 5, 'NO HAY REGISTROS', 1, 1, 'C');
                }
                $pdf->SetFont('univiapro', 'B', 6);
                $pdf->Cell(192, $pdf->intLin, utf8Decode('Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.'), 'T', 1, 'L');
                $pdf->SetFont('univiapro', 'B', 6);
                //$pdf->SetBorders(true);
                +               $pdf->Cell(178, 2, '', 0, 0, 'L', false);
                break;

            case 7: // Deuda Pública
                $mesDesde = (isset($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1;
                $mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
                $tipoReporte = (isset($_POST['tipoReporte']) != '') ? $_POST['tipoReporte'] : '';
                $MilPesos = (isset($_POST['MilPes']) != '') ? 1000 : 1;
                $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
                $excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                $cc = isset($_POST['cc']) && $_POST['cc'] == 1 ? $_POST['cc'] : '';
                $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

                //cuerpo de la tabla
                $pdf->SetTitle('Edo_Analitico_Deuda_Y_Otros_Pasivos');
                //Letra
                $pdf->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
                $pdf->AddFont('Montserrat', '', 'Montserrat-Regular.php');

                $pdf->SetFont('Montserrat', '', 6.8);
                $pdf->setOrientation('P');
                $pdf->AliasNbPages();
                $pdf->AddPage();

                $pdf->SetWidths([70, 25, 25, 30, 30]);
                $pdf->SetAligns(['L', 'R', 'R', 'R', 'R']);
                $pdf->setBorders(true);

                //Márgenes
                $pdf->SetDrawColor(0, 0, 0);

                $arrayApe2014 = $pdf->consultaCuentas($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, substr($_SESSION[_S]['ejercicio'], 0, 4), $tipoReporte, $grupos, $excluir, $pdf->incCta);
                $arrayDesdeHasta2014 = $pdf->consultaCuentas($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, substr($_SESSION[_S]['ejercicio'], 0, 4), $tipoReporte, $grupos, $excluir, $pdf->incCta);

                $res2131 = ($arrayApe2014[2131]['resultado'] + $arrayDesdeHasta2014[2131]['resultado']);
                $res223 = ($arrayApe2014[223]['resultado'] + $arrayDesdeHasta2014[223]['resultado']);
                $res2116 = ($arrayApe2014[2116]['resultado'] + $arrayDesdeHasta2014[2116]['resultado']);

                $totalApe = ($arrayApe2014[2131]['resultado'] + $arrayApe2014[223]['resultado'] + $arrayApe2014[2116]['resultado']) / $MilPesos;
                $totalPeriodo = ($res2131 + $res223 + $res2116) / $MilPesos;

                // Como la mayoría de los conceptos NO tienen cuenta contable, lo remarcamos en la impresión
                // para validación, para que vayan checando ésta situación
                if ($pdf->incCta == '1') {
                    $strPrefijo = '?.?.?.? ';
                } else {
                    $strPrefijo = '';
                }

                $pdf->setX(20);

                $fill = false;

                //Interlineado
                $altoLinea = $pdf->intLin;
                $nivel1 = '                     ';
                $nivel2 = '           ';
                $nivel3 = '              ';

                $pdf->SetFont('Montserrat-b', '', 6.8);
                $pdf->Cell(70, $altoLinea + 2, utf8Decode('DEUDA PÚBLICA'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel1 . 'Corto Plazo', 'LR', 0, 'C', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel2 . 'Deuda Interna', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat', '', 6.8);

                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($pdf->incCta == '1') {
                    $strPasoTexto = '2.1.3.1 Instituciones de Crédito';
                } else {
                    $strPasoTexto = 'Instituciones de Crédito';
                }


                if (($arrayApe2014[2131]['resultado'] > 0) || ($res2131 > 0)) {
                    $moneda = 'Pesos';
                    $pais = utf8Decode('México');
                } else {
                    $moneda = '';
                    $pais = '';
                }

                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPasoTexto), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, $moneda, 'LR', 0, 'C', $fill);
                $pdf->Cell(25, $altoLinea, $pais, 'LR', 0, 'C', $fill);

                //Residuo
                if (($arrayApe2014[2131]['resultado'] / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($arrayApe2014[2131]['resultado'] / $MilPesos, $decimales), 0), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($arrayApe2014[2131]['resultado'] / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                //Residuo
                if (($res2131 / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($res2131 / $MilPesos, 0), $decimales), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($res2131 / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Titulos y Valores'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat-b', '', 6.8);
                $pdf->Cell(70, $altoLinea, $nivel2 . 'Deuda Externa', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat', '', 6.8);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Organismos Financieros Internacionales'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Deuda Bilateral'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Titulos y Valores'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat-b', '', 6.8);
                $pdf->Cell(70, $altoLinea, $nivel2 . utf8Decode('Subtotal de Deuda Pública a Corto Plazo'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                //Residuo
                if (($arrayApe2014[2131]['resultado'] / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($arrayApe2014[2131]['resultado'] / $MilPesos, 2), $decimales), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($arrayApe2014[2131]['resultado'] / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                //Residuo
                if (($res2131 / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($res2131 / $MilPesos, 2), $decimales), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($res2131 / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel1 . 'Largo Plazo', 'LR', 0, 'C', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel2 . 'Deuda Interna', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat', '', 6.8);
                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($pdf->incCta == '1') {
                    $strPasoTexto = '2.2.3 Instituciones de Crédito';
                } else {
                    $strPasoTexto = 'Instituciones de Crédito';
                }

                if (($arrayApe2014[223]['resultado'] > 0) || ($res223 > 0)) {
                    $moneda = 'Pesos';
                    $pais = utf8Decode('México');
                } else {
                    $moneda = '';
                    $pais = '';
                }

                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPasoTexto), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, $moneda, 'LR', 0, 'C', $fill);
                $pdf->Cell(25, $altoLinea, $pais, 'LR', 0, 'C', $fill);
                //Residuo
                if (($arrayApe2014[223]['resultado'] / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($arrayApe2014[223]['resultado'] / $MilPesos, 0), $decimales), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($arrayApe2014[223]['resultado'] / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                //Residuo
                if (($res223 / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($res223 / $MilPesos, 0), $decimales), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($res223 / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Titulos y Valores'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat-b', '', 6.8);
                $pdf->Cell(70, $altoLinea, $nivel2 . 'Deuda Externa', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat', '', 6.8);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Organismos Financieros Internacionales'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Deuda Bilateral'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Titulos y Valores'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, $nivel3 . utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $altoLinea, number_format(0, 0), 'LR', 0, 'R', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat-b', '', 6.8);
                $pdf->Cell(70, $altoLinea, $nivel2 . utf8Decode('Subtotal de Deuda Pública a Largo Plazo'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                //Residuo
                if (($arrayApe2014[223]['resultado'] / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($arrayApe2014[223]['resultado'] / $MilPesos, 2), $decimales), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($arrayApe2014[223]['resultado'] / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                //Residuo
                if (($res223 / $MilPesos) == 0) {
                    $decimales = 0;
                } else {
                    $decimales = 0;
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($res223 / $MilPesos, 2), $decimales), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($res223 / $MilPesos, $decimales), 'LR', 0, 'R', $fill);
                }

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);

                $pdf->SetFont('Montserrat-b', '', 6.8);
                $pdf->Cell(70, $altoLinea, utf8Decode('Total de Otros Pasivos'), 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($arrayApe2014[2116]['resultado'] / $MilPesos, 2), 0), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($arrayApe2014[2116]['resultado'] / $MilPesos, 0), 'LR', 0, 'R', $fill);
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea, number_format(round($res2116 / $MilPesos, 2), 0), 'LR', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea, number_format($res2116 / $MilPesos, 0), 'LR', 0, 'R', $fill);
                }

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $altoLinea, '', 'LR', 0, 'L', $fill);

                $pdf->ln($altoLinea);
                $pdf->Cell(70, $altoLinea + 1, utf8Decode('Total de Deuda Pública y Otros Pasivos'), 'LRB', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea + 1, '', 'LRB', 0, 'L', $fill);
                $pdf->Cell(25, $altoLinea + 1, '', 'LRB', 0, 'L', $fill);

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea + 1, number_format(round($totalApe, 2), 0), 'LRB', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea + 1, number_format($totalApe, 0), 'LRB', 0, 'R', $fill);
                }

                if ($_SESSION[_S]['reportes'] == 'C117') {
                    $pdf->Cell(30, $altoLinea + 1, number_format(round($totalPeriodo, 0), 0), 'LRB', 0, 'R', $fill);
                } else {
                    $pdf->Cell(30, $altoLinea + 1, number_format($totalPeriodo, 0), 'LRB', 0, 'R', $fill);
                }

                $pdf->Ln($altoLinea);

                $pdf->SetFont('Montserrat-b', '', 6);
                $pdf->Cell(180, $altoLinea + 5, utf8Decode('Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.'), 0, 0, 'L', $fill);
                // no break
            default:
                break;
        }
        break;
    default:
        break;
}

// Fin del código fuente
$pdf->Output();
