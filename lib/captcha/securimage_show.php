<?php
// Respuesta binaria (PNG): no mezclar salida de errores con la imagen.
define('SEFIP_CAPTCHA_IMAGE', true);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ERROR);

require_once dirname(__FILE__) . '/securimage.php';

$img = new securimage();

// Change some settings
$img->image_width = 250;
$img->image_height = 70;
$img->perturbation =.5;
$img->image_bg_color = new Securimage_Color("#666666");
$img->text_color = new Securimage_Color("#333333");
$img->use_transparent_text = false;
$img->num_lines = 0;
$img->draw_lines_over_text = false;
$img->line_color = new Securimage_Color("#e8b820");
$img->use_wordlist = true;
$img->show(__DIR__ . "/fondo/bg4.jpg");
