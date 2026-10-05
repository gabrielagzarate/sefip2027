<?php
// Si no llegaron aquí por AJAX ...
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    echo "Poor Cyrus ...";
    return;
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

if (!isset($_SESSION)) {
    session_start();
}

class dameDetallePoliza extends WebResponse
{
    public $prm;

    public function __construct()
    {
        parent::__construct();

        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if (!empty($_POST)) {
            $this->prm = json_decode(json_encode($_POST));
        } else {
            $this->prm = new stdClass();
        }

        $mp = ((isset($this->prm->mp)) ? $this->prm->mp  : "");

        // Para la vinculación ( pdx_*_vinculacion )
        $clc_o = trim(((isset($this->prm->clc_o)) ? $this->prm->clc_o : ''));
        $cmp_o = trim(((isset($this->prm->cmp_o)) ? $this->prm->cmp_o : ''));

        // Bandera para indicar si pidieron filtros por vinculación, ya que en éstos casos
        // NO limitamos por tipo de póliza
        if (!($clc_o != "")) {
            $this->mixScript('notify("poor Cyrus...");');
            $this->returnJson();
            return false;
        }

        $where = " AND (";

        if ($clc_o != "") {
            $where .= " AND d.pdx_clc_id_origen = $clc_o ";
        }

        $where .= " )";

        // Para corregir un error en la lógica del armado
        $where = str_replace("AND ( AND ", "AND ( ", $where);
        $where = str_replace("AND ( OR ", "AND ( ", $where);

        switch ($mp) {
            case '3':   // Mayor
                $strCuentaContable = 'ger_id, cgp_id, rub_id, cgcu_id, cgsc_id';
                break;
            case '6': // cuarto nivel
                $strCuentaContable = 'ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id';
                break;
            default:
                $strCuentaContable = 'ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id';
                break;
        }


        // Obtenemos la primera página a mostrar
        // -----------------------------------------------------------------------------------------------------------------------
        $this->conectar();

        $sql = '
            SELECT
            CONCAT( ' . $strCuentaContable . ' ) AS cuenta,
            pdx_debe, pdx_haber, d.nat_id, CONCAT( pot_id, "-", prf_id, pma_id ) AS poliza,
            msc_id AS mes, mmc_id, ma.nat_id as cgm_naturaleza, cgsc_nombre as cgm_nombre,
            cta_gpo_id, cta_ur_id, cta_ue_id, epg_id, pg_id, pe_id, ecf_id, pdx_afe_id_origen, pdx_bdo_id_origen
            FROM cg_PolizaDetalleAuxiliar d
            JOIN cg_PolizaMaestro m USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            INNER JOIN cat_CgSubcuenta ma USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            WHERE
                d.gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND d.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] .
            $where . '
                AND m.epl_id = "N"';

        $strBusca = " AND ( d.pdx_clc_id_origen = $clc_o ";

        $sqlCmp = str_replace(
            $strBusca,
            " AND ( d.pdx_cmp_id_origen IN ( $cmp_o ) AND mmc_id = 'C' ",
            $sql
        );

        $sql .= " UNION ALL " . $sqlCmp;

        $sql .= ' ORDER BY
            cuenta, nat_id, /* pma_fecha */
            cta_gpo_id, cta_ur_id, cta_ue_id, epg_id, pg_id, pe_id, ecf_id, pdx_afe_id_origen, pdx_bdo_id_origen;';

        $this->setSql($sql);

        $this->consultar();
        $this->desconectar();

        $tes = array();

