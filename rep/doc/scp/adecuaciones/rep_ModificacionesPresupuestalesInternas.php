<?php

session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);

require_once '../../../../lib/constantes.php';
require_once '../../../../rep/lib/reportetabla.1.8.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/FicheroSefip.php';
require_once '../../../../lib/helpers.php';

class reportetabla extends PDF
{
    private $grupo;
    private $perfil;
    private $nvj;
    private $ur;
    private $usuario_id;
    private $usr_id;
    private $ancho;
    private $imagen;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        // Variables de sesi�n
        $this->perfil = $_SESSION[_S]['prf_id'];
        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->grupo = $_SESSION[_S]['gpo_id'];
        $this->ur = $_SESSION[_S]['ur_id'];
        $this->usuario_id = $_SESSION[_S]['usr_id'];

        $this->usr_id = new Usuario();
        $this->usr_id->setUsr_id($_SESSION[_S]['usr_id']);
    } //FIN __construct

    //Encabezado del documento
    public function Header()
    {
        $iyt = 0;
        switch ($this->getOrientation()) {
            case 'P':
                $this->ancho = 123;
                $this->imagen = 168;
                break;
            case 'L':
                $this->ancho = 190;
                $this->imagen = 235;
                break;
        } //FIN switch

        $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
        $gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
        $ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
        $mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';
        $tipoRep = (isset($_POST['tipoRep']) && $_POST['tipoRep'] != '') ? $_POST['tipoRep'] : '';

        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        if ($tipoRep != '') {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($ix + 30, $iy + 160, utf8Decode('CONTABILIDAD'), 45);
            $this->RotatedText($ix + 150, $iy + 160, utf8Decode('CONTABILIDAD'), 45);
            $this->RotatedText($ix, $iy + 80, utf8Decode('CONTABILIDAD'), 45);
            $this->RotatedText($ix + 150, $iy + 80, utf8Decode('CONTABILIDAD'), 45);
        } //fin tipoRep

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $objUnidadResponsable = new UnidadResponsable();
        $objUnidadResponsable->setGpo_id($gpo_id);
        $objUnidadResponsable->setUr_id($ur_id);
        $objUnidadResponsable->select();

        $datos = $this->getDatos();

        if (!$datos) {
            $sqlFolio = '
                SELECT omo_id, mod_id,ecf_id clavefin, mod_tipoMovimiento, mod_clave, mod_concepto, mod_Subsecretaria, tmd_nombre
                FROM scp_MaestroModificacion b
                INNER JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
                INNER JOIN cat_TipoModificacion f using(tmd_id)
                WHERE omo_id = ?
                AND mod_gpo_id = ?
                AND mod_ur_id = ?
                AND mod_id = ?;
            ';

            $parametros = [
                $omo_id,
                $gpo_id,
                $ur_id,
                $mod_id,
            ];

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sqlFolio);
            $conexion->consultar($parametros);
            $conexion->desconectar();
            $datos = $conexion->extraer();
        } //fin if datos

        $datos['mov_id'] = (!isset($datos['mov_id']) ? '' : $datos['mov_id']);
        $this->Image('../../../../img/' . LOGO_ESCUDO, $ix + 164, $iy - 1, '85', '18', 'JPG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, 'CONTROL PRESUPUESTAL ' . APP_EJERCICIO, 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, 'UR: ' . $objUnidadResponsable->getGpo_id() . $objUnidadResponsable->getUr_id() . ' - ' . utf8Decode($objUnidadResponsable->getNombre()), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 2;
        $this->Cell(180, 5, utf8Decode('SOLICITUD DE ADECUACIÓN PRESUPUESTARIA'), 0, 1, 'L');

        $this->SetY($iy + 3 + $iyt);
        $this->SetX($ix + 7);

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(17);

        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->SetWidths([185]);
        $this->SetAligns(['L']);
        $this->SetBorders(0);
        $this->Row([utf8Decode('FOLIO : ' . str_pad($mod_id, 6, '0', STR_PAD_LEFT))]);

        $this->Ln();
        $this->SetXY($ix, $ytmp + 7);
        $ytmp = $this->GetY();
        $this->SetX($ix + 7);
        $this->SetFont('UniviaPro-Bold', 'B', 8);

        if (isset($datos['clavefin'])) {
            $datosCveFin = new AccesoDatos();
            $datosCveFin->conectar();
            $datosCveFin->setSql(
                '
                SELECT cfi_clave,cfi_nombre
                FROM enc_ClaveFinanciamiento
                JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                WHERE ecf_id= ? ;'
            );
            $datosCveFin->consultar([$datos['clavefin']]);
            $datosCveFin->desconectar();
            $cvefin = $datosCveFin->extraer();

            $this->SetXY($ix + 7, $iy + 31);
            $this->Cell(5, 5, $cvefin['cfi_clave'] . ' - ' . utf8Decode($cvefin['cfi_nombre']), 0, 1, 'L');
        }
        $this->SetXY($ix - 70, $ytmp + 3);
        $this->Cell(34, 5, utf8Decode($datos['tmd_nombre']), 0, 1, 'C');

        $this->CheckPageBreak(25);
        $this->SetFont('UniviaPro-Regular', '', 7);
        $this->SetWidths([60, 70, 40, 20]);
        $this->SetAligns(['L', 'L', 'L', 'L']);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);

        $this->SetWidths([$this->ancho - 145, 8, 25, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
        $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetAltoLinea(2.5);
        $this->SetRelleno('si');
        if ($datos['mov_id'] <> '' && $datos['mov_id'] <> 'C') {
            $this->Row([
                'CLAVE PRESUPUESTAL',
                'TIPO',
                'MODIFICADO IMPORTE RESULTADO',
                'ENE',
                'FEB',
                'MAR',
                'ABR',
                'MAY',
                'JUN',
                'JUL',
                'AGO',
                'SEP',
                'OCT',
                'NOV',
                'DIC',
            ]);
        } else {
            $this->Row([
                'CLAVE PRESUPUESTAL',
                'TIPO',
                'MODIFICADO IMPORTE',
                'ENE',
                'FEB',
                'MAR',
                'ABR',
                'MAY',
                'JUN',
                'JUL',
                'AGO',
                'SEP',
                'OCT',
                'NOV',
                'DIC',
            ]);
        } //fin mov_id

        $this->ln(1);
        $this->SetAltoLinea(2);
        $this->SetRelleno('no');
    } //Fin Header

    //Encabezado del documento
    public function Footer($lastPage = true)
    {
        $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
        $gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
        $ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
        $mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';
        switch ($this->getOrientation()) {
            case 'P':
                $x = 15;
                $distancia = 5;
                break;
            case 'L':
                $x = 30;
                $distancia = 40;
                break;
        } //fin switch

        $datos = $this->getDatos();
        $this->SetY(-40);
        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Numero de Pagina
        if ($lastPage && ($datos != null || $datos != '')) {
            switch ($this->nvj) {
                case 1: //UR
                case 2: //UE
                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mod_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mod_ur_id']);
                    $objUnidadResponsable->select();

                    switch ($datos['emo_id']) {
                        case 1:
                        case 3:
                            switch ($this->grupo . $this->ur) {
                                case 118:
                                    //Verifica el perfil del usuario
                                    switch ($this->perfil) {
                                        case 'S': //Supervisor
                                            $datosUsuario = new Usuario();
                                            $datosUsuario->setUsr_id($this->usuario_id);
                                            $datosUsuario->select();

                                            //Instancia la Unidad Responsable
                                            $datosUrRH = new UnidadResponsable();
                                            $datosUrRH->setGpo_id(1);
                                            $datosUrRH->setUr_id(18);
                                            $datosUrRH->select();

                                            $elabora = utf8Decode($datosUsuario->getNombre() . ' '
                                                . $datosUsuario->getPaterno() . ' '
                                                . $datosUsuario->getMaterno());
                                            $cargoUsuario = '';
                                            $revisorAdmvo = $datosUrRH->getNombreAdministrativo();
                                            $cargoAdmvo = $datosUrRH->getCargoAdministrativo();
                                            $voboTitular = $datosUrRH->getNombreTitular();
                                            $cargoTitular = $datosUrRH->getCargoTitular();

                                            //Registra o actualiza las firmas
                                            $sql = "
                                                INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id,
                                                mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2, mod_cargo3,mod_firma3)
                                                VALUES (':omo_id, :gpo_id, :ur_id, :mod_id, :cargoUs, :elabora, :cargoAdm, :revisorAdm, :cargoTit, :voboTit')
                                                ON DUPLICATE KEY UPDATE
                                                mod_cargo1='" . $cargoUsuario . "',
                                                mod_firma1='" . $elabora . "',
                                                mod_cargo2='" . $cargoAdmvo . "',
                                                mod_firma2='" . $revisorAdmvo . "',
                                                mod_cargo3='" . $cargoTitular . "',
                                                mod_firma3='" . $voboTitular . "';";

                                            $parametros = [
                                                ':omo_id'     => $datos['omo_id'],
                                                ':gpo_id'     => $objUnidadResponsable->getGpo_id(),
                                                ':ur_id'      => $objUnidadResponsable->getUr_id(),
                                                ':mod_id'     => $datos['mod_id'],
                                                ':cargoUs'    => $cargoUsuario,
                                                ':elabora'    => $elabora,
                                                ':cargoAdm'   => $cargoAdmvo,
                                                ':revisorAdm' => $revisorAdmvo,
                                                ':cargoTit'   => $cargoTitular,
                                                ':voboTit'    => $voboTitular,
                                            ];

                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($parametros);

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mod_cargo1,mod_firma1,mod_cargo2,mod_firma2,mod_cargo3,mod_firma3
                                                FROM scp_ModificacionFirma
                                                WHERE omo_id= ?
                                                and mod_gpo_id= ?
                                                and mod_ur_id= ?
                                                and mod_id=?;
                                            ';

                                            $parametros = [
                                                $datos['omo_id'],
                                                $objUnidadResponsable->getGpo_id(),
                                                $objUnidadResponsable->getUr_id(),
                                                $datos['mod_id'],
                                            ];

                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($parametros);
                                            $datosFirma = $firmasReportes->extraer();

                                            $this->Cell(92.5, 2, utf8Decode(''), 0, 0, 'C');
                                            $this->Ln(5);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $this->Cell(100.5, 2, 'ELABORÓ', 0, 0, 'C');
                                            $this->SetX(20);
                                            $this->Cell(230, 2, 'REVISÓ', 0, 0, 'C');
                                            $this->SetX(170);
                                            $this->Cell(92.5, 2, 'Vo.Bo.', 0, 1, 'C');
                                            $this->Ln(1);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $y = $this->GetY();
                                            $this->Cell(100.5, 4, $datosFirma['mod_firma1'], 0, 0, 'C');
                                            $this->SetX(100);
                                            $this->Cell(70, 4, utf8Decode($datosFirma['mod_firma2']), 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(99, 4, utf8Decode($datosFirma['mod_firma3']), 0, 1, 'C');
                                            $this->Ln(2);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $this->Cell(252, 2, utf8Decode($datosFirma['mod_cargo2']), 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo3']), 0, 1, 'C');
                                            break;
                                        case 'A':
                                        case 'C':
                                        case 'T':
                                            //Registra o actualiza las firmas
                                            $sql = "
                                                INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id,
                                                mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2)
                                                VALUES (':omo_id, :gpo_id, :ur_id, :mod_id, :cargoAdm, :revisorAdm, :cargoTit, :titular')
                                                ON DUPLICATE KEY UPDATE
                                                mod_cargo1='" . $objUnidadResponsable->getCargoAdministrativo() . "',
                                                mod_firma1='" . $objUnidadResponsable->getNombreAdministrativo() . "',
                                                mod_cargo2='" . $objUnidadResponsable->getCargoTitular() . "',
                                                mod_firma2='" . $objUnidadResponsable->getNombreTitular() . "';
                                            ";

                                            $parametros = [
                                                ':omo_id'     => $datos['omo_id'],
                                                ':gpo_id'     => $objUnidadResponsable->getGpo_id(),
                                                ':ur_id'      => $objUnidadResponsable->getUr_id(),
                                                ':mod_id'     => $datos['mod_id'],
                                                ':cargoAdm'   => $objUnidadResponsable->getCargoAdministrativo(),
                                                ':revisorAdm' => $objUnidadResponsable->getNombreAdministrativo(),
                                                ':cargoTit'   => $objUnidadResponsable->getCargoTitular(),
                                                ':titular'    => $objUnidadResponsable->getNombreTitular(),
                                            ];

                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($parametros);

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mod_cargo1,mod_firma1,mod_cargo2,mod_firma2
                                                FROM scp_ModificacionFirma
                                                WHERE omo_id=? 
                                                and mod_gpo_id=?
                                                AND mod_ur_id=?
                                                AND mod_id=?;
                                            ';
                                            $parametros = [
                                                $omo_id,
                                                $gpo_id,
                                                $ur_id,
                                                $mod_id,
                                            ];
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($parametros);
                                            $datosFirma = $firmasReportes->extraer();

                                            $this->Cell(92.5, 2, utf8Decode(''), 0, 0, 'C');
                                            $this->Ln(5);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $this->Cell(100.5, 2, 'REVISÓ', 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(92.5, 2, 'Vo.Bo.', 0, 1, 'C');
                                            $this->Ln(1);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $y = $this->GetY();
                                            $this->Cell(100.5, 4, utf8Decode($datosFirma['mod_firma1']), 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(92.5, 4, utf8Decode($datosFirma['mod_firma2']), 0, 1, 'C');
                                            $this->Ln(2);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo1']), 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(92, 2, utf8Decode($datosFirma['mod_cargo2']), 0, 1, 'C');
                                            break;
                                    }
                                    break;
                                default:
                                    if (($objUnidadResponsable->getRh() == 1 ||
                                        (
                                            $objUnidadResponsable->getGpo_id() == 5 &&
                                            ($objUnidadResponsable->getUr_id() == '06' ||
                                                $objUnidadResponsable->getUr_id() == '07' ||
                                                $objUnidadResponsable->getUr_id() == '08' ||
                                                $objUnidadResponsable->getUr_id() == 14 ||
                                                $objUnidadResponsable->getUr_id() == 15 ||
                                                $objUnidadResponsable->getUr_id() == 19 ||
                                                $objUnidadResponsable->getUr_id() == 25 ||
                                                $objUnidadResponsable->getUr_id() == 35 ||
                                                $objUnidadResponsable->getUr_id() == 36 ||
                                                $objUnidadResponsable->getUr_id() == 37 ||
                                                $objUnidadResponsable->getUr_id() == 38 ||
                                                $objUnidadResponsable->getUr_id() == 42 ||
                                                $objUnidadResponsable->getUr_id() == 43 ||
                                                $objUnidadResponsable->getUr_id() == 44 ||
                                                $objUnidadResponsable->getUr_id() == 45 ||
                                                $objUnidadResponsable->getUr_id() == 46 ||
                                                $objUnidadResponsable->getUr_id() == 47 ||
                                                $objUnidadResponsable->getUr_id() == 48 ||
                                                $objUnidadResponsable->getUr_id() == 49 ||
                                                $objUnidadResponsable->getUr_id() == 50 ||
                                                $objUnidadResponsable->getUr_id() == 51 ||
                                                $objUnidadResponsable->getUr_id() == 52)
                                        )) && $datos['omo_id'] == 'A' && $datos['tfm_id'] == 'A') {

                                        $datosUrRH = new UnidadResponsable();
                                        $datosUrRH->setGpo_id(1);
                                        $datosUrRH->setUr_id(18);
                                        $datosUrRH->select();

                                        //Registra o actualiza las firmas
                                        $sql = "INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id,
                                            mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2,mod_cargo3,mod_firma3)
                                            VALUES (':omo_id, :gpo_id, :ur_id, :mod_id, :cargoAdm, :revisorAdm, :cargoTit, :titular, :cargoRH, :titular')
                                            ON DUPLICATE KEY UPDATE
                                            mod_cargo1='" . $objUnidadResponsable->getCargoAdministrativo() . "',
                                            mod_firma1='" . $objUnidadResponsable->getNombreAdministrativo() . "',
                                            mod_cargo2='" . $objUnidadResponsable->getCargoTitular() . "',
                                            mod_firma2='" . $objUnidadResponsable->getNombreTitular() . "',
                                            mod_cargo3='" . $datosUrRH->getCargoTitular() . "',
                                            mod_firma3='" . $datosUrRH->getNombreTitular() . "';
                                        ";

                                        $parametros = [
                                            ':omo_id'     => $datos['omo_id'],
                                            ':gpo_id'     => $objUnidadResponsable->getGpo_id(),
                                            ':ur_id'      => $objUnidadResponsable->getUr_id(),
                                            ':mod_id'     => $datos['mod_id'],
                                            ':cargoAdm'   => $objUnidadResponsable->getCargoAdministrativo(),
                                            ':revisorAdm' => $objUnidadResponsable->getNombreAdministrativo(),
                                            ':cargoTit'   => $objUnidadResponsable->getCargoTitular(),
                                            ':titular'    => $objUnidadResponsable->getNombreTitular(),
                                            ':cargoRH'    => $datosUrRH->getCargoTitular(),
                                            ':titular'    => $datosUrRH->getNombreTitular(),
                                        ];

                                        $firmasReportes = new AccesoDatos();
                                        $firmasReportes->conectar();
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($parametros);

                                        //Obtiene las firmas
                                        $sql = '
                                            SELECT mod_cargo1,mod_firma1,mod_cargo2,mod_firma2,mod_cargo3,mod_firma3
                                            FROM scp_ModificacionFirma
                                            WHERE omo_id= ?
                                            AND mod_gpo_id=?
                                            AND mod_ur_id=?
                                            AND mod_id=?;
                                        ';

                                        $parametros = [
                                            $datos['omo_id'],
                                            $objUnidadResponsable->getGpo_id(),
                                            $objUnidadResponsable->getUr_id(),
                                            $datos['mod_id'],
                                        ];

                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($parametros);
                                        $datosFirma = $firmasReportes->extraer();

                                        $this->Cell(92.5, 2, utf8Decode(''), 0, 0, 'C');
                                        $this->Ln(5);
                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                        $this->Cell(80, 2, 'JEFE ADMINISTRATIVO', 0, 0, 'C');
                                        $this->SetX(30);
                                        $this->Cell(214, 2, 'TITULAR DE LA DEPENDENCIA/ENTIDAD', 0, 0, 'C');
                                        $this->SetX(170);
                                        $this->Cell(110, 2, 'Vo.Bo.', 0, 1, 'C');
                                        $this->Ln(1);
                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                        $y = $this->GetY();
                                        $this->Cell(80, 4, utf8Decode($datosFirma['mod_firma1']), 0, 0, 'C');
                                        $this->SetX(132);
                                        $this->Cell(15, 4, utf8Decode($datosFirma['mod_firma2']), 0, 0, 'C');
                                        $this->SetX(100);
                                        $this->Cell(250, 4, utf8Decode($datosFirma['mod_firma3']), 0, 1, 'C');

                                        $this->Ln(.3);
                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                        $this->Cell(83, 2, utf8Decode($datosFirma['mod_cargo1']), 0, 0, 'C');
                                        $this->SetX(90);
                                        $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo2']), 0, 0, 'C');
                                        $this->SetX(175);
                                        $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo3']), 0, 1, 'C');

                                    } else {
                                        //Registra o actualiza las firmas
                                        $sql = "
                                            INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id, mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2,
                                            mod_cargo3, mod_firma3, mod_cargo4, mod_firma4, mod_cargo5, mod_firma5, mod_cargo6, mod_firma6, mod_cargo7, mod_firma7,usr_id)
                                            VALUES (:omo_id,:gpo_id,:ur_id,:mod_id,:cargoAdm,:titularAdm,:cargoTit,:nombreTit, '', '', '', '', '', '', '', '', '', '',:usr_id)
                                            ON DUPLICATE KEY UPDATE mod_cargo1='" . $objUnidadResponsable->getCargoAdministrativo() . "',
                                            mod_firma1='" . $objUnidadResponsable->getNombreAdministrativo() . "',
                                            mod_cargo2='" . $objUnidadResponsable->getCargoTitular() . "',
                                            mod_firma2='" . $objUnidadResponsable->getNombreTitular() . "';
                                        ";

                                        $parametros = [
                                            ':omo_id'     => $datos['omo_id'],
                                            ':gpo_id'     => $objUnidadResponsable->getGpo_id(),
                                            ':ur_id'      => $objUnidadResponsable->getUr_id(),
                                            ':mod_id'     => $datos['mod_id'],
                                            ':cargoAdm'   => $objUnidadResponsable->getCargoAdministrativo(),
                                            ':titularAdm' => $objUnidadResponsable->getNombreAdministrativo(),
                                            ':cargoTit'   => $objUnidadResponsable->getCargoTitular(),
                                            ':nombreTit'  => $objUnidadResponsable->getNombreTitular(),
                                            ':usr_id'     => $this->usuario_id,
                                        ];

                                        $firmasReportes = new AccesoDatos();
                                        $firmasReportes->conectar();
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($parametros);

                                        //Obtiene las firmas
                                        $sql = '
                                            SELECT mod_cargo1,mod_firma1,mod_cargo2,mod_firma2
                                            FROM scp_ModificacionFirma
                                            WHERE omo_id=?
                                            AND mod_gpo_id=?
                                            AND mod_ur_id=?
                                            AND mod_id=?;
                                        ';

                                        $parametros = [
                                            $datos['omo_id'],
                                            $objUnidadResponsable->getGpo_id(),
                                            $objUnidadResponsable->getUr_id(),
                                            $datos['mod_id'],
                                        ];

                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($parametros);
                                        $datosFirma = $firmasReportes->extraer();

                                        $this->Cell(92.5, 2, utf8Decode(''), 0, 0, 'C');
                                        $this->Ln(12.5);

                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                        $this->Cell($distancia + 60, 2, 'JEFE ADMINISTRATIVO', 0, 0, 'C');
                                        $this->SetX(165);
                                        $this->Cell($distancia + 50, 2, 'TITULAR DE LA DEPENDENCIA/ENTIDAD', 0, 1, 'C');
                                        $this->Ln(1);

                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                        $y = $this->GetY();
                                        $this->Cell($distancia + 60, 4, utf8Decode($datosFirma['mod_firma1']), 0, 0, 'C');
                                        $this->SetX(165);
                                        $this->Cell($distancia + 50, 4, utf8Decode($datosFirma['mod_firma2']), 0, 1, 'C');
                                        $this->Ln(.3);

                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                        $this->Cell($distancia + 60, 2, utf8Decode($datosFirma['mod_cargo1']), 0, 0, 'C');
                                        $this->SetX(165);
                                        $this->Cell($distancia + 60, 2, utf8Decode($datosFirma['mod_cargo2']), 0, 1, 'C');
                                    }
                                    break;
                            }
                            break;
                    }
                    break;
            }
        } elseif (($datos != null || $datos != '')) {

            $this->SetY(-20);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(10, 2, utf8Decode('CONTINUA...'), 0, 1, 'L');

            switch ($this->nvj) {
                case 1: //UR
                case 2: //UE
                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mod_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mod_ur_id']);
                    $objUnidadResponsable->select();

                    //ORIGEN A
                    $this->Ln(10);
                    if (($objUnidadResponsable->getRh() == 1 ||
                        (
                            $objUnidadResponsable->getGpo_id() == 5 &&
                            ($objUnidadResponsable->getUr_id() == '06' ||
                                $objUnidadResponsable->getUr_id() == '07' ||
                                $objUnidadResponsable->getUr_id() == '08' ||
                                $objUnidadResponsable->getUr_id() == 14 ||
                                $objUnidadResponsable->getUr_id() == 15 ||
                                $objUnidadResponsable->getUr_id() == 19 ||
                                $objUnidadResponsable->getUr_id() == 25 ||
                                $objUnidadResponsable->getUr_id() == 35 ||
                                $objUnidadResponsable->getUr_id() == 36 ||
                                $objUnidadResponsable->getUr_id() == 37 ||
                                $objUnidadResponsable->getUr_id() == 38 ||
                                $objUnidadResponsable->getUr_id() == 42 ||
                                $objUnidadResponsable->getUr_id() == 43 ||
                                $objUnidadResponsable->getUr_id() == 44 ||
                                $objUnidadResponsable->getUr_id() == 45 ||
                                $objUnidadResponsable->getUr_id() == 46 ||
                                $objUnidadResponsable->getUr_id() == 47 ||
                                $objUnidadResponsable->getUr_id() == 48 ||
                                $objUnidadResponsable->getUr_id() == 49 ||
                                $objUnidadResponsable->getUr_id() == 50 ||
                                $objUnidadResponsable->getUr_id() == 51 ||
                                $objUnidadResponsable->getUr_id() == 52)
                        )) && $datos['omo_id'] == 'A' && $datos['tfm_id'] == 'A') {
                        $rubricas = 'Rúbricas:________________________, ________________________,________________________';
                        $this->Cell(252, 2, utf8Decode($rubricas), 0, 1, 'R');
                    } else {
                        $rubricas = 'Rúbricas:________________________, ________________________';
                        $this->Cell(252, 2, utf8Decode($rubricas), 0, 1, 'R');
                    }
                    break;
            }
        } //fin lastPage
        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(
            57,
            2,
            utf8Decode($this->usr_id->getUsr_id() . ' - ' . $this->usr_id->getUsuario() . ' '),
            0,
            'L'
        );

        $this->SetY($y);
        $this->setX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 57 + $distancia);
        $this->MultiCell(
            57,
            2,
            utf8Decode('Página ') . $this->PageNo() . '/{nb}',
            0,
            'C'
        );

        $this->SetY($y);
        $this->setX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 104 + 2 * $distancia);
        $this->MultiCell(63, 2, date('d/m/y') . ' - ' . date('H:i:s'), 0, 'R');
    } // FIN footer
} //Fin clase reportetabla

