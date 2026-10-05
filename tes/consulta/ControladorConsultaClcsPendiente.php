<?php

/**
 * Clase ControladorConsultaTesoreria
 *
 * Permite consultar y mostrar las clc's pendientes de pago 2016 y 2017
 * de acuerdo al ejercicio seleccionado
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   Package
 * @author    Jose Eduardo Sanchez Mendez <jose.sanchez@finanzasoaxaca.gob.mx>
 * @copyright 2017 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 * @license   http://www.fsf.org/licensing/licenses/gpl.txt GPL 2 or later
 * @version   SVN:2017-02-10
 * @link      egresos.finanzasoaxaca.gob.mx/sinpres2017/
 */

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
class ControladorConsultaTesoreria extends WebResponse
{
    /**
     *Constructor
     *
     *@return JSON
     */
    public $lectura;
    public $exportacion;
    public $gpo;
    public $ur;
    public $ue;
    public function __construct()
    {
        //Mandar a llamar Constructor Base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['T']['1']['4']['1']) ? 1 : 0;
        $this->exportacion = isset($_SESSION[_S]['prm']['T']['1']['4']['47']) ? 1 : 0;
        $this->gpo = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue = false;
        }
        switch ($this->metodo) {
            case 'llenaTablaConsultaClc':
                $this->llenaTablaConsultaClc();
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
     *@return datos
     */
    public function main()
    {
        $btn_expClc = "";
        if ($this->exportacion == true) {
            $btn_expClc = '<button id="btn_expClc" type="button" ejerc="2016"
            form="frm_ConsultaClc" class="btn btn-outline-success me-1"
            title="EXPORTAR A EXCEL LAS CLCS 2016 PENDIENTES DE PAGO">
            <i class="fa-solid fa-file-excel me-2"></i>
            Exportar</button>';
        }
        $tablaConsultaClc = new stdClass();
        $tablaConsultaClc->id = 'tablaConsultaClc';
        $tablaConsultaClc->titulo = "CLC'S SIN PAGAR <span id='xxx'>2016</span>";
        $tablaConsultaClc->comunica = new stdClass();
        $tablaConsultaClc->comunica->pantalla = $this->pantalla;
        $tablaConsultaClc->comunica->metodo = 'llenaTablaConsultaClc';
        $tablaConsultaClc->comunica->ejercicio = '{$("#xxx").html()}';
        $tablaConsultaClc->filasAmostrar = array(5, 10, 20, 50, 100, 120, 200, 250);
        $tablaConsultaClc->numFilas = 50;
        //Encabezados
        $tr = array();
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'UE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->aling = 'left';
        $th->width = '5%';
        $th->texto = 'CLC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '25%';
        $th->texto = 'EDO_CLC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '60%';
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);
        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'UR';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'UE';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'CLC';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'EDO_CLC';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'CONCEPTO';
        array_push($trbody, $td);
        $tablaConsultaClc->encabezado = array($tr);
        $tablaConsultaClc->cuerpo = ($trbody);
        //Se agregan los elementos que usaremos en la barra de botones
        $btn_regresarIndex = '<button id="btn_regresarIndex" type="button"
        class="btn btn-outline-secondary me-1" title="Regresar al listado de Catálogos">
        <i class="fa-solid fa-circle-left me-2"></i>
        Regresar</button>';
        $btn_2016 = '<button id="btn_2016" type="button" form="frm_ConsultaClc"
        class="btn btn-outline-secondary me-1 px-3" title="CLCS 2016 PENDIENTES DE PAGO">
        2016</button>';
        $btn_2015 = '<button id="btn_2015" type="button" form="frm_ConsultaClc"
        class="btn btn-outline-secondary me-1 px-3" title="CLCS 2015 PENDIENTES DE PAGO">
        2015</button>';
        $btn_2014 = '<button id="btn_2014" type="button" form="frm_ConsultaClc"
        class="btn btn-outline-secondary me-1 px-3" title="CLCS 2014 PENDIENTES DE PAGO">
        2014</button>';
        $btn_2013 = '<button id="btn_2013" type="button" form="frm_ConsultaClc"
        class="btn btn-outline-secondary me-1 px-3" title="CLCS 2013 PENDIENTES DE PAGO">
        2013</button>';
        $btn_2012 = '<button id="btn_2012" type="button" form="frm_ConsultaClc"
        class="btn btn-outline-secondary me-1" title="CLCS 2012 PENDIENTES DE PAGO">
        2012</button>';
        $btn_2011 = '<button id="btn_2011" type="button" form="frm_ConsultaClc"
        class="btn btn-outline-secondary me-1 px-3" title="CLCS 2011 PENDIENTES DE PAGO">
        2011</button>';
        // Se pintan los elementos en patalla
        // en la zona destinada a la barra de botones
        $this->agregaTools( $btn_regresarIndex . $btn_expClc . $btn_2016 .
        $btn_2015 . $btn_2014 . $btn_2013 . $btn_2012 . $btn_2011 );
        $this->mixHtml('<div class="col-md-12 col-xs-12">'
            . $this->wrPonTabla($tablaConsultaClc) . '</div>');
        $this->llenaTablaConsultaClc();
        $this->mixScript(
            '$("#btn_expClc").click(function()
            {
                var form = $("<form>")
                    .attr("action", "rep/excel/tes/consulta/exp_PendientesDePago.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type","hidden").attr("name","anio_id")
                    .attr("value",$("#btn_expClc").attr("ejerc")))
                $("body").append(form);
                form.submit();
            });
            $("#btn_regresarIndex").click (function()
            {
                $("nav").menusinpres("clickMenu");
                return false;
            });
            $("#btn_2016").click(function()
            {
                document.getElementById("btn_expClc")
                .title = "EXPORTAR A EXCEL LAS CLCS 2016 PENDIENTES DE PAGO ";
                $("#btn_expClc").attr("ejerc","2016");
                $("#xxx").html("2016");
                ' . $this->wrLlamaLlenaTabla('tablaConsultaClc') . '
            });
            $("#btn_2015").click(function()
            {
                document.getElementById("btn_expClc")
                .title = "EXPORTAR A EXCEL LAS CLCS 2015 PENDIENTES DE PAGO ";
                $("#btn_expClc").attr("ejerc","2015");
                $("#xxx").html("2015");
                ' . $this->wrLlamaLlenaTabla('tablaConsultaClc') . '
            });
            
