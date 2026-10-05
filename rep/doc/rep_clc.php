<?php

error_reporting(E_ALL);
session_start();
require_once dirname(__FILE__) . '/../../lib/helpers.php';
ini_set('memory_limit', '200M');
set_time_limit(500);

//librerias

//define('FPDF_FONTPATH_CG','../fnt/');   // Marcos: 09/feb/2016... para que se pueda impimir desde la contabilidad
require_once '../lib/reportetabla.1.8.php';
require_once '../../lib/AccesoDatos.php';
require_once '../../lib/scp/modelScp.php';
require_once '../../ent/Entidad.php';
require_once '../../ent/UnidadResponsable.php';
require_once '../../ent/UnidadEjecutora.php';
require_once '../../lib/FicheroSefip.php';

require_once '../../lib/Math.php';
require_once '../../scp/afectaciones/ValidaGarantias.php';
//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    public $datosAnt;
    public $ultBorde = 0;
    public $seccion = '{nb}';
    public $importe = '{importe}';
    public $ur_clave = '';
    public $clc_id = '';
    public $pagina = 0;

    public function setDatosAnt($tmp)
    {
        $this->datosAnt = $tmp;
    }
    public function getDatosAnt()
    {
        return $this->datosAnt;
    }

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
    }

    public function Footer($lastPage = true)
    {
        if ($this->getDatos() != null) {
            $datosAnt = $this->getDatosAnt();
            $datos = $this->getDatos();
            $fila = '';

            if ($datos['ur_clave'] != $datosAnt['ur_clave'] || $datos['clc_id'] != $datosAnt['clc_id']) {
                $fila = $datosAnt;
                //para corregir la firma del lote
                if ($lastPage) {
                    $fila = $datos;
                }
            } else {
                $fila = $datos;
            }

            $this->SetY(-45);

            // Marcos: 09/feb/2016... para que se pueda impimir desde la contabilidad
            // Si viene de la contabilidad NO imprimimos la leyenda
            // ni las firmas...

            if ((isset($_POST['scg_vc']) && $_POST['scg_vc'] == '1') || (isset($_POST['consulta']) && $_POST['consulta'] == 'SI')) {
                // nada
            } else {
                switch ($datos['ur_clave']) {
                    /* LEYENDA ESPECIAL PARA ESTAS UR´S */
                    case 116:
                    case 901:
                    case 902:
                        if ($datos['automatica'] != 1) {
                            $this->SetFont('Arial', 'B', 5.5);
                            $this->MultiCell(260, 2.5, utf8Decode('LOS SERVIDORES PUBLÍCOS QUE SUSCRIBIMOS LA PRESENTE CUENTA POR LIQUIDAR CERTIFICADA, DECLARAMOS QUE LA MISMA SE EMITE PARA CUMPLIR EN TIEMPO Y FORMA CON  LA TRANSFERENCIA DE RECURSOS QUE POR MANDATO LEGAL DEBE REALIZARSE A FAVOR DEL EJECUTOR DE GASTO AQUÍ SEÑALADO.QUEDANDO  A CARGO DE LOS EJECUTORES DE GASTO BENEFICIARIOS DE LA TRANSFERENCIA DE RECURSOS LA OBLIGACIÓN DE CUMPLIR CON EL CONTENIDO DE LOS ARTÍCULOS 70 DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA; 23A DE LA LEY DE COORDINACIÓN FISCAL PARA EL ESTADO DE OAXACA; 163 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA, Y DEMÁS DISPOSICIONES  APLICABLES, ASÍ COMO DEL RESGUARDO Y CUSTODIA DE TODA LA DOCUMENTACIÓN JUSTIFICATIVA Y COMPROBATORIA QUE SE ORIGINE. ASIMISMO SON RESPONSABLES DE ENTREGAR LOS DOCUMENTOS, INFORMES Y DEMÁS SOPORTES QUE LES REQUIERAN LOS ÓRGANOS DE CONTROL Y FISCALIZACIÓN FEDERALES Y ESTATALES COMPETENTES.'), 0, 'J');
                            $this->ln(1);
                        }

                        break;
                    default:

                        $this->SetFont('Arial', 'B', 5.5);

                        if ($datos != null) {
                            if ($datos['tcl_id'] == 'B') {

                                if ($datos['leyenda'] == 'Anterior') {
                                    $this->MultiCell(260, 2.5, utf8Decode('LOS SERVIDORES PÚBLICOS QUE SUSCRIBIMOS LA PRESENTE CUENTA POR LIQUIDAR CERTIFICADA, DECLARAMOS QUE LAS PARTIDAS AFECTADAS, FUENTE DE FINANCIAMIENTO E IMPORTES CONSIGNADOS, SE ENCUENTRAN RESPALDADOS CON LA DOCUMENTACIÓN QUE LOS COMPRUEBA Y JUSTIFICA, MISMA QUE EN ORIGINAL SE QUEDA EN NUESTRO RESGUARDO Y CUSTODIA, RECONOCIENDO QUE HEMOS CUMPLIDO CON LOS REQUISITOS FISCALES Y ADMINISTRATIVOS VIGENTES. LO ANTERIOR, CON FUNDAMENTO EN LOS ARTÍCULOS 4 Y 48 DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA Y 58, 61, DEL 186 AL 193 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA.'), 0, 'J');
                                } else {
                                    $this->MultiCell(260, 2.5, utf8Decode('"LOS SERVIDORES PÚBLICOS QUE SUSCRIBIMOS LA PRESENTE CUENTA POR LIQUIDAR CERTIFICADA, DECLARAMOS QUE LAS PARTIDAS AFECTADAS, FUENTE DE FINANCIAMIENTO E IMPORTES CONSIGNADOS, SE ENCUENTRAN RESPALDADOS CON LA DOCUMENTACIÓN QUE LOS COMPRUEBA Y JUSTIFICA, MISMA QUE EN ORIGINAL SE QUEDA EN NUESTRO RESGUARDO Y CUSTODIA, RECONOCIENDO QUE HEMOS CUMPLIDO CON LOS REQUISITOS FISCALES Y ADMINISTRATIVOS VIGENTES. LO ANTERIOR, CON FUNDAMENTO EN LOS ARTÍCULOS 4 Y 48 DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA Y 58, 61, DEL 186 AL 193 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA", ASI MISMO, AUTORIZAMOS A LA SECRETARIA DE FINANZAS, PARA REALIZAR EL PAGO DIRECTO AL PROVEEDOR, EL CUAL SE INDICA EN EL CUERPO DE LA PRESENTE CLC'), 0, 'J');
                                }
                            } else {
                                $this->MultiCell(260, 2.5, utf8Decode('LOS SERVIDORES PÚBLICOS QUE SUSCRIBIMOS LA PRESENTE CUENTA POR LIQUIDAR CERTIFICADA, DECLARAMOS QUE LAS PARTIDAS AFECTADAS, FUENTE DE FINANCIAMIENTO E IMPORTES CONSIGNADOS, SE ENCUENTRAN RESPALDADOS CON LA DOCUMENTACIÓN QUE LOS COMPRUEBA Y JUSTIFICA, MISMA QUE EN ORIGINAL SE QUEDA EN NUESTRO RESGUARDO Y CUSTODIA, RECONOCIENDO QUE HEMOS CUMPLIDO CON LOS REQUISITOS FISCALES Y ADMINISTRATIVOS VIGENTES. LO ANTERIOR, CON FUNDAMENTO EN LOS ARTÍCULOS 4 Y 48 DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA Y 58, 61, DEL 186 AL 193 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA.'), 0, 'J');
                            }
                        }
                        $this->ln(1);
                        break;
                }

                //SEGÚN EL NÚMERO DE FIRMAS Y ALGUNAS UR'S ESPECÍFICAS SE MUESTRAN LAS FIRMAS REQUERIDAS

                switch ($fila['clc_firmas']) {
                    //MUNICIPIOS (CLC'S DE INVERSIÓN PÚBLICA)
                    case 2:
                        if ($fila['ur_clave'] == '902') {
                            $this->ln(2);
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoTitular']), 0, 0, 'C');
                            $this->ln(12);
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreTitular']), 0, 0, 'C');
                        } elseif ($fila['ur_clave'] . $fila['tcl_id'] == '116J') {
                            //UR 315 CLC'S DE DEUDA PÚBLICA
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, 'JEFE ADMINISTRATIVO', 0, 0, 'C');
                            $this->Cell(130, 2, 'TITULAR DEL EJECUTOR DEL GASTO', 0, 0, 'C');
                            $this->Cell(130, 2, '', 0, 1, 'C');
                            $this->ln(10);
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreTitular']), 0, 1, 'C');
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoTitular']), 0, 0, 'C');
                        } elseif ($fila['ur_clave'] == 116 && isset($_SESSION[_S]['ue_id']) && ($_SESSION[_S]['ue_id'] == 005 || $_SESSION[_S]['ue_id'] == 006) && $fila['tcl_id'] == 'M') {
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode('TITULAR DEL EJECUTOR DEL GASTO'), 0, 0, 'C');
                            $this->Cell(130, 2, '', 0, 1, 'C');
                            $this->ln(5);
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreTitular']), 0, 1, 'C');
                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoTitular']), 0, 0, 'C');
                            $this->ln(10);

                            if ($fila['stc_id'] == '09' || ($_SESSION[_S]['ue_id'] == 005 && $fila['stc_id'] == '13')) {
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(130, 2, utf8Decode('ROSARIO LEOCADIO LÓPEZ'), 0, 0, 'C');
                                $this->Cell(130, 2, utf8Decode('LANDO MATUS DELGADO'), 0, 0, 'C');
                                $this->ln(3);
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(130, 2, utf8Decode('JEFA DEL DEPARTAMENTO DE PROGRAMAS FEDERALES'), 0, 0, 'C');
                                $this->Cell(130, 2, utf8Decode('COORDINADOR DE PROGRAMAS FEDERALES Y CONTROL DE FONDOS'), 0, 1, 'C');
                                $this->ln(1);
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(130, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
                                $this->Cell(130, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');
                            } elseif ($_SESSION[_S]['ue_id'] == 006) {
                                if ($fila['stc_id'] == '7' || $fila['stc_id'] == '8') {
                                    $this->SetFont('Arial', '', 6.5);
                                    $this->Cell(130, 2, utf8Decode('C.P. ELIZABETH ADRIANA GÓMEZ GARCÍA'), 0, 0, 'C');
                                    $this->Cell(130, 2, utf8Decode('LORENA ROJAS RIVERA'), 0, 0, 'C');
                                    $this->ln(3);
                                    $this->SetFont('Arial', '', 6.5);
                                    $this->Cell(130, 2, utf8Decode('JEFA DEL DEPARTAMENTO DE CONTROL DE INGRESOS'), 0, 0, 'C');
                                    $this->Cell(130, 2, utf8Decode('DIRECTORA DE INGRESOS Y RECAUDACIÓN'), 0, 1, 'C');
                                    $this->ln(1);
                                    $this->SetFont('Arial', '', 6.5);

                                    $this->Cell(130, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
                                    $this->Cell(130, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');
                                } elseif ($fila['stc_id'] == '5') {
                                    $this->SetFont('Arial', '', 6.5);
                                    $this->Cell(130, 2, utf8Decode('ARQ. ROSA ESTHER VILLALOBOS RUIZ'), 0, 0, 'C');
                                    $this->Cell(130, 2, utf8Decode('LORENA ROJAS RIVERA'), 0, 0, 'C');
                                    $this->ln(3);
                                    $this->SetFont('Arial', '', 6.5);
                                    $this->Cell(130, 2, utf8Decode('JEFA DEL DEPARTAMENTO DE ADMINISTRACIÓN TRIBUTARIA'), 0, 0, 'C');
                                    $this->Cell(130, 2, utf8Decode('DIRECTORA DE INGRESOS Y RECAUDACIÓN'), 0, 1, 'C');
                                    $this->ln(1);
                                    $this->SetFont('Arial', '', 6.5);

                                    $this->Cell(130, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
                                    $this->Cell(130, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');
                                }
                            }
                        } elseif ($fila['ur_clave'] . $fila['tcl_id'] == '116L' && $fila['stc_id'] == '02') {

                            $this->SetFont('Arial', '', 6.5);
                            $this->Cell(130, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode('TITULAR DEL EJECUTOR DEL GASTO'), 0, 0, 'C');
                            $this->Cell(130, 2, '', 0, 1, 'C');
                            $this->ln(5);

                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_nombreTitular']), 0, 1, 'C');

                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoAdministrativo']), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode($fila['clc_cargoTitular']), 0, 0, 'C');
                            $this->ln(10);

                            $this->Cell(130, 2, utf8Decode('NORMA OJEDA SARMIENTO'), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode('LANDO MATUS DELGADO'), 0, 0, 'C');
                            $this->ln(3);

                            $this->Cell(130, 2, utf8Decode('JEFA DEL DEPARTAMENTO DE FIDEICOMISOS PÚBLICOS'), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode('COORDINADOR DE PROGRAMAS FEDERALES Y CONTROL DE FONDOS'), 0, 1, 'C');
                            $this->ln(1);

                            $this->Cell(130, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
                            $this->Cell(130, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');
                        } else {
                            // SON LAS CLCS DE CARGA MASIVA QUE HACE RECURSOS HUMANOS
                            if (($fila['tem_carga'] != null or $fila['tem_carga'] != '') && $fila['tcl_id'] != 'B') {
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(132, 2, utf8Decode('DIRECTOR DE RECURSOS HUMANOS'), 0, 0, 'C');
                                $this->Cell(132, 2, utf8Decode('SECRETARIO DE ADMINISTRACIÓN'), 0, 1, 'C');
                                $this->Cell(132, 2, '', 0, 1, 'C');
                                $this->ln(10);
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(132, 2, utf8Decode($fila['clc_nombreRecursos']), 0, 0, 'C');
                                $this->Cell(132, 2, utf8Decode($fila['clc_nombreAdministracion']), 0, 1, 'C');
                            } else {
                                //EL RESTO DE LAS UR'S
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(130, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
                                $this->Cell(130, 2, utf8Decode('TITULAR DEL EJECUTOR DEL GASTO'), 0, 0, 'C');
                                $this->Cell(130, 2, '', 0, 1, 'C');
                                $this->ln(10);
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(130, 2, utf8Decode($fila['clc_nombreAdministrativo']), 0, 0, 'C');
                                $this->Cell(130, 2, utf8Decode($fila['clc_nombreTitular']), 0, 1, 'C');
                                $this->SetFont('Arial', '', 6.5);
                                $this->Cell(130, 2, utf8Decode($fila['clc_cargoAdministrativo']), 0, 0, 'C');
                                $this->Cell(130, 2, utf8Decode($fila['clc_cargoTitular']), 0, 0, 'C');
                            }
                        }
                        break;
                    case 3:
                        $this->SetFont('Arial', 'B', 6.5);
                        $this->Cell(86.6, 2, 'JEFE ADMINISTRATIVO', 0, 0, 'C');
                        $this->Cell(86.6, 2, 'TITULAR DEL EJECUTOR DEL GASTO', 0, 0, 'C');
                        $this->Cell(86.6, 2, utf8Decode('SECRETARIO DE ADMINISTRACIÓN'), 0, 1, 'C');

                        $this->Cell(130, 2, '', 0, 1, 'C');
                        $this->ln(1);
                        $this->SetFont('Arial', 'B', 6.5);
                        $this->Cell(86.6, 2, utf8Decode($fila['clc_nombreAdministrativo']), 0, 0, 'C');
                        $this->Cell(86.6, 2, utf8Decode($fila['clc_nombreTitular']), 0, 0, 'C');
                        $this->Cell(86.6, 2, utf8Decode($fila['clc_nombreAdministracion']), 0, 1, 'C');

                        $this->Cell(130, 2, '', 0, 1, 'C');
                        $this->SetFont('Arial', 'B', 6.5);
                        $y = $this->getY();
                        $x = $this->getX();
                        $this->setX(10);
                        $this->multicell(86.6, 3, utf8Decode($fila['clc_cargoAdministrativo']), 0, 'C');
                        $this->setY($y);
                        $this->setX($x + 86);
                        $x = $this->getX();
                        $this->multicell(86.6, 3, utf8Decode($fila['clc_cargoTitular']), 0, 'C');
                        $this->setY($y);
                        $this->setX($x + 70);
                        break;

                    case 4:
                        $this->SetFont('Arial', '', 6.5);
                        $this->Cell(65, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
                        $this->Cell(65, 2, utf8Decode('TITULAR DEL EJECUTOR DEL GASTO'), 0, 0, 'C');
                        $this->Cell(65, 2, utf8Decode('DIRECTOR DE RECURSOS HUMANOS'), 0, 0, 'C');
                        $this->Cell(65, 2, utf8Decode('SECRETARIO DE ADMINISTRACIÓN'), 0, 1, 'C');

                        $this->ln(10);
                        $this->SetFont('Arial', '', 6.5);

                        $this->Cell(65, 2, utf8Decode($fila['clc_nombreAdministrativo']), 0, 0, 'C');
                        $this->Cell(65, 2, utf8Decode($fila['clc_nombreTitular']), 0, 0, 'C');
                        $this->Cell(65, 2, utf8Decode($fila['clc_nombreRecursos']), 0, 0, 'C');
                        $this->Cell(65, 2, utf8Decode($fila['clc_nombreAdministracion']), 0, 1, 'C');

                        $this->SetFont('Arial', '', 6.5);
                        $yfirma = $this->GetY();
                        $xfirma = $this->GetX();

                        $this->MultiCell(65, 2, utf8Decode($fila['clc_cargoAdministrativo']), 2, 'C');
                        $this->SetXY($xfirma + 66, $yfirma);
                        $this->MultiCell(65, 2, utf8Decode($fila['clc_cargoTitular']), 2, 'C');
                        break;
                } // Fin del switch de las firmas
            } // Marcos.   fin de la condición para no incluir ésto si lo imprimimos desde la contablidad
        }
    } //fin de la funcion footer

    public function Header()
    { //var_dump( $this->getDatos()) ; die;
        //Encabezado del documento
        if ($this->getDatos() != null) {
            $datos = $this->getDatos();

            if ((isset($_SESSION[_S]['ejercicio']) && $_SESSION[_S]['ejercicio'] == APP_EJERCICIO . '_localhost') || (isset($_SESSION[_S]['ejercicio']) && $_SESSION[_S]['ejercicio'] == APP_EJERCICIO . 'Calidad')) {
                $iy = $this->GetY();
                $ix = $this->GetX();
                $this->SetFont('Arial', 'B', 50);
                $this->SetTextColor(234, 41, 64);
                $this->RotatedText($ix + 35, $iy + 180, 'PRUEBAS', 40);
                $this->RotatedText($ix + 130, $iy + 190, 'PRUEBAS', 40);
                $this->RotatedText($ix + 25, $iy + 105, 'PRUEBAS', 40);
                $this->RotatedText($ix + 130, $iy + 110, 'PRUEBAS', 40);
            }

            // Marcos: 09/feb/2016... para que se pueda impimir desde la contabilidad
            // Si viene de la contabilidad NO imprimimos la leyenda
            if ((isset($_POST['scg_vc']) && $_POST['scg_vc'] == '1')) {
                $ix = $this->GetX();
                $iy = $this->GetY();
                $this->SetFont('Arial', 'B', 50);
                $this->SetTextColor(200, 200, 200);
                $this->RotatedText($ix + 45, $iy + 165, 'VALIDACION', 45);
                $this->RotatedText($ix + 150, $iy + 160, 'CONTABLE', 45);
                $this->RotatedText($ix + 45, $iy + 85, 'VALIDACION', 45);
                $this->RotatedText($ix + 150, $iy + 80, 'CONTABLE', 45);
            } else {
                if ((isset($_POST['tipoRep']) && $_POST['tipoRep'] == 'CONTABILIDAD')) {
                    $ix = $this->GetX();
                    $iy = $this->GetY();
                    $this->SetFont('Arial', 'B', 50);
                    $this->SetTextColor(200, 200, 200);
                    $this->RotatedText($ix + 45, $iy + 165, 'CONTABILIDAD', 45);
                    $this->RotatedText($ix + 150, $iy + 160, 'CONTABILIDAD', 45);
                    $this->RotatedText($ix + 45, $iy + 85, 'CONTABILIDAD', 45);
                    $this->RotatedText($ix + 150, $iy + 80, 'CONTABILIDAD', 45);
                } else {
                    $recursofederal = false;
                    if ($datos['clc_recursoFederal'] == '1' && $datos['ecl_id'] == '17' && $_SESSION[_S]['nvj_id'] <= 2) {
                        $recursofederal = true;
                    }
                    if ($datos['ecl_id'] == 1 || $datos['ecl_id'] == 2) {
                        $ix = $this->GetX();
                        $iy = $this->GetY();
                        $this->SetFont('Arial', 'B', 50);
                        $this->SetTextColor(200, 200, 200);
                        $this->RotatedText($ix + 30, $iy + 160, 'SIN VALIDEZ', 45);
                        $this->RotatedText($ix + 150, $iy + 160, 'SIN VALIDEZ', 45);
                        $this->RotatedText($ix, $iy + 80, 'SIN VALIDEZ', 45);
                        $this->RotatedText($ix + 150, $iy + 80, 'SIN VALIDEZ', 45);
                    } elseif ((
                        ($datos['ocl_id'] != 3 && $datos['ecl_id'] > 3 && !$recursofederal) // SI NO ES DE ORIGEN DE CARGA MASIVA Y EL ESTADO ES MAYOR A 3
                         || ($datos['ocl_id'] == 3 && $datos['ecl_id'] > 3 && !$recursofederal)
                    ) // O ES DE ORIGEN DE CARGA MASIVA Y EL ESTADO ES MAYOR A TRES
                        // NO ES DE RECURSO FEDERAL
                    ) {
                        // Cuando no tengo "reimpresion" se manda marca de agua
                        if (!isset($_POST['reimprimir'])) {
                            $ix = $this->GetX();
                            $iy = $this->GetY();
                            $this->SetFont('Arial', 'B', 50);
                            $this->SetTextColor(200, 200, 200);
                            $this->RotatedText($ix + 50, $iy + 160, 'COPIA', 45);
                            $this->RotatedText($ix + 150, $iy + 160, 'COPIA', 45);
                            $this->RotatedText($ix + 50, $iy + 80, 'COPIA', 45);
                            $this->RotatedText($ix + 150, $iy + 80, 'COPIA', 45);
                        }
                        /*Este se aumento por la consulta de las UR'S */
                    } elseif (isset($_POST['reimprimir'])) {
                        $ix = $this->GetX();
                        $iy = $this->GetY();
                        $this->SetFont('Arial', 'B', 50);
                        $this->SetTextColor(200, 200, 200);
                        $this->RotatedText($ix + 50, $iy + 160, 'COPIA', 45);
                        $this->RotatedText($ix + 150, $iy + 160, 'COPIA', 45);
                        $this->RotatedText($ix + 50, $iy + 80, 'COPIA', 45);
                        $this->RotatedText($ix + 150, $iy + 80, 'COPIA', 45);
                    }
                }
            } // Marcos.   fin de la consulta para otro texto si viene de la contabilidad

            $this->SetMargins(10, 0);
            $this->SetTextColor(0, 0, 0);
            $this->SetBorders(0);
            $this->SetY(2);
            $this->ln(2);
            $ix = $this->GetX();
            $iy = $this->GetY();

            $this->SetFont('Arial', 'B', 6);
            $this->SetXY($ix, $iy + 4);
            if (empty($_POST['reimprimir'])) {
                $this->Cell(20, 5, utf8Decode(date('d/m/y') . ' - ' . date('H:i:s') . '        '), 0, 1, 'L');
            } else {
                /**
                 * Autor: Eduardo Venegas
                 * Comentario: Si manda la fecha por "default", se coloca la fecha y hora actual
                 */
                if ($_POST['reimprimir'] == 'dd/mm/yy - 00:00:00') {
                    $this->Cell(20, 5, utf8Decode(date('d/m/y') . ' - ' . date('H:i:s') . '        '), 0, 1, 'L');
                } else {
                    $this->Cell(20, 5, utf8Decode($_POST['reimprimir'] . '        '), 0, 1, 'L');
                }
            }
            $this->SetXY($ix, $iy + 6);

            if ($datos['ur_clave'] != $this->ur_clave || $datos['clc_id'] != $this->clc_id) {
                $this->ur_clave = $datos['ur_clave'];
                $this->clc_id = $datos['clc_id'];

                if ($this->PageNo() != 1) {
                    $pi = $this->page - $this->pagina;
                    $pf = $this->page;
                    for ($n = $pi; $n <= $pf; $n++) {
                        $this->pages[$n] = str_replace($this->seccion, $this->pagina, $this->pages[$n]);
                    }
                }
                $this->seccion = '{nb' . $datos['ur_clave'] . $datos['clc_id'] . '}';
                $this->pagina = 0;
            }

            //Número de página y de sección
            $this->pagina++;
            $this->Cell(20, 5, utf8Decode('Página ') . $this->pagina . '/' . $this->seccion, 0, 0, 'L');

            //Variantes del encabezado segun tipo de clc
            $datosExtraServPers = '';
            switch ($datos['tcl_id']) {
                //Clc de servicios personales***
                case 'A':
                    if ($datos['mno_id'] != '' or $datos['mno_id'] != null) {
                        if ($datos['mno_nombre'] == 'NO APLICA') {
                            $datosExtraServPers .= '';
                        } else {
                            $datosExtraServPers .= ' - ' . $datos['mno_nombre'];
                        }
                    }

                    if ($datos['smn_id'] != '' or $datos['smn_id'] != null) {
                        if ($datos['smn_nombre'] == 'NO APLICA') {
                            $datosExtraServPers .= '';
                        } else {
                            $datosExtraServPers .= ' - ' . $datos['smn_nombre'];
                        }
                    }

                    break;

                    //Clc de gastos de operacion***
                case 'B':
                    break;
            }

            $this->SetFont('Arial', 'B', 8);
            $this->SetXY($ix, $iy + 4);
            $ytmp = $this->GetY();

            $this->Cell(260, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 0, 'C');
            $this->SetXY($ix - 60, $ytmp);
            $this->SetFont('Arial', '', 6);
            $this->Cell(15, 5, 'Folio...:', 0, 0, 'R');
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(45, 5, $datos['clc_id'], 0, 0, 'L');

            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(260, 5, $datos['ur_clave'] . '  -' . ' ' . utf8Decode($datos['ur_nombre']), 0, 0, 'C');
            $this->SetXY($ix - 60, $ytmp);
            $this->SetFont('Arial', '', 6);
            $this->Cell(15, 5, 'Fecha...:', 0, 0, 'R');
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(45, 5, $datos['clc_fechaElaboracion'], 0, 1, 'L');

            $this->SetXY($ix, $ytmp + 3);
            $this->SetFont('Arial', 'B', 8);
            $ytmp = $this->GetY();
            $this->Cell(260, 5, 'CUENTA POR LIQUIDAR CERTIFICADA (CLC) DE EROGACIONES PRESUPUESTALES PARA PAGO', 0, 0, 'C');

            $this->SetXY($ix - 60, $ytmp);
            $this->SetFont('Arial', '', 6);
            $this->Cell(15, 5, 'Modalidad de Pago...:', 0, 0, 'R');
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(45, 5, $datos['mpc_nombre'], 0, 1, 'L');
            $this->SetFont('Arial', 'B', 8);
            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();

            $subtipo = '';

            if ($datos['stc_id'] != 0 && $datos['tcl_id'] != 'F' && $datos['tcl_id'] != 'G' && $datos['tcl_id'] != 'H') {
                $subtipo = ' - ' . $datos['stc_nombre'];
            }

            $this->Cell(260, 5, utf8Decode($datos['tcl_nombre'] . $subtipo . $datosExtraServPers), 0, 0, 'C');
            $this->SetXY($ix - 60, $ytmp);

            if ($datos['mpc_id'] != 4) {
                $this->SetFont('Arial', '', 6);
                $this->Cell(15, 5, 'Banco...:', 0, 0, 'R');
                $this->SetFont('Arial', 'B', 6);
                $dx = $this->GetX();
                $dy = $this->GetY();
                $this->setXY($dx, $dy + 1);
                $this->MultiCell(34, 2.5, utf8Decode($datos['iba_nombre']), 0, 'L');
                $ytmp = $this->GetY();

                $this->SetXY($ix - 60, $ytmp);
                $this->SetFont('Arial', '', 6);
                $this->Cell(15, 5, 'Clabe Interbancaria...:', 0, 0, 'R');
                $this->SetFont('Arial', 'B', 6);
                $this->Cell(45, 5, $datos['bur_id'], 0, 1, 'L');
                $this->SetFont('Arial', 'B', 8);
                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();

                if ($datos['clc_rectificacion'] != 0 && $datos['automatica'] != 1) {
                    $this->SetXY($ix - 60, $ytmp);
                    $this->SetFont('Arial', '', 6);
                    $this->Cell(15, 5, 'Rectificación de Origen...:', 0, 0, 'R');
                    $this->SetFont('Arial', 'B', 6);
                    $this->Cell(45, 5, $datos['clc_rectificacion'], 0, 1, 'L');
                    $this->SetFont('Arial', 'B', 8);
                    $this->SetXY($ix, $ytmp + 3);
                    $ytmp = $this->GetY();
                }
            } else {
                $this->SetFont('Arial', '', 6);
                $this->Cell(15, 5, 'Banco...:', 0, 0, 'R');
                $this->SetFont('Arial', 'B', 6);
                $dx = $this->GetX();
                $dy = $this->GetY();
                $this->setXY($dx, $dy + 1);
                $this->MultiCell(45, 2.5, utf8Decode('XXXXXXXXXXXXXXXXXXXX'), 0, 'L');
                $ytmp = $this->GetY();

                $this->SetXY($ix - 60, $ytmp);
                $this->SetFont('Arial', '', 6);
                $this->Cell(15, 3, 'Clabe Interbancaria...:', 0, 0, 'R');
                $this->SetFont('Arial', 'B', 6);
                $this->Cell(45, 3, '000000000000000000', 0, 1, 'L');
                $this->SetFont('Arial', 'B', 8);
                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();
                $this->ln(1);

                $this->SetXY($ix - 60, $ytmp);
                $this->SetFont('Arial', '', 6);
                $this->Cell(15, 5, 'Banco...:', 0, 0, 'R');
                $this->SetFont('Arial', 'B', 6);
                $dx = $this->GetX();
                $dy = $this->GetY();
                $this->setXY($dx, $dy + 1);
                $this->MultiCell(45, 2.5, utf8Decode($datos['iba_nombre']), 0, 'L');
                $ytmp = $this->GetY();

                $this->SetXY($ix - 60, $ytmp);
                $this->SetFont('Arial', '', 6);
                $this->Cell(15, 3, 'Clabe Interbancaria...:', 0, 0, 'R');
                $this->SetFont('Arial', 'B', 6);
                $this->Cell(45, 3, $datos['bur_id'], 0, 1, 'L');
                $this->SetFont('Arial', 'B', 8);
                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();
            }

            //Etiqueta de C. Secretario de Finanzas
            $this->SetXY($ix, $ytmp + 5);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 7);
            $this->Cell(60, 5, 'C.SECRETARIO DE FINANZAS', 0, 0, 'L');

            // //Datos Linea de Captura
            if ($datos['clc_lineaCaptura'] != '' && $datos['mpc_id'] == 2) {
                $this->SetXY($ix - 60, $ytmp);
                $this->SetFont('Arial', '', 6);
                $this->Cell(15, 5, 'LINEA DE CAPTURA', 0, 0, 'R');
                $this->SetFont('Arial', 'B', 6);
                $this->Cell(40, 5, utf8Decode($datos['clc_lineaCaptura']), 0, 1, 'L');
            }

            //Etiqueta presente
            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 7);
            $this->Cell(60, 5, 'P R E S E N T E', 0, 1, 'L');

            $impresion = false;
            if ($datos['tcl_id'] == 'F' || $datos['tcl_id'] == 'G' || $datos['tcl_id'] == 'H') {
                $impresion = true;
            }

            if ($impresion == true) {
                $this->SetXY($ix, $ytmp + 5);
                $ytmp = $this->GetY();
                $this->SetFont('Arial', '', 6);

                $this->Cell(200, 5, 'POR MEDIO DEL PRESENTE, SIRVA MINISTRAR RECURSOS FINANCIEROS POR LA CANTIDAD DE $ ' . utf8Encode($this->importe) . ' ', 0, 0, 'L');

                $this->SetXY($ix, $ytmp + 5);
                $ytmp = $this->GetY();
                $this->SetFont('Arial', 'B', 6.5);
                $this->Cell(34, 3, 'OBRA:', 0, 0, 'L');

                $this->MultiCell(230, 2.5, $datos['oa_id'] . '-' . utf8Decode($datos['oa_nombre']), 0, 'L', false);

                $long = strlen($datos['oa_id'] . $datos['oa_nombre']);
                if ($long >= 590) {
                    $this->SetXY($ix, $ytmp + 9);
                } else {
                    $this->SetXY($ix, $ytmp + 3);
                }
                $ytmp = $this->GetY();
                $this->SetFont('Arial', 'B', 6.5);
                $this->Cell(34, 5, 'OFICIO DE AUTORIZACION:', 0, 0, 'L');
                $this->Cell(121, 5, $datos['ore_oficioAut'], 0, 0, 'L');
                $this->Cell(25, 5, 'No.OBRA SSPP:', 0, 0, 'L');
                $this->Cell(20, 5, $datos['oa_numobra'], 0, 0, 'L');

                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();
                $this->SetFont('Arial', 'B', 6.5);
                $this->Cell(34, 5, 'SUBFUENTE:', 0, 0, 'L');
                $this->Cell(121, 5, utf8Decode($datos['sft_nombre']), 0, 0, 'L');
                $this->Cell(25, 5, 'EJERCICIO:', 0, 0, 'L');
                $this->Cell(20, 5, $datos['cfi_anio'], 0, 0, 'L');
                $this->Cell(20, 5, 'REFERENCIA:', 0, 0, 'L');
                $this->Cell(20, 5, $datos['oa_' . APP_EJERCICIO_ANTERIOR], 0, 0, 'L');

                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();
                $this->SetFont('Arial', 'B', 6.5);
                $this->Cell(34, 5, 'APORTACION:', 0, 0, 'L');
                $this->Cell(121, 5, $datos['ter_nombre'], 0, 0, 'L');
                $this->Cell(25, 5, 'AVANCE FISICO:', 0, 0, 'L');
                $this->Cell(20, 5, number_format(floatval($datos['obr_avanceFisico']), 2) . '%', 0, 0, 'L');
                $this->Cell(30, 5, 'AVANCE FINANCIERO:', 0, 0, 'L');
                $this->Cell(30, 5, number_format(floatval($datos['obr_avanceFinanciero']), 2) . '%', 0, 0, 'L');

                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();
                $this->SetFont('Arial', 'B', 6.5);
                $this->Cell(34, 5, 'CLAVE DE FINANCIAMIENTO:', 0, 0, 'L');
                $this->Cell(121, 5, $datos['cfi_clave'] . '-' . utf8Decode($datos['cfi_nombre']) . '   EJERCICIO 20' . substr($datos['cfi_clave'], 7, 2), 0, 0, 'L');
            }

            //Datos origen recurso
            /* ESTA CLAVE DE FINANCIMIENTO ES PARA LOS DISTINTOS DE TIPO DE CLC QUE NOS OSON DE OBRA PUBLICA */

            if ($impresion == false) {
                if ($datos['tcl_id'] == 'I' and $datos['stc_id'] == 4) {
                    $this->SetXY($ix, $ytmp + 5);
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', '', 6);
                    $this->Cell(200, 5, 'POR MEDIO DEL PRESENTE, SIRVA MINISTRAR RECURSOS FINANCIEROS POR LA CANTIDAD DE $ ' . utf8Encode($this->importe) . ' ', 0, 0, 'L');

                    $this->SetXY($ix, $ytmp + 5);
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', 'B', 6.5);
                    $this->Cell(34, 3, 'OBRA:', 0, 0, 'L');
                    $this->MultiCell(230, 2.5, $datos['oa_id'] . '-' . utf8Decode($datos['oa_nombre']), 0, 'L', false);
                    $long = strlen($datos['oa_id'] . $datos['oa_nombre']);
                    if ($long >= 590) {
                        $this->SetXY($ix, $ytmp + 9);
                    } else {
                        $this->SetXY($ix, $ytmp + 3);
                    }
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', 'B', 6.5);
                    $this->Cell(34, 5, utf8Decode('OFICIO DE AUTORIZACIÓN:'), 0, 0, 'L');
                    $this->Cell(121, 5, $datos['ore_oficioAut'], 0, 0, 'L');
                    $this->Cell(25, 5, 'No.OBRA SSPP:', 0, 0, 'L');
                    $this->Cell(20, 5, $datos['oa_numobra'], 0, 0, 'L');

                    $this->SetXY($ix, $ytmp + 3);
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', 'B', 6.5);
                    $this->Cell(34, 5, 'SUBFUENTE:', 0, 0, 'L');
                    $this->Cell(121, 5, utf8Decode($datos['sft_nombre']), 0, 0, 'L');

                    $this->Cell(25, 5, 'EJERCICIO:', 0, 0, 'L');
                    $this->Cell(20, 5, $datos['cfi_anio'], 0, 0, 'L');
                    $this->Cell(20, 5, 'REFERENCIA:', 0, 0, 'L');
                    $this->Cell(20, 5, $datos['oa_' . APP_EJERCICIO_ANTERIOR], 0, 0, 'L');

                    $this->SetXY($ix, $ytmp + 3);
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', 'B', 6.5);
                    $this->Cell(34, 5, utf8Decode('APORTACIÓN:'), 0, 0, 'L');
                    $this->Cell(121, 5, $datos['ter_nombre'], 0, 0, 'L');

                    $this->Cell(30, 5, 'AVANCE FINANCIERO:', 0, 0, 'L');
                    $this->Cell(30, 5, number_format($datos['obr_avanceFinanciero'], 2) . '%', 0, 0, 'L');

                    $this->SetXY($ix, $ytmp + 3);
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', 'B', 6.5);
                    $this->Cell(34, 5, utf8Decode('DOCUMENTACIÓN ANEXA:'), 0, 0, 'L');
                    $xdocanexa = $this->GetX();
                    $this->ln(1);
                    $this->SetX($xdocanexa);
                    $this->MultiCell(228, 3, utf8Decode($datos['obr_documentacion']), 0, 'L');
                }

                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();
                $this->SetFont('Arial', 'B', 6.5);
                $this->Cell(34, 5, 'ORIGEN DEL RECURSO:', 0, 0, 'L');
                $this->Cell(166, 5, $datos['fte_id'] . $datos['sft_id'] . $datos['ori_id'] . '-' . utf8Decode($datos['ori_nombre']), 0, 0, 'L');

                //Datos clave de financiamientoa
                $this->SetXY($ix, $ytmp + 3);
                $ytmp = $this->GetY();
                $this->SetFont('Arial', 'B', 6.5);
                $this->Cell(34, 5, 'CLAVE DE FINANCIAMIENTO:', 0, 0, 'L');
                $this->Cell(166, 5, $datos['cfi_clave'] . '-' . utf8Decode($datos['cfi_nombre']) . '   EJERCICIO 20' . substr($datos['cfi_clave'], 7, 2), 0, 0, 'L');
            }

            if ($impresion == true) {
                switch ($datos['tcl_id']) {
                    case 'F':
                        $this->SetXY($ix, $ytmp + 3);
                        $ytmp = $this->GetY();
                        $this->SetFont('Arial', 'B', 6.5);
                        $this->Cell(34, 5, 'OBRA POR CONTRATO No.:', 0, 0, 'L');
                        $this->Cell(166, 5, $datos['obr_contrato'], 0, 0, 'L');

                        $this->SetXY($ix, $ytmp + 3);
                        $ytmp = $this->GetY();
                        $this->SetFont('Arial', 'B', 6.5);
                        $this->Cell(34, 5, 'FIANZAS', 0, 0, 'L');
                        $this->Cell(30, 5, 'CUMPLIMIENTO:', 0, 0, 'L');
                        $this->Cell(50, 5, $datos['obr_cumplimiento'], 0, 0, 'L');
                        $this->Cell(36, 5, 'VICIOS OCULTOS:', 0, 0, 'L');
                        $this->Cell(50, 5, $datos['obr_vicios'], 0, 0, 'L');
                        break;
                    case 'G':
                        break;
                    case 'H':
                        $this->SetXY($ix, $ytmp + 3);
                        $ytmp = $this->GetY();
                        $this->SetFont('Arial', 'B', 6.5);
                        $this->Cell(34, 5, 'OBRA POR CONTRATO No.:', 0, 0, 'L');
                        $this->Cell(166, 5, $datos['obr_contrato'], 0, 0, 'L');
                        $this->SetXY($ix, $ytmp + 3);
                        $ytmp = $this->GetY();
                        $this->SetFont('Arial', 'B', 6.5);
                        $this->Cell(34, 5, 'FIANZAS', 0, 0, 'L');
                        $this->Cell(30, 5, 'ANTICIPO:', 0, 0, 'L');
                        $this->Cell(50, 5, utf8Decode($datos['obr_anticipo']), 0, 0, 'L');
                        $this->Cell(36, 5, 'CUMPLIMIENTO:', 0, 0, 'L');
                        $this->Cell(50, 5, utf8Decode($datos['obr_cumplimiento']), 0, 0, 'L');
                        break;
                }
                $this->SetBorders(0);
                if ($datos['ur_clave'] == 902) {
                    $this->SetXY($ix, $ytmp + 3);
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', 'B', 6.5);
                    $this->Cell(34, 5, utf8Decode('DOCUMENTACIÓN ANEXA:'), 0, 0, 'L');
                    $xdocanexa = $this->GetX();
                    $this->ln(1);
                    $this->SetX($xdocanexa);
                    $this->MultiCell(228, 3, utf8Decode($datos['obr_documentacion']), 0, 'L');
                }
            }

            //Datos Concepto
            $this->SetXY($ix, $ytmp + 6);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 6.5);
            $this->Cell(34, 5, 'CONCEPTO:', 0, 0, 'L');
            $xconcepto = $this->GetX();
            $this->ln(4);
            $this->SetX($xconcepto);
            $this->MultiCell(228, 2.5, utf8Decode($datos['clc_concepto']), 0, 'L');
            $this->ln(2);
            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->ln(4);

            /* se le aumento a 11 por que el el concepto no tenia cupo */
            //Beneficiario
            $this->SetXY($ix, $ytmp + 11);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 6.5);
            $this->Cell(34, 5, 'BENEFICIARIO:', 0, 0, 'L');
            $xbenef = $this->GetX();
            $this->ln(1);
            $this->SetX($xbenef);
            $this->MultiCell(228, 2.5, utf8Decode($datos['clc_beneficiario']), 0, 'L');
            $this->ln(2);
            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->ln(4);

            switch ($datos['tcl_id']) {
                //Clc de servicios personales***
                case 'A':
                    //Tipo nomina
                    $this->SetXY($ix, $ytmp + 3);
                    $ytmp = $this->GetY();
                    $this->SetFont('Arial', 'B', 6.5);
                    $this->Cell(34, 5, 'TIPO NOMINA:', 0, 0, 'L');
                    $xnomina = $this->GetX();
                    $this->ln(1);
                    $this->SetX($xnomina);
                    $this->MultiCell(228, 2.5, utf8Decode($datos['tno_nombre']), 0, 'L');
                    $this->ln(2);
                    $this->SetXY($ix, $ytmp + 3);
                    $ytmp = $this->GetY();
                    $this->ln(4);
                    break;
                    //Clc de gastos de operacion***
                case 'B':
                    break;
            }

            //Encabezado de la tabla
            $this->SetFont('Arial', 'B', 9);
            $ytmp = $this->GetY();
            $this->Line(10, $ytmp + 1, 270, $ytmp + 1);
            $this->SetFont('Arial', '', 5.5);
            $this->ln(1.5);

            // Marcos: 16/feb/2017
            //Para la validación en la vinculación por clc
            $modo = '';
            if (isset($_POST['modo']) and !empty($_POST['modo'])) {
                $modo = $_POST['modo'];
            }

            switch ($modo) {
                // Comportamiento normal
                case '':
                case 'clc':
                    $nombreCol2 = 'No.CTRL';
                    break;
                case 'clcComp':
                    $nombreCol2 = '#COMPRO';
                    break;
            }

            switch ($datos['tcl_id']) {
                //SERVICIOS PERSONALES
                case 'A':
                    $this->Cell(8, 2, 'No.AFEC', 0, 0, 'L');
                    $this->Cell(11, 2, $nombreCol2, 0, 0, 'L');
                    $this->Cell(42, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                    $this->Cell(48, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                    $this->Cell(17, 2, 'REFERENCIA', 0, 0, 'L');
                    $this->Cell(20, 2, 'FECHA INI.', 0, 0, 'C');
                    $this->Cell(20, 2, 'FECHA FIN.', 0, 0, 'C');
                    $this->Cell(45, 2, 'PROVEEDOR', 0, 0, 'L');
                    $this->Cell(20, 2, 'R. F. C.', 0, 0, 'R');
                    $this->Cell(24, 2, 'IMPORTE NETO', 0, 0, 'R');
                    $this->Cell(5, 2, utf8Decode('+/-'), 0, 0, 'C');
                    break;
                    //GASTOS DE OPERACION

                case 'B':
                    $this->Cell(8, 2, 'No.AFEC', 0, 0, 'L');
                    $this->Cell(11, 2, $nombreCol2, 0, 0, 'L');
                    $this->Cell(39, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                    $this->Cell(38, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                    $this->Cell(16, 2, 'REFERENCIA', 0, 0, 'L');
                    $this->Cell(20, 2, 'FECHA REF.', 0, 0, 'C');
                    $this->Cell(36, 2, 'PROVEEDOR', 0, 0, 'L');
                    $this->Cell(22, 2, 'R. F. C.', 0, 0, 'L');
                    $this->Cell(22, 2, 'IMPORTE', 0, 0, 'R');
                    $this->Cell(22, 2, 'IMPORTE IVA', 0, 0, 'R');
                    $this->Cell(22, 2, 'IMPORTE NETO', 0, 0, 'R');
                    $this->Cell(4, 2, utf8Decode('+/-'), 0, 0, 'C');
                    break;
                case 'F':
                case 'G':
                case 'H':
                    $this->Cell(8, 2, 'No.AFEC', 0, 0, 'L');
                    $this->Cell(11, 2, $nombreCol2, 0, 0, 'L');
                    $this->Cell(39, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                    $this->Cell(38, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                    $this->Cell(16, 2, 'REFERENCIA', 0, 0, 'L');
                    $this->Cell(20, 2, 'FECHA REF.', 0, 0, 'C');
                    $this->Cell(36, 2, 'PROVEEDOR', 0, 0, 'L');
                    $this->Cell(22, 2, 'R. F. C.', 0, 0, 'L');
                    $this->Cell(22, 2, 'IMPORTE', 0, 0, 'R');
                    $this->Cell(22, 2, 'IMPORTE IVA', 0, 0, 'R');
                    $this->Cell(22, 2, 'TOTAL', 0, 0, 'R');
                    $this->Cell(4, 2, utf8Decode('+/-'), 0, 0, 'C');
                    break;
                case 'I':
                    switch ($datos['stc_id']) {
                        case 4:
                            $this->Cell(8, 2, 'No.AFEC', 0, 0, 'L');
                            $this->Cell(11, 2, $nombreCol2, 0, 0, 'L');
                            $this->Cell(39, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                            $this->Cell(38, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                            $this->Cell(16, 2, 'REFERENCIA', 0, 0, 'L');
                            $this->Cell(20, 2, 'FECHA REF.', 0, 0, 'C');
                            $this->Cell(36, 2, 'PROVEEDOR', 0, 0, 'L');
                            $this->Cell(22, 2, 'R. F. C.', 0, 0, 'L');
                            $this->Cell(22, 2, 'IMPORTE', 0, 0, 'R');
                            $this->Cell(22, 2, 'IMPORTE IVA', 0, 0, 'R');
                            $this->Cell(22, 2, 'TOTAL', 0, 0, 'R');
                            $this->Cell(4, 2, utf8Decode('+/-'), 0, 0, 'C');
                            break;

                        default:
                            $this->Cell(10, 2, 'No.AFEC', 0, 0, 'L');
                            $this->Cell(13, 2, $nombreCol2, 0, 0, 'L');
                            $this->Cell(42, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                            $this->Cell(42, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                            $this->Cell(18, 2, 'REFERENCIA', 0, 0, 'L');
                            $this->Cell(24, 2, 'FECHA REF.', 0, 0, 'C');
                            $this->Cell(50, 2, 'PROVEEDOR', 0, 0, 'L');
                            $this->Cell(24, 2, 'R. F. C.', 0, 0, 'L');
                            $this->Cell(31, 2, 'IMPORTE NETO', 0, 0, 'R');
                            $this->Cell(6, 2, utf8Decode('+/-'), 0, 0, 'C');
                            break;
                    }
                    break;
                case 'D':
                case 'E':
                case 'J':
                case 'K':
                case 'L':
                case 'M':
                    $this->Cell(10, 2, 'No.AFEC', 0, 0, 'L');
                    $this->Cell(13, 2, $nombreCol2, 0, 0, 'L');
                    $this->Cell(42, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                    $this->Cell(42, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                    $this->Cell(18, 2, 'REFERENCIA', 0, 0, 'L');
                    $this->Cell(24, 2, 'FECHA REF.', 0, 0, 'C');
                    $this->Cell(50, 2, 'PROVEEDOR', 0, 0, 'L');
                    $this->Cell(24, 2, 'R. F. C.', 0, 0, 'L');
                    $this->Cell(31, 2, 'IMPORTE NETO', 0, 0, 'R');
                    $this->Cell(6, 2, utf8Decode('+/-'), 0, 0, 'C');
                    break;

                case 'C':
                    switch ($datos['stc_id']) {
                        //tiene periodo y no tiene iva
                        case '01':
                            $this->Cell(8, 2, 'No.AFEC', 0, 0, 'L');
                            $this->Cell(11, 2, $nombreCol2, 0, 0, 'L');
                            $this->Cell(39, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                            $this->Cell(48, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                            $this->Cell(17, 2, 'REFERENCIA', 0, 0, 'L');
                            $this->Cell(20, 2, 'FECHA INI.', 0, 0, 'C');
                            $this->Cell(20, 2, 'FECHA FIN.', 0, 0, 'C');
                            $this->Cell(45, 2, 'PROVEEDOR', 0, 0, 'L');
                            $this->Cell(23, 2, 'R. F. C.', 0, 0, 'L');
                            $this->Cell(24, 2, 'IMPORTE NETO', 0, 0, 'R');
                            $this->Cell(5, 2, utf8Decode('+/-'), 0, 0, 'C');
                            break;
                            //no tiene periodo y tiene desglose de IVA
                        case '02':
                        case '03':
                        case '04':
                        case '05':
                            $this->Cell(8, 2, 'No.AFEC', 0, 0, 'L');
                            $this->Cell(11, 2, $nombreCol2, 0, 0, 'L');
                            $this->Cell(39, 2, 'CVE. PRESUP./CTA. CONTAB.', 0, 0, 'L');
                            $this->Cell(38, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
                            $this->Cell(16, 2, 'REFERENCIA', 0, 0, 'L');
                            $this->Cell(20, 2, 'FECHA REF.', 0, 0, 'C');
                            $this->Cell(36, 2, 'PROVEEDOR', 0, 0, 'L');
                            $this->Cell(22, 2, 'R. F. C.', 0, 0, 'L');
                            $this->Cell(22, 2, 'IMPORTE ', 0, 0, 'R');
                            $this->Cell(22, 2, 'IMPORTE IVA', 0, 0, 'R');
                            $this->Cell(22, 2, 'IMPORTE NETO', 0, 0, 'R');
                            $this->Cell(4, 2, utf8Decode('+/-'), 0, 0, 'C');
                            break;
                    }
                    break;
            }
            $this->Line(10, $ytmp + 3.8, 270, $ytmp + 3.8);
            $this->SetBorders($this->ultBorde);
            $ix = $this->GetX();
            $this->SetBorders(0);
            $this->SetFont('Arial', 'B', 10);
            $this->ln(2);
        } else {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('Arial', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($ix + 30, $iy + 160, 'SIN VALIDEZ', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'SIN VALIDEZ', 45);
            $this->RotatedText($ix, $iy + 80, 'SIN VALIDEZ', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'SIN VALIDEZ', 45);

            $this->SetMargins(10, 0);
            $this->SetTextColor(0, 0, 0);
            $this->SetBorders(0);
            //Si la clc no tiene detalles (afectaciones agregadas), la consulta no retornará datos, por lo tanto devolvemos un pdf vacio
            $ix = $this->GetX();
            $iy = $this->GetY();

            $this->SetFont('Arial', 'B', 8);
            $this->SetXY($ix, $iy + 4);
            $ytmp = $this->GetY();

            $this->Cell(260, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 0, 'C');
            $this->SetXY($ix - 60, $ytmp);
            $this->SetFont('Arial', '', 6);
            $this->Cell(15, 5, 'Folio...:', 0, 0, 'R');

            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();

            $this->SetXY($ix - 60, $ytmp);
            $this->SetFont('Arial', '', 6);
            $this->Cell(15, 5, 'Fecha...:', 0, 0, 'R');

            $this->SetXY($ix, $ytmp + 3);
            $this->SetFont('Arial', 'B', 8);
            $ytmp = $this->GetY();
            $this->Cell(260, 5, 'CUENTA POR LIQUIDAR CERTIFICADA (CLC) DE EROGACIONES PRESUPUESTALES PARA PAGO', 0, 0, 'C');

            $this->SetXY($ix - 60, $ytmp);
            $this->SetFont('Arial', '', 6);
            $this->Cell(15, 5, 'Modalidad de Pago...:', 0, 0, 'R');

            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();

            $this->SetXY($ix, $ytmp + 5);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 7);
            $this->Cell(60, 5, 'C.SECRETARIO DE FINANZAS', 0, 0, 'L');

            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 7);
            $this->Cell(60, 5, 'P R E S E N T E', 0, 1, 'L');

            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 6.5);
            $this->Cell(34, 5, 'ORIGEN DEL RECURSO:', 0, 0, 'L');

            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 6.5);
            $this->Cell(34, 5, 'CLAVE DE FINANCIAMIENTO:', 0, 0, 'L');
            $this->Cell(121, 5, 'EJERCICIO -', 0, 0, 'L');

            $this->SetXY($ix, $ytmp + 6);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 6.5);
            $this->Cell(34, 5, 'CONCEPTO:', 0, 0, 'L');

            $this->ln(2);
            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->ln(4);

            $this->SetXY($ix, $ytmp + 11);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 6.5);
            $this->Cell(34, 5, 'BENEFICIARIO:', 0, 0, 'L');

            $this->ln(2);
            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->ln(4);

            $this->SetFont('Arial', 'B', 9);
            $ytmp = $this->GetY();
            $this->Line(10, $ytmp + 1, 270, $ytmp + 1);
            $this->Line(10, $ytmp + 3.8, 270, $ytmp + 3.8);
        }
    }
}

// Marcos: 09/feb/2016... para que se pueda impimir desde la contabilidad sólo de la UR actual
if ((isset($_POST['scg_vc']) && $_POST['scg_vc'] == '1')) {
    // Para que la 116 pueda ver de otras URs
    if ($_SESSION[_S]['gpo_id'] == 1 and $_SESSION[_S]['ur_id'] == 16 and isset($_POST['ur']) && $_POST['ur'] != '') {
        $_POST['gpo_id'] = substr($_POST['ur'], 0, 1);
        $_POST['ur_id'] = substr($_POST['ur'], 1, 2);
        // La 116 sí puede imprimir de otras, ya que tiene su propia pantalla de vinculación por CLC
    } else {
        $_POST['gpo_id'] = $_SESSION[_S]['clasAdm-gpo'];
        $_POST['ur_id'] = $_SESSION[_S]['clasAdm-ur'];
    }
}

$whsub = '';
if (isset($_POST['tem_carga']) and !empty($_POST['tem_carga'])) {
    $whsub = (isset($_POST['gpo_id']) && isset($_POST['ur_id']) && isset($_POST['clc_id'])) ? ' WHERE gpo_id = "' . $_POST['gpo_id'] . '"  AND ur_id = "' . $_POST['ur_id'] . '"and clc_id = "' . $_POST['clc_id'] . '"' : '';
} else {
    if (isset($_POST['gpo_id']) && isset($_POST['ur_id']) && isset($_POST['ur_id'])) {
        $whsub .= 'WHERE gpo_id = "' . $_POST['gpo_id'] . '"  AND  ur_id = "' . $_POST['ur_id'] . '" and clc_id = "' . $_POST['clc_id'] . '"';
    }
}

// Marcos: 16/feb/2017
// agregamos agrupaciones a la impresión de CLC, para validar contra las pólizas en la vinculación
$modo = '';
if (isset($_POST['modo']) and !empty($_POST['modo'])) {
    $modo = $_POST['modo'];
}

/* -----------   SE MODIFICO LA CONSULTA PARA OMITIR AMBIGUEDAD EN EL EPG ID ENTRE AFECTACION Y CAT_OBRACLC  --------------*/
// year(or_fechaOficioAut) ejercicio

// Marcos: 16/feb/2017
// Como apenas estamos probando, pondremos aparte la consulta original y separadas las que vayamos generando
switch ($modo) {
    // Comportamiento normal

    case '':
    case 'clc':
        //Antes estaba oa_2017 en lugar de oa_2018
        $sql = '
            SELECT ur_clave,oa_id, ur_nombre,oa_numobra ,ore_oficioAut,oa_nombre,oa_' . APP_EJERCICIO_ANTERIOR . ',sft_nombre,cfi_anio,ecf_id,
            obr_contrato,obr_avanceFisico,obr_avanceFinanciero,obr_cumplimiento,obr_vicios,obr_documentacion,obr_anticipo,obr_fianza,
            clc_id, scp_Clc.ocl_id ,DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,clc_concepto,
            clc_firmas,clc_beneficiario,clc_lineaCaptura, scp_Clc.tem_carga,
            clc_nombreAdministrativo, clc_cargoAdministrativo, clc_nombreTitular, clc_cargoTitular,
            clc_nombreAdministracion,clc_nombreRecursos,if(ecl_id <= 3,scp_Clc.ecl_id,scp_ClcAuxiliar.ecl_id) as ecl_id,
            ecl_nombre,soc_nombre,clc_rectificacion, mpc_id, mpc_nombre,bur_id,iba_nombre,
            fte_id,sft_id,ori_id,ori_nombre,cfi_clave,cfi_nombre, cfi_ejercicio,t.ter_id,ter_nombre,
            scp_Clc.tcl_id,tcl_nombre,scp_Clc.stc_id, stc_nombre,clc_recursoFederal,
            scp_Clc.tno_id,tno_nombre,scp_Clc.mno_id, mno_nombre, scp_Clc.smn_id, smn_nombre,
            afe_id,DATE_FORMAT(afe_fechaInicio,"%d/%m/%Y") afe_fechaInicio,DATE_FORMAT(afe_fechaFin,"%d/%m/%Y") afe_fechaFin,
            numcontrol,clave,clave_nombre,referencia,DATE_FORMAT(fecha_referencia,"%d/%m/%Y") fecha_referencia,
            percepcion_bruto,percepcion_iva,percepcion_neto, retencion_neto, tipo_movimiento, tipo_numcontrol,
            rfc_id,rfc_nombre, rfc_paterno, rfc_materno ,if(tdf_id="SDI",1,0) as automatica,
            IF ( str_to_date(clc_fechaElaboracion, "%Y-%m-%d %H:%i:%s") < str_to_date("2017-01-18 15:35:00", "%Y-%m-%d %H:%i:%s"),"Anterior","Nueva") AS leyenda, clc_clave
            FROM scp_Clc
            LEFT JOIN scp_ClcAuxiliar  USING  (gpo_id,ur_id,clc_id,ecl_id)
            INNER JOIN cat_UnidadResponsable  USING (gpo_id, ur_id)
            LEFT JOIN cat_ObraClc  USING (gpo_id,ur_id,clc_id)
            JOIN (
                SELECT bur_id,iba_nombre
                FROM cat_BancosUr
                JOIN cat_InstitucionesBancarias USING (iba_id)
            ) bancosUr  USING (bur_id)
            INNER JOIN cat_EstadoClc  USING (ecl_id)
            INNER JOIN cat_ModoPagoClc  USING (mpc_id)
            INNER JOIN enc_ClaveFinanciamiento  USING (ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '  USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . '  USING (cff_id,fte_id,sft_id,ori_id,sor_id)
            INNER JOIN ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . '  USING (cff_id,fte_id,sft_id,ori_id)
            INNER JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' USING  (cff_id,fte_id,sft_id)
            INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso t  USING (ter_id)
            INNER JOIN cat_SubtipoClc  USING (tcl_id,stc_id)
            INNER JOIN cat_TipoClc  USING (tcl_id)
            LEFT JOIN cat_TipoNomina  USING (tno_id)
            LEFT JOIN cat_ModalidadNomina  USING (mno_id)
            LEFT JOIN cat_SubmodalidadNomina  USING (mno_id, smn_id)
            /*para que salga el encabezado y el pie de pagina aunque no tenga afectaciones*/
            /*  AFECTACIONES Y SUS DETALLES SI ES QUE TIENE    --------*/
            LEFT JOIN(
                SELECT gpo_id,ur_id,clc_id,afe_id,afe_fechaInicio,afe_fechaFin,
                numcontrol,clave,clave_nombre,referencia,fecha_referencia,percepcion_bruto,percepcion_iva,percepcion_neto,
                retencion_neto,tipo_movimiento,rfc_id,rfc_nombre,rfc_paterno,rfc_materno,tipo_numcontrol,soc_nombre,bdo_id
                FROM  scp_Afectacion
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, afe_id, ncp_id numcontrol, concat(pre_claveCompleta,tcp_id) clave,
                        pe_nombre clave_nombre,ncp_referencia referencia,ncp_fechaReferencia fecha_referencia,
                        ncp_percepcionBruto percepcion_bruto,ncp_percepcionIva percepcion_iva,
                        ncp_percepcionNeto percepcion_neto, 0 retencion_neto,"+" tipo_movimiento,rfc_id,rfc_nombre,
                        rfc_paterno, rfc_materno,"P" tipo_numcontrol,soc_nombre,bdo_id
                    FROM scp_Presupuestal detalle
                        JOIN presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        JOIN cat_PartidaEspecifica  USING (pg_id,pe_id)
                        LEFT JOIN (
                            SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno,soc_nombre
                            FROM ' . BD_SINPRES . '.cat_Proveedores
                            INNER  JOIN cat_Proveedor  USING (rfc_id)
                            INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor  USING (soc_id)
                            )Prov  USING (rfc_id)
                    ' . $whsub . '
                    UNION
                    SELECT  detalle.gpo_id, detalle.ur_id, clc_id, afe_id,ncc_id numcontrol,
                    CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", cta_gpo_id, cta_ur_id,".", cta_ue_id, ".", cgc_id, ".", cta_id ) clave,
                    cta_nombre clave_nombre,ncc_referencia referencia,ncc_fechaReferencia fecha_referencia,
                    0 percepcion_bruto, 0 percepcion_iva,ncc_percepcion percepcion_neto,ncc_retencion retencion_neto,
                    ncc_tipoMovimiento tipo_movimiento,rfc_id,rfc_nombre, rfc_paterno, rfc_materno,"C" tipo_numcontrol,soc_nombre,bdo_id
                    FROM scp_Contable detalle
                    JOIN cg_PlanCuentas  USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
                    LEFT JOIN (
                        SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno,soc_nombre
                        FROM ' . BD_SINPRES . '.cat_Proveedores
                        INNER  JOIN cat_Proveedor  USING (rfc_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor  USING (soc_id)
                    )Prov  USING (rfc_id)
                    ' . $whsub . '
                ) detalles   USING (gpo_id,ur_id,clc_id,afe_id)
                ' . $whsub . '
            ) nc  USING (gpo_id, ur_id, clc_id)
            /* ------------------------------------------------------  */
            LEFT JOIN enc_Programatico  USING (gpo_id,ur_id,epg_id)
            LEFT JOIN cat_ObraActividad  USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN poa_OrigenRecurso  USING (gpo_id,ur_id,epg_id,ecf_id)
            INNER JOIN cat_BancoDocumentos  USING (gpo_id,ur_id,bdo_id,rfc_id)
            ';
        break;

    case 'clcComp':
        $sql = '
            SELECT ur_clave,oa_id, ur_nombre,oa_numobra ,ore_oficioAut,oa_nombre,oa_2017,sft_nombre,cfi_anio,ecf_id,
            obr_contrato,obr_avanceFisico,obr_avanceFinanciero,obr_cumplimiento,obr_vicios,obr_documentacion,obr_anticipo,obr_fianza,
            clc_id, scp_Clc.ocl_id ,DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,clc_concepto,
            clc_firmas,clc_beneficiario,clc_lineaCaptura, scp_Clc.tem_carga,
            clc_nombreAdministrativo, clc_cargoAdministrativo, clc_nombreTitular, clc_cargoTitular,
            clc_nombreAdministracion,clc_nombreRecursos,
            if(ecl_id <= 3,scp_Clc.ecl_id,scp_ClcAuxiliar.ecl_id) as ecl_id, ecl_nombre,soc_nombre,clc_rectificacion,
            mpc_id, mpc_nombre,
            bur_id,iba_nombre,
            fte_id,sft_id,ori_id,ori_nombre,cfi_clave,cfi_nombre, cfi_ejercicio,
            t.ter_id,ter_nombre,
            scp_Clc.tcl_id,tcl_nombre,scp_Clc.stc_id, stc_nombre,clc_recursoFederal,
            scp_Clc.tno_id,tno_nombre,scp_Clc.mno_id, mno_nombre, scp_Clc.smn_id, smn_nombre,
            afe_id,DATE_FORMAT(afe_fechaInicio,"%d/%m/%Y") afe_fechaInicio,DATE_FORMAT(afe_fechaFin,"%d/%m/%Y") afe_fechaFin,
            cmp_id AS numcontrol,clave,clave_nombre,referencia,DATE_FORMAT(fecha_referencia,"%d/%m/%Y") fecha_referencia,
            SUM( percepcion_bruto ) AS percepcion_bruto,
            SUM( percepcion_iva ) AS percepcion_iva,
            SUM( percepcion_neto ) AS percepcion_neto,
            SUM( retencion_neto ) AS retencion_neto,
            tipo_movimiento, tipo_numcontrol,
            rfc_id,rfc_nombre, rfc_paterno, rfc_materno ,if(tdf_id="SDI",1,0) as automatica,
            IF ( str_to_date(clc_fechaElaboracion, "%Y-%m-%d %H:%i:%s") < str_to_date("2017-01-18 15:35:00", "%Y-%m-%d %H:%i:%s"),"Anterior","Nueva") AS leyenda,clc_clave
            FROM scp_Clc
            LEFT JOIN scp_ClcAuxiliar  USING (gpo_id,ur_id,clc_id,ecl_id)
            INNER JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
            LEFT JOIN cat_ObraClc  USING (gpo_id,ur_id,clc_id)
            JOIN (
                SELECT bur_id,iba_nombre
                FROM cat_BancosUr
                JOIN cat_InstitucionesBancarias USING (iba_id)
            ) bancosUr  USING (bur_id)
            INNER JOIN cat_EstadoClc  USING (ecl_id)
            INNER JOIN cat_ModoPagoClc  USING (mpc_id)
            INNER JOIN enc_ClaveFinanciamiento  USING (ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
            INNER JOIN ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id)
            INNER JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' USING  (cff_id,fte_id,sft_id)
            INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso t  USING (ter_id)
            INNER JOIN cat_SubtipoClc  USING (tcl_id,stc_id)
            INNER JOIN cat_TipoClc  USING (tcl_id)
            LEFT JOIN cat_TipoNomina  USING (tno_id)
            LEFT JOIN cat_ModalidadNomina  USING (mno_id)
            LEFT JOIN cat_SubmodalidadNomina  USING (mno_id, smn_id)
            /*para que salga el encabezado y el pie de pagina aunque no tenga afectaciones*/
            /*  AFECTACIONES Y SUS DETALLES SI ES QUE TIENE    --------*/
            LEFT JOIN(
                SELECT gpo_id,ur_id,clc_id,afe_id,afe_fechaInicio,afe_fechaFin,
                numcontrol,clave,clave_nombre,referencia,fecha_referencia,percepcion_bruto,percepcion_iva,
                percepcion_neto,retencion_neto,tipo_movimiento,rfc_id,rfc_nombre,rfc_paterno,
                rfc_materno,tipo_numcontrol,soc_nombre,bdo_id, cmp_id
                FROM  scp_Afectacion
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, afe_id,
                        ncp_id numcontrol, concat(pre_claveCompleta,tcp_id) clave,pe_nombre clave_nombre,
                        ncp_referencia referencia,ncp_fechaReferencia fecha_referencia,ncp_percepcionBruto
                        percepcion_bruto,ncp_percepcionIva percepcion_iva,
                        ncp_percepcionNeto percepcion_neto, 0 retencion_neto,"+" tipo_movimiento,rfc_id,rfc_nombre,
                        rfc_paterno, rfc_materno,"P" tipo_numcontrol,soc_nombre,bdo_id, cmp_id
                    FROM scp_Presupuestal detalle
                        JOIN presupuesto  USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        JOIN cat_PartidaEspecifica  USING (pg_id,pe_id)
                        LEFT JOIN (
                            SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno,soc_nombre
                            FROM ' . BD_SINPRES . '.cat_Proveedores
                            INNER  JOIN cat_Proveedor  USING (rfc_id)
                            INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor  USING (soc_id)
                            )Prov  USING (rfc_id)
                    ' . $whsub . '
                    UNION
                    SELECT  detalle.gpo_id, detalle.ur_id, clc_id, afe_id, ncc_id numcontrol,
                        CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", cta_gpo_id, cta_ur_id,".", cta_ue_id, ".", cgc_id, ".", cta_id ) clave,
                        cta_nombre clave_nombre,ncc_referencia referencia,ncc_fechaReferencia fecha_referencia,
                        0 percepcion_bruto, 0 percepcion_iva,ncc_percepcion percepcion_neto,ncc_retencion retencion_neto,
                        ncc_tipoMovimiento tipo_movimiento,rfc_id,rfc_nombre, rfc_paterno, rfc_materno,"C" tipo_numcontrol,
                        soc_nombre,bdo_id, 0 as cmp_id
                    FROM scp_Contable detalle
                        JOIN cg_PlanCuentas  USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
                        LEFT JOIN (
                            SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno,soc_nombre
                            FROM ' . BD_SINPRES . '.cat_Proveedores
                            INNER  JOIN cat_Proveedor  USING (rfc_id)
                            INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor  USING (soc_id)
                        )Prov  USING (rfc_id)
                    ' . $whsub . '
                ) detalles   USING (gpo_id,ur_id,clc_id,afe_id)
                ' . $whsub . '
            ) nc  USING (gpo_id, ur_id, clc_id)
            /* ------------------------------------------------------  */
            LEFT JOIN enc_Programatico  USING (gpo_id,ur_id,epg_id)
            LEFT JOIN cat_ObraActividad  USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN poa_OrigenRecurso  USING (gpo_id,ur_id,epg_id,ecf_id)
            INNER JOIN cat_BancoDocumentos  USING (gpo_id,ur_id,bdo_id,rfc_id)
        ';
        break;
}

$wh = ' where ';

//Se revisa si se esta imprimiendo una clc o un lote
if (isset($_POST['tem_carga']) and ($_POST['tem_carga'] != '' or $_POST['tem_carga'] != null)) {
    $wh .= ' scp_Clc.tem_carga = "' . $_POST['tem_carga'] . '"  ';
    $wh .= (isset($_POST['gpo_id']) && isset($_POST['ur_id']) && isset($_POST['clc_id'])) ? ' AND gpo_id = "' . $_POST['gpo_id'] . '"  and ur_id = "' . $_POST['ur_id'] . '" and clc_id = "' . $_POST['clc_id'] . '"  ' : '';
} else {
    if (isset($_POST['gpo_id']) && isset($_POST['ur_id']) && isset($_POST['ur_id'])) {
        $wh .= ' gpo_id = "' . $_POST['gpo_id'] . '"  and ur_id = "' . $_POST['ur_id'] . '" and clc_id = "' . $_POST['clc_id'] . '"  ';
    }
}

$sql .= $wh;
// Marcos: 16/feb/2017
// El GROUP BY sólo se usará cuando agrupemos para vincular en la contabilidad
switch ($modo) {
    // Comportamiento normal
    case '':
    case 'clc':
        // nada
        break;
    case 'clcComp':
        $sql .= ' GROUP BY cmp_id ';
        break;
}
$bloqueoCLC = 1;

if (isset($_POST['tem_carga']) and ($_POST['tem_carga'] != '' or $_POST['tem_carga'] != null)) {
    $sql .= ' order by gpo_id, ur_id,clc_id,referencia,tipo_movimiento, afe_id, numcontrol';
} else {
    $sql .= ' order by tipo_movimiento, afe_id, numcontrol';

    if ($_SESSION[_S]['nvj_id'] <= 2) {

        $bloqueoCLC = bloqueo_clc($_POST['clc_id'], $_POST['gpo_id'], $_POST['ur_id']);
    }
}

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);

// Marcos: 06/junio/2018
// Si viene desde la contabilidad (vinculación por CLC), la imprimimos independientemente de
// los bloqueos, ya que se imprime sin firmas y con marca de agua
if ((isset($_POST['scg_vc']) && $_POST['scg_vc'] == '1')) {
    $conexion->consultar();
} else {
    if ($bloqueoCLC == 1) {
        $conexion->consultar();
    } else {
        echo 'EXISTE UN BLOQUEO DE CLCs POR PARTE DEL AREA DE PRESUPUESTO, FAVOR DE CONSULTAR CON SU ANALISTA';
        die;
    }
}

$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(['print'], '', PDF_PASSWORD_OWNER_SCP);

$percepciones = 0;
$retenciones = 0;
$tipomovanterior = '';
$clc_id = '';
$ur_clave = '';

$clc_claveT = '';

$pagI = true;
$registros = $conexion->getNumeroRegistros();
if ($registros == 0) {
    $pdf->AddPage();
}
if ($row = $conexion->extraer()) {
    $clc_claveT = $row['clc_clave'];
    do {
        if ($pagI) {
            $pdf->setDatosAnt($row);
            $pagI = false;
        } else {
            $pdf->setDatosAnt($pdf->getDatos());
        }
        $pdf->setDatos($row);

        //cuerpo de la tabla
        if ($clc_id != $row['clc_id'] || $ur_clave != $row['ur_clave']) {
            if (mb_strlen($clc_id)) {
                if ($tipomovanterior == '+') {
                    $pdf->ln(2);
                    $pdf->SetFont('Arial', 'B', 5.5);
                    $pdf->SetWidths([233, 22, 5]);
                    $pdf->SetAligns(['R', 'R', 'R']);
                    $pdf->Row(['TOTAL PERCEPCIONES', number_format($percepciones, 2, '.', ','), '']);
                }

                $pdf->ln(2);
                $pdf->SetFont('Arial', 'B', 5.5);
                $pdf->SetWidths([233, 22, 5]);
                $pdf->SetAligns(['R', 'R', 'R']);
                $pdf->Row(['TOTAL RETENCIONES', number_format($retenciones, 2, '.', ','), '']);
                //FIN TOTAL DE RETENCIONES

                if ($percepciones > 0) {
                    $sumatotal = abs($percepciones - $retenciones);
                } else {
                    $sumatotal = $retenciones;
                }

                $pdf->ln(2);
                $pdf->SetFont('Arial', 'B', 5.5);
                $pdf->SetWidths([233, 22, 5]);
                $pdf->SetAligns(['R', 'R', 'R']);
                $pdf->Row(['TOTAL C.L.C.', number_format($sumatotal, 2, '.', ','), '']);

                $percepciones = 0;
                $retenciones = 0;
                $tipomovanterior = '';
            }

            $pdf->AliasNbPages();
            $pdf->AddPage();
            $clc_id = $row['clc_id'];
            $ur_clave = $row['ur_clave'];
        }

        //PARA CLCS DE ANTICIPO DE OBRA,PAGOS A TERCEROS Y PAGOS A TERCEROS OTROS

        if (mb_strlen($tipomovanterior) && $tipomovanterior != $row['tipo_movimiento']) {
            //TOTAL DE PERCEPCIONES
            $pdf->ln(2);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([233, 22, 5]);
            $pdf->SetAligns(['R', 'R', 'R']);
            $pdf->Row(['TOTAL PERCEPCIONES', number_format($percepciones, 2, '.', ','), '']);
        }

        $widths = '';
        $aligns = '';
        $contenido = '';
        $bruto = 0;
        $iva = 0;
        $neto = 0;
        //si es un numero de control presupuestal
        if ($row['tipo_numcontrol'] == 'P') {
            $bruto = $row['percepcion_bruto'];
            $iva = $row['percepcion_iva'];
            $neto = $row['percepcion_neto'];
        }

        // si es un numero de control contable
        elseif ($row['tipo_numcontrol'] == 'C') {
            if ($row['tipo_movimiento'] == '+') {
                $bruto = $row['percepcion_neto'];
                $neto = $row['percepcion_neto'];
            } else {
                $bruto = $row['retencion_neto'];
                $neto = $row['retencion_neto'];
            }
            $iva = $row['percepcion_iva'];
        }

        switch ($row['tcl_id']) {
            //SERVICIOS PERSONALES tiene periodo y no tiene iva
            case 'A':
                $widths = [8, 11, 42, 48, 17, 20, 20, 45, 20, 24, 5];
                $aligns = ['L', 'L', 'L', 'L', 'L', 'C', 'C', 'L', 'R', 'R', 'C'];
                $contenido = [
                    $row['afe_id'], $row['numcontrol'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referencia'], $row['afe_fechaInicio'], $row['afe_fechaFin'], utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']), utf8Decode($row['rfc_id']), number_format((float) $neto, 2), $row['tipo_movimiento'],
                ];
                break;
                //GASTOS DE OPERACION,INVERSION PÚBLICA no tiene periodo y tiene desglose de IVA
            case 'B':
            case 'F':
            case 'G':
            case 'H':
                $widths = [8, 11, 42, 38, 16, 20, 36, 21, 21, 21, 22, 4];
                $aligns = ['L', 'L', 'L', 'L', 'L', 'C', 'L', 'R', 'R', 'R', 'R', 'C'];
                $contenido = [$row['afe_id'], $row['numcontrol'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referencia'], $row['fecha_referencia'], utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']), utf8Decode($row['rfc_id']), number_format(floatval($bruto), 2, '.', ','), number_format(floatval($iva), 2, '.', ','), number_format(floatval($neto), 2, '.', ','), $row['tipo_movimiento']];
                break;

                // ACUERDOS DE MINISTRACION,ANTICIPOS / FONDO ROTATORIO, MUNICIPIOS, DEUD. PUBLICA,
                //BURSATILIZACIÓN, TRANSFERENCIAS, PAGOS A TERCEROS (OTROS)
                //no tiene periodo y no tiene desglose de IVA
            case 'D':
            case 'E':
            case 'J':
            case 'K':
            case 'L':
            case 'M':
                $widths = [10, 13, 42, 42, 18, 24, 50, 24, 31, 6];
                $aligns = ['L', 'L', 'L', 'L', 'L', 'C', 'L', 'R', 'R', 'R', 'C'];
                $contenido = [$row['afe_id'], $row['numcontrol'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referencia'], $row['fecha_referencia'], utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']), utf8Decode($row['rfc_id']), number_format($neto, 2), $row['tipo_movimiento']];
                break;
                //caso municipios
            case 'I':
                switch ($row['stc_id']) {
                    case 4:
                        $widths = [8, 11, 42, 38, 16, 20, 36, 21, 21, 21, 22, 4];
                        $aligns = ['L', 'L', 'L', 'L', 'L', 'C', 'L', 'R', 'R', 'R', 'R', 'C'];
                        $contenido = [$row['afe_id'], $row['numcontrol'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referencia'], $row['fecha_referencia'], utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']), utf8Decode($row['rfc_id']), number_format(floatval($bruto), 2, '.', ','), number_format(floatval($iva), 2, '.', ','), number_format(floatval($neto), 2, '.', ','), $row['tipo_movimiento']];
                        break;
                    default:
                        $widths = [10, 13, 42, 42, 18, 24, 50, 24, 31, 6];
                        $aligns = ['L', 'L', 'L', 'L', 'L', 'C', 'L', 'R', 'R', 'R', 'C'];
                        $contenido = [$row['afe_id'], $row['numcontrol'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referencia'], $row['fecha_referencia'], utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']), utf8Decode($row['rfc_id']), number_format($neto, 2), $row['tipo_movimiento']];
                        break;
                }
                break;
                //PAGOS A TERCEROS
            case 'C':
                switch ($row['stc_id']) {
                    //tiene periodo y no tiene iva
                    case '01':
                        $widths = [8, 11, 39, 48, 17, 20, 20, 45, 23, 24, 5];
                        $aligns = ['L', 'L', 'L', 'L', 'L', 'C', 'C', 'L', 'R', 'R', 'C'];
                        $contenido = [$row['afe_id'], $row['numcontrol'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referencia'], $row['afe_fechaInicio'], $row['afe_fechaFin'], utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']), utf8Decode($row['rfc_id']), number_format($neto, 2), $row['tipo_movimiento']];
                        break;
                        //no tiene periodo y tiene desglose de IVA
                    case '02':
                    case '03':
                    case '04':
                    case '05':
                        $widths = [8, 11, 39, 38, 16, 20, 36, 22, 22, 22, 22, 4];
                        $aligns = ['L', 'L', 'L', 'L', 'L', 'C', 'L', 'R', 'R', 'R', 'R', 'C'];
                        $contenido = [$row['afe_id'], $row['numcontrol'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referencia'], $row['fecha_referencia'], utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']), utf8Decode($row['rfc_id']), number_format($bruto, 2, '.', ','), number_format($iva, 2, '.', ','), number_format($neto, 2, '.', ','), $row['tipo_movimiento']];
                        break;
                }
                break;
        }

        $pdf->ln(1);
        $pdf->SetFont('Arial', 'B', 5.5);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $pdf->SetAltoLinea(2.5);
        $pdf->Row($contenido);

        $tipomovanterior = $row['tipo_movimiento'];
        if ($row['tipo_movimiento'] == '+') {
            $percepciones = $percepciones + $row['percepcion_neto'];
        } else {
            $retenciones = $retenciones + $row['retencion_neto'];
        }

        $yreporte = $pdf->GetY();

        // Marcos: 09/feb/2016... para que se pueda impimir desde la contabilidad
        // Si viene de la contabilidad -al no imprimir notas ni firmas- le damos más espacio a los datos
        if ((isset($_POST['scg_vc']) && $_POST['scg_vc'] == '1')) {
            if ($yreporte > 200) {
                $pdf->AddPage();
            }
        } else {
            if ($yreporte > 150) {
                $pdf->AddPage();
            }
        }
    } while ($row = $conexion->extraer());
} else {
    $pdf->AliasNbPages();
    $pdf->AddPage();
}

if ($percepciones > 0 and $retenciones == 0) {
    //TOTAL DE PERCEPCIONES
    $pdf->ln(2);
    $pdf->SetFont('Arial', 'B', 5.5);
    $pdf->SetWidths([233, 22, 5]);
    $pdf->SetAligns(['R', 'R', 'R']);
    $pdf->Row(['TOTAL PERCEPCIONES', number_format($percepciones, 2, '.', ','), '']);
}

//TOTAL DE RETENCIONES
$pdf->ln(2);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->SetWidths([233, 22, 5]);
$pdf->SetAligns(['R', 'R', 'R']);
$pdf->Row(['TOTAL RETENCIONES', number_format($retenciones, 2, '.', ','), '']);
//FIN TOTAL DE RETENCIONES

if ($percepciones > 0) {
    $sumatotal = abs($percepciones - $retenciones);
} else {
    $sumatotal = $retenciones;
}
$pdf->ln(2);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->SetWidths([233, 22, 5]);
$pdf->SetAligns(['R', 'R', 'R']);
$pdf->Row(['TOTAL C.L.C.', number_format($sumatotal, 2, '.', ','), '']);

//Conversion de importe clc a letra
$total = Math::redondear($percepciones, 2) - Math::redondear($retenciones, 2);

$numero = number_format($total, 2, '.', ',');
$centavos = substr(strstr(number_format($total, 2), '.'), 1, 2);
$enletra = num2letras($total, false, false) . ' pesos ' . $centavos . '/100 M.N.';
$leyenda = $numero . ' (' . ($enletra) . ') SEGUN EL SIGUIENTE DETALLE:';

/**
 * Autor: Eduardo Venegas
 * Se corrigio el set y get para el número de pagina
 */
foreach ($pdf->getPages() as $i => $val) {
    $pdf->setPages($i, str_replace($pdf->importe, $leyenda, $pdf->getPages()[$i]));
}

if (isset($_POST['tem_carga']) and ($_POST['tem_carga'] != '' or $_POST['tem_carga'] != null)) {
    $pi = $pdf->PageNo() - $pdf->pagina;
} else {
    $pi = 1;
}

$pf = $pdf->PageNo();

for ($n = $pi; $n <= $pf; $n++) {
    if ($n > 0) {
        $pdf->setPages($n, str_replace($pdf->seccion, $pdf->pagina, $pdf->getPages()[$n]));
    }
}

if (isset($_POST['reimprimir']) && $_SESSION[_S]['nvj_id'] > 2) {
    $acceso = new AccesoDatos();

    $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
    $acceso->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
            VALUES (?,?,?,?,"SE REIMPRIMIO LA CLC SIN MARCA DE AGUA",INET_ATON(?),?,now());
        ');
    $params = [$_POST['gpo_id'],$_POST['ur_id'],$_POST['clc_id'],$pdf->getDatos()['ecl_id'],$ip, $_SESSION[_S]['usr_id']];
    $acceso->conectar();
    $acceso->consultar($params);
    $acceso->desconectar();
}

if (isset($_POST['enviado'])) {
    echo "<script>document.write('<h3>Favor de no cerrar la ventana, en cuanto el proceso sea completado se cerrará</h3>');</script>";
    uploadArchivo($pdf, $clc_claveT);
    echo json_encode('success!');
    echo '<script>window.close();</script>';
    return;
} elseif (isset($_POST['reimprimir'])) {
    reimprimirArchivo($pdf, $clc_claveT);
}

if (!isset($_POST['enviado'])) {
    $pdf->Output();
}

/* ******************************* Carga de archivos para reposiitorio principal */
function uploadArchivo($pdf, $clc_claveT)
{
    // Primeramente se guardara el reporte en una carpeta temporal
    $pdf2 = $pdf;
    $archivo = 'S-' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . date('m') . '-' . $clc_claveT . '.pdf';
    $rutaNombre = dirname(__FILE__) . '/../../scp/afectaciones/tmpClcPdf/' . $archivo;

    $pdf2->Output($rutaNombre, 'F');

    // Una vez guardado el documento en la pantalla temporal se manda a su verdadera carpeta

    $upload = new FicheroSefip();
    // Se guarda en un arreglo el archivo a enviar
    $file = ['name' => $rutaNombre, 'tmp_name' => $rutaNombre, 'eslocal' => true];
    $upload->setTipos(['pdf']);
    $upload->setFichero($file);
    $upload->setModulo('scp');
    $upload->setfile($archivo);
    $upload->setUrFichero($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur']);
    $upload->setAnioFichero(APP_EJERCICIO);

    $upload->setRuta('AFECTACIONES/' . date('m') . '/FLUJO_CLC');

    if ($upload->upload()) {
        $name = $archivo; //validar
        unlink($name);
    }
}

function reimprimirArchivo($pdf, $clc_claveT)
{
    // Primeramente se renombrar el pdf original para generar un historial
    $sql = '
        select clc_fechaEnvio from ' . BD_CTRL . '.scp_Clc
        where gpo_id = ?
        and ur_id = ?
        and clc_id = ? ';
    $params = [$_POST['gpo_id'],$_POST['ur_id'],$_POST['clc_id']];
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();

    $row = $conexion->extraer();
    $pdf2 = $pdf;
    //Se usa post para el gpo_id y ur_id ya que es el ur con la que fue creada, no con la que tiene en la clasAdm de la sesion,
    // ya que esta puede ser diferente a con la que se creo
    $archivo = 'S-' . $_POST['gpo_id'] . $_POST['ur_id'] . '-' . substr($row['clc_fechaEnvio'], 5, 2) . '-' . $clc_claveT . '-1.pdf';

    $rutaNombre = dirname(__FILE__) . '/../../../ArchivosSefip/SCP' . APP_EJERCICIO . '/' . $_POST['gpo_id'] . $_POST['ur_id'] . '/AFECTACIONES/' . substr($row['clc_fechaEnvio'], 5, 2) . '/REIMPRESION/' . $archivo;

    if (file_exists($rutaNombre)) {
        $numArchivo = explode('-', $rutaNombre);
        $temp = explode('.', $numArchivo[4]);
        $numero = ((int) ($temp[0]) + 1);
        $creado = false;
        while (!$creado) {

            $archivo = 'S-' . $_POST['gpo_id'] . $_POST['ur_id'] . '-' . substr($row['clc_fechaEnvio'], 5, 2) . '-' . $clc_claveT . '-' . $numero . '.pdf';

            $rutaNombre = dirname(__FILE__) . '/../../../ArchivosSefip/SCP' . APP_EJERCICIO . '/' . $_POST['gpo_id'] . $_POST['ur_id'] . '/AFECTACIONES/' . substr($row['clc_fechaEnvio'], 5, 2) . '/REIMPRESION/' . $archivo;

            if (file_exists($rutaNombre)) {
                $numero++;
            } else {
                $pdf2->Output($rutaNombre, 'F');
                // Una vez guardado el documento en la pantalla temporal se manda a su verdadera carpeta
                moverArchivo($rutaNombre, $archivo, substr($row['clc_fechaEnvio'], 5, 2));
                $creado = true;
            }
        }
    } else {
        $rutaNombre = dirname(__FILE__) . '/../../scp/afectaciones/tmpClcPdf/';
        if (!file_exists($rutaNombre)) {
            mkdir($rutaNombre, 0777, true);
        }

        $pdf2->Output($rutaNombre . $archivo, 'F');
        // Una vez guardado el documento en la pantalla temporal se manda a su verdadera carpeta
        moverArchivo($rutaNombre, $archivo, substr($row['clc_fechaEnvio'], 5, 2));
    }
    return;
}

function moverArchivo($rutaNombre, $archivo, $fechaEnvio)
{
    $upload = new FicheroSefip();
    // Se guarda en un arreglo el archivo a enviar
    $file = ['name' => $rutaNombre, 'tmp_name' => $rutaNombre, 'eslocal' => true];
    $upload->setTipos(['pdf']);
    $upload->setFichero($file);
    $upload->setModulo('scp');
    $upload->setfile($archivo);
    $upload->setUrFichero($_POST['gpo_id'] . $_POST['ur_id']);
    $upload->setAnioFichero(APP_EJERCICIO);

    $upload->setRuta('AFECTACIONES/' . $fechaEnvio . '/REIMPRESION');

    if ($upload->upload()) {
        $name = $archivo; //validar
        unlink($name);
    }
}

/*    ************************ BLOQUEO ******************************/
function bloqueo_clc($clc, $gpo, $ur)
{
    $tcl = $stc_id = $ecf_id = '';
    $bloqueo = 1;

    $sql = '
        SELECT tcl_id,stc_id,ecf_id from scp_Clc
        WHERE  gpo_id= ? and ur_id= ? AND clc_id= ? ;
    ';
    $params = [$gpo,$ur,$clc];
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();

    if ($dat = $conexion->extraer()) {
        $tcl = $dat['tcl_id'];
        $stc_id = $dat['stc_id'];
        $ecf_id = $dat['ecf_id'];
    }
    $opc = new stdClass();
    $opc->sis_id = 'S';
    $opc->men_id = '2';
    $opc->opc_id = '2';

    $n = new modelScp();
    $bloqueo1 = $n->gtBloqueos($tcl, $stc_id, $ecf_id, $opc);

    $bloqueo = $bloqueo1->valido;

    return $bloqueo;
}

//****************FUNCIONES PARA LA CONVERSION DEL IMPORTE EN LETRAS*********************************************************//

function num2letras($num, $fem = false, $dec = true)
{
    $matuni[2] = 'dos';
    $matuni[3] = 'tres';
    $matuni[4] = 'cuatro';
    $matuni[5] = 'cinco';
    $matuni[6] = 'seis';
    $matuni[7] = 'siete';
    $matuni[8] = 'ocho';
    $matuni[9] = 'nueve';
    $matuni[10] = 'diez';
    $matuni[11] = 'once';
    $matuni[12] = 'doce';
    $matuni[13] = 'trece';
    $matuni[14] = 'catorce';
    $matuni[15] = 'quince';
    $matuni[16] = 'dieciseis';
    $matuni[17] = 'diecisiete';
    $matuni[18] = 'dieciocho';
    $matuni[19] = 'diecinueve';
    $matuni[20] = 'veinte';
    $matunisub[2] = 'dos';
    $matunisub[3] = 'tres';
    $matunisub[4] = 'cuatro';
    $matunisub[5] = 'quin';
    $matunisub[6] = 'seis';
    $matunisub[7] = 'sete';
    $matunisub[8] = 'ocho';
    $matunisub[9] = 'nove';

    $matdec[2] = 'veint';
    $matdec[3] = 'treinta';
    $matdec[4] = 'cuarenta';
    $matdec[5] = 'cincuenta';
    $matdec[6] = 'sesenta';
    $matdec[7] = 'setenta';
    $matdec[8] = 'ochenta';
    $matdec[9] = 'noventa';
    $matsub[3] = 'mill';
    $matsub[5] = 'bill';
    $matsub[7] = 'mill';
    $matsub[9] = 'trill';
    $matsub[11] = 'mill';
    $matsub[13] = 'bill';
    $matsub[15] = 'mill';
    $matmil[4] = 'millones';
    $matmil[6] = 'billones';
    $matmil[7] = 'de billones';
    $matmil[8] = 'millones de billones';
    $matmil[10] = 'trillones';
    $matmil[11] = 'de trillones';
    $matmil[12] = 'millones de trillones';
    $matmil[13] = 'de trillones';
    $matmil[14] = 'billones de trillones';
    $matmil[15] = 'de billones de trillones';
    $matmil[16] = 'millones de billones de trillones';

    $num = trim((string) @$num);
    if ($num == '-') {
        $neg = 'menos ';
        $num = substr($num, 1);
    } else {
        $neg = '';
    }

    while ($num == '0') {
        $num = substr($num, 1);
    }

    if ($num < '1' or $num[0] > 9) {
        $num = '0' . $num;
    }

    $zeros = true;
    $punt = false;
    $ent = '';
    $fra = '';
    for ($c = 0; $c < strlen($num); $c++) {
        $n = $num[$c];
        if (!(strpos(".,'''", $n) === false)) {
            if ($punt) {
                break;
            } else {
                $punt = true;
                continue;
            }
        } elseif (!(strpos('0123456789', $n) === false)) {
            if ($punt) {
                if ($n != '0') {
                    $zeros = false;
                }

                $fra .= $n;
            } else {
                $ent .= $n;
            }
        } else {
            break;
        }
    }
    $ent = '     ' . $ent;
    if ($dec and $fra and !$zeros) {
        $fin = ' con';
        for ($n = 0; $n < strlen($fra); $n++) {
            if (($s = $fra[$n]) == '0') {
                $fin .= ' cero';
            } elseif ($s == '1') {
                $fin .= $fem ? ' una' : ' un';
            } else {
                $fin .= ' ' . $matuni[$s];
            }
        }
    } else {
        $fin = '';
    }

    if ((int) $ent === 0) {
        return 'Cero ' . $fin;
    }

    $tex = '';
    $sub = 0;
    $mils = 0;
    $neutro = false;
    while (($num = substr($ent, -3)) != '   ') {
        $ent = substr($ent, 0, -3);
        if (++$sub < 3 and $fem) {
            $matuni[1] = 'una';
            $subcent = 'as';
        } else {
            $matuni[1] = $neutro ? 'un' : 'uno';
            $subcent = 'os';
        }
        $t = '';
        $n2 = substr($num, 1);
        if ($n2 == '00') {
        } elseif ($n2 < 21) {
            $t = ' ' . $matuni[(int) $n2];
        } elseif ($n2 < 30) {
            $n3 = $num[2];
            if ($n3 != 0) {
                $t = 'i' . $matuni[$n3];
            }

            $n2 = $num[1];
            $t = ' ' . $matdec[$n2] . $t;
        } else {
            $n3 = $num[2];
            if ($n3 != 0) {
                $t = ' y ' . $matuni[$n3];
            }

            $n2 = $num[1];
            $t = ' ' . $matdec[$n2] . $t;
        }
        $n = $num[0];
        if ($n == 1) {
            //cambio realizado por dd para que las cantidades en letra aparezcan como cien mil o ciento trein....segun sea el caso.
            if ($n2 == '00') {
                $t = ' cien' . $t;
            } else {
                $t = ' ciento' . $t;
            }

            /////////////////////////////////////////////////
        } elseif ($n == 5) {
            $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
        } elseif ($n != 0 && $n != ' ') {
            $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
        }
        if ($sub == 1) {
        } elseif (!isset($matsub[$sub])) {
            if ($num == 1) {
                $t = ' mil';
            } elseif ($num > 1) {
                $t .= ' mil';
            }
        } elseif ($num == 1) {
            $t .= ' ' . $matsub[$sub] . 'ón';
        } elseif ($num > 1) {
            $t .= ' ' . $matsub[$sub] . 'ones';
        }
        if ($num == '000') {
            $mils++;
        } elseif ($mils != 0) {
            if (isset($matmil[$sub])) {
                $t .= ' ' . $matmil[$sub];
            }

            $mils = 0;
        }
        $neutro = true;
        $tex = $t . $tex;
    }
    $tex = $neg . substr($tex, 1) . $fin;
    // Marcos: 22/feb/2017
    // codificamos en utf8 porque el acento de millón no salía bien
    return ucfirst(utf8Decode($tex));
}

//Funcion para pasar de numeros a letras
function unidad($numuero)
{
    switch ($numuero) {
        case 9:{
            $numu = 'NUEVE';
            break;
        }
        case 8:{
            $numu = 'OCHO';
            break;
        }
        case 7:{
            $numu = 'SIETE';
            break;
        }
        case 6:{
            $numu = 'SEIS';
            break;
        }
        case 5:{
            $numu = 'CINCO';
            break;
        }
        case 4:{
            $numu = 'CUATRO';
            break;
        }
        case 3:{
            $numu = 'TRES';
            break;
        }
        case 2:{
            $numu = 'DOS';
            break;
        }
        case 1:{
            $numu = 'UN';
            break;
        }
        case 0:{
            $numu = '';
            break;
        }
    }
    return $numu;
}

function decena($numdero)
{
    if ($numdero >= 90 && $numdero <= 99) {
        $numd = 'NOVENTA ';
        if ($numdero > 90) {
            $numd = $numd . 'Y ' . (unidad($numdero - 90));
        }
    } elseif ($numdero >= 80 && $numdero <= 89) {
        $numd = 'OCHENTA ';
        if ($numdero > 80) {
            $numd = $numd . 'Y ' . (unidad($numdero - 80));
        }
    } elseif ($numdero >= 70 && $numdero <= 79) {
        $numd = 'SETENTA ';
        if ($numdero > 70) {
            $numd = $numd . 'Y ' . (unidad($numdero - 70));
        }
    } elseif ($numdero >= 60 && $numdero <= 69) {
        $numd = 'SESENTA ';
        if ($numdero > 60) {
            $numd = $numd . 'Y ' . (unidad($numdero - 60));
        }
    } elseif ($numdero >= 50 && $numdero <= 59) {
        $numd = 'CINCUENTA ';
        if ($numdero > 50) {
            $numd = $numd . 'Y ' . (unidad($numdero - 50));
        }
    } elseif ($numdero >= 40 && $numdero <= 49) {
        $numd = 'CUARENTA ';
        if ($numdero > 40) {
            $numd = $numd . 'Y ' . (unidad($numdero - 40));
        }
    } elseif ($numdero >= 30 && $numdero <= 39) {
        $numd = 'TREINTA ';
        if ($numdero > 30) {
            $numd = $numd . 'Y ' . (unidad($numdero - 30));
        }
    } elseif ($numdero >= 20 && $numdero <= 29) {
        if ($numdero == 20) {
            $numd = 'VEINTE ';
        } else {
            $numd = 'VEINTI' . (unidad($numdero - 20));
        }
    } elseif ($numdero >= 10 && $numdero <= 19) {
        switch ($numdero) {
            case 10:{
                $numd = 'DIEZ ';
                break;
            }
            case 11:{
                $numd = 'ONCE ';
                break;
            }
            case 12:{
                $numd = 'DOCE ';
                break;
            }
            case 13:{
                $numd = 'TRECE ';
                break;
            }
            case 14:{
                $numd = 'CATORCE ';
                break;
            }
            case 15:{
                $numd = 'QUINCE ';
                break;
            }
            case 16:{
                $numd = 'DIECISEIS ';
                break;
            }
            case 17:{
                $numd = 'DIECISIETE ';
                break;
            }
            case 18:{
                $numd = 'DIECIOCHO ';
                break;
            }
            case 19:{
                $numd = 'DIECINUEVE ';
                break;
            }
        }
    } else {
        $numd = unidad($numdero);
    }

    return $numd;
}

function centena($numc)
{
    if ($numc >= 100) {
        if ($numc >= 900 && $numc <= 999) {
            $numce = 'NOVECIENTOS ';
            if ($numc > 900) {
                $numce = $numce . (decena($numc - 900));
            }
        } elseif ($numc >= 800 && $numc <= 899) {
            $numce = 'OCHOCIENTOS ';
            if ($numc > 800) {
                $numce = $numce . (decena($numc - 800));
            }
        } elseif ($numc >= 700 && $numc <= 799) {
            $numce = 'SETECIENTOS ';
            if ($numc > 700) {
                $numce = $numce . (decena($numc - 700));
            }
        } elseif ($numc >= 600 && $numc <= 699) {
            $numce = 'SEISCIENTOS ';
            if ($numc > 600) {
                $numce = $numce . (decena($numc - 600));
            }
        } elseif ($numc >= 500 && $numc <= 599) {
            $numce = 'QUINIENTOS ';
            if ($numc > 500) {
                $numce = $numce . (decena($numc - 500));
            }
        } elseif ($numc >= 400 && $numc <= 499) {
            $numce = 'CUATROCIENTOS ';
            if ($numc > 400) {
                $numce = $numce . (decena($numc - 400));
            }
        } elseif ($numc >= 300 && $numc <= 399) {
            $numce = 'TRESCIENTOS ';
            if ($numc > 300) {
                $numce = $numce . (decena($numc - 300));
            }
        } elseif ($numc >= 200 && $numc <= 299) {
            $numce = 'DOSCIENTOS ';
            if ($numc > 200) {
                $numce = $numce . (decena($numc - 200));
            }
        } elseif ($numc >= 100 && $numc <= 199) {
            if ($numc == 100) {
                $numce = 'CIEN ';
            } else {
                $numce = 'CIENTO ' . (decena($numc - 100));
            }
        }
    } else {
        $numce = decena($numc);
    }

    return $numce;
}

function miles($nummero)
{
    if ($nummero >= 1000 && $nummero < 2000) {
        $numm = 'MIL ' . (centena($nummero % 1000));
    }
    if ($nummero >= 2000 && $nummero < 10000) {
        $numm = unidad(Floor($nummero / 1000)) . ' MIL ' . (centena($nummero % 1000));
    }
    if ($nummero < 1000) {
        $numm = centena($nummero);
    }

    return $numm;
}

function decmiles($numdmero)
{
    if ($numdmero == 10000) {
        $numde = 'DIEZ MIL';
    }

    if ($numdmero > 10000 && $numdmero < 20000) {
        $numde = decena(Floor($numdmero / 1000)) . 'MIL ' . (centena($numdmero % 1000));
    }
    if ($numdmero >= 20000 && $numdmero < 100000) {
        $numde = decena(Floor($numdmero / 1000)) . ' MIL ' . (miles($numdmero % 1000));
    }
    if ($numdmero < 10000) {
        $numde = miles($numdmero);
    }

    return $numde;
}

function cienmiles($numcmero)
{
    if ($numcmero == 100000) {
        $num_letracm = 'CIEN MIL';
    }

    if ($numcmero >= 100000 && $numcmero < 1000000) {
        $num_letracm = centena(Floor($numcmero / 1000)) . ' MIL ' . (centena($numcmero % 1000));
    }
    if ($numcmero < 100000) {
        $num_letracm = decmiles($numcmero);
    }

    return $num_letracm;
}

function millon($nummiero)
{
    if ($nummiero >= 1000000 && $nummiero < 2000000) {
        $num_letramm = 'UN MILLON ' . (cienmiles($nummiero % 1000000));
    }
    if ($nummiero >= 2000000 && $nummiero < 10000000) {
        $num_letramm = unidad(Floor($nummiero / 1000000)) . ' MILLONES ' . (cienmiles($nummiero % 1000000));
    }
    if ($nummiero < 1000000) {
        $num_letramm = cienmiles($nummiero);
    }

    return $num_letramm;
}

function decmillon($numerodm)
{
    if ($numerodm == 10000000) {
        $num_letradmm = 'DIEZ MILLONES';
    }

    if ($numerodm > 10000000 && $numerodm < 20000000) {
        $num_letradmm = decena(Floor($numerodm / 1000000)) . 'MILLONES ' . (cienmiles($numerodm % 1000000));
    }
    if ($numerodm >= 20000000 && $numerodm < 100000000) {
        $num_letradmm = decena(Floor($numerodm / 1000000)) . ' MILLONES ' . (millon($numerodm % 1000000));
    }
    if ($numerodm < 10000000) {
        $num_letradmm = millon($numerodm);
    }

    return $num_letradmm;
}

function cienmillon($numcmeros)
{
    if ($numcmeros == 100000000) {
        $num_letracms = 'CIEN MILLONES';
    }

    if ($numcmeros >= 100000000 && $numcmeros < 1000000000) {
        $num_letracms = centena(Floor($numcmeros / 1000000)) . ' MILLONES ' . (millon($numcmeros % 1000000));
    }
    if ($numcmeros < 100000000) {
        $num_letracms = decmillon($numcmeros);
    }

    return $num_letracms;
}

function milmillon($nummierod)
{
    if ($nummierod >= 1000000000 && $nummierod < 2000000000) {
        $num_letrammd = 'MIL ' . (cienmillon($nummierod % 1000000000));
    }
    if ($nummierod >= 2000000000 && $nummierod < 10000000000) {
        $num_letrammd = unidad(Floor($nummierod / 1000000000)) . ' MIL ' . (cienmillon($nummierod % 1000000000));
    }
    if ($nummierod < 1000000000) {
        $num_letrammd = cienmillon($nummierod);
    }

    return $num_letrammd;
}

function convertir($numero)
{
    $numf = milmillon($numero);
    return $numf;

    function add_ceros($numero, $ceros)
    {
        $order_diez = explode('.', $numero);
        $dif_diez = $ceros - strlen($order_diez[0]);
        for ($m = 0; $m < $dif_diez; $m++) {
            $insertar_ceros = 0;
            @$insertar_ceros .= 0;
        }
        return $insertar_ceros .= $numero;
    }
}
