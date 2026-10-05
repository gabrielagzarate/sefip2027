<?php
require_once dirname(__FILE__) . '/sql_analiticoPresupuesto6C.php';
require_once dirname(__FILE__) . '/lib_nivelAnaliticoPresupuesto6C.php';
require_once dirname(__FILE__) . '/../../calculaPeso.php';

function lib_analiticoPresupuesto6C(
    $ent_id,
    $msc_trimestre,
    $consolidado = false,
    $ent_idC = '',
    $sec_idC = '',
    $gpo_idC = '',
    $con_idC = '',
    $noAplica = false
) {
    //$ejercicioAnterior = sql_analiticoPresupuesto6C($ent_id, $msc_trimestre, 2018, $consolidado, $ent_idC, $sec_idC, $gpo_idC, $con_idC, $noAplica, array());
    $analitico6C = sql_analiticoPresupuesto6C($ent_id, $msc_trimestre, 2022, $consolidado, $ent_idC, $sec_idC, $gpo_idC, $con_idC, $noAplica, array("nivel" => 3));
    //echo "<pre>";print_r($analitico6C);echo "</pre>";die();
    $datos = lib_nivelAnaliticoPresupuesto6C($analitico6C, 3);
    //echo "<pre>";print_r($datos);echo "</pre>";die();

    //echo '<pre>';print_r($datos);echo '</pre>';die();

    return isset($datos) ? $datos : FALSE;
    /*
     * al final se retorna un array que contiene toda la informacion de cada nivel solicitado
     * esto con la finalidad de que cualquier reporte utilice un unico proceso y que no se involucren en la dificultad del proceso
     * consultas,
     * la mejor opcion fue solicitar todos los registros que cumplieran las condiciones de busqueda, agrupando a nivel de registro
     * y en la consulta externa, agrupar por el nivel mas alto solicitado y realizar los join
     * php
     * el segundo paso consiste en mezclar los registros obtenidos de las dos consultas(iniciales y del periodo)
     * y el tercer paso en agrupar los montos de acuerdo a los niveles solicitados
     * se separaron estos procesos ya que se disparaba la cantidad de condicionantes al proceso por cada nivel
     * a si vez que resulto mas sencillo y sobre todo rapido, y se tiene mas claro cada parte del proceso
     * faltante
     */
}
