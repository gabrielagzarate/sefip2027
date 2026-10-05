<?php
/**
 * ControladorOPs
 *
 * Permite consultar y generar el archivo excel de las OPs pagadas y generadas
 * de acuerdo al mes seleccionado
 *
 * PHP version 5
 *
 * @category  Consulta
 * @package   Package
 * @author    Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * @copyright 2017 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 * @license   http://www.fsf.org/licensing/licenses/gpl.txt GPL 2 or later
 * @link      egresos.finanzasoaxaca.gob.mx/sinpres2017/
 */
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

/**
 * Clase ControladorOPs
 *
 * Permite consultar y generar el archivo excel de las OPs pagadas y generadas
 * de acuerdo al mes seleccionado
 *
 * PHP version 5
 *
 * @category  Class
 * @package   Package
 * @author    Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * @copyright 2017 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 * @license   http://www.fsf.org/licensing/licenses/gpl.txt GPL 2 or later
 * @version   SVN:2017-02-10
 * @link      egresos.finanzasoaxaca.gob.mx/sinpres2017/
 */
class ControladorOPs extends WebResponse
{
    private $lectura;
    private $exportacion;
    private $gpo;
    private $ur;
    private $nur;
    private $ue;

    /**
     * Constructor
     *
     * @return Json
     */
    public function __construct()
    {
        //Mandar a llamar Constructor Base
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['10']['11']['1']) ? 1
        : 0;
        $this->exportacion = isset($_SESSION[_S]['prm']['C']['10']['11']['47'])
        ? 1 : 0;

