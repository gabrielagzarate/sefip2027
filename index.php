<?php
require_once dirname(__FILE__) . "/lib/constantes.php";
require_once dirname(__FILE__) . "/lib/helpers.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!sesionActiva() && isset($_SESSION[_S])) {
    unset($_SESSION[_S]);
}
header('Cache-Control: max-age=31536000');
header('Pragma: max-age=31536000');
header('Expires: Sat, 4 Mar 2023 05:00:00 GMT');
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="es">
<!--<![endif]-->

<head>
    <title>Sistema Estatal de Finanzas Públicas de Oaxaca</title>
    <meta charset="utf-8" />
    <meta http-equiv="Cache-control" content="public">
    <meta http-equiv="pragma" content="max-age=31536000">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=6.0, user-scalable=yes" name="viewport" />
    <meta content="Sistema Estatal de las Finanzas públicas del Estado de Oaxaca" name="description" />
    <meta property="og:image" content="https://sefip.finanzasoaxaca.gob.mx/<?php echo (int) APP_EJERCICIO; ?>/img/sefip.png" />
    <meta content="Gobierno del estado de Oaxaca" name="author" />
    <?php if ($_SERVER['SERVER_ADDR'] == '172.17.90.88') {?>
        <meta name="robots" content="index, follow">
        <meta name="googlebot" content="index, follow" />
        <meta name="googlebot-news" content="index, follow">
    <?php } else {?>
        <meta name="robots" content="noindex, nofollow, noarchive" />
        <meta name="googlebot" content="noindex, nofollow, noarchive" />
        <meta name="googlebot-news" content="noindex, nofollow, noarchive">
    <?php }?>

    <link rel="apple-touch-icon" sizes="57x57" href="apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <!-- ================== BEGIN BASE CSS STYLE ================== -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="assets/css/vendor.min.css" rel="stylesheet" />
    <link href="assets/css/default/app.min.css" rel="stylesheet" />
    <!-- ================== Estilos SEFIP ================== -->
    <link id="login_2027" rel="stylesheet" href="css/login_sf_2027.css?v=<?php echo filemtime(__DIR__ . '/css/login_sf_2027.css'); ?>">
    <link href="css/estilos-sf-2027.min.css?v=<?php echo filemtime(__DIR__ . '/css/estilos-sf-2027.min.css'); ?>" rel="stylesheet" />
    <?php if (!isset($_SESSION[_S]['usr_id'])) {?>
        <link id="login_css" rel="stylesheet" href="css/login_sf_2027.min.css?v=<?php echo filemtime(__DIR__ . '/css/login_sf_2027.min.css'); ?>">
    <?php }?>

    <link rel="stylesheet" href="css/styles.css">
    <link href="css/jquery.treeview.css" rel="stylesheet" />
    <link href="templete/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.standalone.css" rel="stylesheet">
    <link href="css/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/notification.css">
    <link rel="stylesheet" href="css/ayuda.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/estilo-reportes-scp.css">
    <link href="css/slick.grid.css" rel="stylesheet">
    <link href="assets/css/alertify.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" integrity="sha512-mR/b5Y7FRsKqrYZou7uysnOdCIJib/7r5QeJMFvLNHNhtye3xJp1TdJVPLtetkukFn227nKpXD9OjUc09lx97Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="css/usuario-estilo.css" rel="stylesheet">

    <link href="mode/codemirror.css" rel="stylesheet">
    <link href="mode/twilight.css" rel="stylesheet">
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <script src="assets/js/alertify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js" integrity="sha512-FHZVRMUW9FsXobt+ONiix6Z0tIkxvQfxtCSirkKc5Sb4TKHmqq1dZa8DphF0XqKb3ldLu/wgMa8mT6uXiLlRlw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
</head>

<body>
    <div id="cuerpoSinpres">
    </div>
    <script>
        window.serverData = { environment : 'dev'};
        <?php if ($_SERVER['SERVER_ADDR'] == '172.17.90.88') {?>
            window.serverData.environment = 'production';
        <?php } ?>
        $(function() {
            <?php if (!sesionActiva() || empty($_SESSION[_S]['mod_id'])) {?>
                comunica("srv/Controlador.php", {
                    "metodo": "login"
                });
            <?php } else {?>
                comunica("srv/Controlador.php", {
                    metodo: "sinpres"
                });
            <?php }?>
        });
    </script>
</body>
<div class="footer-creditos">
    <div class="footer">
        <img src="images/TECNOLOGIAS.png" alt="">
        <p>Sistema Desarrollado por la Agencia de Tecnologías e Innovación Digital</p>
    </div>
</div>

</html>
<script type="text/javascript" src="js/libreriasSefip.min.js"></script>
<script type="text/javascript" src="js/mousetrap.min.js"></script>
<script type="text/javascript" src="js/jquery-tmpl-1.0.js"></script>
<script type="text/javascript" src="templete/assets/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script src="js/jquery-migrate.min.js"></script>

<script type="text/javascript" src="templete/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="templete/assets/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="templete/assets/plugins/jquery-cookie/jquery.cookie.js"></script>

<script type="text/javascript" src="js/jquery.treeview.js"></script>
<script type="text/javascript" src="js/axios.min.js"></script>
<script type="text/javascript" src="js/jQuery.jTableCheckbox.js"></script>
<script type="text/javascript" src="js/jquery.tablegroup.js"></script>
<script type="text/javascript" src="js/zabuto_calendar_min.js"></script>
<script type="text/javascript" src="js/number.js"></script>
<script type="text/javascript" src="js/charts/highcharts.src.js"></script>
<script type="text/javascript" src="js/charts/highcharts-3d.src.js"></script>
<script type="text/javascript" src="js/charts/modules/data.js"></script>
<script type="text/javascript" src="js/charts/modules/drilldown.js"></script>
<script type="text/javascript" src="js/jquery.fileDownload.js"></script>
<script type="text/javascript" charset="utf8" src="js/datatables.min.js"></script>
<script type="text/javascript" charset="utf8" src="js/ColReorderWithResize.js"></script>

<script type="text/javascript" charset="utf8" src="js/jquery.filtertable.min.js"></script>

<script type="text/javascript" src="js/spin.min.js"></script>
<script type="text/javascript" src="js/ladda.min.js"></script>
<script type="text/javascript" src="js/jquery.event.drag-2.3.0.js"></script>

<script type="text/javascript" src="js/slickGrid/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.core.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.cellrangedecorator.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.cellrangeselector.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.cellselectionmodel.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.grid.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.groupitemmetadataprovider.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.dataview.js"></script>
<script type="text/javascript" src="js/slickGrid/slick.checkboxselectcolumn.js"></script>
<script type="text/javascript" src="mode/codemirror.js"></script>
<script type="text/javascript" src="mode/sql/sql.js"></script>