<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);
/*--------------------------------------------------------------------------------------------------------
Objetivo: MATRIZ DE INDICADOR DE RESULTADOS, por nivel (fin, proposito, componente y actividad), agrega supuestos e indicadores
Elaboró : Anuar López Morgan, Zuli D. López Barbosa, Soledad López Velasco
--------------------------------------------------------------------------------------------------------*/

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/Indicador.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaIndicadorPed.php';
require_once dirname(__FILE__) . '/../../ent/HistorialMensajes.php';
set_time_limit(0);
class ControladorMirAvanceFin extends WebResponse
{
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        #si tiene el permiso de lectura le permitimos pasar

        switch ($this->metodo) {
            case 'pintaPantalla':
                $this->pintaPantalla();
                break;
            case 'verMetas':
                $this->verMetas();
                break;
            case 'addModMeta':
                $this->addModMeta();
                break;
            case 'abcMeta':
                $this->abcMeta();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $this->mixStyle('
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .modal-header{padding:8px!important; background: #E5D299; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:black; opacity: 0.8;};
        ');

        $clasAdmin = new stdClass();
        $clasAdmin->comboUe = false;
        $clasAdmin->comboSpr = false;
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
            }$("#contenedor").html("<p class=\'text-danger\'>Seleccionar un Programa</p>");
        ';

        $this->mixScript('$("#clasAdminSeccionCombos").show();'
            . ' $("#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();' . ClasificacionAdministrativa::muestraClasAdmin($clasAdmin));
    }

    private function pintaPantalla()
    {
        $prg_id = (empty($_SESSION[_S]['clasAdm-prg'])) ? '' : $_SESSION[_S]['clasAdm-prg'];
        $params = [
            ':gpo_id'  => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id'   => $_SESSION[_S]['clasAdm-ur'],
            ':prgs_id' => $prg_id,
        ];

        $this->conectar();
        $this->setSql('
            SELECT pm.prgs_id, prgs_nombre, epg_id,epo_id, pm.emi_id, s.ind_id, s.nim_id, s.ind_nombre, s.frm_nombre,
                count(distinct ind_fin) indicadoresFin,  IF(ISNULL(MAX(metaFin)),0,MAX(metaFin)) metaFin,obj_nombre fin,mir_resumenNarrativo
            FROM cat_Mir M
            INNER JOIN vin_ProgramasMir USING (mir_id)
            INNER JOIN vin_UrProgramaMir pm USING (vpm_id)
            INNER JOIN cat_Programas pr ON pm.prgs_id=pr.prgs_id
            INNER JOIN enc_UrSubprogramas eur ON eur.gpo_id=pm.gpo_id AND eur.ur_id=pm.ur_id AND eur.prgs_id=pm.prgs_id AND eur.sprs_id=pm.sprs_id
            INNER JOIN (
                SELECT epg_id, gpo_id, ur_id, prg_id as prgs_id, p.epo_id, obj_nombre
                FROM enc_Programatico e
                INNER JOIN vin_Programa v ON e.prg_id=v.prgs_id
                INNER JOIN enc_PedObjetivo p ON p.epo_id=v.epo_id
                INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
                WHERE gpo_id= :gpo_id AND ur_id= :ur_id
                AND prg_id= :prgs_id AND spr_id=00 AND py_id=00 AND oa_id=000
            )e ON e.gpo_id= eur.gpo_id AND e.ur_id=eur.ur_id AND e.prgs_id=eur.prgs_id
            LEFT JOIN (
                SELECT vpm_id, count(mtf_id) metaFin, nim_id, ind_id
                FROM vin_ProgramaMirIndicador
                INNER JOIN cat_MetaFin M USING (pmi_id)
                WHERE sts_id=1 AND mtf_trimestreCierre=4
                GROUP BY  ind_id,mtf_id, vpm_id,nim_id
            )m USING(vpm_id)
            LEFT JOIN (
                SELECT vpm_id,concat(nim_id,ind_id) ind_fin, nim_id, ind_id, ind_nombre, fr.frm_nombre
                FROM vin_ProgramaMirIndicador v
                INNER JOIN cat_Indicador i
                INNER JOIN cat_FrecuenciaMedicion fr ON i.frm_id = fr.frm_id
                USING(nim_id,ind_id)
                WHERE nim_id=1 AND v.trim_4=1
            )s USING(vpm_id)
            WHERE pm.gpo_id= :gpo_id AND pm.ur_id=:ur_id
            AND pm.prgs_id=:prgs_id
            GROUP BY pm.prgs_id,M.mir_id, s.ind_id, s.nim_id, pm.emi_id;
        ');
        $this->consultar($params);
        $this->desconectar();
        $htm = '<table  class="table table table-bordered table-hover">';

        $prg = '';
        $clase = 'class="warning"';

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
                        <button type="button" id="verMeta-' . $row['ind_id'] . '" icon="eye-open" title="Ver Meta del Indicador" class="verMeta btn btn-outline-secondary" ind_id=' . $row['ind_id'] . '>
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                    <td align="center" colspan="1">' . $row['ind_id'] . '</td>
                    <td align="left" colspan="1">' . $row['ind_nombre'] . '</td>
                    <td align="center" colspan="1">' . $row['frm_nombre'] . '</td>
                </tr>
                <tr >
                    <th class="warning" ></th>
                    <th class ="divMetas" id="metas' . $row['ind_id'] . '" colspan = "4" ></th>
                </tr>
            ';

            $prg = $row['prgs_id'];
        }

        $htm .= '</table>';

        $this->mixHtml($htm);
        $this->mixScript('
            $(".verMeta").click(function(){
                $(".divMetas").html("");
                comunica("' . $this->pantalla . '",{
                    metodo:"verMetas",
                    prg_id:"' . $prg_id . '",
                    ind_id:$(this).attr("ind_id")
                });
            });
        ');
    }

    private function verMetas()
    {
        $this->setContenedor('metas' . $this->prm->ind_id . '');

        $this->conectar();
        $this->setSql('
            SELECT DISTINCT upm.prgs_id, mtf_descripcion,
                mf.mtf_anio,mf.mtf_valor, mf.mtf_original,
                vpm.vpm_id,umi_id,umi_nombre,pmi_id, ind_id, upm.emi_id,
                (
                select mtft_original
                FROM cat_MetaFinTrimestre mt
                WHERE mf.pmi_id = mt.pmi_id and tri_id =4
                ) AS mem_trimestre4,
                (
                select mtft_modificado
                FROM cat_MetaFinTrimestre mt
                WHERE mf.pmi_id = mt.pmi_id and tri_id =4
                ) AS mem_modificado4,
                (
                select mtft_alcanzado
                FROM cat_MetaFinTrimestre mt
                WHERE mf.pmi_id = mt.pmi_id and tri_id =4
                ) AS mem_alcanzado4
            FROM cat_MetaFin mf
            LEFT JOIN aux_HistoricoModificacionMetaFin USING(pmi_id)
            LEFT JOIN vin_ProgramaMirIndicador USING(pmi_id)
            LEFT JOIN cat_Indicador i using(nim_id,ind_id)
            LEFT JOIN cat_UnidadMedidaIndicador USING(umi_id)
            LEFT JOIN vin_ProgramasMir vpm USING (vpm_id)
            LEFT JOIN vin_UrProgramaMir upm on vpm.vpm_id=upm.vpm_id and vpm.prgs_id=upm.prgs_id
            WHERE upm.gpo_id= ? and upm.ur_id= ?
            and upm.prgs_id= ? and nim_id=1 and ind_id= ?
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $this->prm->prg_id,
            $this->prm->ind_id,
        ];
        $this->consultar($params);
        $registros = $this->getNumeroRegistros();
        if ($registros == 0) {
            $this->mixScript('notify("No existe registro de metas","warning")');
        }
        $numeroMeta = 0;
        $mem_id = 0;
        while ($meta = $this->extraer()) {
            $numeroMeta++;
            $this->mixHtml('
                <tr class="warning">
                    <th>
                        <td align="center" colspan="1">
                            <button type="button" id="verMetaInd" icon="eye-open" title="Ver Meta" class="verMetaInd btn btn-outline-secondary" vpm_id=' . $meta['vpm_id'] . ' ind_id=' . $meta['ind_id'] . ' pmi_id=' . $meta['pmi_id'] . ' prg_id = ' . $this->prm->prg_id . ($meta['emi_id'] != 9 ? ' disabled' : '') . ' >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </th>
                    <th colspan = 4>
                        <table class="table table-hover table-bordered">
                            <tr>
                                <th class="warning" colspan = 13>META ' . $numeroMeta . '</th>
                            </tr><tr>
                                <th class="warning">Descripcion:</th><th colspan = 11>' . $meta['mtf_descripcion'] . '</th>
                            </tr><tr>
                                <th class="warning">Unidad de Medida:</th><th colspan = 11>' . $meta['umi_id'] . ' - ' . $meta['umi_nombre'] . '</th>
                            </tr><tr>
                            <td class="warning">Linea de base:</td><td colspan = 5>AÑO ' . $meta['mtf_anio'] . ' - META: ' . $meta['mtf_valor'] . '</td>
                            <td class="warning">Valor deseado:</td><td colspan = 2>(' . $meta['mtf_original'] . ') </td>
                            </tr>
                        </table>
                    </th>
                </tr>
            ');

            $mem_id = $meta['pmi_id'];
            if ($mem_id != $meta['pmi_id']) {
                $numeroMeta = 0;
            }
        }
        $this->desconectar();

        $this->mixScript('
            $(".verMetaInd").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"addModMeta",
                    pmi_id:$(this).attr("pmi_id"),
                    vpm_id:$(this).attr("vpm_id"),
                    ind_id:$(this).attr("ind_id"),
                    prgs_id:$(this).attr("prg_id")
                });
            });
        ');
    }

