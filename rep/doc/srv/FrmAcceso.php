<?php

//inicio de sesion y llamada a las librerias
session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once '../../lib/reportetabla.1.8.php';
require_once '../../../lib/AccesoDatos.php';
require_once '../../../lib/helpers.php';
require_once '../../../ent/usuarios/UsuarioPrevio.php';
require_once '../../../ent/usuarios/SolicitudAcceso.php';
require_once '../../../ent/usuarios/PermisosUsuarioPrevio.php';

class reportetabla extends pdf
{
    public $ctrlEjercicioActual;
    public $zonaDePermisos;
    public $numeroDeSolicitud;
    public $limiteInferior = 257;

    public function __construct()
    {
        parent::__construct();
    }

    public function Footer()
    {
        $politicas = utf8Decode(
             "* La contraseña es de uso personal e intransferible y es de total responsabilidad de la persona a la que se le asignó.\n"
            ."* Si después de 3 meses la cuenta no es utilizada, será dada de baja automáticamente.\n"
            ."* La contraseña asignada al momento de la creación del usuario tiene una vigencia de 5 días, después de esos días caducará y tendrá que solicitar el reseteo de la misma."
        );

        $this->SetFont('Montserrat-b', '', 9);
        $this->setXY(10, -40);

        $this->SetFillColor(210, 210, 210);
        $this->Cell(190, 5, utf8Decode ('Políticas de Seguridad Informática:'), 0, 0, 'L', true);
        $this->RoundedRect(10, $this->GetY(), 190, 18, 2, '1234');

        $this->setXY(15.5, $this->GetY() + 6);
        $this->SetFont('Montserrat', '', 6);
        $this->MultiCell(180, 2.5, $politicas, 0, 'L');
        $this->SetFillColor(255, 255, 255);

        $this->setXY(10, $this->GetY() + 2);
        $this->SetFillColor(210, 210, 210);
        $this->SetFont('Montserrat-b', '', 6.6);
        $this->Cell(190, 5, utf8Decode('Lo anterior conforme a lo dispuesto en el art.45 fracción XXXIX de la Ley Orgánica del Poder Ejecutivo del Estado de Oaxaca, y artículos 19 fracción II a),'), 0, 0, 'C', false);

        $this->setXY(10, $this->GetY() + 2.15);
        $this->SetFillColor(210, 210, 210);
        $this->SetFont('Montserrat-b', '', 6.6);
        $this->Cell(190, 5, utf8Decode('56 y 95 del Reglamento de la Ley Estatal de Presupuesto y Responsabilidad Hacendaria.'), 0, 0, 'C', false);

        $this->SetY(-18);
        $this->Ln(2);
        $this->SetFillColor(238, 241, 251);
        $this->SetFont('Montserrat', '', 6);
        $this->Cell(190, 5, utf8Decode('Centro Administrativo del Poder Ejecutivo y Judicial "General Porfirio Díaz, Soldado de la Patria" Edificio "D" Saúl Martínez,'), 0, 0, 'C', false);
        $this->Ln(2.5);
        $this->SetFillColor(238, 241, 251);
        $this->SetFont('Montserrat', '', 6);
        $this->Cell(190, 5, utf8Decode('Av. Gerardo Pandal Graft #1, Reyes Mantecón, San Bartolo Coyotepec, Oaxaca. C.P. 71257.'), 0, 0, 'C', false);
        $this->Ln(3);
        $this->SetFillColor(238, 241, 251);
        $this->SetFont('Montserrat', '', 6);
        $this->Cell(190, 5, utf8Decode('Teléfono: 951 50 1 69 00'), 0, 0, 'C', false);
        $this->SetY(-10);
        $this->SetFont('Montserrat-b', '', 6);
        $this->SetX(10);
        $this->Cell(10, 10, date('d/m/Y H:i'), 0, 0, 'L');
        $this->Cell(0, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    public function dameUr($gpo_id, $ur_id)
    {
        if (strlen($ur_id) == 1) {
            $ur_id = '0' . $ur_id;
        }
        return $gpo_id . $ur_id;
    }

    public function Header()
    { //Encabezado del documento
        $this->Image('../../../img/'. LOGO_SOLICITUD_MEMBRETE, 0 , -10 , 220, 307, 'png');

        if ($this->zonaDePermisos) {
            $this->ln(15);
            $this->SetFont('Montserrat-b', '', 12);
            $this->setXY(10, 35);
            $this->MultiCell(100, 5, utf8Decode('PERMISOS'), 0, 'L', false);
            $this->SetFont('Montserrat', '', 10);
            $this->setY(35);
            $this->setX(150);
            $this->SetFillColor(210, 210, 210);
            $this->Cell(32, 5, utf8Decode('No. de Solicitud:'), 0, 0, 'L', true);
            $this->SetFont('Arial', 'B', 10);
            $this->setY(35);
            $this->setX(182);
            $this->SetFillColor(238, 241, 251);
            $this->SetFont('Arial', '', 8);
            $this->Cell(18, 5, utf8Decode($this->numeroDeSolicitud), 0, 0, 'R', true);
            $this->RoundedRect(150, $this->GetY() - .1, 50, 5, 2);
            $this->ln(7);

            $this->setRelleno('si');
            $this->setFillColor(220, 220, 220);
            $this->SetFont('Montserrat-b', '', 8);
            $this->SetTextColor(90, 91, 95);
            $this->setX(10);
            $this->SetDrawColor(180, 180, 180);
            $this->SetBorders(1);
            $this->SetWidths([108, 82]);
            $this->SetAligns(['L', 'L']);
            $this->Row([
                utf8Decode('SISTEMA - MENU - OPCIÓN'),
                utf8Decode('PERMISOS'),
            ]);
            $this->setRelleno('no');
            $this->borders = 'TB';
            $this->setLeftMargin(10);
        }
    }
}

const SQL_CAT_USUARIO_POR_USR = '.cat_Usuario WHERE usr_id=';

function consultarNvjIdPorUsr($usrId, bool $soloActivo = false)
{
    $acceso = new AccesoDatos();
    $acceso->conectar();
    $sql = 'SELECT nvj_id FROM ' . BD_USUARIO . SQL_CAT_USUARIO_POR_USR . $usrId;
    if ($soloActivo) {
        $sql .= ' AND sts_id=1';
    }
    $acceso->setSql($sql . ';');
    $acceso->consultar();
    $row = $acceso->extraer();
    $acceso->desconectar();

    return $row;
}

/**DATOS PERSONALES */
$cargo = 'Cargo:';

$solicitudAceso = new SolicitudAcceso();
$solicitudAceso->setSac_id(isset($_POST['TickSc']) ? $_POST['TickSc'] : '');

$usuarioPrevio = new UsuarioPrevio();
$usuarioPrevio->setUsp_id(isset($_POST['Tick']) ? $_POST['Tick'] : '');

$usuarioPrevio->select();
$solicitudAceso->select();

$pdf = new reportetabla('P', 'mm', 'A4');
$pdf->SetProtection(['print'], '', PDF_PASSWORD_OWNER_MGA);
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(false, 10);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();
$pdf->AddFont('Montserrat', '', 'Montserrat-Regular.php');
$pdf->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');

$pdf->ln();
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);
$pdf->SetFillColor(199, 209, 219);
$pdf->SetWidths([280]);
$pdf->SetAligns(['C']);

$pdf->ln();
$pdf->setY(35);
$pdf->setX(10);
$pdf->SetFont('Montserrat-b', '', 15);
$pdf->SetTextColor(90, 91, 95);
$pdf->MultiCell(110, 5, utf8Decode('SOLICITUD DE ACCESO A SISTEMAS'), 0, 'L', false);
$pdf->SetFont('Montserrat', '', 10);
$pdf->setY(35);
$pdf->setX(150);
$pdf->SetFillColor(210, 210, 210);

$pdf->SetY(39);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->SetTextColor(90, 91, 95);
$pdf->MultiCell(120, 5, utf8Decode('Datos del Titular de la Dependencia (o Titular Administrativo)'), 0, 'L', false);
$pdf->SetFont('Montserrat', '', 10);
$pdf->setY(35);
$pdf->setX(150);

$pdf->Cell(28, 5, utf8Decode('No. de Ticket:'), 0, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(35);
$pdf->setX(178);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, utf8Decode($solicitudAceso->getSac_ticket()), 0, 0, 'R', true);
$pdf->RoundedRect(150, $pdf->GetY() - .1, 48, 5, 2);

$pdf->ln();
$pdf->SetFont('Arial', '', 10);
$pdf->setY(41);
$pdf->setX(150);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(28, 5, utf8Decode('No. de Solicitud:'), 0, 0, 'L', true);

$pdf->setY(41);
$pdf->setX(178);
$pdf->SetFont('Arial', '', 8);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(20, 5, utf8Decode($usuarioPrevio->getSac_id()), 0, 0, 'R', true);
$pdf->RoundedRect(150, $pdf->GetY() - .1, 48, 5, 2);

/*PARA EL NOMBRE RESPONSABLE*/
$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(50);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(16, 5, utf8Decode('Nombre:'), 0, 0, 'L', true);
$pdf->setY(50);
$pdf->setX(26);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);

