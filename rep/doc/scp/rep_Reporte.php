<?php

$pathInPieces = explode(DIRECTORY_SEPARATOR, __FILE__);
require_once('../../../lib/constantes.php');

/*
COLABORADORES.- SOLEDAD LÓPEZ VELASCO
REPORTEADOR GENERAL.   Se le pasa una consulta y la imprime
Opcionalmente se le pueden pasar parametros:
orientacion     => P : Portraid (default)     L : Landscape
papel         =>  letter (default) (puede ser cualquiera válido de fPdf
fnt             => Arial (defaul) (puede ser cualquiera válida)
size         => Tamaño de la fuente (10 default)
encabLin1     => Primera Línea del Reporte (default "SINPRES")
encabLin2     => Primera Línea del Reporte (default vacia)
encabLin3     => Primera Línea del Reporte (default vacía)
sql             => un identificador que permita armar la consulta
NO PASAMOS LA CONSULTA EN SI, YA QUE SE LLAMA EN CODIGO JAVASCRIPT, Y NO QUEREMOS DARLE A NADIE LA POSIBILIDAD DE
QUE ESCRIBA SU PROPIA CONSULTA

dentro del array $attr puden pasarse tambien como array:
tableWidths     para el ancho especifico de cada columna del encabezado ejemplo : 'tablewidths'=>array(55, 500)
alineaCols      para la alineacion de cada columna (encabezado y datos) ejemplo : 'alineaCols'=>array('L', 'C')
 */
session_start();

$bolParaExcel = ((isset($_POST['tipo']) && $_POST['tipo'] == 'xls') ? true : false);

if ($bolParaExcel) {
    require_once dirname(__FILE__) . '/../../lib/query2Excel.php';
} else {
    require_once dirname(__FILE__) . '/../../lib/mysql_report.php';
}
require_once dirname(__FILE__) . '/../../../lib/funcionesGenericas.php';

// REPORTEADOR GENERICO A PDF / EXCEL
// Asignamos valores por default, si no pasan los parámetros
$orientacion = (isset($_POST['orientacion']) ? $_POST['orientacion'] : 'P');
$papel = (isset($_POST['papel']) ? $_POST['papel'] : 'letter');
$fnt = (isset($_POST['fnt']) ? $_POST['fnt'] : 'Arial');
$size = (isset($_POST['size']) ? $_POST['size'] : 10);
$encabLin1 = (isset($_POST['encabLin1']) ? $_POST['encabLin1'] : 'SEFIP');
$encabLin2 = (isset($_POST['encabLin2']) ? $_POST['encabLin2'] : '');
$encabLin3 = (isset($_POST['encabLin3']) ? $_POST['encabLin3'] : '');
$sql = (isset($_POST['sql']) ? $_POST['sql'] : '');
$ejercicio = (isset($_POST['ejercicio']) ? $_POST['ejercicio'] : $_SESSION[_S]['ejercicio']);
$filtro = (isset($_POST['buscar']) ? $_POST['buscar'] : '');
$filtroUE = (isset($_POST['filtroUE']) ? $_POST['filtroUE'] : '');
$anchoColumnas = [];
$alignColumnas = [];

// Para el catalogo maestro
$mayor = (isset($_POST['mayor']) ? $_POST['mayor'] : '');
$desde = (isset($_POST['desde']) ? $_POST['desde'] : '');
$hasta = (isset($_POST['hasta']) ? $_POST['hasta'] : '');

$where = '';
$bolFiltroPorTexto = false;
if ($filtro != '') {
    $bolFiltroPorTexto = (strpos('123456789', substr($filtro, 0, 1)) === false);
    // Si viene la palabrita que identifica al catalogo, son de la CONAC y ajustamos el WHERE
    if (strpos('ger.cgp.rub.cgcu.cgsc', $sql) !== false) {
        if ($bolFiltroPorTexto) {
            $where = 'WHERE ' . $sql . '_nombre LIKE "%' . $filtro . '%"';
        }
    }
    // El catalogo maestro solo acepta filtro por nombre, por eso no filtramos nada aqui
}

