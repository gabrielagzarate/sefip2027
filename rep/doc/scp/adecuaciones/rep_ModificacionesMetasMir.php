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

        $this->setRouteImg2('escudo');
        $this->Image($this->getRouteImg(), $ix + 230, $iy - 1, '16', '26', 'JPG', '');

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
            utf8Decode('SOLICITUD DE ADECUACIÓN DE METAS MIR NIVEL COMPONENTE'),
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
        if ($datos != '') {
            # code...

            $linea = ((($datos['mme_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E'
                || $datos['omo_id'] == 'F')) ? 285 : 272);
            $this->Line($xtm, $ytm, $linea, $ytm);
            $this->Ln(.7);
            $this->SetWidths([47, 20, 24, 24, 24, 24, 24, 24, 24, 24]);
            $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);
            $this->SetAltoLinea(2.5);
            $this->Row([
                'PROGRAMA / COMPONENTE',
                'TIPO MOVIMIENTO',
                'NUMERADOR TRIMESTRE 1',
                'DENOMINADOR TRIMESTRE 1',
                'NUMERADOR TRIMESTRE 2',
                'DENOMINADOR TRIMESTRE 2',
                'NUMERADOR TRIMESTRE 3',
                'DENOMINADOR TRIMESTRE 3',
                'NUMERADOR TRIMESTRE 4',
                'DENOMINADOR TRIMESTRE 4',
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
        if ($lastPage && $datos != '') {
            $emo = '';
            switch ($_SESSION[_S]['nvj_id']) {
                case 1: //UR
                case 2: //UE
                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mme_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mme_ur_id']);
                    $objUnidadResponsable->select();
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
                                            $sql = "INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                                mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                                mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                                mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                                VALUES ('" . $datos['omo_id'] . "', '" . $objUnidadResponsable->getGpo_id() .
                                                    "','" . $objUnidadResponsable->getUr_id() . "', '" . $datos['mme_id'] . "', '" .
                                                    $cargoUsuario . "', '" .
                                                    $elabora . "', '" .
                                                    $cargoAdmvo . "', '" .
                                                    $revisorAdmvo . "', '" .
                                                    $cargoTitular . "', '" .
                                                    $titular . "',
                                                    '', '',
                                                    '', '',
                                                    '', '',
                                                    '', '',
                                                " . $_SESSION[_S]['usr_id'] . ")
                                                ON DUPLICATE KEY UPDATE
                                                mme_cargo1='" . $cargoUsuario . "',
                                                mme_firma1='" . $elabora . "',
                                                mme_cargo2='" . $cargoAdmvo . "',
                                                mme_firma2='" . $revisorAdmvo . "',
                                                mme_cargo3='" . $cargoTitular . "',
                                                mme_firma3='" . $titular . "';";

                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar();

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mme_cargo1,mme_firma1,mme_cargo2,mme_firma2,mme_cargo3,mme_firma3
                                                FROM scp_ModificacionMetaFirma
                                                WHERE omo_id= ? and mme_gpo_id= ?
                                                and mme_ur_id= ?
                                                and mme_id= ?;
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
                                            $sql = "INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                                mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                                mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                                mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                                VALUES ('" . $datos['omo_id'] . "', '" . $objUnidadResponsable->getGpo_id() .
                                                    "','" . $objUnidadResponsable->getUr_id() . "', '" . $datos['mme_id'] . "',
                                                    '', '','" .
                                                    $cargoAdmvo . "', '" .
                                                    $revisorAdmvo . "', '" .
                                                    $cargoTitular . "', '" .
                                                    $titular . "',
                                                    '', '',
                                                    '', '',
                                                    '', '',
                                                    '', '',
                                                " . $_SESSION[_S]['usr_id'] . ")
                                                ON DUPLICATE KEY UPDATE
                                                mme_cargo2='" . $cargoAdmvo . "',
                                                mme_firma2='" . $revisorAdmvo . "',
                                                mme_cargo3='" . $cargoTitular . "',
                                                mme_firma3='" . $titular . "';";
                                            $firmasReportes = new AccesoDatos();
                                            $firmasReportes->conectar();
                                            $firmasReportes->setSql($sql);
                                            $firmasReportes->consultar();

                                            //Obtiene las firmas
                                            $sql = '
                                                SELECT mme_cargo2,mme_firma2,mme_cargo3,mme_firma3
                                                FROM scp_ModificacionMetaFirma
                                                WHERE omo_id= ? and mme_gpo_id= ?
                                                and mme_ur_id= ?
                                                and mme_id= ?;
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
                                        $sql = "INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                            mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                            mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                            mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                            VALUES ('" . $datos['omo_id'] . "', '" . $objUnidadResponsable->getGpo_id() .
                                                "','" . $objUnidadResponsable->getUr_id() . "', '" . $datos['mme_id'] . "', '" .
                                                $cargoAdmvo . "', '" .
                                                $jefeAdmvo . "', '" .
                                                $cargoTitular . "', '" .
                                                $titular . "', '" .
                                                $cargoTitularRh . "', '" .
                                                $titularRh . "',
                                                '','',
                                                '','',
                                                '','',
                                                '','',
                                            " . $_SESSION[_S]['usr_id'] . ")
                                            ON DUPLICATE KEY UPDATE
                                            mme_cargo1='" . $cargoAdmvo . "',
                                            mme_firma1='" . $jefeAdmvo . "',
                                            mme_cargo2='" . $cargoTitular . "',
                                            mme_firma2='" . $titular . "',
                                            mme_cargo3='" . $cargoTitularRh . "',
                                            mme_firma3='" . $titularRh . "';";

                                        $firmasReportes = new AccesoDatos();
                                        $firmasReportes->conectar();
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar();

                                        //Obtiene las firmas
                                        $sql = '
                                            SELECT mme_cargo1,mme_firma1,mme_cargo2,mme_firma2,mme_cargo3,mme_firma3
                                            FROM scp_ModificacionMetaFirma
                                            WHERE omo_id= ? and mme_gpo_id= ?
                                            and mme_ur_id= ?
                                            and mme_id= ?;
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
                                        $sql = "INSERT INTO scp_ModificacionMetaFirma (omo_id, mme_gpo_id, mme_ur_id,
                                            mme_id,  mme_cargo1, mme_firma1, mme_cargo2, mme_firma2, mme_cargo3, mme_firma3,
                                            mme_cargo4, mme_firma4, mme_cargo5, mme_firma5, mme_cargo6,
                                            mme_firma6,mme_cargo7,mme_firma7,usr_id)
                                            VALUES ('" . $datos['omo_id'] . "', '" . $objUnidadResponsable->getGpo_id() .
                                                "','" . $objUnidadResponsable->getUr_id() . "', '" . $datos['mme_id'] . "', '" .
                                                $puestoAdmvo . "', '" .
                                                $jefeAdmvo . "', '" .
                                                $puestoTitular . "', '" .
                                                $titular . "',
                                                '','',
                                                '','',
                                                '','',
                                                '','',
                                                '','',
                                            " . $_SESSION[_S]['usr_id'] . ")
                                            ON DUPLICATE KEY UPDATE
                                            mme_cargo1='" . $puestoAdmvo . "',
                                            mme_firma1='" . $jefeAdmvo . "',
                                            mme_cargo2='" . $puestoTitular . "',
                                            mme_firma2='" . $titular . "';";
                                        $firmasReportes = new AccesoDatos();
                                        $firmasReportes->conectar();
                                        $firmasReportes->setSql($sql);
                                        $firmasReportes->consultar();

                                        //Obtiene las firmas
                                        $sql = '
                                            SELECT mme_cargo1,mme_firma1,mme_cargo2,mme_firma2
                                            FROM scp_ModificacionMetaFirma
                                            WHERE omo_id= ? and mme_gpo_id= ? 
                                            and mme_ur_id= ?
                                            and mme_id= ?;
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
                                        $this->Cell(
                                            100.5,
                                            2,
                                            utf8Decode($datosFirma['mme_cargo1']),
                                            0,
                                            0,
                                            'C'
                                        );
                                        $this->SetX(165);
                                        $this->Cell(
                                            100.5,
                                            2,
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
                    $gpo_id = (isset($_POST['mme_gpo_id']) && $_POST['mme_gpo_id']
                        != '') ? $_POST['mme_gpo_id'] : '';
                    $ur_id = (isset($_POST['mme_ur_id']) && $_POST['mme_ur_id'] != '')
                    ? $_POST['mme_ur_id'] : '';
                    $mme_id = (isset($_POST['mme_id']) && $_POST['mme_id'] != '')
                    ? $_POST['mme_id'] : '';
                    $tipoMovimiento = (isset($_POST['tipoMovimiento']) && $_POST['tipoMovimiento']
                        != '') ?
                    $_POST['tipoMovimiento'] : '';

                    $cargo_titular = '';
                    $nom_titular = '';
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
                            SELECT mme_cargo3,mme_firma3,mme_cargo4,mme_firma4,mme_cargo5,mme_firma5,mme_cargo6,mme_firma6,
                            mme_cargo7,mme_firma7
                            FROM scp_ModificacionMetaFirma
                            WHERE omo_id= ? and mme_gpo_id= ?
                            and mme_ur_id= ?
                            and mme_id= ?;
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
                            $nom_titular = $datosFirma['mme_firma3'];
                            $cargo_titular = $datosFirma['mme_cargo3'];
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

                    switch ($datos['emo_id']) {
                        case 6:
                        case 11:
                        case 17:
                        case 8:
                        case 13:

                            $this->SetXY(30, -20);
                            $this->SetFont('Arial', 'B', 7);
                            $this->MultiAlignCell(
                                50,
                                3,
                                utf8Decode($cargo_analista),
                                0,
                                1,
                                'C',
                                0
                            );

                            $this->SetXY(90, -20);
                            $this->SetFont('Arial', 'B', 7);
                            $this->MultiAlignCell(
                                50,
                                3,
                                utf8Decode($cargo_titular),
                                0,
                                1,
                                'C',
                                0
                            );

                            $this->SetXY(150, -20);
                            $this->MultiAlignCell(
                                50,
                                2,
                                utf8Decode($cargo_departamento),
                                0,
                                1,
                                'C',
                                0
                            );

                            $this->SetXY(217, -20);
                            $this->MultiAlignCell(
                                50,
                                2,
                                utf8Decode($cargo_unidad),
                                0,
                                1,
                                'C',
                                0
                            );

                            $this->SetFont('Arial', '', 7);
                            $this->SetXY(30, -13);
                            $this->MultiAlignCell(
                                63.5,
                                4,
                                utf8Decode($nom_analista),
                                0,
                                'C'
                            );

                            $this->SetFont('Arial', '', 7);
                            $this->SetXY(90, -13);
                            $this->MultiAlignCell(
                                63.5,
                                4,
                                utf8Decode($nom_titular),
                                0,
                                'C'
                            );

                            $this->SetXY(150, -13);
                            $this->MultiAlignCell(
                                100.5,
                                4,
                                utf8Decode($nom_departamento),
                                0,
                                'C'
                            );

                            $this->SetXY(215, -13);
                            $this->MultiAlignCell(
                                100.5,
                                4,
                                utf8Decode($nom_unidad),
                                0,
                                'C'
                            );
                            break;
                    }
                    break;
            }
        } else {
            if ($datos != '') {
                $this->SetY(-20);
                $this->SetFont('Arial', 'B', 6);
                $this->Cell(10, 2, utf8Decode('CONTINUA...'), 0, 1, 'L');

                switch ($_SESSION[_S]['nvj_id']) {
                    case 1: case 2: //Ur y Ue
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
$movimiento = '';

$trimsql = '
    SELECT gpo_id, ur_id, MAX(tri_id) AS trimestre_abierto, etr_id
    FROM mir_TrimestreUr
    WHERE gpo_id = ' . $gpo_id . '
    AND ur_id = ' . $ur_id . '
    AND nim_id IN (1, 2, 3) 
    AND etr_id = "A"
    GROUP BY gpo_id, ur_id;
';
$trim = new AccesoDatos();
$trim->conectar();
$trim->setSql($trimsql);
$trim->consultar();
$trim->desconectar();
$tri_id = $trim->extraer();

$sql = '
    SELECT m.omo_id, m.emo_id, m.mme_gpo_id, m.mme_ur_id, m.mme_id, prgs_id, sprs_id, d.epfi_id, ind_id, pdf_id, 
    CONCAT(prgs_id," - ",prg_nombre) programa, h.mcot_numeradorT1, d.mcot_numeradorT2,h.mcot_numeradorT3,d.mcot_numeradorT4,
    h.mcot_denominadorT1, d.mcot_denominadorT2,h.mcot_denominadorT3,d.mcot_denominadorT4, d.tmf_id, m.tfm_id,
    CONCAT(sprs_id," - ",sprs_nombre) subprograma, CONCAT(ind_id," - ",ind_nombre) indicador, CONCAT(mme_gpo_id,mme_ur_id) ur,
    m.mme_concepto,m.mme_Subsecretaria
    FROM ' . BD_CTRL . '.scp_ModificacionMeta m
    INNER JOIN ' . BD_CTRL . '.scp_DetalleModificacionMetaComponente d USING(omo_id,mme_gpo_id,mme_ur_id,mme_id)
    INNER JOIN ' . BD_CTRL . '.aux_HistoricoModificacionMetaComponente h USING(gpo_id, ur_id,prgs_id, sprs_id,epfi_id)
    INNER JOIN ' . BD_CTRL . '.enc_ProductoFinalIndicador pi USING (epfi_id)
    INNER JOIN ' . BD_CTRL . '.cat_Programa USING(gpo_id, ur_id)
    INNER JOIN ' . BD_CTRL . '.cat_Subprogramas USING (prgs_id, sprs_id, pdf_id)
    INNER JOIN ' . BD_CTRL . '.cat_Indicador USING(nim_id, ind_id)
    WHERE omo_id= ?
    AND mme_gpo_id= ?
    AND mme_ur_id= ?
    AND mme_id= ?
    AND d.tri_id = ?
    AND prgs_id=prg_id
    GROUP BY prg_id, sprs_id, epfi_id;
';

$params = [
    $omo_id,
    $gpo_id,
    $ur_id,
    $mme_id,
    $tri_id['trimestre_abierto'],
];

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
$registros = $conexion->getNumeroRegistros();

if ($registros < 0) {
    $sql = '
    SELECT *
    FROM ' . BD_CTRL . '.scp_ModificacionMeta m
    WHERE omo_id="' . $omo_id . '" 
    AND mme_gpo_id=' . $gpo_id . ' 
    AND mme_ur_id=' . $ur_id . '
    AND mme_id=' . $mme_id . ' ;
';

    $params = [
        $omo_id,
        $gpo_id,
        $ur_id,
        $mme_id,
    ];

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();
}

$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP);
$pdf->setOrientation('L');
$pdf->AliasNbPages();

$fill = false;
$datos = $conexion->extraer();
$pdf->setDatos($datos);
$pdf->AddPage();
$total_a = 0;
$total_r = 0;
$enc_Prog = '';

do {

    if ($datos != '') {
        # code...

        if ($datos['tmf_id'] == 'R') {
            # code...
            $movimiento = 'Reducción';
        } elseif ($datos['tmf_id'] == 'A') {
            $movimiento = 'Ampliación';
        }

        $pdf->SetFont('Arial', '', 6.2);
        $pdf->SetAltoLinea(1.8);
        $pdf->SetWidths([47, 20, 24, 24, 24, 24, 24, 24, 24, 24]);
        $pdf->SetAligns(['L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);

        $pdf->Row(['']);
        $pdf->SetFont('Arial', 'B', 6.2);
        $pdf->Row([utf8Decode($datos['programa'] . ' ' . $datos['subprograma'])]);
        $pdf->SetFont('Arial', '', 6.2);
        $pdf->Row(
            [
                utf8Decode($datos['indicador']),
                utf8Decode($movimiento),
                number_format($datos['mcot_numeradorT1'], 2),
                number_format($datos['mcot_denominadorT1'], 2),
                number_format($datos['mcot_numeradorT2'], 2),
                number_format($datos['mcot_denominadorT2'], 2),
                number_format($datos['mcot_numeradorT3'], 2),
                number_format($datos['mcot_denominadorT3'], 2),
                number_format($datos['mcot_numeradorT4'], 2),
                number_format($datos['mcot_denominadorT4'], 2),
            ]
        );

        $pdf->SetAltoLinea(1);
        $linea = ((($datos['mme_Subsecretaria'] == 2) || ($datos['omo_id'] == 'E' || $datos['omo_id']
            == 'F')) ? 285 : 272);
    }
    $xtm = $pdf->getX();
    $ytm = $pdf->getY();


    $pdf->Ln(1);
    if ($pdf->getY() > 160) {
        $pdf->addPage();
    }
    if ($fill) {
        $fill = false;
    }

} while ($datos = $conexion->extraer());
$linea = 272;
$pdf->Line($xtm, $ytm, $linea, $ytm);
$datos = $pdf->getDatos();

$pdf->SetAltoLinea(2.5);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetWidths([300]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);


$datos = $pdf->getDatos();
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetWidths([200]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
if ($datos != '') {
    # code...

    $pdf->Row([utf8Decode('JUSTIFICACIÓN:') . '  ' . utf8Decode($datos['mme_concepto'])]);
}
$pdf->Output();