$pdf->Cell(174, 5, utf8Decode(ucfirst(mb_strtoupper($solicitudAceso->getJefe(), 'UTF-8'))), 0, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
$pdf->setY(56);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);

/*FIN NOMBRE RESPONSABLE*/

$pdf->ctrlEjercicioActual = 'ctrl' . APP_EJERCICIO;

/*PARA EL PUESTO RESPONSABLE*/
$c1 = new AccesoDatos();

    $c1->setSql('select ur_id, concat(ur_nombreAdministrativo) nombre,ur_cargoAdministrativo from  ' . $pdf->ctrlEjercicioActual . '.aux_UnidadResponsable WHERE gpo_id = ' . $usuarioPrevio->getGpo_id() . '
                and ur_id = ' . $usuarioPrevio->getUr_id() . ' limit 1;');
    $c1->conectar();
    $c1->consultar();
    $d1 = $c1->extraer();
    $pdf->SetFont('Montserrat', '', 9);
    $pdf->Cell(13, 5, utf8Decode($cargo), 0, 0, 'L', true);
    $pdf->setY(56);
    $pdf->setX(22);
    $pdf->SetFillColor(238, 241, 251);
    $pdf->SetFont('Arial', '', 7);
    if ($usuarioPrevio->getNvj_id() <= 2) {
        $pdf->Cell(178, 5, utf8Decode(ucfirst(mb_strtoupper($d1['ur_cargoAdministrativo'], 'UTF-8'))), 0, 0, 'L', true);
        $pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
    }else{//Se agrega de manera temporal para evitar errores al generar el PDF
        $pdf->Cell(178, 5, utf8Decode(ucfirst(mb_strtoupper(' ', 'UTF-8'))), 0, 0, 'L', true);
        $pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
    }
    $c1->desconectar();

// se consulta el usuario que inserto la solicitud para identificar a su jefe inmediato
$nvjTabR = consultarNvjIdPorUsr($usuarioPrevio->getUsr_id(), true);
$nivel = $nvjTabR['nvj_id'];

/*FIN PUESTO RESPONSABLE*/

/*DEPARTAMENTO*/

$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(62);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(42, 5, utf8Decode('Departamento:'), 0, 0, 'L', true);
$pdf->setY(62);
$pdf->setX(37);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);

