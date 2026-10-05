<?php
//función que calcula el peso de un archivo en excel en base al número de líneas
//el peso por defecto de la columna es asuminedo que la columna tenga un string de 500 caracteres(cambio de libreria) y cada caracter pesa 2 bytes
function calcularPeso($lineas, $columnas = 4, $pesoColumna = 1000)
{
    //los tamaños son en bytes
    $tamanoHeader = 5000;
    $tamanoFooter = 5000;
    $peso_aprox_linea = $columnas * $pesoColumna;

    $holgura = 15; //IMAGEN

    //se convierte a MB
    $peso = ($tamanoHeader + $tamanoFooter + ($lineas * $peso_aprox_linea)) / 1048576;

    //se regresa el peso en Mb
    return ceil($peso + $holgura);
}
