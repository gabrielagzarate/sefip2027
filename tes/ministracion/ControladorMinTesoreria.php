<?php

/**
 * Description of ControladorMinTesoreria
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 24-Oct-2019
 * Comment: -----
 * Objective:
 * Regla de Negocio:
 * Permits: Este controlador pregunta por los permisod de lectura, escritura y lectura.
Lectura: El permiso minimo que deben tener todos los usuario para que por lo menos puedan instanciar la clase.
Escritura:
 * */

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/propuesta/EntidadPropuestaDetalle.php';
require_once dirname(__FILE__) . '/../../ent/tes/propuesta/EntidadPropuesta.php';
require_once dirname(__FILE__) . '/../../ent/tes/propuesta/EntidadPropuestaDocumento.php';
require_once dirname(__FILE__) . '/../../ent/tes/propuesta/temPropuestaClc.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_GenerarLoteEstatal.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_GenerarLoteLineaCaptura2026.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/Lote.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDetalle.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LoteDocumento.php';
require_once dirname(__FILE__) . '/../../ent/tes/lote/LcaClc.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/PropuestaLote.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/LoteClcLineacaptura.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/LoteDocumentoLineaCaptura.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/LineaCapturaVencida.php';
require_once dirname(__FILE__) . '/../../ent/tes/linea/LineaCaptura.php';
require_once dirname(__FILE__) . '/../../ent/scp/Clc.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2017.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2018.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2019.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2020.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2021.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2022.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2023.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2024.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2025.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2026.php';
require_once dirname(__FILE__) . "/../../lib/tes/webServicesSiox.php";

class ControladorMinTesoreria extends WebResponse
{
    public $escritura;
    public $autoriza;
    public $generarLoteLineaDeCaptura;
    public $usr_id;
    public $ip;
    public $vin;
    public $clcCambia;
    public $prm2;
    public $lc;

    public function __construct()
    {

        parent::__construct();

        $this->escritura = isset($_SESSION[_S]['prm']['T']['4']['1']['2']);
        $this->autoriza = isset($_SESSION[_S]['prm']['T']['4']['1']['3']);
        $this->generarLoteLineaDeCaptura = isset($_SESSION[_S]['prm']['T']['4']['1']['102']);

        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        switch ($this->metodo) {
            case 'pintaGeneraLote':
                $this->pintaGeneraLote();
                break;
            case 'llenaTablaGeneraLote':
                $this->llenaTablaGeneraLote();
                break;
            case 'detallePropuesta':
                $this->detallePropuesta();
                break;
            case 'llenaTablaDetProp':
                $this->llenaTablaDetProp();
                break;
            case 'eliminaClc':
                $this->eliminaClc();
                break;
            case 'GeneraLote':
                $this->GeneraLote();
                break;
            case 'GeneraLotePorProcedimiento':
                $this->GeneraLotePorProcedimiento();
                break;
            case 'pintaAutorizaLote':
                $this->pintaAutorizaLote();
                break;
            case 'pintaTablaCere':
                $this->pintaTablaCere();
                break;
            case 'actualizarLineaCaptura':
                $this->actualizarLineaCaptura();
                break;
            case 'buscaSiox':
                $this->buscaSiox();
                break;
            case 'mostrarLineasCaptura':
                $this->mostrarLineasCaptura();
                break;
            case 'llenaTablaLineas':
                $this->llenaTablaLineas();
                break;
            case 'mostrarDialogoParaGenerarLote':
                $this->mostrarDialogoParaGenerarLote();
                break;
            case 'generarLoteLDCdeImporteDiferentes':
                $this->generarLoteLDCdeImporteDiferentes();
                break;

            case 'eliminarPropuestaCompleta':
                $this->eliminarPropuestaCompleta();
            default:
                $this->pintaGeneraLote();
                break;
        }

        $this->returnJson();
    }

    private function pintaGeneraLote()
    {
        $this->mixScript('$("#toolsPantalla").html("");');

        $tabla = new stdClass();
        $tabla->id = 'tbl-GeneraLote';
        $tabla->titulo = 'Listado de Propuestas Autorizadas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AÑO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROUPESTA';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLCS';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-eye';
        $td->class = 'detprop me-1';
        $td->id = 'det_prop';
        $td->attr = new stdClass();
        $td->attr->detProp = 'det_prop';
        $td->attr->anioProp = 'anio_prop';
        $td->attr->modo = 'tlo_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tipo_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'anio_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'elementos';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fecha_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'resp_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total_prop';
        $td->align = 'right';
        $td->formato = "moneda";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'solid fa-trash';
        $td->class = 'delprop me-1';
        $td->color = 'rojo';
        $td->id = 'del_prop';
        $td->attr = new stdClass();
        $td->attr->delProp = 'del_prop';
        $td->attr->anioProp = 'anio_prop';

        if ($this->escritura) {
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $tabla->comunica->metodo = 'llenaTablaGeneraLote';

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaGeneraLote();
    }

    private function llenaTablaGeneraLote()
    {
        $sql_ejercicio2017 = '';
        if ($this->serverProduccion) {
            // Contaneta la consulta cuando esta en el ambiente UAT, CALIDAD y PRODUCCION
            // Porque en la BD de desarrollo no se encuentra la BD 2017
            $sql_ejercicio2017 = '
                SELECT
                    p.tlo_id,
                    p.pro_id AS det_prop,
                    tp.tlo_nombre AS tipo_prop,
                    p.pro_ejercicio anio_prop,
                    p.pro_id AS prop,
                    p.pro_fechaGeneracion AS fecha_prop,
                    MAX(ur.ur_nombre) AS resp_prop,
                    IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                    count(pd.clc_id) as elementos,
                    p.pro_id AS aut_prop,
                    p.pro_id AS del_prop
                FROM
                    ' . BD_TESORERIA . '.pro_Propuesta AS p
                INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
                INNER JOIN ' . BD_CTRL_2017 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
                INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc c using(gpo_id,ur_id,clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)
                WHERE
                    p.elo_id = 2 AND p.pro_ejercicio=2017 AND c.ecl_id=10 AND ant.ecl_id = 11 AND ant.ejr_id_clc=2017 AND p.pro_ejr= ' . APP_EJERCICIO . '
                GROUP BY p.pro_id

                UNION ALL

                SELECT
                    p.tlo_id,
                    p.pro_id AS det_prop,
                    tp.tlo_nombre AS tipo_prop,
                    p.pro_ejercicio anio_prop,
                    p.pro_id AS prop,
                    p.pro_fechaGeneracion AS fecha_prop,
                    MAX(ur.ur_nombre) AS resp_prop,
                    IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                    count(pd.clc_id) as elementos,
                    p.pro_id AS aut_prop,
                    p.pro_id AS del_prop
                FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
                INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
                INNER JOIN ' . BD_CTRL_2018 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
                INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

                WHERE
                    p.elo_id = 2 AND pro_ejercicio=2018 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2018 AND p.pro_ejr=' . APP_EJERCICIO . '
                GROUP BY p.pro_id

                UNION ALL

                SELECT
                    p.tlo_id,
                    p.pro_id AS det_prop,
                    tp.tlo_nombre AS tipo_prop,
                    p.pro_ejercicio anio_prop,
                    p.pro_id AS prop,
                    p.pro_fechaGeneracion AS fecha_prop,
                    MAX(ur.ur_nombre) AS resp_prop,
                    IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                    count(pd.clc_id) as elementos,
                    p.pro_id AS aut_prop,
                    p.pro_id AS del_prop
                FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
                INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
                INNER JOIN ' . BD_CTRL_2019 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
                INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

                WHERE
                    p.elo_id = 2 AND p.pro_ejercicio=2019 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2019 AND p.pro_ejr=' . APP_EJERCICIO . '
                GROUP BY p.pro_id

                UNION ALL

                SELECT
                    p.tlo_id,
                    p.pro_id AS det_prop,
                    tp.tlo_nombre AS tipo_prop,
                    p.pro_ejercicio anio_prop,
                    p.pro_id AS prop,
                    p.pro_fechaGeneracion AS fecha_prop,
                    MAX(ur.ur_nombre) AS resp_prop,
                    IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                    count(pd.clc_id) as elementos,
                    p.pro_id AS aut_prop,
                    p.pro_id AS del_prop
                FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
                INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
                INNER JOIN ' . BD_CTRL_2020 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
                INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

                WHERE
                    p.elo_id = 2 AND p.pro_ejercicio=2020 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2020 AND p.pro_ejr=' . APP_EJERCICIO . '
                GROUP BY p.pro_id

                UNION ALL

                SELECT
                    p.tlo_id,
                    p.pro_id AS det_prop,
                    tp.tlo_nombre AS tipo_prop,
                    p.pro_ejercicio anio_prop,
                    p.pro_id AS prop,
                    p.pro_fechaGeneracion AS fecha_prop,
                    MAX(ur.ur_nombre) AS resp_prop,
                    IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                    count(pd.clc_id) as elementos,
                    p.pro_id AS aut_prop,
                    p.pro_id AS del_prop
                FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
                INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
                INNER JOIN ' . BD_CTRL_2021 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
                INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

                WHERE
                    p.elo_id = 2 AND p.pro_ejercicio=2021 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2021 AND p.pro_ejr=' . APP_EJERCICIO . '
                GROUP BY p.pro_id

                UNION ALL
            ';
        }

        $sql = '
            ' . $sql_ejercicio2017 . '

            SELECT
                p.tlo_id,
                p.pro_id AS det_prop,
                tp.tlo_nombre AS tipo_prop,
                p.pro_ejercicio anio_prop,
                p.pro_id AS prop,
                p.pro_fechaGeneracion AS fecha_prop,
                MAX(ur.ur_nombre) AS resp_prop,
                IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                count(pd.clc_id) as elementos,
                p.pro_id AS aut_prop,
                p.pro_id AS del_prop
            FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
            INNER JOIN ' . BD_CTRL_2022 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

            WHERE
                p.elo_id = 2 AND p.pro_ejercicio=2022 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2022 AND p.pro_ejr=' . APP_EJERCICIO . '
            GROUP BY p.pro_id

            UNION ALL

            SELECT
                p.tlo_id,
                p.pro_id AS det_prop,
                tp.tlo_nombre AS tipo_prop,
                p.pro_ejercicio anio_prop,
                p.pro_id AS prop,
                p.pro_fechaGeneracion AS fecha_prop,
                MAX(ur.ur_nombre) AS resp_prop,
                IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                count(pd.clc_id) as elementos,
                p.pro_id AS aut_prop,
                p.pro_id AS del_prop
            FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
            INNER JOIN ' . BD_CTRL_2023 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

            WHERE
                p.elo_id = 2 AND p.pro_ejercicio=2023 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2023 AND p.pro_ejr=' . APP_EJERCICIO . '
            GROUP BY p.pro_id

                        UNION ALL

            SELECT
                p.tlo_id,
                p.pro_id AS det_prop,
                tp.tlo_nombre AS tipo_prop,
                p.pro_ejercicio anio_prop,
                p.pro_id AS prop,
                p.pro_fechaGeneracion AS fecha_prop,
                MAX(ur.ur_nombre) AS resp_prop,
                IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                count(pd.clc_id) as elementos,
                p.pro_id AS aut_prop,
                p.pro_id AS del_prop
            FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
            INNER JOIN ' . BD_CTRL_2024 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

            WHERE
                p.elo_id = 2 AND p.pro_ejercicio=2024 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2024 AND p.pro_ejr=' . APP_EJERCICIO . '
            GROUP BY p.pro_id

            UNION ALL

            SELECT
                p.tlo_id,
                p.pro_id AS det_prop,
                tp.tlo_nombre AS tipo_prop,
                p.pro_ejercicio anio_prop,
                p.pro_id AS prop,
                p.pro_fechaGeneracion AS fecha_prop,
                MAX(ur.ur_nombre) AS resp_prop,
                IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                count(pd.clc_id) as elementos,
                p.pro_id AS aut_prop,
                p.pro_id AS del_prop
            FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
            INNER JOIN ' . BD_CTRL_2025 . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc c using(gpo_id, ur_id,clc_id)
            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

            WHERE
                p.elo_id = 2 AND p.pro_ejercicio=2025 AND ant.ecl_id = 12 AND ant.ejr_id_clc=2025 AND p.pro_ejr=' . APP_EJERCICIO . '
            GROUP BY p.pro_id

            UNION ALL

            SELECT
                p.tlo_id,
                p.pro_id AS det_prop,
                tp.tlo_nombre AS tipo_prop,
                p.pro_ejercicio anio_prop,
                p.pro_id AS prop,
                p.pro_fechaGeneracion AS fecha_prop,
                MAX(ur.ur_nombre) AS resp_prop,
                IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                count(pd.clc_id) as elementos,
                p.pro_id AS aut_prop,
                p.pro_id AS del_prop
            FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
            INNER JOIN ' . BD_CTRL . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL . '.scp_Clc c using(gpo_id, ur_id,clc_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

            WHERE
                p.elo_id = 2 AND p.pro_ejercicio=2025 AND c.ecl_id = 12 AND p.pro_ejr=' . APP_EJERCICIO . '
            GROUP BY p.pro_id

            UNION ALL

            SELECT
                p.tlo_id,
                p.pro_id AS det_prop,
                tp.tlo_nombre AS tipo_prop,
                p.pro_ejercicio anio_prop,
                p.pro_id AS prop,
                p.pro_fechaGeneracion AS fecha_prop,
                MAX(ur.ur_nombre) AS resp_prop,
                IFNULL(SUM(pd.pro_importeClc), 0) AS total_prop,
                count(pd.clc_id) as elementos,
                p.pro_id AS aut_prop,
                p.pro_id AS del_prop
            FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pd USING (pro_id)
            INNER JOIN ' . BD_CTRL . '.cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL . '.scp_Clc c using(gpo_id, ur_id,clc_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tp USING (tlo_id)

            WHERE
                p.elo_id = 2 AND p.pro_ejercicio=2026 AND c.ecl_id = 12 AND p.pro_ejr=' . APP_EJERCICIO . '
            GROUP BY p.pro_id
        ';

        $this->conectar();
        $this->setSql($sql);

        $nav = new stdClass();
        $nav->idTabla = 'tbl-GeneraLote';
        $nav->nf = 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".delprop").click(function(){
                id_prop =  $(this).attr("delProp");
                anio_prop = $(this).attr("anioProp");
                alertify.confirm("CANCELACIÓN DE PROPUESTA", "¿Realmente desea cancelar la propuesta?",
                    function(){
                        comunica(
                            "' . $this->pantalla . '",{
                                metodo: "eliminarPropuestaCompleta",
                                idProp: id_prop,
                                anioProp: anio_prop
                            },false,{
                                "mostrar":true,"mensaje":"Procesando la cancelación de la propuesta, espere unos momentos ..."
                        });
                    },
                    function(){},
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $(".detprop").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"detallePropuesta",
                    det_prop: $(this).attr("detProp"),
                    anioProp: $(this).attr("anioProp"),
                    modo:$(this).attr("modo")
                });
            });
        ');
    }

