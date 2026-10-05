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
    private $perfil;
    private $nvj;
    private $gpo;
    private $ur_id;
    private $usuario_id;
    private $subSecretaria;
    private $usr_id;
    public $ur;
    public $ue;
    public $drh;
    private $ancho;
    private $imagen;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        // Variables de sesión
        $this->perfil = $_SESSION[_S]['prf_id'];
        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->gpo = $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['ur_id'];
        $this->usuario_id = $_SESSION[_S]['usr_id'];
        $this->subSecretaria = $_SESSION[_S]['ssc_id'];

        $this->usr_id = new Usuario();
        $this->usr_id->setUsr_id($_SESSION[_S]['usr_id']);
        $this->usr_id->select();

        $this->ur = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 2 && isset($_SESSION[_S]['prm']['S']['1']['1']['7']) ? true : false);
        $this->ue = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 1 ? true : false);
        $this->drh = (isset($_SESSION[_S]['prm']['S']['1']['1']['82']) ? true : false);
    } //FIN __construct

    public function Header() //Encabezado del documento
    {
        switch ($this->getOrientation()) {
            case 'P':
                $this->ancho = 123;
                $this->imagen = 168;
                break;
            case 'L':
                $this->ancho = 190;
                $this->imagen = 235;
                break;
        } //fin switch

        $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
        $gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
        $ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
        $mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';
        $tipoRep = (isset($_POST['tipoRep']) && $_POST['tipoRep'] != '') ? $_POST['tipoRep'] : '';
        $mod_tipoMovimiento = (isset($_POST['mod_tipoMovimiento']) && $_POST['mod_tipoMovimiento'] != '') ? $_POST['mod_tipoMovimiento'] : '';

        $pne = (isset($_POST['mod_Vobo']) && $_POST['mod_Vobo'] != '') ? $_POST['mod_Vobo'] : 0;

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
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $iyt = 0;

        $objUnidadResponsable = new UnidadResponsable();
        $objUnidadResponsable->setGpo_id($gpo_id);
        $objUnidadResponsable->setUr_id($ur_id);
        $objUnidadResponsable->select();

        $datos = $this->getDatos();
        if (!$datos) {
            $sqlFolio = '
                SELECT omo_id, mod_id,ecf_id clavefin, mod_tipoMovimiento, mod_tipoMovimiento as mov_id, mod_clave, mod_concepto, mod_Subsecretaria, tmd_nombre, emo_id
                FROM scp_MaestroModificacion b
                INNER JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
                INNER JOIN cat_TipoModificacion f using(tmd_id)
                WHERE omo_id = ?
                AND mod_gpo_id= ?
                AND mod_ur_id= ?
                AND mod_id= ?;
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
        }

        $this->Image('../../../../img/' . LOGO_ESCUDO, $ix + 164, $iy - 1, '85', '18', 'JPG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(195, 5, utf8Decode('GOBIERNO DEL ESTADO DE OAXACA'), 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, utf8Decode('CONTROL PRESUPUESTAL ' . APP_EJERCICIO), 0, 1, 'L');

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
                WHERE ecf_id= ?'
            );

            $datosCveFin->consultar([$datos['clavefin']]);
            $datosCveFin->desconectar();
            $cvefin = $datosCveFin->extraer();

            $this->SetXY($ix + 7, $iy + 27);
            $this->Cell(5, 5, $cvefin['cfi_clave'] . ' - ' . utf8Decode($cvefin['cfi_nombre']), 0, 1, 'L');
        }

        $this->SetXY($ix - 65, $ytmp);
        $this->Cell(34, 5, utf8Decode($datos['tmd_nombre']), 0, 1, 'C');

        $this->CheckPageBreak(25);
        $this->SetFont('UniviaPro-Regular', '', 7);
        $this->SetWidths([60, 70, 40, 20]);
        $this->SetAligns(['L', 'L', 'L', 'L']);

        $xtm = $this->getX();
        $ytm = $this->getY();
        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);

        $linea = ((($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F')) ? 285 : 272);
        $this->Line($xtm, $ytm, $linea, $ytm);
        $this->Ln(.7);

        if ($datos != null && $datos['emo_id']) {
            $agregaOficio = ((($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F')) ? 'No.OF.AUT' : '');
        } else {
            $agregaOficio = '';
        }

        if ($agregaOficio != '') {
            $this->SetFont('UniviaPro-Bold', 'B', 5.9);
            $this->SetWidths([$this->ancho - 152, 17, 8, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
            $this->SetAligns(['L', 'L', 'L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'R']);
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->SetWidths([$this->ancho - 145, 10, 23, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
            $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'R']);
        }

        $this->SetAltoLinea(2.5);
        $this->SetRelleno('si');

        if ($datos != null && $datos['mov_id'] != 'C') {
            if ($agregaOficio != '') {
                $this->Row([
                    'CLAVE PRESUPUESTAL',
                    'OF.AUT.',
                    'TIPO',
                    'MODF. IMPORTE RESULTADO',
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
                    'MODF. IMPORTE RESULTADO',
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
            } //fin agregaOficio
        } else {
            if ($agregaOficio != '') {
                $this->Row([
                    'CLAVE PRESUPUESTAL',
                    'OF.AUT.',
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
            } //fin agregaOficio
        } //fin mov_id

        $this->ln(1);
        $this->SetAltoLinea(2);
        $this->SetRelleno('no');
    } //FIN Header

    //Encabezado del documento
    public function Footer($lastPage = true)
    {
        switch ($this->getOrientation()) {
            case 'P':
                $x = 15;
                $distancia = 5;
                break;
            case 'L':
                $x = 30;
                $distancia = 25;
                break;
        } //fin switch

        $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
        $gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
        $ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
        $mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';

        $datos = $this->getDatos();
        if (!$datos) {
            $sqlFolio = '
                SELECT omo_id, tfm_id, mod_id,ecf_id clavefin, mod_tipoMovimiento, mod_tipoMovimiento as mov_id, mod_clave, mod_concepto, mod_Subsecretaria, tmd_nombre, emo_id
                FROM scp_MaestroModificacion b
                INNER JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
                INNER JOIN cat_TipoModificacion f using(tmd_id)
                WHERE omo_id = ?
                AND mod_gpo_id= ?
                AND mod_ur_id= ?
                AND mod_id= ?;
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
        }
        if ($this->gpo . $this->ur_id == 118 && $this->perfil == 'U') {
            $this->SetY(-45);
        } else {
            $this->SetY(-32);
        }
        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Número de página
        if ($lastPage) {
            $emo = '';
            switch ($this->nvj) {
                case 1: //UR
                case 2: //UE
                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mod_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mod_ur_id']);
                    $objUnidadResponsable->select();

                    if ($datos != null) {
                        switch ($datos['emo_id']) {
                            case 1:
                            case 3:
                            case 6:
                            case 7:
                            case 8:
                            case 9:
                            case 17:
                                //Verifica la UR responsable en sesion
                                switch ($this->gpo . $this->ur_id) {
                                    case 118:
                                        //Verifica el perfil del usuario
                                        switch ($this->perfil) {
                                            case 'S': //Supervisor
                                                $datosUsuario = new Usuario();
                                                $datosUsuario->setUsr_id($this->usr_id->getUsr_id());
                                                $datosUsuario->select();

                                                //Instancia la Unidad Responsable
                                                $datosUrRH = new UnidadResponsable();
                                                //Parametros para obtener la información del Administrativo y titular
                                                $datosUrRH->setGpo_id(1);
                                                $datosUrRH->setUr_id(18);
                                                $datosUrRH->select();

                                                $elabora = utf8Decode($datosUsuario->getNombre() . ' '
                                                    . $datosUsuario->getPaterno() . ' '
                                                    . $datosUsuario->getMaterno());
                                                $cargoUsuario = '';
                                                $revisorAdmvo = $datosUrRH->getNombreAdministrativo();
                                                $cargoAdmvo = $datosUrRH->getCargoAdministrativo();
                                                $titular = $datosUrRH->getNombreTitular();
                                                $cargoTitular = $datosUrRH->getCargoTitular();

                                                //Registra o actualiza las firmas
                                                $sql = "INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id,
                                                    mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2,
                                                    mod_cargo3,
                                                    mod_firma3,
                                                    mod_cargo4,
                                                    mod_firma4,
                                                    mod_cargo5,
                                                    mod_firma5,
                                                    mod_cargo6,
                                                    mod_firma6,
                                                    mod_cargo7,
                                                    mod_firma7,
                                                    usr_id)
                                                    VALUES (:omo_id, :gpo_id, :ur_id, :mod_id, :cargoUs, :elabora, :cargoAdm, :revisorAdm, :cargoTit, :titular,
                                                        '','','','','','','','', :usr_id)
                                                    ON DUPLICATE KEY UPDATE
                                                    mod_cargo1='" . $cargoUsuario . "',
                                                    mod_firma1='" . $elabora . "',
                                                    mod_cargo2='" . $cargoAdmvo . "',
                                                    mod_firma2='" . $revisorAdmvo . "',
                                                    mod_cargo3='" . $cargoTitular . "',
                                                    mod_firma3='" . $titular . "';";

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
                                                    ':titular'    => $titular,
                                                    ':usr_id'     => $this->usuario_id,
                                                ];

                                                $firmasReportes = new AccesoDatos();
                                                $firmasReportes->conectar();
                                                $firmasReportes->setSql($sql);
                                                $firmasReportes->consultar($parametros);

                                                //Obtiene las firmas
                                                $sql = '
                                                    SELECT mod_cargo1,mod_firma1,mod_cargo2,mod_firma2,mod_cargo3,mod_firma3
                                                    FROM scp_ModificacionFirma
                                                    WHERE omo_id= ? and mod_gpo_id= ?
                                                    and mod_ur_id= ?
                                                    and mod_id= ?
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
                                                $this->Cell(100.5, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
                                                $this->SetX(20);
                                                $this->Cell(230, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
                                                $this->SetX(170);
                                                $this->Cell(92.5, 2, 'Vo.Bo.', 0, 1, 'C');
                                                $this->Ln(1);
                                                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                $y = $this->GetY();
                                                $this->Cell(100.5, 4, utf8Decode($datosFirma['mod_firma1']), 0, 0, 'C');
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
                                                $revisorAdmvo = $objUnidadResponsable->getNombreAdministrativo();
                                                $cargoAdmvo = $objUnidadResponsable->getCargoAdministrativo();
                                                $titular = $objUnidadResponsable->getNombreTitular();
                                                $cargoTitular = $objUnidadResponsable->getCargoTitular();
                                                //Registra o actualiza las firmas
                                                $sql = "INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id,
                                                    mod_id, mod_cargo2, mod_firma2, mod_cargo3, mod_firma3,
                                                    mod_cargo4,
                                                    mod_firma4,
                                                    mod_cargo5,
                                                    mod_firma5,
                                                    mod_cargo6,
                                                    mod_firma6,
                                                    mod_cargo7,
                                                    mod_firma7,
                                                    usr_id)
                                                    VALUES (:omo_id, :gpo_id, :ur_id, :mod_id, :cargoAdm, :revisorAdm, :cargoTit, :titular,
                                                    '','','','','','','','', :usr_id)
                                                    ON DUPLICATE KEY UPDATE
                                                    mod_cargo2='" . $cargoAdmvo . "',
                                                    mod_firma2='" . $revisorAdmvo . "',
                                                    mod_cargo3='" . $cargoTitular . "',
                                                    mod_firma3='" . $titular . "';";

                                                $parametros = [
                                                    ':omo_id'     => $datos['omo_id'],
                                                    ':gpo_id'     => $objUnidadResponsable->getGpo_id(),
                                                    ':ur_id'      => $objUnidadResponsable->getUr_id(),
                                                    ':mod_id'     => $datos['mod_id'],
                                                    ':cargoAdm'   => $cargoAdmvo,
                                                    ':revisorAdm' => $revisorAdmvo,
                                                    ':cargoTit'   => $cargoTitular,
                                                    ':titular'    => $titular,
                                                    ':usr_id'     => $this->usuario_id,
                                                ];

                                                $firmasReportes = new AccesoDatos();
                                                $firmasReportes->conectar();
                                                $firmasReportes->setSql($sql);
                                                $firmasReportes->consultar($parametros);

                                                //Obtiene las firmas
                                                $sql = '
                                                    SELECT mod_cargo2,mod_firma2,mod_cargo3,mod_firma3
                                                    FROM scp_ModificacionFirma
                                                    WHERE omo_id= ? and mod_gpo_id= ?
                                                    and mod_ur_id= ?
                                                    and mod_id= ?;
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
                                                $this->Cell(100.5, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
                                                $this->SetX(165);
                                                $this->Cell(92.5, 2, 'Vo.Bo.', 0, 1, 'C');
                                                $this->Ln(1);
                                                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                $y = $this->GetY();
                                                $this->Cell(100.5, 4, utf8Decode($datosFirma['mod_firma2']), 0, 0, 'C');
                                                $this->SetX(165);
                                                $this->Cell(92.5, 4, utf8Decode($datosFirma['mod_firma3']), 0, 1, 'C');
                                                $this->Ln(2);
                                                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo2']), 0, 0, 'C');
                                                $this->SetX(165);
                                                $this->Cell(92, 2, utf8Decode($datosFirma['mod_cargo3']), 0, 1, 'C');
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

                                            $jefeAdmvo = $objUnidadResponsable->getNombreAdministrativo();
                                            $cargoAdmvo = $objUnidadResponsable->getCargoAdministrativo();
                                            $titular = $objUnidadResponsable->getNombreTitular();
                                            $cargoTitular = $objUnidadResponsable->getCargoTitular();
                                            $titularRh = $datosUrRH->getNombreTitular();
                                            $cargoTitularRh = $datosUrRH->getCargoTitular();

                                            //Registra o actualiza las firmas
                                            $sql = "INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id,
                                                mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2, mod_cargo3,
                                                mod_firma3,
                                                mod_cargo4,
                                                mod_firma4,
                                                mod_cargo5,
                                                mod_firma5,
                                                mod_cargo6,
                                                mod_firma6,
                                                mod_cargo7,
                                                mod_firma7,
                                                usr_id)
                                                VALUES (:omo_id, :gpo_id, :ur_id, :mod_id, :cargoAdm, :jefeAdm, :cargoTit, :titular, :cargoRh, :titularRh,
                                                    '','','','','','','','', :usr_id)
                                                ON DUPLICATE KEY UPDATE
                                                mod_cargo1='" . $cargoAdmvo . "',
                                                mod_firma1='" . $jefeAdmvo . "',
                                                mod_cargo2='" . $cargoTitular . "',
                                                mod_firma2='" . $titular . "',
                                                mod_cargo3='" . $cargoTitularRh . "',
                                                mod_firma3='" . $titularRh . "';";

                                            $parametros = [
                                                ':omo_id'    => $datos['omo_id'],
                                                ':gpo_id'    => $objUnidadResponsable->getGpo_id(),
                                                ':ur_id'     => $objUnidadResponsable->getUr_id(),
                                                ':mod_id'    => $datos['mod_id'],
                                                ':cargoAdm'  => $cargoAdmvo,
                                                ':jefeAdm'   => $jefeAdmvo,
                                                ':cargoTit'  => $cargoTitular,
                                                ':titular'   => $titular,
                                                ':cargoRh'   => $cargoTitularRh,
                                                ':titularRh' => $titularRh,
                                                ':usr_id'    => $this->usuario_id,
                                            ];

                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($parametros);

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mod_cargo1,mod_firma1,mod_cargo2,mod_firma2,mod_cargo3,mod_firma3
                                                FROM scp_ModificacionFirma
                                                WHERE omo_id= ? and mod_gpo_id= ?
                                                and mod_ur_id= ?
                                                and mod_id= ?;';

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
                                            $this->SetX(180);
                                            $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo3']), 0, 0, 'C');
                                        } else {
                                            $jefeAdmvo = $objUnidadResponsable->getNombreAdministrativo();
                                            $puestoAdmvo = $objUnidadResponsable->getCargoAdministrativo();
                                            $titular = $objUnidadResponsable->getNombreTitular();
                                            $puestoTitular = $objUnidadResponsable->getCargoTitular();
                                            //Registra o actualiza las firmas
                                            $sql = "INSERT INTO scp_ModificacionFirma (omo_id, mod_gpo_id, mod_ur_id,
                                                mod_id, mod_cargo1, mod_firma1, mod_cargo2, mod_firma2,
                                                mod_cargo3,
                                                mod_firma3,
                                                mod_cargo4,
                                                mod_firma4,
                                                mod_cargo5,
                                                mod_firma5,
                                                mod_cargo6,
                                                mod_firma6,
                                                mod_cargo7,
                                                mod_firma7,
                                                usr_id)
                                                VALUES (:omo_id, :gpo_id, :ur_id, :mod_id, :puestoAdm, :jefeAdm, :puestoTit, :titular,
                                                    '','','','','','','','','','', :usr_id)
                                                ON DUPLICATE KEY UPDATE
                                                mod_cargo1='" . $puestoAdmvo . "',
                                                mod_firma1='" . $jefeAdmvo . "',
                                                mod_cargo2='" . $puestoTitular . "',
                                                mod_firma2='" . $titular . "';";

                                            $parametros = [
                                                ':omo_id'    => $datos['omo_id'],
                                                ':gpo_id'    => $objUnidadResponsable->getGpo_id(),
                                                ':ur_id'     => $objUnidadResponsable->getUr_id(),
                                                ':mod_id'    => $datos['mod_id'],
                                                ':puestoAdm' => $puestoAdmvo,
                                                ':jefeAdm'   => $jefeAdmvo,
                                                ':puestoTit' => $puestoTitular,
                                                ':titular'   => $titular,
                                                ':usr_id'    => $this->usuario_id,
                                            ];

                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($parametros);

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mod_cargo1,mod_firma1,mod_cargo2,mod_firma2
                                                FROM scp_ModificacionFirma
                                                WHERE omo_id= ? and mod_gpo_id= ?
                                                and mod_ur_id= ?
                                                and mod_id= ? ;
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
                                            $this->Cell(100.5, 2, 'JEFE ADMINISTRATIVO', 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(92.5, 2, 'TITULAR DE LA DEPENDENCIA/ENTIDAD', 0, 1, 'C');
                                            $this->Ln(1);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $y = $this->GetY();
                                            $this->Cell(100.5, 4, utf8Decode($datosFirma['mod_firma1']), 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(92.5, 4, utf8Decode($datosFirma['mod_firma2']), 0, 1, 'C');
                                            $this->Ln(.3);
                                            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                            $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo1']), 0, 0, 'C');
                                            $this->SetX(165);
                                            $this->Cell(100.5, 2, utf8Decode($datosFirma['mod_cargo2']), 0, 1, 'C');
                                        }
                                        break;
                                }
                                break;
                        }
                    }
                    break;
                default: //SEFIN

                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mod_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mod_ur_id']);
                    $objUnidadResponsable->select();

                    $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
                    $gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
                    $ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
                    $mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';
                    $tipoMovimiento = (isset($_POST['tipoMovimiento']) && $_POST['tipoMovimiento'] != '') ?
                    $_POST['tipoMovimiento'] : '';

                    $nom_analista = '';
                    $cargo_analista = '';
                    $nom_departamento = '';
                    $cargo_departamento = '';
                    $nom_unidad = '';
                    $cargo_unidad = '';
                    $nom_director = '';
                    $cargo_director = '';

                    $omo_id = isset($datos['omo_id']) ? $datos['omo_id'] : '';
                    $mod_id = isset($datos['mod_id']) ? $datos['mod_id'] : 0;
                    if ($omo_id != '' || $mod_id != 0) {
                        //Obtiene las firmas
                        $sql = '
                            SELECT mod_cargo4,mod_firma4,mod_cargo5,mod_firma5,mod_cargo6,mod_firma6,
                            mod_cargo7,mod_firma7
                            FROM scp_ModificacionFirma
                            WHERE omo_id= ? and mod_gpo_id= ?
                            and mod_ur_id= ?
                            and mod_id= ?;';

                        $parametros = [
                            $datos['omo_id'],
                            $objUnidadResponsable->getGpo_id(),
                            $objUnidadResponsable->getUr_id(),
                            $datos['mod_id'],
                        ];

                        $firmasReportes = new AccesoDatos();
                        $firmasReportes->conectar();
                        $firmasReportes->setSql($sql);
                        $firmasReportes->consultar($parametros);
                        $datosFirma = $firmasReportes->extraer();

                        if ($firmasReportes->getNumeroRegistros() > 0) {
                            $nom_analista = $datosFirma['mod_firma4'];
                            $cargo_analista = $datosFirma['mod_cargo4'];
                            $nom_departamento = $datosFirma['mod_firma5'];
                            $cargo_departamento = $datosFirma['mod_cargo5'];
                            $nom_unidad = $datosFirma['mod_firma6'];
                            $cargo_unidad = $datosFirma['mod_cargo6'];
                            $nom_director = $datosFirma['mod_firma7'];
                            $cargo_director = $datosFirma['mod_cargo7'];
                        }
                    } //fin if

                    switch ($datos['emo_id']) {
                        case 6:
                        case 11:
                        case 17:
                        case 8:
                        case 13:
                            switch ($datos['tfm_id']) {
                                case 'A': //Servicios personales
                                case 'B': //Gastos de operacion
                                    switch ($objUnidadResponsable->getGpo_id() . $objUnidadResponsable->getUr_id()) {
                                        case 530:
                                        case 531:
                                            if ($datos['emo_id'] == 6 ||
                                                $datos['emo_id'] == 9 ||
                                                $datos['emo_id'] == 11 ||
                                                $datos['emo_id'] == 17 ||
                                                $datos['emo_id'] == 8 ||
                                                $datos['emo_id'] == 13) {

                                                if ($datos['emo_id'] == 8 || $datos['emo_id'] == 11 || $datos['emo_id'] == 17) {

                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->SetWidths([67, 67, 67, 67]);
                                                    $this->SetAligns(['C', 'C', 'C', 'C']);
                                                    $this->Row([
                                                        utf8Decode($cargo_analista),
                                                        utf8Decode($cargo_departamento),
                                                        utf8Decode($cargo_unidad),
                                                        utf8Decode($cargo_director),
                                                    ]);

                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->SetWidths([67, 67, 67, 67]);
                                                    $this->SetAligns(['C', 'C', 'C', 'C']);
                                                    $this->Row([
                                                        utf8Decode(preg_replace('/Â+/', ' ', $nom_analista)),
                                                        utf8Decode($nom_departamento),
                                                        utf8Decode($nom_unidad),
                                                        utf8Decode($nom_director),
                                                    ]);

                                                } else {
                                                    $this->SetXY(10, -20);
                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->Cell(100.5, 2, utf8Decode($cargo_analista), 0, 1, 'C', 0);

                                                    $this->SetXY(97, -20);
                                                    $this->Cell(92.5, 2, utf8Decode($cargo_departamento), 0, 1, 'C', 0);

                                                    $this->SetXY(178, -20);
                                                    $this->Cell(84.5, 2, utf8Decode($cargo_unidad), 0, 1, 'C', 0);

                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->SetXY(30, -13);
                                                    $this->MultiCell(63.5, 4, utf8Decode($nom_analista), 0, 'C');

                                                    $this->SetXY(93, -13);
                                                    $this->MultiCell(100.5, 4, utf8Decode($nom_departamento), 0, 'C');

                                                    $this->SetXY(170, -13);
                                                    $this->MultiCell(100.5, 4, utf8Decode($nom_unidad), 0, 'C');
                                                }
                                            }
                                            break;
                                        case 105:
                                        case 540:
                                        case 553:

                                            if ($datos['emo_id'] == 6 ||
                                                $datos['emo_id'] == 9 ||
                                                $datos['emo_id'] == 11 ||
                                                $datos['emo_id'] == 17 ||
                                                $datos['emo_id'] == 8 ||
                                                $datos['emo_id'] == 13) {

                                                if ($datos['emo_id'] == 8 || $datos['emo_id'] == 11 || $datos['emo_id'] == 17) {
                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->SetWidths([67, 67, 67, 67]);
                                                    $this->SetAligns(['C', 'C', 'C', 'C']);
                                                    $this->Row([
                                                        utf8Decode($cargo_analista),
                                                        utf8Decode($cargo_departamento),
                                                        utf8Decode($cargo_unidad),
                                                        utf8Decode($cargo_director),
                                                    ]);

                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->SetWidths([67, 67, 67, 67]);
                                                    $this->SetAligns(['C', 'C', 'C', 'C']);
                                                    $this->Row([
                                                        utf8Decode($nom_analista),
                                                        utf8Decode($nom_departamento),
                                                        utf8Decode($nom_unidad),
                                                        utf8Decode($nom_director),
                                                    ]);
                                                } else {

                                                    $this->SetXY(10, -20);
                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->Cell(100.5, 2, utf8Decode($cargo_analista), 0, 1, 'C', 0);

                                                    $this->SetXY(97, -20);
                                                    $this->Cell(92.5, 2, utf8Decode($cargo_departamento), 0, 1, 'C', 0);

                                                    $this->SetXY(178, -20);
                                                    $this->Cell(84.5, 2, utf8Decode($cargo_unidad), 0, 1, 'C', 0);

                                                    $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                    $this->SetXY(30, -13);
                                                    $this->MultiCell(63.5, 4, utf8Decode($nom_analista), 0, 'C');

                                                    $this->SetXY(93, -13);
                                                    $this->MultiCell(100.5, 4, utf8Decode($nom_departamento), 0, 'C');

                                                    $this->SetXY(170, -13);
                                                    $this->MultiCell(100.5, 4, utf8Decode($nom_unidad), 0, 'C');
                                                }

                                            }
                                            break;
                                        default:
                                            switch ($datos['emo_id']) { //Verifica el estado de la modificacion
                                                case 6:
                                                case 8:
                                                case 11:
                                                case 17:
                                                    if ($datos['emo_id'] == 8 || $datos['emo_id'] == 11 || $datos['emo_id'] == 17) {

                                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                        $this->SetWidths([67, 67, 67, 67]);
                                                        $this->SetAligns(['C', 'C', 'C', 'C']);
                                                        $this->Row([
                                                            utf8Decode($cargo_analista),
                                                            utf8Decode($cargo_departamento),
                                                            utf8Decode($cargo_unidad),
                                                            utf8Decode($cargo_director),
                                                        ]);

                                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                        $this->SetWidths([67, 67, 67, 67]);
                                                        $this->SetAligns(['C', 'C', 'C', 'C']);
                                                        $this->Row([
                                                            utf8Decode($nom_analista),
                                                            utf8Decode($nom_departamento),
                                                            utf8Decode($nom_unidad),
                                                            utf8Decode($nom_director),
                                                        ]);

                                                    } else {
                                                        $this->SetXY(10, -20);
                                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                        $this->Cell(100.5, 2, utf8Decode($cargo_analista), 0, 1, 'C', 0);

                                                        $this->SetXY(97, -20);
                                                        $this->Cell(92.5, 2, utf8Decode($cargo_departamento), 0, 1, 'C', 0);

                                                        $this->SetXY(178, -20);
                                                        $this->Cell(84.5, 2, utf8Decode($cargo_unidad), 0, 1, 'C', 0);

                                                        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                        $this->SetXY(30, -13);
                                                        $this->MultiCell(63.5, 4, utf8Decode($nom_analista), 0, 'C');

                                                        $this->SetXY(93, -13);
                                                        $this->MultiCell(100.5, 4, utf8Decode($nom_departamento), 0, 'C');

                                                        $this->SetXY(170, -13);
                                                        $this->MultiCell(100.5, 4, utf8Decode($nom_unidad), 0, 'C');
                                                    }
                                                    break;
                                            }
                                    }

                                    break;
                                case 'C': //Obra publica solamente planeacion.
                                    switch ($datos['emo_id']) {
                                        case 6:
                                        case 7:
                                        case 8:
                                        case 11:
                                        case 17:
                                            if ($datos['emo_id'] == 8 || ($datos['emo_id'] == 11 || $datos['emo_id'] == 17 &&
                                                $this->subSecretaria == 3)) {
                                                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                $this->SetWidths([67, 67, 67, 67]);
                                                $this->SetAligns(['C', 'C', 'C', 'C']);
                                                $this->Row([
                                                    utf8Decode($cargo_analista),
                                                    utf8Decode($cargo_departamento),
                                                    utf8Decode($cargo_unidad),
                                                    utf8Decode($cargo_director),
                                                ]);

                                                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                $this->SetWidths([67, 67, 67, 67]);
                                                $this->SetAligns(['C', 'C', 'C', 'C']);
                                                $this->Row([
                                                    utf8Decode($nom_analista),
                                                    utf8Decode($nom_departamento),
                                                    utf8Decode($nom_unidad),
                                                    utf8Decode($nom_director),
                                                ]);
                                            } else {
                                                $this->SetXY(10, -20);
                                                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                $this->Cell(100.5, 2, utf8Decode($cargo_analista), 0, 1, 'C', 0);

                                                $this->SetXY(97, -20);
                                                $this->Cell(92.5, 2, utf8Decode($cargo_departamento), 0, 1, 'C', 0);

                                                $this->SetXY(178, -20);
                                                $this->Cell(84.5, 2, $cargo_unidad, 0, 1, 'C', 0);

                                                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                                                $this->SetXY(30, -13);
                                                $this->MultiCell(63.5, 4, utf8Decode($nom_analista), 0, 'C');

                                                $this->SetXY(93, -13);
                                                $this->MultiCell(100.5, 4, utf8Decode($nom_departamento), 0, 'C');

                                                $this->SetXY(170, -13);
                                                $this->MultiCell(100.5, 4, utf8Decode($nom_unidad), 0, 'C');
                                            }
                                            break;
                                    }
                            }
                            break;
                        default:

                            break;
                    }
                    break;
            }
        } else {
            $this->SetY(-20);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(10, 2, utf8Decode('CONTINUA...'), 0, 1, 'L');

            switch ($this->nvj) {
                case 1:case 2: //Ur y Ue
                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mod_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mod_ur_id']);
                    $objUnidadResponsable->select();
                    //ORIGEN A
                    $this->Ln(10);
                    if ((
                        $objUnidadResponsable->getRh() == 1 ||
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
                        )
                    ) && $datos['omo_id'] == 'A' && $datos['tfm_id'] == 'A') {
                        $rubricas = 'Rúbricas:________________________, ________________________,________________________';
                        $this->Cell(252, 2, utf8Decode($rubricas), 0, 1, 'R');
                    } else {
                        $rubricas = 'Rúbricas:________________________, ________________________';
                        $this->Cell(252, 2, utf8Decode($rubricas), 0, 1, 'R');
                    }

                    break;
                default: //SEFIN
                    if ($datos['omo_id'] == 'A' && ($datos['emo_id'] == 6 || $datos['emo_id'] == 11)) {
                        $rubricas = 'Rúbricas:________________________, ________________________, ________________________';
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
            utf8Decode('Página ' . $this->PageNo() . '/{nb}'),
            0,
            'C'
        );

        $this->SetY($y);
        $this->setX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 104 + 2 * $distancia);
        $this->MultiCell(63, 2, date('d/m/y') . ' - ' . date('H:i:s'), 0, 'R');

    } // fin de  footer

} // fin de la clase reportetabla

$omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
$gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
$ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
$mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';
$autorizar = (isset($_POST['bandera']) && $_POST['bandera'] != '') ? $_POST['bandera'] : '';

$tipo = '';
switch ($_SESSION[_S]['nvj_id']) {
    case 1:case 2: //UR y Ue
        $tipo = "and tcp_id!='P'";
        break;
    default:
        $tipo = '';
        break;
} //fin switch

$sql = "
    SELECT modificaciones.*, d.pe_nombre, pv.pre_vigente,pv.vig_ene, pv.vig_feb, pv.vig_mar,
        pv.vig_abr, pv.vig_may,pv.vig_jun, pv.vig_jul, pv.vig_ago, pv.vig_sep, pv.vig_oct,
        pv.vig_nov, pv.vig_dic,e.pre_claveCompleta,f.tmd_nombre, o.tmd_id,o.tfm_id,ore_oficioAut,toa_id,
        CASE WHEN mod_tipoMovimiento = 'A' THEN 'AMPLIACIÓN'
        WHEN mod_tipoMovimiento = 'R' THEN 'REDUCCIÓN'
        WHEN mod_tipoMovimiento = '' THEN ''
        END AS tipo_Movimiento
    FROM
    (
        SELECT b.omo_id, b.mod_id, b.mod_concepto,b.emo_id,a.mov_id, b.mod_clave, a.dmo_importe,b.enc_oop, b.mod_tipoMovimiento,
            a.dmo_ene, a.dmo_feb, a.dmo_mar, a.dmo_abr, a.dmo_may, a.dmo_jun, a.dmo_jul,
            a.dmo_ago, a.dmo_sep, a.dmo_oct, a.dmo_nov, a.dmo_dic, a.gpo_id, a.ur_id, a.ue_id,
            a.epg_id, a.pg_id, a.pe_id, a.ecf_id,a.tcp_id,b.ecf_id as clavefin,
            a.vig_ene pa_ene, a.vig_feb pa_feb, a.vig_mar pa_mar, a.vig_abr pa_abr, a.vig_may pa_may,
            a.vig_jun pa_jun, a.vig_jul pa_jul, a.vig_ago pa_ago, a.vig_sep pa_sep, a.vig_oct pa_oct,
            a.vig_nov pa_nov, a.vig_dic pa_dic, a.pre_vigente pa_importe,b.mod_ue_id,mod_Subsecretaria
        FROM scp_MaestroModificacion b
        LEFT JOIN scp_DetalleModificacion a using (omo_id,mod_gpo_id,mod_ur_id,mod_id)
        WHERE omo_id = ? and mod_gpo_id=? and mod_ur_id= ? and mod_id= ? $tipo
    ) AS modificaciones
    INNER JOIN cat_PartidaEspecifica d using(pe_id,pg_id)
    INNER JOIN presupuesto e using (gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
    JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
    INNER JOIN poa_OrigenRecurso using(gpo_id,ur_id,epg_id,ecf_id)
    INNER JOIN cat_ObraActividad using(gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
    INNER JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
    INNER JOIN cat_TipoModificacion f using(tmd_id)
    ;
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

do {
    if ($datos != null) {
        if ($datos['mov_id'] == 'A') {
            $tipomov = '(Amp.)';
        } elseif ($datos['mov_id'] == 'R') {
            $tipomov = '(Red.)';
        } elseif ($datos['mov_id'] == 'C') {
            $tipomov = '(Cal.)';
        } else {
            $tipomov = '(XXX)';
        }
    } //fin switch

    $pdf->SetFont('univiapro', '', 5);
    if ($datos != null && (strlen($datos['dmo_importe']) > 11 || strlen($datos['dmo_ene']) > 11 || strlen($datos['dmo_feb']) > 11 || strlen($datos['dmo_mar']) > 11 || strlen($datos['dmo_abr']) > 11 || strlen($datos['dmo_may']) > 11 || strlen($datos['dmo_jun']) > 11 || strlen($datos['dmo_jul']) > 11 || strlen($datos['dmo_ago']) > 11 || strlen($datos['dmo_sep']) > 11 || strlen($datos['dmo_oct']) > 11 || strlen($datos['dmo_nov']) > 11 || strlen($datos['dmo_dic']) > 11 || strlen($datos['pa_importe']) > 11 || strlen($datos['pa_ene']) > 11 || strlen($datos['pa_feb']) > 11 || strlen($datos['pa_mar']) > 11 || strlen($datos['pa_abr']) > 11 || strlen($datos['pa_may']) > 11 || strlen($datos['pa_jun']) > 11 || strlen($datos['pa_jul']) > 11 || strlen($datos['pa_ago']) > 11 || strlen($datos['pa_sep']) > 11 || strlen($datos['pa_oct']) > 11 || strlen($datos['pa_nov']) > 11 || strlen($datos['pa_dic']) > 11)) {
        $pdf->SetFont('univiapro', '', 4);
    }

    $pdf->SetAltoLinea(1);
    if (($datos != null && ($datos['mod_Subsecretaria'] == 2 || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F')))) {
        $pdf->SetWidths([40, 17, 8, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
        $pdf->SetAligns(['L', 'L', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R']);
    } else {
        $pdf->SetWidths([45, 10, 23, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
        $pdf->SetAligns(['L', 'C', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R']);
    }
    if ($datos != null) {
        switch ($datos['emo_id']) {
            case 14: //Autorizado por egresos
            case 15: //Autorizado por planeacion
                if ($datos != null && ($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F')) {

                    $pdf->Row(
                        [$datos['pre_claveCompleta'] . ' - ' . $datos['tcp_id'], $datos['ore_oficioAut'],
                        $tipomov, number_format($datos['pa_importe'], 2),
                        number_format($datos['pa_ene'], 2), number_format($datos['pa_feb'], 2),
                        number_format($datos['pa_mar'], 2), number_format($datos['pa_abr'], 2),
                        number_format($datos['pa_may'], 2), number_format($datos['pa_jun'], 2),
                        number_format($datos['pa_jul'], 2), number_format($datos['pa_ago'], 2),
                        number_format($datos['pa_sep'], 2), number_format($datos['pa_oct'], 2),
                        number_format($datos['pa_nov'], 2), number_format($datos['pa_dic'], 2)]
                    );
                } else {
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
                }
                break;
            default: //En estado 1,3,6,7,8,9
                if ($datos != null && (($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F'))) {
                    $pdf->Row(
                        [$datos['pre_claveCompleta'] . ' - ' . $datos['tcp_id'], $datos['ore_oficioAut'],
                        $tipomov, number_format($datos['pre_vigente'], 2),
                        number_format($datos['vig_ene'], 2), number_format($datos['vig_feb'], 2),
                        number_format($datos['vig_mar'], 2), number_format($datos['vig_abr'], 2),
                        number_format($datos['vig_may'], 2), number_format($datos['vig_jun'], 2),
                        number_format($datos['vig_jul'], 2), number_format($datos['vig_ago'], 2),
                        number_format($datos['vig_sep'], 2), number_format($datos['vig_oct'], 2),
                        number_format($datos['vig_nov'], 2), number_format($datos['vig_dic'], 2)]
                    );
                } else {
                    if ($datos != null) {
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
                    }

                }
                break;

        }
    }

    $pdf->SetAltoLinea(1.8);
    if ($datos != null && (($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F'))) {
        $pdf->Row(
            [utf8Decode($datos['pe_nombre']),
            '', '', number_format($datos['dmo_importe'], 2),
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
            number_format($datos['dmo_dic'], 2)]
        );
    } else {
        if ($datos != null) {
            $pdf->Row(
                [utf8Decode($datos['pe_nombre']),
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
                number_format($datos['dmo_dic'], 2)]
            );
        }

    }
    $pdf->SetAltoLinea(1);
    if ($datos != null) {
        switch ($datos['emo_id']) {
            case 14: //Autorizado por egresos
            case 15: //Autorizado por planeacion
                if (($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F')) {
                    $pdf->Row(
                        ['', '', '',
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
                } else {
                    $pdf->Row(
                        ['', '',
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
                }
                break;
            default: //1,3,6,7,8,9
                if (($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F')) {
                    $pdf->Row(
                        ['', '', '',
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
                } else {
                    $pdf->Row(
                        ['', '',
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
                }
                break;

        }
    }

    $xtm = $pdf->getX();
    $ytm = $pdf->getY();
    $pdf->ln(1);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->setDrawColor(220, 220, 220);
    $pdf->SetLineWidth(.5);
    $pdf->Line($xtm, $ytm, 268, $ytm);

    $linea = $datos != null ? ((($datos['mod_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id'] == 'F')) ? 285 : 272) : 0;
    $pdf->Line($xtm, $ytm, $linea - 14, $ytm);
    if ($datos != null) {
        switch ($datos['mov_id']) {
            case 'A': //Ampliacion
            case 'C': //Calendario
                $total_a = $total_a + $datos['dmo_importe'];
                break;
            default: //si es reduccion;
                $total_r = $total_r + $datos['dmo_importe'];
                break;
        }
    }

    $pdf->Ln(1);
    if ($pdf->getY() > 160) {
        $pdf->addPage();
    }
    if ($fill) {
        $fill = false;
    }

} while ($datos = $conexion->extraer());
$datos = $pdf->getDatos();
$tipom = '';
if ($datos != null) {
    switch ($datos['tmd_id']) {
        case 'A': //Traspaso intrainstitucional(ampliación,reducción)
            $tipom = 'TOTAL AMPLIACIÓN INTRAINSTITUCIONAL:' . '    ' . number_format($total_a, 2) .
            '   ' . 'TOTAL REDUCCIÓN INTRAINSTITUCIONAL:' . '    ' . number_format($total_r, 2);
            break;
        case 'B': //Traspaso interinstitucional
            if ($datos['mod_tipoMovimiento'] == 'A') { //Traspaso Interinstitucional "ampliacion"
                switch ($datos['emo_id']) {
                    case 6:
                    case 8:
                    case 11:
                    case 14:
                    case 15:
                        if ($_SESSION[_S]['nvj_id'] >= 3) {
                            $tipom = 'TOTAL AMPLIACIÓN INTERINSTITUCIONAL:' . '    ' .
                            number_format($total_a, 2) . '   ' . 'TOTAL REDUCCIÓN INTERINSTITUCIONAL:' .
                            '    ' . number_format($total_r, 2);
                        } else {
                            $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_a, 2);
                        }
                        break;
                    default:
                        $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_a, 2);
                        break;
                }
            }
            if ($datos['mod_tipoMovimiento'] == 'R') { //Traspaso Interinstitucional "reducción"
                switch ($datos['emo_id']) {
                    case 6:
                    case 8:
                    case 11:
                    case 14:
                    case 15:
                    case 17:
                        if ($_SESSION[_S]['nvj_id'] >= 3) {
                            $tipom = 'TOTAL AMPLIACIÓN INTERINSTITUCIONAL:' . '    ' . number_format($total_a, 2) .
                            '   ' . 'TOTAL REDUCCIÓN INTERINSTITUCIONAL:' . '    ' . number_format($total_r, 2);
                        } else {
                            $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_r, 2);
                        }
                        break;
                    default:
                        $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_r, 2);
                        break;
                }
            }
            break;
        case 'C': //Recalendarización
            $tipom = 'TOTAL CALENDARIZADO:' . '   ' . number_format($total_a, 2);
            break;
        case 'D': //Ampliación
            $tipom = 'TOTAL AMPLIACIONES:' . '   ' . number_format($total_a, 2);
            break;
        case 'E': //Reducción
            $tipom = 'TOTAL REDUCCIONES:' . '   ' . number_format($total_r, 2);
            break;
        default:
            $tipom = '';
            break;
    }
}

$pdf->SetAltoLinea(2.5);
$pdf->Ln(5);
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->SetWidths([300]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
$pdf->Row([utf8Decode($tipom)]);

$datos = $pdf->getDatos();
//datos del folio en caso de no tener movimientos
if (!$datos) {
    $sqlFolio = '
            SELECT mod_id,ecf_id,mod_tipoMovimiento,mod_clave,mod_concepto
            FROM scp_MaestroModificacion b
            WHERE omo_id = ?
            AND mod_gpo_id= ?
            AND mod_ur_id= ?
            AND mod_id= ?;
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
}

$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->SetWidths([200]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
$pdf->Row([utf8Decode('JUSTIFICACIÓN:') . '  ' . utf8Decode($datos['mod_concepto'])]);

if ($autorizar) {

    $archivo = $datos['mod_clave'] . '.pdf';
    $UR = $gpo_id . $ur_id;
    $name = dirname(__FILE__) . '/../../../../archivos/' . $archivo;

    $pdf->Output($name, 'F'); //guarda el pdf

    $upload = new FicheroSefip();
    $file = ['name' => $name, 'tmp_name' => $name, 'eslocal' => true, 'error' => false];
    $upload->setTipos(['pdf']);
    $upload->setFichero($file);
    $upload->setModulo('scp');
    $upload->setfile($archivo);
    $upload->setUrFichero($UR);
    $upload->setAnioFichero(APP_EJERCICIO);

    $upload->setRuta('adecuaciones/' . date('m') . '/autorizacionFolio/externas');

    if ($upload->upload()) {
        if (file_exists($name)) {
            unlink($name);
        }
        $pdf->Output();
    } else {
        error_reporting(E_ALL);
        echo 'Ocurrio un Error al Generar el PDF';
    }
} else {
    $pdf->Output();
}