            $("#btn_2014").click(function()
             {
                document.getElementById("btn_expClc")
                .title = "EXPORTAR A EXCEL LAS CLCS 2014 PENDIENTES DE PAGO ";
                $("#xxx").html("2014");
                $("#btn_expClc").attr("ejerc","2014");
                ' . $this->wrLlamaLlenaTabla('tablaConsultaClc') . '
            });
             $("#btn_2013").click(function()
             {
                document.getElementById("btn_expClc")
                .title = "EXPORTAR A EXCEL LAS CLCS 2013 PENDIENTES DE PAGO ";
                $("#xxx").html("2013");
                $("#btn_expClc").attr("ejerc","2013");
                ' . $this->wrLlamaLlenaTabla('tablaConsultaClc') . '
            });
            $("#btn_2012").click(function()
             {
                document.getElementById("btn_expClc")
                .title = "EXPORTAR A EXCEL LAS CLCS 2012 PENDIENTES DE PAGO ";
                $("#xxx").html("2012");
                $("#btn_expClc").attr("ejerc","2012");
                ' . $this->wrLlamaLlenaTabla('tablaConsultaClc') . '
            });
            $("#btn_2011").click(function()
            {
                document.getElementById("btn_expClc")
                .title = "EXPORTAR A EXCEL LAS CLCS 2011 PENDIENTES DE PAGO ";
                $("#xxx").html("2011");
                $("#btn_expClc").attr("ejerc","2011");
                ' . $this->wrLlamaLlenaTabla('tablaConsultaClc') . '
            });
            '
        );
    }
    /**
     *llenaTablaConsultaClc funcion que realiza la consulta
     *dependiendo del ejercicioseleccionado
     *ejecutando por default el ejercicio 2016.
     *
     *@return datos
     */
    private function llenaTablaConsultaClc()
    {
        $paginacion = (isset($this->prm->ejercicio)) ? $this->prm->ejercicio : 2016;
        $sql = "";
        if ($paginacion == 2016) {
            $sql = "SELECT
                concat(A.gpo_id, A.ur_id) AS UR,A.ue_id AS UE,
                A.clc_id AS CLC,
                G.ecl_nombre AS EDO_CLC,
                A.clc_concepto AS CONCEPTO
                FROM
                    ctrl2016.scp_Clc A
                    LEFT JOIN
                    ctrl2016.cat_UnidadResponsable B USING (gpo_id , ur_id)
                    LEFT JOIN
                    ctrl2016.cat_BancosUr X USING (bur_id)
                    LEFT JOIN
                     cat_UsoCuentaBancaria USING(ucb_id)
                    LEFT JOIN
                    ctrl2016.enc_ClaveFinanciamiento C USING (ecf_id)
                    LEFT JOIN
                    ctrl2016.enc_BancosSefinCveFin D USING (ecf_id)
                    LEFT JOIN
                    ctrl2016.cat_TipoClc E USING (tcl_id)
                    LEFT JOIN
                    ctrl2016.cat_SubtipoClc F USING (tcl_id , stc_id)
                    INNER JOIN
                    ctrl2016.cat_EstadoClc G USING (ecl_id)
                where ecl_id in (10,11,12) and clc_RecursoFederal=2
                ORDER BY UR,CLC;";
        }
        if ($paginacion == 2015) {
            $sql = "SELECT
            CONCAT(A.gpo_id, A.ur_id) AS UR,
            A.ue_id AS UE,
            A.clc_id AS CLC,
            G.ecl_nombre AS EDO_CLC,
            A.clc_concepto AS CONCEPTO
            FROM
            ctrl2015.scp_Clc A
            LEFT JOIN
            ctrl2015.cat_UnidadResponsable B USING (gpo_id , ur_id)
            LEFT JOIN
            ctrl2015.cat_BancosUr X USING (bur_id)
            LEFT JOIN
            cat_UsoCuentaBancaria USING(ucb_id)
            LEFT JOIN
            ctrl2015.enc_ClaveFinanciamiento C USING (ecf_id)
            LEFT JOIN
            ctrl2015.enc_BancosSefinCveFin D USING (ecf_id)
            LEFT JOIN
            ctrl2015.cat_TipoClc E USING (tcl_id)
            LEFT JOIN
            ctrl2015.cat_SubtipoClc F USING (tcl_id , stc_id)
            INNER JOIN
            ctrl2015.cat_EstadoClc G USING (ecl_id)
            WHERE ecl_id IN (10,11,12) #and clc_RecursoFederal=2
            AND CONCAT( A.gpo_id, '.', A.ur_id, '.', A.clc_id ) NOT IN
            (
            # determinamos las que SI son de recurso federal
            # sacado del reporte de CLC (rep_Clc) del sinpres2015
            SELECT CONCAT( gpo_id, '.', ur_id, '.', clc_id ) FROM scp_Clc
            WHERE
            ecf_id IN (
            SELECT ecf_id FROM
            ctrl2015.enc_ClaveFinanciamiento
            INNER JOIN ctrl2015.cat_ClasificacionSuborigen USING( fte_id, sft_id, ori_id, sor_id,cso_id)
            WHERE ter_id='B' AND sts_id=1
            AND ecf_id NOT IN(
                000272, 002292, 002067, 000289, 001575,
                000297, 002122, 000308, 002121, 000324,
                000325, 000330, 001351, 000332, 002290,
                001369, 000893, 000894, 000897, 000898,
                001822, 001823, 000904, 002181, 002182,
                002183, 002184, 002114, 001824, 000918,
                001825, 001826, 001827, 001828, 000939,
                001829, 000944, 001830, 001831, 000955,
                001832, 001833, 001835, 001350, 001836,
                002125, 001949, 001991, 001883, 001884,
                001433, 001838, 001839, 000998, 001840,
                001536, 002240, 001581, 002238, 002236,
                002237, 002241, 001498, 001535, 001383,
                001394, 001497, 001404, 001552, 001584,
                001878, 002239, 001876, 002258, 001966,
                002247, 002243, 001855, 002007, 001872,
                001867, 001877, 001882, 002244,002285,
                002299,000950,000973,001525,001563,001885,002288,
                000961,002312,002310,002313,002246,001857,
                001857,002321,002322,002309,002270,
                001856,002284,002287,002343,002357,002385,002389)
            )
            AND CONCAT( gpo_id, ur_id ) NOT IN ( '315', '801', '802' )
            AND NOT (
                CONCAT(gpo_id, ur_id) IN ('202','302','303','304','306','307','308','309','310','324','333','334','471') AND
                tcl_id = 'A' AND stc_id = '02')
            AND ecl_id = 16
            AND NOT (
                    ( CONCAT( gpo_id, ur_id ) = '325' AND tcl_id = 'A' AND ecf_id = 001739 ) OR
                    ( CONCAT( gpo_id, ur_id ) = '318' AND tcl_id = 'C' AND ecf_id = 001739 )
                )
            )
            ORDER BY UR,CLC;
        ";
        }
        if ($paginacion == 2014) {
            $sql = "SELECT
            concat(c.gpo_id,c.ur_id)as UR,
            c.ue_id as UE,
            c.clc_id as CLC,
            E.ecl_nombre AS EDO_CLC,
            c.clc_concepto as CONCEPTO
            FROM ctrl2014.scp_Clc as c
            INNER JOIN ctrl2014.cat_UnidadResponsable USING (gpo_id,ur_id)
            INNER JOIN ctrl2014.cat_EstadoClc as E using (ecl_id)
            INNER JOIN ctrl2014.cat_TipoClc AS T USING (tcl_id)
            INNER JOIN ctrl2014.cat_SubtipoClc as S using (stc_id)
            INNER JOIN ctrl2014.enc_ClaveFinanciamiento as F using(ecf_id)
            INNER JOIN ctrl2014.enc_BancosSefinCveFin as B using(ecf_id)
            WHERE ecl_id in (10,11,12)  and c.loi_id IS NOT NULL AND c.ing_referenciaPago =''
            GROUP BY c.gpo_id,c.ur_id,c.clc_id
            ORDER BY B.bsf_id;";
        }
        if ($paginacion == 2013) {
            $sql = "SELECT
            concat(A.clc_gpo_id,A.clc_ur_id)as UR,
            A.clc_ue_id as UE,
            A.clc_id as CLC,
            C.ecl_nombre as EDO_CLC,
            A.clc_concepto AS CONCEPTO,
            IF(A.otb_id!='',X.ing_lote,Y.ing_lote)as LOTE_PAGO,
            if(A.otb_id !='',X.ing_referenciaPago,Y.ing_referenciaPago)as SPEI
            FROM ctrl2013.scp_Cedula A
            INNER JOIN ctrl2013.cat_EstadoClc as C using (ecl_id)
            INNER JOIN ctrl2013.cat_TipoCedula as D using (tclc_id)
            INNER JOIN ctrl2013.cat_BancosUr as E using(banur_id)
            INNER JOIN ctrl2013.cat_UsoCuentaBancaria  F using(ucb_id)
            LEFT JOIN ctrl2013.cat_UnidadResponsable as B on B.gpo_id=A.clc_gpo_id
            AND B.ur_id=A.clc_ur_id
            LEFT JOIN ctrl2013.scp_Ceres AS X on A.clc_gpo_id = X.clc_gpo_id
            AND A.clc_ur_id = X.clc_ur_id AND A.clc_id = X.clc_id
            LEFT JOIN ctrl2013.scp_Cerecos Y on A.clc_gpo_id = Y.clc_gpo_id
            AND A.clc_ur_id = Y.clc_ur_id AND A.clc_id = Y.clc_id
         WHERE ecl_id = 7 OR X.ececo_id in (4,6)
         HAVING LOTE_PAGO !='' AND SPEI IS NULL;";
        }
        if ($paginacion == 2012) {
            $sql = "SELECT
            CONCAT(a.gpo_id,a.ur_id) UR,
            a.cedula as CLC,
            c.ecl_nombre as EDO_CLC,
            a.concepto CONCEPTO
            FROM ctrl2012.scp_m_orden as a
            LEFT JOIN ctrl2012.scp_r_cedula as b on b.gpo_id=a.gpo_id and b.ur_id=a.ur_id and b.cedula=a.cedula
            LEFT JOIN ctrl2012.cat_EstadoClc as c on c.status=b.status
            LEFT JOIN ctrl2012.cat_UnidadResponsable UR_OP on UR_OP.gpo_id=a.gpo_id and UR_OP.ur_id=a.ur_id
            WHERE a.tra_lote <>'' and a.tra_fecha <>'' and b.status =8
            AND a.tra_numero ='' OR a.tra_fechapago='';
            ";
        }
        if ($paginacion == 2011) {
            $sql = "SELECT
            A.UR,
            C.ue as UE,
            A.CEDULA as CLC ,
            A.CONCEP AS CONCEPTO
            FROM ctrl2011.scp_m_hismin as A
            LEFT JOIN ctrl2011.cat_unidadresponsable as B on B.ur=A.UR
            LEFT JOIN ctrl2011.scp_r_cedula as C on C.ur=A.UR and C.cedula=A.CEDULA
            LEFT JOIN ctrl2011.cat_tipoclc as D on D.tipoclc=C.tipoced
            WHERE A.tra_numero ='';
            ";
        }
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaConsultaClc';
        $nav->conexion = 'sefipAnterior';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
    }
}
if (isset($_SESSION[_S]['prm']['T']['1']['4'])) {
    new ControladorConsultaTesoreria();
} else {
    WebResponse::sinPermisos();
}