$c1 = new AccesoDatos();

if ($nvjTabR['nvj_id'] >= 3) {

    $c1->setSql('
        SELECT dep_id, dep_nombre Direccion
        FROM ' . BD_USUARIO . '.cat_UsuarioPrevio up
        JOIN ' . BD_USUARIO . '.cat_SolicitudAcceso sa USING(sac_id)
        LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable vur USING(uur_id)
        LEFT JOIN ' . $pdf->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
        LEFT JOIN ' . $pdf->ctrlEjercicioActual . '.cat_Departamento d USING(ssc_id, dir_id, uni_id, dep_id)
        WHERE
            up.usp_id = ' . $usuarioPrevio->getUsu() . ' AND
            vur.gpo_id = ' . $usuarioPrevio->getGpo_id() . ' AND
            vur.ur_id = ' . $usuarioPrevio->getUr_id() . ' AND
            vur.vur_ejercicio = sa.sac_ejercicio
        ;
    ');
} else {
    $c1->setSql('
        SELECT dep_id, dep_nombre Direccion
        FROM ' . BD_USUARIO . '.cat_UsuarioPrevio up
        JOIN ' . BD_USUARIO . '.cat_SolicitudAcceso sa USING(sac_id)
        LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable vur USING(uur_id)
        LEFT JOIN ' . $pdf->ctrlEjercicioActual . '.enc_JerarquiaSefin js USING(ejs_id)
        LEFT JOIN ' . $pdf->ctrlEjercicioActual . '.cat_Departamento d USING(ssc_id, dir_id, uni_id, dep_id)
        WHERE
            up.usp_id = ' . $usuarioPrevio->getUsu() . ' AND
            vur.gpo_id = ' . $usuarioPrevio->getGpo_id() . ' AND
            vur.ur_id = ' . $usuarioPrevio->getUr_id() . ' AND
            vur.vur_ejercicio = sa.sac_ejercicio
    ');
}

$c1->conectar();
$c1->consultar();
$d1 = $c1->extraer();
if ($d1 == '') {
    $d = 'Solo aplica para la UR 115';
} else {
    $d = $d1['Direccion'];
}
$c1->desconectar();
$pdf->Cell(163, 5, utf8Decode(ucfirst($d)), 0, 0, 'L', true);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
//FIN DEPARTAMENTO

//DIRECCION DE ADSCRIPCION

$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(68);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(43, 5, utf8Decode('Dirección de Adscripción:'), 0, 0, 'L', true);
$pdf->setY(68);
$pdf->setX(53);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);

$c1 = new AccesoDatos();
$c1->conectar();
//CONSULTA PARA OBTENER LA DIRECCION DE ADSCRIPCION
$c1->setSql('SELECT ur_nombre
                FROM ' . $pdf->ctrlEjercicioActual . '.cat_UnidadResponsable
                WHERE gpo_id = ' . $usuarioPrevio->getGpo_id() . '
                AND ur_id = ' . $usuarioPrevio->getUr_id() . ' limit 1;');
$c1->consultar();
$d1 = $c1->extraer();
$pdf->Cell(147, 5, utf8Decode(ucfirst($d1['ur_nombre'])), 0, 0, 'L', true);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
$c1->desconectar();

//FIN DIRECCION DE ADSCRIPCION

// CAMPO PARA DATOS TELEFONO
$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(74);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(45, 5, utf8Decode('Teléfono de Oficina:'), 0, 0, 'L', true);
$pdf->setY(74);
$pdf->setX(44);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 8);
$c1 = new AccesoDatos();
if ($usuarioPrevio->getNvj_id() <= 2) {
    $c1->setSql('SELECT ur_telefonoAdministrativo
                    FROM  ' . $pdf->ctrlEjercicioActual . '.aux_UnidadResponsable
                    WHERE gpo_id = ' . $usuarioPrevio->getGpo_id() . ' AND ur_id = ' . $usuarioPrevio->getUr_id() . ' limit 1;');
} else {
    $c1->setSql(' SELECT ur_telefonoAdministrativo  FROM  ' . $pdf->ctrlEjercicioActual . '.aux_UnidadResponsable');
}

$c1->conectar();
$c1->consultar();
$d1 = $c1->extraer();
$d2 = $d1['ur_telefonoAdministrativo'];

//si la consulta es vacia agrega los guiones
 if ($d2 == '') {
    $d = '--';
} else {
    $d = $d2;
}
$c1->desconectar();
$pdf->Cell(100, 5, utf8Decode(ucfirst(strtolower($d))), 0, 0, 'L', true);

/*EXTENSION TELEFONO*/
$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(74);
$pdf->setX(132);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(30, 5, utf8Decode('Extensión:'), 0, 0, 'L', true);
$pdf->setY(74);
$pdf->setX(152);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 8);
$c1 = new AccesoDatos();
if ($usuarioPrevio->getNvj_id() <= 2) {
    $c1->setSql('SELECT SUBSTRING(ur_telefonoAdministrativo, 11) as ur_telefonoAdministrativo from  ' . $pdf->ctrlEjercicioActual . '.aux_UnidadResponsable
                    WHERE gpo_id = ' . $usuarioPrevio->getGpo_id() . '
                    AND ur_id = ' . $usuarioPrevio->getUr_id() . ' limit 1;');
} else {
    $c1->setSql('SELECT SUBSTRING(ur_telefonoAdministrativo, 11) as ur_telefonoAdministrativo
                    FROM  ' . $pdf->ctrlEjercicioActual . '.aux_UnidadResponsable');
}

$c1->conectar();
$c1->consultar();
$d1 = $c1->extraer();
$d2 = $d1['ur_telefonoAdministrativo'];

if ($d2 == '') {
    $d = '--';
} else {
    $d = $d2;
}
$c1->desconectar();
$pdf->Cell(48, 5, utf8Decode($d), 0, 0, 'L', true);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);

/*CREAR USUARIO */

$pdf->ln();
$pdf->setY(25);
$pdf->setX(10);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->SetTextColor(90, 91, 95);
$pdf->MultiCell(110, 120, utf8Decode('Datos del usuario a crear'), 0, 'L', false);
$pdf->SetFont('Montserrat', '', 10);
$pdf->setY(35);
$pdf->setX(150);
$pdf->SetFillColor(210, 210, 210);
/*FIN CREAR USUARIO */

// REQUERIMIENTOS

$UR = $pdf->dameUr($usuarioPrevio->getGpo_id(), $usuarioPrevio->getUr_id());

/**nombre usuario */
$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(90);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(16, 5, utf8Decode('Nombre:'), 0, 0, 'L', true);
$pdf->setY(90);
$pdf->setX(26);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);

$pdf->Cell(174, 5, utf8Decode(ucfirst(mb_strtoupper($usuarioPrevio->getTitulo() . ' ' . $usuarioPrevio->getNombre() . ' ' . $usuarioPrevio->getPaterno() . ' ' . $usuarioPrevio->getMaterno() . '           (UR: ' . $UR . '    USUARIO: ' . $usuarioPrevio->getUsuario() . ')'))), 0, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
$pdf->setY(56);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
/**fin nombre usuario */

/**puesto usuario */

$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(96);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(13, 5, utf8Decode($cargo), 0, 0, 'L', true);
$pdf->setY(96);
$pdf->setX(22);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);

$pdf->Cell(178, 5, utf8Decode(ucfirst(mb_strtoupper($usuarioPrevio->getPuesto()))), 0, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
$pdf->setY(56);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);

/**fin puesto usuario */

/**correo usuario */

$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(102);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(60, 5, utf8Decode('Correo Electrónico Institucional:'), 0, 0, 'L', true);
$pdf->setY(102);
$pdf->setX(63);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);