// Consultas predefinidas, para que no esté el SELECT en la pagina ... y puedan manipularlo para obtener otra información
switch ($sql) {
    // Catálogo de Unidades Responsables
    case 'urs':
        // Si está un filtro activo, lo aplicamos también en el reporte
        $where = '';
        if ($filtro != '') {
            $where = 'WHERE (CONCAT(gpo_id, ur_id) LIKE "%' . $filtro . '%") OR (ur_nombre LIKE "%' . $filtro . '%")';
        }
        $sql = 'SELECT CONCAT(gpo_id, ur_id) AS UR, ur_nombre AS NOMBRE FROM cat_UnidadResponsable ' . $where . ' ORDER BY gpo_id, ur_id;';

        if ($bolParaExcel) {
            // El ancho de cada columna, en valores normales que usaríamos para ANCHO - COLUMNA dentro de excel
            $anchoColumnas = [20, 80];
        } else {
            // El ancho de cada columna, en porcentaje (la suma de todas las columnas debe ser máximo 100)
            $anchoColumnas = [20, 80];
        }
        break;

        // DE LA PANTALLA DE CONSULTA DE CATALOGOS
        // De la Clasificación Administrativa
    case 'ca_R': // Ramo
        // Le ponemos un espacio al número con CONCAT para que NO lo alinea a la derecha
        $sql = 'SELECT CONCAT(ram_id, " ") AS RAMO, ram_nombre AS NOMBRE FROM cat_Ramo ORDER BY ram_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80] : [10, 90]);
        break;

    case 'ca_G': // Grupo
        $sql = 'SELECT CONCAT(gpo_id, " ") AS GRUPO, gpo_nombre AS NOMBRE, CONCAT(ram_id, " ") AS RAMO FROM cat_Grupo ORDER BY gpo_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80, 15] : [10, 80, 10]);
        break;

    case 'ca_SA': // Sector Administrativo
        $sql = 'SELECT CONCAT(sad_id, " ") AS SECTOR, sad_nombre AS "NOMBRE DEL SECTOR ADMINISTRATIVO", CONCAT(sad_urCabeza, " ") AS "CABEZA SECTOR" FROM cat_SectorAdministrativo ORDER BY sad_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80, 15] : [10, 80, 10]);
        break;

    case 'ca_SP': // Productivo
        $sql = 'SELECT spd_id AS SECTOR, spd_nombre AS "NOMBRE DEL SECTOR PRODUCTIVO" FROM cat_SectorProductivo ORDER BY spd_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80] : [10, 90]);
        break;

        // De la Clasificación Administrativa CONAC
    case 'ac_sp': // Sector Público
        // Le ponemos un espacio al número con CONCAT para que NO lo alinea a la derecha
        $sql = 'SELECT CONCAT(spu_id, " ") AS SECTOR, spu_nombre AS "NOMBRE DEL SECTOR PÚBLICO" FROM cat_SectorPublico ORDER BY spu_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80] : [10, 90]);
        break;

    case 'ac_ssp': // Subsector Público
        $sql = 'SELECT CONCAT(sfi_id, " ") AS SUBSECTOR, sfi_nombre AS "NOMBRE DEL SUBSECTOR PÚBLICO" FROM cat_SectorFinanciero ORDER BY sfi_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80] : [10, 90]);
        break;

    case 'ac_si': // Sector Institucional
        $sql = 'SELECT CONCAT(spu_id, sfi_id, sec_id) AS SECTOR, sec_nombre AS "NOMBRE DEL SECTOR INSTITUCIONAL" FROM cat_SectorEconomico ORDER BY spu_id, sfi_id, sec_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80, 15] : [10, 80, 10]);
        break;

    case 'ac_ssi': // Subsector Institucional
        $sql = 'SELECT CONCAT(spu_id, sfi_id, sec_id, sse_id) AS SUBSECTOR, sse_nombre AS "NOMBRE DEL SUBSECTOR INSTITUCIONAL" FROM cat_SubsectorEconomico ORDER BY spu_id, sfi_id, sec_id, sse_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80] : [14, 86]);
        break;

    case 'ac_ep': // Ente Público
        $sql = 'SELECT CONCAT(spu_id, sfi_id, sec_id, sse_id, epu_id) AS ENTE, epu_nombre "NOMBRE DEL ENTE PÚBLICO" FROM cat_EntesPublicos ORDER BY spu_id, sfi_id, sec_id, sse_id, epu_id;';
        $anchoColumnas = (($bolParaExcel) ? [15, 80] : [10, 90]);
        break;

        // PED Transversal
    case 'pt_T': // Tema Transversal
        $sql = 'SELECT CONCAT(tet_id, " ") AS TEMA, tet_nombre AS "NOMBRE DEL TEMA TRANSVERSAL" FROM cat_TemaTransversal ORDER BY tet_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 80] : [10, 90]);
        break;

    case 'pt_O': // Objetivo Transversal
        $sql = 'SELECT CONCAT(tet_id, " ") AS TEMA, CONCAT(obt_id, " ") AS OBJETIVO, obt_nombre AS "NOMBRE DEL OBJETIVO TRANSVERSAL" FROM cat_ObjetivoTransversal ORDER BY tet_id, obt_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 80] : [10, 10, 80]);
        break;

    case 'pt_E': // Estrategia Transversal
        $sql = 'SELECT CONCAT(tet_id, " ") AS TEMA, CONCAT(obt_id, " ") AS OBJETIVO, CONCAT(tes_id, " ") AS ESTRATEGIA, tes_nombre "NOMBRE DE LA ESTRATEGIA TRANSVERSAL" FROM cat_EstrategiaTransversal ORDER BY tet_id, obt_id, tes_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 80] : [10, 10, 15, 65]);
        break;

        // PED
    case 'ped_E': // Eje
        $sql = 'SELECT CONCAT(eje_id, " ") AS EJE, eje_nombre AS "NOMBRE DEL EJE" FROM cat_Eje ORDER BY eje_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [6, 94]);
        break;

    case 'ped_T': // Tema
        $sql = 'SELECT CONCAT(eje_id, " ") AS EJE, CONCAT(tem_id, " ") AS TEM, tem_nombre AS "NOMBRE DEL TEMA" FROM cat_Tema ORDER BY eje_id, tem_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 80] : [5, 5, 90]);
        break;

    case 'ped_O': // Objetivo
        $sql = 'SELECT CONCAT(eje_id, " ") AS EJE, CONCAT(tem_id, " ") AS TEM, CONCAT(obj_id, " ") AS OBJ, obj_nombre AS "NOMBRE DEL OBJETIVO" FROM cat_Objetivo ORDER BY eje_id, tem_id, obj_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 80] : [5, 5, 5, 85]);
        break;

    case 'ped_S': // Estrategia
        $sql = 'SELECT CONCAT(eje_id, " ") AS EJE, CONCAT(tem_id, " ") AS TEM, CONCAT(obj_id, " ") AS OBJ, CONCAT(est_id, " ") AS EST, est_nombre AS "NOMBRE DE LA ESTRATEGIA" FROM cat_Estrategia ORDER BY eje_id, tem_id, obj_id, est_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 10, 80] : [5, 5, 5, 5, 80]);
        break;

    case 'ped_LA': // Línea de Acción
        $sql = 'SELECT CONCAT(eje_id, " ") AS EJE, CONCAT(tem_id, " ") AS TEM, CONCAT(obj_id, " ") AS OBJ, CONCAT(est_id, " ") AS EST, CONCAT(lac_id, " ") AS "L.A.", lac_nombre AS "NOMBRE DE LA LÍNEA DE ACCIÓN" FROM cat_LineaAccion ORDER BY eje_id, tem_id, obj_id, est_id, lac_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 10, 10, 80] : [5, 5, 5, 5, 5, 75]);
        break;

        // PES
    case 'pes_S': // Sector
        $sql = 'SELECT CONCAT(spe_id, " ") AS SECT, spe_nombre AS "NOMBRE DEL SECTOR" FROM cat_SectorPes ORDER BY spe_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [8, 90]);
        break;

    case 'pes_SS': // SubSector
        $sql = 'SELECT CONCAT(spe_id, " ") AS SECT, CONCAT(ssp_id, " ") AS SUBS, ssp_nombre AS "NOMBRE DEL SUBSECTOR" FROM cat_SubsectorPes ORDER BY spe_id, ssp_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60] : [8, 8, 76]);
        break;

    case 'pes_P': // Programa
        $sql = 'SELECT CONCAT(spe_id, " ") AS SECT, CONCAT(ssp_id, " ") AS SUBS, CONCAT(ppe_id, " ") AS PROG, ppe_nombre AS "NOMBRE DEL PROGRAMA", ppe_glosario AS "GLOSARIO DEL PROGRAMA" FROM cat_ProgramaPes ORDER BY spe_id, ssp_id, ppe_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 60, 60] : [7, 7, 7, 29, 50]);
        break;

    case 'pes_SP': // Subprograma
        $sql = 'SELECT CONCAT(spe_id, " ") AS SECT, CONCAT(ssp_id, " ") AS SUBS, CONCAT(ppe_id, " ") AS PROG, CONCAT(spp_id, " ") AS SUBP, spp_nombre AS "NOMBRE DEL SUBPROGRAMA" FROM cat_SubprogramaPes ORDER BY spe_id, ssp_id, ppe_id, spp_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 10, 60] : [8, 8, 8, 8, 68]);
        break;

    case 'pes_PR': // Planes Regionales
        $sql = 'SELECT CONCAT(reg_id, " ") AS REGION, reg_glosarioPes AS "GLOSARIO" FROM cat_Region ORDER BY reg_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [10, 90]);
        break;

        // UBICACION GEOGRAFICA
    case 'ug_R': // Región
        $sql = 'SELECT CONCAT(reg_id, " ") AS REG, reg_nombre AS "NOMBRE DE LA REGIÓN" FROM cat_Region ORDER BY reg_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [10, 90]);
        break;

    case 'ug_MR': // MicroRegión
        $sql = 'SELECT CONCAT(mre_id, " ") AS MRE, mre_nombre AS "NOMBRE DE LA MICROREGIÓN" FROM cat_MicroRegion ORDER BY mre_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [6, 94]);
        break;

    case 'ug_D': // Distrito
        $sql = 'SELECT CONCAT(reg_id, " ") AS REG, CONCAT(dto_id, " ") AS DTO, dto_nombre AS "NOMBRE DEL DISTRITO" FROM cat_Distrito ORDER BY reg_id, dto_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60] : [6, 6, 88]);
        break;

    case 'ug_M': // Municipio
        $sql = 'SELECT CONCAT(mun_id, " ") AS MUN, mun_nombre AS "NOMBRE DEL MUNICIPIO", CONCAT(reg_id, " ") AS REG, CONCAT(dto_id, " ") AS DTO, CONCAT(mre_id, " ") AS MRE FROM cat_Municipio ORDER BY mun_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 80, 10, 10, 10] : [6, 76, 6, 6, 6]);
        break;

    case 'ug_L': // Localidad
        $sql = 'SELECT CONCAT(mun_id, " ") AS MUN, CONCAT(loc_id, " ") AS LOC, loc_nombre AS "NOMBRE DE LA LOCALIDAD", CONCAT(reg_id, " ") AS REG, CONCAT(dto_id, " ") AS DTO, CONCAT(mre_id, " ") AS MRE FROM cat_Localidad ORDER BY mun_id, loc_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 80, 10, 10, 10, 10] : [6, 6, 70, 6, 6, 6]);
        break;

        // CLASIFICACION FUNCIONAL
    case 'cf_FI': // Finalidad
        $sql = 'SELECT CONCAT(fin_id, " ") AS FIN, fin_nombre AS "NOMBRE DE LA FINALIDAD", fin_descripcion AS "DESCRIPCION DE LA FINALIDAD" FROM cat_Finalidad ORDER BY fin_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60, 60] : [8, 22, 70]);
        break;

    case 'cf_FU': // Funcion
        $sql = 'SELECT CONCAT(fin_id, " ") AS FIN, CONCAT(fun_id, " ") AS FUN, fun_nombre AS "NOMBRE DE LA FUNCION", fun_descripcion AS "DESCRIPCION DE LA FUNCION" FROM cat_Funcion ORDER BY fin_id, fun_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60, 60] : [6, 6, 24, 64]);
        break;

    case 'cf_SF': // Subfunción
        $sql = 'SELECT CONCAT(fin_id, " ") AS FIN, CONCAT(fun_id, " ") AS FUN, CONCAT(sfu_id, " ") AS SFU, sfu_nombre AS "NOMBRE DE LA SUBFUNCION", sfu_descripcion AS "DESCRIPCION DE LA SUBFUNCION" FROM cat_Subfuncion ORDER BY fin_id, fun_id, sfu_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 60, 60] : [6, 6, 6, 22, 60]);
        break;

        // PROGRAMAS PRESUPUESTARIOS
    case 'pp_T': // Tipología
        $sql = 'SELECT CONCAT(tpg_id, " ") AS TIPOLOGIA, tpg_nombre AS "NOMBRE DE LA TIPOLOGIA" FROM cat_Tipologia ORDER BY tpg_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [14, 86]);
        break;

    case 'pp_C': // Clasificación
        $sql = 'SELECT CONCAT(tpg_id, " ") AS TIP, CONCAT(ppt_id, " ") AS CLA, ppt_nombre AS "NOMBRE DE LA CLASIFICACION" FROM cat_ProgramaPresupuestario ORDER BY tpg_id, ppt_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60] : [6, 6, 88]);
        break;

        // UNIDADES DE MEDIDA
    case 'um_U': // Unidades de Medida
        $sql = 'SELECT CONCAT(med_id, " ") AS MEDIDA, med_nombre AS NOMBRE, med_nombres AS NOMBRES, med_abreviatura AS ABREVIATURA FROM cat_UnidadMedida ORDER BY med_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 30, 30, 30] : [16, 28, 28, 28]);
        break;

    case 'um_T': // Tipos de Medida
        $orientacion = 'L';
        $sql = 'SELECT CONCAT(med_id, " ") AS MEDIDA, CONCAT(tme_id, " ") AS TIPO, tme_nombre AS NOMBRE, tme_nombres AS NOMBRES, tme_abreviatura AS ABREVIATURA, tme_sip AS SIP, tme_descripcion AS DESCRIPCION FROM cat_TipoMedida ORDER BY med_id, tme_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 30, 30, 30, 10, 30] : [8, 6, 20, 20, 20, 4, 22]);
        break;

        // FUENTE DE FINANCIAMIENTO
    case 'ff_F': // Fuente de Financiamiento
        $sql = 'SELECT fte_id AS FTE, fte_nombre AS "NOMBRE DE LA FUENTE" FROM cat_Fuente ORDER BY fte_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [6, 94]);
        break;

    case 'ff_SF': // Subfuente de Financiamiento
        $sql = 'SELECT fte_id AS FTE, sft_id AS SFTE, sft_nombre AS "NOMBRE DE LA SUBFUENTE" FROM cat_Subfuente ORDER BY fte_id, sft_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60] : [6, 6, 88]);
        break;

    case 'ff_O': // Origen del Recurso
        $sql = 'SELECT fte_id AS FTE, sft_id AS SFTE, ori_id AS ORI, ori_nombre AS "NOMBRE DEL ORIGEN DEL RECURSO" FROM cat_OrigenRecurso ORDER BY fte_id, sft_id, ori_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 60] : [6, 6, 6, 82]);
        break;

    case 'ff_SO': // Suborigen del Recurso
        $sql = 'SELECT fte_id AS FTE, sft_id AS SFTE, ori_id AS ORI, sor_id AS SORI, sor_nombre AS "NOMBRE DEL SUB ORIGEN DEL RECURSO" FROM cat_SuborigenRecurso ORDER BY fte_id, sft_id, ori_id, sor_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 10, 60] : [6, 6, 6, 6, 76]);
        break;

    case 'ff_CS': // Clasificación del Suborigen del Recurso
        $sql = 'SELECT fte_id AS FTE, sft_id AS SFTE, ori_id AS ORI, sor_id AS SORI, cso_id AS CLAS, cso_nombre AS "CLASIFICACION DEL SUBORIGEN DEL RECURSO" FROM cat_ClasificacionSuborigen ORDER BY fte_id, sft_id, ori_id, sor_id, cso_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 10, 10, 10, 60] : [6, 6, 6, 6, 6, 70]);
        break;

        // OBJETO DEL GASTO
    case 'ob_CA': // Capítulo
        $sql = 'SELECT CONCAT(cap_id, " ") AS CAPITULO, cap_nombre AS "NOMBRE DEL CAPÍTULO" FROM cat_Capitulo ORDER BY cap_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [15, 84]);
        break;

    case 'ob_CG': // Concepto
        $sql = 'SELECT CONCAT(cap_id, " ") AS CAPITULO, CONCAT(cto_id, " ") AS CONCEPTO, cto_nombre AS "NOMBRE DEL CONCEPTO DEL GASTO" FROM cat_Concepto ORDER BY cap_id, cto_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60] : [15, 15, 70]);
        break;

    case 'ob_PG': // Partida Genérica
        $sql = 'SELECT CONCAT(cto_id, " ") AS CONCEPTO, CONCAT(pg_id, " ") AS PARTIDA, pg_nombre AS "NOMBRE DE LA PARTIDA GENÉRICA" FROM cat_PartidaGenerica ORDER BY cto_id, pg_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60] : [15, 15, 70]);
        break;

    case 'ob_PE': // Partida Específica
        $sql = 'SELECT CONCAT(pg_id, " ") AS GENERICA, CONCAT(pe_id, " ") AS ESPECIFICA, pe_nombre AS "NOMBRE DE LA PARTIDA ESPECÍFICA", idp_id AS ID FROM cat_PartidaEspecifica ORDER BY pg_id, pe_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60, 10] : [15, 15, 65, 5]);
        break;

    case 'ob_TG': // Tipo de Gasto
        $sql = 'SELECT CONCAT(tg_id, " ") AS TIPO, tg_nombre AS "NOMBRE DEL TIPO DE GASTO" FROM cat_TipoGasto ORDER BY tg_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [10, 90]);
        break;

    case 'ob_OG': // Objeto del Gasto
        $sql = 'SELECT CONCAT(tg_id, " ") AS TIPO, og_id AS OBJETO, og_nombre AS "NOMBRE DEL OBJETO DE GASTO" FROM cat_ObjetoGasto ORDER BY tg_id, og_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 10, 60] : [10, 10, 80]);
        break;

    case 'ob_IP': // Identificador de Partida
        $sql = 'SELECT idp_id AS ID, idp_nombre AS "NOMBRE DEL IDENTIFICADOR DE PARTIDA" FROM cat_IdentificadorPartida ORDER BY idp_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [10, 90]);
        break;

    case 'ob_TF': // Clasificación de los Techos Financieros
        $sql = 'SELECT CONCAT(tei_id, " ") AS ID, tei_descripcion AS "NOMBRE DEL CLASIFICADOR DE TECHO FINANCIERO" FROM cat_TechoIdentificador ORDER BY tei_id;';
        $anchoColumnas = (($bolParaExcel) ? [10, 60] : [10, 90]);
        break;

        // OTROS VARIOS
    case 'apg': // Asignación de Partidas Genéricas por Unidad Responsable
        // Si está un filtro activo, lo aplicamos también en el reporte
        $gpo = substr($_POST['ur'], 0, 1);
        $ur = substr($_POST['ur'], 1, 2);
        $where = " gpo_id = $gpo AND ur_id = $ur ";
        if ($filtro != '') {
            $where = 'AND (pg_id LIKE "%' . $filtro . '%") OR (pg_nombre LIKE "%' . $filtro . '%")';
        }
        $sql = '
            SELECT CONCAT(pg_id, " ") AS PARTIDA, pg_nombre AS "DESCRIPCION" , CASE WHEN ccd_id IS NULL THEN "" ELSE "SI" END AS USADA
            FROM cat_PartidaGenerica
            INNER JOIN enc_PartGenUR p USING(pg_id)
            LEFT JOIN presupuesto USING(gpo_id, ur_id, pg_id)
            LEFT JOIN presupuestoOriginal USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
            WHERE ' . $where .
            'GROUP BY pg_id
            ORDER BY pg_id;
        ';
        $anchoColumnas = (($bolParaExcel) ? [10, 60, 10] : [12, 78, 10]);
        $alignColumnas = ['C', 'L', 'C'];

        break;

        // POA
    case 'lps': // Listado de programas y subprogramas para tablas dinámicas de excel
        $where = '';
        if ($filtro != '') {
            $where .= 'AND ((p.prg_id LIKE "%' . $filtro . '%") OR (p.prg_nombre LIKE "%' . $filtro . '%") OR (epr_nombre LIKE "%' . $filtro . '%"))';
        }

        $sql = "
            SELECT
            CONCAT(p.prg_id, '  ', prg_nombre) AS Programa,
            if (spr_id = '00','', CONCAT(spr_id, '  ', spr_nombre)) AS Subprograma ,
            epr_nombre AS EstadoPrograma
            FROM cat_Programa p
            LEFT JOIN cat_Subprograma s USING(gpo_id, ur_id, prg_id)
            JOIN cat_EstadoPrograma USING(epr_id)
            WHERE p.gpo_id = 0 AND ur_id = 0 $where
            ORDER BY p.prg_id, spr_id;
        ";
        $anchoColumnas = [60, 60, 25];
        break;

    case 'ccA': // Cédula de Costeo : Alimentación
        // Si está un filtro activo, lo aplicamos también en el reporte
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $where = " a.gpo_id = $gpo AND a.ur_id = $ur ";
        $sql = "
            SELECT 'ALIMENTACION' AS cedula, ced_id AS numCedula,
            CONCAT(a.gpo_id, a.ur_id, '-', a.ue_id, '-', ep.prg_id, '-', ep.spr_id, '-', ep.py_id, '-', ep.oa_id) AS programatico,
            CONCAT(so.ter_id, '-', cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, cf.cso_id, '-', cf.cfi_id, cf.cfi_anio) AS ClaveFinanciamiento,
            if (gbf_id = 9,ali_beneficiarioOtros,gbf_nombre) AS GrupoBeneficiario, asr_nombre AS AreaServicio,
            ali_beneficiarios AS Beneficiarios,
            ali_cuotaDiaria AS CuotaDiaria,
            mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun +mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic AS Total
            FROM poa_Alimentacion a
            JOIN cat_GrupoBeneficiario USING(gbf_id)
            JOIN cat_AreaServicio USING(asr_id)
            JOIN enc_Programatico ep USING(epg_id)
            JOIN enc_ClaveFinanciamiento cf USING(ecf_id)
            JOIN cat_SuborigenRecurso so USING(fte_id, sft_id, ori_id, sor_id)
            WHERE $where;
        ";

        $anchoColumnas = [15, 15, 30, 30, 30, 30, 15, 15];
        break;

    case 'cmEPG': // Exportar encuadre programatico para carga masiva
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $sql = "
            SELECT o.epg_id,
                CONCAT(p.gpo_id, p.ur_id, '-', ue_id, '-', p.prg_id, '-', p.spr_id, '-', p.py_id, '-', p.oa_id, '  ', if (p.oa_id != 0,oa_nombre,py_nombre)) AS prog
            FROM poa_OrigenRecurso o
            JOIN enc_Programatico p USING (epg_id)
            JOIN cat_Proyecto py ON (p.gpo_id = py.gpo_id AND p.ur_id = py.ur_id  AND p.prg_id = py.prg_id AND p.spr_id = py.spr_id AND p.py_id = py.py_id)
            JOIN cat_ObraActividad a ON (p.gpo_id = a.gpo_id AND p.ur_id = a.ur_id AND p.prg_id = a.prg_id AND p.spr_id = a.spr_id AND p.py_id = a.py_id AND p.oa_id = a.oa_id)
            WHERE o.gpo_id = $gpo AND o.ur_id = $ur AND
            eoa_id = 7
            GROUP BY o.epg_id;
        ";
        $anchoColumnas = [15, 60];
        break;

    case 'cmOG': // Exportar Partida Genérica y Específica, para cargas masivas
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $sql = "
            SELECT DISTINCT pg_id, pg_nombre, pe_id, CONVERT(CONCAT(pg_id, pe_id), SIGNED INTEGER) AS pg_pe, pe_nombre, clg_id AS ClaseDeGasto
            FROM cat_PartidaGenerica
            INNER JOIN cat_PartidaEspecifica e USING(pg_id)
            INNER JOIN enc_PartGenUR USING(pg_id)
            LEFT JOIN (
                SELECT pg_id, pe_id
                FROM enc_PartEspCed
                GROUP BY pg_id, pe_id
            )c USING(pg_id, pe_id)
            WHERE gpo_id = $gpo  AND ur_id = $ur
            AND tno_id = 0
            AND c.pg_id IS NULL;
        ";
        $anchoColumnas = [15, 60, 15, 15, 60, 15];
        break;

    case 'cmOGVehic': // Exportar Partida Genérica y Específica, para cargas masivas de cédulas de Vehículos
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $sql = "
            SELECT DISTINCT pg_id, pg_nombre, pe_id, CONVERT(CONCAT(pg_id, pe_id), SIGNED INTEGER) AS pg_pe, pe_nombre, clg_id AS ClaseDeGasto
            FROM enc_PartEspCed
            INNER JOIN cat_PartidaGenerica USING(pg_id)
            INNER JOIN enc_PartGenUR USING(pg_id)
            INNER JOIN cat_PartidaEspecifica USING(pg_id, pe_id)
            WHERE gpo_id = $gpo  AND ur_id = $ur and ccd_id = 4;
        ";
        $anchoColumnas = [15, 60, 15, 15, 60, 15];
        break;

    case 'cmECF': // Exportar Clave de Financiamiento, para cargas masivas
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $sql = "
            SELECT o.ecf_id,
                CONCAT(so.ter_id, '-', f.fte_id, f.sft_id, f.ori_id, f.sor_id, f.cso_id, '-', f.cfi_id, f.cfi_anio, '   ', cfi_nombre) AS cveF, so.ter_id AS GranFuente
            FROM poa_OrigenRecurso o
            JOIN enc_Programatico p USING (epg_id)
            JOIN enc_ClaveFinanciamiento f USING(ecf_id)
            JOIN cat_ObraActividad a ON (p.gpo_id = a.gpo_id AND p.ur_id = a.ur_id AND p.prg_id = a.prg_id AND p.spr_id = a.spr_id AND p.py_id = a.py_id AND p.oa_id = a.oa_id)
            JOIN cat_ClaveFinanciamiento USING(fte_id, sft_id, ori_id, sor_id, cso_id, cfi_id, cfi_anio)
            JOIN cat_ClasificacionSuborigen so USING(fte_id, sft_id, ori_id, sor_id,cso_id)
            WHERE o.gpo_id = $gpo AND o.ur_id = $ur AND
            eoa_id = 7
            GROUP BY o.ecf_id;
        ";
        $anchoColumnas = [15, 60, 15];
        break;

    case 'cmPV': // Exportar la Plantilla Vehicular 2015
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $sql = "
            SELECT pqv_id AS serie, pqv_numeroPlacas AS placas, pqv_tipo AS tipoVehiculo FROM cat_ParqueVehicular2015
            WHERE gpo_id = $gpo AND ur_id = $ur;
        ";
        $anchoColumnas = [20, 20, 20];
        break;

    case 'cmPV14': // Exportar la Plantilla Vehicular 2014
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $sql = "
            SELECT pqv_id AS serie, pqv_numeroPlacas AS placas, pqv_tipo AS tipoVehiculo FROM ctrl2014.cat_ParqueVehicular2014
            WHERE gpo_id = $gpo AND ur_id = $ur;
        ";
        $anchoColumnas = [20, 20, 20];
        break;

        // REPORTES POA.   Genera el presupuesto autorizado con todos los datos de programa, subprograma, proyecto, obra/actividad
        // clave de financiamiento y objeto del gasto
    case 'poa1':
        // Si está un filtro activo, lo aplicamos también en el reporte
        $gpo = $_SESSION[_S]['clasAdm-gpo']; // Siempre estarán limitados a la UR que tengan seleccionada
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $where = " p.gpo_id = $gpo AND p.ur_id = $ur ";
        // Si no es la 314, NO verán los programas correspondientes a previsiones
        $prg314 = '';
        if ($_SESSION[_S]['s17-gpo'] . $_SESSION[_S]['s17-ur'] != 314) {
            $prg314 = ' and p.prg_id not between 95 and 99';
        }

        $sql = "
            SELECT

            CONCAT(gpo_id, ur_id) AS idUnidadResponsable,
            CONCAT(gpo_id, ur_id, ' - ', ur.ur_nombre) AS UnidadResponsable,

            ue_id AS idUnidadEjecutora,
            CONCAT(ue_id, ' - ', ue.ue_nombre) AS UnidadEjecutora,

            cap_id AS idCapituloGasto,
            CONCAT(cap_id, ' - ', cap.cap_nombre) AS CapituloGasto,

            cto_id AS idConceptoGasto,
            CONCAT(cto_id, ' - ', cto.cto_nombre) AS ConceptoGasto,

            pg_id AS idPartidaGenerica,
            CONCAT(pg_id, ' - ', pg.pg_nombre) AS PartidaGenerica,

            idp_id AS idIdentificadorPartida,
            CONCAT(idp_id, ' - ', idp.idp_nombre) AS IdentificadorPartida,

            tg_id AS idTipoGasto,
            CONCAT(tg_id, ' - ', tg.tg_nombre) AS TipoGasto,

            og_id AS idObjetoGasto,
            CONCAT(og_id, ' - ', og.og_nombre) AS ObjetoGasto,

            clg_id AS idClaseGasto,
            CONCAT(clg_id, ' - ', clg.clg_nombre) AS ClaseGasto,

            pe_id AS idPartidaEspecifica,
            CONCAT(pe_id, ' - ', pe.pe_nombre) AS PartidaEspecifica,

            fte_id AS idFuente,
            CONCAT(fte_id, ' - ', fte.fte_nombre) AS Fuente,

            CONCAT(fte_id, sft_id) AS idSubfuente,
            CONCAT(fte_id, sft_id, ' - ', sft.sft_nombre) AS Subfuente,

            CONCAT(fte_id, sft_id, ori_id) AS idOrigenRecurso,
            CONCAT(fte_id, sft_id, ori_id, ' - ', ori.ori_nombre) AS OrigenRecurso,

            CONCAT(fte_id, sft_id, ori_id, sor_id) AS idSuborigenRecurso,
            CONCAT(fte_id, sft_id, ori_id, sor_id, ' - ', sor.sor_nombre) AS SuborigenRecurso,

            CONCAT(fte_id, sft_id, ori_id, sor_id, cso_id) AS idClasificacionSuborigen,
            CONCAT(fte_id, sft_id, ori_id, sor_id, cso_id,  ' - ', cso.cso_nombre) AS ClasificacionSuborigen,

            cfi.cfi_clave AS idClaveFinanciamiento,
            CONCAT(cfi.cfi_clave, ' - ', cfi_nombre) AS ClaveFinanciamiento,

            cfi_anio AS AñoClaveFinancimiento,

            ter_id AS idTipoRecurso,
            CONCAT(ter_id, ' - ', ter.ter_nombre) AS TipoRecurso,

            prg_id AS idPrograma,
            CONCAT(prg_id, ' - ', prg.prg_nombre) AS Programa,

            CONCAT(prg_id, '-', spr_id) AS idSubprograma,
            CONCAT(prg_id, '-', spr_id, ' - ', spr.spr_nombre) AS Subprograma,

            CONCAT(prg_id, '-', spr_id, '-', py_id) AS idProyecto,
            CONCAT(prg_id, '-', spr_id, '-', py_id, ' - ', py.py_nombre) AS Proyecto,

            CONCAT(prg_id, '-', spr_id, '-', py_id, '-', oa_id) AS idObraActividad,
            CONCAT(prg_id, '-', spr_id, '-', py_id, '-', oa_id, ' - ', oa.oa_nombre) AS ObraActividad,

            pre_claveCompleta,

            pre_original
            FROM presupuesto p
                JOIN cat_UnidadResponsable ur USING(gpo_id, ur_id)
                JOIN cat_UnidadEjecutora ue USING(gpo_id, ur_id, ue_id)
                JOIN cat_PartidaGenerica pg USING(pg_id)
                JOIN cat_Concepto cto USING (cto_id)
                JOIN cat_Capitulo cap USING(cap_id)
                JOIN cat_PartidaEspecifica pe USING(pg_id, pe_id)
                JOIN cat_IdentificadorPartida idp USING(idp_id)
                JOIN cat_TipoGasto tg USING(tg_id)
                JOIN cat_ObjetoGasto og USING(tg_id, og_id)
                JOIN cat_ClaseGasto clg USING(clg_id)
                JOIN cat_Fuente fte USING(fte_id)
                JOIN cat_Subfuente sft USING(fte_id, sft_id)
                JOIN cat_OrigenRecurso ori USING(fte_id, sft_id, ori_id)
                JOIN cat_SuborigenRecurso sor USING(fte_id, sft_id, ori_id, sor_id)
                JOIN cat_ClasificacionSuborigen cso USING(fte_id, sft_id, ori_id, sor_id, cso_id)
                JOIN cat_ClaveFinanciamiento cfi USING(fte_id, sft_id, ori_id, sor_id, cso_id, cfi_id, cfi_anio)
                JOIN cat_TipoRecurso ter USING(ter_id)
                JOIN cat_Programa prg USING(gpo_id, ur_id, prg_id)
                JOIN cat_Subprograma spr USING(gpo_id, ur_id, prg_id, spr_id)
                JOIN cat_Proyecto py USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                JOIN cat_ObraActividad oa USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)

            WHERE $where $prg314;
        ";
        break;

        // PLAN DE CUENTAS
    case 'planActual':
        $sql = DameSqlPlanCuentas('actual', $mayor, $desde, $hasta, $filtro, $filtroUE);

        if ($bolParaExcel) {
            // El ancho de cada columna, en valores normales que usaríamos para ANCHO - COLUMNA dentro de excel
            $anchoColumnas = [30, 60, 10, 10, 7, 7, 6];
        } else {
            // El ancho de cada columna, en porcentaje (la suma de todas las columnas debe ser máximo 100)
            $anchoColumnas = [24, 48, 8, 5, 5, 5, 5];
        }
        break;

    case 'planTodo':
        $sql = DameSqlPlanCuentas('todo', $mayor, $desde, $hasta, $filtro, $filtroUE);

        if ($bolParaExcel) {
            // El ancho de cada columna, en valores normales que usaríamos para ANCHO - COLUMNA dentro de excel
            $anchoColumnas = [30, 60, 10, 10, 7, 7, 6];
        } else {
            // El ancho de cada columna, en porcentaje (la suma de todas las columnas debe ser máximo 100)
            $anchoColumnas = [24, 48, 8, 5, 5, 5, 5];
        }
        break;

    case 'planRango':
        $sql = DameSqlPlanCuentas('rango', $mayor, $desde, $hasta, $filtro, $filtroUE);

        if ($bolParaExcel) {
            // El ancho de cada columna, en valores normales que usaríamos para ANCHO - COLUMNA dentro de excel
            $anchoColumnas = [30, 60, 10, 10, 7, 7, 6];
        } else {
            // El ancho de cada columna, en porcentaje (la suma de todas las columnas debe ser máximo 100)
            $anchoColumnas = [24, 48, 8, 5, 5, 5, 5];
        }
        break;

        // Banco de Documentos
    case 'listaDocto':
        $tdf = (isset($_POST['tdf']) ? $_POST['tdf'] : '');
        $rfc = (isset($_POST['rfc']) ? $_POST['rfc'] : '');
        $ref = (isset($_POST['ref']) ? $_POST['ref'] : '');
        $del = (isset($_POST['del']) ? $_POST['del'] : '');
        $al = (isset($_POST['al']) ? $_POST['al'] : '');
        $ed = (isset($_POST['ed']) ? $_POST['ed'] : '');
        $id = (isset($_POST['id']) ? $_POST['id'] : '');
        $ih = (isset($_POST['ih']) ? $_POST['ih'] : '');
        $where = '';

        // Para el filtro por tipo de documento fuente
        if ($tdf != '') {
            $where .= ' AND tdf_id = "' . $tdf . '" ';
        }

        // Para filtrar por rfc del emisor
        if ($rfc != '') {
            $where .= " AND (rfc_id = '" . $rfc . "') ";
        }

        // Para buscar por referencia
        if ($ref != '') {
            // Si es una petición de la vinculación por CLC, es uno o más documentos y usamos IN
            if (substr($ref, 0, 1) == '*') {
                $where .= ' AND (bdo_id IN (' . substr($ref, 1) . '))';
                // Si es comportamiento normal, usamos LIKE
            } else {
                $where .= " AND (bdo_referencia like '%" . $ref . "%') ";
            }
        }
        // Para filtrar por fecha DEL
        if (trim($del) != '') {
            $where .= " AND bdo_fechaDocumento >= '" . date_to_mysqldate($del) . "' ";
        }

        // Para filtrar por fecha AL
        if (trim($al) != '') {
            $where .= " AND bdo_fechaDocumento <= '" . date_to_mysqldate($al) . "' ";
        }

        // Para filtrar por importe del documento DESDE ...
        if (trim($id) != '') {
            $where .= ' AND bdo_importe >= ' . $id . ' ';
        }
        // Para filtrar por importe del documento HASTA ...
        if (trim($ih) != '') {
            $where .= ' AND bdo_importe <= ' . $ih . ' ';
        }

        // Para filtrar el status del documento:
        // 1 = documento sin devengar o parcialmente devengados
        // 2 = totalmente devengado
        // 3 = todos los documentos SIN cancelados
        // 4 = todos los documentos INCLUYENDO cancelados
        // POR DEFAULT SERA 1
        if (trim($ed) != '') {
            // Si comparamos contra un valor fijo del campo ...
            if ($ed == '1' or $ed == '2') {
                $where .= ' AND bdo_estadoDevengo  = ' . $ed . ' AND sts_id = 1 ';
                // Si es todos SIN cancelados
            } elseif ($ed == '3') {
                $where .= ' AND sts_id = 1 ';
            } elseif ($ed == '4') {
                // Si es todos los documentos entonces NO hacemos nada ...
            }
        }

        $sql = '
                SELECT tdf_id AS TDF, bdo_referencia AS REFERENCIA,  bdo_fechaDocumento AS FECHA,
                d.rfc_id AS RFC, ' . ($bolParaExcel ? ' bdo_total ' : ' FORMAT(bdo_total, 2) ') . " AS IMPORTE, bdo_concepto AS CONCEPTO,
                CASE bdo_estadoDevengo WHEN 2 THEN 'SI' ELSE 'NO' END as DEV,
                CASE sts_id WHEN 1 THEN '' ELSE 'X' END AS EDO,
                CASE bdo_origen WHEN 1 THEN 'MAN' WHEN 2 THEN 'XML' ELSE 'CM' END AS ORI,
                CASE bdo_estadoSat WHEN 1 THEN 'PDTE' WHEN 2 THEN 'OK' WHEN 3 THEN 'N/A' END AS SAT
                FROM cat_BancoDocumentos d
                WHERE gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id = ' . $_SESSION[_S]['clasAdm-ur'] .
            $where . ';
            ';

        if ($bolParaExcel) {
            // El ancho de cada columna, en valores normales que usaríamos para ANCHO - COLUMNA dentro de excel
            $anchoColumnas = [10, 25, 12, 20, 15, 60, 8, 8, 8, 8];
        } else {
            // El ancho de cada columna, en porcentaje (la suma de todas las columnas debe ser máximo 100)
            $anchoColumnas = [5, 15, 6, 13, 12, 30, 5, 5, 5, 5];
            $alignColumnas = ['L', 'L', 'L', 'L', 'R', 'L', 'C', 'C', 'C', 'C'];
        }
        break;

        // Control de Documentos fuente
    case 'ctrlDoctoFte':
        $msc = (isset($_POST['msc']) ? $_POST['msc'] : '');
        $tpo = (isset($_POST['tpo']) ? $_POST['tpo'] : '');
        $tdf = (isset($_POST['tdf']) ? $_POST['tdf'] : '');
        $rfc = (isset($_POST['rfc']) ? $_POST['rfc'] : '');
        $cta = (isset($_POST['cta']) ? $_POST['cta'] : '');

        $where = '';

        // Si es un usuario de nivel UE, sólo mostramos información de su UE
        if (isset($_SESSION[_S]['s17-ue']) && $_SESSION[_S]['s17-ue'] != '') {
            $where = ' AND ue_id = ' . $_SESSION[_S]['s17-ue'] . ' ';
        }

        // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
        $opcion = substr($tpo, 0, 2);
        $valor = substr($tpo, 3, 1);
        $where .= " AND pot_id = '" . $valor . "' ";

        // Si quieren filtrar por Tipo de Documento Fuente
        if ($tdf != '') {
            $where .= " AND tdf_id = '" . $tdf . "' ";
        }

        // Si quieren filtrar por Emisor del Documento Fuente (RFC)
        if ($rfc != '') {
            $where .= " AND d.rfc_id LIKE '" . $rfc . "%' ";
        }

        // Si quieren filtrar por Cuenta Contable, aquí será un poco más de código porque cada dígito es un camo
        // separado
        if ($cta != '') {
            // Separamos la cuenta en cada uno de sus probables campos
            $ger_id = substr($cta, 0, 1);
            $cgp_id = substr($cta, 1, 1);
            $rub_id = substr($cta, 2, 1);
            $cgcu_id = substr($cta, 3, 1);
            $cgsc_id = substr($cta, 4, 1);
            $ue = substr($cta, 8, 3);
            $cgc = substr($cta, 11, 3);
            $reg = substr($cta, 14);

            // Validamos que hasta el tercer nivel sólo tengan numeros
            $ger_id = (preg_match('/^[[:digit:]]+$/', $ger_id)) ? $ger_id : '';
            $cgp_id = (preg_match('/^[[:digit:]]+$/', $cgp_id)) ? $cgp_id : '';
            $rub_id = (preg_match('/^[[:digit:]]+$/', $rub_id)) ? $rub_id : '';
            $cgcu_id = (preg_match('/^[[:digit:]]+$/', $cgcu_id)) ? $cgcu_id : '';
            $cgsc_id = (preg_match('/^[[:digit:]]+$/', $cgsc_id)) ? $cgsc_id : '';
            $ue = (preg_match('/^[[:digit:]]+$/', $ue)) ? $ue : '';

            // Y entonces sí, checamos cada campo para el que sí hayan enviado información
            if ($ger_id != '') {
                $where .= ' AND ger_id = ' . $ger_id . ' ';
            }
            if ($cgp_id != '') {
                $where .= ' AND cgp_id = ' . $cgp_id . ' ';
            }
            if ($rub_id != '') {
                $where .= ' AND rub_id = ' . $rub_id . ' ';
            }
            if ($cgcu_id != '') {
                $where .= ' AND cgcu_id = ' . $cgcu_id . ' ';
            }
            if ($cgsc_id != '') {
                $where .= ' AND cgsc_id = ' . $cgsc_id . ' ';
            }
            // No checamos gpo_id y ur_id porque en otra parte del where viene
            if ($ue != '') {
                $where .= ' AND ue_id = ' . $ue . ' ';
            }

            if ($cgc != '') {
                $where .= " AND cgc_id = '" . $cgc . "' ";
            }
            if ($reg != '') {
                $where .= " AND cta_id = '" . $reg . "' ";
            }
        }

        if ($bolParaExcel) {
            $sql = "
                SELECT tdf_id AS TIPO, dpo_referencia AS REFERENCIA, IFNULL(DATE_FORMAT(dpo_fechaReferencia, '%d-%m-%y'), '') AS FECHA,
                d.rfc_id AS RFC, CONCAT(rfc_nombre, ' ', rfc_paterno, ' ', rfc_materno) AS NOMBRE, dpo_concepto AS CONCEPTO,
                dpo_debe  AS CARGO,
                dpo_haber AS ABONO,
                CONCAT(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id) AS CUENTA,
                d.mmc_id AS MC, dpo_vinculacion AS SCP,
                d.epl_id AS EDO, CONCAT(pot_id, '-', prf_id, pol_numero) AS POLIZA
                FROM cg_PolizaDetalle d
                JOIN cg_PolizaMaestro m USING(gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pol_numero)
                JOIN cat_Proveedores p ON(d.rfc_id = p.rfc_id)
                WHERE d.gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . ' AND d.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] .
                $where . " AND msc_id = $msc
                ORDER BY dpo_fechaReferencia
            ";
        } else {
            $sql = "
                SELECT tdf_id AS TIPO, dpo_referencia AS REFERENCIA, IFNULL(DATE_FORMAT(dpo_fechaReferencia, '%d-%m-%y'), '') AS FECHA,
                d.rfc_id AS RFC, dpo_concepto AS CONCEPTO,
                if (dpo_debe  = 0, '', FORMAT(dpo_debe,  2)) AS CARGO,
                if (dpo_haber = 0, '', FORMAT(dpo_haber, 2)) AS ABONO,
                CONCAT(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id) AS CUENTA,
                d.mmc_id AS MC, dpo_vinculacion AS SCP,
                d.epl_id AS EDO, CONCAT(pot_id, '-', prf_id, pol_numero) AS POLIZA
                FROM cg_PolizaDetalle d
                JOIN cg_PolizaMaestro m USING(gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pol_numero)
                WHERE d.gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . ' AND d.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] .
                $where . " AND msc_id = $msc
                ORDER BY dpo_fechaReferencia
                ";
        }

        if ($bolParaExcel) {
            // El ancho de cada columna, en valores normales que usaríamos para ANCHO - COLUMNA dentro de excel
            $anchoColumnas = [10, 25, 12, 20, 60, 60, 14, 14, 10, 5, 5, 5, 10];
        } else {
            // El ancho de cada columna, en porcentaje (la suma de todas las columnas debe ser máximo 100)
            $anchoColumnas = [5, 14, 5, 8, 27, 10, 10, 6, 3, 3, 3, 6];
            $alignColumnas = ['L', 'L', 'L', 'L', 'L', 'R', 'R', 'C', 'C', 'C', 'C', 'C'];
        }
        break;

    default:
        $sql = 'SELECT "hola, mundo!!!" AS COLUMNA;';
        break;
}

