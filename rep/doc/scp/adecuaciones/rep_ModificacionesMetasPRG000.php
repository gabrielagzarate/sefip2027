<?php

session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once '../../../../rep/lib/reportetabla.1.8.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/helpers.php';

class reportetabla extends PDF
{
    public function __construct()
    {
        parent::__construct();
    }

    public function Header() //Encabezado del documento
    {
        $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
        $gpo_id = (isset($_POST['mme_gpo_id']) && $_POST['mme_gpo_id'] != '') ? $_POST['mme_gpo_id'] : '';
        $ur_id = (isset($_POST['mme_ur_id']) && $_POST['mme_ur_id'] != '') ? $_POST['mme_ur_id'] : '';
        $mme_id = (isset($_POST['mme_id']) && $_POST['mme_id'] != '') ? $_POST['mme_id'] : '';
        $emo_id = (isset($_POST['emo_id']) && $_POST['emo_id'] != '') ? $_POST['emo_id'] : '';

        $objUnidadResponsable = new UnidadResponsable();
        $objUnidadResponsable->setGpo_id($gpo_id);
        $objUnidadResponsable->setUr_id($ur_id);
        $objUnidadResponsable->select();

        $datos = $this->getDatos();
        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $ix = $this->GetX();
        $iy = $this->GetY();
        $iyt = $this->GetY();

        $this->setRouteImg2('escudo2');
        $this->Image($this->getRouteImg(), $ix + 164, $iy - 1, '85', '18', 'JPG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(
            195,
            5,
            utf8Decode('GOBIERNO DEL ESTADO DE OAXACA'),
            0,
            1,
            'L'
        );
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, utf8Decode('CONTROL PRESUPUESTAL ' . APP_EJERCICIO), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;

        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $this->Cell(
            180,
            5,
            'UR: ' . $objUnidadResponsable->getGpo_id() . $objUnidadResponsable->getUr_id() . ' - ' . utf8Decode($objUnidadResponsable->getNombre()),
            0,
            1,
            'L'
        );

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 2;
        $this->Cell(
            180,
            5,
            utf8Decode('SOLICITUD DE ADECUACIÓN DE METAS PROGRAMÁTICAS'),
            0,
            1,
            'L'
        );

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 2;
        $this->Cell(180, 5, utf8Decode('FOLIO:' . $mme_id), 0, 1, 'L');

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('ARIAL', 'B', '7');
        $this->Ln();
        $xtm = $this->getX();
        $ytm = $this->getY();
        $linea = (((isset($datos['mod_Subsecretaria']) == 2) || (isset($datos['omo_id']) == 'E'
            || isset($datos['omo_id'])) == 'F') ? 285 : 272);
        $this->Line($xtm, $ytm, $linea, $ytm);
        $this->Ln(.7);
        $this->SetWidths([47, 28, 10, 35, 10, 35, 10, 35, 10, 35]);
        $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);
        $this->SetAltoLinea(2.5);
        $this->Row([
            'ACTIVIDAD / INDICADOR',
            'ANUAL',
            'TIPO',
            'TRIMESTRE 1',
            'TIPO',
            'TRIMESTRE 2',
            'TIPO',
            'TRIMESTRE 3',
            'TIPO',
            'TRIMESTRE 4',
        ]);
        $xtm = $this->getX();
        $ytm = $this->getY();
        $this->Line($xtm, $ytm, $linea, $ytm);
        $this->Ln(.7);
        //Restauración de colores y fuentes
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
    }

    //Encabezado del documento
    public function Footer($lastPage = true)
    {
        $datos = $this->getDatos();
        if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 118 && $_SESSION[_S]['prf_id']
            == 'U') {
            $this->SetY(-45);
        } else {
            $this->SetY(-32);
        }
        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Número de página
        if ($lastPage) {
            $emo = '';
            switch ($_SESSION[_S]['nvj_id']) {
                case 1: //UR
                case 2: //UE
                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mme_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mme_ur_id']);
                    $objUnidadResponsable->select();
                    $datos['emo_id'] = (isset($datos['emo_id']) ? $datos['emo_id'] : '');
                    switch ($datos['emo_id']) {
                        case 1:
                        case 3:
                        case 6:
                        case 7:
                        case 8:
                        case 9:
                        case 17:
                            //Verifica la UR responsable en sesion
                            switch ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id']) {
                                case 118:
                                    //Verifica el perfil del usuario
                                    switch ($_SESSION[_S]['prf_id']) {
                                        case 'S': //Supervisor
                                            $datosUsuario = new Usuario();
                                            $datosUsuario->setUsr_id($_SESSION[_S]['usr_id']);
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
                                            $sql = "
                                                INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                                mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                                mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                                mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', '', '', '', '', '', '', '', ?)
                                                ON DUPLICATE KEY UPDATE
                                                mme_cargo1='" . $cargoUsuario . "',
                                                mme_firma1='" . $elabora . "',
                                                mme_cargo2='" . $cargoAdmvo . "',
                                                mme_firma2='" . $revisorAdmvo . "',
                                                mme_cargo3='" . $cargoTitular . "',
                                                mme_firma3='" . $titular . "';
                                            ";
                                            $paramsS = [
                                                $datos['omo_id'],
                                                $objUnidadResponsable->getGpo_id(),
                                                $objUnidadResponsable->getUr_id(),
                                                $datos['mme_id'],
                                                $cargoUsuario,
                                                $elabora,
                                                $cargoAdmvo,
                                                $revisorAdmvo,
                                                $cargoTitular,
                                                $titular,
                                                $_SESSION[_S]['usr_id'],
                                            ];
                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($paramsS);

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mme_cargo1,mme_firma1,mme_cargo2,mme_firma2,mme_cargo3,mme_firma3
                                                FROM scp_ModificacionMetaFirma
                                                WHERE omo_id = ?
                                                AND mme_gpo_id = ?
                                                AND mme_ur_id = ?
                                                AND mme_id = ?;
                                            ';
                                            $params = [
                                                $datos['omo_id'],
                                                $objUnidadResponsable->getGpo_id(),
                                                $objUnidadResponsable->getUr_id(),
                                                $datos['mme_id'],
                                            ];
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($params);
                                            $datosFirma = $firmasReportes->extraer();

                                            $this->Cell(
                                                92.5,
                                                2,
                                                utf8Decode(''),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->Ln(5);
                                            $this->SetFont('Arial', '', 7);
                                            $this->Cell(
                                                100.5,
                                                2,
                                                utf8Decode('ELABORÓ'),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(20);
                                            $this->Cell(
                                                230,
                                                2,
                                                utf8Decode('REVISÓ'),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(170);
                                            $this->Cell(
                                                92.5,
                                                2,
                                                'Vo.Bo.',
                                                0,
                                                1,
                                                'C'
                                            );
                                            $this->Ln(1);
                                            $this->SetFont('Arial', 'B', 7);
                                            $y = $this->GetY();
                                            $this->Cell(
                                                100.5,
                                                4,
                                                utf8Decode($datosFirma['mme_firma1']),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(100);
                                            $this->Cell(
                                                70,
                                                4,
                                                utf8Decode($datosFirma['mme_firma2']),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(165);
                                            $this->Cell(
                                                99,
                                                4,
                                                utf8Decode($datosFirma['mme_firma3']),
                                                0,
                                                1,
                                                'C'
                                            );
                                            $this->Ln(2);
                                            $this->SetFont('Arial', '', 7);
                                            $this->Cell(
                                                252,
                                                2,
                                                utf8Decode($datosFirma['mme_cargo2']),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(165);
                                            $this->Cell(
                                                100.5,
                                                2,
                                                utf8Decode($datosFirma['mme_cargo3']),
                                                0,
                                                1,
                                                'C'
                                            );
                                            break;
                                        case 'A':
                                        case 'C':
                                        case 'T':
                                            $revisorAdmvo = $objUnidadResponsable->getNombreAdministrativo();
                                            $cargoAdmvo = $objUnidadResponsable->getCargoAdministrativo();
                                            $titular = $objUnidadResponsable->getNombreTitular();
                                            $cargoTitular = $objUnidadResponsable->getCargoTitular();

                                            //Registra o actualiza las firmas
                                            $sql = "
                                                INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                                mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                                mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                                mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                                VALUES (?, ?, ?, ?, '', '', ?, ?, ?, ?, '', '', '', '', '', '', '', '', ?)
                                                ON DUPLICATE KEY UPDATE
                                                mme_cargo2='" . $cargoAdmvo . "',
                                                mme_firma2='" . $revisorAdmvo . "',
                                                mme_cargo3='" . $cargoTitular . "',
                                                mme_firma3='" . $titular . "';
                                            ";
                                            $paramsT = [
                                                $datos['omo_id'],
                                                $objUnidadResponsable->getGpo_id(),
                                                $objUnidadResponsable->getUr_id(),
                                                $datos['mme_id'],
                                                $cargoAdmvo,
                                                $revisorAdmvo,
                                                $cargoTitular,
                                                $titular,
                                                $_SESSION[_S]['usr_id'],
                                            ];
                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($paramsT);

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mme_cargo2,mme_firma2,mme_cargo3,mme_firma3
                                                FROM scp_ModificacionMetaFirma
                                                WHERE omo_id = ?
                                                AND mme_gpo_id = ?
                                                AND mme_ur_id = ?
                                                AND mme_id = ?;
                                            ';
                                            $params = [
                                                $datos['omo_id'],
                                                $objUnidadResponsable->getGpo_id(),
                                                $objUnidadResponsable->getUr_id(),
                                                $datos['mme_id'],
                                            ];
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar($params);
                                            $datosFirma = $firmasReportes->extraer();

                                            $this->Cell(
                                                92.5,
                                                2,
                                                utf8Decode(''),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->Ln(5);
                                            $this->SetFont('Arial', '', 7);
                                            $this->Cell(
                                                100.5,
                                                2,
                                                utf8Decode('REVISÓ'),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(165);
                                            $this->Cell(
                                                92.5,
                                                2,
                                                'Vo.Bo.',
                                                0,
                                                1,
                                                'C'
                                            );
                                            $this->Ln(1);
                                            $this->SetFont('Arial', 'B', 7);
                                            $y = $this->GetY();
                                            $this->Cell(
                                                100.5,
                                                4,
                                                utf8Decode($datosFirma['mme_firma2']),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(165);
                                            $this->Cell(
                                                92.5,
                                                4,
                                                utf8Decode($datosFirma['mme_firma3']),
                                                0,
                                                1,
                                                'C'
                                            );
                                            $this->Ln(2);
                                            $this->SetFont('Arial', '', 7);
                                            $this->Cell(
                                                100.5,
                                                2,
                                                utf8Decode($datosFirma['mme_cargo2']),
                                                0,
                                                0,
                                                'C'
                                            );
                                            $this->SetX(165);
                                            $this->Cell(
                                                92,
                                                2,
                                                utf8Decode($datosFirma['mme_cargo3']),
                                                0,
                                                1,
                                                'C'
                                            );
                                            break;
                                    }
                                    break;
                                default:
                                    if (($objUnidadResponsable->getRh() == 1 ||
                                        (
                                            $objUnidadResponsable->getGpo_id() == 5
                                            &&
                                            ($objUnidadResponsable->getUr_id() == '06'
                                                ||
                                                $objUnidadResponsable->getUr_id() == '07'
                                                ||
                                                $objUnidadResponsable->getUr_id() == '08'
                                                ||
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
                                        )) && $datos['omo_id'] == 'A' && $datos['tfm_id']
                                        == 'A') {

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
                                        $sql = "
                                            INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                            mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                            mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                            mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '','', '','', '','', '','', ?)
                                            ON DUPLICATE KEY UPDATE
                                            mme_cargo1='" . $cargoAdmvo . "',
                                            mme_firma1='" . $jefeAdmvo . "',
                                            mme_cargo2='" . $cargoTitular . "',
                                            mme_firma2='" . $titular . "',
                                            mme_cargo3='" . $cargoTitularRh . "',
                                            mme_firma3='" . $titularRh . "';";
                                        $paramsD = [
                                            $datos['omo_id'],
                                            $objUnidadResponsable->getGpo_id(),
                                            $objUnidadResponsable->getUr_id(),
                                            $datos['mme_id'],
                                            $cargoAdmvo,
                                            $jefeAdmvo,
                                            $cargoTitular,
                                            $titular,
                                            $cargoTitularRh,
                                            $titularRh,
                                            $_SESSION[_S]['usr_id'],
                                        ];
                                        $firmasReportes = new AccesoDatos();
                                        $firmasReportes->conectar();
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($paramsD);

                                        //Obtiene las firmas
                                        $sql = '
                                            SELECT mme_cargo1,mme_firma1,mme_cargo2,mme_firma2,mme_cargo3,mme_firma3
                                            FROM scp_ModificacionMetaFirma
                                            WHERE omo_id = ?
                                            AND mme_gpo_id = ?
                                            AND mme_ur_id = ?
                                            AND mme_id = ?;
                                        ';
                                        $params = [
                                            $datos['omo_id'],
                                            $objUnidadResponsable->getGpo_id(),
                                            $objUnidadResponsable->getUr_id(),
                                            $datos['mme_id'],
                                        ];
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($params);
                                        $datosFirma = $firmasReportes->extraer();
                                        $this->Cell(
                                            92.5,
                                            2,
                                            utf8Decode(''),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->Ln(5);
                                        $this->SetFont('Arial', '', 7);
                                        $this->Cell(
                                            80,
                                            2,
                                            'JEFE ADMINISTRATIVO',
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(30);
                                        $this->Cell(
                                            214,
                                            2,
                                            'TITULAR DE LA DEPENDENCIA/ENTIDAD',
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(170);
                                        $this->Cell(110, 2, 'Vo.Bo.', 0, 1, 'C');
                                        $this->Ln(1);
                                        $this->SetFont('Arial', 'B', 7);
                                        $y = $this->GetY();
                                        $this->Cell(
                                            80,
                                            4,
                                            utf8Decode($datosFirma['mme_firma1']),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(132);
                                        $this->Cell(
                                            15,
                                            4,
                                            utf8Decode($datosFirma['mme_firma2']),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(100);
                                        $this->Cell(
                                            250,
                                            4,
                                            utf8Decode($datosFirma['mme_firma3']),
                                            0,
                                            1,
                                            'C'
                                        );

                                        $this->Ln(.3);
                                        $this->SetFont('Arial', '', 7);
                                        $this->Cell(
                                            83,
                                            2,
                                            utf8Decode($datosFirma['mme_cargo1']),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(90);
                                        $this->Cell(
                                            100.5,
                                            2,
                                            utf8Decode($datosFirma['mme_cargo2']),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(175);
                                        $this->Cell(
                                            100.5,
                                            2,
                                            utf8Decode($datosFirma['mme_cargo3']),
                                            0,
                                            1,
                                            'C'
                                        );
                                    } else {
                                        $jefeAdmvo = $objUnidadResponsable->getNombreAdministrativo();
                                        $puestoAdmvo = $objUnidadResponsable->getCargoAdministrativo();
                                        $titular = $objUnidadResponsable->getNombreTitular();
                                        $puestoTitular = $objUnidadResponsable->getCargoTitular();
                                        //Registra o actualiza las firmas
                                        $sql = "
                                            INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                            mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                            mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                            mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, '','', '','', '','', '','', '','', ?)
                                            ON DUPLICATE KEY UPDATE
                                            mme_cargo1='" . $puestoAdmvo . "',
                                            mme_firma1='" . $jefeAdmvo . "',
                                            mme_cargo2='" . $puestoTitular . "',
                                            mme_firma2='" . $titular . "';
                                        ";
                                        $paramsE = [
                                            $datos['omo_id'],
                                            $objUnidadResponsable->getGpo_id(),
                                            $objUnidadResponsable->getUr_id(),
                                            $datos['mme_id'],
                                            $puestoAdmvo,
                                            $jefeAdmvo,
                                            $puestoTitular,
                                            $titular,
                                            $_SESSION[_S]['usr_id'],
                                        ];
                                        $firmasReportes = new AccesoDatos();
                                        $firmasReportes->conectar();
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($paramsE);

                                        //Obtiene las firmas
                                        $sql = '
                                            SELECT mme_cargo1,mme_firma1,mme_cargo2,mme_firma2
                                            FROM scp_ModificacionMetaFirma
                                            WHERE omo_id = ?
                                            AND mme_gpo_id = ?
                                            AND mme_ur_id = ?
                                            AND mme_id = ?;
                                        ';
                                        $params = [
                                            $datos['omo_id'],
                                            $objUnidadResponsable->getGpo_id(),
                                            $objUnidadResponsable->getUr_id(),
                                            $datos['mme_id'],
                                        ];
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar($params);
                                        $datosFirma = $firmasReportes->extraer();

                                        $this->Cell(
                                            92.5,
                                            2,
                                            utf8Decode(''),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->Ln(12.5);
                                        $this->SetFont('Arial', '', 7);
                                        $this->Cell(
                                            100.5,
                                            2,
                                            'JEFE ADMINISTRATIVO',
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(165);
                                        $this->Cell(
                                            92.5,
                                            2,
                                            'TITULAR DE LA DEPENDENCIA/ENTIDAD',
                                            0,
                                            1,
                                            'C'
                                        );
                                        $this->Ln(1);
                                        $this->SetFont('Arial', 'B', 7);
                                        $y = $this->GetY();
                                        $this->Cell(
                                            100.5,
                                            4,
                                            utf8Decode($datosFirma['mme_firma1']),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(165);
                                        $this->Cell(
                                            92.5,
                                            4,
                                            utf8Decode($datosFirma['mme_firma2']),
                                            0,
                                            1,
                                            'C'
                                        );
                                        $this->Ln(.3);
                                        $this->SetFont('Arial', '', 7);
                                        $this->MultiAlignCell(
                                            100.5,
                                            3,
                                            utf8Decode($datosFirma['mme_cargo1']),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(165);
                                        $this->MultiAlignCell(
                                            100.5,
                                            3,
                                            utf8Decode($datosFirma['mme_cargo2']),
                                            0,
                                            1,
                                            'C'
                                        );
                                    }
                                    break;
                            }
                            break;
                    }
                    break;
                default: //SEFIN

                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mme_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mme_ur_id']);
                    $objUnidadResponsable->select();

                    $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '')
                    ? $_POST['omo_id'] : '';
                    $mme_id = (isset($_POST['mme_id']) && $_POST['mme_id'] != '')
                    ? $_POST['mme_id'] : '';

                    $nom_analista = '';
                    $cargo_analista = '';
                    $nom_departamento = '';
                    $cargo_departamento = '';
                    $nom_unidad = '';
                    $cargo_unidad = '';
                    $nom_director = '';
                    $cargo_director = '';

                    $omo_id = isset($datos['omo_id']) ? $datos['omo_id'] : '';
                    $mme_id = isset($datos['mme_id']) ? $datos['mme_id'] : 0;
                    if ($omo_id != '' || $mme_id != 0) {
                        //Obtiene las firmas
                        $sql = '
                            SELECT mme_cargo4,mme_firma4,mme_cargo5,mme_firma5,mme_cargo6,mme_firma6,
                            mme_cargo7,mme_firma7
                            FROM scp_ModificacionMetaFirma
                            WHERE omo_id = ?
                            AND mme_gpo_id = ?
                            AND mme_ur_id = ?
                            AND mme_id = ?;
                        ';
                        $params = [
                            $datos['omo_id'],
                            $objUnidadResponsable->getGpo_id(),
                            $objUnidadResponsable->getUr_id(),
                            $datos['mme_id'],
                        ];
                        $firmasReportes = new AccesoDatos();
                        $firmasReportes->conectar();
                        $firmasReportes->setSql($sql);
                        $firmasReportes->consultar($params);
                        $datosFirma = $firmasReportes->extraer();
                        if ($firmasReportes->getNumeroRegistros() > 0) {
                            $nom_analista = $datosFirma['mme_firma4'];
                            $cargo_analista = $datosFirma['mme_cargo4'];
                            $nom_departamento = $datosFirma['mme_firma5'];
                            $cargo_departamento = $datosFirma['mme_cargo5'];
                            $nom_unidad = $datosFirma['mme_firma6'];
                            $cargo_unidad = $datosFirma['mme_cargo6'];
                            $nom_director = $datosFirma['mme_firma7'];
                            $cargo_director = $datosFirma['mme_cargo7'];
                        }
                    }

                    $datos['emo_id'] = (isset($datos['emo_id']) ? $datos['emo_id'] : '');
                    switch ($datos['emo_id']) {
                        case 6:
                        case 11:
                        case 17:
                        case 8:
                        case 13:

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

                                            $this->SetFont('Arial', 'B', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C',
                                                'C']);
                                            $this->Row([
                                                utf8Decode($cargo_analista),
                                                utf8Decode($cargo_departamento),
                                                utf8Decode($cargo_unidad),
                                                utf8Decode($cargo_director),
                                            ]);

                                            $this->SetFont('Arial', '', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C',
                                                'C']);
                                            $this->Row([
                                                utf8Decode($nom_analista),
                                                utf8Decode($nom_departamento),
                                                utf8Decode($nom_unidad),
                                                utf8Decode($nom_director),
                                            ]);
                                        } else {
                                            $this->SetXY(10, -20);
                                            $this->SetFont('Arial', 'B', 7);
                                            $this->Cell(
                                                100.5,
                                                2,
                                                utf8Decode($cargo_analista),
                                                0,
                                                1,
                                                'C',
                                                0
                                            );

                                            $this->SetXY(97, -20);
                                            $this->Cell(
                                                92.5,
                                                2,
                                                utf8Decode($cargo_departamento),
                                                0,
                                                1,
                                                'C',
                                                0
                                            );

                                            $this->SetXY(178, -20);
                                            $this->Cell(
                                                84.5,
                                                2,
                                                utf8Decode($cargo_unidad),
                                                0,
                                                1,
                                                'C',
                                                0
                                            );

                                            $this->SetFont('Arial', '', 7);
                                            $this->SetXY(30, -13);
                                            $this->MultiCell(
                                                63.5,
                                                4,
                                                utf8Decode($nom_analista),
                                                0,
                                                'C'
                                            );

                                            $this->SetXY(93, -13);
                                            $this->MultiCell(
                                                100.5,
                                                4,
                                                utf8Decode($nom_departamento),
                                                0,
                                                'C'
                                            );

                                            $this->SetXY(170, -13);
                                            $this->MultiCell(
                                                100.5,
                                                4,
                                                utf8Decode($nom_unidad),
                                                0,
                                                'C'
                                            );
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

                                        if ($datos['emo_id'] == 8 || $datos['emo_id']
                                            == 11 || $datos['emo_id'] == 17) {
                                            $this->SetFont('Arial', 'B', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C',
                                                'C']);
                                            $this->Row([
                                                utf8Decode($cargo_analista),
                                                utf8Decode($cargo_departamento),
                                                utf8Decode($cargo_unidad),
                                                utf8Decode($cargo_director),
                                            ]);

                                            $this->SetFont('Arial', '', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C',
                                                'C']);
                                            $this->Row([
                                                utf8Decode($nom_analista),
                                                utf8Decode($nom_departamento),
                                                utf8Decode($nom_unidad),
                                                utf8Decode($nom_director),
                                            ]);
                                        } else {

                                            $this->SetXY(10, -20);
                                            $this->SetFont('Arial', 'B', 7);
                                            $this->Cell(
                                                100.5,
                                                2,
                                                utf8Decode($cargo_analista),
                                                0,
                                                1,
                                                'C',
                                                0
                                            );

                                            $this->SetXY(97, -20);
                                            $this->Cell(
                                                92.5,
                                                2,
                                                utf8Decode($cargo_departamento),
                                                0,
                                                1,
                                                'C',
                                                0
                                            );

                                            $this->SetXY(178, -20);
                                            $this->Cell(
                                                84.5,
                                                2,
                                                utf8Decode($cargo_unidad),
                                                0,
                                                1,
                                                'C',
                                                0
                                            );

                                            $this->SetFont('Arial', '', 7);
                                            $this->SetXY(30, -13);
                                            $this->MultiCell(
                                                63.5,
                                                4,
                                                utf8Decode($nom_analista),
                                                0,
                                                'C'
                                            );

                                            $this->SetXY(93, -13);
                                            $this->MultiCell(
                                                100.5,
                                                4,
                                                utf8Decode($nom_departamento),
                                                0,
                                                'C'
                                            );

                                            $this->SetXY(170, -13);
                                            $this->MultiCell(
                                                100.5,
                                                4,
                                                utf8Decode($nom_unidad),
                                                0,
                                                'C'
                                            );
                                        }
                                    }
                                    break;
                                default:
                                    switch ($datos['emo_id']) { //Verifica el estado de la modificacion
                                        case 6:
                                        case 8:
                                        case 11:
                                        case 17:
                                            if ($datos['emo_id'] == 6 || $datos['emo_id'] == 8 || $datos['emo_id']
                                                == 11 || $datos['emo_id'] == 17) {

                                                $this->SetFont('Arial', 'B', 7);
                                                $this->SetWidths([67, 67, 67,
                                                    67]);
                                                $this->SetAligns(['C', 'C',
                                                    'C', 'C']);
                                                $this->Row([
                                                    utf8Decode($cargo_analista),
                                                    utf8Decode($cargo_departamento),
                                                    utf8Decode($cargo_unidad),
                                                    utf8Decode($cargo_director),
                                                ]);

                                                $this->SetFont('Arial', '', 7);
                                                $this->SetWidths([67, 67, 67,
                                                    67]);
                                                $this->SetAligns(['C', 'C',
                                                    'C', 'C']);
                                                $this->Row([
                                                    utf8Decode($nom_analista),
                                                    utf8Decode($nom_departamento),
                                                    utf8Decode($nom_unidad),
                                                    utf8Decode($nom_director),
                                                ]);
                                            } else {
                                                $this->SetXY(10, -20);
                                                $this->SetFont('Arial', 'B', 7);
                                                $this->Cell(
                                                    100.5,
                                                    2,
                                                    utf8Decode($cargo_analista),
                                                    0,
                                                    1,
                                                    'C',
                                                    0
                                                );

                                                $this->SetXY(97, -20);
                                                $this->Cell(
                                                    92.5,
                                                    2,
                                                    utf8Decode($cargo_departamento),
                                                    0,
                                                    1,
                                                    'C',
                                                    0
                                                );

                                                $this->SetXY(178, -20);
                                                $this->Cell(
                                                    84.5,
                                                    2,
                                                    utf8Decode($cargo_unidad),
                                                    0,
                                                    1,
                                                    'C',
                                                    0
                                                );

                                                $this->SetFont('Arial', '', 7);
                                                $this->SetXY(30, -13);
                                                $this->MultiCell(
                                                    63.5,
                                                    4,
                                                    utf8Decode($nom_analista),
                                                    0,
                                                    'C'
                                                );

                                                $this->SetXY(93, -13);
                                                $this->MultiCell(
                                                    100.5,
                                                    4,
                                                    utf8Decode($nom_departamento),
                                                    0,
                                                    'C'
                                                );

                                                $this->SetXY(170, -13);
                                                $this->MultiCell(
                                                    100.5,
                                                    4,
                                                    utf8Decode($nom_unidad),
                                                    0,
                                                    'C'
                                                );
                                            }
                                            break;
                                    }
                            }
                            break;
                    }
                    break;
            }
        } else {
            $this->SetY(-20);
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(10, 2, utf8Decode('CONTINUA...'), 0, 1, 'L');

            switch ($_SESSION[_S]['nvj_id']) {
                case 1:case 2: //Ur y Ue
                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mme_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mme_ur_id']);
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
                    if ($datos['omo_id'] == 'A' && ($datos['emo_id'] == 6 || $datos['emo_id']
                        == 11)) {
                        $rubricas = 'Rúbricas:________________________, ________________________, ________________________';
                        $this->Cell(252, 2, utf8Decode($rubricas), 0, 1, 'R');
                    }
                    break;
            }
        }
    }
}
$omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id']
: '';
$gpo_id = (isset($_POST['mme_gpo_id']) && $_POST['mme_gpo_id'] != '') ? $_POST['mme_gpo_id']
: '';
$ur_id = (isset($_POST['mme_ur_id']) && $_POST['mme_ur_id'] != '') ? $_POST['mme_ur_id']
: '';
$mme_id = (isset($_POST['mme_id']) && $_POST['mme_id'] != '') ? $_POST['mme_id']
: '';
$emo_id = (isset($_POST['emo_id']) && $_POST['emo_id'] != '') ? $_POST['emo_id']
: '';

$tipo = '';
$filtro = '';
$indicador = '';

//Se agrega condición para el modulo de consultas la opcion consulta folios de metas para que muestre el pdf cuando este autorizado un folio de metas
$fechaRegistroEnMactAnterior = strtotime('2025-05-02');
$fechaFolioAurorizado = '';
$datosHistoricoMeta = false;

if ($emo_id == 14) {
    $sql = '
        SELECT mme_fechaAutorizacion
        FROM scp_ModificacionMeta
        WHERE omo_id = :omo_id
        AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id
        AND mme_id = :mme_id
    ';
    $params = [
        ':gpo_id' => $gpo_id,
        ':ur_id'  => $ur_id,
        ':omo_id' => $omo_id,
        ':mme_id' => $mme_id,
    ];
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();
    $datos = $conexion->extraer();
    $fechaFolioAurorizado = strtotime(date($datos['mme_fechaAutorizacion']));
    if ($fechaFolioAurorizado >= $fechaRegistroEnMactAnterior) {
        $datosHistoricoMeta = true;
    }
}

if ($emo_id == 14 && $datosHistoricoMeta) {
    $filtro = BD_CTRL . '.aux_HistoricoModificacionMeta';
    $sql = "SELECT CONCAT(uv.umv_nombre,' - ',i.ind_nombre) AS ind_nombre,
               tm.pii_id, epg_id, m.mme_concepto, m.mme_id, m.emo_id, m.omo_id, m.tfm_id,
               m.mme_gpo_id, m.mme_ur_id, m.mme_id, tm.pii_id AS pii, enc.epg_id AS epg,
               CONCAT(gpo_id, ur_id, '-', ue_id, '-', prg_id, '-', spr_id, '-', py_id, '-', oa_id) AS actividad,
               oa_nombre, tm.pii_id, enc.epg_id, enc.epg_id AS epg, tm.pii_id AS pii,
               (SELECT mact_modificadoAnterior
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 1 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS ori_trimestre1,
               (SELECT mact_modificadoAnterior
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 2 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS ori_trimestre2,
               (SELECT mact_modificadoAnterior
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 3 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS ori_trimestre3,
               (SELECT mact_modificadoAnterior
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 4 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS ori_trimestre4,
               (SELECT mact_modificado
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 1 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t11,
               (SELECT mact_modificado
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 2 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t22,
               (SELECT mact_modificado
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 3 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t33,
               (SELECT mact_modificado
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 4 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t44,
               i.tiv_id,tv.tiv_nombre,toa_id
        FROM scp_ModificacionMeta m
        INNER JOIN scp_DetalleModificacionMeta d USING (omo_id, mme_gpo_id, mme_ur_id, mme_id)
        INNER JOIN $filtro tm USING (epg_id)
        INNER JOIN enc_Programatico enc USING (epg_id)
        INNER JOIN cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
        JOIN vin_ProductoIntermedioIndicador vpi ON  vpi.pii_id = tm.pii_id
        JOIN cat_Indicador i USING (nim_id, ind_id)
        JOIN cat_UnidadMedidaVariable uv USING (umv_id)
        JOIN cat_TipoValor tv ON tv.tiv_id = i.tiv_id 
        WHERE m.omo_id = :omo_id
        AND m.mme_gpo_id = :gpo_id AND m.mme_ur_id = :ur_id
        AND m.mme_id = :mme_id
        GROUP BY m.mme_id,tm.epg_id,tm.pii_id;
    ";
} else {
    if ($emo_id == 14) {
        $filtro = BD_CTRL . '.aux_HistoricoModificacionMeta';
    } else {
        $filtro = BD_TEMPORAL . '.tem_ModificacionMetaTrimestral';
    }
    $sql = "SELECT CONCAT(uv.umv_nombre,' - ',i.ind_nombre) AS ind_nombre,
               tm.pii_id, epg_id, m.mme_concepto, m.mme_id, m.emo_id, m.omo_id, m.tfm_id,
               m.mme_gpo_id, m.mme_ur_id, m.mme_id, tm.pii_id AS pii, enc.epg_id AS epg,
               CONCAT(gpo_id, ur_id, '-', ue_id, '-', prg_id, '-', spr_id, '-', py_id, '-', oa_id) AS actividad,
               oa_nombre, tm.pii_id, enc.epg_id, enc.epg_id AS epg, tm.pii_id AS pii,
               (SELECT mact_modificado
                FROM " . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 1) AS ori_trimestre1,
               (SELECT mact_modificado
                FROM ' . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 2) AS ori_trimestre2,
               (SELECT mact_modificado
                FROM ' . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 3) AS ori_trimestre3,
               (SELECT mact_modificado
                FROM ' . BD_CTRL . '.cat_MetaActividadTrimestre AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 4) AS ori_trimestre4,
               (SELECT mact_modificado
                FROM ' . BD_TEMPORAL . ".tem_ModificacionMetaTrimestral AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 1 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t11,
               (SELECT mact_modificado
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 2 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t22,
               (SELECT mact_modificado
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 3 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t33,
               (SELECT mact_modificado
                FROM $filtro AS ct
                WHERE ct.pii_id = tm.pii_id AND ct.epg_id = epg AND tri_id = 4 AND omo_id = :omo_id
                AND mme_gpo_id = :gpo_id AND mme_ur_id = :ur_id AND mme_id = :mme_id) AS t44,
               i.tiv_id,tv.tiv_nombre,toa_id
        FROM scp_ModificacionMeta m
        INNER JOIN scp_DetalleModificacionMeta d USING (omo_id, mme_gpo_id, mme_ur_id, mme_id)
        INNER JOIN $filtro tm USING (epg_id)
        INNER JOIN enc_Programatico enc USING (epg_id)
        INNER JOIN cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
        JOIN vin_ProductoIntermedioIndicador vpi ON vpi.pii_id = tm.pii_id
        JOIN cat_Indicador i USING (nim_id, ind_id)
        JOIN cat_UnidadMedidaVariable uv USING (umv_id)
        JOIN cat_TipoValor tv ON tv.tiv_id = i.tiv_id 
        WHERE m.omo_id = :omo_id
        AND m.mme_gpo_id = :gpo_id AND m.mme_ur_id = :ur_id
        AND m.mme_id = :mme_id
        GROUP BY m.mme_id,tm.epg_id,tm.pii_id;
    ";
}
$params = [
    ':gpo_id' => $gpo_id,
    ':ur_id'  => $ur_id,
    ':omo_id' => $omo_id,
    ':mme_id' => $mme_id,
];
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
$datos = $conexion->extraer();
$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP);
$pdf->setOrientation('L');
$pdf->AliasNbPages();

$fill = false;
$pdf->setDatos($datos);
$pdf->AddPage();
$total_a = 0;
$total_r = 0;
$enc_Prog = '';
do {
    $tipomov1 = substr($datos['t11'] = (isset($datos['t11']) ? $datos['t11'] : 0), 0, 1);
    $tipomov2 = substr($datos['t22'] = (isset($datos['t22']) ? $datos['t22'] : 0), 0, 1);
    $tipomov3 = substr($datos['t33'] = (isset($datos['t33']) ? $datos['t33'] : 0), 0, 1);
    $tipomov4 = substr($datos['t44'] = (isset($datos['t44']) ? $datos['t44'] : 0), 0, 1);
    switch ($tipomov1) {
        case '0':
            $tipomov1 = ' ';
            break;
        case '-':
            $tipomov1 = '(Red.)';
            break;
        default:
            $tipomov1 = '(Amp.)';
            break;
    }
    switch ($tipomov2) {
        case '0':
            $tipomov2 = ' ';
            break;
        case '-':
            $tipomov2 = '(Red.)';
            break;
        default:
            $tipomov2 = '(Amp.)';
            break;
    }
    switch ($tipomov3) {
        case '0':
            $tipomov3 = ' ';
            break;
        case '-':
            $tipomov3 = '(Red.)';
            break;
        default:
            $tipomov3 = '(Amp.)';
            break;
    }
    switch ($tipomov4) {
        case '0':
            $tipomov4 = ' ';
            break;
        case '-':
            $tipomov4 = '(Red.)';
            break;
        default:
            $tipomov4 = '(Amp.)';
            break;
    }
    $pdf->SetFont('Arial', '', 6.2);
    $pdf->SetAltoLinea(1.8);
    $pdf->SetWidths([47, 18, 30, 15, 30, 15, 30, 15, 30, 15]);
    $pdf->SetAligns(['L', 'R', 'C', 'R', 'C', 'R', 'C', 'R', 'C', 'R']);
    $indicador = 'INDICADOR';
    $anual = 0;
    $datos['toa_id'] = (isset($datos['toa_id']) ? $datos['toa_id'] : '');
    if ($datos['toa_id'] == 1) {
        if ($datos['tiv_id'] == 1) {
            $anual = $datos['ori_trimestre1'] + $datos['ori_trimestre2'] + $datos['ori_trimestre3'] + $datos['ori_trimestre4'];
            $modificado = $datos['t11'] + $datos['t22'] + $datos['t33'] + $datos['t44'];
        } elseif ($datos['tiv_id'] == 2) {
            $anual = $datos['ori_trimestre4'];
            $modificado = $datos['t44'];
        }
    } else {
        $datos['ori_trimestre1'] = (isset($datos['ori_trimestre1']) ? $datos['ori_trimestre1'] : 0);
        $datos['ori_trimestre2'] = (isset($datos['ori_trimestre2']) ? $datos['ori_trimestre2'] : 0);
        $datos['ori_trimestre3'] = (isset($datos['ori_trimestre3']) ? $datos['ori_trimestre3'] : 0);
        $datos['ori_trimestre4'] = (isset($datos['ori_trimestre4']) ? $datos['ori_trimestre4'] : 0);
        $anual = $datos['ori_trimestre1'] + $datos['ori_trimestre2'] + $datos['ori_trimestre3'] + $datos['ori_trimestre4'];
        $modificado = $datos['t11'] + $datos['t22'] + $datos['t33'] + $datos['t44'];
    }
    $datos['emo_id'] = (isset($datos['emo_id']) ? $datos['emo_id'] : '');
    $datos['actividad'] = (isset($datos['actividad']) ? $datos['actividad'] : '');
    $datos['ind_nombre'] = (isset($datos['ind_nombre']) ? $datos['ind_nombre'] : '');
    switch ($datos['emo_id']) {
        default: //En estado 1,3,6,7,8,9,14
            if ($enc_Prog != $datos['actividad']) {
                $pdf->SetFont('Arial', 'B', 6.2);
                $enc_Prog = $datos['actividad'];
                $pdf->Row([$datos['actividad']]);
                $pdf->Row([utf8Decode($datos['oa_nombre'])]);
                $pdf->SetFont('Arial', '', 6.2);
            }
            $pdf->Row(['']);
            $pdf->SetFont('Arial', 'B', 6.2);
            $pdf->Row([$indicador]);
            $pdf->SetFont('Arial', '', 6.2);
            $pdf->Row([utf8Decode($datos['ind_nombre']),
                number_format($anual, 2),
                $tipomov1,
                number_format($datos['ori_trimestre1'], 2),
                $tipomov2,
                number_format($datos['ori_trimestre2'], 2),
                $tipomov3,
                number_format($datos['ori_trimestre3'], 2),
                $tipomov4,
                number_format($datos['ori_trimestre4'], 2)]);

            break;
    }

    $pdf->SetAltoLinea(1.8);
    $pdf->SetAltoLinea(1.8);
    $pdf->Row([utf8Decode($datos['pe_nombre'] ?? ''),
        number_format($modificado, 2), '',
        number_format($datos['t11'], 2), '',
        number_format($datos['t22'], 2), '',
        number_format($datos['t33'], 2), '',
        number_format($datos['t44'], 2)]);

    $pdf->SetAltoLinea(1);

    switch ($datos['emo_id']) {
        default: //1,3,6,7,8,9,14
            if ($datos['toa_id'] == 1) {
                $anualModificado = 0;
                if ($datos['tiv_id'] == 1) {
                    $anualModificado = $anual + $modificado;
                } elseif ($datos['tiv_id'] == 2) {
                    $anualModificado = $datos['ori_trimestre4'] + $datos['t44'];
                }
                $pdf->Row(['',
                    number_format($anualModificado, 2), '',
                    number_format($datos['ori_trimestre1'] + $datos['t11'], 2), '',
                    number_format($datos['ori_trimestre2'] + $datos['t22'], 2), '',
                    number_format($datos['ori_trimestre3'] + $datos['t33'], 2), '',
                    number_format($datos['ori_trimestre4'] + $datos['t44'], 2),
                ]);
            } else {
                $pdf->Row(['',
                    number_format($anual + $modificado, 2), '',
                    number_format($datos['ori_trimestre1'] + $datos['t11'], 2), '',
                    number_format($datos['ori_trimestre2'] + $datos['t22'], 2), '',
                    number_format($datos['ori_trimestre3'] + $datos['t33'], 2), '',
                    number_format($datos['ori_trimestre4'] + $datos['t44'], 2),
                ]);
            }
            break;
    }
    $xtm = $pdf->getX();
    $ytm = $pdf->getY();
    $linea = (((isset($datos['mod_Subsecretaria']) == 2) || (isset($datos['omo_id']) == 'E' || isset($datos['omo_id'])
        == 'F')) ? 285 : 272);

    $pdf->Ln(1);
    if ($pdf->getY() > 160) {
        $pdf->addPage();
    }
    if ($fill) {
        $fill = false;
    }

} while ($datos = $conexion->extraer());

$pdf->Line($xtm, $ytm, $linea, $ytm);
$datos = $pdf->getDatos();

$pdf->SetAltoLinea(2.5);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetWidths([300]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
$pdf->Row([utf8Decode($tipom ?? '')]);

$datos = $pdf->getDatos();
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetWidths([200]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
$datos['mme_concepto'] = (isset($datos['mme_concepto']) ? $datos['mme_concepto'] : '');
$pdf->Row([utf8Decode('JUSTIFICACIÓN:') . '  ' . utf8Decode($datos['mme_concepto'])]);

$pdf->Output();