$pdf->Cell(137, 5, utf8Decode(ucfirst(mb_strtoupper($usuarioPrevio->getCorreo()))), 0, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
$pdf->setY(56);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);

/**fin correo usuario */

/**usuario telefono */

$pdf->SetFont('Montserrat', '', 9);
$pdf->setY(108);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(35, 5, utf8Decode('Teléfono celular:'), 0, 0, 'L', true);
$pdf->setY(108);
$pdf->setX(38);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);

$pdf->Cell(162, 5, utf8Decode(ucfirst(mb_strtoupper($usuarioPrevio->getTelefono()))), 0, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->RoundedRect(10, $pdf->GetY() - .1, 190, 5.2, 2);
$pdf->setY(56);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);

/**fin telefono usuario */


/*acceso requerido */

$pdf->ln();
$pdf->setY(60);
$pdf->setX(10);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->SetTextColor(90, 91, 95);
$pdf->Cell(110, 120, utf8Decode('Acceso requerido'), 0, 'L', false);
$pdf->SetFont('Montserrat', '', 10);
$pdf->setY(35);
$pdf->setX(150);
$pdf->SetFillColor(210, 210, 210);
/*FIN acceso requerido */


/* Star sistemas */
$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(10);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(6, 5, '             ', 0, 0, 'L', true);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->setY(123.5);
$pdf->setX(14);
$pdf->Cell(40, 5, utf8Decode('  SI '), 0, 0, 'L', false);
$pdf->RoundedRect(10, $pdf->GetY() - .5, 6, 5, 1, '1234');

