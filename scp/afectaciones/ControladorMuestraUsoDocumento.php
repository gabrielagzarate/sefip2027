<?php
/*
    Mostramos las afectaciones en que se ha utilizado un documento dado, agrupado por STATUS de la afectacion
    y sumarizando totales por status
*/
DEFINE("LINEAS_POR_PAGINA", 60);
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
class ControladorMuestraUsoDocumento extends WebResponse
{
    protected $lectura;
    protected $escritura;
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "scp/afectaciones/ControladorMuestraUsoDocumento.php";
        // Permisos
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, "C_1_2");
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, "C_1_2");
        $this->escritura = true;

        switch ($this->metodo) {
          
            default:
                $this->dibujaPantalla();
                break;
        }
        $this->returnJson();
    }

    public function dibujaPantalla()
    {
        $rfc = ((isset($this->prm->rfc) && $this->prm->rfc != "") ? $this->prm->rfc : "");
        $ref = ((isset($this->prm->ref) && $this->prm->ref != "") ? $this->prm->ref : "");
        $id = ((isset($this->prm->id) && $this->prm->id  != "") ? $this->prm->id  : "");

        $tablaConsulta = new stdClass();
        $tablaConsulta->id = 'MuestraUsoDocumentos';
        $tablaConsulta->titulo = "Mostrar dónde se ha usado el documento";

        $tr = array();
         $th = new stdClass();
        $th->align = 'center';
        $th->width = '5%';
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'center';
        $th->width = '5%';
        $th->texto = 'AFECTACION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '5%';
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->aling = 'left';
        $th->width = '10%';
        $th->texto = 'FECHA REF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '40%';
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'CLAVE CUENTA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'IMPORTE NETO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'AUTORIZADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'CANCELADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->align = 'left';
        $th->width = '10%';
        $th->texto = 'MOVIMIENTO';
        array_push($tr, $th);
        //Se inicia el cuerpo de la Tabla
        $trbody = array();

         $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'sa';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ia';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ic';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'fr';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'ca';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'cv';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'importeneto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'fe';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'fc';
        array_push($trbody, $td);

        
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'mv';
        array_push($trbody, $td);


        $tablaConsulta->encabezado = array($tr);
        $tablaConsulta->cuerpo = ($trbody);

        $html =
            '<div >
                </div>' . $this->wrPonTabla($tablaConsulta) . '
                <div>
            </div>';

        $this->setContenedor("bodyModalInfo");
        $this->mixHtml($html);
        $js = <<<JS
            $(".modal-title").html("Mostrar dónde se ha usado el documento");
            $("#myModalInfo").modal();
            $("#myModalInfo").show();

        JS;
        $this->mixScript($js);

        $data = $this->DameDocumentos($rfc, $ref, $id);
        $tabla = new stdClass();
        $tabla->filas = $data;


        $this->mixScript('$("#MuestraUsoDocumentos").tabla(' . json_encode($tabla) . ');');
    }
    /* Regresamos en formato JSON las afectaciones donde se usó el documento solicitado------------- */
    private function DameDocumentos($rfc, $ref, $id)
    {
        $parametros = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $rfc, $ref];
        $sql = '
            SELECT
                COUNT(bdo_id) AS total
            FROM cat_BancoDocumentos
            WHERE gpo_id = ?
                AND ur_id = ?
                AND rfc_id = ?
                AND bdo_referencia = ?;
        ';

        $conexion =  new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();
        $row = $conexion->extraer();
        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $rfc,
            $ref,
            $id,
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $rfc,
            $ref,
            $id
        ];

        $sql = '
            SELECT
                p.afe_id, ea.eaf_nombre, p.clc_id, ncp_fechaReferencia, ncp_concepto,
                CONCAT(ep.gpo_id, ep.ur_id, "-", ep.prg_id, ep.spr_id, ep.py_id, ep.oa_id, "-", pg_id, "-", pe_id, "-", cf.cff_id, cf.fte_id, cf.sft_id, cf.ori_id, cf.sor_id, cf.cfi_id, cf.cfi_anio) AS clave,
                SUM(ncp_percepcionNeto) as ncp_percepcionNeto, DATE_FORMAT(afe_fechaAutorizado, "%Y-%m-%d") as afe_fechaAutorizado, DATE_FORMAT(afe_fechaCancelado, "%Y-%m-%d") as afe_fechaCancelado, "presupuestal" as movimiento
            FROM scp_Presupuestal p
            INNER JOIN enc_Programatico ep USING(epg_id)
            INNER JOIN enc_ClaveFinanciamiento ecf USING(ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            INNER JOIN scp_Afectacion a ON (p.gpo_id = a.gpo_id AND p.ur_id = a.ur_id AND p.afe_id = a.afe_id)
            INNER JOIN cat_EstadoAfectacion ea USING(eaf_id)
            WHERE p.gpo_id = ? AND p.ur_id = ?
            AND p.rfc_id = ? AND p.ncp_referencia = ? AND p.bdo_id = ?
            GROUP by afe_id,clc_id
            UNION

            SELECT
                p.afe_id, ea.eaf_nombre, p.clc_id, ncc_fechaReferencia AS ncp_fechaReferencia, ncc_concepto AS ncp_Concepto,
                CONCAT(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", cta_gpo_id, cta_ur_id, ".", cta_ue_id, ".", cgc_id, ".", cta_id) AS clave,
                SUM(CASE ncc_tipoMovimiento WHEN "+" THEN ncc_percepcion ELSE ncc_retencion END) AS ncp_percepcionNeto, DATE_FORMAT(afe_fechaAutorizado, "%Y-%m-%d") as afe_fechaAutorizado, DATE_FORMAT(afe_fechaCancelado, "%Y-%m-%d") as afe_fechaCancelado, "contable" as movimiento
            FROM scp_Contable p
            INNER JOIN scp_Afectacion a USING(gpo_id,ur_id,afe_id)
            INNER JOIN cat_EstadoAfectacion ea USING(eaf_id)
            WHERE p.gpo_id = ? AND p.ur_id = ?
            AND p.rfc_id = ? AND p.ncc_referencia = ? AND p.bdo_id = ?
           	GROUP by afe_id,clc_id
    
        ';
        

        $conexion =  new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $nRegistros = $conexion->getNumeroRegistros();
        $nId = 1;

        if ($nRegistros <= 0) {
            return json_encode(array('id' => 0, 'ia' => '', 'sa' => '', 'ic' => '', 'fr' => '', 'ca' => '', 'cv' => '', 'importeneto' => '', 'fa' => '', 'fc' => ''));
        } else {
            $data = array();
            while ($row = $conexion->extraer()) {
                array_push($data, array(
                    'id' => ++$nId,
                    'ia' => $row['afe_id'],
                    'sa' => $row['eaf_nombre'],
                    'ic' => $row['clc_id'],
                    'fr' => $row['ncp_fechaReferencia'],
                    'ca' => $row['ncp_concepto'],
                    'cv' => $row['clave'],
                    'importeneto' => $row['ncp_percepcionNeto'],
                    'fe' => $row['afe_fechaAutorizado'],
                    'fc' => $row['afe_fechaCancelado'],
                    'mv' =>$row['movimiento']
                ));
            }

   /*  $agrupado = [];

foreach ($data as $row) {
    $sa = $row['sa'];  // clave del grupo

    // Crear grupo si no existe
    if (!isset($agrupado[$sa])) {
        $agrupado[$sa] = [];
    }

    // Agregar registro al grupo
    $agrupado[$sa][] = $row;
}*/


            return $data;
        }
    }


  
}
new ControladorMuestraUsoDocumento();
