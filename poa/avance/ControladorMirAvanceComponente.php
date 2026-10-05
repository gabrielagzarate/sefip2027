<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

/* --------------------------------------------------------------------------------------------------------
Objetivo: MATRIZ DE INDICADOR DE RESULTADOS, por nivel (fin, proposito, componente y actividad), agrega supuestos e indicadores
Elaboró : Anuar López Morgan, Zuli D. López Barbosa, Soledad López Velasco
-------------------------------------------------------------------------------------------------------- */

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/Indicador.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaComponente.php';
require_once dirname(__FILE__) . '/../../ent/HistorialMensajes.php';
set_time_limit(0);

class ControladorMirAvanceComponente extends WebResponse
{
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        #si tiene el permiso de lectura le permitimos pasar
        if (isset($_SESSION[_S]['prm']['A']['10']['3']['1']) || isset($_SESSION[_S]['prm']['A']['10']['3']['2'])) {
            switch ($this->metodo) {
                case 'pintaPantalla':
                    $this->pintaPantalla();
                    break;
                case 'verMetas':
                    $this->verMetas();
                    break;
                case 'listaIndicador':
                    $this->listaIndicador();
                    break;
                case 'addModMeta':
                    $this->addModMeta();
                    break;
                case 'abcMeta':
                    $this->abcMeta();
                    break;
                case 'main':
                    $this->main();
                    break;
            }
            $this->returnJson();
        } else {
            $this->sinPermisos();
        }
    }
    /* -------------------------------------------------------------------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
    ------------------------------------------------------------------------------------------------------------------------------------------- */

    private function main()
    {
        $clasAdmin = new stdClass();
        $clasAdmin->comboUe = false;
        $clasAdmin->comboPy = false;
        $clasAdmin->comboOa = false;
        $clasAdmin->change = '
            if($("#comboprg").val()!= ""){
                if($("#comboprg").val() * 1 >= 101 && $("#comboprg").val() <= 900 ){
                    comunica("' . $this->pantalla . '", {
                        metodo:"pintaPantalla"
                    });
                }
                if( $("#comboprg").val() * 1 >= 0 && $("#comboprg").val()<= 100  ) {
                    comunica("' . $this->pantalla . '", {
                        metodo:"listaIndicador"
                    });
                }
                if($("#comboprg").val()* 1 >= 901 && $("#comboprg").val() <= 999 ){
                    comunica("' . $this->pantalla . '", {metodo:"listaIndicador"});
                }
                $("#clasAdminSeccionCombos").hide();
            }
            $("#contenedor").html("<p class=\'text-danger\'>Seleccionar un Programa</p>");
        ';

        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin) . '
            comunica("' . $this->pantalla . '", {
                metodo:"pintaPantalla"
            });
        ');
    }

    private function listaIndicador()
    {

        $btnregresar = new stdClass();
        $btnregresar->id = 'btnregresar';
        $btnregresar->name = 'btnregresar';
        $btnregresar->text = 'Regresar';
        $btnregresar->value = 'Regresar';
        $btnregresar->type = 'button';
        $btnregresar->class = 'btn btn-outline-secondary ';
        $btnregresar->icon = 'fa-solid fa-circle-left me-2';
        $this->agregaTools($this->ponBoton($btnregresar));

        $this->mixHtml('
            <fieldset class="col-md-1 col-xs-12">
                <div class="alert alert-info alert-dismissable" align="center">
                    <strong>NO CUENTA CON NIGUN INDICADOR</strong>
                </div>
            </fieldset>
        ');

        $this->mixScript('
            $("#btnregresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaPantalla",
                    prg0:1
                });
            });
        ');
    }

    private function pintaPantalla()
    {
        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? '' : ' AND pg.prgs_id= ' . $_SESSION[_S]['clasAdm-prg'];
        $spr_id = (empty($_SESSION[_S]['clasAdm-spr'])) ? '' : ' AND sb.sprs_id= ' . $_SESSION[_S]['clasAdm-spr'];

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'btn btn-outline-secondary me-1';
        $btnregresarindex->icon = 'fa-solid fa-circle-left me-2';
        $btnregresarindex->click = '$("nav").menusinpres("clickMenu");$("#clasAdminSeccionCombos").hide();';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnPdfPrimerSem = new stdClass();
        $btnPdfPrimerSem->id = 'btnPrimerSem';
        $btnPdfPrimerSem->name = 'btnPrimerSem';
        $btnPdfPrimerSem->text = 'Primer Semestre';
        $btnPdfPrimerSem->value = 'Primer Semestre';
        $btnPdfPrimerSem->type = 'button';
        $btnPdfPrimerSem->class = 'btn btn-outline-danger me-1';
        $btnPdfPrimerSem->icon = 'fa-solid fa-file-pdf me-2';

        $btnPdfSegundoSem = new stdClass();
        $btnPdfSegundoSem->id = 'btnSegundoSem';
        $btnPdfSegundoSem->name = 'btnSegundoSem';
        $btnPdfSegundoSem->text = 'Segundo Semestre';
        $btnPdfSegundoSem->value = 'Segundo Semestre';
        $btnPdfSegundoSem->type = 'button';
        $btnPdfSegundoSem->class = 'btn btn-outline-danger me-1';
        $btnPdfSegundoSem->icon = 'fa-solid fa-file-pdf me-2';

        $this->conectar();
        $this->setSql("
            SELECT gpo_id,ur_id, 1 capturandoTri,2 semestre2,
                FORMAT(((total2*100)/total11),2) porcentaje2,
                FORMAT(((total4*100)/total),2) porcentaje4,
                FORMAT(if(mem_modificado=0,0,((mem_alcTrimestre2*100)/mem_modificado)),2) ava2,
                FORMAT(if(mem_modificado=0,0,((mem_alcTrimestre4*100)/mem_modificado)),2) ava4,
                FORMAT(if(mem_modificado=0,0,((mem_alcanzado*100)/mem_modificado)),2) ava,ur_clave,ur_nombre,
                CASE WHEN ((total2*100)/total) AND est_semestre1 = 0  THEN 1
                WHEN ((total4*100)/total) AND est_semestre2 = 0  THEN 2
                ELSE 1 END AS capturandoTri2
            FROM (
                SELECT gpo_id,ur_id,fca_mirTrimestre1,fca_mirTrimestre2,fca_mirTrimestre3,fca_mirTrimestre4,
                SUM(contador) total,SUM(IF(a.usr_fecha<='2026-06-30',contador,NULL)) total11,
                SUM(IF(a.usr_fecha<='2026-06-30',cap_semestre1,0))total2,
                SUM(IF(a.usr_fecha<='2026-12-31',cap_semestre2,1)) total4,
                SUM(alc_trimestre1) mem_alcTrimestre1,SUM(alc_trimestre2) mem_alcTrimestre2,
                SUM(alc_trimestre3) mem_alcTrimestre3,SUM(alc_trimestre4) mem_alcTrimestre4,
                SUM(mem_alcanzado) mem_alcanzado,
                SUM(mem_modificado) mem_modificado,ur_clave,ur_nombre, est_semestre1, est_semestre2
                FROM (
                    SELECT 1 AS mem_id,1 contador, gpo_id, ur_id, ur_clave,ur_nombre,m.usr_fecha,
                    mco_alcanzado AS mem_alcanzado,mco_modificado AS mem_modificado,
                    alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                    mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                    mco_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                    cap_semestre1, cap_semestre2,tru_fechaApertura1, tru_fechaApertura4,
                    fca_mirTrimestre1,fca_mirTrimestre2,fca_mirTrimestre3,fca_mirTrimestre4,
                    est_semestre1, est_semestre2
                    FROM enc_ProductoFinalIndicador
                    INNER JOIN cat_MetaComponente USING (epfi_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,MAX(usr_fecha) usr_fecha,
                        MAX(IF (tri_id=1,mcot_original,NULL)) ori_trimestre1,
                        MAX(IF (tri_id=2,mcot_original,NULL)) ori_trimestre2,
                        MAX(IF (tri_id=3,mcot_original,NULL)) ori_trimestre3,
                        MAX(IF (tri_id=4,mcot_original,NULL)) ori_trimestre4,
                        MAX(IF (tri_id=1,mcot_alcanzado,NULL)) alc_trimestre1,
                        MAX(IF (tri_id=2,mcot_alcanzado,NULL)) alc_trimestre2,
                        MAX(IF (tri_id=3,mcot_alcanzado,NULL)) alc_trimestre3,
                        MAX(IF (tri_id=4,mcot_alcanzado,NULL)) alc_trimestre4,
                        MAX(IF (tri_id=1,mcot_modificado,NULL)) mod_trimestre1,
                        MAX(IF (tri_id=2,mcot_modificado,NULL)) mod_trimestre2,
                        MAX(IF (tri_id=3,mcot_modificado,NULL)) mod_trimestre3,
                        MAX(IF (tri_id=4,mcot_modificado,NULL)) mod_trimestre4,
                        MAX(IF (tri_id=2,mcot_capturaAvance,NULL)) cap_semestre1,
                        MAX(IF (tri_id=4,mcot_capturaAvance,NULL)) cap_semestre2
                        FROM cat_MetaComponenteTrimestre
                        GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id
                    ) m USING ( gpo_id, ur_id,prgs_id, sprs_id, epfi_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS est_semestre1, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS est_semestre2, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_mirTrimestre1,
                        MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_mirTrimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_mirTrimestre3,
                        MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_mirTrimestre4, MAX(IF(tri_id=1,tru_fechaApertura,NULL)) tru_fechaApertura1,
                        MAX(IF(tri_id=2,tru_fechaApertura,NULL)) tru_fechaApertura2, MAX(IF(tri_id=3,tru_fechaApertura,NULL)) tru_fechaApertura3,
                        MAX(IF(tri_id=4,tru_fechaApertura,NULL)) tru_fechaApertura4
                        FROM mir_TrimestreUr
                        WHERE nim_id=3
                        GROUP BY gpo_id, ur_id
                    ) f USING (gpo_id,ur_id,nim_id)
                    INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    WHERE gpo_id = ? and ur_id = ? and nim_id=3
                )a
            )b;
        ");
        $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
        $this->desconectar();
        $datoSem = $this->extraer();
        $semestre = $datoSem['semestre2'];
        $capturandoTri = $datoSem['capturandoTri'];
        $capturandoTri2 = $datoSem['capturandoTri2'];


        //Verificamos si realizo la captura del avance de componentes en el semestre 1
        $this->conectar();
        $this->setSql('
            SELECT mcot_capturaAvance,mcot_alcanzado
            FROM cat_MetaComponenteTrimestre 
            WHERE (gpo_id, ur_id, prgs_id, sprs_id, epfi_id) in (
                SELECT gpo_id, ur_id, prgs_id, sb.sprs_id, epfi_id
                        FROM cat_Subprogramas
                        INNER JOIN enc_UrSubprogramas sb USING(prgs_id, sprs_id)
                        INNER JOIN cat_ProductoFinal epf USING(pdf_id)
                        LEFT JOIN cat_MetaComponente USING(gpo_id, ur_id, prgs_id, sprs_id)
                        LEFT JOIN enc_ProductoFinalIndicador epfi USING(epfi_id)
                        LEFT JOIN cat_Indicador USING(nim_id, ind_id)
                        LEFT JOIN cat_FrecuenciaMedicion using(frm_id)
                        LEFT JOIN cat_Programas pg USING(prgs_id)
                        LEFT JOIN vin_UrProgramaMir upm USING(gpo_id, ur_id, prgs_id)
                        WHERE gpo_id = ? and ur_id = ?
                             AND epfi_id IS NOT NULL
                        GROUP BY epfi_id,sprs_id,prgs_id
                        ORDER BY prgs_id, sprs_id
            )
            AND tri_id = 2
            group by mcot_capturaAvance;
        ');
        $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
        $this->desconectar();
        $numRegT2 = $this->getNumeroRegistros();
        if ($numRegT2 == 1) {
            $datos = $this->extraer();
            $capturaAvanceComponente = (int)$datos['mcot_capturaAvance'];
            if ($capturaAvanceComponente == 1) {
                //Validamos si aun estamos en la fecha de captura para generar el PDF
                $this->conectar();
                $this->setSql('
                    SELECT 
                        CASE 
                            WHEN NOW() BETWEEN tru_fechaApertura AND tru_fechaCierre THEN 1
                            ELSE 0
                        END AS en_captura
                    FROM mir_TrimestreUr
                    WHERE nim_id = 3 AND tri_id = 2 and gpo_id = ? and ur_id = ?;
                ');
                $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
                $this->desconectar();
                $datos = $this->extraer();
                if ($datos['en_captura'] == 1) {
                    //Mostramos el boton de descarga
                    $btnPdfPrimerSem->click = '
                        var gpo= ' . $_SESSION[_S]['clasAdm-gpo'] . ';
                        var ur= ' . $_SESSION[_S]['clasAdm-ur'] . ';
                        var cap1= ' . $capturandoTri . ';
                        var cap2= ' . $capturandoTri2 . ';
                        var form = $("<form>")
                            .attr("action", "rep/doc/poa/avance/rep_AvanceComponente.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "gpo_id").val(gpo))
                            .append($("<input>").attr("type", "hidden").attr("name", "ur_id").val(ur))
                            .append($("<input>").attr("type", "hidden").attr("name", "capturandoTri").val(cap1))
                            .append( $("<input>").attr("type", "hidden").attr("name", "capturandoTri2").val(cap2));
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    ';
                    $this->agregaTools($this->ponBoton($btnPdfPrimerSem));
                }
            }
        }

        //Verificamos si realizo la captura del avance de componentes en el semestre 2
        $this->conectar();
        $this->setSql('
            SELECT mcot_capturaAvance,mcot_alcanzado
            FROM cat_MetaComponenteTrimestre 
            WHERE (gpo_id, ur_id, prgs_id, sprs_id, epfi_id) in (
                SELECT gpo_id, ur_id, prgs_id, sb.sprs_id, epfi_id
                        FROM cat_Subprogramas
                        INNER JOIN enc_UrSubprogramas sb USING(prgs_id, sprs_id)
                        INNER JOIN cat_ProductoFinal epf USING(pdf_id)
                        LEFT JOIN cat_MetaComponente USING(gpo_id, ur_id, prgs_id, sprs_id)
                        LEFT JOIN enc_ProductoFinalIndicador epfi USING(epfi_id)
                        LEFT JOIN cat_Indicador USING(nim_id, ind_id)
                        LEFT JOIN cat_FrecuenciaMedicion using(frm_id)
                        LEFT JOIN cat_Programas pg USING(prgs_id)
                        LEFT JOIN vin_UrProgramaMir upm USING(gpo_id, ur_id, prgs_id)
                        WHERE gpo_id = ? and ur_id = ?
                             AND epfi_id IS NOT NULL
                        GROUP BY epfi_id,sprs_id,prgs_id
                        ORDER BY prgs_id, sprs_id
            )
            AND tri_id = 4
            group by mcot_capturaAvance;
        ');
        $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
        $this->desconectar();
        $numRegT4 = $this->getNumeroRegistros();
        if ($numRegT4 == 1) {
            $datos = $this->extraer();
            $capturaAvanceComponente = (int)$datos['mcot_capturaAvance'];
            if ($capturaAvanceComponente == 1) {
                //Validamos si aun estamos en la fecha de captura para generar el PDF
                $this->conectar();
                $this->setSql('
                    SELECT 
                        CASE 
                            WHEN NOW() BETWEEN tru_fechaApertura AND tru_fechaCierre THEN 1
                            ELSE 0
                        END AS en_captura
                    FROM mir_TrimestreUr
                    WHERE nim_id = 3 AND tri_id = 4 and gpo_id = ? and ur_id = ?;
                ');
                $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
                $this->desconectar();
                $datos = $this->extraer();
                if ($datos['en_captura'] == 1) {
                    //Mostramos el boton de descarga
                    $btnPdfSegundoSem->click = '
                        var gpo= ' . $_SESSION[_S]['clasAdm-gpo'] . ';
                        var ur= ' . $_SESSION[_S]['clasAdm-ur'] . ';
                        var cap1= ' . $semestre . ';
                        var cap2= ' . $capturandoTri2 . ';
                        var form = $("<form>")
                            .attr("action", "rep/doc/poa/avance/rep_AvanceComponente.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "gpo_id").val(gpo))
                            .append($("<input>").attr("type", "hidden").attr("name", "ur_id").val(ur))
                            .append($("<input>").attr("type", "hidden").attr("name", "capturandoTri").val(cap1))
                            .append( $("<input>").attr("type", "hidden").attr("name", "capturandoTri2").val(cap2));
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    ';
                    $this->agregaTools($this->ponBoton($btnPdfSegundoSem));
                }
            }
        }

        $this->conectar();
        $this->setSql('
            SELECT ind_id, ind_nombreMIR, frm_nombre, epfi_id AS eri_id, prgs_id, sb.sprs_id, prgs_nombre,MAX(emi_id) emi_id, epf.pdf_id
            FROM cat_Subprogramas
            INNER JOIN enc_UrSubprogramas sb USING(prgs_id, sprs_id)
            INNER JOIN cat_ProductoFinal epf USING(pdf_id)
            LEFT JOIN cat_MetaComponente USING(gpo_id, ur_id, prgs_id, sprs_id)
            LEFT JOIN enc_ProductoFinalIndicador epfi USING(epfi_id)
            LEFT JOIN cat_Indicador USING(nim_id, ind_id)
            LEFT JOIN cat_FrecuenciaMedicion using(frm_id)
            LEFT JOIN cat_Programas pg USING(prgs_id)
            LEFT JOIN vin_UrProgramaMir upm USING(gpo_id, ur_id, prgs_id)
            WHERE gpo_id = ? and ur_id = ?
                ' . (isset($this->prm->prg0) && $this->prm->prg0 == 1 ? '' : $prg_id . $spr_id) . ' AND epfi_id IS NOT NULL
            GROUP BY epfi_id,sprs_id,prgs_id
            ORDER BY prgs_id, sprs_id;
        ');
        $this->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
        $this->desconectar();

        $htm = '<table  class="table table table-bordered table-hover">';

        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $clase = 'class="warning"';
        $oa = '';

        $htm .= '
            <tr>
                <th class="warning" align="center">VER</th>
                <th class="warning" align="center">ID INDICADOR</th>
                <th class="warning" align="center">NOMBRE INDICADOR</th>
                <th class="warning" align="center">FRECUENCIA</th>
            </tr>';
        while ($row = $this->extraer()) {

            if ($prg != $row['prgs_id']) {
                $htm .= '
                    <tr class="warning">
                        <td ' . $clase . ' align="left" colspan=' . ($row['emi_id'] != 9 ? '2' : '4') . '>PRG: ' . $row['prgs_id'] . ' - ' . $row['prgs_nombre'] . '</td>
                ';
                if ($row['emi_id'] != 9) {
                    $htm .= '<td align="left" colspan="2"><font color="red">MIR DEL PROGRAMA NO VALIDADA</font></td>';
                }
                $htm .= '</tr>';
            }
            $htm .= '
                <tr>
                    <td class="warning" align="center" colspan="1">
                        <button type="button" id="verMeta-' . $row['ind_id'] . '" icon="eye-open" title="Ver Meta del Indicador" class="verMeta btn btn-outline-secondary"  eri_id=' . $row['eri_id'] . ' pdf_id=' . $row['pdf_id'] . '>
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                    <td align="center" colspan="1">' . $row['ind_id'] . '</td>
                    <td align="left" colspan="1">' . $row['ind_nombreMIR'] . '</td>
                    <td align="center" colspan="1">' . $row['frm_nombre'] . '</td>
                </tr>
                <tr>
                    <th class="warning"></th>
                    <th class ="divMetas" id="metas' . $row['eri_id'] . '" colspan = 6 ></th>
                </tr>';

            $prg = $row['prgs_id'];
        }

        $htm .= '</table>';

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verMeta").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    eri_id:$(this).attr("eri_id"),
                    pdf_id:$(this).attr("pdf_id")
                });
            });
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->eri_id . '');
        $this->conectar();
        $this->setSql('
            SELECT mco_descripcion AS mem_descripcion, emi_id,
                (
                    SELECT mcot_original
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 1
                ) AS mem_trimestre1,
                (
                    SELECT mcot_original
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 2
                ) AS mem_trimestre2,
                (
                    SELECT mcot_original
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 3
                ) AS mem_trimestre3,
                (
                    SELECT mcot_original
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 4
                ) AS mem_trimestre4,
                (
                    SELECT mcot_modificado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 1
                ) AS mem_modTrimestre1,
                (
                    SELECT mcot_modificado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 2
                ) AS mem_modTrimestre2,
                (
                    SELECT mcot_modificado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 3
                ) AS mem_modTrimestre3,
                (
                    SELECT mcot_modificado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 4
                ) AS mem_modTrimestre4,
                (
                    SELECT mcot_alcanzado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 1
                ) AS mem_alcTrimestre1,
                (
                    SELECT mcot_alcanzado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 2
                ) AS mem_alcTrimestre2,
                (
                    SELECT mcot_alcanzado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 3
                ) AS mem_alcTrimestre3,
                (
                    SELECT mcot_alcanzado
                    FROM cat_MetaComponenteTrimestre cmct
                    WHERE cmct.gpo_id = cmc.gpo_id AND cmct.ur_id = cmc.ur_id AND cmct.prgs_id = cmc.prgs_id AND cmct.sprs_id = cmc.sprs_id AND cmct.epfi_id = cmc.epfi_id AND cmct.tri_id = 4
                ) AS mem_alcTrimestre4,
                hmc.mco_lb, hmc.mco_lbValor,
                IF(hmc.mco_modificado > 0, hmc.mco_modificado, hmc.mco_original)  AS mco_original, 
                hmc.epfi_id, vpm_id, umi_id, ind_nombreMIR, cmc.prgs_id, cmc.sprs_id, hmc.mcot_numeradorT2, hmc.mcot_numeradorT4
            FROM cat_Subprogramas
            INNER JOIN enc_UrSubprogramas USING(prgs_id, sprs_id)
            INNER JOIN cat_ProductoFinal epf USING(pdf_id)
            LEFT JOIN cat_MetaComponente cmc USING(gpo_id, ur_id, prgs_id, sprs_id)
            LEFT JOIN aux_HistoricoModificacionMetaComponente hmc USING(gpo_id, ur_id, prgs_id, sprs_id,epfi_id)
            LEFT JOIN enc_ProductoFinalIndicador epfi USING(epfi_id)
            LEFT JOIN cat_Indicador USING(nim_id, ind_id)
            LEFT JOIN vin_UrProgramaMir upm USING(gpo_id, ur_id, prgs_id)
            LEFT JOIN vin_ProgramasMir vpm USING (vpm_id)
            LEFT JOIN cat_UnidadMedidaIndicador USING (umi_id)
            WHERE cmc.gpo_id = ? AND cmc.ur_id = ? AND epfi_id = ?
            GROUP BY cmc.gpo_id, cmc.ur_id, epfi_id;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->eri_id,
        ];

        $this->consultar($params);
        while ($meta = $this->extraer()) {
            $meta['alcanzado'] = $meta['mem_alcTrimestre2'] + $meta['mem_alcTrimestre4'];
            $metaActualizada = $meta['mcot_numeradorT2'] + $meta['mcot_numeradorT4'];
            $this->mixHtml('
                <tr class="warning">
                    <th><td align="center" colspan="1">
                        <button type="button" id="verMetaInd" icon="eye-open" title="Ver Meta" class="verMeta btn btn-outline-secondary" epfi_id="' . $this->prm->eri_id . '" prgs_id = "' . $meta['prgs_id'] . '" sprs_id="' . $meta['sprs_id'] . '"' . ($meta['emi_id'] != 9 ? ' disabled' : '') . ' >
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td></th>
                    <th colspan = 4>
                        <table class="table table-hover table-bordered">
                            <tr>
                                <th class="warning" colspan = 13>META</th>
                            </tr><tr>
                                <th class="warning">Definición de variable:</th><th colspan = 11>' . $meta['mem_descripcion'] . '</th>
                            </tr><tr>
                                <th class="warning">Unidad de Medida:</th><th colspan = 11>' . $meta['umi_id'] . ' - ' . $meta['ind_nombreMIR'] . '</th>
                            </tr><tr>
                            <td class="warning">Linea de base:</td><td colspan = 5>AÑO ' . $meta['mco_lb'] . ' - META: ' . $meta['mco_lbValor'] . '</td>
                            <td class="warning">Valor anual: (actualizado/alcanzado)</td><td colspan = 2>(' . $metaActualizada . '/' . $meta['alcanzado'] . ') </td>
                            </tr>
                        </table>
                    </th>
                </tr>
            ');
        }
        $this->desconectar();

        $this->mixScript('
            $("#verMetaInd").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addModMeta",
                    gpo_id:' . $_SESSION[_S]['clasAdm-gpo'] . ',
                    ur_id:' . $_SESSION[_S]['clasAdm-ur'] . ',
                    prgs_id:$(this).attr("prgs_id"),
                    sprs_id:$(this).attr("sprs_id"),
                    epfi_id:$(this).attr("epfi_id")
                });
            });
        ');
    }

    private function addModMeta()
    {
        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? 'NULL' : $_SESSION[_S]['clasAdm-prg'];

        $this->setContenedor('divContenedorGeneralModal');

        $this->conectar();
        $this->setSql('
            SELECT ind_nombreMIR AS ind_nombre, ind_definicion, tiv_id as tiv, tiv_nombre,
            frm_id, umi.umi_id AS umi_id1, nim_id, ind_definicionMIR, IF(tiv_id=2,"S","C") tiv_id, ind_id, umi.umi_id, umi_nombre
            FROM cat_Indicador ind
            inner join enc_ProductoFinalIndicador  using(nim_id,ind_id)
            inner join cat_UnidadMedidaIndicador umi ON ind.umi_id=umi.umi_id
            inner join cat_MetaComponente using(epfi_id)
            LEFT JOIN cat_TipoValor using(tiv_id)
            WHERE nim_id = 3 and epfi_id=?;');

        $this->consultar([$this->prm->epfi_id]);
        $this->desconectar();
        $tiv_id = $this->extraer();
        $frm = $tiv_id['frm_id'];
        $tiv = $tiv_id['tiv_id'];
        $ind = $tiv_id['ind_id'];
        $umi_id = $tiv_id['umi_id1'];
        $umi_nombre = $tiv_id['umi_nombre'];
        $meta = new MetaComponente($this->prm);
        //Revisar como ajustar el valor del tri_id con el semestre en curso
        $this->prm->tri_id = 2;
        $metahistorico = new HistoricoModificacionMetaComponente($this->prm);
        $captura = new AccesoDatos();
        $captura->conectar();
        $captura->setSql('
            SELECT IF(tru_fechaApertura1<=NOW(), IF(NOW()<=fca_mirTrimestre1, 1, 0), 0) trimestre1,est_trimestre1 AS mem_estadoTrimestre1,
                IF(tru_fechaApertura2<=NOW(), IF(NOW()<=fca_mirTrimestre2, 1, 0), 0) trimestre2, est_trimestre2 AS mem_estadoTrimestre2 ,
                IF(tru_fechaApertura3<=NOW(), IF(NOW()<=fca_mirTrimestre3, 1, 0), 0) trimestre3, est_trimestre3 AS mem_estadoTrimestre3 ,
                IF(tru_fechaApertura4<=NOW(), IF(NOW()<=fca_mirTrimestre4, 1, 0), 0) trimestre4, est_trimestre4 AS mem_estadoTrimestre4,
            mem_capturado1, mem_capturado2, mem_capturado3, mem_capturado4
            FROM enc_ProductoFinalIndicador
            INNER JOIN cat_ProductoFinal USING(pdf_id)
            INNER JOIN (
                SELECT gpo_id, ur_id,prgs_id, sprs_id, epfi_id,
                MAX(IF (tri_id=1,mcot_capturaAvance,NULL)) mem_capturado1,
                MAX(IF (tri_id=2,mcot_capturaAvance,NULL)) mem_capturado2,
                MAX(IF (tri_id=3,mcot_capturaAvance,NULL)) mem_capturado3,
                MAX(IF (tri_id=4,mcot_capturaAvance,NULL)) mem_capturado4
                FROM cat_MetaComponenteTrimestre
                GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id
            ) m USING (epfi_id)
            INNER JOIN (
                SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                MAX(IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                MAX(IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_mirTrimestre1,
                MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_mirTrimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_mirTrimestre3,
                MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_mirTrimestre4, MAX(IF(tri_id=1,tru_fechaApertura,NULL)) tru_fechaApertura1,
                MAX(IF(tri_id=2,tru_fechaApertura,NULL)) tru_fechaApertura2, MAX(IF(tri_id=3,tru_fechaApertura,NULL)) tru_fechaApertura3,
                MAX(IF(tri_id=4,tru_fechaApertura,NULL)) tru_fechaApertura4
                FROM mir_TrimestreUr
                WHERE nim_id=3
                GROUP BY gpo_id, ur_id
            ) f USING (gpo_id,ur_id)
            WHERE gpo_id=? AND ur_id=?
            GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id;
        ');

        $captura->consultar([$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']]);
        $captura->desconectar();
        $datosCaptura = $captura->extraer();

        $AlcTrimestre1 = new stdClass();
        $AlcTrimestre1->id = 'AlcTrimestre1';
        $AlcTrimestre1->enDiv = true;
        $AlcTrimestre1->label = '1° Trimestre';
        $AlcTrimestre1->formato = 'numero';

        $banGuardar = false;
        $trimAbierto = '';

        if (!($datosCaptura['trimestre1'] == 1 && $datosCaptura['mem_estadoTrimestre1'] == 'A') || $frm != 1) {
            $tri1 = '<label>1° Trimestre </label><br/>' . $meta->getTrimestre(1, 'mcot_alcanzado');
        } else {
            $AlcTrimestre1->value = $meta->getTrimestre(1, 'mcot_alcanzado');
            $tri1 = $this->ponInput($AlcTrimestre1);
            $banGuardar = true;
            $trimAbierto = 1;
        }

        $AlcTrimestre2 = new stdClass();
        $AlcTrimestre2->required = true;
        $AlcTrimestre2->id = 'AlcTrimestre2';
        $AlcTrimestre2->enDiv = true;
        $AlcTrimestre2->label = '2° Trimestre';
        $AlcTrimestre2->formato = 'numero';

        if (!($datosCaptura['trimestre2'] == 1 && $datosCaptura['mem_estadoTrimestre2'] == 'A') || ($frm != 2 && $frm != 1)) {
            $tri2 = '<label>2° Trimestre </label><br/>' . $meta->getTrimestre(2, 'mcot_alcanzado');
        } else {
            $AlcTrimestre2->value = $meta->getTrimestre(2, 'mcot_alcanzado');
            $tri2 = $this->ponInput($AlcTrimestre2);
            $banGuardar = true;
            $trimAbierto = 2;
        }

        $AlcTrimestre3 = new stdClass();
        $AlcTrimestre3->required = true;
        $AlcTrimestre3->id = 'AlcTrimestre3';
        $AlcTrimestre3->enDiv = true;
        $AlcTrimestre3->label = '3° Trimestre';
        $AlcTrimestre3->formato = 'numero';

        if (!($datosCaptura['trimestre3'] == 1 && $datosCaptura['mem_estadoTrimestre3'] == 'A') || $frm != 1) {
            $tri3 = '<label>3° Trimestre </label><br/>' . $meta->getTrimestre(3, 'mcot_alcanzado');
        } else {
            $AlcTrimestre3->value = $meta->getTrimestre(3, 'mcot_alcanzado');
            $tri3 = $this->ponInput($AlcTrimestre3);
            $banGuardar = true;
            $trimAbierto = 3;
        }

        $AlcTrimestre4 = new stdClass();
        $AlcTrimestre4->required = true;
        $AlcTrimestre4->id = 'AlcTrimestre4';
        $AlcTrimestre4->enDiv = true;
        $AlcTrimestre4->label = '4° Trimestre';
        $AlcTrimestre4->formato = 'numero';

        if (!($datosCaptura['trimestre4'] == 1 && $datosCaptura['mem_estadoTrimestre4'] == 'A') || ($frm != 3 && $frm != 2)) {
            $tri4 = '<label>4° Trimestre </label><br/>' . $meta->getTrimestre(4, 'mcot_alcanzado');
        } else {

            $AlcTrimestre4->value = $meta->getTrimestre(4, 'mcot_alcanzado');
            $tri4 = $this->ponInput($AlcTrimestre4);
            $banGuardar = true;
            $trimAbierto = 4;
        }

        $valInp = true;

        $encValor = '<th>Valor anual</th>';
        $encValor2 = '<th>Valor línea Base</th>';
        $defnomInd = 'Definición de variable';
        $eanio = 'Año';
        $legendp = 'Línea Base';
        $legend = 'Valor Programado Anual';
        $aniopT0 = '<td width="25%">' . $meta->getOriginal() . '</td>';

        $vpT0 = '<td></td>';

        $vum = new AccesoDatos();
        $vum->conectar();
        $vum->setSql('
            SELECT vpm_id AS vum_id
            FROM vin_ProgramasMir
            WHERE gpo_id=?
            AND ur_id=? and prgs_id=?;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $prg_id,
        ];

        $vum->consultar($params);
        $vum->desconectar();

        $vinUrMir = $vum->extraer();
        $vpa = 0;
        if ($metahistorico->getModificado() > 0) {
            $vpa = $metahistorico->getModificado();
        } else {
            $metahistorico->getOriginal();
        }

        $form = new stdClass();
        $form->id = 'metaInd';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMeta';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'c';
        $form->datosExtra->nim_id = '3';
        $form->datosExtra->prgs_id = $meta->getPrgs_id();
        $form->datosExtra->sprs_id = $meta->getSprs_id();
        $form->datosExtra->epfi_id = $meta->getEpfi_id();
        $form->datosExtra->trimestreAbierto = (string) $trimAbierto;
        $form->datosExtra->ind_id = $ind;

        $form = new form($form);
        $form->mixHtml('
            <fieldset class="col-md-12 col-xs-12">
                <table class="table table-bordered">
                    <thead>
                        <tr style="background-color: #eaeaea">
                            <th colspan="11">Definición de variable</th>
                        </tr>
                        <tr>
                            <td colspan="11">' . $meta->getDescripcion() . '</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #eaeaea">
                            <th colspan="8">Unidad de medida</th>
                            <th colspan="8">Tipo de Cálculo</th>
                        </tr>
                        <tr>
                            <td colspan="8">' . $umi_id . ' - ' . $umi_nombre . '</td>
                            <td colspan="8">' . $tiv_id['tiv'] . ' - ' . $tiv_id['tiv_nombre'] . '</td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <legend><small>' . $legendp . '</small></legend>
                <table class="table table-bordered">
                    <thead> <tr style="background-color: #eaeaea"><th colspan="2">' . $eanio . '</th></tr> </thead>
                    <tbody> <tr><td>' . $metahistorico->getLb() . '</td><td>' . $metahistorico->getLbValor() . '</td></tr></tbody>
                </table>
                <legend><small>' . $legend . '</small></legend>
                <table class="table table-bordered">
                    <tbody> <tr><td>' . $vpa . '</td></tr></tbody>
                </table>
                <table class="table table-bordered">
                    <tbody>
                        <tr><td colspan="4" style="background-color: #eaeaea"><b>PROGRAMADO</b></td></tr>
                        <tr><td colspan="4" ><b>Numerador</b></td></tr>
                        <tr>
                            <th>1° Trimestre</th>
                            <th>2° Trimestre</th>
                            <th>3° Trimestre</th>
                            <th>4° Trimestre</th>
                        </tr>
                        <tr>
                            <td>' . $meta->getTrimestre(1, 'mcot_numerador') . '</td>
                            <td>' . $meta->getTrimestre(2, 'mcot_numerador') . '</td>
                            <td>' . $meta->getTrimestre(3, 'mcot_numerador') . '</td>
                            <td>' . $meta->getTrimestre(4, 'mcot_numerador') . '</td>
                        </tr>
                        <tr><td colspan="4"><b>Denominador</b></td></tr>
                        <tr>
                            <th>1° Trimestre</th>
                            <th>2° Trimestre</th>
                            <th>3° Trimestre</th>
                            <th>4° Trimestre</th>
                        </tr>
                        <tr>
                            <td>' . $meta->getTrimestre(1, 'mcot_denominador') . '</td>
                            <td>' . $meta->getTrimestre(2, 'mcot_denominador') . '</td>
                            <td>' . $meta->getTrimestre(3, 'mcot_denominador') . '</td>
                            <td>' . $meta->getTrimestre(4, 'mcot_denominador') . '</td>
                        </tr>
                        <tr><td colspan="4" style="background-color: #eaeaea"><b>ACTUALIZADO</b></td></tr>
                        <tr><td colspan="4" ><b>Numerador</b></td></tr>
                        <tr>
                            <th>1° Trimestre</th>
                            <th>2° Trimestre</th>
                            <th>3° Trimestre</th>
                            <th>4° Trimestre</th>
                        </tr>
                        <tr>
                            <td>' . $metahistorico->getNumeradorT1() . '</td>
                            <td>' . $metahistorico->getNumeradorT2() . '</td>
                            <td>' . $metahistorico->getNumeradorT3() . '</td>
                            <td>' . $metahistorico->getNumeradorT4() . '</td>
                        </tr>
                        <tr><td colspan="4"><b>Denominador</b></td></tr>
                        <tr>
                            <th>1° Trimestre</th>
                            <th>2° Trimestre</th>
                            <th>3° Trimestre</th>
                            <th>4° Trimestre</th>
                        </tr>
                        <tr>
                            <td>' . $metahistorico->getDenominadorT1() . '</td>
                            <td>' . $metahistorico->getDenominadorT2() . '</td>
                            <td>' . $metahistorico->getDenominadorT3() . '</td>
                            <td>' . $metahistorico->getDenominadorT4() . '</td>
                        </tr>
                        <tr><td colspan="4" style="background-color: #eaeaea"><b>ALCANZADO</b></td></tr>
                        <tr>
                            <td>' . $tri1 . '</td>
                            <td>' . $tri2 . '</td>
                            <td>' . $tri3 . '</td>
                            <td>' . $tri4 . '</td>
                            <tr>
                            </tr>
                        <tr>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        ');

        $this->mixHtml('
            <div id="myModal" class="modal fade modal-al80porciento" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">NIVEL COMPONENTE</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $form->ponForm() . '</div>
                            <div class="modal-footer">
                            ' . (($banGuardar) ? '
                                <button type="submit" class="btn btn-outline-success" id="saveMeta" form = "metaInd">Guardar</button>
                            ' : '') . '
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });
        ');
    }

    private function abcMeta()
    {

        $datosMeta = new MetaComponente();
        $datosMeta->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $datosMeta->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $datosMeta->setPrgs_id($this->prm->prgs_id);
        $datosMeta->setSprs_id($this->prm->sprs_id);
        $datosMeta->setEpfi_id($this->prm->epfi_id);
        $datosMeta->select();

        $valTrimestre = '';
        $semestre = '';

        switch ($this->prm->trimestreAbierto) {
            case 1:
                $this->prm->Alcanzado = $this->prm->AlcTrimestre1 + $datosMeta->getTrimestre(2, 'mcot_alcanzado') + $datosMeta->getTrimestre(3, 'mcot_alcanzado') + $datosMeta->getTrimestre(4, 'mcot_alcanzado');
                $valTrimestre = $this->prm->AlcTrimestre1;
                $semestre = 1;
                break;
            case 2:
                $this->prm->Alcanzado = $datosMeta->getTrimestre(1, 'mcot_alcanzado') + $this->prm->AlcTrimestre2 + $datosMeta->getTrimestre(3, 'mcot_alcanzado') + $datosMeta->getTrimestre(4, 'mcot_alcanzado');
                $valTrimestre = $this->prm->AlcTrimestre2;
                $semestre = 1;
                break;
            case 3:
                $this->prm->Alcanzado = $datosMeta->getTrimestre(1, 'mcot_alcanzado') + $datosMeta->getTrimestre(2, 'mcot_alcanzado') + $this->prm->AlcTrimestre3 + $datosMeta->getTrimestre(4, 'mcot_alcanzado');
                $valTrimestre = $this->prm->AlcTrimestre3;
                $semestre = 2;
                break;
            case 4:
                $this->prm->Alcanzado = $datosMeta->getTrimestre(1, 'mcot_alcanzado') + $datosMeta->getTrimestre(2, 'mcot_alcanzado') + $datosMeta->getTrimestre(3, 'mcot_alcanzado') + $this->prm->AlcTrimestre4;
                $valTrimestre = $this->prm->AlcTrimestre4;
                $semestre = 2;
                break;
        }

        $this->conectar();
        $ip = ($this->getIp() != '::1') ? $this->getIp() : '127.0.0.1';
        $this->setSql('
            SET @mip = INET_ATON(?),
            @usr_id = ?,
            @bit_observacion = "Captura de avance MIR Componente del trimestre ' . $this->prm->trimestreAbierto . '"
        ');
        $paramsb = [
            $ip,
            $_SESSION[_S]['usr_id'],
        ];
        $this->consultar($paramsb);
        $this->setSql('
            UPDATE cat_MetaComponenteTrimestre SET mcot_capturaAvance = 1, mcot_alcanzado = ' . $valTrimestre . '
            WHERE gpo_id = ? AND ur_id = ? AND prgs_id=? AND sprs_id = ? AND epfi_id = ? AND tri_id = ?;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->prgs_id,
            $this->prm->sprs_id,
            $this->prm->epfi_id,
            $this->prm->trimestreAbierto,
        ];
        $this->consultar($params);
        $this->setSql('
            UPDATE cat_MetaComponente SET mco_alcanzado = ' . $this->prm->Alcanzado . '
            WHERE gpo_id = ? AND ur_id = ? AND prgs_id=? AND sprs_id = ? AND epfi_id = ?;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->prgs_id,
            $this->prm->sprs_id,
            $this->prm->epfi_id,
        ];
        $this->consultar($params);
        $this->desconectar();

        $this->mixScript('
            notify("Guardado exitosamente");
            $("#myModal,#modalDeTablatbl-metasIndicador").modal("hide");
            comunica("' . $this->pantalla . '",{
                metodo:"pintaPantalla"});
            $("#verMeta-"+' . $this->prm->ind_id . ').click();
        ');
    }
}

new ControladorMirAvanceComponente();