$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(22);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(6, 5, '             ', 0, 0, 'L', true);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->setY(123.5);
$pdf->setX(26);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(40, 5, utf8Decode('  SAI '), 0, 0, 'L', false);
$pdf->RoundedRect(22, $pdf->GetY() - .5, 6, 5, 1, '1234');

$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(37);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(6, 5, '             ', 0, 0, 'L', true);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->setY(123.5);
$pdf->setX(41);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(37, 5, utf8Decode('  SIOX '), 0, 0, 'L', false);
$pdf->RoundedRect(37, $pdf->GetY() - .5, 6, 5, 1, '1234');

$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(55);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(6, 5, 'X', 0, 0, 'C', true);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->setY(123.5);
$pdf->setX(59);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(40, 5, utf8Decode('  SEFIP '), 0, 0, 'L', false);
$pdf->RoundedRect(55, $pdf->GetY() - .5, 6, 5, 1, '1234');

$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(74);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(6, 5, utf8Decode(''), 0, 0, 'L', true);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->setY(123.5);
$pdf->setX(78);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(40, 5, utf8Decode('  SIGDA '), 0, 0, 'L', false);
$pdf->RoundedRect(74, $pdf->GetY() - .5, 6, 5, 1, '1234');

$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(116);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(6, 5, '             ', 0, 0, 'L', true);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->setY(123.5);
$pdf->setX(122);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(40, 5, utf8Decode('OTRO'), 0, 0, 'L', false);
$pdf->RoundedRect(116, $pdf->GetY() - .5, 6, 5, 1, '1234');