        if ($this->getNumeroRegistros() > 0) {

            while ($row = $this->extraer()) {

                $cuenta = $row['cuenta'];
                $nat     = $row['nat_id'];

                // Buscamos en el array si ya existe la cuenta
                $posCuenta = null;
                foreach ($tes as $key => $val) {
                    if ($val === $cuenta) {
                        return $key;
                    }
                }

                // Si ya existe
                if ($posCuenta != null) {
                    if ($nat == "D") {
                        $tes[$posCuenta]["D"][] = array($row['mes'], $row['poliza'], $row['pdx_debe'], $row['mmc_id']);
                    } else {
                        $tes[$posCuenta]["H"][] = array($row['mes'], $row['poliza'], $row['pdx_Haber'], $row['mmc_id']);
                    }
                    $tes[$posCuenta]["nat"] = $row['cgm_naturaleza'];
                    $tes[$posCuenta]["mayor"] = $row['cgm_nombre'];
                    // Si aún no existe
                } else {
                    if ($nat == "D") {
                        $tes[$cuenta]["D"][] = array($row['mes'], $row['poliza'], $row['pdx_debe'], $row['mmc_id']);
                    } else {
                        $tes[$cuenta]["H"][] = array($row['mes'], $row['poliza'], $row['pdx_haber'], $row['mmc_id']);
                    }
                    $tes[$cuenta]["nat"] = $row['cgm_naturaleza'];
                    $tes[$cuenta]["mayor"] = $row['cgm_nombre'];
                }
            }

            $strHtml = "";

            // Imprimimos
            $cta = "";
            $ctaNat = "";
            $nSumaD = 0;
            $nSumaH = 0;
            foreach ($tes as $key => $val) {

                if ($cta != $key) {

                    // Si las sumas son diferentes de 0, hacemos la operación
                    //if ( $nSumaD <> 0 || $nSumaH <> 0 ) {
                    if ($cta != "") {
                        // Totales debe y haber
                        $strHtml .= '
                            <tr>
                                <td width="20%">&nbsp;</td>
                                <td style="border-top: 1px solid; border-right: 1px solid;" width="30%" align="right">' . number_format($nSumaD, 2) . '</td>
                                <td style="border-top: 1px solid;" width="30%" align="right">' . number_format($nSumaH, 2) . '</td>
                                <td width="20%">&nbsp;</td>
                            </tr>
                        ';
                        // Saldo
                        // Si uno de los dos totales es cero, no hay operación que hacer, ya que queda igual
                        if ($nSumaD <> 0 && $nSumaH <> 0) {
                            $strHtml .= '
                                <tr>
                                    <td width="20%">&nbsp;</td>
                                    <td style="border-top: 1px solid; border-right: 1px solid;" width="30%" align="right">' . (($ctaNat == "D") ? number_format($nSumaD - $nSumaH, 2) : "") . '</td>
                                    <td style="border-top: 1px solid;" width="30%" align="right">' . (($ctaNat == "H") ? number_format($nSumaH - $nSumaD, 2) : "") . '</td>
                                    <td width="20%">&nbsp;</td>
                                </tr>
                            ';
                        }
                        // terminamos la tabla de ésta cuenta
                        $strHtml .= '</tbody></table>';
                    }
                    $strHtml .= "<br /><br />";
                    $nSumaD = 0;
                    $nSumaH = 0;
                }

                // Formateamos la cuenta
                switch (strlen($key)) {
                    case 8:
                        $ctaHdr = substr($key, 0, 5) . "." . substr($key, 5, 3);
                        break;
                    case 9:
                        $ctaHdr = substr($key, 0, 6) . "." . substr($key, 6, 3);
                        break;
                    case 12:
                        $ctaHdr = substr($key, 0, 5) . "." . substr($key, 5, 3) . "." . substr($key, 8, 4);
                        break;
                    case 13:
                        $ctaHdr = substr($key, 0, 6) . "." . substr($key, 6, 3) . "." . substr($key, 8, 4);
                        break;
                    case 17:
                        $ctaHdr = substr($key, 0, 5) . "." . substr($key, 5, 3) . "." . substr($key, 8, 9);
                        break;
                    case 18:
                        $ctaHdr = substr($key, 0, 6) . "." . substr($key, 6, 3) . "." . substr($key, 9, 9);
                        break;
                    default:
                        $ctaHdr = $key;
                        break;
                }

                $strHtml .= '
                <table border="0" width="100%" style="cellspacing=0px;">
                <thead>
                <tr>
                    <td style="border-bottom: 1px solid;" align="center" colspan="4"><b><font color="red">' . $ctaHdr . ' - ' . $val["mayor"] . '</font></b></td>
                </tr>
                </thead>
                <tfoot>
                <tbody">
                ';

                $maxD     = (isset($val["D"]) ? count($val["D"]) : 0);
                $maxH     = (isset($val["H"]) ? count($val["H"]) : 0);
                $ctaNat = (isset($val["nat"]) ? $val["nat"] : "D");   // Deudora por default

                $max  = max($maxD, $maxH);

                for ($i = 0; $i <= $max; $i++) {

                    $strHtml .= '
                        <tr>
                            <td width="20%" align="center">' . (($i < $maxD) ? $val["D"][$i][3] . " - " . $val["D"][$i][0] . " - " . $val["D"][$i][1] . " ) " : "") . '</td>
                            <td style="border-right: 1px solid;" width="30%" align="right">' . (($i < $maxD) ? number_format($val["D"][$i][2], 2) : "") . '</td>
                            <td width="30%" align="right">' . (($i < $maxH) ? number_format($val["H"][$i][2], 2) : "") . '</td>
                            <td width="20%" align="center">' . (($i < $maxH) ? "( " . $val["H"][$i][1] . " - " . $val["H"][$i][0] . " - " . $val["H"][$i][3] : "") . '</td>
                        </tr>
                    ';

                    $nSumaD += (($i < $maxD) ? $val["D"][$i][2] : 0);
                    $nSumaH += (($i < $maxH) ? $val["H"][$i][2] : 0);
                }
                $cta = $key;
            }

            // Para la última cuenta que no "cierra" su ciclo dentro del for
            // Si las sumas son diferentes de 0, hacemos la operación
            if ($nSumaD <> 0 || $nSumaH <> 0 || $cta != "") {

                // Totales debe y haber
                $strHtml .= '
                    <tr>
                        <td width="20%">&nbsp;</td>
                        <td style="border-top: 1px solid; border-right: 1px solid;" width="30%" align="right">' . number_format($nSumaD, 2) . '</td>
                        <td style="border-top: 1px solid;" width="30%" align="right">' . number_format($nSumaH, 2) . '</td>
                        <td width="20%">&nbsp;</td>
                    </tr>
                ';
                // Saldo
                // Si uno de los dos totales es cero, no hay operación que hacer, ya que queda igual
                if ($nSumaD <> 0 && $nSumaH <> 0) {
                    $strHtml .= '
                        <tr>
                            <td width="20%">&nbsp;</td>
                            <td style="border-top: 1px solid; border-right: 1px solid;" width="30%" align="right">' . (($ctaNat == "D") ? number_format($nSumaD - $nSumaH, 2) : "") . '</td>
                            <td style="border-top: 1px solid;" width="30%" align="right">' . (($ctaNat == "H") ? number_format($nSumaH - $nSumaD, 2) : "") . '</td>
                            <td width="20%">&nbsp;</td>
                        </tr>
                    ';
                }
                // terminamos la tabla de ésta cuenta
                $strHtml .= '</tbody></table>';
            }
        }

        $this->setContenedor("bdPantallaCompleta");
        $this->mixHtml('
            <div id="myModalMeta" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block; overflow-y:scroll !important;">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Ts de Mayor</h4>
                        <button id="expTMayor" type="button" title="Exporta a Excel las Ts de mayor" class="btn btn-outline-success"> <i class="fa-solid fa-file-excel"></i></button>
                        <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                         
                        
                        </div>
                        <div id="contenidoTMayor" class="modal-body">' . $strHtml . '</div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                $("#myModalMeta").remove();
                e.stopPropagation();
            });
        
            $(".cerrar").click(function(){ $("#myModalMeta").remove(); });

            $("#expTMayor").click(function(e) {
                window.open(
                    "data:application/vnd.ms-excel," + encodeURIComponent( "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />" + $("#contenidoTMayor").html() )
                );
                e.preventDefault();
            });
        ');
        $this->returnJson();
    }
}
new dameDetallePoliza();