    private function detallePropuesta()
    {

        $modo = isset($this->prm->modo) ? $this->prm->modo : "";
        $anioProp = isset($this->prm->anioProp) ? $this->prm->anioProp : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn";
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "fa-solid fa-circle-left me-2";
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "pintaGeneraLote"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $btnGeneraLote = new stdClass();
        $btnGeneraLote->id = 'btnGeneraLote';
        $btnGeneraLote->name = 'btnGeneraLote';
        $btnGeneraLote->text = 'GenerarLote';
        $btnGeneraLote->value = 'CLCs Seleccionadas';
        $btnGeneraLote->type = 'button';
        $btnGeneraLote->color = "gris";
        $btnGeneraLote->class = "me-1";
        $btnGeneraLote->icon = "solid fa-circle-left me-2";
        $btnGeneraLote->click = '
            if ($("#fechaPago").val() == "")
            notify("seleccione una fecha de pago","error");
            else {
                if (seleccion = $("#tblDetProp").tabla("getFilaSeleccion")) {
                    alertify.confirm("FONDEO", "¿Desea registrar el fondeo con los lotes seleccionados?",
                        function(){
                            strSeleccion = "";
                            seleccion.forEach(function (element) {
                                strSeleccion += (strSeleccion.length > 0 ? "," : "");
                                strSeleccion += "(" + element.gpo + "," + element.ur + "," + element.clc + ")";
                            });
                            comunica(
                                "' . $this->pantalla . '",{
                                    metodo: "GeneraLote",
                                    tipoPago: $("#formaPago").val(),
                                    fechaPago: $("#fechaPago").val(),
                                    idProp: "' . $this->prm->det_prop . '",
                                    todos:0,
                                    cere:$("#cmb_cere option:selected").text().split(" ")[0].trim(),
                                    loteDetalles: strSeleccion
                                },false,{
                                    "mostrar":true,"mensaje":"Procesando el registro de fondeo, espere unos momentos ..."
                                });
                        },
                            function(){},
                    ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }
            }
        ';

        $btnGeneraLoteTodos = new stdClass();
        $btnGeneraLoteTodos->id = 'btnGeneraLoteTodos';
        $btnGeneraLoteTodos->name = 'btnGeneraLoteTodos';
        $btnGeneraLoteTodos->text = 'GenerarLote';
        $btnGeneraLoteTodos->value = 'Todas Las CLCs';
        $btnGeneraLoteTodos->type = 'button';
        $btnGeneraLoteTodos->class = "btn btn-outline-warning me-1";
        $btnGeneraLoteTodos->icon = "solid fa-share-from-square me-2";
        $btnGeneraLoteTodos->click = '
            if($("#fechaPago").val() == "")
                notify("seleccione una fecha de pago","error");
            else{
                alertify.confirm("FONDEO", "¿Desea registrar el fondeo con TODOS lotes en pantalla?",
                    function(){
                        comunica(
                            "' . $this->pantalla . '",{
                                metodo: "GeneraLote",
                                tipoPago: $("#formaPago").val(),
                                fechaPago: $("#fechaPago").val(),
                                idProp: "' . $this->prm->det_prop . '",
                                cere: $("#cmb_cere option:selected").text().split(" ")[0].trim(),
                                todos:1
                            },false,{
                                "mostrar":true,"mensaje":"Procesando el registro del fondeo, espere unos momentos ..."
                        });
                    },
                    function(){},
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            }
        ';

        if ($this->autoriza) {
            $this->agregaTools($this->ponBoton($btnGeneraLote));
            $this->agregaTools($this->ponBoton($btnGeneraLoteTodos));
        }

        $btnEliminaPropuesta = new stdClass();
        $btnEliminaPropuesta->id = 'btnEliminaPropuesta';
        $btnEliminaPropuesta->name = 'btnEliminaPropuesta';
        $btnEliminaPropuesta->text = 'Eliminar';
        $btnEliminaPropuesta->value = 'Eliminar';
        $btnEliminaPropuesta->type = 'button';
        $btnEliminaPropuesta->class = "delete me-1";
        $btnEliminaPropuesta->color = "rojo";
        $btnEliminaPropuesta->icon = "solid fa-trash me-2";
        $btnEliminaPropuesta->click = '
            alertify.confirm("ELIMINACIÓN DE CLCS", "¿Realmente desea eliminar todas las CLCs de la propuesta?",
                function(){
                    comunica(
                        "' . $this->pantalla . '",{
                            metodo: "eliminarPropuestaCompleta",
                            anioProp: ' . $anioProp . ',
                            idProp: "' . $this->prm->det_prop . '"
                        },false,{
                            "mostrar":true,"mensaje":"Procesando la eliminación de todas las CLCs, espere unos momentos ..."
                    });
                },
                function(){},
            ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnEliminaPropuesta));
        }

        $inputFormaPago = new stdClass();
        if ($modo == 2) {
            $inputFormaPago->sql = 'SELECT fpa_id,fpa_nombre FROM ' . BD_TESORERIA . '.cat_FormaPago where fpa_id<>3';
        } else if ($modo == 3) {
            $inputFormaPago->sql = 'SELECT fpa_id,fpa_nombre FROM ' . BD_TESORERIA . '.cat_FormaPago where fpa_id = 4';
        } else {
            $inputFormaPago->sql = 'SELECT fpa_id,fpa_nombre FROM ' . BD_TESORERIA . '.cat_FormaPago ';
        }

        $inputFormaPago->id = 'formaPago';
        $inputFormaPago->name = 'formaPago';
        $inputFormaPago->required = true;
        $inputFormaPago->mensaje = 'SELECCIONE METODO DE PAGO';
        $inputFormaPago->label = 'METODO DE PAGO';
        $inputFormaPago->enDiv = true;
        $inputFormaPago->search = true;
        $inputFormaPago->selecciona = 1;

        $inputFecha = new stdClass();
        $inputFecha->id = 'fechaPago';
        $inputFecha->name = 'fechaPago';
        $inputFecha->placeholder = 'Fecha de Pago';
        $inputFecha->label = 'FECHA DE PAGO';
        $inputFecha->formato = 'fecha';
        $inputFecha->enDiv = true;
        $inputFecha->required = true;

        $inputCere = new stdClass();
        $datos = array();
        array_push($datos, "OTB-CERE");
        array_push($datos, "OP-CERECO");
        $inputCere->opciones = $datos;
        $inputCere->id = 'cmb_cere';
        $inputCere->name = 'cmb_cere';
        $inputCere->label = 'TIPO';
        $inputCere->enDiv = true;
        $inputCere->search = true;
        $inputCere->required = true;

        $inputModoPago = new stdClass();
        $datos = array();
        array_push($datos, "TODAS");
        array_push($datos, "TRADICIONAL");
        array_push($datos, "CHEQUE");
        array_push($datos, "LINEA DE CAPTURA");
        array_push($datos, "PDP");
        array_push($datos, "CADENAS PRODUCTIVAS");
        $inputModoPago->opciones = $datos;
        $inputModoPago->id = 'cmd_modoPago';
        $inputModoPago->name = 'cmd_modoPago';
        $inputModoPago->label = 'MODO PAGO';
        $inputModoPago->enDiv = true;
        $inputModoPago->search = true;
        $inputModoPago->required = true;
        if ($modo == 3) {
            $inputModoPago->selecciona = 5;
        }

        $inputClaveBancaria = new StdClass();
        $inputClaveBancaria->sql = 'SELECT cbs_id,cbs_alias FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin ';
        $inputClaveBancaria->id = 'claveBancaria';
        $inputClaveBancaria->name = 'claveBancaria';
        $inputClaveBancaria->placeholder = 'CTA ORIGEN';
        $inputClaveBancaria->enDiv = true;
        $inputClaveBancaria->search = true;
        $inputClaveBancaria->label = 'CTA ORIGEN';

        $botonBuscarCere = new stdClass();
        $botonBuscarCere->id = 'btn_buscaCere';
        $botonBuscarCere->name = 'btn_buscaCere';
        $botonBuscarCere->class = "btn me-1";
        $botonBuscarCere->value = "Buscar";
        $botonBuscarCere->icon = "solid fa-magnifying-glass me-2";
        $botonBuscarCere->color = "gris";
        $botonBuscarCere->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"pintaTablaCere",
                cere: $("#cmb_cere option:selected").text().split(" ")[0].trim(),
                idProp: "' . $this->prm->det_prop . '",
                formaPago: $("#formaPago option:selected").text().split(" ")[0].trim(),
                cmd_modoPago: $("#cmd_modoPago option:selected").text().split(" ")[0].trim(),
                claveBancaria: $("#claveBancaria option:selected").text().split(" ")[0].trim(),
            });
        ';

        $html = '
            <div class="row mb-2">
                <legend> DETALLES DE LA PROPUESTA
                </legend>
                <fieldset class="col-md-3 col-xs-3 " id="">
                    ' . $this->ponCombo($inputFormaPago) . '
                </fieldset>

                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputFecha) . '
                </fieldset>
            </div>

            <div class="row mb-2">
                <legend> FILTRO DE BUSQUEDA
                </legend>
                <fieldset class="col-md-3 col-xs-3 " id="">
                    ' . $this->ponCombo($inputCere) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3 " id="">
                    ' . $this->ponCombo($inputModoPago) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-3 " id="">
                    ' . $this->ponCombo($inputClaveBancaria) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-3 d-flex justify-content-end align-items-end" id="">
                    ' . $this->ponBoton($botonBuscarCere) . '
                </fieldset>
            </div>

            <div class="row mb-2">
                <fieldset id="divTablaCere" class="col-md-12 col-xs-12">
            </div>
        ';

        $this->mixHtml($html);

    }

    private function pintaTablaCere()
    {
        $det_prop = isset($this->prm->idProp) ? $this->prm->idProp : "";
        $formaPago = isset($this->prm->formaPago) ? $this->prm->formaPago : "";

        $this->setContenedor('divTablaCere');

        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = array('gpo', 'ur', 'clc');
        $tabla->casoSeleccionable = new stdClass();

        if ($formaPago == 3) {
            $tabla->casoSeleccionable->campo = "existe";
            $tabla->casoSeleccionable->valor = "0";
        } else {
            $tabla->casoSeleccionable->campo = "elo_id";
            $tabla->casoSeleccionable->valor = "2";
        }
        $tabla->casoSeleccionable->ands = array();
        $tabla->casoSeleccionable->colorEnFalso = "rgba(254, 0, 0, 0.20)";

        //caso desplegable
        if ($formaPago == 3) {
            $tabla->desplegable = new stdClass();
            $tabla->desplegable->metodo = 'mostrarLineasCaptura';
            $tabla->desplegable->titulo = 'Lineas de Captura';
            $tabla->desplegable->encabezado = 'Ver';
            $tabla->desplegable->enFila = false;
            $tabla->desplegable->prm = array('gpo', 'ur', 'clc', 'ejercicio', 'cereId', 'prop');
        }

        $tabla->id = 'tblDetProp';
        $tabla->titulo = 'Detalle Propuesta ' . $det_prop;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetProp';
        $tabla->comunica->cere = '{$("#cmb_cere option:selected").text().split(" ")[0].trim() }';
        $tabla->comunica->id_prop = $det_prop;

        /* Array para dibujar la tabla /*/
        $tr = array();

        /* Encabezados /*/
        $th = new stdClass();
        $th->texto = 'MODO PAGO';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROUPESTA';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ORDEN';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'T';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE CLC';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        if ($formaPago == 3) {

            $th = new stdClass();
            $th->texto = 'IMPORTE LINEAS';
            $th->width = '8rem';
            $th->align = 'center';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DESTINO';
        $th->width = '8rem';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA Autorizacion';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        if ($formaPago == 3) {
            $th = new stdClass();
            $th->texto = 'LINEAS';
            $th->width = '2rem';
            $th->align = 'center';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'CTA ORIGEN';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->width = '2rem';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla /*/
        $trbody = array();

        /*Cuerpo de la tabla /*/
        $td = new stdClass();
        $td->campoTxt = 'mpc_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'orden';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cere';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'imp';
        $td->align = 'center';
        $td->formato = "moneda";
        array_push($trbody, $td);

        if ($formaPago == 3) {
            $td = new stdClass();
            $td->campoTxt = 'importeLineas';
            $td->align = 'center';
            $td->formato = "moneda";
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fechaAutorizacion';
        $td->align = 'center';
        array_push($trbody, $td);

        if ($formaPago == 3) {
            $td = new stdClass();
            $td->campoTxt = 'lineaEstado';
            $td->align = 'center';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'ctaOrigen';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->color = "rojo";
        $td->class = 'del';
        $td->id = 'del';
        $td->attr = new stdClass();
        $td->attr->grupo = 'gpo';
        $td->attr->ur = 'ur';
        $td->attr->clc = 'clc';
        $td->attr->prop = 'prop';
        if ($this->escritura) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla /*/
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaDetProp();

    }
    private function llenaTablaDetProp()
    {
        //obtenemos el año de la propuesta
        $cere = isset($this->prm->cere) ? $this->prm->cere : "";
        $det_prop = isset($this->prm->idProp) ? $this->prm->idProp : "";
        $formaPago = isset($this->prm->formaPago) ? $this->prm->formaPago : "";
        $cmd_modoPago = isset($this->prm->cmd_modoPago) ? $this->prm->cmd_modoPago : "";
        $claveBancaria = (isset($this->prm->claveBancaria) && $this->prm->claveBancaria != 'Seleccione') ?
        ' AND cbs_id = ' . $this->prm->claveBancaria : "";

        $parametros = [
            'det_prop' => $det_prop,
        ];

        $cere++;
        $sql = "SELECT pro_ejercicio
            from " . BD_TESORERIA . ".pro_Propuesta where pro_id= :det_prop";
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $row = $conexion->extraer();
        $anio = $row['pro_ejercicio'];
        $sqlExtraModoPago = " ";
        switch ($cmd_modoPago) {
            case 0: //todas
                break;
            case 1: // tradicional
                $sqlExtraModoPago = " AND cl.mpc_id = 1 AND cl.bur_id <>'000000000000000000' ";
                break;
            case 2: //cheque
                $sqlExtraModoPago = " AND cl.mpc_id = 1 AND cl.bur_id = '000000000000000000' ";
                break;
            case 3: //linea captura
                $sqlExtraModoPago = " AND cl.mpc_id = 2 ";
                break;
            case 4: //pdp
                $sqlExtraModoPago = " AND cl.mpc_id = 5 ";
                break;
            case 5: //cadenas productivas
                $sqlExtraModoPago = " AND cl.mpc_id = 4 ";
                break;
            default:
                break;
        }

        $sqlExtra = ($anio == 2017) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2017 " :
        (($anio == 2018) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2018 " :
            (($anio == 2019) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2019 " :
                (($anio == 2020) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2020 " :
                    (($anio == 2021) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2021 " :
                        (($anio == 2022) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2022 " :
                            (($anio == 2023) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2023 " :
                                (($anio == 2024) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2024 " : 
                                    (($anio == 2025) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2025 " :" AND cl.ecl_id=12 "))))))));
        switch ($anio) {
            case 2017:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2018:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2019:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2020:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2021:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2022:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2023:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2024:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2025:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2026:
            default:
                $condicion2017 = "";
                break;
        }
        $parametros += [
            'cere' => $cere,
        ];
        if ($formaPago == 3) {
            $sql = "SELECT
                'Linea de Captura' AS mpc_nombre,
                '$det_prop' AS prop,
                '$cere' AS cereId,
                '$anio' AS ejercicio,
                CONCAT(gpo_id,LPAD(ur_id,2,0)) AS ur_id,
                clc_id AS clc,
                gpo_id AS gpo,
                MAX(ur_id) AS ur,
                MAX(cx.clc_cereCo) AS orden,
                if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') AS cere,
                MAX(proc.pro_importeClc) AS imp,
                MAX(cl.clc_beneficiario) AS beneficiario,
                MAX(cl.clc_concepto) AS concepto,
                MAX(cl.bur_id),
                sum(existe) AS existe,
                MAX(referencia) AS referenciaCompleta,
                if (count(lin.referencia) = count(lin.lca_lineaCaptura) ,1,0 ) AS completo, #1.->completo ; 2->incompleto
                if (sum(existe)=0 ,'Completo','Incompleto' ) AS lineaEstado, #1.->completo ; 2->incompleto
                sum(lca_importe) AS importeLineas,
                MAX(fechaAutorizacion) fechaAutorizacion,
                MAX(cbs_id) as ctaOrigen
                FROM
                    " . BD_TESORERIA . ".pro_PropuestaClc as proc
                    left join
                    (
                    SELECT MAX(pre.ncp_referencia) AS referencia,MAX(lc.lca_lineaCaptura) lca_lineaCaptura,gpo_id,ur_id,clc_id,pro_importeClc,
                        SUM(IF(lca_importe IS NULL ,0,lca_importe)) AS lca_importe,
                        MAX(IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0))) AS existe, # 0 = SIN ERRORES
                        pro_fechaAutorizacion AS fechaAutorizacion
                        FROM " . BD_TESORERIA . ".pro_Propuesta AS pro
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                        INNER JOIN ( select MAX(ncp_referencia) ncp_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl$anio.scp_Presupuestal where clc_id>0 GROUP BY bdo_id,gpo_id,ur_id,clc_id  )AS pre using (gpo_id,ur_id,clc_id)
                        left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(pre.ncp_referencia)=19,SUBSTRING(pre.ncp_referencia,1,11),pre.ncp_referencia)
                        where pro.pro_id = :det_prop  AND IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5)
                        group by pre.bdo_id,gpo_id,ur_id,clc_id
                    UNION
                    SELECT MAX(c.ncc_referencia) AS referencia,MAX(lc.lca_lineaCaptura) lca_lineaCaptura,gpo_id,ur_id,clc_id,pro_importeClc,
                        SUM(IF(lca_importe IS NULL ,0,lca_importe)) AS lca_importe,
                        MAX(IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0))) AS existe, # 0 = SIN ERRORES
                        pro_fechaAutorizacion AS fechaAutorizacion
                        FROM  " . BD_TESORERIA . ".pro_Propuesta AS pro
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                        INNER JOIN ( select MAX(ncc_referencia) ncc_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl$anio.scp_Contable where clc_id>0 GROUP BY bdo_id,gpo_id,ur_id,clc_id  ) AS c using (gpo_id,ur_id,clc_id)
                        left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(c.ncc_referencia)=19,SUBSTRING(c.ncc_referencia,1,11),c.ncc_referencia)
                    WHERE pro.pro_id = :det_prop AND IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5)
                    group by c.bdo_id,gpo_id,ur_id,clc_id
                ) AS lin using(gpo_id,ur_id,clc_id)
                INNER JOIN ctrl$anio.scp_Clc AS cl using(gpo_id,ur_id,clc_id)
                left join " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento using (ecf_id)
                $condicion2017
                INNER JOIN ctrl$anio.scp_ClcAuxiliar AS cx using(gpo_id,ur_id,clc_id)
                where  cl.mpc_id = 2 and proc.pro_id= :det_prop AND cl.clc_tipoCereCo= :cere $sqlExtra $sqlExtraModoPago $claveBancaria
                group by  gpo_id,ur_id,clc_id
                ";
        } else {
            $sql = "SELECT
                CONCAT(dp.gpo_id,dp.ur_id,dp.clc_id) id,
                dp.bur_id,
                concat(dp.gpo_id,LPAD(dp.ur_id,2,0)) as ur_id,
                p.pro_id AS prop,
                dp.gpo_id AS gpo,
                dp.ur_id AS ur,
                dp.clc_id AS clc,
                dp.pro_importeClc AS imp,
                cl.clc_beneficiario AS beneficiario,
                cl.clc_concepto AS  concepto,
                p.elo_id,
                mp.mpc_nombre,
                if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') as cere,
                ax.clc_cereCo as orden,
                p.pro_fechaAutorizacion AS fechaAutorizacion,
                cbs_id as ctaOrigen
            FROM " . BD_TESORERIA . ".pro_Propuesta AS p
            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
            INNER JOIN ctrl" . $anio . ".scp_Clc cl using(gpo_id,ur_id,clc_id)
            LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento using (ecf_id)
            $condicion2017
            INNER JOIN ctrl" . $anio . ".scp_ClcAuxiliar ax using(gpo_id,ur_id,clc_id)
            INNER JOIN ctrl" . $anio . ".cat_ModoPagoClc mp on (cl.mpc_id = mp.mpc_id)
            WHERE cl.clc_tipoCereCo= :cere AND p.pro_id = :det_prop". $sqlExtra . $sqlExtraModoPago . $claveBancaria;
        }

        /* Hacemos la consulta y llenamos la tabla con el resultado /*/

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = new stdClass();
        $nav->idTabla = 'tblDetProp';
        $nav->nf = -1;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".del").click(function(){
                gpo = $(this).attr("grupo");
                ur = $(this).attr("ur");
                clc = $(this).attr("clc");
                prop = $(this).attr("prop");
                //Mandamos a llamar al metodo para mostrar el detalle de la propuesta
                alertify.confirm("ELIMINACIÓN DE CLC", "¿Realmente desea eliminar esta CLC?",
                    function(){
                        comunica(
                            "' . $this->pantalla . '",{
                                metodo: "eliminaClc",
                                gpo_id: gpo,
                                ur_id: ur,
                                clc_id: clc,
                                prop: prop
                            },false,{
                                "mostrar":true,"mensaje":"Procesando la eliminación de CLC, espere unos momentos ..."
                        });
                    },
                    function(){},
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }
    private function eliminarPropuestaCompleta()
    {
        //1.-liberamos todas las clc's de la propuesta
        $pro_id = isset($this->prm->idProp) ? $this->prm->idProp : "";
        $anio_prop = isset($this->prm->anioProp) ? $this->prm->anioProp : "";
        $eliminarProp = false;

        if ($pro_id == "") {
            return 0;
        }

        switch ($anio_prop) {
            case 2017:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;

            case 2018:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2019:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2020:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2021:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2022:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2023:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2024:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2025:
                $ejercicioBusca = "INNER JOIN " . BD_CTRLANT . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
            case 2026:
            default:
                $ejercicioBusca = "INNER JOIN " . BD_CTRL . ".scp_Clc USING (gpo_id,ur_id,clc_id)";
                $ejercicioEstado = " AND ecl_id=12";
                break;
        }

        $parametros = [
            'pro_id' => $pro_id,
        ];

        $sql1 = "SELECT * FROM " . BD_TESORERIA . ".pro_PropuestaClc
            INNER JOIN " . BD_TESORERIA . ".pro_Propuesta USING(pro_id)
            WHERE  PRO_ID= :pro_id";
        $sql2 = "SELECT
            gpo_id,ur_id,clc_id,pro_ejercicio
            FROM " . BD_TESORERIA . ".pro_PropuestaClc
            INNER JOIN " . BD_TESORERIA . ".pro_Propuesta USING(pro_id)
            " . $ejercicioBusca . "
            WHERE  PRO_ID= :pro_id" . $ejercicioEstado;
        $conexion2 = new AccesoDatos();
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion2->conectar();

        $conexion->setSql($sql2);
        $conexion2->setSql($sql1);
        $conexion2->consultar($parametros);
        $conexion->consultar($parametros);
        $eliminar = $conexion->getNumeroRegistros();
        $propuestaClc = $conexion2->getNumeroRegistros();

        if ($eliminar == $propuestaClc) {
            $eliminarProp = true;
        }

        $this->prm->pro_id = $pro_id;
        $this->prm->abc = 'b';
        while ($row = $conexion->extraer()) {
            $this->prm->gpo_id = $row['gpo_id'];
            $this->prm->ur_id = $row['ur_id'];
            $this->prm->clc_id = $row['clc_id'];
            $ejercicio = $row['pro_ejercicio'];
            
            unset ($parametros);
            $parametros =[
                'gpo_id' => $this->prm->gpo_id,
                'ur_id' => $this->prm->ur_id,
                'clc_id' => $this->prm->clc_id,
                'pro_id' => $this->prm->pro_id,
            ];
            //buscamos si tiene documentos
            $sql = "SELECT
            bdo_id
            from " . BD_TESORERIA . ".pro_PropuestaDocumento
            where pro_id= :pro_id AND gpo_id= :gpo_id
            AND ur_id= :ur_id AND clc_id= :clc_id";
            $conexion2 = new AccesoDatos();
            $conexion2->conectar();
            $conexion2->setSql($sql);
            $conexion2->consultar($parametros);
            while ($row = $conexion2->extraer()) {
                $this->prm->bdo_id = $row['bdo_id'];
                EntidadPropuestaDocumento::abc($this->prm);
            }

            EntidadPropuestaDetalle::abc($this->prm);
            $params = [
                'usr_id' => $this->usr_id,
                'ip' => $this->ip,
            ];
            unset ($parametros);

            $parametros =[
                'gpo_id' => $this->prm->gpo_id,
                'ur_id' => $this->prm->ur_id,
                'clc_id' => $this->prm->clc_id,
            ];
            switch ($ejercicio) {
                case 2017:
                    //en 2017 el estado clc no cambia al cancelar una propuesta
                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2017";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);

                    break;
                case 2018:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2018";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2019:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2019";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2020:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2020";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2021:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2021";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2022:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2022";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2023:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2023";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2024:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) ,@bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2024";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2025:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) ,@bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2025";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
                case 2026:

                    $conexionCLC = new AccesoDatos();
                    $conexionCLC->conectar();
                    $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por cancelacion pantalla MinTesoreria'";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($params);
                    $sql = " UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id";
                    $conexionCLC->setSql($sql);
                    $conexionCLC->consultar($parametros);
                    break;
            }
        }
        if ($eliminarProp) {
            $this->prm->abc = 'c';
            $this->prm->elo_id = 9;
            $this->prm->pro_fechaCancelacion = date("Y-m-d");
            EntidadPropuesta::abc($this->prm);
        }

    }
    private function mostrarLineasCaptura()
    {

        $gpo = $this->prm->gpo;
        $ur = $this->prm->ur;
        $clc = $this->prm->clc;
        $ejercicio = $this->prm->ejercicio;
        $cereId = $this->prm->cereId;
        $prop = $this->prm->prop;

        $btnActualizaFake = new stdClass();
        $btnActualizaFake->id = 'btnActualizaFake';
        $btnActualizaFake->name = 'btnActualizaFake';
        $btnActualizaFake->text = 'Actualiza';
        $btnActualizaFake->value = 'Actualiza';
        $btnActualizaFake->type = 'button';
        $btnActualizaFake->class = "btn btn-outline-success me-1";
        $btnActualizaFake->click = '';
        $tabla = new stdClass();
        $tabla->titulo = 'LISTA DE FOLIOS Y LINEAS DE CAPTURA';
        $tabla->id = 'listaLineas';
        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LINEA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'GENERACION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'VENCIMIENTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CUENTA';
        array_push($tr, $th);

        $tabla->encabezado = array($tr);
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaLineas';
        $tabla->comunica->gpo = $gpo;
        $tabla->comunica->ur = $ur;
        $tabla->comunica->clc = $clc;
        $tabla->comunica->ejercicio = $ejercicio;
        $tabla->comunica->cereId = $cereId;
        $tabla->comunica->prop = $prop;

        $tabla->cuerpo = array();

        $td = new stdClass();
        $td->campoTxt = 'ur_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();

        $td->campoTxt = 'referencia';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'existe';
        $td->casos->valor = '0';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'text';
        $td->casos->falso->class = 'textLinea';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->value = "linea";
        $td->casos->falso->attr->folio = "referencia";
        $td->casos->falso->attr->estado = "estado";
        $td->casos->falso->attr->lca_id = "lca_id";

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->campoTxt = 'linea';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'estado';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'lca_fechaGeneracion';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'lca_fechaVencimiento';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'lca_importe';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'lca_clabeAbono';
        array_push($tabla->cuerpo, $td);

        $this->mixHtml('
            <div id="myModal" >
                <div class="row col-md-12 col-xs-12 ">
                    <fieldset class="col-md-12 col-xs-12">
                        ' . $this->ponBoton($btnActualizaFake) . '
                    </fieldset>
                </div>
                <div class="row col-md-12 col-xs-12 ">
                    <fieldset class="col-md-12 col-xs-12">
                        ' . $this->wrPonTabla($tabla) . '
                    </fieldset>
                </div>
            </div>
        ');
        $this->llenaTablaLineas();
    }
    private function llenaTablaLineas()
    {
        //obtenemos el año de la propuesta
        $gpo = isset($this->prm->gpo) ? $this->prm->gpo : '';
        $ur = isset($this->prm->ur) ? $this->prm->ur : '';
        $clc = isset($this->prm->clc) ? $this->prm->clc : '';
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $cereId = isset($this->prm->cereId) ? $this->prm->cereId : '';
        $cereId--;
        $prop = isset($this->prm->prop) ? $this->prm->prop : '';
        switch ($ejercicio) {
            case 2017:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2018:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2019:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2020:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2021:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2022:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2023:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2024:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2025:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2026:
            default:
                $condicion2017 = "";
                break;
        }
        $sqlExtra = $ejercicio == 2017 ? " AND ant.ecl_id=10 AND ant.ejr_id_clc=2017 " :
        (($ejercicio == 2018) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2018 " :
            (($ejercicio == 2019) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2019 " :
                (($ejercicio == 2020) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2020 " :
                    (($ejercicio == 2021) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2021 " :
                        (($ejercicio == 2022) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2022 " :
                            (($ejercicio == 2023) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2023 " :
                                (($ejercicio == 2024) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2024 " : 
                                    (($ejercicio == 2025) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2025 " : " AND cl.ecl_id=12"))))))));
        //caso linea de captura
        $parametros = [
            'gpo' => $gpo,
            'ur' => $ur,
            'clc' => $clc,
        ];
        $sql = "SELECT
                concat(b.gpo_id,LPAD(b.ur_id,2,0)) as ur_id,
                b.clc_id as clc,
                b.cereCo,
                b.cere,
                b.importe as imp,
                b.beneficiario,
                b.concepto,
                doc.bdo_id,
                b.rfc_id,
                b.linea,
                (doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable) AS importeBdo,
                referencia,
                if( b.importe<>lca_importe,0, existe) as existe,
                lca_fechaGeneracion,
                lca_fechaVencimiento,
                lca_importe,
                lca_clabeAbono,
                estado,
                lca_id

                #if (count(b.referencia) = count(b.lca_folio) ,1,0 ) as completo #1.->completo ; 2->incompleto
                FROM  (
                    SELECT
                    cl.gpo_id,
                    cl.ur_id,
                    cl.clc_id,
                    c.bdo_id,
                    MAX(c.rfc_id) rfc_id,
                    MAX((c.ncc_percepcion-c.ncc_retencion)) AS importeBdo,
                    MAX(c.ncc_referencia) as referencia,
                    lc.lca_folio,
                    ax.clc_cereCo as cereCo,
                    (cl.clc_percepcion - cl.clc_retencion) as importe,
                    cl.clc_concepto as concepto,
                    cl.clc_beneficiario as beneficiario,
                    lc.lca_lineaCaptura as linea,
                    if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') as cere,
                    IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) as existe, #1.-> existe ;0->no existe
                    IF (lc.lca_lineaCaptura is null,'NO SE ENCONTRO', IF (lc.lca_fechaVencimiento < DATE(NOW()),'VENCIDA','VIGENTE')) as estado, #1.-> existe ;0->no existe
                    lc.lca_fechaGeneracion,
                    lc.lca_fechaVencimiento,
                    lc.lca_importe,
                    lc.lca_clabeAbono,
                    lc.lca_id

                    FROM ctrl$ejercicio.scp_Clc cl
                    $condicion2017
                    INNER JOIN ctrl$ejercicio.scp_ClcAuxiliar ax using(gpo_id,ur_id,clc_id)
                    INNER JOIN ctrl$ejercicio.scp_Contable c USING (gpo_id,ur_id,clc_id)
                    left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = c.ncc_referencia
                    WHERE
                        cl.gpo_id= :gpo and cl.ur_id= :ur and cl.clc_id= :clc  AND  IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5) $sqlExtra
                    group by bdo_id,lca_lineaCaptura

                    UNION ALL

                    SELECT
                    cl.gpo_id,
                    cl.ur_id,
                    cl.clc_id,
                    p.bdo_id,
                    MAX(p.rfc_id),
                    MAX(ncp_percepcionNeto) AS importeBdo,
                    MAX(p.ncp_referencia) as referencia,
                    lc.lca_folio,
                    ax.clc_cereCo as cereCo,
                    (cl.clc_percepcion - cl.clc_retencion) as importe,
                    cl.clc_concepto as concepto,
                    cl.clc_beneficiario as beneficiario,
                    lc.lca_lineaCaptura as linea,
                    if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') as cere,
                    IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) as existe, #1.-> existe ;0->no existe
                    IF (lc.lca_lineaCaptura is null,'NO SE ENCONTRO', IF (lc.lca_fechaVencimiento < DATE(NOW()),'VENCIDA','VIGENTE')) as estado, #1.-> existe ;0->no existe
                    lc.lca_fechaGeneracion,
                    lc.lca_fechaVencimiento,
                    lc.lca_importe,
                    lc.lca_clabeAbono,
                    lc.lca_id

                    FROM
                    ctrl$ejercicio.scp_Clc cl
                    $condicion2017
                    INNER JOIN ctrl$ejercicio.scp_ClcAuxiliar ax using(gpo_id,ur_id,clc_id)
                    inner join ctrl$ejercicio.scp_Presupuestal p USING (gpo_id,ur_id,clc_id)
                    left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = p.ncp_referencia
                    WHERE
                        cl.gpo_id= :gpo and cl.ur_id= :ur and cl.clc_id= :clc  AND  IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5) $sqlExtra
                    group by bdo_id,lca_lineaCaptura

                    ) AS b
                INNER JOIN ctrl$ejercicio.cat_BancoDocumentos AS doc USING(gpo_id, ur_id, bdo_id)

            ";

        /* Hacemos la consulta y llenamos la tabla con el resultado /*/
        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'listaLineas';
        $nav->nf = -1;
        $this->wrLlenaTabla($nav);

        $this->mixScript("
            $(document).off('blur', '.textLinea');
            $(document).on('blur', '.textLinea', function() {
                if ($(this).val() != '') {
                    comunica('$this->pantalla', {
                        metodo: 'actualizarLineaCaptura',
                        linea: $(this).val(),
                        folio: $(this).attr('folio'),
                        cere: '$cereId',
                        formaPago:3,
                        idProp: '$prop',
                        estado: $(this).attr('estado'),
                        lca_id: $(this).attr('lca_id'),
                        gpo: '$gpo',
                        ur: '$ur',
                        clc: '$clc',
                        ejercicio: '$ejercicio'
                    });
                }
            });
        ");
    }

    private function actualizarLineaCaptura()
    {
        $linea = isset($this->prm->linea) ? $this->prm->linea : '';
        $folio = isset($this->prm->folio) ? $this->prm->folio : '';
        $cere = isset($this->prm->cere) ? $this->prm->cere : '';
        $cereOLD = $cere + 1;
        $idProp = isset($this->prm->idProp) ? $this->prm->idProp : '';
        $estado = isset($this->prm->estado) ? $this->prm->estado : '';
        $siox = isset($this->prm->lca_id) ? $this->prm->lca_id : '';
        $gpo = isset($this->prm->gpo) ? $this->prm->gpo : '';
        $ur = isset($this->prm->ur) ? $this->prm->ur : '';
        $clc = isset($this->prm->clc) ? $this->prm->clc : '';
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $casosLineas = 1; //default
        //todo: validar que el folio sea V
        if (substr($folio, 0, 1) == 'V') {
            if (substr($linea, 0, 1) != 'V') {
                $this->mixScript(" notify('DEBES INGRESAR UN FOLIO VEHICULAR','error');");
                return;
            }
        }
        $parametros = [
            'linea' => $linea,
        ];
        $sql = "SELECT
            lca_id,
            lca_lineaCaptura,
            lca_importe,
            lca_folio,
            elc_id
            FROM " . BD_TESORERIA . ".lca_LineaCaptura
            WHERE lca_lineaCaptura= :linea";
        $conexionLC = new AccesoDatos();
        $conexionLC->conectar();
        $conexionLC->setSql($sql);
        $conexionLC->consultar($parametros);
        $row = $conexionLC->extraer();
        if ($conexionLC->getNumeroRegistros() > 0) {
            if ($row['elc_id'] == 1) { //si el estado es de la linea es registrado se usa
                $lc_dato = new stdClass();
                $lc_dato->abc = 'a';
                $lc_dato->lca_id_siox = $linea;
                $lc_dato->lca_id_pago = $row['lca_lineaCaptura'];
                LineaCapturaVencida::abc($lc_dato);
            } else {
                $idclc = $gpo . $ur . $clc;
                $_SESSION[$idclc] = $row['lca_folio'];
                $a = $_SESSION[$idclc];
                $b = $row['lca_folio'];
                $this->mixScript(" notify('LA LINEA YA FUE USADA ','error');");
                return 0;
            }
        } else { //la linea no existe

            //determinar el tipo de linea
            if (is_numeric($linea)) {
                if (strlen($linea) == 19) {
                    $casosLineas = 2;
                } else if (strlen($linea) == 11) {
                    $casosLineas = 1;
                }
            } else {
                $lc = substr($linea, 0, 1);
                if ($lc == 'V') {
                    $casosLineas = 3;
                }
            }
            //si exite en siox y no esta registrada la agregamos a la tabla
            //buscamos la linea de captura en webservice
            $cadLineas = "'" . $linea . "'";

            $respuestaLineas = ws_Consulta_Linea($cadLineas, $casosLineas);
            $arrayLc = json_decode($respuestaLineas);
            if (empty($arrayLc)) {
                $this->mixScript(" notify('LA LINEA NO EXISTE EN SIOX','error')");
                return 0;
            }
            $existeSiox = 0;
            $lc_dato = new stdClass();
            foreach ($arrayLc as $j => $lc) {
                //temporal quitar cuando haya conexion siox
                $lineaSiox = $lc->lineaCaptura;
                $lc_dato->lineaCaptura = $lc->lineaCaptura;
                $lc_dato->fechaGeneracion = $lc->fechaGeneracion;
                $lc_dato->lca_folio = $folio;
                $lc_dato->fechaVencimiento = $lc->fechaVencimiento;
                $lc_dato->importe = $lc->importe;
                $lc_dato->cuentaAbono = $lc->clabe;
                $lc_dato->ejr_id = APP_EJERCICIO;
                $lc_dato->tlc_id = "1";
                $lc_dato->elc_id = "1";

                $lc_dato->idConvenioBanco = $lc->idConvenioBanco;
                $lc_dato->abc = "a";
                $existeSiox = 1;

                unset($parametros);
                $parametros = [
                    'lineaSiox' => $lineaSiox,
                ];

                $sql = "SELECT
                    lca_id,
                    lca_lineaCaptura,
                    lca_importe,
                    lca_folio
                    FROM " . BD_TESORERIA . ".lca_LineaCaptura
                    WHERE lca_lineaCaptura= :lineaSiox AND elc_id=1";
                $conexionLC = new AccesoDatos();
                $conexionLC->conectar();
                $conexionLC->setSql($sql);
                $conexionLC->consultar($parametros);
                $row = $conexionLC->extraer();
                if ($conexionLC->getNumeroRegistros() > 0) {
                    $idclc = $gpo . $ur . $clc;

                    $_SESSION[$idclc] = $row['lca_folio'];
                    $a = $_SESSION[$idclc];
                    $b = $idclc;
                    $this->mixScript(" notify('LA LINEA YA FUE USADA $a $b ','error')");
                    return 0;
                } else {
                    if ($estado == 'VENCIDA') {
                        //agregamos a la vinculacion de linea vencida y cambiamos de estado la vencida
                        $lineaPago = LineaCaptura::abc($lc_dato);
                        $lc_dato->lca_id_siox = $siox;
                        $lc_dato->lca_id_pago = $lineaPago;
                        LineaCapturaVencida::abc($lc_dato);
                        $lc_vencida = new stdClass();
                        $lc_vencida->lca_id = $siox;
                        $lc_vencida->elc_id = 5;
                        $lc_vencida->abc = 'c';
                        LineaCaptura::abc($lc_vencida);
                    } else {
                        LineaCaptura::abc($lc_dato);
                    }
                }
            }
            if ($existeSiox != 1) {
                $this->mixScript(" notify('LA LINEA NO EXISTE EN SIOX','error')");
                return 0;
            }
        }
        //REGISTRAMOS LA NUEVA LINEA:
        $this->mixScript(" notify('LA LINEA FUE REGISTRADA EXITOSAMENTE')");
        $this->mixScript('

      comunica("' . $this->pantalla . '",{
                metodo: "pintaTablaCere",
                cere: "' . $cere . '",
                formaPago:3,
                idProp: "' . $idProp . '"
            });
      comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaLineas",
                gpo: "' . $gpo . '" ,
                ur: "' . $ur . '" ,
                clc:  "' . $clc . '" ,
                ejercicio:  "' . $ejercicio . '" ,
                cereId: "' . $cereOLD . '" ,
                prop: "' . $idProp . '"

            });
      ');
    }

    private function eliminaClc()
    {
        //recibimos las variables
        $this->prm->abc = "b";
        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : "";
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : "";
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : "";
        $this->prm->pro_id = isset($this->prm->prop) ? $this->prm->prop : "";
        $this->prm->det_prop = $this->prm->pro_id;

        //consultamos si tiene documentos se liberan
        $parametros = [
            'pro_id' => $this->prm->pro_id,
            'gpo_id' => $this->prm->gpo_id,
            'ur_id' => $this->prm->ur_id,
            'clc_id' => $this->prm->clc_id,
        ];
        $sql = "SELECT
            bdo_id
            from " . BD_TESORERIA . ".pro_PropuestaDocumento
            where pro_id= :pro_id AND gpo_id= :gpo_id
              AND ur_id= :ur_id AND clc_id= :clc_id";

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        while ($row = $conexion->extraer()) {
            $this->prm->bdo_id = $row['bdo_id'];
            EntidadPropuestaDocumento::abc($this->prm);
        }
        unset($parametros);
        $parametros = [
            'pro_id' => $this->prm->det_prop
        ];
        $sql = "SELECT
            p.pro_ejercicio as ejercicio
            FROM " . BD_TESORERIA . ".pro_Propuesta AS p
            WHERE p.pro_id = :pro_id
        ";
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $row = $conexion->extraer();
        $ejercicio = $row['ejercicio'];

        //liberamos la clc
        EntidadPropuestaDetalle::abc($this->prm);
        unset($parametros);
        $parametros = [
            'gpo_id' => $this->prm->gpo_id,
            'ur_id' => $this->prm->ur_id,
            'clc_id' => $this->prm->clc_id,
        ];
        $params = [
            'usr_id' => $this->usr_id,
            'ip' => $this->ip,
        ];
        switch ($ejercicio) {
            case 2017:
                // no se cambia el estado

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc=2017";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2018:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2018";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2019:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2019";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2020:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2020";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2021:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2021";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2022:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2022";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2023:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2023";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2024:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2024";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2025:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc=2025";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
            case 2026:

                $conexionCLC = new AccesoDatos();
                $conexionCLC->conectar();
                $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por eliminacion en ControladorMinTesoreria'";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($params);
                $sql = " UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id=10 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id";
                $conexionCLC->setSql($sql);
                $conexionCLC->consultar($parametros);
                break;
        }
        //consultamos si quedaron clc en esa propuesta
        unset($parametros);
        $parametros = [
            'pro_id' => $this->prm->det_prop,
        ];
        $sql = "SELECT
            p.pro_id AS prop,
            dp.gpo_id AS gpo,
            dp.ur_id AS ur,
            dp.clc_id AS clc,
            dp.pro_importeClc AS imp,
            p.pro_ejercicio as ejercicio
            FROM " . BD_TESORERIA . ".pro_Propuesta AS p
            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
            WHERE p.pro_id = :pro_id
        ";
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->extraer();
        if ($conexion->getNumeroRegistros() > 0) {
            $this->mixScript('
                comunica("' . $this->pantalla . '", {
                    metodo: "llenaTablaDetProp",
                    idProp: ' . $this->prm->det_prop . ',
                    cere: $("#cmb_cere option:selected").text().split(" ")[0].trim(),
                    formaPago: $("#formaPago").val()
                });
            ');
        } else {
            //si no hay mas registros cancelamos la propuesta
            $this->prm->abc = 'c'; //aqui hay error
            $this->prm->pro_id = $this->prm->det_prop;
            $this->prm->elo_id = 9;
            EntidadPropuesta::abc($this->prm);
            $this->mixScript('$("#toolsPantalla").html("");');
            $this->pintaGeneraLote();
        }
    }

    private function GeneraLote()
    {
        //1.- consultar por el consecutivo
        $mes = date("n");
        $fecha = date("Y-m-d");
        $fechaPago = isset($this->prm->fechaPago) ? $this->prm->fechaPago : "";
        $loteDetalles = isset($this->prm->loteDetalles) ? $this->prm->loteDetalles : "";
        $idProp = isset($this->prm->idProp) ? $this->prm->idProp : "";
        $tipoPago = isset($this->prm->tipoPago) ? $this->prm->tipoPago : "";
        $todos = isset($this->prm->todos) ? $this->prm->todos : "";
        $cere = isset($this->prm->cere) ? $this->prm->cere : "";
        $cmd_modoPago = isset($this->prm->cmd_modoPago) ? $this->prm->cmd_modoPago : "";
        $cereID = $cere + 1;
        $cadenaLineas = '';
        $condicion2017 = '';
        $sqlExtraModoPago = '';
        $gpo = isset($this->prm->gpo) ? $this->prm->gpo : '';
        $ur = isset($this->prm->ur) ? $this->prm->ur : '';
        $clc = isset($this->prm->clc) ? $this->prm->clc : '';

        if ($fechaPago == "") {
            $this->mixScript('notify("Faltan datos por capturar ","error");');
            return 0;
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();
        // creamos la cadena para consultar los seleccionados
        $cadena = $loteDetalles;

        if ($todos == 1) {
            $sqlExtraModoPago = " ";
            switch ($cmd_modoPago) {
                case 0: //todas
                    break;
                case 1: // trasferencia
                    $sqlExtraModoPago = " AND cl.mpc_id = 1 AND cl.bur_id <>'000000000000000000' ";
                    break;
                case 2: //cheque
                    $sqlExtraModoPago = " AND cl.mpc_id = 1 AND cl.bur_id = '000000000000000000' ";
                    break;
                case 3: //linea captura
                    $sqlExtraModoPago = " AND cl.mpc_id = 2";
                    break;
                case 4: //pdp
                    $sqlExtraModoPago = " AND cl.mpc_id = 4";
                    break;
                default:
                    break;
            }

            $parametros = [
                'pro_id' => $idProp,
            ];

            $sql = "SELECT pro_ejercicio FROM " . BD_TESORERIA . ".pro_Propuesta WHERE pro_id= :pro_id";
            $conexion->setSql($sql);
            $conexion->consultar($parametros);
            $row = $conexion->extraer();
            $anio = $row['pro_ejercicio'];
            switch ($anio) {
                case 2017:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2018:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2019:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2020:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2021:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2022:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2023:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2024:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2025:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    break;
                case 2026:
                default:
                    $condicion2017 = "";
                    break;
            }
            $sqlExtra = ($anio == 2017) ? " AND ant.ecl_id=11 AND ant.ejr_id_clc =2017 " :
            (($anio == 2018) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2018 " :
                (($anio == 2019) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2019 " :
                    (($anio == 2020) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2020 " :
                        (($anio == 2021) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2021 " :
                            (($anio == 2022) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2022 " :
                                (($anio == 2023) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2023 " :
                                    (($anio == 2024) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2024 " : 
                                        (($anio == 2025) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc =2025 " :" AND cl.ecl_id=12"))))))));
            $cere++;

            unset($parametros);
            $parametros = [
                'cere' => $cere,
                'pro_id' => $idProp,
            ];
            $sql = "SELECT
                  dp.gpo_id AS gpo,
                  dp.ur_id AS ur,
                  dp.clc_id AS clc
                FROM " . BD_TESORERIA . ".pro_Propuesta AS p
                  INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                  INNER JOIN ctrl" . $anio . ".scp_Clc cl using(gpo_id,ur_id,clc_id)
                  $condicion2017
                WHERE cl.clc_tipoCereCo= :cere AND p.pro_id = :pro_id" . $sqlExtra . $sqlExtraModoPago;

            $conexion->setSql($sql);
            $conexion->consultar($parametros);
            //generacion de la cadena de busqueda
            $cadena = "";
            while ($row = $conexion->extraer()) {
                $cadena .= "(" . $row['gpo'] . "," . $row['ur'] . "," . $row['clc'] . "),";
            }
            if ($cadena == "") {
                $this->mixScript(' notify("NO SE ENCONTRARON DATOS","error");');
                return 0;
            }
            $cadena = substr($cadena, 0, -1);
        }

        unset($parametros);
        $parametros = [
            'pro_id' => $idProp,
        ];

        $sql = "SELECT
            SUM(dp.pro_importeClc) AS importe , p.tlo_id,p.pro_ejercicio,gpo_id,ur_id,clc_id
            FROM " . BD_TESORERIA . ".pro_Propuesta AS p
            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
            WHERE p.pro_id = :pro_id AND (dp.gpo_id,dp.ur_id,dp.clc_id) in (" . $cadena . ") AND p.pro_ejr=" . APP_EJERCICIO . "
            GROUP BY dp.gpo_id, dp.ur_id, dp.clc_id";
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $total = 0;
        $tlo_id = 1; //caso default
        $clc_tipoCereCo = 1; //caso default
        $pro_ejercicio = APP_EJERCICIO; //caso default
        $det_prop = $idProp;
        while ($row = $conexion->extraer()) {
            $total += $row['importe'];
            $tlo_id = $row['tlo_id'];
            $pro_ejercicio = $row['pro_ejercicio'];
            $gpo = $row['gpo_id'];
            $ur = $row['ur_id'];
            $clc = $row['clc_id'];
        }

        $anio = $pro_ejercicio;
        $paramSqllinea = [
            'pro_id' => $det_prop,
            'cereId' => $cereID,
        ];
        if ($tipoPago == 3) {
            switch ($anio) {
                case 2017:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2017 ";
                    break;
                case 2018:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=11 AND ant.ejr_id_clc=2018 ";
                    break;
                case 2019:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2019 ";
                    break;
                case 2020:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2020 ";
                    break;
                case 2021:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2021 ";
                    break;
                case 2022:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2022 ";
                    break;
                case 2023:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2023 ";
                    break;
                case 2024:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2024 ";
                    break;
                case 2025:
                    $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                    $sqlExtra = " AND ant.ecl_id=12 AND ant.ejr_id_clc=2025 ";
                    break;
                case 2026:
                    $condicion2017 = "";
                    $sqlExtra = " AND cl.ecl_id=12 ";
                    break;
            }

            $sqlLinea = "SELECT
                'Linea de Captura' AS mpc_nombre,
                '$det_prop' AS prop,
                '$cere' AS cereId,
                '$anio' AS ejercicio,
                CONCAT(gpo_id,LPAD(ur_id,2,0)) AS ur_id,
                clc_id AS clc,
                gpo_id AS gpo,
                ur_id AS ur,
                cx.clc_cereCo AS orden,
                if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') AS cere,
                pro_importeClc AS importe,
                cl.clc_beneficiario AS beneficiario,
                cl.clc_concepto AS concepto,
                cl.bur_id,
                sum(existe) AS existe,
                referencia AS referenciaCompleta,
                if (count(lin.referencia) = count(lin.lca_lineaCaptura) ,1,0 ) AS completo, #1.->completo ; 2->incompleto
                if (sum(existe)=0 ,'Completo','Incompleto' ) AS lineaEstado, #1.->completo ; 2->incompleto
                lca_importe AS importeLineas,
                fechaAutorizacion,
                lca_lineaCaptura
                FROM
                (
                  SELECT pre.ncp_referencia AS referencia,lc.lca_lineaCaptura,gpo_id,ur_id,clc_id,pro_importeClc,
                      SUM(IF(lca_importe IS NULL ,0,lca_importe)) AS lca_importe,
                      IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) AS existe, # 0 = SIN ERRORES
                      pro_fechaAutorizacion AS fechaAutorizacion
                      FROM " . BD_TESORERIA . ".pro_Propuesta AS pro
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                        INNER JOIN ( select ncp_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl$anio.scp_Presupuestal  where clc_id>0 GROUP BY ncp_referencia,bdo_id,gpo_id,ur_id, clc_id )AS pre using (gpo_id,ur_id,clc_id)
                        left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(pre.ncp_referencia)=19,SUBSTRING(pre.ncp_referencia,1,11),pre.ncp_referencia)
                      where pro.pro_id = :pro_id  AND (pc.gpo_id,pc.ur_id,pc.clc_id) in ($cadena) AND IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5)
                      group by  pre.ncp_referencia,pre.bdo_id,lc.lca_lineaCaptura,pc.gpo_id,pc.ur_id,pc.clc_id
                  UNION
                  SELECT c.ncc_referencia AS referencia,lc.lca_lineaCaptura,gpo_id,ur_id,clc_id,pro_importeClc,
                      SUM(IF(lca_importe IS NULL ,0,lca_importe)) AS lca_importe,
                      IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) AS existe, # 0 = SIN ERRORES
                      pro_fechaAutorizacion AS fechaAutorizacion
                      FROM  " . BD_TESORERIA . ".pro_Propuesta AS pro
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                        INNER JOIN ( select ncc_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl$anio.scp_Contable  where clc_id>0 GROUP BY ncc_referencia,bdo_id,gpo_id,ur_id,clc_id ) AS c using (gpo_id,ur_id,clc_id)
                        left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(c.ncc_referencia)=19,SUBSTRING(c.ncc_referencia,1,11),c.ncc_referencia)
                      WHERE pro.pro_id = :pro_id AND (pc.gpo_id,pc.ur_id,pc.clc_id) in ($cadena) AND IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5)
                      group by  c.ncc_referencia,c.bdo_id,lc.lca_lineaCaptura,gpo_id,ur_id,clc_id
                ) AS lin
                INNER JOIN ctrl$anio.scp_Clc AS cl using(gpo_id,ur_id,clc_id)
                $condicion2017
                INNER JOIN ctrl$anio.scp_ClcAuxiliar AS cx using(gpo_id,ur_id,clc_id)
                where cl.mpc_id = 2 AND cl.clc_tipoCereCo=:cereId $sqlExtra $sqlExtraModoPago
                group by  referencia,lin.gpo_id, lin.ur_id, lin.clc_id, lin.pro_importeClc, lin.lca_importe, lin.fechaAutorizacion,
                lin.lca_lineaCaptura ";
            $sqlClc = "SELECT
                'Linea de Captura' AS mpc_nombre,
                '$det_prop' AS prop,
                '$cere' AS cereId,
                '$anio' AS ejercicio,
                CONCAT(gpo_id,LPAD(ur_id,2,0)) AS ur_id,
                clc_id AS clc,
                gpo_id AS gpo,
                ur_id AS ur,
                cx.clc_cereCo AS orden,
                if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') AS cere,
                pro_importeClc AS importe,
                cl.clc_beneficiario AS beneficiario,
                cl.clc_concepto AS concepto,
                cl.bur_id,
                sum(existe) AS existe,
                referencia AS referenciaCompleta,
                if (count(lin.referencia) = count(lin.lca_lineaCaptura) ,1,0 ) AS completo, #1.->completo ; 2->incompleto
                if (sum(existe)=0 ,'Completo','Incompleto' ) AS lineaEstado, #1.->completo ; 2->incompleto
                sum(lca_importe) AS importeLineas,
                fechaAutorizacion
                FROM
                (
                  SELECT pre.ncp_referencia AS referencia,lc.lca_lineaCaptura,gpo_id,ur_id,clc_id,pro_importeClc,
                      IF(lca_importe IS NULL ,0,lca_importe) AS lca_importe,
                      IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) AS existe, # 0 = SIN ERRORES
                      pro_fechaAutorizacion AS fechaAutorizacion
                      FROM " . BD_TESORERIA . ".pro_Propuesta AS pro
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                        INNER JOIN ctrl$anio.scp_Presupuestal AS pre using (gpo_id,ur_id,clc_id)
                        left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(pre.ncp_referencia)=19,SUBSTRING(pre.ncp_referencia,1,11),pre.ncp_referencia)
                      where pro.pro_id = :pro_id  AND (pc.gpo_id,pc.ur_id,pc.clc_id) in ($cadena) AND IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5)
                      group by ncp_referencia,lca_lineaCaptura,gpo_id,ur_id,clc_id
                  UNION
                  SELECT c.ncc_referencia AS referencia,lc.lca_lineaCaptura,gpo_id,ur_id,clc_id,pro_importeClc,
                      IF(lca_importe IS NULL ,0,lca_importe) AS lca_importe,
                      IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) AS existe, # 0 = SIN ERRORES
                      pro_fechaAutorizacion AS fechaAutorizacion
                      FROM  " . BD_TESORERIA . ".pro_Propuesta AS pro
                        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                        INNER JOIN ctrl$anio.scp_Contable AS c using (gpo_id,ur_id,clc_id)
                        left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(c.ncc_referencia)=19,SUBSTRING(c.ncc_referencia,1,11),c.ncc_referencia)
                      WHERE pro.pro_id = :pro_id AND (pc.gpo_id,pc.ur_id,pc.clc_id) in ($cadena) AND IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5)
                      group by ncc_referencia,lca_lineaCaptura,gpo_id,ur_id,clc_id
                ) AS lin
                INNER JOIN ctrl$anio.scp_Clc AS cl using(gpo_id,ur_id,clc_id)
                $condicion2017
                INNER JOIN ctrl$anio.scp_ClcAuxiliar AS cx using(gpo_id,ur_id,clc_id)
                where cl.mpc_id = 2 AND cl.clc_tipoCereCo= :cereId $sqlExtra $sqlExtraModoPago
                group by  referencia,lin.gpo_id, lin.ur_id, lin.clc_id, lin.pro_importeClc, lin.fechaAutorizacion ";
            $sqlClc2 = "SELECT
                pro_importeClc AS importe,
                cl.clc_beneficiario AS beneficiario,
                cl.clc_concepto AS concepto,
                cl.bur_id
                FROM " . BD_TESORERIA . ".pro_Propuesta AS pro
                INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                INNER JOIN ctrl$anio.scp_Clc AS cl using(gpo_id,ur_id,clc_id)
                $condicion2017
                INNER JOIN ctrl$anio.scp_ClcAuxiliar AS cx using(gpo_id,ur_id,clc_id)
                WHERE pro.pro_id = :pro_id AND (pc.gpo_id,pc.ur_id,pc.clc_id) in ($cadena) AND cl.mpc_id = 2 AND cl.clc_tipoCereCo= :cereId $sqlExtra $sqlExtraModoPago
              ";
            //recorremos el resultado buscando errores
            $conexion->setSql($sqlLinea);
            $conexion->consultar($paramSqllinea);
            $totalImporteLineas = 0;
            $totalImporteClc = 0;
            $lineaContador = 0;
            $cadenaLineas = "";
            $lineaCapturaAnterior = '';
            while ($row = $conexion->extraer()) {
                if ($row['existe'] == 0) {
                    if ($lineaContador == 0) {
                        $totalImporteLineas += $row['importeLineas'];
                        $lineaCapturaAnterior = $row['lca_lineaCaptura'];
                    } else {
                        if ($lineaCapturaAnterior == $row['lca_lineaCaptura']) {
                            // Si la linea de captura es igual no se suma
                        } else {
                            $totalImporteLineas += $row['importeLineas'];
                        }
                        $lineaCapturaAnterior = $row['lca_lineaCaptura'];
                    }
                    $referenciaValida = $row['referenciaCompleta'];
                    $lineaContador++;
                    $cadenaLineas .= "'" . $row['lca_lineaCaptura'] . "',";
                }
            }
            $conexion->setSql($sqlClc2);
            $conexion->consultar($paramSqllinea);
            $datosCorrectos = 0;
            while ($row = $conexion->extraer()) {
                $totalImporteClc += $row['importe'];
                $datosCorrectos++;
            }
            if ($datosCorrectos == 0) {
                $this->mixScript(' notify("NO HAY DATOS VALIDOS ","error");');
                return 0;
            }
            $diff = abs($totalImporteClc - $totalImporteLineas);
            if ($diff > 1.0) {
                $this->mixScript("
                    var dialogo=null;
                    var totalImporteClc=$totalImporteClc;
                    var totalImporteLineas=$totalImporteLineas;
                    var tareaGenerarLoteLineaDeCaptura='" . (($this->generarLoteLineaDeCaptura) ? 'true' : 'false') . "';
                    alertify.confirm('Generar lote', 'Hay diferencias entre el <strong>importe total de CLCs</strong> y el <strong>importe de la Linea de captura</strong> de  $" . (number_format($diff, 2)) . "<br></br>'+
                        '¿Desea generar el lote?',
                        function(){
                            if(totalImporteLineas>totalImporteClc){
                                comunica('" . $this->pantalla . "',{
                                    metodo: 'mostrarDialogoParaGenerarLote',
                                    idProp:" . $idProp . ",
                                    cadena:'" . $cadena . "',
                                    anio:'" . $anio . "'
                                });
                                
                            }else{
                                    notify('No puede generar el lote porque el importe de la Linea de Captura es menor al importe total de la propuesta','warning');
                                }
                        },
                        function(){},
                    ).set('defaultFocus','cancel').set('labels', {ok:'Si', cancel:'No'});
                ");
                return 0;
            }
        }

        if ($tipoPago == 3) {
            //verificar si las lineas de captura estan disponibles para pago
            $cadenaLineas = substr($cadenaLineas, 0, -1);
            $respuestaLineas = ws_Consulta_Linea($cadenaLineas, 2);
            $arrayLc = json_decode($respuestaLineas);
            foreach ($arrayLc as $j => $lc) {
                //temporal quitar cuando haya conexion siox
                $lcLineaEvalua = $lc->lineaCaptura;
                if ($lc->estatus != 'GENERADA') {
                    $estadoLC = $lc->estatus;
                    $this->mixScript("
                console.log('error de estado $lcLineaEvalua');
                notify('Error de estado de la linea $lcLineaEvalua $estadoLC ','error');
                ");
                    return 0;
                }
            }
        }
        //optenemos el tipoCereCo
        unset($parametros);
        $parametros = [
            'gpo' => $gpo,
            'ur_id' => $ur,
            'clc_id' => $clc,
        ];
        $sql = "SELECT clc_tipoCereCo,mpc_id,bur_id,tcl_id FROM
                ctrl" . $pro_ejercicio . ".scp_Clc
            WHERE gpo_id = :gpo AND ur_id= :ur_id AND clc_id= :clc_id";

        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $clc_tipoCereCo = 1; //caso default
        $bur_id = 0;
        while ($row = $conexion->extraer()) {
            $clc_tipoCereCo = $row['clc_tipoCereCo'];
            $mpc_id = $row['mpc_id'];
            $bur_id = $row['bur_id'];
            $tcl_id = $row['tcl_id'];
        }

        //buscamos el ultimo consecutivo
        unset($parametros);
        $parametros = [
            'msc_id' => $mes,
            'fpa_id' => $tipoPago,
            'lot_ejercicio' => $pro_ejercicio,
        ];
        $sql = "SELECT MAX(lot_id) lot_id FROM " . BD_TESORERIA . ".lot_Lote WHERE  msc_id= :msc_id AND fpa_id= :fpa_id AND lot_ejercicio = :lot_ejercicio";
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $consecutivo = 0;
        while ($row = $conexion->extraer()) {
            $consecutivo = $row['lot_id'];
        }
        $consecutivo++;

        //antes de iniciar verificamos que el lote no exista en clc_loteTesoreria para 2017
        //Para el 2018 no existe dicha tabla por ese motivo no se realiza la consulta
        if ($pro_ejercicio == 2017) {
            $params2017 = [
                'pro_id' => $idProp,
            ];
            $sql2017 = "SELECT lot_id, clc_id
                FROM " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria
                WHERE (gpo_id,ur_id,clc_id) in
                (
                    SELECT gpo_id,ur_id,clc_id
                    FROM " . BD_TESORERIA . ".pro_Propuesta AS p
                    INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                    WHERE p.pro_id = :pro_id
                    AND (dp.gpo_id,dp.ur_id,dp.clc_id) in (" . $cadena . ")
                    AND p.pro_ejr=" . APP_EJERCICIO . "
                )";
            $conexion->setSql($sql2017);
            $conexion->consultar($params2017);
            $conexion->extraer();
            if ($conexion->getNumeroRegistros() > 0) {
                $error = "ERROR LOTE CLC:";
                while ($row = $conexion->extraer()) {
                    $error .= $row['clc_id'];
                }
                $this->mixScript('notify("error en las Clc","error");');
                return 0;
            }
        }
        //reordenamos el formato de fecha  año/mes/dia y validamos
        $arrFecha = explode("/", $fechaPago);
        $fechaPago = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        $datetime1 = new DateTime($fechaPago . '00:00:00.000');
        $datetime2 = new DateTime($fecha . '00:00:00.000');
        if ($datetime1 < $datetime2) {
            $this->mixScript('
        notify( "LA FECHA DE PAGO NO PUEDE SER MENOR", "error" );
      ');
            return 0;
        }
        //2.- insertar el consecutivo
        $lot_id = $consecutivo;
        $this->prm->fpa_id = $tipoPago;
        $this->prm->msc_id = $mes;
        $this->prm->ejercicio = $anio;
        $this->prm->lot_id = $consecutivo;
        $this->prm->fechaGeneracion = $fecha;
        $this->prm->fechaPago = $fechaPago;
        $this->prm->importe = $total;
        $this->prm->tipoCereCo = $clc_tipoCereCo; // verificar deficiones
        $this->prm->flujoEfectivo = 1; // verificar deficiones
        $this->prm->elo_id = 3; // Lote Generado en ministracion
        $this->prm->lot_ejr = APP_EJERCICIO; // Identificador en que sistema se realiza la captura
        $this->prm->abc = "a";
        if ($tcl_id == 'I' and $bur_id == '000000000000000000') {
            $this->prm->tlo_id = 4; //1-normal 2-pdp 3-cadenas 4-municipios
        } else {
            $this->prm->tlo_id = $tlo_id; //1-normal 2-pdp 3-cadenas 4-municipios
        }
        //3.- insertar el lote
        Lote::abc($this->prm);
        $nuevoLote = '';
        switch ($tipoPago) {
            case 2:
                $nuevoLote = 'C';
                break;
            case 3:
                $nuevoLote = 'L';
                break;
            case 4:
                $nuevoLote = 'I';
                break;
            case 1:
            default:
                $nuevoLote = 'T';
                break;
        }
        $nuevoLote = $nuevoLote . str_pad($mes, 2, "0", STR_PAD_LEFT) . str_pad($consecutivo, 4, "0", STR_PAD_LEFT) . substr($anio, 2, 2);
        // insertamos la vinculacion Propuesta-Lote
        $this->vin = new stdClass();
        $this->vin->pro_id = $this->prm->idProp;
        $this->vin->fpa_id = $this->prm->fpa_id;
        $this->vin->msc_id = $this->prm->msc_id;
        $this->vin->lot_id = $this->prm->lot_id;
        $this->vin->ejercicio = $this->prm->ejercicio;
        $this->vin->abc = "a";
        PropuestaLote::abc($this->vin);
        //4.- insertamos el loteDetalles
        unset($parametros);
        $parametros = [
            'pro_id' => $idProp,
        ];
        $sql = "SELECT
              dp.gpo_id AS gpo,
              dp.ur_id AS ur,
              dp.clc_id AS clc,
              dp.pro_importeClc AS importe,
              dp.bur_id AS bur_id
            FROM " . BD_TESORERIA . ".pro_Propuesta AS p
              INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
            WHERE p.pro_id = :pro_id AND (dp.gpo_id,dp.ur_id,dp.clc_id) in (" . $cadena . ")
            GROUP BY dp.gpo_id, dp.ur_id, dp.clc_id
         ";
        if ($tipoPago == 3) {
            //caso lieas de captura
            $sql = $sqlClc;
            $parametros = $paramSqllinea;
        }
        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexionLinea = new AccesoDatos();
        $conexionLinea->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        while ($row = $conexion->extraer()) {
            $this->prm->fpa_id = $tipoPago;
            $this->prm->msc_id = $mes;
            $this->prm->ejercicio = $anio;
            $this->prm->lot_id = $consecutivo;
            $this->prm->gpo_id = $row['gpo'];
            $this->prm->ur_id = $row['ur'];
            $this->prm->clc_id = $row['clc'];
            $this->prm->importeClc = $row['importe'];
            $this->prm->bur_id = $row['bur_id'];
            $cambioReferencia = 0;
            //comparamos los tipos de datos
            switch ($tipoPago) {
                case 3:
                    if ($row['existe'] == 0) {
                        $datosCorrectos++;
                    } else if ($diff > 1.0) {
                        break;
                    } else {
                        $cambioReferencia = 1;
                    }
                case 1:
                case 2:
                default:
                    $this->prm->abc = 'a';
                    LoteDetalle::abc($this->prm);
                    //es modo cadenas productivas
                    if ($tlo_id == 3) {
                        switch ($pro_ejercicio) {
                            case 2018:
                                $metodoConsulta = new clcs2018();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2019:
                                $metodoConsulta = new clcs2019();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2020:
                                $metodoConsulta = new clcs2020();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2021:
                                $metodoConsulta = new clcs2021();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2022:
                                $metodoConsulta = new clcs2022();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2023:
                                $metodoConsulta = new clcs2023();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2024:
                                $metodoConsulta = new clcs2024();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2025:
                                $metodoConsulta = new clcs2025();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                            case 2026:
                            default:
                                $metodoConsulta = new clcs2026();
                                $sql = $metodoConsulta->sqlDocumentosCadenaProductiva($this->prm);
                                break;
                        }
                        $conexion2->setSql($sql);
                        $conexion2->conectar();
                        $conexion2->consultar();
                        $total_Doc = 0;
                        while ($rowDoc = $conexion2->extraer()) {
                            $this->prm->bdo_id = $rowDoc['bdo_id'];
                            $total_Doc += $rowDoc['importeBdo'];
                            $this->prm->importeBdo = $rowDoc['importeBdo'];
                            $this->prm->rfc_id = $rowDoc['rfc_id'];
                            LoteDocumento::abc($this->prm);
                        }
                    } else if ($tlo_id == 2) { // es pdp
                        switch ($pro_ejercicio) {
                            case 2017:
                                $metodoConsulta = new clcs2017();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2018:
                                $metodoConsulta = new clcs2018();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2019:
                                $metodoConsulta = new clcs2019();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2020:
                                $metodoConsulta = new clcs2020();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2021:
                                $metodoConsulta = new clcs2021();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2022:
                                $metodoConsulta = new clcs2022();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2023:
                                $metodoConsulta = new clcs2023();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2024:
                                $metodoConsulta = new clcs2024();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2025:
                                $metodoConsulta = new clcs2025();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                            case 2026:
                            default:
                                $metodoConsulta = new clcs2026();
                                $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                                break;
                        }
                        $conexion2->setSql($sql);
                        $conexion2->conectar();
                        $conexion2->consultar();
                        $total_Doc = 0;
                        while ($rowDoc = $conexion2->extraer()) {
                            $this->prm->bdo_id = $rowDoc['bdo_id'];
                            $total_Doc += $rowDoc['importeBdo'];
                            $this->prm->importeBdo = $rowDoc['importeBdo'];
                            $this->prm->rfc_id = $rowDoc['rfc_id'];
                            LoteDocumento::abc($this->prm);
                        }
                    } else if ($tipoPago == 3) {
                        //es modo LineaCaptura
                        $gpo_linea = $row['gpo'];
                        $ur_linea = $row['ur'];
                        $clc_linea = $row['clc'];
                        //1.-buscamos sus documentos con referencia x clc
                        switch ($pro_ejercicio) {
                            case 2017:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2018:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2019:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2020:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2021:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2022:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2023:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2024:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2025:
                                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                                break;
                            case 2026:
                            default:
                                $condicion2017 = "";
                                break;
                        }
                        $sqlExtra = ($pro_ejercicio == 2017) ? " AND ant.ecl_id=11 AND ant.ejr_id_clc=2017 " : (($pro_ejercicio == 2018) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2018 " :
                            (($pro_ejercicio == 2019) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2019 " :
                                (($pro_ejercicio == 2020) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2020 " :
                                    (($pro_ejercicio == 2021) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2021 " :
                                        (($pro_ejercicio == 2022) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2022 " :
                                            (($pro_ejercicio == 2023) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2023 " :
                                                (($pro_ejercicio == 2024) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2024 " : 
                                                    (($pro_ejercicio == 2025) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2025 " :" AND cl.ecl_id=12"))))))));
                        unset($parametros);
                        $parametros = [
                            'cereId' => $cereID,
                            'pro_id' => $idProp ,
                            'gpo_id' => $gpo_linea,
                            'ur_id' => $ur_linea,
                            'clc_id' => $clc_linea,
                        ];
                        $sql = "SELECT
                            doc.bdo_id,
                            MAX(b.rfc_id) rfc_id,
                            MAX(b.lca_id) lca_id,
                            (doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable) AS importeBdo,
                            MAX(referencia) referencia,
                            MAX(existe) existe
                            FROM  (SELECT
                            dp.gpo_id,
                            dp.ur_id,
                            dp.clc_id,
                            c.bdo_id,
                            MAX(c.rfc_id) rfc_id,
                            MAX((c.ncc_percepcion-c.ncc_retencion)) AS importeBdo,
                            MAX(c.ncc_referencia) as referencia,
                            MAX(lc.lca_id) lca_id,
                            MAX(if (lc.lca_lineaCaptura is null,0,1)) as existe #1.-> existe ;0->no existe
                            FROM " . BD_TESORERIA . ".pro_Propuesta AS p
                            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                            INNER JOIN ctrl" . $pro_ejercicio . ".scp_Clc cl using(gpo_id,ur_id,clc_id)
                            $condicion2017
                            INNER JOIN ctrl" . $pro_ejercicio . ".scp_Contable c USING (gpo_id,ur_id,clc_id)
                            left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = c.ncc_referencia
                            WHERE
                                cl.clc_tipoCereCo= :cereId AND cl.mpc_id = 2  AND p.pro_id = :pro_id AND dp.gpo_id = :gpo_id AND dp.ur_id = :ur_id AND dp.clc_id = :clc_id " . $sqlExtra . $sqlExtraModoPago . "
                            GROUP BY bdo_id

                            UNION ALL

                            SELECT
                            dp.gpo_id,
                            dp.ur_id,
                            dp.clc_id,
                            p.bdo_id,
                            MAX(p.rfc_id) rfc_id,
                            MAX(ncp_percepcionNeto) AS importeBdo,
                            MAX(p.ncp_referencia) as referencia,
                            MAX(lc.lca_id) lca_id,
                            MAX(if (lc.lca_lineaCaptura is null,0,1)) as existe #1.-> existe ;0->no existe
                            FROM
                            " . BD_TESORERIA . ".pro_Propuesta AS p
                            INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                            INNER JOIN ctrl" . $pro_ejercicio . ".scp_Clc cl using(gpo_id,ur_id,clc_id)
                            $condicion2017
                            inner join ctrl" . $pro_ejercicio . ".scp_Presupuestal p USING (gpo_id,ur_id,clc_id)
                            left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = p.ncp_referencia
                            WHERE
                              cl.clc_tipoCereCo= :cereId AND cl.mpc_id = 2  AND p.pro_id = :pro_id AND dp.gpo_id = :gpo_id AND dp.ur_id = :ur_id AND dp.clc_id = :clc_id " . $sqlExtra . $sqlExtraModoPago . "
                            GROUP BY bdo_id
                                ) AS b
                            INNER JOIN ctrl" . $pro_ejercicio . ".cat_BancoDocumentos AS doc USING(gpo_id, ur_id, bdo_id)
                            GROUP BY gpo_id, ur_id, bdo_id
                        ";
                        $conexion2->setSql($sql);
                        $conexion2->conectar();
                        $conexion2->consultar($parametros);
                        $total_Doc = 0;
                        while ($rowDoc = $conexion2->extraer()) {
                            $this->prm->bdo_id = $rowDoc['bdo_id'];
                            $total_Doc += $rowDoc['importeBdo'];
                            $this->prm->importeBdo = $rowDoc['importeBdo'];
                            $this->prm->rfc_id = $rowDoc['rfc_id'];
                            LoteDocumento::abc($this->prm);
                            if ($rowDoc['existe'] == 0) {
                                $idclc = $gpo_linea . $ur_linea . $clc_linea;
                                $a = $idclc;
                                $b = $idclc;
                                if ($cambioReferencia == 1) {
                                    $conexion3 = new AccesoDatos();
                                    $conexion3->conectar();
                                    $paramsCon3 =[
                                        'lca_LineaCaptura' => $rowDoc['referencia'],
                                    ];
                                    $conexion3->setSql('SELECT lca_folio FROM ' . BD_TESORERIA . '.lca_LineaCaptura WHERE lca_LineaCaptura= :lca_LineaCaptura";');
                                    $conexion3->consultar($paramsCon3);
                                    $getReferencia = $conexion3->extraer();
                                    $conexion3->desconectar();

                                    $paramsDocL = [
                                        'lca_folio' => $getReferencia['lca_folio'],
                                    ];
                                    $sqlDocLinea = " SELECT lca_id FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_folio= :lca_folio";
                                    $conexionLinea->setSql($sqlDocLinea);
                                    $conexionLinea->conectar();
                                    $conexionLinea->consultar($paramsDocL);
                                    while ($rowLinea = $conexionLinea->extraer()) {
                                        $this->prm->lca_id = $rowLinea['lca_id'];
                                        LoteDocumentoLineaCaptura::abc($this->prm);
                                    }
                                } else {
                                    $this->mixScript("notify('NO SE ENCONTRO LA LINEA $a $b ','error');");
                                }
                            } else {
                                // buscar por cada documento sus lineas
                                $paramSqlDocl = [
                                    'lca_folio' => $rowDoc['referencia'],
                                ];
                                $sqlDocLinea = " SELECT lca_id FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_folio= :lca_folio";
                                $conexionLinea->setSql($sqlDocLinea);
                                $conexionLinea->conectar();
                                $conexionLinea->consultar($paramSqlDocl);
                                while ($rowLinea = $conexionLinea->extraer()) {
                                    $this->prm->lca_id = $rowLinea['lca_id'];
                                    LoteDocumentoLineaCaptura::abc($this->prm);
                                }
                            }
                        }
                        //insertamos la vinculacion.
                    }
                    //cambiamos de estado las CLCs
                    $paramUpdate = [
                        'gpo_id' => $this->prm->gpo_id,
                        'ur_id' => $this->prm->ur_id,
                        'clc_id' => $this->prm->clc_id,
                        'pro_ejercicio' => $pro_ejercicio,
                    ];
                    $paramSet = [
                        'usr_id' => $this->usr_id,
                        'ip' => $this->ip,
                    ];
                    switch ($pro_ejercicio) {
                        case 2017:
                            // 3.-cambiamos el estado a estado clc 11
                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=11 WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2018:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2019:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2020:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2021:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2022:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2023:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2024:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2025:
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexion2->setSql($sql);
                            $conexion2->consultar($paramSet);

                            $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=13 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc= :pro_ejercicio";
                            $conexion2->setSql($sql3);
                            $conexion2->consultar($paramUpdate);
                            break;
                        case 2026:
                            $this->clcCambia = new stdClass();
                            $this->clcCambia->gpo_id = $this->prm->gpo_id;
                            $this->clcCambia->ur_id = $this->prm->ur_id;
                            $this->clcCambia->clc_id = $this->prm->clc_id;
                            $this->clcCambia->ecl_id = 13;
                            $this->clcCambia->abc = 'c';
                            $metodoConsulta = new clcs2025();
                            $conexionCLC = new AccesoDatos();
                            $conexionCLC->conectar();
                            $parametros2025 = [
                                'gpo_id' => $this->clcCambia->gpo_id,
                                'ur_id' => $this->clcCambia->ur_id,
                                'clc_id' => $this->clcCambia->clc_id,
                            ];
                            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Actualizacion por Generacion de Lote'";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($paramSet);
                            $sql = " UPDATE " . BD_CTRL . ".scp_ClcAuxiliar set ecl_id=13 where gpo_id= :gpo_id and ur_id= :ur_id and clc_id= :clc_id";
                            $conexionCLC->setSql($sql);
                            $conexionCLC->consultar($parametros2025);
                            $sql = $metodoConsulta->sqlDocumentosPDP($this->prm);
                            break;
                    }
            }
        }
        //cambiamos de estado de la propuesta
        //preguntamos si tiene propuestas en estado 10
        switch ($anio) {
            case 2017:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2018:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2019:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2020:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2021:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2022:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2023:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2024:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2025:
                $condicion2017 = " INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id,ur_id,clc_id) ";
                break;
            case 2026:
            default:
                $condicion2017 = "";
                break;
        }
        $sqlExtra = ($pro_ejercicio == 2017) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2017" :
        (($pro_ejercicio == 2018) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2018" :
            (($pro_ejercicio == 2019) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2019" :
                (($pro_ejercicio == 2020) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2020" :
                    (($pro_ejercicio == 2021) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2021 " :
                        (($pro_ejercicio == 2022) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2022 " :
                            (($pro_ejercicio == 2023) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2023 " :
                                (($pro_ejercicio == 2024) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2024 " :
                                    (($pro_ejercicio == 2025) ? " AND  ant.ecl_id=12 AND ant.ejr_id_clc=2025 ": " AND  c.ecl_id=12 "))))))));
        $paramsclc = [
            'pro_id' => $idProp,
        ];
        $sqlClc = "SELECT
              dp.gpo_id AS gpo,
              dp.ur_id AS ur,
              dp.clc_id AS clc,
              dp.pro_importeClc AS importe,
              dp.bur_id AS bur_id
            FROM " . BD_TESORERIA . ".pro_Propuesta AS p
              INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
              INNER JOIN ctrl" . $pro_ejercicio . ".scp_Clc AS c using(gpo_id,ur_id,clc_id)
              $condicion2017
            WHERE p.pro_id = :pro_id" . $sqlExtra;
        $conexion->setSql($sqlClc);
        $conexion->consultar($paramsclc);
        $conexion->extraer();
        if ($conexion->getNumeroRegistros() > 0) {
            //quedan registros por lotificar
        } else {
            //no quedan registros en la propuesta
            $this->prm2 = new stdClass();
            $this->prm2->abc = 'c';
            $this->prm2->pro_id = $idProp;
            $this->prm2->elo_id = 3;
            EntidadPropuesta::abc($this->prm2);
        }
        $this->mixScript('
            $("#toolsPantalla").html("");
            notify("LOTE GENERADO EXITOSAMENTE ' . $nuevoLote . '","success");');
        $this->pintaGeneraLote();
    }

    private function GeneraLotePorProcedimiento()
    {
        //1.- consultar por el consecutivo
        $fechaPago = isset($this->prm->fechaPago) ? $this->prm->fechaPago : "";
        $loteDetalles = isset($this->prm->loteDetalles) ? $this->prm->loteDetalles : "";
        $idProp = isset($this->prm->idProp) ? $this->prm->idProp : "";
        $tipoPago = isset($this->prm->tipoPago) ? $this->prm->tipoPago : "";
        $todos = isset($this->prm->todos) ? $this->prm->todos : "";
        $cere = isset($this->prm->cere) ? $this->prm->cere : "";
        $gpo = isset($this->prm->gpo) ? $this->prm->gpo : '';
        $ur = isset($this->prm->ur) ? $this->prm->ur : '';
        $clc = isset($this->prm->clc) ? $this->prm->clc : '';
        $condicion2017 = '';
        if ($fechaPago == "") {
            $this->mixScript('notify("Faltan datos por capturar ","error");');
            return 0;
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();
        // creamos la cadena para consultar los seleccionados
        $cadena = $loteDetalles;

        if ($todos == 0) {
            // ########################
            // Consultamos la propuesta para obtener pro_ejercicio
            // ########################
            $parametros = [
                'pro_id' => $idProp,
            ];
            $sql = "SELECT
        dp.pro_importeClc AS importe , p.tlo_id,p.pro_ejercicio,gpo_id,ur_id,clc_id
        FROM " . BD_TESORERIA . ".pro_Propuesta AS p
        INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
        WHERE p.pro_id = :pro_id AND (dp.gpo_id,dp.ur_id,dp.clc_id) in (" . $cadena . ") AND p.pro_ejr=" . APP_EJERCICIO . "
        GROUP BY dp.gpo_id, dp.ur_id, dp.clc_id";

            $conexion->setSql($sql);
            $conexion->consultar($parametros);
            $pro_ejercicio = APP_EJERCICIO; //caso default
            // ########################
            // Validamos la linea de captura
            // ########################
            while ($row = $conexion->extraer()) {
                if ($tipoPago == 3) {
                    $ejercicio = $row["pro_ejercicio"];
                    switch ($ejercicio) {
                        case 2017:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2018:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2019:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2020:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2021:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2022:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2023:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2024:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2025:
                            $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                            break;
                        case 2026:
                        default:
                            $condicion2017 = "";
                            break;
                    }
                    $sqlExtra = $ejercicio == 2017 ? " AND ant.ecl_id=10 AND ant.ejr_id_clc=2017 " :
                    (($ejercicio == 2018) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2018 " :
                        (($ejercicio == 2019) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2019 " :
                            (($ejercicio == 2020) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2020 " :
                                (($ejercicio == 2021) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2021 " :
                                    (($ejercicio == 2022) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2022 " :
                                        (($ejercicio == 2023) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2023 " :
                                            (($ejercicio == 2024) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2024 " : 
                                                (($ejercicio == 2025) ? " AND ant.ecl_id=12 AND ant.ejr_id_clc=2025 " :" AND cl.ecl_id=12"))))))));
                    //caso linea de captura
                    $parametroSql2 = [
                        'gpo' => $gpo,
                        'ur' => $ur,
                        'clc' => $clc,
                    ];
                    $sql2 = "SELECT
                        concat(b.gpo_id,LPAD(b.ur_id,2,0)) as ur_id,
                        b.clc_id as clc,
                        b.cereCo,
                        b.cere,
                        b.importe as imp,
                        b.beneficiario,
                        b.concepto,
                        doc.bdo_id,
                        b.rfc_id,
                        b.linea,
                        (doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable) AS importeBdo,
                        referencia,
                        if( b.importe<>lca_importe,0, existe) as existe,
                        lca_fechaGeneracion,
                        lca_fechaVencimiento,
                        lca_importe,
                        lca_clabeAbono,
                        estado,
                        lca_id
                        #if (count(b.referencia) = count(b.lca_folio) ,1,0 ) as completo #1.->completo ; 2->incompleto
                        FROM  (
                            SELECT
                                cl.gpo_id,
                                cl.ur_id,
                                cl.clc_id,
                                c.bdo_id,
                                MAX(c.rfc_id) rfc_id,
                                MAX((c.ncc_percepcion-c.ncc_retencion)) AS importeBdo,
                                MAX(c.ncc_referencia) as referencia,
                                lc.lca_folio,
                                ax.clc_cereCo as cereCo,
                                (cl.clc_percepcion - cl.clc_retencion) as importe,
                                cl.clc_concepto as concepto,
                                cl.clc_beneficiario as beneficiario,
                                lc.lca_lineaCaptura as linea,
                                if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') as cere,
                                IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) as existe, #1.-> existe ;0->no existe
                                IF (lc.lca_lineaCaptura is null,'NO SE ENCONTRO', IF (lc.lca_fechaVencimiento < DATE(NOW()),'VENCIDA','VIGENTE')) as estado, #1.-> existe ;0->no existe
                                lc.lca_fechaGeneracion,
                                lc.lca_fechaVencimiento,
                                lc.lca_importe,
                                lc.lca_clabeAbono,
                                lc.lca_id

                            FROM ctrl$ejercicio.scp_Clc cl
                                $condicion2017
                            INNER JOIN ctrl$ejercicio.scp_ClcAuxiliar ax using(gpo_id,ur_id,clc_id)
                            INNER JOIN ctrl$ejercicio.scp_Contable c USING (gpo_id,ur_id,clc_id)
                                left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = c.ncc_referencia
                            WHERE
                                cl.gpo_id= :gpo and cl.ur_id= :ur and cl.clc_id= :clc  AND  IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5) $sqlExtra
                                group by bdo_id,lca_lineaCaptura

                            UNION ALL

                            SELECT
                                cl.gpo_id,
                                cl.ur_id,
                                cl.clc_id,
                                p.bdo_id,
                                MAX(p.rfc_id),
                                MAX(ncp_percepcionNeto) AS importeBdo,
                                MAX(p.ncp_referencia) as referencia,
                                lc.lca_folio,
                                ax.clc_cereCo as cereCo,
                                (cl.clc_percepcion - cl.clc_retencion) as importe,
                                cl.clc_concepto as concepto,
                                cl.clc_beneficiario as beneficiario,
                                lc.lca_lineaCaptura as linea,
                                if (cl.clc_tipoCereCo = 1, 'CE' , 'CO') as cere,
                                IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) as existe, #1.-> existe ;0->no existe
                                IF (lc.lca_lineaCaptura is null,'NO SE ENCONTRO', IF (lc.lca_fechaVencimiento < DATE(NOW()),'VENCIDA','VIGENTE')) as estado, #1.-> existe ;0->no existe
                                lc.lca_fechaGeneracion,
                                lc.lca_fechaVencimiento,
                                lc.lca_importe,
                                lc.lca_clabeAbono,
                                lc.lca_id

                            FROM
                                ctrl$ejercicio.scp_Clc cl
                                $condicion2017
                            INNER JOIN ctrl$ejercicio.scp_ClcAuxiliar ax using(gpo_id,ur_id,clc_id)
                            inner join ctrl$ejercicio.scp_Presupuestal p USING (gpo_id,ur_id,clc_id)
                            left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = p.ncp_referencia
                            WHERE
                                cl.gpo_id= :gpo and cl.ur_id= :ur and cl.clc_id= :clc  AND  IF(lc.elc_id is null,'1=1 ', lc.elc_id!=5) $sqlExtra
                            group by bdo_id,lca_lineaCaptura
                        ) AS b
                        INNER JOIN ctrl$ejercicio.cat_BancoDocumentos AS doc USING(gpo_id, ur_id, bdo_id)
                    ";
                    $conexion2 = new AccesoDatos();
                    $conexion2->conectar();
                    $conexion2->setSql($sql2);
                    $conexion2->consultar($parametroSql2);
                    $row2 = $conexion2->extraer();
                    $linea = $row2["linea"];
                    $casosLineas = 1; //default
                    //determinar el tipo de linea
                    if (is_numeric($linea)) {
                        if (strlen($linea) == 19) {
                            $casosLineas = 2;
                        } else if (strlen($linea) == 11) {
                            $casosLineas = 1;
                        }
                    } else {
                        $lc = substr($linea, 0, 1);
                        if ($lc == 'V') {
                            $casosLineas = 3;
                        }
                    }
                    //si exite en siox y no esta registrada la agregamos a la tabla
                    //buscamos la linea de captura en webservice
                    $cadLineas = "'" . $linea . "'";

                    $respuestaLineas = ws_Consulta_Linea($cadLineas, $casosLineas);
                    $arrayLc = json_decode($respuestaLineas);
                    $existeSiox = 0;

                    foreach ($arrayLc as $j => $lc) {
                        $existeSiox = 1;
                    }

                    if ($existeSiox != 1) {
                        $this->mixScript(" notify('LA LINEA NO EXISTE EN SIOX','error')");
                        return 0;
                    }
                }
            }
            // ########################
            // Insertamos en tesoreriaTemporal.tem_LoteClc
            // ########################
            $conexion->setSql($sql);
            $conexion->consultar($parametros);

            $conexion2 = new AccesoDatos();
            $conexion2->conectar();

            $paramSet = [
                'usr_id' => $this->usr_id,
                'ip' => $this->ip,
            ];
            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Insertar la propuesta temporal desde la aplicacíon Tesorería(Lotes)'";
            $conexion2->setSql($sql);
            $conexion2->consultar($paramSet);

            while ($row = $conexion->extraer()) {
                $pro_ejercicio = $row['pro_ejercicio'];
                $paramInsert = [
                    'tipoPago' => $tipoPago,
                    'fechaPago'=> (substr($fechaPago, 3, 2)),
                    'idProp' => $idProp,
                    'pro_ejercicio' => $row["pro_ejercicio"],
                    'gpo_id' => $row["gpo_id"],
                    'ur_id' => $row["ur_id"],
                    'clc_id' => $row["clc_id"],
                    'usr_id' => $this->usr_id,
                ];

                $sql = 'INSERT INTO ' . BD_TESORERITEM . '.tem_LoteClc (fpa_id,msc_id,pro_id,lot_ejercicio,gpo_id,ur_id,clc_id, usr_id)
                    VALUES ( :tpoPago, :fechaPago, ;idProp, :pro_ejercicio,
                        :gpo_id, :ur_id, :clc_id,
                        :usr_id);';
                $conexion2->setSql($sql);
                $conexion2->consultar($paramInsert);
            }
            $conexion2->desconectar();
            $conexion->desconectar();

            $datosSP = new stdClass();
            $datosSP->todas_clcs = $todos;
            $datosSP->fpa_id = $tipoPago;
            $datosSP->lot_ejercicio = $pro_ejercicio;
            $datosSP->fecha_pago = $fechaPago;
            $datosSP->pro_id = $idProp;
            $datosSP->documento = $cere;
            $datosSP->bit_observacion = 'Insertado desde pantalla tesoreria(lotes)';
            $datosSP->sis_id = "TES";

            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Generar lote desde la aplicación tesoreria(lotes)'";
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($paramSet);
            $respuesta = sp_GenerarLoteEstatal::call($datosSP);
            $this->mixScript("notify('" . $respuesta->mensaje . "');");
        }
    }
    private function pintaAutorizaLote()
    {
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-1";
        $btnregresarindex->icon = "solid fa-circle-left me-2";
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{
                metodo: "selecciona"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        $tabla = new stdClass();
        $tabla->id = 'tbl-AutorizaLote';
        $tabla->titulo = 'Listado de Lotes Pendientes de Autorizar';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AÑO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROUPESTA';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'zoom-in';
        $td->class = 'detprop';
        $td->id = 'det_prop';
        $td->attr = new stdClass();
        $td->attr->detProp = 'det_prop';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tipo_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'anio_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fecha_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'resp_prop';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total_prop';
        $td->align = 'right';
        $td->formato = "moneda";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->color = 'rojo';
        $td->class = 'delprop';
        $td->id = 'del_prop';
        $td->attr = new stdClass();
        $td->attr->delProp = 'del_prop';

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $tabla->comunica->metodo = 'llenaTablaAutorizaLote';

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaAutorizaLote();
    }

    private function llenaTablaAutorizaLote()
    {
        $sql = "SELECT p.lot_id AS lot_id,
            pd.beneficiario as beneficiario,
            p.id_propuesta AS prop,
            p.fecha_generacion AS fecha_gen,
            p.fecha_pago AS fecha_pago,
            IFNULL(SUM(pd.importe), 0) as total_prop
            FROM lotepago AS p
            INNER JOIN cat_tipopropuesta AS tp
            USING (tipo_id)
            INNER JOIN cat_usuario AS u
            USING (usr_id)
            LEFT JOIN lotepagodetalle as pd
            USING (lot_id)
            WHERE p.id_estado = 2
            GROUP BY p.lot_id
        ";
        $this->desconectar();
        $this->conectar();
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-GeneraLote';
        $nav->nf = 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".delprop").click(function(){
                id_prop =  $(this).attr("delProp");
                alertify.confirm("CANCELACIÓN DE PROPUESTA", "¿Realmente desea cancelar la propuesta?",
                    function(){
                        comunica(
                            "' . $this->pantalla . '",{
                                metodo: "cambiaEstadoPropuesta",
                                accionPropuesta: "cancelar",
                                id_propuesta: id_prop
                            },false,{
                                "mostrar":true,"mensaje":"Procesando la cancelación de la propuesta, espere unos momentos ..."
                        });
                    },
                    function(){},
                ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
            $(".detprop").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"detallePropuesta",
                    det_prop: $(this).attr("detProp")
                });
            });
        ');
    }

    private function buscaSiox($det_prop, $ejercicio, $cere)
    {
        //1.- buscamos todos los docuementos
        switch ($ejercicio) {
            case 2017:
                $condicion2017 = " inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id) ";
                break;
            case 2018:
            default:
                $condicion2017 = "";
                break;
        }
        $sqlExtra = $ejercicio == 2017 ? " AND ant.ecl_id=10 AND ejr_id_clc=2017" : " AND cl.ecl_id=12";
        $parametros = [
            'cere' => $cere,
            'pro_id' => $det_prop,
        ];
        $sql = "SELECT
            doc.bdo_id,
            b.rfc_id,
            (doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable) AS importeBdo,
            referencia,existe,referenciaCompleta
            FROM (
                SELECT
                    dp.gpo_id,
                    dp.ur_id,
                    dp.clc_id,
                    c.bdo_id,
                    c.rfc_id,
                    (c.ncc_percepcion-c.ncc_retencion) AS importeBdo,
                    IF (LENGTH(c.ncc_referencia)=19,SUBSTRING(c.ncc_referencia,1,11),c.ncc_referencia)as referencia,
                    c.ncc_referencia as referenciaCompleta,
                    if (lc.lca_lineaCaptura is null,0,1) as existe #1.-> existe ;0->no existe
                    FROM " . BD_TESORERIA . ".pro_Propuesta
                    INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                    INNER JOIN ctrl" . $ejercicio . ".scp_Clc cl using(gpo_id,ur_id,clc_id)
                    $condicion2017
                    INNER JOIN ctrl" . $ejercicio . ".scp_Contable c USING (gpo_id,ur_id,clc_id)
                    left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio =  IF (LENGTH(c.ncc_referencia)=19,SUBSTRING(c.ncc_referencia,1,11),c.ncc_referencia)
                    WHERE
                        cl.clc_tipoCereCo= :cere AND cl.mpc_id = 2  AND pro_id = :pro_id" . $sqlExtra . "
                    GROUP BY bdo_id
                    UNION ALL
                    SELECT
                    dp.gpo_id,
                    dp.ur_id,
                    dp.clc_id,
                    p.bdo_id,
                    p.rfc_id,
                    ncp_percepcionNeto AS importeBdo,
                    IF (LENGTH(p.ncp_referencia)=19,SUBSTRING(p.ncp_referencia,1,11),p.ncp_referencia)as referencia,
                    p.ncp_referencia as referenciaCompleta,
                    if (lc.lca_lineaCaptura is null,0,1) as existe #1.-> existe ;0->no existe
                    FROM
                    " . BD_TESORERIA . ".pro_Propuesta
                    INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS dp USING (pro_id)
                    INNER JOIN ctrl" . $ejercicio . ".scp_Clc cl using(gpo_id,ur_id,clc_id)
                    $condicion2017
                    inner join ctrl" . $ejercicio . ".scp_Presupuestal p USING (gpo_id,ur_id,clc_id)
                    left join " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(p.ncp_referencia)=19,SUBSTRING(p.ncp_referencia,1,11),p.ncp_referencia)
                    WHERE
                        cl.clc_tipoCereCo= :cere AND cl.mpc_id = 2  AND pro_id = :pro_id" . $sqlExtra . "
                    GROUP BY bdo_id
                ) AS b
            INNER JOIN ctrl" . $ejercicio . ".cat_BancoDocumentos AS doc USING(gpo_id, ur_id, bdo_id)
            GROUP BY gpo_id, ur_id, bdo_id ";
        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql($sql);
        $conexion2->consultar($parametros);
        $cadLineas = "";
        $cadFolio = "";
        $cadVH = "";
        while ($row = $conexion2->extraer()) {
            //2.- ordenamos por tipo de folio

            if ($row['existe'] == 0) {
                if (is_numeric($row['referenciaCompleta'])) {
                    if (strlen($row['referenciaCompleta']) == 19) {
                        $cadLineas .= "'" . $row['referenciaCompleta'] . "',";
                    } else if (strlen($row['referenciaCompleta']) == 11) {
                        $cadFolio .= "'" . $row['referenciaCompleta'] . "',";
                    }
                } else {
                    $lc = substr($row['referenciaCompleta'], 0, 1);
                    if ($lc == 'V') {
                        $cadVH = $cadVH . "'" . $row['referenciaCompleta'] . "',";
                    }
                }
            }
        }
        if (strlen($cadLineas) > 0) {
            $cadLineas = substr($cadLineas, 0, -1);
        }
        if (strlen($cadFolio) > 0) {
            $cadFolio = substr($cadFolio, 0, -1);
        }
        if (strlen($cadVH) > 0) {
            $cadVH = substr($cadVH, 0, -1);
        }
        $respuestaFolios = ws_Consulta_Linea($cadFolio, 1);
        $respuestaLineas = ws_Consulta_Linea($cadLineas, 2);
        $respuestaVH = ws_Consulta_Linea($cadVH, 3);
        //3.- mandamos los arreglos
        //4.- los arreglos recibidos se almacenan en la tabla lineacaptura
        $arrayLc = json_decode($respuestaFolios);
        foreach ($arrayLc as $j => $lc) {
            //temporal quitar cuando haya conexion siox
            $this->lc->lca_folio = $lc->folio;
            $this->lc->lineaCaptura = $lc->lineaCaptura;
            $this->lc->fechaGeneracion = $lc->fechaGeneracion;
            $this->lc->fechaVencimiento = $lc->fechaVencimiento;
            $this->lc->importe = $lc->importe;
            $this->lc->cuentaAbono = $lc->clabe;
            $this->lc->ejr_id = "2018";
            $this->lc->tlc_id = "1";
            $this->lc->elc_id = "1";

            $this->lc->idConvenioBanco = $lc->idConvenioBanco;
            $this->lc->abc = "a";
            LineaCaptura::abc($this->lc);
        }
        $arrayLc = json_decode($respuestaLineas);
        foreach ($arrayLc as $j => $lc) {
            //temporal quitar cuando haya conexion siox
            $this->lc->lca_folio = substr($lc->folio, 0, 11);
            $this->lc->lineaCaptura = $lc->lineaCaptura;
            $this->lc->fechaGeneracion = $lc->fechaGeneracion;
            $this->lc->fechaVencimiento = $lc->fechaVencimiento;
            $this->lc->importe = $lc->importe;
            $this->lc->cuentaAbono = $lc->clabe;
            $this->lc->ejr_id = "2018";
            $this->lc->tlc_id = "1";
            $this->lc->elc_id = "1";

            $this->lc->idConvenioBanco = $lc->idConvenioBanco;
            $this->lc->abc = "a";
            //para el caso lineas de captura se realiza una segunda consulta de existencia
            LineaCaptura::abc($this->lc);
        }
        $arrayLc = json_decode($respuestaVH);
        foreach ($arrayLc as $j => $lc) {
            //temporal quitar cuando haya conexion siox
            $this->lc->lca_folio = $lc->folio;
            $this->lc->lineaCaptura = $lc->lineaCaptura;
            $this->lc->fechaGeneracion = $lc->fechaGeneracion;
            $this->lc->fechaVencimiento = $lc->fechaVencimiento;
            $this->lc->importe = $lc->importe;
            $this->lc->cuentaAbono = $lc->clabe;
            $this->lc->ejr_id = "2018";
            $this->lc->tlc_id = "1";
            $this->lc->elc_id = "1";

            $this->lc->idConvenioBanco = $lc->idConvenioBanco;
            $this->lc->abc = "a";
            LineaCaptura::abc($this->lc);
        }
        //5.- buscamos la linea de captura en el arreglo, si no existe no vincula

    }
    /*
    Descripcion: Mostrar el dialogo para generar un lote cuando
    la forma de pago sea linea de captura y el importe de la clc y linea de captura sean diferentes.
     */
    private function mostrarDialogoParaGenerarLote()
    {
        $idProp = isset($this->prm->idProp) ? $this->prm->idProp : "";

        $this->setContenedor('divContenedorGeneralModal');

        $btnGenerarLote = new stdClass();
        $btnGenerarLote->id = 'btnGenerarLote';
        $btnGenerarLote->name = 'btnGenerarLote';
        $btnGenerarLote->text = 'Generar lote';
        $btnGenerarLote->value = 'Generar lote';
        $btnGenerarLote->type = 'button';
        $btnGenerarLote->class = "btn btn-outline-success pull-right me-1";
        $btnGenerarLote->click = '
            var tabla=$("#generarLoteLineaDeCaptura tbody tr");
            var contadorFila=1;
            var manejarError="";
            var objectEnviar=new Object();

            jQuery.each(tabla,function(index, element) {
                var selectTdf=$(this).find("td").eq(3).find("select");//TDF
                var textReferencia=$(this).find("td").eq(4).find("input");//REFERENCIA
                var textFecha=$(this).find("td").eq(5).find("input");//FECHA
                var textImporte=$(this).find("td").eq(6).find("input");//IMPORTE
                var importeLineaCaptura=$(this).find("td").eq(6).find("input").attr("importelineacaptura");
                var importeClc=$(this).find("td").eq(6).find("input").attr("importeclc");
                var ur=$(this).find("td").eq(6).find("input").attr("ur");
                var clc=$(this).find("td").eq(6).find("input").attr("clc");
                if(selectTdf[0].tagName.toLowerCase()=="select"){
                if(selectTdf[0].value==0){
                    manejarError+="Seleccione un TDF en la fila "+contadorFila+"\n";
                }
            }
            if(textReferencia[0].tagName.toLowerCase()=="input"){
                if(textReferencia[0].value==""){
                    manejarError+="Ingrese la referencia en la fila "+contadorFila+"\n";
                }
            }
            if(textFecha[0].tagName.toLowerCase()=="input"){
                if(textFecha[0].value==""){
                    manejarError+="Ingrese la fecha en la fila "+contadorFila+"\n";
                }
            }
            if(textImporte[0].tagName.toLowerCase()=="input"){
                var diferenciaCapturada=importeLineaCaptura-importeClc;
                var factor = Math.pow(10, 2);
                diferenciaCapturada= Math.round(diferenciaCapturada*factor)/factor;
                if(textImporte[0].value==""){
                    manejarError+="Ingrese el importe en la fila "+contadorFila+"\n";
                }
                else if(textImporte[0].value!=diferenciaCapturada){
                    manejarError+="Ingrese la diferencia correcta que pagará en la UR "+ur+" - CLC "+clc+"\n";
                }
            }
            if(manejarError!=""){
                notify(manejarError,"warning");
                return false;
            } else {
                objectEnviar["fila"+contadorFila]=new Object();
                objectEnviar["fila"+contadorFila]["gpo_id"]=selectTdf.attr("gpo_id");
                objectEnviar["fila"+contadorFila]["clc_id"]=selectTdf.attr("clc_id");
                objectEnviar["fila"+contadorFila]["msc_id"]=selectTdf.attr("msc_id");
                objectEnviar["fila"+contadorFila]["lot_ejercicio"]=selectTdf.attr("lot_ejercicio");
                objectEnviar["fila"+contadorFila]["tdf"]=selectTdf[0].value;
                objectEnviar["fila"+contadorFila]["referencia"]=textReferencia[0].value;
                objectEnviar["fila"+contadorFila]["fecha"]=textFecha[0].value;
                objectEnviar["fila"+contadorFila]["importe"]=textImporte[0].value;
            }
            contadorFila++;
            });

            if(manejarError==""){
                comunica("' . $this->pantalla . '",{
                    metodo: "generarLoteLDCdeImporteDiferentes",
                    clc:JSON.stringify(objectEnviar),
                    idProp:' . $idProp . '
                });
            }
        ';
        $btnCancelar = new stdClass();
        $btnCancelar->id = 'btnCancelar';
        $btnCancelar->name = 'btnCancelar';
        $btnCancelar->text = 'Cancelar';
        $btnCancelar->value = 'Cancelar';
        $btnCancelar->type = 'button';
        $btnCancelar->icon = 'times';
        $btnCancelar->class = "btn btn-outline-secondary pull-right me-1";
        $btnCancelar->click = '
            dialogo.modal("hide");
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'Captura de los complementos de los importes de las Lineas de captura';
        $tabla->id = 'generarLoteLineaDeCaptura';
        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $tabla->encabezado = array($tr);
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tabla->cuerpo = array();

        $td = new stdClass();
        $td->campoTxt = 'ur_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'lca_folio';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'tdf';
        $td->casos->valor = '1';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'combo';
        $td->casos->falso->sql = 'SELECT "SPEI" AS "SPEI","" AS "nombre" UNION SELECT "CH" AS SPEI,"" AS "nombre";';
        $td->casos->falso->selecciona = 'SPEI';
        $td->casos->falso->val = "SPEI";
        $td->casos->falso->text = "SPEI";
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->tdf = "tdf";
        $td->casos->falso->attr->gpo_id = "ur_id";
        $td->casos->falso->attr->lot_ejercicio = "pro_ejercicio";
        $td->casos->falso->attr->msc_id = "msc_id";
        $td->casos->falso->attr->clc_id = "clc_id";

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->campoTxt = 'tdf';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'referencia';
        $td->casos->valor = '1';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'text';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->referencia = "referencia";
        $td->casos->falso->attr->placeholder = "referencia";
        $td->casos->falso->attr->maxlength = 50;

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->campoTxt = 'referencia';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'fecha';
        $td->casos->valor = '1';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'text';
        $td->casos->falso->class = 'fechaLineaDeCaptura';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->placeholder = "fecha";
        $td->casos->falso->attr->maxlength = 10;

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->campoTxt = 'fecha';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'importe';
        $td->casos->valor = '1';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'text';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->placeholder = "importe";
        $td->casos->falso->attr->maxlength = 12;
        $td->casos->falso->attr->importeLineaCaptura = 'lca_importe';
        $td->casos->falso->attr->importeClc = 'pro_importeClc';
        $td->casos->falso->attr->ur = 'ur_id';
        $td->casos->falso->attr->clc = 'clc_id';

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->campoTxt = 'importe';
        array_push($tabla->cuerpo, $td);

        $modalDiferenciaLineaYLote = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog shadow-lg modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Generar Lote</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                    <div class="row col-md-12 col-xs-12 ">
                        <fieldset class="col-md-12 col-xs-12">
                            ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                        </div>
                    </div>
                    <div class="modal-footer">
                   ' . $this->ponBoton($btnGenerarLote) . '
                    <button type="button" class="btn btn-outline-danger" id="cancel" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';
        $this->mixHtml($modalDiferenciaLineaYLote);
        $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            });
            $("#cancel").click(function(){ $("#myModal").modal().remove(); });
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            });
        ');
        $this->llenaTablaGenerarLoteLineaCaptura();
    }
    /*
    Descripcion: Rellenar la tabla el cual la linea de captura respalda a uno o mas clc
     */
    private function llenaTablaGenerarLoteLineaCaptura()
    {
        $idProp = isset($this->prm->idProp) ? $this->prm->idProp : "";
        $cadena = isset($this->prm->cadena) ? $this->prm->cadena : "";

        $pro_ejr = APP_EJERCICIO;
        $pro_ejercicio = null;
        $esElMismoPro_ejercicio = true;
        $storesProEjerciciosDiferentes = array();

        /*
        Ojo: Ordenamos la consulta por pro_ejercicio, tomando el caso que en algun momento
        se quiera generar un lote con mas de dos CLC's y el valor del ejercicio(pro_ejercicio)
        es diferente.
         */
        $parametros = [
            'pro_id' => $idProp,
            'pro_ejr' => $pro_ejr,
        ];
        $sql = 'SELECT p.pro_ejercicio, dp.gpo_id, dp.ur_id, dp.clc_id
            FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
            INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS dp USING (pro_id)
            WHERE p.pro_id = :pro_id AND (dp.gpo_id,dp.ur_id,dp.clc_id) in (' . $cadena . ') AND p.pro_ejr= :pro_ejr
            GROUP BY dp.gpo_id, dp.ur_id, dp.clc_id';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        while ($row = $this->extraer()) {
            $ejercicio = $row['pro_ejercicio'];
            $pro_ejercicio = ($pro_ejercicio == null) ? $ejercicio : $pro_ejercicio;

            if ($pro_ejercicio != $ejercicio) {
                $esElMismoPro_ejercicio = false;
            }
            $newElment = array('pro_ejercicio' => $ejercicio, 'gpo_id' => $row['gpo_id'],
                'ur_id' => $row['ur_id'], 'clc_id' => $row['clc_id']);
            array_push($storesProEjerciciosDiferentes, $newElment);
        }
        $paramTab = [];
        if ($esElMismoPro_ejercicio) {
            $paramTab = [
                'pro_id' => $idProp,
            ];
            $sql = '
                SELECT
                    CONCAT(pc.gpo_id,"",pc.ur_id) AS ur_id, pc.clc_id, 0 AS tdf,1 as SPEI, 0 AS referencia,
                    0 AS fecha, pro.pro_ejercicio, pro.msc_id, 0 AS importe,lc.lca_importe,pc.pro_importeClc,lc.lca_folio
                    FROM ' . BD_TESORERIA . '.pro_Propuesta AS pro
                      INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pc using(pro_id)
                      INNER JOIN ( select ncp_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl' . $pro_ejercicio . '.scp_Presupuestal  where clc_id>0
                      GROUP BY ncp_referencia,bdo_id,gpo_id,ur_id, clc_id )AS pre using (gpo_id,ur_id,clc_id)
                      left JOIN ' . BD_TESORERIA . '.lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(pre.ncp_referencia)=19,SUBSTRING(pre.ncp_referencia,1,11),pre.ncp_referencia)
                    where pro.pro_id = :pro_id  AND (pc.gpo_id,pc.ur_id,pc.clc_id) in (' . $cadena . ') AND IF(lc.elc_id is null,"1=1", lc.elc_id!=5)
                    group by  pre.ncp_referencia,pre.bdo_id,lc.lca_lineaCaptura,pc.gpo_id,pc.ur_id,pc.clc_id
                UNION
                SELECT CONCAT(pc.gpo_id,"",pc.ur_id) AS ur_id, pc.clc_id, 0 AS tdf,1 as SPEI, 0 AS referencia,
                  0 AS fecha, pro.pro_ejercicio, pro.msc_id, 0 AS importe,lc.lca_importe,pc.pro_importeClc,lc.lca_folio
                    FROM  ' . BD_TESORERIA . '.pro_Propuesta AS pro
                      INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS pc using(pro_id)
                      INNER JOIN ( select ncc_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl' . $pro_ejercicio . '.scp_Contable  where clc_id>0
                      GROUP BY ncc_referencia,bdo_id,gpo_id,ur_id,clc_id ) AS c using (gpo_id,ur_id,clc_id)
                      left JOIN ' . BD_TESORERIA . '.lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(c.ncc_referencia)=19,SUBSTRING(c.ncc_referencia,1,11),c.ncc_referencia)
                    WHERE pro.pro_id = :pro_id AND (pc.gpo_id,pc.ur_id,pc.clc_id) in (' . $cadena . ') AND IF(lc.elc_id is null,"1=1", lc.elc_id!=5)
                    group by  c.ncc_referencia,c.bdo_id,lc.lca_lineaCaptura,gpo_id,ur_id,clc_id
            ';
        } else {
            $paramTab = [
                'pro_id' => $idProp,
                'pro_ejr' => $pro_ejr,
            ];
            foreach ($storesProEjerciciosDiferentes as $value) {
                $sql .= '
                    SELECT CONCAT(dp.gpo_id,"",dp.ur_id) AS ur_id, dp.clc_id, 0 AS tdf,1 as SPEI, 0 AS referencia,
                      0 AS fecha, p.pro_ejercicio, p.msc_id, 0 AS importe,lc.lca_importe,dp.pro_importeClc
                    FROM ' . BD_TESORERIA . '.pro_Propuesta AS p
                    INNER JOIN ' . BD_TESORERIA . '.pro_PropuestaClc AS dp USING (pro_id)
                    INNER JOIN (
                      SELECT SUM(lc.lca_importe) lca_importe,pre.gpo_id, pre.ur_id, pre.clc_id FROM ctrl' . $value['pro_ejercicio'] . '.scp_Presupuestal pre
                      LEFT JOIN ' . BD_TESORERIA . '.lca_LineaCaptura lc on lc.lca_folio = pre.ncp_referencia
                          GROUP BY pre.gpo_id, pre.ur_id, pre.clc_id,lc.lca_folio
                        ) lc ON lc.gpo_id=dp.gpo_id AND lc.ur_id=dp.ur_id AND lc.clc_id=dp.clc_id

                        WHERE p.pro_id = :pro_id AND (dp.gpo_id,dp.ur_id,dp.clc_id) in ((' . $value['gpo_id'] . ',
                        ' . $value['ur_id'] . ',' . $value['clc_id'] . ')) AND p.pro_ejr= :pro_ejr
                        UNION
                ';
            }
            $sql .= 'GROUP BY dp.gpo_id, dp.ur_id, dp.clc_id,lc.lca_importe,lc.lca_folio';
        }

        /* Hacemos la consulta y llenamos la tabla con el resultado /*/
        $this->setSql($sql);
        $this->setParams($paramTab);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'generarLoteLineaDeCaptura';
        $nav->nf = -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('$("#generarLoteLineaDeCaptura .tabla-footer").remove();
            $(".fechaLineaDeCaptura").datepicker({
                format: "dd/mm/yyyy"
            });
        ');
    }
    /*
    Descripcion: generar el lote con forma de pago linea de captura cuando los importes de la CLC's y la linea de
    captura difieren.
     */
    private function generarLoteLDCdeImporteDiferentes()
    {

        $idProp = isset($this->prm->idProp) ? $this->prm->idProp : "";
        $clc = isset($this->prm->clc) ? $this->prm->clc : "";
        $clc = json_decode($clc);
        $mesActual = date("m");
        $folio = "";
        $respuesta = '';
        $fila = 1;
        $seGeneroTodo = true;

        $conexion1 = new AccesoDatos();
        $conexion1->conectar();
        $tem_cargaSegeneraUnaVez = 1;

        foreach ($clc as $key => $value) {
            $ejercicio = $value->lot_ejercicio;
            $parametros = [
                'pro_id' => $idProp,
            ];
            $conexion1->setSql("
                SELECT
                    lc.lca_folio,lc.lca_lineaCaptura,lc.lca_id,
                        IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) as existe, #1.-> existe ;0->no existe
                        IF (lc.lca_lineaCaptura is null,'NO SE ENCONTRO', IF (lc.lca_fechaVencimiento < DATE(NOW()),'VENCIDA','VIGENTE')) as estadoLca,lc.lca_importe, pc.pro_importeClc
                FROM " . BD_TESORERIA . ".pro_Propuesta AS pro
                INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                INNER JOIN ( select ncp_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl$ejercicio.scp_Presupuestal  where clc_id>0
                GROUP BY ncp_referencia,bdo_id,gpo_id,ur_id, clc_id )AS pre using (gpo_id,ur_id,clc_id)
                    left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(pre.ncp_referencia)=19,SUBSTRING(pre.ncp_referencia,1,11),pre.ncp_referencia)
                    where pro.pro_id = :pro_id  AND (pc.gpo_id,pc.ur_id,pc.clc_id) in ((" . substr($value->gpo_id, 0, 1) . ",
                    " . substr($value->gpo_id, 1, 2) . "," . $value->clc_id . ")) AND IF(lc.elc_id is null,'1=1', lc.elc_id!=5)
                group by  pre.ncp_referencia,pre.bdo_id,lc.lca_lineaCaptura,pc.gpo_id,pc.ur_id,pc.clc_id
                UNION
                SELECT
                    lc.lca_folio,lc.lca_lineaCaptura,lc.lca_id,
                        IF (lc.lca_lineaCaptura is null,1, IF (lc.lca_fechaVencimiento < DATE(NOW()),2,0)) as existe, #1.-> existe ;0->no existe
                        IF (lc.lca_lineaCaptura is null,'NO SE ENCONTRO', IF (lc.lca_fechaVencimiento < DATE(NOW()),'VENCIDA','VIGENTE')) as estadoLca,lc.lca_importe, pc.pro_importeClc
                    FROM  " . BD_TESORERIA . ".pro_Propuesta AS pro
                    INNER JOIN " . BD_TESORERIA . ".pro_PropuestaClc AS pc using(pro_id)
                    INNER JOIN ( select ncc_referencia,bdo_id,gpo_id,ur_id,clc_id from ctrl$ejercicio.scp_Contable  where clc_id>0 GROUP BY ncc_referencia,bdo_id,gpo_id,ur_id,clc_id ) AS c using (gpo_id,ur_id,clc_id)
                    left JOIN " . BD_TESORERIA . ".lca_LineaCaptura lc on lc.lca_folio = IF (LENGTH(c.ncc_referencia)=19,SUBSTRING(c.ncc_referencia,1,11),c.ncc_referencia)
                    WHERE pro.pro_id = :pro_id AND (pc.gpo_id,pc.ur_id,pc.clc_id) in ((" . substr($value->gpo_id, 0, 1) . "," . substr($value->gpo_id, 1, 2) . "," . $value->clc_id . ")) AND IF(lc.elc_id is null,'1=1', lc.elc_id!=5)
                    group by  c.ncc_referencia,c.bdo_id,lc.lca_lineaCaptura,gpo_id,ur_id,clc_id
            ");

            $totalImporteLineas = 0;
            $totalImporteClcs = 0;

            $conexion1->consultar($parametros);
            $estadoLcaVigente = true;
            while ($row = $conexion1->extraer()) {
                if ($row['estadoLca'] == 'VENCIDA') {
                    $this->mixScript('
                        notify("No puede generar el lote porque esta vencida la linea de captura","warning");
                    ');
                    $estadoLcaVigente = false;
                    break;
                }
                $totalImporteLineas += $row['lca_importe'];
                $totalImporteClcs += $row['pro_importeClc'];
                $folio = $row['lca_folio'];
            }
            if ($estadoLcaVigente == false) {
                return 0;
            }
            $diferenciaCapturada = round(($totalImporteLineas - $totalImporteClcs), 2);
            if ($diferenciaCapturada != $value->importe) {
                $this->mixScript('notify("Ingrese la diferencia correcta que pagará en la UR ' . $value->gpo_id . ' - CLC ' . $value->clc_id . '","warning"); ');
                $seGeneroTodo = false;
                break;
            }
            $conexion1->consultar($parametros);
            $row = $conexion1->extraer();
            $conexion2 = new AccesoDatos();
            $conexion2->conectar();
            $paramCon2 =[
                'ejr' => APP_EJERCICIO,
                'lca_ejercicio' => $value->lot_ejercicio,
                'msc_id' => $value->msc_id,
                'gpo_id' => (substr($value->gpo_id, 0, 1)),
                'ur_id' => substr($value->gpo_id, 1, 2),
                'clc_id' => $value->clc_id,
            ];
            $conexion2->setSql('
                SELECT tdf_id FROM ' . BD_TESORERIA . '.lca_Clc
                WHERE lca_ejr= :ejr AND lca_ejercicio= :lca_ejercicio AND msc_id= :msc_id
                  AND gpo_id= :gpo_id AND ur_id= :ur_id
                  AND clc_id= :clc_id;
            ');
            $conexion2->consultar($paramCon2);
            if ($conexion2->getNumeroRegistros() == 0) {
                $this->prm->lca_clc = new stdClass();
                $this->prm->lca_clc->ejr = APP_EJERCICIO;
                $this->prm->lca_clc->gpo_id = substr($value->gpo_id, 0, 1);
                $this->prm->lca_clc->ur_id = substr($value->gpo_id, 1, 2);
                $this->prm->lca_clc->clc_id = $value->clc_id;
                $this->prm->lca_clc->folio = $folio;
                $this->prm->lca_clc->ejercicio = $value->lot_ejercicio;
                $this->prm->lca_clc->msc_id = $value->msc_id;
                $this->prm->lca_clc->tdf_id = $value->tdf;
                $this->prm->lca_clc->referencia = $value->referencia;
                $this->prm->lca_clc->fecha = substr($value->fecha, -4) . "-" . substr($value->fecha, 3, 2) . "-" . substr($value->fecha, 0, 2);
                $this->prm->lca_clc->importeClc = $value->importe;
                $this->prm->lca_clc->sts_id = 1; //Estado de la linea de captura
                $this->prm->lca_clc->procesado = 2;
                $this->prm->lca_clc->abc = "a";

                LcaClc::abc($this->prm->lca_clc);
            }

            if ($tem_cargaSegeneraUnaVez == 1) {
                $conexion2->setSql('SET @fechahora = NOW();');
                $conexion2->consultar();
                $conexion2->setSql('SET @fechacarga = DATE(@fechahora);');
                $conexion2->consultar();
                $conexion2->setSql('SET @fecha = CONCAT(SUBSTRING(DATE(@fechahora), -8, 2), SUBSTRING(DATE(@fechahora), -5, 2), SUBSTRING(DATE(@fechahora), -2, 2));');
                $conexion2->consultar();
                $conexion2->setSql('SET @hora = CONCAT(SUBSTRING(TIME(@fechahora), -2, 2), SUBSTRING(TIME(@fechahora), -5, 2), SUBSTRING(TIME(@fechahora), -8, 2));');
                $conexion2->consultar();
                $conexion2->setSql('SET @tem_carga = CONCAT("116-", @fecha, "-253-", @hora);');
                $conexion2->consultar();
                $conexion2->setSql('SELECT @tem_carga;');
                $conexion2->consultar();

                $rowTemCarga = $conexion2->extraer();
            }

            $datosSP = new stdClass();
            $datosSP->pro_id = $idProp;
            $datosSP->lot_ejercicio = $value->lot_ejercicio;
            $datosSP->tem_carga = $rowTemCarga['@tem_carga'];
            $datosSP->bit_observacion = 'Insertado desde el módulo de lotes';
            $datosSP->sis_id = "TES";

            $this->conectar();
            $paramConex =[
                'ejr' => APP_EJERCICIO,
                'msc_id' => $mesActual,
                'lot_ejercicio' => $value->lot_ejercicio,
                'pro_id' => $idProp ,
                'gpo_id' => (substr($value->gpo_id, 0, 1)),
                'ur_id' => (substr($value->gpo_id, 1, 2)),
                'clc_id' => $value->clc_id,
            ];
            $sql = 'SELECT tem_carga,fpa_id FROM ' . BD_TESORERITEM . '.tem_LoteClcLinea
                WHERE tem_ejr= :ejr AND fpa_id="3" AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND pro_id= :pro_id AND gpo_id= :gpo_id
                  AND ur_id= :ur_id AND clc_id= :clc_id';
            $this->setSql($sql);
            $this->consultar($paramConex);
            if ($this->getNumeroRegistros() == 0) {
                $paramsInsert =[
                    'ejr' => APP_EJERCICIO,
                    'mesActual' => $mesActual,
                    'lot_ejercicio' => $value->lot_ejercicio,
                    'idProp' => $idProp,
                    'gpo_id' => (substr($value->gpo_id, 0, 1)),
                    'ur_id' => (substr($value->gpo_id, 1, 2)),
                    'clc_id' => $value->clc_id,
                    'temCarga' => ($rowTemCarga["@tem_carga"]),
                    'usr_id' => $this->usr_id,
                ];
                $sql = 'INSERT INTO ' . BD_TESORERITEM . '.tem_LoteClcLinea (tem_ejr,fpa_id, msc_id,lot_ejercicio, pro_id, gpo_id, ur_id, clc_id, tem_carga, usr_id)
                VALUES ( :ejr ,"3", :mesActual, :lot_ejercicio, :idProp, :gpo_id, :ur_id, :clc_id, :temCarga, :usr_id);';
                $this->setSql($sql);
                $this->consultar($paramsInsert);
            }

            $tem_cargaSegeneraUnaVez++;
            $fila++;
        }
        if ($seGeneroTodo) {
            $paramSet =[
                'usr_id' => $this->usr_id,
                'ip' => $this->ip,
            ];
            $sql = "set @usr_id= :usr_id , @usr_ip=INET_ATON(:ip) , @bit_observacion='Insertado desde el módulo de lotes'";
            $this->setSql($sql);
            $this->consultar($paramSet);
            $respuesta2 = sp_GenerarLoteLineaCaptura2026::call($datosSP);
            $respuesta = $respuesta2->mensaje;
            $conexion2->desconectar();
            $this->mixScript("notify('" . $respuesta . "','info');");
            if ($respuesta2->bandera) {
                $this->mixScript('$("#myModal").modal().remove();');
            } else {
                $conexion2 = new AccesoDatos();
                $conexion2->conectar();
                $this->conectar();
                foreach ($clc as $key => $value) {
                    $params2 =[
                        'ejr' => APP_EJERCICIO,
                        'lca_ejercicio' => $value->lot_ejercicio,
                        'msc_id' => $value->msc_id,
                        'gpo_id' => (substr($value->gpo_id, 0, 1)),
                        'ur_id' => substr($value->gpo_id, 1, 2),
                        'clc_id' => $value->clc_id,
                    ];
                    $conexion2->setSql('
                        SELECT tdf_id,lca_folio FROM ' . BD_TESORERIA . '.lca_Clc
                        WHERE lca_ejr= :ejr AND lca_ejercicio= :lca_ejercicio AND msc_id= :msc_id
                          AND gpo_id= :gpo_id AND ur_id= :ur_id
                          AND clc_id= :clc_id;
                    ');
                    $conexion2->consultar($params2);
                    $fila = $conexion2->extraer();
                    if ($conexion2->getNumeroRegistros() > 0) {
                        $this->prm->lca_clc = new stdClass();
                        $this->prm->lca_clc->ejr = APP_EJERCICIO;
                        $this->prm->lca_clc->gpo_id = substr($value->gpo_id, 0, 1);
                        $this->prm->lca_clc->ur_id = substr($value->gpo_id, 1, 2);
                        $this->prm->lca_clc->clc_id = $value->clc_id;
                        $this->prm->lca_clc->folio = $fila['lca_folio'];
                        $this->prm->lca_clc->ejercicio = $value->lot_ejercicio;
                        $this->prm->lca_clc->msc_id = $value->msc_id;
                        $this->prm->lca_clc->tdf_id = $value->tdf;
                        $this->prm->lca_clc->referencia = $value->referencia;
                        $this->prm->lca_clc->fecha = substr($value->fecha, -4) . "-" . substr($value->fecha, 3, 2) . "-" . substr($value->fecha, 0, 2);
                        $this->prm->lca_clc->importeClc = $value->importe;
                        $this->prm->lca_clc->abc = "b";
                        LcaClc::abc($this->prm->lca_clc);
                    }
                    if (isset($rowTemCarga)) {
                        $paramsCargaSet = [
                            'ip' => $this->ip,
                            'usr_id' => $this->usr_id,
                        ];
                        $bit_observacion = "DESDE EL MÓDULO DE LOTES";
                        $bit_observacion = 'REGISTRO ELIMINADO ' . $bit_observacion;
                        $this->setSql(' SET @usr_ip =INET_ATON(:ip), @usr_id= :usr_id , @bit_observacion ="' . $bit_observacion . '";');
                        $this->consultar($paramsCargaSet);
                        $paramsDelete =[
                            'tem_carga' => $rowTemCarga["@tem_carga"],
                        ];
                        $sql = 'DELETE FROM ' . BD_TESORERITEM . '.tem_LoteClcLinea WHERE tem_carga= :tem_carga';
                        $this->setSql($sql);
                        $this->consultar($paramsDelete);
                    }
                }
                $conexion2->desconectar();
            }
            $conexion1->desconectar();
            $this->mixScript('comunica("' . $this->pantalla . '");
            ');
        } else {
            $conexion2 = new AccesoDatos();
            $conexion2->conectar();
            $this->conectar();
            foreach ($clc as $key => $value) {
                $paramConex2 =[
                    'ejr' => APP_EJERCICIO,
                    'lca_ejercicio' => $value->lot_ejercicio,
                    'msc_id' => $value->msc_id,
                    'gpo_id' => (substr($value->gpo_id, 0, 1)),
                    'ur_id' => substr($value->gpo_id, 1, 2),
                    'clc_id' => $value->clc_id,
                ];
                $conexion2->setSql('
                    SELECT tdf_id,lca_folio FROM ' . BD_TESORERIA . '.lca_Clc
                    WHERE lca_ejr= :ejr AND lca_ejercicio= :lca_ejercicio AND msc_id= :msc_id
                      AND gpo_id= :gpo_id AND ur_id= :ur_id
                      AND clc_id= :clc_id;
                ');
                $conexion2->consultar($paramConex2);
                $fila = $conexion2->extraer();
                if ($conexion2->getNumeroRegistros() > 0) {
                    $this->prm->lca_clc = new stdClass();
                    $this->prm->lca_clc->ejr = APP_EJERCICIO;
                    $this->prm->lca_clc->gpo_id = substr($value->gpo_id, 0, 1);
                    $this->prm->lca_clc->ur_id = substr($value->gpo_id, 1, 2);
                    $this->prm->lca_clc->clc_id = $value->clc_id;
                    $this->prm->lca_clc->folio = $fila['lca_folio'];
                    $this->prm->lca_clc->ejercicio = $value->lot_ejercicio;
                    $this->prm->lca_clc->msc_id = $value->msc_id;
                    $this->prm->lca_clc->tdf_id = $value->tdf;
                    $this->prm->lca_clc->referencia = $value->referencia;
                    $this->prm->lca_clc->fecha = substr($value->fecha, -4) . "-" . substr($value->fecha, 3, 2) . "-" . substr($value->fecha, 0, 2);
                    $this->prm->lca_clc->importeClc = $value->importe;
                    $this->prm->lca_clc->abc = "b";
                    LcaClc::abc($this->prm->lca_clc);
                }
                if (isset($rowTemCarga)) {
                    $paramTeamSet = [
                        'ip' => $this->ip,
                        'usr_id' => $this->usr_id,
                    ];
                    $bit_observacion = "DESDE EL MÓDULO DE LOTES";
                    $bit_observacion = 'REGISTRO ELIMINADO ' . $bit_observacion;
                    $this->setSql(' SET @usr_ip =INET_ATON(:ip), @usr_id= :usr_id , @bit_observacion ="' . $bit_observacion . '";');
                    $this->consultar($paramTeamSet);
                    $paramDelete =[
                        'tem_carga' => $rowTemCarga["@tem_carga"],
                    ];
                    $sql = 'DELETE FROM ' . BD_TESORERITEM . '.tem_LoteClcLinea WHERE tem_carga= :tem_carga';
                    $this->setSql($sql);
                    $this->consultar($paramDelete);
                }
            }
            $conexion2->desconectar();
        }
    }
}

if (isset($_SESSION[_S]['prm']['T']['4']['1']['1'])) {
    new ControladorMinTesoreria();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