    public function consultaInd()
    {
        $this->conectar();
        $this->setSql('
            SELECT ind_nombre, ind_definicion, frm_id, umi_id, nim_id, ind_definicionMIR, IF(tiv_id=2,"S","C") tiv_id,umi_id, umi_nombre
            FROM cat_Indicador
            inner join vin_ProgramaMirIndicador using(nim_id,ind_id)
            inner join cat_UnidadMedidaIndicador using(umi_id)
            inner join cat_MetaFin using(pmi_id)
            WHERE nim_id = 1 and pmi_id= ? and vpm_id= ?;
        ');
        $params = [
            $this->prm->pmi_id,
            $this->prm->vpm_id,
        ];
        $this->consultar($params);
        $tiv_id = $this->extraer();
        $this->desconectar();
        
        return $tiv_id;
    }

    private function consultaHistorico()
    {
        $historicometa = new AccesoDatos();
        $historicometa->conectar();
        $historicometa->setSql('
            SELECT h.pmi_id, h.mtf_id, h.tri_id, h.mtf_modificado, m.mtf_descripcion, m.mtf_valor,
            m.mtf_anio, m.mtf_original, m.mtf_trimestreCierre
            FROM aux_HistoricoModificacionMetaFin h
            JOIN cat_MetaFin m ON h.pmi_id = m.pmi_id AND h.mtf_id = m.mtf_id
            WHERE h.pmi_id = ?
            AND h.mtf_id = 1
            AND h.tri_id = 4;
        ');
        $historicometa->consultar([$this->prm->pmi_id]);
        $historicometa->desconectar();

        return $historicometa->extraer();
    }

    private function consultaCaptura()
    {
        $captura = new AccesoDatos();
        $captura->conectar();
        $captura->setSql('
            SELECT gpo_id,ur_id,
            IF(tru_fechaApertura<=NOW(),IF(NOW()<=tru_fechaCierre,1,0),0) trimestre4,etr_id1 AS estadotrimestre
            FROM mir_TrimestreUr
            WHERE gpo_id= ? AND ur_id= ?
            AND nim_id=1 AND tri_id=4
            GROUP BY gpo_id,ur_id;
        ');
        $params = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
        ];
        $captura->consultar($params);
        $captura->desconectar();

        return $captura->extraer();
    }

    private function addModMeta()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $tiv_id = $this->consultaInd();
        $frm = $tiv_id['frm_id'];
        $tiv = $tiv_id['tiv_id'];
        $umi_id = $tiv_id['umi_id'];
        $umi_nombre = $tiv_id['umi_nombre'];

        $this->prm->nim_id = $tiv_id['nim_id'];
        $this->prm->mem_id = 1;
        $this->prm->ur = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        $meta = new MetaIndicadorPed($this->prm);

        $datosHistorico = $this->consultaHistorico();
        $datosCaptura = $this->consultaCaptura();

        $banGuardar = false;
        $trimAbierto = '';

        $alcTrimestre4 = new stdClass();
        $alcTrimestre4->required = true;
        $alcTrimestre4->id = 'AlcTrimestre4';
        $alcTrimestre4->enDiv = true;
        $alcTrimestre4->formato = 'numero';

        if (!($datosCaptura['trimestre4'] == 1 && $datosCaptura['estadotrimestre'] == 'A') || $frm < 3) {
            $tri4 = $meta->getAlcanzado();
        } else {
            $alcTrimestre4->value = $meta->getAlcanzado();
            $tri4 = $this->ponInput($alcTrimestre4);
            $banGuardar = true;
            $trimAbierto = 4;
        }

        $trLineaBase = '
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Año</th>
                        <th>Valor Anual</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . $meta->getAnio() . '</td>
                        <td>' . $meta->getValor() . '</td>
                    </tr>
                </tbody>
            </table>
        ';

        $trProgramado = '
            <tr>
                <th>1° Trimestre</th>
                <th>2° Trimestre</th>
                <th>3° Trimestre</th>
                <th>4° Trimestre</th>
                <th>Valor Indicador</th>
            </tr>
            <tr>
                <td> 0.00 </td>
                <td> 0.00 </td>
                <td> 0.00 </td>
                <td>' . $meta->getOriginal() . '</td>
                <td>' . 0 + 0 + 0 + $meta->getOriginal() . '</td>
            </tr>
        ';

        $trActualizado = '
            <tr>
                <th>1° Trimestre</th>
                <th>2° Trimestre</th>
                <th>3° Trimestre</th>
                <th>4° Trimestre</th>
                <th>Valor Indicador</th>
            </tr>
            <tr>
                <td> 0.00</td>
                <td> 0.00</td>
                <td> 0.00</td>
                <td>' . (isset($datosHistorico['mtf_modificado']) ? $datosHistorico['mtf_modificado'] : $meta->getModificado()) . '</td>
                <td>' . (isset($datosHistorico['mtf_modificado']) ? $datosHistorico['mtf_modificado'] : $meta->getModificado()) . '</td>
            </tr>
        ';

        $trAlcanzado = '
            <tr>
                <th>1° Trimestre </th>
                <th>2° Trimestre </th>
                <th>3° Trimestre </th>
                <th>4° Trimestre </th>
                <th>Valor Indicador</th>
            </tr>
            <tr>
                <td> 0.00 </td>
                <td> 0.00 </td>
                <td> 0.00 </td>
                <td>' . $tri4 . '</td>
                <td>' . $meta->getAlcanzado() . '</td>
            </tr>
        ';

        $form = $this->armaFormularioMetaFin($meta, $tiv, $trimAbierto);
        $form->mixHtml('
            <fieldset class="col-md-12 col-xs-12">
                <table class="table table-bordered">
                    <thead>
                        <tr style="background-color: #eaeaea">
                            <th>Definición de variable</th>
                        </tr>
                        <tr>
                            <td>' . $meta->getDescripcion() . '</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #eaeaea">
                            <th>Unidad de medida</th>
                        </tr>
                        <tr>
                            <td>' . $umi_id . ' - ' . $umi_nombre . '</td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset class="col-md-12 col-xs-12">
                <legend><small>Línea Base</small></legend>
                ' . $trLineaBase . '
                <table class="table table-bordered">
                    <tbody>
                        <tr><td style="background-color: #eaeaea" colspan="7"><b>PROGRAMADO</b></td></tr>
                        ' . $trProgramado . '
                        <tr><td style="background-color: #eaeaea" colspan="7"><b>ACTUALIZADO</b></td></tr>
                        ' . $trActualizado . '
                        <tr><td style="background-color: #eaeaea" colspan="7"><b>ALCANZADO</b></td></tr>
                        ' . $trAlcanzado . '
                    </tbody>
                </table>
            </fieldset>
        ');

        $this->mixHtml($this->construyeModalMetaFin($form, $banGuardar));

        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").remove();
            });
        ');
    }