//Parametros recibidos
$omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
$gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
$ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
$mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';
$autorizar = (isset($_POST['bandera']) && $_POST['bandera'] != '') ? $_POST['bandera'] : '';
$tipo = '';

switch ($_SESSION[_S]['nvj_id']) {
    case 1: //UR
    case 2: //UE
        $tipo = "and tcp_id!='P'";
        break;
    default:
        $tipo = '';
        break;
} // Fin switch nvj

$sql = "
    SELECT modificaciones.*,d.pe_nombre,pv.pre_vigente,pv.vig_ene,pv.vig_feb,pv.vig_mar,
        pv.vig_abr,pv.vig_may, pv.vig_jun,pv.vig_jul,pv.vig_ago,pv.vig_sep,pv.vig_oct,
        pv.vig_nov,pv.vig_dic, p.pre_claveCompleta, f.tmd_nombre, o.tmd_id,o.tfm_id
    FROM (
        SELECT b.omo_id, b.mod_id, b.mod_concepto,b.emo_id,a.mov_id, b.mod_clave, a.dmo_importe,b.enc_oop,b.ecf_id as clavefin,
            a.dmo_ene, a.dmo_feb, a.dmo_mar, a.dmo_abr, a.dmo_may, a.dmo_jun, a.dmo_jul,
            a.dmo_ago, a.dmo_sep, a.dmo_oct, a.dmo_nov, a.dmo_dic,
            a.gpo_id, a.ur_id, a.ue_id, a.epg_id, a.pg_id, a.pe_id, a.ecf_id,a.tcp_id,
            a.vig_ene pa_ene, a.vig_feb pa_feb, a.vig_mar pa_mar, a.vig_abr pa_abr, a.vig_may pa_may,
            a.vig_jun pa_jun, a.vig_jul pa_jul, a.vig_ago pa_ago, a.vig_sep pa_sep, a.vig_oct pa_oct,
            a.vig_nov pa_nov, a.vig_dic pa_dic, a.pre_vigente pa_importe,b.mod_ue_id
            FROM scp_MaestroModificacion b
        JOIN scp_DetalleModificacion a USING (omo_id,mod_gpo_id,mod_ur_id,mod_id)
        WHERE omo_id = ? AND mod_gpo_id=? AND mod_ur_id=?
            AND mod_id=? $tipo
    ) AS modificaciones
    JOIN cat_PartidaEspecifica d USING(pe_id,pg_id)
    JOIN presupuesto p USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
    JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
    JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
    JOIN cat_TipoModificacion f using(tmd_id);
