<?php
session_start();
require_once '../lib/constantes.php';
define('FOLDER', 'SUIDJURKLDI');
function getNumdescargas($idFile)
{
    $path = './' . $idFile . '.txt';
    if (!file_exists($path)) {
        writeFile($path, 1);
        return 1;
    }
    $file = fopen($path, 'r');
    $value = intval(fgets($file));
    fclose($file);
    $value++;
    writeFile($path, $value);
    return $value;
}

function writeFile($path, $value)
{
    $file = fopen($path, "w+");
    fwrite($file, $value);
    fclose($file);
    return true;
}

function sendFile($file)
{
    $filePath = './' . FOLDER . '/' . $file;
    $contentType = mime_content_type($filePath);
    header("Content-Description: File Transfer");
    header("Content-type: {$contentType}");
    header("Content-Disposition: attachment; filename={$file}");
    header("Content-Length: " . filesize($filePath));
    header('Pragma: public');
    header("Expires: 0");
    readfile($filePath);
}

$error = null;
$file = $_GET['archivo'];

if (!isset($_SESSION[_S]['usr_id'])) {
    $error = "Debe iniciar sesión";
} else if (!isset($_GET['archivo'])) {
    $error = "Parametro inv&aacute;lido";
} else if (!file_exists("./SUIDJURKLDI/$file")) {
    $error = "El recurso no fue encontrado";
} else {
    $descargas = getNumdescargas($file);
    if ($descargas > 5) {
        $error = '<h1>l&iacute;mite de descargas alcanzado</h1>';
    }
}

if (!$error) {
    sendFile($file);
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SEFIP:Sistema Estatal de las Finanzas Públicas de Oaxaca</title>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
                margin-top:1%;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .code {
                border-right: 2px solid;
                font-size: 26px;
                padding: 0 15px 0 15px;
                text-align: center;
            }

            .message {
                font-size: 18px;
                text-align: center;
            }
        </style>
    </head>
    <body style="padding-top: 10% !important;">
        <div class="flex-center position-ref full-width">
            <img src="https://sefip.finanzasoaxaca.gob.mx/<?php echo (int) APP_EJERCICIO; ?>/img/sefip_small.png" alt="">
        </div>
        <div class="flex-center position-ref full-width code">
                SEFIP
            </div>
            <div class="flex-center position-ref full-width code">
                [<?php echo $error; ?>]
            </div>
            <div class="message code" style="padding: 10px;">
                Descargas
            </div>
</body>
</html>