$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(135);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(25, 5, '             ', 0, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123.5);
$pdf->setX(158);
$pdf->SetFillColor(238, 241, 251);
$pdf->setX(138);
$pdf->Cell(40, 5, utf8Decode(''), 0, 0, 'L', false);
$pdf->RoundedRect(135, $pdf->GetY() - .5, 25, 5, 2, '1234');

$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(123);
$pdf->setX(180);
$pdf->SetFillColor(238, 241, 251);
$pdf->Cell(20, 5, '             ', 0, 0, 'L', true);
$pdf->SetFont('Montserrat-b', '', 9);
$pdf->setY(123.5);
$pdf->setX(156);
$pdf->SetFillColor(238, 241, 251);
$pdf->setX(160);
$pdf->Cell(40, 5, utf8Decode('EJERCICIO'), 0, 0, 'L', false);
$pdf->setX(184);
$pdf->Cell(40, 5, utf8Decode($solicitudAceso->getEjercicio()), 0, 0, 'L', false);
$pdf->RoundedRect(180, $pdf->GetY() - .5, 20, 5, 2, '1234');

/* End Sistemas */

if ($usuarioPrevio->getNuevo() == '1') {
    $tipoSolicitud = utf8Decode('Alta de Usuario');
} else {
    $tipoSolicitud = utf8Decode('Actualizar Permisos');
}

/*Tipo solicitud */

$pdf->setY(115);
$pdf->setX(10);
$pdf->SetFont('Montserrat-b', '', 10);
$pdf->SetTextColor(90, 91, 95);
$pdf->Cell(20, 40, utf8Decode('Tipo de Solicitud:'), 0, 'L', false);
$pdf->SetFont('Montserrat', '', 10);
$pdf->setY(132.5);
$pdf->setX(43);

$pdf->SetFont('Montserrat', '', 9);
$pdf->Cell(50, 5, utf8Decode(ucfirst(mb_strtoupper($tipoSolicitud))), 0, 0, 'L', false);
$pdf->SetFont('Arial', 'B', 10);
$pdf->setY(135);
$pdf->setX(10);
$pdf->SetFillColor(210, 210, 210);

/*FIN Tipo solicitud */

/**FIN DATOS PERSONALES */

/** PERMISOS NUEVO*/
/*HOJA DOS -> PEMISOS*/
$pdf->numeroDeSolicitud = $usuarioPrevio->getSac_id();
$pdf->zonaDePermisos = true;

$pdf->SetFont('Montserrat-b', '', 12);
$pdf->setXY(10, 140);
$pdf->MultiCell(100, 5, utf8Decode('PERMISOS'), 0, 'L', false);
$pdf->SetFont('Montserrat', '', 10);
$pdf->setY(146);
$pdf->setX(150);
$pdf->SetFillColor(210, 210, 210);

$pdf->setRelleno('si');
$pdf->setFillColor(220, 220, 220);
$pdf->SetFont('Montserrat-b', '', 8);
$pdf->SetTextColor(90, 91, 95);
$pdf->setX(10);
$pdf->SetDrawColor(180, 180, 180);
$pdf->SetBorders(1);
$pdf->SetWidths(array(108, 82));
$pdf->SetAligns(array('L', 'L'));
$pdf->Row(array(
    utf8Decode('SISTEMA - MENU - OPCIÓN'),
    utf8Decode('PERMISOS'),
));
$pdf->setRelleno('no');
$pdf->borders = 'TB';