    private function armaFormularioMetaFin($meta, $tiv, $trimAbierto)
    {
        $form = new stdClass();
        $form->id = 'metaInd';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMeta';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'c';
        $form->datosExtra->nim_id = (string) 1;
        $form->datosExtra->pmi_id = $this->prm->pmi_id;
        $form->datosExtra->mtf_id = (string) 1;
        $form->datosExtra->trimestreAbierto = (string) $trimAbierto;
        $form->datosExtra->tipoCalendario = $tiv;
        $form->datosExtra->descripcion = $meta->getDescripcion();
        $form->datosExtra->anio = $meta->getAnio();
        $form->datosExtra->valor = $meta->getValor();

        return new form($form);
    }

    private function construyeModalMetaFin($form, $banGuardar)
    {
        $botonGuardar = $banGuardar
            ? '<button type="submit" class="btn btn-outline-primary" id="saveMeta" form="metaInd">Guardar</button>'
            : '';

        return '
            <div id="myModal" class="modal fade modal-al80porciento">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">NIVEL FIN</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $form->ponForm() . '</div>
                            <div class="modal-footer">
                                ' . $botonGuardar . '
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ';
    }

    private function abcMeta()
    {

        $datosMeta = new MetaIndicadorPed();
        $datosMeta->setPmi_id($this->prm->pmi_id);
        $datosMeta->setMtf_id(1);
        $datosMeta->select();

        $this->prm->trimestreCierre = $this->prm->trimestreAbierto;

        if ($this->prm->trimestreCierre == 4) {
            $this->prm->Alcanzado = $this->prm->AlcTrimestre4;
            $this->prm->ori_trimestre4 = $this->prm->AlcTrimestre4;
            if ($this->prm->tipoCalendario == 'S') {
                $this->prm->original = $this->prm->ori_trimestre4;
            } else {
                if ($this->prm->ori_trimestre4 > 0) {
                    $this->prm->original = $this->prm->ori_trimestre4;
                }
            }
        }

        $this->conectar();
        $this->setSql('
            UPDATE cat_MetaFin SET mtf_alcanzado =' . $this->prm->Alcanzado . ', mtf_capturaAvance = 1
            WHERE pmi_id =? AND mtf_id=?
        ');
        $this->consultar([$this->prm->pmi_id, $this->prm->mtf_id]);
        $this->desconectar();

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $bitacora = new AccesoDatos();
        $bitacora->conectar();
        $bitacora->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_MetaFin (
            bit_observacion, pmi_id, mtf_id, mtf_descripcion, mtf_anio, mtf_valor, mtf_original, mtf_modificado, mtf_alcanzado, mtf_tipoCalendario,
            mtf_trimestreCierre, tri_id, mtft_original, mtft_modificado, mtft_alcanzado, usr_ip, usr_id)
            VALUES ("INSERCION realizada desde el Controlador Avance Fin", ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, INET_ATON(?),?);
        ');
        $params = [
            $this->prm->pmi_id,
            $this->prm->mtf_id,
            $this->prm->descripcion,
            $this->prm->anio,
            $this->prm->valor,
            $datosMeta->getOriginal(),
            $datosMeta->getModificado(),
            $datosMeta->getAlcanzado(),
            $datosMeta->getTipoCalendario(),
            $this->prm->trimestreCierre,
            4,
            $this->prm->ori_trimestre4,
            0,
            0,
            $ip,
            $_SESSION[_S]['usr_id']
        ];
        $bitacora->consultar($params);
        $bitacora->desconectar();

        $this->mixScript('
            notify("Guardado exitosamente");
            $("#myModal,#modalDeTablatbl-metasIndicador").modal("hide");
        ');
    }
}
if (isset($_SESSION[_S]['prm']['A']['10']['5']['1']) || isset($_SESSION[_S]['prm']['A']['10']['5']['2'])) {
    $controlador = new ControladorMirAvanceFin();
} else {
    $this->sinPermisos();
}