        $this->gpo = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur = $_SESSION[_S]['clasAdm-ur'];
        $this->nur = $_SESSION[_S]['ur_nombre'];

        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue = false;
        }

        switch ($this->metodo) {
            case '_llenaTablaConsultaOps':
                $this->_llenaTablaConsultaOps();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    /**
     * Función principal del Controlador
     *
     * @return null
     */
    public function main()
    {
        $btn_expOps = "";
        $ur = $this->ur;
        $nur = $this->nur;
        $secretaria = $ur . ' - ' . $nur;

        if ($this->exportacion) {
            $btn_expOps = '
                <button
                    id="btn_expOps"
                    type="button"
                    ejerc="0"
                    sec="' . $secretaria . '"
                    form="frm_ConsultaOps"
                    class="btn btn-outline-success"
                    title="EXPORTAR A EXCEL LAS OPS GENERADAS Y PAGADAS">
                    <i class="fa-solid fa-file-excel"></i>
                    Excel
                </button>
            ';
        }

        //Combo mes
        $comboMes = new stdClass();
        $comboMes->id = 'msc_id';
        $comboMes->class = 'msc_id';
        $comboMes->sql = 'SELECT msc_id,msc_nombre FROM cat_MesContable WHERE msc_id NOT IN (00,13);';
        $comboMes->comoAtr = 'msc_nombre';
        $comboMes->change = '
            comunica("' . $this->pantalla . '", {
                metodo: "_llenaTablaConsultaOps",
                mes: $(this).val()
            });
            $("#btn_expOps").attr("ejerc",$(this).val());
        ';
        $tablaConsultaOps = new stdClass();
        $tablaConsultaOps->id = 'tablaConsultaOps';
        $tablaConsultaOps->titulo = "OPS GENERADAS Y PAGADAS <span id='nomMes'></span>";
        $tablaConsultaOps->comunica = new stdClass();
        $tablaConsultaOps->comunica->pantalla = $this->pantalla;
        $tablaConsultaOps->comunica->metodo = '_llenaTablaConsultaOps';
        $tablaConsultaOps->comunica->mes = '{$("#nomMes").html()}';
        $tablaConsultaOps->filasAmostrar = array(50, 100, 120, 200, 250);
        $tablaConsultaOps->numFilas = 50;
        //Encabezados
        $tr = array();

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'UR_ID';
        array_push($tr, $th);

        $th = new stdClass();
        $th->aling = 'left';
        $th->width = '5%';
        $th->texto = 'CLC_ID';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'UE_ID';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'CLC_CERECO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '15%';
        $th->texto = 'CLAVE_FINANCIAMIENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '25%';
        $th->texto = 'ESTADO_CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '40%';
        $th->texto = 'CLC_CONCEPTO';
        array_push($tr, $th);

        //Se inicia el cuerpo de la Tabla
        $trbody = array();

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ur_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'clc_cereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'claveFinanciamiento';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'estadoClc';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'clc_concepto';
        array_push($trbody, $td);

        $tablaConsultaOps->encabezado = array($tr);
        $tablaConsultaOps->cuerpo = ($trbody);

        //Se agregan los elementos que usaremos en la barra de botones
        $combo_mes = '<div class="col-md-3 me-1">' . $this->ponCombo($comboMes) . '</div>';
        //Se pintan los elementos en patalla, en la zona destinada a la barra de botones
        $this->agregaTools($combo_mes . $btn_expOps);

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaConsultaOps) . '</div>');

        $this->mixScript(
            '$("#btn_expOps").click(function()
            {
                if (($("#msc_id").val() != "")) {
                    var form = $("<form>")
                    .attr("action", "rep/excel/scg/reporte/ConsultaOPs.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append(
                        $("<input>")
                        .attr("type", "hidden")
                        .attr("name","mes_id")
                        .attr(
                            "value",
                            $("#btn_expOps").attr("ejerc")
                        )
                    )
                    .append(
                        $("<input>")
                        .attr("type", "hidden")
                        .attr("name","nom_sec")
                        .attr(
                            "value",
                            $("#btn_expOps").attr("sec")
                        )
                    )
                    $("body").append(form);
                    form.submit();
                } else {
                    notify("Seleccione una opcion de la lista","warning");
                }
            });
            $("#btn_regresarIndex").click(function() {
                $("nav").menusinpres("clickMenu");
                return false;
            });'
        );
    }

    /**
     * Llena la Tabla de Tipo de Documento Fuente
     *
     * @return null
     */
    private function _llenaTablaConsultaOps()
    {
        $mes = (isset($this->prm->mes)) ? $this->prm->mes : '';
        if ($mes != '') {
            $sql = "SELECT c.gpo_id, c.ur_id, c.clc_id, c.ue_id, clc_cereCo,
                CONCAT( fte_id, sft_id, ori_id, sor_id, cff_id, cfi_id, cfi_anio ) AS claveFinanciamiento,
                ecl_nombre AS estadoClc, clc_concepto, clc_percepcion, clc_beneficiario,
                IFNULL(min.bdm_referencia,'') lot_referenciaPago, DATE(min.bdm_fechaAutorizado) as lot_fechaPago,
                CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id,
                    cta_ue_id, cgc_id, cta_id ) AS cuentaContable,cta_nombre,
                    ncc_tipoMovimiento, ncc_percepcion, ncc_retencion
                FROM scp_Clc c
                    INNER JOIN scp_ClcAuxiliar USING( gpo_id, ur_id, clc_id )
                    INNER JOIN enc_ClaveFinanciamiento USING( ecf_id )
                    INNER JOIN cat_EstadoClc ec ON( c.ecl_id = ec.ecl_id )
                    INNER JOIN scp_Contable USING( gpo_id, ur_id, clc_id )
                    INNER JOIN cg_PlanCuentas USING (cta_gpo_id, cta_ur_id,cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    INNER JOIN " . BD_TESORERIA . ".lot_LoteClc ltclc USING( gpo_id, ur_id, clc_id )
                    INNER JOIN " . BD_TESORERIA . ".lot_Lote lt on lt.fpa_id= ltclc.fpa_id and lt.msc_id=ltclc.msc_id
                        AND lt.lot_ejercicio=ltclc.lot_ejercicio
                        and lt.lot_id=ltclc.lot_id
                    LEFT JOIN " . BD_TESORERIA . ".min_BancoDocumentoMinistrado_" . APP_EJERCICIO . " min USING( gpo_id, ur_id, clc_id,afe_id,bdo_id )
                WHERE c.gpo_id = :gpo AND c.ur_id = :ur
                    AND c.ue_id = :ue
                    AND c.clc_tipoCereCo = :clcTipo
                    AND lot_ejr= :lotEjer
                    AND MONTH( clc_fechaEnvio ) = :mes ;";

            $this->setSql($sql);
            $this->setParams(array(':gpo' => 1, ':ur' => 16, ':ue' => 6, ':clcTipo' => 2, ':lotEjer' => APP_EJERCICIO, ':mes' => $mes));
            $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
            $nav->idTabla = 'tablaConsultaOps';
            $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
            $this->wrLlenaTabla($nav);
        }
    }
}
// Permisos del usuario que puede ver este controlador
if (isset($_SESSION[_S]['prm']['C']['10']['11'])) {
    new ControladorOPs();
} else {
    WebResponse::sinPermisos();
}