// Marcos: 15/junio/2018
// Adicionamos una página para los permisos
// PERMISOS
$conexionPermisos = new AccesoDatos();
$conexionPermisos->conectar();
$conexionPermisos->setSql('
            SELECT sis_nombre, men_titulo, opc_titulo, IF(sis_id=\'I\',\'HABILITAR\',
                tar_nombre) tar_nombre
                FROM  ' . $pdf->ctrlEjercicioActual . '.cat_Sistema
                JOIN cat_Opcion opc USING(sis_id)
                JOIN cat_Menu USING(sis_id,men_id)
                JOIN cat_Permisos USING(sis_id,men_id,opc_id)
                JOIN cat_Tareas USING(tar_id)
                JOIN ' . BD_USUARIO.'.cat_PermisoUsuarioPrevio cpup USING (sis_id,men_id,opc_id,tar_id)
                WHERE cpup.usp_id=' . $usuarioPrevio->getUsp_id() . '
            GROUP BY sis_id, men_id, opc_id, tar_id
            ORDER BY sis_id, men_id, opc_id, tar_id');

$conexionPermisos->consultar();

$sis = '';
$men = '';
$opc = '';

$permisos = '';

while ($datos = $conexionPermisos->extraer()) {

    // Si cambió el sistema, lo imprimimos
    if ($sis != $datos['sis_nombre']) {
        $pdf->SetWidths(array(190));
        $pdf->setAligns(array('L'));
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Row(array(
            utf8Decode(($sis == '' ? $datos['sis_nombre'] : $sis)),
        ));
        $sis = $datos['sis_nombre'];
    }
    ;

    // Mientras sea la misma opción, acumulamos los permisos

    if ($opc == '' || $opc == $datos['opc_titulo']) {
        $permisos .= $datos['tar_nombre'] . ', ';
        // Si cambió, lo imprimimos
    } else {
        // Si cambio la opción, lo imprimimos
        if ($opc != $datos['opc_titulo']) {
            $pdf->setX(10);
            $pdf->SetWidths(array(4, 4, 100, 82));
            $pdf->setAligns(array('L', 'L', 'L', 'L'));
            $pdf->SetFont('Arial', '', 8);
            $pdf->Row(array(
                utf8Decode(''),
                utf8Decode(''),
                utf8Decode($opc),
                utf8Decode(substr($permisos, 0, -2)),
            ));
        }
        $permisos = $datos['tar_nombre'] . ', ';
    }

    // Si cambio el menú, lo imprimimos
    $opcMinimo = 15;
    if ($men != $datos['men_titulo']) {
        if (($pdf->GetY() + $opcMinimo) >= $pdf->limiteInferior) {
        $pdf->AddPage();
        }
        $pdf->SetWidths(array(4, 186));
        $pdf->setAligns(array('L', 'L'));
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Row(array(
            utf8Decode(''),
            utf8Decode($datos['men_titulo']),
        ));
        $men = $datos['men_titulo'];
    }

    $opc = $datos['opc_titulo'];
    $h = 5 * $pdf->NbLines(100, $opc);

    if (($pdf->GetY() + $h) >= $pdf->limiteInferior) {
        $pdf->AddPage();
    }
}

// La última línea no estará en el bucle, así que la imprimimos
$pdf->SetWidths(array(4, 4, 100, 82));
$pdf->setAligns(array('L', 'L', 'L', 'L'));
$pdf->SetFont('Arial', '', 8);
$pdf->Row(array(
    utf8Decode(''),
    utf8Decode(''),
    utf8Decode($opc),
    utf8Decode(substr($permisos, 0, -2)),
));

$firmas = 45;

if(($pdf->GetY() + $firmas) > $pdf->limiteInferior){
    $pdf->zonaDePermisos = false;
    $pdf->AddPage();
    $pdf->ln(15);
}

/**FINAL DE LA HOJA */
// COMENTARIOS
$pdf->ln(6);
$pos = $pdf->GetY();
$comentario = utf8Decode(strtoupper($solicitudAceso->getComentario()));
$lineas = ceil(strlen($comentario) / 110);
$alturaTexto = $lineas * 5;
$alturaCaja = $alturaTexto + 10 + 4;


if (($pdf->GetY() + $alturaCaja + 40) > $pdf->limiteInferior) {
    $pdf->AddPage();
    $pos = $pdf->GetY();
}


$pdf->SetFillColor(238, 241, 251);
$pdf->RoundedRect(10, $pos, 191, $alturaCaja, 2, 'DF');

$pdf->SetFont('Montserrat-b', '', 8);
$pdf->SetFillColor(210, 210, 210);

$pdf->RoundedRect(10.1, $pos, 190.8, 5, 1, 'DF');

$pdf->SetXY(10.1, $pos);
$pdf->Cell(190.8, 5, utf8Decode('Comentarios:'), 0, 0, 'L');

$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 7);
$pdf->setXY(10, $pos + 6);
$pdf->MultiCell(190, 5, empty (trim ($comentario)) ? 'NINGUNO' : $comentario, 0, 'J');