// Aramamos los parametros opcionales
// Agregar elemento params, en caso de ser necesario.
$attr = [
    'encabLin1'          => $encabLin1,
    'encabLin2'          => $encabLin2,
    'encabLin3'          => $encabLin3,
    'anchoColumnas'      => $anchoColumnas,
    'alineacionColumnas' => $alignColumnas,
];

// Si lo quieren descargar en formato EXCEL
if ($bolParaExcel) {
    $excel = new query2Excel();
    $excel->generaExcel($sql, $attr);
    // Si lo quieren ver en formato PDF
} else {
    $pdf = new otroPDF($orientacion, 'pt', $papel); // en puntos, porque así venía el ejemplo ...
    $pdf->SetFont($fnt, '', $size);
    $pdf->mysqlReport($sql, $attr);
    $pdf->Output();
}

// FUNCIONES DE APOYO PARA NO SATURAR EL CODIGO DONDE DEFINIMOS LAS CONSULTAS

// Regresamos la consulta para el Plan de Cuentas
// $tipo puede ser : actual (Solo una cuenta), "todo" o "rango"
// $filtro es el texto dentro del nombre a filtrar
function DameSqlPlanCuentas($tipo, $mayor, $desde, $hasta, $filtro, $ue)
{
    // Si estamos filtrando por cuenta ... o por rango de cuenta ...
    $filtroCta = '';
    $filtroUE = '';
    // Si estan pidiendo la seleccion actual, checamos la cuenta Mayor
    if ($tipo == 'actual') {

        $genero = substr($mayor, 0, 1);
        $grupo = substr($mayor, 1, 1);
        $rubro = substr($mayor, 2, 1);
        $cuenta = substr($mayor, 3, 1);
        $subcuenta = substr($mayor, 4, 2);

        $filtroCta = ' AND ger_id = ' . $genero . ' AND cgp_id = ' . $grupo . ' AND rub_id = ' . $rubro . ' AND cgcu_id = ' . $cuenta . ' AND cgsc_id = ' . $subcuenta . ' ';
    }

    // El filtro por UE se aplica siempre, porque puede ser un usuario de nivel UE
    if ($ue != '') {
        $filtroUE = ' AND cta_ue_id = ' . $ue . ' ';
    }

    // Si están pidiendo por rango (lo sabemos porque viene la variable $hasta)
    if ($tipo == 'rango' && $hasta != '') {
        $filtroCta = " AND CONCAT(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id) BETWEEN '" . $desde . "' AND '" . $hasta . "' ";
    }

    // Si están pidiendo un filtro de texto al nombre de la cuenta ...
    $filtroNombre = '';
    if ($filtro != '') {
        $filtroNombre = " AND cta_nombre LIKE '%" . trim($filtro) . "%' ";
    }

    $wherePlanCuentas = ' WHERE cta_gpo_id = ' . $_SESSION[_S]['clasAdm-gpo']
        . ' AND cta_ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' '
        . $filtroCta . $filtroNombre . $filtroUE;

    return "
        SELECT CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id) AS CUENTA, cgm_nombre AS NOMBRE, CASE cgm_naturaleza WHEN 'D' THEN 'Deudora' ELSE 'Acreedora' END AS NAT, cgm_estructura AS TIPO, cgm_tipo AS EDO, cgm_posicion AS POS, ue_id AS ue
        FROM
        (
            SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, '' AS ue_id FROM cg_PlanCuentas
            " . $wherePlanCuentas . "
            GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
        ) AS base
        INNER JOIN cg_CuentaMayor USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)

        UNION

        SELECT cgc_cuenta AS CUENTA, cgc_nombre AS NOMBRE, CASE cgm_naturaleza WHEN 'D' THEN 'Deudora' ELSE 'Acreedora' END AS NAT, cgm_estructura AS TIPO, cgm_tipo AS EDO, cgm_posicion AS POS, cta_ue_id
        FROM
        (
            SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_ue_id FROM cg_PlanCuentas
            " . $wherePlanCuentas . "
            GROUP BY  ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id
        ) AS base
        INNER JOIN cg_CuentaMayor USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)
        INNER JOIN cg_CuentaCuarto USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id)

        UNION

        SELECT cta_Cuenta AS cuenta, cta_Nombre AS nombre, CASE cgm_naturaleza WHEN 'D' THEN 'Deudora' ELSE 'Acreedora' END AS nat, cgm_estructura AS TIPO, cgm_tipo AS edo, cgm_posicion AS pos, cta_ue_id
        FROM cg_PlanCuentas
        INNER JOIN cg_CuentaMayor USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)
        " . $wherePlanCuentas . '
        ORDER BY cuenta;
    ';
}