";

$parametros = [
    $omo_id,
    $gpo_id,
    $ur_id,
    $mod_id,
];

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($parametros);
$conexion->desconectar();

$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
//password PDF
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP);
$pdf->setOrientation('L');
$pdf->AliasNbPages();

$fill = false;
$datos = $conexion->extraer();
$pdf->setDatos($datos);
$pdf->AddPage();
$total_a = 0;
$total_r = 0;

if ($datos != null || $datos != '') {

    do {
        switch ($datos['mov_id']) {
            case 'A':
                $tipomov = '(Amp.)';
                break;
            case 'R':
                $tipomov = '(Red.)';
                break;
            case 'C':
                $tipomov = '(Cal.)';
                break;
            default:
                $tipomov = '(XXX)';
                break;
        } //fin switch mov_id

        $pdf->SetFont('univiapro', '', 5);
        if (strlen($datos['dmo_importe']) > 11 || strlen($datos['dmo_ene']) > 11 || strlen($datos['dmo_feb']) > 11 || strlen($datos['dmo_mar']) > 11 || strlen($datos['dmo_abr']) > 11 || strlen($datos['dmo_may']) > 11 || strlen($datos['dmo_jun']) > 11 || strlen($datos['dmo_jul']) > 11 || strlen($datos['dmo_ago']) > 11 || strlen($datos['dmo_sep']) > 11 || strlen($datos['dmo_oct']) > 11 || strlen($datos['dmo_nov']) > 11 || strlen($datos['dmo_dic']) > 11 || strlen($datos['pa_importe']) > 11 || strlen($datos['pa_ene']) > 11 || strlen($datos['pa_feb']) > 11 || strlen($datos['pa_mar']) > 11 || strlen($datos['pa_abr']) > 11 || strlen($datos['pa_may']) > 11 || strlen($datos['pa_jun']) > 11 || strlen($datos['pa_jul']) > 11 || strlen($datos['pa_ago']) > 11 || strlen($datos['pa_sep']) > 11 || strlen($datos['pa_oct']) > 11 || strlen($datos['pa_nov']) > 11 || strlen($datos['pa_dic']) > 11) {
            $pdf->SetFont('univiapro', '', 4);
        }

        $pdf->SetAltoLinea(1);
        $pdf->SetWidths([45, 8, 25, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
        $pdf->SetAligns(['L', 'C', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R']);

        switch ($datos['emo_id']) {
            case 14: //Autorizado por egresos
            case 15: //Autorizado por planeacion
            case 16: //Autorizado por Ur
                $pdf->Row(
                    [$datos['pre_claveCompleta'] . ' - ' . $datos['tcp_id'],
                    $tipomov, number_format($datos['pa_importe'], 2),
                    number_format($datos['pa_ene'], 2), number_format($datos['pa_feb'], 2),
                    number_format($datos['pa_mar'], 2), number_format($datos['pa_abr'], 2),
                    number_format($datos['pa_may'], 2), number_format($datos['pa_jun'], 2),
                    number_format($datos['pa_jul'], 2), number_format($datos['pa_ago'], 2),
                    number_format($datos['pa_sep'], 2), number_format($datos['pa_oct'], 2),
                    number_format($datos['pa_nov'], 2), number_format($datos['pa_dic'], 2)]
                );
                break;
            default: //En estado 1,3,6,7,8,9
                $pdf->Row(
                    [$datos['pre_claveCompleta'] . ' - ' . $datos['tcp_id'],
                    $tipomov, number_format($datos['pre_vigente'], 2),
                    number_format($datos['vig_ene'], 2), number_format($datos['vig_feb'], 2),
                    number_format($datos['vig_mar'], 2), number_format($datos['vig_abr'], 2),
                    number_format($datos['vig_may'], 2), number_format($datos['vig_jun'], 2),
                    number_format($datos['vig_jul'], 2), number_format($datos['vig_ago'], 2),
                    number_format($datos['vig_sep'], 2), number_format($datos['vig_oct'], 2),
                    number_format($datos['vig_nov'], 2), number_format($datos['vig_dic'], 2)]
                );
                break;
        } //fin switch emo_id

        $pdf->SetAltoLinea(1.8);
        $pdf->Row([
            utf8Decode($datos['pe_nombre']),
            '', number_format($datos['dmo_importe'], 2),
            number_format($datos['dmo_ene'], 2),
            number_format($datos['dmo_feb'], 2),
            number_format($datos['dmo_mar'], 2),
            number_format($datos['dmo_abr'], 2),
            number_format($datos['dmo_may'], 2),
            number_format($datos['dmo_jun'], 2),
            number_format($datos['dmo_jul'], 2),
            number_format($datos['dmo_ago'], 2),
            number_format($datos['dmo_sep'], 2),
            number_format($datos['dmo_oct'], 2),
            number_format($datos['dmo_nov'], 2),
            number_format($datos['dmo_dic'], 2),
        ]);
        $pdf->SetAltoLinea(1);

        switch ($datos['emo_id']) {
            case 14: //Autorizado por egresos
            case 15: //Autorizado por planeaci�n
            case 16: //Aurorizado por UR
                $pdf->Row(
                    [
                    '', '',
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_importe'] + $datos['dmo_importe'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_importe'] - $datos['dmo_importe'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_ene'] + $datos['dmo_ene'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_ene'] - $datos['dmo_ene'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_feb'] + $datos['dmo_feb'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_feb'] - $datos['dmo_feb'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_mar'] + $datos['dmo_mar'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_mar'] - $datos['dmo_mar'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_abr'] + $datos['dmo_abr'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_abr'] - $datos['dmo_abr'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_may'] + $datos['dmo_may'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_may'] - $datos['dmo_may'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_jun'] + $datos['dmo_jun'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_jun'] - $datos['dmo_jun'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_jul'] + $datos['dmo_jul'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_jul'] - $datos['dmo_jul'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_ago'] + $datos['dmo_ago'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_ago'] - $datos['dmo_ago'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_sep'] + $datos['dmo_sep'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_sep'] - $datos['dmo_sep'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_oct'] + $datos['dmo_oct'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_oct'] - $datos['dmo_oct'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_nov'] + $datos['dmo_nov'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_nov'] - $datos['dmo_nov'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['pa_dic'] + $datos['dmo_dic'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pa_dic'] - $datos['dmo_dic'], 2) : '')]
                );
                break;
            default: //1,3,6,7,8,9
                $pdf->Row(
                    [
                    '', '',
                    ($datos['mov_id'] == 'A') ? number_format($datos['pre_vigente'] + $datos['dmo_importe'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['pre_vigente'] - $datos['dmo_importe'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_ene'] + $datos['dmo_ene'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_ene'] - $datos['dmo_ene'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_feb'] + $datos['dmo_feb'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_feb'] - $datos['dmo_feb'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_mar'] + $datos['dmo_mar'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_mar'] - $datos['dmo_mar'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_abr'] + $datos['dmo_abr'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_abr'] - $datos['dmo_abr'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_may'] + $datos['dmo_may'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_may'] - $datos['dmo_may'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_jun'] + $datos['dmo_jun'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_jun'] - $datos['dmo_jun'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_jul'] + $datos['dmo_jul'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_jul'] - $datos['dmo_jul'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_ago'] + $datos['dmo_ago'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_ago'] - $datos['dmo_ago'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_sep'] + $datos['dmo_sep'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_sep'] - $datos['dmo_sep'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_oct'] + $datos['dmo_oct'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_oct'] - $datos['dmo_oct'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_nov'] + $datos['dmo_nov'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_nov'] - $datos['dmo_nov'], 2) : ''),
                    ($datos['mov_id'] == 'A') ? number_format($datos['vig_dic'] + $datos['dmo_dic'], 2) :
                    (($datos['mov_id'] == 'R') ? number_format($datos['vig_dic'] - $datos['dmo_dic'], 2) : '')]
                );
                break;
        } //fin switch emo_id

        $xtm = $pdf->getX();
        $ytm = $pdf->getY();
        $pdf->ln(1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->setDrawColor(220, 220, 220);
        $pdf->SetLineWidth(.5);
        $pdf->Line($xtm, $ytm, 268, $ytm);

        switch ($datos['mov_id']) {
            case 'A': //Ampliacion
            case 'C': //Recalendarizacion
                $total_a = $total_a + $datos['dmo_importe'];
                break;
            default: //Recalendarizacion
                $total_r = $total_r + $datos['dmo_importe'];
                break;
        } //Fin switch mov_id

        $pdf->Ln(1);
        if ($pdf->getY() > 160) {
            $pdf->addPage();
        }
        if ($fill) {
            $fill = false;
        }
    } while ($datos = $conexion->extraer());
}

$datos = $pdf->getDatos();

if ($datos != null || $datos != '') {
    # code...
    switch ($datos['tmd_id']) {
        case 'A':
            $tipom = 'TOTAL TRASPASO INTRAINSTITUCIONAL AMPLIACIÓN:' . '    ' .
            number_format($total_a, 2) . '   ' .
            'TOTAL TRASPASO INTRAINSTITUCIONAL REDUCCIÓN:' . '    ' .
            number_format($total_r, 2);
            break;
        case 'C':
            $tipom = 'TOTAL RECALENDARIZADO:' . '   ' . number_format($total_a, 2);
            break;
        default:
            $tipom = '';
            break;
    } //fin switch tmd_id


    $pdf->SetAltoLinea(2.5);
    $pdf->Ln(5);
    $pdf->SetFont('UniviaPro-Bold', 'B', 7);
    $pdf->SetWidths([300]);
    $pdf->SetAligns(['L']);
    $pdf->SetBorders(0);
    $pdf->Row([utf8Decode($tipom)]);
}

$datos = $pdf->getDatos();
//datos del folio en caso de no tener movimientos
if (!$datos) {
    $sqlFolio = '
        SELECT mod_id,ecf_id,mod_tipoMovimiento,mod_clave,mod_concepto
        FROM scp_MaestroModificacion b
        WHERE omo_id =?
        AND mod_gpo_id=?
        AND mod_ur_id=?
        AND mod_id=?;
    ';
    $parametros = [
        $omo_id,
        $gpo_id,
        $ur_id,
        $mod_id,
    ];
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sqlFolio);
    $conexion->consultar($parametros);
    $conexion->desconectar();
    $datos = $conexion->extraer();
} //fin $datos

$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->SetWidths([200]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
$pdf->Row([utf8Decode('JUSTIFICACIÓN:') . '  ' . utf8Decode($datos['mod_concepto'])]);

if ($autorizar) {
    $archivo = $datos['mod_clave'] . '.pdf';
    $UR = $gpo_id . $ur_id;
    $name = dirname(__FILE__) . '/../../../../archivos/' . $archivo;

    $pdf->Output('F', $name); //guarda el pdf

    $upload = new FicheroSefip();
    $file = ['name' => $name, 'tmp_name' => $name, 'eslocal' => true];
    $upload->setTipos(['pdf']);
    $upload->setFichero($file);
    $upload->setModulo('scp');
    $upload->setfile($archivo);
    $upload->setUrFichero($UR);
    $upload->setAnioFichero(APP_EJERCICIO);

    $upload->setRuta('adecuaciones/' . date('m') . '/autorizacionFolio/internas');

    if ($upload->upload()) {
        if (file_exists($name)) {
            unlink($name);
        }
        $pdf->Output();
    } else {
        echo 'Ocurrio un error al guardar el archivo';
    }
} else {
    $pdf->Output();
} //fin $autorizar