$pos = $pdf->GetY();
/*PARA LLENAR EL NOMBRE DEL RESPONSABLE*/
$pdf->SetXY(10, $pos + 27);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(85, 6, utf8Decode(ucfirst(mb_strtoupper($usuarioPrevio->getNombre() . ' ' . $usuarioPrevio->getPaterno() . ' ' . $usuarioPrevio->getMaterno(), 'UTF-8'))), 0, 0, 'C', true);
$pdf->SetX(10);

/*FIN NOMBRE RESPONSABLE*/

//FIN PARA PUESTO

/*NOMBRE JEFE QUE AUTORIZA*/
$pdf->setXY(10, $pos + 32 );
$pdf->SetFillColor(210, 210, 210);
$pdf->SetFont('Montserrat-b', '', 8);
$pdf->Cell(85, 6, utf8Decode('Nombre y Firma del Usuario'), 0, 0, 'C', true);
$pdf->RoundedRect(10, $pos + 21, 85, 17, 2, '1234');

$pdf->setXY(115, $pos + 27 );
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 8);

$c2J = new AccesoDatos();
$c2J->conectar();
$pdf->Cell(85, 6, utf8Decode(ucfirst(mb_strtoupper($solicitudAceso->getResponsable(), 'UTF-8'))), 0, 0, 'C', true);

/*FIN NOMBRE JEFE QUE AUTORIZA*/

/*PARA PUESTO */
$c2J = new AccesoDatos();
if ($usuarioPrevio->getNvj_id() <= 2) {
    $c2J->setSql('select ur_id, concat(ur_nombreAdministrativo) nombre,ur_cargoAdministrativo from ' . $pdf->ctrlEjercicioActual . '.aux_UnidadResponsable WHERE gpo_id =' . $usuarioPrevio->getGpo_id() . ' and ur_id=' . $usuarioPrevio->getUr_id() . ' limit 1;');
    $c2J->conectar();
    $c2J->consultar();
    $d1J = $c2J->extraer();
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->setXY(115, $pos + 32);
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetFont('Montserrat-b', '', 8);
    $pdf->Cell(85, 6, utf8Decode('Nombre y Firma del Titular de la Unidad Responsable'), 0, 0, 'C', true);
    $pdf->setXY(10, $pos + 22);
    $pdf->RoundedRect(115, $pos + 21, 85, 17, 2, '1234');
    $c2J->desconectar();
}else{ //Se agrega de manera temporal para evitar errores al generar el PDF
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->setXY(115, $pos + 32);
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetFont('Montserrat-b', '', 8);
    $pdf->Cell(85, 6, utf8Decode('Nombre y Firma del Titular de la Unidad Responsable'), 0, 0, 'C', true);
    $pdf->setXY(10, $pos + 22);
    $pdf->RoundedRect(115, $pos + 21, 85, 17, 2, '1234');
}

// se consulta el usuario que inserto la solicitud para identificar a su jefe inmediato
$nvjTabR = consultarNvjIdPorUsr($usuarioPrevio->getUsr_id());
$nivel = $nvjTabR['nvj_id'];

//FIN PUESTO

$pdf->SetFont('Arial', 'B', 6);
$pdf->setXY(132, $pos + 37.5 );
$pdf->SetFillColor(238, 241, 251);
$pdf->SetTextColor(220,20,60);
$pdf->Cell(50, 5, utf8Decode('(Obligatorio: Debe de ser firmado y sellado por personal de nivel 22 o superior)'), 0, 0, 'C', false);

$pdf->SetFont('Montserrat', '', 9);
$pdf->SetTextColor(90, 91, 95);
$pdf->setXY(139, $pos + 48);
$pdf->SetFillColor(210, 210, 210);
$pdf->Cell(38, 5, utf8Decode('Fecha de solicitud:'), 0, 0, 'L', true);
$pdf->setXY(172, $pos + 48);
$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 9);

$pdf->Cell(28, 5, $solicitudAceso->getFechaAlta(), 0, 0, 'C', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->RoundedRect(139, $pos + 47.9, 60, 5.2, 2);

$pdf->SetFillColor(238, 241, 251);
$pdf->SetFont('Arial', '', 6);

// Terminamos
$pdf->Output('Solicitud de acceso No.'.utf8Decode(ucwords(strtoupper($usuarioPrevio->getSac_id()))),'I');
